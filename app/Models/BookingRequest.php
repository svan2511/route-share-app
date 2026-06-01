<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRequest extends Model
{
    protected $fillable = [
        'load_id',
        'user_id',
        'owner_id',
        'pickup_city',
        'drop_city',
        'pickup_offset_minutes',
        'status',
        'goods_description',
    ];

    protected function casts(): array
    {
        return [
            'pickup_offset_minutes' => 'integer',
        ];
    }

    public function relatedLoad(): BelongsTo
    {
        return $this->belongsTo(Load::class, 'load_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
