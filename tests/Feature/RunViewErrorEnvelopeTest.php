<?php

declare(strict_types=1);

use App\Models\TrainingRun;

/*
 * Static review F-04 gate. The `previewed` request-stage error and a per-field error
 * must render in one shared <ul>, both as <li> items. The old top-line <p> treatment is
 * gone. Pinned against the rendered HTML after a rejected confirm submit.
 */

it('renders the previewed error and a field error inside the same list', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    test()->post(route('runs.turns.store', $run), [
        'stage' => 'confirm',
        'turn' => 2,
        'speed' => 550,
        'stamina' => 525,
        'power' => 601,
        'guts' => 75,
        'wit' => 95,
        'sp' => 240,
        'energy' => 88,
        'fans' => 9000,
        // `choice` omitted (per-field error) and `previewed` omitted (stage error).
    ]);

    // No assertSessionHasErrors between the POST and the GET: the assertion boots the
    // session store in a way that ages the flashed errors before the next request, so
    // the rendered page is the assert. The errors flash on the redirect and render on
    // the follow-up GET.
    $html = test()->get(route('runs.show', $run))->assertOk()->getContent();

    $dom = new DOMDocument;
    @$dom->loadHTML($html, LIBXML_NOERROR);
    $xpath = new DOMXPath($dom);

    $stageMessage = 'Preview the turn before confirming it.';
    $fieldMessage = 'Choose what this turn did.';

    $sharedList = null;

    foreach ($xpath->query('//ul') as $ul) {
        $text = $ul->textContent;

        if (str_contains($text, $stageMessage) && str_contains($text, $fieldMessage)) {
            $sharedList = $ul;
            break;
        }
    }

    expect($sharedList)->not->toBeNull('no single <ul> carries both the stage and the field error')
        ->and($sharedList->getElementsByTagName('li')->length)->toBeGreaterThanOrEqual(2);

    // The top-line paragraph treatment is gone: the stage error must not render as a
    // standalone <p class="text-risk"> outside the list.
    expect($html)->not->toContain('text-sm text-risk"><p');
});
