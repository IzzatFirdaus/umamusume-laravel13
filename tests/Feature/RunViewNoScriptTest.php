<?php

declare(strict_types=1);

use App\Models\TrainingRun;

/*
 * Static review F-01 gate. The delete path on the run detail route carries zero inline
 * event handlers and no <script> element. ADR-0007's no-script posture is enforced at
 * the rendered-HTML level, scoped to the delete form's subtree (the layout head keeps
 * its own theme script, which is not the delete path).
 */

it('renders the delete path with no inline handler and no script', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    $html = test()->get(route('runs.show', $run))->assertOk()->getContent();

    expect($html)->not->toContain('onsubmit')
        ->not->toContain('onclick')
        ->not->toContain('onchange')
        ->not->toContain('onload');

    $dom = new DOMDocument;
    @$dom->loadHTML($html, LIBXML_NOERROR);
    $xpath = new DOMXPath($dom);

    // The destroy route and the show route share the same URL (method spoofing), so the
    // form is found by its `_method` spoof input, not by the action string.
    $deleteForms = $xpath->query('//form[.//input[@name="_method" and @value="DELETE"]]');

    expect($deleteForms->length)->toBe(1);

    $deleteForm = $deleteForms->item(0);

    foreach ($deleteForm->attributes as $attribute) {
        expect(str_starts_with($attribute->name, 'on'))->toBeFalse(
            "inline handler {$attribute->name} on the delete form"
        );
    }

    expect($deleteForm->getElementsByTagName('script')->length)->toBe(0);

    // The disclosure shape: the delete form sits inside a <details> whose summary is the
    // destructive-action prompt the old confirm() used to be.
    $details = $xpath->query('//details[.//form[.//input[@name="_method" and @value="DELETE"]]]');

    expect($details->length)->toBe(1)
        ->and($details->item(0)->getElementsByTagName('summary')->length)->toBe(1);
});
