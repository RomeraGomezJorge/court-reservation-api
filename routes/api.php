<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginController::class, 'login']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->group(function (): void {
    Route::apiResource('services', ServiceController::class);
});
