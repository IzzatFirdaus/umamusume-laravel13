# DESIGN.md: Trainer Desk design system

Product name **Trainer Desk**, owner decision 2026-09-27 (closes PRD OQ-1).
This file is the design contract for the app's visual system. Owner rulings
recorded here: dark-first theme, tactical-athletic identity, catalog-first
design priority, and the lore-sensitive iconography boundary.

Design Read: personal analyst desk for one Trainer of Umamusume Pretty Derby,
tactical-athletic language, dial ENERGY 1 / RHYTHM 1 / MOTION 1.
(Reason: the only viewer is the owner mid-planning; decoration costs reading
speed and buys nothing. R-31.)

Relationship to `docs/design-research/DESIGN.md`: that document is the
screenshot-anchored research package (measured color ramps, contrast tables,
cap-stack spec §6.21). This file does not re-derive it. Color values below are
the research package's **measured dark anchors** (its §3.7, taken from the
game's own raceboard screenshot), promoted to the app default by owner
decision. Where a value is genuinely new here, it is marked `Proposed`.

## 1. Design principles

1. Data first, chrome last. Every pixel around a number earns its space by
   making the number faster to read (PRD NFR-3, Pre-Mortem: data-dense tool).
2. Colour is semantic only. A coloured element must change what the Trainer
   knows; otherwise it is neutral (research doc P1, antislop R-01/R-16).
3. Dark by default, measured not inverted. Charcoal field, high-contrast
   ink, amber display numerals, taken from the raceboard anchors rather than
   a generic dark skin (owner ruling 2026-09-27, research doc §3.7).
4. The desk is serious: no pastel anime gradients, no soft drop shadows, no
   marketing layout. It reads like a sports-science or esports analytics
   tool, never like a gacha wiki (owner ruling; antislop Part 1).
5. Every state is designed: empty, loading, error, data (CONSTRAINTS C-7,
   antislop R-27).
6. Lore compliance is a design constraint, not a copy polish pass (see §6;
   CONSTRAINTS C-4 is a hard gate).

## 2. Visual identity

### 2.1 Color system

Tokens live in the `@theme` block of `resources/css/app.css`
(Tailwind v4 CSS-first). Source of truth for full ramps and contrast math:
`docs/design-research/DESIGN.md` §3. The dark theme below is the default;
light remains the research package's measured light system and can ship later
as `html[data-theme="light"]`, but per antislop R-34 an unshipped theme is
not half-shipped: until a second theme is verified, only dark renders.

Implementation status, stated honestly: the committed views still use the
skeleton's light zinc utilities (`bg-zinc-50`, `text-zinc-900`). Migrating the
layout component and views onto the tokens below is pending implementation
work; until it lands, this section is the contract, not the current render.

| Role | Token (dark default) | Value | Why |
|---|---|---|---|
| Page field | `--color-page` | `#0D0C0F` | raceboard surround, measured |
| Panel surface | `--color-panel` | `#121013` | board charcoal, measured |
| Raised surface | `--color-raised` | `#24262A` | unlit LED cell, measured |
| Primary ink | `--color-ink` | `#ECEAF2` | 15.88:1 on panel, AAA |
| Heading/value ink | `--color-ink-strong` | `#FFFFFF` | display numerals |
| Muted ink | `--color-ink-muted` | `#AAABB5` | board label text, 8.30:1 |
| Hairline | `--color-rule` | `#2E2C33` | separation (no shadows on dark) |
| Action / released | `--color-green` | `#7FCC09` | client action green; 9.51:1 as fill with near-black ink |
| Selection / JP-only | `--color-pick` | `#F5B73C` | display amber, measured |
| Announced | `--color-sp` | `#4FC3F7` | Proposed mapping for `GlobalAnnounced` (Skill Points cyan) |
| Risk / rejected | `--color-risk` | `#FF6B7A` | reserved for failure and rejection only |

Status badges (release status is the catalog's load-bearing signal):

| ReleaseStatus | Treatment | Rule |
|---|---|---|
| `GlobalReleased` | green `#7FCC09` capsule, ink `#121013` | fill + text label always together |
| `GlobalAnnounced` | cyan `#4FC3F7` text + hairline outline | Proposed, no fill |
| `JapanOnly` | amber `#F5B73C` text + hairline outline | never color-only: the word says it (D-12) |

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
but not yet implemented.

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
- Inheritance UI: "Parent A" / "Parent B" with abstract lineage nodes; never
  family-tree-with-animal-motifs, never "sire" / "dam" (PRD §6.3).
- UI labels follow the Global client wording in
  `docs/UMAMUSUME_REFERENCE.md` Section 6: Speed / Stamina / Power / Guts /
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
interactive elements, `focus-visible` rings using `--color-green` glow token
`--shadow-glow`. No page animations; a desk does not move (R-19).

## 9. Where tokens live

- `resources/css/app.css` `@theme` block: single source of truth.
  There is no `tailwind.config.js` and none may be created (Tailwind v4
  CSS-first; ARCHITECTURE §1).
- `docs/design-research/DESIGN.md`: measured ramps, contrast tables,
  cap-stack rendering spec (§6.21), and the light theme this default replaced.
- `docs/design-research/CONSTRAINTS.md`: the research-phase design rules
  (D-numbers) this file cites (D-12, D-20, D-101).
- Proposed values in this file (Announced-cyan, scale numbers, `max-w-6xl`,
  the mono stack) need owner sign-off before they become tokens.
