<?php

declare(strict_types=1);

/*
 * The keyboard path (D-55, gate G-11), after the run screen's Inertia port (ADR-0020 §1).
 *
 * What a PHP suite can prove here is structure and wiring. The structure is client-side:
 * the skip link is in `resources/js/layouts/AppLayout.vue`, and the rail's advertisement and
 * key handling are in `resources/js/components/GuidedStep.vue`, which owns its own
 * document-level listener (the Blade-era `guided-flow.ts` module is gone, B1). That the keys
 * do what they claim is a runtime fact, verified with real key presses in
 * tests/browser/run-detail.spec.ts.
 */

function keyboardSource(string $path): string
{
    return (string) file_get_contents(base_path($path));
}

it('offers a skip link whose target exists', function (): void {
    $layout = keyboardSource('resources/js/layouts/AppLayout.vue');

    // A skip link that points at nothing is worse than none: it is a trap for the exact
    // person it exists for. Both halves are asserted, not just the anchor, and the text is
    // tied to the anchor rather than to the file.
    expect($layout)->toMatch('/<a\s+[^>]*href="#main"[^>]*>\s*Skip to content\s*<\/a>/')
        ->and($layout)->toContain('<main id="main"');
});

it('hides the skip link until it has focus', function (): void {
    $layout = keyboardSource('resources/js/layouts/AppLayout.vue');

    preg_match('/<a\s+[^>]*href="#main"[^>]*class="([^"]*)"/s', $layout, $anchor);
    $classes = explode(' ', $anchor[1] ?? '');

    // Revealed only in its own focus state, so it costs no layout to the mouse user and
    // appears exactly when a keyboard Trainer needs it.
    expect($classes)->toContain('sr-only')
        ->and($classes)->toContain('focus:not-sr-only');
});

it('advertises the rail shortcuts instead of assuming them', function (): void {
    $step = keyboardSource('resources/js/components/GuidedStep.vue');

    // D-55 binds 1-5 to the five disciplines; the rail also offers Rest and a Mood
    // adjustment, and the advertised range is counted from the choices themselves.
    expect($step)->toContain('Keys 1 to {{ choices.length }} choose an activity')
        ->and($step)->toMatch('/arrow keys move between them, Enter\s+previews the turn, Escape returns to the choices\./s');
});

it('loads the keyboard component from the entry the browser actually runs', function (): void {
    // The component existing on disk proves nothing about it executing: the entry the run page
    // loads must resolve the page, and the page must mount GuidedStep. Without that the
    // behaviour is untestable from here and invisible in the browser pass, which would report
    // the keys as broken.
    //
    // The third clause is the registration, not the definition. The first draft of this assertion
    // pinned `function onKeydown` and passed against a component that declared the handler and
    // never attached it, which is the exact defect it exists to catch.
    $step = keyboardSource('resources/js/components/GuidedStep.vue');

    expect(keyboardSource('resources/js/spa.ts'))->toContain("'./pages/**/*.vue'")
        ->and(keyboardSource('resources/js/pages/Runs/Show.vue'))->toContain("import GuidedStep from '../../components/GuidedStep.vue'")
        ->and($step)->toContain("document.addEventListener('keydown', onKeydown)")
        // A listener registered on the document and never removed leaks one handler per visit, so
        // the rail that returns here after a turn accumulates handlers that all press the same key.
        ->and($step)->toContain("document.removeEventListener('keydown', onKeydown)");
});

it('leaves the arrow-key roving to the radio group rather than reimplementing it', function (): void {
    $script = keyboardSource('resources/js/components/GuidedStep.vue');

    // The browser already roves focus and selection through a radio group with the arrow
    // keys. A script that re-does it fights the platform and breaks inside text fields, so
    // the absence of tabindex juggling here is the decision, not an omission.
    expect($script)->not->toContain('tabIndex')
        ->and($script)->not->toContain('ArrowDown')
        ->and($script)->toContain('input[type="radio"]');
});

it('guards the number shortcut by input type rather than by tag name', function (): void {
    $script = keyboardSource('resources/js/components/GuidedStep.vue');

    // The guard exists so a digit typed into Speed is not a command. Keyed on the tag it
    // also matched the radios, and because focus is on a radio right after an arrow key
    // moves the selection, the shortcut died exactly where it is used. The browser pass
    // found this; the type list is what keeps it fixed.
    expect($script)->toContain('NON_TEXT_TYPES.includes(target.type)')
        ->and($script)->toMatch('/radio/');
});
