<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

function translationLocales(): array
{
    return ['en', 'es'];
}

function translationTestFiles(): array
{
    return File::allFiles(base_path('tests'));
}

function applicationFiles(): array
{
    return File::allFiles(app_path());
}

function translationFormRequestFiles(): array
{
    $path = app_path('Http/Requests');

    return File::exists($path) ? File::allFiles($path) : [];
}

function localeTranslationPath(string $locale): string
{
    return lang_path($locale);
}

function localeTranslationFiles(string $locale): array
{
    $path = localeTranslationPath($locale);

    if (! File::exists($path)) {
        return [];
    }

    return array_values(array_filter(
        File::allFiles($path),
        static fn (SplFileInfo $file): bool => $file->getExtension() === 'php'
    ));
}

function localeTranslationFileNames(string $locale): array
{
    $names = array_map(
        static fn ($file): string => $file->getBasename('.php'),
        localeTranslationFiles($locale)
    );

    sort($names);

    return $names;
}

function loadLocaleTranslationFile(string $locale, string $fileName): array
{
    $filePath = lang_path("{$locale}/{$fileName}.php");

    if (! File::exists($filePath)) {
        return [];
    }

    return require $filePath;
}

function localeFileFlattenedKeys(string $locale, string $fileName): array
{
    $keys = array_keys(Arr::dot(loadLocaleTranslationFile($locale, $fileName)));
    sort($keys);

    return $keys;
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

    return array_values(array_filter(array_unique($matches[1] ?? [])));
}

function translationPhpKeyExistsInLocale(string $key, string $locale): bool
{
    if (! Str::contains($key, '.')) {
        return false;
    }

    $fileName = Str::before($key, '.');
    $nestedKey = Str::after($key, '.');

    if ($nestedKey === '') {
        return false;
    }

    return Arr::has(loadLocaleTranslationFile($locale, $fileName), $nestedKey);
}

function extractFormRequestRuleKeys(string $fileContent): array
{
    if (! preg_match('/public function rules\(\): array\s*\{(.*?)\s*}\s*(?:public|protected|private|final|$)/s', $fileContent, $methodMatch)) {
        return [];
    }

    $rulesMethodContent = $methodMatch[1];
    preg_match_all('/[\'"](.*?)[\'"]\s*=>/', $rulesMethodContent, $matches);

    return array_values(array_filter(array_unique($matches[1] ?? [])));
}

function normalizeRuleKeyForValidation(string $key): string
{
    return (string) Str::replace('.*.', '_', $key);
}

it('ensures all translation files in each locale use kebab-case naming', function (): void {
    $violations = [];

    foreach (translationLocales() as $locale) {
        foreach (localeTranslationFiles($locale) as $file) {
            $fileNameWithoutExtension = $file->getBasename('.php');

            if (! isStrictlyKebabCase($fileNameWithoutExtension)) {
                $violations[] = sprintf(
                    '[lang/%s/%s] Expected: "%s.php"',
                    $locale,
                    $file->getFilename(),
                    Str::replace('_', '-', Str::lower($fileNameWithoutExtension))
                );
            }
        }
    }

    expect($violations)->toBeEmpty(
        "The following translation files do not follow the kebab-case naming convention:\n- "
        .implode("\n- ", $violations)
    );
});

it('ensures each locale has the same translation files as English', function (): void {
    $violations = [];
    $englishFiles = localeTranslationFileNames('en');

    foreach (translationLocales() as $locale) {
        if ($locale === 'en') {
            continue;
        }

        $localeFiles = localeTranslationFileNames($locale);
        $missingFiles = array_diff($englishFiles, $localeFiles);
        $extraFiles = array_diff($localeFiles, $englishFiles);

        foreach ($missingFiles as $fileName) {
            $violations[] = sprintf('[lang/%s] Missing file: %s.php', $locale, $fileName);
        }

        foreach ($extraFiles as $fileName) {
            $violations[] = sprintf('[lang/%s] Extra file not in [lang/en]: %s.php', $locale, $fileName);
        }
    }

    expect($violations)->toBeEmpty(
        "Translation file parity issues found:\n- "
        .implode("\n- ", $violations)
    );
});

it('ensures each locale has the same translation keys as English for each file', function (): void {
    $violations = [];
    $englishFiles = localeTranslationFileNames('en');

    foreach (translationLocales() as $locale) {
        if ($locale === 'en') {
            continue;
        }

        foreach ($englishFiles as $fileName) {
            $englishKeys = localeFileFlattenedKeys('en', $fileName);
            $localeKeys = localeFileFlattenedKeys($locale, $fileName);

            if ($localeKeys === []) {
                continue;
            }

            $missingKeys = array_diff($englishKeys, $localeKeys);
            $extraKeys = array_diff($localeKeys, $englishKeys);

            foreach ($missingKeys as $key) {
                $violations[] = sprintf('[lang/%s/%s.php] Missing key: "%s"', $locale, $fileName, $key);
            }

            foreach ($extraKeys as $key) {
                $violations[] = sprintf('[lang/%s/%s.php] Extra key not in [lang/en/%s.php]: "%s"', $locale, $fileName, $fileName, $key);
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Translation key parity issues found:\n- "
        .implode("\n- ", $violations)
    );
});

it('ensures every translation php file returns a non-empty array', function (): void {
    $violations = [];

    foreach (translationLocales() as $locale) {
        foreach (localeTranslationFiles($locale) as $file) {
            $fileName = $file->getBasename('.php');
            $translations = loadLocaleTranslationFile($locale, $fileName);

            if (! is_array($translations)) {
                $violations[] = sprintf(
                    '[lang/%s/%s.php] Translation file must return an array',
                    $locale,
                    $fileName
                );

                continue;
            }

            if ($translations === []) {
                $violations[] = sprintf(
                    '[lang/%s/%s.php] Translation array must not be empty',
                    $locale,
                    $fileName
                );
            }
        }
    }

    expect($violations)->toBeEmpty(
        "The following translation files are invalid (must return a non-empty array):\n- "
        .implode("\n- ", $violations)
    );
});

it('ensures translation values are not empty strings after trim', function (): void {
    $violations = [];

    foreach (translationLocales() as $locale) {
        foreach (localeTranslationFiles($locale) as $file) {
            $fileName = $file->getBasename('.php');
            $translations = loadLocaleTranslationFile($locale, $fileName);

            if ($translations === []) {
                continue;
            }

            foreach (Arr::dot($translations) as $key => $value) {
                if (! is_string($value)) {
                    continue;
                }

                if (mb_trim($value) === '') {
                    $violations[] = sprintf(
                        '[lang/%s/%s.php] Empty translation value at key "%s"',
                        $locale,
                        $fileName,
                        $key
                    );
                }
            }
        }
    }

    expect($violations)->toBeEmpty(
        "The following translation keys have empty values:\n- "
        .implode("\n- ", $violations)
    );
});

it('ensures __() function is not used in tests', function (): void {
    $violations = [];
    $forbiddenHelpers = ['__(', 'trans(', 'Lang::get('];

    foreach (translationTestFiles() as $file) {
        if ($file->getFilename() === basename(__FILE__)) {
            continue;
        }

        if (Str::contains($file->getContents(), $forbiddenHelpers)) {
            $violations[] = $file->getRelativePathname();
        }
    }

    expect($violations)->toBeEmpty(
        "The following test files are using translation helpers (__, trans or Lang::get):\n- "
        .implode("\n- ", $violations)
        ."\n\nReason: Tests should be deterministic. Use hardcoded strings to verify that translations actually render what you expect."
    );
});

it('ensures only __() is used for translations in app folder', function (): void {
    $violations = [];
    $alternativeHelpers = [
        'trans(' => 'trans()',
        'Lang::get(' => 'Lang::get()',
        'trans_choice(' => 'trans_choice()',
    ];

    foreach (applicationFiles() as $file) {
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

it('ensures all translation keys from app folder exist in php translation files', function (): void {
    $violations = [];

    foreach (applicationFiles() as $file) {
        $content = $file->getContents();
        $keysInFile = extractTranslationKeysFromContent($content);

        if ($keysInFile === []) {
            continue;
        }

        foreach ($keysInFile as $key) {
            if (! Str::contains($key, '.')) {
                $violations[] = sprintf(
                    '[%s] Non-dotted translation key "%s" is not allowed (JSON translations are disabled)',
                    $file->getRelativePathname(),
                    $key
                );

                continue;
            }

            foreach (translationLocales() as $locale) {
                if (translationPhpKeyExistsInLocale($key, $locale)) {
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
        "The following translation keys from app code are not defined in locale php files:\n- "
        .implode("\n- ", $violations)
        ."\n\nEnsure all keys used in app code exist in all locale files."
    );
});

it('ensures all FormRequest validation rules have translation attributes', function (): void {
    $violations = [];
    $ignoredFormRequestRuleKeys = ['token', 'id', 'hash'];

    foreach (translationFormRequestFiles() as $file) {
        $content = $file->getContents();
        $ruleKeys = extractFormRequestRuleKeys($content);

        if ($ruleKeys === []) {
            continue;
        }

        foreach (translationLocales() as $locale) {
            $validationTranslations = loadLocaleTranslationFile($locale, 'validation');
            $definedAttributes = $validationTranslations['attributes'] ?? [];
            if (! is_array($definedAttributes)) {
                continue;
            }

            if ($definedAttributes === []) {
                continue;
            }

            foreach ($ruleKeys as $ruleKey) {
                if (in_array($ruleKey, $ignoredFormRequestRuleKeys, true)) {
                    continue;
                }

                $normalizedKey = normalizeRuleKeyForValidation($ruleKey);

                if (is_numeric($normalizedKey)) {
                    continue;
                }

                if (isset($definedAttributes[$ruleKey])) {
                    continue;
                }

                if (isset($definedAttributes[$normalizedKey])) {
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
