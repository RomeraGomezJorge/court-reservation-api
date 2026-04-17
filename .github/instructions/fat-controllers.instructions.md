### Fat Controllers (Business Logic in Controllers)

Scan controller methods for signs of business logic that should be extracted:
- Methods longer than ~20 lines of actual logic (excluding blank lines and comments)
- Direct Eloquent queries with complex conditions (multiple `where`, `join`, subqueries)
- Multiple model operations in sequence (create + attach + sync + notify)
- Conditional business rules (if/else chains determining behavior)
- Data transformation or calculation logic

**Why it matters:** Business logic in controllers cannot be reused from Artisan commands, Livewire components, Jobs, or other entry points. Extracting to a Service or Action class enables reuse everywhere.

**What to flag:**
- Controller methods with more than 20 lines of logic
- Controller methods that perform 3+ distinct operations in sequence
- Complex query building directly in controllers
- Business rule conditionals in controllers

**Suggestion:** Extract to a Service class (multiple related methods) or Action class (single operation with `handle()` method). The extracted class can then be called from controllers, commands, Livewire, and Jobs.

**Important — do NOT flag simple CRUD.** A controller method that is just `Model::create($request->validated())` followed by a redirect is fine. Do not suggest extraction for methods under 5 lines or for straightforward create/update/delete with no business rules.
