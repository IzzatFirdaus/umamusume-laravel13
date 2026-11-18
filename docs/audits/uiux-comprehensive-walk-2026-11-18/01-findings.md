# 01 — Findings register

Severity is a judgement of user impact; confidence is separate. Five findings are confirmed and fixed. Every cleared candidate is recorded with the measurement that cleared it so the next pass does not re-file it.

## BO-001 — In-app navigation leaves keyboard focus on the document body

| Field | Value |
|---|---|
| Severity | Medium |
| Confidence | High (measured before and after, mutation-checked) |
| Surfaces | Every page reached by an Inertia visit that changes the Inertia component; measured from the Dashboard via the sidebar links Skills, Database and Review |
| Observation | Before the fix, clicking a sidebar link changed the URL and `h1` but left `document.activeElement` as `BODY` on all three destinations and after browser Back. The wizard, by contrast, focused the new `h1` on every step (`SetupLayout.vue:128-169`). A keyboard or screen-reader Trainer therefore got no signal that the page had changed and had to tab from the top again |
| Mechanism | `AppLayout` is a persistent shell: its `onMounted` hook does not re-run on a visit, so the wizard's focus policy did not apply to the rest of the app |
| Framework | WCAG 2.4.3 focus order and 3.2.x predictable behaviour; W3C APG and web.dev navigation-focus guidance |
| Evidence | `journey.jsonl` pre-fix focus records; `.scratch-uma/uiux-walk-2026-11-18/focus-check` (node probe: `nav-Skills`, `nav-Database`, `nav-Review` all `tag: BODY`) |
| Fix | `resources/js/spa.ts` registers one `router.on('navigate')` listener that focuses `main h1, h1` with `tabindex=-1` only when the Inertia **component changed** and the response carries no validation errors. Same-page filters and per-page alert focus are left alone |
| Verification | `tests/browser/navigation-focus.spec.ts`: both cases green (14 passed / 2 skipped across 3 spec files at port 8402). Mutation check at port 8403 with the listener removed: the focus case fails (`toBeFocused` received `inactive`), the same-page guard still passes. Source restored and rebuilt |
| Status | **FIXED in working tree, uncommitted** |

## Cleared candidates (measured, not filed)

| Candidate | Measurement | Verdict |
|---|---|---|
| 26 controls under 24 px on `/database/races` | 25 are race-name links in table row headers, 16 px tall; minimum distance between any two link centres is 50 px at both 390 and 1280. WCAG 2.5.8 spacing exception holds | Cleared |
| 7 "unnamed" controls on support-card detail at 390 px | `checkVisibility()` false for all of them; they sit inside closed `<details>` panels (`Fetched source`, `More`) | Cleared (known closed-disclosure artefact) |
| 16x16 radio inputs on Preferences and the scenario step | Each is inside a `label` whose rendered box is 324x78 or at least 99x44; the label is the target | Cleared |
| 103 of 161 tables without `caption`/`aria-label` | The four per support-card detail page each have a preceding heading (`Effects, as the source states them`, `Card`, `Where this card is used`, `Provenance`) and axe scans on these pages pass | Not filed; heading context supplies the name, no WCAG failure measured |
| Focus on `BODY` after the 17 run-route visits | Those were full `page.goto` document loads, where body focus is normal; the wizard's in-app visits already focused `h1` | Cleared (observation artefact, not the BO-001 defect) |
| Document overflow | 140/140 crawl visits and 20 contrast readings report zero overflow at 320/390/1280 | Cleared; the previously committed KI-166 fix holds |
| Contrast | 10 routes x 2 viewports, zero computed pairs below 4.5:1 (3:1 for large text) | Cleared |
| Console errors | Zero across 140 crawl visits and 20 contrast readings; one 404 in the journey was my own wrong URL (`/training-runs/1/snapshot`; the route is `/career/snapshot`) | Cleared |

## BO-002 — Turn log scroll region was not keyboard reachable

| Field | Value |
|---|---|
| Severity | Medium |
| Confidence | High (measured before and after at 320 and 768) |
| Observation | `/training-runs/{run}/timeline` wrapped its 704 px wide table in `div.overflow-x-auto` with no `role`, no `tabindex` and no `aria-label`; `scrollWidth 704` against `clientWidth 254` at 320 px, and no `role="region"` anywhere on the page. DESIGN.md:458-466 names the turn log as one of the two wide tables that must scroll inside a focusable named region |
| Fix | `resources/js/pages/Career/Timeline.vue:259` now carries `role="region" tabindex="0" aria-label="Turn log, scrollable"`, matching `Runs/Import.vue:598` |
| Verification | `tests/browser/audit-followup-2026-11-18.spec.ts`: the region takes focus, ArrowRight increases `scrollLeft`; 39/39 green across the follow-up, timeline, dashboard and review suites at port 8404 |

## BO-003 — Transfer Requests title rendered below the page-title tier

| Field | Value |
|---|---|
| Severity | Low |
| Confidence | High (computed style) |
| Observation | `/veterans/transfers` `h1` computed 18px (`text-lg`) while `/veterans` and `/training-runs` both computed 30px (`text-3xl`); DESIGN.md:368 pins the Critical title tier |
| Fix | `resources/js/pages/Veterans/TransferRequests.vue:174` uses the sibling title classes |
| Verification | same spec asserts `30px`; green |

## BO-004 — Review queue stated one tier fact twice

| Field | Value |
|---|---|
| Severity | Medium |
| Confidence | High (rendered label census) |
| Observation | Eleven sampled rows of `/review` each rendered both a `No match` pill and an `Unknown` provenance badge for the same non-Fuzzy tier. DESIGN.md:974-980 records A-96's fix: keep the badge, drop the pill when the tier is not Fuzzy |
| Fix | `resources/js/pages/Review/Index.vue:199` now renders the pill only for `match_tier === 'Fuzzy'`; `tests/browser/review.spec.ts` pins the single provenance word instead of the duplicate |
| Verification | follow-up spec asserts zero `No match` in an Unknown row; full review suite green |

## BO-005 — Dashboard absence reasons were mouse-only

| Field | Value |
|---|---|
| Severity | Medium |
| Confidence | High (DOM inspection) |
| Observation | The Energy and Team rank tiles printed `not recorded`; their reasons existed only as `title` attributes on descendant nodes, with no `.sr-only` text and no visible prose. DESIGN.md:893-899 calls `title`-only absences a superseded idiom and names these two tiles as remaining instances |
| Fix | `resources/js/pages/Dashboard.vue` computes `tileAbsenceNotes` and renders the same hints as visible `text-xs text-ink-muted` prose in the card; `Tile.vue` is unchanged, so every other caller keeps its layout |
| Verification | follow-up spec asserts the Energy sentence is visible; full dashboard suite green |

## Further cleared candidates from the static pass

- Pager spans on `/training-runs` are 36px with `aria-disabled=true`, but `tabIndex -1` makes them non-focusable: they are not activation targets, so neither WCAG 2.5.8 nor the house 44px rule applies (C-07/C-10 cleared).
- C-05, C-06, C-08 (title-only reasons in SparkSummary and RacePlanner) and C-09 (locale-formatted dates) were not re-measured in this pass and are recorded in `06-static-candidates.md`, not filed.

**No severity-4 or severity-3 finding was confirmed in this pass.** The absence of a finding is not a claim that no problem exists; see `04-excluded-findings.md`.
