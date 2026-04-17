### Over-Engineering Detection

Scan for signs of unnecessary abstraction layers:
- Action classes that contain only 1-3 lines (just calling `Model::create()`)
- DTO classes wrapping 2-3 fields with no transformation
- Service classes with a single method that is only called from one place
- Pipeline usage for 2 sequential operations that never change
- Event + Listener pairs where only one listener exists AND the listener contains trivial logic (1-3 lines)
- Repository classes that just wrap Eloquent methods without adding query logic
- Interface-for-everything: `UserRepositoryInterface` / `UserServiceInterface` when there is only one implementation and no realistic second one. Interfaces earn their place when crossing package/module boundaries or when multiple implementations exist.
- Multiple User-like models (`Admin.php`, `Manager.php`, `Staff.php`) extending `Authenticatable` alongside `User.php` — use a single `User` model with a role column and Policies instead

**Why it matters:** Every extra class is a file to maintain, a level of indirection to follow, and cognitive overhead for the team. Abstraction should earn its place by enabling reuse, testability, or clarity — not exist as ceremony.

**What to flag:**
- Action classes under 5 lines of logic in `handle()`/`execute()`
- DTO classes with fewer than 4 properties and no transformation logic
- Service classes with one method used from one place
- Repository classes that mirror Eloquent API without adding query logic (Eloquent IS the data access layer in Laravel — a Repository on top is a second ORM)
- Interfaces with only one implementation and no module/package boundary justification
- Multiple `Authenticatable` models for what should be roles on a single `User` model

**Suggestion:** Inline the logic back into the caller. Simple CRUD does not need Action/DTO/Service layers. Extract only when complexity or reuse justifies it. For single-implementation interfaces, remove the interface and depend on the concrete class directly. For multiple user models, consolidate to a single `User` model with a `role` column (PHP Enum) and use Policies/middleware for authorization.

**Exception:** Do not flag multiple `Authenticatable` models in multi-tenancy setups or projects with truly separate authentication domains (different databases). Do not flag consistent use of Actions/Services across a project if the team clearly values uniformity — consistency has its own value.
