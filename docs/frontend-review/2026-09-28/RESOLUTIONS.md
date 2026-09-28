# Resolutions — frontend audit 2026-09-28

Branch `fix/frontend-audit-2026-09-28`, based on `master` @ `7d4b8cf` (the tip when
this slice opened; master has since moved to `ec0ee2f`, 16 commits ahead — see
§Base below). No push, no merge.

Audit README is treated as the historical record and was not edited. This file sits
beside it.

| ID | Sev | Status | Commit | Verification |
|---|---|---|---|---|
| F-1 | N | **fixed** | `7034c19` | `/` returns 200 with `data-theme="dark"` under a dark preference; response contains zero six-digit hex (was 26 in-file / 42 incl. shorthand), no `cloud.laravel.com`, no `fonts.bunny.net`. Test: *lands inside the product and honors the stored theme*. |
| F-2 | N | **fixed by deletion** | `93cb39b` + `f042d17` | `/design-preview` now 404. Route + view removed; orphaned `View` import removed. Closes F-14 and F-15 with it. |
| F-3 | N | **fixed** | `bc7ceb6` | Run 4 strip caption reads "no scenario set"; rail label chip gone. Captured light + dark. Test asserts both directions so the fix cannot over-correct. |
| F-4 | O | deferred | — | Vocabulary split (strip "N/A" vs panel "not yet totalled") is real but lives in copy the audit graded O; touching it means choosing one vocabulary, which is a design-record call, not a one-liner in a file I was already in. |
| F-5 | O | deferred | — | Validation message "The umamusume id field is required." needs an attribute label in `StoreTrainingRunRequest`, which is outside the frontend fence for this slice. |
| F-6 | O | **decision requested** | — | No column, no input path, permanently N/A. Needs schema work or widget removal. See `DECISIONS-NEEDED.md`. |
| F-7 | N | **fixed — breaks 8 frozen tests** | `83db98c` | POST `stage=preview` now `302` + `Location: /training-runs/1`; target answers GET 200, so the 405 is unreachable. Bubbles recomputed (test). See §Frozen below. |
| F-8 | O | deferred | — | Out-of-range page reuses the zero-result sentence. Audit marked O; a distinct state needs new copy + a branch in `catalog/index.blade.php`, i.e. a new file opened to chase an observation, which the fence forbids here. |
| F-9 | N | **fixed** | `3c8c830` | CSV header now `…,condition,energy,mood,fans`; row 1 = `1,480,300,355,210,95,240,,88,NORMAL,9000`. JSON turn keys carry the same three. Original 8 positions unchanged. Bodies in `resolutions/`. |
| F-10 | N | **fixed** | `9419665` | `resources/views/errors/404.blade.php` renders through `x-layout`: nav, skip link, tokens, three routes back. Both 404 routes captured. |
| F-11 | O | not done | — | `errors/500.blade.php` optional; skipped because a 500 page cannot be exercised on this fixture without injecting a fault, and inventing one to screenshot it would be a fake verification. |
| F-12 | O | deferred | — | Three controls in one `<label>` on the skills editor (`runs/show.blade.php`). Real a11y gap; fixing it means restructuring that block, which is the "do not refactor while fixing" line. |
| F-13 | — | no action | — | Audit recorded this as a pass. |
| F-14 | O | **fixed with F-2** | `93cb39b` | The double-announced "Next, Next" existed only in the deleted route's literal sample data. |
| F-15 | O | **fixed with F-2** | `93cb39b` | design-preview was the last surface outside the token/layout system; it is gone. |
| F-16 | O | deferred | — | Run page length is a layout/design question, not a fix. |
| F-17 | — | no action | — | Audit recorded zero contrast failures. |
| F-18 | O | deferred | — | Plural/pronoun bug in `grade-point-meter.blade.php:104-106`. One-line copy fix, but the file is not one I otherwise touched, so the fence says leave it for the follow-up slice. |
| F-19 | N | **decision requested** | — | `/up` still loads `fonts.bunny.net` + `cdn.jsdelivr.net` (re-confirmed on this branch). Vendor-registered route. See `DECISIONS-NEEDED.md`. |

**Counts: 6 fixed (F-1, F-2, F-3, F-9, F-10, plus F-14/F-15 absorbed by F-2), 8 deferred
(F-4, F-5, F-8, F-11, F-12, F-16, F-18), 2 decision-requested (F-6, F-19), 2 no-action
(F-13, F-17).**

## Frozen-test collision — needs your ruling (F-7)

8 tests in three frozen Phase 3A/3B files now fail, all on one line shape:

- `GuidedFirstTurnTest` (3), `GuidedTurnOnRunViewTest` (3), `GuidedTurnStagesTest` (2)
- failure text: `Expected response status code [200] but received 302`

They assert `post(...)->assertOk()` on the preview submit — i.e. they pin the exact
behavior F-7 files as the defect. The invariant each test exists to protect ("a
preview writes no rows") still holds: the assertion on the line below each failure
passes. PRG and a 200-on-POST are mutually exclusive, so there is no version of the
fix that satisfies both.

Not edited, per the frozen rule. Minimal change when you authorize it:
`->assertOk()` becomes `->assertRedirect(route('runs.show', $run))` followed by
`->followRedirect()` where the test then reads content. Suite state: **base 6 failed
(all `SkillAutomationTest`, pre-existing and unrelated) → branch 14 failed**, so
F-7 accounts for exactly the 8-test delta and nothing else regressed.

## Corrections to the audit's own citations

1. **F-3's cited line does not exist.** The audit names
   `resource-strip.blade.php:62` and a `$scenario ?? 'ura_finale'` default. No such
   expression is in the tree on `master` or on `docs/frontend-review`; both
   components refuse unnamed scenario defaults and throw on an unknown key. The real
   default is `TrainingRun::scenarioKey()` at `app/Models/TrainingRun.php:203`
   falling back to `config('scenarios.baseline')`. The finding is genuine — I
   reproduced it — but fixing the cited line would have edited a comment.
2. **F-3's "no scenario" claim needs a carve-out.** The scenario picker on the same
   page legitimately lists "URA Finale" as an option, including for a run that has
   none. A first cut of the test asserted the whole page must not contain the string
   and failed on that `<option>`. The test now strips `<option>` blocks before
   asserting, because the picker is a control, not a claim.
3. **The audit's base moved under it.** §7 records Slice-7/8 commits landing on the
   audit branch between `7d4b8cf` and the audit commit. Consequence for this slice:
   `7d4b8cf` contains **no** `docs/frontend-review/2026-09-28/` files (0 entries),
   while current master contains all 134. So this branch's `resolutions/` folder sits
   beside no README until the branches meet.

## Base

Branch point `7d4b8cf`. Master has moved to `ec0ee2f` (16 commits) during this slice,
including the audit files themselves. Merging therefore needs a rebase, and
`resources/views/runs/show.blade.php`, `guided-step.blade.php`,
`resource-strip.blade.php` and `routes/web.php` are the likely conflict points, since
16 commits of UI work landed on the same files.

## Captures in `resolutions/`

- `run-detail-no-scenario-light.png`, `run-detail-no-scenario-dark.png` — F-3, 1280×800
- `error-404-run-light.png`, `error-404-catalog-light.png` — F-10
- `export-csv-run1.txt`, `export-json-run1.txt` — F-9 bodies
- `run-detail-guided-preview-prg.txt` — F-7 HTTP evidence, including an honest note
  that the bubble recomputation is proven by the automated test, not by curl

The audit's own PNGs and sidecars were not opened for writing; the two
`error-404-*-light.png` names are reused only inside `resolutions/`.
