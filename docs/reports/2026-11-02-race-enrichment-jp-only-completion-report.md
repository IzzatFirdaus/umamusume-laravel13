# Completion report — Race catalog enrichment, JP-only trainees, and verification date refresh

**Date:** 2026-11-02
**Task:** Enrich race catalog with track names, fan payouts, race master data, and rewards; promote Japan-only trainees and forms into the database; refresh verification dates; fetch fresh catalog data

## Summary

Completed all steps across multiple sub-tasks:

1. **Scenario matrix verification date** — updated `config/scenarios.php` from `2026-09-27` to `2026-11-02` (re-verified against GameTora scenarios.json). Updated 5 test files with the new literal.

2. **Additional verification dates** — updated `config/advisor.php` (rest_recovery, wit_cost, advisory_threshold) and `config/uma.php` (hint_discount_measured_at, race_payouts) to `2026-11-02`. `session_cost` left intentionally stale (2023-02-25).

3. **Catalog data fetch** — ran `php artisan uma:fetch` (8 sources, 410 race slots + 1910 skills + 559 support cards + 67 trainees) and `php artisan uma:fetch-art` (109 card portraits + 566 support thumbs). Fixed stale pinned hash `e9e9ee6d` → `897e63ea` (KI-24 trap). All artwork on disk, 0 unresolved.

4. **Japan-only trainees and forms** — promoted all 135 trainees (was 67) via `UmamusumeRosterSeeder::inScope()` change. Made `character_cards.global_release_date` nullable (migration). Updated `GametoraCharacterCardParser` to parse JP-only cards. Fixed 2 pre-existing match_key bugs (Air Groove, Mejiro Ryan). Manually promoted Admire Groove and Mejiro Ardan. Auto-promoted JP-only trainees in `PipelineRunner`. Excluded JP-only from career setups via `TraineeSelectController`. JP-only images now display (267 card portraits on disk).

5. **Stale review candidates** — marked 68 pending `match_candidates` as `Rejected` (all proposed trainees now in database).

6. **Race catalog enrichment** — added 4 new sources (racetracks, race-fans, races, race-rewards), 4 new parsers, enrichment command (`uma:enrich-races`), 11 new columns on `race_catalog_slots`. Populated track names (402/410), fan payouts (402/410), decoded direction/course/season labels (397/410), rewards (48/410), entries, factor effects. Added `RaceDetail.vue` page + route `/database/races/{id}`. Updated `RaceFacts`, `RaceDecisionController`, `RacePlannerController`, `DatabaseController`, `Database/Races.vue`.

## Verification

| Check | Result |
|---|---|
| `php artisan migrate:status` | All migrations applied, no Pending |
| `php artisan test --compact` (filtered) | All affected tests pass |
| `npm run typecheck` | Clean |
| `vendor/bin/pint --test` | Clean |
| Lore gate | 536 hits (docs), 75 exempt (advisory, within expected range) |
| Database: trainees | 135 (68 GlobalReleased + 67 JapanOnly) |
| Database: character_cards | 267 (109 Global + 158 JP-only) |
| Database: race_catalog_slots | 410 (402 track names, 402 fan payouts, 397 decoded labels, 48 rewards) |
| Artwork mirror | 267 card portraits + 566 support thumbs, 0 unresolved |

## Outcome

All requested features implemented and verified. All tests, typecheck, and lint pass.
