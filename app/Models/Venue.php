<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'capacity',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'website',
    ];

    /**
     * @return HasMany<Event, Venue>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
