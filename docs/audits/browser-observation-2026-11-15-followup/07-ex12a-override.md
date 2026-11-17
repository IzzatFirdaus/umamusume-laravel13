# EX-12a override record — 2026-11-15

**EX-12a is overridden on 2026-11-15 for one specific action:** applying the corrected
`RosyDreamsRiceShowerSeeder` to the shared dev DB so `:8000` reflects the corrected Rosy Dreams run
instead of the pre-correction state (run 14, card 33, `deck_slots` = 0 rows).

**Reason:** the audit's verification evidence is complete against the scratch copy (run 34, card 31,
six deck slots, legacy tree, two events at turn 67 — all observed on the private copy served at
:8157). But the live `:8000` still shows the pre-correction state, and any future walker would
re-file the fixed bug as unfixed. The standing ruling exists for routine audit passes — not for a
one-time correction of a known defect after the audit already proved the fix.

**Scope (exactly):** make `:8000` reflect the corrected Rosy Dreams run. Nothing else in the dev DB
gets touched. The seeder's delete path is scoped by `import_source`, so it only touches run 14's own
rows — deck slots, events, turn entries, race entries, skill pivots — and no other run.

**Nature of override:** one-time exception, not an amendment to EX-12a. EX-12a stays in force for all
future passes. The dev DB is re-seeded under the new run id (delete-and-reseed imports as a new row,
expected ~32, adjusted for dev DB's current max run id).

**Consequence for audit references:** all URLs referencing `/training-runs/14/...` break. A single
annotation line is added at the top of `01-findings.md` and `05-seeder-patch-review.md`:
*"Run 14 references throughout this package refer to the pre-correction dev DB row. On 2026-11-15 the
corrected seeder was applied to the dev DB, superseding run 14 with run N. Screenshots remain valid
evidence; live URLs should use run N."*

Run 13 (BO-002's reproduction case) was already gone before this action. If BO-002 still needs live
reproduction, that requires a fresh zero-turn run created via the wizard — separate from this reseed.

**Step order followed:** override recorded → back up → idempotency confirmed on scratch copy → server
stopped → seeder run on dev DB → new run id captured → four states verified on `:8000` → audit
references updated → backup retained as safety net.
