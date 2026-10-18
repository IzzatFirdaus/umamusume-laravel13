# ADR-0008: The character-card catalog layer, and a card reference on the run

<!-- ADR-CONTRACT
  decision: accepted (owner ruling 2026-09-29) — build the costume-card table and put a card reference on the run, populated by a live fetch and cross-checked deep
  code: built — migrations 2026_09_29_120100 (character_cards) and 2026_09_29_120200 (training_runs.character_card_id); tests/Feature/CharacterCardSchemaTest.php, CharacterCardParserTest, CharacterCardFetchTest
  superseded-by: —
  amended-by: 0012 (Decision 1 widens this to carry the stat arrays; Erratum 2 there withdraws the refusal), 0004 (the same inline-provenance pattern)
  open: the sentinel placeholder at character grain — whether dateOrNull() should reject 9999-12-31 is an Architect question against FR-A-6, and no tracked fixture can establish whether the live export carries it
-->

Status: **Accepted** (owner ruling 2026-09-29). The owner ruled three questions in session on the roster
request: build the card table **and** put a card reference on the run; populate it through a live
`uma:fetch` against the already-declared GameTora host rather than from a gitignored snapshot; and
cross-check **all** Global cards against the two Tier A witnesses rather than spot-checking.

## Context

The catalog models the character, not the collectable card, and the parser says so out loud: the export
holds one entry per costume card, several entries share a `char_id`, and costume variants are dropped
rather than merged. `GametoraCharacterParser::debutForms()` keeps the card with the earliest JP release as
the debut form, and `parse()` derives `release_status` from whether that debut carries a `release_en`. The
debut rule is named by symbol because an extraction has already moved it once.

No requirement ever asked for the rest of them. `PRD.md` FR-A-1 defines the `Umamusume` record and stops;
FR-A-5 (`ADR-0004`) added the aptitude letters and the scenario caps; nothing in §3 or §4 mentions a card.
The roster request asks for every costume card a trainee has, nested under her on the catalog page, and
for the "New training run" selector to submit a card.

That is a schema object with no requirement behind it, so `AGENTS.md` escalation 2 and D-30 applied
before any migration was written: "A screen that needs a field the schema does not have is a scope
question for the Architect, not a reason to add a column." The Architect asked; the owner answered.
`ADR-0005` is this repo's precedent for that exchange with the opposite outcome, and in both cases the
ruling is on the record rather than inferred from silence.

## Decision

| Object | Shape | Why this shape |
|---|---|---|
| `character_cards` | one row per costume card that carries a Global release date: `card_id` (the source's own id, unique), `umamusume_id` FK cascade, `title` (the verbatim `[Global]` client string, brackets included), `rarity`, `global_release_date`, `is_debut_form`, `unconfirmed`, then the inline provenance set `source_url`, `snapshot_path`, `fetched_at`, `source_timezone` and the card's own `is_manual`, plus timestamps | Keyed by the source's id, not by name, so a re-fetch is idempotent by identity (FR-B-5). `title` is source data: the lore gate keeps a verbatim client name as data and puts the guard on the display path, so this column is never a place to normalize copy |
| `umamusume.external_ref` | nullable, indexed string holding `gametora:char:{char_id}` | The link the parser has emitted since it was written and the schema never kept, so promotion dropped it. Cards attach through it rather than by re-matching on a name the engine is built not to guess about (FR-B-3) |
| `training_runs.character_card_id` | nullable FK to `character_cards` | The form the run started on, which is the owner's ruling rather than the implementer's inference. `umamusume_id` stays NOT NULL and stays the owner of the run |
| `unconfirmed` | bool, default false | Holds any card the Tier B source stands alone behind: stored flagged and hidden unless asked for, rather than dropped or quietly trusted |

`is_debut_form` is **derived, never copied**: the export has no debut field, so the flag is the earliest JP
`release` among that trainee's cards, the same rule `GametoraCharacterParser::debutForms()` applies to the
trainee's own dates. `rarity` arrives as a `CardRarity` enum with TitleCase cases, matching the repo's
enum convention rather than a DB-level enum, which `PRD.md` §6.8 keeps out.

Cards arrive as a second declared source with its own parser class, and a card row bypasses the
name-match stage on purpose: a card's identity is its `card_id`, so there is nothing to cross-reference and
no reason to fill the review queue with it.

## Options considered and rejected

- **(a) Card table, run unchanged.** Rejected by the owner. A selector that names a card and a run that
  cannot record which card was chosen would leave the request's second deliverable decorative.
- **(b) No card table: trainee-only selector, catalog shows the debut form as today.** Rejected. It drops
  the nesting the request asks for and re-derives per fetch what a table could hold once.
- **(c) The card tree as JSON in one column on `umamusume`.** Rejected: not queryable, not joinable, not
  provenanceable per card, and it hides a second source of truth inside a field. `PRD.md` §6.12 rejects a
  second authoritative store on the same grounds; the argument does not weaken for a column.

## Consequences

### The character feed does not become Global-only

A trainee's `release_status` still derives from her debut card, and the parser still emits one record per
`char_id` across the whole export. Keeping all of them is what `PRD.md` US-2 (P0) requires: new JP releases
must appear with a `JapanOnly` or `GlobalAnnounced` flag instead of silently missing. A Global-only guard
inside the parser would delete exactly the rows that story exists to keep. **The card table is Global-only
and the character feed is not, and the promotion verdict is where the two scopes meet.** The row counts
that supported this are a dated measurement of a gitignored body, cited from the probe record rather than
restated here.

### The sentinel at two grains

`GametoraCharacterParser::UNKNOWN_DATE` (`9999-12-31`) is the export's placeholder for "a card with no JP
date yet", and it sorts such a card last. The two grains read it differently:

- **Card grain — refused.** `GametoraCharacterCardParser::parse()` drops a card whose `release_en` is the
  placeholder, so it never becomes a row. Pinned by `CharacterCardParserTest`'s `[Sentinel]` case.
- **Character grain — accepted.** `dateOrNull()` validates a date's *shape*, and `9999-12-31` has that
  shape, so the debut loop reads the placeholder as a Global date. A debut card carrying only the
  placeholder would therefore yield `release_status: GlobalReleased` and `global_debut_date: '9999-12-31'`
  while owning no card row at all.

**This is an open question for the Architect against `PRD.md` FR-A-6, and it is deliberately not decided
here.** Making `dateOrNull()` reject the placeholder moves `jp_debut_date`, `global_debut_date` and
`release_status` together, on an input no existing test feeds to the character grain, so a refactor
bracket would stay green across a real behaviour change. Whether the live export ever carries the
placeholder **cannot be established from this tree** — the body is gitignored and no tracked fixture
contains it — so the cross-check owns the measurement, and if the placeholder appears there this becomes a
defect report rather than a design choice.

### The roster filter is safe as measured, and a later roster move can falsify it

Over all Global trainees, the JP-earliest card, the Global-earliest card and the debut the parser picks are
**the same card** in every case, with **no date ties**, and **no trainee is tagged `JapanOnly` while owning
a Global card**. Those three facts are what make `where('release_status', GlobalReleased)->has('cards')` a
safe roster filter today: the status predicate and the card predicate select the same rows, so either can
carry the roster without the other silently disagreeing.

This is a dated observation, falsifiable, and the falsifier is named: a Global trainee whose debut stops
being her earliest card, or one tagged `JapanOnly` while owning a Global card, breaks the equivalence. If
that happens the discrepancy is a data-quality finding for the review queue, not a reason to widen the
query.

### What a live fetch does and does not create

A live fetch creates **zero** trainee rows on its own: `CrossReferenceMatcher::match()` returns `None` for
any name not already stored, and `PipelineRunner` routes `None` to `match_candidates`. `PRD.md` FR-B-3 is
deliberate about it. The roster therefore leaves the review queue through
`app/Actions/ResolveMatchCandidate.php`, and the `JapanOnly` records stay in `/review` awaiting a verdict.
This ADR authorizes no bulk-promote path and no new button.

### Provenance is inline on the card; the cross-check verdict is a separate write

`is_manual` immutability (FR-B-4) and the Floor rule "no fact stored without provenance" are unchanged.
A card row carries `source_url`, `snapshot_path`, `fetched_at`, `source_timezone` and its own `is_manual`,
so the model is fillable over the twelve columns the Decision table names plus the two `dd90330` added for
a run's own skill lists. That is `ADR-0003` Amendment R3's standing rule for a reference row, and trunk
already follows it table for table — `scenario_races`, `scenario_slots` and `race_catalog_slots` each carry
all four fields plus their own `is_manual`, and `scenarios` carries `source_url`, `fetched_at` and
`is_manual`, the first two being precisely what `ADR-0004` asked for.

This is the **same** choice `ADR-0004` made for `scenarios`, not a different one: that record rejects a
generic polymorphic provenance table as scope creep and says reference rows carry `source_url` and
`fetched_at` inline instead. A card is exactly such a row, so the precedent points at inline columns.

`data_sources` keeps its existing meaning and is not a card's provenance: it is `umamusume_id`-scoped, the
table behind FR-A-4. Borrowing the parent trainee's row would make "where did this card's release date come
from" a question with no row that answers it, and borrowing her `is_manual` would mean a Trainer correcting
one card's title claimed her whole character.

`unconfirmed` is a human cross-check verdict, not a source fact: it stays out of the store's update set so
a re-fetch cannot clear it.

### FR-A-6's last clause, read narrowly

FR-A-6 says "a trainee with no Global card does not appear in the catalog". Read as a statement about the
roster this request asks for (the Global-filtered tree), it is what the ruling means. Read as a statement
about `umamusume`, it would be false: a `JapanOnly` row still exists there, and US-2 (P0) requires it to
exist and be flagged rather than missing. The plan's catalog keeps `status=all` as the explicit way back,
which lists a trainee with no cards.

## What is deliberately not stored

- **Per-card JP release dates.** They are read to derive `is_debut_form` and then dropped; the catalog's
  business is what reached `[Global]`, and `PRD.md` §6.6 keeps the event and banner calendar out of
  Phase 1.
- **Aptitudes at card grain.** The parser reads the `aptitude` array at trainee grain under FR-A-5; a
  per-card copy would be a second, disagreeing answer to the same question.
- **Card skill fields** (the unique-skill ids and the `hint_skills` / `hint_others` blocks). `PRD.md` FR-D
  is a flat skill catalog and US-4 attaches skills to runs; nothing asks for skills attached to a costume
  card.
- **The cards with no `release_en`.** Global-only scope, ruled in the request and its §3.

`PRD.md` §6 non-goals 6, 9 and 11 are cited above as **still binding**. Nothing in this ADR relaxes them.

## Not the support-card database

This is the **costume-card** table: the outfits a trainee can appear in. It is not, and does not
authorize, the **support-card** database. `PRD.md` §6.9 ("No support-card database in Phase 1") stood whole
when this was written, and `ADR-0005` was **DECLINED** by owner ruling R37. `support_cards`,
`user_support_cards` and `deck_slots` were forbidden: no table, no model, no factory, no route, and no
slice could cite this ADR as permission for any of them. `ARCHITECTURE.md` §3 warned that an entity copied
out of `ADR-0005` into a migration would silently reverse §6.9, and that warning is why this section
exists rather than a footnote: the two kinds of card are easy to confuse, and the same publisher ships
both datasets. A costume card names what a trainee wears; a support card is one of six deck entries that
change what a run produces. `training_runs.character_card_id` is a reference to the first kind, not a deck
slot, and it keeps the gap §6.9 draws.

**`ADR-0014` later lifted §6.9** for the reference half and the run-scoped deck. That is a separate ruling
and does not make this section wrong: the two-kinds warning it records is what stopped the confusion, and
it still holds.

## Provenance: Tier B data, Tier A witness

GameTora is **Tier B**. The rule this repo applies to it: a Tier B dataset needs A- or S-tier confirmation
before a claim becomes app data. The two Tier A witnesses are `umamusu.wiki` and Game8. Tier A is not
infallible — a conflict row records the English `umamusu.wiki` Grand Masters table shifting the three
goddess bonuses across characters against GameWith and Game8 JP, which agree with each other — so the
cross-check numbers its tie-breaks before it collects them: on a title the `[Global]` client string wins
and the other spelling is recorded as differing; on a date, a rarity or any other disagreement there is no
preference-based pick, and the card is flagged rather than quietly aligned.

The owner ruled the confirmation **deep**: every Global card, not a spot-check. The state this ruling sets
is therefore explicit: a card the Tier B source stands alone behind is **stored, flagged and hidden by
default**, never dropped and never quietly trusted. `unconfirmed` defaults to false in the migration, so a
card carries a flag only once a verdict has been written for it, and the cross-check file counts a fourth
class beside the three verdicts, `unwitnessed`, so "nobody has looked at this one" stays a visible state
rather than reading as confirmed.

Two provenance facts are worth naming so a later reader does not re-open them. First, the source is
already declared and approved: `config/uma.php` records the owner's 2026-09-27 approval of
`gametora-characters` "with conservative politeness defaults rather than a live robots.txt check". The
ruling to fetch live answers `AGENTS.md` escalation 5 for a re-fetch of that same declared host; it is not
an approval of a new host. Second, the export body is gitignored and nothing under `app/` reads it, which
is why the figures above are cited from dated measurements rather than re-runnable from a tracked file.

One citation here is knowingly second-hand: `docs/research-scratch/GOVERNANCE.md` is where §5 lives, but that file is
**not tracked**, so neither its line numbers nor its wording can be checked from here. The rule is quoted
from `docs/scenarios/09-global-race-calendar.md`, which cites §5 by name, and from the source registry at
`docs/UMAMUSUME_REFERENCE.md`. Whoever owns `SOURCE-OF-TRUTH.md` should confirm the wording.

## Links

- `PRD.md` FR-A-1, FR-A-6, FR-B-3, FR-B-4, FR-B-5, FR-C-1, US-1, US-2, §6.6, §6.9, §6.11, §6.12
- `ADR-0003` Amendment R3 (the reference-row provenance rule), `ADR-0004` (the inline-provenance
  precedent), `ADR-0005` (declined; the two-kinds warning), `ADR-0012` (its Decision 1 adds the stat
  arrays this ADR first refused), `ADR-0014` (the support-card ruling that lifted §6.9)
- `tests/Feature/CharacterCardSchemaTest.php` (the shipped column list),
  `tests/Feature/CharacterCardParserTest.php`, `tests/Feature/CharacterCardFetchTest.php`,
  `tests/Feature/DocSchemaDriftTest.php`
- `docs/UMAMUSUME_REFERENCE.md` §1.3.5, §1.4.4, the source registry and the conflict rows
- `docs/scenarios/09-global-race-calendar.md` (the Tier B confirmation rule)

## Ledger

- 2026-09-29 · **status corrected the day this ADR was accepted** — the status asserted "no migration,
  model, enum or parser exists yet"; by end of day three of the four objects existed and `character_cards`
  was queryable-but-empty. Kept verbatim because the false claim is the evidence the drift guard was
  written from.
- 2026-09-29 · **second correction** — `2026_09_29_120200_add_character_card_id_to_training_runs_table`
  applied `training_runs.character_card_id` as a nullable FK, with `TrainingRun::characterCard()`, a
  fillable column and a same-trainee `exists` rule on `StoreTrainingRunRequest`. The run half of the
  previous sentence was therefore false as written, while the card parser, its source registration and the
  card store action had indeed not landed.
- 2026-09-29 · **third correction** — the claim that `tests/Feature/CharacterCardSchemaTest.php` is "what
  keeps the Decision table, `ARCHITECTURE.md` §3, the ESSENTIALS digest line and D-30 from drifting"
  described more than that file guards: it holds the `character_cards` column list and nothing else.
  Both halves are guarded now, by `tests/Feature/DocSchemaDriftTest.php`. The superseded paragraphs above
  are **not exempt** from that guard — they pass because their wording falls outside its three-phrase
  vocabulary, not because it steps aside for them. There is no erratum exemption and none can be built
  precisely, and the guard's own test plants a dated correction quoting a guarded phrase beside a landed
  column and asserts it still fires.
- 2026-09-29 · **fourth correction** — one refusal in "What is deliberately not stored" was withdrawn by
  a later owner ruling: `ADR-0012` Decision 1 widens this ADR to carry a card's stat arrays. **The other
  four bullets in that section stand.** What survives from the withdrawn bullet is its `PRD.md` §6.11
  clause, now a constraint on *use* rather than a prohibition on storage, and its `ADR-0002` reference,
  which is unchanged: nothing here reopens the validation bound.
- 2026-09-29 · **card counts corrected by `ADR-0012` Erratum 1** — the "105 carry a `release_en` date and
  163 carry none" figures stood at 105/163 where the export gives 107/161. The counts are a dated
  measurement of a gitignored body and are not restated in this file.
- 2026-09-29 · **erratum: card provenance is inline, and the card owns its `is_manual`.** What this ADR
  said when written: "Card rows carry no inline `source_url` / `fetched_at`, and that is a different
  choice from `ADR-0004` rather than the same one", and a card's provenance was the `data_sources` row
  written for its trainee. **Both are withdrawn.** It read `ADR-0004` backwards — that passage argues
  *for* inline provenance on a reference row — and the pattern the card store now copies arrived with a
  merge after the first draft. The lesson recorded is not "the merge invalidated the ADR" but "the ADR
  misread a rule it had": `ADR-0003` Amendment R3 was already in the tree at the task base.
