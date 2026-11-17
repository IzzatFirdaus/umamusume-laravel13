# Trainer Desk — Scenario-Flow Surface Roadmap (2026-11-17, session S18)

Sibling plan: `docs/plan/implementation-plan-2026-11-17-s18.md` (states **how**, per slice, with commands).
This document states **what is stale, what remains, and why**; every completed row below is excluded with the
evidence that excludes it.

Measured at **HEAD `be8893a2`** on **2026-11-17T14:00+08:00**. Nothing in this pair claims to be canonical
against the peer pairs dated the same day (`implementation-roadmap-2026-11-17.md`,
`project-roadmap-2026-11-16.md`); those were written against `248e1cbb` and are superseded where this pair
measured a different figure.

---

## 0. Measured current state (P0 probe gate)

| Dimension | Measured | Command |
| --- | --- | --- |
| HEAD | `be8893a25879acad04935a3aeb47c5c38ed84517` (`master`) | `git rev-parse HEAD` |
| Working tree | 187 entries: 36 `M`, 43 `??`, remainder staged peer work | `git status --short \| wc -l` |
| Peer uncommitted app files | `config/scenarios.php`, `resources/js/pages/Career/RunSettings.vue` | `git status --porcelain -- <paths>` |
| Migrations | **80 files, 80 Ran, 0 Pending** | `ls database/migrations/*.php \| wc -l`; `php artisan migrate:status --no-ansi` |
| Feature / unit test files | 259 / 13 | `find tests/Feature -name '*Test.php' \| wc -l` |
| Browser specs | 126 | `ls tests/browser/*.spec.ts \| wc -l` |
| Vue pages / components / layouts | 62 / 119 / 3 | `find resources/js/pages -name '*.vue' \| wc -l` |
| ADRs | 30 (`0001`–`0030`) | `ls docs/adr/0*.md \| wc -l` |
| KI register | 66 heading blocks, **21** exact `Status: OPEN`, **27** lines matching `Status: OPEN*` | `grep -cE '^#{2,4} KI-[0-9]{3}' KNOWN-ISSUES.md` |
| UI lint baseline | `count: 209`, `allowlist: 24` | `python -c "json.load(open('tools/ui-lint-baseline.json'))"` |
| Served bundle | rebuilt this session, `manifest.json` 2026-11-17 13:53 | `npm run build` (970 modules, built in 19.14s) |

**Dev database** (read-only probes, `database/database.sqlite`): 20 runs (12 Active / 8 Completed), 19
`turn_entries`, 8 `veterans`, 559 support cards, 263 character cards (263 with `base_stats` **and**
`growth_rates`), 1910 skills, 304 `match_candidates` — **all 304 `source_key = gametora-races`, all
`Pending`, all `match_tier = NULL`**, 133 `data_sources` rows across a **single** `source_key`
(`gametora-characters`, newest `2026-11-05 15:06:11`). Run `#36` is a Unity Cup snapshot at turn 67 with 6
deck slots and **0** `run_skills`.

**Staleness law applied.** Every finding below was carried through four layers before it was filed: **L1**
schema (`database/migrations`), **L2** writer (`app/Http/Controllers`, `app/Services`, `app/Actions`), **L3**
render (route → page → shared child, `grep` over all of `resources/js`), **L4** served asset
(`public/build`, case-insensitive `grep`). A finding whose value was already correct at any layer is recorded
`ALREADY-FIXED` and is not scheduled.

---

## 1. Completed work — excluded, with evidence

Do not re-open.

| Slice | Evidence that excludes it |
| --- | --- |
| R8 E-01 duplicate `Skills` accessible name | `CareerSectionNav.vue:101-110` maps `skills → This run's Skills` through `:aria-label`; rendered probe of `/training-runs/36/cockpit` returns `aria="This run's Skills"` for `…/36/skills` and `None` for `/skills`. R8 S1 handoff §P0.2 already ruled ALREADY-FIXED. |
| R8 E-02 snapshot door on Dashboard | `Dashboard.vue:423-426` ships the door; measurement on `/` shows **no** `a[href="/career/snapshot"]` (rendered probe returned `[]`) — resolved by S18-06 being the *Careers list* half only (see §3). |
| R8 S1.4 schema half (`trainee_rarity`, `potential_level`) | `2026_10_10_120000_add_trainee_rarity_and_potential_to_training_runs.php:27`; `/training-runs/36/settings` renders `potential_level = 2` and a ★ selector. No migration needed. |
| R8 S1.4 config half (full scenario labels) | `config/scenarios.php` `full_label` ×4 (`:169`, `:423`, `:680`, `:1122`); pinned by `CareerScenarioSelectTest.php:344-347`. |
| R8 S1.7 per-slot card level | `deck_slots.card_level`; `SupportSlot.vue:214-222` renders the `Level` input; dev DB `deck_slots` carries the `card_level` column. ALREADY-FIXED per R8 S1 handoff §P0.7. |
| R8 S1.1 `Skills planner` jump on Cockpit | `Cockpit.vue:666` pushes `Skills planner` with `run.skills_url`. |
| R8 U-05 goal states | TERMINAL by design (states derive from recorded races); R8 §2.2. |
| `base_stats` / `growth_rates` on **catalog detail** (KI-152) | `CatalogController.php:422` ships `growth_rates`; `Catalog/Show.vue:427-437` renders both; `catalog-detail.spec.ts:288-315` asserts the recorded case. |
| Scenario matrix caps (post-rework) | `Database/Scenarios.vue` row set matches §7 config: URA 1400×5, Unity 1300×4/Wit 1800, Trackblazer 1200/1900/1200/1200/1500, OGC 1600/1300/1300/1500/1300 — verified against the rendered `/database/scenarios` table this session. |
| Burst-band table (10–12 gold, 13+) and its conflict caveat | `BurstBandsCard.vue:86-102` prints all three publisher conflicts; `unity-cup-panel.spec.ts:71` pins the payout sentence. Not a defect (guarded by rule: no settled November date is claimed). |

---

## 2. Findings register (S18) — screened through four layers

`V` = verdict. `LAYER` names the deepest layer at which the defect is live.

| ID | Surface | Observation (rendered) | L1 | L2 | L3 | L4 | V / LAYER |
| --- | --- | --- | --- | --- | --- | --- | --- |
| S18-01 | `/` resume card | Prints `ライスシャワー · Junior Year · Early January` beside `Turn 1 logged · imported at turn 67`, while the cockpit for the same run reads `Senior Year · Early October · Turn 67 of 72`. `positionLabel($strip['turn'])` derives from the **recorded-turn count**, not the run's own standing. | n/a | `DashboardController.php:184-209` computes both `position_turn` (decisionTurn) and `position_label` (recorded count) and the card prints the wrong one | `Dashboard.vue:328-333` prints `position_label` | shipped | **CONFIRMED / L3** |
| S18-02 | `/training-runs/36/cockpit` "This turn" | Prints `Senior Year, turn 20` and `7 races here`; the same page header says `Turn 67 of 72`, and `/races` + `/races/planner` both plan `Turn 19` (turn 67). `RaceStripBuilder` reads `nextTurnToPlay()` (turn 68) where the decision screens read `decisionTurn()` (stored position 67). | n/a | `app/Services/Career/RaceStripBuilder.php:85` (`nextTurnToPlay`) vs `RaceDecisionController.php:63`, `RacePlannerController.php:98` (`decisionTurn`) | `RunRaceStrip.vue:126` | shipped | **CONFIRMED / L2** |
| S18-03 | `/umamusume` + `/database/trainees` catalog card | Prints `Growth **not stored** · the catalogue holds no growth row for an` — a **truncated sentence** and a **false** claim: all 263 `character_cards` rows carry `growth_rates` since ADR-0030. The index payload simply never carries the column. | `character_cards.growth_rates` exists | `CatalogController.php:196-206` index map omits `growth_rates` | `catalog/TraineeCatalog.vue:375-379` | shipped (`TraineeCatalog.vue_…js` matched by grep) | **CONFIRMED / L3** |
| S18-04 | `/preferences` Layout width | Prints `Wide is held behind DL.14 in this build and is unavailable until that ships.` while the Wide radio renders **checked and enabled** (`input[name="settings[dashboard_layout]"][value=wide]` → `checked: true, disabled: false`) and `config('dashboard.wide_bento_enabled')` defaults **true**. The sentence is unconditional. | n/a | `config/dashboard.php:13` (`env(..., true)`) | `Preferences/Edit.vue:384` prints it without a guard | shipped (`Edit-…js`) | **CONFIRMED / L3** |
| S18-05 | `/training-runs/36/settings` lede | Prints `Everything here writes through http://127.0.0.1:8000/training-runs/36.` — an absolute loopback URL inside user-facing copy, and a stale artefact of rendering a route URL into prose. | n/a | route helper correct, the **copy** misuses it | `RunSettings.vue:62` | shipped | **CONFIRMED / L3** |
| S18-06 | `/training-runs` list | The Careers list carries `New career`, `Start from existing snapshot`, `Import a run` (measured: three doors present). R8 E-02's Careers-list half is **landed**; only the *Dashboard* half was open and it too renders (see §1). | n/a | `TrainingRunController@index` | `Runs/Index.vue` | shipped | **ALREADY-FIXED** |
| S18-07 | `/help` undo paragraph | Prints `there is no Undo last turn control` while five career screens render a **disabled** `Undo turn N` button (`Timeline.vue:217`, `TrainingDetail.vue:926`, `EventDecision.vue:419`, `RaceDecision.vue:506`) and the `DELETE runs.turns.destroy` route is live and covered by `CareerCockpitTest.php:786-868`. The claim "no control" is false; "no enabled control" is true. | n/a | `TrainingRunController@destroyTurn` exists | `Help.vue:121` | shipped (`Help-…js`); `help.spec.ts:54` pins the stale sentence | **CONFIRMED / L3** |
| S18-08 | `/review` queue | All 304 pending candidates are `gametora-races` rows with `match_tier = NULL` and `suggested_umamusume_id = NULL`; the queue prints `No catalog record carries this name.` for every row and its heading is `Is this the right trainee?`. The rows are *races*, not trainees. | `match_candidates` has no entity/kind column | `PipelineRunner.php:310` writes race rows into the trainee match queue with `match_tier` null | `Review/Index.vue:187`, `Review/Detail.vue:82` | shipped | **CONFIRMED / L1 (schema)** — owner-scope, see §5 |
| S18-09 | `/database/sources` ledger | 13 of 14 fetched datasets print `No fetch recorded`; only `gametora-characters` has a `data_sources` row (133 rows, one key). The note under every row claims the date is "this source's own newest recorded fetch", which is true, but the page as a whole reads as if 13 datasets were never fetched while the UI renders 559 cards / 1910 skills / 263 character cards from them. | `data_sources` written per promoted fact only | `PromoteMatchedRecord` writes provenance only for trainee promotion | `Database/Sources.vue:188-191` | shipped | **CONFIRMED (data coverage) / L2** — data, not copy |
| S18-10 | `/training-runs/36/skills` | `SKILL POINTS N/A`, `No skill is recorded on this run yet` — correct for a run with 0 `run_skills` rows; the snapshot door writes none. R8 U-02's "owned skills at snapshot" remains the real gap (post-create writer exists). | `run_skills` exists | `runs.skills.sync` exists | `SkillsPlanner.vue` add-skill writer exists | shipped | **CONFIRMED / L2 (missing snapshot hand-off)** |

**Findings that fell at the gate** (recorded so they are not re-filed): E-01 (already fixed), S1.7 card level
(already fixed), E-02 Dashboard half (already fixed), `base_stats` on detail (already fixed), the burst-band
table (deliberately unresolved publisher conflict, not a defect), the `Skills`/`Skills` duplicate (aria-scoped).

---

## 3. What remains — dependency graph

```
S18-01 dashboard position ─┐
S18-02 cockpit strip turn ─┼─> (verified against one shared owner: TrainingRun::decisionTurn)
S18-05 settings lede ──────┤
S18-03 catalog growth ─────┘
S18-04 preferences wide ── (independent, copy-only)
S18-07 help undo ───────── (independent, copy + spec)
        │
        └─> S18-11 full battery → S18-12 SITREP
S18-08 review queue entity ── owner-scope (schema/ADR), DEFERRED
S18-09 sources ledger ────── data coverage, DEFERRED with reason
S18-10 snapshot owned skills ─ owner-scope hand-off, DEFERRED (writer exists)
```

---

## Phase A — Stale display text (copy/paragraph slices)

**Goal.** Every rendered sentence this session measured as **false** becomes true, without a schema change.

| Slice | Finding | Deliverable | Acceptance |
| --- | --- | --- | --- |
| A1 | S18-05 | `RunSettings.vue` lede names the run's own update **action** in words, not a URL | Rendered lede carries no `http://`; feature test asserts the absence |
| A2 | S18-04 | `Preferences/Edit.vue` prints the DL.14 sentence only when `!wideBentoEnabled` | Copy present only in the flag-off state |
| A3 | S18-03 | `TraineeCatalog.vue` states the truth: growth rows are stored per card and read on the form's own page | Rendered card no longer claims absence; `catalog.spec.ts` still finds the word `Growth` |
| A4 | S18-07 | `Help.vue` says the Undo control exists but is **disabled** in this build; `help.spec.ts` assertion moved with it | Rendered sentence true; spec quotes the new sentence |

**Phase acceptance.** Four rendered sentences match the measured state; no schema touched; each slice carries a
test that fails on the old text.

---

## Phase B — Cross-surface position agreement (logic slices)

**Goal.** One owner (`TrainingRun::decisionTurn()`) answers "where is this run" on every surface.

| Slice | Finding | Deliverable | Acceptance |
| --- | --- | --- | --- |
| B1 | S18-01 | `DashboardController` derives `position_label` from the **decision** turn, keeping `position_turn` and the recorded-count `turn` as separate facts | Feature test: snapshot run at turn 67 prints `Senior Year · Early October` **and** `Turn 1 logged · imported at turn 67` |
| B2 | S18-02 | `RaceStripBuilder` reads `decisionTurn()` instead of `nextTurnToPlay()` | Feature test: snapshot run strip `this_turn.turn` equals the stored position's turn-in-year; new-career runs unchanged |

**Phase acceptance.** Both slices green in `DashboardTest` + `RunRaceStripTest`, plus the existing browser specs
untouched (`career-race-strip.spec.ts` fixtures are new-career runs).

---

## Phase C — Owner-scope and data-coverage (recorded, not scheduled)

| Item | Why it is not a slice | Where it lands |
| --- | --- | --- |
| S18-08 review queue carries race rows | Requires an entity/kind discriminator on `match_candidates` (schema) or a separate race-match queue; both are ADR-scope. Not self-rulable. | R8 register erratum + this register |
| S18-09 sources ledger coverage | `data_sources` is written per promoted **trainee** fact; making 13 datasets show a fetch date means either backfilling provenance rows or re-fetching. Both are data operations with owner approval. | recorded as data-coverage, not copy |
| S18-10 owned skills at snapshot | Writer (`runs.skills.sync`) and picker exist; the **hand-off** is a flow design decision (post-create editor vs snapshot step). | R8 U-02 stays FIX candidate |
| U-01 / U-04 / U-06 | R8 DEFERRED (per-ancestor sparks, roster/team rank, rounds-as-rows). | unchanged |

---

## 4. Verification battery (unchanged from AGENTS.md §9)

| Gate | Command |
| --- | --- |
| Targeted Pest | `php artisan test --compact --filter='<Name>'` |
| TypeScript | `npm run typecheck` |
| Style | `vendor/bin/pint.bat --test --format agent <paths>` |
| Static analysis | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` |
| Assets | `npm run build` |
| Docs fences | `python tools/md_fence_check.py <touched docs>` |
| Lore | `composer lore` + `composer lore-code` (hit count + delta, ruling per hit) |

`phpunit.xml` forces `DB_DATABASE=:memory:`: a green PHP suite says nothing about `database/database.sqlite`.
The dev file is read-only in this pair; run `#34`/`#36` are read-only.

---

## 5. Risks

1. **Peer dirty tree.** `config/scenarios.php` and `RunSettings.vue` carry uncommitted peer edits. A whole-file
   `git add` would absorb them; this pair commits nothing.
2. **`:8000` serves a rebuilt bundle.** `npm run build` ran at 13:53; any browser claim in the SITREP is dated
   against that build.
3. **Snapshot-vs-new-career divergence** is the root of both Phase B findings; a fix that reads
   `decisionTurn()` must keep new-career behaviour byte-identical (its `decisionTurn()` falls through to
   `nextTurnToPlay()`).
4. **Browser suite** is not run in this session (one process at a time, ~23 min, peer activity). Copy changes
   ship with feature tests + `npm run build` only, and the SITREP says so.

---

## 6. Progression guarantee

A1–A4 make four rendered sentences true → B1–B2 make two surfaces agree with the run's own stored position →
C records three owner-scope gaps as rulings instead of silent defects. Every slice changes a `.vue`/`.php` file
and carries a failing-first assertion; no slice is documentation alone except this pair's own SITREP chapter.

---

## 7. Erratum (2026-11-17, appended — original text above stands)

**§1 row "R8 E-02 snapshot door on Dashboard" and §2 row S18-06: the `[]` rendered probe was wrong.**

The row recorded a measurement on `/` returning no `a[href="/career/snapshot"]` and cited
`Dashboard.vue:423-426` as the link that shipped. Both halves of that sentence were off by a branch:

- The unconditional link is at **`Dashboard.vue:688`**, inside the always-rendered `quick_actions` card,
  not at `:423-426` (which is the **empty-resume** branch — it carries a second link, `:424`, so the door
  is reachable from both states).
- The `[]` probe was taken on a build where the page had not hydrated that card, or on the empty state
  only; it was not a valid absence measurement. Re-probed this session:
  `grep -n 'career.snapshot' resources/js/pages/Dashboard.vue` returns **two** hits, `:424` and `:688`.

**Consequence:** R8 E-02 is **fully closed on both surfaces**, not "dashboard half open". The exclusion in
§1 stands; the reasoning behind it is corrected here. No code change follows from this erratum.

**Second erratum — §2 row S18-07 expected a red spec that is now closed.** The handoff's §9 step 1 named
`help.spec.ts:54` as knowingly-red. It was edited this session to the shipped sentence and run
session-private; the SITREP carries the quoted result.
