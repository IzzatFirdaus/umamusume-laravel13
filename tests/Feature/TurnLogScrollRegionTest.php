<?php

declare(strict_types=1);

/*
 * KI-25: the turn log forces a horizontal scroll at 390px that nobody can reach.
 *
 * The measurement is in the register and in `slice-15-2026-09-29.md` §8.4 — the table's
 * `scrollWidth` is 476px inside a 390px viewport, so content is clipped. What was missed is the
 * second half: the overflow happens, and a keyboard Trainer cannot traverse it. A scroll container
 * that is not focusable has no focus to scroll with, so those 86px are reachable by trackpad only.
 *
 * The race calendar solved this the same way when it was a twelve-column band: `role="region"` +
 * `tabindex="0"` + an `aria-label`. The calendar has since taken the client's four-column shape
 * (docs/game-screenshots/Screenshot 2026-07-17 230755.png) and stopped overflowing at any width a
 * desktop tool is used at, so it keeps the named region and gave the tab stop back. What is left of the
 * convention is asserted per region: the one that clips is the one that must be traversable.
 *
 * The page is component-rendered (ADR-0020 §1), so the attributes are read from the two components
 * that own the two regions instead of from server HTML: the turn log's wrapper is in
 * `resources/js/pages/Runs/Show.vue`, the calendar's in `resources/js/components/RaceCalendar.vue`.
 * What this file can prove is structure — the attributes exist on the element that wraps the
 * clipping table. That arrow keys actually scroll it is a runtime fact, verified in the browser pass;
 * a PHP assertion cannot press a key.
 */

function runDetailSource(string $path): string
{
    return (string) file_get_contents(base_path($path));
}

/**
 * The opening tag of the run page's one clipping region, or an empty string when none exists:
 * the empty string fails every assertion below, so a template that drops the wrapper fails here
 * rather than passing vacuously.
 */
function turnLogRegionTag(): string
{
    $source = runDetailSource('resources/js/pages/Runs/Show.vue');

    return preg_match('/<div[^>]*aria-label="Turn log"[^>]*>/', $source, $tag) === 1 ? $tag[0] : '';
}

/**
 * The opening tag of the calendar's named region.
 */
function calendarRegionTag(): string
{
    $source = runDetailSource('resources/js/components/RaceCalendar.vue');

    return preg_match('/<div[^>]*role="region"[^>]*>/', $source, $tag) === 1 ? $tag[0] : '';
}

it('wraps the turn log in a focusable scroll region', function (): void {
    $tag = turnLogRegionTag();

    expect($tag)->not->toBe('', 'the turn log table has no wrapping region')
        ->and($tag)->toContain('role="region"')
        ->and($tag)->toContain('tabindex="0"')
        ->and($tag)->toContain('overflow-x-auto');
});

it('keeps the tab stop on the region that still clips and the name on the one that does not', function (): void {
    $calendar = calendarRegionTag();
    $turnLog = turnLogRegionTag();

    /*
     * The census of the run page's regions, so neither assertion above can pass on an empty list.
     * Exactly two regions exist across the two components: the calendar, whose name stays and whose
     * tab stop went with its overflow band, and the turn log, which still clips at the 768px floor,
     * so both halves of KI-25 stand there.
     */
    $regions = substr_count(runDetailSource('resources/js/components/RaceCalendar.vue'), 'role="region"')
        + substr_count(runDetailSource('resources/js/pages/Runs/Show.vue'), 'role="region"');

    expect($regions)->toBe(2)
        // A region that does not clip and still holds a tab stop spends a keypress on nothing.
        ->and($calendar)->not->toContain('tabindex')
        ->and($calendar)->not->toContain('overflow-x-auto')
        // The calendar's name is a computed, so the assertion resolves the source of it rather
        // than the bound attribute: it is what a screen reader hears when it enters the region.
        ->and(runDetailSource('resources/js/components/RaceCalendar.vue'))->toContain('`Race calendar, ')
        ->and($turnLog)->toContain('overflow-x-auto')
        ->and($turnLog)->toContain('tabindex="0"');
});
