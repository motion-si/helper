<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Timesheet;

use App\Models\TicketHour;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\BarChartWidget;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MonthlyReport extends BarChartWidget
{
    protected function getHeading(): string
    {
        return __('Logged time monthly');
    }

    public ?string $filter;

    protected $listeners = [];

    protected function getData(): array
    {
        $collection = $this->filter(auth()->user(), [
            'year' => $this->filter
        ]);

        $datasets = $this->getDatasets($this->buildRapport($collection));

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

    public function mount(): void
    {
        $this->filter = (string) Carbon::now()->year;

        parent::mount();
    }

    protected function getFilters(): ?array
    {
        $years = TicketHour::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->pluck('year')
            ->sortDesc()
            ->toArray();

        if (! in_array(Carbon::now()->year, $years, true)) {
            array_unshift($years, Carbon::now()->year);
        }

        return array_combine($years, $years);
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

    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 3
    ];

    protected function filter(User $user, array $params)
    {
        return TicketHour::select([
            DB::raw("DATE_FORMAT(created_at,'%m') as month"),
            DB::raw('ROUND(SUM(TIME_TO_SEC(value)) / 60) as value'),
        ])
            ->whereRaw(
                DB::raw("YEAR(created_at)=" . (is_null($params['year']) ? Carbon::now()->format('Y') : $params['year']))
            )
            ->where('user_id', $user->id)
            ->groupBy(DB::raw("DATE_FORMAT(created_at,'%m')"))
            ->get();
    }

    public function updatedFilter(): void
    {
        parent::updatedFilter();
        $this->emit('yearUpdated', $this->filter);
    }

    protected function getDatasets(array $rapportData): array
    {
        $datasets = [
            'sets' => [],
            'labels' => []
        ];

        foreach ($rapportData as $data) {
            $datasets['sets'][] = $data[1];
            $datasets['labels'][] = $data[0];
        }

        return $datasets;
    }

    protected function buildRapport(Collection $collection): array
    {
        $months = [
            1 => ['January', 0],
            2 => ['February', 0],
            3 => ['March', 0],
            4 => ['April', 0],
            5 => ['May', 0],
            6 => ['June', 0],
            7 => ['July', 0],
            8 => ['August', 0],
            9 => ['September', 0],
            10 => ['October', 0],
            11 => ['November', 0],
            12 => ['December', 0]
        ];

        foreach ($collection as $value) {
            if (isset($months[(int)$value->month])) {
                $months[(int)$value->month][1] = (float)$value->value;
            }
        }

        return $months;
    }
}
