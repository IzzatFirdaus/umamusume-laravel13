# 01 — Design System Assessment

Comparison of current rendered design practices against `DESIGN.md` and `SCREEN_SPEC.md`.

## 1. Existing design tokens and structural rules

### 1.1 Token source of truth
- All colour, font, radius, and container tokens live in `resources/css/app.css` `@theme static` block plus `html[data-theme='dark']` and `html[data-theme='eva01']` blocks. `DESIGN.md` §2.1, §9.
- Token count: `tests/Feature/DesignTokensTest.php` pins `toHaveCount(102)` unique `--color-*` names in the static block (2026-11-14 ratchet; previous pins were 59→81→89→94→95→102).
- `DESIGN.md` §2.1 documents 21 role rows plus token families (step scale, ink step, green family, goal pair, mood, stat bands, grade badges, identity). The file is explicitly **documented, not re-verified** on this host — contrast ratios are transcribed from research captures whose evidence files are absent from the tree (`DESIGN.md` §10).
- Radius scale: `--radius-sm: 6px`, `--radius-md: 16px`, `--radius-lg: 24px`, `--radius-xl: 16px`, `--radius-card: 10px`, `--radius-pill: 999px` (`app.css` :40-47). Cards take `rounded-md` or `rounded-lg`; dialogs `rounded-xl`; pills/chips `rounded-pill`; `turn-chip` pinned to `radius-sm`.
- Shadows: none on cards/tables/list rows/form fields. Single `--shadow-overlay` tier for overlays only (modal dialogs, floating panels, recommendation card, dropdown menus), via `filter: drop-shadow()` not `box-shadow` (`DESIGN.md` §2.3 amendment 2026-10-15).
- Three franchise motifs are material, not elevation: enamel sheen (primary action button only), argyle lattice bleed (capsule headers), torn-page turn chip (turn widget / resource strip) (`DESIGN.md` §2.3).
- Surface hierarchy (current, post-2026-10-24 rulings): page / panel / raised / sunken / idle / rule-soft. `raised` stays white for data cells in light; dark is a measured palette, not an inversion.

### 1.2 Typography scale (DESIGN.md §2.2)
| Tier | Contains | Treatment |
|---|---|---|
| Critical | Page title | `text-3xl font-semibold text-ink-strong` |
| Heading | Section headers, panel titles | `text-lg font-semibold text-ink-strong` |
| Body | Content, table cells, form values | `text-sm` |
| Metadata | Labels, hints, timestamps | `text-xs text-ink-muted` |

- UI text: `--font-sans: ui-sans-serif, system-ui, sans-serif`. Numerals/stats/dates/ids: `--font-mono` with `tabular-nums`.
- One `h1` per page: emitted by `AppLayout.vue`'s `#title` slot (fallback band) or by `PageHeader.vue` (adopted pages). `CareerLayout.vue` and `SetupLayout.vue` forward titles into the shell slot. Never a second title at the same weight; content headings begin at `h2`; card block titles sit at `h3` under `h2` region headings.
- Font-variant-numeric: tabular-nums on every stat cell and timestamp.

### 1.3 Spacing and layout (DESIGN.md §2.3)
- Desktop-first, primary target 1280px+. Ratified shell widths: 5xl shell (legacy), 3xl form, lg narrow form. Bento shell default is `max-w-6xl` (1152px); user-selectable wide shell is 1536px (`--container-shell-wide: 96rem`, `max-w-shell-wide`) gated by `dashboard_layout = 'wide'` + `DASHBOARD_WIDE_BENTO_ENABLED`; compact is `max-w-3xl` (768px reading measure). Stored values: `compact` / `standard` / `wide`, mapped by `resources/js/composables/useLayout.ts` (`LAYOUT_WIDTH_CLASSES`).
- 768px is the supported minimum but marked **held, not ratified** (`DESIGN.md` §2.3 R85). Race calendar and turn log scroll horizontally inside focusable regions below 768px.
- Scale: Tailwind 0.25rem increments, no arbitrary values. Table rows `py-1.5 px-3`; forms `space-y-3`; cards `p-4`; section gaps `mt-8`.
- Radius `rounded-md` consistently; borders `1px --color-rule`.
- Structure per surface: one h1, one primary action per screen, filters as a single inline form row, pagination under tables.

### 1.4 Layout primitives (the shared grid)
`shell.css` grid primitives, ported to `resources/css/layout-grid.css`:
- `.g-2`, `.g-3`, `.g-4`: equal column grids via container queries — 1 col below canvas 585px, 2 cols at 585px+, `.g-3` → 3 cols at 845px+, `.g-4` → 4 cols at 845px+.
- `.g-side`: 2.1fr / 1fr two-column primary:supporting split at 585px+.
- `[data-layout='compact']` overrides all grids to 1 column.
- `.tiles`: `gap:9px`, auto-fit columns `minmax(7.5rem, 1fr)` (KPI strip).
- `.tile`: bordered raised cell (8px/10px padding, radius-sm).
- `.card`: `bg-panel`, `border rule-soft`, `3px left edge --rule-card-edge`, `radius-card` (10px), padding `14px 16px`, optional blue-tinted `--shadow-card` in light only.
- `.card + .card`: `margin-top:14px` (column-stacked groups). `.grid > .card + .card`: `margin-top:0` (grid siblings separated by gap only).
- `.card > h2`: `font-size:16px font-weight:800 text-ink-strong`, `border-bottom rule-card-head`, `padding-bottom:9px margin-bottom:12px`.
- `.tile .k` / `.tile .v`: label/value tile pair; `.tile .v.na` for absences.
- `table.t`: the HFW data-table grammar (12.5px body, 7px/9px cell padding, 10px uppercase kicker, `rule-soft` row hairlines, 44px cell targets for radios/checkboxes).
- `.card:has(> table.t)`: card as scroll region for wide matrices.

### 1.5 Shell structure (AppLayout.vue)
- Sticky rail: 236px wide collapsed to 56px; 22px brand tile; group headings (sr-only when collapsed); 44px foot; version line; `Data sources` link.
- Ten primary nav destinations in four groups (Play / My data / Library / System). Four carry `inBar` on mobile (Dashboard, Careers, Legacy Lab, Support Cards); six behind More.
- Topbar: brand-chrome fill with brand-accent 2px hairline at bottom edge; crumb (interpunct-separated, leaf bolded); `#who` slot (one pill only — shell default is active-career count; page fills: Review "N pending", Preflight "N warning", Veterans "N banked"); Help link.
- Page head: `PageHeader` component in `#head` slot (eyebrow + h1 + lede + hr + actions). Fallback band for pages not yet adopted.
- Canvas: `@container` (container-type: inline-size) on `main`; `position: sticky` is a no-op for descendants (load-bearing, proven by `tests/browser/containment-mechanism.spec.ts`).
- Mobile: fixed bottom bar, five slots + More disclosure (`<details>`), `scroll-padding-bottom: 4rem` on `html`.
- No `bg-page` on the shell wrapper — `body` carries `app-backdrop` (page fill + decorative plate).

## 2. Dashboard reference analysis (SCR-CAR-001, `/`)

The Dashboard is the designated visual reference for the bento language. Source inspection (`resources/js/pages/Dashboard.vue`, `AppLayout.vue`, `layout-grid.css`, SCREEN_SPEC §4.21):

**Current structure**
- Page head: `PageHeader eyebrow="Dashboard" title="Trainer Desk"` with two secondary actions ("New career", "All careers"). The one filled action lives inside the active-career card ("Resume Career").
- Row 1: `g-side` grid — "Pick up where you left off" (≈2/3, active-career panel with artwork, trainee name, scenario pill, position, Resume button) beside "Needs you" (≈1/3).
- Row 2: `g-4` KPI strip — four aligned summary tiles (Careers running, Trainees, Skills, Support cards).
- Row 3: "Recent Veterans" panel beside "Quick actions" panel (wider/narrower).
- Row 4: "Data status" panel (GLOBAL DATA badge, verification date, ruleset row, three catalogue counts).
- Empty states: no active career, no Veterans, no turn logged — each names what is missing, why it matters, what to do.
- Loading: page-level `role="status"`. Error: page-level `role="alert"` with "Try again" button.

**Compliance findings**
- Compliant: five distinct purposefully sized panels; clear primary/supporting split; aligned metric cards; consistent panel backgrounds/radii/borders/internal spacing/gutters; one filled action per screen; `N/A` with reason for absences; empty/loading/error states all present.
- Minor inconsistency: the empty-state wrapper uses `class="card"` directly rather than the Section primitive; the KPI strip header row reads "7 veterans · 3 shown" as footer copy inside the grid.
- The Dashboard correctly uses `g-side` (2.1fr/1fr) for its primary:supporting split and `g-4` for aligned KPI tiles — this is the canonical bento composition to cite when assessing other screens.

## 3. Shared-component observations

| Component | Role | Consistency notes |
|---|---|---|
| `PageHeader.vue` | Page head band (eyebrow, h1, lede, hr, actions) | Adopted by 30+ pages; single `actions` slot; no title-leading slot — support-card detail keeps artwork frame in its own row under sub-nav because `PageHeader` can't lead with it (recorded HFW override). |
| `ArtworkSlot.vue` | Single owner of artwork slot contract | Three pinned sizes (size-16 detail, size-12 index, size-10 costume row); `reserve` prop on index rows only; decorative `alt=""` where name prints beside; `href`+`linkLabel` separate props; absent `href` → bare frame no anchor. |
| `AbsenceValue.vue` | Absence marker (`<details>` disclosure with reason) | Migration partial — Dashboard Energy/Team Rank tiles and `AncestryNode.vue` still use `title`-only (recorded as migration debt, not second channel). |
| `ProvenanceBadge.vue` / `ProvenanceMark.vue` | Single owner of state word per fact | Review queue previously double-printed (StatusPill + ProvenanceBadge) — fixed by one-absence-channel amendment. |
| `.card` / `.tile` / `.pill` / `.section` | Surface primitives | `.card` carries 14px/16px padding; `.tile` is raised bordered cell; pills via tone classes (positive/caution/neutral/accent). |
| `LinkButton` | Action button (primary/secondary/ghost) | One filled primary per screen convention measured across 48 routes (UX audit X-4, 33 `bg-primary-action` consumers re-measured 2026-10-22). |

## 4. Common inconsistencies (cross-screen)

| # | Issue | Affected screens | Rule cited | Correction |
|---|---|---|---|---|
| C-1 | Empty-state wrapper uses bare `class="card"` instead of Section primitive | Dashboard | Section/surface consistency | Wrap in `Section` or align padding with `.card` token |
| C-2 | `title`-only absence channel still ships | Dashboard (Energy, Team Rank tiles), `AncestryNode.vue` | One absence channel per fact (§5 amendment 2026-10-17) | Migrate to `AbsenceValue` disclosure or visible prose |
| C-3 | `Career tools` disclosure below HFW's last region holds writes that have no dedicated doors | Cockpit | HFW override register (SCREEN_SPEC §8.25); KI-144 | Owner decision: retire disclosure once doors exist (Slice 6b) |
| C-4 | Review detail double-prints tier state (StatusPill "No match" + ProvenanceBadge "Unknown") | Review queue (historical; fixed) | One provenance-state label per fact (§5(b)) | Fixed — badge keeps state, pill dropped when tier not Fuzzy |
| C-5 | 768px minimum unratified; race calendar / turn log scroll horizontally below 768px | Run detail (retired), Cockpit race calendar, Race Planner | DESIGN.md §2.3 held proposal R85 | Ratify with measurement or withdraw the number (owner question 5) |
| C-6 | No `dark:` utilities anywhere; themes flip via CSS variables only | All | DESIGN.md §2.1 (D-101, G-19) | Compliant by construction — no action |
| C-7 | Inline `outline-green` overrides deleted (22 sites, 7 components) | Historical | DESIGN.md §8 ring token | Fixed — base-layer `--color-ring` now supplies colour |
| C-8 | `subnav` strip overflow rule wrapped unconditionally | Database pages with subnav | `layout-grid.css` `.subnav` media wrapper | Restore media wrapper (noted in file, pending move to app.css) |

## 5. Bento suitability criteria

Derived from DESIGN.md §1 (data first, chrome last), §2.3 (bento shell widths, `g-side`/`g-4` primitives), SCREEN_SPEC §2 (screen inventory areas), and the Dashboard reference:

**Bento-primary (A)** — deliberate grid of several distinct panels with different visual weights:
- Dashboard (landing, resume + status + KPIs + veterans)
- Career Cockpit (run-scoped primary screen: header strip, advisor rail, scenario region, panels)
- Career Scenario detail (12-track card grid + reference strip)
- Database hub (eight area tiles launcher)

**Bento-hybrid (B)** — dominant content region supported by smaller contextual panels:
- Career Training Decision, Race Decision, Event Decision, Inheritance Event, Skills Planner, Career Result, Support Deck Builder, Legacy Lab index/builder, Catalog detail, Skill detail, Support-card detail, Veteran detail, Team setup

**Conventional (C)** — bento would reduce readability or fragment interaction:
- All wizard steps (scenario, trainee, profile, build target, legacy select, deck select, preflight) — sequential single-column forms
- Runs list, import form/preview — table + single form
- Catalog index, Skills index, Support-cards index — roster/tree lists
- Review queue, Review detail — candidate cards + resolve form
- Veterans library, Transfer requests — lists
- Run settings, Confirm delete, Turn edit — forms
- Race Planner — calendar + comparison table
- Timeline — chronological list
- Database reference pages (races, scenarios, events, shop items, sparks, sources, trainees, supports, skills, race detail) — catalog cards + tables
- Preferences — settings form
- Help — documentation cards
- Error documents (404, 419, 500) — standalone documents

**Approved exception (D)** — none explicitly recorded; HFW overrides preserve intentional structure on 5 career screens (Cockpit priority label, Skills savebar, Deck no gauge, Cockpit `Career tools` disclosure, Review detail verdict, Support card detail artwork position, Legacy/builder comparison tables, Veteran comparison sticky matrix).

## 6. Theme and responsive findings

**Themes (Azure Daybreak light / Midnight Circuit dark / EVA-01 eva01):**
- Light is the base palette; dark and eva01 are measured palettes of their own, applied via `html[data-theme='...']`. No `dark:` utilities anywhere.
- EVA-01 opts out of the decorative backdrop plate (`--backdrop-image: none`).
- `--color-risk` as text on `page`/`raised`/`panel` was stepped to `#FF6F3B` (dark) to clear 4.5:1 (BO-004, 2026-11-16).
- Eva01's `rule on page` (2.70:1) is the one owner-accepted G-18 exception, omitted for that theme only.
- **Unverified:** no live browser session, so theme rendering at each viewport could not be visually confirmed. All theme/viewport combinations are source-only inference.

**Responsive behavior:**
- Breakpoints are container-query based on the `@container` canvas (`main`), not viewport: 585px and 845px crossings in `layout-grid.css`.
- Compact mode (`data-layout='compact'`) forces all grids to 1 column.
- Mobile bottom nav: 4 `inBar` destinations + More disclosure for the other 6.
- Below 768px: race calendar and turn log scroll horizontally inside focusable regions (`overflow-x-auto`, `role="region"`, `tabindex="0"`, `aria-label`) — deliberate contract, not reflow.
- `position: sticky` is a no-op for descendants of `main` (@container) — sticky elements cannot be used inside the canvas.
- **Unverified:** visual confirmation at 1280/768/390/320 for each screen and theme — no live browser session available.

## 7. Evidence limitations

- The dev server at http://127.0.0.1:8000 was not running and could not be started (read-only boundary). No screenshots or live DOM captures exist for this audit.
- All layout assessments are derived from: Vue component templates (`resources/js/pages/**`), layouts (`resources/js/layouts/**`), CSS (`resources/css/app.css`, `resources/css/layout-grid.css`), SCREEN_SPEC.md state tables and structure descriptions, and the HFW override register.
- Contrast ratios and token measurements in DESIGN.md are documented, not re-verified here (the browser contrast gate is implemented but unrun on this host due to sandbox write denial on `pest-plugin-browser/.temp/`).
- Every screen is marked "source-only" in the theme/viewport matrix above.

---

## Re-validation — 2026-11-23 (modernization pass)

**Repository HEAD at re-validation:** `3600741e`
**Document baseline:** 2026-11-22
**Live server status:** NOT running (read-only boundary stated in §7)
**All theme/viewport combinations:** Source-only inference (no live browser session)

### Claim status at current HEAD

| Claim | Status | Evidence |
|---|---|---|
| `tests/Feature/DesignTokensTest.php` pins `toHaveCount(102)` unique `--color-*` names | **STATICALLY VERIFIED** | Test file present; ratchet history 59→81→89→94→95→102 |
| Token source of truth: `resources/css/app.css` `@theme static` block | **CONFIRMED** | `app.css` unchanged since baseline |
| Radius scale: `--radius-sm: 6px` through `--radius-pill: 999px` | **CONFIRMED** | `app.css:40-47` |
| `position: sticky` is a no-op for descendants of `main` (@container) | **CONFIRMED** | `tests/browser/containment-mechanism.spec.ts` present |
| Dashboard as canonical bento reference | **CONFIRMED** | Source inspection; `g-side` (2.1fr/1fr) + `g-4` KPI pattern |
| 8 cross-screen inconsistencies (C-1..C-8) | **CONFIRMED** | Source inspection; C-1..C-4 still present per §4 table |
| Bento suitability matrix (A/B/C/D) | **CONFIRMED** | Source inspection; no layout changes since baseline |
| Theme rendering at each viewport | **UNVERIFIED** | No live browser session; dark/eva01 not audited |
| Contrast ratios from DESIGN.md §10 | **UNVERIFIED** | Browser contrast gate unrun (sandbox write denial on `pest-plugin-browser/.temp/`) |

### Commands executed at re-validation

None — read-only source inspection.

### Limitations

Dev server not running; no screenshots or live DOM captures. All layout assessments are source-derived from Vue templates, CSS, SCREEN_SPEC.md state tables, and HFW override register. Contrast ratios transcribed from research captures whose evidence files are absent from the tree (`DESIGN.md` §10).
