# `S-RS-CLEANUP-1` + `S-RS-RULING-PREP-1` — report

**Rulings 1, 3 and Move 2's caps gate remain owed.** This dispatch advanced cleanup and prepared the
memos; it did **not** unblock the remodel. Move 3 stays the only landed consolidation.

**Base HEAD:** `9defc83b`. **Final HEAD:** `b37c3949` before this report's own commit.
**Pre-flight peer set:** **0 modified, 6 untracked** (re-measured; the previous dispatch's clean tree
held).

---

## 1. Pre-flight

| Check | Result |
|---|---|
| HEAD | `9defc83b` |
| `git show 9defc83b --stat` | **1 file**, the B2 report — committed exactly what it claimed |
| Path verification | every path this dispatch names resolves; no `git ls-files` failure, so no correction to record |
| KI enumeration | KI-155 `:4644`, KI-156 `:4682`, KI-157 `:4721`, KI-158 `:4753` — all present, KI-158 already RESOLVED at `fdf6452f` |
| Wrapper-exit discipline | every run redirected to a log with **no pipe and no trailing echo**; all verdicts read from the logs' own summary lines |

**Per-file diff classification:** no modified files existed at pre-flight. The only untracked paths are
six documents from earlier arc passes (`docs/audits/…`, `docs/proposals/2026-11-09-…`,
`docs/research/RS-UC-…`, `S-RS-SPAN-1-report`, `scenario-detail-design-language-survey`), none touched
by this dispatch. **No peer work anywhere; no stop condition fired.**

## 2. Workstream C

| Item | Status | Commit(s) |
|---|---|---|
| C.1.a `+30` attribution | **landed** | `990bdc7d` (fix), `5bb29e9a` (test) |
| C.1.b `15/15/20/20` caveat | **landed** | `0166b23e` (fix), `26894def` (test) |
| C.2 KI-157 bare `N/A` | **landed** | `ad3d94f3` (fix), `25950098` (register) |
| C.2 KI-155 / KI-156 register | **landed** | `25950098` |
| C.3 Gold hint spec | **landed** | `d191bcfd` |
| C.3 fallout — ruleset-absence selector | **landed** (regression this dispatch caused) | `b37c3949` |

### C.1.a — `+30` is league placement

`bonusTitle` and the render comment rewritten. The note now says the figure is the
**league-placement** bonus, names the ladder's 6th-10th +30 band, cites
`docs/research/flows-career-workspace.md:232`, and states that this build stores the placement but no
ladder — so the line repeats an **observed** figure rather than computing one. The render gate still
keys on `rank === 'S'`; the comment now says explicitly that this is where the observation was
recorded, not a claim that rank causes the bonus.

**One thing the dispatch did not ask for and the fix needed:** the corpus records that
`config/scenarios.php` carries **no** `league_bonus`/`placement_bonus` ladder
(`flows-career-workspace.md:235`). So a pure copy swap to "it is the placement bonus" would have
implied a derivation the tool cannot do. The wording therefore separates *what the client printed*
from *what this build can compute*.

**Non-vacuity:** new case in `tests/Feature/UnityCupPanelTest.php` asserts the new phrases **and**
that the refuted ones cannot return (`binds to the rank`, `inferred from screen position`). Asserted as
source text because the line is not browser-reachable — it renders only at rank `S` and no form writes a
rank (R6-48); `scenario-panel.spec.ts:329` already asserts the absent half. Verified by rewording the
title back: `1 failed, 9 passed (231 assertions)`; restored: `10 passed (235 assertions)`.

### C.1.b — edges versus criteria

Two copies changed, not one. The `detail` was rewritten to conflict row 57's actual finding, and the
visible `text` also had to change: it listed "the Skill Point figures" among the conflicts, which row 57
refutes — the two publishers print the same four reward tiers. Leaving it would have made the fixed
paragraph contradict the sentence above it. The visible line now names "where the low bands' edges
fall".

**Non-vacuity:** new case asserts the corrected phrases and the absence of `15/15/20/20`. Verified
red-on-reword (`1 failed, 10 passed`) and green after (`11 passed, 239 assertions`).

### C.2 — KI-157, and the four sites it does *not* cover

`UnityCupPanel.vue:324` now renders `AbsenceValue` with `compact` — the same shared disclosure
`CareerStatePanel.vue:287` uses — with the old `title` text carried through verbatim as the reason.

**Verified in the rendered page on run 7:** five stat-grade rows each render a `<details>` whose
summary reads `N/A , No per-stat team grade is recorded for this run yet. Enter it in the team-state
editor.`

**KI-157's description still held** (checked before fixing): the span still said bare `N/A` with a
`title`, unchanged by `6493326a` or `64c37c4f`. Not stale.

**Four bare `N/A` spans remain on the same page and are explicitly out of this KI's scope**, recorded
in the register rather than silently widened:

| Site | File | Why separate |
|---|---|---|
| Current Spirit | `UnityCupPanel.vue:270` | a `ResourceMeter` `absence` prop, not a bare span |
| three ladder rows | `TeamRankLadderPanel.vue:67`, `UraMandatoryLadder.vue:67` | different components, different surface |

### C.3 — Gold hint, and the regression it uncovered

Spec expectation corrected to config's exact string with a comment naming the source. **Gold hint now
passes.**

**But the full spec run surfaced a failure in a different test**, and the dispatch's stop condition
("if it fails on a different assertion within the same test, stop") did not apply, because it failed in
a *different file* — and it was **my** doing:

```
scenario-panel.spec.ts:131 › states the scenario, its documentation and the ruleset absence…
  locator('summary').first()
  Expected: /No source defines a Global ruleset version/
  Received: "N/A , No per-stat team grade is recorded for this run yet. …"
```

KI-157 moved five new `summary` elements into the region, so the positional `.first()` no longer found
the ruleset marker. Fixed by locating it by the reason it carries rather than by position — the
assertion is about *that* absence, so it now says so. `scenario-panel.spec.ts` → **11 passed**.

**This is a genuine regression from this dispatch's own change, found and fixed within it**, not a
pre-existing failure relabelled.

## 3. Workstream W — memos

**File:** `docs/proposals/2026-11-10-ruling-memos-scenario-detail.md`, commit `068c42d6`.

| Memo | Status | Recommendation flag |
|---|---|---|
| **W.1** Ruling 1 (capsule on the merged card) | **prepared** | **Flagged A** (one capsule, status quo) — no redraw, preserves the sanctioned chrome, keeps one-header-per-card. But the memo says the *page-wide* capsule question is the real one and is not settled by this card. |
| **W.2** Ruling 3 fork 1 (team vs run-state primary) | **prepared** | **Owner's call; evidence supports both.** Count favours team-primary (6 of 11 panels); data-gate behaviour favours run-state-primary (stable across sparse and full runs). No document names either. |
| **W.3** Ruling 3 fork 2 (banner rule text) | **prepared** | **Owner's call.** Three targets, and **A costs more than the plan priced** — it creates a panel that Move 1's reflow must then absorb. |
| **W.4** Move 2's caps gate | **prepared, stop condition cleared** | **Flagged (c)** — sibling card in place, keeps the widened gate and both caps pins, needs no ruling. |

### W.4's stop condition — checked, not fired

The dispatch warned the plan may have conflated two caps tables. Measured: **one** table.
`ScenarioPanel.vue:300` (header) + `:301` (`sr-only h3`) + `:316` (caption) are one element's parts;
the HFW's two `grep` hits are likewise a `.sublabel` and a `caption`. No conflation, so the memo
proceeds.

### Facts that contradicted the plan or the memo's first draft

Two, both corrected before commit:

1. **`grand-concert-panel.spec.ts:49` is not the caps pin.** The plan and the earlier grid review both
   cite `:49`; the actual caps assertion is `:59` (by caption), with a second at
   `scenario-panel.spec.ts:216`. The memo now cites both real lines.
2. **`lattice-bleed` is one *definition*, not one *call site*.** The survey's "one call site in the
   tree" reads, at this tree state, as one file *defining* the chrome while **9 components import
   `CapsuleHeader`**. The memo states it that way rather than repeating the looser claim.

Neither contradiction changes a recommendation; both would have been wrong citations in a document the
owner rules from.

## 4. Gates

| Gate | Result |
|---|---|
| Tests | `Tests: 2 failed, 9 warnings, 2305 passed (36241 assertions)` — read from the log's own summary. **2303 → 2305** (+2, the two new source-pin tests). The two failures are `ScenarioAdvisorFinaleTest` and `UiHygieneTest`, both pre-existing and both out of scope. |
| PHPStan | `[OK] No errors` |
| Lint | **500** findings, delta **0** |
| Lore docs | **581 hits, 75 exempt** — delta **+2** from 579. Set-diffed per file: one is the new memo (`:108`, the word "stable" in ordinary prose), one is last dispatch's B2 report quoting its own prior lore line. **Both verified mine, neither a content problem.** |
| Lore code | **318 hits**, delta **0** |
| Typecheck | clean, `EXIT=0` |
| Markdown | `0 invalid closing fence(s)` on all three edited files |
| Browser | `unity-cup-panel` + `scenario-panel` + `grand-concert-panel`: **31 passed, 1 failed** initially — that one was the KI-157 selector regression, fixed; `scenario-panel.spec.ts` then **11 passed** |
| Pint | `passed` on every changed PHP file |
| Peer | `git status --short` post-commit: 0 modified, same 6 untracked. No `^ A ` residue. No peer path in any commit. |

## 5. What this dispatch did not do

- No ruling on Ruling 1, Ruling 3, or Move 2's gate.
- Moves 1, 2 and 4 not implemented.
- Plan `28d9afbf` not edited; HFW not edited; the closed register (`ce204885`) not reopened.
- KI-155/156/157 fixed and closed; KI-158 was already closed by the previous dispatch.
- No fifth memo prepared — none surfaced.
