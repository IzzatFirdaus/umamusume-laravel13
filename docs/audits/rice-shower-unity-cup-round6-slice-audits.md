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

## S1.2 — R6-11, deck card levels cross Preflight

### Identity

| Item | Value |
| --- | --- |
| Audit ID | `RS-UC-R6-SLICE-AUDITS-S1.2` |
| Mode | M1 change audit |
| Row | R6-11 (register §4.1), facets (a) and (b) |
| Scope | `app/Http/Requests/Career/StartCareerRequest.php`, `app/Http/Controllers/Career/PreflightController.php`, `tests/Feature/CareerPreflightTest.php`, `tests/browser/career-preflight.spec.ts`, `database/factories/SupportCardFactory.php` (read only, not changed) |
| Out of scope | `rerun()`'s JSON/header isolation, the deck step's own write, the run-scoped deck path (`TrainingRunController::syncDeck`, which already persists the level) |

### Pre-flight

| Item | Value |
| --- | --- |
| HEAD at open | `698d0fe4` |
| Dirty set on the touched paths | Empty |
| Transaction claim verified | `PreflightController::store()` `:139-156`: `TrainingRun::create()` and every `deckSlots()->create()` sit inside one `DB::transaction(function () use ($request) { ... })`. The card-level write was added inside that same closure, so a refusal leaves no run and no slot behind |
| Suite gate | `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` → 3 passed (52 assertions) |
| `RENDERED_EVIDENCE` | **TRUE**, re-probed for this slice |

### RED evidence (captured before GREEN)

`vendor/bin/pest tests/Feature/CareerPreflightTest.php --filter='R6-11'` → `2 failed (4 assertions)`,
verbatim:

```
Failed asserting that null is identical to 35.
at tests\Feature\CareerPreflightTest.php:284
```

```
Session is missing expected key [errors].
Failed asserting that false is true.
at tests\Feature\CareerPreflightTest.php:316
```

The first is facet (a): the level was dropped at creation. The second is facet (b): the stale-level
refusal never fired because the child re-run had no level to check.

### GREEN

| File | Change |
| --- | --- |
| `StartCareerRequest::rules()` | Added `'deck.*.card_level' => ['nullable', 'integer', 'min:1', 'max:50']`. The field bound matches `StoreDeckRequest`'s; the per-rarity ceiling comes from the child re-run |
| `StartCareerRequest::rerunStepRules()` | Forwarded `'card_level' => $slot['card_level'] ?? null` into the deck slice handed to `StoreDraftDeckRequest`, so its rarity-ceiling after-hook runs at Preflight |
| `StartCareerRequest::cardLevelByPosition()` | New accessor mirroring `ownershipByPosition()`: skips a cleared position, and answers `null` (never `0`) for a card whose level was not recorded |
| `PreflightController::store()` | Reads `$levels` beside `$ownership` and writes `'card_level' => $levels[$position] ?? null` on the slot create, inside the existing transaction |

`DeckSlotCardLevelTest`'s `->not->toContain('card_cap')->not->toContain('limit_break')` still passes:
no collection column was added.

### Rule sweep (dimensions A-H)

| Dimension | Result |
| --- | --- |
| A. Status and feedback | `no finding`. The refusal already reports against `deck`, and this slice makes it reachable |
| B. Control and target | `no finding`. No control changed |
| C. Error prevention and recovery | **finding** — F1. The stale-level state was accepted because nothing revalidated it |
| D. Consistency and signifiers | **finding** — F2. The wizard recorded a level the run then did not hold |
| E. Cognitive load and memory | `no finding`. This slice removes a re-entry, it does not add one |
| F. Visual grouping and hierarchy | `no finding`. No layout change |
| G. Accessibility semantics and contrast | `no finding`. The refusal's message is the child request's own, already asserted by `DeckSlotCardLevelTest` |
| H. Content and language | `no finding`. No new copy |

### Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F1 | `H5` | Usability heuristics | Preflight, Start career, a stale level | A level valid when the deck step wrote it and invalid by commit was accepted: `StartCareerRequest` had no `deck.*.card_level` rule and `rerunStepRules()` dropped the key, so the child's rarity-ceiling after-hook never saw a value. The run was created holding a level the client cannot represent | OBSERVED_SOURCE | `StartCareerRequest.php:98-101,182-198` (pre-fix); `StoreDeckRequest.php:105-149` | 3 | High | Done: rule plus forwarded key, so the ceiling applies at commit |
| F2 | `SHR-7` | Shneiderman | Deck step → Preflight → run deck | The deck step stored the level (`DeckSelectController.php:87`, `SetupDraft.php:212`, `StoreDraftDeckRequest.php:85-87`), and `deck_slots.card_level` is fillable and cast (`DeckSlot.php:33,76-80`). Preflight wrote three keys only, so the run deck printed "Not recorded" over a level entered two steps earlier | OBSERVED_SOURCE | `PreflightController.php:143-149` (pre-fix) | 3 | High | Done: `cardLevelByPosition()` plus the transactional write |

`SHR-7` is cited per register §11.4's ruling: `SHR-8` is undefined and must not be cited.

### Unmeasured register

Empty.

### Gap register delta

None.

### Commit body basis

| # | Question | Result | Basis |
| --- | --- | --- | --- |
| 1 | Add, remove or move a control? | `N/A` | No control changed |
| 2 | Change what is on screen at once? | `N/A` | Same screen and steps |
| 3 | Change a colour, tint or state? | `N/A` | No class change |
| 4 | Change a type size or weight? | `N/A` | No type change |
| 5 | Change what the user can undo, or where the action is? | `PASS` | A refused start now leaves the draft intact rather than creating a run; `DeckSlotCardLevelTest`'s both-writers case still passes |
| 6 | Change where a thing lives, or what it is called? | `N/A` | Nothing renamed or moved |
| 7 | Change a progress, an incomplete state or a boundary? | `PASS` | A level of `null` stays "not recorded" and never becomes `0`; a level past the rarity ceiling is now refused at commit |
| 8 | Change what the user has to read or remember? | `PASS` | Removes a re-entry: the level entered at the deck step reaches the run deck |
| 9 | Add a new destination, a landmark or a control type? | `N/A` | None |
| 10 | Change the words? | `N/A` | No copy added or changed |

`RENDERED_EVIDENCE = TRUE`, declared from this slice's own probe: the new
`career-preflight.spec.ts` case passed in 26.7s, asserting level 35 on slot 1 and level 50 plus a
pressed `Rented` on slot 6 after a cold reload of the run's own deck builder.

**Non-vacuity proof by negation.** With the controller's `'card_level' => ...` line removed and
nothing else changed, the same case failed at
`expect(page.getByLabel('Level for Slot 1')).toHaveValue('35')` with `Expected: "35", Received: ""`.
The line was then restored byte-identically (`git diff --stat` back to 6 insertions, `grep -c
card_level` = 1).

### Verification output

```
vendor/bin/pest tests/Feature/CareerPreflightTest.php
  Tests:    21 passed (290 assertions)

vendor/bin/pest tests/Feature/DeckSlotCardLevelTest.php
  Tests:    5 passed (21 assertions)
```

```
npx playwright test tests/browser/career-preflight.spec.ts --grep 'R6-11'
  1 passed (26.7s)
```

## S1.3 — R6-05, manual parent names survive the commit

### Identity

| Item | Value |
| --- | --- |
| Audit ID | `RS-UC-R6-SLICE-AUDITS-S1.3` |
| Mode | M1 change audit |
| Row | R6-05 (register §4.1), facets (a), (b) and (c) |
| Scope | `app/Http/Requests/StoreLegacySelectionRequest.php`, `resources/js/pages/Career/LegacySelect.vue`, `tests/Feature/CareerLegacyDeckStepsTest.php`, `tests/Feature/CareerPreflightTest.php`, `tests/browser/career-legacy-deck-steps.spec.ts` |
| Out of scope | The JSON contract, the schema, the `legacies.*.name` allowlist, ancestor indexing, `is_guest`, ranks, sparks, `pick` — all preserved byte-for-byte |

### Pre-flight

| Item | Value |
| --- | --- |
| HEAD at open | `417315d6` |
| Dirty set on the touched paths | Empty |
| `StartCareerRequest` reads `legacySelection()`, not `legacyParents()` | Confirmed at `:67`: `'legacy_selection' => SetupDraft::legacySelection()`. So the draft payload is the only path to the run, not the parent ids |
| `LegacySelectionPayload::fromArray()` preserves `name` | Confirmed at `:189-193,240-244`: `legacy()` calls `manualName()` and stores the key only when non-null, so the reader already supported it |
| Suite gate | `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` → 3 passed (52 assertions) |
| `RENDERED_EVIDENCE` | **TRUE**, re-probed |

### RED evidence (captured before GREEN)

`vendor/bin/pest tests/Feature/CareerLegacyDeckStepsTest.php --filter='R6-05'` → `1 failed (2 assertions)`,
verbatim:

```
ErrorException
Undefined array key "name"
at tests\Feature\CareerLegacyDeckStepsTest.php:230
```

The stored draft carried no `name` at all, which is facet (a). Facets (b) and (c) are client-side and
`career-legacy-deck-steps.spec.ts` is what pinned them: with only the request fixed, the cold-reload
case still failed at `expect(page.locator('#legacy-name-0')).toHaveValue('Vodka')` with
`element(s) not found` — manual mode never reopened because `manualFor()` tested
`legacy.legacy_id === null` against a key the stored payload does not carry.

### GREEN

| File | Change |
| --- | --- |
| `StoreLegacySelectionRequest::payload()` | Added `'name'` to the rebuilt record: a trimmed non-empty string, else `null` so `LegacySelectionPayload` omits the key and a names-only record keeps its old shape |
| `LegacySelect.vue` `manualFor()` | Facet (b): test `props.parents[index] === null && name !== ''` instead of `legacy.legacy_id === null`, which was always `undefined`. A banked parent stays banked; an unnamed slot stays unselected |
| `LegacySelect.vue` `cardFor()` | Facet (c): in manual mode the form's typed name is the card's name, so the pill and the heading react on the keystroke. A non-manual slot keeps the server card untouched, and the cold-load fallback is the same server card |
| `LegacySelect.vue` `ParentCard` | `veteranId` and `runId` widened to `number \| null`. The server genuinely sends null for a manual parent, so the narrow type was a lie the page worked around |

### Rule sweep (dimensions A-H)

| Dimension | Result |
| --- | --- |
| A. Status and feedback | **finding** — F2. The pill read `Not chosen` over a field holding a name |
| B. Control and target | `no finding`. No control resized or moved |
| C. Error prevention and recovery | **finding** — F3. A cold reload lost the entry |
| D. Consistency and signifiers | **finding** — F1. The step recorded a name the next screen did not see |
| E. Cognitive load and memory | `no finding` after the fix; before it, the Trainer had to retype the parent at every step |
| F. Visual grouping and hierarchy | `no finding`. Same two cards, same layout |
| G. Accessibility semantics and contrast | `no finding`. The pill keeps its word-plus-class pair; a live region was not added and was not needed for a synchronous input echo |
| H. Content and language | `no finding`. No new copy |

### Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F1 | `SHR-7` | Shneiderman | Legacy step → Preflight → Inheritance | The rule accepted `legacies.*.name` and four readers already supported it, but `payload()` rebuilt the record with nine keys and not `name`. A typed parent reached Preflight and the created run with no name | OBSERVED_SOURCE | `StoreLegacySelectionRequest.php:335-347` (pre-fix) | 3 | High | Done: copy the validated name into the rebuilt record |
| F2 | `H1` | Usability heuristics | Legacy step, parent card header, typing | The pill and the heading read `props.parentA` / `props.parentB`, built from the Veteran library, so a typed name produced no `Chosen` state until the step was saved and reloaded | OBSERVED_SOURCE | `LegacySelect.vue:300,392` (pre-fix) | 3 | High | Done: read the form in manual mode |
| F3 | `SHR-5` | Shneiderman | Legacy step, cold reload | Manual mode tested `legacy.legacy_id === null`, and a stored payload carries no such key, so the branch was compared against `undefined` and could not reopen even after the name was stored | OBSERVED_SOURCE | `LegacySelect.vue:130-148` (pre-fix) | 3 | High | Done: derive manual mode from the slot plus a non-empty name |

Facet (c) is reported explicitly because without it the fix would pass the feature test and fail the
browser case: the feature test asserts the stored payload and the rendered props, not the pill's
reaction to a keystroke.

### Unmeasured register

Empty.

### Gap register delta

None.

### Commit body basis

| # | Question | Result | Basis |
| --- | --- | --- | --- |
| 1 | Add, remove or move a control? | `N/A` | No control added or moved |
| 2 | Change what is on screen at once? | `N/A` | Same two cards |
| 3 | Change a colour, tint or state? | `PASS` | The pill flips between `Chosen` and `Not chosen` on a keystroke rather than only after a save; both keep a word, so no colour-only channel |
| 4 | Change a type size or weight? | `N/A` | No type change |
| 5 | Change what the user can undo, or where the action is? | `N/A` | The save is unchanged |
| 6 | Change where a thing lives, or what it is called? | `N/A` | Nothing renamed |
| 7 | Change a progress, an incomplete state or a boundary? | `PASS` | Manual mode now reopens from the stored record, which is the boundary that was broken |
| 8 | Change what the user has to read or remember? | `PASS` | Removes a retype at every later step |
| 9 | Add a new destination, a landmark or a control type? | `N/A` | None |
| 10 | Change the words? | `PASS` | `Chosen` / `Not chosen` are unchanged words; only when they appear changed |

`RENDERED_EVIDENCE = TRUE`, declared from this slice's own probe: `career-legacy-deck-steps.spec.ts`
ran 17 cases and all passed (43.9s), including the new R6-05 case asserting the pill and the name
before any save and the same state after a cold reload.

### Verification output

```
vendor/bin/pest tests/Feature/CareerLegacyDeckStepsTest.php   → 16 passed (309 assertions)
vendor/bin/pest tests/Feature/LegacySelectionSchemaTest.php tests/Feature/GrandparentRanksTest.php tests/Feature/CareerPreflightTest.php tests/Feature/CareerLegacyDeckStepsTest.php
  Tests:    60 passed (677 assertions)
```

```
npx playwright test tests/browser/career-legacy-deck-steps.spec.ts
  17 passed (43.9s)
```

**D2 re-verified green**, as register §11.3 requires: `it('discards only the semantically empty Spark
rows and keeps every real one (D2)')` passed inside the 16-case `CareerLegacyDeckStepsTest` run above
(its own assertions: 3 Sparks kept, targets `['Power', 'Late Surger', null]`, stars `[1, null, 2]`).

### Lore ruling

`lore-code` moved 313 → 314. `diff` of the normalised hit sets shows the one added line is
`tests/Feature/CareerLegacyDeckStepsTest.php` carrying `'ancestors' => ['Grass Wonder', 'Mill
Raptor']`. **Ruling: class 2, a substring inside a proper noun.** `Mill Raptor` is the name of an
existing corpus Umamusume already present five times in that same file (`:39`, `:138`, `:156`,
`:186`), used here as the fixture's ancestor name. It is not a violation and no marker is added;
the term stays in the count with this ruling, per `AGENTS.md` §5 and §9.

## S2.1 — §13.3, the advisor presents the rest failure tier as current

### Identity

| Item | Value |
| --- | --- |
| Audit ID | `RS-UC-R6-SLICE-AUDITS-S2.1` |
| Mode | M1 change audit |
| Row | **§13.3, not an `R6-NN` row.** The walk had no reading that could find it; the register's re-base pass recorded it as an application defect against a corpus that was already correct |
| Scope | `config/advisor.php`, `app/Services/Advisor/NextDecision.php`, `app/Services/Advisor/TrainerAdvisor.php`, `tests/Feature/NextDecisionTest.php`, `tests/Feature/TrainerAdvisorTest.php` |
| Out of scope | The `recorded_forecast` branches (already correct), the other three constants, `energyAfter()`'s arithmetic |

### Pre-flight

| Item | Value |
| --- | --- |
| HEAD at open | `279381b5` |
| Dirty set on the touched paths | Empty |
| Reference is already correct | `UMAMUSUME_REFERENCE.md:190`: `+50 Energy modal, +70 (big success), +30 (failure, sometimes with 夜ふかし気味 / Late-Night Feeling)`. §13.3's reversal confirmed: the corpus needs no correction |
| Suite gate | `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` → 3 passed |
| `RENDERED_EVIDENCE` | **TRUE**, re-probed. This slice renders (the advisor is on the Cockpit), but the assertion is payload-level and no browser case was needed or added |

### RED evidence (captured before GREEN)

`vendor/bin/pest tests/Feature/NextDecisionTest.php --filter='§13.3'` → `1 failed (9 assertions)`,
verbatim:

```
Expected: Rest recovers +30 Energy. Source: GameWith, 2026-09-25, read 2026-11-02.

To contain: +50
at tests\Feature\NextDecisionTest.php:213
```

The rendered row quoted the failure tier, with a `current` confidence label and a read date, as the
recovery figure.

### GREEN

| File | Change |
| --- | --- |
| `config/advisor.php` | `rest_recovery.value` 30 → **50**, with a new `tiers` map holding all three (`modal` 50, `big_success` 70, `failure` 30). Source corrected to the two named tables (`GameWith 257617 (n=3833), Kamigame 147307607101602682 (n=100)`), matching the reference's own citation |
| `NextDecision::restRecoveryRow()` | New row builder: prints the modal figure, then the big-success and failure tiers in the same sentence, so the copy cannot quote one tier as if it were the whole story |
| `NextDecision::explain()` | The Rest branch calls `restRecoveryRow()` instead of `constantRow('rest_recovery', ...)` |
| `TrainerAdvisor::restFailure()` | Reads the failure tier from the same constant's `tiers` map, so the copy's two numbers and the config cannot drift |
| `TrainerAdvisor::reason()` | The Rest sentence names the modal figure **and** the failure figure. This was the same defect one layer down: the reason string said only `Rest recovers +NN Energy` |
| `TrainerAdvisor::constants()` | Return shape widened to `tiers?: array<string, int>` — the honest type now that one constant carries a map |

### Rule sweep (dimensions A-H)

| Dimension | Result |
| --- | --- |
| A. Status and feedback | `no finding`. The row keeps its `confirmed` badge, which is still correct: it is a declared constant printed as read |
| B. Control and target | `no finding`. No control |
| C. Error prevention and recovery | `no finding` |
| D. Consistency and signifiers | **finding** — F1. Two surfaces on one screen printed different numbers for the same mechanic |
| E. Cognitive load and memory | `no finding` |
| F. Visual grouping and hierarchy | `no finding` |
| G. Accessibility semantics and contrast | `no finding` |
| H. Content and language | **finding** — F2. The copy presented the worst case as the figure, with a `current` label behind it |

### Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F1 | `H4` | Usability heuristics | Cockpit, advisor `why` rows, Rest recommended | The Rest row said `Rest recovers +30 Energy` while the same screen's `recorded_forecast` block printed `Success +50 (modal) / Big success +70 / Poor Rest +30`. One mechanic, two numbers, one screen | OBSERVED_SOURCE | `config/advisor.php:32-37`, `NextDecision.php:485` vs `:238-243` (pre-fix) | 3 | High | Done: one constant carries all three tiers and both surfaces read it |
| F2 | `GP-2` | Gerhardt-Powals | Cockpit, advisor Rest row | The quoted figure carried a `current` confidence and a read date, so the copy presented the failure tier as the sourced recovery rather than naming it as one tier of three | OBSERVED_SOURCE | `config/advisor.php:36` (pre-fix) | 3 | High | Done: the copy names which tier is which |

`GP-2` is cited per register §11.5 (defined at `gerhardt-powals-principles-2026-10-21.md:118`, no
routing-table row).

### The one derived-behaviour change, stated plainly

`TrainerAdvisor::energyAfter()` computes `min(100, energy + restRecovery())`. With the modal figure
the forecast now reads `+50` where it read `+30`. That is the correct projection: the modal recovery
is what a forecast should use. `TrainerAdvisorTest`'s own `min`/`max` assertions for `session_cost`
were unaffected (28 passed), and no test pinned the old `energyAfter` figure for Rest.

### Unmeasured register

Empty.

### Gap register delta

None.

### Commit body basis

| # | Question | Result | Basis |
| --- | --- | --- | --- |
| 1 | Add, remove or move a control? | `N/A` | No control |
| 2 | Change what is on screen at once? | `N/A` | Same rows, same count |
| 3 | Change a colour, tint or state? | `N/A` | Badge unchanged |
| 4 | Change a type size or weight? | `N/A` | No type change |
| 5 | Change what the user can undo, or where the action is? | `N/A` | No action |
| 6 | Change where a thing lives, or what it is called? | `N/A` | Nothing moved |
| 7 | Change a progress, an incomplete state or a boundary? | `PASS` | The forecast's projected Energy after Rest moves from `+30` to `+50`, which is the corrected boundary |
| 8 | Change what the user has to read or remember? | `PASS` | The two tiers are on one row instead of two |
| 9 | Add a new destination, a landmark or a control type? | `N/A` | None |
| 10 | Change the words? | `PASS` | The Rest row's sentence names all three tiers; `TrainerAdvisorTest`'s case carries the dated correction |

`RENDERED_EVIDENCE = TRUE`, but no rendered assertion was made and none is claimed: the evidence for
this slice is the payload (`inertiaProps('advisor.decision')`) plus `config()`, both read in the
runner.

### Verification output

```
vendor/bin/pest tests/Feature/TrainerAdvisorTest.php tests/Feature/NextDecisionTest.php
  Tests:    28 passed (309 assertions)
```

```
vendor/bin/pint --test --format agent config/advisor.php app/Services/Advisor/NextDecision.php app/Services/Advisor/TrainerAdvisor.php tests/Feature/NextDecisionTest.php tests/Feature/TrainerAdvisorTest.php
{"tool":"pint","result":"passed"}

vendor/bin/phpstan analyse --no-progress --memory-limit=1G app/Services/Advisor/NextDecision.php app/Services/Advisor/TrainerAdvisor.php
 [OK] No errors
```

`SCREEN_SPEC.md` was checked for the `+30` figure and does not name it, so no screen-spec update was
required.

## S2.2 — facility ladder keyed on facility_level_source

### Identity

| Item | Value |
| --- | --- |
| Audit ID | `RS-UC-R6-SLICE-AUDITS-S2.2` |
| Mode | M1 change audit |
| Row | **§13.4, not an `R6-NN` row** |
| Scope | `app/Models/TrainingRun.php` (`facilityLevel()` and three new private readers), `tests/Feature/ScenarioPanelTest.php` |
| Out of scope | The burst countdown, the counters, `TeamPanelBuilder::teamSection()`'s ladder assembly, `TrainingDecisionController::facilityLevels()` (which already answered the repetition question correctly — see the measured correction below) |

### Pre-flight, with a measured correction to §13.4

| Item | Value |
| --- | --- |
| HEAD at open | `fdd2e722` |
| Dirty set on the touched paths | Empty |
| `facility_level_source` per scenario, located by grep | `repetition` at `config/scenarios.php:191` (URA Finale) and `:1061` (Our Grand Concert); `team_rank` at `:407` (Unity Cup); `repetition_plus_purchases` at `:624` (Trackblazer) |
| Suite gate | `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` → 3 passed |
| `RENDERED_EVIDENCE` | **TRUE**, re-probed |

**Correction 1 — the defect's shape is not what §13.4 described.** §13.4 says a URA run "printed a
team-rank-derived level". Measured: it printed **no level at all**. Both call sites obtain their rank
from `TrainingRun::latestTeamRank()`, which returns `null` unless
`composesPanel('team_rank_ladder')` (`TrainingRun.php:1328-1339`), and that panel is `false` for
URA Finale, Our Grand Concert and Trackblazer (`config/scenarios.php`). So `facilityLevel(null)`
answered `null` before this slice. The harm is an **absence over recorded data**, not a wrong
number. Severity unchanged: a Trainer's own entered facility levels reached no run-level surface.

**Correction 2 — one surface already did the repetition read.** §13.4's claim that "no screen derives
a current facility level from the recorded turn columns" is refuted at
`TrainingDecisionController::facilityLevels()` (`:495-513`), which walks `turnEntries` newest-first
and fills five per-stat levels from the `facility_*` columns. So the training screen's published-figure
keying already worked. The gap was the **run-level** figure on the team panel and the advisor, which
is what this slice fixes. The two now read the same columns through one method instead of one of them
reading a rank letter.

**Correction 3 — a Shop purchase is not a per-stat delta.** §13.4 and the instruction describe
"sum of `source_name = 'Shop'` Scenario `TurnEvent` deltas for that stat". Measured: `deltas` is
`{item, cost, effect}` (`ShopPurchasePayload::KEYS`), and `effect` is prose. There is no numeric
per-stat delta to sum. The only catalogue item that raises a facility level states its own magnitude
in that prose (`config/scenarios.php:758`, `Speed Training Application` → `Speed facility level +1 for
the run`), so the increment is read from the item's **configuration** and never parsed from a
Trainer-typed string. A purchase naming an item the catalogue does not carry contributes `0`.

### RED evidence (captured before GREEN)

`vendor/bin/pest tests/Feature/ScenarioPanelTest.php --filter='§13.4'` → `2 failed, 2 passed
(4 assertions)`:

```
Failed asserting that null is identical to 3.
at tests\Feature\ScenarioPanelTest.php:662   (URA Finale, repetition)
Failed asserting that null is identical to 3.
at tests\Feature\ScenarioPanelTest.php:685   (Trackblazer, repetition_plus_purchases)
```

The two guards passed at RED, which is the asymmetry that proves the branch: Unity Cup's team-rank
ladder answered `5` before and after, and the no-logged-turn case answered `null` before and after.

### GREEN

| Change | Detail |
| --- | --- |
| `facilityLevel(?string $rank, ?string $stat = null)` | Branches on `facility_level_source`. `team_rank` → the old ladder walk, unchanged and extracted to `facilityLevelFromRank()`. `repetition` → the latest turn that recorded that stat. `repetition_plus_purchases` → that plus the catalogue-backed purchase increments |
| `$stat ??= $rank` | Under a repetition family there is no rank letter, so the first argument is read as the stat name there. The signature stays compatible: both existing call sites pass a rank and are unaffected |
| `latestRecordedFacility()` | Newest turn first, skipping a turn that recorded nothing for that stat. `loadMissing('turnEntries')` first, because a `fresh()` model holds an empty collection rather than a pending query — found during the RED and proven by a probe |
| `facilityPurchases()` | Reads the catalogue's `shop_items` for the one facility item, sums the recorded `Shop` events whose `item` matches, and ignores an unrecognised item |
| No-logged-turn policy | **`null`**, matching `burstCountdown`'s own answer for its missing input (`TeamPanelBuilder.php:721-724`). Not `1` (would claim the client's floor) and not `0` (not a level the client has) |

Neither call site changed: `TeamPanelBuilder.php:509` and `NextDecision.php:409` still pass a rank
letter, and `NextDecision`'s own gate (`latestTeamRank()`) means it only asks under Unity Cup, where
the `team_rank` branch answers exactly as before.

### Rule sweep (dimensions A-H)

| Dimension | Result |
| --- | --- |
| A. Status and feedback | **finding** — F1. The absence read as "no data" when the data existed |
| B. Control and target | `no finding`. No control |
| C. Error prevention and recovery | `no finding` |
| D. Consistency and signifiers | **finding** — F2. One method behaved as though every scenario were Unity Cup |
| E. Cognitive load and memory | **finding** — F3. The Trainer had to hold the repetition rule in their head |
| F. Visual grouping and hierarchy | `no finding` |
| G. Accessibility semantics and contrast | `no finding` |
| H. Content and language | `no finding` |

### Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F1 | `H4` | Usability heuristics | Team panel / advisor, a repetition scenario with logged turns | `facilityLevel()` answered `null` under URA Finale and Trackblazer over turns that recorded `facility_speed`. One component served one scenario's role for all four | OBSERVED_SOURCE | `TrainingRun.php:1346-1359` (pre-fix); `TrainingRun.php:1328-1339` for the gate that produced the null | 3 | High | Done: branch on the declared rule family |
| F2 | `H4` | Usability heuristics | Same | `config/scenarios.php` carried `facility_level_source` and nothing read it, so the config's own distinction was decorative | OBSERVED_SOURCE | `grep -rn facility_level_source app/` returned no read before this slice | 2 | High | Done |
| F3 | `GP-1` | Gerhardt-Powals | Same | The product holds the five per-turn levels and left the run-level figure for the Trainer to reconstruct from the Timeline | OBSERVED_SOURCE | `TimelineController.php:378-397` renders them per turn; no run-level reader existed | 3 | High | Done: `facilityLevel()` computes the figure |

### Unmeasured register

Empty. No visual claim is made: this slice's evidence is the runner's assertions on
`facilityLevel()`, plus the 14-case `career-cockpit-forms.spec.ts` run confirming the Unity Cup panel
is unaffected. The panel does **not** compose the ladder under a repetition scenario, so there is no
rendered repetition level to assert; that is a composition-matrix fact, not a gap in this slice.

### Gap register delta

**One new gap, recorded not fixed:** no scenario with `facility_level_source = repetition` composes
`team_rank_ladder`, so `facilityLevel()`'s repetition branch currently has no rendered caller. The
method is correct and the next surface that needs a run-level facility figure for URA, Grand Concert
or Trackblazer gets it; wiring one is a composition-matrix decision (which panel shows it), not part
of this row.

### Commit body basis

| # | Question | Result | Basis |
| --- | --- | --- | --- |
| 1 | Add, remove or move a control? | `N/A` | No control |
| 2 | Change what is on screen at once? | `N/A` | No payload key added or removed |
| 3 | Change a colour, tint or state? | `N/A` | No class change |
| 4 | Change a type size or weight? | `N/A` | No type change |
| 5 | Change what the user can undo, or where the action is? | `N/A` | No action |
| 6 | Change where a thing lives, or what it is called? | `N/A` | Nothing moved |
| 7 | Change a progress, an incomplete state or a boundary? | `PASS` | A repetition scenario's no-logged-turn answer is `null` rather than a floor value; the boundary is asserted |
| 8 | Change what the user has to read or remember? | `PASS` | Removes the reconstruction from the Timeline |
| 9 | Add a new destination, a landmark or a control type? | `N/A` | None |
| 10 | Change the words? | `N/A` | No copy changed |

`RENDERED_EVIDENCE = TRUE`; the only rendered run in this slice is `career-cockpit-forms.spec.ts`, 14
passed (1.9m), asserting the Unity Cup panel is unchanged.

### Verification output

```
vendor/bin/pest tests/Feature/ScenarioPanelTest.php --filter='§13.4'
  Tests:    4 passed (4 assertions)

vendor/bin/pest tests/Feature/ScenarioPanelTest.php tests/Feature/CockpitTeamPanelTest.php \
  tests/Feature/NextDecisionTest.php tests/Feature/UnityCupCountersTest.php tests/Feature/TurnFacilityLevelsTest.php
  Tests:    60 passed (847 assertions)
```

```
npx playwright test tests/browser/career-cockpit-forms.spec.ts
  14 passed (1.9m)
```

**Non-vacuity proof by negation.** With the branch replaced by the unconditional
`return $this->facilityLevelFromRank($rank);` and nothing else changed, the same filter produced
`2 failed, 2 passed` — the two repetition cases failed and both guards passed. Restored
byte-identically (`git diff --stat` back to 139 insertions / 4 deletions; the filter green again).

`pint` initially reported three fixers on the test file (`unary_operator_spaces`,
`not_operator_with_successor_space`, `ordered_imports`); the file was formatted with the explicit path
and re-verified `passed`. PHPStan: `[OK] No errors`.

## S2.3 — R6-04, cap labels name the wrong source

### Identity

| Item | Value |
| --- | --- |
| Audit ID | `RS-UC-R6-SLICE-AUDITS-S2.3` |
| Mode | M1 change audit |
| Row | R6-04 (register §4.1), facets (a) and (b) |
| Scope | `resources/js/pages/Career/BuildTarget.vue`, `resources/js/components/career/CareerStatePanel.vue`, `tests/browser/career-build-target.spec.ts`, `tests/Feature/CareerBuildTargetTest.php` |
| Out of scope | `CockpitController.php`'s numbers (the bars were already faithful — §13.7's "labels only" confirmed by measurement), `ScenarioCaps`'s arithmetic, the server payload |

### Pre-flight

| Item | Value |
| --- | --- |
| HEAD at open | `f83fcfdd` |
| Dirty set on the touched paths | Empty |
| `ScenarioCaps` separates both numbers already | `forRun()` (`:131-141`) overlays the entered caps on `scenarioCaps()` (`:178`); `enteredCaps()` (`:151`) reads the four columns. Nothing needed computing |
| `caps` vs `scenarioCaps` measured distinct | With entered `cap_speed = 1325` on a URA draft, `caps.Speed` is `1325` and `scenarioCaps.Speed` is `1400`; the sums differ, so a label naming the wrong one is visible rather than academic |
| Suite gate | `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` → 3 passed |
| `RENDERED_EVIDENCE` | **TRUE**, re-probed |

### RED evidence (captured before GREEN)

Browser, which is where the defect lives:

```
npx playwright test tests/browser/career-build-target.spec.ts --grep 'R6-04'
  Error: expect(locator).toContainText(expected) failed
  Expected pattern: /ceiling/i
  Received string:  "- / 1200 cap "
```

The feature-level assertions I first wrote **passed at RED**, and that is the register's own finding
restated: the numbers were already correct and only the words were wrong. Asserting the payload could
never fail. The rendered label is the assertion that matters, so the browser case is the primary RED
and the feature cases are the payload-shape guards that accompany it.

### GREEN

| File | Change |
| --- | --- |
| `BuildTarget.vue` denominator | `{{ entered(stat) ?? '-' }} / {{ cap(stat) }} cap` → `... ceiling`. The number is `ScenarioCaps::forRun()`, the run's own ceiling |
| `BuildTarget.vue` share line | `{{ percent(stat) }}% of cap` → `% of the ceiling`; the absence sentence likewise |
| `BuildTarget.vue` total row | `Sum of the five scenario caps` → `Sum of the five entered ceilings`. The sum is `totalCap`, which sums `caps` (the run's), so the old label named the scenario's published figure while printing the Trainer's |
| `BuildTarget.vue` props | Removed the declared-and-never-read `scenarioCaps`, with a comment recording why and noting the server still sends it |
| `CareerStatePanel.vue` caption | `Bars are drawn against the scenario ceiling.` → names both bounds: `the ceilings you entered where you entered one, the scenario's own figure where you did not`. The bars draw against `forRun()`, so the old sentence named one source for a figure that can be the other |

No number changed. `totalCap`, `cap()`, `entered()`, `percent()` and `ScenarioCaps` are untouched.

### Rule sweep (dimensions A-H)

| Dimension | Result |
| --- | --- |
| A. Status and feedback | `no finding`. No state word |
| B. Control and target | `no finding`. No control |
| C. Error prevention and recovery | `no finding` |
| D. Consistency and signifiers | **finding** — F1. Two labels named a source the figure did not come from |
| E. Cognitive load and memory | `no finding` |
| F. Visual grouping and hierarchy | `no finding` |
| G. Accessibility semantics and contrast | `no finding` |
| H. Content and language | **finding** — F2. `scenario cap` is the matrix's word for a figure the page was not printing |

### Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F1 | `GP-2` | Gerhardt-Powals | Build target, denominator and total row, populated | The helper printed `ScenarioCaps::forRun()` — the run's ceiling — and called it `cap`; the total row summed that same figure and called the sum `Sum of the five scenario caps`. A derived figure named a rule it was not using | OBSERVED_SOURCE | `BuildTarget.vue:446,525-526` (pre-fix) | 3 | High | Done: both labels name the ceiling they print |
| F2 | `H2` | Usability heuristics | Cockpit, state panel caption | `Bars are drawn against the scenario ceiling.` while the bars draw against `forRun()` — the entered ceiling where the run has one. The reference's own guard (`:444`) says a run's observed ceilings are "not the URA scenario cap" | OBSERVED_SOURCE | `CareerStatePanel.vue:164` (pre-fix) | 3 | High | Done: the caption names both bounds |

`GP-2` cited per register §11.5 (defined at `gerhardt-powals-principles-2026-10-21.md:118`, no
routing-table row).

### Unmeasured register

Empty. The primary assertion is rendered, not inferred.

### Gap register delta

None.

### Commit body basis

| # | Question | Result | Basis |
| --- | --- | --- | --- |
| 1 | Add, remove or move a control? | `N/A` | No control |
| 2 | Change what is on screen at once? | `N/A` | Same rows |
| 3 | Change a colour, tint or state? | `N/A` | No class change |
| 4 | Change a type size or weight? | `N/A` | No type change |
| 5 | Change what the user can undo, or where the action is? | `N/A` | No action |
| 6 | Change where a thing lives, or what it is called? | `PASS` | Four strings renamed; the figures keep their places |
| 7 | Change a progress, an incomplete state or a boundary? | `N/A` | No boundary moved |
| 8 | Change what the user has to read or remember? | `PASS` | The label now states which ceiling it prints instead of leaving the reader to infer it |
| 9 | Add a new destination, a landmark or a control type? | `N/A` | None |
| 10 | Change the words? | `PASS` | This slice is copy only, and the seven existing browser assertions that pinned `cap` were updated with a dated comment rather than deleted |

`RENDERED_EVIDENCE = TRUE`, declared from this slice's own probe: `career-build-target.spec.ts` 10
passed, including the new R6-04 case asserting the rendered denominator and total row.

### Verification output

```
npx playwright test tests/browser/career-build-target.spec.ts
  10 passed (24.2s)

vendor/bin/pest tests/Feature/CareerBuildTargetTest.php
  Tests:    15 passed (136 assertions)

vendor/bin/pest tests/Feature/CareerBuildTargetTest.php tests/Feature/CareerCockpitTest.php
  Tests:    60 passed (664 assertions)

vendor/bin/phpstan analyse --no-progress --memory-limit=1G
 [OK] No errors
```

**`career-cockpit.spec.ts` classified, not fixed:** `6 failed, 23 passed (3.9m)`. Measured
pre-existing and unrelated by stashing all four slice files, rebuilding, and re-running the same spec:
byte-identical `6 failed, 23 passed (3.9m)`. The failures are `[data-field="turn"]` and
`Open the Career Result` locators, not the caption this slice changed. Recorded per §9's
failure-attribution rule.

**Seven existing browser assertions updated, none deleted.** Each `cap` → `ceiling` change carries the
dated comment `Dated 2026-11-06 (R6-04)` on the first one and follows the deliberate copy change. The
`props.scenarioCaps` declaration was removed rather than left dangling; the server still sends the
prop, so a later reader can re-declare it without a payload change — recorded in the component.

## S2.4 — counter copy: neither line named what it counts to

### Identity

| Item | Value |
| --- | --- |
| Audit ID | `RS-UC-R6-SLICE-AUDITS-S2.4` |
| Mode | M1 change audit |
| Row | **§13.2, not an `R6-NN` row** |
| Scope | `app/Services/Advisor/TrainerAdvisor.php` (`finaleContext()`), `app/Services/Career/TeamPanelBuilder.php` (`burstCountdown()`), `tests/Feature/NextDecisionTest.php` |
| Out of scope | The arithmetic of either counter, `FINALE_TURNS_AHEAD`, `FinaleReader`, the payout turn |

### Pre-flight

| Item | Value |
| --- | --- |
| HEAD at open | `e55f5458` |
| Dirty set on the touched paths | Empty |
| §13.2's settlement confirmed | Two widgets, two bases: the finale is `(3 × 24) + 1 − nextTurnNumber()`, suppressed beyond 5 turns; the burst countdown is `70 − latestLoggedTurn`, null with no logged turn. Different events, so the numbers were never meant to agree |
| Label source | `TrainerAdvisor::finaleContext()` reads `FinaleReader::forRun()['label']`, which is `config("scenarios.scenarios.<key>.label")` — the scenario's own name, so the widget was structurally unable to distinguish the finals from the scenario |
| Suite gate | `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` → 3 passed |
| `RENDERED_EVIDENCE` | **TRUE**, re-probed |

### RED evidence (captured before GREEN)

```
Expected: Need 2 more bursts in 69 turns for Gold Lv3 (Senior November payout).
To contain: Spirit Burst
at tests\Feature\NextDecisionTest.php:266
```

The finale case failed at its own assertion too: the row was absent until the fixture seeded a finale
calendar row, and once present it read `Unity Cup is 5 turns away.` — no `finals`. Both records are in
the slice's run log; the burst string is quoted verbatim above because it is the copy the finding is
about.

### GREEN

| File | Change |
| --- | --- |
| `TrainerAdvisor::finaleContext()` | `'label' => $finale['label'].' finals'` on both the `passed` and the `upcoming`/`next` branches, so `Unity Cup finals is 5 turns away.` The word is composed **here**, not appended to the config label: `FinaleReader`'s label reaches other screens, and changing it would have reworded them all |
| `TeamPanelBuilder::burstCountdown()` | `... for Gold Lv3 (Senior November payout).` → `... to the Spirit Burst payout (Gold Lv3, Senior November).` The tier and the season stay, and the event is now named |

Copy only. The two derivations, `FINALE_TURNS_AHEAD`, the payout turn and `FinaleReader` are untouched.

### Rule sweep (dimensions A-H)

| Dimension | Result |
| --- | --- |
| A. Status and feedback | **finding** — F1. A figure was present but did not say what it was for |
| B. Control and target | `no finding`. No control |
| C. Error prevention and recovery | `no finding` |
| D. Consistency and signifiers | `no finding`. The two widgets use different words for different events, which is now honest |
| E. Cognitive load and memory | `no finding` |
| F. Visual grouping and hierarchy | `no finding` |
| G. Accessibility semantics and contrast | `no finding` |
| H. Content and language | **finding** — F2. `Gold Lv3` is a payout tier, not an event |

### Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F1 | `H1` | Usability heuristics | Cockpit, advisor `why` rows, a run 5 turns from the finale | `Unity Cup is 5 turns away.` — the scenario's own name. A reader cannot tell whether the count reaches the finals or the scenario's end, and the run's own name is already on the screen | OBSERVED_SOURCE | `TrainerAdvisor.php:246` (pre-fix) | 2 | High | Done: the line names the finals |
| F2 | `GP-2` | Gerhardt-Powals | Cockpit, burst countdown | `Need 2 more bursts in 69 turns for Gold Lv3 (Senior November payout).` names a tier and a season, so `69 turns` reads as time to the finals rather than to the payout — a different event, 70 vs 73 in this run's terms | OBSERVED_SOURCE | `TeamPanelBuilder.php:738` (pre-fix) | 2 | High | Done: the line names the Spirit Burst payout |

`GP-2` cited per register §11.5.

### Unmeasured register

Empty for this slice's own claims. The only rendered run was `scenario-panel.spec.ts` and
`career-cockpit-forms.spec.ts`, and no spec pins either string, so **no rendered assertion covers the
new copy**. That is stated rather than implied: the evidence is the payload, and a future browser case
asserting `Spirit Burst` on the panel would close it.

### Gap register delta

None.

### Commit body basis

| # | Question | Result | Basis |
| --- | --- | --- | --- |
| 1 | Add, remove or move a control? | `N/A` | No control |
| 2 | Change what is on screen at once? | `N/A` | Same rows |
| 3 | Change a colour, tint or state? | `N/A` | No class change |
| 4 | Change a type size or weight? | `N/A` | No type change |
| 5 | Change what the user can undo, or where the action is? | `N/A` | No action |
| 6 | Change where a thing lives, or what it is called? | `N/A` | Nothing moved |
| 7 | Change a progress, an incomplete state or a boundary? | `N/A` | Both counts unchanged |
| 8 | Change what the user has to read or remember? | `PASS` | Both lines name their event, so the reader no longer has to hold the two bases in mind |
| 9 | Add a new destination, a landmark or a control type? | `N/A` | None |
| 10 | Change the words? | `PASS` | Copy only; two sentences |

`RENDERED_EVIDENCE = TRUE`, but **no rendered assertion is made and none is claimed** — no browser
spec pins either string, so the verification is the payload.

### Verification output

```
vendor/bin/pest tests/Feature/NextDecisionTest.php --filter='§13.2'
  Tests:    2 passed (19 assertions)

vendor/bin/pest tests/Feature/NextDecisionTest.php tests/Feature/ScenarioPanelTest.php \
  tests/Feature/CockpitUnityCupCountersTest.php tests/Feature/UnityCupCountersTest.php \
  tests/Feature/CockpitFinaleStripTest.php
  Tests:    62 passed (978 assertions)

vendor/bin/phpstan analyse --no-progress --memory-limit=1G
 [OK] No errors
```

**`scenario-panel.spec.ts` classified, not fixed:** `3 failed, 5 passed (1.2m)`. Measured pre-existing
by stashing both source files and the test file, rebuilding, and re-running: byte-identical
`3 failed, 5 passed`. The failures are `Team Rank: not recorded`, `Documented`, and
`Partially documented` locators — documentation labels and a gauge, not the counter copy.

## S3.1 — R6-15, the duplicated `Hint Lvl` prefix

### Identity

| Item | Value |
| --- | --- |
| Audit ID | `RS-UC-R6-SLICE-AUDITS-S3.1` |
| Mode | M1 change audit |
| Row | R6-15 (register §4.1) |
| Scope | `resources/js/pages/Career/SkillsPlanner.vue`, `tests/browser/career-skills-planner.spec.ts`, `tests/Feature/SkillSpendCoverageTest.php` |
| Out of scope | `HintDiscount::priceLadder()` and its consumers (`SkillDetail`, compare) — a different ladder with the SHORT labels the prefix was written for |

### Pre-flight

| Item | Value |
| --- | --- |
| HEAD at open | `49a90c3a` |
| Dirty set on the touched paths | Empty |
| Source confirmed | `SkillsPlanner.vue:590` read `{{ 'Hint Lvl ' + step.label }}`; `HintDiscount::ladder()` already returns `'label' => $labels[$key]` → `Hint Lvl 1` … `Hint Lvl Max` |
| Two ladders, not one | `ladder()` (planner, long labels) and `priceLadder()` (`:58`, `Lv1` … `Lv Max`) are separate. The prefix was written for the short form and applied to the long one |
| Suite gate | `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` → 3 passed |
| `RENDERED_EVIDENCE` | **TRUE**, re-probed |

**A stale comment corrected by measurement.** `tests/Feature/CareerSkillsPlannerTest.php:24-27` says
"the hint-discount ladder was removed on the owner's ruling (A5)" and that "no ladder survives on the
props". Measured: `SkillsPlannerController.php:54` still sends `'hint_discount' => HintDiscount::ladder()`,
the prop is still declared at `SkillsPlanner.vue:109`, the table still renders at `:585-590`, and
`SkillSpendCoverageTest.php:112` asserts the prop key exists. The comment is stale; the code wins
(`.ai/rules/index.md`). The case that owns the ladder is `SkillSpendCoverageTest`, so the RED assertion
went there.

### RED evidence (captured before GREEN)

```
- Expected  - 5
+ Received  + 5
  Array [
-   "Hint Lvl 1",
+   "Hint Lvl Hint Lvl 1",
    ...
+   "Hint Lvl Hint Lvl Max",
```

All five rows duplicated the prefix. The feature-level assertion on the payload label passed at RED —
the label was always correct at source, so the duplication could only be seen rendered.

### GREEN

`SkillsPlanner.vue:590`: `{{ 'Hint Lvl ' + step.label }}` → `{{ step.label }}`. One line, and the
`priceLadder()` path is untouched because it is a different function with its own call sites.

### Rule sweep (dimensions A-H)

| Dimension | Result |
| --- | --- |
| A. Status and feedback | `no finding` |
| B. Control and target | `no finding` |
| C. Error prevention and recovery | `no finding` |
| D. Consistency and signifiers | **finding** — F1. One caption had two owners |
| E. Cognitive load and memory | `no finding` |
| F. Visual grouping and hierarchy | `no finding` |
| G. Accessibility semantics and contrast | `no finding` |
| H. Content and language | **finding** — F2. `Hint Lvl Hint Lvl 1` is not a client caption |

### Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F1 | `H4` | Usability heuristics | Skills planner, the hint-ladder card's Client caption column | Every row doubled its prefix: `Hint Lvl Hint Lvl 1` … `Hint Lvl Hint Lvl Max`. The server returns the full caption and the template prefixed it again | OBSERVED_RENDERED | `SkillsPlanner.vue:590` (pre-fix) | 1 | High | Done: render the server's label |
| F2 | `H2` | Usability heuristics | Same | The ladder's short form (`Lv1` … `Lv Max`) is what the prefix was written for; applying it to the long form produced a string no client prints | OBSERVED_SOURCE | `HintDiscount.php:60-66` for the short form, `:24-31` for the long one | 1 | High | Done |

### Unmeasured register

Empty.

### Gap register delta

None.

### Commit body basis

| # | Question | Result | Basis |
| --- | --- | --- | --- |
| 1 | Add, remove or move a control? | `N/A` | No control |
| 2 | Change what is on screen at once? | `N/A` | Same table |
| 3 | Change a colour, tint or state? | `N/A` | No class change |
| 4 | Change a type size or weight? | `N/A` | No type change |
| 5 | Change what the user can undo, or where the action is? | `N/A` | No action |
| 6 | Change where a thing lives, or what it is called? | `N/A` | The caption now matches its own source |
| 7 | Change a progress, an incomplete state or a boundary? | `N/A` | No boundary |
| 8 | Change what the user has to read or remember? | `PASS` | Removes a stutter, not a memory load |
| 9 | Add a new destination, a landmark or a control type? | `N/A` | None |
| 10 | Change the words? | `PASS` | Five captions, one deleted prefix |

`RENDERED_EVIDENCE = TRUE`, declared from this slice's own probe: `career-skills-planner.spec.ts` 13
passed (34.7s), including the new case asserting all five captions and the absence of
`Hint Lvl Hint Lvl`.

### Verification output

```
npx playwright test tests/browser/career-skills-planner.spec.ts
  13 passed (34.7s)

vendor/bin/pest tests/Feature/SkillSpendCoverageTest.php tests/Feature/CareerSkillsPlannerTest.php \
  tests/Feature/SkillHintLevelTest.php tests/Feature/SkillDetailTest.php
  Tests:    53 passed (735 assertions)
```

## S3.2 — R6-23, `Pal slot` names a card type on a position

### Identity

| Item | Value |
| --- | --- |
| Audit ID | `RS-UC-R6-SLICE-AUDITS-S3.2` |
| Mode | M1 change audit |
| Row | R6-23 (register §4.1) |
| Scope | `resources/js/components/support/SupportSlot.vue`, `tests/browser/career-legacy-deck-steps.spec.ts`, `tests/browser/support-deck.spec.ts` |
| Out of scope | **R4-20's auto-fill defect** (`DeckSelect.vue:296-298` seeds `ownership \|\| 'OWNED'` on equip) — a different mechanism on the same screen, still open, not closed by this slice. Also out: the `Card types` table's `Pal` row, which is correct card-type vocabulary |

### Pre-flight

| Item | Value |
| --- | --- |
| HEAD at open | `847e4cdb` |
| Dirty set on the touched paths | Empty |
| The contradiction measured in one rendered string | Before the fix, `#deck-slot-6` read: `Slot 6 · Friends Pal slot  Not equipped. …` — the server's label said `Friends` and the pill under it said `Pal` |
| Server's own word | `DeckSlot::positionLabel(6)` returns `Slot 6 · Friends`. `ADR-0014:72-74` (quoted in register §6, C6) ratifies `Friends` for the slot and never mentions `Pal` |
| `Pal` is a card type | `SupportCard.php:127` maps the export's `friend` key to `Pal` as one of the seven types. Correct there, wrong as a position label |
| Suite gate | `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` → 3 passed |
| `RENDERED_EVIDENCE` | **TRUE**, re-probed |

### RED evidence (captured before GREEN)

```
Error: expect(locator).toContainText(expected) failed
Expected substring: "Friends slot"
Received string:    "Slot 6 · Friends Pal slot  Not equipped. OwnershipN/A: No ownership has been
recorded for this slot yet.OwnedRentedChoose a card  in Slot 6 · Friends"
```

Both existing specs asserted `Slot 6 · Friends` and `Pal slot` **in adjacent lines**, so the suite was
pinning the contradiction. Both assertions were corrected rather than added to.

### GREEN

`SupportSlot.vue`: `Pal slot` → `Friends slot`, agreeing with `DeckSlot::positionLabel()`. The
component is shared by `DeckSelect.vue:40` (the wizard step) and `Support/Builder.vue:7` (the
run-scoped deck), so one string fixes both surfaces.

The `Card types` table's `Pal` row is untouched: `Pal` is the correct word for the card type, and only
its use as a *position* label was wrong.

### Rule sweep (dimensions A-H)

| Dimension | Result |
| --- | --- |
| A. Status and feedback | `no finding` |
| B. Control and target | `no finding` |
| C. Error prevention and recovery | `no finding` |
| D. Consistency and signifiers | **finding** — F1. Two labels for one slot, disagreeing |
| E. Cognitive load and memory | `no finding` |
| F. Visual grouping and hierarchy | `no finding` |
| G. Accessibility semantics and contrast | `no finding` |
| H. Content and language | **finding** — F2. A card type's word used as a position's |

### Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F1 | `GT-4` | Gestalt | Deck step and run deck, slot six, empty and equipped | The slot printed `Slot 6 · Friends` and then `Pal slot` beside it. Two look-alike labels for one thing, one of them using the word for a different thing | OBSERVED_RENDERED | `SupportSlot.vue:136` (pre-fix); the rendered string is quoted verbatim above | 2 | High | Done: one word, `Friends` |
| F2 | `H2` | Usability heuristics | Same | `Pal` is the client's word for a **card type** (`SupportCard.php:127`), not for a position. `ADR-0014:72-74` ratifies `Friends` for the slot and never mentions `Pal`; the swap landed in `0c3fc35c` with no comment and no ruling (register §6, C6) | OBSERVED_SOURCE | `DeckSlot.php:73` for the correct word; `SupportCard.php:127` for the type | 2 | High | Done |

### Unmeasured register

Empty.

### Gap register delta

None. **R4-20 remains open and is explicitly not closed here**: `DeckSelect.vue:296-298` still seeds
`ownership || 'OWNED'` on equip, so a borrowed card silently reads as owned until the second button is
noticed. Register §11.1 facet (b) assigns that to R4-20's row; renaming the slot does not touch it.

### Commit body basis

| # | Question | Result | Basis |
| --- | --- | --- | --- |
| 1 | Add, remove or move a control? | `N/A` | No control |
| 2 | Change what is on screen at once? | `N/A` | Same pill, same place |
| 3 | Change a colour, tint or state? | `N/A` | Pill class unchanged |
| 4 | Change a type size or weight? | `N/A` | No type change |
| 5 | Change what the user can undo, or where the action is? | `N/A` | No action |
| 6 | Change where a thing lives, or what it is called? | `PASS` | The position's label now uses the position's word |
| 7 | Change a progress, an incomplete state or a boundary? | `N/A` | No boundary |
| 8 | Change what the user has to read or remember? | `PASS` | Removes a second name for one slot |
| 9 | Add a new destination, a landmark or a control type? | `N/A` | None |
| 10 | Change the words? | `PASS` | One word, two surfaces |

`RENDERED_EVIDENCE = TRUE`, declared from this slice's own probe: `support-deck.spec.ts` and
`career-legacy-deck-steps.spec.ts` now assert `Friends slot` and the absence of `Pal slot`, on the
rendered pill.

### Verification output

```
npx playwright test tests/browser/support-deck.spec.ts tests/browser/career-legacy-deck-steps.spec.ts \
  tests/browser/career-deck-select.spec.ts
  45 passed (1.6m), 2 failed

vendor/bin/pest tests/Feature/SupportDeckBuilderTest.php tests/Feature/CareerLegacyDeckStepsTest.php
  Tests:    43 passed (709 assertions)
```

**`career-deck-select.spec.ts`'s two failures classified, not fixed:** `2 failed, 16 passed (58.2s)`,
and byte-identical with all three slice files stashed. The failures are a `Preflight writes it onto
the run as one value per slot` prose assertion and the HFW three-diff — neither is the slot label.









