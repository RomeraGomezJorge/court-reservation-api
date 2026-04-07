<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;



function testFiles(): array
{
    return File::allFiles(base_path('tests'));
}

function appFiles(): array
{
    return File::allFiles(app_path());
}

function formRequestFiles(): array
{
    $path = app_path('Http/Requests');
    return File::exists($path) ? File::allFiles($path) : [];
}

function isStrictlyKebabCase(string $value): bool
{
    if (Str::contains($value, '_') || mb_strtolower($value) !== $value) {
        return false;
    }

    return Str::kebab($value) === $value;
}


function extractTranslationKeysFromContent(string $content): array
{
    if (! preg_match_all('/__\(\s*[\'\"]([^\'\"]+)[\'\"](?:\s*,[^)]*)?\)/', $content, $matches)) {
        return [];
    }

    return array_filter(array_unique($matches[1] ?? []));
}

function loadLanguageFile(string $locale, string $fileName): array
{
    $filePath = lang_path("{$locale}/{$fileName}.php");

    if (! File::exists($filePath)) {
        return [];
    }

    return require $filePath;
}

function loadJsonLanguageFile(string $locale): array
{
    $filePath = lang_path("{$locale}.json");

    if (! File::exists($filePath)) {
        return [];
    }

    $translations = json_decode((string) File::get($filePath), true);

    return is_array($translations) ? $translations : [];
}

function translationKeyExistsInLocale(string $key, string $locale): bool
{
    if (! Str::contains($key, '.')) {
        return array_key_exists($key, loadJsonLanguageFile($locale));
    }

    $fileName = Str::before($key, '.');
    $nestedKey = Str::after($key, '.');
    $translations = loadLanguageFile($locale, $fileName);

    return $nestedKey !== '' && Arr::has($translations, $nestedKey);
}

function extractFormRequestRuleKeys(string $fileContent): array
{
    if (! preg_match('/public function rules\(\): array\s*\{(.*?)\s*}\s*(?:public|protected|private|final|$)/s', $fileContent, $methodMatch)) {
        return [];
    }

    $rulesMethodContent = $methodMatch[1];
    preg_match_all('/[\'"](.*?)[\'"]\s*=>/', $rulesMethodContent, $matches);

    return array_filter(array_unique($matches[1] ?? []));
}

function normalizeRuleKeyForValidation(string $key): string
{
    return (string) Str::replace('.*.', '_', $key);
}

function isValidLaravelRuleKey(string $value): bool
{
    $segments = explode('.', $value);

    foreach ($segments as $segment) {
        if ($segment === '*') {
            continue;
        }

        if (! isStrictlySnakeCase($segment)) {
            return false;
        }
    }

    return true;
}


function detectForbiddenTranslationHelpers(string $content): bool
{
    $forbiddenHelpers = ['trans(', 'Lang::get(', 'trans_choice('];
    return Str::contains($content, $forbiddenHelpers);
}




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
        .implode("\n- ", $violations)
    );
});

it('ensures all translation keys in Spanish files are also defined in English files', function (): void {
    $violations = [];
    $esPath = lang_path('es');

    if (! File::exists($esPath)) {
        return;
    }

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
        "Translation parity issues found:\n- ".implode("\n- ", $violations)
    );
});

it('ensures __() function is not used in tests', function (): void {
    $violations = [];

    $forbiddenHelpers = [
        '__(',
        'trans(',
        'Lang::get(',
    ];

    foreach (testFiles() as $file) {
        if ($file->getFilename() === basename(__FILE__)) {
            continue;
        }

        $content = $file->getContents();

        if (Str::contains($content, $forbiddenHelpers)) {
            $violations[] = $file->getRelativePathname();
        }
    }

    expect($violations)->toBeEmpty(
        "The following test files are using translation helpers (__, trans or Lang::get):\n- "
        .implode("\n- ", $violations)
        ."\n\nReason: Tests should be deterministic. Use hardcoded strings to verify that translations actually render what you expect."
    );
});

it('ensures only __() is used for translations in app code', function (): void {
    $violations = [];

    $alternativeHelpers = [
        'trans(' => 'trans()',
        'Lang::get(' => 'Lang::get()',
        'trans_choice(' => 'trans_choice()',
    ];

    foreach (appFiles() as $file) {
        $content = $file->getContents();

        if (! Str::contains($content, array_keys($alternativeHelpers))) {
            continue;
        }

        foreach ($alternativeHelpers as $pattern => $helperName) {
            if (Str::contains($content, $pattern)) {
                $violations[] = sprintf(
                    '[%s] Uses forbidden helper: %s',
                    $file->getRelativePathname(),
                    $helperName
                );
            }
        }
    }

    expect($violations)->toBeEmpty(
        "The following app files are using alternative translation helpers instead of __():\n- "
        .implode("\n- ", $violations)
        ."\n\nStandardize on __() for all translations in the app."
    );
});

it('ensures all translation keys from App folder are defined in lang files', function (): void {
    $violations = [];
    $locales = ['en', 'es'];


    foreach (appFiles() as $file) {
        $content = $file->getContents();
        $keysInFile = extractTranslationKeysFromContent($content);

        if (empty($keysInFile)) {
            continue;
        }

        foreach ($keysInFile as $key) {
            foreach ($locales as $locale) {
                if (translationKeyExistsInLocale($key, $locale)) {
                    continue;
                }

                $violations[] = sprintf(
                    '[%s] Key "%s" not found in [lang/%s/]',
                    $file->getRelativePathname(),
                    $key,
                    $locale
                );

                break;
            }
        }
    }

    expect($violations)->toBeEmpty(
        "The following translation keys from app code are not defined in lang files:\n- "
        .implode("\n- ", $violations)
        ."\n\nEnsure all keys used in app code exist in all language files."
    );
});

it('ensures all FormRequest validation rules have translation attributes', function (): void {
    $violations = [];
    $locales = ['en', 'es'];
    $ignoredFormRequestRuleKeys = ['token', 'id', 'hash'];

    foreach (formRequestFiles() as $file) {
        $content = $file->getContents();
        $ruleKeys = extractFormRequestRuleKeys($content);

        if (empty($ruleKeys)) {
            continue;
        }

        foreach ($locales as $locale) {
            $validationTranslations = loadLanguageFile($locale, 'validation');

            if (empty($validationTranslations['attributes'] ?? [])) {
                continue;
            }

            $definedAttributes = $validationTranslations['attributes'];

            foreach ($ruleKeys as $ruleKey) {
                if (in_array($ruleKey, $ignoredFormRequestRuleKeys, true)) {
                    continue;
                }

                $normalizedKey = normalizeRuleKeyForValidation($ruleKey);

                if (is_numeric($normalizedKey)) {
                    continue;
                }

                if (isset($definedAttributes[$ruleKey]) || isset($definedAttributes[$normalizedKey])) {
                    continue;
                }

                $violations[] = sprintf(
                    '[%s][%s] Rule key "%s" not in [lang/%s/validation.php][attributes]',
                    $file->getRelativePathname(),
                    $locale,
                    $ruleKey,
                    $locale
                );
            }
        }
    }

    expect($violations)->toBeEmpty(
        "The following FormRequest validation rules do not have translation attributes:\n- "
        .implode("\n- ", $violations)
        ."\n\nAdd missing attributes to lang/*/validation.php under the 'attributes' array."
    );
});

