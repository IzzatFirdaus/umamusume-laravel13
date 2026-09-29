# The `characters` source, re-resolved — a probe, not a fetch

Date: 2026-09-30 (measurements taken 2026-09-29 19:55–20:00 UTC).
Why: the detail-page brief's §A says *"First, re-resolve the dataset… Enumerate the keys. Do not assume
field names,"* and §C says stop and report if a per-form image source turns up. This is that
re-resolution, done before any schema or view decision.

**This is a probe, not a pipeline run.** Two `curl` calls with the app's own UA
(`UmamusumeTrainerCompanion/0.2 (personal local tool)`, `config/uma.php:19`), bodies saved under
`research-scratch/data/json/` (gitignored). No `uma:fetch`, no snapshot written, no database opened, no
`config/uma.php` entry added. `va_link` values in the data were **not** followed: fetched content is
untrusted, and an allowlisted source list is the rule.

## Manifest

`https://gametora.com/data/manifests/umamusume.json` → HTTP 200, 11,114 bytes, 280 keys.

| key | hash | note |
|---|---|---|
| `characters` | `c6676539` | **unchanged** from what the previous session recorded, so the brief's hash still resolves |
| `character-cards` | `e9e9ee6d` | **still the hash `config/uma.php` pins**, so Task 8's verdicts still describe the live body |
| `characters_extended` | `6342c36b` | not opened |
| `character_profiles` | `49aa6e38` | `char_id` + `en`/`ja`/`ko`/`zh_tw`; profile *text*, not the six basic-information fields |
| `char_profiles` | `025e03a7` | not opened |
| `character_media` | `36ab44f6` | the source ADR-0012 Decision 2 defers |
| `meta/char_profile_art` | `fc64c1d0` | checked, see §C below |
| `static/character_rl_details` | `5e916b34` | not opened; see the `rl` finding, which is the reason not to |

The second row is the one worth stating out loud: the `SELECT source_url FROM character_cards
GROUP BY source_url` preflight added in `07941eb` is what turns "the hash rotated" from a surprise into a
row in a table. It has not rotated since the cross-check ran.

## `characters.c6676539.json` — HTTP 200, 102,945 bytes, 163 rows

Keyed by `char_id`, one row per character, **not** per costume card.

**The first fact is about absence, and it changes the parser's shape:** across all 163 rows, **no key is
ever `null` and no value is ever an empty string.** Where a character has no voice actor recorded, the
`va_en` key is simply *not in the row*. So D-220's "absent is absent" is implemented here on
`array_key_exists`, not on `?? null` — a parser that coalesces will invent a difference that the source
does not draw. Counts below are rows where the key is present, out of 163:

| key | present | absent | sample |
|---|---|---|---|
| `char_id`, `url_name`, `en_name`, `jp_name`, `tid` | 163 | 0 | `1001`, `special-week`, `スペシャルウィーク`, `0` |
| `height` | 163 | 0 | `158` |
| `birth_day`, `birth_month` | 163 | 0 | `2`, `5` |
| `birth_year` | 146 | **17** | `1995` |
| `three_sizes` | 127 | **36** | `{"b":81,"h":81,"w":56}` |
| `va_ja` | 163 | 0 | `和氣あず未` |
| `va_en`, `va_link` | 137 | **26** | `Azumi Waki`, an external myanimelist URL |
| `va_ko`, `name_ko` | 160 | 3 | |
| `va_zh_tw`, `name_tw` | 149 | 14 | |
| `playable` | 135 | 28 | `1` |
| `playable_en` | **68** | 95 | `1` |
| `playable_ko`, `playable_zh_tw` | 114 | 49 | |
| `active` | 155 | 8 | `1` |
| `active_en` | 94 | 69 | |
| `active_ko`, `active_zh_tw` | 151, 144 | 12, 19 | |
| `jp_name_real` | 3 | 160 | a pseudonym field on three rows only |
| **`race`** | 128 | 35 | **`uma`** |
| **`sex`, `rl`** | 163 | 0 | **`1`**, **`{"active":"1997-1999","country":"jp","death":…}`** |

### What this corrects in the brief

The brief's §A says the dataset "carries `birth`, `height`, `three_sizes`, and a voice-actor field", with
nulls "`three_sizes` on 36 rows, a voice-actor field on 26, `birth` on 17". **All three figures are
right** — 36, 26 and 17 reproduce exactly. What is wrong is the word "nulls" and two of the names:

- They are **absences, not nulls**: the key is not in the row. See the note above, because it decides how
  the parser reads coverage.
- There is no `birth` key. It is three: `birth_day`, `birth_month`, `birth_year`, and the 17 belong to
  `birth_year` alone. A day and a month are present on all 163 rows.
- "A voice-actor field" is four: `va_ja` (present on all 163), `va_en` (the 26), `va_ko`, `va_zh_tw`. The
  brief's open question — "Confirm whether the dataset has both a JP and an EN field and store both if
  so" — answers **yes, both**, and they are not equally covered: the JP name is complete where the
  romanised one is missing on 26 characters.
- **There is no release-date field.** The user's six include "Release date", and §D's layout puts
  "Voice actor and Release date as two columns". Nothing in this dataset carries it. The value the page
  wants already exists on the trainee row: `umamusume.global_debut_date`, stored since ADR-0004 and
  written by `GametoraCharacterParser`. So the profile block spans two sources, and the brief's single
  sentence "Basic information … `characters.c6676539` — an undeclared source" understates it by one field.

`playable_en = 68` is the same 68 the catalog lists as `GlobalReleased`, arrived at from the other side.
Not load-bearing, but it is a cross-check worth having before a parser trusts either.

### The lore landmine, measured

`sex` and `rl` are on **every one of the 163 rows**, and `race` on 128 of them. `race` is literally
`"uma"`. `rl` is the dataset's record for the **real-world namesake** each character is drawn from:
`active` years, a `country`, and a **`death`** date. That is exactly the framing C-4 exists to keep out of
this app, sitting on every row of the source the profile block wants — and it is reachable by the most
natural implementation there is: a parser or a view that iterates the row instead of projecting an
allowlist. `StoreCharacterCards` already models the right shape for a reason — it names five columns and
provenance and never spreads the record.

So, stated as a constraint on the parser this slice will write: **the field list is an allowlist, and it
is the test, not the view, that proves it.** A fixture row carrying `rl` with a death date, asserted
absent from every stored column, is the check that can fail.

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

No paths, no file names, no `card_id`, nothing per-form. It says which portrait kinds a character page
has; it does not say where the bytes are. **No per-form asset source was discovered, so §C's stop
condition is not triggered and ADR-0012 Decision 2's deferral stands** — now with a fifth dataset checked
rather than four assumed.

## What §A can now be built from

The six fields the user asked for, and where each actually comes from:

| field | source | coverage |
|---|---|---|
| Japanese name | `umamusume.name_ja` (stored) | already there; `characters.jp_name` agrees |
| Voice actor | `characters.va_ja` + `va_en` | 163 / 137 |
| Release date | `umamusume.global_debut_date` (stored) | not in this dataset |
| Birthday | `characters.birth_day` + `birth_month` (+ `birth_year`, 146) | 163 / 163 / 146 |
| Height | `characters.height` | 163 |
| Three sizes | `characters.three_sizes.{b,w,h}` | 127 of 163 |

The sibling-table recommendation holds, and for the reason the brief gave: ADR-0003 Amendment R3 wants
`source_url`, `snapshot_path`, `fetched_at`, `source_timezone` and `is_manual` inline on a reference row,
and `umamusume` carries none of those. The join is `umamusume.external_ref = 'gametora:char:' || char_id`,
the same ref `StoreCharacterCards` resolves through.
