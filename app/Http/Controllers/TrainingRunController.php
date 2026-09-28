<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreRunSkillRequest;
use App\Http\Requests\StoreTrainingRunRequest;
use App\Http\Requests\StoreTurnEntryRequest;
use App\Http\Resources\TrainingRunResource;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\Umamusume;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Trainer-owned run CRUD: runs, their turns, their skill states, and export
 * (PRD US-3/US-4/US-6, FR-C-4/C-5). Validation lives in the Form Requests;
 * deletion cascades to turns and skill rows. No engine data is touched here.
 */
class TrainingRunController extends Controller
{
    public function index(): View
    {
        return view('runs.index', [
            'runs' => TrainingRun::with('umamusume')->latest()->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('runs.create', [
            'umamusumes' => Umamusume::orderBy('name')->get(['id', 'name']),
            'scenarios' => $this->scenarioLabels(),
        ]);
    }

    public function store(StoreTrainingRunRequest $request): RedirectResponse
    {
        $run = TrainingRun::create($request->validated());

        return redirect()->route('runs.show', $run)->with('status', 'Run created.');
    }

    public function show(TrainingRun $run): View
    {
        $run->load(['umamusume', 'turnEntries', 'skills']);

        return view('runs.show', [
            'run' => $run,
            'skills' => Skill::orderBy('name')->get(['id', 'name']),
            'scenarios' => $this->scenarioLabels(),
        ]);
    }

    public function update(StoreTrainingRunRequest $request, TrainingRun $run): RedirectResponse
    {
        $run->update($request->validated());

        return redirect()->route('runs.show', $run);
    }

    public function destroy(TrainingRun $run): RedirectResponse
    {
        $run->delete();

        return redirect()->route('runs.index')->with('status', 'Run deleted.');
    }

    public function storeTurn(StoreTurnEntryRequest $request, TrainingRun $run): RedirectResponse
    {
        $run->turnEntries()->create($request->validated());

        return redirect()->route('runs.show', $run);
    }

    /**
     * Update a nested turn; the turn must belong to the routed run (404
     * otherwise), since route binding alone does not scope it.
     */
    public function updateTurn(StoreTurnEntryRequest $request, TrainingRun $run, TurnEntry $turn): RedirectResponse
    {
        abort_unless($turn->training_run_id === $run->id, 404);

        $turn->update($request->validated());

        return redirect()->route('runs.show', $run);
    }

    public function destroyTurn(TrainingRun $run, TurnEntry $turn): RedirectResponse
    {
        abort_unless($turn->training_run_id === $run->id, 404);

        $turn->delete();

        return redirect()->route('runs.show', $run);
    }

    /**
     * Upsert the given skill statuses on the run; skills not listed keep their
     * current status (syncWithoutDetaching, TrainingRun::setSkillStatus).
     */
    public function syncSkills(StoreRunSkillRequest $request, TrainingRun $run): RedirectResponse
    {
        foreach ($request->validated()['skills'] as $entry) {
            $skill = Skill::find((int) $entry['skill_id']);

            if ($skill !== null) {
                $run->setSkillStatus($skill, $request->acquisitionFor($entry), $entry['turn_acquired'] ?? null);
            }
        }

        return redirect()->route('runs.show', $run);
    }

    /**
     * Scenario slug => the matrix's own display label, for the two forms that offer
     * a choice. Composed from `config('scenarios.php')` rather than from a list
     * here, so a fifth scenario appears without a controller edit (D-240).
     *
     * @return array<string, string>
     */
    private function scenarioLabels(): array
    {
        return array_map(
            static fn (array $def): string => $def['label'],
            config('scenarios.scenarios'),
        );
    }

    /**
     * Download one run as csv (turn rows) or json (TrainingRunResource). Only
     * these two formats exist (PRD FR-C-5; Excel was cut in the Pre-Mortem);
     * anything else 404s. Sent as an attachment so the browser downloads it.
     *
     * @throws NotFoundHttpException on an unknown format
     */
    public function export(TrainingRun $run, string $format): Response
    {
        abort_unless(in_array($format, ['csv', 'json'], true), 404);

        $run->load(['umamusume', 'turnEntries', 'skills']);

        if ($format === 'json') {
            $content = json_encode(['data' => new TrainingRunResource($run)], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } else {
            $rows = [['turn', 'speed', 'stamina', 'power', 'guts', 'wit', 'sp', 'condition']];

            foreach ($run->turnEntries as $entry) {
                $rows[] = [$entry->turn, $entry->speed, $entry->stamina, $entry->power, $entry->guts, $entry->wit, $entry->sp, $entry->condition];
            }

            $content = implode("\n", array_map(fn (array $row): string => implode(',', array_map(fn ($cell): string => (string) ($cell ?? ''), $row)), $rows));
        }

        return response($content, 200, [
            'Content-Type' => $format === 'json' ? 'application/json' : 'text/csv',
            'Content-Disposition' => "attachment; filename=\"run-{$run->id}.{$format}\"",
        ]);
    }
}
