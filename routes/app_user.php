<?php

declare(strict_types=1);

use App\Http\Controllers\App\LoginController;
use App\Http\Controllers\App\PasswordResetController;
use App\Http\Controllers\App\RegisterAppUserController;
use Illuminate\Support\Facades\Route;

/*=============================================
       REGISTER MANAGEMENT
=============================================*/
Route::post('register', [RegisterAppUserController::class, 'store']);
Route::get('verify-email', [RegisterAppUserController::class, 'verifyEmail'])->name('verification.app.verify');

Route::post('login', [LoginController::class, 'login']);

/*=============================================
       PASSWORD MANAGEMENT
=============================================*/
Route::post('forgot-password', [PasswordResetController::class, 'store'])->name('app.password.email');
Route::put('reset-password', [PasswordResetController::class, 'update'])->name('app.password.reset');

Route::middleware(['auth:sanctum'])->group(function (): void {});
