<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
class Event extends Model
{
//    protected $table = 'Event'; // Ensure this matches your actual DB table
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'description',
        'date',
        'venue_id',
        'status',
        'thumbnail_url',
    ];

    protected $casts = [
        'date' => 'datetime',
        'created_at' => 'datetime',
        'is_private' => 'boolean',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Automatically generate uuid if not set
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });

        static::deleting(function ($event) {
            // Delete related banners
            $event->banners()->delete();

            // Delete related ticket types
            $event->ticketTypes()->each(function ($ticketType) {
                $ticketType->orderItems()->delete();
                $ticketType->delete();
            });
        });
    }

    // Relationship: Event belongs to Venue
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class, 'venue_id', 'id');
    }

    // Relationship: Event has many TicketTypes
    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class, 'event_id', 'id');
    }

    // Relationship: Event has many Banners
    public function banners(): HasMany
    {
        return $this->hasMany(Banner::class, 'event_id', 'id');
    }
}
