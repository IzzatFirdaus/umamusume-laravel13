# GP-04 Findings register + Phase 4 divergence classification

One compact block per material finding, every brief field carried inline. Affirmative verification
results stay where produced, not duplicated here: 16/16 figures MATCH (01) and the brand-hairline
1.41:1 reproduces on the 2026-10-24 page (01).

## GP-01 — Trigger document superseded in place, no erratum
- Severity: Medium · Category: Documentation accuracy · Surface: `GREY-SCALE-APP-VS-HFW.md` · Env: document/git audit
- Expected: a document whose recommendation was implemented states so or carries an erratum
- Actual: `bc102252` (2026-11-05) implemented options 1+2 verbatim and names the document in the body; the doc carries no erratum; its "current" tables describe `ce90b0fa`, not HEAD
- Evidence: `git log -1 bc102252`; trigger last touched `78755486` 2026-11-03, clean at HEAD
- Impact: readers trusting "current" mis-audit the tree; this brief itself arrived expecting the 2026-10-31 state · Confidence: Confirmed

## GP-02 — All 16 trigger figures verified
- Severity: Informational · Category: Documentation accuracy · Surface: trigger's two tables · Env: Python recomputation from file-read hex (State A `ce90b0fa`)
- Expected: figures match recomputation · Actual: 16/16 MATCH to printed precision
- Evidence: 01 ratio table; `app.css:136-144,152,165,169` @State A; `shell.css:10-14`
- Impact: the trigger is trustworthy as a measurement record of its date · Confidence: Confirmed

## GP-03 — Brand-hairline 1.41:1 open item resolved (no discrepancy)
- Severity: Informational · Category: Documentation accuracy · Surface: `app.css:162` figure · Env: recomputation
- Expected (brief): flag "surface unidentified" if 1.41 is not reproducible from any listed token
- Actual: reproducible: `#D1D3E0` on `#F8F9FD` = 1.415:1, the page at the 2026-10-24 ruling (`5ba966ed`); later pages 1.367 / 1.363. True when written; the page moved twice
- Evidence: 01 open-item table; `5ba966ed` body quotes the same 1.41:1
- Impact: closes the brief's open item affirmatively · Confidence: Confirmed

## GP-04 — The doc's assumed tier threshold is not the contract
- Severity: Low · Category: Documentation accuracy · Surface: `panel→page`/`panel→sunken` thresholds · Env: source read
- Expected: confirm the "distinct tier" threshold (~1.10 per the doc) from `DESIGN.md`
- Actual: the contract floor for the light surface step is **1.05** (`DESIGN.md:190`), enforced at `tests/Feature/DesignTokensTest.php:903`; no ~1.10 threshold exists. Post-change `panel on page` = 1.057 (clears by 0.007); the doc's "about 1.13" for `panel→sunken` recomputes to exactly 1.130
- Evidence: `DESIGN.md:190`; `DesignTokensTest.php:903`; 01 post-change table
- Impact: with the real floor, change 1 is safer than the doc's framing implied · Confidence: Confirmed

## GP-05 — `rule on page` now passes with a thin margin
- Severity: Informational · Category: Contrast · Surface: `#8C8A9A` on `#F4F4FF` · Env: recomputation (State B)
- Expected: ≥ 3:1 (WCAG 2.2 SC 1.4.11, G-18) · Actual: 3.093:1, margin +0.093; live guard `DesignTokensTest.php:894-899`
- Evidence: 01 post-change table; `app.css:165-167`
- Impact: any future page lightening re-opens this pair; +0.093 is the number to watch · Confidence: Confirmed

## GP-06 — Light surface step clears its floor by 0.007
- Severity: Low · Category: Contrast · Surface: `#FBFBFD` on `#F4F4FF` · Env: recomputation (State B)
- Expected: ≥ 1.05 (`DESIGN.md:190`; `DesignTokensTest.php:903`) · Actual: 1.057:1, the thinnest margin in the light ramp
- Evidence: 01 post-change table
- Impact: genuine guard; any panel move toward white re-collapses the step the parity slice restored · Confidence: Confirmed

## GP-07 — Surface parity achieved and visually verified
- Severity: Informational · Category: Visual consistency · Surface: all four screen pairs · Env: Chromium repo-local, scratch DB, 1440x900 + 390x844, light
- Expected (trigger's goal): "a screen and its wireframe read alike"
- Actual: both sides read as near-white cards on a tinted page with quiet 1px edges; the app no longer paints the card as the brightest surface. Per-pair classification: visually similar (02)
- Evidence: `screenshots/` (16 PNGs); 02 rows
- Impact: parity goal met on surfaces; ink remains the difference · Confidence: Confirmed (structured vision-model readings, criterion-referenced)

## GP-08 — HFW wizard wireframe carries a dark-slate sidebar variant
- Severity: Low · Category: Visual consistency · Surface: `frontend-v2-wireframes/career-scenario.html` · Env: Chromium 1440x900, light
- Expected: same light shell chrome as the other HFW pages
- Actual: dark slate/navy sidebar while canvas, cards and ink match the app; the app's light shell has no such variant
- Evidence: `wizard-scenario-1440x900-hfw.png` vs `-app.png`
- Impact: this pair is valid for canvas/cards/ink only; a wireframe exploration, not an app gap · Confidence: Confirmed

## GP-09 — App secondary-button fill sits slightly off the cool grey family
- Severity: Low · Category: Visual consistency · Surface: `/veterans` "Offer for Transfer" button · Env: Chromium 1440x900, scratch DB
- Expected: all grey surfaces in one temperature family
- Actual: the fill reads a warmer neutral than the surrounding cool slate/indigo greys
- Evidence: `veterans-1440x900-app.png`; 02 off-family notes
- Impact: token-level polish item for the owner; no contrast failure; out of grey-scale scope · Confidence: Inferred (visual reading; no token re-measurement)

## GP-10 — Divergence classified: deliberate decision, later deliberately converged on surfaces
- Severity: Informational · Category: Decision record · Surface: the grey language as a whole · Env: git history + provenance comments (below)
- Expected (brief): classify decision / drift / oversight / needs-owner-input
- Actual: **deliberate decision** — an owner-supplied palette chain with dated provenance; the surface half was then deliberately closed by `bc102252`
- Evidence: commit table below; `shell.css:22`; `app.css:125-134,149-167`
- Impact: the trigger's core classification claim is confirmed by the record · Confidence: Confirmed

## Phase 4 — classification basis

Provenance comments, all dated and attributed: `shell.css:22` ("Track 2-D, owner-signed
2026-10-15"); `shell.css:3` (mirror note, "read 2026-10-21"); `app.css:125-134` (2026-10-24
synthesis, per-value source citations); `app.css:149-167` (four dated `rule` rulings).

| Commit | Date | Body records |
|---|---|---|
| `63d1cdd2` | 10-15 | Track 2-D repair |
| `e30763dc` | 10-24 | colourful-surfaces owner ruling (`DESIGN.md:171-194`) |
| `5ba966ed` | 10-25 | Umamusume brand palette; four given values stepped for G-18, with figures |
| `1ac7f46c` | 10-25 | re-cut from three in-repo sources, per-value provenance |
| `9fa70510` | 10-25 | "Applies the owner-supplied final palette" — the pair the trigger measured |
| `bc102252` | 11-05 | "HFW grey parity": options 1+2 together, re-measured |

**Classification: deliberate decision, then deliberate convergence on surfaces.** No commit reads
as drift or mistake; every value entered with a dated recorded reason, and `git log -S` corroborates
each hex movement (`#F8F9FD` in/out `5ba966ed`/`1ac7f46c`; `#F4F5F9` in `9fa70510`, out `bc102252`;
`--color-panel: #FBFBFD` re-enters at `bc102252`). The trigger's classification is confirmed; the
addition this audit supplies is that the surface half was then ratified away on 2026-11-05, leaving
ink as the deliberate remainder.
