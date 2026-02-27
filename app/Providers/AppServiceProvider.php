<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        JsonResource::withoutWrapping();

        ResetPassword::createUrlUsing(function ($notifiable, $token) {
            $url = '';
            $currentRoute = request()->route()->getName();
            if ($currentRoute === 'admin.password.email') {
                $url = config()->string('app.spa_url').'/#/auth/reset-password/'.$token;
            }

            return $url;

        });

    }
}
