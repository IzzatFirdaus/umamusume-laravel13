# Dispatch report — `S-RS-CLEANUP-2` (arc close-out)

**Pre-flight HEAD:** `c1f09053` — `feat(scenario-detail): HFW visual overhaul, page-wide plain h2 (S-RS-VISUAL-1)`
**Close-out HEAD:** `b22bbd3b` — `docs(research): S-RS-VISUAL-1 — arc closed, reds classified`

**Arc closed.** Visual overhaul landed at `c1f09053`; cleanup landed across `9bd3df7b`, `ba7ea3d0`, `fb8c8973`, `b22bbd3b`. Two baseline reds are now classified rather than carried: `UiHygieneTest` **resolved** (anchor in the comment, source by content in the shipped copy) and `ura-panel.spec.ts:193` **classified KI-160 and corrected** (stale test, code correct). The sibling collision is on record in the register's §14. v4 of the wrapper trap is recorded in the skill's `references/verification-patterns.md`.

---

## 1. Pre-flight

| Item | Measured |
|---|---|
| HEAD | `c1f09053` ✓ matches the dispatch |
| Tracked modifications | 2 (`TeamPanelBuilder.php`, `CockpitUnityCupCountersTest.php`) |
| Untracked | 6 research/audit documents, all pre-existing from the arc |
| Commit size | `c1f09053` = **24 files**, +1130/−420 ✓ matches the report |

**Classification of every dirty path:**

| Path | Class | Evidence |
|---|---|---|
| `app/Services/Career/TeamPanelBuilder.php` | **sibling work** | Not in `c1f09053`; changes the shipped carrier from `config/scenarios.php, transcribed there from GameTora` to `the scenario settings, transcribed from GameTora` |
| `tests/Feature/CockpitUnityCupCountersTest.php` | **sibling work** | Not in `c1f09053`; weakens `toContain('scenarios.php')` → `toContain('scenario')` |
| 6 untracked `docs/**` | **peer-set earlier arc** | Present at pre-flight, untouched by this dispatch |

Nothing unclassified. Sibling state preserved before any edit at `.scratch-uma/S-RS-CLEANUP-2/sibling-weakening-20261110T160926.php`.

---

## 2. Slice A — the weakened assertion

**No commit was needed, and that is the finding rather than a shortcut.** The weakening never reached a commit. `git show HEAD:tests/Feature/CockpitUnityCupCountersTest.php` already carries `toContain('scenarios.php')`, and `TeamPanelBuilder.php` at HEAD already carries `config/scenarios.php, transcribed`. The canonical state was committed; only the sibling's working copy was dirty.

Non-vacuity, run after reverting the working copy: `--filter="CockpitUnityCupCountersTest|UiHygieneTest"` → **`CockpitUnityCupCountersTest` passes** (8 of the 11), `UiHygieneTest` red at its baseline site.

**The re-application happened four more times after this.** The dispatch warned once was not enough, and it was right: the pair was re-dirtied at 16:17 and again at 16:45, and the sibling then began **committing** — `9a9c9359` put the weakened carrier in, `ea5e6f1c` reverted this session's Slice B fix. Per the stop condition, no loop: canonical state restored once, interference recorded in §14, sibling's uncommitted copy left as theirs.

---

## 3. Slice B — the `§2.2.2` citation (B.1-fix, not B.1-file)

**Decision: B.1-fix.** The two requirements are both real and they do not conflict once layered:

- The floor requires a derived figure's claim to keep its provenance (`AGENTS.md` §5: "no fact stored without provenance").
- `UiHygieneTest` requires that no repository identifier reaches shipped copy, and `§\d` is the documented example.

**The fix:** the shipped `detail` string names the source **by content** — "the client's own Team Info count is the tiebreaker" — and the re-checkable anchor lives in an HTML comment beside it: `UMAMUSUME_REFERENCE.md` §2.2.2, conflict row 57. `shippedVueTemplate()` blanks comments before scanning (`UiHygieneTest.php:143-147`), so the anchor is visible to a maintainer and invisible to the gate. This is not an invention: `TrackblazerPanel.vue:607` already keeps its §2.2.3 citation in a comment for the same reason.

**Non-vacuity both ways:** `UiHygieneTest` **4 passed** (gate green), and the anchor is present in the file (`grep -c '2.2.2, conflict row 57'` → 1). Neither half was achieved by deleting the other.

Committed at **`9bd3df7b`**, after the sibling's `ea5e6f1c` had reverted an earlier attempt.

---

## 4. Slice C — `ura-panel.spec.ts:193` (C.1, stale test)

**Classification: C.1 — stale test, code correct.** This retires three dispatches of "pre-existing" with a cause.

| Evidence | Finding |
|---|---|
| `git log -S` on the behaviour | `27a3d374` — "read a past objective with no record as not reached, not missed (R7-82, R7-22, R7-25, R7-61)" |
| The rule it implements | `TOG-3` (`ADR-0007`, line 59): "status may be stale or absent, **but never wrong**" — an absence cannot be called a failure |
| `TeamPanelBuilder.php:302` | `$reached => $this->objectiveHasRecord($goal, $run) ? 'missed' : 'not_reached'` |
| Props-level pin, already green | `TraineeGoalsPanelTest.php:265` — "reads a past objective the run never played as not reached, not as missed" |
| The browser case | Still asserted `Missed`; last touched by `627a7248` (2026-11-02), one day **after** the semantics changed |

**Non-vacuity proven, not asserted:** mutating `TeamPanelBuilder.php:302` back to `$reached => 'missed'` (the pre-`27a3d374` classification) turned the corrected assertion **red** (`1 failed`), then the mutation was reverted and the case passed. The corrected test genuinely fails on the old behaviour.

Committed at **`ba7ea3d0`**, which also asserts `Missed` does not come back — `missed` now means an entry exists and fell short, so the case requires zero of them.

---

## 5. Slice D — collision record

Appended to `docs/audits/rice-shower-unity-cup-round7-reconciliation.md` as **§14** (below §13, nothing above edited), committed at **`fb8c8973`**. It records: the parallel implementation and its file set; why this session's survived, on the two measured defects (empty goal-card positions that would have deleted Trainee goals and Mandatory races; the dropped `+30` `ProvenanceBadge`); the repeated assertion weakening with the commit trail; the sibling files that legitimately landed in `c1f09053` and are declared there; and the standing warning that `git log` clean does not imply `git status` clean.

---

## 6. Slice D-2 — the v4 wrapper trap

`references/verification-patterns.md` is **not a repository path** — it is a reference inside the `trainer-desk-verification` skill (`C:\Users\exatf\AppData\Local\hermes\skills\testing\trainer-desk-verification\references\verification-patterns.md`). The dispatch's `git commit -- references/verification-patterns.md` could not have worked; there is no such tracked file, and `git ls-files` confirms it.

The v4 variant is recorded there via `skill_manage`, in the existing `NF-measurement` section beside v1–v3:

> **v4 — `git log -1` clean ≠ `git status` clean.** A peer session shares the worktree; an uncommitted peer edit is invisible in history and visible only in `git status`. Re-measure immediately before and after every commit. Do not loop on a re-appearing edit: commit the canonical state once, record it, move on.

It also records the pre-commit hook's 20-file guard as a sweep-in guard, release valve `UMA_ALLOW_BROAD_COMMIT=1`, and explicitly not `--no-verify`.

---

## 7. Slice E — arc declaration

Appended to the header of `docs/research/S-RS-VISUAL-1-report-2026-11-10.md`, committed at **`b22bbd3b`**: arc closed, both reds classified with their resolving commits, collision on record.

---

## 8. Gates — all three on one screen

| Gate | Result |
|---|---|
| **Suite** | **`1 failed, 9 warnings, 2306 passed (36289 assertions)`** |
| **Lint** | `ui-framework-check: 500 finding(s)`, exit 0 — **delta 0** |
| **Typecheck** | **exit 0** |
| PHPStan | `[OK] No errors` |
| Build | exit 0 |

**Suite is `1 failed`, better than the dispatch's `2 failed` band, and Slice B is the reason.** `UiHygieneTest` is green in the full run (zero mentions in the failure output). The single remaining red is `ScenarioAdvisorFinaleTest` — the one red this arc has never explained, and outside this dispatch's scope.

The suite never read `3 failed`, which was the regression stop condition.

---

## 9. Post-commit tree

```
?? docs/audits/RS-UC-SCENARIO-DETAIL-2026-11-09.md
?? docs/audits/RS-UC-SCENARIO-DETAIL-2026-11-09/
?? docs/proposals/2026-11-09-scenario-detail-upgrade.md
?? docs/research/RS-UC-SCENARIO-DETAIL-2026-11-09/
?? docs/research/S-RS-SPAN-1-report-2026-11-10.md
?? docs/research/scenario-detail-design-language-survey-2026-11-09.md
```

**Zero tracked modifications.** The peer/untracked set is exactly the pre-flight set, unchanged — no peer path entered any commit of this dispatch.

---

## 10. What this dispatch did not do, stated rather than implied

- **`ScenarioAdvisorFinaleTest` remains red and unclassified by this arc.** It is the last known red and it predates `S-RS-VISUAL-1`. A future dispatch should give it the treatment Slice C gave `ura-panel.spec.ts:193`: find the commit that changed the label, decide stale-versus-defect, prove it with a mutation.
- **The sibling's parallel files remain on disk under `.scratch-uma/S-RS-VISUAL-1/collision/`.** Not deleted, not merged. Deletion is an owner decision.
- **No KI entry was filed for the `+30`/`§2.2.2` line**, because Slice B fixed it rather than filing it. B.1-file was the alternative and was declined on the evidence that both requirements are satisfiable at once.
- **The four bare `N/A` sites (KI-157) stay out of scope**, as the dispatch directed.
- **Moves 1, 2 and 4 remain gated on the plan addendum.** This dispatch closed an arc; it did not reopen one.

---

## 11. The honest note about the shared worktree

The dispatch's first constraint said one clean measurement would not imply the next. It was right, and the cost was real: the same two files were re-dirtied five times, the sibling began committing to `master` mid-dispatch, and one commit of theirs (`ea5e6f1c`) **undid this session's Slice B fix**, which then had to be re-applied and re-committed (`9bd3df7b`). Every commit in this dispatch was re-verified against `git status` and `git log` after landing, not only before. A reader comparing this report to history should expect the sibling's commits interleaved; §14 is the record that they are not this session's work.
