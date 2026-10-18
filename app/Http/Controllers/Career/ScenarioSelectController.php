<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Http\Controllers\Controller;
use App\Http\Requests\Career\StoreScenarioRequest;
use App\Models\Preference;
use App\Services\Career\SetupDraft;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Scenario Selection, `SCREEN-002` (PRD FR-C-1, `ADR-0020` §1). Step 1 of the setup wizard.
 *
 * **Every card field is derived from `config/scenarios.php`, and none is prose this controller
 * invents.** The slice rule is "do not write mechanics the config does not hold", and the plan's own
 * acceptance for this row is that adding a fifth scenario is one config entry and no component edit
 * (D-240, gate G-33). Both hold only if the card reads the matrix:
 *
 * - name from `label`;
 * - optimization focus from `cap_bonus` — the stat(s) at the highest bonus, or a stated uniform bonus;
 * - systems from the `panels` entries that are `true`, labelled through `panel_labels`;
 * - tracked resources from the `widgets` the scenario composes beyond the baseline's, labelled
 *   through `widget_labels`;
 * - turn loop from `steps`;
 * - availability from `live_on_global`;
 * - the documentation badge from `documented` when it is present and false.
 *
 * `our_grand_concert` therefore needs no special case in this file or in the page: every panel is off
 * and its widgets are the baseline three, so the derivation yields "no scenario system" on its own,
 * which is the honest statement, and `documented => false` adds the badge. URA Finale derives the
 * same way and is correct for the same reason — it *is* the baseline.
 *
 * **Absent fields are named, not filled.** The brief's card also asks for a short marketing
 * description and a "recommended use", and neither the corpus nor the config holds either. Printing
 * one would be the invented fact `AGENTS.md` §5 forbids, so those two slots render `N/A` with a
 * `title`, and the gap is an open question on the slice rather than a sentence nobody sourced.
 */
class ScenarioSelectController extends Controller
{
    public function show(): Response
    {
        SetupDraft::markVisited(1);
        $draft = SetupDraft::read();

        return Inertia::render('Career/ScenarioSelect', [
            'visited' => SetupDraft::visited(),
            'saved' => SetupDraft::saved(),
            'scenarios' => $this->cards(),
            // Three states, not two. A draft that has never carried the key may take the Trainer's own
            // default; a draft that carries it as null was cleared on purpose, and a preference must
            // not stand in for either a career in progress or a choice just withdrawn.
            'selected' => $draft['scenario'] ?? (SetupDraft::scenarioRecorded() ? null : $this->storedDefault()),
            'ruleset' => $this->ruleset(),
            // The step's write URL, supplied server-side the way CareerLayout.vue:11 and
            // UnityCupEditor.vue:20 already receive URLs. No Ziggy dependency is introduced.
            'action' => route('career.scenario.store'),
        ]);
    }

    /**
     * The Default-scenario preference (SCREEN-024), or null when none is stored or the stored one
     * no longer names a matrix entry.
     *
     * The config check is `SetupDraft::read()`'s own rule for the same question — a stale key left
     * by a config change reads as no scenario rather than as one that renders a blank card — held
     * here too, so a preference cannot pre-select a card the matrix does not compose.
     */
    private function storedDefault(): ?string
    {
        $stored = Preference::settings()['default_scenario'] ?? null;

        if (is_string($stored) && array_key_exists($stored, (array) config('scenarios.scenarios'))) {
            return $stored;
        }

        return null;
    }

    public function store(StoreScenarioRequest $request): RedirectResponse
    {
        $scenario = $request->validated('scenario');
        SetupDraft::write(['scenario' => $scenario]);

        // Back to this step, deliberately: the choice is read from the session on the next render, so
        // the card reflects stored state rather than whatever the client still holds in memory. That
        // round trip is what makes "the selection survives navigation" a claim about the server. A
        // null write is the clear of it, and it is stored as null rather than as an empty string so
        // `SetupDraft::saved()` stops counting step 1 as saved.
        return redirect()->route('career.scenario')
            ->with('status', $scenario === null ? 'Scenario cleared.' : 'Scenario set.');
    }

    /**
     * One card per scenario the matrix composes, in the matrix's own order.
     *
     * @return list<array<string, mixed>>
     */
    private function cards(): array
    {
        /** @var list<string> $statOrder */
        $statOrder = (array) config('scenarios.stat_order');
        /** @var array<string, string> $widgetLabels */
        $widgetLabels = (array) config('scenarios.widget_labels');
        /** @var array<string, string> $panelLabels */
        $panelLabels = (array) config('scenarios.panel_labels');

        // The generic widgets every scenario composes are the baseline entry's own list, so "the
        // scenario's own resource" is a subtraction against config rather than a hard-coded trio.
        // `ponytail:` the baseline doubles as the generic set; if a fifth scenario adds a widget the
        // baseline lacks, name the generic list in config and read that instead.
        $baselineKey = (string) config('scenarios.baseline');
        /** @var list<string> $baselineWidgets */
        $baselineWidgets = array_values((array) config('scenarios.scenarios.'.$baselineKey.'.widgets'));

        $cards = [];

        foreach ((array) config('scenarios.scenarios') as $key => $definition) {
            /** @var array<string, mixed> $def */
            $def = (array) $definition;

            /** @var array<string, mixed> $bonus */
            $bonus = (array) ($def['cap_bonus'] ?? []);
            $caps = array_map(static fn (mixed $value): int => (int) $value, $bonus);
            $meta = $this->metadata($def);

            $cards[] = [
                'key' => $key,
                'label' => (string) ($def['label'] ?? $key),
                'documented' => $def['documented'] ?? null,
                'optimizes' => $this->optimizes($statOrder, $caps),
                'systems' => $this->systems((array) ($def['panels'] ?? []), $panelLabels),
                'resources' => array_values(array_filter(
                    array_map(
                        static fn (mixed $widget): ?string => in_array($widget, $baselineWidgets, true)
                            ? null
                            : ($widgetLabels[(string) $widget] ?? (string) $widget),
                        array_values((array) ($def['widgets'] ?? [])),
                    ),
                    static fn (?string $label): bool => $label !== null,
                )),
                'loop' => array_values(array_map(
                    static fn (mixed $step): string => (string) $step,
                    (array) ($def['steps'] ?? []),
                )),
                'live_on_global' => isset($def['live_on_global']) ? (string) $def['live_on_global'] : null,
                // The card's own metadata, recorded in the matrix for a scenario a source describes and
                // null for the rest. Null is the absence the page names, never a sentence invented here.
                'metadata' => $meta,
                // Config holds no player-facing blurb and no "recommended use" for a scenario whose
                // guide does not state one, so a null here is a named absence on the page.
                'description' => $meta['description'] ?? null,
                'recommended_use' => $meta['recommended_use'] ?? null,
                // The scenario's rule family, read from the entry's `facility_level_source` — the
                // matrix fact the 2026-10-15 scenario-field-metadata artifact resolved the field
                // against. It is a repository-defined taxonomy over that config fact; the report
                // records that no publisher uses the term. The row renders the humanized label,
                // never the snake_case key (owner ruling, 2026-10-16).
                'rule_family' => isset($def['facility_level_source'])
                    ? $this->ruleFamilyLabel((string) $def['facility_level_source'])
                    : null,
                // The scenario's Global ruleset date (launch or rework). NOT `app.ruleset`, which
                // `ruleset()` below keeps null by ruling; the two are separate facts on separate
                // rows with separate sources, which is the R2-05 split preserved.
                'ruleset_date' => $this->rulesetDate($def),
            ];
        }

        return $cards;
    }

    /**
     * The card's metadata, read straight from the matrix, or null when the entry holds none.
     *
     * @param  array<string, mixed>  $def
     * @return array{summary: string|null, planning_focus: string|null, planning_focus_source: string|null, planning_focus_reason: string|null, key_mechanics: list<string>, description: string|null, recommended_use: string|null, recommended_use_reason: string|null, recommended_use_source: string|null}|null
     */
    private function metadata(array $def): ?array
    {
        $metadata = $def['metadata'] ?? null;

        if (! is_array($metadata)) {
            return null;
        }

        return [
            'summary' => isset($metadata['summary']) ? (string) $metadata['summary'] : null,
            'planning_focus' => isset($metadata['planning_focus']) ? (string) $metadata['planning_focus'] : null,
            // The 2026-10-15 artifact's recorded searches for the cells no source fills. They ride
            // the payload so the page's absence carries the research's own reason, verbatim.
            'recommended_use_reason' => isset($metadata['recommended_use_reason']) ? (string) $metadata['recommended_use_reason'] : null,
            'planning_focus_reason' => isset($metadata['planning_focus_reason']) ? (string) $metadata['planning_focus_reason'] : null,
            // Where a cell was recovered from a publisher, the source travels with it so the rendered
            // value names where it came from (AGENTS.md §5: no fact without provenance).
            'recommended_use_source' => isset($metadata['recommended_use_source']) ? (string) $metadata['recommended_use_source'] : null,
            'planning_focus_source' => isset($metadata['planning_focus_source']) ? (string) $metadata['planning_focus_source'] : null,
            'key_mechanics' => array_values(array_map(
                static fn (mixed $line): string => (string) $line,
                (array) ($metadata['key_mechanics'] ?? []),
            )),
            'description' => isset($metadata['description']) ? (string) $metadata['description'] : null,
            'recommended_use' => isset($metadata['recommended_use']) ? (string) $metadata['recommended_use'] : null,
        ];
    }

    /**
     * The Rule family row's display label. The taxonomy values are config keys, not user copy,
     * so the card prints the humanized form and never a snake_case key (owner ruling, 2026-10-16).
     * A value the map does not name is word-formed rather than printed raw, so a fifth scenario's
     * own taxonomy value still cannot reach the card as a config key.
     */
    private function ruleFamilyLabel(string $key): string
    {
        return match ($key) {
            'repetition' => 'Repetition',
            'team_rank' => 'Team rank',
            'repetition_plus_purchases' => 'Repetition + purchases',
            default => Str::headline($key),
        };
    }

    /**
     * The scenario's own Global ruleset date, from the entry's `ruleset_version`: the dated
     * instant a source pins the scenario's current numbering to, its launch or its rework. It is
     * not `app.ruleset` — that key stays null by ruling — and the two must not be merged into one
     * label, one key or one rendered string.
     *
     * @param  array<string, mixed>  $def
     * @return array{value: string, title: string}|null
     */
    private function rulesetDate(array $def): ?array
    {
        $version = $def['ruleset_version'] ?? null;

        if (! is_array($version) || ! is_string($version['date'] ?? null)) {
            return null;
        }

        // Whether the date is the launch or a later rework is derived from the two sourced dates:
        // a ruleset date equal to the entry's own `live_on_global` is the launch, anything else is
        // a rework. The word is the report's own "(re)launch" split, not a new term.
        $kind = (($def['live_on_global'] ?? null) === $version['date']) ? 'launch' : 'rework';

        return [
            'value' => $version['date'].' ('.$kind.')',
            'title' => trim(((string) ($version['event'] ?? '')).' Source: '.(string) ($version['source'] ?? '')),
        ];
    }

    /**
     * The stat the scenario's own cap bonus favours, as words. Ties are stated as ties rather than
     * broken by ordering, because choosing a winner among five equal bonuses would be inventing a
     * preference the matrix does not express.
     *
     * @param  list<string>  $statOrder
     * @param  array<string, int>  $caps
     * @return array{kind: string, stats: list<string>, bonus: int|null}
     */
    private function optimizes(array $statOrder, array $caps): array
    {
        $present = array_values(array_filter($statOrder, static fn (string $stat): bool => array_key_exists($stat, $caps)));

        if ($present === []) {
            return ['kind' => 'unknown', 'stats' => [], 'bonus' => null];
        }

        $max = max(array_map(static fn (string $stat): int => $caps[$stat], $present));
        $min = min(array_map(static fn (string $stat): int => $caps[$stat], $present));
        $atMax = array_values(array_filter($present, static fn (string $stat): bool => $caps[$stat] === $max));

        return [
            'kind' => $max === $min ? 'uniform' : 'peaked',
            'stats' => $atMax,
            'bonus' => $max,
        ];
    }

    /**
     * The scenario's systems, which is the panel list read as words. A panel that is off is absent
     * rather than printed as a refusal, because the matrix saying "no shop" is a fact and the card
     * saying "no shop" for all four scenarios would be noise.
     *
     * @param  array<string, mixed>  $panels
     * @param  array<string, string>  $panelLabels
     * @return list<string>
     */
    private function systems(array $panels, array $panelLabels): array
    {
        $on = [];

        foreach ($panels as $panel => $value) {
            if ($value === true) {
                $on[] = $panelLabels[$panel] ?? (string) $panel;
            }
        }

        return $on;
    }

    /**
     * The ruleset version the cards ask for. `app.ruleset` is null by ruling
     * (`HandleInertiaRequests`: "No source defines a Global ruleset version … this is a named absence,
     * never an invented number"), so the card prints `N/A` with the reason.
     *
     * @return array{value: string|null, title: string}
     */
    private function ruleset(): array
    {
        return [
            'value' => null,
            'title' => 'No source defines a Global ruleset version, so this tool states none.',
        ];
    }
}
