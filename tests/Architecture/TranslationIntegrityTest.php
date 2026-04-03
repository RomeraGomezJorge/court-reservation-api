<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Helper to get all files from the tests directory.
 */
function testFiles(): array
{
    return File::allFiles(base_path('tests'));
}

/**
 * Check if a filename is strictly kebab-case.
 */
function isStrictlyKebabCase(string $value): bool
{
    if (Str::contains($value, '_') || strtolower($value) !== $value) {
        return false;
    }

    return Str::kebab($value) === $value;
}

// --- Architecture Tests ---

it('ensures all translation files in lang/ use kebab-case naming', function (): void {
    $violations = [];

    foreach (File::allFiles(lang_path()) as $file) {
        $fileNameWithoutExtension = $file->getBasename('.php');

        if (! isStrictlyKebabCase($fileNameWithoutExtension)) {
            $violations[] = sprintf(
                '[%s] Expected: "%s.php"',
                $file->getRelativePathname(),
                Str::replace('_', '-', Str::lower($fileNameWithoutExtension))
            );
        }
    }

    expect($violations)->toBeEmpty(
        "The following translation files do not follow the kebab-case naming convention:\n- "
        . implode("\n- ", $violations)
    );
});

it('ensures all translation keys in Spanish files are also defined in English files', function (): void {
    $violations = [];
    $esPath = lang_path('es');

    if (! File::exists($esPath)) return;

    foreach (File::allFiles($esPath) as $file) {
        $fileName = $file->getFilename();
        $enFilePath = lang_path("en/{$fileName}");

        if (! File::exists($enFilePath)) {
            $violations[] = "Missing English file: [lang/en/{$fileName}]";
            continue;
        }

        $esKeys = array_keys(Arr::dot(require $file->getPathname()));
        $enKeys = array_keys(Arr::dot(require $enFilePath));

        foreach ($esKeys as $key) {
            if (! in_array($key, $enKeys, true)) {
                $violations[] = "[lang/en/{$fileName}] is missing key: \"{$key}\"";
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Translation parity issues found:\n- " . implode("\n- ", $violations)
    );
});

it('ensures __() function is not used in tests', function (): void {
    $violations = [];

    // Define the translation helpers we want to forbid in tests
    $forbiddenHelpers = [
        '__(',
        'trans(',
        'Lang::get(',
    ];

    foreach (testFiles() as $file) {
        // Skip the architecture test itself to avoid self-flagellation
        if ($file->getFilename() === basename(__FILE__)) {
            continue;
        }

        $content = $file->getContents();

        // Using Laravel's Str::contains helper
        // It accepts an array and returns true if ANY of the strings are found
        if (Str::contains($content, $forbiddenHelpers)) {
            $violations[] = $file->getRelativePathname();
        }
    }

    expect($violations)->toBeEmpty(
        "The following test files are using translation helpers (__, trans or Lang::get):\n- "
        . implode("\n- ", $violations)
        . "\n\nReason: Tests should be deterministic. Use hardcoded strings to verify that translations actually render what you expect."
    );
});
