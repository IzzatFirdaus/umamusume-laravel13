# ARCHITECTURE-ESSENTIALS

Token-efficient digest of ARCHITECTURE.md for agent context injection. If this file and ARCHITECTURE.md disagree, ARCHITECTURE.md wins.

_Dated correction 2026-10-08 (documentation-sync pass). Several statements below describe the pre-2.0
surface. The web surface is now Inertia + Vue 3 (`ADR-0020` §1): `resources/views/` holds
`app.blade.php` (the Inertia shell) and the three error documents only, `resources/views/components/`
is empty since slice B1, and the career 2.0 screens (the setup wizard `Career/*`, the cockpit and its
detail screens, the scenario panels) are Vue pages under `resources/js/pages/Career/`, `Veterans/` and
`Database/`, driven by controllers under `app/Http/Controllers/Career/` plus `DatabaseController`.
Phase C–E added a bounded set of services and read routes the schema lines below do not yet name:
`App\Services\Advisor\TrainerAdvisor` (+ `config/advisor.php`, C2); `App\Services\Career\SetupDraft`;
`App\Services\Legacy\AncestryGraph`; `App\Services\DeckAnalysis`; `App\Services\RaceFacts` (the shared
ten-field race list); `App\Actions\RecordVeteran`'s first caller `SaveVeteranController`; and the
`career.*`, `runs.cockpit`, `runs.{training,races.decision,races.planner,events,inheritance,skills.planner,timeline,result,veteran}` and
`veterans.*` / `database.*` routes. The veterans line below says "no routes or screens yet (slice D16
builds those)" — that is now false; D16 built them. Statements that read "Blade" as the shipped surface,
or list the deleted `x-*` Blade components, are superseded by this note and kept as the record of the
pre-2.0 shape._

## Stack (pinned, installed 2026-09-27)

- PHP 8.5.8, laravel/framework 13.32.0, SQLite only (WAL + busy_timeout), `database` cache + queue stores
- Pest 4.7.8 (feature-first), Larastan 3.12.1 level 6, Pint 1.32.1 (`pint.json`)
- Tailwind v4 CSS-first (`@theme` in resources/css/app.css; NO tailwind.config.js), Vite 7, Blade, vanilla JS
- Forbidden deps (cut in Pre-Mortem): sanctum, breeze, maatwebsite/excel, SPA frameworks, Redis

## Product shape

- Local-only single-Trainer tool: NO auth, NO multi-user, NO public deploy, NO telemetry
- Domains: catalog (Umamusume + skills + provenance), trainer data (runs/turns/skills), fetch engine (JP↔Global cross-reference)
- Web = Blade; JSON = read-only /api/v1 (P2); export = CSV/JSON only

## UI composition

- `config/scenarios.php` is the only place scenario names appear in the layout path. `x-resource-strip` / `x-stat-band` / `x-race-calendar` / `x-guided-step` read `widgets`, `steps`, `panels`; a scenario name in a view is a D-240 failure (test: ResourceStripTest, GuidedStepScenarioCompositionTest). All four take `scenario` as a REQUIRED prop — a named default is the same smell
- D-220: a widget a scenario has no mechanic for is absent, never an empty slot. Captions obey the same rule, and so does run state: a value the run has not recorded renders as `N/A` (with a `title` naming which kind of absence) plus "not yet recorded", never as a default (a Team Rank of "G" would assert a rank nobody entered). An em dash is not used as the disclosure glyph — R-02/D-79 ban it in shipped copy (owner ruling 2026-09-28, KI-7)
- `config('scenarios.baseline')` names the strip a run with no scenario renders (URA Finale, the only Global scenario with every panel off by definition)
- Design tokens only: `bg-page`/`bg-panel`/`bg-raised`/`bg-sunken`, `text-ink`/`-strong`/`-muted`, `border-rule`, `bg-chrome`+`enamel` for the one primary action. Zero `dark:` utilities and zero skeleton palette classes (G-19); `resources/views/vendor/pagination/tailwind.blade.php` is published for this reason
- Token set has no caution chrome; `border-ink-faint` is the declared border token (3.26:1 light / 4.21:1 dark), `text-ink-muted` the accessible subordinate tier

## Lore (hard rule, details in CLAUDE.md)

- Characters are Umamusume (humanoid race; singular=plural). Never "horse(s)", never animal framing, never sire/dam/mare/foal, no 🏇
- Table `umamusume` (invariant plural); model `Umamusume`

## Schema (snake_case; enums TitleCase backed)

- umamusume: slug uniq, name (EN), name_ja, match_key (NFKD-normalized, indexed), release_status (GlobalReleased|GlobalAnnounced|JapanOnly), jp_debut_date?, global_debut_date?, is_manual (engine never overwrites), external_ref? nullable + index (source's own char id `gametora:char:{id}`, the link costume cards attach through — ADR-0008, **migrated as `2026_09_29_120000`**; `PromoteMatchedRecord` now writes it on both its create and update paths)
- umamusume_aliases: umamusume_id FK, alias, language (Japanese|English|Romanized), uniq(alias, language)
- character_cards (ADR-0008; costume cards that reached [Global]; **migrated as `2026_09_29_120100`**, with `CharacterCard`, `CardRarity` and `Umamusume::cards()` — the table is empty until the card parser and its declared source land): card_id uniq (the source's own id, so a re-fetch is idempotent by identity, FR-B-5), umamusume_id FK cascade, title (verbatim [Global] client string, brackets included — source data, never normalized copy), rarity (CardRarity: OneStar|TwoStar|ThreeStar), global_release_date (required: no Global date, no row), is_debut_form (derived from the earliest JP release among that trainee's cards; the export has no debut field), unconfirmed (Tier B stands alone behind it → stored flagged, hidden by default); provenance is **inline** — source_url (not null), snapshot_path?, fetched_at?, source_timezone? — per `ADR-0003` Amendment R3, the same set `scenario_races`/`scenario_slots`/`race_catalog_slots` each carry, plus the card's own is_manual (FR-B-4 at card grain: fixing one card's title claims that card, not the whole trainee), and the five skill-list columns the card document publishes — skills_innate?, skills_unique? (KI-33, 2026-09-30) and skills_awakening?, skills_event?, skills_evo? (2026-10-02; `skills_evo` is a json list of `{new, old}` id pairs, the others are id lists). `data_sources` keeps its own meaning: `umamusume_id`-scoped, the table behind FR-A-4 and the detail page's Provenance section, not a card's
- skills: name, name_ja?, match_key?, sp_cost?, type?, is_unique, condition_groups? (the source's own activation predicate and effect vector, a json list of `{base_time, condition, precondition, effects[]}` groups; stored by the 2026-10-02 slice, rendered on the skill detail page as the source's engine data)
- data_sources: umamusume_id FK, url, source_key, fetched_at, snapshot_path?, confidence?, source_timezone? (IANA)
- match_candidates (review queue): source_key, proposed_name/_ja/_match_key, suggested_umamusume_id?, match_tier (Fuzzy|None), status (Pending|Confirmed|Aliased|Rejected), payload json
- training_runs: umamusume_id FK, scenario?, status (Active|Completed|Retired), inheritance_parent_a_id?, inheritance_parent_b_id?, legacy_selection? (json, read back as `LegacySelectionPayload` — the Legacy Select screen's own record, not a free-text note), notes?, character_card_id? (ADR-0008: the costume form the run started on; `umamusume_id` stays the required owner; FR-C-1 as amended; **migrated as `2026_09_29_120200`**, with `TrainingRun::characterCard()`, a fillable `character_card_id` and a same-trainee `exists` rule on `StoreTrainingRunRequest`), imported_at?, import_source? (ADR-0017: the pair recording that this run arrived by file rather than by keystroke; **migrated as `2026_09_30_174646`**. Both nullable with no default, so a typed run — the overwhelming majority — says nothing. Neither borrows an existing column: `created_at` is when the row entered the database while `imported_at` is what the Trainer asserts about the run's own past, and `is_manual` (FR-B-4) and `source_url`/`fetched_at` describe the fetch engine's rows, not a Trainer's history). Two additive pointers, neither a replacement for the other: `legacy_selection` is the pre-run screen's payload and `character_card_id` is the form the Trainer started on, and a run may have one without the other. `build_target?` (json, read back as `App\Models\Advisor\BuildTargetPayload` — the Trainer-entered build target: `purpose` (a `BuildPurpose` case), `distance`, `surface`, `style`, five per-stat `targets` validated against `ScenarioCaps::forRun`, and ordered `skill_priorities`; FR-F-1, `ADR-0020` §2; **migrated as `2026_10_05_180000`**, with `TrainingRun::buildTarget()` and a fillable `build_target`). Nullable with no default, because "the Trainer entered no target" and "a target with nothing in it" are different states to the advisor — the first falls back to Energy-only guidance and names the absence, the second would be a claim they entered something — and a `{}` default would erase that difference at the schema layer, where it is hardest to see. Written whole rather than merged, through `StoreBuildTargetRequest` on `runs.build-target.update`; the fetch engine never writes it
- `training_runs.scenario` is a free-text COLUMN but a validated VALUE: `StoreTrainingRunRequest` rules it against `config('scenarios.scenarios')` keys, blank normalises to null. No FK (owner ruling 2026-09-27). `TrainingRun::scenarioKey()` resolves null → `config('scenarios.baseline')`; `stripValues()` returns the latest turn's end-of-turn Energy/Fans (absolute totals, `reorder('turn','desc')` — NOT `latest()`, which the relation's ascending order would defeat) and omits keys the run has no column for
- veterans: training_run_id FK->training_runs **unique + cascade**, tags? (json, a flat list of strings, the Trainer's own spelling), tags_normalized? (json, the same list folded to lowercase — the column the tag filter matches, so a hand-typed `speed` answers the library's `Speed` suggestion; `RecordVeteran` derives it beside the tags at the one write, and `ListVeterans` matches filter values against it — **migrated as `2026_10_08_180000`**, which also backfills rows written before it, KI-72), notes?, timestamps — the Veteran library (PRD **FR-G**, `ADR-0020` §3, under `ADR-0010`'s recording allowance; **migrated as `2026_10_05_120000`**). A Veteran is a completed run kept in the library: the run's recorded facts (trainee, final stats, skills, sparks, race record) are read back through `training_run_id`, and only the Trainer's own tags and notes are stored here. Record-only — nothing derives a Spark firing, an affinity payout or an offspring (FR-G-4). The FK is unique (one run is one Veteran, so a re-save rewrites its tags and notes) and cascades (with the run gone there is nothing to read back), the same lifetime `turn_entries`/`race_entries` have. No `is_manual` write and no `data_sources` row: a Veteran is Trainer data, not a fetched fact. `App\Actions\{RecordVeteran,ListVeterans,ShowVeteran}` are the record/list/show path; no routes or screens yet (slice D16 builds those)
- turn_entries: training_run_id FK cascade, turn, speed/stamina/power/guts/wit, sp?, condition?, energy?, mood?, fans?, uniq(run, turn); stats validated 0..1200, turn >= 1 [rev 0.2 — repo #4]. **Bound corrected 2026-10-01 by a dated erratum; the `0..1200` above stands as the state as written.** `ADR-0015` (owner decision 5, 2026-09-30, landed in `8bda7db`) replaced the flat bound with the run's **own per-stat scenario ceiling** — `base_cap` (1200) plus that scenario's `cap_bonus` for that stat, clamped to the engine `hard_cap` (2000) — read through `App\Services\ScenarioCaps`, which is now the single owner of that arithmetic. So a stat is accepted to 1400 on URA Finale, to 1800 on Unity Cup Wit, to 1900 on Trackblazer Stamina and to 1600 on Our Grand Concert Speed; **1200 is the ceiling only where the run names no scenario**, which is the one case this sentence still states correctly, because `forRun()` grants no bonus to a run that chose nothing. `PRD.md` carried this supersession from the start (`:28`, `:128`); the two architecture documents did not, and this is one of the two carriers recorded as **KI-48**.
- run_skills pivot: status (Suggested|Acquired|Skipped), turn_acquired? — Suggested = planned pre-run [rev 0.2 — repo #4]
- race_entries: training_run_id FK cascade, scenario_slot_id? FK set-null, race_catalog_slot_id? FK set-null, scenario_race_id? FK set-null, **turn_entry_id? FK set-null (KI-17, Slice 15)**, status, placement?, fans_gain?, objective_index?, circles?, **grade_points_earned? (KI-10's figure, Slice 15)** — four nullable pointers because one entry can be authored four ways (seeded slot, career calendar, legacy scenario race, Trainer-typed free race), and an entry with no slot is still a logged race (R2). `turn_entry_id` is the turn the Trainer names the race having happened on; it is entered, never derived from a race date (D-270). `grade_points_earned` is written by a model guard from the tier (`race_catalog_slots` first, `scenario_slots` second) and the placement, and a Trainer-entered figure outranks the derived one; null means no source prices that finish, which is not zero (KI-10's ratio half stays open). Recorded here because the digest carried no `race_entries` line before these columns were added
- preferences: `key` PK, `value` text, timestamps — no user_id (§6.1 single Trainer). SQLite is the ONLY preference store; §6.12 cuts browser storage as a second source of truth. Authorized keys: `theme`, `failure_estimate` (PRD US-11). Read via `Preference::get()`; `View::composer('components.layout')` passes the resolved theme so the html element is server-rendered and the pre-paint script is skipped
- users/cache/jobs = framework defaults, users unused
- Planner (repo #4) maps onto training_runs + turn_entries + run_skills; its snapshots/race-predictions/dual-storage/image-uploads are cut, no new tables; growth-rate/aptitude data deferred to PRD OQ-4 [rev 0.2 — repo #4]

## Fetch engine (app/Services/DataPipeline)

- Stages: fetch → snapshot (storage/app/private/snapshots, raw body, hashed) → parse (per-source class, Contracts\SourceParser) → normalize → match → promote | review
- NameNormalizer (pure): intl NFKD, mb_strtolower, strip ・/-/spaces/combining marks, collapse whitespace; display names never mutated
- CrossReferenceMatcher tiers: Exact (match_key =) | Alias (alias hit) → auto-promote; Fuzzy (levenshtein ≥ config threshold, default 85%) | None → match_candidates only
- Promotion: upsert engine-owned columns, skip is_manual, one data_sources row per fact, DB::transaction per batch
- Idempotent: unchanged snapshot hash short-circuits; re-run writes nothing
- Concurrency: Cache::lock("uma-fetch:{source}") in UmaFetch; web refresh runs synchronously (stale-while-revalidate)
- Timezone: JP datetimes parsed Asia/Tokyo → stored UTC, source_timezone recorded; date-only stays date
- Commands: uma:fetch {source}, uma:reparse {source} (from snapshots, zero network), uma:backup (WAL checkpoint + file copy)
- HTTP: allowlisted hosts from config('uma.sources') ONLY (SSRF), redirects followed by the fetcher with the host allowlist re-checked per hop, per-source delay_ms/timeout_s, retry max 2 at a flat 500 ms, descriptive UA
- Artwork: `ADR-0021` (2026-10-05), fetch half **built and run 2026-10-05**, display half **built** — `uma:fetch-art` + `ArtworkMirror` + `SourceFetcher::fetchAsset()`, asset host declared in config('uma.sources'), files in gitignored `storage/app/private/artwork/` with a sibling `manifest.json`, path derived from the id, **nothing in the database**, no snapshot of a binary. First live pass: 665 ids, 665 files, 45 MB, zero unresolved, so the `ADR-0012` Erratum 4 falsifier did not fire (`ADR-0021` Erratum 1). `resources/js/components/ArtworkSlot.vue` is the one owner of the slot contract on the four ported screens; an absent file paints nothing and a row reserves a transparent cell so its label holds one x (`DESIGN.md` §4.7). `uma:fetch` steps over parser-less entries. Distinct from the cut upload surface below

## API contract (/api/v1, P2)

- GET umamusume?status=&search=&page=&pageSize= ; GET umamusume/{slug} ; GET training-runs ; GET training-runs/{id}
- Shape: { data: ..., pagination: { page, pageSize, totalItems, totalPages } } on lists
- Error (all non-2xx): { error: { code, message } }; 400/404/422/500; 500 generic, details to log
- camelCase params+fields, plural nouns, no verbs, offset pagination, additive changes only, API Resources + PHPDoc array-shapes

## Caching

- Catalog reads: Cache::remember, TTL config default 15 min, keys include catalog:version counter bumped on promotion (versioned-keys invalidation)
- Trainer-data reads: never cached

## Security posture

- No auth surface; loopback serve only. Untrusted input = fetched pages (data-only parsing, Blade auto-escape, no {!! !!} for source data) + Trainer forms (Form Requests)
- No secrets in Phase 1; future keys go in .env only
- composer audit / npm audit before tagging a build

## Conventions (from AGENTS.md domain rules; non-negotiable)

- declare(strict_types=1); explicit param+return types; #[Fillable]/#[Hidden] attributes; casts() as method; HasFactory + factory per model
- Thin controllers; logic in Actions (app/Actions) / Services; Form Requests for all validation; events only from Actions/Services
- Named routes + route() helper; API Resources for JSON; anonymous migration classes
- Config via config(); env() only in config files; php artisan make:* with --no-interaction
- loadMissing/with against N+1; DB::transaction for atomic multi-writes; no soft deletes
- Pint --dirty after PHP edits; PHPStan level 6 clean; Pest tests for behavior changes (read testing-best-practices skill first); Http::fake in all fetcher tests, no network in tests
- Comments: PHPDoc over inline; no restating-the-obvious comments (antislop-code)

## Directory delta

- app/{Actions, Console/Commands, Enums, Services/DataPipeline(+Contracts)}
- app/Http/{Controllers(+Api/V1), Requests, Resources}
- app/Models/{Umamusume, UmamusumeAlias, Skill, TrainingRun, TurnEntry, DataSource, MatchCandidate, RunSkill (pivot), Preference}
- config/uma.php; config/scenarios.php; database/{factories, migrations, seeders}; storage/app/private/snapshots (gitignored)
- resources/views/components/{stat-band, resource-strip, race-calendar, guided-step, layout}; resources/views/vendor/pagination (token override)
- tests/Feature/{Catalog, TrainingRun, ApiV1, FetchPipeline, CrossReferenceMatcher, Adr0003Schema, DesignTokens, ResourceStrip, ResourceStripOnRunDetail, GuidedStepScenarioComposition}; tests/Unit/NameNormalizer (pure)

## Phase-1 non-goals (do not build without new PRD scope)

- Auth/multi-user; SPA; breeding engine (inheritance = 2 nullable parent FKs only); EAV; Excel; event calendar; legacy DB import; MySQL/PG; support-card **collection**; deploy paths
- [rev 0.2 — repo #4] race simulation/predictions/snapshots; dual storage modes or browser-side authoritative data; trainee image uploads; DB-level enum columns. **Uploads stay cut**; `ADR-0021` authorizes a different object (art the tool fetches itself by id), see "Artwork" under Fetch engine above and PRD OQ-6
- Support cards split in two, 2026-09-30. `ADR-0014` supersedes `ADR-0005` (DECLINED, owner ruling R37) and authorizes `support_cards`, `support_effects` and `deck_slots` — reference data plus the six cards a run was equipped with, per `PRD.md` **FR-A-8**, **FR-A-9**, **FR-C-6**, **US-12**. `ADR-0005`'s re-verification already exercised the cross-reference these deferral text named, on all 559 records. What stays cut is the collection: no `user_support_cards`, no level, limit break or Unique Perk state. Card **tier** labels are held for want of a current Global source, not for want of authorization; `ADR-0014` records that R75 governs race tiers and does not reach this.
- `ADR-0008`'s `character_cards` is the **costume-card** table and still authorizes nothing in the support-card direction: `training_runs.character_card_id` names a costume, `deck_slots.support_card_id` names a training companion, and the two keys are not interchangeable (`character_cards.umamusume_id` is a local FK; `support_cards.char_id` is the source's own id as a plain column)

## Key doc citations

- HTTP client: <https://laravel.com/docs/13.x/http-client> ; Locks: <https://laravel.com/docs/13.x/cache#atomic-locks>
- Resources: <https://laravel.com/docs/13.x/eloquent-resources> ; SQLite: <https://laravel.com/docs/13.x/database#configuration>
- Queues/unique jobs: <https://laravel.com/docs/13.x/queues> ; NFKD: <https://www.php.net/manual/en/normalizer.normalize.php>
