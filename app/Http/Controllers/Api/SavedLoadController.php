<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SavedLoadResource;
use App\Models\Load;
use App\Services\SavedLoadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedLoadController extends Controller
{
    public function __construct(
        private readonly SavedLoadService $savedLoadService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $savedLoads = $this->savedLoadService->getUserSavedLoads($request->user());

        return response()->json([
            'success' => true,
            'data' => SavedLoadResource::collection($savedLoads),
        ]);
    }

    public function toggle(Request $request, Load $load): JsonResponse
    {
        $result = $this->savedLoadService->toggleSave($request->user(), $load);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'data' => ['saved' => $result['saved']],
        ]);
    }
}
