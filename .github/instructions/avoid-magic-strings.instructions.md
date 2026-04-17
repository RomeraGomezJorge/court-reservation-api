### String Constants for Roles, Statuses, Types

Scan for patterns indicating hardcoded string values used as categories:
- Columns compared against string literals: `$user->role === 'admin'`, `$order->status == 'pending'`
- Arrays of string options: `['admin', 'editor', 'viewer']`
- Validation rules with `in:` containing hardcoded values: `'role' => 'in:admin,editor,user'`
- Constants defined as strings in models: `const ROLE_ADMIN = 'admin';`

**Why it matters:** String comparisons are error-prone (typos compile silently), lack IDE auto-completion, and scatter valid values across the codebase. PHP Enums provide type safety, auto-completion, and a single source of truth.

**What to flag:**
- String comparisons against role/status/type values
- `const` definitions that represent a fixed set of options
- Validation `in:` rules with hardcoded option lists

**Suggestion:** Replace with a PHP Enum backed by string values and cast it in the model using `protected $casts`. Use the Enum in validation with the `Enum` rule.
