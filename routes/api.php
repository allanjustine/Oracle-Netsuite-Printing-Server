<?php

use App\Http\Controllers\Api\V1\Admin\AdminController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\ReceiptController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('/profile', function (Request $request) {
        return $request->user()->load(['branch', 'roles']);
    });

    Route::controller(AdminController::class)->middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/users', 'index');
    });
});

Route::prefix('v1')->group(function () {

    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'store')->middleware('throttle:5,1');
        Route::post('/logout', 'destroy');
    });

    Route::controller(ReceiptController::class)->group(function () {
        Route::get('/receipt-records', 'index');
        Route::post('/print-receipt-count', 'store');
    });
});

Route::prefix('v1')->group(function () {

    Route::get('/app-version', function () {
        $version = env('APP_VERSION');
        return response()->json([
            "version"          =>   $version
        ], 200);
    });
});
