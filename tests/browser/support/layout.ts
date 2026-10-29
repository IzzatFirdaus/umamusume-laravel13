import { expect, type Page } from '@playwright/test';

/*
 * The Layout-width preference is stored server-side and shared with every page, so a spec that changes
 * it must put it back: the browser suite is one serial worker against one scratch database, and a
 * leaked `wide` would silently change every later spec's composition. `resetLayout` is therefore part
 * of each mode-aware spec's teardown.
 *
 * The radios' labels carry their descriptions ("Wide Wide bento composition…"), so `getByLabel` with
 * the leading word is the locator; an exact role name does not match.
 */
export type LayoutMode = 'compact' | 'standard' | 'wide';

const RADIO: Record<LayoutMode, RegExp> = {
    compact: /^Compact/,
    standard: /^Standard/,
    wide: /^Wide/,
};

export async function setLayout(page: Page, mode: LayoutMode): Promise<void> {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    const radio = page.getByLabel(RADIO[mode]);

    if (await radio.isChecked()) {
        return;
    }

    await radio.check();
    await page.getByRole('button', { name: 'Save changes' }).click();
    // The server's own acknowledgement, not the form's client-side dirty flag: the flag can lag the
    // Inertia redirect under load, and a layout spec should wait on the write it actually made.
    await expect(page.getByText(/^Preferences saved\./)).toBeVisible();
}

export async function resetLayout(page: Page): Promise<void> {
    await setLayout(page, 'standard');
}
