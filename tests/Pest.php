<?php

declare(strict_types=1);

use App\Models\ClubUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithCachedConfig;
use Illuminate\Foundation\Testing\WithCachedRoutes;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Tests\TestCase;

use function Pest\Laravel\actingAs;

pest()
    ->extend(TestCase::class)
    // ------------------------------------------------------------------------------
    // WithCachedRoutes / WithCachedConfig:
    //
    // These traits enable the use of cached routes and configuration during tests,
    // mimicking production behavior where `route:cache` and `config:cache` are used.
    //
    // This avoids rebuilding the route collection and reloading configuration
    // ------------------------------------------------------------------------------
    ->use(WithCachedRoutes::class)
    ->use(WithCachedConfig::class)

    // ------------------------------------------------------------------------------
    //   Instead of refreshing the database before every test, the database is only
    //   refreshed when actually needed. This significantly reduces test execution
    //   time while preserving isolation guarantees.
    // ------------------------------------------------------------------------------
    ->use(LazilyRefreshDatabase::class)
    ->beforeEach(function (): void {
        // ------------------------------------------------------------------------------
        // Restores default randomness behavior. Useful when other tests or fakes
        // may have altered string generation, ensuring proper test isolation.
        // ------------------------------------------------------------------------------
        Str::createRandomStringsNormally();

        // ------------------------------------------------------------------------------
        // Prevents unintended external HTTP calls during tests. Any unmocked request
        // will fail, enforcing strict boundaries and avoiding flaky tests.
        // ------------------------------------------------------------------------------
        Http::preventStrayRequests();

        // ------------------------------------------------------------------------------
        // Prevents execution of real system processes during tests (e.g. Process::run).
        // Any unmocked process execution will fail, avoiding side effects.
        // ------------------------------------------------------------------------------
        Process::preventStrayProcesses();

        // ------------------------------------------------------------------------------
        // Prevents real delays caused by sleep/usleep, making tests faster and
        // deterministic.
        // ------------------------------------------------------------------------------
        Sleep::fake();

        // ------------------------------------------------------------------------------
        // Freezes time for the current test, ensuring consistent and predictable
        // date/time assertions.
        // ------------------------------------------------------------------------------
        $this->freezeTime();

        Model::preventLazyLoading();
    })
    ->in('app', 'Architecture');

expect()->extend('toBeOne', fn () => $this->toBe(1));

function actingAsClubUser(): ClubUser
{
    $clubUser = ClubUser::factory()->createQuietly();
    actingAs($clubUser);

    return $clubUser;
}

function actingAsUser(): User
{
    $user = User::factory()->createQuietly();
    actingAs($user);

    return $user;
}

function isStrictlySnakeCase(string $value): bool
{
    if (is_numeric($value)) {
        return true;
    }

    // Rule 1: No hyphens (kebab-case)
    if (Str::contains($value, '-')) {
        return false;
    }

    // Rule 2: No capitals (camelCase)
    if (mb_strtolower($value) !== $value) {
        return false;
    }

    return preg_match('/^[a-z0-9_]+$/', $value) === 1;
}
