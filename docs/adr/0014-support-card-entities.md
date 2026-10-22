# ADR-0014: Support card entities — authorized for Phase 1

<!-- ADR-CONTRACT
  decision: accepted (owner ruling 2026-09-30) — reference data plus run linkage; the collection stays out
  code: built — migrations 2026_09_30_142618, 2026_09_30_151945; DeckSlot::booted(); StoreSupportCardsTest; SupportCardTest
  superseded-by: —
  amended-by: 0023 (adds a nullable deck_slots.ownership recording owned-or-rented per slot), 0005 (its recommended split is the one taken)
  open: card levels and limit breaks are still unrecorded, and the run's reached effect values are still unrecorded — the builder's analysis continues to use catalog anchors; the deck picker's offered set still includes a card already equipped in another slot, so the one-copy rule is refused after the write instead of by the control (2026-10-22 framework check)
-->

Status: **ACCEPTED** (owner ruling 2026-09-30, Slice 2 authorization). Supersedes `ADR-0005`.

## Problem

The owner authorized building support card entities for Phase 1, lifting the §6.9 non-goal. This ADR records
the schema decisions, the linkage shape, the data source, and what the source forced changed.

## Decision: reference data plus run linkage; collection stays out

| Entity | What it is | Built? | Reason |
|---|---|---|---|
| `SupportCard` | published reference data about the game | yes | Same class of fact `ADR-0004` already admitted: engine-owned, provenance-bearing, `is_manual` protected |
| `DeckSlot` | the six cards a Trainer equipped for one run | yes | Without it a logged run cannot be re-read: the deck decides training yield as much as the turns do |
| `UserSupportCard` | the Trainer's collection: level, breaks, perk level | no | Collection tracking is still the feature §6.9 cut |

`ADR-0005` recommended splitting exactly here, and the owner's authorization took that split.

## Corrections the data forced on the first implementation

All four were found by checking the shipped schema against `support-cards.json` rather than against the
intent behind it, and all four are landed in `2026_09_30_151945_correct_support_card_schema_and_constraints`.

**1. `char_id` is not a foreign key, and declaring it one would have deleted the evidence.** Two
independent reasons it cannot work: `umamusume.id` is a local surrogate autoincrement key while the GameTora
character id is held in `umamusume.external_ref`, so a card's `char_id` of 1001 was tested against a key that
will never hold it; and a block of records points outside the catalogue entirely, covering the NPC staff.
**The second reason is the one that matters:** those records are **precisely the ones the Scenario Link is
derived from**, so a constraint that nulls them does not tidy the data, it deletes the only evidence the
badge rests on. The shape to copy was already in the repo: `character_cards` keeps the export's own `card_id`
as a plain column. `char_id` is now a plain column, and no `umamusume_id` is added because nothing in this
slice reads a support card's trainee through the local catalogue.

**2. `name` is dropped: the source publishes no composed card name.** It has `char_name` and `title_en` and
nothing between them. A stored `name` could only ever be a composed string this tool invented, which fails the
floor that no fact is stored without provenance. `char_name` holds the source value;
`SupportCard::displayName()` composes the label at the view boundary.

**3. The CHECK constraints did not exist.** The first migration put `->check(...)` on four column definitions
and the report described them as enforced. **They rendered no SQL at all.** `Blueprint::check()` does not
exist as a table method, and as a column modifier the SQLite grammar drops it silently, which is why
`slot_position` 7 inserted without complaint. The correction has two parts, because the two layers catch
different things: the CHECKs are now real, written in raw SQL where SQLite does emit and enforce them; and
`DeckSlot::booted()` additionally guards the range on `saving`, because factories reach the table without
passing through a form request and a constraint whose enforcement nothing in the suite can see is a
constraint a later refactor drops without noticing. The test asserting the range was deleted in the first
pass on the grounds that SQLite "doesn't enforce" it — that reasoning was wrong in both directions: it
accepted an unenforced constraint as a feature and removed the one check that would have shown the gap.

**4. `support_effects.calc` permitted values the source never emits.** The CHECK allowed `flat`, `mult`,
`add`, `level`. Across all records the actual domain is `mult`, `add` and **absent**. `flat` and `level` are
this repository's own prose for the non-declaring effects, not export values. The distinction is load-bearing
rather than cosmetic: the finding is that *exactly* the effects declaring `calc` combine multiplicatively, so
a stored `flat` would claim a fourth combining mode the client does not have, in the one column an engine
would branch on.

## Linkage shape: join table

Six slots per run is one-to-many, so `deck_slots` (`training_run_id`, `support_card_id`, `slot_position`
1..6, unique per run and position) rather than six columns on `training_runs`. Six columns would make "which
slots are empty" a null check repeated in every query, and would make a slot reorder a multi-column update.

`slot_position = 6` is the friend slot. The role belongs to the **slot**, never to the card: the deck editor
labels the sixth position "Friends" and any card may occupy it, so a stat card parked there is a legal deck
(`ADR-0005` correction 1, which stands).

The write is delete-then-insert inside a transaction, not `syncWithoutDetaching`. A deck slot is a position,
so a card moving from slot two to slot three must leave slot two, and an upsert keyed on the card alone would
leave it standing in both, against the unique index.

## Migration default: pre-existing runs get no deck

Every `training_runs` row that predates this slice has zero `deck_slots`. That is the correct default and it
is not a gap to fill: a deck is something only the Trainer can supply, and no source could infer it after the
fact. The alternatives, seeding six slots from the scenario or from the trainee's own card, would state
something about someone's setup that nobody asserted.

Two consequences follow, both implemented: the panel renders an empty state that **names the absence** rather
than six empty frames, per C-7 and D-220; and `TrainingRunResource` emits `deck: []` on a run whose deck *was
loaded* and **omits the key** where it was not, because returning `[]` there would assert that the Trainer
equipped nothing from a query that did not look.

## Source and provenance

`support-cards.json` and `support_effects.json` are the GameTora data export held locally under
`research-scratch/data/json/`. Same base URL family `config('uma.sources')` already allowlists for the other
datasets, so no new source is introduced. Corroborated against the reference guide by counting the file rather
than restating it: the record counts and the rarity distribution match the published figures.

Provenance rules are FR-B's: every row lands with `source_url` and `fetched_at`, and the fetch engine never
writes a row whose `is_manual = true`.

## Held: card tier labels

**Tier labels are held because no current Global source for card-tier assessment is available.** Game8's tier
list is dated and scored at Max Limit Break against a `[Global]` state this repository has not re-read, and
the legacy PDF's column is the same vintage as the rest of that document, which sits outside this slice's edit
fence. A tier column populated from any of them would be a dated third-party ranking presented as product
truth.

**R75 was checked and does not govern this.** R75 concerns `scenario_slots.tier` and the **race** grade
labels. It says nothing about support-card strength tiers. The constraint holding card tiers is source
availability, not the ruling, and stating it that way matters: a reader who finds "R75 does not apply"
written as the reason for a hold will go looking for the wrong gate.

## Deviations from the brief, stated

- **No `is_scenario_link` column.** Scenario Link is derived by `SupportCard::isScenarioLink($scenarioKey)`
  against `config('scenarios.php')`'s `scenario_links`, which is keyed by character name. A stored flag would
  freeze a per-scenario fact into the card and be wrong the moment the run's scenario changes.
- **No `unique_perk_effects[]`,** and none is possible: the perk's values are absent from the export and from
  every source here (`ADR-0005` correction 3, which stands).
- **No `max_level`,** which is `base(rarity) + 5 × breaks` — a derivation from two values the app does not
  hold, since collection state is out of scope.
- **The deck picker offers `release_global IS NOT NULL` cards only,** matching the Global-only audience
  ruling. A card the run already uses is added back, so a Trainer logging an older or JP-only deck is never
  shown a slot they cannot re-select their own card in.
- **The API is read-only.** Adding a write endpoint would break `ApiV1ValidationEnvelopeTest`'s pinned
  premise that the 422 branch is unreachable; the deck is written through the web UI, where every other
  Trainer-owned write already lives.

## Consequences

- `ARCHITECTURE.md` §3 and `ARCHITECTURE-ESSENTIALS.md` carried the pre-ruling text listing these tables as
  forbidden. They were stale as of this ruling and are corrected with it, rather than left to contradict the
  code.
- The stat band printed "breakthrough not tracked + deck untracked." The second half became false the moment
  the panel shipped, so the line now says only what is still missing. It also states, from a check rather than
  an assumption, that **no card in the catalogue raises these ceilings**: the five Max-Stat effect ids are
  carried by zero of the records, which the reference guide already recorded and this slice re-verified.

## Not designed here

Effect-value computation at runtime (which card was on which tile in which turn), friendship-trigger
arithmetic, hint-level accumulation, limit-break material economy, Unique Perk values, and any collection
surface.

## Links

- `PRD.md` §6.9 (non-goal lifted), §6.11, §6.12, FR-A-4, FR-A-9, FR-B, US-9
- `ADR-0005` (declined, then this split taken; corrections 1 and 3 stand), `ADR-0004` (the provenance
  precedent), `ADR-0003` R3 (the reference-row rule), `ADR-0020` (the 2.0 program), `ADR-0023` (deck
  ownership)
- migrations `2026_09_30_142618`, `2026_09_30_151945`; `DeckSlot::booted()`; `SupportCard::isScenarioLink()`
- `tests/Feature/StoreSupportCardsTest.php`, `tests/Feature/SupportCardTest.php`,
  `tests/Feature/SupportDeckBuilderTest.php`, `tests/Feature/ApiV1ValidationEnvelopeTest.php`
- `docs/UMAMUSUME_REFERENCE.md` §1.4, §1.4.1, §1.4.2, §1.4.3, §1.4.4, §1.4.7, §1.4.8; `config/scenarios.php`
  (`scenario_links`)
- `docs/framework-ui-ux/design-framework-index-2026-10-21.md` (the re-check table); rules NRM-3, NRM-4, H5,
  GT-2

## Ledger

- 2026-10-22 · **framework check against `docs/framework-ui-ux/` — the deck's one-copy rule is expressed
  by a message after the fact, not by the control.** The re-check table asks a change that adds or moves
  a control against **Hick** (the option count) and, where a rule the client enforces is concerned,
  **NRM-3** ("a rule the system enforces is expressed by the control, not by a message after it") with
  **H5** (prevent the error rather than report it). The framework's Norman file names this exact case:
  *"the client forbids two copies of one card in a deck; the deck picker should express that by not
  offering the same card twice, not by validating after the fact."* **What the check found is that the
  deck decision above owns the picker's offered set and does not decide the constraint's expression
  point.** `DeckSelectController::picker()` offers the Global releases **plus any card the draft already
  holds**, and does not narrow the set by the cards sitting in the *other* slots; the duplicate is then
  refused on write by `StoreDeckRequest`, which adds "The same card cannot be equipped twice. Break the
  duplicate instead." to the `deck` error bag. So the Trainer can pick a card the deck already carries,
  press Equip, and read why afterwards. **The refusal itself is right** — the client forbids the
  duplicate, and the write is where the whole deck is known — and the decision above stands as written.
  What is owed is a ruling on whether the picker's set is narrowed, and the framework's answer is
  already recorded: an unavailable action should not be offered at all (**NRM-2**, **NRM-3**). The other
  rules this decision touches pass: the role belongs to the slot and the label follows it (**NRM-4**,
  mappings), and the empty panel names the absence instead of drawing six empty frames (**GT-2**).
- 2026-10-01 · **correction against the source data and the shipped schema** — the paragraph on `char_id`
  first cited `characters.json` and its 163 records. That file is not the trainee feed and it does hold a
  block of 9000-series ids; the measurement was taken against an `id` key the file does not have, so every row
  read back as zero and the range printed as empty. The conclusion is unchanged, the evidence named was
  wrong.
- 2026-10-03 · **erratum — the 23 above is the staff-block count and nothing more.** It was later read as the
  answer to a different question. The support-card dispatch asked how many cards resolve to no trainee through
  `umamusume.external_ref = 'gametora:char:{char_id}'`, and that number has never been 23. Measured against
  the working database on 2026-10-03, after the card source was re-run from its committed body: 322 of the 559
  resolve and 237 do not, being the 23 staff cards plus those whose character the feed documents but this
  catalogue does not hold a row for. The 23 stands as written; the dispatch premise that reached three code
  comments as "41 of the 559 resolve to nobody" was wrong in its figure and wrong in its source, since it
  cited this bullet for a claim this bullet does not make. All three comments now name the two reasons and
  print no count, because the second of them moves with the roster.
- 2026-10-09 · **erratum — `ADR-0023` adds a nullable `deck_slots.ownership` holding `OWNED`, `RENTED` or
  nothing, and both deck writers record it.** The Decision table row above that reads "the deck records card
  *identity*, not ownership state" stood from 2026-09-30 to 2026-10-09 and no longer holds as written. What
  that sentence was written to protect still holds and is unchanged: the **collection** stays cut — no
  `UserSupportCard`, no level, no limit breaks, no perk level, no "which cards the player owns" — and the new
  flag is per run and per slot, never derived from the player's inventory. The line separated collection
  tracking from the run's own deck record, and it still does. The cost of leaving it out was measured, not
  assumed: the Rice Shower / Unity Cup UX walk (2026-10-09) recorded a borrowed friend card in slot six
  through the wizard and read it back as `Owned: true, Rented: false` the moment the run existed, because
  nothing wrote the flag down.
