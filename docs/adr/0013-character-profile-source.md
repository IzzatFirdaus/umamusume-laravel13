# ADR-0013: Character profile source - basic information at character grain

Status: **Withdrawn — superseded by ADR-0012 Decision 4 and the branch's 2026-09-30 probe record.**

This ADR was drafted on `feat/umamusume-detail-page` for the same `umamusume_profiles` table that
`master` had already authorized and landed under `ADR-0012` **Decision 4**. The 2026-09-30 port resolves
the collision in `ADR-0012`'s favour, and this paragraph is where the pieces go: its **field inventory,
coverage table and `rl` allowlist obligation move into `ADR-0012` Decision 4 and
`docs/data/2026-09-30-characters-source-probe.md`**; its **schema decision is moot** because master's
`2026_09_29_182820_create_umamusume_profiles_table.php` landed first (this branch's
`2026_09_30_120000_…` migration and its `height_cm`/`bust_cm`/`waist_cm`/`hip_cm` columns are not what
shipped — master stores `height`/`three_sizes_b`/`_h`/`_w`); and its **drafted `FR-A-7` text has landed in
`PRD.md`** as `A-7`, corrected to the `va_en` romanisation reading and the `n = 105` counts. The
`Context`, `Decision` and `Consequences` below are kept **as the record of a parallel attempt** — read
against the probe for the corrected numbers and against `ADR-0012` for the authority. A withdrawn ADR
stays as a trail; it is not deleted. One point of this branch's record stands: it read `va_en` as the
romanisation of `va_ja`, not a dub cast, and `master` adopted that reading; it was the authority question,
not the data, that is withdrawn.

<sub>Original status, superseded on 2026-09-30: **Accepted (owner rulings 2026-09-30).** The six fields
were approved by the detail-page brief's §A ("the user's clarification of the six fields is the approval
for it"); the sibling table, the three nullable birth columns, and the refusal to invent a Release Date
column were the owner's rulings from that session. **Built, not yet rendered:** as of `855bcf4` on
`feat/umamusume-detail-page` the `umamusume_profiles` table, its parser, store action, config entry and
fifth pipeline route existed and were covered by `tests/Feature/CharacterProfileTest.php`;
`resources/views/catalog/show.blade.php` rendered none of it — that was §B and §D of the same slice.</sub>

**This ADR is in conflict with master and does not supersede it.** `555b0cb` on `master` landed a parallel
implementation of the same feature — same table name, same `UmamusumeProfile` / `StoreCharacterProfiles` /
`GametoraCharacterProfileParser` class names, its own migration
(`2026_09_29_182820_create_umamusume_profiles_table.php`), a factory, four test files, and the rendered
profile block — recorded under `ADR-0012` Decision 4 rather than a new ADR. Neither branch has been merged
into the other. Two migrations create one table and two definitions exist for each of three classes, so
this document's decisions are **provisional against that work and the collision is an owner call**, not
something to resolve by whichever branch merges second. Where the two disagree on the data, see the
`va_en` note in Consequences: this side's reading is the one the values support.

Date: 2026-09-30
Deciders: product owner (the field set, the table choice, the birth-column shape), Architect (this ADR)
Relates to: `PRD.md` FR-A-1 (the trainee record, which already carries `name_ja` and both debut dates),
FR-A-6 and `ADR-0008` (the card layer whose store action this copies), `US-1` (the detail page's shape),
`ADR-0003` Amendment R3 (the reference-row provenance rule), `ADR-0004` (aptitude at this same grain),
`ADR-0012` Decision 2 (images, which this narrows with a measurement), `CONSTRAINTS.md` C-4, and the
dated measurements in `docs/data/2026-09-30-characters-source-probe.md`.

## Context

A Trainer's detail page shows a name, a status and a list of costume cards. Five things a Trainer asks
about the character herself are nowhere in the database: voice actor, birthday, height, and three sizes.
The sixth item on the user's list, Release date, **is** in the database and does not need this source.

Everything below was measured on 2026-09-30 against `characters.c6676539.json` (163 rows, one per
character, keyed by `char_id`) before a column was drawn. The probe is recorded in
`docs/data/2026-09-30-characters-source-probe.md`, including the two curl calls and the fact that no
snapshot, database row or config entry was touched to get it.

**Absence in this dataset is the key not being present.** Across all 163 rows no **top-level** value is
ever `null` and none is an empty string; the one nested exception is `rl.death`, null on 17 rows, and `rl`
is a key this app refuses. So the parser's absence test reads `array_key_exists`, and "the source never
sends nulls" is a statement about the projected keys rather than about the file. Coverage, as rows where
the key is present:

| field | key | present | absent |
|---|---|---|---|
| Japanese name | `jp_name` | 163 | 0 |
| Voice actor (JP) | `va_ja` | 163 | 0 |
| Voice actor (romanised) | `va_en` | 137 | 26 |
| Birthday day / month | `birth_day` / `birth_month` | 163 / 163 | 0 / 0 |
| Birth year | `birth_year` | 146 | **17** |
| Height | `height` | 163 | 0 |
| Three sizes | `three_sizes` (`{b,w,h}`) | 127 | **36** |

The asymmetry in the last four rows is the design driver: **a day and a month exist for every character,
and a year for 146 of them.** A composite `birth` date would need a representation for "month and day
known, year unknown" that the source does not have, so it is ruled out and three columns take its place.

**Two things the brief assumed that the data corrects.** There is no `birth` key (it is three keys, and
the brief's "17" belongs to `birth_year` alone), and there is no voice-actor field but four (`va_ja`,
`va_en`, `va_ko`, `va_zh_tw`) with the JP name complete where the romanised one is missing on 26
characters. The brief's coverage figures themselves — 36, 26, 17 — reproduce exactly.

**This is a scope addition, and it is named as one.** `FR-A-1` through `A-6` and `US-1` require a
trainee's names, match key, release status, both debut dates, provenance and the ten aptitude letters.
None of them requires a voice actor, a birthday, a height or a body measurement. The owner's brief
approves the five; the PRD does not yet record that. The `A-7` text is drafted at the end of this
document so the PRD edit is a paste rather than a rewrite, and **it is deliberately not applied here**:
`PRD.md` is held dirty by a concurrent session in the main tree, and this branch has a recorded incident
about two writers sharing one file (`PLAN.md:24-25`, and the register commit that carried a peer's KI
lines). Scope changes are proposed to the human, never adopted silently — including when the human has
already agreed to the scope in another document.

**`ADR-0012` Decision 2's deferral of images is now closed by elimination rather than by search.**
`meta/char_profile_art.fc64c1d0.json` was fetched and measured: 170 rows keyed by character **slug**, each
`{images, url_name}`, where `images` is a flat boolean map using exactly five keys across the whole file —
`uniform` (151 rows), `racing` (144), `starting-future` (129), `concept` (123), `work` (19). No paths, no
file names, no `card_id`, nothing per-form. It states which portrait kinds a character page has and not
where any bytes live, so there is no per-form asset source to defer *from*: the brief's §C stop condition
("if you discover a per-form image source, stop and report") is not triggered, and Decision 2 stands on a
measurement.

**The C-4 exposure, stated before the code rather than after.** `sex` and `rl` are present on all 163
rows and `race` on 128. `race` is literally `"uma"`. `rl` is the dataset's record for the real-world
namesake each character is drawn from — `active` years, a `country`, and a **`death`** date. That is the
framing C-4 exists to keep out of this app, sitting on every row of the source this ADR adds. It is
reachable by the single most natural implementation available, which is to iterate the row. So the
allowlist is not a style preference in this decision; it is enforced at two ends and tested at both, below.

## Decision

**1. A sibling table `umamusume_profiles`, keyed one-to-one to `umamusume.id`.**

| column | type | null | from |
|---|---|---|---|
| `umamusume_id` | `foreignId` → `umamusume`, `cascadeOnDelete`, unique | no | the join |
| `va_ja` | `string`, nullable | yes | `va_ja` |
| `va_en` | `string`, nullable | yes | `va_en` |
| `birth_day` | `unsignedTinyInteger`, nullable | yes | `birth_day` |
| `birth_month` | `unsignedTinyInteger`, nullable | yes | `birth_month` |
| `birth_year` | `unsignedSmallInteger`, nullable | yes | `birth_year` |
| `height_cm` | `unsignedSmallInteger`, nullable | yes | `height` |
| `bust_cm` / `waist_cm` / `hip_cm` | `unsignedTinyInteger`, nullable | yes | `three_sizes.b` / `.w` / `.h` |
| `source_url`, `snapshot_path`, `fetched_at`, `source_timezone`, `is_manual` | per R3 | — | provenance |

`ADR-0003` Amendment R3 is the reason it is a sibling and not five columns on `umamusume`: a reference row
carries its own provenance, and `umamusume` carries none of those columns. Putting profile fields there
would attach a second source's provenance to a row whose provenance belongs to the character fetch, which
is the exact ambiguity the card layer spent a slice removing.

Three birth columns, not a composite, for the asymmetry in Context. Every value column is nullable,
because absence is the normal case in this dataset and not an error state.

The unit is in the column name (`height_cm`, `bust_cm`) because the source publishes bare integers —
`height: 158` — and centimetres is an **inference from magnitude, not a fact the source states**. The same
applies one level down: `three_sizes` is `{"b":81,"h":81,"w":56}`, and reading `b`/`w`/`h` as
bust/waist/hip is an inference from the B/W/H order the field name implies and from `h` not being height,
which the dataset carries separately. Both inferences are recorded here rather than in code comments so a
later reader can overturn either with evidence.

**2. No Release Date column on this table, and none added to it later.** The field the user listed as
"Release date" is `umamusume.global_debut_date`, already stored since `ADR-0004`/`FR-A-1` and written by
`GametoraCharacterParser`. The profile block spans two sources and the view renders that column from the
trainee. This is named as a decision because the natural move for an implementer handed "six fields from
the characters source" is to look for the sixth in the new table and invent it when it is not there.

**3. The field list is an allowlist, enforced at both ends, tested at both ends.**
`GametoraCharacterProfileParser` projects named keys and returns a fixed record shape;
`StoreCharacterProfiles` names its columns and never spreads the record, exactly as
`StoreCharacterCards` does. Either guard alone is a single point of failure, and the implementation the
dataset invites defeats an allowlist that exists only in the parser. The test that carries this is a
fixture row that **carries `rl`, `sex` and `race`**, asserting both that the parsed record has no such key
and that no such column exists on the stored row.

**4. A fifth routed contract.** `CharacterProfileSourceParser` joins `RaceCatalogSourceParser`,
`SkillSourceParser` and `CharacterCardSourceParser` as a reference kind that does not go through
`CrossReferenceMatcher` — its grain is a `char_id` that resolves through `umamusume.external_ref`, the
same join `StoreCharacterCards` uses. `PipelineRunner::run()` gains the branch. **The comments that
currently read "four contracts" and "three `is_a()` branches" move in the same commit**, because a merge
already made that file's comments false once on this branch and a stale count in a routing comment is how
the next implementer picks the wrong branch.

**5. One config entry, five keys, hash pinned: `gametora-character-profiles`.** `url`, `parser`,
`delay_ms`, `timeout_s`, `timezone`, mirroring `gametora-character-cards`, with the robots and rate-limit
note in the comment above it and the pinned hash `c6676539` recorded with a dated rotation note. The
manifest resolves through the app's own UA
(`UmamusumeTrainerCompanion/0.2 (personal local tool)`), so no browser UA is needed and none is claimed.

**6. Two fields in the source are deliberately not stored.** `va_link` is an external URL found inside
fetched content; nothing requires it, and storing it turns a data field into an outbound link.
`jp_name_real` exists on three rows and is a real person's pseudonym; it is out of the approved six, and
the smallest surface that satisfies the requirement is the safe one. Both are named here so their absence
reads as a decision and not as an oversight.

## Consequences

- The detail page's profile block reads two tables. The view joins `umamusume` to `umamusume_profiles`
  and pulls the release date from the trainee, so a trainee with no profile row still renders a complete
  block minus the four absent fields.
- **26 characters have no romanised voice actor and 36 have no three sizes.** D-220 applies: the view
  shows `N/A` with the reason, never an empty slot and never a bare `0`. A parser that coalesced absent
  into `0` would turn a coverage gap into a false measurement, which is why absence is a key test.
- A new source means a new fetch surface, and with it the standing rule that a fact without provenance is
  deleted rather than stored. The store action writes the four provenance columns on every row.
- `migrate:fresh --seed` still produces **zero** profile rows, the same way it produces zero costume
  cards. That is the honest state of a fresh install and it is already the shape the catalog handles;
  nothing here changes it.
- **`va_en` is the romanisation of the Japanese voice actor, not an English dub cast.** Master's parser
  docblock reads it as "the English dub cast" and concludes the page should show `va_ja`, "the one that is
  never missing". The values refute that: `va_ja` `和氣あず未` / `va_en` `Azumi Waki` / `va_ko`
  `와키 아즈미` are one person (Waki Azumi) in three scripts, and for the characters whose stage name is
  already romanised — Machico, Lynn — all three fields hold the *identical* string, which no separate
  English cast could produce. So the field with 26 absences is the romanised form of the same credit, and
  a page that shows only `va_ja` renders Japanese script where a Trainer on the Global client expects the
  romanisation. Measured 2026-09-30 against `characters.c6676539.json`; the eight-row comparison is in
  `docs/data/2026-09-30-characters-source-probe.md`.
- The `A-7` text below is owed to `PRD.md`. Until it lands, the PRD understates what the app stores.

## Rejected

- **Columns on `umamusume`.** Violates R3 by giving a second source's provenance nowhere to live.
- **A composite `birth` date column.** The source has day and month for all 163 and a year for 146; one
  column forces an invented representation for the 17.
- **Storing `rl` "for completeness", or a JSON `extra` column.** C-4 is absolute, and a flexible column is
  how a prohibited field arrives without anyone deciding to add it.
- **Fetching the profile fields at view time.** NFR-1: no network on a page render.
- **Adding images now.** `ADR-0012` Decision 2, closed above by measurement rather than by search.

## Proposed `PRD.md` addition (not applied here)

Under `### FR-A: Catalog domain`, after `A-6`:

> - A-7 [ADR-0013]: `UmamusumeProfile` record, one per trainee, storing the character's voice actor in
>   Japanese and romanised, birthday day, month and year, height, and three sizes, each independently
>   nullable because the declared source omits whole keys rather than sending nulls. Provenance is inline
>   on the row per `ADR-0003` Amendment R3. The field list is an allowlist: the source's `rl`, `sex` and
>   `race` keys are never stored, never rendered, and a fixture carrying them proves it. Release date is
>   **not** part of this record; it is `umamusume.global_debut_date` (A-1).
