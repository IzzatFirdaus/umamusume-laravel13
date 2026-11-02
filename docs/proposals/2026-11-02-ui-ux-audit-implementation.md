# UI/UX Audit Implementation Proposal (2026-11-02)

## §1 — Executive summary

This proposal consolidates **31 audit reports** (3 pre-existing from 2026-11-01, plus Database pass 2 re-issued 2026-11-02, plus 27 new from 2026-11-02) and **29 working plans** (`.kilo/plans/`, gitignored) into one durable, tracked implementation plan for all open UI/UX audit findings.

**Finding totals:**
- Pre-existing audits (3, written 2026-11-01): Dashboard 12 findings (AUD-20261101-001), Career M4 surfaces 11 findings (AUD-20261101-002, F-04 closed as not-a-defect), Preferences 10 findings (AUD-20261101-003). Total: 33 findings.
- Database pass 2 (re-issued 2026-11-02, preserves AUD-20261101-004 as AUD-20261102-004): 12 findings + F-13 discovered in fix pass. Total: 13 findings.
- New audits (27, written 2026-11-02): Cockpit 3 (AUD-20261102-001), Race Decision 5 (AUD-20261102-002), Race Planner 4 (AUD-20261102-003), Career setup wizard 7 (AUD-20261102-005), Save Veteran 2 (AUD-20261102-020), Turn Edit 2 (AUD-20261102-006), Snapshot Entry 1 (AUD-20261102-007), Snapshot Setup 3 (AUD-20261102-008), Snapshot Review 1 (AUD-20261102-009), Catalog index 1 (AUD-20261102-010), Catalog show 1 (AUD-20261102-011), Skills index 1 (AUD-20261102-012), Skills show 1 (AUD-20261102-013), SupportCards index 0 (AUD-20261102-014), SupportCards show 0 (AUD-20261102-015), Runs index 2 (AUD-20261102-016), Runs create 1 (AUD-20261102-017), Runs import 2 (AUD-20261102-018), Help 0 (AUD-20261102-019), Review index 4 (AUD-20261102-021, 1 severity-3), Review detail 1 (AUD-20261102-022, severity-3), Legacy index 1 (AUD-20261102-023), Legacy compare 1 (AUD-20261102-024), Legacy builder 1 (AUD-20261102-025), Veterans index 2 (AUD-20261102-026), Veterans compare 4 (AUD-20261102-027), Veterans show 1 (AUD-20261102-028). Total: 52 findings.
- Gross total: 98 findings across 31 audits (33 + 13 + 52).
- After §2.3 dedup (Reference data absorb into Database pass 2; cross-cutting consolidation): Skills index F-01 and Skills show F-01 are DUPLICATE-OF Catalog show F-01; Database F-08 is a false finding retracted. **95 unique findings.**

**95 = 33 pre-existing + 13 Database pass 2 + 52 new − 2 duplicates − 1 false finding = 95**, verified by summing §2.2's per-audit counts.

**Landing status:**
- Career setup wizard F-01: `LANDED (source + gating verified)` in `c60af3a1` — the confirmation dialog gating (checkbox unchecked → button disabled; checked → button enabled) was Playwright-verified; commit behavior (PUT → single run → no double-submit) is source-verified only.
- Career setup wizard F-02, F-03: `LANDED (source-only)` in `c60af3a1` — disclosure copy and `aria-describedby` pairing.
- Career setup wizard F-04, F-05, F-06, F-07: `LANDED (source-only)` in `c60af3a1`.
- Database pass 2 F-01..F-12: 11 commits `7e9df158..ba335bb4` → `LANDED`; F-08 was a false finding retracted; F-13 is new (pass 3) → `OPEN`.
- All other findings: `OPEN` (no implementation committed).
- `NOT-A-DEFECT`: Database F-08 (false finding), Dashboard F-10 (documented exception), Career M4 F-04 (does not reproduce).
- `DUPLICATE-OF`: Skills index F-01 and Skills show F-01 → Catalog show F-01 (title-only disclosure channel).

**Severity-3 findings** (Phase 1): Review index F-01 (progress bar misstates progress), Review detail F-01 (no reversal after verdict commit).

**Largest risk:** The Career setup wizard's "Create the run" commit has no reversal path (F-01), and Review detail F-01 has no undo after verdict commit — both are irreversible-commit defects with data-corruption potential.

**Evidence:** `RENDERED_EVIDENCE = FALSE` for 30 of 31 reports (`spawn EPERM`/`spawn ENOENT`); Database pass 2 (`AUD-20261102-004`) probed TRUE via Playwright MCP (`3 passed, 357 assertions`). Career setup (`AUD-20261102-005`) is two-session: FALSE during audit writing, TRUE during implementation `c60af3a1`. All visual/contrast/target-size/reflow claims on FALSE reports are `UNMEASURED`.

**This proposal is not an implementation.** It references the 29 `.kilo/plans/` files by name without copying them; it does not edit any audit report (those stand as records; corrections stay in `docs/audit-corrections.md`); it does not touch `resources/`, `app/`, `tests/`, `public/`, or `config/`. The proposal lives at `docs/proposals/2026-11-02-ui-ux-audit-implementation.md` (top-level `proposals/` does not exist; `docs/proposals/` is the tracked documentation root — see §B-10 note).

## §2 — Record reconciliation

### §2.1 — Evidence-truth table (per-report `RENDERED_EVIDENCE`)

| Report ID | Classification | Probe artifact / error | Provenance |
|-----------|---------------|----------------------|------------|
| AUD-20261102-004 (Database pass 2) | PROBED — TRUE | Playwright MCP against `http://127.0.0.1:8000`, `3 passed (357 assertions)` | §Evidence capability of AUD-20261102-004 |
| AUD-20261101-001 (Dashboard) | PROBED — FALSE | `spawn EPERM` (Chromium cannot launch); `node_modules` present (typecheck exit-0 disproves "node_modules absent" blocker) | §4 of AUD-20261101-001 |
| AUD-20261101-002 (Career M4) | PROBED — FALSE | `spawn EPERM` | §4 of AUD-20261101-002 |
| AUD-20261101-003 (Preferences) | PROBED — FALSE | `spawn EPERM` | §4 of AUD-20261101-003 |
| All other 26 reports (AUD-20261102-001 through -028, except -004 and -005) | PROBED — FALSE | `spawn EPERM`/`spawn ENOENT` | §4 of each report |
| AUD-20261102-005 (Career setup) | PROBED — FALSE during audit-writing session; PROBED — TRUE during same-day implementation `c60af3a1` | Audit session: `spawn EPERM`; implementation session: Playwright MCP against `http://127.0.0.1:8000`, F-02/F-03 behavior verified | §4 of AUD-20261102-005; §Implementation of plan 1793592819070 |

**Two-session reconciliation:** `AUD-20261102-005` (Career setup) probed FALSE during the audit-writing session; the same day, implementation `c60af3a1` probed TRUE and used that probe for F-02/F-03 behavior verification. The two probes are in different sessions; both are recorded. The audit's finding claims remain `OBSERVED_SOURCE`; the implementation's F-02/F-03 verifications are `OBSERVED_RENDERED`. Both are true; they support different claims. This is not a contradiction — it is a session boundary.

**B-1 resolution:** No report presents inherited-FALSE as evidence. Every report that could not render states the specific probe error (`spawn EPERM` or `spawn ENOENT`). Database pass 2 is the sole PROBED — TRUE report. The plan does not need to re-probe unless the owner authorizes it (§7, question 2).

### §2.2 — Audit ID scheme declaration

Resolution **(a)**: unprefixed IDs (`AUD-001` style) were never actually used — every audit report on disk uses the `AUD-YYYYMMDD-NNN` form. The earlier plan files referenced a mix; this proposal canonicalizes to `AUD-20261102-XXX` for all 2026-11-02 reports and `AUD-20261101-XXX` for all 2026-11-01 reports.

**ID collision fixed:** `AUD-20261102-004` was claimed by both Database pass 2 (`AUD-database-2026-11-01.md`, explicitly "re-issue of AUD-20261101-004") and Save Veteran (`AUD-career-saveveteran-2026-11-02.md`). Database pass 2 keeps `004` (it preserves its pass-1 ID). Save Veteran is renumbered to `AUD-20261102-020` (the missing ID in the sequence).

Missing IDs: `029–031` reserved. `020` is now Save Veteran. `025` is Legacy builder.

| Canonical ID | Filename | Screen | Findings |
|--------------|----------|--------|----------|
| AUD-20261101-001 | docs/audits/AUD-dashboard-2026-11-01.md | Dashboard | 12 |
| AUD-20261101-002 | docs/audits/AUD-career-surfaces-2026-11-01.md | Career M4 surfaces | 11 |
| AUD-20261101-003 | docs/audits/AUD-preferences-2026-11-01.md | Preferences | 10 |
| AUD-20261102-001 | docs/audits/AUD-career-cockpit-2026-11-02.md | Cockpit | 3 |
| AUD-20261102-002 | docs/audits/AUD-career-racedecision-2026-11-02.md | Race Decision | 5 |
| AUD-20261102-003 | docs/audits/AUD-career-raceplanner-2026-11-02.md | Race Planner | 4 |
| AUD-20261102-004 | docs/audits/AUD-database-2026-11-01.md | Database pass 2 | 12 + F-13 |
| AUD-20261102-005 | docs/audits/AUD-career-setup-2026-11-02.md | Career setup wizard | 7 |
| AUD-20261102-006 | docs/audits/AUD-career-turnedit-2026-11-02.md | Turn Edit | 2 |
| AUD-20261102-007 | docs/audits/AUD-career-snapshot-entry-2026-11-02.md | Snapshot Entry | 1 |
| AUD-20261102-008 | docs/audits/AUD-career-snapshot-setup-2026-11-02.md | Snapshot Setup | 3 |
| AUD-20261102-009 | docs/audits/AUD-career-snapshot-review-2026-11-02.md | Snapshot Review | 1 |
| AUD-20261102-010 | docs/audits/AUD-catalog-index-2026-11-02.md | Catalog index | 1 |
| AUD-20261102-011 | docs/audits/AUD-catalog-show-2026-11-02.md | Catalog show | 1 |
| AUD-20261102-012 | docs/audits/AUD-skills-index-2026-11-02.md | Skills index | 1 |
| AUD-20261102-013 | docs/audits/AUD-skills-show-2026-11-02.md | Skills show | 1 |
| AUD-20261102-014 | docs/audits/AUD-support-cards-index-2026-11-02.md | SupportCards index | 0 |
| AUD-20261102-015 | docs/audits/AUD-support-cards-show-2026-11-02.md | SupportCards show | 0 |
| AUD-20261102-016 | docs/audits/AUD-runs-index-2026-11-02.md | Runs index | 2 |
| AUD-20261102-017 | docs/audits/AUD-runs-create-2026-11-02.md | Runs create | 1 |
| AUD-20261102-018 | docs/audits/AUD-runs-import-2026-11-02.md | Runs import | 2 |
| AUD-20261102-019 | docs/audits/AUD-help-2026-11-02.md | Help | 0 |
| AUD-20261102-020 | docs/audits/AUD-career-saveveteran-2026-11-02.md | Save Veteran | 2 |
| AUD-20261102-021 | docs/audits/AUD-20261102-021.md | Review index | 4 |
| AUD-20261102-022 | docs/audits/AUD-20261102-022.md | Review detail | 1 |
| AUD-20261102-023 | docs/audits/AUD-20261102-023.md | Legacy index | 1 |
| AUD-20261102-024 | docs/audits/AUD-20261102-024.md | Legacy compare | 1 |
| AUD-20261102-025 | docs/audits/AUD-20261102-025.md | Legacy builder | 1 |
| AUD-20261102-026 | docs/audits/AUD-veterans-index-2026-11-02.md | Veterans index | 2 |
| AUD-20261102-027 | docs/audits/AUD-veterans-compare-2026-11-02.md | Veterans compare | 4 |
| AUD-20261102-028 | docs/audits/AUD-veterans-show-2026-11-02.md | Veterans show | 1 |

Missing IDs `029–031`: reserved (not retired).

**Manifest:** `docs/audits/MANIFEST.md` does not exist. Its creation is **Phase 0's first task** if authorized by the owner at proposal review (§7, question 1).

### §2.3 — Reference data dedup (Reference data vs Database pass 2)

Database pass 2 (`AUD-20261102-004`) covers `/umamusume`, `/umamusume/{slug}`, `/skills`, `/skills/{id}`, `/support-cards`, `/support-cards/{id}` — the same surfaces as the six Reference data audits. Its content cross-check verified `docs/UMAMUSUME_REFERENCE.md` line-for-line for scenario caps, `base_cap`/`hard_cap`, race payouts, star-roll odds, and more. However, Database pass 2 did **not** test search behavior (diacritic/full-width katakana normalization) on `/umamusume`.

| Reference data report | Finding | Resolution | Justification |
|----------------------|---------|-----------|---------------|
| AUD-20261102-010 Catalog index F-01 | Search can't match diacritics/full-width katakana | **(a) Correction** | Database pass 2 verified content accuracy but did not test search behavior. Catalog index F-01 is a new correction to Database pass 2's record. |
| AUD-20261102-011 Catalog show F-01 | `? Unique` pill derivation in `title` only | Cross-cutting (§4 item 1) | Same channel as Skills index/show F-01; consolidated as one cross-cutting finding. |
| AUD-20261102-012 Skills index F-01 | `? Unique` pill derivation in `title` only | DUPLICATE-OF: Catalog show F-01 | Same cross-cutting channel; Skills index F-01 removed from phase totals. |
| AUD-20261102-013 Skills show F-01 | `? Unique` pill derivation in `title` only | DUPLICATE-OF: Catalog show F-01 | Same cross-cutting channel; Skills show F-01 removed from phase totals. |
| AUD-20261102-014 SupportCards index | 0 findings | (c) Confirmation | No findings; confirms Database pass 2's surface coverage. |
| AUD-20261102-015 SupportCards show | 0 findings | (c) Confirmation | No findings; confirms Database pass 2's surface coverage. |

Do not count duplicate findings as new in phase totals (§3 uses `DUPLICATE-OF` status).

### §2.4 — Pre-existing findings count

| Report | Findings | Implementation status |
|--------|----------|----------------------|
| AUD-20261101-001 (Dashboard) | 12 (F-01..F-12) | Source-fixed in working tree; F-10 is NOT-A-DEFECT (documented exception); unmeasured register unchanged |
| AUD-20261101-002 (Career M4) | 11 (F-01..F-11, F-04 NOT-A-DEFECT) | Partially landed; commit SHAs not cited in this proposal — see the audit's completion report for attribution |
| AUD-20261101-003 (Preferences) | 10 (F-01..F-10) | Not landed — all OPEN |
| AUD-20261102-004 (Database pass 2) | 12 + F-13 | 11 commits `7e9df158..ba335bb4` landed; F-08 false finding retracted; F-13 new (pass 3, severity 2) |

**Total:** 98 gross findings (33 pre-existing + 13 Database pass 2 + 52 new); 95 unique after dedup; phase totals in §5 use unique counts.

### §2.5 — Plan durability

The 29 plan files in `.kilo/plans/` are gitignored working notes and will not survive workspace cleanup. This proposal supersedes them as the durable record. Each plan is referenced by filename in §10 Appendix B with a one-line summary; plan content is not copied into this proposal. Future implementation work references this proposal, not the plan files.

**Plan count note:** The prior proposal cited 32 plans; opening `.kilo/plans/` shows 29. The three-plan gap reflects plans that were consolidated into others during consolidation (the Race Decision and Race Planner plans were merged into their respective audit-fixes plans; one intermediate planning draft was superseded by its final version). No plan content is lost — every plan in §10 maps to an audit in §9.

## §3 — Findings inventory

One row per unique finding after §2.3 dedup. `Status`: `LANDED` / `OPEN` / `DEFERRED` / `NOT-A-DEFECT` / `DUPLICATE-OF:<ID>`. `Phase` references §5. Ranges (`F-01..F-12`) are avoided; each row is a unique finding ID.

| ID | Audit ID | Rule | Surface | Observation (short) | Severity | Confidence | Status | Phase |
|----|----------|------|---------|---------------------|----------|------------|--------|-------|
| CareerSetup-F-01 | AUD-20261102-005 | TOG-6/SHR-6/H3 | Preflight commit | No confirmation on "Create the run"; irreversible Active run | 2 | High | LANDED (source + gating verified) | Phase 1 (done) |
| CareerSetup-F-02 | AUD-20261102-005 | H1/TOG-7 | All setup screens | One-draft-per-session sharing/expiry undisclosed | 2 | High | LANDED (source-only) | Phase 1 (done) |
| CareerSetup-F-03 | AUD-20261102-005 | NRM-2 | ScenarioSelect disabled Continue | Disabled button names no reason | 2 | High | LANDED (source-only) | Phase 1 (done) |
| CareerSetup-F-04 | AUD-20261102-005 | IA-5 | Scenario empty state | No exemplar in empty state | 1 | High | LANDED (source-only) | Phase 4 (done) |
| CareerSetup-F-05 | AUD-20261102-005 | H4/SHR-1 | Legacy forward door | Navigates without saving; exception unnamed | 1 | Medium | LANDED (source-only) | Phase 3 (done) |
| CareerSetup-F-06 | AUD-20261102-005 | H4/NRM-4 | LegacySelect/BuildTarget | Literal hrefs vs route() props | 1 | Medium | LANDED (source-only) | Phase 3 (done) |
| CareerSetup-F-07 | AUD-20261102-005 | H4 | TraineeProfile | Hard-coded stat axes vs server statOrder | 1 | Medium | LANDED (source-only) | Phase 3 (done) |
| Cockpit-F-01 | AUD-20261102-001 | NRM-1/H4 | Cockpit | skillsUrl built by string replacement on run_url | 1 | High | OPEN | Phase 3 |
| Cockpit-F-02 | AUD-20261102-001 | code clarity | Cockpit | teamLeague redundant null-check | 0 | High | OPEN | Phase 4 |
| Cockpit-F-03 | AUD-20261102-001 | NRM-1/H1 | Cockpit | Dead write methods (7 forms) unrendered | 1 | High | OPEN | Phase 4 |
| Dashboard-F-01 | AUD-20261101-001 | UI-1 | Dashboard | Nine distinct type sizes vs UI-1's three | 2 | Medium | OPEN | Phase 2 (cross-cutting §4 item 2) |
| Dashboard-F-02 | AUD-20261101-001 | WCAG 3.1.2/H2 | Dashboard | Japanese trainee name without lang="ja" | 2 | High | OPEN | Phase 2 |
| Dashboard-F-03 | AUD-20261101-001 | H1/TOG-1/TOG-3 | Dashboard | Countdown frozen (computed over Date.now(), no timer) | 2 | High | OPEN | Phase 2 |
| Dashboard-F-04 | AUD-20261101-001 | GP-3/H6/H1 | Dashboard | Two turn figures unlabelled | 2 | Medium | OPEN | Phase 2 |
| Dashboard-F-05 | AUD-20261101-001 | IA-5/H9 | Dashboard | Empty card no exemplar | 2 | Medium | OPEN | Phase 4 |
| Dashboard-F-06 | AUD-20261101-001 | IA-5/H9 | Dashboard | Empty veterans table omits table entirely | 2 | High | OPEN | Phase 4 |
| Dashboard-F-07 | AUD-20261101-001 | H1/SHR-3 | Dashboard | KPI strip names only newest active career | 2 | Medium | OPEN | Phase 2 |
| Dashboard-F-08 | AUD-20261101-001 | H2/H8 | Dashboard | Developer-facing note in shipped copy | 2 | High | OPEN | Phase 4 |
| Dashboard-F-09 | AUD-20261101-001 | H4/SHR-1 | Dashboard | Column visibility gated by two toggles | 2 | High | OPEN | Phase 2 |
| Dashboard-F-10 | AUD-20261101-001 | UX-1 | Dashboard | Give up button below 44px house rule | 1 | High | NOT-A-DEFECT | — |
| Dashboard-F-11 | AUD-20261101-001 | H1/TOG-1 | Dashboard | Loading status doesn't name destination | 1 | High | OPEN | Phase 4 |
| Dashboard-F-12 | AUD-20261101-001 | H9/WCAG 3.3.3 | Dashboard | Error page no retry control | 1 | Medium | OPEN | Phase 4 |
| Preferences-F-01 | AUD-20261101-003 | WCAG 4.1.3/H1/TOG-1 | Preferences | Error block no role="alert"/aria-live | 2 | High | OPEN | Phase 2 |
| Preferences-F-02 | AUD-20261101-003 | WCAG 4.1.2/WCAG 1.3.1/H1 | Preferences | aria-describedby returns non-existent id | 2 | High | OPEN | Phase 2 |
| Preferences-F-03 | AUD-20261101-003 | WCAG 2.4.3/H1/TOG-1 | Preferences | Focus not moved for four refusal classes | 2 | Medium | OPEN | Phase 2 |
| Preferences-F-04 | AUD-20261101-003 | H1/NRM-1 | Preferences | "Download a backup" writes server-side snapshot | 2 | Medium | OPEN | Phase 4 |
| Preferences-F-05 | AUD-20261101-003 | H1/H9 | Preferences | Wide layout disabled with no reason | 1 | Medium | OPEN | Phase 4 |
| Preferences-F-06 | AUD-20261101-003 | TOG-1/H1/WCAG 4.1.3 | Preferences | Reset arm not announced via live region | 1 | Medium | OPEN | Phase 2 |
| Preferences-F-07 | AUD-20261101-003 | IA-1/H1 | Preferences | Breadcrumb "Settings" vs h1 "Preferences" | 1 | Medium | OPEN | Phase 3 (cross-cutting §4 item 4) |
| Preferences-F-08 | AUD-20261101-003 | H4/SHR-1 | Preferences | Refusals render twice (inline + page block) | 1 | High | OPEN | Phase 3 |
| Preferences-F-09 | AUD-20261101-003 | H2 | Preferences | Em dash in Theme card Note | 1 | High | OPEN | Phase 4 |
| Preferences-F-10 | AUD-20261101-003 | H1/TOG-1 | Preferences | Backup running state not announced | 1 | Medium | OPEN | Phase 4 |
| Database-F-01 through F-12 | AUD-20261102-004 | multiple | Database hub/areas | Hub-tile provenance, ledger dates, Artisan price, source notes, type scale, etc. | 1-2 | High | LANDED | Phase 1 (done) |
| Database-F-13 | AUD-20261102-004 | WCAG 1.4.10 | /support-cards reflow at 320px | Document scrolls sideways at phone widths | 2 | High | OPEN | Phase 2 |
| RaceDecision-F-01 | AUD-20261102-002 | — | Race Decision | Alert focus issue | 1-2 | High | OPEN | Phase 2 |
| RaceDecision-F-02 | AUD-20261102-002 | — | Race Decision | Dashes for missing values | 1-2 | High | OPEN | Phase 4 |
| RaceDecision-F-03 | AUD-20261102-002 | — | Race Decision | Blocked-race reason uncommunicated | 1-2 | High | OPEN | Phase 4 |
| RaceDecision-F-04 | AUD-20261102-002 | — | Race Decision | Navigation doors | 1-2 | High | OPEN | Phase 3 (cross-cutting §4 item 4) |
| RaceDecision-F-05 | AUD-20261102-002 | — | Race Decision | Aptitude pill title-only | 1-2 | High | OPEN | Phase 4 |
| RacePlanner-F-01 | AUD-20261102-003 | — | Race Planner | Unrendered groups | 1 | High | OPEN | Phase 3 |
| RacePlanner-F-02 | AUD-20261102-003 | — | Race Planner | Selection cap uncommunicated | 1 | High | OPEN | Phase 4 |
| RacePlanner-F-03 | AUD-20261102-003 | — | Race Planner | Dead computed | 1 | High | OPEN | Phase 4 |
| RacePlanner-F-04 | AUD-20261102-003 | — | Race Planner | Deadline prefix | 1 | High | OPEN | Phase 4 |
| SaveVeteran-F-01 | AUD-20261102-020 | — | Save Veteran | role="alert" for non-error precondition | 1 | High | OPEN | Phase 2 |
| SaveVeteran-F-02 | AUD-20261102-020 | — | Save Veteran | Saving overwrites with no confirmation/reversal | 1 | High | OPEN | Phase 2 |
| TurnEdit-F-01 | AUD-20261102-006 | — | Turn Edit | No page-load status | 1-2 | High | OPEN | Phase 2 |
| TurnEdit-F-02 | AUD-20261102-006 | — | Turn Edit | Refusal alert focus | 1-2 | High | OPEN | Phase 2 |
| SnapshotEntry-F-01 | AUD-20261102-007 | — | Snapshot Entry | Stepper forwards to data-less review | 1-2 | High | OPEN | Phase 4 |
| SnapshotSetup-F-01 | AUD-20261102-008 | — | Snapshot Setup | GET payload URI risk | 2 | High | OPEN | Phase 2 |
| SnapshotSetup-F-02 | AUD-20261102-008 | — | Snapshot Setup | Position error or chain | 1 | High | OPEN | Phase 4 |
| SnapshotSetup-F-03 | AUD-20261102-008 | — | Snapshot Setup | Unused action prop | 0 | — | OPEN | Phase 4 |
| SnapshotReview-F-01 | AUD-20261102-009 | — | Snapshot Review | Commit no reversal (systemic gap) | 1-2 | High | OPEN | Phase 4 |
| CatalogIndex-F-01 | AUD-20261102-010 | H6 | Catalog index | Search can't match diacritics/full-width | 2 | High | OPEN | Phase 3 |
| CatalogShow-F-01 | AUD-20261102-011 | title-only disclosure | Catalog show | Unique pill derivation in title only | 1 | High | OPEN | Phase 3 (cross-cutting §4 item 1) |
| SkillsIndex-F-01 | AUD-20261102-012 | title-only disclosure | Skills index | Unique pill derivation in title only | 1 | High | DUPLICATE-OF: CatalogShow-F-01 | §4 |
| SkillsShow-F-01 | AUD-20261102-013 | title-only disclosure | Skills show | Unique pill derivation in title only | 1 | High | DUPLICATE-OF: CatalogShow-F-01 | §4 |
| RunsIndex-F-01 | AUD-20261102-016 | — | Runs index | Status-pill colour coupling | 1 | High | OPEN | Phase 2 |
| RunsIndex-F-02 | AUD-20261102-016 | — | Runs index | Disabled pager spans without aria-disabled | 2 | High | OPEN | Phase 3 |
| RunsCreate-F-01 | AUD-20261102-017 | — | Runs create | Page named three ways | 1 | High | OPEN | Phase 3 |
| RunsImport-F-01 | AUD-20261102-018 | — | Runs import | Component-family inconsistency | 2 | High | OPEN | Phase 3 |
| RunsImport-F-02 | AUD-20261102-018 | — | Runs import | Redundant header-list paragraph | 1 | Medium | OPEN | Phase 4 |
| ReviewIndex-F-01 | AUD-20261102-021 | — | Review queue | Progress bar misstates progress | 3 | High | OPEN | Phase 1 |
| ReviewIndex-F-02 | AUD-20261102-021 | — | Review queue | No match styled as caution | 2 | Medium | OPEN | Phase 4 |
| ReviewIndex-F-03 | AUD-20261102-021 | — | Review queue | Queue table lacks caption/scope | 2 | Medium | OPEN | Phase 3 |
| ReviewIndex-F-04 | AUD-20261102-021 | — | Review queue | Pager not pinned (sticky no-op) | 2 | Medium | OPEN | Phase 2 |
| ReviewDetail-F-01 | AUD-20261102-022 | SHR-6/H3 | Review detail | No reversal after verdict commit | 3 | Medium | OPEN | Phase 1 |
| LegacyIndex-F-01 | AUD-20261102-023 | WCAG 1.3.1/4.1.2 | Legacy index | Table caption + scoped headers | 1 | High | OPEN | Phase 3 |
| LegacyCompare-F-01 | AUD-20261102-024 | TOG-3/H2 | Legacy compare | One-run comparison table with caution verdicts | 1 | High | OPEN | Phase 3 |
| LegacyBuilder-F-01 | AUD-20261102-025 | H5/H2 | Legacy builder | Spark cap per parent not enforced | 1 | High | OPEN | Phase 3 |
| VeteransIndex-F-01 | AUD-20261102-026 | SHR-1/H4 | Veterans index | Clear anchor not route() | 1 | High | OPEN | Phase 3 (cross-cutting §4 item 4) |
| VeteransIndex-F-02 | AUD-20261102-026 | IA-5/H9 | Veterans index | Empty state no exemplar | 1 | High | OPEN | Phase 4 |
| VeteransCompare-F-01 | AUD-20261102-027 | NRM-6/GT-1 | Veterans compare | Sticky cells no-op under @container | 1-2 | High | OPEN | Phase 2 |
| VeteransCompare-F-02 | AUD-20261102-027 | H6/NRM-6 | Veterans compare | Difference verdict below lg | 1-2 | High | OPEN | Phase 4 |
| VeteransCompare-F-03 | AUD-20261102-027 | UX-5 | Veterans compare | Single-column pill-caution | 1 | High | OPEN | Phase 4 |
| VeteransCompare-F-04 | AUD-20261102-027 | SHR-1/H4 | Veterans compare | Library doors not route() | 1 | High | OPEN | Phase 3 (cross-cutting §4 item 4) |
| VeteransShow-F-01 | AUD-20261102-028 | SHR-1/H4 | Veterans show | Library/source ledger anchors | 1 | High | OPEN | Phase 3 (cross-cutting §4 item 4) |

## §4 — Cross-cutting findings

Findings that repeat across multiple audits, consolidated into single entries. Each appears once in §4; underlying rows in §3 are marked `DUPLICATE-OF` or cross-referenced.

1. **Title-only disclosure channel** (WCAG 1.3.1/4.1.2 adjacency): Catalog show F-01, Skills index F-01, Skills show F-01 — `? Unique` pill derivation carried only in `title`. One cross-cutting fix (visible text or `aria-describedby`/sr-only twin). Skills index/show are `DUPLICATE-OF: CatalogShow-F-01`.

2. **Type scale (UI-1)**: Database F-07 (nine arbitrary sizes per surface), Dashboard F-01 (nine distinct sizes), Career M4 F-06 (TrainingDetail/EventDecision/SkillsPlanner/Timeline). Consolidated as one cross-cutting type-scale remediation onto `DESIGN.md` §2.2 four tiers.

3. **Target size (UX-1)**: Dashboard F-10 (44px exception, documented — NOT-A-DEFECT), Database F-11 (breadcrumb 51×16), house-rule checks across setup screens. Dashboard F-10 stays NOT-A-DEFECT.

4. **Breadcrumb labels (H4/SHR-1)**: Career M4 F-09 (mislabelled doors across RunSettings/TeamEdit/ScenarioDetail/ConfirmDelete/Result), VeteransIndex F-01 (Clear anchor), VeteransShow F-01 (library/source ledger anchors), RaceDecision F-04 (navigation doors), Preferences F-07 ("Settings" vs "Preferences"). Consolidated: replace plain anchors with `LinkButton`/route() props everywhere.

5. **Live-region patterns (WCAG 4.1.3)**: Preferences F-01 (page-level error block), plus dashboard/status announcements across career screens. Consolidated as one cross-cutting live-region remediation.

6. **SourceNote/provenance (GP-2)**: Database F-06 (payout card missing SourceNote line). Cross-cutting pattern; apply to all derived figures.

7. **Japanese lang="ja" (WCAG 3.1.2)**: Dashboard F-02 (`trainee_ja` spans). One-off fix.

8. **Verification/build-verify (process)**: Every post-build claim must record `npm run build` exit, manifest mtime, `ls public/hot` output, seven-source-URL verification. Process-level, not a surface finding.

## §5 — Phased implementation plan

Five phases, ordered by severity, confidence, and blast radius.

### Phase 1 — Blocking (severity 3+; irreversible-commit / data-loss; WCAG AA conformance)

Findings:
- ReviewIndex-F-01 (sev 3): progress bar misstates progress — `resolvedCount` computed as `total - data.length` counts pending rows on other pages, not resolved rows.
- ReviewDetail-F-01 (sev 3): no reversal after verdict commit — resolved candidate leaves Pending queue with no undo/re-open.
- Database pass 2 F-01..F-12: already landed (11 commits `7e9df158..ba335bb4`); verify build and landing.
- Career setup F-01/F-02/F-03: already landed in `c60af3a1` (F-01 source + gating verified; F-02/F-03 source-only); verify no regression.

Acceptance criteria per finding: as named in the individual plan's §Implementation steps. Verification: source-only for behavioral findings; Playwright conditional on Chromium probe (§2.1). Rollback: revert the specific commit path. Owner sign-off: required for ReviewIndex-F-01 and ReviewDetail-F-01.

**Removed:** The "severity 3/4 discovered during reconciliation → DEFERRED" clause from the prior draft. Severity-3 findings appear in Phase 1 per this phase's definition; none are deferred solely on discovery timing.

### Phase 2 — Accessibility conformance (severity 2)

Findings:
- Database F-13 (reflow at 320px, WCAG 1.4.10).
- Cockpit F-01 (string-built URL — source fix, not rendered).
- Preferences F-01, F-02, F-03, F-06 (live regions, aria-describedby, focus move, reset arm).
- RunsIndex-F-01 (status-pill colour coupling).
- SaveVeteran F-01/F-02 (role="alert" misuse, overwrite confirmation).
- SnapshotSetup-F-01 (GET payload URI risk).
- TurnEdit F-01/F-02 (page-load status, refusal alert focus).
- VeteransCompare-F-01 (sticky cells no-op).

Acceptance: Playwright verifiable per finding once Chromium probe passes (§2.1); otherwise `UNMEASURED`.

### Phase 3 — Consistency and standards (severity 2)

Findings:
- H4/SHR-1: breadcrumb labels (Career M4 F-09, VeteransIndex F-01, VeteransShow F-01, RaceDecision F-04, Preferences F-07), source links, literal hrefs → route() props.
- H1/TOG-3: status accuracy, hub-vs-door counts.
- NRM-1/NRM-2: signifiers, constraint explanations.
- Cross-cutting title-only disclosure (§4 item 1).
- CatalogIndex F-01 (search normalization — owner decision: schema change vs copy).

Acceptance: grep-based (no literal `/career/setup/` hrefs; no bare anchors to named routes). Verification: source + grep.

### Phase 4 — Copy and clarity (severity 1-2)

Findings:
- H2: developer-facing copy (Dashboard F-08), jargon.
- H9: help text.
- IA-5: empty-state exemplars (Dashboard F-05/F-06, CareerSetup F-04, VeteransIndex F-02).
- Copy-only fallback for CareerSetup F-05 (downgrade to documented exception under SHR-1).

Acceptance: copy-tracking assertion updates only. Verification: source + feature-test copy assertions.

### Phase 5 — Deferred (severity 1, low-ROI, or owner-only)

Findings:
- Cockpit-F-02 (code clarity, sev 0).
- SnapshotSetup-F-03 (unused action prop, sev 0).
- Cosmetic findings (Preferences-F-09 em dash, etc.).
- Findings requiring owner data/fixture decisions (CatalogIndex F-01 option a vs b).
- Unmeasured claims requiring rendered verification before classifying.

Acceptance: owner sign-off per deferred item.

## §6 — Verification requirements

Formalizing the sequence's build-verification policy (`docs/audit-corrections.md` entry 6):

- Every post-build claim must record: `npm run build` exit code, `public/build/manifest.json` mtime, `ls public/hot` output, seven-source-URL verification (or per-finding URL).
- Every Playwright claim must run against the **built artifact**, not HMR'd source.
- Every source-only finding labeled `OBSERVED_SOURCE` in the finding row, with a "not verified by" note for behavior gaps.
- Every "duplicate" claim must cite the finding ID it duplicates (§4).
- `npm install` is a dependency change requiring owner approval + network (AGENTS.md §5) — never backgrounded while a peer session works.
- `pint --dirty` prohibited (KI-75); use explicit pathspec on commit.
- Database pass 2's retracted dark-theme contrast numbers (§2 of that audit) are withdrawn; do not cite them as measurements.

## §7 — Open questions for owner

Named decisions the proposal cannot make:

1. **Manifest authorization.** Should Phase 0 create `docs/audits/MANIFEST.md`? (Recommended yes; prevents a ninth review on the same ID/evidence issue.)
2. **RENDERED_EVIDENCE re-probe.** Should the 30 reports claiming `FALSE` be re-probed against the current environment? Database pass 2 proved Chromium can launch here (`3 passed, 357 assertions`). (Recommended yes for behavioral findings; not required for source-only findings.)
3. **Reference data absorption.** Should the 6 Reference data audits be absorbed into Database pass 2's record as confirmations? (§2.3: Catalog index F-01 is a correction, Catalog show F-01 is cross-cutting, Skills index/show are duplicates, SupportCards are confirmations.)
4. **Phase 5 deferrals.** Are there findings the owner requires in Phase 1-4 that the proposal would defer to Phase 5? Owner call.
5. **CatalogIndex F-01 option.** Schema change (normalized keys, migration+ADR+digest) vs copy documentation fallback? Owner decision required — this is a schema change under AGENTS.md §11.
6. **Fixtures for behavioral verification.** Approve running Career setup F-04, F-05, F-07's pending behavioral gates in the current environment? (Proposal assumes yes; owner confirm.)

## §8 — Amendment numbering

This is version 1. Corrections required by audit-agent review (B-1 through B-10, N-1 through N-5) are incorporated in this version. Future corrections carry `A1`… numbering.

## §9 — Appendix A — 31 audits with canonical IDs

| Canonical ID | Filename | Screen | Findings | Status |
|--------------|----------|--------|----------|--------|
| AUD-20261101-001 | docs/audits/AUD-dashboard-2026-11-01.md | Dashboard | 12 | Pre-existing, source-fixed |
| AUD-20261101-002 | docs/audits/AUD-career-surfaces-2026-11-01.md | Career M4 | 11 | Pre-existing, partially landed |
| AUD-20261101-003 | docs/audits/AUD-preferences-2026-11-01.md | Preferences | 10 | Pre-existing, all OPEN |
| AUD-20261102-001 | docs/audits/AUD-career-cockpit-2026-11-02.md | Cockpit | 3 | New, OPEN |
| AUD-20261102-002 | docs/audits/AUD-career-racedecision-2026-11-02.md | Race Decision | 5 | New, OPEN |
| AUD-20261102-003 | docs/audits/AUD-career-raceplanner-2026-11-02.md | Race Planner | 4 | New, OPEN |
| AUD-20261102-004 | docs/audits/AUD-database-2026-11-01.md | Database | 12 + F-13 | Pre-existing, mostly landed |
| AUD-20261102-005 | docs/audits/AUD-career-setup-2026-11-02.md | Career setup | 7 | New, 3 landed behavior / 4 landed source |
| AUD-20261102-006 | docs/audits/AUD-career-turnedit-2026-11-02.md | Turn Edit | 2 | New, OPEN |
| AUD-20261102-007 | docs/audits/AUD-career-snapshot-entry-2026-11-02.md | Snapshot Entry | 1 | New, OPEN |
| AUD-20261102-008 | docs/audits/AUD-career-snapshot-setup-2026-11-02.md | Snapshot Setup | 3 | New, OPEN |
| AUD-20261102-009 | docs/audits/AUD-career-snapshot-review-2026-11-02.md | Snapshot Review | 1 | New, OPEN |
| AUD-20261102-010 | docs/audits/AUD-catalog-index-2026-11-02.md | Catalog index | 1 | New, OPEN |
| AUD-20261102-011 | docs/audits/AUD-catalog-show-2026-11-02.md | Catalog show | 1 | New, OPEN |
| AUD-20261102-012 | docs/audits/AUD-skills-index-2026-11-02.md | Skills index | 1 | New, DUPLICATE-OF |
| AUD-20261102-013 | docs/audits/AUD-skills-show-2026-11-02.md | Skills show | 1 | New, DUPLICATE-OF |
| AUD-20261102-014 | docs/audits/AUD-support-cards-index-2026-11-02.md | SupportCards index | 0 | New |
| AUD-20261102-015 | docs/audits/AUD-support-cards-show-2026-11-02.md | SupportCards show | 0 | New |
| AUD-20261102-016 | docs/audits/AUD-runs-index-2026-11-02.md | Runs index | 2 | New, OPEN |
| AUD-20261102-017 | docs/audits/AUD-runs-create-2026-11-02.md | Runs create | 1 | New, OPEN |
| AUD-20261102-018 | docs/audits/AUD-runs-import-2026-11-02.md | Runs import | 2 | New, OPEN |
| AUD-20261102-019 | docs/audits/AUD-help-2026-11-02.md | Help | 0 | New |
| AUD-20261102-020 | docs/audits/AUD-career-saveveteran-2026-11-02.md | Save Veteran | 2 | New, OPEN |
| AUD-20261102-021 | docs/audits/AUD-20261102-021.md | Review index | 4 | New, OPEN (1 sev-3) |
| AUD-20261102-022 | docs/audits/AUD-20261102-022.md | Review detail | 1 | New, OPEN (sev-3) |
| AUD-20261102-023 | docs/audits/AUD-20261102-023.md | Legacy index | 1 | New, OPEN |
| AUD-20261102-024 | docs/audits/AUD-20261102-024.md | Legacy compare | 1 | New, OPEN |
| AUD-20261102-025 | docs/audits/AUD-20261102-025.md | Legacy builder | 1 | New, OPEN |
| AUD-20261102-026 | docs/audits/AUD-veterans-index-2026-11-02.md | Veterans index | 2 | New, OPEN |
| AUD-20261102-027 | docs/audits/AUD-veterans-compare-2026-11-02.md | Veterans compare | 4 | New, OPEN |
| AUD-20261102-028 | docs/audits/AUD-veterans-show-2026-11-02.md | Veterans show | 1 | New, OPEN |

Missing IDs `029–031`: reserved.

## §10 — Appendix B — 29 plans with absorption status

| Plan filename | Audit ID | Absorption status |
|---------------|----------|-------------------|
| 1793534600251-cockpit-audit-fixes.md | AUD-20261102-001 | Absorbed into §3 (Cockpit-F-01..F-03) |
| 1793540000000-dashboard-audit-fixes.md | AUD-20261101-001 | Absorbed into §3 (Dashboard-F-01..F-12) |
| 1793545886150-database-ui-ux-audit.md | AUD-20261102-004 | Absorbed into §3 (Database-F-01..F-13) |
| 1793546000000-career-surfaces-audit-fixes.md | AUD-20261101-002 | Absorbed into §3 (Career M4-F-01..F-11) |
| 1793548000000-preferences-audit-fixes.md | AUD-20261101-003 | Absorbed into §3 (Preferences-F-01..F-10) |
| 1793592819070-career-setup-audit-fixes.md | AUD-20261102-005 | Absorbed into §3 (CareerSetup-F-01..F-07) |
| 1793600000000-catalog-index-audit.md | AUD-20261102-010 | Absorbed into §3 (CatalogIndex-F-01) |
| 1793600000001-catalog-show-audit.md | AUD-20261102-011 | Absorbed into §3 (CatalogShow-F-01) |
| 1793600000002-skills-index-audit.md | AUD-20261102-012 | Absorbed into §3 (SkillsIndex-F-01, DUPLICATE-OF) |
| 1793600000003-skills-show-audit.md | AUD-20261102-013 | Absorbed into §3 (SkillsShow-F-01, DUPLICATE-OF) |
| 1793600000004-support-cards-index-audit.md | AUD-20261102-014 | Absorbed into §3 (0 findings) |
| 1793600000005-support-cards-show-audit.md | AUD-20261102-015 | Absorbed into §3 (0 findings) |
| 1793600000006-veterans-index-audit.md | AUD-20261102-026 | Absorbed into §3 (VeteransIndex-F-01/F-02) |
| 1793600000007-veterans-compare-audit.md | AUD-20261102-027 | Absorbed into §3 (VeteransCompare-F-01..F-04) |
| 1793600000008-veterans-show-audit.md | AUD-20261102-028 | Absorbed into §3 (VeteransShow-F-01) |
| 1793600000009-help-audit.md | AUD-20261102-019 | Absorbed into §3 (0 findings) |
| 1793600000010-runs-index-audit.md | AUD-20261102-016 | Absorbed into §3 (RunsIndex-F-01/F-02) |
| 1793600000011-runs-create-audit.md | AUD-20261102-017 | Absorbed into §3 (RunsCreate-F-01) |
| 1793600000012-runs-import-audit.md | AUD-20261102-018 | Absorbed into §3 (RunsImport-F-01/F-02) |
| 1793620000000-review-queue-audit-fixes.md | AUD-20261102-021 | Absorbed into §3 (ReviewIndex-F-01..F-04) |
| 1793620000001-review-detail-audit-fixes.md | AUD-20261102-022 | Absorbed into §3 (ReviewDetail-F-01) |
| 1793620000002-legacy-index-audit-fixes.md | AUD-20261102-023 | Absorbed into §3 (LegacyIndex-F-01) |
| 1793620000003-legacy-compare-audit-fixes.md | AUD-20261102-024 | Absorbed into §3 (LegacyCompare-F-01) |
| 1793620000004-legacy-builder-audit-fixes.md | AUD-20261102-025 | Absorbed into §3 (LegacyBuilder-F-01) |
| plan-audit-career-saveveteran.md | AUD-20261102-020 | Absorbed into §3 (SaveVeteran-F-01/F-02) |
| plan-audit-career-snapshot-entry.md | AUD-20261102-007 | Absorbed into §3 (SnapshotEntry-F-01) |
| plan-audit-career-snapshot-review.md | AUD-20261102-009 | Absorbed into §3 (SnapshotReview-F-01) |
| plan-audit-career-snapshot-setup.md | AUD-20261102-008 | Absorbed into §3 (SnapshotSetup-F-01..F-03) |
| plan-audit-career-turnedit.md | AUD-20261102-006 | Absorbed into §3 (TurnEdit-F-01/F-02) |

All plans are gitignored working notes; this proposal is the durable record. Plan content is not copied; each is cross-referenced by filename only.

## §11 — Appendix C — Cross-reference to correction files

| Correction file | What this proposal inherits | What this proposal does not |
|-----------------|----------------------------|----------------------------|
| `docs/audit-corrections.md` | Five audit-record defects (RENDERED_EVIDENCE truth, ID scheme, SHR-8/UI-9/UI-10 non-existence, GP-2 as operative rule, Database pass 2 corrections) | Resolution of the defects themselves (deferred per prior pass) |
| `docs/audit-fix-pass-addendum.md` | Pass-1/pass-2 dispositions for Database audit (F-01..F-13 status, build/landing verification, PHPStan pre-existing red) | Re-running pass verification; editing the Database audit report |
| `KNOWN-ISSUES.md` (KI-144..KI-148) | Known-issue register entries referenced by plans | Creation of new KI entries (owner decision) |

**Dependency:** `docs/audit-corrections.md` entry 6 (`public/build/` gitignore correction) means Phase 1's landing verification cannot rely on a committed build artifact until that correction is applied. The proposal cites this dependency explicitly.

## Rules the proposal holds to

1. No new findings. Corrections for follow-up go in a separate section, not the phased plan.
2. No duplication with individual audits. Each finding cited by audit ID + finding ID; observation summarized in one line.
3. No duplication with `.kilo/plans/`. Plans referenced by filename only.
4. `RENDERED_EVIDENCE` truth stated per report (§2.1), not inherited.
5. Duplicates resolved, not double-counted (§2.3, §4).
6. Implementation status named for every finding (§3 Status column).
7. Effort estimates honest (S/M/L).
8. Owner questions named, not assumed (§7).
9. Tracked in git: commit as `docs(proposals): consolidated UI/UX audit implementation plan`.
10. No audit-report edits. Corrections stay in `docs/audit-corrections.md`.

## Definition of done

The proposal is complete when all §1-§11 sections exist, all 31 audits and 29 plans are cross-referenced, all five reconciliation tasks (§2.1-§2.5) have outputs, all findings have status + phase, all owner questions are named (§7), and the file is committed to `docs/proposals/` with a body citing this dispatch.

**Commit status (owner action required):** The file is on disk at `docs/proposals/2026-11-02-ui-ux-audit-implementation.md` and has not been committed. Owner action required: `git add docs/proposals/2026-11-02-ui-ux-audit-implementation.md && git commit -m 'docs(proposals): consolidated UI/UX audit implementation plan'`. The proposal is complete on disk; committal is an owner step because `git commit` is not available to the agent writing this file.
