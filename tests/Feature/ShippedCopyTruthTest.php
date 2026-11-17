<?php

declare(strict_types=1);

/*
 * Copy that a payload contradicts, guarded at the source.
 *
 * Four shipped sentences were measured on the running build claiming states the application does not
 * hold: a route URL printed as prose, a feature described as unavailable while its control is live,
 * a figure described as never stored while every row carries it, and an absent control that the
 * application actually renders disabled.
 *
 * These cases read the Vue source rather than a rendered page, because each defect is a sentence in a
 * template and the browser specs that own rendered copy are a separate, serial suite. A source scan is
 * the smallest guard that fails on the sentence and cannot pass by accident: the strings below are the
 * claims, and a claim is wrong in the file whether or not a browser was open.
 *
 * The rendered half is `run-settings.spec.ts`, `preferences.spec.ts`, `catalog.spec.ts` and
 * `help.spec.ts`; where this session changed one of those it is named in the case.
 */

/**
 * The text of one Vue file, or an empty string when the path moved.
 *
 * A moved path is a failure in its own right: every case below asserts against a file that must exist,
 * so an empty read has to break the assertion rather than pass it.
 */
function shippedVueSource(string $relative): string
{
    $path = base_path($relative);

    expect(is_file($path))->toBeTrue(sprintf('%s must exist; a moved screen is not a passing guard.', $relative));

    return (string) file_get_contents($path);
}

it('never prints a route URL as a sentence', function (): void {
    $source = shippedVueSource('resources/js/pages/Career/RunSettings.vue');

    // The identity page told a Trainer "Everything here writes through http://127.0.0.1:8000/…".
    // A host and a path are not copy: the writer is worth naming, its URL is not.
    expect($source)->not->toContain('writes through ${props.run.update_url}')
        ->and($source)->not->toContain('http://localhost')
        ->and($source)->not->toContain('127.0.0.1');

    // The prop itself stays bound to the form that writes, which is the half that must not move.
    expect($source)->toContain('props.run.update_url');
});

it('states the Wide layout as unavailable only when the flag withholds it', function (): void {
    $source = shippedVueSource('resources/js/pages/Preferences/Edit.vue');

    // The sentence rendered unconditionally while the Wide radio came back checked and enabled on a
    // build whose `config('dashboard.wide_bento_enabled')` is true. A claim about a flag has to be
    // guarded by that flag.
    expect($source)->toContain('v-if="!props.wideBentoEnabled"');
});

it('never tells a Trainer the catalogue stores no growth row', function (): void {
    $source = shippedVueSource('resources/js/components/catalog/TraineeCatalog.vue');

    // Every `character_cards` row carries `growth_rates` (ADR-0030), so the card's sentence was false
    // as well as truncated ("… no growth row for an").
    expect($source)->not->toContain('holds no growth row')
        ->and($source)->not->toContain('Growth <span class="text-ink">not stored</span>');

    // The card still names the figure it is talking about, or the row loses its subject.
    expect($source)->toContain('Growth');
});

it('describes the undo control as drawn and disabled, not as absent', function (): void {
    $source = shippedVueSource('resources/js/pages/Help.vue');

    // Five career screens render a disabled `Undo turn N`, and `DELETE turns/{turn}` is live. "There
    // is no Undo control" was false; "there is no enabled one" is the fact.
    expect($source)->not->toContain('there is no Undo last turn control')
        ->and($source)->toContain('disabled');
});
