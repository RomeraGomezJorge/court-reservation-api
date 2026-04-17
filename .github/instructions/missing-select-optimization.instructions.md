### Missing Column Selection (SELECT *)

Scan queries, especially in API endpoints, for loading all columns when only a few are needed.

**Why it matters:** `SELECT *` transfers every column from the database, including large text fields, JSON blobs, and columns never used in the response. In API endpoints this directly inflates response payloads. Even for web views, selecting fewer columns reduces memory usage and speeds up hydration.

**What to flag:**
- API controller methods returning models or collections without `select()` — especially when the model has many columns or large text/JSON fields
- Eager-loaded relationships without column constraints (`->with('user')` instead of `->with('user:id,name,email')`)
- `Model::all()` in API responses
- Missing foreign keys or primary keys in `select()` calls that would break relationships
- Do NOT flag admin panels, simple CRUD, or internal tools where payload size is not critical

**Suggestion:** Use `select()` on queries: `Post::select(['id', 'title', 'user_id'])`. Constrain eager-loaded relationships: `->with('user:id,name')`. Always include the primary key and any foreign keys needed for relationships. For API endpoints, prefer API Resources (`vendor/bin/sail artisan make:resource`) to control response shape consistently.
