# 03 — Coverage hole: the deck builder

**Verdict: the hole could NOT be closed by walking. It is permanent until a seeder changes.**

## What was tested

The dispatch named a second door: if any *completed* URA run carried deck slots, walking it would close the hole.
Three completed `UraFinaleRunsSeeder` runs were opened at 1440x900 and 390x844:

| Run | Status | Route | Slots | Result |
|---|---|---|---|---|
| #15 | Completed, URA Finale | `/training-runs/15/deck` | 6 | all **Not equipped**, 0 equipped |
| #16 | Completed, URA Finale | `/training-runs/16/deck` | 6 | all **Not equipped**, 0 equipped |
| #22 | Completed, URA Finale | `/training-runs/22/deck` | 6 | all **Not equipped**, 0 equipped |

Observed on the rendered page, both viewports, 6 screenshots. Every slot prints `Not equipped.` with
`Ownership N/A : No ownership has been recorded for this slot yet.`

## Confirmed independently of the screen

`deck_slots` holds **0 rows for every run** — the table is globally empty. So this is not one seeder's omission:
no seeder in the set (`RosyDreamsRiceShowerSeeder`, `UraFinaleRunsSeeder`, `TrackerVeteransSeeder`) writes a deck
slot for any run, and `ImportHistoricalRun` writes none by design (EX-16).

## Why this is a coverage hole and not a finding

The deck screen renders empty storage **correctly**. Filing "the deck builder is empty" would be filing a data state
as a defect, which both prior passes correctly refused. What is true is narrower and more useful:

- **Three passes** have now listed the deck builder on coverage and walked it **populated zero times**.
- Every property of the populated builder is **unverified by observation**: six cards in place, the friend slot
  accepting a rentable card, ownership badges flipping between Owned/Rented, the effect summary computing from the
  six cards, and the "Effects apply from the next turn" copy being true of a real change.
- The empty-state copy reads well and says the right things; that is **not** evidence about the populated state, and
  the coverage doc now says so explicitly so a future reader cannot make that inference.

## What closes it

Either:

1. **A seeder change** — `RosyDreamsRiceShowerSeeder` imports §1.4's six cards, or `UraFinaleRunsSeeder` gains deck
   slots for one run. Out of scope for an observation pass; belongs to a Data Engineer slice.
2. **A hand-driven run with a deck**, recorded by someone who can write to the DB, then walked. Also out of scope
   for this instrument.

Until one of those happens the hole stands. It is recorded in `00-coverage.md` under **"Coverage holes surviving more
than one pass"**, not folded into EX-16.

## Ruled out this pass

The hypothesis that a completed run would carry a deck was tested and is false. The dispatch was right to make the
second door conditional; the condition failed.
