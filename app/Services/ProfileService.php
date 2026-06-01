<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    public function getProfile(User $user): User
    {
        return $user->loadCount('loads');
    }

    public function updateProfile(User $user, array $data): User
    {
        if (!empty($data)) {
            $this->userRepository->update($user, $data);
            $user->refresh();
        }

        return $user;
    }
}
