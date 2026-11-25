# UI/UX Application Context — Technical Assessment

**Repository:** `D:\Projects\umamusume-laravel13` (Trainer Desk, Laravel 13, Umamusume Global EN)
**Head:** `e04a6db0` `docs(screen-spec): date the database-area claim reconciliations` (2026-11-20)
**Baseline compared against:** brief §17 at HEAD `f6845083` (2026-11-13), 226 commits behind current HEAD
**Working tree:** dirty — 18 modified tracked files, 25 untracked files (mostly image/theme assets plus `ki_entries.json` and `agent.json`)
**Constraint honoured:** read-only assessment; no repo files edited.

---

## 1. State snapshot (measured at `e04a6db0`)

| Measure | Now (§17 baseline) | Δ |
|---|---|---|
| Vue pages | 62 (65) | −3 |
| Vue components | 119 (117; 26 top-level) | +2 |
| Layouts | 3 | 0 |
| `components/scenario/` | 23 | 0 |
| Feature test files | 267 (249) | +18 |
| Unit test files | 13 (11) | +2 |
| Browser specs | 123 (120) | +3 |
| Named web routes | 95 (91) | +4 |
| API routes | 6 | 0 |
| `config/scenarios.php` | 1396 lines (1302) | +94 |
| ADRs on disk | 0037 latest (0030) | +7 (AGENTS.md §2 row stale at 0025) |
| KNOWN-ISSUES.md | through KI-168 (160) | +8 |
| Lore gate (last measured `f6845083`) | 583 hits / 75 exempt docs; 319 code | unverified since |
| `origin/master` vs HEAD | synced (0 behind/ahead) | — |

Dirty tracked files (mid-slice, not yet through gates): `resources/css/app.css`; `resources/js/components/catalog/TraineeCatalog.vue`; `resources/js/pages/Career/{BuildTarget,EventDecision,TraineeProfile,TraineeSelect}.vue`; `resources/js/pages/Database/{Scenarios,Sources}.vue`; `resources/js/pages/{Help,Review/Index}.vue`; `tests/Feature/ReviewQueueTest.php`; six browser specs (`career-build-target`, `career-event-decision`, `career-profile`, `career-trainee-select`, `catalog`, `database-parity`, `database`, `help`). Untracked `ki_entries.json` is the register auditor's data dump; `agent.json` is an off-topic nano-agent config probably dropped in the root by mistake.

---

## 2. Architecture shifts since §17 (highest signal first)

**A. NativePHP desktop packaging is now real (compat break with AGENTS.md §1).**
- `composer.json` requires `nativephp/desktop: 2.3`, `nativephp/php-bin: 1.2`; PHP floor raised to `^8.4.1` (`37320f35`); `config/nativephp.php` exists.
- Three GitHub workflows: `nativephp-windows.yml` (build NSIS installer, PHP 8.5 + Node 24), `nativephp-windows-acceptance.yml` (Playwright against the installed `.exe` under `LOCALAPPDATA\trainer-desk`, diagnostics upload), `release.yml` (SHA-256, upload installer).
- AGENTS.md §1 still claims "No CI" and §2 boost block lists no cloud/deploy — **stale**. The tree now has CI; AGENTS.md §2 precedence table needs correcting (quality bar > gate registry still holds).

**B. Release gate `composer gates` → `tools/gates.sh` now exists.**
Steps in order: lore docs, lore code, pint (changed PHP files only), phpstan (whole-project scope; scoped runs invent Pest `TestCall` errors — documented in `.scratch-uma/hermes-audit-20261118/phpstan-scope-finding.md`), typecheck, UI framework check, md fences, citation census (`tools/doc_census.py`), untracked sweep (`tools/check_untracked.py`), backdrop plates (`tools/verify-backdrop.py`), ADR set (`tools/adr-check.py docs/adr`), status register (`tools/audit_status_register.py KNOWN-ISSUES.md --index-lines 140`), composer/npm audit (pre-tag; SKIPPED if no composer on host — KI-4), php suite (`php artisan test`).
- Browser suite not run in §15–§17 addendums: needs session-private port + scratch DB per KI-73/KI-80/KI-105. Gate may red or green spuriously locally.

**C. ADRs advanced 0028→0037.** Recent accepted binds: 0031 run-level maiden lock, 0032 independent training overlay row, 0033 veteran transfer request row, 0034 Team Rank stored current state, 0035 run skill hint level + ladder, 0036 Japanese skill name search key, 0037 match-candidate entity = `source_key`. AGENTS.md §2 binding-decision row still ends at 0025 — stale.

**D. Screens compressed 65→62 pages while components grew 117→119** — consolidation of review/skills/veterans surfaces (`25d039c0`, `ac65bbe9`) plus four new dirty pages in flight.

---

## 3. Prioritised findings

### P0 — Working tree dirty mid-slice; gates unverified against current code

18 tracked files modified, none committed since `e04a6db0` HEAD itself is a docs commit. The dirty set touches active user journeys: Build Target, Event Decision, trainee select/profile, catalog, review index. `pint --test` only lints changed PHP files; phpstan whole-project; `php artisan test` unrun locally. **Risk:** uncommitted UI changes may fail typecheck/lore/php suite; no evidence they pass §9.

**Evidence:** `git status --porcelain` (18 ` M`, 25 `??`); `tools/gates.sh` step list; KI-73/KI-80/KI-105 (browser needs private port + scratch DB).

**Recommendation:** Commit-or-stash the dirty set before any release-style verification; run `bash tools/gates.sh --fast` then full `composer test` on a scratch DB. Do not tag until green.

### P1 — KI-162 intermittent flake can flip gate outcome

`2453 passed / 1 failed` (Dashboard/Races, 2026-11-17) then `2455 passed / 0 failed` same HEAD, same peer files; cases pass alone and in pairings. **Status OPEN.** A gate run can fail nondeterministically.

**Evidence:** `KNOWN-ISSUES.md` KI-162 body.

**Recommendation:** Treat any single red gate run as suspect; reproduce 3× before acting. Add replay/logging to the two flaky cases if they recur.

### P1 — KI-160 / KI-166 reflow: cockpit half fixed, races half cause unknown

KI-160 cockpit overflow (88px at 640px) RESOLVED via `overflow-x-auto` wrapper in `NextDecision.vue:344`. KI-166 (Races 59px overflow at 320px) marked CLOSED at `816e923e` but body states cause still unknown; `culprits: []` ruled a probe defect, not a finding. Reflow confirmed green at 768/1280; 320px only session-private (port 8391), planner/strip confirmation not re-run (stale public/build).

**Evidence:** `KNOWN-ISSUES.md` KI-160/KI-166 bodies; `career-race-strip.spec.ts:203`; `career-cockpit.spec.ts:373`.

**Recommendation:** Re-probe the Races surface at 320px on a fresh build before declaring KI-166 resolved; add a pinned 320px case to `career-race-strip.spec.ts` or `career-planner.spec.ts`.

### P2 — AGENTS.md / governance docs stale vs shipped tree

- §1 "No CI" contradicted by three NativePHP workflows.
- §2 binding-decisions row ends at ADR-0025; disk is at 0037.
- §3 default-placement rules say new docs → `docs/research-scratch/` masters; this assessment itself would land in `docs/audits/` per defect-register rule — placement tension worth an owner ruling.
- `SCREEN_SPEC.md` §8 remains screen authority; `docs/plan/` holds 10+ plans, latest `implementation-plan-2026-11-19.md`; unclear which plan owns current dirty pages.

**Recommendation:** Owner to re-baseline AGENTS.md §1–§3 and reconcile `docs/plan/` ownership for the dirty surfaces.

### P2 — Lore gate unverified since `f6845083`

Last measured: 583/75 docs-mode, 319 code-mode (advisory, exit 0). Corpus grew (config +94 lines, ADRs +7). No re-measurement at `e04a6db0`.

**Evidence:** §16.8 / §17.1 lore gate paragraphs; `tools/lore.php`.

**Recommendation:** Run `composer lore` and `composer lore-code`; add hits only when a ruling is owed (advisory gate, exit 0).

### P3 — Untracked root artifacts (`agent.json`, `ki_entries.json`, 18 background images)

`agent.json` (nano-agent config, `openai/gpt-5.4-nano`) and `ki_entries.json` (auditor dump) sit in repo root untracked; 18 theme/background images (EVA-01, Azure Daybreak, Midnight Circuit palettes per `8fd7e18e`) untracked. `tools/check_untracked.py` sweep is a gate step — these will flag unless gitignored or committed intentionally.

**Evidence:** `git status --porcelain` `??` entries; `8fd7e18e` theme commit.

**Recommendation:** Decide intent: gitignore the dumps/assets or commit them under `resources/img/` / `tools/`. `agent.json` looks misplaced — remove or relocate.

### P3 — NativePHP acceptance depends on ephemeral install state

Acceptance workflow installs the built `.exe` silently to `LOCALAPPDATA\Programs\trainer-desk`, then runs Playwright against `NATIVEPHP_EXE_PATH`. No quarantine of prior installs; diagnostics always uploaded. Works in Actions; host reproducibility uncertain.

**Evidence:** `.github/workflows/nativephp-windows-acceptance.yml` lines 66–85, 91–145.

**Recommendation:** Document host prerequisites in `MCP_SETUP.md`/`README.md`; consider a clean-install step or versioned install path.

---

## 4. Completed / fixed since baseline (for context, no action needed)

Round-6 defects fixed by round 7 (per §16.2 corrections): R6-05 parent name, R6-11 wizard `card_level`, R6-12 Timeline energy/SP, R6-03 query-string redirect, R6-04 build-target labels, R6-08 spark note, R6-15 `Hint Lvl` duplicate, R6-09/R6-10 preflight counts, R6-23 `Pal slot`→`Friends`. KI-154–158 resolved; KI-161 spirit proxy migration deployed; KI-163 shared type landed (four duplicate `GoalRow`/`GoalGroup` interfaces kept in agreement by hand — debt); KI-164 review-field keyed helper landed (positional assertions in `SnapshotReviewTest` — debt); KI-167 focus-guard fix in `ScenarioSelect.vue:choose()`.

---

## 5. Recommended next steps (priority order)

1. **Gate the dirty tree:** commit or stash the 18 modified files; run `bash tools/gates.sh --fast` → `composer test` on scratch DB. Blocking if red.
2. **Re-probe KI-166 at 320px** on a fresh build; add pinned reflow case.
3. **Re-run lore gates** at current HEAD; record numbers in this assessment's successor.
4. **Re-baseline AGENTS.md §1–§3** (CI exists, ADRs to 0037, docs placement) — owner ruling.
5. **Decide untracked root artifacts:** `agent.json`, `ki_entries.json`, 18 images — gitignore or commit.
6. **Register this assessment** in `docs/audits/INDEX.md` per §3 audit-artifact placement (owner to confirm path).

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e` (feat(backend): complete five supporting UI/UX backend slices)
**Inspected commit referenced in document:** `e04a6db0` — still valid as the document's baseline snapshot; tree has advanced 3 commits since (e04a6db0 → 9b58bfeb → b382de97 → 3600741e)

### Status of claims at `e04a6db0` vs current tree

| Claim | Status at e04a6db0 | Status at current HEAD | Evidence |
|---|---|---|---|
| NativePHP packaging real; 3 workflows exist | Confirmed | Confirmed | `.github/workflows/nativephp-*.yml`, `composer.json` requires `nativephp/desktop: 2.3` |
| ADRs at 0037, AGENTS.md §2 ends at 0025 | AGENTS.md stale | AGENTS.md still stale | AGENTS.md §2 binding-decision row unchanged |
| `config/scenarios.php` 1396 lines | Confirmed | Confirmed | File unchanged at current HEAD |
| `uiux-comprehensive-walk-2026-11-18` folded into master | N/A | Folded 2026-11-12 (Round 26) | `AUDIT-AND-VERIFICATION.md` master sections |
| Dirty tree: 18 modified, 25 untracked | Confirmed | Status changed | Git log shows `3600741e` as clean HEAD (all modified files committed at 3600741e) |

### Key findings still open

| ID | Severity | Finding | Status | Evidence |
|---|---|---|---|---|
| P0 | High | Dirty tree mid-slice; gates unverified | **RESOLVED at current HEAD** | All previously dirty files committed at `3600741e` |
| P1 | High | KI-162 intermittent flake | **OPEN** | `KNOWN-ISSUES.md` KI-162 body; 2453 passed/1 failed then 2455/0 |
| P1 | High | KI-166 reflow at 320px cause unknown | **OPEN** | KI-166 body; `culprits: []` ruled probe defect |
| P2 | High | AGENTS.md stale vs shipped tree | **OPEN** | AGENTS.md §1 "No CI" contradicted by 3 NativePHP workflows |
| P2 | High | Lore gate unverified since `f6845083` | **OPEN** | `tools/lore.php` not re-run at current HEAD |

### Commands executed at re-validation

None executed — read-only inspection of current tree state via `git log --oneline -10` and `git status --porcelain`.

### Limitations

No local server running; no browser suite, Pest suite, phpstan, or lint executed. Evidence categories are source-static where runtime verification was not possible.
