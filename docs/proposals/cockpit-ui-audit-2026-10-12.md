# Career Cockpit — UI audit (SCR-CAR-011)

> **Reference only. Not authorization.** Nothing in this file is a decision, and no component,
> token, copy string or spec line was changed to produce it. Each section names the files an
> approval would touch and changes none of them.
>
> Recorded 2026-10-12 from the owner's screenshots of `GET /training-runs/{id}/cockpit` (Unity Cup,
> turn 1, no turns logged) and a full read of the screen's own components. It is the proposal-side
> counterpart to `SCREEN_SPEC.md` §SCR-CAR-011, which stays the behavioural authority. The
> improvement list the owner supplied is reconciled in §1 rather than adopted: most of its twelve
> items are already shipped or already true, and four suggestions (in five parts) would reverse a
> landed owner ruling or an accepted ADR.
>
> **The run in the screenshots is empty.** `header.turn === 0` drives the empty branch
> (`Cockpit.vue:155`), the advisor declines, every stat is unrecorded, and the scenario region
> prints absence prose for a team state nobody entered. Roughly a third of the visual density in
> those screenshots is the empty state doing its job, not the layout. Findings below say which
> they are; anything that reads as a defect only on an empty run is marked **(empty-run)**.

## 0. What governs this screen

Per `AGENTS.md` §2, the chain that settles every dispute below is: quality bar → gate registry →
accepted ADRs → `PRD.md` → `ARCHITECTURE.md` → `SCREEN_SPEC.md` (screen behaviour) → `DESIGN.md`
(visual system) → `docs/proposals/design-2.0.md` (reference target). Five points from reading it
in full, because four of the owner's suggestions land on them:

1. **`SCREEN_SPEC.md:1841` fixes the layout and forbids the obvious implementation of one
   suggestion.** "The order is CSS only: no element is hidden at a breakpoint, so nothing leaves
   the accessibility tree and no primary decision needs a horizontal scroll." A tab strip that
   shows one section at a time would violate this directly; an anchor nav would not.
2. **`SCREEN_SPEC.md:1858-1865` is an owner ruling that closes the "prominent stat cards at the
   top" suggestion.** Energy, Mood, Fans and Skill Points print *once*, by
   `CareerStatePanel.vue`, beside the five stats the advisor ranks. They used to print twice, on
   the header as well, and were moved off it because "a reading shown twice is a reading that can
   disagree with itself". `EnergyReadingConsistencyTest` pins the header row, the state strip and
   the event screen to one reading. Moving Energy back to the top of the page reopens the defect
   that ruling closed.
3. **`ADR-0020` §2 and `ADR-0001`'s licence bar two of the action-card suggestions.** §2 carries
   `ADR-0001`'s guardrails forward — a three-band indicator by default, any numeric estimate
   opt-in and unbuilt — so there is no score, no percentage and no confidence anywhere in the
   payload (`SCREEN_SPEC.md:1869`), and `risk` was retired from the wire with
   `alternatives[].tradeoff` named as its honest home (`SCREEN_SPEC.md:1880-1881`).
4. **`ActionGrid.vue:9-16` records a landed decision against "unavailable" action cards.** Every
   entry is a link and every link is live: "seven unfocusable items would fail this slice's own
   keyboard-path acceptance and leave the Cockpit with no way to act." There is no absent arm to
   style.
5. **The G-18 contrast contract does not cover the surface this screen uses most.**
   `tests/Feature/DesignTokensTest.php:540-546` pins 19 pairs, and exactly one of them carries
   `ink-muted`: `ink-muted on sunken`. This screen's dominant muted usage is
   `text-xs text-ink-muted` on `bg-raised` and `bg-panel`. That is a coverage gap in the contract,
   not a measured failure — see §2.9.

## 1. The owner's improvement list, reconciled

The owner's twelve suggestions, each part placed in one of three buckets. A suggestion with more
than one part appears in more than one bucket; the number is the owner's, not this document's.

### 1.1 Already shipped — no work, or a one-line correction to the suggestion

| # | Suggestion | Where it already is |
| - | ---------- | ------------------- |
| 11 | "Collapse the sidebar" | `AppLayout.vue:131-163` (the rail), with its `aria-expanded` / `aria-controls` / `aria-label` at `:215-231`. The rail collapses to marks and persists to `localStorage` under `trainer-desk.nav-rail` (`:147`). |
| 8 | "Add visible focus states" | `app.css:335-338`: a global `:focus-visible { outline: 2px solid var(--color-ring); outline-offset: 2px }`. `--color-ring` is measured at 5.17 / 4.88 / 4.61 on light raised / panel / page and 7.61 / 9.51 / 9.79 in dark (`DESIGN.md:129`). `app.css:358` also sets `scroll-padding-bottom: 4rem` inside its `max-width: 767px` block, so a focused control is not covered by the pinned mobile nav bar. |
| 8 | "Avoid relying on lime/yellow alone for meaning" | `ActionGrid.vue:51-53` marks the recommendation with a star glyph plus the word `RECOMMENDED`, never colour alone. Every badge on this screen goes through `ProvenanceBadge.vue` with a glyph and a word. The accent (`--color-pick-line`) is a 2px *boundary*, measured 6.24 light / 8.46 dark against `raised` (`DESIGN.md:124`). |
| 11 | "Stack the advisor panel below the main content" on mobile | `Cockpit.vue:702-703` sets the grid and `order-1` at `:703`; the advisor's `order-2` is at `:850` and the actions' `order-3` at `:854`, so mobile reads state panel, then advisor, then actions. `SCREEN_SPEC.md:1843` requires exactly this: "The recommendation stays above the actions on mobile." |
| 12 | "Add lightweight feedback … confirmation toasts" for status/scenario/identity | Inline save states already exist on two of the four forms (`Cockpit.vue:503`, `:651`) and on the correction (`:820-822`, per ruling R3-04). What is missing is *consistency*, not the mechanism — see §2.4. A toast system does not exist anywhere in the tree and would be a new pattern. |
| 7 | "Use progressive disclosure" for technical evidence | Three `<details>` disclosures already ship on this screen: the manual correction (`Cockpit.vue:731`), delete-turn (`:828`) and delete-career (`:943`). The pattern the repo uses is `AbsenceValue.vue`, a native `<details>` whose summary is the accessible name (`DESIGN.md:576-585`). |

### 1.2 Supported by the governing docs — worth doing, cite the section that asks for it

| # | Suggestion | The governing line |
| - | ---------- | ------------------ |
| 1 | Stronger hierarchy: a real page title, status badge, primary action at the top | `DESIGN.md:188-190` specifies page title `text-2xl font-600`, one `h1` per page. What shipped is `AppLayout.vue:281` at `text-base`, the same size as every `h2` panel heading on this screen, and the trainee name prints in both (`Cockpit.vue:461` and `CareerHeader.vue:82-83`). See §2.1. |
| 2 | Reduce density: slightly larger text, more line height | `DESIGN.md:188-190` specifies body `text-sm` and section headers `text-lg`. Section headers shipped at `text-base` and a large share of this screen is `text-xs`. See §2.2. |
| 3 | Better top summary | Supported in the narrow form: the *scenario resources* strip (`CareerHeader.vue:141-154`) is two bare `N/A` boxes with no hierarchy against the five-field definition list above it. The four readings the suggestion wants (energy, rank, spirit, turn) already exist, and three of them must stay where the owner ruled. See §2.3. |
| 4 | Sticky advisor | `SCREEN_SPEC.md:1841` fixes the columns, not their scroll behaviour, and `design-2.0.md` §23 asks that "the player should never have to search for: *What should I do now?*" That is the argument for sticky. See §3.2 for the two constraints a sticky rail must respect. |
| 5 | Clarify edit mode | The always-open edit strip (`Cockpit.vue:482-689`) mixes eleven identity inputs, two write buttons per select, two export links and a deck link, under an `sr-only` heading, directly beneath the read-only header. See §3.3. |
| 6 | Larger action cards with descriptions and a recommended state | `design-2.0.md` §24 is the section that asks for comparable decision cards, and the grid's entries are already links with a note line. The gain is size and a mark, not new information. See §3.4. |
| 9 | Consistent scenario card headers and badges | The panel shell already composes `ScenarioStatusBadge.vue` (`ScenarioPanel.vue:158`) and `ProvenanceBadge` for `Estimated` (`UnityCupPanel.vue:204`, `:266`). What is inconsistent is header weight and copy length across the Unity Cup sub-panels, not the badge vocabulary. See §3.5. |
| 9 | Two-column scenario layout on desktop | Already true: `ScenarioPanel.vue:171` is `sm:grid-cols-2 lg:grid-cols-4`. The screenshot reads as one long column because Unity Cup composes seven panels and the region is full width under the three columns. |
| 10 | Section nav | Compatible with `SCREEN_SPEC.md:1841` as an anchor list; incompatible as tabs. See §3.6. |
| 11 | Responsive: convert dense tables to stacked cards | `SCREEN_SPEC.md:1841` and `DESIGN.md:209-217` already settle this class of problem: a wide region scrolls inside a focusable container (`overflow-x-auto` plus a `role="region"`, `tabindex="0"` and an `aria-label`), it is not reflowed. The Cockpit's own tables are the scenario caps table (`ScenarioPanel.vue:230`) and the burst-band table (`UnityCupPanel.vue:275`); both are candidates for the existing pattern before any card layout is invented. |

### 1.3 Conflicts with a binding decision — do not implement as written

| # | Suggestion | Why it stops |
| - | ---------- | ------------ |
| 3 | "Replace the small scattered fields with 4–6 prominent stat cards: scenario, energy, rank, spirit, current turn, priority" | Two parts of this conflict. Energy is owner-ruled into the state panel (`SCREEN_SPEC.md:1858-1865`, guarded by `EnergyReadingConsistencyTest`). Priority is the advisor's first element and already sits at the top of the rail (`NextDecision.vue:168`). Rank and spirit are scenario widgets whose home is the header strip *and* the scenario panel; see §2.6 before proposing a third home. |
| 6 | "Turn the action list into larger selectable cards with a **selected** state" | These are navigation links, not a selection control. A selected state would imply the choice has been committed; the Trainer navigates away to record it. `ActionGrid.vue:9-16` is explicit that each entry links to the screen that records the action. |
| 6 | "Visually distinguish **risky** and **unavailable** actions" | Risk is a held computation: `ADR-0001`'s licence section keeps any numeric estimate opt-in and unbuilt, and `SCREEN_SPEC.md:1880-1881` retires `risk` from the wire. Unavailable arms do not exist by a landed keyboard-path decision (`ActionGrid.vue:9-16`). |
| 7 | "Collapse long explanatory content behind 'Show details'" — applied to the advisor's `why` list | Ruling 5a (`SCREEN_SPEC.md:1886`) requires that where a figure rests on a constant, "the source and the read date print in the **visible text**, not only in the tooltip". A `<details>` is keyboard-reachable and so is not a tooltip, but `design-2.0.md` §2 (line 179) puts Explanation as the fourth of five first-class elements in the rail. The `why` list is the answer to "why should I believe this"; the defensible collapse targets are the scenario panels' methodology prose, not the rail. |
| 9 | "Add badges for 'Recorded' and 'Missing'" | The label set is four — `Confirmed` / `Calculated` / `Estimated` / `Unknown` — and its axis is set by surface class by owner ruling (`DESIGN.md:607-614`, recorded in `docs/proposals/provenance-and-absence-contracts.md` §9). `Recorded` and `Missing` are not in the vocabulary. An absence renders through `AbsenceValue.vue` with a reason, not a chip (`DESIGN.md:576-585`). |
> One suggestion is neither shipped nor a conflict. **#8, "Increase muted text contrast"**, is
> *unverified*: the pair this screen leans on is absent from the G-18 contract, so nothing currently
> proves it either way. The fix is to extend the contract and let the gate answer, not to move the
> token (§2.9, §3.7).

## 2. Findings

### 2.1 The page title is the same size as every panel heading, and the trainee name prints twice

`AppLayout.vue:281-282` renders the `#title` slot inside `<h1 class="text-base font-semibold">`.
`Cockpit.vue:461` fills it with `props.run.trainee`. `CareerHeader.vue:82-83` then renders the
same name again as an `<h2 class="text-base font-semibold">`, with the Japanese name and the card
form title beside it.

Consequences, all visible in the screenshot:

- The only `<h1>` on the page is `text-base`, which is the *same* size as the `<h2>` of every
  panel: `CareerHeader.vue:82`, `CareerStatePanel.vue:120`, `ActionGrid.vue:33`,
  `AdvisorRail.vue:30`, `Cockpit.vue:896` (Scenario), `Cockpit.vue:910` (Races in this run).
  Nothing establishes rank by size, so a screen reader's heading list and the eye's scan both
  flatten.
- "Rice Shower" appears twice within 40px of vertical space.
- `DESIGN.md:188-190` specifies the page title at `text-2xl font-600`. The shipped `text-base`
  is two steps below that.

This holds on any run; it is not an empty-run artifact.

### 2.2 The section header scale is one step under the specified one, and `text-xs` carries too much

`DESIGN.md:188-190`: body `text-sm`, table cells `text-sm`, section headers `text-lg`. Shipped:
section headers at `text-base` throughout (citations in §2.1), and a large share of secondary
copy at `text-xs` — `CareerHeader.vue:142,151`, `CareerStatePanel.vue:129,138,149,155`,
`NextDecision.vue:132,188,200,240`, `ActionGrid.vue:55`, `AdvisorRail.vue:39`,
`Cockpit.vue:820,916`. The sub-headings inside the rail (`NextDecision.vue:132,162,188,200,240`)
are `text-xs uppercase tracking-wide`, so five consecutive `text-xs` blocks stack in the right
column.

The cost is not aesthetic. `text-xs` is below `DESIGN.md`'s own body step, and the muted ink it
usually pairs with is the same tier that carries the *reasons* — the sentences that tell a Trainer
whether to go enter a reading or stop looking (`DESIGN.md:576-585`). Small plus muted is the worst
combination available for the text that matters most on this screen.

### 2.3 The header strip gives the scenario resources no hierarchy

`CareerHeader.vue:141-150` renders "Scenario resources" as a `text-xs` label over a
`ResourceStrip`, which draws one bordered box per widget. On Unity Cup that is two boxes: Team
Rank and Spirit Bursts (`ResourceStrip.vue:67-76`). Both render `N/A` / "not yet recorded" on the
screenshot, at the same weight as the five-field definition list above them
(`CareerHeader.vue:100-139`), which carries Scenario, Year, Month, Turn and Energy.

So five position facts and two scenario facts read as one undifferentiated band. The suggestion to
build a top summary out of scenario, energy, rank, spirit and turn is right about the *problem*
and wrong about the *fix*: four of those five already have an owner-ruled home. The available move
is to give the strip its own visual step — a heading size above `text-xs`, and the two boxes
subordinate to the definition list — rather than to relocate the readings.

### 2.4 Save feedback is present on two of the four header forms

| Form | Submit handler | Success line |
| ---- | -------------- | ------------ |
| Status | `saveStatus` (`Cockpit.vue:339-341`) | "Saved." (`:503`) |
| Scenario | `saveScenario` (`:343-345`) | none |
| Grade Point period | `savePeriod` (`:347-349`) | none |
| Identity | `saveIdentity` (`:383-415`) | "Saved." (`:651`) |
| Correction | `saveCorrection` (`:277-288`) | "Correction saved." (`:820-822`, ruling R3-04) |

All five post with `preserveScroll: true`, so without a success line a Trainer who presses
"Change scenario" gets no answer at all unless they notice the select re-render. R3-04 already
fixed this for the correction on exactly this reasoning — "a Trainer could not tell a saved
correction from a dead button" — and the same argument covers the two forms that were left out.

There is no dirty indicator anywhere on the screen. That is defensible for the three single-field
selects (each is an explicit submit of one value) but weaker for the identity form: eleven inputs
— rarity, potential level, five growth percentages, four ceilings — behind one "Save identity"
button, with no indication of which fields differ from what the run holds. An empty ceiling field
is a *clear*, not a no-op (`Cockpit.vue:397-403`), which raises the cost of an unexamined submit.

### 2.5 The accent is spent twice, on two different things (a proposal, not a defect)

`NextDecision.vue:130` wraps the whole decision card in `border-2 border-pick-line`.
`ActionGrid.vue:47` gives the single `RECOMMENDED` entry the same `border-2 border-pick-line`.

`ActionGrid.vue:5-7` states the intent: "the count is Von Restorff's one accent, not an accent per
card". One accent, spent twice — on a rail card that is *entirely* the recommendation, and on a
grid entry that *is* the recommendation — means the border no longer discriminates. The rail
already distinguishes itself by position, heading and the `bg-chrome` CTA
(`NextDecision.vue:218-223`); the grid entry needs the border more.

This is not a defect against a written rule — no section pins the accent to exactly one element —
but it is the cheapest hierarchy win on the screen and it is one class string.

### 2.6 The same two scenario facts print twice, far apart on the page

`CareerHeader.vue:141-149` prints Team Rank and Spirit Bursts through `ResourceStrip`.
`UnityCupPanel.vue` prints a Team Rank section ("Rank not recorded…") and a Spirit section
("Current Spirit / N/A"). The screenshot shows both pairs.

The data cannot disagree: `CockpitController.php:143` builds the team section once — "Built once:
the header's rank and the scenario section's team panel read the same pass." So this is not the
defect the 2026-10-08 ruling closed. It is a density cost: two absences stated twice, in different
words, at opposite ends of the page, and the header's wording ("N/A / not yet recorded") differs
from the panel's ("Rank not recorded: the league letter is read off the client, and this run has
none entered yet").

Worth an owner call, not an agent fix: the header strip exists so a Trainer mid-career can read
position without scrolling, and the panel exists because the scenario owns the mechanic. §5
carries the question.

### 2.7 The identity props interface declares `caps` twice

`Cockpit.vue:120-128` declares `caps: Record<string, { entered: number | null; scenario: number }>;`
twice in the same interface, with the docblock intent split across the two. TypeScript merges
identical members, so nothing breaks; it is a copy-paste artifact from the `ADR-0025` pass. Two
locations, one fix, no behaviour.

### 2.8 A page-level comment contradicts the page's own query-string state

`Cockpit.vue:12-14`: "the only query-string state here is none at all." `Cockpit.vue:245-252`
then implements `?edit_turn=<id>` for the arbitrary-turn correction, and the selector navigates
it. The comment predates ruling 2 of plan §9.6. Stale comments are the cheapest source of a wrong
future decision, so this is worth one line when the file is next touched.

### 2.9 The contrast gap is in the *contract*, not in a measurement

`tests/Feature/DesignTokensTest.php:532-602` is the G-18 browser gate, and it is real now: the
`beforeEach` at `:472-487` skips only when the Playwright driver is absent, and otherwise restores
Vite and reads resolved custom properties off `documentElement` in both themes. The pair list at
`:540-546` is:

```
ink on panel, ink on raised, ink-muted on sunken, ink on tint-speed, ink on tint-stamina,
ink on tint-power, ink on tint-guts, ink on tint-wit, ink on tint-sp, ink on green-tint,
ink-strong on raised, on-chrome on chrome, on-pick on pick, on-pick on green, up on panel,
down on raised, goal on raised, goal-line on raised, green-deep on sunken
```

Exactly one pair carries `ink-muted`, against `sunken`. This screen's muted text sits on
`bg-raised` (`CareerStatePanel.vue:155` labels inside raised cells; `CareerStatePanel.vue:149`
absence prose under a raised row) and on `bg-panel` (every panel's own sub-copy). Neither pairing
is in the contract, so a regression to `ink-muted`'s value would not fail the gate even though
this screen is its heaviest consumer.

Hand arithmetic on the current hexes (`DESIGN.md:113-117`) clears 4.5:1 on all four combinations:
light `#6E6459` on `raised` `#FFFFFF` about 5.8:1, light on `panel` `#F8F8FB` about 5.5:1, dark
`#AAABB5` on `raised` `#24262A` about 6.8:1, dark on `panel` `#121013` about 8.5:1. **Those figures
are hand-computed and are not gate evidence.** The right change is two rows in
`g18ContractPairs()` — `ink-muted on raised`, `ink-muted on panel` — which moves the count from
19 and moves `DesignTokensTest.php:538`'s `toHaveCount(19)` to 21, together with the key order at
`:540-546`. It does **not** touch `DESIGN.md:145`'s "20 of the 60 declared roles", which counts
colour *roles*, not contrast *pairs* — and that line already disagrees with the test, which pins
**59** tokens (`:253`, `:493`) against DESIGN.md's 60. Then the gate answers §2.2's question about
`text-xs` with a number.

## 3. Proposed changes

Ordered by value against risk. Each names what must not break.

### 3.1 Restore the type scale — page title `text-2xl`, section headers `text-lg`

`DESIGN.md:188-190` already specifies this; nothing needs a ruling.

- `AppLayout.vue:281` — `text-base` to `text-2xl`, and check `font-600` against the shipped
  `font-semibold`.
- The six `<h2>` headings listed in §2.1 — `text-base` to `text-lg`.
- Stop printing the trainee name twice. The header needs the Japanese name and card title, so the
  cheaper direction is to keep `#title` as the trainee and reduce the header's `h2` to the
  identity line it actually adds.

**Must not break:** `CareerLayout.vue:32` forwards `#title`, so the change is in `AppLayout` and
is felt by every 2.0 screen, not just this one. `tests/browser/career-cockpit.spec.ts` scans this
screen with axe (`:180`, `:260`); `tests/browser/accessibility.spec.ts` covers the other 2.0
screens but does not visit the Cockpit, because it needs a run. Level 2 verification:
`career-cockpit.spec.ts` plus `SCREEN_SPEC.md` §SCR-CAR-011's state table.

### 3.2 Make the advisor column sticky at `lg`, with a scrollable region when it overflows

`Cockpit.vue:850` is the advisor's column div. `lg:items-start` is already set at `:702`, so the
column will not stretch. The change is `lg:sticky lg:top-4` plus a max-height and an inner
`overflow-y-auto`.

Two constraints, both from this repository's own measured rules rather than from taste:

1. **A scrollable region needs the focusable-container treatment.** `DESIGN.md:209-217` requires
   an `overflow-x-auto` region to carry `role="region"`, `tabindex="0"` and an `aria-label` so it
   is reachable by keyboard, not trackpad alone. A vertical overflow box a keyboard user cannot
   scroll is the same failure one axis over.
2. **The rail is already taller than the viewport on a populated run.** Objectives, priority,
   recommendation, benefit lines, the G4 dual readout, the CTA, alternatives, `why`, `switch_if`
   and the recorded-action card stack well past a screen (`NextDecision.vue:129-272`). Sticky
   without an inner scroll would clip the recorded-action card permanently.

The `bg-chrome` "Go to Rest" CTA at `NextDecision.vue:218-223` is already a filled primary
pill; the suggestion to give Rest a clear primary treatment is met, and making the rail sticky is
what stops that pill scrolling away.

**Must not break:** mobile order (`SCREEN_SPEC.md:1843`), the `aria-live="polite"` on the priority
line (`NextDecision.vue:168`), and the `#action-recommended` fragment target
(`ActionGrid.vue:41`), which the rail's CTA depends on for scroll-and-focus. A sticky ancestor
changes where `scrollIntoView` lands, so the existing `scroll-margin-top` needs re-checking.

### 3.3 Give the header edit strip a visible heading and collapse it behind an explicit mode

`Cockpit.vue:482-689`. Today the `<h3>` is `sr-only` ("Edit the run's identity") and the `<ul>`
renders six always-open groups (`:485`, `:507`, `:529`, `:560`, `:655`, `:676`). The proposal:

- A visible heading, "Run identity", at the section-header step.
- A single "Edit identity" disclosure (native `<details>`, the pattern already used at `:731`)
  that opens status, scenario, period and the identity form together.
- Export (CSV, JSON) and the deck link move out of the disclosure: they are reads, not writes, and
  they are the two things a Trainer wants without entering an edit mode.
- A "Saved." line on the scenario and period forms, matching `:503` and `:651` — the R3-04 fix
  extended, not a new pattern.
- An unsaved-changes marker on the identity form only, driven by `identityForm.isDirty`, since
  that is the one form where an unexamined submit has a non-no-op consequence (an empty ceiling
  clears it, `:397-403`).

**Must not break:** the four forms' hidden sibling fields (`:319-337`, `:360-381`) — a partial edit
must keep blanking nothing. The `name` attributes and ids (`status`, `scenario`,
`current_objective_index`, `trainee_rarity`, `potential_level`) are referenced by
`CareerCockpitTest`; renaming them is a test change and the test is named in the same commit. No
new endpoint: all four already write through `runs.update`.

### 3.4 Action grid: spend the accent once, and give the entries the section's size step

`ActionGrid.vue`. The seven entries already carry a label, a note and a live href; the proposal
adds no information, only weight:

- Entry label `text-sm` to `text-base`, note `text-xs` to `text-sm` (§2.2's body step).
- Keep the star glyph plus the word `RECOMMENDED` (`:51-53`).
- Move the accent: the grid entry keeps `border-2 border-pick-line`, and `NextDecision.vue:130`
  drops to `border border-rule` plus its existing position and heading. That restores the one
  accent the grid's own comment asks for (§2.5).
- Do **not** add icons without going through `DESIGN.md` §6. `NavGlyph.vue:3-21` documents the
  gate: marks are inline paths on one stroke and one grid, filtered by the lore gate's allowed
  motif list. Seven new action marks are a `NavGlyph` extension plus a lore pass, not a style
  change. Feasible, but it is its own slice.

**Must not break:** `#action-recommended` and `tabindex="-1"` (`:41-43`) — the rail's CTA targets
them. Exactly one entry marked, or none when the advisor declines (`SCREEN_SPEC.md:1843`,
`ActionGrid.vue:5-7`). Every entry stays a focusable link (`:9-16`).

### 3.5 Scenario region: consistent panel headers, shorter lead copy, nothing hidden that §5 requires

`ScenarioPanel.vue` composes the panels; `UnityCupPanel.vue` is Unity Cup's own.

- Sub-panel headings to one step so seven panels read as siblings under the region's `h2`.
- The publisher-conflict `AlertRow` at `UnityCupPanel.vue:300` and the methodology paragraphs at
  `:304`, `:333` are the longest always-open copy on the page. These are candidates for a
  `<details>` whose summary states the claim — "Three parts of this table are publisher conflicts"
  is already the summary sentence. **The `AlertRow` itself stays open**: it is a warning, and a
  collapsed warning is not a warning.
- `ProvenanceBadge` `estimated` at `:204`, `:266` and `ScenarioStatusBadge` at
  `ScenarioPanel.vue:158` stay as they are; they are the vocabulary (§1.3).
- The two tables (`ScenarioPanel.vue:230`, `UnityCupPanel.vue:275`) get the
  `overflow-x-auto` plus focusable-container treatment from `DESIGN.md:209-217` if they overflow
  at any supported width, rather than a card reflow.

**Must not break:** gate G-33 — no scenario name may appear in a component; the shell switches on
matrix flags only (`Cockpit.vue:898-900`). A fifth scenario must reach every one of these changes
through config with no edit here.

### 3.6 An anchor section nav, not tabs

A `<nav aria-label="On this page">` with links to the regions, sticky under the career bar.
Every target heading already has a stable id: `career-header-heading`, `cockpit-header-edit`,
`career-state-heading`, `career-actions-heading`, `career-scenario-heading`,
`career-timeline-heading`, `career-advisor-heading`.

- Needs `scroll-margin-top` on the targets; `app.css` already sets `scroll-padding-bottom` for the
  mobile bottom bar, so this extends an existing rule rather than adding one.
- A skip link to it would also satisfy SC 2.4.1 for the page as a whole.
- **Not tabs.** `SCREEN_SPEC.md:1841` forbids hiding elements; tabs hide panels by definition.

### 3.7 Extend the G-18 pair list by two rows

`tests/Feature/DesignTokensTest.php:317-349` (`g18ContractPairs`) plus `:538-546` (the pinned key
order). Add `ink-muted on raised` and `ink-muted on panel`. Then `DESIGN.md:99-108` needs a dated
note: it still reads as though the browser gate were `markTestIncomplete`, which is stale — the
gate is implemented and runs wherever Playwright is installed.

This is the smallest change in §3 and the only one that converts a hand-computed claim into
evidence. It also settles §2.2: once the pairs are pinned, whether `text-xs` muted copy clears
4.5:1 is a gate result, not an opinion.

## 4. What a later pass must not do

1. Move Energy, Mood, Fans or Skill Points out of `CareerStatePanel` — owner ruling 2026-10-08,
   `SCREEN_SPEC.md:1858-1865`, guarded by `EnergyReadingConsistencyTest`.
2. Add a score, a percentage, a confidence or a risk to the rail — `ADR-0001`'s "What this does
   not license", `ADR-0020` §2, `SCREEN_SPEC.md:1869` and `:1880-1881`.
3. Mark an action grid entry unavailable, or make the grid a selection control —
   `ActionGrid.vue:9-16`.
4. Collapse the rail's `why` list, or the `switch_if` list, out of visible text —
   `SCREEN_SPEC.md:1886` (ruling 5a).
5. Introduce a fifth provenance label, or chips named `Recorded` / `Missing` —
   `DESIGN.md:607-614`, `docs/proposals/provenance-and-absence-contracts.md` §9.
6. Hide any region at any breakpoint — `SCREEN_SPEC.md:1841`.
7. Add a skeleton or a cold-load spinner, or give a cold load a custom loading state — ADR-0007
   reserves custom loading for user-initiated async. `Cockpit.vue:463-466`'s `role="status"` and
   `role="alert"` lines are the whole loading story the page needs, and §3.3's save feedback stays
   inside the same line: user-initiated writes get feedback, page loads do not get skeletons.
8. Put a scenario name into a component — gate G-33, `config/scenarios.php` is the only source.

## 5. Questions for the owner

1. **§2.6 — the header strip vs the scenario panel.** Team Rank and Spirit Bursts are printed
   twice on one screen from one payload, in different words. Which copy is the run's statement of
   record, and should the header strip keep its own wording or adopt the panel's?
2. **§2.1 — the duplicate trainee name.** Keep the trainee in the `#title` slot and reduce the
   header `h2`, or the reverse? The second carries the Japanese name and card title, so the two
   options are not symmetric.
3. **§3.2 — the sticky rail's inner scroll.** A sticky rail taller than the viewport needs a
   scrollable region with `role="region"` and `tabindex="0"`, which adds a tab stop to the page.
   Accept that tab stop, or accept the rail clipping its lower third?
4. **§3.4 — action marks.** Seven new marks through `NavGlyph` would need a `DESIGN.md` §6 lore
   pass and are gated on motif, not taste. Worth its own slice, or leave the grid word-only?

## 6. Verification plan

| Change | Minimum |
| ------ | ------- |
| §3.1 type scale, §3.3 edit mode, §3.4 grid, §3.5 scenario region | the affected feature tests (`CareerCockpitTest`, `CockpitObjectivesTest`), then `tests/browser/career-cockpit.spec.ts` and `tests/browser/accessibility.spec.ts` |
| §3.2 sticky advisor | the same two browser specs, plus a keyboard pass: tab into the rail, scroll it with arrow keys, and confirm the `#action-recommended` fragment still lands focus |
| §3.7 G-18 pairs | `php artisan test --compact --filter=DesignTokensTest`, which is the evidence; hand arithmetic in §2.9 is not |
| anything touching `AppLayout` | the full suite, then the hand-off sequence in `AGENTS.md` §9 |

Nothing here touches a migration, a controller, a Form Request or the fetch pipeline, so no
schema, route or network gate is engaged. The lore gate runs as usual: this document is prose in
`docs/`, where a `lore-ignore-line` marker is valid but is not itself a ruling.

