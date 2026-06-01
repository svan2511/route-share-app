<?php

namespace App\Repositories;

use App\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationRepository
{
    public function findByUserId(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return Notification::with('fromUser')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(min($perPage, 50));
    }

    public function create(array $data): Notification
    {
        return Notification::create($data);
    }

    public function markAsRead(int $id, int $userId): bool
    {
        return Notification::where('id', $id)
            ->where('user_id', $userId)
            ->update(['is_read' => true]) > 0;
    }

    public function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function unreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }
}
