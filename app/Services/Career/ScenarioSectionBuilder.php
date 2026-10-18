<?php

declare(strict_types=1);

namespace App\Services\Career;

use App\Models\RaceCatalogSlot;
use App\Models\TrainingRun;
use App\Models\TurnEntry;
use App\Services\Advisor\Advice;
use App\Services\Scenario\FinaleReader;
use App\Services\ScenarioCaps;

/**
 * The scenario panel's own section, extracted from `CockpitController::scenarioSection()` and its
 * three helpers `capsSection()`, `finaleOfficialTitleAbsence()` and `finaleAbsence()` (plan §3.1a,
 * F1 4.4).
 *
 * **The sections this method composes arrive as arguments, not from the service.** `scenarioSection()`
 * reads `scenarioWidgets()`, `milestoneSection()`, `duelSection()` and `preFinalSection()`, and each of
 * those is read elsewhere or belongs to a different slice, so the controller keeps them and passes
 * their values in `$context`. The service owns the section's own shape — its labels, its flags, its
 * four Trackblazer arms and its three absence sentences — and nothing that another reader already owns.
 *
 * **`finale_absence` is load-bearing.** `ScenarioPanelTest` pins the key, and `finaleAbsence()` is its
 * only writer: the key must reach the payload unchanged, or that test fails rather than the screen
 * quietly losing a sentence.
 *
 * **Nothing here predicts.** No readiness band, no win figure, and no scenario fact beyond what
 * `config/scenarios.php` and the run's own recorded state carry (`ADR-0016`, `ADR-0020` §3).
 */
final class ScenarioSectionBuilder
{
    /**
     * The scenario panel's section: the declared scenario's labels, flags, arms and absences.
     *
     * @param  array<string, mixed>  $team  the team panel's section, built once by `CockpitController::show()` so the header and the panel read one pass
     * @param  array<string, mixed>  $context  `widgets` (list<string>), `milestones` (array), `duel` (array|null), `pre_final` (array|null), as `CockpitController` composes them
     * @return array<string, mixed>
     */
    public function scenarioSection(TrainingRun $run, array $team, ?TurnEntry $latest, Advice $advice, array $context): array
    {
        $declared = $run->hasScenario();
        $def = $declared ? (array) config('scenarios.scenarios.'.$run->scenarioKey(), []) : [];

        // The scenario's own Global ruleset date (launch or rework), from the entry's
        // `ruleset_version`. It is NOT `app.ruleset`: the two keys above it — `version` and
        // `version_title` — stay the null-by-ruling pair, and the two facts travel and render
        // separately, which is the R2-05 split and the 2026-10-15 wiring's Caveat 1.
        $rulesetVersion = $def['ruleset_version'] ?? null;
        $rulesetDate = is_array($rulesetVersion) && is_string($rulesetVersion['date'] ?? null)
            ? [
                // Launch when the date is the entry's own availability date, rework otherwise —
                // the same derived split the wizard's card prints, from the same two config facts.
                'value' => $rulesetVersion['date'].((($def['live_on_global'] ?? null) === $rulesetVersion['date']) ? ' (launch)' : ' (rework)'),
                'title' => trim(((string) ($rulesetVersion['event'] ?? '')).' Source: '.(string) ($rulesetVersion['source'] ?? '')),
            ]
            : [
                'value' => null,
                'title' => $declared
                    ? 'No ruleset date is recorded for this scenario.'
                    : 'No scenario is set for this run, so no scenario ruleset date exists to print.',
            ];

        /** @var array<string, string> $widgetLabels */
        $widgetLabels = (array) config('scenarios.widget_labels', []);
        /** @var array<string, string> $panelLabels */
        $panelLabels = (array) config('scenarios.panel_labels', []);

        $labels = [];
        $values = [];
        /** @var list<string> $widgets */
        $widgets = $context['widgets'];

        foreach ($widgets as $widget) {
            if (! isset($widgetLabels[$widget])) {
                continue;
            }

            $labels[$widget] = $widgetLabels[$widget];
            // A scenario resource with no recorded reading is a named absence rather than a zero: a
            // zero would read as "the run has none", which this tool cannot know (D-220). The rank
            // is the one resource a payload already records, so it fills when it exists.
            $values[$widget] = $widget === 'team_rank' ? $team['rank'] : null;
        }

        $panels = [];

        foreach ($panelLabels as $flag => $label) {
            $panels[$flag] = [
                'label' => $label,
                'on' => $declared && ($def['panels'][$flag] ?? false) === true,
            ];
        }

        return [
            'label' => $declared
                ? (string) ($def['label'] ?? $run->scenarioKey())
                : 'No scenario set',
            'declared' => $declared,
            // The badge condition, and the owner's ruling of 2026-10-07 (plan §4.1 item 6,
            // `SCREEN_SPEC.md` SCR-CAR-019): `partially_documented` drives the badge, not
            // `documented`. `documented` stays a provenance marker recording whether a guide is held;
            // it flipped to `true` on 2026-10-05 when the mechanics read landed, no screen reads it,
            // and a value nothing proves cannot double as the badge condition.
            //
            // `partially_documented` is absent from every other entry, which is the shape "nothing to
            // say" this matrix already uses, so a scenario that declares none is documented.
            'documented' => ($def['partially_documented'] ?? false) !== true,
            'version' => null,
            'version_title' => 'No source defines a Global ruleset version, so this run stores none to print.',
            // The scenario's own ruleset date, a different fact from the null version above.
            'ruleset_date' => $rulesetDate['value'],
            'ruleset_date_title' => $rulesetDate['title'],
            // Every key the scenario declares, labelled or not: an unlabelled one is what the
            // shell's fallback exists for, and dropping it here would hide the gap instead of
            // drawing it (the plan's "an unrecognised key renders a labelled fallback").
            'widgets' => $widgets,
            'widget_labels' => $labels,
            'widget_values' => $values,
            'widgets_absence' => $labels === []
                ? null
                : 'No column records a scenario resource, so an unrecorded one reads N/A rather than zero.',
            'panels' => $panels,
            // The team system's own facts (E3, plan §9.1): what the run recorded through the team
            // payloads, what config states, and nothing else — the brief's remaining Team Info
            // fields have no writer and stay absences the panel names. The key set is uniform for
            // every scenario so one renderer can draw all of them without branching.
            'team' => $team,
            // §49's four sections beyond the strip. Their content is each panel's own to fill
            // (E2 to E4); the shell carries them empty rather than inventing a row.
            'objectives' => $context['objectives'],
            'actions' => [],
            'alerts' => $context['alerts'],
            'recommendations' => [],
            'recommendations_absence' => 'No source this tool reads states scenario advice, so none is offered here. The advisor ranks the turn you are deciding.',
            // What the scenario's own config entry declares about its finale, and nothing about this
            // run. Corrected 2026-10-11: this comment used to name `ura_finale` as the only entry that
            // declares a `finale` key, and the matrix says otherwise — the single declaring entry is
            // `trackblazer` (`config/scenarios.php`, 'finale' => points_league over three races), so
            // URA, Unity Cup and Our Grand Concert answer null here. `finale_structure` is the declared
            // shape and `finale_state` below is the run's own standing: the key used to sit under the
            // single word `finale`, and Slice 23 separated the two so one word carries one meaning.
            'finale_structure' => $def['finale'] ?? null,
            'finale_state' => FinaleReader::forRun($run),
            'finale_absence' => $this->finaleAbsence($run, $def),
            // The five Trackblazer-specific sections (plan §9 E4). Each is null for any scenario
            // whose matrix does not turn the matching flag on, so the section's key set stays
            // uniform across the four Global scenarios and the ScenarioPanelTest pin holds.
            // The pino legitimate of E4 is that the renderer reads these never by a scenario
            // identity field.
            'grade' => $context['grade'],
            'shop' => $context['shop'],
            'epithet' => $context['epithet'],
            'rival' => $context['rival'],
            'finale_official_title_absence' => $this->finaleOfficialTitleAbsence($run),
            // The baseline strip's own content (plan §9 E6, D-241). `caps` is uniform for every
            // scenario; the two absence keys are the scenario's own line about why its panels are
            // off, null wherever the entry declares none, which is the shape `grade` and `shop` use.
            'caps' => $this->capsSection($run),
            'panel_absence' => isset($def['panel_absence']) ? (string) $def['panel_absence'] : null,
            'panel_absence_title' => isset($def['panel_absence_title']) ? (string) $def['panel_absence_title'] : null,
            // Phase G's three decision surfaces. Each reads the scenario's own config entry, so a
            // scenario that declares none of these keys gets `[]` or `null` and draws nothing: the
            // empties are the matrix's shape, not a branch on a scenario name (gate G-33).
            'milestones' => $context['milestones'],
            'duel' => $context['duel'],
            'pre_final' => $context['pre_final'],
        ];
    }

    /**
     * The published stat caps, as the baseline strip's own table (plan §9 E6, D-241).
     *
     * `cap` is read through `ScenarioCaps`, the single owner of the base-plus-bonus arithmetic
     * (ADR-0015), so the strip and the header's stat bars cannot disagree about a ceiling. `base` and
     * `bonus` travel as separate terms because the config's own header forbids collapsing them and one
     * JP scenario carries a negative bonus, which a flat cap would erase.
     *
     * The date is the matrix's verification date, not a fetch date: nothing here was fetched on it.
     *
     * @return array{verified_at: string, base: int, hard_cap: int, source_title: string, rows: list<array{key: string, label: string, base: int, bonus: int, cap: int}>}
     */
    private function capsSection(TrainingRun $run): array
    {
        /** @var list<string> $order */
        $order = config('scenarios.stat_order');
        $base = (int) config('scenarios.base_cap');
        // The scenario's own arithmetic, deliberately not `forRun()`: this table's own source line names
        // `config/scenarios.php`, and a ceiling the Trainer read off the client is not in that file. The
        // run's effective bound is what the header's stat bars carry (`stateSection`), so the two surfaces
        // state two different things and stay readable as such (`ADR-0025`).
        $caps = ScenarioCaps::scenarioCaps($run);
        /** @var array<string, mixed> $bonus */
        $bonus = (array) config('scenarios.scenarios.'.$run->scenarioKey().'.cap_bonus', []);
        $verifiedAt = (string) config('scenarios.verified_at');

        $rows = [];

        foreach ($order as $stat) {
            $rows[] = [
                'key' => $stat,
                'label' => $stat,
                'base' => $base,
                'bonus' => (int) ($bonus[$stat] ?? 0),
                'cap' => $caps[$stat],
            ];
        }

        return [
            'verified_at' => $verifiedAt,
            'base' => $base,
            'hard_cap' => (int) config('scenarios.hard_cap'),
            'source_title' => "Published caps from the scenario reference, verified {$verifiedAt} against the sources it names.",
            'rows' => $rows,
        ];
    }

    /**
     * The points-league finale reads as "Trackblazer finale"; any client name is a named absence.
     *
     * "Twinkle Star Climax" is a §7 conflict row 31 UNVERIFIED name in the reference guide,
     * so it is not printed. A screen-reader citation carries the row.
     */
    private function finaleOfficialTitleAbsence(TrainingRun $run): ?string
    {
        if ($run->scenarioKey() !== 'trackblazer') {
            return null;
        }

        return 'No published client name. "Twinkle Star Climax" is §7 conflict row 31 of the reference guide, marked UNVERIFIED, so the export label "Trackblazer" stands until the guide publishes a client string.';
    }

    /**
     * Why no finale structure is shown, where the two answers are different claims.
     *
     * The config's `finale` key describes a composition the shell could draw. An absent key has never
     * meant the career has no finale, and for the four `[Global]` scenarios it would be false: each
     * carries exactly one scenario-scoped row in the finale block
     * (`GametoraRaceCatalogParser::GLOBAL_FINALS_BY_SLOT`), and Our Grand Concert's is the row KI-71
     * corrected to `our_grand_concert` at `480b711`. Telling that Trainer the scenario "declares no
     * finale structure" reads as though the career ends without an ending, which is the false-absence
     * shape the bar's disclosure rule refuses (D-220).
     *
     * The row's presence is read from the catalogue rather than from the config entry, because the
     * catalogue is where the fact lives and `config/scenarios.php` stays the only place a scenario
     * name enters the layout path (D-240). No `finale` key is invented to make the sentence pass:
     * nothing reads that key's contents today, so a value there would be dead config.
     *
     * What is shown is still nothing beyond the calendar row, and the sentence says so.
     *
     * @param  array<string, mixed>  $def
     */
    private function finaleAbsence(TrainingRun $run, array $def): ?string
    {
        if (isset($def['finale'])) {
            return null;
        }

        if (! $run->hasScenario()) {
            return 'This scenario declares no finale structure, so none is shown.';
        }

        $onCalendar = RaceCatalogSlot::query()
            ->where('scenario_key', $run->scenarioKey())
            ->where('year', RaceCatalogSlot::YEAR_FINALE)
            ->exists();

        return $onCalendar
            ? 'The finale is on the career calendar as a mandatory race; this scenario declares no structure beyond it, so nothing further is drawn.'
            : 'This scenario declares no finale structure, so none is shown.';
    }
}
