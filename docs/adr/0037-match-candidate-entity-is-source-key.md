# ADR-0037: The match-candidate entity is its `source_key`, and the queue resolves by entity kind

<!-- ADR-CONTRACT
  decision: accepted (owner delegation 2026-11-18, "proceed with every owner gated tasks") — `match_candidates.source_key` is the record's entity discriminator; the existing review surfaces are the race queue's door; a resolve path for race candidates is future work gated on the promotion semantics it needs
  code: measured — 304/304 rows are `gametora-races`; `Review/Index` lists them with `source_key` printed; no second entity co-inhabits the table today
  superseded-by: —
  amended-by: —
  open: what "resolving" a race candidate means (create a `race_catalog_slots` row? attach an existing one?) — the promotion semantics the resolve screen needs are not designed yet, and this ADR does not design them
-->

Status: **accepted** (owner delegation, 2026-11-18). The proposal
(`docs/proposals/consolidated-ui-ux-and-remaining-work-2026-11-18.md` Phase E) framed D-8 as "add an entity
discriminator to `match_candidates` (schema plus ADR), then a race queue". Measurement changed both halves:
a discriminator column already exists in effect, and a race queue already exists in effect.

## Context

`match_candidates` holds every fetched record the matcher could not confidently place. The D-8 framing
assumed the table mixes entity kinds with no way to tell them apart, and that race candidates therefore
could not be reviewed separately.

## Measurement (2026-11-18, dev DB)

- `select source_key, count(*) from match_candidates group by source_key` → `[('gametora-races', 304)]`.
  **Every row is a race candidate.** No second entity co-inhabits the table.
- `suggested_umamusume_id` is null on all 304 — as expected for races.
- `Review/Index` (`app/Http/Controllers/ReviewController.php::index`) already lists Pending rows with
  `source_key` and `proposed_name` printed, paginated 25 — the race queue's door is the existing screen.
- Each row's `payload` carries the full `gametora-races` record (id, name_en, course, direction, season,
  entries, factor effects, track bias), so promotion never re-fetches — the contract the model's docblock
  already states.

## Decision

**`source_key` is the entity discriminator.** `config/uma.php`'s fetch keys are one-to-one with the parsed
entity kind (`gametora-races` → race, `gametora-characters` → trainee, `gametora-skills` → skill), and the
key is `NOT NULL` on every row. A dedicated `entity` column would duplicate it with a second value that can
never disagree today and would need its own backfill.

**No schema change is made by this ADR.** If a future source ever publishes two entity kinds under one
`source_key` — none does today — the right change at that point is to split the source key, not to add a
parallel column, because the key is what the fetch pipeline, the snapshot store and the reparse command
already key on.

**The review surfaces are the race queue.** Race candidates render in `Review/Index` and `Review/Detail`
today. What is *not* built is a resolve path with race semantics, and that gap is a design question, not a
missing column: "resolving" a race candidate means deciding whether the payload becomes a
`race_catalog_slots` row, attaches to an existing slot, or is discarded — each with different catalogue
consequences the current `ResolveMatchCandidate` (which writes trainee aliases) cannot express.

## What this does not license

- **No resolve-path work without its own design.** The race promotion semantics need a decision about the
  catalogue write they perform; this ADR deliberately does not make it.
- **No new column.** `entity`/`kind` columns are refused while `source_key` partitions the table perfectly.

## Links

- `docs/proposals/consolidated-ui-ux-and-remaining-work-2026-11-18.md` Phase E (D-8 row)
- `app/Models/MatchCandidate.php`; `app/Http/Controllers/ReviewController.php`
- `config/uma.php` fetch keys; `ADR-0010` (the payload shape)

## Ledger

- 2026-11-18 · **drafted and accepted under delegation** — the owner's instruction to proceed with every
  owner-gated task is the recorded ruling; the measurement that changed the decision's shape is quoted above.
