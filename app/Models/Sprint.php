<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sprint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'starts_at',
        'ends_at',
        'description',
        'client_id',
        'tickets_credits',
        'extra_credits',
        'total_credits',
        'billed',
        'billing_reference',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'billed' => 'boolean',
        'billing_reference' => 'date',
    ];

    protected static function booted()
    {
        static::saving(function (Sprint $item) {
            $item->tickets_credits = $item->tickets()->sum('credits');
            $item->total_credits = ($item->tickets_credits ?? 0) + ($item->extra_credits ?? 0);
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function scopeAccessibleBy($query, User $user)
    {
        $clientIds = $user->clients()->pluck('clients.id')->toArray();
        return $query->whereIn('client_id', $clientIds);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'sprint_id', 'id');
    }

    public function remaining(): Attribute
    {
        return new Attribute(
            get: function () {
                if ($this->starts_at && $this->ends_at && $this->started_at && !$this->ended_at) {
                    return $this->ends_at->diffInDays(now()) + 1;
                }
                return null;
            }
        );
    }
}
