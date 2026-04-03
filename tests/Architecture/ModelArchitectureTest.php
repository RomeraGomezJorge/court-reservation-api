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
        if (preg_match('/class\s+([a-zA-Z0-9_]+)/', $content, $matches)) {
            $className = $matches[1];
            if (Str::singular($className) !== $className) {
                $violations[] = "Class [{$className}] in [{$file->getRelativePathname()}] should be singular.";
            }
        }
    }

    expect($violations)->toBeEmpty("Model naming must be singular.\n- " . implode("\n- ", $violations));
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
            $content,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $methodName = $match[1];
            $relationType = $match[2];

            if (in_array($relationType, ['hasOne', 'belongsTo'])) {
                // Singular case
                if (Str::singular($methodName) !== $methodName) {
                    $violations[] = "[{$file->getRelativePathname()}] -> Method '{$methodName}' ({$relationType}) should be singular.";
                }
            } else {
                // Plural case
                if (Str::plural($methodName) !== $methodName) {
                    $violations[] = "[{$file->getRelativePathname()}] -> Method '{$methodName}' ({$relationType}) should be plural.";
                }
            }
        }
    }

    expect($violations)->toBeEmpty("Relationship naming conventions violated:\n- " . implode("\n- ", $violations));
});

/**
 * Test: Model Properties in snake_case.
 * Ensures that any manual property definitions or array keys (fillable, casts, etc.)
 * follow the snake_case convention.
 */
it('ensures model properties and attributes are in snake_case', function (): void {
    $violations = [];

    foreach (modelFiles() as $file) {
        $content = $file->getContents();

        // 1. Validate Accessors/Mutators naming logic
        // Note: Laravel uses CamelCase for method names (getFooAttribute),
        // but we ensure the intended attribute is logically snake_case.
        preg_match_all('/public function (get|set)([a-zA-Z0-9_]+)Attribute/', $content, $matches);

        // 2. Validate keys in $fillable, $casts, or method arrays
        // Detects patterns like 'is_active' => or "phone_number" =>
        preg_match_all('/[\'"]([a-zA-Z0-9_]+)[\'"]\s*=>/', $content, $matches);

        foreach ($matches[1] as $property) {
            if (is_numeric($property)) {
                continue;
            }

            // Reject CamelCase or kebab-case (-) in property naming
            if (Str::snake($property) !== $property || Str::contains($property, '-')) {
                $violations[] = "[{$file->getRelativePathname()}] -> Property/Key '{$property}' should be snake_case.";
            }
        }
    }

    expect($violations)->toBeEmpty("Model properties must be snake_case.\n- " . implode("\n- ", $violations));
});
