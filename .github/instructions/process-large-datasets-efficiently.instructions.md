### Large Datasets Loaded Into Memory

Scan commands, jobs, imports, exports, and maintenance scripts for `get()`/`all()` usage on large tables where chunking or streaming would be safer.

**Why it matters:** The most expensive Eloquent problems are not always query count. Loading 50,000 or 500,000 rows into memory at once can crash workers, exhaust PHP memory, or make simple maintenance tasks unusably slow. For large data processing, iteration strategy matters as much as the query itself.

**What to flag:**
- `Model::all()` or `->get()` used in jobs, commands, or imports that appear to process entire tables or very large subsets
- Long-running loops over large result sets without `chunk()`, `chunkById()`, `lazy()`, or `cursor()`
- Batch update/cleanup scripts that hydrate full models when only IDs or a few columns are needed
- Offset-based pagination over changing large datasets where `chunkById()` would be safer
- Do NOT flag ordinary paginated controller actions or clearly small bounded datasets

**Suggestion:** Use `chunk()` for bounded batch processing, `chunkById()` when rows may be inserted/deleted during iteration, `lazy()` for memory-friendly streaming with model hydration, and `cursor()` when you truly need one-at-a-time traversal. Prefer selecting only the columns needed for the batch operation.
