# Norman's fundamental principles of interaction

Status: **settled reference with a stated source limit.** Records the principles from *The Design of
Everyday Things*, the two gulfs and the seven stages of action.

## Provenance, and what was and was not read

| Field | Value |
| --- | --- |
| Book | Don Norman, *The Design of Everyday Things*. Original 1988 as *The Psychology of Everyday Things*; revised and expanded 2013, MIT Press. ISBN 978-0-262-52567-1 |
| Access attempted | The book itself was not fetched |
| Source used | [The Design of Everyday Things, Wikipedia](https://en.wikipedia.org/wiki/The_Design_of_Everyday_Things), fetched 2026-10-21 |
| Fetched | 2026-10-21 |

**The limit, stated plainly:** the principles below are corroborated by the fetched encyclopedia entry and
by the book's own vocabulary as this repository already cites it, but the book's prose is not quoted here.
Where a sentence appears in quotation marks below it is quoted **from the encyclopedia entry**, not from
the book. The six-as-a-set framing is convention; Norman states affordances, signifiers, mapping,
feedback, constraints and conceptual models across the book rather than as a numbered list in one place.

## The vocabulary Norman introduced

**Affordance** — introduced in the 1988 edition, borrowed from James J. Gibson's ecological psychology.
*"Examples of affordances are doors that can be pushed or pulled. These are the possible interactions
between an object and its user."*

**Signifier** — introduced in the 2013 revision *"to clarify his definition of affordances."* The example
is the one line worth memorising: *"a door affords pushing or pulling, and the plate or button signals
that it is meant to be pushed, while the bar or handle signals pulling."*

**The distinction is the whole point.** An affordance is a possibility; a signifier is the communication of
where it happens. A flat plate on a door that only pushes is a good signifier. A handle on a door that has
to be pushed is a signifier that lies, and it produces the "Norman door" — a door you get wrong in front of
other people.

## The principles

### 1. Affordances

The possible interactions between an object and its user. Not a property of the object alone: it is
relational, and it depends on the user's capabilities.

**Applied here:** a table row affords opening; a radio affords one-of-several; a slot affords filling.

### 2. Signifiers

What signals that the affordance is there and where the action happens. The 2013 addition, and the one
that does the work in an interface: an affordance nobody can see is not an affordance.

**Applied here:** the dashed border on an empty deck slot is a signifier; the add action inside it is the
affordance made visible. The `Undo turn N` control is a signifier for an affordance the build does not yet
have, which is why it is drawn and labelled rather than implied.

### 3. Constraints

*"exploiting the powers of constraint."* Limiting the possible actions so the wrong one is unavailable
rather than discouraged.

**Applied here:** the client forbids two copies of one card in a deck; the deck picker should express that
by not offering the same card twice, not by validating after the fact.

### 4. Mappings

*"Good mappings – it is possible to determine the relationships between actions and results, between the
controls and their effects, and between the system state and what is visible."*

**Applied here:** the five disciplines in a fixed order with their stat affinity named beside them, so the
control's location and its effect correspond. A stat bar's position among the five is the same everywhere.

### 5. Feedback

*"the user receives full and continuous feedback about the results of the actions."*

**Applied here:** every write has a pending label and a saved confirmation. Continuous, not only at the end.

### 6. Conceptual model

*"The designer provides a good conceptual model for the user, with consistency in the presentation of
operations and results and a coherent, consistent system image."*

**Applied here:** the run is a sequence of turns with one commitment per turn. Every screen speaks that
model; nothing presents the run as a collection of independent records.

Plus **visibility**, listed alongside these four in the entry: *"by looking, the user can tell the state of
the device and the alternatives for action."*

## The seven stages of action and the two gulfs

Norman's action cycle, from the book's second chapter. Four stages of execution and three of evaluation:

1. Forming the goal
2. Forming the intention
3. Specifying an action
4. Executing the action
5. Perceiving the state of the world
6. Interpreting the state of the world
7. Evaluating the outcome

**Gulf of execution** — *"the difference between the intentions and the allowable actions."* The user knows
what they want and cannot work out what to do.

**Gulf of evaluation** — *"the degree to which the system or artifact provides representations that can be
directly perceived and interpreted in terms of the expectations and intentions of the user."* And the
sentence that makes it checkable:

> The gulf is small when the system provides information about its state in a form that is easy to get, is
> easy to interpret, and matches the way the person thinks of the system.

Both gulfs were first named in Norman's 1986 *User Centered System Design*.

**These two are the most useful thing in the framework for this product**, because they name the two
distinct failures a screen can have: the user cannot act, or the user cannot tell what happened. A wireframe
review that only asks "is this clear" conflates them.

## How the principles map onto this repository

| Principle | Implemented by | Where it is visible |
| --- | --- | --- |
| **Affordances** | disabled states, empty slots, the 44px target rule | What is available is visible as available. |
| **Signifiers** | labels on every control; the dashed empty slot; the blocked row's reason | A blocked race row says why, which is a signifier doing the work a disabled state cannot. |
| **Constraints** | the client's own rules expressed by the control, e.g. one pick per parent | One radio per parent enforces the rule the game has. |
| **Mappings** | fixed discipline order; fixed stat order; the same glyph grid | Position means the same thing on every screen. |
| **Feedback** | pending labels, saved confirmations, the four states | Continuous, not terminal. |
| **Conceptual model** | the turn loop, stated once on the help page and carried by every screen | One run, one turn at a time. |
| **Visibility** | H1 as the cockpit's whole job | Energy, mood, next obligation in the first viewport. |
| **Gulf of execution** | the advisor printing the number it cites; one action per screen | The user never has to work out what to do next. |
| **Gulf of evaluation** | the timeline; the result screen; the truth-tag vocabulary | The user can always tell what happened and what it was worth. |

## What this framework decides that the others do not

- **The affordance/signifier split** is the sharpest tool here against a specific failure: a control that
  exists but does not announce itself. Nielsen's heuristics ask whether options are visible; Norman
  distinguishes the option from *the thing that says the option is there*, which is what actually fails.
- **Constraints are the strongest form of error prevention.** Everyone in this set says prevent errors;
  Norman says the way to do it is to make the wrong action unavailable rather than validate it afterwards.
- **The two gulfs** are the only instrument in this set that separates "I could not do it" from "I could not
  tell what it did". Every screen review here should answer both separately.

## Checkable rules derived

| Id | Rule | Source |
| --- | --- | --- |
| NRM-1 | Every available action carries a visible signifier. | signifiers |
| NRM-2 | A control that is unavailable says why, rather than disappearing. | signifiers, constraints |
| NRM-3 | A rule the system enforces is expressed by the control, not by a message after it. | constraints |
| NRM-4 | The same position means the same thing on every screen. | mappings |
| NRM-5 | Feedback is continuous through a write, not only at the end. | feedback |
| NRM-6 | Every screen is reviewed twice: can the user act (execution), and can the user tell (evaluation). | the two gulfs |

## Change log

| Date | Change |
| --- | --- |
| 2026-10-21 | Created. The book was not fetched; quotations are from the encyclopedia entry and are marked as such. The six-principle set is convention rather than a quoted list. |
