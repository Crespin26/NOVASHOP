<?php

use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ReadinessController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/health', HealthController::class)
        ->withoutMiddleware(EnsureFrontendRequestsAreStateful::class)->name('health');
    Route::get('/ready', ReadinessController::class)
        ->withoutMiddleware(EnsureFrontendRequestsAreStateful::class)->name('ready');
    Route::get('/me', fn (Request $request): UserResource => new UserResource($request->user()))
        ->middleware('auth:sanctum')
        ->name('me');
});
