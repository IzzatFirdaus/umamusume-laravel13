<?php

declare(strict_types=1);

use App\Http\Controllers\ArtworkAssetController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\SupportCardController;
use App\Http\Controllers\TrainingRunController;
use Illuminate\Support\Facades\Route;

// Trainer Desk 2.0 home (ADR-0020 §1): the SPA Dashboard is the front door. The Blade
// screens below still serve their own routes during the rewrite.
Route::get('/', [DashboardController::class, 'index'])->name('home');

Route::get('/umamusume', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/umamusume/{slug}', [CatalogController::class, 'show'])->name('catalog.show');

// Screen D (PRD FR-D-2). Plural noun, no verb, read-only: the same shape as the two surfaces above.
Route::get('/skills', [SkillController::class, 'index'])->name('skills.index');
Route::get('/skills/{skill}', [SkillController::class, 'show'])->name('skills.show');

// The card catalog (PRD FR-B; ADR-0014). Same plural-noun, read-only shape as the two surfaces above.
Route::get('/support-cards', [SupportCardController::class, 'index'])->name('support-cards.index');
Route::get('/support-cards/{card}', [SupportCardController::class, 'show'])->name('support-cards.show');

Route::get('/training-runs', [TrainingRunController::class, 'index'])->name('runs.index');
Route::get('/training-runs/create', [TrainingRunController::class, 'create'])->name('runs.create');
Route::post('/training-runs', [TrainingRunController::class, 'store'])->name('runs.store');
// Registered before the {run} routes below, which would otherwise match the literal segment
// "import" as a run id and 404 on a model binding instead of showing the form.
Route::get('/training-runs/import', [TrainingRunController::class, 'importForm'])->name('runs.import');
Route::post('/training-runs/import', [TrainingRunController::class, 'importStore'])->name('runs.import.store');
Route::post('/training-runs/import/preview', [TrainingRunController::class, 'importPreview'])->name('runs.import.preview');
Route::get('/training-runs/{run}', [TrainingRunController::class, 'show'])->name('runs.show');
Route::put('/training-runs/{run}', [TrainingRunController::class, 'update'])->name('runs.update');
Route::delete('/training-runs/{run}', [TrainingRunController::class, 'destroy'])->name('runs.destroy');
Route::get('/training-runs/{run}/export/{format}', [TrainingRunController::class, 'export'])->name('runs.export');
Route::post('/training-runs/{run}/turns', [TrainingRunController::class, 'storeTurn'])->name('runs.turns.store');
Route::put('/training-runs/{run}/turns/{turn}', [TrainingRunController::class, 'updateTurn'])->name('runs.turns.update');
Route::delete('/training-runs/{run}/turns/{turn}', [TrainingRunController::class, 'destroyTurn'])->name('runs.turns.destroy');
Route::post('/training-runs/{run}/skills', [TrainingRunController::class, 'syncSkills'])->name('runs.skills.sync');
Route::post('/training-runs/{run}/deck', [TrainingRunController::class, 'syncDeck'])->name('runs.deck.sync');
Route::post('/training-runs/{run}/races', [TrainingRunController::class, 'storeRace'])->name('runs.races.store');
Route::post('/training-runs/{run}/purchases', [TrainingRunController::class, 'storePurchase'])->name('runs.purchases.store');

Route::get('/review', [ReviewController::class, 'index'])->name('review.index');
Route::post('/review/{candidate}', [ReviewController::class, 'resolve'])->name('review.resolve');

// The two UI preferences PRD US-11 authorizes (SCREEN_SPEC.md §7-5). One PUT for both keys,
// because writing a preference is one action on the store rather than one action per key.
Route::get('/preferences', [PreferenceController::class, 'edit'])->name('preferences.edit');
Route::put('/preferences', [PreferenceController::class, 'update'])->name('preferences.update');

// Streams a mirrored artwork file to an <img src> (ADR-0021 read half). A web route, not /api/v1:
// it serves a browser asset, reads a Storage path, and opens no second outbound surface. `id` is
// constrained to a number so a path-traversal value cannot reach the controller.
Route::get('/artwork/{kind}/{id}', [ArtworkAssetController::class, 'show'])
    ->whereNumber('id')
    ->name('artwork.show');
