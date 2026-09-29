# SOURCE-OF-TRUTH.md

**Governance infrastructure for the Umamusume Trainer Desk repository.** This document consolidates the binding rules from `PRD.md`, `ARCHITECTURE.md`, `CLAUDE.md`, `CONSTRAINTS.md`, `AGENTS.md`, `UMAMUSUME_REFERENCE.md`, and `SKILL.md` into a single source of truth for agents and human developers. Violations are hard failures.

---

## 1. Product Identity & Scope

| Field | Value |
|-------|-------|
| **Product name** | Trainer Desk (owner decision 2026-09-27, closes OQ-1) |
| **Target audience** | Trainers of the Global English version of *Umamusume Pretty Derby* |
| **Architecture** | Local-only Laravel 13 tool, SQLite (WAL + busy_timeout), no auth, no SPA, no multi-user |
| **Phase 1 exit** | US-1..US-3 work end-to-end; migrations/factories/seeders clean; zero lore hits; PHPStan L6 clean; Pint clean |

**Non-goals (PRD §6 + Pre-Mortem §4 — do not build):**
1. Auth/multi-user (replaces Breeze/Sanctum)
2. SPA frontend (replaces Vue 3 + Pinia)
3. Breeding/pairing engine (replaces sire×dam system — also a lore violation)
4. EAV attribute storage (replaces legacy `attributes` table)
5. Excel export / `maatwebsite/excel`
6. Event/banner calendar
7. Legacy DB import (no promise to import `uma_musumes`, `plans`, EAV rows)
8. MySQL/PostgreSQL support
9. Support-card database
10. Hosting/deployment/cloud path
11. **Race simulation, prediction engine, race-day snapshots** [rev 0.2 — repo #4]
12. **Dual storage modes, browser-side authoritative data** [rev 0.2 — repo #4]
13. **Trainee image uploads** [rev 0.2 — repo #4]

---

## 2. Lore Integrity (NFR-6, C-4) — Hard Failure

**Characters are Umamusume: a humanoid race of girls. They are never animals.**

### 2.1 Banned Vocabulary (case-insensitive grep)
```
horse, horses, sire, dam, mare, foal, 🏇
```
Plus animal framing: `stallion`, `colt`, `filly`, `gelding`, `equine`, `pony`, `thoroughbred`, `stable` (as noun for character container), `breeding`, `pairing`, `bloodline`, `pedigree`, `lineage` (of characters), `hoof`, `mane`, `tail`, `withers`, `muzzle`, `jockey`, `rider`, `saddle`, `tack`, `reins`, `bit`, `paddock`, `herd`, `flock`, `pack`, "your horse", "your mount", "the animal", "the girl and her horse".

### 2.2 Context-Allowed Senses (grep hits here are NOT violations)
- `dam` inside `damaged`, `demand`, `command`
- `sire` inside `desired`, `surprise`, `Red Desire` (Umamusume name)
- `stable` as adjective: "stable growth", "keep the build stable"
- `mare` inside `nightmare`
- `tail` inside `detail`, `retail`, `curtail`
- Japanese source strings & quoted official notices (source data)
- `docs/PRE-MORTEM.md` legacy quotations (exempt once each under root C-4)

### 2.3 Required Forms
- Race name: **Umamusume** (capital U at sentence start or as proper race name; lowercase `umamusume` mid-sentence)
- Singular = plural: "one umamusume, three umamusume" — never "umamusumes"
- Training context: **Trainee Umamusume**
- Finished career: **Veteran Umamusume** (run status `Retired` is fine — names the run, not the character)
- Human user: **Trainer**
- Sentence case in all UI copy; no decorative emoji in labels/headings/buttons

### 2.4 Exemptions (Identifier & Mechanics — CLAUDE.md §25–26, CONSTRAINTS.md C-4)
The banned list governs **player-facing copy and character framing only**. It does NOT apply to:
- Dataset/export keys: `intelligence` (for Wit), `friend` (for Pal), `scenarios.json` field names
- Localisation-mapping strings quoted as client evidence: "Intelligence Limit Up", "Runner's Tricks ◎"
- Distinct mechanics sharing a word: "Bad Conditions", the "condition correction" in the failure formula, a "condition" item effect — these are mechanics, not Mood synonyms
- Verbatim skill/race/card/title names from source data: kept as source data, barred from promotion into UI copy (Air Messiah ruling, UMAMUSUME_REFERENCE.md §2.7)

---

## 3. Official Global Terminology (UI Labels) — CONSTRAINTS.md §4, UMAMUSUME_REFERENCE.md §6

| Concept | Required Label | Banned Alternatives |
|---------|---------------|---------------------|
| Five stats | Speed, Stamina, Power, Guts, **Wit** | Intelligence (export key, not client label) |
| Skill currency | **Skill Points**, abbreviated **SP** | Skill Pt, Skill Pts (as UI label) |
| Running styles | Front Runner, Pace Chaser, Late Surger, End Closer | Runner, Leader, Betweener, Tracker, Chaser |
| Style abbreviations | Front, Pace, Late, End (aptitude table only) | Full names inside dense aptitude grid |
| Distances | Sprint, Mile, Medium, Long | Short, Middle, Staying |
| Surfaces | Turf, Dirt | Grass, Sand |
| Support card types | Speed, Stamina, Power, Guts, Wit, **Pal** [uncaptured], Group | Friend (for 友人) |
| Gacha | **Scouts** | Gacha, Pickup, Banner |
| Pull currency | Carats | Jewels, Gems |
| Character being trained | **Trainee Umamusume** | Trainee Uma Musume |
| Finished character | **Veteran Umamusume** | Hall of Fame, graduated |
| Inheritance unit | **Spark** | Factor (JP 因子 wording) |
| Inheritance system | **Inspiration** | Inheritance (JP 継承 word) |
| Ancestors picked for run | **Legacies** | Parents, grandparents, bloodline |
| Energy | Energy | Stamina for the gauge (collides with stat) |
| Mood | Mood, with five state words | Motivation (JP guide gloss), **Condition** |
| Scenario names | Ura Finale, Unity Cup, Brighter Together Our Grand Concert; **Trackblazer** or Twinkle Star Climax for the third | Make a new track!!, Climax bare |
| Rest action | Rest | Break, Vacation |
| Bond gauge | Bond | Friendship gauge, trust |
| Friendship training | Friendship Training, Friendship Bonus | Rainbow training |
| Hint discount | Hint Lvl N, NN% OFF | Hint level N discount |
| Skill acquisition states | **Suggested**, **Acquired**, **Skipped** (SkillAcquisition enum) | Planned/Used/Ignored, Proposed |
| Run states | **Active**, **Completed**, **Retired** (RunStatus enum) | Ongoing/Finished/Archived |

### 3.1 Friend / Pal / Friends Distinction — **CLARIFICATION APPLIED**
- **`Pal`** — the sixth support card type (友人), used in UI labels and code. The Global client's own label for this type has **not been captured** (`[uncaptured]` in CONSTRAINTS.md); `Pal` is Game8 English's word. Do not ship it as though the client said it.
- **`Friend`** — the export key for the Pal type in GameTora data (`support-cards.json` `type` field). This is a dataset identifier, not UI copy.
- **`Friends`** — the raw client string for the support card slot label in the deck UI (e.g., "Friends" slot). **Preserve raw client strings faithfully in captures.** Do not overwrite faithful captures with wiki labels.
- **Prose & UI labels** — use the Terminology Map above: "Pal" for the type, "Friendship Training" for the mechanic.
- **Never** use "Friend" as a UI label for the 友人 support card type.

### 3.2 Condition / Skill Pt — **CLARIFICATION APPLIED**
- The bans on **"Condition"** (for Mood) and **"Skill Pt"** (for Skill Points) apply **strictly to prose and UI labels**.
- They do **not** apply to:
  - Committed schema columns: `turn_entries.condition` (free-text notes), `training_runs.condition` if it exists
  - Raw client string captures: the "Skill Pts" header in client screenshots, "Condition" in verbatim client text
  - Mechanics: "Bad Conditions", "condition correction" in failure formula, condition item effects

---

## 4. Scenario Registry & Supersedence Rules

The four `[Global]` scenarios, in order, with their stat caps (base 1200 + bonus) and hard caps:

| # | Scenario (Global) | Speed | Stamina | Power | Guts | Wit | Hard Cap | Status |
|---|-------------------|-------|---------|-------|------|-----|----------|--------|
| 1 | URA Finale | 1400 | 1400 | 1400 | 1400 | 1400 | 2000 | Active |
| 2 | Unity Cup | 1300 | 1300 | 1300 | 1300 | 1800 | 2000 | Active |
| 3 | **Trackblazer** (Twinkle Star Climax) | 1200 | **1900** | 1200 | 1200 | 1500 | 2000 | Active |
| 4 | Our Grand Concert | **1600** | 1300 | 1300 | 1500 | 1300 | 2000 | Known-Gap Stub |

### 4.1 Supersedence Rules (applied to all scenario files)
- **Active** files are the current authoritative reference for mechanics numbers.
- **Superseded** files preserve pre-release or pre-rework snapshots; their mechanics numbers are stale — read the superseding file instead.
- **Known-Gap Stub** files are boundary markers recording sourced facts only; no mechanics are inferred. Per CONSTRAINTS.md D-165, no scenario-specific chrome may be invented from assumption.
- **`03-trackblazer.md`** is a **Superseded (pre-release snapshot)** file, superseded by `04-trackblazer-umaguide.md` and `05-trackblazer-gametora.md`. It is preserved as written; where it hedged or guessed, read 04/05 for the shipped scenario.
- **`08-grand-masters-jp-only.md`** is `[JP-Only]` reference material. Per AGENTS.md and owner ruling: **must not be imported into app data, config, or UI copy until a Global release date exists.**

### 4.2 Speed Ceiling Conflict — **CORRECTION APPLIED**
- **2000** is the hard cap (`hard_cap`) for all four `[Global]` scenarios (URA, Unity Cup, Trackblazer, Our Grand Concert).
- **2100** does not appear in any current Global scenario. It was a misreading; the highest Speed ceiling on Global is **1600** (Our Grand Concert). The `hard_cap` of 2000 is 400 above that.
- Two `[JP]`-only future scenarios (Beyond Dreams / らっしゃい！トレセン軒！) carry `hard_caps = 2500` (and 9999/99999 sixth element, meaning unverified). Nothing in the current Global set approaches 2500.

---

## 5. Source Tier Ladder — **ALIGNED TO [S]/[A]/[B]/[C]/[D]**

Per `UMAMUSUME_REFERENCE.md` source registry (Section 8, preamble):

| Tier | Meaning | Examples |
|------|---------|----------|
| **[S]** | Official Cygames | Umamusume JP/Global official portals, news, character index |
| **[A]** | Major community wiki with editorial process | Kamigame JP, GameWith JP, Game8 EN/JP, Umamusume Wiki (MediaWiki) |
| **[B]** | Database or tool site | GameTora data export |
| **[C]** | Community post requiring corroboration | r/UmaMusume banner megathreads |
| **[D]** | Rumor / unverified | Datamine-only claims |

- Every fact in `UMAMUSUME_REFERENCE.md` carries a tier citation.
- `[Global]` official site is Tier S; `[Global]` community guides (Game8 EN) are Tier A.
- A Tier B dataset (GameTora) needs A- or S-tier confirmation before a claim becomes app data (conflict-resolution protocol).

---

## 6. Skill Registry — **SKILL.md POINTER CORRECTED**

The skill registry lives at **`.agents/skills/skills.json`** (not `SKILL.md`).
- `SKILL.md` is a human-readable summary; the machine-readable registry is `skills.json`.
- Agents MUST read `skills.json` via the `using-agent-skills` skill (§1.2, §1.4.4 of that skill) to discover and invoke skills.
- Local project skills at `.agents/skills/` take precedence over global installs when both exist.

---

## 7. Data-Fetching Engine (JP ↔ Global Cross-Reference)

### 7.1 Pipeline Stages (ARCHITECTURE.md §5, FR-B)
`fetch` → `snapshot` (raw body to disk, hashed) → `parse` (per-source, isolated) → `normalize` (NFKD match_key) → `match` (Exact/Alias/Fuzzy/None) → `promote` | `review`

### 7.2 Match Tiers
- **Exact** (`match_key` equality) → auto-promote
- **Alias** (alias hit) → auto-promote
- **Fuzzy** (Levenshtein ≥ threshold, default 85%) → `match_candidates` review queue
- **None** → `match_candidates` review queue

### 7.3 Invariants
- `is_manual` rows are **immutable to the engine** (FR-B-4, PRD US-8)
- Every engine-owned fact carries provenance: `data_sources` row with `url`, `fetched_at`, `snapshot_path`, `confidence`, `source_timezone` (FR-A-4)
- A fact without provenance is **deleted, not stored** (Data Engineer rules)
- JP datetimes parsed as `Asia/Tokyo` → stored UTC; `source_timezone` recorded (FR-B-6)
- Date-only values stay `date`, never datetimes (CLAUDE.md Planner Rule 3)
- HTTP allowlist only: `config('uma.sources')` (SSRF posture, ARCHITECTURE §8)

---

## 8. Training-Run Domain (Planner — FR-C, US-3, US-4)

### 8.1 Schema (ARCHITECTURE.md §3)
- `training_runs`: `umamusume_id`, `scenario?`, `status` (Active|Completed|Retired), `inheritance_parent_a_id?`, `inheritance_parent_b_id?`, `notes?`
- `turn_entries`: `training_run_id`, `turn` (unique per run), `speed/stamina/power/guts/wit` (0..2000), `sp?`, `condition?` (free-text notes)
- `run_skills` pivot: `status` (Suggested|Acquired|Skipped), `turn_acquired?` — **Suggested = planned pre-run**

### 8.2 Deterministic Math (Planner Rules 4, 5)
- All run math is deterministic over Trainer-entered `turn_entries` — no randomness, no simulation, no speculative prediction
- Every computed number must be explainable from the entered turns
- Stat bounds: **0..2000** per stat (per scenario's `hard_cap`), turn ≥ 1 (StoreTurnEntryRequest, ADR-0002 Option B accepted)
- The 1,200 halved-gains line and the scenario ceiling are **two visible, differently-drawn markers** (ADR-0002 amendment, DESIGN.md §6.5)

### 8.3 Timezone Correctness (Planner Rule 3, US-7)
- Store UTC; parse JP-source datetimes as `Asia/Tokyo`; record `source_timezone`
- Date-only stays `date`; display via `config('uma.display_timezone')`
- Any date-arithmetic change requires a test with `freezeTime()`

---

## 9. Quality Bar (CONSTRAINTS.md) — Verification Sequence

| # | Dimension | Threshold | Command |
|---|-----------|-----------|---------|
| C-1 | Tests | All Pest tests pass; every behavior change ships a test | `php artisan test --compact` |
| C-2 | Static analysis | PHPStan level 6 (Larastan), zero errors | `vendor/bin/phpstan analyse --no-progress` |
| C-3 | Formatting | Pint clean | `vendor/bin/pint --dirty --format agent` then `vendor/bin/pint --test --format agent` |
| C-4 | Lore | Zero unexplained hits of banned patterns | `make lore` |
| C-5 | Migrations | Fresh migrate + seed succeeds; every migration has working `down()` | `php artisan migrate:fresh --seed` |
| C-6 | Performance | Catalog index < 200 ms at ~1k Umamusume / ~2k skills | Manual benchmark per PRD §8 |
| C-7 | UI states | Every data view renders empty, loading/refresh, error | Review checklist |
| C-8 | Dependencies | No new package without approval; `composer audit` / `npm audit` no critical/high | `composer audit`; `npm audit --omit=dev` |
| C-9 | TypeScript | Zero errors from `tsc --noEmit` (`strict: true`, `noEmit: true`) | `npm run typecheck` |

### 9.1 Floor (Never, in Any Change)
- No new suppressions: `@phpstan-ignore`, `@phpstan-` escapes, `eslint-disable`, `@ts-ignore`, `# noqa`
- No stub bodies: `throw new \Exception('not implemented')`, empty `catch {}`, TODO placeholders
- No deleted or skipped tests without human approval and reason in commit message
- No engine write to rows with `is_manual = true` (PRD FR-B-4)
- No fact stored without provenance (AGENTS.md Data Engineer)
- No fetch URL outside `config('uma.sources')` allowlist (SSRF)
- No business logic in controllers; no inline `$request->validate()` (CLAUDE.md)

### 9.2 Verification Sequence Before Hand-off
1. `php artisan migrate:fresh --seed`
2. `php artisan test --compact` (narrow first, full at hand-off)
3. `vendor/bin/pint --dirty --format agent`
4. `vendor/bin/phpstan analyse --no-progress`
5. `npm run typecheck`
6. `make lore`

---

## 10. ADR Index (Binding Decisions)

| ADR | Title | Status | Summary |
|-----|-------|--------|---------|
| 0001 | Lift no-prediction non-goal for energy guidance | Accepted | Energy guidance permitted; race outcomes still prohibited |
| 0002 | Scenario-aware stat caps exceed validation bound | **Accepted (Option B)** | Validation bound widened to **0..2000** sourced from `scenarios.hard_cap`; 1200 halved-gains line + scenario ceiling remain two visible UI markers |
| 0003 | Consolidated Phase 1 schema expansion | **Accepted** | Adds Energy, Fans, Mood, TurnEvent, ScenarioSlots, RaceEntry; US-10 promoted to P1; stat bound reads from scenario's `hard_cap` |
| 0004 | Aptitude & scenario cap reference data | **Accepted** | Stores 10 aptitude letters on `umamusume`; creates `scenarios` table with 5 caps + `hard_cap` + provenance; closes OQ-4 for these domains; `GametoraScenarioParser` implements `ScenarioSourceParser` |
| 0005 | Support card entities | Proposed, not built | Schema shapes specified; not part of design until owner settles scope (PRD §6.9) |
| 0006 | Design authority & theme default | Accepted | Dark-first tactical-athletic system; theme preference in SQLite not localStorage |

---

## 11. Open Questions (PRD §7)

| ID | Question | Status |
|----|----------|--------|
| OQ-1 | Product name | **CLOSED 2026-09-27**: Trainer Desk |
| OQ-2 | Concrete fetch sources for Phase 1 | Open — each addition = legal/robots.txt review + parser class |
| OQ-3 | `uma:fetch` on scheduler vs manual | Default: manual, until rate-limit behavior observed |
| OQ-4 | Aptitude & scenario caps scope | **CLOSED 2026-09-27**: Enters as engine-owned facts with provenance (ADR-0004) |

---

## 12. Key File Locations

| Domain | Files |
|--------|-------|
| Product requirements | `PRD.md` |
| System design | `ARCHITECTURE.md` (authoritative), `ARCHITECTURE-ESSENTIALS.md` (digest) |
| Mechanics reference | `docs/UMAMUSUME_REFERENCE.md` (8 sections, 46 conflict rows) |
| Scenario guides | `docs/scenarios/01`–`08` (07=stub, 08=JP-Only) |
| Design system | `docs/design-research/DESIGN.md` (root), `docs/design-research/CONSTRAINTS.md` (UI contract) |
| Quality bar | `CONSTRAINTS.md` (root), `docs/design-research/CONSTRAINTS.md` (additive) |
| Agent roles | `AGENTS.md` |
| Lore rules | `CLAUDE.md` (top section), `CONSTRAINTS.md` C-4 |
| Skill registry | `.agents/skills/skills.json` (machine), `SKILL.md` (human summary) |
| ADRs | `docs/adr/0001`–`0006` |
| Fetch pipeline | `app/Services/DataPipeline/` (Contracts, Parsers, PipelineRunner, SourceFetcher, CrossReferenceMatcher, NameNormalizer) |
| Models | `app/Models/{Umamusume,UmamusumeAlias,Skill,TrainingRun,TurnEntry,Scenario,DataSource,MatchCandidate,RunSkill,ScenarioRace,RaceEntry,TurnEvent,Preference}` |
| Migrations | `database/migrations/2026_09_26_162814`–`2026_09_27_121500` |

---

## 13. Escalation Paths (AGENTS.md)

1. **Role-level conflict** (e.g., Laravel Dev needs schema change) → Architect decides within PRD scope
2. **Scope conflict** (feature not in PRD, or non-goal requested) → Architect escalates to human owner
3. **Lore ruling dispute** → Lore Guardian verdict stands; only human owner can override in writing
4. **Constraint relaxation** (CONSTRAINTS.md threshold in the way) → QA/Reviewer escalates to human owner
5. **Fetch-source legality/robots uncertainty** → Data Engineer stops and escalates to human owner
6. **Cross-model review disagreement after 3 doubt cycles** → Surface both positions to human
7. **Planner feature implying cut repo #4 system** (simulation, snapshots, predictions, dual storage) → Planner Domain Specialist escalates to Architect → PRD §6 check → human owner

---

*This document is governance infrastructure. It must remain in the repo to bind future agents and human developers. Last updated: 2026-09-27 with 5 corrections + 2 clarifications applied per owner approval.*