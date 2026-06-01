<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteStop extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'route_id',
        'stop_name',
        'stop_order',
        'time_offset_minutes',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }
}
