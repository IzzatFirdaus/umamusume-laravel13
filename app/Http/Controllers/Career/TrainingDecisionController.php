<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Enums\MoodTier;
use App\Enums\PerformanceType;
use App\Enums\TurnChoice;
use App\Http\Controllers\Controller;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\DeckSlot;
use App\Models\SupportCard;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\Advisor\Advice;
use App\Services\Advisor\AdvisorOption;
use App\Services\Advisor\TrainerAdvisor;
use App\Services\ScenarioCaps;
use App\Services\SupportCardEffects;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The Training Decision detail, `SCR-CAR-012` (SCREEN-010, plan §8 D9). Compare the five training
 * actions and record the one the Trainer chooses.
 *
 * **What the screen may not say.** A per-training stat yield and a failure probability have no source:
 * the advisor spec puts both out of scope for v1 (`docs/research-scratch/PROCESS-PLANS.md` section
 * `trainer-advisor.md` §1 and §5) and `ADR-0001` §3 records that no source publishes a curve, a table
 * or a single probability. So neither arrives on the wire at all: `CareerTrainingDetailTest` pins an
 * option payload to a declared key set, and a field that could hold a projected number fails that test
 * before it can reach a screen. The page renders `N/A` with the exclusion named in its `title`. The
 * design brief for this screen prints a projected gain and a failure percentage per card, which states
 * intent rather than data; its own correction table rules those figures unsourced
 * (`docs/proposals/screen-spec-2.0.md`, PRODUCT DIRECTION CORRECTIONS, SCREEN-010 row). They are not
 * quoted here, so a grep for a hard-coded yield or failure number over this slice has one answer.
 *
 * **Every figure comes from one of four places, and none of them is this controller**: entered values
 * (`TurnEntry`), the run's own target (`BuildTargetPayload`), declared constants with their source and
 * date (`config/advisor.php`, read through `TrainerAdvisor::constants()` so a surface prints the date
 * behind a number without a second lookup, `ADR-0001` §7), and the scenario matrix
 * (`config/scenarios.php`). The one arithmetic this screen states, the target deficit, is C2's own and
 * is not repeated here: `ADR-0001` §2 keeps the constants visible wherever the number appears, and two
 * places doing the same subtraction is two places to be wrong.
 *
 * **The write is the guided turn's, unchanged.** `Train` posts `stage=preview` to `runs.turns.store`
 * and `StoreTurnEntryRequest` validates it, exactly as the run record screen's rail does; no route,
 * Form Request or column is added. The five stat totals that request requires are what a Trainer reads
 * off the client after the turn resolves, so the form starts empty: a prefilled number would claim a
 * gain this tool cannot source. The preview is a screen, and the screen that renders it is the run
 * record screen, so the preview POST lands there and the turn is confirmed there.
 */
class TrainingDecisionController extends Controller
{
    /**
     * The scenario-matrix keys that bear on a training decision, and which cards they belong on.
     *
     * `stats` null means every training card; a list means only those. A key the matrix declares and
     * this map does not is not rendered here, because inventing a label for a config word is how a
     * screen ends up asserting a mechanic nobody stated.
     *
     * ponytail: two keys are mapped because two bear on this decision. When a scenario adds a third,
     * add it here rather than reading the whole entry onto the page.
     *
     * @var list<array{key: string, label: string, stats: list<string>|null}>
     */
    private const SCENARIO_EFFECTS = [
        ['key' => 'team_training_energy_penalty', 'label' => 'Extra Energy cost for team training', 'stats' => null],
        ['key' => 'wit_burst_energy_bonus', 'label' => 'Energy a Wit burst returns', 'stats' => ['Wit']],
    ];

    /**
     * The stat each discipline's secondary gain feeds: the game's own routing, which
     * `UMAMUSUME_REFERENCE.md` §1.1.1 records as `[Both]` (Game8 and Kamigame agree on the mapping;
     * only the numbers are JP-measured). Guts feeds two, in the order the 2026-10-15 artifact's own
     * cells print them, which is why its rows key the pair `secondary_a`/`secondary_b`.
     *
     * @var array<string, list<string>>
     */
    private const SECONDARY_ROUTES = [
        'Speed' => ['Power'],
        'Stamina' => ['Guts'],
        'Power' => ['Stamina'],
        'Guts' => ['Speed', 'Power'],
        'Wit' => ['Speed'],
    ];

    /**
     * The training stat to the `turn_entries` column holding its facility level.
     *
     * One map for the two readers this page has: the published per-level table and the record form's
     * pre-fill. Both must state the same level for the same stat, so neither may re-derive it.
     */
    private const FACILITY_COLUMNS = [
        'Speed' => 'facility_speed',
        'Stamina' => 'facility_stamina',
        'Power' => 'facility_power',
        'Guts' => 'facility_guts',
        'Wit' => 'facility_wit',
    ];

    public function show(TrainingRun $run): Response
    {
        $run->load(['umamusume', 'turnEntries', 'deckSlots.supportCard']);

        $advisor = app(TrainerAdvisor::class);
        $latest = $this->statsSource($run);
        $facilityLevels = $this->facilityLevels($run);
        $advice = $advisor->advise($latest, $run->buildTarget(), $run);
        $target = $run->buildTarget();
        $scenarioKey = $run->hasScenario() ? $run->scenarioKey() : null;

        /** @var list<string> $order */
        $order = config('scenarios.stat_order');
        /** @var array<string, array{value: mixed, source: string, verified_at: string, confidence: string}> $constants */
        $constants = $advisor->constants();
        $caps = ScenarioCaps::forRun($run);
        $dictionary = SupportCardEffects::dictionary();

        return Inertia::render('Career/TrainingDetail', [
            'run' => $this->runSection($run),
            // The run's own position, so the screen can name the turn it stands on rather than a
            // number with no calendar behind it. Null for a run that has neither logged nor imported.
            'careerPosition' => $run->careerPosition()?->toArray(),
            'currentStats' => $this->currentStats($latest),
            // The run's own Energy reading, as a state rather than a bare number: exact, a named band,
            // or the explicit "not recorded" the audit found the tool collapsing into N/A everywhere.
            'energy' => $this->energySection($latest),
            'options' => array_map(
                fn (string $stat): array => $this->optionSection(
                    $stat,
                    $latest,
                    $target,
                    $caps[$stat],
                    $advice,
                    $run->deckSlots,
                    $constants,
                    $dictionary,
                    $scenarioKey,
                    $facilityLevels,
                ),
                $order,
            ),
            'deck' => [
                'recorded' => $run->deckSlots->isNotEmpty(),
                'slots' => $run->deckSlots->count(),
            ],
            'advisor' => $this->advisorSection($advice),
            'write' => $this->writeSection($run, $latest, $advice, $facilityLevels),
        ]);
    }

    /**
     * The run's identity and the doors a Trainer wants from it.
     *
     * @return array{id: int, trainee: string, trainee_ja: string|null, status: string, status_label: string, scenario_label: string, run_url: string, cockpit_url: string}
     */
    private function runSection(TrainingRun $run): array
    {
        return [
            'id' => $run->id,
            'trainee' => $run->umamusume->name,
            'trainee_ja' => $run->umamusume->name_ja,
            'status' => $run->status->value,
            'status_label' => $run->status->label(),
            // The absence is named rather than borrowed from the baseline's label: no scenario is not
            // the same fact as the baseline scenario (D-220).
            'scenario_label' => $run->hasScenario()
                ? (string) config('scenarios.scenarios.'.$run->scenarioKey().'.label', $run->scenarioKey())
                : 'No scenario set',
            'run_url' => route('runs.cockpit', $run),
            'cockpit_url' => route('runs.cockpit', $run),
            // The deck this screen counts is the run's own, so the link that offers to fill it goes to
            // the run-scoped builder rather than the wizard's step (R2-18).
            'deck_url' => route('runs.deck', $run),
        ];
    }

    /**
     * One card's whole payload, assembled from the four sources and nothing else.
     *
     * @param  array<string, array{value: mixed, source: string, verified_at: string, confidence: string}>  $constants
     * @param  Collection<int, DeckSlot>  $slots
     * @param  array<int, array{name: string, symbol: string|null, calc: string|null}>  $dictionary
     * @param  array<string, int|null>  $facilityLevels
     * @return array<string, mixed>
     */
    private function optionSection(
        string $stat,
        ?TurnEntry $latest,
        ?BuildTargetPayload $target,
        int $cap,
        Advice $advice,
        Collection $slots,
        array $constants,
        array $dictionary,
        ?string $scenarioKey,
        array $facilityLevels,
    ): array {
        $option = $this->adviceOption($advice, $stat);

        return [
            'key' => $stat,
            'choice' => 'training-'.$stat,
            'recommended' => $advice->recommendation?->action === $stat,
            'current' => $latest?->statValue($stat),
            'target' => $target?->targets[$stat] ?? null,
            'cap' => $cap,
            'cap_bonus' => $this->capBonus($stat, $scenarioKey),
            'deficit' => $option?->deficitClosed,
            'band' => $option?->band,
            'reason' => $option?->reason,
            'energy_after' => $option?->energyAfter,
            'cost' => $this->costFor($stat, $constants),
            'supports' => $this->supportsFor($stat, $slots, $dictionary),
            'scenario_effects' => $this->scenarioEffectsFor($stat, $scenarioKey),
            // The published per-level reference figures the scenario matrix carries, keyed to the
            // facility level this run last recorded for that training. A projected yield for this run
            // is still absent: the key carries published base rows, never a number computed here.
            'published' => $this->publishedFor($stat, $scenarioKey, $facilityLevels),
        ];
    }

    /**
     * The advisor's own option for this training, or null when it did not produce one.
     *
     * C2 drops an option it cannot give a reason line (`ADR-0001` §4), so the absence is a real state
     * the page prints rather than something this controller re-derives to fill the gap.
     */
    private function adviceOption(Advice $advice, string $stat): ?AdvisorOption
    {
        foreach ($advice->options as $option) {
            if ($option->action === $stat) {
                return $option;
            }
        }

        return null;
    }

    /**
     * The declared cost of this training, with the source and the date it was read.
     *
     * `session_cost` is a range and is passed as one; `wit_cost` is a single figure, so its two ends
     * are equal, which is the shape a surface already knows how to render (`ADR-0001` §2).
     *
     * @param  array<string, array{value: mixed, source: string, verified_at: string, confidence: string}>  $constants
     * @return array{min: int, max: int, source: string, verified_at: string, confidence: string}
     */
    private function costFor(string $stat, array $constants): array
    {
        $entry = $constants[$stat === 'Wit' ? 'wit_cost' : 'session_cost'];

        if ($stat === 'Wit') {
            $cost = (int) $entry['value'];
            $min = $cost;
            $max = $cost;
        } else {
            /** @var array{0: int, 1: int} $range */
            $range = $entry['value'];
            [$min, $max] = $range;
        }

        return [
            'min' => $min,
            'max' => $max,
            'source' => $entry['source'],
            'verified_at' => $entry['verified_at'],
            'confidence' => $entry['confidence'],
        ];
    }

    /**
     * The deck cards entered for this training, with the anchor values the source states.
     *
     * The match is on the client's word, so an `intelligence` card lands on the Wit card and the export
     * key never reaches the screen (`SupportCard::typeWord()`).
     *
     * @param  Collection<int, DeckSlot>  $slots
     * @param  array<int, array{name: string, symbol: string|null, calc: string|null}>  $dictionary
     * @return list<array{name: string, effects: list<array{effect_id: int, name: string|null, display: string}>}>
     */
    private function supportsFor(string $stat, Collection $slots, array $dictionary): array
    {
        return array_values($slots
            ->filter(static fn (DeckSlot $slot): bool => SupportCard::typeWord($slot->supportCard->type) === $stat)
            ->map(static fn (DeckSlot $slot): array => [
                'name' => $slot->supportCard->displayName(),
                'effects' => array_map(
                    static fn (array $effect): array => [
                        'effect_id' => $effect['effect_id'],
                        'name' => $effect['name'],
                        'display' => $effect['display'],
                    ],
                    SupportCardEffects::atCap($slot->supportCard, $dictionary),
                ),
            ])
            ->values()
            ->all());
    }

    /**
     * The scenario's cap bonus for this stat, or null when the run declares no scenario.
     */
    private function capBonus(string $stat, ?string $scenarioKey): ?int
    {
        if ($scenarioKey === null) {
            return null;
        }

        /** @var array<string, int> $bonus */
        $bonus = (array) config('scenarios.scenarios.'.$scenarioKey.'.cap_bonus', []);

        return $bonus[$stat] ?? null;
    }

    /**
     * What the scenario matrix says about this training, in the matrix's own words.
     *
     * A run with no scenario is null, which is one absence; a scenario that declares nothing
     * applicable is an empty list, which is another, and the page says them apart.
     *
     * @return list<array{label: string, value: string}>|null
     */
    private function scenarioEffectsFor(string $stat, ?string $scenarioKey): ?array
    {
        if ($scenarioKey === null) {
            return null;
        }

        $effects = [];

        foreach (self::SCENARIO_EFFECTS as $effect) {
            if ($effect['stats'] !== null && ! in_array($stat, $effect['stats'], true)) {
                continue;
            }

            $value = config('scenarios.scenarios.'.$scenarioKey.'.'.$effect['key']);

            if ($value === null) {
                continue;
            }

            $effects[] = [
                'label' => $effect['label'],
                'value' => is_bool($value) ? ($value ? 'Yes' : 'No') : (string) $value,
            ];
        }

        return $effects;
    }

    /**
     * The published per-level reference figures this discipline's card may print, read from the
     * scenario matrix's `training_tables` (the 2026-10-15 scenario-field-metadata artifact,
     * transcribed into `config/scenarios.php`).
     *
     * The level is the most recent one this run recorded for this discipline's facility, which is not
     * necessarily its newest turn: a race or a rest logs no ladder reading, and the level the client
     * showed two turns ago is still this turn's level. When no turn records one, the absence is named
     * rather than a Level-1 figure substituted: a Lv1 row shown for a Lv3 facility is the worse error,
     * so the card shows the absence instead.
     *
     * Every figure is `[JP]`-sourced and the server tag rides the payload: no source publishes a
     * Global per-level table, and `SCENARIO-PUBLISHER-REFERENCES.md` line 34 forbids quoting a JP
     * figure as a Global expectation. The tag is the surface's obligation to print, not to strip.
     *
     * Wit carries gains but no published cost: its table rows state a refund, not a cost, and the
     * advisor's own `wit_cost` constant — sourced and current — already governs that row. The risk
     * reason is the artifact's measured negative, verbatim, and is carried for the four Energy
     * disciplines only: Wit's card renders the engine's own `RiskNotMeasured` word instead.
     *
     * @param  array<string, int|null>  $facilityLevels
     * @return array{gains: array{level: int, text: string, server: string, source: string, notes: list<string>}|null, gains_absence: string|null, cost: array{level: int, value: int, text: string, server: string, source: string, notes: list<string>}|null, risk_reason: string|null}
     */
    private function publishedFor(string $stat, ?string $scenarioKey, array $facilityLevels): array
    {
        $riskReason = null;

        if ($scenarioKey !== null && $stat !== 'Wit') {
            $risk = config('scenarios.scenarios.'.$scenarioKey.'.risk_absence');
            $riskReason = is_array($risk) && is_string($risk['reason'] ?? null) ? $risk['reason'] : null;
        }

        if ($scenarioKey === null) {
            $absence = 'This run declares no scenario, so the published per-scenario training table has no row to read.';

            return ['gains' => null, 'gains_absence' => $absence, 'cost' => null, 'risk_reason' => null];
        }

        /** @var mixed $tables */
        $tables = config('scenarios.scenarios.'.$scenarioKey.'.training_tables');

        if (! is_array($tables) || ! is_array($tables['per_level'] ?? null)) {
            $absence = 'The '.$scenarioKey.' entry of config/scenarios.php records no published training table.';

            return ['gains' => null, 'gains_absence' => $absence, 'cost' => null, 'risk_reason' => $riskReason];
        }

        $level = $facilityLevels[$stat] ?? null;

        if ($level === null) {
            $absence = 'No facility level is recorded for this training on any turn this run has logged, so the published per-level table has no row to read; a Level-1 figure would state a level nobody entered.';

            return ['gains' => null, 'gains_absence' => $absence, 'cost' => null, 'risk_reason' => $riskReason];
        }

        /** @var mixed $row */
        $row = $tables['per_level'][$stat]['Lv'.$level] ?? null;

        if (! is_array($row)) {
            $absence = 'The published table states no row for this training at Level '.$level.'.';

            return ['gains' => null, 'gains_absence' => $absence, 'cost' => null, 'risk_reason' => $riskReason];
        }

        /** @var list<string> $notes */
        $notes = array_values(array_map(
            static fn (mixed $note): string => (string) $note,
            (array) ($tables['notes'] ?? []),
        ));

        $gains = [
            'level' => $level,
            'text' => $this->gainsText($stat, $row),
            'server' => (string) ($tables['server'] ?? 'JP'),
            'source' => (string) ($tables['source'] ?? ''),
            'notes' => $notes,
        ];

        // Wit's published cost is deliberately absent, absence line included: the Energy row keeps
        // the advisor's declared constant, which is the artifact's own cost value for Wit anyway.
        $cost = null;

        if ($stat !== 'Wit' && is_int($row['energy'] ?? null)) {
            $cost = [
                'level' => $level,
                'value' => $row['energy'],
                'text' => 'E'.strtr((string) $row['energy'], ['-' => "\u{2212}"]),
                'server' => (string) ($tables['server'] ?? 'JP'),
                'source' => (string) ($tables['source'] ?? ''),
                'notes' => $notes,
            ];
        }

        return ['gains' => $gains, 'gains_absence' => null, 'cost' => $cost, 'risk_reason' => $riskReason];
    }

    /**
     * The published row's gains as the words the card prints: primary, then the discipline's own
     * secondaries, then Skill Points. The routing is `SECONDARY_ROUTES`'s, and the artifact's own
     * key names decide which slot a value sits in.
     *
     * @param  array<string, mixed>  $row
     */
    private function gainsText(string $stat, array $row): string
    {
        $parts = ['+'.(string) $row['primary'].' '.$stat];

        foreach (self::SECONDARY_ROUTES[$stat] as $index => $route) {
            $key = $index === 0
                ? (array_key_exists('secondary', $row) ? 'secondary' : 'secondary_a')
                : 'secondary_b';

            if (is_int($row[$key] ?? null)) {
                $parts[] = '+'.(string) $row[$key].' '.$route;
            }
        }

        if (is_int($row['skill_points'] ?? null)) {
            $parts[] = '+'.(string) $row['skill_points'].' SP';
        }

        return implode(', ', $parts);
    }

    /**
     * The facility level per training, read from the newest turn that recorded one.
     *
     * A facility level is a state the client keeps showing, not an event that happens on one turn, and
     * the latest logged turn is often a race, a rest or a recreation that recorded none. Keying the
     * published table off that single row lost figures the run had already entered, so each stat walks
     * back on its own: a turn that read Speed but not Guts lifts only Speed. A stat no turn in the run
     * records stays null, which the page prints as the named absence rather than as Level 1.
     *
     * @return array<string, int|null>
     */
    private function facilityLevels(TrainingRun $run): array
    {
        /** @var array<string, int|null> $levels */
        $levels = array_fill_keys(array_keys(self::FACILITY_COLUMNS), null);

        foreach ($run->turnEntries->sortByDesc('turn') as $entry) {
            foreach (self::FACILITY_COLUMNS as $stat => $column) {
                if ($levels[$stat] === null && $entry->{$column} !== null) {
                    $levels[$stat] = (int) $entry->{$column};
                }
            }

            if (! in_array(null, $levels, true)) {
                break;
            }
        }

        return $levels;
    }

    /**
     * C2's answer, flattened to the three fields this screen renders. No score and no numeric
     * confidence, because the contract has no field for either (`ADR-0001` §3).
     *
     * @return array{action: string|null, band: string|null, absence: string|null, reason: string|null}
     */
    private function advisorSection(Advice $advice): array
    {
        return [
            'action' => $advice->recommendation?->action,
            'band' => $advice->band,
            'absence' => $advice->absence,
            // The recommendation's own source line, exposed so the page can print why the advisor said
            // what it said - including a line that names an Energy band rather than a figure.
            'reason' => $advice->recommendation?->reason,
        ];
    }

    /**
     * The guided turn write: the route and Form Request that already own it, plus the turn number the
     * next log lands on. The ceilings the inputs mirror are the options' own `cap`, so no second copy
     * of them travels here.
     *
     * `previous` is the latest logged row, offered as a placeholder and never as a value: an input
     * arriving pre-filled would assert the stat did not change, which is `D-220`'s rule and the reason
     * the run screen's rail passes it the same way.
     *
     * `extra_choices` are the two turn choices that are not trainings, Rest and Recreation. They ride on
     * the write rather than on `options` because `SCREEN_SPEC.md` SCR-CAR-012 rules them "turn choices,
     * not training options", and the five cards are the five. Their words come from `TurnChoice`, and
     * only the advisor's own action word marks one of them. The notes are the two lines the retired
     * rail already shipped (`TrainingRunController::turnChoices()`), moved rather than reworded: each
     * states what the client shows, not what this tool projects, since no source publishes a recovery
     * or an outing figure (`ADR-0001` §3).
     *
     * `outcomes` is every choice's outcome words keyed by the choice value the form posts, each word
     * carrying whether it names a failure. It rides on the write because the select that offers the words
     * and the request that accepts them must read one list (`TurnChoice::outcomes()`), and because the
     * penalty field has to appear for `Poor Rest` exactly as it does for `Failure`.
     *
     * @param  array<string, int|null>  $facilityLevels
     * @return array{action: string, turn: int, moods: list<string>, previous: array<string, int|null>|null, performance: array{enabled: bool, types: list<string>}, extra_choices: list<array{value: string, label: string, note: string, recommended: bool}>, outcomes: array<string, array<string, bool>>}
     */
    private function writeSection(TrainingRun $run, ?TurnEntry $latest, Advice $advice, array $facilityLevels): array
    {
        // The Performance observation pair (Our Grand Concert; the dated rail row in
        // `SCREEN_SPEC.md`). The five client words ride only behind the capability flag, so a
        // career whose scenario does not compose it gets no vocabulary for a control the page does
        // not draw. No starting value, total or cap travels with it: the pair records a change and
        // the corpus publishes none of the rest.
        $acceptsPerformance = $run->acceptsPerformanceObservations();

        $notes = [
            TurnChoice::Rest->value => 'Refills Energy. A poor rest refills less and can leave a stayed-up-late penalty.',
            TurnChoice::Recreation->value => 'Lifts Mood, and the outing may return a little Energy as well.',
        ];

        return [
            'action' => route('runs.turns.store', $run),
            'turn' => $run->nextTurnNumber(),
            'moods' => array_column(MoodTier::cases(), 'value'),
            'extra_choices' => array_map(
                static fn (TurnChoice $choice): array => [
                    'value' => $choice->value,
                    'label' => $choice->label(),
                    'note' => $notes[$choice->value],
                    'recommended' => $advice->recommendation?->action === $choice->label(),
                ],
                [TurnChoice::Rest, TurnChoice::Recreation],
            ),
            'outcomes' => $this->outcomeOptions(),
            'previous' => $latest === null ? null : [
                'speed' => $latest->speed,
                'stamina' => $latest->stamina,
                'power' => $latest->power,
                'guts' => $latest->guts,
                'wit' => $latest->wit,
                'sp' => $latest->sp,
                'energy' => $latest->energy,
                'fans' => $latest->fans,
                // The facility ladder this run last read, under the record form's own field names, so
                // the five inputs open on the state the client is still showing rather than on nothing.
                // The Trainer corrects a level the client has since moved; a blank still means no
                // reading was taken, and the published row then falls back to the walk above.
                'facility_speed' => $facilityLevels['Speed'],
                'facility_stamina' => $facilityLevels['Stamina'],
                'facility_power' => $facilityLevels['Power'],
                'facility_guts' => $facilityLevels['Guts'],
                'facility_wit' => $facilityLevels['Wit'],
            ],
            'performance' => [
                'enabled' => $acceptsPerformance,
                'types' => $acceptsPerformance
                    ? array_map(static fn (PerformanceType $type): string => $type->label(), PerformanceType::cases())
                    : [],
            ],
        ];
    }

    /**
     * Every choice's outcome words, with the failure flag each one carries, keyed by the stored choice.
     *
     * The list is `TurnChoice::outcomes()`'s and this only turns it inside out, so a word the Trainer can
     * pick and a word the request accepts cannot drift apart. No label is composed here: each word is the
     * label, which is why the map's keys are what the select prints.
     *
     * @return array<string, array<string, bool>>
     */
    private function outcomeOptions(): array
    {
        $outcomes = [];

        foreach (TurnChoice::cases() as $choice) {
            $outcomes[$choice->value] = $choice->outcomes();
        }

        return $outcomes;
    }

    /**
     * The run's Energy as a state, with the exact string the page prints.
     *
     * `Energy: 66` for an exact reading, `Energy: Low band` for a coarse one the client showed instead
     * of a number, and `Energy: Not recorded` for the absence. The label is built here so a reader of
     * the props sees the state the page states, not a value it has to re-render.
     *
     * @return array{state: string|null, band: string|null, value: int|null, label: string}
     */
    private function energySection(?TurnEntry $latest): array
    {
        $state = $latest?->energy_state;
        $band = $latest?->energy_band;
        $value = $latest?->energy;

        // The three-way resolution lives on the entry (`TurnEntry::energyReading()`) so this page and the
        // Cockpit and the event screen cannot state different readings of one turn (R3-01). The raw state,
        // band and number ride on unchanged: this screen's form prefills from them.
        $reading = $latest === null ? ['text' => null] : $latest->energyReading();
        $label = 'Energy: '.($reading['text'] ?? 'Not recorded');

        return [
            'state' => $state?->value,
            'band' => $band,
            'value' => $value,
            'label' => $label,
        ];
    }

    /**
     * The stats the decision plans against: the latest logged turn, or the snapshot's own imported
     * values when it has logged none.
     *
     * A snapshot that could not form a complete turn entry - it marked a core stat Unknown - still
     * carries the numbers it did read on its stored position. The advisor's contract is a `TurnEntry`,
     * so those values are handed over as a transient, never-saved row: the deficits the screen states
     * are then against what the Trainer read, not against nothing. A partial set returns null, because
     * the advisor must not rank against a stat nobody entered.
     */
    private function statsSource(TrainingRun $run): ?TurnEntry
    {
        $latest = $run->turnEntries->sortByDesc('turn')->first();

        if ($latest !== null) {
            return $latest;
        }

        $position = $run->career_position;

        if ($position === null || $position->fieldValues === null) {
            return null;
        }

        $values = $position->fieldValues;

        foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat) {
            if (! isset($values[$stat])) {
                return null;
            }
        }

        return new TurnEntry([
            'turn' => $position->turnIndex,
            'speed' => $values['speed'],
            'stamina' => $values['stamina'],
            'power' => $values['power'],
            'guts' => $values['guts'],
            'wit' => $values['wit'],
            'sp' => $values['skill_points'] ?? null,
            'energy' => $values['energy'] ?? null,
            'fans' => $values['fans'] ?? null,
        ]);
    }

    /**
     * The run's current stat values, keyed by matrix name, for the page's own read-back.
     *
     * @return array<string, int|null>
     */
    private function currentStats(?TurnEntry $latest): array
    {
        /** @var list<string> $order */
        $order = config('scenarios.stat_order');

        $stats = [];

        foreach ($order as $stat) {
            $stats[$stat] = $latest?->statValue($stat);
        }

        return $stats;
    }
}
