# User Flow: Create Run and Legacy Select

**Type:** 5 (user flow)
**Date:** 2026-09-28 (pre-dev agent; evidence from HEAD `9134206` plus noted
uncommitted work)
**Governing rules:** PRD FR-C / US-3 / US-4, `CONSTRAINTS.md` C-7 as scoped by
`docs/adr/0007`, ADR-0002 (bounds), ADR-0003 (schema), research constraints
D-260..D-268, root `DESIGN.md` §6 terminology.

## 1. Purpose

Pre-run flow: a Trainer creates a training run for one Umamusume, optionally names
its scenario and two inheritance parents (Legacy Select), then starts logging
turns and skill states. This is the product's only ancestor-selection surface and
it is strictly pre-run (D-260).

## 2. Current implementation status (evidence, not intent)

| Piece | State | Evidence |
|---|---|---|
| Routes `runs.create/store/show/update/destroy`, `runs.turns.*`, `runs.skills.sync`, `runs.export` | Live | `routes/web.php:15-26` (HEAD) |
| Run fields: umamusume_id, scenario (free text), status, notes | Live | `StoreTrainingRunRequest` HEAD lines 29-34 |
| Inheritance parents in validation | Accepted by the Form Request | `inheritance_parent_a_id` / `_b_id`, nullable exists rules (HEAD) |
| Legacy Select UI (picker during create) | **Not implemented** | `resources/views/runs/create.blade.php` at HEAD has no parent fields |
| Turn logging with stats 0..1200, SP, condition, MoodTier | Live | `StoreTurnEntryRequest:40-53`, `app/Enums/MoodTier.php` |
| Skill states Suggested/Acquired/Skipped | Live | `runs.show` sync form, `TrainingRun::setSkillStatus` |
| Scenario config driving panels/widgets | In flight (uncommitted) | `config/scenarios.php` tracked at HEAD; component work dirty in tree |
| `ScenarioSlot`, richer race/lineage tables | In flight (untracked models/migrations) | working tree only; not committed |

Claim limit: nothing here asserts that six-slot Legacy data persists; see §7.

## 3. Preconditions

1. Catalog contains at least one Umamusume (manual `umamusume` rows or
   `uma:fetch gametora-characters` + review promotion). Unmet → see §4.
2. Run creation needs no auth, network, or engine state (local-only, NFR-1/2).
3. Legacy Select (when built) additionally needs candidate parents: completed runs
   marked as available ancestors. Today that concept has no table; see §7.
4. Scenario selection is free text today; `config/scenarios.php` exists but the
   create form does not offer a picker. Do not describe a picker that is not in
   HEAD.

## 4. Empty-state remedies

| Missing | Remedy (required rendering) |
|---|---|
| No Umamusume in catalog | Create form shows empty state: name the two fills (seed for illustration; `uma:fetch` for facts) and link `/umamusume`; submitting is not possible, so the form states why rather than disabling silently (absent-beats-disabled pattern, D-263) |
| No runs yet (`runs.index`) | Existing empty card pointing to "New run" (live: `runs/index.blade.php` @empty) |
| No parents available for Legacy Select | Create the run **without** parents (nullable FKs); do not render empty parent slots (§7) |
| No scenarios picker | Not an error state; scenario stays optional free text |

## 5. Step sequence

1. Trainer opens `/training-runs/create` (`runs.create`).
2. Selects the Umamusume (required select; existing data source: catalog).
3. Optionally types a scenario name, picks status (default Active), adds notes.
4. Optional Legacy Select, when implemented: a pre-run step choosing exactly two
   inheritance sources, labeled **Parent A** / **Parent B** (identifiers stay
   `inheritance_parent_a_id` / `_b_id`, D-267). Copy may use "Legacy"/"Ancestor"
   and "Inspiration" for the mechanic (root DESIGN §6). Never per-turn reachable
   (D-260): after the run exists, its parents are fixed; editing them from a turn
   view is a defect, not a feature.
5. `POST /training-runs` (StoreTrainingRunRequest). Validation failure returns to
   the form with field errors; success redirects to `runs.show`.
6. On the run page the Trainer logs turns (`runs.turns.store`, `StoreTurnEntryRequest`)
   and sets skill states (`runs.skills.sync`, `StoreRunSkillRequest`); export at
   `runs.export` (csv/json).

## 6. Validation rules (current truth)

- Turn stats: `0..1200`, `turn >= 1`, unique per run
  (`StoreTurnEntryRequest:40-46`); UI inputs carry `max="1200"`.
- **Warning:** ADR-0002 accepts a future `0..2000` bound. It is not implemented.
  No doc, label, denominator, or help text may present 2000 as accepted today.
  When it lands, the slice must update validator, UI max attributes, soft-cap vs
  scenario-ceiling display (ADR-0002 UI rule), tests, and docs **in the same
  change** (see KNOWN-ISSUES adjacent watch item in the 2026-09-28 audit).
- Mood: nullable, must be a `MoodTier` enum value (measured client strings,
  a7cabc0). Condition pill **colours** for the lower three tiers remain
  unverified: neutral tints only (`docs/requests/game-mechanics-condition-labels.md`).
- Disclosure of untracked numbers: `N/A` + tooltip, never `0`, never an em dash
  (settled ruling; KI-7).

## 7. D-268 non-persistable panel (binding wording)

Current persistence limit: only **Parent A** and **Parent B** (two nullable FKs to
`umamusume`) may be stored, and only when a UI exposes them; no rank, guest flag,
grandparent circle, Spark list, affinity, or per-Legacy metadata is persistable by
committed schema. Any richer Legacy Select must be marked non-persistable, or the
controls disabled/absent, until a schema proposal on the ADR-0003 pattern lands.
Documenting or mocking the six-slot screen without this notice implies data can be
saved today; it cannot.

## 8. Error states

| Error | Trigger | Handling |
|---|---|---|
| Validation (422/redirect with `$errors`) | bad stats, duplicate turn, unknown umamusume/parent id | field errors on the form; turn errors render in the runs.show error list |
| 404 | unknown run/slug; turn not owned by the routed run (`abort_unless` in `updateTurn`/`destroyTurn`); unknown export format | framework 404 page; API gets the `{error:{code,message}}` envelope |
| Engine/fetch failure | only upstream of preconditions (catalog empty) | NFR-2: prior data untouched; run flow never depends on live fetch |
| Loading | initial navigation: browser-native (ADR-0007 clause 1); user-initiated async (future refresh) requires explicit indicator (clause 2) |

## 9. Lore-safe copy guidance

- Characters are Umamusume (humanoid race); no equine vocabulary or iconography in
  any label, tooltip, empty state, or export header (C-4, DESIGN §6).
- Ancestors: "Parent A" / "Parent B", or "Legacy"/"Ancestor"; mechanic name
  "Inspiration"; screen/widget name "Legacy Select". Never sire/dam/mare/foal or
  breeding framing, including in `title` attributes and CSV headers.
- No unverified client strings as UI copy: words pass only when measured and
  provenance-recorded (D-20; MoodTier words are, pill colours are not).

## 10. Out of scope

Mid-run ancestor editing (forbidden by D-260), six-slot lineage persistence
(no schema), affinity computation (D-262: entered/fetched, never inferred),
spark probability simulation (D-264: show published rates only).
