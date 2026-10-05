<?php

declare(strict_types=1);

use App\Models\TrainingRun;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Static review F-01 gate. The delete path on the run detail route carries zero inline
 * event handlers and no <script> element. The page is component-rendered now (ADR-0020 §1):
 * the payload carries `run.destroy_url` and the disclosure markup lives in
 * `resources/js/pages/Runs/Show.vue`, so the gate is pinned on the payload and the component
 * that owns the markup; the rendered result is asserted in tests/browser/run-detail.spec.ts.
 */

it('renders the delete path with no inline handler and no script', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    test()->get(route('runs.show', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Runs/Show')
            ->where('run.destroy_url', route('runs.destroy', $run)));

    $source = (string) file_get_contents(base_path('resources/js/pages/Runs/Show.vue'));

    // The old form was found by its `_method` spoof input because show and destroy share the
    // URL; the Vue delete goes through Inertia's verb, so the census is the one delete call
    // against the run's own URL.
    expect(substr_count($source, 'editForm.delete(run.destroy_url'))->toBe(1);

    // Inline `on*` attributes are what the gate names; Vue's `@` bindings are not HTML
    // attributes, they compile to listeners, so their absence here is the claim.
    expect($source)->not->toMatch('/\son(click|submit|change|load)=/');

    // The disclosure shape: the delete form sits inside a <details> whose summary is the
    // destructive-action prompt the old confirm() used to be.
    preg_match('/<details class="mt-10">(.*?)<\/details>/s', $source, $disclosure);
    $deleteDisclosure = $disclosure[1] ?? '';

    expect($deleteDisclosure)->toContain('Delete run')
        ->and($deleteDisclosure)->toContain('editForm.delete(run.destroy_url')
        ->and($deleteDisclosure)->not->toContain('<script');
});
