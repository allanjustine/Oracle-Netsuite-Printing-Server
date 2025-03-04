<?php

use App\Http\Controllers\Api\V1\Admin\AdminController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\ReceiptController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/profile', function (Request $request) {
        return $request->user()->with(['branch', 'roles']);
    });

    Route::controller(AdminController::class)->middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/users', 'index');
    });
});

Route::controller(AuthController::class)->prefix('v1')->group(function () {
    Route::post('/login', 'store');
    Route::post('/logout', 'destroy');
});

Route::controller(ReceiptController::class)->prefix('v1')->group(function () {
    Route::post('/print-receipt-count', 'store');
});
