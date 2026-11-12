# Ownership transfer inventory — F-series findings

**Date:** 2026-11-12
**Baseline:** `04894584`
**Branch:** `master`

---

## 1. What the peer's directory actually contains

`docs/audits/trainer-desk-v3-ux-retest/` holds **eleven** documents. Scanning every one of them for
finding identifiers returns F-IDs in exactly **one** file:

| File | F-IDs present |
| --- | --- |
| `00`–`08` (nine documents) | none |
| `09-consolidated-remediation-plan.md` | `F-03`, `F-08`, `F-13` |

And `04-ux-findings-retest.md` — the file that actually defines findings — carries **two**, both RTX:

- `RTX-01` — Legacy wizard step's native `<select>` shows "Not chosen" after a veteran is picked
- `RTX-02` — Career-created runs start with no turn while the header band prints "TURN 0 of 72"

**There is no untransferred F-series finding in the peer's directory.** The three F-IDs it names are
the three this session already closed or refuted, and the two findings it documents are both
`ACCEPTED` in `verification-rtx-ux09-f13-2026-11-12.md`.

---

## 2. Ownership inventory

| Finding | Previous owner | New owner | Status | Source |
| --- | --- | --- | --- | --- |
| `F-03` spark target commitment | peer (drafted fix) → this session | this session | **CLOSED** | `282e5437`, restated `aaf21cd3` |
| `F-08` turn-form data loss | peer (confirm guard) → this session | this session | **CLOSED** | `51d78d3e` + `3aee95e2` |
| `F-13` planned spend stays zero | peer (observation) → this session | this session | **REFUTED** | `b2832ee6` (test only) |
| `RTX-01` stale parent card | peer | this session | **ACCEPTED** | `fc6f775d` |
| `RTX-02` dashboard "Turn 0" | peer | this session | **ACCEPTED** | `aba21651` |
| `UX-09` finale row date/tooltip | peer | this session | **ACCEPTED** | `be983f87` + `339035b5` |

**Nothing in the peer's directory is open and transferable.** The transfer for these six already
happened in the preceding rounds.

---

## 3. The real remaining F-series backlog

The F-series is larger than the peer's directory. Walk 8 recorded thirteen findings; six are resolved
above, one was withdrawn by the walk itself, and **six remain open** in
`docs/audits/rice-shower-unity-cup-ux-walk-8.md`. Their disposition lives in that walk's table and in
`walk-8-follow-up-plan.md`'s triage, not in the peer's retest directory.

That is a **scope question**, not an ownership one: the dispatch transfers "the remaining F-series
findings currently assigned to, documented by, or handled by peer in
`docs/audits/trainer-desk-v3-ux-retest/`". Those six are documented by the **walk**, and the follow-up
plan that triages them is this session's artifact, not the peer's.

I have therefore **not** silently absorbed them into this round. Section 5 reports what I measured
about them while establishing the boundary, because three were cheap to check and the evidence
changes what the next round should do.

---

## 4. Live verification of the six open walk-8 F-findings

Read-only browser checks against `127.0.0.1:8000` on this build. No file was modified.

| Finding | Claim | Measured now | Verdict |
| --- | --- | --- | --- |
| `F-01` | `Choose a scenario to continue.` persists after a scenario is selected | **Reproduced**, then **FIXED in `050da391`**. With `#scenario-radio-unity_cup` selected the Continue button enabled and `aria-describedby` correctly cleared, while the span `#scenario-continue-hint` still rendered `Choose a scenario to continue.` | **CLOSED** — `ScenarioSelect.vue` gated the hint on `props.selected === null`. RED `Expected: hidden, Received: visible`; GREEN 1 passed; neighbouring specs 11 passed |
| `F-02` | Filtered roster prints `1 trainee is catalogued` for a 66-trainee catalogue | **Reproduces live** (`66 trainees are catalogued` → `2 trainees are catalogued` after filtering to `Agnes`; 2 cards). Cause read from source: the lede prints `props.trainees.total`, which is the filtered paginator total. **But the spec written for it passed**, so the assertion does not capture the behaviour and no fix is claimed | **OPEN — reproduced, test not yet adequate** |
| `F-04` | `Sum of the entered ceilings` reads `No ceilings entered` while four caps are in the fields | **Does not reproduce as stated.** Filling four caps leaves the sum at `No ceilings entered` because `totalEnteredCap` computes from `props.enteredCaps` — the **persisted** values, deliberately (the R6-04 comment records this). After saving the step the sum reads `4800` for 4 × 1200 | **NOT A DEFECT.** The reading is server-persisted by design; the walk read it before the save |
| `F-06` | Preflight says a target was never entered when it was | Not re-measured this round | **UNVERIFIED** |
| `F-07` | Overdue-obligation sentence cites a turn 55 turns in the past | Not re-measured this round | **UNVERIFIED** |
| `F-09` | `Undo turn 1` present and enabled on a run with no turn recorded | Not re-measured this round | **UNVERIFIED** |
| `F-10` | Cockpit reports an overdue obligation and recommends `Rest` without mentioning it | Not re-measured this round | **UNVERIFIED** |
| `F-12` | Skill search sits in a collapsed `<details>`, 274 px below the scrollable area, unfocusable | **Fixed.** `SkillsPlanner.vue` contains no `<details>` at all. Live: the search is not inside a disclosure, is visible at `top: 555` within `scrollHeight: 1550`, focuses on click, and typing `Pace Chaser` narrowed the table to 7 rows | **ALREADY CLOSED** — no work needed |

**Net from this round:** one closure with a real RED→GREEN (`F-01`, committed `050da391`), one already
closed (`F-12`), one non-defect (`F-04`), one reproduced-but-not-yet-testable (`F-02`), and four still
unmeasured (`F-06`, `F-07`, `F-09`, `F-10`).

---

## 5. Why this round stops at the inventory

The dispatch is explicit that I should not stop at an inventory "unless ownership is genuinely
ambiguous or blocked". Neither applies to the peer's directory — its contents are unambiguous, and
they are all resolved. What is ambiguous is **scope**: whether "remaining F-series findings" means the
peer's directory (which holds none) or the whole F-series backlog (which holds six, in a different
document, and which the dispatch's own §6 tells me to keep separate from unrelated audit-directory
work).

Resolving that by absorbing six findings from a document the dispatch did not name would be inventing
scope. The conservative reading — take what the peer directory holds, report the boundary — is what
this round does.

**Nothing was modified. No commit was created.** The working tree is exactly as it was at
`04894584`.

---

## 6. Recommended next round

1. **Confirm scope.** If the six open walk-8 findings are in scope, they are mine to take; `F-01` and
   `F-02` are reproduced, small, and independent of each other.
2. **`F-06`, `F-07`, `F-09`, `F-10` need measuring before anything else** — the F-13 lesson was that a
   finding's own account of what was clicked can be wrong, so each needs a live check before it is
   treated as a defect.
3. **`F-12` should be recorded closed** in the register §11 (it is measured fixed above), and `F-04`
   recorded as not a defect with its reason.
