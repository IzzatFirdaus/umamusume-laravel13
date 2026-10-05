<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * The component library the UI/UX brief asks for.
 *
 * Every brief component had a Blade tag and a Vue twin, and the port moved the twin onto
 * the pages (ADR-0020 §1, B1), so these cases read the twin's own source instead of a
 * rendered document: the class list a Trainer never sees is not the contract, but with the
 * rendering server gone the twin is what the browser is handed, and the browser copy lives
 * in `tests/browser/run-detail.spec.ts`.
 *
 * Two repo-wide gates ride along, because a new component is exactly where they get broken:
 * no `dark:` utility and no skeleton palette class (G-18/G-19), and no interactive element
 * parked inside an inert wrapper (R71). The first is also swept over both source trees by
 * DesignTokensTest; the copy here is the brief's own list, so a component added to the brief
 * is covered the day it is added rather than the day somebody remembers the wider gate.
 */

/**
 * Every brief component that has a Vue twin, keyed by its brief name.
 *
 * `deck-editor` is deliberately absent: its replacement is `Support/Builder.vue` (D6,
 * SCREEN-007), which is not built, so there is nothing to assert against yet. The case that
 * used to render it says why, rather than being dropped quietly.
 *
 * @return array<string, string>
 */
function libraryComponents(): array
{
    return [
        'app-button' => 'resources/js/components/AppButton.vue',
        'capsule-header' => 'resources/js/components/CapsuleHeader.vue',
        'energy-gauge' => 'resources/js/components/EnergyGauge.vue',
        'grade-badge' => 'resources/js/components/GradeBadge.vue',
    ];
}

/**
 * The brief's components and the retired Blade tags they replaced, as positional pairs.
 *
 * Positional because Pest's `with()` reads an associative value as named arguments, and the
 * keys here are brief names rather than parameter names.
 *
 * @return list<array{string, string}>
 */
function retiredBladeComponents(): array
{
    $pairs = [];

    foreach ([
        'app-button' => 'app-button',
        'capsule-header' => 'capsule-header',
        'deck-editor' => 'deck-editor',
        'energy-gauge' => 'energy-gauge',
        'grade-badge' => 'grade-badge',
        'run-header' => 'run-header',
    ] as $brief => $tag) {
        $pairs[] = [$brief, $tag];
    }

    return $pairs;
}

/**
 * The brief's components that have a Vue twin, as positional pairs.
 *
 * @return list<array{string, string}>
 */
function libraryComponentPairs(): array
{
    return array_map(
        static fn (string $path, string $brief): array => [$brief, $path],
        array_values(libraryComponents()),
        array_keys(libraryComponents()),
    );
}

it('keeps every brief component free of the withdrawn classes', function (string $component, string $path): void {
    $source = (string) file_get_contents(base_path($path));

    expect($source)
        ->not->toMatch('/\bdark:/')
        ->not->toMatch('/\b(?:zinc|gray|neutral|stone|slate|amber|red|blue|emerald)-[0-9]/')
        ->not->toMatch('/\b(?:bg|text|border)-white\b/');
})->with(libraryComponentPairs());

it('parks no interactive element inside an inert wrapper', function (string $component, string $path): void {
    // R71, in the form the twin can break it: a `<template>` with no directive renders nothing,
    // so a control inside one looks present in the source and is dead in the browser. A `<template>`
    // that carries a directive is the platform's own conditional and grouping, which is fine.
    //
    // The component's own root template is the first `<template>` in the file and carries no
    // directive, so it is skipped by offset rather than by name: it holds everything, including
    // every control, and reading it as a violation would fail every component that has one.
    $source = (string) file_get_contents(base_path($path));

    preg_match('#<template\b#', $source, $root, PREG_OFFSET_CAPTURE);
    $rootAt = $root[0][1] ?? -1;

    preg_match_all(
        '#<template(?<attrs>[^>]*)>(?<body>.*?)</template>#s',
        $source,
        $blocks,
        PREG_SET_ORDER | PREG_OFFSET_CAPTURE,
    );

    $offenders = [];

    foreach ($blocks as $block) {
        if ($block[0][1] === $rootAt) {
            continue;
        }

        if (preg_match('/\bv-(if|else|for|slot|model)\b/', $block['attrs']) === 1) {
            continue;
        }

        foreach (['button', 'input', 'select', 'a', 'textarea'] as $tag) {
            if (preg_match('#<'.$tag.'\b#', $block['body']) === 1) {
                $offenders[] = $tag;
            }
        }
    }

    expect($offenders)->toBe([]);
})->with(libraryComponentPairs());

it('retires each Blade tag without leaving a twin behind', function (string $component, string $tag): void {
    expect(resource_path('views/components/'.$tag.'.blade.php'))->not->toBeFile();

    // And nothing reaches for it: a surviving `<x-...>` would render an empty string, silently.
    $hits = [];

    foreach (bladeTreeSources() as $path => $source) {
        if (str_contains($source, '<x-'.$tag)) {
            $hits[] = $path;
        }
    }

    expect($hits)->toBe([]);
})->with(retiredBladeComponents());

/**
 * Every remaining Blade view's source, keyed by its path relative to the project root.
 *
 * RecursiveDirectoryIterator, not glob('**'): PHP's glob does not expand `**`, so a
 * glob-based sweep reads one directory and passes without having looked at the rest.
 *
 * @return array<string, string>
 */
function bladeTreeSources(): array
{
    $walk = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('resources/views'), FilesystemIterator::SKIP_DOTS)
    );

    $sources = [];

    foreach ($walk as $file) {
        // getExtension() answers "php" for foo.blade.php, so the suffix is matched on the
        // filename. Filtering on the extension silently scans nothing.
        if (str_ends_with($file->getFilename(), '.blade.php')) {
            $sources[str_replace(DIRECTORY_SEPARATOR, '/', str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname()))] =
                (string) file_get_contents($file->getPathname());
        }
    }

    return $sources;
}

it('meets the 44px control contract on every button shape', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/components/AppButton.vue'));

    // Every keyed variant carries the floor; the Discipline shape is a 64px circle instead and
    // still clears it.
    foreach (['primary', 'secondary', 'danger', 'banner'] as $variant) {
        expect($source)->toMatch("/\n    {$variant}: '[^']*min-h-11[^']*'/");
    }

    expect($source)
        ->toContain('class="relative grid size-16 place-items-center rounded-full')
        // The shortcut hint is printed only on the shape that has a cap to print it in.
        ->toContain('v-if="shortcut !== null"');
});

it('strips a grade modifier before the fill lookup', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/components/GradeBadge.vue'));

    // KI-8: a `+` is a step within the base letter's colour, so `B+` must reach `bg-grade-b`
    // rather than emitting a class nothing defines.
    expect($source)
        ->toContain("B: 'bg-grade-b',")
        ->toContain("S: 'bg-grade-s',")
        ->toContain("SS: 'bg-grade-ss',")
        ->toContain("A: 'bg-grade-a',")
        ->toContain("fills[props.grade.replace(/[-+]+$/, '')] ?? 'bg-grade-g'");
});

it('names the energy state word at each threshold', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/components/EnergyGauge.vue'));

    expect($source)
        ->toContain('if (value.value > 50) return \'Safe\';')
        ->toContain('if (value.value >= 30) return \'Caution\';')
        ->toContain('return \'Danger\';')
        // An unrecorded Energy is absent, not exhausted: the gauge shows the word instead of an
        // empty sweep that would read as zero (D-220).
        ->toContain('Energy N/A')
        ->toContain('v-if="!recorded"');
});

it('leaves the run turn and fans to the resource strip', function (): void {
    // `x-run-header` is retired and has no component of its own: the run page draws its own header
    // and the Resources strip owns both figures. The two claims the header used to carry are split
    // accordingly — the turn and the fans are the strip's, and the fan shortfall is computed
    // nowhere, so there is no class of case left to write here.
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    TurnEntry::factory()->create(['training_run_id' => $run->id, 'turn' => 12, 'energy' => 42, 'fans' => 12000]);

    $this->get('/training-runs/'.$run->id)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Runs/Show')
        ->where('strip.values.turn', 1)
        ->where('strip.values.fans', 12000)
        ->where('strip.values.energy', 42));

    $strip = (string) file_get_contents(base_path('resources/js/components/ResourceStrip.vue'));
    $show = (string) file_get_contents(base_path('resources/js/pages/Runs/Show.vue'));

    expect($strip)
        ->toContain("label = 'Turn';")
        ->toContain("label = 'Fans';")
        // A shortfall is a subtraction the page does not do: the fan gate is the calendar's figure.
        ->not->toContain('short');
    expect($show)->not->toContain('short ');
});

it('renders the capsule header with the lattice motif and a word', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/components/CapsuleHeader.vue'));

    expect($source)
        ->toContain('lattice-bleed')
        ->toContain('h-11')
        ->toContain('{{ title }}');
});

/**
 * A shared visual is worth extracting exactly once. Both of these were defined twice and
 * rendered from the second copy: `capsule-header` on eight panels, `grade-badge` in the
 * stat band. A duplicate that renders identically today still drifts, and the drift is
 * invisible until a contrast pass or a capture contradicts it.
 *
 * The owner is now a Vue file, and the sweep covers both trees: the lattice bleed and the grade
 * fill map both still exist as literals, and a second literal is the defect this case is for.
 */
it('defines each shared visual in exactly one source', function (string $marker, string $owner): void {
    $holders = [];

    foreach (libraryComponents() + ['run page' => 'resources/js/pages/Runs/Show.vue'] as $name => $path) {
        if (str_contains((string) file_get_contents(base_path($path)), $marker)) {
            $holders[] = $name;
        }
    }

    expect($holders)->toBe([$owner]);
})->with([
    // The lattice bleed is the motif only a capsule header carries (DESIGN.md §2.3), and
    // the eight panels that hand-copied the div had already moved away from the component's
    // own padding and type size while each one claimed to be the same header.
    'capsule header' => ['lattice-bleed', 'capsule-header'],
    // KI-8 crashed on the banding emitting seventeen labels against a nine-key map, which
    // is what a second copy of that map invites: one owner, one place to add a tenth letter.
    'grade fill map' => ['bg-grade-g', 'grade-badge'],
]);
