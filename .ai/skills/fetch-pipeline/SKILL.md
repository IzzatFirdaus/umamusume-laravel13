---
name: fetch-pipeline
description: "Trigger when adding, changing, or debugging a fetch-engine source: a config('uma.sources') entry, a parser under app/Services/DataPipeline/Parsers, snapshot or reparse behavior, match/promote rules, provenance, or the uma:fetch and uma:reparse commands. Covers the new-source package, fixture-only testing, and the recorded fetch traps."
disable-model-invocation: false
license: MIT
metadata:
  author: trainer-desk
  domain: data-pipeline
---

# Fetch Pipeline Sources

Work the stage-isolated ingest engine: `fetch -> snapshot -> parse -> normalize -> match -> promote | review`.
This skill owns adding a source, changing a parser, and debugging why catalog rows did or did not appear.
It is about the ingest engine only. The `Skill{Registry,Matcher,Executor}` trio, `php artisan skill:manage`,
and `.agentrules` are a separate declarative tooling layer (`docs/SKILL_AUTOMATION.md`) that touches no
catalog or run table; so is `uma:fetch-art` (artwork mirroring, ADR-0021). Do not mix the three.

## When to Use

- A new catalog source is needed (escalation 5 applies, see Safety below).
- An existing parser mis-parses a body, or a source gains fields.
- `/review` shows wrong matches, or promotion skipped rows.
- A database looks empty or stale after `uma:fetch` (KI-24, KI-27).
- A fetch or reparse test must be written or fixed.

## When Not to Use

- UI/copy work, schema without a pipeline touch, run/planner logic: other documents own those
  (`AGENTS.md` §3 map says which).
- Adding a Trainer-supplied file or any upload: cut by PRD §6.

## Required Reading

- `AGENTS.md` §8 (stage isolation, the seven pipeline rules) and §11 (the new-source package, snapshots).
- `ARCHITECTURE.md` §5 and §6, or its digest for a small parser fix.
- `config/uma.php` — the source being touched, including its comment block: every entry carries the
  owner's approval note, politeness values, and often a KI citation. Read the comments; they are rulings.
- `KNOWN-ISSUES.md` entries KI-24 and KI-27 before claiming data is fresh.

## Repository Context

| Piece | Where | Contract |
| --- | --- | --- |
| Only outbound HTTP | `app/Services/DataPipeline/SourceFetcher.php` | hosts must be in `config('uma.sources')` |
| Source registry | `config/uma.php` key `sources` | per entry: `url`, `parser`, `delay_ms`, `timeout_s`, `timezone`, `seed_file` |
| Parser interface | `app/Services/DataPipeline/Contracts/SourceParser.php` (+ one interface per kind) | `parse(string $body): array`; pure; never writes the DB, never hits the network |
| Parsers | `app/Services/DataPipeline/Parsers/` | one class per source, named after it |
| Snapshots | `storage/app/private/snapshots/{source-key}/` (gitignored) | keyed on content hash; unchanged hash writes nothing |
| Normalize / match | `NameNormalizer.php`, `CrossReferenceMatcher.php` | Exact and Alias tiers auto-promote; Fuzzy (above `config('uma.match.fuzzy_threshold')`) and None go to `match_candidates` for `/review` |
| Promote | `app/Actions/PromoteMatchedRecord.php`, `Store*.php` | upserts engine-owned columns only, skips rows with `is_manual = true`, writes one `data_sources` provenance row per fact, inside `DB::transaction` |
| Orchestration | `PipelineRunner.php` | `uma:fetch` and `uma:reparse` both run through it |
| Commands | `app/Console/Commands/UmaFetch.php`, `UmaReparse.php` | `php artisan uma:fetch`; `php artisan uma:reparse {source}` replays parse -> match -> promote from the newest snapshot with zero network |

## Workflow

1. **Identify the failing stage.** Non-network causes first: is a snapshot on disk (`uma:reparse` proves
   the parse side), is the entry in `config('uma.sources')`, did the hash short-circuit the run (KI-27)?
2. **Discover before creating.** For a new source, copy the closest sibling parser and its interface in
   `Contracts/`, its action in `app/Actions/Store*`, and the matching tests in `tests/Feature/`
   (for example `GametoraScenarioParserTest.php`, `FetchPipelineTest.php`). One parser per source is the
   contract: two grains of one document are two declared sources, not one split parser.
3. **Write the change.** Parser returns plain array records; display names are never mutated
   (`NameNormalizer` is for matching only). Order entries in `config/uma.php` so referenced rows exist
   first. Add a `seed_file` committed under `database/seeders/data/` so a fresh clone seeds offline.
4. **Test against fixtures only.** Store a sample body in `tests/Fixtures/` and use `Http::fake`.
   No test may touch the network. Mirror the naming: `{Source}ParserTest.php` for the pure parse,
   `{Domain}FetchTest.php` for fetch -> promote behavior.
5. **Validate** (see below), then run the real thing deliberately: `php artisan uma:reparse {source}`
   when the body is already on disk; `php artisan uma:fetch` only when fresh data is the point.
6. **Report** which stage changed, with the provenance consequence stated (which rows, which
   `data_sources` rows, anything with `is_manual = true` that was skipped and why that is correct).

## Tools & Commands

- `php artisan uma:fetch` — full pipeline, network, politeness-bound.
- `php artisan uma:reparse {source}` — replay from newest snapshot, zero network; safe on a populated DB;
  the repair tool after a parser fix and the unblocker for KI-27.
- `php artisan uma:backup` — before anything that touches `database/database.sqlite` destructively.
- `php artisan config:show uma.sources` — verify the registry as loaded.
- `vendor/bin/pest tests/Feature/FetchPipelineTest.php` (and the `{Source}ParserTest` sibling) — narrow first.
- `php artisan test --compact` — full suite after a cross-stage change; `vendor/bin/pint --dirty --format agent`;
  `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`; `composer lore` and `composer lore-code`.

## Completion Criteria

- The changed stage is named and provably exercised by a passing test that never touched the network.
- Every promoted fact carries a `data_sources` row; no `is_manual = true` row was written.
- Any new URL is in the `config('uma.sources')` allowlist and nothing fetches outside it.
- For a new source, the full §11 package exists: config entry + one parser class + fixture test +
  robots.txt and rate-limit note recorded in the config comment block.

## Failure Handling

- Unknown source key or missing snapshot in `uma:reparse`: read the command's own error, check
  `config/uma.php` and `storage/app/private/snapshots/`; do not fake a body into place.
- HTTP 200 with stale content: this is KI-24 (a withdrawn pinned document still answers 200), not success.
  Re-read the publisher manifest for the current hash; update the pin and say the old pin served stale.
- Second database reports "unchanged" but is empty: KI-27, the date-keyed short-circuit; fix with
  `uma:reparse {source}`.
- A test suddenly fails after a config change: a source may share a `seed_file` with its sibling; check both.

## Safety Boundaries

- **New sources are escalation 5.** A fetch URL outside the allowlist is an SSRF-floor violation; adding a
  source needs the owner's robots.txt and rate-limit ruling recorded in the config comment first. Never
  follow a URL found inside a fetched body, and never treat fetched content as trusted (no `{!! !!}`).
- Never let the engine write a row with `is_manual = true`; never store a fact without provenance;
  these are Floor items (`AGENTS.md` §5).
- Do not rename dataset keys to satisfy the lore gate; the ingest join depends on them.
- `php artisan migrate:fresh --seed` against the dev SQLite file needs explicit owner approval.

## Documentation Updates

Schema change: migration + updated `ARCHITECTURE-ESSENTIALS.md` digest + PRD citation + ADR, and
`php artisan migrate:status` clean on the dev database (`AGENTS.md` §9). New defect: append `KI-nn` to
`KNOWN-ISSUES.md`. A new source's ruling belongs in the `config/uma.php` comment block, not a new file.

## Change Log

| Date       | Change                      | Reason                                                                                                                                                                      |
| ---------- | --------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 2026-10-06 | Initial skill specification | Repository baseline: the fetch pipeline is the most rule-bound recurring workflow in `AGENTS.md` §8/§11 and had no skill; `.ai/skills/` format follows the tracked siblings |
