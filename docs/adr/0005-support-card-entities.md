# ADR-0005: Support card entities — proposed, and blocked on a PRD non-goal

<!-- ADR-CONTRACT
  decision: declined (owner ruling R37, 2026-09-28) — Phase 1 builds no support-card entities; superseded by ADR-0014, which the owner later authorized
  code: superseded — ADR-0014 landed support_cards, support_effects and deck_slots (migrations 2026_09_30_142618, 2026_09_30_151945)
  superseded-by: 0014 (the owner lifted §6.9 and took the reference-plus-deck split this ADR recommended)
  amended-by: 0023 (adds deck_slots.ownership to the deck ADR-0014 authorized)
  open: the collection half stays cut — no UserSupportCard, no level, no limit break, no perk level
-->

Status: **DECLINED for Phase 1** (owner ruling R37, 2026-09-28). The question is closed, not parked, and
**no slice may cite this ADR as permission.** `PRD.md` §6.9 stands whole. The full body — the entity
shapes, the blocking-conflict analysis, the challenge re-verification — is preserved at
`archive/adr/0005-support-card-entities.md`.

**`ADR-0014` reverses this ruling.** The owner authorized the reference half (`support_cards`,
`support_effects`) plus the run-scoped deck (`deck_slots`) on 2026-09-30, taking the §6.9 reversal exactly
as far as the split this ADR recommended and rejecting. `ADR-0023` later added `deck_slots.ownership`.
**The collection half is still cut**, and the six corrections below are what `ADR-0014` cites as standing.

## The six corrections `ADR-0014` cites as standing

1. **`is_friend_card` is not a card attribute.** The client labels the **sixth slot** `Friends`, and any
   card may occupy it. `slot_position` alone carries the role (1-5 training, 6 friend); a boolean would be
   wrong the moment a Trainer parks a stat card there, which the client permits.
2. **`type` has seven values and one is not the client's word.** The export uses `intelligence`; the client
   calls it **Wit**, and "Pal" for `friend` is Game8's label, not a captured client string. Store the export
   key and map to the client word at the view boundary.
3. **`unique_perk_effects[]` cannot be populated from any source in this repository.** The perk's values are
   absent from the export, so importing them would mean hand-typing — colliding with the pipeline's own
   rule that a fact without provenance is deleted, not stored.
4. **No per-level effect table.** Store the eleven anchors as they arrive and compute the displayed value
   with floor-interpolation; a materialised table is derived data that can silently disagree with the rule
   that produced it.
5. **`max_level` is derived, not stored:** `base(rarity) + 5 × limit_break_count`.
6. **Scenario Link is a join, not a column.** It is `support_card.char_id ∈ scenario.linked_characters`,
   proven by the one badged card in the frame being the deck's only linked character. A stored flag would
   freeze a per-scenario fact into the card, and `support-cards.json` carries no scenario field, so an
   intrinsic column would have no source.

## The provenance argument that decided correction 6

`support-cards.json` carries no scenario field of any kind, so an intrinsic `scenario_link` column could
only be populated by hand — colliding with the floor "a fact without provenance is deleted, not stored".
A derived badge costs nothing and cannot drift; a hand-keyed column is 559 rows of transcription with no
authority behind it. Two parts of the 2026-09-27 challenge are accepted and stand: group cards are
semantically thin under the join even though they join, and a `card_scenario_bonuses` table for what a link
*grants* is legitimate but blocked on data, not architecture.

## Reopen trigger

A new PRD story, written by the owner, that authorizes the Trainer's collection (levels, limit breaks,
perk level). `ADR-0014` did not write that story; it took the reference-plus-deck half only.

## Links

- `ADR-0014` (the authorizing ruling), `ADR-0023` (deck ownership), `ADR-0004` (the provenance precedent)
- `PRD.md` §6.9; `docs/UMAMUSUME_REFERENCE.md` §1.4
- Full body: `archive/adr/0005-support-card-entities.md`
