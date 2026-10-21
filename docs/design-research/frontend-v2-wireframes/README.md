# Trainer Desk v2.0.0 — full frontend wireframe set

Static HTML wireframes of every screen in the running app, rebuilt against the UX-law and UI-law
findings. **"Working" means the navigation, the layouts and the states are real; nothing is wired to a
database.** No component, token, copy string, gate or test in the running app was changed.

Open [`index.html`](./index.html) and navigate. Every page shares one shell and one stylesheet, so a
click from the dashboard reaches the whole surface.

## What this set covers

47 screens, matching `SCREEN_SPEC.md`'s registry rather than a hand-picked subset.

| Group | Screens |
| --- | --- |
| Landing and careers | `index`, `runs-index` |
| Setup wizard (6 steps) | `career-scenario`, `career-trainee`, `career-profile`, `career-target`, `career-legacy`, `career-deck`, `career-preflight` |
| Cockpit, four scenarios | `cockpit-unity-cup`, `cockpit-ura-finale`, `cockpit-trackblazer`, `cockpit-grand-concert` |
| Run lifecycle | `training-decision`, `race-decision`, `event-decision`, `inheritance`, `skills-planner`, `run-deck`, `timeline`, `result` |
| Import | `import`, `import-preview` |
| Legacy and veterans | `legacy`, `legacy-builder`, `legacy-compare`, `veterans`, `veteran-detail`, `veterans-compare` |
| Library | `support-cards`, `support-card-detail`, `skills`, `skill-detail`, `catalog`, `catalog-detail` |
| Review | `review`, `review-detail` |
| Database (9 areas) | `database`, `database-sources`, `database-scenarios`, `database-races`, `database-events`, `database-shop-items`, `database-sparks` |
| System | `preferences`, `error-404`, `error-419`, `error-500` |

## The laws this set applies, and where

Each page ends with its own annotation key naming the law behind each decision. The set's recurring
ones:

| Law | How the wireframes apply it |
| --- | --- |
| **Goal-Gradient (18)** | The career goal is in the first viewport on every run screen. The build-target step exists so that bar has a denominator; today every target is `null`. |
| **Hick's Law (4)** | Four scenario cards, not a dropdown. Ten nav destinations grouped into four bands. |
| **Miller's Law (5)** | Three aptitude letters per card on an index, nine on the detail. Chunked, not trimmed. |
| **Von Restorff (15)** | One filled action per screen. The nine saturated lime section bars the external review measured are gone; headings are ink plus a rule. |
| **Fitts's Law (3)** | Every control is at least 40px, most 44px. |
| **Serial Position (16)** | The primary action leads; destructive actions sit last, behind a disclosure. |
| **Peak-End (17)** | The result screen leads with the outcome and states the honest finding. |
| **Doherty Threshold (23)** | Every write has a pending label and a saved confirmation. |
| **Jakob's Law (2)** | Client grammar reused where it exists: four break diamonds, the gauge at 80 turning orange. |
| **Prägnanz (10)** | One vocabulary, one owner: Performance types come from the enum, not from the template. |
| **Occam's Razor (21)** | One save bar, one verb. Seven save verbs in four styles is the defect this replaces. |
| **Law of Common Region (8)** | One field grid, one left edge, three fieldsets. |
| **Skill: Consistency** (uilaws) | One selection grammar — radio, pick border, tint — on every picker in the set. |
| **Skill: Contrast** (uilaws) | Colour never carries meaning alone. Every tint has a word beside it. |

## Where the data comes from

| Kind | Source |
| --- | --- |
| Live run and dashboard values | Inertia props read from `http://127.0.0.1:8000` on 2026-10-21 |
| Scenario rules, caps, mechanics | `docs/UMAMUSUME_REFERENCE.md`, the four `docs/proposals/*-plan.md` files |
| Tokens | `resources/css/app.css` `@theme static`, light theme |

Three of the four cockpits carry a **MOCK DATA** banner, because the dev database holds one Unity Cup
run and ten URA Finale runs, and nothing for Trackblazer or Our Grand Concert. The banner is on the
page, not buried here.

## Token divergence you should know about

The wireframes use `app.css`'s values, because that is what the app renders. `DESIGN.md` §2.1's table
disagrees with the stylesheet on several roles — page `#F2F1F8` vs `#F4F4FF`, rule `#E4E1EA` vs
`#84828C`, `brand-accent-ink` `#8877FF` vs `#6554E0`, dark page `#0D0C0F` vs `#08070A`. That is a
documentation defect in `DESIGN.md`, recorded here and not fixed, because `DESIGN.md` is outside this
task's scope.

## Light and dark, and the three layout widths

Both preferences are **controls on the Settings page only**, matching the app. No page carries its own
theme or width switch.

### Theme

`resources/js/pages/Preferences/Edit.vue:315-327` is the app's one theme control: a select with three
values — **Follow the system / Light / Dark**. The wireframe's Settings page draws the same three
options as radios so they read side by side, and says so on the card.

- **Tokens are `app.css`'s dark block, verbatim** — page `#08070A`, panel `#26262E`, raised `#24262A`,
  rule `#6E6C73`, muted ink `#AAABB5`. Not an inversion.
- **The dark ramp is not simply darker.** `app.css` records that `panel` was lightened to `#26262E` so it
  sits *above* `sunken` `#1A181D`, mirroring the light ramp, and that the page/panel step cannot reach
  3:1 because the `ink-muted on panel` pair caps it at 4.5:1.
- **Resolution order (D-104):** stored preference → `prefers-color-scheme` → light. The pre-paint script
  runs in `<head>`, so there is no flash of the wrong theme. **Every page keeps that script**; what was
  removed was the duplicate switch, not the first-paint resolution.
- **`--status-caution` is not overridden in dark**, matching `app.css`.

### Layout width

`resources/js/composables/useLayout.ts:20-24` is the single width map, and `AppLayout.vue:422`,
`CareerLayout.vue:56` and `SetupLayout.vue:48` all apply it, so every page inherits it.

| Mode | App class | Measure | Wireframe |
| --- | --- | --- | --- |
| Compact | `max-w-3xl` | 48rem / 768px | `--shell-compact`, and every `g-*` grid collapses to one column |
| Standard | `max-w-6xl` | 72rem / 1152px | `--shell-standard` — the default when nothing is stored (`useLayout.ts:45`) |
| Wide | `max-w-shell-wide` | 96rem / 1536px | `--shell-wide` (`--container-shell-wide` in `app.css:48`) |

**Compact is a composition, not just a narrower cap.** `Dashboard.vue:168-169` returns `grid-cols-1` for
compact and keeps the multi-column grid for standard and wide (`:180`). The wireframes mirror that: in
compact, every multi-column grid collapses to a single reading column.

To switch: open **Settings**, choose a width, then navigate. The choice rides `localStorage` here and
every other page reads it on load.

The earlier cockpit set — [`../cockpit-wireframes-2026-10-21/`](../cockpit-wireframes-2026-10-21/) —
carries the same dark block and the same three widths, and reads the same two `localStorage` keys, so a
choice made here carries across to it.

**One concession, stated plainly:** both preferences persist via `localStorage` because this is a static
artifact with no database. The app stores them server-side — `PRD.md` §6.12 cuts browser storage as a
second source of truth. That is a wireframe workaround, not a proposal.

## What is not verified

- **No rendered screenshots.** Chromium cannot launch under this sandbox (`spawn EPERM`). Structure was
  validated by tag-balance checks across all 47 files; the pages were never seen.
- **No contrast arithmetic.** The pages use the runtime tokens; no pair was re-measured.
- **No 390px measurement.** The shell carries a mobile bar below 760px, but nothing was measured on it.
- **The cockpit set is split.** The rich treatments live in `../cockpit-wireframes-2026-10-21/`; the
  `cockpit-*.html` files here are the same records at shell density and link back to them.

## Files

- `assets/shell.css` — the whole visual system: tokens, primitives, nav, mobile bar, annotations.
- `*.html` — one per screen, each self-contained apart from that stylesheet.
