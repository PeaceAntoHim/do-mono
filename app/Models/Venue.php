<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    // Optional: define table name if it doesn't follow Laravel convention (plural lowercase)
//     protected $table = 'venue';

    protected $fillable = [
        'name',
        'address',
        'capacity',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    // Relationship: Venue has many Events
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'venue_id', 'id');
    }
}
