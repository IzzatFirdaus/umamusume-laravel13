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
 * own targets in `resources/js/pages/Runs/Show.vue`. The rendered heights are measured in the
 * browser pass (tests/browser/run-detail.spec.ts).
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

    test()->get('/training-runs/'.$run->id)
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
    test()->get('/training-runs/'.$run->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('run.export_csv_url', route('runs.export', ['run' => $run, 'format' => 'csv']))
            ->where('run.export_json_url', route('runs.export', ['run' => $run, 'format' => 'json'])));

    $source = runViewSource('resources/js/pages/Runs/Show.vue');

    expect(sourceClassOf($source, '/<a[^>]*:href="run\.export_csv_url"[^>]*>/'))->toContain('min-h-11')
        ->and(sourceClassOf($source, '/<a[^>]*:href="run\.export_json_url"[^>]*>/'))->toContain('min-h-11')
        ->and(sourceClassOf($source, '/<a[^>]*href="\/skills"[^>]*>/'))->toContain('min-h-11')
        ->and(sourceClassOf($source, '/<summary[^>]*>\s*Correct a turn by hand/s'))->toContain('min-h-11');

    $layout = runViewSource('resources/js/layouts/AppLayout.vue');

    /*
     * The census of the nav, so the sweep above cannot pass on an empty list. Deviation from
     * the Blade shell's six links, stated because the run page now renders AppLayout's 2.0
     * navigation: nine destinations and one named absence (`to: null`), which renders as a
     * disabled span; the whole list renders twice (desktop aside and mobile bar). Each of
     * the four classes a destination can render with is asserted to carry the floor, so a
     * destination that forgets it fails here rather than sliding past an unchanged count.
     *
     * The absence count was two until slice D5 landed the Legacy Lab: that slice filled the
     * one `to: null` placeholder it owned rather than appending a tenth destination, so the
     * total is still nine and only the Veterans library is still a named absence. The
     * assertion is the count, not the value 2, so the next slice to fill Veterans lowers it
     * deliberately instead of being a silent failure.
     */
    preg_match('/const items = \[(.*?)\];/s', $layout, $items);

    expect(substr_count($items[1] ?? '', 'label:'))->toBe(9)
        ->and(substr_count($items[1] ?? '', 'to: null'))->toBe(1)
        ->and(substr_count($items[1] ?? '', "label: 'Legacy Lab', to: '/legacy'"))->toBe(1);

    foreach (['linkClass', 'disabledClass', 'mobileClass', 'mobileDisabledClass'] as $constant) {
        preg_match('/const '.$constant.'\s*=\s*\'([^\']*)\'/s', $layout, $declared);

        expect($declared[1] ?? '', $constant)->toContain('min-h-11');
    }
});
