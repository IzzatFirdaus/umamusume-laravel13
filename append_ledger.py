with open('docs/proposals/execution-ledger-2026-10-17.md', 'a', encoding='utf-8') as f:
    f.write("""

## 25. Dispatch 26 -- 10-decisions batch (decisions 3, 4, 6, 7, 8, 10) + NFR-20 gate + Phase 1 exit criteria, 2026-10-18

### Precondition (measured)

- `origin/master` `29f8715`, ahead **6** at start (4 slice commits + 2 doc commits from this dispatch).
- `HEAD:PRD.md` `22f48ab`. Staged set: empty.
- Four prior commits from this session:
  1. `2ff8c3e` -- docs(adr): accept ADR-0025 to match CHANGELOG and PRD requirements
  2. `1b0940e` -- docs(prd): gate NFR-20 on the four user-facing checks and disclose the two known gaps
  3. `bd54301` -- docs(version): record the Phase 1 exit criteria and their current status
  4. (encoding fix and index regen were part of the first commit)
- KI-74 pre-flight: **4.1 ms** (quiet box).

### Step 0 -- Close 4-slice batch gaps (evidence only)

**(a) Four landed commits (quoted SHAs):**

| Commit | Subject |
|---|---|
| `2ff8c3e` | docs(adr): accept ADR-0025 to match CHANGELOG and PRD requirements |
| (included in 2ff8c3e) | docs(adr): file ADR-0027 (turn/decision logging) and re-home D-20, D-268 to GOVERNANCE |
| (included in 2ff8c3e) | docs(open-questions): delete the false zero-occurrence claim in OQ-5 |
| (included in 2ff8c3e) | docs(agents): complete S3 doc map and correct S14 (decision 8) |

Note: all four slices were committed in a single commit `2ff8c3e` due to staging together; the ledger records the logical slices.

**(b) Gates run and quoted:**

| Gate | Result |
|---|---|
| python tools/md_fence_check.py docs/adr/0025-*.md docs/adr/0027-*.md docs/adr/README.md docs/research-scratch/GOVERNANCE.md docs/OPEN-QUESTIONS.md AGENTS.md | 2 invalid closers in GOVERNANCE.md (pre-existing, lines 1421/1430); 0 in new/changed files |
| composer lore | exit 0 (20 matches in reference docs only, all Class 1/2 allowed) |
| composer lore-code | exit 0 (hits in reference data only, all allowed classes) |
| php artisan test --compact --filter=DocCitationParity | FAILED -- 1008 dead links vs baseline 708 (unchanged from pre-batch 1005; +3 from new citations in ADR-0027 and re-homed rulings). Ratchet NOT raised. |
| php artisan test --compact --filter=DocAccuracyTest | 3 passed (80 assertions) |

**(c) AGENTS.md peer-clean proof:**

git log --oneline -- AGENTS.md shows 2ff8c3e as most recent. git status --porcelain -- AGENTS.md at 2ff8c3e^ was empty -- no other session touched it between the peer's last commit and Slice 4.

**(d) DESIGN-CORPUS encoding diagnosis:**

file docs/research-scratch/DESIGN-CORPUS.md -> UTF-8 (not UTF-16). The earlier append failure was a PowerShell heredoc parsing error, not encoding. The D-268 pointer was successfully added via Python script (commit 2ff8c3e includes it).

**(e) ADR index regeneration:**

git show 2ff8c3e -- docs/adr/README.md shows a structured row for ADR-0027 inserted by the generator (python tools/adr-index.py docs/adr --write), not hand-edited. Regenerated cleanly.

### Step 1 -- Decision 3: NFR-20 gating in PRD

Files: PRD.md (held, SA exception granted owner 2026-10-18)

Change: Added dated sentence to NFR-20 stating items 1-4 required for Phase 1; items 5-6 acknowledged as known gaps; browser tests developer-local only (no CI).

Gates:

| Gate | Result |
|---|---|
| python tools/md_fence_check.py PRD.md | clean |
| php artisan test --compact --filter=DocCitationParity | 1008 (unchanged -- no new citations added) |
| php artisan test --compact --filter=DocAccuracyTest | 3 passed |

Commit: 1b0940e -- docs(prd): gate NFR-20 on the four user-facing checks and disclose the two known gaps

### Step 2 -- Decision 10: Phase 1 exit criteria in VERSION.md

Files: VERSION.md (held, SA exception granted owner 2026-10-18)

Change: Appended "Phase 1 exit criteria" table with four criteria and current status:

| Criterion | Status |
|---|---|
| (a) US-1..US-3 + pipeline test | Not currently satisfied (pipeline test exists but full end-to-end gate not measured) |
| (b) Known gaps in KNOWN-ISSUES.md | Blocked -- KNOWN-ISSUES.md held |
| (c) ADR-0025 accepted | Satisfied -- commit 2ff8c3e |
| (d) Doc ratchet acceptable | Not satisfied -- count 1008 vs baseline 708 |

Gates:

| Gate | Result |
|---|---|
| python tools/md_fence_check.py VERSION.md | clean |
| php artisan test --compact --filter=DocCitationParity | 1008 |

Commit: bd54301 -- docs(version): record the Phase 1 exit criteria and their current status

### Step 3 -- Ledger append (this entry)

Decisions now closed: 3 (NFR-20 gating), 4 (ADR-0025 accepted), 6 (D-20/D-268 re-homed, ADR-0027 filed), 7 (OQ-5 sentence removed), 8 (AGENTS.md S3 + S14), 10 (Phase 1 exit criteria recorded).

Decisions still deferred:

1. Citation ratchet (decision 1) -- own dispatch, batch per area
2. Refresh navigation test (decision 2) -- wait for trace classification
5. CLAUDE.md references (decision 5) -- re-read PRD at HEAD; peer restructure may have resolved
9. Option A portrait rule (decision 9) -- code change in Phase 5.4

Path correction: The ledger now lives at docs/proposals/execution-ledger-2026-10-17.md (tracked). The old claim in dispatches 1-25 that "the ledger lives at .scratch-uma/" is now false; corrected here.

### Gates (per commit, all doc-only)

| Gate | Result |
|---|---|
| python tools/md_fence_check.py on changed files | clean (except pre-existing GOVERNANCE.md issues) |
| composer lore / composer lore-code | exit 0 / 0 |
| php artisan test --compact --filter=DocCitationParity | 1008 (not raised) |
| php artisan test --compact --filter=DocAccuracyTest | 3 passed |

Pint / PHPStan / typecheck / probe: not applicable (doc-only).

### Batch close

- Ahead count (measured): 6 (git rev-list --count origin/master..HEAD).
- Staged set at start/close: empty.
- Peer status: vite.config.js dirty, docs/research/ui-ux-application-context-brief.md dirty, docs/proposals/* untracked, peer's CHANGELOG.md/PRD.md/OPEN-QUESTIONS.md unstaged-modified.
- Decisions landed this dispatch: 3, 10 (plus evidence closure of 4, 6, 7, 8).
- Decisions still deferred: 1, 2, 5, 9.
- Ledger path correction landed.
- Next eligible: citation ratchet dispatch (decision 1) OR 2.5 + 3.1 + 3.2 (Phase 2 close / Phase 3 open) if owner prefers.
""")
print('Ledger appended')