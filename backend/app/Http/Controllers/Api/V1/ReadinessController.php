<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReadinessController extends Controller
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::select('SELECT 1');
        } catch (Throwable) {
            Log::warning('Database readiness check failed.');

            return response()->json([
                'message' => 'Service temporairement indisponible.',
                'data' => ['status' => 'unavailable'],
            ], 503)->header('Cache-Control', 'no-store');
        }

        return response()->json([
            'data' => ['status' => 'ready', 'database' => 'connected'],
        ])->header('Cache-Control', 'no-store');
    }
}
