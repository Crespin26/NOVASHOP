<?php

use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ReadinessController;
use App\Http\Middleware\RequireAdministrator;
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

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/products', [CatalogController::class, 'index'])->name('products.index');
    Route::get('/products/{slug}', [CatalogController::class, 'show'])->name('products.show');
    Route::get('/categories', [CatalogController::class, 'categories']);
    Route::get('/images/{image}', [CatalogController::class, 'image'])->name('images.show');
    Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])->withoutMiddleware(EnsureFrontendRequestsAreStateful::class);
    Route::middleware(['web', 'throttle:forms'])->group(function (): void {
        Route::post('/contact', [ContactController::class, 'store']);
        Route::post('/newsletter', [ContactController::class, 'subscribe']);
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
        Route::post('/auth/forgot-password', [AuthController::class, 'forgot']);
        Route::post('/auth/reset-password', [AuthController::class, 'reset']);
    });
    Route::get('/newsletter/confirm/{token}', [ContactController::class, 'confirm'])->middleware('throttle:forms');
    Route::post('/newsletter/unsubscribe/{token}', [ContactController::class, 'unsubscribe'])->middleware(['web', 'throttle:forms']);
    Route::middleware(['web', 'auth:sanctum'])->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/verification-notification', [AuthController::class, 'resend'])->middleware('throttle:forms');
        Route::get('/auth/verify/{id}/{hash}', [AuthController::class, 'verify'])->middleware('signed')->name('verification.verify');
        Route::post('/profile/deletion-request', [AccountController::class, 'requestDeletion'])->middleware('throttle:forms');
        Route::patch('/profile', [AccountController::class, 'update']);
        Route::put('/profile/password', [AccountController::class, 'password']);
        Route::get('/addresses', [AccountController::class, 'addresses']);
        Route::post('/addresses', [AccountController::class, 'saveAddress']);
        Route::put('/addresses/{address}', [AccountController::class, 'saveAddress']);
        Route::delete('/addresses/{address}', [AccountController::class, 'deleteAddress']);
        Route::get('/cart', [CartController::class, 'index']);
        Route::put('/cart/items', [CartController::class, 'put']);
        Route::delete('/cart/items/{variant}', [CartController::class, 'remove']);
        Route::delete('/cart', [CartController::class, 'clear']);
        Route::get('/favorites', [CartController::class, 'favorites']);
        Route::put('/favorites/{product}', [CartController::class, 'favorite']);
        Route::delete('/favorites/{product}', [CartController::class, 'unfavorite']);
        Route::middleware('verified')->group(function (): void {
            Route::get('/orders', [OrderController::class, 'index']);
            Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:forms');
            Route::get('/orders/{reference}', [OrderController::class, 'show']);
            Route::post('/orders/{reference}/cancel', [OrderController::class, 'cancel']);
            Route::post('/orders/{reference}/payment', [PaymentController::class, 'session'])->middleware('throttle:forms');
            Route::get('/returns', [OrderController::class, 'returns']);
            Route::post('/returns', [OrderController::class, 'requestReturn']);
        });
        Route::prefix('admin')->middleware(['verified', RequireAdministrator::class])->group(function (): void {
            $controller = AdminController::class;
            Route::get('/dashboard', [$controller, 'dashboard']);
            Route::get('/settings', [$controller, 'settings']);
            Route::put('/settings', [$controller, 'updateSettings']);
            Route::post('/categories', [$controller, 'category']);
            Route::put('/categories/{category}', [$controller, 'category']);
            Route::delete('/categories/{category}', [$controller, 'deleteCategory']);
            Route::post('/products', [$controller, 'product']);
            Route::put('/products/{product}', [$controller, 'product']);
            Route::post('/products/{product}/variants', [$controller, 'variant']);
            Route::put('/products/{product}/variants/{variant}', [$controller, 'variant']);
            Route::post('/products/{product}/images', [$controller, 'image']);
            Route::delete('/images/{image}', [$controller, 'deleteImage']);
            Route::post('/variants/{variant}/stock', [$controller, 'stock']);
            Route::get('/orders/{reference}', [$controller, 'order']);
            Route::patch('/orders/{reference}', [$controller, 'transition']);
            Route::patch('/returns/{reference}', [$controller, 'returned']);
            Route::patch('/messages/{message}', [$controller, 'message']);
            Route::get('/{kind}/{id}', [$controller, 'detail'])->whereIn('kind', ['products', 'returns', 'messages', 'customers'])->whereNumber('id');
            Route::get('/{kind}', [$controller, 'index'])->whereIn('kind', ['products', 'categories', 'stocks', 'orders', 'returns', 'messages', 'activity', 'customers']);
        });
    });
});
