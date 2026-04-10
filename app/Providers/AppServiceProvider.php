<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\ClubUser;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Log;
use RuntimeException;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureStrictModel();
        $this->configureUrl();
        $this->configureDates();

        // ------------------------------------------------------------------------------
        // This will prevent any destructive commands from being executed
        // in production environments, such as dropping tables or truncating data.
        // This is a safety measure to prevent accidental data loss.
        // ------------------------------------------------------------------------------
        DB::prohibitDestructiveCommands(app()->isProduction());

        // ------------------------------------------------------------------------------
        // Enable or disable logging based on application settings
        // ------------------------------------------------------------------------------
        if (Config::boolean('query-logging.enable')) {
            $this->LogAllQueriesSlow();
            $this->logAllQueriesNplusone();
        }

        JsonResource::withoutWrapping();

        ResetPassword::createUrlUsing(function ($notifiable, string $token): string {
            $routeName = match (true) {
                $notifiable instanceof ClubUser => 'club',
                $notifiable instanceof User => 'admin',
                default => throw new RuntimeException('Usuario no soportado para restablecimiento de contraseña'),
            };

            return config()->string('app.spa_url')."/#/{$routeName}/auth/reset-password/".$token;
        });

        VerifyEmail::createUrlUsing(function ($notifiable) {
            $routeName = match (true) {
                $notifiable instanceof ClubUser => 'verification.club.verify',
                $notifiable instanceof User => 'verification.verify',
                default => throw new RuntimeException('Usuario no soportado para verificación de email'),
            };

            return URL::temporarySignedRoute(
                name: $routeName,
                expiration: Date::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                parameters: [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1((string) $notifiable->getEmailForVerification()),
                ],
            );
        });
    }

    /**
     * Use Strict Mode (only on local).
     *
     * 1. Prevent Lazy Loading
     * 2. Prevent Silently Discarding Attributes
     * 3. Prevent Accessing Missing Attributes
     * Reference: https://coderflex.com/blog/laravel-strict-mode-all-what-you-need-to-know
     */
    private function configureStrictModel(): void
    {
        Model::shouldBeStrict(! app()->isProduction());
    }

    /**
     * Enforce HTTPS (only in production).
     */
    private function configureUrl(): void
    {
        URL::forceHttps(app()->isProduction());
    }

    /**
     * Configure the application's dates.
     */
    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    /**
     * Log all slow queries for debugging purposes.
     */
    private function LogAllQueriesSlow(): void
    {
        DB::listen(function ($query): void {
            $threshold = Config::integer('query-logging.slow_threshold');
            if ($query->time > $threshold) {
                Log::warning('An individual database query exceeded '.$threshold.' ms.', [
                    'sql' => $query->sql,
                    'raw' => $query->toRawSQL(),
                    'time' => $query->time,
                    'formatted' => CarbonInterval::milliseconds($query->time)->cascade()->forHumans(['short' => true, 'parts' => 3, 'join' => true]),
                ]);
            }
        });
    }

    /**
     * Log all (N+1) queries for debugging purposes.
     */
    private function logAllQueriesNplusone(): void
    {
        if (Config::bool('query-logging.log_n_plus_one')) {
            Model::handleLazyLoadingViolationUsing(function ($model, $relation): void {
                Log::warning(sprintf(
                    'N+1 Query detected in model %s on relation %s.',
                    $model::class,
                    $relation,
                ));
            });
        }
    }
}
