# Round 7 — slice audits (`RS-UC-R7-SL`)

**Status:** append-only ledger. One section per landed slice, ten sub-sections each. The register's §4 statuses stay
frozen at `8a3cf21c`; this file records what landed **after** that read, and what each slice did **not** close.

**Opened:** 2026-11-07. **Base commit:** `05be6616`.

---

## §0 Session baseline

```
$ git rev-parse HEAD
05be66164c71fa4b01f717adf60c22601ec1e265

$ git stash list
stash@{0}: WIP on master: b4458c09 fix(raceplanner): remove the duplicate closing template tag the group loop left
stash@{1}: On master: peer-traineeprofile
stash@{2}: On master: Phase 2.1 work in progress (foreign, pre-state)
```

Three stash entries, none this session's, none touched.

```
$ git status --short -- <the six peer paths>
 D docs/audits/AUDIT-SUPPORT-CARDS-UIUX-2026-11-05.md
 M docs/imports/Umamusume_Progress_Tracker_Runs.md
 M docs/imports/ura-finale-runs.md
 D docs/proposals/PLAN_SLICES_6_7.md
 M docs/research-scratch/AUDIT-AND-VERIFICATION.md
 M docs/research-scratch/PROCESS-PLANS.md

$ git status --short
 D docs/audits/AUDIT-SUPPORT-CARDS-UIUX-2026-11-05.md
 M docs/imports/Umamusume_Progress_Tracker_Runs.md
 M docs/imports/ura-finale-runs.md
 D docs/proposals/PLAN_SLICES_6_7.md
 M docs/research-scratch/AUDIT-AND-VERIFICATION.md
 M docs/research-scratch/PROCESS-PLANS.md
?? docs/audits/RS-UC-2026-10/
?? docs/audits/RS-UC-2026-11-snapshot-door/
?? docs/audits/RS-UC-2026-12/
?? docs/audits/rice-shower-unity-cup-ux-walk-6.md
?? docs/audits/rice-shower-unity-cup-ux-walk-7-snapshot-door.md
?? docs/audits/rice-shower-unity-cup-ux-walk-7.md
?? docs/imports/ura-finale-runs.md.bak
?? screen_data.json
```

**Lore baseline at session start:** docs `556 hit(s), 75 exempt line(s)`; code `556 hit(s), 75 exempt line(s)`.

**Path corrections:** eight dispatch paths were wrong at this commit. All eight are resolved in
`docs/audits/rice-shower-unity-cup-round7-path-corrections.md`, which precedes every slice below.

---

## §0.1 Ledger index

| Slice | Rows | Commit | Result |
| --- | --- | --- | --- |
| S3 | R7-82, R7-22, R7-25, R7-61 | `27a3d374` | **landed** — `#s3--r7-82-r7-22-r7-25-r7-61` |
| S5 | R7-05 | `da61a04f` | **landed** — `#s5--r7-05` |
| S1 | R7-20 | — | **HELD** — pre-existing red in `career-training-detail.spec.ts`, see `#stop--pre-existing-red-training-detail` |
| S2 | R7-26 | `4efa0876` | **landed** — `#s2--r7-26` |
| S0 | — (housekeeping) | `58bb98a7` | **landed** — `#s0--housekeeping` |
| S6 | R7-19, R7-66 | `c6ac05e7` | **landed** — `#s6--r7-19-r7-66` |

---

## Legend

Each landed slice appends a section with exactly these ten sub-sections:

1. **Slice, rows, register reference** — the ID, the `R7-XX` rows, and where the register classifies them.
2. **Pre-flight** — the verbatim output of the two pre-flight commands.
3. **RED** — the failing assertion, its `file:line`, and its exact message.
4. **GREEN** — the passing assertion and its `file:line`.
5. **Non-vacuity** — the path-scoped revert, and the failure it produced on re-run.
6. **Regression** — the adjacent suites' output.
7. **Gates** — each gate's own result, including the ones not run and why.
8. **Lore delta** — `Lore docs: N (was M); code: P (was Q)`, with a ruling if either moved.
9. **Commit** — the SHA and the exact pathspec.
10. **What this slice did not close** — every facet the register's row left open, named.

---

## §s3 — S3: a past objective with no record reads `not_reached`, not `missed`

### 1. Slice, rows, register reference
**S3.** Rows `R7-82` (blocking, the dropped cleared-objectives finding), `R7-22`, `R7-25`, `R7-61`. Filed in the
register's §4.1 (`R7-22`, `R7-25`, `R7-61`) and §4.4 (`R7-82`), all `Confirmed defect` at `8a3cf21c`.

### 2. Pre-flight
```
$ git status --short -- app/Services/Career/TeamPanelBuilder.php tests/Feature/ScenarioPanelTest.php tests/Feature/CareerCockpitTest.php tests/browser/scenario-panel.spec.ts
(empty — CLEAN)

$ vendor/bin/pest tests/Feature/ScenarioMetadataTest.php
  Tests:    3 passed (52 assertions)
  Duration: 1.90s
```

### 3. RED
New case in `tests/Feature/TraineeGoalsPanelTest.php:263-279`:
`it('reads a past objective the run never played as not reached, not as missed')`.
Run: `vendor/bin/pest tests/Feature/TraineeGoalsPanelTest.php --filter='not reached'`

```
-'not_reached'
+'missed'
  at tests\Feature\TraineeGoalsPanelTest.php:277
  Tests:    1 failed (8 assertions)
```

The message is the walk's own harm: a Junior debut objective at turn 12 and a Senior goal at turn 54, on a run whose
position has passed both, with no `race_entries` — the panel reported `missed`.

**Test-home correction:** the dispatch named `tests/Feature/TeamPanelBuilderTest.php`, which does not exist. The
builder's objective states are asserted in `TraineeGoalsPanelTest.php` (via the cockpit's `scenario.objectives`
payload), and `CockpitObjectivesTest.php` contains no `TeamPanelBuilder` reference. Recorded in
`round7-path-corrections.md` §2.2.

### 4. GREEN
`app/Services/Career/TeamPanelBuilder.php`:
- new `objectiveHasRecord()` helper beside `objectiveMet()`
- objectives arm, `:255-268` — `$reached` now splits on whether a race entry exists
- mandatory-race arm, `:109-118` — `$reached` yields `not_reached`

```
$ vendor/bin/pest tests/Feature/TraineeGoalsPanelTest.php
  ✓ it reads a past objective the run never played as not reached, not as missed
  Tests:    20 passed (208 assertions)
```

**Renderer half, found while running the slice:** four cockpit panels mapped `missed` and nothing else, so a
`not_reached` state fell through `words[state] ?? state` and would have printed the raw token. All four now carry
`not_reached: '–'` (glyph) and `not_reached: 'Not reached'` (word): `CareerGoalsPanel.vue`, `CareerMilestonesPanel.vue`,
`TeamRankLadderPanel.vue`, `UraMandatoryLadder.vue`. Without this the fix would have been server-correct and
screen-wrong.

### 5. Non-vacuity
```
$ git diff app/Services/Career/TeamPanelBuilder.php > .scratch-uma/r7-slices/S3.patch   # 51 lines
$ git checkout -- app/Services/Career/TeamPanelBuilder.php
$ vendor/bin/pest ... --filter='not reached'
  ⨯ it reads a past objective the run never played as not reached, not as missed
  -'not_reached'
  +'missed'
$ git apply .scratch-uma/r7-slices/S3.patch
$ vendor/bin/pest ... --filter='not reached'
  Tests:    1 passed (9 assertions)
$ git diff app/Services/Career/TeamPanelBuilder.php | diff - .scratch-uma/r7-slices/S3.patch
  DIFF MATCHES PRE-REVERT
```
The fix is doing the work: reverting it restores the exact failure.

### 6. Regression
```
$ vendor/bin/pest tests/Feature/TraineeGoalsPanelTest.php tests/Feature/CockpitObjectivesTest.php     tests/Feature/ScenarioPanelTest.php tests/Feature/CareerCockpitTest.php     tests/Feature/Career/SpiritBurstCountdownTest.php tests/Feature/TraineeGoalLadderStateTest.php
  Tests:    116 passed (1423 assertions)
  Duration: 12.64s
```

### 7. Gates
| Gate | Command | Result |
| --- | --- | --- |
| Focused test | `pest TraineeGoalsPanelTest.php` | **20 passed (208 assertions)** |
| Regression | six adjacent suites | **116 passed (1423 assertions)** |
| Typecheck | `npm run typecheck` | **clean** (`vue-tsgo` + `tsc`) |
| Pint | `pint --test --format agent <2 paths>` | `{"tool":"pint","result":"passed"}` |
| PHPStan | `phpstan analyse --memory-limit=1G app/Services/Career/TeamPanelBuilder.php` | `[OK] No errors` |
| Lore docs | `php tools/lore.php` | **556 hits, 75 exempt** |
| Lore code | `php tools/lore.php --code` | **556 hits, 75 exempt** |
| Fence | `md_fence_check.py <register + slice-audits>` | **0 invalid** |
| Browser | not run | No browser spec asserts objective states (`grep missed\|not_reached tests/browser/scenario-panel.spec.ts` → no state assertion). The change is a server payload plus four renderer maps; the renderer maps have no spec coverage. **UNMEASURED** — stated, not implied away |

**Environment note:** `node_modules/` was an empty directory at session start, so `npm run typecheck` failed with
`'vue-tsgo' is not recognized`. Ran `npm ci` (167 packages, 12s) to unblock. `package.json` and `package-lock.json`
are untouched (`git status --short` on both is empty), so no dependency changed.

### 8. Lore delta
`Lore docs: 556 (was 556); code: 556 (was 556)`. **Delta 0.**

### 9. Commit
`27a3d374` — `fix(career): read a past objective with no record as not reached, not missed (R7-82, R7-22, R7-25, R7-61)`

```
app/Services/Career/TeamPanelBuilder.php
resources/js/pages/Career/Cockpit/Panels/CareerGoalsPanel.vue
resources/js/pages/Career/Cockpit/Panels/CareerMilestonesPanel.vue
resources/js/pages/Career/Cockpit/Panels/TeamRankLadderPanel.vue
resources/js/pages/Career/Cockpit/Panels/UraMandatoryLadder.vue
tests/Feature/TraineeGoalsPanelTest.php
```
`git diff HEAD~1 HEAD --name-only` returned exactly these six. Peer set unchanged.

### 10. What this slice did not close
- **`R7-81`** (`U-19`, `Entry criteria met!` with no `entry criteria` state) — same root, different surface, still
  `Unrepresented source data`.
- **`R7-22`'s alert half.** The row's `current` verdict on the *alert* path (`:291-293`) is unchanged: a past
  objective in the run's current year can still alert. Only the row `state` moved.
- **`R7-61`** (phase copy calling `Junior Make Debut` "the one dated obligation") — a copy consequence outside this
  file; untouched.
- **Browser coverage** of the four renderer maps. No spec asserts them; UNMEASURED above.

---

## §s5 — S5: the entered-ceilings total sums what was entered

### 1. Slice, rows, register reference
**S5.** Row `R7-05`, `Confirmed defect`, register §4.1. The label moved in R6-04's S2.3 (`e55f5458`); the figure under
it did not (register C7).

### 2. Pre-flight
```
$ git status --short -- resources/js/pages/Career/BuildTarget.vue tests/Feature/CareerBuildTargetTest.php tests/browser/career-build-target.spec.ts
(empty — CLEAN)

$ vendor/bin/pest tests/Feature/ScenarioMetadataTest.php
  Tests:    3 passed (52 assertions)
```

### 3. RED
`tests/browser/career-build-target.spec.ts:129-136`, added to the existing R6-04 case:

```ts
const totalRow = page.locator('[data-field="total-cap"]');
await expect(totalRow).toContainText(/No ceilings entered/i);
await expect(totalRow).not.toContainText('7000');
```

First run failed at `:127` — the **label** assertion, because `public/build` was stale and Vite had not rebuilt the
`.vue`. After `npm run build`, the case reached the R7-05 assertions:

```
Error: expect(locator).toContainText(expected) failed
Expected pattern: /No ceilings entered/i
Received string:  "Sum of the entered ceilings6000"
  at tests/browser/career-build-target.spec.ts:134
```

**`6000` is the four published floors (Speed 1200 + Stamina 1200 + Power 1200 + Guts 1200) printed under a label that
says "entered".** The walk recorded `7000` on a five-stat sum; the arithmetic differs because `capStats` carries four
entries, not five. Same defect, and the measured figure is recorded here rather than the walk's.

### 4. GREEN
`resources/js/pages/Career/BuildTarget.vue`:
- `totalCap` replaced by `totalEnteredCap`, which maps `props.capStats` through `props.enteredCaps[capColumn(stat)]`
  and returns `null` when every entry is empty
- the row gains `data-field="total-cap"`, prints `No ceilings entered` on `null`, and the label reads
  `Sum of the entered ceilings` (was `Sum of the five entered ceilings` — five was wrong, only four are enterable)

```
$ npx playwright test tests/browser/career-build-target.spec.ts --reporter=line
  10 passed (27.7s)
```

### 5. Non-vacuity
Two proofs, because the revert path alone only exercised the label:

**(a) whole-file revert** — `git diff BuildTarget.vue > S5.patch` (55 lines), `git checkout --`, rebuild, re-run:
failed again at `:127` on the label. Re-applied; `git diff | diff - S5.patch` → `DIFF MATCHES PRE-REVERT`.

**(b) computed-only revert** — the defective `totalCap` restored with the new markup kept, so the R7-05 assertion is
reachable in isolation:
```
Expected pattern: /No ceilings entered/i
Received string:  "Sum of the entered ceilings6000"
  at tests/browser/career-build-target.spec.ts:134
```
Restored from `.bak`; `git diff | diff - S5.patch` → `DIFF MATCHES`; rebuild; full spec `10 passed (22.9s)`.

Proof (b) is the one that binds: it shows the figure assertion, not the label assertion, is what the computation
fixes.

### 6. Regression
```
$ vendor/bin/pest tests/Feature/CareerBuildTargetTest.php tests/Feature/CareerPreflightTest.php tests/Feature/CareerCockpitTest.php
  Tests:    84 passed (990 assertions)
```

### 7. Gates
| Gate | Result |
| --- | --- |
| Focused browser | `career-build-target.spec.ts` → **10 passed** |
| Regression | three Feature suites → **84 passed (990 assertions)** |
| Typecheck | `npm run typecheck` → **clean** |
| Build | `npm run build` → **✓ built in 13.79s** |
| Pint | `{"tool":"pint","result":"passed"}` |
| PHPStan | not run — no PHP file changed (**UNMEASURED**, stated) |
| Lore docs / code | **556 / 75 exempt**, both |
| Fence | **0 invalid** |

**Environment note:** `npm run build` was required before the browser case could see the change, and was re-run three
times during non-vacuity. `public/build` is gitignored (`git check-ignore` confirms), so no build output is committed.

### 8. Lore delta
`Lore docs: 556 (was 556); code: 556 (was 556)`. **Delta 0.**

### 9. Commit
`da61a04f` — `fix(career): the entered-ceilings total sums what was entered, not the scenario floor (R7-05)`

```
resources/js/pages/Career/BuildTarget.vue
tests/browser/career-build-target.spec.ts
```

### 10. What this slice did not close
- **`R7-48`** (`E-10`, only four of five stats have a ceiling input) — `Known limitation`, unchanged. This slice makes
  the total honest about the four; it does not add a fifth.
- **`R7-49`** (`E-11`, `TOTAL AGAINST THE PUBLISHED SAMPLE` prints `N/A` both sides) — untouched.
- **The walk's `7000` vs the measured `6000`.** Recorded in §3; the walk's figure was not reproducible because
  `capStats` carries four entries. The register's row is not edited.

---

## §stop--pre-existing-red-training-detail — S1 held: the training spec is red at HEAD, not from this session

**Classified: pre-existing product defect, not caused by any slice in this session, not in the register.**

### What is measured

`tests/browser/career-training-detail.spec.ts` is **red at `21cf63b0` on a clean working copy** — verified by
`git checkout -- tests/browser/career-training-detail.spec.ts` and re-running. Every case in the file fails, not only
the one this session added, and the failure is inside the shared `recordFirstTurn()` helper at
`tests/browser/career-training-detail.spec.ts:34`:

```
Error: locator.fill: Test timeout of 180000ms exceeded.
  waiting for locator('input[name="speed"]')
  locator resolved to <input ... id="turn-speed" ...>
  338 × waiting for element to be visible, enabled and editable
    - element is not visible
```

### Why

The disclosure is **open by default** and the helper clicks it **closed**:

```
resources/js/pages/Career/TrainingDetail.vue:740
    <details class="card mt-4" open>

tests/browser/career-training-detail.spec.ts:34
    await page.getByText('Client totals this turn (required to record)').click();
```

Measured in a live browser at `http://127.0.0.1:8000/training-runs/3/training`:

```json
{"found": true, "before": true, "after": false, "speedVisible": true,
 "summaryText": "Client totals this turn (required to record)"}
```

`before: true` — the element already carries `open`. The click **removes** it, hiding the very fields the helper then
tries to fill. `speedVisible: true` before the click proves the fields are reachable without clicking at all.

### Dating both halves

```
$ git blame -L 740,740 HEAD -- resources/js/pages/Career/TrainingDetail.vue
dcc562b63 (IzzatFirdaus 2026-11-04 00:49:16 +0800 740)  <details class="card mt-4" open>

$ git blame -L 127,133 HEAD -- tests/browser/career-training-detail.spec.ts
2b44b45bd (IzzatFirdaus 2026-10-29 02:31:26 +0800 127)  test('the client totals stay behind one disclosure, closed by default', ...
2b44b45bd (IzzatFirdaus 2026-10-29 02:31:26 +0800 132)      await expect(details).not.toHaveAttribute('open', '');
```

**The spec has asserted closed-by-default since 2026-10-29. `dcc562b6` added `open` on 2026-11-04, five days later.**
The spec's own header at `:14` states the intent in prose — *"the client totals the write boundary requires live in one
**collapsed** disclosure, which is the documented deviation (HFW OVERRIDE (write fields) in the page)"* — so the
attribute contradicts both the test and the page's own documentation.

### Why this is a stop, not a fix

The dispatch's §5 is explicit: *"A gate reveals a pre-existing red the slice did not cause — classify and record, do
not fix."* Removing `open` is a one-attribute edit, but it is **not S1's scope**: S1 relabels the turn field. Touching
the disclosure's default state would also silently change a rendered surface that the walk did not classify, which is
exactly the re-classification this round forbids.

**It blocks more than S1**: `career-training-detail.spec.ts` is the browser home for S1, S13 and S14. All three are
held behind it.

### What is owed

Either the owner rules that the disclosure is open by default — in which case the spec's assertion and its
`recordFirstTurn` helper must change, and that is its own slice — or `dcc562b6`'s `open` is a defect and removing it
restores the documented state. **A one-line owner ruling resolves it.** Until then, S1, S13 and S14 cannot run their
browser REDs and are held with this reason.

**Nothing in this session changed either file.** `git status --short` on both is empty.

---

## §s2 — S2: the maiden lock clears from the run's position, not only from a record

### 1. Slice, rows, register reference
**S2.** Row `R7-26`, `Confirmed defect`, **blocking**, register §4.1. Owner ruling **(4)** — derive the lock from the
run's effective position — recorded in `round7-owner-calls.md` §2.

### 2. Pre-flight
```
$ git status --short -- app/Models/TrainingRun.php app/Http/Controllers/Career/RaceDecisionController.php tests/Feature/CareerRaceDecisionTest.php tests/browser/career-race-decision.spec.ts
(empty — CLEAN)

$ vendor/bin/pest tests/Feature/ScenarioMetadataTest.php
  Tests:    3 passed (52 assertions)
```

### 3. RED
New case in `tests/Feature/CareerRaceDecisionTest.php`:
`it('clears the maiden lock from the run position when the run carries no Debut record')`.

```
$ vendor/bin/pest tests/Feature/CareerRaceDecisionTest.php --filter='clears the maiden lock from the run position'
  ➜ 200▕     expect($run->hasClearedMaidenLock())->toBeTrue();
  Tests:    1 failed (1 assertions)
```

The message is the walk's harm: a run at Senior Early October with no `race_entries` reports the lock uncleared.

**Test-shape note, found while writing the RED:** the first version asserted `races.0.maiden_gated` on the turn-67 run
and failed with `Property [races.0.maiden_gated] does not exist` — the `raceSlot()` helper defaults its slot to Junior
turn 13, and a run at turn 67 is not offered a turn-13 race. The case was split: the **model** assertion runs on the
turn-67 run, the **payload** assertion on a turn-13 run whose position sits on the slot. Both are in the same case, so
neither half is lost.

### 4. GREEN
`app/Models/TrainingRun.php` — `hasClearedMaidenLock()` gains a leading check:

```php
if ($this->nextTurnNumber() - 1 > 12) {
    return true;
}
```

`nextTurnNumber()` is the effective position **plus one** for both doors: a wizard run reads its last logged turn
(`:678`), a snapshot reads `career_position.turnIndex` (`:685`). Subtracting one therefore yields the position without
a new accessor. Boundary is `> 12`, not `>= 12` — the ruling's own text: at turn 12 the Debut may not have resolved, by
turn 13 it has. `maidenGates()` is unchanged, so Debut/Maiden slots are still not gated.

```
$ vendor/bin/pest tests/Feature/CareerRaceDecisionTest.php
  Tests:    20 passed (380 assertions)
```

### 5. Non-vacuity
```
$ git diff app/Models/TrainingRun.php > .scratch-uma/r7-slices/S2.patch    # 23 lines
$ git checkout -- app/Models/TrainingRun.php
$ vendor/bin/pest ... --filter='clears the maiden lock from the run position'
  Failed asserting that false is true.
  Tests:    1 failed (1 assertions)
$ git apply .scratch-uma/r7-slices/S2.patch
$ git diff app/Models/TrainingRun.php | diff - .scratch-uma/r7-slices/S2.patch
  DIFF MATCHES
$ vendor/bin/pest ... --filter='clears the maiden lock from the run position'
  Tests:    1 passed (12 assertions)
```

### 6. Regression
```
$ vendor/bin/pest tests/Feature/CareerRaceDecisionTest.php tests/Feature/CareerRacePlannerTest.php tests/Feature/CareerCockpitTest.php
  Tests:    86 passed (1293 assertions)
```

**The pre-existing gate case survives.** `it('gates a standard race on the run-level maiden lock and clears it on a
debut win')` uses `raceRun(turns: 12)`, i.e. effective position 12, which `> 12` correctly leaves gated.

### 7. Gates
| Gate | Result |
| --- | --- |
| Focused test | `CareerRaceDecisionTest` → **20 passed (380 assertions)** |
| Regression | three suites → **86 passed (1293 assertions)** |
| Pint | `{"tool":"pint","result":"passed"}` |
| PHPStan | `[OK] No errors` on `TrainingRun.php` |
| Lore docs / code | **556 / 75 exempt**, both — delta 0 |
| Fence | **0 invalid** |
| Browser | `career-race-decision.spec.ts` → **8 passed, 2 skipped, 0 failed** |
| Typecheck / build | not run — no `.ts`/`.vue` changed (**UNMEASURED**, stated) |

### 8. Lore delta
`Lore docs: 556 (was 556); code: 556 (was 556)`. **Delta 0.**

### 9. Commit
`4efa0876` — `fix(career): clear the maiden lock from the run position past turn 12 (R7-26)`

```
app/Models/TrainingRun.php
tests/Feature/CareerRaceDecisionTest.php
```

### 10. What this slice did not close
- **`R7-75`** (`U-13`, the Daishoten card's own absence) — the lock no longer blocks, but the race card still has no
  field of its own. `Confirmed defect`, unchanged.
- **`R7-26`'s calendar half.** The lock is clear, but whether the race desk carries the source's race at the run's turn
  is a separate question the register answers at the surface (`R7-75`).
- **The turn-12 boundary itself** is a source-derived inference (Junior Late June), recorded in the ruling's evidence
  table, not a value the tree states. If a future source contradicts it, the constant moves — it is a literal `12`,
  not a config entry.

---

## §stop--pre-existing-red-denominator-caption — S6 held: two pre-existing reds, neither caused here

**Classified: pre-existing test staleness at `2a04f2b5`, independent of S6's change. Recorded, not fixed.**

### Red 1 — `career-cockpit.spec.ts`, six cases

`tests/browser/career-cockpit.spec.ts` fails six cases on a **clean HEAD build**, with S6's two files reverted to
`HEAD` and `npm run build` re-run:

| Case | Failure |
| --- | --- |
| `reads one turn number in the header` | `[data-field="turn"]` → Expected 1, Received 0 |
| `names the run state in the DOM, and only the one that applies` | same shape, `[data-run-state]` |
| `keeps every section landmark name after the Section adoption` | Expected 1, Received 0 |
| `renders the summary strip with the header values, the affordance and no primary` | `toBeVisible` fails |
| `keeps one primary where the advisor recommends, and none where it does not` | `toBeVisible` fails |
| `names the fan ladder threshold beside the gap` | `toBeVisible` fails |

**Proof it is not S6's:** the five independently re-run cases each fail on the clean build, and
`reads one turn number in the header` was re-run alone against `git checkout --` versions of both touched files
before S6's patch was restored. The failing selectors (`[data-field="turn"]`, `[data-run-state]`) are in the header
and run-state regions, which S6 does not touch.

**S6's own case passes** on the same build: `names the stat denominator by the figure it prints, floor or cap` →
`1 passed (17.9s)`, and fails again when the patch is reverted.

### Red 2 — `StatDenominatorConsistencyTest`, one case

`tests/Feature/StatDenominatorConsistencyTest.php:103` asserts the panel contains
`No build target was set for this run, so bars are drawn against the scenario ceiling.`

Both `HEAD` copies disagree with it:

```
$ git show HEAD:resources/js/components/career/CareerStatePanel.vue | grep -c "run's ceilings"
2
$ git show HEAD:tests/Feature/StatDenominatorConsistencyTest.php | grep -c "scenario ceiling\.'"
1
```

The panel has said `bars are drawn against this run's ceilings:` since the R6-04 caption change; the test was not
updated with it. **Pre-existing, and my S6 edit did not introduce it** — it was already the first failing assertion
before the new expectations were added.

### Why this is a stop

The dispatch's §5 is explicit: *"A gate reveals a pre-existing red the slice did not cause — classify and record, do
not fix."* Both reds are test-versus-source staleness from earlier rounds. Fixing them here would (a) exceed S6's
scope and (b) silently re-baseline assertions this round did not classify.

**S6's implementation is complete and proven**: the `denominatorLabel` computed, the widened prop, the RED→GREEN→
non-vacuity chain, typecheck, Pint, PHPStan, lore delta 0. Only its landing is held.

### What is owed

Two one-line test corrections — `career-cockpit.spec.ts`'s six cases need the selectors re-pointed at whatever the
current header renders, and `StatDenominatorConsistencyTest`'s caption assertion needs the sentence the panel actually
ships. **Neither is S6's work.** A ruling that they are housekeeping opens a small repair slice; until then S6's patch
stays uncommitted in the working tree and S6 is held.

**Nothing is committed from S6.** `git status --short` shows the two Vue files and the spec modified.

---

## §s0 — S0 housekeeping: three pre-existing test reds cleared

### 1. Slice, rows, register reference
**S0.** No `R7-NN` row — a housekeeping slice that unblocks S1, S6, S13 and S14. No register §4 status moves.

### 2. Pre-flight
```
$ git status --short -- tests/browser/career-training-detail.spec.ts tests/browser/career-cockpit.spec.ts tests/Feature/StatDenominatorConsistencyTest.php
(empty — CLEAN)
```

### 3. RED — the reds already existed

**No new assertion was written. The three reds were pre-existing and measured at `c6193993`.**

**(a) `career-training-detail.spec.ts`** — every case failed inside the shared helper:

```
Error: locator.fill: Test timeout of 180000ms exceeded.
  waiting for locator('input[name="speed"]')
  338 × waiting for element to be visible, enabled and editable — element is not visible
```

Cause: `TrainingDetail.vue:740` ships `<details ... open>`; the helper clicked the summary, **closing** it.
Live probe: `{"before": true, "after": false, "speedVisible": true}`.

**(b) `career-cockpit.spec.ts`** — six cases, four distinct stale targets:

| Case | Stale target | What the tree renders instead |
| --- | --- | --- |
| reads one turn number | `[data-field="turn"]` | the lede paragraph carries `Turn N of 72` |
| names the run state | `[data-run-state]` | no such attribute at all |
| keeps every section landmark | `[aria-labelledby="career-closed-heading"]` | `#cockpit-summary-heading`, "This run is complete." |
| renders the summary strip | `role="region"` "Run summary" | `StatSummary` is not imported (`Cockpit.vue:41-47`) |
| keeps one primary | same region | same |
| names the fan ladder | region "Stats and state" | an `<h2>` inside a `<section>`, and the ladder was removed by HFW override |

**(c) `StatDenominatorConsistencyTest:103`** — asserted
`No build target was set for this run, so bars are drawn against the scenario ceiling.`; the panel has shipped
`… against this run's ceilings:` since round 6's S2.3.

### 4. GREEN
- **training spec** — the helper opens the disclosure only when closed; every case that clicked it inline does the same. The `closed by default` case became `open by default`, citing walk 5's E2-01 and round 5's S7.
- **cockpit spec** — six cases re-pointed at measured DOM; two whose whole subject was retired (`StatSummary`, the fan-ladder suffix) were **rewritten as retirement assertions** rather than re-pointed, so a future re-add fails loudly.
- **caption test** — asserts the sentence the panel ships.

```
$ npx playwright test career-training-detail.spec.ts   → 8 passed
$ npx playwright test career-cockpit.spec.ts           → 30 passed
$ vendor/bin/pest StatDenominatorConsistencyTest.php CareerCockpitTest.php → 51 passed (579 assertions)
```

### 5. Non-vacuity
All three files reverted together, then each red re-run:

```
$ git checkout -- <the three test files>
$ vendor/bin/pest StatDenominatorConsistencyTest.php   → 1 failed
$ npx playwright test -g "closed by default"           → 1 failed
$ npx playwright test -g "reads one turn number"       → 1 failed
$ npx playwright test -g "renders the summary strip"   → 1 failed
$ git apply <the three patches, re-applied in sequence>
$ git diff --stat → 104 insertions, 57 deletions across the three files
```

The source was correct throughout; only the tests moved.

### 6. Regression
```
$ vendor/bin/pest StatDenominatorConsistencyTest.php CareerCockpitTest.php   → 51 passed (579 assertions)
$ npx playwright test career-training-detail.spec.ts                        → 8 passed
$ npx playwright test career-cockpit.spec.ts                                → 30 passed
```

### 7. Gates
| Gate | Result |
| --- | --- |
| Focused tests | three files, all green |
| Typecheck | `npm run typecheck` clean |
| Build | `npm run build` ✓ |
| Pint | `{"tool":"pint","result":"passed"}` |
| PHPStan (full) | `[OK] No errors` |
| Lore docs / code | **556 / 75 exempt**, delta 0 |
| Fence | **0 invalid** |

### 8. Lore delta
`Lore docs: 556 (was 556); code: 556 (was 556)`. **Delta 0.**

### 9. Commit
`58bb98a7` — `test(browser): clear three pre-existing test reds blocking S1, S6, S13, S14 (S0)`

### 10. What S0 did not close
- **Nothing about the product.** All three reds were test staleness; no application file changed.
- **`R7-73`** (`Known limitation`) — the fan-ladder absence S0's rewrite now asserts deliberately is still the
  register's row, unchanged.
- **S6's patch** was held through S0 and re-verified after it (see `#s6`).

---

## §s6 — S6: the stat denominator is named by the figure it prints

### 1. Slice, rows, register reference
**S6.** Rows `R7-19` and `R7-66`, both `Confirmed defect`, register §4.1 and §4.3.

### 2. Pre-flight
```
$ git status --short -- app/Http/Controllers/Career/CockpitController.php resources/js/pages/Career/Cockpit.vue resources/js/components/career/CareerStatePanel.vue tests/Feature/CareerCockpitTest.php tests/browser/career-cockpit.spec.ts
(empty — CLEAN)

$ vendor/bin/pest tests/Feature/ScenarioMetadataTest.php → 3 passed (52 assertions)
```

### 3. RED
New case `names the stat denominator by the figure it prints, floor or cap (R7-19, R7-66)`:

```
Error: expect(locator).toBeVisible() failed
  locator('[data-region="stats"]').locator('text=floor').first() — element(s) not found
```

Measured before the fix: all five stat rows print `cap`, and `db read` on run #3 gives
`{"caps":[null,null,null,null],"scenario":"unity_cup"}` — four null columns under a `cap` label.

### 4. GREEN
- `CareerStatePanel.vue` — `denominatorLabel` widened to `'target' | 'cap' | 'floor' | 'in-force'`; `barCaption` fires for `cap` and `in-force`; `denominator()` reads `stat.cap` for all three cap-ish labels.
- `Cockpit.vue` — the literal `denominator-label="cap"` became `:denominator-label="denominatorLabel"`, a computed returning `cap` where any `caps.entered` is non-null and `floor` otherwise.
- `StatDenominatorConsistencyTest` — the case pinning the literal moved to pin the binding and the three words.

**The RED's first selector was wrong** (`[data-region="stats"]` does not exist). Measured the real DOM and scoped
to `li.srow`; the row count and the `floor` word both then matched.

### 5. Non-vacuity
```
$ git diff <the two Vue files> > S6.patch    # 76 lines
$ git checkout -- <the two Vue files> ; npm run build
$ npx playwright test -g "stat denominator"
  Error: expect(locator).toBeVisible() failed — element(s) not found
$ git apply S6.patch ; git diff | diff - S6.patch → DIFF MATCHES
$ npx playwright test -g "stat denominator" → 1 passed
```

### 6. Regression
```
$ npx playwright test career-cockpit.spec.ts                          → 30 passed
$ vendor/bin/pest CareerCockpitTest StatDenominatorConsistencyTest CockpitRunIdentityTest → 58 passed (665 assertions)
```

### 7. Gates
| Gate | Result |
| --- | --- |
| Focused browser | `-g "stat denominator"` → **1 passed** |
| Full cockpit spec | **30 passed** |
| Feature regression | **58 passed (665 assertions)** |
| Typecheck | clean |
| Build | ✓ |
| Pint | passed |
| PHPStan | `[OK] No errors` (full run) |
| Lore | 556 / 75, delta 0 |
| Fence | 0 invalid |

### 8. Lore delta
`Lore docs: 556 (was 556); code: 556 (was 556)`. **Delta 0.**

### 9. Commit
`c6ac05e7` — `fix(cockpit): name the stat denominator by the figure it prints, floor or cap (R7-19, R7-66)`

```
resources/js/pages/Career/Cockpit.vue
resources/js/components/career/CareerStatePanel.vue
```

### 10. What this slice did not close
- **`R7-66`'s storage half.** No surface records an in-force ceiling, so `in-force` is reachable only for a run whose
  `cap_*` columns carry values. The label is honest; a recording surface is not this slice.
- **`R7-73` / `R7-23`** — the fan-ladder and class-badge displays stay removed under R6-43's override.
