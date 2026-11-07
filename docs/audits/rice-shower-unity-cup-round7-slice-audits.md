# Round 7 — slice audits (`RS-UC-R7-SL`)

**Status:** append-only ledger. One section per landed slice, ten sub-sections each. The register's §4 statuses stay
frozen at `8a3cf21c`; this file records what landed **after** that read, and what each slice did **not** close.

**Opened:** 2026-11-07. **Base commit:** `05be6616`.

---

## §0 Session baseline

```
$ git rev-parse HEAD
05be66164c71fa4b01f717adf60c22601ec1e265

$ git stash list
stash@{0}: WIP on master: b4458c09 fix(raceplanner): remove the duplicate closing template tag the group loop left
stash@{1}: On master: peer-traineeprofile
stash@{2}: On master: Phase 2.1 work in progress (foreign, pre-state)
```

Three stash entries, none this session's, none touched.

```
$ git status --short -- <the six peer paths>
 D docs/audits/AUDIT-SUPPORT-CARDS-UIUX-2026-11-05.md
 M docs/imports/Umamusume_Progress_Tracker_Runs.md
 M docs/imports/ura-finale-runs.md
 D docs/proposals/PLAN_SLICES_6_7.md
 M docs/research-scratch/AUDIT-AND-VERIFICATION.md
 M docs/research-scratch/PROCESS-PLANS.md

$ git status --short
 D docs/audits/AUDIT-SUPPORT-CARDS-UIUX-2026-11-05.md
 M docs/imports/Umamusume_Progress_Tracker_Runs.md
 M docs/imports/ura-finale-runs.md
 D docs/proposals/PLAN_SLICES_6_7.md
 M docs/research-scratch/AUDIT-AND-VERIFICATION.md
 M docs/research-scratch/PROCESS-PLANS.md
?? docs/audits/RS-UC-2026-10/
?? docs/audits/RS-UC-2026-11-snapshot-door/
?? docs/audits/RS-UC-2026-12/
?? docs/audits/rice-shower-unity-cup-ux-walk-6.md
?? docs/audits/rice-shower-unity-cup-ux-walk-7-snapshot-door.md
?? docs/audits/rice-shower-unity-cup-ux-walk-7.md
?? docs/imports/ura-finale-runs.md.bak
?? screen_data.json
```

**Lore baseline at session start:** docs `556 hit(s), 75 exempt line(s)`; code `556 hit(s), 75 exempt line(s)`.

**Path corrections:** eight dispatch paths were wrong at this commit. All eight are resolved in
`docs/audits/rice-shower-unity-cup-round7-path-corrections.md`, which precedes every slice below.

---

## §0.1 Ledger index

| Slice | Rows | Commit | Result |
| --- | --- | --- | --- |
| _(none landed yet)_ | | | |

---

## Legend

Each landed slice appends a section with exactly these ten sub-sections:

1. **Slice, rows, register reference** — the ID, the `R7-XX` rows, and where the register classifies them.
2. **Pre-flight** — the verbatim output of the two pre-flight commands.
3. **RED** — the failing assertion, its `file:line`, and its exact message.
4. **GREEN** — the passing assertion and its `file:line`.
5. **Non-vacuity** — the path-scoped revert, and the failure it produced on re-run.
6. **Regression** — the adjacent suites' output.
7. **Gates** — each gate's own result, including the ones not run and why.
8. **Lore delta** — `Lore docs: N (was M); code: P (was Q)`, with a ruling if either moved.
9. **Commit** — the SHA and the exact pathspec.
10. **What this slice did not close** — every facet the register's row left open, named.
