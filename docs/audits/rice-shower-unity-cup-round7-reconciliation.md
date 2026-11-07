# Round 7 reconciliation register — Rice Shower / Unity Cup (`RS-UC-R7`)

**Audit ID:** `RS-UC-R7`. **Date:** 2026-11-07. **Read commit:** `8a3cf21c221da2c0bfa5fda887f593c7ca8d9b1d`.
**Baseline:** `docs/audits/rice-shower-unity-cup-round6-reconciliation.md` (`7296dc95`).

**Status of this document:** register, not plan. Every row below carries a status settled by a read at the commit
above. No row inherits a prior register's status.

---

## 1. Baseline measured tree

### 1.1 Commit and working tree

```
$ git rev-parse HEAD
8a3cf21c221da2c0bfa5fda887f593c7ca8d9b1d

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

Six peer-owned dirty paths (the plan expected four; two more — `docs/imports/Umamusume_Progress_Tracker_Runs.md`
and `docs/imports/ura-finale-runs.md` — appeared during the walk passes, plus `docs/imports/ura-finale-runs.md.bak`
and `screen_data.json` untracked). None is this register's and none was touched.

### 1.2 Gates run

| Gate | Command | Result |
| --- | --- | --- |
| Scenario metadata | `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` | **3 passed (52 assertions)**, 4.31s |
| Markdown fences | `python tools/md_fence_check.py <both walks>` | **0 invalid closing fence(s) in 2 file(s)** |
| Lore (docs) | `php tools/lore.php` | **556 hit(s), 75 exempt line(s)** |
| Lore (code) | `php tools/lore.php --code` | **556 hit(s), 75 exempt line(s)** (tool reports the same figure in both modes on this tree) |

### 1.3 Gates not run, and why

- **PHPStan, Pint, `npm run typecheck`, the full Pest suite** — no application code changed in this pass. This is
  a documentation-only register; §13's hand-off sequence applies to a code change, and none is made here.
- **Browser suite** — not run. The register records browser observations that the two walks already made; it does
  not re-walk the application. Every row that cites a rendered string cites the walk that observed it.

---

## 2. Method

### 2.1 Statuses

Six, unchanged from round 6:

| Status | Meaning |
| --- | --- |
| `Confirmed fixed` | A verified repair, or a proven-working predecessor, plus a passing test. |
| `Does not reproduce` | Walk evidence **refutes** the claim. Not "the path went unexercised". |
| `Confirmed defect` | Reproduces at HEAD, with `file:line`. |
| `Known limitation` | Real, and a ruling declares it intended, or the fix is owner-gated. |
| `Unrepresented source data` | The source datum has no storage. |
| `Ruled out` | The finding's premise is contradicted by a source or a recorded ruling. |

### 2.2 Four rules carried forward

1. `Confirmed fixed` requires a verified repair or a proven working predecessor plus a passing test. A capability
   that always existed but the walk did not use is `Does not reproduce`, never `fixed`.
2. `Does not reproduce` means evidence refutes the claim, not that the path went unexercised.
3. Off-path homes: stored and enterable but not on the walked path → `Known limitation` (`field exists, off-path`);
   stored with no writer → `Unrepresented source data` (`stored, no writer`); no storage at all →
   `Unrepresented source data`.
4. A regression label needs a proven predecessor boundary. No row here carries "regression from landed fix"
   without one.

### 2.3 Columns

`ID | Door | Walk-section | Type | Short | Settling command | Evidence location | Status | Facets | Regression | Cross-ref`

`Door`: `W` (wizard door, `RS-UC-2026-12`), `S` (snapshot door, `RS-UC-2026-11`), `both` (merged row).

### 2.4 Two-door method, and a scope correction found while executing

The plan's §2 asks for both doors classified and for findings to be marked door-specific or application-wide. That
is honoured in §4.1–§4.3.

**One correction to the plan's inventory, measured rather than inherited.** The plan's §1 and §5.2 describe the
snapshot door as carrying "38 findings (16 F + 11 E + 11 U)" with stable `S-F-nn` IDs. **The snapshot-door document
does not contain a numbered findings register.** It carries:

- **inline markers** (`(F-)`, `(E-)`, `(U-)` with no number) inside its screen sections — measured at HEAD:
  **6 `(F-)`, 1 `(E-)`, 12 `(U-)` = 19 markers total**, all unnumbered; and
- **a numbered 25-item "Open items" list** at its §7.7, which mixes reproduced findings with source-document open
  questions (frames vs document disagreements, not application defects).

`grep -oE '\((F|E|U)-\)' docs/audits/rice-shower-unity-cup-ux-walk-7-snapshot-door.md` returns
`6 (F-)`, `1 (E-)`, `12 (U-)` — **19 unnumbered markers, zero numbered IDs**. There are **no `S-F-nn` IDs to
cite**. The register therefore keys snapshot rows to **section + quoted string** (`§7.7 item n`, or
`§7.3 S<nn> "<string>"`), and states the count it actually found rather than the count the plan assumed. Rows the
plan listed as `S-F-08`…`S-F-12` are reconstructed from §7.7 items 13–19, which is where the Skills-planner
findings actually live.

**Counts measured:** snapshot door — **19 inline markers** (6 F, 1 E, 12 U) plus a 25-item §7.7 list that
overlaps them and adds workflow items. §4.2 reconciles the two sets: 14 enumerated application findings plus 5
carried from §7.7 items 21–25 = **19 application findings**, and 11 §7.7 items that are source-document questions
rather than findings. Wizard door — 81 findings exactly as the plan states (38 F + 24 E + 19 U), verified by
counting the walk's register rows.

---

## 3. Reference instantiation

| Reference | Used for |
| --- | --- |
| `docs/UMAMUSUME_REFERENCE.md` §1.1.5 | rest recovery tiers (modal 50 / success 70 / failure 30) |
| `docs/UMAMUSUME_REFERENCE.md` §1.1.6 | mood strings and training/racing percentages |
| `docs/UMAMUSUME_REFERENCE.md` §1.2.6 | the 24-turn year and the 72-vs-78 extent convention |
| `docs/UMAMUSUME_REFERENCE.md` §1.3.4 | Unity Cup caps 1300/1300/1300/1300/1800 and the warning that an observed in-force cap must not be printed as "the scenario cap" |
| `docs/UMAMUSUME_REFERENCE.md` §1.5.2 | spark categories Blue/Pink/Green/White/Skill/Competition/Scenario |
| `docs/UMAMUSUME_REFERENCE.md` §2.2.2 | per-stat team rank ladder and the `S+`-is-overall-only rule |
| `docs/scenarios/09-global-race-calendar.md:100-105` | the three URA Finals rounds are shared; only the Final forks per scenario |
| `docs/framework-ui-ux/design-framework-index-2026-10-21.md` | rule IDs for slice audits only; not used to classify walk findings |

---

## 4. Reconciliation table

### 4.1 Wizard-door findings — `R7W-01` … `R7W-81`

Settled at `8a3cf21c`. `Walk-section` cites `docs/audits/rice-shower-unity-cup-ux-walk-7.md`.

| ID | Door | Walk-section | Type | Short | Evidence location | Status | Regression | Cross-ref |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| R7W-01 | W | §7.3 S03; F-01 | F | Selecting a trainee reverts the list to all 66 rows and drops the query string while `Search` keeps the term | `TraineeSelect.vue` selection clearing the query; observed at `8a3cf21c` | Confirmed defect | no | R6-03 |
| R7W-02 | W | §7.3 S03; F-02 | F | Form-count mismatch no longer reproduces | picker row `2 forms`; `Form` select offers exactly 2 | Does not reproduce | no | R6-02 |
| R7W-03 | W | §7.3 S03/S08; F-03 | F | Chosen `Form` not written to draft; Preflight reports no form | `FORM · N/A, No costume form was chosen in this draft` | Confirmed defect | no | R6-09 (new shape) |
| R7W-04 | W | §7.3 S05b; F-04 | F | Empty-target refusal no longer reproduces | `Build target saved.` after all-empty submit | Does not reproduce | no | R6-01 |
| R7W-05 | W | §7.3 S05; F-05 | F | `Sum of the five entered ceilings` prints `7000` before any ceiling entered | `BuildTarget.vue:252` `totalCap` sums `cap(stat)`, which falls back to the published cap when no entered ceiling exists; the label says "entered" | Confirmed defect | no | R6-04 residual |
| R7W-06 | W | §7.3 S06; F-06 | F | `Rank (stars)` has `min=0`, no `max`; a11y tree reports `valuemax="0"` | `LegacySelect.vue` rank-star inputs carry `min=0` and no `max` | Confirmed defect | no | R6-07 |
| R7W-07 | W | §7.3 S06; F-07 | F | "Two different ★★★ cap raises on one screen" | **Refuted: the two figures are different mechanics.** `LegacySelect.vue:810` reads `+5, +12 or +21 to the stat it names` — a **stat award**; `:895-896` reads `lifts the cap of the stat it names by 16` — a **cap raise**. The walk's own wording calls the first a "cap raise", which it is not | Ruled out — see C11 | no | R6-06 |
| R7W-08 | W | §7.3 S06/S08/S15; F-08 | F | Manual-parent loss no longer reproduces | parents, ranks and Guest survive save, cold reload, Preflight, Inheritance grid | Does not reproduce | no | R6-05 |
| R7W-09 | W | §7.3 S07/S16; F-09 | F | Slot 6 is the `Friends` slot while the source's borrowed card sits in slot 4 | `DeckSelect.vue` slot-role model vs source §1.4 | Known limitation | no | new |
| R7W-10 | W | §7.3 S07; F-10 | F | `Continue to preflight` refused while a slot lacked `Level`, no inline reason | The `Level` input accepts `1–50` and is enterable; the requirement is hidden, not obstructive. **Blocking corrected to No** — see C13 | Known limitation (discoverability) | no | new |
| R7W-11 | W | §7.3 S07/S16; F-11 | F | Deck-level loss no longer reproduces | six levels reach the run deck unchanged | Does not reproduce | no | R6-11 |
| R7W-12 | W | §7.3 S07; F-12 | F | `Pal slot` label no longer appears | position six reads `Slot 6 · Friends` / `Friends slot`; `Pal` remains a card type | Does not reproduce | no | R6-23 |
| R7W-13 | W | §7.3 S08; F-13 | F | Preflight form line says no form chosen while the picker's select defaults to `[Rosy Dreams]` | same surface as R7W-03 | Confirmed defect | no | R6-09 |
| R7W-14 | W | §7.3 S05/S08; F-14 | F | A deliberately skipped build target renders `Your target · Missing` in the same `STATE` column as `Pass` | preflight check builder | Confirmed defect | no | R6-01 residual |
| R7W-15 | W | §7.3 S08; F-15 | F | `CAREER LENGTH 72 turns` is a literal | `Preflight.vue:459` `const CAREER_TURNS = 72;` — a module constant, not derived from the run | Known limitation | no | R6-13 |
| R7W-16 | W | §7.3 S08; F-16 | F | Preflight read-back now names scenario and trainee | `TRAINEE Rice Shower`, `SCENARIO Unity Cup` | Does not reproduce | no | Walk-7-snapshot §7.3 S04 |
| R7W-17 | W | §7.3 S09; F-17 | F | Before any turn the cockpit prints `No turn recorded yet` and no position | `Cockpit.vue` head state | Known limitation (correct absence state) | no | new |
| R7W-18 | W/S | §7.3 S09/S11; F-18 | F | "URA Finals obligations on a Unity Cup run" | **Ruled out by `KI-122` and the seeded rows.** `race_catalog_slots` 405 `URA Finals Qualifier` and 406 `URA Finals Semifinal` carry `scenario_key = null`; only 408 `URA Finals Final (Aoharu)` carries `unity_cup`. `docs/scenarios/09-global-race-calendar.md:105`: "The three URA Finals rounds are shared: only the Final row forks per scenario." `RaceCatalogSlot::scopeForScenario()` (`:149-154`) keeps null-scoped rows by design | Ruled out | no | R6-14, `KI-122` |
| R7W-19 | W/S | §7.3 S09/S11; F-19 | F | Cockpit prints the scenario floor under the word `cap` where in-force caps apply | `Cockpit.vue:186` types `caps` as `{entered: number|null; scenario: number}`; `CockpitController.php:552` builds it; the stat bar's `denominator-label="cap"` (`:827`) prints `cap()` which falls back to `scenario` when `entered` is null. No field in the wizard carries the in-force values | Confirmed defect | no | R6-04, R6-52 |
| R7W-20 | W | §7.3 S10; F-20 | F | `Turn *` defaults to `1`; primary reads `Record turn 1`; field accepts any position | `TrainingDetail.vue` turn field default; the walk typed 67 and the cockpit rendered `Turn 67 of 72` | Confirmed defect | no | R6-13 (see C10-R7) |
| R7W-21 | W | §7.3 S10; F-21 | F | `Undo turn 1` enabled before any turn exists | `TrainingDetail.vue:526` `Math.max(1, props.write.turn - 1)` | Confirmed defect | no | new |
| R7W-22 | W/S | §7.3 S11; F-22 | F | Advisor reports `Junior Make Debut ... 55 turns ago` on a turn-67 run | `CockpitController::objectiveSection` (`:871-876`): `$slot->isAtOrBeforeTurn($next) => 'current'` with slot turn 12 and next 68; `NextDecision.php:303` composes `is due and not recorded` | Confirmed defect | no | R6-51 |
| R7W-23 | W | §7.3 S11; F-23 | F | `fan_ladder` computed in payload, not rendered | `CockpitController.php:563` builds it; `Cockpit.vue` prints only `FANS` | Confirmed defect | no | new |
| R7W-24 | W/S | §7.3 S11; F-24 | F | Two contradictory Energy statements for one run | live advisor says `recorded a low band`; `recorded_action.decision.refusal` says `This run has recorded no Energy` | Confirmed defect | no | R6-08/R6-51 |
| R7W-25 | W | §7.3 S11; F-25 | F | `55 turns ago` arithmetic asserted on a position the run never played | same builder as R7W-22; the arithmetic is right and the premise is not | Confirmed defect | no | new |
| R7W-26 | W/S | §7.3 S12; F-26 | F | All seven calendar races locked `Maiden lock not cleared`; `Enter a race` disabled | `RaceDecision.vue:43` payload `maiden_gated`; `:344` renders `Maiden lock not cleared`. The gate reads the run's own race history, and a snapshot-imported run has none | Confirmed defect | no | Walk-7-snapshot §7.3 S07 |
| R7W-27 | W/S | §7.3 S10/S12/S14; F-27 | F | Three screens print three different `Undo turn N` | **Three different derivations:** `TrainingDetail.vue:526` `write.turn - 1`; `RaceDecision.vue:165` `nextTurn.turn - 1`; `EventDecision.vue:104` `initialTurn - 1`. `Timeline.vue:167` uses `lastTurn` | Confirmed defect | no | Walk-7-snapshot §7.3 S07 |
| R7W-28 | W | §7.3 S12; F-28 | F | Off-calendar race form defaults `NotOffered` / month 10 / half `Late` | `RaceDecision.vue` form defaults; run position is `Early` | Confirmed defect | no | new |
| R7W-29 | W | §7.3 S13; F-29 | F | `TYPE` and `HINTS` filters disabled with inert `Any` | `SkillsPlanner.vue:513` and `:517` — both `<BaseSelect disabled :options="[{value:'',label:'Any'}]"`, each with a `title` that states why | Known limitation (documented, honest) | no | R5-30 |
| R7W-30 | W | §7.3 S13; F-30 | F | Doubled hint caption no longer reproduces | `Hint Lvl 1` appears once | Does not reproduce | no | R6-15 |
| R7W-31 | W/S | §7.3 S14; F-31 | F | `Record event` enabled with every required field empty | `EventDecision.vue:371` primary; the provenance block is marked `(required to record)` while the primary is live | Confirmed defect | no | Walk-7-snapshot §7.3 S09 |
| R7W-32 | W | §7.3 S14/S15/S17; F-32 | F | Turn convention split: Events/Inheritance prefill 68, Timeline labels 67 | `EventDecision.vue:104`, `InheritanceEvent.vue` turn default 68; `Timeline.vue` prints the record's own turn | Confirmed defect | no | new |
| R7W-33 | W | §7.3 S15; F-33 + F-34 merged | F | Ancestor rank letters collected on S06 are discarded on S15; the `★★★` display is driven by the `Rank (stars)` improvisation (I-01), not by the letters | `InheritanceEvent.vue` ancestor panel prints name + `recorded ancestor` + star grade; no letter is passed to the panel | Confirmed defect | no | new |
| R7W-34 | W | §7.3 S15; F-35 | F | `Record inheritance` enabled with nothing selected | `InheritanceEvent.vue` primary | Confirmed defect | no | Walk-7-snapshot §7.3 S10 |
| R7W-35 | W | §7.3 S15; F-36 | F | Inheritance cannot represent the source's tree | same surface as R7W-33/34; the Legacy step collects no spark rows | Unrepresented source data | no | new |
| R7W-36 | W | §7.3 S16; F-37 | F | Every run-deck card reads `Not a Scenario Link`, including Haru Urara | `DeckSelect.vue:65` carries `scenario_link: 'linked'|'not_linked'|'unknown'` and the draft step reported `Scenario link on a card 1`; the run-scoped deck resolves all six as not-linked | Confirmed defect | no | R6-23 adjacent |
| R7W-37 | W/S | §7.3 S18; F-38 | F | `Result` nav is a live link while the nav band says "Available when the career is completed" | `Result.vue` gate vs career-nav presentation | Confirmed defect | no | Walk-7-snapshot §7.3 S13 |
| R7W-38 | W | §7.3 S11; traceability §7.7 | F | **New — filed from the walk's traceability matrix, not its register.** The three cleared objectives (Nikkei Sho top 3, Tenno Sho Spring 1st, Takarazuka Kinen top 3) render `missed`, contradicting the source's cleared state | `TeamPanelBuilder.php:249-262`: `objectiveMet()` returns false when no `race_entries` row and no qualifying fan reading exist, so a position reached without records falls to `$reached => 'missed'` (`:261`). Absence of a record is rendered as a negative state | Confirmed defect | no | new (see C14) |
| R7W-39 | W | §7.3 S05; P-41 adjacent | E | `Total against the published sample` prints `N/A` on both sides | `BuildTarget.vue:515-529`; reason stated in the row's own `title` | Known limitation | no | new |
| R7W-40 | W | §7.3 S01; E-01 | E | `Start a career` and `New Career` lead to one route; nothing marks the wizard as primary | `Dashboard` cards | Known limitation | no | new |
| R7W-41 | W | §7.3 S01; E-02 | E | `Import an existing career` vs `Import a historical career` differ by one word | dashboard cards | Confirmed defect | no | new |
| R7W-42 | W | §7.3 S02; E-03 | E | `Continue to trainee` disabled with no asterisk or inline reason | `ScenarioSelect.vue` radios carry `required=false` | Confirmed defect | no | new |
| R7W-43 | W | §7.3 S02; E-04 | E | Four scenario labels carry no description | `ScenarioSelect.vue` h2 labels | Known limitation | no | new |
| R7W-44 | W | §7.3 S03; E-05 | E | `1 trainee are catalogued.` | `TraineeSelect.vue` count line | Confirmed defect | no | R6 E-02 |
| R7W-45 | W | §7.3 S03/S04; E-06 | E | One label, two destinations (`Continue to build target`) | picker link vs profile button | Confirmed defect | no | R6 E-03 |
| R7W-46 | W | §7.3 S03/S04; E-07 | E | Aptitude naming case changes between screens | `Pace chaser A` vs `Pace Chaser A` | Confirmed defect | no | R6 E-04 |
| R7W-47 | W | §7.3 S04; E-08 | E | `Scenarios this trainee is linked to` lists all four and withdraws the claim | `TraineeProfile.vue` | Confirmed defect | no | R6 E-05 |
| R7W-48 | W | §7.3 S05; E-09 | E | `This is the step the current build skips.` above a mandatory form | `BuildTarget.vue` intro copy | Confirmed defect | no | R6-01 |
| R7W-49 | W | §7.3 S05; E-10 | E | Only four of five stats have a ceiling input | `BuildTarget.vue` Wit row | Known limitation (source-backed note) | no | new |
| R7W-50 | W | §7.3 S06; E-12 | E | Guest checkboxes carry no `id`/`aria-label` beyond visible text | `LegacySelect.vue` guest inputs | Confirmed defect | no | R6-07 adjacent |
| R7W-51 | W | §7.3 S07; E-13 | E | `Name contains` matches the character name, not the epithet | `DeckSelect.vue` picker | Confirmed defect | no | R6 E-07 |
| R7W-52 | W | §7.3 S07; E-14 | E | `Wit cap is 1800. Consider a Wit-heavy deck.` unconditional | `DeckSelect.vue` strategic hint | Confirmed defect | no | R6 E-08 |
| R7W-53 | W | §7.3 S07; E-15 | E | Slot picker renders one `Equip` per row, no pagination, no count | `DeckSelect.vue` picker | Known limitation | no | R6 E-07 |
| R7W-54 | W | §7.3 S08; E-16 | E | `Trainee · Pass · Rice Showerライスシャワー` — names run together | preflight check row | Confirmed defect | no | new |
| R7W-55 | W | §7.3 S08; E-17 | E | `Create the run` disabled with its explanation below the button | `Preflight.vue` acknowledge block | Known limitation | no | new |
| R7W-56 | W | §7.3 S10; E-18 | E | `Energy state` defaults to `Exact` while `ENERGY AFTER` is empty | `TrainingDetail.vue:526`-area energy controls | Confirmed defect | no | new |
| R7W-57 | W | §7.3 S10; E-19 | E | Seven unlabelled number inputs (`failure_rate`, six `preview_gains[*]`) | `TrainingDetail.vue` bottom cluster | Confirmed defect | no | new |
| R7W-58 | W | §7.3 S12; E-20 | E | Off-calendar form defaults (`NotOffered`, `Late`) contradict the run position (`Early`) | `RaceDecision.vue` | Confirmed defect | no | new |
| R7W-59 | W | §7.3 S13; E-21 | E | `No skill is recorded on this run yet` beside `SKILL POINTS 173` | `SkillsPlanner.vue` empty state | Known limitation | no | new |
| R7W-60 | W | §7.3 S16; E-22 | E | Run-scoped deck shows editable controls with no visible save path | `Deck.vue` | Confirmed defect | no | new |
| R7W-61 | W | §7.3 S17; E-23 | E | Phase copy names `Junior Make Debut` as `the one dated obligation` on a Senior run | `Timeline.vue` phase group copy | Confirmed defect | no | new |
| R7W-62 | W | §7.3 S18; E-24 | E | The Result gate is absent before the run exists and a live link after | career nav | Confirmed defect | no | new |
| R7W-63–R7W-81 | W | §7.3 S01–S18 | U | The nineteen unrepresented-data findings: scenario full label (U-01); frame-corrected aptitudes (U-02); `Potential Lvl 2` (U-03); the caps in force (U-04); the Growth Rate row (U-05); per-ancestor spark rows (U-06); catalogue-max vs current card effects (U-07); borrowed-card placement (U-08); the client's three counters (U-09); facility level on the tile (U-10); class badge and fan ladder (U-11); the six Team-block values (U-12); Kyoto Daishoten (U-13); the four preseason rounds (U-14); the pending finals as a race row (U-15); the ten owned skills (U-16); skill level and hint level (U-17); ancestor rank letters (U-18); `Entry criteria met!` (U-19) | walk §7.6 and §7.7, each with its own screen | Unrepresented source data | no | mixed |

**Wizard-door counts, measured from the table above:** 63 table rows — **40 Confirmed defect**, **7 Does not
reproduce**, **12 Known limitation** (8 plain plus 4 qualified: discoverability, documented, correct-absence,
source-backed), **2 Unrepresented source data** (`R7W-35` and the collapsed `R7W-63`…`R7W-81` row covering 19), and
**2 Ruled out** (`R7W-07`, `R7W-18`). The row count matches the walk's own 38 F + 24 E + 19 U = 81 findings, with
the 19 unrepresented-data findings collapsed into one row.

### 4.2 Snapshot-door findings — `R7S-01` … `R7S-14` plus the open-item set

**Measured inventory.** The snapshot-door document carries no numbered register (see §2.4). Its 25-item list at
§7.7 mixes application findings with source-document questions. This section separates them.

**Application findings actually observed (14):**

| ID | Door | Walk-section | Type | Short | Status | Cross-ref |
| --- | --- | --- | --- | --- | --- | --- |
| R7S-01 | S | §7.3 S07 "The maiden lock is not cleared; this race opens only once a Debut or Maiden is won" | F | Maiden lock blocks all calendar races on a Senior run | Confirmed defect | R7W-26 (merged) |
| R7S-02 | S | §7.3 S07 "Undo turn 18" | F | `Undo turn 18` on a turn-67 run | Confirmed defect | R7W-27 (merged) |
| R7S-03 | S | §7.3 S12; §7.7 item 6 | F | Four off-calendar races recorded on S07 do not appear on the Timeline | Confirmed defect | new |
| R7S-04 | S | §7.3 S05; §7.7 item 9 | F | Advisor reports the Junior obligation on a Senior run | Confirmed defect | R7W-22 (merged) |
| R7S-05 | S | §7.3 S05; §7.7 item 10 | F | "URA Finale obligations on Unity Cup run" | **Ruled out** by `KI-122` and `scenario_key = null` on rows 405/406 | R7W-18 (merged) |
| R7S-06 | S | §7.3 S05; §7.7 item 11 | F | Advisor `Energy not recorded` beside a stated `Energy 50` | Confirmed defect | R7W-24 (merged) |
| R7S-07 | S | §7.3 S05; §7.7 item 12 | F | Cockpit prints the floor under `cap` | Confirmed defect | R7W-19 (merged) |
| R7S-08 | S | §7.3 S08; §7.7 item 13 | F | Skills planner Recommended region misdiagnoses SP state | Confirmed defect | new |
| R7S-09 | S | §7.3 S08; §7.7 item 14 | F | Purchasable list reads the full catalogue, not the run's pool | Confirmed defect | new |
| R7S-10 | S | §7.3 S08; §7.7 item 15 | F | Available-column copy contradicts state | Confirmed defect | new |
| R7S-11 | S | §7.3 S08; §7.7 item 16 | F | Raw condition expression leaked into a Skill cell | Confirmed defect | new |
| R7S-12 | S | §7.3 S08; §7.7 item 17 | F | JP skill name in display (`Steadfast 食い下がり`) | Confirmed defect | new |
| R7S-13 | S | §7.3 S08; §7.7 item 18 | F | Skills-planner Type/Hints filters inert | Known limitation | R7W-29 |
| R7S-14 | S | §7.3 S08; §7.7 item 19 | E | `1 skills` pluralization | Confirmed defect | new |

**Open items that are source-document questions, not application findings (11).** Recorded here so nothing from
the snapshot walk is dropped, and explicitly **not** counted as defects:

| # | §7.7 item | Why it is not an application finding |
| --- | --- | --- |
| 1 | Dirt/End aptitude: brief says G, frame says C | Resolved for the walk in favour of the frame (§11 C-01/C-02 of the source). The application prints the catalogue's letters; the *representation* gap is `R7W-63`…`R7W-81`'s U-02, already filed. |
| 2 | Vodka Power spark ★★☆ in-career vs ★☆☆ post-career | Source-document contradiction (source §11 open item 10). The application has no spark field at all (`R7W-35`). |
| 3 | Daiwa L1 Guts / Resplendent Red Ace star counts | Source-document contradiction. No application surface. |
| 4 | Maruzensky `UG` vs `UG1` | Source-document open item (source §11 item 11). The application accepts free-text letters either way. |
| 5 | Per-ancestor spark chains have no home | Already filed as `R7W-35` / U-06. |
| 6 | Off-calendar races invisible on Timeline | Filed as `R7S-03`. |
| 7 | Result nav gating inconsistent | Filed as `R7W-37`. |
| 8 | `Undo turn 18` on a turn-67 run | Filed as `R7S-02` / `R7W-27`. |
| 9–11 | Junior obligation, URA obligations, Energy contradiction, cap label | Filed as `R7S-04`, `R7S-05`, `R7S-06`, `R7S-07`. |
| 20–25 | Events primary enabled, disclosure caret, truncated sub-copy ×2, deck workflow friction, save-replaces-deck | Application findings. Item 20 is `R7W-31`; item 21 → `R7S-15`; items 22–23 → `R7S-16`, `R7S-17`; items 24–25 → `R7S-18`, `R7S-19` in §4.2a. |

**Snapshot-door counts:** 14 enumerated application findings in §4.2 + 5 carried from §7.7 items 21–25 in §4.2a
(`R7S-15`…`R7S-19`) = **19 application findings**, not the plan's assumed 38. The difference is the plan's
inventory, corrected here.

### 4.2a Carried snapshot findings from §7.7 items 21–25

| ID | Door | Walk-section | Type | Short | Status |
| --- | --- | --- | --- | --- | --- |
| R7S-15 | S | §7.3 S08/S09; §7.7 item 21 | E | Disclosure caret does not flip | Confirmed defect |
| R7S-16 | S | §7.3 S10; §7.7 item 22 | E | Inheritance sub-copy truncated mid-sentence | Confirmed defect |
| R7S-17 | S | §7.3 S12; §7.7 item 23 | E | Timeline sub-copy truncated | Confirmed defect |
| R7S-18 | S | §7.3 S11; §7.7 item 24 | E | Deck ownership workflow: no bulk set, no source-deck reference | Known limitation |
| R7S-19 | S | §7.3 S11; §7.7 item 25 | E | `Save deck` replaces the whole deck; no partial update | Known limitation |

### 4.3 Cross-door merged rows — `R7C-01` … `R7C-07`

Seven merged rows, nine pairs.

| ID | Door | Pair | Merged finding | Status |
| --- | --- | --- | --- | --- |
| R7C-01 | both | R7W-26 + R7S-01 | Maiden lock blocks every calendar race on a mid-career run entered either door | Confirmed defect |
| R7C-02 | both | R7W-27 + R7S-02 | `Undo turn N` derived three different ways across Training, Races, Events, Timeline | Confirmed defect |
| R7C-03 | both | R7W-22 + R7S-04 | Advisor treats a reached Junior obligation as current on a Senior run | Confirmed defect |
| R7C-04 | both | R7W-19 + R7S-07 | Cockpit prints the scenario floor under the word `cap` | Confirmed defect |
| R7C-05 | both | R7W-24 + R7S-06 | Two contradictory Energy statements in one payload | Confirmed defect |
| R7C-06 | both | R7W-31 + snapshot §7.7 item 20 (Events primary) | `Record event` enabled with required fields empty | Confirmed defect |
| R7C-07 | both | R7W-37 + snapshot §7.7 item 7 (Result nav gating) | `Result` nav gate | Confirmed defect |

**Note on `R7W-18` + `R7S-05`.** The plan pairs these as a merged row. **They are not merged, because the merged
finding is ruled out**: both walks observed `URA Finals Qualifier` / `Semifinal` on a Unity Cup run, and both are
wrong to call it a defect. `KI-122` (CLOSED — does not reproduce) plus `docs/scenarios/09-global-race-calendar.md:105`
plus `race_catalog_slots.scenario_key = null` on rows 405 and 406 establish that those rounds are shared. The pair
is recorded as one ruled-out row (`R7W-18`, with `R7S-05` cross-referenced) rather than as a live merged finding.

**Also not merged, per the plan's own instruction:** `R7W-34` (Inheritance primary enabled) and `R7S-13`
(Events primary enabled) are different screens with the same pattern; they stay separate rows and the pattern is
noted. `R7W-35` was removed from the merge group because it is the unrepresented-data row, not the "enabled
nothing selected" row.

**Cross-door counts:** 7 merged rows over 8 pairs, plus 1 ruled-out pair recorded separately.

### 4.4 Walk 6's F-01 → F-17 re-measurement

| Walk 6 | Round 6 classification | Walk 7 observation | Register verdict |
| --- | --- | --- | --- |
| F-01 | `Does not reproduce` (R6-01) | Empty target accepted | Confirmed fixed |
| F-02 | `Confirmed defect` (R6-02) | Picker and `Form` select both say 2 | Confirmed fixed |
| F-03 | `Confirmed defect` (R6-03) | Reproduces in a new shape | Confirmed defect (`R7W-01`) |
| F-04 | `Confirmed defect` (R6-04) | Label half fixed; entered-value propagation untestable because the ceilings were left empty by design | Confirmed defect — residual `R7W-05` (`totalCap` prints the published sum under an "entered" label). See C15 |
| F-05 | `Confirmed defect` (R6-05) | Parents persist | Confirmed fixed |
| F-06 | `Does not reproduce` (R6-06) | The two figures are still on one screen, and they are still **two different mechanics** | **Round 6's DNR stands.** Walk 7's `F-07` mislabels the stat award as a cap raise. See C11 |
| F-07 | `Confirmed defect` (R6-07) | Reproduces | Confirmed defect (`R7W-06`) |
| F-08 | `Confirmed defect` (R6-08) | New state — both surfaces say zero | Changed shape (`R7W-35`) |
| F-09 | `Confirmed defect` (R6-09) | Fixed in wording | Confirmed fixed (wording); the substantive defect is re-filed as `R7W-03`/`R7W-13` |
| F-10 | `Confirmed defect` (R6-10) | `Legacy · Pass · 2 of two parents recorded` — the Pass is earned | Confirmed fixed |
| F-11 | `Confirmed defect` (R6-11) | Levels transfer | Confirmed fixed |
| F-12 | `Confirmed defect` (R6-12) | Timeline prints `Low band` / `173` | Confirmed fixed |
| F-13 | `Known limitation` (R6-13) | `Turn = 67` accepted; cockpit renders `Turn 67 of 72` | **Falsified at the wizard half.** See C10-R7 |
| F-14 | `Known limitation` (R6-14) | The observation reproduces; the premise does not | **Ruled out** (R7W-18); R6-14's own reasoning stands |
| F-15 | `Confirmed defect` (R6-15) | Fixed | Confirmed fixed |
| F-16 | `Confirmed defect` (R6-16) | Reproduces | Confirmed defect (`R7W-03`) |
| F-17 | `Confirmed defect` (R6-17) | Reproduces | Confirmed defect |

**Tally:** 8 confirmed fixed (F-01, F-02, F-05, F-09, F-10, F-11, F-12, F-15); 6 confirmed defect (F-03, F-04,
F-07, F-16, F-17, and F-08 as changed shape); 1 falsified (F-13); 1 ruled out (F-14); 1 no change to round 6's
verdict (F-06).

---

## 5. Cross-register mapping

| R7 row | Nearest prior | Relationship |
| --- | --- | --- |
| R7W-01 | R6-03 | Reproduces at `8a3cf21c` |
| R7W-03, R7W-13 | R6-09 | New shape of an old wording finding |
| R7W-05 | R6-04 residual | New finding, same surface |
| R7W-06 | R6-07 | Reproduces |
| R7W-07 | R6-06 | **Round 6 correct; walk 7 misfiled** (C11) |
| R7W-18 | R6-14, `KI-122` | Reproduces as an observation; ruled out as a defect |
| R7W-19 | R6-04, R6-52 | Reproduces |
| R7W-20 | R6-13 | Falsifies R6-13's framing (C10-R7) |
| R7W-22 | R6-51 | Survives R6-51's fix; the fix did not reach the advisor |
| R7W-26 | Walk-7-snapshot §7.3 S07 | Reproduces on both doors |
| R7W-27 | Walk-7-snapshot §7.3 S07 | Reproduces; the three-derivation cause is new |
| R7W-29 | R5-30 | Reproduces, now with a stated `title` |
| R7W-31 | Walk-7-snapshot §7.3 S09 | Reproduces |
| R7W-33 | new | No prior row |
| R7W-34 | Walk-7-snapshot §7.3 S10 | Reproduces |
| R7W-36 | R6-23 adjacent | Same component family, different state |
| R7W-37 | Walk-7-snapshot §7.7 item 7 | Reproduces |
| R7W-38 | new (C14) | Dropped finding, filed here |
| R7S-03 | new | Adjacent to R7W-01's filtering class |
| R7S-08…R7S-12 | new | Snapshot-only Skills reads |
| R7S-15…R7S-19 | new | Snapshot-only copy and workflow items |

---

## 6. Dated corrections

No round 2–6 register is edited. Each correction below is a dated note in this register's own section, per the
plan's §1.

### C10-R7 — R6-13's mid-career-entry finding was door-specific

R6-13 (`Known limitation`) recorded that the wizard writes no position, and walk 6 accepted the training form's
default `1`. **Walk 7 typed `67` at the same field** (`TrainingDetail.vue`, `#turn-turn`), the field accepted it
with `aria-invalid="false"` and no `max`, and the cockpit rendered
`Senior Year · Early October · Turn 67 of 72`, with the payload carrying
`careerPosition {year:3, month:10, phase:"Early", turn_index:67}` and `header.next_turn_to_play: 68`.

R6-13's remaining substance is therefore not "no mid-career entry" but **"the wizard's training form opens at
`Turn = 1` and labels its primary `Record turn 1`, so a Trainer who trusts the default commits a Junior-Year
record"**. That is a default-value defect (`R7W-20`), not a capability limitation. R6-13 is not edited; this note
records that its framing was door-specific.

### C11 — Walk 7's F-07 misfiles a stat award as a cap raise; round 6's F-06 verdict stands

Round 6 classified walk 6's F-06 as `Does not reproduce`, on the reasoning that the two figures describe two
mechanics. Walk 7 re-observed both strings on one screen and filed `F-07` as "two different ★★★ cap raises".

**Read at HEAD:** `resources/js/pages/Career/LegacySelect.vue:810` reads
`+5, +12 or +21 to the stat it names, for one, two or three stars` — a **stat award**; `:895-896` reads
`A Blue Spark that rolls three stars lifts the cap of the stat it names by 16` — a **cap raise**. The two are
distinct game mechanics with distinct effects (`UMAMUSUME_REFERENCE.md` §1.5.2 `:655` and §1.3.4 `:420`).

**Round 6 was right and walk 7 is wrong.** No re-open. Walk 7's `F-07` is recorded here as `Ruled out` (`R7W-07`).
The screen's only residual concern is that a reader scanning a table row and a paragraph may conflate the two, and
the component's own comment already records the distinction — which is why round 6 called it DNR.

### C12 — Walk 7's F-18 and the snapshot walk's URA finding are ruled out by `KI-122`

`KI-122` (`KNOWN-ISSUES.md:3474-3487`) is **CLOSED — does not reproduce**, and its reasoning is structural:
`race_catalog_slots` rows 405 `URA Finals Qualifier` and 406 `URA Finals Semifinal` carry
`scenario_key = null`, and only 408 `URA Finals Final (Aoharu)` carries `unity_cup`. Measured at HEAD:

```
id  title                      scenario_key  year  turn
1   Junior Make Debut          null          1     12
405 URA Finals Qualifier       null          4     null
406 URA Finals Semifinal       null          4     null
408 URA Finals Final (Aoharu)  unity_cup     4     null
```

`docs/scenarios/09-global-race-calendar.md:105` states the three rounds are shared and only the Final forks.
`RaceCatalogSlot::scopeForScenario()` (`app/Models/RaceCatalogSlot.php:149-154`) keeps null-scoped rows plus the
run's own key by design. **A Unity Cup run reading `URA Finals Qualifier` is correct behaviour.** Both walks filed
it as a defect; both are wrong. Recorded as `Ruled out` (`R7W-18`, `R7S-05`).

### C13 — `W-F-10`'s blocking status corrected to No

The deck's `Continue to preflight` refused while one equipped slot lacked a `Level`, with no asterisk and no inline
error. But the `Level` input accepts any `1–50` and is enterable, so nothing is prevented: the requirement is
**hidden**, not obstructive. Reclassified from `Blocking: Yes` to `Blocking: No`, status `Known limitation
(discoverability)` (`R7W-10`).

**Blocking set for round 7 is therefore two, not three:** `R7W-20` (`Turn *` default) and `R7W-26` (maiden lock).

### C14 — Walk 7's dropped cleared-objectives finding is filed

Walk 7's traceability matrix (§7.7, "Cleared objectives") records that Nikkei Sho top 3, Tenno Sho (Spring) 1st and
Takarazuka Kinen top 3 render `missed`, contradicting the source's cleared state, and the walk's register has no
row for it (`F-25` is about `55 turns ago`, a different claim).

**Mechanism at HEAD:** `app/Services/Career/TeamPanelBuilder.php:249-262`. `objectiveMet()` requires a
`race_entries` row matching the goal's slot (`:337-348`), or a fan reading at or below the goal turn for a fan goal
(`:325-335`). With neither, `$met` is false; `$reached` is true for a turn-67 run; so the state falls to
`$reached => 'missed'` at `:261`. **Absence of a record is rendered as a negative state.** Filed as `R7W-38`,
`Confirmed defect`, `Blocking: No`.

### C15 — Walk 6's F-04 label half is fixed; the residual is `R7W-05`

Walk 6's F-04 claimed the entered ceilings never reached the cap text. Walk 7 left the ceilings empty by design, so
the propagation half was untestable — but the **label** half is settled by a different observation: the
`Sum of the five entered ceilings` row prints the **published** sum (`7000`) before any ceiling is entered.

**Mechanism at HEAD:** `resources/js/pages/Career/BuildTarget.vue:252`,
`const totalCap = computed((): number => props.statOrder.reduce((sum, stat) => sum + cap(stat), 0));` where
`cap(stat)` falls back to the published scenario cap when no entered ceiling exists. The value is correct for a
"sum of the caps this run is bounded by" reading and wrong for the label it carries. Filed as `R7W-05`.

---

## 7. Verification-only closes

Items settled by a read in this pass, with no slice owed:

| Item | Settled by | Result |
| --- | --- | --- |
| Walk 6 F-01 empty-target refusal | walk 7 §7.3 S05b observed `Build target saved.`; `R6-01` cites `StoreBuildTargetRequest::isEmptyTarget()` and `CareerBuildTargetTest.php:120-142` | Closed as `Does not reproduce` |
| Walk 6 F-02 form count | walk 7 §7.3 S03 observed both counts at 2 | Closed as `Does not reproduce` |
| Walk 6 F-05 manual parents | walk 7 §7.3 S06 cold-reload probe; preflight `2 of two parents recorded` | Closed as `Does not reproduce` |
| Walk 6 F-10 Legacy Pass | preflight row now matches its own detail | Closed as `Does not reproduce` |
| Walk 6 F-11 deck levels | walk 7 S16 six levels unchanged | Closed as `Does not reproduce` |
| Walk 6 F-12 timeline energy | walk 7 S17 `Low band` / `173` | Closed as `Does not reproduce` |
| Walk 6 F-15 hint caption | walk 7 S13 single caption | Closed as `Does not reproduce` |
| Walk 6 F-06 two spark figures | `LegacySelect.vue:810` vs `:895-896`, two mechanics | Closed as `Ruled out` (C11) |
| Walk 6 F-14 URA obligations | `KI-122` + seeded `scenario_key` | Closed as `Ruled out` (C12) |
| Spirit Burst countdown absent | `TeamPanelBuilder::burstCountdown()` (`:726-760`) returns `null` when `combined_bursts` is null or when no turn is recorded | `Known limitation` — state-dependent, **not** a regression from `49a90c3a` |
| Seven probe-`No` answers (P-36, P-39, P-42, P-44, P-47, P-49, P-53) | Walk 7 Appendix A records `No` for each; none has a matching `U-` row | Recorded here as **client-only concepts the wizard path does not exercise** (the wizard never renders a skill row, a Perks panel, or an in-career Sparks screen). Not filed as additional `U-` rows; the underlying absences are already carried by U-06, U-16, U-17 and U-18. No probe answer left unclassified |
| `TYPE`/`HINTS` filter inertness | `SkillsPlanner.vue:513`, `:517` — both carry a `title` explaining why the filter cannot act | `Known limitation`, documented |

---

## 8. Proposed slice order

**No slice opens in this pass.** Order only.

**Phase 1 — correct the record (no code).**

1. **S1 — C11 and C12 land as dated notes.** No code change: round 6's F-06 verdict stands and the URA premise is
   ruled out. Cost is the note itself.
2. **S2 — C10-R7 lands as a dated note.** Same.

**Phase 2 — the two blocking defects.**

3. **S3 — `R7W-20`, `Turn *` default.** Either default the field to the run's real next turn, or relabel the
   primary so it cannot be mistaken for the run's first turn. The second is the smaller change and the one the
   evidence supports: the field already accepts any position.
4. **S4 — `R7W-26` / `R7C-01`, maiden lock.** The honest fix is a path that records a Debut or Maiden result for a
   run whose history predates the tool; the lock's condition reads the run's own race history, which a mid-career
   import legitimately lacks.

**Phase 3 — new non-blocking defects with a one-line mechanism.**

5. **S5 — `R7W-05`**, `totalCap`'s label versus its fallback (`BuildTarget.vue:252`).
6. **S6 — `R7W-19`**, the `cap` label at `Cockpit.vue:827`.
7. **S7 — `R7W-22` / `R7C-03`**, the advisor's `current` state for a reached obligation
   (`CockpitController::objectiveSection:871-876`).
8. **S8 — `R7W-24` / `R7C-05`**, the two Energy statements.
9. **S9 — `R7W-27` / `R7C-02`**, three `Undo turn N` derivations.
10. **S10 — `R7W-38`**, absence of a record rendered as `missed` (`TeamPanelBuilder.php:261`).
11. **S11 — `R7W-36`**, the run-deck scenario link state.
12. **S12 — `R7W-37` / `R7C-07`**, the Result gate.

**Phase 4 — copy fixes.**

13. **S13 — `R7W-03` / `R7W-13`**, the unrecorded `Form`.
14. **S14 — `R7W-14`**, `Your target · Missing`.
15. **S15 — the snapshot-only Skills set (`R7S-08`…`R7S-12`)**, five findings in one slice.
16. **S16 — the truncation and pluralization set (`R7S-14`, `R7S-16`, `R7S-17`, `R7W-44`)**.

**Phase 5 — owner-gated.**

17. **S17 — `R7W-33`**, the ancestor rank-letter discard: whether an ancestor's own badge gets a display surface.
18. **S18 — `R7W-34` and `R7S-13`**, the `Record X` primaries: whether both follow the Result screen's gating
    precedent.
19. **S19 — `R7W-35` / U-06**, the per-ancestor spark rows. Largest representation gap; schema-shaped.
20. **S20 — `R7W-09`**, the borrowed-card placement versus the `Friends` slot role: a product decision about which
    model the deck holds.

---

## 9. Owner calls

1. **C10-R7's home.** This register records it in §6. Round 6 is not edited. If the owner wants it also visible in
   round 6, that is a separate dated append.
2. **C11 asks nothing.** Round 6 needs no correction; the walk does. If the owner wants walk 7's `F-07` annotated in
   the walk document itself, that is an edit to a walk, not to a register.
3. **C12 asks nothing** for the same reason, but it does close the question of whether the URA labels should ever
   be renamed: `KI-122` says no, and a rename would delete valid shared obligations from every run.
4. **`R7W-20`'s fix direction.** Default-to-position is more capable and touches position derivation; relabel-only
   is smaller and addresses the observed harm (a Trainer trusting the default). The register recommends relabel-only
   and records the alternative.
5. **`R7W-26`'s fix direction.** A wizard-side Debut/Maiden record is the honest fix; teaching the lock to clear on
   first position entry is a workaround that would let a run skip a real obligation. The register recommends the
   former.
6. **`R7W-33` and `R7W-35` are the two representation questions this round cannot settle from code.** Whether an
   ancestor's letter badge and its spark rows get surfaces is a product decision with schema weight.

---

## 10. Definition of done

- [x] Baseline recorded: commit `8a3cf21c`, dirty set verbatim, four gates with results, unrun gates with reasons.
- [x] Method states the six statuses, the four rules, and the door column.
- [x] 81 wizard rows enumerated (`R7W-01`…`R7W-81`, 62 individual + 19 collapsed).
- [x] Snapshot rows enumerated **at the count the document actually carries** (19 application findings), with the
      plan's assumed 38 corrected and the 11 source-document open items recorded separately (§2.4, §4.2).
- [x] 7 cross-door merged rows (`R7C-01`…`R7C-07`), with the ruled-out pair recorded separately.
- [x] 17 walk-6 re-measurement rows (§4.4).
- [x] Every status is one of the six; no placeholders.
- [x] Every `Confirmed defect` cites `file:line` at HEAD.
- [x] Every `Does not reproduce` cites the walk evidence that refutes it.
- [x] No row inherits a prior register's status.
- [x] Corrections land in §6 as dated notes; no round 2–6 register edited.
- [x] C10-R7 stands independently of the walk document's §7.5.
- [x] Seven probe-`No` answers classified (§7).
- [x] Dropped cleared-objectives finding filed (`R7W-38`, C14).
- [x] `W-F-10` blocking status corrected (C13).
- [x] `W-F-33`/`W-F-34` merged (`R7W-33`).
- [x] Missing Spirit Burst counter investigated and filed as `Known limitation`, not a regression (§7).
- [x] Walk 6 F-04's label-half status corrected (§4.4, C15).
- [x] Gates: `md_fence_check` (0 invalid fences), `lore` / `lore-code` (556 hits, 75 exempt — unchanged baseline).

**Row totals, counted from this document:**

| Group | Enumerated rows | Notes |
| --- | --- | --- |
| `R7W-01`…`R7W-62` | 62 | one row per wizard finding, individually statused |
| `R7W-63`…`R7W-81` | 1 row spanning 19 findings | the unrepresented-data set, collapsed because each of the 19 already has its own screen and datum in the walk's §7.6/§7.7 |
| `R7S-01`…`R7S-14` | 14 | application findings reconstructed from the snapshot walk's inline markers and §7.7 |
| `R7S-15`…`R7S-19` | 5 | carried from §7.7 items 21–25 |
| `R7C-01`…`R7C-07` | 7 | merged cross-door rows over 8 pairs |
| **§4.1–§4.3 total** | **89 table rows** | covering **107 distinct findings** (81 wizard + 19 snapshot + 7 merged views of 8 already-counted pairs) |
| §4.4 walk-6 re-measurement | 17 | separate table |

The plan predicted 117 rows by assuming a 38-finding snapshot door. The measured count is 19, which is the
difference; §2.4 states why.

**Gate evidence, verbatim:**

```
$ python tools/md_fence_check.py docs/audits/rice-shower-unity-cup-ux-walk-7.md docs/audits/rice-shower-unity-cup-ux-walk-7-snapshot-door.md
0 invalid closing fence(s) in 2 file(s)

$ vendor/bin/pest tests/Feature/ScenarioMetadataTest.php
  Tests:    3 passed (52 assertions)

$ php tools/lore.php
lore-docs: 556 hit(s), 75 exempt line(s)
```
