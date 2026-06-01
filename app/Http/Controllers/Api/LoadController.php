<?php

namespace App\Http\Controllers\Api;

use App\DTOs\StoreLoadDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLoadRequest;
use App\Http\Resources\LoadCollection;
use App\Http\Resources\LoadResource;
use App\Models\BookingRequest;
use App\Models\Load;
use App\Repositories\BookingRequestRepository;
use App\Services\LoadService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoadController extends Controller
{
    public function __construct(
        private readonly LoadService $loadService,
        private readonly BookingRequestRepository $bookingRepo,
        private readonly NotificationService $notificationService,
    ) {}

    public function index(Request $request): LoadCollection
    {
        $filters = $request->only(['from_city', 'to_city', 'vehicle_type', 'exclude_user_id']);
        $perPage = (int) $request->input('per_page', 20);

        $loads = $this->loadService->getFeed($filters, min($perPage, 50));

        return new LoadCollection($loads);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $load = $this->loadService->getLoad($id);

        if (!$load) {
            return response()->json([
                'success' => false,
                'message' => 'Load not found.',
            ], 404);
        }

        $hasRequested = false;
        if ($request->user()) {
            $hasRequested = BookingRequest::where('user_id', $request->user()->id)
                ->where('load_id', $id)
                ->whereIn('status', ['pending', 'accepted'])
                ->exists();
        }

        return response()->json([
            'success' => true,
            'data' => new LoadResource($load),
            'has_requested' => $hasRequested,
        ]);
    }

    public function store(StoreLoadRequest $request): JsonResponse
    {
        $dto = StoreLoadDTO::fromRequest($request->validated());

        $load = $this->loadService->createLoad($request->user(), $dto);

        return response()->json([
            'success' => true,
            'message' => 'Load created successfully.',
            'data' => new LoadResource($load->load(['route', 'user'])),
        ], 201);
    }

    public function update(StoreLoadRequest $request, Load $load): JsonResponse
    {
        if (!$this->loadService->canModify($request->user(), $load)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to edit this load.',
            ], 403);
        }

        if ($load->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Only active loads can be edited.',
            ], 422);
        }

        $dto = StoreLoadDTO::fromRequest($request->validated());
        $this->loadService->updateLoad($load, $dto);

        return response()->json([
            'success' => true,
            'message' => 'Load updated successfully.',
            'data' => new LoadResource($load->fresh()->load(['route', 'user'])),
        ]);
    }

    public function destroy(Request $request, Load $load): JsonResponse
    {
        if (!$this->loadService->canModify($request->user(), $load)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this load.',
            ], 403);
        }

        $this->loadService->deleteLoad($load);

        return response()->json([
            'success' => true,
            'message' => 'Load deleted successfully.',
        ]);
    }

    public function complete(Request $request, Load $load): JsonResponse
    {
        if (!$this->loadService->canModify($request->user(), $load)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $this->loadService->markAsCompleted($load);

        return response()->json([
            'success' => true,
            'message' => 'Load marked as completed.',
            'data' => new LoadResource($load->fresh()),
        ]);
    }

    public function myLoads(Request $request): JsonResponse
    {
        $loads = $this->loadService->getUserLoads($request->user());

        return response()->json([
            'success' => true,
            'data' => [
                'active' => LoadResource::collection($loads['active']),
                'completed' => LoadResource::collection($loads['completed']),
                'expired' => LoadResource::collection($loads['expired']),
            ],
        ]);
    }

    public function cancel(Request $request, Load $load): JsonResponse
    {
        if (!$this->loadService->canModify($request->user(), $load)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if ($load->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Completed rides cannot be cancelled.',
            ], 422);
        }

        $this->loadService->markAsCancelled($load);

        $bookings = $this->bookingRepo->getLoadRequests($load->id);
        foreach ($bookings as $booking) {
            $this->notificationService->sendNotification(
                userId: $booking->user_id,
                fromUserId: $request->user()->id,
                type: 'ride_cancelled',
                title: 'Ride Cancelled',
                message: ($request->user()->business_name ?? $request->user()->full_name)
                    . ' cancelled their ride from ' . ($load->from_city ?? '') . ' to ' . ($load->to_city ?? ''),
                loadId: $load->id,
                bookingId: $booking->id,
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Ride cancelled successfully.',
        ]);
    }
}
