<?php

use App\Http\Controllers\Club\LoginController;
use App\Http\Controllers\Club\PasswordResetController;
use App\Http\Controllers\Club\RegisterClubController;

Route::post('register', [RegisterClubController::class, 'store']);
Route::post('login', [LoginController::class, 'login']);

/*=============================================
       PASSWORD MANAGEMENT
=============================================*/
Route::post('forgot-password', [PasswordResetController::class, 'store'])->name('admin.password.email');
Route::put('reset-password', [PasswordResetController::class, 'update'])->name('admin.password.reset');

// TODO: crear middleware para que solo pueda acceder los admins
Route::middleware(['auth:sanctum'])->group(function (): void {
});
