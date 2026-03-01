<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\LoginController;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginController::class, 'login']);
