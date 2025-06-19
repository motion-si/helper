<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Sprint;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Project extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name', 'description', 'status_id', 'owner_id', 'ticket_prefix',
        'status_type', 'type', 'client_id'
    ];

    protected $appends = [
        'cover'
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ProjectStatus::class, 'status_id', 'id')->withTrashed();
    }


    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'project_id', 'id');
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(TicketStatus::class, 'project_id', 'id');
    }


    public function scopeAccessibleBy($query, User $user)
    {
        $clientIds = $user->clients()->pluck('clients.id')->toArray();
        return $query->where('owner_id', $user->id)
            ->orWhereIn('client_id', $clientIds);
    }



    public function contributors(): Attribute
    {
        return new Attribute(
            get: function () {
                return collect([$this->owner]);
            }
        );
    }

    public function cover(): Attribute
    {
        return new Attribute(
            get: fn() => $this->media('cover')?->first()?->getFullUrl()
                ??
                'https://ui-avatars.com/api/?background=3f84f3&color=ffffff&name=' . $this->name
        );
    }

    public function currentSprint(): Attribute
    {
        return new Attribute(
            get: function () {
                return Sprint::whereHas('tickets', function ($query) {
                        $query->where('project_id', $this->id);
                    })
                    ->whereNotNull('started_at')
                    ->whereNull('ended_at')
                    ->orderBy('starts_at')
                    ->first();
            }
        );
    }

    public function nextSprint(): Attribute
    {
        return new Attribute(
            get: function () {
                $current = $this->currentSprint;

                $query = Sprint::whereHas('tickets', function ($query) {
                        $query->where('project_id', $this->id);
                    })
                    ->whereNull('started_at')
                    ->whereNull('ended_at');

                if ($current) {
                    $query->where('starts_at', '>=', $current->ends_at);
                }

                return $query->orderBy('starts_at')->first();
            }
        );
    }
}
