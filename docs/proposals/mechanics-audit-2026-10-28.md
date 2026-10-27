# Mechanics Audit — Trainer Desk vs UMAMUSUME_REFERENCE.md

Dated 2026-10-28. One row per reference subsection §1.1.1 through §2.2.4, with the code
location and a status of **correct** / **wrong (fixed)** / **missing**. The governing rule is
`UMAMUSUME_REFERENCE.md` for game mechanics, ADRs for architecture; where an ADR contradicted a
mechanic it was amended, never obeyed.

| § | Reference claim | Code location | Status |
|---|---|---|---|
| 1.1.1 | Five disciplines (Speed/Stamina/Power/Guts/Wit), Wit costs no energy and pays more SP | `config/scenarios.php` per-level training tables | correct |
| 1.1.2 | `gain = (base + bonus) × growth × mood × effectiveness × friendship × participant` | `TrainingCard.vue` gain caption names mood/bonds/growth terms | correct |
| 1.1.3 | Friendship at gauge 80, type-match, multiplicative stacking (1.25×1.30=1.625) | `DeckAnalysis` reads `calc`, 80 threshold | correct |
| 1.1.4 | Hint discount ladder Lv1 10 / Lv2 20 / Lv3 30 / Lv4 35 / LvMax 40, cost = floor(base×(1−d)) | `config/uma.php` `skills.hint_discount`, `App\Services\HintDiscount` | **wrong (fixed R5)** |
| 1.1.5 | Failure risk is energy-scaled, no hard cliff; three penalties; mitigation table | `risk_absence`, Good-Luck Charm (0% for 1 turn, the real item) | correct |
| 1.1.6 | Five mood states, ten percentages (training + race columns), non-monotone colour ramp | `CareerStatePanel.vue` `MOOD_TRAINING_PCT`/`MOOD_RUNNING_PCT` | **wrong (fixed R5)** |
| 1.1.7 | Idle mode: 50 min, app closed, three inputs, no trophies/gallery/concerts, growth rate ignored | `IndependentTrainingRun`, `Lifecycle`, `Simulator`, `CompleteIndependentTraining` | **missing → built (R6)** |
| 1.2.1 | Turf/Dirt, four distance bands, 1400m = engine Mile | `RaceFacts`, `config/uma.php` `fit_distance_type` | correct |
| 1.2.2 | Four strategies (Front Runner/Pace Chaser/Late Surger/End Closer), a trainee property | `config/uma.php` `veteran.suggested_tags` | correct |
| 1.2.3 | Four phases, section boundaries from export | `RaceFacts` phase gating | correct |
| 1.2.4 | Course factors: corners/straights/elevation/surface changes | `RaceFacts` / parser course flags | correct |
| 1.2.5 | Four weather + four ground codes, −50 Speed on Heavy, community stamina multipliers | parser + `RaceFacts` (community-grade per §8.4) | correct |
| 1.2.6 | Five grade codes, run-level maiden lock, not a per-race column | `TrainingRun::maidenGates()`/`hasClearedMaidenLock()`, per-race columns dropped | **wrong (fixed R5/R6)** |
| 1.3.1–1.3.3 | Five stats and their gates (Speed 2000, Stamina 1200, Power, Guts, Wit) | `RaceFacts`, stat band | correct |
| 1.3.4 | Four cap layers; scenario cap read from config, not hardcoded 1200 | `ScenarioCaps` reads `config/scenarios.php` | correct |
| 1.3.5 | `stat_bonus` = Growth Rate row, a multiplier not a training bonus | `training_runs.growth_rate` stored as entered row, rendered `+N%` | correct |
| 1.3.6–1.3.7 | Final totals / current targets are informational | Cockpit target display | correct |
| 1.4.1 | Seven card types; Pal differs by event kit; Group is a distinct type | `SupportCard` type, `PerformanceType`-adjacent enums | correct |
| 1.4.2 | R/SR/SSR, four breaks, +5/level (30/35/40/45/50 SSR) — Global model, not JP levels-removed | `CardRarity` (R/SR/SSR), anchor ladder to 50 | correct |
| 1.4.3 | Friendship gauge 80, type match, multiplicative | `DeckAnalysis` `calc` | correct |
| 1.4.4 | 135 char / 11 Pal / 5 group chains; hint levels 10%/level | `GametoraSupportCardParser` `hint_skills` | correct |
| 1.4.5 | Six slots, no duplicate, 2–3 per type, no Pal/Group stacking | `deck_slots` + `DeckPickerSearchRequest` | correct |
| 1.4.7 | Level ladder 1,5,…,50, floor interpolation, `-1` = locked; Unique Perk unresolved (conflict row 47) | `SupportCardEffects::forLevel()`/`atCap()` (floor, not round) | **wrong (fixed R5)** |
| 1.4.8 | Only Friendship Bonus, Failure Protection, Energy Cost Reduction, Specialty Priority combine; rest flat | `DeckAnalysis` reads `calc` | correct |
| 1.5.1 | Career start deterministic; April Classic/Senior probabilistic; white never fires at start | `InheritanceEventController` | correct |
| 1.5.2 | Four colours (Blue stat / Pink aptitude / Green unique-skill / White skill-hint+competition) | `LegacySelectionPayload`, `LegacySelect.vue`, `Help.vue` | correct |
| 1.5.3 | Star odds rolled, not chosen: <600 90/10/0, 600–1100 50/45/6, >1100 20/70/10 | `InheritanceEventController::starRollTable()` (probabilities, not a map) | correct |
| 1.5.4 | Two parents + four grandparents, no self-ancestor, one rented parent | `LegacySelectionPayload` | correct |
| 1.5.5 | Chain strategy informational; no strategy advisor ships | — | correct (informational) |
| 1.6.0–1.6.11 | Twelve modes; Champions Meeting joins on `resource_id` not name; no guarantee-success item | mode config, no `resource_id`/name bug, `Good-Luck Charm` is the real 0% item | correct |
| 2.1 | Four Global scenarios; caps derived from `scenarios.json` | `config/scenarios.php` (URA 1400×5, Unity 1300/1800, Trackblazer 1200/1900, Grand Live 1600/1300) | correct |
| 2.2.1 | URA Finale: Happy Meek duel, facility repetition, fan gates | `config/scenarios.php` `ura_finale` | correct |
| 2.2.2 | Unity Cup: per-stat ranks G–S, S+ is overall not per-stat, per-member Spirit gauge | `team_rank_ladder` (S+ excluded), `SpiritBurstState` | correct |
| 2.2.3 | Trackblazer: Grade Points (not Result Pts), four objectives, Alarm Clock retry | `config/scenarios.php` `grade_points`/`shop_coins` | correct |
| 2.2.4 | Our Grand Concert: Performance/Promo Concert/Grand Concert/lessons/songs/Great Success | `PerformanceType` (Dance/Passion/Vocals/Visuals/Composure), config nouns | correct |

## Fixed in Round 5 (four defects)

1. §1.1.4 hint discount ladder — ADR-0011/0029 said "no discounts / level only"; the ladder exists in
   the client. Added `config/uma.php` `skills.hint_discount`, `HintDiscount`, wired the Skills
   Planner, amended both ADRs.
2. §1.1.6 mood — running column was GREAT-only; added the full per-tier running column.
3. §1.2.6 maiden lock — per-race columns nullified and dead; run-level `maidenGates()` + dropped the
   per-race columns (migration `2026_10_27_120000`).
4. §1.4.7 effect floor — `round()` was nearest; `floor()` is the reference's truncation.

## Built in Round 6

§1.1.7 Independent Training: schema, lifecycle, simulator, scheduled completion. The simulator is a
calibrated approximation — §1.1.7 does not publish the per-turn algorithm — and its docstring states
that boundary, with two pinned rules (growth rate excluded, priority list is the hint set).

## Re-scoped, not built

The plan's "20-run DailyCap" (`canStart` false at 20) is contradicted by the reference's own conflict
log row 1/28: "Recorded as a cap on jewel payout from training race rewards rather than on
自主トレ育成 entries; plan on the jewel limit binding, not the run count." The 20/day is a free-Carat
payout ceiling, not a run-start gate, and the app models no Carat reward, so there is nothing to
gate. No run-start cap was added.

## Reference-unresolved, correctly unrendered

§1.4.7's Unique Perk is genuinely unresolved in the reference (conflict row 47: a "perk level" or a
"card-level unlock", no source settles it). The app ships **no** perk code at all — `grep perk` over
`app/` returns nothing — so it neither invents a deterministic perk level nor renders a badge it
cannot source. That is "matches the reference's uncertainty", not "matches a fact".