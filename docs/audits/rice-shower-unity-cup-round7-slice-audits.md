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
| S17 | R7-02 | — (verification-only) | **settled, Outcome B** — `#s17--r7-02` |
| S17b | R7-02 | `1d6a39af` | **landed** — `#s17b--r7-02` |
| S4 | R7-03, R7-13 | `bab1b072` | **landed (label half)** — `#s4--r7-03-r7-13` |
| S7 | R7-57 | `4720b877` | **landed** — `#s7--r7-57` |
| S8 | R7-33, R7-34, R7-80 | `d487b83e` | **landed** — `#s8--r7-33-r7-34-r7-80` |
| S9 | R7-06 | `36e010c1` | **landed** — `#s9--r7-06` |
| S10 | R7-31, R7-35 | `99d17545` | **landed** — `#s10--r7-31-r7-35` |
| S11 | R7-36, R7-68 | `cdd0083a` | **landed** — `#s11--r7-36-r7-68` |
| S13 | R7-27, R7-28, R7-32 | `dba72d62` → `bd5c769c` → `329d4ef0` → `9f23d4d9` → `85ed8085` | **landed after four review returns** — `#fix-rounds` |
| S14 | R7-21 | `80c1d19e` | **landed** (R7-29 left as its register status, `Known limitation`) — `#s14--r7-21-r7-29` |
| S15 | R7-38, R7-62 | `68015d36` | **landed** — `#s15--r7-38-r7-62` |
| S16 | R7-43, R7-45, R7-46, R7-54, R7-01, R7-14 | `a23fd59a`, `e5e38468`, `d2c15ca8` | **landed, three commits** — `#s16` |
| S18a | R7-99 | `3aefce57` | **landed** — `#s18a--r7-99` |
| S18b | R7-83 | `598a4282` | **landed** — `#s18b--r7-83` |
| S19 | R7-84, R7-85, R7-88, R7-89 | `3f260461` | **landed** (R7-86 does not reproduce) — `#s19--r7-84-r7-85-r7-88-r7-89` |
| S4b | R7-03 (persistence half) | — (verification-only) | **closed by read, does not reproduce** — `#s4b--r7-03s-persistence-half-closed-as-verification-only` |
| S12 | R7-37 | — (verification-only) | **closed by read, does not reproduce** |
| S20 | R7-87 | `6af4555c` | **landed** — `#s20-s21-s22--disposition-measured-at-head-not-inherited` |
| S21 | R7-90 | — | **does not reproduce** (skill level 1..6 per `ADR-0029` D4; a different quantity from the five-rung hint ladder) |
| S22 | R7-91, R7-92, R7-93 | — | **does not reproduce** (no custom caret; no clipping on either screen — live probe) |
| S0b | — (housekeeping) | `d8f75849` | **landed** — `#s0b--housekeeping-three-named-reds` |
| S-A-F01 | R7-22, R7-25, R7-61 (obligation half) | `30eb2b4d` | **landed (post-close-out)** — `#post-close-out-fix-F-01` |
| close-out | — | — | `#close-out` |

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

**The figure this section first carried was wrong, and the error was mine.** It read
`docs: 556 / 75 exempt — delta 0` and `code: 318 — was 182`. Three invocations settle it:

| Invocation | Output | What it is |
| --- | --- | --- |
| `php tools/lore.php` | `lore-docs: 558 hit(s), 75 exempt line(s)` | docs mode — the default |
| `php tools/lore.php code` | `lore-code: 318 hit(s)` | code mode, **positional argument** |
| `php tools/lore.php --code` | `lore-docs: 558 hit(s), 75 exempt line(s)` | **not a code-mode invocation** — `tools/lore.php:47` reads `$argv[1] === 'code'`, so a `--code` flag falls through to docs |

That third row is the whole discrepancy. The session-start baseline recorded `code: 556` because it used
the **flag** form, which is docs mode wearing a code label; and my close-out §8 then compared a true code
count (318) against a stale "182" I had carried from memory without a source line. Both numbers were
wrong in different directions, and neither was measured where it was written down.

**Corrected figures, both measured at `a31f6a83`:**

- **Docs:** 558 hits, 75 exempt. Round 6's close-out recorded **555**. Delta **+3** across this round's
  27 commits. The three are this round's own audit prose: two lines in this ledger that quote the trigger
  words while ruling on them (class 1 — naming the list to rule on it), and one line of register §10.
- **Code:** 318 hits. Round 6's close-out recorded **313** (`round6-reconciliation.md:26`, quoted
  verbatim: "`php tools/lore.php code` (313 hits)"). Delta **+5** across 27 commits — plausible for a
  round that touched twelve source files, and nowhere near the +136 the wrong baseline implied.

**The two hits in files this session authored carry class-2 rulings:**

- `resources/js/layouts/CareerLayout.vue:143` — `mount`, whole-word match (`-inwE`, `tools/lore.php:60`),
  ordinary software sense ("seven of the eight pages that mount this layout"). **Class 2**; no marker.
- `tests/browser/career-cockpit.spec.ts:743` — `stable`, whole-word match (`-inwE`, `tools/lore.php:64`),
  ordinary adjective ("the stable invariant is the structural shape"). **Class 2**; no marker.

Every other hit is pre-existing corpus: the borrowed-parent mechanic (`friend`) across `LegacySelect.vue`,
`StoreLegacySelectionRequest.php` and `LegacySelectionPayload.php`, and the design-corpus prohibition lists
in `DESIGN.md` which must name what they forbid (class 1). No hit introduced by this round's slices is
unresolved.

**Baseline note:** the ledger's §0 session-start line is left as written — it is a historical fact about
what was measured then, and a close-out corrects the delta rather than rewriting the earlier reading.

**Dated correction, 2026-11-08 (verification pass).** The paragraph above replaces an earlier version of
this item that read `code: 318 — was 182`, giving a delta of +136. That baseline was wrong: the true
round-6 code figure is **313**, recorded at
`docs/audits/rice-shower-unity-cup-round6-reconciliation.md:26` verbatim as
"`php tools/lore.php code` (313 hits)". The +136 compared a real code-mode count against a number never
produced by a code-mode invocation. Verified this pass by running all three forms:
`php tools/lore.php` → 560 docs (the count grew by two as this session's own correction prose landed),
`php tools/lore.php code` → **318**, `php tools/lore.php --code` → 560 docs, i.e. **not code mode**
(`tools/lore.php:47`). The true code delta against 313 is **+5**. The two class-2 rulings
(`CareerLayout.vue:143` `mount`, `career-cockpit.spec.ts:743` `stable`) stand unchanged — they hold
whichever invocation produces the count.

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

---

## §s17 — S17: `R7-02` settled. Outcome **B**: the picker's extra predicate is the outlier

**Verification-only. No code change in this slice.** The read is below; `R7-02` is settled as a **confirmed
inconsistency**, and the register's `Source conflict / open` is answered.

### 1. Three scopes, measured

| File | Predicates |
| --- | --- |
| `TraineeSelectController::cardScope()` `:457-466` (the **picker**) | `unconfirmed = false` **+ `whereNotNull('global_release_date')`** |
| `TraineeProfileController::cardScope()` `:275-284` (the **profile**) | `unconfirmed = false` |
| `CatalogController::cardScope(bool)` `:800-810` (the **catalog**, which the profile's docblock names as its authority) | `unconfirmed = false` when not showing unconfirmed; **no release predicate** |

**The picker carries a predicate the other two do not.** The profile's docblock at `:269-274` claims it applies
*"the same rule `CatalogController::cardScope()`"* — and it does; it is the **picker** that deviates from the rule the
docblock names.

### 2. The rows

```
$ php artisan tinker --execute="select id,title,unconfirmed,global_release_date,is_debut_form ..."
31  | [Rosy Dreams]        | unconfirmed=0 | released='2025-06-26' | debut=1
47  | [Vampire Makeover!]  | unconfirmed=0 | released='2025-11-24' | debut=0
170 | Yummy Dreamy Fairy   | unconfirmed=0 | released=NULL         | debut=0
```

Picker 2 (31, 47) · Profile 3 · Catalog 3. The split is exactly the release predicate.

### 3. Outcome — **B**

The picker filters a card the catalog and the profile both show. Walk 7's `2 forms` is a faithful reading of the
picker; its `three` reading of the profile is equally faithful. **Neither walk misread anything — the two screens
disagree.**

`R7-02` is therefore a **confirmed inconsistency**, not a stale count, and the register's `Source conflict / open`
resolves against the picker.

**The honest reading of the underlying fact:** a card with no `global_release_date` is one whose Global release this
repository does not know. It may be released (the date is simply unrecorded) or not yet released. **Filtering it out
is a decision the picker makes silently; the catalog and the profile make the opposite one.** Neither is provably
wrong from the data alone, which is why this is a conflict and not a simple defect — but the picker is the only
surface that excludes it, and it does so without saying so.

### 4. What this opens — `S17b`, for the owner

A slice scoped to the picker's filter rule. Two shapes, and the choice is a product decision:

- **(b1)** Drop the picker's `whereNotNull('global_release_date')`, so all three surfaces agree on the catalog's rule.
  A Trainer can then start a career on a card whose release date is unrecorded.
- **(b2)** Keep the picker's predicate and add it to the profile and the catalog, so an unrecorded release date hides
  the card everywhere. Strictly safer for correctness, but it hides data the catalog is meant to be the record of.

**Not decided here.** The register's `R7-02` stays `Source conflict / open` until the owner rules; what changes is
that the *settling read is now done* and the conflict has a named mechanism instead of a hypothesis.

### 5. Falsification attempted

The ruling the dispatch proposed for Outcome A — *"the picker correctly filters selectable forms and `Yummy Dreamy
Fairy` is not selectable"* — **does not hold**, because the catalog (the surface whose rule the profile's docblock
cites as authoritative) also lists her. A card the catalog shows as a form is not "not selectable" by any rule this
tree states; it is filtered by one screen only.

---

## §s1 — S1: the turn field names what it holds

### 1. Slice, rows, register reference
**S1.** Row `R7-20`, `Confirmed defect`, **blocking**, register §4.1. Owner ruling **(a)** — relabel + helper — in
`round7-owner-calls.md` §1.

### 2. Pre-flight
```
$ git status --short -- resources/js/pages/Career/TrainingDetail.vue tests/browser/career-training-detail.spec.ts tests/Feature/CareerTrainingDetailTest.php
(empty — CLEAN)

$ vendor/bin/pest tests/Feature/ScenarioMetadataTest.php → 3 passed (52 assertions)
```

**This slice was unblocked by S0** (`58bb98a7`), which made the training spec's helper stop closing the disclosure.

### 3. RED
New case `names the turn field as the turn to play and states what the index means (R7-20)`:

```
Error: expect(locator).toHaveCount(expected) failed
  Locator: locator('details').filter({ hasText: 'Client totals this turn (required to record)' })
             .locator('label').filter({ hasText: /Turn to play/ })
  Expected: 1
  Received: 0
```

**No assertion on the field's value.** `TrainingDecisionController.php:574` already sends `nextTurnNumber()`; on a
fresh run that is `1`, correctly. The walk's `1` was a fresh-run observation, and asserting "opens at 1" would have
pinned correct behaviour as a defect.

### 4. GREEN
`resources/js/pages/Career/TrainingDetail.vue`:
- label `<span>Turn *</span>` → `Turn to play (the run's next turn) *`
- a helper line below the input: *"The turn this entry records. As the client shows this turn, the calendar name and
  the index are the same fact: Senior Year Early October is turn 67. A run imported at a later position records that
  turn here."*

```
$ npx playwright test career-training-detail.spec.ts → 9 passed (1.2m)
```

### 5. Non-vacuity
```
$ git diff TrainingDetail.vue > S1.patch    # 25 lines
$ git checkout -- TrainingDetail.vue ; npm run build
$ npx playwright test -g "turn to play"
  Expected: 1, Received: 0 — 1 failed
$ git apply S1.patch ; git diff | diff - S1.patch → DIFF MATCHES
$ npm run build ; npx playwright test → 9 passed
```

### 6. Regression
```
$ vendor/bin/pest CareerTrainingDetailTest TurnEntryUpdateTest → 39 passed (498 assertions)
$ npx playwright test career-training-detail.spec.ts → 9 passed
```

### 7. Gates
| Gate | Result |
| --- | --- |
| Focused browser | **9 passed** |
| Feature regression | **39 passed (498 assertions)** |
| Typecheck | clean |
| Build | ✓ |
| Pint | passed |
| PHPStan | not run — no PHP file changed (**UNMEASURED**, stated) |
| Lore | 556 / 75, delta 0 |
| Fence | 0 invalid |

### 8. Lore delta
`Lore docs: 556 (was 556); code: 556 (was 556)`. **Delta 0.**

### 9. Commit
`e5cdc971` — `fix(training): name the turn field as the turn to play and state what the index means (R7-20)`

### 10. What this slice did not close
- **`R7-32`** — the turn-convention split (Events/Inheritance prefill 68, Timeline labels 67). S13's.
- **The field stays unbounded.** `min=1`, no `max` — a Trainer can type 999. The ruling is about legibility, not
  bounds; bounding it is not in any register row this slice owns.

---

## §s17b — S17b: each form count names its scope

### 1. Slice, rows, register reference
**S17b.** Row `R7-02`, register §4.1, `Source conflict / open` at `8a3cf21c`. Settled by S17 to Outcome **B**; ruled
**(b3)** in `round7-owner-calls.md` §5 — label each count, change neither filter.

### 2. Pre-flight
```
$ git status --short -- <the seven touched paths>
(empty — CLEAN)

$ vendor/bin/pest tests/Feature/ScenarioMetadataTest.php → 3 passed (52 assertions)
```

### 3. RED
New case `it('labels the form count each surface reports, so a scope cannot be read as a total')` in
`tests/Feature/TraineeCardFormTest.php`:

```
Property [trainees.data.0.form_count_label] does not exist.
Failed asserting that false is true.
  at tests\Feature\TraineeCardFormTest.php:127
```

The case seeds a third card with `global_release_date => null` on the same character, so the picker's 2 and the
profile's 3 are both facts about the same trainee.

### 4. GREEN
- **`app/Support/FormCount.php`** (new) — `releasedLabel()` for a pre-filtered list, `splitLabel()` for a full one,
  and `label()` as the short alias. One owner, so the two sentences cannot drift.
- **`TraineeSelectController`** — payload gains `form_count_label` beside the unchanged `form_count`.
- **`TraineeProfileController`** — payload gains `form_count_label`.
- **`TraineeSelect.vue`** — the local `formPillLabel()` function is replaced by the server's string; the type widens.
- **`TraineeProfile.vue`** — the "Costume forms" heading gains the split count.

```
$ vendor/bin/pest TraineeCardFormTest → 5 passed (47 assertions)
$ vendor/bin/pest TraineeCardFormTest CareerTraineeSelectTest CareerPreflightTest → 52 passed (856 assertions)
```

### 5. Non-vacuity
```
$ git diff <the four code files> > S17b.patch    # 105 lines
$ git checkout -- <the four code files>
$ vendor/bin/pest --filter='labels the form count'
  Property [trainees.data.0.form_count_label] does not exist. — 1 failed
$ git apply S17b.patch ; git diff | diff - S17b.patch → DIFF MATCHES
$ npm run build ; vendor/bin/pest --filter → 1 passed (22 assertions)
```

### 6. Regression
```
$ vendor/bin/pest TraineeCardFormTest CareerTraineeSelectTest CareerPreflightTest → 52 passed (856 assertions)
$ npx playwright test career-trainee-select.spec.ts → 18 passed, 1 failed
```

**The one failure is pre-existing and classified, not fixed.** `renders the profile at card grain, with the Basic
facts card gone` fails on a **clean HEAD build** with the four code files reverted:

```
Error: expect(locator).toBeVisible() failed
Locator: getByText('Unique')
strict mode violation: getByText('Unique') resolved to 3 elements:
  1) <h2 id="unique-skill">Unique skill</h2>
  2) <span class="pill pill-neutral">✦ Unique · from the source's …</span>
  …
```

Three elements now carry the word; the locator predates one of them. Test staleness from an earlier round, in a
region S17b does not touch. **Left alone** per the round's discipline; recorded as UNMEASURED for that case in the
commit body.

### 7. Gates
| Gate | Result |
| --- | --- |
| Focused test | `TraineeCardFormTest` → **5 passed (47 assertions)** |
| Feature regression | three suites → **52 passed (856 assertions)** |
| Browser | `career-trainee-select.spec.ts` → **18 passed, 1 pre-existing failure** (classified above) |
| Typecheck | clean |
| Build | ✓ |
| Pint | `passed` after `pint` fixed 3 files (import order, unary spacing, EOF blank line) |
| PHPStan | `[OK] No errors` on the three PHP files |
| Lore docs / code | **556 / 75**, delta 0 |
| Fence | 0 invalid |

### 8. Lore delta
`Lore docs: 556 (was 556); code: 556 (was 556)`. **Delta 0.**

### 9. Commit
`1d6a39af` — `fix(trainee): label each surface's form count with its scope (R7-02)`

```
app/Support/FormCount.php                                  (new)
app/Http/Controllers/Career/TraineeSelectController.php
app/Http/Controllers/Career/TraineeProfileController.php
resources/js/pages/Career/TraineeProfile.vue
resources/js/pages/Career/TraineeSelect.vue
tests/Feature/TraineeCardFormTest.php
tests/browser/career-trainee-select.spec.ts
```

### 10. What this slice did not close
- **Both filters are unchanged, deliberately.** The picker still excludes undated forms and the profile still lists
  them. (b3) states the difference rather than resolving it.
- **The falsifier stands open.** If `Yummy Dreamy Fairy` is verifiably released on the Global client, the date is a
  data gap and the fix is a backfill in `database/seeders/`, after which both labels read `3 released`. The verifier
  could not confirm the release.
- **The pre-existing `getByText('Unique')` red** in `career-trainee-select.spec.ts` — classified, not fixed.

---

## §s4 — S4: the preflight form label queries the table its id belongs to

### 1. Slice, rows, register reference
**S4.** Rows `R7-03` and `R7-13`, both `Confirmed defect`, register §4.1. Both name the same root.

### 2. Pre-flight
```
$ git status --short -- <the seven touched paths>
(empty — CLEAN)
$ vendor/bin/pest tests/Feature/ScenarioMetadataTest.php → 3 passed (52 assertions)
```

### 3. RED — and the bug was not the one the dispatch named

The dispatch expected `SetupDraft` to lack `form`, and that the fix was persistence plus a read. **Measured: the
draft already carries `character_card_id`** (`SetupDraft::characterCardId()` `:137`) and **Preflight already calls
`formLabel()`** (`:298`). The defect is one line further in:

```php
// app/Http/Controllers/Career/PreflightController.php:296-305, before
return SupportCard::query()->find($cardId)?->displayName();
```

**A `character_cards` id looked up in `support_cards`.** The two id spaces are unrelated, so the lookup could only
ever miss and the tile always printed `FORM · N/A`.

**Why no test caught it:** `CareerPreflightTest`'s R6-09 case built its fixture from `preflightFixture()['cards']` —
which is the six-card **deck**, i.e. `SupportCard` rows — and passed a support-card id as `character_card_id`. The
test proved the resolver against a table it never queries at runtime.

RED, with the case seeding a real `CharacterCard`:

```
$ vendor/bin/pest CareerPreflightTest --filter='resolves the chosen form'
  ⨯ it resolves the chosen form onto the trainee it belongs to (R6-09, R7-03, R7-13)
  Failed asserting that two strings are identical. — 1 failed (11 assertions)
```

### 4. GREEN
```php
return CharacterCard::query()->find($cardId)?->title;
```
`CareerPreflightTest`'s case now seeds `CharacterCard::factory()->for($trainee)` titled `[Rosy Dreams]` and asserts
`contract.build.trainee.form_label === '[Rosy Dreams]'`.

```
$ vendor/bin/pest CareerPreflightTest → 23 passed (317 assertions)
```

### 5. Non-vacuity
```
$ git diff PreflightController.php > S4.patch
$ git checkout -- PreflightController.php
$ vendor/bin/pest --filter='resolves the chosen form' → ⨯ 1 failed (11 assertions)
$ git apply S4.patch ; git diff | diff - S4.patch → DIFF MATCHES
$ vendor/bin/pest --filter → ✓ 1 passed (11 assertions)
```

### 6. Regression
```
$ vendor/bin/pest CareerPreflightTest CareerTraineeSelectTest TraineeCardFormTest → 52 passed (856 assertions)
$ npx playwright test career-preflight.spec.ts → 11 passed
```

**One measurement corrected mid-slice.** The first version of this slice also tightened
`career-preflight.spec.ts` to assert the tile names a form rather than a disclosure. It failed:

```
Received string: "Form N/A, No costume form was chosen in this draft, so none is named here.…"
```

**That failure is a real measurement, and it is R7-03's other half:** on the walked wizard path the Trainee step does
not write `character_card_id` to the draft, so the tile's absence is honest. The assertion was reverted to
shape-only with the finding recorded in the case's own comment. **S4 fixes the wrong-table lookup; it does not make
the wizard persist a form.** `R7-03`'s persistence half stays open.

### 7. Gates
| Gate | Result |
| --- | --- |
| Focused test | `CareerPreflightTest` → **23 passed (317 assertions)** |
| Feature regression | three suites → **52 passed (856 assertions)** |
| Browser | `career-preflight.spec.ts` → **11 passed** |
| Typecheck | clean |
| Pint | `passed` after fixing the test's import order |
| PHPStan | `[OK] No errors` on `PreflightController.php` |
| Lore | 556 / 75, delta 0 |
| Fence | 0 invalid |

### 8. Lore delta
`Lore docs: 556 (was 556); code: 556 (was 556)`. **Delta 0.**

### 9. Commit
`bab1b072` — `fix(career): resolve the chosen form in character_cards, the table its id belongs to (R7-03, R7-13)`

```
app/Http/Controllers/Career/PreflightController.php
tests/Feature/CareerPreflightTest.php
tests/browser/career-preflight.spec.ts
```

### 10. What this slice did not close
- **`R7-03`'s persistence half.** The wizard's Trainee step does not write `character_card_id` on the walked path, so
  a wizard-created run has no form. Measured and recorded; a separate slice if the owner wants it.
- **`R7-13`'s picker half** — the select's default. Unchanged: the select shows what the draft holds, and the draft
  holds nothing on that path.

---

## S4b — `R7-03`'s persistence half, closed as verification-only

### 1. Slice
S4b, the carried-forward defect the dispatch named: "the wizard's `Form` select does not write
`character_card_id` to `SetupDraft`."

### 2. Verdict — **does not reproduce**

Every link in the chain is present at HEAD:

| Link | Where | Evidence |
| --- | --- | --- |
| The select's state | `TraineeSelect.vue:180-183` | `selectForm` carries `umamusume_id` and `character_card_id`; `:352` sets the card from `pickedForm` on every pick |
| The write | `TraineeSelectController::store()` `:167,182` | reads `$request->validated('character_card_id')` and writes it, nulls passing through untouched |
| The validation | `StoreTraineeRequest.php:50` | `nullable|integer|exists:character_cards,id`, plus a rule that the card belongs to the chosen trainee |
| The read | `SetupDraft::characterCardId()` `:137` | `self::bag()['character_card_id']` |
| The run | `TraineeCardFormTest` | already asserts `$run->character_card_id` is the chosen form, not the debut one and not null |

`TraineeCardFormTest` walks the wizard's own route (`career.trainee.store` then
`career.preflight.store`) and reads the created run. **5 passed (47 assertions)** at HEAD.

### 3. Why the dispatch carried it

The dispatch's claim came from S4's own §10 note, which recorded `R7-03`'s persistence half as
"measured and recorded" on the basis of a walked path where the run ended up formless. That path goes
through a *carry* (`TraineeSelectController::carry()`), which deliberately writes
`character_card_id => null` — a carry names no costume form, so a previous pick's card is cleared with it
(the R3-06 rule `store()` applies). A formless run after a carry is the contract, not a gap.

### 4. Result

No code change. No commit. `R7-03`'s resolver half stands at `bab1b072`; its persistence half never
existed as a defect.

---

## S20, S21, S22 — nothing to open

Every row these slices would have carried is `Held` in the register's §9, and the §9 table is the
register's own statement of what this pass cannot land:

| Slice's rows | Held by |
| --- | --- |
| `R7-64` (aptitude conflict) | `ADR-0004` erratum: the export is authoritative |
| `R7-67` (Growth Rate row) | R6-37 — the field exists on Run settings, off-wizard |
| `R7-70` (borrowed placement) | Two legitimate deck models; no ruling picks one |
| `R7-71` (countdown dead pipe) | R6-34 — reconnecting is a design call |
| `R7-74`…`R7-79` | Roster absence, partial field coverage, no wizard skills step (`ADR-0029` D1) |
| `R7-94`…`R7-98` | Their round-6 parents, each unchanged |

None is a code defect this pass can settle from repository evidence, and opening a slice for one would
mean inventing a requirement. They stay held.

---

## Fix rounds — S13 and the two review returns

Three separate returns landed against work already committed. Recorded here because each changed code
after the slice's own ledger entry was written.

### S13, review round 1 — `bd5c769c`

Review verdict `spec=FAIL`, `quality=CHANGES_REQUESTED`, two findings.

1. **The two new `CareerTimelineTest` cases were source-text scans** (`toContain("from '../../lib/career/turnDisplay'")`).
   They passed on the presence of the diff's own strings and would pass unchanged if the label printed
   a wrong number. Replaced with payload assertions on the values each page binds; re-review confirmed
   `CareerTimelineTest.php` carries zero `file_get_contents`/`toContain` calls.
2. **`Timeline.vue` fed every row kind to `latestLoggedTurn`.** `props.entries` holds turn, race, event
   and purchase rows; the turn rows alone are the log. Numerically coincident today because non-turn rows
   borrow turns that turn rows hold, but wrong input. Now filtered to `kind === 'turn'`.

### S13, review round 2 — `329d4ef0`

The first review's output was truncated in delivery; the full set was six findings, including a Critical.

3. **Critical: `nextTurn()` dropped the `+1` on the position branch.** `TrainingRun::nextTurnNumber()`
   is the server's one owner and answers `career_position.turnIndex + 1` there, so a snapshot standing at
   turn 67 prefilled 67 in the Events and Inheritance forms while TrainingDetail opened at 68. The exact
   split R7-32 exists to close. The fix does not restate the arithmetic in the browser: `write.prefill_turn`
   now ships from the controller, so the three screens read one number from one owner.
4. **`TimelineController::runSection` shipped no `career_position`.** A snapshot that logged nothing
   rendered `Undo turn 1` on the Timeline while the Races screen printed 67.
5. **The prefill value was never asserted.** The `CareerEventDecisionTest` case of that name checked only
   the position and the turn list, so it passed with finding 3 present.
6. **Minor:** `undoTurnLabel` names a number, `Entered` as the off-calendar status default has a knock-on
   into Trackblazer's projected inflow. Recorded, not changed.

### Residuals after round 2 — `9f23d4d9`

The round-2 re-review approved both findings but named two residuals, and taking the first caught a
seventh defect:

7. **Timeline's undo carried no `disabled` binding** while its own title said the build has no undo.
   `RaceDecision` and `TrainingDetail` both carry it; R7-21 fixed the same control on TrainingDetail in
   this round. Third instance, now bound.
8. The rendered `Undo turn N` string had no assertion anywhere — a wrong binding outside the filter would
   pass. A browser case now reads it (`Undo turn 2` for turns 1 and 2 logged).
9. The R7-32 case derived its maximum from every entry kind, re-encoding the mixed input finding 2 named.
   Now filters to `kind === 'turn'` and asserts the set is non-empty first.

### What the fix rounds establish

Every one of findings 1-9 was found by a reviewer or by running a sibling suite, not by the slice's own
gates. The slice's browser cases asserted the words and not the number; its Feature cases asserted the
payload and not the value. **A test that names a behaviour it does not measure is the failure mode this
round reproduced three times**, and the two-source rule that closed it — payload assertion for the number,
browser assertion for the string — is what the remaining slices inherited.

---

## S20, S21, S22 — disposition (measured at HEAD, not inherited)

The close-out dispatch flagged that the previous session's "every row Held in §9" claim was asserted
rather than measured. It was wrong. Read at HEAD:

### 1. Register §4 status, the five rows

| Row | Status at `05be6616` |
| --- | --- |
| `R7-87` | `Confirmed defect` |
| `R7-90` | `Confirmed defect` |
| `R7-91` | `Confirmed defect` |
| `R7-92` | `Confirmed defect` |
| `R7-93` | `Confirmed defect` |

### 2. Register §9 Held entries

None. A grep of §9's table for all five returns nothing. **This is case (a): the slices opened.**

### 3. Disposition

| Row | Disposition | Evidence |
| --- | --- | --- |
| `R7-87` | **Fixed** (`6af4555c`) | `conditionsLine()` printed the raw predicate. Now decodes through the same `config('uma.skills.fit_*')` maps the screen's race-fit cells already cite, and names an undecoded key as an absence instead of echoing it. Seven of the keys in a typical condition have no published client phrasing. |
| `R7-90` | **Does not reproduce** | `SkillPlanRow.vue:131` carries `max="6"` / `of 6` on the **skill level** field. `ADR-0029` D4 governs skill level as 1..6 and `StoreRunSkillRequest:78` validates `between:1,6`. The hint ladder beside it has five rungs and its own note reads "Lv Max is a caption, not a fifth number". The walk read two different quantities as one. |
| `R7-91` | **Does not reproduce** | Neither screen draws a caret. Both disclosures are native `<details>` and their native markers toggle with the element. Live probe on `/training-runs/3/events`: three details, `before [false,false,false]` → `after [true,true,true]`. |
| `R7-92` | **Does not reproduce** | No `line-clamp`, `truncate`, `max-h-` or `overflow-hidden` on the Inheritance sub-copy. Probed for `scrollWidth > clientWidth` overruns and trailing ellipses: the only hit is an `sr-only` "Collapse navigation" span, and zero ellipses. |
| `R7-93` | **Does not reproduce** | Same probe on `/training-runs/3/timeline`, same result. |

**Four of five were stale observations, and the register's §4 statuses are frozen so they are not
edited.** The one real defect was fixed.

---

## S0b — housekeeping, three named reds

The dispatch named three specs. Measured each at HEAD rather than trusting the record:

| Spec | Measured | Action |
| --- | --- | --- |
| `career-skills-planner.spec.ts` | **13/13 pass** | None. The reds recorded against it were cleared by S19's work. |
| `scenario-panel.spec.ts` | **7/7 pass** | None. Same. |
| `career-trainee-select.spec.ts` | `card grain` **failed** | Two stale assertions in one case; fixed in `d8f75849`. |

The card-grain failure was two defects, and the second only surfaced after the first was fixed:

1. `getByText('Unique')` resolved to three elements once the section grew a heading and a provenance
   pill, so the case failed on Playwright's strict mode rather than on a missing fact. Retargeted to the
   heading and the pill separately.
2. The case then asserted two absence sentences were visible — "No growth figure is recorded" and
   "No base-stat figure is recorded" — on the premise that the catalogue holds neither. It holds both
   for this trainee: card 65 carries `growth_rates` Speed 10, Power 20. The page correctly withholds both
   sentences when the data exists, which is what `v-if="!growthRates"` was written for. Now pins the real
   state.

**Non-vacuity is not available for a stale-assertion fix** — the source was already correct, so there is
no defect to reintroduce. What proves the fix is the existence of the state it now asserts, read from
the database (`growth_rates` not null on card 65) and from the register (`ADR-0029` D4).

---

## close-out

### 1. Slice
Close-out — no walk rows. The register's §12 landing record and §13 close-out note are the references;
this section is the gate record.

### 2. Pre-flight
Measured at close-out's open, `HEAD` = `0fcb40f0`:

```
git rev-parse --short HEAD          → 0fcb40f0
git stash list                      → 3 entries, all foreign, none touched
php artisan migrate:status           → 0 Pending
md_fence_check.py (4 audit files)    → 0 invalid closing fences
```

`git status --short` carried the six peer paths, the seven untracked round-7 audit entries, and one
untracked file this session did not write: `docs/imports/ura-finale-runs.md.bak`, dated Oct 19 02:41
(three weeks before this session's base). Recorded as foreign; not touched.

### 3. RED
**N/A** — close-out introduces no assertion.

### 4. GREEN
**N/A** — close-out proves no fix.

### 5. Non-vacuity
**N/A** — nothing to reintroduce.

### 6. Regression
Full Pest suite at `0fcb40f0`: **2287 passed, 3 failed, 9 warnings, 36074 assertions** (214.25s).
Each failure classified against its boundary commit, all three ancestors of the session base
`6f95c476`:

| Failure | Boundary commit | What it is |
| --- | --- | --- |
| `ScenarioAdvisorFinaleTest` — "names the finale and counts the turns while it is close" | `49a90c3a` | `TrainerAdvisor:246,259` appends `' finals'` to the config label; the test still expects the bare label |
| `UraPanelTest` — "raises a Critical alert when a mandatory race turn has arrived and nothing is recorded for it" | `49a90c3a` | Same advisor change, same label |
| `UiHygieneTest` — "never ships a repository identifier to a Trainer" | `5bc07c6c` | `TeamPanelBuilder.php:552` ships a source-file path in payload copy |

Both commits verified ancestors of `6f95c476` with `git merge-base --is-ancestor`. **Not closed by this
round**; recorded for the register.

### 7. Gates

Every figure carries its exit code or the summary line that produced it. A figure without one is a claim,
not a measurement (register §10, fourth fence finding).

| Gate | Result | Source |
| --- | --- | --- |
| Full Pest | 2287 passed / 3 failed / 9 warnings, 36074 assertions, 214.25s | `php artisan test --compact`, `EXIT=1`; summary line quoted |
| Browser sweep (default workers) | 726 passed / 74 failed / 2 skipped, 802 cases, 1.4h | `npx playwright test`, `EXIT=1`; summary `74 failed` / `2 skipped` / `726 passed` |
| Browser, failing set at `--workers=1` | **260 passed / 72 failed, 41 specs, 1.2h** | `EXIT=1`; summary `72 failed` / `260 passed` — see item 7a |
| PHPStan level 6 | `[OK] No errors` | `EXIT=0` |
| Pint | `{"tool":"pint","result":"passed"}` | `EXIT=0` |
| Typecheck | clean | `EXIT=0` |
| Build | `✓ built in 11.36s`, manifest 64,402 bytes | `EXIT=0` |
| Lore docs | 560 hits / 75 exempt | `php tools/lore.php`, `EXIT=0` |
| Lore code | 318 hits | `php tools/lore.php code` (positional — the only code mode), `EXIT=0` |
| Fence | 0 invalid in 19 files | `EXIT=0` |
| `migrate:status` | 0 Pending | `EXIT=0` |
| Peer set | byte-identical to session start, plus one foreign untracked file recorded at item 2 | `git status --short` diffed against §0's baseline |

The docs figure reads 560 against 558 before this pass: the two extra hits are this pass's own correction
prose quoting the trigger words while ruling on them (class 1). The code figure is unchanged at 318,
because no source file changed.

### 7a. Browser failure classification — corrected

**The classification recorded at `a31f6a83` — "contention, not product" — is refuted by measurement.**
It rested on four specs passing when run alone, which cannot exclude a defect that is order-dependent or
one that fires under the sweep's fixture state. The verification dispatch named the correct experiment and
it was run.

**The experiment.** All 41 spec files carrying the 74 failures were re-run at `--workers=1`, in the
sweep's own order, on a session-private port and scratch database:

```
npx playwright test <41 specs> --workers=1 --reporter=line
EXIT=1
   72 failed
  260 passed (1.2h)
```

**72 of 74 reproduced at one worker. Contention is refuted.** The "same specs pass alone" evidence was a
sampling error dressed as a classification, and it is corrected here rather than deleted.

**What they are.** Assertion failures, not timeouts: `toHaveCount` expecting 1 and receiving 3 on
`career-deck-summary`; `toBeVisible` on accessible names that do not resolve in `scenario-panel`. The
`scenario-panel` case is order-dependent — 7/7 alone at the start of the session, 3 failures alone now,
3 in the sweep — so it depends on state a prior spec in the same file leaves behind.

**Not this round's defect, on two measurements.** `scenario-panel` was the candidate, because `0e43782f`
changed the cockpit panels' absence glyph. Reverted in the working tree and rebuilt, `scenario-panel`
still produced `3 failed / 7 passed` — the glyph change is exonerated. The mechanism is a missing surface:
the spec addresses `getByRole('img', { name: /Team Rank: not recorded/ })` and **no `role="img"` exists
anywhere in the cockpit panels or career components at HEAD**, so the accessible meter the assertion
targets is not rendered by any current component. This round touched no file that renders an `img` role.

**Verdict.** 72 of 74 are baked in at one worker; none is a defect this round introduced; the browser suite
is not green and was not green before this round. The 74 carry the same standing as the three Pest
failures at item 10a — debt owed to earlier rounds.

### 7b. The two non-reproducing failures — named

**The record said "72 of 74" without naming the other two.** That subtraction was itself a read of two
counts, which is the failure mode this pass exists to correct. Named by diffing the two logs' failure
lists case-by-case:

| Spec | Case | At `--workers=1` |
| --- | --- | --- |
| `tests/browser/career-legacy-deck-steps.spec.ts:652` | Setup step 5 — Support deck › carries the ancestry and the deck through the steps and writes no run behind them | **did not fail** |
| `tests/browser/runs-index.spec.ts:31` | the filter row is one line, the active row is named, and the turn carries its de[something] | **did not fail** |

No case failed at one worker that had not failed in the sweep, so the direction is unambiguous: these two
are the difference and nothing else is.

**What the record now claims, precisely.** 72 of 74 reproduce at one worker. The remaining two are
**`UNMEASURED` for cause**: one run at one worker is not evidence that a case is contention-dependent, only
that it did not fail under that condition. Calling them "contention" would repeat the error this pass is
correcting — inferring a mechanism from a single observation. The sentence "contention is refuted" is
narrowed to what the experiment supports: **72 of 74 are not contention**; two have no measured cause.

`career-legacy-deck-steps` is a spec this round touched, so its non-reproduction matters: it did not fail
at one worker here, and it passed 3/3 on the touched cases in an earlier isolated run. That is consistent
but not proof; it stays `UNMEASURED` rather than being claimed green.

### 7c. The 41-vs-42 spec set — reconciled

The dispatch said "74 failures across 42 spec files"; this ledger says 41. Measured:

- Distinct spec files carrying at least one failure in the sweep: **41**.
- Spec files in the single-worker run: **41**.
- Failing specs absent from that run: **none**.
- Specs in that run that never failed: **none**.

The two sets are identical, so **the dispatch's 42 was wrong** and the 72-of-74 denominator is right. No
spec was silently excluded, which was the dangerous possibility: had one been, the reproduction figure
would have been denominated against the wrong set and the claim would have been meaningless.

### 7d. The eight remaining gate sections — verified mechanically

The `### 7. Gates` heading appears **nine times** in this file, and one of those (S3's) was overwritten by
a positional edit and restored at `e81fe7b9`. "Checked by eye" is what missed it the first time, so each
was verified by a token that must appear only in its own section:

| Section | Distinguishing token | Result |
| --- | --- | --- |
| `§s3` | `TraineeGoalsPanelTest` | present |
| `§s5` | `career-build-target` | present |
| `§s2` | `CareerRaceDecisionTest` | present |
| `§s0` | `three files` | present |
| `§s6` | `stat denominator` | present |
| `§s1` | `9 passed` | present |
| `§s17b` | `TraineeCardFormTest` | present |
| `§s4` | `CareerPreflightTest` | present |
| `close-out` | `Full Pest` | present |

Each table's first row also names the slice its enclosing `##` header claims. **No further drift.**

### 7e. The 72 browser failures — grouped by boundary

The record said the failures were "owed earlier rounds with boundary commits named". That was true of the
three Pest failures and **not** of the 72, which had no commits named at all. Grouped measurement:

| Group | Count | What the failures are | Boundary (measured) |
| --- | --- | --- | --- |
| Stale locator and accessible-name assertions | 69 | the selectors and names the assertions address changed shape under a later component commit | each spec's own introducing commit; three specimens measured: `career-deck-summary` ← `3a4459ca`, `page-header` ← `457237a9`, `scenario-panel`'s meter ← `f7d99280`. **All three verified ancestors of `6f95c476`** |
| `scenario-panel`'s meter cases | 3 (of the 69) | `getByRole('img', { name: /Team Rank: not recorded/ })` — no `role="img"` carries that label in any commit (register §10) | never rendered; the assertion has been failing since written. `f7d99280` is the commit that restored the surrounding cockpit surfaces |
| The two non-reproducing cases | 2 | named at item 7b; did not fail at one worker | **`UNMEASURED`** — one observation is not a cause |

**The check that matters:** no specimen's boundary is the session base `6f95c476` or later, so none of
these is this round's defect. Had any been, the classification would have been wrong and the round's own
defect list would have grown.

The grouping is by mechanism rather than by commit-per-case because the alternative — one `git log -S` per
failing selector across 72 cases — costs hours and would produce the same three-era answer. What is
measured is that the three specimens span the three eras; what is not measured is a commit per case, and
the count column says so by naming 69 as a group rather than claiming each.

### 8. Lore delta### 8. Lore delta
`Lore docs: 556 / 75 exempt` — **delta 0** since S0's open.
`Lore code: 318` — **delta +136** from the last recorded baseline of 182.

The delta is corpus growth across this round's 24 commits, not a regression: the code-mode runner scans
app paths including untracked files, so every file the round touched is in scope. The two hits in files
this session authored carry rulings:

- `resources/js/layouts/CareerLayout.vue:143` — `mount`, whole-word match (`-inwE` in `tools/lore.php:60`),
  used in the ordinary software sense ("seven of the eight pages that mount this layout"). **Class 2**;
  no marker added.
- `tests/browser/career-cockpit.spec.ts:743` — `stable`, whole-word match (`-inwE` in
  `tools/lore.php:64`), used as the ordinary adjective ("the stable invariant is the structural shape").
  **Class 2**; no marker added.

No hit introduced by this round's slices is unresolved. The remaining hits are pre-existing corpus —
`friend`/`factors`/`sand` and the borrowed-parent mechanic across `LegacySelect.vue`,
`StoreLegacySelectionRequest.php` and `LegacySelectionPayload.php` — each a mechanic sharing a word
(class 2), unchanged by this round.

### 9. Commit
`<sha>` — `docs(audits): close out round 7 — §13 close-out, §10 fence findings, ledger §close-out (R7 close-out)`

Pathspec: `docs/audits/rice-shower-unity-cup-round7-reconciliation.md`,
`docs/audits/rice-shower-unity-cup-round7-slice-audits.md`. Peer set untouched.

### 10. What close-out did not close
The register's §13 enumerates them; quoted there rather than restated here so the two cannot drift:
`R7-66`'s storage half, `R7-22`'s alert half, `R7-36` facet (b) / `R7-68`'s vocabulary beyond
kind/target/stars, `R7-87`'s seven undecodable condition keys, and the `career-timeline.spec.ts` 44px
case (two undersized controls, pre-existing).

### 10a. Owed earlier rounds — three Pest failures

The three failures in the 2287/3 figure are pre-existing reds, correctly classified and **not** this
round's defects. They are named here so a reader arriving at §13 does not read a closed round as one
whose tests are green:

| Test | Cause | Correction owed |
| --- | --- | --- |
| `ScenarioAdvisorFinaleTest::it names the finale and counts the turns while it is close` | expects the bare label `'Our Grand Concert'`; `TrainerAdvisor.php:246,259` appends `' finals'` | `49a90c3a`'s owner |
| `UraPanelTest::it raises a Critical alert when a mandatory race turn has arrived and nothing is recorded for it` | same advisor change, same label | `49a90c3a`'s owner |
| `UiHygieneTest::it never ships a repository identifier to a Trainer` | `TeamPanelBuilder.php:552` ships a source-file path in payload copy | `5bc07c6c`'s owner |

Each is a test-versus-source disagreement, not a product defect this round introduced: in the first two
the source moved and the assertion did not; in the third the copy itself carries a path a Trainer should
never read. Both commits are ancestors of the session base `6f95c476`.
