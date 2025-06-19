<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SprintResource\Pages;
use App\Models\Client;
use App\Models\Sprint;
use App\Models\Ticket;
use App\Models\TicketStatus;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\HtmlString;

class SprintResource extends Resource
{
    protected static ?string $model = Sprint::class;

    protected static ?string $navigationIcon = 'heroicon-o-fast-forward';

    protected static ?int $navigationSort = 2;

    protected static function getNavigationLabel(): string
    {
        return __('Sprints');
    }

    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('Management');
    }

    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('List sprints') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Sprint name'))
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('client_id')
                                    ->label(__('Client'))
                                    ->searchable()
                                    ->options(fn() => Client::all()->pluck('name','id')->toArray())
                                    ->required(),
                                Forms\Components\DatePicker::make('starts_at')
                                    ->label(__('Sprint start date'))
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, $set) => $set('ends_at', \Carbon\Carbon::parse($state)->addWeek()->subDay()))
                                    ->required(),
                                Forms\Components\DatePicker::make('ends_at')
                                    ->label(__('Sprint end date'))
                                    ->required(),
                                Forms\Components\RichEditor::make('description')
                                    ->label(__('Sprint description'))
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('tickets_credits')
                                    ->label(__('Tickets Credits'))
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false),
                                Forms\Components\TextInput::make('extra_credits')
                                    ->label(__('Extra Credits'))
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\TextInput::make('total_credits')
                                    ->label(__('Total Credits'))
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false),
                                Forms\Components\Toggle::make('billed')
                                    ->label(__('Billed')),
                                Forms\Components\DatePicker::make('billing_reference')
                                    ->label(__('Billing Reference'))
                                    ->disabled(fn() => !auth()->user()->hasRole('Project Manager')),
                            ])->columns(2)
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Sprint name'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('client.abbreviation')
                    ->label(__('Client'))
                    ->sortable()
                    ->visible(fn () => !auth()->user()->hasRole('Customer'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label(__('Sprint start date'))
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label(__('Sprint end date'))
                    ->date()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tickets_credits')
                    ->label(__('Tickets Credits'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('extra_credits')
                    ->label(__('Extra Credits'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_credits')
                    ->label(__('Total Credits'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('billed')
                    ->label(__('Billed'))
                    ->boolean()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('billing_reference')
                    ->label(__('Billing Reference'))
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('started_at')
                    ->label(__('Sprint started at'))
                    ->dateTime()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('ended_at')
                    ->label(__('Sprint ended at'))
                    ->dateTime()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('remaining')
                    ->label(__('Remaining'))
                    ->suffix(fn($record) => $record->remaining ? ' '. __('days') : '')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('name')
                    ->label(__('Sprint Name'))
                    ->multiple()
                    ->options(fn() => Sprint::all()->pluck('name', 'id')->toArray()),

                Tables\Filters\SelectFilter::make('client_id')
                    ->label(__('Client'))
                    ->multiple()
                    ->options(fn() => Client::all()->pluck('name', 'id')->toArray()),
            ])
            ->actions([
                Tables\Actions\Action::make('start')
                    ->label(__('Start sprint'))
                    ->visible(fn($record) => auth()->user()->hasRole('Project Manager') && !$record->started_at && !$record->ended_at)
                    ->requiresConfirmation()
                    ->color('success')
                    ->button()
                    ->icon('heroicon-o-play')
                    ->action(function (Sprint $record) {
                        $now = now();
                        $record->started_at = $now;
                        $record->save();
                        Filament::notify('success', __('Sprint started at').' '.$now);
                    }),
                Tables\Actions\Action::make('stop')
                    ->label(__('Stop sprint'))
                    ->visible(fn($record) => auth()->user()->hasRole('Project Manager') && $record->started_at && !$record->ended_at)
                    ->requiresConfirmation()
                    ->color('danger')
                    ->button()
                    ->icon('heroicon-o-pause')
                    ->action(function (Sprint $record) {
                        $now = now();
                        $record->ended_at = $now;
                        $record->save();
                        Filament::notify('success', __('Sprint ended at').' '.$now);
                    }),
                Tables\Actions\Action::make('tickets')
                    ->label(__('Tickets'))
                    ->color('secondary')
                    ->icon('heroicon-o-ticket')
                    ->visible(fn() => auth()->user()->hasRole('Project Manager'))
                    ->mountUsing(fn(Forms\ComponentContainer $form, Sprint $record) => $form->fill([
                        'tickets' => $record->tickets->pluck('id')->toArray()
                    ]))
                    ->modalHeading(fn($record) => $record->name.' - '.__('Associated tickets'))
                    ->form([
                        Forms\Components\CheckboxList::make('tickets')
                            ->label(__('Choose tickets to associate to this sprint'))
                            ->required()
                            ->options(function (Sprint $record) {
                                $backlog = TicketStatus::where('name', 'Backlog')->first();
                                $results = [];
                                $tickets = Ticket::where('client_id', $record->client_id)
                                    ->where(function ($query) use ($record, $backlog) {
                                        $query->where('status_id', $backlog->id)
                                            ->orWhere('sprint_id', $record->id);
                                    })->get();
                                foreach ($tickets as $ticket) {
                                    $results[$ticket->id] = new HtmlString('<div class="w-full flex justify-between items-center">'
                                        .'<span>'.$ticket->name.'</span>'
                                        .($ticket->sprint ? '<span class="text-xs font-medium '.($ticket->sprint_id == $record->id ? 'bg-gray-100 text-gray-600' : 'bg-danger-500 text-white').' px-2 py-1 rounded">'.$ticket->sprint->name.'</span>' : '')
                                        .'</div>');
                                }
                                return $results;
                            })
                    ])
                    ->action(function (Sprint $record, array $data) {
                        $tickets = $data['tickets'];
                        Ticket::where('sprint_id', $record->id)->update(['sprint_id' => null]);
                        $statusSprint = TicketStatus::where('name', 'Sprint')->first();
                        Ticket::whereIn('id', $tickets)->update(['sprint_id' => $record->id, 'status_id' => $statusSprint->id]);
                        $record->save();
                        Filament::notify('success', __('Tickets associated with sprint'));
                    }),

                Tables\Actions\Action::make('view_tickets')
                    ->label(__('Tickets'))
                    ->color('secondary')
                    ->icon('heroicon-o-ticket')
                    ->visible(fn() => !auth()->user()->hasRole('Project Manager'))
                    ->modalHeading(fn($record) => $record->name.' - '.__('Associated tickets'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('Close'))
                    ->form([
                        Forms\Components\CheckboxList::make('tickets')
                            ->label(__('Associated tickets'))
                            ->options(fn(Sprint $record) => $record->tickets->pluck('name','id')->toArray())
                            ->disabled()
                            ->dehydrated(false)
                    ]),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('id');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSprints::route('/'),
            'create' => Pages\CreateSprint::route('/create'),
            'view' => Pages\ViewSprint::route('/{record}'),
            'edit' => Pages\EditSprint::route('/{record}/edit'),
        ];
    }
}
