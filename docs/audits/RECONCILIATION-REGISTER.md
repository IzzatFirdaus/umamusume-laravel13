# Reconciliation register — cross-walk audit history

**Scope.** Cross-walk identity, disposition and recurrence prevention for the Rice Shower / Unity Cup
UX walk programme. It is **not** a defect register: `KNOWN-ISSUES.md` owns permanent `KI-nn` defect
identity (111 headings, highest `KI-160`, `AGENTS.md:157` names it "live defect register"). This file
owns the *mapping* between walks, the historical registers and that register, plus what was learned
about why defects recur.

**Established 2026-11-11** while reconciling walk 8 (`RS-UC-2026-13`) against walks 1–7.

---

## 1. Why this file exists and what it does not replace

Four artifacts, four jobs, no overlap:

| Artifact | Owns |
| --- | --- |
| `KNOWN-ISSUES.md` | permanent defect identity, status, closure evidence (`KI-nn`) |
| walk documents (`rice-shower-unity-cup-ux-walk*.md`) | what one walk observed, in visit order |
| round registers (`rice-shower-unity-cup-round*-reconciliation.md`) | what one round decided about one walk's findings |
| **this file** | cross-walk identity, recurrence, prevention, and the counts that make the above comparable |

Creating a second defect register was considered and rejected: it would have duplicated `KI-nn`
identity and produced two answers to "is this fixed". Nothing here assigns a new defect ID. A
finding that is genuinely new is routed to `KNOWN-ISSUES.md` by the `AGENTS.md` §11 procedure.

---

## 2. Count definitions — read these before quoting any number

Every figure in this file, and the definition that makes it true. The review that commissioned this
pass flagged that the earlier report quoted several of these interchangeably, which is how a
line-hits count became a "findings" count.

| Metric | Value | Definition |
| --- | --- | --- |
| KI entries | **158** | distinct `KI-<n>` ids across `KNOWN-ISSUES.md` + `AUDIT-AND-VERIFICATION.md`. Two numbers (16, 34) were never used, so this is below the ceiling |
| Highest KI id | **KI-160** | a ceiling, **not** a count |
| KI closed-shaped | **94** | heading or State line says CLOSED / RESOLVED / FIXED / SUPERSEDED |
| KI open-shaped | **64** | heading or State line says FILED / OPEN. A backlog figure, **not** proof of a live defect |
| Walk registers inventoried | **6** | R2–R7. Walk 1's record is a resolutions file, not a register |
| Historical register rows | **271** | distinct ids: R2 21, R3 32, R4 32, R5 33, R6 54, R7 99 |
| Historical rows open-shaped | **212** | raw register status, carried forward unverified |
| Historical rows closed-shaped | **59** | Confirmed fixed + Fixed + Ruled out + Does not reproduce |

**The trap, stated once.** `212` is a count of *historical register rows whose recorded status was
open-shaped*. It is **not** 212 confirmed current defects: no walk-8 pass verified more than a
fraction of them, and a row can be open-shaped in its register and already fixed in the tree, or
closed in its register and broken today. The 64 open-shaped `KI` entries are a **different,
overlapping** population with its own staleness. Neither number should be quoted as "current
defects" without the verification that would earn it.

**Correction carried forward.** An earlier draft of this pass reported 518–541 issue rows. That was
a count of table **line-hits**: several registers print one issue across more than one table row, and
the parser split on pipes inside code spans. The true distinct count is **271**. The 518–541 figure
should not appear in any artifact as an issue count.

---

## 2a. The corpus this register spans

Nine walk documents, eight walks (walk 7 also has a snapshot-door companion), six round registers.

| Walk | Document | Lines | Round register |
| --- | --- | --- | --- |
| 1 | `rice-shower-unity-cup-ux-walk.md` (un-numbered) | 957 | resolutions file, not a register |
| 2 | `…-walk-2.md` | 941 | R2, 21 rows |
| 3 | `…-walk-3.md` | 989 | R3, 32 rows |
| 4 | `…-walk-4.md` | 795 | R4, 32 rows |
| 5 | `…-walk-5.md` | 756 | R5, 33 rows |
| 6 | `…-walk-6.md` | 1,691 | R6, 54 rows |
| 7 | `…-walk-7.md` + `…-walk-7-snapshot-door.md` | 1,461 + 936 | R7, 99 rows |
| 8 | `…-walk-8.md` | 1,358 | none yet — this pass |

Walk 1's outcome is recorded as `rice-shower-unity-cup-ux-walk-resolutions.md` (23 `D`-series
findings), so it has no `R1` register and no row count comparable to R2–R7.

---

## 3. Registers do not inherit — so this baseline is new work

`rice-shower-unity-cup-round7-reconciliation.md` states its own rule at line 3: *"No row inherits a
prior register's classification."* R7 re-derived all 193 of its rows by a fresh read at `8a3cf21c`.

That has a consequence worth stating plainly: **concatenating R2–R7 does not produce a valid
historical baseline.** Each register is a snapshot of what one round believed at one commit. This
file therefore records, for every row it reuses, both *what the register said* (preserved verbatim)
and *what this pass verified* (added separately). No historical classification is overwritten.

---

## 4. The identity join key

**Walk-local finding ids collide across walks.** `F-01` is a *different defect* in walks 3, 4, 6, 7
and 8:

| Walk | `F-01` means |
| --- | --- |
| 3 | a stat band appearing only once a band is recorded |
| 4 | its own first functional finding |
| 6 | a blocking finding walked before the wizard rework |
| 7 | the trainee list reverting to 66 rows |
| 8 | the stale `Choose a scenario to continue.` helper |

So a walk-8 `F-01` can only be matched by **reading the behaviour**, never by matching the label.
This register uses `R<N>-<nn>` (register-scoped, therefore unique) plus a one-line root-cause key. A
walk-local id appears only as a local reference inside its own walk's section.

---

## 5. Walk-8 dispositions

Every one of the 51 walk-8 finding records carries exactly one primary disposition. The count is
13 F + 23 E + 15 U records; F-11 is withdrawn but still appears here so no record can silently
vanish.

**Totals: 51 records → 14 NEW · 30 EXISTING_ISSUE · 2 RELATED_NEW_ISSUE · 3 KNOWN_LIMITATION ·
1 RECURRENCE · 1 INVALIDATED · 0 REGRESSION · 0 DUPLICATE.**

**Amended 2026-11-11 (follow-up planning pass).** Triaging the 16 `NEW`/`RELATED_NEW_ISSUE` records
meant reading the code behind each one. **Four do not survive that read** — `U-03`, `U-08`, `U-11`
and `U-14` all claim a capability is absent when it exists and the walk simply did not reach it. The
dispositions above are preserved unedited; the refutation lives in
`walk-8-follow-up-plan.md` §2.0 with the file-and-line evidence, and the corrected states are
`REPRODUCED_EXPECTED` ×2 and `NOT_REPRODUCED` ×2 under the vocabulary in that document's §1.

Effective triage population is therefore **12, not 16**, and it is the second-largest reason a
walk-8 finding count should not be read as a defect count.

The headline number is **30**: thirty walk-8 observations were matched to an issue the programme had
already recorded. Under the old process those would have been re-investigated and re-reconciled.


#### RECURRENCE (1)

| Walk-8 | Screen | Historical row | Prior status | Reasoning |
| --- | --- | --- | --- | --- |
| `F-08` | S10 | `R5-23` | Does not reproduce | R5-23 saw the Race option navigate and marked it 'Does not reproduce' as by-design. Walk 8 shows the destruction of 21 unsaved values. Same behaviour, previousl |

#### EXISTING_ISSUE (30)

| Walk-8 | Screen | Historical row | Prior status | Reasoning |
| --- | --- | --- | --- | --- |
| `E-01` | S01 | `R7-39` | Known limitation | three labels for one route — same navigation-label family |
| `E-03` | S01 | `R7-40` | Confirmed defect | two import doors differing by one word — already recorded |
| `E-04` | S02 | `R7-41` | Confirmed defect | required=false radios + reasonless disabled primary — same row |
| `E-05` | S03 | `R7-13` | Confirmed defect | R7-13 is the Form-select-displays-chosen defect verbatim |
| `E-06` | S04 | `R4-11` | Unrepresented source d | form rarity cell empty — same unrepresented-data family |
| `E-07` | S05 | `R7-66` | Unrepresented source d | R7-66 is 'Caps in force 1325/1322/1368/1308/1800' as unrepresented source data — exactly the walk-8 point that no field records a cap as in force rather than as |
| `E-08` | S05 | `R7-47` | Confirmed defect | step claims to be skipped while mandatory — same stale copy |
| `E-10` | S06 | `R7-50` | Confirmed defect | R7-50: guest checkboxes carry no id/aria-label beyond visible text — same accessible-name class, same Legacy step |
| `E-13` | S07, S16 | `R7-50` | Confirmed defect | unlabelled per-slot Level input — same accessible-name class as R7-50 |
| `E-14` | S08 | `R6-24` | Confirmed defect | attention count vs create-anyway — same contradiction family |
| `E-15` | S09 | `R6-32` | Unrepresented source d | scenario name repeated — same redundancy |
| `E-16` | S10 | `R7-57` | Confirmed defect | R7-57 is the seven unlabelled number inputs verbatim |
| `E-17` | S10 | `R7-56` | Confirmed defect | R7-56 is energy-state defaults Exact verbatim |
| `E-18` | S14 | `R7-20` | Confirmed defect | turn field defaults before entry — same default-assertion family |
| `E-19` | S14 | `R7-31` | Confirmed defect | required field label distant from its control |
| `E-23` | S17 | `R7-61` | Confirmed defect | R7-61 is the phase copy naming Junior Make Debut as 'the one dated obligation' — the same fact walk 8 saw described in two registers |
| `F-05` | S07 | `R6-54` | Does not reproduce | R6-54 covers deck slot count vs rendering; walk 8's count-vs-copy disagreement is the same surface. NOT R6-54's DNR claim (that was about slots not rendering) |
| `F-06` | S08 | `R4-03` | Confirmed defect | R4-03: 'A saved build target never attaches to the created run' — same root cause (partial payload parses to null). Walk 8 adds the preflight sentence that misr |
| `F-07` | S09, S12 | `R7-22` | Confirmed defect | R7-22 already recorded the identical sentence at turn 67. Walk 8 is a second occurrence, not a new defect |
| `F-09` | S10 | `R7-21` | Confirmed defect | R7-21: 'Undo turn 1 enabled before any turn exists' — same control, same defect |
| `F-10` | S12 | `R7-22` | Confirmed defect | same advisor obligation contradiction; walk 8 adds the Rest-recommendation juxtaposition |
| `U-01` | S02 | `R7-63` | Unrepresented source d | R7-63 is the missing scenario full label |
| `U-04` | S10 | `R3-23` | new | facility levels recorded, off-path |
| `U-05` | S06 | `R3-23` | new | white/pink/green sparks carry no one-pass path — same row |
| `U-07` | S07 | `R6-46` | Unrepresented source d | spark values beyond kind/category/target/stars |
| `U-08` | S09, S12 | `R2-14` | Fixed | R2-14 marked Fixed via UnityCupEditor.vue, which TeamEdit.vue renders and the cockpit reaches through its closed 'Career tools' disclosure. NOT a regression: th |
| `U-09` | S10 | `R7-71` | Unrepresented source d | three counters |
| `U-12` | S13 | `R7-59` | Known limitation | hint level not recorded, so no discount — same skill-budget family |
| `U-13` | S13 | `R7-78` | Unrepresented source d | owned skills unrepresented |
| `U-15` | S14 | `R3-01` | Confirmed defect | event text/outcome unrepresentable |

#### RELATED_NEW_ISSUE (2)

| Walk-8 | Screen | Historical row | Prior status | Reasoning |
| --- | --- | --- | --- | --- |
| `F-12` | S13 | `R2-20` | Fixed | related surface (a control inside a closed <details>), different cause: R2-20 is a 44px sweep failure, this is unreachable-below-scroll |
| `U-02` | S02 | `R7-63` | Unrepresented source d | scenario mechanics not summarised on the step — broader than the label gap |

#### KNOWN_LIMITATION (3)

| Walk-8 | Screen | Historical row | Prior status | Reasoning |
| --- | --- | --- | --- | --- |
| `E-09` | S05 | `R7-48` | Known limitation | Wit has no ceiling input — same accepted asymmetry |
| `E-21` | S16 | `R7-95` | Known limitation | R7-95 is the deck's whole-deck replace, recorded as a deliberate known limitation |
| `U-06` | S07, S16 | `R3-25` | Deferred | per-card client level — deferred, owner-gated |

#### NEW (14)

| Walk-8 | Screen | Historical row | Prior status | Reasoning |
| --- | --- | --- | --- | --- |
| `E-02` | S13 | — | — | R4-02 matched on 'accessible name' but is a different screen and defect; duplicate-label ambiguity between sidebar and career nav is new |
| `E-11` | S06 | — | — | no historical row covers the Projected-start table failing to read stored sparks; R6-06 (my earlier guess) is a DNR about spark copy, a different claim |
| `E-12` | S07 | — | — | Name-contains filter needs an explicit submit — no historical row |
| `E-20` | S15 | — | — | empty segment between separators is a formatting defect, NOT an accessible-name gap; my earlier R7-36 mapping was keyword noise |
| `E-22` | S17 | — | — | '1 entries' plural — copy defect, no historical row |
| `F-01` | S02 | — | — | no historical row covers the stale 'Choose a scenario to continue.' helper; R7-41 is the disabled-control reason, a different mechanism |
| `F-02` | S03 | — | — | no historical row covers a filtered roster relabelling the catalogue count |
| `F-03` | S06 | — | — | R3-16 = select is unnamed (a11y); R6-31 = spark workflow cost; R6-46 = unrepresented magnitude. None covers typed-text-not-committing. Same surface, three disti |
| `F-04` | S05 | — | — | no row covers the entered-ceilings total reading stale until reload |
| `F-13` | S13 | — | — | no row covers PLANNED SPEND staying 0 after a skill is added |
| `U-03` | S03, S04, S08 | — | — | Potential Lvl has no field anywhere: no historical row |
| `U-10` | S10 | — | — | per-tile card composition unrecordable — no row |
| `U-11` | S11 | — | — | Kyoto Daishoten not on the imported calendar — no row |
| `U-14` | S13 | — | — | 33 Learn prices not importable — no row |

#### INVALIDATED (1)

| Walk-8 | Screen | Historical row | Prior status | Reasoning |
| --- | --- | --- | --- | --- |
| `F-11` | S12 | — | — | withdrawn by the walk itself: the sentence was correct pre-commit |

---

## 6. Resolution verification — the closed set, re-checked against the tree

A register saying `Confirmed fixed` is a claim, not evidence. The six walk-1 `D`-series fixes carry
commit hashes and named tests, so they were re-verified by reading the current tree.

| Fix | Claim | Class | Evidence |
| --- | --- | --- | --- |
| D1 | inheritance HTTP 500 | **present+verified** | `runs.inheritance` route live (`routes/web.php:197`); `CareerInheritanceEventTest` 18 cases |
| D3 | rented/friend flag persisted | **present+verified** | `SupportDeckBuilderTest` 55 cases; walk 8 read the flag back off run #11 at S16 |
| D5 | trainee readback `N/A` | **present+verified** | `CareerTraineeSelectTest` 24 cases; `selectedCard` prop present and read at S03c |
| D9 | duplicate turn-entry paths | **present+verified** | `resources/js/domain/turnEntryFields.ts` is the single field source; `TurnEntryFieldSourceTest` 4 cases |
| D6 | Fans/Energy turn table | **superseded** | surface **removed** by `b30906a6` cockpit cutover; `TurnRowActionsTest` deleted with it, documented in the resolutions file |
| D7 | wizard step numbering | **superseded** | surface removed by the same cutover; `GuidedTurnOnRunViewTest` deleted, no residue |

**Classes used throughout:** `present+verified` · `present+unverified` · `absent` · `superseded` ·
`uncertain`.

**Two of six are `superseded`, not `verified`** — and that is the honest answer. D6 and D7 were
closed by *removing the surface*, so there is no acceptance criterion left to satisfy and no
regression test to keep. Re-running the test would be meaningless: it is gone.

**Not re-verified in this pass:** the other 24 `Confirmed fixed` / `Fixed` rows across R2–R7. They
remain `present+unverified` by default. Stating that is the point: *a closure commit does not prove
the current implementation still meets its acceptance criteria*, and this pass verified the six where
the evidence was cheap. The remaining 24 are listed in §8 as the unverified resolution backlog.

---

## 7. The one recurrence, and why the earlier decision failed

**F-08 (walk 8) · `R5-23` · flagged `Does not reproduce` · now `RECURRENCE`.**

R5-23 recorded the observation correctly: *"Race option navigates by design via `href` +
`chooseAction` (`TrainingDetail.vue:471-481`)"*. It then classified it `Does not reproduce`, because
the navigation *is* intentional. That classification answered the wrong question. Navigation being
deliberate does not make the loss of 21 unsaved values acceptable — it makes it a design fault rather
than a bug, and either way it is a defect a Trainer experiences.

**What the code shows today** (`resources/js/pages/Career/TrainingDetail.vue:518-524`):

```
href: `/training-runs/${props.run.id}/races`,
const chooseAction = (value: string, href: string | null): void => {
    if (href !== null) {
        window.location.href = href;
```

`window.location.href` is a hard browser navigation. There is no `preserveState`, no draft write and
no confirmation. Every unsaved field is destroyed by design.

**Root cause:** the action selector doubles as a navigation control. Selecting an *action* for the
turn to be recorded and *leaving the form* are treated as one interaction, so an ordinary choice has
the side effect of a page exit.

**Why the prior disposition failed to prevent it:** `Does not reproduce` closed the row without
asking whether the by-design behaviour had a cost. The status vocabulary has no slot for "reproduces,
and the behaviour is intended, and the intent is wrong" — which is exactly the case a `Known
limitation` must be *ruled* on rather than assumed.

**Does the same weakness exist elsewhere?** Checked: `RaceDecision.vue:92` uses
`router.get(..., { preserveState: true, preserveScroll: true })` — the Inertia path preserves state.
`TrainingDetail.vue` is the only hard `window.location.href` on a form-bearing screen.

---

## 8. Preventive actions

Measurable, each with an acceptance criterion and a named home. Status is `proposed` unless stated.

| ID | Prevents | Action | Acceptance criterion | Home | Priority |
| --- | --- | --- | --- | --- | --- |
| PA-01 | F-08 recurrence | Guard the action selector: `chooseAction` warns when the form is dirty, or the race radio becomes a link separate from the action radios | a spec fills three fields, selects `Race`, and asserts either a confirm dialog appears or the values survive the round trip | `tests/browser/career-training-leave-guard.spec.ts` (new) | **P1** |
| PA-02 | F-03 (combobox typed-not-committed) | Assert commitment, not typing: after typing into a `role=combobox` and submitting, read the persisted value back | a spec types into the spark `Target`, saves, and asserts the stored `target` is non-null *or* the save is refused with a visible error | `tests/browser/career-legacy-spark-commit.spec.ts` (new) | **P1** |
| PA-03 | F-06 (partial payload reads as "never entered") | Distinguish "absent" from "unparseable" in the preflight contract so one sentence stops covering both | `CareerPreflightTest`: a draft with ceilings but no purpose renders a sentence naming *what* is missing, not "the step has not been entered" | `tests/Feature/CareerPreflightTest.php` (exists) | **P1** |
| PA-04 | Duplicate navigation labels (E-02) | Make the two `Skills` destinations distinguishable in their accessible names (e.g. "Skills library" vs "This run's skills") | a spec asserts the two nav links have different accessible names on the cockpit | `tests/browser/ui-hygiene.spec.ts` (exists) | P2 |
| PA-05 | Unreachable control in a closed `<details>` (F-12) | Assert reachability, not presence: a control inside a disclosure is focusable only after the disclosure opens | a spec measures the search field's `scrollHeight` reachability before and after opening the disclosure | `tests/browser/career-skills-reach.spec.ts` (new) | P2 |
| PA-06 | `Does not reproduce` closing an intended-but-costly behaviour | Add a register rule: a row whose observation reproduces must be `Known limitation` (with a ruling) or `Confirmed defect`, never `Does not reproduce` | this file, §7 — applied to R5-23 as the worked example | this document | P2 |
| PA-07 | Finding-count drift (518–541 vs 271) | Count distinct ids, never table rows; state the counting method beside every figure | the register's §2 table is the definition set; new passes cite it rather than re-deriving loosely | this document | P2 |

**PA-06 and PA-07 cost nothing to adopt** — they are rules, not code. PA-01 and PA-02 are the two
that would have caught walk 8's two most severe findings before a human noticed them.

**Disposition updates 2026-11-12 (dated, append-only).**

- **PA-02 → closed.** `282e5437` (2026-11-12) landed the first-draft fix, and the owner ruling the
  same day (strict revert, no implicit commitment) corrected it forward in `aaf21cd3`:
  `commitTypedText()`/`commitExactText()` removed; the only accepts are clicking an option and
  pressing Enter on a highlighted one; Tab, Escape, blur and outside-click revert to the committed
  value. The spec grew from 5 to 9 cases and now pins the ruling's own list (exact-name-typed,
  rejected filter, Enter, Escape) alongside the original payload-readback cases. Evidence:
  `.scratch-uma/retest-audit-2026-11-11/f03-red-derivation.log` (RED before: the peer's own
  implicit-commitment case failed) and `f03-green-verification.log` (9 passed at HEAD). The
  acceptance criterion above is met in the stronger direction: the stored `target` is exactly what
  was explicitly accepted, never what was typed.
- **PA-01 → superseded in part.** `51d78d3e` added the dirty-form confirm guard (14/21 values
  covered), which satisfies the criterion's confirm-dialog arm; the draft-persistence arm remains
  open as F-08 under the consolidated remediation plan (`09-consolidated-remediation-plan.md` §9.6:
  sessionStorage draft, fingerprint-validated restore, 21 values).

---

## 9. Regression-prevention matrix

What is actually protected today, versus what relies on a human re-reading a register.

| Area | Prior defects | Automated | Manual | Unverified |
| --- | --- | --- | --- | --- |
| Wizard setup (S02–S08) | R5's 33 rows, R6's setup rows | partial — `CareerBuildTargetTest`, `CareerPreflightTest`, `CareerLegacyDeckStepsTest` | yes | most `Confirmed fixed` rows |
| Training form (S10) | R7-20/21/56/57, R5-23 | `TurnEntryFieldSourceTest` (4) | yes | F-08's guard: **none** |
| Cockpit (S09, S12) | R7-22/17/19/21 | `CareerCockpitTest`, `SnapshotCockpitTest` | yes | |
| Skill planner (S13) | R7-59, R7-29 | `CareerSkillsPlannerTest`, `SkillSpendCoverageTest` | yes | F-13's budget total: **none** |
| Unity Cup team (U-08) | R2-14 | `CockpitTeamPanelTest`, `CockpitUnityCupCountersTest` | yes | off-path: wizard cannot reach it |
| Deck (S07, S16) | R6-54, R7-95 | `SupportDeckBuilderTest` (55) | yes | |
| Events / inheritance (S14, S15) | R3-01, R6-05, R7-33 | `CareerInheritanceEventTest` (18), `TurnEntryFieldSourceTest` | yes | |

**Scenarios.** Every row above is Unity Cup, because every walk was Unity Cup. URA Finale,
Trackblazer and Our Grand Concert share the wizard and the cockpit, so the setup and cockpit rows
very likely apply to them too — but **no walk has tested them**, and this register does not claim it.
That is the single largest untested surface in the programme.

---

## 10. Metrics from walk 8 onward

Tracked per walk, not cumulatively, so a trend is visible.

| Walk | Duplicates prevented | Verified resolution rate | Recurrence rate | Backlog closure | Regression coverage |
| --- | --- | --- | --- | --- | --- |
| 8 | **30** matched / 51 records | **4 of 6** D-fixes verified (2 superseded) | **1** (R5-23) | not measured — no fixes landed this pass | 4 of 6 D-fixes have a live test |

Two of these five are measurable from this pass; the other three need a walk that also lands fixes,
which this task does not authorize.
