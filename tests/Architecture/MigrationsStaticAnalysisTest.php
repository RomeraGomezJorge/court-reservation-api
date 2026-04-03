<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

function migrationFiles(): array
{
    return File::allFiles(database_path('migrations'));
}

function fileContainsAny(string $content, array $needles): bool
{
    return Str::contains($content, $needles);
}

it('migrations must not use eloquent models', function () {

    $forbidden = [
        'App\\Models',
    ];

    $violations = [];

    foreach (migrationFiles() as $file) {
        $content = $file->getContents();

        if (fileContainsAny($content, $forbidden)) {
            $violations[] = $file->getRelativePathname();
        }
    }

    expect($violations)->toBeEmpty(
        "The following migration files use Eloquent models and violate the architecture convention:\n- "
        .implode("\n- ", $violations)
        ."\nIf a case is truly unavoidable, discuss it first and document an explicit exception."
    );
});

it('migrations must not use factories', function () {

    $forbidden = [
        'Database\\Factories',
        '::factory(',
        'factory(',
    ];

    $violations = [];

    foreach (migrationFiles() as $file) {
        $content = $file->getContents();

        if (fileContainsAny($content, $forbidden)) {
            $violations[] = $file->getRelativePathname();
        }
    }

    expect($violations)->toBeEmpty(
        "The following migration files use factories and violate the architecture convention:\n- "
        .implode("\n- ", $violations)
        ."\nMigrations must stay deterministic; if this should be an exception, document it explicitly."
    );
});

it('migrations must not use enums', function () {

    $forbidden = [
        'App\\Enums',
        'enum ',
    ];

    $violations = [];

    foreach (migrationFiles() as $file) {
        $content = $file->getContents();

        if (fileContainsAny($content, $forbidden)) {
            $violations[] = $file->getRelativePathname();
        }
    }

    expect($violations)->toBeEmpty(
        "The following migration files use PHP enums and violate the architecture convention:\n- "
        .implode("\n- ", $violations)
        ."\nAvoid enums in migrations to keep them database-agnostic; add a documented exception only if required."
    );
});

it('ensures no migrations use database enums', function (): void {
    $violations = [];

    foreach (migrationFiles() as $file) {
        $content = $file->getContents();

        if (fileContainsAny(Str::lower($content), ['->enum('])) {
            $violations[] = $file->getRelativePathname();
        }
    }

    expect($violations)->toBeEmpty(
        "The following migration files use database enums (->enum()) and violate the architecture convention:\n- "
        .implode("\n- ", $violations)
        ."\nUse strings plus application-level validation/casting, or document an explicit exception if truly required."
    );
});

it('ensures no migrations use default values except whitelisted ones', function (): void {
    $whitelistedMigrationFiles = [
        // Add relative migration file names here when a default value is explicitly approved.
    ];

    $violations = [];

    foreach (migrationFiles() as $file) {
        $relativePath = $file->getRelativePathname();

        if (in_array($relativePath, $whitelistedMigrationFiles, true)) {
            continue;
        }

        $content = $file->getContents();

        if (fileContainsAny(Str::lower($content), ['->default('])) {
            $violations[] = $relativePath;
        }
    }

    expect($violations)->toBeEmpty(
        "The following migration files use default values (->default()) and violate the architecture convention:\n- "
        .implode("\n- ", $violations)
        ."\nIf a default is intentionally required, add the migration file to the whitelist explicitly."
    );
});

it('migrations must not define default values except allowed ones', function () {

    $allowedDefaults = [
        'CURRENT_TIMESTAMP',
        'now()',
    ];

    $violations = [];

    foreach (migrationFiles() as $file) {
        $content = $file->getContents();

        preg_match_all('/->default\((.*?)\)/', $content, $matches);

        foreach ($matches[1] as $default) {
            $normalized = mb_trim($default, " \t\n\r\0\x0B'\"");

            if (! in_array($normalized, $allowedDefaults, true)) {
                $violations[] = sprintf(
                    '%s -> default(%s)',
                    $file->getRelativePathname(),
                    $default
                );
            }
        }
    }

    expect($violations)->toBeEmpty(
        "The following migration defaults are not in the allowed list:\n- "
        .implode("\n- ", $violations)
        ."\nIf a default is valid for the project, add it explicitly to the allowed defaults list."
    );
});

/**
 * Test: Table Naming (Plural).
 * Ensures that standard tables follow the plural naming convention.
 * Includes an exclusion list for third-party or legacy tables.
 */
it('ensures all database tables are named in plural', function (): void {
    // Add third-party or legacy tables here that cannot be changed
    $excludedTables = [
        'cache',           // Laravel default cache table
    ];

    $violations = [];

    foreach (migrationFiles() as $file) {
        $content = $file->getContents();

        // Regex to capture the table name in Schema::create or Schema::table
        if (preg_match('/Schema::(?:create|table)\s*\(\s*[\'"](.+?)[\'"]/', $content, $matches)) {
            $tableName = $matches[1];

            // 1. Skip explicitly excluded tables
            if (in_array($tableName, $excludedTables, true)) {
                continue;
            }

            // 2. Skip pivot tables (containing underscore) as they have their own test/rules
            if (Str::contains($tableName, '_')) {
                continue;
            }

            // 3. Validate pluralization
            if (Str::plural($tableName) !== $tableName) {
                $violations[] = "[{$file->getRelativePathname()}] -> Table '{$tableName}' should be plural.";
            }
        }
    }

    expect($violations)->toBeEmpty(
        "The following database tables are not named in plural and are not in the exclusion list:\n- "
        . implode("\n- ", $violations)
        . "\n\nNote: If this table comes from a third-party package, add it to the \$excludedTables array in this test."
    );
});

/**
 * Test: Pivot Table Naming (Singular & Alphabetical).
 * Ensures pivot tables follow: [singular_model_a]_[singular_model_b] in alphabetical order.
 */
it('ensures pivot tables use singular model names in alphabetical order', function (): void {
    $violations = [];

    foreach (migrationFiles() as $file) {
        $content = $file->getContents();

        if (preg_match('/Schema::create\s*\(\s*[\'"](.+?)[\'"]/', $content, $matches)) {
            $tableName = $matches[1];

            // Only analyze tables that look like pivots (containing underscore)
            if (! Str::contains($tableName, '_')) {
                continue;
            }

            $parts = explode('_', $tableName);

            // 1. Check if all parts are singular
            foreach ($parts as $part) {
                if (Str::singular($part) !== $part) {
                    $violations[] = "[{$file->getRelativePathname()}] -> Pivot part '{$part}' in '{$tableName}' must be singular.";
                }
            }

            // 2. Check for alphabetical order
            $sortedParts = $parts;
            sort($sortedParts);

            if ($parts !== $sortedParts) {
                $violations[] = "[{$file->getRelativePathname()}] -> Pivot '{$tableName}' should be ordered alphabetically: " . implode('_', $sortedParts);
            }
        }
    }

    expect($violations)->toBeEmpty("Pivot table naming conventions violated:\n- " . implode("\n- ", $violations));
});

/**
 * Test: Table Column Naming (snake_case & No redundancy).
 * Ensures columns follow snake_case and avoid repeating the singular table name.
 * * Example: In 'users' table, use 'email' instead of 'user_email'.
 */
it('ensures table columns are snake_case and do not include the model name', function (): void {
    // Columns that are allowed to bypass these rules (e.g., third-party package requirements)
    $excludedColumns = [
        // 'table_name.column_name',
        'sessions.user_id', // Laravel default
    ];

    $violations = [];

    foreach (migrationFiles() as $file) {
        $content = $file->getContents();

        // Identify the table name from the migration
        preg_match('/Schema::(?:create|table)\s*\(\s*[\'"](.+?)[\'"]/', $content, $tableMatches);
        $tableName = $tableMatches[1] ?? '';
        $singularTable = Str::singular($tableName);

        // Regex to capture column names across common Laravel blueprint methods
        preg_match_all('/->(?:string|integer|bigInteger|text|boolean|date|datetime|timestamp|decimal|float|json|uuid)\s*\(\s*[\'"](.+?)[\'"]/', $content, $matches);

        foreach ($matches[1] as $column) {
            $identifier = "{$tableName}.{$column}";

            if (in_array($identifier, $excludedColumns, true)) {
                continue;
            }

            // 1. Validate snake_case
            if (Str::snake($column) !== $column || Str::contains($column, '-')) {
                $suggested = Str::snake($column);
                $violations[] = sprintf(
                    "[%s]\n   - Error: Column '%s' is not snake_case.\n   - Fix: Rename to '%s'.",
                    $file->getRelativePathname(),
                    $column,
                    $suggested
                );
            }

            // 2. Validate Redundancy
            // We ignore foreign keys (ending in _id) because they MUST contain the model name.
            if ($singularTable !== '' && ! Str::endsWith($column, '_id')) {
                if (Str::startsWith($column, $singularTable . '_')) {
                    $suggested = Str::after($column, $singularTable . '_');
                    $violations[] = sprintf(
                        "[%s]\n   - Error: Column '%s' in table '%s' is redundant.\n   - Fix: Rename to '%s' (remove the '%s_' prefix).",
                        $file->getRelativePathname(),
                        $column,
                        $tableName,
                        $suggested,
                        $singularTable
                    );
                }
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Database Schema Architecture Violations:\n\n"
        . implode("\n\n", $violations)
        . "\n\nNote: Keep your schema DRY. A column in the 'products' table doesn't need to be 'product_title'; 'title' is sufficient."
    );
});
