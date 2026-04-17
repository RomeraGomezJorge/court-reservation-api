### Filtering in PHP Instead of Database

Scan for patterns where data is loaded into PHP collections and then filtered, instead of using database-level `where()` clauses.

**Why it matters:** Loading all records then filtering with collection methods (`filter()`, `reject()`, `where()` on collections) wastes memory and database bandwidth. A query returning 4000 records filtered to 10 in PHP can use dramatically more memory than a database query returning 10 records directly. The database is almost always better at filtering than PHP.

**What to flag:**
- `Model::all()` or `->get()` followed by `->filter()`, `->reject()`, `->where()` on the collection
- Loading full collections then using `->first()` on the collection instead of `->first()` on the query
- `->get()` followed by `->take()`, `->slice()`, or `->skip()` instead of using `limit()` and `offset()` in the query
- Search/filter features that load all records then search in PHP
- Do NOT flag collection methods used on already-small, bounded datasets (e.g., filtering a user's 3 roles)

**Suggestion:** Move filtering to the query builder with `where()`, `when()`, `whereHas()`, `whereRelation()`. Use `when()` for conditional filters: `->when($request->search, fn($q, $s) => $q->where('title', 'like', "%$s%"))`. Use `limit()` / `take()` at the query level instead of slicing collections.
