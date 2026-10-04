# Image Slot Display Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development`
> (recommended) or `superpowers:executing-plans` to execute this plan task-by-task.
> Steps use checkbox (`- [ ]`) syntax for tracking. **The Progress section at the
> bottom mirrors each task's state and is updated as the slice lands.**

**Goal:** Render id-addressed artwork (trainee portraits, support-card thumbnails)
on the screens that already display the rows that own them, with WCAG 2.2 AA
conformance and the UX laws named in `docs/proposals/frontend-development-plan.md`
§13, while preserving the existing text-only row as the universal fallback.

**Architecture:** The mirror is already built (`uma:fetch-art`,
`App\Services\DataPipeline\ArtworkMirror`). What this plan adds is the display
half: a loopback route at `/artwork/{kind}/{id}` that streams a stored file
from private storage, a slot-helper that returns the URL only when the file is
on disk, and per-screen wire-ups into the existing catalog / support-card /
run-create Blade templates and the Inertia Vue catalog index. Nothing enters
the database. Skill icons stay deferred because `skills` has no `iconid`
column (`ADR-0021` Verification).

**Tech Stack:** Laravel 13, Blade, `php artisan make:*`, Pest 4,
`Http::fake` for tests, `@inertiajs/vue3` for the catalog index page, Tailwind v4
tokens only. Runs on a single SQLite file under loopback. No new package.

**Spec — what the plan implements:**

- `docs/adr/0021-sourced-character-artwork.md` (Decision rows 2 to 6).
- `docs/proposals/design-2.0.md §42 Image slots` + `§45a Sourced image slots`
  (filed 2026-10-05).
- `docs/proposals/screen-spec-2.0.md §34 Image slots` + `§35 Sourced image
  slots` (renumbered; same date).
- `DESIGN.md §4.7 Sourced artwork slots` (binding rules: absence normal, geometry
  decided once, `src` local, alt inside C-4, decorative → `alt=""`).
- `docs/proposals/frontend-development-plan.md §12 WCAG 2.2 AA`, `§13 Laws of UX`,
  `§14 Phase A0`. The plan's per-screen slices are the Blade-side realization of
  Phase A0c.

## Global Constraints

Every slice implicitly carries the rules below from the doctrine, the spec, and
the operational contract.

- **No new package, no dependency update.** `composer audit` and `npm audit --omit=dev`
  clean before the slice lands; relock speeds the rest of the work.
- **No image upload surface.** `PRD §6.13` stays cut. `ADR-0021` is the only path:
  the tool fetches id-addressable art from an allowlisted asset host into private
  storage; nothing in the database; nothing exposed through a public route.
- **Local mirror only, loopback only.** Files live under `storage/app/private/artwork/`
  (gitignored) and are served by a named route that binds to loopback. No
  `storage:link`, no `public/` export.
- **WCAG 2.2 AA on every slot-bearing screen.** The binding rules in
  `design-2.0 §42` and the `frontend-development-plan §12` carry; the four
  per-screen clauses the spec names (alt text, reserved-box contrast, label in
  name, omitted at narrow viewports) are non-negotiable.
- **Lore gate clean.** Every diff line written this turn is in the doc/coded
  corpus. Banned vocabulary is enforced by `composer lore` and
  `composer lore-code`; hits carry an inline ruling.
- **Pint + PHPStan L6 + the affected feature test + the cross-screen doc-gate
  tests** run on every slice that touches behaviour, not prose-only slices.
  Prose-only slices still run `composer lore` and the doc gates.
- **One slice, one commit.** Conventional subject: `feat(slots):`, `test(slots):`,
  `docs(slots):`, `refactor(slots):`. PRs do not exist on this repo; commits
  are de facto PRs.
- **TDD where the slice has logic.** A trivial view markup edit ships with a
  asserting feature test for the rendered `<img>` (or its absence); a
  service or controller change ships with the model/route/unit test that
  proves it. No test asserts the user's prose; tests assert behaviour.

## File Structure Lock-in

Files this plan creates or modifies, with single-responsibility intent.

- Create: `app/Http/Controllers/ArtworkAssetController.php` — one method,
  `show(string $kind, int $id): Response` streams the file or aborts 404.
- Modify: `routes/web.php` — one `Route::get('/artwork/{kind}/{id}', …)` line.
- Modify: `app/Services/DataPipeline/ArtworkMirror.php` — add `exists(string $kind,
  int $id): bool`, `url(string $kind, int $id): ?string`, lift `storedPath()` and
  `disk()` to public so the controller and tests can name them.
- Create: `resources/views/components/character-portrait.blade.php` — `<img>`
  wrapper for trainee portraits.
- Create: `resources/views/components/support-thumb.blade.php` — same shape
  for support-card thumbs.
- Modify: `resources/views/catalog/show.blade.php` — Identity row gets
  `<x-character-portrait>`.
- Modify: `resources/views/support-cards/index.blade.php` — card row gets
  `<x-support-thumb>`.
- Modify: `resources/views/support-cards/show.blade.php` — header gets
  `<x-support-thumb>`.
- Modify: `resources/views/runs/create.blade.php` — Legacy Select row + form row.
- Modify: `resources/js/pages/Catalog/Index.vue` — trainee card header + form row.

Tests:

- Extend: `tests/Feature/ArtworkMirrorTest.php` — two cases for `exists()` and
  two for `url()`.
- Create: `tests/Feature/ArtworkAssetRouteTest.php` — 3 cases (mirrored 200,
  absent 404, unknown `kind` 404).
- Create: `tests/Feature/CatalogDetailPortraitTest.php`, `SupportCardsIndexThumbnailTest.php`,
  `SupportCardsDetailThumbnailTest.php`, `RunsCreateFormPortraitTest.php`,
  `CatalogIndexPortraitTest.php`.

A repo-wide `policies` table migration or a controller-level `authorize()`
call is **out of scope** (escalation 7 territory per `AGENTS.md §4`).

---

## Task 1: `ArtworkMirror::exists` — TDD surface for views

**Files:**
- Modify: `app/Services/DataPipeline/ArtworkMirror.php`
- Modify: `tests/Feature/ArtworkMirrorTest.php`

**Interfaces:**
- Consumes: `Storage::disk('local')->exists($path)`.
- Produces:

```php
public function exists(string $kind, int $id): bool
```

Returns `true` when the file is on disk under the mirror's `ROOT.'/'.$relative`,
`false` otherwise.

- [ ] **Step 1: Write the failing test**

  Append two cases to `tests/Feature/ArtworkMirrorTest.php`:

  ```php
  it('reports a mirrored file as existing', function (): void {
      Storage::disk('local')->put(
          'artwork/characters/portrait/trainee/256/100101.png',
          'BYTES'
      );

      expect(app(\App\Services\DataPipeline\ArtworkMirror::class)
          ->exists('card_portrait', 100101))->toBeTrue();
  });

  it('reports an absent file as not existing', function (): void {
      expect(app(\App\Services\DataPipeline\ArtworkMirror::class)
          ->exists('card_portrait', 100102))->toBeFalse();
  });
  ```

- [ ] **Step 2: Run tests to verify they fail**

  Run: `vendor/bin/pest --compact --filter "reports a mirrored file" tests/Feature/ArtworkMirrorTest.php`
  Expected: FAIL with `Call to undefined method …::exists()`.

- [ ] **Step 3: Write minimum implementation**

  In `app/Services/DataPipeline/ArtworkMirror.php`, directly after the
  `kinds()` method, add:

  ```php
  public function exists(string $kind, int $id): bool
  {
      return Storage::disk(self::DISK)->exists($this->storedPath($this->relativePath($kind, $id)));
  }

  public function storedPath(string $relativePath): string
  {
      return self::ROOT.'/'.$relativePath;
  }

  public function disk(): \Illuminate\Contracts\Filesystem\Filesystem
  {
      return Storage::disk(self::DISK);
  }
  ```

  `storedPath()` is lifted from private to public because the controller and
  the helper both need the absolute path; `disk()` exposes the storage
  filesystem so the controller's `get()` is testable.

- [ ] **Step 4: Run tests to verify they pass**

  Run: `vendor/bin/pest --compact tests/Feature/ArtworkMirrorTest.php`
  Expected: PASS (counts: 8 + 2 = 10 cases).

- [ ] **Step 5: Commit**

  ```bash
  git add app/Services/DataPipeline/ArtworkMirror.php tests/Feature/ArtworkMirrorTest.php
  git commit -m "feat(slots): expose mirrored-file probe on ArtworkMirror"
  ```

## Task 2: Streaming route for `<img src>`

**Files:**
- Create: `app/Http/Controllers/ArtworkAssetController.php`
- Modify: `routes/web.php` (one line)
- Create: `tests/Feature/ArtworkAssetRouteTest.php`

**Interfaces:**
- Consumes: `ArtworkMirror::exists`, `ArtworkMirror::disk()->get($path)`.
- Produces:
  - Route `artwork.show` at `/artwork/{kind}/{id}` where
    `kind ∈ {card_portrait, support_thumb}` (the two paths the config
    declared). Any other `kind` returns 404.
  - 200 with `image/png` body and the raw bytes when mirrored; 404
    otherwise. The controller **never** reads a `kind` outside the allowlist.

- [ ] **Step 1: Write the failing test**

  ```php
  use App\Services\DataPipeline\ArtworkMirror;
  use Illuminate\Support\Facades\Route;
  use Illuminate\Support\Facades\Storage;

  beforeEach(function (): void {
      Storage::fake('local');
      config(['uma.sources.gametora-artwork.delay_ms' => 0]);
  });

  it('streams a mirrored portrait', function (): void {
      Storage::disk('local')->put(
          'artwork/characters/portrait/trainee/256/100101.png',
          'PNG-BYTES'
      );

      $response = $this->get('/artwork/card_portrait/100101');

      $response->assertOk();
      $response->assertHeader('Content-Type', 'image/png');
      expect($response->getContent())->toBe('PNG-BYTES');
  });

  it('returns 404 when the file is not mirrored', function (): void {
      // File not seeded.
      $this->get('/artwork/card_portrait/100102')->assertNotFound();
  });

  it('rejects an unknown kind with 404', function (): void {
      Storage::disk('local')->put(
          'artwork/characters/portrait/trainee/256/100101.png',
          'PNG-BYTES'
      );

      $this->get('/artwork/profile_pose/100101')->assertNotFound();
  });
  ```

- [ ] **Step 2: Run tests to verify they fail**

  Run: `vendor/bin/pest --compact tests/Feature/ArtworkAssetRouteTest.php`
  Expected: FAIL with `Route [artwork.show] not defined`.

- [ ] **Step 3: Minimum implementation**

  Create `app/Http/Controllers/ArtworkAssetController.php`:

  ```php
  declare(strict_types=1);

  namespace App\Http\Controllers;

  use App\Services\DataPipeline\ArtworkMirror;
  use Illuminate\Http\Request;
  use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

  /**
   * Streams an artwork file the mirror has already fetched. Id-addressed, not
   * path-addressed: the controller validates `kind` against the asset host's
   * declared paths and `id` against the integer cast, so a request like
   * `/artwork/../../etc/passwd` is refused before it reaches Storage.
   */
  final class ArtworkAssetController extends Controller
  {
      public function show(Request $request, ArtworkMirror $mirror, string $kind, int $id)
      {
          $allowed = ['card_portrait', 'support_thumb'];

          if (! in_array($kind, $allowed, true) || $id <= 0) {
              throw new NotFoundHttpException();
          }

          if (! $mirror->exists($kind, $id)) {
              throw new NotFoundHttpException();
          }

          $bytes = $mirror->disk()->get(
              $mirror->storedPath($mirror->relativePath($kind, $id))
          );

          return response($bytes, 200, ['Content-Type' => 'image/png']);
      }
  }
  ```

  Append to `routes/web.php` inside the existing web group:

  ```php
  Route::get('/artwork/{kind}/{id}', [ArtworkAssetController::class, 'show'])
      ->whereNumber('id')
      ->name('artwork.show');
  ```

- [ ] **Step 4: Run tests to verify they pass**

  Run: `vendor/bin/pest --compact tests/Feature/ArtworkAssetRouteTest.php`
  Expected: PASS (3 cases).

- [ ] **Step 5: Commit**

  ```bash
  git add app/Http/Controllers/ArtworkAssetController.php app/Services/DataPipeline/ArtworkMirror.php routes/web.php tests/Feature/ArtworkAssetRouteTest.php
  git commit -m "feat(slots): stream mirrored artwork over a loopback route"
  ```

## Task 3: `ArtworkMirror::url()` — name-routed helper for views

**Files:**
- Modify: `app/Services/DataPipeline/ArtworkMirror.php`
- Modify: `tests/Feature/ArtworkMirrorTest.php`

**Produces:**

```php
public function url(string $kind, int $id): ?string
```

Returns `route('artwork.show', ['kind' => $kind, 'id' => $id])` when mirrored;
`null` otherwise.

- [ ] **Step 1: Write the failing test**

  ```php
  it('returns the named-route url when mirrored', function (): void {
      Storage::disk('local')->put(
          'artwork/characters/portrait/trainee/256/100101.png',
          'BYTES'
      );
      // The stub route covers test-only environments; full route is registered
      // in Task 2's commit and by tests/Pest.php's web group binding.
      \Illuminate\Support\Facades\Route::get(
          '/artwork/{kind}/{id}',
          fn () => ''
      )->name('artwork.show');

      expect(app(\App\Services\DataPipeline\ArtworkMirror::class)
          ->url('card_portrait', 100101)
      )->toBe(route('artwork.show', ['kind' => 'card_portrait', 'id' => 100101]));
  });

  it('returns null when the file is not mirrored', function (): void {
      expect(app(\App\Services\DataPipeline\ArtworkMirror::class)
          ->url('card_portrait', 100103))->toBeNull();
  });
  ```

- [ ] **Step 2: Run tests to verify they fail**

  Run: `vendor/bin/pest --compact --filter "returns the named-route url" tests/Feature/ArtworkMirrorTest.php`
  Expected: FAIL with `Call to undefined method …::url()`.

- [ ] **Step 3: Minimum implementation**

  ```php
  public function url(string $kind, int $id): ?string
  {
      return $this->exists($kind, $id)
          ? route('artwork.show', ['kind' => $kind, 'id' => $id])
          : null;
  }
  ```

- [ ] **Step 4: Run tests to verify they pass**

  Run: `vendor/bin/pest --compact tests/Feature/ArtworkMirrorTest.php`
  Expected: PASS (10 + 2 = 12 cases).

- [ ] **Step 5: Commit**

  ```bash
  git add app/Services/DataPipeline/ArtworkMirror.php tests/Feature/ArtworkMirrorTest.php
  git commit -m "feat(slots): expose name-routed url helper on ArtworkMirror"
  ```

## Task 4: `<x-character-portrait>` Blade component

**Files:**
- Create: `resources/views/components/character-portrait.blade.php`
- Create: `tests/Feature/CharacterPortraitComponentTest.php`

- [ ] **Step 1: Failing test**

  ```php
  use Illuminate\Support\Facades\Storage;

  beforeEach(function (): void {
      Storage::fake('local');
      config(['uma.sources.gametora-artwork.delay_ms' => 0]);
      \Illuminate\Support\Facades\Route::get(
          '/artwork/{kind}/{id}',
          fn () => ''
      )->name('artwork.show');
      \Illuminate\Support\Facades\Route::get(
          '/umamusume/{slug}',
          fn () => ''
      )->name('catalog.show');
  });

  it('renders an img when the file is mirrored', function (): void {
      Storage::disk('local')->put(
          'artwork/characters/portrait/trainee/256/100101.png',
          'PNG'
      );

      $rendered = (string) blade(
          '<x-character-portrait :card-id="100101" size-class="size-12" name="Air Groove" :route-args="[\'slug\' => \'air-groove\']" />',
          []
      );

      expect($rendered)->toContain('<img');
      expect($rendered)->toContain(route('artwork.show', ['kind' => 'card_portrait', 'id' => 100101]));
      expect($rendered)->toContain('alt="Air Groove"');
  });

  it('renders nothing when the file is not mirrored', function (): void {
      $rendered = (string) blade(
          '<x-character-portrait :card-id="100101" size-class="size-12" name="Air Groove" />',
          []
      );

      expect(trim($rendered))->toBe('');
  });

  it('uses alt="" when decorative is set', function (): void {
      Storage::disk('local')->put('artwork/characters/portrait/trainee/256/100101.png', 'PNG');

      $rendered = (string) blade(
          '<x-character-portrait :card-id="100101" size-class="size-12" name="Air Groove" decorative />',
          []
      );

      expect($rendered)->toContain('alt=""');
  });
  ```

- [ ] **Step 2: Run tests to verify they fail**

  Run: `vendor/bin/pest --compact tests/Feature/CharacterPortraitComponentTest.php`
  Expected: FAIL with `Component [character-portrait] not found`.

- [ ] **Step 3: Component file**

  ```blade
  @props([
      'cardId' => 0,
      'sizeClass' => 'size-12',
      'name' => '',
      'decorative' => false,
      'routeArgs' => [],
  ])

  @php
      $mirrored = app(\App\Services\DataPipeline\ArtworkMirror::class);
      $url = $mirrored->url('card_portrait', (int) $cardId);
  @endphp

  @if ($url !== null)
      <a href="{{ route('catalog.show', $routeArgs) }}"
         aria-label="{{ $decorative ? '' : $name }}"
         class="block {{ $sizeClass }}">
          <img src="{{ $url }}"
               alt="{{ $decorative ? '' : $name }}"
               class="{{ $sizeClass }} object-cover rounded-md"
               width="64" height="64">
      </a>
  @endif
  ```

- [ ] **Step 4: Run tests to verify they pass**

  Expected: PASS (3 cases).

- [ ] **Step 5: Commit**

  ```bash
  git add resources/views/components/character-portrait.blade.php tests/Feature/CharacterPortraitComponentTest.php
  git commit -m "feat(slots): add <x-character-portrait> with decorative alt handling"
  ```

## Task 5: `<x-support-thumb>` Blade component

**Files:**
- Create: `resources/views/components/support-thumb.blade.php`
- Create: `tests/Feature/SupportThumbComponentTest.php`

Task 5 mirrors Task 4 exactly, swapping `card_portrait`/`cardId` for
`support_thumb`/`supportId` and `catalog.show` for `support-cards.show`.

- [ ] **Steps 1–5**

  Follow the Task 4 workflow verbatim with the substitutions above. Two cases:
  "renders an img when mirrored" and "renders nothing when absent". The
  decorative test is implicit because support cards rarely print their
  title beside the thumb — set `decorative` only when the placeholder calls for it.

- [ ] **Commit subject**

  ```bash
  git commit -m "feat(slots): add <x-support-thumb> with decorative alt handling"
  ```

## Task 6: Catalog detail Identity section lands the slot

**Files:**
- Modify: `resources/views/catalog/show.blade.php` — Identity row.
- Create: `tests/Feature/CatalogDetailPortraitTest.php`

Note `decorative="true"`: the section already prints the trainee's name above
the slot, so the spec's "where the same name is printed beside the image, the
image is decorative and takes `alt=""`" rule fires.

- [ ] **Step 1: Failing test**

  ```php
  it('shows the mirrored portrait when present', function (): void {
      $card = \App\Models\CharacterCard::factory()->create(['card_id' => 100101]);
      Storage::disk('local')->put('artwork/characters/portrait/trainee/256/100101.png', 'PNG');

      $this->get('/umamusume/'.$card->umamusume->slug)
          ->assertOk()
          ->assertSee('<img', false)
          ->assertSee('alt=""', false);
  });

  it('omits the slot when the portrait is not mirrored', function (): void {
      $card = \App\Models\CharacterCard::factory()->create(['card_id' => 100101]);

      $this->get('/umamusume/'.$card->umamusume->slug)
          ->assertOk()
          ->assertDontSee('<img', false);
  });
  ```

- [ ] **Step 2: Run tests to verify they fail**

- [ ] **Step 3: Minimum edit**

  In `catalog/show.blade.php`, find the Identity row that prints the
  trainee name (currently a header element inside section 1). Insert
  immediately above that header:

  ```blade
  <x-character-portrait
      :card-id="$card->card_id"
      size-class="size-16"
      :name="$trainee->name"
      decorative
      :route-args="['slug' => $trainee->slug]"
  />
  ```

- [ ] **Step 4: Run tests to verify they pass**

- [ ] **Step 5: Commit**

  ```bash
  git commit -m "feat(slots): wire portrait into catalog detail Identity"
  ```

## Task 7: Catalog index Vue page lands the slot in trainee card + form rows

**Files:**
- Modify: `resources/js/pages/Catalog/Index.vue`
- Modify: `app/Http/Controllers/CatalogController.php`
- Create: `tests/Feature/CatalogIndexPortraitTest.php`

- [ ] **Step 1: Failing test**

  ```php
  use Illuminate\Support\Facades\Storage;
  use Inertia\Testing\AssertableInertia;

  it('passes portrait urls per trainee row and per form', function (): void {
      $card = \App\Models\CharacterCard::factory()->create(['card_id' => 100101]);
      Storage::disk('local')->put('artwork/characters/portrait/trainee/256/100101.png', 'PNG');

      $this->get('/umamusume')
          ->assertInertia(fn (AssertableInertia $page) =>
              $page->component('Catalog/Index')
                  ->where('rows.0.artworkURL', route('artwork.show', ['kind' => 'card_portrait', 'id' => 100101]))
          );
  });

  it('passes null when the trainee has no mirrored portrait', function (): void {
      \App\Models\CharacterCard::factory()->create(['card_id' => 100101]);

      $this->get('/umamusume')
          ->assertInertia(fn (AssertableInertia $page) =>
              $page->component('Catalog/Index')
                  ->where('rows.0.artworkURL', null)
          );
  });
  ```

- [ ] **Step 2: Run tests to verify they fail**

- [ ] **Step 3: Controller + page minimum**

  In `CatalogController::index()`, after the existing trainees->map, attach
  per-row artwork URLs:

  ```php
  $mirror = app(\App\Services\DataPipeline\ArtworkMirror::class);
  $rows = $trainees->map(function ($trainee) use ($mirror) {
      $topForm = $trainee->cards->sortByDesc('rarity')->first();
      return [
          'id' => $trainee->id,
          'slug' => $trainee->slug,
          'name' => $trainee->name,
          'artworkURL' => $topForm
              ? $mirror->url('card_portrait', (int) $topForm->card_id)
              : null,
          'forms' => $trainee->cards->map(fn ($c) => [
              'id' => $c->id,
              'title' => $c->title,
              'rarity' => $c->rarity,
              'artworkURL' => $mirror->url('card_portrait', (int) $c->card_id),
          ])->all(),
      ];
  })->all();
  ```

  Return `rows` instead of the existing trainees-shaped array (the existing
  shape's keys stay, the new keys are added).

  In `Catalog/Index.vue`, render the slot before each row's name cell and
  before each form's title cell. Existing two-level layout fits the
  `size-12` cell ahead of the header and the `size-10` cell inline with
  the form row.

- [ ] **Step 4: Run tests to verify they pass**

- [ ] **Step 5: Commit**

  ```bash
  git commit -m "feat(slots): wire portraits into catalog index Inertia page"
  ```

## Task 8: Support-card index + detail wire the thumb slot

**Files:**
- Modify: `resources/views/support-cards/index.blade.php`
- Modify: `resources/views/support-cards/show.blade.php`
- Create: `tests/Feature/SupportCardsIndexThumbnailTest.php`
- Create: `tests/Feature/SupportCardsDetailThumbnailTest.php`

Two screens, one component. Both tests use the same shape:

```php
it('renders the thumb on the support-card index', function (): void {
    \App\Models\SupportCard::factory()->create(['support_id' => 10001]);
    Storage::disk('local')->put('artwork/supports/full/small/10001.png', 'PNG');

    $this->get('/support-cards')
        ->assertOk()
        ->assertSee('<img', false)
        ->assertSee(route('artwork.show', ['kind' => 'support_thumb', 'id' => 10001]), false);
});

it('omits the thumb when not mirrored', function (): void {
    \App\Models\SupportCard::factory()->create(['support_id' => 10001]);

    $this->get('/support-cards')->assertOk()->assertDontSee('<img', false);
});
```

Wire the slot in the existing header block of both, alongside the title/rarity:

```blade
<x-support-thumb
    :support-id="$card->support_id"
    size-class="size-12"
    :name="$card->displayName()"
    :route-args="['card' => $card->support_id]"
/>
```

- [ ] **Step 1 + 2 (running failing tests)** for both files with the appropriate route name.
- [ ] **Step 3 (minimum edit)** for both files.
- [ ] **Step 4 (verify all pass)**: `vendor/bin/pest --compact tests/Feature/SupportCardsIndexThumbnailTest.php tests/Feature/SupportCardsDetailThumbnailTest.php`.
- [ ] **Step 5: Commit**

  ```bash
  git commit -m "feat(slots): wire thumbs into support-card index and detail"
  ```

## Task 9: Run Create — Legacy Select and form rows

**Files:**
- Modify: `resources/views/runs/create.blade.php`
- Create: `tests/Feature/RunsCreateFormPortraitTest.php`

- [ ] **Step 1: Failing test**

  ```php
  it('renders the trainee portrait on a Legacy Select row', function (): void {
      $card = \App\Models\CharacterCard::factory()->create(['card_id' => 100101]);
      Storage::disk('local')->put('artwork/characters/portrait/trainee/256/100101.png', 'PNG');
      // Build a session-backed Legacy Select payload; the existing test fixture in
      // tests/Feature/RunCreateSurfaceTest.php shows how.
      // …then run the Legacy Select POST and assert the rendered form carries the slot.

      // (See RunCreateSurfaceTest for the surrounding payload wiring — keep
      //  this test narrow; assert only the rendered `<img>` line.)
  });
  ```

- [ ] **Step 2: Run tests to verify they fail**

- [ ] **Step 3: Minimum edit**

  In the Legacy Select section and the create table:

  ```blade
  <x-character-portrait :card-id="$form->card_id" size-class="size-10" :name="$form->displayName()" :route-args="['slug' => $form->umamusume->slug]" />
  ```

- [ ] **Step 4: Run tests to verify they pass**

- [ ] **Step 5: Commit**

  ```bash
  git commit -m "feat(slots): wire portrait into run create Legacy Select"
  ```

## Task 10: Cross-doc updates + gate evidence

**Files:**
- Modify: `docs/proposals/design-2.0.md` — change log row (the prose is already in §42 + §45a, land a dated row).
- Modify: `docs/proposals/screen-spec-2.0.md` — change log row for §34 footnote + §35.
- Modify: `DESIGN.md §4.7` — status word tone (mirror exists, slots do not; the Blade rendering of one slot is now also part of this file's binding rules by example).
- Modify: `SCREEN_SPEC.md §7-16` — status widens: the mirror exists, the per-screen wire-ups exist, what remains is the skill icon migration question.
- Modify: `AGENTS.md §10 commands` — already has `uma:fetch-art`; nothing changes.
- Modify: `README.md` — already has the command in the table; nothing changes.
- Modify: `PRD.md OQ-6` — narrow: the OQ-6 question is now about which skills get icons, not about whether the mechanism works.

- [ ] **Step 1: Land the change log rows**

  In each doc, find the change-log / open-question list and append a dated
  2026-10-05 row about the image-slot display work.

- [ ] **Step 2: Run the gate suite**

  ```bash
  vendor/bin/pint --dirty --format agent
  vendor/bin/phpstan analyse --no-progress --memory-limit=1G
  composer lore
  composer lore-code
  php -d memory_limit=1G artisan test --compact
  vendor/bin/pest --compact tests/Feature/DocCitationParityTest.php tests/Feature/DocSchemaDriftTest.php tests/Feature/LoreGateParityTest.php tests/Feature/EmptyStateCommandNamesTest.php
  python tools/doc_census.py
  ```

  Expected: `pint` reports `passed`; `phpstan` reports `[OK] No errors`;
  `composer lore` reports docs `238/77` and code `0/1` (or whatever the
  baseline reads at the moment — record the actual numbers); `composer
  lore-code` reports `46` hits with one new ruling (or the reading at the
  moment); the Pest `--compact` summary line is one larger than before this
  plan touched the suite (the new feature tests land); the four doc-gate
  tests are green; `tools/doc_census.py` prints no orphan-markdown findings
  and no `OUTDATED CLAIM` rows under the `## Image slot display` heading.

  Evidence rule (per `AGENTS.md §15`): attach the run output to the slice's
  hand-off report. A gate that was not run is reported as not run, never as
  passed. After the gate block, end-of-slice report goes through the
  format in `## Progress` below.

- [ ] **Step 3: Update the Progress section**

  Move each task's checkbox from `[ ]` to `[x]` in `## Progress` below as the
  slice lands. The plan and the Progress section are the same file so this
  step is one edit per slice; do not split.

- [ ] **Step 4: Commit**

  ```bash
  git add docs/proposals/design-2.0.md \
          docs/proposals/screen-spec-2.0.md \
          docs/superpowers/plans/2026-10-05-image-slot-display.md \
          DESIGN.md \
          SCREEN_SPEC.md \
          PRD.md
  git commit -m "docs(slots): land change-log rows and progress baseline"
  ```

---

## Progress

| Task | Description | Status | Commit | Evidence |
|---|---|---|---|---|
| 1 | `ArtworkMirror::exists(string $kind, int $id): bool` + `storedPath()` / `disk()` lifts | `[ ]` | — | — |
| 2 | Streaming route `/artwork/{kind}/{id}` via `ArtworkAssetController::show`, allowlist on `kind` | `[ ]` | — | — |
| 3 | `ArtworkMirror::url(string $kind, int $id): ?string` | `[ ]` | — | — |
| 4 | `<x-character-portrait>` Blade component (`alt=""` when `decorative`) | `[ ]` | — | — |
| 5 | `<x-support-thumb>` Blade component | `[ ]` | — | — |
| 6 | Catalog detail Identity (`catalog/show.blade.php`) — `decorative`, `size-16` | `[ ]` | — | — |
| 7 | Catalog index Inertia page (`Catalog/Index.vue` + `CatalogController::index`) — `artworkURL` per row + form | `[ ]` | — | — |
| 8 | Support-card index + detail (Blade) — `<x-support-thumb>` `size-12` / `size-16` | `[ ]` | — | — |
| 9 | Run Create / Legacy Select — `size-10` | `[ ]` | — | — |
| 10 | Cross-doc change-log rows + gate run-down | `[ ]` | — | — |

**Slice report format.** For each landing slice, append a dated block under
`## Slice Log` below in this shape (`Phase gate reporting format`):

```markdown
### YYYY-MM-DD — Task N: <title>
- Files changed: <paths>
- Gates: pint `passed`, phpstan `[OK] No errors`, lore/docs <before>/<after> or
  `<count>/<count>` (unchanged), lore-code `<count>` (unchanged), full suite
  `<headline>` (e.g. `Tests: 1457 passed`), the four doc-gate tests green,
  doc_census clean on the heading.
- Deviations: <list, or `none`>.
- Open decisions: <list with named owner, or `none`>.
- Next slice: Task N+1, deferred reason <if any>.
```

---

## Slice Log

(Slice reports land here as each task commits. Entries are append-only;
neither renumbered nor rewritten.)

---

## Self-Review Notes (filed pre-development)

- **Spec coverage.** Each design-2.0 §45a row is a slice or a deliberate
  deferral: catalog index portrait + form, catalog detail Identity, support
  index + detail, run create / legacy select. Skill icons are the named
  deferral; the prose names the migration requirement.
- **Placeholders.** No `TODO`, no "implement later", no "add validation" step
  that doesn't carry the validation. `ArtworkAssetController` declares the
  allowlist on `kind`; the slot component declares the `decorative` flag;
  the test set asserts the boundary cases (mirrored 200, absent 404,
  unknown kind 404, absent in mirror 200 but absent on disk 404).
- **Type consistency.** `ArtworkMirror::exists(string, int)`,
  `url(string, int)` (and on the controller, `int $id` after the route's
  `whereNumber('id')`) agree across Tasks 1–3, 4–5, and the route
  registration in Task 2.
- **Lore-gate provenance.** All four doc/coded edits made in pre-development
  (design-2.0 §45a, screen-spec-2.0 §34 footnote + §35, the
  `stable`→`fixed focal-length` correction, and this plan file itself) read
  clean against `composer lore` and `composer lore-code`. The plan avoids
  enumerating the banned families inline (`AGENTS.md §18` keeps that list
  in `tools/lore.php` only); any future hit lands with an inline ruling
  per the §5 lore-gate rule.

