# Round 8 reconciliation register — Rice Shower / Unity Cup (`RS-UC-R8`)

**Status:** register. Every row below is re-derived from the completed fresh pass recorded in
`docs/audits/ux-walkthroughs/rice-shower-unity-cup-ux-walk-9.md` (2026-11-17 addendum, sections §3b–§10b) at
HEAD `248e1cbb`. No row inherits a prior register's classification; the walk's own E/U labels are input evidence.

**Audit ID:** `RS-UC-R8`. **Date open:** 2026-11-17. **Commit at open:** `248e1cbb23a3a1c9c2bdae3ef62b7a4d74340a17`.
**Commit at close:** `248e1cbb` — HEAD moved once during the pass (peer doc-fold commits `9638ee4b`, `47c148be`,
`20dfeb38`, `e1253e94` landed before the pass began at 01:40+08:00; none touched application code; all reads below
are dated to `248e1cbb`, which is the tree the walk observed). Migrations: **83 ran, 0 pending** (`php artisan
migrate:status`), so the walk's screens read a schema-current database.

---

## §1 Baseline measured tree

### 1.1 What this round reconciles

| Item | Value |
| --- | --- |
| Source walk | `ux-walkthroughs/rice-shower-unity-cup-ux-walk-9.md` (completed rerun addendum) |
| Walk shape | 19 screens (S01–S19), snapshot door, turn 67 stored not derived, run #36 created through the UI |
| Findings carried in | E-01..E-03 (experiential), U-01..U-07 (unrepresented data), F = 0 |
| Environment events | ENV-DEV-01..07 (tab resets, one harness daemon crash) — recovery, not findings |
| Prior round | R7 (2026-11-07) reconciled walks 6/7/7-snapshot at `8a3cf21c`; its rows are closed history |

### 1.2 Gates at open

| Gate | Command | Result |
| --- | --- | --- |
| Migrations | `php artisan migrate:status --no-ansi` | 83 ran, 0 pending |
| Markdown fences | `python tools/md_fence_check.py docs/audits/ux-walkthroughs/rice-shower-unity-cup-ux-walk-9.md` | 0 invalid closing fence(s) |
| PHP suite | not run this round (docs-only round; walk changed no application file) | — |

---

## §2 Register

Every row: **what the walk observed / what the application intends (code read) / verdict / disposition**.
Verdicts: **CONFIRMED** (defect reproduced at this HEAD), **REFUTED** (walk or code shows the finding wrong),
**ALREADY-FIXED** (landed between the finding's first report and this round), **TERMINAL** (recorded decision, no code),
**DEFERRED** (owner-scope, tracked).

### 2.1 Experiential findings

| ID | Walk observation | Application state (read at `248e1cbb`) | Verdict | Disposition |
| --- | --- | --- | --- | --- |
| E-01 | Run-rail `Skills` and sidebar `Skills` carry identical accessible names; first click went to `/skills` (global library) | `Cockpit.vue:666` pushes `Skills planner` with `run.skills_url`; the sidebar link is the layout's global catalog link. Two controls, one name, same screen | **CONFIRMED** | FIX candidate (rename run-rail label or add `aria-label` "Skills planner"); small, non-blocking |
| E-02 | No in-app link to `/career/snapshot` on Dashboard or Careers list; door reachable only by URL | `routes/web.php:109` `career.snapshot` named route exists; `Entry.vue` reachable; no `grep` hit for a Dashboard/Careers `career.snapshot` reference | **CONFIRMED** | FIX candidate (one link per surface; smallest discoverability fix in the register) |
| E-03 | Settings ceiling inputs labelled `Scenario 1300` while holding the run's in-force ceilings | `RunSettings.vue` labels are static `Scenario N` strings beside override inputs; Cockpit strip disambiguates (`floor` vs `cap`) | **CONFIRMED** | FIX candidate (label copy: `Run ceiling (scenario floor N)`); copy-only, one file |

### 2.2 Unrepresented-data findings

| ID | Walk observation | Application state (read at `248e1cbb`) | Verdict | Disposition |
| --- | --- | --- | --- | --- |
| U-01 | Per-ancestor spark chains (6 ancestors, per-node star counts, Guest flags, rank badges incl. S+, two Daiwa instances with distinct sets) have no input; tool stores one legacy selection per run, stated on-screen | `2026_09_29_012615_add_legacy_selection_to_training_runs.php:35` — single `json legacy_selection` nullable column; ADR-0010 compatibility payload; no per-node table | **CONFIRMED** (structural) | **DEFERRED** to owner: schema change (per-node rows) + ADR; screen already documents its ceiling honestly |
| U-02 | The 11 owned skills have no entry path at snapshot creation or on the run; planner models planned purchases only; hint ladder matches client exactly | `routes/web.php:238` `runs.skills.sync` POST exists (no snapshot-time owned-skill editor); capability matrix confirms "add via API, edit via UI" | **CONFIRMED** | FIX candidate (post-create owned-skills editor writing through `runs.skills.sync`); medium |
| U-03 | Rarity/Potential absent from snapshot form; full scenario label never shown | Snapshot form fields enumerated in walk §4b S03; settings carries both post-create; `config/scenarios.php:416` carries the `unity_cup` block (label display is a view concern) | **CONFIRMED** | FIX candidate (add 2 fields to snapshot setup + label string in scenario config render); small |
| U-04 | Roster (18 named vs 20/20) and overall Team Rank S have no fields | Team editor has per-stat grades G–S with explicit `S+` exclusion note; screen states Team Rank records via a turn's team-race event; no roster table | **CONFIRMED** | **DEFERRED** to owner: roster is a new sub-model; Team Rank needs the turn-event path, which is a flow design decision |
| U-05 | Goal states (3 CLEAR, 1 active) and fan criteria (2,000/20,000) not enterable on the read-only goals board | Goals board renders from config + run rows; state changes require race entries (by design — recording a race is the input) | **TERMINAL** (by design: states derive from recorded races; no separate goal-state editor) | Record as application decision; revisit only if a trainer needs state without the race row |
| U-05b | (folded) fan criteria shown per race (`FANS NEEDED 2000` on the race row) but not per goal on the board | Race rows carry fan thresholds from the calendar; the goals board omits them | **CONFIRMED** (display gap) | FIX candidate (print the race's fan criterion on each goal row); small |
| U-06 | Unity Cup rounds as rows (R1–R4 WIN + dates + finals countdown) stored as a single `Preseason wins` count | `2026_10_10_150000_add_team_identity_to_training_runs.php:31` `team_preseason_wins` tinyint — count-only by design (D-220 note) | **CONFIRMED** (structural) | **DEFERRED** to owner: `preseason_rounds` per-row table + ADR, or terminal ruling that the count is the product truth |
| U-07 | Card levels (35/35, 30/30, 50/50, 35/35) and limit-break state have no input on the deck surface; panel prints `Level` label only | Deck slot panel renders `Level` + `Card effects (N)` + `Not a Scenario Link`/`Scenario Link`; no level input; no per-slot level column | **CONFIRMED** | FIX candidate (per-slot level input + persistence); medium |

### 2.3 Environmental records (no verdicts)

ENV-DEV-01..07 (tab resets, daemon crash `WinError 5` → session switch) are harness events, not application
findings. The browser-harness procedure lesson is banked in the `trainer-desk-ui-audit` skill (Hermes
`browser_exec` substrate, URL-verification per click, deep-link resume, one-session staging).

---

## §3 Deltas against prior rounds

| Prior claim | This round |
| --- | --- |
| Walk 6's "application cannot express a mid-career position" (walk-6 artifact of the wizard door) | **Superseded by construction:** the snapshot door stores turn 67, rendered `Turn 67 logged · Active`, timeline `Recorded as entered`. R7 already named the snapshot door; this walk measured it end-to-end |
| R7-era capability matrix "Team information … write path now exists" | **Reproduced:** team form accepts and persists all six fields + grades; cockpit renders them |
| R7-era "Skill purchasing / hint discounts not modelled" | **Partially superseded:** the planner now models the discount ladder exactly (130→104/91/84; 160→144/128/112/96; floor rule stated on-screen); owned-skill acquisition remains the gap (U-02) |
| Matrix "Support deck … friend slot Rented" | **Reproduced:** slot-6-is-friends + per-slot Owned/Rented persisted; walk records the position conflict (Creek listed 4th, friend slot is 6th) — both facts preserved, not smoothed |
| Walk-9 partial attempt ENV-01 (hidden embedded surface) | **Resolved by substrate change, not by application change:** Hermes CDP Chromium drove the full pass; ENV-01 retained as superseded history in the walk file |

---

## §4 Closure conditions for R8 rows

A row closes when: the FIX candidates land with their smallest change + a test that would fail without them
(E-01/E-02/E-03, U-02, U-03, U-05b, U-07), or an owner ruling records DEFERRED/TERMINAL in this register's
disposition column (U-01, U-04, U-06). No row closes on assertion.


---

## §5 Erratum — 2026-11-17, code probe at `be8893a2` (append-only; §2 rows stand as written)

Five of the ten findings above are **refuted or narrowed by a read of the tree**, not by a re-walk. The walk's
observations remain the record of what that pass saw; the dispositions below supersede their classification.
Every correction runs in one direction — **the application already did what the audit said it did not** — so the
cause is R8's own code reads, not five independent walk errors.

### 5.1 Corrections

| ID | §2 said | Correction | Evidence |
| --- | --- | --- | --- |
| E-01 | CONFIRMED — duplicate accessible name | **ALREADY-FIXED** | `resources/js/components/career/CareerSectionNav.vue:104-110` scopes the run rail's name to `This run's Skills` via `aria-label`. The rail is a shared child component; R8 read only `Cockpit.vue:666` (the "Where to go" grid) and never opened the component that renders the control |
| E-02 | CONFIRMED — no in-app link | **CONFIRMED, half fixed** | `resources/js/pages/Dashboard.vue:423-426` already ships `Import an existing career` → `/career/snapshot`. Only `resources/js/pages/Runs/Index.vue:107` lacks it |
| E-03 | CONFIRMED — label says `Scenario` | **CONFIRMED, re-scope requested** | `RunSettings.vue:312-330`: the input's accessible name is already `${stat} ceiling`; `Scenario ${ceiling.scenario}` is a caption beside it, carrying a `title` that explains an entered ceiling replaces the published figure. The caption is deliberate, not a mislabel |
| U-02 | CONFIRMED — no entry path | **CONFIRMED, narrowed** | `app/Http/Requests/StoreRunSkillRequest.php:71` requires `Rule::enum(SkillAcquisition)` (the writer sets status); `skills_url` is pushed at `Cockpit.vue:666`; `skill_levels` exists (`2026_10_25_150000`) under ADR-0029 D4. Only the snapshot-time hand-off is missing |
| U-07 | CONFIRMED — no level input, no column | **ALREADY-FIXED** | `resources/js/components/support/SupportSlot.vue:206-224` renders the `Level` input, `min=1`, `:max=levelCeiling` from the rarity ladder; `database/migrations/2026_10_25_130000_add_card_level_to_deck_slots_table.php` adds `card_level`; `StoreDeckRequest` refuses past the bound. Ships from `df624cca` (2026-10-25) |

Unchanged: U-01, U-04, U-06 **DEFERRED**; U-05 **TERMINAL**; U-03 and U-05b **CONFIRMED**.

### 5.2 Method rule adopted — the four-layer absence test

An absence claim is fileable only when all four layers come back empty. R8's U-07 failed layer 3 and E-01 failed
layer 3; both were filed as absences anyway.

| Layer | Probe |
| --- | --- |
| L1 schema | `grep -rn "<field>" database/migrations` |
| L2 writer | `grep -rn "<field>" app/Http/Requests app/Actions app/Services` |
| L3 render | route → page → **shared child** chain; grep all of `resources/js`, never only the page file |
| L4 served asset | `grep -ri "<string>" public/build` (lower case — the build writes lower-case literals) |

**L4 discriminates a stale bundle from a fixed source.** Measured this pass: `grep -ri "This run's Skills"
public/build` returns one hit in `CareerLayout.vue_vue_type_script_setup_true_lang-BFHDx2Re.js`, so the served
asset carries the fix and the walk's reproduction is not a stale-bundle artefact.

### 5.3 E-01's residual, stated rather than buried

The **accessible name** collision is fixed. Two controls still **display** the text `Skills` for different
destinations: the global sidebar (`/skills`) and the run rail (`/training-runs/{id}/skills`). The `aria-label`
fix is WCAG-correct and is the whole of E-01 as filed; the visible-label ambiguity is a low-severity owner call,
not a new defect, and it is not filed.

### 5.4 Corrected progression line

The roadmap's `S1 closes 7 of 10 rows in code` is now false. Corrected:

> **2 close ALREADY-FIXED by this erratum · 2 build now (S1.5, S1.2 remainder) · 3 build after rulings (S1.4,
> S1.6, E-03) · 1 withdraw/re-scope (E-03) · 3 DEFERRED (U-01, U-04, U-06) · 1 TERMINAL (U-05)**

Count wording for the register: **7 of 11 dispositions / 10 findings** — unchanged as a denominator; what changed
is that two rows closed with no code and two narrowed.

### 5.5 Acceptance-test correction (carried into S1.1's closure)

The plan pair's S1.1 criterion — `getByRole('link', { name: 'Skills' })` resolves to one element — **would fail on
correct code**, because Playwright matches accessible names as case-insensitive substrings by default, so
`name: 'Skills'` also matches `This run's Skills`. Any future assertion must use `{ name: 'Skills', exact: true }`
and be scoped to `/training-runs/{id}/races`, where the misroute happened.


---

## §6 Slice closures — 2026-11-17 (append-only; supersedes the disposition column for these rows only)

Both slices below closed **in code** with quoted test output. No slice closed on assertion.

### 6.1 S1.5 — U-05b, goal rows print the fan gate (CONFIRMED → **ALREADY-FIXED**)

| Item | Value |
| --- | --- |
| Change | `app/Services/Career/TeamPanelBuilder.php`: every goal row carries `fan_gate`, `fan_gate_label`, `fan_gate_title`, read from the race's own `race_catalog_slots.fans_needed` through `RaceCatalogSlot::hasFanGate()`. Rendered by `resources/js/components/scenario/cards/GoalGroupCard.vue` as `Fan gate: N fans.` with `N/A` + `title` where the slot publishes none. Mandatory-race rows carry the same three keys so the row shape does not depend on the group |
| Guard honoured | **Never derived from grade.** The reference publishes fan *ranges* per grade (§1.2.6: G1 1,000–25,000); a lookup would fabricate. One owner for the value, two readers (race card + goal row) |
| Failing-first proof | `php artisan test --compact --filter=TraineeGoalFanGate` → **3 failed (21 assertions)**, `Undefined array key "fan_gate"` at the assertion lines, before the change |
| Passing proof | `php artisan test --compact --filter=TraineeGoalFanGate` → **3 passed (28 assertions)**, 3.97s |
| Regression | `--filter=TraineeGoalsPanel` **25 passed (265 assertions)**; `--filter=CockpitObjectives` **17 passed (236)**; `--filter=ScenarioPanel` **27 passed (443)**; `--filter=UraPanel` **8 passed (140)** |
| Live confirmation | Run 36 `/training-runs/36/scenario`, 12 `[data-fan-gate]` spans: Nikkei Sho `Fan gate: 2,000 fans.`, Tenno Sho (Spring) `20,000`, Takarazuka Kinen `20,000`, Arima Kinen `25,000`; Junior Make Debut and all three URA finals rows `N/A` with the absence title. Screenshot `RS-UC-2026-11/screens/S1.5-goal-fan-gates.png` |
| Acceptance vs plan | The plan asked for 2,000 / 20,000 / 20,000 — all three observed on the rendered page |

### 6.2 S1.2 remainder — E-02, careers list links the snapshot door (CONFIRMED → **ALREADY-FIXED**)

| Item | Value |
| --- | --- |
| Change | `resources/js/pages/Runs/Index.vue`: `Start from existing snapshot` → `/career/snapshot` beside `Import a run`, label matching the destination's own H1 |
| Failing-first proof | First attempt asserted the rendered HTML in Pest and **failed for the right reason** — the Inertia shell carries no page links (`To contain: href="/training-runs/import"`), which is itself the measurement that the PHP layer cannot see a client-rendered link. The assertion was moved to the instrument that can: `tests/browser/runs.spec.ts:80-88` |
| Passing proof | `php artisan test --compact --filter=SnapshotDoorLink` → **2 passed (2 assertions)**; browser spec extended with visibility + `href` + click + URL assertion |
| Live confirmation | `/training-runs` renders three doors: `New career -> /career/setup/scenario`, `Start from existing snapshot -> /career/snapshot`, `Import a run -> /training-runs/import`. Clicking the middle one changed the URL to `http://127.0.0.1:8000/career/snapshot`. Screenshot `S1.2-careers-snapshot-door.png` |
| PRE-S02 | **Not cited.** The closure rests on this pass's own click observation |

### 6.3 Gates run for these two slices

| Gate | Result |
| --- | --- |
| `npm run typecheck` | **clean**, after 4 pre-existing duplicate `GoalGroup`/`GoalRow` interfaces were brought into agreement (`ScenarioCardGrid.vue`, `UnityCupPanel.vue`, `CareerGoalsPanel.vue`, `GoalGroupCard.vue`) |
| `php vendor/laravel/pint/builds/pint --test --format agent <3 paths>` | `{"tool":"pint","result":"passed"}` |
| `php vendor/phpstan/phpstan analyse --no-progress --memory-limit=1G app/Services/Career/TeamPanelBuilder.php` | `[OK] No errors` |
| `npm run build` | exit 0, `✓ built in 30.15s`; afterwards `public/build` contains both `Start from existing snapshot` and `data-fan-gate` |
| Stale-bundle check | **The first live probe found neither string** — the served bundle predated the edits. Rebuilt, then re-probed. This is L4 doing its job, and it is why a served-asset probe follows every UI change |

### 6.4 Corrected tally after §6

**ALREADY-FIXED 4** (E-01, U-07, and now U-05b, E-02) · **CONFIRMED 3** (E-03 re-scope, U-02 narrowed, U-03) ·
**DEFERRED 3** (U-01, U-04, U-06) · **TERMINAL 1** (U-05). 4+3+3+1 = **11 dispositions / 10 findings**.

Progression wording for the next hand-off: **7 of 11 dispositions decided and closed or ruled; 4 open — 3
awaiting owner rulings (P-1, P-2, P-5) and 1 withdraw/re-scope request (E-03).**


---

## §7 Slice closures — 2026-11-17, rulings adopted (append-only)

P-1, P-2 and P-5 were ruled as recommended. This section records the slices they unblocked, plus the
Dashboard leg that the S1.2 closure had left resting on a code probe.

### 7.1 P-1 adopted — U-03 splits into E-04 + narrowed U-03

**E-04 (experiential)** — "snapshot setup omits Rarity and Potential Level, which `RunSettings` already edits
and persists (`trainee_rarity`/`potential_level`, migration `2026_10_10_120000`)."
**U-03 (narrowed)** — "`full_label` exists (`config/scenarios.php:423`), unrendered on the setup/review path."
**U-04's Team-Rank leg** — "path exists, unexercised" (turn team-race event per the screen's own note); U-04
stays DEFERRED for the roster.

**Denominators after the split: 12 rows / 11 findings.**

### 7.2 S1.4 — closes E-04 and narrowed U-03

| Item | Value |
| --- | --- |
| Change | `StoreSnapshotRequest` gains `trainee_rarity` (`between:1,3`) and `potential_level` (`between:1,5`) — **mirrored from `StoreTrainingRunRequest:68-69`, not re-chosen**. `CreateSnapshotRun` writes both to the run's own columns. `SnapshotController::reviewFields()` reads them back as `Known · Rarity ★3` / `Known · Potential Level 2`. `scenarioFullLabel()` supplies `full_label` as its own prop, printed as a subtitle |
| Why two fields, never one | `potential_level` is the client's Potential Lvl (1-5); the Growth Rate row is `stat_bonus` percentages on the turn. Conflating them would print one number for two facts |
| Guard honoured | The short `scenarioLabel()` is untouched and still pinned by `SnapshotReviewTest` ("Config's label, never the stored key"); the full label rides beside it rather than replacing it |
| Failing-first proof | The first run of `SnapshotTraineeIdentityTest` could not load (`Cannot redeclare function snapshotPayload()` — an existing helper of that name in `CreateSnapshotRunTest`). Renamed to `identitySnapshotPayload`; the field assertions then passed against the new code. `SnapshotReviewTest` **broke on the change** — 4 failed, because two new identity rows moved the state rows from index 2 to 4 and `has('fields', 10)` became 12. Those are the correct failures a real behaviour change produces, and each assertion was re-derived rather than loosened |
| Passing proof | `--filter=SnapshotTraineeIdentity` **6 passed (33 assertions)**; `--filter=SnapshotReview` **10 passed (127 assertions)** after the index re-derivation |
| Live confirmation | `/career/snapshot/setup` renders `SELECT:Rarity` and `INPUT:Potential Level`; `/training-runs/36/settings` unchanged |

### 7.3 S1.6 — closes U-02 (copy-only, per the P-2 probe)

| Item | Value |
| --- | --- |
| P-2 probe result | The writer **requires** an explicit status (`StoreRunSkillRequest:71` `'skills.*.status' => required`), and the planner's add path already posts `status: 'Acquired'` (`SkillsPlanner.vue:255`). So there is no default-to-`planned` bug to repair: **U-02 is copy-only**, exactly the branch the P-2 ruling anticipated |
| Change | `Career/Snapshot/Review.vue` gains one hand-off line: "The trainee's owned skills are added afterwards on the run's Skills planner, which marks each one as acquired when you add it." No new editor, no new column |
| Guard honoured | Blue Rose Closer Lv3 stays in `skill_levels` (ADR-0029 D4); nothing maps a unique-skill level into the 10/20/30/35/40% hint ladder |
| Passing proof | `--filter=SnapshotOwnedSkills` **1 passed (12 assertions)**: snapshot run → 11 named skills posted → planner's `learned` group lists 11, and no row lands in `required`/`recommended` |
| Note on prop discovery | The first assertion named `recorded_skills`; the planner's real prop is `groups`, with status carried on the `learned` group's rows. Corrected by reading `SkillsPlannerController::groupRows():495` rather than guessing |

### 7.4 P-5 adopted — E-03 re-scoped, then closed

| Item | Value |
| --- | --- |
| Change | `RunSettings.vue`: caption `Scenario N` → `Scenario floor N` on the five ceiling inputs. Accessible name (`${stat} ceiling`) and the `title` sentence are unchanged; the Cockpit `floor`/`cap` strip is untouched |
| Live confirmation | `/training-runs/36/settings` prints `Scenario floor 1300` ×5 (Wit at 1800) |

### 7.5 S1.2 — the Dashboard leg, found broken by measurement and then fixed

| Item | Value |
| --- | --- |
| What the probe found | The Dashboard's snapshot link exists **only inside the `v-else-if` empty-resume branch** (`Dashboard.vue:406-434`). With 11 active careers on this database the branch never renders, so a Trainer who already has a career running — the common case — could not reach the door from the Dashboard at all. The earlier closure rested on a source read exactly as §6.2 said it did, and the source read was right about the string while wrong about the branch |
| Change | `Dashboard.vue`: unconditional `Start from existing snapshot` link inside the `quick_actions` card, which always renders |
| Failing-first proof | `SnapshotDoorLinkTest` gained the branch assertion; the pre-fix Dashboard would fail it because the only `href="/career/snapshot"` sat after the empty-branch guard |
| Passing proof | `--filter=SnapshotDoorLink` **3 passed (4 assertions)** |
| Live confirmation | With 11 active careers on the Dashboard, `Start from existing snapshot -> /career/snapshot` renders and the click navigates to `http://127.0.0.1:8000/career/snapshot`. Screenshot `S1.2b-dashboard-snapshot-door-persistent.png` |

### 7.6 Gates for this batch

| Gate | Result |
| --- | --- |
| Targeted Pest, 16 filters | **164 passed, 0 failed** across the snapshot, skills, goal, scenario and panel suites |
| `npm run typecheck` | **clean** |
| `php vendor/laravel/pint/builds/pint --test --format agent <paths>` | `{"tool":"pint","result":"passed"}` after `--fix` on the two new test files (`fully_qualified_strict_types`, `ordered_imports`) |
| `php vendor/phpstan/phpstan/phpstan analyse --no-progress --memory-limit=1G <4 app paths>` | `[OK] No errors` after adding the missing `@param array<string, mixed>` to `scenarioFullLabel()` |
| `npm run build` | exit 0, twice (once after the controller change, once after the Dashboard change) |
| Served asset | Dashboard and snapshot chunks rebuilt; the browser pass read the new copy from the served bundle |

### 7.7 Corrected tally after §7

**ALREADY-FIXED 7** (E-01, E-02, E-03, U-02, U-03, U-05b, U-07) · **DEFERRED 3** (U-01, U-04, U-06) ·
**TERMINAL 1** (U-05) = **11 dispositions / 11 findings**, with E-04 added by the P-1 split and closed in the
same batch.

Progression wording for the next hand-off: **11 of 12 disposition rows closed or ruled; 1 open row is U-04's
roster half, which is owner-scope.** Written as: **12 rows / 11 findings · closed 8 · deferred 3 · terminal 1.**


---

## §8 Second-order corrections — 2026-11-17 (append-only)

Two methodological corrections and three settled dispositions. The first corrects **this register's own
closure**, which is the more important of the two.

### 8.1 E-02: the narrowing was wrong, and the walk was right

The correction chain, stated in full because the middle step is the error:

| Step | Claim | Verdict |
| --- | --- | --- |
| 1 | Walk-9 S01/S02: no in-app link to `/career/snapshot` | **Correct.** The walk ran with career #34 already active |
| 2 | R8 §2.1: `CONFIRMED`, door reachable only by URL | **Correct** |
| 3 | SITREP P0 probe: `grep` found `href="/career/snapshot"` at `Dashboard.vue:423-426`; reclassified to "CONFIRMED, half-fixed" | **Wrong.** The string exists, but it sits inside a `v-else-if="!isCardHidden('active_career')"` branch that renders **only when no career is running** (line 406's guard follows the `v-if` at 303). With any active career the branch is not rendered, so the walk's observation was a correct reading of the live screen |
| 4 | Execution: measured the **rendered state** and found the door unreachable whenever a career is active | **Correct**, and it overturned step 3 |

**The narrowing in step 3 violated the four-layer rule this register adopted in §5.2 one turn earlier.** L3 says
"route → page → shared child chain; grep all of `resources/js`" — a grep finds a string regardless of which
branch guards it. A presence-in-file probe is not a presence-on-screen probe.

**L3 amended:**

> **L3 render**: locate the string, then establish **the state under which it renders** — the branch, guard,
> prop condition or permission that gates it — and verify the probe was taken **in the state the finding
> observed**. Presence-in-file is not presence-on-screen.

**And the bias claim in §5 was too strong.** Five of that batch's refutations ran "the app already had more than
the audit saw"; E-02's ran the **opposite** way, because a `grep` was allowed to stand for a render. The rule is
narrower and truer:

> **Presence-only probes produce false verdicts in both directions.** A missing field, a missing route and a
> missing string are three different absences, and only the third can be manufactured by a `grep` finding the
> fourth one somewhere in the file.

A second, smaller note from the same probe: the walk's own log records "first `find()` matched the sidebar's
global link" for E-01. The Playwright explanation in §5.1 is consistent with that but was not the only
candidate; the L4 asset probe settled it, and this paragraph records that the mechanism was **posited from a
log line plus a matching selector default**, not observed directly.

### 8.2 Exact-match rule for edits

Both content corruptions this session came from fuzzy or regex replacements, and both were self-caught:

| Incident | What happened | Repair |
| --- | --- | --- |
| `RunSettings.vue` `title` | A fuzzy patch matched approximately and **truncated** the `title` string mid-sentence (`…leaving the field emp`) | Second exact-match patch restored the full sentence |
| `SnapshotReviewTest.php` `fields.*` indices | A regex `re.sub` shifting `fields.N` by +2 also moved indices that were already correct, producing `fields.2` / `fields.11` mixtures and three failing assertions | Read the file, enumerated the true 12-row layout, and set each index by exact-match replacement |

> **Rule: copy strings and positional assertions are edited only by exact-match replacement. Never fuzzy, never
> regex.** A fuzzy match is allowed only where the edit is a whole-block append or a structural rewrite the
> author re-reads afterwards.

### 8.3 P-3 confirmed — the three schema questions stay DEFERRED

Owner ruling: U-01 (per-ancestor sparks), U-04 (**roster half**), U-06 (rounds-as-rows) stay DEFERRED. Each needs
a schema change plus an ADR; none gates the release bar.

**U-04's split is kept visible:** the Team-Rank leg is **"path exists, unexercised"** — the turn team-race event
path the screen's own note names — and is *not* part of the deferred row. Only the roster half is deferred.

### 8.4 E-02 follow-up: the two Dashboard doors now share one name

The unconditional link added in §7.5 sat beside the empty-resume branch's link, so the empty state would have
rendered **two doors to one destination under two names** (`Import an existing career` vs `Start from existing
snapshot`) — the same class as E-01, one level milder because the destinations agree.

Measured and unified:

| Probe | Result |
| --- | --- |
| Active-career state (the state this database is in, 11 active careers) | exactly **one** door: `Start from existing snapshot` in card `quick_actions`, visible |
| Empty-resume state | **not reachable without mutating the dev DB** (11 active careers; no destructive action authorized). Measured statically: `Dashboard.vue:423-426`, guard `v-else-if` at 406 |
| Label collisions | 1 door, 0 duplicate accessible names in the reachable state |
| Resolution | **Unified** the empty-branch label to `Start from existing snapshot`, which is the destination's own H1. `grep -rn "Import an existing career" resources/js tests` → **0 hits**. `tests/browser/dashboard.spec.ts:256` updated in the same change; that assertion is data-conditional (`if (hasCareer === 0)`) and does not run on a populated DB |
| Live confirmation | Dashboard renders `Start from existing snapshot`; `/career/snapshot` heading reads `Start from existing snapshot`; **label matches destination: true** |

### 8.5 Denominator, corrected and settled

Previous wordings mixed two denominators. The register's own rows are now the only count:

> **12 rows / 11 findings · 8 closed · 1 terminal · 3 deferred (P-3 confirmed).**

The 8 closed split by provenance: **2 erratum** (E-01, U-07 — fixed before the walk, never a code change this
session) and **6 code** (E-02, E-03, E-04, U-02, U-03, U-05b — across five changes: goal fan gates, snapshot
door, trainee identity, owned-skills hand-off, ceiling caption).

All 12 rows carry a disposition. **The register's remaining open content is exactly the three schema questions
in §8.3.**

### 8.6 The 164 headline is a filter-sum, not a suite number

`164 passed` was the sum across 16 targeted filters; a test matching two filters counts twice. The closure
number for this batch is a **single full-suite run**, recorded in §9 when it lands. Until then, the correct
statement is: *16 targeted filters, 164 filter-matches, 0 failures.*

### 8.7 KI candidates — accepted, both out of S1

| Candidate | Shape | Why deferred |
| --- | --- | --- |
| Duplicate `GoalRow`/`GoalGroup` interfaces | Four copies (`GoalGroupCard.vue`, `ScenarioCardGrid.vue`, `UnityCupPanel.vue`, `CareerGoalsPanel.vue`) that must be kept in agreement by hand; adding one field to the payload required editing all four | A type consolidation is a refactor with no behaviour change; S1's job was the fan gate. File in S2's verdict pass |
| Positional test coupling | `SnapshotReviewTest` asserts `fields.N` by index, so any added payload row costs a re-derivation (two rows cost four assertion edits this batch) | Same class: a test-shape refactor, not a defect in shipped behaviour |


---

## §9 Closure suite run — 2026-11-17 (append-only)

### 9.1 The single full-suite number

```
$ php artisan test --compact          (PID 8948, clean sweep after three stale contenders were killed)
Tests:    1 failed, 2453 passed (37521 assertions)
Duration: 465.68s
```

**This is the closure number for the batch**, replacing the §7.6 filter-sum of 164. The full run is one
process, one duration, one denominator.

### 9.2 The one failure is not this batch's, and the tree says so

| Item | Evidence |
| --- | --- |
| Failing test | `DashboardTest > it prints the imported position a snapshot stands at, not the count of turns it logged` — `activeCareer.position_label` expected `Senior Year · Early October`, received `Junior Year · Early January` |
| Passes in isolation | `--filter=DashboardTest` → **18 passed (339 assertions)**. A second run paired with each of my 8 new/edited suites → **all 16 pairings pass** |
| Second, independent failure | `RunRaceStripTest > it stands at the imported position for a snapshot` (`raceStrip.this_turn.turn` absent), which also **passes alone (18 passed)** |
| Cause: a peer's uncommitted refactor | `git diff --name-only -- app/` lists 7 files. **Four are mine**; **three are not**: `app/Http/Controllers/DashboardController.php`, `app/Models/Umamusume.php`, `app/Services/Career/RaceStripBuilder.php`. The peer diff rewrites exactly the field that fails — `'position_label' => $this->positionLabel($turn)` becomes `$this->positionLabel($position->turnIndex)`, with a new `careerPosition()` owner. Their last commit is `d330c82e`; the working-tree change is uncommitted |
| Classification | **Peer-owned, uncommitted, mid-flight.** Not a regression from S1, and not fixable here: the file belongs to another session and its refactor is unfinished. It is recorded, not touched |

**What this means for the batch's claim:** my five changes are green in isolation and in all 16 pairings
tried; the full-suite red belongs to a concurrent peer edit in three files I did not open. The honest
statement is *"2453 passed, 1 failed, and the failure is in a peer's uncommitted refactor of
`DashboardController.php`"* — never *"suite green"*.

**Measurement hazard, restated from experience:** the first attempt at this run sat 0 bytes for 254 s because
**three stale `pest` processes from the previous day** (PIDs 26188, 23408, 7860) were still alive and
contending. Killing them and re-running produced the numbers above. A contended sweep voids itself before it
starts (KI-74); the cheap pre-flight is to enumerate `php.exe` before launching.

### 9.3 Dashboard duplicate-door probe — result

| State | Doors | Names | Collision |
| --- | --- | --- | --- |
| Active career (this DB's state, 11 active) | **1** | `Start from existing snapshot` (card `quick_actions`) | none |
| Empty resume (unreachable without mutating the dev DB) | 2 by static read: `Dashboard.vue:423-426` (`v-else-if` at 406) + `:688` | both now `Start from existing snapshot` | none, after unification |

`grep -rn "Import an existing career" resources/js tests` → **0 hits**; `tests/browser/dashboard.spec.ts:256`
updated in the same change. Live: the Dashboard door's name equals the destination's own heading exactly
(`label matches destination: true`).

### 9.4 Final denominator

> **12 rows / 11 findings · 8 closed · 1 terminal · 3 deferred (P-3 confirmed).**

The 8 closed: **2 erratum** (E-01, U-07) + **6 code** (E-02, E-03, E-04, U-02, U-03, U-05b) across five
changes. All 12 rows carry a disposition. The remaining open content is exactly the three schema questions —
U-01, U-04's roster half, U-06 — each waiting on an ADR, none gating the release bar.
