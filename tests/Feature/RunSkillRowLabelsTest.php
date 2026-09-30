<?php

declare(strict_types=1);

use App\Enums\ReleaseStatus;
use App\Enums\SkillAcquisition;
use App\Models\Skill;
use App\Models\TrainingRun;

/*
 * KI-36 and the repeater half of KI-33, both on the run screen's skills form.
 *
 * KI-36: one `<label>` wrapped three controls (`show.blade.php:445-461`), so the status `<select>`
 * and the turn `<input>` had no accessible name at all — a placeholder is not a name, it disappears
 * once the field has a value, and this one has a value whenever a Trainer is editing a turn.
 *
 * The repeater: the form hard-indexed `skills[0]` three times. One row, one submit, no way to add
 * another. That was harmless while the table held ten names and a run carried one or two skills; it
 * stopped being harmless the moment Slice A pre-populated four or five rows from a card, because the
 * list the pre-populate seeds is exactly the list a Trainer then cannot edit. KI-33 names this as a
 * required part of itself, not a follow-on, and both defects live in the same seventeen lines, which
 * is why they land together.
 *
 * Every assertion here reads the rendered document through a parser, not through `assertSee` on a
 * class name: a label's `for` and a control's `id` are the fact, and a substring test on the HTML
 * would pass on a `for` that points at nothing.
 */

/**
 * @return array<int, array<string, string>> one entry per form control in the skills form
 */
function skillsFormControls(string $html): array
{
    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $form = $xpath->query('//form[@action and .//select[starts-with(@name, "skills[")]]')->item(0);
    expect($form)->not->toBeNull('the skills form is not on the page');

    $controls = [];

    foreach ($xpath->query('.//select|.//input', $form) as $control) {
        /** @var DOMElement $control */
        if ($control->getAttribute('type') === 'hidden') {
            continue; // the CSRF field is not a Trainer-facing control
        }

        $controls[] = [
            'tag' => $control->nodeName,
            'name' => $control->getAttribute('name'),
            'id' => $control->getAttribute('id'),
        ];
    }

    return $controls;
}

/**
 * @return array<string, string> control id => the text of the label pointing at it
 */
function skillsFormLabels(string $html): array
{
    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $labels = [];

    foreach ($xpath->query('//label[@for]') as $label) {
        /** @var DOMElement $label */
        $labels[$label->getAttribute('for')] = trim(preg_replace('/\s+/', ' ', $label->textContent) ?? '');
    }

    return $labels;
}

function labelledRun(): TrainingRun
{
    $run = TrainingRun::factory()->create();

    foreach ([200512, 201352, 200732] as $id) {
        Skill::create([
            'export_id' => $id,
            'name' => 'Row Skill '.$id,
            'match_key' => 'rowskill'.$id,
            'release_status' => ReleaseStatus::GlobalReleased->value,
            'name_is_client' => true,
        ]);
    }

    foreach (Skill::orderBy('export_id')->get() as $skill) {
        $run->setSkillStatus($skill, SkillAcquisition::Suggested);
    }

    return $run->refresh();
}

it('gives every control in the skills form its own label', function (): void {
    $run = labelledRun();

    $html = $this->get(route('runs.show', $run))->content();
    $controls = skillsFormControls($html);
    $labels = skillsFormLabels($html);

    // Three controls per row: the picker, the acquisition status, the turn. Each has to carry an
    // `id` and each `id` has to be the target of exactly one `<label for>` with real text.
    expect($controls)->not->toBe([]);

    foreach ($controls as $control) {
        // `toHaveKey`'s second argument is the expected **value**, not a failure message, so the
        // membership test goes through array_key_exists and the message rides on `toBe` instead.
        expect($control['id'])->not->toBe('', "control {$control['name']} has no id, so no label can name it")
            ->and(array_key_exists($control['id'], $labels))->toBeTrue("no label points at {$control['id']}")
            ->and($labels[$control['id']])->not->toBe('');
    }

    // The status select and the turn input are the two KI-36 names. A label that reads "Skill" for
    // all three would satisfy `for`/`id` and still leave the defect in place.
    $names = array_values(array_unique(array_map(
        static fn (array $c): string => $labels[$c['id']],
        $controls,
    )));

    expect($names)->toHaveCount(3)
        ->and(implode(' | ', $names))->toMatch('/skill/i')
        ->and(implode(' | ', $names))->toMatch('/status/i')
        ->and(implode(' | ', $names))->toMatch('/turn/i');
});

it('renders one editable row per skill on the run, plus a spare row to add another', function (): void {
    $run = labelledRun();

    $rows = [];

    foreach (skillsFormControls($this->get(route('runs.show', $run))->content()) as $control) {
        if (preg_match('/^skills\[(\d+)\]\[skill_id\]$/', $control['name'], $m) === 1) {
            $rows[] = (int) $m[1];
        }
    }

    // Three seeded skills and one empty row. The spare is what makes the pre-populated list editable
    // rather than merely visible.
    expect($rows)->toBe([0, 1, 2, 3]);
});

it('preselects each existing skill with the status and turn the Trainer gave it', function (): void {
    $run = labelledRun();
    $first = Skill::orderBy('export_id')->first();
    $run->setSkillStatus($first, SkillAcquisition::Acquired, 9);

    $html = $this->get(route('runs.show', $run))->content();

    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $selectedSkill = $xpath->evaluate('string(//select[@name="skills[0][skill_id]"]/option[@selected]/@value)');
    $selectedStatus = trim($xpath->evaluate('string(//select[@name="skills[0][status]"]/option[@selected])'));
    $turnValue = $xpath->evaluate('string(//input[@name="skills[0][turn_acquired]"]/@value)');

    expect((int) $selectedSkill)->toBe($first->id)
        ->and($selectedStatus)->toBe(SkillAcquisition::Acquired->value)
        ->and((int) $turnValue)->toBe(9);
});

it('saves several rows in one submit', function (): void {
    $run = labelledRun();
    $skills = Skill::orderBy('export_id')->get();

    $this->post(route('runs.skills.sync', $run), [
        'skills' => [
            0 => ['skill_id' => $skills[0]->id, 'status' => 'Acquired', 'turn_acquired' => 4],
            1 => ['skill_id' => $skills[1]->id, 'status' => 'Skipped', 'turn_acquired' => null],
        ],
    ])->assertSessionHasNoErrors();

    $run->refresh();

    expect($run->skills()->count())->toBe(3)
        ->and($run->skills->firstWhere('id', $skills[0]->id)->pivot->status)->toBe('Acquired')
        ->and($run->skills->firstWhere('id', $skills[0]->id)->pivot->turn_acquired)->toBe(4)
        ->and($run->skills->firstWhere('id', $skills[1]->id)->pivot->status)->toBe('Skipped')
        // Untouched by the submit: the third skill keeps the state the pre-populate gave it.
        ->and($run->skills->firstWhere('id', $skills[2]->id)->pivot->status)->toBe('Suggested');
});

it('accepts a submit where the spare row was left empty', function (): void {
    // The form always ships one row nobody has filled in. If the validator treated that as a
    // missing skill_id, every honest save would fail on the row the Trainer did not use.
    $run = labelledRun();
    $skills = Skill::orderBy('export_id')->get();

    $this->post(route('runs.skills.sync', $run), [
        'skills' => [
            0 => ['skill_id' => $skills[0]->id, 'status' => 'Acquired', 'turn_acquired' => 2],
            1 => ['skill_id' => '', 'status' => 'Suggested', 'turn_acquired' => ''],
        ],
    ])->assertSessionHasNoErrors();

    expect($run->refresh()->skills()->count())->toBe(3)
        ->and($run->skills->firstWhere('id', $skills[0]->id)->pivot->turn_acquired)->toBe(2);
});

it('adds a skill through the spare row without disturbing the rows above it', function (): void {
    $run = labelledRun();
    $skills = Skill::orderBy('export_id')->get();

    // A brand new Global skill, not yet on the run, to be added through the spare row.
    $extra = Skill::create([
        'export_id' => 202001,
        'name' => 'Added Through Spare',
        'match_key' => 'addedthroughspare',
        'release_status' => ReleaseStatus::GlobalReleased->value,
        'name_is_client' => true,
    ]);

    $this->post(route('runs.skills.sync', $run), [
        'skills' => [
            0 => ['skill_id' => $skills[0]->id, 'status' => 'Suggested', 'turn_acquired' => null],
            1 => ['skill_id' => $skills[1]->id, 'status' => 'Suggested', 'turn_acquired' => null],
            2 => ['skill_id' => $skills[2]->id, 'status' => 'Acquired', 'turn_acquired' => 11],
            3 => ['skill_id' => $extra->id, 'status' => 'Suggested', 'turn_acquired' => null],
        ],
    ])->assertSessionHasNoErrors();

    $run->refresh();

    expect($run->skills()->count())->toBe(4)
        ->and($run->skills->pluck('id')->all())->toBe($run->skills->pluck('id')->unique()->all())
        ->and($run->skills->firstWhere('id', $extra->id)->pivot->status)->toBe('Suggested')
        ->and($run->skills->firstWhere('id', $skills[2]->id)->pivot->turn_acquired)->toBe(11);
});
