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

Inventory measured against the working tree on 2026-10-05, not remembered: 13 page components, 25 shared
components, 9 browser specs, 15 Blade files still in `resources/views/`.

| Layer                     | Files                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  | State            |
| ------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ---------------- |
| Inertia root + resolver   | `resources/views/app.blade.php`, `resources/js/spa.ts`                                                                                                                                                                                                                                                                                                                                                                                                                                                 | Done             |
| Shared props              | `app/Http/Middleware/HandleInertiaRequests.php` (`app`, `flash`, `errors`), `resources/js/types.ts`                                                                                                                                                                                                                                                                                                                                                                                                    | Done             |
| Shell                     | `resources/js/layouts/AppLayout.vue` (sidebar + mobile bottom nav + skip link)                                                                                                                                                                                                                                                                                                                                                                                                                         | Done             |
| Ported pages              | `pages/Dashboard.vue`, `pages/Catalog/{Index,Show}.vue`, `pages/Review/Index.vue`, `pages/Preferences/Edit.vue`, `pages/Skills/{Index,Show}.vue`, `pages/SupportCards/{Index,Show}.vue`, `pages/Runs/{Index,Create,Show,Import}.vue`                                                                                                                                                                                                                                                                   | Done (13)        |
| Ported components         | `components/RarityChip.vue`, `components/SkillRow.vue`, `components/AptitudeGrid.vue`, `components/TraineeCombobox.vue`, `components/ArtworkSlot.vue`, `components/{AppButton,CapsuleHeader,MoodPill,GradeBadge,EnergyGauge,StatBand,ResourceStrip,GradePointMeter,RaceCalendar,DeckPanel,RacePanel,ShopPanel,TeamRankGauge,SpiritBurstRoster,TeamRacePanel,EpithetChecklist,RaceFatigueChip,GuidedStep}.vue`, `components/catalog/{FormDetail,FormTabs}.vue`, `components/review/CandidateForm.vue`   | Done (25)        |
| Browser tests             | `tests/browser/{catalog,catalog-detail,preferences,review,skills,support-cards,runs,run-detail,run-import}.spec.ts`, `playwright.config.ts`                                                                                                                                                                                                                                                                                                                                                            | Done (9 specs)   |
| Still on Blade            | `resources/views/errors/{404,419,500}.blade.php`, `resources/views/components/{layout,capsule-header,energy-gauge}.blade.php`, and the zero-consumer run components (`app-button`, `deck-editor`, `grade-badge`, `grade-point-meter`, `guided-step`, `race-calendar`, `resource-strip`, `run-header`)                                                                                                                                                                                                  | B1 pending       |

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

| Surface                                            | Corpus state                                                                                                                                                                                                             | What the screen renders                                                                                                                                                                                                 |
| -------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Scenario caps / panels                             | `config/scenarios.php`, verified 2026-09-27                                                                                                                                                                              | The config values, as-is                                                                                                                                                                                                |
| Sparks / Affinity / lineage                        | `UMAMUSUME_REFERENCE.md` §1.5 L604–611; factor-slot count clarified 2026-10-05 (L641)                                                                                                                                    | Recorded factor names and star counts; **probabilities**, not guarantees; yield model: exactly 1 Blue + 1 Pink per run, at most 1 Green (requires 3★ parent), White sparks unbounded — variable yield, not a slot cap   |
| Support-card effects                               | §1.4; `SUPPORT-CARDS.md`                                                                                                                                                                                                 | Stated anchor values (`SupportCardEffects`); **seven types** including Pal/Group                                                                                                                                        |
| Per-training stat yields, failure %                | §1.1.1 ⚠️ STALE as a model; the client prints both on the tile (the Rice Shower run report, `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1.7: +18 Speed / +8 Power / +6 SP, Failure 39% at Speed Lv4)                   | Record-as-observed with Confirmed provenance when the Trainer enters them; `N/A` when not; the advisor still derives neither (C2 unchanged)                                                                             |
| Race win probability, goal-race distance/surface   | not sourced; `ADR-0016` blocker                                                                                                                                                                                          | `N/A` — no race planner number; use readiness bands                                                                                                                                                                     |
| Inheritance outcome ("expected inheritance")       | banned (`ADR-0020` §3)                                                                                                                                                                                                   | Not computed; record-only; show **probability estimates**                                                                                                                                                               |
| Grand Concert mechanics                            | `docs/scenarios/07` stub; §2.2.4 ❌ UNVERIFIED; caps corroborated 2026-10-05 (two independent sources: 1600/1300/1300/1500/1300)                                                                                         | Baseline strip, every panel off; cap values now sourced but mechanics remain unverified                                                                                                                                 |
| Shop item recommendation                           | rotation not modelled                                                                                                                                                                                                    | Catalogue list only, no "recommended" flag                                                                                                                                                                              |
| `app.ruleset` version string                       | currently `null`                                                                                                                                                                                                         | `N/A` with a `title`                                                                                                                                                                                                    |
| Official title "Twinkle Star Climax"               | §7 conflict row 31 ❌ UNVERIFIED                                                                                                                                                                                         | Print "Trackblazer" (the export label)                                                                                                                                                                                  |
| Distance band conflicts (1400m)                    | Game8=Sprint vs export=Mile                                                                                                                                                                                              | Do not hard-code; allow configurable `distance_band` with `source`/`confidence`                                                                                                                                         |
| Energy, exact value                                | The client prints no number: bar fill, its colour change and the low-energy warning are the whole readout, and the failure % does not back-solve it (run report §1.7, §8.6)                                              | A band label plus the warning text, value Unknown; C2 treats an absent exact Energy as absent (its refusal state)                                                                                                       |
| Turn counter vs calendar                           | The client prints "5 turn(s) until the Unity Cup" at Senior Early Oct where calendar arithmetic says seven half-months; unresolved (run report §8.7)                                                                     | The client's counter wins; calendar arithmetic renders as a discrepancy note, never a correction                                                                                                                        |
| Unity Cup burst reward tiers                       | Bands 4–6 white Lv1 / 7–9 white Lv3 / 10–12 gold Lv1 / 13+ gold Lv3 (`02-unity-cup.md:201`; gametora 2026-07-20); whether Extreme Bursts count toward the threshold is a live publisher disagreement (run report §8.2)   | The band table with a confidence note; no settled tier verdict until the client settles it                                                                                                                              |
| Team Rank Global bonus                             | "All Attributes + 30" beside the S emblem, client tooltip; its binding to the rank rather than the league standing is inferred from screen position (run report §7.5)                                                    | Estimated, not Confirmed                                                                                                                                                                                                |
| Hint discount ladder                               | 10/20/30/35/40% at Hint Lv1–`Lv Max`, read off the client (run report §2.1; `SKILLS-MECHANICS.md` §2.4)                                                                                                                  | Displayed cost = base × (1 − discount), floored; sourced, so D13's discount calculation ships as Confirmed                                                                                                              |

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

| Phase   | Slice   | Deliverable                                                                 | Screens              | Depends on               |
| ------- | ------- | --------------------------------------------------------------------------- | -------------------- | ------------------------ |
| **A**   | A1      | Port the catalog detail page                                                | `SCR-CAT-002`        | —                        |
| **A**   | A2      | Port Skills (index + detail)                                                | `SCR-SKL-001/002`    | —                        |
| **A**   | A3      | Port Support cards (index + detail)                                         | `SCR-SUP-001/002`    | A2 (shares `SkillRow`)   |
| **A**   | A4a     | Port the runs list + create                                                 | `SCR-RUN-001/002`    | —                        |
| **A**   | A4b     | Port the run detail + guided turns                                          | `SCR-RUN-003`        | A4a                      |
| **A**   | A4c     | Port the run import (form + preview)                                        | `SCR-RUN-004/005`    | A4a                      |
| **B**   | B1      | Retire the Blade shell (`components/layout.blade.php`); delete dead views   | —                    | A complete               |
| **C**   | C1      | `BuildTarget` domain: json column, payload, request, validation             | `FR-F`               | —                        |
| **C**   | C2      | `TrainerAdvisor` engine + `config/advisor.php`                              | `FR-F`               | C1                       |
| **C**   | C3      | Veteran-library schema + record/search actions                              | `FR-G`               | —                        |
| **D**   | D1      | Enrich the Dashboard (active career, recent veterans, goals)                | SCREEN-001           | C1,C3                    |
| **D**   | D2      | Scenario Selection                                                          | SCREEN-002           | —                        |
| **D**   | D3      | Trainee Selection + Profile                                                 | SCREEN-003/004       | D2                       |
| **D**   | D4      | Build Target screen + `ProvenanceBadge`                                     | SCREEN-005           | C1                       |
| **D**   | D5      | Legacy Lab (record-only: pick + browse + compare)                           | SCREEN-006           | C3                       |
| **D**   | D6      | Support Deck Builder                                                        | SCREEN-007           | D3                       |
| **D**   | D7      | Run Preflight                                                               | SCREEN-008           | D4,D5,D6                 |
| **D**   | D8      | Career Cockpit (header, state panel, action grid, advisor rail)             | SCREEN-009           | C2,D7                    |
| **D**   | D9      | Training Decision detail                                                    | SCREEN-010           | D8                       |
| **D**   | D10     | Race Decision                                                               | SCREEN-011           | D8                       |
| **D**   | D11     | Event Decision                                                              | SCREEN-012           | D8                       |
| **D**   | D12     | Inheritance Event (record-only)                                             | SCREEN-013           | D8                       |
| **D**   | D13     | Skills Planner *(design-2.0 only)*                                          | SCR-013              | D8                       |
| **D**   | D14     | Career Timeline                                                             | SCREEN-018           | D8                       |
| **D**   | D15     | Career Result                                                               | SCREEN-019           | D8                       |
| **D**   | D16     | Save Veteran + Veteran Library + Comparison                                 | SCREEN-020/021/022   | C3,D15                   |
| **D**   | D17     | Database (trainees/supports/skills/races/scenarios)                         | SCREEN-023           | A1–A3                    |
| **D**   | D18     | Settings (extend the ported Preferences)                                    | SCREEN-024           | —                        |
| **E**   | E1      | Scenario panel shell bound to `config/scenarios.php`                        | SCREEN-014/015/016   | D8                       |
| **E**   | E2      | URA panel (goals + Happy Meek)                                              | SCREEN-014           | E1                       |
| **E**   | E3      | Unity Cup panel (team rank + spirit)                                        | SCREEN-015           | E1                       |
| **E**   | E4      | Trackblazer panel (grade points + shop + epithets)                          | SCREEN-016           | E1                       |
| **E**   | E5      | Scenario Race Planner (race facts only; **no** win probability)             | SCREEN-017           | D8                       |
| **E**   | E6      | Grand Concert panel — baseline strip only *(design-2.0 only)*               | SCR-017              | E1                       |

**Phase A–D status, 2026-10-06.** A1, A2, A3, A4a, A4b and A4c landed green, B1 landed on their back,
and Phase C's three slices landed frontend-agnostic per `ADR-0020` §3. Phase D has begun: D1 landed
green, D2 to D4 landed green, and D5 and D6 landed run-scoped in `05e9584` before their own slice
numbers were reached. The wizard halves of D5 and D6 — steps 4 and 5 — are in the tree but **not
green**, and D7 onwards has not begun. No Phase E slice has begun.

The table below is the read at `0006b17`. Where a slice carries an open defect, the defect is in the
third column rather than in a commit message, because §4 is where the next slice decides what to start.

| Slice                 | State                 | Evidence, and what is still open                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| --------------------- | --------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| A1                    | Landed                | One clause of its browser case is unreachable, not unfinished: the `JapanOnly` notice. See §5.2.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| A2                    | Landed                | Filter button was found at 32px and raised to `h-11` during the port. See §5.3.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| A3                    | Landed                | `resources/js/pages/SupportCards/{Index,Show}.vue`; `rarity-chip`/`skill-row` Blade retired.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| A4a                   | Landed                | No-script fallback select retired by owner ruling 2026-10-05; 11 source-text shape pins became Playwright behaviour proofs. See §5.5.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| A4b                   | Landed                | `resources/js/pages/Runs/Show.vue` plus 19 ported panel components; `runs/show.blade.php` and its ten now-consumerless components deleted. Deviations and the two behaviour changes under the 2026-10-05 ruling are in §5.5.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| A4c                   | Landed                | Preview kept as a server-rendered Inertia page, not a JSON endpoint (it never was one). See §5.5.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                |
| B1                    | Landed                | `resources/views/components/` is now empty: the shell plus `app-button`, `capsule-header`, `deck-editor`, `energy-gauge`, `grade-badge`, `grade-point-meter`, `guided-step`, `race-calendar`, `resource-strip` and `run-header` are gone, with `resources/js/app.ts` and `guided-flow.ts` and the Vite input that named the first. The three error documents render themselves and `AppServiceProvider` composes them in place of the shell, `errors.500` staying out because it draws itself with no database. §6's measured set named eight components and missed `capsule-header`, `energy-gauge` and the shell itself.                                                                                                                                                                                                                                       |
| C1                    | Landed                | `build_target` json column, `App\Models\Advisor\BuildTargetPayload`, `StoreBuildTargetRequest` validating the five targets against `ScenarioCaps::forRun`.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
| C2                    | Landed                | `app/Services/Advisor/TrainerAdvisor.php` plus `config/advisor.php`; the held fields, score and numeric confidence and per-training yield, stayed out.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           |
| C3                    | Landed                | `veterans` table, `Veteran`, and `RecordVeteran`/`ListVeterans`/`ShowVeteran`, record-only. The plan's "rating" filter is not built: no source records a rating, and inventing one is the computation this slice forbids.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        |
| D1                    | Landed                | Commit `4b79346`. Dashboard enriched: active-career card with career position on the client's 24-turn grid, quick actions, recent Veterans capped at three, and the `GLOBAL DATA ● Current` badge. Recent Builds and the legacy-goal gaps are named absences, per §8.1 decision 5. `tests/browser/dashboard.spec.ts` (6 cases) and `DashboardTest` (7 cases) green; PHPStan and both lore gates at baseline.                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| D2                    | Landed                | Commit `17b793a`. `SetupLayout` plus `Career/ScenarioSelect.vue`; four cards derived wholly from `config/scenarios.php` by subtraction against the baseline entry, so a fifth scenario is one config line and no component edit, which `CareerScenarioSelectTest` proves by injecting a scenario at runtime. Persistence is `App\Services\Career\SetupDraft` on the session, per §8.2; the rejected early-`Active`-run alternative is recorded there with the phantom-career reason.                                                                                                                                                                                                                                                                                                                                                                             |
| D3                    | Landed                | Commit `17b793a`. `Career/TraineeSelect.vue`, `Career/TraineeProfile.vue`, `AptitudeBadge.vue`. Five of the brief's eight filters are built; **growth rate and scenario suitability are omitted because no column or table holds either**, and the profile's career-goals section is omitted because `trainee_goals` does not exist (KI-34), as are hint skills and `skills_evo`, each for a cited reason. `SCR-CAR-003` and `SCR-CAR-004` are in `SCREEN_SPEC.md`.                                                                                                                                                                                                                                                                                                                                                                                              |
| D4                    | Landed                | Commit `17b793a`. `ProvenanceBadge.vue` is the only owner of the four §49 glyphs, and `Career/BuildTarget.vue` is headed "Your target". The clamp reuses `StoreBuildTargetRequest` rather than restating it. **Open: `tests/browser/career-build-target.spec.ts` exists but has never been run by anyone**, so D4's browser evidence is absent. Two §8 rows are unbuilt by ruling, not by oversight: the purpose select offers `BuildPurpose`'s four cases, so the spec's "Competitive Build" has no option, and neither risk tolerance nor per-skill `Required/High/Optional/Ignore` exists because `BuildTargetPayload::KEYS` rejects an unknown key. Both need an owner decision.                                                                                                                                                                             |
| D5                    | Landed (run-scoped)   | Commit `05e9584`, before its slice number was reached. `pages/Legacy/{Index,Builder,Compare}.vue` over `ListVeterans`, `RecordVeteran` and `LegacySelectionPayload`, record-only, with the six-node graph as nested list items and `border-l` connectors. Spark probability renders `N/A` with the reason. Its browser spec is red for the reasons in the row below.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| D6                    | Landed (run-scoped)   | Commit `05e9584`, before its slice number was reached. `pages/Support/Builder.vue` over `runs.deck.sync`, with `components/support/{SupportSlot,SupportTypeMark,DeckAnalysis}.vue`; six slots, seven types, Scenario Link derived and never stored, `DeckAnalysis` limited to stated `SupportCardEffects` anchors with no score. **Open: 7 failures across `legacy.spec.ts` and `support-deck.spec.ts`.** Four are selector bugs — `toHaveText` reads `textContent` including an `aria-hidden` glyph, and one locator resolves to 25 elements because the picker renders one equip button per card. Two need investigation before any assertion is touched: the text-only picker rows, and a click timeout. `AGENTS.md` §18 warns that a never-run `uma:fetch-art` mirror looks exactly like the first of those.                                                 |
| D5/D6 wizard halves   | **Landed un-green**   | Commit `0006b17`. Steps 4 and 5 (`Career/LegacySelect.vue`, `Career/DeckSelect.vue`) carry ancestry and deck in the session draft, because both landed screens are run-scoped and the run is not created until D7. Shared bodies were extracted into `App\Services\Legacy\AncestryGraph` and the deck path rather than duplicated. Committed on the owner's instruction while red so the work is not stranded. **Three failures and three PHPStan errors, no browser spec, and no `SCR-CAR-*` rows.** The failures share one cause: the step-4 write saves `legacy_selection` but never sets `inheritance_parent_a_id` / `inheritance_parent_b_id`, which `LegacyController::update()` writes in the same statement because `ADR-0010` keeps those columns as the parent's identity. Reuse that resolution. A `KNOWN-ISSUES.md` entry is owed for landing red.   |
| D7                    | Not begun             | Blocked behind the row above: a Preflight that composes a draft whose ancestry keys never persist would inherit the defect silently. §8.3 now names the recorded Rice Shower run as the canonical acceptance seed.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               |
| D8–D18, E1–E6         | Not begun             | —                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                |

### 4.1 Open work carried out of this status read

In order, smallest first. Items 1 to 5 are defects; 6 to 9 are decisions and standing constraints that
every later slice inherits.

1. **Green the D5/D6 wizard halves.** The three failures are one cause: the step-4 draft write does not
   set `inheritance_parent_a_id` / `inheritance_parent_b_id`. Reuse `LegacyController`'s parent
   resolution instead of re-implementing it, then add the missing browser spec and the two
   `SCR-CAR-*` registry rows, and file the owed `KNOWN-ISSUES.md` entry.
2. **Return PHPStan level 6 to zero.** It reached zero after `17b793a` and regressed to three errors at
   `0006b17`. No suppression is an acceptable fix.
3. **Run `tests/browser/career-build-target.spec.ts`.** It has never been executed, so D4 has no
   browser evidence even though its props test passes.
4. **Green the seven D5/D6 browser failures.** Four are test-side selector bugs. Investigate the other
   two before touching an assertion, and record which kind each was; never delete or skip a test to
   reach green.
5. **Decide whether the two never-run artwork assertions are environment artefacts.** Prove it with
   `uma:fetch-art --dry-run` rather than by editing the expectation.
6. **Owner decisions that block content, not code:** whether `BuildPurpose` gains a "Competitive Build"
   case; whether risk tolerance and per-skill priority marks enter `BuildTargetPayload` (both are
   stored-shape changes); whether `config/scenarios.php` gains a sourced one-line scenario `description`
   and `recommended_use`; and whether `our_grand_concert.documented` should still read `true` now that
   `ab53861` recovered the primary read, since the flag being `true` makes the PARTIALLY DOCUMENTED
   badge unreachable while the client vocabulary in that scenario is still recorded as unverified.
7. **Environment gate.** Resolved 2026-10-06. `create_veterans_table` and
   `add_build_target_to_training_runs_table` (with `add_condition_groups_to_skills_table` and
   `add_awakening_event_evo_to_character_cards_table`) sat Pending on `database/database.sqlite` for
   days while 1326 tests passed, which is KI-60's symptom, so `/` and `/legacy` returned 500 there. All
   five now read Ran at batch **3**: a peer applied them, not this pass, whose `php artisan migrate`
   reported "Nothing to migrate". Verified on the port-8000 server attached to that database: both
   routes return 200, `Veteran::count()` queries without a missing-table exception, and
   `Schema::hasColumn('training_runs', 'build_target')` is true. `migrate:status` stays in every
   hand-off regardless, because a green suite proves nothing about that file. The browser suite still
   runs against a scratch database on port 8137 with `PLAYWRIGHT_BASE_URL`: it asserts an empty runs
   and veterans table, and the dev database now holds Trainer rows that would break that assertion.
8. **Toolchain constraints now binding on every Phase D and E slice.** TypeScript 7 removed
   `lib/typescript.js`, so `@vue/compiler-sfc` cannot resolve an imported type in `defineProps` and each
   page declares its props contract locally. There is no Ziggy, so pages state literal URLs. There is no
   axe dependency, so §12.5's hand-rolled checks are the accessibility evidence and "axe clean" must not
   be claimed.
9. **Focus and accessible-name rules learned the expensive way.** Restore focus in a visit's `onSuccess`
   plus `nextTick`, never `onMounted`; put the `id` on the focusable element; keep state words out of
   headings, because they join the accessible name; and assert with `getByRole(..., { name })` rather
   than `toHaveText` on any element containing an `aria-hidden` glyph.

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
```text

**Consumes:** `CatalogController::cardScope()` (reuse as-is), `Skill::whereIn('export_id', ...)` (unchanged),
`config('scenarios.scenarios')` labels.
**Produces:** `Catalog/Show.vue` and the four ported components; A3 reuses `SkillRow.vue`.

- [x] Apply the port recipe (5.1) steps 1–9.
- [x] Assert in `catalog-detail.spec.ts`: the trainee's Japanese name renders; the provenance URL renders;
```text
  the costume-form tabs keyboard-navigate and the active tab carries `aria-current`. **Deviation, and it
  is a real shortfall:** the fourth clause of this row — a japan-only entry printing "Not yet released
  on Global" — is **not** browser-asserted and cannot be on this repo's tooling.
  `UmamusumeRosterSeeder::inScope()` files `JapanOnly` rows as pending candidates rather than promoting
  them (PRD FR-A-1; measured 68 promoted of 135), so no `umamusume` row any database can build reaches
  that branch. Intercepting the response was tried and rejected: Blade points at the Vite dev server on
  `localhost:5173`, and Chromium's private-network check refuses those asset requests for a
  `route.fulfill()`-synthesised document, so the page never hydrates (and clearing `public/build/hot`
  to force same-origin assets would break a peer's running `composer dev` in this shared worktree).
  Covered where it can be: the prop that drives the branch is pinned in `CatalogDetailPageTest`, and
  `catalog-detail.spec.ts` proves the `JapanOnly` machine token never reaches a real page.
```

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

```text
  "No skills match", and the detail page's skill band headings are present. **Deviation:** the shipped
  copy is "Nothing matches {ask}." (D-65 names the ask rather than repeating "No skills match"), so
  `tests/browser/skills.spec.ts` asserts that string. The run caught a real defect: the Filter button
  carried `px-3 py-1.5` with no height and measured 32px against the 44px contract, a miss the Blade
  view also had; `Skills/Index.vue` now carries `h-11` like `Catalog/Index.vue`.
```

### 5.4 Slice A3 — Support cards (`SCR-SUP-001/002`)

**Files:** Modify `SupportCardController::index()` and `::show()`; create
`resources/js/pages/SupportCards/Index.vue`, `SupportCards/Show.vue`; migrate `SupportCardPageTest`,
`FilterContractTest` (the `pageSize` case already repointed here); new `tests/browser/support-cards.spec.ts`.
**Note:** `SupportCardController` uses the framework paginator (`->paginate()`), not the hand-built
`LengthAwarePaginator` the catalog uses — so the Vue paginator binding is the same `links` loop, but the
props test asserts `cards.perPage()` rather than a manual `pageSize` prop.
**Delete after green:** `resources/views/support-cards/index.blade.php`, `show.blade.php`, and
`resources/views/components/skill-row.blade.php` (its last consumer).

- [x] Apply the port recipe steps 1–9.
- [x] Browser: assert the card grid renders a rarity chip with an accessible name, and the detail page's

```text
  effect list renders stated anchor values with no invented interpolation. **Deviation:** the no-results
  copy is "Nothing matches {ask}." (D-65, same precedent as A2), so `tests/browser/support-cards.spec.ts`
  asserts that string rather than a "No support cards match" one. The chip's accessible name is the
  enum's `label()` ("One Star"), asserted via `getByRole('img', { name: 'One Star' })`; the at-cap effect
  prints its stated anchor ("Friendship Bonus 15%") beside the "highest stated anchor" basis note. The
  spec also records two Playwright gotchas it worked around: a wrapping `<label>`'s computed name
  swallows its `<option>` text (so the four selects are addressed by `select[name="…"]`), and the seeded
  snapshot URL carries a content hash (so the provenance link is matched on host + document, not a
  literal filename).
```

### 5.5 Slice A4 — Training runs (`SCR-RUN-001`–`005`)

The largest port: five screens, one controller, the guided turn rail, and the run-scoped write forms. Split
into three shippable sub-slices.

- **A4a — list + create** (`Runs/Index.vue`, `Runs/Create.vue`). Migrate `RunsIndexTest`,
  `RunCreateSurfaceTest`. Browser: `runs.spec.ts`. **Landed 2026-10-05.** Owner ruling recorded on the
  way: the create page's no-script fallback `<select>`, its `#trainee-roster` JSON island and the
  script's handover between the two pickers were retired, because a client-rendered page has no
  no-script path to serve. Eleven shape pins in `TraineeSelectorTest` that read `trainee-combobox.ts`
  source text (they existed because no JS runner did, and C-8 still forbids adding a JS unit-test
  dependency) became Playwright behaviour proofs in `runs.spec.ts`, which proves the filter, the cap,
  the keyboard path and the stale-pair rule rather than describing them. `trainee-combobox.ts` and the
  published `vendor/pagination/tailwind.blade.php` deleted with them; that view renders nowhere once
  the run list stopped calling `->links()`, so `DesignTokensTest`'s paginator gate became a class sweep
  over both source trees, which then caught two offenders the rendered-page check could never see.
  Two known gaps, stated not implied away: the populated run row and the pagination control are not
  browser-asserted (the seeded database holds zero runs and creating them from a browser test would
  mutate the development database the suite shares), and the cardless band is unreachable there (all
  67 seeded Global trainees have a confirmed card). Both are covered on the props side.
- **A4b — detail + guided turns** (`Runs/Show.vue`; the port of `x-run-header`, `x-stat-band`,
  `x-energy-gauge`, `x-mood-pill`, `x-resource-strip`, `x-deck-panel`, `x-race-calendar`, `x-race-panel`,
  `x-guided-step`, `x-capsule-header`, `x-app-button`, `x-grade-badge`, and the four Trackblazer/Unity Cup
  panels). **Landed 2026-10-05.** `TrainingRunController::show()` is now
  `Inertia::render('Runs/Show', $this->showData($run))`, and `showData()` *is* the page payload: every value
  it handed the view as a live model is now an explicit array, built by one private mapper per region
  (`runHeader`, `turnRows`, `goalRows`, `skillGroupsFor`, `deckPayload`, `railPayload`, …). The 19 Vue
  panel components are in `resources/js/pages/Runs/Show.vue`'s import graph, so `npm run build` compiles
  them; `runs/show.blade.php` and the ten Blade components whose only consumer it was are deleted.
  **Deviation from the shape this row sketched: the 15 named components each had exactly one consumer**,
  `runs/show.blade.php`, so the twins bought no reuse — but they were already written and each one owns a
  real piece of the page (the rail's `<form>`, the deck's own write, the calendar's cell grid), so the port
  wires them rather than inlining them. `GuidedStep` could not have been inlined: it owns the `<form>` that
  wraps the page's number fields, so its Vue shape is a component rendering a `<form>` around a default
  slot, which Vue supports directly.
  **Props actually shipped** (the sketch below was right about the regions and wrong about some key names):
  `run`, `turns`, `goals`, `skillGroups`, `skillCatalog`, `acquisitionOptions`, `moodOptions`, `scenarios`,
  `statuses`, `caps`, `statOrder`, `baseCap`, `hardCap`, `gradeBanding`, `maxObjectiveIndex`, `band`
  (null, not zeroed, for a run with no turns), `strip`, `currentMood`, `gradeMeter`, `teamRank`,
  `spiritBursts`, `teamRace`, `epithets`, `fatigue`, `calendar`, `deck`, `racePanel`, `shop`, `rail`, plus
  the shared `errors`/`flash`. The sketch's `resources`, `skills`/`runSkills`/`skillRows`, `unityCup` and
  `grade` keys do not exist: the resource strip's declaration rides on `strip`, the skill picker's
  catalogue is `skillCatalog` and the run's own skills are `skillGroups`, and the four Unity Cup panels are
  four top-level keys rather than one grouped under a scenario name (G-33: no scenario name in a prop).
  The contract the sketch worked out:

  ```php
  Inertia::render('Runs/Show', [

```text
  'run'     => ['id','umamusume_id','umamusume_name','status' => $run->status->value,
                'status_label','scenario','scenario_label','has_scenario','notes',
                'imported_display','import_source','export_csv_url','export_json_url',
                'update_url','destroy_url','turn_count'],
  'turns'   => [['id','turn','speed','stamina','power','guts','wit','sp','condition','energy',
                 'fans','mood','update_url','destroy_url',
                 'failure' => ['penalty_kind','source_name']|null]],
  'caps'    => ScenarioCaps::forRun($run),          // the same call the validator makes (KI-47)
  'band'    => null|['values','capBonus','skillPoints'],
  'strip'   => $run->stripValues() + widgets from config,
  'goals'   => [['title','state','year_label','turn']],
  'deck'    => ['equipped','slots','options','openSlot','action'],
  'rail'    => showData()'s `guided` block, with `previous` flattened to the placeholder values
               and `action` replacing `confirm-route`,
  'racePanel' => ['showUrl','racesUrl','entryMode','year','calendarSlots','manualSlots','entries','old'],
  'gradeMeter' => ['objectives','current','earned','unpricedCount','periods','unassignedCount'],
  'shop'    => ['panelsShop','purchaseUrl','catalogue','purchases','spendTotal','maxCopies', …],
```

  ]);

  ```text

  Two behaviours the port kept, both found by reading the view rather than the summary: (1) the failure
  chip is an **event**, not a column — `turn_events` keyed by turn, folded onto `turns[].failure` in
  `turnRows()`, because `turn_entries` holds the absolute readings the client showed (ADR-0003);
  (2) the rail rehydrates **only** through the payload's `rail.values` block, which the controller builds
  from the `old()`-and-`stage` branch, while the escape hatch and the turn-edit row keep their own per-field
  `useForm` defaults. The server branch was kept deliberately and the client rail was left stateless, so
  the two paths to one endpoint cannot fill each other in.
  **The five things the port must not lose, and where each went.** (1) `GuidedStep` renders a real
  `<form>` around its default slot. (2) The double-fill was avoided as above. (3) `KeyboardPathTest`'s
  three `guided-flow.ts` assertions moved, not disappeared: `GuidedStep.vue` owns the digits and Escape
  (a document-level listener, mounted and removed with the component), `import './guided-flow'` is out of
  `app.ts`, and the skip link plus the "Keys 1 to N" advertisement are asserted in
  `tests/browser/run-detail.spec.ts`. `guided-flow.ts` itself is now dead and is B1's to delete.
  (4) `RunViewTargetSizeTest`'s census moved to `AppLayout.vue`'s nine destinations, two of them named
  absences, and the sweep reads `Runs/Show.vue`. (5) `DeckPanel.vue` posting from form state and dropping
  the closed slots' hidden inputs is the stated 2026-10-05 behaviour change, not a port defect.
  **What this row's test migration cost.** ~14 files moved from rendered-text assertions to props
  assertions, which moved ~30 rendered-copy claims into `tests/browser/run-detail.spec.ts` (the section
  order, the 44px sweep, the rail's keyboard path, the scenario prose, the shop write and its price
  refusal, and the band's `B+` badge). `StatBandTest` lost two cases outright: the Blade band's guards
  ("refuses a band with no ceilings", "refuses an unknown scenario") were server throws, and the Vue band
  is not given a scenario key at all (G-33), so the surviving claim is the stronger one — the caps the
  page ships are `ScenarioCaps::forRun()`, the same call the turn validator makes.
  One infrastructure fix travelled with it: `php artisan test` exhausted the host's 128 MB CLI default
  part-way through the suite and reported no totals, so `phpunit.xml` now sets `memory_limit` the way
  PHPStan is already told to on the command line.

  Migrated: `RunViewFrameTest`, `RunViewNoScriptTest`, `RunViewTargetSizeTest`, `RunViewErrorEnvelopeTest`,
  `RunDeckTest`, `RunSkillPickerTest`, `RunStatusControlTest`, `RunSaveConfirmationTest`,
  `RunGoalsPanelTest`, `GoalPanelsOnRunDetailTest`, `GuidedTurn*`, `GuidedStep*`, `GuidedFirstTurnTest`,
  `StatBandTest`, `MoodPillTest`, `RaceCalendarTest`, `ScenarioPanelUiTest`, `KeyboardPathTest`,
  `TurnLogScrollRegionTest`, `ShopPurchasePayloadTest`, `SkillsFetchTest`, `RunUpdateTest`,
  `TrainingRunTest`, `GradePointMeterTest`, and the run halves of `SkillSearchScreenTest`. Browser:
  `run-detail.spec.ts` (7 tests).
- **A4c — import** (`Runs/Import.vue`; the form and the preview/confirm step). Migrate the import cases;
  the preview endpoint stays JSON (`runs.import.preview`). **Landed 2026-10-05.** Deviation from the brief:
  `runs.import.preview` was never JSON. It returned the same Blade view as the form, with a `preview` key
  set, and the plan described that as a JSON endpoint. The port keeps the shape rather than inventing a
  fetch layer: `importPreview` now returns `Inertia::render('Runs/Import', …)` with a `preview` prop, and
  the component branches on it. Two POSTs, no session state, and the commit still re-runs the same Form
  Request, which is what makes the preview not a trust boundary. Also closed here: `ImportErrorCopyTest`
  deferred the composite per-stat error sentence to "a browser pass on the landed view", and
  `tests/browser/run-import.spec.ts` now measures it against `Runs/Import.vue`. `runs/import.blade.php`
  deleted; `x-layout` survives on `runs/show` and the two error pages.

- [x] Each sub-slice applies the port recipe steps 1–9 and lands green before the next starts.
- [x] The run-scoped writes (`runs.turns.store/update/destroy`, `runs.deck.sync`, `runs.skills.sync`,
```text
  `runs.races.store`, `runs.purchases.store`) keep their Form Requests; the Vue forms only post and
  handle `useForm` errors. Assert one write per sub-slice still round-trips through its request.
```

- [x] Do not delete `x-guided-step` / `x-stat-band` / etc. until their consumer count reaches zero (grep in

```text
  step 8); the scenario panels in Phase E may still want them as reference. Ten are deleted; the nine
  that remain have zero consumers and are B1's, so Phase E keeps its Blade reference for now.
```

---

## 6. Phase B — retire the Blade shell

**B1 — one slice, after A1–A4 are green. Unblocked since A4b landed on 2026-10-05.**

The shell (`resources/views/components/layout.blade.php`, 11 consumers when this was written) has two left:
`errors/{404,419,500}.blade.php`. Delete it, delete `resources/views/components/` entries whose `<x-name`
count is now zero, and remove the Blade-era TS entries (`resources/js/guided-flow.ts`, whose last consumer
`Runs/Show.vue` replaced, and `resources/js/app.ts`, which `app.blade.php` no longer loads — it loads
`spa.ts`). `trainee-combobox.ts` already went with A4a.

**The zero-consumer set measured after A4b, which is this slice's real work:** `app-button`, `deck-editor`,
`grade-badge`, `grade-point-meter`, `guided-step`, `race-calendar`, `resource-strip`, `run-header`. Each has
a Vue twin in the run page's import graph. Two of them are still named by a test that renders them through
`Blade::render` and must be repointed or deleted with a stated reason, not deleted silently:
`GradePointMeterTest` (18 cases against `x-grade-point-meter`) and `ErrorPageViewsTest`'s shell assertions.
**Keep:** `resources/views/app.blade.php` (the Inertia root), `resources/views/errors/{404,419,500}.blade.php`
(rendered by Laravel's error handler and required to work with no Inertia page and no database —
`SCREEN_SPEC.md` `SCR-SYS-001/003/004`).

**Tests to repoint:** `DesignTokensTest` and `ErrorPageViewsTest` currently grep rendered Blade HTML. The
error pages stay Blade, so `ErrorPageViewsTest` is unchanged. `DesignTokensTest` moves its four pages from
Blade URLs to the ported Inertia URLs and asserts the same two things (no `dark:` utility, no skeleton
palette class) against the served HTML.

**Landed 2026-10-05, with a set three larger than measured above.** `capsule-header` and `energy-gauge`
were zero-consumer too once A4b had landed, and the shell is itself in the deletion. `DesignTokensTest`
had already moved off the Blade URLs with the A1–A3 retirements, so only its prose changed here;
`ErrorPageViewsTest` stayed Blade-scoped and gained the guard that a view must not reach for the shell
again.

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

| Slice   | Adds                                                                                                                                                                                 | Acceptance                                                                                                                                                                                                                                                                                          |
| ------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| D1      | Enrich `Dashboard.vue`: active career, recent veterans, data status indicator — the legacy-goal gaps panel was dropped by the slice plan (see §8.1 decision 5)                       | Every panel is real data or a named absence; no invented count; subtle "GLOBAL DATA ● Current" badge                                                                                                                                                                                                |
| D2      | `SetupLayout`, `Career/ScenarioSelect.vue`                                                                                                                                           | Cards render from `config/scenarios.php`; Grand Concert shows the baseline strip (`documented => false`); no scenario name branched in a component                                                                                                                                                  |
| D3      | `Career/TraineeSelect.vue`, `Career/TraineeProfile.vue`                                                                                                                              | Reuses the catalog queries; aptitude badges carry text, not colour alone                                                                                                                                                                                                                            |
| D4      | `Career/BuildTarget.vue`, `components/ProvenanceBadge.vue`                                                                                                                           | **Career Plan object**: purpose, race profile, stat/aptitude/style/skill targets, risk tolerance. Render as "Your target" not "The correct target". Target stats clamp via `ScenarioCaps`; skill priorities ordered; badge is only place four glyphs live                                           |
| D5      | `Legacy/Index.vue`, `Legacy/Builder.vue`, `Legacy/Compare.vue`                                                                                                                       | Record-only: pick, browse, compare; **no** optimizer, no "expected inheritance" (banner on the screen)                                                                                                                                                                                              |
| D6      | `Support/Builder.vue` (over `runs.deck.sync`, `x-deck-editor`)                                                                                                                       | **Six slots**; ownership flag (OWNED/RENTED), not structural borrowed assumption; **seven support types** (Speed/Stamina/Power/Guts/Wit/**Pal**/**Group**); Scenario Link derived from scenario+character; granular deck analysis (training power, early run, race bonus, safety, events, skills)   |
| D7      | `Career/Preflight.vue`                                                                                                                                                               | Composes D4–D6; warnings are the derivable ones only                                                                                                                                                                                                                                                |
| D8      | `CareerLayout`, `Career/Cockpit.vue`, `career/CareerHeader.vue`, `career/CareerStatePanel.vue`, `career/ActionGrid.vue`, `career/RecommendationCard.vue`, `career/AdvisorRail.vue`   | Three-column desktop / one-column mobile; the advisor rail reads C2 and prints band + reason lines, **not** a score or a numeric confidence; the recommendation never auto-executes                                                                                                                 |
| D9      | `career/TrainingCard.vue`, `Career/TrainingDetail.vue`                                                                                                                               | Per-option breakdown shows only sourced terms; the unsourced yield renders `N/A`                                                                                                                                                                                                                    |
| D10     | `career/RaceCard.vue`, `Career/RaceDecision.vue`                                                                                                                                     | Race facts from `RaceCatalogSlot` where present; win probability renders `N/A` with a `title` naming the `ADR-0016` blocker                                                                                                                                                                         |
| D11     | `career/EventCard.vue`, `Career/EventDecision.vue`                                                                                                                                   | Records the choice over `TurnEvent`; the advisor's override is always available                                                                                                                                                                                                                     |
| D12     | `Career/InheritanceEvent.vue`                                                                                                                                                        | Records observed inspiration outcomes over the existing event recording; predicted vs observed are kept apart (`ADR-0020` §3)                                                                                                                                                                       |
| D13     | `Career/SkillsPlanner.vue` *(design-2.0 only)*                                                                                                                                       | States Required/Available/Learned/Inherited; warns when SP cannot cover required; hint level discount calculation (base cost × (1 - discount%)); skill race-fit assessment (distance/surface/style/course/weather/ground/phase); no skill recommendation (OQ-5 stays open, `ADR-0020` §2)           |
| D14     | `career/CareerTimeline.vue`, `Career/Timeline.vue`                                                                                                                                   | Renders `TurnEntry` / `TurnEvent` history; before/after state; no silent rewrite of a past decision                                                                                                                                                                                                 |
| D15     | `Career/Result.vue`                                                                                                                                                                  | Reads the finished run; no derived "build quality" score                                                                                                                                                                                                                                            |
| D16     | `Career/SaveVeteran.vue`, `Veterans/Index.vue`, `Veterans/Show.vue`, `Veterans/Compare.vue`                                                                                          | Over C3; tags are suggestions, notes are free text                                                                                                                                                                                                                                                  |
| D17     | `Database/{Trainees,Supports,Skills,Races,Scenarios}.vue`                                                                                                                            | Reuses the ported catalog/skills/support screens; the Race database names the 0-row offline state                                                                                                                                                                                                   |
| D18     | Extend `Preferences/Edit.vue` → Settings categories (General / Recommendation / Data / Game version)                                                                                 | Every new preference is a real column; the ruleset row renders `N/A` until a ruleset string is sourced                                                                                                                                                                                              |

**Recommendation contract (D8).** The brief's §48 shape carries a `score` and a `confidence` string. This
plan **drops both**: numeric confidence and score are unsourced (`ADR-0001` §3). The component takes
`{ action, band: 'AtOrAboveAdvisory'|'BelowAdvisory', reasons: string[], alternative: string|null, risk: string|null }`.

**Scenario panel contract (E1).** The brief's §49 shape is adopted as-is —
`{ name, version, status, resources, objectives, actions, alerts, recommendations, finale }` — with `version`
and any `recommendations` that imply prediction rendered as named absences where the corpus is silent.

### 8.1 D1 — Dashboard enrichment (SCREEN-001): slice plan

The two bite-sized Phase D slice plans this section promises are filed here as they were written.
Embedded 2026-10-06 from `.scratch-uma/D1-dashboard-plan.md`, headings demoted one level.

Slice row: plan §8 D1. Depends on C1 (BuildTarget) and C3 (veterans), both landed.
Authority: landed ADRs > AGENTS.md > plan > design-2.0 / screen-spec-2.0.

### Files

| File                                             | Change                                                                                |
| ------------------------------------------------ | ------------------------------------------------------------------------------------- |
| `app/Http/Controllers/DashboardController.php`   | rewrite `index()`; private mappers `activeCareer`, `recentVeterans`, `widgetLabels`   |
| `config/scenarios.php`                           | add top-level `widget_labels` (7 widgets) and `verified_at`                           |
| `resources/js/types.ts`                          | export `DashboardProps` (the contract lives here once)                                |
| `resources/js/pages/Dashboard.vue`               | rewrite: ActiveCareer, QuickActions, RecentVeterans, RecentBuilds, DataStatus         |
| `tests/Feature/DashboardTest.php`                | rewrite to props assertions (active / no-active / no-veterans)                        |
| `tests/browser/dashboard.spec.ts`                | new: empty state, resume link, DataStatus text, 44px sweep                            |
| `SCREEN_SPEC.md`                                 | new `SCR-CAR-001` row in §2 + §4 section with empty/loading/error states              |

### Props contract

```php
Inertia::render('Dashboard', [
  'activeCareer' => null | [
      'run_id' => int, 'trainee' => string, 'trainee_ja' => string|null,
      'scenario_label' => string,          // matrix label, or 'No scenario set'
      'position_label' => string|null,     // 'Classic Year · Early July'; null when no turn logged
      'turn' => int,                       // logged turn count (same reading as the run strip)
      'energy' => int|null,
      'resource' => null | ['label' => string, 'value' => int|null, 'value_hint' => string],
      'resume_url' => string,
  ],
  'recentVeterans' => [
      'rows' => list<['id','trainee','trainee_ja','scenario_label','run_id','run_url']>,
      'total' => int, 'library_url' => string, 'limit' => int,
  ],
  'quickActions' => list<['label' => string, 'to' => string]>,
  'counts' => ['trainees' => int, 'skills' => int, 'supportCards' => int],
  'dataStatus' => ['label' => string, 'state' => string, 'verified_at' => string|null],
]);
```text

Ruleset line reads the **shared** `app.ruleset` (null) in the component, not a second prop.

### Decisions (and why)

1. **"Primary resource" = first widget in the scenario's `widgets[]` that the baseline
   (`config('scenarios.baseline')`, ura_finale) does not also compose.** Config-driven, no
   scenario-name branch (G-33). Yields `team_rank` (Unity Cup), `grade_points` (Trackblazer),
   null (URA Finale, Our Grand Concert). Its value comes from the same `stripValues()` map the
   run strip uses, so it is `N/A` + a reason today (no column holds it).
2. **`widget_labels` added to `config/scenarios.php`.** ResourceStrip.vue owns its own label
   strings for the run page's treatment; config is the composition single-source-of-truth
   (G-33/D-240) and the Dashboard's label is composition vocabulary. Overlap recorded as a
   follow-up in the report rather than widening D1's blast radius into a landed component.
3. **`verified_at` added to `config/scenarios.php`** (`2026-09-27`), the date the file's own
   header already states and the plan §3 table repeats. No new number.
4. **Recent Builds = named absence.** No "build" entity or list query exists; a run is not a
   build until it holds a Career Plan (C1's `build_target`), and D2–D7 is where that is entered.
   Rendering the runs list under the word "Builds" would mislabel a run.
5. **Legacy-goal gaps panel omitted.** A gap needs the active run's targets compared against
   inherited stats, which is the inheritance computation `ADR-0020` §3 bans. C1 and C3 give no
   honest comparison. Stated in the report, not rendered.
6. **Recent Veterans capped at 3** via the existing `ListVeterans` action (already eager-loads
   `trainingRun.umamusume`). No new query, no N+1, no cache (trainer data is never cached).
7. **Loading / error states are page-level** (Inertia visit in flight / visit failed), because
   this page's only user-initiated async action is following a link (ADR-0007).
8. **Active run = the newest `Active` run by id.** Multiple active runs are possible; recorded
   as an open question.

### Tests

- Props: active career present (name, scenario label, position, turn, energy, resource, resume
  url); no active career → `activeCareer` null; no veterans → `rows` empty + `total` 0.
- Browser: empty state copy + what/why/what-to-do; create a run through the form → Resume link
  and card facts; DataStatus text; 44px sweep; `lang="ja"` on the Japanese name.

### Open questions

1. Multiple `Active` runs: which is "the" active career? (Chose newest; no ruling exists.)
2. Should the Dashboard keep the catalogue counts? (Kept, folded into DataStatus as the
   "database values" row — real data, no invention.)
3. ResourceStrip's TS widget labels vs config `widget_labels`: unify later?

---

### 8.2 D2 — Scenario Selection + SetupLayout (SCREEN-002): slice plan

Embedded 2026-10-06 from `.scratch-uma/D2-wizard-plan.md`, headings demoted one level. D2 sets the wizard contract D3 to D7 inherit, so its persistence decision is the one a later slice reads first.

Depends on: nothing (plan §8). Sets the wizard contract D3–D7 build on.

### Wizard contract (the thing D3–D7 inherit)

Steps, each reading the draft and writing its own key:

| Step             | Slice   | Route                               | Writes to the draft                   |
| ---------------- | ------- | ----------------------------------- | ------------------------------------- |
| 1 Scenario       | D2      | `GET/POST /career/setup/scenario`   | `scenario` (config key)               |
| 2 Trainee        | D3      | `/career/setup/trainee`             | `umamusume_id`, `character_card_id`   |
| 3 Build target   | D4      | `/career/setup/target`              | `build_target` payload (C1)           |
| 4 Legacy         | D5      | `/career/setup/legacy`              | `legacy_selection` payload            |
| 5 Deck           | D6      | `/career/setup/deck`                | deck slot list                        |
| 6 Preflight      | D7      | `/career/setup/preflight`           | nothing; **creates the run**          |

`SetupLayout` shows "Step n of 6" and a labelled step nav. A step whose slice has not
landed is a named absence (`to: null`), the same rule `AppLayout` uses for Veterans.

### Persistence decision

**A session draft, `session('career.setup')`, holding the wizard's selections; the run is
created once, at D7.** No schema change, no new enum value, no migration.

Why not the alternatives:

- **An `Active` run created at step 1** (the obvious "draft run"). Rejected: `RunStatus`
  has no draft case and inventing one needs owner approval. Reusing `Active` writes to the
  database before the Trainer has chosen a trainee, and D1's Dashboard resumes the newest
  `Active` run, while the Legacy Lab builder opens on any `Active` run — so an abandoned
  wizard would leave a phantom career and a phantom inheritance target. `Retired` means
  "abandoned before completion" and `Completed` is worse; both would mislabel a draft.
- **A `drafts` table / a nullable `training_runs.draft` column.** A migration, and the
  prompt forbids one without approval.
- **`sessionStorage` only.** Lost on a new tab and invisible to the server, so no step
  could validate against the draft server-side.

What the challenge surfaced (refresh / back / two tabs / abandonment):

- **Refresh** — the draft is server-side, so the selection survives. ✓
- **Back button** — Inertia restores from history and the server re-reads the session. ✓
- **Two tabs** — both share one session, so they share one draft and the last write wins.
  **Accepted ceiling, stated in the code**: one Trainer, one setup in progress. Two
  scenarios cannot be drafted side by side; a second tab is a second view of the same
  draft, not a second draft.
- **Abandoned wizard** — nothing reaches the database; only a session value expires with
  the session. This is the property the `Active`-run option cannot have. ✓
- **Session loss** (different browser, session expiry) — the draft is gone. Accepted: the
  wizard is six short steps, and a lost draft costs a re-pick, not data.
- **D4's clamp** — `ScenarioCaps::forRun()` reads only `hasScenario()`/`scenarioKey()`
  (verified in `app/Services/ScenarioCaps.php:81-100`), so D4 can clamp against a
  **non-persisted** `new TrainingRun(['scenario' => $draft['scenario']])`. No run needed
  before D7.

### Card content: derived from config, nothing invented

The prompt's rule is "do not write mechanics the config does not hold", and a fifth
scenario must need one config entry and no component edit. So every field is derived:

| Field                                      | Source                                                                                |
| ------------------------------------------ | ------------------------------------------------------------------------------------- |
| Name                                       | `label`                                                                               |
| Optimization focus (primary / secondary)   | `cap_bonus` — highest-bonus stat(s) as primary, the rest secondary. Text, no stars.   |
| Primary mechanic (systems)                 | the `panels` that are `true` + the `widgets` beyond the baseline's three, as labels   |
| Training loop                              | `steps` rendered as a labelled sequence                                               |
| Availability                               | `live_on_global`                                                                      |
| Ruleset version                            | `app.ruleset`, which is null → `N/A` + a `title`                                      |
| PARTIALLY DOCUMENTED badge                 | `documented === false` (only `our_grand_concert` carries it)                          |

`our_grand_concert` falls out of this derivation with **no special case**: every panel off
and baseline-only widgets yields "no scenario system", which is the honest statement, and
`documented` adds the badge. URA Finale derives the same way and is correct — it *is* the
baseline.

Deviation to report: the brief's "short description" and "recommended use" have no source.
The card renders the composed composition summary instead of an invented blurb, and the
owner question is whether `config/scenarios.php` should gain a sourced one-line
`description` per scenario.

### Files

| File                                                         | Change                                                        |
| ------------------------------------------------------------ | ------------------------------------------------------------- |
| `resources/js/layouts/SetupLayout.vue`                       | new: wizard shell, step indicator, skip link, labelled nav    |
| `resources/js/pages/Career/ScenarioSelect.vue`               | new: four cards, radio-group selection                        |
| `app/Http/Controllers/Career/ScenarioSelectController.php`   | new: `show` + `store` (session draft)                         |
| `app/Http/Requests/StoreScenarioChoiceRequest.php`           | new: validates the scenario key against config                |
| `routes/web.php`                                             | two routes                                                    |
| `tests/Feature/ScenarioSelectTest.php`                       | new: props + the config-iteration proof                       |
| `tests/browser/scenario-select.spec.ts`                      | new: four cards, Grand Concert state, keyboard, 44px, 320px   |
| `SCREEN_SPEC.md`                                             | `SCR-CAR-002` row + §4 section with the state table           |

No new package, no migration, no ADR.

---

### 8.3 Run-informed acceptance: the Rice Shower Unity Cup run (D7–D16, E3)

Added 2026-10-06. The recorded run report (`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md`; 67 Global
client frames captured 2026-10-03 plus the seeded catalog) is this repository's only end-to-end record
of a career mid-flight, so its §1.1–1.9 state is the canonical seed for the cockpit slices' browser
specs: stats 607/577/549/736/397 against caps 1325/1322/1368/1308/1800, SP 173, fans 209,245,
Team Rank S with league 8th, 55 Unity Trainings / 6 Spirit Bursts / 5 Extreme, mood GREAT, energy
low, five turns to the finals. The report's own observed / calculated / uncertain splits (§8.2, §8.7)
are the run-shaped instance of the four provenance states the plan already mandates; nothing new is
invented to carry them. Per-slice deltas the run grounds:

- **D6 Support Deck Builder.** Catalog-maximum and client-current are two columns, not one (§1.4:
  "the catalog is what the card can be, the client column is what this run has"). A padlocked effect
  row prints the level the effect unlocks at, never a value ("Race Bonus 🔒 Lvl 45" is a level, not
  45%). The borrowed slot renders the client's "Friends" pill — whether that is a third value of the
  OWNED/RENTED flag or a display variant on RENTED is the slice's first question. Scenario Link is
  derived, not entered: `SupportCard::isScenarioLink()` (`app/Models/SupportCard.php:159`, already
  called by `TrainingRunController`) against `config('scenarios.php')`.
- **D9 Training Decision.** The client prints the preview and the failure percentage on the tile
  (§1.7), so both may be recorded as observed with Confirmed provenance; the advisor still derives
  neither (C2 unchanged, §7).
- **D10 Race Decision.** Condition-skill applicability follows the race card's going and weather:
  at Soft going, Firm Conditions ○ cannot fire while Sunny Days ○ can (§1.6, §2.3). The client's own
  verdict ("top contender") is recorded text, never a computed prediction. Fan-gap arithmetic
  (6,700 of the 30,755 to Top Star) renders as Calculated.
- **D13 Skills Planner.** The hint discount ladder is client-read and sourced (§3's new row), so the
  displayed-cost calculation ships Confirmed. The Ignited → Burning Spirit prerequisite is published
  (run report §8.1) and renders Confirmed; whether the gold suppresses or replaces the white in-race
  renders Unknown. The ○ and ◎ tiers of one skill render as one progression, not two unrelated rows.
- **D14 Career Timeline.** The client's own turn counter wins (§8.7): five turns printed is five
  turns recorded, and calendar arithmetic renders as a discrepancy note, never a correction.
  SCREEN-018's before/expected/actual/result is the report §5's own method and needs no extension.
- **E3 Unity Cup panel.** The Team Info panel's field set is the panel's spine (§1.8): team, motto,
  rank, league, per-stat grades, Unity Trainings, Spirit Bursts, Extreme Bursts, members. The five
  Team-tab columns are the finals programme (report §8.3), so the ACE lineup is the finals
  preparation screen. The burst-band table renders with the Extreme-counting caveat; the November
  deadline renders; the "+30" ranking bonus renders Estimated.

Corpus currency: `docs/scenarios/02-unity-cup.md` carries three corrections and one addition flagged
but not applied by the run report (§7.4–7.5: the payout date, the payload figures, the withdrawal of
the "Ignited Spirit" names, and the +30 bonus). E3 renders per the corpus as it stands; when the
owner applies those edits, the panel's provenance moves with them, not before.

### 8.4 D3 — Trainee Selection + Trainee Profile (SCREEN-003 / SCREEN-004): slice plan

The third and fourth Phase D bite-sized slice plans, embedded 2026-10-06 from `.scratch-uma/D3-trainee-plan.md`, headings demoted one level. D3 is where the wizard contract actually got set, because at its verification point D2 had not landed; where its text differs from §8.2, this section is the record of what shipped.

Depends on D2, which is **not landed** (`resources/js/pages/Career/` does not exist; only
`resources/js/layouts/AppLayout.vue`). Verified on 2026-10-06 at `40c91d4`. So D3 carries the
wizard contract itself. See §Wizard below.

Everything in the Filters/Sections tables below was measured read-only against the seeded scratch
database (`umamusume` 67 rows, `character_cards` 106, `umamusume_profiles` 67, `skills` 1,910), not
inferred from docblocks.

### Wizard (the contract D2 should have set, now set here)

**Persistence: `session('career.setup')` draft. No schema change, no new enum value, no migration.**
The run is created once, at D7 Preflight. An `Active` run created at step 1 was rejected: `RunStatus`
has no draft case, and D1's Dashboard resumes the newest `Active` run while the Legacy Lab builder
opens on any `Active` run — an abandoned wizard would leave a **phantom career**.

Draft keys: `scenario` (string|null), `umamusume_id` (int|null). D4–D7 add theirs.

`SetupLayout.vue` renders "Step n of 6", a labelled step nav, the skip link, and the same `banner` /
`nav` / `main` / `contentinfo` landmarks as `AppLayout`. **A step whose slice has not landed is a
named absence (`to: null`), never a dead link** — so today steps 1 and 4–6 are absences and only
steps 2–3 (this slice) are live. `/career/setup` resolves to the first landed step, which keeps the
routing forward-compatible when D2 and D4 arrive.

### SCREEN-003 roster — filters

The brief names eight. **Two have no data and are OMITTED, per the prompt's own instruction.**

| Filter                     | Decision    | Grounded in                                                                                                                                                                                                                                                                                  |
| -------------------------- | ----------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Search (name / slug)       | **build**   | `umamusume.name` 67/67, `slug` 67/67 (`2026_09_26_162814_create_umamusume_table.php:16-19`)                                                                                                                                                                                                  |
| Surface                    | **build**   | `umamusume.aptitude_turf` / `aptitude_dirt`, 67/67 each (`2026_09_27_090000_add_aptitude_grades_to_umamusume_table.php:16-26`)                                                                                                                                                               |
| Distance                   | **build**   | `aptitude_sprint/mile/medium/long`, 67/67 each                                                                                                                                                                                                                                               |
| Running style              | **build**   | `aptitude_front_runner/pace_chaser/late_surger/end_closer`, 67/67 each. Client words: `lang/en/uma.php:92-95`                                                                                                                                                                                |
| Aptitude (letter)          | **build**   | domain is `S A B C D E F G`, set by the parser's `preg_match('/^[SABCDEFG]$/')` at `app/Services/DataPipeline/Parsers/GametoraCharacterParser.php:143`. All-or-nothing: `:134` writes no letters unless all ten exist                                                                        |
| Unique skill               | **build**   | `character_cards.skills_unique` json (106/106 non-null, 0 empty, 122 ids) — filtered as a json-contains subquery over the trainee's cards. No index on those columns, deliberately (`2026_09_30_073029_…:32-34`)                                                                             |
| **Growth rate**            | **OMIT**    | no column in any of the 30 tables. Explicitly deferred: `docs/research-scratch/GOVERNANCE.md:600` ("**No column added now**"), and banned from display at `docs/research-scratch/DESIGN-CORPUS.md:727-728`                                                                                   |
| **Scenario suitability**   | **OMIT**    | no table holds it. `config/scenarios.php` `scenario_links` is a per-scenario cast list, not a suitability claim — URA's only entry is `Aoi Kiryuin`, a staff character with **no `umamusume` row**; Trackblazer's list is empty and its note says "no Scenario Link character exists here"   |

Sorting: the brief asks for it; `CatalogController::index()` has no sort parameter today, so D3 adds
an explicit allowlist over real columns (`name`, then the aptitude letters) rather than a free
`ORDER BY`. An unknown sort falls back, the way `PageSize::clamp` degrades rather than refuses.

**Trainee card:** name (+ `name_ja` with `lang="ja"`), `RarityChip`, `ArtworkSlot` at the §45a row
geometry (`size-10`, `reserve`, decorative `alt=""`), the aptitude letters, and two actions:
"View Profile" and the primary "Select Trainee". A new `AptitudeBadge.vue` prints **letter + band
word** (S/A Strong, B/C Neutral, D/E Weak, F/G Very weak, `design-2.0` §17) because `AptitudeGrid.vue`
is a definition list with no band word and the acceptance forbids colour-only.

### SCREEN-004 profile — sections

| Section                                           | Decision                        | Grounded in                                                                                                                                                                                                                                                                                                    |               |                  |                                                                                                                                                                                                                                                                 |
| ------------------------------------------------- | ------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------- | ---------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Basic: name                                       | **build**                       | `umamusume.name`, `name_ja`                                                                                                                                                                                                                                                                                    |               |                  |                                                                                                                                                                                                                                                                 |
| Basic: rarity                                     | **build, at card grain**        | `character_cards.rarity` (`app/Enums/CardRarity.php`). No trainee-level column: a trainee rarity is a `MAX()` over her cards, so it is printed per costume form, not as one trainee fact                                                                                                                       |               |                  |                                                                                                                                                                                                                                                                 |
| Basic: version                                    | **N/A + title**                 | no `version` column anywhere; `app.ruleset` is hard-coded null at `app/Http/Middleware/HandleInertiaRequests.php:43-45`                                                                                                                                                                                        |               |                  |                                                                                                                                                                                                                                                                 |
| Basic: growth rates                               | **OMIT the row**                | as above — the brief's own card mock shows "Speed +20%", and there is no such datum. Printing `N/A` for a whole absent concept would be a fake field                                                                                                                                                           |               |                  |                                                                                                                                                                                                                                                                 |
| Basic: birthday / height / VA                     | **build**                       | `umamusume_profiles` 67/67 (`hasFullBirthday()`, `hasThreeSizes()` guards). No weight column at all                                                                                                                                                                                                            |               |                  |                                                                                                                                                                                                                                                                 |
| Aptitudes                                         | **build**                       | ten letters, **once, not per form** (`SCREEN_SPEC.md:581`), via `AptitudeGrid` + `AptitudeBadge`                                                                                                                                                                                                               |               |                  |                                                                                                                                                                                                                                                                 |
| Skills: unique / starting / awakening / event     | **build**                       | `character_cards.skills_unique                                                                                                                                                                                                                                                                                 | skills_innate | skills_awakening | skills_event`. Names only through`Skill::scopeAvailableOnGlobal()`(`app/Models/Skill.php:102-107`, 623 of 1,910 pass) → 6 of 237 awakening ids are not renderable and are counted, not dropped silently.`sp_cost` prints `N/A` where null (1,004 of 1,910 null) |
| Skills: hint                                      | **OMIT**                        | no `character_cards.skills_hint`; hint skills live only on `support_cards`                                                                                                                                                                                                                                     |               |                  |                                                                                                                                                                                                                                                                 |
| Skills: evolution                                 | **OMIT deliberately**           | `skills_evo` is stored but `SCREEN_SPEC.md:586` refuses to list it (829 dead links)                                                                                                                                                                                                                            |               |                  |                                                                                                                                                                                                                                                                 |
| **Career goals**                                  | **OMIT the section**            | **`trainee_goals` does not exist** — no migration, no model, no factory; `race_catalog_slots` has no trainee-bearing column (`:70-72` says so in terms) and `scenario_slots` likewise. Reserved as KI-34, needing a fetch-source decision. Confirms the suspicion that the calendar is per-scenario and shared |               |                  |                                                                                                                                                                                                                                                                 |
| Build analysis: ideal distances / suitable styles | **build, labelled as aptitude** | the only trainee-side signal is the four distance and four style letters. Naming them "ideal"/"suitable" would be my conclusion, so the section says *aptitude letters* and links the letters to the stored columns                                                                                            |               |                  |                                                                                                                                                                                                                                                                 |
| Build analysis: recommended stat distribution     | **N/A + title**                 | no recommendation column anywhere; `training_runs.build_target` is per-run and Trainer-typed, and `BuildTargetPayload` states "It records and computes nothing" (`:19`)                                                                                                                                        |               |                  |                                                                                                                                                                                                                                                                 |
| Build analysis: useful inheritance                | **N/A + title**                 | `inheritance_parent_*` are nullable FKs on a **run** (0 rows); the gene/evo source fields are deliberately not stored (`docs/adr/0011-skills-reference-import.md:117`)                                                                                                                                         |               |                  |                                                                                                                                                                                                                                                                 |
| Build analysis: useful support types              | **N/A + title**                 | `support_cards` has **no** `umamusume_id` and no trainee relation — the absence is a ruling (`2026_09_30_151945_…:36-38`); joining on `char_name` text would be a name match, not data                                                                                                                         |               |                  |                                                                                                                                                                                                                                                                 |

### Reuse, not duplication (code-simplification)

`RarityChip`, `ArtworkSlot`, `AptitudeGrid`, `SkillRow`, `FormTabs`, `TraineeCombobox` are already
standalone shared SFCs, so the new pages **import** them. No extraction from `Catalog/Index.vue` /
`Catalog/Show.vue`: the audit shows the index page imports only `RarityChip` + `ArtworkSlot`, and the
remaining bodies are page-level markup, not components. Extracting them would risk the catalog's
behaviour and its two Playwright specs, which this slice must leave green. Server-side,
`CatalogController::cardScope()` and the `PageSize::clamp` contract are reused as-is.

### Files

| File                                                         | Change                                               |
| ------------------------------------------------------------ | ---------------------------------------------------- |
| `resources/js/layouts/SetupLayout.vue`                       | new                                                  |
| `resources/js/pages/Career/TraineeSelect.vue`                | new                                                  |
| `resources/js/pages/Career/TraineeProfile.vue`               | new                                                  |
| `resources/js/components/AptitudeBadge.vue`                  | new (letter + band word)                             |
| `app/Http/Controllers/Career/TraineeSelectController.php`    | new: `show` + `store`                                |
| `app/Http/Controllers/Career/TraineeProfileController.php`   | new                                                  |
| `app/Http/Requests/CareerTraineeSearchRequest.php`           | new (search/filter/sort allowlist)                   |
| `app/Services/Career/SetupDraft.php`                         | new: the session draft, one owner                    |
| `routes/web.php`                                             | three routes                                         |
| `tests/Feature/CareerTraineeSelectTest.php`                  | new: props + filter behaviour + draft carry          |
| `tests/browser/scenario-trainee-select.spec.ts`              | new: keyboard selection, 44px, 320px                 |
| `SCREEN_SPEC.md`                                             | `SCR-CAR-003` + `SCR-CAR-004` rows and §4 sections   |

No new package. No migration. No ADR.

### Open questions / things to report

1. **D2 and D4 are unbuilt**, so "goes to D4" and "Back keeps the chosen scenario" can only be proven
   as: the draft carries `scenario` and survives a round trip (props test seeds the session; the
   browser proves survival across D3's own two screens). Steps 1 and 4–6 are named absences.
2. **Out-of-slice defect found, not fixed:** `umamusume_profiles.name_ja` is **0 of 67** populated
   because `GametoraCharacterProfileParser.php:139` reads `jp_name` while the committed body
   (`database/seeders/data/characters.c6676539.json`) carries `name_ja`. Consequence:
   `Umamusume::japaneseName()`'s profile branch (`app/Models/Umamusume.php:109-118`) always falls
   through to `umamusume.name_ja`. That is the Data Engineer's pipeline, not this screen; the screen
   reads `umamusume.name_ja` so it is correct either way.
3. `character_cards.skills_*` stored as `[]` is **ambiguous** (parser returns `[]` for missing *or*
   malformed), so an empty list cannot be read as "the source stated none". The profile says "none
   published" only where it can.
4. Whether to add a sourced one-line scenario `description` to config (carried from D2).

---

### 8.5 D4 — Build Target + ProvenanceBadge (SCREEN-005): slice plan

The fifth Phase D bite-sized slice plan, embedded 2026-10-06 from `.scratch-uma/D4-build-target-plan.md`, headings demoted one level. Its four open questions and its two un-built conflicts are the items a later slice or the owner reads first.

Depends on **C1 only**, which is landed (`BuildTargetPayload`, `StoreBuildTargetRequest`,
`build_target` column, `runs.build-target.update`). D2/D3 are unbuilt, so this slice does **not**
invent the wizard shell: the page mounts in `AppLayout` and re-wraps into `SetupLayout` when D2/D3
land. The route is run-scoped, which is what C1's Form Request already requires (`withValidator`
reads `$this->route('run')`).

Verified at `40c91d4`. Gate baseline to beat: PHPStan 18 errors (`TrainingRunController` 2,
`DeckAnalysis` 16), `php artisan test --compact` 1326 passed / 2 skipped, browser suite 73 passed /
6 failed (all pre-existing, all in files outside these diffs).

### ProvenanceBadge — the shared contract, fixed once

States are `design-2.0` §49 verbatim: **Confirmed / Calculated / Estimated / Unknown**. §20's
HIGH/MEDIUM/LOW is a different vocabulary and is **not** merged into the badge; the plan's D8 row
drops numeric confidence entirely, so nothing here prints a percentage.

| state        | glyph   | text         | meaning carried in `title`                                                |
| ------------ | ------- | ------------ | ------------------------------------------------------------------------- |
| Confirmed    | `✓`     | Confirmed    | stored or entered as it is shown                                          |
| Calculated   | `∑`     | Calculated   | derived by this tool from stated inputs, arithmetic named                 |
| Estimated    | `~`     | Estimated    | a judgement, never a fact (§49 "never represent an estimate as a fact")   |
| Unknown      | `?`     | Unknown      | no source holds it                                                        |

Contract rules, decided here so no later slice re-decides them:

- **`defineProps<{ state: ProvenanceState; title?: string }>()`** and
  `export type ProvenanceState = 'confirmed' | 'calculated' | 'estimated' | 'unknown'`. The glyphs and
  words live in one `const` map inside this file and nowhere else. **Grep gate** in the slice test:
  the four state words must not appear as a rendered literal in any other `.vue`.
- **Type export caveat, stated once:** consumers can import `ProvenanceState` for `ref<…>()` and
  helper signatures, but **cannot** put it in their own `defineProps<>` — TS 7 ships no
  `lib/typescript.js`, so `@vue/compiler-sfc` cannot resolve an imported type there (proven in D1:
  the build fails with "No fs option provided to `compileScript` in non-Node environment"). Each page
  therefore declares its props contract locally. This is the repo's existing pattern, not a new one.
- **Accessible name = the word, not the glyph.** The glyph span is `aria-hidden="true"`; the visible
  text node carries the name; the element is `role="img"` with `:aria-label` set to the state word
  plus the `title` when the caller supplied one, so a screen reader hears one statement, not two
  (the same reasoning as `Legacy/Index.vue`'s `role="note"` banner — and its known failure:
  Playwright `toHaveText` reads `textContent` *including* an `aria-hidden` glyph, so this component's
  own spec asserts the label via `getByRole('img', { name })` and never via `toHaveText` on the
  wrapper. That is the pre-existing `legacy.spec.ts` defect, not repeated here).
- **Placement rule:** it sits immediately beside the figure it labels, never in a page-level legend
  (Law of Proximity, `design-2.0` §13 row). A figure with no badge is a bug, and `Unknown` is the
  state for "there is no number".
- **Contrast:** glyph and text both use `text-ink` / `text-ink-strong` on `bg-panel`/`bg-raised`.
  Measured from `resources/css/app.css:40-54` tokens, never a raw hex. The glyph is a text character,
  so it is not a colour-only signal, and each state has a *distinct* glyph *and* a distinct word.

### BuildTarget page — "Your target"

Heading is **"Your target"**. The plan's D4 row forbids "The correct target" outright.

Fields come from `BuildTargetPayload` **only** — `purpose`, `distance`, `surface`, `style`, five
`targets`, ordered `skill_priorities`. Nothing else is offered.

| Field               | Source                                 | Notes                                                                                                                                                                                                                                                                        |
| ------------------- | -------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| purpose             | `BuildPurpose` enum (4 cases)          | options rendered from `array_map(->label())`; **labels added to `lang/en/uma.php` as `build_purpose`**, which `HasLabel.php:19-22` already expects (key `uma.build_purpose.<CaseName>`) and which the enum docblock declares as owed: "a display slice owes the label map"   |
| distance            | `BuildTargetPayload::DISTANCE_BANDS`   | `Sprint/Mile/Medium/Long` = what `race_catalog_slots` publishes                                                                                                                                                                                                              |
| surface             | `BuildTargetPayload::SURFACES`         | `Turf/Dirt`                                                                                                                                                                                                                                                                  |
| style               | `BuildTargetPayload::STYLES`           | Global client vocabulary from `lang/en/uma.php`                                                                                                                                                                                                                              |
| five stat targets   | `config('scenarios.stat_order')`       | cap shown beside each field from `ScenarioCaps::forRun($run)` — **the same call the turn validator makes** (`ADR-0015`, KI-47)                                                                                                                                               |
| skill priorities    | `list<string>`                         | ordered, Move up / Move down buttons. No drag (WCAG 2.5.7)                                                                                                                                                                                                                   |

- **The client clamp is a convenience; the server is the authority.** `StoreBuildTargetRequest`
  already enforces `targets` as `array:Speed,…,Wit` (so a hand-made POST cannot store a sixth stat),
  requires all five by name, `min:0`, and adds `"{Stat} must be between 0 and {cap}."` per
  `ScenarioCaps::forRun`. The page's `max` attribute mirrors that cap and nothing more.
- **Error copy tied to inputs + focus to the first error** (`antislop-human`): every message renders
  with `:id` referenced by `aria-describedby` on its control, and on an error response focus moves to
  the first invalid control.
- **Bar showing distance to target** per stat, labelled with the number and the cap. No readiness
  band, no reachability verdict: whether a target *can* be reached in the turns left is held
  computation, so the bar is the entered number over the config cap and nothing more.
- **Human-readable summary** built only from entered values, badged **Calculated**. An empty field
  makes the summary name the absence rather than skip it.
- **Absent target:** `$run->buildTarget()` returns null (that null is load-bearing for the advisor's
  "no target" case), so the page names the absence and does not pre-fill five zeroes.
- **Write path reused verbatim:** `PUT /training-runs/{run}/build-target` (`runs.build-target.update`
  → `updateBuildTarget()` at `TrainingRunController.php:1582-1587`), which `$run->update([...])`s the
  payload and flashes "Build target saved.". `build_target` is in `#[Fillable]`
  (`TrainingRun.php:72`), and `payload()` reconstructs keys from validated input only.

### The two conflicts, challenged (doubt-driven-development)

**1. Purposes: C1 has 4 cases, the screen spec lists 6.** The spec's list is Story Clear, Competitive
Build, Champions Meeting, Parent Farming, Skill Farming, General Training. `BuildPurpose.php:10-16`
already rules on the overlap: "`StoryClear` is the general-training case the brief calls 'General
Training'" — so **General Training is not a missing case, it is the same case under another name**,
and the real gap is exactly one: **Competitive Build**. Options were: (a) add a case — forbidden
without owner approval and it would silently widen the advisor's contract; (b) render 6 and reject 1 —
a field that cannot save; (c) **render the 4 stored cases and disclose the gap.** Chosen: (c). The
select offers 4; the page states that the design target names a fifth purpose this tool does not
record. **Reported to the owner as an open question, not built.**

**2. Risk tolerance and per-skill Required/High/Optional/Ignore have no payload home.**
`BuildTargetPayload::KEYS` is exactly the six keys and `assertKeys` **refuses an unknown key**, so
adding either would mean changing the payload, the Form Request and the stored shape — a schema-shaped
change requiring owner approval. **Neither is built.** The skill-priority list is rendered as an
ordered list of names, which is all `skill_priorities` can hold (`string, max:255` per element, no
structured marks). Listed as open questions.

### Files

| File                                                      | Change                                                                      |
| --------------------------------------------------------- | --------------------------------------------------------------------------- |
| `resources/js/components/ProvenanceBadge.vue`             | new, the only glyph owner; exports `ProvenanceState`                        |
| `resources/js/pages/Career/BuildTarget.vue`               | new; `useForm` posting to the existing route                                |
| `app/Http/Controllers/Career/BuildTargetController.php`   | new: read-only `show` for the existing run                                  |
| `routes/web.php`                                          | one `GET /training-runs/{run}/target` (the PUT already exists)              |
| `lang/en/uma.php`                                         | add `build_purpose` labels (the debt `HasLabel` + the enum docblock name)   |
| `tests/Feature/CareerBuildTargetTest.php`                 | new: props, payload round-trip, cap clamp, glyph-uniqueness sweep           |
| `tests/browser/career-build-target.spec.ts`               | new: keyboard reorder, cap error text, badge name+text, 44px, 320px         |
| `SCREEN_SPEC.md`                                          | `SCR-CAR-005` row + §4 section with the state table                         |

No new package. No migration. No ADR.

### Open questions for the owner

1. **Competitive Build** — add a `BuildPurpose` case (widens the advisor contract), or leave it out?
2. **Risk tolerance** — needs a payload key, a rule and a consumer. Build it, or drop it from the target?
3. **Per-skill Required/High/Optional/Ignore** — needs `skill_priorities` to stop being a flat
   `list<string>`. That is a stored-shape change with C1's `assertKeys` behind it.
4. Should `Unknown` badges be *required* on every absent figure app-wide (which would let a test
   enforce "no bare N/A without a badge"), or is the `title` sufficient until D8?

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
7. **Energy records as a band, and the landed contracts take an int.** The client prints no Energy
   number (run report §1.7, §8.6) — bar fill, its colour change and the warning text are the whole
   readout — so D1's landed `energy => int|null` renders `N/A` in the normal case, and landed C2
   returns no band and no ranking whenever Energy is absent. Owner ruling owed: extend C2 to a band
   input, or accept the refusal state as the norm and the advisor as best-effort advice.
8. **Deferred, write-up-sourced ideas.** The 2026-10-06 UX-flow document built on the Rice Shower run
   report proposes, beyond what landed in §8.3: an opportunity-cost preview on a skill purchase (what
   the remaining SP can no longer cover — the closest to groundable, since it is SP arithmetic over
   recorded prices); a recommendation history with per-decision factor weights (needs a scoring model
   the plan drops, `ADR-0001` §3); a quick-update capture flow (+ Race / + Training / + Rest with
   inferred state); an endgame accent mode driven by turns-remaining. None has a task in this plan;
   the run report is the only grounding any of them has.

---

## 12. Accessibility conformance: WCAG 2.2 level AA

**Target: WCAG 2.2 level AA (all A and AA criteria) on every shipped screen**, the four ported Inertia
pages and the seven live Blade screens alike, verified by an axe pass in the browser specs plus the
manual checks in §12.5. This is a **regression bar**: no slice may reduce conformance, and each slice's
browser spec asserts the criteria its screen carries. The 2.2 target is chosen over 2.1 because 2.2 is
the current Recommendation and its additions (focus visibility, target size, redundant entry) are the
three the 0.1.0 screens most often miss.

### 12.1 What 2.2 adds over 2.1, and this app's disposition

| Criterion                                    | Level            | Disposition in Trainer Desk 2.0                                                                                                                                                                                                                                                                                                       |
| -------------------------------------------- | ---------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 2.4.11 Focus Not Obscured (Minimum)          | AA               | **Binding.** The sticky mobile bottom nav (`fixed inset-x-0 bottom-0`) and any sticky header must not cover the focused control. Fix: `scroll-padding-bottom` on the scroll container equal to the nav height, plus `scroll-margin` on focusables. Test: tab to the last control on a long page and assert it is not under the nav.   |
| 2.5.7 Dragging Movements                     | AA               | **Binding.** No drag-only interaction ships. Any reorder (deck, skill priority) offers a keyboard/button alternative, not drag.                                                                                                                                                                                                       |
| 2.5.8 Target Size (Minimum)                  | AA               | **Binding.** 24x24 CSS px minimum, satisfied by the existing 44px (`h-11`/`min-h-11`) targets. A smaller target is allowed only for inline text links or the equivalent-spacing exception, and the slice names it.                                                                                                                    |
| 3.2.6 Consistent Help                        | A                | **Binding.** The help affordance (the `title` tooltips and the Settings link) keeps the same relative order on every screen.                                                                                                                                                                                                          |
| 3.3.7 Redundant Entry                        | A                | **Binding.** The career setup wizard (D2 to D7) carries entered values forward; a step never re-asks for a value an earlier step already holds.                                                                                                                                                                                       |
| 3.3.8 Accessible Authentication (Minimum)    | AA               | **Not applicable.** No auth surface exists (`AGENTS.md` §1; PRD NFR-1). Recorded as N/A, not skipped silently.                                                                                                                                                                                                                        |
| 2.4.12 Focus Not Obscured (Enhanced)         | AAA              | Out of target. Met incidentally where the 2.4.11 fix fully reveals the control.                                                                                                                                                                                                                                                       |
| 2.4.13 Focus Appearance                      | AAA              | Out of target. The global `:focus-visible` (2px `--color-ring`, 2px offset) already exceeds the AA focus-visible requirement.                                                                                                                                                                                                         |
| 3.3.9 Accessible Authentication (Enhanced)   | AAA              | Out of target; N/A as above.                                                                                                                                                                                                                                                                                                          |
| 4.1.1 Parsing                                | removed in 2.2   | No action; valid HTML was always the requirement.                                                                                                                                                                                                                                                                                     |

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

axe-core run through Playwright at 0 violations for A + AA on each screen. `@axe-core/playwright` is
installed as a dev dependency and wired into `tests/browser/accessibility.spec.ts`; the shared fixture
scans pages against `wcag2a`, `wcag2aa`, and `wcag21aa`. Manual pass per screen: keyboard-only, 200% zoom,
320px reflow, reduced motion. The browser spec for each screen asserts the criteria it names.

---

## 13. Laws of UX applied

The 30 laws at lawsofux.com, mapped to concrete decisions. A slice cites the rows that govern its screen;
a screen that violates Hick's, Miller's, Jakob's, or Fitts's law, or that shows state through colour
alone, is not done.

| Law                            | Where it binds        | Concrete decision                                                                              |
| ------------------------------ | --------------------- | ---------------------------------------------------------------------------------------------- |
| Aesthetic-Usability Effect     | every screen          | Clean tokens make the tool feel reliable, but polish never substitutes for a sourced number.   |
| Choice Overload                | SCREEN-002, Cockpit   | Four scenario cards, not a matrix; the action grid stays short.                                |
| Chunking                       | Cockpit               | Three columns; the stat panel groups five stats and four meta values.                          |
| Cognitive Load                 | all data screens      | Progressive disclosure (design-2.0 §35): the card shows three facts, expand for modifiers.     |
| Doherty Threshold              | catalog reads         | Local-first, `Cache::remember`; skeletons only for a user-initiated async action.              |
| Fitts's Law                    | shell, Cockpit        | 44px targets, the primary action nearest the decision, mobile bottom nav.                      |
| Flow                           | Cockpit               | One screen carries the run; no modal stacks over a decision.                                   |
| Goal-Gradient Effect           | stat bands, targets   | Progress bars show distance to target, not just the current value.                             |
| Hick's Law                     | advisor rail          | One recommended action plus one alternative, not six equal buttons.                            |
| Jakob's Law                    | shell, forms          | Conventional sidebar, table, and form patterns.                                                |
| Law of Common Region           | cards                 | A card bounds one concept; the scenario panel is one region.                                   |
| Law of Proximity               | every figure          | A provenance badge sits next to the number it labels.                                          |
| Law of Pragnanz                | cards                 | One visual idea per card; no competing treatments.                                             |
| Law of Similarity              | spark chips           | Same meaning, same shape; all spark chips share a component.                                   |
| Law of Uniform Connectedness   | ancestry, timeline    | The six-node graph connects nodes with lines; the timeline is one rail.                        |
| Mental Model                   | whole app             | A planning desk, not a bot; record-only where computation is banned.                           |
| Miller's Law                   | nav, panels           | Nav stays at or under eight items; the stat panel stays at five plus four.                     |
| Occam's Razor                  | scenario panels       | Grand Concert shows a baseline strip, not an invented panel.                                   |
| Paradox of the Active User     | Dashboard             | No onboarding wall; the Dashboard is usable on first load.                                     |
| Pareto Principle               | advisor               | Surface the few facts that drive the decision: target deficit, energy, deadline.               |
| Parkinson's Law                | turn counter          | A bounded run with a deadline, not an open-ended dashboard.                                    |
| Peak-End Rule                  | SCREEN-019            | Career Result is a deliberate, calm summary.                                                   |
| Postel's Law                   | manual correction     | Accept loose input on correction; emit strict, provenance-labeled output.                      |
| Selective Attention            | Cockpit               | The recommendation card is the only accented element.                                          |
| Serial Position Effect         | action grid           | Primary action first, summary last.                                                            |
| Tesler's Law                   | advisor               | The engine absorbs the complexity; the player sees a band and a reason line.                   |
| Von Restorff Effect            | action grid           | One `RECOMMENDED` marker, not an accent on every card.                                         |
| Working Memory                 | setup wizard          | Never ask the player to recall a value from a prior screen; carry it (3.3.7).                  |
| Zeigarnik Effect               | targets, timeline     | Incomplete states are shown, not hidden, so progress stays visible.                            |

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

| Date         | Change                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 | Reason                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        |
| ------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 2026-10-05   | Filed. Derived from `design-2.0.md` + `screen-spec-2.0.md`; bound to real routes, controllers, models, `config/scenarios.php` and gates.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               | Owner asked for a comprehensive frontend development plan from the two 2.0 design docs, grounded in the repository's knowledge corpus.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        |
| 2026-10-05   | Added §12 (WCAG 2.2 AA conformance), §13 (Laws of UX rubric), §14 (Phase A0 remediation of the 0.1.0 screens); strengthened the Global Constraints accessibility bullet and the §10 definition of done.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                | Owner required the 2.0 UI and the in-tandem 0.1.0 UI to meet WCAG 2.2 AA and the UX laws, not just carry the old Blade behavior forward.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| 2026-10-05   | §1 inventory rewritten from the tree (12 pages, 7 components, 8 specs, 26 Blade files). Phase A status block added under §4. A1, A3, A4a and A4c recorded as landed with their deviations; A4b re-sized in §5.5.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       | The inventory had stopped tracking the ports after the first four pages, so the plan read as if A1–A4c were unbuilt. §5.5's A4b row also over-stated the work by assuming a Vue twin per Blade component; the read shows one consumer each.                                                                                                                                                                                                                                                                                                                                                                                                                   |
| 2026-10-05   | A4b recorded as landed in §4 and §5.5, B1 unblocked in §6 with its measured zero-consumer set, §1 re-measured (13 pages, 25 components, 9 specs, 15 Blade files), and the three Phase-A checklist rows closed.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         | The slice landed with a 19-component port, ~24 migrated test files and two behaviour changes that needed writing down where the next slice will read them, not only in a commit message.                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| 2026-10-05   | §4's status block renamed to Phase A–C: B1 recorded as Landed with the eleven Blade files it deleted and the three its measured set had missed, and C1, C2 and C3 recorded as Landed with the shapes that shipped. §6 gains the matching landed note.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  | The block still said B1 was Ready and that no Phase B–E slice had begun, neither of which held once B1 and the Phase C slices committed. §4 is where the next slice reads to decide what to start, so it could not stay a step behind.                                                                                                                                                                                                                                                                                                                                                                                                                        |
| 2026-10-06   | §8 gains its first two bite-sized Phase D slice plans as §8.1 (D1, the Dashboard enrichment: files, props contract, eight decisions, tests, three open questions) and §8.2 (D2, Scenario Selection and `SetupLayout`: the wizard contract D3–D7 inherit, the session-draft persistence decision with its rejected alternatives and its stated two-tab ceiling, the config-derived card table, files). §8's D1 row is amended to match §8.1 decision 5, which drops the legacy-goal gaps panel.                                                                                                                                                                                                                                                                                                                                                                                         | Both plans existed only as untracked `.scratch-uma/` working files while their slices were in flight in the tree; §8 promises "a fresh bite-sized plan per later slice", so the plan document is where they belong and the scratch copies are now duplicates. Embedded verbatim with headings demoted one level; nothing in either plan's body was edited, and each plan's title line was re-titled and re-levelled into the §8.1 / §8.2 headings. Their `SCREEN_SPEC.md` `SCR-CAR-*` rows and the two `tests/browser/` specs are the slices' own deliverables and are not restated here.                                                                     |
| 2026-10-06   | §8 gains §8.4 (D3, Trainee Selection and Trainee Profile: the wizard contract as shipped, the measured eight-filter roster table with the two omissions, the sixteen-row profile sections table, reuse, files, four reports) and §8.5 (D4, Build Target and `ProvenanceBadge`: the four-state badge contract, the BuildTarget field table, the two challenged conflicts, four open questions). Both originals deleted from `.scratch-uma/` after verification.                                                                                                                                                                                                                                                                                                                                                                                                                         | A scan for unconsolidated `.scratch-uma` markdown on 2026-10-06 found exactly these two; the owner ruled them folded here, the same treatment as §8.1 and §8.2. Both describe slices that landed, so they are historical records of what shipped, embedded verbatim with headings demoted one level and their title lines re-titled into the §8.4 / §8.5 headings. Numbered 8.4 and 8.5 rather than 8.3 and 8.4 because a concurrent pass had already claimed §8.3 (the run-informed acceptance section above them) in this same working tree.                                                                                                                |
| 2026-10-06   | §4's status block renamed from Phase A–C to Phase A–D and its table extended with D1, D2, D3, D4, D5, D6, the D5/D6 wizard halves and a Not begun row for D8–D18 and E1–E6, each with its commit and its open defect. New §4.1 carries the open work as an ordered queue: five defects, three owner decisions, the environment gate, and the toolchain and focus rules every later slice inherits. §8's D5 and D6 rows describe run-scoped screens that landed in `05e9584` ahead of their slice numbers, so the table records the fact rather than pretending the order held.                                                                                                                                                                                                                                                                                                         | The plan still read "No Phase D or E slice has begun" while four Phase D slices sat in `master`. §4 is where the next slice decides what to start, so a stale block silently sends someone to rebuild landed work; that has already happened three times this pass, with D5 and D6 dispatched as if unbuilt. The red state of the D5/D6 wizard halves is written down instead of smoothed over, because the tree at `0006b17` has three failing tests and three PHPStan errors and no `KNOWN-ISSUES.md` entry yet. §1's file counts were deliberately not re-measured in this pass and are stale; `AGENTS.md` §6 carries the current read as of 2026-10-06.   |
| 2026-10-06   | §3's per-training-yield row updated to record-as-observed (the client prints the preview and failure % on the tile) and five rows added from the recorded Rice Shower Unity Cup run (`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md`): Energy as band-only, the turn-counter discrepancy, the burst reward tiers with the live Extreme-counting disagreement, the +30 Team Rank bonus as Estimated, and the client-read hint discount ladder. New §8.3 makes that run the canonical acceptance seed for D7–D16 and E3, with per-slice deltas (D6 catalog-max vs client-current and the padlock rule, D9 record-as-observed, D10 condition-skill applicability, D13 the sourced ladder and the prerequisite, D14 the client counter wins, E3 the Team Info spine and the five-category finals). §11 gains the Energy-band contract question and a parked list of write-up-sourced ideas.   | The owner asked for plan updates grounded in the run report; it closes rows the plan previously marked silent, and its live disagreements (burst counting, turn counter) must render as uncertainty rather than resolve silently.                                                                                                                                                                                                                                                                                                                                                                                                                             |
