<?php

namespace App\Models;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'ticket_id', 'value', 'comment', 'activity_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id', 'id');
    }

    public function forHumans(): Attribute
    {
        return new Attribute(
            get: function () {
                if (!$this->value) {
                    return null;
                }

                // TIME (HH:MM:SS)
                if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $this->value)) {
                    $seconds = Carbon::createFromFormat('H:i:s', $this->value)->secondsSinceMidnight();
                    return CarbonInterval::seconds($seconds)->cascade()->forHumans();
                }

                return null; // Format unexpected
            }
        );
    }
}
