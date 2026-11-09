# `S-RS-SPAN-1` + `S-RS-REMODEL-B1` + `S-RS-KI154-1` + `S-A-P01-OG-1` — top-level summary

**Date:** 2026-11-10. **Start HEAD:** `6493326a`. **End HEAD:** `28d9afbf`.

Six commits, one code fix, one code fix followed by its register closure, two research artifacts, one
proposal, one register update, and one workstream that stopped on its own condition.

| # | Commit | What |
|---|---|---|
| 1 | `755c6af3` | `docs(research): KI-154 scenario widget sweep` |
| 2 | `83d7df29` | `docs(research): OGC goal_source resolution` |
| 3 | `2845d82c` | `docs(proposals): close S-A-P01.OG presumption` |
| 4 | `5e39a1f0` | `fix(scenario-detail): source Spirit Bursts widget from run data (KI-154)` |
| 5 | `50e0d9fd` | `docs(hfw): draw Spirit Bursts 11; correct row-3 span staleness` |
| 6 | `2d5553d1` | `docs(known-issues): mark KI-154 resolved; file KI-157` |
| 7 | `28d9afbf` | `docs(proposals): scenario detail consolidation plan (A.1)` |

---

## One-line status

| Workstream | Outcome |
|---|---|
| **C** `S-A-P01-OG-1` | **Complete.** OGC's `goal_source` is `trainee_goals`; it already was. No config change needed — the stale artifact was a code comment, now corrected and the presumption closed in the proposal. |
| **B** `S-RS-KI154-1` | **Complete.** KI-154 fixed at `5e39a1f0`; HFW draws `11` and its row-3 spans now match the app; KI-154 closed, KI-157 filed for the adjacent bare `N/A`. |
| **A** `S-RS-REMODEL-B1` | **Plan written, A.2 did not run.** Stop condition fired: Moves 1 and 4 need Ruling 3 (panel priority). |

## Gates, run once over the whole chain

| Gate | Command | Result |
|---|---|---|
| Tests | `vendor/bin/pest --no-coverage --compact` | **2302 passed, 3 failed** (36229 assertions). The three are the `142bb171` baseline (`ScenarioAdvisorFinaleTest`, `UiHygieneTest`, `UraPanelTest`). **+2 passed** vs the pre-dispatch 2300 — the two new KI-154 cases. |
| PHPStan | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | `[OK] No errors` |
| Lint | `npm run lint:ui` | **500 findings** = baseline, **delta 0**; 0 in any changed file |
| Lore docs | `php tools/lore.php` | **577 hits, 75 exempt** — delta **+1** from 576. Source located and ruled: `VERIFICATION.md:20`, the word "pairing" in a facility-ladder row, class 2 (ordinary-word substring), added by `50e0d9fd`. Not edited; the ruling is the obligation. |
| Lore code | `php tools/lore.php code` | **318 hits** — delta **0** |
| TypeScript | `npm run typecheck` | clean |
| Markdown | `python tools/md_fence_check.py` | `0 invalid closing fence(s)` on every edited file |
| HFW links | `node …/frontend-v2-wireframes/link-check.mjs` | `checked 1547 references across 56 pages` / `no broken references` |
| Pint | `vendor/bin/pint --test --format agent <paths>` | `{"tool":"pint","result":"passed"}` |

**The false-green trap did not recur.** The suite's verdict is read from its own summary line
(`Tests: 3 failed, 2302 passed`), not from the wrapper's exit code — the background notification for
this same log reported `exit code 0` while the log says `EXIT=1`, which is the pattern recorded in
`references/verification-patterns.md` this session.

## Non-vacuity, both fixes

| Fix | Before | After | Proof |
|---|---|---|---|
| Spirit Bursts widget | `spirit_bursts: null` beside `combined_bursts: 11` | `spirit_bursts: 11` | Stashed the fix → the new case fails at the fixed line; restored → 9 passed (230 assertions) |
| Row-3 span (`6493326a`, prior dispatch) | row 3 used 8/12 tracks, **341 px** unused | 12/12, **12 px** (the column gap) | All five other rows unchanged before/after; 320/764/1024/1280 tiers all close with no overflow |

## Stop conditions, all workstreams

| Condition | Status |
|---|---|
| C.1 corpus vs web disagree on `trainee_goals` | **Not fired** — both agree |
| B.1.2 no run-record field feeds Spirit Bursts | **Not fired** — two columns, populated on run 7 |
| B fix alters `UiHygieneTest`'s failure | **Not fired** — that test fails on `TeamPanelBuilder.php:620`, untouched |
| A.1 needs Ruling 3 panel priority | **FIRED** — Moves 1 and 4; A.2 withheld |
| A.1 capsule decision unresolved for the merged case | **Not fired** — stated in plan §3 |

## What the register gained

- **KI-154** — RESOLVED at `5e39a1f0`, with the original report preserved beneath the closure.
- **KI-155** — the `+30` line attributes the figure to team rank; corpus says league placement. Open.
- **KI-156** — the bands caveat cites a `15/15/20/20` publisher figure the corpus does not carry. Open.
- **KI-157** — `UnityCupPanel.vue:324` prints a bare `N/A` instead of the shared absence pattern. Open.

KI-155/156/157 were **not** fixed: the dispatch lists all three as out of scope, and each needs its
own slice.

## What is still uncommitted, and why

| Path | Owner | Why not committed here |
|---|---|---|
| `SCREEN_SPEC.md` | earlier F-01 slice | Peer-set file; belongs with that slice |
| `app/Services/Career/TeamPanelBuilder.php` | earlier F-02 slice **+** this dispatch's C comment | Carries both; committing would sweep the alert-vocabulary work into a docs commit |
| `resources/js/components/scenario/ScenarioPanel.vue` | earlier F-01 slice | Same |
| `resources/js/pages/Career/Cockpit/Panels/CareerGoalsPanel.vue` | earlier F-01 slice | Same |
| `tests/Feature/CockpitAlertScopeTest.php`, `TraineeGoalsPanelTest.php`, `tests/browser/scenario-panel.spec.ts` | earlier slices | Same |
| Untracked audit/research/proposal docs from the earlier arc | earlier slices | Out of this dispatch's scope |

**This is the one ordering debt the chain leaves**: the F-01/F-02 work from the earlier arc is still
uncommitted, and the consolidation plan's Moves 2 and 4 both touch `ScenarioPanel.vue`. Landing F-01
first is a precondition of either, and the plan says so (§5).

## Next move

Nothing here is blocked on further work, and nothing further should proceed without an owner ruling:

1. **Ruling 3 (panel priority)** — needed for consolidation Moves 1 and 4.
2. **Ruling 1 (capsule chrome)** — still unanswered; the plan only overrides it for the merged case.
3. **Cleared to run Move 3 alone** (merge Spirit Bursts into Burst bands) — no ruling fork, clean
   files, a measured 236 px of slack behind it.
4. **The three content corrections** (KI-155/156/157) are each their own slice.
