# Trainer Desk — Frontend Development Plan

**Status:** Phase 4 (Implementation) active through Slice 3. Slice 3's T8 (branch reconciliation) is **resolved** — see KI-13. Phase 6 remains unfrozen and unstarted. Slice 4 (repo-integrity only) completed.
**Last Updated:** 2026-09-28 (Slice 3 T1–T7 and T9 landed; Slice 4 T1–T6 completed; `master` at `33949f5` equals tip; `feat/scenario-races-is-manual` merged at `dc13d8d`; `origin/docs/audit-remediation` at `9b774f9`, `origin/master` at `33949f5`)

---

## Slice Exit Criteria

**Every slice must satisfy all of the following before being marked complete:**

1. **Gates green with pasted output** — `pest`, `pint --test`, `phpstan --no-progress`, `make lore` (manual), `make lore-code` (manual), `npx vite build`, `DesignTokensTest` all pass; outputs recorded in commit message or linked CI run. **Migration gate:** `migrate:fresh --seed` is a destructive drop and is not run here; the equivalent evidence is `php artisan migrate` plus `db:seed` applied to a **fresh empty scratch DB** (`DB_DATABASE=/tmp/…`), which proves the same thing with no blast radius on the shared dev file.
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
| **5 — Verification** | Routine | Browser metrics: light 4.74 / dark 5.48 / badges 9.00+ |
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

## Doc Drift Closed (T8)

| File | Line | Fix |
|---|---|---|
| `docs/design-research/DESIGN.md` | 3 | "no production code" → timestamped + component list |
| `resources/views/components/layout.blade.php` | 3-10 | "until table lands" → composer reads table |
| `DESIGN.md` (root) | 165-180 | Component inventory updated with token-migrated components |
| `docs/design-research/CONSTRAINTS.md` | 900 (G-21) | Ruling B5 exemption recorded |
| `KNOWN-ISSUES.md` | 158 | KI-6 closed as decision |
| `PLAN.md` | header | Slice exit criteria added |