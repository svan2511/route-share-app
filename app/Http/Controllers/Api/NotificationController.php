<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterPushTokenRequest;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);
        $notifications = $this->notificationService->getUserNotifications(
            $request->user()->id,
            min($perPage, 50),
        );

        return response()->json([
            'success' => true,
            'data' => NotificationResource::collection($notifications),
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => ['count' => $count],
        ]);
    }

    public function markRead(int $id, Request $request): JsonResponse
    {
        $marked = $this->notificationService->markAsRead($id, $request->user()->id);

        if (!$marked) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], 404);
        }

        return response()->json(['success' => true, 'message' => 'Marked as read.']);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => $count > 0
                ? "{$count} notifications marked as read."
                : 'No unread notifications.',
        ]);
    }

    public function registerToken(RegisterPushTokenRequest $request): JsonResponse
    {
        $this->notificationService->registerToken(
            $request->user()->id,
            $request->input('token'),
            $request->input('device'),
        );

        return response()->json(['success' => true, 'message' => 'Push token registered.']);
    }

    public function unregisterToken(Request $request): JsonResponse
    {
        $this->notificationService->unregisterAllTokens($request->user()->id);

        return response()->json(['success' => true, 'message' => 'Push tokens removed.']);
    }
}
