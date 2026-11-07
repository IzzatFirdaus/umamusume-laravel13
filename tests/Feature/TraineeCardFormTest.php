<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Models\CharacterCard;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Services\Career\SetupDraft;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * R3-06: the trainee's own costume form.
 *
 * Walk 3 could not say which form the run was on. The storage half already existed at `7ae1847`:
 * `training_runs.character_card_id` is documented as "the costume-card form this run started on",
 * `StoreTrainingRunRequest` validates it, `CockpitController` emits `card_title` and `CareerHeader.vue`
 * prints it. What was missing is the wizard: step 2 writes only `umamusume_id` to the draft, and Preflight
 * creates the run from that draft, so a career started through the wizard has no form and the header prints
 * nothing. These cases pin the chain the owner confirmed: a form is chosen on step 2, reaches the run, and
 * reads back as its own title.
 */
function formTrainee(string $name, array $titles): Umamusume
{
    $trainee = Umamusume::factory()->create([
        'name' => $name,
        'slug' => strtolower(str_replace(' ', '-', $name)),
        'external_ref' => 'test:char:'.strtolower(str_replace(' ', '-', $name)),
    ]);

    foreach ($titles as $index => $title) {
        CharacterCard::factory()->for($trainee)->create([
            'title' => $title,
            'rarity' => CardRarity::ThreeStar,
            'is_debut_form' => $index === 0,
        ]);
    }

    return $trainee;
}

it('sends every costume form the trainee holds so the step can offer them', function (): void {
    $trainee = formTrainee('Rice Shower', ['debut form', '[Rosy Dreams]']);

    $this->get(route('career.trainee'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('trainees.data.0.id', $trainee->id)
            ->where('trainees.data.0.form_count', 2)
            ->where('trainees.data.0.forms.1.title', '[Rosy Dreams]'));
});

it('carries the form chosen on step 2 onto the run the career starts on', function (): void {
    $trainee = formTrainee('Rice Shower', ['debut form', '[Rosy Dreams]']);
    $rosyDreams = CharacterCard::query()
        ->where('umamusume_id', $trainee->id)
        ->where('title', '[Rosy Dreams]')
        ->value('id');

    SetupDraft::write(['scenario' => 'unity_cup']);

    $this->put(route('career.trainee.store'), [
        'umamusume_id' => $trainee->id,
        'character_card_id' => $rosyDreams,
    ])->assertRedirect(route('career.trainee'));

    $this->put(route('career.preflight.store'))->assertRedirect(route('runs.cockpit', ['run' => 1]));

    $run = TrainingRun::query()->where('umamusume_id', $trainee->id)->sole();

    // The chosen form, not the debut one and not null: this is the value the header prints as a title.
    expect($run->character_card_id)->toBe($rosyDreams)
        ->and($run->characterCard?->title)->toBe('[Rosy Dreams]');
});

it('leaves the run without a form when the trainee holds one and nothing was chosen', function (): void {
    $trainee = formTrainee('Single Form', ['only form']);

    SetupDraft::write(['scenario' => 'unity_cup']);

    $this->put(route('career.trainee.store'), ['umamusume_id' => $trainee->id])
        ->assertRedirect(route('career.trainee'));
    $this->put(route('career.preflight.store'));

    expect(TrainingRun::query()->where('umamusume_id', $trainee->id)->sole()->character_card_id)->toBeNull();
});

it('refuses a costume form that belongs to another trainee and starts no run', function (): void {
    $trainee = formTrainee('Rice Shower', ['debut form', '[Rosy Dreams]']);
    $other = formTrainee('Tokai Teio', ['[Peacemaker! Glory]']);
    $teiosCard = CharacterCard::query()->where('umamusume_id', $other->id)->value('id');

    SetupDraft::write(['scenario' => 'unity_cup']);

    $this->put(route('career.trainee.store'), [
        'umamusume_id' => $trainee->id,
        'character_card_id' => $teiosCard,
    ])->assertSessionHasErrors('character_card_id');

    expect(TrainingRun::query()->where('umamusume_id', $trainee->id)->count())->toBe(0);
});

it('labels the form count each surface reports, so a scope cannot be read as a total', function (): void {
    // R7-02, owner ruling (b3). Three surfaces scope `character_cards` differently and all three are
    // right for their purpose: the picker offers only forms with a Global release date (a form the client
    // does not carry cannot be chosen), the profile and the catalog show every confirmed form (a read
    // screen hides nothing). Card 170 `Yummy Dreamy Fairy` has `global_release_date = NULL`, which is why
    // walk 7 read `2 forms` on one screen and three on another.
    //
    // Neither filter changes. What changes is that each count says what it counts.
    $trainee = formTrainee('Rice Shower', ['debut form', '[Rosy Dreams]']);

    // A form with no Global release date: confirmed, on the character, but not yet on the client.
    CharacterCard::factory()->for($trainee)->create([
        'title' => 'Yummy Dreamy Fairy',
        'rarity' => CardRarity::ThreeStar,
        'is_debut_form' => false,
        'global_release_date' => null,
    ]);

    $this->get(route('career.trainee'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // The picker still filters: two forms carry a release date, so two are offered.
            ->where('trainees.data.0.form_count', 2)
            // And it now says which two — the released ones, not "all of them".
            ->where('trainees.data.0.form_count_label', '2 released forms'));

    $this->get(route('career.trainee.profile', ['umamusume' => $trainee->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // The read screen lists all three and splits the count, so a Trainer can see the third form
            // exists without being offered a career on it.
            ->where('forms', fn ($forms) => count($forms) === 3)
            ->where('form_count_label', '3 forms (2 released, 1 undated)'));
});
