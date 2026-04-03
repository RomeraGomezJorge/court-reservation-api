<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use NunoMaduro\Essentials\Configurables\Unguard;

function configFiles(): array
{
    return File::allFiles(config_path());
}

it('ensures all config keys are in snake_case', function (): void {
    // Add full dotted keys or specific segments that should be excluded from this rule.
    $ignoredKeys = [
        Unguard::class,
        'channels.papertrail.handler_with.connectionString',
    ];

    $violations = [];

    foreach (configFiles() as $file) {
        $relativePath = $file->getRelativePathname();

        // We use 'require' to get the actual array from the config file.
        $config = require $file->getPathname();

        if (! is_array($config)) {
            continue;
        }

        // Flatten the array using dot notation to easily iterate over all nested keys.
        $flattened = Arr::dot($config);

        foreach (array_keys($flattened) as $fullKey) {
            // Skip if the full key is explicitly ignored.
            info($fullKey);
            if (in_array($fullKey, $ignoredKeys, true)) {
                continue;
            }

            $segments = explode('.', (string) $fullKey);

            foreach ($segments as $segment) {
                // We ignore numeric keys (e.g. lists of providers or aliases).
                if (is_numeric($segment)) {
                    continue;
                }

                if (! isSnakeCase($segment)) {
                    $violations[] = sprintf(
                        '[%s] -> key: "%s" ',
                        $relativePath,
                        $segment,
                    );
                }
            }
        }
    }

    expect($violations)->toBeEmpty(
        "The following configuration keys do not follow the snake_case convention:\n- "
        .implode("\n- ", $violations)
        ."\n\nIf these are required by a third-party package, add them to the ignoredKeys array."
    );
});

/**
 * Check if a string is strictly snake_case.
 */
function isSnakeCase(string $value): bool
{
    // If the snake_case version is not identical to the original,
    // it means it contains capital letters or invalid formatting.
    return Str::snake($value) === $value;
}
