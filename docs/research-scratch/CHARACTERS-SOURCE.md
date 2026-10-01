# Characters Source and Profile Block

## Provenance

This document consolidates the following source files verbatim (no summarization, no deduplication):

- `docs/requests/reports/2026-09-30-characters-source-findings.md`
- `docs/data/2026-09-30-characters-source-probe.md`
- `docs/requests/reports/2026-09-30-port-and-cleanup.md`

---

## 2026-09-30-characters-source-findings.md

### Characters source — findings for the profile-block slice

Date: 2026-09-30
Purpose: bank the re-derivation so the next implementer does not repeat it, and serve as the
Context section of the ADR for the §A profile block.
Status: **not merged, not a gate record.** Findings only. Every number below was measured on this
machine on 2026-09-30 against the live source, and each section names the document it came from.

Measurement method: `https://gametora.com/data/manifests/umamusume.json` (HTTP 200) resolved each
hash, then each document was fetched from `https://gametora.com/data/umamusume/<name>.<hash>.json`
and read with `ConvertFrom-Json`. Hashes in §7 are the manifest's values on 2026-09-30.

---

#### 1. The dataset holding the profile fields is `characters`, and it is not all Umamusume

`characters.c6676539.json` is 163 rows, 102,945 bytes. It is the **only** manifest dataset that
carries profile fields. The three other profile-shaped datasets do not (§5).

**The rows are two populations, and the discriminator is the `race` key.**

| `race` value | Rows |
|---|---|
| `uma` | 105 |
| `false` | 17 |
| `""` (present, empty) | 35 |
| `human` | 4 |
| `unknown` | 2 |

The 58 rows where `race` is not `uma` are the **real-world race animals the characters are named
after** — their own registry rows, with their own career records, in the same document. Sample of
the `char_id` / `en_name` / `race` triples: `1075` Daring Tact `""`, `1079` Furioso `false`,
`1088` Satono Crown `false`, `1090` Verxina `""`.

**Consequence, and it is the whole reason this note exists:** a parser that walks the row set
without filtering on `race` stores 58 real-world race animals into the Umamusume catalog. That is
a C-4 hard failure (a real animal's stud record on a humanoid character's page) *and* a data
integrity failure, and it is the natural implementation — the existing `GametoraCharacterParser`
filters rows by `name_en` and `char_id` only, and this document satisfies both for all 163 rows.

The filter is `race === 'uma'`, giving 105 characters. Note that the string `"uma"` is source data
in the filter expression (C-4 class 3) and must never be promoted into copy or a UI string.

#### 2. The five fields, by their real names

The brief's field names (`name_jp`, `voice_actor`, `voice_actor_en`, `birthday`, `sizes`) **do not
exist in this document.** The actual keys:

| Profile field | Source key | Type | Notes |
|---|---|---|---|
| Japanese name | `jp_name` | string | 105/105 within the `uma` subset |
| Voice actor (JP) | `va_ja` | string | 105/105 |
| Voice actor (EN) | `va_en` | string | 102/105 |
| Birthday | `birth_year` + `birth_month` + `birth_day` | int ×3 | 98/105; three columns, not one date string |
| Height | `height` | int, cm | 105/105 |
| Three sizes | `three_sizes` | object `{b, h, w}` | 98/105; **all three subkeys always present together** — never partial |

Two more keys exist and are **not** profile fields: `va_link` (137/163) and `va_ko` / `va_zh_tw`
(localisation). `jp_name_real` appears on 3 rows and is a separate thing from `jp_name`.

`three_sizes` completeness was checked per row, not per key: of the 127 rows that carry the object,
**127 have all of `b`/`h`/`w` non-null and 0 have any subkey equal to zero.** There is no partial
shape to guard against.

#### 3. Null coverage, and why the brief's reading of it was wrong

The five numbers reproduce exactly as **non-blank counts out of 163 total rows**:

| Key | Non-blank / 163 |
|---|---|
| `birth_year` | 146 |
| `three_sizes` | 127 |
| `va_en` | 137 |
| `height` | 163 |
| `va_ja` | 163 |

Read as "sparse profile data", this is wrong. The gaps are the 58 non-`uma` rows, not missing
Umamusume data. Restricted to the 105 `uma` rows the coverage is near-complete:

| Key | Non-blank / 105 |
|---|---|
| `jp_name` | 105 |
| `va_ja` | 105 |
| `height` | 105 |
| `va_en` | 102 |
| `birth_year` | 98 |
| `three_sizes` | 98 |

**The 3 `birth_year` and 3 `va_en` gaps inside the `uma` subset are the only real coverage gaps in
this slice.** Every other absence is a row that was never a Umamusume. The schema should carry
these as nullable and the parser should treat "absent" as `null`, but the store's completeness
expectation is 105/105, not 146/163.

#### 4. The banned keys, and the exposure is broader than stated

Confirmed on the live document, all 163 rows unless noted:

| Key | Present | What it is |
|---|---|---|
| `rl` | 163 | Nested object: `record` (`10-4-2-1`), `wins`, `races`, `earnings`, `country`, `active` (a year range), and `death` on 90 rows. A full career and death record for a real animal. |
| `sex` | 163 | Integer `1` (119) or `2` (44). Within `uma`: 83 / 22. |
| `race` | 128 | The population discriminator (§1). Not a lore problem in itself; a correctness gate. |
| `tid` | 163 | String. |
| `url_name` | 163 | String, e.g. `special-week`. The brief's "maybe needed for URL construction later" — not needed, `char_id` is the key. |
| `active` | 155 | Boolean. |
| `active_en` | 94 | Boolean. |

`rl` is the hard one, and the brief understated it: it is not on one row, it is on **every** row,
and it is a nested object, so a naive `$row` projection carries the entire career record plus a
death date for every character. `rl.death` exists on 90 of 163.

**The ban is therefore wider than `rl`/`sex`/`race`.** Per the brief's own rule — store nothing from
this row beyond the five fields — the parser's output must be exactly five keys, by name, and the
store action must project by name onto a five-column schema. A test feeding a body containing
`rl`, `sex`, `race`, `tid`, `url_name`, `active`, `active_en`, `va_link`, and the localisation name
keys must assert that no emitted key is derived from any of them. That is the guard; a comment is
not.

#### 5. The other three profile-shaped datasets

All three were fetched and read.

| Manifest key | Rows | Shape | Carries the five fields? |
|---|---|---|---|
| `characters_extended` | 934 | `{char_id, name_en, name_ja}` | no — **and see §6, the brief is wrong about this one** |
| `char_profiles` | 173 | `{char_id, en, ja, ko, zh_tw}`, `en` is prose | no |
| `character_profiles` | 173 | same shape | no |

`char_profiles` and `character_profiles` are **the same dataset at two `secrets` shapes**, not two
datasets: identical `char_id` sets (verified element-wise equal), identical `en` subkeys
(`class, dorm, ears, family, secrets, self_intro, shoes, strong, tagline, tail, weak, weight`), and
103 of 173 rows differ only in `secrets` — a bare string in one, an `{id, text}` object in the
other. One of the two should be chosen deliberately; do not import both.

Both have **173 rows against the catalog's 163**, and the 10 surplus `char_id`s (`1142`, `1148`,
`2004`–`2008`, `9007`, `9045`, `9051`) have no row in `characters` at all. Import is a left join
with a skip, never an upsert that creates characters.

#### 6. `characters_extended` is a hard C-4 trap, not an alias source

The brief recorded this as "934 rows of `{char_id, name_en, name_ja}` alternate names, exactly
what the aliases section wants, currently near-empty" and recommended it as a follow-up for
`umamusume_aliases`. **That recommendation is wrong and must not be actioned.**

Measured: 934 rows, `char_id` range `9990001`–`9990934`, all 934 in the `9xxxxxx` namespace, and
**zero overlap with the 163 `characters` `char_id`s.** The rows are a registry of the real-world
race animals, under their own identifiers: `9990001` Asahi Creek, `9990002` Offside Trap,
`9990003` Val's Prince, `9990004` Sunny Brian, `9990006` Let's Go Tarquin.

So it is not alternate names for Umamusume. Populating `umamusume_aliases` from it would attach a
real animal's name to a humanoid character's page — the exact C-4 failure this run has now found
twice, in a form the brief described as a win. **Record as banned, alongside `rl`.** The aliases
section stays empty; the correct source for real alternate names is the `ja`/`ko`/`zh_tw` prose in
`char_profiles`, if it is ever needed.

#### 7. Manifest resolution, and one path correction

Hashes read from `https://gametora.com/data/manifests/umamusume.json` on 2026-09-30:

| Key | Hash | Path that answered |
|---|---|---|
| `characters` | `c6676539` | `data/umamusume/characters.c6676539.json` |
| `characters_extended` | `6342c36b` | `data/umamusume/characters_extended.6342c36b.json` |
| `char_profiles` | `025e03a7` | `data/umamusume/char_profiles.025e03a7.json` |
| `character_profiles` | `49aa6e38` | `data/umamusume/character_profiles.49aa6e38.json` |
| `meta/char_profile_art` | `fc64c1d0` | `data/umamusume/meta/char_profile_art.fc64c1d0.json` — **subdirectory, the flat path 404s** |
| `static/character_rl_details` | `5e916b34` | `data/umamusume/static/character_rl_details.5e916b34.json` |

The last two are not in the brief. `character_rl_details` is 148 rows of
`{char_id, jbis_slug, nk_slug, offspring, pedigree, race_history, siblings}` with a three-deep
parent chain in `pedigree` — another dataset no slice should read. Both are recorded here so the
next implementer does not "discover" them by fetching.

#### 8. §C trigger observed, not acted on

`meta/char_profile_art.fc64c1d0.json` — 170 rows, 15,952 bytes, keys `images` and `url_name`.

`images` is a **boolean map, not a URL map**: `{"concept": true, "racing": true,
"starting-future": true, "uniform": true}`. It records which art slots exist for a character; it
carries no image address. The brief's read of this as a source of per-character art does not hold
on its own — the addresses are somewhere this document does not point at.

Reported, not built, per the §C instruction. It does not overturn ADR-0012 Decision 2: the grain is
`url_name` (char-grain, no `card_id`), so it cannot serve per-form art. A per-character portrait on
a trainee page remains a real feature and a separate decision, and this document does not
unblock it on its own. Recorded as a re-decision input.

#### 9. Corrections to the incoming brief

1. **Field names are wrong** (§2). `jp_name`, `va_ja`, `va_en`, `birth_*` ×3, `height`,
   `three_sizes`. None of the brief's six names exist on the row.
2. **Birthday is three integer columns**, not a date string, and `va_ja`/`va_en` are two columns,
   so "five fields" is **seven columns**.
3. **The null coverage means the opposite of what it was read as** (§3). Expect 105, not 163.
4. **`characters_extended` is a real-animal registry, not aliases** (§6). The brief's follow-up
   recommendation is a C-4 failure if actioned.
5. **`rl` is on all 163 rows, not one**, and it is nested (§4).
6. **The filter that §A actually needs is `race === 'uma'`** (§1), which the brief does not
   mention at all. Without it the slice stores 58 real animals.
7. **`char_profiles` and `character_profiles` are one dataset at two shapes** (§5), with 10
   `char_id`s that have no character row.
8. **The manifest holds six relevant datasets, not four** (§7), and `meta/` and `static/` are
   subdirectory paths.

The brief's `release_en` correction stands and is stronger than stated: `characters` carries **no**
`release`, `release_en`, `debut`, or `first_release` key on any of its 163 rows. That field is not
available at this grain at all.

#### 10. What the parser must therefore do

Stated here so the ADR can point at it rather than restate it.

1. Filter to `race === 'uma'` before anything else. 163 in, 105 out.
2. Emit exactly the five field groups, by name, and nothing else.
3. `birth_*` → one date or three nullable ints, per the schema decision; `three_sizes` → three
   nullable ints, written only when the object is present.
4. Key on `char_id`. `url_name` and `tid` are not needed for anything in this slice.
5. Test: a body carrying `rl` (nested, with `death`), `sex`, `race`, `tid`, `url_name`, `active`,
   `active_en`, `va_link`, `va_ko`, `va_zh_tw`, `name_ko`, `name_tw` must produce output with no
   key derived from any of them.
6. Store action projects by name onto the five-column schema, the way `StoreCharacterCards` does.

#### 11. Still owed, and still blocking

Unchanged by this note:

- `docs/data/` is absent. Unresolved.
- Task 8 writes to the same shared index and is the plan's last never-run piece. Unresolved.

Order when Task 8 reports: cardless band first (owed, small), then the detail page on a fresh
branch off this tree with §A as five fields — seven columns — and the allowlist enforced by test.

---

## 2026-09-30-characters-source-probe.md

### The `characters` source, re-resolved — a probe, not a fetch

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

#### Manifest

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

#### `characters.json` (manifest hash `c6676539`) — HTTP 200, 102,945 bytes, 163 rows

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

##### Coverage, re-scoped to the 105 trainee rows the parser keeps

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

##### What this corrects in the brief

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

##### What `va_ja` and `va_en` actually are

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

##### The `rl` allowlist obligation, and where it is tested

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

#### §C, closed with a measurement rather than a search

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

#### What §A can be built from

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

---

## 2026-09-30-port-and-cleanup.md

### Port three artifacts, withdraw ADR-0013, delete the branch — run report

**verified-against:** `a4da6d1` (master tip at the close of this run). All counts re-derived against
the on-disk saved body `research-scratch/data/json/characters.json` (manifest hash `c6676539` at
measurement), not a live fetch — see §6.

#### 1. Preconditions

| # | Check | Result |
|---|---|---|
| 1 | `git fetch`; divergence at start | `behind=0`, `ahead=21`. No fast-forward (origin/master had not moved past local). |
| 2 | `git rev-parse HEAD` at start | `85b37a40e3bdc93e059bf9ec00c4d3a6ac53d95a` |
| 3 | `php artisan test --compact` | green: 826 passed, 2 skipped, 0 failed (baseline, with the peer's tree changes present) |
| 4 | `git status --short` clean | **FAILED on first check** — 5 tracked files carried an uncommitted peer changeset, including a PRD A-7 re-asserting the `va_en` defect Step 1 fixes. |
| 5 | branch exists | `feat/umamusume-detail-page` = `8ffab63`, checked out in worktree `D:/Projects/umamusume-laravel13-catalog-roster`. |

Precondition 4 was surfaced to the human owner before any edit. Disposition chosen: **commit the peer's
work as its own preservation commit, then run the sequence.** The 5-file changeset (KNOWN-ISSUES, PLAN,
PRD, ADR-0012, SkillAutomationTest — the brief said "four files"; it is five) was last written 04:30–04:54,
idle ~2h, and I verified none of its hunks lands `e22d05e`'s "withdraw Decision 4" direction onto master
before committing it.

#### 2. Commits landed (oldest → newest)

| SHA | Step | What |
|---|---|---|
| `71fabfc` | (pre) | preserve the pre-existing 5-file peer changeset |
| `5774a8b` | 1 | `va_en` docblock correction + characterization pin |
| `88d2830` | 2 (net-new) | `race === 'uma'` filter + test moves |
| `3bac088` | 5 | PRD A-7 `va_en` reading + uma-scope |
| `d6670d8` | 3 | port store-side allowlist guard + all-null coercion (parser-side already covered) |
| `4ba2962` | 2 (artifact) | port the probe record + correct the migration `va_en` comment |
| `5a50900` | 4 | withdraw ADR-0013, land on master |
| `a4da6d1` | 7 | two PLAN.md exit criteria (checkpoint, collision) |

#### 3. The `va_en` defect

Master's parser docblock claimed `va_en` is "the English dub cast" and `va_ja` the romanised cast, built
on the wrong denominator (163/26). The body refutes it: `va_ja` 和氣あず未 / `va_en` Azumi Waki / `va_ko`
와키 아즈미 is one performer in three scripts; `Machico`/`Lynn` rows hold the identical string across all
three. Absences are missing romanisations (3 of the 105 trainee rows), not missing dub credits.

Corrected in three surfaces, each as dated errata quoting the withdrawn claim (not a silent rewrite):
parser docblock (`5774a8b`), PRD A-7 (`3bac088`), and the **migration comment** (`4ba2962` — the migration
carried the identical wrong reading; Step 1 named only the docblock, I found it while porting and fixed it
so master holds one consistent reading across code, PRD and schema).

#### 4. RED-then-GREEN evidence — stated honestly, not staged

**The one genuine executable RED this run was the race filter,** not the `va_en` pin:

```
$ php artisan test --compact tests/Feature/GametoraCharacterProfileParserTest.php   # BEFORE the filter
Tests: 1 failed, 11 passed
  keeps only rows whose race is exactly "uma"...
    --- (6 expected: 1,2)
    + (6: kept char:3 race:false, char:4 human, char:5 unknown, char:6 no-key)

$ ... # AFTER `race !== 'uma' -> return null`
Tests: 12 passed (43 assertions)
```

The **`va_en` pin** and the **two ported guards** (store-side refused-key drop, all-null coercion) are
**characterization tests: green by construction against master's already-correct parser/store, red on a
regression.** A docblock correction has no executable RED (the code was right; only the comment was
wrong), and re-derivation says so rather than performing a fake red-green. That the brief listed them
under "RED-then-GREEN evidence" is a premise that did not hold — flagged, not papered over.

#### 5. The race filter — net-new behavior, not a port

A-7 promises the profile is "sourced from the `characters` document filtered to `race === 'uma'`."
**Neither master nor the branch implemented this** — both lean on the ten-key field allowlist and iterate
all 163 rows. So the filter is behavior the brief did not ask for and the port made real: without it,
`StoreCharacterProfiles` would attach a profile block — and, since 78 of 105 trainee rows carry a non-null
`rl.death`, potentially a death record — to real-world namesake rows. Measured: 105 `uma`, 58 refused
(17 `false`, 4 `human`, 2 `unknown`, 35 key-absent).

Blast radius (all repaired, suite green after): the filter drops fixture row 1095 "Believe" (`race:false`),
which five master tests had been using *as a trainee* — including `StoreCharacterProfilesTest:121`
("stores a null" via `external_ref=gametora:char:1095`). That test was built on the unfiltered-parser bug;
1095 is exactly the namesake the filter refuses. Repointed to 9040 "Darley Arabian" (genuine `uma` row with
absent `va_en`), preserving the "store the null when absent" intent. `git grep 1095 HEAD -- tests/` after
the change confirms no other profile test depends on it (remaining hits are the fixture row + the
intentional refusal assertions; the `1007`/`9044` hits are card-domain, different grain).

Per the human's decision, fixture row 1095 is **kept as the in-fixture refused case** — the `resolves
every trainee row` count 4→3 assertion documents the filter itself.

#### 6. Count reconciliation

**No count failed to reproduce.** Both denominators verified against the saved body (live re-run):

- file-wide (163): `va_en` 137/26, `birth_year` 146/17, `three_sizes` 127/36, `rl.death` 90, race-histogram
  uma 105 / false 17 / human 4 / unknown 2 / none 35, `playable_en` 68/95, `jp_name_real` 3/160 — all match.
- uma-scoped (105): `va_en` 102/**3**, `birth_year` 98/**7**, `three_sizes` 98/**7**, `rl.death` **78 = 74.3%**,
  8 distinct key sets, top-level (excl `rl`) nulls/empties = 0 — all match.

The correction is **denominator, not measurement**: the branch's coverage figures are true of the whole
document but the parser keeps only the 105 trainee rows, so the operative gaps are 3/7/7, not 26/17/36.
Re-scoping to 105 removes the false implication that the trainee profile is materially sparse. (The brief's
own `characters.c6676539.json` path is stale on disk — it is `characters.json` — corrected in the probe. No
banned C-4 term is typed in any changed file; the ten-key docblock lead uses "real-world namesake.")

#### 7. The three ports — and why the third artifact is a placement, not a branch edit

1. **Probe record** — ported to `docs/data/2026-09-30-characters-source-probe.md` with the path correction,
   the saved-body/rotation disclosure (a live re-fetch may diverge; same shape as the roster `e9e9ee6d`
   correction — the source rotated once before, `679f7c2e`→`e9e9ee6d`), the `n=105` re-scope, the 8-key-set
   finding, and the `rl` obligation restated as a **tested parser+store guard** pointing at the now-landed
   master tests.
2. **Tests** — ported only the two that are new (store-side refused-key drop; all-null coercion). The
   **parser-side mutation probe was NOT ported**: master's 11-key projection pin and the Step-2
   refused-namesake fixture already prove what the parser must not emit. Duplicating would inflate
   coverage without adding a check. All ported names re-bound to master's schema (`height` not `height_cm`,
   `three_sizes_*` not `bust/waist/hip_cm`).
3. **ADR-0013** — withdrawn and placed **directly on master**, not edited on the branch then copied: the
   source branch is deleted in §9, so only the durable branch matters and copying-then-deleting would lose
   the edit. The withdrawal names where each piece goes (field inventory + coverage → ADR-0012 Decision 4 +
   probe; schema decision moot because master's `182820` landed first; drafted FR-A-7 landed as `A-7`). The
   file body is kept as a record; nothing deleted.

#### 8. Contradictory records found — quarantined, not reconciled

`e22d05e` ("supersede ADR-0012 Decision 4 **in favour of** ADR-0013") and `47a60bb` ("record
feat/catalog-detail-page as superseded") live on **`feat/catalog-detail-page`** — a *different* branch from
the deletion target, holding a disposition **opposite** to what this brief's Step 4 executed. Both survive
the deletion untouched (confirmed `git branch --contains`). They are left as a record of a moment an earlier
session believed the reverse; reconciling them would require a decision outside this brief's scope, and
neither this nor the implementer can signal the peer session from inside the worktree boundary. `e22d05e`'s
marker **supersedes/withdraws** Decision 4 (its first line reads "SUPERSEDED — withdrawn. See ADR-0013") —
not annotates it. On master the opposite holds: **Decision 4 is the authority, ADR-0013 withdrawn.**

#### 9. Branch deletion + recovery

`feat/umamusume-detail-page` had **10 commits** not on master, all unpushed (no `origin/` tracking) and
unmerged — so `-D` discards irreversibly. Recovery SHA if needed before reflog expiry:
`8ffab6363492e81f50043f90460f2909f94a3767`. Worktree `D:/Projects/umamusume-laravel13-catalog-roster` was
**clean (0 changes)** and removed first; the branch then deleted. Confirmed gone. `feat/catalog-detail-page`
and `feat/catalog-roster-and-trainee-selector` both **INTACT** (never deletion targets).

#### 10. Open gaps flagged, not silently dropped

- **Profile `down()` rollback is unproven on master.** The branch had a rollback test
  (`CharacterProfileTest.php:183`, in-memory `profile_rollback` connection) proving `migrate:rollback` drops
  only `umamusume_profiles`. It is not one of the three named artifacts and the brief said not to port the
  branch's other tests, so it was **not** ported. Master's migration has a `dropIfExists` `down()` with no
  test. Recorded as a coverage gap for the owner, not hidden by the port.
- **Live re-verification of the saved body** was deliberately not done (source-rotation risk; a divergent
  re-fetch would break the probe's reproducibility). It is its own future task with its own record.

#### 11. Which port was hardest

The **test port**, not the probe or the ADR. The probe was text re-scoping (mechanical once measured) and
the ADR was a placement. The test port required, per test: a duplication verdict against master's broader
coverage (rejecting the parser-side probe as already covered), name re-binding across two divergent schemas,
and catching that `StoreCharacterProfilesTest:121` was built on the unfiltered-parser premise — a real
defect the filter exposed, where the "obvious" fix (just change 4→3) would have left a test asserting on
refused data. Making it *correct* rather than *green* was the work.

#### 12. Final gate evidence (tree `a4da6d1`)

```
Tests:    2 skipped, 830 passed (2890 assertions)      # full suite
[OK]    No errors                                       # phpstan level 6
pint --test: passed                                     # C-3
lore-code: 8 hit(s)   lore-docs: 116 hit(s), 57 exempt  # == baselines, no new banned-term hit
tsc --noEmit: exit 0                                    # C-9 (no .ts touched this run)
ahead=29 behind=0 vs origin/master                      # 8 commits landed, all local
```