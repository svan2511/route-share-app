<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    protected $fillable = [
        'user_id',
        'route_name',
        'from_city',
        'to_city',
        'destination_offset_minutes',
    ];

    public function stops(): HasMany
    {
        return $this->hasMany(RouteStop::class)->orderBy('stop_order');
    }

    public function loads(): HasMany
    {
        return $this->hasMany(Load::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
