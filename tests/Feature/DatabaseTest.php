<?php

declare(strict_types=1);

use App\Models\RaceCatalogSlot;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * SCREEN-023: Database hub and its eight reference-data areas (plan §8 D17; completed by
 * `SCR-SYS-008` to `010`).
 *
 * Three areas are the ported catalog surfaces: Trainees, Support Cards and Skills render the same
 * shared list components the originals use. Two are DB- and config-derived: Races from RaceCatalogSlot,
 * Scenarios from config/scenarios.php. Three are transcribed from `docs/UMAMUSUME_REFERENCE.md` into
 * `config/reference.php`: Events, Shop Items and Sparks. All read-only; no redirects, no forms.
 */
it('renders the Database hub with links to the eight areas', function (): void {
    $this->get('/database')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Database/Index')
            ->has('areas', 8)
            // The three areas that were once "Not in this build" are live destinations now, in the
            // brief's own order (Trainees, Support Cards, Skills, Races, Events, Scenarios, Shop
            // Items, Sparks).
            ->where('areas.4.label', 'Events')
            ->where('areas.6.label', 'Shop Items')
            ->where('areas.7.label', 'Sparks'));
});

it('renders the Trainees area with the shared catalog list', function (): void {
    $this->get('/database/trainees')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Database/Trainees')
            ->has('umamusumes')
            ->has('statuses')
            ->has('allStatusesLabel'));
});

it('renders the Supports area with the shared support-card list', function (): void {
    $this->get('/database/supports')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Database/Supports')
            ->has('cards')
            ->has('rarityWords')
            ->has('availabilities'));
});

it('renders the Skills area with the shared skill list', function (): void {
    $this->get('/database/skills')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Database/Skills')
            ->has('skills')
            ->has('types')
            ->has('totalCount'));
});

it('renders the Races offline state when the catalog is empty', function (): void {
    $this->get('/database/races')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Database/Races')
            ->where('slots.data', [])
            ->where('slots.total', 0)
            ->where('totalCount', 0));
});

it('renders the Races table with N/A for unrecorded values when populated', function (): void {
    RaceCatalogSlot::factory()->debut()->create();
    RaceCatalogSlot::factory()->create();

    $this->get('/database/races')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Database/Races')
            ->where('slots.total', 2)
            ->where('totalCount', 2)
            ->has('slots.data.0.title')
            ->has('slots.data.0.year_label')
            ->has('slots.data.0.tier')
            ->has('slots.data.0.turn')
            ->has('slots.data.0.distance')
            ->has('slots.data.0.surface')
            ->has('slots.data.0.fans_needed')
            ->has('slots.data.0.is_mandatory')
            ->has('slots.data.0.is_maiden_gated')
            ->has('slots.data.0.is_special_race'));
});

it('renders the Scenarios matrix with the config values and PARTIALLY DOCUMENTED badge', function (): void {
    $this->get('/database/scenarios')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Database/Scenarios')
            ->has('baseCap')
            ->where('baseCap', 1200)
            ->has('verifiedAt')
            ->where('verifiedAt', '2026-09-27')
            ->has('scenarios', 4)
            ->where('scenarios.ura_finale.label', 'URA Finale')
            ->where('scenarios.unity_cup.label', 'Unity Cup')
            ->where('scenarios.trackblazer.label', 'Trackblazer')
            ->where('scenarios.our_grand_concert.label', 'Our Grand Concert')
            ->where('scenarios.our_grand_concert.partially_documented', true)
            ->where('scenarios.ura_finale.caps.0.stat', 'Speed')
            ->where('scenarios.ura_finale.caps.0.bonus', 200)
            ->where('scenarios.ura_finale.caps.0.cap', 1400)
            ->where('scenarios.ura_finale.panels.0.enabled', true)
            ->where('scenarios.ura_finale.panels.0.label', 'Race calendar')
            ->where('scenarios.ura_finale.widgets.0.label', 'Turn'));
});

it('renders the Events reference view with the ten recurring types', function (): void {
    $this->get('/database/events')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Database/Events')
            ->has('rows', 10)
            ->where('rows.0.name', 'Champions Meeting')
            ->where('rows.0.servers', ['JP', 'Global'])
            ->where('rows.0.state', 'confirmed')
            // The one row the guide marks unverified carries the `unknown` state, so the badge is
            // reachable from the payload rather than only in a comment.
            ->where('rows.5.name', 'Season pass')
            ->where('rows.5.state', 'unknown')
            ->has('recheck.url')
            ->has('recheck.text')
            ->where('source.doc', 'docs/UMAMUSUME_REFERENCE.md')
            ->where('totalCount', 10));
});

it('renders the Shop Items view and reuses the scenario Pro Shop block rather than retyping it', function (): void {
    $this->get('/database/shop-items')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Database/ShopItems')
            ->has('rows', 10)
            ->where('rows.0.name', 'Alarm Clock')
            ->where('rows.0.spend_site', 'Career')
            ->where('rows.0.kind', 'consumable')
            // The two currency items the section describes in prose are carried as `currency`.
            ->where('rows.8.kind', 'currency')
            ->where('rows.9.name', 'Goddess Statue')
            // The scenario block is read from config/scenarios.php, not restated in reference.php.
            ->where('scenarioShop.label', 'Trackblazer')
            ->where('scenarioShop.rotation_turns', 6)
            ->has('scenarioShop.items', 19)
            ->where('scenarioShop.items.0.name', 'Speed Notepad')
            ->where('scenarioShop.items.0.cost', 10)
            ->where('totalCount', 10));
});

it('renders the Sparks view with six categories and the star-roll odds table', function (): void {
    $this->get('/database/sparks')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Database/Sparks')
            ->has('categories', 6)
            ->where('categories.0.category', 'Stat')
            ->where('categories.0.global_name', 'Blue Sparks')
            ->where('categories.0.records', 5)
            ->where('categories.2.category', 'Unique skill')
            ->where('categories.2.records', 268)
            ->has('rollOdds', 3)
            ->where('rollOdds.0.band', 'Below 600')
            ->where('rollOdds.0.three_stars', '0%')
            ->has('rollOddsNote')
            ->where('totalCount', 6));
});

it('sums the Spark category counts to the guide total', function (): void {
    $this->get('/database/sparks')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('categories', function (Collection $categories): bool {
                // 5 + 10 + 268 + 452 + 37 + 34 = 806, the figure the reference guide §1.5.2 states.
                expect($categories->sum('records'))->toBe(806);

                return true;
            }));
});
