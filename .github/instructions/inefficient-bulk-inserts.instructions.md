### Unoptimized Seed and Bulk Insert Patterns

Scan seeders and import code for per-record `create()` calls in loops.

**Why it matters:** Using `Model::create()` inside a large loop runs individual INSERT queries over and over. This matters for seeders, CSV imports, and data migration scripts.

**What to flag:**
- `Model::create()` or `->save()` inside loops processing more than roughly 100 records
- Factory `::create()` in large loops in seeders
- CSV/Excel import code that processes rows one at a time with individual `create()` calls
- Do NOT flag small seeders or loops where model events must fire per record

**Suggestion:** Use `Model::insert()` with chunking for bulk operations: `$records->chunk(500)->each(fn($chunk) => Model::insert($chunk->toArray()))`. Note: `insert()` does not trigger model events, does not auto-set timestamps, and does not return model instances. Add timestamps manually if needed. For imports where validation matters per record, batch validated records and insert in chunks rather than one at a time.
