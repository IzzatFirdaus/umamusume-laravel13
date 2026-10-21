# Tognazzini's First Principles of Interaction Design

Status: **settled reference**. Records Bruce Tognazzini's sixteen first principles as the original edition
states them, and how they bind the screens in this repository.

Read from askTog on **2026-10-21**. The wording is quoted.

## Provenance, and which edition this file records

| Field | Value |
| --- | --- |
| Source used | [First Principles of Interaction Design, original edition](https://asktog.com/basics/firstPrinciplesOldEdition.html) |
| Copyright | *"copyright 2003 by Bruce Tognazzini"* |
| Revised edition | [First Principles of Interaction Design (Revised & Expanded), 5 Mar 2014](https://asktog.com/atc/principles-of-interaction-design/) |
| Fetched | 2026-10-21 |

**The sixteen are the original edition's sixteen.** The 2014 revision restructures the list: *"Color
Blindness" is now simply Color*, and it *"added one new principle, Aesthetics, and brought back two old
principles, Discoverability and Simplicity."* The 2014 page truncated during the fetch, so this file does
**not** state the revised edition's final count or its full contents. Where a principle below exists in
both editions the original wording is quoted, and the revised edition's additions are recorded in their
own section at the end so nothing is silently lost.

Tognazzini's framing: *"Effective interfaces are visually apparent and forgiving, instilling in their
users a sense of control."* And the sentence that sets the tone for the whole list: *"Effective
applications and services perform a maximum of work, while requiring a minimum of information from
users."*

## The sixteen, verbatim

### 1. Anticipation

> Applications should attempt to anticipate the user's wants and needs. Do not expect users to search for
> or gather information or evoke necessary tools. Bring to the user all the information and tools needed
> for each step of the process.

### 2. Autonomy

> The computer, the interface, and the task environment all "belong" to the user, but user-autonomy
> doesn't mean we abandon rules.

He adds the counterweight: *"people do not feel free in the absence of all boundaries"* — a child cries
both when held too tight and when left in an empty warehouse. Three sub-principles follow: **use status
mechanisms to keep users aware and informed**; **keep status information up to date and within easy
view**; **ensure status information is accurate.** The accuracy clause is sharpened in the 2014 edition
with an example of an upgrade progress bar that claims five minutes and takes an hour: *"the user, having
been lied to, was left with no way to predict when she might actually get her device back."*

### 3. Color Blindness

> Any time you use color to convey information in the interface, you should also use clear, secondary cues
> to convey the information to those who won't be experiencing any color coding today.

Roughly 10% of males, plus *"a rare sprinkling of females."* He names the three cone deficiencies:
**protanopia** (red), **deuteranopia** (green), **tritanopia** (blue, very rare but *"rules out dependence
on yellow-blue differentiation without secondary cues"*).

### 4. Consistency

Seven **levels**, ordered by how faithful the consistency must be — and he notes that many people assume
the order is the reverse, *"leading to applications that look alike, but act completely different."*

1. **Interpretation of user behavior** — shortcut keys maintain their meanings
2. **Invisible structures**
3. **Small visible structures** — icons, size boxes, scroll arrows
4. **The overall look** of a single application
5. **A suite of products**
6. **In-house consistency**
7. **Platform consistency**

Two rules sit alongside the levels: **inconsistency** (*"It is just as important to be visually
inconsistent when things must act differently"*) and **consistency with user expectations** — *"The only
way to ascertain user expectations is to do user testing."*

### 5. Defaults

> Fields containing defaults should come up selected, so users can replace the default contents with new
> material quickly and easily.

Defaults should be *"intelligent" and responsive*, and: **do not use the word "default"** — say
"Standard", "Use Customary Settings", "Restore Initial Settings", or something describing what will
actually happen.

### 6. Efficiency of the User

> Look at the user's productivity, not the computer's.

His worked example is the microwave: 1:11 is faster than 1:10, because 1-1-1 is one key pressed three
times while 1:10 needs the zero located and acquired. He adds *"keep the user occupied"*, that
organisational efficiency must be everyone's, and that *"the great efficiency breakthroughs in software
are to be found in the fundamental architecture of the system, not in the surface design."* Also: **menu
and button labels should have the key word first** — his "Insert / Page break" versus "Insert page break"
example, where the *less* accurate label is more efficient because the user scans only the first word.

### 7. Explorable Interfaces

> Give users well-marked roads and landmarks, then let them shift into four-wheel drive.

Do not trap users into a single path, but offer a line of least resistance. Sub-rules: sometimes you have
to **provide deep ruts** (the naive end of the curve needs a more directive interface); **offer stable
perceptual cues for a sense of home**; **make actions reversible**; **always allow "Undo"**; **always
allow a way out**; and **make it easier to stay in**.

Two arguments here are load-bearing. On undo: *"The unavoidable result of not supporting undo is that you
must then support a bunch of dialogs that say the equivalent of, 'Are you really, really sure?'"* And on
the absence of either: *"people in a hazardous environment make no more mistakes than people in a
supportive and more visually obvious environment, but they worked a lot slower and a lot more carefully."*

### 8. Fitts' Law

> The time to acquire a target is a function of the distance to and size of the target.

*"While at first glance, this law might seem patently obvious, it is one of the most ignored principles in
design."* Use large objects for important functions; exploit the **pinning action** of sides and corners;
note that *"even a one-pixel boundary can result in a 20% to 30% slow-down"*. His **Multiple Fitts** rule:
*"the time to acquire multiple targets is the sum of the time to acquire each"* — so reduce the *number*
of targets, not only the distance. And: *"Fitts's Law requires a stop watch test."*

### 9. Human-Interface Objects

Human-interface objects are not the same as object-oriented objects; they include folders, documents, the
trashcan. They **can be seen, heard, touched, or otherwise perceived**; they have a **standard way of
interacting**; they have **standard resulting behaviours**; they **should be understandable,
self-consistent, and stable.**

The rule with teeth: *"Human-interface objects should have standard resulting behaviors. Dropping a
document on a trash can does not delete it, it stores it in the trash can."* If your icon destroys
immediately, use a different icon — because otherwise you damage the icon for every other developer.

### 10. Latency Reduction

Push latency into the background where possible, and then:

> Acknowledge all button clicks by visual or aural feedback within 50 milliseconds.
> Display an hourglass for any action that will take from 1/2 to 2 seconds.
> Animate the hourglass so they know the system hasn't died.
> Display a message indicating the potential length of the wait for any action that will take longer than
> 2 seconds.
> Communicate the actual length through an animated progress indicator.
> Make the client system beep and give a large visual indication upon return from lengthy (>10 seconds)
> processes.
> Trap multiple clicks of the same button or object.

This is the only framework in the set with a **millisecond table**. It is stricter than the repository's
Doherty Threshold row, which cites 400ms; the two are compatible because Tognizini's 50ms is for
*acknowledgement* and 400ms is for *response*.

### 11. Learnability

> Usability and learnability are not mutually exclusive. First, decide which is the most important; then
> attack both with vigor. Ease of learning automatically coming at the expense of ease of use is a myth.

The decision input is **frequency of use** and **who is the buyer**. He also warns against only testing
learnability: short sessions tell you everything about the initial curve and nothing about the end state.

### 12. Metaphors, Use of

> Choose metaphors well, metaphors that will enable users to instantly grasp the finest details of the
> conceptual model.

*"Good metaphors are stories, creating visible pictures in the mind."* Bring them alive sensorially; let
them add a twist rather than copy the world (his Windows 95 briefcase synchronises rather than transports).

### 13. Protect Users' Work

> Ensure that users never lose their work as a result of error on their part, the vagaries of Internet
> transmission, or any other reason other than the completely unavoidable.

He puts this close to absolute, and his Harrod's illustration is the one to keep: no shop would shred a
four-page form because you put spaces in your phone number, yet forms do exactly that.

### 14. Readability

> Text that must be read should have high contrast. Favor black text on white or pale yellow backgrounds.
> Avoid gray backgrounds.

Plus: font sizes large enough for the expected displays; **favour large characters for the actual data**,
smaller for labels, *"this becomes even more important for numbers"* because numbers have no redundancy to
heal a garbled readout; test on the oldest expected user, since presbyopia affects most people over 45;
and beware anti-aliasing at small sizes.

### 15. Track State

> Because many of our browser-based products exist in a stateless environment, we have the responsibility
> to track state as needed.

The list of what may be needed: *whether this is the first time the user has been in the system; where the
user is; where the user is going; where the user has been during this session; where the user was when
they left off in the last session.*

The last one is the principle's real demand: a user should be able to leave and resume exactly where they
stopped.

### 16. Visible Navigation

> Avoid invisible navigation. Most users cannot and will not build elaborate mental maps and will become
> lost or tired if expected to do so.

*"Present the illusion that users are always in the same place, with the work brought to them."*

## The 2014 revision's additions

Recorded because they are part of the current article even though they are not in the sixteen.

- **Aesthetics** — aesthetic design left to visual designers; *"Fashion should never trump usability"*;
  user test the visual design as thoroughly as the behavioural design.
- **Color** (formerly Color Blindness) — plus: do not avoid colour because not every user sees every
  colour; do not strip colour cues for a design fad.
- **Discoverability** — brought back because *"Problems with Discoverability, in particular, have come
  roaring back."*
- **Simplicity** — balance ease of installation against ease of use.

## How the sixteen map onto this repository

| Principle | Implemented by | Where it is visible |
| --- | --- | --- |
| **Anticipation** | the advisor printing the number it cites; the cockpit's resource strip | The information a step needs is on the step. |
| **Autonomy** | D-104 theme is the Trainer's; `useLayout`; D-55 Escape | Preferences persist and the Trainer can leave a flow. **Status accuracy** is the C-7 gate: an unmarked figure is confirmed, an exception carries a mark. |
| **Color Blindness** | D-12 state is never colour-only; the `.trust` vocabulary pairs a glyph with a word | Every tint has a word or glyph beside it. |
| **Consistency** | D-1, D-2, one component per role | The seven levels are why platform conventions beat in-house ones where they collide. |
| **Defaults** | the import never fills a missing field; `useLayout.ts:45` reads a missing preference as `standard` | Absence is a normal result. The word "default" is not used in copy. |
| **Efficiency of the User** | the keyboard contract; labels put the key word first | `Undo turn N`, `Import this run` — verb first. |
| **Explorable Interfaces** | **`Undo turn N` on the run screens**; the way out on every flow | The gap is that the build has no undo; the wireframe draws it. |
| **Fitts' Law** | the 44px rule | `.btn`, `.btn-sm`, `.step`, `.field .in`, `.nav-item` are all 44px. |
| **Human-Interface Objects** | one `.btn`, one `.pill`, one `.tag`; the trashcan rule for destructive affordances | A control that destroys must not look like one that stores. |
| **Latency Reduction** | Doherty Threshold row; pending labels | Every write acknowledges immediately. |
| **Learnability** | the preflight stating reversibility; the help page | Learnability and usability are attacked together, not traded. |
| **Metaphors** | the client's own vocabulary | SP, Mood, Bond, Spark. No invented metaphor. |
| **Protect Users' Work** | D-419 error page; the import writes only after confirm | A refused write leaves entered state intact. |
| **Readability** | D-110 font decision; data larger than labels | Numbers are set larger than their labels. |
| **Track State** | D-104 stored preferences; the run's own turn record; the import position | A run resumes at the next turn. |
| **Visible Navigation** | the rail, the stepper, the mobile bar | Navigation is never invisible, and the four layouts are the same spine. |

## What this framework decides that the others do not

- **The 50ms acknowledgement budget.** Nielsen gives no number; Shneiderman says feedback should be
  proportional; Tognazzini supplies the millisecond table, including the trap against double-clicks.
- **Multiple Fitts.** Every other source treats Fitts as target size and distance. He adds that the
  *count* of targets multiplies the cost, which is the argument for one primary action per screen rather
  than a toolbar of them.
- **The undo-versus-confirmation trade.** His claim that not supporting undo forces you to support a pile
  of "are you really sure" dialogs is the sharpest statement of why this repository's missing undo is a
  debt rather than a preference.
- **Status accuracy.** He separates *up to date* from *accurate*, and gives a failure mode where the
  status is current and still lies. That is the rule behind printing `Unknown` rather than a number.

## Checkable rules derived

| Id | Rule | Source |
| --- | --- | --- |
| TOG-1 | Every click is acknowledged within 50ms. | latency |
| TOG-2 | Multiple clicks on one control are trapped. | latency |
| TOG-3 | Status may be stale or absent, but never wrong. | autonomy |
| TOG-4 | A control that destroys must not look like one that stores. | human-interface objects |
| TOG-5 | The count of targets per task is reduced before their size is increased. | multiple Fitts |
| TOG-6 | If undo is absent, the confirmation the absence forces must be present and named. | explorable interfaces |
| TOG-7 | A session can be left and resumed where it stopped. | track state |
| TOG-8 | Labels lead with the differentiating word. | efficiency of the user |

## Change log

| Date | Change |
| --- | --- |
| 2026-10-21 | Created from the askTog original edition. The 2014 revision's additions recorded separately because that page truncated and its final count is unverified. |
