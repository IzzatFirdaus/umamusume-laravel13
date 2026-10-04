<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

/*
 * The component library the UI/UX brief asks for. Each component is asserted on its RENDERED
 * output, not its source: the class list a Trainer never sees is not the contract, the rendered
 * text and the resolved structure are (R71).
 *
 * Two repo-wide gates ride along, because a new component is exactly where they get broken:
 * no `dark:` utility and no skeleton palette class (G-18/G-19), and no interactive element
 * parked inside an inert <template> (R71).
 */

/**
 * Every brief component, rendered through Blade so the component compiler resolves it.
 *
 * @return array<string, string>
 */
function componentMarkup(): array
{
    return [
        'app-button primary' => '<x-app-button>Save</x-app-button>',
        'app-button banner' => '<x-app-button variant="banner">Start run</x-app-button>',
        'app-button discipline' => '<x-app-button variant="discipline" shortcut="1">Speed</x-app-button>',
        'capsule-header' => '<x-capsule-header title="Aptitudes" />',
        'grade-badge' => '<x-grade-badge grade="B+" />',
        'energy-gauge' => '<x-energy-gauge :energy="42" />',
        'deck-editor' => "<x-deck-editor :slots=\"[['name' => 'Kitasan Black', 'rarity' => 'SSR', 'type' => 'Speed', 'limit_break' => 3, 'level' => 45]]\" :legend=\"['Speed' => 2]\" />",
        'run-header' => '<x-run-header :turn="12" scenario="URA Finale" :energy="42" :fans="12000" :fan-gate="60000" />',
    ];
}

/**
 * Every Blade view's source, keyed by its path relative to the project root.
 *
 * RecursiveDirectoryIterator, not glob('**'): PHP's glob does not expand `**`, so a
 * glob-based sweep reads one directory and passes without having looked at the rest.
 *
 * @return array<string, string>
 */
function bladeViewSources(): array
{
    $walk = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('resources/views'), FilesystemIterator::SKIP_DOTS)
    );

    $sources = [];

    foreach ($walk as $file) {
        // getExtension() answers "php" for foo.blade.php, so the suffix is matched on the
        // filename. Filtering on the extension silently scans nothing.
        if (str_ends_with($file->getFilename(), '.blade.php')) {
            // Normalised to forward slashes so the expectation below is one literal on
            // Windows and on a POSIX host alike.
            $sources[str_replace(DIRECTORY_SEPARATOR, '/', str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname()))] =
                (string) file_get_contents($file->getPathname());
        }
    }

    return $sources;
}

/**
 * R71: a component that renders its controls inside an inert <template> looks present in the
 * source and is dead in the browser. Resolve the rendered document and assert none.
 */
function assertNoInteractiveElementInsideTemplate(string $html): void
{
    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML('<!DOCTYPE html><html><body>'.$html.'</body></html>');
    libxml_clear_errors();

    foreach ($dom->getElementsByTagName('template') as $template) {
        foreach (['button', 'input', 'select', 'a', 'textarea'] as $tag) {
            expect($template->getElementsByTagName($tag)->length)
                ->toBe(0, "A <{$tag}> sits inside an inert <template> (R71).");
        }
    }
}

it('renders every brief component from tokens, with no theme fork and no skeleton palette class', function (string $markup): void {
    $html = Blade::render($markup);

    expect($html)
        ->not->toMatch('/\bdark:/')
        ->not->toMatch('/\b(?:zinc|gray|neutral|stone|slate|amber|red|blue|emerald)-[0-9]/')
        ->not->toMatch('/\b(?:bg|text|border)-white\b/');

    assertNoInteractiveElementInsideTemplate($html);
})->with(componentMarkup());

it('meets the 44px control contract on every button shape', function (): void {
    foreach (['primary', 'secondary', 'danger', 'banner'] as $variant) {
        expect(Blade::render("<x-app-button variant=\"{$variant}\">Go</x-app-button>"))->toContain('min-h-11');
    }

    // The Discipline shape is a 64px circle, not a 44px bar; it still clears the floor.
    expect(Blade::render('<x-app-button variant="discipline" shortcut="1">Speed</x-app-button>'))
        ->toContain('size-16')
        ->toContain('>1<');
});

it('strips a grade modifier before the fill lookup', function (): void {
    expect(Blade::render('<x-grade-badge grade="B+" />'))->toContain('bg-grade-b')
        ->and(Blade::render('<x-grade-badge grade="SS" />'))->toContain('bg-grade-ss')
        ->and(Blade::render('<x-grade-badge grade="A" />'))->toContain('bg-grade-a');
});

it('names the energy state word at each threshold', function (): void {
    expect(Blade::render('<x-energy-gauge :energy="80" />'))->toContain('Safe')
        ->and(Blade::render('<x-energy-gauge :energy="40" />'))->toContain('Caution')
        ->and(Blade::render('<x-energy-gauge :energy="10" />'))->toContain('Danger')
        ->and(Blade::render('<x-energy-gauge />'))->toContain('Energy N/A');
});

it('prints the fan shortfall only when both figures are recorded', function (): void {
    expect(Blade::render('<x-run-header :turn="12" scenario="URA Finale" :fans="12000" :fan-gate="60000" />'))
        ->toContain('Turn 12')
        ->toContain('short 48,000')
        ->and(Blade::render('<x-run-header :turn="12" />'))->toContain('Fans N/A');
});

it('renders the capsule header with the lattice motif and a word', function (): void {
    expect(Blade::render('<x-capsule-header title="Aptitudes" />'))
        ->toContain('lattice-bleed')
        ->toContain('h-11')
        ->toContain('Aptitudes');
});

it('renders six deck slots with limit-break diamonds and the type legend', function (): void {
    $html = Blade::render('<x-deck-editor :slots="$slots" :legend="$legend" />', [
        'slots' => [['name' => 'Kitasan Black', 'rarity' => 'SSR', 'type' => 'Speed', 'limit_break' => 3, 'level' => 45]],
        'legend' => ['Speed' => 2],
    ]);

    expect($html)
        ->toContain('Kitasan Black')
        ->toContain('SSR')
        ->toContain('Lv 45')
        ->toContain('Limit break 3 of 4')
        ->toContain('Speed 2')
        ->toContain('Not equipped');
});

/**
 * A shared visual is worth extracting exactly once. Both of these were defined twice and
 * rendered from the second copy: `capsule-header` on eight panels, `grade-badge` in the
 * stat band. A duplicate that renders identically today still drifts, and the drift is
 * invisible until a contrast pass or a capture contradicts it.
 */
it('defines each shared visual in exactly one view', function (string $marker, string $owner): void {
    $holders = [];

    foreach (bladeViewSources() as $path => $source) {
        if (str_contains($source, $marker)) {
            $holders[] = $path;
        }
    }

    expect($holders)->toBe(['resources/views/components/'.$owner]);
})->with([
    // The lattice bleed is the motif only a capsule header carries (DESIGN.md §2.3), and
    // the eight panels that hand-copied the div had already moved away from the component's
    // own padding and type size while each one claimed to be the same header.
    'capsule header' => ['lattice-bleed', 'capsule-header.blade.php'],
    // KI-8 crashed on the banding emitting seventeen labels against a nine-key map, which
    // is what a second copy of that map invites: one owner, one place to add a tenth letter.
    'grade fill map' => ['bg-grade-g', 'grade-badge.blade.php'],
]);
