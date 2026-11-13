<?php

declare(strict_types=1);

use App\Models\Umamusume;
use App\Models\UmamusumeProfile;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Walk-8-era correction, `UMAMUSUME_REFERENCE.md` §3.3 (corrected 2026-10-15) and the corpus's own
 * conflict row 54: the `characters` document carries four voice-actor keys — `va_ja` (Japanese
 * script), `va_en` (romanisation), `va_ko`, `va_zh_tw` — one performer in up to four scripts, not
 * two casts. Where the stage name is already romanised the fields hold the identical string
 * (`Machico`, `Lynn`), which a separate dub cast could not produce.
 *
 * A profile block that labels `va_en` "English voice actor" misstates the source: the field beside
 * the kana is the romanisation of the same credit. This guard reads the shipped copy of the profile
 * block at `resources/js/pages/Catalog/Show.vue` and holds the corrected vocabulary in place, so a
 * later edit cannot quietly restore the dub-cast reading the corpus refuted.
 */
it('labels the latin-script credit as a romanisation, never an English voice actor', function (): void {
    $source = (string) file_get_contents(base_path('resources/js/pages/Catalog/Show.vue'));

    // The label row itself and the absence sentence must both use the romanisation vocabulary; the
    // refuted phrase survives nowhere in the block (including its comment forms).
    expect($source)
        ->toContain('Romanisation')
        ->not->toContain('English voice actor');
});

it('carries the romanisation reading of va_en through the profile payload', function (): void {
    $umamusume = Umamusume::factory()->create(['slug' => 'romanisation-guard']);
    UmamusumeProfile::factory()->create(['umamusume_id' => $umamusume->id]);

    $this->get(route('catalog.show', $umamusume->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Catalog/Show')
            ->where('trainee.profile.va_en', 'Azumi Waki')
            ->where('trainee.profile.va_ja', '和氣あず未'));
});
