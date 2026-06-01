<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\NotificationRepository;
use App\Repositories\PushTokenRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private const EXPO_PUSH_URL = 'https://exp.host/--/api/v2/push/send';

    public function __construct(
        private readonly NotificationRepository $notificationRepo,
        private readonly PushTokenRepository $pushTokenRepo,
    ) {}

    public function getUserNotifications(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->notificationRepo->findByUserId($userId, $perPage);
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->notificationRepo->unreadCount($userId);
    }

    public function markAsRead(int $id, int $userId): bool
    {
        return $this->notificationRepo->markAsRead($id, $userId);
    }

    public function markAllAsRead(int $userId): int
    {
        return $this->notificationRepo->markAllAsRead($userId);
    }

    public function sendNotification(
        int $userId,
        ?int $fromUserId,
        string $type,
        string $title,
        string $message,
        ?int $loadId = null,
        ?int $bookingId = null,
    ): void {
        $notification = $this->notificationRepo->create([
            'user_id' => $userId,
            'from_user_id' => $fromUserId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'load_id' => $loadId,
            'booking_id' => $bookingId,
        ]);

        $this->sendPushNotification($userId, $title, $message, [
            'load_id' => $loadId,
            'booking_id' => $bookingId,
            'type' => $type,
        ]);
    }

    public function registerToken(int $userId, string $token, string $device): void
    {
        $existing = $this->pushTokenRepo->findByToken($token);
        if ($existing) {
            if ($existing->user_id !== $userId) {
                $existing->update(['user_id' => $userId]);
            }
            return;
        }

        $this->pushTokenRepo->create([
            'user_id' => $userId,
            'token' => $token,
            'device' => $device,
        ]);
    }

    public function unregisterToken(string $token): void
    {
        $existing = $this->pushTokenRepo->findByToken($token);
        if ($existing) {
            $this->pushTokenRepo->delete($existing->id);
        }
    }

    public function unregisterAllTokens(int $userId): void
    {
        $this->pushTokenRepo->deleteByUserId($userId);
    }

    private function sendPushNotification(int $userId, string $title, string $message, array $data = []): void
    {
        $tokens = $this->pushTokenRepo->getUserTokens($userId);

        if ($tokens->isEmpty()) {
            return;
        }

        $payload = [
            'to' => $tokens->first(),
            'sound' => 'real_truck_horn.wav',
            'title' => $title,
            'body' => $message,
            'data' => $data,
        ];

        try {
            $response = Http::post(self::EXPO_PUSH_URL, $payload);

            if ($response->failed()) {
                Log::warning('Expo push notification failed', [
                    'user_id' => $userId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Expo push notification exception', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
