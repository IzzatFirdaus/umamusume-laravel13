# Gestalt principles of visual perception

Status: **settled reference.** Records the Gestalt principles as the source enumerates them, and how they
bind the visual grouping in this repository.

## Provenance

| Field | Value |
| --- | --- |
| Source | [What are the Gestalt Principles?](https://ixdf.org/literature/topics/gestalt-principles), Interaction Design Foundation, updated 2026 |
| Origin | Max Wertheimer, Kurt Koffka and Wolfgang Köhler, 1920s Berlin school |
| Fetched | 2026-10-21 |

The source's own count framing, kept because it is the honest one: *"There are more than ten overlapping
principles."* Its FAQ then says *"There are six commonly recognized Gestalt principles of perception:
similarity, continuation, closure, proximity, figure/ground, and symmetry and order (also known as
prägnanz)."* Twelve are enumerated on the page and all twelve are recorded below.

## Attribution note, which matters for this repository

This repository previously had to be told twice which collection publishes which principle. The position
now is:

| Principle | Published by lawsofux.com | Published by uilaws.com | This file |
| --- | --- | --- | --- |
| Closure | no | **yes** | yes |
| Continuity / Continuation | no | **yes** | yes |
| Proximity | **yes** | yes | yes |
| Similarity | **yes** | no | yes |
| Common Region | **yes** | no | yes |
| Prägnanz | **yes** | no | yes |
| Common Fate | **no** | **no** | yes — Gestalt, published by neither |
| Symmetry and Order | **no** | yes | yes |
| Figure/Ground, Multistability, Emergence, Invariance | **no** | no | yes, and cited from here only |

So a citation of Common Fate to either law collection is wrong. Cite this file, which cites IxDF.

## Koffka's formulation, corrected

> The whole is other than the sum of the parts.
>
> — Kurt Koffka

The source is explicit that this is **not** Aristotle's *"the whole is greater than the sum of the parts."*
*"In Gestaltism, the whole is different and may even be completely unrelated to its parts."* The misquote
is common enough that it is worth recording: the two say different things, and only one is Gestalt.

## The twelve

### 1. Emergence

We perceive a whole before we perceive its parts: the Dalmatian emerges from blotches. Central to Gestalt
thinking — *"We perceive the world without thinking too much about understanding every small thing."*

### 2. Closure (Reification)

> We prefer complete shapes, so we automatically fill the gaps between elements to perceive a complete
> image.

**This is the principle with a hard UX limit.** Visual closure is forgiving; **informational closure is
not.** A progress indicator that implies a complete set of steps when the set is unknown is closure
applied to data, and it is a defect. This repository's rule is that a partial indicator must stay honest
while it may still *look* complete.

### 3. Common Region

> We perceive elements that are in the same closed region as one group.

Applied: one card bounds one concept; one fieldset bounds one group of fields.

### 4. Continuity (Continuation)

> We group elements that seem to follow a continuous path in a particular direction. The human eye follows
> the paths, lines, and curves of a design and prefers to see a continuous flow of visual elements rather
> than separated objects.

Applied: the timeline rail. A broken spine stops reading as one run.

### 5. Proximity

> We group closer-together elements, separating them from those farther apart.

Applied: a label sits in the same element as its input; a provenance mark sits beside the figure it marks.

### 6. Multistability

Ambiguous images flip between interpretations and cannot be seen both ways at once. The Necker cube, the
young woman / old woman.

**UX consequence:** an interface element that can be read two ways will be, and the user will be wrong
half the time. Ambiguity in an icon or a state is not a subtlety, it is a coin flip.

### 7. Figure/Ground

> We dislike uncertainty, so we look for solid, stable items.

Applied: light text on dark ground and its inverse across themes without losing the figure. *"When you use
figure/ground well… you'll help guide users in their tasks and lessen their cognitive load."*

### 8. Invariance

Basic shapes are perceived as identical under rotation, movement, scaling, stretching and lighting change.

**UX consequence:** an icon may be transformed and stay recognisable, which is why a hover or active state
can change a glyph's size and still read. It also means a *deliberately different* glyph that happens to be
a rotated version of another will not read as different — the invariance is working against the
distinction.

### 9. Prägnanz

> Prägnanz describes the human tendency to simplify complexity.

The Olympic rings read as five rings, not as C-shapes and lenses, because circles are simpler. Applied:
interface elements use simple shapes for the reason they are easier to process.

### 10. Similarity

> When items, objects or elements share superficial characteristics, we perceive them as grouped.

Applied: one `.btn` vocabulary means every button is a button. Conversely, **a shared style promises a
shared behaviour**, so two controls that look alike and behave differently violate this principle and not
merely consistency.

### 11. Symmetry and Order

> Humans tend to see visual elements as grouped when they are arranged symmetrically.

Applied cautiously: a two-column grid in the stat band reads as pairs. But symmetry is not forced where the
content is asymmetric — a three-quarters plus one-quarter split is an honest asymmetry, not a violation.

### 12. Common Fate

> This principle refers to the human tendency to perceive visual elements moving in the same direction or
> in unison as grouped.

**This is the one that constrains motion in this repository.** Two elements that animate together are read
as one thing. Therefore coordinated transitions are permitted (they state a relationship) and uncoordinated
ones are noise. It is also why the repository permits exactly one `animate-pulse` in the tree: two
pulsing elements have common fate and would read as a single unit.

## How the twelve map onto this repository

| Principle | Implemented by | Where it is visible |
| --- | --- | --- |
| **Emergence** | the whole-before-parts reading of a stat band | A band reads as five stats before any figure is read. |
| **Closure** | honest partial indicators; empty slots drawn as dashed | A missing ancestor reads "slot open", not closed up. |
| **Common Region** | one card per concept; one fieldset per group | `DESIGN-CORPUS.md` D-8 equivalent in the corpus. |
| **Continuity** | the timeline rail; the stepper | The rail never breaks mid-run. |
| **Proximity** | labels inside their `<label>`; provenance beside its figure | Every field and every mark. |
| **Multistability** | one glyph one meaning (D-251); no ambiguous icons | A flame means one thing, not three. |
| **Figure/Ground** | both themes; the raised-on-sunken ramp | Panel above sunken in light and in dark. |
| **Invariance** | one icon family, one grid (D-111, D-84) | A glyph is recognisable at any size in the band. |
| **Prägnanz** | simple shapes; one visual idea per card | No compound geometry in a control. |
| **Similarity** | one component per role | A button looks like a button everywhere. |
| **Symmetry and Order** | the stat band's paired columns | Used where the content is genuinely paired. |
| **Common Fate** | one `animate-pulse` in the tree; coordinated transitions only | Motion states a relationship or does not ship. |

## What this framework decides that the others do not

Gestalt is the only framework here that is about **perception rather than behaviour**. It answers questions
the others cannot: not "is this consistent" but "will two things that share a surface be read as one
group". Three of its principles are load-bearing in ways that are easy to get wrong:

- **Closure cuts both ways.** It makes a partial shape readable as whole, which is fine for decoration and
  dangerous for data. The repository's rule is that the *shape* may close but the *figure* may not.
- **Similarity is a promise.** Sharing a visual characteristic promises shared behaviour, so two
  look-alikes with different behaviour is a broken promise and not a style inconsistency.
- **Common Fate forbids decorative motion.** Anything that moves together is one thing. That is a stronger
  constraint on animation than any duration rule, and it is why the motion budget here is one pulse.

## Checkable rules derived

| Id | Rule | Source |
| --- | --- | --- |
| GT-1 | A group boundary is a closed region, not proximity alone, where the grouping is meaningful. | common region |
| GT-2 | A partial indicator may look complete but must not claim completeness in its text. | closure |
| GT-3 | One glyph carries one meaning across the whole product. | multistability |
| GT-4 | Two elements that look alike must behave alike. | similarity |
| GT-5 | Elements that animate together are read as one; nothing animates decoratively. | common fate |
| GT-6 | A sequence keeps an unbroken spine. | continuity |

## Change log

| Date | Change |
| --- | --- |
| 2026-10-21 | Created from IxDF. Attribution table added because this repository previously attributed Closure, Continuity and Common Fate to the wrong collections. |
