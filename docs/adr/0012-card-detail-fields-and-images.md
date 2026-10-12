# ADR-0012: Card detail fields — stat arrays, images, and objectives

<!-- ADR-CONTRACT
  decision: accepted (owner ruling 2026-09-29) — Decision 1 widens ADR-0008 to the stat arrays; Decisions 2 and 3 decline images and settle per-character objectives; Decision 4 authorizes the profile block
  code: built — CharacterCardSchemaTest pins the shipped stat-array columns; migration 2026_09_29_182820 (umamusume_profiles); StoreCharacterProfilesTest; UmamusumeProfileSchemaTest
  superseded-by: —
  amended-by: 0021 (Decision 2's blocker is obsolete: sourced artwork is derived at render time, no column), 0013 (withdrawn; its field inventory and rl/sex/race allowlist obligation are absorbed here)
  open: the run report's §1.2 gap on what composes an entered cap is measured, not resolved (ADR-0025)
-->

Status: **Accepted** (owner ruling 2026-09-29). The owner ruled on Decision 1 in session: widen `ADR-0008`
to carry a card's stat arrays. Decisions 2 and 3 are recorded from the same session's measurements.
**Decision 4 is the authority for the trainee profile block**, authorized by the product owner on
2026-09-30 under `AGENTS.md` escalation 2.

## Decision

| # | Object | Shape | Why this shape |
|---|---|---|---|
| 1 | **The card's stat arrays, on `character_cards`** | `base_stats`, `four_star_stats`, `five_star_stats` and `stat_bonus` as json columns alongside the existing set, each row carrying its own inline provenance as `ADR-0004` and `ADR-0003` R3 require of a reference row | **Widens `ADR-0008`**, which declined exactly these fields. Stored as the source's own numbers, displayed as numbers, and read by nothing else: see the use-side constraint below |
| 2 | **Card images** | **Nothing. No column, no URL, no uploader, no route.** | The media source is `char_id`-grain with no resolvable asset path, so there is no key to join on and no location to fetch from. Recorded as a finding rather than a refusal, so a future source with `card_id` grain is a re-decision, not a re-litigation. **`ADR-0021` supersedes this for sourced artwork display** by deriving the path from the id at render time |
| 3 | **Objectives** | Per-**character**, one row per `char_id`, not per scenario and not per card | The source is `char_id`-grain with no scenario key. A per-scenario model would have to invent the scenario dimension the source does not carry, which is the second-authoritative-store problem `PRD.md` §6.12 rejects |
| 4 | **The trainee profile block, on a sibling `umamusume_profiles` table** | One row per `char_id` carrying `name_ja`, `va_ja`, `va_en`, `birth_year`/`birth_month`/`birth_day`, `height` and the three `three_sizes` parts, with the same inline provenance set the card row carries, plus its own `is_manual` | **Authorized by the product owner on 2026-09-30 under escalation 2.** Sibling table rather than columns on `umamusume` because the source is one document about one trainee. Declared as source `gametora-character-profiles` in `config('uma.sources')` per **B-1**, routed as a fifth contract in `PipelineRunner` per **B-2** |

`ADR-0013` is **withdrawn** and is not the authority for the profile block. Its field inventory, coverage
table, `va_en` romanisation reading and the `rl`/`sex`/`race` allowlist obligation are absorbed here.

## The use-side constraint on Decision 1, which is the point of the ruling

`PRD.md` §6.11 forbids a prediction or simulation engine. It is **still binding and this ADR does not touch
it.** Storing the arrays is not the thing §6.11 forbids; what it forbids is those numbers feeding a model
that says what a run will produce. So the fields are read by exactly one path — the detail page's display —
and no service, action or query in this repository may read them for anything else. The honest cost is
stated: a stat shown on a card is a **source** number, not a run's outcome, and a Trainer comparing the two
is comparing a fixed value against a trained one. `ADR-0002` is likewise untouched: nothing here widens or
narrows the validation bound.

## What Decision 1 does *not* withdraw

`ADR-0008` declined five things. This ruling withdraws **one**. The other four stand as written and are not
re-opened by implication, because a widening is a decision about the fields it names and not a blanket
licence for the paragraph it sits in:

- Per-card JP release dates stay out (`PRD.md` §6.6 scope).
- Aptitudes at card grain stay out — a second, disagreeing answer to a question already answered at trainee
  grain under FR-A-5.
- The card skill fields stay out as **schema on the card**.
- The cards with no `release_en` stay out.

## The profile field list is an allowlist, enforced at both ends

`GametoraCharacterProfileParser` projects named keys and returns a fixed record shape;
`StoreCharacterProfiles` names its columns and never spreads the record, exactly as `StoreCharacterCards`
does. Either guard alone is a single point of failure, and the implementation the dataset invites defeats
an allowlist that exists only in the parser. The test that carries this is a fixture row that **carries
`rl`, `sex` and `race`**, asserting both that the parsed record has no such key and that no such column
exists on the stored row.

## Decision 4's PRD citation is partial, and the shortfall is recorded rather than filled

No functional requirement in `PRD.md` named a voice actor, a birthday, a height or a three-size
measurement. **That shortfall is why the amendment had to be written**, and the upgrade is recorded here
rather than smoothed: `PRD.md` **A-7** now names the four fields, cross-references this decision, the view
test and the migration, and states the three grains separately rather than collapsing them. A reader
deciding whether to trust a field needs to know which of the six rested on an owner ruling and which rests
on a requirement; `name_ja` always rested on **A-1**.

## Skills were already authorized, and are not re-decided here

`ADR-0011` landed the skills reference: the source is declared (`config/uma.php`, key
`gametora-skills`), `GametoraSkillsParser` exists, and the reference fields are on the table. Three facts a
card-detail reader will otherwise re-derive: the source field is **`cost`**, not `sp_cost`; every skill id a
card references resolves against `skills`, so the detail page can join rather than degrade; and
**descriptions are not stored** — `ADR-0011` declined them and this ADR does not overturn that.

## Consequences

- **A second source on the card row.** `character_cards` carries the trainee's stat arrays as source facts
  and `ADR-0008` explicitly declined them, so the review queue gains a question the cross-check does not
  currently answer: Tier B data standing behind a displayed number. The card's `unconfirmed` flag is the
  existing mechanism and this ADR does not invent a second verdict path.
- **`ADR-0008` is amended, not replaced.** Its Decision table, its "what is deliberately not stored"
  section and its refusals stand; only the bullet named in Erratum 2 is withdrawn.
- **`tests/Feature/CharacterCardSchemaTest.php` pins the shipped column list**, which is what keeps the
  Decision table, `ARCHITECTURE.md` §3, the ESSENTIALS digest line and D-30 from drifting away from the
  migration unnoticed. Its failure after Decision 1's columns land is the guard working.
- **The doc-drift guard does not cover this status line.** `tests/Feature/DocSchemaDriftTest.php` matches
  three phrasings, and the status line above avoids all three, so it is correct today and unguarded against
  its own future correction. The mitigation is the schema test named above.
- **Images cost nothing to defer and would cost a great deal to guess at.** Storing a URL that no fetch can
  satisfy would put a permanent, always-broken image in the provenance set.
- **Objectives become simpler and weaker.** Per-character is what the source supports, and it means the
  detail page cannot show a trainee's objectives as they differ by scenario. That is a real loss of
  information, and it is a loss the source causes rather than a choice this ADR prefers.

## Links

- `PRD.md` FR-A-1, FR-A-4, FR-A-6, FR-A-7, FR-B, FR-D, US-4, §6.6, §6.11, §6.12
- `ADR-0008` (the card catalog layer Decision 1 adds the stat arrays to), `ADR-0003` R3 (the provenance
  rule), `ADR-0004` (reference data with provenance), `ADR-0011` (the skills reference), `ADR-0021`
  (sourced artwork), `ADR-0013` (withdrawn)
- `tests/Feature/CharacterCardSchemaTest.php`, `tests/Feature/UmamusumeProfileSchemaTest.php`,
  `tests/Feature/StoreCharacterProfilesTest.php`, `tests/Feature/DocSchemaDriftTest.php`
- `docs/data/2026-09-30-characters-source-probe.md` (the profile coverage measurements)
- `config/uma.php`; `docs/design-research/CONSTRAINTS.md` D-30

## Ledger

- 2026-09-29 · **Erratum 3 — this ADR's own title and status line misnamed Decision 1.** Both read
  "basic information" where Decision 1 withdraws `ADR-0008`'s refusal of the stat arrays. *Basic
  information* is the profile block, and it lives in a different dataset. **The widening stands exactly as
  ruled; only the naming is corrected.** `ADR-0008` was right all along, so the two records disagreed and
  this one was the wrong one. Cost of the error if it had stayed: a builder could cite this ADR as
  authority to fetch `characters.c6676539` and add profile columns, on a decision that never mentioned
  them and with no PRD citation behind it.
- 2026-09-29 · **Erratum 1 — the card counts in `ADR-0008` are wrong by two.** `ADR-0008` stated "105
  carry a `release_en` date and 163 carry none"; the correct pair measured from the same body is **107 and
  161**, and `ADR-0008` already carried both numbers without reconciling them. Kept because the arithmetic
  check (268 − 107 = 161) is what a reader uses to catch the next one.
- 2026-09-29 · **Erratum 2 — the withdrawal, quoted verbatim as the convention requires.** `ADR-0008`'s
  "The stat arrays … they stay out" bullet is withdrawn by Decision 1, in whole. What survives from that
  paragraph is the `PRD.md` §6.11 clause, which survives as a *constraint on use* rather than a prohibition
  on storage, and the `ADR-0002` reference, which is accurate and unchanged.
- 2026-09-30 · **Decision 4's owner ruling recorded.** The product owner authorized the profile block
  under escalation 2, which is the remedy Erratum 3 named. `PRD.md` A-7 landed with it.
- 2026-09-30 · **the port resolved the `ADR-0013` collision in this ADR's favour.** Master's
  `2026_09_29_182820_create_umamusume_profiles_table.php` landed first, so the branch's
  `2026_09_30_120000_…` migration and its `height_cm`/`bust_cm`/`waist_cm`/`hip_cm` columns are not what
  shipped; master stores `height`/`three_sizes_b`/`_h`/`_w`. The branch's `va_en` reading (a romanisation,
  not a dub cast) was adopted. `ADR-0013` is withdrawn and its field inventory, coverage table, `va_en`
  reading and allowlist obligation live here.
- 2026-10-05 · **Erratum 4 — Decision 2's blocker is obsolete, and the falsifier this ADR named did not
  fire.** Decision 2 declined card images because "a `card_id` key and a resolvable path are both required,
  and neither exists", and set the reopening test as "a media source gaining a `card_id` key". A probe found
  the media dump still has no such key, so **the named falsifier has not fired**. What fired instead is the
  other half of the conjunction, by a route this ADR did not consider: the vendor's asset host is
  addressable directly by the 6-digit `card_id`, already present in the characters export as the numeric
  prefix of `url_name`. `ADR-0021` acts on that while keeping the refusal this bullet was protecting
  against: no stored URL that cannot be fetched, which is why the path is derived at render time rather
  than written to a column. What survives untouched: the data claim about the media dump, the reasoning
  about always-broken URLs in a provenance set, and Decision 3's objectives ruling.
