<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingRequestResource;
use App\Models\Load;
use App\Repositories\BookingRequestRepository;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingRequestController extends Controller
{
    public function __construct(
        private BookingRequestRepository $bookingRepo,
        private NotificationService $notificationService,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'load_id' => 'required|exists:loads,id',
            'pickup_city' => 'required|string|max:255',
            'drop_city' => 'required|string|max:255',
            'pickup_offset_minutes' => 'required|integer|min:0',
            'goods_description' => 'nullable|string|max:500',
        ]);

        $load = Load::findOrFail($data['load_id']);

        if ($load->user_id === (int) $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'You cannot book your own load.'], 422);
        }

        $existing = $this->bookingRepo->findPendingByUserAndLoad($request->user()->id, $data['load_id']);
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'You have already requested this load.'], 422);
        }

        $data['user_id'] = $request->user()->id;
        $data['owner_id'] = $load->user_id;

        $booking = $this->bookingRepo->create($data);

        $this->notificationService->sendNotification(
            userId: $load->user_id,
            fromUserId: $request->user()->id,
            type: 'request_sent',
            title: 'New Booking Request',
            message: $request->user()->business_name ?? $request->user()->full_name
                . ' wants to book your load from ' . $load->from_city . ' to ' . $load->to_city,
            loadId: $load->id,
            bookingId: $booking->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'Booking request sent.',
            'data' => new BookingRequestResource($booking->load(['relatedLoad.route', 'relatedLoad.user', 'user', 'owner'])),
        ], 201);
    }

    public function myRequests(Request $request): JsonResponse
    {
        $bookings = $this->bookingRepo->getUserRequests($request->user()->id);
        return response()->json(['success' => true, 'data' => BookingRequestResource::collection($bookings)]);
    }

    public function receivedRequests(Request $request): JsonResponse
    {
        $bookings = $this->bookingRepo->getOwnerRequests($request->user()->id);
        return response()->json(['success' => true, 'data' => BookingRequestResource::collection($bookings)]);
    }

    public function accept(int $id, Request $request): JsonResponse
    {
        $booking = $this->bookingRepo->findById($id);
        if (!$booking || $booking->owner_id !== (int) $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Not found.'], 404);
        }

        if (!$this->bookingRepo->accept($booking)) {
            return response()->json(['success' => false, 'message' => 'Cannot accept this request.'], 422);
        }

        $load = $booking->relatedLoad;

        $this->notificationService->sendNotification(
            userId: $booking->user_id,
            fromUserId: $request->user()->id,
            type: 'request_accepted',
            title: 'Booking Accepted',
            message: ($request->user()->business_name ?? $request->user()->full_name)
                . ' accepted your booking request for ' . ($load->from_city ?? '') . ' to ' . ($load->to_city ?? ''),
            loadId: $load?->id,
            bookingId: $booking->id,
        );

        return response()->json(['success' => true, 'message' => 'Booking accepted.', 'data' => new BookingRequestResource($booking)]);
    }

    public function reject(int $id, Request $request): JsonResponse
    {
        $booking = $this->bookingRepo->findById($id);
        if (!$booking || $booking->owner_id !== (int) $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Not found.'], 404);
        }

        if (!$this->bookingRepo->reject($booking)) {
            return response()->json(['success' => false, 'message' => 'Cannot reject this request.'], 422);
        }

        $load = $booking->relatedLoad;

        $this->notificationService->sendNotification(
            userId: $booking->user_id,
            fromUserId: $request->user()->id,
            type: 'request_rejected',
            title: 'Booking Rejected',
            message: ($request->user()->business_name ?? $request->user()->full_name)
                . ' rejected your booking request for ' . ($load->from_city ?? '') . ' to ' . ($load->to_city ?? ''),
            loadId: $load?->id,
            bookingId: $booking->id,
        );

        return response()->json(['success' => true, 'message' => 'Booking rejected.']);
    }

    public function cancel(int $id, Request $request): JsonResponse
    {
        $booking = $this->bookingRepo->findById($id);
        if (!$booking || ($booking->user_id !== (int) $request->user()->id && $booking->owner_id !== (int) $request->user()->id)) {
            return response()->json(['success' => false, 'message' => 'Not found.'], 404);
        }

        $cancelledBy = (int) $request->user()->id;

        if (!$this->bookingRepo->cancel($booking, $cancelledBy)) {
            return response()->json(['success' => false, 'message' => 'Cannot cancel this request.'], 422);
        }

        $load = $booking->relatedLoad;
        $isOwner = $booking->owner_id === $cancelledBy;
        $cancellerName = $request->user()->business_name ?? $request->user()->full_name;

        $notifyUserId = $isOwner ? $booking->user_id : $booking->owner_id;

        $this->notificationService->sendNotification(
            userId: $notifyUserId,
            fromUserId: $cancelledBy,
            type: 'request_cancelled',
            title: 'Booking Cancelled',
            message: $cancellerName . ' cancelled the booking for '
                . ($load->from_city ?? '') . ' to ' . ($load->to_city ?? ''),
            loadId: $load?->id,
            bookingId: $booking->id,
        );

        return response()->json(['success' => true, 'message' => 'Booking cancelled.']);
    }
}
