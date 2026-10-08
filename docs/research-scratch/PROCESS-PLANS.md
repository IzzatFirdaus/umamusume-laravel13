# Process Plans and Reports

## Provenance

This document consolidates the following source files verbatim (no summarization, no deduplication):

- `docs/requests/2026-10-01-scratch-tree-reorganization-plan.md`
- `docs/requests/reports/2026-09-30-c5-down-enforcement-gap.md`
- `docs/PLAN-UI-UX-2026-10-02.md` (embedded 2026-10-02, working file deleted 2026-10-03; this section is live)
- `docs/PLAN-DOC-SYNC-2026-10-02.md` (embedded 2026-10-03 and the working file deleted in the same commit; this section is live)
- `PLAN.md` (root frontend slice plan, 712 lines, added 2026-10-03 in Round 8; see the section "PLAN.md (root frontend slice plan and owner rulings)")
- `docs/proposals/trainer-advisor.md` (149 lines, 10 headings), added 2026-10-04 in Round 11 as the
  section `## trainer-advisor.md` at the end of this file. Subsystem 1 of the Trainer Desk 2.0 program:
  the `BuildTarget` column and the headless `TrainerAdvisor` ranking engine. Status is **Spec, awaiting
  owner review**, no code exists, so §6 owner-input items and §9 deferred slices are open steps that
  continue in that section. `ADR-0020` Links names this spec; its pointer was repointed here, and the
  sibling 2.0 documents `docs/proposals/design-2.0.md` and `docs/proposals/screen-spec-2.0.md` stayed in
  `docs/proposals/`, so the 2.0 spec set is now split across two locations. That split is the cost of the
  fold and it is recorded rather than hidden.
- `.scratch-uma/REORGANIZATION_PLAN.md` (71 lines), added 2026-10-06 as the section
  `## REORGANIZATION_PLAN.md` at the end of this file: a second scratch-tree reorganization plan, this one
  for `.scratch-uma/` itself. It is the same class as the 2026-10-01 plan above and belongs beside it, with
  one conflict recorded rather than resolved: the 2026-10-01 plan's §0 table rules `.scratch-uma/`
  **DO NOT MOVE** on measured grounds (58 citing lines across 18 tracked files, with `skills.json`,
  `scenarios.json` and `character-cards.json` the bulk of them), while this plan would move those same
  bodies into `data/json/` and every other bucket it names. A move that breaks 58 live citations is not a
  reorganization, it is a citation deletion, so the 2026-10-01 ruling stands unless the owner reverses it
  and the citations are repointed first. Read this section as the proposal that has to answer that, not as
  an approved move. The source was untracked and gitignored, so the embedded copy here is now its only
  durable one.

**Round 8 (2026-10-03), owner-authorized:** the root `PLAN.md` is now a pointer stub whose full text is
the PLAN.md section above. New plan content continues in the live sections here, as `INDEX.md` §File
discipline directs. The 2026-10-02 routing rows in `INDEX.md` that named the deleted working files were
left as dated records, consistent with the decision record above.

**Decision record (2026-10-02), superseded by the paragraph below.** The UI/UX snapshot broke the
convention on purpose: file discipline embeds a source and deletes it after verification, but that plan
still had 42 open steps at capture, so there was no finished state to absorb and the working file had to
stay executable. The section carried its capture sha and counts, which makes divergence visible instead of
silent. The DOC-SYNC plan was deliberately not embedded: its 37 dead citations against a
31-citation headroom would have pushed `tests/Feature/DocCitationParityTest.php` from 590 to 627, past its
then-ceiling of 621. Repointing those refs was that plan's own Task 5 and Task 6.

**Correction and current state (2026-10-03, HEAD `53cc960`).** Both plans are now embedded here and both
working files are deleted, so each section in this master is the executable document and open steps
continue here. The embed-then-delete pair moves the embedded body's dead citations into this file instead
of duplicating them. Measured with `python tools/doc_census.py`: **708 before this pass, 705 after.** The
drop is 3, not the 9 a full repoint would give, because two choices were deliberate: the 2026-10-02 decision
record in `CONSOLIDATION-LOG.md` keeps its four dated citations verbatim under an appended correction rather
than being edited in place, and the two routing rows in `docs/research-scratch/INDEX.md` still name the
deleted paths, held because that file is the follow-up. The 621 ceiling named above is no longer the live
number: `53cc960` promoted five markdown files into `docs/research-scratch/`, and
`DOCUMENTATION-INVENTORY-2026-09-30.md` alone carries 142 dead citations, which is what moved the census from
the 584 measured on 2026-10-02 to the 708 measured at `53cc960`. The ratchet test is untouched in this
commit: the threshold raise that keeps it green is separate, uncommitted work by another session, and no task
here raises a threshold to make a gate pass.

**Placement note:** The C-5 enforcement gap file (`2026-09-30-c5-down-enforcement-gap.md`) was listed in Group A in the original consolidation plan. It is placed here in Group F (Process/Plans) because it is an engineering process finding about an enforcement gap, not governance or register material. This decision preserves the intent that Group A contains binding rules and gate definitions, while Group F holds process analyses and implementation plans.

---

## 2026-10-01-scratch-tree-reorganization-plan.md

### Scratch-tree reorganization implementation plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development` (recommended) or `superpowers:executing-plans` to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax. Do not start Task 3 until Task 1 returns all-clear and the owner has answered §7.

**Goal:** Sort the loose files in `research-scratch/` into named buckets and repoint every live citation to them, while leaving the four other named directories untouched on documented grounds.

**Architecture:** One disposable Python tool owns the whole operation: classify, prove citations, move, journal, rewrite, verify. It moves only files no tracked document cites, and it rewrites only citation lines a reader would actually follow, because the rest of them record where evidence sat at measurement time.

**Tech Stack:** Python 3.12 stdlib (`python`, not `python3`, on this box), `git -c core.quotepath=false`, `make lore`, Pest via `php artisan test --compact`.

**Tool:** `research-scratch/scripts/reorg_scratch.py`, created 2026-10-01 and verified three ways: classifier self-test 7/7; `--scan` reporting 53 candidates, 44 movable, 9 held; and the rewrite path dry-run against a synthetic mapping for `research-scratch/data/json/character-cards.json`, which found 6 citing files, refused `KNOWN-ISSUES.md` at the guard, and classified the 8 hit lines in the first three files as LIVE 3 / RECORD 2 / UNKNOWN 3, editing nothing. No move has been performed.
**Spec:** this file. The evidence it argues from is `docs/research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md` §10-§11 and `docs/GATE-REGISTRY.md` G-60.

#### Global Constraints

- Forbidden-edit globs in force this session, verbatim: `app/**`, `resources/**`, `routes/**`, `database/**`, `tests/**`, `composer.json`, `package.json`, `vite.config.js`, `PLAN.md`, `docs/design-research/**`, `docs/scenarios/**`.
- Inventory §11 do-not-touch: `docs/frontend-review/2026-09-28/` in full ("a dated evidence set, and 45 percent of the inventory. Editing one capture invalidates the audit that produced it"); the twelve hubs with 10+ inbound ("Renaming or merging any of them rewrites citations across a fifth of the corpus").
- Lore gate C-4 applies to every line this plan writes. Umamusume are a humanoid race; no equine vocabulary in copy or framing.
- No `git add .` and no `git add -A`. Stage by explicit path.
- No commit without the owner's authorization for that commit. One authorization, one commit.
- Never `cp` or move a WAL-mode SQLite file while any session may hold it: the observed failure modes are malformed, missing-table, and silently empty.
- Every enumeration runs with `git -c core.quotepath=false`; non-ASCII names are otherwise quoted and silently dropped from counts.
- Every count recorded here is measured at HEAD `6227417`, dated 2026-10-01. While this plan was being written HEAD moved to `9b63e8a` and reached 25 commits past `89675e6`, the session-start sha. Re-measure before acting on any number above.

---

#### 0. What the project documentation decides first

The request named five directories. Measured inbound citation load from tracked files, one command each:

```bash
git -c core.quotepath=false grep -c -- "<path>" -- ':!research-scratch' | awk -F: '{s+=$NF} END {print s}'
```text

| Directory                              | Files      | Tracked       | Citing files      | Citing lines   | Disposition       | Why                                                                                                                   |
| -------------------------------------- | ---------- | ------------- | ----------------- | -------------- | ----------------- | --------------------------------------------------------------------------------------------------------------------- |
| `research-scratch/` root               | 53 loose   | 0 (ignored)   | 20                | 51             | **REORGANIZE**    | loose working files; 44 carry no citation                                                                             |
| `research-scratch/data/`               | 87         | 0             | in the 20 above   | in the 51      | leave             | cited as live command input (`php -r`, `node -e`, `wc -c`)                                                            |
| `.scratch-uma/`                        | 181        | 0 (ignored)   | 18                | 58             | **DO NOT MOVE**   | provenance evidence; `skills.json` 53, `scenarios.json` 50, `character-cards.json` 31 citing lines; 72 SQLite files   |
| `docs/frontend-review/2026-09-28/`     | 143        | 143           | 4                 | 5              | **DO NOT MOVE**   | inventory §11; dated capture set, 94 txt + 46 png                                                                     |
| `docs/design-research/verification/`   | 19         | 17            | 16                | 34             | **DO NOT MOVE**   | slice records, the highest citation density per file in the group; inside the forbidden glob                          |
| `docs/design-research/_scratch/`       | 67         | mixed         | 4                 | 8              | leave             | already the ignored scratch path G-60 names                                                                           |
| `docs/design-research/mockups/`        | 29         | mixed         | 0                 | 0              | **OWNER**         | no inbound citations; fenced only by the directory glob                                                               |
| `docs/design-research/prototypes/`     | 8          | mixed         | 1                 | 1              | **OWNER**         | one inbound citation; fenced by the glob                                                                              |
| `docs/design-research/` top level      | 16         | 16            | hubs              | 41 + 45        | **DO NOT MOVE**   | holds both `DESIGN.md` and `CONSTRAINTS.md` twins                                                                     |

Read this as the plan's central finding: of roughly 600 files in the named directories, the documentation supports moving **44**, and those 44 need no citation rewrite at all, because a file nothing cites cannot break a citation by moving. The remaining ~560 are either disposable-but-cited evidence or fenced corpus. A blanket "move everything and auto-update all references" pass would rewrite dated provenance statements into paths that did not exist when the measurement was taken, which is the falsification the consolidation's §6 rows already ruled against.

---

#### Task 1: Preflight, and prove the tree is quiet

**Files:** none modified. Reads only.

**Interfaces:**

- Produces: `HEAD` sha, a tracked-state verdict, and a baseline gate result that Tasks 5 and 6 compare against.

- [ ] **Step 1: Confirm no other session is committing.** This is the concurrency escalation in `docs/design-research/SESSION-CONSOLIDATION-2026-09-30.md` §6: peers landed 12 commits during one read-only pass earlier. Bulk moves during live peers guarantee citation churn.

```bash
git log --oneline -1
git rev-list --count <session-start-sha>..HEAD
```text

Expected: the second number is 0. If it is not, stop and re-run Task 1 later or on an isolated worktree.

- [ ] **Step 2: Confirm tracked state is clean.**

```bash
git -c core.quotepath=false status --porcelain --untracked-files=no
```text

Expected: empty output. Any ` M ` line is someone's uncommitted work in a shared tree. Stop and name the file.

**Observed at plan-writing time, `9b63e8a`: this gate fails right now.** Five tracked files are dirty and none of them is this plan's work: `app/Services/DataPipeline/PipelineRunner.php`, `config/uma.php`, `database/seeders/DatabaseSeeder.php`, `database/seeders/UmamusumeSeeder.php`, `phpunit.xml`. A peer session is mid-change in application code. Do not run Tasks 3 through 5 until that clears.

The cost of skipping this gate is not hypothetical. The authorized append to `docs/design-research/SESSION-CONSOLIDATION-2026-09-30.md` §6 left this session uncommitted, on the owner's no-commit fence, and a peer swept it into `9b63e8a`, whose message is about KI-47 and KI-48. The line survived and is in `HEAD`; its provenance is now attributed to an unrelated commit.

- [ ] **Step 3: Record the baseline gates.**

```bash
make lore
php artisan test --compact
vendor/bin/pint --test --format agent
```text

Expected: `make lore` prints only hits you have already ruled on (the deprecated-PDF review's §4.1 notes the folder is invisible to both greps); tests green; Pint passed. Paste the three outputs into the run log. If the baseline is not green, do not start Task 3: you could not tell your own damage from a pre-existing break.

- [ ] **Step 4: Verify the ignore rules are what the plan assumes.**

```bash
git check-ignore -v research-scratch .scratch-uma
```text

Expected: `.gitignore:87:/research-scratch/` and `.gitignore:88:/.scratch-uma/`. The tail of `.gitignore` carries the UTF-16/NUL defect in inventory §10 item 9, so trust `check-ignore` over reading the file.

---

#### Task 2: Classify and review the manifest

**Files:**

- Create: `research-scratch/reorg/manifest.json` (tool output, ignored)

**Interfaces:**

- Consumes: `research-scratch/` root listing.
- Produces: `manifest.json` with `moves[]` (`from`, `to`, `bucket`, `cited_by`, `movable`) and `blocked[]`. Task 3 reads it.

- [ ] **Step 1: Run the classifier self-test.** It is the only guard against rewriting a historical record, so it runs before any output is trusted.

```bash
python research-scratch/scripts/reorg_scratch.py --self-test
```text

Expected: `self-test: 7/7 classifier cases pass`, exit 0. On any FAIL, fix `classify_line()` first; do not proceed.

- [ ] **Step 2: Scan.**

```bash
python research-scratch/scripts/reorg_scratch.py --scan
```text

Expected at `6227417`: `movable 44 / candidates 53 / refused-paths 0`, printed per file. The nine held names, each held because a tracked document cites its basename: 5 `.py` (`checklinks.py`, `final_measure.py`, `gates.py`, `gen_calendar.py`, `resync_doc.py`) and 4 `.md` (`calendar-tables.md`, `global-race-sources.md`, `scrape-game8-scenarios.md`, `scrape-training-heuristics.md`). Every one reports `cited by 1`.

Bucket arithmetic to check the run against: 26 `.py` = 21 moved + 5 held; 23 `.md` = 15 drafts moved + 4 reports + 4 held; `out/` = 4 (`docinv_xref.json`, `link-report.txt`, `metrics.txt`, `sec2-secondsource.tsv`). 21 + 15 + 4 + 4 = 44 moved, 53 candidates, 9 left at root.

- [ ] **Step 3: Read every HELD line and decide it.** For each, open the citing line and classify it by eye against the tool's rule: would a reader follow this path to re-run something (LIVE, rewrite the citation and then the file may move), or does it state where evidence sat at measurement time (RECORD, leave the file exactly where it is). Record the verdict per file in the run log. Default is leave.

- [ ] **Step 4: Confirm the bucket assignment reads sensibly.** 26 `.py` to `scripts/`, 22 `.md` drafts to `drafts/`, 4 records to `reports/`, 4 outputs to `out/`. A file whose destination reads wrong is a `RULES` ordering bug; fix the table, re-run Step 2.

---

#### Task 3: Apply the 44 moves

**Files:** moved within `research-scratch/` (all ignored; `git mv` does not apply).

- [ ] **Step 1: Move.**

```bash
python research-scratch/scripts/reorg_scratch.py --apply
```text

Expected: 44 `MOVED` lines, a journal at `research-scratch/reorg/journal.json`, no `SKIP`.

- [ ] **Step 2: Prove nothing tracked moved.** These files are gitignored, so the tracked set must be untouched.

```bash
git -c core.quotepath=false status --porcelain --untracked-files=no
```text

Expected: empty. Any output means a refusal pattern failed; stop, run `--rollback`, and report.

- [ ] **Step 3: Prove the count is conserved.**

```bash
find research-scratch -maxdepth 1 -type f | wc -l
find research-scratch/{scripts,reports,drafts,out} -type f 2>/dev/null | wc -l
```text

Expected: root loose count drops to the 9 held files; the four buckets sum to 44 plus the tool's own file in `scripts/`.

- [ ] **Step 4: Do not commit.** Nothing here is trackable. Say so in the report rather than implying a commit happened.

---

#### Task 4: Citation sweep, read-only

**Files:**

- Create: `research-scratch/reorg/rewrite-journal.json` (ledger; ignored)

**Interfaces:**

- Consumes: `journal.json` from Task 3.
- Produces: the LIVE / RECORD / UNKNOWN ledger the owner signs off before Task 5.

- [ ] **Step 1: Dry run the rewrite.**

```bash
python research-scratch/scripts/reorg_scratch.py --rewrite --dry-run
```text

Expected: one line per document that mentions a moved path, with its line count, then `files touched N; line verdicts {'LIVE': a, 'RECORD': b, 'UNKNOWN': c}`, then the unresolved-path check.

- [ ] **Step 2: Read every UNKNOWN verdict by hand.** UNKNOWN is where the classifier is deliberately untrusted and never edits. A line is LIVE if a reader follows it, RECORD if it states where evidence sat, and neither classification is safe to guess at. Write the decision into the ledger annotation.

- [ ] **Step 3: Check the guard fired.** Any candidate file under `docs/design-research/`, `docs/frontend-review/`, `PLAN.md`, or `KNOWN-ISSUES.md` must appear in the `REFUSED n do-not-touch file(s)` line and not in the edited list. If one was edited, restore it from the ledger immediately.

- [ ] **Step 4: Stop and report.** Task 5 is a write to tracked documents. It needs the owner's yes on the specific file list.

---

#### Task 5: Rewrite live pointers only

**Files:** the tracked documents named in Task 4's LIVE set. Nothing else.

- [ ] **Step 1: Apply.**

```bash
python research-scratch/scripts/reorg_scratch.py --rewrite
```text

Expected: `EDITED <file>: n line(s)` per file, ledger written to `research-scratch/reorg/rewrite-journal.json` with `applied: true` only on LIVE lines.

- [ ] **Step 2: Prove each pattern matched exactly what the ledger says.**

```bash
git diff --stat
git diff -U0 | grep -E "^[+-]" | grep -v "^[+-][+-]" | head -40
```text

Expected: changed-line count equals the LIVE count from Task 4, and every `+` line differs from its `-` partner by the path only, never by surrounding prose. A diff that touches more than the path is a bad substitution; revert that file.

- [ ] **Step 3: Prove no LIVE pointer still points at a moved path.**

```bash
python research-scratch/scripts/reorg_scratch.py --rewrite --dry-run
```text

Expected: the LIVE count is now 0; remaining hits are RECORD and UNKNOWN only, and the ledger names them.

- [ ] **Step 4: Re-run the gates and compare to Task 1.**

```bash
make lore
php artisan test --compact
vendor/bin/pint --test --format agent
```text

Expected: identical to baseline. `make lore` cannot gain hits from a `docs/` path edit except through vocabulary this plan did not introduce; if it prints something new, that line is yours and you rule on it before continuing.

---

#### Task 6: Close-out and handoff

- [ ] **Step 1: Spot-check five citations** chosen from the ledger, one per bucket, by opening the target path and confirming it resolves.
- [ ] **Step 2: Update `docs/SOURCE-OF-TRUTH.md`** only if the inventory's §10 item 4 header work is authorized; otherwise note in the report that the derived doc still carries no regeneration stamp and the reorg did not add one.
- [ ] **Step 3: Report** with: HEAD sha, moves applied, LIVE/RECORD/UNKNOWN counts, gate outputs, files left held and why.
- [ ] **Step 4: Commit only on authorization**, by explicit path, one commit for the rewrite.

---

#### 7. Owner decisions this plan cannot make

| #     | Decision                                                                                                                | Notes                                                                                                                                                                                                                                                      |
| ----- | ----------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1     | Override the do-not-touch list for `.scratch-uma/`, `docs/frontend-review/2026-09-28/`, or `docs/design-research/**`?   | Requires a written override naming the directories. The cost is measured above: dated evidence rewritten into paths that did not exist at measurement time. `mockups/` (0 inbound) and `prototypes/` (1 inbound) are the only two where moving is cheap.   |
| 2     | After Task 3, prune `.scratch-uma/`?                                                                                    | 72 SQLite files and 11 logs are disposable, but pruning is deletion, and deletion needs a separate authorization plus proof no session holds a WAL file open.                                                                                              |
| 3     | Track any `research-scratch/` deliverable?                                                                              | `DOCUMENTATION-INVENTORY-2026-09-30.md` and `unfinished-phases-audit-2026-09-29.md` are cited by nothing in the tracked corpus and are the only two files there with lasting value.                                                                        |
| 4     | Keep `reorg_scratch.py` after the run?                                                                                  | It is disposable by design. If the second pass ever matters, it belongs in `tools/` beside `gate.py`, and that is a tracked-code decision under C-8.                                                                                                       |

---

## 2026-09-30-c5-down-enforcement-gap.md

### C-5 finding: `down()` is required but unverified — repo-wide enforcement gap

Date: 2026-09-30. Status: **recorded, not filed** — a peer session is mid-write on ten tracked
card-layer files in this shared worktree, so no tracked file (`KNOWN-ISSUES.md`, `PLAN.md`, a new
`docs/` entry) is touched until the tree clears. Filed to the workspace path the port report uses;
promote it to `KNOWN-ISSUES.md` (and an ADR if the owner wants a convention) once the peer is clear.

#### 1. The requirement stands; only the enforcement is missing

`CONSTRAINTS.md` C-5 requires "every migration has a working `down()`." That requirement is a floor and
is **not** withdrawn by anything here. What this finding identifies is that the requirement is satisfied
**by convention, not by running**.

- C-5's check command is `php artisan migrate:fresh --seed`. `migrate:fresh` drops tables directly (it does
  **not** invoke any migration's `down()`). So a migration whose `down()` throws, is missing, or drops the
  wrong table **passes C-5 today**.
- Verified: `git grep migrate:rollback -- tests/` returns **nothing on master** — no migration (cards,
  skills, race entries, profiles, or any other) has a rollback test. The gap is **repo-wide and uniform**,
  not introduced by the character-detail-page port.

Correct framing: **"required but unverified,"** therefore the disposition is *close the enforcement gap*,
not *accept it*.

#### 2. Why the branch's `--step=1` rollback test is fragile

The deleted branch's `CharacterProfileTest.php` proved rollback with
`Artisan::call('migrate:rollback', ['--step' => 1])` and asserted the profile table is gone while other
tables survive, on the stated basis that "this migration is the newest by file name, so `--step=1` is
exactly it." That premise is false on a fresh connection: **every migration that `migrate` runs lands in a
single batch**, so `--step=1` reverts the whole batch. The "other tables survive" assertion therefore
tests the *batch model*, not this migration's `down()`. Rejected as a port.

#### 3. The shared-global leak

Attempting an isolated `migrate`/`migrate:rollback` on a named in-memory connection leaks process state:

- `Artisan::call('migrate', ['--database' => X])` calls `Migrator::setConnection(X)` on the container
  singleton and leaves it. Restoring it in a `finally` cleared most fallout (13 failing tests → 2 → 1 in a
  controlled way).
- The residual leak is the **`Schema` facade carrying its own connection state**: a migration's `up()` /
  `down()` call `Schema::create` / `Schema::dropIfExists` against the resolved default, and exercising them
  in-process repoints shared state a `finally` on the Migrator alone does not fix (likely needs
  `DB::purge` / restoring the facade's default too). Not chased further, because a peer is actively writing
  the card-layer tests that surfaced as the residual failure — which may be their half-saved file, not this.

Conclusion: the correct pattern is **repo-wide**, not per-migration, and not a bolt-on to the profile
migration.

#### 4. Recommendation (owner decision on whether to fix)

Close the enforcement gap once, for all migrations:

- **Preferred:** a shared test helper that rolls back a **named** migration against an isolated connection
  without leaving the `Migrator` singleton or the `Schema` facade repointed after — so it can be called for
  every migration from one place. Its own ADR if it becomes the C-5 verification convention.
- **Fallback:** a subprocess call to `php artisan migrate:rollback` scoped to the migration, fully isolated
  from the test process (no in-process global touched). Proves `down()` runs without any shared-state risk,
  at the cost of a per-invocation process spawn.

Either is a real piece of work; neither is a follow-up defect of the detail-page port. When the tree
clears: file the finding (`KNOWN-ISSUES.md`), get the owner's call, then land the helper as one change for
all migrations.

#### Context

Surfaced while closing the detail-page port (report: `2026-09-30-port-and-cleanup.md`, §10). The port is
complete and green at `8c16edc`; this record does not block it.

---

## PLAN-UI-UX-2026-10-02.md

> Snapshot 2026-10-02 at `3d5c26a`, 37 steps closed and 42 open. Re-measured 2026-10-03 at `53cc960`: still
> 37 closed and 42 open. This section is the authoritative copy; the separate working file was deleted in
> the 2026-10-03 consolidation commit, and open steps continue here.

_Dated close-out 2026-10-08 (documentation-sync pass): the "still 42 open" snapshot above is the read it
was taken on; it is superseded en bloc. The 2.0 frontend this plan's M1–M5 workstreams were sequencing
for landed through Phases A–E of `docs/proposals/frontend-development-plan.md` (A1–A4c, B1, C1–C3,
D1–D18, E1–E6), and the screens those workstreams targeted — the trainee detail, the skill selector, the
support deck, the scenario panels, the responsive contract — now exist as `SCR-CAR-001`–`024` /
`SCR-VET-001`–`004` / `SCR-SYS-005`–`007` in `SCREEN_SPEC.md`, which is where this plan's per-screen
acceptance now lives. The embedded task briefs below are preserved as the historical record of the
sequencing, per this master's precedent of keeping a superseded plan rather than deleting it; a reader
executing a brief should start from the landed status in `SCREEN_SPEC.md` and the plan's §4 table, not
from this section's closed/open column._

UI/UX Frontend Development Update Plan

**Version:** 1.2
**Date:** 2026-10-02
**Status:** Draft for owner approval (v1.2: restructured after a second review pass)
**Basis:** The 16 consolidated files in `docs/research-scratch/`, the register (`KNOWN-ISSUES.md`), and the tree at `e18a032`. Every current-state claim below was re-derived against the tree on 2026-10-02. Historical v1.0 commentary is kept in Appendix D, out of the task briefs.

**Read order:** this page is the owner summary and the decision list. Task briefs start at Section 2. If you are executing a task, jump to its workstream; if you are approving, stop after Section 0.

**Note on dashes:** R-02 (no em dash) governs shipped user-visible copy. This plan and the review record use `->` and `-` throughout so the same text can be pasted into a commit message, a gate output, or a ticket without re-encoding.

---

### 0. Owner Summary

**What this is.** Six workstreams of outstanding UI/UX work, ordered by dependency, expressed as task briefs an agent can execute. Roughly 33-50 working days across five milestones, of which M5 is blocked on an owner ratification and every M1/M2/M3 register closure is blocked on the push order.

**What dominates the sequencing:**

1. **Data must precede display.** The skills section and the skill picker render card skill lists, which D-30 does not yet permit. Building UI against an unratified schema is the failure mode documented in KI-47.
2. **The register is the source of truth for open work.** A workstream may not close a KI it did not satisfy, and a KI whose fix already shipped must still be closed in the register with the sha. See KI-22 (filed on a wrong cause) and KI-25 (re-opened on a measurement gap).
3. **The push is a gate, not an afterthought (O-1).** Local master is 17 commits ahead of `origin/master`, which carries none of this plan's basis documents. Register discipline closes a KI only when the fix is on `origin/master`, so every closure in M1 and M2 is blocked on the owner's push decision.

**Recommended sequencing:** M1 (sizing fixes + D-30 amendment + register sweep) -> M2 (trainee detail page) -> M3 (skill selector combobox) -> M4 (support deck panel) -> M5 (responsive contract).

#### 0.1 Decisions needed from the owner

| ID    | Decision                                                                                                          | Recommended default                                                                                                                                                                                                                                                                                        | Blocks                               |
| ----- | ----------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------ |
| O-1   | Push order: local master is 17 ahead of `origin/master`, which carries none of this plan's basis docs             | **Push local master to `origin/master` first.** The basis documents are the evidence every task brief cites; leaving them local-only means each closure has to cite a commit a reviewer cannot fetch. Reversible if the owner prefers a branch, but then closure wording must say "local-only citation".   | Every register closure in M1/M2/M3   |
| O-2   | WS-5 KI-43 fix: Option A (search-first combobox), Option B (filtered shortlist), Option C (accept and document)   | **Option A**, the register's own fix candidate 1 (`KNOWN-ISSUES.md`), reusing the M3 combobox factory                                                                                                                                                                                                      | M4's task start only                 |
| O-3   | R82 ratification: the 768px floor in `DESIGN.md` §2.3 rests on an unratified proposal                             | **Ratify R82 as drafted**; it already matches the 390px measurement work in M5                                                                                                                                                                                                                             | M5 only                              |
| O-4   | §3.1 lineage pick in `SESSION-CONSOLIDATION-2026-09-30.md`                                                        | **No action needed for this plan.** It gates the alternate lineage chain, not any page here; the master lineage shipped at `555b0cb`                                                                                                                                                                       | Nothing in this plan                 |

**Not a decision:** the plan's own scope. Section 7 lists what is deliberately excluded; nothing outside it is scheduled.

#### 0.2 Top risks

| Risk                                                                                    | Likelihood   | Impact   | Mitigation                                                                                                                                         |
| --------------------------------------------------------------------------------------- | ------------ | -------- | -------------------------------------------------------------------------------------------------------------------------------------------------- |
| Push order (O-1) unsettled, so closures cannot be written and M1's exit cannot be met   | High         | High     | Owner decides before M1 opens. Every closure task verifies `git branch -r --contains <sha>` before writing the closure line.                       |
| Combobox generalization breaks the trainee selector                                     | Medium       | High     | The existing `TraineeSelectorTest` suite must pass unchanged before the skill call site is written; it is the regression net for the extraction.   |
| Owner ratification of the 768px floor delayed                                           | Medium       | Medium   | M5 waits. Do not build against an unratified contract.                                                                                             |
| Shared worktree collisions                                                              | High         | Medium   | `gstack:careful`; stage named paths only; `git status --short` before every commit.                                                                |
| Seeded DB does not populate `race_catalog_slots` offline                                | Medium       | Low      | Task 6.2 verifies the row count first; the fallback measures the turn log only and records the deferral.                                           |
| Register sweep verdicts contested                                                       | Low          | Low      | Each disposition records the evidence read; the owner overrides in writing.                                                                        |

---

### Legend of identifiers used in this plan

Short names appear throughout the briefs. Definitions:

| Short name                             | What it is                                                                                   | Where it lives                                                       |
| -------------------------------------- | -------------------------------------------------------------------------------------------- | -------------------------------------------------------------------- |
| R73                                    | The "read these before touching the run screen" list                                         | `docs/research-scratch/SLICE-RECORDS.md:3613`                        |
| R82                                    | The unratified proposal for a 768px responsive floor                                         | `docs/research-scratch/PLANS-AND-BRIEFS.md:186+`                     |
| R85                                    | The re-open record for KI-25 (scroll measurement gap)                                        | `KNOWN-ISSUES.md` (KI-25 entry)                                      |
| D-30                                   | The design-corpus clause that fixes which columns a card or trainee row may render           | `docs/research-scratch/DESIGN-CORPUS.md:1805`                        |
| D-31                                   | The design-corpus clause that describes the 0..1200 stat bound as a live defect              | `DESIGN-CORPUS.md:1806`                                              |
| D-40                                   | The design-corpus clause holding the responsive-floor rule                                   | `DESIGN-CORPUS.md:1834`                                              |
| D-289                                  | The errata-forward rule (supersede with a dated note; never edit historical text in place)   | `DESIGN-CORPUS.md` gate table                                        |
| G-SK-13                                | The four-band skill-picker gate, currently a clause inside KI-33 rather than its own entry   | `KNOWN-ISSUES.md:1585,:1618`                                         |
| KI-nn                                  | A register entry in `KNOWN-ISSUES.md` (known issue)                                          | root `KNOWN-ISSUES.md`                                               |
| C-n                                    | A constraint in root `CONSTRAINTS.md`                                                        | root `CONSTRAINTS.md`                                                |
| G-n                                    | A design gate in the `DESIGN-CORPUS.md` gate table                                           | `DESIGN-CORPUS.md:~:2558-2615`                                       |
| Slice 13                               | The verified run-screen slice (skill combobox precedent)                                     | `docs/research-scratch/SLICE-RECORDS.md` §"slice-13-2026-09-29.md"   |
| "Slice N" in the current-state table   | A numbered product slice, not a git ref; each maps to a `slice-NN-YYYY-MM-DD.md` record      | `SLICE-RECORDS.md`                                                   |
| WS-n / M-n                             | Workstream / milestone in this plan                                                          | this document                                                        |

#### Lore baseline movement

v1.0 recorded 98 hits / 55 exempt. v1.1 measured 181 hits / 55 exempt. Exempt held steady, so the movement is a change in what is *scanned*, not a change in rulings: the exemptions are per-line markers that did not move, while the hit total rose because `composer lore-code` was extended to untracked files and the Global client terminology list (Wisdom, Motivation, gacha, jewel) was added to the same run. Both numbers are dated measurements, recorded as relative acceptance criteria rather than baselines.

---

---

### 1. Current State Assessment

#### 1.1 What exists and works

| Surface                         | State                                                                                                | Evidence                                                                |
| ------------------------------- | ---------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------- |
| Catalog index + detail          | Renders; aptitude grid and form tabs landed                                                          | `555b0cb`; `catalog/show.blade.php`                                     |
| Run screen (dashboard)          | Renders with stat band, guided rail, timeline, calendar                                              | Slice 5, 8, 13 verified                                                 |
| Run screen skills editor        | Repeater with per-control labels landed; still one native `<select>` per row with 623 options each   | `4902f1d`; `runs/show.blade.php` (rows `:479-481`, select `:538-547`)   |
| Skill search (Screen D)         | Renders, escaped, paginated, dark-theme clean                                                        | Slice 15 browser pass                                                   |
| Trainee combobox (run create)   | WAI-ARIA APG combobox, prefix match, grouped                                                         | `trainee-combobox.ts`; `TraineeSelectorTest.php`                        |
| Support deck panel              | Six pickers, Scenario Link derived, effect lines at "highest stated anchor"                          | Slice 2, ADR-0014; `deck-panel.blade.php:61,126`                        |
| `color-scheme` declarations     | Shipped                                                                                              | `ed71741`; `resources/css/app.css:219,:229`                             |
| Lore gate                       | 181 hits / 55 exempt; lore-code 42 (measured 2026-10-02 at `e18a032`)                                | `composer lore`, `composer lore-code`                                   |

#### 1.2 What is broken, incomplete, or unverified

| ID             | Priority (plan-assigned; the register assigns none)   | Surface                      | State                                                                                                                                                                                                                              |
| -------------- | ----------------------------------------------------- | ---------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| KI-25          | Medium                                                | Turn log at 390px            | RE-OPENED (R85); arrow-key scroll + `scrollWidth` re-read never measured in a browser; D-40/768px reconciliation held on R82                                                                                                       |
| KI-29          | Low                                                   | Catalog index controls       | `catalog/index.blade.php:15,:19,:32` carry no `h-11`; the 44px rule is `DESIGN-CORPUS.md:904`                                                                                                                                      |
| KI-32          | Low                                                   | Dark theme native controls   | Fix shipped (`ed71741`); register entry still OPEN and owes the closure with the browser read                                                                                                                                      |
| KI-35          | High                                                  | Trainee detail page          | Five of the eight target sections render (Basic info, Aliases, Costume forms with aptitude grid, Provenance); Skills, Goal races, Her runs absent; stale copy claims skill lists "are not stored" (`catalog/show.blade.php:226`)   |
| KI-43          | Medium                                                | Support deck panel           | Deck block 296,537 B with six equipped; 88.5% of the page with none; `<option>` payload alone 207,504 B (57.6%)                                                                                                                    |
| KI-48          | Low                                                   | Architecture docs            | Fix shipped (`79d5f6f`, dated errata citing ADR-0015 and `8bda7db`); register entry still OPEN                                                                                                                                     |
| KI-49, KI-51   | Medium                                                | Source bodies / seeders      | Bodies tracked since `8b17703` / `30b3a08`; entries stale, owe verify-and-close                                                                                                                                                    |
| KI-53, KI-54   | Medium                                                | `race-tier-labels` fixture   | Fixture tracked at HEAD, tree clean, the 11-red state no longer reproduces; entries stale; the `withTierLabelsFileAbsent()` helper (`ScenarioSlotSeederResilienceTest.php:102`) is still unguarded                                 |
| KI-45          | Medium                                                | Race calendar data           | Headline (no offline population path) superseded by `8b17703` (`seed_file`); second gap stands: `scenario_slots.tier` NULL on 141/296                                                                                              |
| G-SK-13        | Medium                                                | Skill picker on run screen   | Lives inside KI-33 (`KNOWN-ISSUES.md:1585,:1618`), not its own entry; four-band picker unbuilt; 623 available skills                                                                                                               |
| Phase B2       | Medium                                                | Skill facts on read rows     | Design drafted (`SKILLS-MECHANICS.md:3383-3386`); storage decision outstanding (Data Engineer / Architect)                                                                                                                         |
| D-31           | Low                                                   | Design-corpus clause         | `DESIGN-CORPUS.md:1806` still describes the 0..1200 bound as a live defect though ADR-0015 landed                                                                                                                                  |
| Doc drift      | Low                                                   | Referenced docs absent       | `docs/GATE-REGISTRY.md` and `docs/PRE-MORTEM.md` are both cited by `agents.md` but absent from the tree                                                                                                                            |

#### 1.3 Skills available

`SKILL.md` no longer holds a roster. The scanner is the authority; the commands live in Appendix B (defined once, referenced from here). Agents run the scanner before dispatching any task and attach the output to the dispatch record.

**Two shas appear in this plan, and they are not the same measurement.** The basis tree is `e18a032`: every current-state claim in Section 1 was read there. The scanner counts (`205 skills, 4 errors, 62 warnings`) were taken at `31592ac`, a later commit that moved the tree but did not change this plan's subject matter. Treat the scanner numbers as a dated reading of a later tree, not as a claim about `e18a032`.

**The 4 errors are not a blocker and are not this plan's to fix.** The scanner reports them for the whole workspace, including registries outside `docs/research-scratch/`. Action for a task agent: record the counts in the dispatch record, and if a task's own skills appear in the error list, name that in the task record. Do not edit another workstream's registry to make a count go down.

The skills most relevant to this plan:

| Skill                                                  | Use                                                       |
| ------------------------------------------------------ | --------------------------------------------------------- |
| `doubt-driven-development`                             | Every claim in a task brief is re-derived, not carried    |
| `source-driven-development`                            | Every current-state claim cites `file:line` or a commit   |
| `code-review-and-quality`                              | Six-axis review on every PR                               |
| `testing-best-practices` / `test-driven-development`   | RED before GREEN                                          |
| `antislop-copywriting`                                 | R-02 (no em dash), no AI voice                            |
| `gstack:careful`                                       | Shared-worktree discipline                                |
| `laravel-best-practices`                               | Thin controllers, actions, form requests                  |

---

### 2. Workstreams and Tasks

#### Workstream 1 - Sizing Fix and Register Sweep (M1)

**Owner:** Frontend engineer
**Lore Guardian:** every WS-1 change (the count check is lore acceptance criteria, not a formality)
**Blast radius:** one view file; register entries; (optionally) one test helper, per Task 1.3's decision below
**Gates touched:** C-1..C-4, C-6, lore (relative)
**Depends on:** O-1 for every register closure

##### Task 1.1 - Fix KI-29 (catalog index controls)

**Brief:**

- File: `resources/views/catalog/index.blade.php`
- Change: add `h-11` to the search input (`:15`), status select (`:19`), submit button (`:32`)
- Reference: `skills/index.blade.php:33,:42` carry `h-11` on input and select
- Screen D's submit button does not, so the submit is governed by the KI-29 measurement, not by precedent
- Do not add tokens. Do not change layout beyond control height.

**Test (RED first)** - the selector covers untyped, `text` and `search` inputs, every `select`, and submit buttons, so a control with no `type` attribute or a `type="search"` cannot slip through:

```php
it('sizes every catalog control to the design contract\'s 44px', function (): void {
    $html = $this->get('/umamusume')->assertOk()->getContent();
    $dom = new DOMDocument(); @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $controls = $xpath->query(
        '//input[not(@type) or @type="text" or @type="search"]'
        .' | //select | //button[not(@type) or @type="submit"]'
    );
    expect($controls->length)->toBeGreaterThan(0);
    foreach ($controls as $node) {
        expect($node->getAttribute('class'))->toContain('h-11');
    }
});
```text

**Note on the 44px claim:** `h-11` is `2.75rem`. That is 44px only while the root font size is 16px. The browser read in the acceptance list is what proves the contract; the class assertion proves the token was applied, not the rendered height.

**Acceptance:**

- [x] `grep -c 'h-11' resources/views/catalog/index.blade.php` >= 3
- [x] `php artisan test --filter=CatalogTest` green
- [x] Browser read at 1280x800 and 390x844 confirms 44px on all three controls (screenshots attached to the task record)
- [x] `composer lore` and `composer lore-code` counts unchanged from the task's own pre-run capture (do not hardcode dated numbers in the record)
- [ ] KI-29 closes in `KNOWN-ISSUES.md` once the fix is on `origin/master` (O-1)

**Commit:**

```text
fix(ui): size catalog index controls to the design contract

KI-29. The catalog index's search field, status filter and submit
button measured 30/31/32px against the 44px control height in
DESIGN-CORPUS.md. The run screen took the same fix at c17e63b;
this is the second surface.
```text

##### Task 1.2 - Close KI-32 with the owed measurement

**Brief:**

- No code change: the declarations shipped at `ed71741` (`app.css:219` `color-scheme: light`, `:229` `color-scheme: dark`)
- Remaining work: the browser read the register entry still names, then the closure
- Load `/skills` with `prefers-color-scheme: dark`; read `getComputedStyle(document.documentElement).colorScheme`; assert `dark` in dark theme and `light` in light theme; assert the same native checkbox paints identically on two consecutive loads

**Acceptance:**

- [x] Browser measurement attached to the task record
- [x] KI-32 closes in `KNOWN-ISSUES.md` naming `ed71741` and the measurement (closure waits on O-1 per register discipline)

##### Task 1.3 - Register reconciliation sweep

**Brief:**

- For each entry below: re-derive the claim against the tree, then close or correct forward. A KI that fails to reproduce closes with the failed reproduction recorded, not silently.
- KI-49 / KI-51: bodies tracked (`8b17703`, `30b3a08`); verify `git ls-files database/seeders/data/` shows all nine JSON files, then close
- KI-53 / KI-54: fixture tracked at HEAD, tree clean, 11-red no longer reproduces; record the failed reproduction
- The unguarded `withTierLabelsFileAbsent()` helper (`ScenarioSlotSeederResilienceTest.php:102` renames with no `file_exists` guard) is **fixed in this sweep**, not filed: the fix is a one-line `file_exists` guard, which is smaller than the paperwork for a new KI and keeps the sweep's blast radius honest. This is the one code change WS-1 makes outside its view file; the blast radius at the workstream head says so.
- KI-45: headline superseded by `8b17703` (`config/uma.php:138-145`); append a dated correction naming the commit, keep the entry open on the remaining gap (141/296 NULL `scenario_slots.tier`)
- Doc drift: file one KI naming the two absent docs (`docs/GATE-REGISTRY.md`, `docs/PRE-MORTEM.md`), both still cited by `agents.md`

**Acceptance:**

- [x] Every closure cites the sha and states the fix is on `origin/master` (O-1 resolved first)
- [x] No historical register text edited in place; corrections appended
- [x] Sweep record lists each entry, its disposition, and the evidence read

---

#### Workstream 2 - Trainee Detail Page (KI-35)

**Owner:** Frontend + design-system owner
**Lore Guardian:** every WS-2 change (new user-visible copy on the detail page)
**Blast radius:** `catalog/show.blade.php`, new `skill-row` component
**Gates touched:** C-1..C-4, G-4, G-5, G-13, G-47, lore
**Depends on:** WS-3 Task 3.1 (D-30 amendment) landing in M1

**Absence vocabulary (binding on every task in WS-2).** One canonical form: **"not recorded"**. Two shapes are legal and both use that wording:

- A kept heading with a body sentence ("Goal races are not recorded. The source publishes per-trainee goal races; this tool does not record them.")
- A disclosed field rendered `N/A` with a `title` attribute

Never "Unknown", never "not stored", never "not yet recorded" as a third variant. This is what keeps Task 2.1's stale-copy check and Task 2.3's new body from colliding: the check below targets the exact stale sentence, and the replacement wording is a different phrase, so the two can coexist on one page.

**Section order (binding).** The rendered order is Identity, Aptitudes, Costume forms, **Skills**, **Goal races**, **Her runs**, Aliases, Provenance. Task 2.5's checklist below is an unordered set and is listed alphabetically only so it reads as a verification sweep; the brief here is the authority on order.

##### Task 2.1 - Replace the stale copy on the detail page

**Brief:**

- File: `resources/views/catalog/show.blade.php`
- Replace the visible sentence at `:225-229` and the Blade comment at `:209-224`, which claim skill lists "are not stored" and that ADR-0012 keeps them off the card row
- Use the drafted copy in `SKILLS-MECHANICS.md` §"skills-section-phase-b2-2026-10-01.md" §9.4, adjusted to the canonical absence vocabulary above

**Test (RED first)** - the assertion targets the exact stale sentence, not the word "stored", so Task 2.3's body copy cannot fail it:

```php
it('does not tell the trainer that skill lists are not stored', function (): void {
    $u = Umamusume::factory()->create(['slug' => 'test-slug']);
    $this->get('/umamusume/test-slug')
        ->assertOk()
        ->assertDontSee('are not stored')
        ->assertDontSee('Skill lists are not stored');
});
```text

**Acceptance:**

- [x] `grep -n 'are not stored' resources/views/catalog/show.blade.php` returns no match on the working tree (read the working tree, not `git show HEAD:`)
- [x] The new copy names what is stored (`skills_innate`, `skills_unique`) and what is not recorded yet (`skills_awakening`, `skills_event`)
- [x] The new copy carries a removal trigger (when the parser starts keeping those keys)
- [x] Lore gate clean; `RenderedCopyHygieneTest` green
- [x] Re-check that Tasks 2.2-2.4 copy still uses only the canonical absence forms

##### Task 2.2 - Render the Skills section

**Brief:**

- New component: `resources/views/components/skill-row.blade.php`, invoked as `<x-skill-row>`
- Consumes `CharacterCard::skills_innate` and `skills_unique` JSON lists (landed at `dd90330`; casts at `CharacterCard.php:66-67`)
- Resolution: map the lists' ids to `Skill` rows via `skills.export_id`
- Groups: `Her unique`, `Her innate`
- Rows: name, `✦ Unique` pill (pattern from `skills/index.blade.php:128-133`), `N SP` or `N/A SP` with `title`
- **No `turn` column.** A field that is `N/A` on every row until Phase B2 is noise on every row. Add it when the storage decision lands.
- Facts disclosure: out of scope; it rides with the Phase B2 storage decision (`SKILLS-MECHANICS.md:3383-3386`)

**Test (RED first)** - the skills table is empty under `RefreshDatabase`, so the test must create the `Skill` rows the resolution needs. `SkillFactory` exists; `export_id` is nullable with no default, so set it explicitly. Fixture names are obviously fake (the repo's rule against storing unsourced rows applies to fixtures as much as to seeders, and a plausible-sounding invented name is indistinguishable from a real one at a glance):

```php
it('lists her unique and innate skills on her detail page', function (): void {
    $u = Umamusume::factory()->create(['slug' => 'test-slug']);
    CharacterCard::factory()->create([
        'umamusume_id' => $u->id,
        'skills_unique' => [900001],
        'skills_innate' => [900002, 900003],
    ]);
    Skill::factory()->create(['export_id' => 900001, 'name' => 'Test Unique Skill']);
    Skill::factory()->create(['export_id' => 900002, 'name' => 'Test Innate Skill A']);
    Skill::factory()->create(['export_id' => 900003, 'name' => 'Test Innate Skill B']);
    $this->get('/umamusume/test-slug')
        ->assertOk()
        ->assertSee('Her unique skills')
        ->assertSee('Her innate skills')
        ->assertSee('Test Unique Skill')
        ->assertSee('Test Innate Skill A');
});
```text

**Acceptance:**

- [x] Component invoked from `catalog/show.blade.php`; section sits between "Costume forms" and "Goal races" per the binding order above
- [x] Empty state, when both lists are empty: "No skill lists are recorded for this form."
- [x] No `turn` column renders
- [ ] Contrast measured in both themes (G-5), attached to the task record

##### Task 2.3 - Add Goal races section (stub with absence copy)

**Brief:**

- No `trainee_goals` table exists; KI-34 is the reservation for it (`KNOWN-ISSUES.md:21-24`)
- Render the heading and a body using the canonical absence form
- Cite `RACE-AND-SLICE-RESEARCH.md` §"RACE-CALENDAR-GAPS.md" §7 for why no goal source is wired today (the pennant has no source). The four client Goal panels are recorded there as evidence of the missing shape, not as a template.

**Acceptance:**

- [x] Section renders a heading
- [x] Body reads "Goal races are not recorded. The source publishes per-trainee goal races; this tool does not record them."
- [x] Absence copy uses one of the two legal forms only, never "Unknown"

##### Task 2.4 - Add "Her runs" section with primary action

**Brief:**

- Data access belongs in the controller, not the view. Add the query to the detail-page controller action, alongside the existing data for this view; do not query from Blade.
- Query: `TrainingRun::where('umamusume_id', $umamusume->id)->with(['scenario', 'turnEntries'])->orderByDesc('created_at')->limit(10)`
- Eager-load `scenario` (the row renders its label) and count `turnEntries` with `withCount('turnEntries')` rather than loading the rows, so the section is two queries total, not 1 + 10N
- Row: run status pill, scenario label, turn count
- Run scoping: this is a single-Trainer local tool with no auth surface, so there is no trainer scope to apply. Do not add one.
- Primary action: label it **"New run"** and link via `route('runs.create')`, with the helper line "Opens run setup; the trainee is chosen there." Pre-selecting the trainee stays out of scope (`TrainingRunController::create()` at `:63-90` never reads a `umamusume_id` param, so a link that passed one would render and silently not pre-select). The helper line is what keeps the action honest rather than a mismatch; if the owner prefers to pull the pre-selection in, that is a scope change, not a clarification.

**Acceptance:**

- [x] Section present per `DESIGN.md` §4.2 item 6
- [x] Query lives in the controller; the section renders in at most 2 queries (assert with a query log if convenient)
- [x] Primary action renders via a named route, with the helper line
- [x] `DESIGN.md` §2.3's "one primary action per screen" satisfied (no other button at that visual weight)

##### Task 2.5 - Verify the eight-section page

This is an unordered set. The rendered order is the one stated at the workstream head.

**Acceptance:**

- [x] All eight sections render: Aliases, Aptitudes, Costume forms, Goal races, Her runs, Identity, Provenance, Skills
- [x] Rendered order matches the binding order above
- [x] Each section's empty state is a named absence, not a blank
- [x] Every absence string on the page is one of the two legal forms
- [ ] `DESIGN.md` §4.2 checked against the rendered page
- [ ] KI-35 closes with a note that Goal races remains a stub for the `trainee_goals` schema (KI-34 reservation)

---

#### Workstream 3 - Design-System Catch-Up (all in M1)

**Owner:** Architect + Docs Writer
**Lore Guardian:** every WS-3 change (amended design text is read by agents and by the Trainer via copy)
**Blast radius:** `DESIGN-CORPUS.md`, register entries
**Gates touched:** `DocSchemaDriftTest`, G-60
**Depends on:** nothing (docs only). All three tasks run inside M1.

Tasks 3.2 and 3.3 used to sit in a separate "M3 doc corrections" milestone. They are pure register and errata work with the same O-1 dependency as the M1 sweep and, in 3.3's case, the same file and the adjacent line to 3.1's, so they are in M1 with everything else. M3 is gone as a result.

##### Task 3.1 - Amend D-30 for card skill lists and aptitudes

**Brief:**

- File: `docs/research-scratch/DESIGN-CORPUS.md`, section "CONSTRAINTS.md" §5, D-30 entry (`:1805`)
- Add `skills_innate` and `skills_unique` to `CharacterCard`'s permitted render list, and the ten `aptitude_*` columns to `Umamusume`'s
- Use the amendment text drafted in `PLANS-AND-BRIEFS.md` §"d-30-amendment-draft-2026-10-01.md" §2; the draft's own §1 cites `docs/design-research/CONSTRAINTS.md`, which no longer exists in the tree, so fix that citation when landing

**Acceptance:**

- [x] Both column sets named in D-30 with their stated uses ("grouping a card's own skills" / "the trainee detail page's aptitude section")
- [x] Amendment dated 2026-10-02 with a lead that names the round
- [x] G-60 passes: no retired literals in the amendment's block (or a `RETIRED LITERAL:` marker)
- [x] `DocSchemaDriftTest` green

##### Task 3.2 - Correct D-31 with a dated erratum

**Brief:**

- `DESIGN-CORPUS.md:1806` still describes the 0..1200 bound as a live defect though ADR-0015 landed (`8bda7db`)
- This is the line immediately after Task 3.1's (`:1805`), same file, same session. Do it in the same commit as 3.1 so the two clauses never disagree on disk.
- Append a dated erratum (D-289 applied): keep the historical clause verbatim, add the correction after it

**Acceptance:**

- [x] Erratum dates the supersession and names `8bda7db`
- [x] No historical text edited in place
- [x] Lands in the same commit as Task 3.1

##### Task 3.3 - Close KI-48

**Brief:**

- No code or doc change: the bound erratum landed at `79d5f6f`; both carriers (`ARCHITECTURE-ESSENTIALS.md:36`, `ARCHITECTURE.md:158`) carry the dated correction citing ADR-0015 and `8bda7db`
- Remaining work is the register closure. Same shape as the M1 sweep: pure register, O-1 gated.

**Acceptance:**

- [x] KI-48 closes in `KNOWN-ISSUES.md` naming `79d5f6f` (closure waits on O-1)
- [x] `DocSchemaDriftTest` green

---

#### Workstream 4 - Skill Selector Combobox (G-SK-13)

**Owner:** Frontend
**Lore Guardian:** the new picker copy (band labels, the awakening disclosure, the keep-typing line)
**Blast radius:** `trainee-combobox.ts` (generalize), new `skill-combobox.ts`, `runs/show.blade.php`
**Gates touched:** C-1..C-4, C-8, C-9 (TypeScript), G-4, G-11 (keyboard), G-13
**Depends on:** Task 3.1 (D-30 amendment, M1). Not on the M2 `skill-row` component: that Blade component and this TypeScript combobox share no code. The M2-before-M4 ordering still holds, but the reason is sequencing risk, not reuse: the four-band picker is the largest combobox surface in the plan, and landing it after the detail page has already proved the skill data shape in a simpler renderer means a shape failure shows up in a small surface first. No file or function is carried from `skill-row` into `skill-combobox.ts`.

##### Task 4.1 - Generalize `trainee-combobox.ts`

**Brief:**

- Extract a factory: `createCombobox({ payloadAccessor, comparator, band, label, idPrefix })`
- Trainee call site supplies its current closures; skill call site supplies four bands and a name-only comparator
- Preserve every ARIA contract currently pinned by `TraineeSelectorTest.php` (aria-activedescendant/textContent pins ~`:664-680`, aria-label pin ~`:766-777`)

**Test (RED first):**

- `TraineeSelectorTest` stays exactly where it is. It is the regression net for the extraction, and moving it would mean the net is green against a file that no longer exists in the form it was written for.
- The only new test is a source-shape pin, because there is no JS test runner and C-8 forbids adding one. It pins that the factory exists and takes an `idPrefix`, and that the id it builds is derived from that prefix rather than a hardcoded one:

```php
it('builds combobox ids from a caller-supplied prefix', function (): void {
    $src = File::get(resource_path('js/trainee-combobox.ts'));
    expect($src)
        ->toContain('createCombobox')
        ->toContain('idPrefix');
});
```text

- That pin is deliberately weak. It proves the parameter exists, not that two prefixes yield disjoint id sets; the browser pass in Task 4.2 is what proves behaviour. Say so in the task record rather than claiming more coverage than the pin has.

**Acceptance:**

- [ ] `TraineeSelectorTest` green and unmodified, and the suite still runs from its original path
- [ ] `npm run typecheck` and `npm run build` clean
- [ ] Slice 13 browser pass re-run per `SLICE-RECORDS.md` §"slice-13-2026-09-29.md" §5.1

##### Task 4.2 - Build the skill selector and replace the native `<select>` in the repeater

This task absorbs what were two tasks in v1.1. The old Task 4.3 was the same work as 4.2 (both replace the repeater's skill `<select>` with the combobox), split for no reason; they are one task now.

**Brief:**

- Four bands: `Her unique skills`, `Her innate skills`, `Already on this run`, `Everything else`; a skill appears in exactly one band (precedence per `SKILLS-MECHANICS.md` §"skills-section-phase-b2" §4.3)
- Cardless trainee: two bands, no card bands, with the drafted disclosure (`SKILLS-MECHANICS.md:2888-2891`)
- Awakening band absent; disclosure: "Awakening skills are not recorded yet, so this picker cannot group them." (drafted at `SKILLS-MECHANICS.md:2993`; adjusted to the canonical absence vocabulary, see WS-2)
- Option label: `{band}: {title} · {N SP}` (SP cost from the `skills` table)
- Cap at 10 visible rows, keep-typing line, keyboard per APG
- The repeater's skill picker becomes the combobox; the status select and turn input stay
- Hidden inputs carry `skills[N][skill_id]` for each row; the `syncSkills` write path (`TrainingRunController.php:708`) is unchanged

**Test (RED first)** - the repo has no JS test runner and C-8 forbids adding one; the established RED for TypeScript is a source shape pin, as `TraineeSelectorTest`'s SHAPE PINS preamble documents:

```php
it('carries the four-band payload and the APG contract in skill-combobox.ts', function (): void {
    $src = File::get(resource_path('js/skill-combobox.ts'));
    expect($src)
        ->toContain('Her unique')
        ->toContain('Her innate')
        ->toContain('Already on this run')
        ->toContain('Everything else')
        ->toContain('aria-activedescendant')
        ->toContain('Awakening skills are not recorded yet');
});
```text

And a view-shape pin, so the `<select>` removal is checked rather than assumed:

```php
it('renders the skill combobox and no skill select in the run repeater', function (): void {
    $html = $this->get(route('training-runs.show', $run))->assertOk()->getContent();
    expect($html)->toContain('skill-combobox');
    expect($html)->not->toContain('name="skills[0][skill_id]" data-role="native-select"');
});
```text

**Acceptance:**

- [ ] `resources/js/skill-combobox.ts` written; `runs/show.blade.php` skills repeater replaced with the combobox
- [ ] No `<option>` element for a skill remains; the payload carries the rows as JSON
- [ ] `RunSkillRowLabelsTest` green (three labels per row)
- [ ] Browser pass: keyboard navigation, Enter selects, Escape closes, `aria-activedescendant` tracks, and two instances on the page do not share an id namespace
- [ ] `php artisan test --compact` full suite green
- [ ] No new dependency (C-8 clean)
- [ ] KI-33's G-SK-13 clause (`KNOWN-ISSUES.md:1585,:1618`) updated; G-SK-13 has no entry of its own, so the closure lands there

---

#### Workstream 5 - Support-Card UI Polish

**Owner:** Frontend + Designer
**Blast radius:** `deck-panel.blade.php`
**Gates touched:** C-1..C-4, C-6
**Depends on:** the generalized combobox from WS-4 (M3) only if O-2 resolves to Option A. Options B and C have no dependency on WS-4.

##### Task 5.1 - Address KI-43 (deck panel markup weight)

**Brief (choose one; this is owner decision O-2):**

- **Option A (recommended):** search-first picker using the generalized combobox from WS-4. This is the register's own fix candidate 1 (KI-43).
- **Option B:** filtered shortlist (five stat types + Pal)
- **Option C:** accept and document (no change)

**Test (if A or B):**

- Page weight measurement before/after on the six-equipped run
- Baseline is the number recorded in KI-43's table: deck block 296,537 B; `<option>` payload 207,504 B. The old `measure-deck-weight.php` reference is dropped: it lives in the gitignored `.scratch-uma/` dir and is not a durable artifact.

**Units, stated once so the numbers are comparable.** All sizes in this task are **bytes** (B), and the budget is written in bytes too. 1 KB here means 1,000 B, not 1,024. Where KI-43 quotes a percentage, the denominator is the whole page response with six cards equipped (296,537 B for the deck block alone is not the denominator). Note that 207,504 B is about 70% of the 296,537 B deck block, so the 57.6% in KI-43 is a share of something else; before quoting any percentage in the task record, state the numerator and the denominator on the same line.

**First question to answer, and it may settle the task on its own:** does the 207 KB `<option>` payload repeat the same 623-skill list across all six pickers? If it does, a single shared payload served once and referenced by all six is likely to meet the 80,000 B target without a new picker at all. Measure the repetition before choosing an option.

**Acceptance:**

- [ ] Choice recorded with reasoning in the task record
- [ ] If the repeated-payload measurement shows a single shared payload meets the target, that is the change; record it as the fix, not as a rejected Option A
- [ ] If A or B: deck block payload <= 100,000 B (target <= 80,000 B)
- [ ] Any percentage quoted names its denominator
- [ ] No page-level horizontal overflow at 1280x800 or 390x844
- [ ] Contrast pairs re-measured (the picker's treatment may change)

---

#### Workstream 6 - Responsive Contract (KI-25)

**Owner:** Frontend + Architect
**Blast radius:** `DESIGN.md` §2.3, `runs/show.blade.php`, `race-calendar.blade.php`
**Gates touched:** C-1..C-4, C-7
**Depends on:** Task 6.1 (R82 ratification) for the *contract* work only. Task 6.2 needs no ratification, so it may run in M1 or M2 in parallel; it is the measurement, and the measurement is what tells the owner whether R82 is even the right floor.

##### Task 6.1 - Obtain R82 ratification (owner gate, O-3)

**Brief:** The 768px floor in `DESIGN.md` §2.3 (`:137`, `:146-152`) rests on an unratified proposal (R82, drafted at `PLANS-AND-BRIEFS.md:186+`). The owner must either ratify it or amend D-40 (`DESIGN-CORPUS.md:1834`). This is decision O-3 in Section 0.1.

**Acceptance:**

- [ ] D-40 carries the ratified addendum, or `DESIGN.md` §2.3 is corrected to a ratified floor

##### Task 6.2 - Measure the turn log and race calendar at 390px

**Brief:**

- Data precondition. The calendar reads `race_catalog_slots` (`TrainingRun.php:337-348`). A seeded DB populates that table only via the `seed_file` path that landed at `8b17703`. On the scratch DB, confirm `race_catalog_slots` has rows before measuring.
- If the seed path does not produce rows, measure the turn log only and defer the calendar measurement, recording the reason. Do not report a calendar number that came from an empty table.
- Load `/training-runs/{run}` with a logged turn at 390x844. Read `scrollWidth` and `innerWidth` for the page and for each scroll region.
- Press ArrowRight on the focused log region, measure the `scrollLeft` change, then repeat for the calendar region.

**Acceptance:**

- [ ] Seed verification result and every number pasted into the task record, with the viewport named
- [ ] If `scrollWidth <= innerWidth` for both regions, KI-25's *measurement* obligation is met and the entry moves from RE-OPENED to measured
- [ ] KI-25 does **not** close on the `scrollWidth` comparison alone. KI-25 also carries a D-40/768px reconciliation that Task 6.1 owns; the entry closes only when both are done
- [ ] If `scrollWidth > innerWidth`, escalate to the owner with options: stacked cards, fewer columns, or scope the scroll to the table

##### Task 6.3 - Apply the ratified change

**Depends on:** 6.1 and 6.2

**Acceptance:**

- [ ] The change lands in one commit
- [ ] KI-25 closes with both the measurement and the ratified floor
- [ ] `DESIGN.md` §2.3 carries the ratified contract

---

### 3. Milestones & Sequencing

Milestones are a reporting line, not a lock. Work is ordered by the dependency edges in the diagram below; an agent with a free lane takes the next unblocked task regardless of which milestone letter it carries.

```text
O-1 (push order) ──> gates every register closure in M1, M2, M3

M1  WS-1 (1.1, 1.2, 1.3)  WS-3 (3.1, 3.2, 3.3)        no code deps
     |
     +-- 6.2 (measurement only, no ratification)  ─┐   parallel-safe
     |                                              │
M2  WS-2 (2.1-2.5)  requires 3.1  <────────────────┘   |
     |                                                  |
M3  WS-4 (4.1, 4.2)  requires 3.1                     |
     |                                                  |
M4  WS-5 (5.1)  requires 4.1 IF Option A  <────────────┤
     |                                                  |
M5  WS-6 (6.1 ratification -> 6.3)  requires 6.2 ──────┘
```text

| Milestone                         | Workstreams               | Target                                         | Exit criterion (work)                                                                                                                               | Exit criterion (register)                                                                              |
| --------------------------------- | ------------------------- | ---------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------ |
| **M1 - Immediate fixes + docs**   | WS-1 (all) + WS-3 (all)   | 3-5 days                                       | KI-29 fixed with the browser read attached; D-30 amended; D-31 erratum landed; the seeder helper guarded; KI-45 re-scoped; one doc-drift KI filed   | KI-29, KI-32, KI-48, KI-49, KI-51, KI-53, KI-54 closed, each citing a sha that is on `origin/master`   |
| **M2 - Trainee detail page**      | WS-2 (all)                | 8-12 days                                      | Eight sections render in the order stated in WS-2; every absence string legal                                                                       | KI-35 closes with the `trainee_goals` reservation note                                                 |
| **M3 - Skill selector**           | WS-4 (all)                | 10-14 days                                     | Combobox on the run screen; no skill `<option>` remains; no picker emits more than 10 rows                                                          | KI-33's G-SK-13 clause satisfied                                                                       |
| **M4 - Support cards**            | WS-5 (single task)        | 5-8 days                                       | KI-43 addressed per O-2                                                                                                                             | KI-43 closed or re-scoped with the measurement                                                         |
| **M5 - Responsive**               | WS-6 (all)                | 5-8 days (6.2 itself is 1 day and unblocked)   | D-40 or `DESIGN.md` carries the ratified floor; the 390px measurement recorded                                                                      | KI-25 closed with both the measurement and the ratification                                            |

The work column and the register column are separate on purpose. The work column is verifiable by a reviewer with the tree; the register column depends on O-1 and therefore on the owner. A milestone whose work is done but whose register is not is *work complete*, not *closed*, and the task record says which one it is.

**Sequencing rationale:**

- M1 first: smallest diffs, no code dependencies, clears the register backlog that currently makes every other milestone's exit criteria unverifiable.
- D-30 (Task 3.1) lands in M1, before any UI reads the columns. It is a precondition for both WS-2 and WS-4, so it cannot sit inside either.
- M2 before M3: sequencing risk, not code reuse. The four-band picker is the largest combobox surface here; the detail page proves the skill data shape in a simpler renderer first. Nothing is shared between `skill-row` and `skill-combobox.ts` (see WS-4's dependency note).
- M3 before M4: the generalized combobox is the pattern for the deck panel if Option A is chosen.
- 6.2 is pulled out of the M5 chain. It is a measurement against the current build, needs no ratified contract, and would otherwise sit behind an owner decision it can inform.
- M5 last: the contract work is blocked on O-3; do not build against an unratified floor.
- Every register closure in M1/M2/M3 waits on O-1.

---

### 4. Agent Assignments

| Agent                     | Workstreams                                                                                                        | Skills invoked                                                                                    |
| ------------------------- | ------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------- |
| **Frontend engineer**     | WS-1, WS-2, WS-4, WS-5, WS-6                                                                                       | `laravel-best-practices`, `testing-best-practices`, `test-driven-development`, `gstack:careful`   |
| **Design-system owner**   | WS-2 (design review), WS-5 (design review)                                                                         | `code-review-and-quality`, `doubt-driven-development`                                             |
| **Architect**             | WS-3 (D-30, D-31), WS-6 (ratification)                                                                             | `source-driven-development`, `doubt-driven-development`                                           |
| **Docs Writer**           | WS-3 (KI-48 closure), every register update                                                                        | `antislop-copywriting`, `source-driven-development`                                               |
| **Lore Guardian**         | Every WS-1, WS-2, WS-3 and WS-4 change: those are the four that add or alter user-visible copy or a scanned file   | none - read and rule                                                                              |
| **QA**                    | Every PR's gate run                                                                                                | `code-review-and-quality`                                                                         |

The Lore Guardian row is not a formality on WS-1 or WS-4. WS-1's acceptance is a lore-count check, and WS-4 adds the band labels, the awakening disclosure and the keep-typing line. A change that ships without a Guardian pass has not met its own acceptance list.

**Every dispatch must include:**

1. The task's `file:line` targets (re-derived, not carried)
2. The expected RED run (test written first)
3. The gate commands to run before hand-off
4. The R73 read-list (`SLICE-RECORDS.md:3613`)
5. The branch freshness check idiom from `PROCESS-PLANS.md:77` (`git rev-list --left-right --count HEAD...origin/master`); there is no gate numbered C-11 in this repo

---

### 5. Gates & Verification

Gate locations: C-1..C-9 live in root `CONSTRAINTS.md`; G-*and D-* gates live in the `DESIGN-CORPUS.md` gate table (~`:2558-2615`). `docs/GATE-REGISTRY.md`, which `agents.md` still cites as the gate runner reference, is absent from the tree; the register sweep (Task 1.3) files this.

#### 5.1 The gate sequence, defined once

Every task runs this sequence before hand-off. It is listed here so a task brief can say "the gate sequence" instead of re-listing it:

```bash
vendor/bin/pint --dirty --format agent      # style, fix in place
vendor/bin/phpstan analyse --no-progress --memory-limit=1G   # level 6
composer lore                               # tracked files
composer lore-code                         # adds untracked files + Global client terms
npm run typecheck                          # if a TS/CSS file changed
php artisan test --compact                 # full suite
```text

Notes that are easy to get wrong:

- `phpstan` needs `--memory-limit=1G`; the `composer analyse` script omits it and can OOM on a 128M CLI default.
- Run `pint` before the test run, not after; a formatting fix can change what a rendered-HTML assertion sees.
- `npm run typecheck` is the front-end half of `composer test` and is run separately so a TS-only change does not need a full suite to prove itself.
- `composer lore-code` is additive to `composer lore` (untracked files plus the Global client terminology). Running only `lore` under-reports.
- A Vite manifest error means `npm run build`, not a code defect.

#### 5.2 Per-workstream additional gates

| Workstream   | Additional gates                                                                                            |
| ------------ | ----------------------------------------------------------------------------------------------------------- |
| WS-1         | Browser measurement at two viewports; canary control read; lore counts relative to the task's own pre-run   |
| WS-2         | `DesignTokensTest`, `RenderedCopyHygieneTest`, D-30 amendment landed                                        |
| WS-3         | `DocSchemaDriftTest`, G-60 with retired-literal grep                                                        |
| WS-4         | `npm run build`, `TraineeSelectorTest` green and unmodified, C-8 no new dependency                          |
| WS-5         | Page weight measurement, contrast pairs                                                                     |
| WS-6         | Browser measurement at 390px, D-40/R82 ratification, seed verification                                      |

**Register discipline:**

- A KI closes only when the fix is on `origin/master` and the closure names the sha
- A KI that fails to reproduce closes with the failed reproduction recorded, not silently
- A KI filed on a wrong cause (per KI-22) is corrected forward, not edited in place
- An entry with two independent obligations (KI-25: measurement plus the D-40 reconciliation) closes only when both are met

**Push and closure are two steps, not one.** A closure cannot be written before its fix is on `origin/master`, and the closure is itself a commit. So each milestone is: push the work, verify with `ls-remote`, write the closure commit, push again. There is no single push that both lands the fix and records the closure.

---

### 6. Risks & Mitigations

The full risk table with likelihood, impact and owner is in Section 0.2. It is not repeated here; keeping one copy means an update lands in one place.

---

### 7. Out of Scope

Deliberately not in this plan:

- **Trainee profile fields** (voice actor, birthday, height, three sizes): the master-lineage implementation landed at `555b0cb`; the §3.1 lineage pick in `SESSION-CONSOLIDATION-2026-09-30.md` remains the owner's open decision and gates the alternate chain, not this plan's pages
- **Support-card collection tracking**: cut by `ADR-0014:22`
- **Skill facts on read rows (Phase B2)**: the design is drafted but the storage decision is outstanding (`SKILLS-MECHANICS.md:3383-3386`); it rides with the migration, not with this plan
- **Trainee pre-selection on the create form**: the `umamusume_id` query param is dead in `TrainingRunController::create()`; honoring it is separate scope
- **Legacy Select UI**: schema landed (`ADR-0010`); no screen yet; deferred
- **Live-ops, gacha, event calendar**: cut by `PRD.md` §6
- **Sizing fixes on other surfaces**: audit per the 2026-09-28 frontend review; queue after M1
- **KI-42 (CI)**: owner backlog; not a UI/UX concern

---

### 8. Hand-Off Checklist

Before opening M1:

- [ ] Owner approves the plan
- [ ] **O-1 resolved**: owner decides the push order (push local master first, or branch from local master accepting local-only citations). Register closures cannot precede this.
- [ ] Branch created per O-1's outcome, with `git rev-list --left-right --count HEAD...origin/master` recorded
- [ ] `.agents/` copied into the worktree (per Task 1 Step 3 of `docs/research-scratch/CATALOG-ROSTER-WORKSTREAM.md`)
- [ ] `.env` and `.agents` present; baseline gate run recorded
- [ ] `KNOWN-ISSUES.md` status lines quoted in the slice record
- [ ] R73 read list for the first task assembled

Before closing each milestone:

- [ ] Every task's acceptance criteria checked
- [ ] Every affected KI updated (closures only when the fix is on `origin/master`)
- [ ] `PLAN.md` re-baselined
- [ ] Slice record written under `docs/design-research/verification/`, with slice prose in `docs/research-scratch/SLICE-RECORDS.md`
- [ ] Push 1: the milestone's work commits, `ls-remote` verified
- [ ] Closure commit written citing shas confirmed on `origin/master`
- [ ] Push 2: the closure and record commit. This second push is part of the milestone, not an optional extra; until it lands, the register does not record the fix

---

### Appendix A - Reference Map

| Topic                      | Primary document                                  | Section                                                          |
| -------------------------- | ------------------------------------------------- | ---------------------------------------------------------------- |
| Design system contract     | `DESIGN.md` (root)                                | §2.3, §4.2                                                       |
| Control sizing rule        | `docs/research-scratch/DESIGN-CORPUS.md`          | "DESIGN.md" §6.14, `:904`                                        |
| Design rules (D-numbers)   | `DESIGN-CORPUS.md`                                | "CONSTRAINTS.md" §5, §6, §10                                     |
| Gate table (G-*, D-*)      | `DESIGN-CORPUS.md`                                | ~`:2558-2615`                                                    |
| Gate structure (C-*)       | `CONSTRAINTS.md` (root)                           | C-1..C-9; note `docs/GATE-REGISTRY.md` is absent from the tree   |
| Register                   | `KNOWN-ISSUES.md` (root)                          | per KI number                                                    |
| Frontend plan              | `PLAN.md`                                         | Slice exit criteria, open decisions                              |
| Architecture               | `ARCHITECTURE.md`, `ARCHITECTURE-ESSENTIALS.md`   | §3 schema, §7 frontend                                           |
| Product truth              | `PRD.md`                                          | FR-A through FR-E                                                |
| Skills mechanics           | `docs/research-scratch/SKILLS-MECHANICS.md`       | all sections                                                     |
| Support cards              | `docs/research-scratch/SUPPORT-CARDS.md`          | mechanics + module plan                                          |
| Combobox precedent         | `resources/js/trainee-combobox.ts`                | whole file                                                       |
| Combobox test              | `tests/Feature/TraineeSelectorTest.php`           | whole file                                                       |
| Slice records              | `docs/research-scratch/SLICE-RECORDS.md`          | sections named `slice-NN-YYYY-MM-DD.md`                          |
| Source-of-truth            | `docs/research-scratch/GOVERNANCE.md`             | "SOURCE-OF-TRUTH.md" section                                     |

### Appendix B - Skill Registry Verification

Before any dispatch:

```bash
node "${SKILL_REGISTRY_HOME:-$HOME/.qoder/skills/refresh-skill-registry}/scripts/scan-skills.cjs" --project "$(pwd)" --names
node "${SKILL_REGISTRY_HOME:-$HOME/.qoder/skills/refresh-skill-registry}/scripts/scan-skills.cjs" --project "$(pwd)" --violations
```text

The registry lives outside the repo, so the path is machine-specific. Set `SKILL_REGISTRY_HOME` to wherever the scanner actually is on your host rather than editing these two lines; a hardcoded `$HOME/.qoder` path is a path that only works on one machine. If the scanner is absent, record that in the dispatch record and fall back to reading `.agents/skills.json` by hand. Do not skip the step silently.

Expected summary line: `SKILLS total=N errors=E warnings=W`. Attach to the dispatch record. Measured 2026-10-02 at `31592ac`: `SKILLS total=205 errors=4 warnings=62`. A dated measurement, not a baseline, and a different sha from the `e18a032` basis tree (see Section 1.3). What to do about the 4 errors: record them; they are workspace-wide, not this plan's to fix. If one of a task's own skills is in the error list, name that in the task record.

### Appendix C - What to do when the plan and the tree disagree

The plan is a synthesis. It can be wrong. When a task brief's claim contradicts the tree:

1. **Re-derive before acting.** Open the file. Run the grep. Read the commit.
2. **Cite the file:line or the sha you read it at.** Do not quote the brief.
3. **Record the discrepancy in the task record.** A plan that is silent about its own drift cannot be corrected.
4. **Do not edit the brief.** Amend forward with a dated erratum.

This is D-289 applied to this plan, and the revision note in Appendix D is the first worked example: v1.0 verified its claims against local HEAD while prescribing `origin/master` as the branch base, and listed five tasks whose work had already shipped. Every current-state claim in v1.2 cites a file, a commit, or a KI number, re-derived against the tree on 2026-10-02.

### Appendix D - Revision note (v1.0 -> v1.1 -> v1.2)

Historical record. No task brief depends on it; nothing here is an instruction to an executing agent.

#### D.1 v1.0 -> v1.1

The v1.0 draft was cross-examined by three fresh-context reviewers against the tree. What changed. "v1.0 task" names a task number in the v1.0 draft, which does not match live numbering in v1.1 or v1.2.

| v1.0 claim                                            | Tree state at `e18a032`                                                                     | v1.1 disposition                                | v1.1 task it changed      |
| ----------------------------------------------------- | ------------------------------------------------------------------------------------------- | ----------------------------------------------- | ------------------------- |
| KI-32 lacks `color-scheme`                            | Declarations shipped at `ed71741`; register entry still OPEN                                | Reduced to measurement + register closure       | v1.0 Task 1.2             |
| KI-37 measurement owed                                | Measured 44/44/44/44 both viewports, canary 31; CLOSED 2026-10-01                           | Deleted                                         | v1.0 Task 1.3 (deleted)   |
| KI-48 flat bound still in arch docs                   | Corrected at `79d5f6f` with dated errata; register entry still OPEN                         | Reduced to register closure                     | v1.0 Task 3.2             |
| ADR-0008/0012 lack the skill columns                  | Both carry dated `dd90330` errata (`0012:7`, `:73`; `0008:236`, `:439`)                     | Deleted                                         | v1.0 Task 3.4 (deleted)   |
| DESIGN.md §4.2 still has "amber notice" / "Unknown"   | Rewritten at `bcd8abe`; old text struck through                                             | Deleted                                         | v1.0 Task 3.3 (deleted)   |
| Support-card effect lines unbuilt                     | `SupportCardEffects` + test + deck-panel lines shipped                                      | Deleted                                         | v1.0 Task 5.2 (deleted)   |
| KI-49/51 bodies untracked                             | All nine `database/seeders/data/*.json` tracked (`8b17703`, `30b3a08`)                      | Moved to register sweep                         | v1.0 Task 1.3             |
| KI-53/54 fixture deleted, 11 red                      | Fixture tracked at HEAD; 11-red no longer reproduces                                        | Moved to register sweep                         | v1.0 Task 1.3             |
| "26 consolidated documents"                           | The directory holds 16 consolidated files                                                   | Basis corrected                                 | header                    |
| Branch from `origin/master`                           | `origin/master` is 17 commits behind local master and carries no `docs/research-scratch/`   | Owner gate O-1 added                            | hand-off                  |
| "C-11 branch freshness check"                         | No C-11 exists in the repo                                                                  | Replaced with the `PROCESS-PLANS.md:77` idiom   | Section 4                 |
| Lore baselines 98/55 and 7                            | Current: 181 hits / 55 exempt; lore-code 42                                                 | Acceptance criteria made relative               | all briefs                |

#### D.2 v1.1 -> v1.2

A second review pass over v1.1 found eight defects that would have produced a wrong outcome, and a set of structural ones. All are fixed in v1.2.

**Wrong-outcome defects:**

1. Task 2.1's stale-copy check (`grep -c 'are not stored'` == 0, `assertDontSee('are not stored')`) collided with Task 2.3, whose mandated body contained the same phrase. Landing 2.3 would have broken 2.1's test. Fixed by standardizing on one canonical absence vocabulary ("not recorded") and by narrowing 2.1's assertion to the exact stale sentence.
2. Task 4.1 asked for a test that "constructs the factory twice", which needs a JS test runner that the repo does not have and C-8 forbids adding; it also said to move `TraineeSelectorTest` into a new `ComboboxFactoryTest.php`, contradicting the acceptance criteria, the gate table and the risk table. Fixed: `TraineeSelectorTest` stays put, and the only new test is a source-shape pin, labelled as weaker than it looks.
3. Section order was stated two ways (2.2 put Skills between Costume forms and Goal races; 2.5 listed it third). Fixed by one binding order at the WS-2 head, with 2.5 declared an unordered set.
4. Tasks 4.2 and 4.3 were the same work. Merged into one. WS-4's claimed dependency on the M2 `skill-row` component was unexplained (a Blade component and a TypeScript module share nothing), so it is restated as a sequencing-risk rationale, not a code dependency.
5. WS-6 depended on "M1's seed verification", which no M1 task performed (Task 6.2 does). Task 6.2 also closed KI-25 on `scrollWidth <= innerWidth` alone while KI-25 also carries a D-40/768px reconciliation. Fixed on both counts; 6.2 is now pulled out of the owner-gated chain.
6. "One push" in the hand-off conflicted with the closure rule, since a closure commit follows the push that lands the fix. Fixed: two pushes per milestone, stated explicitly.
7. The revision note said "Task 1.3 deleted" and "Task 3.3 deleted" while v1.1 had live tasks at those numbers. Fixed: every reference in the table is now labelled "v1.0 Task N", plus a column mapping to the live task.
8. Task 1.3's scope contradicted WS-1's stated blast radius ("register entries only") by also allowing a test-helper fix, and left "file a KI, or fix it" undecided. Fixed: the helper is fixed in the sweep, and the blast radius says so.

**Structural fixes:** owner summary and decision table promoted to Section 0; revision note moved to Appendix D; a legend for R73, R82, R85, D-30, D-31, D-40, D-289, G-SK-13, C-n, G-n, Slice n; the gate sequence defined once with exact commands in Section 5.1; a dependency graph; milestone exit criteria split into work vs register; M3 eliminated by folding WS-3 into M1; units in Task 5.1 stated in bytes with denominators required; the `turn N/A` column dropped from Task 2.2; Task 2.4's query placed in the controller with eager loading and a helper line; test fixtures renamed to obviously fake names; the Lore Guardian assigned to WS-1 and WS-4; em dashes removed from headings and prose; ASCII `->` kept as the single arrow form; the scanner path parameterized; the `31592ac` vs `e18a032` difference explained; the 4 scanner errors given a handling instruction; the lore-count movement explained; the risk table given Impact and an owner, and its two duplicate rows merged.

**Not changed:** the evidence discipline (file:line or sha for every claim), the errata-forward convention, RED-first tests, checkbox acceptance criteria, the explicit out-of-scope list, and Appendix C.

---

## PLAN-DOC-SYNC-2026-10-02.md

> Captured 2026-10-03 at `53cc960`, 22 steps closed and 21 open. This section is the live document: the
> separate working file was deleted in the same commit as this embed, and open steps continue here.

Documentation–Register Synchronization Execution Plan

> **For agentic workers:** REQUIRED SUB-SKILL: use `superpowers:executing-plans` (or `superpowers:subagent-driven-development`) to work this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Bring `KNOWN-ISSUES.md` and the markdown corpus (root, `docs/`, `docs/research-scratch/`) into mutual agreement, and add one machine check so the drift cannot silently return.

**Architecture:** Three moves in order. First, establish truth: reconcile the register against the tree and recover the governing inventory the corpus was built without. Second, repair pointers: repoint the citations that follow a reader nowhere, using the consolidation's own absorbed-source map. Third, ratchet: pin the repaired dead-link count in a Pest parity test so any new rot fails a gate instead of accumulating.

**Tech Stack:** Markdown, Python (`tools/doc_census.py`), Pest 4, Git Bash on Windows, Composer scripts.

**Spec:** This document is its own spec; the evidence it argues from is `KNOWN-ISSUES.md`, `docs/research-scratch/INDEX.md`, `docs/research-scratch/CONSOLIDATION-LOG.md`, and the census output baseline recorded below.

**Measured baseline (2026-10-02, HEAD `e18a032`, `python tools/doc_census.py`):** 72 tracked markdown files; **512 dead markdown links (505 GONE, 7 UNTRACKED)**; `docs/research-scratch/` holds 16 masters totalling 28,402 lines; `KNOWN-ISSUES.md` holds **54** `## KI-` entries.

---

### Global Constraints

- `CONSTRAINTS.md` is the bar. No task may weaken a threshold to make a check pass.
- Never overwrite a dated historical claim. Append a dated erratum naming the sha (`79d5f6f`'s treatment of `ARCHITECTURE.md:158` is the model: the original line stays verbatim, `--` lines appended).
- Living documents may be edited in place: `PLAN.md`, `README.md`, `AGENTS.md`, `docs/research-scratch/INDEX.md`, `docs/research-scratch/PLANS-AND-BRIEFS.md`, `docs/research-scratch/PROCESS-PLANS.md`, and this plan.
- Dated records may not: `KNOWN-ISSUES.md` entries, `docs/research-scratch/SLICE-RECORDS.md` sections, `docs/adr/*` (errata appended only), `docs/UMAMUSUME_REFERENCE.md`.
- Do not renumber sections in `docs/research-scratch/*`. Other files cite `§` anchors into them.
- Lore gate is blocking: no equine vocabulary for the characters, no em dashes in shipped UI copy, absence renders as `N/A` plus a `title` or as named copy, never as an em dash or "Unknown".
- `python` only. `python3` is not on PATH on this host (`which python3` returns nothing; `python` is 3.12).
- Do not use `make` (KI-4). Run `composer` scripts or the underlying command.
- Shared worktree: stage named paths only; `git status --short` before every commit; never commit while a gate is running.
- Register discipline: a KI closes only when its fix is on `origin/master` and the closure names the sha.

---

### Part A — Comparative analysis: where the documentation fails the register

| #      | Gap                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       | Evidence                                                                                                           | Severity                       |
| ------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------ | ------------------------------ |
| A-1    | The register's **own status block is stale and self-declares as authoritative**. `KNOWN-ISSUES.md:10` asserts "35 filed, 24 closed, 11 open" (2026-09-30). The file now holds 54 entries; `:28-29` states "The three numbers in this block are the authoritative counts; a heading grep is indicative only."                                                                                                                                                                                                                                                                                                                                                                                                                                              | 54 headings vs 35 claimed; KI-46..KI-54 filed 2026-10-01, KI-37 and KI-47 closed 2026-10-01, all after the block   | **Blocker**                    |
| A-2    | **Seven OPEN entries describe a state the tree no longer has.** KI-32 (fix shipped `ed71741`: `resources/css/app.css:219` light, `:229` dark), KI-48 (fix shipped `79d5f6f`, errata in `ARCHITECTURE-ESSENTIALS.md:36` and `ARCHITECTURE.md:158`), KI-49 and KI-51 (all nine `database/seeders/data/*.json` bodies and the seeder code tracked at `8b17703` / `30b3a08`), KI-53 and KI-54 (fixture tracked, no `.held-aside`, tree clean, the 11-red state no longer reproduces), KI-45 (headline "no offline population path" superseded by `8b17703`: `'seed_file' => 'race_instances.json'` at `config/uma.php:138-145`).                                                                                                                              | 20 entries carry OPEN in a heading without CLOSED/RESOLVED; 7 of them are wrong                                    | **Blocker**                    |
| A-3    | **A doc already asserts what the register denies.** `docs/research-scratch/RACE-AND-SLICE-RESEARCH.md:843` calls KI-48 "the one finding this slice was told to fix — done in §3" while the register keeps KI-48 OPEN. Two sources of truth disagree in public.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            | `RACE-AND-SLICE-RESEARCH.md:843` vs `KNOWN-ISSUES.md:2362`                                                         | **Major**                      |
| A-4    | **The documentation precedence chain points at files that do not exist.** `AGENTS.md:178` ranks `docs/GATE-REGISTRY.md` second of five authorities; `AGENTS.md:5` names `docs/PRE-MORTEM.md` as the risk record; `AGENTS.md:94` says "see GATE-REGISTRY C-5"; `README.md:18` maps `docs/PRE-MORTEM.md`. Neither file is on disk. Both are recoverable at `22e5135^` (13 and 6 commits touch them).                                                                                                                                                                                                                                                                                                                                                        | `git show 22e5135^:docs/GATE-REGISTRY.md` succeeds                                                                 | **Blocker**                    |
| A-5    | **512 dead markdown links**, concentrated on sources the consolidation absorbed without repointing the readers: `docs/design-research/CONSTRAINTS.md` 56, `docs/design-research/DESIGN.md` 42, `docs/GATE-REGISTRY.md` 22, `RAW-FINDINGS.md` 20, `docs/PRE-MORTEM.md` 19, `SKILLS-GAPS.md` 19, `docs/SOURCE-OF-TRUTH.md` 17, `docs/design-research/SKILLS-GAPS.md` 11. 38 files carry citations into `design-research/`, `deprecated/`, `requests/`, `data/`, `flows/`, `frontend-review/`. Densest citing files: `CATALOG-ROSTER-WORKSTREAM.md` 57, `SKILLS-MECHANICS.md` 44, `SLICE-RECORDS.md` 29, `KNOWN-ISSUES.md` 26.                                                                                                                               | `python tools/doc_census.py`                                                                                       | **Major**                      |
| A-6    | **The repoint targets are already known, not guessed.** `docs/research-scratch/INDEX.md` records which master absorbed which source, and the section headings exist: `DESIGN-CORPUS.md:18` `## DESIGN.md`, `:1665` `## CONSTRAINTS.md`; `GOVERNANCE.md:16` `## SOURCE-OF-TRUTH.md`; `SKILLS-MECHANICS.md:2410` `## skills-section-phase-b2-2026-10-01.md`. Only Round 3 repointed its citations (`SCENARIO-PUBLISHER-REFERENCES.md`: 26 inbound); Round 1 and 2 masters sit at 1 inbound each.                                                                                                                                                                                                                                                            | `INDEX.md` tables; census inbound counts                                                                           | **Major**                      |
| A-7    | **The dead-link check exists but gates nothing.** `tools/doc_census.py:54` computes `dead_links()` and prints only the first 20 (`:112`), and no composer script or test invokes it. `composer.json:56` registers `lore` the same way a `docs` script could be registered. Citation rot is therefore invisible after it lands.                                                                                                                                                                                                                                                                                                                                                                                                                            | `grep -n census composer.json` returns nothing                                                                     | **Major** (prevention)         |
| A-8    | **Two KI ids are cited as entries and are not.** `KI-34` (8 citations) is a deliberate reservation with no entry (`KNOWN-ISSUES.md:21-24`); `KI-16` (6 citations) is a renumbering hole the register itself admits: "a genuine renumbering hole, KI-30's pre-merge number" (`:23`). 51 distinct ids are cited outside the register against 54 entries.                                                                                                                                                                                                                                                                                                                                                                                                    | set difference of cited ids vs heading ids                                                                         | **Major**                      |
| A-9    | **Five entries are never referenced outside the register:** KI-31, KI-46, KI-52, KI-53, KI-54. KI-52 (OPEN) is the two-`DESIGN.md`-basename ambiguity, and the register's own headings exhibit it: KI-29 (`:1390`) and KI-37 (`:1781`) both cite "DESIGN.md §6.14", which resolves nowhere — root `DESIGN.md` has no §6.14 and the 44px rule is `DESIGN-CORPUS.md:904`. KI-46 (`turns.0.speed` leaked to the Trainer) has no reflection in the copy rules; its only trace is a test comment at `tests/Feature/SupportCardTest.php:19`.                                                                                                                                                                                                                    | `comm -23` of entry ids vs cited ids                                                                               | **Major**                      |
| A-10   | **The corpus was consolidated on a false premise.** `CONSOLIDATION-LOG.md:4-6` states the governing file `docs/research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md` "is absent from disk and from all git history (verified: `git log --all -- '*DOCUMENTATION-INVENTORY*'` returns nothing)", so sections 6/7/10/11 "could not be read" and a filesystem survey was substituted. The file is **on disk** at `research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md` (101.5 KB, 644 lines), with §6 "Cross-reference map" at `:353` — in the repo-root `research-scratch/` folder, which `.gitignore:87` ignores (`/research-scratch/`). The verification command cannot see an ignored file, so a history-only probe was reported as disk absence.   | `ls research-scratch/`; `git check-ignore -v research-scratch/...`                                                 | **Blocker**                    |
| A-11   | **Seven citations resolve only to untracked files**, so they work on this machine and break on a fresh clone: `README.md:122` → `.agents/README.md`; `docs/SKILL_AUTOMATION.md:29` → `.copilot/instructions.md`; `docs/UMAMUSUME_REFERENCE.md:996,:1042` and `docs/scenarios/09:727,:729` → files inside the ignored root `research-scratch/`; `docs/research-scratch/PROCESS-PLANS.md:27` → the inventory above. `docs/PLAN-UI-UX-2026-10-02.md` is also untracked and unregistered in `INDEX.md`.                                                                                                                                                                                                                                                       | census UNTRACKED list                                                                                              | **Major**                      |
| A-12   | **Every closure is gated on an unpushed branch.** Local master is 17 commits ahead of `origin/master`, which carries no `docs/research-scratch/` at all. Register discipline closes a KI only when the fix is on `origin/master`, so A-2 cannot be actioned until the push is decided.                                                                                                                                                                                                                                                                                                                                                                                                                                                                    | `git rev-list --left-right --count HEAD...origin/master` = `17 0`                                                  | **Blocker** (owner gate O-1)   |

**Not a gap:** the 505 GONE count is not 505 broken instructions. Most are provenance lines inside masters naming the source they absorbed, which are records. The census says so itself (`tools/doc_census.py:109-111`): "Most GONE lines are historical citations inside masters naming the sources they absorbed... repoint only the lines a reader would actually follow." Task 5 triages rather than bulk-rewrites; that distinction is the difference between a repair and a lore-gate-wide vandalism of dated records.

---

### Part B — Execution plan

#### Task 1: Recover the governing inventory and correct the false-absence claim

**Files:**

- Read: `research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md` (`:353` §6 Cross-reference map, and §7, §10, §11)
- Modify (append only): `docs/research-scratch/CONSOLIDATION-LOG.md:1-6`

**Interfaces:**

- Consumes: nothing.
- Produces: the authoritative source→master→section map that Tasks 5 and 6 apply; a corrected record of what the consolidation could actually read.

- [x] **Step 1: Read the sections the consolidation believed lost**

```bash
sed -n '353,460p' research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md
grep -nE '^## (7|10|11)\.' research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md
```text

- [x] **Step 2: Record whether the substituted survey reached the same conclusions.** Diff the inventory's cross-reference map against `docs/research-scratch/INDEX.md`'s absorbed-source tables. Any source the map lists that no master claims is a still-unmerged file, not a repoint target.

- [x] **Step 3: Append the dated erratum** to `CONSOLIDATION-LOG.md`, after the existing `:4-6` paragraph, leaving that paragraph verbatim:

```markdown
> **Corrected 2026-10-02.** The claim above that the inventory is "absent from disk" is wrong. It is on
> disk at `research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md` (644 lines), inside the repo-root
> `research-scratch/` folder that `.gitignore:87` ignores. The verification recorded above
> (`git log --all -- '*DOCUMENTATION-INVENTORY*'`) reads git history only and cannot see an ignored,
> uncommitted file, so a history-only probe was reported as disk absence. §6 "Cross-reference map"
> (`:353`) was therefore available to the consolidation and was not read. The substituted filesystem
> survey stands as the record of what was actually merged; what changes is only that the sections said
> to be unreadable are readable.
```text

- [x] **Step 4: Commit**

```bash
git add docs/research-scratch/CONSOLIDATION-LOG.md
git commit -m "docs(consolidation): correct the false absence claim about the governing inventory

The inventory was on disk in the gitignored root research-scratch/ folder; the
history-only probe could not see it. Sections 6/7/10/11 are readable."
```text

---

#### Task 2: Owner gate O-2 — restore or repoint the two authority documents

**Files:**

- Inspect: `git show 22e5135^:docs/GATE-REGISTRY.md`, `git show 22e5135^:docs/PRE-MORTEM.md`
- Decision brief: `docs/research-scratch/PLANS-AND-BRIEFS.md` (living, append a section)

**Interfaces:**

- Consumes: Task 1's map.
- Produces: the ruling that Task 4's edits and 41 of the 505 GONE links depend on (22 `GATE-REGISTRY` + 19 `PRE-MORTEM`).

- [x] **Step 1: Recover both texts and size them**

```bash
git show 22e5135^:docs/GATE-REGISTRY.md | wc -l
git show 22e5135^:docs/PRE-MORTEM.md | wc -l
git show 22e5135^:docs/GATE-REGISTRY.md | grep -cE '^##? C-[0-9]'
```text

- [x] **Step 2: Put the two options to the owner with their costs.** Append to `PLANS-AND-BRIEFS.md`:
  - **Restore:** `git checkout 22e5135^ -- docs/GATE-REGISTRY.md docs/PRE-MORTEM.md`. Clears 41 citations at once and repairs `AGENTS.md:178`'s precedence chain. Cost: two more files in a corpus that was deliberately consolidated to 16 masters, and `INDEX.md` records them as absorbed into `GOVERNANCE.md` — restoring creates a second copy of content that already has a home.
  - **Repoint:** keep the absorption, and edit `AGENTS.md:5,:94,:178` plus `README.md:18` to name `docs/research-scratch/GOVERNANCE.md` and its sections. Cost: 41 citations reviewed by hand, and the precedence chain loses its named #2.
  - Recommend **Restore** only if the absorbed `GOVERNANCE.md` sections are incomplete for C-5 and the pre-mortem §4 (repo #4) addendum; verify before recommending:

```bash
grep -nE 'C-5|pre-mortem|§4' docs/research-scratch/GOVERNANCE.md | head
```text

- [x] **Step 3: Do not act on the recommendation without the owner's written ruling.** Record the choice and the date in the same section.

- [x] **Step 4: Commit**

```bash
git add docs/research-scratch/PLANS-AND-BRIEFS.md
git commit -m "docs(briefs): put the GATE-REGISTRY and PRE-MORTEM restore-or-repoint choice to the owner"
```text

---

#### Task 3: Reconcile the register against the tree

**Files:**

- Modify: `KNOWN-ISSUES.md` — heading status words plus appended dated blocks per entry; never delete an entry's history.
- Verify against: the shas listed in A-2.

**Interfaces:**

- Consumes: owner gate O-1 (the push). Closures require the fix on `origin/master`.
- Produces: the reconciled statuses and the authoritative filed/closed/open counts that Step 6 restates. Task 4 consumes nothing from here.

- [ ] **Step 1: Establish O-1 first.** — **ASKED IN THE REPORT; unpushed (23 0) at execution, so the fallback ran: verification done, closures HELD.** Ask the owner whether local master is pushed before this task's closures land. If not pushed, do the verification work now and hold the closure lines until after the push; record the hold in each entry rather than writing a premature closure.

- [x] **Step 2: Prove each of the seven claims independently before touching any heading.**

```bash
git show ed71741 --stat | head -5 && grep -n 'color-scheme' resources/css/app.css
git show 79d5f6f --stat | head -6 && sed -n '36p' ARCHITECTURE-ESSENTIALS.md && sed -n '158p' ARCHITECTURE.md
git ls-files database/seeders/data/ | wc -l          # expect 9, was the KI-49/KI-51 complaint
git ls-files database/seeders/data/ | grep -c 'race-tier-labels'   # expect 1, was KI-53/KI-54
sed -n '138,145p' config/uma.php                     # seed_file present, was KI-45's headline
grep -rn 'Schema::hasColumn\|->check(' database/migrations/ | head  # KI-50 still live?
```text

- [ ] **Step 3: Close the six that are done, in house style.** — **HELD per Step 1 fallback: six Verified/HELD blocks written instead; KI-32 was closed concurrently by the M1 batch and its block reconciled to that heading.** KI-32, KI-48, KI-49, KI-51, KI-53 and KI-54 all describe a defect the tree no longer has. Append to each entry, after its existing body:

```markdown
**Closed 2026-10-02 (documentation-sync pass).** The fix landed at `<sha>`; this pass verified it on the
working tree (`<file:line>`) rather than re-deriving it from the report that filed it. Nothing about the
original finding is edited: it was correctly filed on 2026-10-01 against a tree that did not yet have the
fix. What was missing was the closure, and that is a register-lag defect, not a code defect.
```text

and add `, CLOSED 2026-10-02` to the heading, matching the pattern at `KNOWN-ISSUES.md:1781`.

- [ ] **Step 4: Correct-forward the two that are half-true.** KI-45 keeps the NULL-tier gap (`scenario_slots.tier` NULL on 141 of 296) but its headline claim about offline population is superseded; KI-50's `->check()` no-op is still documented only as a test comment at `tests/Feature/SupportCardTest.php:19`. Append a dated correction naming `8b17703` for KI-45 and leave KI-50 OPEN with the verification result recorded.

- [x] **Step 5: File the one genuinely new defect this pass found.** `withTierLabelsFileAbsent()` at `tests/Feature/ScenarioSlotSeederResilienceTest.php:102` renames the fixture with no `file_exists` guard. Use the next free number, state the command that proves it, and follow the entry preamble's rule that each entry names its proof.

- [x] **Step 6: Restate the authoritative status block (closes A-1).** The block at `:10` claims "35 filed, 24 closed, 11 open" and `:28-29` declares those numbers authoritative over any heading grep. Append a new dated block immediately above it, leaving every prior block verbatim, and count by the register's own rule rather than by regex: read each heading once, and count KI-10 and KI-25 by the state their body assigns (KI-10 ratio half open, KI-25 re-opened), because a heading that names both words is one entry, not two.

```markdown
**Status (2026-10-02, documentation-sync pass):** **54 filed, <N> closed, <M> open**, no entry filed here
and <K> closed here, all of them on fixes that landed before this pass. The count is by entry, not by
heading grep: KI-10 and KI-25 each carry both words in one heading and are counted once, in the state
their body assigns. The block below stays as the record of what this register believed on 2026-09-30;
it was accurate when written and is now superseded by nineteen entries' worth of work.
```text

Fill `<N>`, `<M>`, `<K>` from Step 2's verification, not from a grep. Do not delete the superseded block: `:24-29` is the register's own warning that heading greps mislead, and that warning is still load-bearing.

- [x] **Step 7: Verify no entry lost history**

```bash
git diff --stat KNOWN-ISSUES.md            # additions dominate deletions
git diff KNOWN-ISSUES.md | grep -c '^-[^-]' # expect near zero: removals should be heading edits only
```text

- [x] **Step 8: Commit**

```bash
git add KNOWN-ISSUES.md tests/Feature/ScenarioSlotSeederResilienceTest.php
git commit -m "docs(register): reconcile seven entries against the tree and file the unguarded fixture helper"
```text

---

#### Task 4: Repair the doc map and the precedence chain — **HELD on owner gate O-2 (ruling requested in `PLANS-AND-BRIEFS.md`, recommendation Repoint, nothing applied)**

**Files:**

- Modify in place: `AGENTS.md:5`, `:94`, `:178`; `README.md:18` (and `README.md:122` if `.agents/README.md` stays untracked — Task 8).

**Interfaces:**

- Consumes: Task 2's owner ruling.
- Produces: a chain where every named authority resolves on a fresh clone.

- [ ] **Step 1: Replace each dangling authority with the target Task 2 chose.** Example under the repoint branch, `AGENTS.md:178`:

```markdown
When documents disagree: `CONSTRAINTS.md` (the bar) > the gate table in
`docs/research-scratch/GOVERNANCE.md` §"GATE-REGISTRY.md" (how each gate runs) > ADRs (`docs/adr/`)
> `DESIGN.md` > slice plans (`PLAN.md`).
```text

Under the restore branch, leave the chain intact and confirm `docs/GATE-REGISTRY.md` now exists.

- [ ] **Step 2: Fix the same two paths everywhere they appear as instructions, not history.** Read each hit and classify it as a pointer a reader follows (fix) or a provenance line (leave):

```bash
grep -rn 'GATE-REGISTRY\|PRE-MORTEM' --include='*.md' . | grep -v '^\./research-scratch/'
```text

- [ ] **Step 3: Verify the chain resolves**

```bash
for p in CONSTRAINTS.md docs/GATE-REGISTRY.md docs/adr docs/DESIGN.md PLAN.md; do [ -e "$p" ] && echo "ok   $p" || echo "MISS $p"; done
```text

- [ ] **Step 4: Commit**

```bash
git add AGENTS.md README.md
git commit -m "docs(agent-map): point the precedence chain at documents that exist"
```text

---

#### Task 5: Repoint the live citations, in density order, triaged by hand — **PARTIALLY EXECUTED, see the disposition note inside** — the measurement and the worklist are done and committed; the per-file triage of 200 refs across 29 files was scoped down to what a single pass could do without bulk-editing dated records, and the residual is the next pass

**Files:**

- Modify: the masters and root docs named below, section-internal edits only.
- Reference: `docs/research-scratch/INDEX.md` absorbed-source tables and Task 1's §6 map.

**Interfaces:**

- Consumes: Task 1 (map), Task 2 (ruling), and Task 7's ratchet (the baseline is recorded here, enforced there).
- Produces: a dead-link count that Task 7 pins.

- [x] **Step 1: Record the pre-repair baseline so the ratchet has a floor and progress is measurable.**

```bash
python tools/doc_census.py 2>&1 | grep 'dead markdown links' | tee /tmp/dead-baseline.txt
```text

- [x] **Step 2: Generate the per-target worklist instead of guessing at it.**

```bash
python tools/doc_census.py 2>&1 | sed -n '/most-cited dead targets/,$p'
```text

- [ ] **Step 3: Apply the known absorption map to the eight densest targets.** — **SCOPED DOWN:** the two O-2-gated targets (36 + 32 hits) cannot be repointed until the ruling; the convention fix (Task 6) was chosen over bulk editing because KNOWN-ISSUES.md entries are dated records and may not be edited in place, and most of DESIGN-CORPUS / SKILLS-MECHANICS hits are provenance lines the census overcounts. Measured precisely: 200 actionable refs across 29 files (`python .scratch-uma/task5-worklist.py`). Verified section anchors:

| Cited path                                                   | Hits       | Repoint to                                                                                       |
| ------------------------------------------------------------ | ---------- | ------------------------------------------------------------------------------------------------ |
| `docs/design-research/CONSTRAINTS.md`                        | 56         | `docs/research-scratch/DESIGN-CORPUS.md` §`CONSTRAINTS.md` (`:1665`)                             |
| `docs/design-research/DESIGN.md`                             | 42         | `docs/research-scratch/DESIGN-CORPUS.md` §`DESIGN.md` (`:18`); the 44px control rule is `:904`   |
| `docs/GATE-REGISTRY.md`                                      | 22         | per Task 2                                                                                       |
| `RAW-FINDINGS.md`                                            | 20         | `docs/research-scratch/DESIGN-CORPUS.md` §`RAW-FINDINGS.md`                                      |
| `docs/PRE-MORTEM.md`                                         | 19         | per Task 2                                                                                       |
| `SKILLS-GAPS.md` (+ `docs/design-research/SKILLS-GAPS.md`)   | 19 + 11    | `docs/research-scratch/SKILLS-MECHANICS.md` §`SKILLS-GAPS.md`                                    |
| `docs/SOURCE-OF-TRUTH.md`                                    | 17         | `docs/research-scratch/GOVERNANCE.md` §`SOURCE-OF-TRUTH.md` (`:16`)                              |
| `docs/design-research/verification/slice-*.md`               | 15 files   | `docs/research-scratch/SLICE-RECORDS.md` §`slice-NN-YYYY-MM-DD.md`                               |

- [ ] **Step 4: Work the densest citing files first, one file per commit** — **NOT STARTED as a bulk pass**; the register (10 refs) resolved by convention rather than by edit for the same dated-record reason. Remaining: SKILLS-MECHANICS 34, DESIGN-CORPUS 25, CONSOLIDATION-LOG 12, SLICE-RECORDS 12, CATALOG-ROSTER 12, plus ~24 lighter files; PRODUCT.md is machine-generated and excluded, docs/UMAMUSUME_REFERENCE.md was peer-dirty during this pass., in this order: `CATALOG-ROSTER-WORKSTREAM.md` (57), `SKILLS-MECHANICS.md` (44), `SLICE-RECORDS.md` (29), `KNOWN-ISSUES.md` (26), `RACE-AND-SLICE-RESEARCH.md` (18), `GOVERNANCE.md` (16), `docs/adr/0008*` (16), `DESIGN-CORPUS.md` (15), `PROCESS-PLANS.md` (14), `PLAN.md` (11), `PLANS-AND-BRIEFS.md` (10).

- [ ] **Step 5: Keep the rules distinct per file class.**
  - Masters and ADRs: replace a followed pointer inline only where the sentence directs a reader; where the line is a dated record, append a dated erratum instead and leave the original path visible.
  - Never renumber or reorder a section heading; other files anchor into it.
  - `docs/adr/*`: appended errata only (`ADR-0012:7,:73` is the working example).
  - Prove every regex before it runs. These files are hard-wrapped prose; a line-based `sed` will silently miss a citation split across lines and will happily rewrite a provenance line. Check each pattern's match count first:

```bash
grep -cE 'docs/design-research/CONSTRAINTS\.md' docs/research-scratch/DESIGN-CORPUS.md
```text

- [ ] **Step 6: Re-measure after each file and confirm the count falls by the expected amount.** On the final pass, record the number Task 7 pins:

```bash
python tools/doc_census.py 2>&1 | grep 'dead markdown links' | tee /tmp/dead-post.txt
composer lore && composer lore-code
```text

The lore counts move when a citation is rewritten into a section title that carries a banned word; current baseline is 181 hits / 55 exempt and lore-code 42 (measured 2026-10-02 at `e18a032`). Capture the pre-run number per task and compare to it, never to a dated constant.

- [ ] **Step 7: Commit per file**

```bash
git add docs/research-scratch/CATALOG-ROSTER-WORKSTREAM.md
git commit -m "docs(research-scratch): repoint CONSTRAINTS and DESIGN citations into DESIGN-CORPUS sections"
```text

---

#### Task 6: Fix the KI-id citation defects and the DESIGN.md ambiguity convention

**Files:**

- Modify: the 6 `KI-16` and 8 `KI-34` citations outside the register.
- Modify: `docs/research-scratch/DESIGN-CORPUS.md` (appended dated convention note) and `KNOWN-ISSUES.md` (KI-52 note).

**Interfaces:**

- Consumes: Task 3 (KI-16's renumbering is documented at `KNOWN-ISSUES.md:23`).
- Produces: a citation form that resolves for both the register's own headings and the corpus.

- [ ] **Step 1: Repoint `KI-16` to `KI-30`**, keeping the pre-merge number visible on first mention per file, since the register's own entry says it was filed as KI-16: `KI-30 (filed as KI-16 on`fix/frontend-audit-2026-09-28`)`.

- [ ] **Step 2: Annotate every `KI-34` citation** so a reader cannot hunt for an entry that does not exist: `KI-34 (a reservation, not a landed entry; see`KNOWN-ISSUES.md:21-24`)`.

- [ ] **Step 3: Write the disambiguation convention.** `DESIGN.md` is a basename shared by root `DESIGN.md` and by `DESIGN-CORPUS.md` §`DESIGN.md`, and `docs/design-research/DESIGN.md` no longer exists. Root `DESIGN.md` has no §6.14, so KI-29's and KI-37's headings cite a section number that resolves only through the corpus. Append to `DESIGN-CORPUS.md` and note it under KI-52:

```markdown
**Convention (2026-10-02).** A bare `DESIGN.md` citation means the root contract. A section of the corpus
is cited as `DESIGN-CORPUS.md §"DESIGN.md" §6.14`. Register headings KI-29 and KI-37 predate this and
name `DESIGN.md §6.14` for a rule that lives at `DESIGN-CORPUS.md:904`; they are left as filed, and this
convention is the correction forward that KI-52 asks for.
```text

- [ ] **Step 4: Give the never-cited entries the coverage each one deserves.** Five entries appear nowhere outside the register (KI-31, KI-46, KI-52, KI-53, KI-54), and only one of them needs a doc:
  - KI-52 is handled by Step 3's convention.
  - KI-46 (`turns.0.speed` printed to the Trainer in an import error) is live user-facing copy with no reflection outside the register and one test comment at `tests/Feature/SupportCardTest.php:19`. Add it to `PLAN.md`'s open-frontend-work list, since `PLAN.md` is the living frontend plan and this is a copy defect:

```markdown
| Open | KI-46 | Import error text names the internal array path (`turns.0.speed`) to the Trainer.|
  Registered only in `KNOWN-ISSUES.md`; needs a Form Request message rewrite, not a display patch. |
```text

- KI-31, KI-53, KI-54 are closed or being closed by Task 3, and a historical defect with no live surface does not need doc echo. Record that reasoning in Task 3's sweep note so the absence reads as a decision rather than an oversight.

- [ ] **Step 5: Verify no unresolved KI id remains cited outside the register.** The register itself is excluded on purpose: it names KI-16 and KI-34 while explaining they have no entry.

```bash
grep -oE '^## KI-[0-9]+[ab]?' KNOWN-ISSUES.md | sed 's/^## //' | sort -u > /tmp/e.txt
git grep -oE 'KI-[0-9]+[ab]?\b' -- '*.md' | grep -v '^KNOWN-ISSUES\.md:' \
  | sed 's/^[^:]*://' | sort -u > /tmp/c.txt
comm -13 /tmp/e.txt /tmp/c.txt    # expect: nothing. KI-16 and KI-34 must be gone from /tmp/c.txt
```text

- [ ] **Step 6: Commit**

```bash
git commit -am "docs(citations): resolve the KI-16 and KI-34 id defects and fix the DESIGN.md convention"
```text

---

#### Task 7: Ratchet the dead-link count so this cannot recur

**Files:**

- Create: `tests/Feature/DocCitationParityTest.php`
- Modify: `composer.json` (scripts), `CONSTRAINTS.md` only if a gate row is added — the bar is not lowered anywhere.

**Interfaces:**

- Consumes: Task 5's post-repair baseline.
- Produces: a gate every future doc edit hits, mirroring `tests/Feature/LoreGateParityTest.php` (which pins `tools/lore.php`) and `tests/Feature/DocSchemaDriftTest.php`.

- [x] **Step 1: Write the failing test first.** It pins the count at the post-repair number Task 5 Step 6 measured, so the figure may fall and never rise. The single value below is filled from that measurement (`grep 'dead markdown links' /tmp/dead-post.txt`), exactly as `LoreGateParityTest:125` pins the composer script strings it guards:

```php
<?php

declare(strict_types=1);

it('keeps dead markdown citations from rising above the recorded baseline', function (): void {
    $out = (string) shell_exec('python tools/doc_census.py 2>&1');
    expect($out)->toMatch('/dead markdown links: \d+/');
    preg_match('/dead markdown links: (\d+)/', $out, $m);

    // Ratchet, set from the 2026-10-02 post-repair census run. Lower it when a
    // citation is repointed; never raise it to make a failing gate pass.
    expect((int) $m[1])->toBeLessThanOrEqual(DOC_CITATION_BASELINE);
});
```text

Define `DOC_CITATION_BASELINE` as an `int` constant at the top of the file, set to the measured number.

- [x] **Step 2: Run it and confirm it fails against the pre-repair count and passes at the baseline**

```bash
php artisan test --compact --filter=DocCitationParityTest
```text

- [x] **Step 3: Register the script beside `lore` in `composer.json`.** Existing entries are `"lore": "@php tools/lore.php"` and `"lint": "pint --test"`, so a plain shell command is the correct form here (`@` is only valid for composer's own aliases such as `@php`; there is no `@python`):

```json
"docs": "python tools/doc_census.py",
```text

Verify with `composer docs`. Deliberately **not** mirrored into the `Makefile`: GNU make cannot run on this host (KI-4) and the Makefile targets are documentation, so the `make`/composer parity assertion in `LoreGateParityTest:101-116` is left unextended for `docs`. Record that reason in the test's comment so a later reader does not "fix" the asymmetry by adding a dead target.

- [x] **Step 4: Note the truncation limit so the test is not mistaken for a full report** — `tools/doc_census.py:112` prints only `dead[:20]`, so the count is the signal and the per-target summary is the worklist. Record that in the test's comment; do not print 500 lines in a gate.

- [x] **Step 5: Commit**

```bash
git add tests/Feature/DocCitationParityTest.php composer.json
git commit -m "feat(docs): ratchet dead citation counts in a Pest gate"
```text

---

#### Task 8: Resolve the untracked-cited assets and register the two plans

**Files:**

- Decide: `.agents/README.md`, `.copilot/instructions.md`, root `research-scratch/*` (`gitignored at .gitignore:87`).
- Modify: `docs/research-scratch/INDEX.md`; commit `docs/PLAN-UI-UX-2026-10-02.md`.

**Interfaces:**

- Consumes: nothing; blocks the census seeing this plan (it reads `git ls-files`, so an uncommitted file is invisible to its own gate).

- [ ] **Step 1: For each of the seven UNTRACKED citations, commit the target or withdraw the pointer.** — **PENDING O-3** (24 UNTRACKED remain; the two plans are no longer among them — both tracked now) `.agents/` is a tooling layer the repo map already documents; `.copilot/instructions.md` is machine-local and should be withdrawn from `docs/SKILL_AUTOMATION.md:29` rather than committed. Ask before adding files to git; do not un-ignore `research-scratch/` without the owner, since the folder holds scratch bodies by design.

- [ ] **Step 2: Decide the root `research-scratch/` disposition with the owner.** — **PENDING O-3** It contains the governing inventory and cited scrape files. Options: promote the durable ones into `docs/research-scratch/` as registered masters, or leave them ignored and mark every citation as scratch-only. Leaving it ignored while docs cite into it is the state that produced A-10.

- [x] **Step 3: Commit and register both plans**

```bash
git add docs/PLAN-UI-UX-2026-10-02.md docs/PLAN-DOC-SYNC-2026-10-02.md docs/research-scratch/INDEX.md
git commit -m "docs: register the UI/UX and documentation-sync plans in the index"
```text

- [x] **Step 4: Verify the census now sees them**

```bash
python tools/doc_census.py 2>&1 | grep -E 'tracked markdown|dead markdown links'
```text

---

### Owner gates this plan cannot pass alone

| Gate      | Question                                                                                                                             | Blocks                                                 |
| --------- | ------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------ |
| **O-1**   | Push local master (17 ahead, includes all of `docs/research-scratch/`) before register closures land?                                | Task 3's closure lines; every KI closure in the repo   |
| **O-2**   | Restore `docs/GATE-REGISTRY.md` + `docs/PRE-MORTEM.md` from `22e5135^`, or repoint the 41 citations into `GOVERNANCE.md` sections?   | Tasks 4 and 5                                          |
| **O-3**   | Promote durable files out of the gitignored root `research-scratch/`, or declare every citation into it scratch-only?                | Task 8, and it decides whether A-10 can happen again   |

### Out of scope

- Any code, migration, or view change. This plan edits documentation and one test.
- `CONSTRAINTS.md` thresholds, in any direction.
- Rewriting ADR or slice-record history, resolving KI-15's source contradiction, or ruling on the KI-25 768px floor (R82) — each is a product or schema decision, not a citation repair.
- The `docs/PLAN-UI-UX-2026-10-02.md` workstreams themselves; that plan owns them.

### Self-review against the analysis

**Coverage.** A-1 → Task 3 Step 6 (status block) with the recount rule it names. A-2 → Task 3 Steps 2-4. A-3 → Task 3 Step 4. A-4 → Tasks 2 and 4. A-5, A-6 → Task 5. A-7 → Task 7. A-8, A-9 → Task 6 Steps 1-4. A-10 → Task 1. A-11 → Task 8. A-12 → owner gate O-1, applied as Task 3 Step 1's precondition.

**Commands verified while writing this plan**, not assumed: the dead-link baseline and the UNTRACKED list (`python tools/doc_census.py`, plus the `dead_links()` one-liner); the recovery reads `git show 22e5135^:docs/GATE-REGISTRY.md` (159 lines) and `...:docs/PRE-MORTEM.md` (98 lines); the id-arithmetic recipe in Task 6 Step 5, which today returns exactly `KI-16` and `KI-34`; `python` present and `python3` absent; `git rev-list --left-right --count HEAD...origin/master` = `17 0`.

**Defects found in this plan by its own review and fixed inline:**

- A `"@python"` composer script form was written first. Composer only aliases its own commands (`@php`), and the existing `"lint": "pint --test"` proves a bare shell command is correct. Task 7 Step 3 now uses `"docs": "python tools/doc_census.py"`.
- A fabricated ratchet number was replaced by a constant filled from the measurement recorded in Task 5 Step 6, because a pinned figure nobody measured is how a gate gets lowered later to pass.
- The id-arithmetic check first excluded the register with a git pathspec (`:!KNOWN-ISSUES.md`) and then verified the wrong direction of the set difference. Both are corrected in Task 6 Step 5, which is the version that was run.
- A-1 had no task when the analysis was written; the status block is stale and self-declaring, so it needed a recount step of its own rather than riding on Task 4.

**What this plan does not claim.** It does not claim the 505 GONE links are 505 broken instructions, nor that repointing them is a mechanical pass: Task 5 triages per line, and the census's own note (`tools/doc_census.py:109-111`) is quoted as the rule. The final count after Task 5 is unknown here by design, and Task 7 pins whatever it measures.

---

## PLAN.md (root frontend slice plan and owner rulings)

## Trainer Desk — Frontend Development Plan

**Status:** Phase 4 (Implementation) active through Slice 10, the maintenance and drift slice: the register corrected against its own commits, the lore gate given a line-scoped marker, the shop price answered at its field, and one bounded proposal written about `scenario_slots`. No panel work, no seeding migration, no new tokens.
**Intent (R41), as the owner stated it:** "Slice 8 is the scenario panel set: the Unity Cup and
Trackblazer surfaces built on Slice 7's payloads and buckets, plus the missing writers." Nothing
about that intent needed a new table, and none was added: rank, fatigue and purchases ride
`turn_events` payloads, circles and the shop countdown ride columns on existing tables.
Slice 7 was the schema session. Slice 6 landed the
frontend residuals; Slice 7 gave Grade Points the period they belong to (KI-10's schema half) and
gave `turn_events.deltas` a typed shape (D-226), then declined ADR-0005 for Phase 1 per R37. Phase 6
remains unfrozen and unstarted. Unity Cup and Trackblazer panel UI is Slice 8, not this slice.
**Last Updated:** 2026-10-02 (documentation-sync dispatch: the trainee detail page restructured to the WS-2 contract at `80caefd` — eight sections in binding order, the canonical absence vocabulary, the retired "skill lists are not stored" copy replaced, and the per-form aptitude grid promoted to one trainee-level section; the doc-sync register and citation work at `30202bb`, `ee93b38`, `1bd8eb1`, `0c42168`; the O-2/O-3 owner packages at `220ed67`, `a3f6df9`. Three owner gates remain open and nothing above is pushed.)
**Last Updated (prior):** 2026-09-29 (Slice 10: the register's KI-18 closure and KI-15 de-duplication at
`ebfc227`, the subject-prefix erratum and this file's own two repairs at `ec0ee2f`, the line marker
with its three guards at `8c9faf9`, the count history on the registry at `11525ec`, KI-19 at
`fe24dc0`, the validation envelope that had never rendered at `6aa81eb`, the cost field error at
`3b15a6c`, KI-20 at `99f5784`, ADR-0009 proposed at `802d1b9`; `slice-10-2026-09-29.md` carries the
same-version re-audit at 18/20, the marker measurements, and the gates pasted in CONSTRAINTS order.
**Lore baseline, settled: `lore-docs` 98 hits with 51 exempt lines, `lore-code` 7.** The 147 it replaced
was the gate quoting itself; a new itemization row in a rules table no longer moves the number.)
Before it, 2026-09-28 (Slice 9: KI-17 and KI-18 filed at `01a1091`, the burst label map and
the loaded-collection ladder at `f7a59e8`, the D-230 amendment at `4282146`, the purchase writer at
`d73a449`; `slice-9-2026-09-28.md` carries the lore itemization, the coherence evidence and the
branch attribution.) Before it, Slice 7: the Grade Point period columns and meter at `e103122`,
typed payloads at `17dbc54`/`8955394`, that slice's re-baseline plus KI-15 and the ADR-0005 status
at its docs commit; `slice-7-2026-09-28.md` carries the greps, the test names and the one thing
stopped on. Before that, Slice 6's mood pill and measured pairs (`slice-6-2026-09-28.md`).

---

### Slice Exit Criteria

Every slice now opens and closes with two mechanical lines, per R38. **Opening snapshot:** `git branch --show-current` and `git status --porcelain` recorded in the slice record before any edit, repeated before every commit and push. **Push verification:** after `git push`, `git ls-remote origin master` must equal the sha just pushed, and the line is recorded. Both were done in Slice 8; the record§1 holds the snapshot and §2 the incident the practice caught (a shared index let the peer's staged file into one commit).
**Branch attribution (R44):** Slice 8 was built on `docs/frontend-review` after a concurrent session switched the shared worktree there mid-slice; `master` was fast-forwarded to `122d12b` at the start of Slice 9, so every Slice 8 commit is now on `master`. One of them, `4c6486a`, also carries `docs/scenarios/09-global-race-calendar.md`, which is that session’s file and their words: it entered through the shared index, and the attribution stands here rather than in a rewrite.

**Subject-prefix erratum (R50, 2026-09-29):** `68fa190` is prefixed `docs(slice-7)` and is a Slice 9
commit. Its diff adds `docs/design-research/verification/slice-9-2026-09-28.md` and re-baselines this
file's Slice 9 header; nothing in it belongs to Slice 7, whose docs commit is `94db315`. The prefix is
wrong on its face, so read the body and the diffstat, not the subject line, when a slice is traced
through `git log --grep`. Left as shipped because the only other fix is rewriting a commit that is
already on `origin/master`, and Slice 9's brief forbade a history rewrite. T5 of this slice adds the
standing guard: an exit criterion that asserts a commit's subject prefix names the slice its diff
actually belongs to.

**Two PLAN repairs landed with the erratum**, both from earlier slices' edits to this file:
the H1 on line 1 opened with a leaked shell placeholder, `` `$PANELS` `` glued in front of
`# Trainer Desk`, from `5d2ddcc`; and Slice 9's re-baseline rewrote the first line of a multi-line
"Last Updated" parenthetical and left the Slice 7 half-sentence stranded underneath it as an orphan
sentence. Both are restored here, the second by re-chaining it as "Before it, Slice 7" so no
recorded sha is dropped.

**Every slice must satisfy all of the following before being marked complete:**

1. **Gates green with pasted output** — `pest`, `pint --test`, `phpstan --no-progress`, `make lore` (manual), `make lore-code` (manual), `npx vite build`, `DesignTokensTest` all pass; outputs recorded in commit message or linked CI run. **Migration gate:** `migrate:fresh --seed` is a destructive drop and is not run here; the equivalent evidence is `php artisan migrate` plus `db:seed` applied to a **fresh empty scratch DB** (`DB_DATABASE=/tmp/…`), which proves the same thing with no blast radius on the shared dev file. **Lore baseline (R51, 2026-09-29):** `lore-docs` 98 hits / 51 exempt lines, `lore-code` 7; a rules table quoting a banned word to rule on it carries a line marker naming `lore-ignore-line` with `class=` set to one of the four allowed-hit classes and `cite=` set to the rule it answers to (the exact form, with a worked example, is in `docs/research-scratch/GOVERNANCE.md` §"GATE-REGISTRY.md"; markers are legal inside `docs/` and nowhere else), and `LoreGateParityTest` fails a marker outside `docs/`, one missing either half, or a runner that stops honouring the filter.
2. **Every status claim cites file:line or commit sha** — no "done" without evidence.
3. **No open D-number violation in touched files** — `git grep -n D-XXX` in changed files returns zero unresolved hits.
4. **No false/stale status lines** — plan doc re-baselined against tree in the same commit.
5. **Atomic commit per concern** — code, docs, tests, ADRs separated; no mixed drift.
6. **R17 fixture present** — any browser or contrast measurement in a slice runs against the
   five-run fixture (populated URA, populated Unity Cup, populated Trackblazer, a no-scenario
   run, and a run with an unpriceable entry) on an isolated scratch database. The shared
   `database/database.sqlite` is never opened: `SESSION_DRIVER=database` writes a session row
   per request. A claim about a panel that only one of the five states can produce is not
   evidence — the no-scenario regression `32cd78b` is what that rule exists to catch.
7. **Checkout coherence (boot files, not test coverage)** — after the last commit of a slice, every file the committed code
   resolves against is tracked: `git ls-tree -r HEAD database/migrations | wc -l` equals the
   on-disk count, and `git grep -l <NewClass> HEAD` finds a definition, not only a reference.
   Added because KI-13 shows a green suite can coexist with a branch that will not boot.
8. **Every cited sha verified via `git cat-file -e` in-session** — a record's own sha is labelled self-citation.
9. **A commit's subject prefix names the slice its diff belongs to** — checked against the diffstat, not the intent: a commit that adds `slice-9-…md` is a Slice 9 commit. `68fa190` carried `docs(slice-7)` on Slice 9's work, which is why this is a criterion and not a convention; the erratum is `docs(plan)` at `ec0ee2f`. Prefixes are read by `git log --grep` when a slice is traced after the fact, so a wrong one is a false index, and the fix is recorded rather than rewritten when the commit is already on `origin/master`.
10. **A UI claim is measured on the rendered DOM, not the HTML source or the HTTP layer (R71).** Three slices were wrong in a row for want of this. Slice 11 called a T4 regression "pre-existing" because `git stash` reverts tracked modifications only and the regression was already committed, so the probe could not have shown anything else. Slice 11's browser check said the free-race form worked because "Naruta Kinpa Cup" appeared eight times in the response — all server-rendered calendar text, none of it the form. Slice 12 reported R61's rule as deleted because `grep isFreeRace` missed the `$manual` parameter that had replaced it. Each check was soundly built for a different question. So: a rendered branch is asserted by resolving the field through a DOM parser and refusing any node whose ancestor chain holds a `template` (Blade emits a declarative branch and the browser is what makes it inert, so `assertSee('name="title"')` passes against markup nobody can reach); a form is exercised by submitting what the page actually rendered, not by posting the fields the component was meant to show; and a `grep` a comment can satisfy is not a check — reword the comment.
11. **A branch is proved fresh against `origin/master` before it is built on, with a command that can report the wrong answer (R72, 2026-09-30).** Before branching off `master`, and before merging, run:

```text
```

git fetch
git rev-list --left-right --count HEAD...origin/master    # "<ahead>\t<behind>"

```text

The **behind** count must be `0`, and it must be read as the second column. `git log --oneline origin/master..HEAD` is not a substitute and is the form this criterion replaces: it lists commits reachable from `HEAD` but not `origin/master`, which is commits **ahead**, so it reports `0` on tip, on a branch 7 behind, and on one 700 behind alike — it cannot fail for the reason it is written for. The form was given as a merge-time rule with that reasoning and was wrong in the same way. This is the fifth time one run produced a check that ran, passed, and proved nothing about the case it was written for. The other four are: a search test that asserted `active = -1` while its fixture ordered the rows the other way, so it was green against a relation the query never produces; the `data-combobox-selected` contract, which cited a hook that never shipped and therefore constrained nothing; a brief that said `skipped 5` where the fixture showed a different count, arithmetic checked against nothing; and the stale fork 7 commits behind that this rule now catches, where `origin/master..HEAD` was read as behind and so reported `0` on a branch that was not on tip. KI-39 (`SkillFactory` deriving `match_key` with a different algorithm than production) is a sixth instance of the same class, at fixture grain rather than branch grain. **The general guard is a fingerprint, not more tests:** before citing any two measurements as one, compare `git rev-parse HEAD` and a file hash between them, and treat a line number that disagrees with an expected value as the check firing rather than as noise. `rev-list --left-right --count` is the same discipline against a branch: one command, two columns, one that is allowed to be non-zero.
```

 1. **A subagent past 100 turns files a checkpoint before it keeps going.** A subagent that has run more than 100 turns must stop and report — what landed, what is in flight, what is blocked, and the tip SHA of every branch or worktree it created — before continuing. The report may be three lines; the requirement is only that a hard turn cap never loses work silently. This exists because two implementers on the character-detail-page branch hit the 150-turn cap in one run, and one reached it with no report at all, so the main session discovered an un-stated half-ported surface by diffing, not by reading a hand-back. A checkpoint is what turns "lost in a killed subagent" into "resumable."
 2. **Before creating a file that touches schema, migrations, or a same-named class, check for a collision on another tracked branch.** Criterion 11 asks whether *this* branch is fresh against `origin/master`; this asks a different question — whether *another* branch already created the files about to be created. Run:

```text
```

git fetch --all
git log --oneline --all -- database/migrations/*create_umamusume_profiles_table* \
    app/Models/UmamusumeProfile.php app/Actions/StoreCharacterProfiles.php

```text

Generalize the path list to the files the feature will create or the classes it will name. If any tracked branch (not just `origin/master`) has touched a file about to be created, **stop and reconcile before branching** — do not open a parallel implementation of the same table or class. This is the check that would have caught the character-detail-page collision at the start instead of at merge time: two sessions in two worktrees each wrote a migration creating `umamusume_profiles` (under different filenames, so git saw a clean merge that then failed at `migrate`), and each named a class `UmamusumeProfile` / `StoreCharacterProfiles` / `GametoraCharacterProfileParser` — the exact set that made the branches unmergeable and forced a port-not-merge. `git log --oneline --all -- <path>` is the tool because it lists every ref holding a change to that path, which a `HEAD..origin/master` freshness check cannot see.
```

---

### Phase Status (Re-baselined 2026-09-27)

| Phase                       | Status                                                                     | Key Shas                                                                                                                                                                                                                                                                                                   |
| --------------------------- | -------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **0 — Scope Contract**      | Stable                                                                     | `CONSTRAINTS.md` C-1..C-8                                                                                                                                                                                                                                                                                  |
| **1 — Evidence Research**   | Stable                                                                     | `RAW-FINDINGS.md`, `SCREENSHOT-MANIFEST.md`                                                                                                                                                                                                                                                                |
| **2 — Design Contract**     | Locked & Amended                                                           | `DESIGN.md` (root + research), `PRODUCT.md`                                                                                                                                                                                                                                                                |
| **2.5 — Prototype**         | Converged                                                                  | `prototypes/screen-a-scenario-v10.html`                                                                                                                                                                                                                                                                    |
| **3 — Spec Intake**         | Completed                                                                  | `FRONTEND-SPEC-DIVERGENCE.md`, `FRONTEND-BRIEF-AUDIT.md`                                                                                                                                                                                                                                                   |
| **4 — Implementation**      | **Slice 1 Complete**                                                       | `1e859e8` (T2+T3), `e9a944a` (T4), `c70967f` (T5), `b0bb0d5` (T7)                                                                                                                                                                                                                                          |
| **4 — Implementation**      | **Slice 2 Complete**                                                       | S1 `9134206`, S2 `83084ab`, S3 `bb6eec6`, S4 `d53a4b1`, S5 `70f9218` + `9451659`, S6 (this commit)                                                                                                                                                                                                         |
| **4 — Implementation**      | **Slice 3 Complete**                                                       | T1 `ab915f8`, T2 `726f106` + `78697e9`, T3 `2816309`, T4 `725a5ff`, T5 `5c65597`, T6 `5548b9e`, T7 `ee97869` + `cc3f963` + `21f9906` + `ee6786c`, T8 `dc13d8d` + `35fb0c7`, T9 `33949f5`                                                                                                                   |
| **4 — Implementation**      | **Slice 4 Complete (repo-integrity)**                                      | T1 `35fb0c7`, T2 `dc13d8d`, T3 coherence outputs (20/20 migrations, ScenarioSlot+Preference defs, 23 tables), T4 push shas `9b774f9`/`33949f5`, T5 docs commit, T6 gates                                                                                                                                   |
| **4 — Implementation**      | **Slice 5 Complete (frontend only)**                                       | T0 `cd0be38`, T1 `d50a0ec` + `2685a37` + `edeb8cd` + `03a5d05`, T2 `71bbb4b`, T3 `6a53c15`, T4/T5 this commit                                                                                                                                                                                              |
| **4 — Implementation**      | **Slice 8 Complete**                                                       | Panels and scenario widgets; KI-17, KI-18 filed                                                                                                                                                                                                                                                            |
| **4 — Implementation**      | **Slice 9 Complete**                                                       | `f7a59e8` (KI-18 closed), mood pill tokens                                                                                                                                                                                                                                                                 |
| **4 — Implementation**      | **Slice 10 Complete**                                                      | Maintenance: R51 marker, R52 KI-18 bookkeeping, ADR-0009 draft, KI-19/KI-20 filed                                                                                                                                                                                                                          |
| **4 — Implementation**      | **Slice 11 Complete**                                                      | `c0a743f` (T1), `f0ae288` (T2), `70248b3` (T3), `5820e77` (T4), `65f8b92` (T5); KI-20 closed. Three claims corrected by Slice 12: the "pre-existing" failure label was a T4 regression, "297 rows / `ura_finale_slots.json`" is 296 rows across three real files, and R59's record commit was never made   |
| **4 — Implementation**      | **Slice 12 HALTED at T3** (corrected; it was recorded Complete in error)   | `c86ed9f` (T0), `2d0c1dc` (T1), `b295d16` (T2), `37ccc08` (T3 findings + halt). T4 never ran: KI-21 and KI-22 filed, `origin/master` held at `4992282` all slice. KI-22 later found to be filed on a wrong cause                                                                                           |
| **4 — Implementation**      | **Slice 13 Complete**                                                      | `6c1969f` (T1+T2, KI-21 closed), `cb9b61f` + `5ed1ebd` (T3 pins), this commit (T5 docs). Suite 562 passed, all gates green, deferred push finally made                                                                                                                                                     |
| **4 — Implementation**      | **Slice 14 Complete**                                                      | `329cec1` (T1 tier join, G-16c green), `24e491c` (D-153/§1.2.6/G-16c dated), `3ae437d` (T2 quiet edge). Suite 569 passed. 118 tiers restored per race, none lost; parser left a documented dissent because R72's idle condition failed                                                                     |
| **5 — Verification**        | Routine                                                                    | Browser metrics: light 4.74 / dark 5.48 / badges 9.00+; energy bands 6.40 / 8.34 / 10.89 light and 12.60 / 10.57 / 6.88 dark, and the preview pairs, in `slice-5-2026-09-28.md`                                                                                                                            |
| **6 — Iteration**           | Unfrozen by Slice 2, **not started**                                       | Owner instruction: the slice's commit unfreezes it; no Phase 6 anatomy in this session                                                                                                                                                                                                                     |

---

### Slice 1 Summary (2026-09-27)

| Task                                                   | Commit                              | Verification                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
| ------------------------------------------------------ | ----------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| T2a: `+ 0 breakthrough` → `breakthrough not tracked`   | `1e859e8`                           | `stat-band.blade.php:153`                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
| T2b: grade ladder 150→50, disclosure fixed             | `1e859e8`                           | `config/scenarios.php:60-63`, `stat-band.blade.php:159-162`                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| T3: US-10 P2→P1 with ADR-0003 criteria                 | `1e859e8`                           | `PRD.md:35`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| T4: ADR-0003 Amendment R1                              | `e9a944a`                           | `docs/adr/0003...md`                                                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| T5: DesignTokensTest wired                             | `c70967f`                           | `tests/Feature/DesignTokensTest.php`                                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| T6: Gates run                                          | re-run 2026-09-28                   | **Green:** pest 217 passed / 2 skipped; `pint --test` PASS 141 files; phpstan `[OK] No errors`; `lore` + `lore-code` clean (4 allowed-sense hits, all adjudicated); `vite build` ok; migrations + seeders apply to a fresh scratch DB. **Not green:** `DesignTokensTest` passes 9 but **skips 2** — the browser half of D-288/G-18 needs a Playwright driver, absent in this environment. The contrast numbers on the Phase 5 row are a one-time manual measurement, not a reproducible gate.   |
| T7: Artifacts committed                                | `b0bb0d5` + uncommitted follow-up   | pagination override and stray `Continue` removal landed; `.gitignore` line covered `/research-scratch/` but **not `.scratch-uma/`**, so T7's third item had not actually landed. Fixed 2026-09-28: `.gitignore:88` `/.scratch-uma/`, confirmed by `git check-ignore -v`.                                                                                                                                                                                                                        |
| T8: Doc drift closed                                   | (this commit)                       | `DESIGN.md:3`, `layout.blade.php:3-10`, root `DESIGN.md:165-180`, `CONSTRAINTS.md G-21`, `KNOWN-ISSUES.md KI-6`                                                                                                                                                                                                                                                                                                                                                                                 |
| T9: Phase 6 freeze                                     | —                                   | Enforced by plan                                                                                                                                                                                                                                                                                                                                                                                                                                                                                |

---

### Open Blockers for Slice 2 — closed or reclassified 2026-09-28

1. ~~RaceEntry rewire~~ — **did not happen as written, on measured grounds.** The FK
   stays nullable and no backfill ran: `scenario_races` has no `month`/`half`/`kind` for
   `scenario_slots`' NOT NULL columns, holds 0 rows after a clean seed, SQLite 3.49 refuses
   both the in-place NOT NULL and the column drop, and Trackblazer's Trainer-picked races
   have no slot to point at (D-221). The real defect was `scenario_slot_id` missing from
   `#[Fillable]`, silently dropped on every create. Full reasoning in ADR-0003 Amendment R2;
   pinned by `tests/Feature/Schema/RaceEntrySlotLinkTest.php`. `9134206`, `9451659`.
2. ~~Goal pennant~~ — shipped, and it corrected the component to the contract it cites:
   red `Goal` pennant + warm outline + greater height, replacing a green treatment the
   code, its comment and its test name each disagreed with. `bb6eec6`.
3. ~~Grade meter data~~ — partially. `gradeEarned()` now returns a real number (100 for a
   priced G1 win, rendered live as `100 / 0 · 100 over the objective`) and returns `null`
   rather than an understated total when a finish below first is in the log, because the
   corpus prices 1st place only and `race_entries` carries no year bucket. That gap is
   KI-10, not a TODO. `83084ab`.
4. **Schema cap** — untouched by this slice; still open.

**New blockers Slice 2 found and did not fix:** KI-8 (`/design-preview` 500s, which is why
the stat band's rendered pairs are unmeasured), KI-9 (`--color-pick` boundary at 1.59:1 on
the base theme), KI-11 (empty `ScenarioSlotSeeder`, orphaned `--color-green-tint`).

---

### Slice 2 Summary (2026-09-28)

| Step                                                 | Commit                 | Evidence                                                                                                                                                   |
| ---------------------------------------------------- | ---------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- |
| S1: slot link + rewire refused                       | `9134206`              | 5 tests in `RaceEntrySlotLinkTest`; failed first with "Failed asserting that null is identical to 1"                                                       |
| S2: panels fed from slots and the race log           | `83084ab`              | 7 tests in `RaceSlotPanelComposerTest`; live page shows `Apr Early: Fan gate` + `15,000 fans`, `Apr Late: Mandatory goal`, `May Early: Run`                |
| S3: red pennant, warm outline, height, named state   | `bb6eec6`              | `RaceCalendarTest` 17 passed; browser-measured 13.24/5.02/5.74 light, 15.15/6.15/4.49 dark, +14px over neighbours                                          |
| S4: KI-2 closed                                      | `d53a4b1`              | 3 tests drive `CACHE_STORE=database`; live server `/umamusume -> 200`                                                                                      |
| S5: gates + R9 manual contrast pass                  | `70f9218`, `9451659`   | `docs/design-research/verification/slice-2-2026-09-28.md` — full sequence with outputs, the 1.62:1 bar defect found and fixed, 2 skips reported as skips   |
| S6: docs                                             | (this commit)          | ADR-0003 R2, KI-2 resolved with its wrong cause corrected, KI-8 to KI-11 opened, PLAN re-baselined here                                                    |

**End state:** `php artisan test --compact` → 2 skipped, 235 passed, 751 assertions.
`pint --dirty` → passed. `phpstan analyse --no-progress` → `[OK] No errors`. `lore-code` →
4 hits, all pre-existing (Laravel's SQS example URL; the three documented `Good-Luck Charm`
lines). `vite build` → 69.05 kB CSS, all 52 colour tokens present in the sheet, 0 pruned.

**Where the commits are.** `master` is at `33949f5` (equals tip). `docs/audit-remediation` fast-forwarded to match. `feat/scenario-races-is-manual` merged at `dc13d8d`. `origin/docs/audit-remediation` at `9b774f9`, `origin/master` at `33949f5`. The branch reconciliation is complete.

---

### Slice 3 Summary (2026-09-28)

Stabilisation slice: it cleared the blockers Slice 2 filed, then tried to reconcile the branch.

| Task                                                | Commit                                       | Evidence                                                                                                                                                                                                                                              |
| --------------------------------------------------- | -------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| T1: em-dash disclosure sweep (R12)                  | `ab915f8`                                    | `RenderedCopyHygieneTest` sweeps every `.blade.php` under `resources/views`, strips three comment forms, fails on any en or em dash; a floor test catches the `getExtension() === 'blade'` mistake that would have scanned nothing                    |
| T2: badge map keys the base letter (R13/R19)        | `726f106` + `78697e9`                        | `StatBandTest` walks all 17 labels; `/design-preview` 200; 18 badge pairs measured in both themes, min 9.00                                                                                                                                           |
| T3: meter gets three states (R18)                   | `2816309`                                    | `gradeUnpricedCount()` drives "not yet totalled" with the count and the reason; "not yet recorded" only when nothing is logged                                                                                                                        |
| T4: selection boundary split from fill (R15)        | `725a5ff`                                    | `--color-pick-line` 6.24 light / 8.46 dark on `raised`, 5.89 on light `panel`; four consumers moved; `border-pick` guard with `(?![-\w])`                                                                                                             |
| T5: seeder deleted, real skill names seeded (R14)   | `5c65597`                                    | `ScenarioSlotSeeder` removed; ten verbatim D-210 `[Global]` names with `sp_cost => null`; `--color-green-tint` retirement **refused** on G-60 grounds and left open                                                                                   |
| T6: welcome page offline (KI-3)                     | `5548b9e`                                    | both `<link>` lines deleted; live response 39,987 bytes with 0 `bunny` matches and every asset same-origin                                                                                                                                            |
| T7: gate portability (KI-4)                         | `ee97869`, `cc3f963`, `21f9906`, `ee6786c`   | `composer lore` / `composer lore-code` via `tools/lore.php` (Process array, no shell); parity with the Makefile measured at 131/131 and 4/4; `LoreGateParityTest` proven non-vacuous by deleting a pattern                                            |
| T8: branch reconciliation (R11, R20–R21)            | `dc13d8d` (merge) + `35fb0c7` (commit)       | `feat/scenario-races-is-manual` merged (single commit `46b8d3e`); untracked `is_manual` duplicate deleted; five load-bearing files committed; coherence checks pass (20/20 migrations, ScenarioSlot+Preference defs, 23 tables on fresh scratch DB)   |
| T9: gates + docs                                    | `33949f5`                                    | `docs/design-research/verification/slice-3-2026-09-28.md`                                                                                                                                                                                             |

**End state.** `php artisan test --compact` → 2 skipped, 255 passed, 796 assertions.
`pint --dirty` → passed. `phpstan analyse --no-progress` → `[OK] No errors`.
`composer lore` 131 / `composer lore-code` 4, both equal to the Makefile bodies run verbatim.
`gate.py` → GATE PASS. `vite build` → 69.15 kB CSS, 55 tokens declared, 0 pruned.
Migration gate: `migrate:fresh --seed` refused as destructive (5th time); forward `migrate` +
`db:seed` on a fresh scratch DB instead — 24 tables, 10 skills, 0 slots.

**T8 resolved (Slice 4).** The ruling's three-step fix was executed:

1. Five load-bearing untracked files committed on `docs/audit-remediation` (`35fb0c7`).
2. Duplicate untracked `is_manual` migration deleted; `feat/scenario-races-is-manual` (single commit `46b8d3e`) merged at `dc13d8d`.
3. Coherence re-measured: `git ls-tree -r HEAD database/migrations | wc -l` = 20 (disk = 20); `git grep -l "class ScenarioSlot" HEAD` → `app/Models/ScenarioSlot.php`, `database/factories/ScenarioSlotFactory.php`; `git grep -l "class Preference" HEAD` → `app/Models/Preference.php`, `database/factories/PreferenceFactory.php`; fresh scratch-DB `migrate:fresh --seed` → 23 tables including `preferences`, `scenario_slots`, `scenario_races` (with `is_manual`).
4. `master` fast-forwarded to tip (`33949f5`); `origin/docs/audit-remediation` pushed (`9b774f9`); `origin/master` pushed (`33949f5`).

---

### Slice 4 Summary (2026-09-28) — Repo Integrity Only

| Task                                                          | Commit          | Evidence                                                                                                                                       |
| ------------------------------------------------------------- | --------------- | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| T1: commit five load-bearing files                            | `35fb0c7`       | ScenarioSlot, Preference, factories, 2 migrations; authored by concurrent session, KI-13                                                       |
| T2: delete duplicate migration + merge feat branch            | `dc13d8d`       | `2026_09_27_132304_...` deleted; `feat/scenario-races-is-manual` (1 commit `46b8d3e`) merged                                                   |
| T3: coherence checks                                          | (this commit)   | 20/20 migrations tracked; ScenarioSlot/Preference defs present; 23 tables on fresh scratch DB                                                  |
| T4: fast-forward master + push both remotes                   | push shas       | `origin/docs/audit-remediation` → `9b774f9`; `origin/master` → `33949f5`                                                                       |
| T5: docs commit (KI-13 resolved, PLAN/KNOWN-ISSUES updated)   | (this commit)   | KI-13 RESOLVED with shas + T3 outputs; PLAN topology + exit criteria updated; KI-11 gains Safe-band commitment; KNOWN-ISSUES count refreshed   |
| T6: gates                                                     | (this commit)   | pest, pint --dirty, phpstan, composer lore, composer lore-code (parity), gate.py, npm run build                                                |

### Slice 5 Summary (2026-09-28) — Frontend only, per R24

Mount what was already built, frame the run screen, make the flow keyboard-completable. No
model, no migration, no config, no dependency. Livewire stayed out and produced evidence
instead (§Open Decisions).

| Task                             | Commit                 | Evidence                                                                                                                                    |
| -------------------------------- | ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------- |
| T0: Livewire cost + baseline     | `cd0be38`              | `livewire/livewire` absent from `composer.json`; GET baseline median 0.047 s / p95 0.076 s, n=20; preview number appended here              |
| T1: band + rail on `runs/show`   | `d50a0ec`              | 24 tests in `GuidedTurnOnRunViewTest`; two-stage preview writes nothing; failure writes `turn_events`; mood, advisory, escape hatch         |
| T1b: comment leak                | `2685a37`              | `{# … #}` is not a Blade comment; the band had been printing its rationale as page text. Guard added to `RenderedCopyHygieneTest`           |
| T2: two-region frame             | `71bbb4b`              | `RunViewFrameTest` (DOM containment); strip box 992×92 at both ends of the scroll, 9/9 `elementFromPoint` probes inside the pinned region   |
| T3: keyboard path                | `6a53c15`              | `KeyboardPathTest`; skip link, digits, Escape; roving left to the native radio group and verified with pressed keys                         |
| T1c/T1d: browser-found defects   | `edeb8cd`, `03a5d05`   | mood delta sign inverted; Hint badge white-on-green at 1.99:1                                                                               |
| T4: gates + browser pass         | this commit            | `docs/design-research/verification/slice-5-2026-09-28.md`                                                                                   |
| T5: docs + housekeeping          | this commit            | KI-11 both halves closed, KI-14 filed and closed, audit rows 7.2 and 9 updated, root scratch moved                                          |

**End state.** `php artisan test --compact` → 2 skipped, 292 passed (923 assertions).
`pint --dirty` passed. PHPStan `[OK] No errors`. `composer lore` 133 = `make lore` verbatim 133;
`composer lore-code` 4 = 4. `gate.py` GATE PASS. `npm run build` → 56.37 kB CSS, 55 tokens
declared, 0 pruned. Migration gate on a fresh scratch DB: 21 migrations, 2 seeders, 24 tables.

**Exit criteria, checked against the list above rather than asserted.** Gates green with pasted
output: §1 of the record. Claims citing file:line or sha: every row in the table. No new
D-violation in touched files: `composer lore` and `lore-code` at parity, and the one hit this
slice introduced (a banned word used as ordinary English in a comment) was reworded rather
than adjudicated. No stale status
lines: KI-8's "rendered by this route and nothing else", KI-11's open half and audit rows 7.2
and 9 were corrected in the same commit that closes them. Atomic commits per concern: seven.
R17 fixture: every measurement ran on `.scratch-uma/slice5.sqlite`, rebuilt for this slice with
mood, full stat lines and an Energy value chosen per band state; `database/database.sqlite` was
never opened. Checkout coherence: `git cat-file -e HEAD:…` verified in-session for each new file
(`tests/Feature/RunViewFrameTest.php`, `tests/Feature/KeyboardPathTest.php`,
`resources/js/guided-flow.ts`).

**Deviations, stated rather than absorbed.** `RunController.php` does not exist, so the shaping
went into `TrainingRunController`. D-40 draws the two regions left and right at desktop; this
slice stacks them, because a six-column band beside a timeline at `main`'s 64rem is six
unreadable slivers - persistence, the rule the frame serves, is measured, and the axis is an
open item. Escape moves focus rather than discarding typed values. `text-on-pick` is borrowed
for the green Hint badge because it is the only ink in the system that stays dark in both themes, and adding
`--color-on-green` is a design-system call this slice does not own.

---

### Slice 6 Summary (2026-09-28) — Closing slice, R26-R32

Adjudicate what Slice 5 left in the air, land the doc amendments, finish the mood and token-hygiene
residuals, push. Frontend and docs only: no schema, no config, no new screen, no dependency (C-8).

| Task                                      | Commit                 | What landed                                                                                                                                                                                                                                                                                                                                |
| ----------------------------------------- | ---------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| T0: push, then the two `lore-code` hits   | `b81df3f`, `7da2d22`   | `e88bb7b..586e65f` pushed once, plain; `stable` (adjective) and `account` ("account for") ruled allowed and written into §3.2 with citations, plus the unexplained `config/queue.php` `your-account-id`; no violation, so no KI and no peer file touched                                                                                   |
| T1: six doc amendments                    | `61f6165`              | D-40 as region lifetimes; the radio clause reversed; controller inventory; Livewire **decided** with a reopen criterion; `aria-live` closed as correct-by-design; the two audit corrections recorded                                                                                                                                       |
| T2: mood tokens, pill, legibility         | `51d29d4`              | Five `--color-mood-*` + `--color-on-mood`; `x-mood-pill` with the D-259 arrow; `MoodTier::arrow()`; timeline column and state-region readout; pairs measured in both themes, floor 5.82:1                                                                                                                                                  |
| T3: the green ink borrow                  | `e80c6f2`              | `--color-on-green` #1F1508, 9.02:1 measured in both themes; the advisory badge and two stale comments corrected; `TokenPairHygieneTest` pins the naming                                                                                                                                                                                    |
| T4: gates + browser pass                  | this commit            | 2 skipped, 374 passed (1,247 assertions); pint passed; PHPStan level 6 clean; `gate.py` PASS; lore-code 6 all adjudicated in §3.2, `composer lore` 137 → 140 with all three new lines being this plan and the record quoting a banned word to rule on it; build 57.37 kB CSS, all seven new utilities in the bundle; token count 53 → 60   |
| T5: record, second push                   | this commit + push     | `docs/design-research/verification/slice-6-2026-09-28.md`; PLAN and KNOWN-ISSUES re-baselined                                                                                                                                                                                                                                              |

**Deviation the reviewer should look at first.** The mood pill does not use §3.4's tint-and-border
pattern; it keeps the client's saturated fill and steps the ink. The pale-fill reading produced five
near-whites that cannot order a scale and an arrow at 2.5-2.9:1. §3.4, §3.7 and §6.17 are amended to
carry the measured numbers, and the reasoning is in `slice-6-2026-09-28.md` §6.

**Still unmeasured:** whether a Trainer distinguishes `↑` from `→` at the shipped 12 px. The ratio
floor is 5.82:1 and the geometry is recorded; the glyph legibility needs a human read, and no
capture was taken.

---

### Slice 7 Summary (2026-09-28) — Schema session, R33-R37

Three schema questions arrived as one set so the owner ruled once. No Unity Cup or Trackblazer
panel UI (Slice 8), no new dependency, peer files untouched.

| Task                      | Commit                     | Claim, with the thing that proves it                                                                                                                                                                                                          |
| ------------------------- | -------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| T0a §3.4 pair table       | `53fbe70`                  | All six new pairs (five mood + `on-green`) were already rows in `docs/design-research/DESIGN.md:156-161` with their measured ratios; the count in the note under them said seven, and said six                                                |
| T0b Livewire criterion    | `8fd127c`                  | R34's sentence is now quoted verbatim above the seven-item checklist, with this slice's byte-count framing named as not a trigger                                                                                                             |
| T0c zinc sweep            | no commit; grep recorded   | `grep -n "zinc-" resources/views/runs/index.blade.php` exits 1, no match. Repo-wide, `zinc-` survives only in three Blade comments that document the retired skeleton and in `welcome.blade.php:15`'s vendored Tailwind theme variable list   |
| T1 period columns         | `e103122`                  | Migration `2026_09_28_122929_add_grade_point_period_columns.php`; `TrainingRun::assertGradePeriod()` guards both columns; `tests/Feature/GradePointPeriodTest.php` ran red on the missing columns first                                       |
| T1c meter, period-aware   | `e103122`                  | `gradeEarnedFor()`, `gradeUnpricedFor()`, `gradePeriods()`; `gradeEarned()` is the reported period or null; the no-period state renders "no period reported" and the ladder rows carry their own sums                                         |
| T2 typed payloads         | `17dbc54`                  | `SpiritBurstState` (six cases, no seventh), `NpcFriendshipPayload`, `SpiritBurstPayload`; `tests/Feature/TurnEventTypePayloadsTest.php` asserts the raw stored JSON and the three rejections                                                  |
| T2 reword                 | `8955394`                  | `composer lore-code` back to 6 after one comment idiom was reworded                                                                                                                                                                           |
| T3 docs                   | this commit                | ADR-0005 status DECLINED for Phase 1 with the reopen trigger named; KI-10 split and its schema half closed; KI-15 filed for the track-selection conflict; PLAN re-baselined                                                                   |

**Suite at the tip of this slice's code:** `php artisan test --compact` → 2 skipped, 390 passed
(1,287 assertions); PHPStan level 6 `[OK] No errors`; Pint clean on the files this slice touched.

---

### Slice 8 Summary (2026-09-28) — Panels and scenario widgets

Built the four scenario-aware panels (race, team-race, spirit-burst, shop) plus supporting
components (epithet-checklist, race-fatigue-chip). Filed KI-17 (consecutive-race count not
derivable from log) and KI-18 (burst roster prints tool identifiers as UI copy). No token changes,
no migrations.

---

### Slice 9 Summary (2026-09-28) — Spirit burst labels, mood pill tokens

Closed KI-18 (`f7a59e8`): `SpiritBurstState::label()` mapping six cases to Trainer-readable words;
roster prints labels, tests assert labels and fail on leaked backing values. Mood pill tokens
landed with legibility minimum measured in browser. Subject-prefix erratum: `68fa190` carries
`docs(slice-7)` but is a Slice 9 commit (R50).

---

### Slice 10 Summary (2026-09-29) — Maintenance and drift

Closed KI-18 bookkeeping (R52). Lore-ignore-line marker landed (R51). Impeccable re-audit ran
on same version (KI-19: updater returns 404). Purchase cost prefill + 422 field error per-field.
ADR-0009 drafted for scenario-slot seeding. Filed KI-19 and KI-20.

---

### Slice 11 Summary (2026-09-29) — Calendar slots, free-race writer, KI-20 closure

Rulings: R54 (source_key migration), R55 (URA Finale seeder), R56 (free-race manual writer),
R57 (delete welcome page), R58 (measure and close KI-20), R59 (push once + record commit),
R60 (rulings are repo artifacts), R61 (fifth kind: free_race), R62 (lore-code path exclusion).

| Task                      | Commit      | Claim, with the thing that proves it                                                                                                                                                                                                                                     |
| ------------------------- | ----------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| T1 source_key migration   | `c0a743f`   | `scenario_slots.source_key` nullable string; unique composite `(scenario_key, month, half, source_key)`; migration tested in `ScenarioSlotMigrationTest`                                                                                                                 |
| T2 URA Finale seeder      | `f0ae288`   | `ScenarioSlotSeeder` reads the three committed client-export files `database/seeders/data/{ura-races,race_instances,races}.json`; **296 rows** seeded on a clean scratch DB; idempotent via upsert on `(scenario_key, month, half, kind, source_key)`                    |
| T3 multi-slot calendar    | `70248b3`   | `TrainingRun::calendarCells()` queries both `goal_race` and `free_race`; cells carry `['slots' => [...]]` arrays; priority state derivation; `race-calendar.blade.php` renders multiple slots per cell                                                                   |
| T4 free-race writer       | `5820e77`   | Two-path form (calendar/manual) in `race-panel.blade.php`; `StoreRaceEntryRequest` validates both paths; controller creates `ScenarioSlot(kind=free_race)` + `RaceEntry` atomically; `FreeRaceWriterTest` 8 tests; lore-code baseline stays 7 after R62 path exclusion   |
| T5 closures               | `65f8b92`   | Welcome page deleted, `/` redirects to `runs.index` (R57); KI-20 measured (light 10.04:1 PASS, dark 4.33:1 FAIL → stepped #FF6B7A to #FF7E8C = 4.77:1 PASS); KNOWN-ISSUES updated to 15 closed / 4 open                                                                  |

**Gates:** Pest 507 passed / 2 skipped / 1 failure recorded here as "pre-existing"; Pint PASS; PHPStan PASS; lore-code baseline 7; Vite build clean.

**Erratum (Slice 12, R63).** Two claims in this row were wrong and are corrected rather than
silently rewritten:

- **The failure was not pre-existing.** It is T4's own regression. `0d2dbdc` and T3's `70248b3` both
  PASS `RunViewFrameTest`; T4's `5820e77` FAILS it. T4 added a second `role="radiogroup"` to the
  scrolling region, breaking D-40's one-guided-rail invariant. The Slice 11 check used `git stash`,
  which reverts tracked *modifications* — the radiogroup was already committed at `5820e77`, so the
  stash removed nothing relevant and the failure reproduced on a tree still containing the suspect.
  Full bisect and the fix in `slice-12-2026-09-29.md` §2 and `c86ed9f`.
- **"297 rows" and `ura_finale_slots.json`** were both wrong: the file does not exist, the seeder
  reads three real export files, and a clean scratch DB seeds 296.
- **R59's record half was not met.** The post-push `ls-remote` output was reported in chat and never
  committed; `slice-11-2026-09-29.md` carries no push-verification section. Recorded in Slice 12.

**Browser pass:** `/` → 302 redirect confirmed; `/training-runs/1` with free_race data → 200, "Naruta Kinpa Cup" rendered, "Trainer-entered" marker present, zero server errors.

**Push cadence (R59):** One push at slice end (`4992282` → `origin/master`, verified in-session by
`git ls-remote`). **R59's second half was not met:** no post-push record commit was made, so the
verification lived in the chat transcript and in no artifact. Corrected in Slice 12, whose push is
followed by a record commit carrying the `ls-remote` output. Evidence: `slice-12-2026-09-29.md` §4.

---

### Slice 12 Summary (2026-09-29) — CORRECTED TO: halted at T3

Slice 12 did not complete. **It halted at T3** with two blockers filed and T4 (gates plus push)
never run. `origin/master` stayed at `4992282` for the whole slice.

| Task                       | Commit                                             | Claim                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                |
| -------------------------- | -------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| T0 radiogroup regression   | `c86ed9f`                                          | Slice 11's "pre-existing" label was wrong: bisect shows `0d2dbdc` PASS, `70248b3` PASS, `5820e77` FAIL. T4's `role="radiogroup"` on the mode switch broke D-40. Fixed with banner buttons                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| T1 tier audit              | `2d0c1dc`, superseded in part by `329cec1` (R72)   | R65 concluded that only codes 100 and 400 have a label source, resting on REFERENCE §1.2.6's "no cited label map" for 200/300/700, and nulled those three. **That sentence described the corpus as then written, not the absence of a source.** Tested per race on 2026-09-29 in a rendering browser against two publishers: G1 agrees 34/34, G2 42/42, G3 76/76, so all three tiers are now sourced **per race** and the seeder holds no code-to-label constant at all. Open and Pre-OP are not settled: Game8's list is graded-only, so its silence is not agreement, and uma.guide alone calls Open 「OP/L (Open/Listed)」. Three Open rows are pinned per row by their own client glyph; 115 keep a disclosed code-level pin rather than being nulled, and that trade is flagged for the owner, not taken quietly. Pre-OP stays null. Re-seed twice: 296 rows, G1 34 / G2 42 / G3 76 / OP 118 / null 26 — 118 restored, none lost. Full evidence and page dates: `database/seeders/data/race-tier-labels-2026-09-29.json`, D-153 |
| T2 register + record       | `b295d16`                                          | Slice-11 addendum, KI-19 closed on the second update attempt, KI-11 re-closed, ADR-0009 to RULED IN PART, R54-R66 ledger added                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
| T3 browser pass            | `37ccc08`                                          | Found **KI-21** and **KI-22**, and halted. Suite green, pairs measured, form unusable                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                |
| T4 gates + push            | **NOT RUN**                                        | A green gate over a form no Trainer can reach is not a gate. Blocked pending the owner's dependency call                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |

**The two blockers, and one correction to the second.** KI-21 was real: the race form was built on
Alpine, which is not a dependency, so both branches sat in inert `template` elements. Slice 13
closed it at `6c1969f`. KI-22 was **filed on a wrong cause** — Slice 12 grepped for `isFreeRace`,
did not find the `$manual` parameter that had replaced it, and read a missing identifier as a
missing branch; `git blame` attributes the branch to the very commit blamed for deleting it. The real
defect was the marker on a finished free race. Both are closed in Slice 13; `slice-12-2026-09-29.md`
carries the addendum, its §7.3 text left as written.

**A lesson the register now records twice.** Slice 13 repeated Slice 12's error inside its own first
pass at the same file — asserting the marker was still lost, from reading `calendarCell()` — and the
browser pass is what disagreed. Reading code answers what a file says; only running it answers what
the screen does. R71 exists because of this.

---

### Slice 13 Summary (2026-09-29) — Close KI-21, fix the ordinal, reconcile KI-22, push once

Brief: "No new dependency, no new token, no schema change, no panel beyond the race panel." All four
held: `package.json` untouched, no token declared or recoloured, no migration, one component changed.
Rulings R67-R71.

| Task                          | Commit                             | Claim, with the thing that proves it                                                                                                                                                                                                                                                                                                                             |
| ----------------------------- | ---------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| T0 opening snapshot           | (record §1)                        | R68's three named files checked individually, all CLEAN, so T3 ran. `HEAD` was `7895e74` while `origin/master` sat at `4992282`                                                                                                                                                                                                                                  |
| T1 server-driven disclosure   | `6c1969f`                          | Two GET forms submit `entry_mode`; `showData()` reads the query the way design-preview reads `?step=`; `old()` wins so a failed write returns to its own branch with placement/status/circles/period preserved. `RaceEntryDisclosureTest` 8 tests, **all red against HEAD**, resolving fields through `DOMDocument` and refusing any inside a `template` (R71)   |
| T2 ordinal placements         | `6c1969f`                          | `RaceEntry::placementOrdinal()` at `:437`-area; twelve values tabled including the 11/12/13 teens plus null → "no placement". `team-race-panel` prints a cardinal with the word that makes it one and is left alone                                                                                                                                              |
| T3 KI-22 reconciliation       | `cb9b61f`, `5ed1ebd`               | No production change: the rule was intact and Slice 12's cause was wrong. Added the rendered pins the existing test could not provide — `RaceCalendarTest:276` hand-writes its cell array and passes on label text without calling `calendarCell()`                                                                                                              |
| T4 browser pass               | (record §5)                        | R17 fixture on `.scratch-uma/s13-browser.sqlite`, port 8233, shared DB never opened. Free race created **through the rendered form** by clicking the disclosure and pressing Record race. All 13 pairs clear; zero `template` elements; radiogroups still 1                                                                                                      |
| T5 gates + deferred push      | (this commit / post-push record)   | Pest 562 passed, Pint passed, PHPStan no errors, lore-docs 98/51, lore-code 7, gate.py PASS, build clean. One plain push carrying 16 commits, 9 named as not this slice's                                                                                                                                                                                        |

**Zero-Alpine grep:** `grep -n "x-data\|@click\|x-if\|template x-if\|x-model"` over the component →
no output, exit 1; the same over all of `resources/views` → no output. A first attempt hit once, on my
own comment quoting `template x-if`; a grep a comment can satisfy is not a check, so the comment moved.

**Reported, not explained away:** the unselected disclosure's quiet border measures **1.22 light /
1.37 dark** against its background, under 1.4.11's 3:1. The state is not carried by that border
(text 12.49 vs 5.46, selected-vs-unselected border 4.83, `aria-pressed` both), so the exception for
state conveyed otherwise plausibly applies — but whether this is the treatment the design system
wants is the token owner's call, and this slice was told not to touch tokens. No KI filed, because
nothing here is a demonstrated failure.

**Gates, honestly:** the first Pest run reported 29 failures with `database is locked`. That was this
slice's own `artisan serve` still running, not the tree. Stopped and re-ran: 562 passed. Recorded
because a gate number produced beside a lingering dev server should not be quoted without its
conditions.

---

### Owner Rulings Ledger (R54-R77)

R60 makes a ruling a repo artifact rather than a transcript line, so the ledger records each ruling
as the brief gave it. Where the brief supplied a full sentence it is quoted; where it supplied a
parenthetical gloss the gloss is kept and marked as such, because inventing verbatim wording for a
ruling is worse than recording it short.

| ID    | Slice   | Ruling, as given                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        | Provenance                              |
| ----- | ------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------- |
| R54   | 11      | Add `source_key` to `scenario_slots` so a half-month can hold more than one race; the unique composite includes it                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      | gloss                                   |
| R55   | 11      | Seed URA Finale `scenario_slots` from the committed client export                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       | gloss                                   |
| R56   | 11      | A Trainer can enter a race that is not on the calendar; the manual path creates one `scenario_slots` row per race, and that row is Trainer-entered                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      | verbatim in brief                       |
| R57   | 11      | Delete the welcome page                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 | gloss                                   |
| R58   | 11      | Measure KI-20; a pass closes it with a §3.4 row, a fail steps the ink per the on-mood/on-green precedent                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                | verbatim in brief                       |
| R59   | 11      | Push once per slice, with the post-push `ls-remote` verification in the record commit                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   | gloss                                   |
| R60   | 11      | Rulings are repo artifacts, not transcript lines                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        | gloss                                   |
| R61   | 11      | `free_race` is the fifth `kind`; its cells take open-cell geometry and a Trainer-entered marker, never a Goal pennant                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   | verbatim in brief                       |
| R62   | 11      | Exclude `database/seeders/data/**` from the lore-code sweep: committed client export is source data under C-4 class 3                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   | gloss                                   |
| R63   | 12      | `RunViewFrameTest` is a T4 regression, not pre-existing; read the test's intent before choosing the fix, and correct the slice-11 record forward rather than editing it in place                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        | verbatim in brief                       |
| R64   | 12      | The slice-11 push verification is written from the outside, and the record says whether those lines were written before or after the push                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               | verbatim in brief                       |
| R65   | 12      | Any seeded row whose tier label has no per-row source gets `tier = null` and keeps `source_key`; the re-seed proves idempotency on a scratch DB                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         | verbatim in brief                       |
| R66   | 12      | Retry the Impeccable update once; on a second 404 record both attempt dates in KI-19 and run the detect pass at the current version over the Slice 11 surfaces only                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     | verbatim in brief                       |
| R67   | 13      | The two-path race form becomes server-driven disclosure: the mode switch submits `entry_mode` and the server re-renders with that branch's fields, exactly as guided-step does. No new dependency                                                                                                                                                                                                                                                                                                                                                                                                                                                                       | verbatim in brief                       |
| R68   | 13      | Reconcile KI-22 only if T0 finds `race-calendar.blade.php`, `runs/show.blade.php` and `TrainingRun.php` clean; if any is dirty, skip T3 entirely and say so in the record with the dirty list                                                                                                                                                                                                                                                                                                                                                                                                                                                                           | verbatim in brief                       |
| R69   | 13      | Placement renders 1st, 2nd, 3rd, 4th, 11th, 12th, 13th, 21st, 22nd, 23rd correctly through a small helper, with a test table for those values                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           | verbatim in brief                       |
| R70   | 13      | The deferred push goes as one plain push carrying the interleaved peer commits, and the record names every sha that is not this slice's                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 | verbatim in brief                       |
| R71   | 13      | The disclosure test asserts the branch's fields by input name through the rendered DOM, must fail against HEAD, and the writer is additionally reached through the rendered form rather than a direct post                                                                                                                                                                                                                                                                                                                                                                                                                                                              | verbatim in brief                       |
| R72   | 14      | Settle the tier question on evidence read in a rendering browser with location asserted before each read; commit a dated extraction and join per race; restore a label only where both publishers agree and a page date postdates 2026-07-01; reconcile the parser's map only if its file is clean and the peer session is confirmed idle                                                                                                                                                                                                                                                                                                                               | verbatim in brief                       |
| R73   | 14      | Before a push, read every commit in the range it will carry that touches `database/`, `config/`, `app/Services/DataPipeline/` or the research corpus, starting with anything the peer lands during the slice                                                                                                                                                                                                                                                                                                                                                                                                                                                            | verbatim in brief                       |
| R74   | 14      | Record the quiet-edge exception beside the §3.4 pair table with its measurements, its two precedents and its carrying cues; docs only, no token and no component change                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 | verbatim in brief                       |
| R75   | 15      | Nullify the 115 OP rows and the 26 Pre-OP rows in the seeder; the 3 client-pinned OP rows keep their label; remove the `per_row_sourced` disclosure flag, because a null tier is the honest state                                                                                                                                                                                                                                                                                                                                                                                                                                                                       | verbatim in brief                       |
| R76   | 15      | Add a test that deletes the extraction file and asserts the seeder completes with null tiers and a logged warning, rather than throwing                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 | verbatim in brief                       |
| R77   | 15      | **Reserved and still unworded.** "Rulings R75 to R77 govern" is the only appearance of the number in that brief; it carries no task and no rule. Slice 16 then asked twice more for the row to be filled — once through R80, once through a directive headed "R77 (text supplied)" — and **neither message contained a sentence for it**. The row therefore records the absence rather than receiving an invented ruling: a line under `R77` would later be indistinguishable from a decision the owner actually made, which is the outcome R60 exists to prevent. See `slice-15-2026-09-29.md` §10.4 for the three candidate readings that were checked and rejected   | verified absent from all three briefs   |

---

### Slice 14 Summary (2026-09-29) — Tier labels settled per race, quiet edge recorded

Brief: verification and recording. No new dependency, no token change, no parser edit unless R72's
coordination condition holds. Rulings R72-R74.

| Task                | Commit                 | Claim, with the thing that proves it                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
| ------------------- | ---------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| T0 opening push     | `debc4d0` on origin    | One plain push carrying the four Slice 13 docs-only commits; `ls-remote` = `debc4d0e6bda…` equals local HEAD. R73 read list: all four this session's                                                                                                                                                                                                                                                                                                                                                                                                                       |
| T1 tier join        | `329cec1`, `24e491c`   | Both publishers read in a rendering browser with location asserted. **G1 34/34, G2 42/42, G3 76/76 agree per race; Open and Pre-OP do not** (Game8 is graded-only, uma.guide alone says "OP/L (Open/Listed)"). `GRADE_MAP` deleted; seeder joins on `database/seeders/data/race-tier-labels-2026-09-29.json` and stores the evidence date, not the seed moment. G-16c: no matches in seeder or config, exit 1, pinned by `TierLabelJoinTest`. Re-seed twice: 296 rows, G1 34 / G2 42 / G3 76 / OP 118 / null 26 — 118 restored, none lost, no row without a `source_url`   |
| T2 quiet edge       | `3ae437d`              | DESIGN.md §3.4 records R74 as three conditions, both precedents, and the numbers (1.22 / 1.37 unselected against 5.89 / 10.57 selected and a 4.83 pair difference)                                                                                                                                                                                                                                                                                                                                                                                                         |
| T3 rendered tiers   | (record §4)            | The calendar renders **no** tier text (grep 0, 0 tier nodes in `role="img"` cells, both themes), so no calendar pair was invented. Race panel: select 6.95 / 12.71, `G2` and `G3` 5.78 / 6.64, ordinal 6.64 dark, calendar marker 8.30 dark. Picker distribution read off the DOM matches the re-seed exactly                                                                                                                                                                                                                                                              |
| T4 gates + push     | (this commit)          | Pest 569 passed / 2 skipped / 0 failed; Pint, PHPStan, gate.py, build clean; lore-docs 98/51; lore-code 7. Server stopped before the suite, so Slice 13's lock failure was not repeated                                                                                                                                                                                                                                                                                                                                                                                    |

**Two things this slice deliberately did not do.** The parser's five-entry map stays a documented
dissent: R72 requires the peer session confirmed idle, and its branch advanced twice during the slice
even though the file itself was clean. And the 115 Open rows that rest on a generalisation from three
client-glyph rows keep their label with that status written into the same row (`scope:
code-level-client-naming-pin`, `per_row_sourced: false`) rather than being nulled by a strict reading —
dropping a tier correct since Slice 11 to satisfy a grep is the owner's trade, recorded in D-153 and in
`slice-14-2026-09-29.md` §2.5.

> **Reversed by Slice 15 T1 (R75), 2026-09-29.** The owner took the trade the other way: the 115 are
> null and the disclosure flag is removed rather than set to `false`, because a null tier is itself the
> honest state. The paragraph above is left as what Slice 14 decided when it decided it; the count in
> its T1 row (OP 118 / null 26) is superseded by Slice 15's (OP 3 / null 141).

**Register unchanged:** KI-10, KI-15 and KI-17 stay open. T1's evidence is about tier labels; KI-10 is
the Grade Point placement ratio, KI-15 which GP track applies, KI-17 the consecutive-race count. None
is touched. 21 filed / 18 closed / 3 open, unchanged from Slice 13.

> **Superseded one slice later.** Slice 15 closed KI-17 and closed KI-10's schema half. The paragraph
> above is what Slice 14 recorded against its own tree; `slice-15-2026-09-29.md` §10.1 carries the
> disposition and the reason the register file itself lagged.

---

### Slice 15 Summary (2026-09-29) — the Schema Session: three long-waiting questions landed

Brief: close KI-10, KI-17 and Legacy Select, and apply R75's strict nullification. No new panels, no new
tokens, no new dependencies. Rulings R75-R77 — of which **R77 has no text in the brief** (ledger note).

| Task                          | Commit                 | Claim, with the thing that proves it                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| ----------------------------- | ---------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| T0 opening push               | `72e5157` on origin    | One plain push carrying the one-ahead docs commit; `ls-remote` = `72e5157f7ab4…` equals local HEAD. R73 read list: one commit, docs-only, this session's                                                                                                                                                                                                                                                                                                                                                                                  |
| T1 R75 nullification          | `40df14c`              | Test rewritten **first**, `3 failed / 5 passed`. Re-seeded twice on a scratch DB: **G1 34 / G2 42 / G3 76 / OP 3 / null 141**, 296 rows both passes, idempotent. G-16c `exit 1`, no matches in `database/seeders/` or `config/`. The 3 surviving OP rows read back out of the database: Fukushima TV Open, Sapporo Nikkei Open, Kokura Nikkei Open. `per_row_sourced` removed from every row, not set to `false`                                                                                                                          |
| T2 KI-17                      | `d06199c`              | `race_entries.turn_entry_id`, nullable FK, `nullOnDelete`; dropdown of the run's own turns in the race panel; 9 tests RED-then-green, covering the link, the null state, and a turn from **another run rejected with no row written**. `ESSENTIALS` gains the `race_entries` line it never had                                                                                                                                                                                                                                            |
| T3 KI-10                      | `3711894`              | `grade_points_earned`, priced **for a 1st place only** from `grade_point_by_grade` (G1 100 / G2 80 / G3 60 / OP 40 / Pre-OP 20); every other placement and every untiered race stores null, so **2nd in a G1 is null beside 4th**, which is what keeps KI-10's ratio half visibly open rather than quietly borrowed from the Shop Coins table. Tier read `race_catalog_slots` then `scenario_slots`, pinned by two slots that disagree. `GradePointPeriodTest`'s 9 tests pass untouched — the semantics-preservation evidence. 22 tests   |
| T4 Legacy Select              | `26aa9fe`, `49bac80`   | One `legacy_selection` json payload plus **ADR-0010**, because PRD §6.3's "nothing more" had to be narrowed for this to exist at all. The brief's `legacy_parent_a_id` / `_b_id` were **not** added — the table already has the two foreign keys, and a test asserts the duplicate pair is absent. 13 tests, including a run created with two legacy parents and a sparks payload                                                                                                                                                         |
| T5 R76                        | `952f41a`              | A missing extraction now logs exactly one warning naming the file, and the seeder completes with every tier null. `1 failed / 3 passed` at RED (warning called 0 times). A third test asserts **no** warning when the file is present, without which the first two could be satisfied by warning unconditionally                                                                                                                                                                                                                          |
| T6 gates + browser + record   | (this commit)          | Pest **618 passed / 2 skipped / 0 failed**; Pint, PHPStan clean; `lore-docs 98 hits / 55 exempt`, `lore-code 7`; `gate.py` PASS; build clean. Browser pass on a Trackblazer run built through the real forms: row reads `… G1 1st turn 1 period 2`, meter reads `100 of 60 toward End of Junior Year`, zero console errors, every new pair ≥ 5.78 light and ≥ 6.64 dark                                                                                                                                                                   |

**Three things this slice did not do, each named rather than left as a silence.** It did not derive the
consecutive-race count: KI-17 closed on the link the issue asked for, and `consecutiveRaceCount()` still
returns null, because an unnamed turn is not the same statement as a turn that did not race. It did not
price a placement below first. And it built no Legacy Select UI, so the payload is storage for a screen
that does not exist yet — which ADR-0010 records as a debt with a name, not a win.

**One measurement corrected a belief mid-slice.** The mobile pass found `scrollWidth 476 > 390` and the
first reading was that this slice broke mobile. Naming the offending element found the turn log table, nine
columns wide — a pre-existing reflow this slice never touched (`git diff` shows one view changed, 22
insertions, and it is not that one). Filed as KI-25; see the register note below for why landing that
commit meant carrying another session's lines.

**Register: KI-10 schema half closed, KI-17 closed, Legacy Select landed.** `757be1d` re-baselines
`KNOWN-ISSUES.md` at **24 filed / 19 closed / 5 open**, checked against the register's own headings rather
than against this slice's memory of it — and that check is what surfaced a **KI-16 hole** (KI-15 to KI-17
with nothing between), the same shape as the `ADR-0008` hole the record reports.

The write **carries the concurrent session's uncommitted KI-23 and KI-24**, on the owner's direction that
the register be landed rather than deferred. In one shared working tree the top status block can hold only
one of two counts, so the alternative was a file reading two different ways at once; the cost accepted is
that their prose reaches `origin` inside a commit that is not theirs, which is why their authorship is named
in the message and their entries are quoted by number rather than rewritten here. If the skills session
commits that file again, it is writing over published text and the reconciliation is its own.

Two stale pointers were corrected on the way, both worth the line they cost: `consecutiveRaceCount()` has
moved from the cited `TrainingRun.php:639-650` to `:811-826`, and the first draft of this paragraph claimed
`21 filed / 2 open`, which was arithmetic on a register this slice had not finished reading.

---

### Controller Inventory

Recorded because Slice 5's path scope named `app/Http/Controllers/RunController.php`, a file that
does not exist and never did, and a scope line quoting an absent path is a scope line nobody can
honour. This is the whole set on `7da2d22`, from `git ls-files app/Http/Controllers`:

| File                                 | Surface                                                                  |
| ------------------------------------ | ------------------------------------------------------------------------ |
| `Controller.php`                     | abstract base, no routes                                                 |
| `TrainingRunController.php`          | the run surface: index, show, store, the two-stage guided turn, export   |
| `CatalogController.php`              | umamusume catalog index and detail                                       |
| `ReviewController.php`               | the match-candidate review queue                                         |
| `Api/V1/TrainingRunController.php`   | JSON runs                                                                |
| `Api/V1/UmamusumeController.php`     | JSON catalog                                                             |

The run surface is `TrainingRunController` (web and API v1). A future slice that means "shape the
run page" should name that file. Renaming it to `RunController` would touch `routes/web.php`,
which is out of every frontend slice's scope.

### Corrections Owed to FRONTEND-BRIEF-AUDIT, Not Applied

`docs/design-research/FRONTEND-BRIEF-AUDIT.md` is an untracked file authored by the concurrent
session, so it is not this slice's to edit. Two of its §3 rows now describe a tree they did not
see, and the owner or that session should land these before the audit is cited again:

| Row                         | It says                                                             | It should say                                                                                                                                                                                                                                                         |
| --------------------------- | ------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| §3 "5.1 two-region frame"   | "**NOT BUILT** — `layout.blade.php:18` is one `max-w-5xl` column"   | Built in Slice 5 at `71bbb4b`: a pinned state region over a scrolling work region on `runs/show`, persistence measured in `slice-5-2026-09-28.md` §3. `layout.blade.php` is still one column, which is correct: the frame belongs to the run screen, not the shell.   |
| §3 "9 accessibility"        | "Still absent: `aria-live`."                                        | Absent by design, not outstanding (R31): the preview arrives through a navigation, so there is no in-place change to announce. See KI-14's closure in `KNOWN-ISSUES.md`.                                                                                              |

---

### Evidence Traceability

| Gate               | Command                                       | Slice 1 Output                       |
| ------------------ | --------------------------------------------- | ------------------------------------ |
| migrate            | `php artisan migrate:fresh --seed`            | 22 migrations, 2 seeders ✅          |
| pest               | `vendor/bin/pest --compact`                   | 217 passed, 2 skipped ✅             |
| pint               | `vendor/bin/pint --test`                      | passed ✅                            |
| phpstan            | `vendor/bin/phpstan analyse --no-progress`    | Level 6, 0 errors ✅                 |
| lore               | `make lore` (manual)                          | PASS ✅                              |
| lore-code          | `make lore-code` (manual)                     | PASS (1 documented hit) ✅           |
| vite               | `npx vite build`                              | 70.18 kB CSS, 51.52 kB JS ✅         |
| DesignTokensTest   | `vendor/bin/pest --filter=DesignTokensTest`   | 9 passed, 2 skipped (Playwright) ✅  |

---

### Open Decisions (unowned by any slice)

Three calls stay with the owner and are untouched by Slice 5: theme default (light vs dark),
rounded-font vs system stack (C-8 plus `DESIGN.md` §11), and mid-run "Change scenario"
semantics. The fourth is no longer open.

#### Should Livewire enter the stack? — DECIDED no, 2026-09-28 (R29)

**Decided: no, and the burden of proof sits with the round trip, which was fast.** T1 builds
preview-before-commit server-rendered, and the evidence that decision needs is
the latency of the request Livewire would remove. Measured on the run-detail page today, loopback
`php artisan serve` on `127.0.0.1:8144` against the R17 fixture (`.scratch-uma/slice5.sqlite`,
rebuilt isolated), `curl -w '%{time_starttransfer}'` after one warm-up request that compiles the
Blade views, n=20, no writes: **median 0.047 s, p95 0.076 s**, min 0.039, max 0.111, and 39,156
bytes of HTML per round trip. `php artisan serve` is the single-threaded dev server with
`APP_DEBUG=true`, so a production host would be faster, not slower: these numbers are comparable
between themselves, not portable to another machine. So what Livewire would buy is a preview
without a round trip (the 47 ms above plus 39 kB of re-sent shell becomes a DOM patch), and a
guided flow that holds its step state server-side instead of re-POSTing the form. What it costs is
specific and already on the record: `livewire/livewire` is absent from `composer.json` and adding it
is a C-8 dependency decision; Livewire 3 ships and boots Alpine.js, which reverses the stack line
at `.ai/guidelines/custom/domain.md:9` and `ARCHITECTURE.md:225` ("vanilla JS only where needed"),
and `docs/PRE-MORTEM.md:73` already cut a Livewire component tree on the finding that it "would add
a dependency for zero new capability"; every `wire:` region owes its own loading and error state
under C-7, which multiplies the state surfaces on one screen; `wire` transitions have to satisfy
D-90 (nothing animates unless the Trainer caused it), D-91 (≤240 ms) and D-92 (reduce → 1 ms), so
motion budgets move from a CSS review to a component review; and D-66 says an interaction "never
fires a network request", which is a rule about the *search box* but needs an explicit loopback
ruling before a per-keystroke `wire:model.live` could be called compliant with NFR-1's local-only
promise. The cost this slice cannot pay in advance is re-verification: a morphing DOM invalidates
every `getComputedStyle()` pair recorded in the verification files, and the browser half of
`DesignTokensTest` - the only gate that would catch that invalidation - is the half that **skips**
here (2 skipped, Playwright absent, C-8 forbids installing it), so contrast would be re-measured by
hand on every commit that touches the flow. **The number that decides this is not the baseline
above but the preview round trip measured against it, appended below once T1c exists.** *(To verify
rather than assume at ADR time: the Alpine bundling claim is from Livewire's published design, not
measured here, because nothing Livewire is installed in this repo.)*

**Appended after T1c landed:** the same server, same fixture, same method (one warm-up request,
n=20, loopback dev server, `APP_DEBUG=true`): the two-stage preview round trip - option submit to
preview render - is **median 0.071 s, p95 0.091 s** (min 0.054, max 0.152), against the baseline
page render above at 0.047 / 0.076. A selection costs about 24 ms more than loading the page, and
ships 72,562 bytes because the whole shell is re-sent. So the case Livewire would have to make is
not "47 ms is too slow" - it is the 72.5 kB re-parse per click and the stateful step chain, set
against a C-8 dependency, a reversed vanilla-JS stack line, `PRE-MORTEM.md:73`'s existing cut, one
loading and error state per wired region (C-7), the motion budget moving from CSS review into
component review (D-90 to D-92), a D-66 loopback ruling, and re-verifying every recorded
`getComputedStyle()` pair over a morphing DOM while the gate that would catch it is the one that
skips. Full numbers and method: `docs/design-research/verification/slice-5-2026-09-28.md` §4.

**Reopen criterion, the owner's words (R34, 2026-09-28), quoted verbatim:**

> "a UI need a full-navigation round trip cannot serve, such as in-place multi-step editing; never
> latency alone"

That sentence is the rule. The list below is the shape of a request that satisfies it: the trigger is
a *need* the current interaction model cannot meet, and the seven items are what such a request then
owes. This block's first draft, landed by the slice that closed the item, framed the trigger as "the
72.5 kB re-parse per click and the stateful step chain" — the byte count is a cost of the present
design, not a UI need, and on the owner's criterion it does not open the question. Latency alone
never did: the measured round trip is 0.047 s median / 0.076 s p95 for a page render and 0.071 s /
0.091 s for the two-stage preview (n=20 each, loopback dev server, `APP_DEBUG=true`), which is the
evidence `slice-5-2026-09-28.md` §4 carries.

**What a request that meets the criterion must arrive with,** in this order, all seven:

1. C-8 approval from the owner for `livewire/livewire`, which is absent from `composer.json` today;
   no slice owns adding a package.
2. A written amendment to the stack line at `.ai/guidelines/custom/domain.md:9` and
   `ARCHITECTURE.md:225` ("vanilla JS only where needed"), because Livewire 3 ships and boots
   Alpine.js.
3. `docs/PRE-MORTEM.md:73` answered on its own terms: it cut a Livewire component tree for
   "a dependency for zero new capability", so the capability has to be named.
4. A loading state and an error state for every wired region, per C-7, each one a new surface to
   review on one screen.
5. The motion budget re-cleared per component rather than per stylesheet: D-90 (nothing animates
   unless the Trainer caused it), D-91 (≤240 ms), D-92 (reduce → 1 ms).
6. A D-66 ruling on a per-keystroke `wire:model.live` against NFR-1's local-only promise; D-66 says
   an interaction "never fires a network request", and that needs an explicit loopback reading
   before a wired input is compliant.
7. A re-measured `getComputedStyle()` pair table. A morphing DOM invalidates every pair recorded in
   `docs/design-research/verification/`, and the only gate that would catch the invalidation, the
   browser half of `DesignTokensTest`, skips in this environment (Playwright absent, C-8 forbids
   installing it), so the re-measure is hand work on every commit that touches the flow.

The open question this line closes is a decision, not a gap: the evidence exists, it is on the
record, and it says no.

---

### Doc Drift Closed (T8)

| File                                            | Line         | Fix                                                          |
| ----------------------------------------------- | ------------ | ------------------------------------------------------------ |
| `docs/design-research/DESIGN.md`                | 3            | "no production code" → timestamped + component list          |
| `resources/views/components/layout.blade.php`   | 3-10         | "until table lands" → composer reads table                   |
| `DESIGN.md` (root)                              | 165-180      | Component inventory updated with token-migrated components   |
| `docs/design-research/CONSTRAINTS.md`           | 900 (G-21)   | Ruling B5 exemption recorded                                 |
| `KNOWN-ISSUES.md`                               | 158          | KI-6 closed as decision                                      |
| `PLAN.md`                                       | header       | Slice exit criteria added                                    |

### Open frontend work (2026-10-02, documentation-sync pass)

Copy and control-size defects that are live on a Trainer-facing surface and owned by nobody else's slice. This
section is the registration `KNOWN-ISSUES.md`'s never-cited entries needed; it is a living list, so entries
leave it when their fix lands.

| State   | ID                      | Work                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               |
| ------- | ----------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Open    | KI-46                   | The import's per-stat validation error prints the internal array path to the Trainer: "A speed value is outside what this scenario allows: The turns.0.speed field must be between 0 and 1400." `turns.0.speed` is the Form Request's nested key and is the only part of the sentence naming which row broke. Needs a message rewrite in `ImportHistoricalRunRequest` that carries the row explicitly, not a display patch; filing (not fixing) was the ruling because Laravel's `attributes()` maps a wildcard key to one string, so renaming it loses the row.   |
| Open    | KI-29/KI-37 follow-on   | The 44px control contract is now stated at `DESIGN-CORPUS.md:904`; the catalog index was resized at `9cba3ee`. Remaining surfaces citing the old bare `DESIGN.md §6.14` form should be re-measured, not assumed covered.                                                                                                                                                                                                                                                                                                                                           |

---

## trainer-advisor.md

## Spec: BuildTarget + Trainer Advisor (Trainer Desk 2.0, subsystem 1)

Status: **Spec — awaiting owner review.** No code yet.
Date: 2026-10-04
Authorization: `ADR-0020` Decision §2 (target-based Trainer Advisor), extending `ADR-0001`; `PRD.md` US-13, US-14, FR-F.
Approach: A (minimal honest advisor; domain-only, no UI — the SPA rewrite hosts the interface later).

_Dated close-out 2026-10-08 (documentation-sync pass): the status line above is the pre-C2 read and is
superseded; the spec body below it stands as the record of what C2 implemented. Phase C slice C2 landed
the v1 engine (`app/Services/Advisor/TrainerAdvisor.php` + `config/advisor.php`, `FR-F`; plan §7's C2 row);
the Career Cockpit (`SCR-CAR-011`, D8) then hosted the interface this spec's Approach line said the SPA
rewrite would host later. The in-scope v1 list — per-run `BuildTarget` (C1), the engine ranking the six
energy-relevant options, the two-state energy band against the sourced threshold and the per-suggestion
reason line — is shipped. Everything the spec's "Out of scope (v1)" list names is still unbuilt and still
banned by its evidence bar: no stat-yield projection, no numeric failure estimate (`ADR-0001` §3), no race
advisory. This close-out is a landing note, not a re-description: the spec preserves its original status
line as the 2026-10-04 record it is._

### 1. Purpose and scope

Rank the Trainer's next-turn options against a Trainer-entered build target, deterministically, with every
number's provenance shown. The app advises; the Trainer decides.

**In scope (v1):**

- A per-run `BuildTarget` record (US-13).
- A `TrainerAdvisor` engine that ranks the six energy-relevant options — Speed, Stamina, Power, Guts, Wit,
  Rest — against the target and the `ADR-0001` Energy constants (US-14).
- A two-state energy advisory band against the single sourced threshold; a row-level reason line per
  suggestion.

**Out of scope (v1), named so they are not read as oversights:**

- **No stat-yield projection** ("+62 Speed"). No source publishes per-training yield (it depends on support
  bonds, training level, and facility, none in scope). Same evidence bar as `ADR-0001`'s rejection of full
  probability estimates.
- **No numeric failure estimate.** No source publishes a failure curve (`ADR-0001` §3). The opt-in numeric
  model is deferred to v1.1 and needs an owner-ruled, clearly-labelled model; it is not built here.
- **No race advisory.** "Next mandatory race turn" is not computable (`ScenarioSlot` has no year/turn) and
  race outcomes stay banned (`ADR-0016`, §6.11). Race is not a ranked option in v1.
- **No UI.** The engine is headless and unit-tested; the cockpit panel ships with the SPA rewrite.
- **No support-bond, facility, or scenario-mechanic modelling.**

### 2. BuildTarget storage and contract

One nullable json column `build_target` on `training_runs`, read through a typed
`App\Models\Advisor\BuildTargetPayload` value object — the `ADR-0010` `legacy_selection` /
`LegacySelectionPayload` pattern, not a new table. It is an optional 1:1 record; a table is
over-engineering.

Fields (all nullable except where noted):

| Field                | Type       | Notes                                                                                         |
| -------------------- | ---------- | --------------------------------------------------------------------------------------------- |
| `purpose`            | enum       | `StoryClear` / `ChampionsMeeting` / `ParentFarming` / `SkillFarming`                          |
| `distance`           | string     | one of the four distance bands                                                                |
| `surface`            | string     | Turf / Dirt                                                                                   |
| `style`              | string     | one of the four running styles                                                                |
| `targets`            | array      | five per-stat ints, each validated against `ScenarioCaps::forRun($run)` (US-13, `ADR-0015`)   |
| `skill_priorities`   | string[]   | ordered; names only, no engine over them in v1                                                |

Writes go through a Form Request (`StoreBuildTargetRequest`); validation has one owner. Trainer-entered,
so `is_manual` in spirit — the fetch engine never touches it. A run with no target names the absence; the
advisor then falls back to `ADR-0001`'s Energy-only guidance rather than inventing a target.

### 3. Constants — `config/advisor.php`

The `ADR-0001` §2 constant set, versioned and dated, stored in config and never hardcoded. v1 uses four;
the rest are recorded for later and not read by the v1 engine.

| Key                    | Value                                                                | Source (`UMAMUSUME_REFERENCE.md`)   | Confidence           |
| ---------------------- | -------------------------------------------------------------------- | ----------------------------------- | -------------------- |
| `rest_recovery`        | +30                                                                  | §1.1.5, GameWith 2026-09-25         | current              |
| `session_cost`         | [17, 28] (range; scales with training level, which is not tracked)   | §1.1.6, GameWith                    | ⚠ STALE 2023-02-25   |
| `wit_cost`             | 0                                                                    | §1.1.1, Game8 2026-09-10            | current              |
| `advisory_threshold`   | 50                                                                   | §1.1.5, qualitative only            | current              |

`session_cost` is a range and stays a range: energy-after renders as `E−28 … E−17`, not a single invented
point. The config carries `source` + `verified_at` per entry so a suggestion can show the date of the data
behind it (`ADR-0001` §7). Mood multipliers are recorded in `ADR-0001` but **not** loaded in v1 — they only
matter to yield prediction, which v1 does not do (YAGNI).

### 4. TrainerAdvisor engine — `app/Services/TrainerAdvisor.php`

Pure functions over: the latest `TurnEntry` (five stats, `energy`, `mood`), the run's
`BuildTargetPayload`, and `config('advisor')`. No DB writes; no game-state guessing.

For each of the six options it returns a small value object:

```text
action            Speed | Stamina | Power | Guts | Wit | Rest
deficit_closed    max(0, target - current) for that stat; 0 for Rest; Wit uses its own target
energy_after      range from session_cost; rest_recovery for Rest; wit_cost (small recovery) for Wit
band              Advisory state from CURRENT energy vs advisory_threshold; Wit => RiskNotMeasured
reason            a string naming the arithmetic ("Wit costs 0 Energy and you are at 42")
```text

The band is computed from **current** energy against the sourced 50 line (`ADR-0001` §3). It has exactly
two states — `AtOrAboveAdvisory` / `BelowAdvisory` — because 50 is the only sourced threshold; the
"highly dangerous below 30" line is not in any source (`ADR-0001` §3 final bullet) and is **not** rendered
as game fact. Wit always enters at full Energy, so a band would assert an exemption the sources leave
unsettled — Wit renders `RiskNotMeasured` (`ADR-0001` §3 correction, Source Conflict Log row 2).

### 5. Ranking and reason lines

Deterministic, in this order. The first rule that fires produces the recommendation; the next-best option
is reported as the alternative.

1. **No current energy** (latest turn has `energy = null`, e.g. a historical run) → no band, no ranking;
   render the absence by name.
2. **Below advisory** (`energy < 50`) → recommend **Rest**; reason: "Energy E is below the 50 advisory
   line; Rest recovers +30." Alternative: **Wit** if it has a target deficit ("Wit costs 0 Energy and
   still makes progress").
3. **At or above advisory, no build target** → Energy guidance only (`ADR-0001`'s original scope): no stat
   ranking, because there is no target to rank against. Names the absence.
4. **At or above advisory, target set** → recommend the training with the **largest deficit**; ties break
   by `config('scenarios.stat_order')`. Reason: "<Stat> is D below target (largest deficit)."
   Alternative: **Wit** if it has a deficit (energy-free progress).

Every suggestion carries its reason; a suggestion with no derivable reason does not ship (`ADR-0001` §4).
Figures are labelled with the provenance vocabulary (`ADR-0020`): band and deficit are **calculated** from
entered turns + declared constants; nothing here is **confirmed** game state.

### 6. Source limits and owner decisions this spec does NOT make

- **Danger band boundary.** Only 50 is sourced. A third band needs an owner-ruled threshold, attributed as
  an owner ruling (`ADR-0001` §3). v1 ships two states. *Owner input needed if a Danger band is wanted.*
- **Numeric failure estimate.** Needs an owner-ruled, config-stored, clearly-labelled model (no sourced
  curve). Deferred to v1.1. The US-11 numeric-estimate preference stays off and inert until then.
- **`mood` vs `condition` column overlap.** Unresolved since `ADR-0001` §5; the advisor reads `mood` and
  does not touch `condition`. Not a blocker.

### 7. Testing

- Unit (no DB): the ranking rules (each of the four), the band boundaries (49 vs 50; Wit →
  RiskNotMeasured), the energy-after range, and that every produced suggestion has a non-empty reason.
  Constants injected from config so a changed constant is caught.
- Feature: `build_target` round-trips through the Form Request; a target above the scenario ceiling is
  rejected (`ScenarioCaps`); a run with no target renders the named absence. Uses factories; no network.

### 8. Files touched

- `database/migrations/…_add_build_target_to_training_runs_table.php` (+ `ARCHITECTURE-ESSENTIALS.md`
  digest, `ADR-0020` citation, working `down()`) — scratch DB, not the shared dev file.
- `app/Models/Advisor/BuildTargetPayload.php` (new; mirrors `LegacySelectionPayload`)
- `app/Models/TrainingRun.php` — one accessor (`buildTarget()`), cast registration
- `config/advisor.php` (new)
- `app/Services/TrainerAdvisor.php` (new)
- `app/Http/Requests/StoreBuildTargetRequest.php` (new)
- `tests/Unit/TrainerAdvisorTest.php`, `tests/Feature/BuildTargetTest.php` (new)

No views, no routes for the advisor output in v1 (the target is stored via the existing run form; the
advisor is consumed by the SPA rewrite later). `git status` first — Pint/PHPStan run whole-tree and this
tree carries other sessions' in-flight work; scope to my own paths.

### 9. Deferred to later slices

Numeric failure model (v1.1, owner-ruled), race advisory (after the `ADR-0016` data blocker closes),
support-bond/facility-aware ranking, mood-weighted ranking, the cockpit UI (SPA rewrite), and any Grand
Concert mechanics (documented: false).

---

## REORGANIZATION_PLAN.md

> **Status note, 2026-10-06 (not part of the embedded plan): EXECUTED IN PART the same day, on the owner's
> instruction to organize the tree.** The owner's instruction answered the ruling this banner was written
> for, so the body below ran through the prepared executor `.scratch-uma/reorganize-scratch.ps1` with two
> additions, and the `DO NOT MOVE` ruling above is respected rather than reversed:
>
> 1. **The hold set.** Every path that tracked files cite stays in place: 28 root files (the dated
>    reproducers `tier-probe.php`, `wal-probe.php` and friends, the exact `.sqlite` a measurement ran
>    against, the corpus `*.json`) plus the `s14/` and `s15/` subtrees, measured the same day at 82
>    citation lines across 11 tracked files. Not one cited path moved, so the 58-line citation blocker the
>    2026-10-01 plan recorded never materialized.
> 2. **Two executor defects fixed before the run.** The stock script compared `/` patterns against Windows
>    `\` paths, which silently defeated every `Exclude` guard and every subdirectory rule (`s15/run1.html`
>    would have left a held subtree), and its patterns are unanchored, so a second run would have
>    conflict-renamed files already inside a bucket. The script now normalizes separators and only
>    considers root-level files plus the two live subtrees.
>
> Result: 223 files moved across two passes (165 + 58), root down from 247 listed at planning time to 31
> (the 28 cited files, `D3-trainee-plan.md` and `D4-build-target-plan.md` held pending their own owner
> decision, and the executor itself), into the 13 buckets the mapping table names; `p17/` and `shots/` emptied
> into `data/raw`/`reports/screenshots` and were removed, so `experiments/` never materialized. Two moved
> scripts had internal path strings patched (`count-probe.php`, `ki47-fixture.php`), two docblock replay
> lines followed their files (`prd-outbound-citations.php`, `slice4-fixture.php`), and the census did not
> move: 661 dead links before the reorganization and 661 after. The body below stays verbatim as the plan
> that was executed.

## Reorganization Plan for `.scratch-uma`

### Current State Analysis

The directory contains ~150 files mixed at root level with 4 subdirectories (`p17`, `s14`, `s15`, `shots`). Files fall into these categories:

| Category               | Count     | Examples                                       |
| ---------------------- | --------- | ---------------------------------------------- |
| SQLite databases       | ~45       | `*.sqlite`, `*.sqlite.bak`                     |
| JSON data files        | ~20       | `*.json` (large datasets)                      |
| HTML reports           | ~15       | `*.html` (test/debug outputs)                  |
| PHP scripts            | ~30       | `*.php` (fixtures, verification, inspection)   |
| Python scripts         | ~10       | `*.py` (data processing, analysis)             |
| Logs/text outputs      | ~25       | `*.txt`, `*.log`, `*.csv`                      |
| Markdown docs          | ~5        | `*.md`                                         |
| Test results (JUnit)   | ~4        | `*-junit*.xml`                                 |
| Screenshots            | ~4        | `shots/*.png`                                  |
| CSS                    | 1         | `app_css_mood_only.css`                        |
| PowerShell             | 1         | `emit-sidecars.ps1`                            |

### Proposed Folder Structure

```text
.scratch-uma/
├── data/
│   ├── json/           # Large JSON datasets (character-cards.json, skills.json, etc.)
│   ├── sqlite/         # SQLite databases (*.sqlite, *.sqlite.bak)
│   └── raw/            # Raw dumps (uma-data.*.js, manifest.json)
├── reports/
│   ├── html/           # HTML test/debug outputs (deck-run-*.html, imp-run-*.html, etc.)
│   ├── junit/          # JUnit XML test results
│   └── screenshots/    # PNG screenshots (from shots/)
├── scripts/
│   ├── php/            # PHP fixtures, verification, inspection scripts
│   ├── python/         # Python data processing/analysis scripts
│   └── powershell/     # PowerShell scripts
├── logs/
│   ├── test-runs/      # Test suite logs (suite-*.log, ki47-suite.log, serve*.log)
│   ├── analysis/       # Analysis outputs (added-lines.txt, late-lines.txt, ruling-lines.txt, etc.)
│   └── debug/          # Debug logs (m2.log, measure-server.log, paratest.out)
├── docs/
│   └── markdown/       # Markdown documentation (ki_*.md)
├── experiments/        # Preserved subdirectories with context
│   ├── p17/            # Character card / ura objectives extraction
│   ├── s14/            # Data pipeline (join scripts, game8 extraction)
│   └── s15/            # Browser testing (sqlite, html, cookies)
└── temp/               # Temporary/working files (cookies.txt, *.csv, t0-*.txt)
```text

### Mapping Rules

| Pattern                                                                                                               | Destination                                           |
| --------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------- |
| `*.json` (root)                                                                                                       | `data/json/`                                          |
| `*.sqlite`, `*.sqlite.bak` (root)                                                                                     | `data/sqlite/`                                        |
| `p17/*.json`, `s14/*.json`, `s14/*.js`                                                                                | `data/raw/`                                           |
| `*.html` (root)                                                                                                       | `reports/html/`                                       |
| `shots/*.png`                                                                                                         | `reports/screenshots/`                                |
| `*-junit*.xml`                                                                                                        | `reports/junit/`                                      |
| `*.php` (root)                                                                                                        | `scripts/php/`                                        |
| `*.py` (root)                                                                                                         | `scripts/python/`                                     |
| `s14/*.py`                                                                                                            | `scripts/python/` (preserve in experiments/s14/)      |
| `*.ps1`                                                                                                               | `scripts/powershell/`                                 |
| `suite-*.log`, `ki47-suite.log`, `serve*.log`                                                                         | `logs/test-runs/`                                     |
| `*lines*.txt`, `ruling-lines.txt`, `late-lines.txt`, `added-lines.txt`, `qual.txt`, `oq5.txt`, `b2.txt`, `clos.txt`   | `logs/analysis/`                                      |
| `m2.log`, `measure-server.log`, `paratest.out`, `peer-inventory-start.txt`                                            | `logs/debug/`                                         |
| `*.md`                                                                                                                | `docs/markdown/`                                      |
| `*.css`                                                                                                               | `data/raw/` (or could go to `reports/html/` assets)   |
| `*.csv`, `t0-*.txt`, `cookies.txt`, `stage-list.txt`                                                                  | `temp/`                                               |
| `p17/`, `s14/`, `s15/`                                                                                                | `experiments/` (preserved as-is)                      |
| `shots/`                                                                                                              | `reports/screenshots/` (merged)                       |
