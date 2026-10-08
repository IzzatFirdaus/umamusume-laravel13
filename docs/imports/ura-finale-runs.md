# URA Finale runs import (tracker runs 1, 2, 3, 4, 11, 14, 15, 16)

Source: `docs/Umamusume_Progress_Tracker_Runs.md`. Eight career runs were imported into
`training_runs` through `UraFinaleRunsSeeder` (standalone, not part of `DatabaseSeeder`).

## How to run

```bash
php artisan db:seed --class=UraFinaleRunsSeeder
```

Idempotent by `import_source`: each run carries the label
`docs/Umamusume_Progress_Tracker_Runs.md (Run N)`. A run that already has its label is
skipped, so re-running adds nothing. The seeder is intentionally **not** registered in
`DatabaseSeeder`, so a fresh install does not silently write Trainer history.

## What each run contains

| Tracker run | Trainee (card) | Snapshot turn | Status | Race entries with results |
|---|---|---|---|---|
| 1 | El Condor Pasa ([El☆Número 1]) | 48 (Year 1 Late Dec) | Active | Junior Make Debut 1st |
| 2 | Tokai Teio ([Peak Joy]) | 44 (Year 2 Kikuka Sho race day) | Active | Junior Make Debut 1st |
| 3 | Maruzensky ([Formula R]) | 75 (Year 4 URA final) | Completed | Junior 1st, URA Q 1st, URA S 1st, URA F 1st |
| 4 | Maruzensky ([Formula R]) | 75 | Completed | Junior 2nd, URA Q 1st, URA S 2nd, URA F DNF (Skipped) |
| 11 | Haru Urara ([Bestest Prize ♪]) | 73 (Year 4 URA qualifier race day) | Active | Junior 2nd, URA Q Entered |
| 14 | Daiwa Scarlet ([Peak Blue]) | 75 | Completed | URA F 2nd |
| 15 | Vodka ([Wild Top Gear]) | 75 | Completed | URA F 1st |
| 16 | Vodka ([Wild Top Gear]) | 75 | Completed | URA F 1st |

Every run stores one `turn_entries` row holding the snapshot roster line (exp, SP, energy,
condition, mood), selected skills, and a `notes` column with the tracker's goal history.
Trainer-sourced data: growth, aptitude, and remaining goals are only ever documented;
they are not schema columns.

## Skill mapping rules

- Tier names carry their catalog tier in the display (◎ best, ○ normal, × practice). The
  tracker renders tiers as ○ and ⦾; ⦾ resolves to the ◎ row and falls back to × when the
  skill has no ◎ row (Corner Adept, Corner Acceleration).
- `run_skills.status` is `Acquired` for a checked row, `Suggested` for a blank row;
  `turn_acquired` stays null (the tracker does not record acquisition turns).
- Three rows needed out-of-band resolution:
  - Run 15 "Front Runner Straightaways" lost its glyph (cost 117 = the ○ tier) -> id 300.
  - Run 16 "Front Runner Savvy" lost the space before its ○ (cost 99 = the ○ tier) -> id 352.
  - Run 16 "Remove Wet Conditions x N/A ✅" names a skill with no catalog row; left unattached.

## What is deliberately omitted

- **Deck loadouts, growth plans, aptitudes.** The tracker does not record them per run in
  a structural form, and no schema column exists. They live in each run's `notes`.
- **Earn Fans targets.** Run 11 shows the three house targets done with no party/team
  table in the tracker; recorded in `notes` only.
- **Race days without a resolved result.** Runs 1-3 and 11 record non-URA races in the
  goals panel (Derby, Arima, JBC Sprint and so on); only race days the tracker actually
  resolves produce a `race_entries` row. Runs 14-16 record only the terminal URA F result.
- **The run 1 contradiction.** The tracker's plan box says "9 turns" remain before
  Takarazuka while the goals panel already records it won; `turn` was kept at 48 per the
  CLASSIC LATE DEC header. Flagged in `notes`, not smoothed.

## Verification

```bash
php artisan test --compact --filter=UraFinaleRunsSeederTest
php artisan db:seed --class=UraFinaleRunsSeeder   # second run adds nothing
php artisan tinker --execute="TrainingRun::where('import_source','like','docs/Umamusume\_Progress\_Tracker\_Runs.md (Run %')->count()"  # expects 8
```