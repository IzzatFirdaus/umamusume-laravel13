<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use App\Models\TurnEntry;

/*
 * The keyboard path (D-55, gate G-11).
 *
 * What a PHP suite can prove here is structure and wiring: that a skip link exists and
 * points at something real, that the rail advertises its shortcuts rather than assuming
 * them, and that the module that implements them is actually in the entry the browser
 * loads. That the keys do what they claim is a runtime fact, verified with real key
 * presses in the browser pass and recorded in
 * `docs/design-research/verification/slice-5-2026-09-28.md`.
 */
function keyboardHtml(): string
{
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    TurnEntry::create([
        'training_run_id' => $run->id, 'turn' => 1, 'speed' => 300, 'stamina' => 280,
        'power' => 240, 'guts' => 210, 'wit' => 150, 'sp' => 120, 'energy' => 74,
        'mood' => 'GOOD', 'fans' => 4000,
    ]);

    return test()->get('/training-runs/'.$run->id)->assertOk()->getContent();
}

it('offers a skip link whose target exists', function (): void {
    $html = keyboardHtml();
    $doc = new DOMDocument;
    @$doc->loadHTML($html, LIBXML_NOERROR);
    $xpath = new DOMXPath($doc);

    $link = $xpath->query('//a[@href="#main"]')->item(0);
    $target = $xpath->query('//main[@id="main"]')->item(0);

    // A skip link that points at nothing is worse than none: it is a trap for the exact
    // person it exists for. Both halves are asserted, not just the anchor.
    expect($link)->not->toBeNull()
        ->and($link->textContent)->toContain('Skip to content')
        ->and($target)->not->toBeNull();
});

it('hides the skip link until it has focus', function (): void {
    $classes = (string) preg_replace('/.*<a href="#main"([^>]*)>.*/s', '$1', keyboardHtml());

    // Revealed only in its own focus state, so it costs no layout to the mouse user and
    // appears exactly when a keyboard Trainer needs it.
    expect($classes)->toContain('sr-only')
        ->and($classes)->toContain('focus:not-sr-only');
});

it('advertises the rail shortcuts instead of assuming them', function (): void {
    $html = keyboardHtml();

    // D-55 binds 1-5 to the five disciplines; the rail also offers Rest and a Mood
    // adjustment, and the advertised range is counted from the choices themselves.
    expect($html)->toContain('Keys 1 to 7 choose an activity')
        ->and($html)->toContain('Enter previews the turn')
        ->and($html)->toContain('Escape returns to the choices');
});

it('loads the keyboard module from the entry the browser actually runs', function (): void {
    $entry = (string) file_get_contents(base_path('resources/js/app.ts'));

    // The module existing on disk proves nothing about it executing. This is the wiring
    // check: without the import the behaviour is untestable from here and invisible in the
    // browser pass, which would report the keys as broken.
    expect($entry)->toContain("import './guided-flow';");
});

it('leaves the arrow-key roving to the radio group rather than reimplementing it', function (): void {
    $script = (string) file_get_contents(base_path('resources/js/guided-flow.ts'));

    // The browser already roves focus and selection through a radio group with the arrow
    // keys. A script that re-does it fights the platform and breaks inside text fields, so
    // the absence of tabindex juggling here is the decision, not an omission.
    expect($script)->not->toContain('tabIndex')
        ->and($script)->not->toContain('ArrowDown')
        ->and($script)->toContain('isEditing');
});

it('guards the number shortcut by input type rather than by tag name', function (): void {
    $script = (string) file_get_contents(base_path('resources/js/guided-flow.ts'));

    // The guard exists so a digit typed into Speed is not a command. Keyed on the tag it
    // also matched the radios, and because focus is on a radio right after an arrow key
    // moves the selection, the shortcut died exactly where it is used. The browser pass
    // found this; the type list is what keeps it fixed.
    expect($script)->toContain('nonTextInputTypes')
        ->and($script)->toMatch('/radio/');
});
