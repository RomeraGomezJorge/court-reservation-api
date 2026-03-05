<?php

declare(strict_types=1);

use App\Http\Controllers\Club\LoginController;
use App\Http\Controllers\Club\PasswordResetController;
use App\Http\Controllers\Club\ProfileController;
use App\Http\Controllers\Club\RegisterClubController;

/*=============================================
       REGISTER MANAGEMENT
=============================================*/
Route::post('register', [RegisterClubController::class, 'store']);
Route::get('verify-email', [RegisterClubController::class, 'verifyEmail'])->name('verification.club.verify');

Route::post('login', [LoginController::class, 'login']);

/*=============================================
       PASSWORD MANAGEMENT
=============================================*/
Route::post('forgot-password', [PasswordResetController::class, 'store'])->name('club.password.email');
Route::put('reset-password', [PasswordResetController::class, 'update'])->name('club.password.reset');

// TODO: crear middleware para que solo pueda acceder los admins
Route::middleware(['auth:sanctum'])->group(function (): void {

    /*=============================================
        PROFILE
    =============================================*/
    Route::get('profile', [ProfileController::class, 'show']);

});
