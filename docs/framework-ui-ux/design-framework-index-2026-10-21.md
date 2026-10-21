# Design framework index

Status: **settled reference.** The entry point to the framework documents in `docs/framework-ui-ux/`, and the
map from each framework to the pages that answer it.

Seven frameworks were read from source on **2026-10-21**, each into its own file. This file says what
each one is, what it is good for, and where it overlaps another, so a review cites the narrowest
framework that actually decides the question.

## The ten files

| File | Framework | Count | Source read |
| --- | --- | --- | --- |
| `laws-of-ux-2026-10-21.md` | **The Laws of UX** | **29** (28 in the index plus Zeigarnik Effect) | [lawsofux.com](https://lawsofux.com/) |
| `uilaws-2026-10-21.md` | **The Laws of UI** | **13** | [uilaws.com](https://www.uilaws.com/) |
| `usability-heuristics-2026-10-21.md` | Nielsen's usability heuristics | 10 | [NN/g](https://www.nngroup.com/articles/ten-usability-heuristics/), reviewed 2024-01-30 |
| `shneiderman-golden-rules-2026-10-21.md` | Shneiderman's Golden Rules | 8 | [UMD](https://www.cs.umd.edu/users/ben/goldenrules.html), *Designing the User Interface* 6e §3.3.4 |
| `tognazzini-first-principles-2026-10-21.md` | Tognazzini's First Principles | 16 | [askTog](https://asktog.com/basics/firstPrinciplesOldEdition.html), original edition |
| `gerhardt-powals-principles-2026-10-21.md` | Gerhardt-Powals' cognitive engineering principles | 10 (9 in the source) | [secondary enumeration](https://www.brainscape.com/flashcards/gerhardt-powals-cognitive-engineering-pr-4587688/packs/6297555); **primary paywalled** |
| `information-architecture-principles-2026-10-21.md` | Dan Brown's Eight Principles of Information Architecture | 8 | [mirrored enumeration](https://github.com/clemergo/principles.design/blob/64c71ca7198b53e3a86ad2ee5f78ba26c8f8bf6f/_examples/eight-principles-of-information-architecture.md); **primary blocked** |
| `gestalt-principles-2026-10-21.md` | Gestalt principles of visual perception | 12 (6 commonly recognized) | [IxDF](https://ixdf.org/literature/topics/gestalt-principles) |
| `norman-interaction-principles-2026-10-21.md` | Norman's interaction principles, the two gulfs, the seven stages | 6 + 2 + 7 | [Wikipedia on *The Design of Everyday Things*](https://en.wikipedia.org/wiki/The_Design_of_Everyday_Things); **book not read** |
| `wcag-2.2-accessibility-2026-10-21.md` | WCAG 2.2 | 4 principles, 13 guidelines, 87 success criteria | [W3C REC 12 Dec 2024](https://www.w3.org/TR/WCAG22/) |

**The two law collections are separate, and the repository has confused them before.**
`laws-of-ux-2026-10-21.md` and `uilaws-2026-10-21.md` each carry an attribution table, and they agree on
the corrections:

| Principle | Published by lawsofux.com | Published by uilaws.com | Cited from |
| --- | --- | --- | --- |
| Closure | no | **yes** | Laws of UI |
| Continuity | no | **yes** | Laws of UI |
| Proximity | **yes** | yes | Laws of UX |
| Similarity | **yes** | no | Laws of UX |
| Common Region | **yes** | no | Laws of UX |
| Prägnanz | **yes** | no | Laws of UX |
| Common Fate | **no** | **no** | Gestalt principles, and it is not a law |
| Fitts, Hick, Jakob | **yes** | yes | Laws of UX, the more specific collection for them |

There is **no "Law of Experience"** on either site. The concept is **Mental Model**, entry 17 of the
Laws of UX. "Aesthetic consistency and visual hierarchy" is a **house principle**, not a law, and it is
in neither collection.

**Three of these eight had a blocked or partial primary source.** Each says so at the top of its own file,
and the gap is in the file rather than in the reader's memory. Nothing in this set paraphrases a source it
did not read without saying so.

## Which framework to cite

The frameworks overlap heavily. This table is the one that matters, because citing the wrong one is how a
finding gets filed twice.

| The question | Cite | Because |
| --- | --- | --- |
| Does the screen tell the user where they are and what just happened? | **Heuristics H1** | The others do not ask it. |
| Is the option count survivable? | **Hick** (Laws of UX 10, Laws of UI 12, Tognazzini), or **Shneiderman 2** | Hick is about the count; Shneiderman is about novices and experts both being served. |
| Is the control big enough and near enough? | **Fitts** (Laws of UX 7, Laws of UI 11, Tognazzini 8) | All three publish it; Tognazzini adds the count of targets. |
| Does the same thing look the same everywhere? | **Shneiderman 1**, **Laws of UI 6**, or **Tognazzini 4** | Shneiderman bounds the exceptions; Tognazzini gives the seven levels. |
| Is state readable without colour? | **WCAG 1.4.1**, **Laws of UI 8**, or **Tognazzini 3** | WCAG is normative; Tognazzini names the three cone deficiencies. |
| Can the user get out? | **H3**, **Shneiderman 6**, **Tognazzini 7** | Shneiderman 6 and Tognazzini 7 both add the *reversibility* half that H3 alone does not. |
| Does the screen make the user think? | **Gerhardt-Powals 1 and 3** | No other framework asks whether the product left arithmetic in the user's head. |
| Does the user have to remember something across screens? | **Shneiderman 8**, **Laws of UX 17** | The cross-display clause is Shneiderman's; Mental Model is the compressed model it protects. |
| Is the structure between the screens right? | **IA 1, 2, 6, 7, 8** | Every other framework is about a screen. |
| Will two things be read as one group? | **Gestalt**, **Laws of UI 1, 7, 9, 10** | The Laws of UI carry the visual-style half of Gestalt, and publish Closure and Continuity. |
| Can the user act, and can they tell what happened? | **Norman's two gulfs** | The only instrument that separates the two failures. |
| Is this accessible? | **WCAG 2.2 AA** | The only normative one. Cite a success criterion, never a principle. |
| Did the user do what they meant? | **Heuristics H5**, **Shneiderman 5**, **Norman's constraints** | Constraint is the strongest form: make the wrong action unavailable rather than validate it after. |
| Is the content reachable from anywhere? | **IA 5 (front doors)** | Unique to Brown's framework. |
| Is a half-done thing visible and distinguishable? | **Laws of UX 29 (Zeigarnik)**, **Laws of UI 9 (Closure)** | Zeigarnik is the incomplete task; Closure is the shape that implies completeness. The second is why an unfinished state may not *look* finished and say it is. |
| Is the type scale right? | **Laws of UI 5 (Typography Hierarchy)** | The visual-style collection is the one that asks about type. |
| Is the spacing carrying the grouping? | **Laws of UI 3 (White Space)** | Space is a grouping device, not decoration. |
| Does the palette spend its hues on meaning? | **Laws of UI 4 (Color Theory)** | Every hue in the system is semantic, which is why the stats carry none. |

## The conflicts, and which way each was settled

Recorded because the frameworks do not agree, and a review that does not know the ruling will file a
contradiction as a defect.

| Conflict | Settlement | Where |
| --- | --- | --- |
| **Feedback timing.** Heuristics cite the 400ms Doherty threshold; Tognazzini says acknowledge a click within 50ms and show an hourglass past 500ms. | Compatible: 50ms is *acknowledgement*, 400ms is *response*. The repository's rule is the acknowledgement one, because a write that has not been acknowledged reads as a dead control. | `PROCESS-PLANS.md` §13 |
| **Consistency versus exceptions.** Nielsen H4 and Tognazzini 4 push to sameness; Shneiderman 1 permits exceptions. | Shneiderman governs the exceptions, and bounds them: *"comprehensible and limited in number."* The dark theme's three named deviations and the Database area's density are the two recorded exceptions. | `PROCESS-PLANS.md` §13 "Deliberately traded" |
| **Density.** Heuristics H8 and Tognazzini's Aesthetics push minimal; IA 3 (disclosure) says preview and defer. | The Database area is deliberately exempt, and the exemption is named. Every other area previews. | `information-architecture-principles-2026-10-21.md` gap register |
| **Target size.** WCAG 2.5.8 AA requires 24×24. Fitts and the repository's house rule require 44. | The 44px rule is a **house rule exceeding the standard**, not conformance to 2.5.8. It should never be described as 2.5.8 compliance. | `wcag-2.2-accessibility-2026-10-21.md` |
| **Learnability versus usability.** Tognazzini 11 says they are not mutually exclusive; Shneiderman 2 says serve both. | No conflict. Both are recorded together and the repository's wizard states reversibility in advance, which is the learnability half without costing the usability half. | both files |
| **Progressive disclosure versus the Advisor's completeness.** Gerhardt-Powals 8 says show only what is needed now; the cockpit advisor prints a long priority list. | The Advisor is a *reference* surface, opened deliberately, not a summary strip. The cockpit's first viewport still shows three facts. | `tognazzini-first-principles-2026-10-21.md` |
| **Closure versus honesty.** Gestalt closure says we fill gaps to see a whole. | Visual closure is permitted; **informational closure is not**. A partial indicator may look complete but must not claim completeness in its text. | `gestalt-principles-2026-10-21.md` |

## What each framework is for, in one line

- **Heuristics** — the review spine. Ten questions every screen is asked.
- **Shneiderman** — the dialog spine. How a flow behaves from open to close.
- **Tognazzini** — the millisecond and the millimetre spine. Latency budgets, target sizes, undo, state.
- **Gerhardt-Powals** — the cognitive spine. Whether the product left work in the user's head.
- **Information architecture** — the space between screens. Objects, classification, depth, growth.
- **Gestalt** — the perceptual spine. Whether two things will be read as one.
- **Norman** — the comprehension spine. Affordance versus signifier, and the two gulfs.
- **WCAG** — the normative spine. The only one with a pass and a fail.

## How a review uses this set

1. **Pick the narrowest framework that decides the question.** The table above is the index.
2. **Cite a rule, not a framework.** Every framework file ends with a table of derived, checkable rules
   with ids (`UX-`, `UI-`, `SHR-`, `TOG-`, `GP-`, `IA-`, `GT-`, `NRM-`, `WCAG-`, and the heuristics by H
   number). A finding cites the id.
3. **A rule is not a finding.** A finding names a rule, a screen, and what was observed. Every rule in
   this set that depends on a rendered frame is currently unmeasured, because Chromium cannot launch
   under this repository's sandbox (`spawn EPERM`). Say so in the verdict.
4. **A contradiction between frameworks goes to the conflicts table above**, not into the finding list.

## The re-check, and when it is owed

**This index is the checklist for a change, not a document that is read once.** A change to any
surface in this repository is checked against it **before the change is committed**, and the check is
named in the commit body.

The order of the check:

| # | Question | Framework |
| --- | --- | --- |
| 1 | Does the change add, remove or move a control? | Fitts (44px), Hick (count) |
| 2 | Does it change what is on screen at once? | Cognitive Load, Choice Overload, Chunking |
| 3 | Does it change a colour, a tint or a state? | WCAG 1.4.1, 1.4.3, 1.4.11, Laws of UI 4 and 8 |
| 4 | Does it change a type size or a weight? | Laws of UI 5, Laws of UX 28 (Von Restorff) |
| 5 | Does it change what the user can undo, or where the action is? | Shneiderman 4, 5, 6, Tognazzini 7, 10, H3, H5 |
| 6 | Does it change where a thing lives, or what it is called? | IA 1, 2, 7, 8, Laws of UX 17, Jakob |
| 7 | Does it change a progress, an incomplete state or a boundary? | Zeigarnik, Laws of UI 9 and 10, Goal-Gradient |
| 8 | Does it change what the user has to read or remember? | Shneiderman 8, Gerhardt-Powals 1 and 3, H6 |
| 9 | Does it add a new destination, a new landmark or a new control type? | WCAG 2.4.1, 4.1.2, H4, Laws of UI 6 |
| 10 | Does it change the words? | WCAG 3.3.1, 3.3.3, H9, D-79 |

**The check that catches the most, and the one that is easiest to skip:** *does the change make the
user carry something between two places?* That is Shneiderman 8 and Gerhardt-Powals 3, and it is the
failure behind the "Step 1 of 6" appearing in three places at once.

**A change that cannot be checked is recorded as unchecked.** Chromium cannot launch under this
repository's sandbox, so a change whose verdict needs a rendered frame is committed with that limit
named, not with a pass claimed.

## Change log

| Date | Change |
| --- | --- |
| 2026-10-21 | Created alongside the seven framework files and the heuristics file. Records the source state of each, the overlap map, and seven settled conflicts. |
| 2026-10-22 | Added the two law collections, which were missing: `laws-of-ux-2026-10-21.md` (29 entries) and `uilaws-2026-10-21.md` (13 entries). Added the shared attribution table, since the two collections publish different sets and the repository had confused them. Added the re-check procedure above, so a change is checked against this index before it is committed. |
