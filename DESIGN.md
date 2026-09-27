# DESIGN.md: Trainer Desk design system

Product name **Trainer Desk**, owner decision 2026-09-27 (closes PRD OQ-1).
This file is the design contract for the app's visual system. Owner rulings
recorded here: **light-first theme** (revised same day, see §2.1), tactical-athletic
identity, catalog-first design priority, and the lore-sensitive iconography boundary.

Design Read: personal analyst desk for one Trainer of Umamusume Pretty Derby,
tactical-athletic language, dial ENERGY 1 / RHYTHM 1 / MOTION 1.
(Reason: the only viewer is the owner mid-planning; decoration costs reading
speed and buys nothing. R-31.)

Relationship to `docs/design-research/DESIGN.md`: that document is the
screenshot-anchored research package (measured color ramps, contrast tables,
cap-stack rendering spec §6.21). This file does not re-derive it. **Amended
2026-09-27:** the app default is now the research package's measured **light**
system (its §3, D-100), and its raceboard **dark anchors** (§3.7, from the game's
own raceboard screenshot) are the supported override rather than the promoted
default. The earlier decision to ship dark-first is reversed by owner ruling;
the reason is recorded in §2.1, and the two themes are now both verified
surface-down rather than one being a derivative of the other. Where a value is
genuinely new here, it is marked `Proposed`.

## 1. Design principles

1. Data first, chrome last. Every pixel around a number earns its space by
   making the number faster to read (PRD NFR-3, Pre-Mortem: data-dense tool).
2. Colour is semantic only. A coloured element must change what the Trainer
   knows; otherwise it is neutral (research doc P1, antislop R-01/R-16).
3. Light base by default, measured not inverted, with dark as a preference-resolved
   opt-in (ADR-0006). Both palettes are measured from client frames; the dark one
   keeps the raceboard character rather than a generic dark skin.
4. The desk is serious: no pastel anime gradients, no soft drop shadows, no
   marketing layout. It reads like a sports-science or esports analytics
   tool, never like a gacha wiki (owner ruling; antislop Part 1).
5. Every state is designed: empty, loading, error, data (CONSTRAINTS C-7,
   antislop R-27).
6. Lore compliance is a design constraint, not a copy polish pass (see §6;
   CONSTRAINTS C-4 is a hard gate).

## 2. Visual identity

### 2.1 Color system

Tokens live in the `@theme static` block of `resources/css/app.css`
(Tailwind v4 CSS-first). Source of truth for full ramps and contrast math:
`docs/design-research/DESIGN.md` §3. **Light is the base palette** (owner ruling
2026-09-27, reversing the earlier dark-first promotion) because the client is a
high-key light interface — research D-100. Dark ships as
`html[data-theme='dark']` and is a measured palette of its own, not an inversion.
**Light is not a forced default:** resolution is stored preference → `prefers-color-scheme` →
light, so a Trainer on a dark OS gets dark (D-104, amended the same day; preferences go to
SQLite rather than `localStorage`, because PRD §6.12 cuts browser-side storage as a second
source of truth). G-18 requires every text/background pair to pass in both themes, and G-20
requires the first paint to already be the resolved theme.

**The table gives both values per role, deliberately.** One hex per role is the
defect that keeps reappearing in prose about this system: a role's colour is safe in
one theme and fails in the other, and a table with a single column silently teaches
the wrong thing. `chrome` is the clearest case — the ink-bearing green is `#4E7906`
with white letters in the default theme and `#7FCC09` with near-black letters in the
dark one. The same hue cannot do both jobs.

Implementation status, stated honestly: the four components built from the approved
prototype (`stat-band`, `resource-strip`, `guided-step`, `race-calendar`), `design-preview`,
and now `components/layout` render from the tokens below. The shell migrated on 2026-09-27
because system-follow made dark reachable and `bg-zinc-50 text-zinc-900` was holding a
near-white body while `--color-page` had already resolved to #0D0C0F — a dark theme with a
light page. The **four content pages still do not**: `catalog/index`, `catalog/show`,
`review/index`, `runs/create` keep the skeleton's `zinc-*` utilities, so their tables, forms
and badges ignore `data-theme` and G-18 fails on them while passing everywhere a token is
used. Migrating them is the remaining
work, not a contract change.

| Role | Token | Light (default) | Dark (override) | Why |
|---|---|---|---|---|
| Page field | `--color-page` | `#F2F1F8` | `#0D0C0F` | pale lavender; raceboard surround, measured |
| Panel surface | `--color-panel` | `#F8F8FB` | `#121013` | card and modal body; board charcoal |
| Raised surface | `--color-raised` | `#FFFFFF` | `#24262A` | stat cells; unlit LED cell |
| Primary ink | `--color-ink` | `#6A5641` | `#ECEAF2` | warm brown, never grey-900, never black |
| Heading/value ink | `--color-ink-strong` | `#482720` | `#FFFFFF` | display numerals, and every grade letter |
| Muted ink | `--color-ink-muted` | `#6E6459` | `#AAABB5` | the accessible subordinate tier, 4.5:1+ on every surface |
| Hairline | `--color-rule` | `#E4E1EA` | `#2E2C33` | separation; `ink-faint` is non-text only |
| Action green | `--color-green` | `#7FCC09` | `#7FCC09` | client action green; rings, borders, lattice — never behind white text |
| Ink-bearing green fill | `--color-chrome` / `--color-on-chrome` | `#4E7906` / `#FFFFFF` | `#7FCC09` / `#121013` | the only green allowed to carry button text, and its partner |
| Increase / gain | `--color-up` | `#B45309` | `#FF9A2C` | **orange, never green.** Light value stepped from the client's `#FF9A2C` for 5.05:1 |
| Decrease / loss | `--color-down` | `#0667B0` | `#4EA1E8` | **blue, never red.** Stepped from the client's `#0088E0` (3.75:1 on white fails) |
| Selection fill | `--color-pick` / `--color-on-pick` | `#EFC96A` / `#482720` | `#F5B73C` / `#121013` | gold fill with a **dark** ink in both themes: 8.34 light, 10.57 dark. Not `ink-strong`: that is white in the dark theme, and white on amber measures 1.75:1. **Fill only** — as a boundary it is 1.59:1 on light `raised` (KI-9) |
| Selection boundary | `--color-pick-line` | `#7A5C10` | `#F5B73C` | the deep end of the same gold, for the 2px outline that says "this one is live": **6.24** on light `raised`, **8.46** on dark `raised`, against WCAG 1.4.11's 3:1. Aliases to `--color-pick` in dark because that value already cleared there. Four consumers, all measured: the calendar's `current` cell, the meter ladder's `aria-current` step, the guided step's selected card, and the step links. Known weakness: it sits 1.24:1 from `--color-goal-line` in luminance, so a goal cell and a current cell are told apart by hue and by the goal's red pennant, not by lightness — both also carry words, so D-12 holds |
| Skill Points identity | `--color-sp` | `#009FE1` | `#4FC3F7` | the client's cyan, exact; **fills, rules and tint only** |
| Skill Points text | `--color-sp-ink` | `#0E7490` | `#4FC3F7` | the client cyan measures 2.66-2.98 as text on light surfaces (research §3.4 already recorded the fail), so text uses the stepped value |
| Risk / rejected | `--color-risk` | `#800014` | `#FF6B7A` | reserved: training failure and rejection only |
| Turn chip anchor | `--color-anchor` | `#0B6FB8` | `#8FC4EE` | deliberately not `--color-down`; blue already means "went down" |
| Focus ring | `--color-ring` | `#4E7906` | `#7FCC09` | 3:1 non-text boundary in both themes; `--color-green` alone fails light at 1.88 (see §8). Measured against the surfaces it actually sits on: **5.17 / 4.88 / 4.61** on light `raised` / `panel` / `page`, **7.61 / 9.51 / 9.79** in dark |

Status badges (release status is the catalog's load-bearing signal) render through
`--color-green` / `--color-sp` / `--color-pick` and their ink partners rather than
literal hexes, so they follow the active theme instead of pinning one.

| ReleaseStatus | Treatment | Rule |
|---|---|---|
| `GlobalReleased` | `--color-green` capsule with `--color-on-chrome` ink (dark) or `--color-chrome` fill with white ink (light) | fill + text label always together; never color-only |
| `GlobalAnnounced` | `--color-sp-ink` text + hairline outline | Proposed mapping, no fill — cyan as a fill reads as selected. Text uses `sp-ink`, not `sp`: the client cyan cannot carry a label on light surfaces |
| `JapanOnly` | **unimplemented** — the catalog renders this status as `--color-ink-muted` label text today (`catalog/index.blade.php:43`), not as a badge | never color-only: the word says it (D-12). The treatment written here formerly said `--color-pick` **text** + hairline outline; gold text on a light surface is 1.59:1, so building it as specified would have shipped a text-contrast failure. Corrected during the KI-9 split rather than implemented. If a badge is ever wanted, it needs a fill + ink pair from the pair table above, not the pick hue as text |

Never use Tailwind default `green-500` etc. as brand tokens; the client's
action green is hue ~87°, and six degrees of drift breaks the resemblance
(research doc §1 "palette drift", its §3.3 inverted delta rule).

### 2.2 Typography

Owner ruling 2026-09-27: monospace numerals, clean legible sans UI text, no
external fonts (offline constraint + C-8 dependency gate).

- UI text: `--font-sans: ui-sans-serif, system-ui, sans-serif`. The current
  `--font-sans: 'Instrument Sans', ...` token in `app.css` is a framework
  default pointing at a font the app never bundles or loads; replacing its
  value with the system stack is a pending one-line code change.
- Numerals, stats, dates, ids: `--font-mono: ui-monospace, 'Cascadia Mono',
  'Segoe UI Mono', Consolas, monospace` with
  `font-variant-numeric: tabular-nums` on every stat cell and timestamp.
  Raceboard amber display numerals are the motif (research doc §3.7).
- Scale (Proposed, standard Tailwind sizes, reason = desk density):
  body `text-sm`, table cells `text-sm`, section headers `text-lg font-600`,
  page title `text-2xl font-600`, one `h1` per page.
- The research package's rounded display-font proposal ("M PLUS Rounded 1s" /
  self-hosted Nunito) stays parked: it needs a C-8 dependency approval and
  the owner's ruling asks for a neutral sans.

### 2.3 Spacing and layout

- Desktop-first: primary target 1280px+, layout max-width `max-w-6xl`
  for tables (Proposed; current views use `max-w-5xl`). Mobile must not
  break (R-03 floor: no overflow, tap targets via labeled controls) but is
  not a design driver (PRD §2, PRODUCT Operating Context).
- Scale: Tailwind default 0.25rem increments, no arbitrary values
  (tailwindcss-development rule). Table rows `py-1.5 px-3`; forms
  `space-y-3`; cards `p-4`; section gaps `mt-8`.
- Structure per surface: one h1, one primary action per screen, filters as a
  single inline form row, pagination under tables.
- Radius `rounded-md` consistently; borders `1px --color-rule`.
- Shadows: none. Separation comes from the rule edge and the raised/sunken step
  (research doc §3.7 consequence 2).
- **Amended 2026-09-27: three franchise motifs are material, not elevation, and are
  allowed.** An earlier reading of this section treated them as generic decoration and
  banned them along with drop shadows. That was wrong on the evidence: each is measured
  from a specific client frame and each carries meaning rather than gloss. They are
  implemented as named `@utility` rules in `app.css`, not as arbitrary values.

  | Motif | Spec | Where | Why it is not decoration |
  |---|---|---|---|
  | Enamel sheen | research §6.1 | primary action button only | The client's buttons are glossy enamel, and the sheen marks the one confirm control per screen. Hard-edged single split at about 34%, never a feathered ramp (research D-112). |
  | Argyle lattice bleed | research §6.3 | capsule headers | The client's most repeated element, measured across frames `234521`, `230755`, `232345`. It identifies a header as chrome rather than as data. |
  | Torn-page turn chip | research §6.6 | the Turn widget in the resource strip | Frame `194819`. Tab strip plus two punch holes. It is the run's timeline anchor, and in a run list it is the row's identity, because turn depth is the first thing a Trainer reads. |

  Two limits survive the amendment. A capsule always carries a word, so its fill stays on
  the accessible chrome step and never the client's bright lime, where white measures
  1.99:1 (research §6.3, D-3). And none of the three is a shadow: the rule above is
  untouched.

## 3. Component inventory (actual committed Blade)

All views render through `resources/views/components/layout.blade.php`
(`x-layout`: nav, flash, slot). States column: yes / gap = required by C-7
but not yet implemented. **Updated 2026-09-27:** four content pages
(`catalog/index`, `catalog/show`, `review/index`, `runs/create`) still hold
skeleton `zinc-*` utilities; their loading/error states remain gaps.
Token-migrated components (`stat-band`, `resource-strip`, `guided-step`,
`race-calendar`, `design-preview`, `grade-point-meter`) render from
`@theme static` and have no skeleton palette classes.

| Component | Route | Purpose | Empty | Loading | Error |
|---|---|---|---|---|---|
| Catalog filter form | `catalog.index` | status + normalized search | n/a | gap (server-rendered) | invalid status silently ignored, gap: should show |
| Catalog list rows | `catalog.index` | name, name_ja, status badge, aliases count | yes | gap | gap |
| Pagination | index, runs, review | page nav | n/a | n/a | n/a |
| Catalog detail dl | `catalog.show` | identity + dates + manual flag | n/a | gap | 404 page |
| Aliases chips / provenance list | `catalog.show` | aliases, last sources with URL + fetched timestamp in `display_timezone` | "No aliases yet" / "No fetched sources" | gap | gap |
| JP-only notice | `catalog.show` | "Not yet released on Global" banner | conditional | n/a | n/a |
| Run cards list | `runs.index` | run summary + status + date | yes | gap | gap |
| Run create form | `runs.create` | umamusume select, scenario, status, notes | n/a | n/a | validation errors |
| Turn table | `runs.show` | five stats + SP + condition, tabular numerals | "No turns logged yet" | gap | gap |
| Turn add form | `runs.show` | 0..2000-target inputs (currently 1200, see §5) | n/a | n/a | $errors list |
| Skill state groups | `runs.show` | Suggested / Acquired / Skipped lists | "None." per group | n/a | n/a |
| Export links | `runs.show` | csv / json download | n/a | n/a | gap |
| Candidate cards | `review.index` | proposed name, tier, source, suggestion | yes | gap | gap |
| Resolve form | `review.index` | confirm / alias / reject per candidate | n/a | n/a | @error per card |
| Stat band | `runs.show` | grade badges, values, cap markers | "No turns" | gap | gap |
| Resource strip | `runs.show` | turn, energy, fans, scenario widgets | n/a | gap | gap |
| Guided step | `runs.show` | discipline pick, preview, confirm | n/a | n/a | validation errors |
| Race calendar | `runs.show` | scenario timeline, gates, goal pennants | n/a | gap | gap |
| Grade point meter | `runs.show` | Trackblazer objectives, progress | n/a | gap | gap |

## 4. Surface specifications

### 4.1 Catalog index `/umamusume` (first surface, owner priority)

The desk's front page and the most complex grid: filters, release-status
badges, provenance density, normalized search. This surface decides the table
pattern every other screen copies.

- Table, not card grid: one row per Umamusume; columns: name, name_ja
  (muted), release badge, aliases count, last-fetched date (mono).
- Filter row: search input (normalized, placeholder says "Name (normalized)"
  so Trainers learn the match-key behavior), status select, submit.
- Empty state names the remedy (seed or `uma:fetch`), already shipped.
- Loading: static server render; the fetch-in-flight indicator belongs to the
  refresh affordance gap (§7), not to this page's initial paint.

### 4.2 Catalog detail `/umamusume/{slug}`

- Definition list: status, JP/Global debut, Trainer-edited flag.
- Provenance list is visible by default: URL, source key, fetched timestamp
  rendered in `config('uma.display_timezone')` (PRODUCT principle 2).
- JapanOnly renders the amber notice; dateless rows show "Unknown", never a
  sentinel (CLAUDE.md data rules).

### 4.3 Runs index and create `/training-runs`

- Index: compact run rows (Umamusume, scenario, status, created date).
- Create: single-column form, `max-w-lg`; selects over free text where an
  enum exists.
- Ancestor selection, when it ships, is **Legacy Select**: a pre-run step only
  (never reachable from a turn, D-260), labeled Legacy/Ancestor in copy while
  the schema keeps `inheritance_parent_*` identifiers (D-267). Its full
  six-slot client state is not persistable in today's two FK columns; the
  screen must not imply it is until a schema proposal lands (D-268).

### 4.4 Run detail `/training-runs/{run}`

- Turn table: mono numerals, tabular-nums, newest turn last, stable row
  heights; no sparklines until a derived number is explainable from turns
  (CLAUDE.md planner rule 5).
- Stat bars (when added): render the 1200 halved-gains soft cap and the
  scenario ceiling as visually distinct markers with a disclosure line for
  untracked components (ADR-0002 UI requirement, research doc §6.21).
- Skill column grouped Suggested / Acquired / Skipped; the group label is the
  plan-vs-actual story (US-4).
- Export links in the header row, not hidden in menus.

### 4.5 Review queue `/review`

- Queue density: newest candidates first; tier badge (Fuzzy/None) and source
  key always visible; resolve controls inline per card (no detail page).
- Resolved rows leave the view (Pending filter); history is the row status,
  not a separate screen.

### 4.6 API (no visual surface)

`/api/v1/*` is read-only JSON; shapes and error envelope are fixed in
`ARCHITECTURE.md` §4. No design decisions belong here.

## 5. Data display rules with open implementation gaps

- Validation bound: shipped code accepts stats 0..1200
  (`StoreTurnEntryRequest`); ADR-0002 (accepted) rules the bound 0..2000 and
  requires the UI to keep the 1200 soft cap and the scenario ceiling
  distinct. Until that lands, do not display any ceiling claim beyond what
  the form enforces.
- Timestamps: always `display_timezone`, always with zone suffix; date-only
  values never render a time (PRD US-7).

## 6. Lore-sensitive design rules (hard gate)

- Characters are Umamusume, a humanoid race. UI copy, labels, alt text, and
  iconography never use equine vocabulary or framing (CONSTRAINTS C-4; a
  confirmed violation fails the change).
- Visual motifs that are allowed (owner ruling): stopwatches, bar charts,
  tactical grids, target reticles, athletic track lines, abstract geometric
  data-visualization shapes.
- Visual motifs that are forbidden: saddles, whips, reins, hay, horseshoes,
  🏇, any equestrian or animal iconography, and racing-trope decoration that
  frames the characters as animals.
- Inheritance terminology (owner ruling 2026-09-27, matching
  `docs/UMAMUSUME_REFERENCE.md` §1.5): the system is **Inspiration**, the two
  ancestors picked for a run are **Legacies**, inheritable traits are
  **Sparks**, and the pre-run pick screen/widget is labeled **Legacy Select**
  (rules D-260 through D-268, `docs/design-research/CONSTRAINTS.md` §10q;
  anatomy in that file's DESIGN.md §6.27). Never "sire" / "dam", never
  family-tree-with-animal-motifs (PRD §6.3). Two caveats carried with the
  ruling: shipped identifiers stay `inheritance_parent_a_id` /
  `inheritanceParentA()` while copy uses Legacy (D-267, identifier rename is
  schema-phase work), and full Legacy Select state exceeds today's two-id
  columns; do not imply the six-slot payload persists until the ADR-0003
  pattern proposal lands (D-268).
- UI labels follow the Global client wording in
  `docs/UMAMUSUME_REFERENCE.md` Section 6 and §1.5: Speed / Stamina / Power / Guts /
  Wit; Trainee Umamusume; Veteran Umamusume; Scout (not gacha); Uncap;
  Carats; Front Runner / Pace Chaser / Late Surger / End Closer. Fan-English
  strings from exports are never UI copy.
- The five mood tier labels are no longer in this category. `GREAT` / `GOOD` /
  `NORMAL` / `BAD` / `AWFUL` are measured client strings (research doc §6.17, D-203
  withdrawn), and a mood pill renders the word, its colour and its directional arrow
  (D-259). Still blocked: the outing labels and any other term the corpus has not
  captured.
- Naming: the app is "Trainer Desk"; it does not adopt the game's trademarks
  into the product name.

## 7. Offline and low-bandwidth constraints

- No external font loading, no CDNs, no analytics. All assets local;
  system font stacks only (§2.2).
- Known violation: `resources/views/welcome.blade.php` (framework default)
  links fonts.bunny.net. Either remove the link or replace the page; logged
  as a one-line fix, not yet applied.
- The fetch engine is optional connectivity: with zero network the catalog,
  runs, review, and export all work from SQLite (PRODUCT principle, PRD
  NFR-2). Catalog data shows last-fetched timestamps so staleness is visible
  without a refresh (research-doc staleness-flag discipline applied to UI).
- Pending affordance (not built): a "fetch in flight" indicator for
  queue-driven refreshes; when it ships it is the loading state for C-7 on
  catalog surfaces.

## 8. Motion

Dial MOTION 1: hover and focus states only, `transition-colors` on
interactive elements, `focus-visible` rings using `--color-ring`
(2px outline, 2px offset). No page animations; a desk does not move (R-19).

The ring is its own token rather than `--color-green`, which this section used to specify.
That prescription predates the light-first flip: `#7FCC09` measures 9.51:1 on the dark panel
but only **1.88:1 on the light panel and 1.99:1 on white**, against WCAG 1.4.11's 3:1 floor for
non-text boundaries. No single green clears both themes — the deep `--color-chrome` passes the
light surfaces at 4.88 but drops to 2.93 on the dark raised cell — so the ring splits per theme
exactly as `chrome`/`on-chrome` do, and components stay theme-agnostic (D-101, D-258's rule that
a contrast rule must name its second colour).

## 9. Where tokens live

- `resources/css/app.css` `@theme static` block: single source of truth.
  There is no `tailwind.config.js` and none may be created (Tailwind v4
  CSS-first; ARCHITECTURE §1). **`static` is load-bearing:** without it Tailwind
  prunes any custom property no utility class references yet, an undefined
  `var()` silently inherits instead of failing, and a contrast check then reports
  a pass on a token that is not in the stylesheet (research `CONSTRAINTS.md` D-288).
- `docs/design-research/DESIGN.md`: measured ramps, contrast tables,
  cap-stack rendering spec (§6.21), and the light theme that is now the app default.
- `docs/design-research/CONSTRAINTS.md`: the research-phase design rules
  (D-numbers) this file cites (D-12, D-20, D-101).
- Proposed values in this file (scale numbers, `max-w-6xl`, the mono stack) need
  owner sign-off before they become tokens. The Announced-cyan proposal is now a
  shipped pair — `--color-sp` for identity surfaces, `--color-sp-ink` for text.
