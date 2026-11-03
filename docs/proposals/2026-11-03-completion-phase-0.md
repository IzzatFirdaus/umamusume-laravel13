# Completion pre-flight state (rev 2)

Date: 2026-11-03
Session: completion pass (rev 2)

## Ground truth at pre-flight

- HEAD SHA: `2b61a1da1c0398c642d25e95309acdb4991a2959`
- `origin/master` SHA: `2b61a1da1c0398c642d25e95309acdb4991a2959`
- Local-ahead count: `0` (dispatch expected 9; **drift** — the series is already pushed to origin/master; `78755486` is an ancestor of HEAD)
- Tag state: `3.0.0*` absent (matches expectation)
- Peer rename state: uncommitted — 3 deleted `docs/*.md` and 3 untracked `docs/proposals/*.md` (the `docs/` → `docs/proposals/` rename is still in flight; Slice 2.1 stays deferred/coordinate)
- `.scratch-uma/*.staged-peer-revert.vue`: 2 files present at `.scratch-uma/remaining-code-2026-11-03/` (`Edit.staged-peer-revert.vue`, `RaceDecision.staged-peer-revert.vue`); untracked, not staged. Peer should be told their pre-revert state was preserved.
- Playwright: 1.63.0
- `npm run typecheck`: clean (`tsc --noEmit`, exit 0)
- Working tree dirty: `KNOWN-ISSUES.md` (M), peer rename (D/??)

## Commits in the local series (10 since `78755486`)

1. `032b0c02` fix(env): quote APP_NAME so a fresh clone's .env parses
2. `c1e97c2c` docs(proposals): git reconciliation — prove the SHAs and the fresh clone
3. `82551fdf` docs(proposals): bound the reconciliation — Slices D/E and two readings stay open
4. `4f5b9d3d` fix(snapshot): carry the review payload in the request body, not the URL
5. `cbaed673` fix(runs): derive the status pill colour from the stored state
6. `e4786df4` fix(legacy): a single column is not a difference, so no verdict wears the caution tint
7. `6f3d5a81` fix(race-decision,preferences,support-cards): refusal focus on the calendar branch, the armed reset announcement, and the two summary tables
8. `c91458f4` fix(env): default a fresh clone to SQLite so migrate --seed runs
9. `9a61197f` test(preferences,racedecision): pin the two refusal-focus families
10. `2b61a1da` test(preferences): assert the armed reset's label as well as its live region

Dispatch expected 9; actual 10. The extra is `2b61a1da` (landed after the dispatch's commit list was written). All pushed to origin/master.

## Drift from dispatch expectations

- Local-ahead count 0 vs expected 9: the series is pushed; no owner-gated push remains for these. Push statement in Slice 10.3 needs rewording (series already on origin).
- `KNOWN-ISSUES.md` has uncommitted edits (peer or in-flight).
- Peer rename uncommitted (Slice 2.1 coordination still needed).
- Slice 1.3 (44px count) not yet identified — requires a browser run; deferred to a later session.

## Phase 0 outcome

Ground truth recorded. Proceed to Phase 1 residuals.


## Rev-3 pre-flight note (2026-11-03)

### Push state
- origin/master = ae97da9e (docs(agents): correct ui-lint debt count)
- 6 commits on origin: 847b3fe7 .. ae97da9e
- 2 local-only: 83770ded (deferred items), 0e7bc8ee (peer revert coordination)
- origin is not ahead of HEAD (0 commits); no unauthorized push detected beyond the 6 already reported.
- Authorization status: the 6 commits that reached origin are assumed owner-authorized (recorded here per rev-3 O-1); the 2 local commits remain owner-gated.

### Peer series integrity
- 6f3d5a81 (refusal focus fixes) IS an ancestor of origin/master -> OK
- e4786df4 (legacy compare fix) IS an ancestor of origin/master -> OK
- 9a61197f (peer tests) is local-only, NOT on origin -> origin/master fresh clone is not red.
- Conclusion: origin/master carries the required fixes; peer test commit stays local until the series pushes together.


## Phase 1.3 confirmations (rev 3 follow-up)

The 8 undersized targets reported by the full-suite run are `<summary class="min-h-6">` compact `AbsenceValue` disclosures. The filter was refined to exclude the compact marker class (commit 123de9aa), then narrowed to `summary.min-h-6` only (commit 7fe8bc8c). Three confirmations:

- **A — Exception documented:** DESIGN.md line 874: "`compact` is the dense-row form at 24px, WCAG 2.2 SC 2.5.8's floor, against the house 44px for a primary control (Section 12)." Also DESIGN.md 1318-1319: "The house bar is 44px (min-h-11/h-11), stricter than WCAG 2.2 AA's 24px minimum (2.5.8). Every control meets the 24px floor; 44px is the design intent." The exception is house-level documented. A closes.

- **B — Filter bites:** Probe shrunk a primary control (`btn min-h-11` -> `btn min-h-9`) on the race-decision page, rebuilt, ran the spec: FAIL (Expected 0, Received 1). Restored, rebuilt, ran: PASS. The narrowed filter (`summary.min-h-6`) still catches real violations. B closes.

- **C — Targets are the exception class:** All 8 undersized elements share the exact shape `<summary class="list-item cursor-pointer text-ink-muted min-h-6">` (compact AbsenceValue disclosure). No other kind of summary. C closes.

No source weakening. Filter narrowed from `min-h-6` (any element) to `summary.min-h-6` (compact disclosures only), so a primary control built at min-h-6 by mistake is still caught.


## Correction — 9a61197f status (rev 3 follow-up)

The Phase 0 rev 3 record stated `9a61197f` (peer tests) was local-only and NOT on origin. That was incorrect: `git merge-base --is-ancestor 9a61197f origin/master` returns true (exit 0). The peer's test commit has been on origin since before this pass. The earlier "NOT on origin" conclusion was a PowerShell `$?` inversion artifact, not a real state change.

Consequences:
- origin/master carries the full peer series (`6f3d5a81`, `e4786df4`, `9a61197f`) — never red. Peer series integrity: OK (unchanged).
- `origin/master..HEAD` count is 6, not 4: the 6 are all commits landed by this agent (83770ded, 0e7bc8ee, 0e685dfb, 123de9aa, 7fe8bc8c, 37acba79). None are peer commits.
- Extra commit 7fe8bc8c (filter narrowing `min-h-6` -> `summary.min-h-6`) was added during the confirmation pass and is not in the dispatch's expected-4 list. It is a legitimate refinement, not a peer commit or a missing fix.
