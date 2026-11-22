<?php

declare(strict_types=1);

use App\Models\CharacterCard;
use App\Models\Umamusume;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the snapshot entry screen', function (): void {
    $this->get(route('career.snapshot'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Career/Snapshot/Entry'));
});

it('resolves both halves of the dashboard\'s second entry point', function (): void {
    // The link itself is Vue template copy, so it does not appear in the HTML a Pest test sees:
    // Inertia answers a page request with the shell and the data-page payload, and the Dashboard
    // renders client-side (the rendered-copy assertion is browser work, per the migration rule the
    // career specs record). What Pest can pin is that both ends of the link resolve: the dashboard
    // as its component, and the address the card points at as the entry screen.
    $this->get(route('home'))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Dashboard'));

    $this->get('/career/snapshot')->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Career/Snapshot/Entry'));
});

it('renders the setup form the entry screen leads to', function (): void {
    $this->get(route('career.snapshot.setup'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Snapshot/Setup')
            ->has('trainees')
            ->has('scenarios', 4)
            ->has('years', 3)
            ->has('phases', 2));
});

it('labels every position the setup selects can name, matching the review sentence (N3.32)', function (): void {
    // The setup screen had no readout: a Trainer chose year/month/half and first saw the position
    // spelled out on the review, one screen later. The table is derived by the same owners that
    // label the review (`CareerCalendar::turnIndexFor` + `CareerPositionLabel::forTurn`), so the two
    // screens cannot disagree for one selection.
    $this->get(route('career.snapshot.setup'))
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $page->component('Career/Snapshot/Setup')
                // 3 years x 12 months x 2 halves.
                ->has('position_labels', 72)
                ->where('position_labels.3:10:Early', "Senior Year \u{00B7} Early October")
                ->where('position_labels.1:1:Late', "Junior Year \u{00B7} Late January")
                ->where('position_labels.3:12:Late', "Senior Year \u{00B7} Late December");
        });

    // The review's own sentence for the same selection, from the same owner, so the readout cannot
    // drift into a second vocabulary.
    $this->post(route('career.snapshot.review'), [
        'umamusume_id' => Umamusume::factory()->create()->id,
        'scenario' => 'unity_cup',
        'career_year' => 3,
        'career_month' => 10,
        'career_phase' => 'Early',
        'field_states' => ['speed' => 'not_provided'],
    ])
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/Snapshot/Review')
            ->where('position_label', "Senior Year \u{00B7} Early October"));
});

it('ships the combobox roster with confirmed costume cards (N3.31 backend unblock)', function (): void {
    // N3.31 blocked on the setup payload: the picker roster carried {id, name} only, so
    // TraineeCombobox had no costume cards to offer. The roster now travels in the exact
    // TraineeRow shape Runs/Create feeds, from one shared read pattern — no second endpoint.
    $trainee = Umamusume::factory()->create(['name' => 'Zeta Roster', 'name_ja' => 'ゼータ']);

    $confirmed = CharacterCard::factory()->for($trainee, 'umamusume')->create([
        'title' => '[Roster Base]',
        'unconfirmed' => false,
        'is_debut_form' => true,
    ]);
    CharacterCard::factory()->for($trainee, 'umamusume')->create([
        'title' => '[Hidden Form]',
        'unconfirmed' => true,
    ]);

    $this->get(route('career.snapshot.setup'))
        ->assertOk()
        ->assertInertia(function (Assert $page) use ($trainee, $confirmed): void {
            $page->component('Career/Snapshot/Setup');

            $rows = collect($page->toArray()['props']['roster']);
            $row = $rows->firstWhere('trainee', 'Zeta Roster');

            expect($row)->not->toBeNull()
                ->and($row['umamusumeId'])->toBe($trainee->id)
                ->and($row['traineeJa'])->toBe('ゼータ')
                // The unconfirmed form never reaches the payload.
                ->and($row['cards'])->toHaveCount(1)
                ->and($row['cards'][0]['selectionId'])->toBe($confirmed->id)
                // The local id is what the write validates against; the source id rides along.
                ->and($row['cards'][0]['sourceCardId'])->toBe($confirmed->card_id)
                // Title verbatim; key bracket-stripped for prefix matching.
                ->and($row['cards'][0]['title'])->toBe('[Roster Base]')
                ->and($row['cards'][0]['titleKey'])->toBe('Roster Base')
                ->and($row['cards'][0]['debut'])->toBeTrue();
        });
});

it('ships a cardless roster row so a trainee without a confirmed form stays pickable (N3.31)', function (): void {
    // Runs/Create's contract: cards: [] is the component's own "No costume card confirmed yet"
    // row, so a cardless trainee drops off the popup only if the payload omits her. She rides.
    $cardless = Umamusume::factory()->create(['name' => 'Alpha Cardless']);

    $this->get(route('career.snapshot.setup'))
        ->assertOk()
        ->assertInertia(function (Assert $page) use ($cardless): void {
            $page->component('Career/Snapshot/Setup');

            $rows = collect($page->toArray()['props']['roster']);
            $row = $rows->firstWhere('trainee', 'Alpha Cardless');

            expect($row)->not->toBeNull()
                ->and($row['umamusumeId'])->toBe($cardless->id)
                ->and($row['cards'])->toBe([]);
        });
});
