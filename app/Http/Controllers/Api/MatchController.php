<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MatchRequest;
use App\Http\Resources\LoadResource;
use App\Services\MatchService;
use Illuminate\Http\JsonResponse;

class MatchController extends Controller
{
    public function __construct(
        private readonly MatchService $matchService,
    ) {}

    public function index(MatchRequest $request): JsonResponse
    {
        $excludeLoadId = $request->input('exclude_load_id')
            ? (int) $request->input('exclude_load_id')
            : null;

        $matches = $this->matchService->findMatches(
            $request->input('from_city'),
            $request->input('to_city'),
            $excludeLoadId,
        );

        return response()->json([
            'success' => true,
            'data' => LoadResource::collection($matches),
        ]);
    }
}
