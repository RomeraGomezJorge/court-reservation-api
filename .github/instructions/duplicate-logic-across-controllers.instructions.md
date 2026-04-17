### Duplicate Logic Across Controllers

Scan for identical or near-identical code blocks appearing in multiple controllers:
- Same query logic copy-pasted across controllers
- Same data transformation in multiple methods
- Same response formatting repeated (especially in API controllers)
- Same authorization/permission check patterns copied across files

**Why it matters:** Duplicate code means duplicate bugs and drift over time. However, premature abstraction is worse than duplication — extract only when the pattern appears in 3 or more places.

**What to flag:**
- Same block of logic (5+ lines) appearing in 3 or more places
- Same query scope logic repeated across controllers instead of being a Model scope
- Same response formatting across API controllers

**Suggestion — depends on what is duplicated:**
- Query logic: Extract to a Custom Query Builder
- Business logic: Extract to a Service
- Response formatting: Extract to a Trait or base controller method
- Only suggest extraction when duplicated 3+ times. Two occurrences are acceptable.
