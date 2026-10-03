<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Enums\SkillAcquisition;
use App\Models\Skill;
use App\Models\TrainingRun;

/*
 * B.6: the skill picker collapses to one open catalogue and nine closed rows, the
 * same shape the deck panel landed in Part 6. A closed row posts its value through
 * a hidden input under the same field name and offers a query-string link to open
 * it, so the POST contract is unchanged and the no-script path keeps its capability.
 */

it('collapses the skill picker to one open list, nine hidden fields and nine links', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $skills = Skill::factory()->count(9)->create([
        'release_status' => ReleaseStatus::GlobalReleased->value,
        'name_is_client' => true,
    ]);

    foreach ($skills as $skill) {
        $run->setSkillStatus($skill, SkillAcquisition::Acquired);
    }

    $html = test()->get(route('runs.show', $run))->assertOk()->getContent();
    $dom = new DOMDocument;
    @$dom->loadHTML($html, LIBXML_NOERROR);
    $xpath = new DOMXPath($dom);

    expect($xpath->query('//select[contains(@name, "[skill_id]")]')->length)->toBe(1)
        ->and($xpath->query('//input[@type="hidden" and contains(@name, "[skill_id]")]')->length)->toBe(9)
        ->and($xpath->query('//a[starts-with(normalize-space(.), "Change row ")]')->length)->toBe(9);
});

it('opens the row the query names and leaves the rest closed', function (): void {
    $run = TrainingRun::factory()->create(['scenario' => 'ura_finale']);
    $skills = Skill::factory()->count(9)->create([
        'release_status' => ReleaseStatus::GlobalReleased->value,
        'name_is_client' => true,
    ]);

    foreach ($skills as $skill) {
        $run->setSkillStatus($skill, SkillAcquisition::Acquired);
    }

    $html = test()->get(route('runs.show', $run).'?skill_row=4')->assertOk()->getContent();
    $dom = new DOMDocument;
    @$dom->loadHTML($html, LIBXML_NOERROR);
    $xpath = new DOMXPath($dom);

    expect($xpath->query('//select[@id="skill-4-id"]')->length)->toBe(1)
        ->and($xpath->query('//input[@type="hidden" and @name="skills[4][skill_id]"]')->length)->toBe(0)
        ->and($xpath->query('//input[@type="hidden" and @name="skills[0][skill_id]"]')->length)->toBe(1);
});
