<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Timesheet;

use App\Models\TicketHour;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\BarChartWidget;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ActivitiesReport extends BarChartWidget
{
    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 3
    ];

    public ?string $filter;

    protected $listeners = ['yearUpdated' => 'onYearUpdated'];

    private ?string $currentYear = null;

    protected function getHeading(): string
    {
        return __('Logged time by activity');
    }

    protected function getFilters(): ?array
    {
        $year = $this->currentYear ?? Carbon::now()->year;

        $dates = TicketHour::selectRaw('MONTH(created_at) as m')
            ->whereYear('created_at', $year)
            ->distinct()
            ->orderBy('m')
            ->get()
            ->mapWithKeys(function ($row) use ($year) {
                $carbon = Carbon::create($year, $row->m);
                $value  = $carbon->format('Y-m');
                $label  = $carbon->translatedFormat('F/Y');
                return [$value => $label];
            })
            ->toArray();

        if ($dates === []) {
            $carbon = Carbon::create($year, 1);
            $dates  = [$carbon->format('Y-m') => $carbon->translatedFormat('F/Y')];
        }

        return $dates;
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

    public function updatedFilter(): void
    {
        parent::updatedFilter();
        $this->emit('monthUpdated', $this->filter);
    }

    protected function getData(): array
    {
        $collection = $this->filter(auth()->user(), [
            'year' => $this->filter
        ]);

        $datasets = $this->getDatasets($collection);

        return [
            'datasets' => [
                [
                    'label' => __('Total time logged'),
                    'data' => $datasets['sets'],
                    'backgroundColor' => [
                        'rgba(54, 162, 235, .6)'
                    ],
                    'borderColor' => [
                        'rgba(54, 162, 235, .8)'
                    ],
                ],
            ],
            'labels' => $datasets['labels'],
        ];
    }

    protected function getDatasets(Collection $collection): array
    {
        $datasets = [
            'sets' => [],
            'labels' => []
        ];

        foreach ($collection as $item) {
            $datasets['sets'][] = $item->value;
            $datasets['labels'][] = $item->activity?->name ?? __('No activity');
        }

        return $datasets;
    }

    public function mount(): void
    {
        $this->filter = Carbon::now()->format('Y-m');

        parent::mount();
    }

    protected function filter(User $user, array $params): Collection
    {
        [$year, $month] = explode('-', $this->filter);

        return TicketHour::with('activity')
            ->select([
                'activity_id',
                DB::raw('ROUND(SUM(TIME_TO_SEC(value)) / 60) as value'),
            ])
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('user_id', $user->id)
            ->groupBy('activity_id')
            ->get();
    }

    public function onYearUpdated(string $year): void
    {
        $this->currentYear = $year;

        $months = $this->getFilters();
        $this->filter = array_key_first($months);

        $this->emit('monthUpdated', $this->filter);
    }
}
