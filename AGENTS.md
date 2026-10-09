# AGENTS.md

Operational contract for coding agents working in this repository. It answers: how to
orient, what may not be touched, what must be run, and what "done" means here. It is not
a product spec, an architecture document, or a tutorial. Those live in the files named in
§3 and are linked rather than restated.

Read `CONSTRAINTS.md` before writing code. It is a pointer stub now; the binding quality
bar (the C-1..C-9 table, the Floor list, the C-4 lore rules, and the hand-off verification
sequence) lives in `docs/research-scratch/GOVERNANCE.md`, section "CONSTRAINTS.md (root
quality bar, C-1 to C-9)". Do not weaken a threshold to make a change pass.

## 1. What this repository is

**Trainer Desk**, a local-only, single-Trainer Laravel 13 tool for the Global English
release of *Umamusume Pretty Derby*. It consolidates four legacy applications: three
trackers plus the `uma_musume_race_planner` career-run planner (rev 0.2, repo #4).

- **No auth surface, no multi-user support, no public deploy.** It runs on loopback and
  must not be exposed (`PRD.md` §6.10). No hosting, cloud, or deployment path exists.
- **SQLite only** (`database/database.sqlite`, WAL + `busy_timeout`). The framework
  `users` table and `User` model are unused defaults.
- **Web surface is Inertia + Vue 3** (`inertiajs/inertia-laravel`, `@inertiajs/vue3`, TypeScript,
  Tailwind CSS v4, Vite), with 51 single-file components under `resources/js/`. Blade survives only
  as the `resources/views/app.blade.php` shell and `resources/views/errors/`; there are no Blade view
  components. JSON is a read-only `/api/v1` surface (P2, three controllers).
  *Dated correction 2026-10-08 (documentation-sync pass): the "51 single-file components" figure is
  superseded — the tree now carries 58 single-file components under `resources/js/components/` and 54
  page files under `resources/js/pages/`, after the Phase D–E career, Veterans and Database sets
  landed. Counts like these age the moment a slice lands, so this is a snapshot note, not a number to
  keep in step; what holds is the shape: the career set and the scenario panels are Vue, and
  `resources/views/components/` is empty since slice B1.*
- **Data arrives through a stage-isolated fetch engine** that cross-references JP and
  Global catalog sources, snapshots each body, and promotes engine-owned facts with
  provenance.
- **No CI.** There is no `.github/workflows/`, so every gate in §9 runs locally, by an
  agent or the human owner, and its output is the only evidence.

## 2. Instruction scope and precedence

Layers, highest first. A lower layer never overrides a higher one; where a lower layer
disagrees, the higher one wins and the lower one is stale.

| Layer                       | Where                                                                                               | Authority                                                                                    |
| --------------------------- | --------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------- |
| Human owner instruction     | the task itself                                                                                     | Highest, but it cannot relax the quality bar without a written owner ruling (escalation 4)   |
| Quality bar                 | `CONSTRAINTS.md` -> `GOVERNANCE.md` §CONSTRAINTS.md                                                 | Never weakened to pass a check                                                               |
| Gate registry               | `GOVERNANCE.md` §GATE-REGISTRY.md                                                                   | How each gate runs, its evidence, its allowed exceptions                                     |
| Binding decisions           | `docs/adr/0001`-`0018`, `SUPERSEDED-*`                                                              | Accepted ADRs bind; `Proposed` ones do not                                                   |
| Product truth               | `PRD.md`                                                                                            | Users, requirements, §6 non-goals                                                            |
| System design               | `ARCHITECTURE.md` over `ARCHITECTURE-ESSENTIALS.md`                                                 | The digest loses to the full text                                                            |
| Screen behavior             | `SCREEN_SPEC.md` §8 authority table                                                                 | Per-screen states and workflows                                                              |
| Visual system               | `DESIGN.md` (+ folded corpus `docs/research-scratch/DESIGN-CORPUS.md`)                              | Tokens, components, motion                                                                   |
| Path-scoped rules           | `.ai/rules/index.md` -> `code-style.md`, `eloquent.md`, `architecture.md`, `testing-standards.md`   | Conventions for the globs they cover                                                         |
| Coding rules                | `CLAUDE.md`                                                                                         | Assistant coding rules, lore gate detail                                                     |
| Slice plans and records     | `docs/research-scratch/PROCESS-PLANS.md`, `SLICE-RECORDS.md`, `PLANS-AND-BRIEFS.md`                 | Local to a slice; cannot override a gate                                                     |
| Tooling-injected guidance   | the `<laravel-boost-guidelines>` block Laravel Boost appends to this file                           | Generic framework advice                                                                     |

Two rules that settle most disputes:

- **When documents disagree:** quality bar > gate registry > ADRs > `DESIGN.md` > slice
  plans. `ARCHITECTURE.md` beats its digest. `PRD.md` is product truth. Record the
  conflict (`SCREEN_SPEC.md` §7, or an ADR erratum); do not resolve it silently.
- **When code and a written rule disagree:** the code wins and the rule is stale
  (`.ai/rules/index.md`). Say so in the report; do not edit the rule to match.

Laravel Boost's injected block is generic and partly wrong for this repository: it asks
for policies, `Auth::user()`, Laravel Cloud deployment, and a `User` model in active use.
None of that exists here. Repository rules win on every such point.

## 3. Documentation map

Root documentation is a closed standing set (`docs/research-scratch/INDEX.md` §File
discipline). **No new markdown file is created in the repository root**, and new
documentation content goes into the relevant master under `docs/research-scratch/` or
`KNOWN-ISSUES.md`. Documentation files are written or updated only when the task asks
for it.

| Document                                                               | Owns                                                                                                                                                                                                    | Does not own                                              |
| ---------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------- |
| `PRD.md`                                                               | users, stories, FR-A..FR-E, NFRs, §6 non-goals, open questions                                                                                                                                          | implementation detail                                     |
| `ARCHITECTURE.md`                                                      | schema, pipeline, API contract, security model, testing strategy                                                                                                                                        | visual design                                             |
| `ARCHITECTURE-ESSENTIALS.md`                                           | token-efficient digest of the above                                                                                                                                                                     | anything the full text contradicts                        |
| `DESIGN.md`                                                            | visual system: tokens, components, surfaces, motion, lore-sensitive iconography                                                                                                                         | screen behavior (that is `SCREEN_SPEC.md`)                |
| `SCREEN_SPEC.md`                                                       | screen inventory, per-screen states, workflows, §8 authority table                                                                                                                                      | schema                                                    |
| `CONSTRAINTS.md`                                                       | a pointer to the bar                                                                                                                                                                                    | any rule of its own (adding one is forbidden)             |
| `GOVERNANCE.md` (§CONSTRAINTS.md, §GATE-REGISTRY.md, §PRE-MORTEM.md)   | the bar, the gate table, allowed exceptions, risk record                                                                                                                                                | product intent                                            |
| `KNOWN-ISSUES.md`                                                      | live defect register, `KI-nn`                                                                                                                                                                           | history (that is `AUDIT-AND-VERIFICATION.md`)             |
| `PLAN.md`                                                              | a pointer to `PROCESS-PLANS.md`                                                                                                                                                                         | new slices (do not add any)                               |
| `README.md`                                                            | onboarding, commands, route and doc tables                                                                                                                                                              | governance                                                |
| `docs/adr/README.md`                                                   | ADR index, derived; regenerate with the command in that file                                                                                                                                            | hand-edited index rows                                    |
| `docs/UMAMUSUME_REFERENCE.md`                                          | source-cited mechanics corpus, eight sections, dated live-ops snapshots                                                                                                                                 | a write-up that cites this repo is not a second source    |
| `docs/scenarios/01`-`09`                                               | per-scenario playing guides (`07` is the sourced guide for the fourth `[Global]` scenario, with its in-scenario client vocabulary still `❌ UNVERIFIED`; `08` is `[JP-Only]` and must not be imported)  | shipped copy                                              |
| `PRODUCT.md`, `SKILL.md`                                               | generated tooling artifacts                                                                                                                                                                             | hand edits; the generator wins                            |
| `docs/deprecated/`                                                     | retired legacy PDFs                                                                                                                                                                                     | not a source, not a spec; never copy their display text   |

## 4. Roles and escalation

The role table says who decides. Adopt the role that owns the change.

| Role                        | Owns                                                                                                    |
| --------------------------- | ------------------------------------------------------------------------------------------------------- |
| Architect                   | schema, system design, API shapes, performance budgets, `ARCHITECTURE*.md`                              |
| Data Engineer               | `app/Services/DataPipeline/`, `config/uma.php`, parsers, snapshots, `match_candidates`                  |
| Laravel Dev                 | controllers, Form Requests, Resources, routes, views, factories, seeders                                |
| QA / Reviewer               | the gates in §9, the Floor, doubt-driven review of non-trivial diffs                                    |
| Lore Guardian               | every user-visible string, identifier, seed value, and doc line in a change                             |
| Docs Writer                 | README currency, ADRs, doc currency after a change                                                      |
| Planner Domain Specialist   | the training-run domain: plan-vs-actual, turn/skill states, stat bounds, export, timezone correctness   |

Escalation paths (mirrored in `GOVERNANCE.md` §13):

1. Role-level conflict (a schema change is needed mid-feature) -> Architect decides inside PRD scope.
2. Scope conflict (feature absent from the PRD, or a §6 non-goal requested) -> Architect escalates to the human owner. Agents never widen scope.
3. Lore ruling dispute -> the Lore Guardian's verdict stands; only the human owner overrides, in writing.
4. Threshold relaxation -> QA / Reviewer escalates to the human owner. Never edit the bar to pass a check.
5. Fetch-source legality or robots.txt uncertainty -> Data Engineer stops and escalates before adding the source.
6. Review disagreement after three doubt cycles -> surface both positions to the human.
7. A planner feature that implies a cut repo #4 system (simulation, snapshots, predictions, dual storage) -> Planner Domain Specialist -> Architect -> PRD §6 -> human owner.

## 5. Non-negotiables

**Lore gate (blocking, every role).** The characters are Umamusume, a humanoid race. The
banned character vocabulary and banned framing are defined in the bar's C-4 section and
implemented by `tools/lore.php` (`composer lore`, `composer lore-code`) and the `Makefile`
targets. Rules that matter in practice:

- The ban governs **copy and framing**. It does not reach dataset or export keys
  (`intelligence`, `friend`), a mechanic that shares a word, or a verbatim skill, race, or
  card name kept as source data. Those are gated on the **display path**, never by editing
  the data, and **dataset keys must not be renamed** to satisfy the list: the ingest join
  depends on them.
- The grep proposes, the Guardian decides. Four allowed hit classes exist (naming the list
  to forbid it, substrings inside ordinary words, verbatim quoted source data, a gate's own
  pattern source). Each remaining hit needs a one-line context ruling in the hand-off.
- A `lore-ignore-line` marker is valid in `docs/` only, records an existing ruling, and is
  not itself a ruling (`LoreGateParityTest` fails on one placed elsewhere).
- `lore-code` is additive: it scans untracked files and the Global client terminology. The
  banned glosses are the wiki-side words (for Wit and Mood among others); the Global client
  words are the ones the UI may print.

**The Floor (never, in any change).** No new suppressions (`@phpstan-ignore`, `eslint-disable`,
`@ts-ignore`, `# noqa`), no stub bodies (`not implemented`, empty `catch {}`, TODO
placeholders), no deleted or skipped test without owner approval and a stated reason, no
engine write to a row with `is_manual = true`, no fact stored without provenance, no fetch
URL outside the `config('uma.sources')` allowlist, no business logic in a controller, no
inline `$request->validate()`.

**Copy.** No em dashes in shipped copy. A value a run has not recorded renders as `N/A`
with a `title`, never a default and never a dash. No AI buzzwords, no fabricated claims,
no invented statistics or dates: every doc statement traces to code, the PRD, or a cited
source.

**Dependencies.** No new package without human approval; `composer audit` and
`npm audit --omit=dev` clean of reachable critical/high before a build is tagged.

## 6. Repository structure

```text
app/Actions/                 one-off operations (PromoteMatchedRecord, ResolveMatchCandidate,
                             ImportHistoricalRun, Store* ingest actions)
app/Services/DataPipeline/   SourceFetcher (the only outbound HTTP), NameNormalizer,
                             CrossReferenceMatcher, PipelineRunner, Parsers/, Contracts/
app/Services/                ScenarioCaps (the one stat-ceiling owner), SupportCardEffects,
                             Skill{Registry,Matcher,Executor} (separate tooling layer)
app/Console/Commands/        UmaFetch, UmaReparse, UmaBackup, UmaImportSupportCards, ManageSkills
app/Enums/                   TitleCase cases; the DB stores the backed value
app/Http/                    Controllers (+ Api/V1), Requests, Resources
app/Models/                  catalog, trainer-data, and support-card entities, TurnEvents/ payloads
config/uma.php               fetch allowlist, politeness, thresholds, cache TTL, display timezone
config/scenarios.php         the ONLY place scenario names enter the layout path
database/                    migrations, a factory per model, seeders, seeders/data (committed bodies)
resources/views/             4 Blade files only: app.blade.php (the Inertia shell) + errors/
resources/js/                TypeScript + Vue 3: spa.ts, bootstrap.ts, types.ts, pages/ (18),
                             components/ (31, incl. catalog, legacy, review, support), layouts/ (2)
lang/en/uma.php              displayed vocabulary and the Global terms map
routes/                      web.php (browser), api.php (JSON)
tests/                       Feature/ (the bulk), Unit/ (no database), Fixtures/ (stored bodies)
tools/                       lore.php (lore gate), gate.py (design-artifact gate), doc_census.py,
                             roster-crosscheck.php, check_untracked.py, dev-logs-pane.php
docs/                        adr/, scenarios/, proposals/, research-scratch/ masters, deprecated/
```text

## 7. Coding conventions

Read `.ai/rules/index.md` first and open every rule file whose glob covers the path you
touch. The short form, all of it enforced by Pint (`laravel` preset) and PHPStan level 6:

- `declare(strict_types=1);` on line one of every PHP file; explicit parameter and return
  types everywhere; constructor property promotion.
- `#[Fillable]` and `#[Hidden]` attributes, never the legacy properties; `HasFactory` plus
  a factory per model and `@use HasFactory<XFactory>`; casts declared as a `casts()` method,
  never a `$casts` property.
- TitleCase enum cases in `app/Enums/`.
- Thin controllers; logic in `app/Actions` or `app/Services`; Form Requests for every
  write and search; API Resources for JSON; named routes and `route()` everywhere.
- `config()` in app code; `env()` only inside `config/`. Create files with
  `php artisan make:* --no-interaction`.
- Enums live in `app/Enums/`; browser routes in `routes/web.php`; JSON under
  `app/Http/Controllers/Api/V1/` declared in `routes/api.php`.
- No soft deletes, no DB-level enum columns, no Livewire, no Redis, no Excel. **"No Inertia/SPA" is
  withdrawn**: the Trainer Desk 2.0 line made Inertia + Vue 3 the shipped web surface (§1). The other
  three stay banned and are still absent from `composer.lock`.
- Formatting: `.editorconfig` (4 spaces, LF, single quotes, trailing commas, final
  newline), PHPDoc over inline comments, array shapes in PHPDoc, curly braces always.
- `config/scenarios.php` is the only source of scenario names in the layout path; the
  scenario-driven components (`ResourceStrip.vue`, `StatBand.vue`, `RaceCalendar.vue`,
  `GuidedStep.vue`, `GradePointMeter.vue`) take the **resolved label** from the server as a required
  prop with **no default** (`scenarioLabel: string`, `ResourceStrip.vue:14`) rather than a key they
  look up themselves. A scenario name hardcoded in a page or component is a defect.
- Design tokens only (`bg-page`, `text-ink`, `border-rule`, ...). Zero `dark:` utilities,
  zero skeleton palette classes.

## 8. Architecture rules an agent must know before editing

Pipeline stages are isolated: `fetch -> snapshot -> parse -> normalize -> match -> promote | review`.

- **Fetch**: `SourceFetcher` is the only outbound HTTP path; hosts are allowlisted in
  `config('uma.sources')`. A URL outside that list is an SSRF-floor violation.
- **Snapshot**: the raw body is stored gitignored under `storage/app/private/snapshots`,
  hashed. An unchanged hash short-circuits the run and writes nothing.
- **Parse / normalize**: one parser class per source implementing `Contracts\SourceParser`.
  `NameNormalizer` is pure (NFKD, lowercase, separators and combining marks stripped);
  display names are never mutated.
- **Match**: Exact and Alias tiers auto-promote; Fuzzy (above
  `config('uma.match.fuzzy_threshold')`) and None land in `match_candidates` for `/review`.
- **Promote**: upserts engine-owned columns, **skips any row with `is_manual = true`**,
  writes one `data_sources` provenance row per fact, inside `DB::transaction`.
- **Artwork** (`ADR-0021`, accepted 2026-10-05; **both halves built** — the display half was recorded
  here as unbuilt until 2026-10-06, and it is not): `uma:fetch-art`
  reads ids from `character_cards.card_id` and `support_cards.support_id`, requests them from the asset host
  declared in `config('uma.sources')`, and writes files under gitignored `storage/app/private/artwork/` with a
  sibling `manifest.json`. Nothing enters the database and no `data_sources` row is written. `uma:fetch` steps
  over any source entry that declares no parser, which is how the asset host stays allowlisted without being
  parsed. Same allowlist rule as Fetch, not a second one; `DESIGN.md` §4.7 says how an absent file renders, and
  `PRD.md` OQ-6 has placed the slots: `resources/js/components/ArtworkSlot.vue` renders `card_portrait` on the
  catalog index and the trainee detail and `support_thumb` on the support-card index and detail, with the deck
  picker carrying a thumb as a sixth surface that `DESIGN.md`'s component table does not list yet. Files reach
  the browser through the loopback `artwork.show` route and never from the asset host (§7). It is manual:
  nothing schedules it, **and an unrun mirror renders nothing at all**, so a fresh worktree shows no frames
  until someone runs it; on this tree that pass resolved 665 ids with zero unresolved. Still open under OQ-6:
  the pre-run pick screen cannot host a frame while its trainee picker is a native `<select>`, and skill icons
  have no stored column.

Caching: catalog reads use `Cache::remember` with a `catalog:version` counter bumped on
promotion. Trainer-data reads are never cached. `ScenarioCaps` is the single owner of
per-stat ceiling arithmetic; where a validator and a renderer both read a number, both must
call it with the same argument (`ADR-0015`, and the rule generalized in `docs/adr/README.md`).

The `Skill{Registry,Matcher,Executor}` trio and `php artisan skill:manage` are a separate
tooling layer (`.agents/`, `docs/SKILL_AUTOMATION.md`). They touch no catalog or run table.

Full detail: `ARCHITECTURE.md`. Digest: `ARCHITECTURE-ESSENTIALS.md`.

## 9. Testing and validation

Pest 4, feature-first. One global binding in `tests/Pest.php` applies `TestCase` plus
`RefreshDatabase` and calls `withoutVite()` for `tests/Feature`, so **never** add a
`uses()` or `withoutVite()` to an individual test file. `tests/Unit` runs with no database.

- Build models from factories; check for factory states first.
- Fetcher and pipeline tests use `Http::fake` with the stored bodies in `tests/Fixtures`.
  **No test touches the network.**
- Prefer facade fakes (`Bus::fake`, `Queue::fake`, `Mail::fake`) for framework services;
  Mockery only for custom or external dependencies.
- Cover the changed behavior and its important failure modes, and nothing beyond them.
- Do not delete or skip tests without owner approval.

**Cost-aware escalation: use the cheapest verification that gives sufficient confidence for
the change, and widen only as scope and risk grow.** The full suite is a checkpoint and a
gate; it is not the normal implementation feedback loop. The shape of this policy is
repo-agnostic; the commands that implement it here are the table below, §10, and the
hand-off sequence.

- **Level 1, focused (during active implementation).** Run only what directly covers the
  code being changed: one test file, class, case, filter, tag, or the equivalent for this
  repository's runner; for UI work, the smallest relevant browser/e2e spec. Do not
  automatically run the complete suite after every edit, and do not make a small localized
  change wait on an expensive unrelated suite when productive work can safely continue.
- **Level 2, related regression (at the end of a coherent slice).** Re-run the affected
  tests, then the nearby suites that share the changed behavior: related integration tests
  and, when the change touches those paths, the browser/e2e specs covering them.
- **Level 3, full verification (checkpoints).** Run the complete applicable suite,
  including browser/e2e where appropriate, at these moments: a coherent feature or slice
  checkpoint, before declaring substantial work complete, before a release or merge
  milestone, after a major refactor or any change that crosses layers, when explicitly
  requested, or when the change is too broad for targeted tests to be conclusive. In this
  repository, Level 3 is the hand-off sequence below.

When the full suite is expensive, repeated broad runs are not extra diligence: a focused
run that covers the change gives the same signal at a fraction of the cost. Agents should
discover, before testing, the normal full command, the focused commands and filtering
mechanisms, the browser/e2e commands and whether they can target individual specs, and any
CI or test documentation that already says this.

**Classify a failure before re-running.** When broader testing reveals failures, attribute
each one first: a regression from the current change, a pre-existing failure, an unrelated
failure, an infrastructure or environment failure, a flaky non-deterministic failure, or
inconclusive. Reproduce a suspicious failure with the focused command for that single test
rather than rerunning the whole suite repeatedly; record pre-existing and unrelated
failures (e.g. `KNOWN-ISSUES.md`) rather than fixing or deleting them in passing. Never
claim the full suite passed unless it was actually executed and passed: the evidence rule
below applies at every level.

This escalates verification, it does not license skipping it. No level permits weakening
assertions, deleting or suppressing tests, or skipping a Level 3 gate that the change class
in the table below requires.

What to run, by class of change:

| Change                                              | Minimum                                                                                                                                                                               |
| --------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Copy, layout, or styling only                       | the affected feature test(s); no new test needed                                                                                                                                      |
| Logic in a model, action, or service                | the narrow test file, then `php artisan test --compact`                                                                                                                               |
| Route, controller, Form Request, or view behavior   | the feature tests for that screen; `SCREEN_SPEC.md` state coverage must hold                                                                                                          |
| Fetch pipeline or a parser                          | the parser tests plus the fetch/pipeline tests, `Http::fake` only                                                                                                                     |
| Schema                                              | migration plus the digest that travels with it (§11), then a fresh migrate and seed on a scratch DB, **and `php artisan migrate:status` clean on the dev database before hand-off**   |
| TypeScript, Vue, or Blade assets                    | `npm run typecheck`, and `npm run build` if a page references assets                                                                                                                  |
| Anything crossing layers                            | the full suite, then the hand-off sequence below                                                                                                                                      |

Hand-off sequence (the bar's own order): targeted tests green -> `php artisan test --compact`
-> `vendor/bin/pint --dirty --format agent` -> `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`
-> `npm run typecheck` -> `composer lore` and `composer lore-code` with a ruling per hit ->
`composer audit` and `npm audit --omit=dev` when dependencies changed.

**The suite cannot see the dev database.** `phpunit.xml` forces `DB_DATABASE=:memory:`, so every test
builds its own schema from the migration files and a green run proves nothing about
`database/database.sqlite`. Four committed migrations sat Pending there for four days while 1,326 tests
passed (`KNOWN-ISSUES.md` KI-60); the landing page and `/legacy` were returning 500 the whole time. Run
`migrate:status` as part of the hand-off whenever a change reads a table a migration creates.

Evidence rule: a claim is the command output. "Tests pass" means the run output is in the
hand-off; a gate that was not run is reported as not run.

## 10. Commands

Every command below exists in `composer.json`, `package.json`, or artisan on this tree.

| Task                                             | Command                                                                                     |
| ------------------------------------------------ | ------------------------------------------------------------------------------------------- |
| First-time setup                                 | `composer setup` (install, `.env`, key, migrate, npm install + build)                       |
| Dev (serve + queue + logs + Vite HMR)            | `composer dev`                                                                              |
| Full pipeline (config:clear, typecheck, suite)   | `composer test`                                                                             |
| Tests, narrowest first                           | `php artisan test --compact --filter=Name`, or `vendor/bin/pest tests/Feature/XTest.php`    |
| Style fix / style check                          | `vendor/bin/pint --dirty --format agent` / `composer lint`                                  |
| Static analysis                                  | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`                                |
| TypeScript                                       | `npm run typecheck`                                                                         |
| Assets                                           | `npm run build`, `npm run dev`                                                              |
| Lore gate                                        | `composer lore`, `composer lore-code`                                                       |
| Fresh DB with offline catalog data               | `php artisan migrate:fresh --seed` (destructive, §11)                                       |
| Fetch / replay / import / backup                 | `php artisan uma:fetch`, `uma:reparse <source>`, `uma:import:support-cards`, `uma:backup`   |
| Mirror catalog artwork (manual only)             | `php artisan uma:fetch-art` (`--kind`, `--dry-run`, `--refetch`)                            |
| Routes / commands / config                       | `php artisan route:list`, `php artisan list`, `php artisan config:show uma.sources`         |
| Documentation census                             | `composer docs` (python)                                                                    |

**Destructive:** `php artisan migrate:fresh --seed` drops every table in
`database/database.sqlite` and destroys local Trainer data; back up with
`php artisan uma:backup` first, or point `DB_DATABASE` at an empty scratch file. It needs
explicit owner approval when run against the shared dev file.

**Unavailable:** GNU `make` does not run on this host (KI-4). The `Makefile` targets are
documentation; run the underlying `composer` script or vendor command instead.

## 11. Change-safety

- **Schema.** A migration travels with an updated `ARCHITECTURE-ESSENTIALS.md` digest, a
  PRD citation (`FR-x` / `US-x`), and a decision recorded as an ADR. Every migration has
  a working `down()`. Verify on a scratch database, not the shared dev file.
- **Docs and ADRs.** A dated claim that later proves wrong is corrected by appending a
  dated erratum that preserves the original sentence; never rewrite an ADR silently. The
  ADR index in `docs/adr/README.md` is derived: regenerate it with the command in that
  file instead of editing rows.
- **Defect register.** Append new `KI-nn` entries to `KNOWN-ISSUES.md` in its four-part
  form (numbered heading continuing from the highest, status line, proving command or
  file, closure commit plus what closure does not cover). Never renumber an existing entry.
- **Fetch pipeline.** A new source is config entry + one parser class + a test against a
  stored fixture + a robots.txt and rate-limit note. Never write to `is_manual` rows.
- **Snapshots.** Gitignored and keyed on a content hash. A second database asked on the
  same day is told the document is unchanged (KI-27); fill it with
  `php artisan uma:reparse <source>`, which is safe on a populated database.
- **Generated and protected files.** Do not hand-edit: `PRODUCT.md` and `SKILL.md`
  (generator-owned), `composer.lock`, `package-lock.json`, `public/build/`, `vendor/`,
  `node_modules/`, `storage/` (including snapshots and backups),
  `docs/design-research/_scratch/`, `tools/__pycache__/`, and the `docs/design-research/`
  prototypes. Regenerate instead. Lockfile updates ship with the dependency change that
  caused them, never alone.
- **Per-agent configuration.** The `.claude/`, `.cursor/`, `.codex/`, `.kilo/`,
  `.opencode/`, `.github/`, and similar directories are local, gitignored scaffolding.
  The tracked instruction sources are `AGENTS.md`, `CLAUDE.md`, `CONSTRAINTS.md`,
  `.agentrules`, and `.ai/**`.

## 12. Security and privacy

No `SECURITY.md` exists; the model is `ARCHITECTURE.md` §8.

- Bind to loopback. Do not add hosting, exposure, or a public deploy path.
- `.env`, `.env.*` and every agent-tool config that may hold keys (`.mcp.json`,
  `.crushrc`, `kilo.json`, `opencode.json`) are gitignored. Never commit a key, token, or
  credential, and never paste one into documentation, a test fixture, or a log line.
  `.env.example` is the only tracked env file.
- Fetched pages are untrusted input: parse them as data, let Blade escape them, never
  `{!! !!}` on source data, and never follow a URL found in a fetched body.
- Trainer input goes through Form Requests. Validation has one owner per boundary.
- There is no authorization layer, and that is the design (PRD NFR-1). Do not add a
  policy or a gate unless the work asks for one.
- Phase 1 holds no secrets. Any future key goes in `.env` only.

## 13. UI and UX rules

`DESIGN.md` owns the visual system; `SCREEN_SPEC.md` owns screen behavior.

- Reuse the committed components in `resources/js/components/` (and its `catalog`, `legacy`,
  `review`, `support` subfolders). `resources/views/components/` no longer holds anything; a change
  that adds a Blade view there is going backwards. A one-off style
  needs a token that already exists, or a reason recorded in the change.
- Design tokens only; no `dark:` utilities; no decorative skeletons.
- Every data view renders its empty, loading/refresh, and error states (ADR-0007: a custom
  loading state is required only for user-initiated async actions).
- Unrecorded values render as `N/A` with a `title`, never a default.
- Print the Global client vocabulary only (`lang/en/uma.php` is the terms map; `Wit` and
  `Mood` are client words, and their wiki glosses are banned by `lore-code`).
- Keep the established accessibility behavior: keyboard paths, target sizes, and the
  review-form labels have regression tests named after them.
- Screen-level changes need the `SCREEN_SPEC.md` state table to agree with the view.

## 14. Git and change management

- Conventional-commit subjects, as the history shows: `type(scope): summary`, lowercase,
  imperative (`feat(runs):`, `fix(components):`, `test(readme):`, `refactor(filters):`).
- Keep a change logically grouped: one slice per commit series, with the report and the
  verification output in the hand-off. The branch of record is `master`; there is no CI to
  catch a partial landing.
- Never commit `vendor/`, `node_modules/`, `storage/` snapshots or backups, `.env`, or
  `public/build/`.
- Deleting or skipping a test needs owner approval and a stated reason in the commit.
- There is no changelog or version file to bump.

## 15. Definition of done

A change is done when all of these hold, or when the exception is reported explicitly:

- [ ] The implementation matches the architecture and the conventions in §7 and §8.
- [ ] A behavior change ships with a test; copy-only changes ship without one.
- [ ] The narrow tests pass, and the broader suite plus the hand-off sequence in §9 were
      run for a cross-cutting change, with the output attached.
- [ ] `vendor/bin/pint --dirty --format agent` was run; PHPStan level 6 is clean; `npm run
      typecheck` is clean if TypeScript, Vue or Blade changed.
- [ ] The lore gate ran, and every hit carries a context ruling.
- [ ] No unrelated file changed; the diff was read before reporting.
- [ ] Documentation moved with the behavior: digest and ADR for schema, `SCREEN_SPEC.md`
      for screens, `DESIGN.md` for the visual system, `KNOWN-ISSUES.md` for a new defect.
- [ ] Security was considered: untrusted input, secrets, exposure, the SSRF allowlist.
- [ ] Known limitations and unrun gates are stated plainly, not implied away.

## 16. Handling ambiguity

Do not invent a requirement. When the repository does not settle a question:

1. Look for the answer in the owning document first (the §3 map says which one owns it),
   then in the code, then in an ADR or a recorded ruling.
2. Prefer the smallest behavior-preserving change the evidence supports.
3. Record the ambiguity where the repo records such things: `SCREEN_SPEC.md` §7 for
   screen-system conflicts, an ADR erratum for a dated claim, `KNOWN-ISSUES.md` for a
   defect.
4. Ask the owner only when proceeding either way would be unsafe, irreversible, or would
   widen scope. Autonomous work picks the conservative option and says so.

## 17. Legacy and migration guidance

- Four legacy applications were consolidated. The retired originals are in
  `docs/deprecated/`; they are not a specification, not a data source, and their display
  text must never be copied into app copy or documentation.
- `app/Models/Legacy/LegacySelectionPayload.php` (ADR-0010) is a compatibility payload
  for the planner import. It exists to read an old selection format; it is not the current
  run model.
- `app/Models/User.php` and the `users` table are unused framework defaults, kept because
  the framework expects them.
- Neither the oldest nor the newest-looking implementation is authoritative on its own:
  check the ADR status line (`Accepted`, `Accepted in part`, `Declined`, `Proposed`,
  `Superseded`) before coding against a decision. Source files folded into
  `docs/research-scratch/` masters were deleted after verification; cite the master and
  its section anchor, not the deleted original.
- Cut systems stay cut (PRD §6): no race-outcome prediction, no image uploads, no dual
  storage, no simulation. A request that implies one is escalation 7. Read "image uploads"
  precisely: it is a Trainer supplying a file, and `PRD.md` §6.13 still cuts it. Art the tool
  fetches itself by id from an allowlisted host is a different object, authorized by
  `ADR-0021` (2026-10-05), whose fetch half and display half are both built (§8).

## 18. Known traps

- `make` does not run on this host (KI-4); use the `composer` scripts.
- A pinned source URL can serve stale content because a withdrawn document still answers
  `200` (KI-24); `uma:fetch` warns when it falls back to the pinned URL.
- A snapshot short-circuit keyed on today's date can leave a second database empty while
  claiming "unchanged" (KI-27); use `uma:reparse <source>`.
- PHPUnit environment wins over `.env.testing`: `phpunit.xml` forces
  `DB_DATABASE=:memory:`. Keep the two in agreement.
- `umamusume.com/news/NNN` is a client-rendered shell: its HTML carries no text, and a fetch that returns HTTP 200 with an empty body is not evidence of an empty notice. The bodies come from `POST https://umamusume.com/api/ajax/pr_info_index?format=json` with `{"announce_label":0,"limit":100,"offset":N}` (rows in `information_list`, full `message` included) and from `pr_info_detail` with `{"announce_id":NNN}`; `umamusume.jp` is the same shape. Four entries in the reference guide recorded a notice title and date with "URL not recorded" for exactly this reason, until the API was found on 2026-10-05 (see `docs/UMAMUSUME_REFERENCE.md` §4.6). It is a source candidate, not yet an allowlisted one: adding it to the fetch engine needs the §11 new-source package and a robots and rate-limit note, and it must not be polled.
- A page that references built assets without a manifest needs `npm run build`.
- The asset host answers a miss with **27,150 bytes of `text/html` at HTTP 404**, so "bytes came back" is never the test. `SourceFetcher::fetchAsset()` returns null on any non-2xx and `ArtworkMirrorTest` keeps it that way; a build that stored the body of a 404 would put an HTML document in place of a PNG and render it as a broken frame forever.
- An empty artwork mirror is **invisible, not obviously broken**: `ArtworkSlot.vue` renders nothing for a
  null url (`DESIGN.md` §4.7), so a tree where nobody has run `uma:fetch-art` shows no frames and no error.
  Check `migrate:status`-style disk state first (`uma:fetch-art --dry-run` reports the id count and how many
  are already on disk without touching the network) before reading missing images as a frontend defect.
- PHPStan needs `--memory-limit=1G`; `composer analyse` omits it.
- The lore gate is blocking and easy to trip by accident, including inside this file: the
  banned word families are not written out in `AGENTS.md` on purpose. Read the pattern
  list in `tools/lore.php` when a ruling is needed.

## 19. Working style

Lazy means efficient, not careless. Before writing code, climb until the first rung
holds: does this need building; does it already exist here; does the standard library do
it; does a native platform feature cover it; does an installed dependency cover it; can
it be one line; only then write the minimum that works.

A bug fix is a root cause, not a symptom: grep every caller of the function you touch and
fix the shared function once. No unrequested abstraction, no avoidable dependency, no
unrequested boilerplate, deletion over addition, fewest files, shortest diff that is still
correct. Mark a deliberate simplification with a known ceiling using a short `ponytail:`
comment naming the ceiling and the upgrade path.

Never lazy about: understanding the real flow end to end, validation at a trust boundary,
error handling that prevents data loss, security, accessibility, and anything explicitly
requested. Non-trivial logic leaves one runnable check behind, the smallest thing that
fails if the logic breaks; a trivial one-liner needs no test.

## 20. Change log

| Date         | Change                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               | Reason                                                                                                                                                                                                                                                                                                                                                                                                                                      |
| ------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 2026-10-04   | Rewritten as an operational contract: added §2 precedence and the Boost-conflict note, §3 documentation map with the pointer stubs, §9 change-class validation matrix, §11 change-safety, §16 ambiguity handling, §17 legacy handling. Pointer stubs resolved to their masters (`CONSTRAINTS.md` -> `GOVERNANCE.md`). Escaped the banned word families so this file no longer produces lore-gate hits needing a ruling. Role table and the seven escalation paths preserved verbatim in substance.   | Agents were reading a stale file: it cited rules that had moved into `docs/research-scratch/` masters, repeated generic Boost guidance that contradicts the no-auth design, and quoted the banned vocabulary.                                                                                                                                                                                                                               |
| 2026-10-05   | One cell of the §3 documentation map: `docs/scenarios/07` is no longer described as a known-gap stub.                                                                                                                                                                                                                                                                                                                                                                                                | The owner asked for the fourth `[Global]` scenario to be researched and its documents updated; the primary read landed, so the map's own description of the file went stale. The §6 non-negotiables, the gates and the precedence chain are unchanged.                                                                                                                                                                                      |
| 2026-10-05   | §8 gains an **Artwork** bullet, §17's "no image uploads" line is disambiguated, and §18 gains a trap about the asset host's HTML 404 body.                                                                                                                                                                                                                                                                                                                                                           | `ADR-0021` was accepted the same day and `AGENTS.md` still read as though every image question ended at escalation 7. It does not: uploads stay cut, sourced artwork is authorized and unbuilt. An agent reading only this file would have refused work the owner had just authorized.                                                                                                                                                      |
| 2026-10-06   | §8's Artwork bullet and §17 corrected: **the display half is built**, and the bullet now names the six surfaces, the loopback-only `src` rule, and the two OQ-6 remainders. §18 gains the empty-mirror trap.                                                                                                                                                                                                                                                                                         | The row above and §17 both asserted the display half was unbuilt. It is: `ArtworkSlot.vue` plus `CatalogController.php:121,132,299`, `SupportCardController.php:65,110` and `TrainingRunController.php:1119`, and 128 frames were verified rendering in Chromium with zero console errors after the first `uma:fetch-art` pass. An agent reading only this file would have refused to debug an image question as if nothing displayed it.   |
| 2026-10-06   | §1, §6, §7 and §13 corrected for the frontend stack: the web surface is **Inertia + Vue 3**, `resources/views/` holds 4 Blade files and no components, `resources/js/` holds 51 SFCs, and the scenario components take a required `scenarioLabel` rather than declaring `scenario`. §7's "no Inertia/SPA" ban is withdrawn in place, §9 and §15 widen the asset and typecheck rows to Vue, and §18's Blade-manifest line is de-Bladed.                                                               | `8e58b65` folded the Trainer Desk 2.0 line into `master` and `0ea8d43` retired the Blade shell, so the file described a surface that no longer exists. Measured on this tree, not remembered. Per §2 the code wins and the rule is stale; the rule was left standing where it is still true (Livewire, Redis and Excel are absent from `composer.lock`) rather than deleted wholesale.                                                      |
| 2026-10-06   | §9: `migrate:status` on the dev database joins the Schema row and the hand-off, with a paragraph stating that the suite cannot see that file.                                                                                                                                                                                                                                                                                                                                                        | KI-60. Four committed migrations sat Pending on `database/database.sqlite` for four days while 1,326 tests passed, because `phpunit.xml:64` forces `DB_DATABASE=:memory:` and every test builds its own schema. A green suite was reported as evidence of a working application and it was not. This tightens the bar, which §5 permits an agent to apply; relaxing it remains escalation 4.                                                |
| 2026-10-08   | §9 gains the cost-aware escalation policy: Level 1 focused runs during implementation, Level 2 related regression at slice end, Level 3 full verification only at checkpoints; the full suite is a gate, not the feedback loop. Adds failure classification (regression, pre-existing, unrelated, infrastructure, flaky, inconclusive) with focused reproduction, the explicit rule that a full-suite pass may never be claimed unexecuted, and the statement that no level licenses weakening, deleting, or suppressing tests. Repo-agnostic in shape; the existing change-class table and hand-off stay binding as its Level 3. | Agents were burning most of their implementation time rerunning the expensive full and browser suites after small localized edits. The escalation ladder was implicit in §9 ("narrow first") but never written down, so nothing stopped the reflexive broad run or required attributing a broad-run failure before re-running. Concurrent-agent work in this tree also made unrelated failures common, which the classification step now handles explicitly. |
