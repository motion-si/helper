<?php

namespace App\Filament\Resources\TicketResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $recordTitleAttribute = 'content';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\RichEditor::make('content')
                    ->label(__('Comment'))
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
                    ->label(__('Comment'))
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
                    ->visible(fn () => !Auth::user()->hasRole('Developer') && Auth::user()->can('Create ticket comment')),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }

    protected function canCreate(): bool
    {
        $user = Auth::user();
        return !$user->hasRole('Developer') && $user->can('Create ticket comment');
    }

    protected function canEdit(Model $record): bool
    {
        return false; // Is not allowed to edit comments
    }

    protected function canDelete(Model $record): bool
    {
        return false; // Is not allowed to delete comments
    }

    protected function canDeleteAny(): bool
    {
        return false; // Is not allowed to delete any comments
    }

    public static function canViewForRecord(Model $ownerRecord): bool
    {
        return auth()->user()?->can('View ticket comment') ?? false;
    }
}
