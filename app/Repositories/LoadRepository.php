<?php

namespace App\Repositories;

use App\Models\Load;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class LoadRepository
{
    public function paginateActive(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Load::active()->with('user:id,full_name,business_name,city', 'route.stops');

        if (!empty($filters['exclude_user_id'])) {
            $query->where('user_id', '!=', $filters['exclude_user_id']);
        }

        if (!empty($filters['from_city']) && !empty($filters['to_city'])) {
            $from = $filters['from_city'];
            $to = $filters['to_city'];

            $query->where(function ($q) use ($from) {
                $q->whereHas('route', fn($q) => $q->whereRaw('LOWER(from_city) LIKE ?', ['%' . mb_strtolower($from) . '%']))
                  ->orWhereHas('route.stops', fn($q) => $q->whereRaw('LOWER(stop_name) LIKE ?', ['%' . mb_strtolower($from) . '%']));
            })->where(function ($q) use ($to) {
                $q->whereHas('route', fn($q) => $q->whereRaw('LOWER(to_city) LIKE ?', ['%' . mb_strtolower($to) . '%']))
                  ->orWhereHas('route.stops', fn($q) => $q->whereRaw('LOWER(stop_name) LIKE ?', ['%' . mb_strtolower($to) . '%']));
            });
        } elseif (!empty($filters['from_city'])) {
            $query->whereRaw('LOWER(from_city) LIKE ?', ['%' . mb_strtolower($filters['from_city']) . '%']);
        } elseif (!empty($filters['to_city'])) {
            $query->whereRaw('LOWER(to_city) LIKE ?', ['%' . mb_strtolower($filters['to_city']) . '%']);
        }

        if (!empty($filters['vehicle_type'])) {
            $query->where('vehicle_type', $filters['vehicle_type']);
        }

        $paginator = $query->latest()->paginate($perPage);

        // Post-pagination time filter: exclude loads where truck passed pickup stop
        // Also verify load's own from/to cover the requested segment
        if (!empty($filters['from_city']) && !empty($filters['to_city'])) {
            $from = $filters['from_city'];
            $to = $filters['to_city'];
            $paginator->setCollection(
                $paginator->getCollection()
                    ->filter(function ($load) use ($from, $to) {
                        $stops = $load->route?->stops;
                        $route = $load->route;

                        if (!$stops || $stops->isEmpty()) {
                            return stripos($load->from_city ?? '', $from) !== false
                                && stripos($load->to_city ?? '', $to) !== false;
                        }

                        $maxOrder = $stops->max('stop_order');

                        $cityOrder = function ($city) use ($stops, $route, $maxOrder) {
                            $stop = $stops->first(fn($s) => stripos($s->stop_name, $city) !== false);
                            if ($stop) return $stop->stop_order;
                            if ($route && stripos($route->from_city, $city) !== false) return 0;
                            if ($route && stripos($route->to_city, $city) !== false) return $maxOrder + 1;
                            return null;
                        };

                        $loadFromOrder = $cityOrder($load->from_city);
                        $loadToOrder = $cityOrder($load->to_city);
                        $reqFromOrder = $cityOrder($from);
                        $reqToOrder = $cityOrder($to);

                        if ($loadFromOrder === null || $loadToOrder === null || $reqFromOrder === null || $reqToOrder === null) {
                            return false;
                        }

                        // Load must cover the requested segment
                        if ($loadFromOrder > $reqFromOrder || $loadToOrder < $reqToOrder) {
                            return false;
                        }

                        // Time check: truck must not have passed pickup stop
                        $pickupStop = $stops->first(fn($s) => stripos($s->stop_name, $from) !== false);
                        $pickupOffset = $pickupStop?->time_offset_minutes;
                        if ($pickupOffset === null) {
                            return true;
                        }
                        $departureDate = $load->departure_date instanceof Carbon
                            ? $load->departure_date->format('Y-m-d')
                            : $load->departure_date;
                        $pickupTime = Carbon::parse($departureDate . ' ' . $load->departure_time)
                            ->addMinutes((int) $pickupOffset);
                        return $pickupTime->isFuture();
                    })
                    ->map(function ($load) use ($from) {
                        $stops = $load->route?->stops;
                        $pickupOffset = $stops
                            ?->first(fn($s) => stripos($s->stop_name, $from) !== false)
                            ?->time_offset_minutes;
                        if ($pickupOffset === null && $load->route && stripos($load->route->from_city, $from) !== false) {
                            $pickupOffset = 0;
                        }
                        if ($pickupOffset !== null) {
                            $departureDate = $load->departure_date instanceof Carbon
                                ? $load->departure_date->format('Y-m-d')
                                : $load->departure_date;
                            $pickupTime = Carbon::parse($departureDate . ' ' . $load->departure_time)
                                ->addMinutes((int) $pickupOffset);
                            $load->estimated_pickup_date = $pickupTime->format('Y-m-d');
                            $load->estimated_pickup_time = $pickupTime->format('H:i');
                        }
                        return $load;
                    })
            );
        }

        return $paginator;
    }

    public function findById(int $id): ?Load
    {
        return Load::with(['user', 'route.stops'])->find($id);
    }

    public function create(array $data): Load
    {
        return Load::create($data);
    }

    public function update(Load $load, array $data): bool
    {
        return $load->update($data);
    }

    public function delete(Load $load): bool
    {
        return $load->delete();
    }

    public function getUserLoadsGrouped(int $userId): array
    {
        $loads = Load::with('route.stops')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return [
            'active' => $loads->where('status', 'active'),
            'completed' => $loads->where('status', 'completed'),
            'expired' => $loads->where('status', 'expired'),
        ];
    }

    public function findRouteBasedMatches(string $fromCity, string $toCity, array $routesData): Collection
    {
        $routeIds = [];
        $stopOrders = [];
        $stopOffsets = [];

        foreach ($routesData as $route) {
            $routeId = $route instanceof Model ? $route->id : $route['id'];
            $routeIds[] = $routeId;
            $stops = $route instanceof Model ? $route->stops : ($route['stops'] ?? []);

            foreach ($stops as $stop) {
                $stopName = $stop instanceof Model ? $stop->stop_name : ($stop['stop_name'] ?? '');
                $stopOrder = $stop instanceof Model ? $stop->stop_order : ($stop['stop_order'] ?? 0);
                $stopOffset = $stop instanceof Model ? ($stop->time_offset_minutes ?? 0) : ($stop['time_offset_minutes'] ?? 0);
                $stopOrders[$routeId][$stopName] = $stopOrder;
                $stopOffsets[$routeId][$stopName] = (int) $stopOffset;
            }

            $maxOrder = 0;
            if (!empty($stops)) {
                $orders = $route instanceof Model
                    ? $route->stops->pluck('stop_order')->toArray()
                    : array_column($route['stops'] ?? [], 'stop_order');
                $maxOrder = !empty($orders) ? max($orders) : 0;
            }

            $routeFrom = $route instanceof Model ? $route->from_city : ($route['from_city'] ?? '');
            $routeTo = $route instanceof Model ? $route->to_city : ($route['to_city'] ?? '');

            if ($routeFrom && !isset($stopOrders[$routeId][$routeFrom])) {
                $stopOrders[$routeId][$routeFrom] = 0;
                $stopOffsets[$routeId][$routeFrom] = 0;
            }
            if ($routeTo && !isset($stopOrders[$routeId][$routeTo])) {
                $stopOrders[$routeId][$routeTo] = $maxOrder + 1;
                $stopOffsets[$routeId][$routeTo] = ($maxOrder + 1) * 60;
            }
        }

        $fromOrders = [];
        $toOrders = [];

        foreach ($routesData as $route) {
            $routeId = $route instanceof Model ? $route->id : $route['id'];
            $stops = $route instanceof Model ? $route->stops : ($route['stops'] ?? []);

            foreach ($stops as $stop) {
                $stopName = $stop instanceof Model ? $stop->stop_name : ($stop['stop_name'] ?? '');
                $stopOrder = $stop instanceof Model ? $stop->stop_order : ($stop['stop_order'] ?? 0);

                if ($stopName === $fromCity) {
                    $fromOrders[$routeId] = $stopOrder;
                }
                if ($stopName === $toCity) {
                    $toOrders[$routeId] = $stopOrder;
                }
            }

            $routeFrom = $route instanceof Model ? $route->from_city : ($route['from_city'] ?? '');
            $routeTo = $route instanceof Model ? $route->to_city : ($route['to_city'] ?? '');

            if (!isset($fromOrders[$routeId]) && $routeFrom === $fromCity) {
                $fromOrders[$routeId] = 0;
            }
            if (!isset($toOrders[$routeId]) && $routeTo === $toCity) {
                $toOrders[$routeId] = $stopOrders[$routeId][$routeTo] ?? 999;
            }
        }

        $loads = Load::active()
            ->with('user:id,full_name,business_name,city,phone')
            ->whereIn('route_id', $routeIds)
            ->get();

        return $loads->filter(function ($load) use ($fromOrders, $toOrders, $stopOrders, $stopOffsets, $fromCity, $toCity) {
            $routeId = $load->route_id;
            if (!isset($stopOrders[$routeId])) return false;

            $loadFrom = $stopOrders[$routeId][$load->from_city] ?? null;
            $loadTo = $stopOrders[$routeId][$load->to_city] ?? null;
            $reqFrom = $fromOrders[$routeId] ?? null;
            $reqTo = $toOrders[$routeId] ?? null;

            if ($loadFrom === null || $loadTo === null || $reqFrom === null || $reqTo === null) return false;

            // Load must cover the requested segment
            if ($loadFrom > $reqFrom || $loadTo < $reqTo) return false;

            // Time check: truck must not have passed the pickup stop yet
            $pickupOffset = $stopOffsets[$routeId][$fromCity] ?? null;
            if ($pickupOffset !== null) {
                $departureDate = $load->departure_date instanceof Carbon
                    ? $load->departure_date->toDateString()
                    : $load->departure_date;
                $departureTime = $load->departure_time;
                $estimatedPickup = Carbon::parse($departureDate . ' ' . $departureTime)
                    ->addMinutes($pickupOffset);
                if ($estimatedPickup->isPast()) {
                    return false;
                }
            }

            return true;
        })->values();
    }

    public function findExactMatches(string $fromCity, string $toCity): Collection
    {
        return Load::active()
            ->with('user:id,full_name,business_name,city,phone')
            ->where('from_city', $fromCity)
            ->where('to_city', $toCity)
            ->latest()
            ->get()
            ->filter(function ($load) {
                $departureDate = $load->departure_date instanceof Carbon
                    ? $load->departure_date->format('Y-m-d')
                    : $load->departure_date;
                $departure = Carbon::parse($departureDate . ' ' . $load->departure_time);
                return $departure->isFuture();
            })
            ->values();
    }
}
