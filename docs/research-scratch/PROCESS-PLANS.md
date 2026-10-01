# Process Plans and Reports

## Provenance

This document consolidates the following source files verbatim (no summarization, no deduplication):

- `docs/requests/2026-10-01-scratch-tree-reorganization-plan.md`
- `docs/requests/reports/2026-09-30-c5-down-enforcement-gap.md`

**Placement note:** The C-5 enforcement gap file (`2026-09-30-c5-down-enforcement-gap.md`) was listed in Group A in the original consolidation plan. It is placed here in Group F (Process/Plans) because it is an engineering process finding about an enforcement gap, not governance or register material. This decision preserves the intent that Group A contains binding rules and gate definitions, while Group F holds process analyses and implementation plans.

---

## 2026-10-01-scratch-tree-reorganization-plan.md

### Scratch-tree reorganization implementation plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development` (recommended) or `superpowers:executing-plans` to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax. Do not start Task 3 until Task 1 returns all-clear and the owner has answered §7.

**Goal:** Sort the loose files in `research-scratch/` into named buckets and repoint every live citation to them, while leaving the four other named directories untouched on documented grounds.

**Architecture:** One disposable Python tool owns the whole operation: classify, prove citations, move, journal, rewrite, verify. It moves only files no tracked document cites, and it rewrites only citation lines a reader would actually follow, because the rest of them record where evidence sat at measurement time.

**Tech Stack:** Python 3.12 stdlib (`python`, not `python3`, on this box), `git -c core.quotepath=false`, `make lore`, Pest via `php artisan test --compact`.

**Tool:** `research-scratch/scripts/reorg_scratch.py`, created 2026-10-01 and verified three ways: classifier self-test 7/7; `--scan` reporting 53 candidates, 44 movable, 9 held; and the rewrite path dry-run against a synthetic mapping for `research-scratch/data/json/character-cards.json`, which found 6 citing files, refused `KNOWN-ISSUES.md` at the guard, and classified the 8 hit lines in the first three files as LIVE 3 / RECORD 2 / UNKNOWN 3, editing nothing. No move has been performed.
**Spec:** this file. The evidence it argues from is `research-scratch/DOCUMENTATION-INVENTORY-2026-09-30.md` §10-§11 and `docs/GATE-REGISTRY.md` G-60.

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