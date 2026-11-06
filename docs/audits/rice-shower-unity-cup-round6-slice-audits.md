# Round 6 slice audits

Audit ID: `RS-UC-R6-SLICE-AUDITS`. Register:
`docs/audits/rice-shower-unity-cup-round6-reconciliation.md` at `78eb093b`. This file is the
container for the twelve slice reports the round-6 implementation instruction names: three S1
reports, four S2 reports, five S3 reports.

No slice opens before its own pre-flight records HEAD, the dirty set on its touched paths, and the
suite gate. A dirty touched path blocks that slice only; no peer work is reset, absorbed or
modified.

## 1. Baseline

| Item | Value |
| --- | --- |
| Baseline SHA | `78eb093bef13b642123e5f046d68e9ec3c27bc32` (`master`) |
| Register | `docs/audits/rice-shower-unity-cup-round6-reconciliation.md`, 915 lines, revision 13 |
| Dirty set at Phase 0 | `?? docs/audits/RS-UC-2026-10/`, `?? docs/audits/rice-shower-unity-cup-ux-walk-6.md` |
| `migrate:status` | Clean. Every migration reads `Ran`, the last being `2026_11_05_084524_add_match_key_ja_to_skills_table` |
| Suite gate | `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` → **3 passed (52 assertions)**, exit 0, 18.23s |
| Fence check | `python tools/md_fence_check.py docs/audits/rice-shower-unity-cup-round6-reconciliation.md` → 0 invalid closing fences |
| Lore baseline | `php tools/lore.php` → 555 hits, 75 exempt lines; `php tools/lore.php code` → 313 hits |
| `RENDERED_EVIDENCE` | **TRUE**. Probed 2026-11-06 with `chromium.launch()`: Chromium 153.0.8010.12 launched, a page rendered and `page.textContent` returned the expected node in 539 ms. Not inherited from the plan's §6 assumption, which was stale |

### 1.1 Gates run and not run at Phase 0

Run: the four rows above (suite gate, `migrate:status`, fence check, both lore modes) plus the
`RENDERED_EVIDENCE` probe.

Not run at Phase 0, and not claimed: the full Pest suite, the browser suite, `npm run typecheck`,
`npm run build`, `pint`, PHPStan, `composer audit`, `npm audit`. Phase 0 is a baseline
observation; each slice runs its own gates and records them in its own section.

## 2. The ten-question re-check index

Each slice's section answers the ten questions from `docs/framework-ui-ux/_audit-prompt.md` §11,
each `PASS` / `FAIL` / `N/A` / `UNMEASURED` with a one-line basis, and declares its own
`RENDERED_EVIDENCE` from its own probe.

| Slice | Row | Commit | Ten-question result |
| --- | --- | --- | --- |
| S1.1 | R6-12 | pending | pending |
| S1.2 | R6-11 | pending | pending |
| S1.3 | R6-05 | pending | pending |
| S2.1 | §13.3 | pending | pending |
| S2.2 | §13.4 | pending | pending |
| S2.3 | R6-04 | pending | pending |
| S2.4 | §13.2 | pending | pending |
| S3.1 | R6-15 | pending | pending |
| S3.2 | R6-23 | pending | pending |
| S3.3 | R6-09 + R6-10 | pending | pending |
| S3.4 | R6-08 | pending | pending |
| S3.5 | §13.6 | pending | pending |

## S1.1 — R6-12, Timeline reads the record it was given

### Identity

| Item | Value |
| --- | --- |
| Audit ID | `RS-UC-R6-SLICE-AUDITS-S1.1` |
| Mode | M1 change audit |
| Row | R6-12 (register §4.1), facets (a) and (b) |
| Scope | `app/Http/Controllers/Career/TimelineController.php`, `resources/js/pages/Career/Timeline.vue`, `tests/Feature/EnergyReadingConsistencyTest.php`, `tests/browser/career-timeline.spec.ts` |
| Out of scope | Everything else in the Timeline: filters, pagination, phase grouping, correction flags, `before`, `actual` |

### Pre-flight

| Item | Value |
| --- | --- |
| HEAD at open | `78eb093bef13b642123e5f046d68e9ec3c27bc32` |
| Dirty set on the touched paths | Empty. Only the two untracked audit artifacts were dirty tree-wide |
| Suite gate | `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` → 3 passed (52 assertions) |
| `RENDERED_EVIDENCE` | **TRUE**, re-probed for this slice: Chromium 153.0.8010.12 launched and rendered a page in 539 ms |

### RED evidence (captured before GREEN)

`vendor/bin/pest tests/Feature/EnergyReadingConsistencyTest.php` → `3 failed, 5 passed (219 assertions)`.

The band-projection facet, verbatim:

```
Failed asserting that two arrays are identical.
--- Expected
+++ Actual
@@ @@
 Array &0 [
-    0 => 'Low band',
-    1 => 'Mid band',
+    0 => 'N/A',
+    1 => 'N/A',
     2 => 'N/A',
 ]
 at tests\Feature\EnergyReadingConsistencyTest.php:206
```

The two remaining failures, verbatim: `ErrorException — Undefined array key "energy_reading"` at
`:220` (the exact-zero case) and at `:251` (the non-turn-row case).

The SP facet passed at RED on the server side, which is itself the finding: `stats()` already sends
`'sp' => $turn->sp`, so the SP half of R6-12 is a **client property-name mismatch only** and the
feature test cannot see it. `career-timeline.spec.ts` is what pins that half, and its new case
asserted `173` / `0` against a page reading `entry.after?.sp` — keys the server never sent.

### GREEN

| File | Change |
| --- | --- |
| `TimelineController::turnRow()` | Added `'energy_reading' => $turn->energyReading()`. Band logic is not recomputed in the controller; `TurnEntry::energyReading()` is the single owner and returns `{kind, value, band, text}` unmodified |
| `TimelineController::raceRow()` / `eventRow()` | Added `'energy_reading' => null` — a non-turn row records no reading and never borrows a neighbour's |
| `Timeline.vue` props | Replaced the dead `after` declaration with `energy_reading`, typed as the model's own shape |
| `Timeline.vue` helpers | `energyText()` reads `entry.energy_reading?.text`; `spOf()` reads `entry.actual?.sp` |
| `Timeline.vue` render | Energy prints `energyText(entry) ?? 'N/A'`; the absence `title` keys on `null`, never on `kind === 'none'`, so a recorded `exact = 0` carries no absence title |
| `Timeline.vue` note | Reworded to cover both row kinds: a turn row prints the reading it recorded, and race/event rows print `N/A` |

`before` and `actual` are untouched. Filters, pagination, phase grouping and correction flags are
untouched.

### Rule sweep (dimensions A-H)

| Dimension | Result |
| --- | --- |
| A. Status and feedback | `no finding`. The change adds no write and no pending state; the correction door it feeds already reports its own outcome |
| B. Control and target | `no finding`. No control added, removed or resized |
| C. Error prevention and recovery | `no finding`. Read-only projection |
| D. Consistency and signifiers | **finding** — see F1 |
| E. Cognitive load and memory | `no finding` after the fix. Before it, the row asked the Trainer to remember the reading from another screen; that is the `SHR-7` ground below |
| F. Visual grouping and hierarchy | `no finding`. No layout change |
| G. Accessibility semantics and contrast | `no finding`. The `title` disclosure channel is unchanged in kind; it now keys on the true absence rather than a static string |
| H. Content and language | **finding** — see F2 |

### Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F1 | `SHR-7` | Shneiderman | Career Timeline, record table, populated | Every Energy cell printed `N/A` and every SP cell printed `N/A` over readings the turns carried. The page read `entry.after?.energy` and `entry.after?.sp`; the server sends `before` and `actual` and no `after` at all, and no band projection existed. The value a decision needs was not on the screen that needed it | OBSERVED_SOURCE | `Timeline.vue:32,117-118` (pre-fix); `TimelineController.php:237-238,355-366` (pre-fix) | 3 | High | Done: project `energyReading()` per row and read `actual.sp` |
| F2 | `GP-2` | Gerhardt-Powals | Career Timeline, the explanation note under the table | The note said `Energy and SP print N/A where the import did not capture them`, which named the wrong cause: the import captured both, and the screen dropped them. A reader of the row was told a source was empty when the renderer was | OBSERVED_SOURCE | `Timeline.vue:230-231` (pre-fix) | 2 | High | Done: state which row kind prints what |

`GP-2` is cited per register §11.5: it is defined in `gerhardt-powals-principles-2026-10-21.md:118`
and has no routing-table row in `_audit-prompt.md`. The routing gap is the prompt's, not the
finding's.

### Unmeasured register

Empty. `RENDERED_EVIDENCE` is TRUE on this host, the new browser case ran, and it asserted the
rendered cell text rather than a payload.

### Gap register delta

None. No framework-demanded capability was found missing by this slice.

### Commit body basis

Ten-question re-check, each answer with its one-line basis:

| # | Question | Result | Basis |
| --- | --- | --- | --- |
| 1 | Does the change add, remove or move a control? | `N/A` | No control added, removed or moved; the correction link is unchanged |
| 2 | Does it change what is on screen at once? | `N/A` | Same table, same columns, same row count |
| 3 | Does it change a colour, a tint or a state? | `N/A` | No class change in the row |
| 4 | Does it change a type size or a weight? | `N/A` | No type change |
| 5 | Does it change what the user can undo, or where the action is? | `N/A` | The correction door is untouched |
| 6 | Does it change where a thing lives, or what it is called? | `PASS` | The Energy reading is still the fifth column; only its source changed, and it now reads the row's own record |
| 7 | Does it change a progress, an incomplete state or a boundary? | `PASS` | A recorded `exact = 0` now prints `0` rather than an absence, and an `unknown` row with a stale figure prints `N/A`; both are boundaries the case asserts |
| 8 | Does it change what the user has to read or remember? | `PASS` | It removes a cross-display memory: the reading is now on the row instead of only on the Cockpit |
| 9 | Does it add a new destination, a new landmark or a new control type? | `N/A` | No new destination |
| 10 | Does it change the words? | `PASS` | The explanation note reworded; the assertion is `anatomy`-free and asserts the row behaviour, and the browser spec's `never zero` clause still matches |

`RENDERED_EVIDENCE = TRUE`, declared from this slice's own probe: `career-timeline.spec.ts` ran 9
cases and its new R6-12 case passed, asserting `Low band` / `173` on row 1 and `Mid band` / `0` on
row 2, then correcting row 1 to `High band` and re-asserting that row 2 was unchanged.

### Verification output

```
vendor/bin/pest tests/Feature/CareerTimelineTest.php tests/Feature/TurnEntryUpdateTest.php tests/Feature/CockpitEnergyBandDisplayTest.php tests/Feature/EnergyReadingConsistencyTest.php
  Tests:    21 passed (438 assertions)
```

```
npx playwright test tests/browser/career-timeline.spec.ts --reporter=line
  Running 9 tests using 1 worker
  8 passed (37.5s)
  1 failed — sizes the controls to the 44px contract and reflows at 320px
```

That one failure is **pre-existing and unrelated**, classified by stashing all four slice files and
re-running the single case at `78eb093b`: `Expected: 0, Received: 2`, byte-identical. It asserts the
44px house rule over every `main a, button, select, summary` on the page and is not touched by this
slice. Recorded here rather than fixed, per §9's failure-attribution rule.

