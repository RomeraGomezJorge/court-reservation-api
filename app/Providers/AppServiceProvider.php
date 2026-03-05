<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Club;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        JsonResource::withoutWrapping();

        ResetPassword::createUrlUsing(function ($notifiable, string $token): string {

            $routeName = match (true) {
                $notifiable instanceof Club => 'club',
                $notifiable instanceof User => 'admin',
                default => throw new RuntimeException('Usuario no soportado para restablecimiento de contraseña'),
            };

            return config()->string('app.spa_url')."/#/{$routeName}/auth/reset-password/".$token;
        });

        VerifyEmail::createUrlUsing(function ($notifiable) {

            $routeName = match (true) {
                $notifiable instanceof Club => 'verification.club.verify',
                $notifiable instanceof User => 'verification.verify',
                default => throw new RuntimeException('Usuario no soportado para verificación de email'),
            };

            return URL::temporarySignedRoute(
                name: $routeName,
                expiration: Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                parameters: [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ],
            );

        });

    }
}
