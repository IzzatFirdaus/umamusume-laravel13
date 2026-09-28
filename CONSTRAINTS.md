# CONSTRAINTS.md

The quality bar for this repository, written as a contract. Agents: read this before writing code; never weaken a threshold to make a change pass. Relaxation requires the human owner (AGENTS.md escalation path 4).

## Dimensions and thresholds

| # | Dimension | Threshold | Command |
|---|---|---|---|
| C-1 | Tests | All Pest tests pass; every behavior change ships a test | `php artisan test --compact` |
| C-2 | Static analysis | PHPStan level 6 (Larastan), zero errors | `vendor/bin/phpstan analyse --no-progress` |
| C-3 | Formatting | Pint clean | `vendor/bin/pint --dirty --format agent` then `vendor/bin/pint --test --format agent` |
| C-4 | Lore | Zero unexplained hits of banned patterns (below) in tracked text | `make lore` |
| C-5 | Migrations | Fresh migrate + seed succeeds; every migration has a working `down()` | `php artisan migrate:fresh --seed` |
| C-6 | Performance (local budget) | Catalog index under 200 ms at ~1,000 Umamusume / ~2,000 skills (NFR-3) | manual benchmark per PRD §8; re-check when schema or query shape changes |
| C-7 | UI states | Every data view renders empty, loading/refresh, and error states (§7, antislop R-27) | review checklist |
| C-8 | Dependencies | No new package without human approval; `composer audit` and `npm audit` with no reachable critical/high | `composer audit`; `npm audit --omit=dev` |

> C-7 loading-state scope is interpreted by ADR-0007 (`docs/adr/0007-c7-loading-state-scope-for-server-rendered-views.md`): initial server-rendered navigation may rely on browser-native loading; user-initiated async operations require explicit loading states. Empty/error/data states remain mandatory. Gate tooling and G-number registration live in `docs/GATE-REGISTRY.md`.

## Floor (never, in any change)

- No new suppressions: `@phpstan-ignore`, `@phpstan-` escapes, `eslint-disable`, `@ts-ignore`.
- No stub bodies: `throw new \Exception('not implemented')`, empty `catch {}`, TODO placeholders.
- No deleted or skipped tests without the human's approval and a reason in the commit message.
- No engine write to rows with `is_manual = true` (PRD FR-B-4).
- No fact stored without provenance (AGENTS.md Data Engineer rules).
- No fetch URL outside `config('uma.sources')` allowlist (SSRF posture, ARCHITECTURE §8).
- No business logic in controllers; no inline `$request->validate()` (CLAUDE.md Banned Patterns).

## Lore banned patterns (C-4)

Grep, case-insensitive: `horse`, `horses`, `sire`, `dam`, `mare`, `foal`, `🏇`, plus animal framing of characters.

- Context check required before calling a hit a violation: "dam" inside "damaged", "stable" as an adjective, etc. The grep proposes; the Lore Guardian decides (AGENTS.md).
- `docs/PRE-MORTEM.md` quotes legacy violations as elimination evidence, once each; those lines are exempt. No new violation may enter anywhere else.
- `make lore` excludes `vendor/`, `node_modules/`, and the pre-mortem evidence file.
- **Identifier and mechanics exemption.** The list governs **player-facing copy and character framing**, and nothing else. It does not apply to: dataset and export keys (`intelligence` for Wit, `friend` for Pal, `scenarios.json` field names); localisation-mapping strings quoted as client evidence ("Intelligence Limit Up", "Runner's Tricks ◎"); or a **distinct mechanic** that merely shares a word — "Bad Conditions", the "condition correction" in the failure formula, and a "condition" item effect are mechanics, not Mood synonyms. Renaming an identifier to satisfy the list breaks the ingest join; that is a worse failure than an ugly key.
- **Verbatim names are a third category**, between our own copy and a stray noun. A quoted skill, race, card or title string that contains a banned term stays as **source data** and is barred from **promotion into UI copy** — see the Air Messiah ruling in `docs/UMAMUSUME_REFERENCE.md` §2.7 and the `[Global]` "Cleat" case there. Editing the data to satisfy the style rule corrupts the corpus; the guard goes on the display path.
- **The grep is a floor, not the rule.** `make lore` sweeps 23 banned words over three greps and `lore-code` adds the Global client terminology, but only inside app directories. Neither reaches `muzzle`, `rider`, `bit`, `flock`, `pack`, or the framing phrases ("your horse", "the animal"), and neither reads intent, which is why `dam` in "damaged" and `stable` in "stable growth" arrive as hits and are ruled on by hand. The wider dictionary lives in `docs/design-research/CONSTRAINTS.md` §3.1 (D-20); a human read is the control for anything the greps cannot see.

## Verification sequence before any hand-off

1. `php artisan migrate:fresh --seed`
2. `php artisan test --compact` (narrow first, full at hand-off)
3. `vendor/bin/pint --dirty --format agent`
4. `vendor/bin/phpstan analyse --no-progress`
5. `make lore`
