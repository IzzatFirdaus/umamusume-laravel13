# Quality and Verification Roadmap

**Authoritative roadmap for testing and verification.** This file is the single execution authority for all quality tasks: PHP/browser suite truth, DocCitationParityTest citation ratchet (baseline 708), verification gates and tooling, KI-114 resolution, release-bar conditions 1-7, and MASTER phase-0 slice S0.2 (gate repair), phase-1 slices S1.1-S1.3, phase-2 slices S2.1/S2.3/S2.6, and phase-5 slice S5.7. Canonical IDs retained. Historical sources archived in `docs/roadmap/archive/`. Completed work is marked and preserved as historical evidence; do not re-open.

---

## 1. Test Suite Truth

### 1.1 PHP Suite (Pest 4)

| Metric | Historical measurement (not re-run at this HEAD) | Baseline / Target | Source |
|--------|---------------------------|-------------------|--------|
| Feature tests | 2467 passed / 2 failed at an earlier tree; full suite NOT VERIFIED at `3600741e` (`VERSION.md` dated notes) | 0 failures | EXECUTION-RECORD S1.1, `VERSION.md` |
| Unit tests | 13 files | 13 files | PROJECT-ROADMAP §0 |
| Skips | 2 `markTestSkipped` (DesignTokensTest:651, SkillAutomationTest:12) | 0 unexplained | PROJECT-ROADMAP §0 (skip census) |
| Assertions | 37,567 | — | REMAINING-WORK §0 |
| Duration | ~371 s | — | REMAINING-WORK §0 |

**Classified failures** (EXECUTION-RECORD S4.1):
1. CRLF guard — real, fixed
2. `DocCitationParityTest` — pre-existing, 808 vs 708 baseline (see §2)

### 1.2 Browser Suite (Playwright + Pest)

| Metric | Current | Target | Source |
|--------|---------|--------|--------|
| Spec files | 125 (126 in PROJECT-ROADMAP, 124 after nativephp exclusion) | 0 failing | REMAINING-WORK §0, PROJECT-ROADMAP Phase B |
| Test cases | 876 (798 `test(` declarations) | 0 failing | REMAINING-WORK §0 |
| `test.skip` guards | 11 across 8 files | Data-conditional only | PROJECT-ROADMAP §0 |
| Cost per run | ~23 min (session-private DB re-seed) | — | PROJECT-ROADMAP §0 |

**NativePHP exclusion**: `playwright.config.ts` now carries `testIgnore: '**/nativephp-packaged.spec.ts'`; runner total 877→876 in 124 files (REMAINING-WORK §2.1).

---

## 2. DocCitationParityTest — Citation Ratchet

| Metric | Value | Status |
|--------|-------|--------|
| Baseline (`DOC_CITATION_BASELINE`) | 708 | Pinned in `DocCitationParityTest.php:26` and `tools/doc_census.py` |
| Earlier census (`2546689e`/`3cbbd8b9`) | 808 dead links | Historical; superseded by the rows below |
| Post-repoint (`8482c112`) | 546 ≤ 708 | EXECUTION-RECORD N4.2: ratchet 546≤708, divergence `0 0` |
| Current census (`3600741e`) | **546 <= 708** at HEAD; working tree 614 <= 708 during this pass | **GREEN**; ratchet unchanged. `DocCitationParityTest` 2 passed / 5 assertions |

**Law**: `DocCitationParityTest.php:26` forbids raising baseline. Fix is making docs true, not raising the bar.

**History**:
- `fec658f8`/`816e923e`: measured 528 (well under bar)
- `aefd6549`: 716 vs 708 (1 failure)
- `3cbbd8b9`: 716→691 ≤ 708 (P5.3, QWEN truth)
- `8482c112`: citation repoints committed, ratchet 546≤708
- `2546689e`: 690 dead links

---

## 3. Verification Tooling

### 3.1 Gates (`tools/gates.sh` + `composer.json` `"gates"` script)

| Gate | Command | Status |
|------|---------|--------|
| PHP suite | `php vendor/bin/pest --compact` | **NOT VERIFIED at `3600741e`**; focused runs passed 68/1346, 69/623 and 2/5 assertions in this pass |
| Browser suite | `npx playwright test` with `PLAYWRIGHT_PORT` + `PLAYWRIGHT_SCRATCH_DB` | **FULL SWEEP NOT RUN**; focused isolated runs passed theme persistence (1), three-theme axe at 320/1280 (1), page-header + KI-127 fixture (8), backdrop probe (1); `career-profile` 8 passed / 1 environment-limited |
| Style (Pint) | `vendor/bin/pint --test --format agent <paths>` | **GREEN** (on 11 changed paths) |
| Typecheck | `npm run typecheck` | **GREEN** |
| Build | `npm run build` | **GREEN** |
| Static analysis (PHPStan) | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | **GREEN** (`phpstan` needs `--memory-limit=1G`; `composer analyse` omits it) |
| WCAG AA tokens | `php vendor/bin/pest --filter=DesignTokensTest` | **GREEN** — 17 passed, ratios 4.566/5.187/7.190 |
| Lore grep | `php tools/lore.php` | **GREEN** — 651 hits / 75 exempt at this pass; no new unexplained hits, no baseline change |

**Gate script**: `tools/gates.sh` + `composer.json` `"gates": ["bash tools/gates.sh"]`. Composer-script form refuted by measurement (closed stdin aborts `artisan test`); script redirects stdin per step. `bash tools/gates.sh --fast` → `GATES: all green` on fast subset.

### 3.2 Markdown Fence Checker (`tools/md_fence_check.py`)

- **Purpose**: Finds closing fences carrying info strings (CommonMark 0.31 §4.5 violation)
- **Status**: **GREEN** — committed at `2546689e`; expanded to expand dir to tracked markdown; reports unreadable as `UNREADABLE`, exits 2; bare → `0 invalid ... in 292 file(s)`
- **Usage**: `python tools/md_fence_check.py [path ...]`

### 3.3 Documentation Census (`tools/doc_census.py`)

- **Purpose**: Reproducible citation census from `git` (replaces stale hand-counted inventory)
- **Baseline**: `DOC_CITATION_BASELINE = 708` (pinned in test and tool)
- **Flags**: `<!-- census: content-not-citations -->` (catalogues), `<!-- census: quoted-original -->` (quoted history)
- **Usage**: `python tools/doc_census.py [--working-tree]`
- **Current**: 690 dead links at `2546689e` (under 708 baseline)

### 3.4 Register Audit (`tools/audit_status_register.py`)

- **Purpose**: Parses `KNOWN-ISSUES.md` for index/body contradictions, duplicate blocks, OPEN count
- **Status**: Vendored at `tools/audit_status_register.py`; the earlier "skill-only" note is historical (RESIDUAL-VERIFICATION §Slice 4.1).
- **Latest clean run**: 2026-11-24, `python tools/audit_status_register.py KNOWN-ISSUES.md --index-lines 140` -> `RESULT: clean`, 120 index rows, 0 OPEN, no contradictions. The 7 index-OPEN row above is a historical 2026-11-18 reading.

---

## 4. KI-114 Conflict Resolution (Critical)

| Source | Claim | Resolution |
|--------|-------|------------|
| `archive/IMPLEMENTATION-ROADMAP-2026-11-18.md` §2.5 (Slice 2.5) | KI-114 **OPEN** — premise: "specs drive retired controls" | **REFUTED** |
| `EXECUTION-RECORD-2026-11-18.md` S2.3 | KI-114 **DONE** — "premise **refuted**" | **AUTHORITATIVE** |
| `RESIDUAL-VERIFICATION-2026-11-18.md` §Slice 2.5 | "Premise refuted — selectors resolve to live sites (`SupportSlot.vue:221`, `Builder.vue:443/455/494`); only 3 failures on `career-legacy-deck-steps.spec.ts` Legacy step unclassified" | **AUTHORITATIVE** |

**Evidence**: Selectors resolve to live controls in `SupportSlot.vue` and `Builder.vue`. The 3 remaining failures are on Legacy-step specs where the step is unclassified — not a spec-driving-retired-controls issue.

**Canonical status**: **DONE — premise refuted**. Do not re-open.

---

## 5. Release Bar — Seven Conditions (from PROJECT-ROADMAP Phase C, REMAINING-WORK §5.1)

| # | Condition | Command | Current State |
|---|-----------|---------|---------------|
| 1 | PHP suite green, no unexplained skips | `php vendor/bin/pest --compact` | **NOT VERIFIED at `3600741e` — specific blocker cleared 2026-11-24.** The ~23 min stop was `RetrievalBenchmarkTest` embedding every corpus chunk against a missing Ollama (`127.0.0.1:11434`). It now probes the daemon once (2 s timeout) and skips the vector sections with an explicit `markTestSkipped` reason; lexical sections still assert provenance and ranking. Focused run (`RetrievalBenchmarkTest` + `AdvisorAiPipelineTest`, `AdvisorAnalysisRouteTest`, `AdvisorProvenanceTest`, `AdvisorRevisionTest`, `TelemetryConfigTest`): **123 passed, 40 skipped, 1182 assertions, 134 s**. Full suite not re-run here (shared environment; no competing run). Test env now forces `ADVISOR_AI_ENABLED=false`, `ADVISOR_AI_EMBEDDINGS_ENABLED=false`, `LANGFUSE_ENABLED=false` in `phpunit.xml` |
| 2 | Browser suite green on session-private harness | `npx playwright test` with `PLAYWRIGHT_PORT` + `PLAYWRIGHT_SCRATCH_DB` | **BLOCKED** by Phase B |
| 3 | Style, types, build, static analysis | `vendor/bin/pint --test`, `npm run typecheck`, `npm run build`, `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | **GREEN 2026-11-24**: `phpstan analyse --no-progress --memory-limit=1G` → **0 errors** (2m27s). The 11 `missingType.iterableValue` findings in `app/Services/LangfuseTraceService.php` are resolved by applying the class-level `@phpstan-type OtlpAttribute` / `OtlpResourceSpan` aliases to every OTLP array shape (heartbeat, span, usage/user/session/tags/event helpers, export) — no suppression, no baseline, no level change. Pint PASS on the changed test files; `TelemetryConfigTest` 10 passed |
| 4 | WCAG AA on every token pair, carve-outs named | `php vendor/bin/pest --filter=DesignTokensTest` | **GREEN** — 17 passed |
| 5 | Clean clone + install renders artwork; offline placeholder shows | `composer setup`, load card detail with mirror empty | **PENDING** |
| 6 | `QWEN.md` contradicts nothing | Manual read against tree | **GREEN** — corrected at `3cbbd8b9`, 7 facts fixed |
| 7 | BO/KI ledger names every finding's terminal state | `KNOWN-ISSUES.md` parse | **GREEN 2026-11-24** — `python tools/audit_status_register.py KNOWN-ISSUES.md --index-lines 140` → `RESULT: clean`, exit 0; index rows KI-169–KI-176 present, id range 57..176 contiguous, `CONTRADICTIONS: none` (re-verified 2026-11-24: 120 index rows / 126 headings, body census MISSING 20 / CLOSED 78 / OPEN 22, no index-OPEN) |

---

## 6. Source-to-Destination Register

| Source File | Source Section | Destination | Status |
|-------------|----------------|-------------|--------|
| `residual-verification-2026-11-18.md` | §Slice 2.5 (KI-114), §Slice 2.6 (citation ratchet), §Slice 4.1 (register closeout) | §2, §4, §5 | Consolidated |
| `execution-record-2026-11-18.md` | S1.1-S1.3, S2.3 (KI-114), S4.1-S4.4 | §1, §2, §4, §5 | Consolidated |
| `project-roadmap-2026-11-16.md` | Phase A, B, C, §0 measurements | §1, §3, §5 | Consolidated |
| `remaining-work-roadmap-2026-11-17.md` | Phase 1 (register), Phase 2 (test truth), §0 gate state | §1, §2, §3, §5 | Consolidated |
| `v3.0.0-implementation-roadmap-2026-11-16.md` | Phases R1-R4, Definition of Done | §1, §2, §5 | Consolidated |
| `implementation-roadmap-2026-11-18.md` | §2.5 (KI-114 OPEN claim) | §4 | Conflict resolved |
| `master-implementation-roadmap-2026-11-18.md` | §12 (verification command ref), §16 (acceptance) | §3, §5 | Consolidated |

---

## 7. Open Verification Items

| Item | Blocker | Next Step |
|------|---------|-----------|
| Citation ratchet | `546 <= 708` at HEAD, `614 <= 708` working tree; test 2 passed | No repointing owed by this pass | Monitor after each documentation landing; baseline never raised |
| Browser suite full sweep never run at current tree | Phase B triage + isolated re-runs needed | Run `career-profile.spec.ts` first on quiet box |
| Condition 5 (clean clone + artwork) | Requires fresh clone test | Execute `composer setup` on clean clone |
| `audit_status_register.py` not in repo | Resolved 2026-11-23 | **Vendored** at `tools/audit_status_register.py`; the gate runs from the repo |
| `KNOWN-ISSUES.md` dirty (peer-held) | Resolved 2026-11-23, re-verified 2026-11-24 | KI-169–KI-176 index rows added; register audit clean (`RESULT: clean`, no contradictions) |


---

## 8. Residual verification slices (verified 2026-11-24, dependency order)

The full suite is a checkpoint gate, not a feedback loop, and the working tree is contested; no full
Pest or Playwright sweep is claimed. These slices are the remaining verification work in this authority.
Every row produces command output in a hand-off before its status changes.

| Order / ID | Category | Objective and affected components | Prerequisite | Acceptance and verification |
|---|---|---|---|---|
| 1 · Q-1 | Verification-only | Obtain a full PHP baseline at a settled HEAD. | Peer writes stop long enough to attribute the run; quiet-box pre-flight. | `php artisan test --compact` output with SHA and dirty-path counts; every failure classified, no baseline weakened. |
| 2 · Q-2 | Verification-only | Obtain a full browser sweep at the same settled HEAD. | Fresh isolated bundle, session-private port and scratch DB, artwork-mirror policy for `career-profile`. | Full sweep output with each failure classified or filed; focused runs already green are not re-evidence for the whole suite. |
| 3 · Q-3 | Verification-only | Release condition 5: clean-clone install and artwork placeholder. | Clean clone outside the owner's working tree, inside repo scratch per `AGENTS.md` §12. | `composer setup` succeeds; an empty mirror renders the documented placeholder; no peer artifact is reused. |
| 4 · Q-4 | Verification-only | Resolve the `career-profile` artwork-fixture ambiguity without weakening the assertion. | Decision whether the isolated harness seeds an artwork mirror or the spec supplies a fixture. | The portrait case passes for the intended reason (mirror present) or is explicitly classified as environment-limited; current result 8 passed / 1 failed, failure is missing `img[data-artwork-slot]`. |
| 5 · Q-5 | Completed, retained | Citation ratchet and register audit. | None. | `546 <= 708` at HEAD, `DocCitationParityTest` 2 passed, audit register `RESULT: clean`; no active work. |

---

## MASTER Slice Ownership Map (Testing/Verification)

| MASTER Slice | Slice Name | Canonical Owner | Status |
|---|---|---|---|
| S1.1 | Quiet-box full Pest baseline | QUALITY | PARTIALLY VERIFIED |
| S1.2 | Full browser sweep at landed HEAD | QUALITY | BLOCKED |
| S1.3 | Citation ratchet: 808 → 708 or lower | QUALITY | DONE |
| S2.1 | page-header.spec.ts superseded tier | QUALITY | DONE — 6 isolated browser checks passed on 2026-11-24; assertions match `PageHeader.vue:68` |
| S2.3 | KI-114 deck-picker specs | QUALITY | DONE — premise refuted |
| S2.6 | Register closeout KI-116/KI-119 | QUALITY | DONE |
| S5.7 | Performance budgets | QUALITY | **DONE 2026-11-23, re-pinned 2026-11-24** — catalogue index pinned at 4 tree queries (`CatalogRosterTreeTest`, `->group('perf')`); cockpit read pinned at 29 owned / 33 total, invariant 12→40 turns (`tests/Feature/CockpitQueryBudgetTest.php`). The 2026-11-24 re-pin measures the 29th query the C05 `can_change_scenario` existence check added to the run section since the first pin |
| S0.2 | Commit and repair the gate (`tools/gates.sh`) | QUALITY | OPEN — pint paths + adr-check + doc_census + check_untracked |

*All MASTER phase-0 S0.2, phase-1 S1.1–S1.3, phase-2 S2.1/S2.3/S2.6, phase-5 S5.7 owned by QUALITY-AND-VERIFICATION-ROADMAP.md*

---

*Generated: 2026-11-23. All canonical IDs retained. KI-114 resolved: EXECUTION-RECORD/RESIDUAL-VERIFICATION authoritative over IMPLEMENTATION-ROADMAP-2026-11-18. Historical sources in `docs/roadmap/archive/`.*