<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\OtpRepository;
use App\Repositories\UserRepository;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly OtpRepository $otpRepository,
    ) {}

    public function sendOtp(string $phone): void
    {
        $user = $this->userRepository->findByPhone($phone);

        if (!$user) {
            $user = $this->userRepository->create(['phone' => $phone]);
        }

        $this->otpRepository->invalidatePrevious($phone);

        $otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $this->otpRepository->create($phone, $otp, now()->addMinutes(10));

        logger()->info("OTP for {$phone}: {$otp}");
    }

    public function verifyOtp(string $phone, string $otp): array
    {
        $otpRecord = $this->otpRepository->findValid($phone, $otp);

        if (!$otpRecord) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP.'],
            ]);
        }

        $this->otpRepository->invalidatePrevious($phone);

        $user = $this->userRepository->findByPhone($phone);

        if (!$user) {
            throw ValidationException::withMessages([
                'phone' => ['User not found.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function register(array $data): array
    {
        if ($this->userRepository->findByPhone($data['phone'])) {
            throw ValidationException::withMessages([
                'phone' => ['This phone number is already registered.'],
            ]);
        }

        $user = $this->userRepository->create([
            'phone' => $data['phone'],
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
