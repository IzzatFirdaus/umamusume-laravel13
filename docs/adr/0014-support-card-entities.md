# ADR-0014: Support card entities — authorized for Phase 1

Status: **ACCEPTED** (owner ruling 2026-09-30, Slice 2 authorization). Supersedes ADR-0005.

Date: 2026-09-30, corrected 2026-10-01 against the source data and the shipped schema
Deciders: product owner (authorization granted, review returned), implementing agent (draft)
Relates to: `ADR-0005` (declined in Phase 1, reopened by the owner), `PRD.md` §6.9 (non-goal lifted),
`UMAMUSUME_REFERENCE.md` §1.4 (support card system), `config/scenarios.php` (scenario-linked characters),
`2026_09_30_142618` (creates the tables), `2026_09_30_151945` (corrects four things the first got wrong)

## Problem

The owner authorized building support card entities for Phase 1, lifting the §6.9 non-goal. This ADR
records the schema decisions, the linkage shape, the data source, and what the source forced changed.

## Decision: reference data plus run linkage; collection stays out

| Entity              | What it is                                            | Built?   | Reason                                                                                                     |
| ------------------- | ----------------------------------------------------- | -------- | ---------------------------------------------------------------------------------------------------------- |
| `SupportCard`       | published reference data about the game               | yes      | Same class of fact ADR-0004 already admitted: engine-owned, provenance-bearing, `is_manual` protected      |
| `DeckSlot`          | the six cards a Trainer equipped for one run          | yes      | Without it a logged run cannot be re-read: the deck decides training yield as much as the turns do         |
| `UserSupportCard`   | the Trainer's collection: level, breaks, perk level   | no       | Collection tracking is still the feature §6.9 cut. The deck records card *identity*, not ownership state   |

ADR-0005 recommended splitting exactly here, and the owner's authorization took that split.

## Corrections the data forced on the first implementation

All four were found by checking the shipped schema against `support-cards.json` rather than against the
intent behind it, and all four are landed in
`2026_09_30_151945_correct_support_card_schema_and_constraints`.

### 1. `char_id` is not a foreign key, and declaring it one would have deleted the evidence

The first migration wrote `foreign('char_id')->references('id')->on('umamusume')`. Two independent
reasons it cannot work:

- **Different number spaces.** `umamusume.id` is a local surrogate autoincrement key; the GameTora
  character id is held in `umamusume.external_ref`. A card's `char_id` of 1001 was being tested against a
  key that will never hold it, so the constraint rejected trainable cards as readily as staff cards.
- **23 of the 559 records point outside the catalogue entirely.** `char_id` in the 9000 block covers the
  NPC staff: Tazuna Hayakawa 9001, Aoi Kiryuin 9004, Riko Kashimoto 9006, Light Hello 9008, Darley
  Arabian 9040, Speed Symboli 9047 and the rest. The document that feeds `umamusume` is
  `gametora-characters.e9e9ee6d.json`: 268 records whose `char_id` runs 1001 to 1149, none in the 9000
  block, so those characters are not trainee rows and will not become one. *[Dated correction
  2026-10-01: this paragraph first cited `characters.json` and its 163 records. That file is not the
  trainee feed and it does hold 17 ids in the 9000 block; the measurement was taken against an `id` key
  the file does not have, so every row read back as zero and the range printed as `0 .. -`. The
  conclusion is unchanged, the evidence named was wrong.]* *[Dated erratum 2026-10-03: the 23 above is
  the staff-block count and nothing more, and it was later read as the answer to a different question.
  The support-card dispatch asked how many cards resolve to no trainee through
  `umamusume.external_ref = 'gametora:char:{char_id}'`, and that number has never been 23. Measured
  against the working database on 2026-10-03, after the card source was re-run from its committed body:
  322 of the 559 resolve and 237 do not, being the 23 staff cards above plus 214 whose character the
  feed documents but this catalogue does not hold a row for. The 23 stands as written; the dispatch
  premise that reached three code comments as "41 of the 559 resolve to nobody" was wrong in its figure
  and wrong in its source, since it cited this bullet for a claim this bullet does not make. All three
  comments now name the two reasons and print no count, because the second of them moves with the
  roster. Measurement and wording: `docs/research-scratch/PLANS-AND-BRIEFS.md`
  §"D-30 widening proposal, 2026-10-03: the support-card catalog surface".]*

The second reason is the one that matters. Those 23 cards are **precisely the records the Scenario Link is
derived from**: 10 of the 13 scenarios in `scenarios.json` list a 9000-block id in
`scenario_linked_characters`. A constraint that nulls them does not tidy the data, it deletes the only
evidence the badge rests on.

The shape to copy was already in the repo: `character_cards` keeps the export's own `card_id` as a plain
column and carries a separate `umamusume_id` only where a local join is genuinely wanted. `char_id` is
now a plain column. No `umamusume_id` is added, because nothing in this slice reads a support card's
trainee through the local catalogue, and a column with no consumer is a second source of truth for a fact
the row already holds.

### 2. `name` is dropped: the source publishes no composed card name

`support-cards.json` has no `name_en` field. It has `char_name` ("Special Week") and `title_en`
("[Tracen Academy]") and nothing between them. A stored `name` could only ever be a composed string this
tool invented, which fails the floor that no fact is stored without provenance. `char_name` holds the
source value; `SupportCard::displayName()` composes the label at the view boundary.

### 3. The CHECK constraints did not exist

The first migration put `->check(...)` on four column definitions and the report described them as
enforced. **They rendered no SQL at all.** `Blueprint::check()` does not exist as a table method
(`BadMethodCallException`), and as a column modifier the SQLite grammar drops it silently. The DDL
`2026_09_30_142618` actually produced contains no `CHECK` token in any of the three tables, which is why
`slot_position` 7 inserted without complaint.

The correction has two parts, because the two layers catch different things:

- The CHECKs are now real, written in raw SQL where SQLite does emit and enforce them. Verified against a
  freshly migrated database: positions 7 and 0 rejected, each of the six accepted, `rarity` 5 rejected,
  `type` `bogus` rejected, `calc` `flat` rejected.
- `DeckSlot::booted()` additionally guards the range on `saving` with an `InvalidArgumentException`. The
  DDL cannot be the only gate, because factories reach the table without passing through a form request,
  and a constraint whose enforcement nothing in the suite can see is a constraint a later refactor drops
  without noticing.

The test asserting the range was deleted in the first pass on the grounds that SQLite "doesn't enforce"
it. That reasoning was wrong in both directions: it accepted an unenforced constraint as a feature, and it
removed the one check that would have shown the gap.

### 4. `support_effects.calc` permitted values the source never emits

The CHECK allowed `flat`, `mult`, `add`, `level`. Across all 35 records the actual domain is `mult` (3),
`add` (1) and **absent** (31). `flat` and `level` are this repository's own prose for the non-declaring
effects (§1.4.8), not export values.

The distinction is load-bearing rather than cosmetic: §1.4.8's finding is that *exactly* the effects
declaring `calc` combine multiplicatively, so a stored `flat` would claim a fourth combining mode the
client does not have, in the one column an engine would branch on. The rebuilt CHECK admits `mult`, `add`,
or nothing.

## Linkage shape: join table

Six slots per run is one-to-many, so `deck_slots` (`training_run_id`, `support_card_id`, `slot_position`
1..6, unique per run and position) rather than six columns on `training_runs`. Six columns would make
"which slots are empty" a null check repeated in every query, and would make a slot reorder a
multi-column update.

`slot_position = 6` is the friend slot. The role belongs to the **slot**, never to the card: the deck
editor labels the sixth position "Friends" and any card may occupy it, so a stat card parked there is a
legal deck (ADR-0005 correction 1, which stands).

The write is delete-then-insert inside a transaction, not `syncWithoutDetaching`. A deck slot is a
position, so a card moving from slot two to slot three must leave slot two, and an upsert keyed on the
card alone would leave it standing in both, against the unique index.

## Migration default: pre-existing runs get no deck

Every `training_runs` row that predates this slice has zero `deck_slots`. That is the correct default and
it is not a gap to fill: a deck is something only the Trainer can supply, and no source could infer it
after the fact. The alternatives, seeding six slots from the scenario or from the trainee's own card,
would state something about someone's setup that nobody asserted.

Two consequences follow, both implemented:

- The panel renders an empty state that **names the absence** ("No support cards recorded for this run")
  rather than six empty frames, per C-7 and D-220.
- `TrainingRunResource` emits `deck: []` on a run whose deck *was loaded* and **omits the key** where it
  was not. The index does not eager-load slots, so returning `[]` there would assert that the Trainer
  equipped nothing, from a query that did not look.

## Source and provenance

`support-cards.json` (559 records) and `support_effects.json` (35 records): the GameTora data export
pulled 2026-09-27, held locally at `research-scratch/data/json/`. Same base URL family
`config('uma.sources')` already allowlists for the other datasets, so no new source is introduced.

Corroborated against `UMAMUSUME_REFERENCE.md` §1.4.1 and §1.4.2 by counting the file rather than
restating it: 559 records; rarity 1→146, 2→101, 3→312; 251 carrying `release_en`; type distribution
speed 125, guts 111, power 99, stamina 97, intelligence 99, friend 23, group 5; every `effects` inner
array exactly 12 wide. Each figure matches the reference's published counts.

Provenance rules are FR-B's: every row lands with `source_url` and `fetched_at`, and the fetch engine
never writes a row whose `is_manual = true`.

## Held: card tier labels

**Tier labels are held because no current Global source for card-tier assessment is available.** Game8's
tier list is dated 2026-09-23 and scored at Max Limit Break against a `[Global]` state this repository has
not re-read; GameWith's ranking is `[JP]`; uma.guide's currency is unconfirmed; and the legacy PDF's
S+/S/A/B column is the same vintage as the rest of that document, which sits in `docs/deprecated/` and is
outside this slice's edit fence. A tier column populated from any of them would be a dated third-party
ranking presented as product truth.

**R75 was checked and does not govern this.** R75 (`docs/design-research/verification/slice-15-2026-09-29.md`
§2, strict tier nullification) concerns `scenario_slots.tier` and the **race** grade labels Pre-OP, OP, G3,
G2, G1 and EX. It says nothing about support-card strength tiers. The constraint holding card tiers is
source availability, not the ruling, and stating it that way matters: a reader who finds "R75 does not
apply" written as the reason for a hold will go looking for the wrong gate.

## Deviations from the brief, stated

- **No `is_scenario_link` column.** Scenario Link is derived by `SupportCard::isScenarioLink($scenarioKey)`
  against `config('scenarios.php')`'s `scenario_links`, which is keyed by character name. A stored flag
  would freeze a per-scenario fact into the card and be wrong the moment the run's scenario changes.
- **No `unique_perk_effects[]`,** and none is possible: the perk's values are absent from the export and
  from every source here (ADR-0005 correction 3, which stands).
- **No `max_level`,** which is `base(rarity) + 5 × breaks` — a derivation from two values the app does not
  hold, since collection state is out of scope.
- **The deck picker offers `release_global IS NOT NULL` cards only,** matching the Global-only audience
  ruling. A card the run already uses is added back, so a Trainer logging an older or JP-only deck is
  never shown a slot they cannot re-select their own card in.
- **The API is read-only** (`GET /api/v1/support-cards`, `/{id}`). Adding a write endpoint would break
  `ApiV1ValidationEnvelopeTest`'s pinned premise that the 422 branch is unreachable because the api has no
  write; the deck is written through the web UI, where every other Trainer-owned write already lives.

## Consequences

- `ARCHITECTURE.md` §3 and `ARCHITECTURE-ESSENTIALS.md` carried the pre-ruling text listing these tables as
  forbidden. They were stale as of this ruling and are corrected with it, rather than left to contradict
  the code.
- `stat-band.blade.php` printed "breakthrough not tracked + deck untracked." The second half became false
  the moment the panel shipped, so the line now says only what is still missing. It also states, from a
  check rather than an assumption, that **no card in the catalogue raises these ceilings**: the five
  「限界値アップ」 effects (ids 20 to 24) are carried by zero of the 559 records, which §1.4.8 already
  recorded and this slice re-verified against the file.

## Not designed here

Effect-value computation at runtime (which card was on which tile in which turn), friendship-trigger
arithmetic, hint-level accumulation, limit-break material economy, Unique Perk values, and any collection
surface. §1.4.3–§1.4.4 describe the mechanics; none become schema until asked for.
