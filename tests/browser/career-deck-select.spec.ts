import { test, expect } from '@playwright/test';

/*
 * Behaviour evidence for the support-deck step's picker aim (`SCR-CAR-009`, PRD FR-A-4, `ADR-0014`).
 * `CareerLegacyDeckStepsTest` asserts the resolved props and the draft round trips; this file asserts the
 * one thing only a browser holds: what the address bar says while the picker is being aimed.
 *
 * R3-03. `Replace the card in Slot N` moved the picker in client state and issued no navigation, so the
 * query kept naming the slot the last server round trip wrote — one click behind. A Trainer who copied the
 * address bar between a retarget and the next equip copied the previous slot.
 *
 * **This step writes no database rows.** It reads and writes the session draft (`SetupDraft`), and the run
 * is created at Preflight, so a fresh context is a fresh draft and nothing here needs cleaning up.
 */

async function hydrate(page: import('@playwright/test').Page, url: string): Promise<void> {
    await page.goto(url, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

test('the deck_slot query names the slot the picker aims at (R3-03)', async ({ page }) => {
    await hydrate(page, '/career/setup/deck');

    // The server seeds the aim; Slot 1 is where the picker starts.
    await expect(page.getByText('The picker writes to Slot 1')).toBeVisible();

    await page.getByRole('button', { name: 'Replace the card in Slot 3' }).click();

    // The query and the screen agree in the same render: the caption the Trainer reads and the address
    // they would copy say the same slot.
    await expect(page).toHaveURL(/deck_slot=3/);
    await expect(page.getByText('The picker writes to Slot 3')).toBeVisible();
    await expect(page.getByLabel('Card for Slot 3')).toBeVisible();

    await page.getByRole('button', { name: 'Replace the card in Slot 5' }).click();

    await expect(page).toHaveURL(/deck_slot=5/);
    await expect(page.getByText('The picker writes to Slot 5')).toBeVisible();
});

test('narrowing the picker keeps the slot it was aimed at', async ({ page }) => {
    await hydrate(page, '/career/setup/deck');
    await page.getByRole('button', { name: 'Replace the card in Slot 2' }).click();
    await expect(page).toHaveURL(/deck_slot=2/);

    // The filter is the other navigation out of the same function. It carries the aim with it, so a
    // Trainer who narrows the list after retargeting does not send the picker back to Slot 1.
    await page.locator('select[name="type"]').selectOption('speed');
    await page.getByRole('button', { name: 'Filter' }).click();

    await expect(page).toHaveURL(/deck_slot=2/);
    await expect(page).toHaveURL(/[?&]type=speed/);
    await expect(page.getByText('The picker writes to Slot 2')).toBeVisible();
});
