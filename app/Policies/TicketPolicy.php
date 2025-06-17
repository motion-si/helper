<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->can('List tickets');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Ticket $ticket)
    {
        if ($user->hasRole('Customer')) {
            return $user->can('View ticket') && (
                $ticket->owner_id === $user->id
                || $user->belongsToClient($ticket->client_id)
            );
        }

        if ($ticket->project && ($ticket->project->owner_id === $user->id || $user->belongsToClient($ticket->project->client_id))) {
            return $user->can('View ticket');
        }

        return $user->can('View ticket')
            && (
                $ticket->owner_id === $user->id
                || $ticket->responsible_id === $user->id
                || $ticket->developer_id === $user->id
            );
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Client association will be handled in the Resource/Controller layer
        return $user->can('Create ticket');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Ticket $ticket)
    {
        if ($user->hasRole('Customer')) {
            return false;
        }

        if ($ticket->project && ($ticket->project->owner_id === $user->id || $user->belongsToClient($ticket->project->client_id))) {
            return $user->can('Update ticket');
        }

        return $user->can('Update ticket')
            && (
                $ticket->owner_id === $user->id
                || $ticket->responsible_id === $user->id
                || $ticket->developer_id === $user->id
            );
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Ticket $ticket)
    {
        return $user->can('Delete ticket');
    }

    // TicketNote Policies
    public function viewTicketNote(User $user, Ticket $ticket)
    {
        if ($user->hasRole('Customer')) {
            return false; // Customers cannot view ticket notes
        }
        return $user->can('View ticket note');
    }

    public function createTicketNote(User $user, Ticket $ticket)
    {
        if ($user->hasRole('Customer')) {
            return false; // Customers cannot create ticket notes
        }
        return $user->can('Create ticket note');
    }

    public function updateTicketNote(User $user, Ticket $ticket, $ticketNote)
    {
        if ($user->hasRole('Customer')) {
            return false;
        }
        return $user->can('Update ticket note') && $ticketNote->user_id === $user->id;
    }

    public function deleteTicketNote(User $user, Ticket $ticket, $ticketNote)
    {
        if ($user->hasRole('Customer')) {
            return false;
        }
        return $user->can('Delete ticket note') && $ticketNote->user_id === $user->id;
    }

    // TicketComment Policies
    public function viewTicketComment(User $user, Ticket $ticket)
    {
        return $user->can('View ticket comment');
    }

    public function createTicketComment(User $user, Ticket $ticket)
    {
        if ($user->hasRole('Developer')) {
            return false; // Developers cannot create comments
        }
        return $user->can('Create ticket comment');
    }

    public function updateTicketComment(User $user, Ticket $ticket, $ticketComment)
    {
        return false; // No one can update comments
    }

    public function deleteTicketComment(User $user, Ticket $ticket, $ticketComment)
    {
        return false; // No one can delete comments
    }
}
