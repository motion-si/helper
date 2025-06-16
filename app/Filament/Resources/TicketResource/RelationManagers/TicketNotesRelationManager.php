<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TicketNotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    protected static ?string $recordTitleAttribute = 'content';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\RichEditor::make('content')
                    ->label(__('Note'))
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('User'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('content')
                    ->label(__('Note'))
                    ->limit(100)
                    ->html(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn () => Auth::user()->can('Create ticket note')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Model $record) => Auth::user()->can('Update ticket note', $record) && $record->user_id === Auth::id()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (Model $record) => Auth::user()->can('Delete ticket note', $record) && $record->user_id === Auth::id()),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => Auth::user()->can('Delete ticket note')),
            ]);
    }

    protected function canCreate(): bool
    {
        return Auth::user()->can('Create ticket note');
    }

    protected function canEdit(Model $record): bool
    {
        return Auth::user()->can('Update ticket note', $record) && $record->user_id === Auth::id();
    }

    protected function canDelete(Model $record): bool
    {
        return Auth::user()->can('Delete ticket note', $record) && $record->user_id === Auth::id();
    }

    protected function canDeleteAny(): bool
    {
        return Auth::user()->can('Delete ticket note');
    }

    public static function canViewForRecord(Model $ownerRecord): bool
    {
        $user = auth()->user();

        return $user
            && !$user->hasRole('Customer')
            && $user->can('View ticket note');
    }
}
