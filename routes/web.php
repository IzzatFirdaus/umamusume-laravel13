<?php

declare(strict_types=1);

use App\Http\Controllers\ArtworkAssetController;
use App\Http\Controllers\Career\BuildTargetController;
use App\Http\Controllers\Career\CockpitController;
use App\Http\Controllers\Career\DeckSelectController;
use App\Http\Controllers\Career\EventDecisionController;
use App\Http\Controllers\Career\InheritanceEventController;
use App\Http\Controllers\Career\LegacySelectController;
use App\Http\Controllers\Career\PreflightController;
use App\Http\Controllers\Career\RaceDecisionController;
use App\Http\Controllers\Career\RacePlannerController;
use App\Http\Controllers\Career\ResultController;
use App\Http\Controllers\Career\SaveVeteranController;
use App\Http\Controllers\Career\ScenarioSelectController;
use App\Http\Controllers\Career\SnapshotController;
use App\Http\Controllers\Career\SkillsPlannerController;
use App\Http\Controllers\Career\TimelineController;
use App\Http\Controllers\Career\TraineeProfileController;
use App\Http\Controllers\Career\TraineeSelectController;
use App\Http\Controllers\Career\TrainingDecisionController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatabaseController;
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

/*
 * The snapshot entry (the remediation's Phase 4). A second door beside the wizard's: a career that is
 * already underway is recorded in one pass at the position the Trainer reads off their client, rather
 * than walked through a flow that assumes turn 1. Its write is its own route rather than a post to
 * `runs.store` for the same reason Preflight's is - `runs.store` validates the run row alone, and a
 * snapshot's position and current state are facts about the career it is entering, not fields that
 * request knows.
 */
Route::get('/career/snapshot', [SnapshotController::class, 'entry'])->name('career.snapshot');
Route::get('/career/snapshot/setup', [SnapshotController::class, 'setup'])->name('career.snapshot.setup');
// The read-back between the form and the write: a GET over the submitted form, so the Trainer reads
// exactly what will be recorded before the confirm button posts it to the commit route below.
Route::get('/career/snapshot/review', [SnapshotController::class, 'review'])->name('career.snapshot.review');
Route::post('/career/snapshot', [SnapshotController::class, 'store'])->name('career.snapshot.store');

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
// F2 (plan §9.6): the 0.1.0 record screen is retired. Every write it owned now has a 2.0 owner
// reachable from the Cockpit, so this GET redirects there. `{run}` still binds, so a missing id 404s.
// Only this GET is redirected; the write routes below keep their own destinations.
Route::get('/training-runs/{run}', [TrainingRunController::class, 'redirect'])->name('runs.show');
/*
 * The Career Cockpit (SCREEN-009, `SCR-CAR-011`, plan §8 D8). It descends from the run's own URL, as
 * the spec says it does, and it is read-only: the one write it offers posts to `runs.turns.update`
 * below, so no second turn-write route exists. Registered after `runs.show` for readability; the two
 * cannot collide, because the cockpit's path carries an extra segment.
 */
Route::get('/training-runs/{run}/cockpit', [CockpitController::class, 'show'])->name('runs.cockpit');
/*
 * The Race Decision (SCREEN-011, `SCR-CAR-013`, plan §8 D10). A read screen over the run's own
 * calendar; its one write is the existing `runs.races.store` below, so no second race-write route
 * exists. It descends from the run's URL the way the Cockpit does.
 */
Route::get('/training-runs/{run}/races', [RaceDecisionController::class, 'show'])->name('runs.races.decision');
/*
 * The Scenario Race Planner (SCREEN-017, `SCR-CAR-023`, plan §9 E5). The whole calendar, grouped into
 * the obligations, the races ahead, the ones reached and unrecorded, and the rival set. Its one write
 * is the existing `runs.races.store` below, so no second race-write route exists. The path carries an
 * extra segment over the Race Decision, so the two cannot collide.
 */
Route::get('/training-runs/{run}/races/planner', [RacePlannerController::class, 'show'])->name('runs.races.planner');
/*
 * The Skills Planner (plan §8 D13, design-2.0 only). A read screen over the run's own skills and
 * build target; its one write is the existing `runs.build-target.update` below (the priority list
 * lives there), so no second target-write route exists.
 */
Route::get('/training-runs/{run}/skills', [SkillsPlannerController::class, 'show'])->name('runs.skills.planner');
/*
 * The Training Decision detail (SCREEN-010, `SCR-CAR-012`, plan §8 D9). Read-only: its one write is the
 * guided turn, which posts to `runs.turns.store` below with that route's own Form Request, so no second
 * turn-write route exists (the same rule the cockpit's correction follows).
 */
Route::get('/training-runs/{run}/training', [TrainingDecisionController::class, 'show'])->name('runs.training');
/*
 * The Inheritance Event (SCREEN-013, `SCR-CAR-015`, plan §8 D12). Record-only: the predicted section
 * shows sourced probabilities, the observed section records what the Trainer saw via the existing
 * TurnEvent mechanism. No inheritance computation is performed (ADR-0020 §3).
 */
Route::get('/training-runs/{run}/inheritance', [InheritanceEventController::class, 'show'])->name('runs.inheritance');
Route::post('/training-runs/{run}/inheritance', [InheritanceEventController::class, 'store'])->name('runs.inheritance.store');
/*
 * The Event Decision (SCREEN-012, `SCR-CAR-014`, plan §8 D11). Record-only over TurnEvent: the
 * run's own recorded choices become the known outcomes, and the advisor refuses rather than
 * ranking. One new write route with one Form Request; no other screen's write changes.
 */
Route::get('/training-runs/{run}/events', [EventDecisionController::class, 'show'])->name('runs.events.decision');
Route::post('/training-runs/{run}/events', [EventDecisionController::class, 'store'])->name('runs.events.store');
/*
 * The Career Timeline (SCREEN-018, `SCR-CAR-017`, plan §8 D14). A read screen that flattens the
 * run's turns, races and events into one ordered rail. No new write route; every value on the
 * page comes from data the existing routes already own.
 */
Route::get('/training-runs/{run}/timeline', [TimelineController::class, 'show'])->name('runs.timeline');
/*
 * The Career Result (SCREEN-019, `SCR-CAR-018`, plan §8 D15). A read screen over a finished run:
 * its final build, its race history and its scenario objectives. No write route; the screen
 * records nothing, and Save Veteran is D16's.
 */
Route::get('/training-runs/{run}/result', [ResultController::class, 'show'])->name('runs.result');
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
 * The Veteran library (SCREEN-021, PRD FR-G-2, plan §8 D16). Three reads: the list is `ListVeterans`, the
 * detail is `ShowVeteran` — both landed at C3 — and the comparison is `SCREEN-022`. `VeteranRow` is the shape
 * all three print, and the Legacy Lab's browse list reads it too, so no surface describes one Veteran
 * differently from another.
 *
 * `/veterans/compare` is declared before `/veterans/{veteran}` and the binding is `whereNumber`. The
 * constraint is what makes the literal unreachable as a model id; the order matches how the `/legacy/compare`
 * block above is arranged, so the two cannot drift into disagreeing about which half is load-bearing.
 */
Route::get('/veterans', [VeteranController::class, 'index'])->name('veterans.index');
Route::get('/veterans/compare', [VeteranController::class, 'compare'])->name('veterans.compare');
Route::get('/veterans/{veteran}', [VeteranController::class, 'show'])
    ->whereNumber('veteran')
    ->name('veterans.show');

/*
 * Save Veteran (SCREEN-020, PRD FR-G-1, plan §8 D16's write half). The pair follows `runs.inheritance`: the
 * screen on GET, the write on POST, both under the run the career belongs to, because a Veteran is a pointer
 * to one career and never a standalone record (`ADR-0010` keeps the run's foreign keys as the identity).
 *
 * This is `RecordVeteran`'s first caller. The block above used to say no route could file a career; the
 * screen that files one is the reason that sentence is now false.
 */
Route::get('/training-runs/{run}/veteran', [SaveVeteranController::class, 'show'])
    ->whereNumber('run')
    ->name('runs.veteran');
Route::post('/training-runs/{run}/veteran', [SaveVeteranController::class, 'store'])
    ->whereNumber('run')
    ->name('runs.veteran.store');

// The two UI preferences PRD US-11 authorizes (SCREEN_SPEC.md §7-5). One PUT for both keys,
// because writing a preference is one action on the store rather than one action per key.
Route::get('/preferences', [PreferenceController::class, 'edit'])->name('preferences.edit');
Route::put('/preferences', [PreferenceController::class, 'update'])->name('preferences.update');

// The Data panel's two actions (SCREEN-024, the D18b slice plan). Export streams this tool's own
// data as a JSON download; Backup writes a server-side snapshot and never streams the database
// file. Restore and Reset stay absent: they are destructive and owner-scoped (AGENTS.md §5).
Route::get('/preferences/export', [PreferenceController::class, 'export'])->name('preferences.export');
Route::post('/preferences/backup', [PreferenceController::class, 'backup'])->name('preferences.backup');

// Streams a mirrored artwork file to an <img src> (ADR-0021 read half). A web route, not /api/v1:
// it serves a browser asset, reads a Storage path, and opens no second outbound surface. `id` is
// constrained to a number so a path-traversal value cannot reach the controller.
Route::get('/artwork/{kind}/{id}', [ArtworkAssetController::class, 'show'])
    ->whereNumber('id')
    ->name('artwork.show');

/*
 * The Database hub (SCREEN-023, plan §8 D17). Five reference-data areas, all read-only.
 *
 * Three of them are the ported catalog surfaces: the route stays under /database/ so the hub has one
 * URL space, but the controller that owns the query renders the page. A redirect would have been the
 * smaller diff, and no owner ruling authorises one, so the three areas render the shared list component
 * the originals use instead of borrowing their address. /umamusume, /skills and /support-cards are
 * unchanged.
 */
Route::get('/database', [DatabaseController::class, 'index'])->name('database.index');
Route::get('/database/trainees', [CatalogController::class, 'databaseIndex'])->name('database.trainees');
Route::get('/database/supports', [SupportCardController::class, 'databaseIndex'])->name('database.supports');
Route::get('/database/skills', [SkillController::class, 'databaseIndex'])->name('database.skills');
Route::get('/database/races', [DatabaseController::class, 'races'])->name('database.races');
Route::get('/database/scenarios', [DatabaseController::class, 'scenarios'])->name('database.scenarios');
// The three reference views (`SCR-SYS-008` to `010`): config-derived tables transcribed from
// `docs/UMAMUSUME_REFERENCE.md`, completing the eight areas SCREEN-023 lists.
Route::get('/database/events', [DatabaseController::class, 'events'])->name('database.events');
Route::get('/database/shop-items', [DatabaseController::class, 'shopItems'])->name('database.shop-items');
Route::get('/database/sparks', [DatabaseController::class, 'sparks'])->name('database.sparks');
