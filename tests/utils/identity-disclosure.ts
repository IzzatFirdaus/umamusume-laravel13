import { expect, type Page } from '@playwright/test';

/*
 * The run's identity and status writes live on their own door, `/training-runs/{run}/settings`
 * (`C6b.1`, `6b5f45da`). They used to sit inside the Cockpit's "Career tools" disclosure, and this
 * helper used to open that nested `<details>` chain. `C6b.11` deleted the disclosure, so a caller
 * that needs to change a status, a scenario, a period or the trainee's identity navigates here.
 *
 * It keeps the shape callers already rely on: it resolves the run id from the URL they are on, so a
 * case can call it straight after landing on the Cockpit, and it waits for the control before
 * returning, so a caller that arrives on a cold cache does not race the select into the DOM.
 *
 * A caller that asserts Cockpit-only content afterwards has to navigate back: this helper leaves the
 * page on the settings door, which is where the forms are.
 */
export async function openIdentitySettings(page: Page): Promise<void> {
    const runId = /\/training-runs\/(\d+)/.exec(page.url())?.[1];

    expect(runId, `the page is not a training-run URL: ${page.url()}`).toBeDefined();

    await page.goto(`/training-runs/${runId}/settings`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // Waiting on the control the caller drives is what makes the return deterministic, rather than
    // "the app mounted something".
    await page.locator('select#status').first().waitFor({ state: 'attached' });
}
