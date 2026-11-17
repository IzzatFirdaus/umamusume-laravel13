# Trainer Desk — Implementation Roadmap (session: Hermes, 2026-11-16)

**Status:** active · **Measured basis:** `master` @ `d417a9a5`, 2026-11-16 13:52 +08:00.
**Pair:** `docs/plan/implementation-plan-hermes-2026-11-16.md` (per-slice execution steps + commands). Read both.

> **This pair stands alone.** It references no other session's roadmap or plan, and it is not the canonical
> pair — that choice is the owner's. Cross-session status (which pairs exist, what each has covered, the shared
> consolidation dispositions) lives in the project mnemon memory store, not in this file.

## How to read this file

Every claim carries a provenance tag. **[M]** = measured in this authoring session by a command whose reading is
quoted. **[S]** = read out of a committed document or a commit message, not re-measured. **[I]** = inferred, and
therefore a slice's first job is to falsify it.

**Two rules bind every slice.**

1. **Re-measure before acting.** Every path, count and "already done" claim here is a hypothesis at the moment
   you read it. The tree moves under this work: HEAD advanced three times inside one earlier session, and a
   history rewrite is on record for 2026-11-16 04:53. Verify the premise with `git ls-files`, a `grep`, or the
   gate command before spending an editing budget on it.
2. **Prose is not progress.** No slice in this roadmap is complete because a register, a report or a summary
   says so. A slice is complete when its **acceptance criterion** is met by a command whose output is quoted in
   the hand-off. The only documentation a slice may write is the register entry its own change obliges
   (`KNOWN-ISSUES.md` closure, a ledger section, a dated erratum). A slice whose whole diff is documentation
   is a failed slice and is reverted.

**Non-goals do not reappear as slices.** `PRD.md` §6 stands: no auth, no multi-user, no public deploy, no
breeding engine, no Excel, no Redis, no Livewire, no MySQL/PostgreSQL, no race simulation, no image uploads.
`docs/research-scratch/INDEX.md` file discipline stands: no new standalone markdown outside the destinations
the owner named.

---

## 0. Measured current state [M]

| Item | Reading | Command |
| :--- | :--- | :--- |
| HEAD | `d417a9a5` `fix(a11y): lower-case the BO-009 continuation`, **ahead 4 of** `origin/master` (`10dee9d7`) | `git rev-parse HEAD` · `git branch -vv` |
| Worktree | **moving — re-measure.** At authoring: 88 rows (47 ` M`, 1 `M `, 3 ` D`, 37 `??`). Mid-session a peer retired the vendored `jev-inspect/` tree: **175 rows (48 ` M`, 94 ` D`, 32 `??`)** [M] | `git status --porcelain` |
| Peer in flight (2026-11-16) | 94 tracked deletions, **unstaged and on disk absent**: 90 files under `jev-inspect/` (a foreign project's tree, not Trainer Desk's), root `SKILL.md`, and 3 `tools/`+docs paths. Not this roadmap's to restore or commit; R1.4 records the disposition | `git status --porcelain` · `ls-tree HEAD jev-inspect/` |
| Staged | At authoring 1 row (`.tooling/REVIEW-LOG.md`); **empty by the session's end** — peer un-staged it. Re-read live | `git diff --cached --name-status` |
| Stashes | 6 (`stash@{0}` "Track B WIP changes" … `stash@{5}` "Phase 2.1 work in progress") | `git stash list` |
| Routes | 108 total, 36 write | `php artisan route:list --json` |
| Tree | 66 page `.vue`, 117 component `.vue`, 80 migrations | `git ls-tree -r --name-only HEAD` |
| Tests | 257 `tests/Feature`, 13 `tests/Unit`, 124 `tests/browser/**/*.spec.ts`; 172 `ls-files` rows under `tests/browser` counting fixtures and page objects | `git ls-tree -r --name-only HEAD` |
| `laravel/framework` | `v13.32.0` in `composer.lock` | `composer show` / lock read |
| `lint:ui` baseline | `count: 209`, `allowlist: 24` | `tools/ui-lint-baseline.json` |
| `VERSION.md` | `3.0.0`, pre-release; tags `0.1.0, 2.0.1, 2.0.2, 2.1.0, 2.1.1`; `git tag` has no 3.0.0 | `VERSION.md` head |
| `KNOWN-ISSUES.md` (uncommitted, worktree) | 114 `KI-nn` headings, highest `KI-163`, 31 open-shaped; **HEAD carries 112 / 29** | `grep -c '^### KI-'` per ref |
| Gates run by this authoring session | **none.** No test, gate or build was executed while writing this file | — |

Measured facts that correct inherited planning text (each reading quoted, so a stale figure never travels):

- `KNOWN-ISSUES.md` carries **114** `KI-nn` headings in the worktree, highest `KI-163`; **31** carry an
  open-shaped status line [M]. An inherited figure of 37 entries or 6 open candidates is stale.
- `app/Http/Middleware/EnforceNativePHPSecret.php:13` calls `env('NATIVEPHP_SECRET')` outside `config/` [M].
  It is R3.5 here.
- The 114-vs-112 heading delta is exactly two worktree-only entries — `KI-162` and `KI-163` [M].

---

## 1. Completed work — excluded, with its evidence

Do not re-execute any row below. Each is reachable from HEAD and carries a commit sha or a file:line reading.

| Item | Evidence [M unless noted] |
| :--- | :--- |
| Phases closed and recorded by the 2026-11-15 audit pass | `21568017` (+407 lines); `docs/audits/browser-observation-2026-11-15-followup/10-final-verification.md` |
| BO-002 null burst countdown | `2d0b7ee5`; guard at `resources/js/pages/Career/Cockpit.vue:632` (`countdown == null`) |
| BO-005 turn-edit door | Does not reproduce — data-state artifact, `docs/audits/browser-observation-2026-11-15/01-findings.md` reconciliation |
| BO-006 Preflight attention count | `5156bc49`; `Preflight.vue:427` derives the noun from the table |
| BO-007 | Escalated into BO-013; closed by `c6ac05e7` |
| BO-008 Dashboard KPI scope | `d330c82e`; `trainee-scope` and `globalTrainees` in the diff |
| BO-009 bare `N/A :` | `1f4a9c5f` + `d417a9a5`; `grep -rn "N/A :"` over `resources/js app` returns nothing |
| BO-010 unmarked required selects | `9f800577`; `BaseSelect.vue` + `BuildTarget.vue` |
| BO-013 stat-ceiling caption | `c6ac05e7`, `83387115`, `ebe5b3fe`; `Cockpit.vue:769` carries the §1.3.4 guard |
| BO-014 | Reserved id, never a finding — leave the gap |
| BO-015 league-placement bonus | `3ee31da8` |
| BO-016 burst countdown tiers | `fc5aa9d3`; `Cockpit.vue:608-651` |
| BO-017 burst split beside aggregate | `2ae2de54`; `ProgressCard.vue:27-29` |
| R7-02, R7-05, R7-06, R7-14, R7-20…R7-35, R7-38, R7-43, R7-45, R7-46, R7-54, R7-57, R7-61, R7-62, R7-66, R7-68, R7-82, R7-83, R7-84, R7-85, R7-88, R7-89, R7-99 | Round-7 §12 slice-landing record; each row names its sha |
| Walk-8 F-03 spark commitment | `282e5437` + `aaf21cd3`; spec `career-legacy-spark-commit.spec.ts` on HEAD |
| Walk-8 F-08 training-form draft | `TrainingDetail.vue:502,547,572,579` — run-scoped `sessionStorage` draft on HEAD |
| Walk-8 F-01, F-02, F-05, F-07, F-09, F-10, F-12-partial, E-02, E-03, E-08, E-14, E-15, E-17, E-21 | `TraineeSelect.vue:495-504`; commits `6a44a750`, `8476dc8b`, `e28ffb5c`, `39e54e1b`, `e284ade0`, `ed1487bf`, `8176ff52` |
| UX-01…UX-26 retest register | `docs/research-scratch/AUDIT-AND-VERIFICATION.md` sections `## 04 — UX findings retest register` and `## 09 — Consolidated remediation plan`, 24 FIXED, UX-14 not a defect, UX-09 fixed with residual (R3.4 here) |
| NativePHP integration | `composer.json:17-18` (`nativephp/desktop 2.3`, `nativephp/php-bin 1.2`), the `nativephp/electron/` tree, `playwright.nativephp.config.ts`, and 3 workflows tracked under `.github/workflows/` |
| `docs/roadmap/` + `docs/plan/` materialisation | Owner-authorized for this task; the INDEX.md file-discipline carve-out is the owner's naming of the destination |

---

## 2. Dependency graph

```
R0 baseline ──► R1 bank ──► R2 registers ──► R3 suite truth ──► R6 scenarios ──► R7 release
                    │             │                  │
                    │             └──► R4 dependency/security closure
                    │                                │
                    └────────────► R5 feature residuals ───────────┘
```

R1 precedes R2 because a register entry must name a sha, and there is no sha until the diff is committed.
R3 precedes R6 because the scenario walks are gated on the suite being green without unexplained ambiguity.
R7 runs last, on a settled tree.

---

## R0 — Baseline re-verification (blocking; minutes)

**Objective.** Establish the tree truth this roadmap executes against, and detect drift since §0.

**Steps.** `git rev-parse HEAD` · `git status --porcelain` (expect the §0 histogram **as of your own run** — it moved twice in one authoring session; never quote a stored count) ·
`git diff --cached --name-only` (expect 1: `.tooling/REVIEW-LOG.md`) · `git log --oneline -10` ·
`git stash list`.

**Acceptance.** The five readings are quoted in the working notes. If HEAD is no longer `d417a9a5`, re-read §0's
table against the new HEAD and record every delta before proceeding.

**Deliverable.** A baseline block: four readings, and any delta. **No file is written.**

---

## R1 — Bank the worktree (owner-gated; the phase that makes everything after it attributable)

### R1.0 — The commit-attribution decision

Nothing in R2 or R4 can name a sha while the measured fixes sit uncommitted. The owner picks one of four
options per item; this roadmap recommends **A** for the two singleton files and **C** for the 17-file set until
its peer is quiescent.

- **A** — commit the explicit paths now.
- **B** — per-hunk staging, only when `git diff --cached --name-only` is empty.
- **C** — wait for the peer's landing, then re-measure and commit the whole set.
- **D** — park with a written record naming the paths and the `--numstat` figures.

**Never** `git add -A`, `git add .`, `git commit -a`; **never** a real-index commit — the private-index recipe
is in the plan file R1.0. **Verification for any commit:** `git show --stat <sha>` lists exactly the authorized
paths and no peer file.

### R1.1 — BO-001, trainee-profile slug binding

**Objective.** `/career/setup/trainee/rice-shower` resolves instead of 404.
**Measured state [M].** The fix exists in the worktree, not in HEAD: `app/Models/Umamusume.php` is ` M`, and
`git diff HEAD` adds `getRouteKeyName(): string { return 'slug'; }` at line ~106 with its own docblock
explaining that a bookmarked URL form must resolve like the id form. Two call sites build the route with an id
(`BuildTargetController.php:195`, `TraineeSelectController.php:181`).
**Depends.** R0.
**Files.** `app/Models/Umamusume.php` (+ the route's bindings); test in `tests/Feature/CareerTraineeSelectTest.php`.
**Steps.** Confirm the dirty diff is only the binding method · add the failing case (a slug-kind request for a
seeded trainee returns 200 and the same payload as the id-kind request; an unknown slug returns 404) · commit.
**Verification.** `vendor/bin/pest --filter=CareerTraineeSelectTest --compact` green;
`php artisan route:list --path=career/setup/trainee` shows the three routes unchanged.
**Acceptance.** Both URL forms render the profile; the unknown form is a 404, not a 500. `git show --stat`
names only the model and the test.
**Risk.** A `getRouteKeyName` change reaches **every** route bound on `{umamusume}`. Enumerate them first
(`grep -rn 'umamusume' routes/web.php`) and prove each still resolves. This is the slice's real cost.

### R1.2 — BO-004 / BO-011, the dark `--color-risk` token

**Objective.** The dark theme's risk colour clears WCAG 1.4.11 on every surface it paints.
**Measured state [M].** HEAD carries `resources/css/app.css:531` `--color-risk: #FF4400` with no BO-011 marker.
The worktree carries `#FF6F3B` and a `BO-011` comment, so the fix is authored and uncommitted.
**Ratios [S]** (from the BO-004 slice; **re-measure before quoting**): `#FF4400` 3.66 on `raised`, 4.16 on
`panel`, 5.76 on `page`; `#FF6F3B` 4.57 / 5.19 / 7.19; `#FF6D38` rejected at 4.51. Light `#C62828` and eva01
`#FF7E8C` already pass.
**Depends.** R0.
**Files.** `resources/css/app.css`; guard in `tests/Feature/DesignTokensTest.php`.
**Steps.** Re-measure the three pairs on the worktree value · commit the token with the existing comment ·
confirm the named-token coverage case (`DesignTokensTest.php:836-841`) still enumerates `--color-risk`.
**Verification.** `vendor/bin/pest --filter=DesignTokensTest --compact` · `npm run build` (the built CSS is what
the browser gate reads).
**Acceptance.** Every dark surface's risk pair ≥ 3:1 for non-text and ≥ 4.5:1 where it carries text; the figure
is quoted per surface, not asserted once.

### R1.3 — The 17 MWG files (owner decision, R1.0 A/B/C/D)

**Objective.** Land or park Slices 3.2+3.3 of the MWG remediation without sweeping a peer's work.
**Measured state [M].** All 17 paths still ` M`, `+122/−30` as of the prior reading; `BaseInput.vue` carries the
only staged-looking marker (`M ` in one earlier reading — re-check).
**Depends.** R0.
**Files.** The 17 paths listed in `docs/plan/implementation-plan-hermes-2026-11-16.md` §R1.3.
**Verification.** `git diff HEAD --numstat -- <paths>` matches the authorized set; then the focused suites named
in the plan file.
**Acceptance.** One or two conventional commits containing only authorized paths, or a park record naming them.

### R1.4 — Dispositions for the 3 deletions and the 37 untracked rows

**Objective.** Every worktree row has a recorded owner and fate.
**Measured state [M].** Deleted-but-tracked: `docs/research-scratch/rice-shower-unity-cup-capability-matrix.md`,
`tools/check-electron-env.php`, `tools/hermes-preflight.sh`. Untracked: 37 rows across `.scratch-uma/`,
`docs/plans/`, `docs/roadmap/`, `docs/plan/` and peer scratch.
**Verification.** `git status --porcelain` after; every row either committed, restored, or recorded in the
hand-off with its owner.
**Acceptance.** No row is left unnamed. The three deletions are restored or committed with a stated reason —
a silent ` D` of a tracked file is data loss waiting for the next `git checkout`.

---

## R2 — Register truth (every open entry classified with evidence)

**Objective.** Shrink the open backlog from an unverified list to a set of measured verdicts.
**Measured input [M].** `KNOWN-ISSUES.md` is **modified in the worktree** (172 insertions vs HEAD), so the counts differ by ref: **HEAD `d417a9a5` carries 112 headings, highest `KI-163`, 29 open-shaped;
the worktree carries 114 headings, highest `KI-163`, 31 open-shaped.** Quote the ref with the figure — a count
without one is a claim. The BO register leaves **4** residuals. The round-7 register leaves **2** unwalked slices.

**Method — one slice per cluster, never one slice per entry.** Each slice: reproduce or falsify the entry's own
claim · classify `CONFIRMED / STALE / DUPLICATE / BLOCKED-ON-<named>` · fix what is cheap and in scope · then
write the register entry with the evidence.

**The classification is already done — an inventory pass measured all 53 open-shaped entries [M].** Work the
buckets in this order; the first two are the cheapest real progress in the whole roadmap.

| Bucket | Count | What the slice does |
| :--- | ---: | :--- |
| **A. Already fixed, status not flipped** | **17** | The fix is in the tree and reachable from HEAD; only the register line is stale. **Verify the named sha is an ancestor, then flip the status** — no product code |
| B. Code fix, open | 23 | Reproduce, fix, test, register |
| C. Owner ruling needed | 8 | Write the decision request; do not guess |
| D. Infra / process | 7 | A rule or a harness change, not product code |
| E. Documentation only | 4 | The file or line the entry names |
| F. External capture | 1 | KI-100 needs a `[Global]` client screenshot — unclosable on this host, record as such |

**Bucket A, enumerated with its shas [M].** Each sha was confirmed an ancestor of HEAD in this authoring session.

| Entry | Fix in tree | Sha |
| :--- | :--- | :--- |
| KI-142 | pager range wording | `62f43094` |
| KI-143 | dead queue-row variant retired | `76a08984` |
| KI-147 | `RunRaceStrip` mount restored, spec re-green owed | `ad3f8f40` |
| KI-127 | team-race facility panel wrapped | `1119e437` |
| KI-117 | per-run card level column landed | `df624cca` |
| KI-146 | compare reads the query | `da59bdac` |
| KI-119 | superseded by KI-133 (closed 2026-11-13) | — cross-ref |
| KI-111 | body reads fixed at HEAD, entry not flipped | — |
| KI-141 | fix landed 2026-11-10 | — |
| KI-109 | both named files are no longer tracked at HEAD | — |

**Bucket A, extended by the inventory pass [M].** Seven further entries carry HEAD-side fixes with stale status lines, each re-checked at HEAD this session: **KI-61** (fresh-`ParameterBag` construction at `StartCareerRequest.php:351`; `career-preflight.spec.ts` now exists, the gate the entry says was missing), **KI-63** (`progress: false` at `resources/js/spa.ts:23`; every scan scoped to `#app`, `tests/utils/accessibility.ts:4`), **KI-101@3033** (staleness refusal implemented, `tests/browser/global-setup.ts:176` names it), **KI-130** (verified zero `registerPanel` calls for the four URA-only keys at HEAD), **KI-140** (`SUPPORT_CARDS_PER_PAGE = 25` matches the pinned `cards.per_page` 25; `d359e9d9` ancestor [M]), **KI-150** (a recorded constraint, not unfinished work: flip to a constraint status, not `CLOSED`), **KI-152** (`CatalogController.php:421` passes `base_stats`; `Catalog/Show.vue` renders it).

**Bucket A's slice shape.** For each: `git merge-base --is-ancestor <sha> HEAD` → flip the entry's status line with
the sha as evidence → done. Where no sha is recorded, reproduce the entry's own reading first; an entry is never
flipped on the strength of another register's claim. Cite an entry by number **and** line anchor: seven numbers are duplicated (`KI-70`, `KI-71`, `KI-101`, `KI-102`, `KI-103`, `KI-104`, `KI-142` each head two entries) [M].

**R2.8 — Dead code with a measured inventory [M].** Four panel components under
`resources/js/pages/Career/Cockpit/Panels/` have **zero importers** on HEAD: `CareerMilestonesPanel.vue`,
`HappyMeekPanel.vue`, `PreFinalReviewPanel.vue`, `UraMandatoryLadder.vue`. Two more, `SpiritBurstsPanel.vue` and
`TeamRankLadderPanel.vue`, are named only in `ScenarioPanel.vue`'s own comments explaining why their
registrations were removed. **This is KI-130's real state** — its claim is "registered as renderers but never
composed"; measurement says the registrations were already retired for the four URA-only keys, so the entries are
**dead files, not dead registrations**. `CareerGoalsPanel.vue` and `TeamRaceFacilityPanel.vue` are live.

**Slice shape.** One ruling request to the owner (compose them, or delete the files), then the ruling's own
change. Do not delete another session's component on this roadmap's authority — a `.vue` file with no importer is
still a peer's artifact, and KI-70's whole hazard is one session deleting what another is editing.

**Per-slice acceptance.** Every member carries one of the four classes with a command reading or an exact
file:line, written into `KNOWN-ISSUES.md` in its four-part form (never renumbered) — the register write is the
deliverable the change obliges, not a progress report.

**Known readings to save the next agent time [M].** KI-160's claim needs re-reproducing: no `640` case exists in
`career-cockpit-forms.spec.ts`, and `NextDecision.vue` is where the Obligations table lives. KI-145's premise is
already broken: the baseline file records **209**, while the KI records 282 reported — re-measure both sides
before choosing. BO-012's claim "no compare affordance" is false at the component layer:
`support/CardCompare.vue` exists and is wired at `SupportCardCatalog.vue:443`, which `pages/Database/Supports.vue`
renders. The finding's route (`/support-cards`) may still be right — verdict at the route, not the component.
BO-003's `Aut Neque` row is **factory-shaped, not seeder-shaped** [M] (corrected 2026-11-16: the earlier reading here was wrong). `database/factories/UmamusumeFactory.php:27` mints `Str::title(fake()->unique()->words(2, true))` and hard-codes `ReleaseStatus::GlobalReleased` at `:33`. No seeder calls `factory()` (`grep -n 'factory(' database/seeders/` is empty), so `migrate:fresh --seed` cannot produce it; the dev row (`id 138`, `slug aut-neque`, `GlobalReleased`, probed read-only) came from a factory run aimed at the dev file. Remedy stays a purge, plus the owner ruling already raised (EX-02): may a fixture mint a release badge at all.

---

## R3 — Suite truth (close or record every red)

**Objective.** A full run whose every failure has a classification, so R6's walks measure the product rather
than the harness.
**Depends.** R1 (a red attributed to an uncommitted diff cannot be classified).

| Slice | Target | Measured state |
| :--- | :--- | :--- |
| R3.1 | `tests/browser/full-career-journey.spec.ts` | **Likely already green, verify before working it.** `f080bb99` (2026-11-12, ancestor of HEAD [M]) fixed three chained defects and its own message records `full-career-journey.spec.ts 1 passed (23.8s)`; `:108` now *ticks* `#confirmed` rather than pressing past it [M]. The same commit cleared both `UiHygieneTest` violations. One verification run settles it |
| R3.2 | `tests/browser/nativephp-packaged.spec.ts(37,9)` and `(38,9)` TS18047 | `npm run typecheck` errors [S]. Nullable narrowing at a binding boundary |
| R3.3 | PHPStan at `app/Http/Middleware/EnforceNativePHPSecret.php:13` | `env()` outside `config/` [M]. No `config/nativephp.php` exists [M] — either create one for this single key or reuse an existing config file; the middleware's behaviour is unchanged either way |
| R3.4 | UX-09 residual — advisor-backed finale rows print `state: unknown` beside a dated row | Register §11; the negative case (`unknown` **without** context) is pinned by `CockpitObjectivesTest:334` and must stay green |
| R3.5 | F-12 residual / PA-05 — a control unreachable inside a closed `<details>` | `SkillsPlanner.vue:697` still opens closed [M]. Assert **reachability**, not presence |
| R3.6 | PA-01 draft arm and PA-06/PA-07 register rules (`docs/audits/RECONCILIATION-REGISTER.md` §8, §10) | PA-02 closed, PA-01 superseded in part; PA-06/PA-07 are rules, not code |
| R3.7 | `npm run lint:ui` against `count: 209` | Baseline read as 209 [M]; KI-145 claims 282 reported. Re-measure, then either fix the drift or re-baseline with the reason recorded |
| R3.8 | Level-3 sweep on a settled tree | `php artisan test --compact` + the browser suite with a session-private port and scratch DB |
| R3.9 | `resources/js/pages/Veterans/TransferRequests.vue` — the one routable page no browser spec reaches | Route `routes/web.php:298`; only `Veterans/Show.vue:62` references the store route; zero `tests/browser` references [M]. `TransferRequestTest.php` covers the server half, so the gap is render/interaction: the tabs, the pager (`:134`) and the `data-testid="no-transfers"` empty state |

**Acceptance.** Every failure carries one class — regression, pre-existing, unrelated, infrastructure, flaky,
inconclusive — with the focused reproduction already attempted for anything called a regression. Nothing is
deleted or skipped without owner approval and a stated reason.

**Skip census [M].** The whole `tests/` tree carries exactly **2** true test skips — `DesignTokensTest.php:651` (Playwright driver absent) and `SkillAutomationTest.php:12` (gitignored registry input). The other 11 `test.skip(true, …)` calls in `tests/browser` are runtime data-conditional guards inside live cases (the fixture lacks the row), not disabled tests. **Consequence for R7:** the D-288/G-18 contrast arm of `DesignTokensTest` reports a skip when the driver is missing; a skipped gate is not a passed gate.

---

## R4 — Dependency and security closure

**Objective.** Close the framework exposure without inventing the advisory.
**Measured state [M].** `composer.lock` pins `laravel/framework v13.32.0`.
**Steps.** (1) Fetch the official advisories and record affected ranges and fixed-in versions; **if either
figure cannot be confirmed, stop and report — never update on an inherited CVE number.** (2) Narrow update
only. (3) Regression: the suite against R3's baseline count. (4) `composer audit` and `npm audit --omit=dev`.
**Verification.** `composer show laravel/framework` ≥ the confirmed fixed-in; both audits clean of reachable
critical/high; no new test failures.
**Acceptance.** The lock moves, or a written reason says why it must not. A new package is never added here —
`AGENTS.md` §5 requires owner approval for one.

---

## R5 — Feature residuals

| Slice | Objective | Measured state |
| :--- | :--- | :--- |
| R5.1 | R7-37 / S12 — one scenario-link derivation feeding both the draft deck and the run deck | `scenario_link` is typed and counted at `Career/DeckSelect.vue:65,200,208` under a stale finding id; no unifying derivation. Reproduce the `Not a Scenario Link` reading on a run deck first |
| R5.2 | R7-90 — level ceiling vs caption ladder | `SkillPlanRow.vue:131` prints `of 6`; the round-7 landing record dispositioned the row by measurement (both correct, a copy-clarity risk). Verdict only, no code |
| R5.3 | R7-91/92/93 — caret flips and truncated sub-copy | `line-clamp` is absent from `resources/js` [M], so the truncation half may already be gone. One grep + one browser reading settles all three rows |
| R5.4 | OQ-6 artwork remainder | Two remainders stand: the pre-run pick screen cannot host a frame while its trainee picker is a native `<select>`, and skill icons have no stored column. Neither is a slice until the owner widens `PRD.md` |
| R5.5 | `DESIGN.md` §4.7 ruling | Still unruled. Absence rendering is deliberately normal (`R-31` rejects decorative placeholders). No placeholder work executes while it is unruled |

**Acceptance per slice.** A verdict (fixed / falsified / owner-gated) with the reading that produced it. R5.5
produces a decision request, not code.

---

## R6 — Scenario coverage (gated on R3)

**Objective.** Test the three scenarios no walk has covered.

**Measured state [M].** Panels exist for all four (`trackblazer-panel.spec.ts`, `grand-concert-panel.spec.ts`,
`ura-panel.spec.ts`, `unity-cup-panel.spec.ts`). Every UX walk on record is Unity Cup. `RECONCILIATION-REGISTER.md`
§9 names this "the single largest untested surface in the programme".

**Order.** Trackblazer first (largest untested surface, five dedicated components), then Our Grand Concert
(thinnest coverage, shared-strip attribution risk), then URA Finale.

**Gate.** No walk opens while R3 has an unclassified failure. A walk's deliverable is a findings register with
per-screen readings and screenshots under the audit's own evidence directory — the artifacts a walk produces are
its product, so this phase is not a documentation-only phase.

**Acceptance per scenario.** The wizard, the cockpit, the scenario detail and the run deck are each driven with
a real run; every reading is a screenshot plus a value; a scenario-specific defect is filed, and a shared defect
is attributed to the shared surface rather than to the scenario.

---

## R7 — Release bar (`v3.0.0` exit)

The seven conditions, each verified by a command on a settled tree:

1. `vendor/bin/pest` green, no unexplained skips, every skip named.
2. Browser suite green on the session-private harness, and every BO finding that was fixed has an assertion
   pinning it.
3. `vendor/bin/pint --test --format agent <paths>` clean on the change's own files; `npm run typecheck` exit 0;
   `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` at its recorded level.
4. WCAG AA holds on every token pair, with each carve-out named and owner-accepted, and `DesignTokensTest`'s
   browser arm reported as **ran**, not skipped — the driver-absent skip at `:651` would otherwise read as a pass.
5. A clean clone plus install renders artwork with no manual command and shows a labelled placeholder offline.
6. Operator-facing truth (`QWEN.md`, if the owner tracks it) contradicts nothing the tree falsifies.
7. The ledger names every BO and R7 finding's terminal state.

Then, and only then: `VERSION.md` and `CHANGELOG.md` updates for the phase exits that produced them (Docs
Writer / Architect roles), and the tag. `VERSION.md` records `3.0.0` as pre-release and untagged [M] — the
tag is the release act, and it is the owner's.

**Device-dependent items that cannot close on this host** (a physical mobile keyboard check, a Safari
find-in-page reading) are recorded as such in `KNOWN-ISSUES.md`, never forced to a verdict.

---

## Traps, measured

- **`make` does not exist on this host** (`exit 127`, KI-4). Use `composer` scripts or `vendor/bin/*` directly.
- **`pint --dirty` is banned** (KI-75): the dirty set belongs to every session in this tree, not to your slice.
  Always `pint --test --format agent <explicit paths>`.
- **The browser suite is per-session.** `PLAYWRIGHT_PORT=<session port> PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-<port>.sqlite npm run test:browser`. An unqualified run takes a peer's server or DB down (KI-73, KI-80).
- **The suite cannot see the dev database.** `phpunit.xml` forces `DB_DATABASE=:memory:`. Run `php artisan migrate:status` whenever a change reads a table a migration creates (KI-60).
- **The tree moves.** HEAD advanced three times in one earlier session, and the 2026-11-16 reset dropped 15 unpushed commits. Re-measure before attributing any reading, and check `git stash list` before assuming a clean tree.
- **A private index is seeded with `git read-tree HEAD`, never `cp .git/index`.** A copied index carries the
  real index's stale blobs and produces a commit that deletes a peer's landing.
- **Write every artifact under the repository**, in `.scratch-uma/<session>/…`. Nothing at a drive root or beside the repo.
