# Trainer Desk — Frontend Development Plan

**Status:** Phase 4 (Implementation) active through Slice 10, the maintenance and drift slice: the register corrected against its own commits, the lore gate given a line-scoped marker, the shop price answered at its field, and one bounded proposal written about `scenario_slots`. No panel work, no seeding migration, no new tokens.
**Intent (R41), as the owner stated it:** "Slice 8 is the scenario panel set: the Unity Cup and
Trackblazer surfaces built on Slice 7's payloads and buckets, plus the missing writers." Nothing
about that intent needed a new table, and none was added: rank, fatigue and purchases ride
`turn_events` payloads, circles and the shop countdown ride columns on existing tables.
Slice 7 was the schema session. Slice 6 landed the
frontend residuals; Slice 7 gave Grade Points the period they belong to (KI-10's schema half) and
gave `turn_events.deltas` a typed shape (D-226), then declined ADR-0005 for Phase 1 per R37. Phase 6
remains unfrozen and unstarted. Unity Cup and Trackblazer panel UI is Slice 8, not this slice.
**Last Updated:** 2026-09-29 (Slice 10: the register's KI-18 closure and KI-15 de-duplication at
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

## Slice Exit Criteria

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

1. **Gates green with pasted output** — `pest`, `pint --test`, `phpstan --no-progress`, `make lore` (manual), `make lore-code` (manual), `npx vite build`, `DesignTokensTest` all pass; outputs recorded in commit message or linked CI run. **Migration gate:** `migrate:fresh --seed` is a destructive drop and is not run here; the equivalent evidence is `php artisan migrate` plus `db:seed` applied to a **fresh empty scratch DB** (`DB_DATABASE=/tmp/…`), which proves the same thing with no blast radius on the shared dev file. **Lore baseline (R51, 2026-09-29):** `lore-docs` 98 hits / 51 exempt lines, `lore-code` 7; a rules table quoting a banned word to rule on it carries a line marker naming `lore-ignore-line` with `class=` set to one of the four allowed-hit classes and `cite=` set to the rule it answers to (the exact form, with a worked example, is in `docs/GATE-REGISTRY.md`; markers are legal inside `docs/` and nowhere else), and `LoreGateParityTest` fails a marker outside `docs/`, one missing either half, or a runner that stops honouring the filter.
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

    ```
    git fetch
    git rev-list --left-right --count HEAD...origin/master    # "<ahead>\t<behind>"
    ```

    The **behind** count must be `0`, and it must be read as the second column. `git log --oneline origin/master..HEAD` is not a substitute and is the form this criterion replaces: it lists commits reachable from `HEAD` but not `origin/master`, which is commits **ahead**, so it reports `0` on tip, on a branch 7 behind, and on one 700 behind alike — it cannot fail for the reason it is written for. The form was given as a merge-time rule with that reasoning and was wrong in the same way. This is the fifth time one run produced a check that ran, passed, and proved nothing about the case it was written for. The other four are: a search test that asserted `active = -1` while its fixture ordered the rows the other way, so it was green against a relation the query never produces; the `data-combobox-selected` contract, which cited a hook that never shipped and therefore constrained nothing; a brief that said `skipped 5` where the fixture showed a different count, arithmetic checked against nothing; and the stale fork 7 commits behind that this rule now catches, where `origin/master..HEAD` was read as behind and so reported `0` on a branch that was not on tip. KI-39 (`SkillFactory` deriving `match_key` with a different algorithm than production) is a sixth instance of the same class, at fixture grain rather than branch grain. **The general guard is a fingerprint, not more tests:** before citing any two measurements as one, compare `git rev-parse HEAD` and a file hash between them, and treat a line number that disagrees with an expected value as the check firing rather than as noise. `rev-list --left-right --count` is the same discipline against a branch: one command, two columns, one that is allowed to be non-zero.
12. **A subagent past 100 turns files a checkpoint before it keeps going.** A subagent that has run more than 100 turns must stop and report — what landed, what is in flight, what is blocked, and the tip SHA of every branch or worktree it created — before continuing. The report may be three lines; the requirement is only that a hard turn cap never loses work silently. This exists because two implementers on the character-detail-page branch hit the 150-turn cap in one run, and one reached it with no report at all, so the main session discovered an un-stated half-ported surface by diffing, not by reading a hand-back. A checkpoint is what turns "lost in a killed subagent" into "resumable."
13. **Before creating a file that touches schema, migrations, or a same-named class, check for a collision on another tracked branch.** Criterion 11 asks whether *this* branch is fresh against `origin/master`; this asks a different question — whether *another* branch already created the files about to be created. Run:

    ```
    git fetch --all
    git log --oneline --all -- database/migrations/*create_umamusume_profiles_table* \
        app/Models/UmamusumeProfile.php app/Actions/StoreCharacterProfiles.php
    ```

    Generalize the path list to the files the feature will create or the classes it will name. If any tracked branch (not just `origin/master`) has touched a file about to be created, **stop and reconcile before branching** — do not open a parallel implementation of the same table or class. This is the check that would have caught the character-detail-page collision at the start instead of at merge time: two sessions in two worktrees each wrote a migration creating `umamusume_profiles` (under different filenames, so git saw a clean merge that then failed at `migrate`), and each named a class `UmamusumeProfile` / `StoreCharacterProfiles` / `GametoraCharacterProfileParser` — the exact set that made the branches unmergeable and forced a port-not-merge. `git log --oneline --all -- <path>` is the tool because it lists every ref holding a change to that path, which a `HEAD..origin/master` freshness check cannot see.

---

## Phase Status (Re-baselined 2026-09-27)

| Phase | Status | Key Shas |
|---|---|---|
| **0 — Scope Contract** | Stable | `CONSTRAINTS.md` C-1..C-8 |
| **1 — Evidence Research** | Stable | `RAW-FINDINGS.md`, `SCREENSHOT-MANIFEST.md` |
| **2 — Design Contract** | Locked & Amended | `DESIGN.md` (root + research), `PRODUCT.md` |
| **2.5 — Prototype** | Converged | `prototypes/screen-a-scenario-v10.html` |
| **3 — Spec Intake** | Completed | `FRONTEND-SPEC-DIVERGENCE.md`, `FRONTEND-BRIEF-AUDIT.md` |
| **4 — Implementation** | **Slice 1 Complete** | `1e859e8` (T2+T3), `e9a944a` (T4), `c70967f` (T5), `b0bb0d5` (T7) |
| **4 — Implementation** | **Slice 2 Complete** | S1 `9134206`, S2 `83084ab`, S3 `bb6eec6`, S4 `d53a4b1`, S5 `70f9218` + `9451659`, S6 (this commit) |
| **4 — Implementation** | **Slice 3 Complete** | T1 `ab915f8`, T2 `726f106` + `78697e9`, T3 `2816309`, T4 `725a5ff`, T5 `5c65597`, T6 `5548b9e`, T7 `ee97869` + `cc3f963` + `21f9906` + `ee6786c`, T8 `dc13d8d` + `35fb0c7`, T9 `33949f5` |
| **4 — Implementation** | **Slice 4 Complete (repo-integrity)** | T1 `35fb0c7`, T2 `dc13d8d`, T3 coherence outputs (20/20 migrations, ScenarioSlot+Preference defs, 23 tables), T4 push shas `9b774f9`/`33949f5`, T5 docs commit, T6 gates |
| **4 — Implementation** | **Slice 5 Complete (frontend only)** | T0 `cd0be38`, T1 `d50a0ec` + `2685a37` + `edeb8cd` + `03a5d05`, T2 `71bbb4b`, T3 `6a53c15`, T4/T5 this commit |
| **4 — Implementation** | **Slice 8 Complete** | Panels and scenario widgets; KI-17, KI-18 filed |
| **4 — Implementation** | **Slice 9 Complete** | `f7a59e8` (KI-18 closed), mood pill tokens |
| **4 — Implementation** | **Slice 10 Complete** | Maintenance: R51 marker, R52 KI-18 bookkeeping, ADR-0009 draft, KI-19/KI-20 filed |
| **4 — Implementation** | **Slice 11 Complete** | `c0a743f` (T1), `f0ae288` (T2), `70248b3` (T3), `5820e77` (T4), `65f8b92` (T5); KI-20 closed. Three claims corrected by Slice 12: the "pre-existing" failure label was a T4 regression, "297 rows / `ura_finale_slots.json`" is 296 rows across three real files, and R59's record commit was never made |
| **4 — Implementation** | **Slice 12 HALTED at T3** (corrected; it was recorded Complete in error) | `c86ed9f` (T0), `2d0c1dc` (T1), `b295d16` (T2), `37ccc08` (T3 findings + halt). T4 never ran: KI-21 and KI-22 filed, `origin/master` held at `4992282` all slice. KI-22 later found to be filed on a wrong cause |
| **4 — Implementation** | **Slice 13 Complete** | `6c1969f` (T1+T2, KI-21 closed), `cb9b61f` + `5ed1ebd` (T3 pins), this commit (T5 docs). Suite 562 passed, all gates green, deferred push finally made |
| **4 — Implementation** | **Slice 14 Complete** | `329cec1` (T1 tier join, G-16c green), `24e491c` (D-153/§1.2.6/G-16c dated), `3ae437d` (T2 quiet edge). Suite 569 passed. 118 tiers restored per race, none lost; parser left a documented dissent because R72's idle condition failed |
| **5 — Verification** | Routine | Browser metrics: light 4.74 / dark 5.48 / badges 9.00+; energy bands 6.40 / 8.34 / 10.89 light and 12.60 / 10.57 / 6.88 dark, and the preview pairs, in `slice-5-2026-09-28.md` |
| **6 — Iteration** | Unfrozen by Slice 2, **not started** | Owner instruction: the slice's commit unfreezes it; no Phase 6 anatomy in this session |

---

## Slice 1 Summary (2026-09-27)

| Task | Commit | Verification |
|---|---|---|
| T2a: `+ 0 breakthrough` → `breakthrough not tracked` | `1e859e8` | `stat-band.blade.php:153` |
| T2b: grade ladder 150→50, disclosure fixed | `1e859e8` | `config/scenarios.php:60-63`, `stat-band.blade.php:159-162` |
| T3: US-10 P2→P1 with ADR-0003 criteria | `1e859e8` | `PRD.md:35` |
| T4: ADR-0003 Amendment R1 | `e9a944a` | `docs/adr/0003...md` |
| T5: DesignTokensTest wired | `c70967f` | `tests/Feature/DesignTokensTest.php` |
| T6: Gates run | re-run 2026-09-28 | **Green:** pest 217 passed / 2 skipped; `pint --test` PASS 141 files; phpstan `[OK] No errors`; `lore` + `lore-code` clean (4 allowed-sense hits, all adjudicated); `vite build` ok; migrations + seeders apply to a fresh scratch DB. **Not green:** `DesignTokensTest` passes 9 but **skips 2** — the browser half of D-288/G-18 needs a Playwright driver, absent in this environment. The contrast numbers on the Phase 5 row are a one-time manual measurement, not a reproducible gate. |
| T7: Artifacts committed | `b0bb0d5` + uncommitted follow-up | pagination override and stray `Continue` removal landed; `.gitignore` line covered `/research-scratch/` but **not `.scratch-uma/`**, so T7's third item had not actually landed. Fixed 2026-09-28: `.gitignore:88` `/.scratch-uma/`, confirmed by `git check-ignore -v`. |
| T8: Doc drift closed | (this commit) | `DESIGN.md:3`, `layout.blade.php:3-10`, root `DESIGN.md:165-180`, `CONSTRAINTS.md G-21`, `KNOWN-ISSUES.md KI-6` |
| T9: Phase 6 freeze | — | Enforced by plan |

---

## Open Blockers for Slice 2 — closed or reclassified 2026-09-28

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

## Slice 2 Summary (2026-09-28)

| Step | Commit | Evidence |
|---|---|---|
| S1: slot link + rewire refused | `9134206` | 5 tests in `RaceEntrySlotLinkTest`; failed first with "Failed asserting that null is identical to 1" |
| S2: panels fed from slots and the race log | `83084ab` | 7 tests in `RaceSlotPanelComposerTest`; live page shows `Apr Early: Fan gate` + `15,000 fans`, `Apr Late: Mandatory goal`, `May Early: Run` |
| S3: red pennant, warm outline, height, named state | `bb6eec6` | `RaceCalendarTest` 17 passed; browser-measured 13.24/5.02/5.74 light, 15.15/6.15/4.49 dark, +14px over neighbours |
| S4: KI-2 closed | `d53a4b1` | 3 tests drive `CACHE_STORE=database`; live server `/umamusume -> 200` |
| S5: gates + R9 manual contrast pass | `70f9218`, `9451659` | `docs/design-research/verification/slice-2-2026-09-28.md` — full sequence with outputs, the 1.62:1 bar defect found and fixed, 2 skips reported as skips |
| S6: docs | (this commit) | ADR-0003 R2, KI-2 resolved with its wrong cause corrected, KI-8 to KI-11 opened, PLAN re-baselined here |

**End state:** `php artisan test --compact` → 2 skipped, 235 passed, 751 assertions.
`pint --dirty` → passed. `phpstan analyse --no-progress` → `[OK] No errors`. `lore-code` →
4 hits, all pre-existing (Laravel's SQS example URL; the three documented `Good-Luck Charm`
lines). `vite build` → 69.05 kB CSS, all 52 colour tokens present in the sheet, 0 pruned.

**Where the commits are.** `master` is at `33949f5` (equals tip). `docs/audit-remediation` fast-forwarded to match. `feat/scenario-races-is-manual` merged at `dc13d8d`. `origin/docs/audit-remediation` at `9b774f9`, `origin/master` at `33949f5`. The branch reconciliation is complete.

---

## Slice 3 Summary (2026-09-28)

Stabilisation slice: it cleared the blockers Slice 2 filed, then tried to reconcile the branch.

| Task | Commit | Evidence |
|---|---|---|
| T1: em-dash disclosure sweep (R12) | `ab915f8` | `RenderedCopyHygieneTest` sweeps every `.blade.php` under `resources/views`, strips three comment forms, fails on any en or em dash; a floor test catches the `getExtension() === 'blade'` mistake that would have scanned nothing |
| T2: badge map keys the base letter (R13/R19) | `726f106` + `78697e9` | `StatBandTest` walks all 17 labels; `/design-preview` 200; 18 badge pairs measured in both themes, min 9.00 |
| T3: meter gets three states (R18) | `2816309` | `gradeUnpricedCount()` drives "not yet totalled" with the count and the reason; "not yet recorded" only when nothing is logged |
| T4: selection boundary split from fill (R15) | `725a5ff` | `--color-pick-line` 6.24 light / 8.46 dark on `raised`, 5.89 on light `panel`; four consumers moved; `border-pick` guard with `(?![-\w])` |
| T5: seeder deleted, real skill names seeded (R14) | `5c65597` | `ScenarioSlotSeeder` removed; ten verbatim D-210 `[Global]` names with `sp_cost => null`; `--color-green-tint` retirement **refused** on G-60 grounds and left open |
| T6: welcome page offline (KI-3) | `5548b9e` | both `<link>` lines deleted; live response 39,987 bytes with 0 `bunny` matches and every asset same-origin |
| T7: gate portability (KI-4) | `ee97869`, `cc3f963`, `21f9906`, `ee6786c` | `composer lore` / `composer lore-code` via `tools/lore.php` (Process array, no shell); parity with the Makefile measured at 131/131 and 4/4; `LoreGateParityTest` proven non-vacuous by deleting a pattern |
| T8: branch reconciliation (R11, R20–R21) | `dc13d8d` (merge) + `35fb0c7` (commit) | `feat/scenario-races-is-manual` merged (single commit `46b8d3e`); untracked `is_manual` duplicate deleted; five load-bearing files committed; coherence checks pass (20/20 migrations, ScenarioSlot+Preference defs, 23 tables on fresh scratch DB) |
| T9: gates + docs | `33949f5` | `docs/design-research/verification/slice-3-2026-09-28.md` |

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

## Slice 4 Summary (2026-09-28) — Repo Integrity Only

| Task | Commit | Evidence |
|---|---|---|
| T1: commit five load-bearing files | `35fb0c7` | ScenarioSlot, Preference, factories, 2 migrations; authored by concurrent session, KI-13 |
| T2: delete duplicate migration + merge feat branch | `dc13d8d` | `2026_09_27_132304_...` deleted; `feat/scenario-races-is-manual` (1 commit `46b8d3e`) merged |
| T3: coherence checks | (this commit) | 20/20 migrations tracked; ScenarioSlot/Preference defs present; 23 tables on fresh scratch DB |
| T4: fast-forward master + push both remotes | push shas | `origin/docs/audit-remediation` → `9b774f9`; `origin/master` → `33949f5` |
| T5: docs commit (KI-13 resolved, PLAN/KNOWN-ISSUES updated) | (this commit) | KI-13 RESOLVED with shas + T3 outputs; PLAN topology + exit criteria updated; KI-11 gains Safe-band commitment; KNOWN-ISSUES count refreshed |
| T6: gates | (this commit) | pest, pint --dirty, phpstan, composer lore, composer lore-code (parity), gate.py, npm run build |

## Slice 5 Summary (2026-09-28) — Frontend only, per R24

Mount what was already built, frame the run screen, make the flow keyboard-completable. No
model, no migration, no config, no dependency. Livewire stayed out and produced evidence
instead (§Open Decisions).

| Task | Commit | Evidence |
|---|---|---|
| T0: Livewire cost + baseline | `cd0be38` | `livewire/livewire` absent from `composer.json`; GET baseline median 0.047 s / p95 0.076 s, n=20; preview number appended here |
| T1: band + rail on `runs/show` | `d50a0ec` | 24 tests in `GuidedTurnOnRunViewTest`; two-stage preview writes nothing; failure writes `turn_events`; mood, advisory, escape hatch |
| T1b: comment leak | `2685a37` | `{# … #}` is not a Blade comment; the band had been printing its rationale as page text. Guard added to `RenderedCopyHygieneTest` |
| T2: two-region frame | `71bbb4b` | `RunViewFrameTest` (DOM containment); strip box 992×92 at both ends of the scroll, 9/9 `elementFromPoint` probes inside the pinned region |
| T3: keyboard path | `6a53c15` | `KeyboardPathTest`; skip link, digits, Escape; roving left to the native radio group and verified with pressed keys |
| T1c/T1d: browser-found defects | `edeb8cd`, `03a5d05` | mood delta sign inverted; Hint badge white-on-green at 1.99:1 |
| T4: gates + browser pass | this commit | `docs/design-research/verification/slice-5-2026-09-28.md` |
| T5: docs + housekeeping | this commit | KI-11 both halves closed, KI-14 filed and closed, audit rows 7.2 and 9 updated, root scratch moved |

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

## Slice 6 Summary (2026-09-28) — Closing slice, R26-R32

Adjudicate what Slice 5 left in the air, land the doc amendments, finish the mood and token-hygiene
residuals, push. Frontend and docs only: no schema, no config, no new screen, no dependency (C-8).

| Task | Commit | What landed |
|---|---|---|
| T0: push, then the two `lore-code` hits | `b81df3f`, `7da2d22` | `e88bb7b..586e65f` pushed once, plain; `stable` (adjective) and `account` ("account for") ruled allowed and written into §3.2 with citations, plus the unexplained `config/queue.php` `your-account-id`; no violation, so no KI and no peer file touched |
| T1: six doc amendments | `61f6165` | D-40 as region lifetimes; the radio clause reversed; controller inventory; Livewire **decided** with a reopen criterion; `aria-live` closed as correct-by-design; the two audit corrections recorded |
| T2: mood tokens, pill, legibility | `51d29d4` | Five `--color-mood-*` + `--color-on-mood`; `x-mood-pill` with the D-259 arrow; `MoodTier::arrow()`; timeline column and state-region readout; pairs measured in both themes, floor 5.82:1 |
| T3: the green ink borrow | `e80c6f2` | `--color-on-green` #1F1508, 9.02:1 measured in both themes; the advisory badge and two stale comments corrected; `TokenPairHygieneTest` pins the naming |
| T4: gates + browser pass | this commit | 2 skipped, 374 passed (1,247 assertions); pint passed; PHPStan level 6 clean; `gate.py` PASS; lore-code 6 all adjudicated in §3.2, `composer lore` 137 → 140 with all three new lines being this plan and the record quoting a banned word to rule on it; build 57.37 kB CSS, all seven new utilities in the bundle; token count 53 → 60 |
| T5: record, second push | this commit + push | `docs/design-research/verification/slice-6-2026-09-28.md`; PLAN and KNOWN-ISSUES re-baselined |

**Deviation the reviewer should look at first.** The mood pill does not use §3.4's tint-and-border
pattern; it keeps the client's saturated fill and steps the ink. The pale-fill reading produced five
near-whites that cannot order a scale and an arrow at 2.5-2.9:1. §3.4, §3.7 and §6.17 are amended to
carry the measured numbers, and the reasoning is in `slice-6-2026-09-28.md` §6.

**Still unmeasured:** whether a Trainer distinguishes `↑` from `→` at the shipped 12 px. The ratio
floor is 5.82:1 and the geometry is recorded; the glyph legibility needs a human read, and no
capture was taken.

---

## Slice 7 Summary (2026-09-28) — Schema session, R33-R37

Three schema questions arrived as one set so the owner ruled once. No Unity Cup or Trackblazer
panel UI (Slice 8), no new dependency, peer files untouched.

| Task | Commit | Claim, with the thing that proves it |
|---|---|---|
| T0a §3.4 pair table | `53fbe70` | All six new pairs (five mood + `on-green`) were already rows in `docs/design-research/DESIGN.md:156-161` with their measured ratios; the count in the note under them said seven, and said six |
| T0b Livewire criterion | `8fd127c` | R34's sentence is now quoted verbatim above the seven-item checklist, with this slice's byte-count framing named as not a trigger |
| T0c zinc sweep | no commit; grep recorded | `grep -n "zinc-" resources/views/runs/index.blade.php` exits 1, no match. Repo-wide, `zinc-` survives only in three Blade comments that document the retired skeleton and in `welcome.blade.php:15`'s vendored Tailwind theme variable list |
| T1 period columns | `e103122` | Migration `2026_09_28_122929_add_grade_point_period_columns.php`; `TrainingRun::assertGradePeriod()` guards both columns; `tests/Feature/GradePointPeriodTest.php` ran red on the missing columns first |
| T1c meter, period-aware | `e103122` | `gradeEarnedFor()`, `gradeUnpricedFor()`, `gradePeriods()`; `gradeEarned()` is the reported period or null; the no-period state renders "no period reported" and the ladder rows carry their own sums |
| T2 typed payloads | `17dbc54` | `SpiritBurstState` (six cases, no seventh), `NpcFriendshipPayload`, `SpiritBurstPayload`; `tests/Feature/TurnEventTypePayloadsTest.php` asserts the raw stored JSON and the three rejections |
| T2 reword | `8955394` | `composer lore-code` back to 6 after one comment idiom was reworded |
| T3 docs | this commit | ADR-0005 status DECLINED for Phase 1 with the reopen trigger named; KI-10 split and its schema half closed; KI-15 filed for the track-selection conflict; PLAN re-baselined |

**Suite at the tip of this slice's code:** `php artisan test --compact` → 2 skipped, 390 passed
(1,287 assertions); PHPStan level 6 `[OK] No errors`; Pint clean on the files this slice touched.

---

## Slice 8 Summary (2026-09-28) — Panels and scenario widgets

Built the four scenario-aware panels (race, team-race, spirit-burst, shop) plus supporting
components (epithet-checklist, race-fatigue-chip). Filed KI-17 (consecutive-race count not
derivable from log) and KI-18 (burst roster prints tool identifiers as UI copy). No token changes,
no migrations.

---

## Slice 9 Summary (2026-09-28) — Spirit burst labels, mood pill tokens

Closed KI-18 (`f7a59e8`): `SpiritBurstState::label()` mapping six cases to Trainer-readable words;
roster prints labels, tests assert labels and fail on leaked backing values. Mood pill tokens
landed with legibility minimum measured in browser. Subject-prefix erratum: `68fa190` carries
`docs(slice-7)` but is a Slice 9 commit (R50).

---

## Slice 10 Summary (2026-09-29) — Maintenance and drift

Closed KI-18 bookkeeping (R52). Lore-ignore-line marker landed (R51). Impeccable re-audit ran
on same version (KI-19: updater returns 404). Purchase cost prefill + 422 field error per-field.
ADR-0009 drafted for scenario-slot seeding. Filed KI-19 and KI-20.

---

## Slice 11 Summary (2026-09-29) — Calendar slots, free-race writer, KI-20 closure

Rulings: R54 (source_key migration), R55 (URA Finale seeder), R56 (free-race manual writer),
R57 (delete welcome page), R58 (measure and close KI-20), R59 (push once + record commit),
R60 (rulings are repo artifacts), R61 (fifth kind: free_race), R62 (lore-code path exclusion).

| Task | Commit | Claim, with the thing that proves it |
|---|---|---|
| T1 source_key migration | `c0a743f` | `scenario_slots.source_key` nullable string; unique composite `(scenario_key, month, half, source_key)`; migration tested in `ScenarioSlotMigrationTest` |
| T2 URA Finale seeder | `f0ae288` | `ScenarioSlotSeeder` reads the three committed client-export files `database/seeders/data/{ura-races,race_instances,races}.json`; **296 rows** seeded on a clean scratch DB; idempotent via upsert on `(scenario_key, month, half, kind, source_key)` |
| T3 multi-slot calendar | `70248b3` | `TrainingRun::calendarCells()` queries both `goal_race` and `free_race`; cells carry `['slots' => [...]]` arrays; priority state derivation; `race-calendar.blade.php` renders multiple slots per cell |
| T4 free-race writer | `5820e77` | Two-path form (calendar/manual) in `race-panel.blade.php`; `StoreRaceEntryRequest` validates both paths; controller creates `ScenarioSlot(kind=free_race)` + `RaceEntry` atomically; `FreeRaceWriterTest` 8 tests; lore-code baseline stays 7 after R62 path exclusion |
| T5 closures | `65f8b92` | Welcome page deleted, `/` redirects to `runs.index` (R57); KI-20 measured (light 10.04:1 PASS, dark 4.33:1 FAIL → stepped #FF6B7A to #FF7E8C = 4.77:1 PASS); KNOWN-ISSUES updated to 15 closed / 4 open |

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

## Slice 12 Summary (2026-09-29) — CORRECTED TO: halted at T3

Slice 12 did not complete. **It halted at T3** with two blockers filed and T4 (gates plus push)
never run. `origin/master` stayed at `4992282` for the whole slice.

| Task | Commit | Claim |
|---|---|---|
| T0 radiogroup regression | `c86ed9f` | Slice 11's "pre-existing" label was wrong: bisect shows `0d2dbdc` PASS, `70248b3` PASS, `5820e77` FAIL. T4's `role="radiogroup"` on the mode switch broke D-40. Fixed with banner buttons |
| T1 tier audit | `2d0c1dc`, superseded in part by `329cec1` (R72) | R65 concluded that only codes 100 and 400 have a label source, resting on REFERENCE §1.2.6's "no cited label map" for 200/300/700, and nulled those three. **That sentence described the corpus as then written, not the absence of a source.** Tested per race on 2026-09-29 in a rendering browser against two publishers: G1 agrees 34/34, G2 42/42, G3 76/76, so all three tiers are now sourced **per race** and the seeder holds no code-to-label constant at all. Open and Pre-OP are not settled: Game8's list is graded-only, so its silence is not agreement, and uma.guide alone calls Open 「OP/L (Open/Listed)」. Three Open rows are pinned per row by their own client glyph; 115 keep a disclosed code-level pin rather than being nulled, and that trade is flagged for the owner, not taken quietly. Pre-OP stays null. Re-seed twice: 296 rows, G1 34 / G2 42 / G3 76 / OP 118 / null 26 — 118 restored, none lost. Full evidence and page dates: `database/seeders/data/race-tier-labels-2026-09-29.json`, D-153 |
| T2 register + record | `b295d16` | Slice-11 addendum, KI-19 closed on the second update attempt, KI-11 re-closed, ADR-0009 to RULED IN PART, R54-R66 ledger added |
| T3 browser pass | `37ccc08` | Found **KI-21** and **KI-22**, and halted. Suite green, pairs measured, form unusable |
| T4 gates + push | **NOT RUN** | A green gate over a form no Trainer can reach is not a gate. Blocked pending the owner's dependency call |

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

## Slice 13 Summary (2026-09-29) — Close KI-21, fix the ordinal, reconcile KI-22, push once

Brief: "No new dependency, no new token, no schema change, no panel beyond the race panel." All four
held: `package.json` untouched, no token declared or recoloured, no migration, one component changed.
Rulings R67-R71.

| Task | Commit | Claim, with the thing that proves it |
|---|---|---|
| T0 opening snapshot | (record §1) | R68's three named files checked individually, all CLEAN, so T3 ran. `HEAD` was `7895e74` while `origin/master` sat at `4992282` |
| T1 server-driven disclosure | `6c1969f` | Two GET forms submit `entry_mode`; `showData()` reads the query the way design-preview reads `?step=`; `old()` wins so a failed write returns to its own branch with placement/status/circles/period preserved. `RaceEntryDisclosureTest` 8 tests, **all red against HEAD**, resolving fields through `DOMDocument` and refusing any inside a `template` (R71) |
| T2 ordinal placements | `6c1969f` | `RaceEntry::placementOrdinal()` at `:437`-area; twelve values tabled including the 11/12/13 teens plus null → "no placement". `team-race-panel` prints a cardinal with the word that makes it one and is left alone |
| T3 KI-22 reconciliation | `cb9b61f`, `5ed1ebd` | No production change: the rule was intact and Slice 12's cause was wrong. Added the rendered pins the existing test could not provide — `RaceCalendarTest:276` hand-writes its cell array and passes on label text without calling `calendarCell()` |
| T4 browser pass | (record §5) | R17 fixture on `.scratch-uma/s13-browser.sqlite`, port 8233, shared DB never opened. Free race created **through the rendered form** by clicking the disclosure and pressing Record race. All 13 pairs clear; zero `template` elements; radiogroups still 1 |
| T5 gates + deferred push | (this commit / post-push record) | Pest 562 passed, Pint passed, PHPStan no errors, lore-docs 98/51, lore-code 7, gate.py PASS, build clean. One plain push carrying 16 commits, 9 named as not this slice's |

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

## Owner Rulings Ledger (R54-R77)

R60 makes a ruling a repo artifact rather than a transcript line, so the ledger records each ruling
as the brief gave it. Where the brief supplied a full sentence it is quoted; where it supplied a
parenthetical gloss the gloss is kept and marked as such, because inventing verbatim wording for a
ruling is worse than recording it short.

| ID | Slice | Ruling, as given | Provenance |
|---|---|---|---|
| R54 | 11 | Add `source_key` to `scenario_slots` so a half-month can hold more than one race; the unique composite includes it | gloss |
| R55 | 11 | Seed URA Finale `scenario_slots` from the committed client export | gloss |
| R56 | 11 | A Trainer can enter a race that is not on the calendar; the manual path creates one `scenario_slots` row per race, and that row is Trainer-entered | verbatim in brief |
| R57 | 11 | Delete the welcome page | gloss |
| R58 | 11 | Measure KI-20; a pass closes it with a §3.4 row, a fail steps the ink per the on-mood/on-green precedent | verbatim in brief |
| R59 | 11 | Push once per slice, with the post-push `ls-remote` verification in the record commit | gloss |
| R60 | 11 | Rulings are repo artifacts, not transcript lines | gloss |
| R61 | 11 | `free_race` is the fifth `kind`; its cells take open-cell geometry and a Trainer-entered marker, never a Goal pennant | verbatim in brief |
| R62 | 11 | Exclude `database/seeders/data/**` from the lore-code sweep: committed client export is source data under C-4 class 3 | gloss |
| R63 | 12 | `RunViewFrameTest` is a T4 regression, not pre-existing; read the test's intent before choosing the fix, and correct the slice-11 record forward rather than editing it in place | verbatim in brief |
| R64 | 12 | The slice-11 push verification is written from the outside, and the record says whether those lines were written before or after the push | verbatim in brief |
| R65 | 12 | Any seeded row whose tier label has no per-row source gets `tier = null` and keeps `source_key`; the re-seed proves idempotency on a scratch DB | verbatim in brief |
| R66 | 12 | Retry the Impeccable update once; on a second 404 record both attempt dates in KI-19 and run the detect pass at the current version over the Slice 11 surfaces only | verbatim in brief |
| R67 | 13 | The two-path race form becomes server-driven disclosure: the mode switch submits `entry_mode` and the server re-renders with that branch's fields, exactly as guided-step does. No new dependency | verbatim in brief |
| R68 | 13 | Reconcile KI-22 only if T0 finds `race-calendar.blade.php`, `runs/show.blade.php` and `TrainingRun.php` clean; if any is dirty, skip T3 entirely and say so in the record with the dirty list | verbatim in brief |
| R69 | 13 | Placement renders 1st, 2nd, 3rd, 4th, 11th, 12th, 13th, 21st, 22nd, 23rd correctly through a small helper, with a test table for those values | verbatim in brief |
| R70 | 13 | The deferred push goes as one plain push carrying the interleaved peer commits, and the record names every sha that is not this slice's | verbatim in brief |
| R71 | 13 | The disclosure test asserts the branch's fields by input name through the rendered DOM, must fail against HEAD, and the writer is additionally reached through the rendered form rather than a direct post | verbatim in brief |
| R72 | 14 | Settle the tier question on evidence read in a rendering browser with location asserted before each read; commit a dated extraction and join per race; restore a label only where both publishers agree and a page date postdates 2026-07-01; reconcile the parser's map only if its file is clean and the peer session is confirmed idle | verbatim in brief |
| R73 | 14 | Before a push, read every commit in the range it will carry that touches `database/`, `config/`, `app/Services/DataPipeline/` or the research corpus, starting with anything the peer lands during the slice | verbatim in brief |
| R74 | 14 | Record the quiet-edge exception beside the §3.4 pair table with its measurements, its two precedents and its carrying cues; docs only, no token and no component change | verbatim in brief |
| R75 | 15 | Nullify the 115 OP rows and the 26 Pre-OP rows in the seeder; the 3 client-pinned OP rows keep their label; remove the `per_row_sourced` disclosure flag, because a null tier is the honest state | verbatim in brief |
| R76 | 15 | Add a test that deletes the extraction file and asserts the seeder completes with null tiers and a logged warning, rather than throwing | verbatim in brief |
| R77 | 15 | **Reserved and still unworded.** "Rulings R75 to R77 govern" is the only appearance of the number in that brief; it carries no task and no rule. Slice 16 then asked twice more for the row to be filled — once through R80, once through a directive headed "R77 (text supplied)" — and **neither message contained a sentence for it**. The row therefore records the absence rather than receiving an invented ruling: a line under `R77` would later be indistinguishable from a decision the owner actually made, which is the outcome R60 exists to prevent. See `slice-15-2026-09-29.md` §10.4 for the three candidate readings that were checked and rejected | verified absent from all three briefs |

---

## Slice 14 Summary (2026-09-29) — Tier labels settled per race, quiet edge recorded

Brief: verification and recording. No new dependency, no token change, no parser edit unless R72's
coordination condition holds. Rulings R72-R74.

| Task | Commit | Claim, with the thing that proves it |
|---|---|---|
| T0 opening push | `debc4d0` on origin | One plain push carrying the four Slice 13 docs-only commits; `ls-remote` = `debc4d0e6bda…` equals local HEAD. R73 read list: all four this session's |
| T1 tier join | `329cec1`, `24e491c` | Both publishers read in a rendering browser with location asserted. **G1 34/34, G2 42/42, G3 76/76 agree per race; Open and Pre-OP do not** (Game8 is graded-only, uma.guide alone says "OP/L (Open/Listed)"). `GRADE_MAP` deleted; seeder joins on `database/seeders/data/race-tier-labels-2026-09-29.json` and stores the evidence date, not the seed moment. G-16c: no matches in seeder or config, exit 1, pinned by `TierLabelJoinTest`. Re-seed twice: 296 rows, G1 34 / G2 42 / G3 76 / OP 118 / null 26 — 118 restored, none lost, no row without a `source_url` |
| T2 quiet edge | `3ae437d` | DESIGN.md §3.4 records R74 as three conditions, both precedents, and the numbers (1.22 / 1.37 unselected against 5.89 / 10.57 selected and a 4.83 pair difference) |
| T3 rendered tiers | (record §4) | The calendar renders **no** tier text (grep 0, 0 tier nodes in `role="img"` cells, both themes), so no calendar pair was invented. Race panel: select 6.95 / 12.71, `G2` and `G3` 5.78 / 6.64, ordinal 6.64 dark, calendar marker 8.30 dark. Picker distribution read off the DOM matches the re-seed exactly |
| T4 gates + push | (this commit) | Pest 569 passed / 2 skipped / 0 failed; Pint, PHPStan, gate.py, build clean; lore-docs 98/51; lore-code 7. Server stopped before the suite, so Slice 13's lock failure was not repeated |

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

## Slice 15 Summary (2026-09-29) — the Schema Session: three long-waiting questions landed

Brief: close KI-10, KI-17 and Legacy Select, and apply R75's strict nullification. No new panels, no new
tokens, no new dependencies. Rulings R75-R77 — of which **R77 has no text in the brief** (ledger note).

| Task | Commit | Claim, with the thing that proves it |
|---|---|---|
| T0 opening push | `72e5157` on origin | One plain push carrying the one-ahead docs commit; `ls-remote` = `72e5157f7ab4…` equals local HEAD. R73 read list: one commit, docs-only, this session's |
| T1 R75 nullification | `40df14c` | Test rewritten **first**, `3 failed / 5 passed`. Re-seeded twice on a scratch DB: **G1 34 / G2 42 / G3 76 / OP 3 / null 141**, 296 rows both passes, idempotent. G-16c `exit 1`, no matches in `database/seeders/` or `config/`. The 3 surviving OP rows read back out of the database: Fukushima TV Open, Sapporo Nikkei Open, Kokura Nikkei Open. `per_row_sourced` removed from every row, not set to `false` |
| T2 KI-17 | `d06199c` | `race_entries.turn_entry_id`, nullable FK, `nullOnDelete`; dropdown of the run's own turns in the race panel; 9 tests RED-then-green, covering the link, the null state, and a turn from **another run rejected with no row written**. `ESSENTIALS` gains the `race_entries` line it never had |
| T3 KI-10 | `3711894` | `grade_points_earned`, priced **for a 1st place only** from `grade_point_by_grade` (G1 100 / G2 80 / G3 60 / OP 40 / Pre-OP 20); every other placement and every untiered race stores null, so **2nd in a G1 is null beside 4th**, which is what keeps KI-10's ratio half visibly open rather than quietly borrowed from the Shop Coins table. Tier read `race_catalog_slots` then `scenario_slots`, pinned by two slots that disagree. `GradePointPeriodTest`'s 9 tests pass untouched — the semantics-preservation evidence. 22 tests |
| T4 Legacy Select | `26aa9fe`, `49bac80` | One `legacy_selection` json payload plus **ADR-0010**, because PRD §6.3's "nothing more" had to be narrowed for this to exist at all. The brief's `legacy_parent_a_id` / `_b_id` were **not** added — the table already has the two foreign keys, and a test asserts the duplicate pair is absent. 13 tests, including a run created with two legacy parents and a sparks payload |
| T5 R76 | `952f41a` | A missing extraction now logs exactly one warning naming the file, and the seeder completes with every tier null. `1 failed / 3 passed` at RED (warning called 0 times). A third test asserts **no** warning when the file is present, without which the first two could be satisfied by warning unconditionally |
| T6 gates + browser + record | (this commit) | Pest **618 passed / 2 skipped / 0 failed**; Pint, PHPStan clean; `lore-docs 98 hits / 55 exempt`, `lore-code 7`; `gate.py` PASS; build clean. Browser pass on a Trackblazer run built through the real forms: row reads `… G1 1st turn 1 period 2`, meter reads `100 of 60 toward End of Junior Year`, zero console errors, every new pair ≥ 5.78 light and ≥ 6.64 dark |

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

## Controller Inventory

Recorded because Slice 5's path scope named `app/Http/Controllers/RunController.php`, a file that
does not exist and never did, and a scope line quoting an absent path is a scope line nobody can
honour. This is the whole set on `7da2d22`, from `git ls-files app/Http/Controllers`:

| File | Surface |
|---|---|
| `Controller.php` | abstract base, no routes |
| `TrainingRunController.php` | the run surface: index, show, store, the two-stage guided turn, export |
| `CatalogController.php` | umamusume catalog index and detail |
| `ReviewController.php` | the match-candidate review queue |
| `Api/V1/TrainingRunController.php` | JSON runs |
| `Api/V1/UmamusumeController.php` | JSON catalog |

The run surface is `TrainingRunController` (web and API v1). A future slice that means "shape the
run page" should name that file. Renaming it to `RunController` would touch `routes/web.php`,
which is out of every frontend slice's scope.

## Corrections Owed to FRONTEND-BRIEF-AUDIT, Not Applied

`docs/design-research/FRONTEND-BRIEF-AUDIT.md` is an untracked file authored by the concurrent
session, so it is not this slice's to edit. Two of its §3 rows now describe a tree they did not
see, and the owner or that session should land these before the audit is cited again:

| Row | It says | It should say |
|---|---|---|
| §3 "5.1 two-region frame" | "**NOT BUILT** — `layout.blade.php:18` is one `max-w-5xl` column" | Built in Slice 5 at `71bbb4b`: a pinned state region over a scrolling work region on `runs/show`, persistence measured in `slice-5-2026-09-28.md` §3. `layout.blade.php` is still one column, which is correct: the frame belongs to the run screen, not the shell. |
| §3 "9 accessibility" | "Still absent: `aria-live`." | Absent by design, not outstanding (R31): the preview arrives through a navigation, so there is no in-place change to announce. See KI-14's closure in `KNOWN-ISSUES.md`. |

---

## Evidence Traceability

| Gate | Command | Slice 1 Output |
|---|---|---|
| migrate | `php artisan migrate:fresh --seed` | 22 migrations, 2 seeders ✅ |
| pest | `vendor/bin/pest --compact` | 217 passed, 2 skipped ✅ |
| pint | `vendor/bin/pint --test` | passed ✅ |
| phpstan | `vendor/bin/phpstan analyse --no-progress` | Level 6, 0 errors ✅ |
| lore | `make lore` (manual) | PASS ✅ |
| lore-code | `make lore-code` (manual) | PASS (1 documented hit) ✅ |
| vite | `npx vite build` | 70.18 kB CSS, 51.52 kB JS ✅ |
| DesignTokensTest | `vendor/bin/pest --filter=DesignTokensTest` | 9 passed, 2 skipped (Playwright) ✅ |

---

## Open Decisions (unowned by any slice)

Three calls stay with the owner and are untouched by Slice 5: theme default (light vs dark),
rounded-font vs system stack (C-8 plus `DESIGN.md` §11), and mid-run "Change scenario"
semantics. The fourth is no longer open.

### Should Livewire enter the stack? — DECIDED no, 2026-09-28 (R29)

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

## Doc Drift Closed (T8)

| File | Line | Fix |
|---|---|---|
| `docs/design-research/DESIGN.md` | 3 | "no production code" → timestamped + component list |
| `resources/views/components/layout.blade.php` | 3-10 | "until table lands" → composer reads table |
| `DESIGN.md` (root) | 165-180 | Component inventory updated with token-migrated components |
| `docs/design-research/CONSTRAINTS.md` | 900 (G-21) | Ruling B5 exemption recorded |
| `KNOWN-ISSUES.md` | 158 | KI-6 closed as decision |
| `PLAN.md` | header | Slice exit criteria added |
## Open frontend work (2026-10-02, documentation-sync pass)

Copy and control-size defects that are live on a Trainer-facing surface and owned by nobody else's slice. This
section is the registration `KNOWN-ISSUES.md`'s never-cited entries needed; it is a living list, so entries
leave it when their fix lands.

| State | ID | Work |
|---|---|---|
| Open | KI-46 | The import's per-stat validation error prints the internal array path to the Trainer: "A speed value is outside what this scenario allows: The turns.0.speed field must be between 0 and 1400." `turns.0.speed` is the Form Request's nested key and is the only part of the sentence naming which row broke. Needs a message rewrite in `ImportHistoricalRunRequest` that carries the row explicitly, not a display patch; filing (not fixing) was the ruling because Laravel's `attributes()` maps a wildcard key to one string, so renaming it loses the row. |
| Open | KI-29/KI-37 follow-on | The 44px control contract is now stated at `DESIGN-CORPUS.md:904`; the catalog index was resized at `9cba3ee`. Remaining surfaces citing the old bare `DESIGN.md §6.14` form should be re-measured, not assumed covered. |
