<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Get all translation files from the lang directory.
 */
function allLangFiles(): array
{
    return File::allFiles(lang_path());
}

/**
 * Check if a filename is strictly kebab-case.
 * A senior approach: check for forbidden characters in this convention (underscores and capitals).
 */
function isStrictlyKebabCase(string $value): bool
{
    // If it contains underscores, it's snake_case, not kebab-case.
    if (Str::contains($value, '_')) {
        return false;
    }

    // If it's not all lowercase, it's not standard kebab-case.
    if (strtolower($value) !== $value) {
        return false;
    }

    // Finally, ensure it matches Laravel's kebab conversion outcome
    return Str::kebab($value) === $value;
}

// --- Updated Test ---

it('ensures all translation files in lang/ use kebab-case naming', function (): void {
    $violations = [];

    foreach (allLangFiles() as $file) {
        $fileNameWithoutExtension = $file->getBasename('.php');

        if (! isStrictlyKebabCase($fileNameWithoutExtension)) {
            $violations[] = sprintf(
                '[%s] is using invalid naming. Expected: "%s.php"',
                $file->getRelativePathname(),
                Str::replace('_', '-', Str::lower($fileNameWithoutExtension))
            );
        }
    }

    expect($violations)->toBeEmpty(
        "The following translation files do not follow the kebab-case naming convention:\n- "
        . implode("\n- ", $violations)
        . "\n\nSenior Note: Use hyphens (-) instead of underscores (_) for file names in the lang directory."
    );
});
