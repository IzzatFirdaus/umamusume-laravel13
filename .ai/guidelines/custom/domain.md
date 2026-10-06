# Custom Domain Guidelines

This file records the high-level domain terminology, application boundaries, and authorization patterns specific to this repository. It is loaded automatically by agents working on any part of the codebase.

## Application Identity

- **Project name:** Laravel 13 skeleton application.
- **PHP version:** 8.3+ (targeting 8.4+ features where appropriate).
- **Frontend stack:** Vite + Tailwind CSS v4 + vanilla JavaScript (no Livewire, no Inertia, no Flux UI installed).
- **Testing stack:** Pest 4 with `pest-plugin-laravel`.

## Core Domain Boundaries

- `app/Models/` — Eloquent models. Currently contains the `User` model extending `Illuminate\Foundation\Auth\User`.
- `app/Http/` — Controllers, form requests, middleware, and API resources.
- `app/Actions/` — (intended) One-off business operations invoked by controllers or jobs.
- `app/Services/` — (intended) Shared domain services wrapping external integrations or complex logic.
- `app/Console/` — Artisan commands and scheduled tasks.
- `app/Providers/` — Service providers, including the route service provider and any package providers.
- `database/migrations/` — Schema definitions using anonymous migration classes (Laravel 13 default).

## Authorization Patterns

- Use **Laravel policies** at `app/Policies/` for object-level authorization.
- Gate-based checks are acceptable for global, non-model rules.
- Always resolve the `User` model via route model binding or explicit `Auth::user()`; never trust client-supplied identifiers without a policy check.

## API Conventions

- API endpoints return JSON via Eloquent **API Resources** at `app/Http/Resources/`.
- Use **API versioning** for any public-facing endpoints (e.g., `api/v1/`).
- Wrap collections with `ResourceCollection`; wrap single models with the resource class.

## Events & Side Effects

- Dispatch events from **Action classes** or **Service classes**, never from controllers or models directly.
- Use `ShouldQueue` for jobs that interact with external services.
- Listeners should be registered in the `EventServiceProvider` at `app/Providers/EventServiceProvider.php`.

## Data Integrity

- Mass assignment is governed by the `#[Fillable]` attribute on each model.
- Soft deletes are not used by default; if added, ensure the `SoftDeletes` trait and corresponding migration column are added together.
- Use database transactions (`DB::transaction`) when multiple writes must be atomic.
