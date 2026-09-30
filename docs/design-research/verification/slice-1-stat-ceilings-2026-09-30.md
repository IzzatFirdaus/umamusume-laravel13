# Slice 1 verification — per-scenario stat ceilings

Date 2026-09-30. Tree: `master` at the commit this record lands in. Read-only browser pass
against a scratch database; the shared dev file was never opened.

## Setup

```bash
DB_DATABASE="$PWD/.scratch-uma/verify-slice1.sqlite" php artisan migrate --seed --force
DB_DATABASE="$PWD/.scratch-uma/verify-slice1.sqlite" php artisan serve --host=127.0.0.1 --port=8207
```

Two runs seeded through `php artisan tinker` scripts under `.scratch-uma/`, both discarded:

- run 1 — `scenario = trackblazer`, one turn with Stamina 1900 and Wit 1480
- run 2 — `scenario = null`, one turn at base-cap values

`database/database.sqlite` fingerprinted before and after: **1667072 bytes, Sep 30 19:11**,
identical. No WAL or shm file exists beside it at this time.

## What the browser read

`getComputedStyle`-free DOM reads, real browser, `input[name]` attributes and the run-state
section's rendered text.

### Run 1 — Trackblazer (ceilings should be 1200 / 1900 / 1200 / 1200 / 1500)

| Field | band end | rail `max` | raw form `max` |
|---|---|---|---|
| Speed | 1,200 / 1,200 | 1200 | 1200 |
| Stamina | 1,900 / 1,900 | 1900 | 1900 |
| Power | 1,150 / 1,200 | 1200 | 1200 |
| Guts | 1,000 / 1,200 | 1200 | 1200 |
| Wit | 1,480 / 1,500 | 1500 | 1500 |
| SP | "no cap, no grade" | none | none |

The band's ceiling and both forms' `max` agree on every stat, and SP carries no ceiling in
either place. `ADR-0002`'s UI condition holds: the footer still reads "1,200 is where training
gains halve. The bar end is the scenario ceiling." — two markers, separately drawn.

### Run 2 — no scenario

Band renders `/ 1,400` on all five stats. Both forms carry `max="1200"`, and the validator
rejects 1201. **This is a display disagreement, recorded as a finding, not fixed in this slice.**
See "Defects found" second item.

## Defects found by the browser pass

1. **Every stat input hardcoded `max="1200"` — the server change was unreachable.** Found before
   the view fix, on run 1: Stamina's own `placeholder` read `1900` (the previous turn's value)
   while its `max` read `1200`. A Trainer could not type the number the band showed as this
   scenario's ceiling, because the browser refused it before the request was sent.
   `tests/Feature/ScenarioStatCapsTest.php` could not have caught this: every test there POSTs
   directly, and a POST never consults a native constraint. Fixed in
   `resources/views/runs/show.blade.php`, now pinned by three rendered-attribute tests.

2. **SP carried `max="1200"` in the raw form while the server applied no ceiling.** The two
   halves of one field disagreed. The view's 1200 was a recycled stat cap; SP is not one of the
   five rated stats. Removed from the markup; `ADR-0015` records why the server has no ceiling.

3. **`TrainingRunController::showData()` passes `scenarioKey()` into `x-stat-band`, which its own
   `@props` comment forbids.** The component says a named default was removed precisely because
   "silently resolving to the baseline would rate a trainee against the wrong ceiling" — and the
   controller does the resolving one line earlier, so a run with no scenario is rated against URA
   Finale's 1400 on screen while the form and validator hold it to 1200. Not fixed here: closing
   it changes the band's prop contract or the controller's resolution, and the no-scenario run's
   ceiling display is the owner's call, not a side effect of the bound work.

## Gates on this slice

- `php artisan test --compact` — **869 passed, 2 skipped, 0 failed** (3131 assertions). Baseline
  on the same tree before the slice: 850 passed, 2 skipped, 0 failed. Delta is the 19 new tests.
- One intermediate run reported a single failure in the rendered-attribute test at its `get()`
  call; it did not reproduce on the next run or in the full suite. Cause not established, so it
  is recorded rather than dismissed.
- `vendor/bin/pint --test` on the seven touched files — PASS.
- `vendor/bin/phpstan analyse` (level 6) — no errors.
- Lore grep on every authored file — clean. The only hits in `PRD.md` are pre-existing lines that
  describe the ban itself.
