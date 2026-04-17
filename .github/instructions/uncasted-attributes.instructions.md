### Missing or Incorrect Model Casts

Scan models for columns that should be cast but are not.

**Why it matters:** Without proper casts, date columns return strings instead of Carbon instances, boolean columns return integers, JSON columns return raw strings, and enum columns return plain strings instead of type-safe PHP Enums. Missing casts cause subtle bugs that only surface in edge cases.

**What to flag:**
- Date/datetime columns not cast to `'datetime'` — look at migration files for `timestamp`, `date`, `dateTime` columns and verify corresponding casts exist
- Boolean columns (`tinyint(1)`, `boolean` in migrations) not cast to `'boolean'`
- JSON columns (`json` in migrations) not cast to `'array'`, `'collection'`, or `AsCollection::class`
- Columns representing a fixed set of values (status, role, type) not cast to a PHP Enum when the project uses PHP 8.1+
- Do NOT flag legacy projects that intentionally avoid casts for compatibility
- Do NOT treat old-but-valid `$casts` property syntax as a problem by itself

**Suggestion:** Define appropriate casts for columns whose runtime type matters: dates, booleans, JSON, and enums. On Laravel 11+, the `casts()` method is the modern style; older `$casts` property syntax is still valid. For status/role/type columns, create a backed PHP Enum and cast to it for type safety and IDE auto-completion when that matches the project's conventions.
