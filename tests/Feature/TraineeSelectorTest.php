<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Enums\ReleaseStatus;
use App\Models\CharacterCard;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Support\ViewErrorBag;

/*
 * The searchable trainee and costume-card selector on "New training run" (FR-A-6, US-3).
 *
 * Which kind of proof each test is, stated up front because the two are not
 * interchangeable and a slice record has to say which one it is leaning on:
 *
 * - RENDERED-DOM PROOF: the page is fetched, parsed with DOMDocument, and the claim is
 *   read off the element the Trainer's browser receives. This is the technique trunk
 *   moved to when it closed its own KI-21 (RaceEntryDisclosureTest, R71): an assertion
 *   about markup text can be satisfied by markup no Trainer can reach, so the relation
 *   between two elements is resolved through the tree instead.
 * - BEHAVIOUR PROOF: a POST is made and the stored row or the validation result is
 *   inspected. Nothing about the JavaScript runtime is involved.
 * - SHAPE PIN: the module's source text is read. These exist only for what a PHP process
 *   cannot observe, which is everything the script does after it runs: there is no JS
 *   test runner here and C-8 forbids adding one. A shape pin does not prove the filter
 *   works; Task 13's browser pass does. So every behaviour claimed below also has a
 *   rendered-DOM or POST proof behind it, and none rests on source text alone.
 *
 * One trap is worth naming, because three of the assertions written for this file hit it:
 * once the roster ships as a JSON block, every trainee name and every card title in it is
 * present in the page no matter what the form controls do. `assertSee('Gold Ship')`
 * therefore says nothing about the no-script select, and
 * `assertSee('[RUN! RUIN! LAUNCHER!]')` says nothing about a redelivered selection. Both
 * are read off elements here instead.
 */

/**
 * The fixture this file measures against. Re-countable from itself: 2 selectable
 * trainees, 3 confirmed Global cards, 5 rows in total.
 */
function selectorRoster(): void
{
    $goldShip = Umamusume::factory()->create([
        'name' => 'Gold Ship', 'slug' => 'gold-ship',
        'name_ja' => 'ゴールドシップ', 'release_status' => ReleaseStatus::GlobalReleased,
    ]);
    CharacterCard::factory()->create([
        'umamusume_id' => $goldShip->id, 'card_id' => 100701, 'title' => '[Red Strife]',
        'rarity' => CardRarity::TwoStar, 'global_release_date' => '2025-06-26', 'is_debut_form' => true,
    ]);
    CharacterCard::factory()->create([
        'umamusume_id' => $goldShip->id, 'card_id' => 100702, 'title' => '[RUN! RUIN! LAUNCHER!]',
        'rarity' => CardRarity::ThreeStar, 'global_release_date' => '2026-07-02',
    ]);

    $fuji = Umamusume::factory()->create([
        'name' => 'Fuji Kiseki', 'slug' => 'fuji-kiseki',
        'name_ja' => 'フジキセキ', 'release_status' => ReleaseStatus::GlobalReleased,
    ]);
    CharacterCard::factory()->create([
        'umamusume_id' => $fuji->id, 'card_id' => 101401, 'title' => '[Four Seasons]',
        'rarity' => CardRarity::ThreeStar, 'global_release_date' => '2025-12-01', 'is_debut_form' => true,
    ]);
}

function createPageHtml(): string
{
    return test()->get('/training-runs/create')->assertOk()->getContent();
}

function selectorXPath(string $html): DOMXPath
{
    $dom = new DOMDocument;
    @$dom->loadHTML($html, LIBXML_NOERROR);

    return new DOMXPath($dom);
}

/**
 * The roster block, decoded out of the element it was parsed from rather than matched out
 * of the source string: the payload is the page's only card list, so reading it back
 * through the DOM is what makes these ids the ones under test.
 *
 * @return list<array<string, mixed>>
 */
function rosterFrom(string $html): array
{
    $block = selectorXPath($html)->query('//script[@id="trainee-roster"]')->item(0);

    expect($block)->not->toBeNull('the page carries no #trainee-roster script block');

    /** @var list<array<string, mixed>> $decoded */
    $decoded = json_decode((string) $block->textContent, true, 512, JSON_THROW_ON_ERROR);

    return $decoded;
}

/**
 * The name and value of every control a browser with scripting off would post from the run
 * form, in document order. Disabled controls are excluded because a browser drops them,
 * which is the whole no-script contract: the combobox's hidden pair ships disabled and the
 * script is what enables it.
 *
 * @return list<array{name: string, value: string}>
 */
function noScriptFields(string $html): array
{
    $xpath = selectorXPath($html);
    $form = $xpath->query('//form[@method="POST"]')->item(0);

    expect($form)->not->toBeNull('the run form is not in the page');

    $fields = [];

    /** @var DOMElement $control */
    foreach ($xpath->query('.//input[@name] | .//select[@name] | .//textarea[@name]', $form) as $control) {
        if ($control->hasAttribute('disabled')) {
            continue;
        }

        $value = '';

        if ($control->tagName === 'select') {
            $chosen = $xpath->query('.//option[@selected]', $control)->item(0)
                ?? $xpath->query('.//option', $control)->item(0);

            $value = $chosen?->getAttribute('value') ?? '';
        } elseif ($control->tagName === 'textarea') {
            $value = $control->textContent;
        } else {
            $value = $control->getAttribute('value');
        }

        $fields[] = ['name' => $control->getAttribute('name'), 'value' => $value];
    }

    return $fields;
}

/**
 * One construct's slice of the module source, between two markers, so a shape pin can say
 * which construct carries the line it checks. A pin over the whole file would pass with the
 * cursor reset sitting in the wrong function. The throw is the loud half: a marker that has
 * moved fails naming the slice rather than returning a substring that asserts cleanly.
 */
function comboboxSection(string $start, string $end): string
{
    $source = (string) file_get_contents(base_path('resources/js/trainee-combobox.ts'));
    $from = strpos($source, $start);
    $to = $from === false ? false : strpos($source, $end, $from + strlen($start));

    if ($from === false || $to === false) {
        throw new RuntimeException("trainee-combobox.ts does not contain the slice {$start} ... {$end}");
    }

    return substr($source, $from, $to - $from);
}

// ---------------------------------------------------------------------------
// RENDERED-DOM PROOFS
// ---------------------------------------------------------------------------

it('advertises the combobox half of the ARIA surface on the rendered page', function (): void {
    selectorRoster();

    $xpath = selectorXPath(createPageHtml());

    // Each of these is a count on one element, so a control carrying the attribute twice,
    // or a listbox that is not there at all, fails instead of passing on the first
    // substring the page happens to contain.
    expect($xpath->query('//input[@role="combobox"]')->length)->toBe(1)
        ->and($xpath->query('//input[@role="combobox"][@aria-expanded="false"]')->length)->toBe(1)
        ->and($xpath->query('//input[@role="combobox"][@aria-autocomplete="list"]')->length)->toBe(1)
        ->and($xpath->query('//input[@role="combobox"][@aria-controls="trainee-listbox"]')->length)->toBe(1)
        ->and($xpath->query('//input[@role="combobox"][@aria-activedescendant]')->length)->toBe(1)
        ->and($xpath->query('//ul[@id="trainee-listbox"][@role="listbox"]')->length)->toBe(1)
        ->and($xpath->query('//p[@data-combobox-status][@aria-live="polite"]')->length)->toBe(1)
        ->and($xpath->query('//script[@id="trainee-roster"][@type="application/json"]')->length)->toBe(1);
});

it('names the combobox input explicitly, since no label reaches it', function (): void {
    selectorRoster();

    $xpath = selectorXPath(createPageHtml());

    // role="combobox" passes with no accessible name at all, so the attribute under test is
    // the name itself: the <label> belongs to the select (one label names one control), the
    // combobox is outside it, and the input would otherwise be unnamed.
    expect($xpath->query('//input[@data-combobox-input][@aria-label="Trainee or costume card name"]')->length)->toBe(1)
        // ...and the popup it opens is named too, since a listbox has no label of its own.
        ->and($xpath->query('//ul[@role="listbox"][@aria-label="Trainees and costume cards"]')->length)->toBe(1)
        // A5: the input is addressable, which is what lets the script move the caption's `for` onto
        // it when it disables the select. The `for` pair itself is asserted on the caption test
        // below, because the markup the server sends still names the select; what the page has to
        // guarantee is that the target of that move exists.
        ->and($xpath->query('//input[@data-combobox-input][@id="trainee-combobox"]')->length)->toBe(1);
});

it('ships the whole roster to the page as data, and the select as trainees only', function (): void {
    selectorRoster();

    $html = createPageHtml();
    $payload = rosterFrom($html);

    $debutCard = CharacterCard::where('card_id', 100701)->firstOrFail();
    $runCard = CharacterCard::where('card_id', 100702)->firstOrFail();

    expect(collect($payload)->pluck('trainee')->all())->toBe(['Fuji Kiseki', 'Gold Ship'])
        ->and($payload[1]['traineeJa'])->toBe('ゴールドシップ')
        // Cards in release order, oldest first, so the list a Trainer reads matches the
        // ordering the catalog page already uses rather than inventing a second one.
        ->and($payload[1]['cards'])->toBe([
            [
                'selectionId' => $debutCard->id,
                'sourceCardId' => 100701,
                'title' => '[Red Strife]',
                'titleKey' => 'Red Strife',
                'releaseDate' => '2025-06-26',
                'debut' => true,
            ],
            [
                'selectionId' => $runCard->id,
                'sourceCardId' => 100702,
                'title' => '[RUN! RUIN! LAUNCHER!]',
                'titleKey' => 'RUN! RUIN! LAUNCHER!',
                'releaseDate' => '2026-07-02',
                'debut' => false,
            ],
        ]);

    // The two ids are different numbers, and the field the payload names is the local
    // primary key StoreTrainingRunRequest's exists rule reads. Asserted across the whole
    // fixture: this file's local ids are 1, 2, 3 while the source ids are 1007xx, so a
    // payload that carried the source id in `selectionId` fails here on every row.
    foreach ($payload as $trainee) {
        foreach ($trainee['cards'] as $card) {
            expect($card['selectionId'])->not->toBe($card['sourceCardId']);
        }
    }

    // One source of truth. The fallback select lists trainees and nothing else: no card
    // title reaches the template as an <option>, so there is no second list to drift.
    $xpath = selectorXPath($html);

    expect($xpath->query('//select[@name="umamusume_id"]/option')->length)->toBe(3)
        ->and($xpath->query('//select[@name="umamusume_id"]/option')->item(0)->getAttribute('value'))->toBe('')
        ->and($xpath->query('//select[.//option[contains(text(), "RUN! RUIN!")]]')->length)->toBe(0);
});

it('puts every Global trainee in the payload, and only confirmed cards in her card list', function (): void {
    selectorRoster();

    $noCards = Umamusume::factory()->create(['name' => 'No Cards', 'slug' => 'no-cards']);
    $jpOnly = Umamusume::factory()->japanOnly()->create(['name' => 'Japan Only One', 'slug' => 'japan-only-one']);
    CharacterCard::factory()->create(['umamusume_id' => $jpOnly->id, 'card_id' => 109001]);
    $shadowed = Umamusume::factory()->create(['name' => 'Unconfirmed Only', 'slug' => 'unconfirmed-only']);
    CharacterCard::factory()->unconfirmed()->create(['umamusume_id' => $shadowed->id, 'card_id' => 109002]);

    $payload = rosterFrom(createPageHtml());

    // A1 corrects this expectation, it does not relax it: the payload now carries all four Global
    // trainees because the combobox must be able to reach every trainee the select offers, while
    // the card gate stays inside the card list at three confirmed cards. The two trainees whose
    // forms are unconfirmed or absent carry `cards: []` instead of vanishing (FR-A-6, FR-B-4), and
    // the [JP-Only] trainee is in neither list, release status being a trainee-level gate. The
    // clause fails in both directions: a payload that gates the trainee out again drops to three
    // rows, and a payload that let the unconfirmed form through sums to four cards.
    expect($payload)->toHaveCount(4)
        ->and(collect($payload)->map(fn (array $trainee): int => count($trainee['cards']))->sum())->toBe(3)
        ->and(collect($payload)->pluck('trainee')->all())->toBe(['Fuji Kiseki', 'Gold Ship', 'No Cards', 'Unconfirmed Only'])
        ->and(collect($payload)->firstWhere('umamusumeId', $noCards->id)['cards'])->toBe([])
        ->and(collect($payload)->firstWhere('umamusumeId', $shadowed->id)['cards'])->toBe([]);

    $xpath = selectorXPath(createPageHtml());

    // The no-script select is gated on the trainee, not on her card. `character_card_id` is
    // nullable and StoreTrainingRunRequest accepts a submission without it, so a Global trainee
    // with no fetched card is still trainable and stays listed: one placeholder plus the four
    // Global trainees. The [JP-Only] row stays out, because release status is a trainee-level
    // gate this fix does not touch.
    expect($xpath->query('//select[@name="umamusume_id"]/option')->length)->toBe(5)
        ->and($xpath->query(sprintf('//option[@value="%d"]', $noCards->id))->length)->toBe(1)
        ->and($xpath->query(sprintf('//option[@value="%d"]', $shadowed->id))->length)->toBe(1)
        ->and($xpath->query(sprintf('//option[@value="%d"]', $jpOnly->id))->length)->toBe(0);
});

it('makes the payload trainee set the same set the no-script select offers', function (): void {
    /*
     * A1. RENDERED-DOM PROOF that the two lists the page ships hold the same trainees. The
     * combobox disables the select and then commits only trainees carrying a payload card, so a
     * trainee the payload drops is unreachable and unpostable for as long as the payload stays
     * that way, which for a Global trainee whose only fetched form is `unconfirmed` is not a
     * pre-fetch transient but the state the controller describes out loud at `:68-71`. The two
     * id sets are decoded from the rendered page and compared directly, so splitting them on
     * either side fails here. The count clause is the presence control that stops two empty
     * lists from passing the comparison.
     */
    selectorRoster();

    $shadowed = Umamusume::factory()->create(['name' => 'Unconfirmed Only', 'slug' => 'unconfirmed-only']);
    CharacterCard::factory()->unconfirmed()->create(['umamusume_id' => $shadowed->id, 'card_id' => 109002]);
    $noCards = Umamusume::factory()->create(['name' => 'No Cards', 'slug' => 'no-cards']);
    Umamusume::factory()->japanOnly()->create(['name' => 'Japan Only One', 'slug' => 'japan-only-one']);

    $html = createPageHtml();
    $xpath = selectorXPath($html);

    $selectIds = [];

    /** @var DOMElement $option */
    foreach ($xpath->query('//select[@name="umamusume_id"]/option[@value!=""]') as $option) {
        $selectIds[] = (int) $option->getAttribute('value');
    }

    $payload = rosterFrom($html);
    $payloadIds = array_map(static fn (array $row): int => (int) $row['umamusumeId'], $payload);

    sort($selectIds);
    sort($payloadIds);

    expect($payloadIds)->toBe($selectIds)
        ->and($selectIds)->toHaveCount(4)
        // The two trainees the gated payload used to lose are in it with an empty card list, not
        // with an invented card: it is their form that is unconfirmed, not their place on it.
        ->and(collect($payload)->firstWhere('umamusumeId', $shadowed->id)['cards'])->toBe([])
        ->and(collect($payload)->firstWhere('umamusumeId', $noCards->id)['cards'])->toBe([]);
});

it('lists every Global trainee in the no-script select when the database holds no costume card at all', function (): void {
    /*
     * The shape of a freshly seeded dev database: `DatabaseSeeder` calls the Umamusume, Skill
     * and ScenarioSlot seeders, and no seeder writes `character_cards`. That table is filled by
     * a fetch which has not run yet, so every other test in this file is hand-creating card rows
     * a Trainer's own database does not have. A trainee list gated on those rows leaves the rail
     * uncompletable until the fetch lands, which is not progressive enhancement.
     */
    $first = Umamusume::factory()->create(['name' => 'Cardless One', 'slug' => 'cardless-one']);
    $second = Umamusume::factory()->create(['name' => 'Cardless Two', 'slug' => 'cardless-two']);
    $jpOnly = Umamusume::factory()->japanOnly()->create(['name' => 'Japan Only Cardless', 'slug' => 'japan-only-cardless']);

    expect(CharacterCard::count())->toBe(0);

    $html = createPageHtml();
    $xpath = selectorXPath($html);

    // Placeholder plus both Global trainees, and the release gate is still a gate.
    expect($xpath->query('//select[@name="umamusume_id"]/option')->length)->toBe(3)
        ->and($xpath->query(sprintf('//option[@value="%d"]', $first->id))->length)->toBe(1)
        ->and($xpath->query(sprintf('//option[@value="%d"]', $second->id))->length)->toBe(1)
        ->and($xpath->query(sprintf('//option[@value="%d"]', $jpOnly->id))->length)->toBe(0)
        // A1 corrects this clause from `rosterFrom($html))->toBe([])`, which was true of the
        // previous round and is the divergence itself: an empty payload hides the combobox, so the
        // two trainees on this page were reachable only through the select the script disables. The
        // card filter still lives inside the payload, so what no card rows produce is two trainees
        // carrying an empty card list, not a missing key and not an empty list. The sum fails if a
        // card appears that the query filters out, and the name list fails if the trainee gate
        // returns.
        ->and(collect(rosterFrom($html))->pluck('trainee')->all())->toBe(['Cardless One', 'Cardless Two'])
        ->and(collect(rosterFrom($html))->map(fn (array $trainee): int => count($trainee['cards']))->sum())->toBe(0);

    // Then the rail completes, posting the rendered form's own field set with the cardless
    // trainee substituted for the placeholder. This is the C1 failure mode: a trainee with no
    // card row used to be unpostable because she was not offered at all.
    $fields = [];

    foreach (noScriptFields($html) as $field) {
        $fields[$field['name']] = $field['name'] === 'umamusume_id' ? (string) $first->id : $field['value'];
    }

    test()->post('/training-runs', $fields)->assertSessionHasNoErrors();

    $run = TrainingRun::firstOrFail();

    expect($run->umamusume_id)->toBe($first->id)
        ->and($run->character_card_id)->toBeNull();
});

it('hands the module a payload it can refuse, with the native select still intact', function (): void {
    /*
     * A4. RENDERED-DOM PROOF of reachability, not of the branch itself: the module's
     * `! Array.isArray(rows)` guard means nothing until a page can arrive carrying a non-list,
     * and a branch no input reaches is dead code rather than a guard. `rosterJson` is assembled
     * by the controller, so the corrupted payload is rendered straight into the view here. The
     * block then decodes to a map with string keys, which is what that guard returns on, and the
     * picker the guard exists to leave alone is what the page still ships: a select with no
     * `disabled`, still `required`, still the only control that submits, with the hidden pair
     * waiting disabled and the combobox root still hidden. The pin `hands the form over only
     * after a payload that paints` holds the module half of the same contract. No `console.*`
     * call signals the failure: `grep -rn "console\." resources/js` is empty, so a warn would be
     * a new convention rather than a fix, and this test is the repo-consistent answer.
     */
    selectorRoster();

    $html = view('runs.create', [
        'umamusumes' => Umamusume::query()
            ->where('release_status', ReleaseStatus::GlobalReleased->value)
            ->orderBy('name')
            ->get(),
        'rosterJson' => ['not' => 'a roster'],
        'selectedLabel' => null,
        'scenarios' => ['ura_finale' => 'Ura Finale'],
        'errors' => new ViewErrorBag,
    ])->render();

    $decoded = rosterFrom($html);
    $xpath = selectorXPath($html);

    expect(array_is_list($decoded))->toBeFalse()
        ->and($decoded)->toBe(['not' => 'a roster'])
        ->and($xpath->query('//select[@name="umamusume_id"][@disabled]')->length)->toBe(0)
        ->and($xpath->query('//select[@name="umamusume_id"][@required]')->length)->toBe(1)
        ->and($xpath->query('//input[@name="umamusume_id"][@data-combobox-umamusume-id][@disabled]')->length)->toBe(1)
        ->and($xpath->query('//div[@data-combobox][@class="mt-1 hidden"]')->length)->toBe(1);
});

it('associates the caption with one control, and keeps the combobox out of that label', function (): void {
    selectorRoster();

    $xpath = selectorXPath(createPageHtml());

    // A <label> may name exactly one labelable control, and an implicit association resolves to
    // the first one in the tree, which here is the select this script disables. Wrapping both
    // controls therefore gave the caption a click target that goes nowhere on the scripted path.
    // Three clauses because any one of them passes with the wrong structure: the caption has to
    // name the select by id, the combobox has to be outside every label, and no second labelable
    // control may be left inside the caption's label.
    expect($xpath->query('//label[@for="umamusume-select"]/select[@id="umamusume-select"]')->length)->toBe(1)
        ->and($xpath->query('//label//input[@data-combobox-input]')->length)->toBe(0)
        ->and($xpath->query(
            '//label[@for="umamusume-select"]//input'.
            '| //label[@for="umamusume-select"]//textarea'.
            '| //label[@for="umamusume-select"]//select[not(@id="umamusume-select")]'
        )->length)->toBe(0);
});

it('keeps a native select as the no-script path, and that path really submits', function (): void {
    selectorRoster();

    $html = createPageHtml();
    $fields = noScriptFields($html);
    $names = array_column($fields, 'name');

    // A display:none control still submits, so the combobox's hidden pair ships disabled
    // and only the script enables it. Reversed, a scriptless POST sends the Trainer's pick
    // followed by the hidden empty value, PHP keeps the last, and `required` fails on the
    // one path that exists precisely because there is no script. This is the assertion the
    // bug turns on: `umamusume_id` must appear once in what the form actually sends, not
    // twice with the empty value last.
    expect($names)->toContain('umamusume_id')
        ->and(array_count_values($names)['umamusume_id'] ?? 0)->toBe(1)
        ->and($names)->not->toContain('character_card_id');

    $xpath = selectorXPath($html);

    // And the disabled pair is in the page waiting for the script: proving it exists as
    // well as stays inert, so a reader can see which half of the handover is which. The
    // two named-control counts are the presence control that keeps the `toBe(1)` above
    // from passing vacuously: the page really does carry two `umamusume_id` controls, and
    // only one of them survives the browser's drop of disabled fields.
    expect($xpath->query('//select[@name="umamusume_id"] | //input[@name="umamusume_id"]')->length)->toBe(2)
        ->and($xpath->query('//input[@name="umamusume_id"][@data-combobox-umamusume-id][@disabled]')->length)->toBe(1)
        ->and($xpath->query('//input[@name="character_card_id"][@data-combobox-card-id][@disabled]')->length)->toBe(1)
        ->and($xpath->query('//select[@name="umamusume_id"][@required]')->length)->toBe(1);

    // Then post exactly what the rendered form says it sends, with the trainee the Trainer
    // picked substituted for the empty placeholder the page ships with. Nothing here is
    // typed by the test that the page did not also offer.
    $goldShip = Umamusume::where('slug', 'gold-ship')->firstOrFail();
    $payload = [];

    foreach ($fields as $field) {
        $payload[$field['name']] = $field['name'] === 'umamusume_id' ? (string) $goldShip->id : $field['value'];
    }

    // The page ships a selected Status of its own, so the trainee is the only value the
    // Trainer supplies by hand and everything else reaches the server as rendered.
    expect(array_column($fields, 'value', 'name')['status'])->toBe('Active');

    test()->post('/training-runs', $payload)->assertSessionHasNoErrors();

    $run = TrainingRun::firstOrFail();

    expect($run->umamusume_id)->toBe($goldShip->id)
        ->and($run->character_card_id)->toBeNull();
});

it('hands the trainee back to the no-script select when another field fails', function (): void {
    selectorRoster();

    $goldShip = Umamusume::where('slug', 'gold-ship')->firstOrFail();

    // Stickiness on this path is `@selected()` against `old()`, so it is read back out of
    // the redelivered DOM. The roster block names every trainee regardless, which is why
    // this resolves through the <option> rather than by looking for the name in the page.
    $xpath = selectorXPath(test()->withSession(['_old_input' => [
        'umamusume_id' => (string) $goldShip->id,
        'status' => 'Active',
        'scenario' => 'ura_finale',
    ]])->get('/training-runs/create')->assertOk()->getContent());

    expect($xpath->query(sprintf('//select[@name="umamusume_id"]/option[@value="%d"][@selected]', $goldShip->id))->length)->toBe(1)
        ->and($xpath->query('//select[@name="umamusume_id"]/option[@selected][@value=""]')->length)->toBe(0);
});

it('carries the chosen card back to the form when another field fails', function (): void {
    selectorRoster();

    $goldShip = Umamusume::where('slug', 'gold-ship')->firstOrFail();
    $card = CharacterCard::where('card_id', 100702)->firstOrFail();

    // POST, fail validation on `scenario`, redirect back with old() flashed, render. The
    // selection has to survive that round trip: a form that forgets the trainee on a 422
    // is a form the Trainer submits twice. StoreTrainingRunRequest already flashes input on
    // failure, so nothing new is needed to pass this.
    $xpath = selectorXPath(test()->withHeader('referer', url('/training-runs/create'))
        ->followingRedirects()
        ->post('/training-runs', [
            'umamusume_id' => $goldShip->id,
            'character_card_id' => $card->id,
            'status' => 'Active',
            'scenario' => 'not_a_scenario',
        ])
        ->assertOk()
        ->getContent());

    // The roster block names this title on its own, so assertSee() on the title would pass
    // with the selection lost. The proof is the value attribute of the combobox input, and
    // the two hidden fields that have to still name the same pair.
    expect($xpath->query('//input[@data-combobox-input]')->item(0)?->getAttribute('value'))
        ->toBe('Gold Ship · [RUN! RUIN! LAUNCHER!]')
        ->and($xpath->query('//select[@name="umamusume_id"]/option[@value="'.$goldShip->id.'"][@selected]')->length)->toBe(1)
        ->and($xpath->query(sprintf('//input[@data-combobox-umamusume-id][@value="%d"]', $goldShip->id))->length)->toBe(1)
        ->and($xpath->query(sprintf('//input[@data-combobox-card-id][@value="%d"]', $card->id))->length)->toBe(1);
});

// ---------------------------------------------------------------------------
// BEHAVIOUR PROOFS
// ---------------------------------------------------------------------------

it('restores the chosen scenario when the submission fails on another field', function (): void {
    selectorRoster();

    $goldShip = Umamusume::where('slug', 'gold-ship')->firstOrFail();
    $card = CharacterCard::where('card_id', 100702)->firstOrFail();

    // The scenario is the third control on this form and its stickiness was asserted nowhere.
    // A valid scenario posted with a `status` the enum rejects is the shape of the mistake this
    // answers: the form comes back for one bad field with the composition the Trainer chose
    // still chosen. The brief's `assertSee('not_a_scenario', false)` could never have proven
    // this, because a value outside the matrix matches no option, so nothing on the page can
    // echo it back as selected. It is rendered-DOM in method, and its proof is a POST.
    $xpath = selectorXPath(test()->withHeader('referer', url('/training-runs/create'))
        ->followingRedirects()
        ->post('/training-runs', [
            'umamusume_id' => $goldShip->id,
            'character_card_id' => $card->id,
            'status' => 'Not A Status',
            'scenario' => 'ura_finale',
        ])
        ->assertOk()
        ->getContent());

    // The selected option, read through the tree. A page that lost the scenario shows the
    // empty option selected instead, which the second clause refuses.
    expect($xpath->query('//select[@name="scenario"]/option[@value="ura_finale"][@selected]')->length)->toBe(1)
        ->and($xpath->query('//select[@name="scenario"]/option[@selected][@value=""]')->length)->toBe(0)
        ->and($xpath->query(sprintf('//select[@name="umamusume_id"]/option[@value="%d"][@selected]', $goldShip->id))->length)->toBe(1);
});

it('submits the local card id the payload carries, and refuses the source id', function (): void {
    selectorRoster();

    $goldShip = Umamusume::where('slug', 'gold-ship')->firstOrFail();
    $card = CharacterCard::where('card_id', 100702)->firstOrFail();

    // The value the page's own roster offers, read out of the payload rather than the
    // fixture, so this posts exactly what a Trainer's browser would post.
    $fromPayload = collect(collect(rosterFrom(createPageHtml()))
        ->firstWhere('trainee', 'Gold Ship')['cards'])
        ->firstWhere('sourceCardId', 100702);

    expect($fromPayload['selectionId'])->toBe($card->id);

    test()->post('/training-runs', [
        'umamusume_id' => $goldShip->id,
        'character_card_id' => $fromPayload['selectionId'],
        'status' => 'Active',
    ])->assertSessionHasNoErrors()->assertRedirect();

    expect(TrainingRun::firstOrFail()->character_card_id)->toBe($card->id);

    // And the id a client that mixed the two up would send. 100702 is not a local primary
    // key in this fixture, so the `exists` rule refuses it: the form comes back with an
    // error beside that field rather than writing a run that points at nothing.
    test()->post('/training-runs', [
        'umamusume_id' => $goldShip->id,
        'character_card_id' => 100702,
        'status' => 'Active',
    ])->assertSessionHasErrors('character_card_id');

    expect(TrainingRun::count())->toBe(1);
});

it('rejects a card that belongs to a different trainee', function (): void {
    selectorRoster();

    $fuji = Umamusume::where('slug', 'fuji-kiseki')->firstOrFail();
    $goldShipCard = CharacterCard::where('card_id', 100702)->firstOrFail();

    /*
     * A fetch can re-parent a card when the source's char-ref mapping corrects itself,
     * which Task 7 pinned. A roster left open in a stale tab would then post a card that
     * now belongs to someone else, and StoreTrainingRunRequest's `where('umamusume_id',
     * ...)` is what turns that into a rejected pair instead of a run that contradicts
     * itself. This test keeps that rule load-bearing.
     *
     * What the Trainer sees is the form again with a message beside the card field and
     * their own input still in it. What they do not see is a created run.
     */
    test()->post('/training-runs', [
        'umamusume_id' => $fuji->id,
        'character_card_id' => $goldShipCard->id,
        'status' => 'Active',
    ])->assertSessionHasErrors('character_card_id');

    expect(TrainingRun::count())->toBe(0);
});

it('escapes a client title in the JSON block rather than trusting it', function (): void {
    $u = Umamusume::factory()->create(['name' => 'Bad Title One', 'slug' => 'bad-title-one']);
    CharacterCard::factory()->create([
        'umamusume_id' => $u->id, 'card_id' => 199999,
        'title' => '[</script><script>alert(1)</script>]', 'is_debut_form' => true,
    ]);

    $html = createPageHtml();

    // Titles are source data from a Tier B export. The JSON block hex-encodes the angle
    // brackets and quotes, so a title can never close the script element it lives in.
    expect($html)->not->toContain('<script>alert(1)')
        // And the encoding is not lossy: the title decodes back exactly as it was stored,
        // which is what lets the script render the verbatim client string.
        ->and(collect(rosterFrom($html))->firstWhere('trainee', 'Bad Title One')['cards'][0]['title'])
        ->toBe('[</script><script>alert(1)</script>]');
});

// ---------------------------------------------------------------------------
// SHAPE PINS. The module's source text is read because the behaviour it describes
// happens in a JS runtime this suite cannot start: there is no JS test runner, and C-8
// forbids adding one. These hold shape in place. They are NOT proofs that the filter,
// the cap, or the keyboard handling works; Task 13's browser pass is that proof.
// ---------------------------------------------------------------------------

it('wires the module into the entry the browser actually runs', function (): void {
    // Same reasoning KeyboardPathTest's entry-point wiring test records: a module on disk
    // proves nothing about it executing. Without the import the combobox is dead markup,
    // and the browser pass would report the keys as broken rather than missing.
    expect((string) file_get_contents(base_path('resources/js/app.ts')))
        ->toContain("import './trainee-combobox';");
});

it('filters on all three fields, caps the list, and adds no dependency', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/trainee-combobox.ts'));

    expect($source)->toContain('startsWith')
        ->and($source)->toContain('traineeJa')
        ->and($source)->toContain('titleKey')
        ->and($source)->toContain('MAX_VISIBLE = 10')
        ->and($source)->toContain('DEFAULT_VISIBLE = 10')
        ->and($source)->toContain("'ArrowDown'")
        ->and($source)->toContain("'ArrowUp'")
        ->and($source)->toContain("'Escape'")
        ->and($source)->toContain('aria-activedescendant')
        // textContent throughout, never innerHTML: titles arrive from a Tier B export.
        ->and($source)->not->toMatch('/innerHTML|document\.write|eval\(/')
        // No package import: `dependencies` in package.json stays empty (C-8).
        ->and($source)->not->toMatch('/from\s+["\'](?!\.)/');
});

it('builds the option role the listbox needs, keeps the header out of the tree, and takes the select over', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/trainee-combobox.ts'));
    $handover = comboboxSection('fallback.disabled = true;', 'const setOpen =');

    // These roles live only in nodes the script creates, so the rendered page cannot show
    // them and a shape pin is the only check available. The header is deliberately not an
    // option: a heading inside the option list would be a row a Trainer could pick. It is
    // presentation rather than `role="group"` because a group has to own its options to name
    // them, this header is a sibling of them, and `group` is not name-from-content in ARIA,
    // so the shape as built was an unnamed group that carried no trainee either way. The name
    // goes into each option instead, which the pin below holds.
    expect($source)->toContain("'presentation'")
        // A6: this clause closes off the other fix the previous review permitted, a real
        // `role="group"` container with `aria-labelledby`, on purpose rather than by accident. It
        // fails that shape because the per-option accessible name below is what the browser pass
        // can be held to, and a container only names a card if the AT resolves it at the moment
        // navigation moves. An implementer who wants the group has to delete this clause in review,
        // keep the per-option name, and say why in Task 13's record.
        ->and($source)->not->toContain("'group'")
        ->and($source)->toContain("'option'")
        ->and($source)->toContain('aria-selected')
        ->and($source)->toContain('data-combobox-listbox')
        // The handover, in one block: the native select stops submitting and the hidden
        // pair starts. This is the other half of what `noScriptFields()` above proves the
        // page ships, so the two tests describe one contract from both ends.
        ->and($source)->toMatch('/fallback\.disabled\s*=\s*true/')
        ->and($source)->toMatch('/traineeField\.disabled\s*=\s*false/')
        ->and($source)->toMatch('/cardField\.disabled\s*=\s*false/')
        // A5: the caption moves with the handover it belongs to, inside the same block, so a
        // disabled select cannot be left as the thing the word "Umamusume" points at. Sliced
        // rather than matched over the whole file, because a `for` repoint anywhere else is a
        // repoint that survives the payload guard this block sits behind.
        ->and($handover)->toContain("fallback.closest('label')?.setAttribute('for', input.id);");
});

it('opens and closes the listbox with no cursor, so Enter stays a submission', function (): void {
    // SHAPE PIN, not a behaviour proof: the cursor lives in a JS runtime this suite cannot
    // start (no JS runner, and C-8 forbids adding one). What would catch this in Task 13's
    // browser pass is a rendered-DOM assertion that after focus the listbox is open with
    // `aria-activedescendant=""` and no `aria-selected="true"` row, then one Enter posting the
    // pair the field had already committed. These pins fail if a reset moves into the wrong
    // construct, which is how the three cursor paths are held to one rule.
    $focus = comboboxSection("input.addEventListener('focus'", "input.addEventListener('input'");
    $close = comboboxSection('const setOpen =', 'const options =');
    $arrow = comboboxSection('if (!open) {', 'const count = options().length');
    $step = comboboxSection('const count = options().length;', 'options()[active]?.scrollIntoView');

    // I2: opening from focus lands with no cursor. Without it, a refocused committed field shows
    // the default list, whose row zero is the newest card in the whole roster, and the first
    // Enter re-picks someone else's form instead of submitting. That pair is self-consistent, so
    // StoreTrainingRunRequest's trainee-card join cannot see the disagreement.
    expect($focus)->toContain('active = -1;')
        ->and(strpos($focus, 'active = -1;'))->toBeLessThan((int) strpos($focus, 'setOpen(true);'))
        // A3: the ordering that holds I2 is the reset coming *after* `refresh()`, not merely
        // before `setOpen(true)`. `refresh()` ends by parking the cursor on row zero, so a reset
        // lifted above it is undone before the popup opens and row zero is highlighted again,
        // which is I2 reintroduced while every clause above still passes. This comparison is what
        // catches that move.
        ->and(strpos($focus, 'active = -1;'))->toBeGreaterThan((int) strpos($focus, 'refresh();'))
        // I6: Escape, blur and commit all close through here, and a closed listbox must own no
        // cursor, because `aria-activedescendant` has to name a row a screen reader can reach.
        ->and($close)->toContain('if (!value) {')
        ->and(strpos($close, 'active = -1;'))->toBeGreaterThan((int) strpos($close, 'if (!value) {'))
        ->and(strpos($close, 'paintActive();'))->toBeGreaterThan((int) strpos($close, 'active = -1;'))
        // Reopening by arrow starts from no cursor too, which is what tells the step below it
        // that this press opens rather than moves.
        ->and($arrow)->toContain('active = -1;')
        // A2: and the step resolves that no-cursor press per key, because a shared increment from
        // -1 lands ArrowDown on row zero but ArrowUp on `count - 2`, i.e. one short of the last
        // row, skipping the newest card the reopen exists to reach. SHAPE PIN: this is arithmetic
        // in a JS runtime with no runner here, so it pins the branch rather than its result, and
        // Task 13's browser pass is what reads the highlighted row. Reverting to the single
        // modulo ternary fails both clauses, since neither `active < 0` nor `count - 1` is in it.
        ->and($step)->toContain('active < 0')
        ->and($step)->toContain('count - 1');
});

it('names the empty and capped states in words a Trainer reads', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/trainee-combobox.ts'));

    expect($source)->toContain('No trainee or card found.')
        ->and($source)->toContain('keep typing');
});

it('gives every option the trainee it belongs to in its own accessible name', function (): void {
    $header = comboboxSection('const header = document.createElement', 'listbox.append(header)');
    $option = comboboxSection('const option = document.createElement', 'listbox.append(option)');

    // I4: the header cannot carry the grouping (see the roles pin), so navigating one trainee's
    // cards has to say whose they are out of the option's own accessible text. The separator is
    // the catalog's `·`, because an accessible name is copy a screen reader speaks.
    expect($header)->toContain("setAttribute('role', 'presentation')")
        ->and($option)->toContain('setAttribute(\'aria-label\', `${hit.trainee.trainee} · ')
        ->and($option)->not->toMatch('/[\x{2013}\x{2014}]/u');
});

it('paints a trainee with no confirmed costume card as one row she can pick', function (): void {
    /*
     * A1's module half, and a SHAPE PIN for it: the set parity is proven from the rendered page
     * above, but that the combobox paints a cardless trainee and commits her with no card id
     * happens in a JS runtime this suite cannot start. What would catch it in Task 13's browser
     * pass: on a payload whose row carries `cards: []`, type her name, expect the one option
     * saying no costume card is confirmed, take it, then assert the hidden trainee field holds
     * her id, the hidden card field is empty, and the submit creates a run with a null card.
     */
    $cardless = comboboxSection('const cardlessRow =', 'const rowsFor');
    $collect = comboboxSection('const collect =', 'const render = (');
    $filter = comboboxSection('const matchesQuery =', 'const exactTraineeName');
    $enter = comboboxSection('const chooseDebutByTraineeName', 'input.addEventListener');
    $commit = comboboxSection('const commit =', 'const choose =');

    expect($cardless)->toContain('No costume card confirmed yet')
        ->and($cardless)->toContain('cardless: true')
        // The row names her and states the gap in words: no dash, no invented or normalised card
        // title, and an empty match key, because there is no epithet to prefix and a card query
        // must not reach her through a form she does not have confirmed.
        ->and($cardless)->toContain("titleKey: ''")
        ->and($cardless)->not->toMatch('/[\x{2013}\x{2014}]/u')
        // Every list the module builds routes through `rowsFor`, so the synthetic row is a match
        // for its trainee exactly like a card is and counts in the number the status line speaks.
        ->and($collect)->toContain('rowsFor(')
        ->and($filter)->toContain('rowsFor(')
        ->and($enter)->toContain('rowsFor(')
        ->and($commit)->toContain("card.cardless ? '' : String(card.selectionId)");
});

it('puts the cardless band in the default list, under a divider, with no typing', function (): void {
    /*
     * The owner's ruling of 2026-09-30: a separate band, not the bottom of the list. The previous
     * shape was defensible but buried her. `collect` sorted every row by release date descending
     * and `render` took one slice of ten, and a cardless row's `releaseDate` is the empty string,
     * so on any roster that holds even one confirmed card she lands below the cap and the only way
     * to see her is to type her name. Set parity and name-reachability are proven from the rendered
     * page above; what is pinned here is the ordering and the window, because those are what put
     * the row on screen. What the browser pass adds, and only a browser can add: focus the field on
     * a database that holds cards for some trainees and none for others, type nothing, and read the
     * popup top to bottom. `docs/design-research/verification/` carries that evidence.
     */
    $collect = comboboxSection('const collect =', 'const windowFor =');
    $window = comboboxSection('const windowFor =', 'const render = (');
    $divider = comboboxSection('const bandDivider =', 'const sortHits =');
    $loop = comboboxSection('let cardedBandSeen = false;', 'if (hit.trainee.umamusumeId !== lastTrainee)');
    $paint = comboboxSection('const option = document.createElement', 'option.setAttribute(');
    $predicate = comboboxSection('const isCardless =', 'const matchesQuery =');

    expect($predicate)->toContain('hit.card.cardless === true')
        // One definition of the question the whole default list turns on, so the band order in
        // `collect`, the two windows in `windowFor`, the seam in the loop and the element id cannot
        // each spell it a slightly different way and drift.
        ->and($collect)->toContain('.filter((hit) => !isCardless(hit))')
        ->and($collect)->toContain('.filter(isCardless)')
        // Carded first, then cardless: the order is the band, and `windowFor` reads it back off
        // the flag rather than off a position, so the two cannot drift apart silently.
        ->and($collect)->toContain('[...carded, ...cardless]')
        // Two windows, one per band, both at the existing cap. One `.slice()` over the combined
        // list is the shape that hid her, so the count of caps in this section is the assertion.
        ->and(substr_count($window, 'DEFAULT_VISIBLE'))->toBe(2)
        ->and($window)->toContain('.filter(isCardless).slice(0, DEFAULT_VISIBLE)')
        ->and($window)->toContain('matches.slice(0, MAX_VISIBLE)')
        // The seam is presentation, not an option: `options()` selects `li[role="option"]`, and a
        // row the cursor can land on and then commit would be a row with no card id to commit.
        ->and($divider)->toContain("setAttribute('role', 'presentation')")
        ->and($divider)->toContain('data-band-divider')
        ->and($divider)->toContain('No confirmed costume card yet')
        ->and($divider)->not->toMatch('/[\x{2013}\x{2014}]/u')
        // Painted once, and only behind a confirmed band. On a database with no costume card at
        // all every row is cardless, and a divider naming "these" would have no other side.
        ->and($loop)->toContain('cardedBandSeen && !bandDividerPainted')
        ->and(substr_count($loop, 'bandDividerPainted = true'))->toBe(1)
        // Found by the browser pass, not by reading: a cardless row's `selectionId` is the
        // placeholder 0 and the element id was built from it, so the three cardless rows in one
        // paint all answered to `trainee-option-0`. `aria-activedescendant` is set from
        // `options()[active].id`, which a screen reader resolves by id, so the row the cursor was
        // on and the row announced were different elements. She is keyed on her trainee id now.
        ->and($paint)->toContain('`u${hit.trainee.umamusumeId}` : hit.card.selectionId');
});

it('hands the form over only after a payload that paints', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/trainee-combobox.ts'));
    $guard = comboboxSection('try {', 'leave the native select alone');

    // I5: JSON.parse accepts a string, a number, or an object shaped nothing like a roster, and
    // the `as TraineeRow[]` cast hides that from TypeScript until the first `flatMap` throws.
    // The array guard and the first paint therefore sit in the same try as the parse, ahead of
    // the handover. That is what makes the comment on that catch true: hand over first and the
    // page keeps neither picker, because the native select is disabled and the hidden pair is
    // enabled and empty.
    expect($guard)->toContain('Array.isArray(rows)')
        ->and($guard)->toContain('render(rows, \'\', listbox, status, true)')
        ->and($guard)->toContain('rows.length === 0')
        ->and(strpos($source, 'fallback.disabled = true'))->toBeGreaterThan((int) strpos($source, 'catch {'));
});

it('keeps the live region quiet until the Trainer opens the list', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/trainee-combobox.ts'));
    $render = comboboxSection('const render = (', 'export const initTraineeCombobox');
    $say = comboboxSection('const say =', 'status.textContent = text;');

    // M5: some AT announce a mutation to a polite live region even when nobody opened the
    // popup, so a page load writing "10 matches" into it was stating a fact about a list
    // the Trainer had not asked for. Both boot paints pass the flag; every status line after
    // that one answers an action the Trainer took.
    //
    // A6: the requirement is "no paint the Trainer did not ask for writes the live region", which
    // the old `substr_count($render, 'if (!silent) {') === 2` restated as a tally of control flow,
    // so refactoring `render()` broke the count without breaking the rule. `render` now has one
    // writer and that writer is the gated one, which is what these clauses say: the guard sits
    // between the writer's declaration and its write. Proving nothing else in `render` touches the
    // live region is the one piece that cannot be phrased without counting source text, so the
    // clause below counts writers rather than `if` blocks.
    expect($render)->toContain('silent = false')
        ->and($say)->toContain('if (!silent)')
        ->and(substr_count($render, 'status.textContent'))->toBe(1)
        ->and($source)->toContain('refresh(true)');
});

it('joins a trainee to her card with a middle dot, never a dash', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/trainee-combobox.ts'));

    // R-02 and D-79 keep an em or en dash out of shipped copy, and the selection label is
    // copy: it lands in the input's value and in the status line. The positive half is
    // proven on the rendered page by the redelivery test above, which reads the shipped
    // label as `Gold Ship · [RUN! RUIN! LAUNCHER!]`. This is the negative half, which only
    // a source read can reach: RenderedCopyHygieneTest scans Blade, so the labels this
    // module builds are outside its coverage rather than outside the rule.
    expect($source)->not->toMatch('/[\x{2013}\x{2014}]/u');
});
