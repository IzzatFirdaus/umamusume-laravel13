<?php

declare(strict_types=1);

namespace App\Services\Career;

use App\Models\CharacterCard;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Support\Collection;

/**
 * Published reference material for one career, assembled from the catalogs and the dated
 * `imported_career_reference` config. This is deliberately not an importer: it writes nothing to a
 * run, and every value it returns is labeled as reference rather than as observed history.
 *
 * The split matters for the 17 tracker runs. They carry one snapshot each and no recorded deck, so a
 * card's event list, a scenario's fixed events, and a Spark rule can all be useful to the Trainer
 * without being evidence that any of them happened in that career. Keeping them behind this reader
 * means the Timeline and the Veteran detail screen cannot accidentally turn a published event into a
 * `turn_event`, and an unknown support deck cannot acquire an invented support-event pool.
 */
final class CareerReference
{
    /**
     * @return array<string, mixed>
     */
    public static function forRun(TrainingRun $run): array
    {
        $run->loadMissing(['umamusume', 'characterCard']);

        /** @var CharacterCard|null $card */
        $card = $run->characterCard;
        /** @var Umamusume|null $trainee */
        $trainee = $run->umamusume;
        $traineeName = $trainee?->name;
        $imported = $run->import_source !== null;
        $cardId = $card?->card_id;
        $scenarioKey = $run->scenarioKey();

        /**
         * A run with no chosen scenario resolves to the baseline through `scenarioKey()`, which is
         * right for the resource strip but wrong here: printing URA's mechanics on a run that never
         * selected URA would assert a scenario the run does not have. Imported tracker runs all carry
         * `ura_finale` explicitly, so this guard only changes ordinary new-career drafts.
         */
        $scenarioReference = ($imported || $run->hasScenario())
            ? self::configArray('imported_career_reference.scenarios.'.$scenarioKey)
            : null;

        $schedule = [
            'trainee' => $cardId === null
                ? null
                : self::configArray('imported_career_reference.cards.'.$cardId),
            'scenario' => $scenarioReference,
        ];

        $cardReference = self::cardSection($card);
        $profile = self::profileSection($card, $trainee);
        $skills = self::skillSection($card);
        $mechanics = self::mechanicsSection($scenarioKey, $scenarioReference !== null);
        $events = self::eventSection($schedule['trainee'] !== null);

        $coverage = [
            'objectives' => $schedule['trainee'] !== null,
            'scenario_milestones' => $schedule['scenario'] !== null,
            'card_profile' => $profile !== null,
            'published_skills' => $skills !== [],
            'fixed_trainee_events' => $events['fixed_events'],
            'random_trainee_events' => false,
            'secret_trainee_events' => false,
            'support_card_events' => false,
            'spark_activations' => false,
        ];

        return [
            'available' => in_array(true, $coverage, true),
            'imported' => $imported,
            'trainee' => $traineeName,
            'scenario_key' => $run->hasScenario() ? $scenarioKey : null,
            'card' => $cardReference,
            'profile' => $profile,
            'skills' => $skills,
            'schedule' => $schedule,
            'mechanics' => $mechanics,
            'events' => $events,
            'coverage' => $coverage,
            'disclaimer' => 'Published reference only. Nothing here confirms that an event occurred, '
                .'an objective was cleared, a skill was acquired, or a Spark activated in this career.',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function configArray(string $key): ?array
    {
        $value = config($key);

        return is_array($value) && $value !== [] ? $value : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function cardSection(?CharacterCard $card): ?array
    {
        if ($card === null) {
            return null;
        }

        return [
            'card_id' => (int) $card->card_id,
            'title' => $card->title,
            'rarity_label' => $card->rarity->label(),
            'global_release_date' => $card->global_release_date?->toDateString(),
            'source_url' => $card->source_url,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function profileSection(?CharacterCard $card, ?Umamusume $trainee): ?array
    {
        if ($card === null || $trainee === null) {
            return null;
        }

        $aptitudes = $trainee->aptitudeAxes();
        $baseStats = is_array($card->base_stats) ? $card->base_stats : null;
        $growthRates = is_array($card->growth_rates) ? $card->growth_rates : null;

        if ($aptitudes === [] && $baseStats === null && $growthRates === null) {
            return null;
        }

        return [
            'aptitudes' => $aptitudes,
            'base_stats' => $baseStats,
            'growth_rates' => $growthRates,
        ];
    }

    /**
     * Resolve the card's published skill lists through the same Global-only read scope the catalog
     * uses everywhere else. Unresolved ids are counted rather than guessed from a display name.
     *
     * @return list<array<string, mixed>>
     */
    private static function skillSection(?CharacterCard $card): array
    {
        if ($card === null) {
            return [];
        }

        $groups = [
            ['key' => 'unique', 'label' => 'Unique skill', 'ids' => self::ids($card->skills_unique)],
            ['key' => 'innate', 'label' => 'Starting skills', 'ids' => self::ids($card->skills_innate)],
            ['key' => 'awakening', 'label' => 'Awakening skills', 'ids' => self::ids($card->skills_awakening)],
            ['key' => 'event', 'label' => 'Event skills', 'ids' => self::ids($card->skills_event)],
        ];

        $evolutionPairs = $card->skills_evo ?? [];
        $evolutionIds = [];

        foreach ($evolutionPairs as $pair) {
            foreach (['new', 'old'] as $key) {
                $evolutionIds[] = (int) $pair[$key];
            }
        }

        if ($evolutionIds !== []) {
            $groups[] = [
                'key' => 'evolution',
                'label' => 'Evolution skills',
                'ids' => array_values(array_unique($evolutionIds)),
            ];
        }

        $allIds = collect($groups)
            ->flatMap(static fn (array $group): array => $group['ids'])
            ->unique()
            ->values();

        /** @var Collection<int|string, Skill> $resolved */
        $resolved = $allIds->isEmpty()
            ? collect()
            : Skill::query()->availableOnGlobal()->whereIn('export_id', $allIds->all())->get()->keyBy('export_id');

        $rows = [];

        foreach ($groups as $group) {
            if ($group['ids'] === []) {
                continue;
            }

            $skills = collect($group['ids'])
                ->map(static fn (int $id): ?Skill => $resolved->get($id))
                ->filter(static fn (?Skill $skill): bool => $skill !== null)
                ->map(static fn (Skill $skill): array => [
                    'name' => $skill->name,
                    'sp_cost' => $skill->sp_cost,
                    'is_unique' => $skill->is_unique,
                    'url' => route('skills.show', $skill),
                ])
                ->values()
                ->all();

            $rows[] = [
                'key' => $group['key'],
                'label' => $group['label'],
                'skills' => $skills,
                'unresolved' => count($group['ids']) - count($skills),
            ];
        }

        return $rows;
    }

    /**
     * @return list<int>
     */
    private static function ids(mixed $ids): array
    {
        if (! is_array($ids)) {
            return [];
        }

        return collect($ids)
            ->filter(static fn (mixed $id): bool => is_numeric($id))
            ->map(static fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function mechanicsSection(string $scenarioKey, bool $hasScenarioReference): array
    {
        $sections = [];

        foreach (['inheritance', 'skill_hints'] as $key) {
            $entry = self::configArray('imported_career_reference.mechanics.'.$key);
            if ($entry === null) {
                continue;
            }

            $sections[] = self::mechanicRow($key, $entry);
        }

        if ($hasScenarioReference) {
            $entry = self::configArray('imported_career_reference.mechanics.scenarios.'.$scenarioKey);
            if ($entry !== null) {
                $row = self::mechanicRow('scenario', $entry);
                $ruleset = self::configArray('scenarios.scenarios.'.$scenarioKey.'.ruleset_version');

                if ($ruleset !== null) {
                    $row['ruleset'] = [
                        'date' => $ruleset['date'] ?? null,
                        'event' => $ruleset['event'] ?? null,
                        'source' => $ruleset['source'] ?? null,
                    ];
                }

                $sections[] = $row;
            }
        }

        $limits = self::configArray('imported_career_reference.mechanics.event_limits');
        if ($limits !== null) {
            $sections[] = self::mechanicRow('event_limits', $limits);
        }

        return $sections;
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    private static function mechanicRow(string $key, array $entry): array
    {
        return [
            'key' => $key,
            'label' => $entry['label'] ?? $key,
            'source' => $entry['source'] ?? null,
            'published' => $entry['published'] ?? null,
            'retrieved' => $entry['retrieved'] ?? null,
            'version_note' => $entry['version_note'] ?? null,
            'rework_source' => $entry['rework_source'] ?? null,
            'points' => is_array($entry['points'] ?? null) ? array_values($entry['points']) : [],
            'conflicts' => is_array($entry['conflicts'] ?? null) ? array_values($entry['conflicts']) : [],
        ];
    }

    /**
     * @return array{fixed_events: bool, note: string}
     */
    private static function eventSection(bool $hasObjectiveSchedule): array
    {
        return [
            'fixed_events' => $hasObjectiveSchedule,
            'note' => 'Fixed and conditional trainee events named in the objective schedule are '
                .'published reference material. Random and secret event rewards are not decoded in '
                .'the committed datasets, and support-card event chains require a recorded deck. '
                .'None of these has been inserted into this run\'s observed history.',
        ];
    }
}
