# Career setup wizard — UI audit (SCR-CAR-002/003/005/008/009/010)

> **Reference only. Not authorization.** Nothing in this file is a decision, and no component,
> token, copy string or spec line was changed to produce it. Each section names the files an
> approval would touch and changes none of them.
>
> Recorded 2026-10-12 from the owner's screenshots of the six wizard steps and a read of each
> page's source. It is the proposal-side counterpart to `SCREEN_SPEC.md` §SCR-CAR-002, 003, 005,
> 008, 009 and 010, which stay the behavioural authority. The companion audit of the Cockpit is
> `docs/proposals/cockpit-ui-audit-2026-10-12.md`; the two share §4's vocabulary rules.
>
> The owner's improvement list is reconciled in §1 rather than adopted. Most of it is already
> shipped, and six items would reverse a landed decision — the sharpest being the drawn ancestry
> tree, which `LegacySelect.vue:14-16` refuses on WCAG grounds in its own docblock.

## 0. What governs this flow

Six screens, one shell, one draft. The parts that settle disputes below:

1. **`SetupLayout.vue` is the step bar and nothing else.** `AppLayout` plus
   `<nav aria-label="Setup steps">` (`SetupLayout.vue:54-80`). Its docblock records why the
   product sidebar stays: `SetupDraft` writes every step to the session as it is saved, so leaving
   mid-flow loses nothing, and "which step am I on" is answered by the step count and
   `aria-current="step"` rather than by hiding the way out (`:6-12`).
2. **The draft is session state, not a run.** `SetupDraft` holds every step; `training_runs` is
   written once, at Preflight (`routes/web.php:40-46`). A mid-flow screen never touches a run row.
3. **Gate G-33: no scenario name may appear in a component.** `ScenarioSelect.vue:5-8` states it
   plainly — the page "prints props and holds no `config()` lookup and no scenario-name branch",
   and `CareerScenarioSelectTest` proves a fifth scenario arrives with no edit here. Any change to
   card content has to reach it through `ScenarioSelectController`, not through a component.
4. **`ADR-0020` §3 applies harder here than on the Cockpit.** Four of the six screens are
   *record-only* surfaces: Legacy computes no inheritance, no expected stat, no Affinity payout,
   no Spark chance, no score and no ranking (`LegacySelect.vue:6-10`). BuildTarget refuses a risk
   tolerance and per-skill marks as stored-shape changes (`BuildTarget.vue:615-620`).
5. **`DESIGN.md:197-208` ratifies three widths**: `max-w-5xl` shell, `max-w-3xl` in-page form and
   long copy blocks, `max-w-lg` narrow form. §2.1 measures all six screens against that.

## 1. The owner's improvement list, reconciled

### 1.1 Already shipped — no work, or a correction to the suggestion

| Suggestion | Where it already is |
| ---------- | ------------------- |
| **2.1** searchable, filterable roster | `TraineeSelect.vue:252-326`. Search box, three aptitude facet selects (`:264-275`), a unique-skill select (`:277-289`), sort and order selects, Filter and Clear filters buttons. |
| **2.5** sort options | `TraineeSelect.vue:291-296`, driven by a `sortKeys` prop, so the key list is the controller's and not a component branch. |
| **2.7** pagination | `TraineeSelect.vue:447-464`: real pagination with `aria-current="page"` on the active link and a live count ("{{ total }} trainees match."). Virtualization would be a new dependency, barred by `AGENTS.md` §5 without owner approval. |
| **3.4** reachability only where computable | `BuildTarget.vue:438-442` states in prose "Whether a target is reachable in the turns left is not computed here", and each field prints `N/A` with its reason (`:475-478`). The bar that ships measures the entered number against the cap and nothing else (`:465-466`). |
| **3.5** reorderable skill priorities with explicit numbers | `BuildTarget.vue:569` renders an `<ol>`; `:541-544` states the contract: "Reorder with the buttons; no drag is required." |
| **3.2** compact target summary | `BuildTarget.vue:624-633` ships a live Summary with a `calculated` `ProvenanceBadge` whose title reads "assembled by this tool from the values entered on this form, not a stored fact". It sits at the bottom rather than the top; §3.3. |
| **5.6** disable irrelevant ownership controls | `DeckSelect.vue` writes to one slot at a time through the `filling` ref (`:115`), and `SupportSlot.vue:187` words the two options "Owned" and "Rented". |
| **5.7** Slot 6 distinction | `SupportSlot.vue:102` carries the `Friend slot` badge. |
| **5.8** analysis empty state | `DeckAnalysis.vue:58-60`: "Nothing to analyse yet. The analysis reads the effects the six cards state, so it is blank until the deck has at least one card in it. Fill a slot on the left and this fills in with it." |
| **6.2** empty / incomplete / saved / selected / warning states | Empty: `ScenarioSelect.vue:133-139`, `TraineeSelect.vue:345-351`, `DeckAnalysis.vue:58-60`. Incomplete: `Preflight.vue:175-193`. Saved: every write announces `role="status"` — `ScenarioSelect.vue:128`, `TraineeSelect.vue:343`, `BuildTarget.vue:643`, `LegacySelect.vue:651`, `DeckSelect.vue:290`, `Preflight.vue:433`. Selected: `aria-pressed` plus the word "Selected" / "Chosen" (`ScenarioSelect.vue:253,264-267`, `TraineeSelect.vue:428,442`). |
| **6.6** separate blocking errors from warnings | Already split, deliberately. `Preflight.vue:159-172` is a `role="alert"` refusal with `tabindex="-1"` that is focused on return, headed "The career was not created." The warnings beneath are a separate `<section>` whose own comment says "Warnings below never block; this does" (`:159-160`), and the deck warning's detail reads "A career can start either way." |

### 1.2 Supported by the governing docs — worth doing, cite the section that asks for it

| Suggestion | The governing line |
| ---------- | ------------------ |
| **Cross-page** consistent max-width container | `DESIGN.md:197-208` already ratified it. Measured: `BuildTarget.vue:336` uses `max-w-3xl` and complies; the other five constrain nothing. See §2.1. |
| **Cross-page** sticky step bar | `SetupLayout.vue:54-80` puts the step bar in document flow. The wizard is the longest flow in the app, and `design-2.0.md` §40 puts the same bar at the top of the mobile order. |
| **Cross-page** completed steps visibly distinct | `SetupLayout.vue:69` distinguishes only the current step. Every other step renders identically whether or not it was saved. See §2.2. |
| **Cross-page** persistent bottom action bar with Back | Each step already writes its own draft. Back exists on two steps of six, and Preflight's link row has a duplicate and two missing doors — see §2.3. |
| **Cross-page** helper text plus expandable "Learn more" | The house pattern is a native `<details>` whose summary carries the reason (`DESIGN.md:576-585`, `AbsenceValue.vue`), already used here at `LegacySelect.vue:402` and `:459`. |
| **Cross-page** muted-text contrast and minimum size | Same finding as the Cockpit §2.9: `ink-muted on raised` and `ink-muted on panel` are absent from the G-18 pair list (`tests/Feature/DesignTokensTest.php:540-546`), and this flow leans on both. |
| **Cross-page** semantic badges over colour | Already true where a badge exists: `PARTIALLY DOCUMENTED` is glyph plus word (`ScenarioSelect.vue:152-159`), warnings carry `!` plus title plus detail (`Preflight.vue:185-187`), `ProvenanceBadge` always pairs glyph and word. |
| **1.2** equal-height scenario cards | `ScenarioSelect.vue:141-147` is a CSS grid, so cards already share a row height. The screenshot reads unequal because the metadata block is optional (`:217` vs `:240`). |
| **1.3** stronger selected state | The border and the word are there (`ScenarioSelect.vue:146`, `:264-267`). `:260-263` explains why the word sits beside the action and not in the heading: inside the `<h2>` it would join the accessible name and "Trackblazer" would stop matching itself. Only a check icon is genuinely absent. |
| **1.4** collapsible "Scenario details" | The mechanics are the card's sourced content (`ScenarioSelect.vue:233-238`) and `design-2.0.md` §25 names key mechanics as what the card exists to show. Defensible to collapse the `key_mechanics` list; not the fact grid. |
| **3.1** grouped two-column target sections | `BuildTarget.vue:337-340` already groups "What the career is for" into a `sm:grid-cols-2` fieldset. The ceilings fieldset (`:507-535`) is the one that would gain most. |
| **3.6** per-field validation | Already done: every target and ceiling field renders its own `role="alert"` (`BuildTarget.vue:481-486`, `:528-533`) with a per-fieldset fallback (`:490-495`) and `describedBy` wiring (`:459`, `:521`). |
| **4.2** collapse parent branches | `LegacySelect.vue:402` and `:459` already use `<details>` for the per-grandparent spark editors. Extending that to a whole branch is the same pattern. |
| **5.2** slot state badges | `SupportSlot.vue` carries the friend badge and words the ownership actions. "Empty" is a named absence with a reason rather than a badge (`:107`), which is `DESIGN.md:576-585`'s rule for an absence. |
| **5.5** card effects inside each slot | `DeckSelect.vue:89-102` and `DeckAnalysis.vue` already read `SupportCardEffects` anchors; the per-slot surface is `SupportSlot.vue`'s card block. |
| **6.1** readiness summary | `Preflight.vue:195-201` already prints the inverse — "No warnings. Every section is filled…" — when the list is empty. A count line is an addition to the non-empty branch, not a new pattern. |

### 1.3 Conflicts with a binding decision — do not implement as written

| Suggestion | Why it stops |
| ---------- | ------------ |
| **4.1** visual two-branch ancestry tree | Refused in the file itself. `LegacySelect.vue:14-16`: "the nodes are a nested list rather than a drawn tree, because a tree drawn as a picture is unreadable to a screen reader and unreachable by keyboard, and the ancestry assignment is a labelled `<select>` with no dragging path at all (WCAG 2.2 SC 2.5.7)." The nested `<ol>` at `:296-330` is the accessibility mechanism, not a styling shortfall. |
| **6.7** disable "Start Career" when warnings exist | `Preflight.vue:159-160` and the deck warning's own detail record that warnings never block. Disabling on a warning would refuse a career the server accepts. The blocking path already exists: a refused write returns a focused `role="alert"` (`Preflight.vue:127-137`, `:161-172`). |
| **1.5** "Recommended" label for a fitting scenario | No source for a recommendation, and the card already names that absence. `ScenarioSelect.vue:198-200`: "Neither the corpus nor `config/scenarios.php` holds either, so they are named absences: a sentence written here would be an invented fact (`AGENTS.md` §5)." The same argument covers a fit label: the wizard asks for the scenario *first*, before the target a fit would be measured against. |
| **1.6** make the whole card clickable | `ScenarioSelect.vue:10-14` records the inverse: the card-button pattern is a native `<button>` with `aria-pressed` precisely because it "is in the tab order, needs no arrow-key handler to be reachable, and states which card holds the stored choice". A second click target gives one action two paths and two accessible names. |
| **4.4** "Add grandparent" instead of blank fields | The four grandparents are the record's fixed shape — six nodes, `AncestryGraph::build()`'s own node set (`LegacySelect.vue:12-13`). A blank grandparent is a stored *absence*, not an empty row; hiding it would remove the surface where a Trainer records a name she reads later. |
| **6.8** final confirmation dialog | The house pattern for a confirm step is a native `<details>` with an explicit submit inside it — `Cockpit.vue:943-966` (delete-career), `:828-845` (delete-turn). A modal adds a focus-trap surface the flow does not otherwise need, and Preflight is already the summary such a dialog would restate. |
| **2.3 / 2.4** scenario fit, "+N more" on trait chips | "Scenario fit" is the same unsourced recommendation as 1.5. "+N more" is worth doing but changes `AptitudeBadge`'s contract: every letter currently prints its word (`TraineeSelect.vue:373-387`), and `design-2.0.md` §17 plus `DESIGN.md` §3.1 require letter *and* word so nothing is colour-only. A "+N" collapse must not hide letters. |

## 2. Findings

### 2.1 Five of six screens ignore the ratified form width

`DESIGN.md:208`: "Ratified widths, in one place: 5xl shell, 3xl form, lg narrow form." Measured
across the flow:

| Screen | Content width |
| ------ | ------------- |
| `BuildTarget.vue:336` | `max-w-3xl` — compliant |
| `LegacySelect.vue:296` | none; the `<ol>` runs to the shell edge |
| `DeckSelect.vue:246` | none; `grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]` full bleed |
| `ScenarioSelect.vue:141` | none; `grid-cols-1 xl:grid-cols-2` full bleed |
| `TraineeSelect.vue:353` | none; `space-y-4` full bleed |
| `Preflight.vue:206,318,360` | none; three full-bleed `dl` grids |

`AppLayout.vue:295` is `flex-1 px-6 py-6` with no max-width, so the shell does not supply one.
This is the owner's "unused horizontal space" observation, and it is a variance from a ratified
number rather than a taste call. The scenario cards and the trainee rows read worst: their `dl`
and chip rows stretch to the full shell width, which is why "Optimizes / Systems / Tracked
resources" in the screenshot carry a very wide gutter between term and value.

### 2.2 The step bar cannot tell a saved step from an unsaved one

`SetupLayout.vue:69` applies `stepCurrent` only when `index + 1 === props.step` and nothing
otherwise. On step 4, steps 1–3 render identically to steps 5–6: the same `stepLink` class, the
same weight, no mark. The wizard *does* know which steps are saved — `SetupDraft` writes each one
to the session as it lands (`SetupLayout.vue:10-11`) — but no screen passes that back into the
bar, so the bar shows position and nothing else.

The cost is that "have I done this one yet?" is answered by clicking, not by looking, and the
Forward-only navigation means the only way back is a step bar that gives no feedback about where
you have been.

### 2.3 The step bar is not sticky, and Back is present on two steps of six

`SetupLayout.vue:54-80` puts the bar at the top of document flow. `AppLayout.vue:295` pins
nothing. So on Legacy — the longest screen in the app, with two parent branches, four grandparent
cards and eight spark editors — reaching the step bar or the "Leave setup" link requires
scrolling to the very top.

Back, measured across the six steps:

| Step | Back to the previous step |
| ---- | ------------------------- |
| Scenario (`ScenarioSelect.vue`) | none — it is step 1 |
| Trainee (`TraineeSelect.vue:238-243`) | "Change scenario", which is step 1 and happens to be the predecessor |
| Target (`BuildTarget.vue:317`) | "Change scenario" only; no Back to Trainee |
| Legacy (`LegacySelect.vue:654-657`) | none; "Next: Support deck" is the only link out |
| Deck (`DeckSelect.vue:422-431`) | "Back: Legacy" — present |
| Preflight (`Preflight.vue:402-425`) | "Back" — present, but see below |

Every step writes to the draft, so Back is pure navigation and risks nothing. Steps 3 and 4 are
the two longest forms on the flow and neither offers a way back.

Two related defects sit in Preflight's own link row (`:401-425`):

- **"Back" and "Edit Deck" are the same link.** `:403` and `:415` both bind `props.edit.deck`, so
  two of the four links go to the same place with different words.
- **Two steps have no door at all.** The row offers Edit Legacy, Edit Deck and Edit Target —
  there is no Edit Scenario and no Edit Trainee, so a Trainer who reaches Preflight and sees the
  wrong trainee has to use the step bar (which only scrolls, it does not say what is stored) or
  leave and restart.

### 2.4 The trainee row carries ten aptitude badges at full weight, on every row

`TraineeSelect.vue:150-161` declares `aptitudeCells` as a static ten-entry list — Turf, Dirt,
Sprint, Mile, Medium, Long, Front runner, Pace chaser, Late surger, End closer — and
`:380-387` renders one `AptitudeBadge` per entry. Ten letter-plus-word chips wrap onto two lines
per row, repeated on every row of the page, in the screenshot reading as a wall of chips that
outweighs each trainee's name.

`design-2.0.md` §17 and `DESIGN.md` §3.1 require letter **and** word so nothing is colour-only,
so the chips cannot be thinned by dropping words. What is not required is that all ten carry
equal weight on a *selection* screen, whose question is "is this the trainee I want?" and not
"what are her aptitudes?" — the latter is `TraineeProfile.vue`, one click away at
`TraineeSelect.vue:434-439`. This is the strongest density finding on the flow.

One code note travelling with it: `aptitudeCells` is a component-local constant, so it is a
second declaration of an axis list the catalog already owns. `ADR-0004`'s element order and
`CatalogController::aptitudes()` are the server's reading of the same axes
(`TraineeSelect.vue:373-376` says as much). A tenth cell added to the schema would not arrive
here without an edit, which is the same class of drift G-33 was written to prevent on the
scenario side.

### 2.5 `BuildTarget`'s fieldset legends and Summary share one size, so the Summary reads as a field group

`BuildTarget.vue:338,436,499,539` set every `<legend>` at `text-sm font-semibold`. The Summary
heading at `:625` is also `text-sm font-semibold`, so the readout of the whole form — the one
element that should rank above its own fieldsets — is visually identical to four field-group
labels. It also sits last, after the skill-priority fieldset, where a summary is least useful.

### 2.6 Several absences still use a `title`-only reason

`DESIGN.md:576-585` retires `N/A` carrying only a `title` as the shipped idiom, because "a
`title` is reachable with a mouse and by nothing else". The migration is partial and named as
partial in `SCREEN_SPEC.md`. On this flow the remaining `title`-only absences are:

- `ScenarioSelect.vue:115-116,166,171,178,185,190,195,203,209,243` — the whole fact grid.
- `TraineeSelect.vue:235,249,377-379`.
- `BuildTarget.vue:477` (the entered-target readout) and `:524` (the scenario-publishes note).
- `LegacySelect.vue:8-9` — the Spark chance, which is the screen's headline absence.
- `Preflight.vue:218` and the other `N/A` cells in the three review grids.

Each is a mouse-only reason on a screen whose whole job is to state what has not been recorded.
`AbsenceValue.vue` is the replacement and the pattern is already in use elsewhere.

### 2.7 Legacy's form is full-bleed while its copy is `max-w-2xl`

`LegacySelect.vue:235` constrains the intro paragraph to `max-w-2xl`. The form that follows
(`:296` onward) has no width at all, so the explanatory copy reads as a column and the fields as
a wall. `BuildTarget.vue` gets this right by constraining the form itself (`:336`).

### 2.8 Preflight's three review grids use three different column counts for the same kind of data

`Preflight.vue:206` is `sm:grid-cols-2` (Build), `:318` is `sm:grid-cols-2` (Support deck),
`:360` is `sm:grid-cols-3` (Target). Same `dl`-of-cards shape, same `dt`/`dd` treatment, three
column counts with no stated reason, so the Target block reflows differently from the two above
it at the same viewport.

## 3. Proposed changes

Ordered by value against risk.

### 3.1 Constrain the five unconstrained screens to the ratified width

`DESIGN.md:208`. `BuildTarget.vue:336` is the template: a `max-w-3xl` on the form element
itself, so the copy and the controls share a measure.

- `ScenarioSelect.vue:141` — the `<ul>` gets `max-w-3xl` (or `max-w-5xl` if two columns are
  wanted; §2.1's complaint is the gutter, so a narrower two-column grid beats a wider one).
- `TraineeSelect.vue:353`, `LegacySelect.vue:296`, `Preflight.vue`'s three grids — `max-w-3xl`.
- `DeckSelect.vue:246` keeps its two-column grid, which is a genuine workspace split, but the
  container gets `max-w-5xl`.

**Must not break:** the deck grid's `minmax(0,2fr)` tracks, which prevent a long card name from
blowing the layout; keep them inside whatever container is chosen.

### 3.2 Make the step bar sticky, add Back, and mark completed steps

`SetupLayout.vue`. One component, all three changes:

- `sticky top-0 z-10 bg-page` on the `<nav>`, with a bottom border already present (`:54`).
  Check against `app.css`'s `scroll-padding-top` for the mobile bottom bar so a focused control
  is not covered by either bar.
- Completed-step state: the step bar needs a `completed: number[]` (or similar) prop fed from
  the draft, so `index + 1 < props.step && completed.includes(index + 1)` gets a distinct class.
  The data is already in `SetupDraft`; the change is a controller prop, not a new query. The
  current step keeps `aria-current="step"`; a completed step should not get `aria-current`,
  which is reserved for the current position in a set.
- Back on the two steps that lack one, Target and Legacy, matching the "Back: Legacy" pattern
  DeckSelect already uses (`DeckSelect.vue:422-425`): a bordered link to the previous step's
  route, before the submit. Navigation only, nothing writing. (Trainee's "Change scenario" and
  Target's "Change scenario" both point at step 1 rather than the previous step; leave them as
  they are and add the Back link beside them, or repoint — an owner call, §5.)

**Must not break:** `aria-current="step"` on exactly one item (`SetupLayout.vue:70`); the
`sr-only` duplicate count line (`:62`); and the `to: null` branch (`:74-77`), which the docblock
records as the honest answer for a step whose slice has not landed.

### 3.3 Move BuildTarget's Summary above the fieldsets

`BuildTarget.vue:624-633` relocated to sit immediately under the intro paragraph (`:303`), and
raised one step to `text-base` so it ranks above the four `text-sm` legends. It is a live
readout with a `calculated` badge; it belongs where a Trainer checks it, which is before and
during entry, not after four fieldsets.

**Must not break:** the `calculated` badge and its title (`:628-631`) — the summary is tool
arithmetic over entered values and must keep saying so.

### 3.4 Subordinate the aptitude chips on the selection screen

`TraineeSelect.vue:380-387`. Options, in ascending order of invasiveness:

1. Wrap the chip block so the track and distance axes read at full weight and the style axis sits
   behind a "Show all aptitudes" `<details>`, with the summary carrying the count. The letters
   and words all stay in the DOM; nothing becomes colour-only.
2. Keep all ten visible but at `text-xs` inside the chip, which is a density fix and not a
   contract change.
3. Leave the row alone and put the decision weight on the name and the actions, which are already
   the row's landmarks.

Option 1 is the one that answers the owner's "+N more" intent without touching
`AptitudeBadge`'s letter-and-word contract. It also gives `aptitudeCells` (§2.4) a reason to be
derived from the controller rather than declared locally, since the split by axis is server
knowledge.

**Must not break:** letter **and** word on every badge that renders (`design-2.0.md` §17,
`DESIGN.md` §3.1). No badge may collapse to a colour or to a bare letter.

### 3.5 Finish the `AbsenceValue` migration on this flow

Six screens, all listed in §2.6. The pattern is `DESIGN.md:576-585`: the marker is a native
`<details>` whose summary carries the reason, so it is one statement in two media.

Two measured constraints travel with it, from the same section:

- The summary keeps its disclosure triangle only while its display stays `list-item`;
  `inline-flex`, `block` and `inline-block` each paint no marker. So the component's own classes
  are not optional.
- `<details>` is flow content and belongs in a field or cell container (`dd`, `div`, `td`, `li`),
  not inline in a `<p>` or a `<span`. Several of the `N/A`s above sit inside `<dd>` already
  (`ScenarioSelect.vue:166` onward), which is the compliant shape; the ones inside prose
  (`TraineeSelect.vue:378`) need the sentence restructured into a row, which is a small design
  change per site.

**Must not break:** `Preflight.vue`'s review grids are a *summary* surface, not an entry surface;
check whether `SCREEN_SPEC.md` §SCR-CAR-010 pins their wording before changing any of them.

### 3.6 Collapse the scenario card's `key_mechanics` list

`ScenarioSelect.vue:233-238`, behind a `<details>` whose summary states the count ("Key
mechanics (3)"). The fact grid (`:162-213`) stays open — it is the comparison the card exists
for, and `design-2.0.md` §25's "should not become" list is about scenario-specific sprawl, not
about the facts.

**Must not break:** G-33. The count comes from `card.metadata.key_mechanics.length`, a prop, not
from any lookup in the component.

### 3.7 One column count for Preflight's three review grids

`Preflight.vue:206,318,360` to a single value. `sm:grid-cols-2` is the majority and the Target
block's five stat cells divide by two more awkwardly than by three only if the cells are narrow;
at the ratified `max-w-3xl` (§3.1) two columns is the safer choice.

**Must not break:** the deck grid's `<ol>` semantics (`:318`) — it is an ordered list of slots and
must stay one.

### 3.8 Fix Preflight's link row

`Preflight.vue:401-425`, the two defects from §2.3:

- The "Back" link (`:402-407`) and the "Edit Deck" link (`:414-419`) both bind
  `props.edit.deck`. Drop one; "Edit Deck" is the more honest name for a link that goes to step 5
  to change a value, so "Back" should either go or point somewhere "Edit Deck" does not.
- Add "Edit Scenario" and "Edit Trainee" so all five earlier steps have a door, or state in the
  row why two do not. `props.edit` already carries the routes it would need
  (`:403,409,415,421`), so this is a prop-list addition in `PreflightController`, not a new
  endpoint.

**Must not break:** `Preflight.vue:5-8`'s contract — "Every fact on this page is read from the
draft the five earlier steps filled… so Back and Edit round trips are lossless by construction".
The Edit links navigate to a step that opens on the draft; adding two more keeps that true.

## 4. What a later pass must not do

1. Draw the ancestry as a picture, or add a drag path — `LegacySelect.vue:14-16`, WCAG 2.2
   SC 2.5.7.
2. Disable or relabel "Start Career" because warnings exist — `Preflight.vue:159-160`; warnings
   never block and a career can start with them.
3. Add a scenario recommendation, a "fit" score, or a fifth purpose — `ScenarioSelect.vue:198-200`,
   `BuildTarget.vue:430` ("a fifth purpose, Competitive Build, which this tool does not record
   yet").
4. Add a risk tolerance or per-skill marks — `BuildTarget.vue:615-620`, a stored-shape change on
   `BuildTargetPayload::KEYS`.
5. Hide a grandparent node, or turn the six-node graph into a free-form tree —
   `LegacySelect.vue:12-16`.
6. Drop the word from an aptitude badge, or colour-code one — `design-2.0.md` §17,
   `DESIGN.md` §3.1.
7. Put a scenario name into a component — gate G-33.
8. Add a dependency (virtualization, an icon set, a modal library) — `AGENTS.md` §5 requires
   human approval.
9. Hide a step or a screen at a breakpoint — `SCREEN_SPEC.md:1841` states the same rule the
   Cockpit answers to.

## 5. Questions for the owner

1. **§3.2 — what marks a completed step?** The draft knows which steps were written, but a
   Trainer can also visit a step and leave without saving. Should "completed" mean *written to the
   draft* (precise, invisible to the user's intent) or *current or previously visited* (approximate
   but honest about the visit)? The two disagree on a Trainer who opens step 2 and leaves.
2. **§3.4 — how much aptitude belongs on a selection screen?** All ten letters are the
   record; the screen's question is narrower. Subordinate, or leave at full weight and let the
   profile screen carry the detail?
3. **§3.1 — `max-w-3xl` or `max-w-5xl` for the scenario cards?** Two columns at `3xl` are
   cramped for a card with an eight-row fact grid; two columns at `5xl` leave the gutter the
   owner objected to. One column at `3xl` is the third option and the least like the screenshot.
4. **§2.3 — should the step bar stay visible on mobile?** `AppLayout.vue:183-187` already carries
   a bottom navigation bar below `md`. A sticky step bar plus that bar is two pinned strips; the
   alternative is sticky at `md` and up only.
5. **§2.3 / §3.8 — what does "Back" mean when the previous step is not the one you want?**
   Preflight's Edit row offers Legacy, Deck and Target but not Scenario or Trainee, so the two
   choices a Trainer most often wants to change are the two with no door. Is the right fix to add
   the two missing Edit links, or to make the step bar carry each step's stored value so the bar
   answers "is this the right trainee?" without a round trip?
6. **§3.2 — where does Back go on Trainee and Target?** Both already carry a "Change scenario"
   link that points at step 1 rather than the previous step. Add a Back beside it, or repoint it?

## 6. Verification plan

| Change | Minimum |
| ------ | ------- |
| §3.1 width, §3.5 absence migration, §3.6, §3.7 | the affected feature tests (`CareerScenarioSelectTest`, the trainee, target, legacy, deck and preflight suites), then `tests/browser/accessibility.spec.ts` |
| §3.2 sticky bar and Back | the same accessibility spec, plus a keyboard pass: tab through the sticky bar, confirm it does not cover a focused control at the bottom of a long Legacy form, and confirm `aria-current="step"` is on exactly one item |
| §3.3 Summary relocation | `BuildTarget`'s own feature tests; the Summary is asserted by its heading id `target-summary-heading` (`BuildTarget.vue:625`) |
| §3.8 Preflight link row | the Preflight feature tests plus a link-count assertion — two of the four links currently resolve to the same href |
| §3.4 aptitude subordination | `TraineeSelect`'s feature tests plus an assertion that every rendered badge still carries both its letter and its word |
| anything touching `SetupLayout` | the full suite — every one of the six screens mounts it |

Nothing here touches a migration, a controller write path, the fetch pipeline or
`config/scenarios.php`. The lore gate runs as usual: this document is prose in `docs/`, where a
`lore-ignore-line` marker is valid but is not itself a ruling.

