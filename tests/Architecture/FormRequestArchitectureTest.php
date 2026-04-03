<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

function formRequestFiles(): array
{
    $path = app_path('Http/Requests');

    return File::exists($path) ? File::allFiles($path) : [];
}

it('ensures all form request files have the Request suffix', function (): void {
    $violations = [];

    foreach (formRequestFiles() as $file) {
        $fileName = $file->getBasename('.php');

        // Analyzed from Laravel 12.x docs: Str::endsWith is the cleanest way
        if (! Str::endsWith($fileName, 'Request')) {
            $violations[] = $file->getRelativePathname();
        }
    }

    expect($violations)->toBeEmpty(
        "The following files in App/Http/Requests are missing the 'Request' suffix:\n- "
        . implode("\n- ", $violations)
        . "\n\nStandardize naming to [Action][Entity]Request.php (e.g., UpdateProfileRequest.php)."
    );
});

it('ensures all validation keys in FormRequests are strictly snake_case', function (): void {
    $violations = [];

    foreach (formRequestFiles() as $file) {
        $content = $file->getContents();

        // 1. Extract only the rules() method content to avoid false positives in other methods
        // This regex captures everything inside public function rules(): array { ... }
        if (! preg_match('/public function rules\(\): array\s*\{(.*?)\s*\}\s*(?:public|protected|private|final|$)/s', $content, $methodMatch)) {
            continue;
        }

        $rulesMethodContent = $methodMatch[1];

        // 2. Capture keys inside the rules array context
        // We look for 'key' => or "key" =>
        preg_match_all('/[\'"](.*?)[\'"]\s*=>/', $rulesMethodContent, $matches);

        $keys = $matches[1] ?? [];

        foreach ($keys as $key) {
            // We ignore numeric keys and wildcard patterns (e.g., working_days.*.day)
            // For wildcards, we replace the .* with _ to validate the snake_case logic
            $keyToValidate = (string) Str::replace('.*.', '_', (string) $key);

            if (! isStrictlySnakeCase($keyToValidate)) {
                $violations[] = sprintf(
                    '[%s] -> invalid key: "%s"',
                    $file->getRelativePathname(),
                    $key
                );
            }
        }
    }

    expect($violations)->toBeEmpty(
        "The following FormRequest validation keys do not follow the strict snake_case convention:\n- "
        . implode("\n- ", $violations)
        . "\n\nNote: This check is now isolated to the rules() method."
    );
});
