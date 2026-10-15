# Frontend fidelity — why the shipped surface reads as a wireframe, and the two-track path to a product

> **Reference only. Not authorization.** Nothing in this file is a decision. No component, token, copy
> string, gate or spec line was changed to produce it. Each section names the files an approval would
> touch and touches none of them.
>
> Recorded 2026-10-13 from the owner's own assessment ("these are what I consider high-fidelity
> wireframes, but not what the frontend of this application could be"), the six dark-theme wizard
> screenshots supplied with it, a read of the visual contract (`DESIGN.md`), the richer 2.0 target
> (`docs/proposals/design-2.0.md`), the token block (`resources/css/app.css`) and the three existing UI
> audits in this directory. It is the fifth proposal-side audit file; it reconciles the other four rather
> than restating them. **Added 2026-10-13:** the 25 full-page captures in `docs/proposals/HLF_reference/`
> (a v0 high-fidelity build) are folded in at §4 as the concrete, screen-by-screen target. They are still,
> in the owner's words, a high-fidelity wireframe rather than the finished product.
>
> **The one-line finding:** the surface is not under-built as engineering. It is a faithful implementation
> of an **austere** visual contract, and the richer target that would make it read as a product already
> exists on paper — but sits **lower in the precedence chain**, so the austere reading wins by default.

## 0. What governs this question

Per `AGENTS.md` §2 the chain is: quality bar → gate registry → ADRs → `PRD.md` → `ARCHITECTURE.md` →
`SCREEN_SPEC.md` → **`DESIGN.md`** (visual system) → `docs/proposals/design-2.0.md` (reference target).
The last two are the whole argument:

1. **`DESIGN.md` is the shipped contract** (`DESIGN.md:41-47`, `ADR-0006` authority split). It carries the
   austere rules: dial `ENERGY 1 / RHYTHM 1 / MOTION 1` (`:24-27`), "no pastel anime gradients, no soft drop
   shadows, no marketing layout" (`:58-60`), "Radius `rounded-md` consistently" (`:243`), "Shadows: none.
   Separation comes from the rule edge and the raised/sunken step" (`:244-245`), "No page animations; a desk
   does not move" (`:696`).
2. **`design-2.0.md` is the richer target** and it is **outranked**: it describes a four-level visual
   hierarchy (`:290-358`), a radius scale (`:525-540`), sparing shadows for dialogs/panels/recommendations
   (`:543-560`), a Display/Heading/Body/Numeric type hierarchy (`:443-494`), and cards as the fundamental
   grouping mechanism (`:586-613`). Under `AGENTS.md` §2 the reference target loses to `DESIGN.md`; where
   they disagree, the austere clause ships.

So the wireframe read is **the precedence chain working as designed**, not a build failure. Changing it is
a **contract amendment** (an owner ruling), which is why this file has two tracks and only one of them is a
styling job.

## 1. The verdict, stated plainly

- **The engineering is not the problem.** The audits record a token-only surface with zero `dark:` forks,
  44px targets, one focus ring, both themes measured, and single-owner discipline that holds
  (`docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §1.3). The dark screenshots show correct, accessible,
  consistent components.
- **The expression is the problem**, and it is **partly deliberate** (the `DESIGN.md` clauses above) and
  **partly unlanded craft** (the type scale, the measure, the density — things both documents already ask
  for and neither has shipped).
- **"What the frontend could be" is already written down** — it is `design-2.0.md`. The task is not to
  invent a direction; it is to decide which of the 2.0 expression clauses become binding, and to land the
  craft both documents agree on.

## 2. Why it reads as a wireframe (evidence)

### 2.1 The tells

| Tell (visible in the screenshots) | Evidence |
| --- | --- |
| Cards dissolve into the page; the surface is flat | `DESIGN.md:244-245` "Shadows: none"; `app.css:20-21` "No shadows and no custom radius tokens". In the **dark** override the ramp collapses: `page #0D0C0F` ≈ `panel #121013`, and the only separation is the hairline `rule #2E2C33` (`app.css:221-231`). |
| Uniform typography; nothing leads the eye | `DESIGN.md:188-190` specifies section `text-lg` / title `text-2xl`; the audit measured **75** headers at `text-base` and the shell `#title` at `text-base` (`docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §2.2, X-2). |
| One accent, grayscale everywhere else | `app.css:57` "Green is action and affordability only, never 'good'"; `DESIGN.md:53-54` "Colour is semantic only". Lime on the button is the page's only hue. Sampled against the client sheets and the official site in §2.3. |
| Native form controls | selects/inputs are `border border-rule bg-raised rounded-md` and nothing more; there is no shared form-control component. |
| Content pinned left, vast dead space | `AppLayout.vue:295` `main` has no `max-w` (`remaining-screens` §2.8, F-A2); the ratified widths (`DESIGN.md:210-221`) are used by almost nobody. |
| No imagery | the artwork system exists and is built (`ADR-0021`, `uma:fetch-art`) but no wizard or catalog-in-wizard surface shows a frame. |
| No motion | `DESIGN.md:692-709`: MOTION 1, and `transition-colors` "ships nowhere"; one `animate-pulse` in the whole tree (`F-G10`). |
| No display voice | `app.css:31` system font stack only; the rounded display font "stays parked behind a C-8 approval" (`DESIGN.md:204-206`, `app.css:29`). |

### 2.2 The root cause

Two contracts, and the austere one outranks the rich one. `design-2.0.md` §3 asks for a surface that is
"optimistic, tactical, game-adjacent, modern"; `DESIGN.md` §1 asks for a "sports-science or esports
analytics tool, never like a gacha wiki". Both are defensible; they are not the same product, and the
shipped surface is the second one, implemented cleanly and completely. A "wireframe" is the honest
description of a fully-realised austere analytics tool.

### 2.3 The colour question — sampled from the client sheets and the official site

The owner's assessment is specifically about colour: the light surface "does not resemble Umamusume
Pretty Derby". Two palettes were sampled to test that claim rather than assert it — the 19 in-game sheets
in `docs/design-research/_scratch/references/sheets/` and the 11 official captures in
`.../references/web/`. Both are read-only (`AGENTS.md` §11). The values below are **quantized buckets of
dominant and saturated pixels, not a published spec**; treat them as indicative.

| Sampled from | Dominant field | Signature accents |
| --- | --- | --- |
| Client sheets (19) | near-black `#161616` (40-53% of every frame) | cream/parchment `#FFEECC`, pale lavender `#F4F4FF`, lime `#77CC11`, sky `#77CCFF`/`#55BBFF`, violet `#554499`, crimson `#BB3355`, warm tan `#BB9988` |
| Official site (11) | royal blue `#2255FF` (6-29% of saturated pixels) | hot pink `#FF77CC` call-to-action, lime `#BBDD11`, racing green `#44AA33`, violet `#8877FF`, teal `#00BBBB`, gold `#CCBB00` |

Two findings, and the first corrects a framing this proposal would otherwise repeat:

1. **The shipped palette is already client-measured — the hues line up.** `DESIGN.md:56` states both
   palettes are "measured from client frames", `DESIGN.md:120-137` is a measured pair table, and `:149-151`
   warns that six degrees of drift off the client's action green "breaks the resemblance". The sampling
   agrees: shipped action green `#7FCC09` ≈ sheet lime `#77CC11`; shipped ink brown `#6A5641` ≈ sheet tan
   `#BB9988`; shipped page `#F2F1F8` ≈ sheet `#F4F4FF`; shipped Skill-Points cyan `#009FE1`
   (`DESIGN.md:133`, "the client's cyan, exact") is the sheet sky family. So "the app does not use the
   game's colours" is **not** the finding — the app does.
2. **What is missing is the *marketing* palette and the client's *chrome distribution*.** The royal blue
   `#2255FF` and hot pink `#FF77CC` that carry the official site are **not in the token set at all**, and
   the client's own base in the sampled frames is dark (`#161616`) — the opposite of the shipped default.
   The shipped surface uses the client's *hues* but not its *product surface*: no blue chrome field, no
   pink call-to-action, no violet, and a pale default where the client frames are dark. That distribution
   gap is what the eye reads as "does not resemble the game".

The consequence for the tracks: **colour is almost entirely a Track 2 question.** `app.css:57` ("Green is
action and affordability only, never 'good'") and `DESIGN.md:53` ("Colour is semantic only") forbid a brand
blue or pink inside the contract; adding either is a `DESIGN.md` §2.1 amendment (Track 2 D) with a measured
pair per new role (G-18). Track 1 can only (a) spend the *existing* client-derived tokens more deliberately
— gold `pick` `#EFC96A`, cyan `sp` `#009FE1`, warm ink — where the client itself uses them, and (b) fix the
dark ramp (§2.1), which is the one move that brings a theme *closer* to the client base. One hue family,
violet (client `#554499` / site `#8877FF`), has no shipped token in either theme.

Honesty note: the sheets are client captures with artwork, anti-aliasing and a quantized palette; the site
is a marketing surface, not the client. Neither is a brand specification, and no official palette is
published in-repo. These are directions to audit against, not values to paste.

### 2.4 The game client corpus — the fidelity reference above the HLF

The repo already holds a detailed, cited description of the *real* game client's UI, and it outranks the HLF
as a fidelity target: the HLF is a wireframe of a tool, while these notes describe the product the Trainer
already uses. The corpus is `docs/research-scratch/screenshot-notes/00`-`06` (7 notes over 52 reconciled
frames), `screenshot-notes-extras/` (3), and the `screenshot-registry/` evidence set. Each note carries the
same four headings — *Visual hierarchy*, *What carries state*, *Absences*, *What this screen teaches the
app* — so it reads as a UI specification, not a gameplay log.

Patterns the client uses that the shipped build lacks:

| Client pattern | Where | What Trainer Desk lacks |
| --- | --- | --- |
| Per-item detail as a modal over a dimmed backdrop | `00:40`, `04:24` | detail is a page, not a modal grammar |
| A negative-capable mark vocabulary with no legend (double-circle / circle / triangle / cross) | `00:16`, `00:97` | no shape-rank vocabulary; grades are letters |
| Stacked base/bonus gain bubbles (small upper bubble = bonus) | `01:53` | no projected-gain channel at all |
| A three-kind log grammar (named speech / unnamed narration / signed outcome) | `01:143` | the log/rail is flat |
| A risk channel that appears only when non-zero (pink `Failure 5%`) | `01:318` | no conditional alert treatment |
| Pending basket kept distinct from the displayed balance | `03:27` | selection and balance are not separated |
| A goal ladder with one "next" plus a `Goals Achieved 5/8` footer | `02:160`, `02:165` | goals are a list, not a ladder |
| A contextual hint naming the weak stat, on the decision surface | `05:32` | the advisor is a flat rail, not a hint bubble |
| Accent taken as data (per-trainee / per-state tint) | `01:86` | per-scenario accent exists; per-state tint does not |

Three of these reinforce §2.3 rather than opening a new track:

- **The client's signed-change colours match the shipped tokens.** `00:75` records outcome deltas as
  "sentences with direction words colour-coded (down blue, up orange)"; the shipped `--color-down` is blue
  and `--color-up` is orange (`DESIGN.md:129-130`). The app already reproduces the client's up/down
  semantics, which is further evidence for §2.3 finding 1.
- **The client's mood ramp is non-monotone, negatives cool.** `06:103`: "GREAT deep pink, GOOD burnt orange,
  NORMAL gold, BAD blue, AWFUL purple... the two negative tiers sit on the cool side of the wheel". That is
  the same warm/cool split the app's up/down pair uses, and it is a *semantic* ramp, not brand chrome.
- **The client confirms the app's cut features.** No win probability (`02:27`, `02:76`, `02:190`), no score
  or aggregate (`02:217`: a clean sweep renders as "five identical 'WIN' words with no aggregate"). The app's
  cuts are faithful to the client, not a reduction of it.

One tension, carried to the law audit (`ux-laws-audit-plan-2026-10-13.md` §2.7): the client *does* carry
alert channels — a red exclamation badge on a support card (`01:113`), a pink failure badge (`01:318`), and a
pending-obligation tag pinned to an action button (`05:85`) — which sits against the app's "no warning role"
cut (`app.css:57`, `DESIGN.md:53`).

Track placement: the modal grammar, the shape vocabulary, the gain bubbles, the log grammar, the goal ladder
and the decision-surface hint are **Track 1** (craft). The conditional alert channel is **Track 2 D**
(colour/role); the per-state accent is a data-model question. The notes are notes, not frames: the registry's
own rule applies — "a note is a lead, not a verification" (`screenshot-registry/README.md:166-167`).

### 2.5 The corpus's own interaction principles, and the web study

`DESIGN-CORPUS.md` is the folded corpus `DESIGN.md` was derived from, and it states interaction patterns
"worth carrying" that the shipped build does not carry:

- **Preview before commit** (`:4093`) — "every consequential choice shows its outcome numbers before it is
  accepted, and acceptance is a separate action".
- **Colour always paired with a word** (`:4094`) — "both the aesthetic and, conveniently, the accessible
  implementation".
- **Numbers live above the thing they change** (`:4095`) — the gain bubble floats over the stat it changes.
- **One saturated tile marks the active destination** (`:4096`) — a filled block, not an underline.
- **Grouping by phase, collapsible** (`:4097`) — the Log's phase bar, "exactly the shape a 24-turn run needs".
- **Shape carries meaning** (`:4098`) — "Circular buttons for the five disciplines, rectangles for everything
  else".

Plus §35 **Progressive Disclosure** (`:6503`, show `Speed +62 / 3 Supports / 2% Failure`, expand to the
breakdown) and §36 **Explainability** (`:6538`, every recommendation answers "why?"), and the "Additional
Design Details" (`:5614-5622`): never a single unexplained score, `N/A` + `title` for unavailable data,
progressive disclosure at 320px, **non-colour-only warnings**, and actual-vs-hypothetical balances.

These are the repo's own declared principles, not new ones, and each is a **Track 1** gap where the shipped
build does not render it: the wizard's per-stat editor shows the breakdown without a preview-and-accept step,
and the advisor is a flat rail with no "why".

One correction to §3's shadow question. The `DESIGN-CORPUS.md` §6 web study (`:4104-4116`) measured the
official properties as carrying **zero** `box-shadow` and **zero** `linear-gradient`/`radial-gradient`, with
all their depth from `filter: drop-shadow()`. That both supports the app's flat, gradient-free surface and
nuances Track 2 B: where the app does add elevation, the brand precedent is `drop-shadow`, not `box-shadow`.

## 3. The richer target already exists — reconciliation table

Every clause a "real frontend" needs is already specified in `design-2.0.md` and is currently losing to
`DESIGN.md`. This is the reconciliation the owner has to make:

| Expression clause | `design-2.0.md` (rich target) | `DESIGN.md` (shipped, wins) | Conflict? |
| --- | --- | --- | --- |
| Visual hierarchy | four levels, Critical→Metadata (`:290-358`) | none stated as levels | additive, no conflict |
| Radius | 6 / 10 / 14 / 16 / 999 (`:525-540`) | one `rounded-md` (`:243`) | **direct** |
| Shadows | sparing, for dialogs/panels/recommendations/menus (`:543-560`) | "Shadows: none" (`:244-245`) | **direct** |
| Type hierarchy | Display / Heading / Body / Numeric (`:443-494`) | scale is **Proposed**, display font parked (`:201-206`) | **direct on display** |
| Cards | the fundamental grouping mechanism (`:586-613`) | cards exist but with no elevation | partial |
| Personality | optimistic, game-adjacent, modern (`:266-288`) | serious analytics tool, no marketing (`:58-60`) | **tonal** |
| Data visualization / density / disclosure | `:1398`, `:1113`, `:1143` | not addressed | additive |

Three clauses conflict directly. Under `AGENTS.md` §2 the shipped surface is correct to ignore them, and
an agent may not resolve them silently — this is escalation territory (a design-contract change).

## 4. The HLF reference set — the high-fidelity target, pattern by pattern

`docs/proposals/HLF_reference/` holds 25 full-page captures of a v0 build (the filenames read
`ui-improvement-suggestions-v0-build`, page badge `v0.1.0`). It is the concrete, screen-by-screen answer to
"what the frontend could be", and it is the most useful artifact in this directory for the fidelity
question because it renders the target instead of describing it. It is also, per the owner, still a
**high-fidelity wireframe**: its data is placeholder, some copy is invented, and it is not bound by the
token contract. The game client corpus (§2.4) is the higher-fidelity reference beneath it: this section
treats the HLF as the tool-shaped target, §2.4 as the product-shaped one.

### 4.1 The pattern inventory

| Pattern | Where the HLF shows it | What the shipped build lacks |
| --- | --- | --- |
| Page header: lime eyebrow + large title + one-line subtitle | every screen (`OVERVIEW`/`LIBRARY`/`COMPARE`/`REFERENCE`/`DATA QUALITY`/`PREFERENCES`) | the shell `#title` is `text-base`; no eyebrow/subtitle convention |
| Back breadcrumb (`← Back to …`) | every detail screen | present on some, not as a convention |
| KPI stat cards (label, big number, sub-label) | Dashboard | none |
| Status pills (In progress, Complete, Needs review, Needs legacy, Active, Completed, Observed, Mandatory) | Careers, Cockpit, Review, Legacy, Veterans | status exists but flat |
| Provenance badges (Confirmed, Unknown, Calculated, Partial) | Events, Shop Items, Sparks, Cockpit | `ProvenanceBadge` exists but is under-used |
| Category/type chips (SSR/SR/R, Power/Speed, Stat/Aptitude/Skill/Competition/Scenario, Consumable/Currency) | Support cards, Sparks, Shop Items | partial |
| Effect chips (`Friendship Bonus 15%`, `Speed Bonus 1`, …) | Support card detail | none |
| Labeled sub-cells (key/value mini-boxes) | Cockpit identity + stats, Compare, Support/Skill detail | none — the signature HLF component |
| Contained bordered cards on a raised surface | everywhere | cards have no elevation and dissolve in dark |
| Progress bars (lime fill) | Careers | none |
| Row-count line (`400 catalogue rows`, `559 of 559`) | Support cards, Skills, Races, Events, Sparks | partial |
| Tables (Races; Compare as property/value; Sparks odds) | Races, Compare, Sparks | tables exist |
| Search + filter rows in a bordered container | Careers, Support cards, Veterans, Skills, Legacy, Trainee | partial, unstyled controls |
| Tabs | Settings (General/Recommendation/Data/Game version), Cockpit (Cockpit/Dashboard) | none |
| Anchor nav | Cockpit (Position, Run identity, Stats and state, Actions, Scenario, Races, Advisor) | none |
| Pagination | Support cards, Skills, Review, Races | exists |
| Artwork thumbnails | Support cards list + detail header | artwork system built (`ADR-0021`), unused here |
| Wizard option cards (status line + one Select button) | Scenario, Target, Legacy, Deck | the steps are heavy editors instead |
| Advisor structure: Objectives / Current priority / Recommended action / Recorded action | Cockpit | the advisor is a flat rail |
| Scenario panels grid with honesty notes | Cockpit | scenario region is sparser |
| Raw engine data shown as-is with a note | Skill detail (`Condition`, `Base time`, effect codes) | partial |

### 4.2 What the HLF proves is reachable inside the current contract

The HLF's "product" feel comes mostly from **borders + a real surface step + a type scale + density + a
badge/sub-cell vocabulary + artwork** — **not from shadows**. Its cards are bordered, not shadowed. So
Track 1 (plus a radius decision) gets most of the way, and Track 2's **shadow clause is optional**: the HLF
weakens the case for amending "Shadows: none" (`DESIGN.md:244-245`). What the HLF does require beyond
Track 1:

- a **radius scale** (its cards are visibly rounder than `rounded-md`) → Track 2 A;
- a **badge colour vocabulary** (lime `In progress` / `Confirmed` / `Mandatory`, amber `Unknown` / `Needs
  decision`) → **conflicts with `app.css:57` "green is action and affordability only, never 'good'"** →
  Track 2 D;
- a **real type scale** (Display / Heading / Body / Numeric) → Track 1-2, plus Track 2 C for the display
  face.

### 4.3 What the HLF changes beyond styling (scope / semantics — needs a decision)

- **Wizard steps become option-card presets.** Target → `Speed focus / Balanced growth / Race
  preparation`; Legacy → `No legacy recorded / Use a saved selection`; Deck → `Current support deck /
  Start with empty deck`. This **removes the per-stat target editor, the two-parent ancestry editor and
  the six-slot deck editor from the wizard**. That is a domain/scope change, not a skin — those editors
  must move elsewhere or be cut, and it needs a `SCREEN_SPEC.md` + `PRD` decision (escalation 2).
- **Preflight drops the duplicate "Edit step" buttons** and gives each warning an inline reason
  (`Scenario is not chosen. Nothing is recorded in this setup.`). This matches the audits' Occam's Razor
  finding and is adoptable inside Track 1.
- **Cockpit gains a tab toggle ("Cockpit | Dashboard") and an anchor nav.** Per cockpit audit §0.1,
  `SCREEN_SPEC.md:1841` forbids hiding an element at a breakpoint and a tab strip that shows one section
  at a time; an **anchor** nav is allowed. So the anchor nav is compatible; the "Cockpit | Dashboard"
  toggle must be checked against that ruling before it ships.
- **Compare becomes a property/value table** instead of a wide matrix — this is *better* for the
  responsive region contract than the current compare tables, and worth adopting.
- **A richer status vocabulary** (`Needs legacy`, `Needs review`, `In progress`, `Partial record`,
  `Observed`) — a copy/state expansion that ties to the absence/state work in the audits.
- **The stepper still says "done" for un-entered steps.** On the Career-contract screen all six steps read
  "done" while every warning says "Nothing is recorded in this setup". The HLF **reproduces the P0** the
  live capture found (`docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"setup-wizard-ui-audit-2026-10-12.md" §2.2); adopting the HLF does not fix the
  stepper semantics — the three-state decision still stands.

### 4.4 Honesty patterns worth keeping

- "Each figure is the effect's highest stated anchor, not an interpolation."
- "The expressions and effect codes above are the source's own engine data, not client labels."
- "Reference note: this scenario intentionally exposes only the widgets and panels documented for its
  ruleset."
- Raw engine data (skill `Condition`, `Base time`, effect codes) shown as-is with a note.

These are exactly the provenance/absence discipline the audits ask for, rendered well, and they are the
part of the HLF that is safest to adopt.

### 4.5 Where the HLF lands each track

| HLF pattern | Lands in |
| --- | --- |
| Header, sub-cells, badges, chips, progress, counts, tabs, anchor nav, artwork, tables, filters | **Track 1** (craft, no ruling) |
| Radius scale | **Track 2 A** |
| Badge colours (lime/amber status) | **Track 2 D** |
| Display type tier | Track 1-2, full version **Track 2 C** |
| Card borders + surface step | **Track 1** (no shadow amendment needed) |
| Wizard option-card simplification | **scope decision** (escalation 2), not styling |
| Cockpit tab toggle | check against `SCREEN_SPEC.md:1841` |
| Compare as property/value table | Track 1 (and improves the responsive contract) |
| Stepper "done" wording | unchanged — the P0 remains |

### 4.6 The caveat, restated

The HLF is itself a wireframe: it uses placeholder values (a completed run showing `Turn 1`), invented copy
("Good morning, Trainer"), and colours that may not clear the token contract. It is a **direction to audit
against**, not a build to copy. Its value is that it answers "what could this look like" with pixels, so
the two tracks can be argued concretely rather than abstractly.

## 5. Track 1 — inside the current contract (no ruling needed)

These are craft layers that **both** documents already ask for and neither has shipped. They are the
cheapest real gain and they need no contract change. Sequenced by leverage:

| # | Move | Where | Acceptance |
| --- | --- | --- | --- |
| 1 | Apply the ratified shell measure | `AppLayout.vue:295` → `mx-auto w-full max-w-5xl` (F-A2, X-8) | no page runs to the shell's full width; the dead right column is gone; `dashboard.spec.ts:195-224`, `veterans.spec.ts:40-43` move with it |
| 2 | Land the type scale (one shell decision, then pages) | shell `#title` → `text-2xl`; section headers → `text-lg font-semibold`; promote the `text-xs uppercase` eyebrows (X-2, F-A3; cockpit audit §3.1) | one `h1` per page at `text-2xl`; headers at `text-lg`; the 75-vs-30 spread closed |
| 3 | Give the dark ramp real separation | dark override `panel`/`page` step, or render cards on `raised` (`app.css:219-231`); the rule edge is the *sanctioned* mechanism (`DESIGN.md:244-245`), it is simply too weak in dark | a measured non-text step ≥ 3:1 (WCAG 1.4.11) between a card and its page; axe clean |
| 4 | One form-control treatment | a shared input/select/checkbox style; the `caret-color` rule already exists (`app.css:718`) | every control ≥ 44px, one border and radius, consistent focus ring |
| 5 | Densify where the data already exists | Scenario, Trainee, Legacy, Deck have sparse layouts with content available; tighten to the 4/8/12/16/24 scale (`DESIGN.md:238-240`) | measured reduction in vertical dead space; no clipping at 768/1280 |
| 6 | Spend the motif budget, add the artwork | enamel once per screen (X-3, F-F3); render `ArtworkSlot` on the catalog/trainee/deck surfaces (`ADR-0021`) | enamel count = one primary per screen; four artwork slots render |
| 7 | Finish the empty / loading / error states | C-7, `ADR-0007`; the audits found several silent states (X-4) | every data view renders its three states |

Note the ordering constraint the audits already state: **do not restyle per-page headings before the shell
`#title` decision** (`remaining-screens` §5.8). Move 2 is one shell decision first, then the pages.

## 6. Track 2 — needs a `DESIGN.md` amendment (owner ruling)

Each of these reverses or extends a clause in the shipped contract. None is an agent's call.

| # | Amendment | Reverses / extends | Path |
| --- | --- | --- | --- |
| A | Radius scale (6/10/14/16/999) | `DESIGN.md:243` "`rounded-md` consistently" | owner ruling → `DESIGN.md` §2.3 rewrite |
| B | Sparing elevation for overlays only (dialogs, floating panels, the recommendation card, menus) | `DESIGN.md:244-245` "Shadows: none"; `app.css:20-21`; brand precedent is `drop-shadow`, not `box-shadow` (§2.5) | owner ruling → new `DESIGN.md` elevation table + a shadow token |
| C | A Display type tier (self-hosted rounded font) | `DESIGN.md:204-206`, parked behind **C-8** | C-8 dependency approval **and** owner ruling; offline constraint (`DESIGN.md:675-678`) still binds |
| D | A wider semantic colour vocabulary (brand blue chrome, pink call-to-action, violet; §2.3) | `DESIGN.md:53-54`, `app.css:57` (green = action only) | owner ruling; every new role needs a measured pair (G-18) |
| E | A motion budget above 1 for state transitions | `DESIGN.md:694-696`, MOTION 1 | owner ruling → raise the dial; respect the reduced-motion block |
| F | Adopt the four-level visual hierarchy | additive (no shipped equivalent) | owner ruling → `DESIGN.md` §2 gains a hierarchy table |

Track 2 is where "what the frontend could be" actually lives. Track 1 alone will make it read as a
*well-crafted* austere tool, not a product with a voice.

## 7. What each move touches

| Move | Audits / rulings it moves with | Guarded |
| --- | --- | --- |
| T1-1 measure | `remaining-screens` §2.8 / F-A2; `DESIGN.md:210-221` | `dashboard.spec.ts`, `veterans.spec.ts` |
| T1-2 type scale | `remaining-screens` §2.2 / X-2; cockpit audit §3.1 | `dashboard.spec.ts:87-92,130`, the accessible-name assertions in `database.spec.ts` |
| T1-3 dark ramp | `DESIGN.md:244-245`; G-18 pair list (`DesignTokensTest.php`) | the G-18 browser gate; propose pair rows, do not move a token |
| T1-4 form controls | `remaining-screens` §2.5 / X-5 | `tests/utils/tap-targets.ts`, `accessibility.spec.ts` |
| T1-6 enamel / artwork | `remaining-screens` §2.3 / X-3; `ADR-0021` | four 44px sweeps; `ArtworkMirrorTest` |
| T2-A..F | a new owner ruling per clause; `DESIGN.md` §2 amendment | the G-18 gate, `DesignTokensTest.php`, `TokenPairHygieneTest.php` |

## 8. What this proposal must not do

Inherited from `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §5 and `ux-laws-audit-plan-2026-10-13.md` §9,
plus three this document adds:

1. Do not move a colour token to answer a contrast question; propose pair rows and let the gate decide.
2. Do not add a cold-load spinner, skeleton, or `aria-busy` on page load (`ADR-0007`).
3. Do not add a score, percentage, confidence, grade, or recommendation to any surface.
4. Do not restyle per-page before the shell `#title` decision.
5. Do not reflow the race calendar / turn log / compare tables into stacked cards.
6. **Do not implement a Track 2 clause (radius, shadow, font, colour, motion) before the `DESIGN.md`
   amendment is signed.** That is the exact drift the precedence chain exists to prevent.
7. **Do not treat `design-2.0.md` as binding.** It is a reference target (`AGENTS.md` §2); until amended,
   `DESIGN.md` wins on every conflict.
8. **Do not let "make it prettier" reintroduce slop.** The audits confirmed zero gradients-as-identity,
   zero glass, zero glow (`remaining-screens` §1.3); the richer target is hierarchy and material, not
   decoration.
9. **Do not copy the HLF's placeholder data or invented copy.** Its completed-run-shows-`Turn 1` state and
   lines like "Good morning, Trainer" are mock content; the copy rules (`AGENTS.md` §5, the lore gate,
   the client vocabulary) still bind.
10. **Do not adopt the HLF's wizard option-card simplification as a restyle.** It removes the per-stat
    target, ancestry and deck editors from setup; that is a scope change and needs a `SCREEN_SPEC.md` +
    `PRD` decision before any component moves.
11. **Do not adopt the HLF's badge colours before Track 2 D is ruled.** Lime for `In progress` / `Confirmed`
    / `Mandatory` breaks `app.css:57` today.

## 9. Questions for the owner

1. **Which product is it?** `DESIGN.md`'s "serious analytics tool, never a gacha wiki" or
   `design-2.0.md`'s "optimistic, game-adjacent, modern"? Track 2 only makes sense under the second, and
   the answer changes every downstream clause.
2. **Radius and elevation (Track 2 A/B).** Adopt the 2.0 scale and sparing shadows, or hold the flat
   contract and rely on Track 1's dark-ramp fix to give cards presence without elevation?
3. **Display type (Track 2 C).** Is the C-8 dependency approval worth a self-hosted display font for the
   Display tier, given the offline constraint (`DESIGN.md:675-678`), or does the Display tier get its
   emphasis from weight and size on the system stack?
4. **Sequencing.** Land Track 1 as one slice (it is the visible, no-ruling gain), or wait and do Track 1 +
   the approved Track 2 clauses together so the shell is only touched once?
5. **Where does the amendment live?** A `DESIGN.md` §2 rewrite with new owner-ruling lines, or a new ADR
   that `DESIGN.md` cites — given `ADR-0006` makes `DESIGN.md` the single source of truth for the visual
   contract.
6. **The HLF wizard simplification (§4.3).** Do the per-stat target, two-parent ancestry and six-slot deck
   editors move to the run screens, or are they cut? The HLF reduces all three steps to a preset choice,
   which is the single largest change in the reference set.
7. **The HLF badge colours (§4.2).** Adopt the lime/amber status vocabulary as a new semantic role
   (Track 2 D), or map status to the existing tokens so no new pair is needed?
8. **What is the target of record?** Does the HLF become the concrete target (with `design-2.0.md` updated
   to cite it), or does `design-2.0.md` stay the target and the HLF remain illustration?
9. **Colour (§2.3).** Does the target adopt the official site's royal-blue chrome and pink call-to-action
   (Track 2 D, with a measured pair per role), or hold the client-measured, green-is-action palette and
   accept that the surface will resemble the *client* but not the *marketing site*? Relatedly: the shipped
   default theme is light (owner ruling 2026-09-27, `app.css:35`) while the client frames sampled here are
   dark — does light stay the default, or does the fidelity goal pull the default back to the client base?

## 10. Verification plan

Per `AGENTS.md` §9. Nothing below was run for this proposal.

| Approved change | Level 1 while working | Level 2 at slice end | Level 3 before hand-off |
| --- | --- | --- | --- |
| Track 1 (any move) | the touched screen's spec; `tests/utils/tap-targets.ts` where height is involved | the cluster's specs + `tests/browser/accessibility.spec.ts` | the full suite (the shell is felt by every screen); `npm run typecheck`, `pint --test --format agent <paths>`, PHPStan |
| Track 1-3 (dark ramp) | `php artisan test --compact --filter=DesignTokensTest` | the browser half of the same file (G-18, both themes) | the pair list and the `DESIGN.md:145-148` count move together |
| A Track 2 clause | not testable until the `DESIGN.md` amendment lands | — | re-baseline the audits against the amended contract before any component change |

Pre-flight before any broad sweep: time one trivial SQLite read (`KI-74`); a contended box voids the sweep
before it starts.

## 11. Hand-off notes

- **Deliverables produced:** this file. No component, token, copy string, gate, spec, or test was changed.
- **Facts this pass relies on:** the dial and the flat rules are `DESIGN.md:24-27, 58-60, 243-245, 696`; the
  richer target is `docs/proposals/design-2.0.md:290-358, 443-494, 525-560, 586-613`; the token values and
  the dark-ramp collapse are `app.css:20-21, 31, 219-231`; the type-scale and measure gaps are
  `docs/research-scratch/AUDIT-AND-VERIFICATION.md` §"remaining-screens-ui-audit-2026-10-13.md" §2.2 and §2.8; the live evidence is the six dark wizard
  screenshots and the 81 captures in `.scratch-uma/ux-audit/`; the concrete target is the 25 captures in
  `docs/proposals/HLF_reference/` (read in full, §4).
- **Colour evidence (§2.3):** sampled programmatically (`.scratch-uma/ux-audit/palette-extract.ps1`,
  `palette-accents.ps1`) from the 19 client sheets and the 11 official-site captures, because attaching the
  ~2 MB frames directly exceeded the model's context limit. The values are quantized buckets, not a
  published brand specification.
- **Client-UI corpus (§2.4):** `docs/research-scratch/screenshot-notes/00`-`06` (7 notes, 52 reconciled
  frames), `screenshot-notes-extras/` (3) and `screenshot-registry/` describe the real client's UI with
  per-screen *Visual hierarchy / What carries state / Absences / What this screen teaches the app* notes;
  folded in as the fidelity reference above the HLF. All citations verified against the source files.
- **Corpus principles (§2.5):** `DESIGN-CORPUS.md` §5 "Interaction and state patterns worth carrying"
  (`:4091-4098`), §35 Progressive Disclosure (`:6503`), §36 Explainability (`:6538`) and the "Additional
  Design Details" block (`:5614-5622`) declare the interaction principles the app should carry; the §6 web
  study (`:4104-4116`) shows the official properties are gradient-free with `drop-shadow` depth.
- **Fact this pass corrected in its own framing:** the shipped palette is not un-game-like — it is
  client-measured (`DESIGN.md:56, 120-137, 149-151`, including "the client's cyan, exact"). The gap the
  owner sees is the *marketing* palette (royal blue, hot pink) and the client's chrome distribution, not
  the hues the app already uses.
- **Not delivered, stated plainly:** no prototype, no measurement, no suite run, no lore gate, no fix. The
  HLF was read as reference; nothing in it was implemented or copied.
- **Fact this pass corrected in its own framing:** "the frontend could be X" is not an open design
  question — a richer target already exists in `design-2.0.md`, and the HLF reference set now renders it;
  the real question is that it is outranked by `DESIGN.md`, so the work is a contract decision plus the
  craft both documents already ask for.
