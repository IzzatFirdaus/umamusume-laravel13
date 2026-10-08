# ADR-0023: Record the deck slot's owned-or-rented state on the slot

Status: **Proposed** — drafted 2026-10-09 for the owner's ruling (the D3 remediation of the
Rice Shower / Unity Cup UX walk). The column is built against this draft; the owner accepts or
rejects it. Amends `ADR-0014`'s "no ownership column" line by dated erratum, not by rewrite.

Date: 2026-10-09
Deciders: product owner (ruling owed), implementing agent (draft)
Relates to: `ADR-0014` (the deck it adds to), `ADR-0020` (the 2.0 program), `PRD.md` **FR-A-4**
(the support deck step), **FR-A-9**, D3

## Context

`ADR-0014` authorized `deck_slots` as the six cards a run was equipped with and recorded, in the
same table, that the deck holds card **identity** and not ownership state: "`deck_slots` has no
column for this and adding one is the owner's call". The setup wizard's deck step kept the
owned-or-rented flag in its session draft, because a session key needs no migration, and the
run-scoped builder shipped the one value it could read (`OWNED`) while saying in words that the run
record kept nothing.

The Rice Shower / Unity Cup UX walk (2026-10-09) measured the consequence. The friend card in slot
six was borrowed from another account; the wizard recorded `RENTED`; the run was created; and
`/training-runs/{run}/deck` read `Owned: true, Rented: false`. The source's one borrowed card became
indistinguishable from an owned one at the moment the run existed, and no surface could put it back,
because nothing had written it down.

## Decision

`deck_slots` gains one nullable `ownership` string, holding one of `DeckSlot::OWNERSHIP`
(`OWNED`, `RENTED`), and null meaning **not recorded**.

- **Per slot, not per card.** The fact belongs to the position, the way the friend-slot role does
  (`ADR-0014` correction 1): a card can be owned in one run's deck and borrowed in another's.
- **Written by both writers.** The wizard path (`StartCareerRequest::ownershipByPosition()` →
  `PreflightController::store()`) carries the flag the deck step already held; the run-scoped path
  (`StoreDeckRequest::ownershipByPosition()` → `TrainingRunController::syncDeck()`) records what the
  builder's toggle says.
- **Never derived.** The flag is read from `deck_slots`, never from the player's current inventory.
  The historical run state stays stable when the player's ownership changes later, which is the
  whole point of recording it.
- **Null is an absence, not a default.** A run that predates this column, or a slot whose Trainer
  never classified it, holds null and renders `N/A` with a `title`. Defaulting it to `OWNED` would
  state a fact nobody asserted (`AGENTS.md` §5).
- **A surface with no control preserves what is stored.** The run screen's deck panel posts six cards
  and no flag; the write keeps the slot's recorded value rather than erasing it, so a re-save from
  that screen cannot destroy the builder's answer.

`DeckSlot::booted()` guards the value on save the same way it guards `slot_position`, because
factories and seeders reach the table without passing through a Form Request.

## Explicitly unchanged

- **The collection stays cut.** This is not `user_support_cards` and not collection tracking: no
  level, no limit break, no perk level, no "which cards the player owns". `ADR-0014`'s split stands.
- **No derivation of the flag from inventory.** A future collection surface does not become the
  source of this column.
- **The flag is not a scoring input.** Nothing computes a yield, a bonus or a recommendation from it
  (`ADR-0020` §3).

## Consequences

### 1. `ADR-0014` is amended by erratum, not rewritten

`ADR-0014`'s "the deck records card *identity*, not ownership state" sentence stands as written and
gains a dated erratum pointing here. A reader of §6.9's split must be able to see that the recording
case was separated from collection tracking rather than that the line quietly moved.

### 2. The wizard's draft and the run's record now agree

The flag no longer exists only in a session bag. A career started from the wizard carries the flag it
entered, and the run-scoped builder reads back exactly that.

### 3. What this does not close

Card **levels** and limit breaks are still unrecorded (`ADR-0014`'s held list), and the run's
*reached* effect values are still unrecorded; the builder's analysis continues to use catalog
anchors. Those are separate facts with separate sources, and this column says nothing about them.
