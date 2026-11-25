# Consolidated SITREP — Modern Web Guidance Roadmap & Recovery (Workstream Group C)

**Generated:** consolidation of 2 source SITREPs  
**Scope:** MWG adoption, roadmap/plan authoring, separation from peer pairs, recovery gate  
**Source files:** 2 reports from 2026-11-18  
**Disposition:** Standalone workstream report; not a master SITREP; original sources archived in `docs/sitrep/archive/`

---

## 1. Source inventory

| # | Source SITREP | Date | SHA-256 | Role in consolidation |
|---|---|---|---|---|
| C1 | `sitrep-2026-11-18-hermes-mwg-recovery-session.md` | 2026-11-18 | `ab51e06801f7553b92a154f9a10ff13fb0a42739c6d5afd300476e7e3818be5e` | Roadmap authoring, MWG adoption, recovery gate, 3 commits |
| C2 | `sitrep-2026-11-18-mwg-phase3-roadmap-forensics.md` | 2026-11-18 | `488f63eec6084535b50ca805a62ed8d22cbee5f768dec6dc63be4519c1a848fd` | MWG Phase 3 slices 3.1–3.4, roadmap/plan pair superseded, P1.1 forensics |

---

## 2. Consolidated findings

### 2.1 Roadmap/plan pair (C1/C2)

Two files authored and revised against measured state:

| File | Lines | State |
|---|---|---|
| `docs/roadmap/implementation-roadmap-hermes-2026-11-16.md` | 379 | Tracked at HEAD (added by `98c6e3b2`), fence-clean; since moved to `docs/roadmap/archive/` by peer work |
| `docs/plan/implementation-plan-hermes-2026-11-16.md` | 316 | Tracked at HEAD (added by `58f32e5d`), fence-clean |

Phase structure: **R0 baseline → R1 bank → R2 register truth → R3 suite truth → R4 CVE → R5 feature residuals → R6 scenarios → R7 release**, with R4/R5 hanging off R1/R3.

**Separation executed (Objective B):** The earlier unsuffixed pair (`docs/roadmap/implementation-roadmap.md`, `docs/plan/implementation-plan.md`) was renamed to the `-hermes-2026-11-16` names; old paths deleted. The "single canonical roadmap" claim and cross-pair references were removed. Both files state they stand alone; cross-session facts live in mnemon only.

**Pair later SUPERSEDED:** Both files carry `> **SUPERSEDED 2026-11-16**` markers at line 3, added by a peer regeneration. The peer-rewritten pair is 537 + 549 lines (vs 379 + 316 originally).

### 2.2 MWG Phase 3 slices (C2)

| Slice | What was done | Verification | State |
|---|---|---|---|
| 3.1 | Closed before this window (CLS 0.00000 over 15 navigations; 0.1319 fixture proved instrument could see layout shift) | Prior window | Closed, no code |
| 3.2 | Form-capture hints across files | Measurement | Closed |
| 3.3 | One file | Measurement | Closed |
| 3.4 | Measured no-change | Measurement | No change needed |

`legacy-scan` coverage stated at ~20% — caveat recorded; clean scan not treated as compliance. Four owner rulings (later six) gate parts of MWG adoption.

### 2.3 Five claims falsified by measurement (C1/C2)

| # | Original claim | Measured truth | Disposition |
|---|---|---|---|
| 1 | "No factory produces BO-003" | `database/factories/UmamusumeFactory.php:27` mints name via `Str::title(fake()->unique()->words(2, true))`; `:33` hard-codes `release_status` to `GlobalReleased` | Rewritten; open decision (purge or ignore) retained |
| 2 | `CareerView/Cockpit.vue` exists | No such directory; correct path `resources/js/pages/Career/Cockpit.vue:632` | Corrected |
| 3 | "HEAD equals `origin/master`" | `git branch -vv` read **ahead 4 of `10dee9d7`** | Corrected |
| 4 | KI counts "37 headings / 6 open" | Measured **112 headings / 29 open** at HEAD; **114 / 31** in worktree; delta = exactly two worktree-only entries | Corrected |
| 5 | Stale 88-row porcelain histogram | Invalidated mid-session (88 → 175 → 196) | Relabelled "moving — re-measure" |

### 2.4 Additional measured findings (C1/C2)

- **R3.9:** No browser spec reached `Veterans/TransferRequests.vue`; `grep -rln 'veterans/transfers\|TransferRequests' HEAD -- tests/browser` → 0.
- **R7 skip-vs-ran condition** added for the contrast arm.
- KI counts from worktree parse: 41 `## KI-` headings, **27** carrying `Status: OPEN`.
- Migration count discrepancy context: Session-1 reported 81 migrations; Session-3 corrected to 80 (see Group A consolidation).

### 2.5 Recovery gate (C1)

**Objectives D:** Recover seven commits dropped by alleged 2026-11-16 history rewrite:
- **G1:** Preserve peer's unstaged work first.
- **G3:** Recover onto throwaway worktree.
- **G4:** Reset and 3-way apply.
- **G5:** Verify.
- **G6:** Write ledger S70.
- **G7:** Write roadmap status line.

Three commits carry provenance files under `.scratch-uma/mwg-2026-11-16/` and `.scratch-uma/recovery-2026-11-16/`.

### 2.6 Commits attributed to this lineage (C1)

| Commit | Message | Files | Why |
|---|---|---|---|
| `f8e428d9` | feat(forms): adopt MWG form-capture hints across the 17 audited files | 17 files | MWG form-capture adoption |
| `25049095` | (peer component migration) | ProvenanceMark `role="img"` → `interestfor` button | Peer landed component migration; this session's 44px hit-area class rode on same file |
| `c41abbb8` | docs(ci): record packaged main-process output and probe native:config startup | CI docs | Packaged output recording |

**Provenance warning:** Every commit on this branch is authored `Hermes Agent <hermes@local>`, a shared identity. Commit metadata cannot attribute work to a session. Three commits in this lineage carry session-specific artefacts under `.scratch-uma/`.

### 2.7 Tree state at report time (C1/C2)

- HEAD: `816e923e`, ahead 38 / behind 8 vs `origin/master`
- Working tree: 24 → 34 rows during session; peer rows only at report time
- Stashes: 6
- Nothing staged, committed, pushed, branched, reset, or tagged by C2 session; C1 made 3 commits (all ancestors of HEAD)

---

## 3. Cross-stream notes relevant to consolidation

- `docs/sitrep/` directory promotion: commit `2be83336` added `docs/sitrep/` to AGENTS.md §3 map (one row added after `PRODUCT.md`). This was later reverted by a peer commit — see Group B consolidation for the hazard (peer's working-tree copy predates this commit, so their rewrite drops the row).
- No application source, frontend component, backend route, migration, config, dependency, or test was modified outside the documented file changes.
- Nothing was committed, staged, or pushed by C2; C1's 3 commits are documented above.