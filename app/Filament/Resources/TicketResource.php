<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketRelation;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Client;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\HtmlString;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?int $navigationSort = 2;

    protected static function getNavigationLabel(): string
    {
        return __('Tickets');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Select::make('project_id')
                                    ->label(__('Project'))
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($get, $set) {
                                        $project = Project::where('id', $get('project_id'))->first();
                                        if ($project?->status_type === 'custom') {
                                            $set(
                                                'status_id',
                                                TicketStatus::where('project_id', $project->id)
                                                    ->where('is_default', true)
                                                    ->first()
                                                    ?->id
                                            );
                                        } else {
                                            $set(
                                                'status_id',
                                                TicketStatus::whereNull('project_id')
                                                    ->where('is_default', true)
                                                    ->first()
                                                    ?->id
                                            );
                                        }
                                    })
                                    ->options(fn() => Project::accessibleBy(auth()->user())
                                        ->pluck('name', 'id')->toArray())
                                    ->default(fn() => request()->get('project'))
                                    ->required(),
                                Forms\Components\Grid::make()
                                    ->columns(12)
                                    ->columnSpan(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('code')
                                            ->label(__('Ticket code'))
                                            ->visible(fn($livewire) => !($livewire instanceof CreateRecord))
                                            ->columnSpan(2)
                                            ->disabled(),

                                        Forms\Components\TextInput::make('name')
                                            ->label(__('Ticket name'))
                                            ->required()
                                            ->columnSpan(
                                                fn($livewire) => !($livewire instanceof CreateRecord) ? 10 : 12
                                            )
                                            ->maxLength(255),
                                    ]),

                                Forms\Components\Grid::make()
                                    ->columns(3)
                                    ->columnSpan(2)
                                    ->schema([
                                        Forms\Components\Select::make('client_id')
                                            ->label(__('Client'))
                                            ->searchable()
                                            ->options(fn () => Client::all()->pluck('name', 'id')->toArray())
                                            ->visible(fn () => !auth()->user()->hasRole('Customer'))
                                            ->required(fn () => !auth()->user()->hasRole('Customer')),

                                        Forms\Components\Select::make('owner_id')
                                            ->label(__('Ticket owner'))
                                            ->searchable()
                                            ->options(fn() => User::all()->pluck('name', 'id')->toArray())
                                            ->default(fn() => auth()->user()->id)
                                            ->required(),

                                        Forms\Components\Select::make('responsible_id')
                                            ->label(__('Ticket responsible'))
                                            ->searchable()
                                            ->options(fn() => User::all()->pluck('name', 'id')->toArray()),

                                        Forms\Components\Select::make('developer_id')
                                            ->label(__('Developer'))
                                            ->searchable()
                                            ->options(fn() => User::role(['Developer', 'Project Manager'])->pluck('name', 'id')->toArray()),
                                    ]),

                                Forms\Components\Toggle::make('send_email')
                                    ->label(__('Send email'))
                                    ->default(true)
                                    ->columnSpan(2)
                                    ->visible(fn($livewire) => $livewire instanceof CreateRecord),

                                Forms\Components\Grid::make()
                                    ->columns(3)
                                    ->columnSpan(2)
                                    ->schema([
                                        Forms\Components\Select::make('status_id')
                                            ->label(__('Ticket status'))
                                            ->searchable()
                                            ->options(function ($get) {
                                                $project = Project::where('id', $get('project_id'))->first();
                                                if ($project?->status_type === 'custom') {
                                                    return TicketStatus::where('project_id', $project->id)
                                                        ->get()
                                                        ->pluck('name', 'id')
                                                        ->toArray();
                                                } else {
                                                    return TicketStatus::whereNull('project_id')
                                                        ->get()
                                                        ->pluck('name', 'id')
                                                        ->toArray();
                                                }
                                            })
                                            ->default(function ($get) {
                                                $project = Project::where('id', $get('project_id'))->first();
                                                if ($project?->status_type === 'custom') {
                                                    return TicketStatus::where('project_id', $project->id)
                                                        ->where('is_default', true)
                                                        ->first()
                                                        ?->id;
                                                } else {
                                                    return TicketStatus::whereNull('project_id')
                                                        ->where('is_default', true)
                                                        ->first()
                                                        ?->id;
                                                }
                                            })
                                            ->required(),

                                        Forms\Components\Select::make('type_id')
                                            ->label(__('Ticket type'))
                                            ->searchable()
                                            ->options(fn() => TicketType::all()->pluck('name', 'id')->toArray())
                                            ->default(fn() => TicketType::where('is_default', true)->first()?->id)
                                            ->required(),

                                        Forms\Components\Select::make('priority_id')
                                            ->label(__('Ticket priority'))
                                            ->searchable()
                                            ->options(fn() => TicketPriority::all()->pluck('name', 'id')->toArray())
                                            ->default(fn() => TicketPriority::where('is_default', true)->first()?->id)
                                            ->required(),
                                    ]),
                            ]),

                        Forms\Components\RichEditor::make('content')
                            ->label(__('Ticket content'))
                            ->required()
                            ->columnSpan(2),

                        Forms\Components\Grid::make()
                            ->columnSpan(2)
                            ->columns(12)
                            ->schema([
                                Forms\Components\TimePicker::make('estimation')
                                    ->label(__('Estimation time'))
                                    ->withoutSeconds()
                                    ->minutesStep(10)
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('credits')
                                    ->label(__('Credits'))
                                    ->numeric()
                                    ->columnSpan(2),
                            ]),

                        Forms\Components\Grid::make()
                            ->columnSpan(2)
                            ->columns(12)
                            ->schema([
                                Forms\Components\TextInput::make('branch')
                                    ->label(__('Branch'))
                                    ->columnSpan(3)
                                    ->visible(fn() => auth()->user()->hasAnyRole(['Project Manager','Developer'])),
                                Forms\Components\TextInput::make('development_environment')
                                    ->label(__('Development Environment'))
                                    ->columnSpan(3)
                                    ->disabled(fn() => auth()->user()->hasRole('Customer')),
                                Forms\Components\DatePicker::make('starts_at')
                                    ->label(__('Starts At'))
                                    ->columnSpan(3)
                                    ->visible(fn() => auth()->user()->hasAnyRole(['Project Manager','Developer'])),
                                Forms\Components\DatePicker::make('ends_at')
                                    ->label(__('Ends At'))
                                    ->columnSpan(3)
                                    ->visible(fn() => auth()->user()->hasAnyRole(['Project Manager','Developer'])),
                                Forms\Components\DatePicker::make('released_at')
                                    ->label(__('Released At'))
                                    ->columnSpan(3)
                                    ->disabled(fn() => !auth()->user()->hasRole('Project Manager')),
                                Forms\Components\Toggle::make('false_bug_report')
                                    ->label(__('False Bug Report'))
                                    ->columnSpan(2)
                                    ->disabled(fn() => !auth()->user()->hasRole('Project Manager')),
                            ]),

                        Forms\Components\Repeater::make('relations')
                            ->itemLabel(function (array $state) {
                                $ticketRelation = TicketRelation::find($state['id'] ?? 0);
                                if ($ticketRelation) {
                                    return __(config('system.tickets.relations.list.' . $ticketRelation->type))
                                        . ' '
                                        . $ticketRelation->relation->name
                                        . ' (' . $ticketRelation->relation->code . ')';
                                }
                                return null;
                            })
                            ->relationship()
                            ->collapsible()
                            ->collapsed()
                            ->orderable()
                            ->defaultItems(0)
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->columns(3)
                                    ->schema([
                                        Forms\Components\Select::make('type')
                                            ->label(__('Relation type'))
                                            ->required()
                                            ->searchable()
                                            ->options(config('system.tickets.relations.list'))
                                            ->default(fn() => config('system.tickets.relations.default')),

                                        Forms\Components\Select::make('relation_id')
                                            ->label(__('Related ticket'))
                                            ->required()
                                            ->searchable()
                                            ->columnSpan(2)
                                            ->options(function ($livewire) {
                                                $query = Ticket::query();
                                                if ($livewire instanceof EditRecord && $livewire->record) {
                                                    $query->where('id', '<>', $livewire->record->id);
                                                }
                                                return $query->get()->pluck('name', 'id')->toArray();
                                            }),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function tableColumns(bool $withProject = true): array
    {
        $columns = [
            Tables\Columns\TextColumn::make('code')
                ->label(__('Ticket code'))
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('name')
                ->label(__('Ticket name'))
                ->sortable()
                ->searchable(),
        ];

        if ($withProject) {
            $columns[] = Tables\Columns\TextColumn::make('project.name')
                ->label(__('Project'))
                ->sortable()
                ->searchable();
        }

        $columns = array_merge($columns, [
            Tables\Columns\TextColumn::make('client.abbreviation')
                ->label(__('Client'))
                ->sortable()
                ->visible(fn () => !auth()->user()->hasRole('Customer'))
                ->searchable(),

            Tables\Columns\TextColumn::make('owner.name')
                ->label(__('Owner'))
                ->sortable()
                ->formatStateUsing(fn($record) => view('components.user-avatar', ['user' => $record->owner]))
                ->searchable(),

            Tables\Columns\TextColumn::make('responsible.name')
                ->label(__('Responsible'))
                ->sortable()
                ->formatStateUsing(fn($record) => view('components.user-avatar', ['user' => $record->responsible]))
                ->searchable(),

            Tables\Columns\TextColumn::make('developer.name')
                ->label(__('Developer'))
                ->sortable()
                ->formatStateUsing(fn($record) => view('components.user-avatar', ['user' => $record->developer]))
                ->searchable(),

            Tables\Columns\TextColumn::make('status.name')
                ->label(__('Status'))
                ->formatStateUsing(fn($record) => new HtmlString('
                            <div class="flex items-center gap-2 mt-1">
                                <span class="filament-tables-color-column relative flex h-6 w-6 rounded-md" style="background-color: ' . $record->status->color . '"></span>
                                <span>' . $record->status->name . '</span>
                            </div>
                        '))
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('type.name')
                ->label(__('Type'))
                ->formatStateUsing(
                    fn($record) => view('partials.filament.resources.ticket-type', ['state' => $record->type])
                )
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('priority.name')
                ->label(__('Priority'))
                ->formatStateUsing(fn($record) => new HtmlString('
                            <div class="flex items-center gap-2 mt-1">
                                <span class="filament-tables-color-column relative flex h-6 w-6 rounded-md" style="background-color: ' . $record->priority->color . '"></span>
                                <span>' . $record->priority->name . '</span>
                            </div>
                        '))
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('branch')
                ->label(__('Branch'))
                ->sortable()
                ->searchable()
                ->visible(fn () => auth()->user()->hasAnyRole(['Project Manager','Developer'])),

            Tables\Columns\TextColumn::make('development_environment')
                ->label(__('Development Environment'))
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('starts_at')
                ->label(__('Starts At'))
                ->date()
                ->sortable()
                ->visible(fn () => auth()->user()->hasAnyRole(['Project Manager','Developer'])),

            Tables\Columns\TextColumn::make('ends_at')
                ->label(__('Ends At'))
                ->date()
                ->sortable()
                ->visible(fn () => auth()->user()->hasAnyRole(['Project Manager','Developer'])),

            Tables\Columns\TextColumn::make('released_at')
                ->label(__('Released At'))
                ->date()
                ->sortable(),

            Tables\Columns\IconColumn::make('false_bug_report')
                ->label(__('False Bug Report'))
                ->boolean(),

            Tables\Columns\TextColumn::make('created_at')
                ->label(__('Created at'))
                ->dateTime()
                ->sortable()
                ->searchable(),
        ]);
        return $columns;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::tableColumns())
            ->filters([
                Tables\Filters\SelectFilter::make('project_id')
                    ->label(__('Project'))
                    ->multiple()
                    ->options(fn() => Project::accessibleBy(auth()->user())
                        ->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('code')
                    ->label(__('Ticket code'))
                    ->multiple()
                    ->options(fn() => Ticket::all()->pluck('code', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('name')
                    ->label(__('Ticket name'))
                    ->multiple()
                    ->options(fn() => Ticket::all()->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('client_id')
                    ->label(__('Client'))
                    ->multiple()
                    ->options(fn() => Client::all()->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('owner_id')
                    ->label(__('Owner'))
                    ->multiple()
                    ->options(fn() => User::all()->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('responsible_id')
                    ->label(__('Responsible'))
                    ->multiple()
                    ->options(fn() => User::all()->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('developer_id')
                    ->label(__('Developer'))
                    ->multiple()
                    ->options(fn() => User::role(['Developer', 'Project Manager'])->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('status_id')
                    ->label(__('Status'))
                    ->multiple()
                    ->options(fn() => TicketStatus::all()->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('type_id')
                    ->label(__('Type'))
                    ->multiple()
                    ->options(fn() => TicketType::all()->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('priority_id')
                    ->label(__('Priority'))
                    ->multiple()
                    ->options(fn() => TicketPriority::all()->pluck('name', 'id')->toArray()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CommentsRelationManager::class,
            RelationManagers\TicketNotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
