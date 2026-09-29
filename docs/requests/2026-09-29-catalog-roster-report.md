# Catalog roster and trainee selector — closing report

Date: 2026-09-29
Slice: the `feat/catalog-roster-and-trainee-selector` branch, closing as `236e3a5`
Scope: Task 13, the branch's closing gate. Every number below was measured on this machine on
2026-09-29 against the live source and the populated database, and each measurement names the
command or the page that produced it.

---

## 1. What landed

The character-card layer and the searchable trainee selector, across two merges.

`bc11d9b` brought the card layer onto master. `236e3a5` (this session) merged the branch's three
remaining commits forward:

```
236e3a5 Merge branch 'feat/catalog-roster-and-trainee-selector'
4addcd6 fix(ui,http): the payload reaches every trainee the select offers, and ArrowUp opens on the last row
79d7f62 docs(plan): remove the card precondition Task 12 shipped from the brief
1c44698 fix(ui,http): the run form stays completable, and Enter means submit
```

The merge was **not** a rebase. `b63e111` is already an ancestor of master through `bc11d9b`, so
re-basing would have rewritten landed history. `git merge` produced no conflicts: the incoming five
files and the concurrent working-tree changes were disjoint, which was checked with
`Compare-Object` over both path lists before merging rather than discovered afterwards.

**Migrations** (all three from `bc11d9b`):

| Migration | Adds |
|---|---|
| `2026_09_29_120000_add_external_ref_to_umamusume_table` | `umamusume.external_ref`, indexed, nullable, not unique |
| `2026_09_29_120100_create_character_cards_table` | the card table, 14 columns |
| `2026_09_29_120200_add_character_card_id_to_training_runs_table` | the nullable run → card reference |

**Sources.** `config/uma.php` declares four, of which this slice added one:

| Key | Document | Added by |
|---|---|---|
| `gametora-characters` | `character-cards.<hash>.json` (trainee grain) | pre-existing |
| `gametora-character-cards` | the same document, card grain | this slice |
| `gametora-race-catalog` | `race_instances.<hash>.json` | this slice |
| `gametora-skills` | `skills.<hash>.json`, manifest-resolved | `ADR-0011`, already on master |

---

## 2. Counts against the export

Fetched live on 2026-09-29: `character-cards.e9e9ee6d.json`, HTTP 200, 251,294 bytes, 268 rows.

| Measure | Export | Database | Agrees |
|---|---|---|---|
| Distinct `char_id` with a `release_en` | 68 | 68 `release_status = GlobalReleased` | yes |
| `char_id` with no `release_en` | 67 | 67 `JapanOnly` | yes |
| Rows carrying `release_en` | 107 | 107 cards | yes |
| Total `char_id` across the whole export | 135 | 135 trainees | yes |
| Cards flagged `is_debut_form` | — | 68 | one per Global trainee |
| Cards flagged `unconfirmed` | — | **0** | see §4 |

Measured with `App\Models\Umamusume::where('release_status', …)->count()` and
`App\Models\CharacterCard::count()` against `database/database.sqlite`.

**A correction to the plan's own guard.** Task 13 Step 1 asserts
`if ($count !== 68) { … REFUSING TO COPY … }` on `Umamusume::count()`. That assertion cannot pass:
the character parser emits one record per `char_id` across the **whole** export, which the plan's
own Task 3 records as 135. 68 is the Global subset. The guard's intent held — 68 Global, 107
cards, 68 debut cards, 0 unconfirmed, all exactly as predicted — so the copy proceeded, but the
guard as written would have refused a correctly populated database. The count it should assert is
`where('release_status', 'GlobalReleased')->count()`, or 135 on the unfiltered count.

`skills` measured 1,910 rows, of which 623 are `GlobalReleased`, matching `ADR-0011`'s table
exactly.

---

## 3. Sources and tier

GameTora `character-cards` is the machine-readable source, Tier B. `docs/SOURCE-OF-TRUTH.md` §5
records the rule `ADR-0008` restates: a Tier B field needs a Tier A witness before it becomes app
data, and `character_cards.unconfirmed` is the column that carries an un-witnessed row.

No card is flagged `unconfirmed` today, because no card has been through the cross-check against
`umamusu.wiki` and Game8. The mechanism is present and unused, not absent and forgotten.

---

## 4. Unverified entries

**None flagged, and that is a statement about the queue rather than about the data.** All 107 cards
have `unconfirmed = false` by default; `unconfirmed` is never written by a fetch, so a zero here
means no human verdict has been recorded either way. Every Global card is therefore unverified in
the sense `ADR-0008` means, and the catalog hides nothing today because there is nothing hidden to
show.

---

## 5. Deviations from the request

Each of these is a place the shipped result differs from what the request or the plan's acceptance
table predicted, because the data disagreed with the prediction.

1. **105 cards, not ~140.** 107 after the source rotated. The plan's own Task 7 Step 5 recorded the
   hash change from `679f7c2e` to `e9e9ee6d`; both config entries now pin `e9e9ee6d`.
2. **`Fe` and `Fenomeno` both return "No trainee or card found."** Corrected as errata E-5/E-6
   before the browser pass. Measured: 0 options for each, with the no-results paragraph rendered and
   the listbox present but empty.
3. **The `F` query returns "10 of 14", and the plan's expected list cannot fit.** The plan expected
   Fine Motion and Fuji Kiseki as trainee groups "plus 9 cards whose title begins with F". Measured
   against the database: **14** cards match (10 whose bracket-stripped title starts with F, plus
   4 more belonging to the two F-named trainees — `[Noble Seamair]`, `[Titania]`, `[Shooting Star
   Revue]`, `[Succès Étoilé]`). The plan's list also omitted `[Fiery Aqua Vitae]`, a tenth F-titled
   card. With a cap of 10, `[Fast as Lightning]`, `[Fair Lady of the Waves]` and `[Fluttertail
   Spirit]` are cut. The cap and the keep-typing line behave as specified; the expectation was
   arithmetically impossible.
4. **Group headers are `role="presentation"`, not `role="group"`.** The plan's Step 2 table
   expected `role="group"`. This is a deliberate, documented and test-pinned choice, not a defect:
   `resources/js/trainee-combobox.ts:202-207` records that a group must own its options to be named
   (ARIA does not take a group's name from its content), the header here is a sibling of the
   options, and `TraineeSelectorTest.php:689-695, 773` pins both the reason and the string. The
   plan's expectation is the thing that is wrong.
5. **The `·` separator, not an em dash**, in each option's accessible name. Required by the
   em-dash rule; `RenderedCopyHygieneTest` fails a dash in Blade source outside comments.
6. **"1 form" / "2 forms", not a bare `0`.** D-220 and the `N/A`-plus-tooltip rule.
7. **Heading levels one below the request's naming.** Page `h1`, trainee `h2`, card `h3`.
8. **The catalog filter stays server-side behind a submit.** It is a GET form; typing does not
   filter live. Confirmed: `?search=ruin` returns 200 with exactly one `h2` (Gold Ship) and the two
   expected card titles.
9. **No collapse control.** The row count is the reason; the fold-list duplication defect is what
   a second control would reintroduce.
10. **One document is fetched twice**, once per grain, by two config entries. `ADR-0008` calls this
    "the sentinel at two grains" and rules it acceptable; it is a real duplicate request, not an
    accident.
11. **JP per-card dates are not stored.** They are read to derive `is_debut_form` and dropped,
    per `ADR-0008`.

---

## 6. New defects found

### 6.1 `[unsigned]` is stored and rendered as a card title

`character_cards` holds card `103601` (Air Shakur) with `title = "[unsigned]"` and a real
`release_en` of `2026-06-18`. The literal string `[unsigned]` is the source's own value — over the
268-row export, `title` equals `unsigned` on one row — so this is **not** a parser defect and not
something a parser should rewrite. But a card with a placeholder name is being presented to a
Trainer as a real Global costume, on the catalog list, on the detail page, and in the run-form
selector, with a dated release and a rarity beside it. The Global-only rule admits it because it
has a `release_en`; nothing checks that it also has a *name*.

This is a G-16 / D-30 problem: the tool is rendering a placeholder as though it were content, and
D-220's "absent is absent, never an empty slot" has no way to express "this row has no title yet".
Filed as **KI-38**.

### 6.2 `SkillFactory` derives `match_key` with a different algorithm than production

`database/factories/SkillFactory.php:24` computes
`mb_strtolower(str_replace('-', '', Str::slug($name)))`. Production computes it with
`NameNormalizer::normalize()` (NFKD, strip combining marks, strip five specific characters). The
two disagree on punctuation, and the factory is the one that drops it.

Measured over 200 sampled client-named skills: **119 produce a different key**. Examples:

| Name | Factory key | Normalizer key |
|---|---|---|
| `Warning Shot!` | `warningshot` | `warningshot!` |
| `Empress's Pride` | `empressspride` | `empress'spride` |
| `1st Place Kiss☆` | `1stplacekiss` | `1stplacekiss☆` |
| `Class Rep + Speed = Bakushin` | `classrepspeedbakushin` | `classrep+speed=bakushin` |

Over 135 trainee names, 3 differ (`Mr. C.B.`, `K.S.Miracle`, `Curren Bouquetd'or`).

So a search test that seeds a skill through the factory is testing a key that production never
writes. This is the same class of defect as `KI-23b`, where a factory-seeded `name_ja` meant the
parser's wrong key stayed invisible: the fixture agreed with the code because both were wrong in
the same way. Filed as **KI-39**.

### 6.3 `NameNormalizer`'s fold has a Unicode ceiling

`normalize()` folds by NFKD, then removes combining marks, then removes exactly five characters
(`NameNormalizer::FOLDED_CHARACTERS`). NFKD does not decompose the Latin ligature letters or the
stroked letters, and none of them are in that list, so they survive into the match key:

| Pair | Result |
|---|---|
| `Cafe` / `Café` | match |
| `El Condor` / `El Cóndor` | match |
| `Tokai` / `Tōkai` | match |
| `Straights` / `Stræight` | **no match** |
| `Odawara` / `Ødawara` | **no match** |

Severity is low today and the measurement is what makes that claim rather than an assumption:
`æ` appears 375 times in the export, but only in `name_tw` (144 rows), `title_tw` (81), `title_jp`
(45) and `title_ko` (4) — **none of which this tool stores or searches**, and `ø`, `đ`, `ł`, `þ`,
`ß`, `œ` appear **0 times**. No `[Global]` English name currently folds wrong.

It is filed because the failure mode is invisible when it arrives: a name that fails to match reads
as "no such trainee", not as "our normalizer does not cover this letter". Filed as **KI-40**.

### 6.4 The selector's ordering is not a total order

`TrainingRunController::create()` orders the roster with `->orderBy('name')` and no tiebreaker
(`app/Http/Controllers/TrainingRunController.php:82`). Measured: 0 duplicate names, and 135
distinct names across 135 trainees, so the order is total **in fact today**. It is not total **by
construction**, and the tiebreak would be SQLite's row order, which can change across a
re-import. Latent, not live. Filed as **KI-41**.

---

## 7. Browser pass

Served `database/scratch-catalog.sqlite` (a copy) on port 8099 via `php artisan serve`. The copy
was made only after the population guard, and the shared `database/database.sqlite` was re-checked
afterwards and still held 135 trainees and 107 cards. The server was stopped by PID and the port
confirmed closed (`Unable to connect to the remote server`) before the gates ran.

### 7.1 Selector, `http://127.0.0.1:8099/training-runs/create`

| Input | Observed | Expected | |
|---|---|---|---|
| (empty) | 10 options, `10 of 107 (keep typing)`, newest first, grouped by trainee | same | pass |
| `F` | 10 options, `10 of 14 (keep typing)` | see §5.3 | deviation |
| `Fe` | 0 options, "No trainee or card found." | same | pass |
| `Fenomeno` | 0 options, "No trainee or card found." | same | pass |
| `Fu` | Fuji Kiseki group with `[Shooting Star Revue]`, `[Succès Étoilé]`; also Agnes Digital `[Full-Color Fangirling]` | Fuji Kiseki + her forms | pass |
| `RUN` | `[RUN! RUIN! LAUNCHER!]` and `[Run & Win]` | the former under Gold Ship | pass |
| `fenomeno` | 0 options | same as `Fenomeno` | pass |
| `スペシャル` | 3 options, `3 matches`, one group `Special Week スペシャルウィーク` | Special Week + her three forms | pass |
| `zzz` | 0 options, "No trainee or card found." | same | pass |

Screenshot: `docs/design-research/verification/task13-f-query.png` (the `F` query with its
`10 of 14` line and the trainee group headers).

### 7.2 Keyboard and ARIA (G-11)

| Path | Observed |
|---|---|
| ArrowDown ×1 from open | `aria-activedescendant` → `trainee-option-8` |
| ArrowDown ×2 | → `trainee-option-73` |
| ArrowUp from the first option | wraps to the **last** (`trainee-option-99`) |
| ArrowDown past the last | wraps to the **first** (`trainee-option-107`) |
| Enter | label becomes `Fuji Kiseki · [Succès Étoilé]`, `aria-expanded` → `false`, and **both** hidden fields set: `umamusume_id=5`, `character_card_id=73` |
| Escape | popup closes, `aria-expanded` → `false`, **input value left as typed** |
| Focus with no click | `aria-expanded` → `true`, 10 options rendered |

Attributes on the input: `role="combobox"`, `aria-controls="trainee-listbox"`,
`aria-autocomplete="list"`, `aria-activedescendant` tracking and flipping correctly.

The no-script path is intact: the original `<select name="umamusume_id">` with all 68 Global
trainees remains in the DOM, disabled by the script when the script runs.

### 7.3 Catalog and selector cannot disagree

| Check | Result |
|---|---|
| Selector's option count vs catalog | both read the same 107 cards from the same payload query |
| H1-equivalent count | "Showing 1 to 10 of **68** results" — equals the export's distinct Global `char_id` |
| `?search=ruin` | HTTP 200, exactly 1 trainee (`Gold Ship`), 2 card titles |
| Debut leads every card list | Inari One renders `[Edomurasaki]` (debut, May 28 2026) before `[Golden Dream]` (Sep 7 2026) |
| Gold Ship order | `[Red Strife]` (Jun 26 2025) before `[RUN! RUIN! LAUNCHER!]` (Jul 2 2026), debut first |

### 7.4 C-6 against the new query shape

30 sequential requests to `/umamusume`, `Stopwatch` around each:

```
min    = 115.8 ms
median = 135.4 ms
p95    = 242.1 ms
max    = 329.2 ms
```

**Median 135.4 ms against C-6's 200 ms budget**, measured on `php artisan serve` — a single-worker
development server with no opcache, which is slower than any deployment this tool has. The honest
statement is the one the plan asked for: under budget at one-fifteenth the 1,000-row reference
size, with one eager card load per page of 10 trainees. **The 1,000-row budget is not claimed.**

---

## 8. Gates

Run in `CONSTRAINTS.md`'s order, on the merged tree.

| Gate | Result |
|---|---|
| `vendor/bin/pint --dirty --format agent` | `{"tool":"pint","result":"passed"}` |
| `vendor/bin/pint --test --format agent` | `{"tool":"pint","result":"passed"}` |
| `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | `[OK] No errors` |
| `php artisan test --compact` | **782 passed, 2 skipped**, 2715 assertions, 85.06s |
| `composer lore` | 115 hits, 57 exempt lines |
| `composer lore-code` | 8 hits |
| `composer audit` | No security vulnerability advisories found |
| `npm audit --omit=dev` | found 0 vulnerabilities |

`make lore` is unavailable on this host (GNU make absent — the `KI-4` gap the plan itself records),
so the lore gate was run as `composer lore`, which is the same `tools/lore.php` the Makefile target
wraps.

**The lore counts are unchanged by this merge, and that was checked rather than assumed.** All 8
`lore-code` hits are in `config/queue.php`, `config/scenarios.php` (4), `ReviewFormAccessibilityTest`,
`ReviewQueueTest`, `ShopPurchasePayloadTest` and `gametora-skills.sample.json` — none of which the
merge touched. The 5 merged files were separately grepped: `create.blade.php` and
`TraineeSelectorTest.php` are clean; the hits in `TrainingRunController.php`, `trainee-combobox.ts`
and the plan file are pre-existing prose and a pre-existing plan, not new copy.

**`skipped 2`, not `skipped 5`.** Recorded because the plan lists "the brief's `skipped 5`" among
the counts that failed to hold; the current suite skips 2, both Playwright-driver-gated.

### C-5, on the scratch file

```
migrate:fresh --seed   → UmamusumeSeeder, SkillSeeder, ScenarioSlotSeeder all DONE
migrate:rollback --step=3
  2026_09_29_120200_add_character_card_id_to_training_runs_table   44.90ms DONE
  2026_09_29_120100_create_character_cards_table                     4.05ms DONE
  2026_09_29_120000_add_external_ref_to_umamusume_table            15.68ms DONE
```

Re-migrated, then rolled back four to reach `ADR-0011`'s migration, whose `down()` is the only one
here that drops an index, a unique constraint and nine columns together:

```
  2026_09_29_021157_add_reference_fields_to_skills_table            62.80ms DONE
```

Scratch file deleted. Shared `database/database.sqlite` re-verified after: 135 trainees, 107 cards.

---

## 9. Not built

- `docs/frontend-review/` does not exist and was not created.
- `KNOWN-ISSUES.md` gained four entries (KI-38 to KI-41) and nothing else in it was rewritten.
- `docs/scenarios/**`, `docs/UMAMUSUME_REFERENCE.md`, `PLAN.md`, `Makefile` and
  `docs/GATE-REGISTRY.md` were not modified.
- No support-card surface exists or was implied. `ADR-0005` remains declined by R37.
- **No images, and no trainee detail-page fields.** `KI-33` (a trainee's own innate and unique
  skills are published by the source and stored nowhere) and `KI-35` (the detail page is a metadata
  stub) remain **open**. They are the subject of `docs/adr/0012-card-detail-fields-and-images.md`,
  which records the decisions and the measurements but widens no schema until the owner rules on it.

---

## 10. Open for the owner

1. **The em dash rule and `·`.** Should `DESIGN.md` gain an explicit `·` exception, or does the
   middle dot stay a local judgment call at each site?
2. **`pageSize` 25.** The catalog paginates at 10; the default was 25 before this slice. Should it
   rise now that a row is taller?
3. **KI-38, `[unsigned]`.** The source states that string. Options: treat a placeholder title as
   absent and exclude the card from name-search while keeping the row; flag it `unconfirmed`; or
   accept it as a dated snapshot of an upstream placeholder. The first is the smallest change that
   stops a Trainer seeing a placeholder as a costume.
4. **KI-39, the factory's `match_key`.** Fixing it means `SkillFactory` calling `NameNormalizer`,
   which will change keys in every test that seeds a skill. Cheap, and it removes a class of false
   green.
