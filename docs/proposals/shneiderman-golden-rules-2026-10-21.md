# Shneiderman's Eight Golden Rules of Interface Design

Status: **settled reference**. Records Ben Shneiderman's eight rules as the source states them, and how
they bind the screens in this repository.

Read from the author's own page on **2026-10-21**. The rules are quoted, not paraphrased.

## Provenance

| Field | Value |
| --- | --- |
| Source | [The Eight Golden Rules of Interface Design](https://www.cs.umd.edu/users/ben/goldenrules.html), Ben Shneiderman |
| Text version | Section 3.3.4 of the **Sixth edition** (2016): Shneiderman, Plaisant, Cohen, Jacobs, Elmqvist, *Designing the User Interface: Strategies for Effective Human-Computer Interaction* |
| Origin | first written 1985; *"derived from experience and refined over three decades"* |
| Fetched | 2026-10-21 |

Shneiderman's own framing, which governs how this file is used: the rules *"require validation and tuning
for specific design domains"* and *"No list such as this can be complete."* They are therefore a starting
framework, not a gate. He also records that Jakob Nielsen, Jeff Johnson and others have expanded them,
which is why the heuristics file and this one overlap without contradicting.

## The eight, verbatim

### 1. Strive for consistency

> Consistent sequences of actions should be required in similar situations; identical terminology should
> be used in prompts, menus, and help screens; and consistent color, layout, capitalization, fonts, and so
> on, should be employed throughout. Exceptions, such as required confirmation of the delete command or no
> echoing of passwords, should be comprehensible and limited in number.

The clause that is usually dropped is the last one: **exceptions are allowed, but they must be
comprehensible and few.** Consistency is not uniformity.

### 2. Seek universal usability

> Recognize the needs of diverse users and design for plasticity, facilitating transformation of content.
> Novice to expert differences, age ranges, disabilities, international variations, and technological
> diversity each enrich the spectrum of requirements that guides design. Adding features for novices, such
> as explanations, and features for experts, such as shortcuts and faster pacing, enriches the interface
> design and improves perceived quality.

This is the rule that makes novice and expert affordances *additive* rather than a trade-off.

### 3. Offer informative feedback

> For every user action, there should be an interface feedback. For frequent and minor actions, the
> response can be modest, whereas for infrequent and major actions, the response should be more
> substantial.

Feedback is **proportional**, not uniform. A frequent action that fires a modal is a violation of this
rule in the other direction.

### 4. Design dialogs to yield closure

> Sequences of actions should be organized into groups with a beginning, middle, and end. Informative
> feedback at the completion of a group of actions gives users the satisfaction of accomplishment, a sense
> of relief, a signal to drop contingency plans from their minds, and an indicator to prepare for the next
> group of actions.

His example is the e-commerce funnel ending in a confirmation page. The mechanism named is *dropping
contingency plans* — closure is what lets the user stop holding the task in memory.

### 5. Prevent errors

> As much as possible, design the interface so that users cannot make serious errors; for example, gray
> out menu items that are not appropriate and do not allow alphabetic characters in numeric entry fields.
> If users make an error, the interface should offer simple, constructive, and specific instructions for
> recovery. […] Erroneous actions should leave the interface state unchanged, or the interface should give
> instructions about restoring the state.

Three separate requirements: prevent, give specific recovery instructions, and **do not destroy state on
a failed action.** The last one is the one this product most needs, because a turn is entered data.

### 6. Permit easy reversal of actions

> As much as possible, actions should be reversible. This feature relieves anxiety, since users know that
> errors can be undone, and encourages exploration of unfamiliar options. The units of reversibility may
> be a single action, a data-entry task, or a complete group of actions.

The stated benefit is **anxiety relief and exploration**, not just error correction. A screen with no
reversal makes a Trainer conservative about the thing the tool exists to support.

### 7. Keep users in control

> Experienced users strongly desire the sense that they are in charge of the interface and that the
> interface responds to their actions. They don't want surprises or changes in familiar behavior, and they
> are annoyed by tedious data-entry sequences, difficulty in obtaining necessary information, and
> inability to produce their desired result.

Note the three named annoyances: tedious entry, **difficulty obtaining necessary information**, and
inability to produce the desired result. The middle one is an information-architecture complaint inside a
control rule.

### 8. Reduce short-term memory load

> Humans' limited capacity for information processing in short-term memory (the rule of thumb is that
> people can remember "seven plus or minus two chunks" of information) requires that designers avoid
> interfaces in which users must remember information from one display and then use that information on
> another display. It means that cellphones should not require reentry of phone numbers, website locations
> should remain visible, and lengthy forms should be compacted to fit a single display.

The actionable clause is the **cross-display** one. Within a screen, working memory is cheap; across a
screen boundary it is not.

## How the eight map onto this repository

| Rule | Implemented by | Where it is visible |
| --- | --- | --- |
| **1 Consistency** | D-1 token law, D-2 one source per value, `LAYOUT_WIDTH_CLASSES`, one `.btn` vocabulary | One token set, one type scale, one focus ring. The recorded exceptions are the dark theme's three named deviations (`DESIGN.md` §3.7). |
| **2 Universal usability** | D-55 keyboard operation, `prefers-reduced-motion` (D-92), the 44px target rule, progressive disclosure | The guided flow is fully operable without a pointer and the accelerators are now printed on screen (`training-decision`). |
| **3 Informative feedback** | Doherty Threshold row, `role="status"` on a saved write, the pending label | Every write has a pending label and a saved confirmation. Modest for a step, substantial for a commit. |
| **4 Closure** | Peak-End row, the result screen, the preflight commit | The run ends on a stated outcome. The create-run flow ends on a confirmation that names what was created. |
| **5 Prevent errors** | D-200 success and failure branches, D-45 four states, the blocked race row with its reason | A choice preview shows both branches. A refused write leaves the entered state intact. |
| **6 Reversal** | **the `Undo turn N` control now drawn on the run screens** | The build has no undo after a committed turn. The wireframe draws the missing control rather than hiding the gap. |
| **7 Control** | D-55 Escape steps back, the cancel affordance, D-104 the theme is the Trainer's | No surprises: the theme and layout width persist and the wizard states reversibility before the commit. |
| **8 Short-term memory** | D-171 energy beside the confirm, the advisor printing the number it cites | The value a decision needs is on the decision screen. Nothing requires carrying a figure across a screen boundary. |

## Where this framework decides something the heuristics do not

Two places, both worth keeping because the other frameworks are silent.

**Rule 1's exception clause.** Nielsen's Consistency and Standards heuristic and Tognazzini's Consistency
both push toward sameness. Shneiderman is the one who says *how many* exceptions are tolerable and on what
basis: comprehensible and limited. That is the licence this repository uses for the dark theme's three
deviations and for the deliberately dense Database area.

**Rule 3's proportionality.** No other framework in this set says feedback should be *smaller* for
frequent actions. It is the argument against a confirmation dialog on a routine turn record, and the
argument for one on a destructive reset.

## Checkable rules derived

| Id | Rule | Source |
| --- | --- | --- |
| SHR-1 | A consistency exception must be named in a document and be few. | rule 1 |
| SHR-2 | Nothing the product can offer an expert may make the first run harder. | rule 2 |
| SHR-3 | A frequent action gets a quiet acknowledgement, not a modal. | rule 3 |
| SHR-4 | Every multi-step flow ends on a statement of what now exists. | rule 4 |
| SHR-5 | A refused write leaves entered state intact. | rule 5 |
| SHR-6 | A committed turn is reversible from the screen that committed it. | rule 6 |
| SHR-7 | The value a decision needs is on the decision screen. | rule 8 |

## Change log

| Date | Change |
| --- | --- |
| 2026-10-21 | Created from the UMD page, sixth-edition text. Mapped onto the D-rules and the heuristic file. |
