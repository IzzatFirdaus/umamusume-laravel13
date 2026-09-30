<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Enums\SkillAcquisition;
use App\Models\MatchCandidate;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Services\DataPipeline\Parsers\GametoraSkillsParser;
use App\Services\DataPipeline\PipelineRunner;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

function skillsFixtureBody(): string
{
    return (string) file_get_contents(base_path('tests/Fixtures/gametora-skills.sample.json'));
}

/**
 * The declared entry, with the politeness delay zeroed so a suite of twelve rows does not sleep twice.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function skillsSourceConfig(array $overrides = []): array
{
    $declared = config('uma.sources.gametora-skills');

    return [...$declared, 'delay_ms' => 0, ...$overrides];
}

const SKILLS_SNAPSHOT = 'storage/framework/testing/disks/local/snapshots/skills.json';

/**
 * The literal Japanese renderings (`enname`) that differ from the client string on rows Global has.
 *
 * Read off the fixture rather than spelled out here, because the point of this file is that a test which
 * re-types a source value can agree with a bug in it (KI-23), and one of these eight values carries a word
 * `[Global]` does not use for a stat, so typing it into a test to prove its absence fails the lore gate.
 *
 * @return list<string>
 */
function skillsFixtureGlobalRenderings(): array
{
    /** @var list<array<string, mixed>> $rows */
    $rows = json_decode(skillsFixtureBody(), true, 512, JSON_THROW_ON_ERROR);

    $renderings = [];

    foreach ($rows as $row) {
        $client = $row['name_en'] ?? null;
        $rendering = $row['enname'] ?? null;
        $unreleased = $row['unreleased'] ?? [];

        if (is_string($client) && is_string($rendering) && $rendering !== $client
            && ! (is_array($unreleased) && in_array('en', $unreleased, true))) {
            $renderings[] = $rendering;
        }
    }

    return $renderings;
}

it('is declared as a fetch source with the skills parser and a manifest block', function (): void {
    expect(config('uma.sources.gametora-skills.parser'))->toBe(GametoraSkillsParser::class)
        ->and(config('uma.sources.gametora-skills.manifest.key'))->toBe('skills')
        ->and(config('uma.sources.gametora-skills.manifest.base'))->toBe('https://gametora.com/data/umamusume/')
        ->and(config('uma.sources.gametora-skills.manifest.url'))->toBe('https://gametora.com/data/manifests/umamusume.json')
        // The pin stays declared: it is what uma:reparse and a manifest outage fall back to (KI-24).
        ->and(config('uma.sources.gametora-skills.url'))->toBe('https://gametora.com/data/umamusume/skills.609afe88.json');
});

it('routes skills to the writer, not to character matching', function (): void {
    $counts = app(PipelineRunner::class)->run(
        'gametora-skills',
        skillsSourceConfig(),
        skillsFixtureBody(),
        SKILLS_SNAPSHOT,
    );

    expect($counts)->toMatchArray(['created' => 12, 'updated' => 0, 'review' => 0])
        // The failure this branch exists to prevent: every skill filed as a character nobody could match.
        ->and(MatchCandidate::count())->toBe(0)
        ->and(Skill::count())->toBe(12);
});

it('stamps R3 provenance and a normalized key on every row it writes', function (): void {
    app(PipelineRunner::class)->run(
        'gametora-skills',
        skillsSourceConfig(),
        skillsFixtureBody(),
        SKILLS_SNAPSHOT,
    );

    $averseness = Skill::where('export_id', 200311)->firstOrFail();

    expect($averseness->source_url)->toBe('https://gametora.com/data/umamusume/skills.609afe88.json')
        ->and($averseness->snapshot_path)->toBe(SKILLS_SNAPSHOT)
        ->and($averseness->source_timezone)->toBe('Asia/Tokyo')
        ->and($averseness->fetched_at)->not->toBeNull()
        ->and($averseness->is_manual)->toBeFalse()
        ->and($averseness->match_key)->not->toBeNull()
        ->and($averseness->name_ja)->toBe('GⅠ苦手');
});

it('updates in place on a re-run instead of colliding with its own grain', function (): void {
    $pipeline = app(PipelineRunner::class);
    $config = skillsSourceConfig();

    $pipeline->run('gametora-skills', $config, skillsFixtureBody(), null);
    $second = $pipeline->run('gametora-skills', $config, skillsFixtureBody(), null);

    expect($second)->toMatchArray(['created' => 0, 'updated' => 12])
        ->and(Skill::count())->toBe(12);
});

it('adopts a seeded row no source has attributed, and only once', function (): void {
    // The SkillSeeder rows arrive with a name and a match key and no export id. Adoption by name is
    // what stops the import growing a second row for the same skill.
    Skill::create(['name' => 'Gourmand', 'match_key' => 'gourmand']);

    $counts = app(PipelineRunner::class)->run(
        'gametora-skills',
        skillsSourceConfig(),
        skillsFixtureBody(),
        null,
    );

    $gourmand = Skill::where('name', 'Gourmand')->firstOrFail();

    expect($counts)->toMatchArray(['created' => 11, 'updated' => 1])
        ->and(Skill::count())->toBe(12)
        ->and($gourmand->export_id)->toBe(201351)
        ->and($gourmand->sp_cost)->toBe(180);

    // A second run must not be able to repoint the now-attributed row at a different skill by name.
    $other = app(PipelineRunner::class)->run('gametora-skills', skillsSourceConfig(), skillsFixtureBody(), null);
    expect($other)->toMatchArray(['created' => 0, 'updated' => 12])
        ->and(Skill::count())->toBe(12);
});

it('never writes over a row the Trainer entered by hand', function (): void {
    Skill::create([
        'name' => 'Gourmand',
        'match_key' => 'gourmand',
        'sp_cost' => 250,
        'is_manual' => true,
    ]);

    $counts = app(PipelineRunner::class)->run(
        'gametora-skills',
        skillsSourceConfig(),
        skillsFixtureBody(),
        null,
    );

    $manual = Skill::where('name', 'Gourmand')->firstOrFail();

    expect($counts)->toMatchArray(['created' => 11, 'updated' => 0, 'skipped' => 1])
        ->and($manual->sp_cost)->toBe(250)
        ->and($manual->export_id)->toBeNull();
});

it('selects for a Trainer-facing surface exactly the rows the source calls client and Global', function (): void {
    app(PipelineRunner::class)->run('gametora-skills', skillsSourceConfig(), skillsFixtureBody(), null);

    $names = Skill::query()->availableOnGlobal()->orderBy('export_id')->pluck('name')->all();

    // Nine of the twelve. Excluded for two different reasons, and the scope has to catch both: three rows
    // the source marks unreleased on `en`, and within those one that has no client name at all. Two of the
    // nine are the same client name (`Indomitable`), which is what the source states and what Screen D has
    // to render; a scope that returned distinct names would silently drop one.
    expect($names)->toBe([
        'Red Ace',          // 10091,  rarity 3, client-named
        'Certain Victory',  // 110031, rarity 5
        'G1 Averseness',    // 200311, rarity 1
        'Corner Adept ×',   // 200333, rarity 1
        'Indomitable',      // 200471, rarity 2, learnable
        'Gourmand',         // 201351, rarity 2
        'Master of the Sands', // 202001, rarity 2
        "Givin' It 1000%",  // 202391, rarity 2, the row that makes a literal `%` search meaningful
        'Indomitable',      // 300141, rarity 5, the row no card's list reaches
    ])
        ->and(Skill::count())->toBe(12)
        // Neither of the two ways a row can fail the filter may leak through.
        ->and($names)->not->toContain('Check')             // no client name, unreleased
        ->and($names)->not->toContain('Gluttonous Ruler')  // client name, but unreleased (KI-24 §2)
        ->and($names)->not->toContain('Raise My Soul\'s Blade!');
});

it('resolves the document URL through the manifest and records that resolved URL', function (): void {
    Storage::fake('local');
    config(['uma.sources.gametora-skills.delay_ms' => 0]);

    Http::preventStrayRequests();
    Http::fake([
        'gametora.com/data/manifests/umamusume.json' => Http::response(['skills' => 'abcd1234']),
        'gametora.com/data/umamusume/skills.abcd1234.json' => Http::response(skillsFixtureBody(), 200),
    ]);

    $this->artisan('uma:fetch', ['source' => 'gametora-skills'])->assertExitCode(0);

    Http::assertSent(fn (Request $r): bool => str_contains($r->url(), 'skills.abcd1234.json'));

    // Provenance carries the document actually fetched, not the pin declared in config.
    expect(Skill::where('export_id', 200311)->value('source_url'))
        ->toBe('https://gametora.com/data/umamusume/skills.abcd1234.json');
});

it('refuses a manifest hash that is not eight hex characters and uses the pinned URL', function (): void {
    Storage::fake('local');
    config(['uma.sources.gametora-skills.delay_ms' => 0]);

    Http::preventStrayRequests();
    Http::fake([
        // A hostile or merely malformed body: the hash is the one value in the URL that did not come
        // from config/uma.php, so it must be incapable of carrying a path or a host.
        'gametora.com/data/manifests/umamusume.json' => Http::response(['skills' => '../../evil']),
        'gametora.com/data/umamusume/skills.609afe88.json' => Http::response(skillsFixtureBody(), 200),
    ]);

    $this->artisan('uma:fetch', ['source' => 'gametora-skills'])->assertExitCode(0);

    // The degradation is visible as data rather than only as console text: the rows carry the pinned
    // document's URL, because the manifest's answer could not be trusted to name a file.
    expect(Skill::count())->toBe(12)
        ->and(Skill::where('export_id', 200311)->value('source_url'))
        ->toBe('https://gametora.com/data/umamusume/skills.609afe88.json');
});

it('uses the pinned URL when the manifest itself will not answer', function (): void {
    Storage::fake('local');
    config(['uma.sources.gametora-skills.delay_ms' => 0]);

    Http::preventStrayRequests();
    Http::fake([
        'gametora.com/data/manifests/umamusume.json' => Http::response('', 500),
        'gametora.com/data/umamusume/skills.609afe88.json' => Http::response(skillsFixtureBody(), 200),
    ]);

    $this->artisan('uma:fetch', ['source' => 'gametora-skills'])->assertExitCode(0);

    expect(Skill::count())->toBe(12)
        ->and(Skill::where('release_status', ReleaseStatus::GlobalReleased->value)->count())->toBe(9);
});

it('offers the run screen picker only client-named Global skills, and states the cost', function (): void {
    app(PipelineRunner::class)->run('gametora-skills', skillsSourceConfig(), skillsFixtureBody(), null);
    $run = TrainingRun::factory()->create();

    $html = $this->get(route('runs.show', $run))->content();

    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $options = [];
    foreach ($xpath->query('//select[@name="skills[0][skill_id]"]/option') as $option) {
        $options[] = trim((string) $option->textContent);
    }

    // Nine of the twelve imported rows may be offered. `Check` and `Gluttonous Ruler` are excluded because
    // the source marks them unreleased on `en`; the third excluded row carries a client-shaped English
    // name and is still not on Global, which is the case a filter on `name_en` alone would have leaked.
    expect($options)->toContain('Gourmand · 180 SP')
        ->and($options)->toContain('Certain Victory')
        // Ten, not nine: the nine Global rows plus the blank "Choose a skill" option the repeater's
        // spare row needs in order to submit nothing (KI-33's repeater, landed with KI-36's labels).
        // The XPath is scoped to `skills[0][skill_id]`, so the three later rows cannot inflate this.
        ->and($options)->toHaveCount(10)
        ->and(implode(' | ', $options))->not->toMatch('/Gluttonous Ruler|Raise My Soul|Check/')
        // The ruling from conflict row 16 reaches the screen as an absence the Trainer can see.
        // Whitespace is folded because Blade's own line wrapping sits inside the sentence, and a
        // check that failed on indentation would be testing the template's formatting, not its copy.
        ->and((string) preg_replace('/\s+/', ' ', $html))
        ->toContain('hint levels are not shown: no source in this repository settles the per-level reduction');

    // What the fixture's surviving translation is bought for: the run screen renders the client string for
    // every row Global has, and none of these eight renderings appear anywhere on it. Decoded first — a
    // not-contains against escaped HTML could pass because an apostrophe or a `×` was entity-encoded,
    // which is the same failure KI-21 was filed against in the other direction.
    $visible = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    expect(skillsFixtureGlobalRenderings())->toHaveCount(8)
        // Same transformation, one string required present. Without it the eight absences would pass on an
        // extraction that saw nothing, and one of the eight is `'`-escaped in the source markup, so this is
        // also the control that proves the decode step is doing what it is claimed to do.
        ->and($visible)->toContain('Master of the Sands')
        ->and($visible)->not->toContain(...skillsFixtureGlobalRenderings());
});

it('marks a unique skill and states its cost in the recorded groups', function (): void {
    app(PipelineRunner::class)->run('gametora-skills', skillsSourceConfig(), skillsFixtureBody(), null);

    $run = TrainingRun::factory()->create();
    $run->setSkillStatus(Skill::where('name', 'Certain Victory')->firstOrFail(), SkillAcquisition::Acquired, 12);
    $run->setSkillStatus(Skill::where('name', 'Gourmand')->firstOrFail(), SkillAcquisition::Suggested);

    $html = $this->get(route('runs.show', $run))->content();

    // The sparkle is the mark and "Unique" is the word: D-12 forbids a state that lives in a glyph alone.
    expect($html)->toContain('✦ Unique')
        ->and($html)->toContain('· 180 SP')
        ->and($html)->toContain('· turn 12');
});
