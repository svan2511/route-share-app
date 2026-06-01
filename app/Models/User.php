<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::deleted(function (User $user) {
            $user->tokens()->delete();
        });
    }

    protected $fillable = [
        'full_name',
        'business_name',
        'business_logo',
        'phone',
        'city',
        'address',
        'market_type',
    ];

    protected $appends = [
        'business_logo_url',
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'business_logo' => 'string',
        ];
    }

    public function getBusinessLogoUrlAttribute(): ?string
    {
        if (!$this->business_logo) {
            return null;
        }

        if (str_starts_with($this->business_logo, 'http')) {
            return $this->business_logo;
        }

        return url(Storage::url($this->business_logo));
    }

    public function loads(): HasMany
    {
        return $this->hasMany(Load::class);
    }

    public function savedLoads(): HasMany
    {
        return $this->hasMany(SavedLoad::class);
    }
}
