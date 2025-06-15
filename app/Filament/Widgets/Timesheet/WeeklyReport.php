<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Timesheet;

use App\Models\TicketHour;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\BarChartWidget;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class WeeklyReport extends BarChartWidget
{
    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 3
    ];

    public ?string $filter;

    protected $listeners = ['monthUpdated' => 'onMonthUpdated'];

    public array $weeksList = [];

    protected function getHeading(): string
    {
        return __('Weekly logged time');
    }

    protected function getData(): array
    {
        [$weekStart, $weekEnd] = explode('|', $this->filter);

        $collection = $this->filter(auth()->user(), [
            'weekStartDate' => $weekStart,
            'weekEndDate'   => $weekEnd,
        ]);

        $dates = $this->buildDatesRange($weekStart, $weekEnd);

        $datasets = $this->buildRapport($collection, $dates);

        return [
            'datasets' => [
                [
                    'label' => __('Weekly time logged'),
                    'data' => $datasets,
                    'backgroundColor' => [
                        'rgba(54, 162, 235, .6)'
                    ],
                    'borderColor' => [
                        'rgba(54, 162, 235, .8)'
                    ],
                ],
            ],
            'labels' => $dates,
        ];
    }

    protected function getFilters(): ?array
    {
        return $this->weeksList;
    }

    protected static ?array $options = [
        'plugins' => [
            'legend' => [
                'display' => true,
            ],
        ],
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'title' => [
                    'display' => true,
                    'text' => 'Minutes',
                ],
            ],
        ],
    ];

    public function mount(): void
    {
        $month = Carbon::now();
        $this->weeksList = $this->weeksOfMonth($month);

        $today = Carbon::today()->toDateString();
        $this->filter = collect($this->weeksList)->keys()->first(function ($range) use ($today) {
            [$from, $to] = explode('|', $range);
            return $today >= $from && $today <= $to;
        }) ?? array_key_first($this->weeksList);

        parent::mount();
    }

    protected function weeksOfMonth(Carbon $month): array
    {
        $weeks  = [];
        $cursor = $month->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);

        while ($cursor->lessThanOrEqualTo($month->copy()->endOfMonth())) {
            $weekStart = $cursor->copy();
            $weekEnd   = $cursor->copy()->endOfWeek();

            if ($weekStart->month !== $month->month && $weekEnd->month !== $month->month) {
                $cursor->addWeek();
                continue;
            }

            $value = $weekStart->toDateString() . '|' . $weekEnd->toDateString();
            $label = sprintf(
                '#%02d – %s – %s',
                $weekStart->isoWeek(),
                $weekStart->format("d/m"),
                $weekEnd->format("d/m")
            );

            $weeks[$value] = $label;
            $cursor->addWeek();
        }

        return $weeks;
    }

    protected function buildRapport(Collection $collection, array $dates): array
    {
        $template = $this->createReportTemplate($dates);
        foreach ($collection as $item) {
            $template[$item->day]['value'] =  $item->value;
        }
        return collect($template)->pluck('value')->toArray();
    }

    protected function filter(User $user, array $params)
    {
        $start = Carbon::parse($params['weekStartDate'])->startOfDay();
        $end = Carbon::parse($params['weekEndDate'])->endOfDay();

        return TicketHour::select([
                DB::raw('DATE(created_at) as day'),
                DB::raw('ROUND(SUM(TIME_TO_SEC(value)) / 60) as value'),
            ])
            ->whereBetween('created_at', [$start, $end])
            ->where('user_id', $user->id)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();
    }

    protected function buildDatesRange($weekStartDate, $weekEndDate): array
    {
        $period = CarbonPeriod::create($weekStartDate, $weekEndDate);

        $dates = [];
        foreach ($period as $item) {
            $dates[] = $item->format('Y-m-d');
        }

        return $dates;
    }

    protected function createReportTemplate(array $dates): array
    {
        $template = [];
        foreach ($dates as $date) {
            $template[$date]['value'] = 0;
        }
        return $template;
    }

    protected function yearWeeks(): array
    {
        $year = date_create('today')->format('Y');

        $dtStart = date_create('2 jan ' . $year)->modify('last Monday');
        $dtEnd = date_create('last monday of Dec ' . $year);

        for ($weeks = []; $dtStart <= $dtEnd; $dtStart->modify('+1 week')) {
            $from = $dtStart->format('Y-m-d');
            $to = (clone $dtStart)->modify('+6 Days')->format('Y-m-d');
            $weeks[$from . ' - ' . $to] = $from . ' - ' . $to;
        }

        return $weeks;
    }

    protected function getWeekStartAndFinishDays(): array
    {
        $now = Carbon::now();

        return [
            'weekStartDate' => $now->startOfWeek()->format('Y-m-d'),
            'weekEndDate' => $now->endOfWeek()->format('Y-m-d')
        ];
    }

    public function onMonthUpdated(string $ym): void
    {
        $month = Carbon::createFromFormat('Y-m', $ym);
        $this->weeksList = $this->weeksOfMonth($month);
        $this->filter    = array_key_first($this->weeksList);
    }
}
