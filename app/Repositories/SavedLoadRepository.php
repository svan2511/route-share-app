<?php

namespace App\Repositories;

use App\Models\SavedLoad;
use Illuminate\Database\Eloquent\Collection;

class SavedLoadRepository
{
    public function getUserSavedLoads(int $userId): Collection
    {
        return SavedLoad::with('savedLoad.user')
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function isSaved(int $userId, int $loadId): bool
    {
        return SavedLoad::where('user_id', $userId)
            ->where('load_id', $loadId)
            ->exists();
    }

    public function save(int $userId, int $loadId): SavedLoad
    {
        return SavedLoad::create([
            'user_id' => $userId,
            'load_id' => $loadId,
        ]);
    }

    public function unsave(int $userId, int $loadId): bool
    {
        return SavedLoad::where('user_id', $userId)
            ->where('load_id', $loadId)
            ->delete() > 0;
    }
}
