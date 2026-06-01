<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedLoad extends Model
{
    protected $fillable = [
        'user_id',
        'load_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function savedLoad(): BelongsTo
    {
        return $this->belongsTo(Load::class, 'load_id');
    }
}
