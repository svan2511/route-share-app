<?php

namespace App\Services;

use App\Models\Load;
use App\Repositories\LoadRepository;
use App\Repositories\RouteRepository;
use Illuminate\Database\Eloquent\Collection;

class MatchService
{
    public function __construct(
        private readonly LoadRepository $loadRepository,
        private readonly RouteRepository $routeRepository,
    ) {}

    public function findMatches(string $fromCity, string $toCity, ?int $excludeLoadId = null): Collection
    {
        $routes = $this->routeRepository->findRoutesCoveringSegment($fromCity, $toCity);

        if ($routes->isEmpty()) {
            $matches = $this->loadRepository->findExactMatches($fromCity, $toCity);
        } else {
            $matches = $this->loadRepository->findRouteBasedMatches($fromCity, $toCity, $routes->toArray());
        }

        if ($excludeLoadId) {
            $matches = $matches->where('id', '!=', $excludeLoadId)->values();
        }

        return $matches;
    }

    public function getMatchForLoad(Load $load): ?Load
    {
        return $this->findMatches(
            $load->from_city,
            $load->to_city,
            $load->id
        )->first();
    }

    public function findMatchesForDisplay(string $fromCity, string $toCity, ?int $excludeLoadId = null): array
    {
        $matches = $this->findMatches($fromCity, $toCity, $excludeLoadId);

        return [
            'matches' => $matches,
            'match_count' => $matches->count(),
        ];
    }
}
