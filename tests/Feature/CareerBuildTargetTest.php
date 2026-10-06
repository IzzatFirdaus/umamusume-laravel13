<?php

declare(strict_types=1);

use App\Enums\BuildPurpose;
use App\Models\Advisor\BuildTargetPayload;
use App\Models\TrainingRun;
use App\Services\Career\SetupDraft;
use Inertia\Testing\AssertableInertia as Assert;

// SCREEN-005 (`ADR-0020` §1; plan §8 D4). Step 3 of the setup wizard: the build target entered
// before a run exists, written to the same session draft as steps 1 and 2. Props, the draft
// round-trip, the ceiling clamp and the glyph-uniqueness sweep live here; rendered copy, the
// keyboard reorder path, the 44px sweep and 320px reflow live in
// `tests/browser/career-build-target.spec.ts`.

/**
 * A valid draft-target payload. Named apart from the run-scoped helper in
 * `StoreBuildTargetRequestTest` because Pest loads every test file into one process and a second
 * `targetPayload()` would be a fatal redeclare.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function draftTargetPayload(array $overrides = []): array
{
    return array_merge([
        'purpose' => 'StoryClear',
        'distance' => 'Medium',
        'surface' => 'Turf',
        'style' => 'Pace Chaser',
        'targets' => ['Speed' => 900, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500],
        'skill_priorities' => ['Corner Adept'],
    ], $overrides);
}

it('renders the target step with the base caps, the four purposes and nothing pre-filled', function (): void {
    $this->get(route('career.target'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/BuildTarget')
            // No scenario chosen yet, so the ceilings are the base cap with no scenario bonus:
            // `ScenarioCaps::forRun(null)`'s own answer, not a bonus the Trainer never picked.
            ->has('caps', 5)
            ->where('caps.Speed', 1200)
            ->where('caps.Wit', 1200)
            ->where('statOrder', ['Speed', 'Stamina', 'Power', 'Guts', 'Wit'])
            // The four stored cases, labelled through `uma.build_purpose` (the map the enum's
            // docblock owes and `HasLabel` looks up by case name), and nothing else.
            ->where('purposeOptions', [
                ['value' => 'StoryClear', 'label' => 'Story Clear'],
                ['value' => 'ChampionsMeeting', 'label' => 'Champions Meeting'],
                ['value' => 'ParentFarming', 'label' => 'Parent Farming'],
                ['value' => 'SkillFarming', 'label' => 'Skill Farming'],
            ])
            // The option vocabularies arrive from `BuildTargetPayload`'s constants rather than being
            // re-typed in the page, so the form and the payload's own reader cannot drift apart.
            ->where('distanceBands', BuildTargetPayload::DISTANCE_BANDS)
            ->where('surfaces', BuildTargetPayload::SURFACES)
            ->where('styles', BuildTargetPayload::STYLES)
            // Nothing entered yet: the page names the absence and pre-fills no zeroes.
            ->where('target', null)
            ->where('scenarioLabel', null)
            ->where('scenarioPending', true));

    expect(TrainingRun::count())->toBe(0);
});

it('reads the ceilings off the draft scenario, with no run row anywhere', function (): void {
    SetupDraft::write(['scenario' => 'trackblazer']);

    $this->get(route('career.target'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Trackblazer's own bonus row: Stamina +700, Wit +300, the rest on the base cap.
            ->where('caps.Stamina', 1900)
            ->where('caps.Wit', 1500)
            ->where('caps.Speed', 1200)
            ->where('scenarioLabel', 'Trackblazer')
            ->where('scenarioPending', false));

    // The whole reason the clamp reads `SetupDraft::planningRun()`: the ceiling is answered for a
    // career that does not exist in the database yet.
    expect(TrainingRun::count())->toBe(0);
});

it('stores the entered target in the setup draft and reads it back on the next render', function (): void {
    SetupDraft::write(['scenario' => 'ura_finale']);

    $this->put(route('career.target.store'), draftTargetPayload())
        ->assertRedirect(route('career.target'));

    // `payload()`'s own key order, rebuilt from the stat matrix rather than from the post order.
    expect(SetupDraft::buildTarget())->toBe(draftTargetPayload());
    expect(TrainingRun::count())->toBe(0);

    $this->get(route('career.target'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('target.purpose', 'StoryClear')
            ->where('target.targets.Speed', 900)
            ->where('target.skill_priorities.0', 'Corner Adept'));
});

it('keeps the stored target when an earlier step writes the draft again', function (): void {
    // The draft is one session bag. A step 1 or 2 write must merge over it rather than rebuild it
    // from the two keys `read()` narrows to, or going back to re-pick a scenario would silently
    // drop the target step 3 recorded.
    SetupDraft::write(['scenario' => 'ura_finale']);

    $this->put(route('career.target.store'), draftTargetPayload())->assertRedirect();

    $this->put(route('career.scenario.store'), ['scenario' => 'trackblazer'])->assertRedirect();

    expect(SetupDraft::buildTarget())->toBe(draftTargetPayload());
    expect(SetupDraft::read())->toBe(['scenario' => 'trackblazer', 'umamusume_id' => null]);
});

it('refuses a stat above the draft ceiling and names the bound it enforced', function (): void {
    // URA Finale's Speed ceiling is 1,400 (1,200 base + 200): the same number the run-scoped write
    // enforces, because there is one rule set. Asserted from both sides so the rule cannot pass by
    // refusing everything or by accepting everything.
    SetupDraft::write(['scenario' => 'ura_finale']);

    $this->put(route('career.target.store'), draftTargetPayload(['targets' => [
        'Speed' => 1401, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500,
    ]]))->assertSessionHasErrors(['targets.Speed' => 'Speed must be between 0 and 1400.']);

    expect(SetupDraft::buildTarget())->toBeNull();

    $this->put(route('career.target.store'), draftTargetPayload(['targets' => [
        'Speed' => 1400, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500,
    ]]))->assertSessionHasNoErrors();

    // With no scenario chosen the ceiling is the base cap, and the refusal cites that number.
    SetupDraft::reset();

    $this->put(route('career.target.store'), draftTargetPayload(['targets' => [
        'Speed' => 1201, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500,
    ]]))->assertSessionHasErrors(['targets.Speed' => 'Speed must be between 0 and 1200.']);
});

it('refuses a value vocabulary the payload does not store', function (): void {
    // The design target names a fifth purpose this tool does not record, and a hand-made POST
    // cannot widen the vocabulary past `BuildPurpose`'s four cases.
    $this->put(route('career.target.store'), draftTargetPayload(['purpose' => 'Competitive Build']))
        ->assertSessionHasErrors('purpose');

    $this->put(route('career.target.store'), draftTargetPayload(['distance' => 'Marathon']))
        ->assertSessionHasErrors('distance');

    $this->put(route('career.target.store'), draftTargetPayload(['surface' => 'Dirt Track']))
        ->assertSessionHasErrors('surface');

    $this->put(route('career.target.store'), draftTargetPayload(['style' => 'Betweener']))
        ->assertSessionHasErrors('style');

    expect(SetupDraft::buildTarget())->toBeNull();
});

it('refuses a target that is not the five stats', function (): void {
    // A sixth stat is refused by the key restriction, and a missing one is named rather than
    // reported against the whole map.
    $this->put(route('career.target.store'), draftTargetPayload([
        'targets' => ['Speed' => 900, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600, 'Wit' => 500, 'Extra' => 1],
    ]))->assertSessionHasErrors('targets');

    $this->put(route('career.target.store'), draftTargetPayload([
        'targets' => ['Speed' => 900, 'Stamina' => 800, 'Power' => 700, 'Guts' => 600],
    ]))->assertSessionHasErrors('targets.Wit');

    expect(SetupDraft::buildTarget())->toBeNull();
});

it('leaves the run-scoped build target write exactly as it was', function (): void {
    // C1's boundary is untouched by the draft path: the same rule set resolves the clamp against
    // the run on `runs.build-target.update`, and `payload()` still writes the whole object.
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $this->put(route('runs.build-target.update', $run), draftTargetPayload())
        ->assertRedirect(route('runs.show', $run));

    expect($run->fresh()->buildTarget()?->toArray())->toBe(draftTargetPayload());
});

it('keeps the four trust glyphs in ProvenanceBadge alone', function (): void {
    $badge = 'resources/js/components/ProvenanceBadge.vue';
    $badgeSource = (string) file_get_contents(base_path($badge));

    // The badge defines all four; the sweep below proves no other source repeats either sweepable
    // glyph. (`toContain` is not given a message argument here: Pest 4's is variadic, so a message
    // would be read as a second needle.)
    foreach (['✓', '∑'] as $glyph) {
        expect($badgeSource)->toContain($glyph);
    }

    $others = [];

    $walk = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('resources/js'), FilesystemIterator::SKIP_DOTS),
    );

    foreach ($walk as $file) {
        // Relative, forward-slashed, taken by offset: `str_replace` on both needles at once would
        // rewrite the separators first and leave the absolute prefix standing, which is how an
        // exclusion keyed on the badge's own path silently stops excluding it.
        $path = str_replace('\\', '/', mb_substr($file->getPathname(), mb_strlen(base_path()) + 1));

        if (preg_match('/(\.vue|\.ts)$/', $file->getFilename()) !== 1 || $path === $badge) {
            continue;
        }

        $source = (string) file_get_contents($file->getPathname());

        // The two sweepable glyphs, and only them: `~` is a documented brief quote in two Legacy
        // comments and `?` is a ternary everywhere, so a raw sweep for either proves nothing.
        foreach (['✓', '∑'] as $glyph) {
            if (str_contains($source, $glyph)) {
                $others[] = "{$path} carries {$glyph}";
            }
        }
    }

    expect($others)->toBe([]);
});

it('keeps every scenario name and target vocabulary out of the step-3 components', function (): void {
    // `config/scenarios.php` is the only source of scenario names in the layout path (AGENTS.md §7,
    // D-240, gate G-33), and the vocabularies ship from the controller so the page cannot drift
    // from `BuildTargetPayload`'s constants. The files that could carry either are the two SFCs
    // this slice adds.
    $sources = [
        'resources/js/pages/Career/BuildTarget.vue',
        'resources/js/components/ProvenanceBadge.vue',
    ];

    $scenarioNames = array_map(
        static fn (array $definition): string => (string) $definition['label'],
        array_values((array) config('scenarios.scenarios')),
    );

    $vocabulary = array_merge(
        BuildTargetPayload::DISTANCE_BANDS,
        BuildTargetPayload::SURFACES,
        BuildTargetPayload::STYLES,
        array_map(static fn (mixed $order): string => (string) $order, (array) config('scenarios.stat_order')),
        array_map(static fn (BuildPurpose $case): string => $case->label(), BuildPurpose::cases()),
    );

    // Word-boundary matching, not a substring: one stat's name is a substring of ordinary JS
    // (`startsWith`), so `str_contains` would flag any page that mentions it. A quoted or standalone
    // value still matches, which is the shape a hardcoded option would take.
    $repeats = static fn (string $source, string $value): bool => preg_match(
        '/\b'.preg_quote($value, '/').'\b/',
        $source,
    ) === 1;

    foreach ($sources as $path) {
        $source = (string) file_get_contents(base_path($path));

        $violations = array_values(array_filter(
            $scenarioNames,
            static fn (string $name): bool => $repeats($source, $name),
        ));

        foreach (array_keys((array) config('scenarios.scenarios')) as $key) {
            if ($repeats($source, (string) $key)) {
                $violations[] = (string) $key;
            }
        }

        foreach ($vocabulary as $value) {
            if ($repeats($source, $value)) {
                $violations[] = $value;
            }
        }

        // Collected rather than asserted per value with a message argument: Pest 4's `toContain` is
        // variadic, so a trailing message would be read as a second needle and a negated assertion
        // would pass whatever the file held.
        expect($violations)->toBe([], "{$path} repeats: ".implode(', ', $violations));
    }
});
