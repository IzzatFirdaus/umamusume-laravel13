<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Enums\MoodTier;
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

    public function show(TrainingRun $run): Response
    {
        $run->load(['umamusume', 'turnEntries', 'deckSlots.supportCard']);

        $advisor = app(TrainerAdvisor::class);
        $latest = $run->turnEntries->sortByDesc('turn')->first();
        $advice = $advisor->advise($latest, $run->buildTarget());
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
                ),
                $order,
            ),
            'deck' => [
                'recorded' => $run->deckSlots->isNotEmpty(),
                'slots' => $run->deckSlots->count(),
            ],
            'advisor' => $this->advisorSection($advice),
            'write' => $this->writeSection($run, $latest),
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
            'run_url' => route('runs.show', $run),
            'cockpit_url' => route('runs.cockpit', $run),
        ];
    }

    /**
     * One card's whole payload, assembled from the four sources and nothing else.
     *
     * @param  array<string, array{value: mixed, source: string, verified_at: string, confidence: string}>  $constants
     * @param  Collection<int, DeckSlot>  $slots
     * @param  array<int, array{name: string, symbol: string|null, calc: string|null}>  $dictionary
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
    ): array {
        $option = $this->adviceOption($advice, $stat);

        return [
            'key' => $stat,
            'choice' => 'training-'.$stat,
            'recommended' => $advice->recommendation?->action === $stat,
            'current' => $this->currentStat($latest, $stat),
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
        return $slots
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
            ->all();
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
     * C2's answer, flattened to the three fields this screen renders. No score and no numeric
     * confidence, because the contract has no field for either (`ADR-0001` §3).
     *
     * @return array{action: string|null, band: string|null, absence: string|null}
     */
    private function advisorSection(Advice $advice): array
    {
        return [
            'action' => $advice->recommendation?->action,
            'band' => $advice->band,
            'absence' => $advice->absence,
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
     * @return array{action: string, turn: int, moods: list<string>, previous: array<string, int|null>|null}
     */
    private function writeSection(TrainingRun $run, ?TurnEntry $latest): array
    {
        return [
            'action' => route('runs.turns.store', $run),
            'turn' => $run->nextTurnNumber(),
            'moods' => array_column(MoodTier::cases(), 'value'),
            'previous' => $latest === null ? null : [
                'speed' => $latest->speed,
                'stamina' => $latest->stamina,
                'power' => $latest->power,
                'guts' => $latest->guts,
                'wit' => $latest->wit,
                'sp' => $latest->sp,
                'energy' => $latest->energy,
                'fans' => $latest->fans,
            ],
        ];
    }

    /**
     * ponytail: `CockpitController::currentStat()`'s body, kept private to each controller rather than
     * extracted for two callers (the third reading of the same map is `TrainerAdvisor::current()`, which
     * throws rather than returning null). Upgrade path: one accessor on `TurnEntry` when a third career
     * screen reads a stat by matrix name.
     */
    private function currentStat(?TurnEntry $latest, string $stat): ?int
    {
        if ($latest === null) {
            return null;
        }

        return match ($stat) {
            'Speed' => $latest->speed,
            'Stamina' => $latest->stamina,
            'Power' => $latest->power,
            'Guts' => $latest->guts,
            'Wit' => $latest->wit,
            default => null,
        };
    }
}
