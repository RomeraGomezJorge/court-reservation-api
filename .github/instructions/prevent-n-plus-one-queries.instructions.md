### N+1 Query Problems

Scan controllers, Livewire components, services, and Blade templates for relationship access without eager loading.

**Why it matters:** The N+1 problem is the most common Eloquent performance issue. Displaying 30 posts with their authors causes 31 queries (1 for posts + 30 for each author) instead of 2. This multiplies with nested relationships — 30 posts with authors and comments can cause hundreds of queries. The difference between 2 queries and 200 queries is the difference between a fast page and a timeout.

**What to flag:**
- Queries fetching collections (`all()`, `get()`, `paginate()`) without `with()` when relationships are accessed in the view or downstream code
- Relationship access inside `@foreach` loops in Blade templates where the parent query has no eager loading
- Accessing relationships inside accessors — this is a hidden N+1 because the accessor runs per model instance
- Using `->count()` on a relationship method (`$user->posts()->count()`) inside a loop instead of `withCount()` or using the loaded collection `$user->posts->count()`
- Packages like Spatie Media Library relationships not being eager loaded (`->with('media')`)
- Do NOT flag single model lookups (`find()`, `first()`) where only one relationship is accessed — that is 2 queries total, not N+1

**Suggestion:** Add `->with('relationship')` to the query. For counts, use `->withCount('relationship')` which adds a `{relation}_count` attribute via subquery. For aggregates, use `withSum()`, `withAvg()`, `withMin()`, `withMax()`. Consider enabling `Model::preventLazyLoading(!app()->isProduction())` in `AppServiceProvider` to catch N+1 issues during development.
