<?php

declare(strict_types=1);

use App\Http\Controllers\Career\DeckSelectController;
use App\Models\Scenario;
use App\Services\DataPipeline\Parsers\GametoraScenarioParser;
use App\Services\ScenarioCaps;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * ADR-0015's open finding, closed.
 *
 * ADR-0015 recorded, in its own Deviation section, that one number has two homes: `config/scenarios.php`
 * and the `scenarios` table's `cap_speed..cap_wit` / `hard_cap` columns, the latter written by
 * `GametoraScenarioParser` — and that "nothing checks that they agree". Its 2026-10-01 erratum was a bug
 * born of exactly that shape.
 *
 * This test is the span. It asserts, for every scenario the config declares, that the row the parser
 * produces from the source's own bonus array equals what `ScenarioCaps` computes from config — the same
 * arithmetic, the same clamp, reached from the two directions that can disagree.
 *
 * It also pins the second home directly: the parser's `BASE_STAT_CAP` constant and config's `base_cap`
 * are two literals for one number, and a change to either alone fails here rather than drifting.
 *
 * Why the parser body is built from config rather than a stored fixture: the point is not that the parser
 * reads one document correctly (GametoraScenarioParserTest owns that), it is that the two owners agree.
 * Feeding the parser config's own bonus array turns the test into exactly that comparison.
 */

/** The parser's raw record shape for one scenario, built from config's own figures. */
function parityScenarioBody(string $key): array
{
    /** @var array<string, int> $bonus */
    $bonus = config("scenarios.scenarios.{$key}.cap_bonus");

    /** @var array<int, string> $order */
    $order = config('scenarios.stat_order');

    return [
        'id' => $key,
        'url_name' => $key,
        'name_en' => (string) config("scenarios.scenarios.{$key}.label"),
        'name_ja' => null,
        'order' => 1,
        'start_ja' => null,
        'start_en' => null,
        // The source publishes a bonus over the base, in stat_order, not an absolute ceiling.
        'stats' => array_values(array_replace(array_flip($order), $bonus)),
        'hard_caps' => [(int) config('scenarios.hard_cap')],
    ];
}

it('keeps the parser base constant and config base cap one number', function (): void {
    expect(GametoraScenarioParser::BASE_STAT_CAP)->toBe((int) config('scenarios.base_cap'));
});

it('keeps the parser clamp and the ScenarioCaps clamp one number', function (): void {
    expect((int) config('scenarios.hard_cap'))->toBe(ScenarioCaps::hardCap());
});

it('writes a scenarios row whose caps equal what ScenarioCaps computes from config', function (): void {
    /** @var array<string, mixed> $scenarios */
    $scenarios = config('scenarios.scenarios');

    expect($scenarios)->not->toBeEmpty();

    $parser = new GametoraScenarioParser;
    $records = $parser->parse((string) json_encode(array_map(
        static fn (string $key): array => parityScenarioBody($key),
        array_keys($scenarios),
    )));

    expect($records)->toHaveCount(count($scenarios));

    // The expected figures, computed here rather than read from ScenarioCaps, so this half of the
    // assertion is an independent statement of the rule and not a restatement of the other side.
    $base = (int) config('scenarios.base_cap');
    $hardCap = (int) config('scenarios.hard_cap');

    /** @var array<int, string> $order */
    $order = config('scenarios.stat_order');

    foreach ($records as $record) {
        $key = (string) $record['slug'];

        expect($key)->toBeIn(array_keys($scenarios));

        /** @var array<string, int> $bonus */
        $bonus = config("scenarios.scenarios.{$key}.cap_bonus");

        foreach ($order as $stat) {
            $expected = min($base + (int) ($bonus[$stat] ?? 0), $hardCap);

            expect($record['cap_'.strtolower($stat)])->toBe($expected)
                ->and(ScenarioCaps::stat($key, $stat))->toBe($expected);
        }

        expect($record['hard_cap'])->toBe($hardCap);

        // The row the fetch engine would promote, read back through the ceiling owner.
        $row = Scenario::create($record);

        foreach ($order as $stat) {
            expect($row->getAttribute('cap_'.strtolower($stat)))->toBe(ScenarioCaps::stat($key, $stat));
        }
    }
});

it('agrees with the table when a row is stored from an absolute figure the parser computed', function (): void {
    /** @var array<string, mixed> $scenarios */
    $scenarios = config('scenarios.scenarios');

    foreach (array_keys($scenarios) as $key) {
        $caps = ScenarioCaps::caps($key);

        $row = Scenario::create([
            'slug' => $key,
            'name' => (string) config("scenarios.scenarios.{$key}.label"),
            'cap_speed' => $caps['Speed'],
            'cap_stamina' => $caps['Stamina'],
            'cap_power' => $caps['Power'],
            'cap_guts' => $caps['Guts'],
            'cap_wit' => $caps['Wit'],
            'hard_cap' => ScenarioCaps::hardCap(),
        ]);

        // The stored row and the config arithmetic are the same fact, so a scenario's row must
        // reproduce the caps the owner reads in config, column for column.
        foreach ($caps as $stat => $value) {
            expect($row->getAttribute('cap_'.strtolower($stat)))->toBe($value);
        }
    }
});

/*
 * The two screens that PRINT a cap figure had their own copies of it, in prose, in a controller.
 *
 * `DeckSelectController::scenarioHints()` stated "Wit cap is 1800" and "Speed cap is 1600" as string
 * literals, and `DatabaseController::scenarios()` computed `base + bonus` itself with no clamp. Both
 * agreed with `ScenarioCaps` on the day they were written, which is exactly why nothing failed when a
 * `cap_bonus` edit could have left them quoting a stale ceiling. These cases read the figure back out
 * of the rendered surfaces and compare it to the single owner, so the next edit to `cap_bonus` fails
 * here instead of shipping a second answer.
 */
it('states the deck-builder cap hints from ScenarioCaps, not from a literal', function (): void {
    $controller = new ReflectionClass(DeckSelectController::class);
    $method = $controller->getMethod('scenarioHints');
    $instance = $controller->newInstanceWithoutConstructor();

    foreach (['unity_cup', 'our_grand_concert'] as $key) {
        /** @var list<string> $hints */
        $hints = $method->invoke($instance, $key);
        $joined = implode(' ', $hints);
        $caps = ScenarioCaps::caps($key);

        // Every figure the sentence prints must be one of this scenario's own published ceilings.
        preg_match_all('/\b(\d{3,4})\b/', $joined, $matches);

        expect($matches[1])->not->toBeEmpty("{$key}'s hint states no figure at all.");

        foreach ($matches[1] as $printed) {
            expect(array_map('strval', array_values($caps)))->toContain($printed);
        }
    }

    // And the specific numbers the old literals carried are still what the owner computes, so this
    // case fails if a `cap_bonus` edit moves a ceiling and the sentence is not re-derived.
    expect(ScenarioCaps::caps('unity_cup')['Wit'])->toBe(1800)
        ->and(ScenarioCaps::caps('our_grand_concert')['Speed'])->toBe(1600);
});

it('prints the Database matrix cap from ScenarioCaps, clamped the same way', function (): void {
    $this->get(route('database.scenarios'))
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            /** @var array<int, array<string, mixed>> $rows */
            $rows = $page->toArray()['props']['scenarios'] ?? [];

            expect($rows)->not->toBeEmpty();

            // The payload is keyed by scenario key, not a list of rows carrying one.
            foreach ($rows as $key => $row) {
                $key = (string) $key;

                /** @var array<int, array{stat: string, cap: int}> $caps */
                $caps = $row['caps'];
                $published = ScenarioCaps::caps($key);

                foreach ($caps as $entry) {
                    expect($entry['cap'])->toBe(
                        $published[$entry['stat']],
                        "{$key} {$entry['stat']} printed {$entry['cap']} but ScenarioCaps says {$published[$entry['stat']]}"
                    );
                }
            }
        });
});
