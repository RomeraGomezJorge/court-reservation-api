<?php

declare(strict_types=1);

use App\Http\Controllers\Club\ClubController;
use App\Http\Controllers\Club\ClubStatusToggleController;
use App\Http\Controllers\Club\CourtAvailabilityToggleController;
use App\Http\Controllers\Club\CourtController;
use App\Http\Controllers\Club\FeatureController;
use App\Http\Controllers\Club\LoginController;
use App\Http\Controllers\Club\PasswordResetController;
use App\Http\Controllers\Club\ProfileController;
use App\Http\Controllers\Club\RegisterClubUserController;
use App\Http\Controllers\Club\SportTypeController;
use Illuminate\Support\Facades\Route;

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
    Route::delete('profile', [ProfileController::class, 'destroy']);

    /*=============================================
        CLUB
    =============================================*/
    Route::apiResource('clubs', ClubController::class);
    Route::patch('clubs/{club}/toggle-active', ClubStatusToggleController::class);

    /*=============================================
        COURT
    =============================================*/
    Route::apiResource('clubs.courts', CourtController::class)->except(['index'])->scoped();
    Route::patch('clubs/{club}/courts/{court}/toggle-availability', CourtAvailabilityToggleController::class);

    /*=============================================
        COURT CATALOGS
    =============================================*/
    Route::get('court/sport-types', [SportTypeController::class, 'index']);
    Route::get('court/features', [FeatureController::class, 'index']);

});
