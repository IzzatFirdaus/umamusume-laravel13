import { expect, type Page } from '@playwright/test';

/*
 * Teardown for a spec that created its own training run, and nothing else.
 *
 * The run is deleted through `runs.destroy` over HTTP rather than by clicking the run record
 * screen's delete disclosure. The cleanup is not product behaviour, so it has no reason to be
 * coupled to a UI surface: the record screen is the 0.1.0 page being retired (plan §4's F1 row, and the
 * write owners that must land first in §9.6),
 * and a teardown that reaches the write through it fails when that page moves, taking every spec
 * that walks it with it and leaving the shared database with a row per abandoned run.
 *
 * The write follows the idiom `career-skills-planner.spec.ts` sets for a fixture write: the
 * context's `XSRF-TOKEN` cookie, percent-decoded, sent as `X-XSRF-TOKEN`, and `maxRedirects: 0` so
 * the status of the write itself is what is asserted. `page.request` shares the browser context's
 * cookie jar, so the session travels with the call. Following the redirect is wrong here for a
 * second reason: Playwright re-sends the DELETE to the target, `runs.index` is GET-only, and the
 * 405 that comes back reads like a failed write after a delete that already landed.
 */
export async function deleteRun(page: Page, runUrl: string): Promise<void> {
    const id = /\/training-runs\/(\d+)/.exec(runUrl)?.[1];
    if (id === undefined) {
        throw new Error(`Not a training-run URL: ${runUrl}`);
    }

    const cookie = (await page.context().cookies()).find((c) => c.name === 'XSRF-TOKEN');

    const response = await page.request.delete(`/training-runs/${id}`, {
        maxRedirects: 0,
        ...(cookie ? { headers: { 'X-XSRF-TOKEN': decodeURIComponent(cookie.value) } } : {}),
    });

    // `runs.destroy` always answers a delete with a redirect to the run list. Anything else leaves
    // the row behind, and the suite says so rather than hiding it.
    expect(
        response.status(),
        `the teardown DELETE failed: ${response.status()} ${response.headers()['location'] ?? ''}`,
    ).toBe(302);
}
