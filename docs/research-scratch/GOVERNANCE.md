# Governance

## Provenance

This master file is compiled from 7 source files, embedded verbatim with headings demoted by one level:

1. `docs/SOURCE-OF-TRUTH.md`
2. `docs/GATE-REGISTRY.md`
3. `docs/PRE-MORTEM.md`
4. `docs/deprecated/frontend-review/2026-09-28/README.md`
5. `docs/deprecated/frontend-review/2026-09-28/DECISIONS-NEEDED.md`
6. `docs/deprecated/frontend-review/2026-09-28/RESOLUTIONS.md`
7. `CONSTRAINTS.md` (root quality bar C-1 to C-9, 48 lines, added 2026-10-03 in Round 8; see the
   section "CONSTRAINTS.md (root quality bar, C-1 to C-9)" at the end of this file)

**Dating note (2026-10-09):** This master cites several `docs/design-research/` paths that were consolidated and
removed when this master was committed. The design rules from `docs/design-research/CONSTRAINTS.md` now live in
`docs/research-scratch/DESIGN-CORPUS.md` §"CONSTRAINTS.md"; the token system from `docs/design-research/DESIGN.md`
now lives in §"DESIGN.md". These citations are dated records of where the source evidence sat at measurement time
and are not actionable on disk.

**Round 8 (2026-10-03), owner-authorized:** the root `CONSTRAINTS.md` is now a pointer stub whose
binding text is section 7 above. The gate precedence chain is unchanged (R-6 still puts the bar
first); it resolves through the stub. The `AGENTS.md` line "Read `CONSTRAINTS.md` before writing
code" reaches the rules one hop later than before and reaches all of them.

---

## SOURCE-OF-TRUTH.md

**Governance infrastructure for the Umamusume Trainer Desk repository.** This document consolidates the binding rules from `PRD.md`, `ARCHITECTURE.md`, `CLAUDE.md`, `CONSTRAINTS.md`, `AGENTS.md`, `UMAMUSUME_REFERENCE.md`, and `SKILL.md` into a single source of truth for agents and human developers. Violations are hard failures.

> ### Derivation and regeneration trigger
>
> **This file is derived, not authored.** Every rule in it comes from another document, and none of
> them is restated here with independent authority. It was last reconciled at `2033434`
> (2026-09-29, `docs: track the source tier ladder`). Two of its thirteen sections carry seven
> contradictions against the tree today, and both sections are ones an agent reads first: the ADR
> index and the file map.
>
> #### Drift measured on 2026-09-30 at snapshot `c1e14a3`, by the documentation inventory
>
> | Section | What it says | What the tree says |
> | --- | --- | --- |
> | 10. ADR Index | lists 0001 to 0006 | 13 ADRs are tracked: 0001 to 0013. Seven are missing, including ADR-0007, which narrows C-7, and ADR-0012, which superseded ADR-0013 |
> | 10. ADR-0005 row | "Proposed, not built" | **DECLINED for Phase 1**, owner ruling R37, 2026-09-28, in `docs/adr/0005-support-card-entities.md`'s own Status line |
> | 10. ADR-0002 row | "Validation bound widened to 0..2000" | Accepted as a decision and **not implemented**: `app/Http/Requests/StoreTurnEntryRequest.php:49-50` still validates `between:0,1200` |
> | 12. Scenario guides | `docs/scenarios/01`–`08` | Nine files. `09-global-race-calendar.md` is 751 lines and is the read path the race grid and picker now use |
> | 12. Lore rules | `CLAUDE.md` (top section) | `.gitignore:52` ignores `/CLAUDE.md`. A fresh clone has no such file. `AGENTS.md:5` names it as "Coding rules for assistants", and 20 other tracked files cite it, two of them PHP |
> | 12. Skill registry | `.agents/skills/skills.json` (machine), `SKILL.md` (human summary) | `.agents/` is ignored, and root `SKILL.md` is a hand-counted registry written 2026-09-27 and never updated |
> | 12. Migrations | `2026_09_26_162814`–`2026_09_27_121500` | 34 migration files are tracked, running to `2026_09_29_182820_create_umamusume_profiles_table.php` |
>
> Section 12's "46 conflict rows" claim about `docs/UMAMUSUME_REFERENCE.md` was not verified by the
> inventory and is listed here as unchecked rather than as wrong.
>
> **Regenerate this file when any of these happens.** Each one is a command, not a judgment:
>
> 1. A new file lands in `docs/adr/`. Check: `git ls-files docs/adr/ | wc -l` against section 10's row count.
> 2. Any ADR's Status line changes. Check: `grep -m1 "^Status" docs/adr/*.md`.
> 3. A validation bound in `app/Http/Requests/` changes, or an accepted ADR ships.
> 4. A file is added to `docs/scenarios/`. Check: `ls docs/scenarios/ | wc -l`.
> 5. `.gitignore` changes for any path this file names as a source.
> 6. `database/migrations/` gains a file. Section 12's range is a snapshot of a count, and counts go stale first.
>
> Acting on that trigger is a Docs Writer duty (`AGENTS.md`, per-agent instructions). An agent that
> reads this file and finds a row above contradicted by the tree should fix the row in the same
> change, not follow the stale rule.

---

### 1. Product Identity & Scope

| Field                 | Value                                                                                                          |
| --------------------- | -------------------------------------------------------------------------------------------------------------- |
| **Product name**      | Trainer Desk (owner decision 2026-09-27, closes OQ-1)                                                          |
| **Target audience**   | Trainers of the Global English version of *Umamusume Pretty Derby*                                             |
| **Architecture**      | Local-only Laravel 13 tool, SQLite (WAL + busy_timeout), no auth, no SPA, no multi-user                        |
| **Phase 1 exit**      | US-1..US-3 work end-to-end; migrations/factories/seeders clean; zero lore hits; PHPStan L6 clean; Pint clean   |

**Non-goals (PRD §6 + Pre-Mortem §4 — do not build):**

1. Auth/multi-user (replaces Breeze/Sanctum)
2. SPA frontend (replaces Vue 3 + Pinia)
3. Breeding/pairing engine (replaces sire×dam system — also a lore violation) <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
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

### 2. Lore Integrity (NFR-6, C-4) — Hard Failure

**Characters are Umamusume: a humanoid race of girls. They are never animals.**

#### 2.1 Banned Vocabulary (case-insensitive grep)

```text
horse, horses, sire, dam, mare, foal, 🏇 <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
```text

Plus animal framing: `stallion`, `colt`, `filly`, `gelding`, `equine`, `pony`, `thoroughbred`, `stable` (as noun for character container), `breeding`, `pairing`, `bloodline`, `pedigree`, `lineage` (of characters), `hoof`, `mane`, `tail`, `withers`, `muzzle`, `jockey`, `rider`, `saddle`, `tack`, `reins`, `bit`, `paddock`, `herd`, `flock`, `pack`, "your horse", "your mount", "the animal", "the girl and her horse". <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->

#### 2.2 Context-Allowed Senses (grep hits here are NOT violations)

- `dam` inside `damaged`, `demand`, `command` <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
- `sire` inside `desired`, `surprise`, `Red Desire` (Umamusume name) <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
- `stable` as adjective: "stable growth", "keep the build stable" <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
- `mare` inside `nightmare` <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
- `tail` inside `detail`, `retail`, `curtail` <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
- Japanese source strings & quoted official notices (source data)
- `docs/PRE-MORTEM.md` legacy quotations (exempt once each under root C-4)

#### 2.3 Required Forms

- Race name: **Umamusume** (capital U at sentence start or as proper race name; lowercase `umamusume` mid-sentence)
- Singular = plural: "one umamusume, three umamusume" — never "umamusumes"
- Training context: **Trainee Umamusume**
- Finished career: **Veteran Umamusume** (run status `Retired` is fine — names the run, not the character)
- Human user: **Trainer**
- Sentence case in all UI copy; no decorative emoji in labels/headings/buttons

#### 2.4 Exemptions (Identifier & Mechanics — CLAUDE.md §25–26, CONSTRAINTS.md C-4)

The banned list governs **player-facing copy and character framing only**. It does NOT apply to:

- Dataset/export keys: `intelligence` (for Wit), `friend` (for Pal), `scenarios.json` field names
- Localisation-mapping strings quoted as client evidence: "Intelligence Limit Up", "Runner's Tricks ◎"
- Distinct mechanics sharing a word: "Bad Conditions", the "condition correction" in the failure formula, a "condition" item effect — these are mechanics, not Mood synonyms
- Verbatim skill/race/card/title names from source data: kept as source data, barred from promotion into UI copy (Air Messiah ruling, UMAMUSUME_REFERENCE.md §2.7)

---

### 3. Official Global Terminology (UI Labels) — CONSTRAINTS.md §4, UMAMUSUME_REFERENCE.md §6

| Concept                  | Required Label                                                                                                   | Banned Alternatives                         |                                                           |
| ------------------------ | ---------------------------------------------------------------------------------------------------------------- | ------------------------------------------- | --------------------------------------------------------- |
| Five stats               | Speed, Stamina, Power, Guts, **Wit**                                                                             | Intelligence (export key, not client label) |                                                           |
| Skill currency           | **Skill Points**, abbreviated **SP**                                                                             | Skill Pt, Skill Pts (as UI label)           |                                                           |
| Running styles           | Front Runner, Pace Chaser, Late Surger, End Closer                                                               | Runner, Leader, Betweener, Tracker, Chaser  |                                                           |
| Style abbreviations      | Front, Pace, Late, End (aptitude table only)                                                                     | Full names inside dense aptitude grid       |                                                           |
| Distances                | Sprint, Mile, Medium, Long                                                                                       | Short, Middle, Staying                      |                                                           |
| Surfaces                 | Turf, Dirt                                                                                                       | Grass, Sand                                 |                                                           |
| Support card types       | Speed, Stamina, Power, Guts, Wit, **Pal** [uncaptured], Group                                                    | Friend (for 友人)                           |                                                           |
| Gacha                    | **Scouts**                                                                                                       | Gacha, Pickup, Banner                       |                                                           |
| Pull currency            | Carats                                                                                                           | Jewels, Gems                                |                                                           |
| Character being trained  | **Trainee Umamusume**                                                                                            | Trainee Uma Musume                          |                                                           |
| Finished character       | **Veteran Umamusume**                                                                                            | Hall of Fame, graduated                     |                                                           |
| Inheritance unit         | **Spark**                                                                                                        | Factor (JP 因子 wording)                    |                                                           |
| Inheritance system       | **Inspiration**                                                                                                  | Inheritance (JP 継承 word)                  |                                                           |
| Ancestors picked for run | **Legacies**                                                                                                     | Parents, grandparents, bloodline            | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 --> |
| Energy                   | Energy                                                                                                           | Stamina for the gauge (collides with stat)  |                                                           |
| Mood                     | Mood, with five state words                                                                                      | Motivation (JP guide gloss), **Condition**  |                                                           |
| Scenario names           | Ura Finale, Unity Cup, Brighter Together Our Grand Concert; **Trackblazer** or Twinkle Star Climax for the third | Make a new track!!, Climax bare             |                                                           |
| Rest action              | Rest                                                                                                             | Break, Vacation                             |                                                           |
| Bond gauge               | Bond                                                                                                             | Friendship gauge, trust                     |                                                           |
| Friendship training      | Friendship Training, Friendship Bonus                                                                            | Rainbow training                            |                                                           |
| Hint discount            | Hint Lvl N, NN% OFF                                                                                              | Hint level N discount                       |                                                           |
| Skill acquisition states | **Suggested**, **Acquired**, **Skipped** (SkillAcquisition enum)                                                 | Planned/Used/Ignored, Proposed              |                                                           |
| Run states               | **Active**, **Completed**, **Retired** (RunStatus enum)                                                          | Ongoing/Finished/Archived                   |                                                           |

#### 3.1 Friend / Pal / Friends Distinction — **CLARIFICATION APPLIED**

- **`Pal`** — the sixth support card type (友人), used in UI labels and code. The Global client's own label for this type has **not been captured** (`[uncaptured]` in CONSTRAINTS.md); `Pal` is Game8 English's word. Do not ship it as though the client said it.
- **`Friend`** — the export key for the Pal type in GameTora data (`support-cards.json` `type` field). This is a dataset identifier, not UI copy.
- **`Friends`** — the raw client string for the support card slot label in the deck UI (e.g., "Friends" slot). **Preserve raw client strings faithfully in captures.** Do not overwrite faithful captures with wiki labels.
- **Prose & UI labels** — use the Terminology Map above: "Pal" for the type, "Friendship Training" for the mechanic.
- **Never** use "Friend" as a UI label for the 友人 support card type.

#### 3.2 Condition / Skill Pt — **CLARIFICATION APPLIED**

- The bans on **"Condition"** (for Mood) and **"Skill Pt"** (for Skill Points) apply **strictly to prose and UI labels**.
- They do **not** apply to:
  - Committed schema columns: `turn_entries.condition` (free-text notes), `training_runs.condition` if it exists
  - Raw client string captures: the "Skill Pts" header in client screenshots, "Condition" in verbatim client text
  - Mechanics: "Bad Conditions", "condition correction" in failure formula, condition item effects

---

### 4. Scenario Registry & Supersedence Rules

The four `[Global]` scenarios, in order, with their stat caps (base 1200 + bonus) and hard caps:

| #     | Scenario (Global)                       | Speed      | Stamina     | Power     | Guts     | Wit     | Hard Cap     | Status           |
| ----- | --------------------------------------- | ---------- | ----------- | --------- | -------- | ------- | ------------ | ---------------- |
| 1     | URA Finale                              | 1400       | 1400        | 1400      | 1400     | 1400    | 2000         | Active           |
| 2     | Unity Cup                               | 1300       | 1300        | 1300      | 1300     | 1800    | 2000         | Active           |
| 3     | **Trackblazer** (Twinkle Star Climax)   | 1200       | **1900**    | 1200      | 1200     | 1500    | 2000         | Active           |
| 4     | Our Grand Concert                       | **1600**   | 1300        | 1300      | 1500     | 1300    | 2000         | Known-Gap Stub   |

#### 4.1 Supersedence Rules (applied to all scenario files)

- **Active** files are the current authoritative reference for mechanics numbers.
- **Superseded** files preserve pre-release or pre-rework snapshots; their mechanics numbers are stale — read the superseding file instead.
- **Known-Gap Stub** files are boundary markers recording sourced facts only; no mechanics are inferred. Per CONSTRAINTS.md D-165, no scenario-specific chrome may be invented from assumption.
- **`03-trackblazer.md`** is a **Superseded (pre-release snapshot)** file, superseded by `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` sections "Trackblazer (uma.guide)" and "Trackblazer (GameTora)". It is preserved as written; where it hedged or guessed, read its Trackblazer sections for the shipped scenario.
- **`08-grand-masters-jp-only.md`** is `[JP-Only]` reference material. Per AGENTS.md and owner ruling: **must not be imported into app data, config, or UI copy until a Global release date exists.**

#### 4.2 Speed Ceiling Conflict — **CORRECTION APPLIED**

- **2000** is the hard cap (`hard_cap`) for all four `[Global]` scenarios (URA, Unity Cup, Trackblazer, Our Grand Concert).
- **2100** does not appear in any current Global scenario. It was a misreading; the highest Speed ceiling on Global is **1600** (Our Grand Concert). The `hard_cap` of 2000 is 400 above that.
- Two `[JP]`-only future scenarios (Beyond Dreams / らっしゃい！トレセン軒！) carry `hard_caps = 2500` (and 9999/99999 sixth element, meaning unverified). Nothing in the current Global set approaches 2500.

---

### 5. Source Tier Ladder — **ALIGNED TO [S]/[A]/[B]/[C]/[D]**

Per `UMAMUSUME_REFERENCE.md` source registry (Section 8, preamble):

| Tier      | Meaning                                       | Examples                                                            |
| --------- | --------------------------------------------- | ------------------------------------------------------------------- |
| **[S]**   | Official Cygames                              | Umamusume JP/Global official portals, news, character index         |
| **[A]**   | Major community wiki with editorial process   | Kamigame JP, GameWith JP, Game8 EN/JP, Umamusume Wiki (MediaWiki)   |
| **[B]**   | Database or tool site                         | GameTora data export                                                |
| **[C]**   | Community post requiring corroboration        | r/UmaMusume banner megathreads                                      |
| **[D]**   | Rumor / unverified                            | Datamine-only claims                                                |

- Every fact in `UMAMUSUME_REFERENCE.md` carries a tier citation.
- `[Global]` official site is Tier S; `[Global]` community guides (Game8 EN) are Tier A.
- A Tier B dataset (GameTora) needs A- or S-tier confirmation before a claim becomes app data (conflict-resolution protocol).

---

### 6. Skill Registry — **SKILL.md POINTER CORRECTED**

The skill registry lives at **`.agents/skills/skills.json`** (not `SKILL.md`).

- `SKILL.md` is a human-readable summary; the machine-readable registry is `skills.json`.
- Agents MUST read `skills.json` via the `using-agent-skills` skill (§1.2, §1.4.4 of that skill) to discover and invoke skills.
- Local project skills at `.agents/skills/` take precedence over global installs when both exist.

---

### 7. Data-Fetching Engine (JP ↔ Global Cross-Reference)

#### 7.1 Pipeline Stages (ARCHITECTURE.md §5, FR-B)

`fetch` → `snapshot` (raw body to disk, hashed) → `parse` (per-source, isolated) → `normalize` (NFKD match_key) → `match` (Exact/Alias/Fuzzy/None) → `promote` | `review`

#### 7.2 Match Tiers

- **Exact** (`match_key` equality) → auto-promote
- **Alias** (alias hit) → auto-promote
- **Fuzzy** (Levenshtein ≥ threshold, default 85%) → `match_candidates` review queue
- **None** → `match_candidates` review queue

#### 7.3 Invariants

- `is_manual` rows are **immutable to the engine** (FR-B-4, PRD US-8)
- Every engine-owned fact carries provenance: `data_sources` row with `url`, `fetched_at`, `snapshot_path`, `confidence`, `source_timezone` (FR-A-4)
- A fact without provenance is **deleted, not stored** (Data Engineer rules)
- JP datetimes parsed as `Asia/Tokyo` → stored UTC; `source_timezone` recorded (FR-B-6)
- Date-only values stay `date`, never datetimes (CLAUDE.md Planner Rule 3)
- HTTP allowlist only: `config('uma.sources')` (SSRF posture, ARCHITECTURE §8)

---

### 8. Training-Run Domain (Planner — FR-C, US-3, US-4)

#### 8.1 Schema (ARCHITECTURE.md §3)

- `training_runs`: `umamusume_id`, `scenario?`, `status` (Active|Completed|Retired), `inheritance_parent_a_id?`, `inheritance_parent_b_id?`, `notes?`
- `turn_entries`: `training_run_id`, `turn` (unique per run), `speed/stamina/power/guts/wit` (0..2000), `sp?`, `condition?` (free-text notes)
- `run_skills` pivot: `status` (Suggested|Acquired|Skipped), `turn_acquired?` — **Suggested = planned pre-run**

#### 8.2 Deterministic Math (Planner Rules 4, 5)

- All run math is deterministic over Trainer-entered `turn_entries` — no randomness, no simulation, no speculative prediction
- Every computed number must be explainable from the entered turns
- Stat bounds: **0..2000** per stat (per scenario's `hard_cap`), turn ≥ 1 (StoreTurnEntryRequest, ADR-0002 Option B accepted)
- The 1,200 halved-gains line and the scenario ceiling are **two visible, differently-drawn markers** (ADR-0002 amendment, DESIGN.md §6.5)

#### 8.3 Timezone Correctness (Planner Rule 3, US-7)

- Store UTC; parse JP-source datetimes as `Asia/Tokyo`; record `source_timezone`
- Date-only stays `date`; display via `config('uma.display_timezone')`
- Any date-arithmetic change requires a test with `freezeTime()`

---

### 9. Quality Bar (CONSTRAINTS.md) — Verification Sequence

| #     | Dimension         | Threshold                                                                          | Command                                                                                 |
| ----- | ----------------- | ---------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------- |
| C-1   | Tests             | All Pest tests pass; every behavior change ships a test                            | `php artisan test --compact`                                                            |
| C-2   | Static analysis   | PHPStan level 6 (Larastan), zero errors                                            | `vendor/bin/phpstan analyse --no-progress`                                              |
| C-3   | Formatting        | Pint clean                                                                         | `vendor/bin/pint --dirty --format agent` then `vendor/bin/pint --test --format agent`   |
| C-4   | Lore              | Zero unexplained hits of banned patterns                                           | `composer lore` (GNU `make` cannot run on this host; KI-4)                       |
| C-5   | Migrations        | Fresh migrate + seed succeeds; every migration has working `down()`                | `php artisan migrate:fresh --seed`                                                      |
| C-6   | Performance       | Catalog index < 200 ms at ~1k Umamusume / ~2k skills                               | Manual benchmark per PRD §8                                                             |
| C-7   | UI states         | Every data view renders empty, loading/refresh, error                              | Review checklist                                                                        |
| C-8   | Dependencies      | No new package without approval; `composer audit` / `npm audit` no critical/high   | `composer audit`; `npm audit --omit=dev`                                                |
| C-9   | TypeScript        | Zero errors from `tsc --noEmit` (`strict: true`, `noEmit: true`)                   | `npm run typecheck`                                                                     |

#### 9.1 Floor (Never, in Any Change)

- No new suppressions: `@phpstan-ignore`, `@phpstan-` escapes, `eslint-disable`, `@ts-ignore`, `# noqa`
- No stub bodies: `throw new \Exception('not implemented')`, empty `catch {}`, TODO placeholders
- No deleted or skipped tests without human approval and reason in commit message
- No engine write to rows with `is_manual = true` (PRD FR-B-4)
- No fact stored without provenance (AGENTS.md Data Engineer)
- No fetch URL outside `config('uma.sources')` allowlist (SSRF)
- No business logic in controllers; no inline `$request->validate()` (CLAUDE.md)

#### 9.2 Verification Sequence Before Hand-off

1. `php artisan migrate:fresh --seed`
2. `php artisan test --compact` (narrow first, full at hand-off)
3. `vendor/bin/pint --dirty --format agent`
4. `vendor/bin/phpstan analyse --no-progress`
5. `npm run typecheck`
6. `composer lore` (GNU `make` cannot run on this host; KI-4)

---

### 10. ADR Index (Binding Decisions)

| ADR     | Title                                              | Status                    | Summary                                                                                                                                                                                                 |
| ------- | -------------------------------------------------- | ------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 0001    | Lift no-prediction non-goal for energy guidance    | Accepted                  | Energy guidance permitted; race outcomes still prohibited                                                                                                                                               |
| 0002    | Scenario-aware stat caps exceed validation bound   | **Accepted (Option B)**   | Validation bound widened to **0..2000** sourced from `scenarios.hard_cap`; 1200 halved-gains line + scenario ceiling remain two visible UI markers                                                      |
| 0003    | Consolidated Phase 1 schema expansion              | **Accepted**              | Adds Energy, Fans, Mood, TurnEvent, ScenarioSlots, RaceEntry; US-10 promoted to P1; stat bound reads from scenario's `hard_cap`                                                                         |
| 0004    | Aptitude & scenario cap reference data             | **Accepted**              | Stores 10 aptitude letters on `umamusume`; creates `scenarios` table with 5 caps + `hard_cap` + provenance; closes OQ-4 for these domains; `GametoraScenarioParser` implements `ScenarioSourceParser`   |
| 0005    | Support card entities                              | Proposed, not built       | Schema shapes specified; not part of design until owner settles scope (PRD §6.9)                                                                                                                        |
| 0006    | Design authority & theme default                   | Accepted                  | Dark-first tactical-athletic system; theme preference in SQLite not localStorage                                                                                                                        |

---

### 11. Open Questions (PRD §7)

| ID     | Question                             | Status                                                                           |
| ------ | ------------------------------------ | -------------------------------------------------------------------------------- |
| OQ-1   | Product name                         | **CLOSED 2026-09-27**: Trainer Desk                                              |
| OQ-2   | Concrete fetch sources for Phase 1   | Open — each addition = legal/robots.txt review + parser class                    |
| OQ-3   | `uma:fetch` on scheduler vs manual   | Default: manual, until rate-limit behavior observed                              |
| OQ-4   | Aptitude & scenario caps scope       | **CLOSED 2026-09-27**: Enters as engine-owned facts with provenance (ADR-0004)   |

---

### 12. Key File Locations

| Domain                 | Files                                                                                                                                                         |
| ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Product requirements   | `PRD.md`                                                                                                                                                      |
| System design          | `ARCHITECTURE.md` (authoritative), `ARCHITECTURE-ESSENTIALS.md` (digest)                                                                                      |
| Mechanics reference    | `docs/UMAMUSUME_REFERENCE.md` (8 sections, 46 conflict rows)                                                                                                  |
| Scenario guides        | `docs/scenarios/01`–`08` (07=stub, 08=JP-Only)                                                                                                                |
| Design system          | `docs/design-research/DESIGN.md` (root), `docs/design-research/CONSTRAINTS.md` (UI contract)                                                                  |
| Quality bar            | `CONSTRAINTS.md` (root), `docs/design-research/CONSTRAINTS.md` (additive)                                                                                     |
| Agent roles            | `AGENTS.md`                                                                                                                                                   |
| Lore rules             | `CLAUDE.md` (top section), `CONSTRAINTS.md` C-4                                                                                                               |
| Skill registry         | `.agents/skills/skills.json` (machine), `SKILL.md` (human summary)                                                                                            |
| ADRs                   | `docs/adr/0001`–`0006`                                                                                                                                        |
| Fetch pipeline         | `app/Services/DataPipeline/` (Contracts, Parsers, PipelineRunner, SourceFetcher, CrossReferenceMatcher, NameNormalizer)                                       |
| Models                 | `app/Models/{Umamusume,UmamusumeAlias,Skill,TrainingRun,TurnEntry,Scenario,DataSource,MatchCandidate,RunSkill,ScenarioRace,RaceEntry,TurnEvent,Preference}`   |
| Migrations             | `database/migrations/2026_09_26_162814`–`2026_09_27_121500`                                                                                                   |

---

### 13. Escalation Paths (AGENTS.md)

1. **Role-level conflict** (e.g., Laravel Dev needs schema change) → Architect decides within PRD scope
2. **Scope conflict** (feature not in PRD, or non-goal requested) → Architect escalates to human owner
3. **Lore ruling dispute** → Lore Guardian verdict stands; only human owner can override in writing
4. **Constraint relaxation** (CONSTRAINTS.md threshold in the way) → QA/Reviewer escalates to human owner
5. **Fetch-source legality/robots uncertainty** → Data Engineer stops and escalates to human owner
6. **Cross-model review disagreement after 3 doubt cycles** → Surface both positions to human
7. **Planner feature implying cut repo #4 system** (simulation, snapshots, predictions, dual storage) → Planner Domain Specialist escalates to Architect → PRD §6 check → human owner

---

*This document is governance infrastructure. It must remain in the repo to bind future agents and human developers. Last updated: 2026-09-27 with 5 corrections + 2 clarifications applied per owner approval.*

---

## GATE-REGISTRY.md

This registry and root `CONSTRAINTS.md` are the source of truth for global gates.
Slice plans such as `PLAN.md` may add slice-specific exit criteria but cannot
override C-1 through C-8 or G-60. Precedence for gate questions:

1. `CONSTRAINTS.md` (the bar; never relaxed to pass a check)
2. `docs/GATE-REGISTRY.md` (this file: how each gate is run, evidence, exceptions)
3. ADRs (`docs/adr/`)
4. Root `DESIGN.md`
5. Slice plans (`PLAN.md`, phase docs)

The research corpus `docs/design-research/CONSTRAINTS.md` owns the D-number design
rules and the prototype gate series (G-1..G-17, G-18, G-21, G-40). This registry
does not restate them; it records where they run and how they bind the shipped app.

### Gate record format

```text
ID | Name | Type (Automated/Review/Manual) | Scope | Command | Pass | Fail |
Evidence | Exceptions | Owner | Status
```text

### Global gates

| ID   | Name                                        | Type                    | Command                                                                                                                                                                                          | Pass criteria                                                     | Fail =                                                   | Evidence                                      | Exceptions                                                              | Owner                                                                     | Status                                                 |                 |                           |              |    |        |
| ---- | ------------------------------------------- | ----------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ----------------------------------------------------------------- | -------------------------------------------------------- | --------------------------------------------- | ----------------------------------------------------------------------- | ------------------------------------------------------------------------- | ------------------------------------------------------ | --------------- | ------------------------- | ------------ | -- | ------ |
| C-1  | Tests                                       | Automated               | `vendor/bin/pest --compact`                                                                                                                                                                      | zero failures                                                     | any failure                                              | command output in hand-off                    | reasoned skips only (`markTestSkipped` with text)                       | QA                                                                        | active                                                 |                 |                           |              |    |        |
| C-2  | Static analysis                             | Automated               | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`                                                                                                                                     | `[OK] No errors` at level 6                                       | any error                                                | command output                                | none; `@phpstan-ignore` is a Floor violation                            | Laravel Dev                                                               | active                                                 |                 |                           |              |    |        |
| C-3  | Style                                       | Automated               | `vendor/bin/pint --test --format agent`                                                                                                                                                          | `"result":"passed"`                                               | any drift                                                | command output                                | none                                                                    | Laravel Dev                                                               | active                                                 |                 |                           |              |    |        |
| C-4  | Lore                                        | Automated + Review      | `composer lore` and `composer lore-code` (git grep passes) + context ruling — GNU `make` cannot run on this host (KI-4)                                                                                                                                                       | zero *unexplained* hits                                           | violation without an allowed classification (below)      | hit list + one-line ruling per hit            | four allowed hit classes (below)                                        | Lore Guardian                                                             | active; blind spots below                              |                 |                           |              |    |        |
| C-5  | Migrations                                  | Manual/Approval         | `php artisan migrate` + `db:seed` on a fresh scratch DB (`DB_DATABASE=` pointed at an empty file); `migrate:fresh --seed` against the shared dev file is destructive and needs explicit approval | clean up+down, seed idempotent                                    | any error                                                | command output                                | destructive variant by approval only                                    | Laravel Dev                                                               | active                                                 |                 |                           |              |    |        |
| C-6  | Floor (no suppressions/stubs/deleted tests) | Review + grep           | `git grep -nE "@phpstan-ignore                                                                                                                                                                   | eslint-disable                                                    | @ts-ignore                                               | not implemented                               | catch \{\}                                                              | TODO" app resources tests config` + diff review for deleted/skipped tests | zero hits or an approved, commit-noted reason          | unexplained hit | grep output + commit note | none (floor) | QA | active |
| C-7  | UI states                                   | Review (per ADR-0007)   | enumerate per data view: empty, error, data required; custom loading required only for user-initiated async actions                                                                              | every view's state table complete; no decorative skeletons        | missing required state, or async action with no feedback | state enumeration in the view's flow/spec doc | initial server-render navigation may rely on browser loading (ADR-0007) | Frontend                                                                  | active                                                 |                 |                           |              |    |        |
| C-8  | Dependencies                                | Review + automated      | `composer show --direct` / `package.json` diff vs approved list; `composer audit`; `npm audit --omit=dev`                                                                                        | no new package without human approval; no reachable critical/high | unapproved addition                                      | diff + audit output                           | approval recorded in PR/commit                                          | Architect                                                                 | active                                                 |                 |                           |              |    |        |
| G-60 | Retired literals                            | Automated + scope rules | run via `tools/gate.py` family / reviewer grep of the retired-value registry                                                                                                                     | retired values absent from active paths                           | any hit in an active path                                | grep output                                   | ignored paths below; `RETIRED LITERAL` blocks below                     | Pre-Dev                                                                   | **partial: registry live, scanner pending (see Gaps)** |                 |                           |              |    |        |

### G-60: retired-literal scoping (codified per owner ruling + D-286)

- **Checked (active) paths:** root `DESIGN.md`, root `CONSTRAINTS.md`, `app/`,
  `resources/`, `config/`, routes, database, tests, shipped prototypes.
- **Ignored (historical) paths:** `docs/adr/**`,
  `docs/design-research/MECHANICS-TRANSLATION-TRIAGE.md`,
  `docs/PRE-MORTEM.md`, `docs/design-research/_scratch/**`, `.kilo/**`, `vendor/`,
  `node_modules/`. These record what changed and why; quoting a retired value to
  retire it is correct ADR hygiene (precedent: `ADR-0001` §6 RESOLVED block).
- **Supersede blocks:** if a retired literal must appear in an active file, it is
  wrapped in `> [!WARNING] RETIRED LITERAL:` and the scanner skips that block via
  the admonition marker. No semantic parsing; path scoping plus the marker are the
  whole mechanism.
- **Exit behavior:** 0 = clean; nonzero with per-hit path:line list. A `_scratch`
  script being re-run must have its literals updated before execution (D-286).
- **Known limitation:** the retired-value list itself lives with the owning slice
  (cap rows, transposed Trackblazer stats, withdrawn glosses); G-60 fails closed
  only for values registered there.

### Disclosure pattern (false zero), settled ruling 2026-09-28

- A stat, metric, or field that is zero because it is untracked or not applicable
  renders as `N/A` with a tooltip where useful, e.g. `title="Not tracked in this
  scenario"`, optionally plus a static disclosure line ("breakthrough not tracked
  - deck untracked", per `stat-band.blade.php:153`).
- Never render `0` for a value the schema cannot observe (Planner Rule 5).
- Never use an em dash as the disclosure glyph in shipped copy (R-02; no
  C-4/R-02 carve-out granted). Open breach: `KNOWN-ISSUES.md` KI-7 (Frontend,
  do-not-land).
- Checked by: review (G-C7 style enumeration in the component spec); `tools/gate.py`
  checks D-79 em dash only in prototype HTML today, not Blade (Gaps below).

### C-4 allowed hit classes (grep proposes, Guardian decides)

1. Naming the banned list to forbid it (rules files, this registry, ADR text).
2. Substrings inside ordinary words or proper nouns: `damaged`, `desired`, <!-- lore-ignore-line class=1 cite=GATE-REGISTRY.md#C-4 -->
   adjective `stable`, `Red Desire`, "La dama perfetta". <!-- lore-ignore-line class=2 cite=GATE-REGISTRY.md#C-4 -->
3. Verbatim quoted game/client source data where the term is data, not framing
   (display path is the gated surface; dataset keys like `intelligence`,
   `friend` are out of scope per C-4's copy-and-framing boundary).
4. A gate's own source: the pattern list in `tools/gate.py` and the grep list in
   `tools/lore.php` match `make lore` by construction, so each scanner is a permanent
   self-hit. Expect them, do not clear them. Composition on the `ebfc227` tree: the sweep
   prints 98 match lines plus 49 exempt lines, of which 5 distinct `tools/lore.php` lines and 3
   `tools/gate.py` lines are the scanners naming their own patterns. The total counts
   prints, not distinct lines - a line matching two of the three greps prints twice - and
   it moves whenever a rules file quotes a banned word, so treat it as a measurement of
   that day, never as a threshold. `lore-code` cannot self-hit: `tools/` is outside its
   path list.
Ambiguous framing (a real violation vs a quote) requires a Lore Guardian or owner
ruling before merge; the ruling is recorded next to the hit list.

### lore-ignore-line, the line-scoped exemption (R51, 2026-09-29)

A line that exists to state or itemize a lore ruling is the gate's own text, not a new hit every
time the gate runs. `lore-ignore-line` makes that case explicit and auditable instead of
re-counting it each slice. The recorded readings ran 131 → 132 → 133 → 137 → 140 → 144 → 147
(`slice-3-2026-09-28.md:58,206`, `slice-5-2026-09-28.md:278`, `slice-6-2026-09-28.md:40,174`,
`slice-7-2026-09-28.md:147`, `slice-9-2026-09-28.md:20`), and every step up was a rules file or a
record writing the banned words into a table in order to rule on them. The 147 is this slice's
opening reading, carried by `slice-10-2026-09-29.md`.

- Shape: an HTML comment on the line it exempts, carrying one allowed-hit class from above plus
  the ruling it answers to - `<!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->`.
- Scope: one line. Not a file, not a block, not a directory. Removing the marker restores the hit.
- Where: `docs/` only. `LoreGateParityTest` fails on a marker anywhere else, and the `lore-code`
  gate does not honour markers at all, so one planted outside `docs/` buys nothing - the line it
  tried to hide still prints in the app-path sweep. A consequence worth knowing before writing the
  next rules file: a document outside `docs/` cannot show the full form, because writing it makes
  it one. `PLAN.md` names this section instead.
- What it is not: a ruling. It records a class and a citation that already exist, and a marker
  missing either is a test failure. The Guardian still decides the case; the marker only stops the
  count from re-litigating a settled one.
- Both runners honour it, and the two are measured against each other: `make lore` pipes each grep
  through `grep -v` on the comment opener quoted above, `composer lore` skips the same literal in
  docs mode. On the `ebfc227` tree the shell stages print 20 + 35 + 43 = 98 and the runner reports
  98 with 49 exempt.

Baseline moved 147 → 98 on 2026-09-29 by marking 41 ruling-table lines across 10 `docs/` files:
the §3.1 vocabulary table and the §3.2 allowed-sense list in `design-research/CONSTRAINTS.md`, the
two C-4 class rows here, the hit-itemization rows in the slice-3, slice-5, slice-6 and slice-9
records, the rule lines in `flows/create-run-and-legacy-select.md`,
`requests/game-mechanics-condition-labels.md` and the frontend-review grep report, and the JP-only
warning in `scenarios/08`.

What stays counted, on purpose: the 20 `docs/UMAMUSUME_REFERENCE.md` lines and the
`docs/scenarios/*` guide rows that quote client and wiki vocabulary as source data; five
`design-research/DESIGN.md` prose lines and the D-186 and D-268 lines in
`design-research/CONSTRAINTS.md`, where a banned word is used as ordinary English rather than to
name the ban; three `design-research/_scratch` patch scripts holding quoted doc text; ADR-0005's
adjective use; and one PNG that matches as a binary file and cannot carry a comment. Those are
reword-or-rule cases, not self-reference, and marking them would hide the question instead of
answering it.

### Known gate gaps (recorded, not hidden)

| Gap                                                                                                                    | Consequence                                                                                                                                                                                                                                                                                            | Fix owner                                                                                                                  |
| ---------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------------- |
| `make lore` uses `git grep` on tracked files: blind to untracked copy                                                  | fresh, unstaged copy is unswept by the repo-wide pass; `lore-code` reads untracked but only inside app paths, so an unsaved `docs/` edit is unseen by both                                                                                                                                             | Pre-Dev (registry tells reviewers to sweep dirty files; KI-4 closed 2026-09-28 for the runner, not for this scope split)   |
| `make lore` vocabulary: 23 words over three greps (`ee97869`), while client-string terminology stays gate.py's scope   | two vocabularies exist; both `make lore` and `composer lore` read one list, and `LoreGateParityTest` fails if the Makefile and `tools/lore.php` drift                                                                                                                                                  | Pre-Dev: point both at `docs/design-research/CONSTRAINTS.md` §3.1                                                          |
| The shipped-Blade dash check lives in the Pest suite, not in `tools/gate.py`                                           | `composer test` fails on an en or em dash in any `.blade.php` under `resources/views` (`RenderedCopyHygieneTest`, which strips the three comment forms prose hides in); running `gate.py` alone still checks D-79 only in prototype HTML, so a scan-by-gate.py pass is not proof the Blade sweep ran   | Pre-Dev: fold the view sweep into `gate.py` or cite the test wherever the scanner is the only gate                         |
| G-60 scanner does not yet read a retired-value registry file                                                           | G-60 currently reviewer-enforced                                                                                                                                                                                                                                                                       | Pre-Dev at next ADR amendment cycle                                                                                        |
| C-7 loading-state enforcement is interpretive                                                                          | per ADR-0007 clause 2; review checks the state enumeration                                                                                                                                                                                                                                             | Frontend                                                                                                                   |
| Stale mirrors: `.kilo/worktrees/giddy-chronometer/docs/design-research/_scratch/gate.py` exists                        | edits there are inert; never treat as the live gate                                                                                                                                                                                                                                                    | whoever prunes the worktree cache                                                                                          |

### Tooling placement

```text
Canonical gate script: tools/gate.py   (moved 2026-09-28 from
docs/design-research/_scratch/; run: python tools/gate.py; exit 0 = pass)
Forbidden stale copies: docs/design-research/_scratch/gate.py (moved away),
.kilo/worktrees/**/gate.py (stale mirrors, not authoritative)
Data still in provenance: docs/design-research/_scratch/tokens.json (read by
tools/gate.py as a measured-anchor input; it is data, not tooling)
Makefile gate targets: lore, lore-code (git grep); tests/lint/stan as before
```text

### Changelog

- 2026-09-28: registry created (T4). C-7 note added to `CONSTRAINTS.md`
  referencing ADR-0007. gate.py relocated. G-60 scoping codified from the owner
  ruling + D-286. Disclosure pattern codified from the settled `N/A` ruling.

---

## PRE-MORTEM.md

Pre-Mortem Report: Legacy Consolidation & Modernization

Date: 2026-09-27. Produced before Artifacts 1-6, per the phase gate. Revision 0.1 covered repos 1-3; §4 is the revision 0.2 addendum for repo #4.

Sources examined: `D:/Projects/uma-companion`, `D:/Projects/uma-tracker`, `D:/Projects/umamusume-tracker-app`. Addendum source (read-only scan): `D:/umamusume_dev/uma_musume_race_planner`.

### 1. Over-Engineering Audit

Threshold applied (constraint-driven-development): every class must map to a PRD user story, or it does not ship.

| Legacy component                                           | Source repo                                  | Verdict                  | Reason                                                                                                                                                                                                 |                                                           |
| ---------------------------------------------------------- | -------------------------------------------- | ------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | --------------------------------------------------------- |
| Breeze/Sanctum auth stack                                  | uma-tracker, umamusume-tracker-app (dormant) | Cut                      | Local-only, single-Trainer tool. Auth is attack surface with zero utility.                                                                                                                             |                                                           |
| Vue 3 + Pinia SPA frontend                                 | uma-companion                                | Cut                      | Blade + Tailwind v4 already in this skeleton. A SPA pipeline for one local user is maintenance for nobody.                                                                                             |                                                           |
| Breeding/lineage engine (pairing, eligibility, validation) | uma-companion                                | Cut, reduced             | Maps to no user story in the other apps. The one useful fact (which two Umamusume provided inheritance) becomes two nullable FKs on `training_runs`. Its sire/dam vocabulary is also a lore violation. | <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 --> |
| EAV `attributes` table                                     | umamusume-tracker-app                        | Cut                      | Five stats are fixed. Five integer columns beat a name/value table on every axis.                                                                                                                      |                                                           |
| Excel export (`maatwebsite/excel`)                         | uma-tracker, umamusume-tracker-app           | Cut                      | Heavy dependency for a local tool. CSV/JSON cover the need with zero dependencies.                                                                                                                     |                                                           |
| Event/banner calendar + goal progress                      | uma-companion                                | Defer (Phase 1 Non-Goal) | Useful, but entirely dependent on volatile scraped schedule data. Shipping it first would make the most visible feature the most fragile.                                                              |                                                           |
| Redis/queue server, multiple cache stores                  | implied by scale patterns                    | Cut                      | SQLite database cache + the queue worker from `composer run dev` is the ceiling this tool needs.                                                                                                       |                                                           |
| Idempotency keys, cursor pagination, OpenAPI generation    | api-and-interface-design defaults            | Trim                     | Single local consumer. Versioned `/api/v1`, one error shape, offset pagination. Nothing beyond.                                                                                                        |                                                           |
| Dual schemas (legacy EAV vs normalized target)             | umamusume-tracker-app                        | Cut                      | One normalized schema from day one. No legacy DB migration promised.                                                                                                                                   |                                                           |

What survives, and why: catalog browsing (all three apps had it), training-run logging with per-turn stats (all three independently built it), skill acquisition tracking (two of three), the JP to Global cross-reference engine (stated core logic, absent everywhere), CSV/JSON export (cheap, Trainers already use it).

### 2. Failure Point Analysis

#### Data-scraping fragility

Failure: source sites change markup, add rate limits, or block IPs; a parser written against today's DOM silently returns wrong data next month.

Mitigation: every fetch stores the raw snapshot on disk (`storage/app/private/snapshots`) before parsing, so a broken parser is fixed and re-run without re-fetching. Parsers are isolated per source behind one interface. Every stored fact carries provenance (`data_sources`: URL, fetched_at, snapshot path, confidence). Fetch failures never mutate existing rows: the pipeline stages, then promotes only on a confident match. Rows a human corrected (`is_manual = true`) are never overwritten.

Residual risk accepted: web-search cross-referencing is heuristic. The engine proposes, the Trainer disposes. Unmatched and low-confidence candidates land in a review list, not the catalog.

#### JP to Global data discrepancies

Failure: romanization differences, localized renames, release lag (live on JP, absent on Global), skill translations that diverge from community naming.

Mitigation: matching runs on a normalized `match_key` (NFKD, case-folded, punctuation and middle-dot stripped) plus an explicit `umamusume_aliases` table, never on display strings. Match results are tiered (Exact / Alias / Fuzzy / None); only Exact and Alias auto-promote, Fuzzy goes to human review. `release_status` is a first-class enum (`GlobalReleased`, `GlobalAnnounced`, `JapanOnly`), so "JP only" is data, not an error.

Residual risk accepted: fan-community skill translations have no single authority. Skill cross-referencing is Phase 2, starting from Trainer-editable aliases.

#### Cache-invalidation races

Failure: `composer run dev` runs web server and queue worker concurrently; a manual refresh during a scheduled fetch double-writes rows or double-burns a rate-limited source.

Mitigation: one writer at a time via `Cache::lock` around each per-source fetch (atomic claim), fetch jobs unique-queued per source. Catalog reads use TTL cache with stale-while-revalidate semantics: a refresh never blocks a read. For a single-user local tool this is the whole concurrency model.

#### Filesystem/SQLite persistence risks

Failure: SQLite under concurrent writers throws `database is locked`; a crashed write corrupts the only copy of the Trainer's logged runs.

Mitigation: WAL journal mode + `busy_timeout` on the sqlite connection; all multi-write operations inside `DB::transaction`. Backup is a documented file copy after a WAL checkpoint (`uma:backup`). SQLite is the only supported production driver; no DB server is assumed anywhere.

### 3. Edge Case Identification

1. Identical JP/EN names vs divergent localizations. Some names romanize identically (Special Week / スペシャルウィーク); others were renamed in localization. `match_key` handles the identical case, alias rows the divergent case, review queue when neither fires. Seed data includes one of each so scaffolding tests cover both paths.
2. JP-only characters. `release_status = JapanOnly`, Global fields nullable, UI labels JP-sourced data explicitly ("Not yet released on Global") so a Trainer never plans around unavailable content. Debut dates are nullable dates, never sentinel values.
3. Unicode edge cases. Full-width vs half-width katakana, middle dot (・) in multiword names, prolonged sound mark (ー), combining marks. NFKD folds width variants; middle dot and punctuation are stripped for `match_key`, preserved in display names. SQLite lacks MySQL-style utf8mb4 collation, so matching always goes through the normalized key, never `=` on display strings.
4. Timezone handling. JP schedules are announced in JST; the Trainer lives elsewhere. Store UTC; convert JP-source datetimes from `Asia/Tokyo` at fetch time with the source timezone recorded in provenance; display in `config('uma.display_timezone')`. Date-only values (debut dates) stay dates, never datetimes, so no shift can move a release day.
5. Lore leaks from legacy data. Legacy repos contain a horse emoji (🏇) in UI and one schema uses an equine table name for characters (uma-companion). Banned-pattern grep (`horse`, `🏇`, `sire`, `dam`, `mare`, `foal`) runs in review; see CLAUDE.md. <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->

### 4. Addendum (revision 0.2): `uma_musume_race_planner` [rev 0.2 — repo #4]

Fourth repository scanned read-only on 2026-09-27. Sections 1-3 above stand unchanged as revision 0.1. Despite its name, the repo is a career-run planner/tracker (turn-by-turn stats, skills, SP, mood/condition, per-plan dashboards), not a race simulator. It contains no simulation, randomness, or prediction engine; its "predictions" are user-entered aptitude grades. It also carries CSV/JSON importers for a fifth legacy app (`uma-run-tracker`), so the consolidation target count is four repos plus one data-format reference.

#### 4.1 Over-engineering found in repo #4 (verdicts)

| Component                                                                                        | Verdict               | Reason                                                                                                                                                                                               |
| ------------------------------------------------------------------------------------------------ | --------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Dual storage modes (browser localStorage "local runs" vs account/DB runs) + conversion service   | Cut                   | A local-only tool has exactly one store: SQLite. Dual-store parity is pure liability, and browser-only data is one cleared cache away from loss.                                                     |
| Sanctum auth + login/register routes + hardcoded "Public User" id=1                              | Cut                   | Same verdict as §1 row 1. The id=1 FK assumption is a migration trap, not a feature.                                                                                                                 |
| Livewire 3 component tree (~20 components)                                                       | Cut                   | Not installed here; Blade + vanilla JS is the stack (ARCHITECTURE §7). Carrying Livewire would add a dependency for zero new capability.                                                             |
| Race-day snapshots (immutable `career_snapshots` + SnapshotService)                              | Cut                   | Speculative archival of data SQLite already persists. CSV/JSON export (US-6) covers the real need: getting data out.                                                                                 |
| Manual race predictions table (`race_predictions`, venue/ground/aptitude grades)                 | Cut (Phase 1)         | No race/calendar entity exists in the unified schema; predictions hang off free text. Revisit only if US-10 (P2) is ever scheduled.                                                                  |
| Trainee image upload + ImageProcessingService                                                    | Cut                   | No user story; adds an upload attack surface to a tool that otherwise accepts only form fields and fetched text.                                                                                     |
| Excel/Markdown export paths                                                                      | Cut                   | §1 row 5 stands. CSV/JSON only. Note: repo #4's README cites Laravel Excel but the dependency is absent from its composer.json; the claim was already dead.                                          |
| DB-level enum columns (career_stage, class, status on `plans`)                                   | Cut                   | Repo #4's own migration risk: enum DDL complicates SQLite/MySQL portability. Unified schema uses string columns + PHP backed enums (ARCHITECTURE §3).                                                |
| Soft deletes on plans                                                                            | Cut                   | Domain rule: no soft deletes; Trainer-data deletion is explicit and cascades.                                                                                                                        |
| JSON-typed columns on `umamusume` (growth_rates, aptitudes, base_stats)                          | Defer (INVESTIGATE)   | Useful planning data, but engine-owned facts must arrive through the fetch pipeline with provenance, not hardcoded seeders. Phase 2 candidate once sources (OQ-2) are chosen. No column added now.   |
| Turn tracker + per-turn stat logging (StatProgressService)                                       | Keep, merged          | Independently validates the `turn_entries` design. One concrete gain adopted: deterministic stat bounds (0..1200) become validation rules in `StoreTurnEntryRequest`.                                |
| Skill 3-state status (Acquired / Skipped / Suggested)                                            | Keep, refactored      | `SkillAcquisition` enum gains `Suggested` (planned-but-not-yet-taken), matching how planners actually work: plan vs actual comparison (US-4).                                                        |
| CSV/JSON legacy importers (MigrateLegacyCsv/Json, FormatDetector)                                | INVESTIGATE           | Real, working reference code for the `uma-run-tracker` JSON shape. Not carried in (Non-Goal 7 stands for Phase 1); located and cited if the Trainer later asks for import.                           |

#### 4.2 New failure points

**Date/time calculation errors.** Repo #4 hardcodes `Asia/Kuala_Lumpur` in `config/app.php` while its domain data is JP-sourced; any date-sensitive logic silently shifts by 1-2 hours versus JST and by a day near midnight boundaries. Mitigation (already designed, now load-bearing): store UTC, record `source_timezone` per fact, convert JP datetimes from `Asia/Tokyo` at parse time, keep date-only values as `date`, display via `config('uma.display_timezone')`. The app timezone stays UTC; no locale-specific hardcoding.

**Planner recalculation after data updates.** A run's turns and skills reference catalog rows; when a fetch updates or merges catalog data, existing runs must not silently change meaning. Mitigation: runs reference `skill_id`/`umamusume_id` FKs (display follows the catalog, history of entered numbers never mutates); candidate merges that would move FKs go through the human review queue (`match_candidates`), never auto-merge; `is_manual` rows are immutable to the engine. There are no derived/cached calculations over catalog data, so there is nothing to recalculate: all run math (deltas, totals) is computed from Trainer-entered `turn_entries` at render time, deterministically.

**Browser-only data loss.** Repo #4's localStorage mode means authoritative run data can exist only in a browser profile. Mitigation: single store (SQLite), and `uma:backup` (NFR-5) is the documented durability path.

#### 4.3 New edge cases

1. Stat bounds. Repo #4 encodes MAX_STAT_VALUE = 1200, MIN 0, and 70-78 turn careers. Adopted as validation bounds (0..1200 per stat, turn >= 1) rather than free integers; bounds live in one Form Request, not scattered.
2. `Suggested` skills on a run. A planned skill has no `turn_acquired`; UI and export must distinguish "planned" from "acquired turn N" and "skipped".
3. Lore violations in repo #4 to never copy: "racehorses" in two character-list views, 🏇 in its README feature list, "horse" in a skill description seeder and legacy docs, "horse girl" in its BRD. All fail the banned-pattern grep; replacements are "Umamusume"/"umamusume" per CLAUDE.md Lore Rules. <!-- lore-ignore-line class=1 cite=CONSTRAINTS.md#3.1 -->
4. String-slug primary keys on its `umamusume` table vs bigint FKs elsewhere. Unified schema keeps bigint PK + unique slug; if its seed data is ever imported, slugs map to `slug` column values, not PKs.

---

## README.md (frontend-review/2026-09-28)

Trainer Desk — Frontend Audit, 2026-09-28

Documentation-only screenshot audit of every user-facing page. No application
code, test, view, config, route, or dependency was modified by this audit; the
tree under `app/`, `resources/`, `config/`, `tests/`, `routes/`, and `database/`
is untouched by this branch's commit (proof in §7).

---

### 1. Environment header

| Item             | Value                                                                                                                                                                                                                                                                                                                                                       |
| ---------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Branch           | `docs/frontend-review` (based on `master` @ `7d4b8cf`)                                                                                                                                                                                                                                                                                                      |
| Boot commands    | `New-Item -ItemType Directory .scratch-uma` → `$env:DB_DATABASE="D:\Projects\umamusume-laravel13\.scratch-uma\frontend-review.sqlite"` → `php artisan migrate --seed` → `php .scratch-uma\frontend-review-fixture.php` (via tinker `require`) → `.scratch-uma\serve-8144.cmd` (sets `DB_DATABASE`, then `php artisan serve --host=127.0.0.1 --port=8144`)   |
| Scratch DB       | `.scratch-uma/frontend-review.sqlite` (gitignored via `.gitignore:88 /.scratch-uma/`; the shared `database/database.sqlite` was never written — verified read-only)                                                                                                                                                                                         |
| Theme control    | `Preference::put('theme', 'light'/'dark')` on the scratch DB; the layout renders `data-theme` server-side from `AppServiceProvider`'s view composer                                                                                                                                                                                                         |
| Server PID       | 20036 (`php-cgi.exe` child of the 8144 wrapper; `netstat`-verified bound to 127.0.0.1:8144 and serving the scratch DB)                                                                                                                                                                                                                                      |
| Timestamp        | 2026-09-28, captures 21:22–22:58 local (all taken after the scratch DB, the final view-code state, and the server-DB confirmation were in place)                                                                                                                                                                                                            |
| Viewport         | 1280×800, full-page captures, Playwright MCP                                                                                                                                                                                                                                                                                                                |
| Capture format   | `docs/frontend-review/2026-09-28/{page-slug}-{state}-{theme}.png`, each with `.console.txt` and `.network.txt` sidecars                                                                                                                                                                                                                                     |

**Caveat recorded honestly:** a stale `php -S` listener from a prior session briefly
occupied port 8144 early in setup and served the old slice-5 scratch DB. The
audited server was confirmed (API listing + run-6 probe + R17 markers in the
rendered HTML) before every capture in this folder; the few light captures taken
during the ambiguous window were re-taken afterwards, so the 42 PNGs here all
reflect the confirmed scratch build.

#### R17 fixture (seven states)

Built by `.scratch-uma/frontend-review-fixture.php`, seeded on top of
`php artisan migrate --seed` (24 tables, 10 skills, 2 Umamusume, 4 scenario defs):

1. **Run 1 — populated URA Finale:** 2 turns (energy/mood/fans), 4 skills across Suggested/Acquired/Skipped, 4 calendar slots (fan gate, mandatory goal, maiden gate, past goal) with one completed race (1st) and one Skipped entry.
2. **Run 2 — populated Unity Cup:** 1 logged turn; the team-rank/spirit-burst widgets render per config (`widgets: team_rank, bursts`).
3. **Run 3 — populated Trackblazer:** 1 turn, one priced G1 win plus one free-form win, both left unassigned → the meter's "no period reported" state.
4. **Run 4 — no scenario:** goal panels absent (D-220), baseline strip only.
5. **Run 5 — unpriceable entry (KI-10):** period 2 reported with a 3rd-place win no sourced table prices → "not yet totalled" state.
6. **Run 6 — empty run:** zero turns, first-turn guided-rail state.
7. **Review queue:** one pending `MatchCandidate` ("Vodka", Fuzzy, gametora-characters).

---

### 2. Route table

42 page-captures + 7 body captures (4 API, 2 exports, 1 health page). Every
visited route, its HTTP status, and its screenshot(s):

| Route                                                           | Status                            | Captures (light / dark)                                                                            |
| --------------------------------------------------------------- | --------------------------------- | -------------------------------------------------------------------------------------------------- |
| `/` landing                                                     | 200                               | `landing-default-light` (splash is theme-static; dark adds nothing — see F-1)                      |
| `/training-runs` populated                                      | 200                               | `runs-index-populated-light`, `runs-index-populated-dark`                                          |
| `/training-runs` empty (0 runs, via scratch backup/restore)     | 200                               | `runs-index-empty-light`                                                                           |
| `/training-runs/create`                                         | 200                               | `run-create-form-light`, `run-create-form-dark`                                                    |
| `/training-runs/create` validation error (server-side)          | 200 (redirect back with errors)   | `run-create-form-validation-error-light`                                                           |
| `/training-runs/1` URA Finale                                   | 200                               | `run-detail-ura-finale-light`, `run-detail-ura-finale-dark`                                        |
| `/training-runs/2` Unity Cup                                    | 200                               | `run-detail-unity-cup-light`, `run-detail-unity-cup-dark`                                          |
| `/training-runs/3` Trackblazer, no period reported              | 200                               | `run-detail-trackblazer-light`, `run-detail-trackblazer-dark`                                      |
| `/training-runs/4` no scenario                                  | 200                               | `run-detail-no-scenario-light`, `run-detail-no-scenario-dark`                                      |
| `/training-runs/5` KI-10 unpriceable ("not yet totalled")       | 200                               | `run-detail-unpriceable-light`, `run-detail-unpriceable-dark`                                      |
| `/training-runs/6` empty first-turn                             | 200                               | `run-detail-empty-first-turn-light`, `run-detail-empty-first-turn-dark`                            |
| `/training-runs/1/turns` POST preview (refresh/partial state)   | 200 (POST render)                 | `run-detail-guided-preview-light`, `run-detail-guided-preview-dark`                                |
| `/training-runs/99` 404                                         | 404                               | `error-404-run-light`                                                                              |
| `/training-runs/{1..5}/export/csv`                              | 200                               | `export-csv-run1.txt` (body capture, per spec for non-HTML)                                        |
| `/training-runs/1/export/json`                                  | 200                               | `export-json-run1.txt`                                                                             |
| `/training-runs/1/export/xlsx`                                  | 404 (by design, PRD FR-C-5)       | body embedded in F-9                                                                               |
| `/umamusume` populated (2 records, paginated)                   | 200                               | `catalog-index-populated-light`, `catalog-index-populated-dark`                                    |
| `/umamusume?search=special` filtered                            | 200                               | `catalog-index-search-light`, `catalog-index-search-dark`                                          |
| `/umamusume?search=zzzznone` empty result                       | 200                               | `catalog-index-empty-light`, `catalog-index-empty-dark`                                            |
| `/umamusume?status=GlobalAnnounced`                             | 200                               | `catalog-index-filter-announced-light`, `catalog-index-filter-announced-dark`                      |
| `/umamusume?status=GlobalReleased`                              | 200                               | `catalog-index-filter-global-light`                                                                |
| `/umamusume?page=2` out-of-range page                           | 200                               | `catalog-index-page2-out-of-range-light`, `catalog-index-page2-out-of-range-dark`                  |
| `/umamusume/special-week` detail                                | 200                               | `catalog-detail-populated-light`, `catalog-detail-populated-dark`                                  |
| `/umamusume/nope-404` 404                                       | 404                               | `error-404-catalog-light`                                                                          |
| `/review` populated                                             | 200                               | `review-queue-populated-light`, `review-queue-populated-dark`                                      |
| `/review` empty (via scratch backup/restore)                    | 200                               | `review-queue-empty-light`, `review-queue-empty-dark`                                              |
| `/design-preview` deprecated review surface                     | 200                               | `design-preview-all-light`, `design-preview-all-dark` (forced via its own localStorage, see F-2)   |
| `/up` framework health page (not in `routes/web.php`)           | 200                               | `up-health-page.txt` (body evidence for F-19: off-origin CDN loads at page time)                   |
| `/api/v1/umamusume`                                             | 200                               | `api-umamusume-index.txt`                                                                          |
| `/api/v1/umamusume/special-week`                                | 200                               | `api-umamusume-detail.txt`                                                                         |
| `/api/v1/training-runs`                                         | 200                               | `api-training-runs-index.txt`                                                                      |
| `/api/v1/training-runs/1`                                       | 200                               | `api-training-runs-detail.txt`                                                                     |

**Captures: 42 screenshots, 42 console sidecars, 42 network sidecars, 6 response-body `.txt` files (4 API + CSV + JSON).**

Routes in `routes/web.php` with no GET surface (write-only, not screenable):
`runs.store` (POST), `runs.update` (PUT), `runs.destroy` (DELETE),
`runs.turns.update` (PUT), `runs.turns.destroy` (DELETE), `runs.skills.sync`
(POST), `review.resolve` (POST) — exercised indirectly through the preview and
validation captures; direct GET is a 405, recorded as unreachable in §8.

---

### 3. Per-page analysis

#### 3.1 Landing `/` — `landing-default-light.png`

HTTP 200. **The page is the stock Laravel skeleton splash**: title "Laravel",
"Let's get started", "Deploy now" button linking `https://cloud.laravel.com`,
links to `laravel.com/docs`, `laracasts.com`, and the framework changelog, plus
~38 KB of vendored inline CSS. It ignores the stored theme preference (does not
use `components.layout`; no `data-theme` attribute renders). Off-origin links
exist in markup but generate **zero page-load requests** (sidecars show
all-same-origin network). This is the KI-3 "residual" the KI record itself
flags: the bunny font links are gone, but the page is still not a product
surface. For a local-only trainer tool, "Deploy now" (C-4 scope, PRD §6
non-goals) is the sharpest copy failure in the build.

#### 3.2 Training runs index — `runs-index-populated-{light,dark}.png`, `runs-index-empty-light.png`

HTTP 200. Six fixture runs listed as cards ("Special Week · URA Finale" etc.).
Both themes render correctly from tokens; empty state ("No runs yet / A run
holds the turns you log…") exists and is honest (D-220: absence is stated, not
zeroed). No pagination (FR-6.1 cut) — 6 rows render in one page. Contrast probe
light+dark: 0 WCAG failures; no off-origin requests; the only console line is
the Laravel Boost dev-time logger (tooling artifact, not shipped).

#### 3.3 Create form — `run-create-form-{light,dark}.png`, `run-create-form-validation-error-light.png`

HTTP 200. Three selects (Umamusume, Scenario optional, Status) + notes. Native
`required` blocks empty submit client-side; bypassing it (via `form.submit()`)
lands the server-side error: **"The umamusume id field is required."** — raw
snake_case field name surfaced to the Trainer instead of the form's own label
"Umamusume". Error text renders `text-risk` and is linked into the select's
accessible name. Validation error state exists and re-populates — this is real
coverage, only the message vocabulary drifts (F-5).

#### 3.4 Run detail — the six fixture states

All three themes render the token shell. Shared observations:

- **Header** "Special Week Active · URA Finale" + CSV/JSON export links.
- **Resource strip** (turn/energy/fans + scenario widgets from config) and
  **stat band** with derived grade letters, halved-gains note at 1,200, and the
  `[Provisional]` grade-scale disclosure — all present in both themes.
- **Race calendar** renders 24 slots with per-state labels; the maiden-gated
  slot ("Naruta Kinpa Cup") renders "Run, Naruta Kinpa Cup" in the calendar
  legend for run 1 because the fixture logged a completed entry — state mix
  works.

Per-state:

- **Run 1 URA Finale** (`run-detail-ura-finale-*`): calendar fan gate (15,000
  fans), mandatory goal, completed race cell, Skipped cell, skills in all three
  acquisition groups, 2-row turn table with mood pills (`NORMAL →`, `GREAT ↑`).
- **Run 2 Unity Cup** (`run-detail-unity-cup-*`): Team Rank and Spirit Bursts
  widgets render with "N/A / not yet recorded". **There is no capture path for
  them** — no `team_rank`/`bursts` column exists on `turn_entries` (verified via
  `Schema::getColumnListing`) and the guided form posts no such field, so these
  two widgets are permanently N/A on every run screen (F-6).
- **Run 3 Trackblazer** (`run-detail-trackblazer-*`): "no period reported" meter
  state. Copy bug: **"2 logged results have no period entered against it"** —
  plural subject, singular pronoun (`grade-point-meter.blade.php:105-106` names
  the verb but not the pronoun).
- **Run 4 no scenario** (`run-detail-no-scenario-*`): goal panels correctly
  absent; the h1 says "No scenario set" — but the resource strip's Turn cell
  caption and the guided rail's chip both print **"URA Finale"**, the config
  baseline default, for a run with scenario `null` (F-3, a truthfulness bug by
  the project's own D-220/D-256 standards).
- **Run 5 KI-10 unpriceable** (`run-detail-unpriceable-*`): "not yet totalled: 1
  logged result has no published Grade Point value, so any total here would
  count less than this run earned" renders — the KI-12 R18 copy landed. Note
  the strip still reads "Grade Points N/A / not yet recorded", contradicting the
  panel's precise wording two sections below (F-4).
- **Run 6 empty** (`run-detail-empty-first-turn-*`): first-turn rail state with
  the "This is the run's first turn…" sentence and all-withheld "N/A" values.
  Honest.
- **Preview/partial** (`run-detail-guided-preview-*`): the "Preview this turn"
  submit renders the step-2 preview panel — but the address bar shows
  `/training-runs/1/turns` (the POST endpoint re-renders the view instead of
  redirecting with flash input). **A browser refresh of the preview state
  returns 405.** Every other write flow in the app uses PRG; this one doesn't
  (F-7).

#### 3.5 Catalog index + variants — `catalog-index-*.png`

HTTP 200. Search + status filter + pagination links (2 per page, "1, 2" with a
next arrow). States covered: populated, name-filtered, no-match empty ("No
Umamusume match…" — the same sentence serves zero-result and out-of-range-page),
and `?page=2` **out-of-range renders the generic empty message instead of a
distinct "that page is past the end" state** (F-8). Filter chips show the raw
enum key (`?status=GlobalAnnounced` keeps the query string) but labels render
through `->label()`, so the visible text is compliant. Both themes clean
(0 contrast failures).

#### 3.6 Catalog detail — `catalog-detail-populated-{light,dark}.png`

HTTP 200 for `/umamusume/special-week`. Name, Japanese name, release status,
aliases, and an "All runs" section. Tokai Teio renders with **no runs** (all
fixture runs attach to Special Week) — the "none yet" path is therefore
covered in the same light/dark pair as the populated one (populated half).
All fields render from tokens; no off-origin requests.

#### 3.7 Review queue — `review-queue-{populated,empty}-{light,dark}.png`

HTTP 200. Populated: "Vodka / ヴォーカ / Fuzzy · gametora-characters ·
2026-09-28" with the verdict select, optional id, alias-language select, and
"Resolve Vodka" button (PRG confirmed in `ReviewController::resolve` → redirect
with flash). Empty state exists ("nothing to review"). The resolve buttons carry
accessible names. Both themes clean.

#### 3.8 404s — `error-404-catalog-light.png`, `error-404-run-light.png`

HTTP 404 on both. **Bare Laravel error page**: a small centered "404 / Not
Found" with no app chrome, no navigation, no theme, and no route back into the
product. The console sidecars record the expected resource error. Light-only per
protocol (the page has no theme sensitivity to measure). This is the single
biggest state-coverage gap in the build (F-10).

#### 3.9 Deprecated `design-preview` — `design-preview-all-{light,dark}.png`

HTTP 200. Renders all four scenario compositions from `config/scenarios.php`
with real components. Three findings:

- Its inline script **defaults to dark on empty `localStorage` and ignores the
  server-rendered `Preference` theme entirely** — the page's banner is static
  `data-theme="light"` in markup, overridden pre-paint by the script. It writes
  `uma-theme` to localStorage, the exact client-persistence pattern ADR-0006 /
  D-104 forbids for the app shell (F-2).
- The `?step=` param only relights the step chips; all four scenario sections
  always render, so `?step=outcome` and `?step=skill` are byte-identical page
  states beyond the chip row.
- It is the route the header comment says to delete when the real run screen
  landed; the real run screen now exists (§3.4), so **the whole surface is now
  deletion-debt** — KI-8's 500 is fixed (the page renders), but the route
  outlives its purpose (F-15).

#### 3.10 Exports and API bodies

- **CSV** (`export-csv-run1.txt`): header `turn,speed,stamina,power,guts,wit,sp,condition`
  — the logged `energy`, `fans`, and `mood` columns are **not exported**, and
  `condition` (only writable via the collapsed raw form, always empty on guided
  runs) is (F-9).
- **JSON** (`export-json-run1.txt`): same omission through `TurnEntryResource`
  (exposes only `id, turn, five stats, sp, condition`), with correctly
  `JSON_UNESCAPED_UNICODE`-encoded Japanese names (raw bytes verified —
  `スペシャルウィーク` intact).
- **XLSX**: 404 by design (PRD FR-C-5) — not a defect, recorded for completeness.
- **API** (`api-*.txt`): all four endpoints return 200 with `data` envelopes;
  the runs list carries the same `turns[].condition` shape as JSON export.

---

### 4. Categorized findings

Severity: **N** = needs fix before merge, **O** = observation, **D** = drift from
design record. Counts below.

#### Token/Theme drift

- **F-1 (N)** — `/` landing ignores stored theme (no `data-theme`); hard-coded
  `bg-white`, `dark:bg-[#0a0a0a]`, and ~20 literal hex values live only in
  `welcome.blade.php` (grep-verified: every other view uses tokens). KI-3's
  residual, now measurable: the splash cannot be dark-mode audited at all.
- **F-2 (N)** — `design-preview.blade.php:12-27` pre-paint script reads/writes
  `localStorage.uma-theme` and defaults dark, bypassing the `Preference` table —
  directly contrary to ADR-0006/D-104, and the page's static markup ships
  `data-theme="light"` that the script then overwrites (flash-prone by
  construction for the app's own mechanism).
- **F-4 (O/D)** — run-5 strip "Grade Points — N/A / not yet recorded" vs. panel
  "not yet totalled" one section below: same datum, two vocabularies, on one
  screen.
- **F-15 (O)** — design-preview is the last surface outside the token/layout
  system (its own inline theme control); its own header says to delete it now
  the real run screen exists.

#### State-coverage gaps

- **F-10 (N)** — 404 pages are bare framework output: no branded layout, no
  navigation, no "back to runs" action. Every mistyped slug dead-ends outside
  the product. (Light-only captures; the page is theme-static.)
- **F-9 (N)** — CSV and JSON exports ship only `turn, speed, stamina, power,
  guts, wit, sp, condition`. `energy`, `fans`, and `mood` are captured by the
  guided form and shown on the screen but **absent from both exports**, while
  `condition` — reachable only through the collapsed raw-entry form (D-53) and
  empty on every guided turn — is present and always blank. The export shape
  and the logged/displayed data disagree (TrainingRunController::export +
  TurnEntryResource).
- **F-8 (O)** — `?page=N` beyond the last page reuses the zero-result empty
  sentence ("No Umamusume match") instead of a distinct past-the-end state.
- **F-7 (N)** — guided "Preview this turn" renders the POST endpoint's URL into
  the address bar (no PRG): refresh → **405 Method Not Allowed**. The only
  write flow in the app that is not refresh-safe.
- **F-6 (O)** — Unity Cup's Team Rank / Spirit Bursts strip widgets have no
  input path and no column: their "populated" state is unreachable on the run
  screen (only the deprecated preview fakes values for them).
- **F-11 (O)** — no `500`/error page state exists for anything but framework
  exceptions; no custom `errors/` views ship (`resources/views/errors/` absent).
- **F-19 (N)** — the framework's `/up` health page (Laravel's built-in, not in
  `routes/web.php`) is reachable at 127.0.0.1:8144/up and loads **remote assets
  at page time**: `fonts.bunny.net` (Figtree stylesheet) and
  `cdn.jsdelivr.net/npm/@tailwindcss/browser@4`. For a local-only tool (PRD
  NFR-1 / CONSTRAINTS C-4) that is a live off-origin dependency on an
  indexable route — the exact pattern KI-3 closed for `welcome.blade.php`
  still lives here. No screenshot per protocol (not a product route); body
  evidence is in the §2 route table row.

#### Accessibility

- **F-12 (O)** — the run-detail skills editor wraps three controls in one
  `<label>` (`runs/show.blade.php:395-407`), so only the first gets an
  implicit name: the "Skill" combobox is named, the adjacent acquisition-status
  `<select>` (Suggested/Acquired/Skipped) is **unnamed** in the accessibility
  tree, and the turn `<input type=number>` relies on `placeholder="Turn"`
  alone — which vanishes once the field holds a value. A screen-reader user
  hears one named control, one unnamed combobox, and an unlabeled number.
  Contrast: the create form labels every field, so the gap is local to this
  block.
- **F-13 (O)** — the hidden radio inputs (`class="peer size-px opacity-0"`) are
  correctly named in the a11y tree and carry `peer-focus-visible` rings
  (design-record compliant, verified in `guided-step.blade.php:126-131`); no
  unnamed interactive controls found anywhere (`/review` "Resolve" button and
  catalog search selects all carry accessible names — verified per-page).
- Positive: skip-link present on every layout page; `aria-label`ed regions on
  the run screen; energy bars expose "Energy 74 of 100" accessible names.

#### Copy/Lore

- **F-3 (N)** — run 4 (scenario `null`): h1 "No scenario set" contradicted by
  the resource strip caption "URA Finale" and the guided-rail chip "URA Finale"
  (`resource-strip.blade.php:62`, fed from `$scenario ?? 'ura_finale'` default).
  A claim about the run the data does not support (D-256-adjacent).
- **F-5 (O)** — validation error "The umamusume id field is required." leaks the
  DB-ish field name where the form's own label says "Umamusume".
- **F-14 (O)** — design-preview calendar cells double-announce: img alt "Feb
  Late: **Next, Next**" — the state name and label are the same string
  (routes/web.php preview data uses `'label' => 'Next'` for a state the legend
  also names; preview-surface only).
- **F-18 (O)** — grade-point-meter copy, plural case: "**2 logged results have
  no period entered against it**" — the verb agrees (`results have`) but the
  pronoun stays singular (`against it`); `grade-point-meter.blade.php:104-106`.
  Reproduced live on run 3 (light+dark captures).
- Lore grep (banned equine terms, `🏇`) over `resources/views/**`: **zero <!-- lore-ignore-line class=1 cite=GATE-REGISTRY.md#C-4 -->
  matches**. All fixture seed strings and audit copy here were screened.
  ("Homestretch Haste", "Unstoppable" etc. are verbatim source skill names —
  allowed on the data path, gated at display; no authored copy uses equine <!-- lore-ignore-line class=1 cite=GATE-REGISTRY.md#C-4 -->
  framing.)
- KI-7 (em dashes in rendered Blade copy): **not reproduced** — no em dash
  renders in any captured page.

#### Layout (1280px)

- No horizontal overflow, clipped controls, or wrapped-header regressions found
  at 1280×800 in either theme across all 42 captures (full-page review of the
  DOM snapshots taken per capture + visual scan of the PNGs). The three-column
  run layout (strip rail / log / actions) and the 12×2 calendar grid are the
  widest surfaces and both fit with room.
- **F-16 (O)** — the run-detail page is extremely long (the fixture run 1
  captures at ~8× the viewport height); the sticky strip (`lg:sticky`) works at
  1280, but nothing summarizes; not a regression, recorded as drift risk.

#### Contrast (N1-class, D-288 gate)

Programmatic WCAG AA probe (text vs. computed stacked background, 4.5:1 /
3:1-large) on every theme-sensitive capture:

- **0 failures** in light and dark on: runs index, run 1–6, guided preview,
  create form (+error), catalog index (+search/empty/filter variants), catalog
  detail, review queue (both states). The `--color-ring` token is separate from
  the up/down tokens precisely to keep 1.4.11 safe (app.css:73-94); KI-9's
  selection-gold fix is verified holding (no 1.59:1 pairs found).
- **F-17 (N1-pass)** — the probe's floor cases (faint ink on sunken, `green-tint`
  band word) measured 6.4:1+ in both themes, matching slice-5 §3's recorded
  numbers. **No N1-class gate failures found in this build.**

### Count: 19 numbered observations (F-1…F-19) — 7 need-fix (N: F-1, F-2, F-3, F-7, F-9, F-10, F-19), 11 minor/observation (O), and 1 N1-class gate result recorded as a pass (F-17: zero contrast failures, so no D-288 gate finding was raised)

---

### 5. Cross-reference: known issues vs. visibility in this build

| KI      | Subject                                                          | Visible here?                                                                                                                                                                                                                                                                                   |
| ------- | ---------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| KI-1    | `x-layout` Vite entry missing — RESOLVED                         | Not visible. Every page loads `/build/assets/app-*.{css,js}` 200.                                                                                                                                                                                                                               |
| KI-2    | Catalog cache kills models — RESOLVED                            | Not visible. Catalog index/detail/search all 200, no 500s.                                                                                                                                                                                                                                      |
| KI-3    | welcome breaks offline — RESOLVED (font links), residual open    | **Partially visible.** Zero off-origin *requests* (fixed); but the splash itself persists (F-1, F-15-adjacent) — the "residual, not fixed" note is confirmed live.                                                                                                                              |
| KI-4    | `make lore` blind to untracked — RESOLVED                        | n/a to rendered UI.                                                                                                                                                                                                                                                                             |
| KI-5    | fabricated skill name — FIXED                                    | Not visible. Banned-pattern grep clean (§4 Copy/Lore).                                                                                                                                                                                                                                          |
| KI-6    | shipped font — CLOSED as decision                                | Not visible. System stack renders per DESIGN.md §2.2.                                                                                                                                                                                                                                           |
| KI-7    | em dashes in Blade copy — RESOLVED                               | Not visible. No em dash renders in any capture.                                                                                                                                                                                                                                                 |
| KI-8    | design-preview 500s on grade badge — RESOLVED                    | Not visible. `/design-preview` returns 200 in both themes (grade letters render).                                                                                                                                                                                                               |
| KI-9    | selection gold 1.59:1 light — RESOLVED                           | Not visible. Contrast probe found no sub-3:1 selection pairs (F-17).                                                                                                                                                                                                                            |
| KI-10   | Grade Points unpriceable — schema half CLOSED, ratio half OPEN   | **Visible as designed.** Run 5 renders the honest "not yet totalled" withholding (the OPEN half is exactly what F-4/F-16 observe at the copy boundary). Fixture reproduces it.                                                                                                                  |
| KI-11   | seeder stub + green-tint debt — BOTH CLOSED                      | Not visible. Slots seeded via fixture; green-tint band word measures (F-17).                                                                                                                                                                                                                    |
| KI-12   | meter says "nothing entered" — RESOLVED (R18)                    | **Visible as fixed.** Run 5 renders the three-state copy; run 3 renders the "no period reported" state. The plural/pronoun bug (F-18) is new, filed in §4.                                                                                                                                      |
| KI-13   | models exist on no ref — RESOLVED                                | Not visible. Everything resolves on this branch.                                                                                                                                                                                                                                                |
| KI-14   | rail declared fake radios — RESOLVED                             | Not visible. `role=radio` semantics present with focus rings (F-13 positive).                                                                                                                                                                                                                   |
| KI-15   | GP track selection unsourced — OPEN (×2 duplicate sections)      | **Visible as designed.** Run 3/5 meters show the `standard` track (60/300/300) with the record's own caveat rendered beside it. Also: `KNOWN-ISSUES.md` carries KI-15 **twice** (lines 645 and 678, byte-near-identical) — a doc-integrity observation for the file's owner, not a UI defect.   |

**No previously-resolved KI regressed in this build.** KI-10 and KI-15 remain
open and both are *correctly* visible (they are deliberate withholdings).

---

### 6. Unreachable routes

| Route                                        | Reason                                                                                                                                                  |
| -------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `runs.update` PUT `/training-runs/{run}`     | Write-only verb — GET is 405 by design; exercised through the scenario-change form (no visible defect).                                                 |
| `runs.destroy` DELETE                        | Write-only; its UI trigger (the "Delete run" button) was deliberately **not** activated (destructive; documentation-only audit).                        |
| `runs.turns.update` / `runs.turns.destroy`   | Write-only verbs on turn rows; no screenable GET surface.                                                                                               |
| `runs.skills.sync` POST                      | Write-only.                                                                                                                                             |
| `review.resolve` POST                        | Write-only; deliberately not activated (mutates the candidate row; PRG confirmed from code + the success path is covered by the empty-queue capture).   |
| `runs.export` `xlsx`                         | 404 is the implementation (PRD FR-C-5 cut Excel): not an error, a designed absence.                                                                     |

---

### 7. Tree integrity (audit changed no source)

- `git check-ignore -v .scratch-uma/` → `.gitignore:88:/.scratch-uma/ .scratch-uma/`
- The audit commit (`docs(frontend-review): screenshot audit…`) contains **only**
  `docs/frontend-review/**`: `git show --name-only HEAD` lists zero files under
  `app/`, `resources/`, `config/`, `tests/`, `routes/`, or `database/`.
- The branch base moved mid-audit: the concurrent Slice-7/Slice-8 line committed
  schema, model, and view changes (`team-rank-gauge`, `race-panel`, period
  columns, ~1,207 insertions across 17 source files) onto this branch's history
  between `7d4b8cf` and the audit commit. Those commits are **not** the audit's;
  they are recorded here because every PNG post-dates the view code they landed
  (capture times 21:22–22:58 local vs. final view mtime 20:37 local, §1 caveat).
- The shared `database/database.sqlite` was never opened for write by this
  audit: all migrate/seed/tinker/screenshot commands ran with `DB_DATABASE`
  pointed at the gitignored `.scratch-uma/frontend-review.sqlite` (probe
  `php .scratch-uma/list-runs.php` without the env var shows the shared DB
  still holds its own single run, untouched).

---

## DECISIONS-NEEDED.md (frontend-review/2026-09-28)

Decisions needed — frontend audit 2026-09-28

Raised against `fix/frontend-audit-2026-09-28` @ `7d4b8cf` base. These are not fixes:
each needs either a schema change (outside this slice's fence) or a product ruling.
Nothing here was acted on.

---

### F-6 — Unity Cup's Team Rank and Spirit Bursts widgets are permanently N/A

**Evidence.** `config/scenarios.php:92` gives `unity_cup` the widget list
`['turn', 'energy', 'fans', 'team_rank', 'spirit_bursts']`. `turn_entries` has no
`team_rank` and no bursts column (`database/migrations/2026_09_26_162818_create_turn_entries_table.php:14-25`,
plus the ADR-0003 additions of energy/mood/fans). Neither `StoreTurnEntryRequest` nor
`guided-step.blade.php` accepts or posts such a field. `TrainingRun::stripValues()`
therefore can only ever return null for both keys, and the strip renders its
documented withheld state — `N/A / not yet recorded` — on every Unity Cup run, forever.

The only surface that ever showed real values was `/design-preview`, which
fabricated them; that surface is now deleted (F-2/F-15), so nothing fakes it any more.

**Why this is not a frontend fix.** Making the widget populatable means a column and
an input path. The slice fence forbids migrations, and the repo's own rule
(`audit` §17 equivalent, PRD FR-C-2) puts bounds and field ownership in one place.

**Path A — add the data.** Two nullable columns on `turn_entries` (team rank tier,
burst count), guided-form fields, validation, export parity (the F-9 lesson says the
screen, the log and the export must agree or the next audit files it again), and a
source for what a rank tier actually is on Global. Cost: one schema slice plus the
fixture. Benefit: two honest widgets. Risk: `docs/UMAMUSUME_REFERENCE.md` must
actually document both as trainer-readable values, or we are storing numbers the game
does not show.

**Path B — remove the widgets.** Drop `team_rank` and `spirit_bursts` from
`config/scenarios.php:92`. Cost: one config line, and the Unity Cup strip gets shorter
than the design sketch. Benefit: the screen stops reserving space for something it can
never report, which is its own kind of dishonesty under D-220.

**Recommendation: B, now; A only if a sourced definition of both values exists.**
Reason: the withheld state is already honest (it says "not yet recorded"), so the
argument for A is capability, not truthfulness — and capability without a citable
source is exactly what PRD §6 non-goals were written to stop.

---

### F-19 — the framework health route loads off-origin assets

**Evidence.** `GET /up` returns 200 and its HTML references `fonts.bunny.net`
(Figtree stylesheet) and `cdn.jsdelivr.net/npm/@tailwindcss/browser@4`, both fetched at
page time. The route is registered by the framework's health check, not by
`routes/web.php`, which contains 16 app routes and no `/up`.

**Why this is not a frontend fix.** Editing it means overriding vendor routing, which
the fence puts out of scope, and the audit itself marks it "needs a decision, not a fix".

**Path A — accept and document.** It is a local-only tool (PRD NFR-1) and `/up` is not
a Trainer surface; nothing links to it. Record the acceptance in `KNOWN-ISSUES.md` next
to KI-3, which closed the identical pattern in `welcome.blade.php`, so the next reader
does not re-file it. Cost: a live off-origin dependency remains on an indexable route.

**Path B — app-owned handler.** Register our own `/up` before the framework's and
return a token-rendered page or a plain JSON body. Cost: we now own a health endpoint,
and `php artisan health:list`-style tooling may expect the vendor page. Benefit: no
off-origin request at all, consistent with F-1's outcome for `/`.

**Path C — disable outside production.** Bind the route to local only. Cost: the health
check disappears from the environment where it is most useful (a trainer's own machine).

**Recommendation: B.** Reason: F-1 removed the same class of violation from `/` by
putting the page inside the product's own shell, and a one-route override is the
smallest change that keeps the promise "no page this tool serves reaches the internet".
If B is refused, A is acceptable only as a written acceptance, not silence.

---

### One more the audit did not file

`/up` was never in the audit's route table for capture, yet the audit's §2 lists it as a
body capture for F-19 only. Fine. But note that `welcome.blade.php` was the sole reason
`resources/views/welcome.blade.php` carried remote links, and after F-1 the repo has no
off-origin reference in any app view — a state worth a line in KI-3's record so the
residual can be closed rather than left "partially visible". Not acted on.

---

## RESOLUTIONS.md (frontend-review/2026-09-28)

Resolutions — frontend audit 2026-09-28

Branch `fix/frontend-audit-2026-09-28`, based on `master` @ `7d4b8cf` (the tip when
this slice opened; master has since moved to `ec0ee2f`, 16 commits ahead — see
§Base below). No push, no merge.

Audit README is treated as the historical record and was not edited. This file sits
beside it.

---

### Read this before chasing F-3's citation

The audit's F-3 entry cites **`resource-strip.blade.php:62`** and a
**`$scenario ?? 'ura_finale'`** default. **That expression does not exist** — not on `master`
(`7d4b8cf`) and not on `docs/frontend-review`. Verified with
`git grep -n "ura_finale" -- app resources config routes` on both refs: the only hits are
`config/scenarios.php:48` (`'baseline' => 'ura_finale'`), the scenario definition below it, and
the deleted design-preview closure. Neither component names a scenario literal; both throw on an
unknown key, deliberately.

The real mechanism is **`TrainingRun::scenarioKey()`, `app/Models/TrainingRun.php:203`**:
`return $this->scenario ?? (string) config('scenarios.baseline');`. The run screen passes that key
into the strip at `runs/show.blade.php:52`, and the strip's Turn caption is `$def['label']` at
`resource-strip.blade.php:59`, so the baseline's *name* reaches the screen through the composition
fallback. **The finding is genuine and reproduced; the citation is not.** Anyone editing the cited
line would have been editing a comment.

---

| ID     | Sev   | Status                                          | Commit                                        | Verification                                                                                                                                                                                                                                                |
| ------ | ----- | ----------------------------------------------- | --------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| F-1    | N     | **fixed**                                       | `7034c19`                                     | `/` returns 200 with `data-theme="dark"` under a dark preference; response contains zero six-digit hex (was 26 in-file / 42 incl. shorthand), no `cloud.laravel.com`, no `fonts.bunny.net`. Test: *lands inside the product and honors the stored theme*.   |
| F-2    | N     | **fixed by deletion**                           | `93cb39b` + `f042d17`                         | `/design-preview` now 404. Route + view removed; orphaned `View` import removed. Closes F-14 and F-15 with it.                                                                                                                                              |
| F-3    | N     | **fixed**                                       | `bc7ceb6`                                     | Run 4 strip caption reads "no scenario set"; rail label chip gone. Captured light + dark. Test asserts both directions so the fix cannot over-correct.                                                                                                      |
| F-4    | O     | deferred                                        | —                                             | Vocabulary split (strip "N/A" vs panel "not yet totalled") is real but lives in copy the audit graded O; touching it means choosing one vocabulary, which is a design-record call, not a one-liner in a file I was already in.                              |
| F-5    | O     | deferred                                        | —                                             | Validation message "The umamusume id field is required." needs an attribute label in `StoreTrainingRunRequest`, which is outside the frontend fence for this slice.                                                                                         |
| F-6    | O     | **decision requested**                          | —                                             | No column, no input path, permanently N/A. Needs schema work or widget removal. See `DECISIONS-NEEDED.md`.                                                                                                                                                  |
| F-7    | N     | **fixed; frozen tests updated in two phases**   | `83db98c` code, `ad17d99` + `792b5cb` tests   | POST `stage=preview` now `302` + `Location: /training-runs/1`; target answers GET 200, so the 405 is unreachable. All 8 frozen tests green; suite back to base parity at 6. See §Frozen below.                                                              |
| F-8    | O     | deferred                                        | —                                             | Out-of-range page reuses the zero-result sentence. Audit marked O; a distinct state needs new copy + a branch in `catalog/index.blade.php`, i.e. a new file opened to chase an observation, which the fence forbids here.                                   |
| F-9    | N     | **fixed**                                       | `3c8c830`                                     | CSV header now `…,condition,energy,mood,fans`; row 1 = `1,480,300,355,210,95,240,,88,NORMAL,9000`. JSON turn keys carry the same three. Original 8 positions unchanged. Bodies in `resolutions/`.                                                           |
| F-10   | N     | **fixed**                                       | `9419665`                                     | `resources/views/errors/404.blade.php` renders through `x-layout`: nav, skip link, tokens, three routes back. Both 404 routes captured.                                                                                                                     |
| F-11   | O     | not done                                        | —                                             | `errors/500.blade.php` optional; skipped because a 500 page cannot be exercised on this fixture without injecting a fault, and inventing one to screenshot it would be a fake verification.                                                                 |
| F-12   | O     | deferred                                        | —                                             | Three controls in one `<label>` on the skills editor (`runs/show.blade.php`). Real a11y gap; fixing it means restructuring that block, which is the "do not refactor while fixing" line.                                                                    |
| F-13   | —     | no action                                       | —                                             | Audit recorded this as a pass.                                                                                                                                                                                                                              |
| F-14   | O     | **fixed with F-2**                              | `93cb39b`                                     | The double-announced "Next, Next" existed only in the deleted route's literal sample data.                                                                                                                                                                  |
| F-15   | O     | **fixed with F-2**                              | `93cb39b`                                     | design-preview was the last surface outside the token/layout system; it is gone.                                                                                                                                                                            |
| F-16   | O     | deferred                                        | —                                             | Run page length is a layout/design question, not a fix.                                                                                                                                                                                                     |
| F-17   | —     | no action                                       | —                                             | Audit recorded zero contrast failures.                                                                                                                                                                                                                      |
| F-18   | O     | deferred                                        | —                                             | Plural/pronoun bug in `grade-point-meter.blade.php:104-106`. One-line copy fix, but the file is not one I otherwise touched, so the fence says leave it for the follow-up slice.                                                                            |
| F-19   | N     | **decision requested**                          | —                                             | `/up` still loads `fonts.bunny.net` + `cdn.jsdelivr.net` (re-confirmed on this branch). Vendor-registered route. See `DECISIONS-NEEDED.md`.                                                                                                                 |

**Counts: 6 fixed (F-1, F-2, F-3, F-9, F-10, plus F-14/F-15 absorbed by F-2), 8 deferred
(F-4, F-5, F-8, F-11, F-12, F-16, F-18), 2 decision-requested (F-6, F-19), 2 no-action
(F-13, F-17).**

### Frozen-test collision — ruled, and how it was cleared (F-7)

**Resolved.** The owner ruled that F-7 ships and authorized the unfreeze. All 8 tests are now
green and the suite is back to base parity: **6 failed at `7d4b8cf`, 6 failed at `792b5cb`**, the
same six `SkillAutomationTest` cases, with `passed` rising 336 → 342 — exactly the six rewrites,
and no test removed. The original description of the collision is kept below, because it is the
reason the freeze needed a ruling at all.

8 tests in three frozen Phase 3A/3B files now fail, all on one line shape:

- `GuidedFirstTurnTest` (3), `GuidedTurnOnRunViewTest` (3), `GuidedTurnStagesTest` (2)
- failure text: `Expected response status code [200] but received 302`

They assert `post(...)->assertOk()` on the preview submit — i.e. they pin the exact
behavior F-7 files as the defect. The invariant each test exists to protect ("a
preview writes no rows") still holds: the assertion on the line below each failure
passes. PRG and a 200-on-POST are mutually exclusive, so there is no version of the
fix that satisfies both.

Not edited, per the frozen rule. Minimal change when you authorize it:
`->assertOk()` becomes `->assertRedirect(route('runs.show', $run))` followed by
`->followRedirect()` where the test then reads content. Suite state: **base 6 failed
(all `SkillAutomationTest`, pre-existing and unrelated) → branch 14 failed**, so
F-7 accounts for exactly the 8-test delta and nothing else regressed.

### Corrections to the audit's own citations

1. **F-3's cited line does not exist.** The audit names
   `resource-strip.blade.php:62` and a `$scenario ?? 'ura_finale'` default. No such
   expression is in the tree on `master` or on `docs/frontend-review`; both
   components refuse unnamed scenario defaults and throw on an unknown key. The real
   default is `TrainingRun::scenarioKey()` at `app/Models/TrainingRun.php:203`
   falling back to `config('scenarios.baseline')`. The finding is genuine — I
   reproduced it — but fixing the cited line would have edited a comment.
2. **F-3's "no scenario" claim needs a carve-out.** The scenario picker on the same
   page legitimately lists "URA Finale" as an option, including for a run that has
   none. A first cut of the test asserted the whole page must not contain the string
   and failed on that `<option>`. The test now strips `<option>` blocks before
   asserting, because the picker is a control, not a claim.
3. **The audit's base moved under it.** §7 records Slice-7/8 commits landing on the
   audit branch between `7d4b8cf` and the audit commit. Consequence for this slice:
   `7d4b8cf` contains **no** `docs/frontend-review/2026-09-28/` files (0 entries),
   while current master contains all 134. So this branch's `resolutions/` folder sits
   beside no README until the branches meet.

### Base

Branch point `7d4b8cf`. Master has moved to `ec0ee2f` (16 commits) during this slice,
including the audit files themselves. Merging therefore needs a rebase, and
`resources/views/runs/show.blade.php`, `guided-step.blade.php`,
`resource-strip.blade.php` and `routes/web.php` are the likely conflict points, since
16 commits of UI work landed on the same files.

### Captures in `resolutions/`

- `run-detail-no-scenario-light.png`, `run-detail-no-scenario-dark.png` — F-3, 1280×800
- `error-404-run-light.png`, `error-404-catalog-light.png` — F-10. **These two files are
  byte-identical on purpose**, not a copy mistake: both routes now resolve to the one
  `errors/404.blade.php` template, so the pair *is* the evidence that a mistyped run id and a
  mistyped slug render the same product chrome. They are kept as two files because the audit
  captured them as two states, and collapsing them would lose that the fix covers both.
- `export-csv-run1.txt`, `export-json-run1.txt` — F-9 bodies
- `run-detail-guided-preview-prg.txt` — F-7 HTTP evidence, including an honest note
  that the bubble recomputation is proven by the automated test, not by curl

The audit's own PNGs and sidecars were not opened for writing; the two
`error-404-*-light.png` names are reused only inside `resolutions/`.

---

### Follow-up: the F-7 unfreeze, in two phases — first attempt stopped at 2 of 8

The owner ruled F-7 ships and authorized a narrow unfreeze of three frozen files, with each edit
being `->assertOk()` → `->assertRedirect(route('runs.show', $run))->followRedirect()` and nothing
else. Two of the eight failing tests were converted exactly that way and now pass; the other six
cannot be, and the reason is a framework fact worth recording before anyone retries it.

**`Illuminate\Testing\TestResponse` has no `followRedirect()` on Laravel 13.32.** The class defines
`assertRedirect()` at `vendor/laravel/framework/src/Illuminate/Testing/TestResponse.php:206` and no
`followRedirect`. `TestResponse` proxies unknown methods to the underlying response through
`ForwardsCalls`, so the prescribed chain does not fail an assertion — it dies earlier with
`BadMethodCallException: Call to undefined method Illuminate\Http\RedirectResponse::followRedirect()`.
Verified by applying the edit verbatim and reading the exception.

That splits the eight into two groups:

- **2 fixed.** `GuidedTurnStagesTest` *holds the row count at zero after stage one…* and *does not
  let a repeated preview accumulate rows*. Neither reads a response body, so the status assertion
  alone carries the test, and `->assertRedirect(route('runs.show', $run))` without the
  `followRedirect()` half is sufficient. The file is now 4/4 green.
- **6 not reachable by an assertion-line edit.** `GuidedFirstTurnTest` (3) and
  `GuidedTurnOnRunViewTest` (3) each read the previewed **HTML** off the POST response, via
  `->getContent()` on the post chain or off a stored `$response`. After PRG that body is a redirect
  with no page in it, so the content assertions fail regardless of what the status line says. Four
  of the six have no `assertOk()` on the POST at all — they are `post(...)->getContent()`
  statements, which the unfreeze did not authorize touching. The fifth, *previews a turn without
  writing it*, has `assertOk()` on line 145 but reads `$response->getContent()` on line 151 from
  the same stored response, so fixing it means editing a second line. The sixth needs the same.

Repairing those six is a rewrite, not a swap: each has to become an explicit
`post(...)->assertRedirect(...)` followed by a separate request that carries the flashed input
(`withSession(['_old_input' => $payload])`, the shape used in `FrontendAuditFixesTest.php`), because
Laravel flashes input for exactly one request and a bare follow-up `get()` sees nothing. That is
outside the granted fence, so it was not done. **Suite state: 12 failed at branch tip — the 6
pre-existing `SkillAutomationTest` failures (now KI-17) plus these 6.** Base at `7d4b8cf` is 6.

The stale `design-preview` claim in the `GuidedTurnOnRunViewTest` header comment was corrected in
the same commit, comment-only, as authorized.

#### Phase two (`792b5cb`), and the idiom it left behind

A reviewer reading only `ad17d99` would think the job was half done, which is why this is a
separate commit and a separate paragraph. `ad17d99` cleared the 2 tests that read no response body;
`792b5cb` cleared the remaining 6 that read the previewed HTML off the POST.

The carry those 6 need is the reusable part, so it is written down rather than left to be
rediscovered:

```php
$payload = previewPayload($run);

$this->post('/training-runs/'.$run->id.'/turns', $payload)
    ->assertRedirect(route('runs.show', $run));

$html = $this->withSession(['_old_input' => $payload + ['previewed' => '1']])
    ->get(route('runs.show', $run))
    ->getContent();
```text

Two things that snippet encodes, both easy to get wrong. `followRedirect()` does not exist on
`TestResponse` in Laravel 13.32, so the redirect target is reached by an explicit second request.
And the flashed bag must be `$payload + ['previewed' => '1']`, not `$payload`: the controller
flashes `$validated + ['previewed' => '1']` and `showData()` gates the whole preview state on
`$old['previewed'] === '1'`, so flashing the bare payload drops the rail back to step one and the
assertions on `name="previewed"` and `Step 2 of` fail for a reason unrelated to what they test.
Any future PRG test against this controller needs both halves.

---

## CONSTRAINTS.md (root quality bar, C-1 to C-9)

## CONSTRAINTS.md

The quality bar for this repository, written as a contract. Agents: read this before writing code; never weaken a threshold to make a change pass. Relaxation requires the human owner (AGENTS.md escalation path 4).

### Dimensions and thresholds

| #     | Dimension                    | Threshold                                                                                                 | Command                                                                                              |
| ----- | ---------------------------- | --------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------- |
| C-1   | Tests                        | All Pest tests pass; every behavior change ships a test                                                   | `php artisan test --compact`                                                                         |
| C-2   | Static analysis              | PHPStan level 6 (Larastan), zero errors                                                                   | `vendor/bin/phpstan analyse --no-progress`                                                           |
| C-3   | Formatting                   | Pint clean                                                                                                | `vendor/bin/pint --dirty --format agent` then `vendor/bin/pint --test --format agent`                |
| C-4   | Lore                         | Zero unexplained hits of banned patterns (below) in tracked text                                          | `composer lore` (and `composer lore-code`) — not `make lore`, which cannot run on this host (KI-4)   |
| C-5   | Migrations                   | Fresh migrate + seed succeeds; every migration has a working `down()`                                     | `php artisan migrate:fresh --seed`                                                                   |
| C-6   | Performance (local budget)   | Catalog index under 200 ms at ~1,000 Umamusume / ~2,000 skills (NFR-3)                                    | manual benchmark per PRD §8; re-check when schema or query shape changes                             |
| C-7   | UI states                    | Every data view renders empty, loading/refresh, and error states (§7, antislop R-27)                      | review checklist                                                                                     |
| C-8   | Dependencies                 | No new package without human approval; `composer audit` and `npm audit` with no reachable critical/high   | `composer audit`; `npm audit --omit=dev`                                                             |
| C-9   | Frontend types               | `resources/js/**/*.ts` compiles clean under `tsconfig.json`'s `strict` mode, which declares `noEmit`      | `npm run typecheck` (also runs inside `composer test`)                                               |

> C-7 loading-state scope is interpreted by ADR-0007 (`docs/adr/0007-c7-loading-state-scope-for-server-rendered-views.md`): initial server-rendered navigation may rely on browser-native loading; user-initiated async operations require explicit loading states. Empty/error/data states remain mandatory. Gate tooling and G-number registration live in `docs/research-scratch/GOVERNANCE.md` §"GATE-REGISTRY.md".

### Floor (never, in any change)

- No new suppressions: `@phpstan-ignore`, `@phpstan-` escapes, `eslint-disable`, `@ts-ignore`.
- No stub bodies: `throw new \Exception('not implemented')`, empty `catch {}`, TODO placeholders.
- No deleted or skipped tests without the human's approval and a reason in the commit message.
- No engine write to rows with `is_manual = true` (PRD FR-B-4).
- No fact stored without provenance (AGENTS.md Data Engineer rules).
- No fetch URL outside `config('uma.sources')` allowlist (SSRF posture, ARCHITECTURE §8).
- No business logic in controllers; no inline `$request->validate()` (CLAUDE.md Banned Patterns).

### Lore banned patterns (C-4)

Grep, case-insensitive: `horse`, `horses`, `sire`, `dam`, `mare`, `foal`, `🏇`, plus animal framing of characters.

- Context check required before calling a hit a violation: "dam" inside "damaged", "stable" as an adjective, etc. The grep proposes; the Lore Guardian decides (AGENTS.md).
- `docs/PRE-MORTEM.md` quotes legacy violations as elimination evidence, once each; those lines are exempt. No new violation may enter anywhere else.
- `composer lore` excludes `vendor/`, `node_modules/`, and the pre-mortem evidence file.
- **Identifier and mechanics exemption.** The list governs **player-facing copy and character framing**, and nothing else. It does not apply to: dataset and export keys (`intelligence` for Wit, `friend` for Pal, `scenarios.json` field names); localisation-mapping strings quoted as client evidence ("Intelligence Limit Up", "Runner's Tricks ◎"); or a **distinct mechanic** that merely shares a word — "Bad Conditions", the "condition correction" in the failure formula, and a "condition" item effect are mechanics, not Mood synonyms. Renaming an identifier to satisfy the list breaks the ingest join; that is a worse failure than an ugly key.
- **Verbatim names are a third category**, between our own copy and a stray noun. A quoted skill, race, card or title string that contains a banned term stays as **source data** and is barred from **promotion into UI copy** — see the Air Messiah ruling in `docs/UMAMUSUME_REFERENCE.md` §2.7 and the `[Global]` "Cleat" case there. Editing the data to satisfy the style rule corrupts the corpus; the guard goes on the display path.
- **The grep is a floor, not the rule.** `composer lore` sweeps 23 banned words over three greps and `lore-code` adds the Global client terminology, but only inside app directories. Neither reaches `muzzle`, `rider`, `bit`, `flock`, `pack`, or the framing phrases ("your horse", "the animal"), and neither reads intent, which is why `dam` in "damaged" and `stable` in "stable growth" arrive as hits and are ruled on by hand. The wider dictionary lives in `docs/research-scratch/DESIGN-CORPUS.md` section "CONSTRAINTS.md" §3.1 (D-20); a human read is the control for anything the greps cannot see.

### Verification sequence before any hand-off

1. `php artisan migrate:fresh --seed`
2. `php artisan test --compact` (narrow first, full at hand-off)
3. `vendor/bin/pint --dirty --format agent`
4. `vendor/bin/phpstan analyse --no-progress`
5. `make lore` (`composer lore` and `composer lore-code` run the same greps on a host without GNU make; `tools/lore.php` is the runner and `LoreGateParityTest` keeps its list equal to the Makefile's)
