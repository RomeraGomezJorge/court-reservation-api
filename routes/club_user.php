<?php

declare(strict_types=1);

use App\Http\Controllers\Club\ClubController;
use App\Http\Controllers\Club\LoginController;
use App\Http\Controllers\Club\PasswordResetController;
use App\Http\Controllers\Club\ProfileController;
use App\Http\Controllers\Club\RegisterClubUserController;

/*=============================================
       REGISTER MANAGEMENT
=============================================*/
Route::post('register', [RegisterClubUserController::class, 'store']);
Route::get('verify-email', [RegisterClubUserController::class, 'verifyEmail'])->name('verification.club.verify');

Route::post('login', [LoginController::class, 'login']);

/*=============================================
       PASSWORD MANAGEMENT
=============================================*/
Route::post('forgot-password', [PasswordResetController::class, 'store'])->name('club.password.email');
Route::put('reset-password', [PasswordResetController::class, 'update'])->name('club.password.reset');

Route::middleware(['auth:sanctum'])->group(function (): void {

    /*=============================================
        PROFILE
    =============================================*/
    Route::get('profile', [ProfileController::class, 'show']);

    /*=============================================
        CLUB ONBOARDING
    =============================================*/
    Route::post('clubs', [ClubController::class, 'store']);

});
