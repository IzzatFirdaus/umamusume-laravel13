<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\SupportCardController;
use App\Http\Controllers\Api\V1\TelemetryStatusController;
use App\Http\Controllers\Api\V1\TrainingRunController;
use App\Http\Controllers\Api\V1\UmamusumeController;
use App\Http\Middleware\LangfuseTraceMiddleware;
use App\Http\Middleware\LatitudeTraceMiddleware;
use Illuminate\Support\Facades\Route;

// Telemetry status is deliberately outside the tracing middleware group to
// prevent recursive observations from status polling.
Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('telemetry/status', [TelemetryStatusController::class, 'show'])->name('telemetry.status');
});

Route::middleware([LangfuseTraceMiddleware::class, LatitudeTraceMiddleware::class])
    ->prefix('v1')->name('api.v1.')->group(function (): void {
        Route::get('umamusume', [UmamusumeController::class, 'index'])->name('umamusume.index');
        Route::get('umamusume/{slug}', [UmamusumeController::class, 'show'])->name('umamusume.show');
        Route::get('training-runs', [TrainingRunController::class, 'index'])->name('training-runs.index');
        Route::get('training-runs/{run}', [TrainingRunController::class, 'show'])->name('training-runs.show');
        Route::get('support-cards', [SupportCardController::class, 'index'])->name('support-cards.index');
        Route::get('support-cards/{supportCard}', [SupportCardController::class, 'show'])->name('support-cards.show');
    });
