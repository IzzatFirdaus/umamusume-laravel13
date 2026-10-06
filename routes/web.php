<?php

declare(strict_types=1);

use App\Http\Controllers\ArtworkAssetController;
use App\Http\Controllers\Career\BuildTargetController;
use App\Http\Controllers\Career\CockpitController;
use App\Http\Controllers\Career\DeckSelectController;
use App\Http\Controllers\Career\LegacySelectController;
use App\Http\Controllers\Career\PreflightController;
use App\Http\Controllers\Career\RaceDecisionController;
use App\Http\Controllers\Career\ScenarioSelectController;
use App\Http\Controllers\Career\TraineeProfileController;
use App\Http\Controllers\Career\TraineeSelectController;
use App\Http\Controllers\Career\TrainingDecisionController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LegacyController;
use App\Http\Controllers\PreferenceController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SkillController;
use App\Http\Controllers\SupportCardController;
use App\Http\Controllers\TrainingRunController;
use App\Http\Controllers\VeteranController;
use Illuminate\Support\Facades\Route;

// Trainer Desk 2.0 home (ADR-0020 §1): the SPA Dashboard is the front door. The Blade
// screens below still serve their own routes during the rewrite.
Route::get('/', [DashboardController::class, 'index'])->name('home');

/*
 * The career setup wizard (SCREEN-002 onward, `ADR-0020` §1). Step 1 chooses the scenario and writes
 * it to the session draft, not to a run: `RunStatus` has no draft case and an `Active` run created
 * here would surface as a phantom career on the Dashboard and as a phantom builder target in the
 * Legacy Lab. `SetupDraft`'s class docblock carries the whole reasoning. The run is created once, at
 * Preflight (D7).
 */
Route::get('/career/setup/scenario', [ScenarioSelectController::class, 'show'])->name('career.scenario');
Route::put('/career/setup/scenario', [ScenarioSelectController::class, 'store'])->name('career.scenario.store');

/*
 * Step 2 (SCREEN-003, SCR-CAR-003). The roster is read from the catalog and the choice is written to the
 * same session draft as the scenario, so a trainee selected here creates no run either. `{umamusume}`
 * binds the local primary key rather than the slug, because this is the wizard's own hand-off: the id
 * `SetupDraft` stores is the one a later step writes to `training_runs.umamusume_id`. The profile route
 * sits under the same prefix so the two steps can link each other without a literal path appearing
 * twice.
 */
Route::get('/career/setup/trainee', [TraineeSelectController::class, 'show'])->name('career.trainee');
Route::put('/career/setup/trainee', [TraineeSelectController::class, 'store'])->name('career.trainee.store');
Route::get('/career/setup/trainee/{umamusume}', [TraineeProfileController::class, 'show'])->name('career.trainee.profile');

/*
 * Step 3 (SCREEN-005, SCR-CAR-005). The target is entered before the run exists and written to the
 * same session draft as the scenario and the trainee, under `build_target`. The PUT shares C1's rule
 * set (`StoreBuildTargetRequest` through `StoreDraftBuildTargetRequest`): the clamp resolves against
 * `SetupDraft::planningRun()` instead of a run row, which is what lets the ceiling be enforced for a
 * career Preflight has not created yet.
 */
Route::get('/career/setup/target', [BuildTargetController::class, 'show'])->name('career.target');
Route::put('/career/setup/target', [BuildTargetController::class, 'store'])->name('career.target.store');

/*
 * Steps 4 and 5 (SCR-CAR-008, SCR-CAR-009; plan §8 D5-wizard and D6-wizard). The ancestry and the deck
 * are entered before the run exists and written to the same session draft, under `legacy_selection`,
 * `legacy_parents` and `deck`. Both PUTs share the run-scoped rule sets rather than restating them
 * (`StoreLegacySelectionRequest` through `StoreDraftLegacyRequest`, `StoreDeckRequest` through
 * `StoreDraftDeckRequest`), which is what makes a draft value and a saved one obey the same dictionary.
 * The two surfaces that already hold these facts are run-scoped (`/legacy/{run}`,
 * `/training-runs/{run}/deck`), so before these steps landed there was nowhere to put either choice
 * until Preflight created a run.
 */
Route::get('/career/setup/legacy', [LegacySelectController::class, 'show'])->name('career.legacy');
Route::put('/career/setup/legacy', [LegacySelectController::class, 'store'])->name('career.legacy.store');
Route::get('/career/setup/deck', [DeckSelectController::class, 'show'])->name('career.deck');
Route::put('/career/setup/deck', [DeckSelectController::class, 'store'])->name('career.deck.store');
/*
 * Preflight, and the wizard's one write: the GET composes the draft into one contract and the PUT runs
 * `Start Career`. It is its own route rather than a post to `runs.store` because that endpoint validates
 * the run row alone — the deck, the ancestry and the target would be silently dropped — and because this
 * write re-runs each step's Form Request before it creates anything (`StartCareerRequest`).
 */
Route::get('/career/setup/preflight', [PreflightController::class, 'show'])->name('career.preflight');
Route::put('/career/setup/preflight', [PreflightController::class, 'store'])->name('career.preflight.store');

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
/*
 * The Career Cockpit (SCREEN-009, `SCR-CAR-011`, plan §8 D8). It descends from the run's own URL, as
 * the spec says it does, and it is read-only: the one write it offers posts to `runs.turns.update`
 * below, so no second turn-write route exists. Registered after `runs.show` for readability; the two
 * cannot collide, because the cockpit's path carries an extra segment.
 */
Route::get('/training-runs/{run}/cockpit', [CockpitController::class, 'show'])->name('runs.cockpit');
/*
 * The Training Decision detail (SCREEN-010, `SCR-CAR-012`, plan §8 D9). Read-only: its one write is the
 * guided turn, which posts to `runs.turns.store` below with that route's own Form Request, so no second
 * turn-write route exists (the same rule the cockpit's correction follows).
 */
Route::get('/training-runs/{run}/training', [TrainingDecisionController::class, 'show'])->name('runs.training');
Route::put('/training-runs/{run}', [TrainingRunController::class, 'update'])->name('runs.update');
Route::delete('/training-runs/{run}', [TrainingRunController::class, 'destroy'])->name('runs.destroy');
Route::get('/training-runs/{run}/export/{format}', [TrainingRunController::class, 'export'])->name('runs.export');
Route::post('/training-runs/{run}/turns', [TrainingRunController::class, 'storeTurn'])->name('runs.turns.store');
Route::put('/training-runs/{run}/turns/{turn}', [TrainingRunController::class, 'updateTurn'])->name('runs.turns.update');
Route::delete('/training-runs/{run}/turns/{turn}', [TrainingRunController::class, 'destroyTurn'])->name('runs.turns.destroy');
Route::post('/training-runs/{run}/skills', [TrainingRunController::class, 'syncSkills'])->name('runs.skills.sync');
Route::post('/training-runs/{run}/deck', [TrainingRunController::class, 'syncDeck'])->name('runs.deck.sync');
// The deck builder (SCREEN-007). Read-only; it posts to `runs.deck.sync` above, so the write and its
// Form Request are the ones the run screen already uses. Registered after the POST because Laravel
// matches on method first, and either order would bind the same `{run}`.
Route::get('/training-runs/{run}/deck', [TrainingRunController::class, 'deck'])->name('runs.deck');
Route::post('/training-runs/{run}/races', [TrainingRunController::class, 'storeRace'])->name('runs.races.store');
Route::post('/training-runs/{run}/purchases', [TrainingRunController::class, 'storePurchase'])->name('runs.purchases.store');
Route::put('/training-runs/{run}/build-target', [TrainingRunController::class, 'updateBuildTarget'])->name('runs.build-target.update');

Route::get('/review', [ReviewController::class, 'index'])->name('review.index');
Route::post('/review/{candidate}', [ReviewController::class, 'resolve'])->name('review.resolve');

/*
 * The Legacy Lab (SCREEN-006; PRD FR-G, ADR-0020 §3, under ADR-0010). Record-only: these routes read
 * the Veteran library, record what the Trainer entered, and align stored rows. Nothing here computes
 * an inheritance, an Affinity payout, a star-roll chance, or a recommended combination, and the
 * controller's docblock is where that boundary is stated in full.
 *
 * `/legacy/compare` is declared first and `{run}` is constrained to a number, which is the same pair of
 * decisions `routes/web.php` already makes for `runs.import` and `runs.{run}`: the literal segment
 * would otherwise bind to the model and 404. The constraint is belt-and-braces for the ordering, and
 * it is also what stops `/legacy/compare` from reading as a run id at all.
 */
Route::get('/legacy', [LegacyController::class, 'index'])->name('legacy.index');
Route::get('/legacy/compare', [LegacyController::class, 'compare'])->name('legacy.compare');
Route::get('/legacy/{run}', [LegacyController::class, 'builder'])
    ->whereNumber('run')
    ->name('legacy.builder');
Route::put('/legacy/{run}', [LegacyController::class, 'update'])
    ->whereNumber('run')
    ->name('legacy.update');

/*
 * The Veteran library (SCREEN-021, PRD FR-G-2, plan §8 D16's read half). Two reads and no writes: the row
 * is `ListVeterans` and the detail is `ShowVeteran`, both landed at C3, and `VeteranRow` is the shape the
 * Legacy Lab's own browse list prints, so the two surfaces cannot describe one Veteran differently.
 *
 * There is no write route here on purpose. `RecordVeteran` is landed and has no caller, because the screen
 * that files a career (`SCREEN-020`, Save Veteran) is the other half of D16 and the plan gates it behind
 * D15, Career Result. A library with no rows is therefore the true state of a fresh install, not a broken
 * read, and both pages say so rather than leaving an unexplained empty list.
 */
Route::get('/veterans', [VeteranController::class, 'index'])->name('veterans.index');
Route::get('/veterans/{veteran}', [VeteranController::class, 'show'])->name('veterans.show');

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
