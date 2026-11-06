# Flows — Fetch Pipeline (data ingestion)

Date: 2026-11-06. HEAD: `67a4830`. Documentation only; no code changed.
Evidence: `app/Services/DataPipeline/**`, `app/Console/Commands/**`, `config/uma.php`, `app/Actions/Store*.php`, `app/Enums/{MatchTier,CandidateStatus}.php`.

The pipeline is stage-isolated: **fetch → snapshot → parse → normalize → match → promote | review**. Data enters through artisan commands only; no web route writes catalogue rows.

---

## 1. Fetch

`SourceFetcher` is the **only** outbound HTTP path (`app/Services/DataPipeline/SourceFetcher.php`).

| Concern | Detail |
| --- | --- |
| Allowlist | every host comes from `config('uma.sources')`; a URL outside it is an SSRF-floor violation |
| Redirects | at most `MAX_REDIRECT_HOPS = 2`, and each hop is re-checked against the allowlist (`allowlistedTarget()`) |
| Cache-busting | the source URL carries a hash token published in the host's manifest, so a republished document surfaces as a fetch failure rather than as silently old data (`HASH_PATTERN = /^[a-f0-9]{8}$/`) |
| Politeness | per-source `delay_ms` (1000 ms on the approved Gametora entries) and `timeout_s` (15 s) |
| Fallback | `fromManifest()` / pinned-URL fallback; `uma:fetch` warns when it falls back (KI-24) |
| Assets | `fetchAsset()` returns null on **any** non-2xx; the asset host answers a miss with 27,150 bytes of `text/html` at 404, so "bytes came back" is never the test |

Source entries that declare no `parser` are stepped over by `uma:fetch` — that is how the asset host stays allowlisted without being parsed.

---

## 2. Snapshot

- Raw body stored gitignored under `storage/app/private/snapshots/`, keyed on a content hash.
- An unchanged hash short-circuits the run and writes nothing.
- **Trap (KI-27):** a snapshot short-circuit keyed on today's date can leave a second database empty while claiming "unchanged". Use `php artisan uma:reparse <source>` to fill a second database safely.

---

## 3. Parse

One parser class per source implementing `Contracts\SourceParser`. Thirteen exist:

`GametoraCharacterParser`, `GametoraCharacterCardParser`, `GametoraCharacterProfileParser`, `GametoraRaceCatalogParser`, `GametoraRaceParser`, `GametoraRaceFansParser`, `GametoraRaceRewardParser`, `GametoraRacetrackParser`, `GametoraScenarioParser`, `GametoraSkillsParser`, `GametoraSupportCardParser`, `GametoraSupportEffectParser`, `GametoraUraObjectivesParser`.

Fetched pages are untrusted input: parsed as data, escaped on render, never `{!! !!}`, and a URL found in a body is never followed.

---

## 4. Normalize

`NameNormalizer` is pure — NFKD, lowercase, separators and combining marks stripped. Display names are never mutated; the normalized form lands in `match_key`.

`JapaneseNameNormalizer` handles the Japanese side; `match_key_ja` was added to `skills` by `2026_11_05_084524_add_match_key_ja_to_skills_table`.

---

## 5. Match

`CrossReferenceMatcher` proposes one of four tiers:

| Tier | Outcome |
| --- | --- |
| `Exact` | auto-promote |
| `Alias` | auto-promote (via `UmamusumeAlias`) |
| `Fuzzy` | queue for review when above `config('uma.match.fuzzy_threshold')` |
| `None` | queue for review |

Rows that must not enter the name-match path are routed out of it in `PipelineRunner::run()` by parser capability, not by source name:

| Parser kind | Routed to | Why |
| --- | --- | --- |
| `RaceCatalogSourceParser` | `StoreRaceCatalogSlots` | a race row is not a display name; otherwise 410 races become review candidates |
| `TraineeGoalSourceParser` | `StoreTraineeGoals` | a condition id is not a display name; otherwise ~1,701 objectives fail to match a trainee |
| `SkillSourceParser` | `StoreSkills` | otherwise all ~1,910 skills land in the queue filed as unidentifiable characters |

Each branch also bumps `catalog:version` where the surface it feeds is cached behind that counter (the goal ladder and cards do; the profile branch deliberately does not).

The three branches are deliberately duplicated rather than abstracted: each writer owns a different grain (a race row collapses on scenario/year/month/half/title, a skill row on the source's own id, a card row on `card_id`), and a shared abstraction would add a third thing to read with no second implementation to justify it.

---

## 6. Promote

`PromoteMatchedRecord`:

- upserts **engine-owned** columns only;
- **skips any row with `is_manual = true`** (the Floor);
- writes one `data_sources` provenance row per fact;
- runs inside `DB::transaction`.

`data_sources` carries the source key, the URL it came from, and the read timestamp — no fact is stored without provenance.

---

## 7. Review

Fuzzy and None land in `match_candidates` and wait for the `/review` queue (`flows-review.md`).

---

## 8. Artwork mirror (ADR-0021)

`uma:fetch-art` (`--kind`, `--dry-run`, `--refetch`):

1. Reads ids from `character_cards.card_id` and `support_cards.support_id` (`ArtworkMirror::sourceIds()`).
2. Requests each from the asset host declared in `config('uma.sources')` under source key `gametora-artwork`.
3. Writes files under gitignored `storage/app/private/artwork/{kind}/{id}...` with a sibling `manifest.json`.

**Nothing enters the database and no `data_sources` row is written.** The mirror is manual: nothing schedules it, and an unrun mirror renders nothing at all — `ArtworkSlot.vue` draws no frame and no error for a null URL (`DESIGN.md` §4.7). A fresh worktree shows no frames until someone runs it; `--dry-run` reports the id count and how many are already on disk without touching the network.

Files reach the browser through the loopback `artwork.show` route (`bootstrap/app.php`), never from the asset host.

---

## 9. Commands

| Command | Purpose |
| --- | --- |
| `php artisan uma:fetch {source?}` | fetch, snapshot, parse, cross-reference, promote or queue |
| `php artisan uma:reparse {source}` | replay a stored snapshot through the pipeline (safe on a populated DB) |
| `php artisan uma:fetch-art` | mirror id-addressable artwork |
| `php artisan uma:import:support-cards` | the dedicated support-card import |
| `php artisan uma:backup {destination?}` | SQLite backup |
| `php artisan uma:enrich-races` | race enrichment pass |
| `php artisan uma:db-guard` | database safety check |
| `php artisan independent-training:complete` | closes expired Independent Training windows |
| `php artisan skill:manage` / `skill:inventory` | the separate skill tooling layer |

No command is scheduled in-tree except the Independent Training tick; the fetch and artwork passes are manual by design.

---

## 10. Invariants an editor must not break

1. `SourceFetcher` stays the only outbound HTTP path; new sources are allowlist entries plus a robots and rate-limit note.
2. One parser class per source; a source entry with no parser is skipped, not guessed.
3. `NameNormalizer` stays pure; display names are never mutated.
4. Display names are never matched on — always `match_key`.
5. `is_manual = true` rows are never written by the engine.
6. Every fact carries a `data_sources` row.
7. A hash-unchanged body writes nothing.
8. Artwork never enters the database.
9. No test touches the network: pipeline tests use `Http::fake` with bodies from `tests/Fixtures`.

---

## 11. Verification surface

| Claim | Pinned by |
| --- | --- |
| Full pipeline contract | `FetchPipelineTest`, `FetchFailureReportingTest`, `FetchRedirectAllowlistTest` |
| Per-parser behaviour | `Gametora*ParserTest.php` (twelve files) |
| Matching tiers | `CrossReferenceMatcherTest` |
| Store actions and atomicity | `StoreSupportCardsTest`, `StoreCharacterProfilesTest`, `StoreActionBatchAtomicityTest`, `RosterSeederReRunTest` |
| Artwork mirror | `ArtworkMirrorTest`, `ArtworkAssetRouteTest`, `ArtworkAssetRouteTest` |
| Caching counter | `CatalogVersionCounterTest` |
| Backup | `UmaBackupTest`, `DatabaseSafetyTest` |
