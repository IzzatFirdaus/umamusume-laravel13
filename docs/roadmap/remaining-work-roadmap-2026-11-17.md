# Trainer Desk - Remaining-work roadmap (2026-11-17, post-S18)

**Status:** active. **Pair:** `docs/proposals/remaining-implementation-2026-11-17.md` (the owner-ruling package)
and four execution plans: `docs/plan/plan-tree-truth-2026-11-17.md` (phases 0-1),
`docs/plan/plan-truth-gates-2026-11-17.md` (phase 2), `docs/plan/plan-defects-2026-11-17.md` (phase 3),
`docs/plan/plan-capability-release-2026-11-17.md` (phases 4-5). This file owns **what remains and the order**;
each plan owns **how**, with commands. Where a plan and this roadmap disagree, this roadmap governs and the
plan is corrected.

**Measured at** `HEAD be8893a2` (local `master`), `origin/master 0bb3efd0`, 2026-11-17 20:00-21:00 MPST.
Every figure below is a command's output in that window; label `[M]` measured, `[S]` static read, `[I]` inferred.
The tree moved three times during the pass (a peer wrote `KNOWN-ISSUES.md` twice and deleted a probe spec);
re-derive before executing, and treat each `[M]` as a reading at a stated moment.

---

## 0. Measured baseline

| Item | Reading | Re-derive with |
| --- | --- | --- |
| `HEAD` | **`2546689e`** (`be8893a2` when this baseline was first taken; a peer has since landed 21 commits) | `git rev-parse --short HEAD` |
| `origin/master` | `0bb3efd0` (PR #27, the S1 snapshot pipeline, merged) | `git rev-parse --short origin/master` |
| Divergence | **8 behind / 36 ahead** (was 8/15 at `be8893a2`) | `git rev-list --left-right --count origin/master...HEAD` |
| Working tree | **22 porcelain rows** at `HEAD 2546689e`: 1 `M `, 16 ` M`, **1 ` D`**, 4 `??`. The 109 deletions and most of the 57 modifications are **committed**; see slice 0.5 | `git status --porcelain -uall \| awk '{print substr($0,1,2)}' \| sort \| uniq -c` |
| Local commits ahead | 36 at `2546689e` (15 at `be8893a2`), of which **4 carried product files** at the original measurement (`be8893a2`, `50e31d14`, `1a1aeca3`, `3158a9b8`); 11 are docs-only | `git show --name-only --format= <sha>` per commit |
| Origin-side commits | 8, of which **1 carries product files** (`0bb3efd0`, the 7 S1 paths); 7 docs-only | same |
| Tags | `0.1.0 2.0.1 2.0.2 2.1.0 2.1.1`; **no `3.0.*`** | `git tag -l` |
| Routes | 108 total, **36 write**, 6 under `/api/v1` | `php artisan route:list --json` |
| Pages / components / types | 62 tracked page `.vue` / 117 tracked component `.vue` (119 on disk) | `git ls-files 'resources/js/pages' \| grep -c '\.vue$'` |
| PHP suites | **262** `tests/Feature` + 13 `tests/Unit` files; 2 `markTestSkipped` (was 257 at `be8893a2`) | `git ls-files 'tests/Feature' \| grep -c '\.php$'` |
| Browser specs | **125 files, 876 cases** by the runner; 798 `test(` declarations; 11 `test.skip` | `npx playwright test --list` |
| Migrations | 80 files, **0 pending** on the dev DB | `php artisan migrate:status --no-ansi` |
| ADRs | 30 (`0001`-`0030`); `0027` still **Proposed** | `ls docs/adr/0*.md \| wc -l`; `docs/adr/0027-*:11` |
| KI register | **110 index rows**; 116 body headings under **110 distinct ids** (six ids carry two blocks); **10 index-OPEN** (KI-104, 106, 114, 116, 119, 127, 162, 163, 164, 166); **6 index/body contradictions** (KI-69, 74, 100, 107, 117, 124) | `python <skill>/scripts/audit_status_register.py KNOWN-ISSUES.md` |
| Gate state | typecheck exit 0; phpstan `[OK] No errors`; pint on the 11 changed PHP paths `passed`; `lint:ui` 516 findings with **207 arbitrary-value against a 209 baseline**; lore docs **582 hits / 75 exempt**, code **365**; doc census **690 dead** against the 708 baseline | see §7 |
| PHP suite cost | **2455 passed / 37,540 assertions / 370.98 s**, inherited from `docs/plan/finish-plan-2026-11-17.md:36` (quiet box, same HEAD) | `php artisan test --compact < /dev/null` |
| Browser suite cost | 876 cases, `workers: 1`, 180 s per case, single-process `php artisan serve`; the release plan's own estimate is ~23 min | `playwright.config.ts:35-60` |
| Dependencies | `composer audit --no-dev` **clean**; the dev tree carries **1 advisory** (`symfony/yaml v8.1.6`, CVE-2026-123792, transitive via `laravel/roster` and `laravel/sail`); `npm audit --omit=dev` **clean** | `composer audit --no-dev`; `npm audit --omit=dev` |

**Tree hazards, stated because they decide the order.**

1. **The worktree is ahead of both refs.** `origin/master` already carries S1; `HEAD` does not; the worktree
   carries S1 *and* the S18 batch *and* a peer refactor. Six of the seven S1 paths in the worktree are
   **byte-identical to `origin/master`** `[M]`, so a naive "apply the worktree diff" would re-apply merged work.
2. **`npm ci` cannot install this tree** `[M]`: `package.json` declares `@inertiajs/vue3 ^3.11.0` plus
   `interestfor` and `@oddbird/popover-polyfill`, while `package-lock.json` (clean, unmodified) still pins
   `2.3.28` and lacks both. `npm ci --dry-run` exits 1 with `EUSAGE`.
3. **Three build inputs are untracked** `[M]`: `resources/js/polyfills.js` (imported by the modified
   `resources/js/spa.ts:7`), `config/inertia.php`, and `resources/js/components/ui/{Sparkline,StatRing}.vue` (imported by
   the modified `resources/js/pages/Career/Timeline.vue:21`). A clean clone cannot build the modified sources.
4. **A workflow calls a script the worktree deleted** `[M]`: `.github/workflows/nativephp-windows.yml:95`
   runs `composer run pre-native-build`, and the worktree removed that script from `composer.json` while
   `tools/check-electron-env.php` still exists.
5. **`win-unpacked/` is 273 MB of untracked build output and is not in `.gitignore`** `[M]`;
   `git check-ignore win-unpacked` exits 1. A stray zero-byte file named `` table.t)' `` sits at the root,
   also untracked.

---

## 1. Completed work - excluded, with the evidence that excludes it

Do not re-open a row below. Each names the artefact that proves it.

| Item | Evidence |
| --- | --- |
| S1 snapshot pipeline (7 files) | Merged as `0bb3efd0` (PR #27); the same 6 paths in the worktree are **identical to `origin/master`** and `git diff --name-only origin/master -- <the 3 S1 controller/request/action paths>` is empty `[M]` |
| S18-01..S18-07 surface fixes | Landed and carried in the worktree; `docs/audits/reconciliation-rounds/scenario-flow-surface-s18-handoff.md` §3 with per-fix evidence; `help.spec.ts` updated to the new sentence (diff quoted in §2 of the sourcing pass) |
| S18-10 owned-skills hand-off | `resources/js/pages/Career/Snapshot/Review.vue` carries a `data-owned-skills-handoff` paragraph `[M]`; `tests/Feature/SnapshotOwnedSkillsTest.php` exists (untracked, worktree-only) |
| R8 E-01/E-02/E-03/E-04, U-02, U-03, U-05b, U-07 | `rice-shower-unity-cup-round8-reconciliation.md` §5-§9: 12 rows / 11 findings, 8 closed (2 erratum + 6 code), 1 terminal, 3 deferred |
| KI-104 nested-disclosure code half | `resources/js/components/career/UnityCupEditor.vue` `<form>` is the component root; the two remaining `<details` hits are comment text `[M]`; `005561c1` is an ancestor of `HEAD` `[M]` |
| KI-106 citation ratchet | `python tools/doc_census.py` -> **690 dead links** against `DOC_CITATION_BASELINE = 708` `[M]`; `DocCitationParityTest` 2 passed `[M]` |
| KI-114 deck-picker specs | The eleven claimed literals are absent from the specs (`grep 'Equip owned to Slot'` -> 0, `'Rank UG'` -> 0); `#deck-picker-results` survives on the run-scoped `resources/js/pages/Support/Builder.vue:494` `[M]` |
| KI-116 reached card effects | `SupportCardEffects::forLevel()` at `app/Services/SupportCardEffects.php:209`; consumed at `app/Services/DeckAnalysis.php:109`; `tests/Feature/DeckAnalysisReachedEffectsTest.php` `[M]` |
| KI-119 current spirit proxy | Column migration `2026_11_10_010500`, Fillable and cast on `TrainingRun`, prop on `resources/js/components/scenario/UnityCupPanel.vue:87`, request rule, panel tests `[M]` |
| KI-127 panel card wrapper | `resources/js/pages/Career/Cockpit/Panels/TeamRaceFacilityPanel.vue:51` is `<div class="card p-3">` `[M]` |
| KI-159 / KI-141 absences | Closed in the register with per-row evidence; `SupportSlot.vue` keeps the `Level` input and `AbsenceValue` disclosures |
| KI-160 cockpit overflow | Fixed (`resources/js/components/career/NextDecision.vue:346` wrapper); `tests/browser/career-race-strip.spec.ts:216` and `tests/browser/career-cockpit.spec.ts:318` are the surviving guards |
| HFW parity repair, WCAG AA token pairs, dark risk token, QWEN truth, FR-E scope, NFR-19 deferral | `VERSION.md:9`; `DesignTokensTest` 17 passed; `DESIGN.md:1361-1377`; `3cbbd8b9`; `9638ee4b` / `20dfeb38` |
| `_screens-indep-training.spec.ts` retirement | Deleted in the worktree, 0 cases at HEAD after deletion; the controls it drove are absent from `resources/js` `[M]` |
| `career-race-strip.spec.ts` `Actions` assertion | Replaced by a dated `RETIRED 2026-11-17` comment block at `:228`; no dead locator remains `[M]` |

---

## 2. Consolidation - every prior roadmap/plan, and where its remaining content now lives

No prior file is edited or deleted by this consolidation. The sources keep their bytes because several are
another session's uncommitted work; supersession is recorded here once.

| Prior document | State at this pass | Disposition |
| --- | --- | --- |
| `docs/roadmap/implementation-roadmap-2026-11-17.md` (S1-S7) | S1 landed; S2 partial; S3 open; S4 pending; S5 pending; S6 open; S7 open | **Absorbed** as phases 1-5 here; its S1 rows are §1 above |
| `docs/plan/implementation-plan-2026-11-17.md` | every S1.1-S1.7 row landed (measured against the tree) | **Absorbed**; superseded by §1 |
| `docs/plan/optimal-approach-2026-11-17-s18-release.md` | Slice 1 done (retired, not rewritten); Slice 2 half (PR #27 merged, S18 not); Slices 3-5 open | **Absorbed**: 2 -> phase 0, 3 -> 3.1, 4 -> 3.2, 5 -> 5.1/5.3 |
| `docs/plan/finish-plan-2026-11-17.md` (P7.3 hold package) | Conditions 1/3/5/7 closed; 2 (browser), 4 (BO-004), 6 (QWEN) open | **Absorbed**: 2 -> 2.6, 4 -> decision D-6, 6 -> §1 (QWEN is true at `3cbbd8b9`) |
| `docs/roadmap/v3.0.0-release-plan-2026-11-17.md` (P1-P6) | Its tag-blocker premise is refuted (the journey spec is green); P2 (PR #26) done; P3 half (PR #27) done | **Absorbed**; P1 -> 1.2, P4/P5 -> 2.6, P6 -> 5.4 |
| `docs/roadmap/implementation-roadmap-2026-11-16.md` + `docs/plan/implementation-plan-2026-11-16.md` | carry a `SUPERSEDED` banner; its P7.1 **seven conditions** are still the release bar | **Absorbed**: P7.1 -> 5.1, P7.2 -> 5.2/5.3 |
| `docs/roadmap/project-roadmap-2026-11-16.md` + `docs/plan/project-plan-2026-11-16.md` | superseded by the 11-17 pair; its Phase A register pass is done | **Absorbed**; no live slice |
| `docs/roadmap/v3.0.0-implementation-roadmap-2026-11-16.md` + `docs/plan/v3.0.0-execution-plan-2026-11-16.md` | earlier pair, absorbed the same afternoon | **Absorbed**; no live slice |
| `docs/roadmap/implementation-roadmap-hermes-2026-11-16.md` + its plan | a peer session's pair; four measured defects recorded in the 11-16 roadmap §1 | **Not edited, not absorbed**; authoritative to that session only |
| `docs/plans/remediation-plan-2026-11-15.md` | its code defects verified landed; only commit attribution remained | **Absorbed** as 0.1 |
| `docs/plans/implementation-roadmap-v3.0.0.md` | Phases 0-3, 6 landed | **Absorbed**; no live slice |
| `docs/plans/frontend-colour-update-2026-11-14.md`, `frontend-stat-colours-and-dark-mode-integration.md` | landed slices plus research | **Left alone**; no remaining slice |
| `docs/plans/career-legacy-manual-input-findings.md` | concludes "no code defect" | **Left alone** |
| `docs/proposals/{Unity-Cup,Trackblazer,Our-Grand-Concert,URA-Finale}-plan.md` | written game-mechanics plans; their open product items are finals structure and team-race slots | **Absorbed** into 4.2 and 4.3 |
| `docs/audits/reconciliation-rounds/rice-shower-unity-cup-round8-*.md` | 3 schema questions **DEFERRED** by owner ruling; S18-08/S18-09 open | **Absorbed**: S18-08 -> 4.3-adjacent decision D-8, S18-09 -> decision D-9, U-01/U-04/U-06 -> 4.4/4.5/4.6 |
| `docs/audits/capability-matrix/rice-shower-unity-cup-capability-matrix.md` | two rows read `Not implemented`; two more are stale against the tree | **Absorbed**: 4.2 and 4.3, plus the matrix truth pass in 1.1 |
| `docs/audits/reconciliation-rounds/scenario-flow-surface-s18-handoff.md` §7 | four pending items | **Absorbed**: 7.1 -> §1 (help spec updated in worktree), 7.2 -> decision D-8, 7.3 -> decision D-9, 7.4 -> §1 (hand-off shipped) |

---

## 3. Dependency graph

```
0.1 land worktree product content ─┬─> 0.2 lockfile ─> 0.3 untracked build inputs ─> 2.6 browser sweep
        (blocking; nothing ships)  ├─> 0.4 composer script decision
                                   └─> 0.5/0.6 tree hygiene decisions
1.1 close fixed rows ─> 1.2 contradictions ─> 1.3 duplicate blocks ─> 5.1 condition 7
2.1 nativephp exclusion ─> 2.2 stale spec ─> 2.3 probe ruling ─> 2.6 sweep ─> 5.1 condition 2
2.4 fence tool ─> 2.5 gate script (independent; makes every later gate one command)
3.1 KI-166 ─> 5.1 condition 2     3.2 KI-165 ─> 4.1 (fan ladder ships with team rank)
3.3 KI-163 ─> nothing (type hygiene)   3.4 KI-164 ─> nothing   3.5 KI-104 ─> release claim completeness
4.x capability slices: 4.1/4.2 independent and PRD-allowed; 4.3/4.4/4.5 owner-gated; 4.6 small
5.1 seven conditions ─> 5.2 VERSION ─> 5.3 CHANGELOG ─> 5.4 owner cuts the tag
```

**Nothing in phase 3 or 4 may start before 0.1**, because the worktree is the only place the S18 and peer
fixes exist and a branch cut from `HEAD` would ship a tree that does not build (§0 hazard 2 and 3).

---

## 4. Remaining work - phase by phase

Verdict vocabulary: `OPEN` needs building; `DECISION` needs an owner call before it can be scoped;
`BLOCKED` needs an artefact that does not exist yet (a signature, a source, an ADR). No row is `DONE` here;
completed work is §1.

### Phase 0 - Land the tree (blocking)

| # | Slice | Verdict | Deliverable | Acceptance |
| --- | --- | --- | --- | --- |
| 0.1 | Land 36 local commits onto `origin/master` | OPEN | **Re-scoped.** The worktree's product content is already committed on `master` (12 page files, plus `f82b6bd0` Inertia v3). What remains is that `master` is **36 ahead / 8 behind** `origin/master`, so nothing is publishable until the divergence is reconciled | `git rev-list --left-right --count origin/master...HEAD` reads `8 36`; after landing, `0 0` and `npm ci --dry-run` exits 0 on a fresh clone |
| 0.2 | ~~Regenerate `package-lock.json`~~ **CLOSED** | DONE | `npm ci --dry-run` exits **0** and `npm ls @inertiajs/vue3 --depth=0` prints **3.11.0**, so the lock and `package.json` agree again (`f82b6bd0` landed Inertia v3) | `npm ci --dry-run` exit 0 |
| 0.3 | ~~Commit the three untracked build inputs~~ **CLOSED** | DONE | All four are tracked now: `resources/js/polyfills.js`, `config/inertia.php`, `resources/js/components/ui/Sparkline.vue`, `resources/js/components/ui/StatRing.vue` | `git ls-files` names each |
| 0.4 | ~~Resolve the `pre-native-build` removal~~ **CLOSED (restored)** | DONE | The script is back at `composer.json:62-64` (`@php tools/check-electron-env.php`) and the workflow still calls it at `.github/workflows/nativephp-windows.yml:95`. Measured `composer run pre-native-build` -> **exit 0** | `composer run pre-native-build` exit 0 |
| 0.5 | ~~Dispose of the 109 deletions~~ **CLOSED** | DONE | Committed by a peer: `e5fc0e1c docs(legacy): remove jev-inspect subtree` plus the audit moves. Measured `git ls-tree -r HEAD -- jev-inspect` -> 0 files, directory absent, only 1 unrelated ` D` row left. **Do not restore.** |
| 0.6 | ~~Tree hygiene~~ **CLOSED** | DONE | `win-unpacked/` is ignored at `.gitignore:92` (`git check-ignore -v win-unpacked` -> match). The zero-byte root artefact is gone | `git check-ignore win-unpacked` exit 0 |

### Phase 1 - Register and doc truth

| # | Slice | Verdict | Deliverable | Acceptance |
| --- | --- | --- | --- | --- |
| 1.1 | Close the six rows the tree already satisfies | OPEN | `KNOWN-ISSUES.md` dated closure notes for KI-104 (code half), 106, 114, 116, 119, 127, each naming its proving command; KI-162 closes when 0.1 lands its two cases | `audit_status_register.py` OPEN list drops from 11 to 5 (165, 166, 163, 164, 162) |
| 1.2 | ~~Repair the six index/body contradictions~~ **CLOSED** | DONE | A peer reconciled the register: `audit_status_register.py` now reports **`CONTRADICTIONS: none` / `RESULT: clean`**, with **7 index-OPEN** (KI-104, 106, 114, 116, 119, 127, 162) down from 10. The six pairs (KI-69, 74, 100, 107, 117, 124) were closed by dated notes | `python <skill>/scripts/audit_status_register.py KNOWN-ISSUES.md` -> `clean` |
| 1.3 | Six ids carry two body blocks each | OPEN | Merge or cross-reference KI-70, 71, 101, 102, 103, 104; body headings 116 -> 110 | `grep -cE '^#{2,3} KI-'` equals distinct-id count |
| 1.4 | Agent-facing doc truth | OPEN | `AGENTS.md:195` cites `docs/SKILL_AUTOMATION.md` (absent on disk); `PRD.md` §7 links `docs/OPEN-QUESTIONS.md` (absent). Repoint both to the file that actually owns the subject, or record the fold with a dated note | `verify_record_citations.py` on the edited docs reports no `MISSING` for those two paths |

### Phase 2 - Test-suite and tooling truth

| # | Slice | Verdict | Deliverable | Acceptance |
| --- | --- | --- | --- | --- |
| 2.1 | ~~`nativephp-packaged.spec.ts` runs in the default sweep~~ **CLOSED** | DONE | `playwright.config.ts` now carries `testIgnore: '**/nativephp-packaged.spec.ts'`; the runner's total dropped 877 -> **876 in 124 files**, which is how this closure was detected | `npx playwright test --list` -> 876 / 124 and no nativephp line |
| 2.2 | ~~`career-preflight-indep.spec.ts` drives removed controls~~ **REFUTED, leave it alone** | DONE | The premise was wrong. The spec at `:62`/`:64` asserts `getByText('Career mode')` and `getByText('Training policy')` have **`toHaveCount(0)`** — it pins their *absence*, which is the correct assertion for a Phase E2 cut. `git grep 'Career mode' resources/js` -> 0, so the controls really are gone and the spec really is right | nothing to change; do not delete the case |
| 2.3 | Probe specs in the sweep | DECISION | Exact counts now measured: `annotation-sweep` **19** cases (keep, real regression class), `scratch-phase1-screenshots` **4** (park), `scratch-isolation` **3** (keep, KI-80), `containment` **3** (keep), `flash-name-*` **5** across 5 files (retire), `containment-mechanism` **1** (retire). Group total **35**; retiring 6 leaves **870** cases | `npx playwright test --list` parsed per file; see the execution plan's S-1.3 table |
| 2.4 | ~~`tools/md_fence_check.py` crashes with no arguments~~ **CLOSED** | DONE | Committed as `2546689e`: the checker expands a directory to its tracked markdown, reports an unreadable argument as `UNREADABLE`, and exits **2** instead of green. A `@php`-prefixed invocation is a separate false green, documented in the execution plan | bad file -> exit 1; `nope/` -> exit 2; bare -> `0 invalid ... in 292 file(s)` |
| 2.5 | ~~No single gate command exists~~ **CLOSED** | DONE | `tools/gates.sh` plus `composer.json` `"gates": ["bash tools/gates.sh"]`. The Composer-script form was **refuted by measurement** (closed stdin makes `artisan test` abort), so the script redirects stdin per step | `bash tools/gates.sh --fast` -> `GATES: all green`; injected pint violation -> non-zero |
| 2.6 | Full browser sweep never run at this tree | OPEN | Session-private run on a quiet box, after 0.1-0.3 and 2.1-2.3 | 876-minus-ruled-out cases, 0 failures, or <=3 named failures each with a register row |

### Phase 3 - Defects with a named code site

| # | Slice | Verdict | Deliverable | Acceptance |
| --- | --- | --- | --- | --- |
| 3.1 | ~~KI-166: `/training-runs/{run}/races` overflows 59 px at 320~~ **CLOSED** | DONE | Fixed in `816e923e`: `.card:has(> table.t)` now establishes a containing block, so absolutely positioned `sr-only` spans inside table cells no longer escape the overflow scroller. A/B measured on a session-private DB at 320x900: Race Decision **59 -> 0**, Race Planner **40 -> 0**. `career-race-decision.spec.ts` gained 3 occurrences of `320` as the regression case. KI-166 register row CLOSED | `git show 816e923e --stat`; register row reads `KI-166 CLOSED 2026-11-18` |
| 3.2 | ~~KI-165: the fan ladder holds one band of nine~~ **CLOSED** | DONE | Committed: `config/uma.php` carries **7 bands** keyed to each tier's own threshold (`Star [160000, 240000)`, null below 5,000), `FanLadderTest` re-pinned (22 passed / 28 assertions) | `php artisan tinker --execute="echo count(config('uma.fan_ladder'));"` -> 7 |
| 3.3 | ~~KI-163: goal-row shapes duplicated~~ **CLOSED** | DONE | `KI-163 CLOSED 2026-11-18 — shared type landed; typecheck + 26 focused tests green`. Verified: `git grep 'interface GoalRow' resources/js` -> 2 hits, one being the shared owner `resources/js/types.ts:96`; the other is `resources/js/pages/Catalog/Show.vue:91`, which is a genuinely different record (turn/year_label, no fan gate) and was left local by design | `npm run typecheck` exit 0; register row CLOSED |
| 3.4 | ~~KI-164: `SnapshotReviewTest` asserts `fields.N` by position~~ **CLOSED** | DONE | `KI-164 CLOSED 2026-11-18 — keyed reviewField helper landed; 26 focused tests green`. Verified: `function reviewField(array $fields, string $key)` at `tests/Feature/SnapshotReviewTest.php:56`, used throughout; 2 residual `fields.` references are not positional assertions | `php artisan test --filter=SnapshotReviewTest` |
| 3.5 | KI-104: ADR-0027 is Proposed | BLOCKED | Owner signature on `docs/adr/0027-turn-and-decision-logging.md:11` | ADR status line reads Accepted (owner act) |

### Phase 4 - Product capability gaps

| # | Slice | Verdict | Deliverable | Acceptance |
| --- | --- | --- | --- | --- |
| 4.1 | Team Rank cannot be entered anywhere | OPEN | Column exists (`2026_11_15_143000:33`), readers exist (`app/Http/Controllers/Career/CockpitController.php:535`, `app/Http/Controllers/DashboardController.php:234`, `TrainingRun::latestTeamRank()` at `:1376`), **no writer** (`grep team_rank app/Http/Requests` -> none). Add `team_rank` to `StoreTrainingRunRequest` (letters G-S plus S+, matching `TeamRankPayload::LETTERS`), a select on `UnityCupEditor.vue`, and delete the `:226` sentence that points at an unreachable event path | Feature test writes a rank through `runs.update` and the Cockpit strip plus Dashboard tile read it; an invalid letter is refused |
| 4.2 | Unity Cup prints "declares no structure" for its finale | OPEN | `config/scenarios.php` `unity_cup` has **no** `finale` key (only `trackblazer` has one, at `:1024`). Add the finale declaration the corpus supports, and let `ScenarioSectionBuilder::finaleAbsence()` stop claiming absence | Scenario detail prints the declared structure; `ScenarioPanelTest` pins the key |
| 4.3 | Team-race slots can never be created | BLOCKED | `app/Http/Requests/StoreRaceEntryRequest.php:66` validates circles only against a `team_race` slot, and no code creates one (`database/seeders/ScenarioSlotSeeder.php:18` says "Unity Cup: zero rows (no citable source yet)") | A citable source is found and seeded, or the owner records the terminal absence |
| 4.4 | Roster (18 named / 20/20) is a read-out of the deck | BLOCKED | `CockpitController::rosterSection()` derives it; no column, no form field; U-04 roster half is DEFERRED | ADR authorising a sub-model, or a terminal ruling |
| 4.5 | Preseason rounds collapse to one count | BLOCKED | `team_preseason_wins` tinyint only (`2026_10_10_150000:31`); U-06 DEFERRED | `preseason_rounds` table plus ADR, or a ruling that the count is the product truth |
| 4.6 | Per-ancestor residuals | OPEN | The editor ships rank, stars, sparks and add/remove (`resources/js/components/legacy/AncestorEditor.vue:126-218`), but there is **no** per-ancestor guest flag and no per-ancestor identity input, so two same-name ancestors are storable only by hand-built payload | Two fields on the ancestor row, validated in `StoreLegacySelectionRequest` and round-tripped through `LegacySelectionPayload`; PRD §6.3 permits recording, not computing |

### Phase 5 - Release bar and tag hand-off

| # | Slice | Verdict | Deliverable | Acceptance |
| --- | --- | --- | --- | --- |
| 5.1 | The seven release conditions | PARTIAL | PHP suite run: **2 failed, 2467 passed (37567 assertions), 319 s**; both classified — CRLF guard (real, fixed) and `DocCitationParityTest` (pre-existing, 808 vs 708). `bash tools/gates.sh` now exists and is green on `--fast`. Browser suite still unrun at this tree | Each condition quoted with its command output; browser suite on a quiet box |
| 5.2 | `VERSION.md` states two false conditions | OPEN | `:9` says the journey spec is red and cites a `details` locator fix; the file carries one prose comment and no such locator `[M]`. The same row says only `0.1.0` is tagged while the table above it lists five tags. Correct both with dated notes | `git tag -l '3.0.*'` quoted; the journey spec result quoted |
| 5.3 | `CHANGELOG.md` structure and content | OPEN | Two `## [Unreleased]` headings (`:14` and `:464`); **zero** mentions of S1 or S18 | One Unreleased heading; the snapshot pipeline and the S18 batch each named under the release that carries them |
| 5.4 | The tag | BLOCKED | Owner act. Agent prepares through 5.1-5.3 | `git tag -l 'v3*'` contains `v3.0.0` on a commit reachable from `origin/master`, or the owner records why the release waits |

---

## 5. Owner decisions this roadmap needs

| # | Question | Recommendation | Why |
| --- | --- | --- | --- |
| D-1 | Land the worktree as one series from `origin/master`, or split S18 from the peer refactor? | Split: one commit series for S18, one for the peer refactor it depends on | The peer refactor (`careerPosition`, `resolveRouteBinding`) is what makes the two position tests pass; mixing them makes a revert all-or-nothing |
| D-2 | `jev-inspect/` (90 tracked files, deleted in the worktree) | Commit the deletion with its reason, or restore the tree | It is a foreign subproject inside this repo; leaving it deleted-but-tracked keeps every gate's file walk broken (2.4) |
| D-3 | `win-unpacked/` (273 MB) | Ignore it, do not commit it | It is build output; `.gitignore` lists `/public/build` for the same reason |
| D-4 | `pre-native-build`: restore the script or amend the workflow? | Restore the script | `tools/check-electron-env.php` still exists and guards a real build precondition; deleting the caller is a second change to the same intent |
| D-5 | The seven probe specs | Retire the five `flash-name-*` and `containment-mechanism`; keep `containment.spec.ts` | The five pin a mechanism already pinned by `tests/browser/preferences.spec.ts:53`; `containment.spec.ts` pins a deliberate limitation |
| D-6 | BO-004 (dark risk ink) | Close as measured-good | `--color-risk: #FF6F3B` with on-risk `#121212`; re-measure the pair before closing, do not adopt a number from a note |
| D-7 | Per-run `primary_skill` mapping artifact | Return as an artefact warning, not a claim | Names the per-run primary skill and embeds an artefact warning in the response, so a caller cannot mistake it |
| D-8 | `match_candidates` carries 304 race rows, all `Pending` | Add an entity discriminator to `match_candidates` (schema plus ADR), then a race queue | The queue asks "Is this the right trainee?" about races; measured 304 rows, `match_tier` NULL |
| D-9 | `data_sources` covers 1 of 14 configured sources | Backfill or record the terminal gap | Measured 133 rows, one `source_key`; 13 datasets print "No fetch recorded" |
| D-10 | Fan ladder's two non-numeric tiers | Ship the seven numeric bands now; leave the two as a bounded absence | "Beginner 1st place" has no fan count to key on; inventing a threshold is the one thing the repo bans |
| D-11 | Team-race slots (4.3) | Record the terminal absence until a citable source exists | No seeder row can be sourced; a fabricated slot would print invented guidance |
| D-12 | Roster and preseason rounds (4.4/4.5) | Keep DEFERRED; do not widen 3.0.0 | Each needs an ADR plus a sub-model; neither blocks the tag |

---

## 6. Lanes that are not slices

- **Documentation updates are not progress.** The four prose deliverables this project's own exit bar
  requires are `VERSION.md`/`CHANGELOG.md` (5.2/5.3), the register writes a change obliges (phase 1), and
  the owner's tag decision (5.4). Nothing else in this roadmap ends in prose.
- **No new dependency** is needed by any slice here. Every one uses what is installed.
- **No schema change** without an ADR: 4.3/4.4/4.5 are the only schema-shaped items and each is owner-gated.
- **Never touch** `career-preflight.spec.ts` (the Phase E2 cut record); it is the counter-spec that proves
  which specs are stale, and 2.2 is the slice that removes the stale one beside it.
- **Peer-owned paths** are named, not adopted: `app/Http/Controllers/DashboardController.php`,
  `app/Services/Career/RaceStripBuilder.php`, `app/Models/Umamusume.php` carry another session's unfinished
  refactor. 0.1 lands it as that session's work, with attribution.

---

## 7. Verification battery

| Gate | Command | State at this pass |
| --- | --- | --- |
| Targeted Pest | `php artisan test --compact --filter='<Name>' < /dev/null` | the stdin redirect is required on this host |
| Full Pest | `php artisan test --compact < /dev/null` | 2455 / 37,540 / 370.98 s (inherited) |
| Style | `php vendor/bin/pint --test --format agent <explicit paths>` | passed on the 11 changed PHP paths `[M]` |
| Static analysis | `php vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | `[OK] No errors` `[M]` |
| TypeScript | `npm run typecheck` | exit 0 `[M]` |
| UI lint | `node tools/ui-framework-check.mjs` | 516 findings, 207 arbitrary-value vs baseline 209 `[M]` |
| Assets | `npm run build` | not run this pass; a browser gate needs it fresh |
| Fences | `python tools/md_fence_check.py <explicit paths>` | pass on explicit paths; **bare invocation crashes** `[M]` |
| Lore | `php tools/lore.php` and `php tools/lore.php code` | docs 582 / 75, code 365 `[M]` |
| Docs census | `python tools/doc_census.py` | 690 dead vs baseline 708 `[M]` |
| Browser | `PLAYWRIGHT_PORT=83xx PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/b.sqlite npx playwright test <paths> --workers=1 --reporter=line` | not run this pass |
| Dependency audit | `composer audit --no-dev`; `npm audit --omit=dev` | both clean `[M]` |

---

## 7a. Tooling evaluation - what this roadmap uses, and what it refuses

**Verified versions** `[M]`, each read from the tool itself:

| Tool | Version | Read with |
| --- | --- | --- |
| PHP | 8.5.8 | `php -v` |
| Laravel | 13.42.0 | `php artisan --version`; `composer.lock` pins `v13.42.0` (`^13.0` constraint, and Laravel 13 requires PHP >= 8.3 per `laravel.com/docs/13.x/releases`) |
| Pest | 4.7.8 | `composer.lock` |
| inertiajs/inertia-laravel | 3.5.1 | `composer.lock` |
| @inertiajs/vue3 | 3.11.0 installed, **2.3.28 in the lock** | `npm ls`; `package-lock.json:packages` (the drift 0.2 fixes) |
| Node | 26.13.0 | `node -v` |
| npm | 12.2.2 | `npm -v` |
| Playwright | 1.63.0 | `npx playwright --version` |
| Composer | 2.10.2 | `composer --version` (works on this host; `make` does not) |
| git | 2.53.0 | `git --version` |

**Official documentation consulted, and what each settled:**

- `laravel.com/docs/13.x/releases` - Laravel 13's floor is PHP 8.3; this tree runs 8.5.8, so no version work is
  implied by any slice here.
- `inertiajs.com/docs/v3/installation/server-side-setup` - Inertia v3 keeps the `<x-inertia::app>` /
  `<x-inertia::head>` components and still defaults the root view to `app.blade.php`; this tree's
  `resources/views/app.blade.php` is already the modified shell, and `config/inertia.php` (untracked, slice 0.3)
  publishes the SSR switches. It also confirms SSR is opt-in, which is why `INERTIA_SSR_ENABLED=false` in
  `.env`/`.env.example` is correct rather than a workaround.
- `playwright.dev/docs/test-sharding` - with `fullyParallel: false`, `--shard` splits **files**, not cases. This
  repo sets `fullyParallel: false` and `workers: 1`, so sharding across four jobs would need four scratch
  databases and four quiet windows. That is the documented reason slice 2.6 refuses to shard around contention.
- `pestphp.com/docs/optimizing-tests` - `--profile` names slow tests. Use it if the ~6-minute PHP suite ever
  needs splitting; not needed for any slice here.

**MCP servers and agent tooling - used and refused.** Nothing in this roadmap requires a new server:

| Surface | Verdict |
| --- | --- |
| `laravel-boost` | **Useful.** `database_query` for the read-only register and catalogue checks; `application_info` for route/config questions mid-slice. Prefer it over ad-hoc `php -r` PDO. |
| `playwright` (MCP) | **Spot checks only.** It drives a real Chromium and reaches `127.0.0.1`; the repo's own config owns ports, scratch DB and the serial contract, so suites never go through it. |
| `filesystem`, `gitkraken`, `context7`, `tavily`, `memory`, `sequentialthinking` | **Not needed.** `git` in the shell is the source of truth in this repo's docs; `context7` has no library API in question; `tavily` has no subject (every fact here is repo-local). |
| Skills | `trainer-desk-verification` (register census, gate triage, worktree accounting) and `proposal-writing` (this document's shape). `superpowers:verification-before-completion` governs every slice's exit. |
| Subagents | **One dispatch was worth it**: the three-way measurement (register rows, gate truth, capability gaps) ran in parallel against a frozen read-only brief. Do not delegate the release path: it needs `gh`, a worktree with junctions, and PR state read back by the session that reports it. |
| New dependency | **None.** Every slice uses what is installed; `composer audit --no-dev` and `npm audit --omit=dev` are both clean, and the one dev-tree advisory (`symfony/yaml`, transitive) is not reachable from the product. |

---

## 8. Acceptance for the whole roadmap

1. `npm ci` and `npm run build` succeed from a clean clone of the branch (0.2, 0.3).
2. `audit_status_register.py` reports 0 contradictions and every remaining OPEN row carrying a stated reason
   or a ruling (phase 1).
3. The browser sweep runs to completion with no case that fails by construction (2.1, 2.2, 2.6).
4. KI-166 and KI-165 each carry a dated disposition backed by a command (3.1, 3.2).
5. Team Rank is writable through the UI and readable on both surfaces that display it (4.1).
6. The seven release conditions are quoted with exit codes, and no condition is reported as passing without
   its output (5.1).
7. `VERSION.md`, `CHANGELOG.md` and `git tag -l` agree about what `3.0.0` is (5.2, 5.3, 5.4).


---

## 9. Dated erratum — 2026-11-18, measured at `HEAD 816e923e`

The rows above are the record of what was true when this roadmap was written. The re-measurements below
change five verdicts; the original rows are not rewritten. Full detail and per-item commands live in
`docs/proposals/consolidated-ui-ux-and-remaining-work-2026-11-18.md`.

| Row | This roadmap said | Measured 2026-11-18 |
| --- | --- | --- |
| 0.1 | 36 ahead / 8 behind | **38 ahead / 8 behind** (`git rev-list --left-right --count origin/master...HEAD` -> `8  38`) |
| 3.1 KI-166 | fix owed | **CLOSED** at `816e923e`; the nested-scroller subclass closed the same day (`:where(.card,[class*="overflow-x-auto"]):has(table)`), 48-route 390 px sweep reads 0 overflow |
| 3.3 / 3.4 KI-163, KI-164 | OPEN | **BOTH CLOSED 2026-11-18** (shared `GoalRow` type; keyed `reviewField()` helper); register rows carry the dates |
| 4.1 Team Rank | writer owed; conflict noted against ruling R2 | still owed: `grep team_rank app/Http/Requests` reads 0 and `UnityCupEditor.vue:226` still names a turn-event path no UI reaches; the R2/R6-48 owner tension stands |
| 4.6 per-ancestor guest flag | "no guest flag" | **half refuted**: `is_guest` exists end to end (`StoreLegacySelectionRequest.php:113`, `LegacySelectionPayload::LEGACY_KEYS`, the `LegacySelect.vue:565` checkbox); the live residual is a per-ancestor identity label only |
| 2.6 / 5.1 sweep + conditions | unrun | still unrun in full at this HEAD; targeted runs this day: `wizard-actions`+`career-preflight`+`-indep` 26/26, `dashboard`+`wizard-flow`+`accessibility` 29/30 (the 1 is KI-167), `support-deck`+`career-legacy-deck-steps` 31/32 |
| 1.4 dangling citations | OPEN | **DONE 2026-11-18**: `AGENTS.md:195` repointed to the `CLAUDE.md` §3 owner with a dated note; both `PRD.md` link-text mismatches fixed to `docs/research-scratch/PLANS-AND-BRIEFS.md`; census unchanged (808 at HEAD — the counts are historical-record citations, not instructions) |

Two register verdicts this roadmap's §1 row for KI-114 carried forward are also superseded: the two files
measure **31/32 green** (the `#deck-picker-results` locator is legitimate on the run-scoped
`Support/Builder.vue`), and the one red case is the `BaseSelect` `change` re-emit from `282e5437`, not the
retired wizard picker. KI-114 and KI-127 carry dated notes in `KNOWN-ISSUES.md` with the proving commands.
