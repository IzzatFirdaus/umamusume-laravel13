# PRD: Trainer Desk (consolidated Umamusume trainer tool)

Status: the requirements below are current as written. The most recent dated record this file carries is the
2026-10-12 `ADR-0025` ceiling travel in C-1 and C-2; A-10's `ADR-0026` objectives line is later but carries no
date here. The version label is `2.0.0` pre-release ([VERSION.md](VERSION.md)) and the web surface is
Inertia + Vue 3 ([ADR-0020](docs/adr/0020-trainer-desk-2-program.md)), so this is no longer a
documentation-and-scaffolding document. Whether Phase 1 exit (§8) has been declared: **to be confirmed**.
Product name: **Trainer Desk**, owner decision 2026-09-27, closing OQ-1.

Revision: [CHANGELOG.md](CHANGELOG.md). Open-question register: [docs/OPEN-QUESTIONS.md](docs/OPEN-QUESTIONS.md).

## 1. Product Vision

One local-only tool that replaces four abandoned trackers: a Trainer browses a catalog of Umamusume whose JP
and Global data is cross-referenced automatically, logs training runs turn by turn, and exports the result. It
runs entirely on the Trainer's machine (SQLite, no server, no accounts), it works offline against cached data,
and every fact it did not get from the Trainer carries a source citation and a fetch timestamp.

### 1.1 Glossary

Every term here is one this document already uses. A definition states what the file says about it and points
at the requirement that owns it; it adds no rule.

- **Trainer** — the single human user: the person the tool is built for and the only writer of its own data.
  One Trainer, one machine, no accounts (§1, §2, NFR-1, non-goal 1).
- **Trainee** — the individual Umamusume a run is built for. `umamusume_id` is the required owner of a run
  (C-1). This document writes it lowercase in prose and never as a species name.
- **Umamusume** — the humanoid race the catalog lists (§2, NFR-6), and separately the record that holds one
  (A-1). A costume *card* of a trainee is a `CharacterCard` (A-6), not a second Umamusume.
- **Global / JP** — Global is the English release the Trainer plays; JP is the Japanese release whose community
  knowledge is read to predict it (§2). A catalog row's release status is one of `GlobalReleased`,
  `GlobalAnnounced`, `JapanOnly` (A-1), and a Global-facing surface prints only rows carrying a Global release
  date (A-8, D-3).
- **Scenario** — a career format with its own per-stat caps, both server start dates and slot list (A-5); it
  sets the ceiling a stat is validated against (C-2) and the linked-character list a Scenario Link is read
  from (A-9).
- **Cockpit** — the per-run interactive screen. This PRD names it as a Trainer-facing Inertia page that must
  receive a bounded slice rather than the whole run history (NFR-10) and as an important route carrying a
  query ceiling (NFR-11). What it shows and how a state renders is (undefined in this PRD; see
  `SCREEN_SPEC.md`).
- **Veteran** — a completed run saved to the library as a reusable, tagged, searchable parent record
  (US-15, FR-G). Recorded, never derived (G-4).
- **Legacy** — the inheritance side of a run: the two optional parent Umamusume references plus what the Legacy
  Select screen displays, stored as recorded data with no computation behind it (C-1, non-goal 3). "Legacy and
  Veterans" is also a screen name in NFR-11.
- **Trainer Advisor** — the deterministic ranker over the Trainer-entered next-turn options, scored against the
  run's Build Target and the `ADR-0001` Energy constants; every suggestion carries a derivable reason, a
  numeric estimate is opt-in, and it never computes a race outcome (US-14, F-2, F-3, F-4).
- **Build Target** — the optional per-run statement of what a run is being built for: purpose, distance,
  surface, running style, the five per-stat targets, and an ordered skill-priority list. Trainer-entered, so
  `is_manual` by definition (US-13, F-1).
- **Support Card** — a named card a Trainer equips for a run. Reference data at identity level (A-8) plus the
  six positions actually equipped (C-6); no collection, no levels, no limit breaks, no Unique Perk state
  (non-goal 9).
- **Scenario Link** — a read-time derivation, never a stored flag: the card's character appears on the running
  scenario's linked list, so the same card is linked in one scenario and not in another (A-9).
- **Support-effect anchor / effect dictionary** — the `SupportEffect` 35-row dictionary naming the anchors a
  card's effect vector refers to; levels are interpolated on read, not expanded (A-8).
- **Trainer data / engine-owned fact** — Trainer data is what only the Trainer can supply and the engine never
  writes (NFR-5, C-6); an engine-owned fact is a promoted catalog fact that carries provenance and yields to a
  row with `is_manual = true` (A-4, B-3, B-4).
- **Named absence** — the rendered state of a value this run has not recorded: it names the missing thing
  rather than defaulting it or leaving empty frames (A-7, A-10, C-6, US-12, US-13).
- **Review list** — the surface where a Trainer resolves Fuzzy and None `MatchCandidate` rows by confirming,
  aliasing or rejecting a proposed JP↔Global match (US-5, B-3).
- **Screens named in NFR-10 and NFR-11** — Dashboard, Careers index, Career Result, Catalog index and detail,
  Skills, Support Cards, Legacy and Veterans, Race Decision, Scenario Race Planner. This PRD names them only as
  bounded-payload and query-ceiling obligations; each screen's inventory and behavior is (undefined in this
  PRD; see `SCREEN_SPEC.md`).

## 2. Target Audience

**Trainers**: players of the Global English version of *Umamusume Pretty Derby*, playing on JP-server knowledge
from community wikis to plan ahead. Profile:

- Plays Global; reads JP release information to predict upcoming content.
- Runs the tool locally, single user, desktop browser.
- Tolerates an artisan command; does not want to operate a database server.
- Currently keeps track of runs in spreadsheets or in one of the four legacy apps this consolidates. The fourth
  (a career-run planner) proves the planning workflow: Trainers pre-pick skills before a run and compare plan
  vs actual afterwards.

The Umamusume are a humanoid race of girls; nothing in this product refers to them with equine vocabulary (see
the lore gate in AGENTS.md §5, enforced by `tools/lore.php`).

## 3. Core User Stories (prioritized)

P0 = Phase 1 ships without it = failure. P1 = Phase 1 target. P2 = later phase, design must not block it.

| ID      | Story                                                                                                                                                                                                                                                                        | Priority   | Acceptance test                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| ------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| US-1    | As a Trainer, I browse the catalog of Umamusume with names in English and Japanese, filtered by Global release status, so I can see who is available to me.                                                                                                                  | P0         | `GET /umamusume?status=GlobalReleased` lists only released entries; each detail page shows `name`, `name_ja`, release status, and provenance (source URL + fetched date). `GET /umamusume` lists each Global-released trainee with her cards nested; each card row shows its client title, rarity and Global release date.                                                                                                                                                                                                                                                                                  |
| US-2    | As a Trainer, I run a fetch that cross-references JP-source data against Global data, so new JP releases appear with a "JapanOnly" or "GlobalAnnounced" flag instead of silently missing.                                                                                    | P0         | `php artisan uma:fetch {source}` stores raw snapshots, creates or updates only Exact/Alias matches, routes Fuzzy/None to the review list, and never modifies rows with `is_manual = true`.                                                                                                                                                                                                                                                                                                                                                                                                                  |
| US-3    | As a Trainer, I create a training run for an Umamusume and log stats per turn (Speed, Stamina, Power, Guts, Wit, SP), so the app keeps the history my spreadsheet used to.                                                                                                   | P0         | Creating a run and posting turns 1..N renders the run page with all turns in order; a duplicate turn number for the same run is rejected with a validation error; a stat above the ceiling that governs its run is rejected: the ceiling is the per-stat figure the Trainer entered for the run's stat when it states one (ADR-0025, cross-referenced C-2), the scenario's per-stat per-scenario ceiling when the run names a scenario (ADR-0015), and a flat 1200 per stat when the run names no scenario; SP carries no ceiling. [ADR-0015; ADR-0025]                                                                                                                                                                                                                                              |
| US-4    | As a Trainer, I record which skills I plan to acquire, actually acquired, or skipped during a run, so I can compare planned vs actual builds.                                                                                                                                 | P1         | A skill can be attached to a run with status Suggested, Acquired, or Skipped and an optional turn number; the run page lists all three groups.                                                                                                                                                                                                                                                                                                                                                                                                                                                              |
| US-5    | As a Trainer, I resolve the review list: confirm, alias, or reject a proposed JP↔Global match, so the catalog improves without the engine guessing.                                                                                                                          | P1         | Review page lists unmatched candidates; confirming an Exact-tier candidate merges it; adding an alias makes future fetches match automatically.                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| US-6    | As a Trainer, I export my runs as CSV or JSON, so my data is never trapped in the app.                                                                                                                                                                                       | P1         | `GET /training-runs/{run}/export.csv` and `.json` download the run with its turns and skills.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               |
| US-7    | As a Trainer, I read JP-sourced dates in my own timezone, so event and debut timing is unambiguous.                                                                                                                                                                          | P1         | A JP-source datetime is stored UTC with source timezone recorded; UI renders it in `config('uma.display_timezone')`; date-only values render identically in every timezone.                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| US-8    | As a Trainer, I edit catalog data by hand (correct a name, add an alias) and my edit survives future fetches.                                                                                                                                                                | P1         | Setting `is_manual` on a row makes `uma:fetch` skip it; a test asserts the row is unchanged after a fetch.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| US-9    | As a Trainer, I query a local JSON API (`/api/v1`) for catalog and run data.                                                                                                                                                                                                 | P2         | API Resources return the documented shapes; version prefix is enforced.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| US-10   | As a Trainer, I track race goals and predictions per run.                                                                                                                                                                                                                    | P1         | Race calendar and fan gating via `scenario_slots` (ADR-0003); mandatory/optional races with fan/maiden gates per scenario. Predictions (user-entered aptitude grades, race-day snapshots) remain deferred per §6.11. Acceptance: (1) `scenario_slots` seeded for all scenarios with `kind` ∈ {goal_race, team_race, grade_deadline, scripted_event}; (2) `RaceEntry` references `scenario_slot_id`; (3) Run detail renders the calendar panel with gates and the grade meter where applicable; (4) No prediction engine or simulation. Per-character objectives are FR-A-10. [ADR-0003; ADR-0026]                                                  |
| US-11   | As a Trainer, my UI preferences survive a restart, so the app looks and behaves the way I left it. The key set is exactly two keys: the theme (`light` / `dark` / follow the OS) and the numeric failure-estimate toggle, off by default.                                     | P1         | A preference written through the app is stored in the `preferences` table and read back on the next request; **no preference is stored in browser storage**, because §6 non-goal 12 cuts a second source of truth; the stored theme is rendered server-side by `components/layout.blade.php` so the first paint is already correct. The display timezone is **not** covered by this story — US-7 owns it — and the key set is a contract, not a free-form blob. [ADR-0001 §3]                                                                                                                                            |
| US-12   | As a Trainer, I record which six support cards a run was equipped with, so a logged run can be re-read later instead of relying on memory.                                                                                                                                   | P1         | The run page shows a Support deck block with one picker per slot; saving stores six rows at their positions and re-saving moves a card without leaving it in the slot it left; the same card in two slots is refused with the client's own reason; slot six is labelled `Friends` whichever card sits there; a card linked to the run's scenario is badged from the scenario list, not from a stored flag; a run with nothing recorded names the absence rather than showing empty frames; and no card level, limit break or Unique Perk state is stored anywhere (identity, not collection). [ADR-0014]                          |
| US-13   | As a Trainer, I set a build target for a run — purpose, distance, surface, running style, per-stat targets, and skill priorities — so later guidance knows what I am building.                                                                                                | P1         | A run stores an optional build target; the run page renders it; a target stat is validated against the run's own ceiling (C-2); a run with no target names the absence rather than inventing one. [ADR-0020; ADR-0015]                                                                                                                                                                                                                                                                                                                                                                                      |
| US-14   | As a Trainer, I see a recommended next action with its reason, so I can decide faster without the app deciding for me.                                                                                       | P1         | The advisor ranks Trainer-entered options against the run's build target and the `ADR-0001` Energy constants; every suggestion carries a derivable reason line; a numeric estimate is opt-in, shows its formula and parameters, and is labelled as this tool's model, not the game's; nothing computes or implies a race outcome (§6.11 unchanged). [ADR-0020; ADR-0001]                                                                                                                                                                                                                                          |
| US-15   | As a Trainer, I save a completed run as a reusable parent record, tagged and searchable, so my own history feeds the next Legacy selection.                                                                                                                                   | P1         | A completed run can be saved to the Veteran library with tags and notes; the library is searchable by trainee, sparks, distance, style, and tags; no inheritance outcome is computed (§6.3 engine ban unchanged). [ADR-0020; ADR-0010]                                                                                                                                                                                                                                                                                                                                                                        |

## 4. Functional Requirements

### FR-A: Catalog domain

- A-1: `Umamusume` record: slug, English display name, Japanese name, normalized match key, release status enum
  (`GlobalReleased`, `GlobalAnnounced`, `JapanOnly`), nullable JP debut date, nullable Global debut date,
  `is_manual` flag.
- A-2: Alias records per Umamusume (alias string + language), unique per (alias, language).
- A-3: Catalog index with filter by release status and text search over names and aliases (search on normalized
  keys, not raw display strings).
- A-4: Every catalog record exposes its provenance: source URL, fetched timestamp, snapshot reference, match
  confidence.
- A-5: `Umamusume` stores the ten aptitude letters (turf, dirt, four distance bands, four running styles)
  exactly as the declared source publishes them, in that order. A `Scenario` record stores its five per-stat
  caps, the source's hard cap, both server start dates and provenance. Reference data only: nothing in this
  requirement computes a race or a training outcome. [ADR-0004]
- A-6: `CharacterCard` record: the source's own card id (unique), its Umamusume, the `[Global]` client title
  verbatim including its brackets, rarity, Global release date, a debut-form flag derived from the earliest JP
  release among that trainee's cards, and an `unconfirmed` flag. Provenance is inline on the card row:
  `source_url`, `snapshot_path`, `fetched_at`, `source_timezone`, plus the card's own `is_manual` so a
  hand-correction to one card is immutable to the engine without claiming that trainee's whole record (**B-4**).
  Only cards carrying a Global release date are stored; a trainee with no Global card does not appear in the
  catalog. A card confirmed by the Tier B source alone is stored flagged and hidden unless asked for.
  [ADR-0008]
- A-7: A trainee profile block on the sibling `umamusume_profiles` table, one row per trainee, carrying **four**
  fields: **voice actor**, **birthday**, **height**, and **three sizes**. Both voice-actor fields are stored
  because the source records the credit in two scripts of one name: `va_ja` the Japanese credit (`和氣あず未`)
  and `va_en` its romanisation (`Azumi Waki`); `va_en` is a romanisation, not a second cast, and where a stage
  name is already romanised the two hold the identical string, which no separate dub cast could produce. A
  block that showed only `va_ja` would render Japanese script to a Global reader. Every part is nullable:
  measured across the 105 `race === 'uma'` rows this source keeps, `va_en` is absent on 3, `three_sizes` on 7
  and `birth_year` on 7, so a NOT NULL column would make the parser invent a value, and an absent part renders
  as a named absence rather than a blank or a guess. Sibling table rather than columns on `umamusume` because
  the source is one document about one trainee. Provenance inline per **A-4**, with its own `is_manual` per
  **B-4**. Reference data only, on **A-5**'s precedent: nothing here computes a run outcome and §6.11 stays
  untouched. Declared as source `gametora-character-profiles` per **B-1**, routed as a fourth branch in
  `PipelineRunner` per **B-2**. `name_ja` is stored for the same reason the card row keeps its own fields, but
  it is **A-1** that requires the Japanese name to exist on the `Umamusume` record, not this line.
  **Three grains, deliberately not collapsed.** The four named above are this requirement's grain. The detail
  view renders **six** rows, because the page sets the profile beside two fields the profile does not own:
  `Japanese name` from the `Umamusume` record and `Release date` from `global_debut_date`, both per **A-1**.
  `tests/Feature/CatalogDetailPageTest.php` asserts all six. Storage splits further still: the migration
  `2026_09_29_182820_create_umamusume_profiles_table.php` carries `name_ja`, `va_ja`, `va_en`,
  `birth_year`/`birth_month`/`birth_day`, `height` and `three_sizes_b`/`_h`/`_w`, plus the provenance set and
  `is_manual` — and whether the birthday triple counts as one field or three is a column choice this
  requirement does not make. [ADR-0012 Decision 4]
- A-8: `SupportCard` record: the source's own `support_id` (unique), the export's `char_id` **as a plain
  column**, the character name and the card's bracketed title as two separate fields, rarity (the source's
  1/2/3), type (one of the seven export keys `speed`, `stamina`, `power`, `guts`, `intelligence`, `friend`,
  `group`), both server release dates, and the effect anchor vector verbatim. `SupportEffect` is the 35-row
  dictionary that names those anchors.
  **Three limits are part of this line, not footnotes.** (i) `char_id` addresses the *source's* character space
  and is never a foreign key to `umamusume.id`: that column is a local surrogate, the source id lives in
  `external_ref`, and 23 of the 559 records name 9000-block staff who are not trainees at all. Those 23 are
  also the records a Scenario Link is derived from, so the constraint would have deleted the evidence rather
  than cleaned the data. (ii) **No composed card name is stored**, because the source publishes none; the
  label is assembled at the view boundary from the two fields it does publish. (iii) **Effect levels are not
  expanded.** The eleven anchors per effect are stored as the source sends them and any intermediate value is
  interpolated on read; a materialised per-level table would be derived rows that can silently disagree with
  the rule that produced them. The client's rarity word (`R`/`SR`/`SSR`) and type word (`Wit` for
  `intelligence`, `Pal` for `friend`) are display mappings, not stored values — **A-1**'s precedent.
  Availability follows **D-3**'s rule: every record the source publishes is stored, and a Global-facing surface
  offers only rows carrying a Global release date. Provenance inline per **A-4**, with its own `is_manual` per
  **B-4**. Reference data only, on **A-5**'s precedent: no card tier, no strength score, no training-yield
  calculation, and nothing here touches §6.11. [ADR-0014]
- A-9: The Scenario Link is **derived on read**, never stored on the card. It is the card's character being on
  the running scenario's linked list, so the same card is linked in one scenario and not in another, and a flag
  on the row would be wrong the moment the run's scenario changed. The list is held in `config('scenarios.php')`
  keyed by character name. Known and accepted gap: a `group` card represents several characters but carries
  one, so a group card badged on a member who is not its representative would be missed; no captured frame yet
  shows one badged. [ADR-0014]
- A-10 [US-10]: Each trainee's own career objectives are engine-owned reference data on `trainee_goals`, one row
  per objective: the race it names (or no race, for a fan-count objective), the requirement the client states,
  the career year and the deadline turn, and the source's own condition figures kept raw. **Two kinds are stored
  and they are genuinely different** — a race objective names a race and a placing, a fan objective names a
  threshold and occupies no race. Objectives are **per trainee**, not per costume card and not per scenario:
  one ladder serves URA Finale, Unity Cup and Our Grand Concert, and `trackblazer` composes none because that
  scenario replaces race goals with Grade Point deadlines. A run's progress against an objective is **derived**
  from the race entries and turn readings the Trainer already records, never re-entered, so no second owner for
  the same fact exists and no write path is added here. Availability follows **A-8**'s rule: every objective the
  source publishes is stored, and a Global-facing surface prints a race only where `race_catalog_slots` carries
  it, so an objective on a race Global has not shipped stays stored and renders as the source naming it, not as
  a Global obligation. Provenance is inline per **A-4**, with its own `is_manual` per **B-4**, and the fetch
  half is ordinary **B-1** (one declared source, one parser class), which is why this line sits in FR-A rather
  than FR-B. *Scope note for US-10.* US-10's acceptance test names `scenario_slots` seeding, which is the
  **scenario-wide** half of "race goals" and is what the mandatory-race module renders. This line is the
  **per-character** half that test never reached: the two are separate facts and neither substitutes for the
  other. Nothing here computes whether an objective *can* be met — §6.11 stands, and no surface prints a
  probability, a score or a suggested entry. [**ADR-0026**]

- A-11: Portrait form selection (Decision 9 ruling, recorded 2026-10-18). Every surface that renders a
   trainee portrait without an explicit Trainer choice — the catalog index header frame, the catalog
   detail Identity frame, the career trainee profile frame, and the pre-run trainee-select frame — MUST
   render the trainee's *active form*, the card flagged is_debut_form per **A-6**, and where no card
   carries the flag, that trainee's *first* form. No surface selects the frame by rarity or any other
   criterion: the catalog badge keeps naming the top rarity, but the badge and the frame are different
   answers and may name different cards. Where a surface lets the Trainer choose a form explicitly (the
   catalog detail's form query parameter), the choice governs; the ruling fixes the default, not the
   choice. State at the ruling: the index, profile, and select surfaces already resolve the flagged card
   with the first card as fallback, while the catalog detail's no-parameter default resolves to the first
   card, so the index and the detail can still show different portraits of the same trainee until that
   default is consolidated. The consolidation is the Phase 5.4 code item recorded in the execution
   ledger's deferral list, and this line is the specification it implements. Rationale: the pre-ruling
   state showed two images of one trainee on adjacent pages; one uniform default makes the screens agree
   by construction.
### FR-B: Data-fetching & cross-reference engine

- B-1: `uma:fetch {source}` console command; sources are declared in `config('uma.sources')`, each with a parser
  class.
- B-2: Pipeline stages: fetch (HTTP with per-source delay and retry) → snapshot (raw body to disk) → parse
  (per-source, isolated) → normalize (NFKD match key) → match (Exact / Alias / Fuzzy / None tiers) → promote or
  review.
- B-3: Only Exact and Alias matches auto-promote. Fuzzy and None become `MatchCandidate` review rows with the
  proposed data and confidence.
- B-4: `is_manual` rows are immutable to the engine.
- B-5: Per-source cache lock prevents concurrent fetches of the same source; fetches are idempotent: re-running
  with unchanged source data changes nothing.
- B-6: JP-source datetimes convert from `Asia/Tokyo` to UTC at parse time; the source timezone is recorded.

### FR-C: Training-run domain (Trainer's own data)

- C-1: `TrainingRun`: belongs to one Umamusume — `umamusume_id` remains the required owner of a run — optional
  scenario name, status enum (`Active`, `Completed`, `Retired`), optional two inheritance parents (Umamusume
  references), free-text notes, an optional reference to the `CharacterCard` the run was started on, and four
  optional per-stat ceilings (`cap_speed`, `cap_stamina`, `cap_power`, `cap_guts`), each one the figure the
  Trainer read off the client, entered and never derived. Wit carries no entered ceiling because no ruled
  surface writes it. These are Trainer data the engine never writes. [ADR-0008; ADR-0025]
- C-2: `TurnEntry`: run + turn number (unique per run among rows where `corrected_from IS NULL`, with a plain
  lookup index for corrected history), five stat integers, SP integer, optional condition string,
  `energy_state`/`energy_band`, `facility_speed`/`stamina`/`power`/`guts`/`wit` (write path unwired),
  `failure_rate`/`preview_gains` (transcribed client readings), and
  `choice`/`decision_snapshot`/`corrected_from`/`superseded_by` (Phase G ruling D-1a–D-1d, **not yet filed as an
  ADR — governance gap**). `Turn >= 1`. A stat is validated against the ceiling that governs its run: a
  ceiling the Trainer entered for that run's stat answers first, so a stat is accepted to the figure the client
  showed; an entered figure is bounded 1..`hard_cap`, may not sit below the highest value the run already
  recorded for that stat, and where the run states none the fallback is `base_cap` plus that scenario's per-stat
  bonus, clamped to the engine `hard_cap`. `ScenarioCaps::forRun()` is the single ceiling owner and reports
  which of the two answered. The 1,200 halved-gains line and the scenario ceiling are two separate visible markers per ADR-0002's UI condition. SP is non-negative and uncapped: no source states an SP ceiling. [ADR-0024;
  ADR-0015; ADR-0025]
- C-3: Skill acquisition: run × skill with status (`Suggested`, `Acquired`, `Skipped`) and optional turn
  acquired. `Suggested` = planned before the run; `Acquired`/`Skipped` = outcome.
- C-4: Runs and turns are creatable, editable, and deletable through the web UI; all writes validated by Form
  Requests.
- C-5: CSV and JSON export per run.
- C-6: `DeckSlot`: run × support card at a `slot_position` of one to six, unique per (run, position). The deck
  is written as a whole — the six slots are replaced in one transaction — because a slot is a *position*: a card
  moving from two to three must leave two, and an upsert keyed on the card would leave it in both. Position six
  is the friend slot and **the role belongs to the slot, never to the card**: the client labels that position
  `Friends` and any card may occupy it, so a stat card parked at six is a legal deck and no `is_friend_card`
  flag exists. Two copies of one card cannot be equipped together, so a duplicate submission is refused rather
  than stored. An empty deck is a valid state and renders as a named absence: the deck is something only the
  Trainer can supply. [ADR-0014]

### FR-D: Skill catalog (reference data)

- D-1: `Skill`: English name, Japanese name (nullable until cross-referenced), match key, SP cost (nullable),
  type string (nullable), `is_unique` flag, **`rarity`**, **the export's own skill id**, and the provenance
  columns **A-4**'s owner `ADR-0004` requires. Two limits are part of this line, not footnotes: `rarity` stores
  the source's class code and is **never** rendered as a client rarity word, because the source carries six
  class values where the client's three rarities live elsewhere; and `type` holds **this tool's derived
  classification** from the source's effect codes, never client copy (`CONSTRAINTS.md` D-20). The English name
  is the source's localized client string, not its literal rendering of the Japanese — the two differ on 535 of
  623 rows. [ADR-0011; ADR-0004]
- D-2: Skill search/autocomplete for the run UI, on normalized keys.
- D-3: Skills arrive through `uma:fetch` from a declared source, and the source's own server flag decides
  `[Global]` availability. The flag is **recorded at write time and applied at read time**: every row the source
  publishes is stored, and only rows the source states as available on `[Global]` may reach a Trainer-facing
  surface. Dropping the rest at import would leave the excluded population uncountable and the provenance
  unverifiable. [ADR-0011]

### FR-E: Local API (P2)

- E-1: Versioned `/api/v1` read endpoints for catalog and runs, JSON via API Resources, one consistent error
  shape (`{ "error": { "code", "message" } }`), offset pagination on list endpoints.

### FR-F: Build target and Trainer Advisor [ADR-0020; extends ADR-0001]

- F-1: `BuildTarget`: an optional per-run record — purpose, distance, surface, running style, the five per-stat
  targets, and an ordered skill-priority list. Trainer-entered, so `is_manual` by definition; the engine never
  writes it.
- F-2: The Trainer Advisor ranks the Trainer-entered next-turn options against the build target and the
  `ADR-0001` Energy constants. It is deterministic over entered `turn_entries` plus a declared, versioned,
  config-stored constant set, never hardcoded.
- F-3: Every suggestion carries a row-level reason line derivable from the constants; a suggestion with no
  derivable reason does not ship. A numeric estimate is opt-in, renders with its formula and parameters on the
  same surface, and is labelled as this tool's model, not the game's. Figures are marked with the provenance
  vocabulary (confirmed / calculated / RNG / user input) so none reads as more certain than its provenance.
- F-4: No race outcome is computed or implied; §6.11 stands for race outcomes.

### FR-G: Veteran library (record-only) [ADR-0020; under ADR-0010]

- G-1: `Veteran`: a record built from a completed run — its trainee, final stats, skills, sparks, and race
  record, plus Trainer-supplied tags and notes. Recorded, not derived.
- G-2: The library is searchable and filterable by trainee, stat sparks, distance, style, and tags.
- G-3: A Veteran may be recorded as a parent in a new run's Legacy selection, so the library feeds the next run.
  The exact reference mechanism is a schema decision for the subsystem slice.
- G-4: No inheritance computation: no Spark-firing prediction, no affinity payout, no expected-inheritance
  figure. §6.3's engine ban is unchanged.

## 5. Non-Functional Requirements

- NFR-1 **Locality**: runs fully on the Trainer's machine. SQLite is the only production driver. No account, no
  auth surface, no telemetry, no outbound calls except declared fetch sources.
- NFR-2 **Offline resilience**: after any successful fetch, the catalog and all Trainer data work with zero
  connectivity. A failed fetch degrades to a log entry plus retained previous data; it never blanks or corrupts
  the catalog.
- NFR-3 **Performance (local budget)**: catalog index renders in under 200 ms against a fully populated catalog
  (~1,000 Umamusume, ~2,000 skills) on SQLite with indexes; a single-source fetch respects its configured
  inter-request delay and completes without memory growth across sources (snapshots stream to disk, not memory).
- NFR-4 **Data integrity**: multi-write operations run inside `DB::transaction`; SQLite in WAL mode with
  `busy_timeout` so the web request and the queue worker coexist; every engine-written fact is traceable to a
  snapshot.
- NFR-5 **Durability of Trainer data**: `uma:backup` produces a consistent single-file snapshot through
  `VACUUM INTO` on its own PDO connection (`app/Actions/BackupDatabase.php`), which reads inside a transaction
  of its own and leaves the source database untouched; snapshot names are written in UTC
  (`now()->utc()->format('Ymd-His')`). Trainer data (runs, turns, manual edits) is never deleted or overwritten
  by the engine.
- NFR-6 **Lore integrity**: zero equine vocabulary for characters in code, data, docs, and UI (enforced by the
  banned-pattern grep in `tools/lore.php` and the Lore Guardian role in AGENTS.md).
- NFR-7 **Maintainability**: PHPStan level 6 clean (existing baseline), Pint clean, Pest tests for every domain
  behavior; parsers isolated so a source change touches one class.
- NFR-8 **Runtime responsiveness and navigation**: Trainer Desk is a local interactive application. Normal
  Inertia navigation, page refresh, and global navigation must settle deterministically without redirect loops,
  reload loops, permanently pending requests, or blank screens. Global loading state must terminate on success,
  validation failure, server failure, network failure, cancellation, or aborted navigation. Normal page rendering
  must not initiate data-fetching, import, artwork-mirroring, backup, or other long-running background
  operations. Trainer-facing Inertia pages must use bounded, route-specific payloads; large reference
  collections must not be serialized into unrelated page props. Important routes must have automated regression
  coverage for successful and failed navigation, refresh behavior, and loading-state termination. Performance is
  controlled through bounded server payloads, bounded queries, controlled reactivity, lifecycle cleanup, and
  route-specific data loading (NFR-18 owns the page-count rule).
- NFR-9 **Inertia/Vue navigation determinism**: one authoritative navigation mechanism. A successful navigation
  settles; no `navigation -> watcher -> navigation`, `request -> success -> reload -> request`, or
  `route change -> mount -> visit -> route change` cycle. A `Link` prefetch is not a navigation and must never
  set a page's loading state, and `router.on` listeners are registered in one place rather than copied per page.
- NFR-10 **Bounded page payloads**: every Trainer-facing Inertia page sends only the fields it renders. No
  complete model graph, no unbounded collection, and no shared prop beyond `app` and `flash`. The global
  navigation receives no catalog, skills, races, supports or runs, and the Cockpit receives a bounded slice
  rather than the whole run history.
- NFR-11 **Query boundedness and N+1 prevention**: important routes carry a query-count regression test with a
  fixed ceiling (Dashboard, Careers index, Cockpit, Career Result, Catalog index and detail, Skills, Support
  Cards, Legacy and Veterans, Race Decision, Scenario Race Planner). A collection the Trainer browses is
  paginated, and a column that is filtered or sorted on carries an index.
- NFR-12 **Background-work isolation**: fetch, snapshot, parse, normalize, match, artwork mirroring, import and
  backup never run during a page render. They are command-invoked only, and a page never waits on one.
- NFR-13 **Loading and error state termination**: every async surface exposes idle, loading, success and error
  (and cancelled where applicable) and cannot remain in `Loading…` without resolving or showing an error.
  Termination is driven by the lifecycle, not by an arbitrary timeout.
- NFR-14 **SQLite concurrency**: WAL mode and `busy_timeout` stay configured. A write transaction is held no
  longer than the write itself, and no network call or expensive filesystem operation runs inside one.
- NFR-15 **Client-side reactivity budget**: no deep watcher over a whole prop, no watcher that mutates its own
  source, no expensive work in a template expression, and no large immutable dataset held in a deep `ref`.
  Sorting and filtering a large collection happens server-side.
- NFR-16 **Resource lifecycle cleanup**: every component that creates a timer, event listener, observer,
  subscription or `AbortController` releases it on unmount, so repeated navigation cannot multiply them.
- NFR-17 **Asset and artwork loading resilience**: an unavailable artwork file renders the text-only fallback and
  can never block a page render, fail navigation, raise a page-wide exception, or start a retry loop.
  `ArtworkSlot.vue` consumes the local mirror only and never fetches.
- NFR-18 **Build and bundle discipline**: pages are code-split per route and the bundle is measured; no
  server-side dataset and no development-only code ships in it. The number of Vue pages is not itself a
  constraint — per-page work is.
- NFR-19 **Local observability**: a development-only diagnostic mode can report the current route, the Inertia
  component, request duration, query count and payload size. No telemetry, analytics or user data leaves the
  machine.
- NFR-20 **Regression protection**: the failure modes above are covered by automated tests — navigation resolves,
  a failed navigation clears its loading state, a refresh does not loop, an artwork miss does not block a
  render, important routes stay within their query ceiling, and no page request triggers background work.
  Existing Phase A–E tests stay green.

  **Gating decision (2026-10-18, owner ruling):** items 1–4 (navigation resolves, failed navigation clears
  loading state, artwork miss does not block render, query ceiling holds) are required gates for Phase 1.
  Items 5 and 6 (no page request triggers background work; loading/error state termination) are acknowledged
  as known gaps. Browser tests run developer-locally only; the repository has no CI (`.github/workflows/`
  absent), so their results are not continuously verified.
## 6. Non-Goals (eliminated legacy features)

Explicitly not built, with the legacy feature they replace. The numbering is load-bearing: `AGENTS.md`,
`CLAUDE.md`, `DESIGN.md`, `README.md`, `SCREEN_SPEC.md` and several ADRs cite these items by number.

1. **No authentication or multi-user anything** (replaces uma-tracker's Breeze stack, dormant Sanctum in two
   repos). One Trainer, one machine.
2. **Frontend stack**: the shipped surface is Inertia + Vue 3, adopted for the interactive-cockpit interaction
   model; uma-companion's Vue 3 + Pinia scaffold is not revived and no separate SPA product is added. The
   accessibility behavior regression-tested on the earlier Blade stack (keyboard paths, target sizes,
   review-form labels, focusable scroll regions) is a carry-across obligation, not an option. [ADR-0020]
3. **No breeding/pairing engine** (replaces uma-companion's sire × dam system, whose vocabulary was also a lore
   violation). Inheritance is recorded, not computed: two optional parent references on a run plus what the
   Legacy Select screen displays, stored in `training_runs.legacy_selection`, and **nothing more** — that "more"
   is computation. The engine stays banned: nothing predicts a Spark firing, an affinity payout, or an
   offspring, and no outcome is derived from a recorded selection. A completed run may be saved as a tagged,
   searchable parent record in the Veteran library under that recording allowance (FR-G), with the same
   computation ban. [ADR-0010; ADR-0020]
4. **No EAV attribute storage** (replaces umamusume-tracker-app's legacy `attributes` table). Fixed stat
   columns.
5. **No Excel export, no `maatwebsite/excel`** (replaces both Laravel 11/12 trackers' export stacks). CSV/JSON
   only.
6. **No event/banner calendar in Phase 1** (uma-companion's events domain). Revisited in a later phase once the
   fetch engine has proven reliability.
7. **No legacy database migration tooling.** The legacy apps keep their own data; Trainers export from them by
   hand if needed. No promise to import `uma_musumes`, `plans`, or EAV rows. The career-run planner repo does
   contain working CSV/JSON importers (including for a further legacy repository's `uma-run-tracker` JSON
   shape); they are INVESTIGATE reference code, located and cited if import is ever requested, not carried into
   Phase 1.
8. **No MySQL/PostgreSQL support.** SQLite only; the legacy MySQL-only DDL (stored/virtual columns,
   `ALTER TABLE ... COMMENT`) is not carried over, and neither are the career-run planner repo's DB-level enum
   columns: string columns + PHP backed enums instead.
9. **No support-card collection.** In scope: `support_cards` as reference data, `support_effects` as the effect
   dictionary, and `deck_slots` for the six cards a Trainer equipped for one run (A-8, C-6). Cut: **no
   collection** — no `user_support_cards`, no card levels, no limit breaks, no Unique Perk state, because that is
   uma-tracker's abandoned promise and no user story replaced it. Card *tier* labels are held for want of a
   current Global source, not for want of authorization. [ADR-0014; ADR-0008]
10. **No hosting, deployment, or cloud path.** `deploying-to-cloud` and friends do not apply. The deliverable is
    a local `composer run dev`.
11. **No race simulation, prediction engine, or race-day snapshots.** No race outcome, win probability or
    race-day snapshot is computed or implied anywhere in this product, and the reaffirmed `ADR-0016` data
    blocker stands. Run math stays deterministic over Trainer-entered turns. Training guidance is a separate
    object and is authorized: the Trainer Advisor ranks Trainer-entered options against a Trainer-entered build
    target (FR-F), carrying all of `ADR-0001`'s guardrails forward. [ADR-0001; ADR-0016; ADR-0020]
12. **No dual storage modes, no browser-side authoritative data.** SQLite is the single store.
13. **No trainee image uploads.** No user story; avoids an upload surface on a local tool.

`ADR-0021` authorizes a different object and does not lift non-goal 13: game artwork the tool fetches itself by
id from an allowlisted asset host. There is no file input, no user-supplied art, and no route that accepts an
upload; see OQ-6. [ADR-0021]

## 7. Open Questions

The register — question, status, owner, and where each was resolved — is
[docs/OPEN-QUESTIONS.md](docs/OPEN-QUESTIONS.md), which carries the answered-and-remainder detail for OQ-5
and OQ-6. The history of how each entry got its current shape is in [CHANGELOG.md](CHANGELOG.md). One line
each here:

- OQ-1 **CLOSED 2026-09-27**: the product name is **Trainer Desk**, owner decision recorded in `DESIGN.md`;
  `APP_NAME` in `.env` should be set to match at the next config change.
- OQ-2 **PARTIALLY RESOLVED 2026-09-28**: first source approved, GameTora structured JSON datasets, recorded in
  `config/uma.php` (`gametora-characters`, entry dated 2026-09-27). Open: robots/live availability verification,
  refresh/schedule policy, additional source selection, parser maturity, and promotion policy for data types
  beyond characters. Each addition stays a legal/robots.txt review plus one parser class, and the Trainer picks.
- OQ-3 **OPEN**: whether `uma:fetch` runs on the Laravel scheduler by default or only manually. Default: manual,
  until rate-limit behavior of chosen sources is observed.
- OQ-4 **CLOSED 2026-09-27 by the owner**: aptitude grades and stat caps enter as engine-owned facts with
  provenance. Scope of the closure is exactly what `ADR-0004` implements, the ten aptitude letters (FR-A-5) and
  the per-scenario stat caps (FR-A-5, `scenarios` table), both arriving through `uma:fetch` against a declared
  source. Growth rates and base stats stay out: nothing in this PRD requires them yet, and `ADR-0004` leaves them
  unimplemented rather than seeding them by hand.
- OQ-5 **OPEN 2026-10-01**: should the skill screen surface a skill's running-style eligibility, and is that a
  feature this product wants at all? Answered in the negative for now: verdict (c), do not ship a skill
  recommendation, and eligibility must not be relabelled as one. Open remainder: the style number-to-label
  mapping from client strings, the derived-at-read-time-only shape, and the 19.7%-coverage absence reading.
- OQ-6 **OPEN, placement answered for four of five surfaces 2026-10-05**: which artwork families does the mirror
  display, and on which screens? Answered and built: catalog index (trainee header and costume-form row),
  trainee detail Identity section, support-card index and support-card detail, every slot purely visual. Open
  remainder: the pre-run pick screen (native `<select>` content model), skill icons (needs a `skills.iconid`
   migration), and the top-rarity-versus-active-form portrait inconsistency between catalog index and detail
   is resolved by the 2026-10-18 ruling recorded in **A-11**: every surface defaults to the active form or
   the first form, and the code consolidation is the Phase 5.4 item A-11 names.

## 8. Success Criteria (Phase 1 exit)

- US-1..US-3 demonstrably work end-to-end against seeded data (`php artisan test --compact` green, including a
  pipeline test with a faked HTTP source).
- Migrations, factories, and seeders run clean on a fresh SQLite file.
- Zero banned-pattern hits (lore grep, see `tools/lore.php`) across repo text.
- PHPStan level 6 clean; Pint clean.
