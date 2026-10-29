# Frontend Development Update Plan — HFW Parity Rewrite

**Status:** Proposed — Slices 1–2 in execution (2026-10-25)
**Target release:** 3.0.0 (breaking visual-structure change; see §10 Versioning)
**Author:** Architect session, 2026-10-25
**Supersedes the page-structure ambition of:** `docs/proposals/frontend-architecture-plan.md` (that plan shipped v2.1.0: tokens + primitives only, no page-structure rewrite)
**Amended 2026-10-25:** added §13 Domain Mechanics Verification. The HFW is a frontend-only
artifact; where its visuals contradict a verified game mechanic, **the mechanic wins** and the
deviation is marked in the Vue template with `<!-- DOMAIN OVERRIDE: [reason] -->`.

> **Amended 2026-10-27 (v3.0.0 close-out session, `hf-gates-and-slices-2026-10-27`).** Four
> corrections, each with the evidence that produced it:
>
> 1. **DM-3 reads "four family roles across five colour keys"** (amended 2026-10-27, commit
>    `70631a60`): `LegacySelectionPayload::SPARK_KINDS` is `blue, pink, green, white, scenario`, and
>    `white` is one label over two dataset categories. §13 DM-3's acceptance therefore means all five
>    keys render, not four.
> 2. **S7.2's "one spark per parent by radio" is not a UI slice.** `training_runs.legacy_selection` is
>    one column holding one selection (`ADR-0010`), read through
>    `TrainingRun::legacySelection()`; there is no per-run candidate-spark catalogue and no pair table,
>    so radios choosing *one stored spark* would change what the column stores and what
>    `StoreLegacySelectionRequest` accepts. Filed, not built.
> 3. **"Skip this moment" and "slot open" belong to `inheritance.html`, not to any Legacy page.** The
>    phrases exist (7 hits, all in `inheritance.html`), so round 3's "no such phrase" note was scoped
>    too narrowly; the slice landed on `Career/InheritanceEvent.vue` instead of `Legacy/Builder.vue`.
> 4. **The framework audit's exit code is not in this plan**, so no erratum is owed: the ported
>    `tools/ui-framework-audit.mjs` already exits **2** with `UNVERIFIED` when Playwright or Chromium is
>    absent, and `tests/Feature/FrameworkAuditExitTest.php` pins that. The earlier instruction's
>    "exits 0" reading was of the upstream HFW copy under `docs/design-research/`, not of the tool.
>
> Deviations recorded rather than built: the "Effects documented?" column on Races/Events/ShopItems
> (no per-row flag in those payloads; each row's own `N/A` plus reason is the same honesty at the grain
> the data has), and DM-6's four running strategies (they live in the wizard's build-target selects,
> which were peer-dirty this session).


---

## 0. Purpose and contract

Version 2.1.0 ported the design tokens and primitive CSS classes from
`docs/design-research/frontend-v2-wireframes/assets/shell.css` into `resources/css/app.css`
(`@theme static` + `@layer components` primitives). It deliberately did **not** rewrite any
Vue page structure. The result: every screen renders the correct palette on the wrong DOM.

This plan is the second half: rewrite every page in `resources/js/pages/` and the three
layouts in `resources/js/layouts/` so the rendered DOM matches the High-Fidelity Wireframes
(HFW) in `docs/design-research/frontend-v2-wireframes/` — structure, region order, copy
classes, and annotation rulings — while keeping every controller, route, and Form Request
contract intact except where §7 (Backend Integration) names a prop delta.

**Hard rules for every slice:**

1. This is **not** a wireframe implementation. Do not copy HTML bodies verbatim. Port the
   *structure and region order* into Vue components fed by Inertia props. Static figures in
   the HFW (71 trainees, 1,910 skills, Rice Shower turn 67) are live-payload captures from
   2026-10-21/23, not literals to type into templates.
2. Tokens already exist. Use Tailwind utilities mapped from `@theme static` tokens
   (`bg-page`, `text-ink-strong`, `border-rule`, …) and the shipped primitives (`.card`,
   `.tile`, `.pill-*`, `.note`, `.trust-*`, `.srow`, `.slot-*`). Do not re-introduce
   `var(--…)` inline styles except where the HFW's inline style encodes a one-off layout
   measurement with no token (state the exception inline).
3. No `dark:` utilities (theme flips via `html[data-theme='dark']` variable swap only).
   No skeleton loaders. No placeholder grey boxes (ArtworkSlot rule, §8.4).
4. Lore gate: displayed vocabulary comes from `lang/en/uma.php` / enum labels. Run
   `php tools/lore.php` after any copy-carrying slice and record hit count + delta.
5. AGENTS.md §9 validation matrix applies per slice. Behavior changes ship with a test;
   layout-only changes run the browser gates listed in §9 of each slice below.
6. Do not touch `assets/shell.css`, `assets/shell.js`, or `assets/data/*` — they are
   reference artifacts. Do not hand-edit the four `assets/data/*.js` files; regenerate via
   `php build-data.php` only if a page needs a figure the dataset lacks (see
   `assets/data/README.md` for the regeneration contract). The collection root
   `README.md`, `build-data.php`, `framework-check.mjs`, and `render-check.mjs` are
   reference tooling — read them, never run them from slices.
7. One `h1` per document, in the shell's `role="banner"` region (DESIGN.md §2.2 amendment
   2026-10-17). The `.phead` block replaces the current `#head`/`#title` slot band.
8. The `@container` + `data-layout-container` contract on `<main>` is load-bearing
   (`@2xl:`–`@5xl:` variants in 21 files; sticky descendants are deliberately dead, pinned
   by `tests/browser/containment.spec.ts`). HFW layouts resolve against the same container
   breakpoints — verify against the measured content box (1088px @1440 viewport,
   960px @1280, 638px @958), not the viewport.
9. Wireframe-only artifacts are excluded from porting: the `.proto` "Static wireframe"
   chip in the HFW topbar, the `.keybox` annotation blocks, and the dashboard ToC card.
10. `.trust-*` is vocabulary-only — the HFW pages use zero trust classes. Do not render
    trust chrome. `.tag.auto` (light blue) / `.tag.you` (light orange) are the
    auto-vs-trainer provenance pair; use them where a value's origin matters (cockpit
    metrics).

---

## 1. Source-of-truth map

Every HFW page, its target Vue file, its controller, and the slice that owns it.
HFW pages are at `docs/design-research/frontend-v2-wireframes/`; paths below are repo-relative.

| HFW page | Vue target | Controller@method | Slice |
|---|---|---|---|
| index.html (landing) | `resources/js/pages/Dashboard.vue` | `DashboardController@index` (`home` `/`) | S2 |
| dashboard.html (SCR-CAR-001) | same — merge as alt region order ruling, see §3.3 | same | S2 |
| runs-index.html | `resources/js/pages/Runs/Index.vue` | `TrainingRunController@index` (`runs.index`) | S3 |
| runs-create.html | `resources/js/pages/Runs/Create.vue` | `TrainingRunController@create` | S3 |
| import.html | `resources/js/pages/Runs/Import.vue` | `TrainingRunController@importForm` | S3 |
| import-preview.html | same route, `preview` prop branch | `TrainingRunController@preview` | S3 |
| career-scenario.html | `resources/js/pages/Career/ScenarioSelect.vue` | `Career\ScenarioSelectController@show` | S4 |
| career-trainee.html | `resources/js/pages/Career/TraineeSelect.vue` | `Career\TraineeSelectController@show` | S4 |
| career-profile.html | `resources/js/pages/Career/TraineeProfile.vue` | `Career\TraineeSelectController@profile` | S4 |
| career-target.html | `resources/js/pages/Career/BuildTarget.vue` | `Career\BuildTargetController@show` | S4 |
| career-legacy.html | `resources/js/pages/Career/LegacySelect.vue` | `Career\LegacySelectController@show` | S4 |
| career-deck.html | `resources/js/pages/Career/DeckSelect.vue` | `Career\DeckSelectController@show` | S4 |
| career-preflight.html | `resources/js/pages/Career/Preflight.vue` | `Career\PreflightController@show` | S4 |
| cockpit-ura-finale.html | `resources/js/pages/Career/Cockpit.vue` (URA branch) | `Career\CockpitController@show` | S5 |
| cockpit-unity-cup.html | same, Unity Cup branch | same | S5 |
| cockpit-trackblazer.html | same, Trackblazer branch | same | S5 |
| cockpit-grand-concert.html | same, Grand Concert branch | same | S5 |
| training-decision.html | `resources/js/pages/Career/TrainingDetail.vue` | `Career\TrainingDecisionController@show` | S6 |
| race-decision.html | `resources/js/pages/Career/RaceDecision.vue` | `Career\RaceDecisionController@show` | S6 |
| career-race-planner.html | `resources/js/pages/Career/RacePlanner.vue` | `Career\RacePlannerController@show` | S6 |
| event-decision.html | `resources/js/pages/Career/EventDecision.vue` | `Career\EventDecisionController@show` | S6 |
| inheritance.html | `resources/js/pages/Career/InheritanceEvent.vue` | `Career\InheritanceEventController@show` | S6 |
| skills-planner.html | `resources/js/pages/Career/SkillsPlanner.vue` | `Career\SkillsPlannerController@show` | S6 |
| run-deck.html | `resources/js/pages/Career/…` deck sync screen | `TrainingRunController@deckBuilder` | S6 |
| timeline.html | `resources/js/pages/Career/Timeline.vue` | `Career\TimelineController@show` | S6 |
| result.html | `resources/js/pages/Career/Result.vue` | `Career\ResultController@show` | S6 |
| career-save-veteran.html | `resources/js/pages/Career/SaveVeteran.vue` | `Career\SaveVeteranController@show` | S6 |
| career-snapshot-entry.html | `resources/js/pages/Career/Snapshot/Entry.vue` | `Career\SnapshotController@entry` | S4 |
| career-snapshot-setup.html | `resources/js/pages/Career/Snapshot/Setup.vue` | `Career\SnapshotController@setup` | S4 |
| career-snapshot-review.html | `resources/js/pages/Career/Snapshot/Review.vue` | `Career\SnapshotController@review` | S4 |
| legacy.html | `resources/js/pages/Legacy/Index.vue` | `LegacyController@index` | S7 |
| legacy-builder.html | `resources/js/pages/Legacy/Builder.vue` | `LegacyController@builder` | S7 |
| legacy-compare.html | `resources/js/pages/Legacy/Compare.vue` | `LegacyController@compare` | S7 |
| veterans.html | `resources/js/pages/Veterans/Index.vue` | `VeteranController@index` | S7 |
| veteran-detail.html | `resources/js/pages/Veterans/Show.vue` | `VeteranController@show` | S7 |
| veterans-compare.html | `resources/js/pages/Veterans/Compare.vue` | `VeteranController@compare` | S7 |
| catalog.html | `resources/js/pages/Catalog/Index.vue` | `CatalogController@index` | S8 |
| catalog-detail.html | `resources/js/pages/Catalog/Show.vue` | `CatalogController@show` | S8 |
| support-cards.html | `resources/js/pages/SupportCards/Index.vue` | `SupportCardController@index` | S8 |
| support-card-detail.html | `resources/js/pages/SupportCards/Show.vue` | `SupportCardController@show` | S8 |
| skills.html | `resources/js/pages/Skills/Index.vue` | `SkillController@index` | S8 |
| skill-detail.html | `resources/js/pages/Skills/Show.vue` | `SkillController@show` | S8 |
| skills-planner.html | (also Career slice S6 — planner, not library) | — | S6/S8 |
| database.html | `resources/js/pages/Database/Index.vue` | `DatabaseController@index` | S9 |
| database-sources.html | `resources/js/pages/Database/Sources.vue` | `DatabaseController@sources` | S9 |
| database-scenarios.html | `resources/js/pages/Database/Scenarios.vue` | `DatabaseController@scenarios` | S9 |
| database-races.html | `resources/js/pages/Database/Races.vue` | `DatabaseController@races` | S9 |
| database-events.html | `resources/js/pages/Database/Events.vue` | `DatabaseController@events` | S9 |
| database-shop-items.html | `resources/js/pages/Database/ShopItems.vue` | `DatabaseController@shopItems` | S9 |
| database-sparks.html | `resources/js/pages/Database/Sparks.vue` | `DatabaseController@sparks` | S9 |
| database trainees/supports/skills subpages | `Database/Trainees.vue`, `Database/Supports.vue`, `Database/Skills.vue` | `*Controller@databaseIndex` | S9 |
| review.html | `resources/js/pages/Review/Index.vue` | `ReviewController@index` | S10 |
| review-detail.html | `resources/js/pages/Review/Detail.vue` | `ReviewController@show` | S10 |
| preferences.html | `resources/js/pages/Preferences/Edit.vue` | `PreferenceController@edit` | S11 |
| help.html | **new** `resources/js/pages/Help.vue` | **new** `HelpController@show` | S11 |
| error-404.html | `resources/js/pages/Errors/NotFound.vue` (or existing error pages) | Inertia error handling | S11 |
| error-419.html | `resources/js/pages/Errors/SessionExpired.vue` | same | S11 |
| error-500.html | `resources/js/pages/Errors/ServerError.vue` | same | S11 |

`index.html` vs `dashboard.html`: both are dashboards. `index.html` is the landing variant
("Pick up where you left off" + "Needs you" + 4 KPI cards + ToC block, 11 annotations);
`dashboard.html` is the SCR-CAR-001 variant ("Active Career" + "Quick Actions" + 3 KPI
tiles + "Roster"/"Data Status" side grid + "Recent Veterans", 5 annotations). §3.3 rules on
the merge.

---

## 2. Phase 0 — Recon gates (no file changes)

Run before Slice 1. Produces facts the slices cite; nothing ships.

1. `git status` — record in-flight files owned by other sessions; scope all later `pint` /
   `phpstan` runs to own paths only (KI-75: never `--dirty` on the whole tree).
2. Read `docs/proposals/frontend-architecture-plan.md` phase map — confirm which v2.1.0
   primitives are final (`.card`, `.tile`, `.pill-*`, `.slot-*`, `.srow`, `.trust-*`,
   `.note`, `.subnav`, `.savebar`, `.stepper`, `.state`) so slices reuse rather than
   rebuild them.
3. Read `SCREEN_SPEC.md` §4 and §8 for every screen in the slice you are about to start.
   Where HFW and SCREEN_SPEC disagree, the conflict is recorded in SCREEN_SPEC §7 and the
   slice stops for an owner ruling — do not pick one silently.
4. Read `DESIGN.md` §1–§2 (principles, chrome, spacing) and §4.7 (ArtworkSlot) once.
5. Inventory current props per page against the HFW's displayed fields. Produce the prop
   delta table consumed by §7. Template: for each page — `prop | HFW field | source
   (controller query) | action (keep / add / reshape / drop)`.
6. Record the lore-gate baseline: `php tools/lore.php` hit count, committed in the slice
   PR description as "baseline: N".

**Gate:** prop delta table complete for Slices 2–11; SCREEN_SPEC conflicts listed (even if
empty).

---

## 3. Phase 1 — Global shell (Slice 1)

HFW reference: every page's shared chrome. Canonical source DOM: `dashboard.html:12-211`
and `assets/shell.js` `wfPage()` + `NAV` constant. Target files:
`resources/js/layouts/AppLayout.vue` (rewrite), `resources/js/components/NavGlyph.vue`
(extend if the icon set lacks a mark), `resources/js/composables/useLayout.ts` (unchanged),
new partial components listed below.

Current divergence (measured against `AppLayout.vue` @ v2.1.0):

| HFW shell element | Current AppLayout | Action |
|---|---|---|
| `.rail` aside with brand block (`.rail-brand` TD mark + "TRAINER DESK" wordmark) at rail top | Brand wordmark sits in the topbar (`:323`); collapsed rail shows bare `TD` (`:257`) | Move brand into rail head |
| Rail toggle button at **rail head** (`.rail-toggle`, chevron SVG, `data-rail-toggle`) | Toggle sits in the topbar right side (`:332-342`) | Move toggle into rail head beside brand |
| Grouped nav: `.rail-group` labels **Play / My data / Library / System** | Flat `<ul>` of 10 items (`:261-299`) | Add group structure (see §3.1) |
| `.rail-foot` "Data sources" link at rail bottom | Present (`:301-307`) but hides to `sr-only` when collapsed | Keep; match HFW (label collapses, link stays reachable — see §3.4) |
| Topbar: `.crumb` breadcrumb left, `.proto`/status pill right, `.toputils` Help icon-button | Topbar carries brand + toggle only | Rebuild topbar: breadcrumb, status pill, Help |
| `.phead` block: `.eyebrow`, `.hrow` (`h1` + `.lede` + `.hrow-actions`), `.hr` rule | `#head`/`#title` slot band under banner | Standardise via extended `PageHeader` |
| `.canvas` content wrapper | `@container` main with px/py utilities | Keep container contract; add `.canvas` semantics |
| `.mbar` mobile bar: Home / Career / Legacy / Deck / More as 5 equal slots, `aria-label="Primary mobile"` | 4 bar slots + `<details>` More disclosure | Reshape per §3.5 |
| Skip link `.skip` → `#main` | Present (`:242-247`) | Keep as-is |

### 3.1 Grouped navigation

HFW `NAV` (shell.js:7-26), exact groups and order:

- **Play:** Dashboard (`/`), New Career (`/career/setup/scenario`)
- **My data:** Careers (`/training-runs`), Legacy Lab (`/legacy`), Veterans (`/veterans`), Review (`/review`)
- **Library:** Support Cards (`/support-cards`), Skills (`/skills`)
- **System:** Database (`/database`), Settings (`/preferences`)

Edit `AppLayout.vue`:

1. Replace the flat `items: NavItem[]` literal (`:129-145`) with a grouped structure:
   ```ts
   interface NavGroup { label: string; items: NavItem[] }
   const groups: NavGroup[] = [ /* Play, My data, Library, System, order above */ ];
   ```
   Keep every existing `NavItem` field (`mobileLabel`, `spa`, `inBar`, `prefetches`,
   `icon`) and every per-item value — routes, prefetch exceptions (New Career stays
   `prefetches: false`), and icons are unchanged.
2. Template: replace the single `<ul>` (`:261-299`) with
   `<div v-for="group in groups" :key="group.label">` rendering a `.rail-group` heading
   (new primitive class or `text-[11px] font-bold uppercase tracking-wider text-ink-muted
   px-3 pt-4 pb-1`) followed by that group's `<ul>`. Collapsed rail: group labels hide
   (display, not `sr-only` — a label with no visible items is noise), item rendering
   unchanged (marks + `sr-only` words + `title`).
3. This resolves the "ten flat items breach the ≤8 target" note (`AppLayout.vue:140-142`
   comment) the way the HFW rules it: grouping, not deletion (index.html annotation 4).

**Component:** extract the nav item renderer into `resources/js/components/NavItem.vue`
only if the loop body survives grouping unchanged; otherwise keep it inline. Lazy rule:
one consumer = no extraction.

### 3.2 Rail head: brand + toggle

1. Move the brand block into the rail: `.rail-top` wrapper containing `.rail-brand`
   (`TD` mark span + `TRAINER DESK` text span) and the toggle button.
2. Move the toggle button out of the topbar (`:332-342`) into `.rail-top`, per index.html
   annotation 10: the control belongs to the pane it resizes (DESIGN.md §2.2). Restore the
   chevron SVG the HFW ships (`dashboard.html:22`) beside the accessible word; the word
   stays the accessible name (WCAG 2.5.3 — visible text is a substring of the name). Keep
   `aria-expanded`, `aria-controls="primary-nav-rail"`, and the
   `data-rail-verb` sr-only span pattern.
3. Rename the nav landmark `id="primary-nav"` → `id="primary-nav-rail"` and
   `aria-label="Global navigation"` on the `<aside>` to match HFW.
4. Keep `localStorage` key `trainer-desk.nav-rail` and values `'compact'|'wide'` — the
   wireframes read the same key (shell.js comment: shared shape across app and set). Keep
   the width mechanism as-is (`w-14`/`w-64` classes); HFW's `--rail-w` variable note is
   about the static artifact's grid, not a required change here. Verify no visual
   regression: content must not retain a 236px gap when collapsed (the defect the HFW
   comment names).
5. Expanded rail shows brand text + toggle; collapsed rail shows `TD` mark + toggle icon
   only (toggle keeps sr-only verb + `title`).

### 3.3 Topbar rebuild

Replace the current topbar contents (`:322-343`) with the HFW `.topbar > .topbar-in`
structure:

1. `.crumb` — breadcrumb slot. Source: a new optional `breadcrumb` prop pages pass through
   the layout (see §3.6), defaulting to the page's own title (`<b>Dashboard</b>` pattern
   from `dashboard.html:47`). Database-area pages render `Database / <Area>` as a linked
   crumb (the "way back is a strip, not a word in a corner" annotation on database.html,
   skills.html, support-cards.html, catalog-detail.html, support-card-detail.html).
2. `.spacer`.
3. `.who` — status pill `<span class="pill pill-positive">N active career(s)</span>` fed
   from shared Inertia props. **Prop delta:** `HandleInertiaRequests` must share
   `activeCareerCount` globally (currently `DashboardController`-only). See §7.1. Render
   nothing when zero (absence is a normal state).
4. `.toputils` — Help icon-button linking to `/help` (new route, Slice 11). `?` glyph +
   visible `Help` label, `title="Help and documentation"`.
5. The topbar keeps `border-b-2 border-brand-chrome` (the brand-blue rule) and `h-12`.
   Product name leaves the topbar entirely (it now lives in the rail head).

### 3.4 Page head standardisation (`.phead`)

Every page renders: `.eyebrow` (small uppercase label), `.hrow` (`h1` + optional `.lede`
left, `.hrow-actions` right), `.hr` rule. Extend `resources/js/components/ui/PageHeader.vue`:

1. Add props: `lede?: string`, `actions` slot (already present as `#actions`).
2. Render the `.hr` rule after the row.
3. Migrate `AppLayout.vue`'s `#title`/`#eyebrow` fallback band (`:353-367`) to delegate to
   `PageHeader` so both paths produce identical DOM. One `h1` invariant is unchanged.
4. Delete the `#head`-vs-`#title` dual path in AppLayout once all pages pass `breadcrumb`
   and render `PageHeader` in `#head` (checked in each slice's gate).

### 3.5 Mobile bar

HFW `.mbar` (`dashboard.html:205-211`): five equal slots **Home / Career / Legacy / Deck /
More**, each a direct link with a `.dot` mark, `aria-label="Primary mobile"`. Current bar:
4 links + `<details>` More (AppLayout `:386-446`).

Ruling needed before editing (stop condition): the current `<details>` overflow is
test-covered accessibility behavior (`AGENTS.md`-referenced keyboard paths), and design-2.0
§41 says "Home, Career, Legacy, Deck, More". The HFW makes "More" a direct link to
`database.html`; the app has 6 overflow destinations (Veterans, Support Cards, Skills,
Review, Database, Settings) that cannot all be one link.

**Options to put to the owner:** (a) keep `<details>` More, matching the 4+1 shape —
recommended, preserves keyboard/Esc behavior; (b) HFW-literal: More → `/database`, losing
5 destinations from mobile. Record the ruling in the slice report. Default: (a).

If (a): restyle the bar to HFW visuals (`.dot` marks, equal `flex-1` slots — already
present) and change `aria-label` to "Primary mobile". No structural change.

### 3.6 Files touched (Slice 1)

- `resources/js/layouts/AppLayout.vue` — rewrite per §3.1–3.5
- `resources/js/components/ui/PageHeader.vue` — add `lede` prop, `.hr` rule
- `resources/js/components/NavItem.vue` — optional, only if extraction stays trivial
- `app/Http/Middleware/HandleInertiaRequests.php` — share `activeCareerCount` (§7.1)
- `resources/css/app.css` — add `.rail-group` primitive only if utilities prove awkward
  (one-line rule; prefer utilities)
- `tests/browser/` — update shell specs: nav group headings present, toggle in rail head,
  `aria-controls="primary-nav-rail"`, mobile bar label

**Gate (Slice 1):** `php artisan test --filter=AppLayout` (or closest feature test),
`npm run test:browser` shell/nav specs, `pint --test --format agent <own paths>`,
`phpstan analyse <own paths>`, lore gate count vs baseline. Browser check at 1280px and
390px: grouped rail renders, collapse round-trips, topbar shows crumb + pill + Help.

---

## 4. Phase 2 — Dashboard (Slice 2)

Targets: `resources/js/pages/Dashboard.vue`, `app/Http/Controllers/DashboardController.php`.
HFW: `index.html` (landing, canonical) and `dashboard.html` (SCR-CAR-001 variant).

### 4.1 Merge ruling (index.html vs dashboard.html)

`index.html` is the later capture (2026-10-21 payload, 11 annotations) and its region order
matches `Dashboard.vue`'s current composition (dashboard.html annotation 5 concedes this:
"Active Career, Quick Actions, Recent Veterans and Data Status are the four regions
Dashboard.vue composes"). **Rule: `index.html` structure wins**, with these
`dashboard.html` elements adopted:

- The 72-mark turn strip (`dashboard.html:101-114`) — discrete marks, not a bar
  (annotation 2). Current Dashboard has a turn rail already; restyle to one `<li>` mark
  per turn, `role="list"`, `aria-label="Career progress"`, filled marks `bg-action`.
- The `.note` under the strip explaining absence (annotation 3: Energy/Team Rank print
  N/A **with the reason**, never dropped).

### 4.2 Structure rewrite

Replace `Dashboard.vue`'s bento grid with `index.html`'s region order:

1. **`.phead`** (eyebrow "Overview", `h1` "Dashboard" — wait: `index.html` uses
   `h1` "Trainer Desk" with eyebrow "Dashboard". Follow index.html exactly: eyebrow
   `Dashboard`, `h1` `Trainer Desk`, lede from live payload pattern "N active career(s),
   N finished runs, and four permanent Global scenarios." — **prop delta**, see §7.2),
   actions: `New career` (`.btn`) + `All careers` (`.btn btn-quiet`).
2. **`.grid g-side` — "Pick up where you left off" card + "Needs you" card.**
   - Resume card: `ArtworkSlot` `card_portrait` 64px (`.slot slot-16`), trainee name
     (19px/800, `--ink-strong`) + scenario `.pill pill-accent`, Japanese name + year/turn
     position line (12.5px muted), **one** `.btn btn-primary` "Open cockpit" →
     `activeCareer.resume_url`. Three tiles: Turn (`N of 72`, mono), Energy, Team rank —
     absences print "not recorded" via `.v na`, **no `title`-only explanation** (§8.1).
   - Turn-count contradiction note (`index.html:102-107`): prints when
     `stripValues()`-style count disagrees with cockpit position. **Prop delta:**
     `activeCareer.recorded_turns` alongside existing `turn` (§7.2). The note names both
     numbers and links nothing; it is the labelled-defect pattern.
   - "Needs you" card: three tiles — Data status (risk left-border `border-l-[3px]
     border-risk` when stale), Veterans (`N banked`), Review queue (link "Check pending
     matches"). Each tile: `.k` label, value, muted subline, underlined 12px link.
3. **`.grid g-4` — four KPI cards** (Careers running, Trainees catalogued, Skills,
   Support cards): uppercase 10px `.k`, 26px/800 value, 12px muted link line. Values from
   `counts` prop; "Trainees catalogued" uses the **catalogue** count (67-class), not the
   app counter — see §7.2 ruling on `counts.trainees` source. Current `KpiCard` component
   renders this shape; extend with the link subline slot if missing, else compose inline.
4. **`.grid g-side` — "Recent veterans" card + "Quick actions" card.**
   - Veterans: `.t` table (Trainee | Scenario | Saved | actions), 3 rows, each with
     `ArtworkSlot` 40px (`.slot slot-10`) + name + Japanese name; footer "7 veterans ·
     3 shown" + "All veterans" `.btn btn-sm`.
   - Quick actions: four outline `.btn`s (Import a historical career, Review queue, Open
     settings, Database hub) + `.note` explaining the card's purpose (index.html:184).
5. **Table of contents card** (`index.html:188-200` + tail): three-column grouped link
   list (Landing / Setup wizard / Cockpit / Run lifecycle / Snapshot / Legacy and veterans
   / Library / Review / Database / System). **Do not ship this in the app.** It is a
   wireframe navigation aid, not product UI. State in the slice report: "ToC block
   excluded — wireframe artifact."

### 4.3 Prop deltas (feed §7.2)

`DashboardController@index` additions: `lede` parts (activeCareerCount exists; add
`finishedRunCount`), `activeCareer.recorded_turns`, `needsYou.reviewCount`,
`needsYou.veteranCount` (currently only `recentVeterans.total` — reuse if identical),
`counts.forms` (catalogue forms count, 106-class) if the Roster card is kept — **ruling:**
index.html drops the Roster card; drop it from the Vue page too, since the same figures
appear in the KPI row.

### 4.4 Files touched (Slice 2)

- `resources/js/pages/Dashboard.vue` — structural rewrite per §4.2
- `app/Http/Controllers/DashboardController.php` — prop deltas per §7.2
- `resources/js/components/KpiCard.vue` — link-subline slot if absent
- `tests/Feature/` dashboard test — assert new prop shape and region landmarks
- `tests/browser/` dashboard spec — region order, one `.btn-primary`, absence tiles render

**Gate (Slice 2):** feature test green; browser spec green; `browser-logs` clean; lore
gate; visual check 1280/390: region order matches §4.2, exactly one filled action visible.

---

## 5. Phase 3 — Page groups (Slices 3–11)

Each slice: same recipe. (1) Open the HFW page(s) listed in §1. (2) Open the target Vue
file and its controller. (3) Rewrite the template to the HFW's section order using shipped
primitives. (4) Apply the page's annotation key as a checklist (§8.2 names the recurring
ones; the page's own keybox is the per-page list — read it, every `<li>`). (5) Record prop
deltas into §7. (6) Gate per AGENTS.md §9.

### Slice 3 — Careers list & import (`runs-index`, `runs-create`, `import`, `import-preview`)

HFW rulings to implement:
- Filters as **one inline form row**, pagination under the table (runs-index annotation 1;
  DESIGN.md §2.3).
- Active run row: `tr.is-current` green tint + status pill; state = word + position, never
  colour alone.
- Turn column shows `N / 72` with the denominator (`runs.index` prop already carries
  `turn_index` + `turn_count` — render both).
- Retired rows print "Retired at turn N", not "Finished" (matches `Cockpit.vue:973`).
- **No destructive action in the row.** Delete stays on the cockpit behind disclosure.
  Audit `Runs/Index.vue` for any delete control; remove.
- Import: textarea is the primary control, set large; format support stated on the card;
  nothing writes until confirm (import annotations). Import preview: every field an edit
  box with found/missing tag; missing fields name the valid set ("Choose Active, Completed
  or Retired"), not bare "required".

Files: `resources/js/pages/Runs/Index.vue`, `Runs/Create.vue`, `Runs/Import.vue`;
`app/Http/Controllers/TrainingRunController.php` (`index`, `create`, `importForm`,
`preview`).

### Slice 4 — Setup wizard + snapshot (`career-scenario/trainee/profile/target/legacy/deck/preflight`, `career-snapshot-*`)

Layout: `resources/js/layouts/SetupLayout.vue` — keep the 6-step stepper; restyle stepper
to `.stepper`/`.step` primitives with `.l-empty/.l-partial/.l-done/.l-current` states.
HFW rulings:
- Scenario step: four scenario cards (Hick's Law — grid beats dropdown at four options);
  chosen card marked **three ways** (real radio + 2px `--pick-line` border + green tint;
  never colour alone, DESIGN.md D-12). Caps printed as the matrix states them ("1300 ×4 ·
  Wit 1800" — the outlier named, not collapsed).
- Reversibility stated before commit ("What changes after this step" block).
- Preflight: commit is the only filled action; one warning row with one link to the fix
  (no duplicate Edit links); product gaps described as behaviour ("Fans are not recorded
  on every turn, so a fan gate cannot be marked met until one is"), never as build status.
- Profile: read screen says so in the lede; growth row always five cells, zeros print
  `+0%`; absent base stats keep their rows with the 450-rule note (absence, not deletion).
- Legacy step: two slots, chosen parent = pick border + positive pill; "Leave empty" is an
  outline button with a note that a parentless build is legitimate; enhanced sparks
  described with **no invented rate** (source publishes none).
- Deck step: six slots always shown, empty slots dashed with add action; Pal cards print
  "No friendship bonus"; duplicate prevention stated; multiplier shown as multiplication
  (1.25 × 1.30 = 1.625, never "+55%").
- Snapshot trio: Known / Unknown / Not provided are three distinct printed states; confirm
  re-posts the read-back payload.

Files: `resources/js/pages/Career/{ScenarioSelect,TraineeSelect,TraineeProfile,BuildTarget,LegacySelect,DeckSelect,Preflight}.vue`, `Career/Snapshot/{Entry,Setup,Review}.vue`, `resources/js/layouts/SetupLayout.vue`; controllers `app/Http/Controllers/Career/{ScenarioSelect,TraineeSelect,BuildTarget,LegacySelect,DeckSelect,Preflight,Snapshot}Controller.php`.

### Slice 5 — Cockpit, four scenario variants (`cockpit-*`)

Target: `resources/js/pages/Career/Cockpit.vue` + `resources/js/pages/Career/Cockpit/Panels/*`
+ `resources/js/components/scenario/*`. Controller: `Career\CockpitController@show`.

This is the heaviest slice. Structure per `cockpit-unity-cup.html` (the shell-density
reference) with per-scenario panel branches that already exist (`UnityCupPanel`,
`TrackblazerPanel`, `UraPreFinalReview`, `HappyMeekPanel`, etc. — reuse, restyle).

Cross-variant rulings:
- **One decision, stated once.** Recommendation + reason + cost in one block; the
  page-head carries the same single primary action.
- Finale countdown lives in the scenario card, not only the advisor's why-list.
- Stat bars use the **entered** ceiling when the run carries one; never print the
  scenario's published cap as if it were the run's.
- Closed run (`cockpit-ura-finale.html`): `AdvisorRail` already `v-if="isActive"`; a
  Completed run prints the outcome first and the rail collapses to its record — no empty
  rail, no advisor.
- Trackblazer: runway shortfall ("88 short") is the lead figure; fatigue shown as the
  current chain's row; the two currencies separated in one sentence.
- Grand Concert: the loop (Lessons / Songs / gauge / cadence) is explained even where
  numbers are missing; payouts stated per action type (5 SP technique vs 25 SP song).
- The `index.html` known defect applies here: cockpit reports `nextTurnToPlay()` while
  Dashboard counts `turnEntries->count()` — label both until the defect is fixed (do not
  silently align them in this slice; it is a data-semantics fix, not layout).

Files: `resources/js/pages/Career/Cockpit.vue`, all `Cockpit/Panels/*.vue`,
`components/scenario/*.vue`, `components/career/{CockpitIdentityCard,StatSummary,CareerStatePanel,ActionGrid,AdvisorRail,NextDecision,RunRaceStrip}.vue`; `app/Http/Controllers/Career/CockpitController.php`.

**Gate addition:** `tests/browser/unity-cup-panel.spec.ts` baseline check — two known
failures predate this work (13 focusable elements + `<details>` in Cockpit panels,
outside the grid); re-run against baseline commit before attributing any failure.

### Slice 6 — Run lifecycle (`training-decision`, `race-decision`, `career-race-planner`, `event-decision`, `inheritance`, `skills-planner`, `run-deck`, `timeline`, `result`, `career-save-veteran`)

Key rulings:
- Training decision: five disciplines, one row each, gains right-aligned mono; Energy
  signed and coloured (down = blue `--down`, never red; DESIGN.md); Wit marked "no
  Energy" with a tag; recommendation preselected but all five stay selectable; outcome
  fields separate from the choice; keyboard path 1–5 select + Enter commit.
- Race decision / planner: blocked rows visible with the refusal reason ("Aptitude too
  low" — greyed, never hidden); comparison is a table, not two card grids; race facts
  only (win probability / expected risk stay on ADR-named hold — check ADR before
  rendering any invented figure).
- Inheritance: before/after cap-consequence table is the decision; spark families named in
  words (Blue = stat, Pink = aptitude, White = skill hint), colour reinforces; one pick
  per parent via radios; "Skip this moment" always offered; empty grandparent slots drawn
  dashed with "slot open".
- Timeline: turn numbers left-aligned monospace as the spine; phase grouping collapsible
  (DESIGN-CORPUS §5 pattern); blank is not zero (empty Energy/SP cells stay empty);
  finale block grouped separately outside the 72-turn grid.
- Result: leads with outcome + final totals, no aggregate score; honest finding printed
  ("won the finale with 344 Stamina against a published 600 guide" pattern); banking is
  the only filled action; four KPI tiles, one accented.
- Save Veteran: tags are suggestions with `aria-pressed` + visible check, free entry
  allowed; trainee name read-only (library row points at the run); the three unmeasured
  figures (factor analysis, legacy value, best use) print N/A with the ruling; an
  unfinished career gets the blocked state naming its status + two doors onward.

Files: `resources/js/pages/Career/{TrainingDetail,RaceDecision,RacePlanner,EventDecision,InheritanceEvent,SkillsPlanner,Timeline,Result,SaveVeteran}.vue` + deck-sync page; matching `app/Http/Controllers/Career/*Controller.php` + `TrainingRunController@deckBuilder`.

### Slice 7 — Legacy Lab & Veterans (`legacy`, `legacy-builder`, `legacy-compare`, `veterans`, `veteran-detail`, `veterans-compare`)

- Legacy index: saved **pairs** are the unit (sparks combine); value table three rows
  (blue grades + pink range); Compare is the filled action.
- Veterans index: three grade columns, never one total; real duplicate rows stay
  (two Vodka rows are two careers, not a bug); team-eligibility arithmetic done on the
  page ("7 registered · 3 needed · 2 teams available"); ★3 ceiling stated on Global with
  the JP-only ★4 research item named as such.
- Veteran detail: factor table first (a veteran exists to be inherited from), final stats
  second; five absent final-stat rows state the absence; "using it once does not consume
  it" answered on the page.
- Compare: differences in a column ("same" or the delta), matching rows still print;
  "Nothing decisive" is a permitted verdict.
- **Prop drift fix:** `VeteranController@index` passes `roster`; `Veterans/Index.vue`
  does not declare it. Either declare + render it (if HFW uses it — it does not) or stop
  passing it. Slice records the decision; default: stop passing.

Files: `resources/js/pages/{Legacy,Veterans}/*.vue`; `app/Http/Controllers/{LegacyController,VeteranController}.php`.

### Slice 8 — Library (`catalog`, `catalog-detail`, `support-cards`, `support-card-detail`, `skills`, `skill-detail`)

- Catalog: cards not a table; three aptitude letters per card (strongest per axis;
  detail owns the nine); debut form + its rarity named on the card; growth row on the
  card, zeros print `+0%`; release status is a word pill; JP-only = muted label.
- Support cards index: effect chips are words ("Initial Friendship Gauge"), never icons;
  Pal cards print "None" for friendship bonus; type counts on the page; the zero-coverage
  effect family stated; one row per card (detail owns the ladder).
- Support card detail: stage table with the anchors the source states (a missing level is
  not a zero — absent from `levels`, never printed as 0); break state = four diamonds,
  filled per break, level readout beside; duplicate rule stated; where-used answerable;
  provenance as a row + link to the source ledger (**no file paths in UI**).
- Skills index: engine condition in mono beside the readable name; base SP cost only
  (hint discount belongs to the run planner); search covers name and effect text; type is
  a filter, never a colour.
- Skill detail: engine tokens first, prose second; the plain description block prints the
  **"no client string"** empty state (every skill's `description` is null by ruling
  G-SK-20 — do not render `sourceText` as copy; it is reference data, never display).

Files: `resources/js/pages/{Catalog,SupportCards,Skills}/*.vue`;
`app/Http/Controllers/{CatalogController,SupportCardController,SkillController}.php`.

### Slice 9 — Database hub (`database*`)

- Hub: nine areas, one grid, no nesting; each tile names area + live count (prop delta:
  `areas[]` entries need `count` — currently `{label,url,source}` only); Sources tile
  carries the pick border (it is the ledger, the answer to every provenance question).
- "Verified 2026-09-27" printed as a verification date, never "updated today".
- Every area page (races/events/shop-items/sparks/scenarios/sources): "Effects
  documented?" honesty columns where the source lacks data; scenario scoping explicit;
  retry rules beside events; caveats on the row they qualify (Law of Proximity), not in
  footnotes.
- Breadcrumb: `Database / <Area>` in the topbar crumb (§3.3), replacing the dead topbar
  link.

Files: `resources/js/pages/Database/*.vue`; `app/Http/Controllers/DatabaseController.php`
+ the three `databaseIndex` ports.

### Slice 10 — Review (`review`, `review-detail`)

- Queue framed as a question, not an error (copy says so).
- Confidence is a word + policy: "Exact" decisive and pre-selected; "fuzzy" suggested,
  never pre-selected.
- "No match" is a first-class caution pill, never hidden.
- Right-hand card states the consequence (a wrong match corrupts every downstream
  screen).
- Detail: source and candidate side by side in one row; same-trainee-different-costume
  is its own candidate row; Reject is a real action; Confirm is the only filled action
  and is **not pre-checked**.

Files: `resources/js/pages/Review/{Index,Detail}.vue`; `app/Http/Controllers/ReviewController.php`.

### Slice 11 — Preferences, Help, Errors (`preferences`, `help`, `error-*`)

- Preferences: one save model — one `.savebar`, one verb, disabled when clean (this
  directly retires the cockpit's seven-verbs defect the HFW names); layout choices state
  widths ("Compact 768 / Standard 1152 / Wide 1536"); theme resolution order documented;
  language vs game vocabulary separated (client terms are proper nouns, never translated).
- Help: **new page + route.** `routes/web.php` add `Route::get('/help', …)->name('help')`;
  new `app/Http/Controllers/HelpController@show`; new `resources/js/pages/Help.vue`.
  Content per `help.html`: proactive help (the turn loop) leads, reactive lookup follows;
  reachable from every screen via the topbar Help button, deliberately absent from the
  rail (annotation 3); undo described as a gap, no inert buttons; rule rows are text, not
  dead links. New page = PRD citation needed before merge (AGENTS.md §11) — check
  `PRD.md` for the help requirement or escalate for an owner ruling first.
- Errors (404/419/500): designed states (DESIGN.md §1 p5); each answers the data-loss
  question first ("Your last saved turn is unaffected"); three exits, one primary
  (Dashboard filled); no auto-redirect; plain language, no raw error code as the headline.
  Locate existing error page components (Inertia `resolveErrorPage` or Blade error views)
  and port structure.

Files: `resources/js/pages/Preferences/Edit.vue`, new `Help.vue`, error pages;
`app/Http/Controllers/{PreferenceController,HelpController}.php`; `routes/web.php`.

---

## 6. Reserved: component additions

New shared components allowed by this plan (each one line, created on first real use —
no speculative abstraction):

| Component | When created | Replaces |
|---|---|---|
| `NavItem.vue` | Slice 1, only if the grouped loop stays trivial | inline nav `<li>` body |
| `TurnRail.vue` | Slice 2 | Dashboard's inline turn rail; reused by Cockpit if its strip matches |
| `PageBreadcrumb` slot content | Slice 1 | per-page crumb strings passed via layout prop |
| `KeyValueTable.vue` | only if ≥3 pages repeat the `.t` + label-column shape inline | — |

Everything else composes existing primitives (`.card`, `.tile`, `.pill-*`, `.slot-*`,
`.srow`, `.note`, `.t` tables via `DataTable`, `SubCell`, `ArtworkSlot`, `StatusPill`,
`Form/buttonStyles.ts` button classes).

---

## 7. Phase 4 — Backend integration

Prop deltas collected per slice. Rules: additive only within this major (old keys may be
dropped at 3.0.0 since it is a breaking release — confirm at ship time); every new prop
has a query source named; no prop invented to satisfy a static HFW figure.

### 7.1 `app/Http/Middleware/HandleInertiaRequests.php`

- Add shared prop `activeCareerCount: int` (query: count of runs with status Active).
  Powers the topbar `.who` pill on every screen. Cheap single count; no cache needed.

### 7.2 `DashboardController@index`

| Prop | Change | Source |
|---|---|---|
| `finishedRunCount` | add (int) | runs count where status Completed |
| `activeCareer.recorded_turns` | add (int) | `turnEntries->count()` for the run — prints beside `turn` (`nextTurnToPlay()`) until the known defect is fixed |
| `needsYou.reviewCount` | add (int) | pending review candidates count |
| `needsYou.dataStatus` | reshape from `dataStatus` | reuse existing `dataStatus` payload; add `stale: bool` if the tile's risk border needs a flag the label doesn't carry |
| `counts.forms` | optional add | catalogue forms count, only if a kept card needs it |
| `counts.trainees` | **ruling needed** | HFW prints the catalogue count (67); app counter reads 71 (dev fixture rows). Catalogue count source: `assets/data` build or a sourced-join query per `assets/data/README.md` "rules a consumer inherits". Recommend: keep app count, add `counts.catalogueTrainees`, print catalogue figure with a footnote — owner ruling recorded in slice report. |

### 7.3 Other controllers

- `DatabaseController@index`: `areas[]` entries gain `count: int` (9 live queries, one per
  area — measure; if N+1 risk, one grouped query).
- `VeteranController@index`: drop `roster` prop unless Slice 7 records a use.
- `TrainingRunController@index`: confirm `turn_count` = `CareerCalendar::TURNS_PER_CAREER`
  (72) renders as the denominator; no change expected.
- Review/Detail: confirm `candidate` payload carries both source and candidate sides for
  the one-row comparison; add `forms[]` for the same-name-different-costume rows if the
  current payload collapses them.
- Every controller: where the HFW prints an absence with a reason (Energy "not recorded",
  ruleset N/A), the controller passes `null` and the **reason string** comes from the Vue
  page or `lang/en/uma.php` — never a fabricated reason from PHP.

### 7.4 Routes

- Add `help` route only (Slice 11). No other route changes. Wizard, snapshot, cockpit,
  and library routes are untouched.

### 7.6 Prop deltas — corrections recorded 2026-10-29

The three deltas collected in §7.3 were re-measured against the shipped tree; the owner ruled on
them on 2026-10-29 (`DECISIONS-2026-10-29.md`, `.scratch-uma/session-2026-10-29/proposals/`):

- **`globalCount` (SupportCardController).** Shipped as `globalCount` — camelCase — from
  `SupportCard::query()->whereNotNull('release_global')->count()` at `:158`, pinned by
  `SupportCardPageTest:71`. The plan's earlier `global_count` / `release_en` named a parser key,
  not the stored column; the correction landed with the prop (`ca7d4828`).
- **`DatabaseController.areas[].badge` — dropped.** `area.tag` already ships and the Hub binds
  it; the Races "Catalog" badge in `database.html` is a mock figure over a live count — the count
  wins, and the deviation is recorded.
- **`VeteranController.used_as_parent_count` — dropped.** There is no legacies table and no
  per-veteran parentage; `StartCareerRequest` collapses the picked veteran to `traineeId` and
  ancestors are stored as names, so the metric requires a new schema, not a delta.

---

## 8. Phase 5 — Annotation compliance sweep (final gate, Slice 12)

A cross-cutting audit pass after all page slices land. Each item below is a checkable
rule; the sweep runs them repo-wide over `resources/js/**`:

### 8.1 The twelve-plus recurring annotations (every page)

1. **Rail collapse** — word never leaves the DOM (`sr-only` + `title`), active item keeps
   `aria-current="page"` + left rule; control at the rail head; `localStorage` not a
   Preference row. (Present in ~all keyboxes.)
2. **One primary action per screen** — exactly one filled `.btn-primary` per page region
   set; all other actions outline or links. Grep each page for filled-button count.
3. **Absence is printed, not dropped** — missing values keep their tile/row and state the
   reason **visibly** (not hover-only). Remove every hover-only `title` that carries the
   only copy of a reason (current code carries several `title="No logged turn has…"` —
   move the reason into visible muted text or `.note`).
4. **No skeletons, no grey-box placeholders** — `ArtworkSlot` contract: a file the mirror
   lacks renders **nothing**, the row keeps its text. Audit for any `animate-pulse` /
   skeleton divs / placeholder boxes; delete. Note: the HFW ships a `.skel` primitive for
   the D-45 loading state ("rows in the panel's own shape"); the repo rule is stricter
   (no skeletons). Repo rule wins — a loading panel prints real copy, not shimmer rows.
5. **No `dark:` utilities** — theme flips via tokens only. Grep `dark:` in
   `resources/js/**`; expected count 0.
6. **State never rides on colour alone** — every status is word + (shape or position or
   tint); selected cards marked three ways (control + border + tint).
7. **Zeros print as `+0%`** in growth rows; a stat absent from the row is absent (never
   blank, never dash).
8. **Blank is not zero** — unrecorded Energy/SP cells stay empty with the absence stated.
9. **Provenance is a row and a link** — source family named + ledger link; no file paths
   in UI anywhere.
10. **No destructive action in list rows** — deletes live behind disclosure on the owning
    screen.
11. **Buttons use the shared style source** (`Form/buttonStyles.ts` /
    `.btn`-equivalents); one save model per screen (`.savebar`, disabled when clean).
12. **Mobile is a different shape** — below `md`, bottom bar only; no shrunk sidebar.
13. **Lore gate** — `php tools/lore.php`; every hit carries a ruling; delta vs baseline
    recorded.
14. **Client vocabulary verbatim** — skill names, type words, condition atoms print the
    client's/export's strings; no paraphrase (assets/data README rule 6).

### 8.2 Per-page keybox check

For each of the 49 HFW pages with a keybox: open the keybox, walk every `<li>`, mark
satisfied / not-applicable / defect-filed. Output: appended table in the release notes
(`CHANGELOG.md` via Docs Writer).

### 8.3 Sweep tooling

- Grep audits: `dark:`, `animate-pulse`, `title=` on non-interactive spans carrying
  reasons, `.btn-primary` count per page.
- Browser: `npm run test:browser` full suite; keyboard-path specs (review form labels,
  target sizes, focusable scroll regions) must stay green — they are named-protected in
  CLAUDE.md §7.6.
- Boost `browser-logs` + `last-error` after each slice's page loads.

---

## 9. Execution order and dependencies

```
S0 recon ──► S1 shell ──► S2 dashboard ──► S3 careers/import ─┐
                        └─► S4 wizard/snapshot ─► S5 cockpit ─► S6 lifecycle ─┤
                           S7 legacy/veterans ─► S8 library ─► S9 database ──►┤
                           S10 review ─► S11 prefs/help/errors ──────────────►┘
                                                        ▼
                                          S7 backend integration (lands per-slice,
                                          consolidated review here)
                                                        ▼
                                          S12 annotation sweep ─► release 3.0.0
```

Slices 3, 4, 7, 8, 9, 10, 11 are mutually independent after S1; parallelise across
sessions with disjoint file ownership. S5/S6 share cockpit components — run serially.
S1 blocks everything (every page inherits the shell).

## 10. Versioning

Breaking visual-structure change across every screen + prop contract additions + one new
route: **3.0.0**, per semver. `2.2.0` is wrong — a Trainer's learned layout changes on
every screen, which is the major bump this repo's versioning history treats as breaking.
Owner confirms at release; the plan proceeds identically either way.

## 11. Out of scope

- Any change to `docs/design-research/frontend-v2-wireframes/**` (reference artifacts).
- The `stripValues()` vs `nextTurnToPlay()` turn-count defect — labelled in UI, fixed
  under its own KNOWN-ISSUES entry, not here.
- Pipeline, parser, or schema work. Any prop that would need a schema change stops its
  slice for an Architect escalation (ADR-0015/ADR-0025 territory).
- Regenerating `assets/data/*` unless a slice proves a needed figure is absent.
- Deployment (non-goal, PRD §6.10). No auth work (none exists by design, NFR-1).

## 12. Risks and open questions

1. **Mobile "More" ruling** (§3.5) — owner decision, default keep `<details>`.
2. **`counts.trainees` source ruling** (§7.2) — app counter vs catalogue count.
3. **Help page PRD citation** (Slice 11) — new screen needs a requirement citation or an
   owner ruling before merge (AGENTS.md §11).
4. **Container-query breakpoints** — `@3xl`/`@5xl` resolve against `main`'s content box,
   not the viewport (measured: 1088px @1440vw). Any slice asserting "desktop layout at
   1280px" is wrong by a band; verify against the container.
5. **Baseline browser failures** — two `unity-cup-panel.spec.ts` cases fail at baseline;
   re-run failing gates against HEAD before attributing regressions.
6. **Dual form kits** — `components/ui/*` vs `components/Form/Base*` coexist. This plan
   does not consolidate them (separate defect); slices use whichever kit the page already
   imports, and new code uses `components/ui/*`.

---

## 13. Domain Mechanics Verification (binding acceptance criteria)

Added 2026-10-25. Every fact below was verified against `docs/UMAMUSUME_REFERENCE.md` (the
mechanics authority) and the codebase before being written here. Where the HFW shows a
simplification, **the mechanic wins**; the deviation goes in the Vue template as
`<!-- DOMAIN OVERRIDE: [reason] -->`. These are acceptance criteria for the named slices.

### DM-1. Turn structure: 72 + finale outside (Slices 2, 5, 6)

A career is exactly **72 turns** (`CareerCalendar::TURNS_PER_CAREER = 72`), plus the URA
finale period which is **not** turns 73–78. Acceptance:

- Every turn display renders `X of 72`; `total_turns` prop is strictly 72.
- The finale renders as a separate phase block (timeline slice) or a distinct Completed /
  post-72 state, never as numbered turns past 72.
- Dashboard and cockpit both read the same `turn` prop; the `recorded_turns` discrepancy
  note (§4.2) stays until the underlying defect is fixed.

### DM-2. Five stats, distinct roles (Slices 4, 5)

Speed (top speed), Stamina (maintain speed), Power (acceleration/uphill), Guts (final
phase / last spurt), Wit (skill activation/positioning) — §1.3.1. Acceptance:

- `.stat-*` bands map exactly to these five; Guts and Wit are never merged or cosmetic.
- Absent-stat reasons name the role (e.g. Wit = skill activation), per §1.3.1 wording.

### DM-3. Four spark colors, not three (Slices 4, 6, 7)

§1.5.2, `[Global]` names: **Blue** (stat), **Pink** (aptitude), **Green** (unique skill,
carried as 1–3 hint levels), **White** (skill hints mid-run, and competition sparks from
G1 wins). Only Blue and Pink touch *starting* bonuses; Green and White trigger at
inspiration events. Acceptance:

- `LegacySelect.vue`, `InheritanceEvent.vue`, `Legacy/*`, `Veterans/*` render all four
  families with the correct color mapping, even where the HFW drew three.
- Green and White are labelled as hint/skill sparks, never as flat stat boosts.
- Star count is rolled, never presented as a choice; the 600/1100 stat thresholds are
  probabilities, not a deterministic mapping (§1.5.3).

> **Amended 2026-10-27.** The round-2 session measured five spark families in the inheritance
> payload, not four. `LegacySelectionPayload::SPARK_KINDS` is `['blue', 'pink', 'green', 'white',
> 'scenario']`, and its own docblock states that `white` is one label over two dataset categories
> (the skill family and the competition family) and that `green` is the unique-skill family. The
> three families the HFW draws are Blue, Pink and the shared unique-skill family; the two the
> payload adds are Green and White. DM-3 is amended to read "four family roles across five colour
> keys" and the Inheritance screen (commit `f0caa281`) prints the five families with their roles
> named. Any future slice must re-measure the payload before assuming four.

### DM-4. SP cost with hint-discount ladder (Slices 6, 8)

**Erratum 2026-10-27 — this row is not actionable, and the ladder is not built.** `ADR-0011`
("No hint levels, no discounts") and `ADR-0029` D-note ("hint levels stay 'level only, no
percentage'") both precede this row and both bind it: no column anywhere stores a hint level,
so `floor(base × (1 − discount))` has no input to compute from, and a discount printed from a
ladder constant would be a figure the database cannot corroborate. `SkillsPlanner.vue` ships
the base cost and states the absence in words; `tests/browser/career-skills-planner.spec.ts`
pins both halves (`'Hint discounts are not modelled.'` visible, no `Hint Lvl` string
rendered). Implement this row only if a later ADR adds a hint-level column; until then the
acceptance below describes a screen that must not exist.

§1.1.4, client-measured ladder: **Lv1 10%, Lv2 20%, Lv3 30%, Lv4 35%, Lv Max 40%** —
not linear past Lv3; displayed cost = `floor(base × (1 − discount))`. Acceptance:

- `SkillsPlanner.vue` shows base SP cost **and** discounted cost when a hint level is
  applied (e.g. `130 SP → 84 SP` at Lv4).
- `Skills/Show.vue` (library) prints the base cost only, per the HFW ruling — the discount
  belongs to the run planner, not the library row.
- No flat-cost-only rendering in the planner. If the prop carries `hint_level`, compute
  the discounted value with the ladder above; add a feature test pinning 10/20/30/35/40.

### DM-5. Mood pill exact percentages (Slice 5)

§1.1.6, client-confirmed: GREAT +20% training / +4% race, GOOD +10%/+2%, NORMAL 0/0,
BAD −10%/−2%, AWFUL −20%/−4%. The color ramp is **not monotone** — pink, orange, gold,
**blue** (Bad), **purple** (Awful) — the app's `--color-mood-*` token ramp already follows
this; do not "fix" it to a gradient. Acceptance:

- `MoodPill` renders word + arrow + percentage (D-259), e.g. `GREAT ↑ +20%`.
- `moodEffect.trainingPct` / `race_pct` props carry exactly these values; no other scale.

### DM-6. Four running strategies (Slices 4, 6)

§1.2.2: Front Runner, Pace Chaser, Late Surger, End Closer — `running_style` 1–4.
A race carries **no** style; style is a trainee property (do not invent a race-style
field). Acceptance:

- Aptitude letters (S–G) render per style where data supports four distinct aptitudes.
- No collapsed single "Style" field when the prop carries four.

### DM-7. Energy, failure, Rest (Slices 5, 6)

§1.1.5: failure risk scales inversely with Energy, bending around the **50-Energy** line
(not a hard threshold — §2.6 correction). Rest returns **+50 modal, +70 big success, +30
failure** (with possible Late-Night Feeling). Wit training costs 0 Energy. Acceptance:

- Energy tiles print the numeric value or "not recorded" — never a bare "Low" badge
  without the number.
- Where a Rest action is shown, the recovery figures print as the modal (+50) with the
  30/70 branches stated, not a single guaranteed figure.
- No "50 Energy = failure cliff" copy; it is a risk bend.

### DM-8. Deck: six slots, guest sixth (Slice 4)

§1.4.5 + deck HFW: six slots, five owned + one guest rental; duplicates cannot be
equipped; Pal cards print "No friendship bonus" when the field is null. Acceptance:

- `DeckSelect.vue` marks the sixth slot visually distinct (guest/borrowed).
- Duplicate-equip attempts are prevented and the rule stated on the page.
- `null` friendship bonus renders the word "None", not a blank chip.

### Verification sources

`docs/UMAMUSUME_REFERENCE.md` §1.1.4, §1.1.5, §1.1.6, §1.2.2, §1.3.1, §1.4.5, §1.5.2,
§1.5.3, §2.6; `app/Support/CareerCalendar.php` (`TURNS_PER_CAREER`).
