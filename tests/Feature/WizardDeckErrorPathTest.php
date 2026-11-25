<?php

declare(strict_types=1);

it('wires each field refusal to the slot that owns it', function (): void {
    // N3.20: fieldErrors and errorLabel were computed and never rendered, so a level above a
    // card's rarity ceiling produced only the whole-deck banner. The slot now receives its own
    // refusal. A source pin is the cheapest way to hold the wiring; the browser suite owns the
    // rendered copy through the deck specs.
    $source = (string) file_get_contents(base_path('resources/js/pages/Career/DeckSelect.vue'));

    expect($source)->toContain('fieldErrors.value.find(')
        ->and($source)->toContain(':error="errorFor(slot.position)"')
        ->and($source)->toContain('errorLabel(field[0])');
});
