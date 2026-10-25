---
name: trainer-desk-skills
description: Index of the agent skills available to Trainer Desk, covering repository-local skills and user-level global skills with one row per name. Use when starting work in this repository, choosing a skill for a task, or checking where a skill is installed; a local copy wins over a global duplicate.
---

# Trainer Desk agent skills

This repository keeps its own agent skills, and the machine also carries user-level global
skills. This file indexes both, one row per skill name. Where a name exists in both places, the
local copy is the one that applies here and the global install is marked in the local table.

## Scope

- Local Qoder project scope: `.agents/skills/<name>/SKILL.md`, 30 skills. The registry scanner
  reported 30 directories, 0 errors and 0 warnings on 2026-10-26.
- Eight further skills sit directly at `.agents/<name>/SKILL.md`, one is nested at
  `.agents/jev-ultrafast-mcp/skills/jev-ultrafast-mcp/SKILL.md`, and `test-driven-development`
  exists at two local paths. The scanner counts only `.agents/skills`, so its 30 is not the
  whole local set.
- Global stores: `~/.qoder/skills` (Qoder user scope), `~/.agents/skills` (host-readable user
  store), and `~/.claude/skills` (Claude Code user scope). Availability differs by host; the
  path names the store that holds the copy.
- Plugin skills (the `plugin:`-prefixed entries in the session skill list) come from the Qoder
  plugin registry, not from these stores, and are not indexed here.
- `.agents/` is gitignored (`.gitignore:50`), so the local set is local to this checkout and is
  not committed. That is deliberate: the repository contract stays in tracked files, the
  tooling stays disposable.
- Every other dot-directory (`.claude/`, `.cursor/`, `.ai/`, `.github/`, `.gemini/`, and the
  rest) holds copies of the local set for other agent hosts, and `.kilo/worktrees/` holds git
  worktrees. They are mirrors, not extra skills.

Roster measured on 2026-10-26: 179 unique names, 38 local, 15 local copies that also exist
globally, 23 local-only, 141 global-only.

`AGENTS.md` outranks every skill below. Where a skill disagrees with the repository contract,
`AGENTS.md` wins. `deploying-to-cloud` is the standing example: this tool is local-only and has
no cloud path, so its Laravel Cloud guidance does not apply here.

## Repository domain

| Skill | Use when | Also global | Path |
| --- | --- | --- | --- |
| `creating-models` | Creating an Eloquent model, migration, factory, or seeder |  | `.agents/skills/creating-models/SKILL.md` |
| `fetch-pipeline` | Adding, changing, or debugging a fetch-engine source, parser, snapshot, or promote rule |  | `.agents/skills/fetch-pipeline/SKILL.md` |
| `inertia-vue-development` | Building Vue pages, forms, or navigation on Inertia v2 |  | `.agents/skills/inertia-vue-development/SKILL.md` |
| `laravel-best-practices` | Writing or reviewing Laravel PHP: controllers, models, requests, jobs, queries |  | `.agents/skills/laravel-best-practices/SKILL.md` |
| `pest-testing` | Writing, running, or refactoring Pest 4 tests |  | `.agents/skills/pest-testing/SKILL.md` |
| `running-tests` | Running tests, filtering specs, or debugging a failing run |  | `.agents/skills/running-tests/SKILL.md` |
| `testing-best-practices` | Choosing coverage, assertions, test data, or isolating dependencies |  | `.agents/skills/testing-best-practices/SKILL.md` |
| `tailwindcss-development` | Styling with Tailwind or building responsive grids |  | `.agents/skills/tailwindcss-development/SKILL.md` |
| `infer-conventions` | Reading how this Laravel app is actually written and recording its conventions |  | `.agents/skills/infer-conventions/SKILL.md` |
| `deploying-to-cloud` | Deploying to Laravel Cloud. Contradicts the local-only rule; do not use for repo work |  | `.agents/skills/deploying-to-cloud/SKILL.md` |

## Delivery workflow

| Skill | Use when | Also global | Path |
| --- | --- | --- | --- |
| `incremental-implementation` | Delivering a change in thin, verifiable slices | yes | `.agents/skills/incremental-implementation/SKILL.md` |
| `planning-and-task-breakdown` | Breaking requirements into ordered tasks | yes | `.agents/skills/planning-and-task-breakdown/SKILL.md` |
| `spec-driven-development` | Writing a spec before a new feature or significant change | yes | `.agents/skills/spec-driven-development/SKILL.md` |
| `test-driven-development` | Driving a bug fix or behavior change with a red-green-refactor loop | yes | `.agents/skills/test-driven-development/SKILL.md` |
| `verification-before-completion` | Proving a change before claiming it done |  | `.agents/skills/verification-before-completion/SKILL.md` |
| `using-agent-skills` | Deciding which skill applies at the start of a session | yes | `.agents/skills/using-agent-skills/SKILL.md` |
| `review` | Pre-landing review of a diff against the base branch | yes | `.agents/skills/review/SKILL.md` |
| `code-review-and-quality` | Final self-review of the diff before hand-off | yes | `.agents/code-review-and-quality/SKILL.md` |
| `doubt-driven-development` | Cross-examining a non-trivial decision in a fresh context | yes | `.agents/doubt-driven-development/SKILL.md` |
| `retro` | Reviewing commit history and work patterns | yes | `.agents/skills/retro/SKILL.md` |

## Design and browser

| Skill | Use when | Also global | Path |
| --- | --- | --- | --- |
| `design-review` | QA of visual inconsistency, hierarchy problems, and slow interactions | yes | `.agents/skills/design-review/SKILL.md` |
| `design-consultation` | Proposing a design system for a product | yes | `.agents/design-consultation/SKILL.md` |
| `awesome-design` | Matching a page to a named brand's DESIGN.md |  | `.agents/skills/awesome-design/SKILL.md` |
| `browser-testing-with-devtools` | Checking console errors, focus order, 320px reflow, 200% zoom, reduced motion | yes | `.agents/browser-testing-with-devtools/SKILL.md` |

## Code hygiene

| Skill | Use when | Also global | Path |
| --- | --- | --- | --- |
| `antislop` | Loading the baseline filter against generic AI output | yes | `.agents/antislop/SKILL.md` |
| `antislop-code` | Cleaning comments in source files | yes | `.agents/antislop-code/SKILL.md` |
| `antislop-ui` | Building or editing an interface | yes | `.agents/antislop-ui/SKILL.md` |

## External model and API skills

| Skill | Use when | Also global | Path |
| --- | --- | --- | --- |
| `9router` | Entry point for the 9Router gateway |  | `.agents/skills/9router/SKILL.md` |
| `9router-chat` | Chat or code generation through 9Router |  | `.agents/skills/9router-chat/SKILL.md` |
| `9router-embeddings` | Vector embeddings through 9Router |  | `.agents/skills/9router-embeddings/SKILL.md` |
| `9router-image` | Image generation through 9Router |  | `.agents/skills/9router-image/SKILL.md` |
| `9router-stt` | Speech-to-text through 9Router |  | `.agents/skills/9router-stt/SKILL.md` |
| `9router-tts` | Text-to-speech through 9Router |  | `.agents/skills/9router-tts/SKILL.md` |
| `9router-web-fetch` | Fetching a URL as markdown or text through 9Router |  | `.agents/skills/9router-web-fetch/SKILL.md` |
| `9router-web-search` | Web or X search through 9Router |  | `.agents/skills/9router-web-search/SKILL.md` |
| `typesafe-ai` | Building features on TypeSafe's typed model calls |  | `.agents/skills/typesafe-ai/SKILL.md` |
| `jev-eval` | Running a low-cost typed evaluation on a snippet or diff |  | `.agents/skills/jev-eval/SKILL.md` |
| `jev-ultrafast-mcp` | Handing a browser task to the jev-ultrafast-mcp server |  | `.agents/jev-ultrafast-mcp/skills/jev-ultrafast-mcp/SKILL.md` |

## Global only

These 141 names exist only in a user-level store. Open the file for its full
trigger; the path names the store.

| Skill | Path |
| --- | --- |
| `_gstack-command` | `~/.claude/skills/_gstack-command/SKILL.md` |
| `animate` | `~/.agents/skills/animate/SKILL.md` |
| `animate-expo` | `~/.agents/skills/animate-expo/SKILL.md` |
| `animation-vocabulary` | `~/.agents/skills/animation-vocabulary/SKILL.md` |
| `anti-slop` | `~/.qoder/skills/anti-slop/SKILL.md` |
| `anti-slop-design` | `~/.qoder/skills/anti-slop-design/SKILL.md` |
| `antislop-copywriting` | `~/.agents/skills/antislop-copywriting/SKILL.md` |
| `antislop-layoutmobile` | `~/.agents/skills/antislop-layoutmobile/SKILL.md` |
| `api-and-interface-design` | `~/.qoder/skills/api-and-interface-design/SKILL.md` |
| `apple-design` | `~/.agents/skills/apple-design/SKILL.md` |
| `ask-sonner` | `~/.agents/skills/ask-sonner/SKILL.md` |
| `autoplan` | `~/.claude/skills/autoplan/SKILL.md` |
| `banner-design` | `~/.agents/skills/banner-design/SKILL.md` |
| `benchmark` | `~/.claude/skills/benchmark/SKILL.md` |
| `benchmark-models` | `~/.claude/skills/benchmark-models/SKILL.md` |
| `brand` | `~/.agents/skills/brand/SKILL.md` |
| `brandkit` | `~/.agents/skills/brandkit/SKILL.md` |
| `browse` | `~/.claude/skills/browse/SKILL.md` |
| `canary` | `~/.claude/skills/canary/SKILL.md` |
| `careful` | `~/.claude/skills/careful/SKILL.md` |
| `cavecrew` | `~/.agents/skills/cavecrew/SKILL.md` |
| `caveman` | `~/.agents/skills/caveman/SKILL.md` |
| `caveman-commit` | `~/.agents/skills/caveman-commit/SKILL.md` |
| `caveman-compress` | `~/.agents/skills/caveman-compress/SKILL.md` |
| `caveman-discover` | `~/.agents/skills/caveman-discover/SKILL.md` |
| `caveman-evidence-review` | `~/.agents/skills/caveman-evidence-review/SKILL.md` |
| `caveman-explore` | `~/.agents/skills/caveman-explore/SKILL.md` |
| `caveman-help` | `~/.agents/skills/caveman-help/SKILL.md` |
| `caveman-learn` | `~/.agents/skills/caveman-learn/SKILL.md` |
| `caveman-manage` | `~/.agents/skills/caveman-manage/SKILL.md` |
| `caveman-optimize` | `~/.agents/skills/caveman-optimize/SKILL.md` |
| `caveman-review` | `~/.agents/skills/caveman-review/SKILL.md` |
| `caveman-setup` | `~/.agents/skills/caveman-setup/SKILL.md` |
| `caveman-stats` | `~/.agents/skills/caveman-stats/SKILL.md` |
| `ci-cd-and-automation` | `~/.qoder/skills/ci-cd-and-automation/SKILL.md` |
| `code-simplification` | `~/.qoder/skills/code-simplification/SKILL.md` |
| `codex` | `~/.claude/skills/codex/SKILL.md` |
| `connect-chrome` | `~/.claude/skills/connect-chrome/SKILL.md` |
| `constraint-driven-development` | `~/.qoder/skills/constraint-driven-development/SKILL.md` |
| `context-engineering` | `~/.qoder/skills/context-engineering/SKILL.md` |
| `context-restore` | `~/.claude/skills/context-restore/SKILL.md` |
| `context-save` | `~/.claude/skills/context-save/SKILL.md` |
| `create-plan` | `~/.qoder/skills/create-plan/SKILL.md` |
| `cso` | `~/.claude/skills/cso/SKILL.md` |
| `debugging-and-error-recovery` | `~/.qoder/skills/debugging-and-error-recovery/SKILL.md` |
| `deprecation-and-migration` | `~/.qoder/skills/deprecation-and-migration/SKILL.md` |
| `design` | `~/.agents/skills/design/SKILL.md` |
| `design-html` | `~/.claude/skills/design-html/SKILL.md` |
| `design-shotgun` | `~/.claude/skills/design-shotgun/SKILL.md` |
| `design-system` | `~/.agents/skills/design-system/SKILL.md` |
| `design-taste-frontend` | `~/.agents/skills/design-taste-frontend/SKILL.md` |
| `design-taste-frontend-v1` | `~/.agents/skills/design-taste-frontend-v1/SKILL.md` |
| `deslop-shared-libs` | `~/.claude/skills/deslop-shared-libs/SKILL.md` |
| `devex-review` | `~/.claude/skills/devex-review/SKILL.md` |
| `diagram` | `~/.claude/skills/diagram/SKILL.md` |
| `document-generate` | `~/.claude/skills/document-generate/SKILL.md` |
| `document-release` | `~/.claude/skills/document-release/SKILL.md` |
| `documentation-and-adrs` | `~/.qoder/skills/documentation-and-adrs/SKILL.md` |
| `emil-design-eng` | `~/.agents/skills/emil-design-eng/SKILL.md` |
| `find-animation-opportunities` | `~/.agents/skills/find-animation-opportunities/SKILL.md` |
| `freeze` | `~/.claude/skills/freeze/SKILL.md` |
| `frontend-design` | `~/.qoder/skills/frontend-design/SKILL.md` |
| `frontend-development` | `~/.qoder/skills/frontend-development/SKILL.md` |
| `frontend-ui-engineering` | `~/.qoder/skills/frontend-ui-engineering/SKILL.md` |
| `full-output-enforcement` | `~/.agents/skills/full-output-enforcement/SKILL.md` |
| `fullstack-dev` | `~/.qoder/skills/fullstack-dev/SKILL.md` |
| `git-workflow-and-versioning` | `~/.qoder/skills/git-workflow-and-versioning/SKILL.md` |
| `gpt-taste` | `~/.agents/skills/gpt-taste/SKILL.md` |
| `gstack` | `~/.claude/skills/gstack/SKILL.md` |
| `gstack-upgrade` | `~/.claude/skills/gstack-upgrade/SKILL.md` |
| `guard` | `~/.claude/skills/guard/SKILL.md` |
| `health` | `~/.claude/skills/health/SKILL.md` |
| `high-end-visual-design` | `~/.agents/skills/high-end-visual-design/SKILL.md` |
| `i-have-adhd` | `~/.agents/skills/i-have-adhd/SKILL.md` |
| `idea-refine` | `~/.qoder/skills/idea-refine/SKILL.md` |
| `image-to-code` | `~/.agents/skills/image-to-code/SKILL.md` |
| `imagegen-frontend-mobile` | `~/.agents/skills/imagegen-frontend-mobile/SKILL.md` |
| `imagegen-frontend-web` | `~/.agents/skills/imagegen-frontend-web/SKILL.md` |
| `impeccable` | `~/.agents/skills/impeccable/SKILL.md` |
| `improve-animations` | `~/.agents/skills/improve-animations/SKILL.md` |
| `industrial-brutalist-ui` | `~/.agents/skills/industrial-brutalist-ui/SKILL.md` |
| `interview-me` | `~/.qoder/skills/interview-me/SKILL.md` |
| `investigate` | `~/.claude/skills/investigate/SKILL.md` |
| `investigate-first` | `~/.agents/skills/investigate-first/SKILL.md` |
| `ios-clean` | `~/.claude/skills/ios-clean/SKILL.md` |
| `ios-design-review` | `~/.claude/skills/ios-design-review/SKILL.md` |
| `ios-fix` | `~/.claude/skills/ios-fix/SKILL.md` |
| `ios-qa` | `~/.claude/skills/ios-qa/SKILL.md` |
| `ios-sync` | `~/.claude/skills/ios-sync/SKILL.md` |
| `land-and-deploy` | `~/.claude/skills/land-and-deploy/SKILL.md` |
| `landing-report` | `~/.claude/skills/landing-report/SKILL.md` |
| `lean-build` | `~/.agents/skills/lean-build/SKILL.md` |
| `learn` | `~/.claude/skills/learn/SKILL.md` |
| `make-pdf` | `~/.claude/skills/make-pdf/SKILL.md` |
| `migration` | `~/.agents/skills/migration/SKILL.md` |
| `minimalist-ui` | `~/.agents/skills/minimalist-ui/SKILL.md` |
| `mobile-native` | `~/.agents/skills/mobile-native/SKILL.md` |
| `multi-agent-workflow` | `~/.qoder/skills/multi-agent-workflow/SKILL.md` |
| `observability-and-instrumentation` | `~/.qoder/skills/observability-and-instrumentation/SKILL.md` |
| `office-hours` | `~/.claude/skills/office-hours/SKILL.md` |
| `open-gstack-browser` | `~/.claude/skills/open-gstack-browser/SKILL.md` |
| `pair-agent` | `~/.claude/skills/pair-agent/SKILL.md` |
| `performance-optimization` | `~/.qoder/skills/performance-optimization/SKILL.md` |
| `pick-ui-library` | `~/.agents/skills/pick-ui-library/SKILL.md` |
| `plan-ceo-review` | `~/.claude/skills/plan-ceo-review/SKILL.md` |
| `plan-design-review` | `~/.claude/skills/plan-design-review/SKILL.md` |
| `plan-devex-review` | `~/.claude/skills/plan-devex-review/SKILL.md` |
| `plan-eng-review` | `~/.claude/skills/plan-eng-review/SKILL.md` |
| `plan-tune` | `~/.claude/skills/plan-tune/SKILL.md` |
| `prototype` | `~/.agents/skills/prototype/SKILL.md` |
| `qa` | `~/.claude/skills/qa/SKILL.md` |
| `qa-only` | `~/.claude/skills/qa-only/SKILL.md` |
| `redesign-existing-projects` | `~/.agents/skills/redesign-existing-projects/SKILL.md` |
| `refresh-skill-registry` | `~/.qoder/skills/refresh-skill-registry/SKILL.md` |
| `review-animations` | `~/.agents/skills/review-animations/SKILL.md` |
| `safe-refactor` | `~/.agents/skills/safe-refactor/SKILL.md` |
| `scrape` | `~/.claude/skills/scrape/SKILL.md` |
| `security-and-hardening` | `~/.qoder/skills/security-and-hardening/SKILL.md` |
| `setup-browser-cookies` | `~/.claude/skills/setup-browser-cookies/SKILL.md` |
| `setup-deploy` | `~/.claude/skills/setup-deploy/SKILL.md` |
| `setup-gbrain` | `~/.claude/skills/setup-gbrain/SKILL.md` |
| `ship` | `~/.claude/skills/ship/SKILL.md` |
| `shipping-and-launch` | `~/.qoder/skills/shipping-and-launch/SKILL.md` |
| `skill-registry` | `~/.qoder/skills/skill-registry/SKILL.md` |
| `skillify` | `~/.claude/skills/skillify/SKILL.md` |
| `slides` | `~/.agents/skills/slides/SKILL.md` |
| `source-driven-development` | `~/.qoder/skills/source-driven-development/SKILL.md` |
| `spec` | `~/.claude/skills/spec/SKILL.md` |
| `stitch-design-taste` | `~/.agents/skills/stitch-design-taste/SKILL.md` |
| `superpowers-brainstorming` | `~/.qoder/skills/superpowers-brainstorming/SKILL.md` |
| `superpowers-overview` | `~/.qoder/skills/superpowers-overview/SKILL.md` |
| `superpowers-writing-plans` | `~/.qoder/skills/superpowers-writing-plans/SKILL.md` |
| `surgical-patch` | `~/.agents/skills/surgical-patch/SKILL.md` |
| `sync-gbrain` | `~/.claude/skills/sync-gbrain/SKILL.md` |
| `test-audit` | `~/.claude/skills/test-audit/SKILL.md` |
| `ui-designer` | `~/.qoder/skills/ui-designer/SKILL.md` |
| `ui-styling` | `~/.agents/skills/ui-styling/SKILL.md` |
| `ui-ux-pro-max` | `~/.agents/skills/ui-ux-pro-max/SKILL.md` |
| `unfreeze` | `~/.claude/skills/unfreeze/SKILL.md` |
| `verify-and-stop` | `~/.agents/skills/verify-and-stop/SKILL.md` |
| `write-swift` | `~/.agents/skills/write-swift/SKILL.md` |

## Keeping this index current

Re-derive the roster instead of trusting the counts above:

```bash
node "$HOME/.qoder/skills/refresh-skill-registry/scripts/scan-skills.cjs" --names --scope project-agents,project-qoder,user-qoder,user-global
ls ~/.agents/skills/*/SKILL.md
```

`~/.agents/skills` sits outside the scanner scopes, so the `ls` line covers that store. A row
without a file is a defect, and a skill missing from these tables is a defect too. Add or remove
both together. `test-driven-development` also exists at `.agents/test-driven-development/SKILL.md`;
keep the two local copies in step.

