### 11. Sensitive Data Exposed in Serialization

Scan models for missing `$hidden` configuration and API responses that expose internal columns.

**Why it matters:** When models are serialized to JSON, every column is included by default. This exposes `password`, `remember_token`, `two_factor_secret`, internal flags, soft delete timestamps, and pivot data to API consumers. Even if the frontend ignores these fields, they are still exposed.

**What to flag:**
- User/auth models without `$hidden` including at minimum `password` and `remember_token`
- Models with sensitive columns (tokens, secrets, internal flags) not listed in `$hidden`
- API endpoints returning raw models without API Resources, where internal columns are exposed
- `$appends` including computed attributes that expose internal logic or trigger additional queries
- Do NOT flag internal admin tools or CLI output where exposure is not a concern

**Suggestion:** Add `protected $hidden = ['password', 'remember_token']` to auth models. For broader control, use `$visible` to whitelist only the fields that should be serialized. For API endpoints, use API Resources (`vendor/bin/sail artisan make:resource --no-interaction`) to explicitly control the response shape. Use `makeHidden()` and `makeVisible()` for per-response adjustments.
