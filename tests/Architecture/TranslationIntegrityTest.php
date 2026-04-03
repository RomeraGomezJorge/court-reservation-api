<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

/**
 * Get all translation files for a specific locale.
 */
function translationFiles(string $locale): array
{
    $path = lang_path($locale);

    return File::exists($path) ? File::allFiles($path) : [];
}

/**
 * Architecture test to ensure translation parity between Spanish and English.
 * This prevents missing strings in the primary language (English)
 * when new features are added in Spanish.
 */
it('ensures all translation keys in Spanish files are also defined in English files', function (): void {
    // Keys to ignore. Format: 'filename.key' or 'filename.nested.key'
    $ignoredKeys = [
        // 'auth.only_in_spanish',
    ];

    $violations = [];
    $esFiles = translationFiles('es');

    foreach ($esFiles as $file) {
        $fileName = $file->getFilename();
        $baseName = $file->getBasename('.php');
        $enFilePath = lang_path("en/{$fileName}");

        // 1. Check if the corresponding English file exists
        if (! File::exists($enFilePath)) {
            $violations[] = "The English file [lang/en/{$fileName}] is missing.";
            continue;
        }

        $esTranslations = Arr::dot(require $file->getPathname());
        $enTranslations = Arr::dot(require $enFilePath);

        foreach (array_keys($esTranslations) as $key) {
            $fullKeyPath = "{$baseName}.{$key}";

            // Skip if the key is explicitly ignored
            if (in_array($fullKeyPath, $ignoredKeys, true)) {
                continue;
            }

            // 2. Check if the key exists in the English file
            if (! array_key_exists($key, $enTranslations)) {
                $violations[] = "[lang/en/{$fileName}] is missing the key: \"{$key}\"";
            }
        }
    }

    expect($violations)->toBeEmpty(
        "The following translation inconsistencies were found:\n- "
        . implode("\n- ", $violations)
        . "\n\nEnsure all Spanish keys have a corresponding English translation to maintain localization integrity."
    );
});
