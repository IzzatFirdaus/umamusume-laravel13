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

    $dom = new DOMDocument;
    @$dom->loadHTML($this->get(route('runs.show', $run))->content());
    $xpath = new DOMXPath($dom);

    $rows = [];

    // Re-pointed 2026-10-03 (B.6): a closed row posts its skill through a hidden input, so the
    // row set is the union of the open select and the hidden inputs. The claim is unchanged: one
    // row per seeded skill plus the spare, each posting its own `skills[N][skill_id]`.
    foreach ($xpath->query('//form[@action and .//select[starts-with(@name, "skills[")]]//*[@name]') as $control) {
        /** @var DOMElement $control */
        if (preg_match('/^skills\[(\d+)\]\[skill_id\]$/', $control->getAttribute('name'), $m) === 1) {
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

it('keeps the typed edits when a submit is refused', function (): void {
    // The last thing a Trainer does before hitting save is re-read a row they just fixed. When the
    // server refuses the submit for some other row, the re-rendered form used to hand back the
    // *stored* values instead, so the fix they just made vanished and the row they were re-reading
    // said the opposite. The deck form has rehydrated from `old()` since D-3; this form never did.
    $run = labelledRun();
    $skills = Skill::orderBy('export_id')->get();

    // Every row from 0 up is filled, deliberately. `prepareForValidation` filters the unused rows
    // out and re-indexes with `array_values`, so a submit that skips a middle row validates one
    // index while the flashed old input keeps the index the Trainer sent, and the two stop
    // describing the same row. That misalignment is a separate defect from the one under test here,
    // so it is reported on its own rather than folded into this fix.
    //
    // Within the payload: row 0 is edited to a different skill, a different status, and a turn; row
    // 1 carries the refusal (`min:1` refuses a turn of 0), so the rejection is genuine and row 0's
    // edits are the input at risk. The Referer is sent because a real browser sends one from the
    // form page and `back()` has no other way to find the run. Nothing gets filtered, so the
    // re-index is a no-op and the error key and the old-input key are the same key.
    //
    // One round trip with `followingRedirects()`, because that is what the browser does and it is
    // the only way the flashed `old()` input and the error bag reach the rendered form. Issuing a
    // separate `get()` after the post leaves the form rendering the stored values, which reads as
    // the defect this test is about while actually being a harness artefact; `RunDeckTest` records
    // the same trap for the deck form.
    $response = $this->followingRedirects()
        ->post(
            route('runs.skills.sync', $run),
            [
                'skills' => [
                    0 => ['skill_id' => $skills[2]->id, 'status' => 'Acquired', 'turn_acquired' => 17],
                    1 => ['skill_id' => $skills[0]->id, 'status' => 'Suggested', 'turn_acquired' => 0],
                    2 => ['skill_id' => $skills[1]->id, 'status' => 'Suggested', 'turn_acquired' => null],
                ],
            ],
            ['HTTP_REFERER' => route('runs.show', $run)],
        );

    $response->assertOk();

    $html = $response->content();

    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    $selectedSkill = $xpath->evaluate('string(//select[@name="skills[0][skill_id]"]/option[@selected]/@value)');
    $selectedStatus = trim($xpath->evaluate('string(//select[@name="skills[0][status]"]/option[@selected])'));
    $turnValue = $xpath->evaluate('string(//input[@name="skills[0][turn_acquired]"]/@value)');

    expect((int) $selectedSkill)->toBe($skills[2]->id)
        ->and($selectedStatus)->toBe(SkillAcquisition::Acquired->value)
        ->and((int) $turnValue)->toBe(17);

    // The refused row keeps the number the Trainer typed rather than being quietly corrected to
    // something valid, because the reason it was refused is about to be printed under it.
    expect($xpath->evaluate('string(//input[@name="skills[1][turn_acquired]"]/@value)'))->toBe('0');

    // And the refusal has to be visible on the row that caused it, or the Trainer is left staring
    // at a form that looks like it saved. `text-risk` is what the deck form uses for this, so the
    // class is part of the fact being asserted, and the message has to land inside *this* form
    // rather than somewhere else on the page.
    $errorText = array_map(
        static fn (DOMNode $node): string => trim(preg_replace('/\s+/', ' ', $node->textContent) ?? ''),
        iterator_to_array($xpath->query('//form[.//select[starts-with(@name, "skills[")]]//p[contains(@class, "text-risk")]')),
    );

    expect($errorText)->toHaveCount(1)
        ->and($errorText[0])->toContain('at least 1');

    // The refusal must not have half-written either: the row the edit was aimed at still holds what
    // the pre-populate gave it, because validation runs before the controller body.
    $run->refresh();

    expect($run->skills->firstWhere('id', $skills[0]->id)->pivot->status)->toBe('Suggested')
        ->and($run->skills->firstWhere('id', $skills[0]->id)->pivot->turn_acquired)->toBeNull();
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

it('sizes every control in the skills form to the 44 of DESIGN.md 6.14, and steps the turn input', function (): void {
    // KI-37. The screen shipped `px-2 py-1` with no height, so a real browser measured the selects at 31,
    // the turn input at 30 and the submit button at 32 against `docs/design-research/DESIGN.md` §6.14's
    // height of 44. `h-11` is this repository's idiom for that value (the scenario panels' capsule headers
    // carry it), so this test pins the class rather than a pixel: a DOM parser cannot measure, and the
    // earlier entry's own numbers are what rotted, so re-asserting a number here would repeat its failure.
    // What it can pin is that every control was moved and that the missing `step` was added, which is what
    // §6.14's last bullet asks for and what the browser review confirmed was absent (`step` was `null`).
    $run = labelledRun();

    $dom = new DOMDocument;
    @$dom->loadHTML($this->get(route('runs.show', $run))->content());
    $xpath = new DOMXPath($dom);

    $form = $xpath->query('//form[@action and .//select[starts-with(@name, "skills[")]]')->item(0);
    expect($form)->not->toBeNull('the skills form is not on the page');

    // The count is asserted so the class loop below cannot pass over an empty set: a loop that checks
    // nothing is the failure this repository has filed repeatedly. Re-pointed 2026-10-03 (B.6): the
    // ten whole-catalogue selects collapsed to one open picker, so the visible controls are the open
    // select, the status and turn control on each of the four rows, and the submit. The three closed
    // rows' "Change row N" links carry the same 44px floor as `min-h-11`.
    $openSelects = $xpath->query('.//select[contains(@name, "[skill_id]")]', $form)->length;
    $switchLinks = $xpath->query('.//a[starts-with(normalize-space(.), "Change row ")]', $form)->length;

    expect($openSelects)->toBe(1)
        ->and($switchLinks)->toBe(3);

    $checked = 0;

    foreach ($xpath->query('.//select|.//input|.//button[@type="submit"]', $form) as $control) {
        /** @var DOMElement $control */
        if ($control->getAttribute('type') === 'hidden') {
            continue; // the CSRF field is not a Trainer-facing control
        }

        // `toContain`'s second argument is a second needle, not a failure message (the same trap the
        // `toHaveKey` note at the top of this file records), so the check goes through `str_contains` and
        // the message rides on `toBeTrue`.
        expect(str_contains($control->getAttribute('class'), 'h-11'))
            ->toBeTrue("{$control->nodeName} {$control->getAttribute('name')} is not sized to h-11");
        $checked++;
    }

    // One open select, four status selects, four turn inputs and the submit.
    expect($checked)->toBe(10);

    foreach ($xpath->query('.//a[starts-with(normalize-space(.), "Change row ")]', $form) as $link) {
        /** @var DOMElement $link */
        expect(str_contains($link->getAttribute('class'), 'min-h-11'))
            ->toBeTrue('a Change row link is not sized to min-h-11');
    }

    $turn = $xpath->query('.//input[@type="number"]', $form)->item(0);
    expect($turn)->not->toBeNull()
        ->and($turn->getAttribute('step'))->toBe('1');

    // §10's focus ring, the same treatment Screen D's controls already carry
    // (`resources/views/skills/index.blade.php`:31, `:40`). The button is excluded on purpose: Screen D's
    // button does not carry the ring either, and this change copies that surface rather than inventing.
    foreach ($xpath->query('.//select|.//input[@type="number"]', $form) as $control) {
        expect($control->getAttribute('class'))->toContain('focus-visible:outline-2');
    }
});

it('names the two catalogue commands when no skill is offerable, and still renders a submittable form', function (): void {
    // KI-51's UI consequence, and the state this section had no copy for at all. On a fresh clone
    // `DatabaseSeeder` reaches `SkillSeeder`, which writes nine rows and sets neither `release_status` nor
    // `name_is_client` (its own docblock says the omission is deliberate), so the scope at
    // `Skill::scopeAvailableOnGlobal()` matches none of them and no tracked writer in this tree produces a
    // row this picker can offer. The empty picker is therefore the ordinary first state, not an edge case,
    // and Screen D already names both commands for it (`resources/views/skills/index.blade.php`:89-92).
    $run = TrainingRun::factory()->create();

    $html = $this->get(route('runs.show', $run))->content();

    expect($html)->toContain('uma:fetch gametora-skills')
        ->and($html)->toContain('uma:reparse gametora-skills');

    // And the state must not be a dead control: the placeholder option is present and the spare row is
    // the one the repeater always ships, so the form still renders and still submits.
    $controls = skillsFormControls($html);

    expect($controls)->not->toBe([])
        ->and(collect($controls)->pluck('name'))->toContain('skills[0][skill_id]');
});
