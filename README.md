<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Skill Automation

This project includes an **automated skill discovery and invocation system** that automatically runs Laravel development tasks for you.

### How It Works

When you ask for any development task:
1. **The agent automatically discovers matching skills** from `.agents/skills.json`
2. **Shows you which skills will be used** (with relevance scores)
3. **Executes them automatically** via `php artisan skill:manage execute`
4. **Reports results** showing what was created/changed

### Example

```bash
You: "Create a User model with email and password fields"

Agent:
📋 Matched Skills:
- model_create (0.98) — Scaffolds Eloquent models

🔧 Executing: Model Creator
   Running: php artisan make:model User

✅ Complete: User model created
```

### Skill System Files

- `.agentrules` — Auto-loaded rules that activate skill automation
- `.agents/` — Skill system configuration and registry
  - `config.json` — Automation settings
  - `skills.json` — 24 available Laravel skills
  - `README.md` — Skill documentation
- `app/Services/` — Skill execution engine (SkillRegistry, SkillMatcher, SkillExecutor)
- `app/Console/Commands/ManageSkills.php` — Manual skill management commands

### Disable Automation (If Needed)

```bash
# Manually invoke a specific skill
php artisan skill:manage execute "your task description"

# List available skills
php artisan skill:manage list

# Find skills matching a keyword
php artisan skill:manage find "migration"

# Get info about a skill
php artisan skill:manage info migration_create
```

### How Agents Know to Use Skills

The system uses `.agentrules` (loaded by compatible agents on startup):
- **GitHub Copilot** — Reads `.copilot/instructions.md`
- **Codeium Windsurf** — Reads `.windsurf/rules.json`
- **Claude** — Uses custom instructions
- **Any LLM agent** — Can read and follow `.agentrules`

No additional setup needed - skills auto-activate!

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Quick Start (Sail)

The canonical development environment is [Laravel Sail](https://laravel.com/docs/sail). All commands below use `./vendor/bin/sail` — never mix `php artisan` with `sail artisan`.

```bash
# 1. Start the stack (mysql, redis, mailpit, app server)
./vendor/bin/sail up -d

# 2. Install dependencies (first time only)
composer install
npm install

# 3. Generate the app key and run migrations
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate

# 4. Start the Vite dev server (HMR) in a separate terminal
./vendor/bin/sail npm run dev
```

The application is available at `http://localhost`. Mailpit inbox: `http://localhost:8025`.

### Common Commands

| Task | Command |
|---|---|
| Run tests | `./vendor/bin/sail test` |
| Lint code style | `./vendor/bin/sail composer lint` |
| Fix code style | `./vendor/bin/sail composer fix` |
| Static analysis | `./vendor/bin/sail composer analyse` |
| Tinker REPL | `./vendor/bin/sail tinker` |
| Stop the stack | `./vendor/bin/sail stop` |

### Local Development Without Docker

If Docker is unavailable, the project falls back to an in-memory SQLite database. Copy `.env.example` to `.env`, set `DB_CONNECTION=sqlite`, and run `php artisan migrate`.

## Architecture

- **PHP 8.4+** with Laravel 13
- **Vite 7** + **Tailwind CSS v4** + TypeScript (`resources/js/*.ts`)
- **Pest v4** for testing (level 6 Larastan static analysis)
- **Laravel Pint** for PSR-12 code style
- **Sail** with MySQL 8.4, Redis, and Mailpit

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
