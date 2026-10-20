# Claude Code instructions (Trainer Desk)

Read and follow `AGENTS.md` before doing repository work. It is the repository-wide
operational contract: precedence, roles and escalation, the lore gate, the Floor, coding
conventions, the pipeline rules, the validation matrix, the command reference, and the
definition of done. This file does not repeat it. It records only what is specific to
Claude Code in this repository.

If this file and `AGENTS.md` disagree, `AGENTS.md` wins. If either contradicts the binding
bar in `docs/research-scratch/GOVERNANCE.md`, section "CONSTRAINTS.md (root quality bar,
C-1 to C-9)", the bar wins and the conflict gets reported, not silently resolved.
(`CONSTRAINTS.md` was folded into that master in `1e84b0c`; the root file no longer exists.)

One correction to the previous version of this file: `CLAUDE.md` is **tracked** in git and
is yours to edit. `.claude/` **is** gitignored (`.gitignore:48`), so the hooks, skills, and
agent files under it are local configuration, not repository truth.

## 1. Instruction precedence, for Claude specifically

1. The task you were given.
2. `AGENTS.md`, then `docs/research-scratch/GOVERNANCE.md` §"CONSTRAINTS.md (root quality bar, C-1 to C-9)" for the bar.
3. Accepted ADRs (`docs/adr/`); a `Proposed` ADR is not binding.
4. `CLAUDE.md` (this file) for Claude-specific practice.
5. `.ai/rules/index.md` -> the rule files whose globs cover the paths in scope.
6. Laravel Boost's injected framework guidance.

The root quality bar (C-1..C-9) is a floor no task can relax; per `AGENTS.md` §2
("Instruction scope and precedence") it is "never weakened to pass a check."

Boost's injected block asks for Laravel policies, `Auth::user()`, Laravel Cloud deployment,
and an active `User` model. None of that exists here: there is no authorization layer by
design (`PRD.md` §5 "Non-Functional Requirements" NFR-1, and `ARCHITECTURE.md` §8 "Security
Model"), and deployment is a non-goal (`PRD.md` §6 "Non-Goals (eliminated legacy features)",
item 10). Do not add a policy, a gate, a `User` flow, or a deploy path because generic
guidance suggests it.

This repository's `AGENTS.md` is not read natively by Claude Code, because a root `CLAUDE.md` satisfies the project-instructions check, so the line-3 instruction to read `AGENTS.md` is the mechanism by which the contract is reached.

The discretionary read is deliberate: the `@AGENTS.md` import was priced at 14,043 tokens per session for `AGENTS.md` at 679 lines and declined, and the trigger to revisit is an actual miss, which is a session acting without the contract recorded in `KNOWN-ISSUES.md`.

## 2. Required reading before a substantial change

| Change                                         | Read first                                                                                                                                                                                                                |
| ---------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Anything                                       | `AGENTS.md`, then the `.ai/rules` files covering the paths in scope                                                                                                                                                           |
| UI, layout, copy, tokens                       | `DESIGN.md` (visual system) and `SCREEN_SPEC.md` §4 "Screen Specifications" and §8 "Source of Truth" (screen behavior)                                                                                                    |
| Pipeline, parsers, sources, provenance         | `ARCHITECTURE.md` §5 "Data-Fetching Engine (JP ↔ Global cross-reference)" and §6 "Integration Pattern (scraping → normalization → storage → cache)", `config/uma.php`, the parser's sibling in `app/Services/DataPipeline/Parsers/` |
| Schema                                         | `ARCHITECTURE.md` §3 "Database Schema & Eloquent Models", `ARCHITECTURE-ESSENTIALS.md` (the digest travels with the migration), the PRD requirement the column serves, and any ADR in the area                      |
| Planner domain (runs, turns, skills, export)   | `PRD.md` §4 "Functional Requirements" FR-C "Training-run domain (Trainer's own data)", `docs/adr/0015-per-scenario-stat-ceiling.md` (stat ceilings; amended by ADR-0025), `app/Services/ScenarioCaps.php`              |
| Lore, terminology, displayed vocabulary        | `AGENTS.md` §5 "Non-negotiables", `lang/en/uma.php`, `tools/lore.php`                                                                                                                                                     |
| Mechanics or roster facts                      | `docs/UMAMUSUME_REFERENCE.md` (eight sections; re-derive its table rather than quoting prose counts)                                                                                                                        |
| Onboarding, setup, commands                    | `README.md`                                                                                                                                                                                                               |

`PRODUCT.md` is a generated summary for design and agent context. Where it and `PRD.md`
disagree, `PRD.md` wins; do not edit `PRODUCT.md` by hand (the plugin rewrites it).

## 3. Tooling already wired for you here

**Laravel Boost MCP** (`php artisan boost:mcp`, configured in `.mcp.json`):

- `application-info` once per session, so package versions in context are real rather than
  remembered. Read versions from this tool, not from memory or documentation.
- `database-schema` before writing a migration or a model; `database-query` instead of
  `tinker` for read-only SQL.
- `search-docs` before relying on framework behavior that depends on the installed version.
  Pass `packages` when you know which ones matter.
- `browser-logs`, `last-error`, and `read-log-entries` after a UI change, before claiming a
  page works.
- `get-absolute-url` before handing a URL to the user.

**Browser tooling**: the `playwright-cli` skill in `.claude/skills/` drives a real browser for
rendered checks, and the repo's own Playwright suite is `@playwright/test` with
`playwright.config.ts` and specs under `tests/browser/` (`npm run test:browser`,
session-private per `AGENTS.md` §9 "Testing and validation"). There is **no** Playwright MCP
server: `.mcp.json` registers only `filesystem`, `git`, `docs`, `laravel-boost`, and
`gitkraken`.

**Hooks**: `.claude/settings.local.json` registers an impeccable design check after every
`Edit`/`Write` and a deeper pass at `Stop`. The check is active on this host: the script
resolves to a user-level installation outside the repository, so it runs here but not for
anyone who clones. Both are local, gitignored config. Do not edit that file as part of a
feature change.

**Skills** in `.claude/skills/`: `laravel-best-practices`, `pest-testing`,
`testing-best-practices`, `running-tests`, `tailwindcss-development`, `creating-models`,
`infer-conventions`, `playwright-cli`, `impeccable`. `deploying-to-cloud` is installed and
irrelevant: deployment is cut (`PRD.md` §6 "Non-Goals (eliminated legacy features)", item 10).

The `impeccable` design skill is present at `.claude/skills/impeccable` (host-local,
gitignored), `.cursor/skills/impeccable`, `.grok/skills/impeccable`,
`.github/skills/impeccable`, and user level `~/.agents/skills/impeccable`. The previous
version of this file named `.agents/` and `~/.claude/`, neither of which holds it.

**Skill automation** (`.agentrules`, `app/Services/Skill{Registry,Matcher,Executor}.php`,
`php artisan skill:manage`) is a separate declarative layer documented in
`docs/SKILL_AUTOMATION.md`. It reads `.agents/skills.json` (gitignored) and touches no
catalog or run table. Do not wire it into domain code.

## 4. Claude work pattern

1. Read `AGENTS.md`, then the `.ai/rules` files whose globs cover the files in scope
   (`AGENTS.md` §7 "Coding conventions").
2. Read the owning document for the class of change (§2 "Required reading before a substantial
   change"). Do not work from a summary of a document you have not opened.
3. Inspect the real flow end to end before editing. For a bug, see `AGENTS.md` §19 "Working
   style".
4. Plan the smallest coherent change. Name the files it will touch.
5. Edit only those files, following sibling files for structure, naming, and idiom.
6. Run the narrow validation that covers the change (§6 "Testing and validation").
7. Run the broader gates when the change is cross-cutting (`AGENTS.md` §9 "Testing and
   validation").
8. Read `git diff` before reporting. Unrelated hunks mean something went wrong.
9. Update the document that owns the information you changed (§13 "Documentation
   maintenance").
10. Report using the format in §15 "Completion reporting", including what was not run.

Trivial copy or single-token work does not need steps 2 to 5 in full. It does still need the
lore gate and a diff review.

## 5. Search before creating

Reuse is the default in this repository; a second implementation of something that already
ships is a defect.

- Committed components in `resources/js/components/` before writing markup;
  `resources/views/components/` does not exist, and a Blade view component added there is going
  backwards.
- `lang/en/uma.php` before writing a displayed string or an enum label.
- `config/scenarios.php` before naming a scenario anywhere near the layout path.
- `ScenarioCaps` before doing per-stat ceiling arithmetic.
- The Form Request that already owns a validation boundary (`app/Http/Requests/`).
- `app/Actions` and `app/Services` before adding a class.
- `tests/Fixtures/` bodies and `Http::fake` before writing a pipeline test.
- `docs/research-scratch/` masters before writing any new documentation file.

A new table, column, or class cites a PRD requirement (`FR-x` / `US-x`). No citation, no
merge (`AGENTS.md` §11 "Change-safety" and §4 "Roles and escalation", Architect role).

## 6. Testing and validation

Commands, gates, and which checks a change class needs are in `AGENTS.md` §9 "Testing and
validation" and §10 "Commands". The Claude-specific parts:

- `vendor/bin/pint` and `vendor/bin/phpstan analyse` operate on the **whole working tree**,
  not your diff. `AGENTS.md` §9 "Testing and validation" forbids `--dirty` in this tree
  (KI-75): run `pint --test --format agent <explicit paths>`. In a tree carrying other
  sessions' in-flight edits, check `git status` first, scope both tools to your own paths,
  and say in the report what you scoped out and why.
- Behavior changes ship with a test (`AGENTS.md` §15 "Definition of done"). Copy-only and
  layout-only changes do not need a new one. Never delete or skip a test without owner
  approval.
- Never report a gate as passing unless you ran it and are quoting the output. "Not run" is a
  legitimate line in the report.

## 7. UI and browser workflow

1. `DESIGN.md` for the visual system, `SCREEN_SPEC.md` §4 "Screen Specifications" for the
   screen's states and §8 "Source of Truth" for what owns which answer. Both are required
   before a visible change.
2. Design tokens, component reuse, and the no-`dark:`/no-skeleton rules are `AGENTS.md` §13
   "UI and UX rules" and §7 "Coding conventions"; they bind every visible change.
3. Desktop-first at 1280px+ (`DESIGN.md` §2.3 "Spacing and layout"). Below 768px the race
   calendar and the turn log are wide tables that deliberately scroll horizontally inside a
   focusable region rather than reflow; that is the contract, and the 768px minimum is a
   proposal rather than a ratified one (`DESIGN.md` §2.3, the 2026-09-29 R85 status line).
   Do not "fix" the scroll to be responsive without a decision.
4. Build or serve before looking at a page: `npm run dev` for iteration, `npm run build`
   when a Blade view references assets and the manifest is missing.
5. Verify the rendered result when it is cheap: Boost `browser-logs` plus a `playwright-cli`
   snapshot or screenshot at 1280 and 390 widths, or a spec under `tests/browser/` run
   through `npm run test:browser`. Check the console before claiming the page works.
6. Accessibility behavior here is test-covered by name: keyboard paths, target sizes, the
   review-form labels, and the focusable scroll regions. Keep them.

The `impeccable` design skill is the repo's route for design critique and polish work; load
it for that class of task rather than improvising a review.

## 8. Source of truth

The fuller table is `SCREEN_SPEC.md` §8 "Source of Truth"; these are the rows a Claude
session reaches for most often.

| Question                                    | Authority                                                                                                            |
| ------------------------------------------- | -------------------------------------------------------------------------------------------------------------------- |
| Which screens exist, their URLs and names   | `routes/web.php`, `routes/api.php`                                                                                   |
| What a screen shows and how it got there    | controller methods, then the view                                                                                    |
| Validation and refusals                     | `app/Http/Requests/*`, then `ScenarioCaps` for ceilings                                                              |
| Authorization                               | there is none by design (`ARCHITECTURE.md` §8 "Security Model", `PRD.md` §5 "Non-Functional Requirements" NFR-1)      |
| Schema                                      | `ARCHITECTURE.md` §3 "Database Schema & Eloquent Models" plus the migrations; `ARCHITECTURE-ESSENTIALS.md` is the digest |
| Pipeline stage behavior                     | `AGENTS.md` §8 "Architecture rules an agent must know before editing", `app/Services/DataPipeline/`                   |
| Displayed vocabulary                        | `lang/en/uma.php`, enum labels keyed by case name                                                                    |
| Tokens, contrast, motion                    | `DESIGN.md` and `docs/research-scratch/DESIGN-CORPUS.md`                                                             |
| Automated behavior expectations             | `tests/Feature/*`                                                                                                    |
| Known defects and rulings                   | `KNOWN-ISSUES.md` (live register)                                                                                    |
| Agent operating rules                       | `AGENTS.md`                                                                                                          |
| When sources disagree                       | `AGENTS.md` §2 "Instruction scope and precedence"; record the conflict in `SCREEN_SPEC.md` §7 "Screen-System Gaps & Inconsistencies" or an ADR erratum |

## 9. Protected and sensitive areas

- **Authorization**: there is none, and the rule that guards it is the Boost-inoculation
  paragraph in §1 "Instruction precedence, for Claude specifically". `AGENTS.md` §12
  "Security and privacy" carries the same rule.
- **Secrets**: `.claude/` and the other agent-tool configs are gitignored and some hold live
  API keys on this host. Never copy a key into a source file, a doc, a fixture, a log line,
  or your report (`AGENTS.md` §12 "Security and privacy" lists the tracked-file rule and
  `.env.example`).
- **Lore gate**: advisory. The obligation is that it ran, that the report quotes the hit
  count and its delta from the last recorded figure, and that every hit carries a ruling. `AGENTS.md` §9
  "Testing and validation" carries the mechanism and the last recorded figure. Do not type the banned
  character vocabulary into any artifact (`AGENTS.md` §5 "Non-negotiables" owns the rules and
  points at the bar's C-4 section, where the banned vocabulary is defined, and §15
  "Definition of done" requires the gate to have run).
- **The dev SQLite file** holds real Trainer data. It is untracked, not disposable
  (`AGENTS.md` §10 "Commands", Destructive; §9 "Testing and validation").

## 10. Generated and tool-owned files

`AGENTS.md` §11 "Change-safety" owns the protected list, the regeneration notes, and the
lockfile rule. The generated artifact worth naming here is `PRODUCT.md` (impeccable
plugin output): regenerate, never hand-edit.

## 11. Large changes

- Understand the affected architecture first, then split the work so each step is
  independently verifiable. One concern per commit series (`AGENTS.md` §14 "Git and change
  management").
- Run the gates at each meaningful milestone rather than once at the end, so a break is
  cheap to localize.
- Slice plans and records live in `docs/research-scratch/PROCESS-PLANS.md` and
  `docs/research-scratch/SLICE-RECORDS.md`; new slices do not go in the repository root (`AGENTS.md` §3
  "Documentation map").
- Do not silently expand scope. A feature that needs a `PRD.md` §6 "Non-Goals (eliminated
  legacy features)" item lifted, or a new dependency, is an escalation (`AGENTS.md` §4
  "Roles and escalation" and §5 "Non-negotiables"), not a decision to make in passing.
- Review the aggregate diff, not only the last file you edited.

## 12. Ambiguity and unknowns

Do not invent a requirement. Read the owning document, inspect the code and its tests, and
prefer the smallest behavior-preserving change the evidence supports (`AGENTS.md` §16
"Handling ambiguity" carries the same order). When a decision would change product
behavior, security, data integrity, or a public contract and cannot be safely inferred,
stop and surface it instead of guessing. Mark genuinely undecided things as "Undecided,
owner input needed" rather than naming them. The product name is settled: **Trainer Desk**.

## 13. Documentation maintenance

Which document owns what is `AGENTS.md` §3 "Documentation map"; the dated-erratum rule, the
four-part defect form, and the derived ADR index are `AGENTS.md` §11 "Change-safety". This
file takes content only for Claude-specific practice.

**Personal preferences**: Do not add a `CLAUDE.local.md` to this repository; personal,
unshared preferences belong in `~/.claude/CLAUDE.md` (user-level, not tracked).

**Durable learnings** go into `.ai/rules/` files, the `docs/research-scratch/` masters, and
ADRs, not into Claude's own memory layer (`~/.claude/projects/<project>/memory/`), which is
on by default and is disabled by setting `"autoMemoryEnabled": false` in
`.claude/settings.local.json`. Because `.gitignore` ignores `/.claude` wholesale, no Claude
setting in this repository is shared with another clone: every setting file is per-host, so
this paragraph is the only repo-wide mechanism for the memory layer it describes.

## 14. Git and diff hygiene

- `git status` before you start and `git diff` before you report. This tree frequently holds
  uncommitted work from other sessions: do not revert it, do not reformat it, do not commit
  it. Scope your edits and your tools to your own files.
- There is no CI, so the diff is the review. `CHANGELOG.md` and `VERSION.md` are tracked
  files: `CHANGELOG.md` is updated by the Docs Writer on significant landings
  (`AGENTS.md` §3 "Documentation map", §14 "Git and change management").

## 15. Completion reporting

Report in this shape, and say plainly what you did not verify:

```text
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

This file was rewritten as Claude-specific guidance on top of `AGENTS.md`, then revised to
point at the owning document instead of restating it. Three false claims it once carried
are corrected and guarded here: that `CLAUDE.md` is gitignored (it is tracked); that the
`impeccable` skill lives at repo-level `.agents/skills/impeccable` (it does not; it is at
`~/.agents/skills/impeccable` and `.claude/skills/impeccable`); and that the repository has
a deployment path (it does not; deployment is a non-goal, `PRD.md` §6.10). The date-by-date
history is `git log` on this file.
