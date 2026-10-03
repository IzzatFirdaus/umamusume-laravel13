# UI/UX audit: training-run creation and the run page

**Method.** Hands-on. Every finding below was produced by driving the running app in a browser
(Playwright MCP, Chromium, 1280-wide viewport, `http://127.0.0.1:8000`) rather than by reading source
first. Source is read only to confirm a cause after an effect is observed. Evidence references are the
accessibility-tree snapshots and the DOM measurements taken during the pass.

**Design contract.** `DESIGN.md`. No `SCREEN_SPEC.md` and no `.design-qa/` artifacts exist in this repo,
so there is no per-screen layout target to compare against; visual-fidelity claims are limited to token
use, states and copy, which `DESIGN.md` does govern.

**Data entered.** A real run from `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md`: Rice Shower
[Rosy Dreams], Unity Cup, Active, notes citing the report. Created as run 7.

**Verdict.** Held until Part 2 completes. Severity vocabulary is `blocker`, `major`, `minor`, `debt`,
`info`, `needs-design-decision`.

---

## Part 1: `/training-runs/create`

The page renders a single form with four controls: a trainee/costume-card picker, a scenario select, a
status select and a notes textarea. It loads clean: no console errors, a "Skip to content" link, one `h1`
and a five-link nav.

### C-1 · major · the trainee picker does not clear its own text, so a second attempt types into the first

- **Evidence.** After committing a selection, the input read `Rice Shower · [Vampire Makeover!]`. Typing
  `Rosy` into it produced the value `Rice Shower · [Vampire Makeover!]Rosy`, the listbox emptied, and the
  status line read `No trainee or card found.` Only a replacing fill (select-all, or clearing the field)
  recovers.
- **Observed.** The committed label is left in the input as editable text with the caret at the end.
- **Expected.** Either select-all the text on focus, or clear on the first keystroke after a commit, so a
  Trainer who picks the wrong card and starts retyping lands on a filtered list instead of a no-match.
- **Impact.** The obvious recovery gesture, typing again, is the one that fails. The message that appears
  ("No trainee or card found") describes a search problem, not the actual state, so the Trainer is likely
  to keep retyping. This is the first screen of the primary flow and the picker is the only hard
  requirement to get through it.
- **Fix.** In `resources/js/trainee-combobox.ts`, on `focus` (or on the first `beforeinput` after a
  commit) call `select()` on the input, which is the standard combobox affordance and costs one line.
- **Verification.** Re-run: commit a card, type a new query without clearing, confirm the list filters.

**Fixed 2026-10-03, and the fix was not the one proposed here.** The focus handler already called
`input.select()` (`trainee-combobox.ts:522`), so the line this finding asked for was in the file before
the audit ran. The gap is a path focus does not cover: Enter commits while the field keeps focus, so no
`focus` event ever fires again and the next keystroke appends. `commit()` now selects the label it just
wrote. Measured on the live page: type `Rice Shower`, Enter, then press `g` once. The field reads `g`,
the status line reads `9 matches`, and both hidden inputs are empty (`""`/`""`), which is the correct
statement about a half-typed query. Before the change that same gesture produced the label plus `g` and
`No trainee or card found.`

The related data-integrity risk is **not** present and was tested: when the text stops matching, both
hidden inputs (`umamusume_id`, `character_card_id`) are emptied rather than left pointing at the previous
pick, so a submit in the broken state fails `required` instead of silently creating the wrong run.

### C-2 · major · required state is carried by a control the Trainer never uses

- **Evidence.** The fallback `<select name="umamusume_id">` has `required`. The combobox input the script
  enables in its place has no `required` and no `aria-required`; the a11y tree shows it as
  `combobox "Trainee or costume card name"` with no required state. No field on the form shows a visual
  required marker, while two optional fields do carry one in their labels: "Scenario (optional)" and
  "Notes (optional)".
- **Observed.** With JavaScript on, which is the path the form is built for, nothing announces or shows
  that the trainee is mandatory. Status is also required and equally unmarked.
- **Expected.** `aria-required="true"` on the live control, and the same optional/required convention on
  all four labels.
- **Impact.** Screen-reader users get no cue before a failed submit, and sighted users get an asymmetric
  convention: the form tells you what is optional but not what is mandatory. WCAG 3.3.2 territory.
- **Fix.** Mirror `required` onto the input when the script swaps the pair, and pick one marker
  convention for the form.
- **Verification.** Re-snapshot with the script enabled and confirm the combobox exposes required.

**Fixed 2026-10-03 with `aria-required="true"` on the input, not `required`.** The value that posts is the
hidden pair, so `required` on the text field would gate on the label the Trainer sees typed, which is a
different string from the one submitted, and a hidden-but-rendered control still blocks the submit.
Re-measured in the accessibility tree: `combobox "Trainee or costume card name" ... required settable`.
The visible convention is left as it was, marking the optional fields, because that is already one
coherent convention and Status carries a native `required` of its own; adding a second marker to the two
mandatory fields would state the same fact twice.

**Corrected the same day, from a sibling form.** The paragraph above states the convention as if the app
had only one. It does not: `resources/views/runs/import.blade.php` ships "Trainee *", and the run page's
hand-correction form ships "Turn *" and "<Stat> *". `*` is this repository's own required marker, already
in use for the same entity, so the create captions are now "Trainee *" and "Status *" while "(optional)"
stays on the two fields that mark it. One rule now covers all four fields: `*` marks what has no answer of
its own, `(optional)` marks what does.

The marker does interact with WCAG 2.5.3 on the combobox, since the caption reads "Trainee *" while the
explicit `aria-label` stays "Trainee or costume card name". The words are still a prefix and `aria-required`
carries the state, so the pin in `TraineeSelectorTest` compares against the caption with its marker
stripped (`str_starts_with($name, rtrim($caption, ' *'))`); no speech-input user says "Trainee asterisk".
The alternative, the glyph in its own `aria-hidden` span, is what the run page's asterisks would need too,
and that file is held.

### C-3 · minor · the visible caption and the accessible name of the same field disagree

- **Evidence.** The sighted label text is "Umamusume". The live control's accessible name is
  "Trainee or costume card name" (explicit `aria-label`, which wins over the moved `for`).
- **Impact.** Low for a screen reader alone, but a label-vs-name mismatch breaks speech-input users who
  say the visible caption, and it makes the two paths describe one field differently in docs and tickets.
- **Fix.** One name for both: make the visible caption "Trainee or costume card" or set the `aria-label`
  to the caption and move the specificity into a hint below the field.

**Fixed 2026-10-03, on neither of the two options above.** The caption is now "Trainee". "Trainee or
costume card" (the first option) would sit over the no-script `<select>` too, and that control lists
trainees and nothing else, so the caption would promise a card search on the path that cannot do it.
"Trainee" is true on both paths, it is the word the field's own copy already uses ("No trainee or card
found", "Trainees and costume cards"), and it is a prefix of the accessible name, which is what WCAG
2.5.3 asks for and what lets a speech-input user say the words on screen. The `aria-label` is unchanged,
so the pin at `TraineeSelectorTest` naming that string still holds. Re-measured: `StaticText "Trainee"`
beside `combobox "Trainee or costume card name"`.

### C-4 · minor · a non-option text node sits inside `role="listbox"`

- **Evidence.** With the list open, the accessibility tree reads:
  `listbox "Trainees and costume cards"` → `text: Rice Shower ライスシャワー` → `option "Rice Shower · [Rosy Dreams] debut"`.
  The header is presentation by design (`trainee-combobox.ts:300-305` says so in the comment above the
  `aria-label`), and the option's own accessible text already carries the trainee name.
- **Impact.** A listbox's owned elements should be options; a bare text child is inconsistent across
  screen readers, and here it duplicates information the option already announces.
- **Fix.** `aria-hidden="true"` on the header row, or move it outside the listbox as a caption.

**Fixed 2026-10-03, on both presentation row kinds.** `aria-hidden="true"` now goes on the per-trainee
header and on the band divider, which is the same shape: a sentence inside the listbox that the option
under it already states in its own title ("No costume card confirmed yet"). Read off the live DOM, with
the query `g` open: 15 children, the six presentation rows each carry `aria-hidden="true"`, the nine
options carry none.

This one is verified at the DOM level only. The instrument that produced the original evidence was the
Playwright accessibility tree, and that browser session is busy with the activity-label research pass. The
session used here (browser-use) serialises this ARIA listbox with **no children at all**, which it still
did after every `aria-hidden` was stripped back out in the live page, so it can neither show the defect
nor show it gone. A tree-level re-read is still owed on the Playwright engine.

### C-5 · debt · the request validates four fields the create form never sends

- **Evidence.** `StoreTrainingRunRequest::rules()` covers `inheritance_parent_a_id`,
  `inheritance_parent_b_id`, `current_objective_index` and `shop_resets_in`. `create.blade.php` renders
  none of them.
- **Impact.** Two things. The store path carries validation that cannot be exercised from the surface it
  guards, which is how a rule stays untested. And a Trainer starting a run has no signal that ancestry,
  objective period or shop countdown exist as run data until they find them on the detail page.
- **Fix.** Either put the fields on the form (ancestry is known at the start of a run, which is exactly
  when it is entered in the game) or drop them from the store-time rule set and keep them on update.
  This is a design decision, not a bug: **needs-design-decision**.

### Checked, no finding

- The keyboard path works: ArrowDown moves the highlight, Enter commits, `aria-expanded` closes, and the
  live region announces `Selected Rice Shower · [Vampire Makeover!]`.
- The first match is pre-highlighted, so Enter on a filtered list picks it without navigation.
- Match count is announced (`2 matches`, `1 match`, `No trainee or card found.`).
- The epithet and the date inside an option are separated by `ml-2` in the rendered row, and the option
  carries an explicit spaced `aria-label`; the run-together visible in raw `textContent` is not a defect.
- Scenario options come from the composition matrix rather than free text, and "Not set (baseline strip)"
  states what the empty choice does.
- No `{!! !!}` on catalog data, no `dark:` utilities, no off-token colors in the form's classes.

---

## Part 2: `/training-runs/7`, the run page

Created as run 7 from the form above. Populated hands-on: the six-card deck, five acquired skills, and
turn 67 carrying the stat line from the report (607 / 577 / 549 / 736 / 397, SP 173, mood GREAT).

### R-1 · major · the page is 5,284 DOM nodes and almost all of them are unfilterable options

- **Evidence.** Measured after load: 5,284 elements, 9 forms, 47 inputs, 22 selects. Six deck selects
  carry **252 options each** (1,512 nodes) and five skill rows carry **624 options each** (3,120 nodes).
  Option elements are roughly 99% of the page. The three-year race calendar is also rendered inline in
  full, with every candidate race and its fan threshold, above the fold content the Trainer came for.
- **Observed.** To set one deck slot a Trainer opens a 252-row native list and scrolls; to add one skill,
  a 624-row list. Browser typeahead is the only search.
- **Expected.** The pattern already exists in this codebase: the create page's trainee combobox filters
  as you type, announces match counts and works by keyboard. It is used for one field in the app and for
  none of the twelve pickers on the run page.
- **Impact.** The page's two most-used controls are its slowest and hardest to use, and the DOM cost is
  paid on every visit. This is the single highest-value change in the audit.
- **Fix.** Extract the combobox into a shared component and use it for deck slots and skill rows, with
  the option list filtered server-side or from the existing roster JSON rather than emitted as 624
  `<option>` elements per row.
- **Verification.** Re-measure node count after the swap; the page should drop by an order of magnitude.

### R-2 · major · the Unity Cup numbers the scenario turns on have no input anywhere

- **Evidence.** The Resources strip renders `TEAM RANK N/A not yet recorded` and
  `SPIRIT BURSTS N/A not yet recorded`. Across all nine forms on the page the field names are:
  `choice, circles, condition, deck[n][support_card_id], energy, entry_mode, fans, guts, mood, outcome,
  penalty_kind, placement, power, race_catalog_slot_id, scenario, skills[n][...], sp, speed, stamina,
  status, turn, umamusume_id, wit, year`. There is no team-rank, burst-count, league-placement or
  team-grade field.
- **Observed.** The strip displays a value the app can never fill.
- **Impact.** For Unity Cup these are not decoration. The documented run's burst count (6 normal plus 5
  Extreme) is what decides whether the November event pays the white or the gold scenario skill, and
  team rank S is one of three Elite Team gates. A Trainer planning in this tool cannot record the inputs
  to the scenario's own decisions.
- **Fix.** Either add the fields to the run update form, or drop the two cells from the strip until they
  can be set. Showing `N/A not yet recorded` for a value with no entry point reads as "I forgot to fill
  this in", which sends the Trainer looking for a control that does not exist.

### R-3 · major · caps render as the scenario floor, so the band contradicts the client

- **Evidence.** After saving turn 67 the Stats band reads `SPEED A 607 / 1,300`. The client's own screen
  for this run shows `607 /1325`, and the other four are 1322, 1368, 1308 and 1800.
- **Cause.** The denominator is the scenario base cap. The overages from inheritance breakthroughs and
  whatever else raises a cap have nowhere to be entered, so the tool states a ceiling the game does not.
- **Impact.** A Trainer comparing screen to tool sees a mismatch on every stat and concludes one of them
  is wrong. It also hides the real headroom, which is the number the plan depends on.
- **Fix.** Let the turn or the run carry a per-stat cap override, defaulting to the scenario base, and
  label the source the same way the report does. **needs-design-decision** on whether the cap is
  run-level or per-turn.

### R-4 · major · the skill picker lets a one-glyph-different name win, and the wrong pick saves cleanly

- **Evidence.** The catalog holds both `Corner Adept ○ · 180 SP` (export 200332) and
  `Corner Adept × · 100 SP` (export 200333); 39 skills carry `×`, 69 carry `○`, 65 carry `◎`. Typing
  "Corner Adept" in a 624-row list and taking the first hit recorded the `×` variant. The save accepted
  it with no warning, and the Acquired list then displayed the wrong skill with full confidence.
- **Impact.** This is the failure mode of the tier glyph: three near-identical names differing by one
  character, in an unfilterable list, with no confirmation step. It was caught here only because the
  source data was checked afterwards.
- **Fix.** Group or label tiers in the option text (`Corner Adept (○) · 180 SP`), and sort the family
  together so the variants are adjacent rather than scattered across 624 rows.

### R-5 · major · no save on this page confirms itself

- **Evidence.** After "Save deck", after "Save skill status", and after the turn commit, the page
  re-renders with no flash and no status element. The only text near the top is the run header and the
  notes.
- **Impact.** With nine independent forms on one page, the Trainer cannot tell which save landed, and a
  silently rejected submission is indistinguishable from a successful one. The turn table and the
  Resources counter do update, which is the only feedback that exists, and for the deck save there is no
  such visible change at all if the selection was already showing.
- **Fix.** One flash partial reused by all nine redirects, naming what was saved.

### R-6 · minor · internal review markers and stale disclaimers are shipped as UI copy

- **Evidence.** The activity choice labelled "Mood adjustment" renders with the literal text
  `[Unverified]` in its on-page description: "Mood adjustment [Unverified] Raises Mood. The client label
  is not verified." Separately, the skills help reads "Skill-point discounts from hint levels are not
  shown: no source in this repository settles the per-level reduction."
- **Impact.** The first is an editorial state marker a Trainer cannot act on, and it advertises that the
  control's own name is a guess. The second is now false: the per-level ladder was settled from the
  client on 2026-10-03 and recorded in `docs/research-scratch/SKILLS-MECHANICS.md` §2.4 and
  `docs/UMAMUSUME_REFERENCE.md` §1.1.4 (10 / 20 / 30 / 35 / 40 percent at Lv1 through `Lv Max`). The
  picker could show discounted prices and currently states the opposite.
- **Fix.** Move `[Unverified]` to a build-time note or a tooltip on the source, not the label. Update the
  discount sentence, or better, use the ladder.

### R-7 · major · the race calendar cannot record this scenario's races

- **Evidence.** The calendar race picker offers 58 slots and none of them is the Kyoto Daishoten or the
  Arima Kinen. Queried at the data layer: `race_catalog_slots` holds 410 rows and matches zero on
  `%Daishoten%` or `%Arima%`.
- **Impact.** Both of this run's remaining races, including the goal race, are unrecordable through the
  "Calendar race" path, so the "+" markers on the calendar stay empty and the race must be entered as
  "not on the calendar", which loses the slot link the rest of the page is built around.
- **Fix.** Seed the Unity Cup slot set, or fall back to the free-text path with a visible reason rather
  than an empty list.

### R-8 · debt · two forms post to `/turns` with two controls both labelled "Add turn"

- **Evidence.** `form[action$="/turns"]` resolves to two forms: the guided one, and a manual-correction
  one inside `<details><summary>Correct a turn by hand</summary>`. Both contain a submit labelled
  "Add turn". A selector that does not exclude the disclosure resolves to the hidden one and its click
  never lands; the audit pass hit exactly that before submitting the visible form directly.
- **Impact.** For a human the disclosure label disambiguates, so this is not a blocker. It is a
  testability and automation hazard, and the duplicate label is worth renaming ("Add turn" versus
  "Save correction").

### Checked, no finding

- The turn flow is a keyboard-guided stepper: "Step 2 of 5 · Choose activity" with the instruction
  "Keys 1 to 7 choose an activity, arrow keys move between them, Enter previews the turn, Escape returns
  to the choices." The commit control is deliberately withheld until preview, which is why it reads as
  absent. Good design, and my R-8 note is about the duplicate label only.
- Activity choices carry the mechanics in their labels: "Wit Costs no Energy, so it stays available when
  the bar is low", "Rest Returns about +30 Energy, and a rest can backfire into a stayed-up-late
  penalty". These match `docs/UMAMUSUME_REFERENCE.md` §1.1.1 and §1.1.6.
- The `N/A` plus reason rule is honoured throughout the strip, with no em dash and no zero default.
- Deck slot 6 is labelled "Slot 6 · Friends", which matches the client's friend slot exactly. The deck
  form records the card only: no level, limit break, bond or hint level, so the run's card state from
  §1.4 of the report has no home. That is a scope question, not a defect.
- "Delete run" sits behind a `<details>` disclosure with a separate confirm button, not beside the
  everyday actions as a one-click control.
- Acquired skills are marked `✦ Unique`, so the unique-skill tier survives into the list.

---

## Verdict: **Pass with warnings**, revised to **Fail** on the run page after the owner pass below

The flow completes. A real run was created and populated end to end without a blocker, the empty and
`N/A` states are honest, the keyboard stepper is better than most commercial tools, and the copy that
explains mechanics is genuinely useful. Nothing is broken.

The owner pass that followed found one blocker and eleven more items, and the blocker is not a detail:
the sticky run-state region occupies 66 to 73 percent of the viewport, so the forms a Trainer came to
use get the remainder. See O-2 and the revised verdict at the end of Part 3.

What the pass exposes is that the tool models URA Finale well and Unity Cup only as a label. The
scenario's own decision variables, team rank, burst counts, league placement, real stat caps and the
race card the Trainer is actually looking at, are either absent (R-2, R-3, R-7) or displayed as `N/A`
with no way to fill them. Twelve of the page's heaviest controls are unfilterable native selects that a
component already in this repo would fix (R-1, R-4).

**If only three things get fixed:** R-1 (reuse the combobox for deck and skill pickers), R-2 (enter the
Unity Cup state the scenario turns on), and R-5 (confirm every save). R-3 and R-7 need a data decision
before code.

## What this audit could not verify

- No `SCREEN_SPEC.md` and no `.design-qa/` baseline exist, so there is no approved layout or spacing
  target to diff against. Visual-fidelity findings are limited to token use, states and copy.
- Numeric turn fields were set programmatically after a label probe, so keystroke-level validation on
  those inputs was not exercised. Everything else went through the UI.
- Hover, focus-visible and disabled states were not systematically walked; the keyboard path was.
- No responsive pass. `DESIGN.md` may configure viewports; nothing here establishes which.
- The 5,284-node count is one measurement at one viewport with the deck and skill forms in their default
  state, not a performance profile.

---

## Part 3: owner observations, 2026-10-03

Twelve observations from using the tool on a live run. Each was checked against source, the database or
a measurement before being recorded, and three came back different from how they were reported.

### O-1 · major · the dark theme ships with no way to select it

- **Confirmed.** `DESIGN.md` documents two themes: light-first, with `html[data-theme='dark']` as "a
  measured palette of its own, not an inversion", G-18 requiring every text/background pair to pass in
  both and G-20 requiring the first paint to already be resolved. `layout.blade.php` renders
  `data-theme` from a server-side `$theme` supplied by `AppServiceProvider` under an owner ruling dated
  2026-09-27, and a head script can set `dataset.theme = 'dark'`.
- **Gap.** There is no control in the shell. A second verified theme exists in the design system and the
  only way to reach it is a server-side value.
- **Fix.** A toggle in the nav that persists the choice, or state in `DESIGN.md` that dark is
  config-only. Not a defect in the palette, which is the part that was ruled on.

### O-2 · blocker · the sticky run-state region takes two thirds of the viewport

- **Measured.** `runs/show.blade.php:70` puts `lg:sticky lg:top-0 lg:z-10` on
  `section[aria-label="Run state"]`. That section measures **524px tall at both 1024x720 and 1280x800**,
  which is **73% of the shorter viewport and 66% of the taller one**, and it stays pinned while the
  Trainer works on the forms below. Its largest child is 216px.
- **Impact.** Above the `lg` breakpoint, which is where the sticky behaviour is designed to apply, the
  working area is whatever is left after the pinned block. The comment at `show.blade.php:14` shows the
  intent was to spare narrow screens, so the case that breaks is the one the rule targets.
- **Fix.** Pin only the Resources strip, which is the part that benefits from being always visible, and
  let Stats and Mood scroll away. Failing that, cap the sticky block with `max-height` and an internal
  scroll, or collapse it to a one-line summary once it is pinned.
- **Verification.** Re-measure at 1024x720 and 1280x800; the pinned region should be under roughly 25%
  of the viewport height.

### O-3 · major · four `N/A` cells, and they are not the same problem

- **Split, after checking the field list.** `energy` and `fans` **are** enterable, on the turn form, and
  they read `N/A` on this run only because the turn was logged without them: the client gives no Energy
  number, and the report's 209,245 fans is a running total rather than a per-turn delta. `TEAM RANK` and
  `SPIRIT BURSTS` have no field anywhere on the page, which is R-2.
- **Real finding underneath the report.** The strip does not distinguish "you have not entered this"
  from "this tool cannot record this", and the `fans` cell does not say whether it wants the total or the
  change. Both are ambiguity, and the second is a modelling decision. **needs-design-decision.**

  **Ruling revised 2026-10-03.** Owner confirmed (b)-equivalent. Fields store totals, display
  computes the delta, labels read `Energy (after this turn)` / `Fans (after this turn)`. The (c)
  delta-storage option is not in force.

### O-4 · major · Mood sits in its own block while the stats band wastes vertical space

- **Confirmed as reported, with a structural cause.** The DOM does not group the way the screen reads:
  asking for the nearest `section` of the `Stats` or `Skills` heading returns the race-calendar wrapper,
  so the panels are not independently addressable. That is why the band cannot be reflowed in one place
  and why the same markup produces both this problem and the R-1 weight.
- **Fix as proposed.** Move the explanatory text beside the values instead of under the last two columns,
  and place Mood in the Resources row with turn, Energy, fans, team rank and bursts. Requires the section
  nesting fixed first.

### O-5 · blocker · the calendar is not scenario-scoped, and it is showing the wrong races

- **Confirmed at the data layer.** `race_catalog_slots` holds 410 rows and its `scenario_key` is **empty
  on the Senior October set**. Those twelve rows are Aichi Hai, Carbuncle Stakes, January Stakes, Kyoto
  Kimpai, Tokyo Hai, Fuji Stakes, Swan Stakes, Tenno Sho (Autumn) and friends. The client's Senior Early
  October race for this run is the **Kyoto Daishoten, G2**, which appears nowhere in the table, and the
  goal race, the **Arima Kinen**, does not either. This extends R-7 from "the picker lacks these two" to
  "the seeded set is not the scenario's set and carries no scenario key to filter by".
- **Second half of the observation, confirmed.** Every candidate race for the half-month is listed flat,
  with no focus ring, badge or marker distinguishing the race the trainee ran, the one scheduled next, or
  the goal. The client distinguishes all three (Scheduled pill, Recommended flag, goal slot) and the
  report records them.
- **Note on turn numbering, which is right.** The Senior Early October rows carry `turn 19`, and the run
  page accepted turn 67 for the same half-month, which is 48 + 19. The turn arithmetic is consistent even
  though the race identities are not.
- **Correction (2026-10-03).** The table holds 410 rows and only **4** carry a `scenario_key` at all
  (`trackblazer`, `unity_cup`, `grand_concert`, `ura_finale`, one each); **406 are NULL** and none is an
  empty string. `year` holds `1..4`, not a name, so `year='Senior'` matches nothing. Seeding is therefore a
  data job for 406 unscoped rows, not a backfill of the October set. This audit's "empty on the Senior
  October set" wording overstated the scope.

### O-6, O-7 · major · three panels are documentation, not capture

- Team rank, spirit bursts and team races render explanatory text where a control should be. The
  description of the six burst states is genuinely useful and is currently the only thing the panel does.
  Team races have a partial path, the `circles` and `placement` fields on the race form, but no round
  list, so the four preseason rounds and the finals this run has are unrecordable.
- The report has all of it: rank S, 8th, 55 Unity Trainings, 6 bursts, 5 extremes, four rounds won.

### O-8 · major · the deck renders as a bar of selects and never closes

- **Confirmed.** After saving, the six selects stay on screen at 252 options each. There is no reset
  control; clearing the deck means choosing "Not equipped" six times and saving. The deck is also frozen
  in the game once a career starts, so a surface that keeps offering the picker is offering something the
  Trainer cannot legitimately do.
- **Fix as proposed.** Render the deck as six card tiles, with the locked state as the default once a
  deck is saved, one "Reset deck" action behind a confirm, and the card's own identity, level, type and
  scenario-link badge on the tile. The data for the tile already exists in `support_cards` and the
  `isScenarioLink()` derivation used in the report.
- **Related gap, not in the original observation.** The deck stores the card only: no level, limit break,
  bond or hint level. The report carries all four per card and the tool cannot.

### O-9 · major · the activity picker describes mechanics instead of showing numbers

- **Confirmed.** The choice labels are good prose ("Wit Costs no Energy, so it stays available when the
  bar is low", "Rest Returns about +30 Energy, and a rest can backfire into a stayed-up-late penalty")
  and they match the corpus. They are also the only information offered. The client prints, per tile, the
  facility level, the stat preview, the SP preview and the failure percentage, which is what the decision
  actually runs on. None of it has a home here.
- **Fans and Energy are enterable and were left empty**, per O-3, and the form does not say which sense
  it wants.
- **"Mood adjustment" is unresolved and the owner's challenge is fair**: the client's action set is
  Rest, an outing that raises Mood, and a treatment option, and this app offers a choice named after its
  effect rather than its button. A research pass is running on the verbatim client labels, the Infirmary
  rules and the stayed-up-late probability. Until it returns, the `[Unverified]` marker should not be
  shown to Trainers (R-6).
- Manual correction behind a disclosure is right and is kept.

### O-10 · major · the Energy control should be a gauge, not a number box

- **Partly established already**: maximum starts at 100, and the official Global account announced a
  numeric readout under the bar around 2025-10-24, which sits oddly against a 2026-10-03 capture that
  shows none. The colour behaviour described, green at full grading to blue as it drains, is **not
  established** by any source found so far, and neither is the Trackblazer item route for raising the
  maximum. Both are with the running research pass.
- **Design position for the doc either way**: a free-text number field is the wrong control for a value
  the game itself only shows as a gauge. A bounded slider or a 0 to 100 stepper with the bar rendered
  beside it matches how the Trainer reads it, and it makes the "no number in the client" case a state the
  tool can show rather than a blank.

### O-11 · major · the skills panel is mislabelled, capped, and source-blind

- **The label is wrong, and the owner's reading is the correct one.** The three rows this run shows as
  `Suggested` are the costume card's own starting skills, seeded by the controller per KI-33. They are
  not suggestions, they are what the trainee arrives with, and the unique skill among them is from turn
  one. Call the state `Starting` or `Innate` and reserve `Suggested` for hintable skills.
- **The wall the owner describes is real and unrepresented**: 624 catalog skills, 173 SP available, and
  no budget line anywhere. The prices are in the data, the SP total is enterable per turn, and the
  discount ladder is now settled in the corpus (§2.1 of the report), so a "what can I actually buy" view
  is buildable today.
- **Structure**: five fixed rows, one unfilterable select each, statuses set per row, no grouping by
  source. The distinction the Trainer needs is unique / from support card / from event / evolved, and the
  catalog can supply three of the four.
- This is the owner's worst-rated panel and the evidence supports it: it combines R-1, R-4, R-5 and R-6.

### O-12 · major · there is no goals surface

- **Confirmed, with one component already there.** `components/grade-point-meter.blade.php` renders an
  objective list, but its props and comments scope it to Trackblazer Grade Points, and
  `current_objective_index` has no input on either screen (C-5). Nothing in the tool shows the run's
  actual goal line, which is the first thing the client puts in its header: "Place 1st in Arima Kinen,
  entry criteria met, 5 turns", plus the cleared goals behind it.
- **Fix.** A goals panel: name, deadline period, state (cleared, active, failed), and the turn countdown
  the client prints. The race rows already carry `is_mandatory` and `is_special_race`, and the report
  records three cleared goals and one active, so the shape is known.

  **Ruling revised 2026-10-03.** Owner confirmed (b). The goals panel renders directly in
  `show.blade.php` from `is_mandatory` and `is_special_race` race rows. The grade-point-meter
  component is not generalised. A future surface that needs the same list can extract a shared
  component at that time.

---

## Part 4: the sibling forms, swept 2026-10-03

The run page is held on file ownership (see the backlog), so the sweep went sideways instead: the other
forms that share the create page's rules, in files no other session has open. Two findings, one of them
the worst on this record, and both fixed the same day.

### I-1 · blocker · the import form's paste field was `required`, which closed the upload path

- **Evidence.** `resources/views/runs/import.blade.php` put `required` on `textarea[name="csv"]` while
  `input[name="file"]` carried none. Measured on the live page before the change:
  `textareaRequired: true`, `fileRequired: false`, `formNoValidate: false`, `form.checkValidity(): false`
  with the textarea empty. A native constraint is evaluated per field, so a Trainer who chooses the file
  and leaves the box empty gets "Please fill out this field" and the form never submits.
- **Why the server does not want it.** `ImportHistoricalRunRequest::prepareForValidation()` copies the
  upload into `csv` and removes `file` before rules run, so `csv` is only empty on submission when the
  Trainer supplied neither. Its own message is written for that case: `'csv.required' => 'Paste the run's
  CSV or choose the file to import.'` The browser constraint was firing before that message could, on the
  one path the file input exists to offer.
- **Fix.** `required` dropped from the textarea. The rule stays where it can see both halves.
- **Verification.** Re-measured after: `textareaRequired: false`, `valueMissing: false`, and
  `traineeRequired: true` (the pick the Trainer genuinely still owes is still gated).

### I-2 · minor · the required marker was three different conventions across four forms

- **Evidence.** `Trainee *` on import, `Turn *` and `<Stat> *` on the run page's hand-correction form,
  nothing at all on the two mandatory fields of the create form, and `Tier` marking itself optional only
  through `placeholder="optional"`, which disappears the moment the Trainer types in it.
- **Fix.** One rule, using the marker the repository already chose: `*` on every field that has no answer
  of its own, `(optional)` in the caption on every field that does. Applied to `Trainee *` and `Status *`
  on create, `Status *` on import, and `Race title *`, `Month *`, `Half *`, `Tier (optional)` on the race
  panel's manual branch, where the placeholder marker is gone.
- **Checked, no finding.** The race panel's `title`, `month` and `half` are `required` only inside the
  manual branch, and `StoreRaceEntryRequest` adds those three rules only when `$mode === 'manual'`, so the
  client constraint and the server rule cover the same branch. The calendar branch's select has no empty
  option, so it cannot be left un-answered and needs no marker.
- **Still open.** The run page's own asterisks are inside the held file.

### I-3 · settled from the research pass · the mood action is named `Recreation`

Backlog item 7 asked for the `[Unverified]` treatment to come off the mood action once the research pass
reported. It reported, and the repository already had the stronger evidence: three July 2026 client
captures under `docs/research-scratch/screenshot-notes/` read the action row verbatim, six buttons,
"Rest, Training, Skills, Infirmary, Recreation, Races". `TrainingRunController::turnChoices()` now labels
that row `Recreation` with the flag removed, and the controller's docblock records which captures the name
came from. Measured on the live run page: the word `Recreation` is present, `Mood adjustment` is not.

Three things the same pass surfaced that are **not** edited here, with the reason each one waits:

*(Read Part 5 before acting on items 2, 3 and 4 below: the local corpus withdrew item 3 and item 4 outright
and settled item 2 as an in-repo conflict.)*

1. **The client offers two actions the tool cannot log.** `Infirmary` and `Races` are buttons on that row,
   and the turn-choice list has no key for either, so a Trainer recording a rest-gone-wrong turn or a race
   turn has no honest option. Adding them is new behaviour with a `choice` value behind it, which needs a
   PRD citation before code, not a copy change.
2. **`Rest`'s "+30" is now contested.** The detail line reads "Returns about +30 Energy", which traces to
   the corpus row at `docs/UMAMUSUME_REFERENCE.md` §1.1.5 (+30 per standard rest, GameWith 2026-09-25). The
   research pass returned a post-rework Global table of 30 / 50 / 70 with 50 the most common outcome
   (umareference, 2026-08-26), and two JP probability pages agreeing on the three tiers but stale on the
   split. That is a source conflict for the corpus's Source Conflict Log, and the single-source post-rework
   figure is not strong enough to overwrite a row on inference. Flagged, not applied.
3. **"Motivation" is not a client word.** Across the 154 Global notices the official site still serves, the
   research found `Mood` and never `Motivation`, and our own seeded `support_effects` row 2 is named "Mood
   Effect". Verified here that no view, controller, config or `tools/lore.php` pattern prints `Motivation`,
   so there is no UI defect to fix, but `AGENTS.md` describes `Motivation` as Global client terminology the
   lore gate adds, and the gate does not contain that word. `docs/UMAMUSUME_REFERENCE.md` §1.1.6 also titles
   itself "Motivation (mood)". Both are documentation lines about a ruling, and rulings are the owner's to
   write, so they are named here rather than edited.
4. **Not a UI item, but the same pass surfaced it, so it is recorded rather than lost.** The Trackblazer
   shop block in `config/scenarios.php` looks short one row, "Energy Drink MAX" (30 coins, maximum Energy
   +4 and Energy +5), and prices "Artisan Cleat Hammer" at 25 where the publisher page says 20. The two
   Global sources agree with each other and both are marked stale, so this is a recompare against the
   export, not an edit from a guide.

---

## Part 5: what the local corpus settled, 2026-10-03

The research pass came back from the web, and the same questions were then run against this repository's
own files: the nine committed JSON data sets under `database/seeders/data/`, the five snapshot
directories, the 49-plus screenshot notes, and the two corpus documents. That pass answered more than the
web pass did, and it withdrew two of the four claims in I-3.

### Withdrawn first, because they are wrong and they are printed above

**I-3 item 3 is false as written.** It says `AGENTS.md` describes `Motivation` as a term the lore gate
adds "and the gate does not contain that word". It does: `tools/lore.php:64` carries the pattern
of the JP-wiki stat names, `wisdom` and `motivation` among them, as whole words
over the app paths. The grep that produced my zero was case-sensitive and every one of those patterns is
lowercase, so it matched nothing and reported silence as absence. `AGENTS.md` is accurate, and the
repository has ruled Wit and Mood as the client words with Wisdom and Motivation as the JP-wiki English
for years.

**I-3 item 4 is false too.** It says `config/scenarios.php` "looks short one row", Energy Drink MAX, and
misprices Artisan Cleat Hammer at 25. The block's own comment answers the first half
(`config/scenarios.php`, above `shop_items`): "This is a subset of the published list, chosen to span the
categories (stats, energy, mood, training effects, races, facility). It is not the whole catalogue, and the
step says so where it renders." The second half is answered by the table the block is transcribed from:
`docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md:422` prices Artisan Cleat Hammer at **25**, the same
number config carries. The web pass's "20" came from a page this repository does not hold, so the config was
never the thing out of line.

### What the local files settled, with the line each answer came from

1. **Rest's recovery tier is a conflict inside this repo, and the UI was quoting the wrong side of it.**
   `docs/UMAMUSUME_REFERENCE.md:162` says "+30 Energy per standard rest", cited to GameWith 2026-09-25. The
   same repository's `SCENARIO-PUBLISHER-REFERENCES.md` §2.1 says the opposite in three rows: ":1518 Rest
   costs 1 turn and restores 50 energy (the modal case)", and the measured table at :1531-1533 (Kamigame,
   2022-12-19, n = 100) "休息はバッチリ！ 体力+70 12%", "リフレッシュ完了 体力+50 66%", "寝不足で…… 体力+30
   確率で「夜ふかし気味」獲得 22%". So +30 is the failure tier, +50 is the common one, and the same section
   settles that a rest consumes a turn, which the reference row's own "Free-turn action" wording obscures.
   Both Trainer-facing copies of the number were dropped rather than re-picked: the `Rest` detail line in
   `TrainingRunController::turnChoices()`, and `resources/views/components/guided-step.blade.php` ("Rest
   returns about +30" became "Rest refills Energy"). Measured on the live run page: no `+30` string remains,
   the new detail renders, 43 guided tests pass, and `GuidedTurnOnRunViewTest:293` still pins the hint
   branch that copy lives in. The reference row itself is left for the owner's Source Conflict Log; it is
   contested between one post-rework citation and three measured rows, and the client-capture evidence that
   would settle it is an Energy-bar read this tool does not have.
2. **`Recreation` was already the repository's word.** `docs/scenarios/01-ura-finale.md:54` reads "Use
   **Recreation** to raise mood, at the cost of an entire turn with no training progress", and :57 and :103
   record the summer-camp variant where Rest and Recreation both restore Energy and Mood. So the label change
   in I-3 matches the repo's own Global scenario guide, not only the three July captures.
3. **The Infirmary rules the web pass could not find are transcribed here.** §2 of the publisher references:
   energy +20 plus an attempt to clear one bad condition, the attempt can fail (:1558); an inferred 85% cure
   and 15% fail from 119 and 111 trials, stated by its authors as an estimate (:1560-1561); only one
   condition per visit (:1564); unavailable during summer camp (:1565); and resting during camp clears every
   condition (:1566). Every one of those rows carries the `[JP]` tag, so under the Global-only audience
   ruling they are mechanics to cite as JP-sourced rather than Global client reads, and the button itself is
   evidenced by the captures. The plan item that needs an `Infirmary` turn choice now has its numbers.
4. **The Wisdom and Motivation strings in the data never reach a Trainer.** They are in
   `skills.609afe88.json` (for example "Your Stamina, Guts, and Wisdom stats are increased by 40 if your
   Motivation is Good or better"), but `SkillSeeder` and the `Skill` model import no description field at
   all, and the views already say the consequence: `skills/index.blade.php:9-10` scopes the permitted Skill
   surface to name, name_ja, sp_cost, type and is_unique, and `skills/show.blade.php:118-124` states that
   "This tool's skills source stores no description it may render". So there is no display-path conflict to
   fix, and the one item that looked like a UI terminology bug is a data-provenance note.
5. **The Energy gauge question has a local answer, and it is not the web one.** The capture at
   `docs/research-scratch/screenshot-notes/Screenshot 2026-07-17 221504.md:24` records what carries state:
   "Energy is bar length plus gradient colour". No note reads a number. So of the two things the web pass
   could not establish, the Oct-2026 numeric display and the colour rule, the client frames in this repo say
   the bar is graphical and the gradient is a state channel. That is the evidence O-10's colour decision
   should be written against, and it is also a caution: a HUD capture of the bar at low Energy is still what
   would name the thresholds.

---

## Part 6: the option flood, halved where it was reachable, 2026-10-03

Item 6 of the plan was "extract the create page's combobox into a shared component and a shared JS module,
use it for the deck and skill pickers, and source the options from data rather than emitting `<option>` per
row." Measured and reasoned about first, that shape is the wrong vehicle, and the right one needed no JS at
all. Recorded here because it changes what the remaining half should be built as.

**Why not the JS extraction.**

1. The option nodes are not decoration, they are the no-script path. ADR-0007 and the create page's whole
   design say the native control stays in the markup and the script only takes it over. A JS-populated
   picker either keeps the `<option>` elements, in which case the node count is unchanged, or drops them,
   in which case the Trainer with scripting off has a select with no choices. The saving the audit wanted
   and the contract the audit's own page defends are the same bytes.
2. `TraineeSelectorTest` holds about twenty assertions pinned to the trainee module's source text, by
   function slice. Extracting its engine would not adapt those pins, it would void them, and the module it
   would leave behind is a generic list over a flat payload while the trainee module commits a *pair*
   (trainee and card) with banding, cardless rows and exact-name debut resolution. Two shapes, one name.
3. The peer session's in-flight `RunViewTargetSizeTest` asserts `//select[starts-with(@name, "skills[0][")]`
   carries `h-11`. Replacing those selects is not a collision in one file, it is a contradiction of a test
   that has not been committed yet.

**What landed instead, and where.** `resources/views/components/deck-panel.blade.php` now renders the card
catalogue once instead of six times: one slot open with the full list, five closed, each carrying a hidden
input under the same field name and a `Change slot N` link built with `request()->fullUrlWithQuery()`, the
idiom `x-race-calendar` already uses for its year tabs. A failed submission opens the slot that errored. The
POST contract is byte-identical from `StoreDeckRequest`'s point of view: six keys, blanks dropped by its own
`prepareForValidation`. No JavaScript, no new dependency, and the no-script path keeps every capability it
had, at one extra click per slot.

| Measured on the run page | Before | After |
|---|---|---|
| `select[name^="deck["]` | 6 | 1 |
| options inside the deck form | 1,512 | 252 |
| element nodes, whole page | 8,941 | 7,696 |
| `option` nodes, whole page | 7,997 | 6,737 |
| text nodes, whole page | 17,752 | 15,257 |

**A correction to R-1's own numbers, in passing.** The audit recorded five skill rows of 624 options. The
page now measures **ten** `skills[N][skill_id]` selects and ten `skills[N][status]` selects, 6,270 options
between them, which is why the total it reported was 5,284 elements and the total measured today is 8,941.
The row count follows the run's skill list, so the figure is not a drift in the page, it is a count of this
run's rows. Anyone sizing the remaining work should use 6,270, not 3,120.

**The remaining half is the same fix, one file later.** Ten `skill_id` selects over one catalogue collapses
the same way the six did: one open picker, nine hidden inputs, ten `Change row N` links, and the `status`
selects left as they are because they hold four options each and are not the problem. That cut is roughly
5,600 option nodes, larger than what landed here, and it lives in `resources/views/runs/show.blade.php`,
which is still held. It also needs the peer's `h-11` pin re-pointed at whatever replaces the select, so it is
a coordinated change rather than a quiet one.

Tests: `RunDeckTest` 21 passed / 66 assertions, including one new pin that counts one open list, five hidden
fields and five switch links. One existing assertion was re-pointed rather than removed: it proved the
`old()` rehydration by looking for `value="X" selected` on the second slot's option, and under the new
markup the rehydrated slot is closed and posts a hidden value, so it now asserts the card name the Trainer
reads back and the field and value the next submit carries. The claim is unchanged; the markup it was read
off is not. Peer's three untracked `RunView*` tests, `RunWriteAtomicityTest`, `FrontendComponentLibraryTest`,
`DesignTokensTest` and `RenderedCopyHygieneTest` all pass against the change: 42 passed / 171 assertions with
2 pre-existing skips. Pint clean, PHPStan level 6 clean, `composer lore` unchanged at 187 hits and 76 exempt
lines.

---


## Completion backlog

Ordered by what unblocks what, not by size.

**Shipped 2026-10-03, same day as the audit.** C-1, C-2, C-3 and C-4 (the whole create-page set that was
not a design decision), in `resources/js/trainee-combobox.ts` and `resources/views/runs/create.blade.php`,
with pins in `tests/Feature/TraineeSelectorTest.php`. C-5 stays `needs-design-decision`, and the run-page
half is held, for the reason below.

Later the same day, the Part 4 sweep: I-1 (the import upload path), I-2 (the `*` and `(optional)`
convention on create, import and the race panel), and I-3 (the `Recreation` label, backlog item 7), in
`resources/views/runs/create.blade.php`, `resources/views/runs/import.blade.php`,
`resources/views/components/race-panel.blade.php` and `TrainingRunController::turnChoices()`.
Verified in the browser on the live pages, not from the source. Gates after the full set:
`TraineeSelectorTest` 28 / 182, the guided-turn family plus `FrontendAuditFixesTest` and
`DesignTokensTest` 58 / 247 with 2 pre-existing skips, `GuidedStepScenarioCompositionTest` 36 / 207,
`npm run typecheck` clean, Pint clean, PHPStan level 6 clean, `composer lore` unchanged at 187 hits and
76 exempt lines. No test was deleted, skipped or weakened.

Then Part 5's local-corpus pass removed one contested number from two more places: the `Rest` detail line in
`TrainingRunController::turnChoices()` and the low-energy hint in
`resources/views/components/guided-step.blade.php`. Measured on the live run page afterward: no `+30` string
anywhere in the page, the new detail line renders, and 43 guided tests pass with Pint, PHPStan and the lore
gate all still clean.

**Held on file ownership, not on work.** Almost every remaining item lands in one file:
`resources/views/runs/show.blade.php` (items 10, 11, 12, 13 and 15's redirect targets, plus R-5, R-6, R-8,
O-2, O-3 and O-4), and `resources/views/components/layout.blade.php` (item 1's theme control and the
sticky rule). Both are modified and uncommitted in this shared worktree while another session finishes a
touch-target and no-script pass on the same page, with three new untracked tests beside it
(`RunViewErrorEnvelopeTest`, `RunViewNoScriptTest`, `RunViewTargetSizeTest`). Two sessions rewriting one
Blade file is how a pass gets silently reverted, so these wait for that work to land. Item 6 is held for
the same reason from the other side: the word lives on `SkillAcquisition`, and `SkillController.php` and
`SkillSeeder.php` are open in the same tree.

**Data first, because four UI items are blocked on it.**

1. Seed the Unity Cup race slot set and populate `scenario_key` on every slot (O-5, R-7). Without this
   the calendar cannot mark scheduled, run or goal races honestly.
2. Add run-level capture for team rank, league placement, burst counts and team-race rounds (O-6, O-7).
   Needs a schema proposal with a PRD citation before code, which makes it an Architect item.
3. Add per-card deck state: level, limit breaks, bond, hint level (O-8). Same gate.
4. Add a per-stat cap field, or derive the cap and show its source (R-3).
5. Add the goals list as data: name, deadline, state (O-12).

**Copy and semantics, cheap and independent of the above.**

6. Rename `Suggested` to `Starting` for the KI-33 seed and reserve the word for hints (O-11).
7. ~~Remove `[Unverified]` from the Mood adjustment label and settle the label from the running research
   pass (R-6, O-9).~~ **Done 2026-10-03 as I-3: the client string is `Recreation`, read off the action row
   in three July 2026 captures, and the flag is off that row.** The `[Unverified]` wording R-6 pointed at
   is the controller's detail line, which now names what the action does instead.
8. Replace the stale hint-discount disclaimer with the ladder now recorded in the corpus (R-6).
9. State whether `fans` and `energy` mean a total or a delta, on the field (O-3).

**Layout, after the section nesting is fixed.**

10. ~~Stop pinning Stats and Mood; pin the Resources strip only (O-2, blocker).~~ **Done 2026-10-03:
    `f5a91b2` (view) and `5110c3b` (frame pin re-point). The pinned region measures 176px, 22 percent of
    800 and 24.4 percent of 720, down from 524px. `lg:contents` on the Run state wrapper keeps the sticky
    child's containing block the page, so the strip stays pinned over the log.**
11. Fix the section scoping so each panel is addressable, then move the stats explanation beside the
    values and Mood into the Resources row (O-4). **Partially done 2026-10-03 (`f5a91b2`): each panel is
    its own section (Run state, Resources, Stats, Skills, Race calendar, Turn log) and Mood sits with the
    strip values. The stats explanation reflow is deferred: that text lives in the read-only `x-stat-band`
    component, outside this dispatch's fence.**
12. Deck as six tiles with a locked state and one reset action (O-8). **Open; needs a design decision.**
13. Skills as a budgeted, grouped, filterable panel rather than the stacked selects it has (O-11). Ten
    rows, not the five R-1 counted; see Part 6. **Open; the option flood is cut (see item 14), the panel
    design is not decided.**

**Reuse, the single highest-leverage code change.**

14. ~~Extract the create page's combobox into a shared component and use it for the deck and skill
    pickers (R-1, R-4).~~ **Landed in a different shape, and only half of it was reachable: Part 6.** The
    deck half is in (1,512 option nodes to 252, page from 8,941 elements to 7,696). **The skill half landed
    2026-10-03 (`e0aa029`, `8faea29`): one open picker, nine hidden inputs, nine switch links, the ten
    whole-catalogue selects' 6,270 option nodes down to 624, page elements 7,696 to 2,111.**
15. ~~One flash partial on every save redirect (R-5).~~ **Done 2026-10-03: `04244a4` (status region and
    controller messages), `b27328e` (test `RunSaveConfirmationTest`).**

**Research pass landed 2026-10-03 (activity labels and the Energy gauge).** What it settled, and what it
did not:

- **Settled:** the action row is six buttons and their words are `Rest`, `Training`, `Skills`,
  `Infirmary`, `Recreation`, `Races`. Three of those are our own client captures, so the label question in
  item 7 closed on first-party evidence rather than on the guides. The pass also confirmed the repo's own
  captures, not just the guides, since it returned the same six from uma.guide and umamusu.wiki.
- **Not settled, and it blocks item 10's colour decision:** the Energy gauge. The only published
  description anywhere is "rainbow-colored bar" (umamusu.wiki, edited 2026-09-28), no source gives a
  colour threshold, a warning string or a grading rule, and the numeric-value announcement of 2025-10-28
  could not be reconfirmed in any post-rework source. Our July 2026 captures read the bar as length plus
  gradient with no number, which is the O-10 answer the HUD capture still owes.
- **Not settled:** whether the Infirmary costs anything beyond the turn, and whether the rest and outing
  outcome names ("Sleep Deprived", "Well-Rested", "Riverside Stroll", "Shrine Date", "Karaoke") are client
  strings or guide paraphrase. Two independent guides agree on the words and no string table was reachable,
  so none of them may be promoted into UI copy yet.
- **New, from the same pass, and not applied:** the three items in I-3. The `Rest` "+30" line is contested
  by a post-rework Global probability table, the client's `Infirmary` and `Races` actions have no key in
  `turnChoices()`, and `Motivation` is not a Global client word in either the official corpus or our own
  seeded `support_effects` row 2.

**Revised verdict: Fail on the run page.** The create flow still passes with warnings. O-2 and O-5 are
blockers: one makes the page hard to work in at the widths the sticky rule was written for, the other
means the calendar shows races this run never had.

## Plan from here, in the order that unblocks things

1. **Wait for the run-page pass to land**, then re-measure O-2 before touching the sticky rule. Re-measured
   2026-10-03 after Part 6, at 1280x800 with the rule active: the `Run state` section is still **524px, 66% of
   the viewport**, so the peer's touch-target work did not move it and the blocker's number is stable. Its
   children measure 28 (heading) + 91 (Resources strip) + 28 + 216 (Stats band) + 28 + 25 (Mood row), which
   says the fix in item 10 takes the sticky region from 524px to the 91px strip plus its padding, about 14% of
   the viewport, comfortably under the 25% this audit asked for. That last figure is arithmetic on the numbers
   above, not a measurement of a change that has landed. The file is still open in the peer session.
2. **Run-page items 10 to 13**, in that order, each with a browser measurement after it rather than before
   it. Part 6 already took the deck half of item 14; the skill half is the biggest single cut left on this
   page (about 5,600 option nodes) and it needs `show.blade.php` plus an agreed answer on what happens to
   the `h-11` pin that currently holds those pickers as selects.
3. **Two items the research pass opened, both gated on a decision rather than on work:** an `Infirmary` and
   a `Races` choice (Part 5 supplies the Infirmary's numbers, so the decision is now a PRD citation for new
   behaviour rather than a search for data); and the `Rest` recovery tier, which Part 5 shows is contested
   *inside this repository*. The number came out of the two Trainer-facing copies instead of being
   re-picked, and the reference row waits for the owner's Source Conflict Log entry.
4. **Ask the repository before the web.** On every question this pass could answer twice, the local files
   answered with a line number and the web pass either arrived late at the same place or was wrong: both
   withdrawn claims in Part 5 came out of the web pass.
   `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` is the transcribed publisher tables,
   `docs/research-scratch/screenshot-notes/` is the client reads, and `database/seeders/data/` is nine
   committed exports. Grep those three before opening a browser, and treat a web answer as evidence only
   where they are silent.
5. **Surfaces this audit never opened,** in the order a Trainer meets them: the run index, the import
   preview step (the half that parses a real sheet, where I-1 lived), then catalog, skills, support cards
   and review. The convention sweep in I-2 found defects on the two forms it looked at, so the same three
   checks (marker convention, one name per field, and no native constraint stricter than the server rule)
   are worth running across the rest.

## Part 7: run-page fixes applied (2026-10-03)

Part A landed the WCAG 2.2 AA pass that was in the tree but never committed. Part B then took the run-page
items that were held on file ownership. Every figure below is a measurement or a test run, not a projection.

### Landed

| Item | Commit | File | Test that closes it |
|---|---|---|---|
| L-F01 nav target size | `39a6cea` | `resources/views/components/layout.blade.php` | `RunViewTargetSizeTest` |
| F-04 error envelope | `a3e323c` | `resources/views/runs/show.blade.php` | `RunViewErrorEnvelopeTest` |
| F-01 no inline JS on the run route | `ecae77d` | `resources/views/runs/show.blade.php` | `RunViewNoScriptTest` |
| WCAG test gates | `db48655` | the three tests | themselves |
| O-2 pin the Resources strip only | `f5a91b2`, `5110c3b` | `show.blade.php`, `RunViewFrameTest` | `RunViewFrameTest` (re-pointed), measured 176px |
| O-4 section nesting and Mood placement | `f5a91b2` | `show.blade.php` | `RunViewFrameTest` pins preserved |
| R-8 duplicate submit label | `767de93` | `show.blade.php` | `SkillsFetchTest` |
| R-6 stale hint-discount disclaimer | `595cb25`, `4f76ecf` | `show.blade.php`, `SkillsFetchTest` | `SkillsFetchTest` (re-pointed) |
| R-6/O-11 copy: Suggested to Starting | `b1814cc` | `lang/en/uma.php`, `show.blade.php`, `TrainingRunController.php` | `RunSkillRowLabelsTest` |
| R-5 save confirmation | `04244a4`, `b27328e` | `show.blade.php`, `TrainingRunController.php` | `RunSaveConfirmationTest` |
| Item 14 skill half: picker collapse | `e0aa029`, `8faea29` | `show.blade.php`, `RunViewTargetSizeTest`, `RunSkillPickerTest`, `RunSkillRowLabelsTest` | `RunSkillPickerTest`, both re-points |

Part A commits: `1a87e2c` (mandate to WCAG 2.2 AA), `39a6cea`, `a3e323c`, `ecae77d`, `db48655`. The two
folded review records are not separate files; `docs/research-scratch/INDEX.md` records them as folded into
`AUDIT-AND-VERIFICATION.md`, which is where the L-F01 to L-F07 numbering and the F-02 supersession note
live, so no duplicate file was created.

### Measured

- **O-2.** Pinned region 524px to 176px. At 1280x800 that is 22 percent; at 1024x720, 24.4 percent. The
  children are heading 28, strip 92, Mood row 20. The strip stays pinned at scroll 600 (bottom 176) and no
  focusable control in the Turn log is fully behind it.
- **Item 14 skill half.** Element nodes 7,696 to 2,111; option nodes 6,737 to 1,121; the ten
  whole-catalogue selects' 6,270 options to 624.
- **Domain fingerprint.** `04870f28...` before Part A and after Part B, unchanged. No domain table was
  written.

### Corrections folded in

- The `Race calendar` section now gates on `panels.race_calendar`. A static wrapper added the label for
  every scenario and broke `GoalPanelsOnRunDetailTest`'s absence checks for Trackblazer and Our Grand
  Concert (G-34).
- The hint-ladder copy uses commas, not slashes. The slash form (`10 / 20 / 30 / 35 / 40`) tripped the
  Grade Point absence pin's `\d+ / \d+` regex in `GoalPanelsOnRunDetailTest`, a read-only file, so the
  copy moved rather than the pin.
- `RunViewFrameTest`'s sticky pin, `RunViewTargetSizeTest`'s two skill-row selectors,
  `RunSkillRowLabelsTest`'s row and sizing pins, and `SkillsFetchTest`'s copy pin were re-pointed, each with
  the original claim stated in the test and the commit. No claim was weakened or removed.
  `RunSkillRowLabelsTest` and `GoalPanelsOnRunDetailTest` sit outside the dispatch's write fence but pin the
  exact shape this dispatch changed; the re-points are recorded here as a fence deviation.

### Still open

O-11 (skills panel design), O-8 (deck tiles and per-card state), O-12 (goals surface), O-3 (fans/energy
total or delta), O-5 and R-7 (seed 406 unscoped race slots; Architect pass), R-2 and R-3 (schema), C-5
(validation fields), the Infirmary and Races choices, and the Rest recovery tier. None of these changed.

---

## Part 8: quick wins applied (2026-10-03)

Dispatch A's scope, stated in the dispatch itself as no schema and no PRD: the Rest recovery doc
edit, the O-3 label disambiguation, the C-5 four-field surface on the create form, and the O-12
goals panel. Each lands in its own commit.

### Landed

| Item | Commit | File | Test that closes it |
|---|---|---|---|
| A.1 Rest recovery tier | `24ba395` | `UMAMUSUME_REFERENCE.md` §1.1.5, `SCENARIO-PUBLISHER-REFERENCES.md` §2.1 note | doc-only, no test gate |
| A.2 / O-3 fans and energy labels | `48544ca` | `resources/views/runs/show.blade.php` | the existing `ResourceStripOnRunDetailTest` and `ResourceStripTest` pins still pass on the longer labels |
| A.3 / C-5 four create fields | `b411fd0` | `resources/views/runs/create.blade.php` | `RunCreateSurfaceTest` (new file, three tests) |
| A.4 / O-12 goals surface | `6f7e738` | `resources/views/runs/show.blade.php` | `RunGoalsPanelTest` (new file, four tests) |

### Premise 3, the domain fingerprint

The dispatch opened expecting `04870f28...`, the value recorded at Part B end. Measured value at
Dispatch A start was `87164db8f03e02d6523c144cfbe9606ac383e1aeb197144c7eca1e3e10ce4d70`. The
mismatch is not a schema write from Dispatch A, because Dispatch A writes no rows.

Resolution attempt: the audit doc records the expected value but does not record the method that
produced it. The prior dispatch's own summary named a "per-table INSERT dump ordered by rowid",
which hashes row data, but the current method (`sqlite_master.sql` + `PRAGMA table_info` per
table) hashes schema metadata. Neither reproduces `04870f28...` against today's DB, nor do five
other data-dump serialisations tried (pipe, csv, serialize, json, kv) against either the all-table
set or the domain-only set. The expected value is unreproducible from what is on disk today.

Owner ruling 2026-10-03 (this dispatch, pre-work): the premise conflated "fingerprint at dispatch
start" with "fingerprint recorded at a prior dispatch end". The guard is the within-dispatch
delta; the cross-session value is a stale reference. Re-baseline to the current schema-dump
`87164db8...` and proceed. Dispatch A re-computes at the end of Stage 5 using the same method;
delta must be zero.

Amended template for the next dispatch: *domain fingerprint computed at the start of this
dispatch; compare at the end; any delta is a finding*. Not: *domain fingerprint matches a value
recorded at the end of a prior dispatch*.

### Out-commits recorded

Two of the four stage commits swept edits that were already on disk at dispatch start.

- `24ba395` (Stage 1) carries, in `UMAMUSUME_REFERENCE.md`, the prior session's in-client hint
  discount closure at §1.1.4, the Growth Rate owner identification at §1.3.5, the two §8.4 closed
  gap lines, and the source-path renames that point §2.5 and §2.6 at `SCENARIO-PUBLISHER-REFERENCES.md`.
  In the same commit, `SCENARIO-PUBLISHER-REFERENCES.md` grows by 1,262 lines of source expansion
  the prior session made but never committed. All of it landed at one SHA because line-level staging
  against a tree with no active peer was not authorised.
- `b411fd0` (Stage 3) carries the prior session's C-2 caption edits in the same `create.blade.php`:
  "Umamusume" → "Trainee *", "Status" → "Status *", `aria-required="true"` on the combobox input,
  and the caption rationale that goes with them.

Both sweeps are stated in the commit body. The alternative was to leave the prior session's work
uncommitted across the rest of the dispatch, which the owner's "no peer on the board" ruling
argues against. The next session that reads git log will see those edits under a Dispatch A label
rather than under their own C-2 label; the bodies name the sweep so the audit trail is honest.

### Stage 2 deviation, named

The dispatch named Stage 2 as "label both as deltas". The turn-form fields on the run page take the
client's post-turn reading, not a delta: the controller line
`app/Http/Controllers/TrainingRunController.php:447` computes the per-turn change by subtracting
the previous turn's stored value from the entered value. Switching the field to take a delta
itself would need a controller edit, and the controller is outside this dispatch's fence. The
labels read "Energy (after this turn)" and "Fans (after this turn)", which is what the fields
actually take, and the Blade comment names the preview computation. The ambiguity O-3 found is
resolved on the field. Reconciling the dispatch's wording to the field's shape is recorded here
rather than papered over.

### Stage 4, the grade-point-meter decision

The dispatch's fence included `resources/views/components/grade-point-meter.blade.php` and named
Stage 4 as generalising the component to an objective-list. The goals panel's data shape is
different from the meter's (race state, year, turn countdown vs. points ladder, target, current
sum), so the goals section was rendered directly on `show.blade.php` rather than shared through a
generalised component. That is the smaller diff and it does not risk the Trackblazer meter's
existing test pins. If a third panel lands that needs the same list pattern, the abstraction
becomes worth its cost.

### Suite state after Stage 5

- **Start:** 1,123 passed, 2 skipped, 17,982 assertions, 82.29s, exit 0.
- **End:** 1,130 passed, 2 skipped, 18,014 assertions, 100.05s, exit 0. The seven new tests are
  Stage 3's three in `RunCreateSurfaceTest` and Stage 4's four in `RunGoalsPanelTest`. No test was
  deleted, skipped, or weakened.
- **Domain fingerprint.** `87164db8f03e02d6523c144cfbe9606ac383e1aeb197144c7eca1e3e10ce4d70` at both
  Stage 5 start and end. Delta is zero, which is what the guard was set up to catch.
- **One in-flight fix landed in Stage 5's commit.** Two `TraineeSelectorTest` xpath assertions at
  lines 309 and 376 broadened `//option[@value="N"]` across the whole page. Stage 3's two new
  inheritance-parent selects reuse the Global trainee list, so the broad pattern started matching
  three rows per trainee and both assertions failed. Each was scoped to
  `//select[@name="umamusume_id"]/option[@value="N"]`, the trainee select the test was actually
  about, with a comment naming the C-5 selects as the reason the scoping matters. That tightens
  the original claim rather than weakening it.

### Still open

O-11 (skills panel), O-8 (deck tiles and per-card state), O-5 and R-7 (the 406-row unscoped race
catalog; Architect), R-2 and R-3 (schema), the Infirmary and Races choices. None of those landed
here; they were in Dispatch A's held-or-future list, not its scope.
