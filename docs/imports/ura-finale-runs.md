# URA Finale runs import (tracker runs 1–17)

Source: `docs/imports/Umamusume_Progress_Tracker_Runs.md`. All seventeen career runs are imported into
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

| Tracker run | Trainee (card) | Position (turn) | Status | Race entries with results |
| --- | --- | --- | --- | --- |
| 1 | El Condor Pasa ([El☆Número 1]) | Classic Late Dec (48) | Active | Junior Make Debut 1st, Kyodo News Hai 1st, NHK Mile C. 2nd, Japanese Derby 2nd, Mainichi Okan 1st, Takarazuka Kinen 1st |
| 2 | Tokai Teio ([Peak Joy]) | Classic Late Oct (44) | Active | Junior Make Debut 1st, Wakagoma S. 1st, Satsuki Sho 1st, Japanese Derby 4th |
| 3 | Maruzensky ([Formula R]) | Senior Year Finale (75) | Completed | Junior Make Debut 1st, Asahi Hai F.S. 1st, Spring S. 1st, Satsuki Sho 1st, Japanese Derby 4th, Arima Kinen 12th, Osaka Hai 1st, Yasuda Kinen 1st, Tenno Sho (Autumn) 1st, URA Qualifier 1st, URA Semifinal 1st, URA Final 1st |
| 4 | Maruzensky ([Formula R]) | Senior Year Finale (75) | Completed | Junior Make Debut 2nd, Asahi Hai F.S. 3rd, Spring S. 1st, Satsuki Sho 1st, Japanese Derby 4th, Arima Kinen 7th, Osaka Hai 1st, Yasuda Kinen 1st, Tenno Sho (Autumn) 1st, URA Qualifier 1st, URA Semifinal 2nd, URA Final DNF (Skipped) |
| 5 | Agnes Tachyon ([tach-nology]) | Classic Early Apr (31) | Active | Junior Make Debut 1st, Yayoi Sho 1st, Satsuki Sho 1st |
| 6 | Agnes Tachyon ([tach-nology]) | Senior Late Mar (54) | Active | Junior Make Debut 6th, Yayoi Sho 5th, Satsuki Sho 5th, Japanese Derby 1st, Kikuka Sho 3rd |
| 7 | El Condor Pasa ([El☆Número 1]) | Classic Early May (33) | Active | Junior Make Debut 1st, Kyodo News Hai 5th, NHK Mile C. 2nd |
| 8 | Mayano Top Gun ([Scramble☆Zone]) | Classic Late Oct (44) | Active | Junior Make Debut 1st, Kikuka Sho 3rd |
| 9 | Mayano Top Gun ([Scramble☆Zone]) | Senior Early Apr (55) | Active | Junior Make Debut 1st, Kikuka Sho 3rd, Arima Kinen 3rd, Hanshin Daishoten 2nd |
| 10 | Mayano Top Gun ([Scramble☆Zone]) | Senior Late Dec, Arima Kinen (72) | Completed (Arima DNF) | Junior Make Debut 1st, Kikuka Sho 3rd, Arima Kinen DNF |
| 11 | Haru Urara ([Bestest Prize ♪]) | Senior Year URA Qualifier race day (73) | Active | Junior Make Debut 2nd, URA Qualifier Entered |
| 12 | Haru Urara ([Bestest Prize ♪]) | Senior Early Nov (69) | Completed (Arima DNF) | Junior Make Debut 2nd, Negishi S. 1st, February S. 2nd, Elm S. 1st, JBC Sprint 4th, Arima Kinen DNF |
| 13 | Tokai Teio ([Beyond Horizon]) | Senior Late Apr, Tenno Sho (Spring) (56) | Completed (Japan C. & Arima DNF) | Junior Make Debut 1st, Wakagoma S. 1st, Satsuki Sho 3rd, Japanese Derby 1st, Kikuka Sho 1st, Tenno Sho (Spring) 4th, Japan C. DNF, Arima Kinen DNF |
| 14 | Daiwa Scarlet ([Peak Blue]) | Senior Year Finale (75) | Completed | URA Final 2nd |
| 15 | Vodka ([Wild Top Gear]) | Senior Year Finale (75) | Completed | URA Final 1st |
| 16 | Vodka ([Wild Top Gear]) | Senior Year Finale (75) | Completed | URA Final 1st |
| 17 | Biwa Hayahide ([pf. Winning Equation…]) | Senior Late Apr, Tenno Sho (Spring) (56) | Active | (no race result; goals panel records GOAL TOP 3, RACE DAY? YES) |

Every run stores one `turn_entries` row holding the snapshot roster line (exp, SP, energy,
condition, mood), selected skills, and a `notes` column with the tracker's goal history.
Trainer-sourced data: growth, aptitude, and remaining goals are only ever documented;
they are not schema columns.

`career_position` and `career_position_source = 'imported'` are populated from the
Career Stage header so Cockpit and Timeline read a named position rather than deriving
one from `max(turn)`.

## Skill mapping rules

- Tier names carry their catalog tier in the display (◎ best, ○ normal, × practice). The
  tracker renders tiers as ○ and ⦾; ⦾ resolves to the ◎ row and falls back to × when the
  skill has no ◎ row (Corner Adept, Corner Acceleration).
- `run_skills.status` is `Acquired` for a checked row, `Suggested` for a blank row;
  `turn_acquired` stays null (the tracker does not record acquisition turns).
- Out-of-band resolutions (tracker row names a skill or tier the catalog cannot match directly):
  - Run 7: bare `140` with no skill name -> resolve against catalogue.
  - Run 8: bare `117` with no skill name -> resolve against catalogue.
  - Run 12: `∴ Win Q.E.D.` naming (unique skill, resolves to Lvl 1 in catalogue).
  - Run 15: "Front Runner Straightaways" lost its glyph (cost 117 = the ○ tier) -> id 300.
  - Run 16: "Front Runner Savvy" lost the space before its ○ (cost 99 = the ○ tier) -> id 352.
  - Run 16: "Remove Wet Conditions x N/A ✅" names a skill with no catalog row; left unattached.
  - Run 17: `Wet Conditions (())` mangled tier glyph -> resolve against catalogue.
  - Run 17: truncated card name `[pf. Winning Equation…]` -> resolve against catalogue as `Biwa Hayahide [pf. Winning Equation]`.

## What is deliberately omitted

- **Deck loadouts, growth plans, aptitudes.** The tracker does not record them per run in
  a structural form, and no schema column exists. They live in each run's `notes`.
- **Earn Fans targets.** Run 11 shows the three house targets done with no party/team
  table in the tracker; recorded in `notes` only.
- **Race days without a resolved result.** Runs 1-4 and 11-13 record non-URA races in the
  goals panel (Derby, Arima, JBC Sprint and so on); only race days the tracker actually
  resolves produce a `race_entries` row. Runs 14-16 record only the terminal URA F result.
- **The run 1 contradiction.** The tracker's plan box says "9 turns" remain before
  Takarazuka while the goals panel already records it won; `turn` was kept at 48 per the
  CLASSIC LATE DEC header. Flagged in `notes`, not smoothed.
- **Plan-versus-goals contradictions (runs 1, 10, 13).** Tracker headers record a calendar
  position; the goal panel's race-day results sometimes disagree. Header wins and is flagged.
- **Goal rows with only a goal (no result).** The tracker's rule: a goal row carrying two
  numbers is goal + result; a row with one number is an unresolved goal. Only resolved
  results produce `race_entries`.
- **Aptitude drift.** The same trainee can show different aptitude grades across runs
  (e.g., Tokai Teio run 2 Dirt G vs run 13 Dirt D); imported as documented, not unified.
- **1200-base growth rates.** Tracker prints growth against a 1200 base while URA 2026
  uses 1400; the cross-check fails for three repeated pairs (El Condor Pasa, Tokai Teio,
  Haru Urara). Growth rows are recorded in `notes`; the catalogue's `growth_rates` is the
  source of truth.
- **The support-card catalogue (PDF pages 1-13).** Not transcribed; a separate source for
  card typing (Pal, Group, failure protection) that the application catalogue can check
  against.
- **The two blank template forms (PDF pages 14-16 and 74-82).** Field vocabulary only.

## Provenance

The original PDF is 82 pages: pages 1-13 are the support-card catalogue, pages 17-73 are
the seventeen runs, and pages 14-16 plus 74-82 are two blank template forms (one pre-filled
URA goals panel). The PDF should be stored under `docs/imports/` so the extraction can be
re-derived; today the corpus's evidence chain ends at markdown nobody can check against.

Truncations (`CLASSI…` on run 5, `[pf. Winning Equation…]` on run 17) and stale headers
(runs 10, 13) are defects in the source tracker form, not in the transcription. The
transcription itself is sound: every one of seventeen runs' goal rows matches the PDF row
for row; differences are line wrapping only. The only genuine extraction losses are one
value row (run 13's condition/mood/energy) and run 17's missing month.

## Verification

```bash
php artisan test --compact --filter=UraFinaleRunsSeederTest
php artisan db:seed --class=UraFinaleRunsSeeder   # second run adds nothing
php artisan tinker --execute="TrainingRun::where('import_source','like','docs/Umamusume\_Progress\_Tracker\_Runs.md (Run %')->count()"  # expects 17
```

Plus a manual pass: each imported run's Cockpit shows its named position rather than a
turn-derived one, the three new Completed runs appear in the Veterans library, the
duplicate-character trainees can both be selected as parents, and every run's result list
matches the table above.