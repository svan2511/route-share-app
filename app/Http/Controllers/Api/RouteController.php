<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RouteResource;
use App\Repositories\RouteRepository;
use App\Services\IndianCities;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RouteController extends Controller
{
    public function __construct(
        private readonly RouteRepository $routeRepository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['from_city', 'to_city']);
        $routes = $this->routeRepository->getAll($filters);

        return response()->json([
            'success' => true,
            'data' => RouteResource::collection($routes),
        ]);
    }

    public function cities(): JsonResponse
    {
        $cities = $this->routeRepository->getAllCities();

        return response()->json([
            'success' => true,
            'data' => $cities,
        ]);
    }

    public function searchCities(Request $request): JsonResponse
    {
        $q = $request->query('q', '');

        if (strlen($q) < 2) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $localCities = IndianCities::search($q);

        // If local IndianCities has results, return them directly (instant, consistent)
        if (!empty($localCities)) {
            return response()->json([
                'success' => true,
                'data' => array_slice($localCities, 0, 15),
            ]);
        }

        // Fallback: Photon API for cities not in our local list (cached 24h)
        $photonCities = Cache::remember('cities_search_photon_' . md5($q), 86400, function () use ($q) {
            $url = 'https://photon.komoot.io/api/?' . http_build_query([
                'q' => $q,
                'limit' => 15,
                'countrycode' => 'IN',
                'lang' => 'en',
            ]);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 4,
                CURLOPT_USERAGENT => 'LoadApp/1.0 (ride-sharing-app)',
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || $response === false) {
                return [];
            }

            $data = json_decode($response, true);
            $cities = [];
            $seen = [];

            $features = $data['features'] ?? [];

            foreach ($features as $feature) {
                $props = $feature['properties'] ?? [];
                $type = $props['type'] ?? '';
                $name = $props['name'] ?? '';
                $state = $props['state'] ?? '';

                if (!in_array($type, ['city', 'town', 'village', 'municipality'])) {
                    continue;
                }

                if (!empty($name) && !isset($seen[$name])) {
                    $seen[$name] = true;
                    $label = $name . ($state ? ', ' . $state : '');
                    $cities[] = $label;
                }
            }

            return $cities;
        });

        return response()->json([
            'success' => true,
            'data' => array_slice($photonCities, 0, 15),
        ]);
    }

    public function myRoutes(Request $request): JsonResponse
    {
        $routes = $this->routeRepository->findUserRoutes($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => RouteResource::collection($routes),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'route_name' => 'required|string|max:255',
            'from_city' => 'required|string|max:255',
            'to_city' => 'required|string|max:255|different:from_city',
            'stops' => 'nullable|array',
            'stops.*.stop_name' => 'required_with:stops|string|max:255',
            'stops.*.duration_minutes' => 'required_with:stops|integer|min:5|max:1440',
            'destination_offset_minutes' => 'nullable|integer|min:5|max:1440',
        ]);

        $route = $this->routeRepository->createWithStops(
            [
                'route_name' => $validated['route_name'],
                'from_city' => $validated['from_city'],
                'to_city' => $validated['to_city'],
                'destination_offset_minutes' => $validated['destination_offset_minutes'] ?? null,
            ],
            $validated['stops'] ?? [],
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Route created successfully',
            'data' => new RouteResource($route),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $route = $this->routeRepository->findUserRoute($id, $request->user()->id);

        if (!$route) {
            return response()->json([
                'success' => false,
                'message' => 'Route not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new RouteResource($route),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $route = $this->routeRepository->findUserRoute($id, $request->user()->id);

        if (!$route) {
            return response()->json([
                'success' => false,
                'message' => 'Route not found',
            ], 404);
        }

        $route->delete();

        return response()->json([
            'success' => true,
            'message' => 'Route deleted successfully',
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $route = $this->routeRepository->findUserRoute($id, $request->user()->id);

        if (!$route) {
            return response()->json([
                'success' => false,
                'message' => 'Route not found',
            ], 404);
        }

        $validated = $request->validate([
            'route_name' => 'required|string|max:255',
            'from_city' => 'required|string|max:255',
            'to_city' => 'required|string|max:255|different:from_city',
            'stops' => 'nullable|array',
            'stops.*.stop_name' => 'required_with:stops|string|max:255',
            'stops.*.duration_minutes' => 'required_with:stops|integer|min:5|max:1440',
            'destination_offset_minutes' => 'nullable|integer|min:5|max:1440',
        ]);

        $route = $this->routeRepository->updateWithStops(
            $route,
            [
                'route_name' => $validated['route_name'],
                'from_city' => $validated['from_city'],
                'to_city' => $validated['to_city'],
                'destination_offset_minutes' => $validated['destination_offset_minutes'] ?? null,
            ],
            $validated['stops'] ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'Route updated successfully',
            'data' => new RouteResource($route),
        ]);
    }
}
