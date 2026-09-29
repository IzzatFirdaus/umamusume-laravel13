<?php

declare(strict_types=1);

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\TrainingRunController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/training-runs')->name('home');

Route::get('/umamusume', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/umamusume/{slug}', [CatalogController::class, 'show'])->name('catalog.show');

// Screen D (PRD FR-D-2). Plural noun, no verb, read-only: the same shape as the two surfaces above.
Route::get('/skills', [SkillController::class, 'index'])->name('skills.index');

Route::get('/training-runs', [TrainingRunController::class, 'index'])->name('runs.index');
Route::get('/training-runs/create', [TrainingRunController::class, 'create'])->name('runs.create');
Route::post('/training-runs', [TrainingRunController::class, 'store'])->name('runs.store');
Route::get('/training-runs/{run}', [TrainingRunController::class, 'show'])->name('runs.show');
Route::put('/training-runs/{run}', [TrainingRunController::class, 'update'])->name('runs.update');
Route::delete('/training-runs/{run}', [TrainingRunController::class, 'destroy'])->name('runs.destroy');
Route::get('/training-runs/{run}/export/{format}', [TrainingRunController::class, 'export'])->name('runs.export');
Route::post('/training-runs/{run}/turns', [TrainingRunController::class, 'storeTurn'])->name('runs.turns.store');
Route::put('/training-runs/{run}/turns/{turn}', [TrainingRunController::class, 'updateTurn'])->name('runs.turns.update');
Route::delete('/training-runs/{run}/turns/{turn}', [TrainingRunController::class, 'destroyTurn'])->name('runs.turns.destroy');
Route::post('/training-runs/{run}/skills', [TrainingRunController::class, 'syncSkills'])->name('runs.skills.sync');
Route::post('/training-runs/{run}/races', [TrainingRunController::class, 'storeRace'])->name('runs.races.store');
Route::post('/training-runs/{run}/purchases', [TrainingRunController::class, 'storePurchase'])->name('runs.purchases.store');

Route::get('/review', [ReviewController::class, 'index'])->name('review.index');
Route::post('/review/{candidate}', [ReviewController::class, 'resolve'])->name('review.resolve');
