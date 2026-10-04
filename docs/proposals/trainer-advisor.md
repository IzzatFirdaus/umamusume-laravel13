# Spec: BuildTarget + Trainer Advisor (Trainer Desk 2.0, subsystem 1)

Status: **Spec — awaiting owner review.** No code yet.
Date: 2026-10-04
Authorization: `ADR-0020` Decision §2 (target-based Trainer Advisor), extending `ADR-0001`; `PRD.md` US-13, US-14, FR-F.
Approach: A (minimal honest advisor; domain-only, no UI — the SPA rewrite hosts the interface later).

## 1. Purpose and scope

Rank the Trainer's next-turn options against a Trainer-entered build target, deterministically, with every
number's provenance shown. The app advises; the Trainer decides.

**In scope (v1):**

- A per-run `BuildTarget` record (US-13).
- A `TrainerAdvisor` engine that ranks the six energy-relevant options — Speed, Stamina, Power, Guts, Wit,
  Rest — against the target and the `ADR-0001` Energy constants (US-14).
- A two-state energy advisory band against the single sourced threshold; a row-level reason line per
  suggestion.

**Out of scope (v1), named so they are not read as oversights:**

- **No stat-yield projection** ("+62 Speed"). No source publishes per-training yield (it depends on support
  bonds, training level, and facility, none in scope). Same evidence bar as `ADR-0001`'s rejection of full
  probability estimates.
- **No numeric failure estimate.** No source publishes a failure curve (`ADR-0001` §3). The opt-in numeric
  model is deferred to v1.1 and needs an owner-ruled, clearly-labelled model; it is not built here.
- **No race advisory.** "Next mandatory race turn" is not computable (`ScenarioSlot` has no year/turn) and
  race outcomes stay banned (`ADR-0016`, §6.11). Race is not a ranked option in v1.
- **No UI.** The engine is headless and unit-tested; the cockpit panel ships with the SPA rewrite.
- **No support-bond, facility, or scenario-mechanic modelling.**

## 2. BuildTarget storage and contract

One nullable json column `build_target` on `training_runs`, read through a typed
`App\Models\Advisor\BuildTargetPayload` value object — the `ADR-0010` `legacy_selection` /
`LegacySelectionPayload` pattern, not a new table. It is an optional 1:1 record; a table is
over-engineering.

Fields (all nullable except where noted):

| Field | Type | Notes |
|---|---|---|
| `purpose` | enum | `StoryClear` / `ChampionsMeeting` / `ParentFarming` / `SkillFarming` |
| `distance` | string | one of the four distance bands |
| `surface` | string | Turf / Dirt |
| `style` | string | one of the four running styles |
| `targets` | array | five per-stat ints, each validated against `ScenarioCaps::forRun($run)` (US-13, `ADR-0015`) |
| `skill_priorities` | string[] | ordered; names only, no engine over them in v1 |

Writes go through a Form Request (`StoreBuildTargetRequest`); validation has one owner. Trainer-entered,
so `is_manual` in spirit — the fetch engine never touches it. A run with no target names the absence; the
advisor then falls back to `ADR-0001`'s Energy-only guidance rather than inventing a target.

## 3. Constants — `config/advisor.php`

The `ADR-0001` §2 constant set, versioned and dated, stored in config and never hardcoded. v1 uses four;
the rest are recorded for later and not read by the v1 engine.

| Key | Value | Source (`UMAMUSUME_REFERENCE.md`) | Confidence |
|---|---|---|---|
| `rest_recovery` | +30 | §1.1.5, GameWith 2026-09-25 | current |
| `session_cost` | [17, 28] (range; scales with training level, which is not tracked) | §1.1.6, GameWith | ⚠ STALE 2023-02-25 |
| `wit_cost` | 0 | §1.1.1, Game8 2026-09-10 | current |
| `advisory_threshold` | 50 | §1.1.5, qualitative only | current |

`session_cost` is a range and stays a range: energy-after renders as `E−28 … E−17`, not a single invented
point. The config carries `source` + `verified_at` per entry so a suggestion can show the date of the data
behind it (`ADR-0001` §7). Mood multipliers are recorded in `ADR-0001` but **not** loaded in v1 — they only
matter to yield prediction, which v1 does not do (YAGNI).

## 4. TrainerAdvisor engine — `app/Services/TrainerAdvisor.php`

Pure functions over: the latest `TurnEntry` (five stats, `energy`, `mood`), the run's
`BuildTargetPayload`, and `config('advisor')`. No DB writes; no game-state guessing.

For each of the six options it returns a small value object:

```
action            Speed | Stamina | Power | Guts | Wit | Rest
deficit_closed    max(0, target - current) for that stat; 0 for Rest; Wit uses its own target
energy_after      range from session_cost; rest_recovery for Rest; wit_cost (small recovery) for Wit
band              Advisory state from CURRENT energy vs advisory_threshold; Wit => RiskNotMeasured
reason            a string naming the arithmetic ("Wit costs 0 Energy and you are at 42")
```

The band is computed from **current** energy against the sourced 50 line (`ADR-0001` §3). It has exactly
two states — `AtOrAboveAdvisory` / `BelowAdvisory` — because 50 is the only sourced threshold; the
"highly dangerous below 30" line is not in any source (`ADR-0001` §3 final bullet) and is **not** rendered
as game fact. Wit always enters at full Energy, so a band would assert an exemption the sources leave
unsettled — Wit renders `RiskNotMeasured` (`ADR-0001` §3 correction, Source Conflict Log row 2).

## 5. Ranking and reason lines

Deterministic, in this order. The first rule that fires produces the recommendation; the next-best option
is reported as the alternative.

1. **No current energy** (latest turn has `energy = null`, e.g. a historical run) → no band, no ranking;
   render the absence by name.
2. **Below advisory** (`energy < 50`) → recommend **Rest**; reason: "Energy E is below the 50 advisory
   line; Rest recovers +30." Alternative: **Wit** if it has a target deficit ("Wit costs 0 Energy and
   still makes progress").
3. **At or above advisory, no build target** → Energy guidance only (`ADR-0001`'s original scope): no stat
   ranking, because there is no target to rank against. Names the absence.
4. **At or above advisory, target set** → recommend the training with the **largest deficit**; ties break
   by `config('scenarios.stat_order')`. Reason: "<Stat> is D below target (largest deficit)."
   Alternative: **Wit** if it has a deficit (energy-free progress).

Every suggestion carries its reason; a suggestion with no derivable reason does not ship (`ADR-0001` §4).
Figures are labelled with the provenance vocabulary (`ADR-0020`): band and deficit are **calculated** from
entered turns + declared constants; nothing here is **confirmed** game state.

## 6. Source limits and owner decisions this spec does NOT make

- **Danger band boundary.** Only 50 is sourced. A third band needs an owner-ruled threshold, attributed as
  an owner ruling (`ADR-0001` §3). v1 ships two states. *Owner input needed if a Danger band is wanted.*
- **Numeric failure estimate.** Needs an owner-ruled, config-stored, clearly-labelled model (no sourced
  curve). Deferred to v1.1. The US-11 numeric-estimate preference stays off and inert until then.
- **`mood` vs `condition` column overlap.** Unresolved since `ADR-0001` §5; the advisor reads `mood` and
  does not touch `condition`. Not a blocker.

## 7. Testing

- Unit (no DB): the ranking rules (each of the four), the band boundaries (49 vs 50; Wit →
  RiskNotMeasured), the energy-after range, and that every produced suggestion has a non-empty reason.
  Constants injected from config so a changed constant is caught.
- Feature: `build_target` round-trips through the Form Request; a target above the scenario ceiling is
  rejected (`ScenarioCaps`); a run with no target renders the named absence. Uses factories; no network.

## 8. Files touched

- `database/migrations/…_add_build_target_to_training_runs_table.php` (+ `ARCHITECTURE-ESSENTIALS.md`
  digest, `ADR-0020` citation, working `down()`) — scratch DB, not the shared dev file.
- `app/Models/Advisor/BuildTargetPayload.php` (new; mirrors `LegacySelectionPayload`)
- `app/Models/TrainingRun.php` — one accessor (`buildTarget()`), cast registration
- `config/advisor.php` (new)
- `app/Services/TrainerAdvisor.php` (new)
- `app/Http/Requests/StoreBuildTargetRequest.php` (new)
- `tests/Unit/TrainerAdvisorTest.php`, `tests/Feature/BuildTargetTest.php` (new)

No views, no routes for the advisor output in v1 (the target is stored via the existing run form; the
advisor is consumed by the SPA rewrite later). `git status` first — Pint/PHPStan run whole-tree and this
tree carries other sessions' in-flight work; scope to my own paths.

## 9. Deferred to later slices

Numeric failure model (v1.1, owner-ruled), race advisory (after the `ADR-0016` data blocker closes),
support-bond/facility-aware ranking, mood-weighted ranking, the cockpit UI (SPA rewrite), and any Grand
Concert mechanics (documented: false).
