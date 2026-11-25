# Master implementation roadmap - Trainer Desk (2026-11-18)

**What this file is.** One execution roadmap for the remaining work, derived from all fifteen files in
`docs/sitrep/` and re-measured against the live tree. It replaces the *ordering* role of
`docs/roadmap/IMPLEMENTATION-ROADMAP-2026-11-18.md` and the ten earlier roadmaps in this directory. Those files
stay on disk as the historical record of how each slice was derived; none of them is edited here, and none of the
peer-owned working-tree files is touched by this document.

**Measurement basis.** Figures carry the HEAD they were measured at: `816e923e` (2026-11-18, 13:30-14:10 +08:00) unless stamped `fec658f8` (re-measured in this session). The tree is shared and moves while it is read: a count is a timestamp with its command beside it, not a constant.

**Provenance labels used below.**

- **Measured** - a command was run and its output is quoted.
- **Static read** - read from a tracked file; file and line named.
- **Inferred** - reasoning from measured/static facts, stated as such.

**Authority order** (`AGENTS.md` §2): quality bar `GOVERNANCE.md` §CONSTRAINTS C-1..C-9 > gate registry > accepted
ADRs > `DESIGN.md` > slice plans. `PRD.md` is product truth. `ARCHITECTURE.md` beats its digest. An ADR marked
`Proposed` does not bind.

**Definition of "fully functional final product" used here.** The `3.0.0` line is tagged on a commit reachable
from `origin/master`; the full Pest suite and the full browser suite both have a quiet-box baseline at that commit
with every red classified; no `OPEN` defect changes a shipped screen or the `/api/v1` and schema surfaces; every
capability the enabled configurations promise has a reachable write path and a read-back; and `PRD.md` §6
non-goals stay cut. It does **not** mean auth, hosting, a second database, a wallet, or race-outcome prediction.

---

## 1. Measured current state

### 1.1 Tree and history

| Fact | Measured value | Command |
|---|---|---|
| HEAD | `fec658f8` (merge reconciling the 8-behind with the local series) | `git rev-parse --short HEAD` |
| Divergence | `0  0` — `master` == `origin/master` | `git rev-list --left-right --count origin/master...HEAD` -> `0  0` |
| Working tree | **32 rows** - 9 ` M`, 0 ` D`, 23 `??`, nothing staged | `git status --porcelain -uall` |
| Stashes | 6, all peer/foreign work | `git stash list` |
| Tags | `0.1.0 2.0.1 2.0.2 2.1.0 2.1.1`; no `3.0.*` | `git tag -l` |
| Gate script | `tools/gates.sh` exists, **untracked**; `composer.json` has `gates` | `git ls-files tools/gates.sh`, `git status --porcelain -- tools/gates.sh` |
| Markdown fence checker | tracked (`tools/md_fence_check.py`), 0 invalid fences | `git ls-files`, run |

### 1.2 Stack and version compatibility (researched, cited in §13)

| Component | Installed / declared | Documented requirement | Verdict |
|---|---|---|---|
| PHP | runtime **8.5.8**; `composer.json` `php ^8.3` | Pest 4 requires PHP 8.3+; Pest 5 requires 8.4+ | Floor **stays `^8.3`** while Pest is pinned `^4.7`.[2][3] |
| Laravel | **13.42.0** | Laravel 13 testing docs describe `php artisan test` as the runner.[1] | Match |
| Pest | **4.7.8**, `pest-plugin-laravel ^4.1`, `pest-plugin-browser 4.3.1` | Pest 4 line | Match |
| Inertia (client) | `@inertiajs/vue3` **3.11.0** installed and locked | v3: PHP 8.2+/Laravel 11+, Axios/`qs`/`lodash-es` removed, event renames, `cancelAll`, future flags removed, republish config.[4] | Migration-complete for every listed breaking change; repo imports `axios` directly and uses no removed API |
| Inertia (server) | `inertiajs/inertia-laravel` **v3.5.1** | v3 line[4] | Match |
| Inertia SSR | `config/inertia.php` **now published**, `ssr.enabled` defaults **false** | SSR needs Node 22+ and `@inertiajs/vite` for the automatic path[5] | Node **26.13.0** present; SSR deliberately off, so the KI-76 trap is closed at config level |
| Vue / Vite / TS | Vue **3.5.43**, Vite **7.3.7**, TypeScript **7.0.2**, `vue-tsgo 0.3.0` | - | Match |
| Tailwind | `tailwindcss ^4.0`, `@tailwindcss/vite` | v4: `@theme` must be top-level; `@theme inline` is required when a token references another variable.[6] | Repo's `@theme static` + `[data-theme]` is valid; primitives must use `@theme inline` |
| Playwright | `@playwright/test 1.63.0` | `--workers=1`, `--output`, `--list`, `--grep` are supported CLI options.[7] | Match |
| Node / npm | **26.13.0** / **12.2.2** | SSR floor 22[5] | Above floor |

### 1.3 Inventory

| Surface | Count | Command |
|---|---|---|
| Routes | **110** | `php artisan tinker --execute="echo count(app('router')->getRoutes()->getRoutes());"` |
| Vue pages | **62** | glob `resources/js/pages/**/*.vue` |
| Vue components | **119** | glob `resources/js/components/**/*.vue` |
| PHP test files | **275** (262 Feature, 13 Unit) | glob `tests/Feature/**/*.php`, `tests/Unit/**/*.php` |
| Browser cases | **884 tests in 126 files** | `npx playwright test --list` |
| Skills / runs / team-rank rows | 1910 / 15 / 1 | tinker counts |
| `match_key_ja` nulls | **0** | tinker |
| Independent-training rows | **0** | tinker |

### 1.4 Register and documentation truth

| Fact | Measured value |
|---|---|
| `KNOWN-ISSUES.md` index | 110 rows; **7 OPEN**: KI-104, 106, 114, 116, 119, 127, 162 |
| Body-only new entries | **KI-167** (OPEN, filed 2026-11-18) - not yet in the index |
| Body headings | 116 headings / 110 distinct ids; six duplicate pairs (70, 71, 101, 102, 103, 104) kept by ruling |
| Citation ratchet | `DocCitationParityTest` = **808 dead links vs baseline 708**; `python tools/doc_census.py` -> `808 (808 GONE, 0 UNTRACKED)` |
| ADR checker | **2 findings**: ADR-0026 Decision 529 words, ADR-0029 546 words, both over the 500-word cap |
| New ADRs on disk, untracked | **0031-0036**; ADR-0034 is **accepted** (R6-48), the other five are `Proposed` |
| `VERSION.md` | still `3.0.0` pre-release; two stated release conditions are stale |
| `docs/research-scratch/INDEX.md` | deleted in the working tree; `docs/roadmap/INDEX.md` is the untracked replacement |

### 1.5 Uncommitted work, by concern (read the diff before staging)

| Concern | Paths | Evidence |
|---|---|---|
| UI/UX accessibility batch BO-001..BO-005 | `resources/js/spa.ts`, `resources/js/pages/Career/Timeline.vue`, `Dashboard.vue`, `Review/Index.vue`, `Veterans/TransferRequests.vue`, `resources/css/app.css`, shared `Tile.vue` | audit `03-code-change-plan.md`; `git diff --stat` = 11 files, +142/-11; two new specs |
| New browser specs | `tests/browser/navigation-focus.spec.ts`, `tests/browser/audit-followup-2026-11-18.spec.ts` | untracked |
| Scenario-select card hit area | `resources/js/pages/Career/ScenarioSelect.vue` | attribution **unverified** - may be separate peer work |
| Preflight edits | `resources/js/pages/Career/Preflight.vue` | attribution **unverified** |
| Gate tooling | `tools/gates.sh`, `composer.json` `gates` entry | untracked / modified |
| ADR drafts | `docs/adr/0031`..`0036` | untracked |
| Roadmap, audit and residual docs | `docs/roadmap/*`, `docs/audits/uiux-comprehensive-walk-2026-11-18/*`, `docs/roadmap/RESIDUAL-VERIFICATION-2026-11-18.md`, `docs/sitrep/*` | untracked or modified |
| Status noise | `tests/browser/career-trainee-select.spec.ts`, `tests/browser/wizard-flow.spec.ts` | `git diff --numstat` **empty**; CRLF already normalised to 0 |

---

## 2. The fifteen sitreps: what survives

| Sitrep | Contribution kept here | Disposition |
|---|---|---|
| `sitrep-2026-11-17-phase-a-register-closeout.md` | Register-heading discrepancy; shell-redirection and `make` traps | Historical; traps carried into §15 |
| `sitrep-2026-11-17-s1-s2-wrap.md` | S1 batch (+147/-24, 3 tests); suite-green-exits-1 websocket hazard; 90 `jev-inspect` deletions | Suite hazard carried into §15; deletions CLOSED (`e5fc0e1c`) |
| `sitrep-2026-11-18.md` | Phase 0-5 slice verdicts at the older taxonomy; 38-ahead divergence | Superseded by §5-§10 ordering; facts re-measured |
| `sitrep-2026-11-18-hermes-mwg-recovery-session.md` | MWG adoption; orphan worktree outside the repo; unfiled 3-diff failure | MWG is post-tag scope; worktree cleanup in S4.1 hygiene |
| `sitrep-2026-11-18-mwg-phase3-roadmap-forensics.md` | Working tree moves mid-read; KI counting method; `@php` false green | Traps carried; counting method used in §1.4 |
| `sitrep-2026-11-18-remaining-work-instruction-set.md` | 31-slice taxonomy, seven gate runs, 808/708 proof, gate-script rationale | Slice *content* absorbed; taxonomy replaced |
| `sitrep-2026-11-18-session-handoff.md` | HEAD/divergence/12-file snapshot | Superseded by §1.1 re-measurement |
| `sitrep-2026-11-18-sweep-reconciliation-and-patch-forensics.md` | 876-case sweep at a 23-commit-old tree; 8 failure classes; `page-header.spec.ts` contradiction; absolute scratch-DB path | Failure classes feed S1.2; page-header becomes S2.1 |
| `sitrep-docs-isolation-tag-hold-2026-11-16.md` | PR #26; quiet-box three-axis rule; tag prerequisites | Quiet-box rule kept; PR #26 is owner action |
| `sitrep-roadmap-remediation-2026-11-16.md` | `DocAccuracyTest` false-green fix; `config/nativephp.php` secret; peer drift | Closed work; not re-opened |
| `sitrep-roadmap-remediation-2026-11-16-session2.md` | Citation headroom 699 vs 708; peer staged files | Historical; headroom now negative by 100 |
| `sitrep-roadmap-remediation-2026-11-16-session3.md` | Peer-drift confirmation | Historical |
| `sitrep-session-handoff-2026-11-16-to-18.md` | 50 commits in window, 29 peer; "never raise the baseline" | Rule kept in S1.3 |
| `sitrep-visual-overhaul-2026-11-16.md` | BO-017 plumbing; BaseButton accessible name; Spirit Bursts label drift | Not re-opened here; S1.2 re-measures the specs those findings touched |
| `sitrep-visual-overhaul-2026-11-16-close.md` | `database.spec.ts` 5-vs-6 column drift | Expected suspect in S1.2 |

---

## 3. Completed work - excluded, with the evidence that excludes it

| Area | Evidence |
|---|---|
| Fan ladder (KI-165) | `count(config('uma.fan_ladder'))` -> **7**; `FanLadderTest` green |
| KI-166 Race Decision reflow | fix at `816e923e`; session-private 320px case -> 1 passed; index CLOSED |
| Shared goal types (KI-163) | one `GoalRow`/`GoalGroup` in `resources/js/types.ts`; typecheck green |
| Keyed review assertions (KI-164) | `reviewField()` helper; focused suite 26 passed |
| Inertia lockfile | installed and lock both **3.11.0**; `@inertiajs/vue3` typecheck green |
| `pre-native-build` | present in `composer.json`, called by the Windows workflow |
| `win-unpacked/`, `*.json.bak`, `.workbuddy-ai/` | ignored in `.gitignore` |
| `jev-inspect/` deletions | 0 files at HEAD; closure commit `e5fc0e1c` |
| Packaged-app browser cases | excluded by `testIgnore` in `playwright.config.ts`; default list drops them |
| Markdown fence false greens | `md_fence_check.py` tracked, exit 2 on unreadable, 0 invalid on tree |
| ADR-0030 reference data | contract `code: built`; 263/263 provenance rows measured |
| ADR-0029 per-level effects | `SupportCardEffects::forLevel()/atLevel()/valueAt()` at HEAD (`550fd542`); `DeckAnalysisReachedEffectsTest` exists |
| CHANGELOG single heading | `grep -c '^## [Unreleased]' CHANGELOG.md` -> 1 |
| Team Rank storage | column, model guard, `latestTeamRank()`, turn-event payload writer, seeder - see §4 |
| Logo and favicon wiring | `public/favicon.png`, `resources/img/app-logo.png`, `resources/views/app.blade.php` (`<link rel="icon" type="image/png" href="/favicon.png">`), `resources/js/layouts/AppLayout.vue` (rail `<img :src="appLogo" ... data-rail-mark aria-hidden="true">`); `npm run build` emits `public/build/assets/app-logo-*.png`; `curl http://127.0.0.1:8000/favicon.png` -> `200 image/png`. **Left uncommitted** — no commit instruction received; commit as its own pathspec, never folded into S0.1's accessibility batch (different concern). |
| Backgrounds | Not added and not referenced anywhere in code. `bg-light.png`, `bg-dark.png`, `bg-eva01.png` and renderer variants sit untracked at repo root per the standing instruction "revert back backgrounds ... stay previous state". |
| Register contradictions | `audit_status_register.py` -> `CONTRADICTIONS: none` |

---

## 4. Refuted premises - do not redo these

| Claim found in older plans | Measurement that refutes it | Consequence |
|---|---|---|
| "Team Rank has readers and no writer" | `team_rank` is in `TrainingRun` `$fillable` and the `saving` guard; `ScenarioPanelTest:508-515` records it as a turn-event payload and reads `latestTeamRank()`; ADR-0034 (accepted) defines column-first, event-fallback | Capability gap **closed**; no rank writer slice |
| "Unity Cup needs a `finale` key" | `config('scenarios.scenarios.unity_cup')` has no `finale` key by design; the terminal note is in `docs/proposals/Unity-Cup-plan.md` §14 item 12; no authoritative Global string exists | Closed as terminal; do not invent a label |
| "KI-116 is blocked on A-0.5" | A-0.5 landed `df624cca`; `forLevel()` exists and `DeckAnalysis.php:109` uses it when a slot records a level; only unrecorded slots use catalogue maxima | Close the KI with a dated note; no code |
| "KI-119 needs a run-level `current_spirit` column" | Superseded by KI-133's per-member shape; the run-level column slice was withdrawn | Register closeout, not a migration |
| "`config/inertia.php` is absent" | File exists; `ssr.enabled` defaults false from env | KI-76 trap closed at config level |
| "876 browser cases / 124 files" | `npx playwright test --list` -> **884 tests in 126 files** | Use 884/126 at this HEAD |
| "`gates` can be a Composer script" | Composer passes the caller's closed stdin to `artisan test`; `stdin is not a tty`, exit 1 while every step passes standalone | Shell script with per-step stdin redirect stays |
| "`tools/gates.sh` is committed" | `git ls-files tools/gates.sh` -> empty | Still Phase 0 work |
| "Six probe specs can be retired" | Retirement still needs owner approval (Floor); `career-preflight-indep.spec.ts` pins absence and is correct | Only the 6 named cases remain candidates; no deletion without a ruling |

---

## 5. Phase 0 - Land the tree (blocks every other phase)

The tree carries real product work that no clone can see. Nothing else in this roadmap is measurable until this
phase lands, because every gate result would otherwise describe a tree that exists in one working directory only.

### S0.1 - Commit the UI/UX accessibility batch

**Objective.** Land BO-001..BO-005 as reviewed commits so the focus, keyboard-scroll, title-tier, provenance and
dashboard-absence fixes stop living in one working directory.

**Deliverable.** One commit per concern, named pathspecs only:
`resources/js/spa.ts`, `resources/js/pages/Career/Timeline.vue`, `resources/js/pages/Dashboard.vue`,
`resources/js/pages/Review/Index.vue`, `resources/js/pages/Veterans/TransferRequests.vue`,
`resources/js/components/ui/Tile.vue`, `resources/css/app.css`, `tests/browser/dashboard.spec.ts`,
`tests/browser/review.spec.ts`, plus new `tests/browser/navigation-focus.spec.ts` and
`tests/browser/audit-followup-2026-11-18.spec.ts`.

**Do not stage blind.** `Preflight.vue` (14 lines) and `ScenarioSelect.vue` (23 lines) have unverified attribution;
read `git diff -- <path>` and attribute before including them.

**Dependencies.** None for the commit; owner names the pathspecs (standing rule).

**Acceptance.** Each commit contains only its own paths; `git diff --cached` empty between commits; the two new
specs pass.

**Verify.**

```bash
npm run typecheck
npm run build
PLAYWRIGHT_PORT=8410 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/land-2026-11-18/browser-8410.sqlite \
  npx playwright test navigation-focus.spec.ts audit-followup-2026-11-18.spec.ts --workers=1 \
  --output=.scratch-uma/land-2026-11-18/artifacts
```

### S0.2 - Commit and repair the gate (`tools/gates.sh`)

**Objective.** Make the single local evidence command tracked and honest. The current script runs bare
`pint --test --format agent` (whole tree, the KI-75 defect) and omits the ADR checker, the citation census and the
untracked-file checker.

**Deliverable.** `tools/gates.sh` tracked; the pint step runs explicit paths (changed files from
`git diff --name-only HEAD` plus untracked PHP), and three steps added: `python tools/adr-check.py docs/adr`,
`python tools/doc_census.py`, `python tools/check_untracked.py`. Each step keeps its `< /dev/null` redirect.

**Dependencies.** S0.1 (so the changed-file set is meaningful).

**Acceptance.** `bash tools/gates.sh --fast` exits 0 and its summary line names every step; an injected pint
violation makes it exit 1 (non-vacuous proof, already recorded once).

**Verify.**

```bash
bash tools/gates.sh --fast
python tools/adr-check.py docs/adr
python tools/doc_census.py
```

### S0.3 - Commit the ADR drafts and the document batch

**Objective.** Put the six drafted ADRs and the audit/residual/roadmap documents under version control, and
settle the `INDEX.md` swap on evidence rather than on tidiness.

**Deliverable.** `docs/adr/0031`..`0036` committed; `docs/roadmap/RESIDUAL-VERIFICATION-2026-11-18.md`,
`docs/audits/uiux-comprehensive-walk-2026-11-18/*`, `docs/roadmap/*` committed; the deletion of
`docs/research-scratch/INDEX.md` committed only after `docs/roadmap/INDEX.md` is proven to cover every heading the
old index carried (diff the heading lists, not the prose).

**Dependencies.** Owner names pathspecs.

**Acceptance.** `git status --porcelain` shows no unexplained `D`; the fence checker reports 0 invalid fences over
the added markdown; the citation census does not rise above 808 from this batch alone.

**Verify.**

```bash
python tools/md_fence_check.py docs/adr docs/audits docs/roadmap docs/research-scratch
python tools/doc_census.py
```

### S0.4 - Clear the empty-diff status noise

**Objective.** Remove two ` M` rows that carry no content change.

**Deliverable.** `git checkout -- tests/browser/career-trainee-select.spec.ts tests/browser/wizard-flow.spec.ts`
after confirming both show an empty `git diff --numstat` and 0 CRLF pairs.

**Dependencies.** None.

**Acceptance.** Both rows leave `git status`; `LineEndingGuardTest` still passes.

**Verify.**

```bash
git diff --numstat -- tests/browser/career-trainee-select.spec.ts tests/browser/wizard-flow.spec.ts
php artisan test --compact --filter=LineEndingGuardTest
```

### S0.5 - Reconcile `master` with `origin/master` (owner-gated)

**Objective.** Turn `8 behind / 38 ahead` into one history that a tag can sit on. Merge is the default; a rebase
needs an explicit owner ruling (`AGENTS.md` §14).

**Deliverable.** A merge commit (or a fast-forward after owner-approved rebase), then a push of the 38 local
commits plus the merge. Do this only on a tree free of peers' uncommitted work.

**Dependencies.** S0.1-S0.3 landed; owner authorises the push; peers' edits committed or stashed by their owners.

**Acceptance.** `git rev-list --left-right --count origin/master...HEAD` -> `0  0`; `npm ci --dry-run` exits 0 on a
fresh clone of the resulting commit; `git merge-base --is-ancestor HEAD origin/master` exits 0.

**Verify.**

```bash
git rev-list --left-right --count origin/master...HEAD
npm ci --dry-run
git log --oneline --graph -12
```

### S0.6 - Re-tune light/dark to Azure Daybreak / Midnight Circuit (owner-gated)

**Objective.** Replace the current light page #F4F4FF / dark page #09090B with the Azure Daybreak (light) / Midnight Circuit (dark) palettes so neither theme sits at a contrast extreme, per the owner's 2026-11-18 paste. Theme wiring is already complete and green: server-side compose in `AppServiceProvider`, `useTheme()` hydration (`data-theme` on `<html>`, null = follow system), `Preference` keys `light`/`dark`/`eva01`, radio tiles on `Preferences/Edit` (SCREEN-024), and `data-rail-mark` untouched on the rail logo. `DesignTokensTest` is 17/17 and G-18/G-19 pass at the current palettes.

**Why an owner ruling first.** The current palettes carry signed decisions: light-first default reversed 2026-09-27, HLF exact pixels and the Umamusume brand palette signed 2026-10-24, C1a warm-brown ink #6A5641 raised 2026-11-14, G-18 pairs measured 4.57/5.19/7.19. Swapping to Azure Daybreak / Midnight Circuit reverses those rulings and can break G-18: Azure Daybreak page #DDEEFF with ink #6A5641 measures about 3.7:1 (inferred), under 4.5:1. No token changes until D-13.

**Deliverable (shovel-ready once D-13 lands).** CSS only, in `resources/css/app.css`:

- `@theme static` light: `--color-page: #DDEEFF`, `--color-panel`/`--color-raised: #FFFFFF`, `--color-primary: #347DFF`, `--color-accent: #9DDCFF`, `--color-secondary: #B9F0DE`, `--color-highlight: #FF65B8`; ink tokens keep their C1a values pending G-18 re-measurement.
- `html[data-theme='dark']`: `--color-page: #050A1B`, `--color-panel: #101F49`, `--color-raised: #101F49`, `--color-primary: #164BFF`, `--color-accent: #22C8FF`, `--color-secondary: #283B70`, `--color-highlight: #F02FA9`; ink #FCFCFC unchanged.
- No `dark:` utilities, no skeleton palette classes (G-19), no new tokens beyond the palette set, `data-rail-mark` untouched, eva01 block not touched.

**Dependencies.** D-13 owner ruling (apply Azure Daybreak / Midnight Circuit, keep signed palettes, or third measured option). S0.1-S0.4 landed so the diff is one concern only.

**Acceptance.** D-13 recorded; if approved, the CSS diff above applied with no other file touched; `pint --test --format agent resources/css/app.css` passes; `DesignTokensTest` 17/17; G-18 pairs re-measured at least 4.5:1 in both themes (any failure goes to an ink ruling, never a silent default); `npm run typecheck` and `npm run build` green.

**Verify.**
```bash
php artisan test --compact --filter=DesignTokensTest < /dev/null
php vendor/bin/pint --test --format agent resources/css/app.css
npm run typecheck
npm run build
```
Plus a browser G-18 spot check on a shell page against resolved custom properties (DESIGN.md §2.1).


---

## 6. Phase 1 - Suite and document truth

### S1.1 - Quiet-box full Pest baseline

**Objective.** Produce the first full PHP result at the landed HEAD, with every red classified; settle KI-162.

**Deliverable.** One recorded run with counts, exit code, duration and artifact path under
`.scratch-uma/<session>/`; a dated KI-162 verdict (reproduces / does not reproduce / contention); any new failure
filed with its first failing assertion and file.

**Dependencies.** Phase 0; quiet box (free memory > 6 GB, CPU < 30%, no peer `pest`/`serve`); one Pest process at a
time.

**Acceptance.** No unclassified red; no pass claimed from an unread run; `DocCitationParityTest` red is expected
until S1.3 and must be named as such.

**Verify.**

```bash
php tools/gate-preflight.php
php artisan test --compact < /dev/null
```

### S1.2 - Full browser sweep at the landed HEAD

**Objective.** Replace the 23-commit-old 876-case sweep with a current baseline at 884 cases / 126 files, and
classify every failure into the eight known classes or a new one.

**Deliverable.** One single-process run with a session-private port, an **absolute** `PLAYWRIGHT_SCRATCH_DB` path and
a fresh `public/build`; per-file wall clock; a failure table naming spec, line, class (A-H from the earlier
reconciliation, or new), and whether it is product, spec, or harness. `annotation-sweep` (19 cases) gets its own
timing measurement, not a deletion.

**Dependencies.** S0.1 (build inputs), S1.1 (quiet box), owner authorisation for `npm run build` in the shared tree.

**Acceptance.** Every failure classified; `page-header.spec.ts` expected red is confirmed or refuted against S2.1;
total wall clock recorded.

**Verify.**

```bash
npm run build
PLAYWRIGHT_PORT=8411 PLAYWRIGHT_SCRATCH_DB=$PWD/.scratch-uma/sweep-2026-11-18/browser-8411.sqlite \
  npx playwright test --workers=1 --output=.scratch-uma/sweep-2026-11-18/artifacts
```

### S1.3 - Citation ratchet: 808 -> 708 or lower

**Objective.** Close the 100-link gap that keeps `DocCitationParityTest` red at clean HEAD without raising the
baseline.

**Deliverable.** A worklist generated from `python tools/doc_census.py` (GONE targets, most-cited first); each target
either repointed to the master that absorbed it, replaced by a backticked path that exists, or tombstoned with a
dated note in the citing file. No `DOC_CITATION_BASELINE` change. Re-run until the census is at or below 708.

**Dependencies.** S0.3 (the new docs are committed, so the census sees them).

**Acceptance.** `python tools/doc_census.py` -> `dead markdown links: <= 708`; `DocCitationParityTest` passes both
cases; the 12 most-cited dead targets are each resolved or explicitly tombstoned.

**Verify.**

```bash
python tools/doc_census.py
php artisan test --compact --filter=DocCitationParityTest
```

**Scope warning.** This is a mechanical pass over docs, not a rewrite. If a target turns out to be a live file that
moved, repoint; if it was deleted with its content folded into a master, point at the master's section anchor.

---

## 7. Phase 2 - Open defects with a named code site

### S2.1 - `page-header.spec.ts` asserts a superseded tier (6 cases)

**Objective.** End the contradiction between the spec and the signed design. `PageHeader.vue:68` renders
`text-3xl font-semibold text-ink-strong`; `DESIGN.md:368` specifies the same; `page-header.spec.ts:46-48` asserts
`text-[28px]` and `font-extrabold`. The component is correct, the spec is stale.

**Deliverable.** Update the three assertions to `text-3xl` / `font-semibold` / `text-ink-strong`; keep the one-`h1`
and eyebrow checks unchanged. Owner approval is recorded in the commit message because the earlier pass flagged test
edits as approval-gated.

**Dependencies.** S1.2 (baseline shows the 6 failures); owner approval for the spec edit.

**Acceptance.** `page-header.spec.ts` green on all six pilots; no other spec touched; diff limited to lines 45-48.

**Verify.**

```bash
npx playwright test page-header.spec.ts --workers=1
```

### S2.2 - KI-167 Scenario Selection focus round trip

**Objective.** Establish why `career-scenario-select.spec.ts:147` sees `inactive` when a real CDP `Space` press
restores focus correctly, then fix the harness or the component - not the assertion.

**Deliverable.** A root-cause note naming the mechanism (Playwright keyboard dispatch vs Inertia re-render
re-resolution); the minimal fix; three added cases for mouse selection, arrow-key navigation and a slow PUT, which
KI-167's own "not covered" note calls out.

**Dependencies.** S0.1 (the card hit-area change may interact), S1.2 (build).

**Acceptance.** `npx playwright test career-scenario-select.spec.ts --grep "selects a scenario by keyboard"` passes;
the three new cases pass or are filed as separate defects with evidence.

**Verify.**

```bash
npx playwright test career-scenario-select.spec.ts --workers=1
npm run test:a11y
```

### S2.3 - KI-114 deck-picker specs drive retired controls

**Objective.** Repair the two specs against the shipped `CardPicker.vue` vocabulary: `query` -> `q`,
`status` -> `owned_only`, drop the rarity filter, and correct the `Level for Slot` label.

**Deliverable.** `tests/browser/support-deck.spec.ts` edited; `career-legacy-deck-steps.spec.ts` re-measured; 21 cases
kept, none deleted. A dated KI-114 note with the run result.

**Dependencies.** S1.2 (fresh build); no owner approval - it is a spec repair, not a deletion.

**Acceptance.** Both files green at the session-private harness; no selector in either file targets an id that the
shipped components do not render.

**Verify.**

```bash
PLAYWRIGHT_PORT=8412 PLAYWRIGHT_SCRATCH_DB=$PWD/.scratch-uma/ki114/browser.sqlite \
  npx playwright test support-deck.spec.ts career-legacy-deck-steps.spec.ts --workers=1
```

### S2.4 - KI-127 Team Race Facility panel has no card wrapper

**Objective.** Give `TeamRaceFacilityPanel.vue:54` the same card and capsule header its sibling panels carry, so the
panel stops rendering as a bare flex column.

**Deliverable.** Markup change in the component using the existing `Tile`/capsule pattern; one assertion in the
scenario-panel spec that the panel root carries the card class; no new component.

**Dependencies.** S0.1 (Tile edits landed).

**Acceptance.** Rendered panel has the card wrapper at 390 and 1280; `scenario-panel.spec.ts` green.

**Verify.**

```bash
npm run lint:ui
npx playwright test scenario-panel.spec.ts --workers=1
```

### S2.5 - KI-104 browser half and ADR-0027

**Objective.** Close the two remaining strict-mode cases and settle the ADR that blocks the register row.

**Deliverable.** (a) Run `career-cockpit-forms.spec.ts` after a fresh build; if green, a dated closure note naming
the commit and run. (b) Owner signature on `docs/adr/0027-turn-and-decision-logging.md`; then regenerate the ADR
index. If the cases are red, a new KI row with the first failing assertion.

**Dependencies.** Build authorisation; owner signature for (b).

**Acceptance.** KI-104 index row either CLOSED with evidence or narrowed to the ADR half only; `adr-check.py` shows no
new finding.

**Verify.**

```bash
npx playwright test career-cockpit-forms.spec.ts --workers=1
python tools/adr-check.py docs/adr
```

### S2.6 - Register closeout for KI-116 and KI-119 (and vendor the auditor)

**Objective.** Convert two stale `OPEN` rows into measured verdicts, and make the acceptance tool runnable from a
clean checkout.

**Deliverable.** Dated body notes: KI-116 closed because `forLevel()` exists and `DeckAnalysis` uses it for recorded
slots, with the unrecorded-slot behaviour named as disclosed absence; KI-119 closed as superseded by KI-133's
per-member shape, with the run-level column explicitly withdrawn. Index rows flipped. Copy
`audit_status_register.py` into `tools/` (it currently exists only in the verification skill), or record the exact
substitute command in the slice's note.

**Dependencies.** None for the notes; the register file is peer-owned while dirty - coordinate before editing.

**Acceptance.** `python tools/audit_status_register.py` (or the recorded substitute) reports `CONTRADICTIONS: none`;
the two rows this slice closes leave the OPEN set; no row renumbered.

**Verify.**

```bash
python tools/audit_status_register.py
python tools/adr-check.py docs/adr
```

---

## 8. Phase 3 - Missing product capability

### S3.1 - Restore the Independent Training door (the one real functional gap)

**Objective.** The mode is fully built in the backend - `Lifecycle::start()` is called from
`PreflightController.php:194-197`, and `StartCareerRequest` already validates `independent`, `policy`, `rotation` and
`priority_skills` - but the Preflight controls were cut in Phase E2 and never replaced. `IndependentTrainingRun` has
**0 rows**. A Trainer therefore cannot start the mode; only a seeder or a test can.

**Deliverable.** Three controls on `resources/js/pages/Career/Preflight.vue` wired into the existing `useForm`:
(a) an independent-session toggle, (b) a `policy` select bound to `IndependentTrainingPolicy`, (c) an optional
priority-skill picker capped at 10; the rotation control may stay omitted if the default is documented. One Feature
test proving `POST` with `independent=1` creates the overlay row and `Lifecycle` advances it; one browser case
proving the toggle reveals the fields and the run's Dashboard/Result surfaces show the stored state.

**Dependencies.** Owner ruling on ADR-0032 (the mode has no PRD story); S0.1 Preflight attribution resolved.

**Acceptance.** A run created through the UI has a non-null `independent_training_runs` row; the Result screen's
`independent` section renders it; invalid policy values are rejected by the Form Request, not inline.

**Verify.**

```bash
php artisan test --compact --filter=IndependentTraining
npx playwright test career-preflight.spec.ts --workers=1
php artisan tinker --execute="echo \App\Models\IndependentTrainingRun::count();"
```

### S3.2 - Correct the shipped hint-level copy

**Objective.** The schema stores a hint level, but two shipped sentences say it does not:
`resources/js/pages/Skills/Show.vue:397` ("No run stores a hint level...") and `:522` ("No table stores a hint
level..."). The planner component comment repeats the stale claim at `SkillsPlanner.vue:15`.

**Deliverable.** Rewrite both sentences to state the recorded fact (a run may hold a hint level, priced from the
config ladder); correct the component comment; add one assertion to the skills-page Feature test that the stale
strings are absent.

**Dependencies.** ADR-0035 acceptance (owner) for the wording that becomes canon.

**Acceptance.** `git grep -n "No table stores a hint level\\|No run stores a hint level"` -> 0 hits; skills page test
green; lore gate reports no new hit.

**Verify.**

```bash
git grep -n "No table stores a hint level"
php artisan test --compact --filter=SkillHintLevel
php tools/lore.php
```

### S3.3 - Confirm the Japanese search key is populated

**Objective.** ADR-0036's open item says the column is never back-filled in the migration. Measurement shows the dev
database is already complete: `Skill::whereNull('match_key_ja')->count()` -> **0** across 1910 skills. What is not
proven is that a **Japan-only** row exists and is reachable by its printed name.

**Deliverable.** A Feature case that flags one skill `japan_only`, searches by its folded Japanese name, and asserts
the row is returned and that its printed label is `name_ja`; a dated note on ADR-0036 recording the 0-null
measurement. If no such row can be flagged without inventing data, record the terminal absence instead.

**Dependencies.** None.

**Acceptance.** The search case passes, or a dated terminal note explains why the surface cannot be exercised.

**Verify.**

```bash
php artisan test --compact --filter=SkillSearchScreenTest
php artisan tinker --execute="echo \App\Models\Skill::whereNull('match_key_ja')->count();"
```

### S3.4 - Decide the maiden-lock fallback for imported runs

**Objective.** ADR-0031's open item: the turn-12 fallback is inferred from where the Debut sits in the calendar,
not read from a stored fact. Either record the inference as the accepted rule, or add the stored fact.

**Deliverable.** An owner ruling recorded in ADR-0031; if the ruling asks for storage, a migration with `down()`, a
backfill for the 15 existing runs, and a case proving an imported run resolves the lock the same way a native run
does. If the ruling accepts the inference, a dated note and no code.

**Dependencies.** Owner signature on ADR-0031.

**Acceptance.** ADR status and code agree; `MaidenGateColumnDroppedTest` and `CareerRaceDecisionTest` green either
way.

**Verify.**

```bash
php artisan test --compact --filter=MaidenGate
python tools/adr-check.py docs/adr
```

### S3.5 - Owner-gated capability register (no agent code until ruled)


**D-number erratum (2026-11-25).** This table cites archived
emaining-work-roadmap-2026-11-17.md §5 D-numbers, not MASTER's §14 D-numbers. canonical register is DECISIONS-AND-DEPENDENCIES.md §1. Cross-reference: archived D-8 = match_candidates discriminator (resolved by ADR-0037, measured); archived D-9 = data_sources backfill (still open); archived D-11 = team-race slots (canonical D-8, **ACCEPTED** 2026-11-25); archived D-12 = roster+preseason (canonical D-9 **ACCEPTED (architectural)** via ADR-0039, D-10 **ACCEPTED** 2026-11-25). No D ID renumbered; note records register each row belongs to.

| Capability | What exists | What unblocks it | Deliverable when ruled |
|---|---|---|---|
| Team-race slots | **ACCEPTED** unity-cup-team-races.json (5 seeded slots) | D-8 (canonical) — ruling recorded 2026-11-25 | Migration + parser + test landed; focused verification 5/5 green |
| Roster sub-model / preseason rounds | **ACCEPTED / ACCEPTED** — roster via ADR-0039; preseason accepted 2026-11-25 | D-9 (ACCEPTED - architectural), D-10 (ACCEPTED) | oster_members migration + tests green; preseason_rounds design delegated Backend |
| Per-ancestor sparks / guest fields | Single affinity grade in `legacy_selection` | ADR acceptance | Typed payload, accessors, form, round-trip test |
| Independent Training completion maths | Calibrated approximation | Evidence ruling | Source-cited calibration brief, then code |
| Grand Concert packages (S9-S18) | Blocked, not partial | Owner accepts evidence | One code slice per accepted package |
| `match_candidates` entity discriminator | 304 Pending race rows | D-8 ADR | Migration + review queue + feature test |
| `data_sources` provenance backfill | One `source_key` covers 14 datasets | D-9 ruling | Backfill action + provenance test |

**Rule.** No row in this table may be started as a "small follow-up". Each needs its ruling first, and each
deliverable is code or a dated terminal note - never a progress report.

---

## 9. Phase 4 - Release truth and tag

### S4.1 - Re-baseline `VERSION.md`

**Objective.** Replace the two false release conditions with the measured state: the suite baseline and the browser
baseline now exist (S1.1/S1.2), the tag does not, and `master` is not yet `0 0` with `origin/master`.

**Deliverable.** `VERSION.md` rewritten with: version `3.0.0`, release state, the exact gate commands and their last
recorded results, the tag row empty, and the framework/PHP table matching §1.2. No aspirational condition left in.

**Dependencies.** S1.1, S1.2, S0.5.

**Acceptance.** Every claim in the file is re-derivable from a command printed beside it; no stale count remains.

**Verify.**

```bash
git grep -n "3\\.0\\.0" VERSION.md
git rev-list --left-right --count origin/master...HEAD
```

### S4.2 - Complete the CHANGELOG for the 3.0.0 range

**Objective.** The single `[Unreleased]` heading is correct, but the range is silent on the S1 batch, the S18 batch
and the UI/UX accessibility batch.

**Deliverable.** Entries for: S1 snapshot pipeline; S18 scenario-flow surface; fan ladder; Inertia v3; the
accessibility batch (focus continuity, timeline keyboard scroll, title tier, provenance labels, dashboard absence
reasons); the six ADR drafts. Keep a Changelog order and one heading.

**Dependencies.** S0.1, S0.3.

**Acceptance.** `grep -c '^## [Unreleased]' CHANGELOG.md` -> 1; every merged commit family in the range appears once.

**Verify.**

```bash
grep -c '^## \[Unreleased\]' CHANGELOG.md
git log --oneline v2.1.1..HEAD
```

### S4.3 - ADR hygiene, signatures and index

**Objective.** Clear the two word-cap findings, collect the five missing signatures, and regenerate the index.

**Deliverable.** ADR-0026 Decision section trimmed to <= 500 words without losing a ruling; ADR-0029 likewise (or an
owner waiver recorded in the ADR); owner signatures for 0022, 0023, 0027, 0031, 0032, 0033, 0035, 0036; index
regenerated with `python tools/adr-index.py --write`.

**Dependencies.** Owner acts for signatures; trimming is agent work.

**Acceptance.** `python tools/adr-check.py docs/adr` -> `0 finding(s)`; every ADR's contract and Status line agree.

**Verify.**

```bash
python tools/adr-check.py docs/adr
git diff -- docs/adr/README.md
```

### S4.4 - Cut `v3.0.0` (owner only)

**Objective.** Tag the release once Phases 0-2 are green and Phase 4's truth work is landed.

**Deliverable.** Annotated tag on a commit reachable from `origin/master`; `VERSION.md` release and tag rows updated
in the same change; release workflow observed.

**Dependencies.** S0.5, S1.1, S1.2, S4.1-S4.3; owner authorisation.

**Acceptance.** `git tag -l 'v3*'` contains `v3.0.0`; `git merge-base --is-ancestor v3.0.0 origin/master` exits 0;
the tagged commit's full gate output is recorded.

**Verify.**

```bash
git tag -l 'v3*'
git merge-base --is-ancestor v3.0.0 origin/master
bash tools/gates.sh
```

---

## 10. Phase 5 - After the tag, toward 1.0

| Slice | Objective and tangible deliverable | Dependencies | Verification |
|---|---|---|---|
| S5.1 | Provenance backfill: every engine-written row carries a `data_sources` entry; one backfill action plus a provenance test | D-9 | `php artisan test --compact --filter=Provenance` |
| S5.2 | `match_candidates` entity discriminator + race review queue, so 304 Pending race rows can be disposed of | D-8 ADR | scratch-DB migrate + seed; review feature test |
| S5.3 | Close OQ-2 (source legality/refresh) and OQ-3 (scheduler vs manual) in `PRD.md` and `config/uma.php` | owner ruling | `php artisan config:show uma.sources` |
| S5.4 | ADR-0028 primitive layer: FQ-01..FQ-09, `@theme inline` primitives, parity doc, arbitrary-value count down | owner FQ rulings | `npm run lint:ui`; `npm run typecheck` |
| S5.5 | ADR code gaps: 0002 markers, 0004 FK + repoint, 0010 legacy UI, 0011 hint import, 0018 `PageSize` | respective rulings | focused Feature tests; `adr-check.py` |
| S5.6 | Artwork coverage (OQ-6): skill-icon column and pre-run frame from the allowlisted host only | OQ-6 ruling | `php artisan uma:fetch-art --dry-run`; mirror test |
| S5.7 | Performance budgets: measure catalogue index and cockpit reads on a quiet box; pin query ceilings | S1.1 | benchmark output; performance test |
| S5.8 | MWG accessibility items still unimplemented (IME-safe Enter, `<search>`, popover tooltips, LCP images, dialog overscroll, `BaseInput` props, `<fieldset>`, `closedby`, `content-visibility`); MWG #5 stays excluded | owner approval | `npm run test:a11y`; axe cases |
| S5.9 | 1.0 gate: close the four open PRD questions, declare `/api/v1` shapes in `ARCHITECTURE.md`, clear every `OPEN` KI touching API or schema | Phases 0-5 | `VERSION.md` gate table; full `gates.sh` |

---

## 11. Execution order and dependency graph

```
Phase 0  S0.4 (noise) -> S0.1 (UI batch) -> S0.2 (gate) -> S0.3 (docs/ADRs) -> S0.6 (light/dark, owner D-13) -> S0.5 (merge+push, owner)
Phase 1  S1.1 (PHP baseline) --+-> S1.2 (browser sweep)
                                +-> S1.3 (citations, independent)
Phase 2  S2.1 (page-header) -> S2.3 (KI-114) -> S2.2 (KI-167) -> S2.4 (KI-127) -> S2.6 (register) -> S2.5 (KI-104, owner)
Phase 3  S3.2 (copy) -> S3.3 (search key) -> S3.1 (Independent Training door, owner ADR) -> S3.4 (maiden lock, owner)
Phase 4  S4.1 -> S4.2 -> S4.3 (owner signatures) -> S4.4 (tag, owner)
Phase 5  S5.1 -> S5.2 -> S5.3 -> S5.4 -> S5.5 -> S5.6 -> S5.7 -> S5.8 -> S5.9
```

Rules that hold across every arrow:

- An arrow is a verification checkpoint. A slice that cannot produce its acceptance evidence stays open and is
  reported, not marked done.
- Nothing in Phase 4 starts before Phase 0's merge and Phase 1's two baselines.
- Owner-only acts (signatures, push, tag) are not simulated by an agent; the slice parks with the exact artifact the
  owner must sign.
- A red gate naming a path you did not touch is contention until proven otherwise. Classify before fixing.

---

## 12. Verification command reference

```bash
# Tree and dependencies
git status --porcelain -uall
git rev-list --left-right --count origin/master...HEAD
git ls-files tools/gates.sh
npm ci --dry-run
composer validate --no-check-publish

# PHP gates
php tools/gate-preflight.php
php artisan test --compact < /dev/null
php artisan test --compact --filter=<SuiteName>
php vendor/bin/pint --test --format agent <explicit paths>
php vendor/bin/phpstan analyse --no-progress --memory-limit=1G <paths>
php tools/lore.php && php tools/lore.php code
python tools/adr-check.py docs/adr
python tools/doc_census.py
python tools/md_fence_check.py <paths>

# Frontend gates
npm run typecheck
npm run build
npm run lint:ui
npm run test:a11y
PLAYWRIGHT_PORT=<port> PLAYWRIGHT_SCRATCH_DB=$PWD/.scratch-uma/<session>/browser-<port>.sqlite \
  npx playwright test --workers=1 --output=.scratch-uma/<session>/artifacts

# Single local evidence command (after S0.2)
bash tools/gates.sh
bash tools/gates.sh --fast
```

**Never:** `pint --dirty` (KI-75), an unqualified browser run (KI-73/80), a relative `PLAYWRIGHT_SCRATCH_DB`,
`migrate:fresh --seed` against the shared dev database without approval, or any commit without named pathspecs.

---

## 13. Research basis and version compatibility

All eight sources were fetched on 2026-11-18. None of the pages prints a publication date; version statements below
are what the pages state at fetch time, and the repo-side version is a measured lockfile/vendor value, not a claim
from documentation.

**PHP floor and Pest.** The Pest installation page says "Pest requires PHP 8.4+".[3] The upgrade guide explains the
apparent conflict: Pest 4 requires PHP 8.3.0, and Pest 5 raises the floor to 8.4.0.[2] This repository pins
`pestphp/pest ^4.7` (locked 4.7.8), whose own requirement is `^8.3`. The declared floor `php ^8.3` is therefore
correct for the installed major, and the 8.4+ page applies to a Pest 5 install. Raising the floor now would exclude
a supported PHP 8.3 runtime for no compatibility gain; revisit only when Pest 5 is adopted.

**Laravel 13.** The 13.x testing documentation treats `php artisan test` as the standard runner and documents the
Pest/PHPUnit entry points the repo already uses.[1] No change is needed to `composer test` or `phpunit.xml` beyond
the gate-script issues.

**Inertia v3.** v3 requires PHP 8.2+ and Laravel 11+,[4] both satisfied by PHP 8.5.8 and Laravel 13.42. Its breaking
changes are: Axios removed, `qs` and `lodash-es` removed as transitive dependencies, `invalid`/`exception` events
renamed to `httpException`/`networkError`, `router.cancel()` replaced by `router.cancelAll()`, the `future` namespace
removed, and the config file to be republished.[4] The repo imports `axios` directly (`resources/js/bootstrap.ts:1`),
uses no `router.cancel` or `future` block, and now carries a published `config/inertia.php` - so every listed
breaking change is either not used or handled. SSR is off by default in that config, which matches the served
application: SSR would need Node 22+ and, for the automatic path, `@inertiajs/vite`.[5] Node 26.13.0 satisfies the
runtime floor, but the app does not opt into SSR, so no SSR service is a release requirement.

**Tailwind v4.** Theme variables must be top-level; `@theme inline` is required when a token references another
variable, otherwise the utility resolves the variable at the wrong scope.[6] The repo's `@theme static` block plus
`[data-theme]` overrides is valid for its current tokens; ADR-0028's future primitive layer must use `@theme inline`
for switchable values.

**Playwright.** The CLI documents `--workers=1` for a single worker, `--output <dir>` for artifact isolation, `--list`
for collection without a run, and `--grep` for title filtering.[7] The repo's config already narrows the packaged
spec; the remaining isolation (port, scratch database) must come from config and global setup, which the
`PLAYWRIGHT_PORT` / `PLAYWRIGHT_SCRATCH_DB` pattern does.

**Focus order.** WCAG 2.2 SC 2.4.3 requires focusable components to receive focus in an order that preserves meaning
and operability.[8] That is the obligation behind BO-001 and KI-167: both are about an SPA visit leaving focus on
`BODY` or failing to restore it after a round trip, not about visual styling. A green axe scan does not cover this
class, which is why the two specs exist.

**What is measured, what is inferred.** Installed versions, counts, file existence, lockfile resolutions and command
outputs in §1 are measured. The two-hour browser estimate and the "annotation-sweep dominates wall clock" claim are
**inferred** from `workers: 1` and the case distribution, never timed at this HEAD. The claim that S2.1's six
failures all die with the tier assertions is **inferred** from the sweep's class analysis; S1.2 must confirm it.

---

## 14. Owner decision register

| ID | Decision | Blocks |
|---|---|---|
| D-1 | Sign ADR-0022, 0023, 0027, 0031, 0032, 0033, 0035, 0036 (drafted from shipped code) | S3.1, S3.2, S3.4, S4.3 |
| D-2 | Authorise Independent Training (no PRD story) or retire the mode | S3.1 |
| D-3 | Merge or rebase the 8/38 divergence; authorise the push | S0.5, S4.4 |
| D-4 | Authorise `npm run build` in the shared tree for the browser baseline | S1.2, S2.x |
| D-5 | Approve the `page-header.spec.ts` tier assertion update | S2.1 |
| D-6 | Rule on the six probe-case retirements (only if still wanted) | none - optional cleanup |
| D-7 | Team-race slots: source package or terminal absence | S3.5 |
| D-8 | Roster/preseason rounds: defer or schedule with an ADR | S3.5 |
| D-9 | `data_sources` backfill vs terminal gap | S5.1 |
| D-10 | FQ-01..FQ-09 rulings for the primitive layer | S5.4 |
| D-11 | OQ-2 / OQ-3 / OQ-6 rulings | S5.3, S5.6 |
| D-12 | Cut `v3.0.0` after gates | S4.4 |
| D-13 | Palette ruling: apply Azure Daybreak / Midnight Circuit, keep signed palettes, or third measured option | S0.6 |

---

## 15. Risks and traps

1. **The tree moves while it is read.** Porcelain changed from 31 to 42 rows inside one earlier session. Re-run
   `git rev-parse HEAD` and `git status --porcelain` before quoting any figure in this document.
2. **`tools/gates.sh` is untracked and has already been deleted once** by a peer's housekeeping. Until S0.2 lands,
   every "the gate is green" claim is one `git clean` from being false.
3. **Redirection characters are blocked by the terminal hook.** `>`, `2>&1`, `2>/dev/null`, `tee` and commit messages
   containing `=>` are refused. Use `< /dev/null` for stdin, write probe scripts to files, and avoid redirects.
4. **`@php` on a Python tool is a false green.** `php tools/md_fence_check.py` prints the source and exits 0. Invoke
   the checker with `python`.
5. **A directory argument once made the fence checker match zero files.** The fixed tool exits 2 on an unreadable
   argument; do not reintroduce an `isfile` filter.
6. **`PLAYWRIGHT_SCRATCH_DB` must be absolute.** A relative path silently targets the wrong database and makes a run
   meaningless.
7. **`public/build` staleness is a hard refusal** at `tests/browser/global-setup.ts:203`. Build before any browser
   run, and never read a token or class from a stale bundle.
8. **A green Pest suite has exited 1** because of a post-summary Playwright websocket teardown. Read the `Tests:`
   line as well as the exit code; the hazard is recorded in `sitrep-2026-11-17-s1-s2-wrap.md` §9.
9. **CRLF can be invisible to git.** `LineEndingGuardTest` is the detector; `.gitattributes` normalises on checkout,
   so `git diff` may be empty while the file is wrong on disk.
10. **A basename is not a path.** `DashboardController.php` lives at `app/Http/Controllers/`, not under `Career/`.
    Resolve every cited path with `git ls-files '*Name.php'` before trusting a parent directory.
11. **`KNOWN-ISSUES.md` is peer-owned while dirty.** Coordinate before editing; the register is append-only and a
    reconciliation must never rewrite or renumber history.
12. **The only complete restore patch carries peer work.** `.scratch-uma/s18c/uncommitted-2026-11-17.patch` is 3.3 MB
    and mixes sessions; there is no clean per-session restore point. Commit small, by concern, now.

---

## 16. Whole-roadmap acceptance

The roadmap is complete when all of the following are true at one commit that is reachable from `origin/master`:

1. `git rev-list --left-right --count origin/master...HEAD` -> `0  0`.
2. `bash tools/gates.sh` exits 0 with every step quoted, including the PHP suite.
3. `npx playwright test --list` reports a stable case count, and the full run at that commit is green or every red is
   filed with a class and an owning slice.
4. `DocCitationParityTest` passes with `DOC_CITATION_BASELINE` still 708.
5. `adr-check.py` reports `0 finding(s)`; every ADR's contract matches its Status.
6. No `OPEN` KI names a shipped screen, `/api/v1`, or a schema surface.
7. `VERSION.md` and `CHANGELOG.md` describe that commit, and `v3.0.0` tags it.
8. An Independent Training run can be created, advanced and read back through the UI.
9. The light/dark decision (D-13) is either applied and green (S0.6) or formally parked with a dated note - never silently reversed.

Anything less is a stated partial, with the unrun gate named - never a document claiming progress in place of code.

---

## Sources

[1] https://laravel.com/docs/13.x/testing — Laravel 13.x - Testing: Getting Started
[2] https://pestphp.com/docs/upgrade-guide — Pest - Upgrade Guide (Pest 4 PHP 8.3; Pest 5 PHP 8.4)
[3] https://pestphp.com/docs/installation — Pest - Installation (PHP 8.4+ current)
[4] https://inertiajs.com/docs/v3/getting-started/upgrade-guide.md — Inertia.js v3 - Upgrade Guide
[5] https://inertiajs.com/docs/v3/advanced/server-side-rendering — Inertia.js v3 - SSR
[6] https://tailwindcss.com/docs/theme — Tailwind CSS - Theme variables
[7] https://playwright.dev/docs/test-cli — Playwright - Command line
[8] https://www.w3.org/WAI/WCAG22/Understanding/focus-order.html — W3C WAI - Understanding SC 2.4.3
