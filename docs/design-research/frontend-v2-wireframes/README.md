# Trainer Desk v2.0.0 — full frontend wireframe set

Static HTML wireframes of every screen in the running app, rebuilt against the UX-law and UI-law
findings. **"Working" means the navigation, the layouts and the states are real; nothing is wired to a
database.** No component, token, copy string, gate or test in the running app was changed.

Open [`index.html`](./index.html) and navigate. Every page shares one shell and one stylesheet, so a
click from the dashboard reaches the whole surface.

## What this set covers

49 screens: the 48 that match `SCREEN_SPEC.md`'s registry rather than a hand-picked subset, plus
`help`, which the usability heuristics required and the registry does not yet carry.

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
| System | `preferences`, `help`, `error-404`, `error-419`, `error-500` |

## The law inventory this set answers to

Read from the two sources on 2026-10-21, because a wireframe that cites a law by the wrong name
cites nothing. They are **two different collections** and the difference matters:

- **Laws of UX** — `lawsofux.com`. **28 entries in the index, plus Zeigarnik Effect**, which has its
  own page but is truncated out of the index listing. Total **29**. The index gained **Cognitive Bias**
  and **Paradox of the Active User** since the repository's original 27-row audit.
- **Laws of UI** — `uilaws.com`. Exactly **13** entries, and it currently lists **Continuity** and
  **Closure**, the two Gestalt principles the repository previously had to attribute to nobody.

Attribution corrections carried here:

| Claim | Reality |
| --- | --- |
| Continuity, Closure were "laws of UX" | They are **Gestalt principles published by uilaws.com**, not lawsofux.com entries. |
| Common Fate was a published law | **Neither site publishes it.** It is Gestalt, and any use here is cited as a principle, not a law. |
| A "Law of Experience" | **No such law exists** on either site. The concept is Prior Experience, a sourced principle. |
| "Aesthetic consistency and visual hierarchy" as law 27 | A **house principle**, not a law. |
| Proximity, Similarity are uilaws-only | Both also publish them; the wireframes cite lawsofux.com's page. |

## What each law changed in this set

| Law | Source | How the wireframes apply it |
| --- | --- | --- |
| **Fitts's Law (3)** | both | **Every control is 44px minimum.** The app ships `h-11` for all of them (`CapsuleHeader.vue`, `TraineeCombobox.vue:467`, `VeteranRow.vue:79`) and `SLICE-RECORDS.md:4716-4730` measures 44px in a browser rather than reading it. An earlier draft used 40px for `.btn`, 32px for `.btn-sm`, 34px for `.step`, 38px for `.field .in`; all are 44px now. |
| **Zeigarnik Effect (19)** | lawsofux | **The wizard step bar carries four states, not two** — not started, started-unfinished, saved, current — each with a glyph as well as a colour, plus a legend. The law's own takeaway is "provide a clear indication of progress", and the incomplete state is the one worth seeing. The shipped wizard fails here: `SetupLayout.vue:45-46` marks every step `done` while nothing has been entered. |
| **Law of Uniform Connectedness (9)** | lawsofux | **The inheritance diagram is drawn.** Six ancestors read as one line of descent only if the connection between generations is visible, so a rule between columns carries it and the diagram is not six boxes. Continuity on uilaws makes the same point. |
| **Goal-Gradient (18)** | lawsofux | The career goal is in the first viewport on every run screen. The build-target step exists so that bar has a denominator. |
| **Hick's Law (4)** | both | Four scenario cards, not a dropdown. Ten nav destinations grouped into four bands. |
| **Miller's Law (5)** | lawsofux | Three aptitude letters per card on an index, nine on the detail. Chunked, not trimmed. |
| **Von Restorff (15)** | lawsofux | One filled action per screen. An unmarked figure is the norm, so only exceptions carry a provenance mark. |
| **Serial Position (16)** | lawsofux | The primary action leads; destructive actions sit last, behind a disclosure. |
| **Peak-End (17)** | lawsofux | The result screen leads with the outcome and states the honest finding. |
| **Doherty Threshold (23)** | lawsofux | Every write has a pending label and a saved confirmation. |
| **Jakob's Law (2)** | both | Client grammar reused where it exists: four break diamonds, the gauge at 80 turning orange. |
| **Prägnanz (10)** | lawsofux | One vocabulary, one owner: Performance types come from the enum, not from the template. |
| **Occam's Razor (21)** | lawsofux | One save bar, one verb. Seven save verbs in four styles is the defect this replaces. |
| **Common Region (8)** | lawsofux | One field grid, one left edge, three fieldsets. |
| **Proximity (6)** | both | Every field label sits inside its own `<label class="field">`, bound to the input — never floating above it. |
| **Similarity (7)** | lawsofux | One `.btn`, one `.pill`, one `.tag` vocabulary. The same control in two places is the same class. |
| **Selective Attention (33)** | lawsofux | The goal-relevant datum wins. Mood is word + arrow + percentage, because at equal luminance `GOOD` and `BAD` are 3.3 degrees of hue apart (`D-259`). |
| **Chunking (28)** | lawsofux | Stat bands are five plus four tiles; the DB counts are grouped, not one flat run. |
| **Cognitive Load (24)** | lawsofux | Recognition over recall: the advisor prints the number the decision needs rather than a pointer to another screen. |
| **Choice Overload (25)** | lawsofux | Defaults and one recommendation at each decision point. |
| **Tesler's Law (20)** | lawsofux | The app absorbs complexity: no internal model leaks into a label. |
| **Pareto Principle (22)** | lawsofux | The few facts that drive the decision sit first; the long tail is behind a link. |
| **Parkinson's Law (31)** | lawsofux | Nothing fills an empty layout. No invented controls for symmetry. |
| **Postel's Law (32)** | lawsofux | The importer accepts liberal input and writes canonical enum labels out. |
| **Mental Model (29)** | lawsofux | The app's nouns are the client's nouns: SP, Mood, Bond, Spark, Trainee Umamusume. |
| **Paradox of the Active User (30)** | lawsofux | Every screen works without a manual. Reversibility is stated before the commit. |
| **Aesthetic-Usability (1)** | lawsofux | Hierarchy and spacing are deliberate, but polish never substitutes for a sourced number. |
| **Flow (26)** | lawsofux | One screen carries the run; the career nav is a stepper, not a second tab bar. |
| **Closure (12)** | uilaws | Partial indicators stay honest. An empty grandparent slot reads "slot open" rather than being closed up or hidden. |
| **Typography Hierarchy** | uilaws | Three steps, and three is the measured count: 28px page title, 15px section heading, 12.5-13px body. A card's sub-label is a labelled paragraph, not a heading, and an empty state's heading is the page's `h2`, so no fourth step exists. The shipped app's 75 headers at `text-base` is the defect this avoids. |
| **Consistency / Contrast** | uilaws | One selection grammar on every picker; colour never carries meaning alone. |
| **D-45 (Nielsen H1/H9)** | — | Four states on every data view: empty, loading, populated, error. `.state`, `.skel` and `.state-error` are the primitives. |
| **D-259** | — | Mood renders `GREAT ↑ +20%`. The arrow is the only part carrying ordinal direction. |

Laws this set deliberately does **not** claim: **Symmetry** and **Rule of Thirds** (uilaws) are
composition rules for a marketing surface, not a data tool; applying them to a stat table would be
ornament. **Cognitive Bias** is recorded as awareness, not as an applied rule, because every actual
use of it in an interface is either an honest warning or a dark pattern, and this tool does not
manipulate.

## Every page names its law

All **48** pages carry a law-naming item in their own annotation key, and **23 distinct laws** are
cited across the set. Nothing is a generic list: each item is written against that page's own first
decision. The distribution:

| Law | Pages |
| --- | --- |
| Goal-Gradient | `career-target`, all four `cockpit-*`, `skills-planner`, `veterans` |
| Hick | `career-scenario`, `catalog`, `event-decision`, `race-decision`, `skills` |
| Miller / Chunking | `career-deck`, `catalog-detail`, `database`, `run-deck`, `support-cards`, `support-card-detail`, `training-decision` |
| Peak-End | `career-preflight`, `error-404`, `error-419`, `result` |
| Paradox of the Active User | `career-legacy`, `career-profile`, `career-scenario`, `career-trainee` |
| Proximity | `database-events`, `database-shop-items`, `import-preview`, `review-detail` |
| Closure | `database-races`, `race-decision` |
| Continuity | `inheritance`, `timeline` |
| Uniform Connectedness | `inheritance`, `legacy` |
| Cognitive Load | `runs-index`, `skill-detail` |
| Doherty | `error-500`, `legacy-builder` |
| Jakob | `career-trainee`, `error-404`, `preferences`, `support-card-detail` |
| Von Restorff | `index`, `veterans-compare` |
| Others | Postel (`import`), Zeigarnik (`review`), Serial Position (`veteran-detail`), Selective Attention (`database-scenarios`), Pareto (`database-sparks`), Mental Model (`database-sources`), Contrast (`legacy-compare`), Occam (`preferences`), Fitts (`training-decision`) |

`training-decision` cites **Fitts** and **Miller** together, and `race-decision` cites **Hick** and
**Closure** in one item, because naming one law per page would have understated what those screens do.

## The rail, the marks, and the way back

Three things the app has and this set did not, added 2026-10-21 and corrected 2026-10-22.

### The rail collapses

The app's global navigation collapses to marks instead of words, and the wireframe now
does the same. Mirroring `resources/js/layouts/AppLayout.vue:191-233` and its template
at `:286-345`:

- **Collapsed is marks; expanded is words.** Never both, and never at the same width. The glyph is
  `display:none` at every width except the collapsed one, so an expanded rail shows a word and a
  collapsed rail shows a mark.
- **The word never leaves the DOM.** It moves to `sr-only`, so every destination keeps its
  accessible name, and the item gains a `title` carrying it too.
- **The active destination keeps `aria-current="page"`** plus a left rule on top of its fill,
  because state may never ride on colour alone.
- **The brand stays.** The original `TD` mark and the `TRAINER DESK` word are both in the column
  again; collapsed, the word goes to `sr-only` and the mark stays, so the rail keeps its identity.
- **The control is with the sidebar**, at the head of the column, and it is an **icon**: a chevron
  that points the way the rail will move. The word is `sr-only`, so the icon is not the accessible
  name. It was in the top bar before, which put the control that resizes the rail far from the rail.
  The mark is the control's own class, not a `.glyph`: the nav glyphs are hidden at every width
  except the collapsed one, so a `.glyph` mark left the control empty while the rail was open. The
  chevron points LEFT when the rail is open and can only go left, RIGHT when it is collapsed.
- **The collapsed column stacks its head**, so the mark and the control both fit 56px.
- **The choice is `localStorage`, not a Preference row.** `Preference::KEYS` is exactly `theme`
  and `failure_estimate`, so a third stored key would widen product scope. The cost, stated rather
  than hidden: the rail follows this browser, not this Trainer, and it does not appear on Settings.

The key and the two values are the app's: `trainer-desk.nav-rail`, `compact` and `wide`.

**The collapse changes `--rail-w`, not the rail's width.** `.app` is a grid whose first column is
`--rail-w`, so narrowing the rail inside a column that stayed 236px left the content starting 180px
to the right of the marks: a negative space with nothing in it. Changing the variable moves the grid
column, the rail and the top bar's width together, because the top bar lives in the same column, so
the content fills what the rail gives back.

**And the column is what animates.** `.app` transitions `grid-template-columns` and the rail
stretches to whatever the column measures. Animating the rail's own `width` instead left it 236px
wide inside a 56px column for the length of the transition, so the rail was painted OVER the
content: a measured 180px of overlap. The overlap is now zero at every frame.

**The ten words are one column, and the dot holds them there.** Every destination carries the dot,
the active one among them, so no word is out of line with the others. The group headings take the
same x as the words, so a heading names the group it sits over.

### The stat and type marks

**The marks are the owner's, and the wireframes reference them.**
`docs/design-research/icons_svg/{speed,stamina,power,guts,wit,pal,group}.svg` each remade from
the client's own type chip: a rounded tile in that type's gradient with the white client glyph on
it, with the original PNG beside it in `source/`. The pages carry an `<img>` to those files, so
there is one copy and the file stays the source of truth. An SVG `<use>` across files would not
have worked: it is blocked under `file://` and would have rendered blank.

**The colours are the owner's, and they correct the corpus.** The corpus recorded the legend-row
chip fills as indicative until measured, and it read them as a "brown flexed arm", a "dark-green
cap" and an "olive figure". Measured from the source, they are **amber** `#FFA834`, **green**
`#30CB93` and **gold** `#FFC94D`. Speed is blue `#2F94EE`, Stamina is red `#F05B4F`, Guts is
pink `#F45E96`, Group is green `#26B864`.

**They are not a stat hue, and that is the part worth being careful about.**
`DESIGN-CORPUS.md:371-393` is explicit: the five stats carry **no saturated identity colour**, a hue
per stat collides with the semantic roles (a Guts delta tinted like a validation error is a defect
waiting to ship), and a stat's identity is its **glyph plus a whisper of band tint**. So the chip is
the client's own coloured tile, and the band tint stays a tint, with its own dark values.

**The chip keeps its word.** The mark alone would break WCAG 1.4.1, and the tile is already a
colour, so the word is what carries the name.

Two collisions the corpus records as **unresolved** are carried here unresolved: Mood against the
friend chip, and Fans against the group chip.

### The image slots

**The app puts pictures on eleven of these screens, and this set drew none of them.** The app
renders a mirrored file in a square beside a name: a trainee portrait (`card_portrait`) or a
support-card thumbnail (`support_thumb`), at 40px, 48px or 64px. This set has no mirror and no
database, so each of those cells is now **drawn** as a dashed frame carrying the subject, at the
size the app uses.

| Page | The slot | Size |
| --- | --- | --- |
| `index` | the active career's portrait; each recent veteran | 64px, 40px |
| `career-trainee` | each trainee row | 40px |
| `career-profile` | the trainee's portrait; each costume form | 64px, 40px |
| `career-deck` | each card in a slot | 48px |
| `run-deck` | each card in a slot | 48px |
| `catalog` | each trainee card | 48px |
| `catalog-detail` | the form in view | 64px |
| `support-cards` | each card row | 48px |
| `support-card-detail` | the card | 64px |
| `veterans` | each veteran row | 48px |
| `veteran-detail` | the veteran | 48px |

**A slot the app does not have is not invented.** The catalog index's costume-form row and the
deck picker's option list both carry a 40px frame in the app, and neither has a counterpart on
the wireframe pages that draw those lists, so neither is drawn. An empty deck slot carries no
frame either, because there is no card to picture.

**The frame is a drawing, and the product paints no frame at all.** `DESIGN.md` §4.7 is
explicit: when the mirror holds no file, `ArtworkSlot.vue` renders **nothing** — no grey box, no
placeholder glyph, no broken frame — because absence is a normal state and a broken visual
would claim a defect the tool does not have. So the frame here is `aria-hidden` (the app's own
alt is empty wherever the name prints beside it), the subject inside it is the only thing it
says, and every page that draws one states the rule in its own annotation key.

**The checks own the registry.** `framework-check.mjs` carries the expected set per page, so a
slot that goes missing, a slot on a page that should have none, a wrong kind, a wrong size, a
slot that is not `aria-hidden`, and a page that draws a slot without naming the file it stands
for are all findings. `render-check.mjs` measures the frame in Chromium: a square of 40, 48 or
64, a painted border, and a caption that fits inside it. Each rule was proven to have teeth by
reverting it in turn and watching the check fail with the finding it was written for.

### The way back to the Database hub

Every Database sub-page carried a breadcrumb reading "Database · Sources", with "Database" as a
link. So a way back existed, and that was the problem: the breadcrumb sits in the top bar on the
brand-blue chrome at 0.9 opacity with no underline, so the link reads as a word rather than as a
link, and a Trainer looking for the way back finds the global rail instead.

Two changes fix it. A **sub-navigation strip** of the nine areas now sits at the top of the canvas on
every Database and library page, with the current one marked by a word weight AND a fill. And the
breadcrumb's "Database" is now visibly a link.

This is Information Architecture principle 7 (focused navigation: the nine areas are one family, so
they are grouped as one family) and principle 5 (front doors: a Trainer who arrives at a library
page directly finds the whole reference set, not a dead end).

**One list, once.** `catalog.html` already carried a "Database areas" stepper that listed the same
nine, so it had the sub-nav **and** the stepper. The stepper went: the same list twice on one screen,
with two different labels for the same place, is the defect the wizard had.

### One place for the position and the action

The wizard carried "Step 1 of 6" in the save bar at the bottom of the page while the stepper sat at
the top, so a Trainer was told where they were twice, in two places, one of them below the fold.
Across the seven wizard pages the position appeared in **three** different places: the save bar, the
breadcrumb, and nowhere at all. The action buttons were in the save bar on four pages, in the page
head on two, and down in a "Commit" card on one.

**Every wizard page now carries ONE row, in the page head, on the title row above the rule: the
position, then the actions.** The breadcrumb keeps the trail and loses the step, because a trail and
a position are different facts. The save bar is gone from the wizard pages.

The position text is also repaired. It read "Step 1 6" and "3 6 · targets entered", because the word
"of" was lost when the pages were first generated, and that defect was still in the committed set.
It reads "Step 1 of 6" and "Step 3 of 6 · targets entered" now.

**On Settings, the save action moved up too.** "Save changes" belongs beside the page's position,
because the page's whole job is that one action, so the save bar leaves the page head and the status
line moves with it.

A wizard is the one flow where the action must never be below the fold, and this is the WCAG 2.4.1
and Shneiderman 4 (closure) case for it: the position, the action and the page title are read in one
glance.

### The re-check, and the two tools that run it

`docs/framework-ui-ux/design-framework-index-2026-10-21.md` is the checklist a change is held against
before it is committed, and it names the procedure. Four scripts run the mechanical part of it:

- `framework-check.mjs` — the checkable rules, over all 49 pages and the stylesheet.
- `link-check.mjs` — every internal link and image, resolved against the page's own directory.
- `render-check.mjs` — the rules a static read cannot decide, measured in a rendered frame.
- `framework-audit.mjs` — the framework's own rules that only a painted frame can decide: contrast
  per text node in both themes, the effective 44px target, the heading ladder, the accent dose,
  reflow at 320px, text resize and spacing, and focus visible measured by tabbing.

All four are in this directory and all four are run before a commit. On 2026-10-22 the first run of
`framework-check.mjs` found a real regression: the CSS edit that moved the rail's control had
dropped the `.rail.is-collapsed .nav-label` rule, so the word would have stayed visible beside the
mark in a 56px column. It also found the sub-navigation items at 34px, under the 44px target rule,
and a bare "Continue" that did not name its destination.

### The full framework audit (2026-10-22)

`docs/framework-ui-ux/` is ten documents. Every one of them records the same limit in its own words:
"Chromium cannot launch under this repository's sandbox (`spawn EPERM`), so no verdict in this file
or the files it feeds is a rendered observation." **That limit is false on this host.** Chromium
launches and a `file://` page loads without a server, so the rules the framework left unmeasured
were measured, and the ones that failed were fixed rather than filed as unverified.

`framework-audit.mjs` is the result. It renders all 49 pages in **both themes** and reports:

```
=== FAILURES ===
none
=== 49 pages x 2 themes, 0 findings ===
```

What it found, and what changed. Every one of these was invisible to `framework-check.mjs`, which
reads markup and CSS as text:

| Rule | Measured before | Root cause | Now |
| --- | --- | --- | --- |
| **WCAG 1.4.3** text contrast | **18 distinct failures, 303 page-hits** | `--ink-faint` carried LABELS in seven places. The app's own stylesheet says that token "must never carry a label" (`resources/css/app.css:88-92`); it measures 3.07:1 on the panel. The worst was the "Static wireframe" chip at **1.74:1** on the chrome. | 0. Labels take `--ink-muted` (5.17:1); the chrome chip takes `--on-brand-chrome` (5.51:1) |
| **WCAG 1.4.10** reflow | **45 of 49 pages scrolled sideways at 320px** | the top bar was `flex-wrap:nowrap` with four fixed children, measuring 360px in a 320px viewport. The table cards were `overflow:visible`, so a 454px table pushed the page to 487px. | 0. The bar wraps and drops its annotation chip and status pill below 520px; a card with a table scrolls it inside itself |
| **WCAG 2.4.7** focus visible | **bare inputs, selects and a textarea painted nothing** | the set had per-component focus rules and not the app's base rule (`resources/css/app.css:524-528`). A `textarea` also carried an inline `outline:none`, which suppresses the browser's own fallback ring. | 0. A base `:focus-visible` rule, plus the chrome's white ring on the chrome |
| **UX-1** 44px targets | `.rail-foot` 41px, `.skip` 37.5px, `btn-sm` 38.2px wide, field controls 19.5px, radios 13-16px | the app uses `min-h-11` on each of these (`AppLayout.vue:341`, `BaseInput.vue:105`, `BaseRadio.vue:72`); the set used padding and inline sizes instead | every effective target is 44px or more |
| **UI-1** three type sizes | **four steps**, and the third was the same size as the body | a card's `h3` sat at 13px, the body's own size | the ladder is three: `h1 28px`, `h2 15px`, body. A card's sub-label is a labelled paragraph; an empty state's heading is the page's `h2` |
| **UI-7** one accent per screen | **the accent did three jobs on 22 pages** | it filled the active destination, marked the provenance chip, and marked the recommendation | one accent element on every page. Selection wears the chrome's 10% tint (`AppLayout.vue:239`); provenance wears ink weight |

Three **false claims** in the set contradicted its own render and were corrected:

- `index.html` said "'Open cockpit' is the only filled button in the top region" while "New career"
  was also `btn-primary` 150px above it. Two filled actions make the accent mean "a button" rather
  than "the button", which is the page's own Von Restorff argument. "New career" is now secondary.
- All 49 annotation keys said "The control is in the top bar because the bar is the one row present
  at every width". The control moved into the rail's head; the claim was stale on every page.
- The "Accent discipline" line described three roles and left out the accent's own three.

**Every rule in `framework-audit.mjs` was proven to have teeth**: reverting each fix in turn makes the
audit fail with that rule's own finding. One case taught something worth recording — removing a focus
ring is *not* a 2.4.7 defect, because the browser falls back to its own `outline: auto`. The defect is
`outline: none`, which suppresses that fallback, which is why the rule is written to ask whether
anything is PAINTED rather than whether `:focus-visible` matches.


### The rail, audited in a rendered frame (2026-10-22)

**Chromium launches in this tree.** The framework index records that "Chromium cannot launch under
this repository's sandbox (`spawn EPERM`)" and that every rule depending on a rendered frame was
therefore unmeasured. That is not true on this host: Playwright's bundled Chromium runs, and the
49 pages render. The rail was rendered and measured, and **five defects were found that the static
check could not see**, because each one is a fact about a painted frame rather than about markup:

| Defect | Measured | Root cause |
| --- | --- | --- |
| **The collapse control rendered empty.** With the rail open the control was a 44px box with no mark in it, on all 49 pages. | the mark's box was 0x0 | the mark was a `.glyph`, and the nav glyphs are `display:none` at every width except the collapsed one. The control needs a mark class of its own. |
| **The rail was painted over the content while it resized.** | 180px of overlap at t=1ms | the rail animated its own `width` inside a column that had already moved to 56px. The COLUMN has to animate and the rail stretch to it. |
| **The active destination's word was 16px left of the other nine.** | active word at x=15, the rest at x=31 | the active item's dot had been dropped from the markup, and the dot is what holds the words in one column. |
| **"Data sources" wrapped onto three lines in the collapsed column** and overflowed its 59px box. | 3 lines, 58px of content in a 59px box | the collapsed rule targeted `.rail-foot a`, an anchor INSIDE `.rail-foot`, and the markup is a single anchor that IS `.rail-foot`, so the rule never matched. |
| **The collapse control's tooltip never changed.** | it said "Collapse" while the action was "Expand" | the `title` was hard-coded in the markup and the script updated `aria-label` and the `sr-only` word but not the title. |

Two smaller ones: the group headings sat 31px left of the words they name, and the chevron pointed
the wrong way (it was drawn to the right, so the OPEN rail needed the 180° rotation, not the
collapsed one).

**The fixes are in `assets/shell.css` and in the 49 pages**, and every one of them is now a rule in
`framework-check.mjs` or `render-check.mjs`. Each new rule was proven to have teeth: reverting each
fix in turn makes the check fail with the finding it was written for. A check that cannot fail is
not a check.

**`render-check.mjs` reports UNVERIFIED, never "fine", when it cannot run.** Where Chromium cannot
launch it says the rail was never rendered and the rules are unmeasured, because a check that cannot
run and still passes is worse than no check.

## The seven frameworks this set answers to

The references are in [`docs/framework-ui-ux/`](../../framework-ui-ux/): one file per framework, each quoted from its
source, plus [`design-framework-index-2026-10-21.md`](../../framework-ui-ux/design-framework-index-2026-10-21.md),
which maps a question to the narrowest framework that decides it and records the seven places where the
frameworks contradict each other and how each was settled.

Every page now carries **three** annotation items, because they answer three different questions:

| Item | The question it answers |
| --- | --- |
| the law, and the annotation around it | which principle governs this screen's composition |
| **Heuristic coverage** | is the screen usable, in Nielsen's ten questions |
| **Framework coverage** | which framework decides this screen's *distinctive* decision |

**Framework coverage, by page group:**

| Framework | Pages | The decision it settles there |
| --- | --- | --- |
| **Tognazzini** (16) | `index`, `support-card-detail`, `career-deck`, `training-decision` | Latency, 44px targets, undo, and labels that lead with the differentiating word |
| **Shneiderman** (8) | `index`, `runs-index`, `career-preflight`, `career-scenario`, `cockpit-ura-finale`, `cockpit-grand-concert`, `training-decision`, `import`, `review`, `result`, `preferences` | How a flow behaves from open to close, and where consistency may be broken |
| **Gerhardt-Powals** (10) | `career-trainee`, `career-target`, the four cockpits, `skills-planner`, `legacy-builder`, `veterans`, `veterans-compare`, `index` | Whether the product left arithmetic, estimation or comparison in the user's head |
| **Information architecture** (8) | `career-scenario`, `career-profile`, `career-legacy`, `race-decision`, `runs-index`, `veterans`, `veteran-detail`, `support-cards`, `skills`, `catalog`, `catalog-detail`, the `database-*` pages, `legacy-*`, `veterans-compare`, `inheritance` | The space between screens: objects, classification, disclosure, growth |
| **Gestalt** (12) | `inheritance`, `run-deck`, `timeline`, `legacy`, `database-scenarios` | Whether two things will be read as one group |
| **Norman** (6) | `career-deck`, `race-decision`, `event-decision`, `run-deck`, `timeline`, `result`, `error-404` | Affordance versus signifier, and the two gulfs |
| **WCAG 2.2 AA** | every page, plus `import-preview`, `review-detail`, `legacy-compare`, the three error pages | The only normative one. See the accessibility section below. |

## Accessibility, as WCAG 2.2 AA

The audit found the set had **no skip link, no `main` landmark and no status-message region on any of its
49 pages**, and one class of button whose accessible name did not contain its visible label. All four are
now fixed:

| Criterion | Before | Now |
| --- | --- | --- |
| **2.4.1 Bypass Blocks** | 0 of 49 pages had a skip link | 49 of 49, visible on focus, never permanently |
| **1.3.1 Info and Relationships** | `<div class="main">` was a class, not a landmark | `<main class="main" id="main">` on all 49, and it is what the skip link targets |
| **4.1.3 Status Messages** | nothing announced a state change | the save bar's status text carries `role="status"` on the 12 pages that have one |
| **2.5.3 Label in Name** | four deck buttons said "Remove Speed card" over a visible `✕` | the glyph is `aria-hidden` and the name follows it in a visually-hidden span, so a voice-control user can say what they see |

**What is still unmeasured, and it is most of it.** Chromium cannot launch under this repository's
sandbox (`spawn EPERM`), so **no contrast ratio has ever been measured in a browser.** `app.css` records
intended ratios and the G-18 gate measures nothing yet, which `DESIGN.md` §2.1 already says. So
1.4.3 Contrast (Minimum) and 1.4.11 Non-text Contrast are **unverified here**, and this file says so
rather than claiming conformance.

**The 44px target is a house rule that exceeds the standard.** WCAG 2.5.8 (Minimum) requires 24×24 CSS px.
This product uses 44px because the app ships `h-11` and Fitts's Law asks for it. It should never be
described as 2.5.8 compliance.

## Usability heuristics

The reference is [`docs/framework-ui-ux/usability-heuristics-2026-10-21.md`](../../framework-ui-ux/usability-heuristics-2026-10-21.md),
which records Nielsen's ten as NN/g states them. Every page carries a **Heuristic coverage** item in its
own annotation key. Three of the ten needed a change to the set rather than an annotation:

| Heuristic | What changed |
| --- | --- |
| **H3 User Control and Freedom** | **An `Undo turn N` control is now drawn** on the three decision screens and on the timeline. The shipped build has no undo after a committed turn, which the audit filed as severity 3. Drawing the missing control makes the gap visible instead of leaving it invisible. |
| **H7 Flexibility and Efficiency** | **The keyboard contract is now on screen.** D-55 already specified "1 to 5 select, Enter confirms, Escape steps back", but nothing displayed it, so the accelerator could only be found by already knowing it. `training-decision` now prints `1` to `5` beside each discipline and states Enter and Escape in the save bar. |
| **H10 Help and Documentation** | **A new `help.html` closes the gap.** Proactive help leads (the turn loop, the keyboard); reactive help follows (the tag vocabulary, the rules worth looking up, what to do when a figure looks wrong). It is reachable from a `?` in every page's top bar and deliberately absent from the rail, which already carries ten destinations. |

The other seven are answered by decisions already in the set, and are recorded per page rather than
restated here. Two NN/g points worth carrying because both are commonly misread:

- **H8 is not the minimalism trend.** A cockpit is legitimately dense. The test is whether every element
  carries information, not whether the page looks spare.
- **H9 never displays an error prematurely.** Flagging a field the Trainer is still filling is the
  hostile pattern NN/g names. A turn is committed once and cannot be re-answered, which makes premature
  error copy here strictly worse than on a form.

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

## The mock catalogue

**The pages are static HTML with no database behind them, so the content they draw has to come from
somewhere. It comes from `assets/data/`.**

Four files, the catalogue the app already holds, shaped for a page to read, with no figure invented
for it: the trainee roster (67 trainees, 106 card forms), the support cards (559), the support-effect
dictionary (35) and the skills (1,910). Each is a classic script, because the pages are opened under
`file://` and a module loaded from `file://` is blocked by the browser, and each publishes its data
and its schema into one namespace, `window.TRAINER_DESK_DATA`.

```html
<script src="assets/data/umamusume.js"></script>
<script src="assets/data/support-cards.js"></script>
<script src="assets/data/support-effects.js"></script>
<script src="assets/data/skills.js"></script>
```

**The schema is in the data.** Each file carries a schema object beside its records: every field,
what it means, and where the value comes from, so a consumer reads the absences as deliberately and
prints the right empty state. `assets/data/README.md` is the long form: the field-by-field tables, the
join between a card's effect row and the dictionary, and the six rules a consumer inherits.

**Three things are worth knowing before a page uses it.**

1. **A skill carries no description, and that is the ruling, not an omission.** No client-vocabulary
   description for a skill exists in this repository, and adding one is held against until a
   client-string source exists (G-SK-20, `docs/research-scratch/SKILLS-MECHANICS.md`). The export's
   English is the publisher's, not the client's, and it is carried beside the field as `sourceText`:
   reference material, not copy. A surface that binds to `description` prints the **"no client
   string"** state that `skill-detail.html` already draws.
2. **The numbers stay numbers.** A skill's `rarity` is the source's class code and names no tier, and
   the effect codes inside a condition are the engine's own. The dataset attaches no meaning to
   either, because none is established.
3. **The dev database holds four rows the game does not** — trainees named "Form Counted 1" to "Form
   Counted 4" and twelve "[Sample Form]" cards, written by a test against the `.test` host. The app's
   own counters include them, which is why the live dashboard reads **71 trainees** where the
   catalogue holds **67**. The dataset's rule is a join, not a name filter, and the generator checks
   that every row the join left out was a row that was never sourced.

The generator is `build-data.php`, the check is `data-check.mjs`, and both are described in
`assets/data/README.md`.

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

- **Contrast, targets, the type ladder, the accent dose, reflow, text resize, text spacing and focus
  visible are now MEASURED**, in both themes, by `framework-audit.mjs`. The earlier note that they
  were unverified rested on "Chromium cannot launch under this sandbox (`spawn EPERM`)", which is
  false on this host. The audit's table above records what that measurement found and fixed.
- **The result is a rendered observation, not a conformance claim.** WCAG 2.2 has 87 success
  criteria; this set measures the ones a rendered frame can decide and the framework file maps to a
  rule id. Criteria about timing, motion sensitivity, captions, authentication or error prevention
  are out of its reach, and `prefers-reduced-motion` is honoured in the stylesheet but not asserted.
- **No 390px measurement beyond overflow.** The shell carries a mobile bar below 760px, and the
  pages were checked for a sideways scroll at 320, 360 and 390px, but the bar's own touch targets
  and its five-item order were not measured.
- **The generated content is not exercised.** The wireframes are static: no copy action fires, no
  keyboard contract runs, and no state transition is driven beyond the rail's collapse.
- **The cockpit set is split.** The rich treatments live in `../cockpit-wireframes-2026-10-21/`; the
  `cockpit-*.html` files here are the same records at shell density and link back to them.

## Files

- `assets/shell.css` — the whole visual system: tokens, primitives, nav, mobile bar, annotations.
- `assets/stat-icons.svg` — the stat and type marks, as `<symbol>`s. The source of truth; each page that
  uses a mark carries the sprite inline, because an SVG `<use>` across files is blocked under `file://`
  and would render blank.
- `assets/shell.js` — the navigation's destination list, the single place the ten destinations live.
- `assets/data/*.js` — **the mock catalogue**: the trainee roster, the support cards, the
  support-effect dictionary and the skills, each with its schema, published into
  `window.TRAINER_DESK_DATA`. Generated by `build-data.php`; checked by `data-check.mjs`.
  `assets/data/README.md` is the schema in long form.
- `build-data.php` — the generator that writes the catalogue. It reads the dev database and the
  committed export bodies, and refuses to write a dataset whose growth rows, base rows, counts or
  joins do not match the catalogue.
- `data-check.mjs` — the catalogue's own check: the counts, the effect join, the growth and base
  sums, the exclusion of the test-fixture rows, the null descriptions, the schema's coverage of
  every field, and the classic-script load under `file://` in Chromium. Run before a commit.
- `../icons_svg/*.svg` — **the stat and type marks, and they are the owner's.** Seven files, each
  remade from the client's own type chip, with the original PNG beside each in `source/`. The pages
  reference them by `<img>`, so there is one copy.
- `framework-check.mjs` — the checkable rules from the design-framework index, over all 49 pages and
  the stylesheet. Run before a commit.
- `render-check.mjs` — the rules a static read cannot decide, measured in a rendered frame: whether
  the collapse control paints a mark, whether the ten words share one x, whether the rail is painted
  over the content while it resizes. Needs Playwright's Chromium; where it cannot launch it reports
  the rules as UNVERIFIED rather than passing them. Run before a commit.
- `framework-audit.mjs` — the framework's own rules that need a painted frame, over all 49 pages in
  both themes: contrast per text node (WCAG 1.4.3), the effective 44px target (UX-1), the heading
  ladder (UI-1), the accent dose (UI-7), reflow at 320px and text resize and spacing (1.4.10, 1.4.4,
  1.4.12), and focus visible measured by tabbing (2.4.7). Needs Playwright's Chromium; reports
  UNVERIFIED where it cannot launch. Run before a commit.
- `link-check.mjs` — every internal link and image, resolved against the page's own directory. Run
  before a commit.
- `build-index.js` — the generator that wrote the pages. It is a record of how they were made, not a
  build step: re-running it would overwrite the annotated pages, so the pages are the artefact and this
  is the history.
- `*.html` — one per screen, each self-contained apart from that stylesheet.
