# Custom Domain Guidelines

This file records the high-level domain terminology, application boundaries, and authorization patterns specific to this repository. It is loaded automatically by agents working on any part of the codebase.

## Application Identity

- **Project name:** Trainer Desk, a local-only Laravel 13 tool (see `AGENTS.md` §1).
- **PHP version:** `composer.json` requires `^8.3`; this host runs PHP 8.5. Confirm the installed version before relying on a version-specific API.
- **Frontend stack:** Inertia + Vue 3 with TypeScript, Tailwind CSS v4 and Vite (`resources/js/spa.ts` is the only JavaScript entry; `resources/views/app.blade.php` is the shell). No Livewire, no Flux UI.
- **Testing stack:** Pest 4 with `pest-plugin-laravel` and `pest-plugin-browser`.

## Core Domain Boundaries

- `app/Models/` — Eloquent models for the catalog, trainer data and support cards. `User.php` and the `users` table are unused framework defaults (`AGENTS.md` §17).
- `app/Http/` — Controllers, form requests, middleware, and API resources.
- `app/Actions/` — One-off business operations invoked by controllers or commands (`PromoteMatchedRecord`, `ImportHistoricalRun`, `Store*`).
- `app/Services/` — Shared domain services such as the data pipeline, `ScenarioCaps` and `SupportCardEffects`.
- `app/Console/` — Artisan commands and scheduled tasks.
- `app/Providers/` — Service providers, including the route service provider and any package providers.
- `database/migrations/` — Schema definitions using anonymous migration classes (Laravel 13 default).

## Authorization Patterns

- There is **no authorization layer**, and that is the design (`PRD.md` NFR-1, `ARCHITECTURE.md` §8). The app is loopback-only, single-Trainer.
- Do not add policies, gates or an `Auth::user()` flow unless the work explicitly asks for one (`AGENTS.md` §12).

## API Conventions

- API endpoints return JSON via Eloquent **API Resources** at `app/Http/Resources/`.
- Use **API versioning** for any public-facing endpoints (e.g., `api/v1/`).
- Wrap collections with `ResourceCollection`; wrap single models with the resource class.

## Events & Side Effects

- Dispatch events from **Action classes** or **Service classes**, never from controllers or models directly.
- Use `ShouldQueue` for jobs that interact with external services.
- Register listeners in a service provider under `app/Providers/`. This tree has `AppServiceProvider` and `DatabaseGuardServiceProvider`; there is no `EventServiceProvider`.

## Data Integrity

- Mass assignment is governed by the `#[Fillable]` attribute on each model.
- Soft deletes are not used by default; if added, ensure the `SoftDeletes` trait and corresponding migration column are added together.
- Use database transactions (`DB::transaction`) when multiple writes must be atomic.

## Task Workflow

- Task execution follows `AGENTS.md` §2a: establish the goal, scope, acceptance criteria, ordered TODOs, verification and risks before implementation; maintain the TODOs and verify the result before reporting completion.
