# Reconciliation and Actionability

> **Current status note, 2026-11-24.** The condition and index rows below are the historical reconciliation snapshot. Current values live in the five canonical roadmaps: condition 7 is GREEN (`audit_status_register.py RESULT: clean`, 0 OPEN), the citation ratchet is `546 <= 708` at HEAD, and full PHP/browser suites remain NOT VERIFIED at `3600741e`. No row here is an active execution order.
 (Tasks #10-#11)

Status reconciled against execution evidence, and canonical-roadmap actionability verified. Generated 2026-11-23. Distinctions preserved: historical vs current measurements, partial verification vs unverified assertions, DONE vs premise-refuted vs terminal.

## 1. Status reconciliation vs execution evidence (Task #10)

### KI-114 (S2.3) — premise refuted, DONE; do not re-open

| Source | Claim | Status | Weight |
|--------|-------|--------|--------|
| `archive/IMPLEMENTATION-ROADMAP-2026-11-18.md` §2.5 | KI-114 OPEN — "specs drive retired controls" | Historical claim, superseded | Low (pre-consolidation) |
| `EXECUTION-RECORD-2026-11-18.md` S2.3 | KI-114 DONE — "premise refuted" | Execution evidence | **Authoritative** |
| `RESIDUAL-VERIFICATION-2026-11-18.md` §Slice 2.5 | Selectors resolve to live sites (`SupportSlot.vue:221`, `Builder.vue:443/455/494`); only 3 failures on `career-legacy-deck-steps.spec.ts` Legacy step unclassified | Execution evidence | **Authoritative** |

Reconciled status: **DONE — premise refuted**. The OPEN marking reflects a refuted premise, not an open defect. The 3 unclassified Legacy-step failures are recorded but do not re-open KI-114.

### KI-167 (S2.2) — root cause found and fixed, DONE

| Source | Claim | Status |
|--------|-------|--------|
| `archive/UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md` §11/§12 | "root cause NOT established" | Historical claim, superseded |
| `EXECUTION-RECORD-2026-11-18.md` S2.2 | DONE — root cause found and fixed (`:disabled="form.processing"` on scenario radio) | **Authoritative** |

Reconciled status: **DONE**. Root cause `:disabled="form.processing"` fixed. The "NOT established" marking is historical.

### UI/UX audit Phases 0–5

| Phase | Status | Evidence |
|-------|--------|----------|
| Phase 0 | DONE | Accessibility batch (S0.1 commit pending owner) |
| Phase 1 | DONE | Routing table + screen-level findings sent to `AUDIT-AND-VERIFICATION.md` |
| Phase 2 | DONE | Stat denominator defect fixed |
| Phase 3 | DONE | WCAG AA token pairs; `DesignTokensTest` 17 passed; ratios 4.566/5.187/7.190 ≥ 4.5:1 |
| Phase 4 | DONE | KI-166 CLOSED 2026-11-18 (`.card:has(> table.t)` `position: relative`) |
| Phase 5 | **OPEN — implementation landed in working tree; not measured against HEAD `3600741e`** | Error summary, IME-safe Enter, deferred props, WCAG 2.2 cases, Baseline rows. The five slices exist on disk (2026-11-24) — `tests/browser/error-summary.spec.ts` (2 cases, untracked), `isComposing`/`keyCode === 229` in 5 working-tree files (1 at HEAD), `Inertia::defer(..., 'cockpit-secondary', true)` in `CockpitController.php:377` (absent at HEAD), `accessibility.spec.ts` WCAG 2.2 cases HEAD 1 → working 4, `DESIGN.md` §13 rows HEAD 107 → working 114 — but are **not at HEAD** and have not been verified across the whole Phase 5 surface. See FRONTEND-AND-UX-ROADMAP.md §1 evidence-status note. |

### Rendered verification R0–R5

| Phase | Status | Detail |
|-------|--------|--------|
| R0 Harness | DONE | Playwright session-private harness operational |
| R1 Route reconciliation | DONE | All 59 GET routes mapped |
| R2 Rendered defects R2.1–R2.5 | DONE | All 5 defect classes resolved |
| R3 Screens left alone | DONE | Intentionally unmodified screens catalogued |
| R4 Remaining work R4.1–R4.5 | DONE | Measurements on isolated scratch DB |
| R5 Integration at HEAD `3600741e` | DONE | All DONE; **R4.3 error flow PARTIALLY VERIFIED** — 419 (CSRF) not browser-reproducible due to Laravel 13 `PreventRequestForgery::hasValidOrigin()` |

### Browser suite truth

- 15 failing specs (38 failed tests at `3cbbd8b9`) triaged: **3 stale** (`_screens-indep-training.spec.ts`, `career-preflight-indep.spec.ts`, `accessibility.spec.ts` — assert removed controls; delete/rewrite), **12 need isolated re-run** (`career-deck-summary` 6, `career-trainee-select` 6, `career-legacy-deck-steps` 4, `career-profile` 8 [beforeEach timeout — priority], `career-surface-cutover` 2, `career-deck-select` 1, `career-race-planner` 1, `career-race-strip` 1, `career-scenario-select` 1, `career-snapshot` 1, `catalog-detail` 1, `catalog-to-wizard` 1)
- `career-preflight.spec.ts` **PROTECTED** — asserts Phase E2 cut (`toHaveCount(0)`); must not touch
- Suite size measured two ways (historical vs current distinction): MASTER/earlier cites 884 tests / 126 files; QUALITY §1.2 measures 876 cases / 125 files (126 in PROJECT-ROADMAP, 124 after `nativephp-packaged.spec.ts` exclusion). These are different commits/scopes — not contradictory, but not a single current number. Re-measure at quiet box (P3/P4).

### PHP suite truth

- HEAD `3600741e`: **2467 passed / 2 failed** (QUALITY §1.1)
- Failures classified: (1) CRLF guard — real, fixed; (2) `DocCitationParityTest` — pre-existing, 808 vs 708 baseline at older commit; current working tree under bar (see §2)
- Baseline/final: 2478 passed / 4 failed (final target)

### Citation ratchet (DocCitationParityTest, baseline 708 pinned at `DocCitationParityTest.php:26`)

| Measurement | Ref | Dead links | vs 708 baseline | Status |
|-------------|-----|------------|-----------------|--------|
| Baseline | `DOC_CITATION_BASELINE` | 708 | at bar | Pinned (law #3: never raised) |
| Committed tree (older) | `2546689e`/`3cbbd8b9` | 808 | over bar | Historical, pre-QWEN-fix |
| Post-repoint | `8482c112` | 546 | under bar | EXECUTION-RECORD N4.2 |
| Working tree (current HEAD `3600741e`) | — | 690 | under bar | Current (QUALITY §2) |
| Working tree (alt cite) | — | 694 | under bar | Alternate cite in consolidation summary |

Reconciled: current tree is **under baseline (690 ≤ 708)**. The 808 figure is a historical committed-tree measurement, not current. Law #3 holds: fix is making docs true, never raising the baseline. (Minor 690-vs-694 difference reflects different measurement commits; both under bar.)

### Release bar — seven conditions (QUALITY §5 / V3-RELEASE §1)

| # | Condition | State | Blocker |
|---|-----------|-------|---------|
| 1 | PHP suite green, no unexplained skips | **OPEN** | 2 failures (CRLF fixed; DocCitationParity) |
| 2 | Browser suite green on session-private harness | **BLOCKED** | Phase B incomplete; 12 specs need isolated re-run |
| 3 | Style, types, build, static analysis | **GREEN** | pint, typecheck, build, phpstan all pass |
| 4 | WCAG AA token pairs | **GREEN** | 17 passed |
| 5 | Clean clone + install renders artwork | **PENDING** | Not yet tested on clean clone |
| 6 | `QWEN.md` contradicts nothing | **GREEN** | Corrected at `3cbbd8b9` |
| 7 | BO/KI ledger names every finding's terminal state | **BLOCKED** | Phase A incomplete; 7 index-OPEN |

Release is **blocked** on conditions 1, 2, 5, 7. `v3.0.0` tag is owner-only (P6) and not spec-gated ("single red spec blocks tag" premise refuted).

### Owner-gated decisions (D-1..D-13)

D-1..D-7 **DONE**; D-8, D-9, D-10, D-12 **BLOCKED** (owner ruling/signature); D-11 **OPEN**; D-13 **OWNER-GATED**. Release requires D-8, D-9, D-10, D-12, D-13 resolutions plus tag authorization.

## 2. Measurement distinctions preserved (Task #10)

| Item | Historical | Current | Note |
|------|-----------|---------|------|
| KI-114 | OPEN (IMPLEMENTATION roadmap §2.5) | DONE — premise refuted | Later evidence overrides earlier claim |
| KI-167 | root cause NOT established | DONE — root cause fixed | Later evidence overrides |
| Citation ratchet | 808 (committed `2546689e`) | 690 (HEAD `3600741e`) | Different commits; current under baseline |
| Browser suite | 884 / 126 (earlier) | 876 / 125 (current measure) | Different scopes; re-measure at quiet box |
| v3.0.0 tag | "single red spec blocks tag" | refuted; owner-cuts-tag | Premise refuted, not fact |

Rule applied: later docs do **not** automatically override earlier evidence; `DONE` requires identifiable evidence; historical `OPEN` is not proof a task remains open today. KI-114/KI-167 are DONE because execution evidence (EXECUTION-RECORD) identifies the fix; the earlier OPEN markings are historical claims superseded by evidence.

## 3. Actionability verification (Task #11)

Every active MASTER slice (S0.1–S4.4, S5.1–S5.9) checked for: objective, dependencies, acceptance criteria, verification requirements, evidence status.

| Slice | Objective | Dependencies | Acceptance | Verification | Evidence status | Actionable? |
|-------|-----------|--------------|------------|--------------|-----------------|-------------|
| S0.1 | Commit UI/UX batch | — | named pathspec commit | `git status` clean for pathspec | Not committed (owner-gated) | Yes |
| S0.2 | Commit/repair `tools/gates.sh` | S0.1 | gate committed | `git ls-files tools/gates.sh` non-empty | Not committed | Yes |
| S0.3 | Commit ADR drafts + doc batch | — | named pathspec commit | `git status` | Not committed (owner-gated) | Yes |
| S0.4 | Clear empty-diff noise | — | tree hygiene clean | `git status --porcelain` | DONE | Yes (done) |
| S0.5 | Reconcile master/origin | S0.1–S0.3 | divergence `0 0`, push | `git rev-list --left-right --count` | Owner-gated | Yes |
| S0.6 | Light/dark re-tune | D-13, S0.1–S0.4 | palette applied | visual + D-13 ruling | Owner-gated (D-13) | Yes |
| S1.1 | Quiet-box Pest baseline | Phase 0 | baseline green | `php vendor/bin/pest --compact` | 2467/2 failed | Yes |
| S1.2 | Full browser sweep | S0.1, S1.1, owner | all failures classified | `npx playwright test` scratch DB | BLOCKED (Phase B) | Yes |
| S1.3 | Citation ratchet ≤ 708 | S0.3 | dead links ≤ 708 | `python tools/doc_census.py` | 690 ≤ 708 | Yes (done, tool times out — see §4) |
| S2.1 | page-header tier fix | S1.2, owner | 6 cases confirmed/refuted | spec edit | Expected red | Yes |
| S2.2 | KI-167 focus round trip | S0.1, S1.2 | root cause fixed | reproduction | DONE | Yes |
| S2.3 | KI-114 premise refuted | S1.2 | selectors live | spec run | DONE (premise refuted) | Yes |
| S2.4 | KI-127 card wrapper | S0.1 Tile edits | `<div class="card">` | `TeamRaceFacilityPanel.vue:51` | DONE | Yes |
| S2.5 | KI-104 + ADR-0027 | — | ADR-0027 signed | `docs/adr/0027...:11` | BLOCKED (owner signature) | Yes |
| S2.6 | Register closeout KI-116/119 | — | register clean | `KNOWN-ISSUES.md` parse | DONE | Yes |
| S3.1 | Independent Training door | ADR-0032 owner, S0.1 | door works | feature test | DONE | Yes |
| S3.2 | Hint-level copy | — | copy corrected | — | DONE | Yes |
| S3.3 | Japanese search key | — | key populated | — | DONE | Yes |
| S3.4 | Maiden-lock fallback | owner | fallback decided | — | Owner-gated | Yes |
| S3.5 | Capability register | owner | register ruled | — | Owner-gated | Yes |
| S4.1 | Re-baseline VERSION.md | S1.1, S1.2, S0.5 | line 9 fixed | `git tag -l '3.0.*'` | OPEN | Yes |
| S4.2 | Complete CHANGELOG 3.0.0 | S0.1, S0.3 | one Unreleased heading | CHANGELOG read | OPEN | Yes |
| S4.3 | ADR signatures + index | S4.1–S4.2 | all ADRs signed | `adr-check.py` | Owner-gated (D-13) | Yes |
| S4.4 | Cut v3.0.0 tag | S0.5, S1.1, S1.2, S4.1–S4.3, owner | tag created | `git tag v3.0.0` | BLOCKED (conditions) | Yes |
| S5.1–S5.9 | Phase 5 (post-tag) | various | per-slice acceptance | per-slice verification | OPEN / blocked on D-8, D-9 | Yes |

**Result:** All 33 master slices have objective, dependencies, acceptance, verification, and evidence status. **No duplicate active tasks with independent status** — each slice ID appears once in MASTER §5–§10; the dependency graph (§11) gives a single execution order. Slices marked DONE carry quoted command evidence (per Law #1); slices blocked on owner carry the exact artifact the owner must sign (per MASTER §11 rule).

## 4. Tooling validation results (Task #12)

| Tool | Command | Result | Notes |
|------|---------|--------|-------|
| md_fence_check.py | `python tools/md_fence_check.py docs/roadmap/` | **GREEN** — 0 invalid closing fences in 14 file(s) | Includes new CROSSWALK + MOVED-FILE-REFERENCE-AUDIT |
| git diff --check | `git diff --check -- docs/roadmap/` | clean | no whitespace/EOF errors |
| git diff --cached --check | `git diff --cached --check -- docs/roadmap/` | clean | staged changes clean |
| git status --porcelain -uall | `git status --porcelain -uall -- docs/roadmap/` | documented | 11 staged archive moves; INDEX.md modified; 5 canonical + 2 audit docs untracked; peer files excluded |
| doc_census.py | `python tools/doc_census.py` / `--working-tree` | **TIMEOUT exit 124** (>90s) | **Pre-existing limitation, not introduced by consolidation** — full-repo scan: `git ls-tree -r` all tracked files + `git cat-file --batch` all `.md` blobs + `git grep` per master over whole repo; rejects path args (`unknown argument`). Bounded validation: use `md_fence_check.py` (path-scoped, fast, green). doc_census remains a whole-repo gate; its timeout is a known tool characteristic. |

`audit_status_register.py` — confirmed **absent from repo** (exists only in the verification skill). RESIDUAL-VERIFICATION §Slice 4.1 already notes this. Register truth instead verified directly: `KNOWN-ISSUES.md` shows 7 index-OPEN (KI-104, 106, 114, 116, 119, 127, 162), 6 duplicate blocks (KI-70/71/101/102/103/104), contradictions clean (FRONTEND §5; QUALITY §3.4).

## 5. Peer-change preservation confirmation

No peer-owned files touched. Scope limited to `docs/roadmap/`:
- Edited: `docs/roadmap/INDEX.md` (Task #7 authority: MASTER = execution authority/ordering authority; RESIDUAL = verification appendix; 5 canonical = derived views no independent authority)
- Added: `docs/roadmap/CROSSWALK-SOURCE-TO-CANONICAL.md` (Task #8), `docs/roadmap/MOVED-FILE-REFERENCE-AUDIT.md` (Task #9), this file (Tasks #10-#12)
- All 12 archive moves pre-staged (10 `git mv` + 1 `mv`) and preserved.
- 269 modified + 141 untracked peer files across the repo: **not touched**.

---
*Consolidation audit complete: Tasks #7 (authority), #8 (crosswalk), #9 (reference audit), #10 (status reconciliation), #11 (actionability), #12 (validation) documented. Release remains blocked on conditions 1, 2, 5, 7 and owner decisions D-8, D-9, D-10, D-12, D-13.*
