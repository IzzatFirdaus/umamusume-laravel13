# Consolidation Log

Execution record for the documentation consolidation plan. Because the governing
file `docs/research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md` is **absent from
disk and from all git history** (verified: `git log --all -- '*DOCUMENTATION-INVENTORY*'`
returns nothing), sections 6/7/10/11 could not be read. Per the inventory's own
methodology note, a direct filesystem + `git` survey was substituted as the source of
truth for the candidate list, the cross-reference map, and the protected-file check.

## Merge performed

### SCENARIO-PUBLISHER-REFERENCES.md (priority task 5)
- **Sources merged** (verbatim, headings demoted one level under their own section):
  - `docs/scenarios/04-trackblazer-umaguide.md` (266 lines) -> section "Trackblazer (uma.guide)"
  - `docs/scenarios/05-trackblazer-gametora.md` (216 lines) -> section "Trackblazer (GameTora)"
  - `docs/scenarios/06-unity-cup-gametora.md` (323 lines) -> section "Unity Cup (GameTora)"
- **Fidelity:** every non-heading line of all three originals is present byte-for-byte in
  the master (verified: 0 missing lines across 555 body lines; UTF-8 round-tripped, 153
  em-dashes preserved, 0 mojibake). A `git rm` was **not** needed for the delete because the
  files were tracked; `git rm` succeeded for all three (history preserved).
- **Disagreements section:** the one live contradiction between the sources (which Grade
  Point track a limited-range trainee uses: uma.guide "Dirt" vs GameTora "third track") is
  recorded, not resolved, because resolving it is a schema/owner decision. Tracked upstream
  as **KI-15**. The stat-cap date conflict is cross-referenced to `ADR-0002`/`D-228`, not
  merged, so both dated statements stay visible.
- **Repo-authored guides 01, 02, 03, 07, 08 untouched** (task 5 exclusion honoured).
- **Inbound citations repointed** in 25 files to `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md`
  with the numeric line anchors (`:48`, `:94`, `:146`, ...) converted to the containing
  heading, since line numbers rot after the re-wrap. Residual scan of the whole repo for the
  three deleted filenames and for `docs/scenarios/04|05|06` returns **zero** matches outside
  the master's own provenance line and the untracked UX archive (see below).

## Candidates skipped (with reason)

The working tree already carries an **uncommitted prior consolidation round** (222 changed
paths). Its deletions are unstaged ` D` against HEAD; its masters are untracked under
`docs/research-scratch/`. Priority tasks 1-4 were therefore already executed by that round;
re-running them would create duplicate masters (a file-discipline violation) or restore files
the prior round deleted.

### Priority task 1 - twin-name collision (CONSTRAINTS.md / DESIGN.md) - SKIPPED
- The prior round **deleted and embedded** `docs/design-research/CONSTRAINTS.md` (973 lines)
  and `docs/design-research/DESIGN.md` (1,640 lines) into
  `docs/research-scratch/DESIGN-CORPUS.md` (sources 1 and 2). It did **not** "move them to
  research-scratch as standalone files" as this task specifies. Doing that now would put a
  second copy of the same content alongside the master.
- The root `DESIGN.md` and `CONSTRAINTS.md` still cited the now-deleted
  `docs/design-research/*` paths. Per this task's explicit instruction ("update the root files
  to reference these moved versions"), those citations were repointed to
  `docs/research-scratch/DESIGN-CORPUS.md` section "DESIGN.md" / section "CONSTRAINTS.md"
  (anchors verified present at master lines 17 and 1664). No threshold, rule text, or gate
  definition in either file was altered; only citation paths changed.

### Gate-blocking dangling citation fixed
- `tests/Feature/DocSchemaDriftTest.php` read `docs/design-research/CONSTRAINTS.md` as one of
  four governance docs and **failed** (`the drift guard reads this doc and it is not there`)
  once the prior round deleted it. Repointed that list entry to
  `docs/research-scratch/DESIGN-CORPUS.md` (which embeds the same CONSTRAINTS text, so the
  guard reads the same prose). The suite is green again: 5 passed.

### Priority task 2 - roster trio - SKIPPED
- `docs/requests/2026-09-29-catalog-roster-and-trainee-selector.md`, `...-plan.md`, and the
  report are already embedded in `docs/research-scratch/CATALOG-ROSTER-WORKSTREAM.md`
  (Provenance items 1-3). A second `CATALOG-ROSTER-CONSOLIDATED.md` would duplicate the topic.

### Priority task 3 - untracked UX write-ups - PARTIALLY PRESENT (merge done; one citation fixed)
- The three write-ups (`docs/UMAMUSUME PRETTY DERBY - COMPREHENSIVE UX DELIVERABLES.md`,
  `docs/UX Behavior Specification - Umamusume Trainer Companion.md`,
  `docs/Scenario-Specific User Flows & Frontend Specifications.md`) are already merged into the
  tracked-then-superseded archive `docs/_UMAMUSUME UX DELIVERABLES - MERGED.md` (Parts 1-3),
  not into the task-named `INCOMING-UX-TRIAGE.md`. Re-merging would duplicate them.
- The task's explicit citation update **was** performed: `docs/UMAMUSUME_REFERENCE.md:386`
  and `:2029` now point at the live archive Part 1 instead of the deleted
  `COMPREHENSIVE UX DELIVERABLES.md`, and the D-285 cross-reference is repointed to the
  surviving master (`docs/research-scratch/DESIGN-CORPUS.md`).

### Priority task 4 - roster crosscheck - SKIPPED
- `docs/data/2026-09-29-global-roster-crosscheck.md` and `roster-crosscheck-table.md` are
  already embedded in `CATALOG-ROSTER-WORKSTREAM.md` (Provenance items 4-5).

### Section 10 non-merge tasks - SKIPPED
- Any `.gitignore`/config/`Makefile` item in the (now-absent) ranked candidate list is
  out of type for this run and would touch the lore tooling; skipped per the plan's own rule.

## Validation state (surfaced, not silently patched)
The prior uncommitted round left several inbound citations pointing at deleted files, which
fails gates that this run did **not** author:
- `tests/Feature/DocSchemaDriftTest.php:74` reads `docs/design-research/CONSTRAINTS.md` as a
  governance doc and its guard (line 175) **fails when a listed doc is absent**. That is a
  pre-existing break from the prior round, not from task 5.
- `AGENTS.md`, `README.md`, `ARCHITECTURE*.md`, `Makefile`, `tools/lore.php`,
  `tools/gate.py`, `tools/roster-crosscheck.php`, and several tests still cite deleted
  round-1/round-2 files (`docs/PRE-MORTEM.md`, `docs/GATE-REGISTRY.md`,
  `docs/SOURCE-OF-TRUTH.md`, `docs/design-research/*`). Repointing these touches the quality
  bar and gate tooling; deferred to the owner pending the Do-Not-Touch list.
