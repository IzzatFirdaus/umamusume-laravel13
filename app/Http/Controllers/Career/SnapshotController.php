<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Actions\CreateSnapshotRun;
use App\Enums\CareerPhase;
use App\Enums\CareerYear;
use App\Enums\SnapshotFieldState;
use App\Http\Controllers\Controller;
use App\Http\Requests\SnapshotReviewRequest;
use App\Http\Requests\StoreSnapshotRequest;
use App\Models\CharacterCard;
use App\Models\Umamusume;
use App\Services\Career\CareerPositionLabel;
use App\Services\CareerCalendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The snapshot entry point, from the Snapshot UI slice of the Rice Shower / Unity Cup remediation.
 *
 * The dashboard's single card routed every career through the new-career wizard, so a Trainer holding
 * a mid-career snapshot had to walk in through a door that assumes turn 1 - which is the audit's
 * finding that the mid-career position had no representation. This is the second door: an entry
 * screen that says what it is for, one form that records the snapshot in a single pass, and one
 * action that writes it.
 *
 * The write reuses the run's own surfaces rather than adding read paths: the position is stored on
 * the run, and the entered current state rides the run's turn log, so the cockpit, the timeline and
 * the training decision all read a snapshot the way they read any other run.
 */
class SnapshotController extends Controller
{
    /**
     * The display labels for the state fields, in the order the review screen reads them.
     *
     * @var array<string, string>
     */
    private const FIELD_LABELS = [
        'speed' => 'Speed',
        'stamina' => 'Stamina',
        'power' => 'Power',
        'guts' => 'Guts',
        'wit' => 'Wit',
        'energy' => 'Energy',
        'fans' => 'Fans',
        'skill_points' => 'Skill Points',
    ];

    public function entry(Request $request): Response
    {
        $draft = $request->session()->get('career.snapshot.draft');

        return Inertia::render('Career/Snapshot/Entry', [
            // The stepper's Review door is gated on a parked draft: Review reads back what Setup
            // parked, so an Entry visit before any record has nothing to read back
            // (AUD-20261102-007 F-01).
            'hasDraft' => is_array($draft) && $draft !== [],
        ]);
    }

    public function setup(Request $request): Response
    {
        return Inertia::render('Career/Snapshot/Setup', [
            'trainees' => Umamusume::query()->orderBy('name')->get(['id', 'name'])->values()->all(),
            // N3.31: the trainee picker's combobox roster, in the exact `TraineeRow` shape
            // `TrainingRunController::create()` feeds `TraineeCombobox.vue` — one query with the
            // costume cards eager-loaded, so the swap at the boundary needs no second endpoint and
            // no shape adaptation. Cards gate `unconfirmed = false` the same way the run-create
            // roster does: a form no source confirmed stays out of the payload, and a trainee with
            // no confirmed form ships `cards: []` which the combobox paints as its own
            // "No costume card confirmed yet" row. Scope stays DB-wide (not GlobalReleased-only)
            // because a snapshot records an existing career and `StoreSnapshotRequest` accepts any
            // stored `umamusume.id`; the stored identity stays `umamusume_id`, and a card row's
            // `selectionId` is the local `character_cards.id` the run's foreign key reads.
            'roster' => $this->comboboxRoster(),
            'scenarios' => array_map(
                static fn (array $def): string => (string) $def['label'],
                (array) config('scenarios.scenarios'),
            ),
            'years' => array_map(
                static fn (CareerYear $year): array => ['value' => $year->value, 'label' => $year->label()],
                CareerYear::cases(),
            ),
            'phases' => array_map(
                static fn (CareerPhase $phase): array => ['value' => $phase->value, 'label' => $phase->value],
                CareerPhase::cases(),
            ),
            // The setup form's submit opens the review, which is where the snapshot is read back before
            // it is written. The commit endpoint is not handed to this form: the review screen posts to
            // it, and it receives its own `action` from `review()`.
            'review' => route('career.snapshot.review'),
            // The review screen's Back returns here, and without this the Trainer landed on a blank
            // form having just confirmed their entries. `review()` parks the validated payload in the
            // session for exactly this read; a first visit has none and the form opens empty.
            'draft' => $request->session()->get('career.snapshot.draft'),
            // N3.32: the readable position for every year/month/half the three selects can produce,
            // derived here by the same owners (`CareerCalendar::turnIndexFor` +
            // `CareerPositionLabel::forTurn`) that label the review, the race header and the
            // Dashboard. A keyed table rather than a turn formula in the page: the frontend looks up
            // the label the Trainer's own selection earns and never does calendar arithmetic.
            'position_labels' => $this->positionLabels(),
        ]);
    }

    /**
     * The read-back the Trainer confirms before committing.
     *
     * It is a POST of the form the setup screen submitted, so nothing the Trainer typed travels in a
     * URL, and the same `StoreSnapshotRequest` validates it here: a snapshot missing its position is
     * refused with the field named before any row is written, not after. The validated payload travels
     * to the page so the confirm button re-posts exactly what was read back rather than re-collecting
     * it.
     */
    public function review(SnapshotReviewRequest $request): Response
    {
        // The validated body where the setup screen posted one, the parked draft otherwise. The
        // boundary is a Form Request now (S1.16): `reviewData()` used to call
        // `Validator::make($submitted, StoreSnapshotRequest::fieldRules())->validate()` inline, so the
        // rules were shared but the BOUNDARY was a controller method, which is the Floor's no-inline-
        // validation rule one layer down.
        $data = $request->hasSnapshotPayload()
            ? $request->validated()
            : $this->reviewData($request);

        if ($data !== []) {
            // Parked for `setup()`: the review screen's Back returns to the form, and the Trainer
            // should meet their own entries rather than an empty one.
            $request->session()->put('career.snapshot.draft', $data);
        }

        return Inertia::render('Career/Snapshot/Review', [
            'fields' => $this->reviewFields($data),
            'payload' => $data,
            'has_draft' => $data !== [],
            // U-03 / R8 (narrowed): the client's own banner name for the scenario, printed under the
            // read-back as a subtitle. Kept out of the `fields` list on purpose — those rows are the
            // things the write records, and `SnapshotReviewTest` pins their order and wording.
            'scenario_full_label' => $this->scenarioFullLabel($data),
            // The position the snapshot names, through the same owner the race header and the
            // Dashboard use, so the read-back and the cockpit cannot spell it two ways. A review
            // opened with no draft has no position to name rather than a wrong one.
            'position_label' => $this->hasPosition($data) ? $this->positionLabel($data) : null,
            'action' => route('career.snapshot.store'),
            'back' => route('career.snapshot.setup'),
            'entry' => route('career.snapshot'),
        ]);
    }

    /**
     * The payload the review reads: the submitted request body when the setup screen posted it,
     * otherwise the draft parked in the session by an earlier read, otherwise nothing.
     *
     * The body bag only, deliberately: reading `input()` would merge the query string, so a payload
     * that reached this route in a URL would be honoured, and the URL is the thing being retired.
     *
     * The bare review URL is a real state, not an error: the HFW's review draws every field as
     * "Not provided" when no snapshot has been entered, and the confirm has nothing to re-post.
     *
     * @return array<string, mixed>
     */
    private function reviewData(SnapshotReviewRequest $request): array
    {
        $draft = $request->session()->get('career.snapshot.draft');

        return is_array($draft) ? $draft : [];
    }

    /**
     * Whether the payload names a position the shared label can derive.
     *
     * @param  array<string, mixed>  $data
     */
    private function hasPosition(array $data): bool
    {
        return isset($data['career_year'], $data['career_month'], $data['career_phase']);
    }

    /**
     * The position in the client's own words. A snapshot arrives as year/month/phase, so the turn is
     * derived once here and handed to the shared label; nothing about the wording lives in this class.
     *
     * @param  array<string, mixed>  $data
     */
    private function positionLabel(array $data): string
    {
        $turn = CareerCalendar::turnIndexFor(
            CareerYear::from((int) $data['career_year']),
            (int) $data['career_month'],
            CareerPhase::from((string) $data['career_phase']),
        );

        return (string) CareerPositionLabel::forTurn($turn);
    }

    /**
     * The combobox roster `TraineeCombobox.vue` reads: trainees with their confirmed costume
     * cards eager-loaded into the component's own row shape. One query and one lazy load for the
     * whole roster, mirroring `TrainingRunController::create()`'s read so two screens cannot drift
     * into two lists (ADR-0020 §1's one-picker rule). Card fields are the component's contract:
     * `selectionId` is the local `character_cards.id` the write validates against, `sourceCardId`
     * is the source's own id for display only, `titleKey` is bracket-stripped so prefix matching
     * works on the verbatim `[Brackets]` title, and `releaseDate` stays an empty string where the
     * source carries no date rather than claiming one.
     *
     * @return list<array{umamusumeId: int, trainee: string, traineeJa: string|null, cards: list<array{selectionId: int, sourceCardId: int, title: string, titleKey: string, releaseDate: string, debut: bool}>}>
     */
    private function comboboxRoster(): array
    {
        $trainees = Umamusume::query()
            ->with(['cards' => fn ($query) => $query
                ->where('unconfirmed', false)
                ->orderBy('global_release_date')
                ->orderBy('card_id')])
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return $trainees
            ->map(fn (Umamusume $u): array => [
                'umamusumeId' => $u->id,
                'trainee' => $u->name,
                'traineeJa' => $u->name_ja,
                'cards' => $u->cards->map(fn (CharacterCard $c): array => [
                    'selectionId' => $c->id,
                    'sourceCardId' => $c->card_id,
                    'title' => $c->title,
                    'titleKey' => preg_replace('/^\\[|\\]$/', '', $c->title) ?? $c->title,
                    'releaseDate' => $c->global_release_date?->toDateString() ?? '',
                    'debut' => $c->is_debut_form,
                ])->all(),
            ])
            ->all();
    }

    /**
     * Every position the setup form's three selects can name, keyed `<year>:<month>:<phase>`.
     *
     * The setup screen prints the same sentence the review prints for the same selection, so the
     * Trainer sees where the run will stand before reading it back. One server-side table keeps the
     * calendar arithmetic in `CareerCalendar`/`CareerPositionLabel` where the rest of the app already
     * reads it; the key is the page's own form values joined, not a derived turn. Colons rather than
     * dots, because Inertia reads a dot in a prop key as a nested path.
     *
     * @return array<string, string>
     */
    private function positionLabels(): array
    {
        $labels = [];

        foreach (CareerYear::cases() as $year) {
            for ($month = 1; $month <= 12; $month++) {
                foreach (CareerPhase::cases() as $phase) {
                    $turn = CareerCalendar::turnIndexFor($year, $month, $phase);

                    $labels[$year->value.':'.$month.':'.$phase->value] = (string) CareerPositionLabel::forTurn($turn);
                }
            }
        }

        return $labels;
    }

    public function store(StoreSnapshotRequest $request): RedirectResponse
    {
        $run = (new CreateSnapshotRun)->handle($request->validated());

        return redirect()
            ->route('runs.cockpit', $run)
            ->with('status', 'Snapshot recorded.');
    }

    /**
     * One row per state field: its label, its three-way state, and the exact string the screen prints.
     *
     * The three states are distinct sentences, never three renderings of an absence: Known names the
     * value, Unknown says the Trainer looked and the client shows nothing, and Not provided says the
     * field was left alone. A reviewer can therefore tell "I don't know this" from "I didn't fill it in".
     *
     * @param  array<string, mixed>  $data
     * @return list<array{key: string, label: string, state: string, state_label: string, value: int|null, display: string}>
     */
    private function reviewFields(array $data): array
    {
        $states = is_array($data['field_states'] ?? null) ? $data['field_states'] : [];

        $rows = [];

        // R7-99: the two identity fields the snapshot is *about* lead the read-back. Both are
        // `required` in `StoreSnapshotRequest::fieldRules()`, and `payload` already carries them, so the
        // write was always correct — the review simply never rendered what it was going to write about.
        // The screen's own copy is "This is exactly what will be written. Read it back", and a Trainer
        // could not confirm the trainee or the scenario from it.
        $identity = [
            ['key' => 'umamusume_id', 'label' => 'Trainee', 'value' => $this->traineeName($data)],
            ['key' => 'scenario', 'label' => 'Scenario', 'value' => $this->scenarioLabel($data)],
            // U-03 / R8 (E-04): the two facts the setup form now collects. They are read back in the
            // same `Known · …` / `Not provided · …` vocabulary as everything else the review lists, so a
            // Trainer confirms them before the write exactly as they confirm the stats. The value is a
            // client word (`★3`, `Potential Lvl 2`) rather than the stored integer, because the review's
            // own sentence is "This is exactly what will be written. Read it back" and an integer is not
            // what the client shows.
            [
                'key' => 'trainee_rarity',
                'label' => 'Rarity',
                'value' => isset($data['trainee_rarity']) && $data['trainee_rarity'] !== ''
                    ? '★'.(int) $data['trainee_rarity']
                    : null,
            ],
            [
                'key' => 'potential_level',
                'label' => 'Potential Level',
                'value' => isset($data['potential_level']) && $data['potential_level'] !== ''
                    ? (string) (int) $data['potential_level']
                    : null,
            ],
        ];

        foreach ($identity as $row) {
            $known = is_string($row['value']) && $row['value'] !== '';

            $rows[] = [
                'key' => $row['key'],
                'label' => $row['label'],
                'state' => $known ? SnapshotFieldState::Known->value : SnapshotFieldState::NotProvided->value,
                'state_label' => $known ? SnapshotFieldState::Known->label() : SnapshotFieldState::NotProvided->label(),
                'value' => null,
                'display' => $known
                    ? "Known · {$row['label']} {$row['value']}"
                    : "Not provided · {$row['label']}",
            ];
        }

        foreach (StoreSnapshotRequest::STATE_FIELDS as $field) {
            $label = self::FIELD_LABELS[$field];

            $state = is_string($states[$field] ?? null)
                ? (SnapshotFieldState::tryFrom($states[$field]) ?? SnapshotFieldState::NotProvided)
                : (($data[$field] ?? null) === null || ($data[$field] ?? null) === ''
                    ? SnapshotFieldState::NotProvided
                    : SnapshotFieldState::Known);

            // S1.1: a state can claim `known` while the value is absent — the payload arrives from a
            // form, and a form can carry a flag without its number (a truncated submission, a crafted
            // POST, a draft parked by an older revision). The cast must not fatal on that; a claimed
            // Known with no figure reads as no value recorded.
            $value = $state === SnapshotFieldState::Known && isset($data[$field]) && $data[$field] !== ''
                ? (int) $data[$field]
                : null;

            $rows[] = [
                'key' => $field,
                'label' => $label,
                'state' => $state->value,
                'state_label' => $state->label(),
                'value' => $value,
                'display' => match ($state) {
                    SnapshotFieldState::Known => "Known · {$label} {$value}",
                    SnapshotFieldState::Unknown => "Unknown · {$label}",
                    SnapshotFieldState::NotProvided => "Not provided · {$label}",
                },
            ];
        }

        return $rows;
    }

    /**
     * The trainee's display name for the read-back, or null when the payload names none.
     *
     * @param  array<string, mixed>  $data
     */
    private function traineeName(array $data): ?string
    {
        $id = $data['umamusume_id'] ?? null;

        if (! is_int($id) && ! (is_string($id) && ctype_digit($id))) {
            return null;
        }

        return Umamusume::query()->find((int) $id)?->name;
    }

    /**
     * The scenario's configured label, never the stored slug.
     *
     * @param  array<string, mixed>  $data
     */
    private function scenarioLabel(array $data): ?string
    {
        $key = $data['scenario'] ?? null;

        if (! is_string($key) || $key === '') {
            return null;
        }

        $label = config('scenarios.scenarios.'.$key.'.label');

        return is_string($label) ? $label : null;
    }

    /**
     * The client's own full scenario name, for the read-back's second line (U-03 / R8, narrowed).
     *
     * Kept separate from `scenarioLabel()` on purpose: that one is the short name every other surface
     * prints and `SnapshotReviewTest` pins it deliberately ("Config's label, never the stored key"). This
     * one is the in-game banner text the client shows — `Unity Cup: Shine On, Team Spirit!` — and it is
     * a subtitle on the same row rather than a replacement for the identity the row already states.
     *
     * @param  array<string, mixed>  $data
     */
    private function scenarioFullLabel(array $data): ?string
    {
        $key = $data['scenario'] ?? null;

        if (! is_string($key) || $key === '') {
            return null;
        }

        $full = config('scenarios.scenarios.'.$key.'.full_label');

        return is_string($full) && $full !== '' ? $full : null;
    }
}
