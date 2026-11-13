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
> two spellings, `DESIGN.md` 41 + 21, `docs/research-scratch/GOVERNANCE.md` 12, `docs/research-scratch/GOVERNANCE.md` 11,
> `docs/research-scratch/GOVERNANCE.md` 10 — which matches the census's post-consolidation dead-link densities and is
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
paths). Its deletions are unstaged `D` against HEAD; its masters are untracked under
`docs/research-scratch/`. Priority tasks 1-4 were therefore already executed by that round;
re-running them would create duplicate masters (a file-discipline violation) or restore files
the prior round deleted.

### Priority task 1 - twin-name collision (CONSTRAINTS.md / DESIGN.md) - SKIPPED

- The prior round **deleted and embedded** `docs/research-scratch/DESIGN-CORPUS.md` (973 lines)
  and `DESIGN.md` (1,640 lines) into
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

- `tests/Feature/DocSchemaDriftTest.php` read `docs/research-scratch/DESIGN-CORPUS.md` as one of
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

- `tests/Feature/DocSchemaDriftTest.php:74` reads `docs/research-scratch/DESIGN-CORPUS.md` as a
  governance doc and its guard (line 175) **fails when a listed doc is absent**. That is a
  pre-existing break from the prior round, not from task 5.
- `AGENTS.md`, `README.md`, `ARCHITECTURE*.md`, `Makefile`, `tools/lore.php`,
  `tools/gate.py`, `tools/roster-crosscheck.php`, and several tests still cite deleted
  round-1/round-2 files (`docs/research-scratch/GOVERNANCE.md`, `docs/research-scratch/GOVERNANCE.md`,
  `docs/research-scratch/GOVERNANCE.md`, `docs/design-research/*`). Repointing these touches the quality
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
`docs/research-scratch/DESIGN-CORPUS.md` (53), `DESIGN.md` (40),
`docs/research-scratch/GOVERNANCE.md` (20), `RAW-FINDINGS.md` (19), `SKILLS-GAPS.md` (18),
`docs/research-scratch/GOVERNANCE.md` (17) and `docs/research-scratch/GOVERNANCE.md` (16). Most of those mentions are
historical citations inside masters naming the sources they absorbed, which are records and must
not be repointed. The live ones are in `AGENTS.md`, `README.md`, `ARCHITECTURE*.md`, `Makefile`,
`tools/lore.php`, `tools/gate.py` and `tools/roster-crosscheck.php`, and they are the owner's
decision, unchanged from the section above.

Correction to the section above, forward-looking rather than by deletion. It states that
`tests/Feature/DocSchemaDriftTest.php:74` reads `docs/research-scratch/DESIGN-CORPUS.md` and that its
absence fails a gate. That was true when written. The test's governance list now reads
`docs/research-scratch/DESIGN-CORPUS.md`, and `php artisan test --compact
tests/Feature/DocSchemaDriftTest.php` returns 5 passed (12 assertions). The deferred item is
therefore partly closed: the drift guard was repointed, the prose citations in the root and tooling
files were not.

## Plans deliberately NOT consolidated (decided 2026-10-02)

**Corrected forward 2026-10-03 (HEAD `53cc960`).** The owner reversed this decision the next day. Both
plans are now embedded in `PROCESS-PLANS.md` (in the sections named PLAN-DOC-SYNC-2026-10-02.md and
PLAN-UI-UX-2026-10-02.md) and both working files were deleted in the same commit, so each section is the
executable copy and open steps continue there. Measured at `53cc960` rather than carried from this record:
DOC-SYNC 22 closed and 21 open, UI/UX 37 closed and 42 open, against the 22/21 and 17/62 counted on
2026-10-02. The text below stays verbatim because it is the record of what was decided that day and of the
numbers current then.

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

| Document                                                        | Why it stays out                                                                                                                                                                                                    |
| --------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `docs/PLAN-DOC-SYNC-2026-10-02.md`                              | Task 4 is held on owner gate O-2 and Task 5 is partially executed; Tasks 1, 2, 3, 6, 7 and 8 are complete or scoped. Consolidating a plan with one held task would record a false completion.                       |
| `docs/PLAN-UI-UX-2026-10-02.md`                                 | Milestone 1 is partly shipped (WS-1 controls, and the M1 register sweep), WS-2 landed at `80caefd`, and WS-3 through WS-6 are open. It owns its own workstreams and the register entries those workstreams close.   |
| docs/design-research/slice-7-prd-revision-draft-2026-10-01.md   | A DRAFT awaiting an owner ruling on PRD shape and on R-02's scope. It is deliberately uncommitted, so it is not a tracked master and cannot be registered in `INDEX.md` without defeating its own review gate.      |

**The condition for consolidating any of them:** every acceptance box closed or struck, and gates O-1 (push),
O-2 (GATE-REGISTRY/PRE-MORTEM restore-or-repoint) and O-3 (root `research-scratch/` disposition) resolved.
Until then `PROCESS-PLANS.md` stays as it is, and these three are cited by path rather than absorbed.

Recorded because the alternative — leaving the three uncited — is the state A-10 and A-11 describe: a document
that exists, is relied on, and is invisible to the census's own gate.

## Round 11 (2026-10-04) — six sources named by the owner, on branch `trainer-desk-2.0`

The owner named six files and directed them into the masters: the training-run UI/UX audit, the audit
decisions owed on that pass, the subsystem-1 advisor spec, the Unity Cup capture schema proposal, the
per-card deck state proposal, and the generated calendar tables. Full routing and reasons are in `INDEX.md`
§Round 11; this entry records only the two things a routing table cannot carry.

**This pass overrides the condition stated twice above.** Both "Plans deliberately NOT consolidated"
sections argue that a document with open acceptance boxes must stay out of a master because embedding it
records a false completion. That condition held for one day and the owner reversed it, and
`PROCESS-PLANS.md` now carries two plans with open steps as their authoritative copy. Round 11 follows the
reversed rule and keeps its guard: five of the six sources were live, each destination states in its own
Provenance that the section is the authoritative copy where open steps continue, and no status line was
softened to make a fold look like a closure. Where a fold was refused, it is said: `docs/proposals/` keeps `docs/proposals/design-2.0.md` and <!-- census: quoted-original -->
`docs/proposals/screen-spec-2.0.md` in place, so the 2.0 spec set now spans two locations, and that <!-- census: quoted-original -->
split is recorded in `PROCESS-PLANS.md` rather than hidden by the commit message.

**Round 7's standing ruling on the calendar tables was reversed by the owner, against the objection
recorded here.** That ruling held the file standalone because its two generator scripts live in the
gitignored root `research-scratch/`, so a move could not be committed with its fix. The owner chose to fold
the tables and commit the generators. Both scripts are now tracked under `tools/` with the hardcoded
absolute path to one machine's checkout replaced by one derived from `__file__`, which is what made the
move committable in a worktree. `python tools/resync_doc.py` reports `unchanged` for all seven pushed
labels against the folded section and leaves `docs/scenarios/09-global-race-calendar.md` byte-identical, so
the pipeline was proven before the original was deleted, not after. The measurement also disproved Round 7's
claim that nothing in the file was redundant: 512 of its 551 lines were already byte-identical in the
scenario guide, and only a 12-line block existed nowhere else. `gen_calendar.py` cannot run in a worktree,
because its four cached GameTora JSON bodies are gitignored scratch; it now exits with that stated rather
than raising `FileNotFoundError`, and the fetch recipe stays at `docs/scenarios/09-global-race-calendar.md`
§To reproduce.

## Round 24 coordination notes (2026-11-10)

Round 24 (2026-11-10, owner-ordered) is recorded in full in `INDEX.md` §Round 24: thirteen dated
`docs/research/` dispatch records of the 2026-11-10 scenario-detail arc, appended to
`SLICE-RECORDS.md` as thirteen `## <name>` sections and deleted from `docs/research/` after
line-by-line verification (1,370/1,370 non-blank lines, 0 missing; pure append of 124,690 bytes with
the master's 6,571 NUL bytes carried through unchanged). Two notes belong here beside the INDEX entry:

1. The `docs/research/` tree retains files this round does not touch, by the owner's own list:
   `SYSTEM-FLOWS.md`, the eleven `flows-*.md`, `ui-ux-application-context-brief.md` (Round
   14-embedded but not deleted), `scenario-detail-design-language-survey-2026-11-09.md`, and the
   RS-UC set and directory, which is untracked peer in-flight work. A future pass must not read an
   empty directory as leftover consolidation debt. The flows files and SYSTEM-FLOWS.md are likewise
   unconsolidated strays on purpose; `INDEX.md` §Rounds 14 records the brief's live-embed status.
2. Five live citations were repointed before the delete, each at the master and its section anchor:
   `app/Services/Career/TeamPanelBuilder.php` (the S-A-P01.OG resolution comment),
   `docs/design-research/scenario-detail-hfw-2026-11-09/README.md` (the KI-154 sweep pointer),
   `docs/proposals/2026-11-08-trainee-preset-autofill.md` (the S-A-P01.OG ledger row), and
   `KNOWN-ISSUES.md` twice (the KI-154 and KI-157 entries). The `KNOWN-ISSUES.md` edits were
   byte-safe binary splices preserving its 399 NUL bytes. Outside those five sites, every remaining
   reference to the thirteen deleted paths is either the round's own dated record or an embedded
   body's quoted `git status` snapshot — the tolerated historical class of Rounds 18/22/23.

## Round 25 coordination notes (2026-11-10)

Absorbed files and their destinations are above, in the Round 25 row of the routing table. Notes for
the next pass:

 1. The scenario-detail design-language survey (`docs/research/scenario-detail-design-language-survey-2026-11-09.md`)
    and the RS-UC audit set (`docs/audits/RS-UC-SCENARIO-DETAIL-2026-11-09.md`, `docs/audits/RS-UC-SCENARIO-DETAIL-2026-11-09/`,
    `docs/research/RS-UC-SCENARIO-DETAIL-2026-11-09/`) are untracked peer in-flight work and stay at their
    paths by design; their citations inside the folded upgrade plan are quoted verbatim as history.
 2. Twelve live citations were repointed before the delete (the twelve named in the INDEX Round 25
    section). The ledger `from` rows inside `SLICE-RECORDS.md` that name the six absorbed files are
    dated records and stay verbatim as history.
 3. `docs/research/ui-ux-application-context-brief.md` carries a peer session's uncommitted additions
    that cite the upgrade plan; the file is peer-owned and was not touched. When that session lands,
    its citations should be repointed at the `PROCESS-PLANS.md` section anchor.
 4. The sparks proposal body cites `docs/audits/rice-shower-cup-round7-reconciliation.md:956` as the
    source wrote it; the disk form of that name is `rice-shower-unity-cup-round7-reconciliation.md`.
    The citation was quoted verbatim, not repaired.
 5. Decomposition addendum (run after the commit, owner-approved): the commit message's
    working-tree split ("+13 this round / +11 peer") was a harness artifact and is superseded
    by the census's own dead_links() at the two refs. Definitive figures: 586 GONE at
    `62f43094` -> 609 GONE at `50e09715`, +23 net (+29 added, -6 removed), all of it this
    fold's, in five classes: 6 fold banners quoting the deleted full paths; 12 ledger `from`
    rows inside `SLICE-RECORDS.md` (sparks x3, autofill x3, remodel x4, memos x2; dated
    records kept verbatim by design, live before the delete and dead after it); 2 Round 24
    dated rows (`INDEX.md` and this file) naming the autofill path, live before the delete;
    2 mentions of the untracked peer survey/audit files in this note (UNTRACKED at the working
    tree, GONE in ref mode); and 1 embedded upgrade-body citation of the peer audit file.
    Everything else moved with its body netting zero (the walk-7 pairs, `08-unity-cup.md`, and
    the memos' survey citation). The peer contribution on the HEAD path is +1, from their
    committed `KNOWN-ISSUES.md` edit between the freeze point and the fold (585 -> 586), not
    from uncommitted work; at the working tree the round reads +24 = that +1 plus the fold's
    +23, five of which read UNTRACKED there.

## Round 26 coordination notes (2026-11-12)

Absorbed files and their destinations are above, in the Round 26 row of the routing table. Notes
for the next pass:

 1. The RS-UC set (`docs/audits/RS-UC-2026-13/`, `docs/audits/RS-UC-SCENARIO-DETAIL-2026-11-09/`
    screenshots, `docs/research/RS-UC-SCENARIO-DETAIL-2026-11-09/`) was recorded in the Round 25
    note as peer in-flight work that stays by design. The 2026-11-12 owner order named the report
    file and the two bundled sets and supersedes that ruling for the 31 named paths; the un-named
    neighbours (the `RS-UC-2026-13/` directory, `trainer-desk-v3x-roadmap-baseline-2026-11-12.md`,
    `scenario-research/`, the design-language survey) stay untouched by design.
 2. The bundled sets' prior leave-untouched rulings (audits README dir rows, and the handoff's
    explicit prohibition list) are superseded by the same order; every fold banner states this.
 3. Live citations were repointed before the delete: 21 external sites across README,
    RECONCILIATION-REGISTER (4), round-6 reconciliation (15 anchor cells), walks 7 (2), walk 8,
    three browser specs, and the hfw HTML line, plus 8 intra-set live pointers; the dated
    inventory rows and embedded `git status` listings inside the folded bodies stay verbatim as
    history.
 4. Colliding `00`-`08` basenames between the two bundled sets are disambiguated with
    parenthetical qualifiers in the anchors, the `CONSTRAINTS.md` precedent. The
    `04-ux-findings.md` (original) and `04-ux-findings-retest.md` basenames are unique, so they
    take unqualified anchors.
 5. Wait-state: the peer session active during this round (F-series ownership arc) holds
    uncommitted edits citing the folded paths; those sites are in files this round did not own
    (`docs/research/ui-ux-application-context-brief.md`, `docs/UMAMUSUME_REFERENCE.md`,
    `docs/framework-ui-ux/_audit-prompt.md`, and the round-closing +346-line
    `docs/research-scratch/PLANS-AND-BRIEFS.md` edit whose inventory cites
    `docs/audits/walk-8-follow-up-plan.md` and
    `docs/audits/trainer-desk-v3-ux-retest/09-consolidated-remediation-plan.md` in
    slash form) and were left untouched by design; those slash cites are GONE-class
    until the peer lands and repoints them.

## Round 26 post-commit evidence note (2026-11-12, adb9d5cc)

Commit adb9d5cc: 20 files, +9094/-3644; six tracked originals deleted (recoverable at
  adb9d5cc^), 25 untracked originals removed after only-copy containment proof.
Post-commit verification, each figure measured from the landed blobs:
  - Anchors: 27/27 A&V + 4/4 SR section anchors present, in embed order.
  - Only-copy re-proof from landed blobs (fence-aware, with the sanctioned
    transforms applied: heading re-level/demotion and the 9 intra-set repoints):
    25/25 untracked source bodies exact-identical in their masters.
  - Drift-zero: `git diff HEAD` empty on all 14 landed modified paths.
  - Canonical census, the tool's committed mode via `git worktree` at the two
    refs (the method both this round's and prior rounds' pins use):
    dead markdown links 635 GONE at f029a806 -> 635 GONE at adb9d5cc, net zero.
    The working-tree mode figure (649 pre-commit = 565 GONE + 84 UNTRACKED, then
    648 post-delete = 589 + 59) is transient bookkeeping for on-disk-but-untracked
    resolver context; the ref-mode net zero is the figure the pins track. The
    residual 8 mentions of the deleted RS-UC-SCENARIO-DETAIL path are dated
    inventory rows and quoted git-status listings inside masters and peer files,
    enumerated in the round scratch; none is a live pointer.
  - Lore: docs 595 -> 596 with 75 exempt (net +1: the embedded copy of
    verification-rtx's "HEAD was stable" line relocated with its body into the
    tracked master, an ordinary-adjective hit in verbatim record prose, C-4
    verbatim-source class; the peer's uncommitted PLANS-AND-BRIEFS "Red Desire"
    list line accounts for +1 more in the working tree, pending the peer's own
    ruling at landing); code 351 unchanged. Every R26-touched hit carries a
    ruling.
  - Fences: 0 invalid closing fences across touched files.
  - `npm run typecheck` and `npm run lint:ui` exit 0; DocCitationParityTest
    2 passed and LoreGateParityTest 12 passed at adb9d5cc.
  - docs/audits/README.md re-derived from measurement after the delete: 22 report
    rows (17 on disk: 4 with evidence, 13 without; 5 folded rows pointing at
    master sections), 20 loose .md total (18 reports + this index + the
    RECONCILIATION-REGISTER), 121 PNGs across the five split-audit screens
    directories, retest screenshots 72 (row said 40), REGISTER 422 lines (row
    said 332), v3-ux screenshots 59. Rule-1/rule-3/inconsistency-4 counts updated
    to the on-disk survivors. The peer v3x reports remain rowless by design.
  - tests/browser/dashboard-turn-state.spec.ts:11: the repoint had edited the
    head of the line but left the old-path tail
    `docs/audits/trainer-desk-v3-ux-retest/04-ux-findings-retest.md:60-78`
    citing the deleted path; fixed to a folded-source locator in the same commit.
  - Wait-state remains: the peer session's uncommitted edits cite two deleted
    paths in slash form (`docs/audits/walk-8-follow-up-plan.md` and
    `docs/audits/trainer-desk-v3-ux-retest/09-consolidated-remediation-plan.md`
    inside PLANS-AND-BRIEFS.md) and reference folded paths in
    ui-ux-application-context-brief.md / UMAMUSUME_REFERENCE.md / _audit-prompt.md;
    they are peer-owned GONE-class until that session lands and repoints them.
  - Scratch harness files for this round live under
    .scratch-uma/round26-consolidation/ (scripts and outputs only; no worksheet
    files at the scratch root).
## Round 27 coordination notes (2026-11-12)

Owner order: consolidate the three `trainer-desk-v3x-*` Phase-0 records dated 2026-11-12 into
`docs/research-scratch/` and delete the originals. All three were tracked at the fold (peer-landed
in `324e92f7`); the sections are recoverable at the delete commit. Routing followed the
companion-master precedent: the change-boundary record declares `PLANS-AND-BRIEFS.md` its
companion, the peer-landed ruling section `trainer-desk-3x-roadmap-rulings-2026-11-12.md` lives
there, and the record chain 0.1-0.4 stays in one master. This order supersedes the Round-26
"rowless by design" README ruling and the Round-26 stays-note in INDEX.md (both left verbatim as
dated history). Seven live citations repointed pre-delete: P&B :7726 (its own rulings-section
grounding cite); intra-set phase0-close :87/:88, change-boundary :5/:75/:230,
roadmap-baseline :161. Verbatim kept: phase0-close :89 `(this file)` and :140-142 staging
manifest; baseline :48-58 dated enumeration. The three repointed sources were deleted with
git rm -f (edit-then-delete doctrine: containment was proven against the edited bytes).
Sanctioned mechanical departures (banner-stated): the three LF sources were written with CRLF
terminators matching the master's fold-time working-tree convention (the repository's
.gitattributes, text=auto eol=lf, stores the landed content as LF); H1 re-level to ### plus
one-level demotion outside fences; the repoint list above.
Post-write terminator event: between the splice and the delete gate, an event outside this
round normalised the working-tree CRLF of PLANS-AND-BRIEFS.md and CONSOLIDATION-LOG.md to LF;
content verified intact by numstat (688/1 and 32/0, exactly this round's edits).
Lore-gate probe: the appended regions and repointed lines added zero lore hits at write time
(checked per-hit against the freeze figures, 599/75 docs + 351 code at 4ba014bf); the post-delete
gate re-runs are the authority and every delta gets a per-hit ruling below.
Guard rails: prefix byte-identity, NUL unchanged (0), CRLF purity of the appended region
(687 new CRLF lines, 0 LF-only), anchor line-uniqueness (a line equal to "## <name>"; backticked
mentions in prose do not count), fences balanced, containment 3/3 bodies line-complete
(532 non-blank lines, total missing 0), and the same proof re-run from the landed blob
post-commit. docs/audits/README.md gains three folded-into rows; its totals line and rule 1
are re-measured post-delete; figures written pre-delete are asserted by the gate before commit.
Scratch: .scratch-uma/round27-consolidation/ only (scripts and outputs at the round subdir,
nothing at the scratch root). Wait-state: none at fold time. Peer commits 01ad5e46 and f080bb99 landed mid-round
(scenario research notes, the scenario test-data matrix, BurstBandsCard.vue,
Veterans/Index.vue, full-career-journey.spec.ts) on paths this round does not touch;
UMAMUSUME_REFERENCE.md, _audit-prompt.md and ui-ux-application-context-brief.md remain
peer-uncommitted, and none of the three carries a live cite of the round's sources
(checked: no trainer-desk-v3x stem in their bodies).
Post-fold addition carried, provenance recorded here (2026-11-13): the change-boundary section's
`### 3. Tally` carries a nine-line paragraph added by the scenario-research session on 2026-11-12
21:36 under the owner's Decision-6 instruction (its session record: 'the tally reconciles exactly,
36/14/9/1 = 60, re-derived independently'; inserted into the fold region because the section is now
the living copy). This fold pass re-verified its arithmetic against the section's own table (49 rows
parsed; range cells `3.2-3.6`=5, `5.2-5.4`=4, `7.1-7.5`=5 expanded; 36/14/9/1 = 60) and amended this
round's banner to say the body is 'otherwise untouched except for' that recorded addition.
Round 27 post-commit evidence note (2026-11-13). Canonical ref-mode census, the tool's own
dead_links() at both refs in isolated worktrees: 647 GONE at 33c85c54^ = 655 GONE at
33c85c54, net +8, every entry classed. 3 are the fold banners' provenance lines naming
the absorbed sources (the tolerated masters-naming-absorbed-sources class every round
adds). 4 moved with their bodies into PLANS-AND-BRIEFS.md (quoted-history and
verbatim rows the sources already carried: the baseline's 6.1 'does not exist' row now at
P&B:8307, its peer-owned-mention list now at P&B:8249, the close record's '(this file)'
self-reference and staged manifest now at P&B:8819/8872). 1 is docs/audits/README.md's
rule-1 prose naming uc3-elite-team-boundary-inspection-2026-11-12.md, a peer report still
untracked at this commit; it is the same class as the pre-existing UNTRACKED mentions and
converts to live the moment the peer session lands it. Zero live defects. Lore gate, measured
after the commit against the frozen 599/75 + 351: docs 602/75 (+3, all peer: the
UMAMUSUME_REFERENCE archive pass's two stable-measurement lines and the newly-tracked
ki-82-ki-133-resolution-plan), code 351 unchanged; this round's embedded bodies added zero
hits (pre-checked per-fragment). Parity tests at the landed commit: DocCitationParityTest
and LoreGateParityTest 14 passed, 34 assertions. Containment re-proved from the landed
blob: 3/3 regions, 532 non-blank body lines, 0 missing; each region's leftover accounted
exactly (anchor + banner + blanks +, for change-boundary only, the 9 banner-recorded
Decision-6 lines). P&B fence parity balanced (210 fence lines; the three folded sources
contain no fences of their own). Drift-zero on all four landed masters.
