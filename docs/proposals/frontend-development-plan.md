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

_Dated close-out 2026-10-08 (documentation-sync pass; the table above is preserved as the read it was
taken on, 2026-10-05): that read is superseded in both directions. The counts it states — 13 page
components, 25 shared components, 9 browser specs, 15 Blade files still in `resources/views/` — are
stale for this tree, which now holds 54 page files under `resources/js/pages/` (the 2.0 career set,
the Veterans set and the Database set in addition to the ported 0.1.0 pages), 58 single-file Vue
components under `resources/js/components/`, 44 browser specs under `tests/browser/`, and no
`resources/views/components/` at all: slice B1 deleted that directory, and `resources/views/` now
holds `app.blade.php` (the Inertia root) and `errors/{404,419,500}.blade.php` only, which the "Still
on Blade" row's own B1-pending reading was awaiting. The "B1 pending" state is closed: the deletion
landed with slice B1 and the `resources/views/components/` entry the row names is gone, so the row's
last column is false on this tree in the same way the "D16–E6 Not begun" reading is false in §4._

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
| **D**   | D13     | Skills Planner _(design-2.0 only)_                                          | SCR-013              | D8                       |
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
| **E**   | E6      | Grand Concert panel — baseline strip only _(design-2.0 only)_               | SCR-017              | E1                       |

**Phase A–D status, 2026-10-07.** A1, A2, A3, A4a, A4b and A4c landed green, B1 landed on their back,
and Phase C's three slices landed frontend-agnostic per `ADR-0020` §3. Phase D has begun: D1 landed
green, D2 to D4 landed green, and D5 and D6 landed run-scoped in `05e9584` before their own slice
numbers were reached. The wizard halves of D5 and D6 — steps 4 and 5 — are in the tree but **not
green**. D7 landed green. D8, D9, D10 and D12 landed green. D11 has not begun. No Phase E slice
has begun.
_Dated correction 2026-10-08 (documentation-sync pass): the "D7 landed green" clause of this paragraph
is the one this pass keeps live. D7 is registered at `routes/web.php:91-92`
(`career.preflight`, `career.preflight.store`), and the six-step wizard is what the `SCR-CAR-002`
status cell records as "all six live". Everything after "D11 has not begun" is superseded by the
close-out note appended under the "Dated status, 2026-10-07" paragraph above._

**Dated status, 2026-10-07 (later the same day; the paragraph above is preserved as written).**
D11, D14 and D15 have landed green since this read. **D11** was built by a concurrent session
(`EventDecisionController`, `Career/EventDecision.vue`, `runs.events.*`) and was verified rather
than rebuilt. **D14** is `App\Http\Controllers\Career\TimelineController` plus
`resources/js/pages/Career/Timeline.vue` and `components/career/CareerTimeline.vue`
(`SCR-CAR-017`); its first landing left `runs.timeline` reachable by URL only, and **D15 closed
that defect** by adding the Cockpit left column's door. **D15** is
`App\Http\Controllers\Career\ResultController` plus `resources/js/pages/Career/Result.vue`
(`SCR-CAR-018`), reached from the run record screen's header. Three rulings rode with D15 and
belong to later slices: the Screen-019 brief's "build quality" section is **omitted as a ruling**
(see `SCREEN_SPEC.md` SCR-CAR-018), Save Veteran stays a named absence until D16, and
`TrainerAdvisor::deficits()` widened from private to public so the Result screen reads the
advisor's own subtraction rather than a second copy of it (`ADR-0015`). D13 landed green earlier
the same day. **D16** (Save Veteran, `SCR-VET-003`), **D17** (Database hub, `SCR-SYS-005`) and
**D18** (the four-category Settings shell over the ported Preferences) have since landed in the
working tree as untracked files. **E1** (`SCR-CAR-019`, the scenario panel shell) landed 2026-10-07
with its browser gate green at 8 passed, and **E2** (`SCR-CAR-020`, the URA panel) is the first
renderer registered into it, drawn from the `career_goals` flag rather than a scenario name. **E3** has
a `components/scenario/UnityCupPanel.vue` sitting in the working tree from a concurrent session, which
this pass neither read nor claims; **E5–E6 remain Not begun.** _Dated correction 2026-10-07 (E4):
"E4 remain Not begun" is superseded — E4 (`SCR-CAR-022`, the Trackblazer panel) landed this session
with props tests 10 passed and browser 10 passed. E5–E6 are unbuilt._

- *Dated close-out 2026-10-08 (documentation-sync pass; the rows above are preserved as written): the
**"D16, D17, D18 and every Phase E slice remain Not begun"** reading is false on this tree. Every slice in
the §4 sequence has since landed, in order: **D16** (Save Veteran, `SCR-VET-003`; the read half of the
library and the write half are both in the tree, with `runs.veteran` / `runs.veteran.store` wired in
`routes/web.php:232-240`), **D17** (the Database hub, `SCR-SYS-005/006/007`, over the `database.*` routes
`routes/web.php:269-279`), **D18** (the four-category Settings shell over the ported Preferences,
`PreferenceController` + `Preferences/Edit.vue`'s tablist), **E1** (the scenario panel shell,
`SCR-CAR-019`), **E2** (the URA panel, `SCR-CAR-020`, the first `career_goals` registration in
`resources/js/components/scenario/ScenarioPanel.vue:55`), **E3** (the Unity Cup panel, `SCR-CAR-021`,
registering `team_race` / `team_rank_ladder` at `:59-60`), **E4** (the Trackblazer panel, `SCR-CAR-022`,
registering `grade_objectives` / `shop` / `epithet_routes` at `:63-65`), **E5** (the Scenario Race
Planner, `SCR-CAR-023`, its own page and `runs.races.planner` at `routes/web.php:133`) and **E6** (the
Grand Concert baseline strip, `SCR-CAR-024`, the branch the panel shell draws where every flag is off).
The gate claims below for E1, E2, E4, E5 and E6 were made by the sessions that built those slices on
their own trees; this pass did not re-run `npm run test:browser`, and it says so rather than treating a
green recorded elsewhere on another day as a green on this tree. The `D7 ... Not begun` row and the
"`E5–E6 remain Not begun`" clause are the two live false statements this pass supersedes; both are
corrected in place under their own rows with a dated note, the way the D11 close-out corrected the D11
row, so the original text stays the record of the read it was taken on._

The table below is the read at `0006b17`. Where a slice carries an open defect, the defect is in the
third column rather than in a commit message, because §4 is where the next slice decides what to start.

**Tree state at hand-off, 2026-10-08 (measured, so a fresh session does not rediscover it).** `HEAD` is
`2ffe521`, `master` is **9 ahead of `origin/master`** and unpushed — the O-1 push gate is still the owner's
to lift, and `7b04b6c` is the last commit that moved the tree rather than the register. Both this slice and
the sibling (Database reference views) are uncommitted: the whole of each is in the working tree, with
`routes/web.php` carrying the two `preferences.*` routes as a HEAD-version patch and nothing else from
either slice staged. The dev database holds **12 `training_runs`** from other sessions' leaks and **2
`preferences`** rows (`failure_estimate = off`, plus a `settings` key whose value is a JSON blob) — there is
no `settings` _table_, so a query against it errors rather than returning 0. `storage/app/backups/` is empty.
Port 8127 remains wedged at PIDs `30320`/`30528`; per KI-73 its owner reaps it and this slice did not kill
either listener. `public/hot` points at `[::1]:5174` with nothing answering (KI-76); the clean browser-harness
state is **no `public/hot` file**, with `public/hot.save` present as a moved-aside copy from an earlier
session. `app/Http/Controllers/PreferenceController.php` carries a peer's D18b work (138 of its 185 lines,
uncommitted) and is not part of either patch. KI-75, KI-76, KI-77 and KI-78 are all open.

| Slice               | State                                                         | Evidence, and what is still open                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| ------------------- | ------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| A1                  | Landed                                                        | One clause of its browser case is unreachable, not unfinished: the `JapanOnly` notice. See §5.2.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| A2                  | Landed                                                        | Filter button was found at 32px and raised to `h-11` during the port. See §5.3.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| A3                  | Landed                                                        | `resources/js/pages/SupportCards/{Index,Show}.vue`; `rarity-chip`/`skill-row` Blade retired.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| A4a                 | Landed                                                        | No-script fallback select retired by owner ruling 2026-10-05; 11 source-text shape pins became Playwright behaviour proofs. See §5.5.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                |
| A4b                 | Landed                                                        | `resources/js/pages/Runs/Show.vue` plus 19 ported panel components; `runs/show.blade.php` and its ten now-consumerless components deleted. Deviations and the two behaviour changes under the 2026-10-05 ruling are in §5.5.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| A4c                 | Landed                                                        | Preview kept as a server-rendered Inertia page, not a JSON endpoint (it never was one). See §5.5.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
| B1                  | Landed                                                        | `resources/views/components/` is now empty: the shell plus `app-button`, `capsule-header`, `deck-editor`, `energy-gauge`, `grade-badge`, `grade-point-meter`, `guided-step`, `race-calendar`, `resource-strip` and `run-header` are gone, with `resources/js/app.ts` and `guided-flow.ts` and the Vite input that named the first. The three error documents render themselves and `AppServiceProvider` composes them in place of the shell, `errors.500` staying out because it draws itself with no database. §6's measured set named eight components and missed `capsule-header`, `energy-gauge` and the shell itself.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           |
| C1                  | Landed                                                        | `build_target` json column, `App\Models\Advisor\BuildTargetPayload`, `StoreBuildTargetRequest` validating the five targets against `ScenarioCaps::forRun`.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           |
| C2                  | Landed                                                        | `app/Services/Advisor/TrainerAdvisor.php` plus `config/advisor.php`; the held fields, score and numeric confidence and per-training yield, stayed out.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               |
| C3                  | Landed                                                        | `veterans` table, `Veteran`, and `RecordVeteran`/`ListVeterans`/`ShowVeteran`, record-only. The plan's "rating" filter is not built: no source records a rating, and inventing one is the computation this slice forbids.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| D1                  | Landed                                                        | Commit `4b79346`. Dashboard enriched: active-career card with career position on the client's 24-turn grid, quick actions, recent Veterans capped at three, and the `GLOBAL DATA ● Current` badge. Recent Builds and the legacy-goal gaps are named absences, per §8.1 decision 5. `tests/browser/dashboard.spec.ts` (6 cases) and `DashboardTest` (7 cases) green; PHPStan and both lore gates at baseline.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| D2                  | Landed                                                        | Commit `17b793a`. `SetupLayout` plus `Career/ScenarioSelect.vue`; four cards derived wholly from `config/scenarios.php` by subtraction against the baseline entry, so a fifth scenario is one config line and no component edit, which `CareerScenarioSelectTest` proves by injecting a scenario at runtime. Persistence is `App\Services\Career\SetupDraft` on the session, per §8.2; the rejected early-`Active`-run alternative is recorded there with the phantom-career reason.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| D3                  | Landed                                                        | Commit `17b793a`. `Career/TraineeSelect.vue`, `Career/TraineeProfile.vue`, `AptitudeBadge.vue`. Five of the brief's eight filters are built; **growth rate and scenario suitability are omitted because no column or table holds either**, and the profile's career-goals section is omitted because `trainee_goals` does not exist (KI-34), as are hint skills and `skills_evo`, each for a cited reason. `SCR-CAR-003` and `SCR-CAR-004` are in `SCREEN_SPEC.md`.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| D4                  | Landed                                                        | Commit `17b793a`. `ProvenanceBadge.vue` is the only owner of the four §49 glyphs, and `Career/BuildTarget.vue` is headed "Your target". The clamp reuses `StoreBuildTargetRequest` rather than restating it. **Open: `tests/browser/career-build-target.spec.ts` exists but has never been run by anyone**, so D4's browser evidence is absent. Two §8 rows are unbuilt by ruling, not by oversight: the purpose select offers `BuildPurpose`'s four cases, so the spec's "Competitive Build" has no option, and neither risk tolerance nor per-skill `Required/High/Optional/Ignore` exists because `BuildTargetPayload::KEYS` rejects an unknown key. Both need an owner decision.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| D5                  | Landed (run-scoped)                                           | Commit `05e9584`, before its slice number was reached. `pages/Legacy/{Index,Builder,Compare}.vue` over `ListVeterans`, `RecordVeteran` and `LegacySelectionPayload`, record-only, with the six-node graph as nested list items and `border-l` connectors. Spark probability renders `N/A` with the reason. Its browser spec is red for the reasons in the row below.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| D6                  | Landed (run-scoped)                                           | Commit `05e9584`, before its slice number was reached. `pages/Support/Builder.vue` over `runs.deck.sync`, with `components/support/{SupportSlot,SupportTypeMark,DeckAnalysis}.vue`; six slots, seven types, Scenario Link derived and never stored, `DeckAnalysis` limited to stated `SupportCardEffects` anchors with no score. **Open: 7 failures across `legacy.spec.ts` and `support-deck.spec.ts`.** Four are selector bugs — `toHaveText` reads `textContent` including an `aria-hidden` glyph, and one locator resolves to 25 elements because the picker renders one equip button per card. Two need investigation before any assertion is touched: the text-only picker rows, and a click timeout. `AGENTS.md` §18 warns that a never-run `uma:fetch-art` mirror looks exactly like the first of those.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| D5/D6 wizard halves | **Landed un-green**                                           | Commit `0006b17`. Steps 4 and 5 (`Career/LegacySelect.vue`, `Career/DeckSelect.vue`) carry ancestry and deck in the session draft, because both landed screens are run-scoped and the run is not created until D7. Shared bodies were extracted into `App\Services\Legacy\AncestryGraph` and the deck path rather than duplicated. Committed on the owner's instruction while red so the work is not stranded. **Three failures and three PHPStan errors, no browser spec, and no `SCR-CAR-*` rows.** The failures share one cause: the step-4 write saves `legacy_selection` but never sets `inheritance_parent_a_id` / `inheritance_parent_b_id`, which `LegacyController::update()` writes in the same statement because `ADR-0010` keeps those columns as the parent's identity. Reuse that resolution. A `KNOWN-ISSUES.md` entry is owed for landing red.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
| D7                  | Landed (un-green)                                             | Working tree, untracked at the 2026-10-07 read; its gate run is recorded in the D7 hand-off at `docs/research-scratch/SLICE-RECORDS.md` §"D7 / SCREEN-008 (Preflight)". `Career/Preflight.vue`, `PreflightController`, `Career/StartCareerRequest`, `SCR-CAR-010`. Composes the five entered steps into one read-only contract and creates the run exactly once; `career.preflight` is wired at `routes/web.php:91`. _Dated close-out 2026-10-08:_ the "Not begun" status the row carried is the defect this close-out supersedes; the row above is preserved as written, and its blocker reading is false on this tree.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| D8                  | Landed                                                        | Commit `e8f75bd`. `CareerLayout`, `Career/Cockpit.vue`, `components/career/*`. Cockpit reads C2's advisor and renders the seven-entry action grid. `tests/browser/career-cockpit.spec.ts` (7 cases) green.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           |
| D9                  | Landed                                                        | Commit `55be0cd` (part). `Career/TrainingDetail.vue`, `components/career/TrainingCard.vue`. Five training option cards with sourced costs, deficit calculation, `RiskNotMeasured` for Wit, energy-after range. Preview round trip through `runs.turns.store`. `CareerTrainingDetailTest` (10 cases) and `career-training-detail.spec.ts` (6 cases) green.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            |
| D10                 | Landed                                                        | Commit `55be0cd` (part). `Career/RaceDecision.vue`, `components/career/RaceCard.vue`. Race catalogue facts, mandatory races, readiness `N/A` with `title` citing `ADR-0016`, no percentage. `CareerRaceDecisionTest` (the four empty states, readiness refusal, mandatory list, no-percentage assertion) and `career-race-decision.spec.ts` green.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
| D11                 | Landed                                                        | _Dated close-out 2026-10-08:_ the "Not begun" status this row carried is the defect the close-out above names; the row above is preserved as written, and its blocker reading is false on this tree. `Career/EventDecision.vue`, `EventDecisionController`, `career/EventCard.vue`, `runs.events.*` wired at `routes/web.php:158-159`; `SCR-CAR-014`. Record-only: known outcomes are derived from this run's own recorded choices, the advisor refuses with its reason, and a choice without a recorded outcome carries the incomplete warning. `CareerEventDecisionTest` and `career-event-decision.spec.ts` gate it.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              |
| D12                 | Landed                                                        | Commit in working tree. `Career/InheritanceEvent.vue`, `InheritanceEventController`. Predicted section: parent/grandparent Sparks from `legacy_selection`, star-roll odds table (REFERENCE §1.5.3), "expected inheritance" = `N/A` citing `ADR-0020` §3. Observed section: Inheritance-type TurnEvents recorded via existing mechanism. Milestone timeline (turns 1, 31, 55). `CareerInheritanceEventTest` (12 cases, 276 assertions) and `career-inheritance-event.spec.ts` green. All gates passed. _Dated note 2026-10-07 (D14a session, on the owner's close-out): two things this row leaves unsaid. First, ordering: D12 landed ahead of D11, the same out-of-order shape the D5/D6 rows record, so the row above ("D11 Not begun") is not evidence that D11 was skipped. Second, the tree already holds D11's halves (`Career/EventDecision.vue`, `EventDecisionController`, `career-event-decision.spec.ts`, `CareerEventDecisionTest.php`, and `SCREEN_SPEC.md` SCR-CAR-014 with its own test counts), so D11's status is for its own hand-off to claim, not this note. Close-out review also filed `KNOWN-ISSUES.md` KI-68 against this slice's write boundary (`InheritanceEventController.php:83` validates inline, which the Floor bans) and recorded that no owner ruling names the `TurnEventType::Inheritance` case._                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                |
| D14a                | Landed                                                        | Interim slice before D14, briefed 2026-10-07. `components/career/RunRaceStrip.vue` replaces the reused `RaceCalendar` in the Cockpit's left column with three run-scoped regions: this run's recorded races, the turn being decided, and the mandatory races still to come. `RaceCalendar.vue` is unchanged and stays `runs.show`'s component. The brief's single `this_turn` object became a count, because the seed catalogue holds eleven rows at Senior turn 19; its per-row `goal` flag became one named absence, because no column records per-trainee goals (KI-34). Tests: `RunRaceStripTest` (10 cases) and `career-race-strip.spec.ts` (6 cases). D14 still owns this column's future. _Note 2026-10-07, after D14: `TimelineController`, `Career/Timeline.vue`, `components/career/CareerTimeline.vue`, `CareerTimelineTest` and `career-timeline.spec.ts` are in the tree as `SCR-CAR-017`, and D14 mounted its timeline on its own page rather than the Cockpit column, so the strip is what the Cockpit shows until a ruling changes it. D14's own status row is its hand-off's to claim._                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| D13                 | Landed                                                        | Working tree (untracked). `Career/SkillsPlanner.vue`, `SkillsPlannerController`. `SCR-CAR-016`. `CareerSkillsPlannerTest` (11 cases) and `career-skills-planner.spec.ts` green.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| D14                 | Landed                                                        | Working tree (untracked). `Career/Timeline.vue`, `TimelineController`, `components/career/CareerTimeline.vue`. `SCR-CAR-017`. `CareerTimelineTest` (4 cases) and `career-timeline.spec.ts` (5 cases) green. Its first landing left `runs.timeline` URL-reachable only; D15 closed that by adding the Cockpit left column's door.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| D15                 | Landed                                                        | Working tree (untracked). `Career/Result.vue`, `ResultController`. `SCR-CAR-018`. `TrainerAdvisor::deficits()` widened from private to public so this screen reads the advisor's own subtraction rather than a second copy of it (`ADR-0015`). "Build quality" omitted as a ruling. `CareerResultTest` (8 cases) and `career-result.spec.ts` (5 cases) green.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        |
| D16                 | Landed                                                        | Working tree (untracked). `VeteranController`, `SaveVeteranController`, `Veterans/{Index,Show,Compare}.vue`, `Career/SaveVeteran.vue`. `SCR-VET-001`–`SCR-VET-004`. The first caller of `RecordVeteran`; read half (Index/Show) and write half (Save Veteran) are both in the tree.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| D17                 | Landed                                                        | Working tree (untracked). `DatabaseController`, `Database/Database.vue`. `SCR-SYS-005`. Three destinations redirect to existing catalog surfaces; two are new (Races from `RaceCatalogSlot`, Scenarios from `config/scenarios.php`). _Dated close-out 2026-10-08: SCREEN-023 lists eight areas and this landed five, so the hub's "Not in this build" section naming Events, Shop Items and Sparks is deleted, not softened, and the three become `SCR-SYS-008/009/010` over `database.events`, `database.shopItems` and `database.sparks`. The rows are transcribed into `config/reference.php` with a `source` and a `recheck` per table, read from `docs/UMAMUSUME_REFERENCE.md` §4.4, §1.6.10, §1.5.2 and §1.5.3/§1.5.4; nothing is ingested and no source is allowlisted. The Shop Items view re-uses the scenario matrix's own 19-item Pro Shop block rather than retyping it. `DatabaseTest` **11 passed (220 assertions)**; `database.spec.ts` **12 passed (2.3m)**; `npm run typecheck` and `npm run build` exit 0; PHPStan `[OK] No errors` on its two PHP files; lore-code 124 hits tree-wide with **0 in this slice's files**; full suite **1586 passed, 2 skipped (27,273 assertions)**, exit 0. Slice plan `docs/research-scratch/PLANS-AND-BRIEFS.md` §"Database reference views"._                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
| D18                 | Landed                                                        | Working tree (untracked). `Preferences/Edit.vue` extended into a four-category tablist; `PreferenceController` gains `verifiedAt` and `importUrl` props; `AppLayout.vue`'s flash gains `role="status"` (`aria-live` dropped as redundant). `PreferenceControlsTest` and `preferences.spec.ts` extended. **Blocked, then unblocked; final state verified by rebuild + browser spec.**                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| E1                  | **Landed**                                                    | Working tree (untracked). `components/scenario/{ScenarioPanel,ResourceMeter,ScenarioStatusBadge,AlertRow,WidgetFallback}.vue` and `registry.ts`; `types.ts`'s `ScenarioPanelSection`; `CockpitController::scenarioSection()`; `SCR-CAR-019`. `ScenarioPanelTest` **5 passed / 123 assertions**; `npm run build` **exits 0**; `scenario-panel.spec.ts` **8 passed (3.5m)** against a scratch-DB server on port 8161; PHPStan clean on its files; Pint clean; `npm run typecheck` clean; zero lore hits. **The gate run found one real defect, and it belonged to D14 rather than to E1:** the `Career Timeline` door E1's sibling slice added to the Cockpit (`Cockpit.vue:327`) was an inline link measuring 32px with no `min-h-11`, so E1's page-wide sweep failed the first run (`control 11 is not sized to the 44px contract`, received 32). Hoisted to the standalone `inline-flex min-h-11` pattern the sibling `Go to the run record screen` link already uses; the re-probe reports 0 controls under 44px on the Unity Cup cockpit, and the fix is what let the sweep pass. The earlier blocker (D18's uncommitted `Edit.vue:81`) cleared before this run. **Blocked, then unblocked; final state verified by rebuild + browser spec.** No author is recorded for the intermediate state: four reports gave four incompatible stories for one character, and the authorship of the intermediate version is not knowable from the current tree. The plain facts that matter are that the file now compiles, the build succeeds (955 modules), and the tablist renders in this spec's output. The gate closed inside the session, so per the recorded criterion no `KI-nn` entry was owed: a browser gate owed past 24 hours earns one, a gate owed within the session does not. That sweep failure is the defect `KNOWN-ISSUES.md` files as **KI-70 (the 44px entry)**, and this change closes it with the `min-h-11` hoist the entry itself prescribes; the register carries two entries numbered KI-70, which is a numbering collision rather than the same defect. **Cross-slice note for the E1 session:** E1's `AlertRow` gained a critical tone; its warning tone was deleted by E2 as dead code (the token never existed in `resources/css/app.css`). No renderer is registered: every widget resolves to `ResourceMeter`, every ON panel flag to `WidgetFallback`. Two fallback arms therefore have no rendered case — the registered-renderer arm lands with E3's first `register()` call, and the unlabelled-widget arm is unreachable while the matrix labels every declared key. _Dated correction 2026-10-07 (E2): that sentence held until E2 registered `career_goals` against `UraPanel.vue`, so the registered-renderer arm now has a rendered case and is no longer owed; the unlabelled-widget arm is still unreachable._ |
| E2                  | **Landed**                                                    | Working tree (untracked). `components/scenario/UraPanel.vue`; `AlertRow.vue` tone change; `ScenarioPanel.vue` first `register()` call; `CockpitController::careerGoalSections()` and `happyMeekRows()`; `config/scenarios.php` one `panel_labels` row plus the `ura_finale` flag; `SCR-CAR-020`. `UraPanelTest` **7 passed / 125 assertions**; `ura-panel.spec.ts` **10 passed (6.2m)** against a scratch-DB server (`VACUUM INTO` copy, port 8175, `SESSION_DRIVER=file`); `scenario-panel.spec.ts` re-run as the `AlertRow` regression at **8 passed**, and both files together **18 passed (2.9m)**; full suite **1530 passed, 2 skipped (26,446 assertions)**, exit 0; `npm run typecheck` and `npm run build` exit 0; Pint clean on its files; PHPStan `[OK] No errors`. Slice plan §9.2. **Three of SCREEN-014's five components have no data**, ruled absent by the owner: no `trainee_goals` table, no Happy Meek level column, no sourced reward. They render as named absences with `N/A` plus the reason, visible and in a `title`. The mandatory race set is the one sourced module, read through the existing `RaceCatalogSlot::isAtOrBeforeTurn()` owner, never re-derived. The alert says "due and not recorded"; no window length is invented. Deviations found by the gate itself: `AlertRow` carried a `warning` tone mapped to `text-warning`, a token that has never existed in `resources/css/app.css`, so Tailwind emitted nothing for it, and it is replaced rather than left dead; and the spec's first three failures were the spec's own wrong assumptions (a stale CSRF token reused across eleven writes, a nested-`li` filter that resolved to three elements, and one no-turn finale row assumed where the calendar holds three).                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| E3                  | **Landed**                                                    | Working tree (untracked, this session). `components/scenario/{UnityCupPanel,RaceCalendarNote}.vue`; three `register()` calls beside E2's on `ScenarioPanel.vue` (the home E2 chose; an earlier home on `Cockpit.vue` was converged into it rather than kept as a second one); `CockpitController::teamSection()` plus the uniform `team` key on `scenarioSection()`; `config/scenarios.php` `unity_cup.spirit_burst_bands` + `burst_payout_timing`; `types.ts`'s `ScenarioTeamSection`; `SCR-CAR-021`. `UnityCupPanelTest` **7 cases**; targeted **20 passed (583 assertions)** across `UnityCupPanelTest`/`ScenarioPanelTest`/`CareerCockpitTest`; `unity-cup-panel.spec.ts` **7 passed** and `scenario-panel.spec.ts` **8 passed** (15 together, 4.2m) against a `VACUUM INTO` scratch copy on port 8166 with `SESSION_DRIVER=file`; `npm run typecheck` and `npm run build` exit 0; Pint run scoped to this slice's files after a concurrent session's half-written test file blocked the whole-suite load, then `--dirty` clean across the tree; PHPStan **`[OK] No errors`** tree-wide at hand-off. `career-cockpit.spec.ts:107` went red on this slice's account and is fixed here: `getByText('RECOMMENDED')` is a case-insensitive substring, so it matched the new panel's absence sentence "Recommended timing and projected benefit are not built", not the grid marker; the assertion is scoped to the Actions region the way the ranked case in the same file already scopes itself, and the file re-runs **7 passed (4.7m)**. A probe (created, run and deleted in this session) is the evidence: `recommendedKeys: []`, the advisor's refusal printed, and the only matching node was the panel's prose. lore-code 115 hits, none in this slice's files (the tree rose from the 97 baseline through peers' files). Slice plan at this file's §9.1. The doubt review's three accepted findings are in the artifact: the +30 bonus renders only beside Rank S (the only rank the client frame shows it at), the band table carries an `Estimated` badge plus one warning line naming all three publisher conflicts (§7.4 SP figures and payout month, §8.2 the counting rule), and the readiness sentence states the bands are thresholds with no recorded Spirit to set against them. Two spec defects of this slice's own were fixed before landing: an exact-text ladder assertion the gauge's `G lv 1` rung could never satisfy, and a keyboard case that asserted nothing. Deviations recorded: E1's fallback DOM case is rewritten to the property that still holds (`cannot draw yet` count 0) because E2's, E3's and E4's registrations left no Global scenario an unclaimed flag; E1's meter locator is retargeted to the accessible name because the Team Panel legitimately prints a second "Team Rank" heading.             |
| E4–E6               | E4 Landed, E5–E6 Landed                                       | **E4 Landed 2026-10-07** (this session, working tree untracked). `components/scenario/TrackblazerPanel.vue`; three `register()` calls (`grade_objectives`, `shop`, `epithet_routes`) on `ScenarioPanel.vue`; `CockpitController::scenarioSection()` gains the five uniform-shape nullable keys (`grade`, `shop`, `epithet`, `rival`, `finale_official_title_absence`) and five section builders; `types.ts`'s `Trackblazer{Grade,Shop,Epithet}Section`; `Cockpit.vue`'s action-area Shop jump (`hasShop` + `jumpToShop` to `#trackblazer-shop-heading`, WCAG 2.4.11); `SCREEN_SPEC.md` `SCR-CAR-022`. `TrackblazerPanelTest` **10 passed / 127 assertions**; `trackblazer-panel.spec.ts` **10 passed (6.0m)** against the shared `:8127` server; `scenario-panel.spec.ts` + `ura-panel.spec.ts` + `unity-cup-panel.spec.ts` re-run green at **29 passed / 581 assertions** scoped to the E1/E2/E3/E4 surface; full Pest suite **1541 passed / 2 skipped (26600 assertions)**; Pint clean; `vendor/bin/phpstan analyse app/Http/Controllers/Career/CockpitController.php app/Http/Requests/StoreShopPurchaseRequest.php app/Models/TrainingRun.php --no-progress --memory-limit=1G` **[OK] No errors**; `npm run typecheck` exit 0; `npm run build` exit 0 with `Cockpit-D-WiUHWv.js` 53.65 kB; `composer lore` + `composer lore-code` clean on E4 files. Slice plan `docs/research-scratch/PLANS-AND-BRIEFS.md` §"E4 — Trackblazer scenario panel". Two layout decisions the panel makes and the props tests hold: the rival list caps at eight rows (Miller's Law, plan §13; the Junior-Year seed's 50+ entries would push the page over WCAG 1.4.10 reflow at 320px) and the grade bar renders only when a period is reported (design-2.0 §49 absent-is-text-only, the same trade-off E3 made for Spirit). Two cases the browser spec found and tightened rather than dropped: a tampered item value reaches the boundary through a `<datalist>`-backed text input (the Form Request is the authority, not the option list); the "no `Recommended`" assertion scopes to "Recommended Purchase" / heading / image-name because the panel's own rotation-absence copy carries the word. _Dated close-out 2026-10-08 (documentation-sync pass): "E5–E6 untouched" is the false clause this close-out supersedes; both landed 2026-10-07 and the close-out note above the D16/D17/D18 rows is the record, which this row's own status cell now matches._                                                                                                                                                                                                                                                                                                                                                                                               |
| F1 (selection half) | Landed; redirect **held by the owner's ruling of 2026-10-08** | **The 0.1.0 run-detail page still owns four write paths, and it stays live until each has a 2.0 owner** (plan §9.6): the run status change (`runs.update`), a recorded turn's edit and delete (`runs.turns.update` for any row but the latest, and `runs.turns.destroy` for every row), the free-race entry (`runs.races.store`'s `entry_mode=manual` branch), and the CSV/JSON exports (`runs.export/{format}`). That is why `GET /training-runs/{run}` does not yet redirect; the test count is downstream of it and resolves as one sweep once the writes have owners. What this slice delivered is the selection half: `TrainingRunController::index()` row URLs now carry `runs.cockpit` (`SCR-CAR-011`) instead of `runs.show`, so Careers -> Select Career opens the 2.0 Cockpit directly, and the Dashboard's `resume_url` already did. `RunsIndexTest`'s two row-URL assertions repointed with the contract they assert; `CareerSurfaceCutoverTest` pins the index rows, the Dashboard resume, and (as an invariant, not a literal) that every Veteran row's link resolves to a career screen that renders; `tests/browser/career-surface-cutover.spec.ts` (6 cases) passed on a `VACUUM INTO` scratch copy. Pint, PHPStan `[OK] No errors`, typecheck and lore-code clean. Since the first report the hold's two named blockers moved: **the browser cleanups are no longer one** (23 specs' `afterEach` deletes now go over HTTP through `tests/utils/delete-run.ts`, measured as 23 rather than the 9 first reported, because `git grep` skips untracked files), and the Career Result door is no longer legacy-only (`run.result_url` from `CockpitController::runSection()`, rendered in the Cockpit's left column beside the timeline door, §9.6 records both). The 38 feature files that assert `component('Runs/Show')` (158 assertions) are the flip's sweep, listed in §9.6. The full audit table is in the F1 hand-off.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          |

### 4.1 Open work carried out of this status read

**Tree state as measured at 07:5x on 2026-10-08, for the next session to start from.** `master` is at
`7b04b6c`, unpushed and **7 commits ahead of `origin/master`** (`git status -sb`). Two slices sit in the
working tree uncommitted: this F1 residual (thirty-one paths) and the Database reference views slice, and
`app/Http/Controllers/PreferenceController.php` carries a third session's D18b work (settings blob,
`scenarioOptions`, streamed `export()`, `backup()`) rather than the one-line Pint change this thread caused
there. **The D11-D17 screens are not in HEAD at all**: `routes/web.php` at HEAD has no `runs.result` and no
`runs.timeline` (181 lines; both routes exist only in the working tree), and
`app/Http/Controllers/Career/{ResultController,TimelineController,RacePlannerController,SkillsPlannerController,EventDecisionController,InheritanceEventController}.php`
are all untracked - KI-71's condition, still open. Two consequences: the Career Result door landed here
cannot be committed apart from that block, because its controller line calls a route HEAD does not define,
and a browser pass cannot be treated as evidence while the shared harness on `:8127` is held by PIDs
30320/30528 and answers nothing (`curl /` returns no code at 12s and at 20s), which is KI-73's shape and
needs that holder's owner to reap it. `database/database.sqlite` holds **12** `training_runs` (1 at this
session's start); those rows are other sessions' leaks and nobody's cleanup has been authorised. **KI-76 is
open with its config untouched**, the two named remedies being a 2-3s SSR timeout or SSR off by default.

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

_Dated closures, 2026-10-07 (the D9 session's queue pass; original text kept above)._ **Item 1** closed:
`LegacySelectController`'s private `parentNames()` (22 lines) is gone in `b494d6f`, replaced by
`AncestryGraph::parentNames(SetupDraft::legacyParents())`; `CareerLegacyDeckStepsTest` is 12 passed /
248 assertions, `tests/browser/career-legacy-deck-steps.spec.ts` is 9 passed on the 8137 harness, both
`SCR-CAR-008` and `SCR-CAR-009` rows exist, and the owed register entry is `KNOWN-ISSUES.md` KI-65.
**Item 2** closed: `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` reads `[OK] No errors`
on the current tree. **Item 3** closed: `career-build-target.spec.ts` ran for the first time, 6 passed
(D4 has browser evidence). **Item 4** closed: `career-legacy-deck-steps.spec.ts` 9 passed after two
defects were fixed and one environment one cleared. One was a product defect (KI-66: `DeckSelect.vue`
bound `:is-friend` against the snake_case prop, so the "Friend slot" chip never rendered on step 5); one
was the test's own selector (`getByText('Wit')` is a case-insensitive substring match and resolved to 54
elements — replaced with one scoped, ordered assertion of the seven type words); the third was the
scratch database holding a stale run from a crashed spec, which voided
`career-legacy-deck-steps.spec.ts`'s empty-table premise and was deleted from that database (not the dev
one). Six of the seven cases the item named were already green when the pass began. _Dated correction
2026-10-07 (D13 session, on the owner's D11 close-out): item 4 was closed for that pass, not closed
forward._ Two facts the closure leaves unsaid. First, the green was bought by deleting one row by hand, so
the premise returns the moment a spec crashes or a concurrent session writes to the served database; that is
exactly what happened, and `career-legacy-deck-steps.spec.ts:268` failed again in the D11 full-suite read for
that reason alone (it is 9 passed again when re-run in isolation against a wiped scratch database). Second,
the selector fix was not in history either: `git show 55be0cd:tests/browser/career-legacy-deck-steps.spec.ts`
carried the live `await expect(page.getByText('Wit')).toBeVisible();` at `:183`, so a fresh checkout of
`b494d6f` or `55be0cd` reproduced the case this closure reports fixed. Committed 2026-10-07 as `b5d69be`,
which replaces that line with the scoped paragraph locator and one ordered `toHaveText`; `55be0cd` is left
as it is, because a fix in the working tree is not a fix in the plan's own record of it. Read "closed" here
as "green in one working tree on one day". Recorded as `KNOWN-ISSUES.md` KI-69. **Item 5** closed:
`uma:fetch-art --dry-run` on the scratch DB reports `card_portrait 106 ids, already on disk 106,
unresolved 0` and `support_thumb 559, already on disk 559, unresolved 0`, so the mirror is full and
`catalog-detail.spec.ts`'s "mirror holds no file" case was asserting the disk rather than the screen; it
now asserts the shape of whichever state the tree is in, the same ruling `support-deck.spec.ts` recorded
for the picker's thumbnails. _Dated note 2026-10-07 (D13 session): the ruling is sound and stands; the
sweep that applied it missed its third instance._ `support-cards.spec.ts:171` is the same defect:
`await expect(page.locator('#support-card-results img')).toHaveCount(0)` asserts that the mirror holds no
file, and `storage/app/private/artwork` holds 665 PNGs on this tree, so the case fails on a wiped scratch
database too (measured: `1 failed` with `runs=0`). It belongs in the spec, in the shape item 5 already set
at `catalog-detail.spec.ts:149`: assert `img[src=""]` count 0, which is a fact about the screen, and read
the frame's presence or absence from whichever state the disk is in rather than demanding the empty one.
Recorded as `KNOWN-ISSUES.md` KI-69. **D11** is no longer "Not begun": a concurrent session landed
`EventDecisionController`, `Career/EventDecision.vue`, `career/EventCard.vue`, the `runs.events.*`
routes, `CareerEventDecisionTest` (9 passed / 173 assertions) and `career-event-decision.spec.ts` while
this pass ran, so this session verified it rather than rebuilding it.
6. **Owner decisions that block content, not code:** whether `BuildPurpose` gains a "Competitive Build"
   case; whether risk tolerance and per-skill priority marks enter `BuildTargetPayload` (both are
   stored-shape changes); whether `config/scenarios.php` gains a sourced one-line scenario `description`
   and `recommended_use`; and whether `our_grand_concert.documented` should still read `true` now that
   `ab53861` recovered the primary read, since the flag being `true` makes the PARTIALLY DOCUMENTED
   badge unreachable while the client vocabulary in that scenario is still recorded as unverified.
   **Narrowed 2026-10-07 by slice E1.** The `documented` half is answered by a sibling slice's prose:
   a concurrent pass added `partially_documented` to `our_grand_concert` in the working tree, with the
   argument that `documented` is a provenance marker whose value flipped on 2026-10-05 and must not
   double as a badge condition, and that the Scenarios database screen reads the new key. That answers
   the first half of this item: `documented` stays a provenance marker reading `true`. What remains is
   **one** question and it is the owner's alone, not an agent's and not a slice's: **which key drives
   the badge.** E1 does not own that ruling and does not wait on it. Its controller read stays on the
   committed `documented`, and it will not read a key that exists only in a working tree, so its
   badge renders "Documented" for all four Global scenarios and `SCREEN_SPEC.md` SCR-CAR-019 records
   the partial arm as unreachable. Committing `partially_documented` (with the Scenarios screen's read
   of it) is the one change that makes the state reachable; when the owner rules, E1's controller line
changes in a follow-up commit, never inside this slice, and `scenario-panel.spec.ts` already has a
    case shape ready for it.
    _Dated correction 2026-10-08 (documentation-sync pass): the ruling this item was waiting on landed
    with E6. `config/scenarios.php` carries `our_grand_concert.partially_documented` and the badge now
    reads that key, so the PARTIALLY DOCUMENTED arm is reachable and `SCR-CAR-024` records the strip it
    drives. The owner's ruling is the one recorded in the E6 close-out; this item's own text stands as
    the record of the question it asked, which is now answered rather than open._
7. **Environment gate.** Resolved 2026-10-06. `create_veterans_table` and
   `add_build_target_to_training_runs_table` (with `add_condition_groups_to_skills_table` and
   `add_awakening_event_evo_to_character_cards_table`) sat Pending on `database/database.sqlite` for
   days while 1326 tests passed, which is KI-60's symptom, so `/` and `/legacy` returned 500 there. All
   five now read Ran at batch **3**: a peer applied them, not this pass, whose `php artisan migrate`
   reported "Nothing to migrate". Verified on the port-8000 server attached to that database: both
   routes return 200, `Veteran::count()` queries without a missing-table exception, and
   `Schema::hasColumn('training_runs', 'build_target')` is true. `migrate:status` stays in every
   hand-off regardless, because a green suite proves nothing about that file.

   _Dated correction 2026-10-07 (D13 session, on the owner's D11 close-out; the original sentence is quoted
   rather than deleted because it is the record of a false premise)._ The sentence read: "The browser suite
   still runs against a scratch database on port 8137 with `PLAYWRIGHT_BASE_URL`: it asserts an empty runs
   and veterans table, and the dev database now holds Trainer rows that would break that assertion." It does
   not. `playwright.config.ts:7` hardcodes `const port = 8127`, and `webServer.command` is
   `php artisan serve --port=8127` with `reuseExistingServer: true`, so that server reads `DB_DATABASE` from
   `.env`, which is `database/database.sqlite`: the **shared dev file**. The fourteen-failure read in the D11
   hand-off was taken there, and its own log line names `http://127.0.0.1:8127/training-runs/150`; at the
   time of that read the file held five Active runs (ids 7, 52, 107, 136, 216; `sqlite_sequence` 216). This
   contradicts the requirement in this same item, that the suite needs an isolated database because it
   asserts empty tables. Port 8137 belongs to a concurrent session, not to this plan. The fix is one of two
   things, and neither is a note: commit an isolated harness (a scratch database the suite creates and
   wipes, named in `playwright.config.ts` rather than in prose), or drop the claim that an isolated harness
   exists. Recorded as `KNOWN-ISSUES.md` KI-69.
   _Dated correction 2026-10-07 (measured during the D12 close-out sweep): that harness has lapsed, and the
   drift is the defect. `playwright.config.ts:7` sets `const port = 8127`, `:36` starts
   `php artisan serve --port=8127` with no `DB_DATABASE`, `:8` falls back to `http://127.0.0.1:8127` when
   `PLAYWRIGHT_BASE_URL` is unset (it is unset in this shell), and `config('database.connections.sqlite.database')`
   resolves `database/database.sqlite`. So `npm run test:browser` runs against the shared dev database, which
   is exactly the state this item says breaks the empty-table assertions: it held 4 `training_runs` rows
   (ids 7, 52, 107, 136, created Oct 3 and Oct 6) and 0 veterans when 152 passed and 14 failed. Two
   consequences. First, `dashboard.spec.ts:65`, `:105`, `:163` and `runs.spec.ts:41` are red on the dev file
   by construction, not because the screens regressed. Second, an empty `veterans` table is **not** a scratch
   artifact: no seeder touches that table and `RecordVeteran` has no caller, so a browser spec that needs a
   Veteran cannot pass on either database. Restoring the scratch harness is a separate decision from adding a
   fixture, and `KNOWN-ISSUES.md` KI-69 records the class._
8. **Toolchain constraints now binding on every Phase D and E slice.** TypeScript 7 removed
   `lib/typescript.js`, so `@vue/compiler-sfc` cannot resolve an imported type in `defineProps` and each
   page declares its props contract locally. There is no Ziggy, so pages state literal URLs. There is no
   axe dependency, so §12.5's hand-rolled checks are the accessibility evidence and "axe clean" must not
   be claimed.
   _Dated correction 2026-10-07: the axe clause is withdrawn, and the change is upward. `package.json:13`
   carries `@axe-core/playwright@^4.13.0`, `tests/utils/accessibility.ts` wraps it and scopes the scan to
   `#app` (the scoping is what makes it deterministic, KI-63), and nine browser specs assert `violations`
   is empty: `accessibility.spec.ts` and eight career specs (`grep -l buildAxe tests/browser` was the
   count, 2026-10-07). "axe clean" may therefore be claimed,
   and §12.5's four manual checks stay required beside it rather than in place of it: keyboard-only and axe
   are now cases, while reflow at 320px, reflow at a 200-percent width and the reduced-motion probe are the
   ones a slice must add. `career-race-strip.spec.ts:204` is the pattern (320 and 640x512 measured in one
   case, with the probe that can fail under `no-preference`)._
9. **Focus and accessible-name rules learned the expensive way.** Restore focus in a visit's `onSuccess`
   plus `nextTick`, never `onMounted`; put the `id` on the focusable element; keep state words out of
   headings, because they join the accessible name; and assert with `getByRole(..., { name })` rather
   than `toHaveText` on any element containing an `aria-hidden` glyph.
10. **The browser suite has two contradictory database preconditions, and an owner ruling is owed before
    D12's spec is committed.** OPEN, filed 2026-10-07. Item 7 requires an isolated database because the suite
    asserts an **empty** `veterans` table; D12's `career-inheritance-event.spec.ts` needs that same table
    **populated** with named Veterans, because `LegacyController::roster():308` seeds the parent picker from
    it and the fixture selects `Symboli Rudolf` and `Special Week` by label. No single scratch database
    satisfies both, and today nothing satisfies either half: `veterans` holds 0 rows in
    `database/database.sqlite`, in `.scratch-uma/browser.sqlite` and in a fresh `migrate --seed`, no seeder
    touches it, and `app/Actions/RecordVeteran.php` is referenced only in docblocks, so the picker renders no
    options and all seven fixture-driven cases time out at the 180s budget. **Recommended resolution:** the
    fixture owns its rows. Seed the Veterans the case picks inside the spec and delete them in teardown, so a
    browser test depends on data it created rather than on shared database state, and pair that with the
    item-7 harness fix so port 8127 stops serving the shared dev file. This is a D12 fix and its spec is
    untracked, along with its controller, page and feature test. The second half of the same defect is a
    selector contract: `Builder.vue` emits `name` only for `legacies.${index}.legacy_id` and `affinity`, while
    rank, ancestors and Spark fields carry `v-model` and `:id` and no `name` at all, so the fixture's
    `input[name="legacies.0.rank"]` cannot resolve even once the roster is populated (measured: supplying four
     Veterans moves the failure off `:80` onto `:81`). Recorded as `KNOWN-ISSUES.md` KI-69.
     _Dated correction 2026-10-08 (documentation-sync pass): the "no seeder touches it" and "RecordVeteran
     has no caller" halves are the stale readings this correction supersedes. D16 landed `RecordVeteran`'s
     first caller in `SaveVeteranController`, wired at `routes/web.php:237-240`, so the picker this item's
     fixture drives now has a write path. The harness question named in item 7 — whether the browser suite
     runs against a scratch database at all — is the half that stays open; this correction does not close
     it, and `KNOWN-ISSUES.md` KI-69 records that reading._
11. **The `completed` goal state has no browser case.** E2's `UraPanelTest` proves the branch (a mandatory
    race the run records renders `Recorded` and raises no alert), but reaching that state in a browser needs
    a race written through `SCR-CAR-013`'s write path, which E2 does not own. Open item for whichever pass
    next runs `career-race-decision.spec.ts`: record the debut there, then assert `Recorded` in
    `ura-panel.spec.ts`. No fixture is invented to green it (owner ruling 2026-10-07).
    _Dated correction 2026-10-08 (documentation-sync pass): the `ura_finale` strip on the URA panel's
    career-goals module renders the `Recorded` arm only when the run records the mandatory race; this item's
    browser case is still open as written, because `ura-panel.spec.ts` has not been re-run on a tree where
    the debut race is written through `runs.races.store` first. The close-out does not mark it closed; the
    next pass that runs the URA panel spec owes the case the item describes._

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
  `Inertia::render('Runs/Show', $this->showData($run))`, and `showData()` _is_ the page payload: every value
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

| Slice | Adds                                                                                                                                                                               | Acceptance                                                                                                                                                                                                                                                                                                                                                                                                                |
| ----- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| D1    | Enrich `Dashboard.vue`: active career, recent veterans, data status indicator — the legacy-goal gaps panel was dropped by the slice plan (see §8.1 decision 5)                     | Every panel is real data or a named absence; no invented count; subtle "GLOBAL DATA ● Current" badge                                                                                                                                                                                                                                                                                                                      |
| D2    | `SetupLayout`, `Career/ScenarioSelect.vue`                                                                                                                                         | Cards render from `config/scenarios.php`; Grand Concert shows the baseline strip (`documented => false`); no scenario name branched in a component                                                                                                                                                                                                                                                                        |
| D3    | `Career/TraineeSelect.vue`, `Career/TraineeProfile.vue`                                                                                                                            | Reuses the catalog queries; aptitude badges carry text, not colour alone                                                                                                                                                                                                                                                                                                                                                  |
| D4    | `Career/BuildTarget.vue`, `components/ProvenanceBadge.vue`                                                                                                                         | **Career Plan object**: purpose, race profile, stat/aptitude/style/skill targets, risk tolerance. Render as "Your target" not "The correct target". Target stats clamp via `ScenarioCaps`; skill priorities ordered; badge is only place four glyphs live                                                                                                                                                                 |
| D5    | `Legacy/Index.vue`, `Legacy/Builder.vue`, `Legacy/Compare.vue`                                                                                                                     | Record-only: pick, browse, compare; **no** optimizer, no "expected inheritance" (banner on the screen)                                                                                                                                                                                                                                                                                                                    |
| D6    | `Support/Builder.vue` (over `runs.deck.sync`, `x-deck-editor`)                                                                                                                     | **Six slots**; ownership flag (OWNED/RENTED), not structural borrowed assumption; **seven support types** (Speed/Stamina/Power/Guts/Wit/**Pal**/**Group**); Scenario Link derived from scenario+character; granular deck analysis (training power, early run, race bonus, safety, events, skills)                                                                                                                         |
| D7    | `Career/Preflight.vue`                                                                                                                                                             | Composes D4–D6; warnings are the derivable ones only                                                                                                                                                                                                                                                                                                                                                                      |
| D8    | `CareerLayout`, `Career/Cockpit.vue`, `career/CareerHeader.vue`, `career/CareerStatePanel.vue`, `career/ActionGrid.vue`, `career/RecommendationCard.vue`, `career/AdvisorRail.vue` | Three-column desktop / one-column mobile; the advisor rail reads C2 and prints band + reason lines, **not** a score or a numeric confidence; the recommendation never auto-executes                                                                                                                                                                                                                                       |
| D9    | `career/TrainingCard.vue`, `Career/TrainingDetail.vue`                                                                                                                             | Per-option breakdown shows only sourced terms; the unsourced yield renders `N/A`                                                                                                                                                                                                                                                                                                                                          |
| D10   | `career/RaceCard.vue`, `Career/RaceDecision.vue`                                                                                                                                   | Race facts from `RaceCatalogSlot` where present; win probability renders `N/A` with a `title` naming the `ADR-0016` blocker                                                                                                                                                                                                                                                                                               |
| D11   | `career/EventCard.vue`, `Career/EventDecision.vue`                                                                                                                                 | Records the choice over `TurnEvent`; the advisor's override is always available                                                                                                                                                                                                                                                                                                                                           |
| D12   | `Career/InheritanceEvent.vue`                                                                                                                                                      | Record-only: predicted section shows parent/grandparent Sparks from `legacy_selection` with sourced star-roll odds (REFERENCE §1.5.3), "expected inheritance" = `N/A` citing `ADR-0020` §3. Observed section records Trainer-entered inspiration outcomes via existing TurnEvent mechanism. Milestone timeline (turns 1, 31, 55 per REFERENCE §1.5.1). Two clear regions with Estimated/Confirmed badges per antislop-ui. |
| D13   | `Career/SkillsPlanner.vue` _(design-2.0 only)_                                                                                                                                     | States Required/Available/Learned/Inherited; warns when SP cannot cover required; hint level discount calculation (base cost × (1 - discount%)); skill race-fit assessment (distance/surface/style/course/weather/ground/phase); no skill recommendation (OQ-5 stays open, `ADR-0020` §2)                                                                                                                                 |
| D14   | `career/CareerTimeline.vue`, `Career/Timeline.vue`                                                                                                                                 | Renders `TurnEntry` / `TurnEvent` history; before/after state; no silent rewrite of a past decision                                                                                                                                                                                                                                                                                                                       |
| D15   | `Career/Result.vue`                                                                                                                                                                | Reads the finished run; no derived "build quality" score                                                                                                                                                                                                                                                                                                                                                                  |
| D16   | `Career/SaveVeteran.vue`, `Veterans/Index.vue`, `Veterans/Show.vue`, `Veterans/Compare.vue`                                                                                        | Over C3; tags are suggestions, notes are free text                                                                                                                                                                                                                                                                                                                                                                        |
| D17   | `Database/{Trainees,Supports,Skills,Races,Scenarios}.vue`                                                                                                                          | Reuses the ported catalog/skills/support screens; the Race database names the 0-row offline state                                                                                                                                                                                                                                                                                                                         |
| D18   | Extend `Preferences/Edit.vue` → Settings categories (General / Recommendation / Data / Game version)                                                                               | Every new preference is a real column; the ruleset row renders `N/A` until a ruleset string is sourced                                                                                                                                                                                                                                                                                                                    |

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
_Dated close-out 2026-10-08 (documentation-sync pass): the "not landed" reading this plan's premise
states is the stale claim the close-out supersedes. `resources/js/pages/Career/` now exists and holds
the full career set (ScenarioSelect, TraineeSelect, TraineeProfile, BuildTarget, Cockpit,
TrainingDetail, RaceDecision, EventDecision, InheritanceEvent, SkillsPlanner, Timeline, Result,
Preflight, SaveVeteran), `CareerLayout.vue` and `SetupLayout.vue` are in `resources/js/layouts/`,
and D2 itself landed green on 2026-10-06 at `17b793a` (its own §4 row records the commit). This
section's body stands as the record of the read it was taken on at `40c91d4`, when D2's route had
not yet landed, and is preserved as written rather than re-described against the tree._

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
   _Dated close-out 2026-10-08 (documentation-sync pass): the "unbuilt" reading this item states is
   the stale claim the close-out supersedes. D2 landed green at `17b793a` and D4 landed green at the
   same commit (both recorded in §4), so step 1 and step 3 of the wizard are live on this tree;
   steps 4 and 5 are the two the §4.1 item 1 pass greened and registered as `SCR-CAR-008` and
   `SCR-CAR-009`, and step 6 landed as `SCR-CAR-010` with D7. The full six-step wizard is live, and
   the `SCR-CAR-002` status cell records it as such. This item's own text stands as the record of
   the read it was taken on at `40c91d4`, when D2's route had not yet landed._
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
_Dated close-out 2026-10-08 (documentation-sync pass): the "D2/D3 are unbuilt" reading this
plan's premise states is the stale claim the close-out supersedes. Both landed green at
`17b793a` (their §4 rows record the commit), and the re-wrap this plan anticipated has since
happened: D4's `BuildTarget.vue` now mounts inside `SetupLayout` step 3 alongside D2 and D3, which
is what the §8.4 wizard-contract table this section builds on describes. The re-wrap is a fact of
the tree now rather than a forward-looking expectation, and the plan's own §8.1/§8.2 precedent
records it the same way: the body of a slice plan preserved as written, with a dated close-out
beside it, rather than re-described against the tree._

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
  _Dated correction 2026-10-07: the gate as shipped does not enforce the last sentence. `CareerBuildTargetTest.php:193-221`
  sweeps two glyphs, `✓` and `∑`, and its own comment at `:215-217` excludes `~` and `?` as unquotable (a tilde sits in
  two Legacy comments, a question mark in every ternary). The word clause was never implemented and cannot be, as
  written: `InheritanceEvent.vue:378` and `:420` print sentences containing "Confirmed", which is ordinary English
  rather than a second map. The enforceable contract is therefore **the glyph pair and the state→word map live in
  `ProvenanceBadge.vue` and nowhere else**. If the owner wants the words gated too, the sweep must target the map
  shape (a `BADGES`-style literal assigning `glyph:` alongside `word:`), not any mention of the four words._
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

### 8.6 D18 — Settings categories (SCREEN-024): slice plan

Extends `Preferences/Edit.vue` into four categories (General, Recommendation, Data, Game
version) per `docs/proposals/screen-spec-2.0.md` SCREEN-024. The page already ships theme and
failure-estimate controls; this slice adds the tablist shell, the Game-version panel (the only new
fact surface), and named absences for every preference that has no column.

Depends on: nothing (PreferenceController and the two controls landed as the 2026-10-04 port).

### Files

| File | Change |
| --- | --- |
| `app/Http/Controllers/PreferenceController.php` | add `verifiedAt` and `importUrl` to `edit()` props |
| `resources/js/layouts/AppLayout.vue` | add `role="status"` and `aria-live="polite"` to the flash `<p>` |
| `resources/js/pages/Preferences/Edit.vue` | restructure into tablist: General, Recommendation, Data, Game version; render named absences; Game version panel reads `app.ruleset` + `verifiedAt` |
| `tests/Feature/PreferenceControlsTest.php` | append prop tests for `verifiedAt` and `importUrl` |
| `tests/browser/preferences.spec.ts` | append D18 cases: tablist keyboard nav, N/A ruleset row |
| `SCREEN_SPEC.md` | extend §7-5 with the four-category structure and states table |

### Props contract

```php
Inertia::render('Preferences/Edit', [
    'theme' => in_array($theme, ['light', 'dark'], true) ? $theme : null,
    'failureEstimate' => Preference::get('failure_estimate') ?? 'off',
    'verifiedAt' => config('scenarios.verified_at'),   // '2026-09-27'
    'importUrl' => route('runs.import'),
]);
// ruleset comes from the shared app.ruleset prop (null by ruling), read in the component.
```

### Decisions (and why)

1. **Heading stays "Preferences"; the page is organized by category, not renamed.** The existing
   browser test asserts an h2 reading "Preferences"; the SCREEN-024 name is "Settings" but the route
   and the nav label already say Settings, so the h2 does not carry the screen name. No rename avoids
   re-breaking the shell control-count.
2. **Tablist, not anchored fieldsets.** Four named views of one form; a tablist gives arrow-left/right
   switching with a stable Help placement and preserves the single POST to `/preferences`.
3. **Race-risk thresholds is omitted from the tree entirely.** It is held on ADR-0016 (win probability),
   so it is not rendered as an absence-with-a-column — there is no column to absent.
4. **Game version panel reads `app.ruleset` from shared props** (null), same as the Dashboard's
   `dataStatus` row, to avoid a second copy of the same null.
5. **Save feedback is a polite live region.** `role="status"` + `aria-live="polite"` on the flash `<p>`
   in `AppLayout`, so screen readers announce "Preferences saved." after the redirect-back visit without
   stealing focus.
6. **Data category: Import is a real link; Export/Backup/Restore/Reset are named absences.**
   `route('runs.import')` exists; the others have no route — Reset is destructively scoped per
   AGENTS §5, so it is absent, not a confirmation dialog without a backend.

### Tests

- Props: on an unset DB, `verifiedAt` is the config value and `importUrl` is the import route URL;
  existing theme/failureEstimate assertions remain green.
- Browser: tablist arrow navigation lands on each tab; the active tab panel's controls render; the
  ruleset row reads "N/A" with its title; save shows the live-region message.

### Open questions

1. **(Owner decision, recommended)** Add a nullable `settings` JSON column to `preferences` — one column,
   one migration — so Language, Units, Default scenario, Aggressiveness, Stat-target defaults and
   Race-risk thresholds live in a single JSON blob validated by `Preference::KEYS` at the top level?
   _Rationale:_ `training_runs.build_target` already carries a `build_target` json column
   (`add_build_target_to_training_runs_table`); the precedent exists and the write path reuses
   `UpdatePreferenceRequest`'s key-set refusal at the blob's top level. Ranked alternatives if no:
   (a) which individual columns to approve, (b) whether the Reset route is approved as a destructive
   action.
2. **DesignTokensTest column assertion.** If the owner approves (1), `DesignTokensTest:168` asserts
   `preferences` has exactly `['key','value','created_at','updated_at']` — a one-line update in the
   same commit. Noted, not gating.

**Closed 2026-10-08 by the owner, on the D18b hand-off.** The authorization was wrong and the schema refused
it: (1) closes as the **`settings` row**, not a column. `preferences` is a key-value table with
`value TEXT NOT NULL` and no-row-means-unset, so the `training_runs.build_target` precedent does not
transfer, and the one row that could carry a nullable column must also fill the `value` it duplicates. The
key set is the contract the open question was actually describing: `Preference::KEYS` gains `settings` as the
top-level check, `Preference::SETTINGS_KEYS` validates the interior, and `UpdatePreferenceRequest` refuses an
unknown nested key. (2) closes as **not applicable**: the column list never changed, so the assertion never
moved, and editing it would have blessed an unread column. The `ALTER TABLE` alternative is explicitly
rejected and is not to be implemented.

#### D18b — Settings stored preferences (SCREEN-024, 2026-10-08): slice plan

The owner authorized open question 1. Building it surfaced one premise that fails its own check, and it
is recorded here rather than worked around.

**The store is a row, not a column.** The authorization says "add a nullable `settings` JSON column to
`preferences`, on the pattern of `training_runs.build_target`". That pattern does not transfer:
`training_runs` has a numeric key and real rows, so a JSON column per row is meaningful; `preferences` is
a key-value table (`2026_09_27_121500_create_preferences_table.php`) whose `value` is `text()` NOT NULL
and whose own docblock says "a preference that has never been set has no row, and that absence is the
default rather than a NULL to interpret". A nullable `settings` column on that table would be read by
nothing, and the one row that would carry it cannot exist without also filling the NOT NULL `value` it
duplicates. The settings blob is therefore **one row keyed `settings` with JSON in `value`** — the same
shape `build_target` uses on its own table — and `Preference::KEYS` gains `'settings'`, which is the
top-level validation open question 1 actually describes. `DesignTokensTest`'s column assertion stays
exactly as it is, because the column list does not change; updating it would bless an unread column,
which is the drift that assertion exists to prevent. If the owner still wants the column, the migration
is `ALTER TABLE preferences ADD COLUMN settings TEXT NULL` plus that one assertion line, and nothing in
this slice's code changes shape.

**Files.** `app/Models/Preference.php` (`KEYS` + `SETTINGS_KEYS` + `settings()`/`putSettings()`);
`app/Http/Requests/UpdatePreferenceRequest.php` (the blob's rules and its nested key-set refusal);
`app/Http/Controllers/PreferenceController.php` (`edit()` gains the settings, scenario options, stat
order and hard cap; `update()` persists the blob; new `export()` and `backup()`); `routes/web.php`
(`preferences.export`, `preferences.backup`); `app/Actions/BackupDatabase.php` (new — the snapshot,
extracted so the CLI command and the web route share one implementation); `app/Console/Commands/UmaBackup.php`
(delegates; its mechanism moves from checkpoint-then-copy to `VACUUM INTO`, which holds a consistent
snapshot of a live WAL database where checkpoint-then-copy cannot while a request is in flight);
`app/Services/ScenarioCaps.php` (`hardCap()`, the one-line accessor the prompt names that did not exist);
`resources/js/pages/Career/BuildTarget.vue` and `app/Http/Controllers/Career/BuildTargetController.php`
(the stat-target pre-fill); `app/Http/Controllers/Career/ScenarioSelectController.php` (the
default-scenario pre-select); `resources/js/pages/Preferences/Edit.vue`; `tests/Feature/PreferenceControlsTest.php`;
`tests/browser/preferences.spec.ts`.

**Scope decisions.**

| Preference | Decision |
| --- | --- |
| Default scenario | Built. `settings.default_scenario`, validated against `config('scenarios.php')`'s own keys; Scenario Select pre-selects it when the draft names none |
| Recommendation aggressiveness | Built. `conservative` / `balanced` / `aggressive`; stored, and a `title` says the advisor reads it in a follow-up slice |
| Stat-target defaults | Built. Five ints keyed by the matrix's stat names, `min:0` and `max:ScenarioCaps::hardCap()`; Build Target pre-fills them only when no target is stored |
| Language | Built as a disabled single-option control ("English (Global)") with the corpus's Global-only scope in its `title`; the write path accepts only `en` |
| Units | **Left as `N/A`**, and not for the reason the prompt guessed. The prompt said "if no unit-bearing surface exists" — one does: `RaceCatalogSlot::distanceLabel()` prints metres, and its two callers are the Race Decision screen (`RaceDecisionController.php:156`) and the Race Planner (`RacePlannerController.php:306`, both through `RaceFacts::forSlot()`). The real reason is sourcing: the client prints metres, no source in the corpus publishes an imperial rendering, and converting would make this tool's copy disagree with the client's own display. A **sourcing absence**, one of the four classes now pinned in `SCREEN_SPEC.md`'s SCR-SYS-002 Named-absence row |
| Export | Built. `preferences.export`, a streamed JSON download of `training_runs`, `turn_entries`, `veterans` and the settings blob |
| Backup | Built. `preferences.backup`, a server-side `VACUUM INTO` snapshot under `storage/app/backups/`, the path returned in the flash, never streamed |
| Race-risk thresholds | Held on `ADR-0016`; absent from the tree entirely (D18 decision 3) |
| Restore, Reset | Destructive, owner-scoped (`AGENTS.md` §5); absent, not disabled |
| Risk tolerance | `N/A` with a `title` naming D4's open question on whether it enters `BuildTargetPayload` |

**Design-language boundary.** §4 (Level 3 rows, Level 2 Save), §5 (state by word, not colour alone),
§6 (tokens only), §7 (`tabular-nums` on the stat inputs), §8/§9 (the screen's existing values), §11
(the save confirmation is the shell's one `role="status"` flash, not a second surface), §12
(one concept per category), §13 (one primary action), §14 (Save primary, Import/Export/Backup
secondary), §29 (`N/A` rows as micro empty states), §30 (the Backup button's own pending state, ADR-0007),
§33 (`title` explains terminology), §34 (moderate density), §35 (current value shown, the why in a
`title`), §40 (the 320px reflow the spec measures), §42 (labels, tablist), §43 (`prefers-reduced-motion`,
measured as the parsed `transitionDuration` under `0.01s`, because the app's global reduce sets `0.00001s`
rather than `none`), §48 (the Game-version panel stays). Not applied, with the
reason: §2, §15–24 and §25–28 are cockpit or catalog constructs a preferences form has none of; §36 has
no recommendation to explain; §37–39 are the Trainer's own state, acknowledged by the flash; §41 is the
shell's; §44 adds no glyph to a form; §45–47 have no data to visualise and no expert surface; §49 has no
engine fact to trust-rate, every value on this screen is the Trainer's own input; §50 is a question a
settings screen does not answer.

**Three defects, all found by browser verification and none by static analysis.** `npm run typecheck` passed
and `vendor/bin/phpstan` was clean over all three. (1) `form.errors.value[key]` — `useForm` returns a
reactive object, so `.value` is `undefined` and the first render threw `TypeError: Cannot read properties of
undefined (reading 'theme')`; every `/preferences` case failed at the hydration wait for 180s. This is the
strongest single argument in the record for the browser suite's cost: the Feature test asserts the Inertia
props and cannot see it, and the pattern **would have shipped as a TypeError on first render to any screen
that inherited it**. (2) The focus-on-refusal handler named only `theme` and `failure_estimate`, so no new
control could receive focus; replaced with `document.getElementById(controlId(firstKey))?.focus()`, the
idiom `BuildTarget.vue:140` already uses, and the five control ids unified onto `controlId()` so the id, the
`aria-describedby` reference and the focus target agree. (3) `min="0"` and `:max="hardCap"` on the stat
inputs gave the engine ceiling two owners and stopped the submit before the Form Request saw it, which made
the server's refusal unreachable from a browser and left the bound untested; removing the attributes leaves
one owner and the refusal is now asserted in `tests/browser/preferences.spec.ts`. (3) is the subtle one: an
HTML clamp looks like defence and is actually a second validator.

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

_Dated close-out 2026-10-08 (documentation-sync pass; the slice descriptions above are preserved as
written): every Phase E slice this section describes as work to be done has since landed. **E1** shipped
the shell (`SCR-CAR-019`, `components/scenario/ScenarioPanel.vue` and its registry, `registry.ts`),
**E2** registered the URA panel (`SCR-CAR-020`, `UraPanel.vue`, the `career_goals` key), **E3**
registered the Unity Cup panel (`SCR-CAR-021`, `UnityCupPanel.vue`, the `team_race` /
`team_rank_ladder` / `race_calendar` keys), **E4** registered the Trackblazer panel (`SCR-CAR-022`,
`TrackblazerPanel.vue`, the `grade_objectives` / `shop` / `epithet_routes` keys), **E5** landed the
Scenario Race Planner as its own page (`SCR-CAR-023`, `Career/RacePlanner.vue`, `runs.races.planner`),
and **E6** drew the Grand Concert baseline strip where every flag is off (`SCR-CAR-024`, the badge arm
that reads `partially_documented` per the owner's ruling recorded in the §4.1 item 6 correction). The
§4 table's E1–E4 rows carry each slice's own gate evidence as it was run on its own tree; this close-out
does not re-run those gates and does not claim a green it has not taken. The `SCR-CAR-019` status cell in
`SCREEN_SPEC.md` records the same completion for the shell and names the key the E6 strip drives._

### 9.1 E3 — Unity Cup panel, the Team Cockpit (SCREEN-015): slice plan

Written before code, 2026-10-07. The Blade references were retired by B1 but their Vue ports
(`components/{TeamRankGauge,SpiritBurstRoster,TeamRacePanel}.vue`, ported at A4b) are live on the run
record screen, so the port is a composition, not a recovery: carry their props and logic verbatim
(behaviour parity first), composed inside `components/scenario/UnityCupPanel.vue`.

**Files.** `CockpitController::scenarioSection()` gains a `team` key (uniform shape for every
scenario; `turnEvents` joins the eager load); `headerSection()`'s `values.team_rank` and the section's
`widget_values.team_rank` fill with the recorded letter so the header strip, the panel meter and the
gauge cannot disagree; `types.ts` gains `ScenarioTeamSection`; `ScenarioPanelTest`'s pinned key list
gains `team` (the contract anticipates it: panel data cannot be smuggled in without a test naming it);
`config/scenarios.php` `unity_cup` gains `spirit_burst_bands` and `burst_payout_timing`, transcribed
from `docs/scenarios/02-unity-cup.md:213-220` (post-rework corrected values) with the Extreme-counting
disagreement of run report §8.2 rendered as a caveat, per §8.3's grounding; new
`components/scenario/UnityCupPanel.vue` and `components/scenario/RaceCalendarNote.vue`; the shell's
registration block (E2 moved it into `ScenarioPanel.vue`) registers `team_race` and
`team_rank_ladder` → `UnityCupPanel` and `race_calendar` → `RaceCalendarNote`;
`tests/Feature/UnityCupPanelTest.php`; `tests/browser/unity-cup-panel.spec.ts`; `SCREEN_SPEC.md`
`SCR-CAR-020`.

**Registration and the two mounts.** The shell renders one component per ON flag, and Unity Cup has
three ON. `UnityCupPanel` registers for the two Unity-Cup flags and branches on `props.name` — the
flag key the shell mounted it for, never a scenario name: the `team_race` mount renders the ported
`TeamRacePanel` (guidance, entries, margin word); the `team_rank_ladder` registration is the §27 first-class
Team Panel — `TeamRankGauge` (recorded letter, ladder, facility level with its cause, S+ note), the
five per-stat team grades as `N/A`, member rows as the recorded burst roster (`SpiritBurstRoster`,
glyph plus word), Spirit as a `ResourceMeter` (value and bar), the burst-band table with the caveat
and the payout timing, and Special Training from its two configured facts
(`team_training_energy_penalty`, `wit_burst_energy_bonus`). `race_calendar` gets the note renderer
because the Cockpit's left column already draws that calendar and E1's fallback would print a false
"cannot draw" beside it.

**Data that exists.** Team rank letter (`TeamRankPayload` via `latestTeamRank()`), burst states per
teammate (`SpiritBurstPayload` via `spiritBurstRoster()`), and team race entries (`raceEntries` where
`scenarioSlot.kind === 'team_race'`, carrying `circles`/`placement`). Nothing else in the brief's
Team Info field set (team name, motto, league standing, per-stat grades, Unity Trainings and burst
counts, member count, member stats and roles, current Spirit) has a column or payload, so each is a
named absence, and the run's own band position is `N/A` because D-223 stores states, not counters.

**Not built, and why.** Burst readiness ("current Spirit versus the configured threshold") is not
derivable: the bands are thresholds, but no Spirit reading has a writer, so there is nothing to set
against them — named absence.
"Recommended timing" and "projected benefit" are held advice (ADR-0020 §3) — named absences with
`title`s. Recording team rank or burst states has no UI write (the "own Requests" line in
`StoreTurnEventRequest`'s docblock names requests that do not exist) — the panel is read-only and the
capture schema proposal of `SCREEN_SPEC.md` §7-6 stays the owner's. The +30 ranking bonus renders
`Estimated` and **only beside Rank S**, the rank the recorded client frame shows it at (run report
§1.8, §7.5): other ranks pay a figure this corpus does not state, and a badge would not excuse
printing +30 for them. The band table carries one `Estimated` badge and one warning line naming all
three publisher conflicts the run report records (§7.4: the Skill Point figures and the payout month;
§8.2: whether Extreme Bursts count), not only the counting one.

**Tests.** Props: full team, partial team, no team data, uniform shape across the four scenarios plus
a fifth from config alone, `widget_values.team_rank` filled only when a rank is recorded. Browser:
the empty-run Unity Cup Cockpit (what a real run shows today) — band table, named absences for
timing/benefit/readiness, Spirit meter text and track, roster empty state, Special Training facts;
keyboard reading order, the 44px sweep, 320px reflow, reduced motion, axe A + AA scoped to `#app`.
Data states (roster rows with glyphs, a recorded rank) are asserted at props level because no write
path can produce them in a browser.

_Dated close-out 2026-10-08 (documentation-sync pass; the plan body above is preserved as written): this
slice plan is the plan E3 executed, and the §4 table's E3 row records the landing with its gate
evidence — `UnityCupPanelTest`, `unity-cup-panel.spec.ts`, the `team_race` / `team_rank_ladder` /
`race_calendar` registrations in `ScenarioPanel.vue:59-60`, and the `SCR-CAR-021` row it filed. The
"Not built, and why" section's reading — that recording team rank or burst states has no UI write, and
the capture schema proposal of `SCREEN_SPEC.md` §7-6 stays the owner's — is the half this close-out
keeps live: no write path exists on this tree, the panel is read-only, and the owner's call on the
`unity-cup-capture.md` schema proposal is not made by any Phase E slice. The tree state this pass
measures is the same state the plan's body describes; the close-out is a landing note, not a
re-description._

 ---

### 9.2 E2 — URA panel (SCREEN-014): slice plan

 Embedded 2026-10-07 from `docs/research-scratch/E2-URA-PANEL-2026-10-07.md`, headings demoted one level.

 Written before code, per the brief's PLAN PHASE. Read against the tree at the E1 landing (browser gate
 green: `npm run build` exit 0; `scenario-panel.spec.ts` 8 passed).

#### 1. Dependency and preconditions

- **D8 landed** (the Cockpit exists; `SCR-CAR-011`). **E1 landed** with its browser gate green, so
   the shell E2 registers against is verified, not just props-green.
- E1 registers no renderer, so every ON flag draws `WidgetFallback` today. E2 is the first `register()`
   call, which is also the arm E1's spec could not render (plan §4, E1 row).

#### 2. The headline finding: four of SCREEN-014's five components have no data

 Measured on a `VACUUM INTO` copy of `database/database.sqlite` (2026-10-07), not remembered:

 | SCREEN-014 component | Repo state | Verdict |
 | --- | --- | --- |
 | character goals | no table holds them. `TrainingRun.php:599-616` says it outright: the Goal pennant "returns when `trainee_goals` exists to drive it; until then an unearned pennant is a worse claim than an absent one". No `trainee_goals`, no `goals` column, no `Goal` model. | **unsourced** |
 | URA progression | `config/scenarios.php`'s `ura_finale` entry has no `finale` and no milestone key. `'finale' => ['kind' => 'points_league', 'races' => 3]` belongs to **trackblazer** (E4), not to URA. | **unsourced as a per-character goal set** |
 | Happy Meek level | no column anywhere stores it; no write route exists. | **unsourced, and the module is read-only** (brief: no migration) |
 | Happy Meek duel availability | nothing stored, nothing in config. | **unsourced** |
 | Happy Meek potential reward, final-race contribution | the brief already orders `N/A` "unless the repo already holds a sourced value". It does not. | **unsourced** |
 | upcoming mandatory races | `race_catalog_slots` holds them: `is_mandatory = 1` on 4 rows — `Junior Make Debut` (year 1, turn 12), `URA Finals Qualifier` (year 4 early), `URA Finals Semifinal` (year 4 late), `URA Finals Final (URA)` (scenario-scoped `ura_finale`). All four are `is_special_race = 1`. | **present, sourced** |
 | finale preparation | the three URA Finals rounds are those recorded rows; the run's position against them is derivable. | **present, derived** |

 So E2 is not "render the goal data" — it is **a panel whose two honest modules are the mandatory-race
 record and the finale rounds, plus named absences for goals and Happy Meek, each saying what is missing,
 why it matters, and what to do** (design-2.0 §29, and the brief's empty-state rule).

 This is the shape the brief already anticipates for Happy Meek ("render each as `N/A` with a title").
 It does **not** anticipate it for character goals, so the plan says it here for the owner rather than
 inventing a goal set from `docs/scenarios/09-global-race-calendar.md`, which holds four _different_
 per-character Goal sets and is exactly what `TrainingRun.php:606-610` refuses to guess from.

#### 3. Composition: the flag, and why a new one

 Registration must be by flag or widget key (G-33). URA's entry declares `widgets => ['turn','energy','fans']`
 (all labelled, all shared with other scenarios) and exactly one ON panel flag, `race_calendar` — which
 `unity_cup` and `trackblazer` also turn on, so registering URA content against it would draw URA content
 in their cockpits.

 So E2 adds **one composition key**, in config only:

- `panel_labels` gains `'career_goals' => 'Career goals'`.
- `ura_finale.panels` gains `'career_goals' => true`; every other scenario omits it (the controller's
   `?? false` covers them, and `ScenarioPanelTest:120` asserts the flag set equals `panel_labels`' keys).
- `CareerScenarioSelectTest` builds a sixth test-fixture scenario whose `panels` map lists the six flags
   by hand (`:157-164`); the payload will gain the seventh key automatically. Whether that test asserts the
   map exactly is **to verify in the build** — if it does, the fixture needs the seventh row.

 Zero component edits are needed to add a fifth scenario, which is the point. `CareerScenarioSelectTest`
 pins a six-flag `panels` map and will need the seventh row.

#### 4. Payload contract (E2's additions to E1's section)

 E1 carries `objectives`, `actions`, `alerts`, `finale` empty, "the §49 sections each panel fills for
 itself". E2 fills them, gated on the same flag, and adds no top-level key (E1's pinned 17-key list stays
 valid; a new key would need a test naming it, which E2 does not need):

- `objectives[]` — one row per mandatory race: `{ label, state, deadline, race_url, absence }`, where
   `state` uses the brief's four glyph words (completed / current / upcoming / missed) plus an absence row
   for the per-character goals. `deadline` is the recorded turn's `year`/`turn` or `null` with a `title`.
- `finale` — the three URA Finals rounds in order, each `{ label, state, turn }` where a source states one.
- `alerts[]` — a **Level 1 Critical** `AlertRow` when a mandatory race's recorded turn **has arrived or
   passed** and the run records no race entry for it. Derived from the calendar and the run's own records
   only; no window is invented (see the open question).
- Absence statements are carried as text on the rows themselves, the way E1's widgets already carry
   `absence`, rather than as new top-level keys.

 **The alert's level is sourced, and its treatment is not colour.** `design-2.0` §4 defines four visual
 hierarchy levels; **Level 1 — Critical** lists "mandatory race" as its first example and prescribes
 "strong emphasis, clear icon, prominent placement" — no colour. So E2 extends E1's `AlertRow`
 (today `tone: 'note' | 'warning'`) with a `critical` tone drawn from the existing `text-risk` token, and
 the level's own word travels as text beside the glyph, per the repo's "never state by colour alone" rule.

 **Same-owner rule (ADR-0015):** the run's current turn is read through `TrainingRun::nextTurnToPlay()`,
 the owner the Cockpit's race section already calls (`CockpitController.php:371`), not a second subtraction.

#### 5. Files

 | File | Change |
 | --- | --- |
 | `config/scenarios.php` | one `panel_labels` row, one `ura_finale.panels` flag |
 | `app/Http/Controllers/Career/CockpitController.php` | `scenarioSection()` fills `objectives` / `finale` / `alerts` when the flag is on |
 | `resources/js/components/scenario/UraPanel.vue` | new: goals/absence module, finale strip, Happy Meek module, alert |
 | `resources/js/components/scenario/AlertRow.vue` | a `critical` tone beside `note`/`warning`, on the existing `text-risk` token |
 | `resources/js/components/scenario/ScenarioPanel.vue` | the first `register()` call — it imports `UraPanel.vue` and registers it against the flag key. `registry.ts` itself is unchanged; the shell today imports only `resolve` |
 | `tests/Feature/ScenarioPanelTest.php` (or a new `UraPanelTest.php`) | the E2 props cases |
 | `tests/browser/scenario-panel.spec.ts` or a new `ura-panel.spec.ts` | goal states as text, the N/A titles, the alert, keyboard path, 44px sweep |
 | `SCREEN_SPEC.md` | `SCR-CAR-019`'s contract section and the state table gain the panel's states |

 **No migration, no new route, no write** — the brief's read-only instruction, stated here for the owner.

#### 6. Tests (RED first)

 Props: goals in each state; the absence row when no goal set exists; Happy Meek with and without a
 recorded level (both `N/A`); `N/A` carries a `title`; the mandatory-race rows match the calendar; the
 alert fires only when a mandatory race's turn has arrived with no record; **no scenario-name branching**
 (the E1 shape: the same payload for all four scenarios, and the flag set stays config-derived).
 Browser: rendered goal-state text, the absent-module copy, the alert as glyph plus text, the 44px sweep,
 keyboard path, axe A+AA under `#app`.

#### 7. Open questions, and the rulings

 **All three were ruled on 2026-10-07 before the build.** Kept here with the ruling, because this file is
 where a later slice reads why the shape is what it is.

 1. **`career_goals` in the matrix: APPROVED**, config only: one `panel_labels` row plus the `ura_finale`
    flag. The owner also confirmed the refusal to register against `race_calendar`, which Unity Cup and
    Trackblazer both turn on.
 2. **Deadline window: derivable form only.** Due or overdue means `is_mandatory = 1`, the recorded turn
    has arrived or passed, and the run records nothing for it. Copy reads **"due and not recorded"**, never
    "inside the deadline window"; an N appears nowhere, so none is invented. The level stays design-2.0
    §4's Level 1 Critical: emphasis, icon, placement, word carried as text, never colour alone.
 3. **Character goals: absent.** E2 renders the named absence in design-2.0 §29's three parts and does
    **not** open the `trainee_goals` schema in this slice. The same three-part shape carries the Happy
    Meek, progression, duel, reward and final-race-contribution modules.

##### Decided while building, and why

 1. **`AlertRow`'s `warning` tone was dead, not merely unused.** E1 mapped it to `text-warning`, and
    `--color-warning` does not exist in `resources/css/app.css`, so Tailwind never emitted a rule for it;
    both callers passed `note`, which is why no run could see the difference. E2 replaced the branch with
    `critical` on `text-ink-strong` plus weight rather than leaving a dead class beside a new one.
    Recorded at `SCREEN_SPEC.md` `SCR-CAR-019`.
 2. **`career_goals` sits last in `panel_labels`.** Inserting it first renumbered the D17 session's
    `DatabaseTest.php:104` pin on `panels.0.label`, in a file that is untracked and not E2's to edit.
    Ordering the new flag last keeps that test honest and untouched.
 3. **The `completed` arm has no browser case.** Proving it needs a race written through `SCR-CAR-013`'s
    screen; the props test covers it and the spec covers the three states a turn write can reach. Stated
    as a gap rather than papered over by POSTing a race entry.

 ---

### 9.5 E5 — Scenario Race Planner (SCREEN-017): slice plan

**Authorization.** §4's E5 row (race facts only, **no** win probability, SCREEN-017, depends on D8)
and §9's E5 entry. D8 landed at `e8f75bd` and D10 at `55be0cd`, so both dependencies are met.

**What it is.** A Cockpit sub-page, not a panel: it descends from the run's URL the way D9 to D15 do
(`GET /training-runs/{run}/races/planner`, `runs.races.planner`), because §4 puts E5 on D8 rather than
on E1, and because the brief's four groups need a page's worth of room. It registers no widget and no
panel flag, so the E1 registry and `ScenarioPanel.vue` are untouched.

**Files.** `app/Http/Controllers/Career/RacePlannerController.php` (new); `routes/web.php` (one GET,
beside `runs.races.decision`); `resources/js/pages/Career/RacePlanner.vue` (new);
`resources/js/components/career/RacePlanList.vue` (new); `app/Services/RaceFacts.php` (new — the
brief's ten-field list, moved out of `RaceDecisionController`, which keeps its behaviour and loses its
private copies, so the two screens cannot drift on the list they both print);
`resources/js/pages/Career/RaceDecision.vue` (one door link to the planner, so the page is not
URL-only — D14 ruled URL-only reachability a defect); `tests/Feature/CareerRacePlannerTest.php` (new);
`tests/browser/career-race-planner.spec.ts` (new); `SCREEN_SPEC.md` (`SCR-CAR-023`, the matrix row and
the section with its state table).

**Props contract** (explicit arrays, never Eloquent models):

```text
run        { id, trainee, trainee_ja, scenario_label, status_label, run_url }
nextTurn   { year_label, turn, position_label } | null
groups     { mandatory, upcoming, optional, rival }   each:
           { key, label, definition, empty: string|null, races: Race[] }
           rival is null when the run's scenario is not declared (no calendar at all)
comparison { rows: [{ key, label, title }] }          the aligned row labels, in §46 order
alignment  { recorded: bool, absence: string|null,
             rows: [{ key, label, value: 'Matches'|'Does not match'|'Not recorded', title }] }
held       { win_probability: {label,title}, expected_risk: {label,title} }
deadline   { next: {id,title,year_label,turn,state}|null, alert: {text,detail}|null }
entry      { action, turns: [{id,turn}] }
Race       the D10 `RaceCard` shape, plus `comparison: [{key,label,value,title}]`
```

**The four groups, and the rule that defines each.** One calendar query per page
(`forScenario()->orderBy(year)->orderBy(turn)->orderBy(sort_order)`, one eager load of the run's
entries), partitioned by two citable facts — `is_mandatory` and `RaceCatalogSlot::isAtOrBeforeTurn()`
(the single owner of the position comparison, `ADR-0015`):

| Group | Rule | Empty state |
| --- | --- | --- |
| `mandatory` | `is_mandatory = true` (7 of 410 rows) | none needed; the catalogue always carries them |
| `upcoming` | `is_mandatory = false` **and** the turn has not arrived | named: nothing is ahead of the run |
| `optional` | `is_mandatory = false` **and** the turn has arrived and the run recorded no entry | named: every reached race was entered |
| `rival` | rows the run's scenario itself authors | named absence: no column marks a rival race |

**Reward comparison.** One real `<table>` (design-2.0 §46, the `Legacy/Compare.vue` pattern: `<th
scope="row">` per property, `role="region"` + `tabindex="0"` scroll container), columns are the races
the Trainer ticks, capped at four. Rows: Grade, Distance, Distance band, Surface, Fan gate, Fan gain,
Reward, Skill Points, Grade Points, Shop Coins. Only the first five and Grade Points have a source;
the rest are `N/A` with a `title`. Grade Points comes from `TrainingRun::gradePointsForTier()`, which
returns null when the run's scenario defines no `grade_point_by_grade` table, so the row is filled by
config presence and never by a scenario name.

**Target alignment.** `BuildTargetPayload` against the race: distance band, surface and style, each
`Matches` / `Does not match` / `Not recorded`, with `ProvenanceBadge` `calculated`. Style has no race
column (it is the trainee's aptitude), so it is always `Not recorded` with that reason; a run with no
target records nothing and says so.

**Held figures.** `Win probability: N/A` and `Expected risk: N/A`, each with a `title` naming the
`ADR-0016` blocker. The brief's recommendation block is not built, and no LOW / MEDIUM / HIGH wording
appears (design-2.0 §21 is read as the thing to refuse).

**Deadline awareness.** The next uncleared mandatory race and its turn, plus a Level 1 `AlertRow`
(`tone="critical"`, E1's own component) when `isAtOrBeforeTurn()` says its turn has arrived. The copy
states the next obligation and its turn; it never claims "no critical training deadline will be
missed".

**Tests.** `CareerRacePlannerTest` — each group present and absent, the `N/A` fields with their
titles, a four-race comparison with aligned rows, the three alignment values, the held pair naming
`ADR-0016`, the Critical alert on a reached obligation, and the no-percent assertion over the Vue
files and every prop. `career-race-planner.spec.ts` — the keyboard path through the groups and the
comparison table, the `N/A` titles, the Critical alert text, 320px reflow, the 44px sweep, axe A + AA.

**Open questions for the owner.** (1) "Optional" is the brief's own word; the plan defines it as
"reached, non-mandatory, not entered" because no other reading is non-overlapping — is that the
intended set? (2) The rival group has no source: the corpus says rival appearance is random and gated
on the trainee's aptitude, and no column marks one, so the group renders a named absence. Should a
rival marker column be added instead? (3) `race_catalog_slots` tags the Grand Concert final
`grand_concert` while `config/scenarios.php` keys it `our_grand_concert`, so `forScenario()` cannot
match that row; this is pre-existing and shared with D10, and E5 neither fixes nor depends on it.

_Dated close-out 2026-10-08 (documentation-sync pass; the plan body above is preserved as written):
E5 executed this slice plan and landed it as `SCR-CAR-023`, with `Career/RacePlanner.vue`,
`RacePlannerController`, `components/career/RacePlanList.vue`, `app/Services/RaceFacts.php` and
`runs.races.planner` (wired at `routes/web.php:133`); the §4 table's E5 row records the gate
evidence (`CareerRacePlannerTest`, `career-race-planner.spec.ts`). Two of the three owner questions
are the ones the landing records as still open: question (1)'s "optional" reading is the definition
the page shipped with, and question (2)'s rival group is a named absence because no column marks a
rival race. Question (3) — the Grand Concert final's `grand_concert` vs `our_grand_concert` key
mismatch — is the defect `KNOWN-ISSUES.md` files as **KI-71** (OPEN, filed 2026-10-07 by the E5
hand-off), whose closure is the owner's call between the parser's map and the config key; E5 neither
fixed it nor depends on it, exactly as the plan body says._

---

### 9.6 F2 — the record screen's write owners: slice plan

**Authorization.** The owner's F1 ruling of 2026-10-08. Ruling (a) is the direction: the 2.0 surfaces
own these writes. The redirect of `GET /training-runs/{run}` flips only once each of them has an owner
reachable from inside the Cockpit, and the flip lands with the test sweep in the same commit. This
section is the plan the ruling asked for; the ruling that accepts it is the owner's to write.

**What the 0.1.0 record screen still owns, measured 2026-10-08.** The ruling named four writes. Reading
every write target in `Runs/Show.vue` and `components/RacePanel.vue` (the two files that mount only on that
page) and checking each against every `.put(`/`.post(`/`.delete(` in `resources/js/pages/Career/`,
`components/career/` and `components/scenario/` finds **nine controls on seven routes** that no 2.0 surface
offers. Every route below already exists and its Form Request already validates; nothing here is a new
write path.

| # | Control | Route (and validator) | Where it lives today | In the ruling's four? | Size |
| --- | --- | --- | --- | --- | --- |
| 1 | Run status change | `PUT runs.update` (`StoreTrainingRunRequest`) | `Show.vue`'s `Change status` form | yes | small |
| 2 | Scenario assignment | `PUT runs.update` | `Show.vue`'s scenario form | no - found by measurement | small |
| 3 | Grade-point period | `PUT runs.update` | `Show.vue`'s period form | no - found by measurement | small |
| 4 | Correct a recorded turn | `PUT runs.turns.update` (`StoreTurnEntryRequest`, the same one `runs.turns.store` uses) | `Show.vue`'s per-row edit forms | yes | substantial |
| 5 | Delete a recorded turn | `DELETE runs.turns.destroy` (no Form Request; the controller scopes the row to the run and deletes its failure event in one transaction) | `Show.vue`'s per-row delete | yes | rides along with 4 |
| 6 | Free-race entry | `POST runs.races.store` with `entry_mode=manual` (also writes a `scenario_slots` row of kind `free_race`) | `RacePanel.vue`'s manual disclosure | yes | medium |
| 7 | Skill acquisition status | `POST runs.skills.sync` | `Show.vue`'s skills form | no - found by measurement | medium |
| 8 | Export CSV / JSON | `GET runs.export/{format}` | `Show.vue`'s header links | yes | small (links only) |
| 9 | Delete the run | `DELETE runs.destroy` | `Show.vue`'s disclosure | no - found by measurement | small |

Rows 1-3 share one route and one validator but are three different controls with three different
consequences, so an owner that hosts one does not host the others. Row 9 is the one a flip would strand
hardest: after `runs.show` redirects there is no UI anywhere that deletes a run, and `tests/utils/delete-run.ts`
reaching the route over HTTP is a spec's teardown, not a Trainer's door.

**Two premises the ruling carried, corrected against the tree.**

1. _Turn-row edit is partly owned already._ `CockpitController::correctionSection()` posts to
   `runs.turns.update` and `Cockpit.vue` renders the form, but only for `$latest` - the most recent turn.
   `career-cockpit.spec.ts`'s "records Energy through the cockpit correction" case walks that path. What row
   4 above lacks an owner is an **arbitrary** recorded turn's edit, so the gap is narrower than "turn-row
   edit has no 2.0 owner", and the existing correction block is the pattern the new owner should follow
   rather than a second correction UI.
2. _The browser half of the blast radius is already closed._ The nine cleanups the first F1 report named are
   twenty-three, and all of them now go over HTTP, so the flip's sweep is a feature-test sweep only.

**Non-goals.** No new write route, no new Form Request, no migration, no package, no token. Do not
redesign the Cockpit or any 2.0 screen: giving a write an owner is the whole scope. Do not delete
`Runs/Show.vue`, `RacePanel.vue`, or any assertion of the 38 files below until the owners are reachable
from inside the Cockpit.

**The flip that follows this slice** (one commit, with the redirect line):

- The 38 feature files that assert `component('Runs/Show')` (158 assertions, re-measured 2026-10-08):
  `FlashBannerTokensTest`, `FreeRaceCalendarCellTest`, `FrontendAuditFixesTest`,
  `FrontendComponentLibraryTest`, `GoalPanelsOnRunDetailTest`, `GradePointMeterTest`,
  `GradePointPeriodTest`, `GuidedFirstTurnTest`, `GuidedStepScenarioCompositionTest`,
  `GuidedStepScenarioVariationTest`, `GuidedTurnOnRunViewTest`, `GuidedTurnValidationTest`,
  `MoodPillTest`, `RaceCalendarTest`, `RaceCalendarYearTabsTest`, `RaceCatalogPickerTest`,
  `RaceEntryCirclesTest`, `RaceEntryDisclosureTest`, `RaceEntryTurnLinkTest`,
  `RenderedCopyHygieneTest`, `ResourceStripOnRunDetailTest`, `ResourceStripTest`, `RunDeckTest`,
  `RunGoalsPanelTest`, `RunSaveConfirmationTest`, `RunSkillPickerTest`, `RunSkillRowLabelsTest`,
  `RunStatusControlTest`, `RunUpdateTest`, `RunViewErrorEnvelopeTest`, `RunViewFrameTest`,
  `RunViewNoScriptTest`, `ScenarioPanelSchemaTest`, `ScenarioPanelUiTest`, `ScenarioStatCapsTest`,
  `SkillsFetchTest`, `TrainingRunTest`, `TurnRowActionsTest`. Per file: retire the assertion with the
  screen, or move it to the 2.0 owner that now carries that surface's behaviour. Deleting a test needs
  the owner's approval (AGENTS.md §9, §14), so the disposition list goes in the hand-off before the
  redirect line is touched.
- The copy sweep, made test-aware in the same commit: `SCREEN_SPEC.md`'s SCR-RUN-003 row and section,
  `README.md`'s route table, and any `runs.show` label that now reads as a redirect rather than a page.
- Already resolved, so they are not part of the sweep: the browser cleanups (23 specs' `afterEach`
  deletes now go over HTTP through `tests/utils/delete-run.ts`), the Career Result door
  (`run.result_url`, rendered in the Cockpit's left column), and `CareerSurfaceCutoverTest`'s Veteran-row
  pin, which now asserts that a Veteran row's link resolves to a career screen that renders
  (`Runs/Show` today, `Career/Cockpit` after the flip) rather than pinning `runs.show` forever.

**The `run_url` producers that become self-loops at the flip.** Each of these prints the 0.1.0 record
screen's URL from a 2.0 surface, so after the redirect they loop back through it. Each needs a
disposition in the flip commit, and the owner's ruling of 2026-10-08 named two options for the first one
(`runs.cockpit`, or a non-run Veterans surface) — the rest are the same question:

- `DashboardController::veteranRow()['run_url']` — a Veteran row is a career-selection link, and F1's rule
  says no normal navigation path may open the 0.1.0 page. `CareerSurfaceCutoverTest`'s pin no longer
  hardcodes it, so this is now a product decision rather than a test edit.
- `CockpitController::runSection()['run_url']` — the Cockpit's own "Run record" door, which the action grid
  and `CareerLayout` both route through today.
- `Career\RaceDecisionController`, `Career\TrainingDecisionController`,
  `Career\SaveVeteranController` and `VeteranController` each print a `run_url` on the record screen, and
  `Career\CockpitController` prints three: `runSection()`, the action grid's `default` arm for the entries
  with no 2.0 screen yet, and `strip.links.run_url` on the race strip. `Career\ResultController` is already
  off the list: it points its own `run_url` at `runs.cockpit`, which `CareerResultTest` pins.
- `TrainingRunController`'s own write redirects and `back()` fallbacks (`store`, `update`, `destroyTurn`,
  `syncSkills`, `syncDeck`, `storeRace`, `storePurchase`) land on `runs.show`. They are the legacy
  surface's destinations today; the flip makes each one a redirect hop, so each is a disposition too.
- 2.0 empty-state copy that instructs the Trainer to use "the run record screen" (Race Decision's refusal
  line, E5's history line). This is copy, so it sweeps with the screen's behaviour and with the browser
  specs that assert those sentences.

**Verification state of the migration, as the plan holds it.** The patch should be self-consistent; it is
**not verified as green**. Static verification holds: all imports in the 7 patched specs resolve to tracked
files or to the new helper, the patched set compiles, and no patched file carries a peer hunk. Individual
specs run green with the helper: `run-detail`, `veteran-compare`, `career-preflight` (first case),
`career-cockpit`, `career-surface-cutover`, `trackblazer-panel`. Five specs have never executed since the
migration (`career-preflight`'s remaining cases, `career-save-veteran`, `dashboard`, `legacy`,
`support-deck`), and three browser attempts were voided - one by gate runs on the box beside the pass, two by
reaping or deleting a harness while its own pass was live. The claim upgrades only when §4.1's harness state
clears and the five-spec pass runs start to finish with nothing else on the box.

**Suite rule this slice should record.** A browser spec's teardown goes through
`tests/utils/delete-run.ts`. A spec must not reach a product write through a screen it is not asserting;
if a case needs state, it uses the app's own write route, the way `career-skills-planner.spec.ts`'s
build-target PUT and the eleven turn writes do. `KNOWN-ISSUES.md` KI-76 is the other standing hazard for
these specs: while a session's Vite dev server is hot, an Inertia render POSTs to a dead SSR endpoint with no
timeout and answers 500 after 30s, so a browser pass must boot its harness with `INERTIA_SSR_ENABLED=false` or
the run is void before it starts.

**Tests.** Per owner: one feature assertion that the write lands when posted from the 2.0 surface (the
props that change, the redirect target, and the row), and one browser case that the control is reachable
by keyboard from inside the Cockpit and prints its own state afterwards. No test asserts the legacy
screen's copy on the way.

**Open questions for the owner.** (1) Does the status change belong on the Cockpit header, where it is
always visible, or on the Career Result surface, where a finished career is summarised? Ruling (a) names
both. (2) Turn correction on the Timeline (D14) changes that screen's contract: `routes/web.php` records
it as a read screen with no write route, so this is a real edit to a landed decision, not an added link.
(3) Free-race entry: `career/RaceCard.vue` posts only the calendar branch, so is the manual arm a second
arm on Race Decision or a drawer on E5's planner card? (4) Does the run delete keep a UI door at all, and
on which surface? (5) Rows 2, 3 and 7 were not in the ruling's list. Scenario assignment and the
grade-point period are both `runs.update` fields, so the same host that takes row 1 can take them at the
cost of two more fields on one form - but the period control is Grade-Point machinery and may belong with
the ladder that prints it. Skill acquisition status (`runs.skills.sync`) has no obvious 2.0 home: D13's
Skills Planner writes the build target, not the acquired/skipped marks. Confirm which of these three the
slice owns and which stay on the legacy surface with `runs.show` un-redirected for them.

**One recommendation per question, drafted for the ruling.** These are the options I would take and why;
accepting or replacing them is the owner's decision, and the plan is not executed until it is ruled. Each one
carries the cost of the option not taken, because that is the half the ruling has to weigh.

1. **Status control on the Cockpit header, not the Career Result surface.** The Result screen's own route
   block and controller state that it records nothing and has no write route, and its `Retired` state refuses
   even the Cockpit door, so a status form there contradicts a landed contract; the header already prints
   `status_label`, so a select replaces a label rather than adding a region. Cost of the alternative: a write
   lands on a screen whose contract says it records nothing, and a career that was abandoned is offered a
   control its own empty state then refuses.
2. **Arbitrary-turn correction extends the Cockpit's existing correction block; the Timeline takes no
   write.** `routes/web.php` and `TimelineController` both record the Timeline as a read screen with no write
   route, so putting a write there is a second contract change on a screen D14 just landed, while widening
   `correctionSection()` from `$latest` to a row the Trainer names reuses the form, the validator and the
   focus behaviour the Cockpit already has. Cost of the alternative: two correction UIs on two screens, and a
   read screen's documented promise reversed in the same phase it landed.
3. **The manual race arm goes on Race Decision, not the planner card.** `entry_mode=manual` creates a slot at
   a month and half, which is the turn-scoped question Race Decision already asks; E5's planner lists are
   partitioned by calendar position rules, so a hand-entered race would be a row those rules did not produce.
   Cost of the alternative: the planner's four groups start carrying rows their own definitions exclude, and
   the group rules stop being explainable in one line each.
4. **Keep a run-delete door, on the Cockpit, behind a two-step disclosure that names the cascade.** The
   delete is the one write whose consequence reaches outside the run (a Veteran files from its run and
   cascades with it, which `career-save-veteran.spec.ts` records), so the control belongs where the rest of
   the run's identity is edited and must state the loss in its own copy; the legacy disclosure's two-step
   shape is the pattern to carry, not a native `confirm()`. Cost of the alternative: after the flip the tool
   has no way at all to remove a career, and the only caller of `runs.destroy` left is a spec's teardown.
5. **Rows 2 and 3 ride with row 1; row 7 goes to the Skills Planner.** Scenario assignment and the
   grade-point period are `runs.update` fields on the same validator, so one header form with three fields is
   a smaller diff than three hosts and no new route. Skill acquisition status has no other candidate: D13's
   Skills Planner is the 2.0 screen that already renders the run's skills against its target, and it writes
   that run today. Cost of the alternative: three hosts for one validator, and the acquired/skipped marks stay
   editable only on the page being retired, so the flip drops the control with nothing to replace it.

**Close-out, 2026-10-09 (F2).** All five rulings were approved as recommended (R-1 approve retirement;
R-2 move assertions and retire the flow-only remainder; R-3 split per assertion). The flip landed:
`routes/web.php`'s `GET /training-runs/{run}` now calls `TrainingRunController::redirect()` and 302s to
`runs.cockpit`; `{run}` still binds, so a missing id 404s. `TrainingRunController::show()` and its
`showData()` payload stay in the tree, unrouted, pending a follow-up deletion of `Runs/Show.vue`.

- **Delta A.** This section claimed `Career\ResultController::run_url` already resolved to `runs.cockpit`.
  On the tree it resolved to `runs.show`; it was repointed to `runs.cockpit` in this slice, as were the
  other Career producers and the `TrainingRunController` write redirects.
- **Delta B.** The 38-file list was re-measured to **40**: `PerformanceTurnInputTest` and
  `TrainingDetailPerformanceBoundaryTest` are untracked and post-date the list, so the sweep extended to
  them. `CareerSurfaceCutoverTest` is excluded per this section's own note that its Veteran-row pin is an
  invariant, not a literal.
- **Delta C (new).** The 40-file list was drawn from `component('Runs/Show')` assertions only. It missed
  tests that reach the legacy page by literal URL: `StatBandTest`, `ShopPurchasePayloadTest`,
  `RunViewTargetSizeTest`, `RosterOrderingTiebreakerTest`. The first two were repointed/moved; the last two
  carry legacy-only assertions (`StatBand`/`GradeBadge` props; the retired skill-select `ORDER BY`) with no
  2.0 subject and are reported to the owner for a retirement ruling rather than deleted unilaterally.
- **`TrainingRunController.php:1014`** (`run_url` in the Legacy builder) is intentionally left on
  `runs.show`: the builder is out of F2's scope and the redirect resolves it to the Cockpit without a loop.
- **Shared-tree blocker.** The working tree carries a peer session's in-flight `Performance*` work and
  seeder/doc changes in the same paths F2 touches, so F2 is left uncommitted (see the F2 hand-off).

**Close-out follow-up, 2026-10-09 (F2 sweep-miss cleanup).** The assertions this close-out's Delta C left
behind are resolved across six files. Every failure the sweep caused is gone; one file stays red and its
premise is corrected below. F2 is still uncommitted, because the blocker in the bullet above has not
cleared.

**Group A, the deleted `GuidedStep.vue` (retired).** `KeyboardPathTest` retired the four cases that read
it: the shortcut advertisement, the document-listener registration, the radio-group roving, and the digit
guard. Its four `AppLayout`/`SetupLayout`/`NavGlyph` cases stand. Moving the retired four into
`CareerCockpitTest` was ruled out rather than attempted: the Cockpit's correction form is a turn `select`
and a Save button with no digit, arrow or Escape path, so the interaction R-2 retired has no successor to
inherit them. `TokenPairHygieneTest` retired its one case, because `AcquisitionStatusEditor.vue` carries
neither token (it renders `bg-chrome`/`text-on-chrome`) and no surviving component holds both halves of the
pair in one file, so there was no subject to repoint at. The rule's two live halves are intact and
unorphaned: `--color-on-green` stays declared at `resources/css/app.css:69` with its `DesignTokensTest`
pin, and that file's sweep still refuses the borrow onto a bare `bg-green`.

**Group B, the deleted `Runs/Show.vue` (one repointed, four retired).** `SkillSearchScreenTest` repointed
at the shell nav item `resources/js/layouts/AppLayout.vue:112`, now the only `/skills` door. The repoint
this section proposed, at the Cockpit's skill surface, does not exist: `Career/Cockpit.vue` carries no
skills link and `Career/CockpitController.php` ships no skills prop. `TurnEntryFieldSourceTest` dropped its
run-record half and kept the cockpit half, which holds at `Career/Cockpit.vue:34`.
`TurnLogScrollRegionTest` retired both cases and stays in the tree as a retirement record rather than being
deleted, since deleting a test is the owner's call; both subjects are gone, the turn-log wrapper with the
page and `RaceCalendar.vue` unmounted with it. The KI-25 convention is unchanged, its two live instances
are named in that file and in the dated note now carried by `SCREEN_SPEC.md` SCR-RUN-003 against the
accessibility line that named the region.

**Group C, `StatBandTest` (red, premise corrected).** This file was handed over as peer-owned, blocked on
a `StatBand.vue` mid-migration into `CareerStatePanel.vue` and `TrainingCard.vue`. The tree disagrees.
`resources/js/components/StatBand.vue` carries no working-copy change and its last commit is `7a81c7c`
(2026-10-09); `resources/js/components/career/TrainingCard.vue` is the path with the peer hunk, at one line;
and `CareerStatePanel.vue:16-18` states "StatBand was deliberately not reused here". So its four failures
are the same class as groups A and B, `test()->get('/training-runs/'.$run->id)` answering the new 302, and
no repoint exists because the Cockpit ships none of the props they read (`gradeBanding`, `baseCap`,
`hardCap`, `band.*`, `caps`). The file is untouched and red, which is the retirement ruling Delta C asked
for and did not deliver; those four need the owner's word, not a repoint. Its three green cases read live
subjects (`GradeBadge.vue`'s fill map and `ScenarioCaps::caps`' unknown-scenario refusal). Its docblock
still describes the retired page, and the ruling should carry that too.

**Gate results.** `php artisan test --compact`: **4 failed, 2 skipped, 1463 passed (25905 assertions),
418s**, the four being `StatBandTest` and nothing else. Scoped to the five files this pass
touched: **24 passed (314 assertions)**. `vendor/bin/pint --test --format agent` on those five: passed, after
one `single_blank_line_at_eof` in `TokenPairHygieneTest` was fixed. `vendor/bin/phpstan analyse` was not run
in its configured scope, and none of this pass's files would be seen if it were: `phpstan.neon:5-6` restricts
`paths` to `app`. `composer lore`, `composer lore-code`, `npm run typecheck` and `npm run build` were not run,
because no shipped string, no TypeScript and no Vue file changed. `npm run test:browser` was not run, and
`tests/browser/run-detail.spec.ts:331` asserts `getByRole('region', { name: 'Turn log' })` against the page
that now redirects, so that spec is expected to fail and is outside the six files this pass was scoped to.
`migrate:status` was not run: no migration is in this pass and no table is read differently.

**Two findings outside this pass's scope, neither acted on.** `RaceCalendar.vue` and `RacePanel.vue` are
orphaned by the deletion above: nothing under `resources/js` imports either, and `RacePanel.vue:49` takes a
`composesRaceCalendar` flag it never renders, joining `StatBand.vue` and `GradeBadge.vue` as four components
the retirement ruling has not yet claimed. And `tests/browser/run-detail.spec.ts` still drives the redirected
page. Both warrant `KI-nn` entries; neither was filed here because `KNOWN-ISSUES.md` carries a peer session's
staged hunks on this tree.

**Blocker, restated.** Option A has not cleared. A peer hunk now sits inside an F2 path:
`app/Http/Controllers/Career/CockpitController.php` carries the Slice 23 `finale` to `finale_structure`
rename beside F2's own edit, so that file cannot be committed for F2 alone. Peer paths holding hunks:
`app/Domain/Career/CareerPosition.php`, `app/Actions/CreateSnapshotRun.php`,
`app/Http/Controllers/Career/SnapshotController.php`, `app/Http/Requests/StoreSnapshotRequest.php`,
`resources/js/components/career/TrainingCard.vue`, `resources/js/pages/Career/Snapshot/{Entry,Setup}.vue`,
`KNOWN-ISSUES.md`; untracked peer work at `resources/js/pages/Career/Snapshot/Review.vue`,
`app/Enums/{SnapshotFieldState,PerformanceType}.php` and `app/Models/TurnEvents/PerformancePayload.php`.
The tree moved three times during this pass, and a peer unstaged `app/Services/Scenario/FinaleReader.php`
and recreated `tests/Feature/CockpitFinaleStripTest.php` mid-flight.

**Addendum, 2026-10-09 (F2 residuals, rulings, and peer drift).** The passes below ran after the
close-out above, and one sentence in it is now superseded.

- **Part 1.6.1 retired `tests/browser/run-detail.spec.ts`.** The whole file was evidence for
  SCR-RUN-003, which the flip retired, and every case's subject was either gone or already covered
  elsewhere: the `Turn log` region, the guided rail's keyboard path and the 44px sweep of the legacy
  controls died with `Runs/Show.vue`; the Unity Cup prose is asserted in `unity-cup-panel.spec.ts`;
  the Trackblazer prose and the shop purchase flow, including the price-refusal case, are asserted in
  `trackblazer-panel.spec.ts`. Deleting the file is Fix A applied to all eight of its cases.
- **Part 1.6.2 retired `--color-on-green`.** The Group A paragraph above says the token "stays
  declared at `resources/css/app.css:69` with its `DesignTokensTest` pin"; that sentence is
  superseded. The token's only consumer was the low-Energy advisory badge on the guided rail, no
  plan or design document names another, and a declaration cites a use, so it was removed with the
  declaration. `DesignTokensTest` counts 59 tokens now, and `TokenPairHygieneTest`'s declaration pin
  retired with it. The sweep that refuses the borrow onto a bare `bg-green` remains.
- **Ruling R-A1 retired `tests/Feature/StatBandTest.php`.** The correction above stands: the file was
  not peer-owned, `StatBand.vue` was clean at `7a81c7c`, and `CareerStatePanel.vue:16-18` refuses the
  reuse, so there was no 2.0 subject for its four 302 failures.
- **Ruling R-B1 retired four orphaned components**: `StatBand.vue`, `GradeBadge.vue`,
  `RacePanel.vue` and `RaceCalendar.vue`, all zero-importer after `Runs/Show.vue` was deleted.
  `RacePanel.vue:49` kept a `composesRaceCalendar` flag it never rendered. The delta: retiring
  `RaceCalendar.vue` broke `RaceSlotPanelComposerTest`, which read its `stateClass` map as the
  reference for the model's cell states. That file keeps its model-behaviour cases and a
  retired-case comment; only the three source reads went, because `RunRaceStrip.vue`, the Cockpit's
  calendar, carries none of the cell treatment.
- **Peer drift, resolved in F2's own file.** `1ec92ab feat(turn): energy state as exact/band/unknown`
  changed `TrainingDetail.vue`'s import and replaced its inline `energy` field with a three-state
  control, leaving HEAD red on `TurnEntryFieldSourceTest`. That file is F2-touched, so its two stale
  assertions were adapted to the tree rather than left: the import check is now a substring on the
  shared list plus its domain path, and the `energy` row check asserts the shared
  `TURN_ENTRY_ENERGY_STATES` / `TURN_ENTRY_ENERGY_BANDS` constants it reads now. The peer-owned
  paths themselves were not touched.
- **The commit (Part 1.7) stays blocked.** Option A has not cleared: the peer paths are still dirty
  in the working tree. The Slice 23 rename landed as `aa5d975 refactor(career): give the finale one
  word per question on both screens`, but `app/Http/Controllers/Career/CockpitController.php` and
  `Career/ResultController.php` still carry staged and unstaged changes, and the tree is 22 commits
  ahead of `origin/master` with newer peer work (`SnapshotController` at `a31000c`,
  `config/scenarios.php` at `1098546`, `composer.json` newly dirty). F2's changes stay uncommitted;
  the commit needs the peer working-tree hunks to land first, or an isolated worktree (Part 1.7.3 B1).
- **Parts 2 and 4 were already delivered by peer commits, and Part 3 closed eight register entries.**
  The KI-45/KI-71 scenario-key backfill is in the tree (`GametoraRaceCatalogParser.php:163` publishes
  `scenario_key`; `RaceCatalogSlot::scopeForScenario` includes the null-scenario rows; `480b711`
  corrected the Grand Concert key), so Part 2.2 had nothing left to change and Part 2.3's slices stay
  evidence-blocked. The two Part 4 browser suites exist: `tests/browser/career-cockpit-forms.spec.ts`
  carries all eleven cases, and the Timeline read-only case is at `tests/browser/career-timeline.spec.ts:128`.
  Part 3 closed KI-72, KI-73, KI-75, KI-76, KI-77, KI-78, KI-79 and KI-80 in `KNOWN-ISSUES.md`, each
  because its fix was already in the tree and only the register entry was stale; KI-69 stays open and
  peer-owned, because the two named commits do not evidence ruling 6a's halves.

---

## 9a. Scenario Decision Support — Shared Contract and Scenario-Specific Slices

**Authority:** this section refines Phase E's panel contracts with a shared decision-support layer. It does not override `config/scenarios.php`, `ADR-0020` §3 (held advice), or the Global Constraints above. Every proposed calculation must be sourced; every panel must use the shared contract without importing another scenario's rules.

### FD-1 — Audit current scenario capability contracts

Inventory the actual scenario configuration, available state, existing components, controller props, and write paths. Mark each capability as shipped, partial, missing, blocked, or not applicable.

Exit condition: no task duplicates existing functionality or assumes a backend field that does not exist.

### FD-2 — Define the Next Decision contract

Define the input state, candidate actions, eligibility checks, objective priorities, explanation shape, provenance label, and output contract. The decision logic must live in a backend service or a dedicated composable, never inside a presentation component.

Exit condition: unit tests demonstrate deterministic behavior for equivalent inputs and explicit handling of insufficient information.

Required shape of the Next Decision component:

```
CURRENT PRIORITY
  Prepare for the next mandatory objective

RECOMMENDED ACTION
  [ Train Stamina ]     primary, supports the upcoming race

ALTERNATIVES
  [ Race a G3 (Mile) ]  +GP, -energy, delays Stamina block
  [ Rest ]              clears fatigue, loses a training turn

WHY THIS RECOMMENDATION
  - Objective: Valentine's gate in 14 turns (60k fans / 40k dirt)
  - Constraint: Stamina floor for Medium = 600–700; current = 520
  - Trade-off: +6 Sta this turn vs. +GP from a race you do not strictly need yet
  - Confidence: Calculated (facility level by repetition, config `facility_level_source`)
  - Switch if: Summer camp starts in 4 turns, or Happy Meek appears on a Stamina tile
```

### FD-3 — Add objective and deadline context

Reuse existing race, goal, and scenario state where possible. Implement the shared presentation for the next objective, due date/turn, completion status, and consequences of missing it — using only verified or explicitly estimated data.

Exit condition: correct display and state transitions for upcoming, completed, missed, and unknown deadlines.

### FD-4 — Close the action reconciliation loop

Ensure the user can record the selected action and actual outcome, inspect the previous state, and see the updated run state and next recommendation. Undo, if implemented, must go through a defined, tested state-restoration contract.

Exit condition: a recorded action persists correctly, the history remains consistent, and recalculation uses the updated state.

### FD-5 — Strengthen scenario-specific panels

Add only the missing Unity Cup team-decision, URA milestone/readiness, and Trackblazer schedule/resource behaviors identified by FD-1. Each slice specifies its own state dependencies and acceptance tests.

Exit condition: the panels remain distinct in behavior while using the same shared UI and provenance contracts.

### Shared testing matrix

| Test dimension     | Required coverage                                                                       |
| ------------------ | --------------------------------------------------------------------------------------- |
| State              | Empty, complete, partial, stale, invalid                                                |
| Objective          | Upcoming, completed, missed, deadline unknown                                           |
| Recommendation     | Supported, tied alternatives, insufficient evidence, no eligible action                 |
| Action recording   | Valid submission, validation failure, persisted result, corrected result                |
| Scenario isolation | Each panel receives the right scenario state and no other scenario's rules              |
| Accessibility      | Keyboard-only operation, focus visibility, screen-reader labels, non-colour status cues |
| Regression         | Existing career, deck, skills, race, purchase, and veteran workflows remain intact      |

### Implementation guardrails

- Never implement a new calculation in Vue solely because it is convenient to display.
- Do not treat a scenario configuration flag as proof that its mechanics are fully modeled.
- Do not invent numeric training gains, failure percentages, race win probabilities, or shop recommendations.
- Do not claim a recommendation is optimal when the engine can only establish that it is feasible or preferable under incomplete criteria.
- Keep the existing test, accessibility, linting, type-checking, and build gates required by the plan.

These constraints matter because the design and screen specifications already defer unsupported calculations (`ADR-0020` §3, Phase E "Not built, and why"). New scenario UX must not accidentally authorize them.

---

## 9b. Grand Concert Implementation Principle (from `docs/Our-Grand-Concert-plan.md`)

Our Grand Concert is implemented as a sequence of narrow, independently verifiable slices. This principle extends to all scenario work:

> **Evidence → smallest truthful representation → canonical lifecycle → UI → tests → stop**

A roadmap item is not permission to implement every mechanic implied by its name. Every slice must read the authoritative project documentation first, search the existing codebase before creating new architecture, prefer existing models/controllers/UI surfaces, and stop when the assigned slice is complete.

### Evidence hierarchy

When implementing scenario mechanics, distinguish:

1. **Authoritative and verified** — safe to implement/render.
2. **Observed in the application but not formally documented** — may be represented only where the observation itself is trustworthy.
3. **Documented as unverified** — do not render or calculate as fact.
4. **Inferred/community/external mechanics** — do not silently promote to project truth.

This hierarchy is binding for all three scenarios. Grand Concert's blocked slices (Lessons, Songs, Live Bonuses, Promo Concerts) demonstrate it in practice: when evidence does not support a truthful representation, the slice documents the exclusion and adds a regression test rather than inventing mechanics.

### Grand Concert slice status (reference)

| Phase | Slices | Status summary | Reference |
|-------|--------|----------------|-----------|
| A — Performance | 1–7 | COMPLETE (observations recorded, no totals derived) | `docs/Our-Grand-Concert-plan.md` |
| B — Lessons | 8–10 | Slices 9–10 BLOCKED BY EVIDENCE | §§494–575, 783–808 |
| C — Songs | 11–13 | All BLOCKED BY EVIDENCE (23 Song titles unverified) | §§494–575, 783–808 |
| D — Live systems | 14–18 | Live Bonuses & Promo Concerts BLOCKED BY EVIDENCE | §§577–781 |
| E — Grand Concert + Intelligence | 19–22 | Slices 19–20 COMPLETE, 21–22 PLANNED | §§784–878 |
| F — Acceptance | 23–24 | COMPLETE (acceptance record 2026-10-09) | §§881–1011 |

### Global out-of-scope rules (from Grand Concert plan)

These rules apply to every remaining slice unless a future slice establishes verified evidence:

- Do not invent game mechanics, numeric costs, starting values, caps, Hype formulas, Live Bonus percentages, Song effects, Lesson effects, or unsupported Performance conversions.
- Do not create speculative recommendation logic or duplicate models/tables/endpoints.
- Do not introduce a new frontend framework, perform broad refactors, or redesign unrelated screens.

### Repository safety constraints

Protected areas — must not be modified unless explicitly authorized by a separate task:

```
config/database-safety.php
app/Services/DatabaseSafety/
tests/browser/global-setup.ts
playwright.config.ts
.scratch-uma/incident-2026-10-08-devdb/
.scratch-uma/db-recovery-2026-10-08/
composer.json
composer.lock
D:/Projects/uma_musume_race_planner/
```

### Slice completion contract

Every slice ends with a report containing: Determination, Evidence, Files changed, Architecture decision, UI behavior, Mechanics explicitly not implemented, Tests and gates actually run, Safety/concurrency confirmation, Git/commit status, and Explicit stop.

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
   _Dated correction 2026-10-08 (documentation-sync pass): the risk is closed, not by an exception but
   by the row being met for every slice. `SCREEN_SPEC.md` §3 carries `SCR-CAR-001` through
   `SCR-CAR-024` and `SCR-VET-001` through `SCR-VET-004`, each with a §4 section whose Status line
   names the slice that shipped it, and the `SCR-CAR-0NN` numbers in the §4 body match the §3 matrix —
   the "one screen, one §4 section" rule this risk warned about is now held by the file itself._
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

**Landed 2026-10-07 (D18):** `role="status"` added to the `AppLayout` flash `<p>` (WCAG 4.1.3); the
explicit `aria-live="polite"` on the same element was dropped as redundant, because `role="status"`
already implies an `aria-live` of `polite`. The flash banner's accessible name is its text content,
asserted by `tests/browser/preferences.spec.ts:48` (`getByRole('status', { name: 'Preferences saved.' })`).

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

| Date       | Change                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           | Reason                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| ---------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 2026-10-05 | Filed. Derived from `design-2.0.md` + `screen-spec-2.0.md`; bound to real routes, controllers, models, `config/scenarios.php` and gates.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         | Owner asked for a comprehensive frontend development plan from the two 2.0 design docs, grounded in the repository's knowledge corpus.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| 2026-10-05 | Added §12 (WCAG 2.2 AA conformance), §13 (Laws of UX rubric), §14 (Phase A0 remediation of the 0.1.0 screens); strengthened the Global Constraints accessibility bullet and the §10 definition of done.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          | Owner required the 2.0 UI and the in-tandem 0.1.0 UI to meet WCAG 2.2 AA and the UX laws, not just carry the old Blade behavior forward.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
| 2026-10-05 | §1 inventory rewritten from the tree (12 pages, 7 components, 8 specs, 26 Blade files). Phase A status block added under §4. A1, A3, A4a and A4c recorded as landed with their deviations; A4b re-sized in §5.5.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 | The inventory had stopped tracking the ports after the first four pages, so the plan read as if A1–A4c were unbuilt. §5.5's A4b row also over-stated the work by assuming a Vue twin per Blade component; the read shows one consumer each.                                                                                                                                                                                                                                                                                                                                                                                                                 |
| 2026-10-05 | A4b recorded as landed in §4 and §5.5, B1 unblocked in §6 with its measured zero-consumer set, §1 re-measured (13 pages, 25 components, 9 specs, 15 Blade files), and the three Phase-A checklist rows closed.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   | The slice landed with a 19-component port, ~24 migrated test files and two behaviour changes that needed writing down where the next slice will read them, not only in a commit message.                                                                                                                                                                                                                                                                                                                                                                                                                                                                    |
| 2026-10-05 | §4's status block renamed to Phase A–C: B1 recorded as Landed with the eleven Blade files it deleted and the three its measured set had missed, and C1, C2 and C3 recorded as Landed with the shapes that shipped. §6 gains the matching landed note.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            | The block still said B1 was Ready and that no Phase B–E slice had begun, neither of which held once B1 and the Phase C slices committed. §4 is where the next slice reads to decide what to start, so it could not stay a step behind.                                                                                                                                                                                                                                                                                                                                                                                                                      |
| 2026-10-06 | §8 gains its first two bite-sized Phase D slice plans as §8.1 (D1, the Dashboard enrichment: files, props contract, eight decisions, tests, three open questions) and §8.2 (D2, Scenario Selection and `SetupLayout`: the wizard contract D3–D7 inherit, the session-draft persistence decision with its rejected alternatives and its stated two-tab ceiling, the config-derived card table, files). §8's D1 row is amended to match §8.1 decision 5, which drops the legacy-goal gaps panel.                                                                                                                                                                                                                                                                                                                                                                                                                                   | Both plans existed only as untracked `.scratch-uma/` working files while their slices were in flight in the tree; §8 promises "a fresh bite-sized plan per later slice", so the plan document is where they belong and the scratch copies are now duplicates. Embedded verbatim with headings demoted one level; nothing in either plan's body was edited, and each plan's title line was re-titled and re-levelled into the §8.1 / §8.2 headings. Their `SCREEN_SPEC.md` `SCR-CAR-*` rows and the two `tests/browser/` specs are the slices' own deliverables and are not restated here.                                                                   |
| 2026-10-06 | §8 gains §8.4 (D3, Trainee Selection and Trainee Profile: the wizard contract as shipped, the measured eight-filter roster table with the two omissions, the sixteen-row profile sections table, reuse, files, four reports) and §8.5 (D4, Build Target and `ProvenanceBadge`: the four-state badge contract, the BuildTarget field table, the two challenged conflicts, four open questions). Both originals deleted from `.scratch-uma/` after verification.                                                                                                                                                                                                                                                                                                                                                                                                                                                                   | A scan for unconsolidated `.scratch-uma` markdown on 2026-10-06 found exactly these two; the owner ruled them folded here, the same treatment as §8.1 and §8.2. Both describe slices that landed, so they are historical records of what shipped, embedded verbatim with headings demoted one level and their title lines re-titled into the §8.4 / §8.5 headings. Numbered 8.4 and 8.5 rather than 8.3 and 8.4 because a concurrent pass had already claimed §8.3 (the run-informed acceptance section above them) in this same working tree.                                                                                                              |
| 2026-10-06 | §4's status block renamed from Phase A–C to Phase A–D and its table extended with D1, D2, D3, D4, D5, D6, the D5/D6 wizard halves and a Not begun row for D8–D18 and E1–E6, each with its commit and its open defect. New §4.1 carries the open work as an ordered queue: five defects, three owner decisions, the environment gate, and the toolchain and focus rules every later slice inherits. §8's D5 and D6 rows describe run-scoped screens that landed in `05e9584` ahead of their slice numbers, so the table records the fact rather than pretending the order held.                                                                                                                                                                                                                                                                                                                                                   | The plan still read "No Phase D or E slice has begun" while four Phase D slices sat in `master`. §4 is where the next slice decides what to start, so a stale block silently sends someone to rebuild landed work; that has already happened three times this pass, with D5 and D6 dispatched as if unbuilt. The red state of the D5/D6 wizard halves is written down instead of smoothed over, because the tree at `0006b17` has three failing tests and three PHPStan errors and no `KNOWN-ISSUES.md` entry yet. §1's file counts were deliberately not re-measured in this pass and are stale; `AGENTS.md` §6 carries the current read as of 2026-10-06. |
| 2026-10-06 | §3's per-training-yield row updated to record-as-observed (the client prints the preview and failure % on the tile) and five rows added from the recorded Rice Shower Unity Cup run (`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md`): Energy as band-only, the turn-counter discrepancy, the burst reward tiers with the live Extreme-counting disagreement, the +30 Team Rank bonus as Estimated, and the client-read hint discount ladder. New §8.3 makes that run the canonical acceptance seed for D7–D16 and E3, with per-slice deltas (D6 catalog-max vs client-current and the padlock rule, D9 record-as-observed, D10 condition-skill applicability, D13 the sourced ladder and the prerequisite, D14 the client counter wins, E3 the Team Info spine and the five-category finals). §11 gains the Energy-band contract question and a parked list of write-up-sourced ideas.                                             | The owner asked for plan updates grounded in the run report; it closes rows the plan previously marked silent, and its live disagreements (burst counting, turn counter) must render as uncertainty rather than resolve silently.                                                                                                                                                                                                                                                                                                                                                                                                                           |
| 2026-10-08 | Documentation-sync pass: the plan's own status reads are corrected in place with dated close-outs where the tree moved past them. §1's inventory gained a close-out (54 pages / 58 components / 44 browser specs / `resources/views/components/` gone). §4's dated-status paragraph, the "Phase A–D status" paragraph, the D7 and D11 table rows and the E4–E6 row now carry close-outs recording D16, D17, D18, E1, E2, E3, E4, E5 and E6 as landed, each naming the route or registration in the tree. §4.1 items 6, 10 and 11 gained dated corrections (the badge key ruling, the `RecordVeteran` caller, the URA `Recorded` browser case still open). §8.4's and §8.5's pre-landing premises and §9's "work to be done" reading gained close-outs; §9.1's and §9.5's slice plans gained landing notes; §11 risk 5 is marked closed by the full `SCR-CAR-*` matrix. The embedded D7 slice spec gained a close-out at its end. | Owner asked for the documentation set to be brought into agreement with the completion of Phases A–E. Every close-out preserves the original sentence and appends a dated correction beside it, per the pattern this file's §4.1 and §8.1–§8.6 already use; no slice body was rewritten as if the work were still in flight. This pass did not run `npm run test:browser`; the browser-gate claims quoted in the §4 table are the slices' own, taken on their own trees and recorded there.                                                                                                                                                                 |

# D7 / SCREEN-008 — Run Preflight ("Career Contract") — slice spec and task list

Slice row: `docs/proposals/frontend-development-plan.md` line 180 (D7, SCREEN-008, deps D4/D5/D6).
Plan phase per `spec-driven-development`; task list per `planning-and-task-breakdown`.

## 1. What this screen is

Step 6 of the setup wizard. It composes the five entered steps into one read-only contract, states the
warnings that are derivable from that entered data, and creates the run exactly once, on "Start Career".
Nothing is re-asked (WCAG 3.3.7). Every figure is read from the session draft (`App\Services\Career\SetupDraft`),
which already carries all five keys.

Blocker check, done before writing this plan: `SetupDraft::legacyParents()` and
`StoreDraftLegacyRequest::parentIds()` exist and `LegacySelectController::store()` writes both
`legacy_selection` and `legacy_parents` (`app/Http/Controllers/Career/LegacySelectController.php:115-125`),
so the defect the plan flagged at line 227 is already closed in the tree. D7 is unblocked.

## 2. Files

| Action | Path |
| --- | --- |
| Create | `app/Http/Controllers/Career/PreflightController.php` |
| Create | `app/Http/Requests/Career/StartCareerRequest.php` |
| Create | `resources/js/pages/Career/Preflight.vue` |
| Modify | `routes/web.php` (two routes, beside the other five wizard pairs) |
| Modify | `resources/js/layouts/SetupLayout.vue` (step 6 `to: '/career/setup/preflight'`) |
| Create | `tests/Feature/CareerPreflightTest.php` |
| Create | `tests/browser/career-preflight.spec.ts` |
| Modify | `SCREEN_SPEC.md` (new `SCR-CAR-010` row + §4 state table) |
| Modify | `docs/proposals/frontend-development-plan.md` (D7 status row; §4.1 items 1, 3, 4) |

Reuse, do not re-implement: `SetupDraft` (all five keys), `ScenarioCaps::forRun(SetupDraft::planningRun())`,
`AncestryGraph::build()` via a `LegacySelectionPayload::fromArray()`, `DeckAnalysis::build()` with the same
input builder `DeckSelectController::analysisInput()` uses, `SupportCardEffects::dictionary()/atCap()`,
`StoreTrainingRunRequest::scenarios()`, `DeckSlot::POSITIONS|OWNERSHIP|MAX_POSITION`,
`AptitudeBadge.vue`, `ProvenanceBadge.vue`.

## 3. Props contract (one typed object, lossless round trip)

```ts
interface PreflightProps {
    step: number                                  // 6
    contract: {
        complete: boolean                         // every section present
        build: {
            trainee: { id: number; name: string; name_ja: string | null } | null
            scenario: { key: string; label: string; focus: string | null } | null
            target: { purpose: string | null; purpose_label: string | null
                      distance: string | null; surface: string | null; style: string | null
                      aptitude_floor: string | null; stat_priority: number[]
                      notes: string | null } | null
            legacy: {
                affinity: string | null
                members: Array<{ slot: string; label: string; name: string | null
                                 rank: number | null; is_guest: boolean
                                 ancestors: Array<{ slot: string; name: string | null }>
                                 sparks: Array<{ kind: string; kind_label: string|null; target: string|null; stars: number|null }>
                                 probability: { value: null; title: string } }>
            } | null
        }
        deck: {
            slots: Array<{ position: number; label: string; is_friend: boolean
                           ownership: string | null
                           card: { id: number; name: string; type_label: string
                                   rarity_word: string; artworkURL: string|null
                                   effects: Array<{ effect_id: number; name: string|null; display: string }> } | null }>
            analysis: ReturnType<DeckAnalysis::build>   // covered / categories / uncategorised / strengths / weaknesses
        } | null
        target: {
            stats: Array<{ key: string; label: string; rank: number|null }>   // stat_priority, in order
            skills: { value: null; title: string }                            // named absence, see §5
            races: { distance: string|null; surface: string|null; style: string|null }
        }
        warnings: Array<{ key: string; title: string; detail: string; href: string }>
        ruleset: { label: string; title: string }    // "N/A" + reason (app.ruleset is null, §48)
    }
    edit: { scenario: string; trainee: string; target: string; legacy: string; deck: string }
    startAction: string                              // route('career.preflight.store')
    notice: string                                   // LegacyController::RECORD_ONLY_NOTICE for §4
}
```

All URLs resolved server-side with `route()` (no Ziggy in this repo). "N/A" is rendered as text with a
`title` and never as a dash (AGENTS.md §5).

## 4. Warning table (each row is one test)

Only warnings whose inputs are entered facts or stated anchors. No scoring, no held computation.

| Key | Trigger (data read) | Reason line | Source |
| --- | --- | --- | --- |
| `missing_section` (one per absent key) | draft `scenario`, `umamusume_id`, `build_target`, `legacy_selection`, `deck` is null | "No scenario chosen yet. Step 1 holds this." + href | draft |
| `deck_incomplete` | fewer than six slots carry a card | "2 of six positions carry a card." | draft `deck` |
| `legacy_slot_empty` | a legacy member has no name | "Parent B has no Legacy chosen." | draft `legacy_parents` |
| `aptitude_below_target` | target `distance`/`surface`/`style` set and the trainee's `aptitude_{x}` letter is D/E/F/G | "Your target is Long; this trainee's Long aptitude is D (Weak)." | `umamusume.aptitude_*`, band words are design-2.0 §17 |
| `deck_category_blank` | `DeckAnalysis::build()['categories'][$k]['blank']` is true for a category | "Training power: no card in the deck carries an effect here." | DeckAnalysis (restatement of a count) |
| `stat_priority_unsourced` | a `stat_priority` stat that no card in the deck contributes a training effect for | "Speed is your first priority; no card in the deck carries a Speed training effect." | SupportCardEffects anchors via DeckAnalysis lines |
| `ruleset_absent` | always, while `app.ruleset` is null | "No source defines a Global ruleset version." | `HandleInertiaRequests` |

The aptitude cut-off is the brief's D-or-lower, which is design-2.0 §17's Weak and Very weak bands
(`S|A Strong`, `B|C Neutral`, `D|E Weak`, `F|G Very weak`). It is not a number this slice invented.
`stat_priority_unsourced` reads stat→effect through the effect names the dictionary states; if the
mapping is not exact for a stat, that stat is skipped rather than guessed (see §5).

**Skipped, with the reason recorded in the page copy and the report:** "weak stamina plan" and "poor
support synergy" (per-training yields and synergy weights are unsourced/held, PRD §6.4, C2),
"missing scenario requirement" (no per-scenario requirement data exists in `config/scenarios.php`),
"low factor probability" (star-roll odds are held, REFERENCE §1.5.3), per-skill priorities (no key
exists in `BuildTargetPayload`; D4's owed owner decision).

Warnings never block. Only a refused `StartCareerRequest` blocks.

## 5. Open questions for the owner (carried, not blocking)

1. `BuildPurpose` has four cases; the brief names five ("Competitive Build"). Same as D4's open item.
2. Per-skill priorities and risk tolerance need keys in `BuildTargetPayload::KEYS` (stored-shape change).
3. "Skill priorities" on the Target section: rendered as a named absence with the D4 reason until (2) lands.
4. `config/scenarios.php` `our_grand_concert.documented` reads `true` after `ab53861`, so the
   PARTIALLY DOCUMENTED badge is unreachable; reported, not changed.

## 6. Tasks (TDD, each ends green)

1. **RED:** `tests/Feature/CareerPreflightTest.php` — props test: `GET /career/setup/preflight` with a full
   draft asserts the whole contract shape (trainee, scenario, target, six legacy members, six deck slots,
   analysis keys, warnings list, ruleset). Run it: fails (route missing).
2. **GREEN:** `routes/web.php` (`career.preflight`, `career.preflight.store`) + `PreflightController::show()`
   composing the contract from the draft. Re-run: the props test passes.
3. **RED:** warning tests — one per table row, each a draft state; assert the key is present (and absent
   when the trigger is not met). Implement the warning builder in the controller (private method,
   table-driven). Re-run.
4. **RED:** `Start Career` tests — (a) a full draft creates exactly one run with the draft's `umamusume_id`,
   `scenario`, `status = Active`, the two `inheritance_parent_*` ids resolved from the Veteran rows, the
   `legacy_selection` json, the `build_target` json, six `deck_slots` rows and seeded start skills;
   (b) a tampered draft (support card id that no longer exists / scenario removed from config / unknown
   `build_target` key) is refused with the run count unchanged and an error naming the section;
   (c) `SetupDraft::reset()` on success, so a second Start Career cannot double-create.
5. **GREEN:** `StartCareerRequest` (composed rules; re-runs the three step requests by hydrating them with
   the draft slice and calling `validateResolved()`; see §7) + `PreflightController::store()` in one
   transaction.
6. **Vue:** `Career/Preflight.vue` following `pages/Catalog/Index.vue`'s pattern, `SetupLayout step=6`,
   three sections, warning list with glyph + text, Edit hrefs, `router.visit` for Back, `useForm` posting
   `startAction`; focus to the first error (`role="alert"` summary is `tabindex="-1"`, focused when
   `form.hasErrors`).
7. **Gates:** typecheck, build, targeted tests, full suite, Pint, PHPStan, lore, `migrate:status`.
8. **Browser:** `tests/browser/career-preflight.spec.ts` — values carried in from all five steps, the
   warning text, Edit → return keeps values, Start Career lands on the run, and the error-focus path.

## 7. Start Career refusal mechanics (the trust boundary)

The preview is not the boundary. `store()`:

```php
$draft = [ 'umamusume_id' => ..., 'scenario' => ..., 'status' => RunStatus::Active->value,
           'build_target' => SetupDraft::buildTarget(), 'legacy_selection' => SetupDraft::legacySelection(),
           'legacy_parents' => SetupDraft::legacyParents(), 'deck' => SetupDraft::deck() ];
```

`StartCareerRequest` validates the composed array and, in `withValidator()`, re-runs each step's own
Form Request against its slice of the draft by hydrating a child instance
(`StoreDraftDeckRequest::createFrom($this->duplicate($slice))`, `->setContainer(app())`,
`->validateResolved()`, wrapping `ValidationException` into the same error bag with the section named).
That keeps one rule set per step (no duplication) and refuses a draft that a config change or a deleted
row has made stale. The payload readers (`LegacySelectionPayload::fromArray()`,
`BuildTargetPayload::fromArray()`) stay the one owner of the accepted vocabularies and are the second
gate. Run creation, deck rows, legacy json + the two foreign keys and the build target are one
transaction; nothing is written on refusal, and the draft is reset only after the commit.

_Dated close-out 2026-10-08 (documentation-sync pass; the slice spec above is preserved as written):
this spec is the plan D7 executed, and D7 landed 2026-10-06 — the §4 table row records the landing,
`career.preflight` / `career.preflight.store` are wired at `routes/web.php:91-92`, and the route reads
the same `StartCareerRequest` trust boundary this spec's §7 describes. Its §5 open question 4 — the
`our_grand_concert.documented => true` reading that made the PARTIALLY DOCUMENTED badge unreachable —
is the question the owner's E6 ruling answered: the badge now reads `partially_documented`
(`config/scenarios.php`), recorded in the §4.1 item 6 correction above. The defect this spec's blocker
check says is already closed — the step-4 draft write never persisting the parent ids — was the subject
of plan §4.1 item 1 and was closed by the `b494d6f` reuse of `AncestryGraph::parentNames()`, filed as
`KNOWN-ISSUES.md` KI-65._
