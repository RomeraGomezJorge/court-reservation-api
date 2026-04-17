### Incorrect Use of Eloquent `$with` Property

Scan models for the `$with` property that auto-eager-loads relationships on every query.

**Why it matters:** `protected $with = ['user', 'comments']` loads those relationships on every single query for that model, including queries where the relationships are not needed. This is hidden overhead that is easy to forget about because it does not appear in the query code.

**What to flag:**
- Models with `$with` containing relationships that are clearly not needed on every query
- Models with `$with` containing heavy relationships (has-many with many records, nested relationships)
- Repository evidence that `$with` is causing over-fetching across different contexts (API, admin, CLI)
- Do NOT flag `$with` on models that genuinely always need specific relationships

**Suggestion:** Remove `$with` and use explicit `->with()` in each query that needs the relationship when the relationship is not truly universal. If a model is received from another function and relationships need to be added later, use `->load()` on the existing collection. Reserve `$with` for models where the relationship is genuinely needed in nearly all use cases.
