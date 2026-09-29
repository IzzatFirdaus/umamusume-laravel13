<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\View\ViewException;

/*
 * Screen B for the baseline scenario (owner directive: build URA first, defer the
 * Trackblazer and Unity Cup variations until the baseline is verified).
 *
 * The baseline is not "the version with things removed" — URA Finale is defined by
 * absence (config/scenarios.php:74, "Defined by absence: no team system, no shop,
 * no scenario currency"). So the assertions that matter here are negative: the URA
 * turn must not name a shop, a team race, Grade Points or Shop Coins, because a step
 * or a caption mentioning them would state a mechanic the run has no source for.
 */

/**
 * URA's turn flow, straight from the composition matrix.
 *
 * @return array<string, mixed>
 */
function uraStepProps(array $overrides = []): array
{
    return array_merge([
        'scenario' => 'ura_finale',
        'current' => 'training',
        'selected' => 'train-speed',
        'energy' => 78,
        'choices' => [
            ['key' => 'train-speed', 'label' => 'Speed Work', 'detail' => 'Facility Lv 4 · personal repetition', 'facility_level' => 4],
            ['key' => 'rest', 'label' => 'Rest', 'detail' => 'Recovers Energy, spends the turn'],
            ['key' => 'race', 'label' => 'Race entry', 'detail' => 'Fans and Skill Points. Goal race needs 12,000 fans'],
        ],
        'preview' => [
            ['direction' => 'up', 'text' => '+8 Speed'],
            ['direction' => 'up', 'text' => '+4 Power'],
            ['direction' => 'down', 'text' => '-19 Energy'],
        ],
    ], $overrides);
}

function renderGuidedStep(array $props): string
{
    return Blade::render(
        '<x-guided-step :scenario="$scenario" :current="$current" :selected="$selected" :choices="$choices"'
        .' :preview="$preview" :energy="$energy" />',
        $props,
    );
}

it('walks the baseline turn flow in config order and adds no step', function (): void {
    $html = renderGuidedStep(uraStepProps(['current' => 'outcome']));

    expect($html)
        ->toContain('Step 2 of 3')
        ->toContain('Record outcome')
        // Unity Cup's facility step and Trackblazer's shop step belong to scenarios
        // that own them. Naming either here would be D-220 in the step rail.
        ->not->toContain('Choose facility')
        ->not->toContain('Spend Shop Coins');
});

it('states no mechanic the baseline scenario does not have', function (): void {
    $html = renderGuidedStep(uraStepProps());

    expect($html)
        ->not->toContain('Team Race')
        ->not->toContain('Shop Coin')
        ->not->toContain('Grade Point')
        ->not->toContain('Spirit Burst')
        ->not->toContain('teammate')
        ->not->toContain('Team Rank');
});

it('carries the fan gate on the race option, because the baseline calendar has one', function (): void {
    $html = renderGuidedStep(uraStepProps());

    expect($html)->toContain('Goal race needs 12,000 fans');
});

it('colours increases orange and decreases blue', function (): void {
    $html = renderGuidedStep(uraStepProps());

    // The client's pair is inverted from the web's green-up / red-down habit, so a
    // sign character is not enough: the class is the contract (research §3.2).
    expect($html)
        ->toMatch('/text-up[^>]*>\s*\+8 Speed/')
        ->toMatch('/text-down[^>]*>\s*-19 Energy/')
        ->not->toContain('text-green');
});

it('places the Energy gauge immediately before the control that spends it', function (): void {
    $html = renderGuidedStep(uraStepProps());

    $gauge = strpos($html, 'aria-label="Energy 78 of 100"');
    $confirm = strpos($html, 'Confirm turn');

    // D-171 / G-30: a gauge carried only in the header is read once at the top of
    // the screen and forgotten before the button.
    expect($gauge)->toBeInt()->and($confirm)->toBeInt()
        ->and($gauge)->toBeLessThan($confirm)
        ->and($html)->toContain('Energy 78/100');
});

it('shows the unverified-label treatment rather than promoting a guess', function (): void {
    $html = renderGuidedStep(uraStepProps([
        'choices' => [
            ['key' => 'mood', 'label' => 'Mood adjustment', 'detail' => 'Raises Mood. Client label unverified', 'unverified' => true],
        ],
    ]));

    expect($html)->toContain('Unverified');
});

it('contains no scenario name anywhere in the component', function (): void {
    $source = (string) file_get_contents(resource_path('views/components/guided-step.blade.php'));
    $code = (string) preg_replace(['#\{\{--.*?--\}\}#s', '#/\*.*?\*/#s'], '', $source);

    foreach (array_keys(config('scenarios.scenarios')) as $key) {
        expect($code)->not->toContain($key);
    }
});

it('refuses a scenario it has no descriptor for', function (): void {
    renderGuidedStep(uraStepProps(['scenario' => 'jp_only_event']));
})->throws(ViewException::class, 'Unknown scenario [jp_only_event] for x-guided-step.');
