# AGENTS.md

Operational contract for coding agents working in this repository. It answers: how to
orient, what may not be touched, what must be run, and what "done" means here. It is not
a product spec, an architecture document, or a tutorial. Those live in the files named in
§3 and are linked rather than restated.

Read `docs/research-scratch/GOVERNANCE.md` §"CONSTRAINTS.md (root quality bar, C-1 to C-9)" before writing code. It is a pointer stub now; the binding quality
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
  Tailwind CSS v4, Vite), with the career set and the scenario panels as Vue components under
  `resources/js/`. Blade survives only as the `resources/views/app.blade.php` shell and
  `resources/views/errors/`; there are no Blade view components. JSON is a read-only `/api/v1`
  surface (P2, three controllers).
  *Dated correction 2026-10-08 (documentation-sync pass): the "51 single-file components" figure is
  superseded — the tree then carried 58 single-file components under `resources/js/components/` and 54
  page files under `resources/js/pages/`, after the Phase D–E career, Veterans and Database sets
  landed. *Dated correction 2026-10-12: the tree now carries 63 components and 48 pages. Counts like
  these age the moment a slice lands, so this is a snapshot note, not a number to keep in step; what
  holds is the shape: the career set and the scenario panels are Vue, and
  `resources/views/components/` is empty since slice B1.*
  *Dated correction 2026-10-27 (v3.0.0 release): re-measured, the tree carries 100 single-file
  components under `resources/js/components/` and 59 page files under `resources/js/pages/`. The
  v3 HFW-parity slices added the scenario panels and the Legacy/Catalog/SupportCards/Skills/Database
  surfaces. The two earlier corrections stand as history; this figure is a snapshot too, and only the
  shape above is a rule.*
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
| Quality bar                 | `docs/research-scratch/GOVERNANCE.md` §"CONSTRAINTS.md (root quality bar, C-1 to C-9)" | Never weakened to pass a check                                                               |
| Gate registry               | `GOVERNANCE.md` §GATE-REGISTRY.md                                                                   | How each gate runs, its evidence, its allowed exceptions                                     |
| Binding decisions           | `docs/adr/0001`-`0025`, `SUPERSEDED-*`                                                              | Accepted ADRs bind; `Proposed` ones do not                                                   |
| Product truth               | `PRD.md`                                                                                            | Users, requirements, §6 non-goals                                                            |
| System design               | `ARCHITECTURE.md` over `ARCHITECTURE-ESSENTIALS.md`                                                 | The digest loses to the full text                                                            |
| Screen behavior             | `SCREEN_SPEC.md` §8 authority table                                                                 | Per-screen states and workflows                                                              |
| Visual system               | `DESIGN.md` (+ folded corpus `docs/research-scratch/DESIGN-CORPUS.md`)                              | Tokens, components, motion                                                                   |
| Path-scoped rules           | `.ai/rules/index.md` -> code-style.md, eloquent.md, architecture.md, testing-standards.md   | Conventions for the globs they cover                                                         |
| Coding rules                | `CLAUDE.md`                                                                                         | Assistant coding rules, lore gate detail                                                     |
| Slice plans and records     | `docs/research-scratch/PROCESS-PLANS.md`, SLICE-RECORDS.md, `PLANS-AND-BRIEFS.md`                 | Local to a slice; cannot override a gate                                                     |
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

## 2a. Plan-first contract (mandatory)

Every task starts with a short plan before a file is changed. Planning is proportional to
the work: a trivial task gets a checklist of a few lines; a multi-file feature, refactor,
migration or investigation gets milestones and independently verifiable steps. Do not turn
a trivial task into a planning document.

The plan states:

- **Goal:** the outcome the task asks for.
- **Scope:** the behavior, files, subsystems and deliverables in scope, and what must stay
  untouched.
- **Acceptance criteria:** observable conditions that establish whether the task is
  complete.
- **TODO list:** ordered steps, with each prerequisite (rule reads, owning document, gate)
  placed before the step that depends on it.
- **Verification:** the focused checks, gates and inspections that prove the acceptance
  criteria, following the change-class table in §9.
- **Risks and dependencies:** uncertainties, dependencies, destructive operations and
  existing constraints, where they apply.

**Orientation precedes planning.** Before publishing the plan, confirm the working tree
(`git status --short`), read this file and the quality bar, and perform the `.ai/rules`
discovery required by §7 for the paths in scope. The plan may name a still-unread owning
document as a prerequisite TODO; it may not skip that read.

**Use the host's task facility.** When the IDE provides a native task list, TODO list or
plan mode, create the TODOs there before implementation and keep them there. Mark a TODO in
progress when work on it starts, and complete only after its work and required checks are
done. Add or revise TODOs when investigation reveals necessary work, update the plan when
implementation materially diverges, and leave unfinished or blocked items visible. When no
native facility exists, keep an explicit Markdown checklist in the response or working
conversation. Planning recorded only in hidden reasoning or an after-the-fact summary does
not satisfy this section.

**A plan is not an approval gate.** Publishing the plan and proceeding with in-scope work
is expected. Do not ask the owner to approve routine steps the task already authorizes.
Pause only when proceeding would require a new requirement, exceed the task's
authorization, perform a destructive action that needs explicit permission (§10, §11), or
cannot be settled from repository evidence (§16). Existing gates, ADRs, the quality bar and
security rules are unaffected: planning never bypasses them.

**Do not create planning files by default.** Use the native task facility or the
conversation. A persistent plan is written only when an established repository process
requires one (a slice plan or execution-ledger entry under `docs/research-scratch/`), when
the work is a durable multi-stage or multi-session handoff, or when the owner asks for it.
Follow §3 and §11 when a plan document is warranted; never add a new root document or a
per-task checklist file.

**Completion is verified, not asserted.** Before reporting a task complete, the agent
compares the result with the goal and acceptance criteria, resolves or explicitly discloses
every open TODO, runs the applicable verification, and reports each command actually run
with its observed result. It names any check not run and why, states known failures,
blockers and follow-ups, and confirms the final working-tree changes while leaving unrelated
work unclaimed. Mark the plan complete only when the acceptance criteria and required
verification hold; §15 remains the definition of done.

## 3. Documentation map

Root documentation is a closed standing set (`docs/research-scratch/INDEX.md` §File
discipline). **No new markdown file is created in the repository root**, and new
documentation content goes into the relevant master under `docs/research-scratch/` or
`KNOWN-ISSUES.md`. Documentation files are written or updated only when the task asks
for it.


Two root stubs named by earlier versions of this map are gone. `1e84b0c` folded the
CONSTRAINTS.md stub into `docs/research-scratch/GOVERNANCE.md` under the same section heading, and the
PLAN.md stub into `docs/research-scratch/PROCESS-PLANS.md`. Both subjects still have an owner;
neither stub is a root document any more.
| Document                                                               | Owns                                                                                                                                                                                                    | Does not own                                              |
| ---------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------- |
| `PRD.md`                                                               | users, stories, FR-A..FR-E, NFRs, §6 non-goals, open questions                                                                                                                                          | implementation detail                                     |
| `ARCHITECTURE.md`                                                      | schema, pipeline, API contract, security model, testing strategy                                                                                                                                        | visual design                                             |
| `ARCHITECTURE-ESSENTIALS.md`                                           | token-efficient digest of the above                                                                                                                                                                     | anything the full text contradicts                        |
| `DESIGN.md`                                                            | visual system: tokens, components, surfaces, motion, lore-sensitive iconography                                                                                                                         | screen behavior (that is `SCREEN_SPEC.md`)                |
| `SCREEN_SPEC.md`                                                       | screen inventory, per-screen states, workflows, §8 authority table                                                                                                                                      | schema                                                    |
| `CHANGELOG.md`                                                         | all notable changes to Trainer Desk; Keep a Changelog format; version follows Semantic Versioning per `VERSION.md` and `ADR-0019`                                                                                                                                                       | implementation detail; version bumps (that is `VERSION.md`) |
| `docs/research-scratch/GOVERNANCE.md` (§CONSTRAINTS.md, §GATE-REGISTRY.md, §PRE-MORTEM.md)   | the bar, the gate table, allowed exceptions, risk record                                                                                                                                                | product intent                                            |
| `KNOWN-ISSUES.md`                                                      | live defect register, `KI-nn`                                                                                                                                                                           | history (that is AUDIT-AND-VERIFICATION.md)             |
| `README.md`                                                            | onboarding, commands, route and doc tables                                                                                                                                                              | governance                                                |
| `VERSION.md`                                                           | current version, pre-release status, Git tags, release commit, framework and PHP versions; Semantic Versioning as interpreted for this project                                                            | implementation detail; changelog (that is `CHANGELOG.md`) |
| `MCP_SETUP.md` | MCP client configuration: where each client reads its servers, and the host gotchas that make a correct-looking config fail silently | framework or schema guidance |
| `docs/research-scratch/SLICE-RECORDS.md` section `## execution-ledger-2026-10-17.md` | the append-only dispatch ledger: one numbered section per dispatch, with the gates run, the decisions taken and what was deferred | product intent, or the gate definitions (that is the governance master) |
| `docs/adr/README.md`                                                   | ADR index, derived; regenerate with the command in that file                                                                                                                                            | hand-edited index rows                                    |
| `docs/OPEN-QUESTIONS.md`                                               | open-question register for `PRD.md`; one line per question with status, owner, resolution pointer                                                                                                                                                                   | answered-and-remainder narration (that is `CHANGELOG.md`) |
| `docs/UMAMUSUME_REFERENCE.md`                                          | source-cited mechanics corpus, eight sections, dated live-ops snapshots                                                                                                                                 | a write-up that cites this repo is not a second source    |
| `docs/scenarios/01`, `02`, `03`, `07`, `08`, `09`                                               | per-scenario playing guides (`07` is the sourced guide for the fourth `[Global]` scenario, with its in-scenario client vocabulary still `❌ UNVERIFIED`; `08` is `[JP-Only]` and must not be imported)  | shipped copy                                              |
| `PRODUCT.md`                                               | generated tooling artifacts                                                                                                                                                                             | hand edits; the generator wins                            |
| `docs/research-scratch/GOVERNANCE.md`, sections `## SKILL_REGISTRY.md` and `## coverage.md` | the skill-registry pointer and the location classes its scanner cannot reach, each with the command that re-derives it; the figures are host-local, so re-derive before quoting | a shared snapshot of the host's installed skills |
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

**Lore gate (advisory; ban binding; every role).** The characters are Umamusume, a humanoid race. The
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
engine write to a row with `is_manual = true`, no fact stored without provenance, no
matching on display strings (match on the normalized `match_key`), no fetch URL outside the
`config('uma.sources')` allowlist, no business logic in a controller, no inline
`$request->validate()`.

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
resources/js/                TypeScript + Vue 3: spa.ts, bootstrap.ts, types.ts, pages/ (48),
                             components/ (63, incl. catalog, legacy, review, support), layouts/ (2)
lang/en/uma.php              displayed vocabulary and the Global terms map
routes/                      web.php (browser), api.php (JSON)
tests/                       Feature/ (the bulk), Unit/ (no database), Fixtures/ (stored bodies)
tools/                       lore.php (lore gate), gate.py (design-artifact gate), doc_census.py,
                             roster-crosscheck.php, check_untracked.py, dev-logs-pane.php
docs/                        adr/, scenarios/, proposals/, research-scratch/ masters, deprecated/
```

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

**Background and parallel test execution.** A Level 1 or Level 2 run is
read-only: `phpunit.xml:64` forces `DB_DATABASE=:memory:`, so Pest never touches
a file on disk, and `pint --test`, PHPStan, `npm run typecheck` and the lore
gates only read. Such a run may be launched in the background while
implementation continues:

    php artisan test --compact --filter=Name > .scratch-uma/<session>/test-<slice>.log 2>&1 &

Three rules make a background run admissible evidence. **The log is
session-private**: it lives under a session-named directory in gitignored
`.scratch-uma/` (KI-70: a shared-worktree file is a collision surface), and it is
quoted in the hand-off exactly like attached output — a backgrounded gate that
is never read back is a gate not run, per the evidence rule below. **Nothing
that writes is backgrounded** while any peer session works: no `pint` without
`--test`, no `migrate` in any form, no `npm run build` (Vite rewrites
`public/build`, whose manifest every session's browser gate reads; one session's
unbuildable file blocked every peer's browser gate for a day, KI-70). **A red
background run is classified before anything re-runs**: the
failure-attribution step applies verbatim, and a sweep that came back red under
load is reported void with its counts and the contention readings attached,
never as a defect list (KI-74).

**Test dispatch.** A Level 1, Level 2 or Level 3 run may be dispatched to a subagent with a defined
brief rather than launched synchronously and waited on. The subagent's result is admissible only when
it is measured against a **frozen tree state** — an isolated `git worktree add` at the commit under
test, never the shared working tree. A run against the shared tree while any session is editing cannot
be classified: a red result may be the code under test, a peer's uncommitted edit, or the tree moving
under the bundle mid-run, and none of those is distinguishable from the log alone.

The brief states the target commit, the specs and filters to run, and the required report shape: the
commit sha and dirty-path count at start and end, each command run, each result, and a classification
per failure (regression, pre-existing, unrelated, infrastructure, flaky, inconclusive) with the
focused reproduction already attempted for any case it calls a regression. The main agent keeps editing
the shared tree; nothing it writes reaches the subagent's run and nothing the subagent writes reaches
the shared tree — its worktree is retired once the report is read.

The dispatch relaxes nothing in this block. A writing command (a `pint` without `--test`, a `migrate`
in any form, `npm run build`) is not dispatched while any peer session works, whether in the shared
tree or in an isolated one whose `node_modules` or `vendor` is a junction into it — verify `LinkType`
is empty before trusting that install. A dispatched run that comes back red is classified by the
subagent before anything re-runs, and the classification travels with the report. A broad dispatch is
pre-flighted exactly as a broad synchronous sweep is: a timed SQLite read, and a contended box voids
it before it starts (KI-74).

Prompts that direct this work should therefore say **"dispatch a subagent to run X against an isolated
worktree at HEAD"** rather than "run X", so the run does not block the main agent and its evidence is
attributable to a commit.

The pattern's precedent is the `verify-commit-pass` dispatch of 2026-10-16: four Feature filters and
four browser specs run against `23f9d64` on an isolated worktree, whose classification named
`64fc4b8`'s trainee-label change as the root cause of the 17 failing `career-cockpit-forms` cases. That
result describes a commit; a synchronous run against the shared tree the same day described a moving
target. The worktree was retired once its report was read, so the precedent is its report, not a path
on disk.

**Everything a session writes under `.scratch-uma/` goes in its own
subdirectory (owner rule, 2026-10-13).** Logs, scratch SQLite databases, dumps,
probes, and driver scripts all live at `.scratch-uma/<session>/...`, never in
the root of that gitignored tree (the root is a shared collision surface, KI-70).
The root holds only directories (the sweep buckets from
`.scratch-uma/reorganize-scratch.ps1` such as `data/`, `logs/`, `scripts/`, each
session's own subdirectory, and `cited/`, which holds the citation-pinned
evidence files because tracked documents cite their exact paths as dated
evidence), plus `README.md` and
`.scratch-uma/reorganize-scratch.ps1`. `README.md` records the
layout and the sweep command. Root paths in older proving commands predate these
moves; do not copy them as templates.

**Every artifact an agent writes stays inside the repository (owner rule,
2026-10-22).** Scratch files, logs, dumps, probe scripts, extracted data and
one-off tooling live under this repository's own tree: `.scratch-uma/<session>/...`
(the rule above) or a temporary directory the repository itself owns. Do not
write to a drive root (`D:\`), to a directory beside the repository
(`D:\Projects\...`), or to a scratch directory outside it. A file written
outside the tree is untracked, unreachable from the repository, invisible to
every gate in this document, and a cleanup burden for whoever finds it later;
the drive root and `D:\Projects` are shared surfaces, so a stray file there
collides with every other project on the host, and one of the files found
outside the tree on 2026-10-22 belonged to a different project entirely and
could not be removed. An exception needs the owner naming the path for that
task, and it is per task, not a standing permission.

**A broad sweep is pre-flighted, not launched.** Before any Level 3 run or the
browser suite, time one trivial SQLite read (`php -r` on `select id from
training_runs limit 1` against the dev file, per KI-74). If it takes more than
a few seconds the box is contended and the sweep is void before it starts:
wait for a quiet box, or drop to the slice's own specs on a session-private
harness, and say which of the two the evidence is. Do not re-run a void sweep
hoping for a different result.

**The browser suite is always session-private.** `PLAYWRIGHT_PORT=<session port>
PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-<port>.sqlite npm run test:browser`
(KI-73, KI-80). An unqualified `npm run test:browser` in a shared worktree
either polls a leaked server serving a deleted database, or deletes a peer's
scratch database mid-run — and both failures read as product regressions.

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
-> `vendor/bin/pint --test --format agent <paths>` -> `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`
-> `npm run typecheck` -> `npm run lint:ui` -> `composer lore` and `composer lore-code` with a ruling per hit ->
`composer audit` and `npm audit --omit=dev` when dependencies changed.

**The lore gate is advisory.** `tools/lore.php` is report-only and always exits 0, so the
gate does not fail a change on its own. The obligation is that the gate ran, that the
hand-off quotes the hit count and its delta from the last recorded figure, and that every
hit carries a ruling. The last recorded figure is a dated observation, not a threshold: at
`5ed45d67` (2026-10-20) the docs-mode gate reported 478 hits and 75 exempt lines, and 182
hits in code mode. The count moves as the corpus grows, so a rising count is a signal to
rule on, not a regression to defend.

**The ruling for `KNOWN-ISSUES.md:3154`.** That line carries a term from the banned
vocabulary, and it is class 2: a substring inside an ordinary word or a proper noun, in the
software sense. The compound names the D-268 re-home of the agent pointer, where the term
means the history of the change rather than anything the gate polices. 31 other hits of
the same term are already in the tree, each carried as a hit with a ruling, so the term is
not a new class and the line is not a new violation. The register is a root planning file
whose prose the gate exists to police, so no marker is added to it: `LoreGateParityTest`
refuses markers outside `docs/` on purpose. The hit stays in the count and carries this
ruling.

`npm run lint:ui` (plan slice 1.12) fails a Card inside a Card and any new arbitrary Tailwind value
`bg-[#…]`, `w-[37px]`). The check is `tools/ui-framework-check.mjs` — `tools/ui-lint.mjs` was retired
in Phase 1.6 and its two rules absorbed as rules 8 and 10 there. Arbitrary values are enumerated in
`tools/ui-lint-baseline.json`; the debt count there is **219** as measured on 2026-10-27 (the earlier
"sets `count: 0`" line described the gate's intent, not its state), and each remaining value is named
individually, so any new one fails at 219 + 1. That list is deferred debt with a named intent, not a
place to add. The
identifier and bare-`N/A` rules are enforced by `tests/Feature/UiHygieneTest.php`, and the
one-`h1`-per-route rule by `tests/browser/ui-hygiene.spec.ts`.

*Dated addition 2026-10-23 (Phase 6, from two measured findings).* Two requirements the gate list did
not carry, both proven on this host:

- **The D-288/G-18 browser gate needs an unwritable-by-default path.** `php artisan test --filter='browser
  contrast'` makes `pest-plugin-browser` write
  `vendor/pestphp/pest-plugin-browser/.temp/playwright-server.json` before it starts a browser. Under a
  workspace-write sandbox that write is denied, and the run fails as `ErrorException … Permission denied`
  with **0 assertions** — never as a contrast verdict. A run that reports zero assertions has measured
  nothing; give that gate unrestricted filesystem access or do not report it as passed.
- **The browser suite is one process at a time, dispatched rather than run inline.** A single slow spec can
  consume a whole inline run: the 2026-10-23 attempt was stopped at test 361 and its `45 failed` was a
  floor, not a total. Dispatch specs as background tasks or subagents and serialise them with a lock
  directory (`.scratch-uma/<session>/playwright.lock`, acquired with `mkdir`, released with `rmdir`),
  so a slow spec times out only itself and the count that lands is the real one.

**Never `pint --dirty` in this tree (KI-75).** `--dirty` collects every file that differs from
`HEAD`, and several sessions share one worktree (KI-70, KI-71), so the dirty set is not the slice's own
set. Measured 2026-10-08: a slice that touched no controller ran the gate and Pint rewrote
`app/Http/Controllers/PreferenceController.php` — another session's uncommitted file — with
`no_unused_imports`. Run `pint --test --format agent <explicit paths>` on the files the change
actually touches and fix only those; the worktree's other uncommitted files are not this change's to
reformat.

**The browser harness is per-session, not per-worktree (KI-73, KI-80).** `playwright.config.ts` reads
its port from `PLAYWRIGHT_PORT` (default 8127) and `tests/browser/global-setup.ts` gives each
invocation its own scratch database (`PLAYWRIGHT_SCRATCH_DB`, else
`database/browser-scratch-<pid>-<ms>.sqlite`). A leaked `php artisan serve` on the default port takes
every other session's suite down (KI-73), and one fixed scratch path let a second session delete the
first one's database mid-run (KI-80). Run the browser suite with a session-private pair, e.g.
`PLAYWRIGHT_PORT=8141 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-8141.sqlite npm run test:browser`.

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
| Style fix / style check                          | `vendor/bin/pint --test --format agent <paths>` / `composer lint`                           |
| Static analysis                                  | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`                                |
| TypeScript                                       | `npm run typecheck`                                                                         |
| Assets                                           | `npm run build`, `npm run dev`                                                              |
| Lore gate                                        | `composer lore`, `composer lore-code`                                                       |
| Fresh DB with offline catalog data               | `php artisan migrate:fresh --seed` (destructive, §11)                                       |
| Fetch / replay / import / backup                 | `php artisan uma:fetch`, `uma:reparse <source>`, `uma:import:support-cards`, `uma:backup`   |
| Mirror catalog artwork (manual only)             | `php artisan uma:fetch-art` (`--kind`, `--dry-run`, `--refetch`)                            |
| Routes / commands / config                       | `php artisan route:list`, `php artisan list`, `php artisan config:show uma.sources`         |
| Documentation census                             | `composer docs` (python)                                                                    |
| Markdown fence check                             | `python tools/md_fence_check.py [paths]` (+ `md_fence_check_selftest.py`)                   |

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
- **Execution ledger.** `docs/research-scratch/SLICE-RECORDS.md` section `## execution-ledger-2026-10-17.md` is append-only: a
dispatch appends one numbered section and never edits an earlier one. The file mixes CRLF and LF
line endings and is not clean UTF-8, so read its diff with `git diff --ignore-cr-at-eol` and
compare byte counts rather than rendered text.
- **Fetch pipeline.** A new source is config entry + one parser class + a test against a
  stored fixture + a robots.txt and rate-limit note. Never write to `is_manual` rows.
- **Snapshots.** Gitignored and keyed on a content hash. A second database asked on the
  same day is told the document is unchanged (KI-27); fill it with
  `php artisan uma:reparse <source>`, which is safe on a populated database.
- **Generated and protected files.** Do not hand-edit: `PRODUCT.md`
  (generator-owned), `composer.lock`, `package-lock.json`, `public/build/`, `vendor/`,
  `node_modules/`, `storage/` (including snapshots and backups),
  `docs/design-research/_scratch/`, `tools/__pycache__/`, and the `docs/design-research/`
  prototypes. Regenerate instead. Lockfile updates ship with the dependency change that
  caused them, never alone.
- **Per-agent configuration.** The `.claude/`, `.cursor/`, `.codex/`, `.kilo/`,
  `.opencode/`, `.github/`, and similar directories are local, gitignored scaffolding.
  The tracked instruction sources are `AGENTS.md`, `CLAUDE.md`, `docs/research-scratch/GOVERNANCE.md`,
  `.agentrules`, and `.ai/**`.

## 12. Security and privacy

No security document exists; the model is `ARCHITECTURE.md` §8.

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
- Unrecorded values render as `N/A` with a **disclosure channel**, never a default.
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
- `git commit <path>` guards the index, not the working tree: it commits that path's
  working-tree content, so another session's uncommitted lines in the same file land in the
  commit with yours. Name every path on the command line to keep the index clean, and skip
  a file or stage only your own hunk when the working tree carries another session's work.
- Never commit `vendor/`, `node_modules/`, `storage/` snapshots or backups, `.env`, or
  `public/build/`.
- Deleting or skipping a test needs owner approval and a stated reason in the commit.
- `CHANGELOG.md` and `VERSION.md` are tracked files; `CHANGELOG.md` is updated by the Docs Writer on significant landings, and `VERSION.md` is updated by the Architect on phase exits.
- Tracked root documents: `AGENTS.md`, `ARCHITECTURE.md`, `ARCHITECTURE-ESSENTIALS.md`,
`CHANGELOG.md`, `CLAUDE.md`, `DESIGN.md`, `KNOWN-ISSUES.md`, `MCP_SETUP.md`, `PRD.md`,
`PRODUCT.md`, `README.md`, `SCREEN_SPEC.md`, `VERSION.md`. The set is closed; a new
root document is not added, and the masters under `docs/research-scratch/` are indexed by
`docs/research-scratch/INDEX.md`. *Dated correction 2026-10-26 (Round 21 fold): this list also carried
`coverage.md` and `SKILL_REGISTRY.md`; both were folded into `docs/research-scratch/GOVERNANCE.md` as <!-- census: quoted-original -->
the sections `## coverage.md` and `## SKILL_REGISTRY.md` and deleted from the root, so the named set is thirteen. The root directory carries 14 markdown
files; the fourteenth, `CONTRIBUTING.md`, is a name this list has never carried, and this pass did not
add it.*

## 15. Definition of done

A change is done when all of these hold, or when the exception is reported explicitly:

- [ ] The §2a plan-first contract was followed: the goal, acceptance criteria, TODO list
      and verification evidence exist, the TODOs are current, and every open item is
      reported rather than silently dropped.
- [ ] The implementation matches the architecture and the conventions in §7 and §8.
- [ ] A behavior change ships with a test; copy-only changes ship without one.
- [ ] The narrow tests pass, and the broader suite plus the hand-off sequence in §9 were
      run for a cross-cutting change, with the output attached.
- [ ] `vendor/bin/pint --test --format agent <paths>` was run on the changed files (never
      `pint --dirty`, KI-75); PHPStan level 6 is clean; `npm run
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
- **A hot Vite dev server plus the package's default SSR settings makes every full page load 500 after
  30s** (KI-76). `INERTIA_SSR_ENABLED` defaults to `true`, `config/inertia.php` is not published so
  `ssr.timeout` is unset, and while `public/hot` exists Inertia skips its "no bundle" guard and POSTs
  to the dev server's `/__inertia_ssr`, which nothing answers: the request blocks until PHP's
  `max_execution_time` fatals it. `.env.example` sets `INERTIA_SSR_ENABLED=false` with the reason;
  keep that set, or publish `config/inertia.php` with a short `INERTIA_SSR_TIMEOUT`.
- The lore gate is advisory and easy to trip by accident, including inside this file: the
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
| 2026-10-04   | Rewritten as an operational contract: added §2 precedence and the Boost-conflict note, §3 documentation map with the pointer stubs, §9 change-class validation matrix, §11 change-safety, §16 ambiguity handling, §17 legacy handling. Pointer stubs resolved to their masters (CONSTRAINTS.md -> GOVERNANCE.md). Escaped the banned word families so this file no longer produces lore-gate hits needing a ruling. Role table and the seven escalation paths preserved verbatim in substance.   | Agents were reading a stale file: it cited rules that had moved into `docs/research-scratch/` masters, repeated generic Boost guidance that contradicts the no-auth design, and quoted the banned vocabulary.                                                                                                                                                                                                                               |
| 2026-10-05   | One cell of the §3 documentation map: `docs/scenarios/07` is no longer described as a known-gap stub.                                                                                                                                                                                                                                                                                                                                                                                                | The owner asked for the fourth `[Global]` scenario to be researched and its documents updated; the primary read landed, so the map's own description of the file went stale. The §6 non-negotiables, the gates and the precedence chain are unchanged.                                                                                                                                                                                      |
| 2026-10-05   | §8 gains an **Artwork** bullet, §17's "no image uploads" line is disambiguated, and §18 gains a trap about the asset host's HTML 404 body.                                                                                                                                                                                                                                                                                                                                                           | `ADR-0021` was accepted the same day and `AGENTS.md` still read as though every image question ended at escalation 7. It does not: uploads stay cut, sourced artwork is authorized and unbuilt. An agent reading only this file would have refused work the owner had just authorized.                                                                                                                                                      |
| 2026-10-06   | §8's Artwork bullet and §17 corrected: **the display half is built**, and the bullet now names the six surfaces, the loopback-only `src` rule, and the two OQ-6 remainders. §18 gains the empty-mirror trap.                                                                                                                                                                                                                                                                                         | The row above and §17 both asserted the display half was unbuilt. It is: `ArtworkSlot.vue` plus `CatalogController.php:121,132,299`, `SupportCardController.php:65,110` and `TrainingRunController.php:1119`, and 128 frames were verified rendering in Chromium with zero console errors after the first `uma:fetch-art` pass. An agent reading only this file would have refused to debug an image question as if nothing displayed it.   |
| 2026-10-06   | §1, §6, §7 and §13 corrected for the frontend stack: the web surface is **Inertia + Vue 3**, `resources/views/` holds 4 Blade files and no components, `resources/js/` holds 51 SFCs, and the scenario components take a required `scenarioLabel` rather than declaring `scenario`. §7's "no Inertia/SPA" ban is withdrawn in place, §9 and §15 widen the asset and typecheck rows to Vue, and §18's Blade-manifest line is de-Bladed.                                                               | `8e58b65` folded the Trainer Desk 2.0 line into `master` and `0ea8d43` retired the Blade shell, so the file described a surface that no longer exists. Measured on this tree, not remembered. Per §2 the code wins and the rule is stale; the rule was left standing where it is still true (Livewire, Redis and Excel are absent from `composer.lock`) rather than deleted wholesale.                                                      |
| 2026-10-06   | §9: `migrate:status` on the dev database joins the Schema row and the hand-off, with a paragraph stating that the suite cannot see that file.                                                                                                                                                                                                                                                                                                                                                        | KI-60. Four committed migrations sat Pending on `database/database.sqlite` for four days while 1,326 tests passed, because `phpunit.xml:64` forces `DB_DATABASE=:memory:` and every test builds its own schema. A green suite was reported as evidence of a working application and it was not. This tightens the bar, which §5 permits an agent to apply; relaxing it remains escalation 4.                                                |
| 2026-10-08   | §9 gains the cost-aware escalation policy: Level 1 focused runs during implementation, Level 2 related regression at slice end, Level 3 full verification only at checkpoints; the full suite is a gate, not the feedback loop. Adds failure classification (regression, pre-existing, unrelated, infrastructure, flaky, inconclusive) with focused reproduction, the explicit rule that a full-suite pass may never be claimed unexecuted, and the statement that no level licenses weakening, deleting, or suppressing tests. Repo-agnostic in shape; the existing change-class table and hand-off stay binding as its Level 3. | Agents were burning most of their implementation time rerunning the expensive full and browser suites after small localized edits. The escalation ladder was implicit in §9 ("narrow first") but never written down, so nothing stopped the reflexive broad run or required attributing a broad-run failure before re-running. Concurrent-agent work in this tree also made unrelated failures common, which the classification step now handles explicitly. |
| 2026-10-12   | §9 gains the background/parallel test-execution rule: a Level 1/Level 2 run may be backgrounded to a session-private `.scratch-uma/<session>/` log, nothing that writes (`pint` without `--test`, `migrate`, `npm run build`) is backgrounded while a peer session works, a red background run is classified before anything re-runs, a broad sweep is pre-flighted with a timed SQLite read, and the browser suite is always session-private. §2's binding-decisions row is corrected to `docs/adr/0001`-`0024`; §6's `resources/js/` counts are corrected to the measured 48 pages and 63 components (2026-10-12). | KI-70 (a shared-worktree file is a collision surface; one session's unbuildable file blocked every peer's browser gate for a day), KI-73 and KI-80 (a leaked `:8127` server and a shared scratch database each take a peer's suite down), KI-74 (a gate run on a contended worktree is void, and a red sweep under load reads as a product regression), KI-75 (`pint --dirty` reformatted another session's uncommitted file). The §2 row still ended at 0018 while 0024 is on disk, and §6's counts were a 2026-10-06 snapshot. |
| 2026-10-13   | §9's session-private rule is generalized: everything a session writes under `.scratch-uma/` (logs, scratch databases, dumps, probes, driver scripts) goes in `.scratch-uma/<session>/...`, never the root, and both browser-harness examples are repointed from `.scratch-uma/browser-<port>.sqlite` to the session path; the `.scratch-uma` root now holds no files beyond its README and reorganize script. | Owner instruction 2026-10-13 after the root had accumulated 676 loose files (104 scratch SQLite databases, 175 logs, 72 `.exit` sidecars) because every agent wrote there by default (KI-70). The same-day sweep of `.scratch-uma/` moved 635 files into the buckets of `.scratch-uma/reorganize-scratch.ps1`; on the follow-up ruling the 41 citation-pinned evidence files moved to `.scratch-uma/cited/` and all 72 citation references across 13 tracked files were repointed, reversing for this tree the 2026-10-06 hold-in-place ruling recorded in `PROCESS-PLANS.md`. |
| 2026-10-16   | §9 gains the **test-dispatch** subsection: a run may be dispatched to a subagent rather than launched synchronously, on the condition that it works against a frozen tree state (an isolated `git worktree add` at the commit under test), reports its commit sha and dirty-path counts at start and end, and classifies every failure before anything re-runs. Prompts that direct this work should name dispatch rather than a synchronous command. The existing background-execution rules — session-private log, nothing-that-writes-is-backgrounded, red-runs-classified-before-reruns, KI-74 pre-flight, KI-73/KI-80 browser isolation — are not relaxed. | Synchronous runs against the shared tree blocked the main agent for the full suite duration (up to 30 minutes) and produced inconclusive evidence whenever a peer session was editing: the 19 red browser cases attributed to "peer work" and the six file-level holds all trace to that pattern. The commit agent's `verify-commit-pass` run on 2026-10-16 proved the isolated-worktree dispatch gives a signal attributable to a commit — its only red cases trace to a committed defect (`64fc4b8`'s label flip), not to peer interference. The pattern was already implicitly permitted by the background-execution rule; it is now named and its preconditions stated. |
| 2026-10-22   | §9 gains the **stay inside the repository** rule: every artifact an agent writes lands under this repository's own tree (`.scratch-uma/<session>/...`, or a temporary directory the repository owns), and never in a drive root, beside the repository, or in a shared scratch directory outside it, unless the owner names the path for that task. The session-private rule above stays the first rung; the new paragraph generalizes it rather than replacing it. | Owner instruction 2026-10-22, after a cleanup measured what agents had been leaving outside the tree: three DSH ACL output directories under `D:\Projects\`, a 99 MB session scratchpad at `D:\private`, a stale copy of this repository's own scratch tree at `D:\.scratch-uma`, and 37 loose files in `D:\tmp`. No tracked file referenced any of them, and the `D:\tmp` survivors included another project's database and script, which is exactly the collision the rule exists to prevent. |
| 2026-10-24   | Adds §2a, the mandatory plan-first contract: goal, scope, acceptance criteria, ordered TODO list, verification and risks before implementation; native task-list use with a visible Markdown fallback; TODO maintenance and blocked-item reporting; the statement that a plan is not an approval gate; the no-persistent-planning-files default; and the verified-completion checklist. §15 gains the matching first checkbox. This edit also restores the whole §3-§20 contract that commit `baa4b755` had replaced with an injected guidelines blob, and drops the same stale blob appended to `CLAUDE.md`. | Every task now must show its goal, acceptance criteria and task list before a file changes, consistently across agents, while keeping routine work unblocked and the existing gates, precedence and documentation policy intact. The restore is part of the same alignment: the corrupted `AGENTS.md` had lost the documentation map, change-safety, command reference and definition of done that every other tracked instruction file cites. |
| 2026-10-26   | Round 21 fold, owner-ordered: the root `coverage.md` and `SKILL_REGISTRY.md` and the eight files under `docs/audits/` and `docs/proposals/` dated 2026-10-24/25 (the group-ab audit, pass report and Phase 8.1 browser triage; the group-ab plan, consolidated plan and rulings-owed brief; the remaining-work roadmap and its rulings brief) were folded into `docs/research-scratch/` masters and deleted from their locations. §3's `coverage.md` map row now names `GOVERNANCE.md` sections, and §14's tracked root set shrinks to thirteen. Nothing else in this file moved. | The owner ordered the consolidation. `INDEX.md` §File discipline forbids standalone markdown outside the masters, and the two root skill files were not in that section's standing-set table at all, so the fold clears a standing violation as well as absorbing ten sources. | <!-- census: quoted-original -->
| 2026-10-27   | §13's named-primitive migration ruleblock was removed, per its own banner ("removed at Phase 6 exit"). §9's `count: 219` debt figure was re-verified against `tools/ui-lint-baseline.json`; no other section moved. | The named-primitive migration exited when Phase 6 released 2.1.0 on 2026-10-23 (`docs/proposals/frontend-architecture-plan.md:1006`); that plan's rationale at `:791` is that a migration-scoped rule outliving its migration becomes stale instruction. The clean tree measures 219 arbitrary values; the working tree's 221 are two peer-owned additions (`Review/Index.vue:300`, `Career/SkillsPlanner.vue:374`), so the baseline is unchanged. |
