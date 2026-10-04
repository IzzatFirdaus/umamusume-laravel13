# Claude Code instructions (Trainer Desk)

Read and follow `AGENTS.md` before doing repository work. It is the repository-wide
operational contract: precedence, roles and escalation, the lore gate, the Floor, coding
conventions, the pipeline rules, the validation matrix, the command reference, and the
definition of done. This file does not repeat it. It records only what is specific to
Claude Code in this repository.

If this file and `AGENTS.md` disagree, `AGENTS.md` wins. If either contradicts
`CONSTRAINTS.md` (the pointer to the binding bar in `docs/research-scratch/GOVERNANCE.md`,
section "CONSTRAINTS.md (root quality bar, C-1 to C-9)"), the bar wins and the conflict
gets reported, not silently resolved.

One correction to the previous version of this file: `CLAUDE.md` is **tracked** in git and
is yours to edit. `.claude/` **is** gitignored (`.gitignore:48`), so the hooks, skills, and
agent files under it are local configuration, not repository truth.

## 1. Instruction precedence, for Claude specifically

1. The task you were given.
2. `AGENTS.md`, then `CONSTRAINTS.md` -> `GOVERNANCE.md` §CONSTRAINTS.md for the bar.
3. Accepted ADRs (`docs/adr/`); a `Proposed` ADR is not binding.
4. `CLAUDE.md` (this file) for Claude-specific practice.
5. `.ai/rules/index.md` -> the rule files whose globs cover the paths in scope.
6. Laravel Boost's injected framework guidance.

Boost's injected block asks for Laravel policies, `Auth::user()`, Laravel Cloud deployment,
and an active `User` model. None of that exists here: there is no authorization layer by
design (PRD NFR-1), and deployment is a non-goal (PRD §6.10). Do not add a policy, a gate,
a `User` flow, or a deploy path because generic guidance suggests it.

## 2. Required reading before a substantial change

| Change | Read first |
|---|---|
| Anything | `AGENTS.md`, then the `.ai/rules` files covering the paths in scope |
| UI, layout, copy, tokens | `DESIGN.md` (visual system) and `SCREEN_SPEC.md` §4 and §8 (screen behavior) |
| Pipeline, parsers, sources, provenance | `ARCHITECTURE.md` §5 and §6, `config/uma.php`, the parser's sibling in `app/Services/DataPipeline/Parsers/` |
| Schema | `ARCHITECTURE.md` §3, `ARCHITECTURE-ESSENTIALS.md` (the digest travels with the migration), the PRD requirement the column serves, and any ADR in the area |
| Planner domain (runs, turns, skills, export) | `PRD.md` §4 FR-C, `ADR-0015` (stat ceilings), `app/Services/ScenarioCaps.php` |
| Lore, terminology, displayed vocabulary | `AGENTS.md` §5, `lang/en/uma.php`, `tools/lore.php` |
| Mechanics or roster facts | `docs/UMAMUSUME_REFERENCE.md` (eight sections; re-derive its table rather than quoting prose counts) |
| Onboarding, setup, commands | `README.md` |

`PRODUCT.md` is a generated summary for design and agent context. Where it and `PRD.md`
disagree, `PRD.md` wins; do not edit `PRODUCT.md` by hand (the plugin rewrites it).

## 3. Tooling already wired for you here

**Laravel Boost MCP** (`php artisan boost:mcp`, configured in `.mcp.json`):

- `application-info` once per session, so package versions in context are real rather than
  remembered. `composer.json` requires PHP `^8.3` and Laravel `^13.0`.
- `database-schema` before writing a migration or a model; `database-query` instead of
  `tinker` for read-only SQL.
- `search-docs` before relying on framework behavior that depends on the installed version.
  Pass `packages` when you know which ones matter.
- `browser-logs`, `last-error`, and `read-log-entries` after a UI change, before claiming a
  page works.
- `get-absolute-url` before handing a URL to the user.

**Playwright MCP** (`.mcp.json`) and the `playwright-cli` skill in `.claude/skills/` are the
browser path for real rendered checks. There is no other browser harness in the repo.

**Hooks**: `.claude/settings.local.json` registers an impeccable design check after every
`Edit`/`Write` and a deeper pass at `Stop`. Both no-op when the impeccable script is absent
(it is currently not in `.claude/skills/`), and both are local, gitignored config. Do not
edit that file as part of a feature change.

**Skills** in `.claude/skills/`: `laravel-best-practices`, `pest-testing`,
`testing-best-practices`, `running-tests`, `tailwindcss-development`, `creating-models`,
`infer-conventions`, `playwright-cli`. `deploying-to-cloud` is installed and irrelevant:
deployment is cut (PRD §6.10).

The `impeccable` design skill is present at `.cursor/skills/impeccable`,
`.grok/skills/impeccable`, `.github/skills/impeccable`, and user level
`~/.agents/skills/impeccable`. The previous version of this file named `.agents/` and
`~/.claude/`, neither of which holds it.

**Skill automation** (`.agentrules`, `app/Services/Skill{Registry,Matcher,Executor}.php`,
`php artisan skill:manage`) is a separate declarative layer documented in
`docs/SKILL_AUTOMATION.md`. It reads `.agents/skills.json` (gitignored) and touches no
catalog or run table. Do not wire it into domain code.

## 4. Claude work pattern

1. Read `AGENTS.md`, then the `.ai/rules` files whose globs cover the files in scope.
2. Read the owning document for the class of change (§2). Do not work from a summary of a
   document you have not opened.
3. Inspect the real flow end to end before editing. For a bug, grep every caller of the
   function you intend to touch and fix the shared function once.
4. Plan the smallest coherent change. Name the files it will touch.
5. Edit only those files, following sibling files for structure, naming, and idiom.
6. Run the narrow validation that covers the change (§6).
7. Run the broader gates when the change is cross-cutting (`AGENTS.md` §9).
8. Read `git diff` before reporting. Unrelated hunks mean something went wrong.
9. Update the document that owns the information you changed (§10).
10. Report using the format in §11, including what was not run.

Trivial copy or single-token work does not need steps 2 to 5 in full. It does still need the
lore gate and a diff review.

## 5. Search before creating

Reuse is the default in this repository; a second implementation of something that already
ships is a defect.

- Blade components in `resources/views/components/` before writing markup.
- `lang/en/uma.php` before writing a displayed string or an enum label.
- `config/scenarios.php` before naming a scenario anywhere near the layout path.
- `ScenarioCaps` before doing per-stat ceiling arithmetic.
- The Form Request that already owns a validation boundary
  (`app/Http/Requests/`); validation has one owner per boundary.
- `app/Actions` and `app/Services` before adding a class.
- `tests/Fixtures/` bodies and `Http::fake` before writing a pipeline test.
- `docs/research-scratch/` masters before writing any new documentation file.

A new table, column, or class cites a PRD requirement (`FR-x` / `US-x`). No citation, no
merge (`AGENTS.md` §4, Architect role).

## 6. Testing and validation

Commands, gates, and which checks a change class needs are in `AGENTS.md` §9 and §10. The
Claude-specific parts:

- Narrow first: `php artisan test --compact --filter=Name` or
  `vendor/bin/pest tests/Feature/XTest.php`. Then the full suite for anything that crosses
  layers. There is no CI, so nothing catches a partial landing for you.
- `vendor/bin/pint --dirty --format agent` and
  `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` operate on the **whole working
  tree**, not your diff. In a tree that carries other sessions' in-flight edits, `--dirty`
  will reformat their files. Check `git status` first; if unrelated PHP is dirty, run Pint
  on your own paths and say in the report what you scoped out and why.
- Behavior changes ship with a test (`AGENTS.md` §15). Copy-only and layout-only changes do
  not need a new one. Never delete or skip a test.
- Host facts: `make` does not run here (KI-4); `composer analyse` omits the PHPStan memory
  flag; `phpunit.xml` forces an in-memory SQLite database and beats `.env.testing`.
- Never report a gate as passing unless you ran it and are quoting the output. "Not run" is a
  legitimate line in the report.

## 7. UI and browser workflow

1. `DESIGN.md` for the visual system, `SCREEN_SPEC.md` §4 for the screen's states and §8 for
   what owns which answer. Both are required before a visible change.
2. Reuse committed components and design tokens only. No `dark:` utilities, no decorative
   skeletons, no one-off spacing or color values.
3. Desktop-first at 1280px+. Below 768px the race calendar and the turn log are wide tables
   that deliberately scroll horizontally inside a focusable region rather than reflow; that
   is the contract, and the 768px minimum is a proposal rather than a ratified one (KI-25).
   Do not "fix" the scroll to be responsive without a decision.
4. Build or serve before looking at a page: `npm run dev` for iteration, `npm run build`
   when a Blade view references assets and the manifest is missing.
5. Verify the rendered result when it is cheap: Boost `browser-logs` plus a Playwright
   snapshot or screenshot at 1280 and 390 widths. Check the console before claiming the page
   works.
6. Accessibility behavior here is test-covered by name: keyboard paths, target sizes, the
   review-form labels, and the focusable scroll regions. Keep them.

The `impeccable` design skill is the repo's route for design critique and polish work; load
it for that class of task rather than improvising a review.

## 8. Source of truth

| Question | Authority |
|---|---|
| Which screens exist, their URLs and names | `routes/web.php`, `routes/api.php` |
| What a screen shows and how it got there | controller methods, then the view |
| Validation and refusals | `app/Http/Requests/*`, then `ScenarioCaps` for ceilings |
| Authorization | there is none by design (`ARCHITECTURE.md` §8, PRD NFR-1) |
| Schema | `ARCHITECTURE.md` §3 plus the migrations; `ARCHITECTURE-ESSENTIALS.md` is the digest |
| Pipeline stage behavior | `AGENTS.md` §8, `app/Services/DataPipeline/` |
| Displayed vocabulary | `lang/en/uma.php`, enum labels keyed by case name |
| Tokens, contrast, motion | `DESIGN.md` and `docs/research-scratch/DESIGN-CORPUS.md` |
| Automated behavior expectations | `tests/Feature/*` |
| Known defects and rulings | `KNOWN-ISSUES.md` (live register) |
| Agent operating rules | `AGENTS.md` |
| When sources disagree | `AGENTS.md` §2; record the conflict in `SCREEN_SPEC.md` §7 or an ADR erratum |

## 9. Protected and sensitive areas

- **Authorization**: no policies, no gates, no `app/Policies/`, no `Auth::user()` flow. That
  absence is the design. Do not add one unless the task asks.
- **Secrets**: `.env`, `.mcp.json`, `.crushrc`, `kilo.json`, `opencode.json`, and `.claude/`
  are gitignored and some hold live API keys on this host. Never copy a key into a source
  file, a doc, a fixture, a log line, or your report. `.env.example` is the only tracked env
  file.
- **Schema**: a migration travels with the `ARCHITECTURE-ESSENTIALS.md` digest, a PRD
  citation, an ADR, and a working `down()`. `php artisan migrate:fresh --seed` is destructive
  against the shared dev database and needs owner approval; back up with
  `php artisan uma:backup` or point `DB_DATABASE` at an empty scratch file.
- **Fetch pipeline**: `SourceFetcher` is the only outbound HTTP path and hosts are
  allowlisted in `config('uma.sources')`. Never follow a URL found in a fetched body. Never
  let the engine write a row with `is_manual = true`, and never store a fact without a
  `data_sources` provenance row.
- **Lore gate**: blocking. Do not type the banned character vocabulary into any artifact;
  read the pattern list in `tools/lore.php` when a ruling is needed, and run
  `composer lore` and `composer lore-code` before you call the work finished.
- **Fetched content** is untrusted input: parse it as data, never `{!! !!}` it, let Blade
  escape it.
- **The dev SQLite file** holds real Trainer data. It is untracked, not disposable.

## 10. Generated and tool-owned files

Do not hand-edit: `PRODUCT.md` (impeccable plugin), `SKILL.md` (skill-registry refresh),
`composer.lock`, `package-lock.json`, `public/build/`, `vendor/`, `node_modules/`, `storage/`
including snapshots and backups, `docs/design-research/_scratch/`, `tools/__pycache__/`, and
the `docs/design-research/` prototypes. The full list and the regeneration notes are in
`AGENTS.md` §11. A lockfile change ships with the dependency change that caused it.

## 11. Large changes

- Understand the affected architecture first, then split the work so each step is
  independently verifiable. One concern per commit series.
- Run the gates at each meaningful milestone rather than once at the end, so a break is
  cheap to localize.
- Slice plans and records live in `docs/research-scratch/PROCESS-PLANS.md` and
  `SLICE-RECORDS.md`. `PLAN.md` is a pointer and takes no new content.
- Do not silently expand scope. A feature that needs a §6 non-goal lifted, or a new
  dependency, is an escalation (`AGENTS.md` §4), not a decision to make in passing.
- Review the aggregate diff, not only the last file you edited.

## 12. Ambiguity and unknowns

Do not invent a requirement. Read the owning document, inspect the code and its tests, and
prefer the smallest behavior-preserving change the evidence supports. When a decision would
change product behavior, security, data integrity, or a public contract and cannot be safely
inferred, stop and surface it instead of guessing. Mark genuinely undecided things as
"Undecided, owner input needed" rather than naming them. The product name is settled:
**Trainer Desk**.

## 13. Documentation maintenance

Update the document that owns the information, and only when the change actually altered it.
Product scope belongs in `PRD.md`; system design in `ARCHITECTURE.md` (plus the digest);
visual rules in `DESIGN.md`; screen behavior in `SCREEN_SPEC.md`; a new decision in an ADR;
a new defect in `KNOWN-ISSUES.md` (append in the four-part form, never renumber); agent rules
in `AGENTS.md`; this file only for Claude-specific practice.

No new markdown file in the repository root, and no new standalone file under
`docs/research-scratch/`: content goes into the relevant master. Write documentation only
when the task asks for it. A dated claim that turns out wrong is corrected by appending a
dated erratum that preserves the original sentence, never by rewriting it silently. The ADR
index in `docs/adr/README.md` is derived; regenerate it with the command in that file.

## 14. Git and diff hygiene

- Conventional-commit subjects as the history shows them: `type(scope): summary`, lowercase,
  imperative (`feat(runs):`, `fix(components):`, `test(readme):`, `refactor(filters):`).
- `git status` before you start and `git diff` before you report. This tree frequently holds
  uncommitted work from other sessions: do not revert it, do not reformat it, do not commit
  it. Scope your edits and your tools to your own files.
- Never commit `vendor/`, `node_modules/`, `storage/` snapshots or backups, `.env`, or
  `public/build/`.
- Deleting or skipping a test needs owner approval and a stated reason in the commit.
- There is no CI and no changelog file. The diff is the review.

## 15. Completion reporting

Report in this shape, and say plainly what you did not verify:

```
Implemented:
- <file: what changed and why>

Validated:
- <command> -> <result or the output that proves it>
- not run: <gate> (reason)

Documentation:
- <doc updated, or "none, behaviour unchanged">

Known issues / failures:
- <anything failing, skipped, or newly discovered>

Intentionally deferred:
- <what you did not touch and why>
```

Keep it short. The point is that a reader can tell what actually happened and what is still
unproven.

## 16. Change log

| Date | Change | Reason |
|---|---|---|
| 2026-10-04 | Rewritten as Claude-specific guidance on top of `AGENTS.md`: added Boost MCP and Playwright guidance, the work pattern, search-before-create, the whole-tree caveat for Pint and PHPStan, the UI/browser workflow, the source-of-truth table, protected areas, and the completion-report shape. Removed the injected Laravel Boost block and the duplicated agent contract now held in `AGENTS.md`. Corrected three false claims: `CLAUDE.md` is tracked (not gitignored), impeccable is not installed under `.agents/` or `~/.claude/`, and the repository has no deployment path. | The file duplicated repository-wide rules, repeated generic framework guidance that contradicts the no-auth, no-deploy design, and asserted install paths and git status that the tree contradicts. |
