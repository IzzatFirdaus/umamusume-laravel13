<?php

declare(strict_types=1);

use App\Models\SupportCard;
use App\Models\SupportEffect;
use App\Services\SupportCardEffects;
use Inertia\Testing\AssertableInertia as Assert;

uses()->group('support-cards');

beforeEach(function (): void {
    // Create the effect dictionary (35 effects)
    for ($i = 1; $i <= 35; $i++) {
        SupportEffect::factory()->create([
            'effect_id' => $i,
            'name_en' => "Effect {$i}",
            'calc' => 'add',
            'symbol' => 'percent',
        ]);
    }
});

it('includes stages in the index payload for each card', function (): void {
    $card = SupportCard::factory()->create([
        'effects' => [[1, 10, 15, -1, -1, 20, -1, -1, 25, -1, -1, 30]],
    ]);

    $this->get('/support-cards')
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('SupportCards/Index')
                ->where('cards.data.0.stages', function ($stages) {
                    // Should have stages for effect 1
                    $stage = collect($stages)->firstWhere('effect_id', 1);
                    return $stage !== null
                        && count($stage['anchors']) > 0
                        && isset($stage['cap']);
                })
        );
});

it('stages match the detail page structure', function (): void {
    $card = SupportCard::factory()->create([
        'effects' => [[1, 10, 15, -1, -1, 20, -1, -1, 25, -1, -1, 30]],
    ]);

    // Get stages from index
    $this->get('/support-cards')
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('SupportCards/Index')
                ->where('cards.data.0.stages', function ($stages) {
                    $stage = collect($stages)->firstWhere('effect_id', 1);
                    return $stage !== null
                        && isset($stage['anchors'])
                        && isset($stage['cap']);
                })
        );

    // Get stages from detail page
    $this->get(route('support-cards.show', $card))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('SupportCards/Show')
                ->where('effectStages', function ($stages) {
                    $stage = collect($stages)->firstWhere('effect_id', 1);
                    return $stage !== null
                        && isset($stage['anchors'])
                        && isset($stage['cap']);
                })
        );
});

it('stages in index and detail have same anchor structure', function (): void {
    $card = SupportCard::factory()->create([
        'effects' => [[1, 10, 15, -1, -1, 20, -1, -1, 25, -1, -1, 30]],
    ]);

    $indexResponse = $this->get('/support-cards');
    $detailResponse = $this->get(route('support-cards.show', $card));

    $indexStages = $indexResponse->getOriginalContent()->getData()['page']['props']['cards']['data'][0]['stages'] ?? [];
    $detailStages = $detailResponse->getOriginalContent()->getData()['page']['props']['effectStages'] ?? [];

    $indexStage = collect($indexStages)->firstWhere('effect_id', 1);
    $detailStage = collect($detailStages)->firstWhere('effect_id', 1);

    expect($indexStage)->not->toBeNull();
    expect($detailStage)->not->toBeNull();

    // Same number of anchors
    expect(count($indexStage['anchors']))->toBe(count($detailStage['anchors']));

    // Same cap
    expect($indexStage['cap']['level'])->toBe($detailStage['cap']['level']);
    expect($indexStage['cap']['display'])->toBe($detailStage['cap']['display']);

    // Same anchor values
    for ($i = 0; $i < count($indexStage['anchors']); $i++) {
        expect($indexStage['anchors'][$i]['level'])->toBe($detailStage['anchors'][$i]['level']);
        expect($indexStage['anchors'][$i]['display'])->toBe($detailStage['anchors'][$i]['display']);
    }
});

it('cards without effects have empty stages array', function (): void {
    $card = SupportCard::factory()->create(['effects' => []]);

    $this->get('/support-cards')
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('SupportCards/Index')
                ->where('cards.data.0.stages', [])
        );
});

it('cards with -1 anchors have correct N/A handling', function (): void {
    // Card with all -1 anchors for an effect
    $card = SupportCard::factory()->create([
        'effects' => [[2, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1]],
    ]);

    $this->get('/support-cards')
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('SupportCards/Index')
                ->where('cards.data.0.stages', [])
        );
});

it('stages cap is the last stated anchor', function (): void {
    // Effect with anchors at levels 1, 10, 20, 30, 40, 50
    $card = SupportCard::factory()->create([
        'effects' => [[3, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55]],
    ]);

    $this->get('/support-cards')
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('SupportCards/Index')
                ->where('cards.data.0.stages', function ($stages) {
                    $stage = collect($stages)->firstWhere('effect_id', 3);
                    return $stage !== null
                        && $stage['cap']['level'] === 50
                        && $stage['cap']['display'] === '55%';
                })
        );
});