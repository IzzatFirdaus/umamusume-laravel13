# docs/audits — Audit Index

Central index for all audit artifacts in this repository. Organized by audit type and date.

## Directory Structure

| Category | Path | Description |
|----------|------|-------------|
| **Reconciliation Register** | [`reconciliation-register/`](reconciliation-register/) | Master register of all reconciliation rounds |
| **Reconciliation Rounds** | [`reconciliation-rounds/`](reconciliation-rounds/) | Individual Rice Shower Unity Cup reconciliation reports (rounds 2–7) |
| **UX Walkthroughs** | [`ux-walkthroughs/`](ux-walkthroughs/) | Step-by-step UX walkthroughs and resolutions for Rice Shower Unity Cup |
| **Capability Matrix** | [`capability-matrix/`](capability-matrix/) | Capability matrix for Rice Shower Unity Cup |
| **MWG Audit** | [`mwg-audit/`](mwg-audit/) | Modern Web Guidance audit report |
| **Grey Parity** | [`grey-parity/`](grey-parity/) | Grey/parity color audit (executive summary, measurements, recommendations) |
| **Browser Observations** | [`browser-observation-2026-11-14/`](browser-observation-2026-11-14/) | Browser test observation — 2026-11-14 |
| | [`browser-observation-2026-11-15/`](browser-observation-2026-11-15/) | Browser test observation — 2026-11-15 |
| | [`browser-observation-2026-11-15-followup/`](browser-observation-2026-11-15-followup/) | Follow-up corrections & verification — 2026-11-15 |
| **RS-UC Dated Audits** | [`RS-UC-2026-10/`](RS-UC-2026-10/) | Rice Shower Unity Cup audit — October 2026 (screenshots) |
| | [`RS-UC-2026-11-snapshot-door/`](RS-UC-2026-11-snapshot-door/) | Snapshot Door feature audit — November 2026 |
| | [`RS-UC-2026-12/`](RS-UC-2026-12/) | Rice Shower Unity Cup audit — December 2026 |
| | [`RS-UC-2026-13/`](RS-UC-2026-13/) | Rice Shower Unity Cup audit — January 2027 |
| **Scenario Detail** | [`RS-UC-SCENARIO-DETAIL-2026-11-09/`](RS-UC-SCENARIO-DETAIL-2026-11-09/) | Scenario detail audit — 2026-11-09 |
| **Trainer Desk v3 UX** | [`trainer-desk-v3-ux/`](trainer-desk-v3-ux/) | Trainer Desk v3 UX audit with scenario research |
| | [`trainer-desk-v3-ux-retest/`](trainer-desk-v3-ux-retest/) | Retest screenshots for v3 UX |
| **Advisor AI** | [`2026-11-22-advisor-ai-audit/`](2026-11-22-advisor-ai-audit/) | Advisor integration audit and [JSON response contract](2026-11-22-advisor-ai-audit/advisor-api-contract.json) |

## Navigation

- **Start here**: [`reconciliation-register/RECONCILIATION-REGISTER.md`](reconciliation-register/RECONCILIATION-REGISTER.md) — master register linking all rounds
- **Latest reconciliation**: [`reconciliation-rounds/rice-shower-unity-cup-round7-reconciliation.md`](reconciliation-rounds/rice-shower-unity-cup-round7-reconciliation.md)
- **Latest UX walkthrough**: [`ux-walkthroughs/rice-shower-unity-cup-ux-walk-8.md`](ux-walkthroughs/rice-shower-unity-cup-ux-walk-8.md)
- **Grey parity decision**: [`grey-parity/06-final-signoff.md`](grey-parity/06-final-signoff.md)
- **README**: [`README.md`](README.md) — original directory overview

## Naming Convention

- Directory names use kebab-case with dates: `browser-observation-YYYY-MM-DD`
- Report files use descriptive names with round/version numbers
- Screenshot directories live inside their parent audit directory under `screens/` or `screenshots/`

## Maintenance

When adding new audits:
1. Create a dated directory under `docs/audits/`
2. Add an entry to this INDEX.md
3. Include a `00-coverage.md` or `README.md` inside the audit directory
4. Keep screenshots in `screens/` or `screenshots/` subdirectory