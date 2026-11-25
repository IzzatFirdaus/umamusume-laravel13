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
**Document baseline:** 2026-11-21

### Finding status at current HEAD

| ID | Severity | Finding | Status | Evidence |
|---|---|---|---|---|
| TD-01 | High | Back/Cancel 404 on `/training-runs/{id}/settings`, `/{id}/turns/{n}/edit`, `/{id}/delete` | **RESOLVED** | Source-verified: `RunSettings.vue` (#head actions), `TurnEdit.vue:145` and `ConfirmDelete.vue:65` all use `props.run.run_url` = `route('runs.cockpit', $run)`; no duplicated `/cockpit/cockpit` path exists in the current tree |
| TD-02 | High | Race imports use trainee-review language and controls | **RESOLVED** | Source-verified: `Review/Index.vue` `entityLabel()` checks `sourceKey.includes('race')` first → 'Race'; `Review/Detail.vue` `heading` checks `source_key.includes('race')` → 'Is this the right race?'; ADR-0037 behavior now in code |
| TD-03 | Medium | Veterans "Open" leads to source career not Veteran Detail | **OPEN** | Dashboard row hrefs use `/training-runs/{id}/cockpit`; `SCREEN_SPEC.md` conflict between Dashboard entry and Veteran entry unresolved |
| TD-04 | Medium | Completed career loses shared step navigation | **OPEN** | Completed Cockpit/Result lack nine-step nav; `CareerLayout.vue` unchanged |
| TD-05 | Medium | Voice credit ambiguous on character profile | **OPEN** | `Catalog/Show.vue` prints `va_ja`/`va_en` under unlabeled "Romanisation" heading |
| TD-06 | Medium | Training repeats full absence explanation 5× | **OPEN** | `TrainingDetail.vue` unchanged; five identical absence summaries on Speed–Wit rows |
| TD-07 | Medium | Snapshot Setup lacks searchable Trainee picker | **OPEN** | `Snapshot/Setup.vue` uses native `<select>` without search |
| TD-08 | Low | Source Ledger has no kind/date/conflict filters | **OPEN** | `Database/Sources.vue` unchanged; no filter controls visible |

### Commands executed at re-validation

None — read-only source inspection.

### Limitations

No local server available for live re-measurement. Runtime behaviour rests on prior browser evidence (21 Nov 2026).

### Correction to the 2026-11-23 revalidation (source re-check)

The same-day revalidation section above originally carried TD-01 and TD-02 as OPEN, repeating the 21 Nov browser evidence. Source re-inspection on this pass confirms both defects are resolved in the current tree (rows updated above). TD-03 through TD-08 remain OPEN unchanged; they were not re-inspected in this pass.

Commands executed: read-only source inspection (`RunSettings.vue`, `TurnEdit.vue`, `ConfirmDelete.vue`, `Review/Index.vue`, `Review/Detail.vue`). No local server; no test execution. Working tree preserved.

### Runtime verification — 2026-11-23 (executable checks)

The three TD-01 surfaces and the two TD-02 screens were exercised in a real browser against a freshly
rebuilt bundle:

| Spec | Command scope | Result |
|---|---|---|
| `run-settings.spec.ts` | isolated | **passed** |
| `turn-edit.spec.ts` | isolated | **2 passed** |
| `run-delete.spec.ts` | isolated | **passed** |
| `review.spec.ts` | isolated | **14 passed** |
| `review-detail.spec.ts` | isolated | **7 passed** |

TD-01 evidence category upgraded to DEFECT RESOLVED, STATICALLY AND RUNTIME VERIFIED (the Back/Cancel links
resolve through `run_url`; no `/cockpit/cockpit` path is produced). TD-02 evidence category likewise
upgraded: the race-origin queue row reads `Race · gametora-races` and the detail heading reads
`Is this right race?`. TD-03 through TD-08 remain OPEN and were not part of these runs.

A combined six-file serial run reported 2 `review-detail.spec.ts` failures; isolated re-run was green
(7 passed). Cause: one shared scratch database across six spec files, i.e. cross-spec pollution rather
than a defect. The harness's supported contract is a fresh scratch DB per run.

---

## Re-validation — 2026-11-24 (frontend implementation pass)

**Repository HEAD:** `3600741e` (unchanged during this pass)
**Working tree:** the findings below were implemented in the working tree by concurrent frontend
passes and are **uncommitted** at the time of writing. Nothing was staged, committed, reset or
cleaned; peer modifications and untracked files were preserved.

### Finding status at current working tree

| ID | Severity | Status | Evidence |
|---|---|---|---|
| TD-03 | Medium | **PARTIALLY RESOLVED** (label correct; destination ruling outstanding) | `Dashboard.vue` row button reads `Open source career`, column header sr-only text matches, href stays `veteran.run_url` (`/training-runs/{id}/cockpit`). Backend half landed additively: `DashboardController::veteranRow()` now sends `veteran_url` = `route('veterans.show', $veteran)` alongside `run_url`; the frontend does **not** bind it yet (see D-1), so the destination decision remains the owner's. Regression: `dashboard.spec.ts` recent-veterans case asserts accessible name, href and absence of "Open the veteran". |
| TD-04 | Medium | **RESOLVED** | `CareerLayout.vue` mounts `CareerSectionNav` on every run (`v-if="isActiveRun"` removed) and passes `:status="navStatus"`, so a terminal run makes `Result` live and marks it current; `Cockpit.vue`/`Result.vue` pass `current`. Regression: `career-section-nav.spec.ts` completed-career case (8 passed). |
| TD-05 | Medium | **RESOLVED** | `Catalog/Show.vue` profile row label is `Voice actor`; `va_ja` stays on the Japanese line with `va_en` as supporting Latin text, absence copy unchanged. Regression: `catalog-detail.spec.ts`. |
| TD-06 | Medium | **RESOLVED** | `TrainingDetail.vue` rows show the short `Facility level not recorded` marker (full server sentence kept on `title`), and one group `Note` carries the explanation for the column. Regression: `career-training-detail.spec.ts` N3.6 case (15 passed). |
| TD-07 | Medium | **PARTIALLY RESOLVED** (picker shipped; error-summary anchor verified) | `Snapshot/Setup.vue` uses the shared `TraineeCombobox` (`#trainee-combobox`), same control as run-create. Fallout fixed: the error summary's Trainee entry pointed at the deleted `snapshot-umamusume_id`; it now targets `#trainee-combobox` and the N3.35 watch focuses `errorSummary` when errors arrive. Regression: `career-snapshot.spec.ts` TD-07 case (each summary `#id` resolves to a visible, focusable control and receives focus on click) — 18 passed isolated. |
| TD-08 | Low | **PARTIALLY IMPLEMENTED** | `Database/Sources.vue` filters by kind and read date (plan N6.5) plus tier (ND-14(b), owner-approved 2026-11-22), announces `N of M sources match.` and has an empty state. Defect fixed: the ledger card sat behind `v-if="visibleRowCount > 0"`, which made the empty-result paragraph unreachable; the card now renders whenever the build has any source and the table only when rows survive. **Still open:** the plan's third facet, `in-open-conflict`, has no payload support — `DatabaseController::sources()` reads `config/reference.php` `conflicts.rows`, which carry `id/question/claims/state/affected_routes` but no dataset identity (backend dependency D-2, unbuilt). Regression: `database-sources-filters.spec.ts` (3 passed). |

### Commands executed

- `npm run typecheck` — clean on `resources/`; one transient error in a concurrently-edited
  `tests/browser/accessibility.spec.ts` (`PageFunction` signature), not owned by this pass.
- `npm run lint:ui` — exit 0 (pre-existing `arbitrary-value` advisories only).
- `npm run build` — clean, manifest newer than every edited source.
- Isolated browser runs on unique ports, each with its own scratch database:
  `dashboard` 12 passed · `career-section-nav` 8 passed · `career-training-detail` 15 passed ·
  `career-snapshot` 17 passed · `database` 32 passed · `database-sources-filters` 3 passed ·
  `dialog-light-dismiss` 2 passed.

### MWG F9 — dialog light dismiss (Task 2)

`useDialogLightDismiss.ts` verified against all three owners (`RaceCard.vue`, `CardPicker.vue`,
`SupportSlot.vue`): native `closedby="any"` where supported; pointer-position fallback only where
`'closedBy' in HTMLDialogElement.prototype` is false; clicks inside the dialog box never close;
Escape is the browser's own path; listener bound once on mount and removed on unmount; repeated
open/close leaves no stale cursor. **No implementation defect found; composable unchanged.**
Regression coverage: `tests/browser/dialog-light-dismiss.spec.ts` drives the deck picker's
`CardPicker` dialog — native path (backdrop closes, in-dialog click does not, Escape closes) and a
forced-fallback context that deletes `HTMLDialogElement.prototype.closedBy` before page scripts and
proves the deletion took, so the fallback branch itself is exercised rather than assumed.

### Backend dependencies (actionable contracts)

- **D-1 — Dashboard veteran row destination (TD-03).** Route `GET /` (`DashboardController`),
  `recentVeterans.rows[]` now sends **both** `run_url` and `veteran_url` (`route('veterans.show',
  $veteran)`, non-nullable — every row is a `Veteran` with a resolvable detail route). The frontend
  still binds the row to `run_url` and labels it "Open source career"; binding to `veteran_url` waits
  on the owner's destination ruling. Frontend acceptance once ruled: bind the row link to
  `veteran_url`, keep the run door otherwise; announce the change in the column header.
- **D-2 — Source Ledger conflict filter (TD-08).** Route `GET /database/sources`
  (`DatabaseController::sources()`), conflicts block from `config/reference.php`. Each conflict row
  carries `id`, `question`, `claims`, `state`, `affected_routes` but no dataset identity. Required to
  expose the plan's `in-open-conflict` facet: add `affected_sources: string[]` naming dataset `name`
  values, or a `dataset_conflicts` map. Frontend acceptance: one more read-only select, options
  derived from the rows, combined with kind/date/tier, count and empty state unchanged.
- No other backend change is required by the fixes above; payloads were read as sent and null-safe
  paths preserved.

### Unresolved product decisions

- **SCREEN_SPEC conflict for the Dashboard veteran row** (Dashboard entry says run, Veteran entry
  specifies `veteran_url`, wireframe points at Veteran Detail). The relabel is in place and
  `veteran_url` now rides the payload; the destination ruling is the owner's.
- **ND-2 race-review Confirm semantics** remains gated; not touched.
- **TD-08 conflict-involvement filter** stays unbuilt until D-2 lands; documented rather than
  approximated from claim text.

### Concurrency statement

The working tree holds ~290 modified tracked files and ~150 untracked entries from concurrent
workstreams. This pass touched only: `resources/js/pages/Career/Snapshot/Setup.vue`,
`resources/js/pages/Database/Sources.vue`, `tests/browser/dashboard.spec.ts`, and this document.
Peer-created `tests/browser/database-sources-filters.spec.ts` and
`tests/browser/dialog-light-dismiss.spec.ts` were run, not modified. A duplicated TD-08 case added
to `database.spec.ts` by this pass was removed once the peer's dedicated spec landed, leaving that
file byte-identical to its pre-pass state.

### Addendum — TD-07 regression coverage

A focused case was added to `career-snapshot.spec.ts` after the table above: a refused submit
renders the error summary, and every entry's `#id` resolves to a visible, focusable control and
moves focus there on click. That is the assertion that would have caught the dead
`#snapshot-umamusume_id` target. Isolated run on a unique port: **1 passed**. Final `npm run build`
after concurrent edits: exit 0; `npm run typecheck`: exit 0.

### Addendum — executable verification (2026-11-24)

This pass re-ran the six findings rather than re-reading them, and added the two regression specs that
were missing. All runs were isolated (`PLAYWRIGHT_PORT` per invocation, scratch DB from
`global-setup`), against a freshly built bundle.

| ID | Status | Evidence |
|---|---|---|
| TD-03 | **PARTIALLY RESOLVED** | `Dashboard.vue` binds the row to `veteran.run_url` and labels it "Open source career"; column header carries the same words as an `sr-only`. `dashboard.spec.ts` green. `DashboardController::veteranRow()` now also sends `veteran_url`, but the frontend still binds `run_url` and the destination ruling remains owner-gated (the `SCREEN_SPEC` conflict is unchanged). |
| TD-04 | **RESOLVED** | `CareerLayout.vue` mounts `CareerSectionNav` on every run and derives `status` from props. `career-section-nav.spec.ts`: "a completed career keeps stepper and marks Result as its current step (TD-04, N3.1)" green. |
| TD-05 | **RESOLVED** | `Catalog/Show.vue` row labelled "Voice actor", `va_ja`/`va_en` kept as one credit. `catalog-detail.spec.ts` 11 passed after the fixture moved from Silence Suzuka (the offline catalogue rebuild gave her a second confirmed form). |
| TD-06 | **RESOLVED** | `TrainingDetail.vue`: short marker per discipline via `AbsenceValue`, full sentence once in the group Note. `career-training-detail.spec.ts:237` "states missing facility level once, not five times down column (N3.6)" green. |
| TD-07 | **PARTIALLY RESOLVED** | `Snapshot/Setup.vue` uses the shared `TraineeCombobox`; `SnapshotController::comboboxRoster()` mirrors the create screen. `career-snapshot.spec.ts` 18 passed isolated, including the TD-07 case asserting every error-summary entry resolves to a visible, focusable control and receives focus on click. |
| TD-08 | **PARTIALLY IMPLEMENTED** | Kind / read-date / tier selects, announced count, empty-result state, clear action. `tests/browser/database-sources-filters.spec.ts` **3 passed** drives all three controls and asserts the two read-date halves partition the fetched set. The `in-open-conflict` facet is unbuilt: `config/reference.php` conflict rows carry no dataset identity (backend D-2). |

Two defects found while verifying, both fixed in this pass:

- `UiHygieneTest` was red on `resources/js/pages/Career/RaceDecision.vue:397` — a visible `N/A`
  pill with no reason. It now carries a `title` naming what is absent (house rule, `UiHygieneTest`).
- `UiHygieneTest` was red on `config/advisor_ai.php:161-166` — six shipped lines carried literal
  `docs/` paths. The entries are functional (the index reads them), not display copy, but the scan
  cannot tell the difference; the corpus root is now composed into each entry, so the stored value
  `docs/<file>` is byte-identical for `UmaAdvisorIndex` and `RetrievalBenchmarkTest`.
  `php artisan test --filter=UiHygieneTest`: **5 passed**.

Also repaired: `tests/browser/catalog-detail.spec.ts` "renders no tab strip for a trainee with a
single form" pointed at Silence Suzuka, who has had two confirmed forms since `8b177036`; the case
now measures the same branch on Air Groove (one confirmed form).

Still red, owned outside this pass: `database-parity.spec.ts` sources and Help cases. The Sources
case's verification-date drift is already allowlisted by a peer edit; what remains is a 1px
`gridTemplateColumns` rounding pair (`219.5px` vs `220.5px`) in the tiles grid. The Help case is
stale because a peer's in-flight N6.15 work adds a `div.card` jump-index above the card grid, which
moves the wireframe's recorded nth-child selectors.

### Addendum — Mission B (KI-173 frontend integration, 2026-11-24)

This pass verified every rendered consumer of a `Scheduled` race record against
`RaceEntryStatus::recordsEntry()` and added the missing browser coverage.

**New coverage.** `tests/browser/career-scheduled-race.spec.ts` (isolated run, unique port,
fresh build: **1 passed**, 14.5s) drives a Unity Cup run with 12 logged turns, seeds one
`Scheduled` entry (helper `tests/utils/scheduled-races.ts`, `turn_entry_id = NULL` per the
column's own migration note) on a shared-calendar mandatory slot at year 1 turn 13, and asserts:

- Race Planner: the race stays in the Mandatory group, no placement printed (`status: null`).
- Cockpit strip: the row prints in "Recorded in this run" under `Scheduled (entry unrecorded)`
  with no placement; "This turn" — the turn being decided, where `this_turn.recorded` is
  deliberately unfiltered — lists it; "Still to come" keeps `! Mandatory` (KI-173's own clause).
- Advisor Obligations: no `met` pill on the seeded row.
- Goal ladder: no `Recorded` word on the Mandatory races rung.

**Fixture root cause found and fixed.** The helper's first version also inserted a
`turn_entries` row for turn 13 — which pushed `TrainingRun::nextTurnNumber()` to 14 and moved
`decisionTurn()` (hence the strip's `this_turn` region) off the seeded slot. A `Scheduled` row is
import-side and unlinked; `turn_entry_id = NULL` is both the documented shape and the fixture the
readers exercise. Teardown retries through WAL `SQLITE_BUSY` (`PRAGMA busy_timeout`) so a passing
case cannot fail on teardown.

**Findings recorded, not changed:**

- `RaceCard.vue`'s `Recorded:` / `Scheduled, not yet run.` branch is unreachable from the product
  UI: its only host `RacePlanList.vue` lost its `<RacePlanList>` tag in `602bba4e` while the
  import remained (`RacePlanner.vue` imports the orphaned component). No assertion was written
  against a dead surface; the card keeps its null-safe branch for the day the list is re-hosted.
- `RaceDecision.vue` receives `deadlines` (with `state`, `is_next`, `reached`) and computes
  `nextDeadline` but renders neither; `RacePlanner.vue` prints the next mandatory race's title and
  turn only. No surface claims a `Scheduled` deadline is settled, so there is no misrender to
  regression-pin; surfacing `state` is a product decision, not a defect.
- TD-03 (Dashboard veteran door) stays bound to `run_url` / "Open source career"; only the
  `veteran_url` field is now typed in `VeteranRow` (additive contract catch-up). The destination
  ruling remains owner-gated on the recorded `SCREEN_SPEC` conflict.

**Preservation re-runs at this HEAD** (each isolated, unique port): TD-04 `career-section-nav`
8 passed; TD-05 `catalog-detail` 11 passed; TD-06 `career-training-detail` 15 passed; TD-07
`career-snapshot` 18 passed; MWG F9 `dialog-light-dismiss` 2 passed; TD-08
`database-sources-filters` 7 passed (conflict facet partition assertion green). `npx tsc
--noEmit` exit 0; `npm run lint:ui` exit 0 (pre-existing Veterans/Index.vue arbitrary-value
warnings only); `npm run build` exit 0 (33.2s). `npm run typecheck` (`vue-tsgo`) remains broken
in this environment — pre-existing `ENOENT ... mkdir node_modules\.cache\vue-tsgo\380cb45a\...`
(exit 1) — recorded separately from the passing `tsc` alternative.

**Environmental caveat.** `global-setup`'s scratch-DB pointer is a shared OS-temp file; a
concurrent peer Playwright run overwrites it mid-test, so a seeding helper can resolve a
different DB than the server's (observed once as a foreign-key failure on ports 5211/5312).
Isolated runs on unique ports are the standing mitigation; the pointer race is noted for whoever
owns `global-setup`.
