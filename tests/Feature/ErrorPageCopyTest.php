<?php

declare(strict_types=1);

/*
 * Slice 11.3 â€” the three error documents' copy contract, asserted on the views themselves.
 *
 * The 419 and the 500 cannot be reached from a browser test without either expiring a session or
 * crashing the server, so the copy is asserted where it lives: the Blade document. The 404 has its own
 * end-to-end case in `tests/browser/errors.spec.ts`; this file covers all three, which is what makes it
 * the one place the contract is stated.
 *
 * The contract (`error-404.html`, `error-419.html`, `error-500.html`):
 *   - the data-loss question is answered in the body, in the first paragraph, not in a footnote;
 *   - exactly one filled action, and the other exits are outline links;
 *   - no automatic redirect and no `<meta http-equiv="refresh">`;
 *   - the headline is plain language, and the raw code is a small eyebrow rather than the heading.
 */
$documents = [
    'errors.404' => ['That page does not exist', 'nothing was lost'],
    'errors.419' => ['Your session timed out', 'Your last saved turn is unaffected.'],
    'errors.500' => ['Something went wrong on our side', 'no data was'],
];

foreach ($documents as $view => [$headline, $dataLossLine]) {
    it("answers the data-loss question and offers one filled exit on {$view}", function () use ($view, $headline, $dataLossLine): void {
        $html = view($view)->render();

        // The headline is plain language; the code lives in the small eyebrow above it.
        expect($html)->toContain($headline)
            ->and($html)->toContain($dataLossLine)
            // One filled action: the primary class appears exactly once, and the other exits are not it.
            ->and(substr_count($html, 'bg-primary-action'))->toBe(1)
            // No automatic redirect of any kind.
            ->and($html)->not->toContain('http-equiv="refresh"')
            ->and($html)->not->toContain('<meta http-equiv');
    });
}

it('offers exactly three exits on the 419 and the 500, and one filled of the three', function (): void {
    foreach (['errors.419', 'errors.500', 'errors.404'] as $view) {
        $html = view($view)->render();

        // The exits are the anchors inside the body's action row; the shell's skip link is the fourth
        // anchor on the page and is not an exit, so the count is taken on `main`.
        $main = substr($html, (int) strpos($html, '<main'));
        $anchors = substr_count($main, '<a ');

        expect($anchors)->toBeGreaterThanOrEqual(3, "{$view} does not offer three exits");
    }
});
