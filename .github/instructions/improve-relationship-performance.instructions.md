### Inefficient Relationship Patterns

Scan for relationship usage patterns that have simpler or more performant Eloquent alternatives.

**Why it matters:** Laravel offers specialized relationship methods that reduce query count and code complexity. Using `whereHas()` + `with()` with the same closure is duplicated logic that `withWhereHas()` solves in one call. Counting with `$user->posts()->count()` runs a query per user when `withCount('posts')` does it in one subquery. Loading all related records to find the latest one wastes memory when `latestOfMany()` returns a single record.

**What to flag:**
- `whereHas()` and `with()` using the same closure — replace with `withWhereHas()`
- `->count()` called on relationship methods inside loops — replace with `withCount()`
- Loading a full `hasMany` relationship then accessing only the first/last record — use `latestOfMany()`, `oldestOfMany()`, or `ofMany()`
- Manual `where('user_id', $user->id)` instead of `whereBelongsTo($user)`
- Deeply nested eager loading (`with('projects.tasks')`) when only the deepest level is needed — consider `hasManyThrough()`
- Do NOT flag patterns where the simpler alternative does not exist for the Laravel version in use

**Suggestion:** Use `withWhereHas()` to combine filtering and eager loading in one call. Use `withCount()` / `withSum()` / `withAvg()` for aggregates instead of loading full relationships. Define `latestOfMany()` / `oldestOfMany()` relationships for single-record access from has-many. Use `whereBelongsTo($model)` for cleaner belongs-to filtering.

