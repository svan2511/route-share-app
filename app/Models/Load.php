<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Load extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'route_id',
        'from_city',
        'to_city',
        'vehicle_type',
        'available_space',
        'departure_date',
        'departure_time',
        'notes',
        'phone',
        'status',
        'expires_at',
        'destination_stop_id',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'departure_time' => 'string',
            'expires_at' => 'datetime',
            'available_space' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function bookingRequests(): HasMany
    {
        return $this->hasMany(BookingRequest::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }
}
