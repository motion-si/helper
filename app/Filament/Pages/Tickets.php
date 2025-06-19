<?php

namespace App\Filament\Pages;

use App\Models\Client;
use App\Models\Project;
use App\Models\Sprint;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\TicketType;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class Tickets extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $slug = 'reports/tickets';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.tickets';

    protected static function getNavigationLabel(): string
    {
        return __('Tickets');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Reports');
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Card::make()->schema([
                Grid::make()->columns(3)->schema([
                    Select::make('client_id')
                        ->label(__('Client'))
                        ->options(fn () => Client::all()->pluck('name', 'id')->toArray())
                        ->searchable(),
                    Select::make('status_id')
                        ->label(__('Status'))
                        ->options(fn () => TicketStatus::all()->pluck('name', 'id')->toArray())
                        ->searchable(),
                    Select::make('type_id')
                        ->label(__('Type'))
                        ->options(fn () => TicketType::all()->pluck('name', 'id')->toArray())
                        ->searchable(),
                    Select::make('project_id')
                        ->label(__('Project'))
                        ->options(fn () => Project::all()->pluck('name', 'id')->toArray())
                        ->searchable(),
                    Select::make('sprint_id')
                        ->label(__('Sprint'))
                        ->options(fn () => Sprint::accessibleBy(auth()->user())->pluck('name', 'id')->toArray())
                        ->searchable(),
                    DatePicker::make('billing_reference')
                        ->label(__('Sprint billing reference'))
                        ->displayFormat('Y-m')
                        ->closeOnDateSelection(),
                ])
            ])
        ];
    }

    protected function getTableQuery(): Builder
    {
        $data = $this->form->getState();

        $query = Ticket::query()->with([
            'project',
            'client',
            'owner',
            'responsible',
            'developer',
            'status',
            'type',
            'priority',
            'sprint',
        ]);

        if (!empty($data['client_id'])) {
            $query->where('client_id', $data['client_id']);
        }

        if (!empty($data['status_id'])) {
            $query->where('status_id', $data['status_id']);
        }

        if (!empty($data['type_id'])) {
            $query->where('type_id', $data['type_id']);
        }

        if (!empty($data['project_id'])) {
            $query->where('project_id', $data['project_id']);
        }

        if (!empty($data['sprint_id'])) {
            $query->where('sprint_id', $data['sprint_id']);
        }

        if (!empty($data['billing_reference'])) {
            $date = Carbon::parse($data['billing_reference']);
            $query->whereHas('sprint', function (Builder $q) use ($date) {
                $q->whereYear('billing_reference', $date->year)
                    ->whereMonth('billing_reference', $date->month);
            });
        }

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('code')->label(__('Ticket code'))->sortable(),
            Tables\Columns\TextColumn::make('name')->label(__('Ticket name'))->limit(30)->sortable(),
            Tables\Columns\TextColumn::make('project.name')->label(__('Project'))->sortable(),
            Tables\Columns\TextColumn::make('client.name')->label(__('Client'))->sortable(),
            Tables\Columns\TextColumn::make('owner.name')->label(__('Owner'))->sortable(),
            Tables\Columns\TextColumn::make('responsible.name')->label(__('Responsible'))->sortable(),
            Tables\Columns\TextColumn::make('developer.name')->label(__('Developer'))->sortable(),
            Tables\Columns\TextColumn::make('status.name')->label(__('Status'))->sortable(),
            Tables\Columns\TextColumn::make('type.name')->label(__('Type'))->sortable(),
            Tables\Columns\TextColumn::make('priority.name')->label(__('Priority'))->sortable(),
            Tables\Columns\TextColumn::make('created_at')->label(__('Created At'))->date()->sortable(),
            Tables\Columns\TextColumn::make('credits')->label(__('Credits'))->sortable(),
            Tables\Columns\TextColumn::make('development_environment')->label(__('Development environment')),
            Tables\Columns\TextColumn::make('released_at')->label(__('Released at'))->date(),
            Tables\Columns\IconColumn::make('false_bug_report')->label(__('False bug report'))->boolean(),
            Tables\Columns\TextColumn::make('sprint.name')->label(__('Sprint name'))->sortable(),
            Tables\Columns\TextColumn::make('sprint.starts_at')->label(__('Sprint start date'))->date()->sortable(),
            Tables\Columns\TextColumn::make('sprint.ends_at')->label(__('Sprint end date'))->date()->sortable(),
            Tables\Columns\TextColumn::make('sprint.tickets_credits')->label(__('Sprint ticket credits')),
            Tables\Columns\TextColumn::make('sprint.extra_credits')->label(__('Sprint extra credits')),
            Tables\Columns\TextColumn::make('sprint.total_credits')->label(__('Sprint total credits')),
            Tables\Columns\IconColumn::make('sprint.billed')->label(__('Sprint billed'))->boolean(),
            Tables\Columns\TextColumn::make('sprint.billing_reference')->label(__('Sprint billing reference'))->date(),
        ];
    }
}

