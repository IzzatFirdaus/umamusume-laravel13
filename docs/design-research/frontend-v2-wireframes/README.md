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
| **Typography Hierarchy** | uilaws | Three steps: 28px page title, 15px card heading, 13px sub-heading, 12.5-13px body. The shipped app's 75 headers at `text-base` is the defect this avoids. |
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

## The seven frameworks this set answers to

The references are in [`docs/proposals/`](../../proposals/): one file per framework, each quoted from its
source, plus [`design-framework-index-2026-10-21.md`](../../proposals/design-framework-index-2026-10-21.md),
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

The reference is [`docs/proposals/usability-heuristics-2026-10-21.md`](../../proposals/usability-heuristics-2026-10-21.md),
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
