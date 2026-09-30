# Slice 2 verification — support card entities

Date: 2026-10-01 (work begun 2026-09-30)
Scope: the four deliverables the 2026-09-30 review found missing, plus the five corrections it ruled on.
ADR: `docs/adr/0014-support-card-entities.md` (corrected the same day)
Supersedes: the first Slice 2 pass, which was schema-and-models only.

---

## 1. Shared-database fingerprint

The review asked for the fingerprint as proof rather than assertion. Recorded before and after every
`migrate` this slice ran.

| When | Size | mtime |
|---|---|---|
| Slice 1 hand-off (as recorded in that pass) | 1,667,072 | 2026-09-30 19:11 |
| This slice, before any work | 409,600 | 2026-09-30 22:51:41 |
| Mid-slice observation | 1,179,648 | 2026-09-30 23:59:43 |
| After all slice work | 1,179,648 | 2026-09-30 23:59:43 (unchanged since the mid-slice read) |

The "after" value is identical to the mid-slice one, and the table counts match it exactly
(`tables=30`, `migrations=37`, `support_cards=0`). Nothing in this slice advanced the file: its last
movement was 23:59:43, and every command this slice ran against a database named a scratch path instead.

**This slice did not write `database/database.sqlite`, and the file is not stable underneath it.** Two
findings, neither of them mine to resolve:

1. **A live `artisan serve` is attached to the shared database.** `Get-CimInstance` shows PID 19272
   `php artisan serve` (started 22:56:26) and PID 18736 `php -S 127.0.0.1:8000`, plus PID 21232
   `php artisan boost:mcp`. The file's mtime advanced between two of my own reads (22:51:41 → 22:53:47)
   with no command of mine in between.
2. **A peer's `migrate` applied my *uncommitted* correction migration to the shared file.** It now carries
   37 migrations, all in batch 1, and `PRAGMA foreign_key_list(support_cards)` returns empty on it. The
   working tree is shared, so a file I had not committed was visible to that run. The schema that landed is
   the correct one, but it landed without the owner's migrate decision, and the size dropped from
   1,667,072 to 409,600 first, which means the file was **rebuilt**, not only migrated.

Consequence recorded rather than acted on: every browser and import measurement in this file was taken
against a scratch database on a throwaway port, because the shared file cannot be treated as a stable
fixture while a dev server is writing to it.

## 2. The corrupted scratch database — cause named

The first pass reported "the scratch database is corrupted, let me create a fresh one" and moved on. The
cause is worth naming, because it is a repeatable trap:

- The corrupted file was **`.scratch-uma/test.db`**, created by this session. It was **not**
  `.scratch-uma/verify-slice1.sqlite`, which the Slice 1 record names.
- It was produced by `cp database/database.sqlite .scratch-uma/test.db`. A bare file copy of a SQLite
  database that is open by a writer in WAL mode copies the main file without its `-wal` sidecar, and the
  result reads back as `database disk image is malformed`. Confirmed present on this host: `testing-wal`
  and `testing-shm` sat in the repo root at session start, from the suite run that was in flight.
- So the corruption was a symptom of an unsafe copy method, not a pre-existing defect and not a shared-DB
  write. **Every database in this pass was created by `php artisan migrate` against a new file path.
  Nothing was copied.**

Separate finding, pre-existing: `.scratch-uma/verify-slice1.sqlite`, named as the fixture in the Slice 1
verification record, is **not present** in `.scratch-uma/` any more. That record cites an artifact that no
longer exists. Not caused by this session; reported so the record is not read as reproducible.

## 3. Schema corrections (task 5 of the review)

Four defects, all found by checking the shipped schema against `support-cards.json` rather than against
intent. Landed in `2026_09_30_151945_correct_support_card_schema_and_constraints`.

| # | Defect | Evidence | Fix |
|---|---|---|---|
| 1 | `char_id` declared `foreign … on umamusume` | `umamusume.id` is an autoincrement surrogate over 268 rows while the source id is 1001..1149 and lives in `external_ref`; 23 records carry a 9000-block staff id | FK dropped, column kept verbatim. Shape copied from `character_cards`, which separates `card_id` from `umamusume_id` |
| 2 | `name` was a composed string | the export has `char_name` and `title_en`, no `name_en` | Column dropped, `char_name` added, `displayName()` composes at the view boundary |
| 3 | **The four CHECK constraints rendered no SQL at all** | `Blueprint::check()` does not exist as a table method; as a column modifier the SQLite grammar drops it. `PRAGMA`/`sqlite_master` shows no `CHECK` token | Rebuilt in raw SQL, where SQLite both emits and enforces |
| 4 | `support_effects.calc` allowed `flat` and `level` | across 35 records: `mult` 3, `add` 1, absent 31. `flat`/`level` are this repo's prose (§1.4.8), not export values | CHECK narrowed to `('mult','add')` or null |

Defect 1 was more wrong than first reported. The reviewer's ruling said "SQLite does not honour CHECK";
measurement says the CHECKs were never in the DDL, and separately that **all 559** cards would have been
rejected by the foreign key, not only the 23 staff ones: after the real import, the count of cards whose
`char_id` matches no `umamusume.id` is 559 of 559.

Defect 3 corrects a claim in my own previous report. I described the constraints as belt-and-braces and,
when `slot_position` 7 inserted cleanly, deleted the test instead of fixing the constraint. Both are now
pinned, at both layers.

**Measured, on a database migrated from zero (`verify-schema.php`):**

```
support_cards    CHECK present: YES   FK count: 0
support_effects  CHECK present: YES   FK count: 0
deck_slots       CHECK present: YES   FK count: 2

  ✓ slot_position 7: rejected     ✓ rarity 3 / type group: accepted
  ✓ slot_position 0: rejected     ✓ NPC char_id 9001: accepted (would trip the old FK)
  ✓ rarity 5: rejected            ✓ calc 'mult': accepted
  ✓ type 'bogus': rejected        ✓ calc NULL (the other 31): accepted
  ✓ calc 'flat': rejected
```

`release_status` survived the rebuild as a real generated column (`varchar as (CASE …) stored`), and
`down()` was exercised by calling the migration object directly against a scratch copy: 2 rows in, 2 rows
out, shape restored, and re-applying `up()` returns the CHECKs. `migrate:rollback` was **not** run; the
fence on it stands.

## 4. Application-layer slot validation

The review required validation that holds even where the DDL does not.

- `DeckSlot::booted()` guards `saving` against `self::POSITIONS`, raising `InvalidArgumentException`.
- `StoreDeckRequest` restricts the array keys with `'array:1,2,3,4,5,6'`, so a hand-made POST cannot
  address slot 9.
- `DeckSlotFactory::atPosition()` refuses an impossible position at construction.

Three tests, one per layer, plus the model-layer one specifically because factories reach the table
without passing through a form request:

```
✓ it rejects a slot position outside one to six at the model layer
✓ it rejects a slot position outside one to six at the column layer
✓ it refuses to build a slot at an impossible position through the factory
```

## 5. R75 framing

Corrected in ADR-0014 rather than left implied. R75 governs `scenario_slots.tier` and the **race** grade
labels (Pre-OP, OP, G3, G2, G1, EX); it does not reach support-card strength tiers. Card tiers are held
because no current Global source for a tier assessment exists — Game8 is dated and scored at MLB, GameWith
is `[JP]`, uma.guide's currency is unconfirmed, and the legacy PDF is deprecated and outside the edit
fence. The ADR now says the constraint is source availability, not the ruling.

## 6. Migration default for existing runs

Stated in ADR-0014: pre-existing `training_runs` rows get **zero** `deck_slots`, and that is the correct
state, not a gap. A deck is Trainer-supplied and no source can infer it after the fact. The two rendering
consequences — a named empty state, and `deck` omitted rather than `[]` when not loaded — are both
implemented and tested.

## 7. API

`GET /api/v1/support-cards` (paginated) and `GET /api/v1/support-cards/{supportCard}`. Envelope matches
`TrainingRunController`: `data` plus `pagination`, `->resolve()` on the collection, `pageSize` clamped
1..100, no auth, and **no write endpoint**, which keeps `ApiV1ValidationEnvelopeTest`'s pinned premise
("the 422 branch is unreachable, because the api has no write") true.

Sample list row, read from the live server against the imported catalogue:

```json
{"data":[{"id":4,"supportId":10015,"charId":1030,"charName":"Rice Shower",
  "nameJa":"ライスシャワー","titleEn":"[Tracen Academy]","titleJa":"[トレセン学園]",
  "rarity":1,"type":"stamina","releaseJp":"2021-02-24","releaseGlobal":"2025-06-26",
  "releaseStatus":"Global","effects":[[1,5,-1,-1,10,10,-1,-1,15,-1,-1,-1],[2,10,-1,-1,-1,25,-1,-1,-1,35,-1,-1]],
  "sourceUrl":"https://gametora.com/data/umamusume/support-cards.88dea522.json",
  "fetchedAt":"2026-09-27T15:49:00+00:00","isManual":false}],
 "pagination":{"page":1,"pageSize":25,"totalItems":559,"totalPages":23}}
```

Machine tokens on the wire (`rarity` int, `type` export key), not client words — the client vocabulary
(`Wit`, `Pal`, `SSR`) is a view-boundary mapping, matching how `mood` and `status` are emitted as enum
values elsewhere.

`TrainingRunResource` gained `deck`, ordered by position, with `isFriendSlot` and the nested card:

```json
"deck":[{"slotPosition":6,"isFriendSlot":true,
  "supportCard":{"supportId":10022,"charName":"Aoi Kiryuin","type":"friend",…}}]
```

## 8. UI

`resources/views/components/deck-panel.blade.php`, mounted on `runs/show.blade.php` between the scenario
panels and the turn log as its own `h2`. Deliberately **outside** the `hasScenario()` guard: every scenario
has six slots, so a run naming no scenario still had a deck.

Measured through the browser on the imported catalogue (not factories):

| Run | State | selects | options/slot | equipped rows | Scenario Link | empty state |
|---|---|---|---|---|---|---|
| 1 | `ura_finale`, six cards | 6 | 253 | 6 | 0 | – |
| 2 | `unity_cup`, two linked | 6 | 252 | 2 | **2** | – |
| 3 | blank scenario, none | 6 | 252 | 0 | 0 | **1** |

Positions arrive as 1..6, slot six reads `Slot 6 · Friends` whatever sits there, and the option count is
`1 blank + 251 Global + any equipped non-Global card` in every case.

**Page weight, measured and owed to the owner.** Offering the full Global catalogue in six selects takes
the run page from roughly 90 KB to **360 KB** (and 329 KB with nothing equipped). Six selects duplicating
251 `<option>` elements is the cost. It does not breach C-6, which budgets catalog index latency rather
than page weight, but it is a real regression a scoping decision (search-first picker, or a filtered
shortlist) would remove. Flagged rather than silently accepted.

`stat-band.blade.php` said `breakthrough not tracked + deck untracked.` The second half became false the
moment the panel shipped. It now reads `breakthrough not tracked … Deck recorded under Support deck; no
card in the catalogue raises these ceilings.` The last clause is checked, not assumed: effect ids 20-24
(`Max Speed` … `Max Wit`) are carried by **zero of the 559** records, which §1.4.8 states and this pass
re-measured.

## 9. Defect the browser pass found that the suite could not

Run 3 above originally returned **Laravel's exception page at 949,622 bytes** with no deck block at all.
`TrainingRun::hasScenario()` read `$this->scenario !== null`, so an empty-string scenario counted as
declared; `stat-band` then looked its caps up under `scenarios.scenarios.` (blank key), got nothing, and
fatalled on `$def['cap_bonus']`.

Three places spelled "is a scenario set" differently, and the model's spelling was the wrong one:

| Site | Before | After |
|---|---|---|
| `TrainingRun::hasScenario()` | `$this->scenario !== null` | `filled($this->scenario)` |
| `TrainingRun::scenarioKey()` | `?? baseline` (so `''` stayed `''`) | `?: baseline` |
| `runs/show.blade.php:39` caption | `$run->scenario === null` | `$run->hasScenario()` |
| `runs/index.blade.php:26` label | `$run->scenario === null` | `$run->hasScenario()` |

The view already guarded `! $run->scenario` in one place, so the model and the view disagreed about the
same value; the view's reading is the one that renders. The web form normalises `''` to `null`, which is
why no test covered this — and Slice 4 inserts Trainer-supplied rows, where a blank column is reachable.
After the fix run 3 renders at 329,355 bytes with the empty state and the caption `No scenario set`.
Pinned by a new test in `GoalPanelsOnRunDetailTest`.

## 10. Import path

Named source, verified by measurement rather than by the subagent's report:

- **Source**: GameTora data export, declared in `config('uma.sources')` as `gametora-support-cards` and
  `gametora-support-effects`.
- **URLs**: `https://gametora.com/data/umamusume/support-cards.88dea522.json` and
  `…/support_effects.ca447e53.json`. I initially suspected the `88dea522` token was invented, because it
  appears nowhere in this repo's prose. It is not: `research-scratch/data/json/manifest.live.json`
  declares `support-cards => 88dea522` and `support_effects => ca447e53` alongside the corroborated
  `character-cards => 679f7c2e`. Suspicion retracted on evidence.
- **Raw bodies**: `database/seeders/data/support-cards.88dea522.json` (591,364 B) and
  `…/support_effects.ca447e53.json` (17,609 B). Both are **byte-identical** to the scratch source
  (md5 `2bde884b…` / `f7ad9973…`).
- **Re-run command**: `php artisan uma:import:support-cards` — offline, idempotent.

Measured on a scratch database migrated from zero:

```
first run : 559 created, 0 updated, 0 skipped (manual)   |  35 created
re-run    :   0 created, 559 updated, 0 skipped          |   0 created  ← idempotent

cards                     559   (expect 559)
effects                    35   (expect 35)
Global-released cards     251   (expect 251)
9000-block staff cards     23   (expect 23)   ← would all have been rejected by the old FK
anchors not 12 wide         0   (expect 0)
calc mult / add / null    3 / 1 / 31                (expect exactly that)
rarity 1/2/3          [146, 101, 312]              (expect that)
```

`is_manual` protection tested against the real command, not a mock: a row was marked manual and renamed,
`uma:import:support-cards` re-run, and the row came back with `char_name = 'Hand Corrected Name'` and
`is_manual = true` — **manual row PRESERVED**.

Every count above matches `UMAMUSUME_REFERENCE.md` §1.4.1/§1.4.2 and the export itself, so the pipeline,
the schema and the documentation agree on the same numbers.

### Open point the import handed back

The subagent corrected a fact I had asserted. `characters.json` (163 records) does hold 17 ids in the 9000
block; the document that feeds `umamusume` is `gametora-characters.e9e9ee6d.json` (268 records,
`char_id` 1001..1149, none above 1149). My original measurement read `$row['id']` from a document keyed by
`char_id`, so every row resolved to `0`, the range printed `0 .. -`, and the check passed for the wrong
reason — a guard that cannot fail. The conclusion stands; the cited file was wrong, and both the ADR and
the migration docblock now carry a dated correction naming the mistake rather than a silent edit.

## 11. Gates

All five run against the working tree with every file in place.

| Gate | Command | Result |
|---|---|---|
| Tests | `php artisan test --compact` | **972 passed, 2 skipped, 0 failed** (16,232 assertions, 65.53 s) — the working tree, import files included |
| Static analysis | `vendor/bin/phpstan analyse --no-progress` | **[OK] No errors** (level 6) |
| Formatting | `vendor/bin/pint --test --format agent <my files>` | **passed** |
| Lore | `composer lore` | **exit 0** |
| Lore (code) | `composer lore-code` | **exit 0** |

**Two numbers, because the commit and the working tree differ (§12).** The 972 above counts the four
import test files, which are on disk but not committed:

```
GametoraSupportCardParserTest    10 passed (12,524 assertions — it walks all 559 records)
GametoraSupportEffectParserTest   9 passed (   277 assertions)
StoreSupportCardsTest             9 passed (    73 assertions)
SupportCardFetchTest              8 passed (    41 assertions)
                                 ─────────  ─────────
                                 36 passed
```

So the committed subset alone is **936 passed**. Both figures were run, not derived; `972 − 36 = 936` is
stated because the two files that would have isolated `HEAD` (a stash, or a second worktree) are unsafe to
use in a tree another session is writing.

PHPStan caught two of my own errors, both real: `$this->rarity?->value` and
`$this->fetched_at?->toIso8601String()` used nullsafe operators on columns the schema declares NOT NULL.
Fixed to `->` rather than suppressed, per the floor on suppressions.

Pint and PHPStan were run **read-only** (`--test`, no `--dirty`) on an explicit file list. The working tree
carries another session's uncommitted files, and `pint --dirty` would have rewritten them; it did need to
fix four of mine once I added the `@property` blocks and the `?:` change.

Suite baseline movement: 869 (Slice 1 hand-off) → 887 → **972**. The rise is this slice's `SupportCardTest`
(36), `ApiV1SupportCardTest` (13), `RunDeckTest` (17), the import suite (36), and the blank-scenario case.

## 12. What this slice did NOT commit, and why

The review asked for an import path. It is **built and verified above, but deliberately not committed.**

`config/uma.php` and `app/Services/DataPipeline/PipelineRunner.php` each carry two kinds of uncommitted
edit at once: mine (two `gametora-support-*` source entries; two `is_a()` branches) and a concurrent
session's (`seed_file` declarations for four other sources; the roster and source-document seeders that
consume them). The same session also changed `phpunit.xml` to `DB_DATABASE=:memory:` at 23:42 and left
five seeder files plus three JSON bodies untracked.

Staging either file whole would land another session's in-flight work under this commit's message, and
committing the import's own files without them would leave `HEAD` unable to run
`php artisan uma:import:support-cards`. Both are worse than waiting, so this commit takes everything that
is unambiguously one slice's and stops at the seam.

To finish it, whoever owns the seeder work should commit the config and pipeline changes together, then:

```
git add app/Actions/StoreSupport{Cards,Effects}.php \
        app/Console/Commands/UmaImportSupportCards.php \
        app/Services/DataPipeline/Contracts/Support{Card,Effect}SourceParser.php \
        app/Services/DataPipeline/Parsers/GametoraSupport{Card,Effect}Parser.php \
        database/seeders/data/support-cards.88dea522.json \
        database/seeders/data/support_effects.ca447e53.json \
        tests/Feature/{GametoraSupportCardParserTest,GametoraSupportEffectParserTest,StoreSupportCardsTest,SupportCardFetchTest}.php
```

**Verification limit, stated rather than glossed.** The 972 was measured with the whole tree present. The
committed subset is smaller and additive, but a strict `HEAD`-only run was not performed: isolating it
would mean `git stash -u` or a second worktree in a directory another session is actively writing to,
which is how work gets lost. The exclusion list above is the honest boundary.

## 13. Fence

| Hard stop | Status |
|---|---|
| Never write `database/database.sqlite` | **Honoured by me.** No command of this slice opened it for writing; every migrate ran with `DB_DATABASE=<scratch path>`, confirmed by the connection name in the error text. The file did change hands underneath the slice — see §1, attributed to a live `artisan serve` and a peer's `migrate`, not to this work. |
| No `migrate:fresh` / `db:wipe` / `migrate:rollback` in any form | **Honoured.** All scratch databases were created by a plain `php artisan migrate` on a new file. `down()` was exercised by calling the migration object directly, never through the rollback command. |
| No push, no merge | **Honoured.** `origin` is configured (`github.com/IzzatFirdaus/umamusume-laravel13`) and the local branch already sits 48 commits ahead of `origin/master` from prior sessions; no `git push`, `git merge` or `git rebase` was run here. |
| Shared-worktree safety | A `stash@{0}` from another session is present ("stale forks of branch commits + broken Blade cardless band (triage 2026-09-30)"). It was **not** read, popped, dropped or extended: in a tree another session is writing to, `git stash pop` can restore the wrong work. |
| No editing `ADR-0002` / `ADR-0003` / `ADR-0005` | **Honoured.** ADR-0005 is cited and superseded in prose only; its file is unmodified. `git status` shows no change to it. |
| No editing `.gitignore`, `CLAUDE.md`, `docs/deprecated/**`, `docs/frontend-review/**`, `README.md` | **Honoured.** None staged. The legacy PDF's tier column is cited as *reason not to use it*, not read as a source. |
| No tier labels until a current Global source is confirmed | **Honoured.** No tier column, no tier copy. ADR-0014 states the hold and its real reason. |
| No ADR-0013 dual-state resolution | **Honoured.** Not touched. |
| No new dependency | **Honoured.** `composer.lock` and `package.json` unchanged. |

