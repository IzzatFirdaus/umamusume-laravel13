# Consolidated SITREP — Register, Browser Sweeps & Session Handoffs (Workstream Group B)

**Generated:** consolidation of 6 source SITREPs  
**Scope:** KNOWN-ISSUES register closure, browser sweep reconciliation, patch forensics, session handoffs  
**Source files:** 6 reports spanning 2026-11-16 through 2026-11-18  
**Disposition:** Standalone workstream report; not a master SITREP; original sources archived in `docs/sitrep/archive/`

---

## 1. Source inventory

| # | Source SITREP | Date | SHA-256 | Role in consolidation |
|---|---|---|---|---|
| B1 | `sitrep-2026-11-17-phase-a-register-closeout.md` | 2026-11-17 | `58411291747a0e4404d33c315486d90c8d4e0c9a9c59049ace3ded16a3aa9556` | Phase A register closure, OPEN count, heading count, KI classifications |
| B2 | `sitrep-2026-11-17-s1-s2-wrap.md` | 2026-11-17 | `76fbc6a15deb27489d4ad429c363d911e993aaa1377a02263f99d679a5610c0c` | S1/S2 walk-9 remediation, S2 register truth, OPEN count reconciliation |
| B3 | `sitrep-session-handoff-2026-11-16-to-18.md` | 2026-11-18 | `da3d56c65bab85c5c378facf1d3ac8032eab177041ac87acbe4598f80e27921e` | Roadmap/plan pair, review.spec.ts closeout, visual-overhaul backlog, ADR token corrections |
| B4 | `sitrep-2026-11-18.md` | 2026-11-18 | `0db29bf7fe5acbfdc604ffe940286d4e31a40ac5da4fff78e83f608c432d9fac` | HEAD 816e923e, register reconciliation, OPEN set now 10, ADR token corrections |
| B5 | `sitrep-2026-11-18-session-handoff.md` | 2026-11-18 | `17e60dcc9870e2687f478b99899383d7c2cd60d23b3640c6a9ce2d3589bb89f4` | Session handoff, git state, browser artifact inventory, run classification |
| B6 | `sitrep-2026-11-18-sweep-reconciliation-and-patch-forensics.md` | 2026-11-18 | `2d60f6c656f49d4fc9746e974f2cd995cfe9848f8f6732ee80b321eaaf45a684` | Browser sweep 876 cases / 816 passed, 58 failure classes, patch provenance |

---

## 2. Consolidated findings

### 2.1 Phase A register closure (B1)

**Commit `be8893a2`** — `docs(register): close Phase A entries`, pathspec exactly 3 files: `KNOWN-ISSUES.md`, `resources/js/components/support/SupportSlot.vue`, `resources/js/pages/Catalog/Show.vue`.

**Acceptance (measured on committed tree):**
- `grep -cE '^\*\*State:\*\* OPEN' KNOWN-ISSUES.md` → **0** ✓
- `grep -cE '^#{2,3} KI-' KNOWN-ISSUES.md` → **111** (target 114; gap of 3 pre-existing, flagged stale, not introduced by this session)
- `grep -c 'title=' resources/js/components/support/SupportSlot.vue` → **0** ✓
- `grep -c 'base_stats' resources/js/pages/Catalog/Show.vue` → **5** ✓
- `npm run typecheck` → exit 0 ✓

**TERMINAL classifications:** KI-130, KI-131, KI-132, KI-150 → TERMINAL.  
**CLOSED with code evidence:** KI-141 (BuildTarget `title=` → AbsenceValue), KI-143 (review-detail variant row removed), KI-152 (`base_stats` table cell added), KI-159 (both redundant `title=` removed from SupportSlot.vue).  
**Left open:** KI-136 (peer-owned), KI-137 (stale assertions), KI-145 (peer-owned lint debt), KI-147 (cockpit RunRaceStrip mount removed), KI-160 (cockpit grid overflow, root cause undiagnosed).

### 2.2 S2 register truth (B2)

The brief predicted S2 would be "nearly empty" because Phase A drove body-OPEN to 0. **That prediction was wrong; the warning about grep measuring the wrong field was correct.**

Key finding: the OPEN-count discrepancy arises from three header spellings in `KNOWN-ISSUES.md`:
- `**State:** OPEN` — the Phase A acceptance pattern
- `**Status:** OPEN` — variant spelling
- `**Status: VALUE.**` — variant with trailing period

Phase A's grep only matched `**State:** OPEN` (→ 0). A body-read census across all three spellings yields **53 OPEN**. This is the **0 vs 10 vs 53** conflict:
- Phase A reports OPEN = 0 (using `**State:** OPEN` pattern)
- Session 11-18 (B4) reports OPEN set now **10** (KI-104, 106, 114, 116, 119, 127, 162, 163, 164, 166) — using index-parsed OPEN ids
- S2 (B2) body-read census → **53 OPEN** (all three header spellings)

Six index/body contradictions remain (KI-69, 74, 100, 107, 117, 124).

### 2.3 Browser sweep reconciliation (B6)

Sweep JSON at `.scratch-uma/s18d/sweep.json` (1,419,892 bytes, written 2026-11-17 22:42:18):
- `expected: 816, skipped: 2, unexpected: 58, workers: 1`
- Walking every suite/spec/test node: **876 tests, 2 skipped, 58 unexpected**
- Arithmetic: **876 − 2 − 58 = 816 passed**

**`816` was the pass count, never the case count.** The case count is **876** across **125 distinct spec files**.

The figure **884** (requested for reconciliation) matches nothing measurable and should be dropped.

### 2.4 58 failures classified into 8 classes

| Class | n | Specimens | Verdict |
|---|---|---|---|
| A. Diagnostic probe leaked into suite | 4 | `flash-name-{first,fix,probe,probe-settled}.spec.ts` | Probes, not specs. All four deleted by peer commit `f7f1c25c` |
| B. Environment-dependent | 1 | `nativephp-packaged.spec.ts:5` | Requires `NATIVEPHP_EXE_PATH`; legitimate skip |
| C. 180s timeout | 6 | `career-deck-summary:261`, `catalog-to-wizard:11`, `rice-shower-unity-cup:{64,105,186}`, `scratch-phase1-screenshots:56` | Contention-shaped; box loaded (231 dirty entries during run) |
| D. Stale spec vs signed design | 6 | `page-header.spec.ts:34` × 6 routes | Spec wrong, component right |
| E. Wireframe parity drift | 3 | `career-deck-select:147`, `database-parity:{219,303}` | Real, still open |
| F. Focus assertion | 4 | `career-legacy-deck-steps:269`, `career-scenario-select:122`, `career-surface-cutover:130`, `career-trainee-select:431` | Real, still open |
| G. Infrastructure | 0 | — | None |
| H. Other | remainder | — | See source for full inventory |

Full per-case inventory: `.scratch-uma/s18d/FAILURES.md`.

### 2.5 Patch forensics and provenance (B6)

Every commit on this branch is authored `Hermes Agent <hermes@local>` — a shared identity. Commit metadata cannot attribute work to a session. Session attribution is verifiable only via `.scratch-uma/<session>/` artefacts, private-index message files, or mnemon entries.

Peer commit inventory from `be8893a2..HEAD 816e923e` (21 commits shown, 38 ahead total):
- `58c4f71b` — fan ladder ceiling band answers null
- `90e18ac1` — fan ladder docs restated
- `7e34a711` — snapshot carries trainee rarity and potential level
- `f4e7adc6` — stat ring, sparkline and shipped-copy truth fixtures
- `e57e3b79` / `b86d4667` / `39a28376` — audit UX walkthroughs / reconciliation / capability matrix trees
- `ddb87e40` / `1dd80554` / `7d9b60a3` — browser observation trees (2026-11-15, -14)
- `98c6e3b2` — dated roadmap tree
- `0cde2fae` — prior sitreps landed
- `58f32e5d` — dated plan tree
- `13ad587b` — retired rice-shower Unity Cup reconciliation and UX-walk docs (peer)
- `e5fc0e1c` — removed jev-inspect subtree (peer)
- `cfd8bd78` — roadmap slices 0.2–4.3 landed
- `2546689e` — md_fence_check no longer silently checks nothing (peer)
- `f7f1c25c` — housekeeping: duplicate capability matrix, retired spec, guard files
- `a5a87e9c` — compacted AGENTS.md
- `f82b6bd0` — Inertia v3, polyfills, overlay-shadow token
- `6634925c` — dropped pre-native-build hook (later restored)
- `816e923e` — KI-160/KI-166 reflow and AUD-20261118-001 reconciliation (**HEAD**)

### 2.6 Review.spec.ts closeout (B3)

Two stale assertions, not regressions:
- `:126` — pager pinned to viewport after 900px wheel; `661527e1d` (2026-11-11, UX-07) removed `sticky bottom-0` deliberately (containment kills sticky). Rewritten to pin pager renders after last queue row.
- `:332` — wanted `pill-caution` pill; corrected to `pill-neutral` deviation.

### 2.7 ADR token corrections (B3/B4)

| ADR | Token change | Ruling |
|---|---|---|
| ADR-0019 | `proposed` → `advisory` | Corrected 2026-10-23 owner ruling; dated ledger entry |
| ADR-0028 | `proposed` → `accepted (2026-10-22)` | Dated ledger entry |
| ADR-0029 | `code-open` → `four built` | A-0.4 at `550fd542` via `SupportCardEffects::forLevel()/atLevel()/valueAt()`; A-0.12 awaits owner ruling |
| ADR-0030 | → `built` | Measured 263/263 provenance rows non-null |

ADR index (`docs/adr/README.md`) rows 0019/0028/0029/0030 updated to match corrected contract tokens.

### 2.8 Session handoff state (B5)

- HEAD: `816e923e`, master ahead 38 / behind 8 vs `origin/master`
- Working tree: 12 modified / 1 deleted / 4 untracked paths / 10 untracked files; index empty
- Stashes: 6 peer/foreign — not adopted, not committed
- Tags: 0.1.0, 2.0.1, 2.0.2, 2.1.0, 2.1.1; no `3.0.*` tag
- No full suite (Pest or Playwright) verified green at HEAD `816e923e`
- Browser artifacts classified: `p5.json` failed results split into connection-refused (infra) vs real assertion failures

### 2.9 Divergence

`git rev-list --left-right --count origin/master...HEAD` → **8 behind / 38 ahead**. `git cherry` refines: **36 unique to local, 2 carried upstream by patch-id**. Branch has diverged; ahead-only or behind-only framing hides half. Merge is default; rebase needs owner ruling.

---

## 3. Cross-stream notes relevant to consolidation

- The OPEN-count conflict (0 vs 10 vs 53) is a **measurement-method** conflict, not a data conflict. All three figures are correct under their respective counting rules. The reconciliation ledger in `CONSOLIDATION-MANIFEST.md` records all three.
- Peer commits landed between every measurement; figures are timestamps, not constants.
- Nothing was committed, staged, or pushed by any session in this group except peer commits (all documented).