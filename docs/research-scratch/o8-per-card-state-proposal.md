# Proposal: per-card deck state for runs (O-8 2b(d))

**Status.** Proposal only. Stopped at Dispatch C Stage 1 on a PRD-absence premise. Files moved:
none yet. Schema moved: none yet. Migration target only stated here for the owner's review.

## Problem

A logged run currently records only the six support cards by their identity, on
`deck_slots` (`training_run_id`, `support_card_id`, `slot_position` 1..6). Anything else the client
prints about each card at deck-save time is not captured: card level, the limit-break count, the
bond value, the hint level. `docs/UIX-AUDIT-TRAINING-RUNS.md` O-8 names this gap: a run that
survives to be re-read cannot answer "what level was that card at when this run happened".
`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1.4 lists four values per card for the documented
run, and the tool cannot reproduce them.

## Why this proposal exists

`PRD.md` §6.9 partial lift (line 172) cuts the half §6.9 was really about: "no collection: no
`user_support_cards`, no card levels, limit breaks or Unique Perk states, because that is
uma-tracker's abandoned promise and no user story replaced it." US-12 (line 37) repeats the same
body in plainer English: "no card level, limit break or Unique Perk state is stored anywhere
(`ADR-0014`: identity, not collection)." `ADR-0014` records the decision in two places: the
§"Decision" table puts `UserSupportCard: the Trainer's collection: level, breaks, perk level` on
the **no** row with the reason "Collection tracking is still the feature §6.9 cut. The deck
records card identity, not ownership state"; §"Not designed here" lists "hint-level
accumulation" among the off-scope items. The forward plan in `docs/UIX-AUDIT-TRAINING-RUNS.md`
backlog item 3 says "Same gate" referring to item 2's note that a schema proposal with a PRD
citation must precede code.

No PRD section authorizes the change. No owner pre-approval is on the record in the ADRs I can
read. ADR-0014's "Same gate" reference in the backlog is the existing reading of what is and is
not in scope. Three things follow from this.

1. The four values proposed here would have to be added through a PRD amendment plus an ADR
   that supersedes ADR-0014's decision table entry, before any migration or model change.
2. The amendment is a deliberate reversal of the partial-lift language, which cut per-card state
   on the grounds that uma-tracker's abandoned PRD promised it without delivering. The ownerturned
   question is "does this tool want the half §6.9 cut, on a per-run basis, today?" It is not a
   technical question.
3. The proposal below is the smallest readable answer to that question, in case the owner wants it.

## Source of the four values

`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` §1.4 records the four values per card for the run-7
fixture: card level, limit break count, bond value, hint level. These are the values the client
prints at deck-save time in the run header and which a Trainer reads off to log a run. They are
display-only state on the client: the run math does not depend on them. The deck panel in the
tool would render them as a tile if they were captured, so a Trainer reading a logged run sees
the same four numbers a logged run was started with.

## Proposed schema

Four nullable columns on `deck_slots`, alongside the existing three identity columns:

| Column | Type | Default | Why these |
|---|---|---|---|
| `card_level` | unsigned small integer | null | in identity range 1..50 across the catalogue; unsigned small avoids the tinyint ceiling |
| `limit_break` | unsigned tiny integer | null | 0..4 in the client's ladder; tinyint fits without an unreachable cap |
| `bond` | unsigned tiny integer | null | 0..10 in the ladder, sometimes higher with bond-up events; tinyint with a controller-side 0..20 check stays honest |
| `hint_level` | unsigned tiny integer | null | 0..5 on the client's hint ladder; tinyint with a 0..5 controller-side check |

Nullable on every column so an existing row is untouched and a fresh slot fill that leaves the
four blank is just an untrained deck slot. The four values are tied to a `(run, slot_position)`,
not to the card on the catalogue, so the same card in two runs may carry different values.

Naming convention check: the catalogue uses `type`, `rarity`, `char_id` (ADR-0014 correction 1).
The deck slot uses `slot_position` and `support_card_id`. Adding `card_level` keeps the noun
form `card_*` next to the existing `slot_position`. The other three follow with no
abbreviation since the dispatch's column list named them in full. No rename proposed.

## User-facing entry point

The locked-tile view the dispatch's Stage 3 adds to `deck-panel.blade.php` is one place the four
values could ride, with an inline edit disclosure per tile. The other is the existing picker
form: extend the per-slot select so the four small inputs sit beside the chosen card's name.
The picker path stays a `<input type="number">` per value with `min` and `max` constraints
matching the column comment, and accepted only on save. The tile path would be the same four
fields rendered invisibly inside an edit disclosure so the locked view stays a view by default.

Either path satisfies the dispatch's Stage 4 contract. The picker path is the smaller change
because the locked view does not yet exist; the tile path is the cleaner UX once a Trainer has
saved the deck once and is just adjusting values.

## What this proposal is not

- It does **not** model card effects, unique perks, or hint pools: none are captured. ADR-0014
  correction 3 stands: the perk's values are absent from the export and from every source here.
- It does **not** capture friend-training bond arithmetic, hint discounting, or any run-time
  computation. None are captured.
- It does **not** backfill existing rows. Every row that predates this proposal has null on the
  four columns and is left untouched.
- It does **not** import per-card state from any external source. The four values would be
  Trainer-entered only, like the deck itself.
- It does **not** promote hard-coded level, break or hint numbers into copy on any surface. The
  values are stored against a slot; the tile and the picker render what the row holds; nothing
  in `config('uma')` or `config('scenarios.php')` changes.
- It does **not** lift the §6.9 partial-lift language. The partial lift covers the catalogue and
  the deck identity, and that language stays. The proposal only adds per-run state on a single
  table that already exists.

## Alternatives considered

| Alternative | Cost | Why not |
|---|---|---|
| Keep the deck card-only | Zero | The audit's O-8 finding already names the gap, and the run report cannot reproduce the four values without it. |
| A new `deck_card_state` table, normalised | A second table, a foreign key, a join | The values belong on `deck_slots` already: a row exists iff the slot is equipped, and the four values are properties of that equipping, not parallel facts. A second table would let a Trainer edit the four values without touching `deck_slots`, but no surface needs that freedom today. |
| Capture the four as JSON on `deck_slots` | One column, no migration of the others, the values are nested | The catalogue is integer-typed for the four values; a JSON column would push type discipline into the application, and no current use case asks for arbitrary keys. The proposal's four integer columns are the same shape the catalogue would carry. |
| OnDeckLoad, ask GameWith for current values | Runtime fetch on the run page | The four values are per-run, not per-current-state. The run report captures what the deck was *at run start*, and a fetch against today's catalogue will give today's values, not those. The dispatch's audit finding is that the tool cannot reproduce what was true at deck-save time. |
| Wait for OQ-5 to resolve, then redo | Zero | OQ-5 is about skill eligibility, not card run state. The two are independent. |

## What the owner rules on

- **Whether the section 6.9 partial-lift's "no collection" clause extends to per-run per-card
  values.** The clause's plain text covers `user_support_cards`, but ADR-0014's reading extended it
  to `level`, `limit_break`, and `unique_perk` because those are collection facts. The proposal
  asks for the opposite reading on a per-run basis: card level at run start is a per-run fact, not
  a collection fact.
- **Whether a user story replaces umamusume-tracker's abandoned promise.** The PRD US-12
  body cites ADR-0014 with the reason "no user story replaced it", and the proposal names the run
  report as that user story. The owner can accept the proposal and amend §6.9 and US-12, decline
  and keep the partial lift as written, or accept only some of the four values.
- **Column conventions.** The proposal's `card_level`, `limit_break`, `bond`, `hint_level` are
  proposed; the owner may prefer `card_lvl`, `lb`, `bond_pts`, `hint_lvl` to match an existing
  convention. None of those names is currently used in `deck_slots` or `support_cards`, so the
  choice is open.

On approval, the owner should land the PRD amendment (`§6.9` partial lift text and
`US-12` body) plus an ADR that supersedes ADR-0014's decision-table entry for `UserSupportCard`
or reads "no UPPER-cut does not extend to per-run per-card values" by amendment. Re-issue
Dispatch C from Stage 2 against that ruling.
