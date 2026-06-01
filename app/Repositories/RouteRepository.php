<?php

namespace App\Repositories;

use App\Models\Route;
use App\Models\RouteStop;
use Illuminate\Database\Eloquent\Collection;

class RouteRepository
{
    public function findByCities(string $fromCity, string $toCity): ?Route
    {
        return Route::where('from_city', $fromCity)
            ->where('to_city', $toCity)
            ->with('stops')
            ->first();
    }

    public function findRouteContainingCities(array $cityNames): Collection
    {
        return Route::whereHas('stops', function ($q) use ($cityNames) {
            $q->whereIn('stop_name', $cityNames);
        })->with('stops')->get();
    }

    public function findRoutesCoveringSegment(string $fromCity, string $toCity): Collection
    {
        return Route::where(function ($q) use ($fromCity) {
                $q->where('from_city', $fromCity)
                  ->orWhereHas('stops', fn($q) => $q->where('stop_name', $fromCity));
            })
            ->where(function ($q) use ($toCity) {
                $q->where('to_city', $toCity)
                  ->orWhereHas('stops', fn($q) => $q->where('stop_name', $toCity));
            })
            ->with('stops')
            ->get()
            ->filter(function ($route) use ($fromCity, $toCity) {
                $maxOrder = $route->stops->max('stop_order') ?? 0;
                $fromOrder = null;
                $toOrder = null;

                foreach ($route->stops as $stop) {
                    if ($stop->stop_name === $fromCity) {
                        $fromOrder = $stop->stop_order;
                    }
                    if ($stop->stop_name === $toCity) {
                        $toOrder = $stop->stop_order;
                    }
                }

                if ($fromOrder === null && $route->from_city === $fromCity) {
                    $fromOrder = 0;
                }
                if ($toOrder === null && $route->to_city === $toCity) {
                    $toOrder = $maxOrder + 1;
                }

                return $fromOrder !== null && $toOrder !== null && $fromOrder < $toOrder;
            })
            ->values();
    }

    public function findByIds(array $ids): Collection
    {
        return Route::whereIn('id', $ids)->with('stops')->get();
    }

    public function getAll(array $filters = []): Collection
    {
        $query = Route::with('stops');

        if (!empty($filters['from_city'])) {
            $query->where('from_city', $filters['from_city']);
        }

        if (!empty($filters['to_city'])) {
            $query->where('to_city', $filters['to_city']);
        }

        return $query->get()
            ->filter(function ($route) use ($filters) {
                if (empty($filters['from_city']) || empty($filters['to_city'])) {
                    return true;
                }
                $fromOrder = null;
                $toOrder = null;
                foreach ($route->stops as $stop) {
                    if ($stop->stop_name === $filters['from_city']) {
                        $fromOrder = $stop->stop_order;
                    }
                    if ($stop->stop_name === $filters['to_city']) {
                        $toOrder = $stop->stop_order;
                    }
                }
                return $fromOrder !== null && $toOrder !== null && $fromOrder < $toOrder;
            })
            ->values();
    }

    public function getAllCities(): array
    {
        $routeCities = Route::select('from_city as city')
            ->union(Route::select('to_city as city'))
            ->pluck('city');

        $stopCities = RouteStop::select('stop_name as city')
            ->pluck('city');

        return $routeCities
            ->merge($stopCities)
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    public function create(array $data): Route
    {
        return Route::create($data);
    }

    public function addStop(Route $route, string $stopName, int $order, int $timeOffsetMinutes): RouteStop
    {
        return $route->stops()->create([
            'stop_name' => $stopName,
            'stop_order' => $order,
            'time_offset_minutes' => $timeOffsetMinutes,
        ]);
    }

    public function findById(int $id): ?Route
    {
        return Route::with('stops')->find($id);
    }

    public function findUserRoutes(int $userId): Collection
    {
        return Route::where('user_id', $userId)
            ->with('stops')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findUserRoute(int $id, int $userId): ?Route
    {
        return Route::where('id', $id)
            ->where('user_id', $userId)
            ->with('stops')
            ->first();
    }

    public function createWithStops(array $routeData, array $stops, int $userId): Route
    {
        $routeData['user_id'] = $userId;
        $route = $this->create($routeData);

        $cumulativeOffset = 0;
        foreach ($stops as $order => $stop) {
            if (is_string($stop)) {
                $stopName = $stop;
                $cumulativeOffset += 60;
            } else {
                $stopName = $stop['stop_name'];
                $cumulativeOffset += $stop['duration_minutes'] ?? 60;
            }
            $this->addStop($route, $stopName, $order + 1, $cumulativeOffset);
        }

        return $route->load('stops');
    }

    public function updateWithStops(Route $route, array $routeData, array $stops): Route
    {
        $route->update($routeData);

        // Delete existing stops and recreate with new ones
        $route->stops()->delete();

        $cumulativeOffset = 0;
        foreach ($stops as $order => $stop) {
            if (is_string($stop)) {
                $stopName = $stop;
                $cumulativeOffset += 60;
            } else {
                $stopName = $stop['stop_name'];
                $cumulativeOffset += $stop['duration_minutes'] ?? 60;
            }
            $this->addStop($route, $stopName, $order + 1, $cumulativeOffset);
        }

        return $route->load('stops');
    }
}
