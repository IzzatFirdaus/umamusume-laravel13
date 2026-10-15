# Trainee personal goals — the fullstack plan (data, ingest, surfaces, frontend coordination)

**What this file is.** The executable plan for putting each Umamusume's own career objectives into
Trainer Desk end to end: a declared fetch source, a `trainee_goals` table with per-row provenance, the
Cockpit's career-goals module, the goal marks on the live race surfaces, and the trainee's ladder on the
catalog detail page. It is the plan `AGENTS.md` §11 requires a schema change to travel with, and it is the
home of the goal slice that `KI-34` was reserved for.

**What it is not.** It is not one of the two frontend files it coordinates with.
`docs/proposals/frontend-fidelity-2026-10-13.md` and `docs/proposals/ux-laws-audit-plan-2026-10-13.md` are
both headed **"Reference only. Not authorization"** and neither is edited here. Their binding lists are
imported as constraints (§6), because the goals feature is a frontend feature and their rulings outrank
this plan's preferences.

**Owner decisions recorded 2026-10-13**, asked before the design was written and answered as a menu:

| # | Question | Ruling |
| --- | --- | --- |
| 1 | How does the ladder enter the database | **Declared fetch source** (`gametora-ura-objectives`), not a seeder body and not Trainer entry |
| 2 | How the §11 PRD bar is satisfied | **US-10 plus a drafted FR line** in `PRD.md`; the wording is in §8.1 and is the owner's to sign |
| 3 | Display reach | **Widest**: Cockpit panel, goal marks on the live race surfaces, and the trainee's ladder on catalog detail |
| 4 | What to do with codes the tool cannot read | **Store everything the source states, decode on display**; an undecoded row names itself as undecoded and never invents a string |

---

## 0. What governs this plan

`AGENTS.md` §2, in order: quality bar → gate registry → accepted ADRs → `PRD.md` → `ARCHITECTURE.md` →
`SCREEN_SPEC.md` → `DESIGN.md` → the two proposals-file "must not do" lists (§6 below) → this plan.
Three bindings decide the shape of the work:

1. **§11 Schema.** A migration travels with an updated `ARCHITECTURE-ESSENTIALS.md` digest, a PRD citation
   and a decision recorded as an ADR, with a working `down()`, verified on a scratch database.
2. **§11 Fetch pipeline.** A new source is config entry + one parser class + a test against a stored
   fixture + a robots and rate-limit note. `SourceFetcher` is the only outbound path and a URL outside
   `config('uma.sources')` is an SSRF-floor violation.
3. **§5 The Floor.** No suppression, no stub, no invented client string, no engine write to an
   `is_manual` row, no fact without provenance.

`ADR-0009:44`'s "standing instruction not to add tables" is read correctly here: it is
`ARCHITECTURE-ESSENTIALS.md:63`, and its scope is the repo #4 planner consolidation ("its
snapshots/race-predictions/dual-storage/image-uploads are cut, no new tables"). It is not a global ban —
`character_cards`, `support_cards`, `support_effects`, `race_catalog_slots`, `umamusume_profiles`,
`veterans` and `deck_slots` all landed after it, each with an ADR. This table follows that pattern.

---

## 1. The premise this plan corrects

Three documents state that per-character goals have no source. All three are wrong, and the correction is
dated and evidenced rather than silently applied.

| Where | What it says | What was measured 2026-10-13 |
| --- | --- | --- |
| `docs/scenarios/09-global-race-calendar.md:733` | "**❌ Per-character Goal races are not in the export.** … no GameTora key carries them" | `ura-objectives` **is** a GameTora manifest key (hash `ead4bbfe`), and it answers `200` |
| `docs/research-scratch/RACE-AND-SLICE-RESEARCH.md:268` | "none of it is in the export, which is why only four characters have any Goal data at all" | 135 characters publish a ladder; **all 67** trainees in our `umamusume` table have one |
| `docs/proposals/frontend-development-plan.md:1182` | "`trainee_goals` does not exist … Reserved as KI-34, needing a fetch-source decision" | Correct that the table does not exist. The fetch-source decision is now made (§0 rulings 1–4) |

### 1.1 The measurements, and how to reproduce them

```text
curl -A "UmamusumeTrainerCompanion/0.2 (personal local tool)" https://gametora.com/data/manifests/umamusume.json
curl -A "UmamusumeTrainerCompanion/0.2 (personal local tool)" https://gametora.com/data/umamusume/ura-objectives.ead4bbfe.json
node .scratch-uma/goalprobe2.mjs        # session-private probe, read-only sqlite handle
```

| Fact | Value |
| --- | --- |
| Manifest keys, total | 281. `ura-objectives`, `ura-fans`, `ura-races`, `ura-race-rewards`, `race_instances`, `scenarios` |
| Rows in `ura-objectives` | 135, keyed `char_id` (+ `obj_id`, `objectives[]`) |
| Objectives | 1,701 total; 10–18 per character, mean 12.6 |
| Rows on our roster | **67 characters, 829 objectives** — 597 race-placing, 26 fan-count, 201 scenario-final rounds (67 × 3), 5 undecoded |
| Roster rows with no ladder | 0 |
| Characters skipped | 68 (Japan-only, no `umamusume` row) — the same rule as FR-A-6, which stores only cards carrying a Global date |
| Calendar join | **1,219 of 1,222** race objectives match an existing `race_catalog_slots` row on `(year, month, half, title)` |
| Turn agreement | On all 1,219, the stored `turn` equals the derived turn. Delta histogram: `[0, 1219]` |
| The 3 misses | `Queen Elizabeth Cup` ×2, `Milestone Championship Nambu Hai` ×1 — each carries the source's own `unreleased_servers: ["en"]` |
| Key grain | `(char_id, cond_id, turn)` is unique (829/829); `(char_id, order)` is not (19 collisions, from alternates) |
| `robots.txt` | Disallows `/404`, `/500`, `/patron-zone`, `/loc/`, `/cdn-cgi/`, two event paths. `/data/` is **not** disallowed; no `crawl-delay` |

Turn arithmetic, re-confirmed by a Global client readout rather than by arithmetic alone:
`turn = (year − 1) × 24 + (month − 1) × 2 + half`, with `half` Early = 1 / Late = 2, years 1 Junior,
2 Classic, 3 Senior, and turns 74/76/78 being the three post-December final rounds. This matches
`TrainingRun::TURNS_PER_YEAR` and `RaceCatalogSlot`'s existing turn column.

### 1.2 Why the ladder is scenario-neutral

One ladder per trainee, no scenario dimension in the source, and three documents agree that is how the
game works: `docs/scenarios/02-unity-cup.md:155` ("your trainee's own individual race goals still apply on
their normal schedule (same as URA Finale)"), `docs/scenarios/07-grand-concert.md:92-95` (the scenario
"returns the standard trainee career features of race goals and career events"), and
`docs/scenarios/03-trackblazer.md:14-20` with `SCENARIO-PUBLISHER-REFERENCES.md:1199` (Trackblazer has **no**
race goals; Grade Points replace them). So: goals render for `ura_finale`, `unity_cup` and
`our_grand_concert`, and stay off `trackblazer`, whose `grade_objectives` panel already carries its own
obligations.

---

## 2. The decode table

`cond_type` selects the kind, `cond_value` the figure. `cond_id` is race-scoped, not requirement-scoped:
`cond_id 81` is Arima Kinen and appears with `cond_value` 0, 1 and 3 across characters, so the placing is
carried by `cond_value` alone.

| Source | Displayed requirement | Evidence this plan will cite |
| --- | --- | --- |
| `ct=1, cv=1` | Place 1st in {race} | `[Global]` client capture, `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md:186-191`: "Place 1st in Tenno Sho (Spring) (Senior Late Apr)" |
| `ct=1, cv=3` | Place top 3 in {race} | same capture: "Place top 3 in Nikkei Sho (Senior Late Mar)" |
| `ct=1, cv=5` | Place top 5 in {race} | `screenshot-notes/00-race-entry-screenshots.md:62`: goal banner "Place top 5 in Japanese Derby" |
| `ct=1, cv=0` | Participate in {race} | `screenshot-notes/02-career-progression-screenshots.md` ladder + Haru Urara's published Global-client objective list ("Objective: Participate"); population check: the 20 non-debut `cv=0` rows are underdog G1 objectives (Haru Urara / Mejiro Dober / Maruzensky / Eishin Flash / Fuji Kiseki, Arima Kinen and Kikuka Sho), not a random sample |
| `ct=3, cv=N` | Earn N fans | **Corrected 2026-10-13: the client banner is the source, not the guide.** `screenshot-notes/01-training-screenshots.md:15` reads "Goal banner: 'Earn 3000 fans'" (repeated at `:41`), so `TraineeGoal::requirementPhrase()` prints `Earn {N} fans`. The published guide line "Have at least 5000 fans … Turn: 37 … Classic Class … Early July" is what confirms `ct=3` *semantically* and the turn arithmetic — the export's `ct=3 cv=5000` at turn 37 is exactly Classic Early July — but its wording is second-hand and moves to `SCREEN_SPEC.md` §7. |
| `ct=1, cv=2` (17 rows stored, 31 in the document), `cv=4` (0 stored, 1 document) | **not decoded** | no client capture and no guide line covers these; `D-20` forbids inventing the string |
| `ct=2` (5 stored: Oguri Cap, Agnes Digital, Tamamo Cross, Smart Falcon, Matikanetannhauser; 10 in the document), `ct=12` (0 stored, 1 document) | **not decoded** | same. **22 stored rows in total carry no readable requirement**, measured on the scratch seed |
| `target_type=3`, `race_type` 1/2/3 | the three scenario-final rounds | already rendered by the Mandatory-races group from `race_catalog_slots.is_mandatory`; stored for completeness, **not** re-printed as a trainee goal |
| `race_choice > 0` (35 rows on the roster) | an alternative at the same `order` | e.g. char 1144 order 7: `race_choice 1` = Arima Kinen, `race_choice 2` = Nikkei Shinshun Hai — printed as alternatives, never as two separate obligations |

**The one wording conflict.** Our own captures print both "Place 1st" and "Place top 1" for the same kind of
goal (`00:19` vs the Rice Shower doc). This plan adopts "Place 1st", because the named-character document
that carries a capture hash (`885d4671`) uses it, and records the variant in `SCREEN_SPEC.md` §7 rather than
silently choosing. Same treatment for "Have at least N fans" against "Earn N fans".

---

## 3. Schema

`database/migrations/2026_10_13_...._create_trainee_goals_table.php`. Engine-owned reference data, so it
carries `is_manual` and the four inline provenance columns (`ADR-0003` Amendment R3 — the shape
`race_catalog_slots` and `character_cards` already use, not `data_sources`, which stays the character-level
table behind FR-A-4).

```text
trainee_goals
├── id
├── umamusume_id            FK → umamusume, cascade. Goals are per character, not per card.
├── race_catalog_slot_id    FK → race_catalog_slots, nullable, nullOnDelete. Null for a fan goal, an
│                           undecoded goal, and the 3 objectives whose race is not on Global.
├── goal_type               placement | participate | fan | scenario_final | undecoded
├── required_placement      unsignedTinyInteger nullable — the source's own cond_value, kept raw
├── fan_threshold           unsignedInteger nullable — fan goals only
├── career_year             tinyInt 1 Junior / 2 Classic / 3 Senior / 4 the post-December block
├── turn                    the source's own deadline turn, 1..78
├── source_order            the ladder position, 1..18
├── source_race_choice      the alternate index at that position, 0 when there is no branch
├── source_cond_id          int — the source's own condition id, kept so a re-fetch re-derives the row
├── source_cond_type / source_cond_value / source_cond_value_2 / source_target_type / source_race_type
│                           int — raw, never rendered as a bare code without the note (§6.5)
├── source_race_name        string nullable — only when no slot row carries the race (the 3 non-Global)
├── global_unreleased       bool — the source's own unreleased_servers marks "en" on this race
├── source_url / snapshot_path / fetched_at / source_timezone
├── is_manual               bool — the engine's stop sign (PRD FR-B-4)
└── timestamps
unique (umamusume_id, source_cond_id, turn, source_race_choice)
index  (umamusume_id, career_year, turn, source_order)
```

`source_race_choice` earns its place in the unique key because alternates genuinely share an `order`:
`(umamusume_id, source_cond_id, turn)` is already unique on today's body, and the fourth column is what
keeps it unique when a republish adds a branch.

Deliberate deviations, stated rather than smuggled:

- **No `training_run_id`.** `RACE-AND-SLICE-RESEARCH.md:250` sketched one ("set when scoped to one run").
  The source ships one ladder per trainee, and a run's progress against it is derivable from
  `race_entries.placement`, which already exists. A run-scoped column would make the table part-engine,
  part-Trainer for no data gain, and would need a write path this feature does not ask for. That section
  gets a dated correction, not a silent edit.
- **Nothing reuses the name `objective_index`.** `race_entries.objective_index` already means the Trackblazer
  Grade-Point period (`RaceEntry.php:30`, D-270, `MAX_OBJECTIVE_INDEX` = 4). The collision is recorded here
  so nobody "discovers" the column and re-points it.
- **`#[Fillable]`, `HasFactory`, `casts()` method, `@use HasFactory<TraineeGoalFactory>`** per `AGENTS.md` §7.
  Note `RaceCatalogSlot` and `ScenarioSlot` are the two models still on `protected $fillable`; the attribute
  is the rule, not the outlier.
- `down()` drops the table. Verified on a scratch file, never on the shared dev database.

---

## 4. Ingest

| File | Change |
| --- | --- |
| `config/uma.php` | new `gametora-ura-objectives` entry: pinned fallback `url` `…/ura-objectives.ead4bbfe.json`, `manifest { url, base, key: "ura-objectives" }` (the KI-67 route, so a rotated hash refreshes instead of 404-ing), `parser`, `delay_ms` 1000, `timeout_s` 15, `timezone` `Asia/Tokyo`, `seed_file` `ura-objectives.json` |
| `app/Services/DataPipeline/Contracts/TraineeGoalSourceParser.php` | new interface, for the one reason every sibling exists: the rows carry no display name, so routing them through the cross-reference stage would flood `match_candidates` with condition ids |
| `app/Services/DataPipeline/Parsers/GametoraUraObjectivesParser.php` | pure `parse(string $body): array`; flattens `objectives[]`, derives `career_year` and the half-month from `turn`, resolves nothing against the database |
| `app/Actions/StoreTraineeGoals.php` | `DB::transaction`; skip any row whose existing twin has `is_manual = true`; upsert on the unique key; stamp provenance on every row; skip a `char_id` with no `umamusume` row and report the count; **bump `catalog:version`** when anything was written, because §5.4 caches on it |
| `app/Services/DataPipeline/PipelineRunner.php` | the seventh `is_a()` branch, the same 8-line shape as the other six. `:88-91` records that a shared abstraction across these branches was rejected on purpose, so a new branch is the sanctioned cost, not a refactor opportunity |
| `database/seeders/data/ura-objectives.json` | the committed body, named with its first 8 hash digits per the `UmaImportSupportCards:31-35` convention so the pinned URL stays a true statement about it |
| `tests/Fixtures/ura-objectives.sample.json` + `tests/Feature/GametoraUraObjectivesParserTest.php` + `tests/Feature/TraineeGoalsFetchTest.php` | `Http::fake` only; the fetch test follows `RaceCatalogFetchTest.php` |

**Robots and rate-limit note, required by §11 and written into the config block.** `/data/` is not
disallowed by `gametora.com/robots.txt`, which publishes no `crawl-delay`; one request per fetch, inside the
existing `delay_ms`, per-source lock and retry ceiling; same host, publisher, path shape and response class
(static JSON, no HTML, no JS) as the entries the owner approved on 2026-09-27. Stated honestly in the entry,
because `config/uma.php:59-64` and `:122-131` already say it: **the approval for this host was given without
a live robots check**, and `KI-24` means a pinned hash that goes stale answers `200` with old content rather
than failing — which is why `manifest` is present.

---

## 5. Read path and surfaces

### 5.1 Cockpit, career-goals module

`CockpitController::careerGoalSections()` loses the hardcoded absence group at `:1816-1832` and gains a real
**'Trainee goals'** group of rows ordered by `(career_year, turn, source_order)`, beside the existing
'Mandatory races' and 'Happy Meek' groups.

State is derived, never stored:

| State | Rule |
| --- | --- |
| `completed` | a `Completed` `race_entry` at the goal's slot with `placement <= required_placement`; any entry at the slot for a `participate` goal; the highest `fans` recorded at or before the deadline turn meets `fan_threshold` |
| `current` | the earliest unmet goal whose turn equals the run's next turn to play |
| `missed` | unmet, and the deadline turn has passed — through `RaceCatalogSlot::isAtOrBeforeTurn()`, the existing single owner of that arithmetic (ADR-0015). A goal with no recorded turn can be `current` and can never be `missed`, which is the E2 ruling carried forward |
| `upcoming` | unmet and not yet due |

An unmet goal whose turn has arrived on an Active run raises the **Critical** alert the mandatory rows
already raise, with the goal's own requirement in the text. `UraPanel.vue` needs **no change**: it already
loops `props.scenario.objectives` (`:132`), derives headings from the group name, and is scenario-neutral by
gate G-33, so a fourth group renders as authored. `config/scenarios.php` turns `career_goals` on for
`unity_cup` (`:268-275`) and `our_grand_concert` (`:711-718`), which today omit the key entirely; the two
tests that read `array_keys(config('scenarios.panel_labels'))` (`ScenarioIsolationTest:33`,
`GrandConcertPanelTest:44`) keep passing because no key is added — the existing 7th flag is what is switched
on.

### 5.2 Goal marks on the live race surfaces

**This replaces the pennant slice, and the reason is a stale premise this plan found in its own brief.**
`TrainingRun.php:746-749` promises "the banner returns when `trainee_goals` exists to drive it… the
component still renders a `goal` state", and `RACE-AND-SLICE-RESEARCH.md:279-286` repeats it. It does not:
`calendarCells()` is **not called by any live surface** — `SCREEN_SPEC.md:2092` says so, and the only caller
left in the tree is `tests/Feature/RaceSlotPanelComposerTest.php`. Its renderer was the Blade
`race-calendar.blade.php`, retired with the rest of the Blade components (slice B1), and the E1 SPA cutover
fed the Cockpit from `raceStripSection()` instead. Wiring `calendarCell()` would paint a mark on a grid
nobody renders, and would leave the actual live surfaces unchanged.

So the goal mark goes where the client itself paints it, which the screenshot corpus records precisely:

| Surface | Change | Client precedent |
| --- | --- | --- |
| `RunRaceStrip.vue` | each strip row that is one of the trainee's objectives carries the goal mark and its requirement line; `absences.goal` at `:168-172` becomes a real value and `CockpitController.php:656`'s KI-34 sentence is corrected forward | `00:19`, `00:41`, `00:62` — the goal strip on the entry and race-day screens |
| `Career/RaceDecision.vue` | the obligation prints on the decision itself, beside the race being chosen, not on a separate summary | `05:85` "obligation tag pinned to an action button"; fidelity §2.4 reads this as law 9 (Uniform Connectedness) with obligation and action as separate layers |
| `RaceCard.vue` drawer | the requirement line joins the gate lines the drawer already prints (`:157` "Fan gate: … fans", `:160` "Maiden rule: …") | same |

No revival of the 24-cell grid. If a later pass wants the calendar back, that is a `SCREEN_SPEC.md` decision
of its own and this plan does not pre-empt it. What this plan does is record the drift: `TrainingRun.php`'s
comment and the research file's §7 get dated corrections saying the pennant's rendering home is gone and the
mark now lives on the three surfaces above.

### 5.3 The ladder, not a list

`frontend-fidelity-2026-10-13.md` §2.4 lists "a goal ladder with one 'next' plus a `Goals Achieved 5/8`
footer" as a client pattern the build lacks, and `ux-laws-audit-plan-2026-10-13.md` §2.7 maps it to
Goal-Gradient (18) and Zeigarnik (19). The goals module is the first place that grammar can land, so it
lands here rather than as a flat list:

- one row badged **Next** — the earliest unmet goal, and only one, whatever the count;
- a footer in the client's own shape: **Goals Achieved {cleared} of {total}**, both integers;
- rows in ladder order, grouped by career year through the existing group structure;
- alternates (`source_race_choice > 0`) printed as alternatives of one obligation, not two obligations, and
  counted once in the footer.

**No percentage, no score, no progress bar.** `ux-laws-audit-plan` §9 item 6 and `frontend-fidelity` §8 item 3
forbid a score, percentage, confidence or grade. A count of cleared goals against the source's own total is a
recorded fact, not a computed index, and the client prints it; the distinction is written into the component's
docblock so a later pass does not "improve" it into a bar.

### 5.4 Catalog detail

`CatalogController::show()` composes the trainee's ladder inside the existing `Cache::remember` guarded by
`catalog:version` (which §4's store action bumps, or the page serves a stale ladder forever), and
`pages/Catalog/Show.vue` renders it with the same row component the Cockpit uses. This is the widest option
the owner chose: a Trainer can read what a trainee's career asks of her before starting one. The block
renders its empty state too — a trainee with no ladder row is a Japan-only unit, and that says so.

### 5.5 Server honesty

- The race title a Trainer ever sees comes from **`race_catalog_slots.title`**, which is already the
  Global-vetted client string. The objectives file's own `name_en` is stored in `source_race_name` **only**
  where no slot row carries the race (the 3 `unreleased_servers` objectives) and is then rendered as
  "the source names this race; it is not on this calendar".
- One note line, not a per-row claim: the ladder is read from the publisher's Japan-side objectives export;
  where this tool has a `[Global]` client capture of the same deadline, the capture is cited in
  `docs/`. `global_unreleased` rows never print as a Global obligation.
- Provenance follows the standing ruling: a badge qualifies a **displayed value**, and no fifth provenance
  label is added (`ux-laws-audit-plan` §9 item 2). The existing set is Confirmed / Calculated / Estimated /
  Unknown (`ProvenanceBadge.vue:55-58`). Goal rows carry `Confirmed` on the source-stated figures; the
  undecoded requirement carries no badge and says it is undecoded instead.

---

## 6. Constraints imported from the two frontend files, and how this plan obeys them

These are the rows of their "must not do" lists that a goals slice would otherwise trip. Each is a
constraint on this plan, not a note.

| # | Binding rule (source) | How this plan complies |
| --- | --- | --- |
| 6.1 | Do not implement a Track 2 clause — radius, shadow, font, colour, motion — before the `DESIGN.md` amendment is signed (`frontend-fidelity` §8 item 6) | The goals UI changes **no** radius, shadow, type tier or motion. It spends `--color-goal` / `--color-goal-line`, which are already committed and already measured at 5.74 / 5.02 on `raised` (`app.css:70-80`), i.e. Track 1 move 6, "spend the existing client-derived tokens more deliberately" |
| 6.2 | Do not invent a `--color-warning` role; the declared pair is `risk` (laws §9 item 3) | A goal is an obligation, never a fault. `--color-risk` stays on errors. This is already the ruling in `app.css:70-78` and the E2 alert-level note: a Critical row is `text-ink-strong` plus weight plus the level word, no colour |
| 6.3 | Do not add a fifth provenance label or a section-qualifying badge (laws §9 item 2) | §5.5: existing four only |
| 6.4 | Do not add a score, percentage, confidence, risk grade, win chance or recommendation (laws §9 item 6; fidelity §8 item 3) | §5.3: an integer count, no bar, no percentage, nothing predictive. `PRD` §6.11 is untouched |
| 6.5 | Tesler's Law (20): the tool absorbs complexity, no internal-model exposure (laws §3.2 row 20) | The raw `cond_type` / `cond_value` codes are **never** shown as the whole answer. An undecoded row prints a sentence naming what is missing, then the source's own engine figures inside a `<code>` element under a note that says they are the source's data and not client labels — the pattern `frontend-fidelity` §4.4 endorses for the skill detail's `Condition` / `Base time` |
| 6.6 | Law of Closure (12) / X-1: an absence reason carried by a `title` is a P1 FAIL, mouse-only | New absences use `AbsenceValue.vue`, whose reason is the `<summary>`'s accessible name (`AbsenceValue.vue:5-16`), never a bare `title`. This is also why §9's slice 6 rewrites rather than relaxes the `getByTitle` browser pin |
| 6.7 | Do not migrate an absence without moving the specs that pin the current channel, owner approval (laws §9 item 9) | Explicitly authorised by ruling 3 (widest reach). The pins that move: `UraPanelTest.php:187` (`toContain('trainee_goals')`), `tests/browser/ura-panel.spec.ts:127-132` (`getByText(/trainee_goals/)`, `getByTitle(...)` → `N/A`), `RunRaceStripTest.php:209`, `RaceSlotPanelComposerTest.php:67-76`. Rewritten to the new contract with the reason in the commit message; none deleted or skipped (§5 Floor) |
| 6.8 | Do not restyle per-page headings before the shell `#title` decision (laws §9 item 8; fidelity §5 ordering note) | The goals blocks add **no** type declarations. Headings use the existing section-header treatment the panel already uses, so the Track 1 type-scale pass can restyle the shell without touching this feature |
| 6.9 | Do not name a scenario in a component or default a `scenarioLabel` (laws §9 item 7, gate G-33) | The ladder is server-composed and the renderer is the existing scenario-neutral one. `TrackblazerPanel.vue`'s `props.name` branch at `:62-65` is a known violation this plan neither copies nor widens |
| 6.10 | Do not reflow the race calendar, turn log or compare tables into stacked cards (laws §9 item 4) | The strip and the decision surface keep their region contract (`overflow-x-auto` + `role="region"` + `tabindex="0"` + `aria-label`); a goal mark is added inside the existing row |
| 6.11 | Von Restorff (15): one accented primary per screen (fidelity §2.6) | The **Next** badge and the goal mark use the goal pair, not the action green. Slice 6 measures the accented-primary count on the Cockpit and the decision screen and keeps it where `remaining-screens-ui-audit` X-3 left it; if a count moves, the mark loses its colour and keeps its word |
| 6.12 | Do not copy the HLF's placeholder data or invented copy (fidelity §8 item 9) | Every string is the client vocabulary of §2 or a server-composed fact. No "Good morning, Trainer"-class filler, no mock total |
| 6.13 | Progressive disclosure (§35), Hick's (4), Choice Overload (25) | The ladder is ~12 rows per trainee and is listed in full in the panel — the client itself lists them — but the strip and the decision surface each show **only** the obligation that bears on that action, which is the split laws §2.7 describes as "obligation and action are separate layers" |

Two forward-compatibility notes, because the frontend files describe work that will land after this:

- **Sub-cells.** The HLF's "labeled sub-cells (key/value mini-boxes)" (§4.1, the signature component the
  build lacks) is a Track 1 move not yet scheduled here. Goals rows are therefore emitted as **structured
  fields** (`label`, `state`, `year_label`, `turn`, `requirement`, `absence`) conforming to the eight-field
  row contract `CockpitObjectivesTest.php:127` pins, so a later sub-cell pass restyles the row with **no**
  data change.
- **Track 2 D.** If the badge-colour ruling ever lands (lime `Confirmed` / amber `Needs decision`), the
  goals panel inherits it through the token and component layer; nothing in this plan pre-implements it.

---

## 7. Copy and lore

- No em dashes in shipped copy. An unrecorded value renders `N/A` through `AbsenceValue`, never a default and
  never a dash.
- Global client vocabulary only, from `lang/en/uma.php`'s terms map where a word is shared. "Participate",
  "Place 1st", "Place top 3", "Place top 5", "Participate", "Earn N fans", "Goals Achieved" are the client's own.
- `composer lore` and `composer lore-code` run at hand-off with a one-line ruling per hit. The word "goal" is
  not on the banned list; the danger is in prose about trainee objectives drifting into the banned equine
  families, so the new copy is written in terms of a career, a race and a deadline.
- Scenario names come from `config/scenarios.php` only; no scenario string is written into a component (§6.9).

---

## 8. The documentation package §11 requires

### 8.1 PRD citation — drafted for the owner's signature

An `FR-B` line, appended after the last existing B item, in the PRD's own voice:

> **B-8 [US-10; `ADR-0026`]:** Each trainee's own career objectives are engine-owned reference data on
> `trainee_goals`, one row per objective: the race it names (or no race, for a fan-count objective), the
> requirement the client states, the career year and the deadline turn, and the source's own condition
> figures kept raw. Two kinds are stored and they are genuinely different — a race objective names a race and
> a placing, a fan objective names a threshold and occupies no race. Objectives are per trainee, not per
> costume card and not per scenario, and a run's progress against them is **derived** from the race entries
> the Trainer already records, never re-entered. Nothing here computes whether an objective *can* be met:
> §6.11 stands, and no surface prints a probability, a score or a suggested entry.

Also: `PRD.md:260`'s open-question block and `US-10`'s acceptance test gain a dated note that
`scenario_slots` seeding was its reading of "race goals" and this line is the per-character half.
**The owner signs this text; an agent does not record an owner ruling on inference.**

### 8.2 `ADR-0026-per-trainee-career-objectives.md`

Status `Accepted` on the owner's ruling. Context: the three stale "not in the export" claims and the
measured file. Decision: fetch source over seeder body; trainee grain with no run scope; store-all-decode-on
-display; five goal kinds; the pennant's rendering home is gone and the mark lives on the live race surfaces;
`calendarCells()` is left as-is. Consequences: `catalog:version` now invalidates on goal promotion; alternates
are stored as branches, not merged; 22 stored rows keep an undecoded requirement; §6.11 untouched. It also records
the conflict list (§8.4) rather than resolving it silently.

### 8.3 Corrections that travel with the change

Each preserves the original sentence and appends a dated correction, per §11:

| File | What moves |
| --- | --- |
| `ARCHITECTURE-ESSENTIALS.md` | the schema digest gains the `trainee_goals` row, the PRD citation and the source key |
| `ARCHITECTURE.md` | the pipeline section names the seventh parser/store pair and the objective ladder's join |
| `docs/scenarios/09-global-race-calendar.md` | `:733` and `:666` — the "no GameTora key carries them" claim corrected; the four client panels become a corroboration source rather than the only known data |
| `docs/research-scratch/RACE-AND-SLICE-RESEARCH.md` | §6's "none of it is in the export" and the `training_run_id` sketch; §7's "the component still renders a `goal` state" |
| `SCREEN_SPEC.md` | SCR-CAR-020 / SCR-CAR-021 goal rows from named absence to real module; `:843`, `:1572`, `:2020-2024`, `:2725-2728`, `:480-482` (O-12); the §7 conflict rows for the three items below |
| `docs/proposals/frontend-development-plan.md` | `:1182`, `:1676-1677`, `:1690-1691`, `:1773-1775` |
| `docs/research-scratch/PROCESS-PLANS.md` | `:743`, and the two unchecked KI-35 boxes at `:782`/`:1029` |
| `docs/Umamusume_Progress_Tracker_Runs.md` | a note that the 17 hand-kept run sheets are now checkable against the sourced ladder, and remain the owner's record, never a data source |
| `docs/proposals/URA-Finale-plan.md` | `:59`'s wrong citation (`01:77-84` is the fans section, not a goal ladder) and `:66`'s stale `config/scenarios.php` line reference |

### 8.4 Conflicts recorded, not resolved

1. The debut. `RACE-AND-SLICE-RESEARCH.md:287-290` says the client does **not** banner the debut, while the
   export makes it objective #1 for all 135 characters (`cv=0`, Participate). The export is stored faithfully;
   the strip prints what the export states; the contradiction goes to `SCREEN_SPEC.md` §7 for a client capture
   to settle.
2. "Place 1st" versus "Place top 1"; "Have at least N fans" versus "Earn N fans" — both in our own corpus (§2).
3. `KI-16`-style register rot: `docs/research-scratch/AUDIT-AND-VERIFICATION.md:929-931` and
   `DESIGN-AND-MECHANICS-REQUESTS.md:112-116` both cite a KI-34 reservation at `KNOWN-ISSUES.md:17`, and there
   is no KI-34 entry there today. Filed as part of slice 7.

### 8.5 `KNOWN-ISSUES.md`

The reserved **KI-34** is landed in the four-part form it was reserved for: heading continuing the highest
number convention, status line, the proving command or file, and the closure commit plus what closure does not
cover. Its subject is the *documentation* defect — three files asserted a key did not exist — with the
proving command `curl -s https://gametora.com/data/manifests/umamusume.json | grep ura-objectives`. Closure
of the register entry is the owner's to confirm. The undecoded codes (`cv` 2 and 4, `ct` 2 and 12) and the
debut-banner contradiction each get their own entry only if they are still open at hand-off.

---

## 9. Slices

Each in the template the sibling plan files use, and each independently shippable. Nothing here widens
scope beyond rulings 1–4.

### Slice 1 — schema, model, factory

**Outcome.** `trainee_goals` exists, migrates down, and has a factory.
**Inspect first.** §3's shape; `RaceCatalogSlot`'s migration for the null-coalescing-index caution; the
`#[Fillable]`-bypasses-factories trap (`DeckSlot.php:106`).
**Implement.** Migration with a working `down()`; `TraineeGoal` model with `#[Fillable]`, `casts()`, enum-typed
`goal_type`, `BelongsTo` to `Umamusume` and `RaceCatalogSlot`, and a `requirement()` reader that returns the
decoded sentence or the undecoded marker; `TraineeGoalFactory`; a `GoalType` enum in `app/Enums/`.
**Expected code changes.** 1 migration, 1 model, 1 factory, 1 enum, `ARCHITECTURE-ESSENTIALS.md` digest row.
**Acceptance checks.** `migrate:fresh --seed` clean on a scratch file; `migrate:status` clean on the dev file
(the §9 rule that a green suite proves nothing about that file); the `down()` call actually drops the table on
the copy; a factory row builds and the requirement reader returns the decoded sentence for `ct=1 cv=3`.

### Slice 2 — ingest

**Outcome.** `php artisan uma:fetch gametora-ura-objectives` and `php artisan migrate --seed` both populate
the table with real provenance and never touch an `is_manual` row.
**Inspect first.** `RaceCatalogSourceParser` + `StoreRaceCatalogSlots` + `PipelineRunner:68-79` as the thread
to copy; `UmaImportSupportCards:14-35` for the seed-file and hash-naming convention.
**Implement.** §4's seven rows, including the committed body and the robots note.
**Expected code changes.** 1 config entry, 1 contract, 1 parser, 1 action, 1 `PipelineRunner` branch, 1 seed
body, 2 test files, 1 fixture.
**Acceptance checks.** Parser unit test on the fixture: 135 rows in, correct `career_year`/`turn` split, no
database access. Fetch test with `Http::fake`: 67 roster characters stored, 68 skipped and counted, 829 rows,
every row with all four provenance columns, a second run changes nothing (idempotent), an `is_manual` row is
skipped and reported, `catalog:version` moves only when something was written. A hand-built alternates case
asserts the two branches at one `order` are both kept.

### Slice 3 — Cockpit goals module

**Outcome.** The career-goals panel prints the trainee's real ladder with Next badging and an achieved count,
and the named absence is gone.
**Inspect first.** `careerGoalSections()` `:1757-1844`; the eight-field row contract
(`CockpitObjectivesTest.php:127`); `UraPanel.vue` `:132-167`; the E2 rulings in §5.1.
**Implement.** Replace the absence group; add the derivation and the Critical alert; switch the config flag on
for `unity_cup` and `our_grand_concert`; correct `TraineeProfile.vue:285`'s "no career-goal section" claim.
**Expected code changes.** `CockpitController`, `config/scenarios.php`, one test file rewritten, one Vue doc
comment.
**Acceptance checks.** URA run: 12-ish rows in ladder order, one and only one `current`; Unity Cup and Grand
Concert runs draw the same module; a Trackblazer run does not; a completed goal reads `completed` from the
existing race entry without any new write; a passed unmet goal alerts Critical with the requirement in the
text; a goal whose race is `global_unreleased` renders as the source naming it, not as a Global obligation;
the footer counts alternates once.

### Slice 4 — goal marks on the live race surfaces

**Outcome.** The obligation appears on the strip row, on the decision surface, and in the race drawer.
**Inspect first.** `raceStripSection()` `:646-660`; `RunRaceStrip.vue` `:55,168-172`; `RaceDecision.vue:165`;
`RaceCard.vue:157,160`; §5.2's dead-grid finding; the eager-load list at `CockpitController:134` (this adds a
relation read, so check for N+1 before it ships).
**Implement.** Compose the goal marks server-side and pass them as fields; render the mark with the existing
`border-goal-line` / `--color-goal` treatment; replace `absences.goal`.
**Expected code changes.** `CockpitController`, `TrainingRun` (only if the read belongs there),
`RunRaceStrip.vue`, `RaceDecision.vue`, `RaceCard.vue`, `RunRaceStripTest`, one browser spec.
**Acceptance checks.** A cell that is both a goal and fan-gated keeps the lock reading and adds the obligation
(§6.13); a recorded goal keeps its finish state and still shows the mark; `npm run typecheck` clean; the
accented-primary count unchanged on both screens (§6.11); the tap-target sweep still passes; axe scoped to
`#app` clean.

### Slice 5 — catalog detail

**Outcome.** A trainee's ladder is readable on her catalog page with no run in progress.
**Inspect first.** `CatalogController::show()` and its `Cache::remember` / `catalog:version` read at `:563`;
`ArtworkSlot` placement precedent from OQ-6; `pages/Catalog/Show.vue`'s existing block structure.
**Implement.** One composed block, one rendered section, empty state for a trainee with no rows.
**Acceptance checks.** Promotion bumps the cache so the page cannot serve a stale ladder; the block renders
empty, populated and error states (ADR-0007); no new query per row.

### Slice 6 — tests that pin the absence, rewritten

**Outcome.** No spec asserts a thing this feature made false.
**Inspect first.** §6.7's list; `ura-panel.spec.ts:127-132`'s `getByTitle`; the `getByText` substring
collisions that bit E3 and E4.
**Implement.** Rewrite `UraPanelTest.php:187`, `ura-panel.spec.ts:127-132`, `RunRaceStripTest.php:209`,
`RaceSlotPanelComposerTest.php:67-76` to the new contract; scope every new page-wide assertion to the region
that owns the marker.
**Acceptance checks.** Each rewritten test fails against the pre-slice code for the right reason (a mutation
check, not a belief); nothing is deleted or skipped; the commit message carries the reason and the owner
authorisation.

### Slice 7 — documentation package

**Outcome.** §8 lands in full: ADR-0026, PRD B-8 (owner-signed), digest, the correction table, the three
recorded conflicts, KI-34.
**Acceptance checks.** `python tools/md_fence_check.py` on every touched file; `composer docs` census clean;
the ADR index regenerated with the command in `docs/adr/README.md`, not hand-edited; every corrected sentence
still readable in its dated-erratum form (`git diff` shows appends, not rewrites).

---

## 10. Verification

Level 1 focused while working, Level 2 related regression at each slice end, Level 3 the hand-off at the
checkpoint. Nothing that writes is backgrounded while a peer session is live; the browser suite is always
session-private.

```text
php artisan test --compact --filter=TraineeGoal
php artisan test --compact --filter=UraPanel
PLAYWRIGHT_PORT=<session port> PLAYWRIGHT_SCRATCH_DB=.scratch-uma/browser-<port>.sqlite npm run test:browser
vendor/bin/pint --test --format agent <explicit paths>       # never --dirty, KI-75
vendor/bin/phpstan analyse --no-progress --memory-limit=1G
npm run typecheck
composer lore && composer lore-code                          # a ruling per hit
php artisan migrate:status                                   # on the dev file, §9
```

Before any broad sweep: time one trivial SQLite read against `database/database.sqlite` (KI-74). If it takes
more than a few seconds the box is contended, the sweep is void before it starts, and it is reported void with
its counts rather than re-run.

**Baseline caution, stated now.** As at 2026-10-13 the shared worktree carries roughly 55 dirty paths from
peer sessions, including `tests/TestCase.php` and `composer.lock`. A green full suite is therefore not assumed
at any point; the count this plan reports at hand-off is measured on the tree as it stands, and any failure is
classified (regression / pre-existing / unrelated / infrastructure / flaky / inconclusive) before anything
re-runs.

---

## 11. Open items this plan does not decide

1. **The `PRD.md` B-8 wording** (§8.1). The owner signs it; the migration should not land before it does.
2. **Whether `calendarCells()` stays.** It is now known to be reachable only from its own test. Deleting it is
   a `SCREEN_SPEC.md` decision and a separate slice; this plan leaves it running and documents that nothing
   renders it.
3. **Reviving the 24-turn calendar grid.** The client's grid is the pennant's original home. It needs a
   screen decision, not a data one.
4. **Whether `cv=2` / `cv=4` / `ct=2` / `ct=12` ever decode.** Needs a client capture of one of those 23 rows,
   or a ruling that they stay opaque.
5. **The three unresolved `frontend-fidelity` §9 questions** that bear on this surface: which product the app
   is (item 1), whether the HLF is the target of record (item 8), and whether the client's alert vocabulary is
   in scope (laws §10 item 7). None blocks slices 1–5; slice 4's §6.11 accent measurement is where they would
   land first.

## 12. What this plan must not do

Inherits all eleven rows of `frontend-fidelity` §8 and all fourteen of `ux-laws-audit-plan` §9, and adds four
of its own:

1. Do not derive a goal list from `docs/scenarios/09`'s four client panels. The panels are corroboration for
   the decode; the ladder comes from the sourced key or it does not exist.
2. Do not seed `trainee_goals` from a hand-authored body under an engine-owned name. That is the ADR-0003 R3
   line, and the fetch source was chosen over it deliberately.
3. Do not add a Trainer write path for goals under this plan. Ruling 1 chose engine data; a write would be a
   second owner for the same fact and needs its own decision.
4. Do not edit `frontend-fidelity-2026-10-13.md` or `ux-laws-audit-plan-2026-10-13.md`. They are the owner's
   reference files and both say so at the top.

---

## 13. Amendment 2026-10-13, after slices 1 and 2 landed

Slices 1 and 2 are built and green (`TraineeGoalTest`, `TraineeGoalsFetchTest`,
`GametoraUraObjectivesParserTest` → 28 passed / 160 assertions; `pint --test` passed; PHPStan
`[OK] No errors`; `migrate:fresh --seed` on a scratch file → 67 trainees, 829 goals, 597 joined to a
calendar row, 0 blank provenance). Five things in this plan were wrong or imprecise, corrected here in
their own words rather than quietly. The earlier text stands above as what was believed when it was
written.

1. **§8.1's line number was misplaced.** The PRD requirement was drafted as "B-8" in FR-B. FR-B runs B-1
   to B-6 and is the *fetch engine*; the entity rows (`character_cards` A-6, profiles A-7, support cards
   A-8, scenario link A-9) are all in FR-A. A per-trainee objective table is an entity, and its fetch half
   is already covered verbatim by B-1 ("sources are declared in `config('uma.sources')`, each with a
   parser class"). It landed as **FR-A-10**, and the US-10 scope note rides inside that line instead of
   editing US-10's table cell — the acceptance column is a single long markdown table row, and surgery
   inside it risks splicing rows for no added truth.
2. **§2 and §5's fan wording was wrong.** The plan chose the guide transcription "Have at least N fans".
   The `[Global]` client capture prints **"Earn 3000 fans"** on the goal banner
   (`docs/research-scratch/screenshot-notes/01-training-screenshots.md:15`, repeated at `:41`), and §5
   allows the client's words only. `TraineeGoal::requirementPhrase()` prints `Earn {N} fans`; the guide
   variant moves to `SCREEN_SPEC.md` §7 with the "Place 1st" / "Place top 1" pair.
3. **§5.1's row contract was cited at the wrong surface.** `CockpitObjectivesTest.php:127` pins the
   **advisor's** eight fields (`label, state, due_turn, due_label, consequence, source, badge, title`) on
   `advisor.decision.objectives`, and that vocabulary deliberately excludes `missed` ("nothing in this
   build may claim" it). The career-goals module this plan fills is `scenario.objectives`, whose group
   rows carry six fields (`label, state, year_label, turn, race_url, absence`) as
   `careerGoalSections()` already composes them. **Slice 3 stays on `scenario.objectives` and does not
   feed the advisor's list** — that would be a second surface, a different vocabulary and a scope
   widening the owner's four rulings do not cover.
4. **Slice 3 gained a dedupe rule the plan did not state.** The export makes the debut objective #1 for
   all 135 characters, while the capture corpus says the client banners no pennant on it
   (`RACE-AND-SLICE-RESEARCH.md:287-290`) — blocking question A-3. The operational answer, which does not
   require the contradiction to be resolved: a goal whose calendar row is already `is_mandatory` renders
   in the ladder as the source states it, and raises **no second Critical alert**, because the mandatory
   group alerts on it already. One obligation, one alert, whatever the client's banner turns out to mean.
5. **The names diverged from §3, and the code is the record.** The enum is
   `App\Enums\TraineeGoalType` (not `GoalType`: no sibling is named for its table, and `GoalType` would
   read as a domain type), its cases are `Placement|Participate|Fan|FinalRound|Undecoded` with `FinalRound`
   rather than `scenario_final` (TitleCase backed values are §7's rule), the reader is
   `requirementPhrase()` (not `requirement()`, which collides in spirit with the validation vocabulary), and
   `seed_file` carries the hash (`ura-objectives.ead4bbfe.json`) because the committed body's sha256 prefix
   **is** `ead4bbfe` — measured, not assumed — which is what makes the pinned URL a true statement about it.
   `ARCHITECTURE-ESSENTIALS.md` and `ADR-0026` carry these names; the §3 sketch above does not.

Also: `ADR-0026` is drafted **Proposed**, on `ADR-0025`'s precedent — the rulings are recorded and not in
question, the wording is what awaits the owner's signature — so Phase 0 produces the ADR text and the FR-A-10
line without an agent asserting an acceptance on the owner's behalf. The index regenerates with
`python tools/adr-index.py docs/adr --write` (`docs/adr/README.md:55` says `python3`; this host has no
`python3`, which is why the command differs).

Two premises the plan still cannot verify, stated as such: the sweep launched for slice 2 ran while nine
`php.exe` processes were live, so if it returns red it is **void under KI-74** and the evidence for slices
1–2 is the focused suites and the scratch seed named above, not a full-suite pass; and the migration has not
been applied to `database/database.sqlite`, so `migrate:status` on the dev file currently shows this
migration Pending. Applying it is a write to shared state and is the owner's call.

## 14. Provenance of this plan

Written 2026-10-13 against `master` at `1266182`. Every number in §1 came from a command in §1.1 run in this
session against the live manifest and a **read-only** handle on `database/database.sqlite`; nothing was
carried in from a brief, and no `.kilo` or `.scratch-uma` copy was counted (both hold stale duplicates of
`CockpitController.php`, `config/scenarios.php` and `TrainingRun.php`). The two frontend files were read in
full and their constraints are in §6 rather than paraphrased. Probes live in `.scratch-uma/` and are
disposable; the plan is the durable artifact.
