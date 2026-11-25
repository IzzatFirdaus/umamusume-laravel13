<?php

declare(strict_types=1);

use App\Models\CharacterCard;
use App\Models\RaceCatalogSlot;
use App\Models\Skill;
use App\Models\SupportCard;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use Illuminate\Support\Facades\DB;
use Database\Seeders\RosyDreamsRiceShowerSeeder;

/**
 * §1.9 of `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` records 17 named team members plus the
 * trainee, and ADR-0039 makes that recorded roster the Cockpit's authoritative read-out.
 *
 * The seeder wrote none of them. Every `db:seed` therefore deleted the run (cascading the roster
 * with it) and rebuilt a team whose members the report had just named, so the team screen fell
 * back to "No team roster has been recorded" and the only copy of those 18 rows was the live
 * database. These cases pin the reproduction: a fresh seed writes exactly the §1.9 roster, and a
 * second seed leaves it at 18 rather than doubling it or emptying it.
 */
const ROSY_SOURCE = 'docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md (Run Rosy Dreams Rice Shower)';

beforeEach(function (): void {
    // The seeder binds concrete catalog rows rather than resolving by name, so the fixture
    // materialises exactly the ones the Rosy run names: her trainee, the [Rosy Dreams] card, the
    // two Legacy parents, the eleven owned skills and the five scheduled race slots. Anything not
    // created here is an id the seeder would fail to find.
    foreach ([30, 4, 8] as $umamusumeId) {
        if (Umamusume::find($umamusumeId) === null) {
            Umamusume::factory()->create(['id' => $umamusumeId]);
        }
    }

    if (CharacterCard::find(31) === null) {
        CharacterCard::factory()->create(['id' => 31, 'card_id' => 103001, 'umamusume_id' => 30]);
    }

    foreach ([47, 31, 142, 147, 174, 217, 219, 223, 311, 315, 148] as $skillId) {
        if (Skill::find($skillId) === null) {
            Skill::factory()->create(['id' => $skillId]);
        }
    }

    $sharedDeck = [
        ['support_id' => 30003, 'card_id' => 250],
        ['support_id' => 30018, 'card_id' => 265],
        ['support_id' => 30020, 'card_id' => 267],
        ['support_id' => 30016, 'card_id' => 263],
        ['support_id' => 30030, 'card_id' => 277],
        ['support_id' => 30019, 'card_id' => 266],
    ];

    foreach ($sharedDeck as $card) {
        if (SupportCard::find($card['card_id']) !== null) {
            continue;
        }

        $created = SupportCard::factory()->create(['support_id' => $card['support_id']]);

        // `support_cards.id` is not fillable, so the row is re-keyed through the query builder the
        // way the catalog import resolves the client's own card id.
        DB::table('support_cards')->where('id', $created->id)->update(['id' => $card['card_id']]);
    }

    // The five race slots the Rosy run has entered or completed, Senior (year-3) instances of the
    // races §1.6 names. Same re-keying: `race_catalog_slots.id` is not fillable either.
    $slots = [
        264 => 'Nikkei Sho',
        280 => 'Tenno Sho (Spring)',
        310 => 'Takarazuka Kinen',
        355 => 'Kyoto Daishoten',
        400 => 'Arima Kinen',
    ];

    foreach ($slots as $slotId => $title) {
        if (RaceCatalogSlot::find($slotId) !== null) {
            continue;
        }

        $created = RaceCatalogSlot::factory()->create([
            'year' => RaceCatalogSlot::YEAR_SENIOR,
            'title' => $title,
        ]);

        DB::table('race_catalog_slots')->where('id', $created->id)->update(['id' => $slotId]);
    }
});

it('seeds the eighteen members the report names', function (): void {
    $this->seed(RosyDreamsRiceShowerSeeder::class);

    $run = TrainingRun::where('import_source', ROSY_SOURCE)->sole();

    expect($run->rosterMembers()->count())->toBe(18)
        ->and($run->rosterMembers()->where('is_trainee', true)->sole()->name)->toBe('Rice Shower')
        ->and($run->rosterMembers()->where('is_trainee', false)->count())->toBe(17);
});

it('places the five ACEs in the columns §1.8 reads off the Team tab', function (): void {
    $this->seed(RosyDreamsRiceShowerSeeder::class);

    $run = TrainingRun::where('import_source', ROSY_SOURCE)->sole();

    $aces = $run->rosterMembers()
        ->where('is_ace', true)
        ->get()
        ->mapWithKeys(static fn ($member): array => [
            $member->distance_assignment?->value => $member->name,
        ])
        ->all();

    // `toEqual` rather than `toBe`: the map is keyed by distance, and the seeder writes members in
    // §1.9's reading order, so the key order is the roster order and not the column order. What
    // this case pins is who holds which column, which is order-independent.
    expect($aces)->toEqual([
        'Sprint' => 'Seeking the Pearl',
        'Mile' => 'Nishino Flower',
        'Medium' => 'Tokai Teio',
        'Long' => 'Rice Shower',
        'Dirt' => 'Haru Urara',
    ]);
});

it('keeps a member off a column the report never assigns, rather than defaulting one', function (): void {
    $this->seed(RosyDreamsRiceShowerSeeder::class);

    $run = TrainingRun::where('import_source', ROSY_SOURCE)->sole();

    // §1.8 names five ACEs and no other placements. The remaining thirteen members are on the
    // roster without a column, which is null and not an invented assignment (D-220).
    expect($run->rosterMembers()->whereNull('distance_assignment')->count())->toBe(13);
});

it('seeds the caps §1.9 prints beside each stat, and no cap the report omits', function (): void {
    $this->seed(RosyDreamsRiceShowerSeeder::class);

    $run = TrainingRun::where('import_source', ROSY_SOURCE)->sole();

    $urara = $run->rosterMembers()->where('name', 'Haru Urara')->sole();

    expect($urara->guts)->toBe(1068)
        ->and($urara->guts_cap)->toBe(1140)
        ->and($urara->speed_cap)->toBe(900)
        ->and($urara->wit_cap)->toBe(850);

    // The trainee's five caps come from §1.2's stat panels. The Wit ceiling is 1800 — the Wit
    // panel's floor is different from the other four, but the cap itself is a recorded reading.
    $trainee = $run->rosterMembers()->where('is_trainee', true)->sole();

    expect($trainee->speed_cap)->toBe(1325)
        ->and($trainee->stamina_cap)->toBe(1322)
        ->and($trainee->power_cap)->toBe(1368)
        ->and($trainee->guts_cap)->toBe(1308)
        ->and($trainee->wit_cap)->toBe(1800);
});

it('leaves the roster at eighteen on a second seed', function (): void {
    $this->seed(RosyDreamsRiceShowerSeeder::class);

    $run = TrainingRun::where('import_source', ROSY_SOURCE)->sole();
    $before = $run->rosterMembers()->count();

    $this->seed(RosyDreamsRiceShowerSeeder::class);

    $reseeded = TrainingRun::where('import_source', ROSY_SOURCE)->sole();

    expect($before)->toBe(18)
        ->and($reseeded->rosterMembers()->count())->toBe(18)
        ->and($reseeded->rosterMembers()->where('position', 1)->sole()->name)->toBe('Haru Urara');
});
