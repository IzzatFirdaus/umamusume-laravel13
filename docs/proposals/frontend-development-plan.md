# Trainer Desk 2.0 — Frontend Development Plan

> **Status: proposal, reference only — filed 2026-10-05. Not authorization.**
>
> Derived from `docs/proposals/design-2.0.md` and `docs/proposals/screen-spec-2.0.md` (the 2.0 design
> target), under the scope authorized by `docs/adr/0020-trainer-desk-2-program.md`. It binds the rewrite to
> the repository's real routes, controllers, models, `config/scenarios.php` and gates. Where this plan and a
> landed ADR conflict, the ADR wins (`AGENTS.md` §2). Every deferred-computation section of the two source
> docs stays deferred here: race prediction, inheritance computation, per-training stat yields, and the
> Grand Concert mechanics panel.
>
> **For agentic workers:** REQUIRED SUB-SKILL — use `superpowers:subagent-driven-development` (recommended)
> or `superpowers:executing-plans` to execute this plan slice by slice. Steps use checkbox (`- [ ]`) syntax.

**Goal:** Finish porting the Blade screens to Inertia + Vue, then build the Trainer Desk 2.0 cockpit
screens, without dropping the regression-tested accessibility contract and without printing a game fact the
repository cannot source.

**Architecture:** Laravel 13 + Inertia v3 + Vue 3 + TypeScript. A controller returns
`Inertia::render('Page', $props)`; `resources/js/spa.ts` globs `pages/**/*.vue` into the resolver. Every
scenario-specific surface reads `config/scenarios.php` and never branches on a scenario name. Every number
renders with a provenance label (Confirmed / Calculated / Estimated / Unknown), and an unrecorded value
renders as `N/A` with a `title`.

**Tech stack:** Laravel 13, PHP 8.5, Pest 4, `inertiajs/inertia-laravel` v3, `@inertiajs/vue3` v2, Vue 3,
TypeScript 7, Tailwind CSS v4 (`@theme static` tokens), Vite 7, Playwright (`@playwright/test`).

**Spec:** `docs/proposals/design-2.0.md`, `docs/proposals/screen-spec-2.0.md`, `SCREEN_SPEC.md` (current
screens), `DESIGN.md` (visual system), `docs/research-scratch/PROCESS-PLANS.md` §`## trainer-advisor.md`.

## Global Constraints

Copied verbatim from the governing docs; every task implicitly includes this section.

- **Tokens only.** No `zinc-*` utility, no `dark:` fork outside the theme override block (G-19,
  `DesignTokensTest`). `resources/css/app.css` `@theme static` is the single source of token truth; no new
  token file.
- **No scenario-name branching.** Components read the resolved matrix from `config/scenarios.php`
  (G-33, `CONSTRAINTS.md` D-240). Adding a fifth scenario is one config entry and no component edit.
- **Unrecorded renders `N/A`.** Never a default, never a dash (`AGENTS.md` §5 Copy). Numbers carry a
  `title`.
- **Trust vocabulary on every figure.** Confirmed / Calculated / Estimated / Unknown (`ADR-0020` §2).
  An estimate is never written as a fact.
- **No new package without owner approval** (`AGENTS.md` §5). `vue-tsc` stays out until TypeScript 7
  ships `typescript/lib/tsc`.
- **Accessibility is a conformance target, not a carry-across.** The 2.0 UI conforms to **WCAG 2.2
  level AA**, and the shipped 0.1.0 screens are remediated to the same bar in Phase A0 (§12). Keyboard
  paths, visible focus, `h-11`/`min-h-11` (44px) targets, labels, `role`/`aria-*`, and a
  `prefers-reduced-motion` path are the floor, not the ceiling (`ADR-0020` §5; G-11; G-47).
- **UX laws are the review rubric.** Every slice names the laws it satisfies and the ones it
  deliberately trades (§13). A screen that violates Hick's, Miller's, Jakob's, or Fitts's law, or that
  shows a state only through colour, is not done.
- **Pest conventions.** `tests/Feature` gets `TestCase` + `RefreshDatabase` + `withoutVite()` from
  `tests/Pest.php`; never add `uses()` or `withoutVite()` per file. `tests/Unit` has no database.
- **Test strategy for ports.** Behavior is asserted on **Inertia props** (`assertInertia`); rendered copy
  and a11y move to **Playwright** specs under `tests/browser/`. No new Blade-HTML assertions on ported
  screens.
- **Hand-off per slice:** targeted tests → `php artisan test --compact` → `vendor/bin/pint --dirty
  --format agent` → `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` → `npm run typecheck` →
  `npm run build` → `npm run test:browser` → `composer lore` + `composer lore-code` with a ruling per hit.

---

## 1. What is already built (do not rebuild)

| Layer | Files | State |
|---|---|---|
| Inertia root + resolver | `resources/views/app.blade.php`, `resources/js/spa.ts` | Done |
| Shared props | `app/Http/Middleware/HandleInertiaRequests.php` (`app`, `flash`), `resources/js/types.ts` | Done |
| Shell | `resources/js/layouts/AppLayout.vue` (sidebar + mobile bottom nav + skip link) | Done |
| Ported pages | `pages/Dashboard.vue`, `pages/Catalog/Index.vue`, `pages/Review/Index.vue`, `pages/Preferences/Edit.vue` | Done |
| Ported components | `components/RarityChip.vue`, `components/review/CandidateForm.vue` | Done |
| Browser tests | `tests/browser/{catalog,preferences,review}.spec.ts`, `playwright.config.ts` | Done |

The established page pattern is `resources/js/pages/Catalog/Index.vue`: local `Paginator<T>` interface,
filters via `router.get(url, params, { preserveState, preserveScroll })`, forms via `useForm` +
`@submit.prevent`, `<Head title>` + `#title` slot. Copy it; do not invent a second pattern.

## 2. Conventions the rewrite must keep

1. **Page registration.** Add a file under `resources/js/pages/**`; `spa.ts` globs it. No registry edit.
2. **Props over HTML.** A ported screen's `tests/Feature` file asserts `->assertInertia(fn (Assert $page) =>
   $page->component('Catalog/Show')->has('trainee.slug', ...))`. Rendered text goes to Playwright.
3. **Layouts.** `AppLayout` is the global shell. A career-scoped layout (`CareerLayout`) and a setup
   wizard layout (`SetupLayout`) are added only when their first screen lands (Phase D), not upfront.
4. **Scenario binding.** A scenario panel component receives the resolved scenario array as a prop; it
   switches on `panels.*` flags and `widgets[]`, never on `scenario.label`.
5. **Provenance.** A single `components/ProvenanceBadge.vue` renders the four states and is the only place
   the glyphs live. It is built in the first slice that prints a calculated number (Phase D4).
6. **Forms.** Writes keep their Form Requests server-side (`AGENTS.md` §5, no inline `validate()`); the Vue
   form only posts. Existing write routes (`runs.turns.store`, `runs.deck.sync`, `runs.skills.sync`,
   `runs.races.store`, `runs.purchases.store`, `review.resolve`, `preferences.update`) are reused as-is.
7. **Navigation.** `AppLayout` `items[]` gains an entry when a screen lands; a not-yet-built destination
   stays `to: null` and renders as a named absence, not a dead link.

## 3. Knowledge grounding and the unknowns this plan must not paper over

Full provenance tables live in the two source docs' "Knowledge grounding" sections (added 2026-10-05).
The plan-level rule: **where the corpus is silent or stale, the screen renders absence, never a number.**

**Product direction corrections (2026-10-05).** A new strategy document reorients the app from "optimizer" to
"planning desk," introduces four Global scenarios (not three), six-node ancestry (not two parents), seven support
types (adds Pal/Group), Spark probabilities (not guarantees), derived Scenario Link, confidence layers, deterministic
rules engine first, observed/calculated/predicted/RNG distinction, mandatory versioning, and removes unsupported
concepts ("Trainer Abilities," "friendship radius"). See the "Product direction corrections" sections added to both
`design-2.0.md` and `screen-spec-2.0.md` for the full table of changes. Key implementation impacts:

- **SCREEN-002**: Four scenario cards; Grand Concert marked "PARTIALLY DOCUMENTED"; remove difficulty stars
- **SCREEN-005**: Add Career Plan object fields (purpose, race profile, skill priorities, risk tolerance)
- **SCREEN-006**: Six-node ancestry graph; Spark probability display (`~10% ★★★`); optimize entire configuration
- **SCREEN-007**: Seven support types; ownership flag (OWNED/RENTED); granular deck analysis categories
- **SCREEN-008**: "Career Contract" with warnings for low factor probability, missing aptitudes
- **SCREEN-009**: State machine with explicit before/expected/actual/result tracking
- **SCREEN-010**: Per-training yields render `N/A` (unsourced)
- **SCREEN-011**: Readiness bands (Excellent/Good/Borderline/Poor), not win probability %
- **SCREEN-012**: Event model with source/choices/outcomes; incomplete outcome warning
- **SCREEN-013**: Factor outlook with probability estimates, not guarantees
- **SCREEN-014–017**: Scenario-specific panels; Unity Cup gets Team Cockpit; Grand Concert limited
- **SCREEN-018**: Decision audit trail (before/action/expected/actual/result/confidence)
- **SCREEN-019–022**: Veteran Creation workflow; Factor Analysis; three-view Library (Veterans/Factors/Ancestry); "Find Parents for This Build" search
- **Data layer**: `GameRule` model with confidence/version/provenance; ruleset snapshot per career run

| Surface | Corpus state | What the screen renders |
|---|---|---|
| Scenario caps / panels | `config/scenarios.php`, verified 2026-09-27 | The config values, as-is |
| Sparks / Affinity / lineage | `UMAMUSUME_REFERENCE.md` §1.5 L604–611; factor-slot count clarified 2026-10-05 (L641) | Recorded factor names and star counts; **probabilities**, not guarantees; yield model: exactly 1 Blue + 1 Pink per run, at most 1 Green (requires 3★ parent), White sparks unbounded — variable yield, not a slot cap |
| Support-card effects | §1.4; `SUPPORT-CARDS.md` | Stated anchor values (`SupportCardEffects`); **seven types** including Pal/Group |
| Per-training stat yields, failure % | §1.1.1 ⚠️ STALE | `N/A` — excluded by the advisor spec |
| Race win probability, goal-race distance/surface | not sourced; `ADR-0016` blocker | `N/A` — no race planner number; use readiness bands |
| Inheritance outcome ("expected inheritance") | banned (`ADR-0020` §3) | Not computed; record-only; show **probability estimates** |
| Grand Concert mechanics | `docs/scenarios/07` stub; §2.2.4 ❌ UNVERIFIED; caps corroborated 2026-10-05 (two independent sources: 1600/1300/1300/1500/1300) | Baseline strip, every panel off; cap values now sourced but mechanics remain unverified |
| Shop item recommendation | rotation not modelled | Catalogue list only, no "recommended" flag |
| `app.ruleset` version string | currently `null` | `N/A` with a `title` |
| Official title "Twinkle Star Climax" | §7 conflict row 31 ❌ UNVERIFIED | Print "Trackblazer" (the export label) |
| Distance band conflicts (1400m) | Game8=Sprint vs export=Mile | Do not hard-code; allow configurable `distance_band` with `source`/`confidence` |

**Terminology:** "July 2026 rebalance" → **2026-07-01 Global rework**. "Support Decks" → **Support Cards**.

---

## 4. Slice sequence

Phases run in order. Each slice is independently shippable, green, and reviewable on its own. A slice is
the unit of work; a **slice plan** (this document's §5 for Phase A, and a fresh bite-sized plan per later
slice when it starts) is the unit of instruction — the multi-subsystem spec is split this way on purpose
(`writing-plans`: one plan per subsystem).

**Two screen inventories.** `screen-spec-2.0.md` numbers `SCREEN-001` … `SCREEN-024`; `design-2.0.md`
numbers a different set `SCR-001` … `SCR-027`. They disagree: design-2.0 adds a Skills Planner and a Grand
Concert panel and omits Inheritance Event, Career Timeline and Veteran Comparison. This plan builds the
**union**, uses `SCREEN-nnn` where the two agree, and marks the two design-2.0-only screens.

| Phase | Slice | Deliverable | Screens | Depends on |
|---|---|---|---|---|
| **A** | A1 | Port the catalog detail page | `SCR-CAT-002` | — |
| **A** | A2 | Port Skills (index + detail) | `SCR-SKL-001/002` | — |
| **A** | A3 | Port Support cards (index + detail) | `SCR-SUP-001/002` | A2 (shares `SkillRow`) |
| **A** | A4a | Port the runs list + create | `SCR-RUN-001/002` | — |
| **A** | A4b | Port the run detail + guided turns | `SCR-RUN-003` | A4a |
| **A** | A4c | Port the run import (form + preview) | `SCR-RUN-004/005` | A4a |
| **B** | B1 | Retire the Blade shell (`components/layout.blade.php`); delete dead views | — | A complete |
| **C** | C1 | `BuildTarget` domain: json column, payload, request, validation | `FR-F` | — |
| **C** | C2 | `TrainerAdvisor` engine + `config/advisor.php` | `FR-F` | C1 |
| **C** | C3 | Veteran-library schema + record/search actions | `FR-G` | — |
| **D** | D1 | Enrich the Dashboard (active career, recent veterans, goals) | SCREEN-001 | C1,C3 |
| **D** | D2 | Scenario Selection | SCREEN-002 | — |
| **D** | D3 | Trainee Selection + Profile | SCREEN-003/004 | D2 |
| **D** | D4 | Build Target screen + `ProvenanceBadge` | SCREEN-005 | C1 |
| **D** | D5 | Legacy Lab (record-only: pick + browse + compare) | SCREEN-006 | C3 |
| **D** | D6 | Support Deck Builder | SCREEN-007 | D3 |
| **D** | D7 | Run Preflight | SCREEN-008 | D4,D5,D6 |
| **D** | D8 | Career Cockpit (header, state panel, action grid, advisor rail) | SCREEN-009 | C2,D7 |
| **D** | D9 | Training Decision detail | SCREEN-010 | D8 |
| **D** | D10 | Race Decision | SCREEN-011 | D8 |
| **D** | D11 | Event Decision | SCREEN-012 | D8 |
| **D** | D12 | Inheritance Event (record-only) | SCREEN-013 | D8 |
| **D** | D13 | Skills Planner *(design-2.0 only)* | SCR-013 | D8 |
| **D** | D14 | Career Timeline | SCREEN-018 | D8 |
| **D** | D15 | Career Result | SCREEN-019 | D8 |
| **D** | D16 | Save Veteran + Veteran Library + Comparison | SCREEN-020/021/022 | C3,D15 |
| **D** | D17 | Database (trainees/supports/skills/races/scenarios) | SCREEN-023 | A1–A3 |
| **D** | D18 | Settings (extend the ported Preferences) | SCREEN-024 | — |
| **E** | E1 | Scenario panel shell bound to `config/scenarios.php` | SCREEN-014/015/016 | D8 |
| **E** | E2 | URA panel (goals + Happy Meek) | SCREEN-014 | E1 |
| **E** | E3 | Unity Cup panel (team rank + spirit) | SCREEN-015 | E1 |
| **E** | E4 | Trackblazer panel (grade points + shop + epithets) | SCREEN-016 | E1 |
| **E** | E5 | Scenario Race Planner (race facts only; **no** win probability) | SCREEN-017 | D8 |
| **E** | E6 | Grand Concert panel — baseline strip only *(design-2.0 only)* | SCR-017 | E1 |

Race prediction (the win-probability field on `SCREEN-011` and `SCREEN-017`), inheritance optimization
(`SCREEN-006`), and per-training stat yields (`SCREEN-010`) are **not built**: they are held on `ADR-0016`
and `ADR-0020` §3–4 and have no task anywhere in this plan. The screens that would show them still ship,
rendering the held value as `N/A` with a `title`.

---

## 5. Phase A — finish the ports

### 5.1 The port recipe (normative for A1–A4)

Every port slice applies these nine steps in order. They are the pattern proven by the Preferences, Review
and Catalog-index ports; a slice that deviates states why.

1. **Read** the controller method and its Blade view. List every value the view reads (each `$variable`,
   each component prop, each `config()`/`lang()` call).
2. **Write the failing props test.** Rewrite that screen's `tests/Feature/*Test.php` assertions from
   `assertSee`/`assertViewHas` to `assertInertia(fn (Assert $page) => $page->component('X')->has('prop')
   ->where('prop', $value))`. Keep every behavior; drop the rendered-text assertions (step 7 owns those).
3. **Run it — it must fail.** The controller still returns a Blade `view()`, so `component('X')` fails.
   A green step 3 means the test is not testing the change; fix it.
4. **Change the controller** method to `return Inertia::render('X', $props);`. Map every model and
   collection to explicit arrays — mirror `CatalogController::index()`'s `->through()`, never pass Eloquent
   models (a model serialises every loaded attribute, which is how a private column leaks into a page).
   Query logic, Form Requests, `PageSize::clamp` and cache keys stay byte-for-byte unchanged. Fix the
   method's return type (`Response`) and drop the now-unused `use Illuminate\View\View;` **only if no other
   method on the controller still returns a `View`** — `CatalogController` keeps it for `show()` until A1
   lands.
5. **Create the page** `resources/js/pages/X.vue`: `AppLayout` wrapper, `<Head title>`, `#title` slot, and
   the same content. Port each Blade component it used to a Vue SFC under `resources/js/components/` with
   the same tokens, `role`/`aria-*`, labels and `h-11`/`min-h-11` targets.
6. **Run the props test → pass.** Run `npm run build` → the page compiles. Run `npm run typecheck`.
7. **Add** `tests/browser/<screen>.spec.ts`: assert the rendered copy, the headings, the form labels, and
   the keyboard/focus path that the Blade regression test named (e.g. the review form's labels, the
   catalog's 44px controls). This is where the HTML-text assertions from step 2 went.
8. **Delete the dead Blade view** and its now-unused partial. Before deleting any shared `x-*` component,
   grep `resources/views` for `<x-name` — delete it only when the count reaches zero (see each slice for
   the count at the time of writing). If `FrontendComponentLibraryTest` names the deleted component,
   repoint it at the Vue equivalent; never delete the assertion.
9. **Run the hand-off sequence** (Global Constraints) and report the output.

### 5.2 Slice A1 — Catalog detail (`SCR-CAT-002`)

**Files:**
- Modify: `app/Http/Controllers/CatalogController.php:199-274` (`show()` → `Inertia::render('Catalog/Show', ...)`; return type `View` → `Response`)
- Create: `resources/js/pages/Catalog/Show.vue`
- Create: `resources/js/components/catalog/FormDetail.vue`, `resources/js/components/AptitudeGrid.vue`, `resources/js/components/FormTabs.vue`, `resources/js/components/SkillRow.vue`
- Test: `tests/Feature/CatalogDetailPageTest.php`, `tests/Feature/CatalogSkillListsTest.php` (migrate), `tests/browser/catalog-detail.spec.ts` (new)
- Delete (after green): `resources/views/catalog/show.blade.php`, `resources/views/catalog/partials/form-detail.blade.php`, and the now-unused `resources/views/components/form-tabs.blade.php` + `aptitude-grid.blade.php` (each has exactly one consumer, this view)

**Interfaces — the page props** (mapped from today's `view('catalog.show', [...])` args):

```php
Inertia::render('Catalog/Show', [
    'trainee' => [ 'id','slug','name','name_ja','release_status_label',
                   'aliases' => [['alias','language_label']],
                   'profile' => null|[...] ],
    'cards'   => [['id','title','rarity_label','rarity_stars','is_debut_form','unconfirmed',
                   'global_release_date','global_release_date_display','source_url','fetched_at']],
    'activeCardId' => int|null,
    'hiddenFormCount' => int,
    'runs'    => [['id','label','turn_count','scenario_label']],
    'skillLists' => [['label','absent','skills'=>[['id','name','sp_cost','is_unique','release_status_label','name_is_client']]]],
    'scenarioLabels' => string[],
    'showUnconfirmed' => bool,
]);
```

**Consumes:** `CatalogController::cardScope()` (reuse as-is), `Skill::whereIn('export_id', ...)` (unchanged),
`config('scenarios.scenarios')` labels.
**Produces:** `Catalog/Show.vue` and the four ported components; A3 reuses `SkillRow.vue`.

- [x] Apply the port recipe (5.1) steps 1–9.
- [ ] Assert in `catalog-detail.spec.ts`: the trainee's Japanese name renders; the provenance URL renders;
      the costume-form tabs keyboard-navigate and the active tab carries `aria-current`; a japan-only entry
      prints "Not yet released on Global" and no `>JapanOnly<` machine token (carries `EnumLabelTest`'s
      detail-page case into the browser).
- [x] Verify the `?form=` deep link selects the named form and an out-of-scope id falls back (props
      assertion in `CatalogDetailPageTest`).

### 5.3 Slice A2 — Skills (`SCR-SKL-001/002`)

**Files:** Modify `SkillController::index()` and `::show()`; create `resources/js/pages/Skills/Index.vue`,
`resources/js/pages/Skills/Show.vue`; test `SkillSearchScreenTest`, `SkillDetailTest` (migrate) +
`tests/browser/skills.spec.ts`.
**Consumes:** `PageSize::clamp` (unchanged), the `type` facet contract (`ADR-0018`: an unknown `type`
refuses and redirects — keep that server behavior, assert the redirect in the props test).
**Produces:** `Skills/Index.vue`, `Skills/Show.vue`; `SkillRow.vue` from A1 is reused (its second Blade
consumer, `support-cards/show`, is still live — so `resources/views/components/skill-row.blade.php` stays
until A3).

- [x] Apply the port recipe steps 1–9.
- [x] Browser: assert the search field and the type select are 44px (`h-11`), the empty state reads
      "No skills match", and the detail page's skill band headings are present. **Deviation:** the shipped
      copy is "Nothing matches {ask}." (D-65 names the ask rather than repeating "No skills match"), so
      `tests/browser/skills.spec.ts` asserts that string. The run caught a real defect: the Filter button
      carried `px-3 py-1.5` with no height and measured 32px against the 44px contract, a miss the Blade
      view also had; `Skills/Index.vue` now carries `h-11` like `Catalog/Index.vue`.

### 5.4 Slice A3 — Support cards (`SCR-SUP-001/002`)

**Files:** Modify `SupportCardController::index()` and `::show()`; create
`resources/js/pages/SupportCards/Index.vue`, `SupportCards/Show.vue`; migrate `SupportCardPageTest`,
`FilterContractTest` (the `pageSize` case already repointed here); new `tests/browser/support-cards.spec.ts`.
**Note:** `SupportCardController` uses the framework paginator (`->paginate()`), not the hand-built
`LengthAwarePaginator` the catalog uses — so the Vue paginator binding is the same `links` loop, but the
props test asserts `cards.perPage()` rather than a manual `pageSize` prop.
**Delete after green:** `resources/views/support-cards/index.blade.php`, `show.blade.php`, and
`resources/views/components/skill-row.blade.php` (its last consumer).

- [ ] Apply the port recipe steps 1–9.
- [ ] Browser: assert the card grid renders a rarity chip with an accessible name, and the detail page's
      effect list renders stated anchor values with no invented interpolation.

### 5.5 Slice A4 — Training runs (`SCR-RUN-001`–`005`)

The largest port: five screens, one controller, the guided turn rail, and the run-scoped write forms. Split
into three shippable sub-slices.

- **A4a — list + create** (`Runs/Index.vue`, `Runs/Create.vue`). Migrate `RunsIndexTest`,
  `RunCreateSurfaceTest`. Browser: `runs.spec.ts`.
- **A4b — detail + guided turns** (`Runs/Show.vue`; port `x-run-header`, `x-stat-band`, `x-energy-gauge`,
  `x-mood-pill`, `x-resource-strip`, `x-deck-panel`, `x-race-calendar`, `x-race-panel`, `x-guided-step`,
  `x-capsule-header`, `x-app-button`, `x-grade-badge`). Migrate `RunViewFrameTest`, `RunViewNoScriptTest`,
  `RunViewTargetSizeTest`, `RunViewErrorEnvelopeTest`, `RunDeckTest`, `RunSkillPickerTest`,
  `RunStatusControlTest`, `RunSaveConfirmationTest`, `RunGoalsPanelTest`, `GoalPanelsOnRunDetailTest`,
  `GuidedTurn*`, `GuidedStep*`, `GuidedFirstTurnTest`, `StatBandTest`, `MoodPillTest`, `RaceCalendarTest`,
  `ScenarioPanelUiTest`. Browser: `run-detail.spec.ts` asserting the guided rail's keyboard path and the
  44px control sweep.
- **A4c — import** (`Runs/Import.vue`; the form and the preview/confirm step). Migrate the import cases;
  the preview endpoint stays JSON (`runs.import.preview`).

- [ ] Each sub-slice applies the port recipe steps 1–9 and lands green before the next starts.
- [ ] The run-scoped writes (`runs.turns.store/update/destroy`, `runs.deck.sync`, `runs.skills.sync`,
      `runs.races.store`, `runs.purchases.store`) keep their Form Requests; the Vue forms only post and
      handle `useForm` errors. Assert one write per sub-slice still round-trips through its request.
- [ ] Do not delete `x-guided-step` / `x-stat-band` / etc. until their consumer count reaches zero (grep in
      step 8); the scenario panels in Phase E may still want them as reference.

---

## 6. Phase B — retire the Blade shell

**B1 — one slice, after A1–A4 are green.**

The shell (`resources/views/components/layout.blade.php`, 11 consumers today) loses its last consumer when
A4 lands. Delete it, delete `resources/views/components/` entries whose `<x-name` count is now zero, and
remove the Blade-era TS entries (`resources/js/guided-flow.ts`, `resources/js/trainee-combobox.ts`, and
`resources/js/app.ts` if nothing under `resources/views` still loads it — `app.blade.php` loads `spa.ts`).

**Keep:** `resources/views/app.blade.php` (the Inertia root), `resources/views/errors/{404,419,500}.blade.php`
(rendered by Laravel's error handler and required to work with no Inertia page and no database —
`SCREEN_SPEC.md` `SCR-SYS-001/003/004`), and `resources/views/vendor/pagination/tailwind.blade.php`.

**Tests to repoint:** `DesignTokensTest` and `ErrorPageViewsTest` currently grep rendered Blade HTML. The
error pages stay Blade, so `ErrorPageViewsTest` is unchanged. `DesignTokensTest` moves its four pages from
Blade URLs to the ported Inertia URLs and asserts the same two things (no `dark:` utility, no skeleton
palette class) against the served HTML.

---

## 7. Phase C — domain-first, frontend-agnostic

Per `ADR-0020` §3, this work is built and tested against the current stack before the screens that show it.
Each slice travels with its migration, its `ARCHITECTURE-ESSENTIALS.md` digest update, its PRD citation and
a working `down()` (`AGENTS.md` §11).

**C1 — `BuildTarget` (`FR-F`).** One nullable json column `build_target` on `training_runs` (not a new
table), read through `App\Models\Advisor\BuildTargetPayload` (mirrors `LegacySelectionPayload`, `ADR-0010`).
Fields: `purpose` (StoryClear/ChampionsMeeting/ParentFarming/SkillFarming), `distance`, `surface`, `style`,
`targets` (five ints, validated by `ScenarioCaps::forRun`), `skill_priorities` (ordered names). Written by
`StoreBuildTargetRequest`. Tests: payload round-trip, the validator's use of `ScenarioCaps`, and an absent
target naming the absence rather than defaulting.

**C2 — `TrainerAdvisor` (`FR-F`, extends `ADR-0001`).** `app/Services/Advisor/TrainerAdvisor.php` plus
`config/advisor.php` holding the constants verbatim from `PROCESS-PLANS.md` §`## trainer-advisor.md` §3:
`rest_recovery` = 30, `session_cost` = [17, 28] (a range, rendered `E−28 … E−17`), `wit_cost` = 0,
`advisory_threshold` = 50. It ranks six options (Speed, Stamina, Power, Guts, Wit, Rest) against the
entered target; when Energy is absent it returns no band and no ranking and the surface names the absence.
Two band states only — `AtOrAboveAdvisory` / `BelowAdvisory`. Every suggestion carries a row-level derivable
reason line; a suggestion with no derivable reason does not ship. `Wit` renders `RiskNotMeasured`. **No
numeric confidence, no score, no per-training yield** — those stay out (`ADR-0001` §3, `ADR-0020` §2).

**C3 — Veteran library (`FR-G`, record-only, under `ADR-0010`).** A `veterans` table: a completed
`training_runs` row referenced by id, plus `tags` (json), `notes`, and timestamps. Actions to record, list
(with filters: spark type, distance, surface, style, scenario, rating), and show. **No computation** — it
stores and searches what the Trainer entered; it never derives a Spark, an affinity payout, or an offspring
(`ADR-0020` §3).

---

## 8. Phase D — the 2.0 screens

Each slice adds its page(s), the layout it needs (`SetupLayout` for D2–D7, `CareerLayout` for D8–D13), the
nav entry, and a browser spec. Slice-level acceptance is stated; each gets its own bite-sized plan when it
starts.

| Slice | Adds | Acceptance |
|---|---|---|
| D1 | Enrich `Dashboard.vue`: active career, recent veterans, legacy-goal gaps, data status indicator | Every panel is real data or a named absence; no invented count; subtle "GLOBAL DATA ● Current" badge |
| D2 | `SetupLayout`, `Career/ScenarioSelect.vue` | Cards render from `config/scenarios.php`; Grand Concert shows the baseline strip (`documented => false`); no scenario name branched in a component |
| D3 | `Career/TraineeSelect.vue`, `Career/TraineeProfile.vue` | Reuses the catalog queries; aptitude badges carry text, not colour alone |
| D4 | `Career/BuildTarget.vue`, `components/ProvenanceBadge.vue` | **Career Plan object**: purpose, race profile, stat/aptitude/style/skill targets, risk tolerance. Render as "Your target" not "The correct target". Target stats clamp via `ScenarioCaps`; skill priorities ordered; badge is only place four glyphs live |
| D5 | `Legacy/Index.vue`, `Legacy/Builder.vue`, `Legacy/Compare.vue` | Record-only: pick, browse, compare; **no** optimizer, no "expected inheritance" (banner on the screen) |
| D6 | `Support/Builder.vue` (over `runs.deck.sync`, `x-deck-editor`) | **Six slots**; ownership flag (OWNED/RENTED), not structural borrowed assumption; **seven support types** (Speed/Stamina/Power/Guts/Wit/**Pal**/**Group**); Scenario Link derived from scenario+character; granular deck analysis (training power, early run, race bonus, safety, events, skills) |
| D7 | `Career/Preflight.vue` | Composes D4–D6; warnings are the derivable ones only |
| D8 | `CareerLayout`, `Career/Cockpit.vue`, `career/CareerHeader.vue`, `career/CareerStatePanel.vue`, `career/ActionGrid.vue`, `career/RecommendationCard.vue`, `career/AdvisorRail.vue` | Three-column desktop / one-column mobile; the advisor rail reads C2 and prints band + reason lines, **not** a score or a numeric confidence; the recommendation never auto-executes |
| D9 | `career/TrainingCard.vue`, `Career/TrainingDetail.vue` | Per-option breakdown shows only sourced terms; the unsourced yield renders `N/A` |
| D10 | `career/RaceCard.vue`, `Career/RaceDecision.vue` | Race facts from `RaceCatalogSlot` where present; win probability renders `N/A` with a `title` naming the `ADR-0016` blocker |
| D11 | `career/EventCard.vue`, `Career/EventDecision.vue` | Records the choice over `TurnEvent`; the advisor's override is always available |
| D12 | `Career/InheritanceEvent.vue` | Records observed inspiration outcomes over the existing event recording; predicted vs observed are kept apart (`ADR-0020` §3) |
| D13 | `Career/SkillsPlanner.vue` *(design-2.0 only)* | States Required/Available/Learned/Inherited; warns when SP cannot cover required; hint level discount calculation (base cost × (1 - discount%)); skill race-fit assessment (distance/surface/style/course/weather/ground/phase); no skill recommendation (OQ-5 stays open, `ADR-0020` §2) |
| D14 | `career/CareerTimeline.vue`, `Career/Timeline.vue` | Renders `TurnEntry` / `TurnEvent` history; before/after state; no silent rewrite of a past decision |
| D15 | `Career/Result.vue` | Reads the finished run; no derived "build quality" score |
| D16 | `Career/SaveVeteran.vue`, `Veterans/Index.vue`, `Veterans/Show.vue`, `Veterans/Compare.vue` | Over C3; tags are suggestions, notes are free text |
| D17 | `Database/{Trainees,Supports,Skills,Races,Scenarios}.vue` | Reuses the ported catalog/skills/support screens; the Race database names the 0-row offline state |
| D18 | Extend `Preferences/Edit.vue` → Settings categories (General / Recommendation / Data / Game version) | Every new preference is a real column; the ruleset row renders `N/A` until a ruleset string is sourced |

**Recommendation contract (D8).** The brief's §48 shape carries a `score` and a `confidence` string. This
plan **drops both**: numeric confidence and score are unsourced (`ADR-0001` §3). The component takes
`{ action, band: 'AtOrAboveAdvisory'|'BelowAdvisory', reasons: string[], alternative: string|null, risk: string|null }`.

**Scenario panel contract (E1).** The brief's §49 shape is adopted as-is —
`{ name, version, status, resources, objectives, actions, alerts, recommendations, finale }` — with `version`
and any `recommendations` that imply prediction rendered as named absences where the corpus is silent.

---

## 9. Phase E — scenario panels

**E1 — the shell.** `components/scenario/ScenarioPanel.vue` resolves the scenario array from
`config/scenarios.php` (passed as a prop by the Cockpit) and switches on `panels.*` / `widgets[]`. It never
reads `scenario.label`. A fifth scenario is one config entry and no component edit (G-33, D-240). Test: the
same run header renders in all four Global scenarios (G-33's own assertion).

**E2 URA** — career goals, Happy Meek module. **E3 Unity Cup** — team rank ladder, spirit-burst roster
(port `x-team-rank-gauge`, `x-team-race-panel`, `x-spirit-burst-roster`). **E4 Trackblazer** — grade-point
meter, shop catalogue, epithet checklist (port `x-grade-point-meter`, `x-shop-panel`, `x-epithet-checklist`);
the shop renders the catalogue list and **no** "recommended purchase" (the rotation is not modelled).
**E5 Scenario Race Planner** — upcoming/mandatory/optional/rival races and reward comparison from
`RaceCatalogSlot` where present; the win-probability field renders `N/A` with a `title` naming the
`ADR-0016` blocker. **E6 Grand Concert** — the baseline strip only: every panel off, per
`our_grand_concert.documented => false` (D-241, G-41). No songs, lessons, or Performance Tokens exist in
the corpus; the panel says so.

---

## 10. Verification and definition of done

Per slice (the hand-off sequence in Global Constraints), plus:

- [ ] The slice's props test asserts behavior; the browser spec asserts rendered copy and the a11y path.
- [ ] WCAG 2.2 AA holds on the screen: axe A + AA clean, plus a keyboard pass, 200% zoom, 320px reflow,
      and a reduced-motion check (§12).
- [ ] The screen's named UX laws hold, and no state is shown through colour alone (§13).
- [ ] `DesignTokensTest`'s two gates hold on the ported page (no `dark:`, no skeleton class).
- [ ] G-33 holds: no component branches on a scenario name.
- [ ] `composer lore` + `composer lore-code` ran, with a one-line ruling per hit.
- [ ] The `SCREEN_SPEC.md` state table for the screen agrees with the view (empty / loading / error); a
      screen-level change updates `SCREEN_SPEC.md`.
- [ ] The diff was read; no unrelated file changed; no unrun gate is implied to have passed.

Program-level (a phase is done when): the full suite is green, PHPStan L6 is clean, Pint is clean,
`npm run typecheck` and `npm run build` are clean, `npm run test:browser` is green, and the phase's slice
plans are filed.

---

## 11. Risks and open questions

1. **TypeScript 7 blocks `.vue` type-checking.** `vue-tsc` cannot run (it needs `typescript/lib/tsc`,
   removed in TS 7). `.vue` SFCs are build-checked only. Accepted, recorded; revisit when TS ships the path.
2. **`SCREEN_SPEC.md` is the authority for today's screens.** Every port updates it; a port that leaves it
   describing a deleted Blade view is a documentation regression.
3. **The `x-*` component library is shared.** Deleting a Blade component before its last consumer ports
   breaks a live view. The recipe's grep gate is the guard.
4. **`app.ruleset` is `null`.** The versioning UI (design-2.0 §48) cannot render a ruleset until one is
   sourced. Until then it renders `N/A`.
5. **Phase D screens have no current `SCREEN_SPEC.md` rows.** D2–D16 need new `SCR-*` entries (a new area,
   e.g. `SCR-CAR-*` for career) before or with their first slice; `SCREEN_SPEC.md` §2 is the place.
6. **Held computation stays held.** Race prediction, inheritance optimization, per-training yields, and the
   Grand Concert mechanics have no task here. A later slice that closes `ADR-0016`'s data blocker is what
   unlocks them, not a UI decision.

---

## 12. Accessibility conformance: WCAG 2.2 level AA

**Target: WCAG 2.2 level AA (all A and AA criteria) on every shipped screen**, the four ported Inertia
pages and the seven live Blade screens alike, verified by an axe pass in the browser specs plus the
manual checks in §12.5. This is a **regression bar**: no slice may reduce conformance, and each slice's
browser spec asserts the criteria its screen carries. The 2.2 target is chosen over 2.1 because 2.2 is
the current Recommendation and its additions (focus visibility, target size, redundant entry) are the
three the 0.1.0 screens most often miss.

### 12.1 What 2.2 adds over 2.1, and this app's disposition

| Criterion | Level | Disposition in Trainer Desk 2.0 |
|---|---|---|
| 2.4.11 Focus Not Obscured (Minimum) | AA | **Binding.** The sticky mobile bottom nav (`fixed inset-x-0 bottom-0`) and any sticky header must not cover the focused control. Fix: `scroll-padding-bottom` on the scroll container equal to the nav height, plus `scroll-margin` on focusables. Test: tab to the last control on a long page and assert it is not under the nav. |
| 2.5.7 Dragging Movements | AA | **Binding.** No drag-only interaction ships. Any reorder (deck, skill priority) offers a keyboard/button alternative, not drag. |
| 2.5.8 Target Size (Minimum) | AA | **Binding.** 24x24 CSS px minimum, satisfied by the existing 44px (`h-11`/`min-h-11`) targets. A smaller target is allowed only for inline text links or the equivalent-spacing exception, and the slice names it. |
| 3.2.6 Consistent Help | A | **Binding.** The help affordance (the `title` tooltips and the Settings link) keeps the same relative order on every screen. |
| 3.3.7 Redundant Entry | A | **Binding.** The career setup wizard (D2 to D7) carries entered values forward; a step never re-asks for a value an earlier step already holds. |
| 3.3.8 Accessible Authentication (Minimum) | AA | **Not applicable.** No auth surface exists (`AGENTS.md` §1; PRD NFR-1). Recorded as N/A, not skipped silently. |
| 2.4.12 Focus Not Obscured (Enhanced) | AAA | Out of target. Met incidentally where the 2.4.11 fix fully reveals the control. |
| 2.4.13 Focus Appearance | AAA | Out of target. The global `:focus-visible` (2px `--color-ring`, 2px offset) already exceeds the AA focus-visible requirement. |
| 3.3.9 Accessible Authentication (Enhanced) | AAA | Out of target; N/A as above. |
| 4.1.1 Parsing | removed in 2.2 | No action; valid HTML was always the requirement. |

### 12.2 The 2.1 criteria the 0.1.0 screens must already meet (re-checked, not assumed)

1.4.11 Non-text Contrast (AA): chips, borders, meter fills and timeline strokes reach 3:1 against their
background. 1.4.12 Text Spacing (AA): no fixed-height box clips text at 1.5 line height and 0.12em
tracking. 1.4.13 Content on Hover or Focus (AA): every tooltip is dismissable (Escape), hoverable, and
persistent until dismissed. 2.5.1 Pointer Gestures, 2.5.2 Pointer Cancellation, 2.5.3 Label in Name
(the accessible name contains the visible label), 2.5.4 Motion Actuation (A): no gesture-only or
motion-only control.

### 12.3 The 2.0 core AA set, restated as per-slice gates

1.4.3 Contrast (4.5:1 text, 3:1 large), 1.4.4 Resize Text (200% with no loss), 1.4.5 Images of Text,
1.4.10 Reflow (320px, no two-dimensional scroll), 1.4.11 to 1.4.13, 2.4.5 Multiple Ways, 2.4.6 Headings
and Labels, 2.4.7 Focus Visible, 2.5.8, 3.1.2 Language of Parts (a Japanese name carries `lang="ja"`),
3.2.3 Consistent Navigation, 3.2.4 Consistent Identification, 3.3.3 Error Suggestion, 3.3.4 Error
Prevention, 3.3.7.

### 12.4 Known 0.1.0 gaps this plan closes

- **No declared conformance target.** `DESIGN.md` §8 has a MOTION dial but no accessibility target line.
  Phase A0 adds it.
- **No `prefers-reduced-motion` block** in `resources/css/app.css`. The MOTION dial is 1, so the block is
  small: it neutralizes the few transitions and any future animation.
- **Loading and error states missing on nearly every page** (antislop R-27; ADR-0007). Each screen gets
  them in A0.
- **2.4.11 unhandled** against the sticky mobile nav. Fixed with scroll padding in A0a.
- **No `lang="ja"`** on Japanese names in the Blade views. Added where printed.

### 12.5 Verification

axe-core run through Playwright at 0 violations for A + AA on each screen. Adding `@axe-core/playwright`
needs owner approval (`AGENTS.md` §5); if it is already a dev dependency, reuse it, otherwise inject the
pinned `axe-core` in the spec. Manual pass per screen: keyboard-only, 200% zoom, 320px reflow, reduced
motion. The browser spec for each screen asserts the criteria it names.

---

## 13. Laws of UX applied

The 30 laws at lawsofux.com, mapped to concrete decisions. A slice cites the rows that govern its screen;
a screen that violates Hick's, Miller's, Jakob's, or Fitts's law, or that shows state through colour
alone, is not done.

| Law | Where it binds | Concrete decision |
|---|---|---|
| Aesthetic-Usability Effect | every screen | Clean tokens make the tool feel reliable, but polish never substitutes for a sourced number. |
| Choice Overload | SCREEN-002, Cockpit | Four scenario cards, not a matrix; the action grid stays short. |
| Chunking | Cockpit | Three columns; the stat panel groups five stats and four meta values. |
| Cognitive Load | all data screens | Progressive disclosure (design-2.0 §35): the card shows three facts, expand for modifiers. |
| Doherty Threshold | catalog reads | Local-first, `Cache::remember`; skeletons only for a user-initiated async action. |
| Fitts's Law | shell, Cockpit | 44px targets, the primary action nearest the decision, mobile bottom nav. |
| Flow | Cockpit | One screen carries the run; no modal stacks over a decision. |
| Goal-Gradient Effect | stat bands, targets | Progress bars show distance to target, not just the current value. |
| Hick's Law | advisor rail | One recommended action plus one alternative, not six equal buttons. |
| Jakob's Law | shell, forms | Conventional sidebar, table, and form patterns. |
| Law of Common Region | cards | A card bounds one concept; the scenario panel is one region. |
| Law of Proximity | every figure | A provenance badge sits next to the number it labels. |
| Law of Pragnanz | cards | One visual idea per card; no competing treatments. |
| Law of Similarity | spark chips | Same meaning, same shape; all spark chips share a component. |
| Law of Uniform Connectedness | ancestry, timeline | The six-node graph connects nodes with lines; the timeline is one rail. |
| Mental Model | whole app | A planning desk, not a bot; record-only where computation is banned. |
| Miller's Law | nav, panels | Nav stays at or under eight items; the stat panel stays at five plus four. |
| Occam's Razor | scenario panels | Grand Concert shows a baseline strip, not an invented panel. |
| Paradox of the Active User | Dashboard | No onboarding wall; the Dashboard is usable on first load. |
| Pareto Principle | advisor | Surface the few facts that drive the decision: target deficit, energy, deadline. |
| Parkinson's Law | turn counter | A bounded run with a deadline, not an open-ended dashboard. |
| Peak-End Rule | SCREEN-019 | Career Result is a deliberate, calm summary. |
| Postel's Law | manual correction | Accept loose input on correction; emit strict, provenance-labeled output. |
| Selective Attention | Cockpit | The recommendation card is the only accented element. |
| Serial Position Effect | action grid | Primary action first, summary last. |
| Tesler's Law | advisor | The engine absorbs the complexity; the player sees a band and a reason line. |
| Von Restorff Effect | action grid | One `RECOMMENDED` marker, not an accent on every card. |
| Working Memory | setup wizard | Never ask the player to recall a value from a prior screen; carry it (3.3.7). |
| Zeigarnik Effect | targets, timeline | Incomplete states are shown, not hidden, so progress stays visible. |

**Deliberately traded.** Occam's Razor yields to completeness in the Database area (a reference table is
dense by nature); density is confined there and never leaks into the Cockpit. Aesthetic-Usability yields
to no-false-precision: polish never adds a number the corpus lacks.

---

## 14. Phase A0 — 0.1.0 remediation

The 0.1.0 frontend ships in tandem with the rewrite, so it must meet the same WCAG 2.2 AA bar and the
same UX-law rubric, or the two halves of the app disagree. A0 is the smallest set of changes that brings
the shipped Blade and Vue surfaces to the bar without a rewrite. It runs before or alongside Phase A.

**A0a — shell and tokens** (`resources/js/layouts/AppLayout.vue`, `resources/views/components/layout.blade.php`,
`resources/css/app.css`): add the `prefers-reduced-motion` block; add `scroll-padding-bottom` for the
mobile nav (2.4.11); confirm the skip link and `<main id="main">` in both shells; one `banner`, one
labelled `nav`, one `main`, one `contentinfo` per page; nav targets at or above 44px with `aria-current`
on the active item.

**A0b — ported Vue pages** (`Dashboard.vue`, `Catalog/Index.vue`, `Review/Index.vue`,
`Preferences/Edit.vue`): add loading and error states (R-27, ADR-0007); keep the existing empty states;
add `lang="ja"` to Japanese names.

**A0c — live Blade screens** (`catalog/show`, `runs/{index,create,import,show}`,
`skills/{index,show}`, `support-cards/{index,show}`): loading and error states where a user-initiated
async action exists; a 44px control sweep; `lang="ja"` on Japanese names; no skipped heading levels;
validation messages tied to their inputs with `aria-describedby`.

**A0d — error pages** (`errors/{404,419,500}`): heading structure, a focus target, and a skip-to-main path.

**A0e — `DESIGN.md`**: declare the WCAG 2.2 AA target and the UX-law rubric in §8, and note the
reduced-motion addition (the MOTION dial stays 1; the block is the escape hatch for the OS preference).

**A0 acceptance:** axe A + AA clean per screen; a keyboard-only pass, 200% zoom, 320px reflow, and
reduced-motion check; each screen's browser spec asserts the criteria it names.

---

## 15. Change log

| Date | Change | Reason |
|---|---|---|
| 2026-10-05 | Filed. Derived from `design-2.0.md` + `screen-spec-2.0.md`; bound to real routes, controllers, models, `config/scenarios.php` and gates. | Owner asked for a comprehensive frontend development plan from the two 2.0 design docs, grounded in the repository's knowledge corpus. |
| 2026-10-05 | Added §12 (WCAG 2.2 AA conformance), §13 (Laws of UX rubric), §14 (Phase A0 remediation of the 0.1.0 screens); strengthened the Global Constraints accessibility bullet and the §10 definition of done. | Owner required the 2.0 UI and the in-tandem 0.1.0 UI to meet WCAG 2.2 AA and the UX laws, not just carry the old Blade behavior forward. |

