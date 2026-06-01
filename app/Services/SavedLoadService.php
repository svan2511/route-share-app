<?php

namespace App\Services;

use App\Models\Load;
use App\Models\User;
use App\Repositories\SavedLoadRepository;
use Illuminate\Database\Eloquent\Collection;

class SavedLoadService
{
    public function __construct(
        private readonly SavedLoadRepository $savedLoadRepository,
    ) {}

    public function getUserSavedLoads(User $user): Collection
    {
        return $this->savedLoadRepository->getUserSavedLoads($user->id);
    }

    public function toggleSave(User $user, Load $load): array
    {
        if ($this->savedLoadRepository->isSaved($user->id, $load->id)) {
            $this->savedLoadRepository->unsave($user->id, $load->id);
            return ['saved' => false, 'message' => 'Load removed from saved.'];
        }

        $this->savedLoadRepository->save($user->id, $load->id);
        return ['saved' => true, 'message' => 'Load saved successfully.'];
    }
}
