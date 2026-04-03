<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Helper to retrieve all model files from the App/Models directory.
 */
function modelFiles(): array
{
    $path = app_path('Models');

    return File::exists($path) ? File::allFiles($path) : [];
}

/**
 * Test: Class and File Naming (Singular).
 * Ensures that both the physical file and the class definition follow
 * Laravel's singular naming convention for Eloquent models.
 */
it('ensures model classes and files are named in singular', function (): void {
    $violations = [];

    foreach (modelFiles() as $file) {
        $fileName = $file->getBasename('.php');
        $content = $file->getContents();

        // Check if the filename itself is singular
        if (Str::singular($fileName) !== $fileName) {
            $violations[] = "File [{$file->getRelativePathname()}] should be singular.";
        }

        // Check if the class name defined inside the file is singular
        if (preg_match('/class\s+(\w+)/', (string) $content, $matches)) {
            $className = $matches[1];
            if (Str::singular($className) !== $className) {
                $violations[] = "Class [{$className}] in [{$file->getRelativePathname()}] should be singular.";
            }
        }
    }

    expect($violations)->toBeEmpty("Model naming must be singular.\n- ".implode("\n- ", $violations));
});

/**
 * Test: Relationship Naming Conventions.
 * One-to-One/Belongs-To relationships must be singular.
 * All other relationships (collections) must be plural.
 */
it('ensures model relationships follow singular/plural conventions', function (): void {
    $violations = [];

    foreach (modelFiles() as $file) {
        $content = $file->getContents();

        /**
         * Improved Regex:
         * 1. Uses negative lookahead (?!casts) to ignore the Laravel 11/12 casts() method.
         * 2. Captures the method name and the Eloquent relationship type.
         * 3. Supports optional return types for modern PHP standards.
         */
        preg_match_all(
            '/public function\s+(?!casts)([a-zA-Z0-9_]+)\s*\(.*?\)\s*(?::\s*[a-zA-Z0-9_|]+)?\s*\{\s*return\s+\$this->(hasOne|belongsTo|hasMany|belongsToMany|morphMany|morphToMany|hasManyThrough)\(/s',
            (string) $content,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $methodName = $match[1];
            $relationType = $match[2];

            if (in_array($relationType, ['hasOne', 'belongsTo'], true)) {
                // Singular case
                if (Str::singular($methodName) !== $methodName) {
                    $violations[] = "[{$file->getRelativePathname()}] -> Method '{$methodName}' ({$relationType}) should be singular.";
                }
            } elseif (Str::plural($methodName) !== $methodName) {
                // Plural case
                $violations[] = "[{$file->getRelativePathname()}] -> Method '{$methodName}' ({$relationType}) should be plural.";
            }
        }
    }

    expect($violations)->toBeEmpty("Relationship naming conventions violated:\n- ".implode("\n- ", $violations));
});

/**
 * Test: Model Properties in snake_case.
 * Updated to capture both list values (like $fillable) and associative keys (like $casts).
 */
it('ensures model properties and attributes are in snake_case', function (): void {
    $violations = [];

    foreach (modelFiles() as $file) {
        $content = $file->getContents();

        /**
         * This regex captures strings inside quotes that are either:
         * 1. Followed by => (associative keys)
         * 2. Followed by , or ] (list values like in $fillable)
         */
        preg_match_all('/[\'"]([a-zA-Z0-9_\-]+)[\'"]\s*(?==>|,|\])/', (string) $content, $matches);

        $potentialProperties = array_unique($matches[1] ?? []);

        foreach ($potentialProperties as $property) {
            // Ignore numeric strings and known standard non-snake methods/classes
            if (is_numeric($property)) {
                continue;
            }
            if (in_array($property, ['string', 'integer', 'boolean', 'datetime', 'float'])) {
                continue;
            }
            // Check for CamelCase, kebab-case, or any non-snake_case format
            if (Str::snake($property) !== $property || Str::contains($property, '-')) {
                $violations[] = "[{$file->getRelativePathname()}] -> Property/Key '{$property}' should be snake_case.";
            }
        }
    }

    expect($violations)->toBeEmpty("Model properties must be snake_case.\n- ".implode("\n- ", $violations));
});

/**
 * Test: Mass Assignment Protection.
 * Ensures all models define a $fillable property to avoid security risks
 * and explicitly define their allowed attributes.
 */
it('ensures all models have a fillable property to define their attributes', function (): void {
    $violations = [];

    foreach (modelFiles() as $file) {
        $content = $file->getContents();

        // Check for the existence of 'protected $fillable' or 'protected array $fillable'
        if (! Str::contains($content, 'protected $fillable') && ! Str::contains($content, 'protected array $fillable')) {
            $violations[] = $file->getRelativePathname();
        }
    }

    expect($violations)->toBeEmpty(
        "The following models are missing a \$fillable property:\n- "
        .implode("\n- ", $violations)
        ."\n\nNote: Always use \$fillable for mass assignment protection."
    );
});

/**
 * Test: Query Logic Decoupling.
 * Forbids the use of 'scopeName' methods in favor of Custom Query Builders.
 * This keeps the Model clean and improves type-hinting.
 */
it('ensures that models do not use scopes and use custom query builders instead', function (): void {
    $violations = [];

    foreach (modelFiles() as $file) {
        $content = $file->getContents();

        // Regex to find methods starting with 'scope' (e.g., scopeActive)
        if (preg_match('/public function scope[A-Z]/', (string) $content)) {
            $violations[] = $file->getRelativePathname();
        }
    }

    expect($violations)->toBeEmpty(
        "The following models are using 'scope' methods:\n- "
        .implode("\n- ", $violations)
        ."\n\nNote: Remove scopes and move the logic to a Custom Query Builder by overriding the newQuery() method."
    );
});
