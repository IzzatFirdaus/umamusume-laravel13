# SITREP Index — `docs/sitrep/`

**Navigation order:** `AGENTS.md` §3 → this file → consolidated reports → archive

This directory holds session reports and handoffs. Consolidated workstream reports are in `consolidated/`. Original reports are preserved in `archive/` by date. Audit-owned sitreps remain in `docs/audits/` and are not listed here.

---

## Consolidated workstream reports (start here)

| # | Report | Workstream | Source SITREPs | Date range |
|---|---|---|---|---|
| 1 | [`consolidated/release-roadmap-and-tag-hold.md`](consolidated/release-roadmap-and-tag-hold.md) | Release readiness, audit remediation, tag hold | 5 | 2026-11-16 → 2026-11-18 |
| 2 | [`consolidated/register-sweeps-and-handoffs.md`](consolidated/register-sweeps-and-handoffs.md) | Register closure, browser sweeps, patch forensics | 6 | 2026-11-16 → 2026-11-18 |
| 3 | [`consolidated/mwg-roadmap-recovery.md`](consolidated/mwg-roadmap-recovery.md) | MWG adoption, roadmap authoring, recovery gate | 2 | 2026-11-18 |
| 4 | [`consolidated/visual-overhaul-and-identity.md`](consolidated/visual-overhaul-and-identity.md) | Visual overhaul V1–V5, W1–W6, brand/palettes | 3 | 2026-11-16 → 2026-11-19 |
| 5 | [`consolidated/frontend-implementation-verification.md`](consolidated/frontend-implementation-verification.md) | Frontend slices, UI/UX, rendered verification, integration | 5 | 2026-11-20 → 2026-11-23 |
| 6 | [`consolidated/advisor-ai-integration.md`](consolidated/advisor-ai-integration.md) | Advisor AI audit reconciliation, backend contract, full-stack | 5 | 2026-11-22 → 2026-11-23 |
| 7 | [`consolidated/backend-support-telemetry.md`](consolidated/backend-support-telemetry.md) | Telemetry, supporting backend slices, Hermes bento | 2 | 2026-11-22 → 2026-11-23 |

**Consolidation manifest:** [`CONSOLIDATION-MANIFEST.md`](CONSOLIDATION-MANIFEST.md) — source mapping table, SHA-256 hashes, conflict reconciliation ledger, archive structure.

---

## Active acceptance trackers (not yet consolidated)

| Tracker | Workstream | Date |
|---|---|---|
| [`DB-SAFETY-ACCEPTANCE-2026-11-25.md`](DB-SAFETY-ACCEPTANCE-2026-11-25.md) | Cross-agent database safety, eight checks | 2026-11-25 |
| [`SITREP-P0-DB-SAFETY-RECONCILIATION-2026-11-27.md`](SITREP-P0-DB-SAFETY-RECONCILIATION-2026-11-27.md) | P0 reconciliation of MCP #132–#141: genuine read access, no established modification, candidate identity drift, `UMA_CANONICAL_DATABASE` identity confusion, WAL-vs-hash limit | 2026-11-27 |
| [`2026-11-27-advisor-infrastructure-qualification.md`](2026-11-27-advisor-infrastructure-qualification.md) | Advisor infrastructure qualification: 81 tests / 325 assertions (attributed), corrected `terminate()` telemetry lifecycle, revision-gap and evaluator findings | 2026-11-27 |
| [`SITREP-RESEARCH-ATTRIBUTION-2026-11-27.md`](SITREP-RESEARCH-ATTRIBUTION-2026-11-27.md) | Research attribution correction (#101–#110 = docs, not deep-research), `ux` identity, three undelivered Advisor artifacts | 2026-11-27 |

---

## Archive (original 28 SITREPs, preserved by date)

### 2026-11-16 (6 files)
- `archive/2026-11-16/sitrep-roadmap-remediation-2026-11-16.md`
- `archive/2026-11-16/sitrep-roadmap-remediation-2026-11-16-session2.md`
- `archive/2026-11-16/sitrep-roadmap-remediation-2026-11-16-session3.md`
- `archive/2026-11-16/sitrep-docs-isolation-tag-hold-2026-11-16.md`
- `archive/2026-11-16/sitrep-visual-overhaul-2026-11-16.md`
- `archive/2026-11-16/sitrep-visual-overhaul-2026-11-16-close.md`

### 2026-11-17 (2 files)
- `archive/2026-11-17/sitrep-2026-11-17-phase-a-register-closeout.md`
- `archive/2026-11-17/sitrep-2026-11-17-s1-s2-wrap.md`

### 2026-11-18 (8 files)
- `archive/2026-11-18/sitrep-2026-11-18-hermes-mwg-recovery-session.md`
- `archive/2026-11-18/sitrep-2026-11-18-mwg-phase3-roadmap-forensics.md`
- `archive/2026-11-18/sitrep-2026-11-18-remaining-work-instruction-set.md`
- `archive/2026-11-18/sitrep-session-handoff-2026-11-16-to-18.md`
- `archive/2026-11-18/sitrep-2026-11-18.md`
- `archive/2026-11-18/sitrep-2026-11-18-session-handoff.md`
- `archive/2026-11-18/sitrep-2026-11-18-sweep-reconciliation-and-patch-forensics.md`

### 2026-11-19 (1 file)
- `archive/2026-11-19/sitrep-2026-11-19-visual-identity-session-handoff.md`

### 2026-11-20 (1 file)
- `archive/2026-11-20/sitrep-frontend-slices-2026-11-20.md`

### 2026-11-21 (2 files)
- `archive/2026-11-21/sitrep-2026-11-21-frontend-uiux-session-handoff.md`
- `archive/2026-11-21/sitrep-frontend-completion-2026-11-21.md`

### 2026-11-22 (3 files)
- `archive/2026-11-22/sitrep-frontend-rendered-verification-2026-11-22.md`
- `archive/2026-11-22/sitrep-advisor-ai-reconciliation-2026-11-22.md`
- `archive/2026-11-22/sitrep-telemetry-supporting-backend-2026-11-22.md`

### 2026-11-23 (6 files)
- `archive/2026-11-23/sitrep-frontend-integration-signoff-2026-11-23.md`
- `archive/2026-11-23/sitrep-hermes-advisor-ai-integration-2026-11-23.md`
- `archive/2026-11-23/sitrep-hermes-backend-verification-2026-11-23.md`
- `archive/2026-11-23/sitrep-advisor-ai-frontend-2026-11-23.md`
- `archive/2026-11-23/sitrep-advisor-fullstack-2026-11-23.md`
- `archive/2026-11-23/sitrep-backend-hermes-bento-support-2026-11-23.md`

---

## Audit-owned sitreps (NOT in this directory)

| Path | Note |
|---|---|
| `docs/audits/2026-11-22-advisor-ai-audit/18-sitrep.md` | Advisor AI audit, reconciliation in consolidated report #6 |
| `docs/audits/browser-observation-2026-11-15-followup/09-sitrep.md` | Browser observation followup |

---

## Related documentation

| Directory | Index |
|---|---|
| `docs/audits/` | [`docs/audits/INDEX.md`](../audits/INDEX.md) |
| `docs/roadmap/` | [`docs/roadmap/INDEX.md`](../roadmap/INDEX.md) |
| `docs/plan/` | [`docs/plan/INDEX.md`](../plan/INDEX.md) |
| `docs/adr/` | [`docs/adr/README.md`](../adr/README.md) |
| `docs/research-scratch/` | [`docs/research-scratch/GOVERNANCE.md`](../research-scratch/GOVERNANCE.md) |

---

**Total original SITREPs consolidated:** 28  
**Consolidated workstream reports:** 7  
**Original reports archived:** 28 (100%, with SHA-256 preserved)  
**Audit-owned reports excluded from consolidation:** 2 (remain in place)