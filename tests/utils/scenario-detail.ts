import { expect, type Page } from '@playwright/test';

/*
 * The scenario panels (URA, Trackblazer, Grand Concert) render inside a closed
 * `details[data-region="scenario-detail"]` on the Cockpit: no cockpit HFW draws them, so the visible
 * page stays the wireframe's. A spec that reads a panel therefore has to open that disclosure first,
 * or every locator inside it resolves to nothing because a closed `<details>` keeps its content out
 * of the accessibility tree.
 *
 * The helper is idempotent: it only clicks when the disclosure is still closed, so a case that calls
 * it after a reload does not toggle an already-open panel shut.
 */
export async function openScenarioDetail(page: Page): Promise<void> {
    // The page mounts client-side, so the disclosure does not exist yet on a cold arrival.
    await page.locator('#app > *').first().waitFor();

    const detail = page.locator('details[data-region="scenario-detail"]');

    // A scenario whose matrix composes no panel renders no disclosure at all; that is a legal page,
    // not a failure, and the flag-off cases assert exactly that.
    if ((await detail.count()) === 0) {
        return;
    }

    const first = detail.first();
    await expect(first).toBeAttached();

    const open = await first.evaluate((el) => (el as HTMLDetailsElement).open);

    if (!open) {
        await first.locator('summary').first().click();
        await expect(first).toHaveAttribute('open', '');
    }
}
