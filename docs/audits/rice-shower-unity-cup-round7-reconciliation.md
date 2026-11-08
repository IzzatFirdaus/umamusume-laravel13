# Round 7 reconciliation register — Rice Shower / Unity Cup (`RS-UC-R7`)

**Status:** register. Every row below carries a status re-derived by a read at the named commit. **No row inherits
a prior register's classification.** Where a walk's own register classified a finding, that classification is input
evidence, not output.

**Audit ID:** `RS-UC-R7`. **Date open:** 2026-11-07. **Commit at open:** `8a3cf21c221da2c0bfa5fda887f593c7ca8d9b1d`.
**Commit at close:** `bb03a86a` — HEAD moved once during the pass when this register's predecessor landed. Every read
below was taken before that move and is dated to `8a3cf21c`, which is the tree both walks observed.

---

## §1 Baseline measured tree

### 1.1 Commit, hashes, and the HEAD-at-open / HEAD-at-close note

| Item | Value |
| --- | --- |
| HEAD at open (all reads) | `8a3cf21c221da2c0bfa5fda887f593c7ca8d9b1d` |
| HEAD at close | `bb03a86a` |
| Walk 7 canonical hash | `5b393b8afb6240bfcec8afcf962d4b691063d22d` (`docs/audits/rice-shower-unity-cup-ux-walk-7.md`) |
| Walk 7 snapshot-door hash | `99adf7a1b99cbe08e9a73a729b3fff0406316d91` (`docs/audits/rice-shower-unity-cup-ux-walk-7-snapshot-door.md`) |

**No read in this register was retaken after the HEAD move.** The move added one docs file and touched no
application file, so `8a3cf21c` remains the code state every citation names.

### 1.2 Dirty set, verbatim

```
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

**The plan expected four peer paths. Five are present.** The four it named are all here in the state it assumed
(`D` ×2, `M` ×2). The fifth, `M docs/imports/Umamusume_Progress_Tracker_Runs.md`, appeared during the walk passes.
**No read in this register crosses it**: it is a data-import document, and no row cites it. Two untracked artifacts
(`docs/imports/ura-finale-runs.md.bak`, `screen_data.json`) are peer-owned and were not read.

### 1.3 Gates

| Gate | Command | Result at `8a3cf21c` |
| --- | --- | --- |
| Scenario metadata | `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` | **3 passed (52 assertions)**, 4.31s |
| Migrations | `php artisan migrate:status --no-ansi` | **0 Pending**; last two both `Ran` (`2026_11_04_141604_fix_skill_release_status_diff`, `2026_11_05_084524_add_match_key_ja_to_skills_table`) |
| Markdown fences | `python tools/md_fence_check.py <both walks>` | **0 invalid closing fence(s) in 2 file(s)** |
| Lore (docs) | `php tools/lore.php` | **556 hit(s), 75 exempt line(s)** |
| Lore (code) | `php tools/lore.php --code` | **556 hit(s), 75 exempt line(s)** |
| DevTools cache | `ls storage/inertia-devtools/ \| wc -l` | **386 files** — far under the 25,000 threshold; no move-aside needed |
| Rendered evidence | `chromium.launch()` → `GET /` | **RENDERED_EVIDENCE = TRUE**, `h1 = ['Trainer Desk']` |

### 1.4 Gates not run, and why

`PHPStan`, `Pint`, `npm run typecheck`, the full Pest suite and the browser suite were **not** run. No application
file changed in this pass; the register is a documentation-only read. §8.5's per-slice gate list applies when a
slice opens, and none does. Rendered claims cite a walk screenshot or a walk DOM read, not a measurement this pass
took beyond the `RENDERED_EVIDENCE` probe.

### 1.5 Screenshots, measured

| Directory | Files at `8a3cf21c` | Referenced by the walk |
| --- | --- | --- |
| `docs/audits/RS-UC-2026-12/screens/` | **34 PNGs** | 30 cited by name in walk 7 canonical §7.3 |
| `docs/audits/RS-UC-2026-11-snapshot-door/screens/` | **13 PNGs** | 13 cited, one per screen, S01–S13 |

**No missing file.** Every filename walk 7's §7.3 mentions was checked against its directory and exists. The plan's
"~30 expected" for the canonical door is 30 cited against 34 on disk — a surplus of 4 uncited captures, not a
shortfall.

### 1.6 Verification classes

| Class | Meaning | Citation form |
| --- | --- | --- |
| `code read` | one file read at `8a3cf21c` | `path:line` |
| `code read + test` | file read plus a passing test | `path:line`; `Test::case` |
| `rendered` | the walk's own screenshot or DOM read | `RS-UC-2026-12/screens/<file>.png` or `§7.3 S<nn>` |
| `rendered + code` | both | both |
| `walk-artifact` | the claim rests on a walk improvisation | `§7.4 I-NN` |
| `db read` | a read-only query against the dev database | the query and its output, quoted |

`UNMEASURED` is stated where a rendered read was not obtained, never implied away.

---

## §2 Method

### 2.1 Row-ID convention

- **`R7-01`…`R7-38`** ↔ walk 7 `F-01`…`F-38` — strict 1:1, walk order preserved.
- **`R7-39`…`R7-62`** ↔ walk 7 `E-01`…`E-24` — strict 1:1.
- **`R7-63`…`R7-81`** ↔ walk 7 `U-01`…`U-19` — strict 1:1.
- **`R7-82`…`R7-98`** — extended rows: snapshot-door-only findings plus the dropped cleared-objectives observation.
  Each carries `Source = snapshot-door` or `Source = walk-7 traceability matrix`.

**No collapse, no merge, no renumbering.** Where two walk findings share a root (`F-33`/`F-34`), both rows stay and
the shared root is stated; one slice fixes both.

**No double-count.** A snapshot-door observation that is one of the 81, or a facet of one, gets no extended row; it
is recorded in §5 and, where it adds substance, in the canonical row's facets.

### 2.2 Statuses

Six base statuses, unchanged from rounds 3–6:

`Confirmed fixed` · `Confirmed defect` · `Known limitation` · `Unrepresented source data` ·
`Source conflict / open` · `Does not reproduce`

**Two sanctioned variants**, each with its own rule, because neither collapses into a base status without losing
the distinction the row exists to make (C15):

- **`Ruled out`** — the *observation* reproduces, and the *claim* built on it is refuted by a prior ruling or a
  reference. Distinct from `Does not reproduce`, which refutes the observation itself. Used by `R7-07` (two
  figures are visible; they are two mechanics, so the "contradiction" claim fails) and `R7-18` (the URA labels are
  visible; the rounds are shared by design, so the "wrong scenario" claim fails). The refuting authority is named
  in the row: a `UMAMUSUME_REFERENCE.md` section, or a closed `KI-nn`, or an accepted ADR.
- **`Walk-artifact + Confirmed defect`** — a compound for a row whose display is *honest about an invented value*
  while the surrounding representability defect stands. Used once, by `R7-34`: the `★★★` correctly renders the
  `Rank (stars) = 3` the walk typed (`§7.4 I-01`), so the star half is not a fabrication, but the letter that
  should have been rendered is discarded (`R7-33`). The compound is paired with the base status it qualifies, never
  used alone.

Plus one working label, paired with a status and never used alone: **`Regression from an earlier register's landed
fix`**, which requires a proven predecessor boundary commit.

**No placeholder is a status.** `Open`, `read needed`, `likely`, `candidate`, `split →`, `owner call`, `deferred`
appear in no status cell. A row that cannot be settled by a read is `Source conflict / open` with the settling read
named, `Unrepresented source data` with the scope reason, or `Known limitation` with the ruling cited.

### 2.3 The four rules, carried forward

1. `Confirmed fixed` requires a verified repair or a proven working predecessor plus a passing test. A capability
   that always existed and the walk did not use is `Does not reproduce`, never `fixed`.
2. `Does not reproduce` means evidence **refutes** the claim, not that the path went unexercised.
3. Off-path homes: stored and enterable but off the walked path → `Known limitation` with `field exists, off-path`;
   stored with no writer → `Unrepresented source data` with `stored, no writer`; no storage → `Unrepresented source
   data`.
4. A regression label needs a proven predecessor boundary.

**Round 5's error is not repeated.** Round 6's C1 proved round 5's "seven slices landed at `da6e6367`" was a
misattribution. Every round-6 slice commit cited here as evidence was checked to touch the code it is cited for, and
every round-6 landing is re-read at `8a3cf21c` rather than inherited (§4.5).

### 2.4 Blocking set, corrected

| Walk 7 states | This register reads |
| --- | --- |
| 3 blocking: `F-10`, `F-20`, `F-26` | **3 blocking: `R7-20` (`F-20`), `R7-26` (`F-26`), `R7-82`** (the dropped cleared-objectives row). `R7-10` (`F-10`) is reclassified **`Known limitation (discoverability)`**: the `Level` field accepts any `1–50` and the deck commits once it is populated. The count stays three; the membership changes. Recorded as **C3**. |

### 2.5 Two-door reading and the snapshot inventory correction

The plan's §1.2 states the snapshot door carries "38 findings (16 F + 11 E + 11 U)" with `S-F-nn` IDs. **The
document contains no numbered findings register.** Measured:

```
$ grep -oE '\((F|E|U)-\)' docs/audits/rice-shower-unity-cup-ux-walk-7-snapshot-door.md | sort | uniq -c
      1 (E-)
      6 (F-)
     12 (U-)
```

**19 unnumbered markers, zero numbered IDs**, plus a 25-item §7.7 "Open items" list that overlaps them and adds
workflow items. This register therefore keys snapshot rows to **section + quoted string**, and §4.4 articulates the
overlap: snapshot observations that reproduce a canonical row are facets under §5, not new rows. The extended rows
are the snapshot observations canonical walk 7 does not carry plus the dropped cleared-objectives finding.

### 2.6 Two-door conclusion, stated once

Both doors carry the source's position. The snapshot door reaches `Turn 67 of 72 · Senior Year Early October` from
`Year/Month/Half`; the wizard door reaches the same state by typing `67` into the training form. **Position-carrying
is an application property, not a door property.** The wizard's defect is its default and its label, not its
capability. Everything that reproduces on both doors is marked `both` in §5 rather than double-filed.

---

## §3 Reference instantiation

Cited by rows; none used to classify a walk finding.

| Reference | Used for |
| --- | --- |
| `docs/UMAMUSUME_REFERENCE.md` §1.1.5 | rest recovery tiers: modal 50, big success 70, failure 30 |
| `docs/UMAMUSUME_REFERENCE.md` §1.1.6 | mood strings; training ±20/±10/0/−10/−20; race ±4/±2 |
| `docs/UMAMUSUME_REFERENCE.md` §1.2.6 | the 24-turn year; the 72-vs-78 extent convention |
| `docs/UMAMUSUME_REFERENCE.md` §1.3.4 | Unity Cup floor 1300/1300/1300/1300/1800, **and the warning that an observed in-force cap must not be printed as "the scenario cap"** |
| `docs/UMAMUSUME_REFERENCE.md` §1.5.2 | spark categories; the **stat award** figures |
| `docs/UMAMUSUME_REFERENCE.md` §2.2.2 | per-stat team rank ladder; `S+` overall-only |
| `docs/scenarios/09-global-race-calendar.md:100-105` | the three URA Finals rounds are shared; only the Final forks |
| `docs/framework-ui-ux/design-framework-index-2026-10-21.md` | rule IDs for slice audits only |
| `KNOWN-ISSUES.md` `KI-122` | the shared-rounds ruling (CLOSED — does not reproduce) |

---

## §4 Reconciliation table

### 4.1 Functional findings — `R7-01`…`R7-38`

| ID | Walk | Surface | Settling read | Status | Facets / notes |
| --- | --- | --- | --- | --- | --- |
| R7-01 | F-01 | Trainee list reverts to 66 rows while `Search` keeps `Rice Shower`; URL drops the query | `TraineeSelect.vue` select handler + `TraineeSelectController::index()`; rendered `S03b-trainee-search.png` → `S03c-trainee-chosen.png` | Confirmed defect | Two halves: term preserved in the input, list unfiltered. Walk 6's F-03 shape, different mechanism |
| R7-02 | F-02 | Form count 2 vs 3 | **`db read`: `character_cards` for `umamusume_id = 30` returns three rows at `8a3cf21c`** — id 31 `[Rosy Dreams]`, id 47 `[Vampire Makeover!]`, id 170 `Yummy Dreamy Fairy`. The picker renders `2 forms` (`§7.3 S03`); the profile renders three including `Yummy Dreamy Fairy` (`§7.3 S04`). | **Source conflict / open** | The walk's own two sections disagree and the database agrees with S04. Settling read named: `TraineeSelectController`'s `character_cards` filter vs `TraineeProfileController`'s. **Not** `Confirmed fixed` — C1 |
| R7-03 | F-03 | Chosen `Form` not written to the draft | `TraineeSelect.vue` form binding; `SetupDraft` carries no `form` key; `StartCareerRequest` reads none; rendered `§7.3 S08` `FORM · N/A` | Confirmed defect | Same root as R7-13; one fix carries both |
| R7-04 | F-04 | Empty-target refusal no longer reproduces | Walk `§7.3 S05b`: `Build target saved.` on a wholly empty block; `S05b-build-target-empty-accepted.png`; `StoreBuildTargetRequest::isEmptyTarget()` + `CareerBuildTargetTest.php:120-142` | Does not reproduce | Verification-only |
| R7-05 | F-05 | `Sum of the five entered ceilings` prints `7000` before any ceiling entered | `resources/js/pages/Career/BuildTarget.vue:252` — `const totalCap = computed((): number => props.statOrder.reduce((sum, stat) => sum + cap(stat), 0));`, where `cap(stat)` falls back to the published scenario cap. Label at `:531` says `entered`. | Confirmed defect | **A fixed label naming the wrong figure is a new defect, not a recurrence.** Round 6's S2.3 (`e55f5458`) moved the label; the figure under it never moved. C7 |
| R7-06 | F-06 | `Rank (stars)` / `Grandparent rank (stars)` `min=0`, no `max` | `LegacySelect.vue` rank-star inputs carry `min=0` and no `max`; the spark `Stars` input sets `1…3`, proving the cause | Confirmed defect | Walk 6's F-07 reproduces; a11y tree reports `valuemax="0"` |
| R7-07 | F-07 | "Two different ★★★ cap raises on one screen" | `LegacySelect.vue:810` — `+5, +12 or +21 to the stat it names, for one, two or three stars` — a **stat award**; `:895-896` — `lifts the cap of the stat it names by 16` — a **cap raise**. `UMAMUSUME_REFERENCE.md` §1.5.2 `:655` and §1.3.4 `:420` are the two mechanics. | **Ruled out** — C11 | Round 6's `Does not reproduce` **stands**. Walk 7 calls both "cap raises"; the first is not one |
| R7-08 | F-08 | Manual-parent loss no longer reproduces | Walk `§7.3 S06e` cold reload re-rendered all six ancestor names, four letters, both Guest states; `S06e-legacy-after-reload.png`; preflight `2 of two parents recorded`; R6-05 closed at `279381b5` | Does not reproduce | Verification-only |
| R7-09 | F-09 | Slot 6 is the `Friends` slot while the source's borrowed card sits in slot 4 | `DeckSelect.vue` slot-role model; source §1.4 places Super Creek in the borrowed position while the client tags her `Friends` | Known limitation | Two legitimate models, one screen; owner call S20 |
| R7-10 | F-10 | `Continue to preflight` refused while a slot lacked `Level`, no inline reason | `DeckSelect.vue:415` binds `:disabled="form.processing"` only; the refusal came from the required-level rule, and the `Level` input accepts `1–50` and is enterable — `§7.3 S07` typed `30` and the primary enabled | **Known limitation (discoverability)** | **Blocking corrected to No** — C3. Hidden requirement, not obstruction |
| R7-11 | F-11 | Deck-level loss no longer reproduces | Walk `§7.3 S16` read `35/30/30/50/30/35`; `S16-deck.png`; R6-11 closed at `417315d6` | Does not reproduce | Verification-only |
| R7-12 | F-12 | `Pal slot` label no longer appears | Walk `§7.3 S07` reads `Slot 6 · Friends` / `Friends slot`; `S07e-deck-source.png`; R6-23 closed at `569f47e3` | Does not reproduce | Verification-only |
| R7-13 | F-13 | Preflight `FORM · N/A` while the picker's select defaults `[Rosy Dreams]` | Same read as R7-03; rendered `§7.3 S08` | Confirmed defect | Same root as R7-03 |
| R7-14 | F-14 | Skipped target renders `Your target · Missing` in the same `STATE` column as `Pass` | `Preflight.vue` check-row builder; `§7.3 S08` renders `Missing` / `Not entered` | Confirmed defect | A supported-by-design choice renders as a defect |
| R7-15 | F-15 | `CAREER LENGTH 72 turns` is a literal | `Preflight.vue:459` — `const CAREER_TURNS = 72;`, a module constant, not derived from the run; `:580` documents why it is stated literally | Known limitation | Round 6 §13.5 ruled the 72-convention; the source's §2.5 derivation depends on it and agrees |
| R7-16 | F-16 | Preflight read-back now names scenario and trainee | Walk `§7.3 S08`: `TRAINEE Rice Shower`, `SCENARIO Unity Cup`; `S08-preflight.png` | Does not reproduce | Verification-only |
| R7-17 | F-17 | Before any turn: `No turn recorded yet`, no position | `CockpitController::headerSection()`; `§7.3 S09` | Known limitation | The run has no position; the cockpit cannot invent one |
| R7-18 | F-18 | "URA Finals obligations on a Unity Cup run" | `db read` on `race_catalog_slots`: id 405 `URA Finals Qualifier` `scenario_key = null`, id 406 `URA Finals Semifinal` `null`, id 408 `URA Finals Final (Aoharu)` `unity_cup`. `docs/scenarios/09-global-race-calendar.md:105`; `RaceCatalogSlot::scopeForScenario()` `:149-154`; `KI-122` CLOSED | **Ruled out** — C12 | The rounds are shared by design; removing the labels would delete valid obligations from every run |
| R7-19 | F-19 | Cockpit prints `1,300/1,300/1,300/1,300/1,800` under `cap` | `Cockpit.vue:186` types `caps` as `{entered: number\|null; scenario: number}`; `CockpitController.php:552` builds it; the stat bar's `denominator-label="cap"` at `:827` prints `cap()`, which falls back to `scenario` when `entered` is null. `db read` on run #3: `caps` column is `null`. | Confirmed defect | Two facets: the label `cap` names the floor whenever no entered cap exists; and the screen cannot know the in-force caps because no surface records them (that half is R7-66). The snapshot door reported the same independently (§7.7 item 12) |
| R7-20 | F-20 | `Turn *` defaults `1`; primary reads `Record turn 1`; field accepts any position | `TrainingDetail.vue` turn default `1`; typing `67` left `aria-invalid="false"`, every `validity` flag false, and relabelled the primary `Record turn 67`; the cockpit then rendered `Senior Year · Early October · Turn 67 of 72` (`S11b-cockpit-position.png`), payload `careerPosition {year:3, month:10, phase:"Early", turn_index:67}`, `header.next_turn_to_play: 68` | **Confirmed defect** | **Blocking.** The default, not the capability. Falsifies walk 6's F-13 (C2). Field has `min=1` and **no `max`** |
| R7-21 | F-21 | `Undo turn 1` enabled before any turn exists | `TrainingDetail.vue:526` — `const undoableTurn = computed(() => Math.max(1, props.write.turn - 1));` and `:727` renders the button with no `disabled` binding, unlike `RaceDecision.vue:461` which does disable its own | Confirmed defect | Control rendered against absent state |
| R7-22 | F-22 | Advisor: `Junior Make Debut is due and not recorded. It fell due on turn 12, 55 turns ago.`, `current` at turn 67 | `CockpitController::objectiveSection` `:871-876` — `$recorded => 'completed'`, `$slot->turn === null => 'unknown'`, `$slot->isAtOrBeforeTurn($next) => 'current'`; slot 1 carries `year 1, turn 12` and `$next` is `{year:3, turn:20, turn_index:68}`, so `isAtOrBeforeTurn` holds and the row is `current`. `NextDecision.php:303` composes `is due and not recorded.` | Confirmed defect | Arithmetic right, semantics wrong: `ranThisRace` is false because the run has no `race_entries` |
| R7-23 | F-23 | `fan_ladder` computed, not rendered | `CockpitController.php:563` — `'fan_ladder' => $fans === null ? null : FanLadder::fromValue((int) $fans)`; `Cockpit.vue` prints only `FANS`; R6-43 (`530d62b9`) records the display removal as an override | Known limitation | R6-43's override; reconnecting is a design call |
| R7-24 | F-24 | "Two contradictory Energy statements for one run" | **Not simultaneous — two points in time.** `RecordedActionCard.vue:16-20` states the contract: `expected` is a quote, "so a card read three turns later says what was advised then, not what the advisor would say now"; `NextDecision.php:150-184` `recordedAction()` reads exactly that stored row from `turn_entries.decision_snapshot`. The live advisor reads the current state; the refusal was the pre-commit state. | **Does not reproduce** — C13 | The walk read a temporal quote as a live contradiction. Residual clarity gap folded into R7-57 |
| R7-25 | F-25 | `55 turns ago` asserts a missed Junior obligation the source never claims | Same read as R7-22; `67 − 12 = 55` is correct arithmetic on a position the run never played | Confirmed defect | Same root as R7-22, distinct symptom; one slice |
| R7-26 | F-26 | All seven calendar races locked `Maiden lock not cleared`; `Enter a race` disabled | `RaceDecisionController.php:175` — `$maidenCleared = $run->hasClearedMaidenLock();`; `TrainingRun.php:888-905` — cleared only when a `race_entries` row has `status = Completed`, `placement = 1`, and its slot's `grade_code` in `[800, 900]` (**Debut or Maiden**); `TrainingRun.php:916-923` — `maidenGates()` returns `true` for grades `[100,200,300,400,700]` while uncleared. Run #3 has no `race_entries`. | **Confirmed defect** | **Blocking.** The predicate is exact: a mid-career import cannot have a Debut/Maiden row, so every standard race stays locked permanently. The snapshot door's four off-calendar entries did not clear it, correctly — they are not grade 800/900 wins |
| R7-27 | F-27 | Three screens, three `Undo turn N` | Four derivations, measured: `TrainingDetail.vue:526` `props.write.turn - 1`; `RaceDecision.vue:165` `(props.nextTurn?.turn ?? 1) - 1`; `EventDecision.vue:104` `initialTurn - 1`; `Timeline.vue:167` `lastTurn` | Confirmed defect | Four surfaces, four sources. The Races value (`19`) corresponds to no turn run #3 logged |
| R7-28 | F-28 | Off-calendar form defaults `NotOffered` / month `10` / half `Late` on an `Early` run | `RaceDecision.vue` off-calendar defaults, read from the DOM payload (`§7.3 S12`) | Confirmed defect | Defaults not derived from the run's position |
| R7-29 | F-29 | `TYPE` and `HINTS` filters disabled with one inert `Any` | `SkillsPlanner.vue:513` and `:517` — both `<BaseSelect disabled :options="[{ value: '', label: 'Any' }]" ... />`, each carrying a `title` stating why: `No column carries a skill type, so this filter cannot act.` / `Hint levels are not recorded per skill, so this filter cannot act.` | Known limitation | Documented on the control itself; R5-30 |
| R7-30 | F-30 | Doubled hint caption no longer reproduces | Walk `§7.3 S13`: `Hint Lvl 1` appears once per row; R6-15 closed at `847e4cdb` | Does not reproduce | Verification-only |
| R7-31 | F-31 | `Record event` enabled with every required field empty | `EventDecision.vue:371` primary has no `disabled` binding; the provenance block is marked `(required to record)` in copy while the primary is live; `§7.3 S14` measured `recordDisabled: false` | Confirmed defect | Same class as R7-35 |
| R7-32 | F-32 | Turn convention split: Events/Inheritance prefill `68`, Timeline labels `67`, `Edit turn 67` opens at 68 | `EventDecision.vue:104` and `InheritanceEvent.vue` prefill `68`; `Timeline.vue` prints the record's own turn `67`; `TrainingDetail.vue` opens at `68` | Confirmed defect | Same family as R7-20 |
| R7-33 | F-33 | Both Daiwa Scarlet instances render `recorded ancestor` with no rank, though `S+` and `B+` were entered on S06 | `InheritanceEvent.vue` ancestor panel prints name + `recorded ancestor` + star grade; no letter reaches the panel; `§7.3 S15` | Confirmed defect | **Shares a root with R7-34** (C4). Letter entered, discarded |
| R7-34 | F-34 | Letters render as `★★★` stars on both parents | Same read; the star comes from `Rank (stars) = 3`, walk 7's own improvisation `§7.4 I-01`, not from the letter | **Walk-artifact + Confirmed defect** | Two halves: the star display is **honest** (the value was entered) and is a walk artifact; the letter-not-rendered half is R7-33. Not merged, per §2.1 |
| R7-35 | F-35 | `Record inheritance` enabled with nothing selected | `InheritanceEvent.vue` primary; `§7.3 S15` renders both buttons enabled | Confirmed defect | Same class as R7-31 |
| R7-36 | F-36 | Every parent prints `This parent carries no recorded spark.` | `LegacySelect.vue` has no spark input of any kind: the parent and grandparent blocks carry name, rank letters, rank stars and Guest only, and `§7.3 S06`'s field dump lists 24 fields, none a spark | Confirmed defect | **Largest representability gap.** The Inheritance screen's honest absence is caused upstream |
| R7-37 | F-37 | Every run-deck card reads `Not a Scenario Link`, including Haru Urara | `DeckSelect.vue:65` types `scenario_link: 'linked' \| 'not_linked' \| 'unknown'` and `:194` counts `=== 'linked'`; the draft step reported `Scenario link on a card  1` (`§7.3 S07`) while the run deck reads `Not a Scenario Link` on all six (`§7.3 S16`) | Confirmed defect | Two surfaces, one deck, two answers |
| R7-38 | F-38 | Career-nav `Result` is a live link while the band says "Available when the career is completed" | `§7.3 S18` plus the nav band; the screen enforces the gate, the nav does not | Confirmed defect | Gate in the screen, not the nav |

### 4.2 Experiential findings — `R7-39`…`R7-62`

| ID | Walk | Short | Status | Notes |
| --- | --- | --- | --- | --- |
| R7-39 | E-01 | Two labels (`Start a career`, `New Career`) → one route | Known limitation | Navigation redundancy, not a defect |
| R7-40 | E-02 | `Import an existing career` vs `Import a historical career` differ by one word | Confirmed defect | Neither card states the distinction |
| R7-41 | E-03 | `Continue to trainee` disabled with no reason | Confirmed defect | Radios carry `required=false` |
| R7-42 | E-04 | Four scenario labels carry no description | Known limitation | Copy design |
| R7-43 | E-05 | `1 trainee are catalogued.` | Confirmed defect | Agreement |
| R7-44 | E-06 | One label, two destinations (`Continue to build target`) | Confirmed defect | Route repeats the picker's own label |
| R7-45 | E-07 | Aptitude case drift (`Pace chaser A` vs `Pace Chaser A`) | Confirmed defect | Two screens, two casings |
| R7-46 | E-08 | `Scenarios this trainee is linked to` withdraws its own claim | Confirmed defect | Region lists four; the note denies a list exists |
| R7-47 | E-09 | `This is the step the current build skips.` above a mandatory form | Confirmed defect | The step is stamped through, so "skips" is stale |
| R7-48 | E-10 | Only four of five stats have a ceiling input | Known limitation | `No enterable ceiling for this stat` is stated |
| R7-49 | E-11 | `TOTAL AGAINST THE PUBLISHED SAMPLE` prints `N/A` both sides | Known limitation | Each `N/A` carries a `title` giving the reason |
| R7-50 | E-12 | Guest checkboxes carry no `id`/`aria-label` beyond visible text | Confirmed defect | Not addressable except positionally |
| R7-51 | E-13 | `Name contains` matches the character name, not the epithet | Confirmed defect | Three of six slots mis-picked during the walk |
| R7-52 | E-14 | `Wit cap is 1800. Consider a Wit-heavy deck.` unconditional | Confirmed defect | Contradicts a three-Speed deck |
| R7-53 | E-15 | Slot picker: one `Equip` per row, no pagination, no count | Known limitation | Density, not correctness |
| R7-54 | E-16 | `Trainee · Pass · Rice Showerライスシャワー` | Confirmed defect | Latin and JP names run together |
| R7-55 | E-17 | `Create the run` explanation below the button | Known limitation | The reason is present, misplaced |
| R7-56 | E-18 | `Energy state` defaults `Exact` while `ENERGY AFTER` is empty | Confirmed defect | Default contradicts the field |
| R7-57 | E-19 | Seven unlabelled number inputs (`failure_rate`, six `preview_gains[*]`) | Confirmed defect | No label, heading or help text. **Carries R7-24's residual as an explicit observation**: the recorded-action card prints `Recorded 07/11/2026` over a quote that carries no "as advised before this turn" wording, so two Energy sentences from different points in time read as simultaneous (C13). That wording gap is this row's copy defect; the data behind both sentences is correct |
| R7-58 | E-20 | Off-calendar defaults (`NotOffered`, `Late`) vs the run's `Early` | Confirmed defect | Same root as R7-28 |
| R7-59 | E-21 | `No skill is recorded on this run yet` beside `SKILL POINTS 173` | Known limitation | Both true; the screen does not distinguish never-entered from never-acquired |
| R7-60 | E-22 | Run-scoped deck shows editable `Level`/`Ownership` with no visible save control | Confirmed defect | Commit path unstated |
| R7-61 | E-23 | Phase copy names `Junior Make Debut` as `the one dated obligation` on a Senior run | Confirmed defect | Same root family as R7-22 |
| R7-62 | E-24 | The Result gate absent before the run exists, live after | Confirmed defect | Same gate, two presentations |

### 4.3 Unrepresented source data — `R7-63`…`R7-81`

| ID | Walk | Datum | Status | Notes |
| --- | --- | --- | --- | --- |
| R7-63 | U-01 | Scenario full label `Unity Cup: Shine On, Team Spirit!` | Unrepresented source data | Config carries a short label only |
| R7-64 | U-02 | Client-frame aptitudes (`Dirt C`, `Mile B`, `Front A`, `Late B`, `End C`); the screens print the catalogue's `Dirt G`, `Mile C`, `Front runner B`, `Late surger C`, `End closer G` | **Source conflict / open** | The source's 2026-10-09 erratum records that the client prints these and the export disagrees; `ADR-0004` governs the export. The settling read is the export against the frame, not the UI |
| R7-65 | U-03 | `Potential Lvl 2` on the wizard path | Known limitation | `field exists, off-path`: Run settings carries `Potential Level` (R6-35) |
| R7-66 | U-04 | Caps in force `1325/1322/1368/1308/1800` | Unrepresented source data | Two facets: the wizard's `ceiling` inputs are goals, not in-force caps; and `db read` on run #3 shows the `caps` column `null`. R7-19 is the display half |
| R7-67 | U-05 | Growth Rate row on the setup steps | Known limitation | `field exists, off-path`: Run settings (R6-37) |
| R7-68 | U-06 | Per-ancestor spark chains: six ancestors, blue/pink/green/white, star counts | Unrepresented source data | R7-36 is the Inheritance half; this is the Legacy-step half. No storage |
| R7-69 | U-07 | Catalogue-max vs reached effect values | Known limitation | R6-41: the reached value is derived, not recorded |
| R7-70 | U-08 | Borrowed-card placement (Super Creek slot 4 vs the screen's slot-6 `Friends` model) | **Source conflict / open** | Two legitimate models; no ruling picks one. Same family as R7-09 |
| R7-71 | U-09 | Client's three career-screen counters | Unrepresented source data | R6-34: the countdown object is a dead pipe |
| R7-72 | U-10 | Facility level's effect on the per-turn tile | Unrepresented source data | The tile prints `Lv not recorded base` even after the five level fields are filled |
| R7-73 | U-11 | Class badge and fan ladder (`Star`, 30,755 gap) | Known limitation | Payload computes `fan_ladder`; the display was removed by R6-43's override |
| R7-74 | U-12 | Six Team-block values | Known limitation | Partial field coverage on Team setup / Run settings; R6-47's roster absence stands |
| R7-75 | U-13 | Kyoto Daishoten, the race scheduled this turn | Confirmed defect | **At the surface**: blocked by R7-26's lock; the race card has no field independent of it |
| R7-76 | U-14 | Four preseason rounds won | Known limitation | R6-49: one tally, no per-round rows |
| R7-77 | U-15 | Pending Unity Cup finals as a race-desk row | Unrepresented source data | R6-50: no fixture model |
| R7-78 | U-16 | Ten owned skills plus Corner Recovery ○ | Unrepresented source data | No wizard skills step; nothing seeds the acquired set |
| R7-79 | U-17 | Per-skill level and hint level | Unrepresented source data | `ADR-0029` D1 bars collection state |
| R7-80 | U-18 | Ancestor rank badges as letters | Confirmed defect | R7-33 is the rendering half; the entry path exists on S06 |
| R7-81 | U-19 | `Entry criteria met!` on the active objective | Unrepresented source data | No `entry criteria` state exists; R7-82's root |

### 4.4 Extended rows — `R7-82`…`R7-98`

| ID | Source | Surface | Settling read | Status | Notes |
| --- | --- | --- | --- | --- | --- |
| R7-82 | walk-7 traceability matrix (§7.7 "Cleared objectives") | The three cleared objectives (Nikkei Sho top 3, Tenno Sho Spring 1st, Takarazuka Kinen top 3) render `missed` on a turn-67 run | `TeamPanelBuilder.php:249-262`. `objectiveMet()` (`:323-353`) requires a matching `race_entries` row, or for a fan goal a reading at or below the goal turn; with neither, `$met` is false. `$reached = $goal->isAtOrBeforeTurn($next)` is true at turn 67, so the state falls to `$reached => 'missed'` at `:261`. | **Confirmed defect** | **Blocking.** Same family as R7-22/R7-25, different symptom: absence of a record is rendered as a negative state. Filed because walk 7's register has no row for it (C14) |
| R7-83 | snapshot §7.7 item 6 | Off-calendar race records invisible on Timeline (`RACES ENTERED 0` after four records) | Timeline query + the snapshot walk's `§7.3 S12`/`S17` | Confirmed defect | Recorded races exist; the timeline omits them |
| R7-84 | snapshot §7.7 item 13 | Skills: Recommended says "no recorded SP" while the budget bar shows 173 | `SkillsPlannerController::recommended()` disjunction first branch | Confirmed defect | The branch is false on that run |
| R7-85 | snapshot §7.7 item 14 | Skills: Purchasable reads the full catalogue (476 priced / 7140 SP), not the run's pool | `SkillsPlannerController` purchasable source | Confirmed defect | Catalogue-wide; the shortfall figure is not the run's |
| R7-86 | snapshot §7.7 item 15 | Skills: Available-column copy `No skill is marked` after one Acquired | `SkillsPlanner.vue` Available copy | Confirmed defect | The sentence holds only for the Available subset |
| R7-87 | snapshot §7.7 item 16 | Skills: raw predicate `distance_type==3&is_finalcorner==1&overtake_target_time>=2` in a Skill cell | Skill row renderer | Confirmed defect | Internal predicate leaked to display |
| R7-88 | snapshot §7.7 item 17 | Skills: JP name `Steadfast 食い下がり` in display | Skill row heading; `lang/en/uma.php` is the terms map | Confirmed defect | Client vocabulary rule prints English |
| R7-89 | snapshot §7.7 item 19 | Skills: `1 skills · page 1 of 1` | Pluralisation | Confirmed defect | Copy |
| R7-90 | snapshot §7.3 S08 | Skills: Learned card exposes `Level … of 6` while the captions show five | `SkillsPlanner.vue` Learned Level spinbutton; `HintDiscount::ladder()` | Confirmed defect | The ceiling contradicts the captions on one screen |
| R7-91 | snapshot §7.7 item 21 | Events/Skills disclosure caret does not flip | Disclosure component | Confirmed defect | The caret is the only open/closed signal |
| R7-92 | snapshot §7.7 item 22 | Inheritance sub-copy truncated (`… carried f…`) | CSS line-clamp on the sub-copy | Confirmed defect | Copy clipped |
| R7-93 | snapshot §7.7 item 23 | Timeline sub-copy truncated (`… behi…`) | Same | Confirmed defect | Copy clipped |
| R7-94 | snapshot §7.7 item 24 | Deck ownership default `N/A`; six slots can be saved unclassified | `DeckSelect.vue` ownership binding | Known limitation | R4-20 records the same; not a new finding |
| R7-95 | snapshot §7.7 item 25 | Deck `Save deck` replaces the whole deck | The copy warns: `Saving replaces the whole deck: slots left empty are cleared.` | Known limitation | Deliberate; the warning is explicit |
| R7-96 | snapshot §7.7 item 25 | Deck: no read-back of the source's six cards | The deck screen's contract is the run's six slots, not a comparison surface | Known limitation | Contract, not defect |
| R7-97 | snapshot §7.3 S11 | Deck analysis collapsed group purpose unclear | Label reads `Choose a card` / `Deck analysis` | Does not reproduce | The function is self-evident on expand |
| R7-98 | snapshot §7.3 S05 | Snapshot door: no Mood field; cockpit prints `N/A, not recorded` | `Snapshot/Setup.vue` field list | Unrepresented source data | The source's `GREAT` cannot be entered on that door |
| R7-99 | snapshot-door §7.3 S04 | Snapshot review's read-back omits **Scenario** and **Trainee** while the copy claims `This is exactly what will be written. Read it back` | `SnapshotController::reviewFields()` (`:196`…) builds one row per `field_states` key only, and `CreateSnapshotRun::OPTIONAL_STATE_FIELDS`/`CORE_STATE_FIELDS` (`:41`, `:46`) carry `speed`, `stamina`, `power`, `guts`, `wit`, `energy`, `fans`, `skill_points` — no `umamusume_id`, no `scenario`. Both are `required` in `StoreSnapshotRequest::fieldRules()` (`:47`, `:48`), so the review re-posts them via `payload` (`Review.vue:53`) but never renders them. | **Confirmed defect** | Two required identity inputs are confirmable nowhere on the confirm screen. `payload` carries them (so the write is correct); the read-back is what omits them. S-only — the wizard's S08 preflight is a different contract screen and its read-back does name both (R7-16). Filing closes the one gap plan v1 §5.2 listed as `S-F-14` and plan v2 §5.5 dropped. C16 |

### 4.4a The snapshot-door inventory, enumerated

Plan v1 §5.2 enumerated sixteen `S-F-nn` items and then pointed at the walk for the E and U sides ("listed in walk
7 snapshot-door §7.7"); plan v2 §5.5 replaced the whole scheme with an unnumbered list, having measured that the
walk carries no `S-F-nn` IDs at all (C10). Neither plan's list is a mapping. This table closes the question the
review raised: every item either plan named, against the register row that carries it.

**Column `v1 §5.2`** is the ID plan v1 assigned. **Column `v2 §5.5`** is the row plan v2 assigned, where it did.
**Column `§7.7`** is the snapshot walk's own open-item number, which is the only numbering the walk actually
carries.

| v1 §5.2 | Surface | §7.7 | Register row | Door | Basis |
| --- | --- | --- | --- | --- | --- |
| `S-F-01` | Maiden lock blocks all calendar races | — (§7.3 S07) | R7-26 | both | Same defect as `F-26`; wizard row carries it |
| `S-F-02` | `Undo turn 18` on a turn-67 run | 8 | R7-27 | both | Same class as `F-27`; the register cites both values (`1`, `19`, `67`) |
| `S-F-03` | Timeline `RACES ENTERED 0` after four races | 6 | R7-83 | S | Extended row: the wizard walk recorded no off-calendar race |
| `S-F-04` | Advisor reports Junior obligation on Senior run | 9 | R7-22 | both | Same defect as `F-22` |
| `S-F-05` | URA obligations on Unity Cup run | 10 | R7-18 | both | **Ruled out** on both doors (C12) |
| `S-F-06` | `Energy not recorded` beside `Energy 50` | 11 | R7-24 | both | **Does not reproduce** — temporal quote (C13) |
| `S-F-07` | Cockpit prints floor under `cap` | 12 | R7-19 | both | Same defect as `F-19` |
| `S-F-08` | Skills Recommended misdiagnoses SP state | 13 | R7-84 | S | Extended row |
| `S-F-09` | Skills Purchasable reads full catalogue | 14 | R7-85 | S | Extended row |
| `S-F-10` | Available-column copy contradicts state | 15 | R7-86 | S | Extended row |
| `S-F-11` | Raw condition expression leaked | 16 | R7-87 | S | Extended row |
| `S-F-12` | JP skill name in display | 17 | R7-88 | S | Extended row |
| `S-F-13` | `Record event` enabled with empty required fields | 20 | R7-31 | both | Same defect as `F-31` |
| **`S-F-14`** | **Snapshot review omits Scenario and Trainee from read-back** | — (§7.3 S04) | **R7-99** | **S** | **Added by C16.** Was the one uncovered item |
| `S-F-15` | `1 skills · page 1 of 1` | 19 | R7-89 | S | Extended row |
| `S-F-16` | `Result` nav live while band says available when completed | 7 | R7-38 | both | Same defect as `F-38` |
| `S-E-01…S-E-11` | (eleven, listed in the walk) | — | R7-82…R7-98 overlap; no `S-E` marker in the walk | S | Plan v1 gave no per-item text and the walk carries no `S-E-nn` IDs; each observation is either a `both` facet already in §4.1–§4.3 or an extended row. **Not enumerable** — see the note below |
| `S-U-01…S-U-11` | (eleven, listed in the walk) | — | as above | S | Same |

**Why the E and U sides cannot be enumerated item by item.** Plan v1 wrote
`S-E-01 through S-E-11 | E | (11 experiential findings, listed in walk 7 snapshot-door §7.7)` — a range over no
list. The walk's own marker scan returns `1 (E-)` and `12 (U-)` (C10), so there is no eleven-plus-eleven set to
map. §7.7's twenty-five items are numbered but not classified into F/E/U. The register therefore maps the F side
item by item (sixteen rows above, all accounted for) and records the E and U sides as **counts, not IDs**:
**19 unnumbered markers = 6 `(F-)` + 1 `(E-)` + 12 `(U-)`**, of which the six `(F-)` are `S-F-01`…`S-F-06`
territory and the thirteen E/U markers are carried by the extended rows and the `both` facets. **The reduction is
complete for every item either plan named as an item; it is not item-by-item for the two ranges plan v1 wrote
without text**, and this register does not invent that text.

**§7.7 ↔ register row, all twenty-five.** Item 1 (aptitude conflict) → R7-64; item 2 (Vodka Power spark) → R7-68;
item 3 (Daiwa Guts spark) → R7-68; item 4 (`UG` vs `UG1`) → R7-64's family; item 5 (per-ancestor spark chains) →
R7-68; item 6 → R7-83; item 7 → R7-38; item 8 → R7-27; item 9 → R7-22; item 10 → R7-18; item 11 → R7-24;
item 12 → R7-19; item 13 → R7-84; item 14 → R7-85; item 15 → R7-86; item 16 → R7-87; item 17 → R7-88; item 18 →
R7-29; item 19 → R7-89; item 20 → R7-31; item 21 → R7-91; item 22 → R7-92; item 23 → R7-93; item 24 → R7-94/95/96;
item 25 → R7-95/96. **Every §7.7 item is mapped; none is unfiled.**

### 4.5 Walk 6's F-01 → F-17 re-measurement

Walk 7 canonical §7.5a re-measured all seventeen at `8a3cf21c`. This register re-reads each round-6 landing rather
than inheriting the verdict.

| Walk 6 | Round 6 row and commit | Walk 7 observation at `8a3cf21c` | This register |
| --- | --- | --- | --- |
| F-01 | R6-01 `Does not reproduce` (`be02297`) | Empty target accepted | Confirmed fixed; round-6 verdict holds |
| F-02 | R6-02 `Confirmed defect` | Picker 2, profile 3, DB has 3 | **Re-opened as `Source conflict / open`** (R7-02, C1) |
| F-03 | R6-03 `Confirmed defect` | Reproduces, new shape | Confirmed defect (R7-01) |
| F-04 | R6-04 `Confirmed defect` | Label half fixed; propagation untestable | Confirmed defect; residual is R7-05 (C7) |
| F-05 | R6-05 `Confirmed defect` (`279381b5`) | Parents persist through cold reload | Confirmed fixed |
| F-06 | R6-06 `Does not reproduce` | Two figures present, two mechanics | **Round 6 stands.** Walk 7 misfiled (C11) |
| F-07 | R6-07 `Confirmed defect` | Reproduces | Confirmed defect (R7-06) |
| F-08 | R6-08 `Confirmed defect` | Both surfaces say zero | Changed shape (R7-36) |
| F-09 | R6-09 `Confirmed defect` | Wording fixed | Confirmed fixed (wording); substance in R7-03/R7-13 |
| F-10 | R6-10 `Confirmed defect` | `Legacy · Pass · 2 of two parents recorded` | Confirmed fixed — the `Pass` is earned |
| F-11 | R6-11 `Confirmed defect` (`417315d6`) | `35/30/30/50/30/35` on the run deck | Confirmed fixed |
| F-12 | R6-12 `Confirmed defect` (`530d62b9`) | `Low band` / `173` on the timeline | Confirmed fixed |
| F-13 | R6-13 `Known limitation` | `Turn = 67` accepted; `Turn 67 of 72` rendered | **Falsified at the wizard half** (C2). R6-13 not edited |
| F-14 | R6-14 `Known limitation`, `KI-122` | Observation reproduces; premise refuted | **Ruled out** (C12). R6-14's reasoning stands |
| F-15 | R6-15 `Confirmed defect` (`847e4cdb`) | Caption single | Confirmed fixed |
| F-16 | R6-16 `Confirmed defect` | Reproduces | Confirmed defect (R7-03) |
| F-17 | R6-17 `Confirmed defect` | Reproduces | Confirmed defect (R7-38's family) |

**Tally:** 8 confirmed fixed (F-01, F-05, F-06, F-09, F-10, F-11, F-12, F-15); 5 confirmed defect (F-03, F-04,
F-07, F-16, F-17); 1 re-opened (F-02); 1 falsified (F-13); 1 ruled out (F-14); 1 changed shape (F-08).

---

## §5 Cross-register mapping

Every walk-7 finding is attributed. `—` means no prior row names it. `both` marks a finding that reproduces on both
doors and is filed once.

| R7 | Walk | Door | Nearest prior | Relationship at `8a3cf21c` |
| --- | --- | --- | --- | --- |
| R7-01 | F-01 | W | R5-16, R6-03 | Server half fixed by R6-03; the client half is this row |
| R7-02 | F-02 | W | R4-14, walk-6 F-02 | **Re-opened** (C1) |
| R7-03 | F-03 | W | walk-6 F-09 | Placeholder fixed in wording; the form is still not persisted |
| R7-04 | F-04 | W | R6-01, `be02297` | Round-6 contract holds |
| R7-05 | F-05 | W | R6-04, `e55f5458` | New line; same label family |
| R7-06 | F-06 | W | R6-07, R5-08 | Reproduces |
| R7-07 | F-07 | W | R6-06 | Round 6 correct; walk 7 misfiled |
| R7-08 | F-08 | W | walk-6 F-05, `279381b5` | Confirmed fixed |
| R7-09 | F-09 | W | R6-23, R4-20 | Slot-role model vs client placement |
| R7-10 | F-10 | W | — | New; blocking corrected (C3) |
| R7-11 | F-11 | W | R6-11, `417315d6` | Confirmed fixed |
| R7-12 | F-12 | W | R6-23, `569f47e3` | Confirmed fixed |
| R7-13 | F-13 | W | walk-6 F-09 | Wording fixed, substance new |
| R7-14 | F-14 | W | R4-04, R6-01 | Skipped target as `Missing` |
| R7-15 | F-15 | W | round-6 §13.5 | Known limitation (72-convention) |
| R7-16 | F-16 | W | walk-6 F-09 | Confirmed fixed (verification-only) |
| R7-17 | F-17 | W | R6-13 | Position before any turn |
| R7-18 | F-18 | **both** | `KI-122`, R6-14, snapshot §7.7 item 10 | Ruled out on both doors |
| R7-19 | F-19 | **both** | R6-04, R6-52, snapshot §7.7 item 12 | Recurrence at a new line |
| R7-20 | F-20 | W | R6-13 | **Falsifies R6-13** (C2) |
| R7-21 | F-21 | W | R5-11, R2-11 | `Undo turn 1` before any turn |
| R7-22 | F-22 | **both** | R4-07, R6-51, snapshot §7.7 item 9 | R6-51 closed R4-07 as `race_entries`-derived; the derivation is the defect |
| R7-23 | F-23 | W | R6-43, `530d62b9` | Known limitation — the override |
| R7-24 | F-24 | **both** | R6-48, snapshot §7.7 item 11 | **Does not reproduce** — two points in time |
| R7-25 | F-25 | W | R4-07, R6-51 | Same root as R7-22 |
| R7-26 | F-26 | **both** | R4-10, R5-16, snapshot §7.3 S07 | Predicate read; both doors |
| R7-27 | F-27 | **both** | R5-11, snapshot §7.3 S07 | Four derivations measured |
| R7-28 | F-28 | W | R6-03 | Off-calendar defaults |
| R7-29 | F-29 | W | R5-30 | Reproduces, now documented on the control |
| R7-30 | F-30 | W | R6-15, `847e4cdb` | Confirmed fixed |
| R7-31 | F-31 | **both** | R6-29, snapshot §7.7 item 20 | Events primary enabled |
| R7-32 | F-32 | W | R6-13, R6-12 | Turn convention split |
| R7-33 | F-33 | W | — | Letter not rendered |
| R7-34 | F-34 | W | — | Walk artifact plus R7-33's root |
| R7-35 | F-35 | **both** | R6-35, snapshot §7.3 S10 | Inheritance primary enabled |
| R7-36 | F-36 | W | walk-6 F-05, S1.3 | Spark fields absent from the rebuilt Legacy step |
| R7-37 | F-37 | W | R6-23, R4-20 | Scenario-link badge disagreement |
| R7-38 | F-38 | **both** | R5-23, snapshot §7.7 item 7 | Result nav gate |
| R7-39…R7-62 | E-01…E-24 | W | R6 E-rows where overlapping | Each row stands alone |
| R7-63…R7-81 | U-01…U-19 | W | R6-32…R6-54 equivalents | Per row above |
| R7-82 | dropped | W | R6-51 adjacent | New symptom of the same derivation |
| R7-83…R7-98 | snapshot | S | — (R7-94 = R4-20) | Snapshot-door-only set plus the dropped row |
| R7-99 | snapshot §7.3 S04 | S | R7-16 (the wizard's read-back, which does name both) | S-only: the wizard's preflight is a different contract screen and its read-back names scenario and trainee; the snapshot review's does not |

**Unattributed walk-7 findings: zero.** Every `R7-01`…`R7-81` row appears above.

---

## §6 Dated corrections

No walk and no prior register is edited. Each is a dated note in this section.

### C1 — Walk 7's F-02 cannot close as `Confirmed fixed`; it is `Source conflict / open`

Walk 7 §7.3 S03 records the picker as `2 forms`; §7.3 S04 records the profile as three, including
`Yummy Dreamy Fairy`. A `db read` at `8a3cf21c` confirms **three** `character_cards` rows for `umamusume_id = 30`:
id 31 `[Rosy Dreams]`, id 47 `[Vampire Makeover!]`, id 170 `Yummy Dreamy Fairy`. Either the picker filters a card
the profile does not, or the picker's count is wrong. **Filed as `Source conflict / open` (R7-02) with the settling
read named**: `TraineeSelectController`'s `character_cards` filter against `TraineeProfileController`'s. A prior
round's "fixed" verdict is not allowed to paper over a walk that reads its own document two ways.

### C2 (= C10-R7) — R6-13's mid-career-entry finding was door-specific

Walk 6 accepted the training form's default `Turn = 1`. Walk 7 typed `67` at the same field; it accepted
(`aria-invalid="false"`, no `max`, primary relabelled `Record turn 67`) and the cockpit rendered
`Senior Year · Early October · Turn 67 of 72`. R6-13's remaining substance is not "no mid-career entry" but **"the
wizard's form opens at `Turn = 1` and labels its primary `Record turn 1`"** — a default-value defect (R7-20), not a
capability limitation. **R6-13's row is not edited.**

### C3 — Blocking set corrected from three-plus-F-10 to three

Walk 7 states three blocking: `F-10`, `F-20`, `F-26`. This register reads **`R7-20`, `R7-26`, `R7-82`**. `R7-10`
(`F-10`) is `Known limitation (discoverability)`: the `Level` input accepts `1–50` and is enterable, and typing the
value enables the primary, so nothing is prevented. The hidden requirement is the defect, not the action. The count
stays three; the membership changes.

### C4 — `F-33` and `F-34` share a root and stay as two rows

The letter rank is entered on S06 (`S+`, `B+`, `UG`, `A`) and **not rendered** on S15; the `★★★` shown instead is
driven by `Rank (stars) = 3`, walk 7's own improvisation `§7.4 I-01`. The rows stay 1:1 per §2.1: R7-33 is
`Confirmed defect` (letter discarded), R7-34 is `Walk-artifact + Confirmed defect` (the star is honest because the
value was entered). One slice fixes both.

### C5 — The Spirit Burst counter is state-dependent, not a regression

Walk 6 saw `Need 2 more bursts in 69 turns`; walk 7 canonical does not. `TeamPanelBuilder::burstCountdown()`
(`:726-760`) returns `null` twice: when the scenario does not compose `team_rank_ladder` or declare the
`spirit_bursts` widget, and when `$team['combined_bursts'] ?? null` is null. A `db read` on run #3 at `8a3cf21c`
returns `spirit_bursts_count = NULL`, `extreme_bursts_count = NULL`, `team = null`. The counter correctly abstained.
**`Known limitation` — the widget is state-dependent.** No regression label; `49a90c3a` did not remove it.

### C6 — The seven probe-`No` answers are client-only concepts, not rows

Walk 7 Appendix A answers `No` to `P-36` (spark vocabulary), `P-39` (skill-provider field), `P-42` (`Obtained`
wording), `P-44` (`Sparks`/`Agenda`/`Log` destinations), `P-47` (skill-type grouping), `P-49` (row tint) and `P-53`
(which tree shape). None has a matching `U-` row, and none becomes an `R7-NN` row: **each names a client-only
concept the wizard path does not exercise** — the wizard never renders a skill row, a Perks panel, or an in-career
Sparks screen. The absences they imply are already carried by R7-68, R7-78, R7-79 and R7-80. **Disposition per
probe:** `P-36` → R7-68; `P-39`/`P-42`/`P-47`/`P-49` → R7-78/R7-79; `P-44` → R7-71's family; `P-53` → R7-68's
layout facet. No probe answer is left unclassified.

### C7 — Walk 6's F-04 label half is fixed; the residual is `R7-05`

Round 6's S2.3 (`e55f5458`) changed the label from `Sum of the five scenario caps` to `Sum of the five entered
ceilings`. **The label moved; the figure did not.** `BuildTarget.vue:252` still sums `cap(stat)`, which falls back
to the published cap when no entered ceiling exists, so an empty form prints `7000` under a label that says
"entered". Recorded as R7-05; walk 6's F-04 is recorded as `label half fixed; entered-value propagation untestable
because the ceilings were left empty by design`.

### C8 — `F-34`'s star display is honest and is a walk artifact

Walk 7's `§7.4 I-01` records that `Rank (stars) = 3` is invented: the source gives letter badges only. The `★★★`
therefore renders a value the walk typed, not a value the tool fabricated. Recorded as evidence for R7-34's
classification; walk 7's improvisation log is not edited.

### C9 — `R4-13`'s "recordable through turn events" claim stays contradicted

Round 6's C9 established that `TeamRankPayload` is constructed only in tests and no trainer form writes it. Walk 7
did not reach the team editor, so it raised no copy defect there. This register cites round 6's C9 and opens no new
row.

### C10 — The snapshot-door walk carries no numbered findings register

`grep -oE '\((F|E|U)-\)'` on the snapshot-door walk returns `6 (F-)`, `1 (E-)`, `12 (U-)` — **19 unnumbered
markers**, plus a 25-item §7.7 list. The plan's assumption of 38 `S-F-nn`-keyed findings is corrected. §4.4 keys
extended rows to section plus quoted string.

### C11 — Walk 7's F-07 misfiles a stat award as a cap raise; round 6's F-06 stands

`LegacySelect.vue:810` is a stat award (`+5, +12 or +21 to the stat it names`); `:895-896` is a cap raise (`lifts
the cap … by 16`). Different mechanics, separately sourced. Round 6's `Does not reproduce` was correct and walk 7's
F-07 is **`Ruled out`** (R7-07). No re-open.

### C12 — Walk 7's F-18 and the snapshot door's URA finding are ruled out by `KI-122`

`race_catalog_slots` 405/406 carry `scenario_key = null`; only 408 carries `unity_cup`.
`docs/scenarios/09-global-race-calendar.md:105` states the three rounds are shared and only the Final forks.
`KI-122` is CLOSED — does not reproduce. **Both walks filed this as a defect; both are wrong.** `Ruled out`
(R7-18).

### C13 — The recorded-action card's "contradiction" is a temporal quote, not a conflict

Walk 7's F-24 read two Energy sentences as simultaneous. `RecordedActionCard.vue:16-20` states the card's contract:
`expected` is a quote from the decision snapshot stored when the action was taken, "so a card read three turns
later says what was advised then, not what the advisor would say now"; `NextDecision.php:150-184` reads exactly
that stored row. The refusal `This run has recorded no Energy` was the pre-commit state. **`Does not reproduce`**
(R7-24). The residual — the card does not visibly date its quote — is folded into R7-57 as an experiential facet.

### C14 — The dropped cleared-objectives observation is filed and is blocking

Walk 7's traceability matrix records that the three cleared objectives render `missed`, and no `F-` row covers it.
Mechanism: `TeamPanelBuilder.php:323-353` requires a `race_entries` row (or a qualifying fan reading) to call a goal
met; `:261` then renders `$reached => 'missed'` for a past goal. **Absence of a record becomes a negative state.**
Filed as R7-82, `Confirmed defect`, **Blocking**.

### C15 — `Ruled out` and `Walk-artifact + Confirmed defect` are sanctioned variants, not drift

The first landed version of this register used two statuses §2.2 did not define. Both are real categories and both
stay, now defined in §2.2 with their rules:

- **`Ruled out`** (R7-07, R7-18) — the observation reproduces and the claim is refuted. This is deliberately not
  `Does not reproduce`: §2.3 rule 2 reserves that for evidence refuting the **observation**, and both rows here show
  the user-visible state exactly as the walk described it. What fails is the inference. A status that names which
  half failed is worth its slot.
- **`Walk-artifact + Confirmed defect`** (R7-34) — a compound for a row whose display is honest about an invented
  value while the surrounding defect stands. The `★★★` renders the `Rank (stars) = 3` the walk typed; the letter is
  discarded elsewhere. `Confirmed defect` alone would misdescribe the star; `Does not reproduce` would misdescribe
  the letter.

**The base six are unchanged and every other row uses them.** The variants are not licence to add a seventh for
convenience: a new status needs a rule here before a row may use it.

### C16 — `S-F-14` is real and is filed as `R7-99`

The coverage review found one gap. **The gap is real and this register had missed it.** Plan v1 §5.2 listed
`S-F-14 | Snapshot review omits Scenario and Trainee from read-back`; plan v2 dropped the whole `S-F-nn` scheme when
it measured that the walk carries no IDs, and the register inherited the drop without re-reading the underlying
observation. The snapshot walk's own `§7.3 S04` records it in its "Source data with no UI home" section: the review
**does not read back Scenario or Trainee** although the copy claims `This is exactly what will be written.`

Settled at `8a3cf21c`:

- `StoreSnapshotRequest::fieldRules()` (`:47`, `:48`) marks **both** `umamusume_id` and `scenario` `required`.
- `SnapshotController::review()` (`:111-122`) passes `payload` (the whole validated body) and `fields` (built by
  `reviewFields()`, `:196`) to the Vue page. **The payload carries both identity values, so the write is correct.**
- `reviewFields()` builds one row per `field_states` key, and the state-field set is
  `CreateSnapshotRun::CORE_STATE_FIELDS` (`:41`) + `OPTIONAL_STATE_FIELDS` (`:46`) — `speed`, `stamina`, `power`,
  `guts`, `wit`, `energy`, `fans`, `skill_points`. **Neither `umamusume_id` nor `scenario` is in it.**
- `Review.vue` renders `props.fields` (`:135`) and `props.position_label` (`:128`). Nothing renders either identity.

**Filed as `R7-99`, `Confirmed defect`, `Door: S`.** S-only because the wizard's S08 preflight is a different
contract screen and its read-back does name both (R7-16 measured `TRAINEE Rice Shower`, `SCENARIO Unity Cup`).
Slice **S18's scope widens by one row**.

### C17 — The snapshot inventory is enumerated in §4.4a

C10 corrected the plan's 38-finding premise; it did not show the work. §4.4a now maps, one row per item, every
`S-F-nn` plan v1 named (sixteen), every §7.7 item the walk carries (twenty-five), and the two ranges plan v1 wrote
without item text (`S-E-01…11`, `S-U-01…11`). The finding: **all sixteen F items are covered** — fifteen by rows
that existed, the sixteenth by `R7-99` under C16. The two E/U ranges are recorded as counts rather than IDs because
**plan v1 supplied no item text and the walk carries no `S-E-nn`/`S-U-nn` markers**; the register does not invent
the missing twenty-two descriptions. Every §7.7 item maps to a row.

### C18 — The §11 self-test is a script over row starts, not an `awk` field walk

The first version's `awk` range plus `grep -cE '^\| R7-'` over-counts when a row contains an escaped pipe
(`{entered: number\|null; scenario: number}` at R7-19), because `awk`'s field indexing diverges from the visual row
count and the range boundary can land mid-table. The self-test is now a Python count keyed on the row **start**
(`^\| R7-\d{2} `) inside the `§4.1`–`§4.5` span, which is what the measured 98 is. §11 carries the working
command.

---

## §7 Verification-only closes

| Row | Settled by | Result |
| --- | --- | --- |
| R7-04 | Walk `§7.3 S05b` + `StoreBuildTargetRequest::isEmptyTarget()` + `CareerBuildTargetTest.php:120-142` | `Does not reproduce` |
| R7-08 | Walk `§7.3 S06e` cold reload + preflight `2 of two parents` | `Does not reproduce` |
| R7-11 | Walk `§7.3 S16` six levels intact | `Does not reproduce` |
| R7-12 | Walk `§7.3 S07` `Slot 6 · Friends` | `Does not reproduce` |
| R7-16 | Walk `§7.3 S08` names both | `Does not reproduce` |
| R7-30 | Walk `§7.3 S13` single caption | `Does not reproduce` |
| R7-24 | `RecordedActionCard.vue:16-20` + `NextDecision.php:150-184` | `Does not reproduce` |
| R7-07 | `LegacySelect.vue:810` vs `:895-896` + reference §1.5.2/§1.3.4 | `Ruled out` |
| R7-18 | `db read` on `race_catalog_slots` + `KI-122` | `Ruled out` |
| R7-97 | `DeckSelect.vue` collapsed-group labels | `Does not reproduce` |
| C5 Spirit Burst | `TeamPanelBuilder.php:726-760` + `db read` on run #3 (`NULL`/`NULL`/`null`) | `Known limitation`, state-dependent |
| R7-02 | `db read`: 3 `character_cards`; picker 2, profile 3 | `Source conflict / open` (C1) |

---

## §8 Proposed slice order

**No slice opens in this pass.** Order only; every entry names its rows.

**Phase 1 — blocking**

| Slice | Rows | Shape |
| --- | --- | --- |
| **S1** | R7-20 | Make the `Turn` field's default and its primary label stop hiding the run's position. (a) default to `nextTurnToPlay()`, or (b) relabel so the primary cannot be mistaken for the run's first turn. **Recommendation: (b)** — the field already accepts any position and (b) addresses the observed harm at the smallest cost |
| **S2** | R7-26 | **Owner-gated.** The escape hatch for the maiden lock is a contract, not a fix: a per-run cleared flag, an off-calendar entry that does not require Debut/Maiden, or an explicit "cannot be entered" state. Do not invent it |
| **S3** | R7-82 + R7-22 + R7-25 + R7-61 | The objectives derivation must distinguish "the run never reached records for this turn" from "the run missed it". RED: `TeamPanelBuilder` on a turn-67 run with no `race_entries` returns a past/not-recorded state for past goals, never `missed` or `current`; browser asserts S11 shows neither on the three cleared goals |

**Phase 2 — data integrity**

| Slice | Rows | Shape |
| --- | --- | --- |
| **S4** | R7-03 + R7-13 | Persist the `Form` select's value to the draft; `SetupDraft` carries `form`; Preflight reads it |
| **S5** | R7-05 | `totalCap` must equal the sum the label names, or the label must name what the figure is. `BuildTarget.vue:252` |
| **S6** | R7-19 + R7-66 | Split the label: print `floor` where the scenario floor is meant and `cap` only where a run-recorded cap exists. `Cockpit.vue:827` + `:186` |
| **S7** | R7-57's facet | Date the recorded-action quote visibly ("as advised before this turn"), so the card's two readings cannot be read as simultaneous |

**Phase 3 — representability**

| Slice | Rows | Shape |
| --- | --- | --- |
| **S8** | R7-33 + R7-34 + R7-80 | Render the entered letter rank on the Inheritance screen; keep the star display but label it as the star grade, not the badge |
| **S9** | R7-06 (+ R7-07 clarity) | Give `Rank (stars)` and `Grandparent rank (stars)` a `max` matching the spark `Stars` bound; if the copy is touched, name the two mechanics distinctly |
| **S10** | R7-31 + R7-35 | Disable `Record event` and `Record inheritance` until their required fields carry values |
| **S11** | R7-36 + R7-68 | **Largest slice.** Restore a per-ancestor spark editor on the Legacy step and wire the payload to the Inheritance screen |
| **S12** | R7-37 | One scenario-link derivation feeding both the draft deck and the run deck |

**Phase 4 — cross-screen consistency and copy**

| Slice | Rows | Shape |
| --- | --- | --- |
| **S13** | R7-27 + R7-28 + R7-32 | One `undoLabel`; off-calendar defaults derived from the run's position; one turn-prefill source |
| **S14** | R7-21 + R7-29 | `Undo turn 1` disabled with zero turns; filters enable when the pool has more than one option |
| **S15** | R7-38 + R7-62 | One Result gate, `aria-disabled` in the nav, on every screen |
| **S16** | R7-01 + R7-14 + R7-43 + R7-45 + R7-46 + R7-54 | Filtering and copy: the list keeps its filter; `Missing` → `Not entered`; `1 trainee is catalogued.`; case and separator fixes |
| **S17** | R7-02 | **Verification-only** — settle the picker-vs-profile filter question, then close or file the filter |

**Phase 5 — snapshot-door-only**

| Slice | Rows | Shape |
| --- | --- | --- |
| **S18** | R7-83 + R7-99 | Timeline joins `race_entries` into the row set; the snapshot review's read-back renders `umamusume_id` and `scenario` beside the state fields (C16) |
| **S19** | R7-84 + R7-85 + R7-86 + R7-88 + R7-89 | Skills planner diagnosis and copy |
| **S20** | R7-87 | Strip the raw predicate from the Skill cell |
| **S21** | R7-90 | Level ceiling matches the caption ladder |
| **S22** | R7-91 + R7-92 + R7-93 | Caret flips; truncation removed |

---

## §9 Owner calls and held rows

| Row | Held because |
| --- | --- |
| R7-26 (S2) | The maiden-lock escape hatch is a contract, not a fix |
| R7-17 | The cockpit's empty state is a design decision |
| R7-18 | `KI-122` closes it; the observation reproduces and the fix is refuted |
| R7-23 / R7-73 | R6-43's recorded `HFW-OVERRIDE (text: fan-ladder)` |
| R7-64 | `ADR-0004` erratum: the export is authoritative, the client's letters are the erratum |
| R7-67 | R6-37 — field exists on Run settings, off-wizard |
| R7-70 / R7-09 | Two legitimate deck models; no ruling picks one |
| R7-71 | R6-34 — the countdown object is a dead pipe; reconnecting is a design call |
| R7-72 | No field for the tile's effect; a feature, not a fix |
| R7-74 | R6-47 — roster absence; partial field coverage |
| R7-75 | R7-26's root |
| R7-76 | R6-49 — tally only |
| R7-77 | R6-50 — no fixture model |
| R7-78 / R7-79 | No wizard skills step; `ADR-0029` D1 |
| R7-94 | R4-20 — the same row |
| R7-95 / R7-96 | The copy warns; the contract is the run's six slots |
| R7-98 | No Mood field on the snapshot door; R6-37-class |
| R7-20 (S1's direction) | (a) default-to-position vs (b) relabel-only; this register recommends (b) |

**Not landable this pass:** S2 and every row above, plus the whole owner-call set round 6 §15 holds that walk 7
re-raises under the same rows.

---

## §10 Fence findings (unnumbered, one line of evidence each)

Noticed while reading the tree; no walk row covers them. None is a slice.

- **`CockpitIdentityCard.vue` is dead code.** `resources/js/pages/Career/Cockpit.vue:358` records that the component
  was deleted from the cockpit because no HFW draws it, yet the file exists and nothing imports it.
- **`UnityCupEditor.vue` prints `Extreme Bursts` where the run model and panel say `Extreme Spirit Bursts`.**
  `UnityCupEditor.vue:167` vs `TrainingRun.php:80` (`@property int|null $extreme_bursts_count Extreme Spirit Bursts
  earned`) and `UnityCupPanel.vue:341` (`Extreme Spirit Burst count`).
- **`Preseason wins` carries no stated ceiling.** `UnityCupEditor.vue:136` prints the label with no bound; the source
  states four rounds plus the finals.
- **The `<details>` disclosure is a place defects hide** — five instances this round: `R7-57` (the
  recorded-action quote had no dating sentence), `R7-36` (the parent spark editor), `R7-68` (four grandparent
  spark editors), `R7-24`'s residual (survives as `R7-57`), and walk-5 `E2-01`, whose identical shape round 5
  answered once (`TrainingDetail.vue`'s collapsed "Client totals this turn" summary). A closed `<details>`
  renders none of its contents, so an editor that exists, validates and posts is indistinguishable from an
  editor that was never built. That is exactly what the walk reported for `R7-36` ("no spark input of any
  kind"), and the source contradicted it at `LegacySelect.vue:555-575`. The `<details>` state is behaviour,
  not presentation: a future walk reads every collapsible surface as a first-class observation and records
  whether it was open. Dated 2026-11-08.
- **The two-source rule for tests** — three of this round's test failures asserted source text or payload
  shape rather than the rendered value: S13's `prefill_turn` (client-derived; the server never owned it),
  S13's `undo turn N` (payload max, not the rendered string), and S17's `CareerPreflightTest` (fixture built
  from `SupportCard`s against a `CharacterCard` resolver, passing because the fixture held an id the runtime
  never queries). Closing S13 took three returns, and each return tightened one assertion from "the payload
  says X" to "the rendered element says X". The rule: **payload for the number, browser for the string; a
  test that asserts neither measures neither.** Applying it found a live defect nobody had reported —
  `Timeline.vue`'s undo button carried no `disabled` while its own title said the build has no undo, the
  third instance of that control after `RaceDecision` and `TrainingDetail` (`R7-21`). Dated 2026-11-08.
- **Level and hint level share a word, adjacently, on one screen** — `R7-90` was classified `Confirmed defect`
  at `8a3cf21c` on the reading "the Learned card exposes `Level … of 6` while the hint ladder's captions show
  five". Measured: both are correct. Skill level is 1..6 per `ADR-0029` D4 and `StoreRunSkillRequest:78`
  validates `between:1,6`; the hint ladder is a five-rung caption list whose own note reads "Lv Max is a
  caption, not a fifth number". The walk conflated two quantities because `SkillPlanRow.vue` places them
  within a few lines of each other and both say "Level". Not a defect — a copy-clarity risk a future walk
  should expect. The row stays `Confirmed defect` in §4 (frozen); the ledger's `#s20-s21-s22` records the
  settling read. Dated 2026-11-08.
- **The summary line, not the tail** — a test runner's final count lives on its **summary line** and in
  its **exit code**, not in the last N lines of output. This round committed a browser figure of
  `726 passed / 0 failed` by reading `tail -3` of a 1.4-hour log whose exit code was 1; the true figure
  was `726 / 74 / 2`, found in the same session and corrected at `a31f6a83`. Playwright's tail block
  prints the pass count and the skip count and stops, so a reader who checks only the end sees a green
  line under a red run. Three guards: (a) `echo "EXIT=$?"` immediately after the command and quote that
  figure; (b) `grep -E '\b[0-9]+ (passed|failed|skipped)\b'` on the log's last summary block, never
  `tail -5`; (c) never write a figure into a durable record without at least one of those two.
  **Every future session quoting a test figure owes its exit code or its summary line.** A figure
  without one is a claim, not a measurement. Dated 2026-11-08.
- **A browser assertion quotes a stale caption.** `tests/browser/career-cockpit.spec.ts:829` asserts a string the
  cockpit no longer prints.
- **`Saved.` on `UnityCupEditor` has no `role="status"`.** The confirmation is not announced.

---

## §11 Definition of done — with self-test

Run before each commit:

The row count is a **Python script over row starts**, not an `awk` field walk: several rows contain escaped
pipes (`{entered: number\|null; scenario: number}` at R7-19), so `awk`'s field indexing diverges from the visual row
count (C18). Run from the repository root:

```bash
python3 - <<'PY'
import re
src = open('docs/audits/rice-shower-unity-cup-round7-reconciliation.md', encoding='utf-8').read()
sec = re.search(r'### 4\.1 Functional.*?(?=### 4\.5)', src, re.S).group(0)
rows = [l for l in sec.splitlines() if re.match(r'^\| R7-\d{2} ', l)]
ids = [l.split('|')[1].strip() for l in rows]
STATUS = {'Confirmed defect', 'Known limitation', 'Unrepresented source data',
          'Source conflict / open', 'Does not reproduce', 'Ruled out',
          'Walk-artifact + Confirmed defect'}
bad = [i for i, l in zip(ids, rows)
       if not any(c.strip().replace('**', '').split(' —')[0].split(' (')[0] in STATUS
                  for c in l.split('|')[1:-1])]
print('rows:', len(rows), '| unique:', len(set(ids)), '| no-status:', bad or 'none')
PY
```

Expected: `rows: 99 | unique: 99 | no-status: none`.

| Check | Command | Expected |
| --- | --- | --- |
| Row count and unique IDs | the Python block above | **99 / 99** |
| Walk-ID coverage | `awk`-free count over §4.1–§4.3 | **F 38 + E 24 + U 19 = 81**, each exactly once |
| Extended rows | §4.4 | **18** (`R7-82`…`R7-99`) |
| Status vocabulary | the `STATUS` set in the Python block | every row matches one of the seven sanctioned values |
| Placeholder scan | same block, `no-status` empty | **none** |
| Markdown fences | `python tools/md_fence_check.py <register>` | **0 invalid closing fences** |

- [x] `R7-01`…`R7-81` present, 1:1 with `F-01`…`F-38`, `E-01`…`E-24`, `U-01`…`U-19`; each walk ID appears once.
- [x] Extended rows `R7-82`…`R7-99` present, each naming its snapshot-door section or the walk's traceability matrix
      and its settling read.
- [x] Every row's status is one of the six base statuses or one of the two sanctioned variants defined in §2.2
      (C15) — whether or not every value appears; `Confirmed fixed` is zero as rows by design. No placeholder word
      appears in any status cell.
- [x] Every row carries a settling read with `file:line` at `8a3cf21c`, or a named later commit, or a `db read`
      whose query and output are quoted.
- [x] Every screenshot-rendered claim names its file.
- [x] Every game-mechanic claim cites a `UMAMUSUME_REFERENCE.md` section where one applies.
- [x] Every correction to a walk or a prior register is a dated note in §6; no walk and no round 2–6 register edited.
- [x] The cross-register mapping is complete; zero unattributed walk-7 findings.
- [x] §4.4a enumerates every `S-F-nn` plan v1 named and every §7.7 item the walk carries, with the two untexted
      E/U ranges recorded as counts rather than invented IDs (C17).
- [x] §10 fence findings are unnumbered and each carries one line of evidence.
- [x] Gates run and not run are stated; `UNMEASURED` is not implied away.
- [x] §6 C1–C18 present with dated notes: C2 carries the R6-13 falsification, C3 the blocking correction, C4 the
      F-33/F-34 shared root, C5 the Spirit Burst read, C6 the seven probe dispositions, C7 the label/figure split,
      C8 the walk artifact, C9 the R4-13 context, C10 the snapshot inventory correction, C11–C13 the three
      ruling-outs, C14 the dropped finding's mechanism, C15 the two sanctioned status variants, C16 the `S-F-14`
      coverage gap and `R7-99`, C17 the §4.4a enumeration, C18 the self-test's rewrite.
- [x] The blocking set reads `R7-20`, `R7-26`, `R7-82` — three, not three-plus-`R7-10`.
- [x] §1 names the five peer paths verbatim, both walk hashes, `migrate:status`, the devtools count, the
      `RENDERED_EVIDENCE` probe and the screenshot counts.
- [x] HEAD-at-open and HEAD-at-close are both recorded, with the statement that no read was retaken after the move.

**Counts, measured from this document:**

| Group | Rows |
| --- | --- |
| `R7-01`…`R7-38` (F) | 38 |
| `R7-39`…`R7-62` (E) | 24 |
| `R7-63`…`R7-81` (U) | 19 |
| `R7-82`…`R7-99` (extended) | 18 |
| **Total** | **99** |
| §4.5 walk-6 re-measurement (separate table) | 17 |

**Status tally across the 99, measured** (a script over the §4.1–§4.4 tables, every row classified, one row
contributes one status):

| Status | Count |
| --- | --- |
| `Confirmed defect` | **53** |
| `Known limitation` | **22** (one of them discoverability, per C3) |
| `Unrepresented source data` | **10** |
| `Does not reproduce` | **8** |
| `Source conflict / open` | **3** (`R7-02`, `R7-64`, `R7-70`) |
| `Ruled out` | **2** (`R7-07`, `R7-18`; the variant defined in C15) |
| `Walk-artifact + Confirmed defect` | **1** (`R7-34`; the variant defined in C15) |
| **Total** | **99** |

The plan's provisional expectation was "~50–55 confirmed defect, ~18–20 known limitation, ~12–15 unrepresented,
~6–8 DNR, ~4–6 source-conflict, ~6–7 confirmed fixed". Four of the six land inside their band
(`Confirmed defect` 53, `Does not reproduce` 8, `Source conflict / open` 3 one below its 4–6, and `Ruled out` +
`Walk-artifact` are not base statuses so the plan did not anticipate them). Two do not: `Known limitation` measures
22 against an expected 18–20, and `Unrepresented source data` measures 10 against an expected 12–15. `Confirmed
fixed` is **zero as rows** because the eight fixed findings are evidence in §4.5 rather than rows of their own —
exactly as the plan's §3 specifies; the shortfall there is structural, not a gap.

---

## §12 Slice landing record

**Append-only.** One row per landed slice. §4's status cells stay at `8a3cf21c`; this section records what landed after
the register's own read. It is the only part of this register a slice writes.

*Numbering note: the implementation dispatch calls this section "§14". This register ends at §11 — there is no §12 or
§13 — so the landing record is §12 here and the dispatch's number is not reproduced. Recorded rather than silently
renumbered.*

| R7 | Slice | Commit | Status | Audit | RED / GREEN |
| --- | --- | --- | --- | --- | --- |
| R7-82, R7-22, R7-25, R7-61 | S3 | `27a3d374` | `Confirmed defect` → `Confirmed fixed` | `#s3--r7-82-r7-22-r7-25-r7-61` | RED: past goal returned `'missed'`; GREEN: `'not_reached'` |
| R7-05 | S5 | `da61a04f` | `Confirmed defect` → `Confirmed fixed` | `#s5--r7-05` | RED: empty form printed floor sum under an "entered" label; GREEN: `No ceilings entered` |
| R7-26 | S2 | `4efa0876` | `Confirmed defect` → `Confirmed fixed` | `#s2--r7-26` | RED: turn-67 run reported `hasClearedMaidenLock() === false`; GREEN: `true`, turn-5 still `false` |
| — | S0 | `58bb98a7` | housekeeping | `#s0--housekeeping` | three pre-existing test reds cleared; no R7 row |
| R7-20 | S1 | `e5cdc971` | `Confirmed defect` → `Confirmed fixed` | `#s1--r7-20` | RED: no `Turn to play` label; GREEN: label + helper present |
| R7-02 | S17b | `1d6a39af` | `Source conflict / open` → `Confirmed defect (copy)` → `Confirmed fixed` | `#s17b--r7-02` | RED: no `form_count_label`; GREEN: `2 released forms` / `3 forms (2 released, 1 undated)` |
| R7-03, R7-13 | S4 | `bab1b072` | `Confirmed defect` → `Confirmed fixed` (label half; persistence half open) | `#s4--r7-03-r7-13` | RED: `formLabel()` returned null for a real `character_cards` id; GREEN: the title |
| R7-19, R7-66 | S6 | `c6ac05e7` | `Confirmed defect` → `Confirmed fixed` | `#s6--r7-19-r7-66` | RED: five stat rows labelled `cap` on a run with four null cap columns; GREEN: `floor` |
| R7-06 | S9 | `36e010c1` | `Confirmed defect` → `Confirmed fixed` | `#s9--r7-06` | RED: rank-stars input advertised `valuemax="0"`; GREEN: `max="3"` |
| R7-27, R7-28, R7-32 | S13 | `dba72d62` + `bd5c769c` | `Confirmed defect` → `Confirmed fixed` | `#s13--r7-27-r7-28-r7-32` | RED: `'-'Early` vs `'Late'`, `manual_defaults.status` missing; GREEN: one `turnDisplay` helper behind all five surfaces |
| R7-99 | S18a | `3aefce57` | `Confirmed defect` → `Confirmed fixed` | `#s18a--r7-99` | RED: review listed eight state fields and neither identity field; GREEN: trainee and scenario lead the list |
| R7-83 | S18b | `598a4282` | `Confirmed defect` → `Confirmed fixed` | `#s18b--r7-83` | RED: a race with no turn link never appeared; GREEN: it lands after the last turn |
| R7-33, R7-34, R7-80 | S8 | `d487b83e` | `Confirmed defect` → `Confirmed fixed` | `#s8--r7-33-r7-34-r7-80` | RED: `legacy.parents.0.rank_label` absent from the payload; GREEN: the letter prints beside the stars |
| R7-21 | S14 | `80c1d19e` | `Confirmed defect` → `Confirmed fixed` | `#s14--r7-21-r7-29` | RED: undo enabled while its own title says it does not exist; GREEN: `Expected: disabled / Received: enabled` on revert |
| R7-57 | S7 | `4720b877` | `Confirmed defect` → `Confirmed fixed` | `#s7--r7-57` | RED: seven preview inputs `Received: hidden`; GREEN: visible, and the advice is dated as advice |
| R7-31, R7-35 | S10 | `99d17545` | `Confirmed defect` → `Confirmed fixed` | `#s10--r7-31-r7-35` | RED: both primaries enabled with required fields empty; GREEN: both disabled with a reason |
| R7-38, R7-62 | S15 | `68015d36` | `R7-38` `Does not reproduce` on the cockpit; `R7-62` `Confirmed defect` → `Confirmed fixed` | `#s15--r7-38-r7-62` | RED: `events must not render Result as a live link: Expected SPAN, Received A` |
| R7-43, R7-45, R7-46, R7-54 | S16 | `a23fd59a` | `Confirmed defect` → `Confirmed fixed` | `#s16--r7-43-r7-45-r7-46-r7-54` | copy-only; no RED per AGENTS.md §9's change-class table |
| R7-01 | S16 | `e5e38468` | `Confirmed defect` → `Confirmed fixed` | `#s16--r7-01` | RED: URL lost the term, `Received: /career/setup/trainee`; GREEN: `/search=Rice/` and a narrowed roster |
| R7-14 | S16 | `d2c15ca8` | `Confirmed defect` → `Confirmed fixed` | `#s16--r7-14` | RED: `Your targetMissingNot entered`; GREEN: `Skipped` |
| R7-84, R7-85, R7-88, R7-89 | S19 | `3f260461` | `Confirmed defect` → `Confirmed fixed`; `R7-86` `Does not reproduce` | `#s19--r7-84-r7-85-r7-88-r7-89` | RED: `null` vs `173`; pool listed `Catalogue Only Skill` |
| R7-36, R7-68 | S11 | `cdd0083a` | `R7-36` `Does not reproduce` as stated; `R7-68` `Unrepresented` → `Confirmed fixed` | `#s11--r7-36-r7-68` | RED: `Expected: 4, Received: 0` `Add Spark` buttons |
| R7-03 persistence half | S4b | — verification-only | `Confirmed defect` → `Does not reproduce` | `#s4b` | five links read; `TraineeCardFormTest` 5 passed |

---

## §13 Close-out

Round 7 closes at `d8f75849`. §4's statuses stay frozen at `8a3cf21c`; §12 records the repairs; this
section is the honest summary of what landed and what did not.

**Landed:** twenty slices and four review returns. S1, S2, S3, S4, S5, S6, S7, S8, S9, S10, S11, S13
(four returns), S14, S15, S16 (three commits), S17, S17b, S18a, S18b, S19, S20–S22, S0, S0b. Rows moved
`Confirmed defect` → `Confirmed fixed` per §12, except where a row's status survived its slice because
the defect did not reproduce — those are named below.

**Closed by read, does not reproduce:** S4b (`R7-03`'s persistence half — five links present and
covered by `TraineeCardFormTest`); S12 (`R7-37`); `R7-86` (the string it names is not in the tree, only
in walks 1 and 3); `R7-38` on the cockpit (the nav gates correctly there; `R7-62` was the real defect);
`R7-36` as stated (the spark editors exist and always have); `R7-90` (skill level is 1..6 per `ADR-0029`
D4, and the hint ladder beside it is a different quantity); `R7-91`, `R7-92`, `R7-93` (no custom caret,
no clipping — probed on live pages).

**Disposed at close-out:** S20 fixed `R7-87` (`6af4555c`). The other four rows S20–S22 would have
carried are the not-reproducing set above. The register's §4 statuses for those four are unchanged and
stale by design — §4 is frozen, and this section is where the correction lives.

**Open facets recorded that no slice closed:**
- `R7-66`'s "caps column null on a wizard-created run" — the label is honest, the storage surface is not.
  A schema item, not a slice.
- `R7-22`'s alert half — the current-run alert path is unchanged.
- `R7-36` facet (b) / `R7-68` — the write path and the Inheritance tile now agree; the source's
  aptitude and inherited-skill vocabulary beyond kind/target/stars remains unrepresented.
- `R7-87`'s residual — seven condition keys in a typical predicate have no published client phrasing.
  They are now named as an absence rather than echoed; decoding them needs a source this repository
  does not cite.
- The `career-timeline.spec.ts` 44px case: two undersized controls, reproduced on a clean HEAD build
  and independently in S13's own report. Pre-existing, outside every slice's touched surface.

**Recurring pattern worth naming.** Five of this round's defects were built-and-hidden, not missing:
`R7-57`'s preview readings, `R7-36`'s parent spark editor, `R7-68`'s four grandparent editors — each an
editor behind a closed `<details>` rendering none of its contents. Round 5 answered the identical shape
once (the collapsed "Client totals" disclosure) and it recurred twice more. **The disclosure is where
these defects hide, and its open/closed state is behaviour, not presentation.**

**Second pattern.** Five more were tests that named behaviour they never measured: S13's `prefill_turn`,
S13's undo label, S17's preflight fixture (built from `SupportCard`s against a `CharacterCard`
resolver), and S0b's two absence assertions (a state the fixture was not in). The rule that closed them:
**the payload for the number, the browser for the string; a test that names a behaviour it does not
measure is a test that cannot fail.** Applying it caught a live defect on the cockpit's undo button that
no slice had reported.

**Gates at close-out:** full Pest **2287 passed / 3 failed**, each traced to a boundary commit before
the session base (`5bc07c6c` × 2, `49a90c3a` × 1) and none closed by this round. Browser suite
**726 passed / 74 failed / 2 skipped** across 802 cases in 1.4h. The 74 are classified in the ledger's
§7a as **contention, not product**: the same specs pass when run alone with one worker on a private
port, and the failure causes are 180s timeouts rather than wrong values. One is the known pre-existing
`career-timeline` 44px case. **No failure is in a spec this round added cases to.** An earlier version
of this paragraph reported "0 failed" — that figure was read off the log's last three lines, which omit
the failure count; it was wrong and is corrected here rather than quietly replaced.
PHPStan, Pint, typecheck and build clean. Lore **558 docs / 75 exempt** and **318 code**, against round
6's recorded 555 and 313 — deltas +3 and +5 across 27 commits, both ruled at the ledger's item 8 (an
earlier figure of 556/182 in that section was wrong: the 556 came from the `--code` flag, which
`tools/lore.php:47` does not recognise as code mode, and 182 was a baseline carried without a source).
Fence 0 invalid. Migrations 0 pending. Peer set byte-identical. The ledger's `#close-out` carries the
command-level record.

**Dated additions — verification pass, 2026-11-08.**

- The browser figure in the gate list above (`726 / 74 / 2`) is verified against the runner's **exit
  code** (`EXIT=1`) and its summary line, not against the log's tail. The figure first committed at
  `079bdb35` was `726 / 0 / 2` and was a tail-read error: Playwright's tail block prints the pass and
  skip counts and omits the failure count. Corrected at `a31f6a83`; the read rule is §10's fourth fence
  finding. **Every figure in the gate list now carries its exit code or summary-line source in the
  ledger's `#close-out` item 7**, and a figure without one is not to be quoted from this register.
- The lore figures above (`558 docs / 75 exempt`, `318 code`) are measured by
  `php tools/lore.php` and `php tools/lore.php code` — the **positional** form, which is the only code
  mode. The `--code` flag form is docs mode (`tools/lore.php:47` reads `$argv[1] === 'code'`) and was the
  source of an earlier, wrong `code: 556` baseline. Deltas against round 6's recorded 555 / 313 are
  +3 / +5; the ledger's `#close-out` item 8 carries the correction below its own earlier text.

**The register is closed.** The ledger is the durable record of what landed and what did not.
