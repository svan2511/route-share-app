<?php

namespace App\Repositories;

use App\Models\PushToken;
use Illuminate\Support\Collection;

class PushTokenRepository
{
    public function findByUserId(int $userId): Collection
    {
        return PushToken::where('user_id', $userId)->get();
    }

    public function findByToken(string $token): ?PushToken
    {
        return PushToken::where('token', $token)->first();
    }

    public function create(array $data): PushToken
    {
        return PushToken::create($data);
    }

    public function delete(int $id): bool
    {
        return PushToken::destroy($id) > 0;
    }

    public function deleteByUserId(int $userId): int
    {
        return PushToken::where('user_id', $userId)->delete();
    }

    public function getUserTokens(int $userId): Collection
    {
        return PushToken::where('user_id', $userId)->pluck('token');
    }
}
