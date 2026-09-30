# ADR-0014: Support card entities — authorized for Phase 1

Status: **ACCEPTED** (owner ruling 2026-09-30, Slice 2 authorization). Supersedes ADR-0005.

Date: 2026-09-30
Deciders: product owner (authorization granted), implementing agent (draft)
Relates to: `ADR-0005` (declined in Phase 1, now reopened), `PRD.md` §6.9 (non-goal lifted by this ruling),
`UMAMUSUME_REFERENCE.md` §1.4 (support card system documentation), `config/scenarios.php` (scenario-linked characters)

## Problem

The owner authorized building support card entities for Phase 1, lifting the §6.9 non-goal. This ADR records the schema decisions, the linkage shape, and the data source.

## Decision: reference data only, with run linkage

Two domains exist in the brief's original three-entity proposal:

| Entity | What it actually is | Built? | Reason |
|---|---|---|---|
| `SupportCard` | published reference data about the game | yes | Same class as `scenarios` and `skills`: engine-owned reference data with provenance, `is_manual` protected |
| `DeckSlot` | a Trainer's deck for a run | yes | Required to record which six cards a Trainer used; no collection tracking needed |
| `UserSupportCard` | the Trainer's own collection: level, breaks, perk level | no | Collection tracking remains a non-goal; the deck records card identity, not ownership state |

The split follows ADR-0005's reasoning: reference data can be ingested from GameTora exports like other catalogues, while the deck is a minimal user-facing capability scoped to one run rather than a full collection manager.

## Linkage shape: join table, not direct columns

A run carries six support card slots, so the linkage must be one-to-many from `training_runs` to `deck_slots`. Direct columns (`support_card_1_id` through `support_card_6_id`) would violate normalization and make queries awkward. A join table is the correct shape:

- `deck_slots`: `id`, `training_run_id` (FK), `support_card_id` (FK), `slot_position` (1–6, unique per run)

The friend slot is `slot_position = 6`; no separate flag is needed because any card may occupy that position (UMAMUSUME_REFERENCE.md §1.4.1 correction from ADR-0005).

## Source and provenance

All reference data comes from the same GameTora export pipeline already configured in `config/uma.php`:

- `support-cards.json`: 559 records with `support_id`, `char_id`, `name`, `title_en`, `rarity`, `type`, `release`, `release_en`, `effects` (JSON array of `[effect_id, v1 … v11]` anchors)
- `support_effects.json`: 35 effect definitions with `effect_id`, `name_en`, `name_ja`, `calc`, `symbol`, `description_en`

Provenance rules match FR-B: every fact lands with `source_url` and `fetched_at`, rows with `is_manual = true` are never overwritten by the fetch engine, and new sources require a parser class plus tests against stored fixtures.

## Schema corrections from ADR-0005 analysis

Applied to the design below, each with its reason:

1. **`is_friend_card` is not a card attribute.** The client's deck editor labels the sixth slot `Friends`, and any card may occupy it. `slot_position` alone carries the role (1–5 training, 6 friend). A boolean on the card would be wrong the moment a Trainer puts a stat card there.

2. **`type` has seven values.** The export uses `speed`, `stamina`, `power`, `guts`, `intelligence`, `friend`, `group`. Store the export key, map to the client word at the view boundary. "Pal" for `friend` is Game8's label; the only friend-adjacent word proven from the client is `Friends`, and it names the slot.

3. **Scenario Link is derived, not stored per card.** It is `support_card.char_id ∈ scenario.linked_characters`, proven by the Unity Cup frame where Haru Urara badges as linked. Storing it per card would freeze a per-scenario fact into the card. The join costs nothing and cannot drift.

4. **No per-level effect table.** Store the eleven anchors as they arrive and compute the displayed value with floor-interpolation (UMAMUSUME_REFERENCE.md §1.4.7). A materialised 50-row table per card per effect is 28,000 rows of derived data that can silently disagree with the rule that produced it.

5. **Unique Perk effects are not derivable.** The export's `effects` array covers the card's levelled effects, not the perk's. The perk's magnitude at a given perk level is not derivable from anything in this repository. ❌ UNVERIFIED: the Unique Perk value table and what raises the perk level.

## Proposed schema

Reference domain — same pipeline, same provenance rules as `scenarios` and `skills`:

```sql
CREATE TABLE support_cards (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    support_id INTEGER UNIQUE NOT NULL, -- GameTora support_id
    char_id INTEGER, -- FK to umamusume (null for NPC staff cards)
    name TEXT NOT NULL, -- e.g., "Tokai Teio [Dream Big!]"
    name_ja TEXT, -- Japanese name
    title_en TEXT, -- e.g., "Dream Big!"
    title_ja TEXT, -- Japanese title
    rarity INTEGER NOT NULL CHECK(rarity IN (1, 2, 3)), -- R=1, SR=2, SSR=3
    type TEXT NOT NULL CHECK(type IN ('speed', 'stamina', 'power', 'guts', 'intelligence', 'friend', 'group')),
    release_jp DATE,
    release_global DATE,
    release_status TEXT GENERATED ALWAYS AS (
        CASE
            WHEN release_global IS NOT NULL THEN 'Global'
            WHEN release_jp IS NOT NULL THEN 'JP-only'
            ELSE 'Unreleased'
        END
    ) STORED,
    effects JSON NOT NULL DEFAULT '[]', -- [[effect_id, v1, ..., v11], ...]
    source_url TEXT NOT NULL,
    fetched_at TIMESTAMP NOT NULL,
    is_manual BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE support_effects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    effect_id INTEGER UNIQUE NOT NULL,
    name_en TEXT NOT NULL,
    name_ja TEXT,
    calc TEXT CHECK(calc IN ('flat', 'mult', 'add', 'level')),
    symbol TEXT,
    description_en TEXT,
    source_url TEXT NOT NULL,
    fetched_at TIMESTAMP NOT NULL
);
```

Trainer domain — run linkage:

```sql
CREATE TABLE deck_slots (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    training_run_id INTEGER NOT NULL REFERENCES training_runs(id) ON DELETE CASCADE,
    support_card_id INTEGER NOT NULL REFERENCES support_cards(id),
    slot_position INTEGER NOT NULL CHECK(slot_position BETWEEN 1 AND 6),
    UNIQUE(training_run_id, slot_position)
);
```

## Consequences

- The fetch engine gains two new source datasets. `config/uma.php` already holds the GameTora entry; `support-cards.json` and `support_effects.json` are new parser + fixture pairs under FR-B's rule set.
- `PRD.md` §6.9 is amended to reflect that support-card reference data and run linkage are now in scope, while collection tracking (`UserSupportCard`) remains out of scope.
- The run detail page can now explain why a deck was strong by rendering real card names and effect values, without storing anyone's collection or break levels.
- Scenario Link badges render via a join at query time, not from a stored column, keeping the badge current when the scenario changes.

## Not designed here

Effect-value computation at runtime (which cards were on which tile in which turn), friendship-trigger arithmetic, hint-level accumulation, limit-break material economy, and Unique Perk value tables. UMAMUSUME_REFERENCE.md §1.4.3–§1.4.4 describe the mechanics; none become schema until explicitly requested.
