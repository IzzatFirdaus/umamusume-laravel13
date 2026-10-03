<?php

declare(strict_types=1);

use App\Models\Preference;

/*
 * SCREEN_SPEC.md §7-5 / PRD US-11: the `preferences` table and the server-side theme render both
 * shipped, and nothing in `resources/views/` could write to them, so the dark theme was reachable
 * only by inserting a row by hand (audit O-1: "the dark theme ships with no way to select it").
 *
 * The first-paint half of this story is already pinned in `DesignTokensTest:100-131` (a stored
 * theme renders into `<html data-theme>` and the pre-paint script is dropped), so this file does
 * not re-assert it. What it adds is the control: the two authorized keys, their allowed values,
 * the refusal of anything else, and what the off-by-default failure estimate is allowed to mean.
 *
 * `failure_estimate` is stored and read back, and it shows no number, because there is no number
 * to show: ADR-0001 §3 records that no source publishes a failure curve, and requires that a
 * numeric estimate render with its formula and parameters visible on the same surface. Wiring a
 * percentage here would be the fabricated statistic that section refuses, so the control says it
 * shows nothing yet and §7-5 records the block.
 */

it('offers both preferences with their default states on an unset database', function (): void {
    $html = test()->get('/preferences')->assertOk()->getContent();

    // Absence is the default for both keys: no row means follow the OS, and the estimate is off
    // by default (PRD US-11). A control that rendered "light" as if it were stored would claim a
    // preference the Trainer never set.
    expect($html)->toContain('name="theme"')
        ->toContain('name="failure_estimate"')
        ->toContain('value="" selected')
        ->not->toMatch('/name="failure_estimate"[^>]*checked/')
        ->and(Preference::count())->toBe(0);
});

it('is reachable from the main layout', function (): void {
    // The dispatch's requirement is a control reachable from the shell, not a form inside every
    // page: the nav carries a link, and the form lives on its own screen.
    test()->get('/umamusume')->assertOk()->assertSeeText('Preferences');

    expect(test()->get('/umamusume')->getContent())
        ->toContain('href="'.route('preferences.edit').'"')
        // The shell stays form-free, which is what keeps the page-wide control counts that
        // ReviewFormAccessibilityTest and TraineeSelectorTest read honest.
        ->not->toContain('name="failure_estimate"');
});

it('writes the theme through the control and renders it on the next request', function (): void {
    test()->put('/preferences', ['theme' => 'dark', 'failure_estimate' => 'off'])
        ->assertRedirect();

    expect(Preference::get('theme'))->toBe('dark');

    // First paint, not a script fixing it afterwards: the attribute is in the document the
    // server sent.
    test()->get('/umamusume')->assertOk()->assertSee('data-theme="dark"', false);
});

it('switches the theme back to following the system by dropping the row', function (): void {
    Preference::put('theme', 'light');

    test()->put('/preferences', ['theme' => '', 'failure_estimate' => 'off'])->assertRedirect();

    // US-11 authorizes three values and the third is the absence of a row, so "follow the system"
    // has to remove the preference rather than store the word `system`, which the composer in
    // AppServiceProvider:45 would honour as no theme anyway while leaving a dead row behind.
    expect(Preference::get('theme'))->toBeNull()
        ->and(Preference::whereKey('theme')->exists())->toBeFalse();
});

it('persists the failure estimate both ways', function (string $value): void {
    test()->put('/preferences', ['theme' => 'light', 'failure_estimate' => $value])->assertRedirect();

    expect(Preference::get('failure_estimate'))->toBe($value);
})->with(['on', 'off']);

it('keeps the estimate checkbox unchecked when the stored value is off', function (): void {
    Preference::put('failure_estimate', 'off');

    $html = test()->get('/preferences')->assertOk()->getContent();

    expect($html)->not->toMatch('/name="failure_estimate"[^>]*checked/');

    Preference::put('failure_estimate', 'on');

    expect(test()->get('/preferences')->getContent())
        ->toMatch('/name="failure_estimate"[^>]*checked/');
});

it('refuses a theme value this tool does not store', function (): void {
    test()->put('/preferences', ['theme' => 'graphite', 'failure_estimate' => 'on'])
        ->assertSessionHasErrors('theme');

    expect(Preference::get('theme'))->toBeNull();
});

it('refuses a failure-estimate value that is not the pair the key is authorized for', function (): void {
    test()->put('/preferences', ['theme' => 'light', 'failure_estimate' => 'sometimes'])
        ->assertSessionHasErrors('failure_estimate');

    expect(Preference::get('failure_estimate'))->toBeNull();
});

it('refuses a preference key that is not one of the two US-11 authorizes', function (): void {
    test()->put('/preferences', ['theme' => 'light', 'failure_estimate' => 'on', 'display_timezone' => 'UTC'])
        ->assertSessionHasErrors('preferences');

    // US-11 states the key set is a contract, not a free-form blob, and the migration's own
    // docblock says a third key is a PRD change. Silently dropping it would let a form outlive
    // its ruling.
    expect(Preference::whereKey('display_timezone')->exists())->toBeFalse()
        ->and(Preference::pluck('key')->all())->toEqual([]);
});

it('stores preferences in the database and not in browser storage', function (): void {
    test()->put('/preferences', ['theme' => 'dark', 'failure_estimate' => 'on'])->assertRedirect();

    $html = test()->get('/preferences')->assertOk()->getContent();

    // PRD §6 non-goal 12 cuts a second source of truth, so nothing here may be mirrored to
    // localStorage. The head script that reads the OS is the fallback for *no* stored theme and
    // must not become a preferences store.
    expect($html)->not->toContain('localStorage')
        ->not->toContain('uma-theme')
        ->and(Preference::whereKey('theme')->value('value'))->toBe('dark');
});
