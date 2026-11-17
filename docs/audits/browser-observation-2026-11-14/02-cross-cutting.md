# 02 — Cross-cutting observations

An index to the register, not a duplicate of it. Each row names the class, the screens it appeared on, and the
finding IDs that instance it. Where a class appeared and was **cleared** on re-measurement, it says so — a
cross-cutting chapter that lists only failures teaches the next auditor nothing about what held.

## Colour and contrast

| Observation | Screens | Findings |
|---|---|---|
| **Contrast held in light mode across every measured screen.** 9 screens, 246 distinct (colour, size) pairs, **0 failures** at 4.5:1 body / 3:1 large. | Dashboard, Cockpit, Careers, Review, Preferences, Preflight, Support cards, Data sources, Help | — (a clean result) |
| **Dark mode passes except one pair.** 14 pairs measured on the Dashboard in dark; **1 failure** at 4.15:1. | Dashboard (`To do`) | BO-004 |
| **Cards read as raised, consistently.** Every screen carries exactly one bordered/rounded surface family (`1px rgb(215,216,224)`, radius 10px) over a `rgb(244,244,255)` page. No screen drifted to a second card style. | all | — |
| **Warning/risk colour is used as a colour, not just as coloured text** — `text-risk` at `rgb(198,40,40)` with 5.44:1 in light. | Dashboard, Careers | — |

## Typography and hierarchy

| Observation | Screens | Findings |
|---|---|---|
| **One `h1` per route, always, and it is always the entity name.** No route rendered zero or two `h1`s (56 visits). | all | — |
| **Heading scale is consistent:** `h1` 30px/600, `h2` 16px/800, `h3` 13px/800. Section headings are uniformly the small-caps-weight style, never a competing 24px+ secondary. | all | — |
| **Body sizes cluster at 12/12.5/13.5/14/16px.** The 10px sidebar group labels (`PLAY`, `MY DATA`, `LIBRARY`, `SYSTEM`) are the only text below 11px, and they are eyebrow labels rather than reading text. | all | — |
| **Numbers use `font-mono tabular-nums`** on KPI tiles and stat rails, and proportional elsewhere. Used where it helps and not decoratively. | Dashboard, Cockpit, Skills | — |

## Spacing and layout

| Observation | Screens | Findings |
|---|---|---|
| **Zero horizontal overflow at both viewports on all 56 visits** — `scrollWidth - clientWidth` was 0 everywhere, including the widest tables. | all | — |
| **Wide tables scroll inside their own container, never the page.** `/skills` at 390px: table 672px in a 324px wrapper with `overflow-x: auto`; page overflow 0. Same on `/support-cards` and `/database/sources`. | `/skills`, `/support-cards`, `/database/sources` | — |
| **The 6-step wizard stepper fits at 390px** — 6 items × 44px tall, wrapping to two rows, no truncation (`scrollWidth === clientWidth` on every item) and 0 page overflow. | wizard steps 1–6 | — |
| **The mobile bottom bar reserves its own space** — `main` carries `padding-bottom: 96px` against a 45px fixed bar, and scrolling to the very bottom found **no** element under the bar. | Dashboard at 390px | — |
| **A fixed 281px panel reports a box but paints nothing** — measured and cleared (`checkVisibility()` false, hit-test passes through). Recorded as a false positive, not a defect. | Dashboard at 390px | — (withdrawn) |

## Interaction

| Observation | Screens | Findings |
|---|---|---|
| **Focus is never invisible, but the ring is not one colour.** 20 tab stops produced 4 outline colours; all `2px solid`. | `/review/10` | BO-011 |
| **Tab order follows visual order** on the three screens walked fully: sidebar → main content → actions. No trap; focus never left the document. | Dashboard, Preflight, `/review/10` | — |
| **The mobile "More" disclosure is a `<details>`/`<summary>` with a 44px summary target and 44px rows**, dismissing with a second activation. Keyboard-native. | Dashboard at 390px | — |
| **Loading state on the wizard trainee step is named** — "Saving your choice..." appears between the click and the advance. | `/career/setup/trainee` | — |
| **Inline validation is well-placed** — each invalid field's own sentence sits under that field, not in a distant alert. | `/career/setup/target` | BO-010 (affordance only) |
| **Buttons are correctly disabled until prerequisites are met** — "Continue to trainee" and "Create the run" both carried `disabled` until the ack checkbox was ticked. | wizard steps 1, 6 | — |

## Copy and terminology

| Observation | Screens | Findings |
|---|---|---|
| **Trainer vocabulary is used consistently** — career, run, deck, card, spark, parent, Veteran. I found **no** developer vocabulary (schema, payload, nullable, migration, run-scoped) anywhere in shipped copy. | all | — |
| **The same concept keeps one word** across screens: "career" and "run" are used for different things and used that way consistently; "Legacy"/"Veterans" are kept distinct and the distinction is explained. | all | — |
| **No copy apologizes for the software.** Absence is stated as fact ("No turn has been recorded yet.", "The library holds no Veterans yet."), not as a defect note. | all | — |
| **`N/A` is disclosed, not decorated** — the Cockpit's stat rail uses `title="No turn has recorded this stat yet."` correctly. | Cockpit | — |
| **One surface glues a colon onto the token**: `N/A : no turn has been logged…`. | `/training-runs` | BO-009 |

## Domain fidelity

| Observation | Screens | Findings |
|---|---|---|
| **The scenario is named consistently and the panels match it.** Run #13 is Unity Cup; the Cockpit reads `Admire Vega · Unity Cup`; the Scenario detail shows Team Rank and Spirit Bursts, which are Unity Cup's panels. No URA/Trackblazer vocabulary leaked into a Unity Cup screen. | Cockpit, Scenario detail, `/database/scenarios` | — |
| **Caps agree in arithmetic but not in noun.** `1,300`/`1,800` are identical on the Cockpit rail, the Scenario detail table (`Base 1200 + Bonus 100 = Cap 1300`) and the scenario reference prose; the Cockpit calls them a **floor**. | Cockpit vs Scenario detail | BO-007 |
| **The finale/timing model states its own basis** — `72 turns is three career years of the client's own 24-turn grid`, with the post-December block explicitly excluded. | Preflight, Cockpit | — |
| **Team Rank is accounted for, not left dangling** — the rank prints with `Rank not recorded: the league letter is read off the client, and this run has none entered yet.` | Scenario detail | — |
| **Aptitudes are present where a race decision reads them** — the trainee step prints per-surface/distance/style letter grades on the card itself. | `/career/setup/trainee` | — |
| **Fixture data reaches a player-facing roster** and is indistinguishable from a real trainee. | `/umamusume`, `/umamusume/aut-neque` | BO-003 |

## States

| Observation | Screens | Findings |
|---|---|---|
| **Empty states guide rather than strand.** Every naturally-empty surface named its cause and offered a next action: Veterans ("The library holds no Veterans yet… Open the run list"), Veterans compare ("Nothing is selected… Open the library"), Legacy compare ("Pick at least one run above"), Snapshot review ("Fill the form first"), Result ("This career is still running… the Cockpit is where the next turn is decided"). | 5 surfaces | — |
| **A `Warning` state is explained as non-blocking** — "A warning is not a block. The one warning here is a deck with empty slots, which is a legitimate choice." | Preflight | — |
| **An error state names its own field** rather than a distant summary. | `/career/setup/target` | BO-010 |
| **A count sentence can disagree with the table beneath it.** | Preflight | BO-006 |
