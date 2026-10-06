<code-snippet name="strict-types" lang="php">
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{

```text
public function index(): JsonResponse
{
    return response()->json([
        'data' => User::select(['id', 'name', 'email'])->get(),
    ]);
}
```

}
</code-snippet>

## PHP Baseline

- Every PHP file **must** begin with `<?php` followed by `declare(strict_types=1);`.
- Use **PHP 8.4+** features: readonly classes, `#[Override]`, intersection types where applicable, and constructor property promotion.
- All method parameters and return types must be explicitly typed: `function store(Request $request): JsonResponse`.
- Use **TitleCase** for enum cases: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer **PHPDoc** blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array-shape type definitions in PHPDoc blocks, e.g. `@return array{ id: int, name: string }`.

## Controllers & Actions

- Controllers should be thin; delegate business logic to **Action classes** in `app/Actions/` or **Service classes** in `app/Services/`.
- Action classes expose a `handle(...)` method (or `__invoke`) and are invoked from controllers or jobs.
- Use **Form Request classes** in `app/Http/Requests/` for all input validation. Never inline `validate()` in controllers.
- Return JSON responses via `response()->json()` for APIs; use `redirect()->back()` or named routes for web flows.

## Models & Eloquent

- Models live in `app/Models/`. Use the **attributes** (`#[Fillable]`, `#[Hidden]`) over legacy `$fillable`/`$hidden` properties.
- Use the `HasFactory` trait and a factory at `database/factories/{Model}Factory.php`.
- Define `casts()` as a method returning an array — this is the Laravel 13 standard.
- Use **lazy eager loading** (`loadMissing`) to avoid N+1 queries in endpoints.
- API responses must use **Eloquent API Resources** at `app/Http/Resources/` rather than manual `toArray()` mappings.

## Routing

- Use **named routes** and the `route()` helper for all URL generation.
- Group routes by middleware: `api` prefix + `api` middleware for API routes; `web` middleware for browser routes.
- Keep `routes/web.php` and `routes/api.php` thin — delegate to route model bindings and controller methods.

## Configuration & Environment

- Read configuration with `config('app.name')` or `php artisan config:show app.name`. Never hardcode env values.
- Use `env()` only inside configuration files; in application code use `config()` so values are cached.

## Frontend

- Use **Vite** with `@tailwindcss/vite` and **Tailwind CSS v4**. Assets are referenced via `@vite(['resources/css/app.css', 'resources/js/app.js'])`.
- Blade templates live in `resources/views/`. Use the `layout` Blade component for shared wrappers.
- For Tailwind-specific guidance, activate the `tailwindcss-development` skill.
