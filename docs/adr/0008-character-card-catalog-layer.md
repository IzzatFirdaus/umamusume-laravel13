# ADR-0008: The character-card catalog layer, and a card reference on the run

Status: **Accepted (owner ruling 2026-09-29).** The owner ruled three questions in session on the roster
request, and `docs/requests/2026-09-29-catalog-roster-and-trainee-selector.md` §2 is the record: build
the card table **and** put a card reference on the run, so the form submits the card and not only the
trainee; populate it through a live `uma:fetch` against the already-declared GameTora host rather than
from the 2026-09-27 gitignored snapshot; cross-check **all 105** Global cards against the two Tier A
witnesses rather than spot-check. This ADR is the record of that ruling; the citations it produces are
`PRD.md` FR-A-6 (new here) and FR-C-1 as amended, and every later commit in the roster slice quotes this
number. **As of this commit the layer is authorized, not built:** no migration, model,
enum or parser exists yet. Tasks 4 to 7 of
`docs/requests/2026-09-29-catalog-roster-and-trainee-selector-plan.md` carry the schema and the pipeline,
and until they land, a reader who queries `character_cards` will find no such table.
Date: 2026-09-29
Deciders: product owner (ruling), Architect (this ADR and the `PRD.md` / `ARCHITECTURE.md` amendments)
Relates to: `ADR-0004` (Tier B reference data promoted with provenance: the closest precedent),
`ADR-0005` (declined, owner ruling R37: support-card entities), `ADR-0003` (Phase 1 schema expansion),
`PRD.md` FR-A-1, FR-A-6, FR-B-3, FR-C-1, US-1, US-2, §6.6, §6.9, §6.11, §6.12,
`docs/requests/2026-09-29-catalog-roster-and-trainee-selector.md` §2 and §3 (erratum E-1, E-4, E-9,
E-11, E-12, E-14, E-15, E-16), `app/Services/DataPipeline/Parsers/GametoraCharacterParser.php`,
`app/Services/DataPipeline/PipelineRunner.php:92` (`MatchCandidate::create`, in the Fuzzy/None branch), `config/uma.php:46-62`, `CONSTRAINTS.md` C-4 with
its verbatim-name exemption at `:38`, `docs/design-research/CONSTRAINTS.md` §5 preamble and D-30,
`docs/scenarios/09-global-race-calendar.md:655` (the Tier B confirmation rule, quoted there from
`docs/SOURCE-OF-TRUTH.md` §5), and `docs/UMAMUSUME_REFERENCE.md` `:66-78` (the source registry that rates
the tiers, including `:72` GameTora B and `:75-77` the tier A witnesses),
`:199` (the export's card counts), §1.3.5 (`:388-394`), §1.4.4 (`:465`), conflict row 46 (`:2032`)

## Context

The catalog models the character, not the collectable card, and the parser says so out loud.
`GametoraCharacterParser.php:15-18` records that the export "holds one entry per costume card", that
several entries share a `char_id`, and that "Costume variants are therefore dropped, not merged."
`:74-79` keeps the card with the earliest JP `release` as the debut form, and `:87-98` derives
`release_status` from whether that debut carries a `release_en`.

No requirement ever asked for the rest of them. `PRD.md` FR-A-1 defines the `Umamusume` record and
stops; FR-A-5 (`ADR-0004`) added the aptitude letters and the scenario caps; nothing in `PRD.md` §3 or
§4 mentions a card. The roster request
(`docs/requests/2026-09-29-catalog-roster-and-trainee-selector.md` §1) asks for every costume card a
trainee has, nested under her on the catalog page, and for the "New training run" selector to submit a
card.

That is a schema object with no requirement behind it, so `AGENTS.md` escalation 2 and D-30 applied
before any migration was written: "A screen that needs a field the schema does not have is a scope
question for the Architect, not a reason to add a column" (`docs/design-research/CONSTRAINTS.md:136`).
The Architect asked; the owner answered. `ADR-0005` is this repo's precedent for that exchange and this
ADR is the same form with the opposite outcome: there the owner declined, here the owner accepted, and
in both cases the ruling is on the record rather than inferred from silence.

## Decision

| Object | Shape | Why this shape |
|---|---|---|
| `character_cards` | one row per costume card that carries a Global release date: `card_id` (the source's own id, unique), `umamusume_id` FK cascade, `title` (the verbatim `[Global]` client string, brackets included), `rarity`, `global_release_date`, `is_debut_form`, `unconfirmed`, timestamps | Keyed by the source's id, not by name, so a re-fetch is idempotent by identity (FR-B-5). `title` is source data: `CONSTRAINTS.md:38` keeps a verbatim client name as data and puts the lore guard on the display path, so this column is never a place to normalize copy |
| `umamusume.external_ref` | nullable, indexed string holding `gametora:char:{char_id}` | The link the parser has emitted since it was written (`GametoraCharacterParser.php:101`) and the schema never kept, so promotion dropped it (erratum E-14). Cards attach through it rather than by re-matching on the name the engine is built not to guess about (FR-B-3) |
| `training_runs.character_card_id` | nullable FK to `character_cards` | The form the run started on, which is the owner's ruling rather than the implementer's inference. `umamusume_id` stays NOT NULL and stays the owner of the run |
| `unconfirmed` | bool, default false | Holds any card the Tier B source stands alone behind: stored flagged and hidden unless asked for, rather than dropped or quietly trusted |

`is_debut_form` is **derived, never copied**: the export has no debut field to read
(`docs/requests/2026-09-29-catalog-roster-and-trainee-selector.md` E-4), so the flag is the earliest JP
`release` among that trainee's cards, which is the same rule `GametoraCharacterParser.php:74-79` already
applies to the trainee's own dates. `rarity` arrives as a `CardRarity` enum with TitleCase cases
(`OneStar`, `TwoStar`, `ThreeStar`), matching the repo's enum convention rather than a DB-level enum,
which `PRD.md` §6.8 keeps out.

Cards arrive as a second declared source with its own parser class, per `AGENTS.md`'s Data Engineer
rule (config entry + one parser + tests against a stored fixture + a robots/rate-limit note), and a
card row bypasses the name-match stage on purpose: a card's identity is its `card_id`, so there is
nothing to cross-reference and no reason to fill the review queue with it.

## Options considered and rejected

- **(a) Card table, run unchanged.** Rejected by the owner. A selector that names a card and a run that
  cannot record which card was chosen would leave the request's second deliverable decorative.
- **(b) No card table: trainee-only selector, catalog shows the debut form as today.** Rejected. It
  drops the nesting the request asks for (one heading per card under each trainee) and re-derives per
  fetch what a table could hold once.
- **(c) The card tree as JSON in one column on `umamusume`.** Rejected: not queryable, not joinable, not
  provenanceable per card, and it hides a second source of truth inside a field. `PRD.md` §6.12 rejects
  a second authoritative store on the same grounds when it is a browser; the argument does not weaken
  for a column.

## Consequences

### The character feed does not become Global-only (measured 2026-09-29)

A trainee's `release_status` still derives from her debut card (`GametoraCharacterParser.php:87-98`),
and the parser still emits **one record per `char_id` across the whole export**. Measured on the export
body on 2026-09-29: 268 rows, of which **105** carry a `release_en` date and 163 carry none; those 105
belong to **68** distinct `char_id`; the parser over the same body yields **135 records, 68
`GlobalReleased` and 67 `JapanOnly`**. The same 268-and-105 counts, with a latest Global date of
2026-09-24, are recorded against the 2026-09-27 fetch in `docs/UMAMUSUME_REFERENCE.md:199`.

Keeping all 135 is what `PRD.md` US-2 (P0) requires: its story text says new JP releases "appear with a
'JapanOnly' or 'GlobalAnnounced' flag instead of silently missing" (the PRD sets those two flags in
double quotes; single quotes here are nesting, not rewording). A Global-only guard inside the parser
would delete exactly the rows that story exists to keep. So the scopes differ on purpose: the
card table is Global-only, the character feed is not, and **the promotion verdict is where the two
scopes meet.**

### The roster filter is safe as measured, and a later roster move can falsify it

Also measured 2026-09-29, over all 68 Global trainees: the JP-earliest card, the Global-earliest card
and the debut the parser picks are **the same card** in every case, with **no date ties**, and **no
trainee is tagged `JapanOnly` while owning a Global card**. Those two facts are what make
`where('release_status', GlobalReleased)->has('cards')` a safe roster filter today: the status predicate
and the card predicate select the same 68 rows, so either one can carry the roster without the other
silently disagreeing. Every existing card owns a trainee in that set, and the derivation has no ambiguity
to hide in.

This is a dated observation, recorded with the day it was true in keeping with the policy this repo
applies to measurements: "Every line below was measured against the dataset on 2026-09-29, not argued"
(`docs/requests/2026-09-29-catalog-roster-and-trainee-selector.md` §3), the same convention that makes
Task 8's cross-check file a dated read. It is falsifiable, and the falsifier is named: a Global trainee
whose debut stops being her earliest card, or one tagged `JapanOnly` while owning a Global card, breaks
the equivalence. If that happens the discrepancy is a data-quality finding for the review queue, not a
reason to widen the query, and erratum E-15's counts need re-recording here and in the slice report.

### What a live fetch does and does not create

Per erratum E-9, a live fetch creates **zero** trainee rows on its own:
`CrossReferenceMatcher::match()` returns `None` for any name not already stored
(`app/Services/DataPipeline/CrossReferenceMatcher.php:28-52`) and `PipelineRunner.php:92` routes
`None` to `match_candidates`. `PRD.md` FR-B-3 is deliberate about it. Erratum E-15 records what that
means in numbers for this export: 135 parsed records queue **133** candidates, not the 66 the original
brief assumed. The roster therefore leaves the review queue through the existing
`app/Actions/ResolveMatchCandidate.php`, and the `JapanOnly` records stay in `/review` awaiting one. This
ADR authorizes no bulk-promote path and no new button.

### Provenance write and cross-check verdict are separate writes

`is_manual` immutability (FR-B-4) and the Floor rule "no fact stored without provenance" are unchanged.
Card rows carry no inline `source_url` / `fetched_at`, and that is a different choice from
`ADR-0004:49-51` rather than the same one: `scenarios` carries those two columns inline because
`data_sources` is `umamusume_id`-scoped (`ARCHITECTURE.md:102-106`) and generalising provenance was
rejected there as scope creep, while every card **has** an owning trainee to hang its provenance on. So
a card's provenance is the `data_sources` row written for its trainee under the card source key, and
Task 11's detail-page source line prints
itself out of exactly that row (`$umamusume->dataSources->first()->source_key`, plan Task 11).
`unconfirmed` is a human cross-check verdict, not a source fact: it stays out of the upsert's update set
so a re-fetch cannot clear it, Task 8's file owns it, and Task 9 writes it (plan Tasks 7 to 9).

### FR-A-6's last clause, read narrowly

FR-A-6 says "a trainee with no Global card does not appear in the catalog". Read as a statement about
the roster this request asks for (the Global-filtered tree), it is what the ruling means and what the
measured counts support. Read as a statement about `umamusume`, it would be false: a `JapanOnly` row
still exists there, and US-2 (P0) requires it to exist and to be flagged rather than missing. The plan's
catalog keeps `status=all` as the explicit way back
(`docs/requests/2026-09-29-catalog-roster-and-trainee-selector-plan.md`, Task 10), which lists a
trainee with no cards. Flagged for the owner rather than rewritten inside a citation string.

## What is deliberately not stored

- **Per-card JP release dates.** They are read to derive `is_debut_form` and then dropped; nothing in the
  request displays them, the catalog's business is what reached `[Global]`, and `PRD.md` §6.6 keeps the
  event and banner calendar out of Phase 1.
- **The stat arrays.** `base_stats`, `four_star_stats`, `five_star_stats` and `stat_bonus` are rows of
  the same export (`docs/UMAMUSUME_REFERENCE.md` §1.3.5, `:388-394`), and they stay out: `PRD.md` §6.11
  forbids a prediction or simulation engine to feed on them, and `ADR-0002` still owns the cap-bound
  question they would reopen.
- **Aptitudes at card grain.** `GametoraCharacterParser.php:102` reads the `aptitude` array at trainee
  grain under FR-A-5; a per-card copy would be a second, disagreeing answer to the same question.
- **Card skill fields** (the unique-skill ids and the `hint_skills` / `hint_others` blocks recorded at
  `docs/UMAMUSUME_REFERENCE.md:465`). `PRD.md` FR-D is a flat skill catalog and US-4 attaches skills to
  runs; nothing asks for skills attached to a costume card.
- **The 163 cards with no `release_en`.** Global-only scope, ruled in the request and its §3.

`PRD.md` §6 non-goals 6, 9 and 11 are cited above as **still binding**. Nothing in this ADR relaxes
them.

## Not the support-card database

This is the **costume-card** table: the outfits a trainee can appear in, the objects the roster request
names. It is not, and does not authorize, the **support-card** database.

`PRD.md` §6.9 ("No support-card database in Phase 1") stands whole, and `ADR-0005` is **DECLINED** by
owner ruling R37 (2026-09-28). `support_cards`, `user_support_cards` and `deck_slots` remain forbidden:
no table, no model, no factory, no route, and no slice may cite this ADR as permission for any of them.
`ARCHITECTURE.md` §3's subsection "Support-card entities: proposed, not built" (`:159-162` at this
commit) warns that an entity copied out of `ADR-0005` into a migration would silently reverse §6.9, and
that warning is exactly why this section exists rather than a footnote: the two kinds of card are easy to
confuse, and the same publisher ships both datasets (`docs/UMAMUSUME_REFERENCE.md:199` names
`character-cards.json` and `support-cards.json` side by side). A costume card names what a trainee wears;
a support card is one of six deck entries that change what a run produces. `training_runs.character_card_id`
is a reference to the first kind. It is not a deck slot, and it does not narrow the gap §6.9 keeps.

## Provenance: Tier B data, Tier A witness

GameTora is **Tier B** (`docs/UMAMUSUME_REFERENCE.md:72`). The rule this repo applies to it, quoted
from the tracked file that cites it (`docs/scenarios/09-global-race-calendar.md:655`): "a Tier B dataset
needs A- or S-tier confirmation before a claim becomes app data", per `docs/SOURCE-OF-TRUTH.md` §5. The
two Tier A witnesses are `umamusu.wiki` and Game8, rated A in the same source registry
(`docs/UMAMUSUME_REFERENCE.md:75-77`). Tier A is not infallible: conflict row 46
(`docs/UMAMUSUME_REFERENCE.md:2032`) records the English `umamusu.wiki` Grand Masters table shifting the
three goddess bonuses across characters against GameWith and Game8 JP, which agree with each other. Task
8 therefore numbers its tie-breaks before it collects them: on a title the `[Global]` client string wins
and the other spelling is recorded as differing; on a date, a rarity or any other disagreement there is
no preference-based pick, and the card is flagged rather than quietly aligned
(`docs/requests/2026-09-29-catalog-roster-and-trainee-selector-plan.md`, Task 8 Step 4).

The owner ruled the confirmation **deep**: all 105 Global cards, not a spot-check. Task 8 produces
`docs/data/2026-09-29-global-roster-crosscheck.md` with one verdict per `card_id`, the two URLs and the
read date, stated as a dated observation, and Task 9 writes `single-source` and `conflict` into
`unconfirmed = true`. The state this ruling sets is therefore explicit: a card the Tier B source stands
alone behind is **stored, flagged and hidden by default**, never dropped and never quietly trusted.
`unconfirmed` defaults to false in the migration, so a card carries a flag only once a verdict has been
written for it, and Task 8's file counts a fourth class beside the three verdicts, `unwitnessed`, so
"nobody has looked at this one" stays a visible state rather than reading as confirmed
(`docs/requests/2026-09-29-catalog-roster-and-trainee-selector-plan.md`, Tasks 8 and 9).

Two provenance facts are worth naming so a later reader does not re-open them. First, the source is
already declared and approved: `config/uma.php:46-62` records the owner's 2026-09-27 approval of
`gametora-characters` "with conservative politeness defaults rather than a live robots.txt check". The
2026-09-29 ruling to fetch live answers `AGENTS.md` escalation 5 for a re-fetch of that same declared
host; it is not an approval of a new host, and OQ-2 stays open for every other source question. Second,
erratum E-11 records that the 2026-09-27 export body sits at the gitignored
`research-scratch/data/json/character-cards.json` and nothing under `app/` reads it, which is why the
figures above are cited from dated measurements rather than re-runnable from a tracked file here.

One citation here is knowingly second-hand: `docs/SOURCE-OF-TRUTH.md` is where §5 lives, and the plan
quotes it at `§5:152`, but that file is **not tracked in this branch tree**, so neither its line numbers
nor its wording can be checked from here. The rule is quoted from `docs/scenarios/09-global-race-calendar.md:655`,
which cites §5 by name, and from the source registry at `docs/UMAMUSUME_REFERENCE.md:72-77`. Whoever owns
`SOURCE-OF-TRUTH.md` should confirm the wording, and this ADR's §5 reference is marked as a quotation
rather than a verified read.

## Verification

This ADR adds no behaviour, so it ships no test. What proves it:

- `php artisan test --compact tests/Feature/LoreGateParityTest.php tests/Feature/RenderedCopyHygieneTest.php`
  and `make lore` over the tracked tree, both reported in the task record.
- The citation greps of the plan's Task 3 Step 5: `A-6` and `ADR-0008` resolve in `PRD.md`,
  `ADR-0008` resolves in `docs/adr/`, `ARCHITECTURE.md` and `ARCHITECTURE-ESSENTIALS.md`,
  `character_cards` resolves in the design docs and in D-30, and exactly one file claims number 0008.
- The schema and pipeline tests arrive with the code they cover: `CharacterCardSchemaTest` (Task 4-5),
  `CharacterCardParserTest` (Task 6), `CharacterCardFetchTest` (Task 7), `CatalogRosterTreeTest`
  (Task 10), `TraineeSelectorTest` (Task 12), and Task 9's live pass re-measures the counts recorded
  above and reports where they moved.

---

## Citation re-derivation note - 2026-09-29, added on merge

Every `file:NN` in this ADR was measured against branch base `b387e07`. Twenty commits
landed on `master` before this branch merged them, and `e7b78a4` inserted 31 lines into
`PipelineRunner.php`, so the two citations above were corrected from `:61-74` to `:92` and
from `config/uma.php:44-61` to `:46-62`.

The rest were re-read rather than assumed, and three that looked wrong were right:
`GametoraCharacterParser.php:101` is the `external_ref` line and `:102` is the `aptitude`
line, so both appear correctly in different paragraphs; `:15-18` still holds the
costume-variants sentence at `:18`; and `CrossReferenceMatcher.php:28-52` is unmoved, that
file being untouched by the merge. `TrainingRun.php` moved on trunk while `#[Fillable]`
stayed at `:50` - which is the reason this pass re-reads line by line instead of
blanket-shifting every number by the insertion count.

Line numbers in this document are hints to the reader, not load-bearing assertions. Where a
claim depends on a location, it names the symbol as well as the line.
