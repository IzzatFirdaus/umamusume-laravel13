# Process Plans and Reports

## Provenance

This document consolidates the following source files verbatim (no summarization, no deduplication):

- `docs/requests/2026-10-01-scratch-tree-reorganization-plan.md`
- `docs/requests/reports/2026-09-30-c5-down-enforcement-gap.md`
- `docs/PLAN-UI-UX-2026-10-02.md` (embedded 2026-10-02, working file deleted 2026-10-03; this section is live)
- `docs/PLAN-DOC-SYNC-2026-10-02.md` (embedded 2026-10-03 and the working file deleted in the same commit; this section is live)

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
```

| Directory | Files | Tracked | Citing files | Citing lines | Disposition | Why |
|---|---|---|---|---|---|---|
| `research-scratch/` root | 53 loose | 0 (ignored) | 20 | 51 | **REORGANIZE** | loose working files; 44 carry no citation |
| `research-scratch/data/` | 87 | 0 | in the 20 above | in the 51 | leave | cited as live command input (`php -r`, `node -e`, `wc -c`) |
| `.scratch-uma/` | 181 | 0 (ignored) | 18 | 58 | **DO NOT MOVE** | provenance evidence; `skills.json` 53, `scenarios.json` 50, `character-cards.json` 31 citing lines; 72 SQLite files |
| `docs/frontend-review/2026-09-28/` | 143 | 143 | 4 | 5 | **DO NOT MOVE** | inventory §11; dated capture set, 94 txt + 46 png |
| `docs/design-research/verification/` | 19 | 17 | 16 | 34 | **DO NOT MOVE** | slice records, the highest citation density per file in the group; inside the forbidden glob |
| `docs/design-research/_scratch/` | 67 | mixed | 4 | 8 | leave | already the ignored scratch path G-60 names |
| `docs/design-research/mockups/` | 29 | mixed | 0 | 0 | **OWNER** | no inbound citations; fenced only by the directory glob |
| `docs/design-research/prototypes/` | 8 | mixed | 1 | 1 | **OWNER** | one inbound citation; fenced by the glob |
| `docs/design-research/` top level | 16 | 16 | hubs | 41 + 45 | **DO NOT MOVE** | holds both `DESIGN.md` and `CONSTRAINTS.md` twins |

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
```
Expected: the second number is 0. If it is not, stop and re-run Task 1 later or on an isolated worktree.

- [ ] **Step 2: Confirm tracked state is clean.**

```bash
git -c core.quotepath=false status --porcelain --untracked-files=no
```
Expected: empty output. Any ` M ` line is someone's uncommitted work in a shared tree. Stop and name the file.

**Observed at plan-writing time, `9b63e8a`: this gate fails right now.** Five tracked files are dirty and none of them is this plan's work: `app/Services/DataPipeline/PipelineRunner.php`, `config/uma.php`, `database/seeders/DatabaseSeeder.php`, `database/seeders/UmamusumeSeeder.php`, `phpunit.xml`. A peer session is mid-change in application code. Do not run Tasks 3 through 5 until that clears.

The cost of skipping this gate is not hypothetical. The authorized append to `docs/design-research/SESSION-CONSOLIDATION-2026-09-30.md` §6 left this session uncommitted, on the owner's no-commit fence, and a peer swept it into `9b63e8a`, whose message is about KI-47 and KI-48. The line survived and is in `HEAD`; its provenance is now attributed to an unrelated commit.

- [ ] **Step 3: Record the baseline gates.**

```bash
make lore
php artisan test --compact
vendor/bin/pint --test --format agent
```
Expected: `make lore` prints only hits you have already ruled on (the deprecated-PDF review's §4.1 notes the folder is invisible to both greps); tests green; Pint passed. Paste the three outputs into the run log. If the baseline is not green, do not start Task 3: you could not tell your own damage from a pre-existing break.

- [ ] **Step 4: Verify the ignore rules are what the plan assumes.**

```bash
git check-ignore -v research-scratch .scratch-uma
```
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
```
Expected: `self-test: 7/7 classifier cases pass`, exit 0. On any FAIL, fix `classify_line()` first; do not proceed.

- [ ] **Step 2: Scan.**

```bash
python research-scratch/scripts/reorg_scratch.py --scan
```
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
```
Expected: 44 `MOVED` lines, a journal at `research-scratch/reorg/journal.json`, no `SKIP`.

- [ ] **Step 2: Prove nothing tracked moved.** These files are gitignored, so the tracked set must be untouched.

```bash
git -c core.quotepath=false status --porcelain --untracked-files=no
```
Expected: empty. Any output means a refusal pattern failed; stop, run `--rollback`, and report.

- [ ] **Step 3: Prove the count is conserved.**

```bash
find research-scratch -maxdepth 1 -type f | wc -l
find research-scratch/{scripts,reports,drafts,out} -type f 2>/dev/null | wc -l
```
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
```
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
```
Expected: `EDITED <file>: n line(s)` per file, ledger written to `research-scratch/reorg/rewrite-journal.json` with `applied: true` only on LIVE lines.

- [ ] **Step 2: Prove each pattern matched exactly what the ledger says.**

```bash
git diff --stat
git diff -U0 | grep -E "^[+-]" | grep -v "^[+-][+-]" | head -40
```
Expected: changed-line count equals the LIVE count from Task 4, and every `+` line differs from its `-` partner by the path only, never by surrounding prose. A diff that touches more than the path is a bad substitution; revert that file.

- [ ] **Step 3: Prove no LIVE pointer still points at a moved path.**

```bash
python research-scratch/scripts/reorg_scratch.py --rewrite --dry-run
```
Expected: the LIVE count is now 0; remaining hits are RECORD and UNKNOWN only, and the ledger names them.

- [ ] **Step 4: Re-run the gates and compare to Task 1.**

```bash
make lore
php artisan test --compact
vendor/bin/pint --test --format agent
```
Expected: identical to baseline. `make lore` cannot gain hits from a `docs/` path edit except through vocabulary this plan did not introduce; if it prints something new, that line is yours and you rule on it before continuing.

---

#### Task 6: Close-out and handoff

- [ ] **Step 1: Spot-check five citations** chosen from the ledger, one per bucket, by opening the target path and confirming it resolves.
- [ ] **Step 2: Update `docs/SOURCE-OF-TRUTH.md`** only if the inventory's §10 item 4 header work is authorized; otherwise note in the report that the derived doc still carries no regeneration stamp and the reorg did not add one.
- [ ] **Step 3: Report** with: HEAD sha, moves applied, LIVE/RECORD/UNKNOWN counts, gate outputs, files left held and why.
- [ ] **Step 4: Commit only on authorization**, by explicit path, one commit for the rewrite.

---

#### 7. Owner decisions this plan cannot make

| # | Decision | Notes |
|---|---|---|
| 1 | Override the do-not-touch list for `.scratch-uma/`, `docs/frontend-review/2026-09-28/`, or `docs/design-research/**`? | Requires a written override naming the directories. The cost is measured above: dated evidence rewritten into paths that did not exist at measurement time. `mockups/` (0 inbound) and `prototypes/` (1 inbound) are the only two where moving is cheap. |
| 2 | After Task 3, prune `.scratch-uma/`? | 72 SQLite files and 11 logs are disposable, but pruning is deletion, and deletion needs a separate authorization plus proof no session holds a WAL file open. |
| 3 | Track any `research-scratch/` deliverable? | `DOCUMENTATION-INVENTORY-2026-09-30.md` and `unfinished-phases-audit-2026-09-29.md` are cited by nothing in the tracked corpus and are the only two files there with lasting value. |
| 4 | Keep `reorg_scratch.py` after the run? | It is disposable by design. If the second pass ever matters, it belongs in `tools/` beside `gate.py`, and that is a tracked-code decision under C-8. |

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

| ID | Decision | Recommended default | Blocks |
|---|---|---|---|
| O-1 | Push order: local master is 17 ahead of `origin/master`, which carries none of this plan's basis docs | **Push local master to `origin/master` first.** The basis documents are the evidence every task brief cites; leaving them local-only means each closure has to cite a commit a reviewer cannot fetch. Reversible if the owner prefers a branch, but then closure wording must say "local-only citation". | Every register closure in M1/M2/M3 |
| O-2 | WS-5 KI-43 fix: Option A (search-first combobox), Option B (filtered shortlist), Option C (accept and document) | **Option A**, the register's own fix candidate 1 (`KNOWN-ISSUES.md`), reusing the M3 combobox factory | M4's task start only |
| O-3 | R82 ratification: the 768px floor in `DESIGN.md` §2.3 rests on an unratified proposal | **Ratify R82 as drafted**; it already matches the 390px measurement work in M5 | M5 only |
| O-4 | §3.1 lineage pick in `SESSION-CONSOLIDATION-2026-09-30.md` | **No action needed for this plan.** It gates the alternate lineage chain, not any page here; the master lineage shipped at `555b0cb` | Nothing in this plan |

**Not a decision:** the plan's own scope. Section 7 lists what is deliberately excluded; nothing outside it is scheduled.

#### 0.2 Top risks

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Push order (O-1) unsettled, so closures cannot be written and M1's exit cannot be met | High | High | Owner decides before M1 opens. Every closure task verifies `git branch -r --contains <sha>` before writing the closure line. |
| Combobox generalization breaks the trainee selector | Medium | High | The existing `TraineeSelectorTest` suite must pass unchanged before the skill call site is written; it is the regression net for the extraction. |
| Owner ratification of the 768px floor delayed | Medium | Medium | M5 waits. Do not build against an unratified contract. |
| Shared worktree collisions | High | Medium | `gstack:careful`; stage named paths only; `git status --short` before every commit. |
| Seeded DB does not populate `race_catalog_slots` offline | Medium | Low | Task 6.2 verifies the row count first; the fallback measures the turn log only and records the deferral. |
| Register sweep verdicts contested | Low | Low | Each disposition records the evidence read; the owner overrides in writing. |

---

### Legend of identifiers used in this plan

Short names appear throughout the briefs. Definitions:

| Short name | What it is | Where it lives |
|---|---|---|
| R73 | The "read these before touching the run screen" list | `docs/research-scratch/SLICE-RECORDS.md:3613` |
| R82 | The unratified proposal for a 768px responsive floor | `docs/research-scratch/PLANS-AND-BRIEFS.md:186+` |
| R85 | The re-open record for KI-25 (scroll measurement gap) | `KNOWN-ISSUES.md` (KI-25 entry) |
| D-30 | The design-corpus clause that fixes which columns a card or trainee row may render | `docs/research-scratch/DESIGN-CORPUS.md:1805` |
| D-31 | The design-corpus clause that describes the 0..1200 stat bound as a live defect | `DESIGN-CORPUS.md:1806` |
| D-40 | The design-corpus clause holding the responsive-floor rule | `DESIGN-CORPUS.md:1834` |
| D-289 | The errata-forward rule (supersede with a dated note; never edit historical text in place) | `DESIGN-CORPUS.md` gate table |
| G-SK-13 | The four-band skill-picker gate, currently a clause inside KI-33 rather than its own entry | `KNOWN-ISSUES.md:1585,:1618` |
| KI-nn | A register entry in `KNOWN-ISSUES.md` (known issue) | root `KNOWN-ISSUES.md` |
| C-n | A constraint in root `CONSTRAINTS.md` | root `CONSTRAINTS.md` |
| G-n | A design gate in the `DESIGN-CORPUS.md` gate table | `DESIGN-CORPUS.md:~:2558-2615` |
| Slice 13 | The verified run-screen slice (skill combobox precedent) | `docs/research-scratch/SLICE-RECORDS.md` §"slice-13-2026-09-29.md" |
| "Slice N" in the current-state table | A numbered product slice, not a git ref; each maps to a `slice-NN-YYYY-MM-DD.md` record | `SLICE-RECORDS.md` |
| WS-n / M-n | Workstream / milestone in this plan | this document |

#### Lore baseline movement

v1.0 recorded 98 hits / 55 exempt. v1.1 measured 181 hits / 55 exempt. Exempt held steady, so the movement is a change in what is *scanned*, not a change in rulings: the exemptions are per-line markers that did not move, while the hit total rose because `composer lore-code` was extended to untracked files and the Global client terminology list (Wisdom, Motivation, gacha, jewel) was added to the same run. Both numbers are dated measurements, recorded as relative acceptance criteria rather than baselines.

---

---

### 1. Current State Assessment

#### 1.1 What exists and works

| Surface | State | Evidence |
|---|---|---|
| Catalog index + detail | Renders; aptitude grid and form tabs landed | `555b0cb`; `catalog/show.blade.php` |
| Run screen (dashboard) | Renders with stat band, guided rail, timeline, calendar | Slice 5, 8, 13 verified |
| Run screen skills editor | Repeater with per-control labels landed; still one native `<select>` per row with 623 options each | `4902f1d`; `runs/show.blade.php` (rows `:479-481`, select `:538-547`) |
| Skill search (Screen D) | Renders, escaped, paginated, dark-theme clean | Slice 15 browser pass |
| Trainee combobox (run create) | WAI-ARIA APG combobox, prefix match, grouped | `trainee-combobox.ts`; `TraineeSelectorTest.php` |
| Support deck panel | Six pickers, Scenario Link derived, effect lines at "highest stated anchor" | Slice 2, ADR-0014; `deck-panel.blade.php:61,126` |
| `color-scheme` declarations | Shipped | `ed71741`; `resources/css/app.css:219,:229` |
| Lore gate | 181 hits / 55 exempt; lore-code 42 (measured 2026-10-02 at `e18a032`) | `composer lore`, `composer lore-code` |

#### 1.2 What is broken, incomplete, or unverified

| ID | Priority (plan-assigned; the register assigns none) | Surface | State |
|---|---|---|---|
| KI-25 | Medium | Turn log at 390px | RE-OPENED (R85); arrow-key scroll + `scrollWidth` re-read never measured in a browser; D-40/768px reconciliation held on R82 |
| KI-29 | Low | Catalog index controls | `catalog/index.blade.php:15,:19,:32` carry no `h-11`; the 44px rule is `DESIGN-CORPUS.md:904` |
| KI-32 | Low | Dark theme native controls | Fix shipped (`ed71741`); register entry still OPEN and owes the closure with the browser read |
| KI-35 | High | Trainee detail page | Five of the eight target sections render (Basic info, Aliases, Costume forms with aptitude grid, Provenance); Skills, Goal races, Her runs absent; stale copy claims skill lists "are not stored" (`catalog/show.blade.php:226`) |
| KI-43 | Medium | Support deck panel | Deck block 296,537 B with six equipped; 88.5% of the page with none; `<option>` payload alone 207,504 B (57.6%) |
| KI-48 | Low | Architecture docs | Fix shipped (`79d5f6f`, dated errata citing ADR-0015 and `8bda7db`); register entry still OPEN |
| KI-49, KI-51 | Medium | Source bodies / seeders | Bodies tracked since `8b17703` / `30b3a08`; entries stale, owe verify-and-close |
| KI-53, KI-54 | Medium | `race-tier-labels` fixture | Fixture tracked at HEAD, tree clean, the 11-red state no longer reproduces; entries stale; the `withTierLabelsFileAbsent()` helper (`ScenarioSlotSeederResilienceTest.php:102`) is still unguarded |
| KI-45 | Medium | Race calendar data | Headline (no offline population path) superseded by `8b17703` (`seed_file`); second gap stands: `scenario_slots.tier` NULL on 141/296 |
| G-SK-13 | Medium | Skill picker on run screen | Lives inside KI-33 (`KNOWN-ISSUES.md:1585,:1618`), not its own entry; four-band picker unbuilt; 623 available skills |
| Phase B2 | Medium | Skill facts on read rows | Design drafted (`SKILLS-MECHANICS.md:3383-3386`); storage decision outstanding (Data Engineer / Architect) |
| D-31 | Low | Design-corpus clause | `DESIGN-CORPUS.md:1806` still describes the 0..1200 bound as a live defect though ADR-0015 landed |
| Doc drift | Low | Referenced docs absent | `docs/GATE-REGISTRY.md` and `docs/PRE-MORTEM.md` are both cited by `agents.md` but absent from the tree |

#### 1.3 Skills available

`SKILL.md` no longer holds a roster. The scanner is the authority; the commands live in Appendix B (defined once, referenced from here). Agents run the scanner before dispatching any task and attach the output to the dispatch record.

**Two shas appear in this plan, and they are not the same measurement.** The basis tree is `e18a032`: every current-state claim in Section 1 was read there. The scanner counts (`205 skills, 4 errors, 62 warnings`) were taken at `31592ac`, a later commit that moved the tree but did not change this plan's subject matter. Treat the scanner numbers as a dated reading of a later tree, not as a claim about `e18a032`.

**The 4 errors are not a blocker and are not this plan's to fix.** The scanner reports them for the whole workspace, including registries outside `docs/research-scratch/`. Action for a task agent: record the counts in the dispatch record, and if a task's own skills appear in the error list, name that in the task record. Do not edit another workstream's registry to make a count go down.

The skills most relevant to this plan:

| Skill | Use |
|---|---|
| `doubt-driven-development` | Every claim in a task brief is re-derived, not carried |
| `source-driven-development` | Every current-state claim cites `file:line` or a commit |
| `code-review-and-quality` | Six-axis review on every PR |
| `testing-best-practices` / `test-driven-development` | RED before GREEN |
| `antislop-copywriting` | R-02 (no em dash), no AI voice |
| `gstack:careful` | Shared-worktree discipline |
| `laravel-best-practices` | Thin controllers, actions, form requests |

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
```

**Note on the 44px claim:** `h-11` is `2.75rem`. That is 44px only while the root font size is 16px. The browser read in the acceptance list is what proves the contract; the class assertion proves the token was applied, not the rendered height.

**Acceptance:**
- [x] `grep -c 'h-11' resources/views/catalog/index.blade.php` >= 3
- [x] `php artisan test --filter=CatalogTest` green
- [x] Browser read at 1280x800 and 390x844 confirms 44px on all three controls (screenshots attached to the task record)
- [x] `composer lore` and `composer lore-code` counts unchanged from the task's own pre-run capture (do not hardcode dated numbers in the record)
- [ ] KI-29 closes in `KNOWN-ISSUES.md` once the fix is on `origin/master` (O-1)

**Commit:**
```
fix(ui): size catalog index controls to the design contract

KI-29. The catalog index's search field, status filter and submit
button measured 30/31/32px against the 44px control height in
DESIGN-CORPUS.md. The run screen took the same fix at c17e63b;
this is the second surface.
```

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
```

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
```

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
```
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
```
And a view-shape pin, so the `<select>` removal is checked rather than assumed:
```php
it('renders the skill combobox and no skill select in the run repeater', function (): void {
    $html = $this->get(route('training-runs.show', $run))->assertOk()->getContent();
    expect($html)->toContain('skill-combobox');
    expect($html)->not->toContain('name="skills[0][skill_id]" data-role="native-select"');
});
```

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

```
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
```

| Milestone | Workstreams | Target | Exit criterion (work) | Exit criterion (register) |
|---|---|---|---|---|
| **M1 - Immediate fixes + docs** | WS-1 (all) + WS-3 (all) | 3-5 days | KI-29 fixed with the browser read attached; D-30 amended; D-31 erratum landed; the seeder helper guarded; KI-45 re-scoped; one doc-drift KI filed | KI-29, KI-32, KI-48, KI-49, KI-51, KI-53, KI-54 closed, each citing a sha that is on `origin/master` |
| **M2 - Trainee detail page** | WS-2 (all) | 8-12 days | Eight sections render in the order stated in WS-2; every absence string legal | KI-35 closes with the `trainee_goals` reservation note |
| **M3 - Skill selector** | WS-4 (all) | 10-14 days | Combobox on the run screen; no skill `<option>` remains; no picker emits more than 10 rows | KI-33's G-SK-13 clause satisfied |
| **M4 - Support cards** | WS-5 (single task) | 5-8 days | KI-43 addressed per O-2 | KI-43 closed or re-scoped with the measurement |
| **M5 - Responsive** | WS-6 (all) | 5-8 days (6.2 itself is 1 day and unblocked) | D-40 or `DESIGN.md` carries the ratified floor; the 390px measurement recorded | KI-25 closed with both the measurement and the ratification |

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

| Agent | Workstreams | Skills invoked |
|---|---|---|
| **Frontend engineer** | WS-1, WS-2, WS-4, WS-5, WS-6 | `laravel-best-practices`, `testing-best-practices`, `test-driven-development`, `gstack:careful` |
| **Design-system owner** | WS-2 (design review), WS-5 (design review) | `code-review-and-quality`, `doubt-driven-development` |
| **Architect** | WS-3 (D-30, D-31), WS-6 (ratification) | `source-driven-development`, `doubt-driven-development` |
| **Docs Writer** | WS-3 (KI-48 closure), every register update | `antislop-copywriting`, `source-driven-development` |
| **Lore Guardian** | Every WS-1, WS-2, WS-3 and WS-4 change: those are the four that add or alter user-visible copy or a scanned file | none - read and rule |
| **QA** | Every PR's gate run | `code-review-and-quality` |

The Lore Guardian row is not a formality on WS-1 or WS-4. WS-1's acceptance is a lore-count check, and WS-4 adds the band labels, the awakening disclosure and the keep-typing line. A change that ships without a Guardian pass has not met its own acceptance list.

**Every dispatch must include:**
1. The task's `file:line` targets (re-derived, not carried)
2. The expected RED run (test written first)
3. The gate commands to run before hand-off
4. The R73 read-list (`SLICE-RECORDS.md:3613`)
5. The branch freshness check idiom from `PROCESS-PLANS.md:77` (`git rev-list --left-right --count HEAD...origin/master`); there is no gate numbered C-11 in this repo

---

### 5. Gates & Verification

Gate locations: C-1..C-9 live in root `CONSTRAINTS.md`; G-* and D-* gates live in the `DESIGN-CORPUS.md` gate table (~`:2558-2615`). `docs/GATE-REGISTRY.md`, which `agents.md` still cites as the gate runner reference, is absent from the tree; the register sweep (Task 1.3) files this.

#### 5.1 The gate sequence, defined once

Every task runs this sequence before hand-off. It is listed here so a task brief can say "the gate sequence" instead of re-listing it:

```bash
vendor/bin/pint --dirty --format agent      # style, fix in place
vendor/bin/phpstan analyse --no-progress --memory-limit=1G   # level 6
composer lore                               # tracked files
composer lore-code                         # adds untracked files + Global client terms
npm run typecheck                          # if a TS/CSS file changed
php artisan test --compact                 # full suite
```

Notes that are easy to get wrong:
- `phpstan` needs `--memory-limit=1G`; the `composer analyse` script omits it and can OOM on a 128M CLI default.
- Run `pint` before the test run, not after; a formatting fix can change what a rendered-HTML assertion sees.
- `npm run typecheck` is the front-end half of `composer test` and is run separately so a TS-only change does not need a full suite to prove itself.
- `composer lore-code` is additive to `composer lore` (untracked files plus the Global client terminology). Running only `lore` under-reports.
- A Vite manifest error means `npm run build`, not a code defect.

#### 5.2 Per-workstream additional gates

| Workstream | Additional gates |
|---|---|
| WS-1 | Browser measurement at two viewports; canary control read; lore counts relative to the task's own pre-run |
| WS-2 | `DesignTokensTest`, `RenderedCopyHygieneTest`, D-30 amendment landed |
| WS-3 | `DocSchemaDriftTest`, G-60 with retired-literal grep |
| WS-4 | `npm run build`, `TraineeSelectorTest` green and unmodified, C-8 no new dependency |
| WS-5 | Page weight measurement, contrast pairs |
| WS-6 | Browser measurement at 390px, D-40/R82 ratification, seed verification |

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

| Topic | Primary document | Section |
|---|---|---|
| Design system contract | `DESIGN.md` (root) | §2.3, §4.2 |
| Control sizing rule | `docs/research-scratch/DESIGN-CORPUS.md` | "DESIGN.md" §6.14, `:904` |
| Design rules (D-numbers) | `DESIGN-CORPUS.md` | "CONSTRAINTS.md" §5, §6, §10 |
| Gate table (G-*, D-*) | `DESIGN-CORPUS.md` | ~`:2558-2615` |
| Gate structure (C-*) | `CONSTRAINTS.md` (root) | C-1..C-9; note `docs/GATE-REGISTRY.md` is absent from the tree |
| Register | `KNOWN-ISSUES.md` (root) | per KI number |
| Frontend plan | `PLAN.md` | Slice exit criteria, open decisions |
| Architecture | `ARCHITECTURE.md`, `ARCHITECTURE-ESSENTIALS.md` | §3 schema, §7 frontend |
| Product truth | `PRD.md` | FR-A through FR-E |
| Skills mechanics | `docs/research-scratch/SKILLS-MECHANICS.md` | all sections |
| Support cards | `docs/research-scratch/SUPPORT-CARDS.md` | mechanics + module plan |
| Combobox precedent | `resources/js/trainee-combobox.ts` | whole file |
| Combobox test | `tests/Feature/TraineeSelectorTest.php` | whole file |
| Slice records | `docs/research-scratch/SLICE-RECORDS.md` | sections named `slice-NN-YYYY-MM-DD.md` |
| Source-of-truth | `docs/research-scratch/GOVERNANCE.md` | "SOURCE-OF-TRUTH.md" section |

### Appendix B - Skill Registry Verification

Before any dispatch:

```bash
node "${SKILL_REGISTRY_HOME:-$HOME/.qoder/skills/refresh-skill-registry}/scripts/scan-skills.cjs" --project "$(pwd)" --names
node "${SKILL_REGISTRY_HOME:-$HOME/.qoder/skills/refresh-skill-registry}/scripts/scan-skills.cjs" --project "$(pwd)" --violations
```

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

| v1.0 claim | Tree state at `e18a032` | v1.1 disposition | v1.1 task it changed |
|---|---|---|---|
| KI-32 lacks `color-scheme` | Declarations shipped at `ed71741`; register entry still OPEN | Reduced to measurement + register closure | v1.0 Task 1.2 |
| KI-37 measurement owed | Measured 44/44/44/44 both viewports, canary 31; CLOSED 2026-10-01 | Deleted | v1.0 Task 1.3 (deleted) |
| KI-48 flat bound still in arch docs | Corrected at `79d5f6f` with dated errata; register entry still OPEN | Reduced to register closure | v1.0 Task 3.2 |
| ADR-0008/0012 lack the skill columns | Both carry dated `dd90330` errata (`0012:7`, `:73`; `0008:236`, `:439`) | Deleted | v1.0 Task 3.4 (deleted) |
| DESIGN.md §4.2 still has "amber notice" / "Unknown" | Rewritten at `bcd8abe`; old text struck through | Deleted | v1.0 Task 3.3 (deleted) |
| Support-card effect lines unbuilt | `SupportCardEffects` + test + deck-panel lines shipped | Deleted | v1.0 Task 5.2 (deleted) |
| KI-49/51 bodies untracked | All nine `database/seeders/data/*.json` tracked (`8b17703`, `30b3a08`) | Moved to register sweep | v1.0 Task 1.3 |
| KI-53/54 fixture deleted, 11 red | Fixture tracked at HEAD; 11-red no longer reproduces | Moved to register sweep | v1.0 Task 1.3 |
| "26 consolidated documents" | The directory holds 16 consolidated files | Basis corrected | header |
| Branch from `origin/master` | `origin/master` is 17 commits behind local master and carries no `docs/research-scratch/` | Owner gate O-1 added | hand-off |
| "C-11 branch freshness check" | No C-11 exists in the repo | Replaced with the `PROCESS-PLANS.md:77` idiom | Section 4 |
| Lore baselines 98/55 and 7 | Current: 181 hits / 55 exempt; lore-code 42 | Acceptance criteria made relative | all briefs |

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

| # | Gap | Evidence | Severity |
|---|---|---|---|
| A-1 | The register's **own status block is stale and self-declares as authoritative**. `KNOWN-ISSUES.md:10` asserts "35 filed, 24 closed, 11 open" (2026-09-30). The file now holds 54 entries; `:28-29` states "The three numbers in this block are the authoritative counts; a heading grep is indicative only." | 54 headings vs 35 claimed; KI-46..KI-54 filed 2026-10-01, KI-37 and KI-47 closed 2026-10-01, all after the block | **Blocker** |
| A-2 | **Seven OPEN entries describe a state the tree no longer has.** KI-32 (fix shipped `ed71741`: `resources/css/app.css:219` light, `:229` dark), KI-48 (fix shipped `79d5f6f`, errata in `ARCHITECTURE-ESSENTIALS.md:36` and `ARCHITECTURE.md:158`), KI-49 and KI-51 (all nine `database/seeders/data/*.json` bodies and the seeder code tracked at `8b17703` / `30b3a08`), KI-53 and KI-54 (fixture tracked, no `.held-aside`, tree clean, the 11-red state no longer reproduces), KI-45 (headline "no offline population path" superseded by `8b17703`: `'seed_file' => 'race_instances.json'` at `config/uma.php:138-145`). | 20 entries carry OPEN in a heading without CLOSED/RESOLVED; 7 of them are wrong | **Blocker** |
| A-3 | **A doc already asserts what the register denies.** `docs/research-scratch/RACE-AND-SLICE-RESEARCH.md:843` calls KI-48 "the one finding this slice was told to fix — done in §3" while the register keeps KI-48 OPEN. Two sources of truth disagree in public. | `RACE-AND-SLICE-RESEARCH.md:843` vs `KNOWN-ISSUES.md:2362` | **Major** |
| A-4 | **The documentation precedence chain points at files that do not exist.** `AGENTS.md:178` ranks `docs/GATE-REGISTRY.md` second of five authorities; `AGENTS.md:5` names `docs/PRE-MORTEM.md` as the risk record; `AGENTS.md:94` says "see GATE-REGISTRY C-5"; `README.md:18` maps `docs/PRE-MORTEM.md`. Neither file is on disk. Both are recoverable at `22e5135^` (13 and 6 commits touch them). | `git show 22e5135^:docs/GATE-REGISTRY.md` succeeds | **Blocker** |
| A-5 | **512 dead markdown links**, concentrated on sources the consolidation absorbed without repointing the readers: `docs/design-research/CONSTRAINTS.md` 56, `docs/design-research/DESIGN.md` 42, `docs/GATE-REGISTRY.md` 22, `RAW-FINDINGS.md` 20, `docs/PRE-MORTEM.md` 19, `SKILLS-GAPS.md` 19, `docs/SOURCE-OF-TRUTH.md` 17, `docs/design-research/SKILLS-GAPS.md` 11. 38 files carry citations into `design-research/`, `deprecated/`, `requests/`, `data/`, `flows/`, `frontend-review/`. Densest citing files: `CATALOG-ROSTER-WORKSTREAM.md` 57, `SKILLS-MECHANICS.md` 44, `SLICE-RECORDS.md` 29, `KNOWN-ISSUES.md` 26. | `python tools/doc_census.py` | **Major** |
| A-6 | **The repoint targets are already known, not guessed.** `docs/research-scratch/INDEX.md` records which master absorbed which source, and the section headings exist: `DESIGN-CORPUS.md:18` `## DESIGN.md`, `:1665` `## CONSTRAINTS.md`; `GOVERNANCE.md:16` `## SOURCE-OF-TRUTH.md`; `SKILLS-MECHANICS.md:2410` `## skills-section-phase-b2-2026-10-01.md`. Only Round 3 repointed its citations (`SCENARIO-PUBLISHER-REFERENCES.md`: 26 inbound); Round 1 and 2 masters sit at 1 inbound each. | `INDEX.md` tables; census inbound counts | **Major** |
| A-7 | **The dead-link check exists but gates nothing.** `tools/doc_census.py:54` computes `dead_links()` and prints only the first 20 (`:112`), and no composer script or test invokes it. `composer.json:56` registers `lore` the same way a `docs` script could be registered. Citation rot is therefore invisible after it lands. | `grep -n census composer.json` returns nothing | **Major** (prevention) |
| A-8 | **Two KI ids are cited as entries and are not.** `KI-34` (8 citations) is a deliberate reservation with no entry (`KNOWN-ISSUES.md:21-24`); `KI-16` (6 citations) is a renumbering hole the register itself admits: "a genuine renumbering hole, KI-30's pre-merge number" (`:23`). 51 distinct ids are cited outside the register against 54 entries. | set difference of cited ids vs heading ids | **Major** |
| A-9 | **Five entries are never referenced outside the register:** KI-31, KI-46, KI-52, KI-53, KI-54. KI-52 (OPEN) is the two-`DESIGN.md`-basename ambiguity, and the register's own headings exhibit it: KI-29 (`:1390`) and KI-37 (`:1781`) both cite "DESIGN.md §6.14", which resolves nowhere — root `DESIGN.md` has no §6.14 and the 44px rule is `DESIGN-CORPUS.md:904`. KI-46 (`turns.0.speed` leaked to the Trainer) has no reflection in the copy rules; its only trace is a test comment at `tests/Feature/SupportCardTest.php:19`. | `comm -23` of entry ids vs cited ids | **Major** |
| A-10 | **The corpus was consolidated on a false premise.** `CONSOLIDATION-LOG.md:4-6` states the governing file `docs/research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md` "is absent from disk and from all git history (verified: `git log --all -- '*DOCUMENTATION-INVENTORY*'` returns nothing)", so sections 6/7/10/11 "could not be read" and a filesystem survey was substituted. The file is **on disk** at `research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md` (101.5 KB, 644 lines), with §6 "Cross-reference map" at `:353` — in the repo-root `research-scratch/` folder, which `.gitignore:87` ignores (`/research-scratch/`). The verification command cannot see an ignored file, so a history-only probe was reported as disk absence. | `ls research-scratch/`; `git check-ignore -v research-scratch/...` | **Blocker** |
| A-11 | **Seven citations resolve only to untracked files**, so they work on this machine and break on a fresh clone: `README.md:122` → `.agents/README.md`; `docs/SKILL_AUTOMATION.md:29` → `.copilot/instructions.md`; `docs/UMAMUSUME_REFERENCE.md:996,:1042` and `docs/scenarios/09:727,:729` → files inside the ignored root `research-scratch/`; `docs/research-scratch/PROCESS-PLANS.md:27` → the inventory above. `docs/PLAN-UI-UX-2026-10-02.md` is also untracked and unregistered in `INDEX.md`. | census UNTRACKED list | **Major** |
| A-12 | **Every closure is gated on an unpushed branch.** Local master is 17 commits ahead of `origin/master`, which carries no `docs/research-scratch/` at all. Register discipline closes a KI only when the fix is on `origin/master`, so A-2 cannot be actioned until the push is decided. | `git rev-list --left-right --count HEAD...origin/master` = `17 0` | **Blocker** (owner gate O-1) |

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
```

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
```

- [x] **Step 4: Commit**

```bash
git add docs/research-scratch/CONSOLIDATION-LOG.md
git commit -m "docs(consolidation): correct the false absence claim about the governing inventory

The inventory was on disk in the gitignored root research-scratch/ folder; the
history-only probe could not see it. Sections 6/7/10/11 are readable."
```

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
```

- [x] **Step 2: Put the two options to the owner with their costs.** Append to `PLANS-AND-BRIEFS.md`:
  - **Restore:** `git checkout 22e5135^ -- docs/GATE-REGISTRY.md docs/PRE-MORTEM.md`. Clears 41 citations at once and repairs `AGENTS.md:178`'s precedence chain. Cost: two more files in a corpus that was deliberately consolidated to 16 masters, and `INDEX.md` records them as absorbed into `GOVERNANCE.md` — restoring creates a second copy of content that already has a home.
  - **Repoint:** keep the absorption, and edit `AGENTS.md:5,:94,:178` plus `README.md:18` to name `docs/research-scratch/GOVERNANCE.md` and its sections. Cost: 41 citations reviewed by hand, and the precedence chain loses its named #2.
  - Recommend **Restore** only if the absorbed `GOVERNANCE.md` sections are incomplete for C-5 and the pre-mortem §4 (repo #4) addendum; verify before recommending:

```bash
grep -nE 'C-5|pre-mortem|§4' docs/research-scratch/GOVERNANCE.md | head
```

- [x] **Step 3: Do not act on the recommendation without the owner's written ruling.** Record the choice and the date in the same section.

- [x] **Step 4: Commit**

```bash
git add docs/research-scratch/PLANS-AND-BRIEFS.md
git commit -m "docs(briefs): put the GATE-REGISTRY and PRE-MORTEM restore-or-repoint choice to the owner"
```

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
```

- [ ] **Step 3: Close the six that are done, in house style.** — **HELD per Step 1 fallback: six Verified/HELD blocks written instead; KI-32 was closed concurrently by the M1 batch and its block reconciled to that heading.** KI-32, KI-48, KI-49, KI-51, KI-53 and KI-54 all describe a defect the tree no longer has. Append to each entry, after its existing body:

```markdown
**Closed 2026-10-02 (documentation-sync pass).** The fix landed at `<sha>`; this pass verified it on the
working tree (`<file:line>`) rather than re-deriving it from the report that filed it. Nothing about the
original finding is edited: it was correctly filed on 2026-10-01 against a tree that did not yet have the
fix. What was missing was the closure, and that is a register-lag defect, not a code defect.
```

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
```

Fill `<N>`, `<M>`, `<K>` from Step 2's verification, not from a grep. Do not delete the superseded block: `:24-29` is the register's own warning that heading greps mislead, and that warning is still load-bearing.

- [x] **Step 7: Verify no entry lost history**

```bash
git diff --stat KNOWN-ISSUES.md            # additions dominate deletions
git diff KNOWN-ISSUES.md | grep -c '^-[^-]' # expect near zero: removals should be heading edits only
```

- [x] **Step 8: Commit**

```bash
git add KNOWN-ISSUES.md tests/Feature/ScenarioSlotSeederResilienceTest.php
git commit -m "docs(register): reconcile seven entries against the tree and file the unguarded fixture helper"
```

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
```

Under the restore branch, leave the chain intact and confirm `docs/GATE-REGISTRY.md` now exists.

- [ ] **Step 2: Fix the same two paths everywhere they appear as instructions, not history.** Read each hit and classify it as a pointer a reader follows (fix) or a provenance line (leave):

```bash
grep -rn 'GATE-REGISTRY\|PRE-MORTEM' --include='*.md' . | grep -v '^\./research-scratch/'
```

- [ ] **Step 3: Verify the chain resolves**

```bash
for p in CONSTRAINTS.md docs/GATE-REGISTRY.md docs/adr docs/DESIGN.md PLAN.md; do [ -e "$p" ] && echo "ok   $p" || echo "MISS $p"; done
```

- [ ] **Step 4: Commit**

```bash
git add AGENTS.md README.md
git commit -m "docs(agent-map): point the precedence chain at documents that exist"
```

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
```

- [x] **Step 2: Generate the per-target worklist instead of guessing at it.**

```bash
python tools/doc_census.py 2>&1 | sed -n '/most-cited dead targets/,$p'
```

- [ ] **Step 3: Apply the known absorption map to the eight densest targets.** — **SCOPED DOWN:** the two O-2-gated targets (36 + 32 hits) cannot be repointed until the ruling; the convention fix (Task 6) was chosen over bulk editing because KNOWN-ISSUES.md entries are dated records and may not be edited in place, and most of DESIGN-CORPUS / SKILLS-MECHANICS hits are provenance lines the census overcounts. Measured precisely: 200 actionable refs across 29 files (`python .scratch-uma/task5-worklist.py`). Verified section anchors:

| Cited path | Hits | Repoint to |
|---|---|---|
| `docs/design-research/CONSTRAINTS.md` | 56 | `docs/research-scratch/DESIGN-CORPUS.md` §`CONSTRAINTS.md` (`:1665`) |
| `docs/design-research/DESIGN.md` | 42 | `docs/research-scratch/DESIGN-CORPUS.md` §`DESIGN.md` (`:18`); the 44px control rule is `:904` |
| `docs/GATE-REGISTRY.md` | 22 | per Task 2 |
| `RAW-FINDINGS.md` | 20 | `docs/research-scratch/DESIGN-CORPUS.md` §`RAW-FINDINGS.md` |
| `docs/PRE-MORTEM.md` | 19 | per Task 2 |
| `SKILLS-GAPS.md` (+ `docs/design-research/SKILLS-GAPS.md`) | 19 + 11 | `docs/research-scratch/SKILLS-MECHANICS.md` §`SKILLS-GAPS.md` |
| `docs/SOURCE-OF-TRUTH.md` | 17 | `docs/research-scratch/GOVERNANCE.md` §`SOURCE-OF-TRUTH.md` (`:16`) |
| `docs/design-research/verification/slice-*.md` | 15 files | `docs/research-scratch/SLICE-RECORDS.md` §`slice-NN-YYYY-MM-DD.md` |

- [ ] **Step 4: Work the densest citing files first, one file per commit** — **NOT STARTED as a bulk pass**; the register (10 refs) resolved by convention rather than by edit for the same dated-record reason. Remaining: SKILLS-MECHANICS 34, DESIGN-CORPUS 25, CONSOLIDATION-LOG 12, SLICE-RECORDS 12, CATALOG-ROSTER 12, plus ~24 lighter files; PRODUCT.md is machine-generated and excluded, docs/UMAMUSUME_REFERENCE.md was peer-dirty during this pass., in this order: `CATALOG-ROSTER-WORKSTREAM.md` (57), `SKILLS-MECHANICS.md` (44), `SLICE-RECORDS.md` (29), `KNOWN-ISSUES.md` (26), `RACE-AND-SLICE-RESEARCH.md` (18), `GOVERNANCE.md` (16), `docs/adr/0008*` (16), `DESIGN-CORPUS.md` (15), `PROCESS-PLANS.md` (14), `PLAN.md` (11), `PLANS-AND-BRIEFS.md` (10).

- [ ] **Step 5: Keep the rules distinct per file class.**
  - Masters and ADRs: replace a followed pointer inline only where the sentence directs a reader; where the line is a dated record, append a dated erratum instead and leave the original path visible.
  - Never renumber or reorder a section heading; other files anchor into it.
  - `docs/adr/*`: appended errata only (`ADR-0012:7,:73` is the working example).
  - Prove every regex before it runs. These files are hard-wrapped prose; a line-based `sed` will silently miss a citation split across lines and will happily rewrite a provenance line. Check each pattern's match count first:

```bash
grep -cE 'docs/design-research/CONSTRAINTS\.md' docs/research-scratch/DESIGN-CORPUS.md
```

- [ ] **Step 6: Re-measure after each file and confirm the count falls by the expected amount.** On the final pass, record the number Task 7 pins:

```bash
python tools/doc_census.py 2>&1 | grep 'dead markdown links' | tee /tmp/dead-post.txt
composer lore && composer lore-code
```

The lore counts move when a citation is rewritten into a section title that carries a banned word; current baseline is 181 hits / 55 exempt and lore-code 42 (measured 2026-10-02 at `e18a032`). Capture the pre-run number per task and compare to it, never to a dated constant.

- [ ] **Step 7: Commit per file**

```bash
git add docs/research-scratch/CATALOG-ROSTER-WORKSTREAM.md
git commit -m "docs(research-scratch): repoint CONSTRAINTS and DESIGN citations into DESIGN-CORPUS sections"
```

---

#### Task 6: Fix the KI-id citation defects and the DESIGN.md ambiguity convention

**Files:**
- Modify: the 6 `KI-16` and 8 `KI-34` citations outside the register.
- Modify: `docs/research-scratch/DESIGN-CORPUS.md` (appended dated convention note) and `KNOWN-ISSUES.md` (KI-52 note).

**Interfaces:**
- Consumes: Task 3 (KI-16's renumbering is documented at `KNOWN-ISSUES.md:23`).
- Produces: a citation form that resolves for both the register's own headings and the corpus.

- [ ] **Step 1: Repoint `KI-16` to `KI-30`**, keeping the pre-merge number visible on first mention per file, since the register's own entry says it was filed as KI-16: `KI-30 (filed as KI-16 on `fix/frontend-audit-2026-09-28`)`.

- [ ] **Step 2: Annotate every `KI-34` citation** so a reader cannot hunt for an entry that does not exist: `KI-34 (a reservation, not a landed entry; see `KNOWN-ISSUES.md:21-24`)`.

- [ ] **Step 3: Write the disambiguation convention.** `DESIGN.md` is a basename shared by root `DESIGN.md` and by `DESIGN-CORPUS.md` §`DESIGN.md`, and `docs/design-research/DESIGN.md` no longer exists. Root `DESIGN.md` has no §6.14, so KI-29's and KI-37's headings cite a section number that resolves only through the corpus. Append to `DESIGN-CORPUS.md` and note it under KI-52:

```markdown
**Convention (2026-10-02).** A bare `DESIGN.md` citation means the root contract. A section of the corpus
is cited as `DESIGN-CORPUS.md §"DESIGN.md" §6.14`. Register headings KI-29 and KI-37 predate this and
name `DESIGN.md §6.14` for a rule that lives at `DESIGN-CORPUS.md:904`; they are left as filed, and this
convention is the correction forward that KI-52 asks for.
```

- [ ] **Step 4: Give the never-cited entries the coverage each one deserves.** Five entries appear nowhere outside the register (KI-31, KI-46, KI-52, KI-53, KI-54), and only one of them needs a doc:
  - KI-52 is handled by Step 3's convention.
  - KI-46 (`turns.0.speed` printed to the Trainer in an import error) is live user-facing copy with no reflection outside the register and one test comment at `tests/Feature/SupportCardTest.php:19`. Add it to `PLAN.md`'s open-frontend-work list, since `PLAN.md` is the living frontend plan and this is a copy defect:

```markdown
| Open | KI-46 | Import error text names the internal array path (`turns.0.speed`) to the Trainer.
  Registered only in `KNOWN-ISSUES.md`; needs a Form Request message rewrite, not a display patch. |
```

  - KI-31, KI-53, KI-54 are closed or being closed by Task 3, and a historical defect with no live surface does not need doc echo. Record that reasoning in Task 3's sweep note so the absence reads as a decision rather than an oversight.

- [ ] **Step 5: Verify no unresolved KI id remains cited outside the register.** The register itself is excluded on purpose: it names KI-16 and KI-34 while explaining they have no entry.

```bash
grep -oE '^## KI-[0-9]+[ab]?' KNOWN-ISSUES.md | sed 's/^## //' | sort -u > /tmp/e.txt
git grep -oE 'KI-[0-9]+[ab]?\b' -- '*.md' | grep -v '^KNOWN-ISSUES\.md:' \
  | sed 's/^[^:]*://' | sort -u > /tmp/c.txt
comm -13 /tmp/e.txt /tmp/c.txt    # expect: nothing. KI-16 and KI-34 must be gone from /tmp/c.txt
```

- [ ] **Step 6: Commit**

```bash
git commit -am "docs(citations): resolve the KI-16 and KI-34 id defects and fix the DESIGN.md convention"
```

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
```

Define `DOC_CITATION_BASELINE` as an `int` constant at the top of the file, set to the measured number.

- [x] **Step 2: Run it and confirm it fails against the pre-repair count and passes at the baseline**

```bash
php artisan test --compact --filter=DocCitationParityTest
```

- [x] **Step 3: Register the script beside `lore` in `composer.json`.** Existing entries are `"lore": "@php tools/lore.php"` and `"lint": "pint --test"`, so a plain shell command is the correct form here (`@` is only valid for composer's own aliases such as `@php`; there is no `@python`):

```json
"docs": "python tools/doc_census.py",
```

Verify with `composer docs`. Deliberately **not** mirrored into the `Makefile`: GNU make cannot run on this host (KI-4) and the Makefile targets are documentation, so the `make`/composer parity assertion in `LoreGateParityTest:101-116` is left unextended for `docs`. Record that reason in the test's comment so a later reader does not "fix" the asymmetry by adding a dead target.

- [x] **Step 4: Note the truncation limit so the test is not mistaken for a full report** — `tools/doc_census.py:112` prints only `dead[:20]`, so the count is the signal and the per-target summary is the worklist. Record that in the test's comment; do not print 500 lines in a gate.

- [x] **Step 5: Commit**

```bash
git add tests/Feature/DocCitationParityTest.php composer.json
git commit -m "feat(docs): ratchet dead citation counts in a Pest gate"
```

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
```

- [x] **Step 4: Verify the census now sees them**

```bash
python tools/doc_census.py 2>&1 | grep -E 'tracked markdown|dead markdown links'
```

---

### Owner gates this plan cannot pass alone

| Gate | Question | Blocks |
|---|---|---|
| **O-1** | Push local master (17 ahead, includes all of `docs/research-scratch/`) before register closures land? | Task 3's closure lines; every KI closure in the repo |
| **O-2** | Restore `docs/GATE-REGISTRY.md` + `docs/PRE-MORTEM.md` from `22e5135^`, or repoint the 41 citations into `GOVERNANCE.md` sections? | Tasks 4 and 5 |
| **O-3** | Promote durable files out of the gitignored root `research-scratch/`, or declare every citation into it scratch-only? | Task 8, and it decides whether A-10 can happen again |

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
