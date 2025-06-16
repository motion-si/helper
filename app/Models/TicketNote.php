<?php

namespace App\Models;

use App\Notifications\TicketNoted;
use App\Notifications\TicketCreated;
use App\Notifications\TicketStatusUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'ticket_id', 'content'
    ];


    public static function boot()
    {
        parent::boot();

        static::creating(function (TicketNote $item) {
            $item->user_id = $item->user_id ?? auth()->id();
        });

        static::created(function (TicketNote $item) {
            foreach ($item->ticket->watchers as $user) {
                $user->notify(new TicketNoted($item));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
    }
}
