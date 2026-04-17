### Authorization Logic Inline Instead of Policies

Scan controllers for manual permission/ownership checks.

**Why it matters:** Authorization scattered across controllers is easy to forget in new endpoints. Policies centralize authorization per model, are auto-discovered in Laravel 11+, and integrate with `Gate`, `@can` in Blade, and `can` middleware.

**What to flag:**
- `if ($user->id !== $post->user_id) abort(403)` or similar ownership checks
- `abort_if`/`abort_unless` with permission-like conditions
- `if (!$user->isAdmin())` type checks in controller methods
- Same authorization logic repeated across multiple controller methods

**Suggestion:** Create a Policy using `vendor/bin/sail artisan make:policy PostPolicy --model=Post`. Use `Gate::authorize('update', $post)` in controllers, `@can('update', $post)` in Blade, or `->can('update,post')` in route definitions. Policies are auto-discovered in Laravel 11+ — no manual registration needed.
