# Branch superseded: feat/catalog-detail-page

Date recorded: 2026-09-30
Status: warning record for future sessions

## Decision

Branch `feat/catalog-detail-page` is SUPERSEDED by branch `feat/umamusume-detail-page`.
It must never be merged into `feat/umamusume-detail-page`, in whole or in part, unless the
table hazard below has first been dealt with by hand. The canonical branch is
`feat/umamusume-detail-page` and the canonical schema is the one described by ADR-0013.

This branch is kept, not deleted, because the reusable view and test work on it is being
ported to the canonical schema by hand. Read that section before deciding anything is safe
to discard.

## The hazard, stated first

Both branches contain a migration that calls `Schema::create('umamusume_profiles')` under
different filenames:

- this branch: `database/migrations/2026_09_29_182820_create_umamusume_profiles_table.php`
- canonical branch: `database/migrations/2026_09_30_120000_create_umamusume_profiles_table.php`

Because the paths differ, git sees no textual conflict and reports a clean merge. Nothing in
a merge tool output, a diff review, or a textual read of either file will catch this. The
second migration to run throws `table already exists`, and `migrate:fresh` hard-stops
there, leaving the database half migrated. The failure appears at migrate time, far from
the merge that caused it.

If this branch is ever merged into the canonical branch, delete or rename one of the two
migration files in the same change that does the merge. Doing it later is how the failure
reaches someone who did not make the merge.

## Divergences that are not cosmetic

These are the places where this branch and the canonical branch encode different contracts.
Each is a real semantic difference, not a naming preference or a cosmetic spelling.

- Contract interface. This branch defines `ProfileSourceParser`. The canonical branch defines
  `CharacterProfileSourceParser`. Implementations, call sites, and type hints differ, so a
  merge leaves two names for one role.
- Config entry arity. The config entry here has 6 keys, including a `manifest` key; the
  canonical entry has 5 keys, and a test pins the exact key set, so the merge breaks it.
- Measurement column names. This branch stores `height`, `three_sizes_b`, `three_sizes_h`,
  `three_sizes_w`. The canonical branch stores `height_cm`, `bust_cm`, `waist_cm`, `hip_cm`.
- The `name_ja` column. This branch adds a `name_ja` column to the profile table. ADR-0013
  correctly refuses this, because the Japanese name already lives on `umamusume.name_ja`.
  Adding it again would duplicate the same fact in two tables with no owner and no rule
  about which one wins.
- Missing race filter. This branch's parser applies no filter on `race === 'uma'`, and its
  fixture contains a row with `"race": false` that the parser would happily store. The
  canonical parser filters to `uma` only. This is the most serious divergence after the
  migration hazard, because it silently persists rows that are not Umamusume.

## Authority

ADR-0013 is the authority for this domain. It was accepted with owner rulings dated
2026-09-30, and those rulings settled the contract name, the config keys, the measurement
column names, the `name_ja` question, and the `race` filter. Where this branch disagrees with
ADR-0013, ADR-0013 wins and this branch is wrong.

ADR-0012's Decision 4 was added on this branch. It is withdrawn. It is not a record of an
accepted decision and must not be cited as one.

## Reusable parts being ported

These files carry real, finished work. They are being ported to the canonical schema, which
is why this branch must not be deleted before that port is verified complete:

- `resources/views/catalog/show.blade.php`
- `resources/views/components/form-tabs.blade.php`
- `resources/views/components/aptitude-grid.blade.php`
- `resources/views/catalog/partials/form-detail.blade.php`
- their tests

Port them by hand against the canonical schema and canonical column names. Do not merge this
branch to obtain them, and do not copy this branch's migrations, parser, config entry, or
model into the canonical branch.
