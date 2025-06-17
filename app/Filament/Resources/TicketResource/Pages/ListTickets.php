<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $clientIds = auth()->user()->clients()->pluck('clients.id');
        return parent::getTableQuery()
            ->where(function ($query) use ($clientIds) {
                return $query->where('owner_id', auth()->user()->id)
                    ->orWhere('responsible_id', auth()->user()->id)
                    ->orWhere('developer_id', auth()->user()->id)
                    ->orWhereHas('project', function ($query) use ($clientIds) {
                        return $query->where('owner_id', auth()->user()->id)
                            ->orWhereIn('client_id', $clientIds);
                    });
            });
    }
}
