# AGENTS.md

Operational contract for coding agents. Not a product spec or tutorial; those live in the
files named in §3 and are linked rather than restated. Read `docs/research-scratch/GOVERNANCE.md`
§"CONSTRAINTS.md (root quality bar, C-1 to C-9)" before writing code; do not weaken a
threshold to pass a check.

## 1. What this repository is

**Trainer Desk**, a local-only, single-Trainer Laravel 13 tool for the Global English release
of *Umamusume Pretty Derby*. Consolidates four legacy apps (three trackers + the
`uma_musume_race_planner` planner).

- No auth surface, no multi-user, no public deploy; loopback only (`PRD.md` §6.10).
- SQLite only (`database/database.sqlite`, WAL + `busy_timeout`); framework `users`/`User` unused.
- Web surface: Inertia + Vue 3 + TS, Tailwind v4, Vite. Blade only as `app.blade.php` shell + errors.
  JSON is read-only `/api/v1` (3 controllers). ~119 components / ~62 pages under `resources/js/`.
- Data via stage-isolated fetch engine (JP↔Global cross-ref, snapshots, provenance on promote).
- No CI; every gate in §9 runs locally and its output is the only evidence. Three build-only GitHub
  workflows exist (`.github/workflows/`); none runs a §9 gate.

## 2. Instruction scope and precedence

Higher wins; code wins over a stale rule (`.ai/rules/index.md`).

| Layer | Where | Authority |
|---|---|---|
| Human owner | task | Highest; cannot relax quality bar without written ruling |
| Quality bar | `docs/research-scratch/GOVERNANCE.md` §CONSTRAINTS.md | Never weakened |
| Gate registry | `GOVERNANCE.md` §GATE-REGISTRY.md | How gates run |
| Binding decisions | `docs/adr/0001`-`0027` | Accepted binds; Proposed does not |
| Product truth | `PRD.md` | users, FRs, §6 non-goals |
| System design | `ARCHITECTURE.md` over `ARCHITECTURE-ESSENTIALS.md` | digest loses |
| Screen behavior | `SCREEN_SPEC.md` §8 | per-screen states |
| Visual system | `DESIGN.md` | tokens, components, motion |
| Path-scoped rules | `.ai/rules/index.md` | conventions for covered globs |
| Coding rules | `CLAUDE.md` | assistant rules, lore gate detail |
| Slice plans | `docs/research-scratch/PROCESS-PLANS.md`, `SLICE-RECORDS.md` | local to a slice |
| Boost block | injected | generic; repo rules win |

Disputes: quality bar > gate registry > ADRs > DESIGN.md > slice plans; ARCHITECTURE.md beats digest;
PRD is product truth. Record conflicts (`SCREEN_SPEC.md` §7 or ADR erratum), don't resolve silently.
Boost asks for policies, `Auth::user()`, Cloud deploy, active `User` — none exist; do not add them.

## 2a. Plan-first contract (mandatory)

Every task: short plan before editing. Proportional detail. Plan states goal, scope, acceptance
criteria, ordered TODOs, verification, risks/dependencies. Orientation (git status, read this file +
quality bar + `.ai/rules` for paths) precedes planning. Use host task facility; Markdown checklist
fallback. Plan is not an approval gate; pause only for new requirements, scope exceed, destructive
actions, or unsettled evidence. No persistent plan files by default. Completion verified, not
asserted (§15).

## 3. Documentation map

No new markdown in repo root; new docs go into `docs/research-scratch/` masters or `KNOWN-ISSUES.md`.

| Document | Owns |
|---|---|
| `PRD.md` | users, stories, FR-A..FR-E, NFRs, §6 non-goals, open questions |
| `ARCHITECTURE.md` | schema, pipeline, API, security, testing strategy |
| `ARCHITECTURE-ESSENTIALS.md` | digest (loses to full text) |
| `DESIGN.md` | visual system |
| `SCREEN_SPEC.md` | screen inventory, states, workflows, §8 authority |
| `CHANGELOG.md` | notable changes, Keep a Changelog |
| `docs/research-scratch/GOVERNANCE.md` | quality bar C-1..C-9, gate registry, risk record |
| `KNOWN-ISSUES.md` | live defect register `KI-nn` |
| `README.md` | onboarding, commands, routes, docs |
| `VERSION.md` | version, pre-release, tags, framework/PHP versions |
| `MCP_SETUP.md` | MCP client config and host gotchas |
| `docs/research-scratch/SLICE-RECORDS.md` §execution-ledger | append-only dispatch ledger |
| `docs/adr/README.md` | derived ADR index |
| `docs/research-scratch/PLANS-AND-BRIEFS.md` | open-question register for PRD |
| `docs/UMAMUSUME_REFERENCE.md` | source-cited mechanics corpus |
| `docs/scenarios/01,02,03,07,08,09` | per-scenario guides (08 JP-only, don't import) |
| `PRODUCT.md` | generated; don't hand-edit |
| `docs/deprecated/` | retired legacy PDFs; not a source |
| `docs/adr/` | Architecture Decision Records (0001-0037); `docs/adr/README.md` is the derived index, Accepted binds only |
| `docs/audits/` | Repository audits only (browser observations, RS-UC audits, UX walks); dated dirs, register in `docs/audits/INDEX.md` |
| `docs/archive/` | Superseded artifacts (mockups, screenshots, captures); not a source |
| `docs/game-screenshots/` | Raw Global client screenshots; reference only |
| `docs/imports/` | Legacy tracker run records; folded into `TRAINING-RUN-IMPORTS.md` master; files remain as record |
| `docs/plan/` | Dated execution plans (session plans and legacy plans) |
| `docs/proposals/` | Descriptive proposals, not authoritative; verify paths against the tree before trusting |
| `docs/research/` | Flow briefs (`flows-*.md`), `SYSTEM-FLOWS.md`, mechanics source research |
| `docs/research-scratch/` | Process masters (`GOVERNANCE.md`, `PROCESS-PLANS.md`, `SLICE-RECORDS.md`, `PLANS-AND-BRIEFS.md`); no new files unless owner names the path |
| `docs/research-scratch-source/` | Umamusume game knowledge ONLY (`data/`, `scripts/`); no repo process docs |
| `docs/roadmap/` | Live roadmaps + verifications (`MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md`, `INDEX.md`) |
| `docs/sitrep/` | Session reports and handoffs |

When docs disagree: quality bar > gate registry > ADRs > DESIGN.md > slice plans; ARCHITECTURE.md
over digest; PRD is product truth.

### Default placement (no path specified)

When no path is specified, place by document class:

- New defect → `KNOWN-ISSUES.md` (`KI-nn`, four-part form, never renumber).
- Repository audit artifact → `docs/audits/<dated-slug>/`, then register in `docs/audits/INDEX.md`.
- Umamusume game-mechanics fact → `docs/research-scratch-source/` — game knowledge only, and only on owner direction.
- Architecture decision → `docs/adr/`, then regenerate `docs/adr/README.md`.
- Process or planning doc → append to the owning `docs/research-scratch/` master. Do not create a new master; the owner must name the path.
- Anything else → ask the owner. Do not invent a folder or place by guess.

Check order: `AGENTS.md` §3 → `docs/INDEX.md` → the folder's own `INDEX.md` (`docs/audits/`, `docs/roadmap/`, `docs/research-scratch-source/`, `docs/design-research/_scratch/` each carry one).

## 3a. Cross-directory workflow (proposals → roadmap → plan → sitrep)

Four doc directories form one execution loop. A slice moves through them in order; each directory owns exactly one step:

| Step | Directory | Owns | Relation |
|---|---|---|---|
| Propose | `docs/proposals/` | Descriptive ideas, **not** authoritative | Accepted ideas sequence into a roadmap slice; cited as source |
| Sequence | `docs/roadmap/` | Live roadmap + verifications; **ordering authority** | `MASTER-IMPLEMENTATION-ROADMAP-2026-11-18.md` holds slices S0.1–S4.4; plans cite slice ids |
| Plan | `docs/plan/` | Dated execution plans | `PLAN-CONTRACT` `slices:` lists roadmap slice(s) the plan covers; roadmap governs, plan corrects |
| Report | `docs/sitrep/` | Session reports and handoffs | Sitreps record progress on plan slices and update roadmap acceptance status; remaining work feeds back |

Rules:
- **Propose → Sequence → Plan → Report** is the forward direction. Sitrep outcomes feed remaining work back into the roadmap's acceptance list (§16 of MASTER-IMPLEMENTATION-ROADMAP); never rewrite a landed plan.
- Proposals are descriptive only — verify paths against `git ls-files` before reading; plan text re-measured against the tree.
- No roadmap ↔ plan pair references another pair file; shared facts live in `mnemon`, never inside a pair file (owner ruling 2026-11-16).
- A plan's `PLAN-CONTRACT` `slices:` field is the machine-readable join to its roadmap slice(s); a sitrep names the plan file(s) and slice(s) it worked on.
- Historical pair files stay on disk as record; they own no current execution order and are never edited (`docs/roadmap/INDEX.md`).

## 4. Roles and escalation

| Role | Owns |
|---|---|
| Architect | schema, system design, API shapes, perf budgets, ARCHITECTURE* |
| Data Engineer | `app/Services/DataPipeline/`, `config/uma.php`, parsers, snapshots, match_candidates |
| Laravel Dev | controllers, Form Requests, Resources, routes, views, factories, seeders |
| QA / Reviewer | gates (§9), Floor, doubt-driven review |
| Lore Guardian | every user-visible string, identifier, seed value, doc line |
| Docs Writer | README, ADRs, doc currency |
| Planner Domain Specialist | training-run domain: plan-vs-actual, turns/skills, stat bounds, export, tz |

Escalation: 1 role conflict→Architect; 2 scope conflict→owner (never widen scope); 3 lore dispute→
Guardian (owner overrides in writing); 4 threshold relaxation→owner; 5 fetch legality→Data Eng stops;
6 review disagreement after 3 doubt cycles→surface both; 7 planner feature implying cut system
(simulation/predictions/dual storage)→Specialist→Architect→PRD §6→owner.

## 5. Non-negotiables

**Lore gate (advisory; ban binding).** Characters are Umamusume (humanoid race). Banned vocab/
framing defined in bar C-4, implemented by `tools/lore.php` (`composer lore`, `composer lore-code`).
- Ban governs copy/framing only. Does not reach dataset/export keys (`intelligence`, `friend`),
  mechanics sharing a word, or verbatim skill/race/card names as source data. Gated on display
  path; never edit data; dataset keys must not be renamed (ingest join depends on them).
- Grep proposes, Guardian decides. 4 allowed hit classes. Each remaining hit needs a one-line
  context ruling in hand-off.
- `lore-ignore-line` marker valid in `docs/` only.
- `lore-code` additive: scans untracked files + Global client terminology. Banned glosses are
  wiki-side words (Wit, Mood etc.); Global client words are what UI may print.

**The Floor (never, any change).** No new suppressions (`@phpstan-ignore`, `eslint-disable`,
`@ts-ignore`, `# noqa`); no stub bodies; no deleted/skipped test without owner approval + reason;
no engine write to `is_manual=true` row; no fact without provenance; no matching on display
strings (match on normalized `match_key`); no fetch URL outside `config('uma.sources')`; no business
logic in controller; no inline `$request->validate()`.

**Copy.** No em dashes in shipped copy. Unrecorded value renders `N/A` with `title`, never default
or dash. No AI buzzwords/fabricated claims/stats/dates.

**Dependencies.** No new package without approval; `composer audit` + `npm audit --omit=dev` clean
of reachable critical/high before tagging.

## 6. Repository structure

```
app/Actions/              one-off ops (PromoteMatchedRecord, ResolveMatchCandidate, ImportHistoricalRun, Store* ingest)
app/Services/DataPipeline/ SourceFetcher (only HTTP), NameNormalizer, CrossReferenceMatcher, PipelineRunner, Parsers/, Contracts/
app/Services/             ScenarioCaps (stat-ceiling owner), SupportCardEffects, Skill{Registry,Matcher,Executor} (separate tooling)
app/Console/Commands/     UmaFetch, UmaReparse, UmaBackup, UmaImportSupportCards, ManageSkills
app/Enums/                TitleCase cases; DB stores backed value
app/Http/                 Controllers (+Api/V1), Requests, Resources
app/Models/               catalog, trainer-data, support-card entities, TurnEvents payloads
config/uma.php            fetch allowlist, politeness, thresholds, cache TTL, display tz
config/scenarios.php      ONLY place scenario names enter layout path
database/                 migrations, factory per model, seeders, seeders/data (committed bodies)
resources/views/          4 Blade files only: app.blade.php + errors/
resources/js/             TS+Vue3: spa.ts, bootstrap.ts, types.ts, pages/ (66), components/ (119), layouts/ (2)
lang/en/uma.php           displayed vocabulary + Global terms map
routes/                   web.php (browser), api.php (JSON)
tests/                    Feature/ (bulk), Unit/ (no DB), Fixtures/ (stored bodies)
tools/                    lore.php, gate.py, doc_census.py, roster-crosscheck.php, check_untracked.py
docs/                     adr/, audits/, archive/, game-screenshots/, imports/,
                          plan/, plans/, proposals/, research/, research-scratch/
                          masters, research-scratch-source/, roadmap/, scenarios/,
                          sitrep/, deprecated/
```

## 7. Coding conventions

Read `.ai/rules/index.md` first; open every rule file whose glob covers the path touched.
Pint `laravel` preset + PHPStan level 6.

- `declare(strict_types=1);` line 1; explicit param/return types; constructor property promotion.
- `#[Fillable]`/`#[Hidden]` attributes (never legacy props); `HasFactory` + factory per model +
  `@use HasFactory<XFactory>`; casts as `casts()` method (never `$casts` property).
- TitleCase enum cases in `app/Enums/`.
- Thin controllers; logic in `app/Actions`/`app/Services`; Form Request every write/search; API
  Resources for JSON; named routes + `route()` everywhere.
- `config()` in app code; `env()` only in `config/`. Create files via `php artisan make:* --no-interaction`.
- Enums in `app/Enums/`; browser routes in `routes/web.php`; JSON under `app/Http/Controllers/Api/V1/`
  declared in `routes/api.php`.
- No soft deletes, no DB-level enum columns, no Livewire, no Redis, no Excel. "No Inertia/SPA"
  withdrawn (2.0 line shipped it); other three banned and absent from `composer.lock`.
- Formatting: `.editorconfig` (4 spaces, LF, single quotes, trailing commas, final newline);
  PHPDoc over inline comments; array shapes in PHPDoc; braces always.
- `config/scenarios.php` is only source of scenario names in layout path; scenario-driven components
  (`ResourceStrip.vue`, `RaceCalendar.vue`, `GuidedStep.vue`, `GradePointMeter.vue`) take resolved
  label from server as required prop with no default (`scenarioLabel: string`). Hardcoded scenario
  name in page/component is a defect. (`StatBand.vue` does not exist; historical comments only.)
- Design tokens only (`bg-page`, `text-ink`, `border-rule`, ...). Zero `dark:` utilities, zero
  skeleton palette classes.

## 8. Architecture rules an agent must know before editing

Pipeline stages isolated: `fetch -> snapshot -> parse -> normalize -> match -> promote | review`.

- **Fetch**: `SourceFetcher` only outbound HTTP; hosts allowlisted in `config('uma.sources')`. URL
  outside list = SSRF-floor violation.
- **Snapshot**: raw body stored gitignored under `storage/app/private/snapshots`, hashed. Unchanged
  hash short-circuits run, writes nothing.
- **Parse/normalize**: one parser class per source implementing `Contracts\SourceParser`.
  `NameNormalizer` pure (NFKD, lowercase, separators + combining marks stripped); display names
  never mutated.
- **Match**: Exact + Alias auto-promote; Fuzzy (above `config('uma.match.fuzzy_threshold')`) + None
  land in `match_candidates` for `/review`.
- **Promote**: upserts engine-owned columns, skips `is_manual=true` rows, writes one
  `data_sources` provenance row per fact, inside `DB::transaction`.
- **Artwork** (`ADR-0021`, both halves built): `uma:fetch-art` reads ids from
  `character_cards.card_id` + `support_cards.support_id`, requests asset host in
  `config('uma.sources')`, writes `storage/app/private/artwork/` + `manifest.json`. Nothing in DB,
  no provenance row. `uma:fetch` steps over entries with no parser (asset host allowlisted without
  parsing). Manual only; unrun mirror renders nothing. Files reach browser via loopback
  `artwork.show` route, never asset host.
- Caching: catalog reads `Cache::remember` with `catalog:version` counter bumped on promotion.
  Trainer-data never cached. `ScenarioCaps` single owner of per-stat ceiling arithmetic; validator
  and renderer both call it with same arg (ADR-0015).
- `Skill{Registry,Matcher,Executor}` + `php artisan skill:manage` are separate tooling layer
  (`.agentrules`, documented in `docs/research-scratch/PLANS-AND-BRIEFS.md` per `CLAUDE.md` §3); touch no
  catalog/run table. This row previously named `docs/SKILL_AUTOMATION.md`, which no longer exists in the
  tree; the owning doc is the one `CLAUDE.md` §3 names.

### Hermes toolset narrowing (S1)

`terminal` removed from `platform_toolsets.cli`; Hermes file-and-web-only. Shell commands need MCP
or explicit user invocation. Replacements: read→`file.read_file`, write→`file.write_file`/`patch`,
fetch→`web.*`, search→`file.search`, scripts→`code_execution`. `pre_tool_call` hook
(`.claude/hooks/hermes-pretooluse.mjs`) denies `cat`/redirect/`curl`. VCS/deps/migrations/tests
delegated to a different IDE. Intentional narrowing, not a gap.

## 9. Testing and validation

Pest 4, feature-first. Global binding in `tests/Pest.php` applies `TestCase` + `RefreshDatabase` +
`withoutVite()` for `tests/Feature`; never add `uses()` or `withoutVite()` to an individual test
file. `tests/Unit` no DB.

- Build models from factories; check factory states first.
- Fetcher/pipeline tests use `Http::fake` with stored bodies in `tests/Fixtures`. No test touches network.
- Prefer facade fakes (`Bus::fake`, `Queue::fake`, `Mail::fake`); Mockery only for custom/external deps.
- Cover changed behavior + important failure modes, nothing beyond. No delete/skip test without approval.

**Cost-aware escalation.** Full suite is checkpoint/gate, not normal feedback loop.
- Level 1 focused (during implementation): only what covers the change; no auto full suite.
- Level 2 related regression (end of coherent slice): affected tests + nearby suites sharing behavior.
- Level 3 full verification (checkpoints): coherent feature/slice, before substantial-complete/release/
  merge, after major refactor or cross-layer change, explicit request, or too broad for targeted.
- Classify failures before re-running: regression, pre-existing, unrelated, infra/env, flaky,
  inconclusive. Reproduce suspicious with focused command. Record pre-existing/unrelated (e.g.
  KNOWN-ISSUES.md), don't fix in passing. Never claim full suite passed unless executed+passed.

**Background/parallel execution.** Level 1/2 runs are read-only (phpunit forces `DB_DATABASE=:memory:`);
may background to session-private `.scratch-uma/<session>/` log. Never background writing commands
(`pint` without `--test`, `migrate`, `npm run build`) while peers work. Red background run classified
before re-run; contended box voids sweep (KI-74).

**Test dispatch.** May dispatch to subagent against frozen tree state (isolated `git worktree add`
at commit under test), never shared working tree. Brief states target commit, specs/filters, report
shape (sha + dirty-path counts at start/end, commands, results, per-failure classification with
focused reproduction). Writing commands not dispatched while peers work. Prompts should say
"dispatch a subagent to run X against an isolated worktree at HEAD".

**Browser DB path.** Playwright global setup (`tests/Browser/global-setup.ts`) → `scratchDatabasePath()`
→ `.scratch-uma/sqlite/`. Keeps `database/` clean. Do not revert to `database/browser-scratch-*.sqlite`.

**Scratch discipline.** Everything a session writes under `.scratch-uma/` goes in `.scratch-uma/<session>/...`,
never root (collision surface, KI-70). Artifacts stay inside repo (owner rule); no drive root or sibling
dirs. Worktrees/test dirs/vendor copies inside repo (`.scratch-uma/worktrees/` etc.) or not at all.

**Browser suite always session-private.** `PLAYWRIGHT_PORT=<port> PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-<port>.sqlite npm run test:browser` (KI-73, KI-80). Unqualified run risks leaked server / deleted peer DB.

**Broad sweep pre-flight.** Time one trivial SQLite read (`php -r` select id from training_runs limit 1
against dev file, KI-74). >few seconds = contended; void before start.

**Every flow a user completes needs an end-to-end spec** (enter at flow start, assert URL after each
primary, delete created in afterEach; `tests/browser/wizard-flow.spec.ts` pattern).

Change-class minimums:

| Change | Minimum |
|---|---|
| Copy/layout/styling only | affected feature test(s); no new test |
| Logic in model/action/service | narrow test file, then `php artisan test --compact` |
| Route/controller/Form Request/view | feature tests for that screen; SCREEN_SPEC state coverage |
| Fetch pipeline/parser | parser + fetch/pipeline tests, `Http::fake` only |
| Schema | migration + digest, fresh migrate+seed on scratch DB, `migrate:status` clean on dev DB |
| TS/Vue/Blade | `npm run typecheck`, `npm run build` if page references assets |
| Anything crossing layers | full suite, then hand-off sequence |

Hand-off sequence: targeted tests green → `php artisan test --compact` → `vendor/bin/pint --test --format agent <paths>` →
`vendor/bin/phpstan analyse --no-progress --memory-limit=1G` → `npm run typecheck` → `npm run lint:ui` →
`composer lore` + `composer lore-code` (ruling per hit) → `composer audit` + `npm audit --omit=dev` (deps changed).

**Lore gate advisory.** `tools/lore.php` report-only, always exits 0. Obligation: ran, hand-off quotes
hit count + delta from last recorded figure, every hit carries ruling. Last recorded: `5ed45d67`
(2026-10-20) docs mode 478 hits + 75 exempt, 182 code mode. Count moves; rising = signal to rule, not
regression.

**KNOWN-ISSUES.md:3154 ruling.** Term from banned vocab, class 2 (substring in ordinary word/proper noun,
software sense). Compound names D-268 re-home of agent pointer; means history of change. 31 other hits
same term already in tree with rulings. No marker added (register is root planning file; parity test
refuses markers outside docs/). Hit stays in count with this ruling.

`npm run lint:ui` (`tools/ui-framework-check.mjs`) fails Card-in-Card and new arbitrary Tailwind values.
Arbitrary values enumerated in `tools/ui-lint-baseline.json` (debt 209; 207 last observed 2026-11-03);
any new one fails at 209+1. Identifier/bare-`N/A` rules enforced by `tests/Feature/UiHygieneTest.php`;
one-h1-per-route by `tests/browser/ui-hygiene.spec.ts`.

Dated 2026-10-23 (Phase 6, two measured findings):
- D-288/G-18 browser gate needs unwritable-by-default path: `php artisan test --filter='browser contrast'`
  makes pest-plugin-browser write `vendor/pestphp/pest-plugin-browser/.temp/playwright-server.json`;
  under workspace-write sandbox denied → `ErrorException Permission denied` with 0 assertions. Zero
  assertions = nothing measured; give unrestricted FS or don't report passed.
- Browser suite one process at a time, dispatched not inline. Serialise with lock dir
  `.scratch-uma/<session>/playwright.lock` (mkdir acquire, rmdir release).

**Never `pint --dirty` (KI-75).** Collects every file differing from HEAD; shared worktree means dirty
set isn't your slice. Run `pint --test --format agent <explicit paths>` on files actually touched.

**Working copy can fall behind HEAD** (peer reset or private-index landing built from HEAD's bytes).
`git diff HEAD -- <path>` before/after staging a file another session writes: additions only = peer's
uncommitted work; deletions of lines you recognise = stale working copy.

**Browser harness per-session, not per-worktree (KI-73, KI-80).** `playwright.config.ts` reads port from
`PLAYWRIGHT_PORT` (default 8127); global setup gives each invocation its own scratch DB.

Suite can't see dev DB: `phpunit.xml` forces `DB_DATABASE=:memory:`; every test builds own schema.
Green suite proves nothing about `database/database.sqlite`. Run `migrate:status` on hand-off when a
change reads a table a migration creates.

Evidence rule: a claim is the command output. "Tests pass" means run output in hand-off; unrun gate
reported as not run.

## 10. Commands

| Task | Command |
|---|---|
| First-time setup | `composer setup` (install, `.env`, key, migrate, npm install+build) |
| Dev | `composer dev` |
| Full pipeline | `composer test` |
| Tests, narrowest first | `php artisan test --compact --filter=Name`, or `vendor/bin/pest tests/Feature/XTest.php` |
| Style check/fix | `vendor/bin/pint --test --format agent <paths>` / `composer lint` |
| Static analysis | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` |
| TypeScript | `npm run typecheck` |
| Assets | `npm run build`, `npm run dev` |
| Lore gate | `composer lore`, `composer lore-code` |
| Fresh DB + offline catalog | `php artisan migrate:fresh --seed` (destructive, §11) |
| Fetch/replay/import/backup | `php artisan uma:fetch`, `uma:reparse <source>`, `uma:import:support-cards`, `uma:backup` |
| Mirror artwork (manual) | `php artisan uma:fetch-art` (`--kind`, `--dry-run`, `--refetch`) |
| Routes/commands/config | `php artisan route:list`, `php artisan list`, `php artisan config:show uma.sources` |
| Docs census | `composer docs` |
| Markdown fence check | `python tools/md_fence_check.py [paths]` |

Destructive: `migrate:fresh --seed` drops every table in `database/database.sqlite`; back up first
(`uma:backup`) or point `DB_DATABASE` at empty scratch file. Needs owner approval against shared dev file.

Unavailable: GNU `make` (KI-4). Makefile targets are docs; run underlying composer/vendor command.

Browser serving: `.env` sets SESSION_DRIVER=database/CACHE_STORE=database, so page loads mutate shared
DB. For a browser pass use `composer serve:browser` (or `SESSION_DRIVER=file CACHE_STORE=file php artisan serve`).
Use `file`, not `array` (array driver breaks CSRF between GET render and POST submit).

## 11. Change-safety

- **Schema.** Migration travels with updated `ARCHITECTURE-ESSENTIALS.md` digest, PRD citation
  (FR-x/US-x), decision recorded as ADR. Every migration has working `down()`. Verify on scratch DB,
  not shared dev file.
- **Docs/ADRs.** Dated claim later wrong → append dated erratum preserving original sentence; never
  rewrite ADR silently. ADR index in `docs/adr/README.md` is derived: regenerate, don't edit rows.
- **Defect register.** Append new `KI-nn` to `KNOWN-ISSUES.md` in four-part form (numbered heading
  continuing from highest, status line, proving command/file, closure commit + what closure doesn't
  cover). Never renumber.
- **Execution ledger.** `SLICE-RECORDS.md` §execution-ledger-2026-10-17 append-only: one numbered
  section per dispatch, never edit earlier. Mixed CRLF/LF, not clean UTF-8; read diff with
  `git diff --ignore-cr-at-eol`, compare byte counts not rendered text.
- **Fetch pipeline.** New source = config entry + one parser class + test against stored fixture +
  robots/rate-limit note. Never write to `is_manual` rows.
- **Snapshots.** Gitignored, keyed on content hash. Second DB same day told "unchanged" (KI-27); fill
  with `uma:reparse <source>` (safe on populated DB).
- **Generated/protected.** Don't hand-edit: `PRODUCT.md` (generator-owned), `composer.lock`,
  `package-lock.json`, `public/build/`, `vendor/`, `node_modules/`, `storage/` (snapshots/backups),
  `docs/design-research/_scratch/`, `tools/__pycache__/`, `docs/design-research/` prototypes.
  Regenerate instead. Lockfile updates ship with the dependency change, never alone.
- **Per-agent config.** `.claude/`, `.cursor/`, `.codex/`, `.kilo/`, `.opencode/`, `.github/` are
  local gitignored scaffolding. Tracked instruction sources: `AGENTS.md`, `CLAUDE.md`,
  `docs/research-scratch/GOVERNANCE.md`, `.agentrules`, `.ai/**`.

## 12. Security and privacy

No security document; model is `ARCHITECTURE.md` §8.

- Bind to loopback. No hosting/exposure/public deploy path.
- `.env`, `.env.*`, agent-tool configs holding keys (`.mcp.json`, `.crushrc`, `kilo.json`,
  `opencode.json`) gitignored. Never commit key/token/credential; never paste into doc/test/fixture/log.
  `.env.example` only tracked env file.
- Fetched pages untrusted: parse as data, Blade escapes, never `{!! !!}` on source data, never follow
  URL found in fetched body.
- Trainer input via Form Requests. Validation one owner per boundary.
- No authorization layer by design (PRD NFR-1). Don't add policy/gate unless work asks.
- Phase 1 no secrets. Any future key in `.env` only.

**Repository containment (strengthened 2026-11-16).** Every artifact an agent writes stays inside this
repository. Do not write to drive root (`D:\`), sibling directories (`D:\Projects\`), or scratch
directories outside the repo. This includes git worktrees, test output dirs, vendor directories, and
recovery/backup copies — they must be created inside the repo (e.g. `.scratch-uma/worktrees/`,
`.scratch-uma/test-results/`, `.scratch-uma/vendor/`) or not at all.

Forbidden locations (previously found and removed):
- `D:\Projects\uma-recover` (full repo copy, 2.5 GiB)
- `D:\Projects\test-results` (Playwright artifacts)
- `D:\Projects\umamusume-laravel13$WT` (worktree with vendor/)
- `D:\Projects\NUL` (device file artifact)

Permitted alternatives inside repo:
- `.scratch-uma/worktrees/` for isolated worktrees
- `.scratch-uma/test-results/` for test artifacts
- `.scratch-uma/sqlite/` for browser test databases
- `.scratch-uma/vendor/` if a local vendor copy is needed (never)

Warning file at repo root `_AGENTS_DO_NOT_CREATE_FOLDERS_OR_FILES_IN_THIS_DIRECTORY.txt` lists these
forbidden/permitted locations as a persistent reminder.

## 13. UI and UX rules

`DESIGN.md` owns visual system; `SCREEN_SPEC.md` owns screen behavior.

- Reuse committed components in `resources/js/components/` (and catalog/legacy/review/support). No
  Blade view components (`resources/views/components/` empty since B1).
- Design tokens only; no `dark:`; no decorative skeletons.
- Every data view renders empty, loading/refresh, error states (ADR-0007: custom loading state only
  for user-initiated async actions).
- Unrecorded values render `N/A` with disclosure channel, never default.
- Print Global client vocabulary only (`lang/en/uma.php` terms map; `Wit`/`Mood` are client words,
  wiki glosses banned by `lore-code`).
- Keep accessibility behavior: keyboard paths, target sizes, review-form labels regression-tested.
- Screen-level changes need `SCREEN_SPEC.md` state table to agree with view.
- Browser baseline: `Baseline 2024` (web-platform-dx). A platform feature that shipped in every core
  browser by the end of 2024 is usable without a polyfill; the `modern-web-guidance` guides assume it.

## 14. Git and change management

- Conventional commits: `type(scope): summary`, lowercase, imperative.
- One slice per commit series; report + verification output in hand-off. Branch of record `master`;
  CI is build-only (three workflows, no gate job), so the diff is still the review.
- `git commit <path>` guards index, not working tree: commits that path's working-tree content, so
  another session's uncommitted lines in same file land too. Name every path; stage only your hunk
  when working tree carries another session's work.
- Never commit `vendor/`, `node_modules/`, `storage/` snapshots/backups, `.env`, `public/build/`.
- Deleting/skipping test needs owner approval + stated reason in commit.
- `CHANGELOG.md`/`VERSION.md` tracked; CHANGELOG updated by Docs Writer on significant landings;
  VERSION by Architect on phase exits.
- Tracked root docs closed set (AGENTS.md, ARCHITECTURE.md, ARCHITECTURE-ESSENTIALS.md, CHANGELOG.md,
  CLAUDE.md, DESIGN.md, KNOWN-ISSUES.md, MCP_SETUP.md, PRD.md, PRODUCT.md, README.md, SCREEN_SPEC.md,
  VERSION.md). No new root doc; masters under `docs/research-scratch/` indexed by `INDEX.md`.

## 15. Definition of done

- §2a plan-first followed: goal, acceptance criteria, TODOs, verification evidence exist; TODOs
  current; open items reported not dropped.
- Implementation matches architecture/conventions §7/§8.
- Behavior change ships with test; copy-only without.
- Narrow tests pass; broader suite + hand-off sequence for cross-cutting, output attached.
- `pint --test --format agent <paths>` on changed files (never `--dirty`); PHPStan level 6 clean;
  `npm run typecheck` clean if TS/Vue/Blade changed.
- Lore gate ran; every hit carries context ruling.
- No unrelated file changed; diff read before reporting.
- Docs moved with behavior: digest+ADR for schema, SCREEN_SPEC for screens, DESIGN for visual system,
  KNOWN-ISSUES for new defect.
- Security considered: untrusted input, secrets, exposure, SSRF allowlist.
- Known limitations and unrun gates stated plainly.

## 16. Handling ambiguity

Don't invent requirements. 1) owning doc first (§3 map), then code, then ADR/ruling. 2) prefer
smallest behavior-preserving change evidence supports. 3) record ambiguity: SCREEN_SPEC.md §7 for
screen conflicts, ADR erratum for dated claim, KNOWN-ISSUES.md for defect. 4) ask owner only when
proceeding either way unsafe/irreversible/widens scope. Autonomous work picks conservative option
and says so.

## 17. Legacy and migration guidance

- Four legacy apps consolidated. Retired originals in `docs/deprecated/`; not spec/data source;
  never copy display text.
- `app/Models/Legacy/LegacySelectionPayload.php` (ADR-0010) is compatibility payload for planner
  import; not current run model.
- `app/Models/User.php` + `users` table unused framework defaults.
- Check ADR status line (Accepted/Accepted in part/Declined/Proposed/Superseded) before coding
  against a decision. Folded sources cite master + section anchor, not deleted original.
- Cut systems stay cut (PRD §6): no race-outcome prediction, no image uploads, no dual storage, no
  simulation. "Image uploads" = Trainer supplying a file (PRD §6.13 cuts it). Art tool fetches by
  id from allowlisted host is different, authorized by ADR-0021 (both halves built).

## 18. Known traps

- `make` unavailable (KI-4); use composer scripts.
- Pinned source URL can serve stale content; withdrawn doc still answers 200 (KI-24); `uma:fetch`
  warns on fallback to pinned URL.
- Snapshot short-circuit keyed on today's date can leave second DB empty claiming "unchanged" (KI-27);
  use `uma:reparse <source>`.
- PHPUnit env wins over `.env.testing`: `phpunit.xml` forces `DB_DATABASE=:memory:`. Keep in agreement.
- `umamusume.com/news/NNN` is client-rendered shell; HTML carries no text; 200 with empty body is not
  evidence of empty notice. Bodies from `POST https://umamusume.com/api/ajax/pr_info_index?format=json`
  with `{"announce_label":0,"limit":100,"offset":N}` (rows in `information_list`, full `message`) and
  `pr_info_detail` with `{"announce_id":NNN}`; `umamusume.jp` same shape. Source candidate, not
  allowlisted; adding needs §11 new-source package + robots/rate-limit note; must not be polled.
- Page referencing built assets without manifest needs `npm run build`.
- Asset host answers miss with 27,150 bytes of `text/html` at HTTP 404. "Bytes came back" is never
  the test. `SourceFetcher::fetchAsset()` returns null on any non-2xx; `ArtworkMirrorTest` keeps it.
  Storing 404 body would put HTML in place of PNG → broken frame forever.
- Empty artwork mirror invisible not broken: `ArtworkSlot.vue` renders nothing for null url (DESIGN.md
  §4.7). Check disk state first (`uma:fetch-art --dry-run`) before reading missing images as frontend
  defect.
- PHPStan needs `--memory-limit=1G`; `composer analyse` omits it.
- Hot Vite dev server + default SSR settings → every full page load 500 after 30s (KI-76).
  `INERTIA_SSR_ENABLED` defaults true, `config/inertia.php` unpublished so `ssr.timeout` unset; while
  `public/hot` exists Inertia skips "no bundle" guard and POSTs to dev server's `/__inertia_ssr`,
  which nothing answers, blocks until `max_execution_time` fatals. `.env.example` sets
  `INERTIA_SSR_ENABLED=false`; keep it set, or publish `config/inertia.php` with short
  `INERTIA_SSR_TIMEOUT`.
- Lore gate advisory; banned word families not written out here on purpose. Read pattern list in
  `tools/lore.php` when ruling needed.

## 19. Working style

Lazy means efficient, not careless. Climb rungs first: does it need building; exists here; stdlib;
native platform; installed dependency; one line; then minimum that works.

Bug fix = root cause, not symptom: grep every caller of function touched, fix shared function once.
No unrequested abstraction/dependency/boilerplate; deletion over addition; fewest files; shortest diff
still correct. Mark deliberate simplification with known ceiling via short `ponytail:` comment naming
ceiling + upgrade path.

Never lazy about: understanding real flow end-to-end, validation at trust boundary, error handling
preventing data loss, security, accessibility, anything explicitly requested. Non-trivial logic leaves
one runnable check behind (smallest thing that fails if logic breaks); trivial one-liner needs no test.

## 20. Change log

| Date | Change | Reason |
|---|---|---|
| 2026-10-04 | Rewritten as operational contract | agents reading stale file |
| 2026-10-05 | Artwork bullet, §17 disambiguation, §18 asset-host trap | ADR-0021 accepted |
| 2026-10-06 | Artwork display half built; frontend stack corrected to Inertia+Vue | 8e58b65 folded 2.0 line |
| 2026-10-08 | Cost-aware escalation policy added | agents rerunning expensive suites |
| 2026-10-12 | Background/parallel execution rules; §2 row corrected to 0024; counts corrected | KI-70/73/74/75/80 |
| 2026-10-13 | Session-private `.scratch-uma/` subdirectory rule | 676 loose files sweep |
| 2026-10-16 | Test-dispatch subsection | verify-commit-pass precedent |
| 2026-10-22 | Stay-inside-repository rule | files found outside tree |
| 2026-10-24 | §2a plan-first contract; restore §3-§20 | alignment |
| 2026-10-26 | Round 21 fold; root set shrinks to 13 | owner-ordered consolidation |
| 2026-10-31 | Working-copy drift rule | SCREEN_SPEC/KNOWN-ISSUES drift |
| 2026-11-14 | StatBand.vue removed from scenario-driven list | phantom component |
| 2026-11-16 | Compact pass: trimmed dated corrections, verified counts (119 components, 66 pages) | this task |

## Delegation defaults

Delegate multi-step or context-heavy work to a subagent; inline only single-file trivial tasks.
"Inline is faster" is not a valid reason to skip delegation. Child subagent `tools:` allowlist must be
strict subset of parent's; spawn depth capped by Hermes `delegation.max_spawn_depth`. Delegation log
`.tooling/logs/delegation.jsonl` records one line per `SubagentStart`.
