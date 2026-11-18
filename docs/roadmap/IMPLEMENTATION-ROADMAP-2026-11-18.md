# Implementation Roadmap 2026-11-18 (post-3.0.0 re-baseline)

## 0. Scope, assumptions and method

**Assumption on `[TASK]`.** The request named `[TASK]` without a value. This roadmap interprets the task as "the remaining implementation work needed to bring Trainer Desk to a fully functional final product," because the second paragraph asks for a phase/slice roadmap of the whole project, skipping completed work. If a narrower task was intended, only Sections 3-6 need re-scoping; Sections 1-2 and 7 remain valid.

**"Fully functional final product" here means:** the `3.0.0` line is tagged after a clean full-suite and browser-suite baseline; every named capability gap that is still implementable is landed or explicitly closed by an owner ruling; the open defect register contains no `OPEN` row that changes a shipped screen or the `/api/v1` / schema public surfaces; and the remaining non-goals in `PRD.md` §6 stay cut. It does **not** mean adding auth, hosting, Redis, a second database, or a simulation of race outcomes.

**Evidence rule.** Every status in this document was re-measured against the working tree at HEAD `2546689e` on 2026-11-18. Where an authoritative doc still claims a different number, the doc is named as stale in Section 2 and the slice that corrects it is in Phase 4. A claim is the command output; unrun gates are reported as unrun.

**Authority order** (`AGENTS.md` §2): quality bar `GOVERNANCE.md` §CONSTRAINTS C-1..C-9 > gate registry > accepted ADRs > `DESIGN.md` > slice plans. `PRD.md` is product truth; `ARCHITECTURE.md` beats its digest. `docs/research-scratch/SLICE-RECORDS.md` §execution-ledger is the record of what landed; `docs/plan/hermes-execution-plan-2026-11-17.md` pass 2 governs ordering and counts; `docs/roadmap/remaining-work-roadmap-2026-11-17.md` governs the phase map. Both were re-based against trees earlier than HEAD, hence the re-measurement below.

**Plan-first contract** (`AGENTS.md` §2a) applies to every slice: goal, scope, acceptance criteria, ordered TODOs, verification and risks before the first edit; no persistent plan files beyond this roadmap; pause only for new requirements, scope growth, destructive actions or unsettled evidence.

---

## 1. Tooling decision (evaluated, then narrowed)

### 1.1 What is available

| capability | evaluated item | decision |
|---|---|---|
| WorkBuddy agents | `general-purpose`, `Explore` subagents | **Use** for the large planning corpus and parallel read-only verification; one agent per document group, never on a shared dirty tree for writes. |
| WorkBuddy web | official docs fetched this session | **Use** for compatibility verification only (Section 1.3); never as a source of product facts. |
| WorkBuddy skills | browser-use / playwright-browser-automation / frontend-dev available in the host skill list | **Exclude.** The repo owns a session-private Playwright harness (`playwright.config.ts`, `tests/browser/global-setup.ts`); a second browser tool would drift from the project rules. |
| Connectors / MCP | `list_installed_plugins` returned no connected connectors. Local `.mcp.json` declares `laravel-boost`, `playwright`, `filesystem` but they are not bound to this WorkBuddy session. | **Exclude from the plan.** Do not add, install or authorize any connector to implement a slice; the repo's composer/artisan/vendor commands cover the work. |
| Repo tooling | `composer test`, `gates`, `lint`, `analyse`, `docs`, `docs-adr`, `lore`, `lore-code`; `npm run typecheck`, `build`, `lint:ui`, `test:a11y`, `test:browser`; `tools/adr-check.py`, `doc_census.py`, `md_fence_check.py`, `check_untracked.py`, `gate-preflight.php`, `ui-framework-check.mjs` | **Use.** These are the only evidence-producing gates; §9 of `AGENTS.md` makes their output the proof. |
| Browser harness | `PLAYWRIGHT_PORT`, `PLAYWRIGHT_SCRATCH_DB` env overrides; `--workers=1`, `--output`, `-c` per official CLI docs | **Use exactly as the repo prescribes:** one process, session-private DB and port, artifacts inside `.scratch-uma/<session>/`. |

### 1.2 Explicitly excluded tooling

No new Composer or npm package without owner approval (quality bar C-8). No auth, policies, `Auth::user()`, multi-user, MySQL/Postgres, Redis, Livewire, Excel, cloud deploy, CI service, `make` (unavailable, KI-4), or generated-file edits (`composer.lock` by hand, `public/build/`, `vendor/`, `PRODUCT.md`, `docs/design-research/` prototypes). Retired or declined features stay cut (PRD §6 and `AGENTS.md` §17). No new MCP server or connector is installed for this roadmap.

### 1.3 Compatibility checked against official documentation

- **Laravel 13 testing** (`laravel.com/docs/13.x/testing`): feature-first tests, array session/cache in the testing environment, `config:clear` before running, and `php artisan test` pass-through of runner flags all match `phpunit.xml` and `composer test` in this repo. No change needed.
- **Pest 4** (`pestphp.com/docs/installation`): official docs now state PHP 8.4+, but the **installed** `pestphp/pest v4.7.8` declares `"php": "^8.3.0"` (`composer.lock:9400`), as does Laravel `v13.42.0` (`composer.lock:1177`). **Slice 1.1 ruling (2026-11-18): keep `composer.json` at `^8.3`.** The declared floor, both locked requirements and `php -v` 8.5.8 agree; raising the floor would exclude supported PHP 8.3 users for no compatibility gain. This is the recorded reason the roadmap's original premise was not applied. Revisit only when a Pest upgrade actually requires 8.4.
- **Tailwind CSS v4** (`tailwindcss.com/docs/theme`): CSS-first `@theme` and `@theme inline` are the supported token mechanism; theme variables must be top-level and `@theme inline` is required when a token references another variable. The repo uses `@theme static` plus `[data-theme]` overrides in `resources/css/app.css`, which is compatible; ADR-0028's primitive layer must use `@theme inline` for switchable values (Slice 5.4). No `dark:` utilities.
- **Playwright Test CLI** (`playwright.dev/docs/test-cli`): `--workers=1` removes in-run contention, but per-session DB/port isolation must come from config and global setup, not from the CLI. The repo's `PLAYWRIGHT_PORT` / `PLAYWRIGHT_SCRATCH_DB` pattern is the supported approach; `--output <session dir>` should be used alongside it for artifact isolation.
- **Inertia v3**: the official upgrade URL currently returns 404, so no new upstream requirement is asserted here. The tree already bumped `@inertiajs/vue3` to `^3.11.0` in `package.json:33` while the lockfile still pins `2.3.28` (Slice 0.2); `INERTIA_SSR_ENABLED=false` must remain set (KI-76 trap, `AGENTS.md` §18).

---

## 2. Measured current state at HEAD `2546689e`

| Fact | Measured value | Consequence |
|---|---|---|
| Version | `VERSION.md:3` `3.0.0`, pre-release, untagged | Release phase (4.5) is owner-gated. |
| Working tree | `composer.json` modified (new `gates` script at `:67-76`); untracked `composer.json.bak`, `win-unpacked/` (273 MB, contains `trainer-desk.exe`), `.workbuddy-ai/` | Phase 0.4/0.6; no agent may treat the dirty set as its own slice (KI-75). |
| Dependency lock | `package.json:33` `@inertiajs/vue3 ^3.11.0`; `package-lock.json` resolves `2.3.28` | Phase 0.2 blocks all frontend gating. |
| Build wiring | `composer.json` has no `pre-native-build`; `.github/workflows/nativephp-windows.yml:95` still calls it | Phase 0.4. |
| Tree hygiene | `git check-ignore win-unpacked` exits 1; `jev-inspect/` 90 tracked deletions still pending disposition | Phase 0.6 and owner decision D-2/D-3. |
| Register | `KNOWN-ISSUES.md:2-117`: 10 `OPEN` (KI-104, 106, 114, 116, 119, 127, 162, 163, 164, 166); KI-165 now CLOSED with seven fan bands at `config/uma.php:588-594` | Phase 2; do not re-run the old fan-ladder slice. |
| Changelog | `grep -c '^## \[Unreleased\]' CHANGELOG.md` = 2 | Phase 4.3. |
| Gates | Uncommitted `gates` script has bare `pint --test` (KI-75), omits `adr-check.py`, G-60, untracked check, audits; `G-60` scanner pending, `G-61` status cell stale | Phase 1.4. |
| Browser baseline | No quiet-box full-suite result at HEAD; `full-career-journey.spec.ts:177-193` was already rewritten (the older two-details locator is gone) | Phase 1.6 re-measures; `VERSION.md:9`'s "single red spec" claim is stale. |
| Capability gaps | No `team_rank` in `app/Http/Requests`; no `unity_cup` finale key in `config/scenarios.php`; `match_candidates` has no entity discriminator; 304 Pending race rows | Phase 3.1-3.5, 5.1-5.2. |
| ADR state | 0022/0023/0027 Proposed, 0028 contract `proposed`/`not built` vs Status Accepted, 0019 Advisory vs contract proposed, 0030 contract `pending` but migration `b63bee59` landed | Phase 4.4 (owner signatures) and 3.10. |
| Test inventory | 262 Feature, 13 Unit, 125 browser specs; 62 Vue pages, 119 components | Docs that quote older counts are stale (Phase 4.2). |
| Completed at HEAD | S18 landing, BO-001..BO-017 fixes, KI-141/143/152/159/160, fan ladder, fence check `2546689e`, Inertia v3 bump, 7 fan bands | Excluded from this roadmap (Section 7). |

---

## 3. Phase 0 - Tree, dependency and build truth (blocking everything)

No phase 1+ slice may start until 0.1, 0.2, 0.4 and 0.6 are green. Owner decisions D-1..D-4 gate their first edit.

### Slice 0.1 - Land the worktree product content
- **Objective:** put the S18 batch, the peer refactor (`DashboardController.php`, `Umamusume.php`, `RaceStripBuilder.php`) and the five new tests onto `master` as a reviewed series.
- **Deliverables:** one commit per coherent concern; `origin/master` reachable from the new commits; no unrelated file in any commit.
- **Dependencies:** owner decision D-1 (one series or split); isolated worktree at the frozen commit under `.scratch-uma/worktrees/`.
- **Acceptance:** `git diff --stat origin/master HEAD -- app resources config routes database tests` contains only agreed paths; `npm ci --dry-run` exits 0 on a clean clone of the resulting commit.
- **Verification:** `git log --oneline origin/master..HEAD`; `npm ci --dry-run`; `npm run build`.

### Slice 0.2 - Synchronise the Inertia lockfile
- **Objective:** make `package-lock.json` resolve the declared `@inertiajs/vue3 ^3.11.0`.
- **Deliverables:** regenerated lockfile shipping with the dependency change, never alone.
- **Dependencies:** 0.1 frozen tree; owner approval only if the resolved version introduces a breaking change.
- **Acceptance:** `node -e` read of the lockfile reports `3.11.x`; `npm run typecheck` and `npm run build` clean.
- **Verification:** `npm install`; `npm run typecheck`; `npm run build`; `npm audit --omit=dev`.

### Slice 0.3 - Build inputs (already landed; record only)
- `f4e7adc6` added the stat-visual fixtures and build inputs. **Exclude.** Re-verify with `npm run build` during 0.2; no new edit.

### Slice 0.4 - Restore `pre-native-build`
- **Objective:** make the Windows workflow's `composer run pre-native-build --no-interaction` resolvable again.
- **Deliverables:** either the script restored in `composer.json` with the exact commands the workflow expects, or an owner-signed amendment to `.github/workflows/nativephp-windows.yml` that removes the call.
- **Dependencies:** owner decision D-4; do not guess the script body.
- **Acceptance:** `composer run pre-native-build --no-interaction --list` does not fail with "command not defined" (or the workflow no longer calls it); workflow file and composer script agree.
- **Verification:** `php -r '$j=json_decode(file_get_contents("composer.json"),true); echo isset($j["scripts"]["pre-native-build"])?"present":"absent";'`; read the workflow step at `:95`.

### Slice 0.5 - Deletions disposition (owner-gated)
- **Objective:** decide the 90 `jev-inspect/` tracked deletions (restore or accept with a written reason).
- **Deliverables:** a commit implementing the ruling plus a dated note in the execution ledger; no silent restoration.
- **Dependencies:** owner decision D-2.
- **Acceptance:** `git status --short` has no unexplained deletion; ledger records the reason.

### Slice 0.6 - Repository hygiene
- **Objective:** stop generated packaging output and local backups from entering the index.
- **Deliverables:** `.gitignore` entry for `win-unpacked/`; `composer.json.bak` removed from the working tree after its diff is confirmed as the uncommitted `gates` script only; `.workbuddy-ai/` kept out of commits (agent memory, not product content).
- **Dependencies:** owner decision D-3; confirm the 273 MB directory is build output before removing it.
- **Acceptance:** `git check-ignore -q win-unpacked` exits 0; `git status --short` shows only the intended `composer.json` change and no untracked build artifacts.
- **Verification:** `git status --short`; `git check-ignore -v win-unpacked`.

---

## 4. Phase 1 - Gate and test truth

### Slice 1.1 - Declared PHP floor
- **Objective:** reconcile `composer.json` `"php": "^8.3"` with Pest 4's documented PHP 8.4+ requirement (runtime 8.5.8).
- **Deliverables:** updated constraint plus `composer.lock` content hash, or an owner ruling that the declared floor stays wider with the reason recorded.
- **Acceptance:** `composer validate --no-check-publish` clean; lockfile ships with the constraint change.
- **Verification:** `composer validate --no-check-publish`; `php -v`; `php artisan test --compact --filter=DocCitationParityTest`.

### Slice 1.2 - Separate the packaged-app browser cases
- **Objective:** keep `nativephp-packaged` cases out of the default browser run while retaining them in the acceptance workflow.
- **Deliverables:** `testIgnore` or an equivalent config partition in `playwright.config.ts`; a documented command that still runs them.
- **Acceptance:** default `npm run test:browser` excludes them; acceptance workflow includes them.
- **Verification:** `npx playwright test --list` (packaged cases absent); acceptance workflow file read.

### Slice 1.3 - Retire or rewrite stale probe specs
- **Objective:** decide the 35-case probe group (six candidates for retirement), including `career-preflight-indep.spec.ts` and `annotation-sweep.spec.ts`.
- **Deliverables:** per-file ruling; rewrite where the case still names a real regression class, retirement only with owner approval and a reason in the commit (Floor rule).
- **Dependencies:** owner decision D-5.
- **Acceptance:** no deleted or skipped test without approval and reason; each retained case has a named behaviour it protects.
- **Verification:** `npx playwright test --list`; diff review of removed assertions.

### Slice 1.4 - One honest gate command
- **Objective:** finish the uncommitted `composer gates` script so it is the single local evidence command.
- **Deliverables:** explicit-path `pint --test --format agent <paths>` (KI-75), plus `tools/adr-check.py` (G-61), `tools/check_untracked.py`, `tools/doc_census.py`, `composer audit`, `npm audit --omit=dev`, and the existing eight steps; documentation in `README.md` §Testing & Quality Gates.
- **Dependencies:** S-1.5 landed at HEAD; G-60 scanner decision is **not** required for this slice (record it as partial).
- **Acceptance:** `composer gates` runs end to end and every step's output is quotable; no step silently checks zero files.
- **Verification:** `composer gates`; `composer validate`; `python tools/md_fence_check.py docs`.

### Slice 1.5 - Markdown fence check (already landed; record only)
- `2546689e` fixed the false-green step. **Exclude.** Use bare `python tools/md_fence_check.py <paths>`; never the `"@php ..."` form recorded in the old plan.

### Slice 1.6 - Quiet-box full baseline
- **Objective:** establish the HEAD baseline the release bar needs and classify KI-162.
- **Deliverables:** one full Pest run and one full browser run with recorded counts, exit codes, and environment readings; a dated KI-162 verdict (reproduces / does not reproduce / infra).
- **Dependencies:** 0.1, 0.2, 1.2, 1.4; quiet-box pre-flight `tools/gate-preflight.php`; session-private DB/port and `.scratch-uma/<session>/` output.
- **Acceptance:** zero unexplained reds; any failure classified per `AGENTS.md` §9 before a re-run; no claim of a pass without output.
- **Verification:** `php artisan test --compact < /dev/null`; `php tools/gate-preflight.php`; `PLAYWRIGHT_PORT=<port> PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-<port>.sqlite npx playwright test --workers=1 --output=.scratch-uma/<session>/artifacts`.

---

## 5. Phase 2 - Named open defects (implementation, not register edits)

### Slice 2.1 - KI-166 mobile overflow on the Races page
- **Objective:** find the real cause of the 59px overflow at the 320px reflow width; the earlier direct-child combinator hypothesis was refuted in the audit.
- **Deliverables:** the corrected probe (the old `culprits: []` filter excluded self-scrolling ancestors), the minimal CSS/markup fix, and a regression case at 320/768/1280.
- **Dependencies:** 0.1; do not touch the already-resolved Cockpit half of KI-160.
- **Acceptance:** measured `over=0` at 320px; the Races and planner strips remain green.
- **Verification:** session-private Playwright on the races spec with `--grep` for the reflow case; `npm run lint:ui` (no new arbitrary value).

### Slice 2.2 - KI-163 shared goal shapes
- **Objective:** remove the four duplicate `GoalRow`/`GoalGroup` interfaces from `GoalGroupCard.vue`, `ScenarioCardGrid.vue`, `UnityCupPanel.vue`, `CareerGoalsPanel.vue`.
- **Deliverables:** one shared type in `resources/js/types.ts` (or a dedicated types module) imported by all four; no `as never` escape hatch added.
- **Acceptance:** `npm run typecheck` clean; rendered goal cards unchanged.
- **Verification:** `npm run typecheck`; focused goal/cockpit browser specs.

### Slice 2.3 - KI-164 keyed review assertions
- **Objective:** replace positional `fields.N` checks in `tests/Feature/SnapshotReviewTest.php` with a keyed `field()` helper.
- **Deliverables:** helper plus re-pointed assertions; a case proving a new payload row does not invalidate unrelated checks.
- **Acceptance:** adding a payload field does not require editing assertions for other fields.
- **Verification:** `php artisan test --compact --filter=SnapshotReviewTest`.

### Slice 2.4 - ADR-0027 signature and correction writer
- **Objective:** obtain the owner's acceptance of ADR-0027 and decide who writes `corrected_from` / `superseded_by` and from which screen.
- **Deliverables:** signed ADR (status text + index regeneration), then the writer action and its UI door if the ruling asks for one.
- **Dependencies:** owner signature first; no code may invent the missing writer.
- **Acceptance:** `tools/adr-check.py docs/adr` reports 0 findings; a test proves a correction row is written only by the ruled path.
- **Verification:** `python tools/adr-check.py docs/adr`; focused `TurnChoiceTest` / `NextDecisionTest`.

### Slice 2.5 - KI-114 browser cases on a retired picker
- **Objective:** re-measure the two browser specs named in KI-114 against the current deck-picker surface; rewrite or close with a dated ruling.
- **Dependencies:** 1.6 baseline.
- **Acceptance:** no spec drives a control the shipped screen no longer renders.
- **Verification:** session-private Playwright for the two files.

### Slice 2.6 - KI-106 citation re-measure
- **Objective:** re-run the doc-citation parity check against the current baseline and record the figure.
- **Deliverables:** updated KI row with command output and either a clean result or a queued correction slice.
- **Acceptance:** figure quoted with commit and date; baseline not raised without a written reason.
- **Verification:** `php artisan test --compact --filter=DocCitationParityTest`; `python tools/doc_census.py`.
- **Measured 2026-11-18 at HEAD `2546689e`:** `php artisan test --compact --filter=DocCitationParityTest` = **1 failed / 1 passed**, `Failed asserting that 808 is equal to 708 or is less than 708` (`tests/Feature/DocCitationParityTest.php:56`); `python tools/doc_census.py --working-tree` = `dead markdown links: 808 (761 GONE, 47 UNTRACKED)`. The gate measures the committed tree, so the new files in this slice's working tree cannot have caused the rise. `DOC_CITATION_BASELINE` stays at 708: lowering rot is a queued correction slice, raising the bar to pass is a quality-bar violation (C-8/KI-106). This roadmap document itself adds 0 dead citations (checked with the census resolver).

---

## 6. Phase 3 - Remaining product capability (the functional gap)

These slices add user-visible behaviour or data. Every one needs a failing Pest or browser case before code, per `AGENTS.md` §9.

### Slice 3.1 - Team Rank writer
- **Objective:** let a Trainer record and read back a team rank on the run, with the published letter set as the only allowed values.
- **Concrete changes:** a Form Request using `Rule::in(TeamRankPayload::LETTERS)`, the run-update action, the team editor control, and removal of the stale sentence at `docs/research-scratch/PLANS-AND-BRIEFS.md`-tracked copy site (verify exact file before edit); an `S+` round-trip case.
- **Dependencies:** 0.1; no new enum casing outside `app/Enums/`.
- **Acceptance:** `S+` survives write and reload on both surfaces; invalid letters are rejected by the Form Request; no inline `$request->validate()`.
- **Verification:** focused Feature test; team-editor browser spec.
- **BLOCKED 2026-11-18 — implementation attempted and reverted.** A Form Request rule plus an editor select was written and its two new tests passed in isolation, but the full suite exposed `tests/Feature/UnityCupSurfacesTest.php:297-305`, which pins owner ruling R2 (2026-10-25): the editor must say Team Rank is recorded through a turn's team-race event and must have **no** `team_rank` input. The later R6-48 ruling (2026-11-15, `docs/audits/browser-observation-2026-11-15-followup/08-r6-48-review-prep.md:57-68`) says the turn-event path stays for the `UnityCupEditor` write route while the column is the stored current state. Per `AGENTS.md` §2 (owner > slice plan) and the Floor (no deleting a test to pass a change), the conflicting edit was reverted and the test kept. This slice needs an owner resolution of the R2/R6-48 tension before any Trainer-facing rank writer is added; it is removed from the executable sequence.

### Slice 3.2 - Unity Cup finale key
- **Objective:** add a `unity_cup` finale key only if a Global client string is on record.
- **Deliverables:** config entry plus its source citation, or a terminal note that no authoritative string exists (D-rule: never invent a client string).
- **Acceptance:** no unsourced label reaches the UI.
- **Verification:** `php artisan config:show uma` / scenario config read; lore gate on any new copy.

### Slice 3.3 - Team-race slots
- **Objective:** resolve whether team-race slot facts are sourced or a terminal absence.
- **Dependencies:** owner decision D-11 (source or terminal ruling); **blocked** until ruled.
- **Acceptance:** either a migration + parser + test with provenance, or a dated terminal note in `KNOWN-ISSUES.md`; no null-rendered placeholder that implies a value.
- **Verification:** `php artisan migrate:status --no-ansi` on a scratch DB if a migration lands.

### Slice 3.4 - Roster and preseason rounds
- **Objective:** close the roster half (new sub-model + ADR) and decide whether preseason rows get a per-row table.
- **Dependencies:** owner decisions D-12 and the R8 ruling; keep `DEFERRED` until ruled; do not widen PRD §6.
- **Acceptance:** ADR status and schema agree; `migrate:fresh --seed` succeeds on a scratch DB with a working `down()`.
- **Verification:** scratch-DB migrate + seed; `php artisan test --compact --filter=<new suite>`.

### Slice 3.5 - Per-ancestor spark and guest fields
- **Objective:** record per-ancestor `guest` / `label` and spark grades instead of one affinity grade.
- **Deliverables:** ADR proposing the typed JSON payload change to `training_runs.legacy_selection` (D-268); migration with `down()`; `LegacySelectionPayload` accessors; a form with validation; one feature test.
- **Dependencies:** owner ADR acceptance. The current payload is the known ceiling; do not widen without the ADR.
- **Acceptance:** round-trip of four ancestor links; unrecorded values render `N/A` with a disclosure, never a default.
- **Verification:** `php artisan test --compact --filter=Legacy`; `php artisan migrate:status --no-ansi`.

### Slice 3.6 - Independent Training evaluator shape
- **Objective:** close the evidence gap that blocks the Unity Training evaluator: input, output, validation and calibration shape.
- **Deliverables:** a source-cited shape brief in `docs/research-scratch/`, then only the code the ruling authorises.
- **Dependencies:** owner evidence ruling (`PLANS-AND-BRIEFS.md` evidence-blocked register).
- **Acceptance:** no prediction of race outcomes; `Simulator.php` remains a stat/fans calculator, not an outcome model.
- **Verification:** feature test on the calculator; lore and non-goal review.

### Slice 3.7 - Grand Concert evidence packages
- **Objective:** supply the missing client evidence for Lessons, Songs, Live Bonus and Promo Concert (S9-S18), which are blocked, not partially built.
- **Delivers:** source-cited packages with date and confidence; each then becomes its own code slice.
- **Dependencies:** owner acceptance of evidence; schema ADR for S16.
- **Acceptance:** no `❌ UNVERIFIED` item promoted into UI copy (D-20).
- **Verification:** `composer lore`; `composer lore-code`; focused catalog/scenario tests.

### Slice 3.8 - Trackblazer and URA Finale residual verification
- **Objective:** verify the six unconfirmed Trackblazer items (skip consequences, four risk severities, projected inflow, lost-race greying, scenario reconciliation) and the four URA Finale items (facility levels, stamina-floor shape, run-at-risk flag, duel icons deliberately out).
- **Deliverables:** per-item verified / terminal ruling; code change only where the ruling says the shipped behaviour is wrong.
- **Acceptance:** each item carries a source citation or a dated "terminal by ruling" line.
- **Verification:** focused browser walkthrough; `php artisan test --compact --filter=<scenario>`.

### Slice 3.9 - ADR-0030 reference data population
- **Objective:** populate `base_stats` / `growth_rates` from a fresh fetch of the ruled source.
- **Deliverables:** re-fetched rows with provenance; contract line in ADR-0030 corrected from `code: pending` to the measured state; no hand-edited data.
- **Dependencies:** owner approval to apply against the shared dev DB; use `uma:reparse <source>` where a snapshot already exists (KI-27).
- **Acceptance:** every engine-written row has a `data_sources` entry; `is_manual` rows untouched.
- **Verification:** `php artisan uma:fetch --help`; catalogue-detail feature test; `php artisan migrate:status --no-ansi`.

### Slice 3.10 - ADR-0029 residual facts
- **Objective:** add `SupportCardEffects::forLevel()` (A-0.4) and settle A-0.12.
- **Dependencies:** owner ruling on A-0.12.
- **Acceptance:** per-level effect reads are pure, tested, and do not invent values for unrecorded levels.
- **Verification:** `php artisan test --compact --filter=SupportCardEffects`; `python tools/adr-check.py docs/adr`.

---

## 7. Phase 4 - Document and release truth (after Phase 3 code lands)

### Slice 4.1 - Register and contradiction closeout
- **Objective:** close the six satisfied KIs, six contradictions and six duplicate blocks named in `remaining-work-roadmap-2026-11-17.md` §4.1, using the dated index at `KNOWN-ISSUES.md:2-117` as the status authority.
- **Deliverables:** one-line rulings per hit; no renumbering; dated errata for wrong dated claims.
- **Acceptance:** contradiction scan reports zero; every `OPEN` row has a reason and an owning slice.
- **Verification:** `python tools/adr-check.py docs/adr`; `python tools/doc_census.py`; `git grep -n '^KI-.*OPEN' KNOWN-ISSUES.md`.

### Slice 4.2 - VERSION and README re-baseline
- **Objective:** replace stale `VERSION.md:9` blocker claims, stale page/test counts and version labels (`README.md:15` `2.1.0`, `PRD.md:5` `2.0.0`, `ARCHITECTURE.md:585` Inertia 2.3, `AGENTS.md:17` 66 pages) with measured values.
- **Deliverables:** exact figures with commands and dates; `PRODUCT.md` regenerated, never hand-edited.
- **Acceptance:** every count in a root doc is re-derivable from a command in the same row.
- **Verification:** `find` counts for pages/tests; `git grep -n '2\.0\.0\|2\.1\.0\|Inertia 2\.3\|66 pages' -- '*.md'`.

### Slice 4.3 - Single Unreleased heading
- **Objective:** merge the two `## [Unreleased]` blocks in `CHANGELOG.md`.
- **Acceptance:** `grep -c '^## \[Unreleased\]' CHANGELOG.md` = 1; no entry lost; Keep a Changelog order preserved.
- **Verification:** the grep above plus diff review.

### Slice 4.4 - ADR contract/status alignment
- **Objective:** resolve the contradictions measured in Section 2: 0028, 0019, 0030, and owner signatures for 0022/0023/0027.
- **Deliverables:** owner rulings recorded; ADR index regenerated with `tools/adr-index.py`; dated errata, never silent rewrites.
- **Acceptance:** `python tools/adr-check.py docs/adr` = `0 finding(s)`; every ADR's contract and Status line agree.
- **Verification:** the ADR check plus `git diff -- docs/adr/README.md`.

### Slice 4.5 - Tag `v3.0.0` (owner only)
- **Objective:** cut the tag once Phases 0-4 are green.
- **Deliverables:** annotated tag on an `origin/master`-reachable commit; `VERSION.md` Release commit and tag rows updated in the same change.
- **Dependencies:** owner decision D-13; 1.6 baseline green; no `OPEN` KI touching API or schema.
- **Acceptance:** `git tag -l 'v3*'` contains `v3.0.0`; `git merge-base --is-ancestor v3.0.0 origin/master` exits 0.
- **Verification:** the two git commands; quoted output of every gate in `composer gates`.

---

## 8. Phase 5 - After the tag: bounded completion toward 1.0

These slices are real implementation work, not reports. They are ordered by public-surface risk: data truth first, then OQ closure, then visual-system debt, then the 1.0 gate.

| Slice | Objective and tangible deliverable | Dependencies | Verification |
|---|---|---|---|
| 5.1 | Backfill `data_sources` provenance (one `source_key` currently covers 14 datasets; the audit names 133 rows). Deliverable: a reparse/backfill action with a provenance test; no fabricated fetch timestamps. | 4.5 | `php artisan test --compact --filter=Provenance`; `uma:reparse <source>` |
| 5.2 | Add an `entity`/`kind` discriminator to `match_candidates` and a race review queue; 304 Pending race rows cannot be disposed of on a trainee-shaped form. Deliverable: migration + ADR + queue screen + feature test. | owner ADR; 4.5 | scratch migrate + seed; review feature test |
| 5.3 | Close OQ-2 (robots/live-availability and refresh policy) and OQ-3 (scheduler vs manual). Deliverable: recorded policy in `PRD.md` and `config/uma.php`, scheduler only if owner rules it in. | owner ruling | `php artisan config:show uma.sources`; lore/fetch legality review |
| 5.4 | ADR-0028 primitive layer: answer FQ-01..FQ-09, write the parity document under docs/proposals/ (absent at HEAD, so no backticked path is added to the citation census), build named primitives with `@theme inline`, then ratchet down the 207 arbitrary values. Deliverable: token map, primitives, parity doc, UI lint count lower than 209. | owner FQ rulings | `npm run lint:ui`; `npm run typecheck`; rendered audit |
| 5.5 | Close the ADRs whose code is still missing: ADR-0002 two markers, ADR-0004 FK + scenario repoint, ADR-0010 legacy UI + fixity guard, ADR-0011 hint import, ADR-0018 `PageSize` consolidation, ADR-0007 manual refresh. Deliverable: one focused slice per ADR with tests. | respective owner rulings where named | focused Feature tests; `python tools/adr-check.py docs/adr` |
| 5.6 | Artwork coverage (OQ-6): skill-icon column and pre-run pick frame from allowlisted host only, no user uploads. Deliverable: migration if needed, `uma:fetch-art` extension, and a frame on the ruled screens. | owner OQ-6 ruling | `php artisan uma:fetch-art --dry-run`; artwork mirror test |
| 5.7 | Performance budgets (NFR-3/C-6): measure catalogue index and cockpit reads on a quiet box, then pin query-count ceilings. Deliverable: benchmark output and a regression test for the query budget. | 1.6 quiet box | manual benchmark; `php artisan test --compact --filter=Performance` |
| 5.8 | Ten MWG accessibilty remediations (IME-safe Enter, `<search>` landmark, Popover tooltips, LCP images, dialog overscroll, `BaseInput` mobile props, `<fieldset>`, `closedby`, `content-visibility`). Deliverable: one slice per item with an axe case. MWG #5 is excluded by ruling. | owner approval (audits say "awaiting approval") | `npm run test:a11y`; axe spec |
| 5.9 | Gate 1.0.0: close the four open/partial PRD questions, declare `/api/v1` response shapes in `ARCHITECTURE.md`, and clear every `OPEN` KI touching API or schema. Deliverable: a version-label change with the three conditions quoted. | Phases 0-5 | `VERSION.md` gate table; full `composer gates` output |

---

## 9. Explicitly excluded (do not re-implement)

- **Already landed at HEAD:** S1 snapshot pipeline, S18-01..07/10, BO-001..BO-017, KI-141/143/152/159/160/165, fence check `2546689e`, Inertia v3 bump `f82b6bd0`, seven fan bands `90e18ac1`/`58c4f71b`, peer `c3b45049` dark-token lift, all sitrep-era commits (`be8893a2`, `50e31d14`, `27800fa8`, etc.).
- **Terminal or ruled out:** PRD §6 non-goals (race-outcome prediction, user image uploads, dual storage, simulation of outcomes); ADR-0005 collection half; ADR-0001 numeric failure estimate; KI-100's 22 undecoded objective rows (disclosed, not inferred); KI-124/KI-132/KI-150; EX-02/03/04/05/06/07/08/09/10/11/12a/12b/13/14/15/16 from the audit corpus; GP-08; the "Neon Glow" dark pass; F-13 planned spend; MWG #5.
- **Retired specs:** the `Actions` assertion in `career-race-strip.spec.ts` and `_screens-indep-training.spec.ts` are to be retired, not repaired.
- **Not to be started before the tag:** Phase 5 slices, the old `plan-defects` slice 3.2 fan array (the peer's own-threshold mapping won), and any new source fetch without a robots/rate-limit note (`config/uma.php` allowlist only).

---

## 10. Owner decision register (blocking slices)

| ID | Decision needed | Blocks |
|---|---|---|
| D-1 | Land worktree as one series or split S18 from peer refactor | 0.1, all later phases |
| D-2 | Restore or accept 90 `jev-inspect/` deletions | 0.5 |
| D-3 | Ignore the 273 MB `win-unpacked/` directory (and confirm it is build output) | 0.6 |
| D-4 | Restore `pre-native-build` or amend the Windows workflow | 0.4 |
| D-5 | Ruling per probe spec; six candidates for retirement | 1.3 |
| D-6 | Re-measure BO-004 dark risk token contrast (reported landed at `c3b45049`) | 1.6 confirmation |
| D-7 | Per-run `primary_skill` mapping | Phase 3 scope |
| D-8 | Entity discriminator for 304 Pending race rows in `match_candidates` | 5.2 |
| D-9 | `data_sources` backfill vs terminal-gap ruling | 5.1 |
| D-10 | Fan ladder's two non-numeric tiers | **Moot** (seven bands landed) - close with a note |
| D-11 | Team-race slots: source or terminal absence | 3.3 |
| D-12 | Roster/preseason rounds: keep deferred or schedule with ADR | 3.4 |
| D-13 | Cut `v3.0.0` after gates | 4.5 |
| OQ-2/3/5/6 | Source legality/refresh, scheduler, running-style eligibility, artwork families | 5.3, 5.6, Phase 3 scope |
| R1-R8, FQ-01..09 | Rulings owed in `PLANS-AND-BRIEFS.md` | 3.6, 3.7, 4.4, 5.4, 5.5 |

---

## 11. Verification command reference

```bash
# Tree and dependencies
git status --short
git check-ignore -v win-unpacked
npm ci --dry-run
composer validate --no-check-publish
composer audit && npm audit --omit=dev

# PHP gates
php artisan test --compact < /dev/null
php artisan test --compact --filter=<SuiteName>
vendor/bin/pint --test --format agent <explicit paths>
vendor/bin/phpstan analyse --no-progress --memory-limit=1G
php tools/lore.php && php tools/lore.php code
python tools/adr-check.py docs/adr
python tools/doc_census.py
python tools/md_fence_check.py <paths>
php artisan migrate:status --no-ansi            # shared dev DB, read only
php tools/gate-preflight.php                    # quiet-box precondition

# Frontend gates
npm run typecheck
npm run build
npm run lint:ui
PLAYWRIGHT_PORT=<port> PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-<port>.sqlite \
  npx playwright test --workers=1 --output=.scratch-uma/<session>/artifacts
npm run test:a11y

# Single local evidence command (after slice 1.4)
composer gates
```

**Never:** `pint --dirty` (KI-75), unqualified browser runs (KI-73/80), `migrate:fresh --seed` against the shared dev file without approval, network-touching tests, or writing outside this repository (including worktrees, test output and backups).

---

## 12. Slice execution order (one line)

`0.1 → 0.2 → 0.4 → 0.6 → (0.5 when ruled) → 1.1 → 1.2 → 1.4 → 1.6 → 2.1 → 2.2 → 2.3 → 2.5 → 2.6 → (2.4 when signed) → 3.1 → 3.2 → 3.9 → 3.10 → (3.3/3.4/3.5/3.6/3.7/3.8 when ruled and evidenced) → 4.1 → 4.2 → 4.3 → 4.4 → 4.5 → 5.1 → 5.2 → 5.3 → 5.4 → 5.5 → 5.6 → 5.7 → 5.8 → 5.9`

Every arrow is a verification checkpoint, not just an ordering hint. A slice that cannot produce its acceptance evidence stays open and is reported, not marked done.

## 13. Execution record 2026-11-18 (this session)

**Landed and verified:** 0.2 (`package-lock.json` now resolves `@inertiajs/vue3` 3.11.0; `npm run typecheck` and `npm run build` clean); 0.4 (exact `pre-native-build` body restored from `git show 6634925c^:composer.json`, `composer run pre-native-build` reaches the guard); 0.6 (`.gitignore` now ignores `win-unpacked/`, `*.json.bak`, `.workbuddy-ai/`); 1.1 (evidence ruling in §1.3: keep `php ^8.3` because the locked Pest 4.7.8 and Laravel 13.42 both require ^8.3); 1.2 (`playwright.config.ts` `testIgnore` for `nativephp-packaged.spec.ts`; absent from the default list, present in the dedicated config); 2.2 (`GoalRow`/`GoalGroup` now one shared type in `resources/js/types.ts`, four local aliases; `vue-tsgo` + `tsc --noEmit` clean); 2.3 (`SnapshotReviewTest` keyed `reviewField()` helper plus a new-row independence case; 11 passed); 3.9 (ADR-0030 contract line corrected to measured `code: built`); 3.2 (terminal note in `docs/proposals/Unity-Cup-plan.md` §14 item 12); 4.2 (version/count claims in README, PRD, ARCHITECTURE, AGENTS re-baselined to 3.0.0 / 62 pages / 119 components); 4.3 (two `[Unreleased]` blocks merged into one, every entry preserved, `grep -c` = 1).

**Verification evidence:** focused `php artisan test --compact --filter='SnapshotReviewTest|UnityCupSurfacesTest|TeamEditTest'` = 26 passed / 323 assertions; `vendor/bin/pint --test --format agent <changed PHP files>` = `passed`; `vendor/bin/phpstan analyse --no-progress --memory-limit=1G app/Http/Requests/StoreTrainingRunRequest.php` = `[OK] No errors`; `python tools/md_fence_check.py <8 changed docs>` = 0 invalid fences; `python tools/adr-check.py docs/adr` = 2 pre-existing word-cap findings (ADR-0026, ADR-0029), neither from this session.

**Full suite result:** one run before the slice 3.1 revert read 2467 passed / 4 failed. Two failures were the reverted `TeamRankWriterTest`; `UnityCupSurfacesTest` passed again in the focused run after the revert. The 2 remaining failures are **pre-existing or peer-owned, not from this session's edits**: `DocCitationParityTest` (808 vs 708, recorded at slice 2.6) and `LineEndingGuardTest` (`tests/browser/career-trainee-select.spec.ts`, `tests/browser/wizard-flow.spec.ts` carry CRLF in the working tree while HEAD blobs are LF; both outside this session's diff). A post-revert full suite was not re-run; no pass is claimed for it.

**Blocked, with reasons:** 0.1 (needs owner D-1 and a push, not performed); 0.5 (owner D-2); 1.3 (owner D-5); 1.4 (a peer owns the `gates` script and `tools/gates.sh` in the shared tree; not overwritten); 1.6 (full baseline not green because of the two failures above); 2.1 (needs a browser probe and a session-private Playwright run); 2.4 (owner signature for ADR-0027); 2.6 (baseline not raised, queued correction slice); 3.1 (see the slice's BLOCKED note); 3.3-3.8 and Phase 4.5/5.x (owner rulings, evidence packages, or post-tag per the roadmap's own dependency order).
