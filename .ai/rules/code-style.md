# Code Style Rules

These rules record the formatting and style conventions this project commits to. They are enforced by **Laravel Pint** (`vendor/bin/pint`).

## Formatting Standards

- **Formatter:** Laravel Pint with the default `Laravel` preset (PSR-12 based).
- Run `vendor/bin/pint --dirty --format agent` after every PHP file change before finalizing.
- Indentation: **4 spaces**. No tabs.
- Line ending: **LF** (see `.editorconfig`).
- Files must end with a single newline; no trailing whitespace (except Markdown files).

## Quoting & Arrays

- Use **single quotes** for PHP string literals unless the string contains a single quote or requires interpolation.
- Use **double quotes** for Blade attribute strings and JSON strings.
- In multi-line arrays, use **trailing commas**:

<code-snippet name="trailing-comma" lang="php">
$attributes = [
```text
'name' => $name,
'email' => $email,
'password' => $password,
```
];
</code-snippet>

## PHPDoc & Comments

- Prefer PHPDoc blocks over inline comments.
- Only add inline comments for exceptionally complex logic.
- Use `@return array{ id: int, name: string }` shape syntax in PHPDoc.
- Mark `@use HasFactory<UserFactory>` on models that use the HasFactory trait.

## Type Hints & Return Types

- Always declare explicit type hints for all method parameters.
- Always declare explicit return types.
- Use constructor property promotion: `public function __construct(public GitHub $github) { }`.
- Never leave empty zero-parameter `__construct()` methods unless the constructor is private.

## Control Structures

- Always use curly braces for control structures, even for single-line bodies:

<code-snippet name="if-braces" lang="php">
if ($condition) {
    doSomething();
} else {
    doSomethingElse();
}
</code-snippet>

## Enum Keys

- Use **TitleCase** for enum keys: `FavoritePerson`, `BestLake`, `Monthly`.

## Blade Templates

- Use **snake_case** for local Blade variables.
- Use **kebab-case** for Blade component names (`<x-card />`).
- Prefer `@php` blocks over inline PHP in templates, but keep them short.
- Escape output with `{{ }}` (Blade escapes via `e()`); use `{!! !!}` only when explicitly rendering trusted HTML.

## Migrations

- Migrations are **anonymous classes** (Laravel 13 default).
- Use `$table->foreignId()` for foreign keys; add `->index()` explicitly when needed.
- Always provide both `up()` and `down()` methods with explicit `: void` return types.
