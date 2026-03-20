<?php

declare(strict_types=1);

use App\Models\ClubUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use LaravelIdea\Helper\App\Models\_IH_ClubUser_C;
use Tests\TestCase;

use function Pest\Laravel\actingAs;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        Str::createRandomStringsNormally();
        Http::preventStrayRequests();
        Process::preventStrayProcesses();
        Sleep::fake();

        $this->freezeTime();
    })
    ->in('Http');

expect()->extend('toBeOne', fn () => $this->toBe(1));

function actingAsClubUser(): ClubUser
{
    $clubUser = ClubUser::factory()->create();
    actingAs($clubUser);

    return $clubUser;
}

function actingAsUser(): User
{
    $user = User::factory()->create();
    actingAs($user);

    return $user;
}
