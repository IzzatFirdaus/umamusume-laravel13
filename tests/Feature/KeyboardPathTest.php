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

it('carries the product navigation into the wizard without duplicating the shell', function (): void {
    $wizard = keyboardSource('resources/js/layouts/SetupLayout.vue');
    $layout = keyboardSource('resources/js/layouts/AppLayout.vue');

    // The wizard now mounts `AppLayout` rather than re-declaring a shell of its own, so the skip link,
    // the banner, the `main` landmark and the mobile bar exist exactly once on a setup screen. A second
    // `#main` would make the one skip link on the page point at the wrong region, which is the trap this
    // file exists to catch.
    expect($wizard)->toContain('<AppLayout>')
        ->and($wizard)->not->toContain('href="#main"')
        ->and($wizard)->not->toContain('<main id="main"')
        // The step bar stays as the wizard's own sub-navigation, labelled, with its way out.
        ->and($wizard)->toContain('aria-label="Setup steps"')
        ->and($wizard)->toContain('aria-current')
        ->and($wizard)->toContain('Leave setup')
        // ...and the shell it inherits is the §41 bar: four slots plus a More disclosure, never the
        // whole sidebar in a scrolling strip. Matched on class attributes, because AppLayout's own
        // comment quotes the utility to explain why it is gone.
        ->and($layout)->not->toMatch('/class="[^"]*overflow-x-auto/')
        ->and($layout)->toContain('<details');
});

it('collapses the rail to marks without removing any destination name', function (): void {
    $layout = keyboardSource('resources/js/layouts/AppLayout.vue');
    $glyph = keyboardSource('resources/js/components/NavGlyph.vue');

    // The control itself: a named button that announces its own state and points at the region it
    // controls, at the 44px floor. `aria-expanded` is the state, so a screen reader hears "collapsed"
    // without reading the mark.
    expect($layout)->toMatch('/<button[^>]*type="button"[^>]*class="[^"]*min-h-11[^"]*"/')
        ->and($layout)->toContain(':aria-expanded="railCollapsed ? \'false\' : \'true\'"')
        ->and($layout)->toContain('aria-controls="primary-nav"')
        ->and($layout)->toContain('@click="toggleRail"');

    // Collapsing hides the word visually and keeps it in the accessibility tree. This is the whole
    // reason the rail is allowed to be icon-only: a mark that replaces the name would leave ten
    // anonymous links, and §42 asks for text alternatives to icons rather than icons instead of them.
    expect($layout)->toContain("railCollapsed ? 'sr-only' : ''")
        ->and($layout)->toContain(':title="railCollapsed ? item.label : undefined"');

    // The marks belong to the collapsed rail alone (owner instruction, 2026-10-06): the full-width
    // sidebar is text, and a drawing beside the word it stands for is noise. Three destination renders
    // exist (live Inertia link, native link, named absence) and all three carry the guard, so a mark
    // that drops its `v-if` fails here rather than shipping a mixed rail. The toggle's own chevron is
    // unguarded by design and is not a destination mark.
    expect(substr_count($layout, ':name="item.icon"'))->toBe(3)
        ->and(substr_count($layout, '<NavGlyph v-if="railCollapsed" :name="item.icon" />'))->toBe(3);

    // Both widths exist, and the width change is a motion that reduced-motion turns off.
    expect($layout)->toContain("'w-14'")
        ->and($layout)->toContain("'w-64'")
        ->and($layout)->toContain('transition-[width]')
        ->and($layout)->toContain('motion-reduce:transition-none');

    // The choice is remembered in the browser, not in a preference row: a third stored key would widen
    // PRD US-11's two authorised keys. Asserted on the write and the absence of a server route name,
    // because the file's own comment cites `Preference::KEYS` to explain the decision, and a whole-file
    // ban on that string would fail on the prose that documents it.
    expect($layout)->toContain("const RAIL_KEY = 'trainer-desk.nav-rail'")
        ->and($layout)->toContain('localStorage.setItem(RAIL_KEY')
        ->and($layout)->not->toContain('preferences.update');

    // Every mark is drawn as a path on one grid, which is how design-2.0 §44's "no emoji as the primary
    // icon system" is satisfied by construction. Checked on the template rather than the whole file,
    // because the docblock names the refused motif families to explain what §6 refuses.
    $templatePos = strpos($glyph, '<template>');
    $glyphTemplate = $templatePos === false ? '' : substr($glyph, $templatePos);

    expect($glyphTemplate)->toContain('<path')
        ->and($glyphTemplate)->toContain('aria-hidden="true"')
        ->and($glyphTemplate)->not->toMatch('/[\x{1F000}-\x{1FAFF}]/u');

    preg_match_all('/^    [a-z]+: \[/m', $glyph, $marks);

    expect($marks[0] ?? [])->toHaveCount(11);
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
