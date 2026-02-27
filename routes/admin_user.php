<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\PasswordResetController;
use App\Http\Controllers\Admin\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginController::class, 'login']);

/*=============================================
       PASSWORD MANAGEMENT
=============================================*/
Route::post('forgot-password', [PasswordResetController::class, 'store'])->name('admin.password.email');
Route::put('reset-password', [PasswordResetController::class, 'update'])->name('admin.password.reset');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('features', FeatureController::class);
});
