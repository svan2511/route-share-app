<?php

namespace App\Repositories;

use App\Models\OtpCode;

class OtpRepository
{
    public function create(string $phone, string $otp, \DateTimeInterface $expiresAt): OtpCode
    {
        return OtpCode::create([
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);
    }

    public function findValid(string $phone, string $otp): ?OtpCode
    {
        return OtpCode::where('phone', $phone)
            ->where('otp', $otp)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    public function invalidatePrevious(string $phone): void
    {
        OtpCode::where('phone', $phone)->delete();
    }
}
