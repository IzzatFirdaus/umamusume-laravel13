<?php

declare(strict_types=1);

use App\Enums\CardRarity;
use App\Enums\ReleaseStatus;
use App\Models\CharacterCard;
use App\Models\Skill;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Models\UmamusumeProfile;
use App\Services\Career\SetupDraft;
use Inertia\Testing\AssertableInertia as Assert;

// SCREEN-003 and SCREEN-004 (`ADR-0020` §1). Props and the session draft are asserted here; rendered copy,
// the keyboard path, the 44px sweep, 320px reflow and `lang="ja"` live in
// `tests/browser/career-trainee-select.spec.ts` (plan §2 convention 2). The wizard writes to the session
// draft and never to a run, so no case here needs a `TrainingRun` row.

/**
 * A trainee with the full aptitude set, so a fixture is never accidentally a null-aptitude fixture.
 *
 * @param  array<string, string>  $letters
 */
function rosterTrainee(string $name, array $letters): Umamusume
{
    return Umamusume::factory()->create([
        'name' => $name,
        'slug' => strtolower(str_replace(' ', '-', $name)),
        'aptitude_turf' => $letters['turf'] ?? null,
        'aptitude_dirt' => $letters['dirt'] ?? null,
        'aptitude_sprint' => $letters['sprint'] ?? null,
        'aptitude_mile' => $letters['mile'] ?? null,
        'aptitude_medium' => $letters['medium'] ?? null,
        'aptitude_long' => $letters['long'] ?? null,
        'aptitude_front_runner' => $letters['front_runner'] ?? null,
        'aptitude_pace_chaser' => $letters['pace_chaser'] ?? null,
        'aptitude_late_surger' => $letters['late_surger'] ?? null,
        'aptitude_end_closer' => $letters['end_closer'] ?? null,
    ]);
}

it('renders the roster paginated, with no stored choice and no draft write', function (): void {
    rosterTrainee('Special Week', ['turf' => 'A', 'long' => 'B', 'pace_chaser' => 'A']);
    rosterTrainee('Silence Suzuka', ['turf' => 'A', 'long' => 'A', 'pace_chaser' => 'C']);

    $this->get('/career/setup/trainee')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/TraineeSelect')
            ->has('trainees.data', 2)
            ->where('trainees.total', 2)
            ->where('trainees.data.0.name', 'Silence Suzuka')
            // Nothing chosen yet, so the screen never implies a choice nobody made.
            ->where('selected', null)
            ->where('scenarioLabel', null)
            ->where('scenarioPending', true)
            ->where('sortBy', 'name')
            ->where('direction', 'asc')
            ->has('facets', 3)
            ->has('sortKeys')
            ->has('uniqueSkills'));

    expect(SetupDraft::read()['umamusume_id'])->toBeNull();
});

it('sends the trainee card the payload the row prints, at the grain each fact lives at', function (): void {
    $trainee = rosterTrainee('Tokai Teio', [
        'turf' => 'A', 'dirt' => 'G', 'sprint' => 'F', 'mile' => 'E', 'medium' => 'A',
        'long' => 'B', 'front_runner' => 'G', 'pace_chaser' => 'A', 'late_surger' => 'C', 'end_closer' => 'B',
    ]);

    $card = CharacterCard::factory()->for($trainee)->debut()->create([
        'title' => '[Peacemaker! Glory]',
        'rarity' => CardRarity::ThreeStar,
    ]);

    $this->get('/career/setup/trainee')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('trainees.data.0.id', $trainee->id)
            ->where('trainees.data.0.slug', 'tokai-teio')
            ->where('trainees.data.0.name_ja', null)
            ->where('trainees.data.0.release_status_label', 'Released (Global)')
            ->where('trainees.data.0.form_count', 1)
            // Rarity is a `character_cards` column and there is no trainee-level rarity to aggregate, so
            // the row carries it per costume form and no `max_rarity` key at all. `label()` reads "Three
            // stars" off `lang/en/uma.php` `card_rarity`, the same value the catalog tests pin.
            ->where('trainees.data.0.forms.0.rarity_label', 'Three stars')
            ->where('trainees.data.0.forms.0.rarity_stars', '★★★')
            ->where('trainees.data.0.forms.0.rarity_word', 'SSR')
            ->where('trainees.data.0.forms.0.is_debut_form', true)
            // The ten letters are the trainee's own and appear once, not per form.
            ->where('trainees.data.0.aptitudes.long', 'B')
            ->where('trainees.data.0.aptitudes.end_closer', 'B')
            ->has('trainees.data.0.aptitudes', 10)
            // No `uma:fetch-art` pass has run on a test database, so the slot's absence guard is the
            // expected answer rather than a broken frame.
            ->where('trainees.data.0.artworkURL', null));
});

it('states a trainee with no published aptitude as an absence rather than an empty grid', function (): void {
    // The parser writes all ten columns or none, so a null `aptitude_turf` means the whole set is absent
    // and the row carries null rather than ten nulls (D-220).
    Umamusume::factory()->create(['name' => 'Unmeasured Trainee']);

    $this->get('/career/setup/trainee')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('trainees.data.0.aptitudes', null));
});

it('narrows the roster with each filter it claims to offer', function (): void {
    rosterTrainee('Turf Specialist', ['turf' => 'A', 'long' => 'G', 'pace_chaser' => 'G']);
    rosterTrainee('Dirt Specialist', ['turf' => 'G', 'long' => 'G', 'pace_chaser' => 'A']);
    rosterTrainee('Stayer', ['turf' => 'G', 'long' => 'A', 'pace_chaser' => 'G']);

    $this->get('/career/setup/trainee?search=turf')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('trainees.data', 1)
            ->where('trainees.data.0.name', 'Turf Specialist')
            ->where('filters.search', 'turf'));

    // A facet matches when any column in its group holds the letter, so Surface reads the two surface
    // columns and never the distance ones.
    $this->get('/career/setup/trainee?surface=A')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('trainees.data', 1)->where('trainees.data.0.name', 'Turf Specialist'));

    $this->get('/career/setup/trainee?distance=A')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('trainees.data', 1)->where('trainees.data.0.name', 'Stayer'));

    $this->get('/career/setup/trainee?style=A')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('trainees.data', 1)->where('trainees.data.0.name', 'Dirt Specialist'));

    // Two facets compose: an A on some surface *and* an A on some running style has none here, because
    // the A-on-turf trainee is a G-chaser.
    $this->get('/career/setup/trainee?surface=A&style=A')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('trainees.data', 0));
});

it('narrows the roster to trainees whose costume forms list a unique skill', function (): void {
    $carrier = rosterTrainee('Skill Carrier', ['turf' => 'A']);
    $other = rosterTrainee('Skillless', ['turf' => 'A']);

    $unique = Skill::factory()->create([
        'name' => 'Evasive Triple Cut',
        'export_id' => 100201,
        'release_status' => ReleaseStatus::GlobalReleased,
        'name_is_client' => true,
    ]);

    CharacterCard::factory()->for($carrier)->create(['skills_unique' => [$unique->export_id]]);
    CharacterCard::factory()->for($other)->create(['skills_unique' => [999999]]);

    $this->get('/career/setup/trainee?skill='.$unique->export_id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('trainees.data', 1)
            ->where('trainees.data.0.name', 'Skill Carrier'));

    // The option list is the catalog's own unique skills that survive `availableOnGlobal()`, offered so a
    // Trainer is never asked to type an export id.
    $this->get('/career/setup/trainee')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('uniqueSkills', 1)
            ->where('uniqueSkills.0.export_id', 100201)
            ->where('uniqueSkills.0.name', 'Evasive Triple Cut'));

    // A JP-only or non-client-named skill is not a Global Trainer's option, so it never reaches the facet
    // list even when a card references it (`ADR-0011` §2).
    $jpOnly = Skill::factory()->create([
        'name' => 'Withdrawn Skill',
        'export_id' => 100202,
        'release_status' => ReleaseStatus::JapanOnly,
        'name_is_client' => true,
    ]);

    CharacterCard::factory()->for($carrier)->create(['skills_unique' => [$jpOnly->export_id]]);

    $this->get('/career/setup/trainee')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('uniqueSkills', 1));
});

it('refuses an unknown filter value and degrades an unknown sort rather than refusing it', function (): void {
    rosterTrainee('Special Week', ['turf' => 'A']);

    // A refused filter is the honest answer: a roster that ignored `surface=Z` and printed one trainee
    // would read as "this is who matches Z".
    $this->get('/career/setup/trainee?surface=Z')
        ->assertRedirect(route('career.trainee'))
        ->assertSessionHasErrors('surface');

    $this->get('/career/setup/trainee?distance=9')->assertSessionHasErrors('distance');
    $this->get('/career/setup/trainee?skill=424242')->assertSessionHasErrors('skill');

    // A sort key is a presentation choice and usually arrives from a pasted URL, so it falls back the way
    // `PageSize::clamp` does. The allowlist is what makes the fallback safe: an off-list key never reaches
    // `orderBy()`.
    $this->get('/career/setup/trainee?sortBy=password%3Bid')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('sortBy', 'name')
            ->has('trainees.data', 1));
});

it('orders an aptitude sort by the client scale, not by the alphabet', function (): void {
    // ASCII order would put A first and leave S last, which would present a G trainee as the best long
    // choice in the list. The rank expression is the fix, and this is the case that fails if it is dropped.
    rosterTrainee('Alphabetically First', ['long' => 'A', 'turf' => 'A']);
    rosterTrainee('Best Letter', ['long' => 'S', 'turf' => 'A']);
    rosterTrainee('Worst Letter', ['long' => 'G', 'turf' => 'A']);

    $this->get('/career/setup/trainee?sortBy=long&direction=asc')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('sortBy', 'long')
            ->where('direction', 'asc')
            ->where('trainees.data.0.name', 'Best Letter')
            ->where('trainees.data.1.name', 'Alphabetically First')
            ->where('trainees.data.2.name', 'Worst Letter'));

    $this->get('/career/setup/trainee?sortBy=long&direction=desc')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('trainees.data.0.name', 'Worst Letter'));
});

it('caps the page size through the shared rule rather than trusting the query', function (): void {
    for ($i = 0; $i < 3; $i++) {
        rosterTrainee('Roster Trainee '.str_pad((string) $i, 2, '0', STR_PAD_LEFT), ['turf' => 'A']);
    }

    $this->get('/career/setup/trainee?pageSize=2')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('trainees.data', 2)
            ->where('trainees.total', 3));

    // A nonsense size is a bigger or smaller page, not a refused request (`PageSize`, ADR-0018).
    $this->get('/career/setup/trainee?pageSize=5000')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('trainees.per_page', 100));
});

it('stores the chosen trainee in the draft and reads her back after navigation', function (): void {
    $trainee = rosterTrainee('Tokai Teio', ['turf' => 'A']);

    $this->put('/career/setup/trainee', ['umamusume_id' => $trainee->id])
        ->assertRedirect(route('career.trainee'));

    expect(SetupDraft::read()['umamusume_id'])->toBe($trainee->id);

    $this->get('/career/setup/trainee')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('selected', $trainee->id));

    // The draft carries both steps independently: leaving the wizard for the Dashboard and returning must
    // not lose the scenario or the trainee (WCAG 3.3.7 redundancy).
    $this->put('/career/setup/scenario', ['scenario' => 'trackblazer'])->assertRedirect();
    $this->get('/')->assertOk();

    $this->get('/career/setup/trainee')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('selected', $trainee->id)
            ->where('scenarioLabel', 'Trackblazer')
            ->where('scenarioPending', false));

    expect(SetupDraft::read())->toBe(['scenario' => 'trackblazer', 'umamusume_id' => $trainee->id]);
});

it('creates no run when a trainee is chosen, and refuses one who is not in the catalog', function (): void {
    $trainee = rosterTrainee('Special Week', ['turf' => 'A']);

    $this->put('/career/setup/trainee', ['umamusume_id' => $trainee->id])->assertRedirect();

    // The whole reason `SetupDraft` is a session: a run made here would surface as a phantom career on the
    // Dashboard and a phantom builder target in the Legacy Lab.
    expect(TrainingRun::count())->toBe(0);

    $this->put('/career/setup/trainee', ['umamusume_id' => 999999])->assertSessionHasErrors('umamusume_id');
    $this->put('/career/setup/trainee', [])->assertSessionHasErrors('umamusume_id');

    expect(SetupDraft::read()['umamusume_id'])->toBe($trainee->id);
});

it('renders the profile with basic facts, aptitude once and the four skill lists per form', function (): void {
    $trainee = rosterTrainee('Tokai Teio', [
        'turf' => 'A', 'dirt' => 'G', 'sprint' => 'F', 'mile' => 'E', 'medium' => 'A',
        'long' => 'B', 'front_runner' => 'G', 'pace_chaser' => 'A', 'late_surger' => 'C', 'end_closer' => 'B',
    ]);

    UmamusumeProfile::factory()->for($trainee)->create();

    $unique = Skill::factory()->create([
        'name' => 'Peacemaker',
        'export_id' => 100301,
        'sp_cost' => null,
        'is_unique' => true,
        'release_status' => ReleaseStatus::GlobalReleased,
        'name_is_client' => true,
    ]);

    $innate = Skill::factory()->create([
        'name' => 'Ahead Spirit',
        'export_id' => 100302,
        'release_status' => ReleaseStatus::GlobalReleased,
        'name_is_client' => true,
    ]);

    CharacterCard::factory()->for($trainee)->debut()->create([
        'title' => '[Peacemaker! Glory]',
        'skills_unique' => [$unique->export_id],
        'skills_innate' => [$innate->export_id],
        'skills_awakening' => [],
        'skills_event' => [],
    ]);

    $this->get('/career/setup/trainee/'.$trainee->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Career/TraineeProfile')
            ->where('trainee.name', 'Tokai Teio')
            ->where('trainee.aptitudes.long', 'B')
            ->has('trainee.aptitudes', 10)
            ->has('trainee.profile')
            ->where('trainee.profile.height', 158)
            ->has('trainee.profile.birthday.iso')
            ->where('forms.0.rarity_word', 'SSR')
            ->where('forms.0.skillLists.0.label', 'Unique skill')
            ->where('forms.0.skillLists.0.skills.0.name', 'Peacemaker')
            // `skills.sp_cost` is null on 1,004 of the 1,910 catalog rows, so the row carries null and
            // `SkillRow.vue` prints `N/A` with its reason rather than a fabricated cost.
            ->where('forms.0.skillLists.0.skills.0.sp_cost', null)
            ->where('forms.0.skillLists.0.unresolved', 0)
            ->where('forms.0.skillLists.1.skills.0.name', 'Ahead Spirit')
            ->where('selected', null));
});

it('counts a skill id that resolves to no Global skill instead of dropping it silently', function (): void {
    $trainee = rosterTrainee('Special Week', ['turf' => 'A']);
    $card = CharacterCard::factory()->for($trainee)->debut()->create([
        // Two awakening ids this catalog can resolve, one it cannot: 6 of its 237 awakening ids are in
        // exactly this state, and a list that quietly printed two names would read as the whole list.
        'skills_awakening' => [100401, 100402, 999999],
    ]);

    foreach ([100401, 100402] as $exportId) {
        Skill::factory()->create([
            'export_id' => $exportId,
            'release_status' => ReleaseStatus::GlobalReleased,
            'name_is_client' => true,
        ]);
    }

    $this->get('/career/setup/trainee/'.$trainee->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('forms.0.id', $card->id)
            ->has('forms.0.skillLists.2.skills', 2)
            // The gap is stated as a number rather than dropped: an uncounted short list reads as "this
            // form has no more awakening skills".
            ->where('forms.0.skillLists.2.unresolved', 1));
});

it('names every profile absence the brief asks for and omits the sections the schema cannot fill', function (): void {
    $trainee = rosterTrainee('Silence Suzuka', ['turf' => 'A']);

    $this->get('/career/setup/trainee/'.$trainee->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            // Each named absence carries its reason as a title, so the screen states the gap in words.
            ->where('absences.version.value', null)
            ->has('absences.version.title')
            ->where('absences.stat_distribution.value', null)
            ->has('absences.stat_distribution.title')
            ->where('absences.inheritance.value', null)
            ->has('absences.inheritance.title')
            ->where('absences.support_types.value', null)
            ->has('absences.support_types.title')
            // Career goals is omitted entirely: `trainee_goals` has never been migrated, and the filing is
            // reserved as KI-34. A key here would be a heading waiting for data that does not exist.
            ->missing('goals')
            ->missing('trainee.goals')
            ->missing('trainee.growth_rates')
            // Hint skills live on `support_cards`, not on a trainee's form, and evolved pairs are refused by
            // SCR-CAT-002's own ruling, so neither reaches the payload either.
            ->missing('forms.0.skills_hint')
            ->missing('forms.0.skills_evo'));
});

it('keeps every scenario name out of the step-2 components', function (): void {
    // `config/scenarios.php` is the only source of scenario names in the layout path (AGENTS.md §7, D-240,
    // gate G-33), and a name written into a page is a defect. The props are not the risk here: the label
    // `TraineeSelect.vue` prints arrives from `SetupDraft`, which reads the matrix. The files that could
    // carry a hardcoded one are the three SFCs this slice adds, so those are what is read.
    $sources = [
        'resources/js/pages/Career/TraineeSelect.vue',
        'resources/js/pages/Career/TraineeProfile.vue',
        'resources/js/components/AptitudeBadge.vue',
    ];

    $names = array_map(
        static fn (array $definition): string => (string) $definition['label'],
        array_values((array) config('scenarios.scenarios')),
    );

    foreach ($sources as $path) {
        $source = (string) file_get_contents(base_path($path));

        foreach ($names as $name) {
            expect($source)->not->toContain($name, "{$path} names the scenario {$name} instead of reading a prop");
        }

        foreach (array_keys((array) config('scenarios.scenarios')) as $key) {
            expect($source)->not->toContain((string) $key, "{$path} hardcodes the scenario key {$key}");
        }
    }
});
