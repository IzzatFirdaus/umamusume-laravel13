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

**Round 14 (2026-10-10, augment-only):** the four scenario implementation plans and the three Trainer
Desk 2.0 prompt sets, all live documents that remain in place, embedded verbatim as their own sections:
`docs/Our-Grand-Concert-plan.md` (1145 lines), `docs/Trackblazer-plan.md` (583), `docs/Unity-Cup-plan.md`
(561), `docs/URA-Finale-plan.md` (381), `docs/Trainer-Desk-2.0-G-Prompts.md` (1046),
`docs/Trainer-Docs-2.0-D-Prompts.md` (542), `docs/Trainer-Docs-2.0-E-Prompts.md` (230). Each section is
the canonical reading copy while the source stays the live working file; nothing was deleted and no
citation was repointed.

**Round 15 (2026-10-12, consolidation):** the owner ordered the three Trainer Desk 2.0 prompt sets
consolidated and their originals deleted. Each embedded section was re-verified line-by-line against
its source before the delete (G: 687 of 687 non-blank lines matched in order, D: 380 of 380, E: 166 of
166, one-level heading demotion allowed; 0 missing in each). The originals
`docs/Trainer-Desk-2.0-G-Prompts.md` (untracked, so its deletion is not recoverable from history),
`docs/Trainer-Docs-2.0-D-Prompts.md` and `docs/Trainer-Docs-2.0-E-Prompts.md` (both tracked and clean
at HEAD, recoverable from history) were then deleted from `docs/`; these sections are now the only
copies and the citation targets. Two live citations were repointed in the same pass:
`docs/proposals/provenance-and-absence-contracts.md` §22 and
`docs/research-scratch/PLANS-AND-BRIEFS.md` §"E6 — Our Grand Concert baseline strip". The four scenario
implementation plans above remain live and in place; only the three prompt sets were deleted.

---

## 2026-10-01-scratch-tree-reorganization-plan.md

### Scratch-tree reorganization implementation plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development` (recommended) or `superpowers:executing-plans` to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax. Do not start Task 3 until Task 1 returns all-clear and the owner has answered §7.

**Goal:** Sort the loose files in `research-scratch/` into named buckets and repoint every live citation to them, while leaving the four other named directories untouched on documented grounds.

**Architecture:** One disposable Python tool owns the whole operation: classify, prove citations, move, journal, rewrite, verify. It moves only files no tracked document cites, and it rewrites only citation lines a reader would actually follow, because the rest of them record where evidence sat at measurement time.

**Tech Stack:** Python 3.12 stdlib (`python`, not `python3`, on this box), `git -c core.quotepath=false`, `composer lore`, Pest via `php artisan test --compact`.

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
composer lore
php artisan test --compact
vendor/bin/pint --test --format agent
```text

Expected: `composer lore` prints only hits you have already ruled on (the deprecated-PDF review's §4.1 notes the folder is invisible to both greps); tests green; Pint passed. Paste the three outputs into the run log. If the baseline is not green, do not start Task 3: you could not tell your own damage from a pre-existing break.

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
composer lore
php artisan test --compact
vendor/bin/pint --test --format agent
```text

Expected: identical to baseline. `composer lore` cannot gain hits from a `docs/` path edit except through vocabulary this plan did not introduce; if it prints something new, that line is yours and you rule on it before continuing.

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

- [ ] **Step 3: Apply the known absorption map to the eight densest targets.** — **SCOPED DOWN:** the two O-2-gated targets (36 + 32 hits) cannot be repointed until the ruling; the convention fix (Task 6) was chosen over bulk editing because KNOWN-ISSUES.md entries are dated records and may not be edited in place, and most of DESIGN-CORPUS / SKILLS-MECHANICS hits are provenance lines the census overcounts. Measured precisely: 200 actionable refs across 29 files (`python .scratch-uma/cited/task5-worklist.py`). Verified section anchors:

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

1. **Gates green with pasted output** — `pest`, `pint --test`, `phpstan --no-progress`, `composer lore`, `composer lore-code`, `npx vite build`, `DesignTokensTest` all pass; outputs recorded in commit message or linked CI run. **Migration gate:** `migrate:fresh --seed` is a destructive drop and is not run here; the equivalent evidence is `php artisan migrate` plus `db:seed` applied to a **fresh empty scratch DB** (`DB_DATABASE=/tmp/…`), which proves the same thing with no blast radius on the shared dev file. **Lore baseline (R51, 2026-09-29):** `lore-docs` 98 hits / 51 exempt lines, `lore-code` 7; a rules table quoting a banned word to rule on it carries a line marker naming `lore-ignore-line` with `class=` set to one of the four allowed-hit classes and `cite=` set to the rule it answers to (the exact form, with a worked example, is in `docs/research-scratch/GOVERNANCE.md` §"GATE-REGISTRY.md"; markers are legal inside `docs/` and nowhere else), and `LoreGateParityTest` fails a marker outside `docs/`, one missing either half, or a runner that stops honouring the filter.
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

**Dating note (2026-10-09):** This line records that `composer lore` produces output verbatim
identical to `make lore`. GNU `make` does not run on this host (KI-4), so `make lore` is non-
functional here; the parity was measured on a machine where the Makefile target exists. The
correct command on this host is `composer lore`.

**Exit criteria, checked against the list above rather than asserted.** Gates green with pasted
output: §1 of the record. Claims citing file:line or sha: every row in the table. No new
D-violation in touched files: `composer lore` and `lore-code` at parity, and the one hit this
slice introduced (a banned word used as ordinary English in a comment) was reworded rather
than adjudicated. No stale status
lines: KI-8's "rendered by this route and nothing else", KI-11's open half and audit rows 7.2
and 9 were corrected in the same commit that closes them. Atomic commits per concern: seven.
R17 fixture: every measurement ran on `.scratch-uma/cited/slice5.sqlite`, rebuilt for this slice with
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

**Dating note (2026-10-09):** The Slice 1 Output table records the command as `make lore` / `make lore-code` because GNU `make` does not run on this host (KI-4); the Makefile targets are documentation. The actual commands to run are `composer lore` / `composer lore-code`. This table is a dated record and is left as written.

---

### Open Decisions (unowned by any slice)

Three calls stay with the owner and are untouched by Slice 5: theme default (light vs dark),
rounded-font vs system stack (C-8 plus `DESIGN.md` §11), and mid-run "Change scenario"
semantics. The fourth is no longer open.

#### Should Livewire enter the stack? — DECIDED no, 2026-09-28 (R29)

**Decided: no, and the burden of proof sits with the round trip, which was fast.** T1 builds
preview-before-commit server-rendered, and the evidence that decision needs is
the latency of the request Livewire would remove. Measured on the run-detail page today, loopback
`php artisan serve` on `127.0.0.1:8144` against the R17 fixture (`.scratch-uma/cited/slice5.sqlite`,
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

*Dated close-out 2026-10-08 (documentation-sync pass): the status line above is the pre-C2 read and is
superseded; the spec body below it stands as the record of what C2 implemented. Phase C slice C2 landed
the v1 engine (`app/Services/Advisor/TrainerAdvisor.php` + `config/advisor.php`, `FR-F`; plan §7's C2 row);
the Career Cockpit (`SCR-CAR-011`, D8) then hosted the interface this spec's Approach line said the SPA
rewrite would host later. The in-scope v1 list — per-run `BuildTarget` (C1), the engine ranking the six
energy-relevant options, the two-state energy band against the sourced threshold and the per-suggestion
reason line — is shipped. Everything the spec's "Out of scope (v1)" list names is still unbuilt and still
banned by its evidence bar: no stat-yield projection, no numeric failure estimate (`ADR-0001` §3), no race
advisory. This close-out is a landing note, not a re-description: the spec preserves its original status
line as the 2026-10-04 record it is.*

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

---

## Our-Grand-Concert-plan.md

> **Augmented 2026-10-10 (Round 14, augment-only).** Source: `docs/Our-Grand-Concert-plan.md` (1145 lines), reproduced verbatim below with headings demoted one level. The source file remains in place; no citation was repointed and nothing was deleted.

## Our Grand Concert — Incremental Implementation Roadmap

**Project:** Trainer Desk 2.0  
**Scenario:** Our Grand Concert  
**Purpose:** Incrementally complete the existing Our Grand Concert implementation without speculative mechanics, duplicate architecture, broad refactors, or unverified game data.

---

### Implementation Principle

Our Grand Concert is implemented as a sequence of narrow, independently verifiable slices.

Every slice follows:

> **Evidence → smallest truthful representation → canonical lifecycle → UI → tests → stop**

A roadmap item is **not** permission to implement every mechanic implied by its name.

At every boundary:

- Read the authoritative project documentation first.
- Search the existing codebase before creating new architecture.
- Prefer existing models, TurnEvents, controllers, requests, capabilities, and UI surfaces.
- Do not invent unsupported game mechanics.
- Do not infer numeric values from external/community sources when project evidence marks them unverified.
- Do not create duplicate models, tables, endpoints, stores, or frameworks.
- Keep observed facts separate from derived state and recommendations.
- Stop when the assigned slice is complete.
- A slice may split, collapse, be deferred, or conclude with a documented exclusion if evidence does not justify implementation.

#### Evidence hierarchy

When implementing scenario mechanics, distinguish:

1. **Authoritative and verified** — safe to implement/render.
2. **Observed in the application but not formally documented** — may be represented only where the observation itself is trustworthy.
3. **Documented as unverified** — do not render or calculate as fact.
4. **Inferred/community/external mechanics** — do not silently promote to project truth.

---

## Phase A — Performance

### Slice 1 — Baseline Grand Concert Audit

**Status: COMPLETE**

Establish the current implementation state before making changes.

#### Scope

Audit:

- scenario registration;
- scenario selection;
- trainee selection;
- training target;
- inheritance/Legacy;
- support deck;
- preflight;
- career state;
- Performance;
- Lessons;
- Songs;
- Live Bonuses;
- Promotional Lives;
- Grand Concert/finale;
- Trainer Advisor;
- Career Report;
- veteran/Legacy handoff.

Identify existing architecture, missing pieces, known defects, disabled panels/capabilities, and explicit documentation boundaries.

#### Outcome

Created the incremental implementation map and established that Grand Concert functionality must be completed in narrow slices rather than through a broad rewrite.

---

### Slice 2 — Grand Concert Finale Race Catalog Correction

**Status: COMPLETE**

#### Scope

Correct the Grand Concert finale race catalog scenario key:

```text
final_live => our_grand_concert
```

instead of the incorrect:

```text
final_live => grand_concert
```

Verify the resulting catalog identity and ensure there is one valid finale row for the scenario.

#### Additional work

- Investigated the orphaned engine-owned catalog row.
- Backed up the row before removal.
- Verified it was not referenced by race entries.
- Removed only the incorrect duplicate/orphan.
- Revalidated the race planner's scenario-scoped lookup.

#### Outcome

Grand Concert finale catalog data is correctly associated with `our_grand_concert`.

Commit:

```text
480b711716b0756443a3f5e3aeac3a7f879e3abc
fix(parser): correct Grand Concert final scenario key to our_grand_concert (KI-71)
```

---

### Slice 3 — Performance Domain Foundation

**Status: COMPLETE**

Establish Performance as a typed scenario observation without inventing a Performance balance system.

#### Authoritative Global terminology

The verified Global client terms are:

- Dance
- Passion
- Vocals
- Visuals
- Composure

Do **not** substitute the GameTora/JP-derived term "Mental".

#### Architecture

Implemented:

```text
app/Enums/PerformanceType.php
app/Models/TurnEvents/PerformancePayload.php
app/Models/TurnEvent.php
tests/Feature/PerformanceFoundationTest.php
```

Payload:

```json
{
  "type": "Dance",
  "delta": 12
}
```

`delta` is signed:

- positive = Performance acquired;
- negative = Performance spent.

#### Explicitly not implemented

- starting values;
- current totals;
- caps;
- acquisition formulas;
- spending costs;
- conversion formulas;
- Lesson costs;
- Song costs;
- Hype;
- Live Bonus;
- stat mappings.

Performance TurnEvents are observations, not an absolute balance.

---

### Slice 4 — Performance Turn-Write Integration

**Status: COMPLETE**

Wire Performance observations into the canonical turn-writing lifecycle.

#### Scope

Extend the existing:

```text
runs.turns.store
```

path rather than creating a Performance endpoint.

Use:

```text
performance[type]
performance[delta]
```

and persist the observation as the existing Scenario TurnEvent type.

#### Constraints

- Existing turn transaction remains authoritative.
- No scenario-specific duplicate write path.
- No automatic calculation.
- Numeric strings from browser forms remain valid and are cast appropriately.
- Empty Performance pair produces no event.

---

### Slice 5 — Performance Read-Side Integration

**Status: COMPLETE**

Expose recorded Performance observations through the canonical `TrainingRun` read path.

Implemented:

```text
TrainingRun::performanceObservations()
```

Output is deterministic and contains:

```text
turn
event_id
type
delta
```

#### Explicit boundary

The method does **not**:

- sum Performance;
- derive a current Performance balance;
- infer starting values;
- calculate costs;
- calculate caps.

It exposes the recorded observations only.

---

### Slice 6 — Trainer-Facing Performance Observation Input

**Status: COMPLETE**

Add Performance observation input to the actual committing turn-entry surface.

#### Surface

```text
resources/js/pages/Runs/Show.vue
```

using the existing GuidedStep / turn-entry architecture.

#### Capability

Performance input is enabled through:

```text
performance_input => true
```

for `our_grand_concert`.

It is deliberately not a new panel.

#### UI

Fields:

- Performance type;
- signed Performance change.

Exact options:

- Dance
- Passion
- Vocals
- Visuals
- Composure

No default selection.

No invented min/max.

No calculated total.

#### User-facing semantics

Positive values represent Performance gained.

Negative values represent Performance spent.

The UI explicitly explains that the application does not maintain a Performance total because the available source does not establish starting values, training-turn payments, or Lesson costs.

#### Specification

`SCREEN_SPEC.md` updated for SCR-RUN-003.

#### Validation

Invalid terminology such as:

```text
Mental
Vocal
Visual
Dancing
dance
```

is rejected.

Zero and decimal values are rejected.

Browser-form numeric strings are accepted.

---

### Slice 7 — TrainingDetail Consistency Boundary

**Status: COMPLETE**

Determine whether:

```text
resources/js/pages/Career/TrainingDetail.vue
```

is an equivalent committing turn-entry surface.

#### Determination

It is **not**.

Although it submits to:

```text
runs.turns.store
```

it always submits:

```text
stage: preview
```

and does not reach the committing confirmation path.

Its own screen explicitly states that the turn is not written until confirmation occurs elsewhere.

#### Decision

Do **not** add Performance controls to TrainingDetail.

Performance remains on the actual committing Runs/Show turn-entry surface.

#### Regression protection

Added:

```text
tests/Feature/TrainingDetailPerformanceBoundaryTest.php
```

The tests establish that:

- TrainingDetail remains preview-only;
- no turn/event is written by its preview submission;
- Performance data can survive the preview hand-off;
- invalid Performance values are not silently dropped.

#### Specification

`SCREEN_SPEC.md` SCR-CAR-012 updated with the boundary decision and reopen condition.

---

## Phase B — Lessons

### Slice 8 — Lessons Evidence + Foundation

**Status: COMPLETE** (ran 2026-10-09 and was re-tested the same day; the gate closed with no Lesson representation
established, recorded in `SCREEN_SPEC.md` §7-19)

Establish exactly what the project can truthfully represent about Lessons.

#### First determine

- whether a canonical Lesson representation already exists;
- whether Lessons are observations, actions, or both;
- whether a Lesson has an established TurnEvent representation;
- whether Lesson categories/types are authoritative;
- whether costs are authoritative;
- whether Performance effects are authoritative;
- whether Hype/stat effects are authoritative;
- whether confirmation/reservation terminology is verified.

#### Important boundary

The Grand Concert documentation explicitly identifies some Lesson-related information as unverified.

Do **not** implement:

- unverified Lesson labels;
- cost curves;
- per-level magnitudes;
- inferred Performance gains;
- inferred Hype;
- inferred stat effects.

#### Possible outcome

If only a minimal Lesson observation is supported, implement only that foundation.

If no truthful representation is currently supported, document the boundary and add a regression test rather than inventing a Lesson system.

---

### Slice 9 — Lessons Turn Lifecycle

**Status: PLANNED**

Only if Slice 8 establishes a valid Lesson representation.

Integrate Lessons into the canonical turn/event lifecycle.

#### Preferred architecture

Reuse:

- existing TurnEvent infrastructure;
- canonical turn write path;
- `TrainingRun` read-side conventions;
- existing scenario capability/configuration.

#### Do not create

- Lesson table;
- standalone Lesson model;
- separate Lesson endpoint;
- duplicate turn-writing path;
- speculative calculation engine.

#### Tests

Verify:

- valid Lesson representation;
- invalid values;
- persistence;
- readback;
- deterministic ordering;
- no accidental total/balance calculation.

---

### Slice 10 — Lessons Trainer UI

**Status: PLANNED**

Only if authoritative evidence supports a truthful Trainer-facing Lesson interaction.

#### Preferred surface

The actual committing turn-entry surface.

Do not add Lesson controls to `TrainingDetail.vue`.

#### UI constraints

- exact verified terminology;
- no invented defaults;
- no unverified costs;
- no unverified magnitudes;
- no automatic Lesson selection;
- no hidden calculations.

If evidence does not support truthful UI, document the exclusion instead.

---

## Phase C — Songs

### Slice 11 — Songs Evidence + Foundation

**Status: BLOCKED BY EVIDENCE** (gate run 2026-10-09; the determination is in `SCREEN_SPEC.md` §7-21)

Establish the authoritative Song representation.

#### Outcome of the gate

**Songs — BLOCKED BY EVIDENCE.** The noun and the count are settled; nothing a field could carry is.

- **Settled:** **songs** is Cygames' own English (notice 905), so it needs no capture. The **23** and its
  availability segments are corroborated row for row by both publishers, and reference §7 row 49 treats the count
  and the segment gating as settled while leaving the naming open.
- **Not settled:** the language of the 23 titles (row 49, unresolved); the Performance type each cost figure
  belongs to (not recoverable from either page's text layer); any magnitude (no cost curve, no Hype per Song);
  whether acquiring a Song consumes a turn and what the client calls the action.
- **New measured negative:** no Song title appears in any committed data this repository holds. Of nine distinctive
  fragments from `docs/scenarios/07`'s title table grepped against `database/seeders/data/`, eight return 0 hits and
  the ninth, `Umadol`, matches only unrelated client skill and card names (`Rising to Umadol Stardom!`). The one
  local scenario JSON in the tree, `.scratch-uma/cited/static_scenarios.json`, is a gitignored GameTora scratch copy that
  no commit has ever touched and it carries no Song key. Row 49 is therefore not closable locally; it needs the
  `[Global]` Lesson-menu frame that gap §7-19 already names.
- **Smallest truthful representation:** already exists and is generic. `TurnEventType::Scenario` with
  `source_name` and nullable `deltas` carries "the Trainer says this happened" for any scenario event, so a Song
  needs no enum, table, column, route, flag or component. A `Song` enum would pick a language the client was never
  read stating; a Song count would state a timing no source publishes.
- **Absence is guarded by committed tests:** `tests/Feature/GrandConcertPanelTest.php:93` (payload) and
  `tests/browser/grand-concert-panel.spec.ts:98` (markup) both refuse a `song` string.
- **Reopen condition:** a `[Global]` capture of the Lesson menu. Nothing before that is implementable without
  inventing client text or a number.

#### Determine

- verified Song terminology;
- whether individual Song names are authoritative;
- whether Song acquisition is an observed event or action;
- whether Song spending/selection is represented;
- whether costs are verified;
- whether Song effects are verified;
- whether Hype effects are verified;
- whether Song timing is verified.

#### Explicit warning

The existing Grand Concert documentation identifies the **23 Song titles** and other Song-related details as unverified where applicable.

Do not render or encode unverified Song data merely because it appears in an external source.

---

### Slice 12 — Songs Lifecycle Integration

**Status: BLOCKED BY EVIDENCE** (gated on Slice 11, which closed the gate: no truthful Song representation exists to integrate)

If Slice 11 establishes a truthful representation:

- integrate Songs into the canonical turn/event lifecycle;
- provide a typed read path where justified;
- preserve observed-vs-derived separation;
- avoid calculating unsupported Song state.

No new standalone subsystem unless existing architecture genuinely requires one.

---

### Slice 13 — Songs Trainer UI

**Status: BLOCKED BY EVIDENCE** (gated on Slice 11; there is no verified Song interaction or status to display)

Add the minimum verified Song interaction/status information to the appropriate committing surface.

Do not expose:

- unverified Song costs;
- unverified effects;
- unsupported Hype calculations;
- speculative Song availability;
- automatic recommendations masquerading as game facts.

---

## Phase D — Live Systems

### Slice 14 — Live Bonus Evidence + Foundation

**Status: BLOCKED BY EVIDENCE** (gate run 2026-10-09; the determination is in `SCREEN_SPEC.md` §7-22)

Establish what Live Bonus state can actually be represented.

#### Outcome of the gate

**Live Bonuses — BLOCKED BY EVIDENCE.** The structure is the best-corroborated thing in this scenario and the
vocabulary is the worst-evidenced, and a field needs both.

- **Settled (tier A and B, not the client):** a Lesson pays two layers of reward and one is deferred. GameTora
  names both layers and their behaviour; Game8 independently describes the deferred activation ("Takes effect
  after concerts") while collapsing both under one heading, "Mastery Bonus". Two publishers that disagree on every
  noun agree on the shape. Reference §7 row 51 adopts the partition "for the mechanics" and adopts "neither name
  … for display".
- **Not settled:** every English word. The corpus carries **six renderings for three objects** — GameTora's
  Friendship Bonus / Speciality Rate Up / Support Event Chance Up against Game8's Friendship Training
  Effectiveness / Specialty Priority / Support Chain Event Frequency — and notice 905 "names neither bonus layer",
  which is what keeps row 51 open. Its "boosting training effects" sentence is about what a Lesson pays in
  general; reading it onto the deferred layer is inference.
- **Two measured negatives, both local.** In the app's own committed dictionary
  `database/seeders/data/support_effects.ca447e53.json` (all 35 rows read), `友情ボーナス` is id 1 with one English
  name, `得意率アップ` is id 19 with **two conflicting ones** (`name_en` "Specialty Priority", `name_en_eon`
  "Specialty Rate Up"), and `サポート連続イベント率アップ` has **no row at all** — so the divergence between the two
  guides is reproduced inside one file, and the third type of a three-type mechanic has no stored row. And
  "Friendship Bonus" is **already displayed copy** in this application, as a support-card effect name from that
  GameTora dictionary (`SupportEffect.php:17`, asserted by `SupportCardPageTest.php:85,93,253,261`), which is a
  trap rather than a settlement: different domain, no capture, and no source says the scenario's bonus and a
  card's effect are one counter.
- **The magnitude boundary is narrower than the shorthand in this slice said.** "No Live Bonus percentage ladder"
  is true, and "no number exists" is false: the **first-instance** value of each type is printed row for row by
  both publishers inside the Song table (`Friendship Bonus +5%` and `+10%`, `Speciality Rate Up +5`, `Support
  Event Chance Up +1`). What is absent is the level progression. Every one of those figures is third-party prose
  riding on a Song title whose language row 49 has not settled, so the standard `SCREEN_SPEC.md` §7-21 applied to
  Songs rejects them here too.
- **Single-source only, with no corroboration at all:** that a bonus persists for the entirety of the run, that it
  has levels raised by repeating the same bonus in another Lesson, and that automatically granted Songs do or do
  not pay one. GameTora's "Automatically gained songs also count" sentence is about the Hype gauge, not about
  bonuses.
- **Smallest truthful representation:** already exists, generic, and unchanged from Slices 8 and 11.
  `TurnEventType::Scenario` with `source_name` and nullable `deltas` records "I queued a Friendship Bonus this
  turn" today. A `LiveBonusType` enum would pick among six renderings the client was never read stating and would
  have to name a third case no committed row supports, so it fails the test `PerformanceType` passes. Percentage,
  level, duration and stacking fields, a current-bonus reader, and any Live Bonus → Song / Lesson /
  `support_effects` key are all unimplemented because unevidenced, not deferred.
- **Absence is guarded:** `tests/Feature/GrandConcertPanelTest.php:103`'s refusal list grew from five words to eight
  on this gate and now refuses `live bonus`, `practice bonus` and `mastery bonus` beside `song`, `lesson` and
  `hype`. No second structural file was added: `LessonEvidenceBoundaryTest.php:84` already pins that an
  unrecognised key on the turn write is collected as nothing and stored as nothing, and its structural refusals
  cover the same shape.
- **Reopen condition:** the same `[Global]` Lesson-menu capture gaps §7-19 and §7-21 name, and it owes this gate
  the layer label, each of the three type names as printed, and which type each Song row carries. One cheaper
  local route to try first, which does **not** close the gate alone: `docs/research-scratch/DESIGN-CORPUS.md:4261`
  records **Our Grand Concert: None found**, and the corpus's screen-type table (`:4269-4282`) documents no
  support-card effect list, only rail avatars, a Career Profile "Support Cards" row and an unread **Perks**
  button. Support effects are server-wide rather than scenario-specific, so an existing frame may already
  measure the client's word for `友情ボーナス` and `得意率アップ`. That settles two of six renderings for the
  support-effect domain only; a ladder needs no frame and has no source.

#### Determine

Superseded by the outcome above. The five questions this slice asked (terminology, observations versus derived
values, sources of existence, per-value authority, and whether a current total is representable) are each answered
in §7-22: terminology unmeasured, existence corroborated at tier B, no per-value authority, and no current total
derivable.

#### Explicit boundary

The existing documentation marks the **Live Bonus percentage ladder** as unverified. Confirmed and sharpened: the
ladder is unpublished while the first-instance values are published by both guides as third-party prose. Neither
is encodable.

Do not invent or encode it.


---

### Slice 15 — Live Bonus Lifecycle / Read Model

**Status: BLOCKED BY EVIDENCE** (gated on Slice 14, which closed the gate: no verified Live Bonus vocabulary or value exists to persist or read)

If Slice 14 establishes valid data:

- persist verified Live Bonus observations;
- expose them through canonical read-side architecture;
- preserve provenance;
- do not derive unsupported percentages.

No speculative balance/meter system.

---

### Slice 16 — Promotional Lives Evidence + Foundation

**Status: BLOCKED BY EVIDENCE** (gate run 2026-10-09; the determination is in `SCREEN_SPEC.md` §7-23)

Establish the authoritative representation and lifecycle boundary for Promotional Lives.

#### Naming correction first

The `[Global]` client calls these **Promo Concert**, not "Promotional Lives". Cygames' notice 905 prints "**Four
Promo Concerts and one Grand Concert** are held, making for a total of five concerts", and Game8 independently uses
"Promo Concerts" too. "Promotional Lives" is GameTora's rendering of the JP word, which makes this Phase's own
title guide vocabulary rather than client vocabulary — the one Grand Concert mechanic in the whole line whose noun
is already settled at tier S, and settled against the roadmap's name.

That is a naming fact, not an implementation licence, and this slice changes no code. `docs/UMAMUSUME_REFERENCE.md`
§2.9 ruled the same thing on the day of the primary read; the guards already carry the asymmetry
(`GrandConcertPanelTest.php:107` and `tests/browser/grand-concert-panel.spec.ts:98` refuse `promotional live`, and
nothing refuses `promo concert` because that word is licensed copy). **Whether Phase D is retitled "Promo
Concerts" is the owner's decision and is raised, not made.**

#### Outcome of the gate

**Promo Concerts — BLOCKED BY EVIDENCE.** Unlike Slices 8, 11 and 14, the blocker is not vocabulary. It is a
calendar position, a lifecycle, and a table that can hold either.

- **Settled at tier S:** the noun (**Promo Concert**, **Grand Concert**), the count (**four plus one, five
  concerts**), the cadence (**biannually, from the second half of December of the trainee's junior year**), the
  gauge's official name (**Live Performance Expectations**) and the max-gauge outcome (**Great Success**). No
  other Grand Concert mechanic has ever had this.
- **Not settled, and it is structural, not textual.** Neither timeline table can hold the series.
  `scenario_slots` has **no `year` column** and its live unique index is `(scenario_key, month, half, kind,
  source_key)`, so two Late-December concerts in different career years collide; `ScenarioSlot::VALID_KINDS` is
  `goal_race / team_race / grade_deadline / scripted_event / free_race` with no concert kind, and the model's
  `saving` guard throws on any other. `race_catalog_slots` does carry `year`, but it is race-shaped (non-null
  `grade_code`, plus `distance`, `surface`, `track_id`) and engine-owned from the GameTora instance export.
- **Measured negative:** searching every committed race name for Concert, Live, Promo or Grand returns **zero
  rows**. The catalog holds the scenario's finale (`final_live => our_grand_concert`, mandatory, the value KI-71
  corrected at `480b711`) and none of its four Promo Concerts — so the finale is a calendar row and the concerts
  are nothing.
- **Timing is not derivable without invention.** No source prints the (year, half) positions. GameTora's "before
  the 1st/2nd/3rd/4th Promo Live" segments gate **Song lesson unlocks**, not concert dates; Game8's per-year
  figures are the same unlock pattern. Placing the first concert needs this app's mapping of "junior year" to a
  career-year integer, which no source supplies.
- **Lifecycle unknown in every direction the app would need:** whether a concert occupies a turn (`docs/scenarios/07`
  states the turn shape of the live events was never published as a turn list), whether it can be declined or
  missed, and whether a confirmation, stage or result screen exists. GameTora's "you may choose to do lessons
  before stepping onto the stage … like you would learn skills before races" is an analogy for the *lesson* step,
  not evidence of a concert screen.
- **The outcome set is one word deep.** **Great Success** is official; `大成功` is the JP string; GameTora's "great
  or normal success" is its own English. No source names the other outcomes as printed or says how many tiers
  exist, so an outcome enum ships one licensed case and one inferred case.
- **Costs:** no source states a concert consumes Performance, and the one official relation runs the other way —
  gauge max *guarantees* a Great Success. Its subject is the still-unbuilt Live Performance Expectations concept,
  so the relation stands recorded and unmodelled.
- **Every payout is single-source:** 5 skill points per Technique Lesson and 25 per Song Lesson, and the +50
  resource-cap rise (GameTora); "each Promo Concert grants a stat increase" (Game8); "a Great Success raises stat
  caps" (GameTora); "the outcome of the Promo Lives feeds the finale" (GameTora, no mechanism named). The notice
  "publishes no number of any kind".
- **Smallest truthful representation:** already exists, generic. `TurnEventType::Scenario` with `source_name` and
  nullable `deltas` records "the second Promo Concert happened at turn N and was a Great Success" today, in words
  the Trainer supplies. The fourth consecutive gate to reach that finding.
- **Rejected as invention:** `PromotionalLiveType`, `PromotionalLivePayload`, a model or table, a state machine, a
  milestone scheduler, a cost or reward calculator, a `concert` kind in `scenario_slots` (that is a schema change,
  which §11 makes travel with a migration, an `ARCHITECTURE-ESSENTIALS.md` digest, a PRD citation and an ADR —
  spent backwards to hold a schedule no source pins down), synthetic `race_catalog_slots` rows for a non-race, and
  any Promo Concert → Song / Lesson / Live Bonus / Hype key.
- **Stale claim reported, not edited:** `database/migrations/2026_09_27_153416_create_scenario_slots_table.php:15`
  still says "Our Grand Concert has no guide at all", false since the 2026-10-05 read. Per `AGENTS.md` §2 the code
  wins and the rule is stale; a schema file's prose is not this slice's surface.
- **Reopen condition:** **two** captures, and one alone is not enough — a `[Global]` **pre-live / backstage screen**
  (the event's own name, the gauge label, whatever the client calls the action) **and** a `[Global]` **career
  calendar or goal row** showing where the four concerts sit, because only a screen settles a (year, half)
  position. Magnitudes need a source rather than a frame: no screen resolves 5 / 25 skill points or the +50.

#### Determine

Superseded by the outcome above. The five questions this slice asked are each answered in §7-23: what constitutes
it (a scheduled concert, official, but with no unplaceable-in-data date), what can be observed (the event and a
licensed outcome word), what can be entered (nothing the app may prefill), which outcomes are authoritative (one),
and what remains unverified (turn shape, interaction, costs, payouts, repeatability, the finale relation).

Do not assume mechanics from external/community sources.

---

### Slice 17 — Promotional Lives Lifecycle

**Status: BLOCKED BY EVIDENCE** (gated on Slice 16, which closed the gate: no Promotional Live representation exists and no table can hold the series without a schema change)

Integrate verified Promotional Live observations/results into the existing run lifecycle.

Reuse existing:

- TurnEvents;
- run state;
- read-side methods;
- capability configuration.

No duplicate subsystem.

---

### Slice 18 — Promotional Lives Trainer UI

**Status: BLOCKED BY EVIDENCE** (gated on Slice 16; there is no verified interaction, schedule or status to display, though the event's own noun is official)

Add the minimum truthful Trainer-facing Promotional Live interaction/status surface.

Do not expose speculative success formulas, Hype effects, bonus percentages, or rewards.

---

## Phase E — Grand Concert + Intelligence

### Slice 19 — Grand Concert Finale State Foundation

**Status: COMPLETE** (landed 2026-10-09 at `aa5d975`; the reasoning is in
`app/Services/Scenario/FinaleReader.php`'s docblock and `CockpitController::finaleAbsence()`, and the
cases are in `tests/Feature/FinaleAbsenceTest.php`)

What the slice asked for is settled by reading the catalogue rather than the config. The finale is a
scenario-scoped mandatory row in the career calendar, so `CockpitController::finaleAbsence()` answers
from that row instead of from the `config/scenarios.php` `finale` key, which stays undeclared because
no component reads its contents. Three answers, and the branch is driven by the row's presence rather
than by a scenario name, so a scenario name in the payload path would be a second source for the
layout path, which `AGENTS.md` §7 forbids.

- On the calendar: the absence sentence names the mandatory race and states that the scenario declares
  no structure beyond it.
- Not on the calendar: the sentence denies a finale, which is now true rather than a blanket claim.
- Declared structure: no absence sentence at all, because there is nothing absent.
- A row belonging to another scenario is ignored, so a shared catalogue cannot leak one scenario's
  finale into another's screen.

No finale state machine was built. The representation is the catalogue row's presence plus the run's
own reading of it, which is what Slice 20 reports.

---

### Slice 20 — Grand Concert Execution + Reporting

**Status: COMPLETE** (landed 2026-10-09 at `aa5d975`; `SCR-CAR-018`, cases in
`tests/Feature/FinaleReportingTest.php`)

The verified finale observations reach the existing read-only reporting path through `ResultController`
and `/run/{run}/result`, with no new endpoint and no speculative success calculation. One server-side
reader (`FinaleReader`) feeds both the Career Result and the Cockpit, so no two screens can disagree.

Three states, and the middle one is the one nothing could previously say:

- **Not yet reached.** `placement` and `turn` are null rather than zero or an empty string, because a
  screen that printed an empty placement cell for a finale nobody has run would state a finish that
  does not exist (D-220).
- **Reached with nothing recorded.** The position is read from the highest logged turn against the
  finale block, so one row is enough; no count is taken.
- **Recorded.** The run's own stored outcome word, placement and turn. The outcome set is one word
  deep: the run's status label, not a finale-specific vocabulary.

A scenario whose calendar carries no finale row emits no finale state at all, and the old `finale` key
is gone rather than left standing as a null, which is how a reader tells a scenario with no finale row
from a payload that never carried the key. The catalogue row's own title is not carried: it reads
"URA Finals Final (Grand Live)" and is filed as KI-82.

---

### Slice 21 — Scenario-Aware Trainer Advisor

**Status: COMPLETE** (landed 2026-10-09 at `ad8dc77`; `ScenarioAdvisorFinaleTest`, cases in tests/Feature/ScenarioAdvisorFinaleTest.php)

Only after underlying Grand Concert state exists.

Update Trainer Advisor so that it can consume verified Grand Concert-specific state.

#### Critical rule

Do not make Advisor scenario-aware by inventing scenario mechanics.

Advisor recommendations must distinguish:

```text
Observed fact
Derived fact
Recommendation
```

and maintain provenance.

---

### Slice 22 — Scenario Cockpit Integration

**Status: COMPLETE** (landed 2026-10-09 at `aa5d975`; `CockpitFinaleStripTest` and `CareerCockpitTest` pin the scenario-state surfaces, cases in tests/Feature/CockpitFinaleStripTest.php)

Expose verified Grand Concert state through the existing Career Cockpit/turn flow.

Respect existing:

```text
config/scenarios.php
```

capabilities and panel gates.

Do not turn every scenario mechanic into a new panel.

Use existing cockpit composition patterns.

---

## Phase F — Acceptance / Hardening

### Slice 23 — Cross-Feature Consistency Audit

**Status: COMPLETE** (`aa5d975`; see the Cross-Feature Consistency entry in the Current Status table, page 1125)

Audit the complete chain:

```text
Performance
    ↓
Lessons
    ↓
Songs
    ↓
Live Bonuses
    ↓
Promotional Lives
    ↓
Grand Concert
```

Verify that each feature:

- uses canonical architecture;
- has no duplicate state representation;
- preserves observed-vs-derived separation;
- has consistent TurnEvent behavior;
- has deterministic read-side behavior;
- has correct scenario capability gating;
- does not expose unverified mechanics;
- does not silently calculate unsupported totals;
- has appropriate tests;
- has matching SCREEN_SPEC documentation.

Only fix demonstrated inconsistencies.

No broad refactor for aesthetic reasons.

---

### Slice 24 — Full Grand Concert Acceptance Pass

**Status: COMPLETE** (see the acceptance record at 2026-10-09, below)

Perform the final scenario readiness assessment.

#### Verify

##### Scenario entry

- scenario registration;
- scenario selection;
- trainee selection;
- training target;
- inheritance;
- support deck;
- preflight.

##### Career lifecycle

- run creation;
- turn entry;
- Performance;
- Lessons;
- Songs;
- Live Bonuses;
- Promotional Lives;
- finale;
- Career Report;
- veteran/Legacy handoff.

##### UI

- capability visibility;
- panel visibility;
- turn-entry surfaces;
- validation;
- confirmation;
- readback;
- empty states;
- provenance/explanations.

##### Data integrity

- scenario-scoped race catalog;
- no duplicate finale;
- no speculative database state;
- no accidental canonical DB mutation.

##### Tests

Run the appropriate project testing levels according to `AGENTS.md §9`.

Only claim gates that were actually executed.

#### Acceptance record — 2026-10-09

The scenario is complete as far as current evidence permits. Every actionable criterion above is
verified against the passing feature tests on this tree, not against a remembered state:

- **Scenario entry** — `CareerScenarioSelectTest`, `CareerTraineeSelectTest`, `CareerBuildTargetTest`,
  `CareerInheritanceEventTest`, `CareerLegacyDeckStepsTest`, `CareerPreflightTest`: green.
- **Career lifecycle** — `CareerCockpitTest` (run creation, turn entry, correction, status/scenario/period
  writes), `CareerTrainingDetailTest` (Performance), `CareerResultTest` (Career Report),
  `CareerSaveVeteranTest` (veteran handoff): green.
- **UI** — `GrandConcertPanelTest` and `ScenarioPanelTest` (capability visibility, panel visibility,
  validation, readback, empty states, provenance): green.
- **Data integrity** — scenario-scoped race catalog and the finale reads are pinned by
  `CockpitFinaleStripTest`, `FinaleAbsenceTest`, `FinaleReportingTest`, `RunRaceStripTest`: green.
- **Blocked mechanics (Lessons, Songs, Live Bonuses, Promo Concerts)** — the acceptance is the absence,
  and it is stated: `config/scenarios.php:480`'s `panel_absence` ("The mechanics for this scenario are
  sourced, but no capture of the client's own screens exists yet, so no panel is drawn."), asserted by
  `GrandConcertPanelTest`. No surface invents a mechanic.

**Gates, as actually run.** `php artisan test --compact`: **8 failed, 2 skipped, 1489 passed (26155
assertions), 341.74s**. All eight failures are the F2 cutover's, not this scenario's: six in
`StatBandTest` (its `StatBand`/`GradeBadge` subject retired with `Runs/Show.vue`, the F2 close-out's
Delta C) and two in `TurnEntryFieldSourceTest` (stale assertions left by `1ec92ab`, the energy-state
change). `npm run typecheck` exit 0; `npm run build` exit 0; `composer lore` exit 0 (302 pre-existing
hits); `composer lore-code` exit 0 (118 pre-existing hits).

**Browser walk: not completed.** The harness cannot own its port: a leaked `php artisan serve` holds the
default `:8127` (KI-73), and the box was contended enough that the suite ran ~46s per case (KI-74), so
the run was voided as a signal rather than read. The failures seen before the void were all F2 cutover
leftovers — `career-cockpit.spec.ts`'s stale "run screen" link copy, `career-result.spec.ts`'s stale
`runs.show` status flow, and `run-detail.spec.ts` driving the redirected page — and the concurrent
session was repairing them in the working tree during this pass (`career-cockpit.spec.ts:114` and
`career-result.spec.ts:35` are already repointed, and `run-detail.spec.ts` is staged for deletion).

*Corrected 2026-10-10.* The sentence above that these eight failures were "repaired by a concurrent session during the pass" is superseded. Of the eight, **six were in `StatBandTest`** and were **retired** under the owner's ruling R-A1 (the subject, `GradeBadge.vue` and `StatBand.vue`, was deleted by F2's Delta C cleanup). The remaining **two were in `TurnEntryFieldSourceTest`** and were **adapted by F2** in a later commit after `1ec92ab` changed `TrainingDetail.vue`'s import. All eight were F2's to close; F2 closed them outside the Grand Concert line. The Grand Concert acceptance did not see them repaired mid-run.

---

## Global Out-of-Scope Rules

These rules apply to every remaining slice unless a future slice explicitly establishes verified evidence for them.

Do not:

- invent game mechanics;
- invent numeric costs;
- invent starting values;
- invent caps;
- invent Hype formulas;
- invent Live Bonus percentages;
- invent Song effects;
- invent Lesson effects;
- infer unsupported Performance conversions;
- create speculative recommendation logic;
- create duplicate models/tables;
- create duplicate endpoints;
- create a second turn-writing path;
- add unnecessary global state;
- introduce a new frontend framework;
- perform broad refactors;
- redesign unrelated screens;
- modify unrelated concurrent work.

---

## Repository Safety Constraints

The following areas are protected and must not be modified by scenario slices unless explicitly authorized by a separate task:

```text
config/database-safety.php
app/Services/DatabaseSafety/
tests/browser/global-setup.ts
playwright.config.ts
.scratch-uma/incident-2026-10-08-devdb/
.scratch-uma/db-recovery-2026-10-08/
composer.json
composer.lock
D:/Projects/uma_musume_race_planner/
```

Never run destructive database commands against the canonical database.

Do not use the canonical development database as a disposable test database.

Do not commit changes unless a separate instruction explicitly authorizes a commit.

Do not clean or revert unrelated concurrent work.

---

## Slice Completion Contract

Every slice must end with a report containing:

1. **Determination**
2. **Evidence**
3. **Files changed**
4. **Architecture decision**
5. **UI behavior**
6. **Mechanics explicitly not implemented**
7. **Tests and gates actually run**
8. **Safety/concurrency confirmation**
9. **Git/commit status**
10. **Explicit stop**

An agent must stop after its assigned slice.

A later slice may be started only after the previous slice has been reviewed/accepted.

---

## Current Status

```text
Phase A — Performance
  Slice 1  Baseline audit                         COMPLETE
  Slice 2  Finale race catalog correction        COMPLETE
  Slice 3  Performance foundation                 COMPLETE
  Slice 4  Performance turn-write                COMPLETE
  Slice 5  Performance read-side                 COMPLETE
  Slice 6  Performance Trainer input              COMPLETE
  Slice 7  TrainingDetail boundary                COMPLETE

Phase B — Lessons
  Slice 8  Evidence + foundation                  COMPLETE (gate closed: no Lesson representation exists)
  Slice 9  Turn lifecycle                         BLOCKED BY EVIDENCE
  Slice 10 Trainer UI                             BLOCKED BY EVIDENCE

Phase C — Songs
  Slice 11 Evidence + foundation                  BLOCKED BY EVIDENCE
  Slice 12 Lifecycle integration                  BLOCKED BY EVIDENCE
  Slice 13 Trainer UI                             BLOCKED BY EVIDENCE

Phase D — Live systems
  Slice 14 Live Bonus foundation                  BLOCKED BY EVIDENCE
  Slice 15 Live Bonus lifecycle                   BLOCKED BY EVIDENCE
  Slice 16 Promotional Lives foundation          BLOCKED BY EVIDENCE (client noun is Promo Concert)
  Slice 17 Promotional Lives lifecycle            BLOCKED BY EVIDENCE
  Slice 18 Promotional Lives UI                  BLOCKED BY EVIDENCE

Phase E — Grand Concert + Intelligence
  Slice 19 Finale state foundation                COMPLETE
  Slice 20 Finale execution/reporting             COMPLETE
  Slice 21 Scenario-aware Advisor                 COMPLETE
  Slice 22 Scenario cockpit integration           COMPLETE

Phase F — Acceptance
  Slice 23 Cross-feature consistency audit        COMPLETE (aa5d975)
  Slice 24 Full Grand Concert acceptance          COMPLETE (see the acceptance record, 2026-10-09)
```

### Definition of "Complete"

Our Grand Concert is considered implementation-complete only when the existing Trainer Desk architecture can truthfully represent and expose the verified scenario lifecycle from career setup through Grand Concert completion, while clearly distinguishing:

```text
Observed Game State
        ↓
Derived State
        ↓
Recommendation
```

and without presenting unsupported game mechanics as facts.

**Completion does not mean implementing every mechanic found on external wikis.**

It means implementing everything the project's authoritative evidence supports, documenting what remains unknown, and ensuring the application never fabricates certainty where the underlying game data has not been verified.

---

## Trackblazer-plan.md

> **Augmented 2026-10-10 (Round 14, augment-only).** Source: `docs/Trackblazer-plan.md` (583 lines), reproduced verbatim below with headings demoted one level. The source file remains in place; no citation was repointed and nothing was deleted.

## Trackblazer — Implementation Plan

**Project:** Trainer Desk 2.0  
**Scenario:** Trackblazer (Make a New Track / MANT)  
**Purpose:** Spec the Trackblazer career cockpit, the screens that differ from Grand Concert, and the pre-run planning flow.

---

### Sources

- `docs/UMAMUSUME_REFERENCE.md` (sections 1.3.4, 1.4.8, 2.2.3, 2.5, 2.7)
- [`GameTora, Trackblazer Scenario`](https://gametora.com/umamusume/trackblazer) (last updated 2026-03-12)
- [`uma.guide, Trackblazer Guide`](https://uma.guide/guides/trackblazer) (last updated Apr 29, 2026)
- [`Game8, Trackblazer Scenario Guide`](https://game8.co/games/Umamusume-Pretty-Derby/archives/580723) (returned pre-release copy)
- `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` sections "Trackblazer (uma.guide)" and "Trackblazer (GameTora)"

---

### 1. The Central UX Concept

Grand Concert is a conversion loop: training makes Performance, Lessons turn it into Songs, Songs raise Hype. Trackblazer is an economy loop with a hard deadline. The persistent question changes to:

**Race, train, or rest this turn, and does it keep me alive at the next Grade Point checkpoint?**

About half of all turns are races, and a typical career runs 30 to 40 of them. That is why the race decision, not the training decision, should lead the cockpit.

```
   RACE ──► placement ──► Grade Points + Shop Coins + stats/SP/fans
    ▲                          │                 │
    │                          ▼                 ▼
    │                   CHECKPOINT          SPECIAL SHOP
    │                  (60 / 300 / 300)          │
    │                   miss = run ends          ▼
    │                                    ITEMS (energy, training
    │                                    boosts, race bonus, ...)
    │                                            │
    └──────── stronger races / training ◄────────┘

   EPITHMET ROUTES: win every race in a route = bonus stats/hints
   FINALE: Twinkle Star Climax (3 races, Victory Points)
```

Three meters stay on screen all run: Grade Point runway, Coin bank, and Race fatigue. They play the role Performance played in the Grand Concert cockpit.

---

### 2. Scenario Select

The scenario card should show what makes Trackblazer different. It also loads the rules engine.

```
TRACKBLAZER
Race-driven career. Grade Points replace career goals.

Stat caps   SPD 1200  STA 1900  POW 1200  GUT 1200  WIT 1500
No career goals  •  No secret events  •  No Scenario Link
```

The caps are the figures in the reference, and whether they are live on Global today is still open (section 15). The engine loads these tables, each with a source badge:

- Grade Point objectives, by checkpoint profile
- Grade Points per race grade, and the placement modifiers
- Shop Coins by placement
- The shop catalog
- Race-fatigue odds
- Epithet routes
- Year-end unique-skill gates
- Finale rules

---

### 3. Trainee & Target

Trackblazer needs four checks that the Grand Concert flow did not.

**Checkpoint profile.** Both sources agree on three of the numbers, so default to the standard profile and let the Trainer override it:

- Standard: 60 / 300 / 300.
- High-dirt, low-turf trainees (Haru Urara is the example both sources give): 30 / 200 / 300.
- Short-distance turf trainees (Curren Chan is GameTora's example): sources disagree on the first number (section 15).

Show the profile as editable, with a source badge, and tell the Trainer to confirm it against the in-game objectives screen.

`config/scenarios.php` carries all three tracks (`standard` / `dirt_leaning` / `limited_turf_range`); `TrainingRun::gradeObjectives()` renders only `standard` and says so, because a wrong denominator is worse than a conservative one. Tracked as **KI-15**, OPEN.

**Race coverage.** Mile and Medium dominate graded racing. By uma.guide's counts, 24 of 30 unique G1s, 25 of 36 G2s and 50 of 69 G3s are Mile or Medium. The guide says to bring at least a C, ideally a B, in both. So show a coverage number:

```
RACE COVERAGE
Graded races this trainee can run at C or better:
G1  21 / 30    G2  22 / 36    G3  44 / 69
⚠ Weak Medium aptitude: 14 G1s are Medium
```

The values above are illustrative. The real numbers come from the aptitude data joined to the race list.

**Secret-event warnings.** Secret events and career goals are disabled. Both guides give the same example: Silence Suzuka cannot get Runaway here. If the Trainer's target relies on a secret event, flag it before the run starts.

`docs/UMAMUSUME_REFERENCE.md` §2.2.3 confirms: "Character secret events do not fire here, which is the mechanical reason a character cannot unlock an extra strategy option in this scenario."

**Cap-aware targets.** Speed, Power and Guts cap at 1200, Stamina at 1900, Wit at 1500. The target screen should show headroom per stat, so a Trainer does not aim Speed at 1400.

---

### 4. Legacy Lab (Deltas Only)

- No Scenario Link badge, because Trackblazer has zero linked characters. `docs/UMAMUSUME_REFERENCE.md` §2.2.3: "this scenario has zero linked characters, the only one of the fourteen with none."
- The scenario factor is **"Climax Scenario"**, which gives Stamina and Guts on inheritance. `docs/UMAMUSUME_REFERENCE.md` §2.7: "The Trackblazer scenario factor is printed as 'Climax Scenario' and grants Stamina and Guts bonuses on successful inheritance."
- Only show cap-raise projections where the data supports them. The cap-breakthrough rule in the reference is JP-tagged. The Global 2026-07-01 rework raises caps above the 1200 base; whether the raised Trackblazer caps (1900 Stamina, 1500 Wit) are live on Global today is open (section 15).

---

### 5. Support Deck Builder (Deltas)

Deck effects you evaluate:

- Race Bonus (effect id 15). uma.guide says to stay at or above 50. Draw a meter with a marker at 50, labelled as a community guideline and not a game rule.
- Fan Bonus (16).
- Skill Point Bonus (30).
- Failure Protection (27).
- Energy Cost Reduction (28).
- Bond coverage.

Archetypes are suggestions, not rules. The guide describes two frameworks:

- Speed + Wit: 2 Speed and 2 Wit, with the remaining slots flexed for skills or Stamina.
- Wit + Guts: 3 Guts and 2 Wit, leaning on Guts Ankle Weights. It can cap Guts, but skills and Stamina are harder to source.

Detect which shape the deck resembles, but evaluate the real cards.

**Friendship is valued differently here.** uma.guide says a lone friendship training often isn't worth skipping a graded race, because race rewards are high and Trackblazer training bases are low. Base facility values match Unity Cup's (lower than URA Finale's): Speed +8/+4/+2 SP, Stamina +7/+3/+2 SP, Power +4/+6/+2 SP, Guts +3/+3/+6/+2 SP, Wit +2/+6/+3 SP (energy costs −19 to −20, Wit +5). The Advisor should compare friendship value against race value instead of assuming friendship wins.

Source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 260-268 and 1276-1280.

---

### 6. Route Planner (New, and the Key Pre-Run Screen)

This takes the place the Concert Planner held in the Grand Concert flow. The route is a plan, not a script, and both guides say to deviate when a training turn is exceptional.

```
ROUTE PLAN                             Rice Shower • Standard profile

JUNIOR         ──●debut───────────────────●60 GP checkpoint──
 GP this period   0 / 60   planned: 100   floor (2nd-3rd): 52

CLASSIC        ─────[SUMMER]──────────────●300 GP checkpoint─
 GP this period   0 / 300  planned: 340   floor: 214

SENIOR         ─────[SUMMER]──────────────●300 GP─[FINALE]───
 GP this period   0 / 300  planned: 320   floor: 190

EPITHMET ROUTES IN PLAN
 Oka Sho / Oaks / Shuka Sho line    1 of 3 planned   ⚠ 2 races unscheduled
 Spring Champion                    0 of 3
 Mile route                         2 of 3

FLAGS
 ⚠ 5 consecutive races planned in Classic spring (fatigue risk)
 ⚠ Year-end gate, Classic: 60,000 fans + Akikawa bond 31
 ○ Rival races are random; they cannot be scheduled
```

The numbers above are illustrative. What is sourced:

- Grade Points for a win: G1 100, G2 80, G3 60, OP 40, Pre-OP 20. Source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 92-100.
- Placement modifiers (uma.guide, single source): 1st 100%, 2nd 60%, 3rd 40%, 4th-5th 20%, 6th and lower 10%. Source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 102-110.
- Points never carry over. Each period starts at zero. Source: both publishers, confirmed in `docs/UMAMUSUME_REFERENCE.md` §2.2.3.

So the planner can show three numbers per period: needed, planned if you win, and a floor if you finish 2nd to 3rd. A G2 win is 80 and a G2 second place is 48.

---

### 7. Run Preflight

```
RUN PREFLIGHT

TRAINEE   Rice Shower     SCENARIO  Trackblazer
PROFILE   Standard 60 / 300 / 300 (confirm in game)

CHECKPOINTS
 ✓ Junior 60 GP reachable (plan 100, floor 52)
 ⚠ Floor is under target: needs 1+ win
 ✓ Classic / Senior reachable

RACE COVERAGE      ✓ Mile B  ✓ Medium A  ○ Long A
DECK               ✓ Race Bonus 56%   ⚠ Stamina coverage low
ROUTES             ✓ 3 epithet routes planned
YEAR-END GATES     ⚠ Akikawa bond plan needed (19 / 31 / 51)
SECRET EVENTS      ✓ Plan does not depend on any

RULE CONFIDENCE
 GP table, coin table, shop    community-sourced
 Checkpoint profile            confirm in game

[ START CAREER ]
```

---

### 8. The Career Cockpit

```
┌─────────────────────────────────────────────────────────────────────┐
│ TRACKBLAZER                          CLASSIC • TURN 31 • Energy 54  │
├─────────────────────────────────────────────────────────────────────┤
│ GRADE POINT RUNWAY            period needs 300                      │
│ ███████████░░░░░░░░░  172 / 300    128 short • 9 turns left        │
│ Races that fit: 2 G2 wins + 1 G3 win ≈ 220                          │
├─────────────────────────────────────────────────────────────────────┤
│ COINS  210       SHOP refresh in 3 turns      ITEMS 7 (cap 5/item) │
│ RACE FATIGUE  2 consecutive races  → next race: see table          │
├─────────────────────────────────────────────────────────────────────┤
│ ★ RECOMMENDED: RACE (G2, mile)                                      │
│                                                                     │
│ 1st  +80 GP  +100 coins   2nd  +48 GP  +60 coins   3rd  +32 GP  +60 │
│                                                                     │
│ WHY                                                                 │
│ You are 128 GP short with 9 turns left. A G2 win is worth 80.       │
│ Training Speed here gains less than the checkpoint risk costs.      │
│ ⚠ A third consecutive race raises fatigue risk (60% Mood Down).     │
│                                                                     │
│ [ RACE ]                                                            │
│                                                                     │
│ Alternatives                                                        │
│ ○ Train Wit: safer, +energy, does not move the runway               │
│ ○ Rest: clears the race chain, loses a turn of GP                   │
│ ○ Use Good-Luck Charm, then train Power: 0% failure for 1 turn      │
└─────────────────────────────────────────────────────────────────────┘
```

Every number in that wireframe is illustrative. The tie to sources:

- 80 GP and 100 coins are the G2 first-place values. Source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 92-99 and 116-121.
- 48 GP is 80 × 60%. Source: line 107.
- The fatigue figure comes from the table in section 10.

Two design points:

- The Race Desk should show the same breakdown for any race the Trainer picks. I would not display an exact placement probability, and I would show the 1st, 2nd and 3rd branches instead.
- After a period's threshold is met, further Grade Points do not help the checkpoint. The Advisor should say so ("checkpoint met; this race is now worth coins, stats and epithet progress only"). Races still pay coins, stats and fans after that.

---

### 9. Advisor Priority Hierarchy

1. Don't end the run. This is Grade Point feasibility at the next checkpoint, plus the debut race (which costs energy here).
2. Don't wreck the run. This covers energy floors and race fatigue.
3. Route integrity. An epithet needs every race in its route won, so a skipped race can break a route.
4. Economy. This is the coin bank, the shop rotation, the Summer stockpile and the finale reserve.
5. Training target.
6. Optimization.

The explanation shape stays the same: recommendation, reason, alternatives. For example, "Speed gives the most raw stats, but a race is better now because you are 128 GP short."

---

### 10. Race Fatigue Panel

Consecutive races matter more here than in any other scenario. uma.guide publishes the odds of the fatigue event by consecutive-race count. Show the row for the current count:

```
RACE FATIGUE          chain so far: 2

                       1 race   2 races   3 races   4+ races
Mood Down (1+ energy)    0%       0%        60%       100%
Mood Down (0 energy)    15%      33%       90%+      100%
Skin Outbreak (1+)       0%       0%        15%        33%
Skin Outbreak (0)        4%       8%        25%        33%
3 random stats -10 (any)  0%       0%         0%        40%

Cannot occur after late December.
Guaranteed recovery events: Classic early Feb (+1 mood), early Mar (+20
energy), late Sep (+1 mood); Senior late Jun (+20 energy), late Oct (+1
mood), late Dec (+30 energy).
```

Source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 229-242.

Items mitigate this, so the Advisor should factor in what the Trainer is holding. The guide says to race without fear if you hold the right items. Two caveats:

1. The guide does not say whether "3 races" counts the upcoming race or the ones already run. Show the exact row being read, and flag the convention as unverified.
2. "After late December" does not say which year.

---

### 11. Shop Drawer (In the Cockpit, Not a Separate Page)

The shop is used almost every turn, so it should be a drawer in the cockpit. Display the rules the game imposes:

- The shop is locked until the Debut race. (`SCENARIO-PUBLISHER-REFERENCES.md` line 374)
- It refreshes every 6 turns. (line 375)
- A maximum of 5 copies per item. (line 376)
- Race-triggered items last 3 turns.
- Sales are 10% to 20%. (line 379)
- Multi-turn items cannot be used while still active. (line 380)
- A lower-grade item used before a higher one is wasted, and the reverse is not possible. (line 380)

```
SHOP                                coins 210   refresh in 3 turns

★ ADVISOR      Keep 100+ coins: Summer starts in 6 turns and the shop resets then

Master Cleat Hammer    40   +35% race bonus, 1 turn     held 1/5
Empowering Megaphone   70   +60% training, 2 turns      held 2/5
Reset Whistle          20   shuffle support cards       held 0/5
Good-Luck Charm        40   0% failure, 1 turn          held 1/5
Vita 65                75   +65 energy                  held 0/5

Community tags (uma.guide, opinion): Must Buy / Skip
```

Keep community opinions visibly separate from game rules. Items you will want to track:

- Training Applications (150 coins) permanently raise a facility level for the run. (line 433)
- Grilled Carrots (40) give +5 bond to all supports. (line 411)
- Yummy Cat Food (10) gives +5 Akikawa bond. uma.guide says to skip it (line 317), but section 14 gives a case where it matters.
- Scholar's Hat (280 coins) — "the equivalent of 3 first-place wins" — grants the Fast Learner discount on skill hints. (line 443)
- Stat books at +7 or +15, avoid the +3 ones — recommended purchase tiers. (line 444)
- Royal Kale Juice restores 100 Energy at the cost of 1 Mood; the mood debuff is fixed with a Cupcake. (line 445)
- Cleat Hammers do not stack: Master Cleat Hammer used after Artisan Cleat Hammer gives only the 35% from the Master, not a combined 55%. (line 447)
- Empowering Megaphone (60% training boost, 2 turns) overwrites an earlier Coaching Megaphone. (line 446)

Reserve rules from uma.guide, shown as guidance:

- Keep at least 100 coins entering Summer. (line 271)
- Stock two Megaphones for the four Summer turns. (line 275)
- Save about 150 coins for the final shop. (line 297)
- Save three Gold Hammers for the finale. (line 296)

Full item list source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 383-453; Summer Camp window: line 1225; cleat hammer stacking and Empowering Megaphone overwrite: lines 446-447.

---

### 12. Epithet Tracker

Epithets pay random stats, and some give hints. Examples from uma.guide:

- Lady: win the Oka Sho, Japanese Oaks and Shuka Sho, for 2 random stats +10.
- Stunning: the Satsuki Sho, Japanese Derby and Kikuka Sho line, for +10.
- Goddess: Lady plus three more races, for +15.
- The dirt epithets: win 5, 10 or 15 dirt races, and 3, 4, 5 or 9 dirt G1s.

Source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 125-176 (epithet tables).

Show each route as a chain: won, remaining, and whether it is still alive. If a race in a route is lost, grey the whole route out and say so.

**Summer Camp.** Early July to Late August, in both Classic and Senior Years, training facilities reach temporary Level 5. This is the highest-value training window for the scenario, and cleat hammers / megaphones reserved for it pay the most. Source: `SCENARIO-PUBLISHER-REFERENCES.md` line 1225 (`[Both]`).

---

### 13. Year-End Gate (Unique Skill Level-Up)

The gate is separate from Grade Points. In late December of each year the game picks the "Umamusume of the Year". uma.guide gives numeric thresholds:

- Junior: 5,000 fans and Akikawa bond 19.
- Classic: 60,000 fans and bond 31.
- Senior: 120,000 fans and bond 51.

GameTora describes the same gate as bar colors (blue 1 bar, blue 2 bars, green 3 bars) and says the fan and win requirements are unclear. The two are consistent if one bar is about 20 points. Treat the uma.guide numbers as single-source.

```
YEAR-END GATE  Classic, late December
Fans         41,200 / 60,000     ⚠ 18,800 short
Akikawa bond     24 / 31         ⚠ 7 short
```

Source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 300-308.

If bond is the shortfall, the Advisor can note that Yummy Cat Food costs 10 coins for +5. That is a derivation from the item list (line 410). uma.guide itself says to skip the item (line 317), so label the recommendation as derived, not sourced.

---

### 14. Twinkle Star Climax Briefing

The finale is three races and three training turns. It is a mini-league, not an elimination bracket. Victory Points by placement:

- 1st 10, 2nd 8, 3rd 6, 4th 4, 5th-6th 3, 7th-9th 2, 10th-13th 1, 14th and lower 0.
- The maximum is 30, and the top total wins.

```
TWINKLE STAR CLIMAX PREFLIGHT

FINAL STATS      SPD 1180  STA 1620  POW 1090  GUT 880  WIT 1210
VICTORY POINTS   0 / 30 possible
TRAINING TURNS   3  (hold a few training items)
HAMMERS HELD     Gold 3   ✓ recommended: 3
COINS            150      ✓ the finale races pay no coins
```

Per uma.guide, each finale race gives a base +10 to all stats, which is why the hammers matter. Winning also gives a hint for the Radiant Star skill. GameTora says the finale's distance and terrain depend on the races you ran across the three years, but does not give the rule, so it would not be modeled.

Source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 279-298 and 460-478.

---

### 15. What I Could Not Confirm

1. **Short-distance turf checkpoints.** GameTora (page stamped 2026-03-12) says Curren Chan's profile is 60 / 200 / 300, with only the third objective reduced. uma.guide (2026-04-29) says sprinters with weak other aptitudes follow the dirt profile, 30 / 200 / 300. `config/scenarios.php` carries all three tracks (`standard` / `dirt_leaning` / `limited_turf_range`); `TrainingRun::gradeObjectives()` renders only `standard` and says so, because a wrong denominator is worse than a conservative one. Keep the profile editable and confirm in game. KI-15, OPEN.

2. **Stat caps on Global.** RESOLVED. The GameTora data export `scenarios.json` (fetched 2026-09-27, after the 2026-07-01 update) carries Trackblazer `stats: [0, 700, 0, 0, 300]` over the 1200 base, confirming 1200 / 1900 / 1200 / 1200 / 1500 on Global. The config `cap_bonus` matches exactly, with `live_on_global` set to `2026-03-12`.

3. **Names.** GameTora calls it the "Special Shop", and uma.guide calls it the "Coin Shop" or "Climax Store". Do not hard-code a display name until it is read from the client.

4. **Placement modifiers.** RESOLVED. The 100/60/40/20/10% curve is confirmed `[Both]` — uma.guide prints it as a labeled table and GameTora records the same modifiers in prose ("proportionally lower"). Source: `SCENARIO-PUBLISHER-REFERENCES.md` lines 104-110.

5. **Race stat table.** uma.guide's per-grade table gives base figures with Race Bonus and hammer scaling. The page excerpt does not say how the stat figure is split across stats, so treat the unit as unclear until checked.

6. **Alarm Clock retries.** PARTIALLY RESOLVED. SCENARIO-PUBLISHER-REFERENCES.md:1223 confirms the per-career cap is **5 Alarm Clocks** ("the item that re-runs a race you failed to win, up to 5 per career"), not three per run as the wiki stated. The resume point remains unconfirmed: the wording is *that race*, not a period start (UMAMUSUME_REFERENCE.md §2.2.3). Which turn the retry resumes on is unstated by any source.

7. **Finale selection rule, and which year "after late December" means in the fatigue rule.**

What I would not build:

- A fixed "best route" that plays itself. Trainer decides, assistant advises.
- A placement-probability number. The game does not publish one.
- A hard-coded shop tier list presented as a game rule.

---

### MVP Screen Set

1. Trainer Home
2. Trainee & Target
3. Legacy Lab
4. Support Deck Builder
5. Route Planner
6. Run Preflight
7. Career Cockpit (with the Race Desk and Shop drawer)
8. Checkpoint & Finale Briefing
9. Career Report / Veteran

#7 should again get most of the design effort. The route planner is the screen that has no equivalent in Grand Concert.

---

### 16. DECISION CONTRACT

Every recommendation the Advisor produces follows this shape — it must not be a bare action label:

1. **Current state and immediate objective.**
2. **Recommended action** and expected benefit (with numbers).
3. **Best alternative** and its trade-off.
4. **Confidence** — verified, derived, configurable, or unknown — with the source badge.
5. **Conditions that would change the recommendation.**

Example:

> **★ RECOMMENDED: RACE (G2, Mile)**
>
> You need 128 GP this period with 9 turns left. A G2 win is worth 80; the floor (2nd–3rd) is 48.
>
> - **Race G2 Mile:** +80 GP (1st), +100 coins, advances route progress.
> - **Alternative — train Speed:** +stats but 0 GP; checkpoint risk remains.
> - **Confidence: verified** (GP values, `SCENARIO-PUBLISHER-REFERENCES.md` lines 92–99).
> - **Switch if:** fatigue chain hits 3 (60% Mood Down), or a Summer Camp turn opens (bank coins first).

---

### 17. RACE CALENDAR PANEL

Trackblazer is race-driven, so the race calendar deserves a dedicated panel in the cockpit — not just the route planner's period view.

```
RACE CALENDAR                Classic • Turn 31

UPCOMING RACES                             DISTANCE / SURFACE
• G2 Mile (Satsuki Sho qualifier)           Mile / Turf
  → 4 turns                                Win: +80 GP, +100 coins
• G3 Sprint (fill-in)                       Sprint / Turf
  → 2 turns                                Win: +60 GP, +80 coins
• G1 Long (Derby lead-up)                   Long / Turf
  → 18 turns                               Win: +100 GP, +150 coins

CONSEQUENCES OF SKIPPING
• G2 Mile: +1 route gap → Spring Champion route at risk
• G3 Sprint: no impact on epithet progress
• G1 Long: -100 GP from runway, -150 coins from economy

ADVISOR: Race the G2 Mile now. You are 128 GP short with 9 turns left.
```

Source values: GP per grade from §6 (G1 100, G2 80, G3 60), placement modifiers from §6.

---

### 18. SCHEDULE RISK WARNINGS

The cockpit should surface five risk types as flags, each with a severity level:

| Risk                     | Trigger                              | Severity | Mitigation                                  |
| ------------------------ | ------------------------------------ | -------- | ------------------------------------------- |
| Race density             | 3+ consecutive races without rest    | High     | Insert rest turn or use recovery item       |
| Fitness deficit          | 2+ consecutive races at 1 energy      | Medium   | Hold Vita 65 or Good-Luck Charm             |
| GP shortfall             | Floor projection < checkpoint target  | High     | Race higher-grade or use Master Cleat       |
| Coin shortfall           | < 100 entering Summer                 | Medium   | Race an extra G2 before Summer              |
| Epithet decay            | Lost race in an active route          | High     | Grey the route; abandon or replan           |

Source: fatigue table §10, coin reserves §11, GP values §6, epithet rules §12, checkpoint tables SCENARIO-PUBLISHER-REFERENCES.md §7.

---

### 19. RESOURCE LEDGER

A persistent ledger tracks coins, items, and expected income/expense across the remaining career.

```
RESOURCE LEDGER                    Classic • Turn 31

COINS
  Current:          210
  Projected inflow: +180 (2 races)
  Projected cost:   -120 (2 Megaphones + Hammer)
  Summer reserve:   100 (target)

ITEMS
  Good-Luck Charm   1/5   ✓ hold for fatigue risk
  Master Cleat       1/5   ⚠ use before G1 Long (next race)
  Empowering Meg.    2/5   ✓ Summer stock: 2 held, need 2 more

ADVISOR: Hold 100+ coins: Summer starts in 6 turns and the shop resets then.
```

Rules: shop locked until Debut (§11), refresh every 6 turns (§11), 5 copies per item max (§11), Summer Camp window (§12).

---

### 20. ACCEPTANCE CRITERIA

| Feature                    | Success Criterion                                                                 | Edge Case                              |
| -------------------------- | --------------------------------------------------------------------------------- | ---------------------------------------- |
| Race Calendar panel        | Shows next 3 races, distances, GP/coin values, and skip consequences              | No eligible races in upcoming period    |
| Schedule risk warnings     | Flags fatigue chains, GP shortfalls, and coin shortfalls before they bite         | All risks resolved (empty flag set)     |
| Resource ledger            | Tracks coins + 5 key items, forecasts to Summer and Finale                       | Item held at 5/5 cap                    |
| Checkpoint projector       | Shows needed, planned (wins), and floor (2nd–3rd) for each GP period             | Current period already failed           |
| Route tracker              | Greys out routes when a race is lost, tracks remaining per route                  | Rival races random (cannot schedule)    |
| Recommendation reconciliation | Predicted outcome vs actual entry updates run state and flags deviation        | User edits state manually              |

---

### 21. VERIFICATION STATUS

Each feature carries one of three levels — never collapsed:

- **Implemented** — the code exists and the behavior is connected.
- **Verified** — automated tests or reproducible manual checks demonstrate the intended behavior.
- **Game-validated** — calculations have been compared against authoritative mechanics or observed in-game.

A page that renders is not verified. A verified calculation can still rest on an incorrect understanding of the game. This distinction is tracked per feature.

---

### 22. IMPLEMENTATION AND TESTING PLAN

| Phase                      | Deliverable                                                                     | Completion evidence                                              |
| -------------------------- | ------------------------------------------------------------------------------- | ---------------------------------------------------------------- |
| 0. Reconnaissance          | Inspect existing scenario code, plans, contracts, routes, data models and tests | Inventory of existing, partial, missing and conflicting behavior |
| 1. Rules audit             | Document verified scenario mechanics and unresolved assumptions                 | Source-backed rules and explicit unknowns                        |
| 2. State model             | Define run state, transitions, persistence and validation                       | Tests for valid and invalid state changes                        |
| 3. Decision engine         | Implement scenario-specific candidate evaluation and ranking                    | Deterministic tests using known inputs and expected outputs      |
| 4. Command center          | Build the primary screen around the next actionable decision                    | UI tests for loading, empty, error, stale and normal states      |
| 5. Reconciliation          | Compare predicted versus actual outcomes                                        | Tests for updating run state without losing history              |
| 6. End-to-end verification | Exercise complete careers and edge cases                                        | Reproducible scenarios, regression results and completion report |

**Trackblazer focus:** phase 3 builds the Grade Point rules engine (GP per grade, placement modifiers, coin payouts, fatigue odds). Phase 4 builds the Race Desk and Shop drawer. Phase 5 adds reconciliation for race outcomes (predicted GP vs. actual placement). Phase 6 validates checkpoint feasibility against in-game captures.

---

### 23. PRIORITY MATRIX

| Priority | Scope                    | Rationale                                                  |
| -------- | ------------------------ | ---------------------------------------------------------- |
| P0       | Canonical run state, GP rules, regression tests | Protects the trustworthiness of the race-driven scenario     |
| P1       | Next-action recommendations, deadline tracking, risk warnings | Turns Cockpit from display into a command center          |
| P2       | Resource forecasting, route optimization, matchup analysis | Materially affects coin and GP planning                   |
| P3       | Keyboard-friendly controls, richer historical analytics | Convenience only                                           |

---

### Note on Grand Concert Write-Up

The Grand Concert write-up uses GameTora's wording (Hype, Mental, Vocal and Visual). The reference's section 2.9 has Cygames' official Global copy for the same objects (Live Performance Expectations, Composure, Vocals, Visuals). That is worth a pass before it goes into a spec.

---

## Unity-Cup-plan.md

> **Augmented 2026-10-10 (Round 14, augment-only).** Source: `docs/Unity-Cup-plan.md` (561 lines), reproduced verbatim below with headings demoted one level. The source file remains in place; no citation was repointed and nothing was deleted.

## Unity Cup Plan

This document defines the Unity Cup scenario for Trainer Desk. It is written in the same architectural style as the Trackblazer plan: it assumes the shared cockpit frame, the three-layer model (Trainer Home, Career Cockpit, Advisor), decision cards, visible uncertainty, and the Veteran loop, all of which carry over unchanged. Only the Unity Cup-specific deltas are spelled out here.

### Sources

- Your `UMAMUSUME_REFERENCE.md` (sections 1.3.4, 2.1, 2.2.2, 2.5)
- [GameTora, Unity Cup Scenario](https://gametora.com/umamusume/unity-cup) (last updated 2026-07-20)
- [Game8, Unity Cup Guide](https://game8.co/games/Umamusume-Pretty-Derby/archives/545572) (returned pre-update copy)
- [uma.guide, Unity Cup Deckbuilding Guide](https://uma.guide/guides/unity-cup-deckbuilding-guide) (last updated Feb 2, 2026)
- [uma.guide, Special Scaling Skills](https://uma.guide/guides/skills-special-scaling)

Unity Cup changed a lot on Global on 2026-07-01. The Game8 page returned the pre-update values (older training bonuses, no Extreme Spirit Burst). GameTora (updated 2026-07-20) supplied every post-update number. Game8 and uma.guide supply the schedule, deck advice, and team-name data, which are tagged where they predate the update.

---

### 1. THE CENTRAL UX CONCEPT

Unity Cup is URA with a second thing to raise: your team. The persistent question becomes:

> Which training turn builds the most team and trainee value, and should I cash a Spirit Burst now or bank it?

Facility levels no longer rise with use. They are set by your team's stat rank for that training type (F/G = Lv1, D/E = Lv2, B/C = Lv3, A = Lv4, S = Lv5). So training with teammates is also how you raise your own training bonuses.

```
   TRAIN WITH TEAMMATES (white flames)
        │
        ├─► spirit gauge fills ──► SPIRIT BURST ──► big member stats, trainee stats,
        │                                            skill hint
        │                              │
        │                              └─► EXTREME BURST (once per member, after
        │                                   normal): 0% failure, raises member caps,
        │                                   Ignited Spirit hint
        ▼
   TEAM STAT RANKS rise ──► FACILITY LEVELS rise ──► better training
        │
        ▼
   TEAM RACES every 6 months (5 races vs an NPC team) ──► league rank, stats
        │
        ▼
   ZENITH FINALS ──► then the three URA Finals races
```

The race calendar and career goals are the URA ones. The "extra sprinkle" is entirely the team layer.

The defining design principle: the Advisor must compare two kinds of value — what a training action gives the trainee now, versus what it contributes to the team and to future training opportunities. The UI should make that trade-off legible, not just report the numbers.

---

### 2. SCENARIO SELECT

```
UNITY CUP
Team career. Spirit Bursts, team races, Zenith finals.

Stat caps   SPD 1300  STA 1300  POW 1300  GUT 1300  WIT 1800
Facility levels set by team rank, not by use
Scenario Link: Taiki Shuttle, Rice Shower, Haru Urara,
               Matikanefukukitaru, Riko Kashimoto
```

The cap figures match both the reference and GameTora's current page. The five linked names are from the reference's scenario data; GameTora's article names four story characters plus Riko as the linked Pal card.

---

### 3. TRAINEE & TARGET (DELTAS)

- Career goals and the fan gates are the URA ones, so keep that screen mostly as is.
- The unique-skill level-up gates are 60,000 fans by Valentine's Day, 70,000 by early April, and 120,000 by late December of Senior year. Dirt-leaning trainees (Haru Urara, Smart Falcon) use 40,000 / 60,000 / 80,000.
- Akikawa is absent here, so the April gate has no green-bond requirement.
- Add a Team Name picker, chosen in late September of Junior year. The options depend on which linked character is your trainee or in your deck. Winning the finals grants a gold skill by name (Game8's pre-update table):

| Team Name          | Linked Character        | Gold Skill Granted |
|--------------------|-------------------------|--------------------|
| Happy Hoppers      | Taiki Shuttle           | Mile Maven         |
| Sunny Runners      | Matikanefukukitaru      | Clairvoyance       |
| Carrot Pudding     | Haru Urara              | Indomitable        |
| Blue Bloom         | Rice Shower             | Cooldown           |
| Team Carrot        | (fallback)              | No Stopping Me!    |

- Show cap headroom per stat. Wit is the outlier at 1800.

---

### 4. LEGACY LAB (DELTAS)

- The scenario factor is "Unity Cup" and grants Power and Wit on inheritance.
- Beating the strengthened Zenith makes it easier to get the scenario factor, and it unlocks the "+" versions of the scenario skill factors.
- Linked characters get extra bonuses. Show them as a read-only list, since the badge is derived and not stored per card.

---

### 5. SUPPORT DECK BUILDER (DELTAS)

The team is built from your deck, so the deck screen should show which slots become teammates:

- Support cards (except Pals) become team members. They are the only members with a bond gauge, so they are the only ones that can do friendship training.
- Story characters join early, and random characters join after each team race. These have no gauge, but they can take part in Special Training and Spirit Bursts.

Deck guidance from uma.guide (Feb 2026, before the July update):

- The framework is 2 Speed, 3 Wit, and Riko Kashimoto.
- Riko is the linked Pal and is called a must-slot in most decks.
- Race Bonus: aim for at least 35 total, up to 50 to 60 with multiple SSRs.
- Power cards are weak here because you get Power mainly from Sparks. Stamina cards are best avoided.

Present these as community guidance, and let the Trainer's own cards and distance goal override them. Show Race Bonus as a meter with markers at 35 and 50 to 60, labelled as guidelines.

---

### 6. TEAM PLANNER (NEW, AND THE KEY PRE-RUN SCREEN)

This is the Unity Cup equivalent of the Route Planner. It turns the team layer into something the Trainer can plan before starting.

```
TEAM PLAN                                   Rice Shower • Blue Bloom

TEAM MEMBERS (from your deck)
 Speed SSR      ✓ team member
 Speed SR       ✓ team member
 Wit SSR        ✓ team member
 Wit SSR        ✓ team member
 Wit SR         ✓ team member
 Riko (Pal)     ✗ not a team member, but linked: +energy, +events

JOINS LATER   Story: Haru Urara, Rice Shower, Matikanefukukitaru, Taiki Shuttle
              Random characters after each team race

DISTANCE GROUPS (up to 3 per group)
 Sprint   [ ]   Mile  [ ]   Medium [ ]   Long [ ]   Dirt [ ]

TEAM RACE CALENDAR            Junior late Dec • Classic late Jun • Classic late Dec
                              Senior late Jun • FINALS Senior late Dec
```

The race calendar comes from Game8's pre-update page, so it is tagged as such. The planner also reminds the Trainer that team members should sit in the distance where their aptitude is A.

---

### 7. RUN PREFLIGHT

```
RUN PREFLIGHT

TEAM NAME     Blue Bloom → Cooldown (if finals won)       ✓ available
DECK          Race Bonus 41%   ✓ above 35
              Riko slotted     ✓
TEAM PLAN     Wit cards: 3     ✓ burst/energy support
              Stamina cards: 0 ✓
GOALS         URA fan gates    ✓ planned
ELITE TEAM    Needs league rank 10+, team rank A+, 1+ Extreme Burst by the 4th race
              ⚠ none planned yet

RULE CONFIDENCE
 Burst/Extreme values (post-update)   GameTora, 2026-07-20
 Race calendar                        Game8, pre-update
```

---

### 8. THE CAREER COCKPIT

```
┌──────────────────────────────────────────────────────────────────────┐
│ UNITY CUP                           CLASSIC • TURN 31 • Energy 58    │
├──────────────────────────────────────────────────────────────────────┤
│ TEAM BOARD                  League rank 6 • Team rank B              │
│            rank   facility                                           │
│ Speed       A       Lv 4                                             │
│ Stamina     C       Lv 3                                             │
│ Power       C       Lv 3                                             │
│ Guts        D       Lv 2                                             │
│ Wit         B       Lv 3                                             │
│ Next team race: 14 turns   Weakest group: Dirt                       │
│ Bursts done: 5  (next skill breakpoint: 7)   Extreme done: 1         │
├──────────────────────────────────────────────────────────────────────┤
│ BURST BANK                                                           │
│ Rice Shower (linked)  normal burst READY   best on: Stamina          │
│ Fine Motion           gauge 70%                                      │
├──────────────────────────────────────────────────────────────────────┤
│ ★ RECOMMENDED: TRAIN STAMINA                                         │
│                                                                      │
│ 4 flames: +6 Stamina, +3 Guts, +5 SP (Special Training)              │
│ Rice Shower burst (linked): +20 Stamina, +10 Guts, +10 SP            │
│                                                                      │
│ WHY                                                                  │
│ Cashing the burst here raises your weakest team stat and moves       │
│ Stamina toward B (facility Lv 3 → Lv 3). No energy penalty.         │
│ ⚠ Banking it for a Wit tile gives less because Rice Shower's         │
│   burst is a Stamina one.                                            │
│                                                                      │
│ [ TRAIN STAMINA ]                                                    │
│ Alternatives                                                         │
│ ○ Wit: +energy, burst not ready for it                               │
│ ○ Optional race: only if a goal requires it                          │
└──────────────────────────────────────────────────────────────────────┘
```

All numbers in that wireframe are illustrative except where tied to a table:

- The 4-flame row and the linked burst values come from GameTora's post-update tables.
- The rank-to-level mapping is the published one (F/G = Lv1, D/E = Lv2, B/C = Lv3, A = Lv4, S = Lv5).

Two behaviours to build in:

#### Flame triggers

Flames are the trigger. A training only pays the extra Special Training stats when it has at least 2 white flames. GameTora's per-flame table for Speed, Stamina and Power:

| Flames | Primary | Secondary | SP |
|--------|---------|-----------|-----|
| 2      | 2       | 1         | 3   |
| 3      | 4       | 1         | 3   |
| 4      | 6       | 3         | 5   |
| 5      | 10      | 5         | 7   |

#### Bursts can be held

Per GameTora, "you don't have to trigger it at the first opportunity", and an unused burst can come back on another facility. That is why the Burst Bank exists.

---

### 8a. TEAM BOARD (PERMANENT COCKPIT ELEMENT)

The Team Board is a permanent fixture of the Career Cockpit — not a one-off pre-race screen, but a continuously visible summary that makes the team-development trade-off legible on every turn. It should support manual team assignments, show each member's aptitude and available skills, track development through training, and forecast which categories need attention before the next showdown.

```
UNITY CUP · TEAM BOARD                      Classic • Turn 31 • Energy 58

OVERALL TEAM RANK           A
NEXT CUP                    4 turns

FACILITY PROGRESSION
Speed       A → Lv 4        (rank determines facility level)
Stamina     B → Lv 3
Power       A → Lv 4
Guts        C → Lv 2
Wit         B → Lv 3

TEAM STAT RANKS
Speed       A                Mile       Review mile aptitude and available skills
Stamina     B                Medium     Review balanced race coverage
Power       A                Long       Review stamina and long-distance skills
Guts        C                Dirt       Review dirt aptitude and available racers
Wit         B

ADVISOR PRIORITY
Develop weak team categories (Guts → B), improve facility ranks,
and assign the most suitable available racers before the next Cup.
```

The Team Board tracks four things the Trainer needs on every turn:

1. **Overall team rank** — the aggregate that determines Zenith-finals eligibility.
2. **Facility progression** — the rank-to-level mapping for each stat, derived from team stat ranks, shown as the current rank transitioning to the next level.
3. **Team stat ranks** — the five-letter-rank grid (Speed, Stamina, Power, Guts, Wit), the same scale that drives facility levels.
4. **Team race coverage** — a distance-by-distance checklist (Sprint, Mile, Medium, Long, Dirt) that surfaces aptitude and available-skill gaps per category.

All values must come from the actual team data via `TrainingRun::gradeObjectives()` and the rules engine; the wireframe above is illustrative only.

---

### 9. ADVISOR PRIORITY HIERARCHY

1. **Don't end the run.** The URA goal races, fan gates, and energy come first, and the Alarm Clock covers goal-race failures.
2. **Team race readiness.** How many turns until the next race, which distance group is weakest, and whether a loss would drop the league rank.
3. **Burst economics.** Cash or bank, which facility, and progress toward the 4 / 7 / 10 / 13 burst-count breakpoints.
4. **Facility levels.** These are derived from team ranks, so show what a training does to the rank.
5. **Training target.**
6. **Optimization.**

Each recommendation must carry a dual-value readout: the trainee gain this turn versus the team-gain / future-opportunity cost. The Advisor should make that trade-off explicit rather than collapsing it into a single number.

An optional race is rarely worth a turn here. Game8 says to skip non-goal races in favour of teammate training, because a race turn is a turn not spent developing the team.

---

### 10. BURST BANK AND EXTREME BURSTS

This is the new mechanic, so it gets its own panel. For each team member, show:

- Gauge progress (a full gauge means the next Special Training with them bursts).
- Whether the normal burst is ready, and whether the Extreme burst is unlocked (it appears for that member during the next Unity Training after their normal burst).
- Where cashing it pays most. The stat bonus depends on the facility.

Values to load as data (GameTora, post-update):

#### Normal burst — Speed tile

| Variant   | Speed | Power | SP  |
|-----------|-------|-------|-----|
| Base      | +15   | +7    | +5  |
| Linked    | +20   | +10   | +10 |

#### Extreme burst — Speed tile

| Variant   | Speed | Power | SP  |
|-----------|-------|-------|-----|
| Base      | +20   | +10   | +15 |
| Linked    | +25   | +15   | +20 |

#### Wit tile

- Normal burst: +15 Wit and +2 Speed. A burst on a Wit tile adds +5 energy recovery.
- Bursts are additive, so there is no bonus for stacking several.
- The hint level from a burst is the card's hint bonus plus 2, and one more for linked cards. The hint comes from the support card's own pool.

An Extreme burst also sets that turn's failure chance to 0%, raises the member's stat caps, and gives a hint for the Ignited Spirit skill for that facility. Treat it as a zero-risk turn when planning energy.

---

### 11. TEAM RACE BRIEFING (EVERY SIX MONTHS)

```
TEAM RACE 2 of 4                    Classic late June
League rank 6      Alarm Clocks 2

OPPONENT (top = strongest)
 ○ Strong     rank if won: (read from game)
 ○ Medium
 ○ Weak

TAZUNA'S OVERVIEW (enter what the game shows)
 Sprint ●●○   Mile ●○○   Medium ●●●   Long ●●○   Dirt ○○○
 Total circles: 8  ✓ ≥ 3 is the "safe" reading

WIN CONDITION   3 of 5 races won → league rank rises. Lose → it falls.
```

Rules to encode:

- A team race is five races, one each of Sprint, Mile, Medium, Long and Dirt.
- Winning at least three raises league rank, and a loss lowers it.
- Alarm Clocks can be used on a team-race loss since the 2026-07-01 update.
- GameTora says at least three circles in total is the generally safe reading of Tazuna's overview.

#### ELITE TEAM (race 4 only)

```
ELITE TEAM (race 4 only)
  League rank ≥ 10     ✓ 11
  Team rank ≥ A        ✓ A
  1+ Extreme Burst     ✓ 2
  → option available. Winning gives extra stats and lets you face the
    strengthened Zenith in the finals.
```

#### Team Roster Dashboard

The briefing should also surface the team development layer — who needs what, and whether they can deliver at the next Cup:

```
TEAM ROSTER                      League rank 6 • Next Cup in 4 turns

Member            Stat focus     Rank  Needs before Cup       Contribution
Rice Shower       Stamina        B     +30 Sta for race 2     Burst: Stamina tile
Fine Motion       Speed          A     —                      Burst: Speed tile (Lv 2)
Biwa Hayahide     Power          C     +50 Power, +2 wins     Epithet: Goddess route
Silence Suzuka†   Guts           D     Bond 2 bars, +40 Guts  Burst: Guts tile
Mejiro Ryan†      Wit            C     +30 Wit                 Burst: Wit tile

† Story characters (no bond gauge, no Friendship training)
```

This makes the trade-off explicit: training Rice Shower's Stamina this turn raises a facility level for everyone, but leaves Biwa at C rank for the next Cup.

#### Tournament Readiness

```
TOURNAMENT READINESS                    Classic late June • Next Cup in 4 turns

OPPONENT PROJECTIONS (top = strongest)
  ○ Strong     rank if won: A+          ⚠ Rice Shower needs +30 Stamina for Guts races
  ○ Medium     rank if won: B           ✓ Biwa's Power is borderline
  ○ Weak       rank if won: C

RACE PREVIEW
  Sprint     Stamina 580      ✓ covered (Rice Shower +20 from burst)
  Mile       Power 920        ⚠ borderline (Fine Motion +30 needed)
  Medium     Speed 1010       ✓ covered
  Long       Stamina 1180     ✓ covered
  Dirt       Guts 640         ⚠ low (Mejiro Ryan bond not ready)

UNRESOLVED RISKS
  • Mejiro Ryan Dirt Guts: no Spirit Burst hint unlocked yet
  • Biwa Hayahide: C rank in Power → facility stays at Lv 3
```

---

### 12. FINALS BRIEFING

```
UNITY CUP FINALS                Senior late December

OPPONENT       Zenith  (strengthened if the elite team was beaten)
TEAM RANK      S   → "It's On" hint (+3 levels with a linked trainee)
               S+  → one more hint level (new rank in the July update)

BURST COUNT    9   breakpoints: 4 / 7 / 10 / 13
SKILL EVENT    "Team Zenith Declares War", Senior late November:
               skill type follows your highest team stat rank

THEN: the three URA Finals races.
```

The late-November event gives hint levels by burst count (GameTora):

| Bursts | Hint Level | Bonus |
|--------|------------|-------|
| 4+     | white Lv1  | +10   |
| 7+     | white Lv3  | +20   |
| 10+    | gold Lv1   | +30   |
| 13+    | gold Lv3   | +40   |

The skill's strength scales with team total stats. uma.guide's example is that 3,580 total Speed gives a 1.1x multiplier.

---

### 13. CAREER REPORT

Unity Cup specifics for the report: final team ranks per stat, league rank, bursts and Extreme bursts, team races won, whether the elite team and strengthened Zenith were beaten, and which gold skill the team name earned. Then offer "Promote to Veteran" as before.

---

### 14. WHAT I COULD NOT CONFIRM

1. **Rank stat thresholds.** GameTora says members are ranked by the average of their stats, but no source read gives the stat-value boundary for each letter rank. Read the rank from the game, and only project a rank if the rule becomes available.

2. **Burst payout split.** The table shows total gains but does not state how much goes to the teammate versus the trainee (the scenario guide says "a large boost to the teammate, a moderate boost to your trainee" but gives no ratio). The Advisor shows the table total and does not split it.

3. **Burst-count breakpoints.** RESOLVED. Post-update sources agree on four non-overlapping bands: 4–6, 7–9, 10–12, 13+ (docs/scenarios/02-unity-cup.md:209-218, SCENARIO-PUBLISHER-REFERENCES.md:1105-1108, config `spirit_burst_bands`). The earlier "4 to 9" / "7 to 9" overlap was pre-rework; the corrected values are non-overlapping and the plan's 4 / 7 / 10 / 13 breakpoints are confirmed.

4. **Event timing.** RESOLVED. Post-rework sources place the burst-count skill event at **Senior Year, Late November** (docs/scenarios/02-unity-cup.md:220, SCENARIO-PUBLISHER-REFERENCES.md:1109). The Game8 extract that printed "Early November" was pre-update; the plan correctly adopted Late November.

5. **Elite team colour.** RESOLVED. The elite team's background is **pink/purple** — the discrepancy was that GameTora's update-list entry used "purple" for Extreme Spirit Bursts while a later section used "pink" for the elite team itself; both colours describe the same pink/purple background (docs/scenarios/02-unity-cup.md:174, SCENARIO-PUBLISHER-REFERENCES.md:1095, UX-DELIVERABLES.md:3058).

6. **Pre-update Game8 data.** Game8's team-rank all-stat bonuses (F +2 up to S +5) and its "rank 5 gives +50" claim before the finals predate the update, so they are left out.

7. **Calendar.** RESOLVED. All five team-race dates are confirmed from post-update sources: Round 1 Junior Late Dec, Round 2 Classic Late Jun, Round 3 Classic Late Dec, Round 4 Senior Late Jun, Finals Senior Late Dec (SCENARIO-PUBLISHER-REFERENCES.md:1021-1025, docs/scenarios/02-unity-cup.md:141-147).

8. **"Result Pts".** RESOLVED. DESIGN-CORPUS.md:1174, 1215, 3612 and 4258 record `Result Pts` as an observed Unity Cup UI counter, measured from captured frames alongside Team Rank and Spirit Burst counts. The term reaches the repo from client captures, not just the reference.

9. **Pal membership.** RESOLVED. docs/scenarios/02-unity-cup.md:56 and :261 state explicitly: "Pal-type cards are excluded from the team roster, so a Pal buys you no Spirit Bursts at all." The GameTora team description is corroborated by the scenario guide.

10. **Alarm Clock limits on team-race retries.** PARTIALLY RESOLVED. The 2026-07-01 rework added Alarm Clock retry on a lost Team Race, confirmed live (SCENARIO-PUBLISHER-REFERENCES.md:577, :661, :1016; docs/scenarios/02-unity-cup.md:151; config `loss_retryable_with_alarm_clock`). The general per-career cap ("up to 5 Alarm Clocks per career", SCENARIO-PUBLISHER-REFERENCES.md:1223) is from the data export but is not Unity-Cup-specific; the per-run allowance and resume turn for a team-race retry remain undocumented.

11. **Names.** Team names and skill names are from English guides, not captured client strings.

---

### WHAT I WOULD NOT BUILD

- An auto-player.
- Fake win-probability numbers for team races (use Tazuna's circles as entered).
- A fixed "best deck" that overrides the Trainer's collection.

---

### MVP SCREEN SET

1. Trainer Home
2. Trainee & Target
3. Legacy Lab
4. Support Deck Builder
5. Team Planner
6. Run Preflight
7. Career Cockpit (with Team Board and Burst Bank)
8. Team Race & Finals Briefing
9. Career Report / Veteran

#7 gets most of the design effort again, and the Team Planner has no equivalent in URA.

The following table maps the recommended MVP features to the screen set above for implementation prioritisation:

| Priority | Feature                  | Screen(s)                | Why it matters                                                |
| -------- | ------------------------ | ------------------------ | ------------------------------------------------------------- |
| 1        | Career Cockpit           | #7                       | Integrates trainee growth and team development                |
| 2        | Team Board               | #7 (permanent panel)     | Tracks members, aptitude, ranks and race coverage             |
| 3        | Unity Training evaluator | #7                       | Identifies valuable multi-member training                     |
| 4        | Spirit Burst planner     | #7 (§10 Burst Bank)      | Tracks gauges and helps time burst opportunities              |
| 5        | Unity Cup match planner  | #8                       | Evaluates opponents and five-race team assignments            |
| 6        | Support Deck Builder     | #4                       | Balances scenario-linked effects, training and skill coverage |
| 7        | Career Report / Veteran  | #9                       | Preserves trainee outcomes and team-development history       |

---

### 17. DECISION CONTRACT

Every recommendation the Advisor produces follows this shape — it must not be a bare action label:

1. **Current state and immediate objective.**
2. **Recommended action** and expected benefit (with numbers).
3. **Best alternative** and its trade-off.
4. **Confidence** — verified, derived, configurable, or unknown — with the source badge.
5. **Conditions that would change the recommendation.**

Example:

> **★ RECOMMENDED: TRAIN STAMINA**
>
> You are 18,800 fans short of the Classic gate with 8 turns remaining, and Stamina is below the Medium-distance floor.
>
> - **Train Stamina (Lv 3):** +6 Sta, +3 Guts, +5 SP. Facility is 4/12 to Lv 4.
> - **Alternative — race a G3 (Mile):** +100 coins, +60 GP if 1st, but consumes energy and risks Mood Down from fatigue.
> - **Confidence: verified** (facility level by repetition, config `facility_level_source = 'repetition'`).
> - **Switch if:** Summer camp starts in 6 turns (bank energy first), or Akikawa bond reaches 3-bar (Fan Fest gate opened).

---

### 18. ACCEPTANCE CRITERIA

Each planned feature ships with concrete success conditions:

| Feature                    | Success Criterion                                                                 | Edge Case                                  |
| -------------------------- | --------------------------------------------------------------------------------- | ------------------------------------------ |
| Career Cockpit             | Shows trainee stats, team ranks, burst gauges, and next cup date in a single view | Team member has zero bonds                 |
| Team Board                 | Tracks 5+ members, surfaces weak race categories, updates after training          | All members at S rank (no upgrades left)   |
| Spirit Burst planner       | Warns when a burst is missed (turn passed without cashing)                        | Burst ready on multiple members simultaneously |
| Unity Training evaluator   | Identifies multi-member trainings worth 3+ flames                                 | No teammates available for a facility      |
| Unity Cup match planner    | Assigns 5 racers to 5 distances, highlights missing aptitudes                     | Fewer than 5 eligible team members         |
| Recommendation reconciliation | Predicted outcome vs actual entry updates the run state and flags deviation    | User edits state manually after the fact   |

---

### 19. VERIFICATION STATUS

Each feature carries one of three levels — never collapsed:

- **Implemented** — the code exists and the behavior is connected.
- **Verified** — automated tests or reproducible manual checks demonstrate the intended behavior.
- **Game-validated** — calculations have been compared against authoritative mechanics or observed in-game.

An "Implemented and verified" feature can still rest on an incorrect understanding of the game. A page that renders is not verified. This distinction is tracked per feature, not per scenario.

---

### 20. IMPLEMENTATION AND TESTING PLAN

| Phase                      | Deliverable                                                                     | Completion evidence                                              |
| -------------------------- | ------------------------------------------------------------------------------- | ---------------------------------------------------------------- |
| 0. Reconnaissance          | Inspect existing scenario code, plans, contracts, routes, data models and tests | Inventory of existing, partial, missing and conflicting behavior |
| 1. Rules audit             | Document verified scenario mechanics and unresolved assumptions                 | Source-backed rules and explicit unknowns                        |
| 2. State model             | Define run state, transitions, persistence and validation                       | Tests for valid and invalid state changes                        |
| 3. Decision engine         | Implement scenario-specific candidate evaluation and ranking                    | Deterministic tests using known inputs and expected outputs      |
| 4. Command center          | Build the primary screen around the next actionable decision                      | UI tests for loading, empty, error, stale and normal states      |
| 5. Reconciliation          | Compare predicted versus actual outcomes                                        | Tests for updating run state without losing history              |
| 6. End-to-end verification | Exercise complete careers and edge cases                                        | Reproducible scenarios, regression results and completion report |

**Unity Cup focus:** phase 4 builds the Team Board and Unity Training evaluator as integrated cockpit panels. Phase 5 adds reconciliation for Spirit Burst outcomes (predicted stat gain vs. actual). Phase 6 validates team-rank-to-facility-level math against in-game captures.

---

### 21. PRIORITY MATRIX

| Priority | Scope                    | Rationale                                                  |
| -------- | ------------------------ | ---------------------------------------------------------- |
| P0       | Canonical run state, scenario isolation, regression tests | Protects the trustworthiness of team-development tracking  |
| P1       | Next-action recommendations, deadline tracking, dual-value readout | Turns the cockpit from a display into a command center     |
| P2       | Matchup analysis, support-deck optimization for team synergy | Materially affects tournament outcomes                     |
| P3       | Keyboard-friendly controls, richer historical analytics | Convenience only                                           |

---

## URA-Finale-plan.md

> **Augmented 2026-10-10 (Round 14, augment-only).** Source: `docs/URA-Finale-plan.md` (381 lines), reproduced verbatim below with headings demoted one level. The source file remains in place; no citation was repointed and nothing was deleted.

## URA Finale Plan

This document defines the URA Finale scenario for Trainer Desk. It is written in the same architectural style as the Unity Cup plan: it assumes the shared cockpit frame, the three-layer model (Trainer Home, Career Cockpit, Advisor), decision cards, visible uncertainty, and the Veteran loop, all of which carry over unchanged. Only the URA Finale-specific deltas are spelled out here.

### Sources

- `docs/scenarios/01-ura-finale.md` — 142-line scenario guide (fan gates, camp, facilities, stamina thresholds, deck shapes, mood effects)
- `docs/UMAMUSUME_REFERENCE.md` §2.2.1 — URA Finale `[Global]` mechanics summary (Happy Meek, fan gates, beginner targets)
- `config/scenarios.php:111-130` — config matrix entry (`cap_bonus`, `widgets`, `panels`, `facility_level_source`, `scenario_links`)
- `.scratch-uma/cited/scenarios.json` — GameTora data export (`stats:[200,200,200,200,200]`, `start_en:1750897800`)
- `SCENARIO-PUBLISHER-REFERENCES.md:892-996` — Game8 extraction (July 1, 2026 rework confirmation)
- [Game8, URA Finale Scenario Guide](https://game8.co/games/Umamusume-Pretty-Derby/archives/536520) (last updated July 6, 2026)

**Cross-check note.** URA Finale enters SCENARIO-PUBLISHER-REFERENCES.md:33 "solely through the Game8 extraction" — uma.guide and GameTora cover only Trackblazer and Unity Cup. There is no second publisher to cross-check against for URA Finale. The config matrix and the scenario guide are the first author and the Game8 page the second; they agree on every figure cited below.

The July 1, 2026 rework added Happy Meek duels and raised caps from 1200 to 1400 in all stats. `config/scenarios.php:114` (`cap_bonus` = 200 each) and `.scratch-uma/cited/scenarios.json` (`stats:[200,200,200,200,200]`) both record this; the Game8 page confirms it in §"Increased Stat Caps".

---

### 1. THE CENTRAL UX CONCEPT

URA Finale is the fundamentals scenario. There is no team, no shop, no mid-scenario currency, and no special program. The single thing layered on top of the base training loop is the **Happy Meek duel**:

```
         STAT TRAINING turn
               │
               ├─► Happy Meek may appear
               │        │
               │        ├─► WIN → stat gain + cap bump + racing-spirit hint + 30 SP
               │        └─► LOSE → minor stat gain + 15 SP
               │
               └─► 6 wins escalate her to powered-up state at the finals
                     WIN → Past My Limits skill + 200+ SP + enhanced spirit spark
```

The persistent question for URA is:

> Which training turn raises the stats I need for the next fan gate or goal race, and should I spend energy on a Happy Meek duel now or save it for a Summer facility upgrade?

---

### 2. SCENARIO SELECT

```
URA FINALE
Fundamentals. Happy Meek duels for cap increases.

Stat caps   SPD 1400  STA 1400  POW 1400  GUT 1400  WIT 1400
Facility level rises by repetition (every 4 same-stat trainings)
Scenario Link: Aoi Kiryuin
```

The +200 cap_bonus is from `config/scenarios.php:114` and `.scratch-uma/cited/scenarios.json:stats`. Aoi Kiryuin is the scenario link; `config/scenarios.php:126` lists her and `docs/scenarios/01-ura-finale.md:16-19` confirms her event role.

---

### 3. TRAINEE & TARGET (DELTAS)

The career goals, fan gates, and goal-race ladder are the URA defaults carried from `docs/scenarios/01-ura-finale.md:77-84` and confirmed in Game8 §"Run More Races to Get Fans and Upgrade Unique Skill".

- **Fan gates** (Turf / Dirt): Valentine's Day 60,000 / 40,000 (Senior, early Feb); Fan Fest 70,000 / 60,000 (Senior, early Apr, with 3-bar Director Akikawa friendship); Holiday Season 120,000 / 80,000 (Senior, late Dec). Source: `docs/scenarios/01-ura-finale.md:80-83`, Game8 §"Run More Races...", UMAMUSUME_REFERENCE.md §2.2.1.
- **Stamina floors** by distance: Sprint 350–450, Mile 450–550, Medium 600–700, Long 700–800. Source: `docs/scenarios/01-ura-finale.md:66-73`, UMAMUSUME_REFERENCE.md §2.2.1.
- **Unique Skill level-up gates** at those same fan thresholds. Source: same as fan gates above.
- **Goal race distance.** The finale distance is determined by the most-raced distance type across the career; ties resolve to the shortest distance. This is the URA-specific distance ledger and the one surface where URA diverges from the other scenarios' fixed calendars. Source: `docs/scenarios/01-ura-finale.md:140` ("plan a race calendar"), confirmed by Game8 §"Ensure You Have Enough Stamina" listing all four distance-specific thresholds as the decision driver.

No new goal-race or fan-gate screen is needed — the shared `race_calendar` panel handles distance tracking and the `career_goals` panel handles the gates. Both are `true` in `config/scenarios.php:117-124`.

---

### 4. LEGACY LAB (DELTAS)

Three scripted Inspiration events scale stat boosts off the Legacy's own build — Junior Year start, Classic Year early April, Senior Year early April. Source: `docs/scenarios/01-ura-finale.md:43`.

There is **no scenario factor** in URA Finale. Unlike Unity Cup (`Power` and `Wit` on inheritance) or Trackblazer (distance-dependent stat grants), URA's Legacy inherits base stats and skills only. The Legacy Lab screen should show no scenario-factor badge to avoid implying a bonus that does not exist.

---

### 5. SUPPORT DECK BUILDER (DELTAS)

Deck templates from `docs/scenarios/01-ura-finale.md:56-80` and confirmed in Game8 §"Use Proper Support Cards That Will Benefit Your Umamusume":

| Distance   | Build 1                | Build 2                          |
| ---------- | ---------------------- | -------------------------------- |
| Sprint     | 4× Speed, 2× Power     | 3× Speed, 1× Wit, 2× Power       |
| Mile       | 4× Speed, 2× Power     | 3× Speed, 1× Wit, 2× Power       |
| Medium     | 4× Speed, 1× Friend/Guts, 1× Power | 3× Speed, 1× Wit, 1× Friend/Guts, 1× Power |
| Long       | 4× Speed, 2× Friend/Guts | 3× Speed, 1× Wit, 2× Friend/Guts  |

Key principles:
- Check the skills a card provides — a card's built-in skill matching the trainee's running style/distance becomes purchasable during training. Source: Game8 §"Pay Attention to Skills that Cards Provide".
- Speed is close to universally valuable regardless of build.
- Stamina cards are best avoided (you get Stamina mainly from stats, and the Stamina floor is a hard prerequisite, not a flex).

Present these as community guidance only; the Trainer's own cards and distance goal override them.

---

### 6. RUN PREFLIGHT

```
RUN PREFLIGHT                                    URA Finale

DECK          Race Bonus 41%   ✓ adequate for beginner targets
              Aoi Kiryuin (SR) ✓ Scenario Link present
LEGACY        [Legacy Uma Name]  → Inspiration events scale on this
TARGET        Sprint  →  Speed 800+, Stamina ≥ 400
              Mile    →  Speed 800+, Stamina ≥ 500
              Medium  →  Speed 800+, Stamina ≥ 600
              Long    →  Speed 800+, Stamina ≥ 700
GOALS         URA fan gates   ✓ planned
FANS          Valentine 60k   ✓ (10 weeks out)
              Fan Fest 70k    ✓ (22 weeks out, Akikawa friendship planned)
              Holiday 120k    ✓ (final gate)
CAMP          Summer Y2: Jul–Aug, 4 turns, all Lvl 5  ✓ timed
              Summer Y3: Jul–Aug, 4 turns, all Lvl 5  ✓ timed
DUels         Happy Meek     0/6 duels won so far
              Past My Limits  ✗ not yet in reach

RULE CONFIDENCE
  Stat caps                 config/scenarios.php:114 + .scratch-uma/cited/scenarios.json
  Fan gates                 docs/scenarios/01-ura-finale.md + Game8
  Stamina floors            docs/scenarios/01-ura-finale.md + UMAMUSUME_REFERENCE.md §2.2.1
  Training level (repetition) config/scenarios.php:127 ($facility_level_source = 'repetition')
```

---

### 7. THE CAREER COCKPIT

```
┌──────────────────────────────────────────────────────────────────────┐
│ URA FINALE                 CLASSIC • TURN 31 • Energy 58           │
├──────────────────────────────────────────────────────────────────────╢
│ FACILITY LEVELS              Training facility level by repetition  │
│ Speed       3    (12 trainings)                                     │
│ Stamina     2    (8 trainings)                                      │
│ Power       3    (12 trainings)                                     │
│ Guts        2    (8 trainings)                                      │
│ Wit         3    (12 trainings)                                     │
├──────────────────────────────────────────────────────────────────────╢
│ FAN GATES                                                           │
│ Valentine's Day    48,000 / 60,000 (Turf) — 14 turns to gate        │
│ Fan Fest           32,000 / 70,000 (Turf) — 22 turns to gate        │
│ Holiday Season     18,000 / 120,000 (Turf) — 44 turns to gate       │
├──────────────────────────────────────────────────────────────────────╢
│ DUEL TRACKER                                                        │
│ Happy Meek:  3 / 6 wins   (2 more to escalation)                    │
│ Racing Spirit hints earned: Speed Lv1, Power Lv1                    │
├──────────────────────────────────────────────────────────────────────╢
│ ★ RECOMMENDED: TRAIN STA                                            │
│                                                                  │
│ 4 flames: +6 Sta, +3 Guts, +5 SP (repetition Lv3)                  │
│                                                                    │
│ WHY                                                                  │
│ Stamina is your weakest floor for a Medium-distance build.           │
│ Facility is at Lv 2; one more block of 4 reaches Lv 3.               │
│ Energy 58 → 18 after training. Summer starts in 6 turns;              │
│ you need 60 for 4 Lvl-5 facility turns. Recreation on turn 33        │
│ covers the gap.                                                    │
│                                                                    │
│ [ TRAIN STA ]                                                      │
│ Alternatives                                                       │
│ ○ Speed: +facility, but Stamina floor is the blocker                 │
│ ○ Optional race: only if a goal requires it                          │
└──────────────────────────────────────────────────────────────────────┘
```

All numbers in that wireframe are illustrative except the rule they encode: facility level rises every 4 repetitions (`config/scenarios.php:127`), and Stamina is a floor not a flex stat.

---

### 8. HAPPY MEEK DUEL PANEL (NEW, THE KEY IN-RUN SCREEN)

This is the URA-only mechanic, so it gets a dedicated panel — not folded into the generic cockpit.

#### When she appears

Happy Meek appears on any stat-training turn after the "I'm Here to Challenge You" event. Source: Game8 §"Training With Happy Meek". She does **not** give Friendship trainings (unlike Team Sirius or Heirs to the Throne). Source: Game8 §"Training With Happy Meek".

#### Duel choice

You are prompted to pick between three options: two random stats and the stat you dueled Happy Meek on. Each option has an icon indicating win chance. Source: Game8 §"Duel for Additional Stat Cap Increases and Skill Points".

#### Rewards

| Outcome | Primary                     | Secondary        | Cap bump | Skill Points | Racing Spirit hint |
| ------- | --------------------------- | ---------------- | -------- | ------------ | ------------------- |
| Win     | Stat gain (varies)          | Related stat +SP | +1 tier  | 30           | Yes (stat-appropriate) |
| Lose    | Minor stat gain             | —                | —        | 15           | —                   |

Source: Game8 §"Duel for...", UMAMUSUME_REFERENCE.md §2.2.1.

#### Escalation

After **6 wins**, Happy Meek escalates to a powered-up state at the finals. Beating her there grants:
- The `Past My Limits` skill (source: Game8 §"Powered Up Happy Meek", and the linked skill page title)
- 200 or more Skill Points
- A chance at an enhanced Racing Spirit spark (carries a stat bonus and a skill hint into inheritance)

Source: Game8 §"Powered Up Happy Meek", UMAMUSUME_REFERENCE.md §2.2.1.

#### Racing Spirit skills

Each Racing Spirit skill activates on the stat you dueled Happy Meek on. Source: Game8 §"New Racing Spirit Skills":

| Skill                    | Effect                                                  |
| ------------------------ | ------------------------------------------------------- |
| Racing Spirit: Speed     | Slightly increase velocity upon approaching late-race   |
| Racing Spirit: Stamina   | Moderately expend endurance to moderately increase velocity in the last spurt |
| Racing Spirit: Power     | Slightly increase velocity for a medium duration when passing another runner in the last spurt |
| Racing Spirit: Guts      | Slightly increase acceleration when dueling             |
| Racing Spirit: Wit       | Slightly increase velocity upon activating 2 skills during the second half |
| Racing Spirit: Mood      | Stamina, Guts, and Wit might moderately increase when mood is good or great |

Display these as hints earned — the duel tells you which Racing Spirit skill the hint is for, and winning a powered-up duel enhances the spark that carries the hint into inheritance.

---

### 9. ADVISOR PRIORITY HIERARCHY

1. **Don't end the run.** The URA goal races, fan gates, and energy come first, and the Alarm Clock covers goal-race failures.
2. **Summer timing.** Is Summer within 6 turns? Bank full energy and good mood now — a forced Rest during Summer wastes the free Level-5 facility turns.
3. **Fan gate runway.** How many turns until the next fan gate, and is your race calendar on track to clear it?
4. **Happy Meek duel economics.** Cash the duel now (energy cost vs. stat gain + cap bump + SP) or save energy for Summer. A duel win also raises a cap, which matters for Long builds hitting the 1200→1400 transition.
5. **Facility level.** Track repetition counts; a stat at 8/12 trainings is one block away from the next level.
6. **Stamina floor.** Before a Medium or Long race, is Stamina at the floor? If not, that training turn is non-negotiable.
7. **Training target.**
8. **Optimization.**

---

### 10. CAREER REPORT

URA Finale specifics for the report: final stat breakdown, which fan gates were cleared, Summer camp utilization, Happy Meek duel record (wins / escalation), Racing Spirit hints earned, unique skill level at career end, and whether the finale was won. Then offer "Promote to Veteran" as before.

Post-race rewards (Game8 §"After URA Finale Races"):
- **Qualifier win:** All stats +10, +40 Skill Points, affected by Race Bonus.
- **Semifinals win:** All stats +10, +60 Skill Points, affected by Race Bonus.
- **Finals win:** All stats +10, +80 Skill Points, affected by Race Bonus.

---

### 11. WHAT I COULD NOT CONFIRM

1. **Per-option duel win-chance percentages.** Game8 states each option "has an icon which indicates your chances of winning" but does not print the percentage values. Read the icon from the game; the plan's duel rewards table is confirmed, the exact probability per option is not.

2. **Happy Meek stat-gain formulas.** Game8 says winning grants "stat gains" and a cap bump but does not quantify the per-stat gain values beyond "the stat you dueled on." The 30/15 Skill Point split and the Racing Spirit hint award are confirmed; the exact stat-delta per win is not in any cited source.

3. **Aoi Kiryuin event bonuses.** `docs/scenarios/01-ura-finale.md:17` states she "boosts your stats via her interactions with another character, Happy Meek" but does not quantify the multiplier. Game8 §"Aoi Kiryuin is the Scenario Link" confirms the link role and recommends the SR card to amplify effects, but gives no numeric bonus. Treat this as a qualitative bonus, not a quantified one.

4. **Turns-per-year count.** The Game8 calendar uses relative timing ("After 11 turns", "Early July", "Late August") but does not give a total turn count per year. The Summer window is confirmed as 4 turns, Early July through Late August, in both Classic and Senior years. The exact turn offset from career start to each event is in `docs/scenarios/01-ura-finale.md:86-132` but the source notes (line 88-92) explicitly defer race-timing to `docs/scenarios/09-global-race-calendar.md`.

5. **Powered-up Happy Meek exact stat values.** Game8 §"Powered Up Happy Meek" states she "gets increased stats and is overall harder to beat" but does not list the specific numbers. The reward list (Past My Limits skill, 200+ SP, enhanced spark) is confirmed.

6. **Enhanced Racing Spirit spark mechanics.** "A chance to get an enhanced spark" — the exact trigger rate and the exact stat-bonus amount are not quantified in any cited source.

7. **Raffle Time! outcomes.** Game8 §"Raffle Time!" lists one of four random effects but does not state the probability distribution among them. Treated as a non-quantified random event.

8. **New Year's Resolutions / New Year's Shrine Visit choice weights.** Game8 lists the three options per event but does not state whether the choice is purely player-selected (it is — these are choice events, not random). This is confirmed by the scenario guide calling them "Choice:" events (`docs/scenarios/01-ura-finale.md:117-119`); the plan records the options, not probabilities.

---

### WHAT I WOULD NOT BUILD

- An auto-player for Happy Meek duels (read the icon, let the Trainer decide).
- A fixed "best deck" that overrides the Trainer's collection (present deck templates as guidance, not prescription).
- The enhanced Racing Spirit spark rate as a numeric prediction (no source quotes it).
- A stamina floor that silently defaults to a value a run has not recorded (render `N/A` with a title, never a default).

---

### MVP SCREEN SET

1. Trainer Home
2. Trainee & Target
3. Legacy Lab
4. Support Deck Builder
5. Run Preflight
6. Career Cockpit (with Fan Gates, Facility Levels, and Duel Tracker)
7. Happy Meek Duel Panel (in-run overlay triggered on a duel appearance)
8. Career Report / Veteran

§6 and §7 get most of the URA-specific design effort. The Duel Panel exists because URA Finale is the only scenario where a single in-run choice (duel stat + option) carries a cap-bump reward, and that choice is opaque without the panel surfacing the Racing Spirit hint tied to each result.

---

### 12. DECISION CONTRACT

Every recommendation the Advisor produces follows this shape — it must not be a bare action label:

1. **Current state and immediate objective.**
2. **Recommended action** and expected benefit (with numbers).
3. **Best alternative** and its trade-off.
4. **Confidence** — verified, derived, configurable, or unknown — with the source badge.
5. **Conditions that would change the recommendation.**

Example:

> **★ RECOMMENDED: TRAIN STA**
>
> You are at 48,000 / 60,000 fans for Valentine's gate, 14 turns out, and Stamina is below the Medium-distance floor (600–700).
>
> - **Train Stamina (Lv 2):** +8 Sta, +4 SP. Facility is 6/8 to Lv 3.
> - **Alternative — race a Tier 2 (Mile):** +4 Skill Points, +fans, but consumes energy and risks mood drop.
> - **Confidence: verified** (facility level by repetition, config `facility_level_source = 'repetition'`).
> - **Switch if:** Summer camp starts in 5 turns (bank energy first), or Happy Meek appears on a Speed tile (cap-bump opportunity).

---

### 13. CAREER MILESTONE TRACKER

URA Finale is a deadline scenario: three goal races with win-or-end stakes, three fan-gate events, and two Summer camps. The cockpit should surface a timeline, not just the current turn.

```
CAREER MILESTONES

UPCOMING
  • Valentine's Day gate    60,000 fans    14 turns    ⚠ 12,000 short
  • Summer camp (Y2)        4 turns        18 turns    Energy: 58 / 60
  • Fan Fest gate           70,000 fans    22 turns    ⚠ 32,000 short
  • URA Qualifier           Win required   36 turns    Speed target: 800+

COMPLETED
  • Junior debut            ✓ (Turn 11)
  • Inspiration 1           ✓ (Career start)
  • Inspiration 2           ✓ (Classic Apr)
  • 3-bar Akikawa friend    ✓ (for Fan Fest)
```

The timeline should distinguish **optimal now** (e.g., train Stamina) from **keeping the run viable later** (e.g., bank energy before Summer, hit Valentine's gate for Unique Skill upgrade).

---

### 14. ACCEPTANCE CRITERIA

| Feature                    | Success Criterion                                                                 | Edge Case                              |
| -------------------------- | --------------------------------------------------------------------------------- | ---------------------------------------- |
| Career Cockpit             | Shows stat caps, facility levels, fan gates, and duel count in a single view      | No fans yet (N/A, not 0)                 |
| Happy Meek Duel Panel      | Surfaces the 3 options, their icons, and Racing Spirit hint tied to each outcome  | All 6 duel wins achieved (escalation shown) |
| Career Milestone Tracker   | Surfaces each gate 5+ turns in advance, flags shortfalls                          | Gate already missed (run-at-risk flag)   |
| Stamina floor check        | Flags before any Medium/Long race if Stamina is below the distance floor          | Dirt surface (different floor threshold) |
| Recommendation reconciliation | Predicted outcome vs actual entry updates the run state and flags deviation   | User edits state manually              |

---

### 15. VERIFICATION STATUS

Each feature carries one of three levels — never collapsed:

- **Implemented** — the code exists and the behavior is connected.
- **Verified** — automated tests or reproducible manual checks demonstrate the intended behavior.
- **Game-validated** — calculations have been compared against authoritative mechanics or observed in-game.

A page that renders is not verified. A verified calculation can still rest on an incorrect understanding of the game. This distinction is tracked per feature.

---

### 16. IMPLEMENTATION AND TESTING PLAN

| Phase                      | Deliverable                                                                     | Completion evidence                                              |
| -------------------------- | ------------------------------------------------------------------------------- | ---------------------------------------------------------------- |
| 0. Reconnaissance          | Inspect existing scenario code, plans, contracts, routes, data models and tests | Inventory of existing, partial, missing and conflicting behavior |
| 1. Rules audit             | Document verified scenario mechanics and unresolved assumptions                 | Source-backed rules and explicit unknowns                        |
| 2. State model             | Define run state, transitions, persistence and validation                       | Tests for valid and invalid state changes                        |
| 3. Decision engine         | Implement scenario-specific candidate evaluation and ranking                    | Deterministic tests using known inputs and expected outputs      |
| 4. Command center          | Build the primary screen around the next actionable decision                    | UI tests for loading, empty, error, stale and normal states      |
| 5. Reconciliation          | Compare predicted versus actual outcomes                                        | Tests for updating run state without losing history              |
| 6. End-to-end verification | Exercise complete careers and edge cases                                        | Reproducible scenarios, regression results and completion report |

**URA Finale focus:** phase 3 builds the scenario rules layer (repetition-based facility levels, fan gates, Happy Meek duel rewards as verified). Phase 4 builds the Career Cockpit with the Duel Panel. Phase 5 adds reconciliation for duel outcomes (predicted stat gain vs. actual). Phase 6 validates cap-bump behavior against in-game captures.

---

### 17. PRIORITY MATRIX

| Priority | Scope                    | Rationale                                                  |
| -------- | ------------------------ | ---------------------------------------------------------- |
| P0       | Canonical run state, verified mechanics, regression tests | Protects the trustworthiness of the fundamentals scenario    |
| P1       | Next-action recommendations, deadline tracking, duel preview | Turns Cockpit from display into a command center          |
| P2       | Stat-cap ceiling arithmetic, distance-floor optimization | Materially affects finale eligibility                     |
| P3       | Keyboard-friendly controls, richer historical analytics | Convenience only                                           |

---

## Trainer-Desk-2.0-G-Prompts.md

> **Augmented 2026-10-10 (Round 14, augment-only).** Source: `docs/Trainer-Desk-2.0-G-Prompts.md` (1046 lines), reproduced verbatim below with headings demoted one level. The source file remains in place; no citation was repointed and nothing was deleted.
>
> **Consolidated 2026-10-12 (Round 15).** The original was deleted after line-by-line verification (687 of 687 non-blank source lines found in this section, in order, 0 missing). The original was untracked, so this section is the only copy. Cite this anchor, not the deleted path.

## Trainer Desk 2.0 — Phase G Prompt Set (Revised)

This revision separates the execution contract from the slice specifications and makes the amendments you identified. It keeps the detailed scenario requirements, source hierarchy, and owner questions intact.

Three layers, used in order:

1. **Phase G Execution Contract** (Part 1) — authority, preflight, safety, workflow, gates, reporting. Paste once before any G slice.
2. **Shared Architecture Contract** (Part 2) — configuration-driven capability selection, the Next Decision contract, provenance, prohibited mechanics. Embedded in the master and referenced by every slice.
3. **Slice Specifications G1–G7** (Part 3) — feature behavior and acceptance tests. Paste one at a time after the master.

---

## PART 1 — PHASE G EXECUTION CONTRACT

**Scope:** Scenario decision surfaces, the shared Next Decision contract, cross-scenario consistency, and Phase G acceptance.

**Project:** `umamusume-laravel13` — Laravel 13, Inertia v3, Vue 3, TypeScript, Tailwind v4, Pest 4, Playwright.

**Objective:** Implement FD-1 through FD-5 from `docs/proposals/frontend-development-plan.md` §9a, using the approved screen specifications, design briefs, scenario plans, existing persistence contracts, and repository conventions.

Phase G extends the landed D8 Career Cockpit and E1–E6 scenario-panel work. It must not create a second frontend architecture, introduce unsupported game mechanics, or replace established contracts without authorization.

The G1–G7 specifications define individual deliverables, source requirements, acceptance criteria, and owner questions. This document governs their execution and takes precedence over informal interpretation of those specifications.

---

### 1. Non-negotiable execution rules

1. **Establish repository truth before implementation.** Read the actual files, configuration, routes, Form Requests, models, migrations, components, tests, and Git state. A proposed feature or field in a design document is not evidence that its implementation exists.
2. **Do not invent game mechanics.** Every displayed game value, rule, threshold, calculation, and recommendation must have an authorized source. When evidence is insufficient, preserve the documented absence behavior or omit the feature where the specification directs.
3. **Keep the UI scenario-agnostic.** Scenario capabilities come from the resolved configuration matrix, `panels.*`, and `widgets[]`. No scenario-specific conditional rendering based on scenario names, labels, or key literals. A fifth scenario must require configuration, not component changes.
4. **Preserve existing write contracts.** Use existing routes and Form Requests. Do not introduce migrations, routes, enum values, packages, or new persistence semantics without the required authorization.
5. **Make decisions traceable.** Every recommendation must have a derivable reason. Every computed value must identify its provenance. Never substitute a numeric score or confidence value for an explanation.
6. **Protect shared-worktree integrity.** Do not overwrite, revert, stage, or commit another session's work. Do not assume a clean Git status. Stop before implementation if file ownership or the safety of the working tree cannot be established.
7. **Test claims, not intentions.** Report actual command output, exit status, relevant test counts, and known blockers. Never describe an unexecuted gate as passing.
8. **Minimize the diff.** Implement the smallest architecture that satisfies the approved contract. No unrelated refactoring, visual redesign, opportunistic cleanup, or speculative abstraction.
9. **Separate implementation from release readiness.** A slice can have verified implementation with a blocked browser gate, but it cannot claim full acceptance. The applicable owner-approved baseline determines whether implementation may begin.
10. **Stop at authority boundaries.** When a decision requires owner approval, document the alternatives and evidence. Do not silently choose an irreversible or contract-changing interpretation.

---

### 2. Mandatory preflight and stop conditions

Complete preflight before running any G slice.

#### 2.1 Shared-worktree coordination

Read `docs/proposals/provenance-and-absence-contracts.md` §15 and inspect the actual repository state.

Confirm:

- Which session owns the relevant files.
- Whether another session is currently modifying the same tree.
- Which files are safe to edit.
- Whether the working tree contains uncommitted work that must be preserved.
- Whether the coordination ruling in §20(h) has been resolved.

**Stop before modifying files** if ownership conflicts remain unresolved or the tree cannot be modified safely. Report the conflicting ownership, affected files, and exact decision required.

Do not treat a previous agent's assertion that the coordination issue was resolved as proof. Verify the current state.

#### 2.2 KI-88 browser blocker

KI-88 concerns the Cockpit header forms `saveStatus`, `saveScenario`, and `savePeriod`, which lack submit controls required by existing browser fixtures.

Before implementation, establish:

1. The current reproduction and affected browser specs.
2. The owner's decision between the candidate save-control and autosave behaviors.
3. The authorized KI-88 fix and the files it may change.
4. The baseline browser results after that fix, including the 44px target sweep and axe scan.

Do not independently choose between the two save behaviors. Do not change the save interaction merely to satisfy test fixtures.

**Blocking policy:**

- Until the owner resolves the KI-88 behavior, do not implement changes that depend on that behavior.
- Until the owner resolves the Phase G baseline policy, do not claim browser acceptance or full Phase G acceptance.
- If the owner explicitly permits isolated work before KI-88 is fixed, a slice may proceed only when its implementation is independent of the unresolved save behavior and shared-tree coordination is safe.
- Record any baseline failure separately from failures introduced by the slice.

If no such authorization exists, stop and report. Do not reinterpret this exception as permission to bypass the prerequisite.

#### 2.3 Open decisions

Read `docs/proposals/provenance-and-absence-contracts.md` §9 and §20.

Treat unresolved owner decisions as follows:

| Decision | Required behavior |
|---|---|
| Group C wording | Does not block G1–G2 unless the affected copy is required |
| Motion | Does not block G1–G2 unless the affected interaction depends on it |
| 768px minimum | Does not block G1–G2 unless the affected layout depends on it |
| `Runs/Show.vue` ownership | Resolve before modifying that surface |
| Shared-tree coordination | Resolve before modifying files |
| Other owner-controlled decisions | Keep the affected behavior out of scope until authorized |

The individual slice specifications may introduce additional blocking questions. Apply the same rule: identify the exact affected deliverable rather than expanding the blocker to unrelated work.

#### 2.4 Dependency verification

Before starting implementation, verify that D8 and E1 have landed and that the required preceding slices are complete.

The intended execution order is:

- G1: capability audit and shared Next Decision contract.
- G2: objectives, deadlines, and action reconciliation.
- G3: URA Finale surfaces.
- G4: Unity Cup surfaces.
- G5: Trackblazer planning and race surfaces.
- G6: Trackblazer shop, resource, and epithet surfaces.
- G7: cross-scenario consistency and final acceptance.

G3, G4, and G5 may run in parallel after G2 only when file ownership is disjoint, G1's contract is stable, and G2's contract changes are complete. G6 additionally depends on G5.

If those conditions cannot be demonstrated, serialize the work.

---

### 3. Required source reading

Read the following in order, recording any missing or contradictory authority in the slice plan.

1. `AGENTS.md`
2. `docs/proposals/frontend-development-plan.md` — Global Constraints, §3, §4, §8, §9, §9a, §10, §12, and §13.
3. `docs/proposals/screen-spec-2.0.md` — the relevant SCREEN-nnn sections, the Product Direction Corrections table, and §18–20.
4. `docs/proposals/design-2.0.md` — §2, §4, §5, §13, §19–24, §25–28, §29, §35, §36, §40, §45, and §49, plus any additional sections named in the slice specification.
5. `docs/proposals/provenance-and-absence-contracts.md` — §1, §2, §9, §12, §15, and §20 as relevant.
6. `config/scenarios.php` — inspect actual configuration keys and structures.
7. `SCREEN_SPEC.md` and `DESIGN.md`.
8. The relevant scenario plans:
   - `docs/URA-Finale-plan.md`
   - `docs/Unity-Cup-plan.md`
   - `docs/Trackblazer-plan.md`
   - `docs/Our-Grand-Concert-plan.md`
9. The specific source documents, services, controllers, models, routes, Form Requests, and components identified in the individual G specification.

Do not assume that a file, section, key, route, model column, or test exists because it appears in the prompt. Verify it.

#### Authority order

Apply this order:

1. Landed ADRs and binding owner rulings.
2. `AGENTS.md` and repository-wide constraints.
3. `frontend-development-plan.md`.
4. `screen-spec-2.0.md` and `design-2.0.md`.
5. Scenario plans and research documents, within their evidentiary authority.
6. Installed skills as implementation methodology only.

When two sources conflict, identify the precise conflict and apply the governing authority. Do not silently reconcile conflicting documents by inventing a third interpretation.

If a required decision remains unresolved, report the alternatives and stop only the affected work.

---

### 4. Phase G shared architecture contract

#### 4.1 Scenario capability registry

The scenario configuration is the authority for scenario capabilities.

- Read real configuration keys; never guess them.
- Resolve scenario behavior through the existing configuration matrix.
- Use `panels.*` and `widgets[]` for panel registration and availability.
- Gate shared behavior through declared capabilities, not scenario identity.
- Do not add per-scenario component branches, test-fixture branches, CSS classes, or prop contracts based on scenario names or labels.
- A fifth scenario must be supported by configuration without editing a component.

G1's capability audit is the evidence base for later slice plans. It must distinguish what configuration declares from what persistence, write paths, and UI actually implement.

Use the status vocabulary `shipped`, `partial`, `missing`, `blocked`, and `not applicable`. Include the evidence and reason for each classification.

#### 4.2 Shared Next Decision contract

G1 establishes the contract consumed by G2–G6.

The recommendation follows the documented sequence:

**Current state and objective → Recommended action and benefit → Best alternative and trade-off → Provenance-backed explanation → Conditions that change the recommendation.**

The contract must distinguish a valid recommendation from an inability to rank actions.

- A valid recommendation contains an action, destination, and source-supported benefits.
- Alternatives state their opportunity costs.
- Explanation rows carry provenance states and explanatory titles.
- Refusal identifies why ranking is impossible and which required inputs are missing.
- `recommended` and `refusal` are mutually exclusive.
- No score or numeric confidence is permitted.
- No recommendation ships without a derivable reason line.

The five-element contract in G1 is authoritative. Any subsequent slice that proposes a shape change must explicitly identify the change, its compatibility implications, and its tests before implementing it.

G2 and G4 may extend the contract only within their approved scope. They must not independently redefine the shared type or create competing representations of the same fact.

#### 4.3 Held computations

The following remain prohibited under `ADR-0020` and the existing absence contracts:

- Race win probabilities.
- Readiness bands and risk percentages prohibited by `ADR-0016`.
- Per-training yield and failure percentages.
- Inheritance computation.
- Shop-item recommendations.
- Grand Concert mechanics not yet authorized.
- Numeric confidence or scores.
- Unsupported recommended timing or projected benefit.
- Unsupported opponent-strength projections.
- Any additional predicted outcome not supported by an approved source.

A held value must follow the applicable documented absence behavior. Where a named `N/A` is required, its `title` must identify the precise missing evidence or authorization. Do not use an invented fallback number, dash, or generic "unavailable" message.

#### 4.4 Provenance and terminology

Use the existing `ProvenanceBadge.vue` from D4. Do not redefine its glyphs, states, or semantics.

Every derived figure must expose its provenance. Every absence must explain why the value cannot be shown. Labels must distinguish recorded facts, calculations, estimates, unknowns, and community guidance.

Use the repository's approved terminology:

- Support Cards
- Scouts
- Veteran Umamusume
- Pal
- Wit
- Sparks
- Trackblazer

Use the exact approved label `2026-07-01 Global rework`.

Do not introduce alternative terminology merely because it is more familiar.

---

### 5. Skills and execution method

Skills are methods, not authorities. Read their actual instructions and report missing skills. Do not claim to have used a skill that was not available or consulted.

#### Baseline method

Use the following skills at the relevant phase:

**Planning**

- `using-agent-skills`
- `planning-and-task-breakdown`
- `spec-driven-development`
- `infer-conventions`

**Implementation**

- `incremental-implementation`
- `test-driven-development`
- `laravel-best-practices`
- `testing-best-practices`
- `frontend-ui-engineering`
- `tailwindcss-development`
- `constraint-driven-development`
- `source-driven-development`

**Review and verification**

- `doubt-driven-development`
- `code-review-and-quality`
- `browser-testing-with-devtools`

**Copy and layout**

- `antislop`
- `antislop-ui`
- `antislop-human`
- `antislop-copywriting`
- `antislop-layoutmobile`
- `antislop-code` for comments.

Load slice-specific skills from the relevant G specification. Do not reload every skill's full instructions for every slice if the applicable instructions are already available in the active context. Never assume an uninstalled skill is installed.

`freeze`, `careful`, `design-review`, and `impeccable` are optional. Report their availability only when relevant.

Do not install new packages to satisfy skill advice.

---

### 6. Required workflow for each slice

#### Phase A — Reconnaissance and plan

Before editing, produce a slice plan containing:

1. Scope and acceptance criteria.
2. Verified prerequisites and dependencies.
3. Source-to-field mapping for every material number, label, calculation, and recommendation.
4. Current state of the relevant backend and frontend contracts.
5. Exact files expected to change.
6. Props, types, configuration, and persistence contracts.
7. Tests to add or update.
8. Known baseline failures and applicable gates.
9. Open questions and owner-controlled decisions.
10. Explicit exclusions, especially held mechanics.

Identify whether each requirement is supported by existing data, derivable from supported data, blocked by missing data, or requires owner authorization.

Do not begin implementation while a required contract or file-ownership decision is unresolved.

#### Phase B — Test-driven implementation

1. Write or update the focused failing test.
2. Implement the smallest backend or configuration change required.
3. Implement the shared component or scenario panel using established repository patterns.
4. Integrate it through the existing page and configuration contract.
5. Verify that the focused test passes.
6. Add regression tests for absent data, invalid states, configuration changes, and boundary conditions.
7. Add the browser spec for the affected screen.

Use the existing routes and Form Requests for writes. Vue submits through the established form contract and displays validation errors; it does not replace backend validation.

Do not use `uses()` or `withoutVite()` in test files. Do not run `vue-tsc`. Do not introduce a package or migration without authorization.

#### Phase C — Accessibility and browser verification

Each affected panel must have loading, empty, and error states. Each state must explain what is missing, why it matters, and what the Trainer can do next.

Verify:

- WCAG 2.2 AA.
- Keyboard access and visible focus.
- Minimum 44px interaction targets.
- Correct labels and `aria-describedby` for errors.
- Polite announcements for non-urgent status changes.
- Reduced-motion preferences.
- Focus trapping, Escape behavior, and focus return for dialogs and drawers.
- 320px reflow.
- 200% zoom.
- No drag-only interaction.
- State communicated by text and glyph, not colour alone.
- Sticky elements do not obscure focused controls.
- Meters display both a numeric value and a labelled bar.

Write or update `tests/browser/<screen>.spec.ts`. Include rendered-copy assertions, keyboard paths, the 44px sweep, and axe A/AA checks if the existing dependency supports them.

If KI-88 prevents a meaningful browser run, report the affected specs and the blocker. Do not weaken assertions, add test-only submit controls, or misclassify the failure as a slice pass.

#### Phase D — Doubt-driven review

Challenge every non-trivial implementation decision.

Ask:

- Is the displayed fact recorded, calculated, estimated, or unknown?
- Can the calculation be reproduced from the cited source and inputs?
- Is a design proposal being mistaken for an implemented capability?
- Does the UI imply a recommendation, prediction, or guarantee that is not authorized?
- Does a missing value produce a truthful refusal?
- Does the new abstraction duplicate an existing service or state representation?
- Does the component depend on a scenario name?
- Is any write bypassing the existing persistence contract?
- Is any test passing because of a fabricated fixture or hard-coded scenario assumption?

Fix demonstrated defects within scope. Report unresolved owner decisions without silently resolving them.

#### Phase E — Documentation and final review

Update `SCREEN_SPEC.md` §2 with every screen added or materially changed. Each entry must include loading, empty, and error behavior.

Review the complete diff for:

- Unrelated changes.
- Unapproved migrations, routes, packages, or schema changes.
- Scenario-name branching.
- Unsupported mechanics and values.
- Missing provenance.
- Accessibility regressions.
- Missing regression tests.
- Documentation that disagrees with the implementation.

Do not stage or commit changes unless explicitly authorized.

---

### 7. Required hand-off sequence

Run the following gates in order where the repository and environment permit:

1. Targeted Pest tests.
2. `php artisan test --compact`
3. `vendor/bin/pint --dirty --format agent`
4. `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`
5. `npm run typecheck`
6. `npm run build`
7. `npm run test:browser`
8. `composer lore`
9. `composer lore-code`
10. `php artisan migrate:status`

For every gate, report:

- The exact command.
- Exit status.
- Relevant counts or output.
- Whether it passed, failed, or was blocked.
- Whether the failure is pre-existing, caused by the slice, or undetermined.
- The evidence supporting that attribution.

Do not claim a gate is clean when it was skipped, timed out, or did not execute.

Use the actual development database for `php artisan migrate:status`, as required by `AGENTS.md` §9. Do not substitute a scratch database.

For `composer lore` and `composer lore-code`, report every relevant hit and give a specific ruling per hit. Do not suppress hits merely to obtain a clean result.

#### Gate attribution

A baseline failure may be classified as pre-existing only when the evidence supports that classification. If the cause is uncertain, mark it `undetermined`.

A blocked browser gate does not erase passing unit tests. Passing unit tests do not imply browser acceptance.

A slice may be reported as **implementation complete, acceptance blocked** when its own deliverables are verified but an authorized external prerequisite prevents full verification. It must not be reported as fully accepted.

---

### 8. Hard rules

- Copy existing page, controller, component, and test patterns.
- New pages require an appropriate controller method using `Inertia::render`, explicit arrays rather than Eloquent models, a route, and a Pest `assertInertia` test.
- Writes use existing routes and Form Requests.
- Do not trust client-supplied costs, balances, or other authoritative values.
- No new npm or Composer package.
- No unapproved migration or enum value.
- No `vue-tsc`.
- No `uses()` or `withoutVite()` in tests.
- No unapproved ADR creation. Draft proposed ADR text in the report if necessary.
- No broad refactoring or unrelated cleanup.
- No weakening existing tests to conceal failures.
- No committing or staging shared-tree changes without explicit authorization.
- No unsupported mechanics, predictions, numeric scores, or invented defaults.
- No component-level scenario-name branching.

---

### 9. Final report contract

Every slice must finish with the following sections.

**Determination** — State whether the slice is accepted, implementation complete with acceptance blocked, incomplete, or stopped at a prerequisite.

**Evidence** — Summarize the authoritative source evidence and the source-to-field decisions that governed the implementation.

**Files changed** — List every changed, added, and deleted file with a brief explanation. Identify changes made by the slice separately from pre-existing or concurrent changes.

**Architecture and contracts** — Report the props, types, service boundaries, configuration contracts, persistence paths, and any backward-compatibility changes.

**UI behavior** — Describe loading, empty, error, refusal, and normal states, plus accessibility behavior.

**Mechanics explicitly not implemented** — List held, unsupported, blocked, or deliberately excluded behavior.

**Tests and gates** — List every command actually executed, its result, the relevant output, and failure attribution.

**Deviations** — List each deviation from the approved plan and explain why it was necessary.

**Owner decisions** — List unresolved decisions, the evidence, the available alternatives, and the affected work. Do not convert an open question into an unapproved implementation.

**Safety and concurrency** — Report shared-tree ownership checks, files protected from concurrent changes, and any remaining risk.

**Git and commit status** — Report the actual Git state. Never claim a commit exists if none was made.

**Skills used** — Name each skill actually consulted and describe its contribution. List unavailable or skipped skills and the reason.

**Explicit stop** — State whether work may proceed to the next dependent slice. Name any blocker that prevents continuation.

---

### 10. Phase G completion criteria

Phase G is complete only when:

- G1–G6 satisfy their slice-specific acceptance criteria.
- The shared Next Decision contract is consistent across all four Global scenarios.
- Configuration-based capability gating is tested, including a fifth scenario configured in a test without component edits.
- The FD-3 objective and deadline behavior is covered.
- The FD-4 reconciliation behavior preserves correction history.
- All scenario-specific surfaces satisfy their evidence and absence contracts.
- G7's cross-scenario audit is complete.
- `SCREEN_SPEC.md` documents every added or changed screen and its empty, loading, and error states.
- Every demonstrated inconsistency is fixed with a regression test or reported as an owner decision.
- The Grand Concert baseline strip still renders when all relevant panels are off, satisfying G-41 and preserving the `partially_documented` ruling.
- The full acceptance sequence has been executed and its results reported honestly.
- No unresolved prerequisite, unauthorized contract change, or unreported blocking failure is concealed.

**Start with preflight and the slice plan. Do not begin implementation until the applicable prerequisites and file ownership are verified.**

---

## PART 2 — SHARED ARCHITECTURE CONTRACT

The shared architecture contract is §4 of the master execution contract above. Every G slice consumes it. Two additions apply.

**Contract migration is authorized only by the master.** If a G slice needs to change the Next Decision shape, the change must be:

1. Named in the slice's plan phase.
2. Justified against the contract's compatibility tests.
3. Reviewed against every parallel consumer (G2–G6).
4. Approved by the owner before implementation.

No slice may independently redefine `NextDecision`, `RecordedAction`, `ObjectiveRow`, or any shared type.

**Test fixtures must not invent scenario identity.** A fifth-scenario test constructs the scenario from `config()->set` with keys drawn only from existing config vocabulary. A fixture may not use a scenario name, a scenario key literal, or a scenario-specific branch to prove scenario-agnosticism.

---

## PART 3 — SLICE SPECIFICATIONS

Each specification assumes the master execution contract has been read, preflight has been completed, and file ownership is established. Each specification ends with an **Explicit stop** condition.

---

### PROMPT G1: Capability audit and the shared Next Decision contract

**Scope:** FD-1 audit; FD-2 shared contract; scenario panel registry consumed by G2–G6. Depends on D8, E1.

**Preflight:** Verify D8 and E1 landed. Verify `config/scenarios.php` is the version current in the tree. Verify `ProvenanceBadge.vue` from D4 exists. Verify the Cockpit's scenario region is E1's `ScenarioPanel.vue` mount.

**Freeze points:** This slice produces the shared contract G2–G6 consume. Implementation may not begin until the contract shape is confirmed in the plan phase against the four Global scenarios and against a fifth-scenario `config()->set` fixture. Any subsequent change to `NextDecision`, `RecordedAction`, or `ObjectiveRow` is a contract migration (see Part 2).

**Add-on skills (beyond the baseline):**

- `api-and-interface-design` — fix the types in `resources/js/types.ts` once.
- `constraint-driven-development` — G-33 is the headline gate; make it a test.
- `code-simplification` — one decision service, one component, zero per-scenario conditionals.
- `performance-optimization` — the payload is read on every Cockpit render; avoid N+1.

**Source reading:** `frontend-development-plan.md` §8 ("Recommendation contract (D8)"), §9a FD-1 and FD-2; `design-2.0.md` §2, §19, §25–28; `screen-spec-2.0.md` §12, §13, §29–30; `provenance-and-absence-contracts.md` §1, §2, §9 (ruling 1c); `config/scenarios.php` (all four entries); `app/Services/Advisor/TrainerAdvisor.php`; `config/advisor.php`; `resources/js/components/career/RecommendationCard.vue`, `AdvisorRail.vue`, `ActionGrid.vue`, `CareerStatePanel.vue`, `CareerHeader.vue`.

#### Evidence gate (before implementation)

Produce the FD-1 audit as a slice-plan table. For each of the four Global scenarios, list:

- The capability the config declares — every relevant key, read from the tree, not from this prompt.
- The state the run actually records — from `training_runs`, `turn_entries`, `turn_events`, `race_entries`, `scenario_slots`, `deck_slots`, `veterans`.
- The write paths that exist — from `routes/web.php` and the Form Requests.
- A status classification per capability: `shipped` / `partial` / `missing` / `blocked` / `not applicable`.

The audit is the evidence base for G2–G6. If a capability is not in the tree, it is `missing`; do not propose it as `shipped` because a design document describes it.

Then freeze the `NextDecision` contract shape. Confirm the exact TypeScript interface, the backend output, the refusal states, and the component props against the audit. The contract is frozen when:

- The four Global scenarios each produce a valid payload against the current tree.
- A fifth scenario constructed by `config()->set` produces the same shape with zero component edits.
- The refusal payload fires when the tree cannot rank (missing energy, missing `build_target`, no objective).
- The contract contains no `score` and no `confidence` field.

Report the freeze confirmation as part of the slice plan. Do not proceed to implementation until it is recorded.

#### Build

**FD-1 audit** — the table produced in the evidence gate is the deliverable. It lives in the slice plan.

**FD-2 backend service** — `app/Services/Advisor/NextDecision.php` or the same family as `TrainerAdvisor` (read both and pick the smaller-diff option; report the choice). Inputs: run, resolved scenario config, current state, recorded objectives/deadlines, `build_target`. Outputs the frozen shape:

```ts
interface NextDecision {
    priority: string
    recommended: { action: string; action_url: string; benefit: string[] } | null
    alternatives: Array<{ action: string; action_url: string; tradeoff: string }>
    why: Array<{ text: string; badge: ProvenanceState; title: string }>
    switch_if: string[]
    refusal: { reason: string; missing: string[] } | null
}
```

`recommended` and `refusal` are mutually exclusive. When the engine cannot rank, it returns `refusal` and no `recommended`. The confidence element is expressed as one or more `why` rows carrying a `ProvenanceBadge`, not a summary label.

**FD-2 component** — `components/career/NextDecision.vue`. Renders the five-element shape. `why` rows render as a `<dl>`, each row's `ProvenanceBadge` beside its text. The refusal state renders the design-2.0 §2 distinction: "No suitable action found" vs "Not enough information" are two separate copies.

**Cockpit integration** — replace the current `RecommendationCard` + `AdvisorRail` composition in `Cockpit.vue` with `NextDecision.vue`. Keep `AdvisorRail` as the slot for the reasons list. `ActionGrid` entries link to decision screens and to any panel the scenario declares. Do not redesign the Cockpit layout — the three-column desktop shape is D8's and stands. `band` is retired: energy is either recorded (and the advisor ranks) or absent (and the advisor refuses).

**Shared building blocks** — `ScenarioStatusBadge`, `ResourceMeter`, `AlertRow` already exist from E1. Reuse them. Build any additional shared block only if two later slices would otherwise invent one.

#### Tests

- Pest: same `NextDecision` payload shape across all four Global scenarios.
- Pest: a fifth scenario injected via `config()->set` produces the same shape without a component edit.
- Pest: refusal fires when energy is absent and when `build_target` is null; `recommended` is null in both.
- Pest: key-set assertion proves no `score` and no `confidence` field exists in the payload.
- Grep: no scenario names, `scenario.label` comparisons, or key literals in conditionals.
- Playwright: five-element shape renders; each `ProvenanceBadge` has its accessible name; refusal copy distinguishes the two cases; 320px reflow; 44px sweep.

#### Acceptance

The contract shape is frozen, tested against four scenarios plus a fifth, and the audit table is complete. No later slice has started.

#### Owner questions

1. Does `NextDecision` replace `TrainerAdvisor`, or wrap it? Read both; pick the smaller-diff option; report the choice.
2. The `priority` field is a single sentence. Should the multi-objective hierarchy from `screen-spec-2.0.md` §29 be exposed as a ranked list, or kept as the one-line current priority? Report both shapes.

#### Explicit stop

Stop after the audit, the contract, and its tests are reported. Do not begin G2. Report the frozen contract shape for owner review; G2 depends on this being stable.

---

### PROMPT G2: FD-3 objectives and deadlines + FD-4 action reconciliation

**Scope:** Objective and deadline context (FD-3); action reconciliation loop (FD-4). Depends on G1's frozen contract.

**Preflight:** Verify G1's contract is frozen and the compatibility tests pass. Verify no other session is editing `Cockpit.vue`, `RecommendationCard.vue`, `AdvisorRail.vue`, or `CareerTimeline.vue`.

**Freeze points:** This slice extends the `NextDecision` contract with `objectives[]` and `recorded_action`. Both are contract migrations; both are named in the plan phase and reported before implementation.

**Add-on skills:**

- `spec-driven-development` — write the objective state machine and the reconciliation state machine before coding.
- `doubt-driven-development` — challenge the deadline window against the E2 ruling ("due and not recorded").
- `laravel-best-practices` — reconciliation writes through existing `runs.turns.*` and `runs.events.*` routes.
- `api-and-interface-design` — one typed history-entry shape shareable by G5, G6, D14, D15, D16.
- `antislop-human` — the "enter actual result" prompt announces politely and never steals focus.

**Source reading:** `frontend-development-plan.md` §9a FD-3, FD-4; `design-2.0.md` §2 (Forecast/Commit/Reconcile), §22, §36, §37, §38; `screen-spec-2.0.md` §12 ("Explicit states"), §18, §19, §22, §32; `provenance-and-absence-contracts.md` §9 (ruling 7a); `SCREEN_SPEC.md` `SCR-CAR-017` and `SCR-CAR-018`; `app/Http/Controllers/Career/TimelineController.php`; `resources/js/components/career/CareerTimeline.vue`; `resources/js/pages/Career/Timeline.vue`; `resources/js/components/career/RunRaceStrip.vue`.

#### Evidence gate

Before implementation, establish:

- The objective classes the config declares, and where each class comes from (`race_catalog_slots`, `training_runs`, recorded `TurnEvent` rows). Do not invent a class.
- The priority ordering. If config does not declare one, propose the ordering as an open question and stop the priority line before implementation; the rest of FD-3 proceeds.
- The write routes that will record the actual result for each action type. Training → `runs.turns.update`. Race → `runs.races.store`. Event → `runs.events.store`. If a route for an action type does not exist, that action type's reconciliation is out of scope and reported.

#### Build

**FD-3 objectives.** Add `objectives[]` to the `NextDecision` payload and a shared `ObjectiveRow.vue`. Objective shape: `{ label, state, due_turn, due_label, consequence, source, badge, title }`. State ∈ `upcoming` | `current` | `completed` | `missed` | `unknown`. Priority is a config value at `config/scenarios.php`'s top level (`objective_priority`), inherited by a fifth scenario.

Deadline context: a mandatory objective inside the current window fires a `Critical` `AlertRow` from E1, on the same rule E2 uses (`is_mandatory` and `isAtOrBeforeTurn()` and not recorded). Copy reads "due and not recorded". The next mandatory objective and its turn are always shown when one exists.

**FD-4 reconciliation.** Extend `NextDecision` with `recorded_action: RecordedAction | null`.

```ts
interface RecordedAction {
    id: number
    decision: NextDecision                       // frozen snapshot at the time of the action
    action: string
    expected: Array<{ text: string; badge: ProvenanceState }>
    actual: Array<{ text: string; badge: ProvenanceState }> | null
    reconciliation: { match: boolean; note: string } | null
    recorded_at: string
    corrected_from: number | null
}
```

The `decision` field is a snapshot of the recommendation **at the time of the action**. It is not a recomputation of the current recommendation. Define how actual results and corrections append history before coding the UI; the history append is tested against the existing `turn_entries`/`turn_events` tables and never rewrites a previous row.

Three states: `action under review` (forecast shown), `action recorded, actual result pending` (grey out, "Enter actual result"), `action recorded, actual result entered` (reconciliation shown). Recorded actions display on the Cockpit's left rail, capped at three, with a link to D14's Timeline for the full history.

**No undo in this slice.** Render the recorded action with a "Correct" link that opens the existing correction form on D8's Cockpit. Report the undo contract as unresolved.

#### Tests

- Pest: objective state machine — upcoming, completed, missed, unknown — each with its own row shape.
- Pest: priority line reads from the config-declared ordering; a fifth scenario inherits it.
- Pest: reconciliation state machine — recorded without actual, recorded with actual, corrected — three separate payload shapes.
- Pest: correction appends a new entry and does not silently rewrite the previous one (assert both rows exist in storage).
- Pest: `recorded_action.decision` is the snapshot, not the current recommendation (mutate the run state after recording; assert the stored snapshot is unchanged).
- Grep: no `undo` route, no `score`, no `confidence`, no `win_probability`.
- Playwright: objective row state as glyph plus text; reconciliation shows expected vs actual side by side; polite announcement; 320px reflow; 44px sweep.

#### Acceptance

FD-3 and FD-4 land with their tests. The priority ordering question is either resolved by config or reported open. The undo contract is reported open. No other slice's contract is modified.

#### Owner questions

1. Objective priority ordering. If config does not declare it, propose the ordering from `screen-spec-2.0.md` §29 and stop the priority line until the owner rules.
2. Undo contract. The plan's FD-4 exit condition names it but does not settle the shape.
3. Recorded-action cap on the Cockpit (three). Confirm or change.

#### Explicit stop

Stop after FD-3 and FD-4 land. Do not begin G3–G6 until the contract extensions are reported and the priority ordering is either config-declared or explicitly deferred.

---

### PROMPT G3: URA Finale decision surfaces

**Scope:** Happy Meek Duel Panel; Career Milestone Tracker; Pre-Final Review. Extends E2's URA panel; does not replace it. Depends on G1, G2.

**Preflight:** Verify G1 and G2 landed. Verify the run's write path records duel appearances — read `turn_entries`, `turn_events`, and the `TurnEvents/*Payload` classes. If the write path does not record a duel appearance, the duel overlay is display-only and driven by a Trainer-entered flag; **do not add a database column**. Report as an owner question.

**Freeze points:** None for the shared contract. Any change to `NextDecision` from this slice is unauthorized.

**Add-on skills:**

- `source-driven-development` — every field traces to `docs/scenarios/01-ura-finale.md`, `UMAMUSUME_REFERENCE.md` §2.2.1, `config/scenarios.php` `ura_finale`, or a recorded run column. The plan's §11 exclusions are the exclusion list.
- `doubt-driven-development` — challenge every number on the Duel Panel. The Racing Spirit hint award is stated; the per-stat gain values are not. The Aoi Kiryuin bonus is qualitative.
- `antislop-ui` — URA is deliberately quiet (`design-2.0.md` §26).
- `frontend-ui-engineering` — the Duel Panel is a modal with focus trap, Escape, and focus return.
- `antislop-copywriting` — `N/A` titles name the reason precisely.

**Source reading:** `docs/URA-Finale-plan.md` §8, §13, and §11; `screen-spec-2.0.md` §18; `design-2.0.md` §26, §29, §31; `config/scenarios.php` `ura_finale`; `docs/scenarios/01-ura-finale.md` §77–84; `resources/js/components/scenario/UraPanel.vue`.

#### Evidence gate

Before implementation:

- Prove which recorded field, if any, establishes a duel appearance. Cite the model and the write path.
- Prove which fields the recorded turn carries (options, selected stat, outcome).
- Confirm the fan-gate thresholds (60k / 70k / 120k, and dirt variants) are in config or in a recorded entry. If not, propose config placement as an owner question; do not hard-code them.

#### Build

**Happy Meek Duel Panel.** Triggered by a recorded duel appearance. Content: the three duel options (only if the recorded turn carries them; otherwise the selected stat only), the Racing Spirit hint tied to each outcome (names from `docs/URA-Finale-plan.md` §8's table), the win-chance icon glyph only (no percentage, no band), the duel count (`n / 6 wins`), and the escalation reward (`Past My Limits`, 200+ SP). The enhanced-spark rate is `N/A` with a title. Focus trap, Escape, focus return.

**Career Milestone Tracker.** An `<ol>` with glyph plus text (`●` completed, `◉` current, `○` upcoming, `×` missed). Milestones from the recorded calendar and the config's fan-gate thresholds. Each milestone shows label, due turn, state, consequence (when the source states one), and a link to the decision screen that addresses it.

**Pre-Final Review.** A section of the milestone tracker that appears within a config-declared pre-final window. Content: recorded strengths (stats above the finale's distance floor), known deficits, unresolved requirements. The distance floors come from `docs/URA-Finale-plan.md` §3; they are read from config or recorded Trainer entries, not hard-coded.

#### Tests

- Pest: Duel Panel renders the recorded options, the hint table, the duel count, and the `N/A` for the spark rate.
- Pest: milestone tracker reads from config and recorded data; a missed milestone is marked `missed`.
- Pest: Pre-Final Review fires only within the configured window.
- Grep: no hard-coded 60,000 / 70,000 / 120,000; no numeric win chance; no "recommended stat".
- Playwright: Duel Panel opens/closes by keyboard with focus return; tracker renders each state as glyph plus text; 320px reflow; 44px sweep.

#### Acceptance

The three surfaces land with their tests. No database column was added. The turn-write-path limitation is reported as an owner question.

#### Owner questions

1. Does the turn-write path record duel appearances? If not, does the owner approve a Trainer-entered flag on the turn (nullable column) or a display-only panel driven by a checkbox on the training decision screen?
2. Fan-gate thresholds: config or Trainer-entered? Recommend config; do not decide.

#### Explicit stop

Stop after the three surfaces land. Do not proceed to G4 until the duel-appearance evidence question is answered.

---

### PROMPT G4: Unity Cup decision surfaces

**Scope:** Team Board; Burst Bank; Team Race Briefing; Tournament Readiness; dual-value readout. Extends E3's Unity Cup panel; does not replace it. Depends on G1, G2.

**Preflight:** Verify G1 and G2 landed. Verify the run's team-write path exists — check `runs.deck.sync` and any team-write route. If no team-write route exists, the Team Board is read-only; report as an owner question.

**Freeze points:** The dual-value readout is a **contract migration** of `NextDecision.recommended.benefit`. The current type `string[]` does not express two separately labelled values. Specify the typed representation **before** changing the component, and test that the additional data appears only when the resolved capability declares `team`. Do not modify the shared contract while G3, G5 or G6 are consuming it. If parallel work is active, this slice waits.

**Add-on skills:**

- `source-driven-development` — every field traces to `docs/Unity-Cup-plan.md`, `docs/scenarios/02-unity-cup.md`, `SCENARIO-PUBLISHER-REFERENCES.md` §7, or a recorded run column. The plan's §14 exclusions are the exclusion list.
- `doubt-driven-development` — the dual-value readout is the central abstraction; every number on both sides must trace. Rank thresholds are unpublished (plan §14 item 1); burst payout split is unpublished (item 2).
- `frontend-ui-engineering` — the Team Board is a `<table>` with row-level keyboard reach.
- `antislop-layoutmobile` — the Team Board stacks by column at 320px.
- `performance-optimization` — eager-load team data; the board reads on every Cockpit render.

**Source reading:** `docs/Unity-Cup-plan.md` §1, §6, §8, §8a, §9, §10, §11, §12, §14; `screen-spec-2.0.md` §19; `design-2.0.md` §27, §15, §45; `frontend-development-plan.md` §9a FD-5, §9 E3, §9.1; `config/scenarios.php` `unity_cup` (`spirit_burst_bands`, `burst_payout_timing`); `resources/js/components/scenario/UnityCupPanel.vue`; `TrainingRun::latestTeamRank()`, `spiritBurstRoster()`, `raceEntries()`.

#### Evidence gate

Before implementation:

- Confirm the config keys that drive the Team Board: rank ladder, facility-level source, coverage threshold. Do not guess.
- Confirm the team write path.
- Confirm the `NextDecision.recommended.benefit` shape and its consumers. The dual-value readout's typed representation is named in the plan phase and reported before implementation.

#### Build

**Team Board** (permanent region of E3's panel). Four regions: overall team rank; facility progression (rank → level, from config); team stat ranks (five-letter grid, from recorded data); race coverage (distance-by-distance state, computed from recorded roster aptitudes against the config threshold). The coverage threshold is config; cite the key.

**Burst Bank** (in E3's panel). Per member: gauge (value and bar, labelled); normal burst ready / Extreme unlocked; best facility for cashing (config + member stat focus; `Calculated`). Do NOT show "recommended timing" or "projected benefit" — held.

**Team Race Briefing** (drawer). Content: next team race, recorded circle reading, win condition from config or the reference doc, Alarm Clock retry availability from config. No opponent projection; no "rank if won".

**Tournament Readiness** (section of the panel). Race preview per distance (same coverage rule as the Team Board); unresolved risks; one "Insufficient data for opponent strength projection" line.

**Dual-value readout** (contract migration). Add a typed `team_value: Array<{ label: string; value: string; badge: ProvenanceState }>` field to `NextDecision.recommended`. Component renders two labelled columns ("Trainee value" / "Team value"). Field appears only when the resolved capability declares `team`. **Do not modify the shared contract while G3, G5 or G6 are actively consuming it.** If parallel work is active, this slice's contract migration is serialized before G5/G6.

#### Tests

- Pest: Team Board renders from config and recorded data; missing team stat reads `N/A`.
- Pest: coverage rule uses the config threshold; the three states are reachable **given** the config declares the threshold (do not assert reachability if the threshold is not config-declared).
- Pest: Burst Bank's best-facility line is `Calculated`; its reason names the member stat focus and the config key.
- Pest: dual-value readout fires only when the resolved capability declares `team`.
- Grep: no "recommended timing", "projected benefit", "rank if won", "win probability".
- Playwright: Team Board table with row-level keyboard reach; Burst Bank meters value+bar; Team Race Briefing opens/closes with focus return; 320px stack; 44px sweep.

#### Acceptance

Four surfaces land with their tests. The dual-value readout contract migration is reported and tested before G5 or G6 proceed.

#### Owner questions

1. Team member writes: supported or read-only? If read-only, the board says so.
2. Rank thresholds: unpublished (`docs/Unity-Cup-plan.md` §14 item 1). Read from run; do not project.
3. Dual-value readout shape: two columns or combined line? Report candidates.

#### Explicit stop

Stop after the four surfaces land and the contract migration is reported. Do not begin G5 until the migration is confirmed stable.

---

### PROMPT G5: Trackblazer planning and race surfaces

**Scope:** Route Planner (pre-run planning panel); Race Calendar Panel; Race Fatigue Panel. Extends E4's Trackblazer panel; does not replace it. Depends on G1, G2. **Not** dependent on G4's contract migration if the two slices do not touch the same contract fields.

**Preflight:** Verify G1 and G2 landed. Verify G4's contract migration is either complete or does not intersect this slice's work. Verify the calendar write path (`runs.races.store`).

**Freeze points:** None for the shared contract if G4's migration is separate. If G4's migration intersects, coordinate.

**Add-on skills:**

- `source-driven-development` — every field traces to `docs/Trackblazer-plan.md`, `SCENARIO-PUBLISHER-REFERENCES.md`, `docs/scenarios/03-trackblazer.md`, `config/scenarios.php` `trackblazer`, or a recorded run column. The plan's §15 exclusions are the exclusion list.
- `doubt-driven-development` — the fatigue table's caveats must be visible in the panel itself, not only in the source.
- `frontend-ui-engineering` — the Route Planner is an aligned property list; the Race Calendar Panel is an `<ol>` grouped by period.
- `antislop-copywriting` — flags worded exactly ("5 consecutive races planned in Classic spring (fatigue risk)").
- `performance-optimization` — one eager-loaded query per period.

**Source reading:** `docs/Trackblazer-plan.md` §1, §3, §6, §7, §8, §10, §12, §15, §17, §18; `screen-spec-2.0.md` §20; `design-2.0.md` §28, §21, §45; `config/scenarios.php` `trackblazer`; `resources/js/components/scenario/TrackblazerPanel.vue`; `docs/scenarios/03-trackblazer.md`.

#### Evidence gate

Before implementation:

- Confirm the placement-modifier table exists with line citations (`SCENARIO-PUBLISHER-REFERENCES.md` lines 104–110). If not, the floor derivation is not built.
- Confirm the checkpoint profiles' config keys (KI-15 records the conservative default). If only `standard` renders, do not silently render the other two.
- Confirm the fatigue table source and its two caveats.
- Confirm the year-end gate thresholds' location (config or Trainer entry).

**Provenance of each calculation** (must be named in the plan phase):

- Race plan entries per period: **Trainer-entered**.
- GP per grade: **sourced** (`SCENARIO-PUBLISHER-REFERENCES.md` lines 92–100).
- Placement-modifier arithmetic: **calculated** from the sourced modifiers and the entered plan.
- Fatigue odds: **community-sourced** (`SCENARIO-PUBLISHER-REFERENCES.md` lines 229–242), with the two caveats visible.
- A deterministic floor is not a projected outcome. Do not label the floor with a projected-outcome word.

#### Build

**Route Planner.** Content: three checkpoint periods with the current period's GP runway (needed from config, planned from Trainer entry, floor from the arithmetic); epithet routes with progress; flags (consecutive-race density, year-end gate, unschedulable rival races). The floor derivation uses the placement modifiers applied to the planned races' GP values; cite the source lines. Prominent at low turn count; collapses after the first checkpoint.

**Race Calendar Panel.** Extends E4's calendar region. Next three races with distance/surface/grade, GP/coin values, and skip consequences (negative GP, epithet route effect, coin loss). Skip consequences are `Calculated` (derived comparison against the period target).

**Race Fatigue Panel.** Collapsible. Current consecutive-race chain; relevant row of the fatigue table; the two caveats as **visible text in the panel**, not only in a `title`; guaranteed recovery events as a read-only list with source citation.

#### Tests

- Pest: Route Planner's floor derivation from the placement modifiers and the entered races.
- Pest: Epithet route progress reads from recorded races and config.
- Pest: Race Fatigue Panel's two caveats render as visible text.
- Pest: Race Calendar Panel's skip consequences are `Calculated`; no win probability appears.
- Grep: no "win probability", no "expected outcome", no "predicted".
- Playwright: three periods render with the current period emphasised; skip consequences readable; chain row matches recorded count; 320px reflow; 44px sweep.

#### Acceptance

The three surfaces land with their tests. The provenance of each calculation is named. KI-15 is either resolved (if the owner rules) or reported open.

#### Owner questions

1. Checkpoint profiles: does this slice render the other two tracks, or keep the conservative default? KI-15 is open.
2. Route Planner location: wizard step or cockpit panel? The wizard is settled at six steps.
3. Year-end gate thresholds: config or Trainer entry?

#### Explicit stop

Stop after the three surfaces land. Do not begin G6 until G5's provenance table is reported.

---

### PROMPT G6: Trackblazer cockpit surfaces

**Scope:** Shop Drawer; Resource Ledger; Epithet Tracker. Extends E4's Trackblazer panel; does not replace it. Depends on G1, G2, G5.

**Preflight:** Verify G5 landed. Verify the purchase write route (`runs.purchases.store`) and its Form Request.

**Freeze points:** None for the shared contract.

**Add-on skills:**

- `source-driven-development` — the shop catalogue rows come from `config/scenarios.php` `trackblazer.shop`; item effects trace to `SCENARIO-PUBLISHER-REFERENCES.md` lines 383–453.
- `doubt-driven-development` — every shop element is challenged for an implied recommendation. Community guidance is labelled `Community`; the plan's derivations are labelled `Calculated`; game rules are `Confirmed`.
- `security-and-hardening` — purchases write through `runs.purchases.store`; the Form Request is the authority; do not trust client cost or coin totals.
- `frontend-ui-engineering` — the Shop Drawer traps focus, closes on Escape, returns focus.
- `antislop-copywriting` — item rows as exact labelled values. Community tags visibly separate from game rules.
- `antislop-layoutmobile` — the drawer at 320px takes the full width; items stack.

**Source reading:** `docs/Trackblazer-plan.md` §11, §12, §19; `screen-spec-2.0.md` §20; `design-2.0.md` §28, §32, §45; `config/scenarios.php` `trackblazer.shop` and its `verified_at`; `resources/js/components/scenario/TrackblazerPanel.vue`; `runs.purchases.store`.

#### Evidence gate

Before implementation, prove the following boundary:

- **Game rules** — sourced from `SCENARIO-PUBLISHER-REFERENCES.md` and marked `Confirmed`.
- **Community guidance** — sourced from uma.guide (`docs/Trackblazer-plan.md` §11) and marked `Community`.
- **Calculations** — derived by this tool from entered data and marked `Calculated`.
- **Recorded balances** — entered or stored and marked `Confirmed`.

Every reserve line ("Keep 100+ coins entering Summer") is community guidance. Do not derive a new reserve number. Do not present a community tag as an authoritative recommendation.

#### Build

**Shop Drawer.** A drawer opened from the Cockpit's action area. Content: coins and refresh timer (recorded); item list from config (item, cost, effect, duration, held count with the cap); game rules block (`Confirmed`, cited); community tags (`Must Buy` / `Skip` labelled with source); reserve guidance labelled `Community`. "Record Purchase" posts through `runs.purchases.store`. Purchased items list below.

**Resource Ledger.** A cockpit panel. Coins, five key items held, current balance, projected inflow (planned races), projected cost (planned purchases), Summer reserve target. Projections `Calculated`; actuals `Confirmed`; reserve target `Community`. The ledger distinguishes actual / planned / hypothetical balances with visible labels.

**Epithet Tracker.** A cockpit panel. Each epithet route from config as a chain. Each race: state (won/remaining/lost/not started) from `raceEntries`. A route with a lost race is greyed with text ("Route broken: Oka Sho not won"). A route with all races won is `completed`. Rival races are shown but marked "cannot be scheduled".

#### Tests

- Pest: shop catalogue renders from config with held count and cap.
- Pest: game rules block cites source lines; each rule `Confirmed`.
- Pest: reserve guidance is labelled `Community`, not `Confirmed` or `Calculated`.
- Pest: Resource Ledger projections `Calculated`; actuals `Confirmed`.
- Pest: Epithet Tracker route state derives from recorded races.
- Grep: no "recommended purchase", "best value", "Twinkle Star Climax" outside a named-absence `title`.
- Playwright: Shop Drawer opens/closes by keyboard with focus return; ledger's three balance states are labelled; epithet route with a lost race is greyed with text; 320px reflow; 44px sweep.

#### Acceptance

The three surfaces land with their tests. The four-way boundary (game rules / community / calculations / recorded) is enforced in code and in tests.

#### Owner questions

1. Shop jump E4 already added: drawer or jump-to-region? Report which shipped and which this slice ships.
2. Reserve guidance numbers: rendered as labelled community guidance, or under a disclosure?

#### Explicit stop

Stop after the three surfaces land. Do not begin G7 until the boundary is reported and the reserve guidance question is reported.

---

### PROMPT G7: Cross-scenario consistency audit and Phase G acceptance

**Scope:** Audit the full chain (Performance → Lessons → Songs → Live Bonuses → Promotional Lives → Grand Concert, plus URA / Unity Cup / Trackblazer decision surfaces) for consistency; fix demonstrated inconsistencies; run Phase G acceptance. Depends on G1–G6.

**Preflight:** Verify G1–G6 landed. Verify the audit table is complete. Verify file ownership across the tree is established.

**Freeze points:** None. This slice fixes demonstrated inconsistencies only; no broad refactor.

**Add-on skills:**

- `spec-driven-development` — write the audit checklist before running it.
- `doubt-driven-development` — fix only demonstrated inconsistencies.
- `code-review-and-quality` — final self-review.
- `constraint-driven-development` — re-run every gate against the full Phase G tree.
- `browser-testing-with-devtools` — full cross-scenario walkthrough at 1280/768/320 with reduced motion and 200% zoom.

**Source reading:** `docs/Our-Grand-Concert-plan.md` Slice 23 and Slice 24; `frontend-development-plan.md` §9a FD-5, §10, §12, §13; all G1–G6 slice plans.

#### The audit must produce evidence rows, not a checklist

Produce a table with one row per (scenario, screen, criterion) triple. Each row must identify the implementation or test that proves the result. A row that cites neither is a defect to be investigated.

Criteria per row:

- Uses the shared `NextDecision` contract from G1.
- No duplicate state representation.
- Observed-vs-derived separation preserved (`ProvenanceBadge` on every computed figure).
- `TurnEvent` behavior consistent across scenarios.
- Read-side behavior deterministic.
- Scenario capability gating correct.
- No unverified mechanic exposed.
- No silently calculated unsupported total.
- Tests exist.
- `SCREEN_SPEC.md` documentation matches.

The audit table is the deliverable. Only demonstrated inconsistencies are fixed.

#### Regression attribution

For each finding, classify:

- **Phase G regression** — introduced by a G1–G6 slice; fixed in this slice with a regression test.
- **Inherited defect** — pre-existing on the tree; reported as an owner question, not fixed unless the fix is within Phase G's authorized scope.
- **Undetermined** — cause unclear; reported as `undetermined` with the evidence.

Do not classify a failure as pre-existing without evidence. Do not classify a failure as a slice regression without evidence.

#### Phase G acceptance pass

Run the hand-off sequence from the master contract, on the Phase G tree:

- `php artisan test --compact`
- `vendor/bin/pint --dirty --format agent`
- `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`
- `npm run typecheck`
- `npm run build`
- `npm run test:browser`
- `composer lore`
- `composer lore-code`
- `php artisan migrate:status`

Report each gate's real output, exit status, relevant counts, and the failure attribution per the master contract's §7.

Re-verify the Grand Concert baseline strip renders with every panel off (G-41) and that `partially_documented` is intact.

#### Diff report

Report the Phase G diff size (files, lines added/removed) without staging or committing. Do not stage or commit. The owner's commit strategy is separate.

#### Acceptance

- The audit table is complete, one row per (scenario, screen, criterion).
- Every demonstrated inconsistency is fixed with a regression test or reported as an owner decision.
- The Phase G acceptance pass is executed and reported.
- `SCREEN_SPEC.md` §2 has a row for every screen G1–G6 added, with empty/loading/error states.
- A "Skills used" report names each skill and what it changed.

#### Owner questions

1. Any inconsistency that requires a decision rather than a fix — reported, not decided.
2. Phase G diff size — reported for the owner's commit strategy.

#### Explicit stop

Stop after the audit and the acceptance pass are reported. Do not stage or commit. Phase G is complete when the owner accepts the audit and the acceptance pass.

---

## Part 4 — Amendments applied

This section records the specific amendments from your review, so a reader can see what changed and why.

### Master contract changes

| Issue | Amendment |
|---|---|
| Prerequisites too broad | Master §2.2 now distinguishes KI-88 blocking acceptance from KI-88 blocking all work. Isolated work may proceed under explicit owner authorization, with baseline failures recorded separately. |
| Shared contract not settled | Master §4.2 declares the G1 contract authoritative; any subsequent change is a contract migration requiring owner approval (Part 2). |
| Infrastructure assumed | Master §3 and every slice spec require verifying files, keys, routes, and columns against the tree, not against the prompt. Each slice's evidence gate names what must be proven before implementation. |
| Tests demand unsupported behavior | Slice G4's coverage-threshold test now fires only when config declares the threshold. Slice G5 separates provenance of each calculation. Slice G7 requires evidence rows, not a checklist. |
| Skill-loading expensive | Master §5 keeps the baseline but instructs not to reload every skill's full instructions per slice. Slice-specific skills are named in each spec. |
| Gates not attributable | Master §7 requires per-gate attribution (pre-existing / slice / undetermined) with evidence. |
| Discovery/design/implementation/release combined | Master §6 separates the phases with explicit stop conditions. Each slice's "Explicit stop" names what must not proceed. |

### Slice amendments

| Slice | Amendment |
|---|---|
| G1 | Freeze point added: contract shape must be confirmed against four scenarios plus a fifth (`config()->set`) before implementation. Compatibility tests are the gate. |
| G2 | `RecordedAction.decision` is a snapshot at action time, not a recomputation. History append is defined before UI. Undo reported open. |
| G3 | Duel overlay conditional on evidence: first prove the recorded field; no silent database column; display-only alternative if the write path does not record. |
| G4 | Dual-value readout is an explicit contract migration; typed representation named before component change; test that it fires only when capability declares `team`; no parallel contract modification. |
| G5 | Provenance of each calculation named (entered / sourced / calculated / community). Fatigue caveats visible in the panel. Floor is not a projected outcome. |
| G6 | Game rules / community guidance / calculations / recorded balances kept visually and semantically distinct. Server-side validation through the existing Form Request. Community never presented as authoritative. |
| G7 | Audit produces evidence rows, not a checklist. Each row cites implementation or test. Only demonstrated inconsistencies fixed. Inherited defects distinguished from Phase G regressions. Diff reported without staging or committing. |

---

## Trainer-Docs-2.0-D-Prompts.md

> **Augmented 2026-10-10 (Round 14, augment-only).** Source: `docs/Trainer-Docs-2.0-D-Prompts.md` (542 lines), reproduced verbatim below with headings demoted one level. The source file remains in place; no citation was repointed and nothing was deleted.
>
> **Consolidated 2026-10-12 (Round 15).** The original was deleted after line-by-line verification (380 of 380 non-blank source lines found in this section, in order, 0 missing). It was tracked and clean at HEAD, so the delete is recoverable from history. Cite this anchor, not the deleted path.

I read SKILL.md. It lists 115 global and 39 project-local skills, and I mapped the useful ones to each slice. The shared context now carries a baseline skill set, a skill precedence rule, and a skills-used report line. Every prompt gets its own add-on skills, each with the reason it applies.

Two choices to know about:

* I only used skills that fit the repo's constraints. I left out `pick-ui-library` (no new packages), the `design-taste` / `high-end-visual-design` family (they push redesigns), and the animation skills (the MOTION dial is 1).
* The shared context now says that if a skill's default advice conflicts with AGENTS.md, an ADR or the plan, the repo wins. `tailwindcss-development` suggests `dark:` variants, for example, and the repo forbids them.

==============================================================  
SHARED CONTEXT (paste before every prompt)
===========================================================================================================

You are working in the Trainer Desk 2.0 repo (Laravel 13, Inertia v3, Vue 3, TypeScript, Tailwind v4, Pest 4, Playwright). You are building ONE slice of Phase D of the Trainer Desk 2.0 frontend.

READ FIRST, in this order:

1. AGENTS.md
2. docs/proposals/frontend-development-plan.md: Global Constraints, sections 2, 3, 8, 12 and 13, plus your slice's row
3. docs/proposals/screen-spec-2.0.md: your SCREEN-nnn section AND the "Product direction corrections" table
4. docs/proposals/design-2.0.md: the sections named in your prompt
5. DESIGN.md and SCREEN_SPEC.md

AUTHORITY ORDER: landed ADRs, then AGENTS.md, then the development plan, then the two design briefs. The briefs are design intent. The plan is the authorization. Skills (below) are METHOD only. If a skill's default advice conflicts with any repo document (for example a skill suggesting dark: variants, a new package, an extra animation, a redesign, a placeholder image, or a recommendation score), the repo wins. Say so in your report and move on.

SKILLS: how to use them  
Load each skill by name at the phase shown. If a skill is not installed, say so in your report and apply its intent anyway.

Baseline skills (every slice):

* using-agent-skills: governs how you find and invoke the rest.
* planning-and-task-breakdown + spec-driven-development: PLAN PHASE. Turn your slice into an ordered task list and a short written spec (files, props contract, tests, open questions) before any code.
* incremental-implementation + test-driven-development: BUILD PHASE. Thin vertical steps: failing props test, controller, page, passing test, Playwright spec.
* laravel-best-practices + testing-best-practices: controllers, Form Requests, query shape, Pest structure, naming, isolation.
* frontend-ui-engineering + tailwindcss-development: Vue components, layout, state, WCAG. Tokens only, never dark: and never zinc-*.
* antislop, antislop-ui, antislop-human, antislop-copywriting, antislop-layoutmobile: apply to every string and layout you write (no filler copy, honest empty states, keyboard and focus, tap targets, no horizontal overflow). antislop-code for comments (keep only the ones that carry information).
* constraint-driven-development: the repo's gates (G-19, G-33, G-47, DesignTokensTest) and CONSTRAINTS.md are the contract. Check your diff against them.
* doubt-driven-development: REVIEW PHASE. For every non-trivial decision you made (persistence mechanism, thresholds, anything you inferred), get an adversarial fresh-context challenge before it stands, and report the result.
* code-review-and-quality: final self-review of the diff before hand-off. The diff must contain no unrelated file.
* browser-testing-with-devtools: VERIFY PHASE. Check console errors, focus order, 320px reflow, 200% zoom and reduced-motion in a real browser alongside the Playwright spec.

Optional if installed: freeze (restrict edits to resources/js, app/Http, routes, tests, config as needed for your slice), careful (before any destructive command), design-review (visual QA pass on flagship screens).

HARD RULES

* Copy the page pattern of resources/js/pages/Catalog/Index.vue: AppLayout, <Head title>, #title slot, useForm with @submit.prevent, router.get with preserveState and preserveScroll. Do not invent a second pattern.
* Each page gets a controller method returning Inertia::render('Page', $props), a route, and a Pest test using assertInertia. Map models to explicit arrays. Never pass Eloquent models. No uses() or withoutVite() in test files.
* Writes keep their existing Form Requests and routes. The Vue form only posts.
* Tokens only: no zinc-* utilities, no dark: outside the theme override block.
* Never branch on a scenario name. Read config/scenarios.php through props (panels.* flags, widgets[]).
* Unrecorded or unsourced values render as "N/A" with a title attribute explaining why. Never a default, never a dash, never an invented number.
* Trust vocabulary: every computed figure carries Confirmed / Calculated / Estimated / Unknown. ProvenanceBadge.vue is built in D4. Before D4, show only entered or stored values and named absences.
* HELD computation stays held: no race win probability or readiness band, no inheritance computation or "expected inheritance", no per-training stat yields or failure %, no shop recommendation, no Grand Concert mechanics, no numeric confidence or score.
* Global terminology only: Support Cards, Scouts, Veteran Umamusume, Pal, Wit, Sparks. Print "Trackblazer", not "Twinkle Star Climax". Write "2026-07-01 Global rework".
* WCAG 2.2 AA: keyboard path, visible focus, h-11 / min-h-11 (44px) targets, labels, aria-describedby on errors, lang="ja" on Japanese names, prefers-reduced-motion respected, no drag-only interaction (always give a button alternative), sticky UI must not cover focus. Never show state through colour alone.
* Every page has loading, empty and error states. An empty state says what is missing, why it matters, and what to do.
* Wizard slices (D2 to D7) carry entered values forward and never re-ask for them (WCAG 3.3.7).
* Navigation: AppLayout already has nine destinations, two of them named absences (to: null). Miller's Law says at most eight. Replace a named absence instead of adding an entry. If a slice would exceed eight, stop and report.
* Wizard persistence: build_target lives on training_runs and runs.deck.sync is run-scoped, so the D2 to D7 wizard needs a draft/planning run or an equivalent. Decide the smallest mechanism using existing TrainingRun statuses. Do not invent an enum value, column or migration without owner approval. Report the decision.
* No new npm or composer package. No vue-tsc. Do not file an ADR; draft the text in your report if one seems needed.
* Add or extend the SCR-CAR-* (or matching area) row in SCREEN_SPEC.md section 2 with the empty / loading / error state table.

PROCESS: plan phase (skills above), then build phase with TDD, then Playwright spec in tests/browser/<screen>.spec.ts (rendered copy, 44px sweep, keyboard path, axe A + AA if the dependency already exists), then review and verify phases.

HAND-OFF SEQUENCE, report real output and never claim an unrun gate passed: targeted tests, php artisan test --compact, vendor/bin/pint --dirty --format agent, vendor/bin/phpstan analyse --no-progress --memory-limit=1G, npm run typecheck, npm run build, npm run test:browser, composer lore + composer lore-code with a ruling per hit.

FINAL REPORT must include: files changed, props contract, every deviation from the plan and why, open questions for the owner, gate output, and a "Skills used" list with one line each (what it changed in your work) plus any skill you skipped and why.

==============================================================  
PROMPT D1: Dashboard enrichment (SCREEN-001)
=============================================================================================================

TASK: Enrich resources/js/pages/Dashboard.vue and its controller props. Depends on C1 (BuildTarget) and C3 (veterans).

SKILLS (add to baseline):

* performance-optimization: the dashboard aggregates several sources. Eager-load, avoid N+1, cap recent lists, and use Cache::remember only where the catalog pattern already does.
* api-and-interface-design: define the dashboard props contract (activeCareer, recentVeterans, dataStatus) once, typed in resources/js/types.ts.
* antislop-copywriting: the empty states and the DataStatus line must be specific, not generic ("Welcome back" filler is fine only if the spec has it).

READ: screen-spec-2.0 SCREEN-001 plus the data-status note under "Product direction corrections"; design-2.0 sections 4, 29, 39, 48; plan section 8 row D1; plan section 13 rows "Paradox of the Active User" and "Von Restorff".

BUILD:

* ActiveCareerCard: the active run's trainee name, scenario label, year and half, turn, Energy, and the scenario's primary resource (read from config widgets[], not by scenario name). Primary action "Resume Career" links to the run. Energy reads "N/A" with a title when no turn has recorded it.
* Quick actions: New Career, Legacy Lab, Support Cards. A destination whose slice has not landed is a named absence (to: null), not a dead link.
* Recent Veterans from the C3 list action. Recent Builds only if a real source exists; otherwise a named absence.
* Legacy-goal gaps only if C1 and C3 give a real comparison. Otherwise omit the panel and say why in the report.
* DataStatus: a subtle "GLOBAL DATA ● Current" badge with the verified date from config/scenarios.php. Glyph plus text. The ruleset line reads "N/A" with a title because app.ruleset is null.
* Empty state when no active career: "No active career. Start a new training run." plus what / why / what-to-do per design-2.0 section 29.
* No onboarding wall: the page is usable on first load.

ACCEPTANCE: every panel is real data or a named absence with no invented counts. Props test covers active, no-active and no-veterans cases. Playwright covers the empty state, Resume link, DataStatus text and 44px sweep. lang="ja" on any Japanese name.

==============================================================  
PROMPT D2: Scenario Selection + SetupLayout (SCREEN-002)
=========================================================================================================================

TASK: Create layouts/SetupLayout.vue and pages/Career/ScenarioSelect.vue. This is the first screen of the setup wizard.

SKILLS (add to baseline):

* spec-driven-development: write the wizard contract first (steps, carried state, what each step reads and writes). D3 to D7 will build on it.
* doubt-driven-development: the wizard-persistence decision is the riskiest call in this slice. Challenge it adversarially (what breaks on refresh, back button, two tabs, abandoned drafts) before it stands.
* constraint-driven-development: G-33 (no scenario-name branching) is the central gate here.
* antislop-ui + antislop-layoutmobile: four cards must be distinct by content, not decoration; no overflow at 320px.
* design-review (optional, global): one visual QA pass on the card grid.

READ: screen-spec-2.0 SCREEN-002 and its correction row (four scenarios, no star ratings); design-2.0 sections 13, 25, 48; plan sections 8 (D2) and 9 (E6); config/scenarios.php, especially the our_grand_concert entry with documented => false.

BUILD:

* SetupLayout: minimal wizard shell with a step indicator ("Step n of 6"), skip link, a labelled nav, and the same landmarks as AppLayout. Do not add CareerLayout yet.
* ScenarioSelect: four cards generated by looping the scenarios the controller resolves from config/scenarios.php. Each shows name, short description, what it optimizes (primary / secondary / training complexity as text, NO star ratings or difficulty), primary mechanic, availability (live_on_global date), and ruleset version rendered as "N/A" with a title.
* Grand Concert card: a "PARTIALLY DOCUMENTED" badge with glyph and text. Its description says only baseline tracking is available. Do not write mechanics the config does not hold.
* Primary action per card: "Select Scenario". Use a radio-group or card-button pattern with a full keyboard path and aria-checked / aria-pressed. Carry the selection to D3 via the persistence decision.
* Adding a fifth scenario must be one config entry and zero component edits. Prove it with a test that iterates the config.

ACCEPTANCE: grep the diff for scenario-name branching (none allowed). Four cards render. The Grand Concert baseline state is visible. Selection survives navigation to the next step and back.

==============================================================  
PROMPT D3: Trainee Selection + Trainee Profile (SCREEN-003 / SCREEN-004)
=========================================================================================================================================

TASK: Create pages/Career/TraineeSelect.vue and pages/Career/TraineeProfile.vue. Depends on D2.

SKILLS (add to baseline):

* code-simplification: reuse, do not copy. Extract shared bodies from Catalog/Index.vue and Catalog/Show.vue into components if both screens need them. Behaviour of the catalog pages must not change.
* performance-optimization: the roster query is the heaviest in the wizard. Keep the catalog's cardScope() and PageSize::clamp, avoid N+1 on aptitudes and cards.
* antislop-layoutmobile: card grids and the filter bar at 320px.
* source-driven-development: ground each filter and profile section in a real column or relation. Cite the model or migration in your slice plan.

READ: screen-spec-2.0 SCREEN-003 and SCREEN-004; design-2.0 sections 17, 42, 45a, 46; existing pages/Catalog/Index.vue and Show.vue, components/AptitudeGrid.vue, FormTabs.vue, ArtworkSlot.vue, SkillRow.vue, TraineeCombobox.vue; CatalogController (cardScope() and the filter queries).

BUILD:

* TraineeSelect: reuse the catalog queries and Paginator pattern. Searchable, sortable roster. Filters only where real columns or relations exist (Surface, Distance, Running style, Aptitude, Unique skill, Character). If Growth rate or Scenario suitability have no data, omit them and list the omission in the report.
* Trainee card: name (lang="ja" for the Japanese name), aptitude badges as letter plus text (S/A strong, B/C neutral, D/E weak, F/G very weak), "View Profile" and the primary "Select Trainee". ArtworkSlot follows 45a geometry; when absent it renders the text-only row with no placeholder.
* TraineeProfile sections: Basic (name, rarity, version, growth rates only if stored), Aptitudes, Skills (unique, starting, awakening, event, only lists the data holds), Career goals (only if stored), Build analysis. Build analysis shows sourced facts only. "Recommended stat distribution" and "useful inheritance" are unsourced, so render "N/A" with a title.
* Selecting stores the trainee in wizard state and goes to D4. "Back" keeps the chosen scenario.

ACCEPTANCE: components reused, not duplicated. Aptitude never colour-only. Props test covers filter behaviour. Playwright covers keyboard selection and the 44px sweep. Existing catalog specs still pass.

==============================================================  
PROMPT D4: Build Target + ProvenanceBadge (SCREEN-005)
=======================================================================================================================

TASK: Build components/ProvenanceBadge.vue and pages/Career/BuildTarget.vue. Depends on C1.

SKILLS (add to baseline):

* api-and-interface-design: ProvenanceBadge is a shared contract used by every later slice. Fix its prop type (state union, optional source), its accessible-name rule and its placement rule once, and export the type.
* security-and-hardening: server-side validation is the authority. Stat targets clamp through ScenarioCaps::forRun in the Form Request. The client clamp is a convenience. Check mass-assignment and that skill_priorities cannot carry unexpected keys.
* doubt-driven-development: challenge the purposes conflict and the missing risk-tolerance field before deciding how to report them.
* source-driven-development: ground payload shape in BuildTargetPayload, StoreBuildTargetRequest and ScenarioCaps.
* antislop-human: error messages tied to inputs, focus moves to the first error.

READ: screen-spec-2.0 SCREEN-005 and its Career Plan correction; design-2.0 sections 15, 20, 36, 49; plan C1 (section 7) and the D4 row; app/Models/Advisor/BuildTargetPayload, StoreBuildTargetRequest, ScenarioCaps::forRun.

BUILD:

* ProvenanceBadge.vue: the ONLY place the four glyphs live. States Confirmed / Calculated / Estimated / Unknown. Each has a distinct glyph plus visible text, 3:1 non-text contrast, an accessible name, and a title. It sits adjacent to the figure it labels.
* BuildTarget page headed "Your target" (never "The correct target"). Fields come from BuildTargetPayload only: purpose, distance, surface, style, five stat targets, ordered skill priorities.
* Purpose options come from the C1 enum (StoryClear, ChampionsMeeting, ParentFarming, SkillFarming). The screen spec lists six. Do NOT add values. Report the difference to the owner.
* Risk tolerance and per-skill Required / High / Optional / Ignore marks are in the screen spec but not in the C1 payload. Do not build them without owner approval; list them as open questions.
* Stat targets: numeric input plus a bar showing distance to target, each labelled. Show the cap beside the field and server errors tied to the input with aria-describedby.
* Skill priorities: ordered list with Move up / Move down buttons (no drag-only).
* Human-readable summary generated only from entered values, badge Calculated. If a field is empty the summary names the absence.
* Absent target: the page names the absence and does not default values.

ACCEPTANCE: props test round-trips the payload and the cap clamp. Playwright covers keyboard reorder, cap error text, and the badge's text plus accessible name. Grep proves no glyph is defined outside ProvenanceBadge.

==============================================================  
PROMPT D5: Legacy Lab, record-only (SCREEN-006)
================================================================================================================

TASK: Create pages/Legacy/Index.vue, Legacy/Builder.vue and Legacy/Compare.vue. Depends on C3. Record-only: pick, browse, compare.

SKILLS (add to baseline):

* doubt-driven-development: the line between "record" and "compute" is where this slice fails. Challenge every displayed number and label: is it entered, stored, or derived? Derived means it does not ship.
* source-driven-development: UMAMUSUME_REFERENCE.md section 1.5 (sparks, star-roll odds, six-node tree) and ADR-0010 / ADR-0020 section 3. Cite lines in your slice plan.
* code-simplification: one SparkChip component for every spark (Law of Similarity), one ancestry-node component.
* frontend-ui-engineering: the six-node graph needs a real accessible structure (a labelled list or tree with connector lines as decoration), not an SVG-only picture.
* antislop-ui: no decorative anime flourish; connectors and chips carry meaning.

READ: screen-spec-2.0 SCREEN-006 and its correction row; design-2.0 sections 18, 22, 29, 46 and the knowledge-grounding rows on Sparks and Affinity; plan D5 and section 3 (the Sparks row); ADR-0020 section 3; ADR-0010; LegacySelectionPayload.

BUILD:

* A persistent banner on every Legacy screen: "Record only. This screen stores and compares what you enter. It does not compute inheritance." Glyph plus text.
* Index: browse recorded veterans (C3 list action). Filters: spark type, distance, surface, style, scenario, rating. Offer only what C3 supports.
* Builder: a six-node ancestry graph (Trainee on top; Parent A and Parent B; four grandparents A1, A2, B1, B2) joined by visible connector lines. Assigning a veteran to a node is a button flow ("Assign to Parent A") with a full keyboard path, no drag. Each node shows recorded Blue / Pink / Green / White sparks via the shared SparkChip (colour AND label AND star count).
* Spark probabilities: show one only if the repo already holds a sourced star-roll table (reference section 1.5), labelled Estimated. Otherwise "N/A" with a title. Never sum, aggregate, or show "expected inheritance", payout totals or a recommended combination. Affinity is not computed: render "N/A" with a title.
* Compare: up to four veterans side by side, identical properties aligned in rows (design-2.0 section 46), recorded values only.
* Primary action "Confirm Inheritance", saving via the existing LegacySelectionPayload path.

ACCEPTANCE: grep shows no inheritance, affinity or optimization computation. Props test covers filters and the six-node payload. Playwright covers keyboard assignment, the banner and compare alignment.

==============================================================  
PROMPT D6: Support Deck Builder (SCREEN-007)
=============================================================================================================

TASK: Create pages/Support/Builder.vue over the existing runs.deck.sync write, plus components/support/{SupportSlot,DeckAnalysis}.vue as needed.

SKILLS (add to baseline):

* source-driven-development: every analysis number must trace to SupportCardEffects anchors (UMAMUSUME_REFERENCE.md section 1.4, SUPPORT-CARDS.md). Cite them.
* code-simplification + deprecation-and-migration: DeckPanel.vue and the run page's deck payload already exist. Extract and reuse. Do not delete anything without a zero-consumer grep, and keep runs.deck.sync behaviour (including the 2026-10-05 closed-slot change).
* performance-optimization: the card picker lists many cards. Keep the paginated query shape of SupportCardController.
* doubt-driven-development: challenge each deck-analysis category: is it derivable, or is it a score in disguise?
* frontend-ui-engineering: slot replacement is a stateful keyboard interaction. Define focus return after a replacement.

READ: screen-spec-2.0 SCREEN-007 and its correction row; design-2.0 sections 12, 17, 33, 42, 45a; plan D6; components/DeckPanel.vue, the run deck payload in TrainingRunController, SupportCardEffects, pages/SupportCards/Index.vue and Show.vue.

BUILD:

* Six slots, each with an ownership flag OWNED / RENTED as a labelled toggle with text. Do not assume "five owned plus one borrowed".
* Seven support types as one typed list: Speed, Stamina, Power, Guts, Wit, Pal, Group. Type shown as text plus icon.
* Scenario Link: DERIVED from scenario plus character at render time, never stored on the card. If the repo cannot derive it, "N/A" with a title.
* Card picker with filters only where data exists (type, rarity, level, limit break, skill, training bonus, race bonus, scenario compatibility). Keyboard slot replacement, no drag.
* Deck analysis by category (training power, early run, race bonus, safety, events, skills). Compute a category only from stated SupportCardEffects anchor values, show the actual numbers with a Calculated badge. No composite score, and no bar unless the number is printed beside it. Strengths and weaknesses are plain text derived from those numbers. "Recommended replacement" is NOT built: render a named absence.
* Primary action "Confirm Deck", posting through the existing runs.deck.sync and its Form Request.
* Image slots follow 45a. Absent artwork shows no placeholder.

ACCEPTANCE: props test covers six slots, the ownership flag, seven types. Playwright covers slot replacement by keyboard, the 44px sweep, and that analysis shows numbers with labels.

==============================================================  
PROMPT D7: Run Preflight / Career Contract (SCREEN-008)
========================================================================================================================

TASK: Create pages/Career/Preflight.vue. Depends on D4, D5, D6.

SKILLS (add to baseline):

* spec-driven-development: write the warning rules as a table in the slice plan (trigger, data read, reason line) before coding. Each rule must be testable.
* doubt-driven-development: challenge every threshold. The aptitude cut-off must come from design-2.0 section 17's tiers, not from you.
* security-and-hardening: the preview is not a trust boundary. "Start Career" must re-run the Form Requests server-side and refuse stale or tampered wizard state.
* api-and-interface-design: the contract prop that composes D4 to D6 should be one typed object, so Edit / Back round-trips are lossless.

READ: screen-spec-2.0 SCREEN-008 and its "Career Contract" correction; design-2.0 sections 13, 14, 21, 36; plan D7; design-2.0 section 17 for aptitude tiers.

BUILD:

* Sections Build (trainee, scenario, target, six legacy members), Support deck (six cards and the D6 analysis), Target (stats, skill priorities, race profile). Everything is read from carried wizard state. Nothing is re-asked.
* Warnings: only those derivable from entered data, each with a one-line reason citing the values. Derivable examples: a target distance, surface or style whose trainee aptitude is D or lower (state the threshold in your plan); a skill priority with no source on the trainee, deck or legacy; a missing wizard section. Do NOT ship "weak stamina plan", "poor support synergy" or "missing scenario requirement" unless derivable from sourced data. Report what you skipped.
* Each warning: glyph plus text. Warnings never block; the Trainer decides. Only hard server validation errors block.
* Actions: Back, Edit Legacy, Edit Deck, Edit Target (each returns to its step with values intact), and the primary "Start Career", posting to the existing run-creation route and redirecting to the run.
* Ruleset snapshot field: "N/A" with a title (app.ruleset is null).

ACCEPTANCE: props test covers the full contract, each warning trigger, and that Start Career creates a run. Playwright covers Edit then return with values intact, the warning text, and focus moving to the first error after a failed submit.

==============================================================  
PROMPT D8: Career Cockpit + CareerLayout (SCREEN-009)
======================================================================================================================

TASK: Create layouts/CareerLayout.vue, pages/Career/Cockpit.vue, and components/career/{CareerHeader,CareerStatePanel,ActionGrid,RecommendationCard,AdvisorRail}.vue. Depends on C2 and D7. This is the flagship screen.

SKILLS (add to baseline):

* impeccable: use its critique and polish passes on the finished cockpit, within the repo's tokens and MOTION dial 1. Its suggestions never override DESIGN.md.
* design-review (optional, global): visual hierarchy QA against design-2.0 section 4 (four levels). The recommendation card is the only accented element.
* code-simplification: reuse the 19 components in the Runs/Show.vue import graph (StatBand, EnergyGauge, MoodPill, ResourceStrip, GuidedStep, RaceCalendar). Wire them, do not rewrite them.
* performance-optimization: the cockpit is read on every turn. Keep the payload lean and avoid N+1 on turns and goals.
* antislop-layoutmobile + antislop-human: three breakpoints, no horizontal scroll for primary decisions, sticky-nav scroll-padding so focus is never obscured (2.4.11).
* doubt-driven-development: challenge that no field in the advisor props smuggles a score or numeric confidence.
* browser-testing-with-devtools: check 1280, 768 and 320 widths, 200% zoom and reduced motion in a real browser.

READ: screen-spec-2.0 SCREEN-009, sections 13, 29, 33, plus the "state machine" correction; design-2.0 sections 2, 4, 19, 23, 24, 36, 38, 40; plan D8 including the "Recommendation contract (D8)" paragraph; app/Services/Advisor/TrainerAdvisor and config/advisor.php; the existing pages/Runs/Show.vue and its 19 components.

BUILD:

* Do not delete Runs/Show.vue or its components. Decide the route in your slice plan (the spec says it descends from GET /training-runs/{run}) and keep all existing write routes.
* Layout: desktop three columns (Timeline about 20%, Current decision about 50%, Advisor about 30%); tablet two columns with the timeline as a horizontal strip; mobile single column in the order Header, Current State, Recommendation, Action, Scenario, Timeline.
* CareerHeader: scenario, year, month, turn, Energy, Mood, Fans, Skill Points, scenario resources from config widgets[].
* CareerStatePanel: five stats plus four meta values. Each stat shows "current / target" AND a bar. Missing values read "N/A".
* ActionGrid: Training, Race, Rest, Recreation, Scenario action, Event, Inheritance. Exactly one carries a "RECOMMENDED" marker. Entries link to D9 to D12 if landed, otherwise they are named absences.
* RecommendationCard / AdvisorRail props exactly: { action, band: 'AtOrAboveAdvisory'|'BelowAdvisory', reasons: string[], alternative: string|null, risk: string|null }. Band printed as text plus glyph, with reason lines. NO score and NO numeric confidence. It never auto-executes; the Trainer's action is always their own button. When Energy is absent show no band and no ranking and print "Recommendation unavailable because Energy has not been entered."
* Manual correction goes through the existing runs.turns.update. Do not build undo unless a route already exists.

ACCEPTANCE: props test for the advisor contract with Energy absent and present. Grep confirms no scenario-name branching. Playwright covers the three breakpoints, reflow with no horizontal scroll, the keyboard path through the grid, and the reduced-motion check.

==============================================================  
PROMPT D9: Training Decision detail (SCREEN-010)
=================================================================================================================

TASK: Create components/career/TrainingCard.vue and pages/Career/TrainingDetail.vue. Depends on D8.

SKILLS (add to baseline):

* source-driven-development: every term shown on a training card must trace to PROCESS-PLANS.md "trainer-advisor.md" sections 1, 3 and 5, config/advisor.php, or Trainer-entered data. Cite them.
* antislop-copywriting: write the N/A titles precisely ("No published source; excluded by the advisor spec, ADR-0001 section 3"), not generic "unavailable".
* doubt-driven-development: challenge every number on the card. If you cannot name its source, it becomes N/A.
* frontend-ui-engineering: progressive disclosure with aria-expanded and a stable focus order.

READ: screen-spec-2.0 SCREEN-010 and its correction row (yields are unsourced, render N/A); design-2.0 sections 12, 24, 35, 36; plan D9; PROCESS-PLANS.md section "trainer-advisor.md" sections 1, 3, 5; config/advisor.php.

BUILD:

* Option cards for Speed, Stamina, Power, Guts, Wit. Default summary shows only what is sourced or entered: Energy cost (config session_cost 17 to 28 rendered "E−28 … E−17"; Wit shows wit_cost 0 as "RiskNotMeasured"), supports entered for that training if recorded, bond gains if entered, and target deficit (target minus current, badge Calculated).
* DO NOT print "+62 Speed / +25 Power", "Failure: 2%" or any invented yield. Render "N/A" with a title citing the exclusion.
* Expandable details (disclosure button, aria-expanded): expected gains N/A, energy cost, failure probability N/A, support effects (stated anchor values only), bond gains, scenario effects, target impact.
* Actions: "Train" posts through the existing guided-turn write (runs.turns.store); "Inspect details" toggles the disclosure. The recommended option, if any, carries the single RECOMMENDED marker.
* Risk words (LOW / MEDIUM / HIGH RISK) only where a sourced rule supports them; otherwise "N/A".

ACCEPTANCE: grep proves no hard-coded yield or failure number. Props test covers option payloads. Playwright covers the disclosure by keyboard and the N/A titles.

==============================================================  
PROMPT D10: Race Decision (SCREEN-011)
=======================================================================================================

TASK: Create components/career/RaceCard.vue and pages/Career/RaceDecision.vue. Depends on D8.

SKILLS (add to baseline):

* source-driven-development: race facts come from RaceCatalogSlot and the calendar data only. Cite the model and migration.
* doubt-driven-development: the briefs ask for readiness bands and risk thresholds, but the plan holds all race prediction on ADR-0016. Challenge any string, prop or enum that implies a prediction.
* frontend-ui-engineering: the race-detail drawer needs focus trap, Escape to close, and focus return to the opener.
* antislop-human: Level 1 "Critical" mandatory-race alerts are glyph plus text, never colour alone.

READ: screen-spec-2.0 SCREEN-011 and its correction row; design-2.0 sections 21, 32, 36; plan D10 and the ADR-0016 note in sections 3 and 6; RaceCatalogSlot, components/RaceCalendar.vue and RacePanel.vue, the existing runs.races.store route.

BUILD:

* RaceCard shows race name, grade, distance, surface, running style, fan gain, reward and skill-point gain, only fields present on RaceCatalogSlot or entered by the Trainer. Missing fields read "N/A" with a title. Do not hard-code distance bands; if distance_band is shown it carries a source/confidence note (the 1400m Sprint-versus-Mile conflict).
* Win probability and readiness bands (Excellent / Good / Borderline / Poor) are NOT computed. Render "Readiness: N/A" with a title naming the ADR-0016 data blocker. The percentage risk indicator is not built.
* Mandatory-race deadlines come from the calendar data, Level 1 "Critical", glyph plus text.
* Actions: "Enter Race" posts via runs.races.store; "Skip" returns without recording. Details open in a drawer or inline disclosure with focus management.

ACCEPTANCE: grep proves no win-percentage string. Props test covers present and absent race fields. Playwright covers the N/A title text, the drawer's keyboard behaviour and the 44px sweep.

==============================================================  
PROMPT D11: Event Decision (SCREEN-012)
========================================================================================================

TASK: Create components/career/EventCard.vue and pages/Career/EventDecision.vue. Depends on D8.

SKILLS (add to baseline):

* api-and-interface-design: the event props contract must mirror the TurnEvents*Payload classes exactly. Do not widen it for fields the spec wants but the payloads lack.
* security-and-hardening: event text and notes are untrusted. No v-html, validate through the existing Form Request, tie errors to inputs.
* doubt-driven-development: challenge the advisor panel. A recommendation without a derivable reason line must not ship.
* antislop-human: the "outcome incomplete" warning is glyph plus text; the override path is as prominent as the recommended path.

READ: screen-spec-2.0 SCREEN-012 and its event-model correction; design-2.0 sections 2, 36, 38; plan D11; TurnEvent, the TurnEvents*Payload classes, and the existing event recording in TrainingRunController.

BUILD:

* Event model in the UI: source (Support / Character / Scenario / Random), event name, choices, known outcomes per choice, current career state alongside. Build only the sources and outcome fields the payload classes hold.
* Choices are labelled radio options. Outcomes show recorded effects only. A choice with no recorded outcome prints "⚠ Event outcome incomplete. Choose manually."
* Advisor panel: show a recommended option only when the engine returns one with a derivable reason line. Otherwise "No recommendation available" and why. The Trainer can always override. Never preselect the recommended option.
* Primary action "Record Choice" posts through the existing TurnEvent write and its Form Request. Errors tie to inputs with aria-describedby.

ACCEPTANCE: props test covers complete and incomplete outcomes plus the override path. Playwright covers the incomplete warning, keyboard selection and the recorded result.

==============================================================  
PROMPT D12: Inheritance Event, record-only (SCREEN-013)
========================================================================================================================

TASK: Create pages/Career/InheritanceEvent.vue. Depends on D8.

SKILLS (add to baseline):

* source-driven-development: use the same SparkChip and the reference section 1.5 facts as D5. Take milestone turns only from config or recorded events, and cite where.
* doubt-driven-development: predicted versus observed is the contract of this screen (ADR-0020 section 3). Challenge any element that blurs them.
* code-simplification: reuse SparkChip and the timeline glyph component. Do not fork them.
* antislop-ui: two clearly separate regions with their own headings and badges, not one merged table.

READ: screen-spec-2.0 SCREEN-013 and its correction row (probability, not guarantees); design-2.0 sections 18, 22, 49; plan D12; ADR-0020 section 3; LegacySelectionPayload and the existing event recording.

BUILD:

* Parent and grandparent sparks from the run's legacy selection, via SparkChip.
* Observed outcomes: a form to record the inspiration result the Trainer saw in the game (which sparks activated, previous inheritance), posted through the existing event recording. Predicted and observed are visually apart, each with a heading and badge (Estimated vs Confirmed). Observed is always Confirmed because the Trainer entered it.
* Predicted / "expected inheritance": NOT computed. Show "N/A" with a title citing ADR-0020 section 3. A sourced star-roll table may be shown as Estimated, never summed.
* Milestone timeline with glyph plus text (● completed, ◉ current, ○ upcoming, × missed). Do not hard-code "Classic April".

ACCEPTANCE: props test proves observed values round-trip and that no predicted total exists in the payload. Playwright covers keyboard entry and the predicted-versus-observed separation.

==============================================================  
PROMPT D13: Skills Planner (SCR-013, design-2.0 only)
======================================================================================================================

TASK: Create pages/Career/SkillsPlanner.vue. Depends on D8. This screen exists only in design-2.0.

SKILLS (add to baseline):

* spec-driven-development: write the state model first (Required / Available / Learned / Inherited): where each state is read from and its precedence when a skill qualifies for two.
* source-driven-development: hint-level discount percentages must come from a sourced table in config or the reference doc. If none exists, the discounted cost is N/A. Cite what you found or the absence.
* doubt-driven-development: the race-fit assessment is where a recommendation could sneak in (OQ-5 stays open). Challenge any wording that ranks or advises.
* frontend-ui-engineering: ordered list with button-based reordering and a live-region announcement of the new position.

READ: plan D13 and open question OQ-5; design-2.0 sections 15, 17, 33; components/SkillRow.vue, the run's skillCatalog and skillGroups props, the runs.skills.sync write, and BuildTarget skill_priorities.

BUILD:

* Skill states Required / Available / Learned / Inherited, each text plus glyph. Required comes from the Trainer's ordered skill priorities in the BuildTarget.
* SP coverage: sum SP cost of Required skills against current Skill Points and warn (glyph plus text) when SP cannot cover them. Badge Calculated.
* Hint-level discount: base cost × (1 − discount%), with percentages only from a sourced table. Otherwise "N/A" with a title.
* Race-fit assessment per skill: the skill's recorded conditions (distance, surface, style, course, weather, ground, phase) beside the build target as a plain "matches / does not match / not recorded". No score.
* No skill recommendation of any kind.
* Priority reorder via Move up / Move down buttons. Save through runs.skills.sync and its Form Request.

ACCEPTANCE: props test covers the SP warning and each state. Playwright covers keyboard reorder, the warning text and the 44px sweep.

==============================================================  
PROMPT D14: Career Timeline (SCREEN-018)
=========================================================================================================

TASK: Create components/career/CareerTimeline.vue and pages/Career/Timeline.vue. Depends on D8.

SKILLS (add to baseline):

* performance-optimization: a full career is 70+ turns with events. Eager-load TurnEvent per turn, paginate or window the list, and avoid N+1.
* api-and-interface-design: define one typed history-entry shape (turn, kind, before, after, corrected_from) that D8's strip, D15 and D16 can reuse.
* source-driven-development: ground the before/after mapping in ADR-0003 and turnRows(); cite them.
* antislop-human: the rail is a list with proper semantics (ol), each entry reachable by keyboard, glyph plus text for state.

READ: screen-spec-2.0 SCREEN-018 and its decision-audit correction; design-2.0 sections 22, 32, 37; plan D14; TurnEntry and TurnEvent, turnRows(), ADR-0003.

BUILD:

* One vertical rail with glyph plus text: ● completed, ◉ current, ○ upcoming, × missed or failed. Each entry: turn, action label, before state, after state. Entries expand inline (aria-expanded) or open a drawer with focus management.
* Decision audit: BEFORE, ACTION, EXPECTED, ACTUAL, RESULT, accepted / rejected only where recorded. Anything unstored prints "N/A" with a title. Do not show advisor confidence.
* Corrections appear as additional entries and are never silently rewritten. A past decision is read-only here.
* Filters by type (training, races, events, inheritance, scenario actions, purchases), only for types that exist in the data.
* Link to the matching decision screens where they exist.

ACCEPTANCE: props test proves the before/after mapping and that a correction appends. Playwright covers the keyboard path through the rail, the expansion and glyph-plus-text states.

==============================================================  
PROMPT D15: Career Result (SCREEN-019)
=======================================================================================================

TASK: Create pages/Career/Result.vue. Depends on D8.

SKILLS (add to baseline):

* antislop-copywriting: calm, specific summary copy (Peak-End Rule). No celebratory filler, no "Great job!".
* impeccable: one polish pass on hierarchy and spacing, within tokens.
* doubt-driven-development: challenge every aggregate. Per-stat deficits are fine; any single "quality", "completion %" or "legacy value" figure is not.
* design-review (optional, global): quick visual QA.

READ: screen-spec-2.0 SCREEN-019 and its correction (Veteran Creation framing); design-2.0 sections 4, 15, 17, 45; plan D15 and the "Peak-End Rule" row in section 13.

BUILD:

* Reads a finished run only. If not finished, show an empty state that says what is missing and links back to the cockpit.
* Sections: Final build (five stats with value AND the target they were set against; skills; aptitudes with letter badges), Race history (races, wins, losses, G1 wins, from recorded entries only), Scenario result (objectives and rewards from the scenario config and recorded data; panels driven by flags, not scenario names).
* NO derived build-quality score, no aggregate target-completion percentage, no legacy-value rating, no "best use recommendation". Per-stat deficit against target is allowed, badge Calculated.
* Ruleset line "N/A" with a title.
* Primary action "Save Veteran" to D16.

ACCEPTANCE: props test for finished and unfinished runs. Playwright covers the unfinished empty state, the headings and the 44px sweep.

==============================================================  
PROMPT D16: Save Veteran + Veteran Library + Comparison (SCREEN-020 / 021 / 022)
=================================================================================================================================================

TASK: Create pages/Career/SaveVeteran.vue, pages/Veterans/Index.vue, Veterans/Show.vue and Veterans/Compare.vue. Depends on C3 and D15.

SKILLS (add to baseline):

* security-and-hardening: notes and custom tags are free text. Never v-html. Validate length and shape in the Form Request. Delete is destructive: confirm, authorise as the repo does, and test the refusal path.
* careful (optional, global): active before any destructive command while testing delete.
* api-and-interface-design: define the veteran list/show props once and reuse in Index, Show, Compare and the Legacy Lab picker.
* doubt-driven-development: challenge "Factors", "Ancestry" and "Find Parents for This Build" for hidden computation. They are search and display only.
* code-simplification: reuse SparkChip, the ancestry graph from D5 and the comparison row component from D5.
* laravel-best-practices: list/filter/sort queries use indexed stored fields and explicit allow-lists.

READ: screen-spec-2.0 SCREEN-020 to 022 and their correction rows; design-2.0 sections 18, 29, 31, 46; plan D16 and C3 (section 7); ADR-0020 section 3; ADR-0010; the veterans table and its record / list / show actions.

BUILD:

* SaveVeteran: name, tags, notes (free text). Tags are SUGGESTIONS (Speed, Stamina, Power, Guts, Wit, Sprint, Mile, Medium, Long, Dirt, Turf, Front Runner, Pace Chaser, Late Surger, End Closer, Skill, Race, Scenario) the Trainer toggles, plus custom tags. Workflow: review recorded sparks, tag, save. Factor analysis and "legacy value" are computation and NOT built: "N/A" with a title citing ADR-0020 section 3. Write no recommendations such as "Excellent Medium parent".
* Index: three views, Veterans (all), Factors (searchable inventory of recorded factors), Ancestry (six-node graph of where a veteran came from, using SparkChip). Search, filter and sort only on stored fields. "Find Parents for This Build" navigates to the Legacy Lab with the build's filters applied (a search, not an optimizer).
* Favorite, archive, delete: build ONLY if C3 provides them; delete needs a confirmation dialog with focus management. Otherwise list them as not built.
* Show: the recorded veteran with ruleset line "N/A". Empty state "No Veterans Yet" (what / why / action).
* Compare: up to four, aligned property rows, recorded values only.

ACCEPTANCE: grep proves no inheritance computation. Props test covers tags, filters, compare limit. Playwright covers tag toggling by keyboard, the empty state and the delete confirmation if present.

==============================================================  
PROMPT D17: Database (SCREEN-023)
==================================================================================================

TASK: Create pages/Database/{Trainees,Supports,Skills,Races,Scenarios}.vue. Depends on A1 to A3 (landed).

SKILLS (add to baseline):

* code-simplification + deprecation-and-migration: reuse, don't fork. Extract shared list and detail bodies, keep /umamusume, /skills and /support-cards working, and prove with the existing browser specs that nothing regressed. Redirect only if an owner ruling exists.
* performance-optimization: reference tables are the largest lists in the app. Keep server-side pagination and the existing cache keys.
* antislop-layoutmobile: wide tables scroll inside their own container, never the page, at 320px.
* source-driven-development: the Scenarios page prints config/scenarios.php as-is with its own verification date; cite the header.

READ: screen-spec-2.0 SCREEN-023; design-2.0 sections 29, 32, 34; plan D17 and the "Deliberately traded" note in section 13; the existing pages/Catalog/_, Skills/_, SupportCards/* and their controllers; config/scenarios.php.

BUILD:

* Reuse the ported screens. Add a Database hub linking to the five areas. Trainees, Supports and Skills present existing data; do not rebuild their queries.
* Races: from RaceCatalogSlot. With 0 rows, name the offline state (what is missing, why, what to do). No placeholder races.
* Scenarios: render the resolved matrix from config/scenarios.php as-is (caps, panels, widgets, live date, documented flag). Grand Concert shows baseline plus "PARTIALLY DOCUMENTED". No scenario-name branching.
* Events, Shop Items and Sparks tabs are out of scope; list them as deferred.
* Density is allowed here and nowhere else. Tables still need labelled columns and keyboard-reachable rows.
* Image slots follow 45a.

ACCEPTANCE: Playwright covers the hub, the Races offline state, the Scenarios matrix and 320px reflow. Existing catalog, skills and support specs pass untouched.

==============================================================  
PROMPT D18: Settings (SCREEN-024)
==================================================================================================

TASK: Extend pages/Preferences/Edit.vue into Settings categories: General, Recommendation, Data, Game version.

SKILLS (add to baseline):

* laravel-best-practices: any migration needs a working down(), an ARCHITECTURE-ESSENTIALS.md digest update and a PRD citation (AGENTS.md section 11). Stop and ask before writing one.
* deprecation-and-migration: if a preference column is added, use expand-then-contract and keep the existing preferences working.
* documentation-and-adrs: update the SCREEN_SPEC.md row and the digest. Draft (do not file) any ADR text the owner might need.
* security-and-hardening + careful (optional, global): Reset is destructive. Confirmation dialog, server-side guard, and a test of the refusal path.
* doubt-driven-development: challenge each proposed preference. If nothing reads it, it should not exist.
* antislop-human: save feedback is a polite live region that never steals focus.

READ: screen-spec-2.0 SCREEN-024; design-2.0 sections 34, 47, 48; plan D18 and risk 4 in section 11; PreferencesController, its Form Request, the preferences columns and tests/browser/preferences.spec.ts.

BUILD:

* Category navigation inside the page: a labelled tablist or anchored fieldsets with a keyboard path and stable Help placement (WCAG 3.2.6). Keep existing preferences and the existing spec passing.
* General (theme, language, units, default scenario) and Recommendation (aggressiveness, risk tolerance, stat-target defaults): add a control ONLY if its column already exists or the owner approves the migration. In your slice plan list every proposed preference, mark which already have a column, and stop and ask before any migration. "Race risk thresholds" is NOT built because it depends on win probability.
* Data (import, export, backup, restore, reset): link only to import and export routes that already exist. Reset is destructive and needs a confirmation dialog with focus trap, Escape and focus return; build it only if a route exists, otherwise a named absence.
* Game version: "Global Ruleset, Version: N/A" with a title explaining that no ruleset string is sourced; plus the verified date from config/scenarios.php.
* Save feedback: polite live region ("✓ Preferences saved").

ACCEPTANCE: props test for each real preference, existing preferences tests unchanged. Playwright covers category navigation by keyboard, the saved message, the N/A ruleset row and the reset confirmation if it exists.

==============================================================  
NOTES
======================================================================

* Skills the prompts lean on hardest: doubt-driven-development for the slices where computation could creep in (D5, D7, D10, D12, D13, D16) and for the wizard-persistence decision (D2). source-driven-development grounds any number on screen in a model, config or reference line.
* Global-only skills (design-review, careful, freeze) are marked optional. SKILL.md shows them installed globally but not in `.agents/skills/`, so an agent in a clean sandbox may not have them. The prompts tell the agent to say so and carry on.
* I did not use `qa`, `ship`, `land-and-deploy` or `review`. `qa` fixes bugs and could touch unrelated files, and the others change git or deploy state. The hand-off sequence already covers verification, and the owner decides when to ship.
* The "Skills used" line in the final report is there so you can see which skills the agent applied. If one is never reported, it probably wasn't loaded.

---

## Trainer-Docs-2.0-E-Prompts.md

> **Augmented 2026-10-10 (Round 14, augment-only).** Source: `docs/Trainer-Docs-2.0-E-Prompts.md` (230 lines), reproduced verbatim below with headings demoted one level. The source file remains in place; no citation was repointed and nothing was deleted.
>
> **Consolidated 2026-10-12 (Round 15).** The original was deleted after line-by-line verification (166 of 166 non-blank source lines found in this section, in order, 0 missing). It was tracked and clean at HEAD, so the delete is recoverable from history. Cite this anchor, not the deleted path.

The E-slice prompts follow, E1 through E6. Paste the SHARED CONTEXT (E-phase) block above each one. It replaces the D-phase block and keeps the same baseline skills.

==============================================================  
SHARED CONTEXT, E-PHASE (paste before every E prompt)
======================================================================================================================

You are working in the Trainer Desk 2.0 repo (Laravel 13, Inertia v3, Vue 3, TypeScript, Tailwind v4, Pest 4, Playwright). You are building ONE slice of Phase E (scenario panels) of the Trainer Desk 2.0 frontend. Phase E plugs into the Career Cockpit that D8 built. If D8 has not landed, stop and report.

READ FIRST, in this order:

1. AGENTS.md
2. docs/proposals/frontend-development-plan.md: Global Constraints, sections 3, 8 (the "Scenario panel contract (E1)" paragraph), 9 (Phase E), 10, 12, 13, plus your slice's row in section 4
3. docs/proposals/screen-spec-2.0.md: your SCREEN-nnn section AND the "Product direction corrections" table
4. docs/proposals/design-2.0.md: sections 4, 25 to 28, 29, 36, 45, 45a, 49 and the "Appendix, Frontend architecture"
5. config/scenarios.php (read the real key names; never guess them), DESIGN.md, SCREEN_SPEC.md

AUTHORITY ORDER: landed ADRs, then AGENTS.md, then the development plan, then the two design briefs. The briefs are design intent. The plan is the authorization. Skills are METHOD only. If a skill's default advice conflicts with a repo document (dark: variants, a new package, extra animation, a redesign, a recommendation score), the repo wins. Say so in your report.

THE CENTRAL RULE OF PHASE E: the UI must not know how a scenario works (G-33, D-240). Panels are selected by panels.* flags and widgets[] read from config/scenarios.php through props. No component, test fixture, prop or class name may branch on scenario.label, scenario name, or a scenario key literal. The component FILE names from the brief (UraPanel.vue, UnityCupPanel.vue, TrackblazerPanel.vue) are allowed, but they are registered against widget or flag keys, not scenario names. A fifth scenario must be one config entry and zero component edits.

SKILLS: load each by name at the phase shown. If one is not installed, say so in your report and apply its intent anyway.  
Baseline (every slice):

* using-agent-skills: governs how you find and invoke the rest.
* planning-and-task-breakdown + spec-driven-development: PLAN PHASE. Write the slice plan (files, props contract, tests, open questions) before code.
* incremental-implementation + test-driven-development: BUILD PHASE. Failing props test, controller, component, passing test, Playwright spec.
* laravel-best-practices + testing-best-practices: controllers, Form Requests, query shape, Pest structure.
* frontend-ui-engineering + tailwindcss-development: Vue components, layout, state, WCAG. Tokens only, never dark: and never zinc-*.
* antislop, antislop-ui, antislop-human, antislop-copywriting, antislop-layoutmobile: apply to every string and layout. antislop-code for comments.
* constraint-driven-development: the repo's gates (G-19, G-33, G-41, G-47, DesignTokensTest) are the contract. Check your diff against them.
* source-driven-development: every number or label you print traces to config/scenarios.php, a model, a migration, an ADR or a reference-doc line. Cite it in the slice plan.
* doubt-driven-development: REVIEW PHASE. Challenge every non-trivial decision (especially anything that looks like a recommendation or a prediction) and report the result.
* code-review-and-quality: final self-review. The diff must contain no unrelated file.
* browser-testing-with-devtools: VERIFY PHASE. Console errors, focus order, 320px reflow, 200% zoom and reduced motion in a real browser.  
  Optional if installed: freeze (restrict edits to the files your slice needs), design-review (visual QA).

HARD RULES

* Panels are static modules with their own currencies and objectives, mounted inside the Cockpit's scenario region as "Career Cockpit + Scenario Panel" (design-2.0 section 25). The Cockpit layout itself must not change per scenario.
* Copy the repo's page and component patterns; do not invent a second one. Any new page gets a controller method returning Inertia::render with explicit arrays (never Eloquent models), a route, and a Pest assertInertia test. No uses() or withoutVite() in test files.
* Writes keep their existing Form Requests and routes (runs.purchases.store, runs.races.store, runs.turns.*). Vue only posts and handles useForm errors.
* Unrecorded or unsourced values render as "N/A" with a title attribute explaining why. Never a default, a dash, or an invented number. ProvenanceBadge.vue (built in D4) labels every computed figure Confirmed / Calculated / Estimated / Unknown. Use it, never redefine the glyphs.
* HELD computation stays held: no race win probability, readiness band or risk percentage (ADR-0016), no per-training yields or failure %, no inheritance computation, no shop-item recommendation (rotation not modelled), no Grand Concert mechanics, no numeric confidence or score, no "recommended timing" or "projected benefit".
* Global terminology: Support Cards, Scouts, Veteran Umamusume, Pal, Wit, Sparks. Print "Trackblazer", never "Twinkle Star Climax" (UNVERIFIED, conflict row 31). Write "2026-07-01 Global rework".
* WCAG 2.2 AA: keyboard path, visible focus, min-h-11 (44px) targets, labels, aria-describedby on errors, prefers-reduced-motion respected, no drag-only interaction, state never by colour alone (glyph plus text), sticky UI must not cover focus. Meters are numbers AND bars, with the bar labelled.
* Every panel has loading, empty and error states, and each says what is missing, why it matters and what to do.
* Do not delete a Blade x-* component until its consumer count is zero (grep). If B1 already deleted a reference component (x-team-rank-gauge, x-team-race-panel, x-spirit-burst-roster, x-grade-point-meter, x-shop-panel, x-epithet-checklist), recover it from git history (git log --diff-filter=D, git show) and port its logic and props; do not rewrite from memory.
* No new npm or composer package. No vue-tsc. Do not file an ADR; draft text in your report if one seems needed.
* Update SCREEN_SPEC.md section 2 with the screen's row and its empty / loading / error state table.

PROCESS: plan phase, then TDD build, then Playwright spec in tests/browser/<screen>.spec.ts (rendered copy, 44px sweep, keyboard path, axe A + AA if the dependency exists), then review and verify phases.

HAND-OFF SEQUENCE, report real output and never claim an unrun gate passed: targeted tests, php artisan test --compact, vendor/bin/pint --dirty --format agent, vendor/bin/phpstan analyse --no-progress --memory-limit=1G, npm run typecheck, npm run build, npm run test:browser, composer lore + composer lore-code with a ruling per hit.

FINAL REPORT: files changed, props contract, every deviation from the plan and why, open questions for the owner, gate output, and a "Skills used" list (one line each, plus skipped skills and why).

==============================================================  
PROMPT E1: Scenario panel shell bound to config/scenarios.php (SCREEN-014 / 015 / 016)
=======================================================================================================================================================

TASK: Create components/scenario/ScenarioPanel.vue (the shell) and the panel registry that maps config widget and flag keys to renderer components. Mount it in the Cockpit's scenario region. Depends on D8. E2, E3, E4 and E6 plug into this slice, so its contract must be right the first time.

SKILLS (add to baseline):

* api-and-interface-design: this slice defines the contract that four later slices consume. Fix the types in resources/js/types.ts once and keep them stable.
* doubt-driven-development: challenge the registry. What renders when a widget key has no renderer? What if panels.* is all false? What if the scenario is unknown to the frontend? Each must fail visibly and safely.
* constraint-driven-development: G-33 is the headline gate here; make it a test, not a promise.
* code-simplification: one shell, one registry, zero per-scenario conditionals.

READ: plan section 8 "Scenario panel contract (E1)" and section 9 "E1 — the shell"; design-2.0 section 25 (Cockpit + Scenario Panel), section 49 appendix shape; screen-spec-2.0 SCREEN-014 to 016 headers and the corrections table rows for "§1–2" and "§26–31"; config/scenarios.php (all four entries, including our_grand_concert with documented => false); the Cockpit (D8) scenario region.

BUILD:

* Adopt the brief's section 49 shape as-is: { name, version, status, resources, objectives, actions, alerts, recommendations, finale }. Rules from the plan: version renders as a named absence ("N/A" with a title, because app.ruleset is null), and any "recommendations" entry that implies prediction renders as a named absence where the corpus is silent.
* ScenarioPanel.vue takes the resolved scenario array from the Cockpit as a prop. It switches on panels.* flags and widgets[] only. It never reads scenario.label for logic (display of the label as text is fine).
* Registry: a typed map of widget or flag key to async-free renderer component. An unrecognised key renders a labelled fallback ("This scenario declares a widget this build cannot draw") rather than an error or a blank. A scenario with every panel off renders only the baseline strip (E6 owns its content; E1 just must not break).
* Shared building blocks the panels reuse: ScenarioStatusBadge (status text plus glyph, including PARTIALLY DOCUMENTED from documented => false), ResourceMeter (value AND bar, labelled, "N/A" when unrecorded), AlertRow (glyph plus text). Build them here so E2 to E4 do not each invent one.
* Placement: inside the Cockpit's scenario region at all breakpoints (desktop right of the decision area per D8, tablet and mobile in the D8 order). The Cockpit header and state panel must render identically across all four scenarios.
* Loading, empty and error states for the shell per design-2.0 sections 29 and 30 (no skeleton class; DesignTokensTest forbids it).

ACCEPTANCE:

* A Pest test renders the same run header for all four Global scenarios (G-33's own assertion).
* A test adds a fifth scenario entry via config()->set (with a new widget set drawn only from existing keys) and asserts a panel renders with zero component changes.
* A grep over the diff for scenario names, scenario.label comparisons and key literals in conditionals returns nothing.
* Playwright: shell landmarks, status badge text, fallback text for an unknown widget key, 320px reflow, 44px sweep.

==============================================================  
PROMPT E2: URA panel, goals and Happy Meek (SCREEN-014)
========================================================================================================================

TASK: Create components/scenario/UraPanel.vue, registered in the E1 registry against the widget or flag keys that config/scenarios.php assigns to URA's panels. Depends on E1.

SKILLS (add to baseline):

* source-driven-development: URA is the least complex scenario (design-2.0 section 26). Every field must trace to config or an existing run column. Cite the config keys in your slice plan.
* doubt-driven-development: the screen spec asks for "potential reward" and "final-race contribution" for Happy Meek. Challenge each: sourced, entered, or unsourced?
* antislop-ui: URA is deliberately quiet. No decorative permanent panels beyond what the spec lists.

READ: screen-spec-2.0 SCREEN-014 (section 18); design-2.0 section 26 (URA Visual Language: career goals, Happy Meek, finale progression, avoid unnecessary permanent panels), section 22 (timeline glyphs), section 36; plan section 9 "E2 URA"; the run's existing goal data (read TrainingRunController, the goals relation and the run page props); config/scenarios.php URA entry.

BUILD:

* Career goals: the character's goals as recorded on the run, each with status as glyph plus text (● completed, ◉ current, ○ upcoming, × missed) and the turn deadline when stored. Mandatory races within the goals link to the D10 race screen if it exists.
* URA progression and finale preparation: a short strip derived only from recorded goals and config milestones. Do not hard-code race names or turns.
* Happy Meek module: current level (Trainer-entered value, badge Confirmed), duel availability (only if a stored or configured field exists), potential reward and final-race contribution. Render each of the last two as "N/A" with a title unless the repo already holds a sourced value. Do not estimate.
* Alerts: a mandatory goal race inside the deadline window is a Level 1 "Critical" AlertRow (glyph plus text), derived from the calendar data only.
* Recording a Happy Meek level or goal outcome goes through an existing write. If none exists, the module is read-only and the slice plan says so for the owner. Do not add a migration.
* Reuse E1's ResourceMeter, AlertRow and status badge. No new permanent panels.

ACCEPTANCE: props test covers goals in each state, Happy Meek with and without a recorded level, and N/A fields. A grep proves no scenario-name branching. Playwright covers goal states as text, the Critical alert, the N/A titles, keyboard path and the 44px sweep.

==============================================================  
PROMPT E3: Unity Cup panel, Team Cockpit (SCREEN-015)
======================================================================================================================

TASK: Create components/scenario/UnityCupPanel.vue (the Team Cockpit), registered against the config keys for Unity Cup's panels. Port the three Blade references. Depends on E1.

SKILLS (add to baseline):

* code-simplification + deprecation-and-migration: this is a port. Recover x-team-rank-gauge, x-team-race-panel and x-spirit-burst-roster (from the tree, or from git history if B1 deleted them) and carry over their props and logic. Behaviour parity first, redesign second. Delete a Blade component only when its grep consumer count is zero.
* source-driven-development: Spirit, Spirit Burst, Extreme Spirit Burst, team rank and Special Training numbers come from config/scenarios.php or run data only. Cite the keys.
* doubt-driven-development: the spec's "burst readiness", "recommended timing" and "projected benefit" are advice. Challenge each: derivable from stated config values, or not?
* frontend-ui-engineering: the roster is a labelled list or table with row-level keyboard reach, not a div grid.

READ: screen-spec-2.0 SCREEN-015 (section 19) and its Components table row (reuses x-spirit-burst-roster, x-team-rank-gauge, x-team-race-panel); design-2.0 section 27 (Unity Cup Visual Language: team cards, member rows, Spirit meters, burst indicators; the Team Panel is a first-class UI element), sections 15 and 45; plan section 9 "E3"; config/scenarios.php Unity Cup entry; the existing Blade components and the run page's Unity-related props.

BUILD:

* Team Panel (first-class): team rank as the recorded letter grade (for example "A+"), per-stat team grades (Speed, Stamina, Power, Guts, Wit) as letter plus text, only for values recorded on the run. Missing values read "N/A".
* Team composition and member rows: members from the run's recorded team data (stats, role), as a labelled table or list.
* Team races: reuse the x-team-race-panel logic with race facts from RaceCatalogSlot or recorded data. No win probability, no risk percentage.
* Spirit and bursts: current Spirit as value AND bar (ResourceMeter), burst indicators for Spirit Burst and Extreme Spirit Burst as glyph plus text, with the thresholds taken from config. Burst readiness is shown only as "current Spirit versus the configured threshold" (Calculated). "Recommended timing" and "projected benefit" are NOT built: render a named absence with a title.
* Special Training: shown as recorded or configured facts only.
* Recording Spirit or team state goes through existing writes. If none exists, read-only; list it as an open question. No migration.
* Responsive: desktop shows team cards beside the Spirit meters; mobile stacks, with no horizontal scroll.

ACCEPTANCE: props test covers a full team, a partial team and no team data. Grep proves no scenario-name branching. Playwright covers the roster by keyboard, meter text AND bar, burst glyph plus text, the named absence for timing/benefit, 320px reflow and the 44px sweep.

==============================================================  
PROMPT E4: Trackblazer panel, grade points, shop, epithets (SCREEN-016)
========================================================================================================================================

TASK: Create components/scenario/TrackblazerPanel.vue registered against Trackblazer's config keys. Port the three Blade references. Depends on E1.

SKILLS (add to baseline):

* code-simplification + deprecation-and-migration: recover x-grade-point-meter, x-shop-panel and x-epithet-checklist (tree or git history) and port props and logic. Grep consumers before any deletion.
* doubt-driven-development: the screen spec shows a "Recommended Purchase" card with a reason line. The plan holds it. Challenge every shop element for an implied recommendation, including sort order, default selection and badge wording.
* security-and-hardening: purchases write through runs.purchases.store. Keep its Form Request as the authority, tie errors to inputs, and never trust client-side cost or coin totals.
* source-driven-development: shop catalogue rows come from config trackblazer.shop. Cite the keys and the verification date.
* antislop-copywriting: shop rows are scan-read. Write item, cost, effect and duration as exact labelled values.

READ: screen-spec-2.0 SCREEN-016 (section 20) and its Components row (reuses x-shop-panel, x-grade-point-meter, x-epithet-checklist; shop recommendation HELD); design-2.0 section 28 (Trackblazer Visual Language: Grade Points, Shop Coins, Race Calendar, Shop Inventory, Rival Alerts; the Shop must be quickly accessible from the Cockpit), section 45; plan section 3 (Shop row) and section 9 "E4"; config/scenarios.php Trackblazer entry and trackblazer.shop; the existing purchases write and its Form Request.

BUILD:

* Grade Points meter: current value, thresholds from config, value AND bar, remaining to next threshold (Calculated). Shop Coins: the recorded balance, "N/A" when unrecorded.
* Shop catalogue: a labelled table or list of item, cost, effect, duration from config. Catalogue order is the config order. NO "Recommended Purchase" card, NO recommended flag, NO "best value" sort or highlight, NO default-selected item. State once, in text, that the shop rotation is not modelled so this is the catalogue only.
* Recording a purchase: select item, then a plain "Record Purchase" button posting through runs.purchases.store. Show the server's validation errors tied to inputs. Show purchased items for the run as a list.
* Epithet checklist: items from config or recorded data, each as checked / unchecked with glyph plus text. Do not infer completion.
* Rival races and race schedule: from RaceCatalogSlot or recorded data only, as facts. Rival alerts are AlertRows (glyph plus text) triggered only by data on the calendar.
* Do not print "Twinkle Star Climax". The finale reads "Trackblazer finale" and any official title is a named absence with a title citing conflict row 31.
* Quick access: the Cockpit's action area gets a "Shop" entry that scrolls or navigates to this panel's shop region with focus moved to its heading (2.4.11 safe).

ACCEPTANCE: a grep over the diff finds none of: "recommended", "best value", "Twinkle Star Climax" outside the named-absence title. Props test covers the catalogue, a recorded purchase and a rejected purchase. Grep proves no scenario-name branching. Playwright covers the shop by keyboard, the purchase round-trip and error text, glyph plus text for epithets, the 44px sweep and 320px reflow.

==============================================================  
PROMPT E5: Scenario Race Planner (SCREEN-017)
==============================================================================================================

TASK: Create pages/Career/RacePlanner.vue (or the route the D8 slice plan fixed for Cockpit sub-pages) and components/career/RacePlanList.vue. Race facts only, no win probability. Depends on D8. It reuses RaceCard from D10 if D10 has landed.

SKILLS (add to baseline):

* source-driven-development: every race field traces to RaceCatalogSlot, its migration, or the calendar data. Cite them. The 1400m Sprint-versus-Mile conflict means distance_band, if shown, carries source and confidence, and is never hard-coded.
* doubt-driven-development: the spec asks for "expected risk", a "Win probability: 84%" recommendation block and "No critical training deadline will be missed". Challenge each line and keep only what is derivable.
* code-simplification: reuse D10's RaceCard and D5's comparison row pattern. No second race component.
* performance-optimization: the planner lists a full calendar. Keep one eager-loaded query and paginate or group by phase.
* frontend-ui-engineering: reward comparison is a real labelled table with keyboard-reachable rows.

READ: screen-spec-2.0 SCREEN-017 (section 21), its header banner about win-probability being HELD under ADR-0016 and ADR-0020 section 4, and the correction "race simulator explicitly deferred"; design-2.0 sections 21, 32, 46; plan section 3 (Race win probability row) and section 9 "E5"; RaceCatalogSlot, components/RaceCalendar.vue, RacePanel.vue, the existing runs.races.store route.

BUILD:

* Four groups: upcoming, mandatory, optional, rival races, taken from the calendar and RaceCatalogSlot. A group with no data shows a named empty state (what is missing, why, what to do). The rival group is shown only where the data exists.
* Reward comparison: selected races side by side (up to four) with aligned rows: grade, distance, surface, fan gain, reward, skill-point gain, Grade Points or Shop Coins where config defines them. Missing fields read "N/A" with a title.
* Target alignment: plain matches / does not match / not recorded text for race distance, surface and style against the BuildTarget. No score and no percentage. Badge Calculated.
* Expected risk and win probability: render "Win probability: N/A" and "Expected risk: N/A", each with a title naming the ADR-0016 data blocker. Do not build readiness bands or LOW / MEDIUM / HIGH wording. Do not render the spec's recommendation block.
* Deadline awareness: show mandatory races with their turn and a Level 1 "Critical" AlertRow when the calendar puts one inside the current window. Do not claim "no deadline will be missed"; state only the next mandatory race and its turn.
* Actions: "Enter Race" posts via runs.races.store; "Skip" returns without recording.

ACCEPTANCE: a grep proves no win-percentage string and no readiness-band enum. Props test covers each group present and absent, N/A fields and a four-race comparison. Playwright covers the keyboard path through the groups and comparison table, the N/A titles, the Critical alert text, 320px reflow and the 44px sweep.

==============================================================  
PROMPT E6: Grand Concert panel, baseline strip only (SCR-017, design-2.0 only)
===============================================================================================================================================

TASK: Create the baseline strip for the fourth scenario, registered in the E1 registry for the "every panel off" case. Depends on E1. NOTE: this is SCR-017 (Grand Concert panel) from design-2.0. It is NOT SCREEN-017 (the Scenario Race Planner), which is E5. Do not mix the two.

SKILLS (add to baseline):

* doubt-driven-development: the point of this slice is restraint. Challenge every element for an invented mechanic. If it is not in config/scenarios.php or the run data, it does not appear.
* source-driven-development: cite the scenario's config entry (documented => false, the caps, the verification date) and the sourcing rows in plan section 3 ("Grand Concert mechanics").
* antislop-copywriting: the honesty line must be plain and specific, not apologetic filler.
* constraint-driven-development: G-41 and G-33 are the gates; make G-41's assertion a test.

READ: plan section 3 (Grand Concert mechanics row), section 9 "E6" (D-241, G-41); screen-spec-2.0 corrections rows "§1–2" and "§26–31" and the SCREEN-014 to 017 summary row; design-2.0 sections 25, 29, 49; config/scenarios.php our_grand_concert entry; docs/scenarios/07 (the stub).

BUILD:

* The baseline strip shows only what the config and the run hold: scenario name, the "PARTIALLY DOCUMENTED" status badge (glyph plus text), the five stat caps from config (they were corroborated by two independent sources on 2026-10-05), the config verification date, and the run's own tracked values (the ordinary career state the Cockpit already shows).
* Every panel is off. Songs, lessons and Performance Tokens do not exist in the corpus, and the panel says so in one plain sentence with a title pointing at the stub. Do not list placeholder songs, lessons, tokens, objectives or recommendations. Do not add input fields for mechanics nobody has documented.
* Advanced advisor features are limited: the Cockpit's recommendation for this scenario uses only the generic engine, and this panel adds nothing to it. Print that scope in text so the Trainer is not left guessing.
* Because the caps are sourced but the mechanics are not, show caps with badge Confirmed (sourced) and the mechanics line with badge Unknown.
* Use E1's ScenarioStatusBadge, ResourceMeter and AlertRow. No Grand-Concert-specific component beyond the strip itself.
* When mechanics are verified later, the change should be a config edit plus new widget renderers. State this in your slice plan so the owner knows the extension point.

ACCEPTANCE: G-41's assertion as a test (every panel flag off yields the strip and nothing else). A test proves no songs, lessons or token strings appear anywhere in the rendered props or markup. Grep proves no scenario-name branching. Playwright covers the badge text, the caps list, the honesty sentence and its title, 320px reflow and the 44px sweep.

==============================================================  
NOTES
======================================================================

* Name collision: SCREEN-017 is the Scenario Race Planner (E5). SCR-017 is the Grand Concert panel (E6, design-2.0 only). The E6 prompt says so in its first line.
* G-33 and the component names: the brief names UraPanel, UnityCupPanel and TrackblazerPanel, which can look like branching on scenario. The prompts allow those file names but require registration against widget or flag keys from config. I didn't have config/scenarios.php, so every prompt says to read the real key names and never guess them.
* Held items the spec still shows: Spirit "recommended timing" and "projected benefit" (E3), Happy Meek "potential reward" and "final-race contribution" (E2), the shop "Recommended Purchase" card (E4), "Win probability", "Expected risk" and the recommendation block (E5). The prompts render each as a named "N/A" absence, or leave it out where the plan says so.
* Blade references: the plan says Phase E keeps the six x-* references, but B1 may delete the zero-consumer ones. E3 and E4 tell the agent to recover them from git history if so.
* Writes: Happy Meek level, Spirit and team state have no known existing write routes. E2 and E3 therefore build those parts read-only if none exists, and report it as an open question. A migration needs owner approval.
* Dependencies: E1 first, then E2, E3, E4 and E6 can run in parallel. E5 needs only D8, and reuses D10's RaceCard if that has landed.
