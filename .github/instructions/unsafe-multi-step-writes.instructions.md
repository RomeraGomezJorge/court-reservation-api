### Transactions and Multi-Step Write Correctness

Scan services, actions, controllers, commands, and jobs for multi-step Eloquent writes that should likely be wrapped in a database transaction.

**Why it matters:** Creating a record, attaching relations, updating counters, and writing audit rows across multiple statements is common in Laravel apps. Without a transaction, a failure halfway through leaves partial state behind. This is a correctness issue, not just style.

**What to flag:**
- Multi-step create/update/delete flows that must succeed or fail as a unit but have no `DB::transaction()`
- Code paths that update several related models in sequence with no rollback strategy
- Inventory, balance, quota, or status transitions that can leave inconsistent state if one later query fails
- Do NOT flag a single `create()` or simple isolated update that stands on its own

**Suggestion:** Wrap genuinely atomic write flows in `DB::transaction()`. Keep the transaction scope tight and include all writes that must stay consistent together. If there are follow-up side effects such as events or jobs, make sure they happen after commit when correctness depends on committed data.
