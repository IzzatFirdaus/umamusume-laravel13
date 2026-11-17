# 04 — Excluded findings (seeded re-audit)

Prior exclusions re-stated with a current verdict, then new ones.

| ID | Subject | Verdict this pass |
|---|---|---|
| EX-02 | Factory row in the catalogue (BO-003) | **unchanged.** Still a data + content decision. Seeding did not touch the catalogue. |
| EX-03 | BO-005 turn-edit door unreachable | **CLOSED.** With a populated run the door renders (`Edit turn 67`). It was a data-state gap, exactly as recorded. |
| EX-04 | No support-card compare affordance (BO-012) | **unchanged.** Still a scope question. `/veterans/compare` exists and works, so the pattern exists elsewhere. |
| EX-05 | The wizard's third trainee count (66) | **unchanged.** Needs a product definition of what that count is. |
| EX-06 | `eva01` never measured | **unchanged.** Not measured again this pass. |
| EX-07 | Peer-work contamination of the prior baseline | **RESOLVED for this pass.** Peer `playwright test` gone, tree sampled stable twice. The peer worktree still exists and one file in it moved 8.7 min before the walk but not during it; it cannot reach this tree's build. |
| **EX-08** | Vision unavailable: **no visual judgement is possible under this method.** Two consecutive passes, both probes failing identically. | **Reclassified as a property of the method, not a per-pass exclusion** - see the statement below. | - |
| EX-09 | `KNOWN-ISSUES.md` mid-edit, unusable as a census | **unchanged.** Still +131 uncommitted lines. |
| EX-10 | The seeding gap | **CLOSED by this pass** — seeding ran, via the `--class=` invocations, on a copy. The audit did not seed the shared file (no explicit approval), which is the correct behaviour, not the gap. |
| EX-11 | Result-with-data needs `UraFinaleRunsSeeder` / `TrackerVeteransSeeder`, not Rosy Dreams | **CONFIRMED by observation.** Rosy Dreams (#14) is `Active` and its Result screen prints "still running"; `Maruzensky` (#15, from `UraFinaleRunsSeeder`) renders 10 rows of real result data. The correction was right about the remedy and wrong about which seeder provides it — this pass settles it. |

## New exclusions

| ID | Subject | Decision needed | Owner |
|---|---|---|---|
| **EX-12a** | **Walk convention** - private-copy every pass, or seed the dev file once and walk it. Both work; leaving it to per-pass improvisation does not. | **DECIDED 2026-11-15 by the owner: (A) Private-copy is the method.** Every pass backs the dev file up into `.scratch-uma/<session>/`, seeds the copy, serves it on a session-private port; the dev file is never mutated by an audit. Needs no per-pass permission. Full-audit passes additionally follow the owner's quiet-tree ruling (A-rule: signed window for full audits, timestamped-evidence header for narrow follow-ups). | **Owner - RULED** |
| **EX-12b** | **Dev-file state** - the shared DB's own contents, independent of the walk method. This pass held 1 run / 0 veterans. | **DECIDED 2026-11-15 by the owner: seed it once to match the walk dataset.** The dev file was backed up first (`.scratch-uma/browser-obs-followup/dev-db-pre-seed-backup.sqlite`), then seeded to 19 runs / 8 veterans so `:8000` is useful. Standing rule: audits still never mutate it (EX-12a); only an explicit owner instruction seeds it. | **Owner - RULED** |
| **EX-13** | `DB_DATABASE=… php artisan …` from this host's bash silently does **not** set the variable — the seeders report `DONE` while writing nothing | **Tooling/documentation.** This is a real trap for any owner following the documented shell form and deserves a `KNOWN-ISSUES.md` entry. Append KI at next pass. | Docs Writer / Owner |
| **EX-14** | The league-placement bonus is not stored (BO-015's data half) | **Schema/data decision.** Rendering `+30` requires the bonus ladder to exist somewhere. Whether the app should hold it is an Architect call. | Architect |
| **EX-15** | BO-016's countdown contradicts its band (`11 inside 10-12` + `need 2 more`) | **One-line product ruling.** Which figure is the target? The fix is trivial once stated; filing it as code before the ruling would be guessing. | Planner Domain Specialist |
| **EX-16** | The seeder imports no deck and no legacy selection, so the doc's six-card deck and six blue sparks are unrepresented | **Content/seed decision, not a screen defect.** The seeder's own docblock states the omission deliberately. Whether to extend the seeder to import §1.4's deck and §1.5's sparks is a data-coverage call. **This is the real answer to the dispatch's roster/deck question**, and it is not a rendering bug. | Data Engineer / Owner |

## What is *not* excluded

BO-001, BO-002, BO-009, BO-010, BO-008, BO-013, and BO-017 are code slices in `03-code-change-plan.md`, each
touchable without a schema change, an ADR, a content ruling or an external config change.

## Method-property statement (this replaces EX-08 as an exclusion)

**This audit method cannot see.** After two consecutive passes with both vision probes failing identically, that is a
measured property of the instrument rather than a one-off exclusion, and it belongs in every future dispatch.

- **What the method detects, with evidence behind it:** copy defects, state-dependent labels, count contradictions,
  contrast ratios computed from rendered colours, geometry and overflow, target sizes, focus-ring values, console
  errors, tab order, empty-state behaviour, and rendered-vs-reference divergence against a cited document.
- **What it cannot detect at all:** whether a populated screen *reads* well. Density, visual hierarchy, whether a card
  looks raised, whether two surfaces feel like one product, whether the app looks current. Each is a judgement that
  needs eyes on pixels, and no measurement substitutes for it.
- **Consequence for the next dispatch:** either repair the vision endpoint and commission a dedicated **visual** pass
  over the two passes' measured findings, or accept that this instrument is a **measurement** audit and obtain a
  visual review elsewhere. Continuing to run it and reading its clean colour and geometry sections as "the UI is
  fine" would be the actual failure mode.
