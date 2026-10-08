<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Http\Controllers\Controller;
use App\Http\Requests\Career\StartCareerRequest;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\CharacterCard;
use App\Models\DeckSlot;
use App\Models\Legacy\LegacySelectionPayload;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Services\Career\SetupDraft;
use App\Services\DataPipeline\ArtworkMirror;
use App\Services\DeckAnalysis;
use App\Services\Legacy\AncestryGraph;
use App\Services\SupportCardEffects;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

/**
 * Run Preflight, `SCR-CAR-010` (SCREEN-008, PRD FR-A-5, `ADR-0020` §1 and §3). Step 6 of the setup
 * wizard, and the only step that writes.
 *
 * **One contract, composed from the five entered steps.** The page re-asks nothing: every fact it prints
 * comes out of the session draft (`SetupDraft`), which the earlier steps filled, and every Edit link
 * returns to the step that owns the fact. The `contract` prop is a single typed object rather than a
 * dozen loose props, because Back and Edit round trips have to be lossless and a page that reads five
 * unrelated props cannot prove that.
 *
 * **The warnings are the derivable ones and no others.** Each is a restatement of entered data — a
 * section that is missing, a deck with an empty position, a target sitting on a Weak aptitude
 * (design-2.0 §17's own bands), a skill priority no source on the trainee, the deck or the ancestry
 * names. The brief's "weak stamina plan", "poor support synergy" and "missing scenario requirement" are
 * not among them: the first two need per-training yields and synergy weights this repository does not
 * hold (`PRD` §6.4, `ADR-0020` §3) and the third needs per-scenario requirement data that
 * `config/scenarios.php` does not carry. Warnings never block; only `StartCareerRequest` does.
 *
 * **`Start Career` creates the run once.** The write is one transaction and the draft is reset after it
 * commits, so a double submit cannot mint a second career from the same setup. The run is created
 * `Active` because that is what a career is, and the wizard collects no costume card, so no start skills
 * are seeded (the same shape `runs.import` has, and for the same reason: the Trainer's sheet names a
 * trainee, not which of her forms was equipped).
 */
class PreflightController extends Controller
{
    /**
     * `GET /career/setup/preflight`.
     */
    public function show(): Response
    {
        $draft = SetupDraft::read();
        $trainee = $draft['umamusume_id'] === null
            ? null
            : Umamusume::query()->find($draft['umamusume_id']);

        $target = $this->targetPayload();
        $legacy = $this->legacySection($trainee);
        $deck = $this->deckSection();
        $scenario = $this->scenarioSection();

        $build = [
            'trainee' => $trainee === null
                ? null
                : ['id' => $trainee->id, 'name' => $trainee->name, 'name_ja' => $trainee->name_ja],
            'scenario' => $scenario,
            'target' => $target === null ? null : [
                'purpose' => $target->purpose->value,
                'purpose_label' => $target->purpose->label(),
                'distance' => $target->distance,
                'surface' => $target->surface,
                'style' => $target->style,
                'targets' => $target->targets,
            ],
            'legacy' => $legacy,
        ];

        $complete = $build['trainee'] !== null
            && $build['scenario'] !== null
            && $build['target'] !== null
            && $build['legacy'] !== null
            && $deck !== null;

        return Inertia::render('Career/Preflight', [
            'step' => 6,
            'contract' => [
                'complete' => $complete,
                'build' => $build,
                'deck' => $deck,
                'target' => [
                    'stats' => $this->statRows($target),
                    'races' => [
                        'distance' => $target?->distance,
                        'surface' => $target?->surface,
                        'style' => $target?->style,
                    ],
                    'skills' => ['names' => $target->skillPriorities ?? []],
                ],
                'warnings' => $this->warnings($build, $deck, $target, $trainee),
                // design-2.0 §48: no source defines a Global ruleset version, so the snapshot states its
                // own absence (`HandleInertiaRequests` shares the null).
                'ruleset' => [
                    'label' => 'N/A',
                    'title' => 'No source defines a Global ruleset version, so this snapshot names the absence rather than a version.',
                ],
            ],
            'edit' => [
                'scenario' => route('career.scenario'),
                'trainee' => route('career.trainee'),
                'target' => route('career.target'),
                'legacy' => route('career.legacy'),
                'deck' => route('career.deck'),
            ],
            'startAction' => route('career.preflight.store'),
        ]);
    }

    /**
     * `PUT /career/setup/preflight` — `Start Career`.
     */
    public function store(StartCareerRequest $request): RedirectResponse
    {
        $run = DB::transaction(function () use ($request): TrainingRun {
            $run = TrainingRun::create($request->runAttributes());

            $ownership = $request->ownershipByPosition();

            foreach ($request->deckByPosition() as $position => $cardId) {
                $run->deckSlots()->create([
                    'support_card_id' => $cardId,
                    'slot_position' => $position,
                    // The flag the deck step recorded, carried onto the run (ADR-0023, D3).
                    'ownership' => $ownership[$position] ?? null,
                ]);
            }

            return $run;
        });

        SetupDraft::reset();

        // The Cockpit, not the run record screen (SCREEN-009, `SCR-CAR-011`): a career that has just
        // started lands on the screen it is read from every turn, and the record screen stays one
        // link away in the career bar. `CareerPreflightTest` pins the destination.
        return redirect()->route('runs.cockpit', $run)->with('status', 'Run created.');
    }

    /**
     * The draft's build target through its own reader, or null when it is absent or no longer readable.
     *
     * A stored payload the reader refuses is treated as no target rather than as a fatal: the reader's
     * refusal is a statement about the tool's vocabulary having changed under a saved draft, and the page's
     * answer to that is the same missing-section warning a target that was never entered gets.
     */
    private function targetPayload(): ?BuildTargetPayload
    {
        $stored = SetupDraft::buildTarget();

        if ($stored === null) {
            return null;
        }

        try {
            return BuildTargetPayload::fromArray($stored);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function scenarioSection(): ?array
    {
        $key = SetupDraft::read()['scenario'];

        if ($key === null) {
            return null;
        }

        /** @var array<string, mixed> $config */
        $config = (array) config('scenarios.scenarios.'.$key, []);

        return [
            'key' => $key,
            'label' => (string) ($config['label'] ?? $key),
            'focus' => isset($config['focus']) ? (string) $config['focus'] : null,
        ];
    }

    /**
     * The six-node ancestry and the entered Affinity, through the same builder the run screen and the
     * wizard's own step render from.
     *
     * @return array<string, mixed>|null
     */
    private function legacySection(?Umamusume $trainee): ?array
    {
        $stored = SetupDraft::legacySelection();

        if ($stored === null) {
            return null;
        }

        try {
            $payload = LegacySelectionPayload::fromArray($stored);
        } catch (InvalidArgumentException) {
            return null;
        }

        $parents = AncestryGraph::parentNames(SetupDraft::legacyParents());
        $graph = AncestryGraph::build($payload, $trainee?->name, $parents[0], $parents[1]);

        return [
            'affinity' => $payload->affinity,
            'members' => $graph['parents'],
        ];
    }

    /**
     * The six positions with the cards in them and the D6 analysis beside them, or null when the step was
     * never saved.
     *
     * @return array<string, mixed>|null
     */
    private function deckSection(): ?array
    {
        $draft = SetupDraft::deck();

        if ($draft === null) {
            return null;
        }

        $selected = array_values(array_filter(array_column($draft, 'support_card_id')));
        $cards = SupportCard::query()->whereIn('id', $selected)->get()->keyBy('id');
        $dictionary = SupportCardEffects::dictionary();

        $slots = array_map(
            function (array $slot) use ($cards, $dictionary): array {
                $card = $slot['support_card_id'] === null ? null : $cards->get((int) $slot['support_card_id']);

                return [
                    'position' => $slot['position'],
                    'label' => DeckSlot::positionLabel($slot['position']),
                    // The role belongs to the position: slot six is the friend slot whatever card sits
                    // there (`ADR-0014` correction 1).
                    'is_friend' => $slot['position'] === DeckSlot::MAX_POSITION,
                    'ownership' => $slot['ownership'],
                    'card' => $card === null ? null : [
                        'id' => $card->id,
                        'name' => $card->displayName(),
                        'type' => $card->type,
                        'type_label' => $card->typeLabel(),
                        'rarity_word' => $card->rarityWord(),
                        'artworkURL' => app(ArtworkMirror::class)->url('support_thumb', (int) $card->support_id),
                        'effects' => SupportCardEffects::atCap($card, $dictionary),
                    ],
                ];
            },
            $draft,
        );

        $equipped = $cards->values();

        return [
            'slots' => $slots,
            'equipped' => $equipped->count(),
            'analysis' => DeckAnalysis::build(DeckAnalysis::inputFor($equipped)),
        ];
    }

    /**
     * The five stats in the matrix's own order, with the number the Trainer entered beside each.
     *
     * @return list<array{key: string, label: string, value: int|null}>
     */
    private function statRows(?BuildTargetPayload $target): array
    {
        $rows = [];

        /** @var list<string> $order */
        $order = config('scenarios.stat_order');

        foreach ($order as $stat) {
            $rows[] = [
                'key' => $stat,
                'label' => $stat,
                'value' => $target?->targets[$stat] ?? null,
            ];
        }

        return $rows;
    }

    /**
     * The warnings the entered data supports, in a fixed order: the missing sections first, then the
     * filled ones' own gaps.
     *
     * @param  array<string, mixed>  $build
     * @param  array<string, mixed>|null  $deck
     * @return list<array{key: string, title: string, detail: string, href: string}>
     */
    private function warnings(array $build, ?array $deck, ?BuildTargetPayload $target, ?Umamusume $trainee): array
    {
        $warnings = [];

        // Keyed in the wizard's own step order, and the step number is stated rather than derived: the
        // warning tells a Trainer which of the six steps to open.
        $sections = [
            'scenario' => [1, 'Scenario is not chosen', 'No scenario is recorded in this setup.'],
            'trainee' => [2, 'Trainee is not chosen', 'No trainee is recorded in this setup.'],
            'target' => [3, 'Your target is not entered', 'No build target is recorded in this setup.'],
            'legacy' => [4, 'Ancestry is not entered', 'No two-parent ancestry is recorded in this setup.'],
            'deck' => [5, 'Support deck is not entered', 'No six-slot deck is recorded in this setup.'],
        ];

        $routes = [
            'scenario' => 'career.scenario',
            'trainee' => 'career.trainee',
            'target' => 'career.target',
            'legacy' => 'career.legacy',
            'deck' => 'career.deck',
        ];

        foreach ($sections as $key => [$step, $title, $detail]) {
            $present = $key === 'deck' ? $deck !== null : ($build[$key] ?? null) !== null;

            if (! $present) {
                $warnings[] = [
                    'key' => 'missing_section.'.$key,
                    'title' => $title,
                    'detail' => $detail.' Step '.$step.' enters it, and nothing is lost by going back.',
                    'href' => route($routes[$key]),
                ];
            }
        }

        if ($deck !== null) {
            /** @var int $equipped */
            $equipped = $deck['equipped'];
            $slots = (array) $deck['slots'];
            $empty = array_filter($slots, static fn (array $slot): bool => $slot['card'] === null);

            if ($equipped < DeckSlot::MAX_POSITION) {
                $warnings[] = [
                    'key' => 'deck_incomplete',
                    'title' => 'The deck is not full',
                    'detail' => $equipped.' of six positions carry a card; '
                        .count($empty).' are still empty. A career can start either way.',
                    'href' => route('career.deck'),
                ];
            }
        }

        $legacy = $build['legacy'] ?? null;

        if (is_array($legacy)) {
            foreach ((array) $legacy['members'] as $member) {
                if (($member['name'] ?? null) === null) {
                    $warnings[] = [
                        'key' => 'legacy_slot_empty.'.(string) $member['slot'],
                        'title' => $member['label'].' has no Legacy chosen',
                        'detail' => $member['label'].' carries no parent, so her half of the ancestry is empty.',
                        'href' => route('career.legacy'),
                    ];
                }
            }
        }

        foreach ($this->aptitudeWarnings($target, $trainee) as $warning) {
            $warnings[] = $warning;
        }

        foreach ($this->skillSourceWarnings($target, $trainee) as $warning) {
            $warnings[] = $warning;
        }

        return $warnings;
    }

    /**
     * A target band whose trainee aptitude is D or lower.
     *
     * The threshold is design-2.0 §17's own band table, not a number chosen here: S and A are Strong, B and
     * C Neutral, D and E Weak, F and G Very weak. The warning fires on Weak and below and says the letter
     * and the axis, so a Trainer can disagree with it and change either one.
     *
     * @return list<array{key: string, title: string, detail: string, href: string}>
     */
    private function aptitudeWarnings(?BuildTargetPayload $target, ?Umamusume $trainee): array
    {
        if ($target === null || $trainee === null) {
            return [];
        }

        $axes = [
            'distance' => [$target->distance, 'aptitude_'.strtolower($target->distance)],
            'surface' => [$target->surface, 'aptitude_'.strtolower($target->surface)],
            'style' => [$target->style, 'aptitude_'.str_replace(' ', '_', strtolower($target->style))],
        ];

        $warnings = [];

        foreach ($axes as $axis => [$band, $column]) {
            /** @var string|null $letter */
            $letter = $trainee->{$column};

            if ($letter === null || ! in_array(strtoupper($letter), ['D', 'E', 'F', 'G'], true)) {
                continue;
            }

            $warnings[] = [
                'key' => 'aptitude_below_target.'.$axis,
                'title' => 'Your target '.$band.' sits on a Weak aptitude',
                'detail' => 'Your target is '.$band.'. This trainee\'s '.$axis.' aptitude is '
                    .strtoupper($letter).', which design-2.0 §17 bands Weak or lower.',
                'href' => route('career.target'),
            ];
        }

        return $warnings;
    }

    /**
     * A skill priority that no consulted source names.
     *
     * The sources are the ones the tool actually holds: the six deck cards' hint and event skills, the
     * trainee's own cards' skill lists, and the Spark targets the Trainer recorded on the ancestry step.
     * The warning says which sources were read, so a Trainer whose priority lives on a card they have not
     * equipped sees a statement about the sources rather than a claim about the game.
     *
     * @return list<array{key: string, title: string, detail: string, href: string}>
     */
    private function skillSourceWarnings(?BuildTargetPayload $target, ?Umamusume $trainee): array
    {
        if ($target === null || $target->skillPriorities === []) {
            return [];
        }

        $sources = $this->sourcedSkillNames($trainee);

        $warnings = [];

        foreach ($target->skillPriorities as $name) {
            foreach ($sources as $source) {
                if (mb_strtolower($source) === mb_strtolower($name)) {
                    continue 2;
                }
            }

            $warnings[] = [
                'key' => 'skill_priority_unsourced.'.$name,
                'title' => $name.' has no source in this setup',
                'detail' => 'No source this setup holds names '.$name.': not the six deck cards\' hint and '
                    .'event skills, not the trainee\'s own cards\' skills, and not the Spark targets recorded '
                    .'on the ancestry step.',
                'href' => route('career.target'),
            ];
        }

        return $warnings;
    }

    /**
     * Every skill name the draft's own sources hold, for the priority check above.
     *
     * @return list<string>
     */
    private function sourcedSkillNames(?Umamusume $trainee): array
    {
        $names = [];

        $deck = SetupDraft::deck() ?? [];
        $cardIds = array_values(array_filter(array_column($deck, 'support_card_id')));

        if ($cardIds !== []) {
            $exportIds = [];

            foreach (SupportCard::query()->whereIn('id', $cardIds)->get() as $card) {
                foreach ([$card->hint_skills ?? [], $card->event_skills ?? []] as $list) {
                    foreach ((array) $list as $id) {
                        $exportIds[] = (int) $id;
                    }
                }
            }

            $names = array_merge($names, $this->skillNamesByExportId($exportIds));
        }

        if ($trainee !== null) {
            $exportIds = [];

            foreach (CharacterCard::query()->where('umamusume_id', $trainee->id)->get() as $card) {
                foreach ([$card->skills_innate ?? [], $card->skills_unique ?? [], $card->skills_awakening ?? [], $card->skills_event ?? []] as $list) {
                    foreach ((array) $list as $id) {
                        $exportIds[] = (int) $id;
                    }
                }
            }

            $names = array_merge($names, $this->skillNamesByExportId($exportIds));
        }

        $selection = SetupDraft::legacySelection();

        if ($selection !== null) {
            foreach ($selection['legacies'] as $legacy) {
                foreach ((array) ($legacy['sparks'] ?? []) as $spark) {
                    $sparkTarget = $spark['target'] ?? null;

                    if (is_string($sparkTarget) && $sparkTarget !== '') {
                        $names[] = $sparkTarget;
                    }
                }
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * @param  list<int>  $exportIds
     * @return list<string>
     */
    private function skillNamesByExportId(array $exportIds): array
    {
        if ($exportIds === []) {
            return [];
        }

        /** @var list<string> $names */
        $names = Skill::query()->whereIn('export_id', array_unique($exportIds))->pluck('name')->all();

        return $names;
    }
}
