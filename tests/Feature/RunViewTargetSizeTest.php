<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Enums\SkillAcquisition;
use App\Models\Skill;
use App\Models\TrainingRun;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * L-F01 regression gate (WCAG 2.2 SC 2.5.8). The navigation, the two export links, the helper
 * link and the summary disclosure must all carry `min-h-11` (44px, the project's floor idiom
 * from KI-37), and the skill-row controls must still carry the `h-11` KI-37 closed with.
 *
 * The page is component-rendered now (ADR-0020 §1), so the classes are read from the component
 * sources that render them: the nav is in `resources/js/layouts/AppLayout.vue`, the run page's
 * surviving targets in `resources/js/pages/Career/Cockpit.vue`. The rendered heights are measured
 * in the browser pass (tests/browser/run-detail.spec.ts).
 */

function runViewSource(string $path): string
{
    return (string) file_get_contents(base_path($path));
}

/**
 * The class attribute of the first element whose opening tag matches the pattern, or an empty
 * string when nothing matches: the empty string fails every floor assertion below, so a
 * component that drops a control fails here rather than passing vacuously.
 */
function sourceClassOf(string $source, string $pattern): string
{
    if (preg_match($pattern, $source, $tag) !== 1) {
        return '';
    }

    preg_match('/class="([^"]*)"/', $tag[0], $class);

    return $class[1] ?? '';
}

it('keeps the skill-row controls at the KI-37 height', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    // One recorded skill so the collapsed picker has a closed row beside the open one,
    // which is what makes the "Change row N" link half of the L-F01 claim non-vacuous.
    $skill = Skill::factory()->create([
        'release_status' => ReleaseStatus::GlobalReleased->value,
        'name_is_client' => true,
    ]);
    $run->setSkillStatus($skill, SkillAcquisition::Acquired);

    test()->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('skillGroups.1.key', 'Acquired')
            ->where('skillGroups.1.skills.0.id', $skill->id));

    $source = runViewSource('resources/js/pages/Runs/Show.vue');

    /*
     * Re-pointed 2026-10-03 (B.6), and again with the Inertia port: the ten `skill_id` selects
     * collapsed to one open picker, so the visible picker control is now the open select plus
     * the "Change row N" links that open a closed row. The claim is unchanged: every control a
     * Trainer reaches in the skill rows meets the KI-37 44px floor. The hidden per-row
     * `skill_id` inputs are deliberately excluded, because they are not controls and must not
     * carry a height.
     */
    expect(sourceClassOf($source, '/<select[^>]*v-model="row\.skill_id"[^>]*>/'))->toContain('h-11')
        ->and(sourceClassOf($source, '/<button[^>]*>\s*Change row/s'))->toContain('min-h-11')
        ->and(sourceClassOf($source, '/<select[^>]*v-model="row\.status"[^>]*>/'))->toContain('h-11')
        ->and(sourceClassOf($source, '/<input[^>]*v-model="row\.turn_acquired"[^>]*>/'))->toContain('h-11')
        ->and(sourceClassOf($source, '/<button[^>]*>\s*Save skill status/s'))->toContain('h-11');
});

it('gives every L-F01 target the min-h-11 floor', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    // The two export links are payload-driven; the floor itself is a class on the component
    // that renders them.
    test()->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('run.export_csv_url', route('runs.export', ['run' => $run, 'format' => 'csv']))
            ->where('run.export_json_url', route('runs.export', ['run' => $run, 'format' => 'json'])));

    $source = runViewSource('resources/js/pages/Career/Cockpit.vue');

    expect(sourceClassOf($source, '/<a[^>]*:href="props\.run\.export_csv_url"[^>]*>/'))->toContain('min-h-11')
        ->and(sourceClassOf($source, '/<a[^>]*:href="props\.run\.export_json_url"[^>]*>/'))->toContain('min-h-11')
        ->and(sourceClassOf($source, '/<summary[^>]*>\s*Correct turn/s'))->toContain('min-h-11');

    $layout = runViewSource('resources/js/layouts/AppLayout.vue');

    /*
     * The census of the nav, so the sweep above cannot pass on an empty list: ten destinations, none of
     * them a named absence now that the Veteran library landed as D16's read half. The list renders twice
     * (desktop aside and mobile bar), and the mobile bar renders a third set inside its More disclosure.
     * Each of the five classes a destination can render with is asserted to carry the floor, so a
     * destination that forgets it fails here rather than sliding past an unchanged count.
     *
     * Four destinations are pinned by target as well as counted, because the nav audit changed where they
     * point rather than how many there are. "New Career" goes to the wizard's step 1 instead of the
     * pre-2.0 `runs.create` form, "Careers" is the run list, which had no clickable inbound link at all,
     * and "Veterans" is the library, which was the nav's one `to: null` placeholder. Reverting any of
     * them silently orphans a screen, so it has to fail here.
     *
     * The mobile bar is `design-2.0` §41's shape: four slots plus a More disclosure, and explicitly not
     * the sidebar shrunk down. The ten-row `overflow-x-auto` strip that used to render the whole sidebar
     * is asserted absent, because that is the shape §41 forbids and the reason nothing at 320px looked
     * scrollable.
     */
    preg_match('/const items: NavItem\[\] = \[(.*?)\];/s', $layout, $items);

    expect(substr_count($items[1] ?? '', 'label:'))->toBe(10)
        ->and(substr_count($items[1] ?? '', 'to: null'))->toBe(0)
        ->and(substr_count($items[1] ?? '', 'inBar: true'))->toBe(4)
        // Nine of the ten destinations hover-prefetch. The tenth is "New Career", excluded because the
        // wizard renders session-draft state and Inertia reuses a prefetched response for 30 seconds by
        // default, which would show the choice the Trainer has just overwritten. Counted, so a tenth
        // prefetching link is a deliberate act rather than a slip.
        ->and(substr_count($items[1] ?? '', 'prefetches: true'))->toBe(9)
        ->and(substr_count($items[1] ?? '', "label: 'New Career'"))->toBe(1)
        ->and((bool) preg_match("/\{ label: 'New Career',[^}]*prefetches: false/", $items[1] ?? ''))->toBeTrue()
        // The flag has to reach the render sites, or it is decoration in a data array.
        ->and(substr_count($layout, "? 'hover' : false"))->toBe(3)
        ->and(substr_count($layout, '>not built</span>'))->toBe(3);

    foreach ([
        'New Career' => '/career/setup/scenario',
        'Careers' => '/training-runs',
        'Veterans' => '/veterans',
        'Legacy Lab' => '/legacy',
    ] as $label => $path) {
        expect((bool) preg_match("#\\{ label: '$label',[^}]*to: '".preg_quote($path, '#')."'#", $items[1] ?? ''))
            ->toBeTrue($label.' points at '.$path);
    }

    foreach (['Home', 'Career', 'Legacy', 'Deck'] as $slot) {
        expect(substr_count($items[1] ?? '', "mobileLabel: '$slot'"))->toBe(1, $slot);
    }

    // The §41 violation, stated as its own assertion so a re-introduction is named rather than counted.
    // Matched against class attributes only: the file's own prose quotes the utility to explain why it
    // is gone, and a whole-file `not->toContain` would fail on that explanation.
    expect($layout)->not->toMatch('/class="[^"]*overflow-x-auto/');

    // Five slots that divide the viewport rather than five `shrink-0` labels that add up wider than 320px
    // and re-create the overflow the bar exists to remove.
    expect($layout)->toMatch('/const mobileBarClass\s*=\s*\'([^\']*)\'/s')
        ->and($layout)->toMatch('/const mobileBarClass\s*=\s*\'[^\']*flex-1 min-w-0/');

    // The disclosure is the native widget, so the expanded state and the keyboard path come from the
    // platform. Escape closes it and focus returns to the summary.
    expect($layout)->toContain('<details')
        ->and($layout)->toContain('@keydown.esc="closeMore"');

    foreach (['linkClass', 'disabledClass', 'mobileClass', 'mobileBarClass', 'mobileDisabledClass', 'mobileMoreClass'] as $constant) {
        preg_match('/const '.$constant.'\s*=\s*\'([^\']*)\'/s', $layout, $declared);

        expect($declared[1] ?? '', $constant)->toContain('min-h-11');
    }
});
