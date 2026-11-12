# F-series ownership inventory

**Date:** 2026-11-12
**Baseline:** `f192f75a` (F-02 fix)
**Branch:** `master`

Every F-prefixed finding that appears in a relevant source document, including the ones that did not
reproduce and the ones already closed, so nothing is silently dropped.

---

## 1. Classification key

| Class | Meaning |
| --- | --- |
| **Transferred from the peer** | The finding was documented by, or assigned to, `docs/audits/trainer-desk-v3-ux-retest/`. That directory holds `F-03`, `F-08`, `F-13` and no others (verified by scanning all eleven of its documents) |
| **Newly adopted (walk-8 backlog)** | The finding is walk 8's, documented in `docs/audits/rice-shower-unity-cup-ux-walk-8.md` and triaged in `walk-8-follow-up-plan.md`. Not the peer's; adopted in the ownership-transfer round |
| **Previously resolved** | Closed or refuted before the transfer round |
| **Still open / blocked** | Reproduced or unmeasured, with no correction landed |

The distinction matters: nothing in this table was presented as transferred unless the peer's directory
actually carries it. Six of the thirteen are walk-8's own backlog.

---

## 2. The complete table

| # | Finding | Original owner | Current owner | Class | Original claim | Reproduction verdict | Disposition | Evidence | Commit | Remaining work |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | **F-01** | peer retest dir (`09-…plan.md`) / walk 8 | this session | **Transferred** | `Choose a scenario to continue.` persists after a scenario is selected | **Reproduced** — button enabled and `aria-describedby` cleared, hint span still rendered | **CLOSED** | `ownership-transfer-f-series-2026-11-12.md` §4 | `050da391` | none |
| 2 | **F-02** | peer retest dir / walk 8 | this session | **Transferred** | The searched roster prints `1 trainee is catalogued` where the unfiltered roster printed `66` | **Reproduced** — `66 trainees are catalogued.` → `2 trainees are catalogued.` after filtering | **CLOSED** | `career-trainee-catalogue-count.spec.ts` | `f192f75a` | none |
| 3 | **F-03** | peer (drafted fix) | this session | **Transferred** | The spark `Target` combobox accepts and displays typed text without committing it, saving `target: null` | Reproduced; fixed, then restated under the 2026-11-12 owner ruling | **CLOSED** | `verification-rtx-ux09-f13-2026-11-12.md` §B | `282e5437` + `aaf21cd3` | none |
| 4 | **F-04** | walk 8 only | this session (measured) | **Newly adopted** | `Sum of the entered ceilings` reads `No ceilings entered` while four caps are in the fields | **Did not reproduce as stated.** `totalEnteredCap` computes from `props.enteredCaps` — the **persisted** values, deliberately (R6-04 comment). Filling four caps does not move it; **saving does: 4 × 1200 reads `4800`** | **NOT A DEFECT** | `ownership-transfer-f-series-2026-11-12.md` §4 | — | none; the walk read the figure before the save |
| 5 | **F-05** | walk 8 only | unassigned | **Newly adopted** | `Deck 5 of 6 slots filled` above a paragraph that still reads `Not equipped…` | Not measured | **OPEN — unmeasured** | recorded as `EXISTING_ISSUE` → `R6-54` (Does not reproduce) in the register §5 | — | needs a live check before any work |
| 6 | **F-06** | walk 8 only | unassigned | **Newly adopted** | Preflight says a target was never entered when it was | Not measured | **OPEN — unmeasured** | registered as `EXISTING_ISSUE` → `R4-03` (Confirmed defect) | — | needs a live check |
| 7 | **F-07** | walk 8 only | unassigned | **Newly adopted** | `Junior Make Debut is due and not recorded. It fell due on turn 12, 55 turns ago` on a run imported at turn 67 | Not measured | **OPEN — unmeasured** | `EXISTING_ISSUE` → `R7-22` | — | needs a live check |
| 8 | **F-08** | peer (confirm guard) | this session | **Transferred** | Selecting `Race` hard-navigates and destroys 21 unsaved values | Reproduced; the confirm guard alone did not satisfy the contract | **CLOSED** | `verification-…md` §B; handoff §B | `51d78d3e` + `3aee95e2` | none |
| 9 | **F-09** | walk 8 only | unassigned | **Newly adopted** | `Undo turn 1` present and enabled on a run with no turn recorded | Not measured | **OPEN — unmeasured** | `EXISTING_ISSUE` → `R7-21` | — | needs a live check |
| 10 | **F-10** | walk 8 only | unassigned | **Newly adopted** | Cockpit reports a 55-turn-overdue obligation and recommends `Rest` without mentioning it | Not measured | **OPEN — unmeasured** | `EXISTING_ISSUE` → `R7-22` | — | needs a live check |
| 11 | **F-11** | walk 8 only | walk 8 (self-withdrawn) | **Newly adopted** | The `No turn has been logged yet` sentence at S11 was a defect | **Withdrawn by the walk itself, 2026-11-11** — it visited the race desk before committing the turn | **WITHDRAWN** | walk-8 finding table, F-11 row | — | none |
| 12 | **F-12** | walk 8 only | this session (measured) | **Newly adopted** | Skill search sits in a collapsed `<details>`, 274 px below the scrollable area, unfocusable | **Already fixed.** The search is not inside a `details`, is visible at `top: 555` within `scrollHeight: 1550`, focuses on click, and typing `Pace Chaser` narrowed the table to 7 rows. Re-verified after `f192f75a` | **CLOSED (already fixed)** | live measurement, twice | — (fixed upstream before this round) | none |
| 13 | **F-13** | peer (observation) | this session | **Transferred** | `PLANNED SPEND` stays `0` and `REMAINING` stays `173` after a 140 SP skill is added | **Refuted.** Planned spend `0 → 140 → 0`, remaining `173 → 33 → 173` at the walk's own skill. The walk pressed `Add to run` (records as acquired), not the plan checkbox | **REFUTED, not fixed** | `verification-…md` §B.4; register §11.2 | `b2832ee6` (test only) | none |

---

## 3. F-01 reconciliation, verified against the test and commit

Recorded from the artifacts, not from memory:

| Field | Value |
| --- | --- |
| Commit | `050da391` — `fix(scenario): drop the continuation hint once a scenario is chosen (F-01)` |
| Files | `resources/js/pages/Career/ScenarioSelect.vue`, `tests/browser/career-wizard-count-and-hint.spec.ts` |
| Original failing assertion | `Expected: hidden, Received: visible`, with the locator resolving 33 times to `<span id="scenario-continue-hint">` — so the failure was the stale sentence, not a slow repaint |
| Corrected behaviour | The hint is gated on `props.selected === null`, the same condition that already gated `aria-describedby`, so the sighted reading and the announced one stay in step |
| GREEN | **1 passed** |
| Neighbouring regression | `career-scenario-select.spec.ts` + `wizard-flow.spec.ts` → **11 passed** |
| Typecheck | **0 errors** |

---

## 4. F-02 reconciliation

| Field | Value |
| --- | --- |
| Commit | `f192f75a` — `fix(trainees): preserve the catalogue count under filtering (F-02)` |
| Files | `app/Http/Controllers/Career/TraineeSelectController.php`, `resources/js/pages/Career/TraineeSelect.vue`, `tests/browser/career-trainee-catalogue-count.spec.ts` |
| Intended contract | The sentence says **catalogued**, so its number is the catalogue's size. F-02's own wording — "the count follows the filter and the sentence does not say so" — is satisfied by fixing either half |
| Correction | The controller counts the catalogue from the roster's own base (published rows, filter not applied) and sends `catalogueTotal`. The lede prints it, and adds `The current filter matches N.` only when a filter is active and the figures differ |
| RED | `Expected: 66, Received: 2` |
| GREEN | **1 passed** |
| Browser | Unfiltered `66 trainees are catalogued.`; filtered `66 trainees are catalogued. The current filter matches 2.`; clearing restores the opening state. Both numbers honest in all three |
| Neighbouring | `career-trainee-select.spec.ts` **20 passed**; `CareerTraineeSelectTest` **24 passed** (492 assertions) |
| Typecheck / lint | 0 errors; `php -l` clean |

**Why the first attempt at this case passed against the unfixed screen** — kept because it is the trap:
the roster shows six rows a page, so a guard of `rows < 66` was true on load as well as after
filtering. The "the filter applied" precondition proved nothing. The case now waits on the pager's own
denominator (`page 1 of N`), which only moves when the filtered set does.

---

## 5. F-04 and F-12, evidence preserved as measured

- **F-04** — `totalEnteredCap` computes from `props.enteredCaps`, the persisted values. Four saved caps
  of 1200 produce **4800**. Typing into the fields does not move the figure because it is not a live
  form readout, and that is deliberate (R6-04). **Not labelled a defect.**
- **F-12** — the skill search is visible, focuses on click, and filters (623 rows → 7 for
  `Pace Chaser`). Re-verified after `f192f75a`; `SkillsPlanner.vue` holds one `<details>` but the
  search is not inside it (`inDetails: false`).

---

## 6. Previously resolved, not reopened

`F-03` (CLOSED, `282e5437` + `aaf21cd3`), `F-08` (CLOSED, `51d78d3e` + `3aee95e2`), `F-13` (REFUTED,
test-only `b2832ee6`). Their implementation and verification evidence is unchanged. `RTX-01`, `RTX-02`
and `UX-09` are outside the F-series and were not touched.

---

## 7. Remaining work

| Finding | Next step |
| --- | --- |
| `F-05`, `F-06`, `F-07`, `F-09`, `F-10` | Each needs a live browser check at current HEAD before it can be classified. The F-04 and F-13 lessons both say a finding's own account of what it saw can be wrong, so none should be implemented from the walk's text alone |
| `F-11` | Withdrawn by the walk; nothing to do |

Nothing in this round was blocked. No migration was run and the shared database was not modified.
