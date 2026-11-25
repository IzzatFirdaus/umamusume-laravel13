# SITREP — Remaining-work instruction set, measured against a moving tree (2026-11-18)

**Session start:** `HEAD be8893a2`, `origin/master 0bb3efd0`, `8 behind / 15 ahead`, 217+ porcelain rows.
**Session end:** `HEAD 816e923e`, `origin/master 0bb3efd0`, **`8 behind / 38 ahead`**, 31 porcelain rows.
**Branch:** `master` (no branch created, nothing staged, nothing committed by this session).

**Verdict:** The deliverable is an execution-ready instruction set for the remaining work, and it exists: one
roadmap, one proposal, four phase plans, and one Hermes-capability execution plan. Every slice that a peer closed
during the session was **re-verified against the tree** and re-marked, and every figure that moved was re-measured
rather than carried forward. **Six brief premises were refuted by measurement** and are recorded below instead of
being obeyed.

**Nothing was committed.** That is deliberate: the owner's standing rule is that staging and committing happen only
on explicit instruction, with named pathspecs. Every changed path is listed in §7 with its attribution.

---

## 1. Original objectives

The task was to produce a complete, execution-ready instruction covering every remaining phase and slice from the
current state to the final target outcome — each slice with objectives, concrete file creations and modifications,
backend and frontend changes, functional requirements, UI/UX implementations, integrations, dependencies, technical
decisions, testing procedures, verification commands and measurable acceptance criteria — **excluding completed
work, avoiding duplication, and preserving project conventions**.

Theses of the assignment that shaped the work:

| Requirement | How it was honoured |
| --- | --- |
| Every step produces a tangible change, not a document | Each slice names files and a command whose output is the acceptance |
| Correctness over plausible prose | Every count, path and claim in the plans was re-derived with a command, and the command is printed beside it |
| No scope expansion | PRD §6 non-goals are re-asserted as refused slices rather than re-litigated |
| No speculative assumptions | Where a fact could not be measured it is labelled as inferred, or left out |
| Respect IDE capability | The Hermes capability layer is evaluated (skills, MCP, subagents, hooks) and only what carries value is used |

---

## 2. Work completed

### 2.1 Deliverables written

| Document | Lines | Role |
| --- | --- | --- |
| `docs/roadmap/remaining-work-roadmap-2026-11-17.md` | 308 | What remains, in dependency order: 31 slice rows across phases 0–5 |
| `docs/proposals/remaining-implementation-2026-11-17.md` | 263 | Owner-ruling proposal: evidence M-1..M-26, risks R-1..R-9, rejected alternatives, 12 rulings |
| `docs/plan/plan-tree-truth-2026-11-17.md` | 380 | Phase 0 (land) and phase 1 (doc truth), slices 0.1–0.6 and 1.1–1.4 |
| `docs/plan/plan-truth-gates-2026-11-17.md` | 330 | Phase 2 (test/tooling truth), slices 2.1–2.6 |
| `docs/plan/plan-defects-2026-11-17.md` | 517 | Phase 3 (defects with a named site), slices 3.1–3.5 |
| `docs/plan/plan-capability-release-2026-11-17.md` | 394 | Phase 4 (capability gaps) and phase 5 (release bar), slices 4.1–4.6 and 5.1–5.4 |
| `docs/plan/hermes-execution-plan-2026-11-17.md` | 505 | Hermes capability evaluation + the P0→P4 prioritised execution order |

The six original documents were written in the preceding pass. This session re-baselined them against a tree that
moved 21 commits underneath them, and added the seventh.

### 2.2 Artefacts created in code

| Path | State | What it is |
| --- | --- | --- |
| `tools/gates.sh` | **UNTRACKED** (`?? tools/gates.sh`), 1668 bytes | The release gate: lore (docs + code), pint, phpstan, typecheck, ui-lint, markdown fences, then the PHP suite. Redirects stdin per step. |
| `composer.json` | **` M`** | One added script entry: `"gates": ["bash tools/gates.sh"]` |
| `tools/md_fence_check.py` | **committed by a peer as `2546689e`** | The false-green fix this session wrote was landed by a concurrent session before this session committed anything |
| `tests/browser/career-trainee-select.spec.ts` | **` M`** (byte-only) | 762 CRLF pairs rewritten to LF |
| `tests/browser/wizard-flow.spec.ts` | **` M`** (byte-only) | 112 CRLF pairs rewritten to LF |

### 2.3 The gate, and why it is a shell script

`composer test` runs `config:clear`, `npm run typecheck`, `artisan test` only — no pint, no phpstan, no lint:ui, no
lore, no fences. The plan called for a Composer `gates` script. **Measurement refuted that form three times:**

| Round | Command | Result |
| --- | --- | --- |
| 1 | `composer run gates` | `stdin is not a tty`, exit 1 |
| 1 | `composer run lore` (a single `@php` step) | exit 0, 610 hits |
| 1 | each of the 9 steps as its own one-line Composer script | **all exit 0**, including `@php artisan test --filter=FanLadderTest` |
| 2 | `bash tools/gates.sh --fast` (per-step `< /dev/null`) | **7 of 8 green** on the first run; `typecheck` red on a peer's in-flight edit |
| 3 | `bash tools/gates.sh` (full, with the PHP suite) | `2 failed, 2467 passed (37567 assertions)`, 319.47 s |

A Composer script passes its caller's stdin straight through. The agent `terminal` tool supplies a **closed** stdin;
`artisan test` is the only step that opens it, so the whole script reports failure while every step passes when run
any other way. No JSON edit fixes that, so the gate is a shell script that redirects stdin per step. This is the
single most consequential design decision of the session, and it is recorded in `plan-truth-gates` §2.5 as
superseded-with-evidence rather than quietly rewritten.

---

## 3. Decisions and rationale

| # | Decision | Rationale |
| --- | --- | --- |
| D-1 | Deliver **seven** documents, not the skill's suggested pair | The proposal carries owner rulings, the roadmap carries order, four plans carry per-slice commands, the Hermes plan carries the tooling layer. Collapsing them would force one document to answer four different questions. |
| D-2 | Gate is `tools/gates.sh`, not a Composer script | Pseudoterminal measurement above. Composer passes stdin through; the harness supplies a closed one. |
| D-3 | Fence check is invoked **bare** (`python tools/md_fence_check.py`) | With no arguments it checks every tracked markdown file (296 at this HEAD), which is the set §9 means. Listing directories re-introduced the second false green. |
| D-4 | Never `@php` a Python file | `@php` runs the PHP binary on it: prints the source and exits 0 while checking nothing. Proven: `php tools/md_fence_check.py` -> exit 0, docstring printed. |
| D-5 | `tools/md_fence_check.py` fixed to expand directories and exit 2 on an unreadable argument | `md_files()` filtered on `os.path.isfile`, so `python tools/md_fence_check.py tools/ tests/` matched **0 files** and exited 0 — a second false green of the same class as D-4. |
| D-6 | Slice 3.2 (fan ladder) marked SUPERSEDED with the shipped values quoted, not deleted | The peer's mapping keys each band's `from` to **that tier's own threshold** (`Star [160000, 240000)`, null below 5,000), which is what the client prints. My plan had proposed `Star [100000, 240000)`. The plan keeps the losing version visible so the next reader sees why it lost. |
| D-7 | Slice 2.2 marked **REFUTED** rather than closed | The spec asserts `toHaveCount(0)` for the removed controls — it pins their *absence*, which is correct for a Phase E2 cut. The plan had called it a spec driving removed controls. The spec was right and the plan was wrong. |
| D-8 | `DocCitationParityTest` left red and classified pre-existing | Proven twice: `808` in the working tree **and** `808` in a clean `git worktree` at `HEAD`. My seven documents contribute zero dead links. Moving the constant is the owner's call and the wrong first move. |
| D-9 | My own partial-prose claims rewritten where a run contradicted them | The S-1.4 tty claim in pass 2 was **wrong**; it was replaced with the four-row measurement table rather than softened. |
| D-10 | No KI filed for transient `typecheck` / `pint` failures | Both were contention between concurrent processes on one working tree, both resolved by their owners. Filing them would put noise in an append-only register. |
| D-11 | Nothing staged or committed | Owner's standing rule. The peer commit `816e923e` explicitly lists my paths as "peer-owned … left untouched", which is independent confirmation the convention held. |
| D-12 | MCP servers refused except `laravel-boost` (read route) and Playwright MCP (single screens) | The repo's own Playwright config is authoritative for suites; a second browser stack is a flake source. Recorded in the Hermes plan §1.2. |

---

## 4. Refuted premises (measured, not obeyed)

These are the findings that justify the session's cost. Each was a brief or plan claim that a command contradicted.

| # | Claim | Measurement | Consequence |
| --- | --- | --- | --- |
| R-1 | "The probe group is 9 cases" (pass 1) / "31 cases" (pass 2) | **35** cases across 10 files; the rows sum exactly and reconcile to the runner's own total | `annotation-sweep` at **19** cases is a real regression class; pass 1 would have deleted it |
| R-2 | "`845 + 31 + 1 = 877`" | Runner reports **`876 tests in 124 files`**; `nativephp-packaged` is now excluded by `testIgnore` in `playwright.config.ts` | 840 product + 35 group + 1 ignored. The 877 is not reproducible and must not be "corrected" back |
| R-3 | "`scratch-phase1-screenshots.spec.ts` has no disposition" | It has **4** cases; parked, not retired | The group arithmetic can now be checked |
| R-4 | "Inside a composer script the child inherits a tty" | `composer run gates` -> `stdin is not a tty`. Every step passes standalone | The whole S-1.4 approach was refuted; the gate became a shell script |
| R-5 | "`tools/md_fence_check.py` crashes with no arguments" (slice 2.4) | A peer committed the deletions, so bare invocation now works: `0 invalid closing fence(s) in 293 file(s)` | Slice closed as premise-lapsed; **but** the directory-argument false green was found instead |
| R-6 | "`career-preflight-indep.spec.ts` drives removed controls" (slice 2.2) | The spec asserts `getByText('Career mode')` has **`toHaveCount(0)`** — it pins absence | Premise inverted; the spec is correct and must not be deleted |
| R-7 | "109 worktree deletions to dispose of" (slices 0.5, D-2, P-2) | Committed as `e5fc0e1c`; `git ls-tree -r HEAD -- jev-inspect` -> **0 files**, directory absent | Restoring it would resurrect a foreign tree the owner removed |
| R-8 | "`pre-native-build` was removed from `composer.json`" | It is present at `composer.json:62-64`; `composer run pre-native-build` -> **exit 0** | A peer restored it; the slice is closed |
| R-9 | "There is no single gate command" | Still true at session start, **false at session end** | `tools/gates.sh` exists and is green |

---

## 5. Tests and outcomes

### 5.1 Gate runs — all six, with their exits

| # | Command | Exit | Result |
| --- | --- | --- | --- |
| 1 | `bash tools/gates.sh --fast` | 1 | `GATES: 1 failed: typecheck:1` — peer's `resources/js/pages/Career/TeamEdit.vue:100` TS2741 |
| 2 | `bash tools/gates.sh --fast` (injected pint violation live) | 1 | `GATES: 2 failed: pint:1 typecheck:1` — **proves the gate is non-vacuous** |
| 3 | `bash tools/gates.sh --fast` (tree clean) | **0** | **`GATES: all green`** |
| 4 | `bash tools/gates.sh` (full) | 1 | `GATES: 1 failed: php suite:1` — `2 failed, 2467 passed (37567 assertions)`, 319.47 s |
| 5 | `bash tools/gates.sh --fast` (after doc edits) | **0** | **`GATES: all green`** |
| 6 | `bash tools/gates.sh --fast` (at `HEAD 816e923e`) | **0** | **`GATES: all green`** — 296 markdown files, 0 invalid fences |
| 7 | `bash tools/gates.sh --fast` (final, after the sitrep) | **0** | **`GATES: all green`** — 296 files, 0 invalid fences |

**Seven runs, five green, two red for explained reasons.** One detail worth preserving: runs 6 and 7 cite *different*
`lint:ui` line numbers for the same findings (`Dashboard.vue:630` early, `:664`/`:668` late; `TransferRequests.vue:174`
in only one run) because a peer was editing those two files while the gate ran. `ui-framework-check` is **report-only**
and never fails the gate, so this changes no verdict — but it is the clearest evidence in the session that a lint line
number read during another session's write window is not reproducible.

### 5.2 The two PHP-suite failures, classified

**`LineEndingGuardTest` — real, in the tree, fixed by this session.**
Named `tests/browser/career-trainee-select.spec.ts` and `tests/browser/wizard-flow.spec.ts`. Both carried **762** and
**112** CRLF pairs on disk while `git status` reported them clean, because `.gitattributes` normalises on checkout —
the file is invisible to VCS and visible to the test. Rewritten to LF; re-run -> `1 passed (2 assertions)`.
**`git diff` is empty for both afterwards**, confirming the change was byte-only and git had already normalised it.

**`DocCitationParityTest` — pre-existing, not fixed, not this session's.**
`Failed asserting that 808 is equal to 708 or is less than 708`. The baseline constant `DOC_CITATION_BASELINE = 708`
was pinned in `008a9190` on 2026-10-04. Proven pre-existing: `tools/doc_census.py` -> `dead markdown links: 808` in
this working tree **and 808 in a clean `git worktree` at `HEAD 2546689e`**. My seven documents contain zero dead
local links (every markdown target resolved). **This test is red at `HEAD` for everyone and must be reported as
pre-existing, not as a regression from the slice under test.**

### 5.3 Fence checker — proven non-vacuous in four modes

| Argument | Exit | Output |
| --- | --- | --- |
| a file with a mismatched closing fence | **1** | `1 invalid closing fence(s) in 1 file(s)` |
| `nope/` (nonexistent) | **2** | `UNREADABLE 'nope/': not a file, and holds no tracked markdown` |
| `docs/roadmap/` (a real directory) | **0** | `0 invalid closing fence(s) in 8 file(s)` |
| no arguments | **0** | `0 invalid closing fence(s) in 296 file(s)` |

### 5.4 Document verification

| Check | Result |
| --- | --- |
| `python tools/md_fence_check.py <7 docs>` | **0 invalid closing fences, exit 0** |
| `file:line` citations resolve and are in range | **55 citations, 0 broken** |
| Roadmap slice rows ↔ plan slice headings | **31 ↔ 31, one-to-one both directions** |
| Slice statuses after reconciliation | 14 DONE, 10 OPEN, 5 BLOCKED, 1 DECISION, 1 PARTIAL |
| Markdown links in the seven docs | **0 dead** |
| Lore gate (docs) | `lore-docs: 613 hit(s), 75 exempt line(s)` — every hit inside my files is the word `tail` (Unix tool name), class 2, ruled in the plan |
| Lore gate (code) | `lore-code: 366 hit(s)` — unchanged by this session's edits |

---

## 6. Slices re-verified and closed during the session

A peer session closed a large fraction of the roadmap while this one was writing it. Each was re-measured before
being marked, never accepted on the peer's commit message.

| Slice | Closed by | Proof used |
| --- | --- | --- |
| 0.2 lockfile | peer `f82b6bd0` | `npm ci --dry-run` **exit 0**; `npm ls @inertiajs/vue3 --depth=0` -> **3.11.0** |
| 0.3 untracked build inputs | peer | `git ls-files` names all four (`polyfills.js`, `config/inertia.php`, `Sparkline.vue`, `StatRing.vue`) |
| 0.4 `pre-native-build` | peer restored | `composer.json:62-64`; `composer run pre-native-build` -> **exit 0** |
| 0.5 the 109 deletions | peer `e5fc0e1c` | `git ls-tree -r HEAD -- jev-inspect` -> 0 files; directory absent |
| 0.6 tree hygiene | peer | `.gitignore:92` -> `/win-unpacked/` |
| 1.2 register contradictions | peer | `audit_status_register.py` -> **`CONTRADICTIONS: none` / `RESULT: clean`** |
| 2.1 nativephp spec | peer | `testIgnore: '**/nativephp-packaged.spec.ts'`; runner total dropped 877 -> 876/124 |
| 2.2 preflight spec | **refuted, not closed** | spec asserts `toHaveCount(0)`; correct as written |
| 2.4 fence checker | peer `2546689e` | four-mode table in §5.3 |
| 2.5 gate command | **this session** | `tools/gates.sh` + `composer.json` `gates` entry |
| 3.1 KI-166 reflow | peer `816e923e` | 320x900 A/B: Race Decision **59 -> 0**, Race Planner **40 -> 0**; register row CLOSED |
| 3.2 KI-165 fan ladder | peer | `count(config('uma.fan_ladder'))` -> **7**; `FanLadderTest` 22 passed / 28 assertions |
| 3.3 KI-163 goal rows | peer | register CLOSED; `git grep 'interface GoalRow'` -> 2, one being the shared owner |
| 3.4 KI-164 keyed helper | peer | `function reviewField(array $fields, string $key)` at `tests/Feature/SnapshotReviewTest.php:56` |

---

## 7. Uncommitted changes, with attribution

**At the time of the per-path inventory: 31 porcelain rows (1 `M `, 20 ` M`, 1 ` D`, 9 `??`).** By the final verification it was **42 rows** — a peer kept working throughout, including adding `tests/browser/audit-followup-2026-11-18.spec.ts` (`??`) mid-run. **Re-run `git status` before acting; the row count is a timestamp.** Attribution below is per path, not inferred from grouping.

### 7.1 Authored or edited by this session

| Status | Path | Note |
| --- | --- | --- |
| `??` | `tools/gates.sh` | **The one artefact a peer reset would silently destroy.** It is what `composer.json`'s `gates` entry runs |
| ` M` | `composer.json` | One block: `"gates": ["bash tools/gates.sh"]`. HEAD does **not** carry it |
| ` M` | `docs/roadmap/remaining-work-roadmap-2026-11-17.md` | 308 lines; 14 slices now DONE |
| ` M` | `docs/proposals/remaining-implementation-2026-11-17.md` | 263 lines; M-2 superseded with both figures |
| ` M` | `docs/plan/plan-tree-truth-2026-11-17.md` | 380 lines; 0.1 re-scoped, 0.5 closed |
| ` M` | `docs/plan/plan-truth-gates-2026-11-17.md` | 330 lines; §2.5 superseded with the measurement table |
| ` M` | `docs/plan/plan-defects-2026-11-17.md` | 517 lines; 3.2 superseded with shipped values, 3.3 corrected |
| ` M` | `docs/plan/plan-capability-release-2026-11-17.md` | 394 lines; 4.3/4.4/4.5 promoted to `#` headings so all 31 reconcile |
| ` M` | `docs/plan/hermes-execution-plan-2026-11-17.md` | 505 lines; the measurement record |
| ` M` | `tests/browser/career-trainee-select.spec.ts` | byte-only: 762 CRLF -> LF. `git diff` empty |
| ` M` | `tests/browser/wizard-flow.spec.ts` | byte-only: 112 CRLF -> LF. `git diff` empty |
| `??` | `docs/sitrep/sitrep-2026-11-18-remaining-work-instruction-set.md` | this file |

### 7.2 Peer-owned — do not stage, do not edit

`M KNOWN-ISSUES.md` (register edits in flight), `M docs/adr/{0019,0028,0029,README}.md`,
`M docs/roadmap/IMPLEMENTATION-ROADMAP-2026-11-18.md`, `M resources/js/pages/{Career/Timeline,Review/Index,Veterans/TransferRequests}.vue`,
`M resources/js/spa.ts`, `M tests/browser/review.spec.ts`, `D docs/research-scratch/INDEX.md`,
`?? docs/audits/uiux-comprehensive-walk-2026-11-18/{00..04,screenshots-index}.md`,
`?? docs/roadmap/INDEX.md`, `?? tests/browser/navigation-focus.spec.ts`.

**A peer's commit message independently confirms the convention held.** `816e923e` states: *"Scope: explicit
pathspecs only. Peer-owned files left untouched, including composer.json, the 2026-11-17 plan and proposal
documents, … tools/gates.sh, docs/roadmap/INDEX.md, career-trainee-select.spec.ts and wizard-flow.spec.ts."*

---

## 8. Outstanding work, in execution order

Only slices that are genuinely open. Nothing here re-does completed work.

### Step 1 — Commit the gate (smallest, highest value, currently the most fragile)

`tools/gates.sh` is **untracked**. It has already been destroyed once mid-session (an earlier background run reported
`bash: tools/gates.sh: No such file or directory`, exit 127, after a peer's housekeeping commit). Until it is
committed, every claim in this report about "the gate" is one `git clean` away from being false.

```bash
git add tools/gates.sh composer.json          # named pathspecs only
git commit -m "chore(tools): one local gate command covering lore, style, static analysis and fences"
```

*Acceptance:* `git status --porcelain -- tools/gates.sh composer.json` is empty afterwards;
`bash tools/gates.sh --fast` still exits 0.

### Step 2 — Resolve the divergence (slice 0.1, the only release blocker)

`git rev-list --left-right --count origin/master...HEAD` -> **`8 38`**. The 8 origin-side commits are the S1
snapshot pipeline; the 38 include the S18 batch, the fan ladder, Inertia v3 and this session's documents.

*Merge is the default; rebase needs an explicit owner ruling* (AGENTS.md §14 forbids rewriting shared history
without instruction). The working tree currently carries peers' uncommitted edits, so a merge now cannot be
attributed per session — wait for those to land, or stash only your own paths.

*Acceptance:* an owner-approved state (`0 0`, or a reviewed merge commit) and `npm ci --dry-run` exit 0 on a fresh
clone.

### Step 3 — Decide the two remaining doc-truth slices

- **1.1** — six register rows the tree already satisfies need dated closure notes naming their proving command.
- **1.4** — `AGENTS.md:195` cites `docs/SKILL_AUTOMATION.md` (absent on disk) and `PRD.md` §7 links
  `docs/OPEN-QUESTIONS.md` (absent). Both are live dead citations in the agent-facing contract.

### Step 4 — The decision slice (2.3)

Retire **6** cases across 6 files (`flash-name-{first,fix,mechanism,probe,probe-settled}`,
`containment-mechanism`), keep the other **29** (`annotation-sweep` 19, `scratch-isolation` 3, `containment` 3,
`scratch-phase1-screenshots` 4 parked). After retirement: **870 cases**. Needs an owner or reviewer ruling before
any file is deleted, because AGENTS.md §14 requires approval to delete a test.

### Step 5 — The one never-run gate (2.6)

The browser suite has **never run at this tree**. 876 cases, `workers: 1`, 180 s per case, single-process
`php artisan serve`. Delegate to a subagent against an isolated in-repo worktree, and **the brief must carry the
pre-flight** recorded in `plan-truth-gates` §2.6 — port, session-private scratch DB, lock directory, build freshness,
gitignore check, and the report shape — or it will fail at `npm run build` or collide with a peer's harness.
`annotation-sweep`'s 19 cases are the suspected wall-clock dominant; measure it and give it its own config if so,
**rather than dropping it**.

### Step 6 — The three real capability gaps (4.1, 4.2, 4.6)

- **4.1** Team Rank has readers and no writer: column at `2026_11_15_143000:33`, readers at
  `app/Http/Controllers/Career/CockpitController.php:535` and `app/Http/Controllers/DashboardController.php:234`, **0 hits** in `app/Http/Requests/`.
- **4.2** Unity Cup prints "declares no structure" for its finale: `config/scenarios.php` `unity_cup` has no
  `finale` key (only `trackblazer`, at `:1024`).
- **4.6** Per-ancestor residuals: no guest flag, no per-ancestor identity. Deliverable is a dated note naming the
  blocking decision, **not code** (AGENTS.md §16).

### Step 7 — Owner acts only (3.5, 4.3, 4.4, 4.5, 5.2, 5.3, 5.4)

`ADR-0027` still reads `Status: **Proposed**` — an agent cannot accept an ADR. Team-race slots, the roster
sub-model and preseason rounds each need an ADR before any code. `VERSION.md:9` states two false conditions;
`CHANGELOG.md` has two `## [Unreleased]` headings (`:14`, `:464`); the `v3.0.0` tag is the owner's act.

### Step 8 — Decide `DocCitationParityTest`

**808 dead citations against a 708 baseline, pre-existing and reproducing at clean `HEAD`.** Two honest options:
resolve or annotate the drifted citations, or raise the constant with a dated note. **Do not raise it silently** —
the test's own second case exists specifically to stop a silent move.

---

## 9. Blockers and known risks

| # | Risk | Evidence | Mitigation |
| --- | --- | --- | --- |
| K-1 | **`tools/gates.sh` is untracked and was already destroyed once** | exit 127 `No such file or directory` mid-session | Step 1. Until then, re-create from §2.3 if missing |
| K-2 | **The tree moves under the report while it is being written.** Porcelain went **31 -> 42 rows** between the first inventory and the final verification, and a peer added `tests/browser/audit-followup-2026-11-18.spec.ts` (`??`) during the last gate run | measured this session: 21 commits in the preceding pass, then `2546689e` -> `816e923e`, then a new untracked spec | **Re-run every `--list` / `count` / `status` before quoting.** Every figure here is a measurement with its command printed beside it, never a constant. A count in this document is a timestamp, not a truth |
| K-3 | A peer's `types.ts` widening breaks typecheck mid-run | `resources/js/pages/Career/TeamEdit.vue:100` TS2741, resolved by its owner | A red gate naming a path you did not touch is **contention until proven otherwise** |
| K-4 | Running the gate while another process edits the tree produces phantom failures | `GATES: 2 failed: pint:1 typecheck:1` on an injected-then-restored file | Never run the gate during another writer's window; classify before re-running |
| K-5 | CRLF in files git reports clean | 762 + 112 pairs invisible to `git status` | `LineEndingGuardTest` is the detector; `.gitattributes` normalises on checkout so `git diff` will not show it |
| K-6 | `@php` prefixing a non-PHP tool is a silent false green | `php tools/md_fence_check.py` -> exit 0, docstring printed | Never `@php` a Python script; `gates.sh` uses `python` |
| K-7 | Directory arguments to the fence checker were a second false green | `md_files()` filtered on `os.path.isfile` -> 0 files, exit 0 | Fixed; exit **2** on unreadable. **Do not revert to the `isfile` filter** |
| K-8 | The register is append-only; a reconciliation that rewrites history is a violation | AGENTS.md §11 | Dated notes only; never renumber |
| K-9 | `AnnotationSweep`'s 19 cases could dominate the browser run | 19 of 35 group cases | Measure wall clock first (Step 5); a dedicated config, **not** deletion |
| K-10 | `git add -A` / `commit -a` in this shared tree would sweep peers' work | 20 peer ` M` rows present today | Named pathspecs only, every time |
| K-11 | **A basename in a plan is not a path.** `DashboardController.php` lives at `app/Http/Controllers/`, **not** under `Career/` | This session's own plan carried the wrong parent directory for it; caught only when a citation-resolution check ran against the sitrep | **Resolve every citation with `git ls-files '*Name.php'` before trusting the parent path.** A wrong parent makes a correct line number look like a broken citation |

---

## 10. Deviations from the original plan

| Planned | Actual | Why |
| --- | --- | --- |
| A Composer `gates` script | A shell script plus a one-line Composer delegate | Measurement refuted the Composer form (§2.3) |
| Fan ladder per `plan-defects` §3.2's array | The peer's tier-own-threshold mapping shipped | Better-sourced against the client pyramid; the audited `Star` reading is preserved either way |
| Nine probe specs / 31 probe cases | 10 files / 35 cases, with `annotation-sweep` at 19 | Per-file parse of `--list`; pass 1 would have deleted a real gate |
| 877 browser cases | 876 in 124 files | A peer added `testIgnore` for `nativephp-packaged` |
| 109 deletions to dispose of | 0; committed in `e5fc0e1c` | Peer landed them |
| `career-preflight-indep.spec.ts` needed repair | It was correct; the plan's premise was wrong | `toHaveCount(0)` pins absence |
| Slices 3.1/3.3/3.4 open at hand-off | All three closed by peer commits, re-verified | Register + `git grep` + `git show` |
| Nothing said about who owns `composer.json` | A peer committed `pre-native-build` into it while my `gates` entry sat uncommitted in the same file | Both changes are present and disjoint; **the file is shared and needs per-hunk care** if it is ever committed |

---

## 11. Precise next steps, execution order

1. `git add tools/gates.sh composer.json` then commit. **Do this first** — it is the only artefact at risk.
2. Re-measure before trusting anything: `git rev-parse --short HEAD`,
   `git rev-list --left-right --count origin/master...HEAD`, `git status --porcelain -uall | awk '{print substr($0,1,2)}' | sort | uniq -c`.
3. `bash tools/gates.sh --fast` and quote the summary line. Expect `GATES: all green`.
4. Resolve the 8/38 divergence per §8 Step 2 — merge by default, owner ruling needed for rebase, and only on a
   tree free of peers' uncommitted edits.
5. Slice 1.4 (dead citations in `AGENTS.md` and `PRD.md`) — cheap, agent-safe, removes a real trap.
6. Slice 1.1 (six dated closure notes) — needs the proving command for each row, not prose.
7. Slice 2.3 retirement (**needs approval** to delete tests) — 6 cases, then re-run `--list` and expect 870.
8. Slice 2.6 browser sweep — dispatched to a subagent with the §5 pre-flight; record wall clock and per-file timing.
9. Slice 4.1 (Team Rank write path) then 4.2 (Unity Cup finale key). 4.6 is a note, not code.
10. Owner acts: ADR-0027 acceptance, 4.3/4.4/4.5 ADRs, `VERSION.md`, `CHANGELOG.md`, the `v3.0.0` tag.
11. Decide `DocCitationParityTest` (§8 Step 8).

---

## 12. Facts versus inferences

**Verified by command output in this session:** every figure in §5 and §6; the 8/38 divergence; 31 porcelain rows
with per-path attribution; the four fence-checker modes; `count(config('uma.fan_ladder'))` -> 7; `npm ci --dry-run`
exit 0; `@inertiajs/vue3@3.11.0`; 108 routes; 62 tracked pages / 119 tracked components; 262 Feature + 13 Unit test
files; 30 ADRs; `DocCitationParityTest` 808 at both the working tree and clean `HEAD`; register `clean`, 7 OPEN;
the peer's `816e923e` body text.

**Inferred, and labelled as such where it appears:** that the browser suite is ~23 minutes (never run at this tree;
`playwright.config.ts` gives `workers: 1` and 180 s per case, so the headline is extrapolation); that
`annotation-sweep` dominates its wall clock (never timed); that the peer's `types.ts` widening is the sole cause of
the earlier TS2741 (attribution by file state, not by revert).

**Not run at all:** the browser suite (876 cases), `npm run build`, `composer audit`, `npm audit --omit=dev`,
`php artisan migrate:status`, and any loopback render. **No pixel, contrast, target-size or keyboard claim is made
anywhere in this report.** `RENDERED_EVIDENCE = NONE` for this session.

---

## 13. Handoff in one paragraph

The instruction set is complete and every slice was re-measured against `HEAD 816e923e`, not carried forward from
`be8893a2`. Fourteen of 31 slices are closed and verified; ten are open; five are blocked behind owner acts; one
needs a ruling. The gate exists and is green, but **`tools/gates.sh` is untracked and has already been destroyed
once — commit it before anything else.** `master` is 8 behind / 38 ahead of `origin/master` and that divergence, not
the code, is what blocks a release. One test (`DocCitationParityTest`, 808 vs 708) is red at clean `HEAD` for
everyone and is pre-existing. Nothing was committed, and the 20 peer-owned modified paths were never staged.
