### Accessors Causing Hidden Performance Issues

Scan accessors for relationship loading, database queries, or expensive computations.

**Why it matters:** Accessors run once per model instance. An accessor that loads a relationship or runs a query causes N+1 problems that are invisible. A seemingly simple accessor can trigger a query per model in a collection loop.

**What to flag:**
- Accessors that access relationships not guaranteed to be eager loaded (e.g., `$this->networks`, `$this->posts`)
- Accessors containing `DB::`, `Model::where()`, or any database query
- Accessors performing expensive computations when used in collection contexts
- `$appends` including accessors that trigger queries — these run on every serialization
- Do NOT flag simple formatting accessors (date formatting, string concatenation, basic calculations)

**Suggestion:** Move query-dependent logic out of accessors into the controller or service layer. If the accessor needs relationship data, ensure it is always eager loaded via `$with` or explicit `with()`. For aggregates, use `withCount()`, `withSum()`, etc. at query time instead of computing in accessors. If the value is expensive to compute and rarely changes, consider caching it as a database column.
