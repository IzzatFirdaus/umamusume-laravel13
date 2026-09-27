# ADR-0005: Support card entities — proposed, and blocked on a PRD non-goal

Status: **PROPOSED. Not accepted, not built.** This ADR exists because a brief dated 2026-09-27 asked
for three support-card entities to be written into `ARCHITECTURE.md`, and `PRD.md` §6 item **9** says
"**No support-card database in Phase 1.**" The two cannot both stand, so the entities are specified
here for the owner's decision rather than adopted silently. `AGENTS.md` escalation 2 routes a
non-goal request to the human owner, and the Architect "proposes scope changes to the human, never
adopts them silently".
Date: 2026-09-27
Deciders: product owner (decision outstanding), implementing agent (draft)
Relates to: `ADR-0003` (Phase 1 schema expansion), `ADR-0004` (reference data with provenance),
`PRD.md` §6.9 and §7, `UMAMUSUME_REFERENCE.md` §1.4.1, §1.4.2, §1.4.5, §1.4.7,
`docs/design-research/DESIGN.md` §6.0b, §6.5b

## Problem

A run's training outcome is decided as much by the six-card deck as by the turns entered, and the app
currently has no vocabulary for a support card at all: no table, no model, no route, and no PRD
requirement. The brief asks for three entities — `SupportCard`, `UserSupportCard`, `DeckSlot` — so a
Trainer can record a deck and read the effects it produces.

The evidence needed to design them now exists. `UMAMUSUME_REFERENCE.md` §1.4.7 records the decoded
effect ladder, the floor-interpolation rule, the per-level effect unlocks, the Unique Perk's separate
level axis, and the derivation of Scenario Link, all cross-checked against the client's own frames and
against a GameTora page read at three selector levels.

## The blocking conflict, stated precisely

`PRD.md` §6.9 is not a vague omission; it names the legacy feature it eliminates — uma-tracker's
abandoned promise, never built anywhere, with repo #4's support-card component left as an empty stub.
Two of the three requested entities sit on opposite sides of that line, and the split is the useful
thing this ADR contributes:

| Entity | What it actually is | Fetchable? | PRD citation today |
|---|---|---|---|
| `SupportCard` | published reference data about the game | yes, from the same export `ADR-0004` already ingests | none. Closest relative is FR-B, which is scoped to the **Umamusume catalogue**, not to support cards |
| `UserSupportCard` | the Trainer's own collection: level reached, breaks done, perk level | **no** — it is one player's account state | none, and this is precisely the tracker feature §6.9 cut |
| `DeckSlot` | a Trainer's deck for a run | no | none. `training_runs` has no deck story in §3 or §4 |

So `SupportCard` is the same *class* of fact ADR-0004 already admitted (engine-owned reference data with
provenance, `is_manual` protected, never hand-seeded), while the other two are a new user-facing
capability with no user story behind it. Admitting all three under one approval would smuggle the
second kind in under the argument that carried the first.

## Corrections the evidence forces on the brief's shape

Applied to the design below, each with its reason.

1. **`is_friend_card` is not a card attribute.** The client's deck editor labels the **sixth slot**
   `Friends`, and any card may occupy it: in `Screenshot 2026-07-15 155016.png` the Friends slot holds
   Super Creek `[Piece of Mind]`, whose type is `stamina`. `slot_position` alone carries the role
   (1–5 training, 6 friend). A boolean on the card would be wrong the moment a Trainer puts a stat card
   there, which the client permits.
2. **`type` has seven values and one of them is not the client's word.** The export uses `speed`,
   `stamina`, `power`, `guts`, `intelligence`, `friend`, `group`. The client calls `intelligence`
   **Wit**, and "Pal" for `friend` is Game8's label rather than a captured client string — the only
   friend-adjacent word proven from the client is `Friends`, and it names the slot. Store the export key,
   map to the client word at the view boundary, and do not present "Pal" as UI copy until the client's
   own type label is captured.
3. **`unique_perk_effects[]` cannot be populated from any source in this repository.** The perk prints
   two effect names, and for Tokai Teio `[Dream Big!]` the second one, Initial Speed, is absent from
   that card's `effects` array entirely. The perk's *values* are likewise absent. Importing a perk
   effect list means hand-typing it, which collides with the pipeline's own rule that a fact without
   provenance is deleted, not stored.
4. **No per-level effect table.** Store the eleven anchors as they arrive and compute the displayed
   value with floor-interpolation (§1.4.7). A materialised 50-row table per card per effect is 28,000
   rows of derived data that can silently disagree with the rule that produced it.
5. **`max_level` is derived, not stored.** It is `base(rarity) + 5 × limit_break_count`, with bases
   20 / 25 / 30 for R / SR / SSR, confirmed from the client's break diamonds and level readouts and
   independently from where each rarity's value ladder stops.
6. **Scenario Link is a join, not a column.** It is `support_card.char_id ∈ scenario.linked_characters`,
   proven by the one badged card in the frame being the deck's only Unity Cup linked character. Storing
   it per card would freeze a per-scenario fact into the card.

## Proposed shape, if the owner accepts the scope

Reference domain — same pipeline, same provenance rules as `scenarios` and `skills`:

- `support_cards`: `support_id` (unique), `char_id` (FK to `umamusume`, null for the 23 `friend` and 5
  `group` cards whose `char_id` points at NPC staff), `name`, `name_ja`, `title_en`, `title_ja`,
  `rarity` (1/2/3), `type` (the seven export keys), `release_jp`, `release_global`, `release_status`,
  `effects` (json: list of `[effect_id, v1 … v11]` anchors, `-1` meaning no anchor), `source_url`,
  `fetched_at`, `is_manual`.
- `support_effects`: `effect_id` (unique), `name_en`, `name_ja`, `calc`, `symbol`, `description_en`,
  `source_url`, `fetched_at`. Small, stable, and the join target that makes `effects` readable in the UI
  without hardcoding a name per id.

Trainer domain — new user stories required, and this is the part §6.9 currently forbids:

- `user_support_cards`: `support_card_id`, `level`, `limit_break_count` (0–4), `unique_perk_level`
  (nullable, because nothing here says what raises it or what its cap is). Owner-entered, so
  `is_manual` semantics apply and the fetch engine must never write it.
- `deck_slots`: `training_run_id`, `user_support_card_id`, `slot_position` (1–6, unique per run). The
  friend slot is `slot_position = 6`; no separate flag.

If the owner keeps §6.9 intact, the reference half can still be admitted on its own — it changes what
the catalog can display, not what the tool tracks — and the Trainer half stays out. That is the
recommended split, and it is the reason the two domains are separated above rather than merged into one
"support card feature".

## Decision required from the owner

1. **Reference data only.** Accept `support_cards` + `support_effects` with an FR citation added to
   §4 (FR-A-6 proposed), keep §6.9 for the collection and deck, and stop there. Smallest change, and it
   makes the run detail page able to explain *why* a deck was strong without storing anyone's collection.
2. **Reference data plus deck recording.** Also accept `deck_slots` and `user_support_cards`, add a
   user story for "record the deck I used", and amend §6.9 to say the non-goal was the *legacy*
   support-card database rather than any support-card table. This is the brief as written.
3. **Nothing.** Keep §6.9 whole, leave §1.4.7 as reference documentation only, and let the design
   package keep specifying the components without data behind them.

Option 1 is recommended: the deck-composition UI in `docs/design-research/DESIGN.md` §6.5b is already
specified, and the reference tables are what let it render real names and real effect values instead of
sample content, which is the part that is currently blocked. Options 2 and 3 both leave that blocked.

## Consequences if accepted

- The fetch engine gains a third source dataset. `config/uma.php` already holds the GameTora entry;
  `support-cards.json` and `support_effects.json` are new parser + fixture pairs under FR-B's rule set,
  and neither has a display-name ambiguity the way character cards do, so the `ScenarioSourceParser`
  shape (no cross-reference) fits better than the matching pipeline.
- `docs/design-research/CONSTRAINTS.md` gains rules D-277 to D-282 and gate G-58, and
  `docs/design-research/DESIGN.md` §6.0b and §6.5b gain the measured glyph set, the deck-editor anatomy
  and the card-detail anatomy. Those are already applied in this change, because they document the
  client rather than the app, and they hold under all three options above.
- `ARCHITECTURE.md` §3 gains a "Proposed, not built" subsection pointing here. It is deliberately
  labelled, because an unlabelled entity in the system-design document reads as decided to the next
  agent that finds it — which is how the phantom tables in earlier briefs kept recurring.

## Challenge and re-verification, 2026-09-27 (second brief)

A follow-up brief contested correction 6, arguing the Scenario Link is "an intrinsic property of the
Support Card entity, defined at release", that the join "fails for Group cards", that it "fails for cards
linked to NPCs… who lack a standard `char_id`", and that the right shape is a column or a
`card_scenario_bonuses` table. Every one of those is checkable, so it was checked against the export
rather than argued.

**The two named failure cases do not occur.** All five group cards carry exactly one `char_id` —
`30067`/1017, `30081`/1001, `30137`/9040, `30180`/1068, `30241`/9047 — so a group card joins cleanly;
it is not a set of char_ids in the data. And the NPCs hold ordinary `char_id`s in the 9000 block: Tazuna
Hayakawa 9001, Aoi Kiryuin 9004, Sasami Anshinzawa 9005, Riko Kashimoto 9006, Light Hello 9008, and the
Pal cards keyed to them are ordinary support records (`10021`/9001, `10022`/9004, `10060`/9006,
`10083`/9008). **Twelve** of the 73 linked-character entries across all fourteen scenarios carry a
`char_id` in the 9000 block — the non-trainee cast: staff such as Tazuna Hayakawa 9001, Aoi Kiryuin 9004,
Yayoi Akikawa 9002 and Riko Kashimoto 9006, and legends such as Darley Arabian 9040 and Speed Symboli
9047 — and 72 of 73 own at least one support card, the single exception being Loves Only You (1132) under
Beyond Dreams. The linked entries' own `id` field matches no `support_id` anywhere in the file, which is
what confirms the list is keyed by **character** rather than by card.

**The decisive point is provenance, not elegance.** `support-cards.json` carries no scenario field of any
kind. An intrinsic `scenario_link` column therefore has no source: it could only be populated by hand,
which collides with the pipeline's own floor ("a fact without provenance is deleted, not stored") and with
the rule that fetched content never overwrites `is_manual` rows. A derived badge costs nothing and cannot
drift; a hand-keyed column is 559 rows of transcription with no authority behind it.

**Two parts of the challenge are accepted, and they are different from the one it was making.**

1. *Group cards are semantically thin under the join, even though they join.* `training_events__group.json`
   records six character ids behind card `30081`, so a group card genuinely represents several characters
   while carrying one `char_id`. If the client ever badges a group card on the strength of a member who is
   not its representative, the join is wrong and the correct shape is a link table over the membership set.
   That is a real risk, and it is testable the moment a frame shows a badged group card.
2. *The badge and the bonus are not the same thing, and only the badge was claimed.* A
   `card_scenario_bonuses` table for **what a link grants** is a legitimate entity and this ADR never
   argued otherwise — correction 6 was about the `Scenario Link` pill's presence, which is what the frame
   shows. What blocks that table today is data, not architecture: no source in this repository publishes
   link bonus magnitudes, so it would be created empty and filled by hand.

**Evidence strength, stated.** The derivation rests on one frame (`155016`) with one positive case and five
negatives in a single Unity Cup deck. It is consistent, it is not yet broad. A frame containing two linked
characters, or a badged card in a scenario whose list does not contain its `char_id`, would falsify it, and
the correction should be revisited on sight of one.

## Not designed here

The effect-value formula's *inputs at runtime* (which cards were on which tile in which turn), the
friendship-trigger arithmetic, hint-level accumulation, and anything about limit-break material
economy. §1.4.3 and §1.4.4 describe the mechanics; none of them become schema until the owner settles
the scope question above.
