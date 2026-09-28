<?php

declare(strict_types=1);

use App\Enums\AliasLanguage;
use App\Enums\CandidateStatus;
use App\Models\MatchCandidate;
use App\Models\Umamusume;
use Illuminate\Support\ViewErrorBag;

/*
 * D-2 / D-56 for the review queue's verdict form.
 *
 * The view rendered `@error('status')` and nothing else, while
 * `ResolveMatchCandidateRequest` validates three fields: `status` (required, enum),
 * `umamusume_id` (integer, must exist) and `alias_language` (required when the
 * verdict is Aliased). Two failures a Trainer can actually reach - an alias with no
 * language, and an id that is not in the catalog - therefore failed silently: the
 * submit did nothing visible and the form looked broken.
 *
 * These are feature tests, not browser tests, because the defect was a missing
 * message in the response, not a missing interaction. D-2 was fixed rather than
 * characterized: the old behaviour violated D-56, so no test may pin it.
 *
 * The assertions read the messages out of the error bag rather than hardcoding
 * Laravel's phrasing. What D-2 is about is that the message the validator produced
 * reaches the page, so the test asserts exactly that and stays true if the wording
 * is ever reworded.
 */

/**
 * Every message the validator produced for a verdict payload, together with the review
 * page as it renders those messages.
 *
 * The verdict form posts back to itself and a failed validation redirects with `back()`,
 * so the flashed bag reaches whichever page the browser came from. A test has no referer
 * and would land on `/`, which is why the referer is set: it is the queue URL a real
 * Trainer is standing on, and reading the bag off a different page would test nothing.
 *
 * The messages are read from the error bag the template was *given*, via the response's
 * view data. Reading them from the session does not work: the redirect consumes the flash
 * before anything can look at it. Reading them from the view proves both halves of D-2 at
 * once - the template received the message, and the markup contains it.
 *
 * @return array{0: list<string>, 1: string}
 */
function verdictErrorMessages(array $payload): array
{
    $candidate = MatchCandidate::factory()->create();

    test()->withHeader('referer', url('/review'))
        ->post("/review/{$candidate->id}", $payload)
        ->assertRedirect('/review');

    $response = test()->get('/review')->assertOk();

    $bag = $response->viewData('errors');

    expect($bag)->toBeInstanceOf(ViewErrorBag::class);

    // `messages()` is keyed by field; this is the flat list of everything the
    // validator said, which is the list the template has to account for.
    return [array_merge(...array_values($bag->getBag('default')->messages())), $response->getContent()];
}

/**
 * The subset of a verdict's validator messages the review page fails to render.
 *
 * Before the D-2 fix this returned the `umamusume_id` and `alias_language` messages
 * while the form reported a failed submit, which is the whole defect.
 *
 * @return list<string>
 */
function unrenderedVerdictMessages(array $payload): array
{
    [$messages, $html] = verdictErrorMessages($payload);

    return array_values(array_filter(
        $messages,
        fn (string $message): bool => ! str_contains($html, e($message))
    ));
}

it('renders the missing alias language on an Aliased verdict', function (): void {
    $candidate = MatchCandidate::factory()->create();

    test()->post("/review/{$candidate->id}", ['status' => CandidateStatus::Aliased->value])
        ->assertSessionHasErrors('alias_language');

    expect(unrenderedVerdictMessages(['status' => CandidateStatus::Aliased->value]))->toBe([]);
});

it('stacks the unknown id and the missing language rather than showing one', function (): void {
    $messages = verdictErrorMessages([
        'status' => CandidateStatus::Aliased->value,
        'umamusume_id' => 999999,
    ]);

    expect($messages)->toHaveCount(2)
        ->and(unrenderedVerdictMessages([
            'status' => CandidateStatus::Aliased->value,
            'umamusume_id' => 999999,
        ]))->toBe([]);
});

it('shows the enum failure on a verdict that is not one of the three', function (): void {
    $candidate = MatchCandidate::factory()->create();

    test()->post("/review/{$candidate->id}", ['status' => 'Merged'])
        ->assertSessionHasErrors('status');

    expect(unrenderedVerdictMessages(['status' => 'Merged']))->toBe([]);
});

it('keeps the accepted verdict free of error copy', function (): void {
    $umamusume = Umamusume::factory()->create();
    $candidate = MatchCandidate::factory()->create();

    test()->post("/review/{$candidate->id}", [
        'status' => CandidateStatus::Aliased->value,
        'umamusume_id' => $umamusume->id,
        'alias_language' => AliasLanguage::Japanese->value,
    ])->assertSessionHasNoErrors();

    expect(test()->get('/review')->assertOk()->getContent())
        ->not->toContain('role="alert"');
});
