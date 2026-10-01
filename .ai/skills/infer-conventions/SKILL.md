---
name: infer-conventions
description: "Use this skill to analyze how a Laravel application is actually written and record its conventions as shared rules. Trigger when the user wants to detect, infer, document, or standardize project conventions or coding style, set up or grow `.ai/rules`, resolve mixed or conflicting patterns (e.g. \"are we using Form Requests or inline validation?\"), or onboard agents and teammates to \"how we do things here\"."
disable-model-invocation: true
license: MIT
metadata:
  author: laravel
---

# Infer Conventions

Learn how this application writes Laravel, then record what you learn as durable, path-scoped rules other agents will read. You are documenting reality, not improving it.

## Ground Rules

- Consistency first. The codebase's majority style is the convention. Never judge it, never propose a "better" pattern, never record what the code should do.
- Skip what an active tool produces, keep what a tool would fight. Inspect the project's Pint and PHPStan configuration first.
- Record decisions, not defaults. A consistent pattern earns a rule only when it reflects a choice.
- Architecture choices are the gold. Record presence and deliberate absence.
- Never duplicate `.ai/rules`. Read `.ai/rules/index.md` and the area files before the sweep.
- Evidence or silence. A convention needs at least 3 consistent examples and no meaningful rival to become a candidate.
- The recorded rule states the convention, nothing else. One or two imperative lines.

## Process

### Step 0: Orient

Read `composer.json`, `package.json`, the Pint/PHPStan config, `.ai/rules/index.md` if present, and map the `app/` tree. List every directory under `app/`. Every folder beyond Laravel's default skeleton is a structural pattern the app committed to.

### Step 1: Predefined Sweep

Work every applicable dimension using its search hints. Give each exactly one verdict: Pattern, Conflict, Default, No signal, or Tooling-owned/Already-recorded.

### Step 2: Open-ended Pass

Close out the architecture map. For every non-default `app/` directory, confirm how the pattern is used. Make genuine structural patterns candidates.

### Step 3: Confirm

Present every candidate in one batch. Per item: dimension, verdict, evidence, and the exact proposed `glob` / `title` / `note`.

### Step 4: Record

Make one `record-rule` call for each glob an approved convention applies to. The `note` is the bare convention: strip every trace of detection.

### Step 5: Summarize

List recorded rules, conflicts the user deferred, notable no-signals.

## Glob Mapping

- Models: `app/Models/**`
- Controllers, routing, validation: `app/Http/**`
- Actions, Services, DTOs: `app/Actions/**`, `app/Services/**`
- Tests: `tests/**`
- Migrations: `database/migrations/**`
- Truly app-wide: `app/**`
