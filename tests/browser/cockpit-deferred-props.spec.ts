import { test, expect } from './support/base-test';
import type { Page, Route } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';

/*
 * UI/UX Phase 5 §5.3 - the Cockpit's Performance reading is a deferred prop.
 *
 * The Feature half (`CockpitDeferredPropsTest`) pins the wire contract: the first payload omits
 * `performance` and the group partial reload returns it. These cases pin what only a browser reaches:
 * the placeholder the `<Deferred>` fallback draws while the partial reload is in flight, the real
 * card once it lands, and the rescue slot when the server marks the prop failed.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

async function createGrandConcertRun(page: Page): Promise<string> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'our_grand_concert' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);

    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    return runUrl;
}

/** True when the request is the `<Deferred>` partial reload that asks for the performance key. */
function requestsPerformance(route: Route): boolean {
    return (route.request().headers()['x-inertia-partial-data'] ?? '').split(',').includes('performance');
}

test('draws the deferred placeholder, then the real Performance card', async ({ page }) => {
    const runUrl = await createGrandConcertRun(page);

    // Hold the partial reload open long enough to observe the fallback, then let it land.
    await page.route('**/training-runs/**/cockpit', async (route) => {
        if (requestsPerformance(route)) {
            await new Promise((resolve) => setTimeout(resolve, 500));
        }

        await route.continue();
    });

    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('status').filter({ hasText: 'Loading performance readings.' }))
        .toHaveText('Loading performance readings.');
    await expect(page.getByRole('heading', { name: 'Performance' })).toBeVisible();

    // The deferred payload replaces the placeholder: the five published types and their absence
    // sentence, not an empty skeleton. The static concert-loop card waits on the same flag and is
    // unaffected.
    await expect(page.getByText('Dance', { exact: true })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'The concert loop' })).toBeVisible();
});

test('renders the rescue slot when the server marks the deferred prop failed', async ({ page }) => {
    const runUrl = await createGrandConcertRun(page);

    let rescueOnce = true;
    await page.route('**/training-runs/**/cockpit', async (route) => {
        if (rescueOnce && requestsPerformance(route)) {
            rescueOnce = false;

            const response = await route.fetch();
            const body = await response.json() as {
                props: Record<string, unknown>;
                rescuedProps?: string[];
            };

            delete body.props.performance;
            body.rescuedProps = ['performance'];

            await route.fulfill({ response, json: body });

            return;
        }

        await route.continue();
    });

    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('alert')).toHaveText(/could not be loaded/);
    await expect(page.getByRole('button', { name: 'Retry' })).toBeVisible();
});
