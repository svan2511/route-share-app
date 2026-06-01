<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $table = 'app_notifications';

    protected $fillable = [
        'user_id',
        'from_user_id',
        'type',
        'title',
        'message',
        'load_id',
        'booking_id',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function relatedLoad(): BelongsTo
    {
        return $this->belongsTo(Load::class, 'load_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(BookingRequest::class, 'booking_id');
    }
}
