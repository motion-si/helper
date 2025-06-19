<?php

namespace App\Filament\Widgets;

use App\Models\Sprint;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ActiveSprints extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = [
        'sm' => 1,
        'md' => 6,
        'lg' => 6,
    ];

    public function mount(): void
    {
        self::$heading = __('Active sprints');
    }

    public static function canView(): bool
    {
        return auth()->user()->can('List sprints');
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    protected function getTableQuery(): Builder
    {
        return Sprint::accessibleBy(auth()->user())
            ->whereDate('starts_at', '<=', now())
            ->whereDate('ends_at', '>=', now());
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')->label(__('Sprint name')),
            Tables\Columns\TextColumn::make('client.name')->label(__('Client')),
            Tables\Columns\TextColumn::make('ends_at')->label(__('Sprint end date'))->date(),
            Tables\Columns\TextColumn::make('remaining')->label(__('Remaining'))->suffix(fn($record) => $record->remaining ? ' '. __('days') : ''),
        ];
    }
}
