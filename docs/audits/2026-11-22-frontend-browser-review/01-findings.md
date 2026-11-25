# 01 Findings | Trainer Desk frontend browser review

**Review date** 21 November 2026, MYT  
**Browser** Connected Chrome, Midnight Circuit on app screens

[Coverage and evidence bounds](00-coverage.md) · [Plan reconciliation and owner decisions](02-plan-reconciliation.md)

## Executive assessment

All 60 registered screen, redirect and status templates rendered or redirected. Two high-priority problems affect safe navigation and the meaning of a Review decision. The Career Workspace has broken Back and Cancel links, including the primary exit from the delete confirmation. Race imports are presented as trainee matches. Fix these before further layout refinement. The 320 px sample showed no document-level horizontal overflow. This audit includes targeted manual accessibility checks and establishes no site-wide WCAG verdict.

| Severity | Count | Finding IDs |
| --- | ---: | --- |
| Critical | 0 | — |
| High | 2 | TD-01, TD-02 |
| Medium | 5 | TD-03 through TD-07 |
| Low | 1 | TD-08 |

TD-01 through TD-06 are reproducible defects or departures from an identified visible requirement. TD-07 and TD-08 are outstanding planned usability improvements. Owner-gated design proposals are tracked in the [reconciliation](02-plan-reconciliation.md#owner-gated-decisions) and carry no defect severity.

## TD-01 | High | Back and Cancel can lead to the 404 screen

**Where and plan** Run Settings `/training-runs/36/settings`, Correct Turn `/training-runs/36/turns/29/edit`, and Delete Career confirmation `/training-runs/36/delete`. Midnight Circuit at 1280 × 900 for all three, with Run Settings also checked at 320 × 900. Direct relevance to N3.1 navigation and N3.13 Run Settings. [Plan N3.1](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L396), [Plan N3.13](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L504)

**Expected** “Back to Cockpit” and “Cancel” should restore the current career context. The registered cockpit destination ends in one `/cockpit`; link text and context should describe the actual destination. [Route registry](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/routes/web.php#L147), [WCAG 2.2 SC 2.4.4](https://www.w3.org/TR/WCAG22/#link-purpose-in-context)

**Observed** The three links ended in `/training-runs/36/cockpit/cockpit`. The browser rendered the heading “That page does not exist” when that exact destination opened at 22:03:57. The separate career stepper Cockpit link resolved to the valid URL. On the delete screen the alternative “Back to all careers” is present. The main Cancel path can interrupt recovery from a consequential screen.

**Reproduce safely**

1. From Careers, open the active Unity Cup Rice Shower career, then Run Settings. Inspect “Back to Cockpit”.
2. From its Timeline, open “Edit turn 67”. Inspect “Back to Cockpit”.
3. From the Cockpit, open “Delete this run” to the confirmation screen and inspect or follow “Cancel”. The linked URL contains the repeated segment. Open that exact href directly if the client-side error appears over the current page. Leave the acknowledgment and destructive action untouched.

**Browser evidence** Settings at 21:57:22 and 22:05:05. Turn Edit at 21:58:06. Delete at 21:58:09 and the Cancel activation at 22:02:57. Exact destination verified at 22:03:57 on the later asset. Settings was also measured again at 320 px at 22:06:34. [Coverage](00-coverage.md#verified-safe-interactions-and-exceptional-states)

**Correction** Use the controller-provided canonical Cockpit URL, which already includes the suffix. Review the three templates together. Verify that the safe exit reaches the same run even on a narrow layout. **Confidence high.** Destination and all three hrefs were observed. [RunSettings.vue](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/resources/js/pages/Career/RunSettings.vue), [TurnEdit.vue](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/resources/js/pages/Career/TurnEdit.vue), [ConfirmDelete.vue](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/resources/js/pages/Career/ConfirmDelete.vue), [CockpitController.php](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/app/Http/Controllers/Career/CockpitController.php#L392)

## TD-02 | High | Race imports use the trainee-review language and controls

**Where and plan** Review queue `/review` at 1280 × 900 and 320 × 900, and Review Case `/review/304` at 1280 × 900, Midnight Circuit. N2.1 entity labels and N2.8 review filters, with any race Confirm semantics gated under ND-2. [Plan N2.1](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L288), [Plan N2.8](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L351), [Plan decision register](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L146)

**Expected** The review display and eligible actions should follow the entity type established by the candidate's source key. The plan expects a visible Race kind for race rows. [Accepted ADR-0037](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/adr/0037-match-candidate-entity-is-source-key.md)

**Observed** “Hanshin Umamusume Stakes (Story Race Use)” appeared as “Trainee Umamusume · from gametora-races”. Its Detail asked “Is this the right trainee?” and presented an “Umamusume id” control and “Create catalog record for Hanshin Umamusume Stakes (Story Race Use)”. The entity filter offered Race, so the queue's own taxonomy could have described the row correctly. The wrong description can steer a Trainer toward a verdict or record type that does not fit the source.

**Reproduce safely**

1. Open Review and read the kind on the first `gametora-races` row. American Oaks and other race-origin rows in the list used the same trainee phrase.
2. Follow the Hanshin Umamusume Stakes row into Detail and read the heading and field names.

**Browser evidence** Queue at 21:55:10 and 22:07:16, including a later-bundle 320 px recheck. Case at 21:58:31. 304 pending matches were visible during the review. The Detail's action controls were inspected without activating them.

**Correction** Trace the entity from the source discriminator through both payloads, use race-specific copy and expose only permitted race-review controls. Keep ND-2's behavior unresolved until the owner rules. **Confidence high** for the rendered mismatch. The server response to any verdict and the correctness of filtered results were unverified. [Review Index](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/resources/js/pages/Review/Index.vue#L69), [Review Detail](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/resources/js/pages/Review/Detail.vue), [ReviewController](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/app/Http/Controllers/ReviewController.php)

## TD-03 | Medium | Recent Veterans “Open” leads to the source career

**Where and plan** Dashboard `/`, Midnight Circuit at 1280 × 900, also visible at 390 × 900. Related to N3.37's distinction between Veteran Detail and its source run. The consolidated plan has no direct Dashboard-link slice. [Plan N3.37](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L720)

**Expected** The accessible table context “Open the veteran” should match its destination. The approved-source record has a conflict. The Dashboard screen entry describes a link to the run, while the Veteran entry specifies `veteran_url` and the Dashboard wireframe points to Veteran Detail. [SCREEN_SPEC Dashboard](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/SCREEN_SPEC.md#L1432), [SCREEN_SPEC Veteran entry](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/SCREEN_SPEC.md#L2511), [Dashboard wireframe](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/design-research/frontend-v2-wireframes/dashboard.html#L175), [WCAG 2.2 SC 2.4.4](https://www.w3.org/TR/WCAG22/#link-purpose-in-context)

**Observed and reproduce safely** Open Dashboard, go to “Recent veterans”, and follow the first Vodka “Open”. The browser lands at `/training-runs/22/cockpit`, titled “Career: Vodka”, showing “This run is complete.” The separate Veteran Library links to `/veterans/8`, which shows the banked record, lineage and Veteran actions. The other two Dashboard row hrefs also used the Cockpit address pattern.

**Browser evidence** Dashboard links observed at 21:47:48 and 22:04:24. Actual row activation confirmed the destination at 22:03:03. Library and Veteran Detail were separately visited at 21:55:05 and 21:58:14.

**Correction** Reconcile the two SCREEN_SPEC entries. If the row is the Veteran door, bind it to the Veteran detail URL. If the run door is the intended contract, use “Open source career” in the row and column context and keep an explicit Veteran door. **Confidence high** for the mismatch, with destination intent subject to the documented conflict. [Dashboard.vue rows](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/resources/js/pages/Dashboard.vue#L701), [Current row link](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/resources/js/pages/Dashboard.vue#L723)

## TD-04 | Medium | The completed career loses its shared step navigation

**Where and plan** Completed Cockpit `/training-runs/22/cockpit` and Result `/training-runs/22/result`, compared with active Cockpit and Result for run 36, Midnight Circuit at 1280 × 900. Completed Result rechecked at 320 × 900. N3.1. [Plan N3.1](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L396)

**Expected** The plan calls for keeping the appropriate career step navigation reachable after completion and preserving link semantics and the current-step indication. Retired-run links have separately documented constraints. [Plan acceptance and risk](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L396)

**Observed and reproduce safely** From Careers, open the completed Vodka Result. It provides Timeline, Veteran and Cockpit doors but has no shared nine-step navigation or Result step indicator. Its Cockpit also lacks the shared steps. Compare with the active Rice Shower Cockpit, which shows nine steps and a clear disabled explanation for Result. The active run's read-only Result page was reachable from its Timeline and marked Result as current.

**Browser evidence** Completed Result at 21:57:56, completed Cockpit at 22:02:07, active Result at 22:02:00. Newer-bundle Result rechecks at 22:05:03 and 22:06:27 still showed only the global Careers current marker.

**Correction** Keep a consistent navigation spine for the completed journey, show only eligible destinations as actionable, and mark Result current when applicable. Verify active, completed and retired contracts separately. **Confidence high** for the completed/active difference. Retired behavior remains unverified. [CareerLayout.vue](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/resources/js/layouts/CareerLayout.vue#L150)

## TD-05 | Medium | The character profile leaves its voice credit ambiguous

**Where and plan** Vodka in the Trainee Catalogue `/umamusume/vodka`, Midnight Circuit at 1280 × 900 and 320 × 900. Adjacent to N6.8 catalogue truth, a separate visible copy problem. [Plan N6.8](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L1004)

**Expected** The character name and the voice credit should have distinct labels. Accepted ADR-0012 Decision 4 defines `name_ja` and `va_ja`/`va_en` as separate profile fields. Cygames identifies the character as Vodka and 大橋彩香 as the voice credit. [Accepted ADR-0012 Decision 4](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/adr/0012-card-detail-fields-and-images.md#L13), [Cygames official Vodka profile](https://umamusume.jp/character/vodka/)

**Observed and reproduce safely** Open Vodka through the catalogue or Veteran detail and find Profile. The Japanese name is ウオッカ. The immediately following row, labelled only “Romanisation”, displays “大橋彩香 (Ayaka Oohashi)”. The English rendering can correctly describe the credited actor. The table heading leaves the actor's role unnamed and can be read as a transliteration of the character's name.

**Browser evidence** Profile text at 22:00:54 and 22:07:20. The pinned template prints `va_ja` and `va_en` under that heading.

**Correction** Use “Voice actor” or “Voice credit” as the field label, retaining the Romanised actor name as supporting text. Keep the character-name fields and absence behavior distinct. **Confidence high** for the exact label and data, supported by the publisher. [Catalog Show.vue](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/resources/js/pages/Catalog/Show.vue#L276)

## TD-06 | Medium | Training repeats the full facility absence explanation five times

**Where and plan** Training Detail `/training-runs/36/training`, Midnight Circuit at 1280 × 900 and 320 × 900. N3.6. The placement question stays under ND-1. [Plan N3.6](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L441), [Plan ND-1](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L170)

**Expected** N3.6 calls for a compact absence on each discipline and one explanation near the group. The approved form placement and source-based refusal to infer Level 1 still apply. [Plan acceptance](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L444)

**Observed and reproduce safely** Open Training from the active Unity Cup cockpit with no logged facility levels. Inspect Speed through Wit. Each row's accessible detail summary repeats the long “No facility level is recorded for this training on any turn this run has logged…” explanation. It forms part of the rendered label information for the five choices. The figures correctly remain N/A.

**Browser evidence** Training at 21:55:40, 22:04:47 and 22:06:07. The later 320 px DOM yielded five matching absence summaries and the distinct Speed, Stamina, Power, Guts and Wit accessible choices.

**Correction** Put the longer explanation once in the Disciplines group and keep a short meaningful label beside each unavailable value. Leave the published values and HFW field positions intact. **Confidence high** for repetition. The entry-dependent preview behavior remains unverified. [TrainingDetail.vue](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/resources/js/pages/Career/TrainingDetail.vue)

## TD-07 | Medium | Snapshot Setup lacks a searchable Trainee picker

**Type** Planned usability improvement. **Where and plan** `/career/snapshot/setup`, Midnight Circuit at 1280 × 900 and 320 × 900. N3.31. [Plan N3.31](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L666)

**Expected** The snapshot Trainee field should reuse the searchable, keyboard-operable Trainee combobox while retaining trainee-level identity. [Plan scope and acceptance](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L668)

**Observed and reproduce safely** From Dashboard Quick actions, open “Start from existing snapshot” then Setup. At the required Trainee field, inspect the options. The rendered control is the native `select#snapshot-umamusume_id` with a long roster and no search input. The ordinary Create screen provided a searchable Trainee combobox. A native select can support keyboard interaction, so this observation establishes the missing search improvement rather than a keyboard failure.

**Browser evidence** Setup at 21:49:19, 22:02:16 and 22:08:36. Its actual Vodka option was used to identify the existing record for the read-only Wizard Profile. No selection was saved.

**Correction** Reuse the shared trainee search control, preserve the required label and keyboard behavior, and retain the existing trainee identifier. **Confidence high** for this visible gap. [Snapshot Setup.vue](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/resources/js/pages/Career/Snapshot/Setup.vue)

## TD-08 | Low | The Source Ledger has no visible kind, date or conflict filters

**Type** Planned usability improvement. **Where and plan** Source Ledger `/database/sources`, Midnight Circuit at 1280 × 900 and 320 × 900. N6.5 follows N6.3 and N2.11. The per-row evidence-tier proposal remains ND-14. [Plan N6.5](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L977), [Plan ND-14](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L183)

**Expected** A Trainer should be able to narrow the ledger by source kind, date status or conflict involvement once the source data supports those facets. [Plan filter acceptance](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/docs/plan/implementation-plan-2026-11-20.md#L980)

**Observed and reproduce safely** Open Database, then Sources. Inspect the entire rendered path through the verification note, Tiers, Sources, Open conflicts and the explanation. There is no search form, select, filter control or empty-filter state. Source and conflict rows were still reachable.

**Browser evidence** Ledger at 22:00:20 and 22:07:08. The DOM inspection found no visible form fields or filter buttons at either width.

**Correction** Sequence the record and union-read work first, then expose the planned read-only filters and announce the result count. **Confidence high** for the absent visible controls. Completeness of the backing source flags remains unverified. [Sources.vue](https://github.com/IzzatFirdaus/umamusume-laravel13/blob/1b987cecd52db460d8f78417fb41f87fdcd70a7b/resources/js/pages/Database/Sources.vue)


---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22

### Finding status at current HEAD

| ID | Severity | Finding | Status | Evidence |
|---|---|---|---|---|
| TD-01 | High | Back/Cancel 404 on `/cockpit/cockpit` | **OPEN** | `RunSettings.vue`, `TurnEdit.vue`, `ConfirmDelete.vue` unchanged |
| TD-02 | High | Race imports use trainee-review language | **OPEN** | `Review/Index.vue`, `Review/Detail.vue`, `ReviewController.php` unchanged |
| TD-03 | Medium | Veterans "Open" leads to source career | **OPEN** | `Dashboard.vue` row hrefs unchanged |
| TD-04 | Medium | Completed career loses shared step nav | **OPEN** | `CareerLayout.vue` unchanged |
| TD-05 | Medium | Voice credit ambiguous | **OPEN** | `Catalog/Show.vue` unchanged |
| TD-06 | Medium | Training repeats absence explanation 5× | **OPEN** | `TrainingDetail.vue` unchanged |
| TD-07 | Medium | Snapshot Setup lacks searchable picker | **OPEN** | `Snapshot/Setup.vue` unchanged |
| TD-08 | Low | Source Ledger lacks filters | **OPEN** | `Database/Sources.vue` unchanged |

### Commands executed at re-validation

None — read-only source inspection.

### Limitations

No local server available for live re-measurement. Runtime behaviour rests on prior browser evidence (22 Nov 2026).

---

## Rendered re-validation — 2026-11-24

**Method.** Playwright/Chromium against a session-private SQLite database (`LOG_CHANNEL=stderr`,
`workers=1`). Every verdict below was measured on the rendered page, not read out of source. This
section supersedes the read-only 2026-11-23 table above, which was accurate when it was written.

| ID | Rendered verdict | Evidence |
|----|------------------|----------|
| TD-01 | **FIXED** | Run Settings, Correct Turn and Delete confirmation all address `{run_url}/cockpit`; followed at 1280px and again at 390px (`tests/browser/review-findings-regression.spec.ts`) |
| TD-02 | **FIXED** | A `gametora-races` row reads `Race · from gametora-races`; its case asks `Is this the right race?` and renders no Umamusume id control |
| TD-03 | **PARTIALLY FIXED** (label correct; destination ruling outstanding) | The row is labelled `Open source career`, bound to `run_url`, and the Veteran door stays explicit (`tests/browser/dashboard.spec.ts`). The payload now also carries `veteran_url` (`DashboardController::veteranRow()`), so the audit's alternative rebind is unblocked on the backend; the frontend still binds `run_url` and the destination decision remains the owner's. |
| TD-04 | **FIXED** | A completed career keeps the nine-step spine; Result is live and carries `aria-current="step"` on its own page |
| TD-05 | **FIXED** | The voice credit row is labelled `Voice actor` (`tests/browser/catalog-detail.spec.ts`) |
| TD-06 | **FIXED** | The ~180-character absence sentence appears in zero text nodes, sr-only included; one group Note carries the explanation and each marker keeps a short label |
| TD-07 | **FIXED** (picker + summary anchor) | Snapshot Setup picks the trainee through `#trainee-combobox` and the error summary's Trainee entry resolves to that input (`tests/browser/career-snapshot.spec.ts` TD-07 case) |
| TD-08 | **PARTIALLY FIXED** | Kind / Read date / Tier filters narrow the ledger and announce `N of M sources match.`; the `in-open-conflict` facet remains unbuilt pending backend D-2 (`DatabaseController::sources()` sends no dataset identity on conflict rows) |

```bash
LOG_CHANNEL=stderr PLAYWRIGHT_PORT=8435 PLAYWRIGHT_SCRATCH_DB=$PWD/.scratch-uma/frontend-findings-2026-11-24/browser-8435.sqlite \
  npx playwright test tests/browser/review-findings-regression.spec.ts --workers=1   # 6 passed
LOG_CHANNEL=stderr PLAYWRIGHT_PORT=8432 PLAYWRIGHT_SCRATCH_DB=$PWD/.scratch-uma/frontend-findings-2026-11-24/browser-8432.sqlite \
  npx playwright test tests/browser/dashboard.spec.ts tests/browser/catalog-detail.spec.ts tests/browser/career-snapshot.spec.ts --workers=1   # 41 passed
```

**Carried forward.** TD-03's rebind variant needs a `veteran_url` field the Dashboard payload does
not send, so it stays an owner decision plus a backend change. Retired-run contracts remain
unmeasured, as the 2026-11-23 limitations recorded.
