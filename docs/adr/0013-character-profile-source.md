# ADR-0013: Character profile source — basic information at character grain

<!-- ADR-CONTRACT
  decision: withdrawn — superseded by ADR-0012 Decision 4, which the 2026-09-30 port resolved in master's favour
  code: superseded — migration 2026_09_29_182820_create_umamusume_profiles_table.php (master) landed first; the branch's height_cm/bust_cm/waist_cm/hip_cm columns did not ship
  superseded-by: 0012 (Decision 4 is the authority for the profile block)
  amended-by: —
  open: one point of this record stands and is not withdrawn — va_en is the romanisation of va_ja, not an English dub cast, and master adopted that reading
-->

Status: **Withdrawn — superseded by `ADR-0012` Decision 4 and the branch's 2026-09-30 probe record.**

This ADR was drafted on `feat/umamusume-detail-page` for the same `umamusume_profiles` table that `master`
had already authorized and landed under `ADR-0012` **Decision 4**. The 2026-09-30 port resolves the
collision in `ADR-0012`'s favour. The full body is preserved at
`archive/adr/0013-character-profile-source.md`.

## What was absorbed, and what was moot

- **Field inventory, coverage table and the `rl`/`sex`/`race` allowlist obligation** move into `ADR-0012`
  Decision 4 and docs/data/2026-09-30-characters-source-probe.md.
- **The schema decision is moot.** Master's `2026_09_29_182820_create_umamusume_profiles_table.php` landed
  first; this branch's `2026_09_30_120000_…` migration and its `height_cm`/`bust_cm`/`waist_cm`/`hip_cm`
  columns are not what shipped — master stores `height`/`three_sizes_b`/`_h`/`_w`.
- **The drafted `FR-A-7` text landed in `PRD.md`** as `A-7`, corrected to the `va_en` romanisation reading
  and the `n = 105` counts.
- **One point stands and is not withdrawn:** this record read `va_en` as the romanisation of `va_ja`, not a
  dub cast, and `master` adopted that reading. It was the authority question, not the data, that is
  withdrawn.

## Why a withdrawn ADR stays as a trail

A withdrawn ADR is not deleted: the `Context`, `Decision` and `Consequences` in the archived body are the
record of a parallel attempt, and they are what show that two branches independently reached the same table
with different column names. That is the evidence a later reader needs to understand why the allowlist is
enforced at both ends.

This ADR **is in conflict with master and does not supersede it**. Two migrations create one table and two
definitions exist for each of three classes, so this document's decisions are provisional against that work
and the collision is an owner call — which the owner resolved on 2026-09-30 in `ADR-0012`'s favour.

## Links

- `ADR-0012` Decision 4 (the authority), `PRD.md` A-1, A-7, FR-A-6, `docs/research-scratch/GOVERNANCE.md` §"CONSTRAINTS.md (root quality bar, C-1 to C-9)" C-4
- docs/data/2026-09-30-characters-source-probe.md (the coverage measurements)
- Full body: `archive/adr/0013-character-profile-source.md`
