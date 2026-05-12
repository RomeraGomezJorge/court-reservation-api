<?php

declare(strict_types=1);

use App\Http\Controllers\Club\ActiveFeatureController;
use App\Http\Controllers\Club\ActiveSportTypeController;
use App\Http\Controllers\Club\AppUserController;
use App\Http\Controllers\Club\ClubController;
use App\Http\Controllers\Club\ClubStatusToggleController;
use App\Http\Controllers\Club\CourtAvailabilityToggleController;
use App\Http\Controllers\Club\CourtController;
use App\Http\Controllers\Club\CourtPriceRuleController;
use App\Http\Controllers\Club\LoginController;
use App\Http\Controllers\Club\PasswordResetController;
use App\Http\Controllers\Club\ProfileController;
use App\Http\Controllers\Club\RegisterClubUserController;
use Illuminate\Support\Facades\Route;

/*=============================================
       REGISTER MANAGEMENT
=============================================*/
Route::post('register', [RegisterClubUserController::class, 'store']);
Route::get('verify-email', [RegisterClubUserController::class, 'verifyEmail'])
    ->name('verification.club.verify')
    ->middleware('signed');

Route::post('login', [LoginController::class, 'login']);

/*=============================================
       PASSWORD MANAGEMENT
=============================================*/
Route::post('forgot-password', [PasswordResetController::class, 'store'])->name('club.password.email');
Route::put('reset-password', [PasswordResetController::class, 'update'])->name('club.password.reset');

Route::middleware(['auth:sanctum', 'verified', 'ensure_is_club_user'])->group(function (): void {

    /*=============================================
        APP USER
    =============================================*/
    Route::apiResource('app-users', AppUserController::class);

    /*=============================================
        CLUB
    =============================================*/
    Route::apiResource('clubs', ClubController::class);
    Route::patch('clubs/{club}/toggle-active', ClubStatusToggleController::class);

    /*=============================================
        COURT
    =============================================*/
    Route::apiResource('clubs.courts', CourtController::class)->except(['index']);
    Route::patch('clubs/{club}/courts/{court}/toggle-availability', CourtAvailabilityToggleController::class);

    /*=============================================
        FEATURE
    =============================================*/
    Route::get('court/active-features', [ActiveFeatureController::class, 'index']);

    /*=============================================
        PRICE RULE
    =============================================*/
    Route::post('clubs/{club}/courts/{court}/price-rules', [CourtPriceRuleController::class, 'store']);
    Route::get('clubs/{club}/courts/{court}/price-rules', [CourtPriceRuleController::class, 'show']);

    /*=============================================
        PROFILE
    =============================================*/
    Route::get('profile', [ProfileController::class, 'show']);
    Route::delete('profile', [ProfileController::class, 'destroy']);

    /*=============================================
        SPORT TYPE
    =============================================*/
    Route::get('court/active-sport-types', [ActiveSportTypeController::class, 'index']);
});
