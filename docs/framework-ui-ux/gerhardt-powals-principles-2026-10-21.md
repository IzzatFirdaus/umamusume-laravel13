# Gerhardt-Powals' cognitive engineering principles

Status: **settled reference with a stated caveat.** Records the ten cognitive engineering principles and
how they bind the screens in this repository.

The primary text could not be read. This file says so plainly rather than presenting a secondary
enumeration as if it were the paper.

## Provenance, and the caveat

| Field | Value |
| --- | --- |
| Paper | Gerhardt-Powals, J. (1996), *Cognitive engineering principles for enhancing human-computer performance*, International Journal of Human-Computer Interaction, 8(2) |
| DOI | [10.1080/10447319609526147](https://doi.org/10.1080/10447319609526147) |
| Primary access attempted | [Taylor & Francis abstract](https://www.tandfonline.com/doi/abs/10.1080/10447319609526147) — **HTTP 403**; the article body is paywalled and the course copies are PDFs the fetch tool cannot read |
| Enumeration source | [a Human-Computer Interface course deck](https://www.brainscape.com/flashcards/gerhardt-powals-cognitive-engineering-pr-4587688/packs/6297555), fetched 2026-10-21 |
| Fetched | 2026-10-21 |

**The caveat, stated where it can be read:** the secondary deck lists **nine** items and carries "group
data in consistently meaningful ways" as a clause *inside* the naming principle. The paper is
conventionally cited as **ten**, with that clause as its own principle. The split between items 5 and 6
below is therefore **unconfirmed**, and this file does not invent a citation for it. Anyone relying on the
count should read the paper.

## The ten, as enumerated

Quoted from the deck where it carries text. Items with no gloss in the source are left without one rather
than given an invented gloss.

### 1. Automate unwanted workload

> Free cognitive resources for high-level tasks. Eliminate mental calculations, estimations, comparisons,
> and unnecessary thinking.

This is the principle that makes a tool more than a form. If the Trainer is doing arithmetic the product
could do, the product has failed this principle.

### 2. Reduce uncertainty

> Display data in a manner that is clear and obvious.

### 3. Fuse data

> Reduce cognitive load by bringing together lower level data into a higher-level summation.

The distinction from item 1 is worth keeping: item 1 removes work the user should not do at all; item 3
removes work the user *could* do but should not have to, by presenting a summation.

### 4. Present new information with meaningful aids to interpretation

> Use a familiar framework, making it easier to absorb. Use everyday terms, metaphors, etc.

### 5. Use names that are conceptually related to function

> Context-dependent. Attempt to improve recall and recognition.

### 6. Group data in consistently meaningful ways

> [… to decrease search time.]

**This is the item whose numbering is unconfirmed** — the deck folds it into item 5 as a trailing clause
reading *"Group data in consistently meaningful ways to decrease search time."*

### 7. Limit data-driven tasks

> Reduce the time spent assimilating raw data. Make appropriate use of color and graphics.

### 8. Include in the displays only that information needed by the user at a given time

No gloss in the source.

### 9. Provide multiple coding of data when appropriate

No gloss in the source. In practice this is what a redundant single-sense marker looks like: a grade as
letter plus position plus word, rather than as colour.

### 10. Practice judicious redundancy

No gloss in the source. Paired with item 9, this is the constraint that keeps redundancy *judicious*:
redundancy for a second channel is a benefit, redundancy within one channel is noise.

## How the ten map onto this repository

| Principle | Implemented by | Where it is visible |
| --- | --- | --- |
| **1 Automate unwanted workload** | `ScenarioCaps`, the Grade Point runway, the finale countdown | The shortfall and the turns remaining are computed. A Trainer is never asked to subtract 212 from 300. |
| **2 Reduce uncertainty** | D-45 four states; the `.trust` vocabulary | A figure is confirmed, calculated, estimated or unknown, and says which. |
| **3 Fuse data** | the cockpit's stat band, the result screen's summary | Per-turn deltas are summed into a stat with a denominator rather than left as a turn list to add up. |
| **4 Meaningful aids to interpretation** | the client's own vocabulary; the "why" lists | Mood is `GREAT ↑ +20%`, not a colour. The percent is the aid. |
| **5 Names related to function** | `lang/en/uma.php`; the Global client strings | The name on the button is the name of the thing it does. |
| **6 Consistently meaningful grouping** | one field grid, one left edge; the stat panel's five plus four | Grouping never varies between screens for the same kind of data. |
| **7 Limit data-driven tasks** | D-94 no chart without a written question; D-95 the turn list before the line chart | The raw table is the fallback, not the primary view. |
| **8 Only the information needed at a time** | progressive disclosure (design-2.0 §35) | A card shows three facts and expands for modifiers. |
| **9 Multiple coding** | D-12 state is never colour-only; grade badges carry a letter | Grade is a letter, a position and a colour together. |
| **10 Judicious redundancy** | D-82 colour dose caps; D-80 every screen earns its elements | Redundancy across channels is required; redundancy within a channel is a defect. |

## What this framework decides that the others do not

**It is the only framework in this set that is about cognitive engineering rather than interface
behaviour.** Nielsen's heuristics ask whether the system communicates; Tognazzini asks whether it is fast
and forgiving; Shneiderman asks whether the dialog closes. Gerhardt-Powals asks **whether the user has to
think**, and specifically whether the product has left arithmetic, estimation and comparison in the user's
head.

That is the whole justification for this product existing as something other than a notes app, which is why
items 1 and 3 are the two that matter most here:

- **Item 1** is the reason the advisor prints `88 short` rather than `212 / 300`.
- **Item 3** is the reason a stat band is a number with a denominator rather than a list of per-turn gains.
- **Item 8** is the reason the modifier breakdown collapses: showing every multiplicative term at once is
  the failure this principle names, even though it is more transparent.

## Checkable rules derived

| Id | Rule | Source |
| --- | --- | --- |
| GP-1 | The product computes a figure the user would otherwise compute. | 1 |
| GP-2 | A derived figure states the rule that produced it and whether the rule is the client's or ours. | 2, and D-256 |
| GP-3 | A summation is labelled as a summation, so the reader knows the parts exist. | 3 |
| GP-4 | A state carries at least two codings. | 9 |
| GP-5 | Redundancy is across channels, never repeated within one. | 10 |

## Change log

| Date | Change |
| --- | --- |
| 2026-10-21 | Created. Primary text paywalled and PDF-only; enumeration from a secondary deck, with the 9-versus-10 split recorded as unconfirmed. |
