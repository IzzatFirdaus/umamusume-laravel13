<?php

declare(strict_types=1);

namespace App\Services\Career;

use App\Enums\RaceEntryStatus;
use App\Enums\TurnEventType;
use App\Models\RaceCatalogSlot;
use App\Models\RaceEntry;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Models\TurnEvent;
use Illuminate\Support\Collection;

/**
 * The Trackblazer panel's five sections and their nested surfaces, extracted from
 * `CockpitController::gradeSection()`, `::shopSection()`, `::epithetSection()` and `::rivalSection()`
 * (plan §3.1a, F1 4.2).
 *
 * **One service, one scenario's arm.** Every method here answers `null` for any scenario whose matrix
 * does not turn the matching flag on, exactly as the controller arm did, so the section's key set
 * stays uniform across the four Global scenarios and `ScenarioPanelTest`'s exhaustive pin holds.
 * The renderer still reads these never by a scenario identity field (gate G-33): the branch inside
 * each method is the matrix's `composesPanel()` / `scenarioKey()`, not a name passed in.
 *
 * **The planning surfaces nest under grade, not the top level.** `planning`, `calendar` and
 * `fatigue` are readings of the grade ladder, which is why `gradeSection()` composes them rather
 * than the caller: the section's own shape is what `scenarioSectionKeys()` pins, and splitting them
 * across a builder would be a second shape for one contract. `longestEnteredRaceChain()` moves with
 * `planningSection()` because planning is its only caller.
 *
 * **The shop's ledger nests the same way.** `ledgerSection()` and `trackblazerPurchases()` are shop
 * facts and were never read from outside `shopSection()`, so they travel here rather than staying
 * on the controller; `TrackblazerPanelTest`'s exhaustive key pin stays true because the payload is
 * unchanged by the move.
 *
 * **Nothing here predicts.** No readiness band, no win figure, no percentage, and no race fact
 * beyond the stored title and tier (`ADR-0016`, `ADR-0020` §3). Each section's own docblock, moved
 * with its body, states that at length.
 */
final class TrackblazerPanelBuilder
{
    /**
     * The grade-point meter and its three planning surfaces (plan §9 E4, G5).
     *
     * Returns null for any scenario that is not Trackblazer, so the `grade` key is the arm's own and
     * a scenario with no grade ladder draws nothing rather than an empty one.
     *
     * The ladder row count is 4, debut + three end-of-year rows from
     * `config('scenarios.scenarios.trackblazer.grade_objectives.standard')`. The dirt-leaning and
     * limited-turf-range tracks the matrix also carries are not the rendered track, because no column
     * tells the renderer which trainee belongs where (KI-15).
     *
     * @return array<string, mixed>|null
     */
    public function gradeSection(TrainingRun $run, ?TurnEntry $latest): ?array
    {
        if ($run->scenarioKey() !== 'trackblazer') {
            return null;
        }

        return [
            'objectives' => $run->gradeObjectives(),
            'current' => $run->current_objective_index,
            'earned' => $run->gradeEarned(),
            'unpriced_count' => $run->gradeUnpricedCount(),
            'unassigned_count' => $run->gradeUnassignedCount(),
            'periods' => $run->gradePeriods(),
            'ladder_source' => 'standard',
            'rotation_turns' => (int) (config('scenarios.scenarios.trackblazer.shop.rotation_turns') ?? 0) ?: null,
            'rotation_resets_in' => $run->shop_resets_in,
            // G5's three planning surfaces. They nest here rather than joining the top-level section
            // because they are readings of the grade ladder, and the section's own shape is what
            // `scenarioSectionKeys()` pins (gate G-33: the matrix, not a scenario name, opens them).
            'planning' => $this->planningSection($run, $latest),
            'calendar' => $this->calendarSection($run),
            'fatigue' => $this->fatigueSection($run),
        ];
    }

    /**
     * The route planner's runway (plan §6): for each end-of-year period, what it asks for, what the
     * races this run has entered would pay if every one of them were won, and what those same races
     * still pay at 2nd and 3rd place.
     *
     * No row here is an outcome. The first figure is config, the second is the Trainer's own entered
     * `race_entries` rows at status `Entered` priced through `TrainingRun::gradePointsForTier()`, and
     * the floors are that second figure scaled by the published modifiers. `ADR-0016` holds the
     * readiness-scoring shape and `PRD.md` §6.11 keeps prediction out, so the surface states what a
     * placement is worth and never what this trainee will place.
     *
     * The entered races the published grade table does not price are counted out and the count is
     * printed: a total that silently omits them would understate itself, which is the same refusal
     * `gradeUnpricedFor()` makes about finished races.
     *
     * @return array<string, mixed>
     */
    private function planningSection(TrainingRun $run, ?TurnEntry $latest): array
    {
        /** @var array<string, mixed> $def */
        $def = (array) (config('scenarios.scenarios.trackblazer') ?? []);
        /** @var array<string, int> $modifiers */
        $modifiers = (array) ($def['grade_point_placement_modifiers'] ?? []);
        /** @var array<string, mixed> $planning */
        $planning = (array) ($def['planning'] ?? []);
        /** @var list<string> $floorPlacements */
        $floorPlacements = (array) ($planning['floor_placements'] ?? []);
        $threshold = (int) ($planning['density_threshold'] ?? 0);

        $entries = $run->raceEntries;
        $entries->loadMissing('raceCatalogSlot');

        /** @var array<int, int> $planByPeriod */
        $planByPeriod = [];
        /** @var array<int, int> $uncounted */
        $uncounted = [];
        $unattributed = 0;

        foreach ($entries as $entry) {
            if ($entry->status !== RaceEntryStatus::Entered) {
                continue;
            }

            if ($entry->objective_index === null) {
                $unattributed++;

                continue;
            }

            $tier = $entry->tierKey();
            $price = $tier === null ? null : $run->gradePointsForTier($tier);

            if ($price === null) {
                $uncounted[$entry->objective_index] = ($uncounted[$entry->objective_index] ?? 0) + 1;

                continue;
            }

            $planByPeriod[$entry->objective_index] = ($planByPeriod[$entry->objective_index] ?? 0) + $price;
        }

        $secondLabel = $floorPlacements[0] ?? '';
        $thirdLabel = $floorPlacements[1] ?? '';
        $secondModifier = (int) ($modifiers[$secondLabel] ?? 0);
        $thirdModifier = (int) ($modifiers[$thirdLabel] ?? 0);

        $floorTitle = 'The two figures a plan still pays at the placements this row names: the total '
            .$secondLabel.' ('.$secondModifier.'% of a win) and the total at '.$thirdLabel.' ('
            .$thirdModifier.'% of a win). Both are arithmetic on the published modifiers in '
            .'config(\'scenarios.scenarios.trackblazer.grade_point_placement_modifiers\'), which is '
            .'uma.guide\'s table at docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 104-112 '
            .'(the page the master dates 2026-04-29). It is a lower bound at a stated placement, not a '
            .'guess at a result, and no earned total in this tool reads these modifiers: the other '
            .'publisher in the same master names no ratio (line 355), which is why '
            .'`RaceEntry::gradePointsFor()` still prices a 1st place alone.';

        $current = $run->current_objective_index;

        $periods = [];

        foreach ($run->gradeObjectives() as $objective) {
            $index = (int) $objective['index'];

            // The debut row asks for no points, so it has no runway to plan.
            if ($index < 2) {
                continue;
            }

            $plan = $planByPeriod[$index] ?? null;

            $periods[] = [
                'index' => $index,
                'name' => (string) $objective['name'],
                'needed' => number_format((int) $objective['required']),
                'needed_badge' => 'confirmed',
                'needed_title' => 'The standard track in config(\'scenarios.scenarios.trackblazer.grade_objectives\'), '
                    .'transcribed from docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 84-89 '
                    .'and 342-349. Only the standard track is printed: no column says which trainee belongs '
                    .'on the dirt-leaning or limited-turf-range one, which is KI-15.',
                'is_current' => $current === $index,
                'plan' => $plan === null ? null : number_format($plan),
                'plan_badge' => $plan === null ? null : 'calculated',
                'plan_title' => $plan === null
                    ? 'No race is entered against this period yet, so there is no plan to total. The Trainer '
                        .'enters a race from the Race Decision screen and names the period it is for.'
                    : 'The sum of the entered races attributed to this period, each at what a 1st place pays '
                        .'for its grade in config(\'scenarios.scenarios.trackblazer.grade_point_by_grade\') '
                        .'(docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 94-102). A race that has '
                        .'already been run is history, not plan, and is left out.',
                'uncounted' => $uncounted[$index] ?? 0,
                'uncounted_reason' => ($uncounted[$index] ?? 0) > 0
                    ? 'An entered race whose grade no published table prices is left out of that total rather '
                        .'than priced at a guess.'
                    : null,
                'uncounted_title' => 'How many entered races in this period have no published Grade Point value '
                    .'for their grade, the same wording the meter above uses for a finished race. They are left '
                    .'out of the total rather than guessed at, and a total that silently omitted them would '
                    .'understate the plan.',
                'floor_second' => $plan === null ? null : number_format(intdiv($plan * $secondModifier, 100)),
                'floor_third' => $plan === null ? null : number_format(intdiv($plan * $thirdModifier, 100)),
                'floor_label' => $secondLabel.' / '.$thirdLabel,
                'floor_badge' => $plan === null ? null : 'calculated',
                'floor_title' => $floorTitle,
            ];
        }

        /** @var array<string, mixed> $rivals */
        $rivals = (array) ($def['rival_races'] ?? []);
        /** @var list<array<string, mixed>> $gates */
        $gates = (array) ($def['year_end_gates'] ?? []);
        $gate = $current === null ? null : ($gates[$current - 2] ?? null);
        $fans = $latest?->fans;

        $yearEnd = null;

        if (is_array($gate)) {
            $needed = (int) $gate['fans'];
            $shortfall = $fans === null ? null : max(0, $needed - (int) $fans);

            $yearEnd = [
                'gate_label' => (string) $gate['label'],
                'timing' => (string) $gate['timing'],
                'fans_needed' => number_format($needed),
                'fans_needed_badge' => 'confirmed',
                'fans_needed_title' => 'The fan figure two publishers in this master agree on: '
                    .'docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 306-310 (uma.guide) and the '
                    .'GameWith row at line 1235 both print 5,000, 60,000 and 120,000 at the three assessments.',
                'fans_recorded' => $fans === null ? null : number_format((int) $fans),
                'fans_recorded_badge' => $fans === null ? null : 'confirmed',
                'fans_recorded_title' => $fans === null
                    ? 'No turn entry for this run records a fan count, so the reading is unknown rather than zero.'
                    : 'The fan count on the run\'s most recent turn entry, the same reading the header and the '
                        .'fan ladder use.',
                'fans_shortfall' => $shortfall === null ? null : number_format($shortfall),
                'fans_shortfall_badge' => $shortfall === null ? null : 'calculated',
                'fans_shortfall_title' => 'The published figure minus the recorded reading, floored at zero. '
                    .'Nothing about the assessment itself is estimated: it is the gap between two figures this '
                    .'run has, not a forecast of how it will place.',
                'bond_figures' => implode(' / ', array_map(
                    static fn (array $row): string => (string) $row['bond_uma_guide'],
                    $gates
                )).' (uma.guide) · '.implode(' / ', array_map(
                    static fn (array $row): string => (string) $row['bond_game_with'],
                    $gates
                )).' (GameWith)',
                'bond_recorded' => null,
                'bond_title' => 'The Director\'s bond level has no column: `turn_events` stores an unattributed '
                    .'bond delta with no starting value, so this run\'s bond reading cannot be taken and the '
                    .'comparison is not printed. The two figures above are both published and neither settles '
                    .'the other: docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 306-310 against '
                    .'the GameWith row at 1235, which docs/Trackblazer-plan.md:361 rules only as single-source.',
            ];
        }

        $flags = [];
        $longest = $this->longestEnteredRaceChain($entries);

        if ($threshold > 0 && $longest >= $threshold) {
            $flags[] = [
                'kind' => 'race_density',
                'label' => $longest.' consecutive races with no rest turn between them (fatigue risk)',
                'badge' => 'calculated',
                'title' => 'Counted over the races this run has entered that point at a catalogue row, on '
                    .'consecutive turns of the career grid. It reads the entered plan only: what the client '
                    .'offers on a turn this run has not entered is not a race the Trainer chose, and the '
                    .'threshold is config(\'scenarios.scenarios.trackblazer.planning.density_threshold\'). '
                    .'Fatigue keys on this same length, which is why the flag names it.',
            ];
        }

        if ($yearEnd !== null) {
            $flags[] = [
                'kind' => 'year_end_gate',
                'label' => 'Year-end gate, '.$yearEnd['gate_label'].': '.$yearEnd['fans_needed']
                    .' fans and the Director\'s bond',
                'badge' => 'confirmed',
                'title' => 'The Late December assessment that levels up the Unique Skill. Its fan figure is '
                    .'published by two sources and its bond figure by two disagreeing ones; the bond half of '
                    .'this gate has no reading to compare against.',
            ];
        }

        $flags[] = [
            'kind' => 'rival_races',
            'label' => 'Rival races cannot be scheduled',
            'badge' => 'confirmed',
            'title' => 'They appear on their own: '
                .(string) ($rivals['first_offered'] ?? 'not recorded in this corpus')
                .' and at random after that (docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md line 194). '
                .'A plan that listed one would be a plan for something the client has not offered, so no row '
                .'of this surface claims a rival race.',
        ];

        return [
            'periods' => $periods,
            // Prominent while the first checkpoint is still ahead of the Trainer; folded to the
            // periods that still have a plan to make once it is behind them.
            'collapses_after_period' => (int) ($planning['collapses_after_period'] ?? 0),
            'flags' => $flags,
            'year_end' => $yearEnd,
            'unattributed' => $unattributed,
            'unattributed_title' => 'Entered races this run has not attributed to a period. They count toward '
                .'no runway row above, and the grade meter prints the same figure as "unassigned".',
            'routes_pointer' => 'Route progress is the Epithet routes surface\'s own list, printed there once.',
        ];
    }

    /**
     * The longest run of entered races on consecutive turns of the career grid.
     *
     * A career year is a fixed number of turns, so one integer orders the two the same way
     * `RaceCatalogSlot::isAtOrBeforeTurn()` does, and the Late December into Early January step counts
     * as consecutive because on the grid it is.
     *
     * @param  Collection<int, RaceEntry>  $entries
     */
    private function longestEnteredRaceChain(Collection $entries): int
    {
        $positions = [];

        foreach ($entries as $entry) {
            if ($entry->status !== RaceEntryStatus::Entered) {
                continue;
            }

            $slot = $entry->raceCatalogSlot;

            if ($slot === null || $slot->turn === null) {
                continue;
            }

            $positions[(int) $slot->year.'-'.(int) $slot->turn] =
                (int) $slot->year * TrainingRun::TURNS_PER_YEAR + (int) $slot->turn;
        }

        $positions = array_values($positions);
        sort($positions);

        $longest = 0;
        $streak = 0;
        $previous = null;

        foreach ($positions as $position) {
            $streak = $previous === $position - 1 ? $streak + 1 : 1;
            $longest = max($longest, $streak);
            $previous = $position;
        }

        return $longest;
    }

    /**
     * The three races this run has coming up, with what each pays and what skipping it costs
     * (plan §17).
     *
     * This is not the rival block above. That one lists the scenario's catalogue as a reference and
     * caps at eight rows; this one is windowed against the turn the run is actually deciding, drops
     * rows the run already entered, and carries the two figures a race is worth. The matrix keeps
     * `race_calendar` off for Trackblazer (`config/scenarios.php`), so `raceStripSection()` prints its
     * "ahead" region for the scenarios that do compose a calendar and nothing here — this surface is
     * the only place a Trackblazer run sees what is still to come.
     *
     * The values are published rules, not this run's prospects: a win at G2 pays 80 whether or not
     * this trainee can win it, which is exactly why no row here compares her aptitudes to the race
     * (`ADR-0016`, `PRD.md` §6.11).
     *
     * @return array<string, mixed>
     */
    private function calendarSection(TrainingRun $run): array
    {
        /** @var array<string, mixed> $def */
        $def = (array) (config('scenarios.scenarios.trackblazer') ?? []);
        /** @var array<string, int> $gradePoints */
        $gradePoints = (array) ($def['grade_point_by_grade'] ?? []);
        /** @var array<string, int> $coins */
        $coins = (array) ($def['shop_coins_by_placement'] ?? []);
        /** @var array<string, mixed> $planning */
        $planning = (array) ($def['planning'] ?? []);
        /** @var list<array<string, mixed>> $routes */
        $routes = (array) ($def['epithet_routes'] ?? []);
        $window = (int) ($planning['calendar_window'] ?? 0);

        $next = $run->nextTurnToPlay();
        $enteredIds = $run->raceEntries->pluck('race_catalog_slot_id')->filter();

        /** @var Collection<int, RaceCatalogSlot> $ahead */
        $ahead = RaceCatalogSlot::query()
            ->forScenario($run->scenarioKey())
            ->orderBy('year')
            ->orderBy('turn')
            ->orderBy('sort_order')
            ->get()
            ->reject(fn (RaceCatalogSlot $slot): bool => $slot->isAtOrBeforeTurn($next)
                || $enteredIds->contains($slot->id));

        $firstCoin = $coins['1st'] ?? null;
        $now = $next === null ? null : $next['year'] * TrainingRun::TURNS_PER_YEAR + $next['turn'];

        $rows = $next === null ? [] : $ahead->take($window)
            ->map(function (RaceCatalogSlot $slot) use ($gradePoints, $firstCoin, $routes, $now): array {
                $gradePointsWin = $slot->tier === null ? null : ($gradePoints[$slot->tier] ?? null);

                $epithets = array_values(array_filter(array_map(
                    static fn (array $row): ?string => in_array($slot->title, (array) ($row['races'] ?? []), true)
                        ? (string) $row['epithet']
                        : null,
                    $routes
                )));

                $forgone = [];

                if ($gradePointsWin !== null) {
                    $forgone[] = 'up to '.number_format($gradePointsWin).' Grade Points';
                }

                if ($firstCoin !== null) {
                    $forgone[] = number_format($firstCoin).' Shop Coins';
                }

                $consequence = 'Skipping this race forgoes '.($forgone === []
                    ? 'the fan gain and a turn of racing, which is all this row can say'
                    : implode(' and ', $forgone))
                    .($epithets === []
                        ? '.'
                        : ', and leaves '.$slot->title.' unclaimed for '.implode(', ', $epithets).'.');

                return [
                    'title' => $slot->title,
                    'tier' => $slot->tier,
                    'year_label' => $slot->yearLabel(),
                    'turn' => $slot->turnNumber(),
                    'turns_away' => $now === null || $slot->turn === null
                        ? null
                        : (string) ((int) $slot->year * TrainingRun::TURNS_PER_YEAR + (int) $slot->turn - $now),
                    'distance' => $slot->distanceLabel(),
                    'surface' => $slot->surface,
                    'surface_title' => $slot->surface === null
                        ? 'The catalogue row this race is stored on carries no surface, so none is printed.'
                        : 'Metres and surface are the catalogue row\'s own columns, printed as stored.',
                    'grade_points_win' => $gradePointsWin === null ? null : number_format($gradePointsWin),
                    'grade_points_badge' => $gradePointsWin === null ? null : 'confirmed',
                    'grade_points_title' => $gradePointsWin === null
                        ? 'No published Grade Point value prices this grade, so no figure is printed for it. '
                            .'A race with no tier is common: the catalogue keeps a grade only where a source '
                            .'corroborated it per row (R75).'
                        : 'What a 1st place at this grade pays, from '
                            .'config(\'scenarios.scenarios.trackblazer.grade_point_by_grade\'), transcribed from '
                            .'docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 94-102 and 357-363.',
                    'coins_win' => $firstCoin === null ? null : number_format($firstCoin),
                    'coins_badge' => $firstCoin === null ? null : 'confirmed',
                    'coins_title' => 'Shop Coins are paid by placement alone: the same master says at line 372 '
                        .'that a Pre-OP win and a G1 win both pay 100 for 1st place, so this figure does not '
                        .'move with the grade above. The plan\'s illustrative §17 rows print different coin '
                        .'figures per grade; the published table wins.',
                    'skip_consequence' => $consequence,
                    'skip_badge' => 'calculated',
                    'skip_title' => 'The published figures a race that is not run cannot pay, added to this row. '
                        .'"Up to" is because a 1st place is the only placement the grade table prices: '
                        .'what a 4th place would have paid is the gap KI-10 leaves open.',
                ];
            })
            ->values()
            ->all();

        return [
            'window' => $window,
            'turns' => $next === null ? null : [
                'year' => $next['year'],
                'year_label' => RaceCatalogSlot::YEARS[$next['year']] ?? (string) $next['year'],
                'turn' => $next['turn'],
            ],
            'rows' => $rows,
            'absence' => match (true) {
                $next === null => 'This run has no turn being decided, so nothing can be placed ahead of it. '
                    .'A career with every turn logged has no next turn on the grid.',
                $rows === [] => 'No race is left ahead of turn '.$next['turn'].' on this scenario\'s calendar that '
                    .'this run has not already entered. The catalogue a run reads is the wizard\'s optional seed '
                    .'set, so an empty list here is a local database with no rows rather than a season with no '
                    .'races (R39).',
                default => null,
            },
        ];
    }

    /**
     * The race-fatigue reading (plan §10): the chain the Trainer reported, the row the publisher
     * labels with it, the two caveats that row leaves open, and the fixed recovery events.
     *
     * The chain length is entered, never derived. `TrainingRun::consecutiveRaceCount()` returns null
     * by design: a turn with no race logged is "the Trainer has not named this turn a race", which is
     * not the same statement as "this turn rested" (D-270). So an unreported chain prints its reason
     * and no row is read.
     *
     * The caveats are display copy rather than a tooltip because a caveat a Trainer has to hover for
     * is a caveat they never read.
     *
     * @return array<string, mixed>
     */
    private function fatigueSection(TrainingRun $run): array
    {
        /** @var array<string, mixed> $fatigue */
        $fatigue = (array) (config('scenarios.scenarios.trackblazer.race_fatigue') ?? []);
        /** @var list<string> $labels */
        $labels = (array) ($fatigue['chain_labels'] ?? []);
        /** @var array<string, array<string, string>> $odds */
        $odds = (array) ($fatigue['odds'] ?? []);

        $reported = $run->latestFatigue();
        $chain = $reported?->consecutiveRaces;
        $label = $chain === null ? null : ($labels[min($chain, 4) - 1] ?? null);

        return [
            'chain' => $chain,
            'chain_label' => $label,
            'chain_badge' => $chain === null ? null : 'confirmed',
            'chain_title' => $chain === null
                ? 'The Trainer has not entered a consecutive-race count on any turn of this run, so no chain '
                    .'length is shown and no row is read. The count is entered on the turn it applies to; a turn '
                    .'with no race logged is not a rest this tool was told about.'
                : 'The count the Trainer reported on a `turn_events` row of this run. Nothing here derives it: '
                    .'`TrainingRun::consecutiveRaceCount()` returns null rather than reading a rest off an '
                    .'unlogged turn.',
            'row' => $label === null ? null : ($odds[$label] ?? null),
            'row_title' => $label === null
                ? 'No chain, so no row. The table prints in full below rather than one column being guessed at.'
                : 'The row this publisher labels '.$label.'. Odds are published only by uma.guide '
                    .'(docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md lines 231-236, a page the master '
                    .'dates 2026-04-29); the other Trackblazer section in the same master names no odds, so '
                    .'one source is not two.',
            'table' => $odds,
            'caveats' => array_values((array) ($fatigue['caveats'] ?? [])),
            'recovery' => array_values((array) ($fatigue['recovery_events'] ?? [])),
            'recovery_badge' => 'confirmed',
            'recovery_title' => 'Guaranteed Energy and Mood events, from '
                .(string) ($fatigue['recovery_source'] ?? 'the publisher master'),
        ];
    }

    /**
     * The Trackblazer Pro Shop (plan §9 E4, `x-shop-panel` provenance).
     *
     * The catalogue renders in `shop_items` order — verbatim from config, transcribed from
     * `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` section "Full Shop Item List".
     * No `recommended` field exists: the rotation is not modelled (plan §3 knowledge
     * groundings), and a "best value" sort or default selection would state a state of a
     * lineup this tool cannot see (D-256).
     *
     * Purchases are events, not a column (`ShopPurchasePayload` shape pinned in §3 + ADR-0003).
     * `fill` and `select_action` are the existing `runs.purchases.store` and `runs.update`
     * routes — the Vue form posts to them and `StoreShopPurchaseRequest` is the boundary.
     *
     * @return array<string, mixed>|null
     */
    public function shopSection(TrainingRun $run): ?array
    {
        if ($run->scenarioKey() !== 'trackblazer') {
            return null;
        }

        $def = (array) (config('scenarios.scenarios.trackblazer.shop') ?? []);

        $catalogue = collect((array) config('scenarios.scenarios.trackblazer.shop_items'))
            ->map(fn (array $row): array => [
                'name' => (string) $row['name'],
                'cost' => (int) ($row['cost'] ?? 0),
                'effect' => (string) ($row['effect'] ?? ''),
            ])
            ->values()
            ->all();

        $purchases = $this->trackblazerPurchases($run);

        return [
            'catalogue' => $catalogue,
            'purchases' => $purchases,
            'rotation_turns' => (int) ($def['rotation_turns'] ?? 0) ?: null,
            'rotation_resets_in' => $run->shop_resets_in,
            'max_copies' => (int) ($def['max_copies_per_item'] ?? 0),
            'spend_total' => $run->composesShop() ? $run->shopSpendTotal() : 0,
            'fill' => route('runs.purchases.store', $run),
            'select_action' => route('runs.update', $run),
            // G6's ledger. It nests under `shop` because these are shop facts, and the exhaustive key
            // assertion in `TrackblazerPanelTest` is updated in the same change so the contract move is
            // test-visible rather than silent.
            'ledger' => $this->ledgerSection($run, $purchases),
        ];
    }

    /**
     * The shop ledger (G6): what this run holds, the cap each holding sits against, the one publisher's
     * worth ratings, and its Summer reserve.
     *
     * **What it does not print, on purpose.** The catalogue, the purchases list, the purchase form and
     * the spend total all render in this arm already, and so does the coin balance's absence ("Shop
     * Coins: not yet recorded"). A ledger that repeated any of them would be the second surface for one
     * fact that correction 27 exists to prevent, so it carries only what the arm lacks: a held count per
     * item, the cap, the publisher's ratings, and an inflow projection.
     *
     * **Nothing here is this tool's advice.** The ratings and the reserve are uma.guide's own words
     * (config `shop_worth`, `shop_reserve`, each citing the master), they travel as `estimated`, and the
     * arm's sentence "no row is recommended" stays true: the tool recommends nothing and reports what one
     * publisher said.
     *
     * **No projected cost.** Nothing writes a planned purchase, so a cost projection would be invented;
     * it is reported as an owed writer and the row is absent.
     *
     * @param  list<array{item: string, cost: int, effect: string, turn: int}>  $purchases
     * @return array<string, mixed>
     */
    private function ledgerSection(TrainingRun $run, array $purchases): array
    {
        /** @var array<string, mixed> $def */
        $def = (array) (config('scenarios.scenarios.trackblazer') ?? []);
        /** @var array<string, mixed> $shop */
        $shop = (array) ($def['shop'] ?? []);
        /** @var array<string, string> $worth */
        $worth = (array) ($def['shop_worth'] ?? []);
        /** @var array<string, mixed> $reserve */
        $reserve = (array) ($def['shop_reserve'] ?? []);
        /** @var array<string, int> $coinTable */
        $coinTable = (array) ($def['shop_coins_by_placement'] ?? []);

        $cap = (int) ($shop['max_copies_per_item'] ?? 0);
        $coinWin = (int) ($coinTable['1st'] ?? 0);

        /** @var array<string, int> $held */
        $held = [];

        foreach ($purchases as $purchase) {
            $held[$purchase['item']] = ($held[$purchase['item']] ?? 0) + 1;
        }

        // Catalogue order, so the list does not reshuffle as purchases land.
        $items = [];

        foreach ((array) config('scenarios.scenarios.trackblazer.shop_items') as $row) {
            $name = (string) $row['name'];

            if (! isset($held[$name])) {
                continue;
            }

            $items[] = [
                'name' => $name,
                'held' => $held[$name],
                'cap' => $cap,
                'worth' => $worth[$name] ?? null,
                'worth_badge' => isset($worth[$name]) ? 'estimated' : null,
                'worth_title' => isset($worth[$name])
                    ? 'One publisher\'s rating rather than a game rule: '.(string) ($def['shop_worth_source'] ?? '')
                    : 'The publisher does not rate this item, so no rating is printed rather than one inferred '
                        .'from a nearby row.',
            ];
        }

        $planned = $run->raceEntries
            ->filter(fn (RaceEntry $entry): bool => $entry->status === RaceEntryStatus::Entered)
            ->count();

        return [
            'items' => $items,
            'items_absence' => $items === []
                ? 'No purchase is recorded for this run yet, so nothing is held. Record one from the form above.'
                : null,
            'cap' => $cap,
            'inflow' => $planned === 0 ? null : number_format($planned * $coinWin),
            'inflow_badge' => $planned === 0 ? null : 'calculated',
            'inflow_title' => $planned === 0
                ? 'No race is entered for this run yet, so there is nothing to project an inflow from. The '
                    .'plan above shows the races the Trainer has entered.'
                : 'The races this run has entered, each at the published 1st-place payout of '
                    .number_format($coinWin).' coins from config(\'scenarios.scenarios.trackblazer.'
                    .'shop_coins_by_placement\'), which the master says does not move with race grade '
                    .'(docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md line 372). A projection from '
                    .'the entered plan, not a promise: it assumes every one of them is won.',
            'reserve' => $reserve === [] ? null : [
                'label' => (string) ($reserve['label'] ?? ''),
                'value' => number_format((int) ($reserve['coins'] ?? 0)),
                'badge' => 'estimated',
                'title' => 'The publisher\'s own tip rather than a rule this tool derives: '
                    .(string) ($def['shop_reserve_source'] ?? ''),
            ],
            'rated_count' => count($worth),
            'rated_title' => 'How many catalogue items carry a rating from the publisher. An item with no '
                .'rating prints none, and an item this run does not hold is not listed here at all.',
        ];
    }

    /**
     * The recorded purchases, read through the same payload path the Blade form used. Each row
     * carries the turn the Trainer recorded, which `ShopPurchasePayload` itself does not hold.
     *
     * @return list<array{item: string, cost: int, effect: string, turn: int}>
     */
    private function trackblazerPurchases(TrainingRun $run): array
    {
        return $run->turnEvents()
            ->where('event_type', TurnEventType::Scenario->value)
            ->get()
            ->filter(fn (TurnEvent $event): bool => $event->purchasePayload() !== null)
            ->map(fn (TurnEvent $event): array => [
                'item' => (string) $event->purchasePayload()->item,
                'cost' => (int) $event->purchasePayload()->cost,
                'effect' => (string) $event->purchasePayload()->effect,
                'turn' => (int) $event->turn,
            ])
            ->values()
            ->all();
    }

    /**
     * The epithet checklist (plan §9 E4, `x-epithet-checklist` provenance). Returns the rows
     * from `TrainingRun::epithetProgress()` so the renderer never recomputes the three states.
     *
     * @return array<string, mixed>|null
     */
    public function epithetSection(TrainingRun $run): ?array
    {
        if ($run->scenarioKey() !== 'trackblazer') {
            return null;
        }

        return [
            'rows' => $run->epithetProgress(),
            'seen_titles' => $run->completedRaceTitles(),
        ];
    }

    /**
     * The rival / race-schedule facts Trackblazer draws on directly. The matrix carries
     * `race_calendar => false` (Trackblazer has no mandatory calendar), but rival races still
     * need to render as rows the panel can show.
     *
     * @return array<string, mixed>|null
     */
    public function rivalSection(TrainingRun $run): ?array
    {
        if ($run->scenarioKey() !== 'trackblazer') {
            return null;
        }

        $rows = RaceCatalogSlot::query()
            ->forScenario('trackblazer')
            ->orderBy('year')
            ->orderBy('turn')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (RaceCatalogSlot $slot): array => [
                'title' => (string) $slot->title,
                'year_label' => $slot->yearLabel(),
                'turn' => $slot->turnNumber(),
                'tier' => $slot->tier,
            ])
            ->values()
            ->all();

        return [
            'rows' => $rows,
            // The race catalog is the wizard's optional seed set, so an empty read is the
            // normal shape on a fresh database (R39). Saying so keeps the panel honest.
            'absence' => $rows === []
                ? 'No Trackblazer race is in the local calendar; rival rows appear once a row is added.'
                : null,
        ];
    }
}
