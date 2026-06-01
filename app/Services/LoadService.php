<?php

namespace App\Services;

use App\DTOs\StoreLoadDTO;
use App\Models\Load;
use App\Models\User;
use App\Repositories\LoadRepository;
use App\Repositories\RouteRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class LoadService
{
    public function __construct(
        private readonly LoadRepository $loadRepository,
        private readonly RouteRepository $routeRepository,
    ) {}

    public function getFeed(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->loadRepository->paginateActive($filters, $perPage);
    }

    public function getLoad(int $id): ?Load
    {
        return $this->loadRepository->findById($id);
    }

    public function createLoad(User $user, StoreLoadDTO $dto): Load
    {
        $route = $dto->route_id
            ? $this->routeRepository->findByIds([$dto->route_id])->first()
            : $this->routeRepository->findByCities($dto->from_city, $dto->to_city);

        $expiresAt = $this->calculateExpiresAt(
            $dto->departure_date,
            $dto->departure_time,
            $route
        );

        return $this->loadRepository->create([
            'user_id' => $user->id,
            'route_id' => $route?->id,
            'from_city' => $dto->from_city,
            'to_city' => $dto->to_city,
            'vehicle_type' => $dto->vehicle_type,
            'available_space' => $dto->available_space,
            'departure_date' => $dto->departure_date,
            'departure_time' => $dto->departure_time,
            'notes' => $dto->notes,
            'phone' => $dto->phone,
            'status' => 'active',
            'expires_at' => $expiresAt,
            'destination_stop_id' => $dto->destination_stop_id,
        ]);
    }

    public function updateLoad(Load $load, StoreLoadDTO $dto): bool
    {
        $route = $dto->route_id
            ? $this->routeRepository->findByIds([$dto->route_id])->first()
            : $this->routeRepository->findByCities($dto->from_city, $dto->to_city);

        $expiresAt = $this->calculateExpiresAt(
            $dto->departure_date,
            $dto->departure_time,
            $route
        );

        return $this->loadRepository->update($load, [
            'route_id' => $route?->id,
            'from_city' => $dto->from_city,
            'to_city' => $dto->to_city,
            'vehicle_type' => $dto->vehicle_type,
            'available_space' => $dto->available_space,
            'departure_date' => $dto->departure_date,
            'departure_time' => $dto->departure_time,
            'notes' => $dto->notes,
            'phone' => $dto->phone,
            'expires_at' => $expiresAt,
            'destination_stop_id' => $dto->destination_stop_id,
        ]);
    }

    public function deleteLoad(Load $load): bool
    {
        return $this->loadRepository->delete($load);
    }

    public function markAsCompleted(Load $load): bool
    {
        return $this->loadRepository->update($load, ['status' => 'completed']);
    }

    public function markAsCancelled(Load $load): bool
    {
        return $this->loadRepository->update($load, ['status' => 'cancelled']);
    }

    public function getUserLoads(User $user): array
    {
        return $this->loadRepository->getUserLoadsGrouped($user->id);
    }

    public function canModify(User $user, Load $load): bool
    {
        return $user->id === $load->user_id;
    }

    private function calculateExpiresAt(string $departureDate, string $departureTime, ?\App\Models\Route $route): \Carbon\Carbon
    {
        $departureDatetime = \Carbon\Carbon::parse($departureDate . ' ' . $departureTime);

        if ($route && $route->stops->isNotEmpty()) {
            $maxStopOffset = $route->stops->max('time_offset_minutes') ?? 0;
            $destOffset = $route->destination_offset_minutes ?? 0;
            $maxOffset = max($maxStopOffset, $destOffset);
            return $departureDatetime->copy()->addMinutes((int) $maxOffset);
        }

        $maxExpiry = now()->addHours(24);
        return $departureDatetime->lessThan($maxExpiry) ? $departureDatetime : $maxExpiry;
    }
}
