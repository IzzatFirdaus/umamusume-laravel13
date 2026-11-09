# `S-RS-REMODEL-B1` (Workstream A) — report

**Base HEAD:** `5e39a1f0`. **Deliverable:** `docs/proposals/2026-11-10-scenario-detail-remodel.md`
(commit `28d9afbf`).

**Outcome: A.1 produced the plan and stopped at a named fork. A.2 did not run.** This is the
dispatch's own stop condition firing, not a failure to plan.

---

## 1. Stop-condition status — **FIRED**

A.1's conditions:

> - If plan requires panel-priority ordering (Ruling 3) → stop plan, owner rules, A.2.
> - If plan requires capsule decision isn't cleanly resolved → stop plan, owner rules.

**The first fired, twice.** Two of the four priced moves need a panel-priority answer that Ruling 2
(consolidate) does not supply:

| Move | Why it needs Ruling 3 |
|---|---|
| **1** — reflow regroup | Deciding "team block contiguous, run-state after" *is* a priority claim: it says team outranks run state on a page titled **Scenario detail**. |
| **4** — absorb the Team Race banner into an identity card | The destination does not exist as a named object. `GRID-REVIEW.md` measured that the last card is a **reference** block, not the identity, and the page's real identity is the `h1` + lede + stepper above the grid. Creating an identity card decides the same ranking question in another form. |

**The second did not fire**: the capsule treatment for the merged case is stated in the plan (§3):
preserve the surviving panel's own header, one header per merged card, and the roster becomes a
labelled sub-block. That is a plan decision, explicitly scoped to the merged case only, and it does
not rule for the page.

## 2. What the plan contains

`docs/proposals/2026-11-10-scenario-detail-remodel.md`, 186 lines:

- §1 the five moves priced against the tree, with each file's **dirty state measured** — because a
  move whose file carries uncommitted work cannot be committed by pathspec without sweeping it.
- §2 move-by-move specification (before/after panel lists, spans, exact files).
- §3 the capsule treatment for the merged case (stated, not deferred).
- §4 the three content corrections named and kept out of consolidation commits.
- §5 the uncommitted F-01 work two moves depend on.
- §6 what A.2 can run if cleared.

## 3. The finding the dispatch did not anticipate

**Two of the four moves touch a file that carries this session's own uncommitted work.**
`resources/js/components/scenario/ScenarioPanel.vue` is DIRTY (10+/2−) with the F-01 guard split.
Moves 2 and 4 both modify it. Committing either with a pathspec would carry the F-01 fix into a
consolidation commit — the same hazard the commit rule exists to prevent, except the "peer" here is
earlier work in this same session.

This is not a blocker, it is an ordering fact: **land F-01 as its own commit first**, then the two
moves have a clean file. Recorded in the plan (§5) rather than silently swept.

## 4. Why A.2 did not run at all

The dispatch authorizes A.2 "conditional on A.1 not hitting a stop condition." A.1 hit one. Moves 2
and 3 would individually be runnable — Move 3 has no ruling fork at all and its files are clean —
but the dispatch also says the plan must not proceed on a consolidation move that needs a ruling,
and Move 1 is the frame the other moves are arranged around. Implementing Move 3 alone under a
stopped plan would be a partial consolidation the owner never reviewed as a set.

**Recommendation offered in the plan (§6): run Move 3 only, on clearance.** It is the move with a
measured defect behind it (P4's 236 px of slack), no ruling fork, and clean files.

## 5. Content corrections — named, not implemented

| Item | Site | Register |
|---|---|---|
| `+30` attribution | `UnityCupPanel.vue:128-131` | KI-155 |
| `15/15/20/20` caveat | `UnityCupPanel.vue:370` | KI-156 |
| Bare `N/A` | `UnityCupPanel.vue:324` | KI-157 |

All three remain **unfixed** and uncommitted, exactly as the dispatch's out-of-scope list requires.

## 6. Gates

No code changed in this workstream, so the suite, PHPStan, typecheck and lint were run once at the
end of the chain and reported in the top-level summary. Markdown fences:
`0 invalid closing fence(s)` on the new plan document.

## 7. Peer paths

`git show --stat 28d9afbf` names one file: the new plan document. No peer path touched.
