# Scenario Field Metadata Wiring Report

**Date:** 2026-10-16
**Scope:** Wiring `scenario-field-metadata-2026-10-15.json` (the 8-field × 4-scenario matrix) into the Scenario Select, Run Detail and Training Decision surfaces.
**Source of record:** the 2026-10-15 artifact and its report, both in this directory. Nothing was re-researched; every rendered value, reason and source string below is carried from that artifact verbatim.
**State:** working tree only. Nothing committed, staged, or pushed; the owner reviews the diff.

**Erratum, 2026-10-16 (close-out pass):** §1, §3 and §4 above describe the Rule family row rendering the taxonomy keys (`team_rank`, `repetition`, `repetition_plus_purchases`). The same-day close-out pass carried the owner's display ruling: the row now renders the humanized labels (`Team rank`, `Repetition`, `Repetition + purchases`) derived in `ScenarioSelectController::ruleFamilyLabel()`, and the card never prints a snake_case key. The wiring itself is unchanged. See `scenario-field-metadata-closeout-report-2026-10-16.md` §4.

---

## 1. Determination

All eight fields now render on their target surfaces in one of the four accepted states (sourced value / reasoned absence / hidden / decision-required). The storage decision deviates from the brief's guess in one place, with reasons in §6.

| # | Field | Scenario Select | Run Detail (Cockpit) | Training Decision |
| - | ----- | --------------- | -------------------- | ----------------- |
| 1 | `ruleset_version` | Resolved ×4 — new "Scenario ruleset date" row | Resolved — new strip line | n/a |
| 2 | `recommended_use` | URA resolved (pre-existing config value); ×3 N/A with the artifact's recorded search verbatim | n/a | n/a |
| 3 | `rule_family` | Resolved ×4 — reads the entry's `facility_level_source` | n/a | n/a |
| 4 | `planning_focus` | Unity resolved (pre-existing config value); ×3 N/A with the artifact's reason verbatim | n/a | n/a |
| 5 | `expected_gains` | n/a | n/a | Resolved per discipline per recorded facility level, `[JP]`-tagged; absence when no level recorded |
| 6 | `energy_cost` | n/a | n/a | Resolved per discipline per level, `[JP]`-tagged; Unity divergence note visible; Wit excluded |
| 7 | `risk` | n/a | n/a | N/A with the artifact's measured-negative reason verbatim, per scenario; Wit keeps `RiskNotMeasured` |
| 8 | `team_rank` | n/a | Current rank stays run data (`N/A` until recorded — already correct); the rank→level mapping already rendered by the Team Panel (`TeamRankGauge` + `config team_rank_ladder`) — verified, no change needed | n/a |

The `rule_family` question the brief flagged is answered by the artifact's own report: the field is "a taxonomy over an already-sourced config fact" (`facility_level_source`), no publisher uses the term. The wiring therefore cites the repository's own definition, as the brief directs for that case.

## 2. Files changed

**Wholly this change:**

| File | Change |
| ---- | ------ |
| `config/scenarios.php` | Per scenario: `ruleset_version` (date/event/source), `training_tables` (the artifact's four per-level grids verbatim, with source/server/notes), `risk_absence` (reason/source/server verbatim); `metadata` gains `recommended_use_reason` / `planning_focus_reason` where the artifact records a searched-and-not-found cell |
| `app/Http/Controllers/Career/ScenarioSelectController.php` | Cards gain `rule_family` (from `facility_level_source`) and `ruleset_date` (value = date + derived launch/rework word, title = event + source); `metadata()` maps the two reason keys; the `ruleset()` method (app.ruleset) is untouched |
| `app/Http/Controllers/Career/CockpitController.php` | Scenario section gains `ruleset_date` / `ruleset_date_title` beside the untouched `version` / `version_title` pair |
| `app/Http/Controllers/Career/TrainingDecisionController.php` | New `SECONDARY_ROUTES` const (the `[Both]` secondary routing, §1.1.1) and `publishedFor()` / `gainsText()` / `facilityLevel()`; each option payload gains one `published` key |
| `resources/js/pages/Career/ScenarioSelect.vue` | New "Scenario ruleset date" SubCell; Rule family row repointed to `card.rule_family`; Planning focus absent-state now an AbsenceValue carrying the artifact's reason; Recommended use absent-state title now the artifact's reason when configured |
| `resources/js/components/scenario/ScenarioPanel.vue` | Strip gains the "Scenario ruleset date" line (hidden when no scenario); **removed the `register('spirit_bursts', SpiritBurstsPanel)` line — pre-existing defect, see §8** |
| `resources/js/components/career/TrainingCard.vue` | Expected gains renders the published row (value + `[server]` tag + provenance badge + visible detail line) or the reasoned absence; Energy cost value slot prints the published figure when one applies, with the declared constant still stated beneath; Risk absence reason comes from the payload |
| `resources/js/pages/Career/TrainingDetail.vue` | `Option` interface extended; **restored `aria-label="Energy band"` — pre-existing regression, see §8** |
| `resources/js/types.ts` | `ScenarioPanelSection` gains `ruleset_date` / `ruleset_date_title` |
| `SCREEN_SPEC.md` | SCR-002, SCR-CAR-012 and SCR-CAR-019 updated with dated amendments; gap 3 of SCR-CAR-012 gains a dated note |
| `tests/Feature/ScenarioMetadataTest.php` | Stale pre-existing test re-pinned (see §8) plus the new keys |
| `tests/Feature/ScenarioPanelTest.php` | Section key list extended; ruleset-date value asserted |
| `tests/Feature/CareerScenarioSelectTest.php` | First case extended with ruleset dates, rule families, and the verbatim reasons |
| `tests/Feature/CareerTrainingDetailTest.php` | `OPTION_KEYS` gains `published`; three new payload cases plus the 4×5 sweep case; the banned-key `missing()` list is unchanged |
| `tests/browser/career-scenario-select.spec.ts` | R2-05 case extended to the three ruleset-worded rows; reason assertions per render form |
| `tests/browser/career-training-detail.spec.ts` | Gains-disclosure reason strings re-pinned; new case renders the published path through a real walk with facility levels |

**Co-modified with a concurrent session (their Rest/Recreation slice is landing live in the same tree):** `CockpitController.php`, `TrainingDetail.vue`, `CareerTrainingDetailTest.php`, `career-training-detail.spec.ts`. My hunks in those files are only the ones named above; the rest of each diff is theirs. Also co-present in the tree: their controller import, `ActionGrid.vue`, their new browser cases, and modified `CareerCockpitTest.php` / `DocSchemaDriftTest.php` files I did not touch.

## 3. Data path per field

1. **`ruleset_version`** → `config/scenarios.php` per-scenario `ruleset_version` (date/event/source, verbatim from the artifact) → `ScenarioSelectController::rulesetDate()` composes `value` = `2026-07-01 (rework)` (launch/rework derived against the entry's own `live_on_global`) and `title` = event + `Source: …` → the card's new `Scenario ruleset date` SubCell between "Live on Global" and "Ruleset version". Same config on the Cockpit path: `CockpitController::scenarioSection()` emits `ruleset_date` / `ruleset_date_title` → the strip's new line. `app.ruleset` is untouched at both ends.
2. **`recommended_use`** → already in config for URA (`metadata.recommended_use`, pre-existing) → the card's existing row. For the other three → config `metadata.recommended_use_reason` (artifact reason verbatim) → payload → the row's `title` on the `N/A`.
3. **`rule_family`** → already in config for all four (`facility_level_source`) → `cards()['rule_family']` → the metadata block's "Rule family" row, repointed from `metadata.ruleset`. One owner; no new config key needed.
4. **`planning_focus`** → already in config for Unity (`metadata.planning_focus`, pre-existing) → the row. For the other three → `metadata.planning_focus_reason` (verbatim) → AbsenceValue disclosure on the row.
5. **`expected_gains`** → `config/scenarios.php` per-scenario `training_tables.per_level` (the artifact's grids verbatim) → `TrainingDecisionController::publishedFor()` keys `per_level[stat]['Lv'.$level]` to the facility level the latest `TurnEntry` recorded (`facility_speed`…`facility_wit`, nullable) → composes `text` ("+10 Speed, +4 Power, +4 SP") with `SECONDARY_ROUTES` and carries `server` / `source` / `notes` → option payload `published.gains` → `TrainingCard.vue` renders value + `[JP]`-style server tag + Confirmed badge + visible provenance line. No level recorded → `published.gains_absence` (controller-composed sentence), never a Lv1 substitute.
6. **`energy_cost`** → the same `per_level` rows' `energy` column → `published.cost` (`value` + display text `E−21`) for the four Energy disciplines → the card's Energy cost row: value slot prints the published figure with the server tag; the declared constant (`config('advisor.constants.session_cost')`, untouched) is still printed beneath because `energy_after` computes from it. Unity's divergence (`Global SP payouts for Unity Cup match URA Finale (+2 SP on Energy disciplines)…`, verbatim) rides `notes` and renders in the visible detail line on both the gains and cost rows. Wit: no published cost — its rows state a refund, not a cost, and the advisor's `wit_cost` constant (sourced, current) already governs the row.
7. **`risk`** → config per-scenario `risk_absence.reason` (the artifact's measured negative, verbatim; the per-scenario wording differences are preserved) → `published.risk_reason` (four Energy disciplines only) → the card's Risk AbsenceValue. The failure-outcome set stays in the artifact; shipping it was left an owner decision by the artifact's report and the brief's Field 7 spec asks for the reason only.
8. **`team_rank`** → verified pre-existing: the current rank is `widget_values.team_rank` (null → `N/A` with the D-220 absence sentence — correct: it is a run observation, not external data), and the rank→level mapping renders in the Team Panel (`TeamRankGauge :ladder`, fed from `config team_rank_ladder` via `teamSection()`), with `facility_level` derived per run. The mapping never substitutes for the rank anywhere on the wire. No change needed; the state matches the brief's Field 8 spec.

## 4. Field-by-field outcome (all four scenarios)

**Scenario Select** (each of the four cards):
- Scenario ruleset date: URA `2026-07-01 (rework)`, Unity `2026-07-01 (rework)`, Trackblazer `2026-03-12 (launch)`, Grand Concert `2026-07-22 (launch)`; title carries the artifact's event sentence + source.
- Recommended use: URA resolved (source visible in title per the pre-existing row); Unity/Trackblazer/Grand Concert `N/A` titled with their recorded searches verbatim.
- Rule family: `repetition` / `team_rank` / `repetition_plus_purchases` / `repetition`, titled with the repository-taxonomy citation.
- Planning focus: Unity resolved; URA/Trackblazer/Grand Concert AbsenceValue with the artifact's reason verbatim.

**Run Detail** (scenario strip, any declared scenario): the new `Scenario ruleset date:` line after the `Ruleset version: N/A` line; hidden for a run with no scenario. Unity Cup additionally shows the existing Team Panel with the rank ladder and `N/A` current rank.

**Training Decision** (five cards, per scenario): gains and cost rows resolve at the recorded level (Unity Cup Speed Lv3 renders `+10 Speed, +4 Power, +4 SP` and `E−21`, both tagged `[JP + Global(rework)]`, with the divergence note visible); no recorded level renders the named absence; a scenario-less run renders the no-scenario absence and no risk reason; Wit keeps its gains row (tagged) but no published cost and the `RiskNotMeasured` word.

## 5. Caveat checks

1. **`ruleset_version` is not `app.ruleset`** — holds. `app.ruleset` stays null in `HandleInertiaRequests`; `ScenarioSelectController::ruleset()` and the Cockpit `version`/`version_title` pair are byte-identical to before; the artifact's date travels only in the new `ruleset_version` config key and the new `ruleset_date` payload keys, under a different row label on both surfaces.
2. **Per-level tables are `[JP]`** — holds. The config tables carry `server` verbatim from the artifact; the Vue renders the tag beside every gains and cost value (`[JP]` on three, `[JP + Global(rework)]` on Unity), and the detail lines state the server again. No code path renders a per-level figure without the tag.
3. **Unity Cup SP divergence** — holds. The artifact's sentence is carried in `training_tables.notes` and renders in the visible detail line of the gains and Energy cost rows on Unity Cup's four Energy cards; the sweep test asserts it appears nowhere else.
4. **`app.ruleset` and `session_cost` untouched** — holds. `git diff` shows neither key modified; the Energy row's *display* changed, the governed constant and the `energy_after` arithmetic did not, and the constant stays printed beneath the published figure (ADR-0001 §2).

## 6. Production location and artifact lifecycle

**The values live in `config/scenarios.php`, not `database/seeders/data/`.** The brief guessed seeders data; the binding rule it also gives is "follow the closest precedent, do not introduce a new pattern". `database/seeders/data/*.json` files are read only by seeders writing DB rows (`race-tier-labels-2026-09-29.json` → `ScenarioSlotSeeder` → `race_catalog_slots`); nothing reads that directory at runtime, so wiring the screens from it would have meant a new DB table, a migration, a digest and an ADR for data that is static per-scenario reference. The established runtime home for per-scenario sourced tables is the matrix itself — `race_fatigue.odds`, `shop_items`, `spirit_burst_bands` and `fan_gates` all live there with the same shape: transcribed values, provenance comments, sources named. The training grids follow that precedent exactly. The artifact stays here in `docs/research-scratch-source/` as the dated research record; the config comments cite it and the publisher master.

**Lifecycle recommendation — option 1 (promote-and-pointer), with the pointer being the config citation rather than a README edit.** The wired artifact's values are now in config with provenance pointing at this file and `SCENARIO-PUBLISHER-REFERENCES.md`; a future reader asking "where do the training tables come from" lands on the citation chain. What is owed is a one-line pointer in this directory (this report is it) noting that `scenario-field-metadata-2026-10-15.json` is promoted into `config/scenarios.php` as of 2026-10-16, and that `race-field-data.{json,md}` remains unwired. This directory then continues as the permanent research corpus (option 3's shape) for the unwired workstream while promoted artifacts read as promoted from their own pointer lines. Owner call on the wording; no README was edited without a ruling.

## 7. Open questions for the owner

1. **The lifecycle wording** above — confirm the promote-and-pointer shape and whether `docs/research-scratch-source/README.md` (if the directory should carry one) is the right pointer location.
2. **`app.ruleset` and `session_cost`** — still governed, still untouched. The artifact now carries exactly the values those keys would want (the rework dates; the per-discipline cost tables that supersede the stale 2023-02-25 range). If you want either key populated or narrowed, that is a written ruling; the surfaces render the artifact's values without it.
3. **`rule_family` is repository-defined** (the report states it; the wiring cites the repo). The Unity Cup card now prints `team_rank` where it printed `Team` — config's `metadata.ruleset` key still holds `Team` but nothing reads it any more. If you want the humanized word kept on the card, say so and the row mapping changes back; if `metadata.ruleset` should be deleted from the matrix, that is a config edit I did not make unilaterally.
4. **The three unrecovered `recommended_use` and `planning_focus` values** — they render as the artifact's recorded searches. A fresh search pass may or may not find a sourced line; the current state is honest without one, and the searches are recorded verbatim for a future pass to pick up.
5. **The failure-outcome set** (Field 7) — published, scenario-independent, and currently not shipped to the card; shipping it is an owner decision the artifact's report defers.
6. **`metadata.ruleset` on the wire** — the payload still carries it (and `ScenarioMetadataTest` still pins Unity's `Team`) so the config fact stays inspectable, but no screen renders it. Harmless, but it is now a second copy of a fact with a single renderer; deleting it from the wire is a one-line cleanup whenever you rule on open question 3.

## 8. Pre-existing defects found and fixed in passing (with evidence)

Both were blocking the browser gates for every session, and both are in files this change already touches:

1. **Every Unity Cup Cockpit render crashed.** `ScenarioPanel.vue` (landed by the cockpit-wt merge, `53e491a`, 2026-10-16 01:44) registered `SpiritBurstsPanel` against `spirit_bursts` — a *widget* key, not a panel flag — so the panel-shaped component received the widget shape and its template read `props.scenario.alerts` off undefined. Measured: `TypeError: Cannot read properties of undefined (reading 'alerts')` at `SpiritBurstsPanel.vue…js` in the first browser run's error context, on the Cockpit page of the training-detail walk. Fixed by removing the registration and its import (the shell's own contract: "a renderer that cannot draw its key should not register for it"); the widget falls back to the labelled `ResourceMeter`, its pre-merge shape. The component file stays on disk.
2. **The Energy band select lost its accessible name.** `e02537e` (`fix(a11y): name the Training band select`) added `aria-label="Energy band"`; `63d1cdd` (`chore: commit stale tracked changes…`) dropped the attribute while keeping the R3-16 comment that documents it. Restored the one attribute; the R3-16 browser case proves it.
3. **`ScenarioMetadataTest` was red on `master` before this change** (verified solo: 1 failed / 2 passed at the start of this pass): it asserted URA/Trackblazer/Grand-Concert `metadata` is null, while config has carried metadata for all four since later slices landed. Since this slice revises that exact test, it is re-pinned to the matrix's current state; the stale assertion is not preserved.

Also recorded, not fixed: PHPStan on `CockpitController.php` reports 3 pre-existing errors at HEAD (`MONTHS` unused at :110; two nullsafe-at-:281 findings) — both sites are outside every hunk in the working tree, verified against `git show HEAD:`. They belong to the concurrent session's in-flight slice or to HEAD itself; I did not touch them.

## 9. Verification (commands and output)

Feature (Level 1 + Level 2, logs in `.scratch-uma/scenario-field-wiring-2026-10-16/`):

```
php artisan test --compact --filter="ScenarioMetadataTest|CareerScenarioSelectTest|ScenarioPanelTest|CareerTrainingDetailTest"
  → 1 failed (my Inertia closure typed array vs Collection; fixed), 52 passed
php artisan test --compact --filter="CareerTrainingDetailTest"   → 16 passed, then 17 passed (399 assertions) after the sweep case
php artisan test --compact tests/Feature/{CareerCockpit,CockpitRunIdentity,CockpitTeamPanel,SnapshotTrainingDecision,TrainingDetailPerformanceBoundary,SnapshotCockpit,ScenarioCapParity,ScenarioIsolation}Test.php
  → 77 passed (1005 assertions)
```

The full-suite pass count at this tree's HEAD is not claimed: Level 3 was not run (checkpoint policy; the owner reviews the diff before landing). `migrate:status` not required — no schema change; the wiring reads existing columns only.

Browser (session-private per KI-73/80, pre-flighted per KI-74 — SQLite read 400 ms, box quiet):

```
PLAYWRIGHT_PORT=8141 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/scenario-field-wiring-2026-10-16/browser-8141.sqlite \
  npm run test:browser -- tests/browser/career-scenario-select.spec.ts tests/browser/career-training-detail.spec.ts
Run 1: 13 passed / 5 failed — stale bundle + the two pre-existing defects above + two of my own spec bugs
Run 2 (rebuilt): 15 / 3
Run 3 (defects fixed, rebuilt): 18 / 2 (my two remaining spec bugs)
Targeted re-runs + final full run: **20 passed (1.5m), 0 failed** — includes the concurrent session's two new Rest/Recreation cases, which also pass against the merged tree
```

A build was required and is disclosed: the browser harness serves `public/build`, which predated the cockpit-wt merge — the merge's crash was invisible until something rebuilt. Both builds were probe-built to `.scratch-uma/<session>/build-check` first (a peer's mid-write file stripping `public/build` is the KI-70 hazard), then published in the foreground.

Static gates:

```
vendor/bin/pint --test --format agent <my PHP files>  → fail (1 fixer on one test file), fixed, re-run passed
vendor/bin/phpstan analyse --no-progress --memory-limit=1G <my files>  → my files clean; 3 pre-existing CockpitController findings recorded in §8
npm run typecheck  → clean (three runs across the change)
```

## 10. Lore gate

```
php tools/lore.php code  → 148 hit(s)      (baseline 97 on 2026-10-06; the movement is peer/untracked files)
php tools/lore.php docs  → 449 hit(s), 75 exempt line(s)   (baseline 443 on 2026-10-13)
```

Every hit on a file this change touched was classified against `AGENTS.md` §5's four allowed classes: the four `config/scenarios.php` hits are the pre-dated **Good-Luck Charm** rows (verbatim client item name, an allowed class the config's own comment documents), and the `CockpitController` hits are pre-existing "planned"/shop-projection lines outside my hunks. Zero new hits are attributable to files this change added or modified; the added vocabulary is client terms (Wit, Mood, Happy Meek, Spirit Burst) and the artifact's own sourced prose.

## 11. Explicit stop

The wiring is complete and verified; the report is the deliverable. No commit, no stage, no PR — the owner reviews the diff, including the two in-passing fixes (§8) and the four co-modified files shared with the concurrent session's Rest/Recreation slice. Nothing outside the change set was touched except `public/build` (rebuilt, gitignored) and the session-private `.scratch-uma/scenario-field-wiring-2026-10-16/` logs.
