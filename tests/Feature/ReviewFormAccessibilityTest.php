<?php

declare(strict_types=1);

use App\Enums\CandidateStatus;
use App\Models\MatchCandidate;

/*
 * F14 for the review queue's verdict form.
 *
 * The three controls had no accessible name at all. Two were bare `<select>`s and the
 * number input carried a placeholder, which is not a name: a placeholder is not a label,
 * and it stops reading as one the moment the field has a value - which the id field does
 * whenever the engine offered a suggestion. A Trainer reaching this form with a screen
 * reader heard three unlabelled controls and a "Resolve" button, on a page that renders
 * one of those groups per pending candidate.
 *
 * The queue is therefore a case where a *generic* name is not enough: `Status` and
 * `Status` on two cards are both technically named and both useless. Each name carries
 * the candidate it belongs to. That is also what makes the browser locators in the
 * Phase 3A mapping unique, which is the second half of why it matters.
 *
 * Asserted on the parsed DOM rather than by substring, because "this control has a name"
 * is a relation between two elements and a substring cannot see the relation. The XPath
 * is the accessibility name the browser would compute for the simple case F-14 is about:
 * a `<label for>` matching the control's id, falling back to `aria-label`.
 *
 * F-14 was fixed rather than characterized, so no test here pins the unnamed state.
 */

/**
 * The accessible name the browser would compute for every form control on a page.
 *
 * Deliberately the simple algorithm: `aria-label`, else the text of a `<label for>`,
 * else `aria-labelledby`. Anything richer (a title attribute, a wrapping label) is not
 * used by the controls this gate covers, so a control needing one fails here rather than
 * passing on a rule this file does not implement.
 *
 * @return array<string, string> keyed by a stable control identifier
 */
function formControlNames(string $html): array
{
    $document = new DOMDocument;
    $document->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOERROR);
    $xpath = new DOMXPath($document);

    $names = [];
    $unkeyed = 0;

    /** @var DOMElement $control */
    foreach ($xpath->query('//select[@name] | //input[@name] | //button[@type="submit"] | //textarea[@name]') as $control) {
        // A CSRF token is not a control anyone navigates to, and it would otherwise
        // triple the count and hide a real unnamed control in the noise.
        if ($control->getAttribute('type') === 'hidden') {
            continue;
        }

        $id = $control->getAttribute('id');
        $name = $control->getAttribute('name');

        // A control with neither an id nor a name still has to appear, so it keys on its
        // position rather than colliding with every other such control at the same key.
        $key = $name !== ''
            ? $name.($id !== '' ? '#'.$id : '')
            : 'unnamed['.$unkeyed++.']';

        if ($control->hasAttribute('aria-label')) {
            $names[$key] = trim($control->getAttribute('aria-label'));

            continue;
        }

        $names[$key] = $id === '' ? '' : trim((string) $xpath->query('//label[@for="'.$id.'"]')->item(0)?->textContent);
    }

    return $names;
}

it('names every verdict control, and names it after its own candidate', function (): void {
    $candidate = MatchCandidate::factory()->create(['proposed_name' => 'Unmatched One']);

    $names = formControlNames(test()->get('/review')->assertOk()->getContent());

    expect($names)->toHaveCount(4)
        ->and($names['status#review-status-'.$candidate->id])->toBe('Verdict for Unmatched One')
        ->and($names['umamusume_id#review-umamusume-'.$candidate->id])
        ->toBe('Umamusume id (optional) for Unmatched One')
        ->and($names['alias_language#review-alias-language-'.$candidate->id])
        ->toBe('Alias language for Unmatched One')
        ->and(array_values($names)[3])->toBe('Resolve Unmatched One');
});

it('leaves no control on the queue unnamed', function (): void {
    MatchCandidate::factory()->count(3)->create();

    $names = formControlNames(test()->get('/review')->assertOk()->getContent());

    expect($names)->toHaveCount(12);

    foreach ($names as $key => $name) {
        expect($name)->not->toBe('', "control [{$key}] on the review queue has no accessible name");
    }
});

it('keeps the two cards distinguishable, so a name is not ambiguous', function (): void {
    MatchCandidate::factory()->create(['proposed_name' => 'Unmatched One']);
    MatchCandidate::factory()->create(['proposed_name' => 'Unmatched Two']);

    $names = array_values(formControlNames(test()->get('/review')->assertOk()->getContent()));

    expect($names)->toHaveCount(8)
        ->and(array_unique($names))->toHaveCount(8);
});

it('keeps the visible word inside the submit name', function (): void {
    MatchCandidate::factory()->create(['proposed_name' => 'Unmatched One']);

    $html = test()->get('/review')->assertOk()->getContent();

    // WCAG 2.5.3, Label in Name: a name that drops the visible word breaks voice control,
    // which is how the name is made card-specific in the first place.
    $name = array_values(formControlNames($html))[3];

    expect($name)->toContain('Resolve')
        ->and($html)->toContain('>Resolve</button>');
});

it('points a failed field at the message the queue rendered', function (): void {
    $candidate = MatchCandidate::factory()->create();

    $html = test()->withHeader('referer', url('/review'))
        ->followingRedirects()
        ->post("/review/{$candidate->id}", ['status' => CandidateStatus::Aliased->value])
        ->getContent();

    $document = new DOMDocument;
    $document->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOERROR);
    $xpath = new DOMXPath($document);

    $described = $xpath->query('//*[@aria-invalid="true"]');

    expect($described->length)->toBe(1)
        ->and((string) $xpath->query('//*[@id="review-errors-'.$candidate->id.'"]')->item(0)?->textContent)
        ->toContain('alias language');
});
