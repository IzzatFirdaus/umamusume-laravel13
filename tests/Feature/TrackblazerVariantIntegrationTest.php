<?php

declare(strict_types=1);

use App\Enums\RunStatus;
use App\Models\TrainingRun;
use App\Models\Umamusume;
use App\Services\Career\SetupDraft;
use App\Services\Career\TrackblazerVariant;
use Database\Seeders\UmamusumeRosterSeeder;
use Inertia\Testing\AssertableInertia as Assert;

/*
 * Ruling F1 at the application boundary: the derivation reaches the Cockpit and Preflight from ONE
 * owner, the roster distribution the ruling records reproduces, and no other scenario receives a
 * Trackblazer variant.
 *
 * The measured distribution is `docs/research-scratch/PLANS-AND-BRIEFS.md` §"F — Trackblazer variant
 * point tracks from aptitudes": 9 dirt_leaning / 3 limited_turf_range / 8 ambiguous / 113 standard
 * over the 133 trainees that carry aptitudes. The seeded roster is the committed
 * `gametora-characters` body, so this asserts against real catalogue data rather than fixtures built
 * to agree with the rule.
 */

/**
 * A run whose trainee carries the given aptitude letters.
 *
 * @param  array<string, string>  $aptitudes  column suffix => letter, e.g. `['turf' => 'A']`
 */
function tbVariantRun(array $aptitudes, string $scenario = 'trackblazer'): TrainingRun
{
    $letters = [];

    foreach ($aptitudes as $axis => $letter) {
        $letters['aptitude_'.$axis] = $letter;
    }

    return TrainingRun::factory()->create([
        'scenario' => $scenario,
        'status' => RunStatus::Active,
        'umamusume_id' => Umamusume::factory()->create($letters)->id,
    ]);
}

it('reproduces the recorded 133-trainee distribution: 9 / 3 / 8 / 113', function (): void {
    (new UmamusumeRosterSeeder)->run();

    $classified = Umamusume::query()->whereNotNull('aptitude_turf')->get();

    // The roster figure the ruling measured. A trainee with no published aptitude is excluded, which
    // is the same population the ruling counted (133 of 135 roster rows; the two extra are rows the
    // seeder files as pending candidates).
    expect($classified)->toHaveCount(133);

    $counts = ['dirt_leaning' => 0, 'limited_turf_range' => 0, 'ambiguous' => 0, 'standard' => 0];

    foreach ($classified as $trainee) {
        $counts[TrackblazerVariant::forTrainee($trainee)['classification']]++;
    }

    expect($counts)->toBe([
        'dirt_leaning' => 9,
        'limited_turf_range' => 3,
        'ambiguous' => 8,
        'standard' => 113,
    ]);
});

it('puts the three named examples on their own tracks', function (): void {
    // The two sources name Haru Urara and Curren Chan, and the ruling names Agnes Digital as an exact
    // A/A tie. These three are the reason the rule is believed to be the right one at all.
    (new UmamusumeRosterSeeder)->run();

    expect(TrackblazerVariant::forTrainee(Umamusume::query()->where('name', 'Haru Urara')->first())['classification'])
        ->toBe(TrackblazerVariant::DIRT_LEANING)
        ->and(TrackblazerVariant::forTrainee(Umamusume::query()->where('name', 'Curren Chan')->first())['classification'])
        ->toBe(TrackblazerVariant::LIMITED_TURF_RANGE)
        ->and(TrackblazerVariant::forTrainee(Umamusume::query()->where('name', 'Agnes Digital')->first())['classification'])
        ->toBe(TrackblazerVariant::AMBIGUOUS);
});

it('renders the variant track figures for a dirt-leaning trainee', function (): void {
    // 30 / 200 / 300 is the dirt track the ruling derives, and it is the whole point of the slice: the
    // page printed 60 / 300 / 300 for this trainee before.
    $run = tbVariantRun(['turf' => 'G', 'dirt' => 'A', 'sprint' => 'A', 'mile' => 'B', 'medium' => 'G', 'long' => 'G']);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade.ladder_source', TrackblazerVariant::DIRT_LEANING)
            ->where('scenario.grade.variant.classification', TrackblazerVariant::DIRT_LEANING)
            ->where('scenario.grade.objectives.1.required', 30)
            ->where('scenario.grade.objectives.2.required', 200)
            ->where('scenario.grade.objectives.3.required', 300));
});

it('renders the limited-turf-range track figures for a sprint-only turf trainee', function (): void {
    // 60 / 200 / 300: only the Classic objective drops.
    $run = tbVariantRun(['turf' => 'A', 'dirt' => 'F', 'sprint' => 'A', 'mile' => 'D', 'medium' => 'G', 'long' => 'G']);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade.ladder_source', TrackblazerVariant::LIMITED_TURF_RANGE)
            ->where('scenario.grade.objectives.1.required', 60)
            ->where('scenario.grade.objectives.2.required', 200)
            ->where('scenario.grade.objectives.3.required', 300));
});

it('renders the standard track for an ambiguous trainee and says why', function (): void {
    // The classification is `ambiguous`, so `standard` is what prints — but the payload must not let
    // that read as a classification. The variant block carries the tying aptitudes.
    $run = tbVariantRun(['turf' => 'A', 'dirt' => 'A', 'sprint' => 'F', 'mile' => 'A', 'medium' => 'A', 'long' => 'G']);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade.ladder_source', TrackblazerVariant::STANDARD)
            ->where('scenario.grade.variant.classification', TrackblazerVariant::AMBIGUOUS)
            ->where('scenario.grade.variant.reason', fn (string $reason): bool => str_contains($reason, 'exactly equal'))
            ->where('scenario.grade.objectives.1.required', 60));
});

it('leaves an unclassified trainee on the standard track without claiming a classification', function (): void {
    // No aptitude letters at all: the conservative denominator prints, and `classification` stays null
    // so the panel can say "not classified" rather than "standard".
    $run = tbVariantRun([]);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade.ladder_source', TrackblazerVariant::STANDARD)
            ->where('scenario.grade.variant.classification', null)
            ->where('scenario.grade.variant.reason', fn (string $reason): bool => str_contains($reason, 'not recorded'))
            ->where('scenario.grade.objectives.1.required', 60));
});

it('carries the aptitude evidence the classification read', function (): void {
    $run = tbVariantRun(['turf' => 'G', 'dirt' => 'A', 'sprint' => 'A', 'mile' => 'B', 'medium' => 'G', 'long' => 'G']);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('scenario.grade.variant.evidence', 6)
            ->where('scenario.grade.variant.evidence.0.label', 'Turf')
            ->where('scenario.grade.variant.evidence.0.letter', 'G')
            ->where('scenario.grade.variant.evidence.1.label', 'Dirt')
            ->where('scenario.grade.variant.evidence.1.letter', 'A'));
});

it('gives no other scenario a Trackblazer variant', function (string $scenario): void {
    // The derivation is Trackblazer's rule and the matrix gates it: `gradeSection()` answers null for
    // every other scenario, so `scenario.grade` is null and no variant travels at all.
    $run = tbVariantRun(['turf' => 'G', 'dirt' => 'A', 'sprint' => 'A', 'mile' => 'B', 'medium' => 'G', 'long' => 'G'], $scenario);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade', null)
            // `gradeObjectives` is the top-level ladder prop, and the model's own gate refuses it too.
            ->where('gradeObjectives', []));
})->with(['ura_finale', 'unity_cup', 'our_grand_concert']);

it('keeps the preflight and cockpit classifications identical for the same trainee', function (): void {
    // The roadmap's consistency requirement: one owner, two doors. The draft reads
    // `TrackblazerVariant::forTrainee()` and the run reads `::forRun()`, which delegates to it.
    (new UmamusumeRosterSeeder)->run();

    foreach (['Haru Urara', 'Curren Chan', 'Agnes Digital', 'Special Week'] as $name) {
        $trainee = Umamusume::query()->where('name', $name)->firstOrFail();
        $run = TrainingRun::factory()->create([
            'scenario' => 'trackblazer',
            'status' => RunStatus::Active,
            'umamusume_id' => $trainee->id,
        ]);

        $fromRun = TrackblazerVariant::forRun($run);
        $fromTrainee = TrackblazerVariant::forTrainee($trainee);

        expect($fromRun)->toBe($fromTrainee)
            ->and($fromRun['classification'])->toBe($fromTrainee['classification'])
            ->and($fromRun['track'])->toBe($fromTrainee['track']);
    }
});

it('exposes the variant on preflight for a Trackblazer draft', function (): void {
    (new UmamusumeRosterSeeder)->run();
    $trainee = Umamusume::query()->where('name', 'Haru Urara')->firstOrFail();

    session([SetupDraft::SESSION_KEY => [
        'scenario' => 'trackblazer',
        'umamusume_id' => $trainee->id,
    ]]);

    $this->get(route('career.preflight'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('contract.build.scenario.key', 'trackblazer')
            ->where('contract.build.scenario.variant.classification', TrackblazerVariant::DIRT_LEANING)
            ->where('contract.build.scenario.variant.track', TrackblazerVariant::DIRT_LEANING));
});

it('exposes no variant on preflight for a scenario with no Result Point objectives', function (): void {
    (new UmamusumeRosterSeeder)->run();
    $trainee = Umamusume::query()->where('name', 'Haru Urara')->firstOrFail();

    session([SetupDraft::SESSION_KEY => [
        'scenario' => 'ura_finale',
        'umamusume_id' => $trainee->id,
    ]]);

    $this->get(route('career.preflight'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('contract.build.scenario.key', 'ura_finale')
            ->where('contract.build.scenario.variant', null));
});

it('recalculates the track when the trainee changes', function (): void {
    // The roadmap's staleness requirement: the variant is derived, never stored, so a trainee swap
    // changes it on the next read with nothing to invalidate.
    $dirt = tbVariantRun(['turf' => 'G', 'dirt' => 'A', 'sprint' => 'A', 'mile' => 'B', 'medium' => 'G', 'long' => 'G']);
    $turf = Umamusume::factory()->create([
        'aptitude_turf' => 'A',
        'aptitude_dirt' => 'F',
        'aptitude_sprint' => 'A',
        'aptitude_mile' => 'D',
        'aptitude_medium' => 'G',
        'aptitude_long' => 'G',
    ]);

    expect($dirt->gradeObjectives()[1]['required'])->toBe(30);

    $dirt->update(['umamusume_id' => $turf->id]);
    $dirt->refresh();

    expect($dirt->gradeObjectives()[1]['required'])->toBe(60);
});

it('does not change the standard figures a standard trainee already saw', function (): void {
    // The regression the ruling's 113 count protects: the standard track is untouched.
    $run = tbVariantRun(['turf' => 'A', 'dirt' => 'G', 'sprint' => 'A', 'mile' => 'A', 'medium' => 'A', 'long' => 'A']);

    $this->get(route('runs.cockpit', $run))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('scenario.grade.ladder_source', TrackblazerVariant::STANDARD)
            ->where('scenario.grade.objectives.0.name', 'Debut race')
            ->where('scenario.grade.objectives.0.required', 0)
            ->where('scenario.grade.objectives.1.required', 60)
            ->where('scenario.grade.objectives.2.required', 300)
            ->where('scenario.grade.objectives.3.required', 300));
});
