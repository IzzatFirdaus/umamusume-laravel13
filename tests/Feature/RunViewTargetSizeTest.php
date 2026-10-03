<?php

declare(strict_types=1);

use App\Models\TrainingRun;

/*
 * L-F01 regression gate (WCAG 2.2 SC 2.5.8). The five nav links, the two export links,
 * the helper link and the summary disclosure must all carry `min-h-11` (44px, the
 * project's floor idiom from KI-37), and the four skill-row controls must still carry
 * the `h-11` KI-37 closed with. Asserted on the rendered DOM, not the source.
 */

function runViewTargetsHtml(): string
{
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    return test()->get('/training-runs/'.$run->id)->assertOk()->getContent();
}

it('keeps the four skill-row controls at the KI-37 height', function (): void {
    $html = runViewTargetsHtml();
    $dom = new DOMDocument;
    @$dom->loadHTML($html, LIBXML_NOERROR);
    $xpath = new DOMXPath($dom);

    $classesOf = function (DOMNodeList $nodes): array {
        $out = [];

        foreach ($nodes as $node) {
            $out[] = $node->attributes->getNamedItem('class')?->nodeValue ?? '';
        }

        return $out;
    };

    $skillSelects = $xpath->query('//select[starts-with(@name, "skills[0][")]');
    $turnInput = $xpath->query('//input[starts-with(@name, "skills[0][")]');
    $saveButton = $xpath->query('//button[contains(., "Save skill status")]');

    expect($classesOf($skillSelects))->each->toContain('h-11')
        ->and($classesOf($turnInput))->each->toContain('h-11')
        ->and($classesOf($saveButton))->each->toContain('h-11');
});

it('gives every L-F01 target the min-h-11 floor', function (): void {
    $html = runViewTargetsHtml();
    $dom = new DOMDocument;
    @$dom->loadHTML($html, LIBXML_NOERROR);
    $xpath = new DOMXPath($dom);

    $nav = $xpath->query('//nav//a');
    $exportCsv = $xpath->query('//a[contains(@href, "/export/csv")]');
    $exportJson = $xpath->query('//a[contains(@href, "/export/json")]');
    $helper = $xpath->query('//a[contains(., "Search the skill catalog")]');
    $summary = $xpath->query('//summary[contains(., "Correct a turn by hand")]');

    $assertFloor = function (?DOMNodeList $nodes, string $label): void {
        expect($nodes, $label)->not->toBeNull();
        expect($nodes->length, $label.' found')->toBeGreaterThan(0);

        foreach ($nodes as $node) {
            $class = $node->attributes->getNamedItem('class')?->nodeValue ?? '';

            expect($class, $label)->toContain('min-h-11');
        }
    };

    $assertFloor($nav, 'nav links');
    $assertFloor($exportCsv, 'export csv');
    $assertFloor($exportJson, 'export json');
    $assertFloor($helper, 'helper link');
    $assertFloor($summary, 'summary disclosure');

    expect($nav->length)->toBe(5);
});
