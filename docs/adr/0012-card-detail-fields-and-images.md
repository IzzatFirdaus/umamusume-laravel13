# ADR-0012: Card detail fields - basic information, images, and objectives

Status: **Accepted (owner ruling 2026-09-29).** The owner ruled on Decision 1 in session: widen
`ADR-0008` to carry a card's basic information. Decisions 2 and 3 are recorded from the same session's
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
the brief asked for a field none of the three decisions names, that field is unaddressed here and this
ADR does not authorize it - `AGENTS.md` escalation 2 applies, and a reader holding the brief should
check the three Decision rows against it before building.

**What the source carries.** The declared GameTora export holds one entry per costume card
(`GametoraCharacterParser.php:15-18`), and the same export's rows carry the trainee's stat arrays,
which `ADR-0008` declined to store. Measured on the 2026-09-29 body
(`character-cards.e9e9ee6d.json`, gitignored, so cited as a dated measurement per erratum E-11's
convention): 268 rows, 107 carrying a `release_en` date, 161 carrying none, spanning 68 distinct
`char_id` for the Global set and 135 records through the parser.

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
