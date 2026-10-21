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
| **Fitts's Law (3)** | **Every control is 44px minimum.** The app ships `h-11` for all of them (`CapsuleHeader.vue`, `TraineeCombobox.vue:467`, `VeteranRow.vue:79`) and `SLICE-RECORDS.md:4716-4730` measures 44px in a browser rather than reading it. An earlier draft of this sheet used 40px for `.btn`, 32px for `.btn-sm` and 34px for `.step`, all below the contract; they are 44px now. |
| **Goal-Gradient (18)** | The career goal is in the first viewport on every run screen. The build-target step exists so that bar has a denominator; today every target is `null`. |
| **Hick's Law (4)** | Four scenario cards, not a dropdown. Ten nav destinations grouped into four bands. |
| **Miller's Law (5)** | Three aptitude letters per card on an index, nine on the detail. Chunked, not trimmed. |
| **Von Restorff (15)** | One filled action per screen. The nine saturated lime section bars the external review measured are gone; headings are ink plus a rule. An unmarked figure is the norm, so only exceptions carry a provenance mark. |
| **Serial Position (16)** | The primary action leads; destructive actions sit last, behind a disclosure. |
| **Peak-End (17)** | The result screen leads with the outcome and states the honest finding. |
| **Doherty Threshold (23)** | Every write has a pending label and a saved confirmation. |
| **Jakob's Law (2)** | Client grammar reused where it exists: four break diamonds, the gauge at 80 turning orange. |
| **Prägnanz (10)** | One vocabulary, one owner: Performance types come from the enum, not from the template. |
| **Occam's Razor (21)** | One save bar, one verb. Seven save verbs in four styles is the defect this replaces. |
| **Law of Common Region (8)** | One field grid, one left edge, three fieldsets. |
| **Selective Attention (33)** | The goal-relevant datum wins. The mood readout is word + arrow + percentage because at equal luminance `GOOD` and `BAD` are 3.3 degrees of hue apart, so colour alone cannot order them (`DESIGN-CORPUS.md:2469`, D-259). |
| **Law of Prägnanz + D-259** | Mood renders `GREAT ↑ +20%`, not `GREAT` and not a colour. The arrow is the only part carrying the ordinal direction. |
| **Nielsen H1 / H9 (D-45)** | Four states on every data view: empty, loading, populated, error. `.state`, `.skel` and `.state-error` are the primitives. |
| **Skill: Consistency** (uilaws) | One selection grammar — radio, pick border, tint — on every picker in the set. |
| **Skill: Contrast** (uilaws) | Colour never carries meaning alone. Every tint has a word beside it. |

## Two rules this set had to be corrected against

Both were real defects found by reading the sources, not styling preferences.

**D-81 — a prototype control either works or says it does not.** Only the Settings page's theme and
layout-width controls are live. Every other control in this set is a drawing, so every page carries a
`Static wireframe` chip in its top bar. A control that looks live and is not is the
mockup-wearing-a-product tell (`DESIGN-CORPUS.md:1992`).

**The four-value trust vocabulary, and `confirmed` renders nothing.** `ProvenanceMark.vue:46-53` defines
exactly `confirmed | calculated | estimated | unknown`, and `:52` returns `null` for `confirmed` — "only
the exceptions are marked, so the eye lands on the exception". An earlier draft printed a `confirmed`
chip on five figures and invented `rule`, `source` and `found` as if they were trust states. Those chips
are gone: an unmarked figure is confirmed, and only `Calculated`, `Estimated` and `Unknown` carry a glyph.

## Where the data comes from

| Kind | Source |
| --- | --- |
| Live run and dashboard values | Inertia props read from `http://127.0.0.1:8000` on 2026-10-21 |
| Scenario rules, caps, mechanics | `docs/UMAMUSUME_REFERENCE.md`; `docs/proposals/URA-Finale-plan.md`, `Unity-Cup-plan.md`, `Trackblazer-plan.md` |
| Scenario mechanics, per-scenario | `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` (the GameTora and uma.guide sections) |
| Support cards, the 35-effect dictionary | `docs/research-scratch/SUPPORT-CARDS.md` |
| Character and catalog fields | `docs/research-scratch/CHARACTERS-SOURCE.md` |
| Skills, hint ladder, acquisition paths | `docs/research-scratch/SKILLS-MECHANICS.md` |
| Import format and field honesty | `docs/research-scratch/TRAINING-RUN-IMPORTS.md` |
| Component shapes, heuristics, states | `docs/research-scratch/UX-DELIVERABLES.md` |
| Design contract, D-rules, scenario matrix | `docs/research-scratch/DESIGN-CORPUS.md` |
| Law inventory and audit method | `docs/research-scratch/PLANS-AND-BRIEFS.md`, `PROCESS-PLANS.md` |
| Race and slice research | `docs/research-scratch/RACE-AND-SLICE-RESEARCH.md`, `SLICE-RECORDS.md` |
| Tokens | `resources/css/app.css` `@theme static`, light theme |

Three of the four cockpits carry a **MOCK DATA** banner, because the dev database holds one Unity Cup
run and ten URA Finale runs, and nothing for Trackblazer or Our Grand Concert. The banner is on the
page, not buried here.

## Factual corrections this pass made

Recorded because each was a wrong claim in an earlier draft, not a restyle.

| Page | Was | Now |
| --- | --- | --- |
| `legacy` | Blue spark = `+16 cap`, Pink = `+5/+12/+21` | **Blue is the stat spark** (`+5/+12/+21` at ★1/★2/★3) and **Pink is the aptitude spark** (`+1/+2/+3/+4` grade at ★1/4/7/10-18). The `+16` cap raise is the breakthrough mechanic, a different thing entirely (`UMAMUSUME_REFERENCE.md` §1.5.2, §1.3.4). |
| `veterans` | The `★4` ceiling stated as fact | **Three stars is the Global ceiling.** The `★4` cap is the JP-only factor-research item and is named as such (`UMAMUSUME_REFERENCE.md` §1.5.3, §1.3.6). |
| `training-decision` | Unity Cup run carrying URA Finale's gain and energy figures | **Unity Cup's own Lv 1 base**: Speed `+8/+4 Power/+2 SP/−19`, Stamina `+7/+3/+2/−17`, Power `+4 Stamina/+6/+2/−18`, Guts `+3 Speed/+3 Power/+6/+2/−20`, Wit `+2 Speed/+6 Wit/+3 SP/+5` (`SCENARIO-PUBLISHER-REFERENCES.md` GameTora). |
| `training-decision` | Rest `+30 on a normal result` | **`+50` is modal at 58.1%**, `+70` 25.4%, `+30` 12.8%, `+30` + Late-Night Feeling 3.7% (n=3,833). `+30` was the failure outcome printed as the normal one. |
| `training-decision` | Energy `50 advisory line` tagged `confirmed` | Tagged as an **editorial heuristic**, because no tier-A source publishes a failure table by energy level (`UMAMUSUME_REFERENCE.md` §2.6). |

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
