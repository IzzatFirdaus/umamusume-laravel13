# ADR-0032: Independent Training is an overlay row on a run, not a second run shape

<!-- ADR-CONTRACT
  decision: proposed — the idle mode is stored as one independent_training_runs row overlaying an ordinary training_runs row, carrying the three inputs fixed at start, the fifty-minute window and the two endings
  code: built — migrations 2026_10_28_000000_create_independent_training_runs_table, 2026_10_28_000100_add_result_to_independent_training_runs (both with down()); IndependentTrainingRun, IndependentTrainingPolicy, IndependentTraining\Lifecycle, IndependentTraining\Simulator, CompleteIndependentTraining; tests/Feature/IndependentTraining{Schema,Lifecycle,Simulator,Store,Dashboard}Test.php
  superseded-by: —
  amended-by: —
  open: no PRD requirement covers the mode (the migration flags it to the owner in its own docblock), so its authorisation is unrecorded; the Preflight entry controls were cut at ae16ec88 (2026-10-28) and never replaced, so Lifecycle::start() now has no reachable door and the mode survives as a stored state the Dashboard, Cockpit and Result screens render; the completion payload is a calibrated approximation because the per-turn algorithm is unpublished
-->

Status: **Proposed.** Drafted 2026-11-18 from shipped code; the schema landed at `c50eccc3` (2026-10-28) and the
wizard door was cut the same day. **Nothing here binds until the owner records it as Accepted**, and the two gaps
below are the reason the owner's ruling matters more than usual.

## Context

`UMAMUSUME_REFERENCE.md` §1.1.7 documents a mode the career cockpit cannot express: the Trainer fixes three
things, closes the app, and the run plays itself out over a fixed window. The three inputs are a training policy,
an optional custom race rotation (null meaning the scenario's own target races) and up to ten priority skill
hints. Two endings exist: the window completes, or the Trainer gives up.

`PRD.md` contains no story for this mode. The closest anchor is US-10 (race calendar and fan gating), which is a
weak fit at best. The migration's docblock says so plainly and flags it to the owner rather than inventing a
requirement.

## Decision

**The mode is an overlay, not a parallel run type.** One `independent_training_runs` row belongs to one
`training_runs` row, which keeps carrying the trainee, deck and scenario. `TrainingRun` exposes the overlay as a
`hasOne` and answers `isIndependent()` and `isInProgress()` from it, so the cockpit, the dashboard and the
preflight branch on one relation instead of on a duplicated run shape.

- **Only mode-specific facts are stored here**: the policy, the rotation, the priority skills, the window
  (`started_at` to `completes_at`) and the two endings. Everything a career also has stays on the run.
- **`policy` is a string validated at the model boundary.** SQLite has no `CHECK` through the schema builder, so
  an enum column would be a varchar with no constraint; the real work is done by the `IndependentTrainingPolicy`
  cast, matching the `scenario_slots.kind` precedent.
- **The window is closed by a scheduled tick**, not a browser timer, because the run is meant to finish with the
  app closed. `CompleteIndependentTraining` drives completion.
- **One session at a time.** `Lifecycle::canStart()` refuses a second start while any window is open, which is the
  client's own rule rather than a policy invented here.
- **No entry control ships.** `Preflight.vue` cut the mode switch and the policy radios at `ae16ec88` (Phase E2), and
  `career-preflight-indep.spec.ts` pins that absence from the walk that first asked for it. `StartCareerRequest`
  keeps `independentConfig()` nullable, so the write path is alive and a body carrying the mode still starts one;
  what is missing is a control a Trainer can press. The mode is therefore a stored state every reading surface
  renders, not a door this tree offers.
- **The structural limits are not stored.** A win inside the mode awards no trophy and unlocks no gallery or
  concert content, and skills that evolve off rest counts do not advance. Those are absences, so no column holds
  them; a column would imply the tool could record a trophy it cannot know about.
- **The completion payload is stored, not recomputed.** The per-turn algorithm is unpublished, so `Simulator` is
  a calibrated approximation and `result` keeps what it produced, so the completion screen reads one stored
  answer instead of re-running an approximation behind the Trainer's back.

## What this does not license

- **No race outcome, no win probability, no race-day snapshot.** `PRD.md` §6.11 stands unchanged. The result
  payload reports the mode's own stat and fan output; it may not model a race result, and a future simulator
  change that does is a §6.11 violation wearing this ADR's number.
- **No second scheduler, queue or daemon.** Completion rides the existing console command.
- **No requirement invented here.** This ADR records a built shape; it does not create the PRD story the mode
  needs, and a reader must not cite it as that story.

## Links

- `PRD.md` US-10 (closest anchor, explicitly insufficient), §6.11; `docs/UMAMUSUME_REFERENCE.md` §1.1.7, §1.6.8
- `IndependentTrainingRun::DURATION_MINUTES`, `MAX_PRIORITY_SKILLS`; `App\Enums\IndependentTrainingPolicy`
- `app/Services/IndependentTraining/Lifecycle.php`, `Simulator.php`; `app/Console/Commands/CompleteIndependentTraining.php`
- `routes/web.php` (`runs.indep.abandon`); `Career/Result.vue` (the completion block), `Career/Preflight.vue`
  (the cut controls and the comment recording the cut)
- `tests/browser/career-preflight-indep.spec.ts`, `tests/browser/career-result-indep.spec.ts`,
  `tests/browser/dashboard-indep-timer.spec.ts`
- `ADR-0020` (the program this mode sits beside, which does not enumerate it)

## Ledger

- 2026-11-18 · **drafted** — five feature test files and two migrations shipped between 2026-10-28 and 2026-11-03
  with no decision record. The PRD gap is the migration's own finding, quoted rather than resolved here. The second
  gap was measured while drafting: the Preflight entry controls were cut on the schema's own landing day and no
  other surface replaced them, which leaves `Lifecycle::start()` reachable only from `StartCareerRequest`'s
  `independentConfig()` and not from any control a Trainer can press. Both are recorded in `open` rather than
  resolved, because either one may be the owner's answer to the other.
