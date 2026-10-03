<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Enums\SkillAcquisition;
use App\Models\Skill;
use App\Models\TrainingRun;

/*
 * L-F01 regression gate (WCAG 2.2 SC 2.5.8). The five nav links, the two export links,
 * the helper link and the summary disclosure must all carry `min-h-11` (44px, the
 * project's floor idiom from KI-37), and the skill-row controls must still carry
 * the `h-11` KI-37 closed with. Asserted on the rendered DOM, not the source.
 */

function runViewTargetsHtml(): string
{
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);

    // One recorded skill so the collapsed picker has a closed row beside the open one,
    // which is what makes the "Change row N" link half of the L-F01 claim non-vacuous.
    $run->setSkillStatus(
        Skill::factory()->create([
            'release_status' => ReleaseStatus::GlobalReleased->value,
            'name_is_client' => true,
        ]),
        SkillAcquisition::Acquired,
    );

    return test()->get('/training-runs/'.$run->id)->assertOk()->getContent();
}

it('keeps the skill-row controls at the KI-37 height', function (): void {
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

    /*
     * Re-pointed 2026-10-03 (B.6). The ten `skill_id` selects collapsed to one open picker,
     * so the visible picker control is now the open select plus the "Change row N" links that
     * open a closed row. The old selectors matched whole-catalogue selects that no longer exist
     * on every row. The claim is unchanged: every control a Trainer reaches in the skill rows
     * meets the KI-37 44px floor. The hidden per-row `skill_id` inputs are deliberately excluded,
     * because they are not controls and must not carry a height.
     */
    $openSelect = $xpath->query('//select[contains(@name, "[skill_id]")]');
    $switchLinks = $xpath->query('//a[starts-with(normalize-space(.), "Change row ")]');
    $statusSelects = $xpath->query('//select[contains(@name, "[status]")]');
    $turnInputs = $xpath->query('//input[contains(@name, "[turn_acquired]")]');
    $saveButton = $xpath->query('//button[contains(., "Save skill status")]');

    expect($openSelect->length)->toBe(1)
        ->and($switchLinks->length)->toBeGreaterThan(0)
        ->and($classesOf($openSelect))->each->toContain('h-11')
        ->and($classesOf($switchLinks))->each->toContain('min-h-11')
        ->and($classesOf($statusSelects))->each->toContain('h-11')
        ->and($classesOf($turnInputs))->each->toContain('h-11')
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
