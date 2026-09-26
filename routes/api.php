<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\TrainingRunController;
use App\Http\Controllers\Api\V1\UmamusumeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('umamusume', [UmamusumeController::class, 'index'])->name('umamusume.index');
    Route::get('umamusume/{slug}', [UmamusumeController::class, 'show'])->name('umamusume.show');
    Route::get('training-runs', [TrainingRunController::class, 'index'])->name('training-runs.index');
    Route::get('training-runs/{run}', [TrainingRunController::class, 'show'])->name('training-runs.show');
});
