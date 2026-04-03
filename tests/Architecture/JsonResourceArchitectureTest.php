<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Get all files from the App/Http/Resources directory.
 */
function resourceFiles(): array
{
    $path = app_path('Http/Resources');

    return File::exists($path) ? File::allFiles($path) : [];
}

/**
 * Validates if a key is strictly snake_case.
 */
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
    if (strtolower($value) !== $value) {
        return false;
    }

    return Str::snake($value) === $value;
}

// --- Architecture Tests ---

/**
 * Ensures that all files in the Resources directory end with the 'Resource' suffix.
 * Example: UserResource.php (Valid), User.php (Invalid)
 */
it('ensures all resource files have the Resource suffix', function (): void {
    $violations = [];

    foreach (resourceFiles() as $file) {
        $fileName = $file->getBasename('.php');

        // Using Str::endsWith from Laravel 12.x helpers
        if (! Str::endsWith($fileName, 'Resource')) {
            $violations[] = $file->getRelativePathname();
        }
    }

    expect($violations)->toBeEmpty(
        "The following files in App/Http/Resources are missing the 'Resource' suffix:\n- "
        . implode("\n- ", $violations)
        . "\n\nStandardize your naming convention to [Name]Resource.php for better code discovery."
    );
});

/**
 * Ensures all attributes defined in the Resources are strictly snake_case.
 */
it('ensures all attributes in Http Resources are strictly defined in snake_case', function (): void {
    $violations = [];

    foreach (resourceFiles() as $file) {
        $content = $file->getContents();

        // Capture keys before the '=>' operator in the toArray method
        preg_match_all('/[\'"](.*?)[\'"]\s*=>/', $content, $matches);

        $keys = $matches[1] ?? [];

        foreach ($keys as $key) {
            if (! isStrictlySnakeCase((string) $key)) {
                $violations[] = sprintf(
                    '[%s] -> invalid key: "%s"',
                    $file->getRelativePathname(),
                    $key
                );
            }
        }
    }

    expect($violations)->toBeEmpty(
        "The following Resource attributes do not follow the strict snake_case convention:\n- "
        . implode("\n- ", $violations)
    );
});
