<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

it('ensures all loaded route URIs follow the kebab-case convention', function (): void {
    $ignoredUris = [
        '_ignition/execute-solution',
        '_ignition/health-check',
        '_ignition/handle-solution',
        '_ignition/update-config',
        'livewire/livewire.min.js',
        'livewire/livewire.js',
        'livewire/livewire.min.js.map',
    ];

    $invalidRoutes = collect(Route::getRoutes())
        ->map(fn ($route) => $route->uri())
        ->reject(fn ($uri): bool => in_array($uri, $ignoredUris))
        ->reject(fn (string $uri): bool => isKebabCaseUri($uri))
        ->values();

    $this->assertEmpty(
        $invalidRoutes,
        "The following route URIs do not follow the kebab-case convention:\n- "
        .$invalidRoutes->implode("\n- ")
        ."\nIf any URI is a valid third-party exception, add it to ignoredUris.",
    );

});

function isKebabCaseUri(string $uri): bool
{
    $uriWithoutParams = (string) preg_replace('/\{[^}]+\}/', 'param', $uri);

    $hasCapitalLetters = (bool) preg_match('/[A-Z]/', $uriWithoutParams);
    if ($hasCapitalLetters) {
        return false;
    }

    $hasSnakeCaseCharacters = Str::contains($uriWithoutParams, '_');
    if ($hasSnakeCaseCharacters) {
        return false;
    }

    $hasSpaces = (bool) preg_match('/\s/', $uriWithoutParams);
    if ($hasSpaces) {
        return false;
    }

    $hasInvalidCharacters = (bool) preg_match('/[^a-z0-9\/-]/', $uriWithoutParams);

    return ! $hasInvalidCharacters;
}
