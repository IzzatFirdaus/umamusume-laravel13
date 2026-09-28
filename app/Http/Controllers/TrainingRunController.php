<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\MoodTier;
use App\Enums\TurnEventType;
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
        $run->load(['umamusume', 'turnEntries', 'skills', 'turnEvents']);

        return view('runs.show', $this->showData($run));
    }

    /**
     * Everything `runs.show` renders, in one place, so the GET and the staged preview
     * cannot drift into two different versions of the same screen.
     *
     * `band` is null rather than an empty array when the run has logged no turns: five
     * zeroed stats would be a claim about a trainee nobody entered (D-220). `guided`
     * always exists, because the door to logging the first turn has to be there before
     * the first turn is.
     *
     * @param  array<string, mixed>  $staged
     * @param  list<array{direction: string, text: string}>  $preview
     * @return array<string, mixed>
     */
    private function showData(TrainingRun $run, array $staged = [], array $preview = []): array
    {
        $latest = $run->turnEntries->sortByDesc('turn')->first();

        return [
            'run' => $run,
            'skills' => Skill::orderBy('name')->get(['id', 'name']),
            'scenarios' => $this->scenarioLabels(),
            'band' => $latest === null ? null : [
                'scenario' => $run->scenarioKey(),
                'values' => [
                    'Speed' => $latest->speed,
                    'Stamina' => $latest->stamina,
                    'Power' => $latest->power,
                    'Guts' => $latest->guts,
                    'Wit' => $latest->wit,
                ],
                'skillPoints' => $latest->sp,
            ],
            'guided' => [
                'scenario' => $run->scenarioKey(),
                // Stage one asks what the turn did; stage two records how it ended.
                'current' => $preview === [] ? 'training' : 'outcome',
                'choices' => $this->turnChoices(),
                'values' => $staged,
                'preview' => $preview,
                'energy' => $staged['energy'] ?? $latest?->energy,
                'mood' => $staged['mood'] ?? $latest?->mood?->value,
                'turn' => (int) ($staged['turn'] ?? $this->nextTurn($run)),
                'has_previous' => $latest !== null,
                'previous' => $latest,
            ],
        ];
    }

    /**
     * The five disciplines plus the two free-turn actions.
     *
     * Labels are the stat words the matrix already uses, not composed client copy: the
     * corpus evidences `Rest` and its +30 (UMAMUSUME_REFERENCE.md §1.1.5) and Wit's zero
     * Energy cost (§1.1.1), and nothing in this repository records an English string for
     * the training buttons, so a `Speed Work` label would be an invented client string
     * promoted into a Trainer-facing select (D-20). The mood row keeps the showcase's
     * treatment for exactly that gap: neutral words, and the gap shown (D-54).
     *
     * @return list<array<string, mixed>>
     */
    private function turnChoices(): array
    {
        $choices = [];

        foreach (config('scenarios.stat_order') as $stat) {
            $choices[] = [
                'key' => 'training-'.$stat,
                'label' => $stat,
                'detail' => $stat === 'Wit'
                    ? 'Costs no Energy, so it stays available when the bar is low.'
                    : 'Energy spent and gains made are what you record for this turn.',
            ];
        }

        $choices[] = [
            'key' => 'rest',
            'label' => 'Rest',
            'detail' => 'Returns about +30 Energy, and a rest can backfire into a stayed-up-late penalty.',
        ];
        $choices[] = [
            'key' => 'mood',
            'label' => 'Mood adjustment',
            'detail' => 'Raises Mood. The client label is not verified.',
            'unverified' => true,
        ];

        return $choices;
    }

    private function nextTurn(TrainingRun $run): int
    {
        return (int) $run->turnEntries->max('turn') + 1;
    }

    /**
     * The row this submission replaces the comparison against: the highest stored turn
     * below the one being entered, not simply the last row. Inserting turn 4 into a
     * three-turn run compares against turn 3; re-editing turn 2 compares against turn 1.
     */
    private function previousTurn(TrainingRun $run, int $turn): ?TurnEntry
    {
        return $run->turnEntries
            ->filter(fn (TurnEntry $entry): bool => $entry->turn < $turn)
            ->sortBy('turn')
            ->last();
    }

    /**
     * The preview, built from what was entered minus what is stored.
     *
     * This is arithmetic on two rows the Trainer typed, not a projection: no gain is
     * forecast and no odds are shown, because no source publishes a failure curve
     * (Planner Rule 5, ADR-0001 §3). Zeros are left out rather than printed, and the
     * rail says so in words when the difference cannot be computed at all.
     *
     * @param  array<string, mixed>  $entered
     * @return list<array{direction: string, text: string}>
     */
    private function previewDeltas(array $entered, ?TurnEntry $previous): array
    {
        if ($previous === null) {
            return [];
        }

        $rows = [
            ['Speed', (int) $entered['speed'] - $previous->speed],
            ['Stamina', (int) $entered['stamina'] - $previous->stamina],
            ['Power', (int) $entered['power'] - $previous->power],
            ['Guts', (int) $entered['guts'] - $previous->guts],
            ['Wit', (int) $entered['wit'] - $previous->wit],
            ['Skill Points', (int) ($entered['sp'] ?? 0) - (int) $previous->sp],
            ['Energy', (int) ($entered['energy'] ?? 0) - (int) $previous->energy],
            ['Fans', (int) ($entered['fans'] ?? 0) - (int) $previous->fans],
        ];

        $moodDelta = $this->moodDelta($entered['mood'] ?? null, $previous->mood);

        if ($moodDelta !== null) {
            $rows[] = ['Mood', $moodDelta];
        }

        $deltas = [];

        foreach ($rows as [$label, $delta]) {
            if ($delta === 0) {
                continue;
            }

            $deltas[] = [
                'direction' => $delta > 0 ? 'up' : 'down',
                'text' => ($delta > 0 ? '+' : '-').abs($delta).' '.$label,
            ];
        }

        return $deltas;
    }

    /**
     * Mood moves in tier steps, which is how the client's own panel counts it
     * (`DESIGN.md` §6.17: +20% at GREAT down to -20% at AWFUL), so the difference of two
     * recorded tiers is a number the Trainer can check rather than a scale invented here.
     *
     * The sign runs the good way up, which means subtracting the new tier from the old:
     * `MoodTier::cases()` is ordered best to worst, so a raw index difference reads
     * GREAT to BAD as +3 and paints a mood collapse in the colour of a gain. The browser
     * pass found it; the first version of this method did not.
     */
    private function moodDelta(mixed $entered, ?MoodTier $stored): ?int
    {
        if ($entered === null || $entered === '' || $stored === null) {
            return null;
        }

        $tier = MoodTier::tryFrom((string) $entered);

        if ($tier === null) {
            return null;
        }

        return array_search($stored, MoodTier::cases(), true)
            - array_search($tier, MoodTier::cases(), true);
    }

    /**
     * Only the columns `turn_entries` owns. The rail's five extra keys are validated but
     * must not ride into the create call: Laravel 13 discards non-fillable keys silently,
     * so a typo here would lose a value without a word of complaint.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function turnAttributes(array $validated): array
    {
        return array_intersect_key(
            $validated,
            array_flip(['turn', 'speed', 'stamina', 'power', 'guts', 'wit', 'sp', 'condition', 'energy', 'mood', 'fans']),
        );
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

    /**
     * Two stages on one endpoint. A staged preview re-renders the screen with the deltas
     * and writes nothing (D-51: "Selecting must never write"), and the confirm stage is
     * the only path that reaches `create`. The FormRequest already refused a confirm that
     * never carried a preview.
     *
     * A recorded failure writes a `turn_events` row. It has no home in `turn_entries`,
     * which holds the absolute values the Trainer read off the client and would rather
     * stay a single kind of thing, and `turn_events` was built for exactly this: an
     * observed outcome with its deltas and a note (ADR-0003). A success writes no event,
     * because the turn row already says everything a success adds.
     */
    public function storeTurn(StoreTurnEntryRequest $request, TrainingRun $run): View|RedirectResponse
    {
        $validated = $request->validated();

        if (($validated['stage'] ?? null) === 'preview') {
            $run->load(['umamusume', 'turnEntries', 'skills', 'turnEvents']);

            return view('runs.show', $this->showData(
                $run,
                $validated,
                $this->previewDeltas($validated, $this->previousTurn($run, (int) $validated['turn'])),
            ));
        }

        $entry = $run->turnEntries()->create($this->turnAttributes($validated));

        if (($validated['outcome'] ?? null) === 'Failure') {
            $label = $this->choiceLabel((string) ($validated['choice'] ?? ''));
            $kind = (string) $validated['penalty_kind'];
            $previous = $this->previousTurn($run, (int) $entry->turn);

            $penalties = [];

            foreach ($this->previewDeltas($validated, $previous) as $delta) {
                if ($delta['direction'] === 'down') {
                    $penalties[$delta['text']] = true;
                }
            }

            $run->turnEvents()->create([
                'turn' => $entry->turn,
                'event_type' => TurnEventType::Failure,
                'source_name' => $label,
                'choice_label' => $label,
                'deltas' => [
                    'penalty_kind' => $kind,
                    'recorded' => array_keys($penalties),
                ],
                'origin_note' => 'The Trainer recorded this turn as a failure of '.$label.', '
                    .'with a '.$kind.' penalty. Logged from the client, not modelled: '
                    .'no source in this repository publishes a failure chance.',
            ]);
        }

        return redirect()->route('runs.show', $run);
    }

    /**
     * The choice's own label, read back out of the list the rail was built from, so the
     * event never stores a word the rail did not offer.
     */
    private function choiceLabel(string $key): string
    {
        foreach ($this->turnChoices() as $choice) {
            if ($choice['key'] === $key) {
                return (string) $choice['label'];
            }
        }

        return $key === '' ? 'unrecorded choice' : $key;
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
