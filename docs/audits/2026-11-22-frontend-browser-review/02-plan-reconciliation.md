# 02 Plan reconciliation | Trainer Desk frontend browser review

**Review date** 21 November 2026, MYT  
**Plan** [Consolidated Implementation Plan, 20 November](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md)  
**Ordering and history** [Master roadmap](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/roadmap/MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md) · [UI/UX roadmap](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/roadmap/UI-UX-IMPLEMENTATION-ROADMAP-2026-11-18.md)

[Coverage and data limits](00-coverage.md) · [Verified findings](01-findings.md)

A status applies to the visible part of a slice at the observed build and data. Plan §7 requires separate implementation evidence to close a slice, including appropriate gates. “Observed complete” here applies to the named browser-visible acceptance only. The plan's historical baseline, published repository baseline and locally running browser build are separately identified in [Coverage](00-coverage.md#baseline-and-source-authority).

## Baseline and cross-cutting slices

| Slice | Observable status | Browser evidence or prerequisite |
| --- | --- | --- |
| N0.1, N0.3–N0.6 | Not applicable | Worktree, lint, test registration, issue register and citation gates are repository work. The local worktree and quoted gate results were unavailable. |
| N1.2 | Unverified | Browser sampling is documented. The full baseline requires its own environment and suite output. |
| N2.1 | Defect | Race-source rows and a Review detail use trainee vocabulary and fields. [TD-02](01-findings.md#td-02--high--race-imports-use-the-trainee-review-language-and-controls) |
| N2.3 | Unverified | Race Detail displayed a populated track on one record and gaps on another. Offline seeding provenance and completeness were beyond browser evidence. |
| N2.5 | Unverified | The observed skills run had no booked SP. Base-cost terminology remains in two provenance controls. Price parity needs an existing hinted skill with both panels and a recorded budget. |
| N2.6 | Unverified | No no-scenario run was identified for the Cockpit check. The observed scenario-aware Cockpits rendered. |
| N2.7 | Not applicable | Config-flag and scenario-literal migration requires source/gate evidence. Trackblazer-specific live career branches were not exercised. |
| N2.8 | Partially complete | Entity and match-tier selectors, selection and a disabled zero-selected Reject control were rendered. Filter response, staging and bulk write behavior were unverified. Entity labels require TD-02. |
| N2.9 | Owner-gated | The observed queue showed 304 pending matches and included race-source rows. Disposal requires owner authorisation and its own measured data outcome. |
| N2.10 | Owner-gated | Completed Result and Save Veteran rendered. There was no transferred-case record observed and the transfer lists were empty. ND-4 governs the refusal presentation. |
| N2.11 | Unverified | Source Ledger read was visible. Inline versus registry timestamp recovery and data-channel correctness were not determined from the rendered subset. D-9 governs the full backfill. |

The other Phase 0–2 slices concern Git, command documentation, telemetry guards, scheduling or tag gates. Their results are outside the live screen baseline. [Plan Phases 0–2](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L199)

## Career Workspace

| Slice | Observable status | Browser evidence or prerequisite |
| --- | --- | --- |
| N3.1 | Partially complete | Active links retain step semantics. Team Editor marked Cockpit current. Completed Cockpit and Result lacked the shared steps, and three contextual Back/Cancel links go to a 404. [TD-01](01-findings.md#td-01--high--back-and-cancel-can-lead-to-the-404-screen) · [TD-04](01-findings.md#td-04--medium--the-completed-career-loses-its-shared-step-navigation) |
| N3.2 | Partially complete | Active Cockpit showed last recorded 67 and next entry 68. Completed Cockpit explicitly showed no next entry. Stats and unknown resources were visible in a separate section. One complete combined strip and all resource variants remain unverified. |
| N3.3 | Unverified | Recorded/this-turn/still-to-come sections were visible. The observed records had too little race history to establish the bound. |
| N3.4 | Unverified | Where to go displayed and the Settings, Scenario and Team doors were reachable for run 36. The exact set of duplicated hrefs within that grid was not measured. |
| N3.5 | Owner-gated | ND-6 governs future wording for past unrecorded obligations. UI absence provides insufficient evidence that a race was missed. |
| N3.6 | Defect | Five identical facility explanations repeated on the later bundle. Entry-dependent preview and focus remain unverified. [TD-06](01-findings.md#td-06--medium--training-repeats-the-full-facility-absence-explanation-five-times) |
| N3.7 | Partially complete | Race Decision showed a named aptitude axis in the column and a selected race panel. Individual Enter controls were still present. The one-selection-to-action flow and a recorded consecutive-race count were unverified. |
| N3.8 | Partially complete | “Learned on this run 0” was visible above the candidate area. Base-price words remained in provenance details. Populated learned and hint-adjusted comparisons require a matching record. |
| N3.9 | Unverified | Event entry and its unknown-source state rendered. The Alarm Clock explanation's exact repeat count and disclosure were not exercised. |
| N3.10 | Partially complete | The absence of Legacy configuration was stated above the recorded moment, with a Legacy Lab door. A recorded configuration on the event screen and any sourced odds remained unverified. |
| N3.11 | Partially complete | Slot destination appeared in the wizard's picker and its Equip buttons. Run Deck exposed separately named Replace and Remove controls. The Remove safeguard was unverified. |
| N3.12 | Partially complete | The one-entry Timeline showed no pager and contained phase disclosures. Multi-page, fallback marker and expanded phase body variants were unverified. |
| N3.13 | Unverified | All four settings concerns were present in the visible form areas. Deliberate dirty-state and Wit note checks were not completed. Related Back link defect is verified. |
| N3.14 | Partially complete | Team Editor used Identity, Season performance and counters groups. Active help identified Current Spirit as a transcribed manual proxy. Dirty state and saved feedback remain unverified. |
| N3.15 | Partially complete | Unity Cup displayed a Recorded on this run overview, an estimate marker and Edit team state. Team Editor rendered for the same run. Per-figure qualifiers and all capability variants remain unverified. |
| N3.16–N3.18 | Owner-gated | Respect the recorded field placement, approved deletion scope and unresolved slot-5 design premise. See ND-1, ND-8 and ND-7. |

[Plan Career Workspace slices](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L386)

## Wizard, Snapshot and Veterans

| Slice | Observable status | Browser evidence or prerequisite |
| --- | --- | --- |
| N3.19 | Unverified | Build Target rendered. A local value just below the ceiling and equality were not exercised. |
| N3.20 | Unverified | Wizard and run Deck rendered. Rejected slot input would require an already-visible error or a submission. |
| N3.21 | Unverified | Wizard Deck opened the picker. The upgraded/current-tier marker on a recorded card was not exercised. |
| N3.22 | Unverified | Blank Preflight and disabled Create were seen. Isolating zero-parent Skipped requires a draft that meets the other checks. |
| N3.23 | Partially complete | Preflight displayed “What this tool cannot tell you yet” and a separate Independent Training section. Exact limitation grouping and completeness were not established. |
| N3.24 | Unverified | Vodka Profile rendered with no saved selection. A legitimately selected alternate form and chosen scenario were not changed. |
| N3.25 | Unverified | Unselected Trainee page rendered. The selected-trainee/no-form variant depends on a draft write or existing draft. |
| N3.26 | Unverified | Growth row section rendered at 320 and 1280 px. The tile positions were not individually measured. |
| N3.27 | Unverified | Backward links and the draft warning were present. Fresh upstream-change messaging and preservation require changing the saved draft. |
| N3.28 | Owner-gated | ND-9 preserves the existing wizard rulings. |
| N3.29 | Not applicable | Doc/citation/test maintenance requires a separate accepted baseline. |
| N3.30 | Partially complete | Entry had a readiness list and a meaningful unavailable-Review hint. Its heading and old Entry/Setup/Review step words still differed from the proposed unified onboarding copy. |
| N3.31 | Missing | Setup used a native roster select without a searchable trainee control. [TD-07](01-findings.md#td-07--medium--snapshot-setup-lacks-a-searchable-trainee-picker) |
| N3.32 | Unverified | Required Year, Month and Half were present. Selected Setup-to-Review label parity was unverified. |
| N3.33 | Partially complete | Per-field Unknown controls and “Mark unfilled values as unknown” were visible. The feedback stated that entered numbers, including zero, are left alone. This effect was not exercised. |
| N3.34 | Unverified | Unrecorded values and the numeric interface were visible. Timing and complete Energy limitation copy were not separately checked. |
| N3.35 | Unverified | No existing rejected submission was available. Field-linked error summary and retention of parsed values and unknown states remain unverified. |
| N3.36 | Unverified | No populated snapshot was supplied. A live summary driven by non-empty form state remains unverified. |
| N3.37 | Partially complete | Separate Career and Veteran statuses, Open the source run, and clear transfer consequence copy observed. All absence/Provenance branches and a transferred record remain unverified. |
| N3.38 | Partially complete | Trainer notes and lineage headings were visible on Veteran 8. The view also showed a descendant career. All empty and populated ancestry presentations and the visual grouping remain unverified. |
| N3.39 | Not applicable | End-to-end create and dated spec maintenance are outside this read-only browser audit. |
| N3.40 | Owner-gated | ND-10 governs structural and persistence proposals. |

[Plan Wizard, Snapshot and Veteran slices](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L558)

## Tags and post-tag frontend work

| Slice | Observable status | Browser evidence or prerequisite |
| --- | --- | --- |
| N4.4 | Unverified | Preflight showed an Independent Training disclosure. Creation, cooldown and transitions were unverified. |
| N4.6–N4.9 | Not applicable | Spec reconciliations, roadmap errata, CI and pre-tag records require evidence of their own. |
| N5.1 | Unverified | Error-summary presentation and retained input require an existing validation state or an authorised separate fixture. [GOV.UK error summary](https://design-system.service.gov.uk/components/error-summary/) · [GOV.UK validation](https://design-system.service.gov.uk/patterns/validation/) · [WCAG Error Identification](https://www.w3.org/WAI/WCAG22/Understanding/error-identification) |
| N5.2 | Unverified | IME Enter was not used in command or save controls. Confirm with authorised data in the explicit composition states. |
| N5.3 | Unverified | Read pages rendered after their data loaded. Reproducible loading/empty/deferred states were outside the observations. |
| N5.4 | Unverified | Manual samples exist for focus and reflow. The a11y tag and suite acceptance remain unverified. |
| N5.5 | Not applicable | Baseline decision record requires repository/governance work. |
| N5.6–N5.9 | Owner-gated | D-7 through D-11 govern source backfill, team/roster data, open PRD issues and the primitive layer. |
| N5.10 | Unverified | Code-gap reconciliation is not inferable from rendered screens. |
| N5.11 | Owner-gated | Respect the authorised artwork scope and remaining OQ-6 calls. |
| N5.12–N5.13 | Unverified | No formal performance or Modern Web Guidance gate was measured. |
| N5.14–N5.15 | Not applicable | Parked debt triggers and release gates are outside the browser verification. |

[Plan Phases 4–5](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L756)

## Database and Help

| Slice | Observable status | Browser evidence or prerequisite |
| --- | --- | --- |
| N6.1 | Partially complete | Hub and Ledger distinguished verification from fetching today. Conflict count/row parity across sources was unverified. |
| N6.2 | Partially complete | The verification note was high on the Ledger. Count placement and the per-source supplies distinctions were not individually checked. |
| N6.3 | Unverified | Conflicts and a stable 1,400 m anchor were rendered. Data-model and relationship correctness cannot be inferred from that anchor. |
| N6.4 | Partially complete | Races exposed a 1,400 m Source conflict link to the Ledger. Destination and backlink round-trip were not exercised. |
| N6.5 | Missing | No kind, date status or conflict filters were visible. [TD-08](01-findings.md#td-08--low--the-source-ledger-has-no-visible-kind-date-or-conflict-filters) |
| N6.6 | Observed complete for sampled absence display | In the race list the missing surface, distance, band and fan requirement exposed distinct reasons. Debut and Chukyo Junior Stakes details preserved the difference between absence and a recorded value. Other record combinations remain unverified. |
| N6.7 | Partially complete | Caps and panels remained at the top, followed by sections for four scenarios. Matrix-word and detail disclosure behavior need further verification. |
| N6.8 | Partially complete | Catalogue cards refer readers to the trainee profile for growth and Vodka showed both base and growth values. Filtered return path was unverified. Voice credit labelling is separately documented. [TD-05](01-findings.md#td-05--medium--the-character-profile-leaves-its-voice-credit-ambiguous) |
| N6.9 | Partially complete | Scoped counts, filters and “What unconfirmed means” appeared. Active constraint echo and definition disclosure behavior remain unverified. |
| N6.10 | Partially complete | A visible Trackblazer Pro Shop heading, explanatory copy and “Rating (one guide)” distinguish source facts from opinion. Show all 19 was visible. Show fewer and long-catalogue behavior remain unverified. |
| N6.11 | Partially complete | Kind and Spend site selects rendered. Count, clear and no-match interactions remain unverified. |
| N6.12 | Partially complete | Global and JP choices, cadence text and section provenance were visible. Filtering, shorthand coverage and the source drawer were unverified. |
| N6.13 | Partially complete | Families, categories and a final-stat odds section were rendered. Attribution and specific contextual Legacy/Veteran round trips were unverified. |
| N6.14 | Partially complete | Nine reference destinations were present, with scoped counts and disclosure text. Hover and focus treatment across all themes remains unverified. |
| N6.15 | Partially complete | Help offered a question index, separate limitations, keyboard and troubleshooting sections. Each shortcut and the full anchor set were unverified. |
| N6.16 | Partially complete | Named Help links on Trainees, Races and Sources used the expected anchors. The Help page and matching topics rendered separately. Direct click and focus/scroll checks were unverified. |
| N6.17 | Partially complete | All reference routes and Help rendered at 1280 px in Midnight Circuit. Hub, Races, Scenarios, Sources and Help also had document overflow checks at 320 px. The rest of the width/theme matrix and formal assertions are unverified. |
| N6.18 | Owner-gated | ND-11 through ND-16 apply. |

[Plan Database and Help slices](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L931)

## Owner-gated decisions

| Decision and slice | Binding boundary for this review |
| --- | --- |
| ND-1 / N3.16 | Keep the recorded Training totals, Event identification and Inheritance moment-field placement. Any reversal requires an owner ruling and a dated SCREEN_SPEC override. TD-06 can be fixed within the current placement. |
| ND-9 / N3.28 | Preserve catalogue density/continue/pagination, form-grain Profile, Build Target, Legacy, Deck and Preflight rulings. The accepted record-only and deck-slot constraints still govern. |
| ND-10 / N3.40 | One-accent hierarchy, identity layout, unavailable-door hint, final stats and PageHeader contracts, sticky/widening treatment and Energy Band persistence stay under their recorded decisions. The widening variant needs its override record. |
| ND-11 / N6.18 | Retain the flat nine-tile Database hub and both Ledger entry points under the current wireframe ruling. |
| ND-12 / N6.18 | Keep the labelled Global release pill. Demotion depends on an override. |
| ND-13 / N6.18 | Retain current neutral Spark/event chips pending a palette ruling. |
| ND-14 / N6.18, N6.5 | Keep the page-level tier reference. A per-source tier and tier filter require data and owner approval. Ordinary kind/date/conflict filters can proceed under N6.5. |
| ND-15 / N6.18 | Turning intentionally inert Help rules into links requires an updated ruling for the selected destinations. N6.16's inbound Help deep links are a separate slice. |
| ND-16 / N6.18 | Preserve the accepted outlier emphasis and current scenario headers. Identity colours, column-max emphasis or charts require their own design decision. |

The dated design decision records Azure Daybreak and Midnight Circuit as approved palettes and retains EVA-01. Preserve those identities when checking accessibility. ND-2, ND-4, ND-6, ND-7 and ND-8 separately govern race confirmation, re-banking, unrecorded obligation wording, slot-5 interpretation and deletions. [Plan decision register](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L146) · [DESIGN §2 palette ruling](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/DESIGN.md#L335) · [SCREEN_SPEC overrides](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/SCREEN_SPEC.md#L4190)

## Recommended remediation sequence

1. **Repair safe exits.** Fix TD-01 at the shared URL contract or all three verified callers. Verify that the Cancel and Back links return to the same career under N3.1. Recheck the active and completed routes before broad layout work.
2. **Correct review entity presentation.** Complete N2.1 in queue and detail before treating N2.8 as usable for race triage. Verify filter classification with existing records and the safe allowed actions. Leave ND-2 unresolved.
3. **Restore completed-run context and align link purpose.** Close the N3.1 completed navigation gap, then resolve the Dashboard requirement conflict and align its row label with the actual destination. Correct the catalogue voice-credit label independently.
4. **Simplify read/record forms under their current contracts.** Address N3.6 and N3.31 without altering game values, Identity or HFW placement. Verify N3.20, N3.35, N5.1 and N5.2 with an authorised fixture before changing shared feedback or keyboard behavior.
5. **Complete source prerequisites before the Ledger filters.** Verify N2.11 and N6.3 data, then N6.4 bidirectional links and N6.5 filters. Keep unresolved source conflicts visible and hold off on derived row tiers.
6. **Recheck on the landed build.** Confirm the remaining states, widths and all three themes in the existing verification environment. Prioritise keyboard focus under sticky UI, measured contrast/targets and the validation recovery paths. Use N1.2, N5.4 and N6.17 evidence to close the appropriate acceptance items.

This review is evidence for triage. Approved requirements and decision records remain the authority for changes.


---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-21 / 2026-11-18
**Status:** Reviewed; findings preserved as historical evidence.
**Limitations:** No local server available for live re-measurement.
