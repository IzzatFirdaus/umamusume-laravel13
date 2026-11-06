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



