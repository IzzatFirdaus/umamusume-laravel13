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
