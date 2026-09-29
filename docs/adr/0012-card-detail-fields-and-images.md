# ADR-0012: Card detail fields - stat arrays, images, and objectives

Status: **Accepted (owner ruling 2026-09-29).** The owner ruled on Decision 1 in session: widen
`ADR-0008` to carry a card's stat arrays. Decisions 2 and 3 are recorded from the same session's
measurements. **The layer is authorized, not built:** no migration, parser change, store action or
view for any of the three decisions exists in this tree, and `character_cards` still carries the
twelve columns `ADR-0008` named.

**As of this commit the three decisions below are records, not schema.** A reader who queries
`character_cards` today gets zero rows for the stat arrays, no image column, and no objectives table.
The errata at the end of this document are the load-bearing part of it: the card counts in
`ADR-0008` are wrong by two, and its refusal of the stat arrays is withdrawn by this ruling.

Date: 2026-09-29
Deciders: product owner (Decision 1), Architect (this ADR, and the `ADR-0008` amendment it carries)
Relates to: `ADR-0008` (the card catalog layer this widens), `ADR-0002` (stat bounds, still binding),
`ADR-0011` (the skills reference, already authorized), `ADR-0004` (reference data with provenance),
`ADR-0003` Amendment R3 (the reference-row provenance rule), `PRD.md` FR-A-6, FR-D, US-4, §6.6, §6.11,
`docs/design-research/CONSTRAINTS.md` D-30, `config/uma.php`, and the dated measurements in
`docs/requests/2026-09-29-catalog-roster-report.md`

## Context

The catalog page nests a trainee's costume cards under her (`ADR-0008`) and the run form records which
card a run started on. Neither surface answers the question a Trainer asks next: what does this card
actually give me. Three gaps were measured on 2026-09-29 before this ADR was written, and each is
recorded below as a dated observation rather than an argument, because each one falsifiable a later
export can overturn.

**The brief's own wording is not quoted here.** The card-detail brief asked for a set of fields on the
detail page, and this Context does not reproduce its premises: the brief is not a tracked file in this
repository and its wording was not carried into this ADR's authorship, so quoting it would be
reconstruction rather than citation. What follows is therefore scoped to what the tree can prove. If
the brief asked for a field none of the four decisions names, that field is unaddressed here and this
ADR does not authorize it - `AGENTS.md` escalation 2 applies, and a reader holding the brief should
check the four Decision rows against it before building.

**What the source carries.** The declared GameTora export holds one entry per costume card
(`GametoraCharacterParser.php:15-18`), and the same export's rows carry the trainee's stat arrays,
which `ADR-0008` declined to store. Measured on the 2026-09-29 body
(`character-cards.e9e9ee6d.json`, gitignored, so cited as a dated measurement per erratum E-11's
convention): 268 rows, 107 carrying a `release_en` date, 161 carrying none, spanning 68 distinct
`char_id` for the Global set and 135 records through the parser.

**What the profile source carries, and what no decision here addresses.** The card export is not the
only GameTora dataset a detail page wants. `characters.c6676539` is keyed by `char_id` and carries the
trainee's profile block - the Japanese name, a voice-actor field, `birth`, `height` and `three_sizes` -
which is what the product clarification means by **basic information**, and it is a different thing from
the stat arrays Decision 1 widens. Three things make it unaddressed rather than merely unbuilt: it is
**not a declared source** in `config('uma.sources')`, so nothing in this tree fetches it; its coverage is
partial and must be re-measured rather than trusted (recorded on 2026-09-29 as null on 36 rows for
`three_sizes`, 26 for the voice-actor field and 17 for `birth`); and it is `char_id`-grain, so it joins
the trainee and never the card. **Decision 4 authorizes it, and it is the fourth decision rather than a
part of the first:** the escalation-2 ruling it records is what turned this from an unaddressed source
into a built one. It is named here in the same way Decision 2 names the media source, so that a reader
knows the dataset exists and knows that its authority is an owner ruling on 2026-09-30 with a **partial**
PRD citation - not a functional requirement, and not a field this document had already granted.

**What the images source carries.** The declared media source `character_media.36ab44f6` is keyed by
`char_id`. It carries no `card_id`, so it cannot be joined to a costume card rather than to a trainee,
and it carries no asset base path that resolves to a fetchable location from this tree. A card image
is therefore not merely unbuilt, it is not addressable from the data that exists.

**What the objectives source carries.** `ura-objectives.74f80501` holds 135 rows, one per `char_id`,
and carries no scenario key. An objectives model keyed by scenario cannot be built from this source at
all; a per-character one can, and 135 matches the parser's record count exactly.

## Decision

| # | Object | Shape | Why this shape |
|---|---|---|---|
| 1 | **The card's stat arrays, on `character_cards`** | `base_stats`, `four_star_stats`, `five_star_stats` and `stat_bonus` as json columns alongside the existing twelve, each row carrying its own inline provenance as `ADR-0004` and `ADR-0003` Amendment R3 require of a reference row | **Widens `ADR-0008`**, which declined exactly these fields. Stored as the source's own numbers, displayed as numbers, and read by nothing else: see the use-side constraint below. The owner ruled the widening; this row is the record of it. |
| 2 | **Card images** | **Nothing. No column, no URL, no uploader, no route.** | The source is `char_id`-grain and has no resolvable asset path, so there is no key to join on and no location to fetch from. Recorded as a finding rather than a refusal so a future source with `card_id` grain is a re-decision, not a re-litigation. |
| 3 | **Objectives** | Per-**character**, one row per `char_id`, not per scenario and not per card | The source is `char_id`-grain with no scenario key. A per-scenario model would have to invent the scenario dimension the source does not carry, which is the second-authoritative-store problem `PRD.md` §6.12 rejects. This **revises the premise** the brief carried, and the revision is the decision. |
| 4 | **The trainee profile block, on a sibling `umamusume_profiles` table** | One row per `char_id` carrying `name_ja`, `va_ja`, `va_en`, `birth_year`/`birth_month`/`birth_day`, `height` and the three `three_sizes` parts, with the same inline provenance set the card row carries, plus its own `is_manual` | **Authorized by the product owner on 2026-09-30 under `AGENTS.md` escalation 2**, which is the remedy Erratum 3 below names: storage of these six fields was a fresh decision requiring an owner ruling and a PRD citation, and this row is that record. Declared as source `gametora-character-profiles` in `config('uma.sources')` per **B-1**, routed as a fourth `is_a()` branch in `PipelineRunner` per **B-2**. Sibling table rather than columns on `umamusume` because the source is one document about one trainee. Provenance per **A-4**; stored and read as reference data only, on **A-5**'s precedent, so nothing here computes a run outcome and `PRD.md` §6.11 stays untouched. |

### Decision 4's PRD citation is partial, and the shortfall is recorded rather than filled

`AGENTS.md` requires every new table, column or class to cite a PRD requirement, and "no citation, no
merge." This decision cites **A-1** for the Japanese name, **A-4** for the provenance set, **A-5** as
precedent for the reference-data-only rule, and **B-1**/**B-2** for the declared source and the pipeline
stage. **Those citations cover the shape, the grain and the discipline. They do not cover the content.**

No functional requirement in `PRD.md` names a voice actor, a birthday, a height or a three-size
measurement. `FR-A` A-1 through A-6 describe the trainee, aliases, the index, provenance, aptitudes and
the card, and stop there. **So four of the six authorized fields have no PRD requirement behind them**;
the owner's escalation-2 ruling is the whole of their authority. That is stated here because the failure
mode Erratum 3 was written to prevent is exactly this one: a later reader finding a populated table and a
green test and citing this row as if the PRD had asked for a birthday. It had not. If the PRD is ever
amended to carry a profile requirement, this paragraph is where the citation is upgraded from an owner
ruling to a requirement, and a reader who arrives before that amendment knows which of the six fields
rest on what.

**Upgrade recorded, 2026-09-30.** The amendment has landed: `PRD.md` **A-7** names the four fields
(voice actor, birthday, height, three sizes), cross-references this decision, the view test and the
migration, and states the three grains separately rather than collapsing them. So voice actor,
birthday, height and three sizes now rest on a functional requirement; `name_ja` always rested on
**A-1**; and the two fields the detail view sets beside the profile — `Japanese name` and
`Release date` — are **A-1**'s, not this table's. The paragraph above is deliberately left as
written, because the shortfall it records is the reason the amendment had to be written, and a
reader deciding whether to trust a field needs the reason to still be there.

### The use-side constraint on Decision 1, which is the point of the ruling

`PRD.md` §6.11 forbids a prediction or simulation engine. It is **still binding and this ADR does not
touch it.** Storing the arrays is not the thing §6.11 forbids: what it forbids is those numbers
feeding a model that says what a run will produce. So the fields are read by exactly one path - the
detail page's display - and no service, action or query in this repository may read them for anything
else. The honest cost of that is a real one and is stated here rather than discovered later: a stat
shown on a card is a **source** number, not a run's outcome, and a Trainer comparing the two is
comparing a fixed value against a trained one. `ADR-0002` is likewise untouched - it owns the
`0..2000` validation bound on `TurnEntry`, and nothing here widens or narrows it.

### What Decision 1 does *not* withdraw

`ADR-0008` declined five things. This ruling withdraws **one**. The other four stand as written and
are not re-opened by implication, because a widening is a decision about the fields it names and not a
blanket licence for the paragraph it sits in:

- Per-card JP release dates stay out. Their reason is `PRD.md` §6.6 keeping the event and banner
  calendar out of Phase 1, which is a scope decision, not a judgement about what a card is.
- Aptitudes at card grain stay out - a second, disagreeing answer to a question already answered at
  trainee grain under FR-A-5.
- The card skill fields stay out as **schema on the card**; see below, because Decision 1's sibling
  question was already answered and the answer was yes, elsewhere.
- The cards with no `release_en` stay out. 161 rows, corrected from 163 below.

### Skills were already authorized, and are not re-decided here

The card-detail brief's skills ask needs no decision, because `ADR-0011` already landed it: the source
is declared (`config/uma.php`, key `gametora-skills`), `GametoraSkillsParser` exists, and migration
`2026_09_29_021157_add_reference_fields_to_skills_table` added the reference fields. Three facts about
it that a card-detail reader will otherwise re-derive:

1. The source field is **`cost`**, and the parser maps it to the stored `sp_cost`. Looking for `sp_cost`
   in the export finds nothing. 906 rows carry the source field.
2. Every skill id referenced by a card resolves against `skills`, so the detail page can join rather
   than degrade. The cost asymmetry is a data property, not a join failure: **unique** skills carry no
   cost, while innate, awakening and event skills do.
3. **Descriptions are not stored.** `ADR-0011` declined them, and this ADR does not overturn that. A
   detail page that wants a description must be given one by a later decision or a later source.

## Errata to earlier records, dated 2026-09-29

**Erratum 3 - this ADR's own title and status line misnamed Decision 1, corrected 2026-09-29.** Both
read, verbatim: "Card detail fields - basic information, images, and objectives" and "widen `ADR-0008`
to carry a card's **basic information**". Decision 1 widens nothing of the kind: it withdraws
`ADR-0008`'s refusal of `base_stats`, `four_star_stats`, `five_star_stats` and `stat_bonus`, which are
the stat arrays. *Basic information* is the profile block - Japanese name, voice actor, birthday,
height, three sizes - and it lives in a different dataset that no decision here addresses, as the
Context now says out loud. The two are separate items on the same request list, and collapsing them
let this ADR appear to authorize a source it never measured. **The widening stands exactly as ruled;
only the naming is corrected**, which is why the title and status read "stat arrays" now. `ADR-0008`
was right all along - its §72 already says "carry a card's stat arrays" - so the two records disagreed
and this one was the wrong one. Cost of the error if it had stayed: a builder could cite this ADR as
authority to fetch `characters.c6676539` and add profile columns, on a decision that never mentioned
them and with no PRD citation behind it.

**Erratum 1 - the card counts in `ADR-0008` are wrong by two, and `ADR-0008` contradicts itself about
it.** `ADR-0008`'s section "The character feed does not become Global-only (measured 2026-09-29)"
states, verbatim: "268 rows, of which **105** carry a `release_en` date and 163 carry none". The correct
pair measured from the 2026-09-29 body is **107 and 161**. The same ADR's later Provenance section
already says "all 105 Global cards (107 from the same day's hash rotation)", so it carries both numbers
and neither is reconciled. **107 / 161 is correct**, and 163 - which appears in `ADR-0008` and in
`docs/UMAMUSUME_REFERENCE.md:199` - is a stale count that no re-fetch of the 2026-09-27 body can
reproduce. 268 - 107 = 161 is the arithmetic check; the two counts were never meant to disagree, and a
reader who subtracts 105 from 268 gets 163 and concludes the export is self-consistent.

**Erratum 2 - the withdrawal, quoted verbatim as the convention requires.** `ADR-0008`'s section "What
is deliberately not stored" reads, verbatim:

> - **The stat arrays.** `base_stats`, `four_star_stats`, `five_star_stats` and `stat_bonus` are rows of
>   the same export (`docs/UMAMUSUME_REFERENCE.md` §1.3.5, `:388-394`), and they stay out: `PRD.md` §6.11
>   forbids a prediction or simulation engine to feed on them, and `ADR-0002` still owns the cap-bound
>   question they would reopen.

**That bullet is withdrawn by Decision 1, in whole.** The fields are stored, on the card, with inline
provenance. What survives from the paragraph above is the `PRD.md` §6.11 clause, which survives as a
*constraint on use* rather than a prohibition on storage, and the `ADR-0002` reference, which is
accurate and unchanged: this ADR reopens nothing about the `0..2000` bound. The withdrawal is of the
storage decision, not of the reason the refusal gave, and a reader who deletes the §6.11 clause with
the bullet deletes a constraint that still holds.

## Consequences

- **A second source on the card row.** `character_cards` will carry the trainee's stat arrays as
  source facts and `ADR-0008` explicitly declined them, so the review queue gains a question the
  cross-check does not currently answer: Tier B data standing behind a displayed number. The card's
  `unconfirmed` flag is the existing mechanism and this ADR does not invent a second verdict path.
- **`ADR-0008` is now amended, not replaced.** Its Decision table, its "what is deliberately not
  stored" section and its refusals stand; only the bullet named in Erratum 2 is withdrawn. A reader
  arriving at either ADR gets a consistent answer, and `tests/Feature/CharacterCardSchemaTest.php`,
  which pins the shipped column list, will fail when the four json columns land until its list is
  updated in the same change - which is the guard working, not the guard breaking.
- **The doc-drift guard does not cover this line.** `tests/Feature/DocSchemaDriftTest.php` matches
  three phrasings (`not migrated yet`, `the next migration in the slice`, `lands as YYYY_MM_DD_...`).
  The status line above avoids all three, which means it is correct today and **unguarded against its
  own future correction**: when the schema lands, nothing in that test will object to this ADR
  continuing to say the layer is not built. The repo's own warning applies - coverage here is by
  phrasing, and phrasing is fragile in both directions. The mitigation is the schema test named above.
- **Images cost nothing to defer and would cost a great deal to guess at.** A `card_id` key and a
  resolvable path are both required, and neither exists. Storing a URL that no fetch can satisfy would
  put a permanent, always-broken image in the provenance set, which is the same defect ADR-0008's
  Provenance section was written to prevent.
- **Objectives become simpler and weaker.** Per-character is what the source supports, and it means
  the detail page cannot show a trainee's objectives as they differ by scenario. That is a real loss of
  information, and it is a loss the source causes rather than a choice this ADR prefers.

## Verification

This ADR adds no behaviour, so it ships no test. What proves it:

- `composer lore` over the tracked tree - not `make lore`, which cannot run on this host (KI-4). Both
  this file and the `ADR-0008` amendment are inside the docs sweep.
- The measurements in the Context are reproducible only against a gitignored body, so per erratum
  E-11 they are dated claims. The falsifiers are named: an export whose Global cards do not number
  107 falsifies Erratum 1; a media source gaining a `card_id` key reopens Decision 2; an objectives
  source gaining a scenario key reopens Decision 3.
- The drift guard is run deliberately against this file even though it cannot cover the status line,
  because the other three guarded docs are the ones that can regress:
  `php artisan test --compact tests/Feature/DocSchemaDriftTest.php`.
- The schema test that will first object is
  `php artisan test --compact tests/Feature/CharacterCardSchemaTest.php`, and its failure after
  Decision 1 lands is the expected first signal that the four columns shipped.
