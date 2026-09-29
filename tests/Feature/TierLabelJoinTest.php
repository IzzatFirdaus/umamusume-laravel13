<?php

declare(strict_types=1);

use App\Models\ScenarioSlot;
use Database\Seeders\ScenarioSlotSeeder;

/*
 * R72: a seeded tier is a per-race claim, so it is sourced per race. The seeder used to hold a
 * grade-code to label constant; the join now reads the dated two-publisher extraction committed at
 * database/seeders/data/race-tier-labels-2026-09-29.json.
 *
 * Race names and their expected outcomes are taken from that file, which records for each row what
 * uma.guide and Game8 said and when. Game8's page is dated 2026-09-09; uma.guide publishes no date
 * at all, so only the fetch date is known and Game8's date is the one that has to clear G-39.
 */

it('labels a G2 race from the two publishers rather than from its numeric grade code', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    $slot = ScenarioSlot::where('scenario_key', 'ura_finale')->where('title', 'Nikkei Shinshun Hai')->first();

    expect($slot)->not->toBeNull()
        ->and($slot->tier)->toBe('G2')
        ->and($slot->source_key)->not->toBeNull()
        ->and($slot->source_url)->toContain('game8.co')
        ->and($slot->source_url)->toContain('uma.guide')
        ->and($slot->fetched_at)->not->toBeNull();
});

it('labels a G3 race the same way', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    $slot = ScenarioSlot::where('scenario_key', 'ura_finale')->where('title', 'Kyoto Kimpai')->first();

    expect($slot)->not->toBeNull()
        ->and($slot->tier)->toBe('G3')
        ->and($slot->source_url)->toContain('game8.co');
});

it('leaves a Pre-OP race unlabelled because one publisher is silent about it', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    $slot = ScenarioSlot::where('scenario_key', 'ura_finale')->where('title', 'Aster Sho')->first();

    // uma.guide says Pre-OP; Game8's list is graded-only, so its silence is not agreement.
    expect($slot)->not->toBeNull()
        ->and($slot->tier)->toBeNull()
        ->and($slot->source_key)->not->toBeNull();
});

it('keeps the Open label only where the client name of that row carries the open glyph', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    $pinned = ScenarioSlot::where('scenario_key', 'ura_finale')->where('title', 'Fukushima TV Open')->first();
    $generalised = ScenarioSlot::where('scenario_key', 'ura_finale')->where('title', 'Anemone Stakes')->first();

    // R75: Anemone Stakes is Open by export grade, but no publisher names it Open per row, and the
    // client glyph 「オープン」 is absent from its own name. A generalisation from three pinned rows
    // is not per-race evidence, so the honest state is null rather than a labelled row that
    // discloses in a sidecar flag that it is not sourced.
    expect($pinned)->not->toBeNull()->and($pinned->tier)->toBe('OP')
        ->and($generalised)->not->toBeNull()->and($generalised->tier)->toBeNull();
});

it('dates a labelled row to the evidence, not to the moment the seed ran', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    $doc = json_decode(
        (string) file_get_contents(database_path('seeders/data/race-tier-labels-2026-09-29.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    $labelled = ScenarioSlot::where('scenario_key', 'ura_finale')->whereNotNull('tier')->get();

    expect($labelled)->not->toBeEmpty()
        ->and($labelled->every(
            fn (ScenarioSlot $s): bool => $s->fetched_at !== null
                && $s->fetched_at->toDateString() === $doc['fetched_at']
        ))->toBeTrue()
        ->and($labelled->first()->snapshot_path)->toContain('race-tier-labels-');
});

it('holds no grade-code to tier-label constant, which is what G-16c guards', function (): void {
    $source = file_get_contents(base_path('database/seeders/ScenarioSlotSeeder.php'));

    // Any of these shapes would put the inference back in code: a code-keyed array, or a literal
    // tier string chosen by comparing a numeric grade.
    expect($source)->not->toMatch('/=>\s*\'(G1|G2|G3|OP|Pre-OP)\'/')
        ->and($source)->not->toMatch('/GRADE_MAP/');
});

it('seeds only per-race sourced tiers, so Open is down to its three client-pinned rows', function (): void {
    $this->seed(ScenarioSlotSeeder::class);

    $byTier = ScenarioSlot::where('scenario_key', 'ura_finale')
        ->where('kind', 'goal_race')
        ->get()
        ->groupBy(fn (ScenarioSlot $s): string => $s->tier ?? 'null')
        ->map(fn ($g): int => $g->count());

    expect($byTier->get('G1'))->toBe(34)
        ->and($byTier->get('G2'))->toBe(42)
        ->and($byTier->get('G3'))->toBe(76)
        ->and($byTier->get('OP'))->toBe(3)
        ->and($byTier->get('null'))->toBe(141);
});

it('carries no disclosure flag beside a null tier, because null is the disclosure', function (): void {
    $doc = json_decode(
        (string) file_get_contents(database_path('seeders/data/race-tier-labels-2026-09-29.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    // R75: `per_row_sourced` existed to keep a generalised label on the row while admitting it was
    // a generalisation. With the generalisation gone the flag says nothing a null tier does not.
    $withFlag = array_filter(
        $doc['rows'],
        fn (array $r): bool => array_key_exists('per_row_sourced', $r),
    );

    expect($withFlag)->toBe([])
        ->and($doc['counts']['400']['null'])->toBe(115)
        ->and($doc['counts']['400']['OP'])->toBe(3)
        ->and($doc['counts']['700']['null'])->toBe(26);
});
