### Route Model Binding Not Used

Scan controllers for manual model lookups from route parameters.

**Why it matters:** Route model binding eliminates boilerplate, automatically returns 404 when the model is not found, and makes controller signatures self-documenting.

**What to flag:**
- `Model::find($id)`, `Model::findOrFail($id)`, or `Model::where('id', $id)->firstOrFail()` in controller methods where `$id` comes from a route parameter
- Manual 404 handling after a `find()` call: `if (!$model) abort(404);`

**Suggestion:** Type-hint the model in the controller method signature: `public function show(Post $post)`. For slug-based lookups, override `getRouteKeyName()` on the model. For scoped lookups, use implicit scoping: `public function show(User $user, Post $post)`.
