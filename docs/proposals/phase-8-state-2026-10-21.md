# Phase 8 state — 2026-10-21

**HEAD:** `a6715220` · **ahead:** 5 of `origin/master` · **tree:** clean at write time

## §1 — Per-slice verdicts (8.1–8.8)

| Slice | Verdict | Evidence |
|---|---|---|
| 8.1 | landed (side agent) | `b43e0edb` a11y disclosure work; `SetupLayout.vue` stepper |
| 8.2 | no-op | `role="bar"` does not exist in the tree |
| 8.3 | landed (partial) | `d664f948` catalogue filter busy/error states; Career/Preferences forms carry `form.processing` + `role="alert"` across 14/17 files |
| 8.4 | landed | `07230bfc` responsive.spec.ts at 44px |
| 8.5 | deferred | only `unity_cup` has an update endpoint; isolation needs two |
| 8.6 | **not run in this pass** | see §3 |
| 8.7 | landed | `openAllDisclosures` helper (`tests/utils/accessibility.ts`); `legacy:149` rewrite `d6c62d6f`; `:330` deferred |
| 8.8 | landed | `RouteCoverageTest.php` (16 routes) + `fc43df47` guard |

## §2 — Route coverage scope

`tests/Feature/RouteCoverageTest.php` pins 16 live page routes to axe scans; `Catalog/Show` is
excluded by design (its exclusion and lift condition are commented in the file). Extending: add the
route to the whitelist, add the axe assertion, run the Feature test.

## §3 — Gate classification (Step 9)

**Not run in this pass.** `npm run build`, `npm run typecheck`, `npm run lint:ui` are green on the
current tree (verified at each landed commit). The full `php artisan test` + `npx playwright test`
classification — and the naming of the 7 Feature failures (`RenderedCopyHygieneTest`,
`UiHygieneTest`, and the rest) — remains unperformed. `grand-concert-panel.spec.ts` is expected
green: the `5e7da543` fix is present (`ScenarioPanel.vue` gate `careerGoalsOn || onPanels.length
=== 0`; the spec asserts `Verified \d{4}-\d{2}-\d{2}`).

## §4 — Deferred items with unblocking conditions

| Item | Unblocks when |
|---|---|
| `:330` anomaly | pseudo-element / `position: fixed` / `@container` inline-size containment behaviour localised. The viewport genuinely scrolls (`scrollLeft` proves it); no element box escapes; four hypotheses excluded |
| Trackblazer `shop_coins` | a stored column ruled in scope — currently **derived** from `shop_coins_by_placement` config (`TrackblazerPanelBuilder.php:372`), correctly |
| `UraDuelPanel.vue` / `UraMilestoneTracker.vue` removal | `tests/Feature/UraFinaleSurfacesTest.php:303-304,353` retired deliberately — they are read by a Feature test |
| `5.1` sort whitelist | controller change ruled in scope |
| 8.5 scenario isolation | a second scenario update endpoint exists |
| `vue-tsc` fixes | Step 12 measurement result |
| Grand Concert panel | new-scope playbook (the panel is a documented "undrawn" absence, `7.11`) |
| Trackblazer additions (Shop Drawer, Epithet Tracker, Year-End Gate, Twinkle Star Climax) | new-scope playbook |

## §5 — Landed slices this phase

| Commit | Subject |
|---|---|
| `72d83476` | chore(career): remove two unreferenced Cockpit panels |
| `f456ed8c` | chore(career): remove unreferenced ProShopPanel |
| `749c35a5` | feat(catalog): start-career label names the trainee; va_ja is lang="ja" |
| `d6c62d6f` | test(legacy): rewrite the six-node case against the post-a11y DOM |
| `a6715220` | refactor(pagination): unify on ui/Pagination.vue; delete the duplicate |

## §6 — Docs landing (side agent)

`03acb133` landed the research extracts and scenario plans: `UMAMUSUME_REFERENCE.md`,
`[Rosy_Dreams]Rice_Shower_Unity-Cup.md`, `Our-Grand-Concert-plan.md`, `Trackblazer-plan.md`,
`URA-Finale-plan.md`, `02-unity-cup.md`, plus the execution-record updates and the plan `.txt`.
These introduced new-scope UI claims (Grand Concert panel, Trackblazer Shop Drawer / Epithet Tracker
/ Year-End Gate / Twinkle Star Climax) which are tracked as deferred, not part of this arc.

## §7 — Closing sentence

Phase 8 closes 2026-10-21 with 8.1/8.2/8.4/8.7/8.8 landed or no-op, 8.3 partial, 8.5 deferred, and
8.6's full gate classification unperformed in this pass. Deferred items are tracked with their
unblocking conditions above. The arc's remaining work is the deferred-owner queue and the new scope
the docs landing introduced.