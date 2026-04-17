### Mass Assignment Risks or Silent Failures

Scan models for missing or incorrect `$fillable`/`$guarded` configuration, and check whether strict mode is enabled.

**Why it matters:** Mass assignment problems are often silent. Attributes are discarded with no obvious error, or user input flows into unguarded models in ways the developer did not intend. The risk is not the presence of one style or another; the risk is mismatched model guarding and real write paths.

**What to flag:**
- Models with neither `$fillable` nor `$guarded` defined that are used with `create()`, `update()`, or `fill()` arrays
- Models with `$guarded = []` (fully unguarded) that are written from request data or other user-controlled input without clear validation/DTO boundaries
- `$fillable` arrays that are clearly out of sync with actual write paths, causing attributes to be silently ignored
- No `Model::shouldBeStrict()` call in `AppServiceProvider` — this would catch silent mass assignment failures during development
- Do NOT flag `$guarded = []` in projects that consistently validate and shape input before mass assignment
- Do NOT insist that every model use `$fillable` specifically — assess the safety of the actual pattern in use

**Suggestion:** Align model guarding with real write paths. Use `$fillable`, `$guarded`, DTOs, or validated arrays consistently, but avoid silent discard scenarios. Enable strict mode in development: `Model::shouldBeStrict(!app()->isProduction())` in `AppServiceProvider::boot()`. This makes Eloquent throw exceptions for mass assignment violations, lazy loading, and accessing missing attributes — catching bugs early instead of silently ignoring them.
