<?php

declare(strict_types=1);

use App\Models\ClubUser;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Mockery\MockInterface;

beforeEach(function (): void {
    Config::set('app.spa_url', 'https://spa.test');
    app()->instance('env', 'testing');
});

afterEach(function (): void {
    app()->instance('env', 'testing');
    Model::handleLazyLoadingViolationUsing(null);
});

it('builds reset password URL for club users', function (): void {
    bootAppServiceProvider();

    $clubUser = ClubUser::factory()->createQuietly();

    $url = invokeNotificationCallback(ResetPassword::class, $clubUser, 'token-club');

    expect($url)->toBe('https://spa.test/#/club/auth/reset-password/token-club');
});

it('builds reset password URL for admin users', function (): void {
    bootAppServiceProvider();

    $user = User::factory()->createQuietly();

    $url = invokeNotificationCallback(ResetPassword::class, $user, 'token-admin');

    expect($url)->toBe('https://spa.test/#/admin/auth/reset-password/token-admin');
});

it('throws type error for unsupported reset password notifiable', function (): void {
    bootAppServiceProvider();

    $this->expectException(TypeError::class);
    $this->expectExceptionMessage('must be of type App\Models\ClubUser|App\Models\User|App\Models\AppUser');

    invokeNotificationCallback(ResetPassword::class, new stdClass(), 'token-invalid');
});

it('builds verify email URL for club users using signed route', function (): void {
    Config::set('auth.verification.expire', 90);
    bootAppServiceProvider();

    $clubUser = ClubUser::factory()->unverified()->createQuietly();

    URL::shouldReceive('temporarySignedRoute')
        ->once()
        ->withArgs(fn (string $name, CarbonImmutable $expiration, array $parameters): bool => $name === 'verification.club.verify'
            && $expiration->greaterThanOrEqualTo(Date::now()->addMinutes(90)->subSecond())
            && $parameters['id'] === $clubUser->getKey()
            && $parameters['hash'] === sha1((string) $clubUser->getEmailForVerification()))
        ->andReturn('https://signed.test/club');

    $url = invokeNotificationCallback(VerifyEmail::class, $clubUser);

    expect($url)->toBe('https://signed.test/club');
});

it('builds verify email URL for admin users using signed route with mock', function (): void {
    Config::set('auth.verification.expire', 60);
    bootAppServiceProvider();

    $user = User::factory()->createQuietly();

    URL::shouldReceive('temporarySignedRoute')
        ->once()
        ->withArgs(fn (string $name, CarbonImmutable $expiration, array $parameters): bool => $name === 'verification.verify'
            && $expiration->greaterThanOrEqualTo(Date::now()->addMinutes(60)->subSecond())
            && $parameters['id'] === $user->getKey()
            && $parameters['hash'] === sha1((string) $user->getEmailForVerification()))
        ->andReturn('https://signed.test/admin');

    $url = invokeNotificationCallback(VerifyEmail::class, $user);

    expect($url)->toBe('https://signed.test/admin');
});

it('throws type error for unsupported verify email notifiable', function (): void {
    bootAppServiceProvider();

    $this->expectException(TypeError::class);
    $this->expectExceptionMessage('must be of type App\\Models\\ClubUser|App\\Models\\User|App\\Models\\AppUser');

    invokeNotificationCallback(VerifyEmail::class, new stdClass());
});

it('enables strict mode in non production environment', function (): void {
    Config::set('app.env', 'testing');
    app()->instance('env', 'testing');

    bootAppServiceProvider();

    expect(Model::preventsLazyLoading())->toBeTrue();
});

it('disables strict mode in production environment using config set', function (): void {
    Config::set('app.env', 'production');
    app()->instance('env', 'production');

    bootAppServiceProvider();

    expect(Model::preventsLazyLoading())->toBeFalse();
});

it('uses carbon immutable dates after boot', function (): void {
    bootAppServiceProvider();

    expect(Date::now())->toBeInstanceOf(CarbonImmutable::class);
});

it('registers query logging listeners from boot when enabled', function (): void {
    Config::set('query-logging.enable', true);
    Config::set('query-logging.log_n_plus_one', true);

    DB::shouldReceive('listen')->once();

    bootAppServiceProvider();

    $modelReflection = new ReflectionClass(Model::class);
    $property = $modelReflection->getProperty('lazyLoadingViolationCallback');

    expect($property->getValue())->toBeCallable();
});

it('logs slow queries when logging is enabled', function (): void {
    $queryListener = null;

    Config::set('query-logging.slow_threshold', 100);

    DB::shouldReceive('listen')
        ->once()
        ->withArgs(function (callable $listener) use (&$queryListener): bool {
            $queryListener = $listener;

            return true;
        });

    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'An individual database query exceeded 100 ms.'
            && $context['sql'] === 'select * from "users"'
            && $context['raw'] === 'select * from "users"'
            && $context['time'] === 150
            && is_string($context['formatted']));

    $provider = new AppServiceProvider(app());
    $method = new ReflectionMethod($provider, 'logAllQueriesSlow');
    $method->invoke($provider);

    expect($queryListener)->toBeCallable();

    /** @var QueryExecuted&MockInterface $query */
    $query = Mockery::mock(QueryExecuted::class);
    $query->time = 150;
    $query->sql = 'select * from "users"';
    $query->shouldReceive('toRawSQL')->once()->andReturn('select * from "users"');

    $queryListener($query);
});

it('logs n+1 violations when enabled', function (): void {
    Config::set('query-logging.log_n_plus_one', true);

    Log::shouldReceive('warning')
        ->once()
        ->with('N+1 Query detected in model App\\Models\\User on relation clubs.');

    $provider = new AppServiceProvider(app());
    $method = new ReflectionMethod($provider, 'logAllQueriesNplusone');
    $method->invoke($provider);

    $modelReflection = new ReflectionClass(Model::class);
    $property = $modelReflection->getProperty('lazyLoadingViolationCallback');

    $lazyLoadingCallback = $property->getValue();

    expect($lazyLoadingCallback)->toBeCallable();

    $lazyLoadingCallback(new User(), 'clubs');
});

function bootAppServiceProvider(): void
{
    new AppServiceProvider(app())->boot();
}

function invokeNotificationCallback(string $notificationClass, mixed ...$arguments): string
{
    $reflectionClass = new ReflectionClass($notificationClass);
    $property = $reflectionClass->getProperty('createUrlCallback');

    $callback = $property->getValue();

    expect($callback)->toBeCallable();

    return $callback(...$arguments);
}
