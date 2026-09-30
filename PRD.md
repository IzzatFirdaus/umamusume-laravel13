# PRD: Trainer Desk (consolidated Umamusume trainer tool)

Status: Phase 1 (documentation & scaffolding). Revision 0.2 (fourth legacy repository integrated; sections changed by it are marked [rev 0.2 — repo #4]). Product name: **Trainer Desk**, owner decision 2026-09-27, closing OQ-1.

## 1. Product Vision

One local-only tool that replaces four abandoned trackers [rev 0.2 — repo #4]: a Trainer browses a catalog of Umamusume whose JP and Global data is cross-referenced automatically, logs training runs turn by turn, and exports the result. It runs entirely on the Trainer's machine (SQLite, no server, no accounts), it works offline against cached data, and every fact it did not get from the Trainer carries a source citation and a fetch timestamp.

## 2. Target Audience

**Trainers**: players of the Global English version of *Umamusume Pretty Derby*, playing on JP-server knowledge from community wikis to plan ahead. Profile:

- Plays Global; reads JP release information to predict upcoming content.
- Runs the tool locally, single user, desktop browser.
- Tolerates an artisan command; does not want to operate a database server.
- Currently keeps track of runs in spreadsheets or in one of the four legacy apps this consolidates [rev 0.2 — repo #4]. The fourth (a career-run planner) proves the planning workflow: Trainers pre-pick skills before a run and compare plan vs actual afterwards.

The Umamusume are a humanoid race of girls; nothing in this product refers to them with equine vocabulary (see Lore Rules in CLAUDE.md).

## 3. Core User Stories (prioritized)

P0 = Phase 1 ships without it = failure. P1 = Phase 1 target. P2 = later phase, design must not block it.

| ID | Story | Priority | Acceptance test |
|---|---|---|---|
| US-1 | As a Trainer, I browse the catalog of Umamusume with names in English and Japanese, filtered by Global release status, so I can see who is available to me. | P0 | `GET /umamusume?status=GlobalReleased` lists only released entries; each detail page shows `name`, `name_ja`, release status, and provenance (source URL + fetched date). `GET /umamusume` lists each Global-released trainee with her cards nested; each card row shows its client title, rarity and Global release date. |
| US-2 | As a Trainer, I run a fetch that cross-references JP-source data against Global data, so new JP releases appear with a "JapanOnly" or "GlobalAnnounced" flag instead of silently missing. | P0 | `php artisan uma:fetch {source}` stores raw snapshots, creates or updates only Exact/Alias matches, routes Fuzzy/None to the review list, and never modifies rows with `is_manual = true`. |
| US-3 | As a Trainer, I create a training run for an Umamusume and log stats per turn (Speed, Stamina, Power, Guts, Wit, SP), so the app keeps the history my spreadsheet used to. | P0 | Creating a run and posting turns 1..N renders the run page with all turns in order; duplicate turn number for the same run is rejected with a validation error; a stat above its scenario's own ceiling is rejected [rev 0.2 — repo #4]. **Superseded in part 2026-09-30 by `ADR-0015`: the flat 0..1200 test becomes a per-stat, per-scenario ceiling; a run that names no scenario is still held to 1200, and SP carries no ceiling.** |
| US-4 | As a Trainer, I record which skills I plan to acquire, actually acquired, or skipped during a run, so I can compare planned vs actual builds [rev 0.2 — repo #4]. | P1 | A skill can be attached to a run with status Suggested, Acquired, or Skipped and an optional turn number; the run page lists all three groups. |
| US-5 | As a Trainer, I resolve the review list: confirm, alias, or reject a proposed JP↔Global match, so the catalog improves without the engine guessing. | P1 | Review page lists unmatched candidates; confirming an Exact-tier candidate merges it; adding an alias makes future fetches match automatically. |
| US-6 | As a Trainer, I export my runs as CSV or JSON, so my data is never trapped in the app. | P1 | `GET /training-runs/{run}/export.csv` and `.json` download the run with its turns and skills. |
| US-7 | As a Trainer, I read JP-sourced dates in my own timezone, so event and debut timing is unambiguous. | P1 | A JP-source datetime is stored UTC with source timezone recorded; UI renders it in `config('uma.display_timezone')`; date-only values render identically in every timezone. |
| US-8 | As a Trainer, I edit catalog data by hand (correct a name, add an alias) and my edit survives future fetches. | P1 | Setting `is_manual` on a row makes `uma:fetch` skip it; a test asserts the row is unchanged after a fetch. |
| US-9 | As a Trainer, I query a local JSON API (`/api/v1`) for catalog and run data. | P2 | API Resources return the documented shapes; version prefix is enforced. |
| US-10 | As a Trainer, I track race goals and predictions per run. | P1 | Race calendar and fan gating via `scenario_slots` (ADR-0003); mandatory/optional races with fan/maiden gates per scenario. Predictions (user-entered aptitude grades, race-day snapshots) remain deferred per §6.11. Acceptance: (1) `scenario_slots` seeded for all scenarios with `kind` ∈ {goal_race, team_race, grade_deadline, scripted_event}; (2) `RaceEntry` references `scenario_slot_id`; (3) Run detail renders the calendar panel with gates and the grade meter where applicable; (4) No prediction engine or simulation. |
| US-11 | As a Trainer, my UI preferences survive a restart, so the app looks and behaves the way I left it. Authorized 2026-09-27 for two keys only: the theme (`light` / `dark` / follow the OS) and the numeric failure-estimate toggle (off by default, `ADR-0001` §3). | P1 | A preference written through the app is stored in the `preferences` table and read back on the next request; **no preference is stored in browser storage**, because §6 non-goal 12 cuts a second source of truth; the stored theme is rendered server-side by `components/layout.blade.php` so the first paint is already correct. The display timezone is **not** covered by this story — US-7 owns it — and the key set is a contract, not a free-form blob. |
| US-12 | As a Trainer, I record which six support cards a run was equipped with, so a logged run can be re-read later instead of relying on memory. Authorized 2026-09-30 by the owner's Slice 2 ruling, which supersedes `ADR-0005` and lifts the first half of §6.9 (`ADR-0014`). | P1 | The run page shows a Support deck block with one picker per slot; saving stores six rows at their positions and re-saving moves a card without leaving it in the slot it left; the same card in two slots is refused with the client's own reason; slot six is labelled `Friends` whichever card sits there; a card linked to the run's scenario is badged from the scenario list, not from a stored flag; a run with nothing recorded names the absence rather than showing empty frames; and no card level, limit break or Unique Perk state is stored anywhere (`ADR-0014`: identity, not collection). |

## 4. Functional Requirements

### FR-A: Catalog domain
- A-1: `Umamusume` record: slug, English display name, Japanese name, normalized match key, release status enum (`GlobalReleased`, `GlobalAnnounced`, `JapanOnly`), nullable JP debut date, nullable Global debut date, `is_manual` flag.
- A-2: Alias records per Umamusume (alias string + language), unique per (alias, language).
- A-3: Catalog index with filter by release status and text search over names and aliases (search on normalized keys, not raw display strings).
- A-4: Every catalog record exposes its provenance: source URL, fetched timestamp, snapshot reference, match confidence.
- A-5 [ADR-0004]: `Umamusume` stores the ten aptitude letters (turf, dirt, four distance bands, four running styles) exactly as the declared source publishes them, in that order. A `Scenario` record stores its five per-stat caps, the source's hard cap, both server start dates and provenance. Reference data only: nothing in this requirement computes a race or a training outcome (CLAUDE.md Planner Rule 6).
- A-6 [ADR-0008]: `CharacterCard` record: the source's own card id (unique), its
  Umamusume, the `[Global]` client title verbatim including its brackets, rarity,
  Global release date, a debut-form flag derived from the earliest JP release
  among that trainee's cards, and an `unconfirmed` flag. Provenance is inline on the
  card row, as the sibling reference tables do (`ADR-0003` Amendment R3;
  `scenario_races`, `scenario_slots` and `race_catalog_slots` carry all four fields,
  `scenarios` predates the set and carries three): `source_url`, `snapshot_path`, `fetched_at`,
  `source_timezone`, plus the card's own `is_manual` so a hand-correction to one card
  is immutable to the engine without claiming that trainee's whole record (B-4).
  Only cards carrying a Global release date are stored;
  a trainee with no Global card does not appear in the catalog. A card confirmed by
  the Tier B source alone is stored flagged and hidden unless asked for.
- A-7 [ADR-0012 Decision 4; owner ruling 2026-09-30 under `AGENTS.md` escalation 2]: a trainee
  profile block on the sibling `umamusume_profiles` table, one row per trainee, carrying **four**
  fields this PRD did not name before this line: **voice actor**, **birthday**, **height**, and
  **three sizes**. Both voice-actor fields are stored because the source records the credit in two
  scripts of one name: `va_ja` the Japanese credit (`和氣あず未`) and `va_en` its romanisation
  (`Azumi Waki`); where a stage name is already romanised the two hold the identical string, which no
  separate dub cast could produce. *[Dated erratum 2026-09-30: the first draft of this line said the
  source "states a Japanese and an English one and they disagree about who speaks for her." The body
  refutes it — `va_en` is a romanisation, not a second cast — and a block that showed only `va_ja`
  would render Japanese script to a Global reader.]* Every part is nullable: measured across the 105
  `race === 'uma'` rows this source keeps, `va_en` is absent on 3, `three_sizes` on 7 and
  `birth_year` on 7, so a
  NOT NULL column would make the parser invent a value, and an absent part renders as a named
  absence rather than a blank or a guess. Sibling table rather than columns on `umamusume` because
  the source is one document about one trainee. Provenance inline per **A-4**, with its own
  `is_manual` per **B-4**. Reference data only, on **A-5**'s precedent: nothing here computes a run
  outcome and §6.11 stays untouched. Declared as source `gametora-character-profiles` per **B-1**,
  routed as a fourth branch in `PipelineRunner` per **B-2**. `name_ja` is stored for the same reason
  the card row keeps its own fields, but it is **A-1** that requires the Japanese name to exist on
  the `Umamusume` record, not this line.
  **Three grains, deliberately not collapsed.** The four named above are this requirement's grain.
  The detail view renders **six** rows, because the page sets the profile beside two fields the
  profile does not own: `Japanese name` from the `Umamusume` record and `Release date` from
  `global_debut_date`, both per **A-1**. `tests/Feature/CatalogDetailPageTest.php` asserts all six.
  Storage splits further still: the migration
  `2026_09_29_182820_create_umamusume_profiles_table.php` carries `name_ja`, `va_ja`, `va_en`,
  `birth_year`/`birth_month`/`birth_day`, `height` and `three_sizes_b`/`_h`/`_w`, plus the
  provenance set and `is_manual` — and whether the birthday triple counts as one field or three is a
  column choice this requirement does not make. `docs/adr/0012-card-detail-fields-and-images.md`
  Decision 4 is the ruling; its "Decision 4's PRD citation is partial" paragraph is the record this
  line upgrades from an owner ruling to a requirement.
- A-8 [ADR-0014; owner authorization 2026-09-30 lifting §6.9's first half]: `SupportCard` record: the
  source's own `support_id` (unique), the export's `char_id` **as a plain column**, the character name
  and the card's bracketed title as two separate fields, rarity (the source's 1/2/3), type (one of the
  seven export keys `speed`, `stamina`, `power`, `guts`, `intelligence`, `friend`, `group`), both server
  release dates, and the effect anchor vector verbatim. `SupportEffect` is the 35-row dictionary that
  names those anchors.
  **Three limits are part of this line, not footnotes.** (i) `char_id` addresses the *source's* character
  space and is never a foreign key to `umamusume.id`: that column is a local surrogate, the source id
  lives in `external_ref`, and 23 of the 559 records name 9000-block staff who are not trainees at all.
  Those 23 are also the records a Scenario Link is derived from, so the constraint would have deleted the
  evidence rather than cleaned the data. (ii) **No composed card name is stored**, because the source
  publishes none; the label is assembled at the view boundary from the two fields it does publish.
  (iii) **Effect levels are not expanded.** The eleven anchors per effect are stored as the source sends
  them and any intermediate value is interpolated on read; a materialised per-level table would be derived
  rows that can silently disagree with the rule that produced them.
  The client's rarity word (`R`/`SR`/`SSR`) and type word (`Wit` for `intelligence`, `Pal` for `friend`)
  are display mappings, not stored values — **A-1**'s precedent. Availability follows **D-3**'s rule: every
  record the source publishes is stored, and a Global-facing surface offers only rows carrying a Global
  release date. Provenance inline per **A-4**, with its own `is_manual` per **B-4**. Reference data only, on
  **A-5**'s precedent: no card tier, no strength score, no training-yield calculation, and nothing here
  touches §6.11.
- A-9 [ADR-0014]: The Scenario Link is **derived on read**, never stored on the card. It is the card's
  character being on the running scenario's linked list, so the same card is linked in one scenario and
  not in another, and a flag on the row would be wrong the moment the run's scenario changed. The list is
  held in `config('scenarios.php')` keyed by character name. Known and accepted gap: a `group` card
  represents several characters but carries one, so a group card badged on a member who is not its
  representative would be missed; no captured frame yet shows one badged.

### FR-B: Data-fetching & cross-reference engine
- B-1: `uma:fetch {source}` console command; sources are declared in `config('uma.sources')`, each with a parser class.
- B-2: Pipeline stages: fetch (HTTP with per-source delay and retry) → snapshot (raw body to disk) → parse (per-source, isolated) → normalize (NFKD match key) → match (Exact / Alias / Fuzzy / None tiers) → promote or review.
- B-3: Only Exact and Alias matches auto-promote. Fuzzy and None become `MatchCandidate` review rows with the proposed data and confidence.
- B-4: `is_manual` rows are immutable to the engine.
- B-5: Per-source cache lock prevents concurrent fetches of the same source; fetches are idempotent: re-running with unchanged source data changes nothing.
- B-6: JP-source datetimes convert from `Asia/Tokyo` to UTC at parse time; the source timezone is recorded.

### FR-C: Training-run domain (Trainer's own data)
- C-1: `TrainingRun`: belongs to one Umamusume, optional scenario name, status enum (`Active`, `Completed`, `Retired`), optional two inheritance parents (Umamusume references), free-text notes, and since ADR-0008 an optional reference to the `CharacterCard` the run was started on. `umamusume_id` remains the required owner of a run.
- C-2: `TurnEntry`: run + turn number (unique per run), five stat integers, SP integer, optional condition string. Stats validated against the run's own scenario ceiling — `base_cap` plus that scenario's per-stat bonus, clamped to the engine `hard_cap` (`ADR-0015`, which supersedes the flat 0..1200 recorded here and the 0..2000 of `ADR-0002` option B; the 1,200 halved-gains line and the scenario ceiling stay two separate visible markers per `ADR-0002`'s UI condition). SP is non-negative and uncapped: no source states an SP ceiling. Turn >= 1 [rev 0.2 — repo #4].
- C-3: Skill acquisition: run × skill with status (`Suggested`, `Acquired`, `Skipped`) and optional turn acquired [rev 0.2 — repo #4]. `Suggested` = planned before the run; `Acquired`/`Skipped` = outcome.
- C-4: Runs and turns are creatable, editable, and deletable through the web UI; all writes validated by Form Requests.
- C-5: CSV and JSON export per run.
- C-6 [ADR-0014; owner authorization 2026-09-30]: `DeckSlot`: run × support card at a `slot_position` of
  one to six, unique per (run, position). The deck is written as a whole — the six slots are replaced in
  one transaction — because a slot is a *position*: a card moving from two to three must leave two, and an
  upsert keyed on the card would leave it in both. Position six is the friend slot and **the role belongs
  to the slot, never to the card**: the client labels that position `Friends` and any card may occupy it,
  so a stat card parked at six is a legal deck and no `is_friend_card` flag exists. Two copies of one card
  cannot be equipped together, so a duplicate submission is refused rather than stored. An empty deck is a
  valid state and renders as a named absence: the deck is something only the Trainer can supply, and every
  run that predates this requirement has no deck recorded.

### FR-D: Skill catalog (reference data)
- D-1: `Skill`: English name, Japanese name (nullable until cross-referenced), match key, SP cost (nullable), type string (nullable), `is_unique` flag. [amended 2026-09-29 by `ADR-0011`] adds **`rarity`**, **the export's own skill id**, and the provenance columns `ADR-0004` requires. Two limits are part of the amendment, not footnotes: `rarity` stores the source's class code and is **never** rendered as a client rarity word, because the source carries six class values where the client's three rarities live elsewhere; and `type` holds **this tool's derived classification** from the source's effect codes, never client copy (`CONSTRAINTS.md` D-20). The English name is the source's localized client string, not its literal rendering of the Japanese — the two differ on 535 of 623 rows.
- D-2: Skill search/autocomplete for the run UI, on normalized keys.
- D-3 [added 2026-09-29, `ADR-0011`]: Skills arrive through `uma:fetch` from a declared source, and the source's own server flag decides `[Global]` availability. The flag is **recorded at write time and applied at read time**: every row the source publishes is stored, and only rows the source states as available on `[Global]` may reach a Trainer-facing surface. Dropping the rest at import would leave the excluded population uncountable and the provenance unverifiable.

### FR-E: Local API (P2)
- E-1: Versioned `/api/v1` read endpoints for catalog and runs, JSON via API Resources, one consistent error shape (`{ "error": { "code", "message" } }`), offset pagination on list endpoints.

## 5. Non-Functional Requirements

- NFR-1 **Locality**: runs fully on the Trainer's machine. SQLite is the only production driver. No account, no auth surface, no telemetry, no outbound calls except declared fetch sources.
- NFR-2 **Offline resilience**: after any successful fetch, the catalog and all Trainer data work with zero connectivity. A failed fetch degrades to a log entry plus retained previous data; it never blanks or corrupts the catalog.
- NFR-3 **Performance (local budget)**: catalog index renders in under 200 ms against a fully populated catalog (~1,000 Umamusume, ~2,000 skills) on SQLite with indexes; a single-source fetch respects its configured inter-request delay and completes without memory growth across sources (snapshots stream to disk, not memory).
- NFR-4 **Data integrity**: multi-write operations run inside `DB::transaction`; SQLite in WAL mode with `busy_timeout` so the web request and the queue worker coexist; every engine-written fact is traceable to a snapshot.
- NFR-5 **Durability of Trainer data**: `uma:backup` produces a consistent single-file copy (WAL checkpoint, then copy). Trainer data (runs, turns, manual edits) is never deleted or overwritten by the engine.
- NFR-6 **Lore integrity**: zero equine vocabulary for characters in code, data, docs, and UI (enforced by the banned-pattern grep in CLAUDE.md and the Lore Guardian role in AGENTS.md).
- NFR-7 **Maintainability**: PHPStan level 6 clean (existing baseline), Pint clean, Pest tests for every domain behavior; parsers isolated so a source change touches one class.

## 6. Non-Goals (eliminated legacy features)

Explicitly not built, with the legacy feature they replace:

1. **No authentication or multi-user anything** (replaces uma-tracker's Breeze stack, dormant Sanctum in two repos). One Trainer, one machine.
2. **No SPA frontend** (replaces uma-companion's Vue 3 + Pinia scaffold). Blade + Tailwind v4.
3. **No breeding/pairing engine** (replaces uma-companion's sire × dam system, whose vocabulary was also a lore violation). Inheritance is recorded as two optional parent references on a run, nothing more. **Narrowed 2026-09-29 by `ADR-0010`:** "nothing more" caps *computation*, not *recording*. The engine stays banned — nothing predicts a Spark firing, an affinity payout, or an offspring — and `training_runs.legacy_selection` now stores what the Legacy Select screen displays, because `CONSTRAINTS.md` D-268 found the two references short of what that screen produces. No outcome is derived from it.
4. **No EAV attribute storage** (replaces umamusume-tracker-app's legacy `attributes` table). Fixed stat columns.
5. **No Excel export, no `maatwebsite/excel`** (replaces both Laravel 11/12 trackers' export stacks). CSV/JSON only.
6. **No event/banner calendar in Phase 1** (uma-companion's events domain). Revisited in a later phase once the fetch engine has proven reliability.
7. **No legacy database migration tooling.** The legacy apps keep their own data; Trainers export from them by hand if needed. No promise to import `uma_musumes`, `plans`, or EAV rows. Repo #4 does contain working CSV/JSON importers (including for a fifth app's `uma-run-tracker` JSON shape); they are INVESTIGATE reference code, located and cited if import is ever requested, not carried into Phase 1 [rev 0.2 — repo #4].
8. **No MySQL/PostgreSQL support.** SQLite only; the legacy MySQL-only DDL (stored/virtual columns, `ALTER TABLE ... COMMENT`) is not carried over. Repo #4's DB-level enum columns are likewise not carried: string columns + PHP backed enums instead [rev 0.2 — repo #4].
9. **No support-card database in Phase 1.** Promised by uma-tracker's abandoned PRD, never built anywhere (repo #4's support-card component is an empty stub with no backing model); deferred until the cross-reference engine is proven on characters and skills [rev 0.2 — repo #4]. **Partly lifted 2026-09-30 by the owner's Slice 2 authorization, recorded in `ADR-0014` which supersedes `ADR-0005`:** `support_cards` (reference data), `support_effects` (the effect dictionary) and `deck_slots` (the six cards a Trainer equipped for one run) are now in scope. What stays cut is the half this item was really about — **no collection**: no `user_support_cards`, no card levels, limit breaks or Unique Perk states, because that is uma-tracker's abandoned promise and no user story replaced it. The cross-reference engine the deferral hinged on is proven: `character_cards` landed under `ADR-0008`, and `ADR-0005`'s re-verification exercised the `char_id` join against all 559 support-card records. Card *tier* labels remain held, for want of a current Global source rather than for want of authorization.
10. **No hosting, deployment, or cloud path.** `deploying-to-cloud` and friends do not apply. The deliverable is a local `composer run dev`.
11. **No race simulation, prediction engine, or race-day snapshots** [rev 0.2 — repo #4]. Repo #4's manual aptitude-grade predictions and immutable snapshots are cut (Pre-Mortem §4.1); run math stays deterministic over Trainer-entered turns.
12. **No dual storage modes, no browser-side authoritative data** [rev 0.2 — repo #4]. Repo #4's localStorage-vs-account split is cut; SQLite is the single store.
13. **No trainee image uploads** [rev 0.2 — repo #4]. No user story; avoids an upload surface on a local tool.

## 7. Open Questions

- OQ-1 (CLOSED 2026-09-27): Product name is **Trainer Desk**, owner decision recorded in `DESIGN.md`. `APP_NAME` in `.env` should be set to match at the next config change.
- OQ-2: **Partially resolved** (2026-09-28). First source approved: GameTora structured JSON datasets, recorded in `config/uma.php` (`gametora-characters`, entry dated 2026-09-27). Remaining: robots/live availability verification, refresh/schedule policy, additional source selection, parser maturity, and promotion policy for data types beyond characters. Original wording: candidates observed in legacy docs were community wikis (GamePress, umamusume.wiki) and the official JP site; each addition stays a legal/robots.txt review plus one parser class, and the Trainer picks.
- OQ-3: Whether `uma:fetch` runs on the Laravel scheduler by default or only manually. Default: manual, until rate-limit behavior of chosen sources is observed.
- OQ-4 [rev 0.2 — repo #4]: **Closed 2026-09-27 by the owner: enters as engine-owned facts with provenance.** Scope of the closure is exactly what `ADR-0004` implements, the ten aptitude letters (FR-A-5) and the per-scenario stat caps (FR-A-5, `scenarios` table), both arriving through `uma:fetch` against a declared source. Growth rates and base stats stay out: nothing in the PRD requires them yet, and `ADR-0004` leaves them unimplemented rather than seeding them by hand.
- OQ-5 (OPEN 2026-10-01, filed by the Slice 5 Phase A verdict): **Should the skill screen surface a skill's running-style eligibility — "this skill can only fire for style X" — and is that a feature this product wants at all?** A product question, deliberately not a defect, which is why it is here and not in `KNOWN-ISSUES.md`. It is sourced: `docs/UMAMUSUME_REFERENCE.md` §1.2.2 ("The four running strategies `[Both]`, labels server-specific") states the mechanic as a client fact — a skill whose condition contains `running_style==N` cannot fire for any other style, so the label rewrites the usable skill list rather than a hidden curve — and Phase A measured it on **123 of the 623 Global skill rows (19.7%)**, with `condition_groups` carrying a condition string on 100% of rows. **This is NOT `best_for` and must not be built as such.** Phase A's verdict was **(c): do not ship a skill recommendation** — no source carries one (GameTora's 30 keys include no usage, role, target or priority field; Game8's list is `Tier / Skill / Points` with use cases inline in prose) and no requirement cites one, this PRD having zero occurrences of `best_for`, "best for" or `recommend`. Eligibility says when a skill **may** fire; it is not a judgement that the skill is good, and relabelling it would be the same inversion the card-tier-label hold and the provisional `grade_banding` marker both refused. If OQ-5 is ever answered yes, Phase A records the only defensible shape: **derived at read time from `condition_groups`, no new column, no backfill, so it cannot drift from the data it reads** (`docs/design-research/slice-5-phase-a-best-for-2026-10-01.md` §5). Nothing is authorised by this entry. One precondition: the style **number-to-label** mapping must be resolved from client strings first — `factors.json` ids 21–24 give Front Runner / Pace Chaser / Late Surger / End Closer, while the four names in wider circulation ("Runner / Leader / Betweener / Chaser") are not the shipped strings and "Betweener" has zero occurrences in client data; Phase A therefore left its counts unlabelled on purpose. Coverage below the whole subset is the other honest constraint: a badge on 19.7% of skills must read as absence-of-data on the rest, the same rule the mood and `condition` fields already follow.

## 8. Success Criteria (Phase 1 exit)

- US-1..US-3 demonstrably work end-to-end against seeded data (`php artisan test --compact` green, including a pipeline test with a faked HTTP source).
- Migrations, factories, and seeders run clean on a fresh SQLite file.
- Zero banned-pattern hits (lore grep, see CLAUDE.md) across repo text.
- PHPStan level 6 clean; Pint clean.
