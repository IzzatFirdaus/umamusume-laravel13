# DESIGN.md: Trainer Desk design system

Product name **Trainer Desk**, owner decision 2026-09-27 (closes PRD OQ-1).
This file is the design contract for the app's visual system. Owner rulings
recorded here: **light-first theme** (revised same day, see §2.1), tactical-athletic
identity, catalog-first design priority, and the lore-sensitive iconography boundary.

**Audited against the tree 2026-10-04.** Every claim below now carries a status word,
because this file had drifted past the implementation in both directions: it still
described a migration that had already happened, and it described browser
verification that this host cannot currently run.

| Status | Meaning |
|---|---|
| **Existing** | Shipped in the tree, read from source on 2026-10-04. |
| **Established Standard** | Binding decision, owned by an owner ruling, ADR, or `GOVERNANCE.md`. Not optional, not this file's to change. |
| **Intended** | Shipped, but the build diverges from the contract written here. Named as a gap, not silently normalised. |
| **Recommended** | A judgement from the audit. Ships nothing until an owner signs it off. |
| **Unknown** | The claim cannot be checked from this tree. Not asserted either way. |

Screen-level behaviour belongs to `SCREEN_SPEC.md`; this file owns the visual
system and points at it rather than restating it.

Design Read: personal analyst desk for one Trainer of Umamusume Pretty Derby,
tactical-athletic language, dial ENERGY 1 / RHYTHM 1 / MOTION 1.
(Reason: the only viewer is the owner mid-planning; decoration costs reading
speed and buys nothing. R-31.)

Relationship to `docs/research-scratch/DESIGN-CORPUS.md` section "DESIGN.md" (the
consolidated research package, formerly `docs/design-research/DESIGN.md`): that document is the
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
`docs/research-scratch/DESIGN-CORPUS.md` section "DESIGN.md" §3. **Light is the base palette** (owner ruling
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

Implementation status, corrected 2026-10-04. **Existing:** the token migration this
paragraph used to call pending is complete. `resources/views` contains no `zinc-*`
utility and no `dark:` fork anywhere — the only `zinc` strings left are inside
explanatory comments. Every page named here as unmigrated (`catalog/index`,
`catalog/show`, `review/index`, `runs/create`) is token-only today, and
`tests/Feature/DesignTokensTest.php` asserts that for the shell pages
("renders each legacy shell page from tokens, with no theme fork and no skeleton
palette class", 11 passing on 2026-10-04). `design-preview` is gone from the tree
entirely and is struck from every inventory below; nothing renders it.

**Unknown, and it undercuts several "measured" claims elsewhere in this file.** The
browser contrast gate that is supposed to prove G-18 is `markTestIncomplete` behind a
`class_exists(Browser::class)` guard, and this host has neither `pestphp/pest-plugin-browser`
nor Playwright installed, so its two tests report *skipped*, not passed. Corpus figures
in the comments below are transcribed from research-phase measurements whose evidence
files are absent from the tree (`docs/design-research/verification/slice-6-2026-09-28.md`,
cited by `app.css`, and `FRONTEND-SPEC-DIVERGENCE.md`, cited by the test, are both
missing; that directory holds four PNGs and nothing else). Treat every ratio in this
file as **documented, not re-verified here**. Closing that is a gate decision (R-class),
not a copy fix.

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
| `JapanOnly` | **unimplemented** — the catalog renders this status as `--color-ink-muted` label text today (`catalog/index.blade.php:71`, inside the `text-ink-muted` meta row), not as a badge | never color-only: the word says it (D-12). The treatment written here formerly said `--color-pick` **text** + hairline outline; gold text on a light surface is 1.59:1, so building it as specified would have shipped a text-contrast failure. Corrected during the KI-9 split rather than implemented. If a badge is ever wanted, it needs a fill + ink pair from the pair table above, not the pick hue as text |

Never use Tailwind default `green-500` etc. as brand tokens; the client's
action green is hue ~87°, and six degrees of drift breaks the resemblance
(research doc §1 "palette drift", its §3.3 inverted delta rule).

**Existing: the table documents 20 of the 60 declared roles.** `@theme static`
carries **60** `--color-*` properties plus 2 font stacks, and
`tests/Feature/DesignTokensTest.php` pins that 60 as a tripwire ("counts every colour
token the static theme declares") — adding a role moves the number in the test and in
this file together. The 40 roles with no row above are not undocumented decisions, they
are token families whose contract lives in their own `app.css` comment block:

| Family | Roles | Why it is not in the table above |
|---|---|---|
| Step scale | `sunken`, `idle`, `disabled` | surface steps, never text-bearing on their own |
| Ink step | `ink-faint` | non-text only: borders, ticks, disabled glyphs. Measured as body text it fails, which is why `ink-muted` is the subordinate text tier |
| Green family | `on-green`, `green-deep`, `green-tint`, `green-line` | the fill/ink pair and the tint/line pair that `chrome` and `green` are measured against |
| Goal pair | `goal`, `goal-line` | two colours on one cell (red pennant, warm outline), so neither can borrow |
| Mood | 5 tier fills + `on-mood` | chrome, deliberately *not* theme-overridden: the client's pill is one pink in both themes |
| Stat bands | 6 `tint-*` + 6 `line-*` | per-stat whisper tints; stats carry no saturated identity hue |
| Grade badges | 9 `grade-*` | 9 letters × 2 themes, white letters banned on the light fills |
| Identity | `rank`, `lattice`, `anchor`, `scrim` | one-off roles; `scrim` is the only overlay treatment |

**Intended:** the test's own comment says a new role is added "here and in DESIGN.md
§3.1 together", but this file has no §3.1 — the token table is §2.1. The citation
inside the test is stale, not the table's location.

### 2.2 Typography

Owner ruling 2026-09-27: monospace numerals, clean legible sans UI text, no
external fonts (offline constraint + C-8 dependency gate).

- UI text: `--font-sans: ui-sans-serif, system-ui, sans-serif`.
  **Existing (closed 2026-10-04):** the pending one-line change this bullet used to
  carry has landed. `app.css` declares the system stack and its comment records the
  close, naming the reason: the skeleton's `'Instrument Sans'` named a font the app
  neither bundles nor loads, which also broke the offline requirement (NFR-1). Nothing
  loads an external font anywhere in the tree.
- `app.css`'s own header comment still says "Dark is the shipped default" twelve lines
  above a block that correctly declares light. **Intended:** that comment is stale
  against §2.1's light-first ruling and is a source-file fix, out of scope here.
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

- Desktop-first: primary target 1280px+. **Existing:** `max-w-6xl` appears nowhere in
  the tree, so the proposal below it is superseded by what shipped. The measured widths
  are `max-w-5xl` for the shell (nav and `main`, `components/layout.blade.php`), `max-w-3xl`
  for the in-page forms and long copy blocks on the run screen, and `max-w-lg` for the
  single-column create form. Mobile must not break (R-03 floor: no overflow, tap targets
  via labeled controls) but is not a design driver (PRD §2, PRODUCT Operating Context).
- **Recommended:** retire the `max-w-6xl` line rather than keep a proposed token that
  no surface uses. Ratified widths, in one place: 5xl shell, 3xl form, lg narrow form.
- **768px is the supported minimum (amended 2026-09-29, KI-25), and the floor above
  is narrower than it read.** "No overflow" was measured and is false below 768px for
  exactly two surfaces: the race calendar and the turn log are wide tables whose content
  is 476px at the narrowest viewport tested. Neither is squeezed to fit and neither is
  hidden — both scroll horizontally inside a focusable region (`overflow-x-auto` with
  `role="region"`, `tabindex="0"` and an `aria-label`), so the clipped columns are
  reachable by arrow keys and not by trackpad alone. Below 768px that is the contract for
  these two regions: usable and scrolling, deliberately not reflowed. Everything else on
  those screens still reflows as before.
- **Status of the line above (2026-09-29, R85):** the 768px minimum is a **proposal, not a ratified
  contract**, and it rests on an attribute read rather than a browser measurement. It was written on the
  strength of closing KI-25; KI-25 is re-opened, and the measurement that would justify the number —
  arrow-key traversal, the document `scrollWidth` after scoping, the other columns and regions — has not
  been taken. It also contradicts `docs/research-scratch/DESIGN-CORPUS.md` section "CONSTRAINTS.md" D-40's standing sentence that no
  mobile-first compromise is accepted in exchange for desktop density; that reconciliation is a ruling
  (R82) and is deliberately **not** made here. Read this bullet as held, not landed.
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
(`x-layout`: nav, flash, slot), which is the shell and is not one of the count below.
**Existing:** 11 Blade components exist, and 26 Vue single-file components sit under
`resources/js/components/`. The Blade count is small because the port has been retiring it:
`character-portrait` and `support-thumb` were added on 2026-10-05 for the artwork slots and
deleted the same day, because the three Blade screens that were their only call sites were
retired by the A1 to A3 ports and the slots moved to `ArtworkSlot.vue` with them.

Of the 11, 2 have no call site at all, and `energy-gauge` is reachable only through
one of those 2. All of them are token-only — the skeleton-palette migration is finished,
so no row below is blocked on it. `design-preview` is removed from the tree and struck.

Two columns carry the audit's findings. **Reachable** is measured by call site, not by
intent. **Loading** is uniformly `gap`: the app is server-rendered end to end, so C-7's
loading state has no surface to live on, and the "fetch in flight" affordance §7 defers
is the only thing that would ever create one. That is a deliberate absence, not an
oversight, and it is cheaper than the indicator would be.

| Component | Route | Purpose | Empty | Error | Reachable |
|---|---|---|---|---|---|
| Catalog filter form | `catalog.index` | status + search + unconfirmed opt-in | n/a | n/a | yes |
| Catalog roster rows | `catalog.index` | trainee header + her costume forms | dashed panel naming the remedy | 404 page | yes |
| Catalog detail `dl` | `catalog.show` | identity, profile fields, dates | "unpublished" wording | 404 page | yes |
| Trainee artwork slot | `catalog.index`, `catalog.show` | `ArtworkSlot`: a mirrored portrait streamed from `artwork.show`, geometry per §4.7 | renders nothing; the row stays text-only | the stream route 404s if the file goes missing between page and request | yes |
| Support-card artwork slot | `support-cards.index`, `support-cards.show` | `ArtworkSlot`: a mirrored thumbnail, same stream and rules | renders nothing; the row stays text-only | the stream route 404s | yes |
| Costume form tabs | `catalog.show` | one panel per form, CSS/native-radio tabs | single form draws no strip | 404 page | yes |
| Aptitude grid | `catalog.show` | 10 letters, letter **and** word (D-12) | "unpublished", never half a grid | 404 page | yes |
| Aliases / provenance lists | `catalog.show` | aliases, source URL + fetched stamp in `display_timezone` | "No aliases yet" | 404 page | yes |
| Run rows | `runs.index` | trainee, scenario, status, date (mono) | dashed panel, "No runs yet" | n/a | yes |
| Run create form | `runs.create` | trainee combobox, scenario, status, legacy slots, notes | n/a | `@error` per field | yes |
| Turn table | `runs.show` | five stats + SP + condition, tabular numerals | "No turns logged yet" | gap | yes |
| Guided step | `runs.show` | discipline pick, preview, confirm | n/a | one error list for field + request errors | yes |
| Stat band | `runs.show` | grade badges, values, cap markers | "No turns" | gap | yes |
| Resource strip | `runs.show` | turn, energy, fans, scenario widgets | n/a | gap | yes |
| Race calendar | `runs.show` | scenario timeline, gates, goal pennants | n/a | gap | yes |
| Grade point meter | `runs.show` | Trackblazer objectives, progress | n/a | gap | yes |
| Race panel / fatigue chip | `runs.show` | race entry + declared fatigue | n/a | `@error` | yes |
| Shop panel | `runs.show` | purchase form + rotation countdown | n/a | `@error` | yes |
| Deck panel | `runs.show` | equip one open picker, closed rows post hidden inputs | n/a | `@error` per row | yes |
| Team panels | `runs.show` | team race, rank gauge, spirit burst roster, epithet checklist | varies | `@error` | yes |
| Skill state groups | `runs.show` | Suggested / Acquired / Skipped lists | "None." per group | n/a | yes |
| Skill rows form | `runs.show` | per-row skill id, status, turn acquired | n/a | `@error` per field | yes |
| Hand-correction form | `runs.show` | `<details>` escape hatch, every field at once | n/a | `$errors` list | yes |
| Delete run | `runs.show` | `<details>` disclosure wrapping the destructive POST | n/a | n/a | yes |
| Export links | `runs.show` | csv / json download | n/a | gap | yes |
| Skill filter form | `skills.index` | search, type, unique-only, all `h-11` | invitation shown only with no query | `@error('type')` | yes |
| Skill rows | `skills.index` | name, name_ja, Unique mark, type, SP cost | two distinct no-data states | n/a | yes |
| Candidate cards | `review.index` | proposed name, tier, source, suggestion | "Nothing pending" + command | stacked `role="alert"` list | yes |
| Resolve form | `review.index` | confirm / alias / reject per candidate | n/a | same stacked list | yes |
| Pagination | all index routes | two responsive blocks, token-only | n/a | n/a | yes |
| 404 | error page | branded recovery, named routes out | n/a | n/a | yes |

**Intended: three components are built and unreachable.** `deck-editor`,
`energy-gauge`, `run-header`. Two of them matter beyond dead weight, because each
duplicates a pattern the tree renders elsewhere:

- `run-header` → `energy-gauge` is a ten-segment energy gauge. `resource-strip` and
  `guided-step` ship the run's energy readout instead. Two energy representations, and the
  unreachable one is the one whose hue treatment §2.2 rules *not* material: a stat colour on
  a track gradient reads as a threshold the client never states.
- `deck-editor` is the deck-slot read view. Mounting it means per-card level and limit-break
  state, which PRD §6.9 and US-12 keep out, so it stays gated on that decision.

**Adopted since this section was written.** `capsule-header` and `grade-badge` had call
sites that re-implemented them: eight panels hand-copied the lattice-bleed capsule div and
`stat-band` carried its own copy of the nine grade fills beside its own badge span. Both are
mounted now, so the franchise motif §2.3 rules material reaches the surfaces it was ruled for
and the grade letters §2.1 routes through `ink-strong` have one implementation instead of two.
`FrontendComponentLibraryTest` fails if either visual is defined a second time.

**Recommended:** for each of the three, either adopt the component at its call site or
delete it. A component that exists only as an unimplemented decision is the cheapest
thing in the tree to keep and the most expensive to leave, because a future author will
read it as the pattern.


## 4. Surface specifications

### 4.1 Catalog index `/umamusume` (first surface, owner priority)

**Existing, and it is not what this section used to describe.** The old contract ruled
"table, not card grid" with columns for name, `name_ja`, release badge, aliases count and
last-fetched date. The shipped page is a **two-level roster tree**: one card per trainee
(`<li>` with a bordered `bg-raised` surface) whose header carries her name, her `name_ja`,
her max rarity chip, her form count and her release status, and whose body is a
`divide-y` list of her costume forms, each with title, rarity chip, debut marker,
confirmation warning and Global release date.

Why the tree and not the table is the right call, recorded because the old ruling got
this backwards: search matches **forms and aliases**, not only trainee names, so a table
keyed on the trainee row would hide the row a search actually matched. The tree keeps the
match visible at the level it occurred.

- Filter row: one `GET` form, one inline row, all controls `h-11` — search, release
  status, an unconfirmed-cards opt-in checkbox, submit. It is a reload, not live typing,
  and the view says so in its own comment rather than implying otherwise.
- The trainee badge reads max rarity over the cards the filter let through, so hiding an
  unconfirmed card moves the badge with it. Absence is worded ("no forms recorded"),
  never a bare `0`, because `0` reads as a count.
- Empty state names the remedy (seed or `uma:fetch`) in a dashed panel, already shipped.
- Loading: static server render; the fetch-in-flight indicator belongs to the
  refresh affordance gap (§7), not to this page's initial paint.

### 4.2 Catalog detail `/umamusume/{slug}`

~~Definition list: status, JP/Global debut, Trainer-edited flag.~~
~~Provenance list is visible by default: URL, source key, fetched timestamp rendered in
`config('uma.display_timezone')` (PRODUCT principle 2).~~
~~JapanOnly renders the amber notice; dateless rows show "Unknown", never a sentinel (CLAUDE.md data
rules).~~

**Withdrawn 2026-09-29, and the page is not at fault.** `catalog/show.blade.php` implements all three
lines faithfully. The specification under-designed the surface: it names three items for a page a Trainer
opens to decide *which trainee to run*, and it converted `CLAUDE.md:23`'s storage rule — "no sentinel
**dates** for 'unreleased' (use nullable date + `release_status`)" — into display copy, which is where the
word "Unknown" on this page comes from. No other surface in the tool uses it. Its "amber notice" is
likewise unimplementable: the token set has no caution chrome and `pick` measures 1.60:1 on the raised
surface, so the notice is copy over `ink-faint` (3.26:1 light / 4.21:1 dark) as the view's own comment at
`:33-38` records.

**Ported 2026-10-05.** `SCR-CAT-002` now renders through `resources/js/pages/Catalog/Show.vue`; the
`catalog/show.blade.php` view and its `form-detail` partial are deleted. The withdrawal above stands as
written for the 0.1.0 view it describes, and the eight-section workspace below is the ported page's shape.

**The trainee page is a workspace for that trainee.** Eight sections, in this order:

1. **Identity** — name, Japanese name, release status, JP debut, Global debut, Trainer-edited flag.
2. **Aptitudes** — Track (Turf, Dirt), Distance (Sprint, Mile, Medium, Long), Style (Front Runner, Pace
   Chaser, Late Surger, End Closer), in `ADR-0004`'s element order, each cell letter **and** word (D-12).
3. **Skills** — Her innate · Her unique · Her awakening ladder.
4. **Costume forms** — bracketed title, rarity, debut marker, Global release date.
5. **Goal races** — turn, race, tier, distance, required placement, in goal order.
6. **Her runs** — her run rows and the page's **primary action**, "New run for <name>".
7. **Aliases.**
8. **Provenance** — URL, source key, fetched timestamp in `config('uma.display_timezone')`
   (PRODUCT principle 2). Last, and quiet: it leads the page only when it is the page's content.

**Absence has exactly two forms on this page.** A value the record does not carry renders as `N/A` with a
`title` naming the kind of absence, per the reasoning already written into
`resources/views/components/resource-strip.blade.php` — never `Unknown`, never a dash, never zero. A
section the record cannot yet supply keeps its heading and says **"not yet recorded"**, which is the
pattern every run-screen panel already uses. The heading is the honest part: a missing section states "she
has none" where a heading with an empty body states "this tool has not recorded it", and on sections 3, 4
and 5 the second sentence is the true one today.

**A cell reading *absent* is a hard requirement, not an option** (D-220's rule, applied to a trainee
rather than to a scenario). **The reason:** the page has existed since Phase 1 and its specification has
never been revisited, so it under-specifies the one surface every other trainee-facing decision starts
from. Sections 3-5 have no stored data on this ref; the sections are not blocked on it, and neither is the
disclosure wording. Filed as KI-35.

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

### 4.7 Sourced artwork slots (`ADR-0021`; mirror and slots built)

**Status: the mirror exists and so do slots, on the ported screens.** `uma:fetch-art` and its
`artwork/` directory are built (`ADR-0021`, 2026-10-05). The read half landed the same day:
`ArtworkAssetController` streams a mirrored file over the loopback route `artwork.show`,
`ArtworkMirror::url()` hands the pages that route and returns `null` for a miss, and
`resources/js/components/ArtworkSlot.vue` is the single owner of the slot contract.

**The slots live in Vue, not Blade, and that is the port's doing rather than a preference.**
`x-character-portrait` and `x-support-thumb` were built for the three Blade screens the artwork
work originally targeted, then deleted the same day: the A1 to A3 ports retired
`catalog/show.blade.php` and both support-card Blade views, and those components had no other call
site (`frontend-development-plan.md` §5.1 step 8: delete at zero call sites). `ArtworkSlot.vue`
replaced them and is now used by all four slot-bearing screens. `grep -rn "<img" resources/views`
is zero again, and that is now the correct answer rather than a gap: the screens render `<img>` from
`.vue` files.

**Which screens carry a slot is still PRD OQ-6's remainder, and the shipped answer is partial.** The
slots are on the catalog index and detail, the support-card index and detail. They are **not** on the
run create screen: that screen's trainee picker is a native `<select>` whose `<option>` content model
is text, plus a client-rendered combobox listbox, so neither surface hosts a frame today. Skill rows
remain deferred because `skills` has no `icon` column (`ADR-0021` Verification).

**Which card supplies a trainee's portrait differs by screen, and that split is deliberate.** The
index row is identity, so it frames the debut form: the export derives `is_debut_form` by rule, and a
trainee who later gained an SSR costume is still the trainee she was at debut. The detail page is the
form in view, so it frames `activeCard`, which follows the costume tab and the `?form=` deep link. A
multi-form trainee therefore shows two different portraits across the two screens by design, and the
rarity badge on the index row keeps naming the top form on the same line as its debut portrait. Both
read `?? cards->first()` as their fallback, so a trainee whose rows carry no debut flag cannot make
the two screens disagree by accident. `tests/Feature/CatalogIndexPortraitTest.php` pins the split.

Every row that has no slot still renders text only: name, `name_ja`, rarity chip, counts. That
text-only rendering is what this section calls the fallback, and it is not a decorative gap waiting to
be filled — R-31 already rules that decoration costs reading speed and buys nothing, and §1's design
read is an analyst desk.

Which surfaces get a picture at all is **PRD OQ-6**, the owner's call, not this file's.
What this file owns is the behaviour once a surface is chosen, and five rules bind it:

- **Absence is a normal state, never an error state.** A file that was never mirrored, or
  that no longer exists upstream, renders the fallback. No broken frame, no grey box, no
  loader, no placeholder glyph. This is §4.2's absence discipline applied to an image
  rather than to a value: the mirror is partial by nature, and a broken visual would claim
  a   defect the tool does not have. `ArtworkSlot.vue` implements this by rendering nothing
  when the mirror misses: the guard wraps both of its branches rather than the `<img>`
  inside one, so an absent file leaves no frame and no anchor either.
- **The slot's geometry is decided once, at build time, and recorded here.** Either the box
  is reserved and a fallback paints inside it, or the element is omitted and the row
  reflows. Both are defensible; a per-page mixture is not, and neither is a size class that
  only exists in one view. **This section names the values, because the slots exist and
  leaving it silent would leave the rule unenforced.** No `width`/`height` attributes are
  emitted: the size class sets both edges, so the square is reserved before the file
  arrives and there is no layout shift for intrinsic dimensions to paper over. Recorded
  geometry, and where each is used:

  | Box | Surface | Click action | Source |
  |---|---|---|---|
  | `size-16` | trainee portrait on catalog detail; support thumb on support-card detail | no action | `design-2.0` §45a |
  | `size-12` | trainee portrait on catalog index; support thumb on support-card index | navigates to detail | `design-2.0` §45a |
  | `size-10` | trainee portrait on the catalog index's costume-form row | no action | `design-2.0` §45a |

  The three values are pinned as a TypeScript union on `ArtworkSlot.vue`'s `size` prop
  rather than passed as free strings, so Tailwind's scanner — which reads raw source text —
  is guaranteed to see all three even though the binding is dynamic. `size-10` is the one
  class that appears in a single screen, which the rule above forbids. It is recorded here
  as a deliberate exception rather than left as an accident: the costume-form row is a
  denser row nested under a trainee header that already carries a `size-12` frame, so a
  second `size-12` beside it would dominate its own row header. Reconciling that row with
  the header is a layout decision, not a geometry one.
- **`src` is a local path.** §7's rule (no CDNs, all assets local, the catalog works with
  zero network) means hotlinking a third-party host in rendered HTML is out even though the
  host is reachable: the page would then depend on someone else's uptime to render, and the
  offline promise would be false on exactly the surfaces that have a picture. The route is
  id-addressed rather than path-addressed: `artwork.show` validates `kind` against the
  asset host's two declared paths and casts `id` to an integer, so a request naming a
  traversal cannot reach storage.
- **Alt text is inside the C-4 boundary** (§6 governs its vocabulary, so the alt is the client
  display name and nothing else). No invented descriptor either, because the tool cannot see
  inside the file it is describing. Where the same name is already printed beside the image,
  the image is decorative in that position and takes `alt=""`, so a screen reader does not
  read the name twice.
- **A decorative image and an unnamed link are different defects, and the two must be chosen
  separately.** WCAG 2.2 AA 4.1.2 needs every focusable control to have an accessible name, and
  §42's label-in-name clause needs a clickable slot's accessible name to contain the printed label
  verbatim. So "the name prints beside the image" (which argues for `alt=""`) does **not** imply
  "the link needs no name", and §45a's *no action* click action does **not** imply "render a link to
  the page you are already on". `ArtworkSlot.vue` therefore takes the two decisions as separate props:
  `alt` for the image, and `href` plus `linkLabel` for the link, with an absent `href` rendering a
  bare frame and no anchor at all. That is what lets the three no-action slots (catalog detail,
  support-card detail, and the catalog index rows whose own name link is already the destination)
  carry a decorative `alt=""` without leaving a nameless focus target behind, and it is why the
  unnamed self-link the deleted Blade components produced does not exist to fail 4.1.2.

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
  (rules D-260 through D-268, `docs/research-scratch/DESIGN-CORPUS.md` section "CONSTRAINTS.md" §10q;
  anatomy in that master's "DESIGN.md" section §6.27). Never "sire" / "dam", never
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
- **Closed 2026-10-04.** This file carried a standing "known violation":
  `resources/views/welcome.blade.php` (framework default) linking
  `fonts.bunny.net`. That view does not exist in the tree, so the only CDN font
  reference is gone and the offline requirement holds by absence of the page rather
  than by a fix to it. Nothing else in `resources/` references an external origin.
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
exactly as `chrome`/`on-chrome` do, and components stay theme-agnostic (D-101, D-258's rule
that a contrast rule must name its second colour).

**Intended: the `transition-colors` half of this section ships nowhere.** The tree contains
zero `transition-*`, `duration-*` and `animate-*` utilities, so hover and focus changes are
instant on every surface. The contract's *intent* — "a desk does not move" — is honoured
more completely than the contract's own text, because nothing eases at all.

**What is shipped, from `app.css`'s base layer, is three treatments this section did not
previously name:**

| Treatment | Value | Why it is here |
|---|---|---|
| `:focus-visible` ring | `2px solid var(--color-ring)`, `2px` offset | one rule covers every focusable element, so a component cannot ship without a ring |
| `::selection` | `--color-pick` fill with `--color-on-pick` ink | the amber selection, never white on amber (G-47) |
| `caret-color` on input/textarea/select | `var(--color-chrome)` | the caret was a browser default belonging to no system, which is the cheapest tell of an assembled page |

**Intended: two controls specify `focus-visible:outline-green` inline, bypassing
`--color-ring`.** `resources/views/vendor/pagination/tailwind.blade.php` and the skill
rows on `runs/show.blade.php` both do it. `green` is `#7FCC09`, which measures 1.88 on the
light panel against WCAG 1.4.11's 3:1 for a non-text boundary — the exact failure
`--color-ring` exists to prevent. The base-layer rule is correct and is being overridden by
hand at four call sites.

**Recommended:** delete the four inline `outline-green` declarations and let the base layer
win. No `transition-colors` is a deliberate choice to keep; adding one needs an owner
ruling, because it would be the first easing in the app.

**Corrected 2026-10-05.** The sentence that stood here read: "Also absent: any
`prefers-reduced-motion` block. With zero motion that block is currently unnecessary, and it
becomes mandatory the moment the ruling above goes the other way." The block now ships in
`app.css`'s base layer (§12), so the absence it recorded no longer holds. It is the escape
hatch for the operating-system setting and the guard for the first easing a later slice adds;
the MOTION dial stays 1 and no `transition-*` utility was added.

## 9. Where tokens live

- `resources/css/app.css` `@theme static` block: single source of truth.
  There is no `tailwind.config.js` and none may be created (Tailwind v4
  CSS-first; ARCHITECTURE §1). **`static` is load-bearing:** without it Tailwind
  prunes any custom property no utility class references yet, an undefined
  `var()` silently inherits instead of failing, and a contrast check then reports
  a pass on a token that is not in the stylesheet (research `CONSTRAINTS.md` D-288).
- `docs/research-scratch/DESIGN-CORPUS.md` section "DESIGN.md" (formerly
  `docs/design-research/DESIGN.md`): measured ramps, contrast tables,
  cap-stack rendering spec (§6.21), and the light theme that is now the app default.
- `docs/research-scratch/DESIGN-CORPUS.md` section "CONSTRAINTS.md" (formerly
  `docs/design-research/CONSTRAINTS.md`): the research-phase design rules
  (D-numbers) this file cites (D-12, D-20, D-101).
- Proposed values in this file (scale numbers, the mono stack) need owner sign-off before
  they become tokens. The Announced-cyan proposal is now a shipped pair — `--color-sp` for
  identity surfaces, `--color-sp-ink` for text. `max-w-6xl` is retired: it never shipped,
  and §2.3 now records the three widths that did.

## 10. What is enforced, and what is only claimed

The gap between these two columns is the honest state of design verification in this
repo, and it is the finding most worth carrying out of this audit.

| Claim | Enforced by | State 2026-10-04 |
|---|---|---|
| No `zinc-*` utility, no `dark:` fork, on shell pages | `DesignTokensTest` (data provider over URLs) | **passing** |
| Pagination renders from tokens | `DesignTokensTest` | **passing** |
| Exactly 60 colour tokens in `@theme static` | `DesignTokensTest` | **passing** (11 passed, 42 assertions) |
| Selection boundary uses `pick-line`, never `pick` | `DesignTokensTest` (whole view tree) | **passing** |
| Theme preference is one keyed row, SQLite only | `DesignTokensTest` | **passing** |
| First paint is the resolved theme; unknown stored value ignored | `DesignTokensTest` | **passing** |
| All 60 tokens resolve non-empty in both themes (D-288) | `DesignTokensTest`, browser block | **skipped** — no Playwright, test is `markTestIncomplete` |
| All 19 text/background pairs + 9 grade fills clear 4.5:1 in both themes (G-18) | `DesignTokensTest`, browser block | **skipped** — same cause |
| Rendered copy: `N/A` for absence, never a dash, no `Unknown` | `RenderedCopyHygieneTest`, view tests | **passing** |
| Flash banners use tokens | `FlashBannerTokensTest` | **passing** |
| 768px minimum width is safe | nothing | **Unverified** — see §2.3's held proposal |
| Every ratio quoted in this file | research-phase measurement, files absent | **documented, not reproducible here** |

The two skipped rows are why this file's contrast numbers cannot be treated as verified on
this host: they were transcribed from research captures whose evidence files are not in
the tree. Installing `pestphp/pest-plugin-browser` plus a Playwright driver and replacing
the two `markTestIncomplete` calls with real assertions is the single change that would move
the whole §2.1 table from *documented* to *enforced*.

## 11. Open questions for the owner

Not defects; decisions this file cannot make for itself.

1. **The three unreachable components** (§3): adopt or delete? Two of them block a motif
   this file already rules material.
2. **The stale `app.css` header comment** declaring dark the shipped default, twelve lines
   above a light-default block (§2.2). Source-file fix, needs owner go-ahead.
3. **`transition-colors`**: keep the tree motionless, or ratify a single transition token?
   The contract currently prescribes something that ships nowhere (§8).
4. **The four inline `outline-green` overrides** (§8): a correctness fix, not a decision.
   Listed here only because it touches a vendored pagination view.
5. **The 768px minimum** (§2.3): ratify with the measurement, or withdraw the number.
6. **Loading states** (§3): with no client-side fetching, C-7's loading state has no
   surface. Confirm that "no loading state, because nothing loads client-side" is the
   accepted answer, or approve the fetch-in-flight affordance §7 defers.
7. **Which surfaces get artwork, and whether the box is reserved** (§4.7): `ADR-0021`
   authorizes sourced art and §4.7 fixes how an absent file behaves, but neither decides
   where a picture appears or whether the layout reserves its box. Recorded as PRD OQ-6.
   No pixel value is proposed here for the same reason §2.3's 768px number is question 5:
   a number this file invents is a number no measurement supports.

## 12. Accessibility conformance

**Target: WCAG 2.2 level AA on every shipped screen**, the ported Inertia pages and the live
Blade screens alike, verified by an axe pass plus a keyboard-only pass, 200% zoom, 320px
reflow, and a reduced-motion check. The criterion-by-criterion disposition lives in
`docs/proposals/frontend-development-plan.md` §12; this section records only what the visual
system owns.

- **Focus.** One base-layer `:focus-visible` rule (2px `--color-ring`, 2px offset) covers every
  focusable element, so a component cannot ship without a ring. The four inline `outline-green`
  overrides named in §8 remain the one violation; deleting them is the fix.
- **Focus not obscured (2.4.11).** Below the `md` breakpoint the shell pins a nav bar to the
  viewport bottom; `scroll-padding-bottom: 4rem` on `html` keeps a focused control or a fragment
  jump from landing underneath it.
- **Reduced motion.** A `prefers-reduced-motion: reduce` block in `app.css` collapses any
  animation and transition to a single frame and disables smooth scrolling. With the MOTION dial
  at 1 there is nothing to collapse today; the block is the escape hatch and the guard for later
  work.
- **Target size.** The house bar is 44px (`min-h-11`/`h-11`), stricter than WCAG 2.2 AA's 24px
  minimum (2.5.8). Every control meets the 24px floor; 44px is the design intent.
- **Colour is never the only signal.** Every state carries a word or a glyph beside its hue
  (§5, §6).

## Change log

- **2026-10-05** — §4.7 and §3 corrected after the artwork slots moved from Blade to Vue. The two
  rows the read-half entry below added named `x-character-portrait` and `x-support-thumb`; both
  components were deleted the same day because the A1 to A3 ports retired their only call sites, and
  `ArtworkSlot.vue` replaced them across all four slot-bearing screens. Changed: the §3 count (11
  Blade components and 26 Vue SFCs, with the two-day lifespan of the Blade slot components recorded
  rather than hidden), §4.7's status paragraph (the slots are in `.vue` files, so
  `grep -rn "<img" resources/views` being zero is again the correct answer and not a gap), the
  geometry rule (`ArtworkSlot` emits no `width`/`height`, the size class reserves the square, and the
  three values are pinned as a TypeScript union so Tailwind's scanner sees them), and the fifth
  bullet — which had recorded the `decorative`-flag limitation as a known owner-visible gap and now
  records its resolution: `alt` and `href`/`linkLabel` are separate props, so the three no-action
  slots carry a decorative `alt=""` with no anchor at all rather than a nameless self-link that
  failed WCAG 2.2 AA 4.1.2. Unchanged: all four original rules, the absence discipline, the C-4 alt
  boundary, the `64 64`-free geometry decision in favour of CSS reservation, the run-create carve-out,
  the skill-row deferral, and OQ-6 remaining the owner's call. **Superseded in part:** the entry below
  claims `size-*` defaults and a reserved-pixel pair that the deleted Blade components carried.
- **2026-10-05** — Declared the WCAG 2.2 AA conformance target (§12) and shipped its three
  visual-system pieces: a `prefers-reduced-motion` block and `scroll-padding-bottom` for the
  pinned mobile nav in `app.css`, and a `role="banner"` landmark on the shell header. Corrected
  the §8 sentence that recorded the reduced-motion block as absent. The 0.1.0 Blade and Vue
  screens were remediated to the same bar (Japanese names carry `lang="ja"`, the guided-rail
  heading skip is closed, the error-page actions meet the 44px bar, and the ported pages gained
  loading and processing states).
- **2026-10-05** — §4.7 added (sourced artwork slot behaviour) and §11 item 7 added, both
  following `ADR-0021`. No token, component, motif or ruling was changed, and no size class
  was invented: the section states the four rules that bind a slot and names the two open
  decisions it refuses to guess at, which are `PRD.md` OQ-6 and the reserved-box question.
  The measured starting point is recorded rather than assumed — there is no image element
  anywhere in `resources/views/`, so today's text-only row is the fallback. `ADR-0021`'s fetch
  half landed the same day as `uma:fetch-art`, which changes no rule in §4.7: the mirror can now
  be filled, and no slot exists to read it.
- **2026-10-05** — §4.7 rewritten for the read half. The `ADR-0021` fetch half (`uma:fetch-art`) was
  already recorded; this row records the display half landing on the catalog and support-card screens.
  Changed: the heading status (`mirror built, slots not` → `mirror and slots built`), the status
  paragraph (the "no image element anywhere in `resources/views/`" claim above is now false and is
  superseded by this row, not edited), the run-create carve-out and the skill-row deferral, the
  geometry rule (this section now records `64 64` as the reserved pair and the three painted boxes,
  with `size-10` named as the one single-view class and the reason it stays), the `src` rule (now that
  a route exists, it records that the route is id-addressed and cannot be walked), and a new fifth
  bullet separating a decorative image from an unnamed link. §3 count corrected to 26 components and
  24 reachable, and two artwork-slot rows added to the surface table. Unchanged: all four original
  rules, the absence discipline, the C-4 alt boundary, and OQ-6 remaining the owner's call. The
  known gap named in the new bullet is real and unfixed; two call sites currently hold it apart by
  opposite workarounds rather than by one decision.
- **2026-10-04** — 2.0 design target filed for reference at `docs/proposals/design-2.0.md`
  (with `docs/proposals/screen-spec-2.0.md`), the Inertia/Vue rewrite reference (`ADR-0020` §1).
  This file still owns the shipped Blade visual system. The target's trust-model, explainability,
  no-false-precision and accessibility rules align with `ADR-0001` and are the intended carry-across;
  its one conflict (a race win-chance example) is governance-held in the target's banner. No token,
  component, or ruling in this file was changed.
- **2026-10-04** — Mounted the two adopted orphans. `capsule-header` now renders the
  capsule on all eight panels that were hand-copying the div, and `grade-badge` now owns the
  grade fill in `stat-band`, which had carried a second copy of the nine-letter map and its own
  badge span. §3 reachability recounted (24 components, 22 reachable, `deck-editor` and
  `run-header` with no call site, `energy-gauge` only through `run-header`), the seven-unreachable
  paragraph replaced with the three that remain and the reasons they are still parked, and open
  question 1 narrowed to match. The ruling that these motifs are material is unchanged and now
  reaches the surfaces it was ruled for.
- **2026-10-04** — Audited this file against the tree. Corrected four stale claims
  (token migration complete, font stack shipped, `welcome.blade.php` gone, catalog is a
  roster tree not a table). Added the status legend, the 40 undocumented token roles,
  reachability across all 28 components, the shipped base-layer treatments, the
  enforcement table, and the owner questions. No owner ruling was changed or reversed.
- 2026-09-29 — Catalog detail specification withdrawn and rewritten; 768px minimum
  recorded as a held proposal rather than a contract.
- 2026-09-27 — Light-first owner ruling; lattice/enamel/torn-chip motifs ruled material.
