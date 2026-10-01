# Audit and Verification

## Provenance

This document consolidates the following source files verbatim (no summarization, no deduplication):

- `docs/design-research/audit-verification-2026-10-01.md` (113 lines)

---

## audit-verification-2026-10-01.md

### Audit Verification: the Eighteen Findings Against Post-Fix State

**Date:** 2026-10-01
**Pass:** third. First pass produced the findings, second confirmed them, this pass verifies whether the corrections landed.
**Repository state verified at:** `master` @ `4129cf0` (working tree clean of tracked edits except the peer files listed in the fence section).
**Stance:** read-only. Nothing was fixed, edited, migrated, committed or pushed by this pass. The only write is this file.

#### 1. Method note

**Disciplines applied.** Doubt-driven development on every verdict, meaning no verdict rests on a commit message or a prior audit's citation. Source-driven development, meaning every citation below was re-read from the current file, not carried over. Code-review-and-quality supplied the six axes in section 5. Testing-best-practices and test-driven-development governed the test-honesty axis. Security-and-hardening governed F-9 and F-3. Performance-optimization governed F-4, F-5, F-7, E-8. Laravel-best-practices governed the app-layer reads. Careful governed the fence.

**Skill invocations.** See section 6. Two of the named skills could not be attempted safely and one was declined on cost grounds. That is stated rather than papered over.

**Tooling reliability, and it matters to every number below.** Earlier in this session a tool layer rewrote several of my commands and returned output attributing work to git objects that do not exist. Four SHAs appeared in that output, `9274f7d`, `c1882f1`, `742390c`, `8443b84`, and `git cat-file -e` fails for all four, so none exists. Two file rewrites also reported success while the file on disk did not change. Consequently every claim in this document was produced by one of three methods: the Read tool on the file itself, `git grep` or `git ls-files` whose result I cross-checked against a second command, or a test run whose output is reproducible. No verdict in section 2 rests on a single unrepeated shell echo.

**Commands run.** `Read` on `StoreCharacterCards.php`, `StoreRaceCatalogSlots.php`, `SourceDocumentSeeder.php`, `SkillSeeder.php:55-82`, `SourceFetcher.php:140-170`, `CatalogController.php:214-236`. `git grep` for `DB::transaction`, `Cache::lock`, `FetchSourceJob`, `maxRedirects`, `retry(`, `catalog:version`, `is_manual`, `cap_speed`, `cache.ttl`, `seed_file`, `1200`. `git ls-files --error-unmatch` on six paths. `git log` for last-touch per finding file. `git show HEAD:` for ref-state comparisons. `php artisan test --compact` and one filtered run.

**Not verified, and why.** The runtime behaviour of the redirect posture (F-9) was not tested against a live source, because that means a network fetch and the fence keeps this pass offline. The concurrency windows in F-7 and E-6 were reasoned from code shape, not reproduced with parallel requests. The test-honesty axis could not be exercised on the fixes, because no fix added a test; see section 5.

#### 2. Verification table

| ID | Verdict | Evidence, current | Reasoning |
|---|---|---|---|
| F-1 | **NOT FIXED** | `StoreCharacterCards.php:35-108` loops over records with `CharacterCard::create` at `:97` and `->update` at `:103`, no `DB::transaction`, no `DB` import in the use list. `StoreRaceCatalogSlots.php:28-55` same shape | Both halves unchanged |
| F-2 | **NOT FIXED** | `SourceDocumentSeeder.php:80-91` still catches `Throwable`, logs, warns, continues | Identical behaviour |
| F-3 | **NOT FIXED, both halves** | `SkillSeeder.php:75-80` delete has no `is_manual` predicate. `updateOrCreate` at `:61-68` writes null over imported rows | Both halves still present |
| F-4 | **NOT FIXED** | No unique constraint on `data_sources` or `match_candidates`. Write sites unchanged | Growth still unbounded |
| F-5 | **NOT FIXED** | `SourceFetcher.php:57` still date-scoped path; no content-hash check | Short-circuit still missed |
| F-6 | **NOT FIXED** | `FetchSourceJob` only hit is its own class declaration. Nothing dispatches it | Still dead code |
| F-7 | **NOT FIXED** | `storeTurn` and `storeRace` write multiple rows with no transaction | Non-atomic multi-write persists |
| E-3 | **NOT FIXED** | Six untracked files confirmed not in git. Was never added | KI-49 and KI-51 remain open |
| F-8 | **NOT FIXED** | Only `UmaFetch.php:55` uses `Cache::lock`. `UmaReparse` has none | Reparse writes unlocked |
| F-9 | **NOT FIXED** | `SourceFetcher.php:167` still `maxRedirects(2)`. No post-redirect host assertion | SSRF posture unchanged |
| F-10 | **NOT FIXED** | `CatalogController.php:233` still `Cache::remember('catalog:version', 3600, fn () => 0)`. Writer does `Cache::add` then `Cache::increment` | Counter collision window |
| E-1 | **NOT FIXED, doc side** | `ARCHITECTURE.md:276` still claims exponential backoff; code uses flat 500ms | Doc not corrected |
| E-8 | **NOT FIXED** | Factory `numberBetween(0, 1200)`; `stat-band.blade.php:189` literal 1,200; scenario caps unused | All four sites unchanged |
| E-9 | **NOT FIXED, still skipped** | 2 skipped tests unchanged | D-288 and G-18 still don't execute |
| C-3 | **NOT FIXED** | Five `config/uma.php` lines still claim cache TTL bounds fetch load | Comment credit wrong |
| C-4 | **NOT FIXED** | `Scenario::cap_*` never read outside model declaration and parser | Table holds fetched truth app doesn't consult |
| E-2 | **NOT FIXED** | `UmaFetch` returns `FAILURE` on abort; `SourceDocumentSeeder` catches and continues | Asymmetry intact |
| E-10 | **NO CHANGE REQUIRED** | `composer.json:49` still runs bare `php artisan serve` concurrently | State recorded |

**Tally:** 0 FIXED, 17 NOT FIXED, 1 no-change, 0 REGRESSED.

#### 3. New findings

**N-1, High:** `F-1` and `F-2` naming collision — a commit `4e17997` uses "F-1, F-2" for unrelated UI fixes. A false `FIXED` verdict possible if searching history by label.

**N-2, Medium:** Three files (`SourceDocumentSeeder.php`, `ReadsCommittedSource.php`, `UmamusumeRosterSeeder.php`) are untracked — no committed state to verify against.

**N-3, Medium:** Source fetch failure returns `null` silently at the fetch layer (`SourceFetcher.php:149-155` catches `RequestException` → `null`; combined with `->retry(..., throw: false)`, exhausting retries yields null with no log line).

**N-4, Medium:** Version key resets mid-life — `Cache::add('catalog:version', 0, 3600)` then `Cache::increment`. Page cache keys embed the version number, and version 1 of hour 2 reuses the key namespace of version 1 of hour 1, serving stale pages as fresh.

#### 4. Doc alignment

| Doc | State | Match |
|---|---|---|
| `ARCHITECTURE.md:272, :279` | Still present `FetchSourceJob` | Nothing dispatches the job |
| `ARCHITECTURE-ESSENTIALS.md:49` | Lists `ShouldBeUnique FetchSourceJob` | Same, not dispatched |
| `ARCHITECTURE.md:276` | Claims exponential backoff | Flat 500ms in code |
| `SkillSeeder.php:30-38` docblock | Explains cost/type left for import | Matches intent, contradicts effect — nulls imported values |

#### 5. Not-a-finding checks, and the six axes

**Not-a-finding, confirmed clean.** `StoreSupportEffects.php:19-23` still discloses the missing `is_manual` guard.

**Six axes:** Correctness (not assessable, no fix landed). Regression (none — suite returns 1003 passed, 2 skipped, 16,404 assertions, byte-identical to previous audit). Scope creep (not assessable). Test honesty (not assessable — no test forces mid-batch throw). Doc alignment (section 4). Recovery (N-3 is the only new failure mode).

#### 6. Skills invoked, and the refusals

Resolved from roster: `doubt-driven-development`, `source-driven-development`, `code-review-and-quality`, `debugging-and-error-recovery`, `security-and-hardening`, `performance-optimization`, `test-driven-development`, `laravel-best-practices`, `testing-best-practices`, `antislop-copywriting`, `gstack:careful`, and `gstack:codex`.

**Not resolvable:** `infer-conventions` exists on disk but not in session roster.

**Declined:** `gstack:codex` — with zero FIXED verdicts, nothing to second-guess.

#### 7. Severity tally

| Bucket | Original findings | New findings |
|---|---|---|
| Critical | 3 (F-1, F-2, F-3), all open | 0 |
| High | 5 (F-4–F-7, E-3), all open | 1 (N-1) |
| Medium | 6 (F-8–F-10, E-1, E-8, E-9), all open | 3 (N-2, N-3, N-4) |
| Low | 4 (C-3, C-4, E-2, E-10) | 0 |
| Fixed | **0** | 0 |

#### 8. What this pass did not do

No file other than this one was written. No source file, view, controller, model, migration, test, config, PRD, ADR, or DESIGN.md was edited. Nothing was committed or pushed. No network fetch was attempted.