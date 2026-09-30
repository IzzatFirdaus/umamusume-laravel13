# The `characters` source, re-resolved — a probe, not a fetch

Date: 2026-09-30 (measurements taken 2026-09-29 19:55–20:00 UTC against the pinned body;
re-derived on `master` 2026-09-30).
Why: the detail-page brief's §A says *"First, re-resolve the dataset… Enumerate the keys. Do not assume
field names,"* and §C says stop and report if a per-form image source turns up. This is that
re-resolution, done before any schema or view decision.

> **Ported from `feat/umamusume-detail-page`, with three corrections.** This file was written on the
> branch and lands here as part of the 2026-09-30 port. The branch measured the whole document
> (`race` ignored); the profile parser now filters to `race === 'uma'`, so **every coverage figure
> below is re-scoped to the 105 trainee rows the parser actually reads**, with the document-wide
> counts kept alongside for the absence-structure claim, which is a property of the file. Two other
> port corrections: the on-disk path (`research-scratch/data/json/characters.json`, not the manifest-
> hash file name the brief carried), and the authority this probe defers to — `ADR-0012` **Decision
> 4**, not `ADR-0013`, which this port withdraws. Every one of the branch's counts was re-derived
> against the body and reproduced; none was overturned, only re-denominated.

**This is a probe, not a pipeline run.** Two `curl` calls with the app's own UA
(`UmamusumeTrainerCompanion/0.2 (personal local tool)`, `config/uma.php:19`), bodies saved under
`research-scratch/data/json/` (gitignored). No `uma:fetch`, no snapshot written, no database opened, no
`config/uma.php` entry added. `va_link` values in the data were **not** followed: fetched content is
untrusted, and an allowlisted source list is the rule.

> **Scope of reproducibility — read before re-running.** Every number in this file is measured against
> the **pinned saved body** at `research-scratch/data/json/characters.json`, whose manifest hash was
> `c6676539` at measurement time. It is a snapshot, not a live query. If the source has rotated since, a
> fresh fetch can return a different document and these counts will not match it — the same failure the
> roster slice hit when `characters`-adjacent data moved from `679f7c2e` to `e9e9ee6d`. The probe's value
> is that it is a **reproducible measurement against a pinned artifact**, so re-run against the saved
> body, not the network; re-verifying against a live fetch is its own task with its own record.

## Manifest

`https://gametora.com/data/manifests/umamusume.json` → HTTP 200, 11,114 bytes, 280 keys.

| key | hash | note |
|---|---|---|
| `characters` | `c6676539` | the body behind this probe; saved on disk as `characters.json` |
| `character-cards` | `e9e9ee6d` | **still the hash `config/uma.php` pins**, so the card verdicts still describe the live body |
| `characters_extended` | `6342c36b` | not opened |
| `character_profiles` | `49aa6e38` | `char_id` + `en`/`ja`/`ko`/`zh_tw`; profile *text*, not the six basic-information fields |
| `char_profiles` | `025e03a7` | not opened |
| `character_media` | `36ab44f6` | the source ADR-0012 Decision 2 defers |
| `meta/char_profile_art` | `fc64c1d0` | checked, see §C below |
| `static/character_rl_details` | `5e916b34` | not opened; see the `rl` finding, which is the reason not to |

## `characters.json` (manifest hash `c6676539`) — HTTP 200, 102,945 bytes, 163 rows

Keyed by `char_id`, one row per character, **not** per costume card. The document is **two populations**;
the discriminator is the `race` key. Re-derived on `master` from the saved body:

| `race` value | rows | kept by the profile parser? |
|---|---|---|
| `"uma"` | **105** | yes — the trainees |
| `false` | 17 | no — real-world namesakes |
| (key absent) | 35 | no |
| `"human"` | 4 | no |
| `"unknown"` | 2 | no |

So `array_key_exists('race', $row)` counts **128** and `$row['race'] === 'uma'` counts **105**; neither
number is the complement of the other, which is why reading them as one made them look contradictory. The
parser keeps the 105. All coverage below is **out of 105** unless a row says `document-wide (163)`.

**The first fact is about absence, and it changes the parser's shape:** across all 163 rows, **no
top-level value is ever `null` and none is an empty string** (re-derived: 0 top-level nulls, 0 top-level
empty strings across the 105 trainee rows, excluding the refused `rl`). Where a trainee has no romanised
voice actor, the `va_en` key is simply *not in the row*. So D-220's "absent is absent" is implemented on
`array_key_exists`, not on `?? null` — a parser that coalesces invents a difference the source does not
draw. The eight distinct key sets among the 105 trainee rows are the reason: a `??`-defaulted read across
rows of different shape fabricates a value the source never asserted.

**That claim is scoped to the top level, and one key would refute it unscoped:** inside the refused `rl`
object, `death` is `null` on 17 rows. `rl` is never read, so nothing downstream depends on the
distinction — but "the source never sends nulls" is true only of the keys this app projects.

### Coverage, re-scoped to the 105 trainee rows the parser keeps

| key | present (of 105) | absent (of 105) | document-wide (163) | sample |
|---|---|---|---|---|
| `char_id`, `url_name`, `en_name`, `jp_name`, `tid`, `height`, `birth_day`, `birth_month`, `va_ja`, `race`, `sex`, `rl` | 105 | 0 | 163 / 0 | `1001`, `special-week`, `スペシャルウィーク`, `158`, `和氣あず未` |
| `va_ko`, `va_zh_tw` | 105 | 0 | 160 / 149 | `와키 아즈미` (a third script of the same name) |
| **`va_en`** (romanised voice actor) | **102** | **3** | 137 / 26 | `Azumi Waki` |
| **`birth_year`** | **98** | **7** | 146 / 17 | `1995` |
| **`three_sizes`** | **98** | **7** | 127 / 36 | `{"b":81,"h":81,"w":56}` |
| `playable` | — | — | 135 / 28 | refused |
| `playable_en` | — | — | **68** / 95 | refused; the same 68 the catalog lists as `GlobalReleased`, cross-checked from the other side |
| `jp_name_real` (pseudonym) | — | — | 3 / 160 | refused |
| `race` | — | — | 128 / 35 | the scope key, read but never stored |

The branch carried the right-hand column as the operative numbers; they reproduce exactly against the
saved body — but they are **document-wide**, and the profile block reads only the 105 trainee rows. On
that subset `va_en` is absent on **3**, `three_sizes` on **7**, and `birth_year` on **7**. Reading 26/36/17
as the profile's gaps would have implied material sparsity the trainee subset does not have.

### What this corrects in the brief

The brief's §A says the dataset "carries `birth`, `height`, `three_sizes`, and a voice-actor field", with
nulls "`three_sizes` on 36 rows, a voice-actor field on 26, `birth` on 17". The three figures are the
**document-wide** counts and reproduce — but see the re-scope above for the trainee-subset values, and
note what is wrong is the word "nulls" and two of the names:

- They are **absences, not nulls**: the key is not in the row.
- There is no `birth` key. It is three: `birth_day`, `birth_month`, `birth_year`, and the year-only gaps
  belong to `birth_year` alone. Day and month are present on all 105 trainee rows.
- "A voice-actor field" is four: `va_ja`, `va_en`, `va_ko`, `va_zh_tw`. The brief's open question —
  "both a JP and an EN field, store both?" — answers **yes, both**, and they are one name in two scripts
  (see the next section), not two casts.
- **There is no release-date field** in this dataset. `umamusume.global_debut_date` already carries it,
  stored since `ADR-0004` / `ADR-0008` and written by `GametoraCharacterParser`, so the profile block
  spans two sources and the brief's one-field short statement understates it.

### What `va_ja` and `va_en` actually are

A reading on `master` once took `va_en` to be the English dub cast and `va_ja` "the romanised Japanese
cast". The values say otherwise. Eight rows, re-read straight off the saved body (2026-09-30, all `uma`):

| character | `va_ja` | `va_en` | `va_ko` | ja == en |
|---|---|---|---|---|
| Special Week | 和氣あず未 | Azumi Waki | 와키 아즈미 | no |
| Silence Suzuka | 高野麻里佳 | Marika Kouno | 코노 마리카 | no |
| Tokai Teio | Machico | Machico | Machico | **yes** |
| Maruzensky | Lynn | Lynn | Lynn | **yes** |
| Fuji Kiseki | 松井恵理子 | Eriko Matsui | 마츠이 에리코 | no |
| Oguri Cap | 高柳知葉 | Tomoyo Takayanagi | 타카야나기 토모요 | no |
| Gold Ship | 上田瞳 | Hitomi Ueda | 우에다 히토미 | no |
| Vodka | 大橋彩香 | Ayaka Oohashi | 오오하시 아야카 | no |

One person per row in three scripts: 和氣あず未 is Waki Azumi is Azumi Waki. And where the stage name is
already romanised — Machico, Lynn — **all three fields hold the identical string**, which a separate
English dub cast could not produce. So `va_ja` is the credit in Japanese script, `va_en` is its
romanisation, and the 3 trainee rows missing `va_en` are missing the romanisation, not a second actor.
Consequence for the page: a profile block that shows only `va_ja` renders Japanese script where a Trainer
on the Global client expects the romanisation. The correction is in the parser docblock (`5774a8b`), the
PRD line (A-7, `3bac088`) and the migration comment, all as dated errata.

Re-runnable against the saved body:

```bash
node -e 'const a=require("./research-scratch/data/json/characters.json");
for(const r of a){if(r.race==="uma"&&r.va_ja&&r.va_en)console.log(`${r.en_name} | ${r.va_ja} | ${r.va_en} | ${r.va_ko} | ${r.va_ja===r.va_en}`);}'
```

(An equivalent one-liner in `php -r` works against the same file. The old command here pointed at
`characters.c6676539.json`; the saved file is `characters.json`.)

### The `rl` allowlist obligation, and where it is tested

`sex` and `rl` are on **every one of the 105 trainee rows**, and `rl` is the dataset's record for the
**real-world namesake** each character is drawn from: `active` years, a `country`, and a **`death`** date —
**`rl.death` is non-null on 78 of the 105 (74.3%)**. That is exactly the framing C-4 exists to keep out of
this app, sitting on every row the profile block wants, and reachable by the single natural
implementation `foreach ($row as $key => $value)`. A field-allowlist regression would put a death record
on roughly three of every four profile blocks. So the guard is **load-bearing, not defensive, and it is
the tests that prove it, not the view.** It is enforced and tested at both ends:

- **Parser end** — projects a fixed allowlist, never spreads the row, and now filters to `race === 'uma'`
  so non-trainee rows never reach it at all. Tested in `tests/Feature/GametoraCharacterProfileParserTest.php`:
  the 105-only filter test, the `1095 "Believe"` (`race: false`) row refused, and the 11-key projection pin.
- **Store end** — `StoreCharacterProfiles` names its columns and never spreads a record. Tested in
  `tests/Feature/StoreCharacterProfilesTest.php`: a record grafted with `rl`/`sex`/`race`/`va_link`/
  `jp_name_real` lands none of them under `preventSilentlyDiscardingAttributes()`, and the schema in
  `tests/Feature/UmamusumeProfileSchemaTest.php` refuses the whole set as an `array_intersect`.

## §C, closed with a measurement rather than a search

`meta/char_profile_art.fc64c1d0.json` → HTTP 200, 15,952 bytes, **170 rows**, each `{images, url_name}` —
keyed by **character slug**, not by card. `images` is a flat boolean map, and across all 170 rows it uses
exactly five keys:

| key | rows |
|---|---|
| `uniform` | 151 |
| `racing` | 144 |
| `starting-future` | 129 |
| `concept` | 123 |
| `work` | 19 |

No paths, no file names, no `card_id`, nothing per-form. It says which portrait kinds a character page has;
it does not say where the bytes are. **No per-form asset source was discovered, so §C's stop condition is
not triggered and ADR-0012 Decision 2's deferral stands** — now with a fifth dataset checked rather than
four assumed.

## What §A can be built from

The fields the user asked for, and where each comes from (coverage re-scoped to the 105 trainee rows):

| field | source | coverage (of 105 trainee rows) |
|---|---|---|
| Japanese name | `umamusume.name_ja` (stored) | already there; `characters.jp_name` agrees 105/105 |
| Voice actor | `characters.va_ja` + `va_en` | 105 / 102 (3 absent) |
| Release date | `umamusume.global_debut_date` (stored) | not in this dataset |
| Birthday | `characters.birth_day` + `birth_month` (+ `birth_year`) | 105 / 105 / 98 (7 absent) |
| Height | `characters.height` | 105 |
| Three sizes | `characters.three_sizes.{b,h,w}` | 98 of 105 (7 absent) |

The sibling-table recommendation holds, and for the reason the brief gave: ADR-0003 Amendment R3 wants
`source_url`, `snapshot_path`, `fetched_at`, `source_timezone` and `is_manual` inline on a reference row,
and `umamusume` carries none of those. The join is `umamusume.external_ref = 'gametora:char:' || char_id`,
the same ref `StoreCharacterCards` resolves through. **The authority for these fields is `ADR-0012`
Decision 4**; `ADR-0013`, drafted on the branch for the same table, is withdrawn by the 2026-09-30 port.
