---
name: creating-models
description: "Trigger when the user asks to create a new Eloquent model, migration, factory, or seeder. Covers model scaffolding, mass-assignment attributes, casts, relationships, and factory definitions."
disable-model-invocation: false
license: MIT
metadata:
  author: laravel
  domain: backend
---

# Creating Models

Scaffold a new Eloquent model with its migration, factory, and seeder following this project's conventions.

## Trigger Criteria

User requests one or more of: "create a model", "make a migration", "add a model", "scaffold a model", "create a new Eloquent class".

## Inputs

- **Model name** (PascalCase, singular): e.g., `Invoice`, `Product`.
- **Fields**: column names with optional types (`string`, `integer`, `boolean`, `foreignId`, `text`, `timestamp`).

## Workflow

### 1. Create the Migration

Run `php artisan make:migration create_{table}_table --no-interaction`. Use an anonymous migration class. Define columns inside `Schema::create()`.

<code-snippet name="migration-example" lang="php">
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->string('number')->unique();
            $table->decimal('total', 10, 2);
            $table->boolean('paid')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
</code-snippet>

### 2. Create the Model

Run `php artisan make:model {Model} --no-interaction`. Apply these conventions:

- Use `#[Fillable([...])]` and `#[Hidden([...])]` attributes.
- Use the `HasFactory` trait.
- Define `casts()` as a method returning an array.
- Reference the factory with `@use HasFactory<{Model}Factory>`.

<code-snippet name="model-example" lang="php">
namespace App\Models;

use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'number', 'total', 'paid'])]
#[Hidden(['created_at', 'updated_at'])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'paid' => 'boolean',
        ];
    }
}
</code-snippet>

### 3. Create the Factory

Run `php artisan make:factory {Model}Factory --no-interaction`. Define the `definition()` method returning fake attributes.

<code-snippet name="factory-example" lang="php">
namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'number' => $this->faker->unique()->numerify('INV-#####'),
            'total' => $this->faker->randomFloat(2, 10, 1000),
            'paid' => $this->faker->boolean(),
        ];
    }
}
</code-snippet>

### 4. Create the Seeder (if requested)

Run `php artisan make:seeder {Model}Seeder --no-interaction`. Use `Model::factory()->count(N)->create()`.

## Validation Rules

- Apply validation in a **Form Request class** at `app/Http/Requests/`, not inline in the controller.
- Foreign keys must be validated against the parent model's existence.

## Emitted Events

- Do not dispatch events from the model itself. Dispatch from the calling Action class or controller.
- If the model should trigger events, register a model observer at `app/Providers/EventServiceProvider.php`.

## Expected Outputs

- `database/migrations/{timestamp}_create_{table}_table.php`
- `app/Models/{Model}.php`
- `database/factories/{Model}Factory.php`
- `database/seeders/{Model}Seeder.php` (only if requested)

## Related Skills

- `running-tests` — after scaffolding, add feature tests for the new model.
- `infer-conventions` — if unsure about the app's existing model patterns, run this skill first.
