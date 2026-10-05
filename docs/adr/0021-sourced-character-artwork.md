# ADR-0021: Sourced character and support-card artwork, mirrored locally and derived from ids

Status: **Accepted (owner ruling 2026-10-05); the fetch half is built, the display half is not.** The owner
chose "A then C": record the findings, then build the local mirror. Decisions 2 to 5 landed on 2026-10-05 as
`uma:fetch-art`, `App\Services\DataPipeline\ArtworkMirror` and `SourceFetcher::fetchAsset()`, with
`tests/Feature/ArtworkMirrorTest.php` proving them against `Http::fake`. **No screen renders a picture**: no
`<img>` exists in `resources/views/`, no slot was placed, and which screens would carry one is `PRD.md` OQ-6.
**No pass has been run against the live host**, so the mirror is empty on this tree. Nothing here changes
`PRD.md` §6.13.

Date: 2026-10-05
Deciders: product owner (decision), Architect (recording and consequences)
Amends: `ADR-0012` Decision 2 (its stated blocker is obsolete; see Erratum 4 there)
Preserves: `PRD.md` §6.13 ("No trainee image uploads") - untouched, and this ADR does not narrow it
Related: `PRD.md` FR-A, FR-B, OQ-6 (which surfaces get art, still open); `ARCHITECTURE.md` §6 and §8;
`DESIGN.md` §4.7 (new, owns slot behaviour), §6 (alt text inside C-4), §11 item 7;
`SCREEN_SPEC.md` §7 item 16; `config/uma.php`; `database/seeders/data/`

## Context

The owner asked whether the legacy planner (`D:\umamusume_dev\uma_musume_race_planner`, repo #4) holds trainee
image knowledge, and then to search for an API or a bulk source covering every trainee and support card. Both
questions were answered by probe, not by assumption. Every HTTP result below is a request this pass made.

**The legacy repo supplies nothing.** Its `umamusume.images` column is `{avatar, full, cropped, source}`; in its
data only `avatar` is populated and `source` is the literal string `"fanart"`. Its files are booru downloads
(`__agnes_tachyon_umamusume_drawn_by_welchino__sample-….jpg`) hand-fed through an upload form, 11 characters in
a 135-character roster, keyed by slug rather than id, and gitignored. Its app contains **zero outbound HTTP
calls**. It is the feature `PRD.md` §6.13 cut, and the cut was sound.

**GameTora's asset host resolves by id.** Base `https://media.gametora.com/umamusume/`, keyed by the **6-digit
`card_id`**, never by `char_id`:

| Path | Verified |
|---|---|
| `characters/portrait/trainee/{128,256,512}/{card_id}.png` | 200 image/png; 8.7 KB / ~22-30 KB / 57.8 KB. `/1024/` is 404, so the size set is fixed |
| same, `…/256/1001.png` (a `char_id`) | **404** - the grain is the card, not the trainee |
| same, `…/256/100101.png` and `…/113601.png` | 200 - debut forms resolve, and `113601` is Red Desire, **unreleased on `[Global]`**, so the host is not Global-filtered |
| `characters/render/trainee/idle[/thumb]/{card_id}.png` | 200, 236 KB full |
| `characters/chibi/{card_id}_{n}.png` | 200, and the filenames match `character_media.chibi_sheet` |
| `supports/full/small/{support_id}.png`, `supports/full/{support_id}.png` | 200, 85.6 KB and 3.1 MB |
| `skills/icon/{skill_id}.png` | 200, ~2 KB |
| `gacha/char/thumb/{card_id}.png` | Partial: 404 on `101001` and `101801`. Banner-era coverage, so it is not a base |

Two properties of that host matter for the build. A miss returns **27,150 bytes of `text/html` with HTTP 404**,
so every check must gate on status and never on "did bytes come back". And `media.gametora.com` publishes no
`robots.txt` (404), while `gametora.com/robots.txt` disallows only `/404`, `/500`, `/patron-zone`, `/cdn-cgi/`,
`/loc/` and one event landing page - nothing covering `/data/` or the media host.

**Our ids already reach it.** `gametora-characters.e9e9ee6d.json` yields **268 distinct 6-digit ids** from
`url_name` (`100101-special-week`), `support-cards.88dea522.json` yields **559 distinct `support_id`s**, and
`skills.609afe88.json` carries `iconid` on all 1,910 rows but only **125 distinct** values, so skill art is a
per-icon fetch rather than a per-skill one. No committed export carries an image field, and no dump names
the base path, so the base URL is a constant this repository owns.

**The document's card count is not the catalog's row count, and the mirror reads the rows.**
`uma:fetch-art --dry-run` against a freshly seeded database on 2026-10-05 counted **106** ids in
`character_cards` and **559** in `support_cards`. The smaller number is a filter, not a different key space:
`gametora-characters.e9e9ee6d.json` is the seed body behind both the character and the character-card sources,
its 268 records are card-grain with a distinct `card_id` apiece, and `ADR-0008` promotes only those carrying a
Global release date. So the portrait set reachable today is 106 files, and it widens as cards are promoted
rather than as the host gains art. A Trainer who wants the other 162 has to widen the promotion rule, which is
a catalog decision and not this ADR's.

**Bulk is worse, and the search was thorough.** No repository commits the ~962 images: the candidates are
installers that read a game directory the user already owns, or CDN downloaders. The client's own CDN
(`https://prd-storage-game-umamusume.akamaized.net/dl/resources/{hash[0:2]}/{hash}`, no auth) serves **Unity
AssetBundles keyed by content hash**, and the id-to-hash table lives in the `meta` database inside an installed
client - so reaching it needs a game install, and its own tooling warns about 403 throttling. `umamusu.wiki`
files are id-named (`Support_Card_30002_Card.png`) but its `robots.txt` carries `Disallow: /w/`, which covers
every image path, so scripted fetching contradicts the site's own policy. `umamusume.fandom.com` resolves
filenames through `api.php` but its CDN answers 403 to a plain fetch. game8.co re-hosts under opaque ids that
relate to nothing. Officially, `umamusume.jp` serves art from hashed microCMS URLs and `umamusume.com` has no
id-addressable host.

## Decision

| # | Object | Shape | Why this shape |
|---|---|---|---|
| 1 | **What is authorized** | Display of third-party-sourced, id-addressable game artwork for trainee costume cards, support cards and skills. `ADR-0012` Decision 2's "Nothing" is superseded for this object only | The two things that ADR-0012 said were both missing now exist: a `card_id` key, from rows we already store, and a resolvable path, verified above. Its falsifier named a different event (the media *dump* gaining a `card_id` field) and that has not happened; the requirement it was protecting - be able to fetch the thing - is met by the host |
| 2 | **How files arrive** | A command, `uma:fetch-art`, in the same family as `uma:fetch`, reading ids from the catalog tables and writing to `storage/app/private/artwork/` (gitignored), with the per-source `delay_ms` and `timeout_s` politeness pattern and a skip-files-already-present default that `--refetch` overrides | One polite pass replaces a per-page crawl, which is what makes this acceptable against a host that has no robots policy of its own. The pipeline's snapshot discipline already exists; this reuses its fetcher rather than inventing one, and takes the short-circuit from the filesystem rather than from a content hash, because the file *is* the body. The flag is named for what it does (`--refetch`) instead of restating the default, so `ADR-0021`'s original "`--skip-existing` default" is implemented as that default with an override |
| 3 | **What is stored, and where** | **Nothing in the database.** The path is derived from the id at render time; a sibling `artwork/manifest.json` records url, sha256 and fetched-at per file | Storing a path column duplicates a fact the id already determines, which is the second-store problem `PRD.md` §6.12 rejects, and `ADR-0004` provenance rows are for engine-owned *facts* about the catalog. The manifest is the audit trail for files, not for assertions |
| 4 | **The allowlist** | `media.gametora.com` is declared in `config('uma.sources')` as an **asset** host with its robots note and no data parser | §8's SSRF rule bars URLs outside that config. An asset host that is not declared is either a silent hole or a broken build; declaring it keeps the single rule true |
| 5 | **Missing art** | The text-only row that ships today is the fallback: no image element paints, and the screen never shows a broken frame, a grey box or a placeholder glyph. Absent art is normal state, not an error | The mirror is partial by nature (a new card's art may not exist upstream yet), and `DESIGN.md` §4.7 records the measurement that there is no chip or avatar component to fall back *to* — today's rows are name, `name_ja`, rarity chip and counts, and that is what renders when the file is not on disk. `DESIGN.md` §1's R-31 already refuses decoration that buys nothing |
| 6 | **What stays cut** | `PRD.md` §6.13 unchanged: no upload surface, no file input, no user-supplied art. No hotlinking in rendered HTML either, because the tool's offline-local premise (`PRD.md` §6.9, `ARCHITECTURE.md` §8) means the asset must be on disk | Two different objects. This ADR authorizes *sourced* art fetched by the tool; it does not authorize the user's files, and it does not make the app depend on a third party to render |

## Consequences

- **Files that must move when this is built, in one change each**: `config/uma.php` (the asset host with its
  politeness and robots note); a command class under `app/Console/Commands/` plus the fetch path in
  `app/Services/DataPipeline/` or `app/Actions/`, whichever the build picks, with `Http::fake` tests and stored
  fixtures and **no test touching the network**; `resources/views/components/` slots on the catalog index and
  detail and the support-card index and detail; `DESIGN.md` §4.7 (its rules already written, its pixel values
  and its surface list deliberately not) and §11 item 7; `SCREEN_SPEC.md` §7 item 16; `PRD.md` OQ-6; and this
  ADR's status line.
- **No migration.** Decision 3 stores nothing, so `CharacterCardSchemaTest`'s column list is untouched and no
  digest travels with this change. If a future build adds a column, the §11 rule about digest-plus-ADR-plus-
  down() applies to that change instead.
- **The lore gate reaches this through alt text.** `DESIGN.md` §6 puts alt text inside the C-4 boundary, so the
  alt is the client display name and nothing else: no equine vocabulary or framing, and no fabricated descriptor
  for a file the tool cannot see inside.
- **Cache, not database.** Artwork lookup is a filesystem existence check, so it inherits the catalog read cache
  and the `catalog:version` bump only if a build ever records availability in the database. It does not today.
- **Scale, measured.** A default pass on the seeded tree is **665 requests**: 106 portraits at 256 px, about
  **2.7 MB**, plus 559 support thumbnails at `full/small`, about **48 MB** — roughly **51 MB**, gitignored.
  Beyond that set: the same portraits at 512 are about 15 MB for all 268 card ids the host answers, the
  full-resolution support art is 1.7 GB, every idle render about 63 MB, and skill icons are the row a naive
  count overstates, since all **1,910** skill rows carry an `iconid` but only **125 distinct** ids exist
  (measured on `database/seeders/data/skills.609afe88.json`, 2026-10-05), so a build would fetch by distinct
  id. None of those are in the default set.
- **Rights, stated plainly rather than argued away.** This is Cygames' copyrighted art, re-hosted by a third
  party, and a resolvable URL is not a licence. The authorized use is a private copy on one owner's loopback
  machine for a personal tracker: never published, never redistributed, never reachable from outside that
  machine. Loading the owner's own local file through the owner's own loopback page is inside that boundary;
  publishing a static asset path or deploying the tool is outside it, which is why `ARCHITECTURE.md` §8 says no
  static path is published today and leaves the streaming choice to the display decision. That is low exposure
  and close to community norm; it is still copying protected work, and the owner accepted it on that
  understanding. `PRD.md`'s "must not be exposed publicly" rule is part of what keeps this a personal copy, so
  an artwork surface strengthens the existing ban on deployment rather than loosening it.
- **`ADR-0012` Erratum 4 is what keeps the record honest**: its Decision 2 blocker is corrected rather than
  deleted, and its falsifier is marked as not-fired-but-bypassed, which is the kind of distinction this
  repository's errata convention exists to preserve.

## Verification

This ADR adds no behaviour, so it ships no test. What proves it, and what would falsify it:

- Every URL row above is reproducible with `curl -o /dev/null -w "%{http_code} %{content_type} %{size_download}"`
  against `media.gametora.com`; re-running them is the falsifier. A 404 on any `characters/portrait/trainee/256/`
  id that exists in `gametora-characters` falsifies Decision 1.
- `composer lore` and `composer lore-code` over the tracked tree: both were run on 2026-10-05 at 238 hits /
  77 exempt and 46 hits respectively, unchanged by this file.
- `php artisan test --compact tests/Feature/DocCitationParityTest.php` must stay at or under the ratchet, which
  is why the dead `docs/design-research/` pointers this pass found are repointed rather than repeated.
- **Not settled by this ADR**, and each named so a later build does not guess: the four official profile poses per
  trainee have no resolvable path yet (GameTora serves them behind a hashed route); `gacha/char/thumb` coverage is
  incomplete and is therefore not a base; and whether the Global client's own CDN host matches the JP one is
  unverified, which is only relevant if someone later proposes extracting from an installed client.
- **Not settled by the build either**, and found while building it. Two kinds the ADR's scale paragraph counted
  are not mirrorable by what `uma:fetch-art` does. Skill icons are the clear one: `skills.iconid` is not a column,
  so the 125 distinct ids exist in `database/seeders/data/skills.609afe88.json` and nowhere the command can read
  them; mirroring them would need a migration, which `PRD.md` §6's no-column-without-a-consumer rule and AGENTS.md
  §11 both make a separate decision rather than a detail of this one. Chibi sheets are the other: they are keyed
  on `character_media.chibi_sheet`, a table this tree does not have. What the built command does reach is
  `character_cards.card_id` (268) and `support_cards.support_id` (559), which are the two shapes in
  `config('uma.sources.gametora-artwork.paths')`.
- **The live pass has not been run.** `uma:fetch-art --dry-run` reports the id counts and asks for nothing; the
  first real run is the owner's call at 665 requests and roughly 51 MB on the seeded tree (`uma:fetch-art
  --dry-run` counted them on 2026-10-05), and the `ADR-0012` Erratum 4 falsifier (a
  portrait id that 404s although `gametora-characters` has the row) is what that run would surface.

**Erratum 1 - the live pass has been run as of 2026-10-05, and the falsifier named above did not fire.** The
sentence preserved above recorded the state at the time of writing: the fetch half was built and had never
touched the network. The owner authorised the full default walk the same day. `uma:fetch-art` reported
`card_portrait: 106 ids, wrote 106, unresolved 0` and `support_thumb: 559 ids, wrote 559, unresolved 0`, so
**665 files, 45 MB**, in `storage/app/private/artwork/` with a sibling `manifest.json` carrying url, sha256 and
fetched-at per file. Nothing entered the database and no `data_sources` row was written, which is what Decision
3 chose. The cost prediction above was right on the request count and about 6 MB high on bytes.

What the run settles: the asset host answers every id the catalog holds, so **the `ADR-0012` Erratum 4 falsifier
did not fire** - no portrait id 404s against a row `gametora-characters` has. It also exercises the read path
end to end against real bytes rather than an `Http::fake` body: `GET /artwork/card_portrait/100101` returns
`200`, `image/png`, 21,937 bytes, inside the 22-30 KB band this ADR's Verification measured, and the non-2xx
guard that `ArtworkMirrorTest` holds against the host's 27,150-byte HTML 404 has now been observed in the wild
as well as in the fixture.

What it does not settle: one seeded tree on one day. The mirror stays partial by nature for any row added after
this pass, and a file withdrawn upstream still renders the fallback. `uma:fetch-art` remains manual and nothing
schedules it - which matters more than it did before, because `mirror()` writes the manifest once, after a whole
kind completes, so a run interrupted mid-kind leaves bytes on disk with no manifest rows and the next run's
`skipExisting` never adds them.

