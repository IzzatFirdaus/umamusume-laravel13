# Seeder correction + private-DB verification (2026-11-15)

**Scope:** `database/seeders/RosyDreamsRiceShowerSeeder.php` corrected per the verified roadmap in
`05-seeder-patch-review.md`, then verified on a private database copy (EX-12a ruling). The shared dev
database was never touched; no UI file was read for behaviour or modified.

## What changed in the seeder

| Task | Fix applied |
| --- | --- |
| 1 — Trainee card | `CHARACTER_CARD_ID = 31` (card 31 is `[Rosy Dreams]` Rice Shower; card 33 is Winning Ticket's `[Get to Winning!]`, the mis-assignment the audit caught). `UMAMUSUME_ID = 30` named as a constant beside it. |
| 2 — Deck | New `DECK` constant writes six `deck_slots` rows keyed by slot position, FK = `support_cards.id` (250 Tokai Teio, 263 Super Creek, 265 Nishino Flower, 266 Haru Urara, 267 Biko Pegasus, 277 Matikanetannhauser); the GameTora `support_id` values (30003…) are kept as comments only. Ownership strings are `DeckSlot::OWNERSHIP`'s exact values; slot 6 (Super Creek) is the client's Friends rental → `rented`. Levels 50/35/30/35/30/30 per §1.4. |
| 3 — Legacy | `LEGACY` constant transcribes doc §1.5 verbatim into the exact `LegacySelectionPayload` shape: top-level `legacies` + `affinity` only; parents as FK ids plus `{name, rank_letter, is_guest}` (Vodka `B+` guest false; Maruzensky `UG` guest true); grandparents as plain `ancestors` strings (Daiwa Scarlet, Seiun Sky, Daiwa Scarlet, Taiki Shuttle); the six blue sparks as `{kind, target, stars}` under their correct ancestor. The payload is passed through `LegacySelectionPayload::fromArray()` at seed time, so a wrongly-shaped transcription now fails at seed time instead of throwing on the Inheritance screen. |
| 4 — Events | Two `turn_events` anchored at the run's only recorded turn (67 — resolved via `CareerCalendar::positionToTurnIndex`, not hardcoded), typed with `TurnEventType::SupportCard` (Super Creek `[Piece of Mind]`, choice "Piece of Mind", bond +5) and `TurnEventType::Character` (Rice Shower, "a small snack", bond +5) — no free strings. |
| 5 — Guard | The early-return guard is replaced by delete-and-reseed: a prior run carrying the same `import_source` is deleted together with its deck slots, events, turn entries, race entries and skill pivots, then re-imported whole. Documented in the class docblock; re-running the seeder is now idempotent by refresh. |

Deliberate nulls (documented in the docblock): `energy` and `condition` (§1.7 shows a bar and a warning
banner, never a number), `current_objective_index` (three cleared goals + one active, unnumbered). Wit's
1,800 cap has no column — `training_runs` carries `cap_speed`..`cap_guts` only.

## Commands — private DB spin-up (EX-12a)

```
copy database/database.sqlite -> .scratch-uma/browser-obs-followup/seeder-verify.sqlite   (byte copy)
set DB_DATABASE=<abs path to seeder-verify.sqlite>   (env passed via process env dict — KI-163 trap 1)
php artisan db:seed --class=Database\\Seeders\\RosyDreamsRiceShowerSeeder --no-interaction
php -S 127.0.0.1:8157 -t public vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
    (cwd=public, absolute router path — KI-163 trap 2; env DB_DATABASE as above)
```

Server stopped after evidence capture. The scratch copy lives under `.scratch-uma/browser-obs-followup/`
(session-private, gitignored). **No `migrate:fresh` was run anywhere; the shared dev file is untouched.**

Note on run identity: the delete-and-reseed path imports the corrected run as a **new row, id 32** (the
old run 14 row is deleted with its children). All verification below therefore observes
`/training-runs/32/...`; the dispatch's `/training-runs/14/...` URLs correspond to the pre-correction row.

## Verification — all four states observed on :8157

| State | Expected | Observed (measured) | Evidence |
| --- | --- | --- | --- |
| Cockpit | Rice Shower [Rosy Dreams], not Winning Ticket | `h1 = "Rice Shower"`; body contains `[Rosy Dreams]`; zero matches for `Winning Ticket` / `[Get to Winning!]` on the page; spark chips render (Guts ★, Stamina ★★★, Speed ★★ ×2, Power ★★★); team block "Blue Bloom · Dreaming Big", League 8 | `screenshots/verify-cockpit-32-1440x900.png` |
| Deck | six populated slots, correct cards, Super Creek Rented | `Not equipped` count 0; all six names present (Tokai Teio, Nishino Flower, Biko Pegasus, Super Creek, Matikanetannhauser, Haru Urara); Rented adjacency to Super Creek confirmed | `screenshots/verify-deck-32-1440x900.png` |
| Inheritance | tree renders, no exception | No exception text of any kind on the page; Vodka, Maruzensky and all three grandparent names present (Daiwa Scarlet ×2 counted once by substring test); `B+`, `UG`, `Guest` all render; rank-letter vocabulary correct | `screenshots/verify-inheritance-32-1440x900.png` |
| Events | two seeded events render | Both `<details>` disclosures open to show Super Creek `[Piece of Mind]` ("Piece of Mind", fatigue option) and the Rice Shower character event ("a small snack"); both at turn 67 | `screenshots/verify-events-32-1440x900.png` |

**Inheritance no-throw confirmation (two layers).** (1) Seed-time: the seeder itself calls
`LegacySelectionPayload::fromArray(self::legacySelection())` before writing, and the seed command exited
0. (2) Read-time: the browser load of `/training-runs/32/inheritance` produced zero console errors and
zero page errors, and the rendered text contains the full tree — the accessor's strict validation
(`TrainingRun::legacySelection()` → `fromArray()` on every read) accepted the payload.

DB-level cross-check on the scratch copy after seeding: `character_card_id = 31`,
`inheritance_parent_a_id / _b_id` populated, six `deck_slots` rows (correct FK ids, `rented` on slot 6,
levels per §1.4), two `turn_events` rows at turn 67, `legacy_selection` non-null and validating.

## Gates run

| Gate | Result |
| --- | --- |
| `php -l` on the seeder | clean |
| `php -l` on the payload-check probe | clean; payload validated through the real `LegacySelectionPayload` before any DB write |
| `vendor/bin/pint --test --format agent database/seeders/RosyDreamsRiceShowerSeeder.php` | passed |
| `vendor/bin/phpstan analyse database/seeders/RosyDreamsRiceShowerSeeder.php --no-progress --memory-limit=1G` | No errors |
| `php artisan test --compact tests/Feature/HistoricalRunImportTest.php` | 17 passed (143 assertions) |
| `php artisan test --compact tests/Feature/AncestorRankTest.php` | 5 passed (20 assertions) |
| Lore gate (`php tools/lore.php`) | no hit names the seeder or the seeders tree; docs-mode figure 615 hits / 75 exempt lines (rising count = corpus growth, ruled on per §9; delta from prior recorded figure is corpus drift, not this change) |

## Lingering issues

1. **Team rank has no column.** Doc §1.8's Team Info panel shows "Team rank: S"; `training_runs` carries
   `team_name`, `team_motto`, `team_league_placement`, `team_preseason_wins`, `team_stat_grades` — no
   team-rank column — so the Cockpit prints "Team rank — not recorded" for this run. A seeder cannot fix
   this; it needs a schema change, which is outside this dispatch's constraints. Recorded here, not filed
   as a UI defect (the empty-state rendering itself is correct per the `N/A` convention).
2. **The 11-combined-bursts figure is stored decomposed** (spirit 6 + extreme 5 = 11, per the roadmap's
   verified read of §1.8); the combined number is a client summary, not a stored value. Rendering matches
   the stored decomposition.
3. **EX-16's deck hole is closed for this run only.** The URA seeders (`UraFinaleRunsSeeder`,
   runs 15/16/22) still import no deck slots — `03-deck-coverage-hole.md` remains accurate for them.
   This dispatch did not touch those seeders.
4. **Support-card detail data**: the deck screen renders names/levels/ownership from the seeded rows;
   whether each card's effect text is complete is a catalog-data question outside this seeder (the
   `support_cards` rows were already present in the catalog; nothing about their effect payloads changed).

## Provenance

- Seeder diff: this dispatch (patches applied 2026-11-15).
- Verification evidence: measured on the private copy served at :8157, screenshots captured at 1440×900
  full-page against that server's responses (timestamped per EX-12b; server stopped after capture).
- Prior audit packages under `docs/audits/browser-observation-2026-11-1{4,5}/` and the other follow-up
  files are byte-unchanged by this dispatch.
