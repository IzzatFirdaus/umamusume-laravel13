# Consolidation Log

Execution record for the documentation consolidation plan. Because the governing
file `docs/research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md` is **absent from
disk and from all git history** (verified: `git log --all -- '*DOCUMENTATION-INVENTORY*'`
returns nothing), sections 6/7/10/11 could not be read. Per the inventory's own
methodology note, a direct filesystem + `git` survey was substituted as the source of
truth for the candidate list, the cross-reference map, and the protected-file check.

> **Corrected 2026-10-02.** The claim above that the inventory is "absent from disk" is wrong. It is on
> disk at `research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md` (644 lines), inside the repo-root
> `research-scratch/` folder that `.gitignore:87` ignores. The verification recorded above
> (`git log --all -- '*DOCUMENTATION-INVENTORY*'`) reads git history only and cannot see an ignored,
> uncommitted file, so a history-only probe was reported as disk absence. §6 "Cross-reference map"
> (`:353`) was therefore available to the consolidation and was not read. The substituted filesystem
> survey stands as the record of what was actually merged; what changes is only that the sections said
> to be unreadable are readable.
>
> What the recovered sections yield, read 2026-10-02: §6's cross-reference map is a **pre-consolidation**
> record (its adjacency list `research-scratch/docinv_xref.json` shows all fourteen masters at 0 inbound,
> because they did not exist on 2026-09-30), so it cannot cross-check `INDEX.md`'s absorption claims. What
> it does carry is each absorbed source's reader pressure at map time — `CONSTRAINTS.md` 45 + 30 across its
> two spellings, `DESIGN.md` 41 + 21, `docs/GATE-REGISTRY.md` 12, `docs/PRE-MORTEM.md` 11,
> `docs/SOURCE-OF-TRUTH.md` 10 — which matches the census's post-consolidation dead-link densities and is
> the baseline the citation repointing works from. §7's rot scan (391 line-citations: 155 resolvable, 19
> stale, 33 broken) used a different method than the current census and its counts are not comparable to
> the 512 dead links now recorded; §10's candidate list has since been executed as Rounds 1–4; §11's
> do-not-touch list is consistent with the standing set `INDEX.md` now governs.

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

## Census, added 2026-10-02

The inventory this log was written against is gone from disk and from all git history, so the
filesystem survey substituted for it is now the only source of truth of its kind. A survey that
lives in prose goes wrong the moment history moves, which is exactly what happened to the
paragraph above it. `tools/doc_census.py` is the reproducible replacement: run
`python tools/doc_census.py` and it prints, at the sha it names, the tracked markdown count by
directory, every master with its line total and inbound citations, the files outside the masters,
and dead markdown links split into GONE (cited and absent from disk) and UNTRACKED (on disk, not
in git).

Measured at the sha this commit lands on: 72 tracked markdown files, 16 masters totalling 28,351
lines, 23 documented files outside the masters excluding `docs/adr/` and `.ai/**`, and 495 dead
links, of which 488 are GONE and 7 UNTRACKED. The most-cited dead targets are
`docs/design-research/CONSTRAINTS.md` (53), `docs/design-research/DESIGN.md` (40),
`docs/GATE-REGISTRY.md` (20), `RAW-FINDINGS.md` (19), `SKILLS-GAPS.md` (18),
`docs/PRE-MORTEM.md` (17) and `docs/SOURCE-OF-TRUTH.md` (16). Most of those mentions are
historical citations inside masters naming the sources they absorbed, which are records and must
not be repointed. The live ones are in `AGENTS.md`, `README.md`, `ARCHITECTURE*.md`, `Makefile`,
`tools/lore.php`, `tools/gate.py` and `tools/roster-crosscheck.php`, and they are the owner's
decision, unchanged from the section above.

Correction to the section above, forward-looking rather than by deletion. It states that
`tests/Feature/DocSchemaDriftTest.php:74` reads `docs/design-research/CONSTRAINTS.md` and that its
absence fails a gate. That was true when written. The test's governance list now reads
`docs/research-scratch/DESIGN-CORPUS.md`, and `php artisan test --compact
tests/Feature/DocSchemaDriftTest.php` returns 5 passed (12 assertions). The deferred item is
therefore partly closed: the drift guard was repointed, the prose citations in the root and tooling
files were not.

## Plans deliberately NOT consolidated (decided 2026-10-02)

A request arrived to fold `docs/PLAN-DOC-SYNC-2026-10-02.md` and `docs/PLAN-UI-UX-2026-10-02.md`
into the masters. Both stay where they are, and the reason is measurable rather than stylistic.

- Both are open work: 21 and 62 unchecked steps respectively, against 22 and 17 checked.
  `INDEX.md` routes each of them to its live path in `docs/`, while the one plan this repository
  has already finished consolidating, the scratch-tree reorganization, routes to
  `PROCESS-PLANS.md` and its original file is gone. Routing target is the record of state: a plan
  that is still being executed is not a source that has been absorbed.
- File discipline says consolidated sources are deleted **after verification**, and there is no end
  state to verify while checkboxes move. Copying them in without deleting the originals would put
  two live accounts of one task list in the tree, and the second is stale the next time either plan
  is committed. That duplication is the failure this whole pass exists to remove.
- The DOC-SYNC plan is load-bearing tooling documentation right now, not just a to-do list: the
  citation gate `tests/Feature/DocCitationParityTest.php` names its Task 5 triage rule in a code
  comment as the procedure for lowering the dead-citation count. Moving the text would break the
  citation a gate depends on.
- Concurrency is the immediate hazard. At the time of this decision `5b21582` (the DOC-SYNC
  disposition commit) was the tip of `master`, so the owning session was working from these files
  within minutes of the request.

**Condition to consolidate:** every unchecked step closed or explicitly struck, owner gates O-1 to
O-3 resolved, and the disposition recorded in the plan itself. Then embed each under its own
`## <FILENAME>.md` wrapper with headings demoted one level, verify line-by-line that no non-heading
source line is missing, update the Provenance list, delete the source, and repoint the two
`INDEX.md` routing rows to the master. Expected destination is `PROCESS-PLANS.md`, which already
carries the completed plan precedent.

Census at this commit: 615 dead citations against the `DOC_CITATION_BASELINE = 621` ratchet. This
section adds none: the two plan paths it names exist, and they are cited as plain text rather than
as links to be followed.

## Plans deliberately NOT consolidated (2026-10-02)

Three plan-shaped documents sit outside the master set on purpose. Consolidating any of them now would freeze
a status that is still moving, and `PROCESS-PLANS.md` is the wrong home for a document whose acceptance boxes
are open.

| Document | Why it stays out |
|---|---|
| `docs/PLAN-DOC-SYNC-2026-10-02.md` | Task 4 is held on owner gate O-2 and Task 5 is partially executed; Tasks 1, 2, 3, 6, 7 and 8 are complete or scoped. Consolidating a plan with one held task would record a false completion. |
| `docs/PLAN-UI-UX-2026-10-02.md` | Milestone 1 is partly shipped (WS-1 controls, and the M1 register sweep), WS-2 landed at `80caefd`, and WS-3 through WS-6 are open. It owns its own workstreams and the register entries those workstreams close. |
| docs/design-research/slice-7-prd-revision-draft-2026-10-01.md | A DRAFT awaiting an owner ruling on PRD shape and on R-02's scope. It is deliberately uncommitted, so it is not a tracked master and cannot be registered in `INDEX.md` without defeating its own review gate. |

**The condition for consolidating any of them:** every acceptance box closed or struck, and gates O-1 (push),
O-2 (GATE-REGISTRY/PRE-MORTEM restore-or-repoint) and O-3 (root `research-scratch/` disposition) resolved.
Until then `PROCESS-PLANS.md` stays as it is, and these three are cited by path rather than absorbed.

Recorded because the alternative — leaving the three uncited — is the state A-10 and A-11 describe: a document
that exists, is relied on, and is invisible to the census's own gate.
