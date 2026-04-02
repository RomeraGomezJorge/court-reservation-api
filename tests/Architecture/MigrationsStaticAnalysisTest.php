<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

function migrationFiles(): array
{
    return File::allFiles(database_path('migrations'));
}

function fileContainsAny(string $content, array $needles): bool
{
    return Str::contains($content, $needles);
}

it('migrations must not use eloquent models', function () {

    $forbidden = [
        'App\\Models',
    ];

    $violations = [];

    foreach (migrationFiles() as $file) {
        $content = $file->getContents();

        if (fileContainsAny($content, $forbidden)) {
            $violations[] = $file->getRelativePathname();
        }
    }

    expect($violations)->toBeEmpty(
        "The following migration files use Eloquent models and violate the architecture convention:\n- "
        .implode("\n- ", $violations)
        ."\nIf a case is truly unavoidable, discuss it first and document an explicit exception."
    );
});

it('migrations must not use factories', function () {

    $forbidden = [
        'Database\\Factories',
        '::factory(',
        'factory(',
    ];

    $violations = [];

    foreach (migrationFiles() as $file) {
        $content = $file->getContents();

        if (fileContainsAny($content, $forbidden)) {
            $violations[] = $file->getRelativePathname();
        }
    }

    expect($violations)->toBeEmpty(
        "The following migration files use factories and violate the architecture convention:\n- "
        .implode("\n- ", $violations)
        ."\nMigrations must stay deterministic; if this should be an exception, document it explicitly."
    );
});

it('migrations must not use enums', function () {

    $forbidden = [
        'App\\Enums',
        'enum ',
    ];

    $violations = [];

    foreach (migrationFiles() as $file) {
        $content = $file->getContents();

        if (fileContainsAny($content, $forbidden)) {
            $violations[] = $file->getRelativePathname();
        }
    }

    expect($violations)->toBeEmpty(
        "The following migration files use PHP enums and violate the architecture convention:\n- "
        .implode("\n- ", $violations)
        ."\nAvoid enums in migrations to keep them database-agnostic; add a documented exception only if required."
    );
});

it('ensures no migrations use database enums', function (): void {
    $violations = [];

    foreach (migrationFiles() as $file) {
        $content = $file->getContents();

        if (fileContainsAny(Str::lower($content), ['->enum('])) {
            $violations[] = $file->getRelativePathname();
        }
    }

    expect($violations)->toBeEmpty(
        "The following migration files use database enums (->enum()) and violate the architecture convention:\n- "
        .implode("\n- ", $violations)
        ."\nUse strings plus application-level validation/casting, or document an explicit exception if truly required."
    );
});

it('ensures no migrations use default values except whitelisted ones', function (): void {
    $whitelistedMigrationFiles = [
        // Add relative migration file names here when a default value is explicitly approved.
    ];

    $violations = [];

    foreach (migrationFiles() as $file) {
        $relativePath = $file->getRelativePathname();

        if (in_array($relativePath, $whitelistedMigrationFiles, true)) {
            continue;
        }

        $content = $file->getContents();

        if (fileContainsAny(Str::lower($content), ['->default('])) {
            $violations[] = $relativePath;
        }
    }

    expect($violations)->toBeEmpty(
        "The following migration files use default values (->default()) and violate the architecture convention:\n- "
        .implode("\n- ", $violations)
        ."\nIf a default is intentionally required, add the migration file to the whitelist explicitly."
    );
});

it('migrations must not define default values except allowed ones', function () {

    $allowedDefaults = [
        'CURRENT_TIMESTAMP',
        'now()',
    ];

    $violations = [];

    foreach (migrationFiles() as $file) {
        $content = $file->getContents();

        preg_match_all('/->default\((.*?)\)/', $content, $matches);

        foreach ($matches[1] as $default) {
            $normalized = mb_trim($default, " \t\n\r\0\x0B'\"");

            if (! in_array($normalized, $allowedDefaults, true)) {
                $violations[] = sprintf(
                    '%s -> default(%s)',
                    $file->getRelativePathname(),
                    $default
                );
            }
        }
    }

    expect($violations)->toBeEmpty(
        "The following migration defaults are not in the allowed list:\n- "
        .implode("\n- ", $violations)
        ."\nIf a default is valid for the project, add it explicitly to the allowed defaults list."
    );
});
