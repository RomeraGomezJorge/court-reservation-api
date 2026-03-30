<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\FeatureStatusToggleController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\PasswordResetController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SportTypeController;
use App\Http\Controllers\Admin\SportTypeStatusToggleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginController::class, 'login']);

/*=============================================
       PASSWORD MANAGEMENT
=============================================*/
Route::post('forgot-password', [PasswordResetController::class, 'store'])->name('admin.password.email');
Route::put('reset-password', [PasswordResetController::class, 'update'])->name('admin.password.reset');

Route::middleware(['auth:sanctum', 'admin_user'])->group(function (): void {

    /*=============================================
       ADMIN USERS
    =============================================*/
    Route::put('users/{user}/change-password', [UserController::class, 'changePassword']);
    Route::apiResource('users', UserController::class);

    /*=============================================
       SPORT TYPES
    =============================================*/
    Route::apiResource('court/sport-types', SportTypeController::class);
    Route::patch('court/sport-types/{sport_type}/toggle-active', SportTypeStatusToggleController::class);

    /*=============================================
       FEATURES
    =============================================*/
    Route::apiResource('court/features', FeatureController::class);
    Route::patch('court/features/{feature}/toggle-active', FeatureStatusToggleController::class);

    /*=============================================
        PROFILE
    =============================================*/
    Route::get('profile', [ProfileController::class, 'show']);
});
