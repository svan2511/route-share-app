<?php

namespace App\Repositories;

use App\Models\BookingRequest;
use App\Models\Load;
use Illuminate\Support\Collection;

class BookingRequestRepository
{
    public function create(array $data): BookingRequest
    {
        return BookingRequest::create($data);
    }

    public function findById(int $id): ?BookingRequest
    {
        return BookingRequest::with(['relatedLoad', 'user', 'owner'])->find($id);
    }

    public function getUserRequests(int $userId): Collection
    {
        return BookingRequest::with(['relatedLoad.route', 'relatedLoad.user', 'user', 'owner'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getOwnerRequests(int $ownerId): Collection
    {
        return BookingRequest::with(['relatedLoad.route', 'relatedLoad.user', 'user', 'owner'])
            ->where('owner_id', $ownerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getLoadRequests(int $loadId): Collection
    {
        return BookingRequest::with(['user'])
            ->where('load_id', $loadId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findPendingByUserAndLoad(int $userId, int $loadId): ?BookingRequest
    {
        return BookingRequest::where('user_id', $userId)
            ->where('load_id', $loadId)
            ->whereIn('status', ['pending', 'accepted'])
            ->first();
    }

    public function findRejectedByUserAndLoad(int $userId, int $loadId): bool
    {
        return BookingRequest::where('user_id', $userId)
            ->where('load_id', $loadId)
            ->where('status', 'rejected')
            ->exists();
    }

    public function accept(BookingRequest $booking): bool
    {
        if ($booking->status !== 'pending') {
            return false;
        }

        $load = $booking->relatedLoad;
        if (!$load || $load->available_space <= 0) {
            return false;
        }

        $booking->update(['status' => 'accepted']);
        $load->update(['available_space' => 0]);
        return true;
    }

    public function reject(BookingRequest $booking): bool
    {
        if ($booking->status !== 'pending') {
            return false;
        }
        $booking->update(['status' => 'rejected']);
        return true;
    }

    public function cancel(BookingRequest $booking, ?int $userId = null): bool
    {
        if (!in_array($booking->status, ['pending', 'accepted'])) {
            return false;
        }

        $isOwner = $userId && (int) $booking->owner_id === $userId;
        $wasAccepted = $booking->status === 'accepted';

        if ($isOwner && $wasAccepted) {
            $booking->update(['status' => 'pending']);
        } else {
            $booking->update(['status' => 'cancelled']);
        }

        if ($wasAccepted) {
            $load = $booking->relatedLoad;
            if ($load) {
                $load->update(['available_space' => (int) ($booking->space_snapshot ?? $load->available_space)]);
            }
        }
        return true;
    }
}
