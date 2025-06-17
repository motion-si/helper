<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use App\Models\Ticket;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        if ($user->hasRole('Customer') && $user->client_id) {
            $data['client_id'] = $user->client_id;
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $sendEmail = Arr::pull($data, 'send_email', true);
        $ticket = new Ticket($data);
        $ticket->sendEmail = (bool) $sendEmail;
        $ticket->save();

        return $ticket;
    }
}
