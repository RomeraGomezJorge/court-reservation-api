### Convenience Methods Used Without Correctness Guarantees

Scan for `firstOrCreate()`, `updateOrCreate()`, and `upsert()` usage that assumes these methods alone prevent duplicates or race conditions.

**Why it matters:** These helpers are convenient, but they do not replace database constraints. Under concurrent requests, two workers can still attempt the same insert unless the database enforces uniqueness. This is a classic source of duplicate rows and subtle production-only bugs.

**What to flag:**
- `firstOrCreate()` or `updateOrCreate()` used for uniqueness-sensitive data with no matching unique index visible in migrations
- Code that assumes these helpers are race-condition-safe by themselves
- `upsert()` usage where the conflict target or unique columns do not appear to match the intended business rule
- Do NOT flag convenience helpers used on low-risk data where duplicates are harmless

**Suggestion:** Keep the helper if it reads well, but back it with a real unique index in the database. For truly atomic uniqueness guarantees, rely on schema constraints first and application helpers second. Use transactions where surrounding writes must stay consistent with the upserted row.
