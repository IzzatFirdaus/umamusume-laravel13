# v3.0.0 Release Roadmap

**Authoritative roadmap for release.** This file is the single execution authority for all release tasks: release-bar seven conditions, version truth, tag hand-off, release path P1-P6, NativePHP packaged exe acceptance, dependency graph, definition of done, and MASTER phase-4 slices S4.1/S4.2/S4.4 and phase-0 slice S0.5. Canonical IDs retained. "Single red spec blocks tag" premise refuted by V3-RELEASE-PLAN; tag is owner-cut, not spec-gated. Historical sources archived in `docs/roadmap/archive/`. S4.3 (ADR hygiene/signatures/index) is owned by DECISIONS-AND-DEPENDENCIES.md; V3-RELEASE depends on S4.3 for tag hand-off but does not own it.

---

## 1. Release Bar — Seven Conditions (must all pass before tag)

| # | Condition | Command | Current State | Evidence |
|---|-----------|---------|---------------|----------|
| 1 | PHP suite green, no unexplained skips | `php vendor/bin/pest --compact` | **NOT VERIFIED at `3600741e`** | The historical two failures are cleared (CRLF fixed; ratchet now `546 <= 708` with `DocCitationParityTest` 2 passed). Focused runs passed 68/1346 and 69/623 assertions; no full suite was run in this pass (`VERSION.md` dated notes). |
| 2 | Browser suite green on session-private harness | `npx playwright test` with `PLAYWRIGHT_PORT` + `PLAYWRIGHT_SCRATCH_DB` | **FULL SWEEP NOT VERIFIED** | Focused isolated runs on a fresh bundle passed theme persistence (1), three-theme axe at 320/1280 (1), page-header + KI-127 fixture (8), backdrop probe (1); `career-profile` 8 passed / 1 environment-limited by an absent artwork mirror. No assertion weakened. |
| 3 | Style, types, build, static analysis | `vendor/bin/pint --test --format agent <paths>`, `npm run typecheck`, `npm run build`, `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | **GREEN** | All pass; `phpstan` needs `--memory-limit=1G` |
| 4 | WCAG AA on every token pair, carve-outs named | `php vendor/bin/pest --filter=DesignTokensTest` | **GREEN** | 17 passed (480 assertions); ratios 4.566/5.187/7.190 ≥ 4.5:1 |
| 5 | Clean clone + install renders artwork; offline placeholder shows | `composer setup`, load card detail with mirror empty | **PENDING** | Not yet tested on clean clone |
| 6 | `QWEN.md` contradicts nothing | Manual read against tree | **GREEN** | Corrected at `3cbbd8b9`; 7 facts fixed |
| 7 | BO/KI ledger names every finding's terminal state | `KNOWN-ISSUES.md` parse | **GREEN 2026-11-24** | `python tools/audit_status_register.py KNOWN-ISSUES.md --index-lines 140` -> `RESULT: clean`, 120 index rows, 0 OPEN, no contradictions |

**Skip census** (measured at `3cbbd8b9`): 2 PHP skips (`markTestSkipped` in DesignTokensTest:651, SkillAutomationTest:12); 11 browser `test.skip(true, …)` across 8 files.

**Gate script**: `bash tools/gates.sh --fast` → `GATES: all green` on fast subset; full gates require Conditions 1,2,5,7.

---

## 2. Version Truth and Tag Hand-off

| Slice | Work | Acceptance | Status |
|-------|------|------------|--------|
| D.1 / 5.2 | `VERSION.md` and `CHANGELOG.md` agree on `3.0.0` untagged pre-release | Declared version, tag list, and changelog sections all name `3.0.0` and five shipped tags (`0.1.0`, `2.0.1`, `2.0.2`, `2.1.0`, `2.1.1`); `3.0.0` stated as untagged | **DONE with dated notes** |
| D.2 / 5.3 | Tag request presented to owner with Phase C evidence | Owner cuts tag; agent does not | **BLOCKED** by Conditions 1,2,5,7 |

### `VERSION.md` Corrections — landed with dated notes (from REMAINING-WORK §5.2)
- Line 9: Claims journey spec is red and cites a `details` locator fix — file carries one prose comment and no such locator
- Line 9: Claims only `0.1.0` is tagged while table above lists five tags
- **Action**: Correct both with dated notes; quote `git tag -l '3.0.*'` and journey spec result

### `CHANGELOG.md` Corrections — landed (from REMAINING-WORK §5.3)
- Two `## [Unreleased]` headings (lines 14 and 464)
- Zero mentions of S1 (snapshot pipeline) or S18 (surface batch)
- **Action**: One Unreleased heading; name snapshot pipeline and S18 batch under the release that carries them

---

## 3. Release Path (from V3-RELEASE-PLAN-2026-11-17)

**Prioritized slices (execution order):**

| Priority | Slice | Description | Status |
|----------|-------|-------------|--------|
| P1 | Register truth + citation ratchet | Phase A + DocCitationParity ≤ 708 | **DONE** — register clean, 0 OPEN; ratchet 546 at HEAD |
| P2 | Browser suite truth | Phase B — 15 specs classified | **OPEN** |
| P3 | Quiet-box gate | Full suite on quiet box (no peer contention) | **OPEN** |
| P4 | Full browser suite | All 876 cases green or ≤3 named failures with register rows | **OPEN** |
| P5 | Version truth + changelog | `VERSION.md` + `CHANGELOG.md` corrected | **DONE** — dated notes in both files; owner tag decision remains P6 |
| P6 | Owner cuts tag | `git tag v3.0.0 <sha>` + `git push origin v3.0.0` | **BLOCKED** |

**Refuted premise**: V3-RELEASE-PLAN explicitly refutes "single red spec blocks tag" from `v3.0.0-implementation-roadmap-2026-11-16.md`. The journey spec is green at HEAD `3600741e` (per FRONTEND-RENDERED R5). Tag decision is owner-gated, not spec-gated.

---

## 4. NativePHP Packaged Exe Acceptance (V3-R4.3)

| Step | Command | Status |
|------|---------|--------|
| 1 | `composer install --no-dev` | **PENDING** |
| 2 | Hidden acceptance suite | **PENDING** |
| 3 | `NATIVEPHP_EXE_PATH` resolved | **PENDING** |
| 4 | Packaged exe present | **PENDING** |

Per `VERSION.md` release criteria. Runs after tag.

---

## 5. Dependency Graph (from PROJECT-ROADMAP §3, REMAINING-WORK §3)

```
0.1 Land worktree product content ─┬─> 0.2 Lockfile (DONE: npm ci --dry-run exits 0)
        (blocking; nothing ships)  ├─> 0.3 Untracked build inputs (DONE: all 4 tracked)
                                   ├─> 0.4 Composer script decision (DONE: pre-native-build restored)
                                   └─> 0.5/0.6 Tree hygiene (DONE: win-unpacked ignored, artefact gone)
1.1 Close fixed rows ─> 1.2 Contradictions (DONE) ─> 1.3 Duplicate blocks ─> 5.1 Condition 7
2.1 NativePHP exclusion (DONE) ─> 2.2 Stale spec refuted ─> 2.3 Probe ruling ─> 2.6 Sweep ─> 5.1 Condition 2
2.4 Fence tool (DONE) ─> 2.5 Gate script (DONE)
3.1 KI-166 (DONE) ─> 5.1 Condition 2
3.2 KI-165 (DONE) ─> 4.1 (fan ladder ships with team rank)
3.3 KI-163 (DONE) ─> nothing (type hygiene)
3.4 KI-164 (DONE) ─> nothing
3.5 KI-104 (DONE — ADR-0027 accepted 2026-11-18; dated body correction in KNOWN-ISSUES.md) ─> release claim completeness
4.x Capability slices: 4.1/4.2 independent; 4.3/4.4/4.5 owner-gated; 4.6 small
5.1 Seven conditions ─> 5.2 VERSION ─> 5.3 CHANGELOG ─> 5.4 Owner cuts tag
```

**Critical path**: 0.1 → (1.x + 2.6) → 5.1 → 5.4. Nothing in Phase 3/4 may start before 0.1 (worktree is only place S18/peer fixes exist; branch from `HEAD` would ship non-buildable tree).

---

## 6. Owner Decisions Required for Release

| Decision | Required For | Status |
|----------|--------------|--------|
| ADR-0027 signature (KI-104) | Condition 7 completeness, release claim | **DONE** — accepted under owner delegation 2026-11-18 at `docs/adr/0027-turn-and-decision-logging.md:11`; the remaining correction-writer question is separate and is not a signature gate |
| Team-race slots (4.3) | Capability completeness | **BLOCKED** — citable source or terminal ruling |
| Roster sub-model (4.4) | Capability completeness | **DONE** — ADR-0039 (owner delegation 2026-11-18); `roster_members` (`2026_11_24_120000`), `RosterMember` + `RosterMemberController`; `CockpitRosterTest` green |
| Preseason rounds table (4.5) | Capability completeness | **BLOCKED** — ADR or ruling |
| Tag authorization | Final release | **BLOCKED** — Conditions 1,2,5,7 must pass first |

---

## 7. Source-to-Destination Register

| Source File | Source Section | Destination | Status |
|-------------|----------------|-------------|--------|
| `v3.0.0-release-plan-2026-11-17.md` | P1-P6, execution order, quiet-box gate | §1, §3, §5 | Consolidated |
| `project-roadmap-2026-11-16.md` | Phase C (7 conditions), Phase D (version/tag), §3 dependency graph | §1, §2, §5 | Consolidated |
| `remaining-work-roadmap-2026-11-17.md` | Phase 5 (release bar), §5 owner decisions | §1, §2, §5, §6 | Consolidated |
| `v3.0.0-implementation-roadmap-2026-11-16.md` | Phases R1-R4, Definition of Done | §1, §2, §3, §4 | Consolidated |
| `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` | S4.1-S4.4, §16 acceptance | §1, §2, §5 | Consolidated |
| `EXECUTION-RECORD-2026-11-18.md` | S4.1-S4.4, N0.1 (D-5 approval) | §1, §2, §6 | Consolidated |

---

## 8. Definition of Done (Fully Functional v3.0.0)

From `v3.0.0-implementation-roadmap-2026-11-16.md` §5, verified:

1. `npm run typecheck` **GREEN**
2. `npm run build` **GREEN**
3. `vendor/bin/pest --compact` **NOT VERIFIED at `3600741e`**. The older "single red `full-career-journey.spec.ts` blocks tag" premise is refuted (`PRODUCT-AND-CAPABILITY-ROADMAP.md` §7, `FRONTEND-AND-UX-ROADMAP.md` R5); focused runs are green but are not a full-suite claim
4. `php tools/lore.php` **zero hits**
5. `php artisan migrate:fresh --seed --force` **clean** on scratch SQLite
6. OQ-3 (fetch scheduler: manual-only, resolved in code) and OQ-6 (skill icons out of scope, pick-screen model confirmed) **RESOLVED**
7. All OPEN KIs classified with evidence
8. `git tag v3.0.0` created + pushed (owner instruction required)
9. NativePHP packaged exe acceptance **GREEN**

---

## MASTER Slice Ownership Map (Release)

| MASTER Slice | Slice Name | Canonical Owner | Status |
|---|---|---|---|
| S0.5 | Reconcile master with origin/master | V3-RELEASE | OPEN — owner-gated |
| S4.1 | Re-baseline VERSION.md | V3-RELEASE | DONE — dated 2026-11-24 notes, five tags and no `3.0.*` |
| S4.2 | Complete CHANGELOG for 3.0.0 range | V3-RELEASE | DONE — `[Unreleased]` and `[3.0.0] - unreleased` sections present with Not tagged note |
| S4.3 | ADR hygiene, signatures and index | DECISIONS | BLOCKED — owner (V3-RELEASE depends on S4.3 for tag hand-off, not owner) |
| S4.4 | Cut v3.0.0 tag (owner only) | V3-RELEASE | BLOCKED — conditions 1,2,5,7 |
| S5.9 | 1.0 gate: close PRD questions, declare `/api/v1` shapes, clear OPEN KI touching API/schema | V3-RELEASE | OPEN — owner-gated (release gate) |

*All MASTER phase-4 S4.1/S4.2/S4.4 and phase-0 S0.5 owned by V3-RELEASE-ROADMAP.md. S5.9 (1.0 gate) assigned to V3-RELEASE as release-gate slice — owner confirms single ownership; spans PRD/ARCHITECTURE/KI closure but is gated by the tag/release decision.*


---

## 9. Residual release slices (verified 2026-11-24, dependency order)

Conditions 3, 4, 6 and 7 are green and are recorded above as evidence, not as execution work. The
remaining release work is verification and an owner decision; none of it may be closed on a status edit.

| Order / ID | Category | Objective and affected components | Prerequisite | Acceptance and verification |
|---|---|---|---|---|
| 1 · R-1 | Verification-only | Close release condition 1 with a full PHP baseline at a settled HEAD. | Quiet box and attributable tree (`VERSION.md` dated notes). | `php artisan test --compact` output with SHA and dirty-path counts; classify every failure. |
| 2 · R-2 | Verification-only | Close release condition 2 with a full browser sweep at the same HEAD. | Fresh isolated bundle, session-private port/scratch DB, artwork-fixture policy. | Full sweep output; focused runs already green do not substitute for the whole suite. |
| 3 · R-3 | Verification-only | Close release condition 5 (clean clone + artwork placeholder). | Clean clone inside repo scratch; no peer build artifact reused. | `composer setup` output and rendered placeholder observation. |
| 4 · R-4 | Owner-gated | Owner cuts `v3.0.0` after conditions 1, 2 and 5 are evidenced. | R-1..R-3 complete and register clean. | Owner-issued tag and push; agent does not tag. |

---

*Generated: 2026-11-23. All canonical IDs retained. "Single red spec blocks tag" refuted by V3-RELEASE-PLAN. Historical sources in `docs/roadmap/archive/`.*