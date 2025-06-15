<?php

namespace App\Notifications;

use App\Models\TicketNote;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketNoted extends Notification implements ShouldQueue
{
    use Queueable;

    private TicketNote $ticketNote;

    /**
     * Create a new notification instance.
     *
     * @param TicketNote $ticket
     * @return void
     */
    public function __construct(TicketNote $ticketNote)
    {
        $this->ticketNote = $ticketNote;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line(
                __(
                    'A new note has been added to the ticket :ticket by :name.',
                    [
                        'ticket' => $this->ticketNote->ticket->name,
                        'name' => $this->ticketNote->user->name
                    ]
                )
            )
            ->line(__('See more details of this ticket by clicking on the button below:'))
            ->action(
                __('View details'),
                route('filament.resources.tickets.share', $this->ticketNote->ticket->code)
            );
    }

    public function toDatabase(User $notifiable): array
    {
        return FilamentNotification::make()
            ->title(
                __(
                    'Ticket :ticket noted',
                    [
                        'ticket' => $this->ticketNote->ticket->name
                    ]
                )
            )
            ->icon('heroicon-o-ticket')
            ->body(fn() => __('by :name', ['name' => $this->ticketNote->user->name]))
            ->actions([
                Action::make('view')
                    ->link()
                    ->icon('heroicon-s-eye')
                    ->url(fn() => route('filament.resources.tickets.share', $this->ticketNote->ticket->code)),
            ])
            ->getDatabaseMessage();
    }
}
