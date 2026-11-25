# Source-to-Canonical Crosswalk (Task #8)

Machine-auditable crosswalk mapping every original actionable slice from the 15 consolidated `docs/roadmap/` source files to its canonical destination. Generated 2026-11-23. Canonical IDs retained from original sources. This document is the audit record for Task #8 (prove task-level completeness).

> **Current status note, 2026-11-24.** The slice/KI statuses in this crosswalk are the historical mapping snapshot. For current state read the five canonical roadmaps indexed by `docs/roadmap/INDEX.md`; for example S1.1 is NOT VERIFIED at `3600741e`, S1.3 is DONE at `546 <= 708`, D-11 is DONE, and the register audit is clean with 0 OPEN. This table is not edited to preserve the historical mapping.

## 1. Authority determination (Task #7 result)

Exactly one authoritative execution owner for every MASTER slice (`MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md`, slices S0.1–S4.4 plus Phase 5 S5.1–S5.9): the five canonical domain roadmaps. `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` is the historical baseline (slice definitions + supporting evidence), not an execution authority.

| File | Role | Execution authority? | Basis |
|------|------|----------------------|-------|
| `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` | Historical baseline / slice definitions | **NO** | INDEX.md Round 30 §30.2: "no execution authority; superseded by five domain-specific authorities" |
| `RESIDUAL-VERIFICATION-2026-11-18.md` | Verification appendix / evidence only | **NO** | INDEX.md live register: "evidence appendix behind four master slices"; §Slice 2.5/2.6/4.1 + §Slice 3.8 |
| 5 canonical subject roadmaps | Domain-specific execution authorities | **YES** (exactly one owner per slice) | INDEX.md Round 30 §30.1; Round 31 ownership census 2026-11-24: 33/33 owned, zero duplicates |
| 10 archived originals | Historical record | **NO** | INDEX.md register: "stay on disk as the historical record ... own no current execution order" |

> This section supersedes the earlier Task #7/#8 single-master authority determination. The §2 destination column is the historical Task #8 consolidation record; where a listed destination differs from the Round 31 owner in §12, the §12 census is authoritative. Such mentions are dependency or reference citations, not ownership claims — each slice has exactly one execution owner per §12.

## 2. Master slice inventory (33 slices, S0.1–S4.4 + S5.1–S5.9)

Canonical home: `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md`. Destination column names the canonical subject roadmap(s) that consolidate the slice.

| Slice | Phase | Objective / deliverable | Canonical destination(s) | Classification | Supporting evidence | Disposition |
|-------|-------|-------------------------|--------------------------|----------------|---------------------|-------------|
| S0.1 | 0 | Commit the UI/UX accessibility batch | PRODUCT §6, DECISIONS §1 | OPEN — owner-gated, not committed | EXECUTION-RECORD S0.1; PRODUCT §6 | Retained — awaiting owner commit (named pathspec) |
| S0.2 | 0 | Commit and repair the gate (`tools/gates.sh`) | QUALITY §3, DECISIONS §3 | OPEN — "Still Phase 0 work" | MASTER §4 refuted premise "tools/gates.sh is committed" | Retained — gate not yet committed |
| S0.3 | 0 | Commit the ADR drafts and document batch | PRODUCT §6, DECISIONS §1 | OPEN — owner-gated, not committed | EXECUTION-RECORD S0.3; PRODUCT §6 | Retained — awaiting owner commit |
| S0.4 | 0 | Clear empty-diff status noise | DECISIONS §3 (0.5/0.6 tree hygiene) | DONE — tree hygiene done | DECISIONS §3: "0.5/0.6 Tree hygiene ... [DONE]" | Merged into tree hygiene |
| S0.5 | 0 | Reconcile `master` with `origin/master` | PRODUCT §6 | OPEN — owner-gated | EXECUTION-RECORD S0.5; MASTER §9 S4.1 deps | Retained — owner authorises push |
| S0.6 | 0 | Re-tune light/dark to Azure Daybreak / Midnight Circuit | PRODUCT §6 (D-13) | BLOCKED — owner D-13 | MASTER §8 S0.6 deps D-13; DECISIONS D-13 OWNER-GATED | Retained — owner-gated |
| S1.1 | 1 | Quiet-box full Pest baseline | QUALITY §1.1 | PARTIALLY VERIFIED | 2467 passed / 2 failed at HEAD `3600741e` (CRLF fixed; DocCitationParity 808 vs 708) | Retained — 2 failures unresolved |
| S1.2 | 1 | Full browser sweep at landed HEAD | QUALITY §1.2, FRONTEND §4 | BLOCKED | Phase B incomplete; 15 specs need triage/re-run | Retained — browser suite not green |
| S1.3 | 1 | Citation ratchet: 808 → 708 or lower | QUALITY §2 | DONE — VERIFIED | 690 dead links at `2546689e` (≤ 708 baseline); QWEN 716→691 at `3cbbd8b9` | Merged — baseline held, never raised |
| S2.1 | 2 | `page-header.spec.ts` asserts superseded tier (6 cases) | QUALITY §1 (expected red) | OPEN — confirm/refute | MASTER §6 S2.1 acceptance; page-header contradiction noted | Retained — expected red to confirm |
| S2.2 | 2 | KI-167 Scenario Selection focus round trip | FRONTEND §2, QUALITY §4 | DONE — VERIFIED | EXECUTION-RECORD S2.2 root cause `:disabled="form.processing"` fixed | Merged — root cause fixed |
| S2.3 | 2 | KI-114 deck-picker specs drive retired controls | QUALITY §4, PRODUCT §7 | DONE — premise refuted | EXECUTION-RECORD S2.3 "premise refuted"; RESIDUAL §Slice 2.5 live selectors | Superseded — premise invalid, 3 unclassified Legacy-step failures remain |
| S2.4 | 2 | KI-127 Team Race Facility panel card wrapper | PRODUCT §5 | DONE | `TeamRaceFacilityPanel.vue:51` is `<div class="card p-3">` | Merged |
| S2.5 | 2 | KI-104 browser half + ADR-0027 | QUALITY §4, V3-RELEASE §6 | BLOCKED — owner | ADR-0027 owner signature at `docs/adr/0027...:11`; code half DONE | Retained — owner-gated |
| S2.6 | 2 | Register closeout for KI-116 and KI-119 | PRODUCT §5, QUALITY §5 Cond.7 | DONE | `SupportCardEffects::forLevel()` at `SupportCardEffects.php:209`; column migration `2026_11_10_010500` | Merged |
| S3.1 | 3 | Restore Independent Training door | PRODUCT §1 (note) | DONE | EXECUTION-RECORD S3.1 confirms door DONE | Merged |
| S3.2 | 3 | Correct shipped hint-level copy | PRODUCT §1 (note) | DONE | EXECUTION-RECORD S3.2 hint-level copy DONE | Merged |
| S3.3 | 3 | Confirm Japanese search key populated | PRODUCT §1 (note) | DONE | EXECUTION-RECORD S3.3 Japanese search key DONE | Merged |
| S3.4 | 3 | Maiden-lock fallback for imported runs | PRODUCT §6 | BLOCKED — owner | EXECUTION-RECORD S3.4; DECISIONS D-13-related | Retained — owner-gated |
| S3.5 | 3 | Owner-gated capability register | PRODUCT §6 | BLOCKED — owner | EXECUTION-RECORD S3.5; DECISIONS D-13 OWNER-GATED | Retained — owner-gated |
| S4.1 | 4 | Re-baseline `VERSION.md` | V3-RELEASE §2 | OPEN | VERSION.md line 9 stale; two `[Unreleased]` headings in CHANGELOG | Retained — corrections needed |
| S4.2 | 4 | Complete CHANGELOG for 3.0.0 range | V3-RELEASE §2 | OPEN | Two `## [Unreleased]` headings (lines 14, 464); zero S1/S18 mentions | Retained — corrections needed |
| S4.3 | 4 | ADR hygiene, signatures and index | DECISIONS §2, V3-RELEASE §6 | BLOCKED — owner | ADR-0027 PROPOSED awaiting owner signature | Retained — owner-gated |
| S4.4 | 4 | Cut `v3.0.0` (owner only) | V3-RELEASE §3 P6 | BLOCKED — owner | Conditions 1,2,5,7 must pass first; owner cuts tag | Retained — owner-gated |
| S5.1 | 5 | Provenance backfill (`data_sources` entry) | DECISIONS §3 (D-9 dep) | OPEN | Depends D-9 (roster sub-model, BLOCKED) | Retained — blocked on D-9 |
| S5.2 | 5 | `match_candidates` entity discriminator + race review queue | DECISIONS §3 (D-8 dep) | OPEN | Depends D-8 (team-race structure, BLOCKED) | Retained — blocked on D-8 |
| S5.3 | 5 | Close OQ-2 and OQ-3 in PRD + config/uma.php | DECISIONS §4 (OQ-3) | OPEN | OQ-3 RESOLVED manual-only; OQ-2 not in canonical OQ table | Retained — verify OQ-2 |
| S5.4 | 5 | ADR-0028 primitive layer (FQ-01..FQ-09) | — | OPEN | Owner FQ rulings needed | Retained — not yet in ADR index |
| S5.5 | 5 | ADR code gaps (0002, 0004, 0010, 0011, 0018) | — | OPEN | Respective owner rulings needed | Retained — not in ADR index |
| S5.6 | 5 | Artwork coverage (OQ-6) skill-icon + pre-run frame | DECISIONS §4 (OQ-6) | RESOLVED | OQ-6 RESOLVED; `<select>` confirmed; `skills.iconid` migration out of scope | Merged |
| S5.7 | 5 | Performance budgets (catalogue index, cockpit reads) | DECISIONS §3 Phase 5 | OPEN | Depends S1.1 quiet-box baseline | Retained — follows S1.1 |
| S5.8 | 5 | MWG accessibility items unimplemented | DECISIONS §5 (AdvisorAnalysisPanel) | OPEN — owner | `AdvisorAnalysisPanel.vue` 279×20 < 44px; BENO-COCKPIT-001 | Retained — owner-gated peer work |
| S5.9 | 5 | 1.0 gate: close 4 PRD questions, declare `/api/v1` shapes | V3-RELEASE §8 DoD | OPEN | Phases 0-5 prerequisite; `VERSION.md` gate table | Retained — release gate |

**Master slice disposition totals:** DONE 10 (S0.4, S1.3, S2.2, S2.3, S2.4, S2.6, S3.1, S3.2, S3.3, S5.6) · OPEN 10 (S0.1, S0.2, S0.3, S0.5, S1.1-part, S2.1, S4.1, S4.2, S5.1-S5.4, S5.5, S5.7, S5.9) · BLOCKED-OWNER 7 (S0.6, S2.5, S3.4, S3.5, S4.3, S4.4, S5.8) · PARTIALLY VERIFIED 1 (S1.1). Counts are approximate pending archived-original verification (§11).

## 3. KI series crosswalk

| KI | Original source | Canonical status | Evidence | Disposition |
|----|-----------------|------------------|----------|-------------|
| KI-101 | Refresh navigation count | DONE | `navigation-determinism.spec.ts:75-95` asserts `toEqual(['reload'])` | Merged (PRODUCT §5 R1) |
| KI-104 | ADR-0027 nested-disclosure (code half) | DONE — BLOCKED on owner signature | `UnityCupEditor.vue` `<form>` component root; 2 `<details>` hits are comment text | Split: code DONE, ADR signature BLOCKED (PRODUCT §5; QUALITY §4; V3-RELEASE §6) |
| KI-106 | — | INDEX-OPEN | 7 index-OPEN list | Retained — register closeout (RESIDUAL §Slice 4.1) |
| KI-114 | Deck-picker specs drive retired controls | DONE — premise REFUTED | Selectors resolve to live `SupportSlot.vue:221`, `Builder.vue:443/455/494`; 3 Legacy-step failures unclassified | Superseded premise (QUALITY §4; PRODUCT §7; RESIDUAL §Slice 2.5) |
| KI-116 | Reached card effects | DONE | `SupportCardEffects::forLevel()` at `SupportCardEffects.php:209`; `DeckAnalysis.php:109` | Merged (PRODUCT §5) |
| KI-119 | Current spirit proxy | DONE | Column migration `2026_11_10_010500`; `UnityCupPanel.vue:87` | Merged (PRODUCT §5) |
| KI-127 | Panel card wrapper | DONE | `TeamRaceFacilityPanel.vue:51` card wrapper | Merged (PRODUCT §5; MASTER S2.4) |
| KI-128 | `Delete career` placement | DONE | `Cockpit.vue:1230-1237` outside race `<section>` | Merged (PRODUCT §5 R2) |
| KI-132 | Build-target payload contract | DONE | `StoreBuildTargetRequest.php:69-71`; `BuildTargetController::store():172-178` (ADR-0025) | Merged (PRODUCT §5 R4) |
| KI-136 | Stale/ambiguous assertions | FIX | `full-career-journey.spec.ts` locator matches 2 disclosures (peer-owned) | Retained — peer coordinate (FRONTEND §5) |
| KI-137 | Stale/ambiguous assertions | FIX | `full-career-journey.spec.ts` 3 stale assertions | Retained (FRONTEND §5) |
| KI-140 | `FilterContractTest` no longer exists | ALREADY-FIXED | Test removed from `tests/` | Merged (FRONTEND §5) |
| KI-141 | Build target step `title`-only reasons | FIX | `BuildTarget.vue` | Merged (FRONTEND §5) |
| KI-142 | Duplicate block at `:4621` | TERMINAL | Second copy must be deleted | Retained — duplicate block collapse (FRONTEND §5) |
| KI-143 | `review-detail.spec.ts` unrendered variant | FIX | Describes unrendered variant | Merged (FRONTEND §5) |
| KI-144 | `Cockpit.vue` 0 live disclosures | ALREADY-FIXED | `ConfirmDelete.vue` own door | Merged (FRONTEND §5) |
| KI-145 | `lint:ui` 282 arbitrary values vs 219 baseline | FIX | Update `ui-lint-baseline.json` | Retained — debt paydown (FRONTEND §7) |
| KI-147 | `career-race-strip.spec.ts` strip cockpit | FIX | Strip cockpit stopped rendering | Merged (FRONTEND §5) |
| KI-148 | Purged by direct delete | TERMINAL | No code change | Merged (FRONTEND §5) |
| KI-149 | `.card > h2` 15→16px, 13 screens unmeasured | TERMINAL — pending measurement | Pending measurement pass | Retained (FRONTEND §5) |
| KI-150 | Page-level sticky unavailable | TERMINAL | Option 3; `AppLayout.vue:615-618` `@container` load-bearing | Merged (FRONTEND §5) |
| KI-151 | `--text-kpi` 0 hits | ALREADY-FIXED | 0 hits in `resources/css` | Merged (FRONTEND §5) |
| KI-152 | `base_stats`/`growth_rates` render | ALREADY-FIXED | `Catalog/Show.vue:44-46,150-152` | Merged (FRONTEND §5) |
| KI-154 | Scenario widget sweep | DONE | `ki-154-scenario-widget-sweep` landed | Merged (PRODUCT §3) |
| KI-159 | Deck step `title`-only reasons | FIX | `BuildTarget.vue`, `SupportSlot.vue` | Merged (FRONTEND §5) |
| KI-160 | Cockpit overflow 88px at 640px | FIX — DONE | `NextDecision.vue:346` wrapper | Merged (PRODUCT §5; FRONTEND §5) |
| KI-162 | — | INDEX-OPEN | 7 index-OPEN list | Retained — register closeout (RESIDUAL §Slice 4.1) |
| KI-165 | — | DONE | Type hygiene | Merged (V3-RELEASE §5 dep graph) |
| KI-166 | `/training-runs/{run}/races` overflow 59px 320px | CLOSED 2026-11-18 | `.card:has(> table.t)` `position: relative` | Merged (FRONTEND §1) |
| KI-167 | Scenario Selection focus root cause | DONE — root cause fixed | `:disabled="form.processing"` on scenario radio | Merged (FRONTEND §2; QUALITY §4; MASTER S2.2) |

**KI series totals:** 27 entries · DONE/FIXED/ALREADY-FIXED/CLOSED/TERMINAL ~19 · FIX/retrained ~4 · INDEX-OPEN 2 (KI-106, KI-162) · BLOCKED-owner 1 (KI-104 ADR signature).

## 4. OQ series crosswalk

| OQ | Question | Resolution | Evidence | Disposition |
|----|----------|------------|----------|-------------|
| OQ-2 | Source legality/refresh | Not in canonical OQ table | MASTER S5.3 references OQ-2 | Retained — verify OQ-2 exists/resolution |
| OQ-3 | Fetch scheduler default | RESOLVED — manual-only | `bootstrap/app.php:50-53` registers only `independent-training:complete` everyMinute | Merged (DECISIONS §4; V3-R3.1) |
| OQ-5 | Skill style eligibility | DEFERRED | Do not ship | Retained — deferred (DECISIONS §4; V3-R3) |
| OQ-6 | Artwork remainder | RESOLVED | Pre-run `<select>` confirmed; `skills.iconid` migration out of scope | Merged (DECISIONS §4; MASTER S5.6) |

## 5. Owner decision register crosswalk (D-1..D-13)

| D | Decision | Status | Evidence | Canonical home |
|---|----------|--------|----------|----------------|
| D-1 | MWG file banking (14 files) | DONE | Commit `178a9079`; typecheck 0, build 0 | DECISIONS §1; PRODUCT §5 P1.1 |
| D-2 | BO-011 dark risk token `#FF6F3B` + focus ring | DONE | Token in `app.css:531`; `DesignTokensTest` 17 passed | DECISIONS §1; PRODUCT §5 P1.2 |
| D-3 | Dependency advisories | DONE | Framework 13.42.0; `composer audit` 1 (unreachable `symfony/yaml`) | DECISIONS §1; PRODUCT §5 P2 |
| D-4 | Suite-truth fixes (P3.1-P3.7) | DONE | Peer commits (`44px`, `f8e428d9`, `veterans-transfer-requests.spec.ts`) | DECISIONS §1; PRODUCT §5 P3.1-P3.7 |
| D-5 | Batch commits | DONE | Committed `1b987cec` (2026-11-22); owner approval | DECISIONS §1; V3-RELEASE §6; EXECUTION-RECORD N0.1 |
| D-6 | BO-004 dark `To do` pill | DONE | `--color-risk: #FF6F3B`; ratios ≥ 4.5:1 | DECISIONS §1; PRODUCT §5 P5.5 |
| D-7 | `QWEN.md` truth | DONE | Commit `3cbbd8b9`; census 716→691 ≤ 708 | DECISIONS §1; PRODUCT §5 P5.3; QUALITY §2 |
| D-8 | Trackblazer/Unity Cup structure | BLOCKED | Owner ruling: citable source for team-race slots or terminal absence | DECISIONS §1; V3-RELEASE §6; MASTER S5.2 dep |
| D-9 | Roster sub-model | DONE | ADR-0039 (owner delegation 2026-11-18) authorises `roster_members`; migration `2026_11_24_120000`, `RosterMember` + `RosterMemberController`; `CockpitRosterTest` green | DECISIONS §1; V3-RELEASE §6; MASTER S5.1 dep; `docs/adr/0039-unity-cup-roster-recorded-sub-model.md` |
| D-10 | Preseason rounds table | BLOCKED | Owner ruling: `preseason_rounds` table + ADR or count-is-truth | DECISIONS §1; V3-RELEASE §6 |
| D-11 | Per-ancestor guest flag + identity input | OPEN | `StoreLegacySelectionRequest` validation needed | DECISIONS §1; PRODUCT §2 (4.6) |
| D-12 | ADR-0027 Turn/Decision Logging | BLOCKED | Owner signature `docs/adr/0027...:11` | DECISIONS §1; V3-RELEASE §6; MASTER S2.5/S4.3 |
| D-13 | ADR signatures (general) | OWNER-GATED | Multiple ADRs need owner signatures | DECISIONS §1; PRODUCT §6; MASTER S0.6/S3.4/S3.5/S4.3 |

**D series totals:** 13 · DONE 8 (D-1..D-7, D-9) · BLOCKED 3 (D-8, D-10, D-12) · OPEN 1 (D-11) · OWNER-GATED 1 (D-13).

## 6. P series (completed product work) crosswalk

| P | What | Evidence | Disposition |
|---|------|----------|-------------|
| P1.1 | 14 MWG files banked | Commit `178a9079`; cockpit-forms 14/14 | Merged (PRODUCT §5) |
| P1.2 | BO-011 dark risk token + focus ring | `app.css:531`; `DesignTokensTest` 17 passed | Merged (PRODUCT §5) |
| P2 | Dependency advisories | Framework 13.42.0; `composer audit` 1 unreachable | Merged (PRODUCT §5) |
| P3.1-P3.7 | Suite-truth fixes | Peer commits `44px`, `f8e428d9`, `veterans-transfer-requests.spec.ts` | Merged (PRODUCT §5) |
| P5.3 | `QWEN.md` landed true | Commit `3cbbd8b9`; census 691 ≤ 708 | Merged (PRODUCT §5) |
| P5.5 | BO-004 dark `To do` pill | `--color-risk: #FF6F3B`; ratios ≥ 4.5:1 | Merged (PRODUCT §5) |
| R1 (KI-101) | Refresh navigation count | `navigation-determinism.spec.ts:75-95` | Merged (PRODUCT §5) |
| R2 (KI-128) | `Delete career` placement | `Cockpit.vue:1230-1237` | Merged (PRODUCT §5) |
| R4 (KI-132) | Build-target payload contract | `StoreBuildTargetRequest.php:69-71` | Merged (PRODUCT §5) |

## 7. Release path crosswalk (P1–P6, V3-RELEASE §3)

| Priority | Slice | Description | Status | Blocker |
|----------|-------|-------------|--------|---------|
| P1 | Register truth + citation ratchet | Phase A + DocCitationParity ≤ 708 | PARTIAL | register 7 OPEN, ratchet 690 |
| P2 | Browser suite truth | Phase B — 15 specs classified | OPEN | 12 specs need isolated re-run |
| P3 | Quiet-box gate | Full suite on quiet box | OPEN | peer contention |
| P4 | Full browser suite | All 876 cases green or ≤3 named failures | OPEN | Phase B incomplete |
| P5 | Version truth + changelog | `VERSION.md` + `CHANGELOG.md` corrected | OPEN | line 9 stale; two `[Unreleased]` headings |
| P6 | Owner cuts tag | `git tag v3.0.0 <sha>` + push | BLOCKED | Conditions 1,2,5,7 |

## 8. ADR index crosswalk

| ADR | Title | Status | Related decision |
|-----|-------|--------|------------------|
| ADR-0020 | Sourced Character Artwork | ACCEPTED | D-1 |
| ADR-0021 | Sourced Character Artwork (mirror) | ACCEPTED | D-1 |
| ADR-0025 | Build Target Payload (two-key split) | ACCEPTED | R4 (KI-132) |
| ADR-0027 | Turn and Decision Logging | PROPOSED | D-12 (owner signature needed) |
| ADR-0029 | Per-Run Measurement Columns | ACCEPTED | KI-116, KI-119 |

## 9. Roadmap laws (L1–L8)

1. No slice closed on assertion — closes on quoted command output
2. Recorded owner ruling is terminal — reversing requires new written ruling
3. Baseline never raised to pass — `DocCitationParityTest.php:26` forbids
4. `career-preflight.spec.ts` is record of Phase E2 cut — not stale
5. Never `pint --dirty` — dirty set not your slice
6. One Pest process at a time — overlapping runs hang
7. Private index for every commit — peer files staged intermittently
8. Stale evidence is not evidence — re-derive SHA before attributing counts

## 10. Verified counts (audit summary)

| Metric | Count | Source |
|--------|-------|--------|
| Original source files consolidated | 15 | INDEX.md Round 30 §30.2 |
| Source files archived | 12 (10 `git mv`, 1 `mv`) | INDEX.md Round 30 §30.4 |
| Source files retained live | 2 (MASTER, RESIDUAL) + INDEX.md | INDEX.md Round 30 §30.4 |
| Canonical subject roadmaps created | 5 | INDEX.md Round 30 §30.4 |
| Canonical roadmaps with independent execution authority | 5 | §1 authority determination; §12 ownership census |
| Master slices (S0.1–S4.4 + S5.1–S5.9) | 33 | MASTER §5–§10 |
| KI series entries crosswalked | 27 | §3 above |
| OQ series entries crosswalked | 4 (OQ-2 unverified) | §4 above |
| Owner decisions (D-1..D-13) | 13 | §5 above |
| ADR entries | 5 | §8 above |
| Release-path slices (P1–P6) | 6 | §7 above |
| Conflict resolutions recorded | 4 (KI-114, KI-167, v3.0.0 tag blocker, MASTER vs IMPLEMENTATION) | INDEX.md Round 30 §30.3 |
| Peer-owned hands-off items | 3 (Dashboard.vue BO-008, full-career-journey.spec.ts, AdvisorAnalysisPanel.vue) | DECISIONS §5 |

## 11. Items pending verification against archived originals

The following could not be fully classified from canonical roadmaps alone and require re-reading the archived originals in `docs/roadmap/archive/`:

- S0.4, S1.2, S2.1 status confirmation (MASTER gives objectives/acceptance, not final status)
- OQ-2 existence and resolution (referenced in MASTER S5.3 but absent from canonical OQ table)
- S5.4 (ADR-0028) and S5.5 (ADR code gaps) — not in canonical ADR index
- Full disposition of the 10 archived originals' internal slices (S-series from IMPLEMENTATION-*.md, R-series, P-series beyond those listed)
- Which archived slices are fully superseded vs retained as historical only

These are recorded here rather than guessed, per Law #8 (stale evidence is not evidence) and the audit rule to record out-of-scope corrections rather than make them.

---
## 12. Round 31 ownership census (2026-11-24)

Deterministic census over the five canonical roadmaps' `MASTER Slice Ownership Map` blocks, reading the Canonical Owner column. Reconciled against the 33 MASTER slice IDs (24 headings S0.1–S4.4 plus 9 Phase-5 table rows S5.1–S5.9).

| Metric | Result |
|---|---|
| Distinct MASTER slices owned | 33 |
| Slices missing an authoritative owner | 0 |
| Slices claimed by more than one authority | 0 |
| Reconciliation against MASTER inventory | 33/33 match |

| Canonical owner | Owned slices | Count |
|---|---|---|
| `PRODUCT-AND-CAPABILITY-ROADMAP.md` | S3.1, S3.2, S3.3, S3.4, S3.5 | 5 |
| `FRONTEND-AND-UX-ROADMAP.md` | S0.1, S2.2, S2.4, S5.8 | 4 |
| `QUALITY-AND-VERIFICATION-ROADMAP.md` | S0.2, S1.1, S1.2, S1.3, S2.1, S2.3, S2.6, S5.7 | 8 |
| `V3-RELEASE-ROADMAP.md` | S0.5, S4.1, S4.2, S4.4, S5.9 | 5 |
| `DECISIONS-AND-DEPENDENCIES.md` | S0.3, S0.4, S0.6, S2.5, S4.3, S5.1, S5.2, S5.3, S5.4, S5.5, S5.6 | 11 |

Round 31 corrections applied: S0.2 assigned to QUALITY (gate tooling), S3.5 confirmed solely under PRODUCT (capability register content), S4.3 confirmed solely under DECISIONS (ADR hygiene/index), with V3-RELEASE retaining only a dependency citation. Referenced-but-not-owned citations (e.g. V3-RELEASE → S4.3 tag hand-off) are dependency references and do not claim ownership.

---

*Generated: 2026-11-23. Ownership census appended 2026-11-24 (Round 31). Crosswalk lives at `docs/roadmap/CROSSWALK-SOURCE-TO-CANONICAL.md`. Canonical IDs retained from original sources. Historical sources archived in `docs/roadmap/archive/`.*
