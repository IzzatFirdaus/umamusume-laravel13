# ADR-0030: Add base stats and growth rates to character_cards

<!-- ADR-CONTRACT
  decision: accepted (2026-11-02) — add nullable base_stats and growth_rates json columns to character_cards, populated from GameTora base_stats and stat_bonus fields, stored as keyed objects
  code: built — migration 2026_11_02_141639 (with down()), CharacterCard casts + fillable, GametoraCharacterCardParser, StoreCharacterCards, CatalogController + TraineeProfileController, Catalog/Show.vue + TraineeProfile.vue; measured 2026-11-18 at HEAD 2546689e: 263/263 rows non-null with keyed objects and source_url/snapshot_path/fetched_at
  superseded-by: —
  amended-by: —
  open: none — the 2026-11-18 measurement closed the re-fetch item; provenance backfill for the wider data_sources table is tracked separately (Phase 5.1 of IMPLEMENTATION-ROADMAP-2026-11-18.md)
-->

- **Status:** Accepted
- **Date:** 2026-11-02
- **Deciders:** Architect (schema), Data Engineer (pipeline)

## Context

The trainee profile (`/career/setup/trainee/{id}`) rendered `N/A` for Base stats and Growth on every costume form, even though the GameTora character-card dataset (`database/seeders/data/gametora-characters.897e63ea.json`) already carries `base_stats` (5 ints) and `stat_bonus` (growth rates, 5 ints) per card. The `character_cards` table had no columns for them, and `GametoraCharacterCardParser` ignored both fields.

## Decision

Add nullable `base_stats` (json) and `growth_rates` (json) columns to `character_cards`. Store them as **keyed objects** (`{Speed: 83, Stamina: 88, ...}`), not positional arrays, so rendering is order-independent of `config('scenarios.stat_order')`. The parser converts the source's positional 5-int arrays to keyed objects using the canonical key list `['Speed', 'Stamina', 'Power', 'Guts', 'Wit']`. Malformed or missing input yields null (not a partial row).

## Consequences

- Positive: profile page shows real base stats and growth rates after a re-fetch; no `statOrder`-mismatch latent bug.
- Negative: a migration is required; existing rows need `uma:reparse` to populate; ADR-0003 provenance applies (stamped in `StoreCharacterCards`).
- Storage shape is load-bearing: changing from keyed to positional would require a data migration and a re-fetch.

## Alternatives considered

- Positional arrays matching `statOrder`: rejected because a future `statOrder` change would silently mislabel every stat.
- No columns, keep `N/A`: rejected because the source has the data and the page's "not stored" wording would be dishonest.

## Source of truth

`docs/UMAMUSUME_REFERENCE.md` §3 (Character Roster); GameTora `character-cards.json` fields `base_stats` and `stat_bonus`. PRD citation: `FR-A-5` (catalog data); no dedicated FR exists for per-card base stats — filed as a PRD gap note.