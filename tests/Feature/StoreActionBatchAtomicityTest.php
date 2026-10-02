<?php

declare(strict_types=1);

use App\Actions\StoreCharacterCards;
use App\Actions\StoreRaceCatalogSlots;
use App\Models\CharacterCard;
use App\Models\RaceCatalogSlot;
use App\Models\Umamusume;
use Illuminate\Database\Eloquent\Model;

/*
 * F-1: a store action that writes row by row without a transaction commits the rows
 * that succeeded before the throw and leaves them there with no marker that the batch
 * half landed. Seven of the nine store actions wrap the loop in DB::transaction, see
 * StoreSkills.php:51 and StoreSupportCards.php:57. These two do not.
 *
 * ARCHITECTURE.md:266 and ARCHITECTURE-ESSENTIALS.md:47 state one transaction per batch,
 * so this is the convention being missed rather than a preference.
 *
 * The throw is forced with a model `creating` listener rather than a malformed record, so
 * the rows themselves are valid and only the batch shape is under test. The listener counts
 * inserts and fails on the second one, which is the mid-batch case.
 */

/**
 * @param  class-string<Model>  $model
 */
function failOnSecondInsert(string $model): void
{
    $seen = 0;

    $model::creating(function () use (&$seen): void {
        $seen++;

        if ($seen === 2) {
            throw new RuntimeException('forced mid-batch failure');
        }
    });
}

it('rolls the whole character-card batch back when a row in the middle fails', function (): void {
    $trainee = Umamusume::factory()->create(['external_ref' => 'char:1001', 'is_manual' => false]);

    $records = [
        [
            'card_id' => 501, 'char_external_ref' => 'char:1001', 'title' => 'First Card',
            'rarity' => 3,
            'global_release_date' => '2026-01-05', 'is_debut_form' => true,
            'skills_innate' => [], 'skills_unique' => [],
            'skills_awakening' => [], 'skills_event' => [], 'skills_evo' => [],
        ],
        [
            'card_id' => 502, 'char_external_ref' => 'char:1001', 'title' => 'Second Card',
            'rarity' => 3,
            'global_release_date' => '2026-01-05', 'is_debut_form' => true,
            'skills_innate' => [], 'skills_unique' => [],
            'skills_awakening' => [], 'skills_event' => [], 'skills_evo' => [],
        ],
        [
            'card_id' => 503, 'char_external_ref' => 'char:1001', 'title' => 'Third Card',
            'rarity' => 5,
            'global_release_date' => '2026-02-16', 'is_debut_form' => false,
            'skills_innate' => [], 'skills_unique' => [],
            'skills_awakening' => [], 'skills_event' => [], 'skills_evo' => [],
        ],
    ];

    failOnSecondInsert(CharacterCard::class);

    try {
        app(StoreCharacterCards::class)->handle($records, 'https://example.test/cards.json', null, null);
        $this->fail('the forced mid-batch throw did not propagate');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('forced mid-batch failure');
    }

    // F-1: the first row is durably committed today, so this count is 1 and not 0.
    expect(CharacterCard::count())->toBe(0)
        ->and($trainee->cards()->count())->toBe(0);
});

it('rolls the whole race-catalogue batch back when a row in the middle fails', function (): void {
    $records = [
        ['scenario_key' => null, 'year' => 2, 'month' => 4, 'half' => 'Early', 'slot_label' => 'Early April', 'grade_code' => 100, 'title' => 'Oka Sho'],
        ['scenario_key' => null, 'year' => 2, 'month' => 5, 'half' => 'Early', 'slot_label' => 'Early May', 'grade_code' => 100, 'title' => 'NHK Mile Cup'],
        ['scenario_key' => null, 'year' => 3, 'month' => 4, 'half' => 'Early', 'slot_label' => 'Early April', 'grade_code' => 100, 'title' => 'Oka Sho'],
    ];

    failOnSecondInsert(RaceCatalogSlot::class);

    try {
        app(StoreRaceCatalogSlots::class)->handle($records, 'https://example.test/race_instances.json', null, 'Asia/Tokyo');
        $this->fail('the forced mid-batch throw did not propagate');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('forced mid-batch failure');
    }

    // F-1: the first slot survives today, so this count is 1 and not 0.
    expect(RaceCatalogSlot::count())->toBe(0);
});
