<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', fn (): JsonResponse => response()->json([
    'data' => ['service' => 'NovaShop API', 'version' => 'v1'],
]))->name('home');
