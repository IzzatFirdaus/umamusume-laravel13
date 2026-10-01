---
name: laravel-best-practices
description: "Trigger when writing, reviewing, or refactoring Laravel PHP code. Covers controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Activated for N+1 issues, caching, authorization, validation, queue, routing, and architectural decisions."
disable-model-invocation: false
license: MIT
metadata:
  author: laravel
  domain: backend
---

# Laravel Best Practices

Apply Laravel idioms when writing or reviewing PHP code in this project.

## Trigger Criteria

User requests: "write a controller", "add a job", "create a policy", "refactor this model", "fix N+1", "add validation", "set up caching", "review this code".

## Controllers

- Keep controllers **thin**. Delegate business logic to Action classes (`app/Actions/`) or Service classes (`app/Services/`).
- Use **Form Request classes** for validation — never inline `validate()` in controller methods.
- Use named routes for URL generation.
- Return JSON via `response()->json()` for APIs.

<code-snippet name="controller-example" lang="php">
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => User::select(['id', 'name', 'email'])->get(),
        ]);
    }
}
</code-snippet>

## Models

- Use attributes (`#[Fillable]`, `#[Hidden]`) over legacy properties.
- Define `casts()` as a method.
- Use the `HasFactory` trait with a factory.
- Avoid N+1 with `loadMissing` or `with` for eager loading.

<code-snippet name="model-example" lang="php">
<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
</code-snippet>

## Validation

- Form Request classes in `app/Http/Requests/`.
- Use `Rule::exists()` for database existence checks.
- Return custom error responses for API consumers.

## Queues & Jobs

- Jobs implementing `ShouldQueue` are dispatched via `Bus::dispatch`.
- Use `--tries` and `--timeout` when running `queue:listen`.
- Keep jobs small and single-purpose.

## Caching

- Cache expensive queries with `Cache::remember()` and a sensible TTL.
- Use tags for grouped cache invalidation when using Redis.

## Authorization

- Policies in `app/Policies/` for object-level checks.
- Gates for global, non-model rules.
- Use the `authorize()` helper or `Gate::allows()` in controllers.

## Architecture

- Prefer **Action classes** (`handle` / `execute` / `__invoke`) for one-off operations.
- Prefer **Service classes** for shared domain logic and external integrations.
- Use **API Resources** (`app/Http/Resources/`) for JSON transformations.
- Dispatch events from Actions/Services, never from controllers or models directly.
