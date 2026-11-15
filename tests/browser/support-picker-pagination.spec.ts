import { test, expect } from '@playwright/test';

/*
 * R6-24: the deck step's support-card picker returned every Global-released card in one payload
 * (`->get()`), so the dialog mounted 251 rows at once and the Dashboard's 559-card total had no
 * scoped partner. The picker now pages at 25 and the Dashboard prints the Global count beside the
 * catalogue count.
 *
 * This case reads the picker's own pager and the page change it drives. The exact seeded totals are
 * not hardcoded: the browser harness owns its scratch database, and the assertion that matters is
 * that the pager reports a real range and that Next moves the list.
 */

test('paginates the support-card picker at 25 cards per page', async ({ page }) => {
    await page.goto('/career/setup/deck', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await page.locator('#deck-slot-1 button[aria-label^="Choose a card"]').click();

    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();

    const pager = dialog.locator('[data-region="pager"]');
    await expect(pager).toBeVisible();
    await expect(pager).toContainText(/\d+ cards · page 1 of \d+/);

    const rows = dialog.locator('ul[aria-label="Support cards, scrollable"] li');
    await expect(rows).toHaveCount(25);

    const firstPage = await rows.allInnerTexts();

    await pager.getByRole('button', { name: 'Next' }).click();

    await expect(pager).toContainText(/\d+ cards · page 2 of \d+/);
    await expect(rows).toHaveCount(25);

    const secondPage = await rows.allInnerTexts();
    expect(secondPage).not.toEqual(firstPage);
});

test('keeps the picker page when the same slot is reopened or equipped', async ({ page }) => {
    await page.goto('/career/setup/deck', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('button', { name: 'Choose a card for Slot 1' }).click();

    const dialog = page.getByRole('dialog');
    const pager = dialog.locator('[data-region="pager"]');
    const rows = dialog.locator('ul[aria-label="Support cards, scrollable"] li');

    await expect(pager).toContainText(/page 1 of \d+/);
    await pager.getByRole('button', { name: 'Next' }).click();
    await expect(pager).toContainText(/page 2 of \d+/);

    // Reopening the slot already being browsed must keep the page, not snap back to page 1.
    await page.keyboard.press('Escape');
    await expect(dialog).not.toBeVisible();
    await page.getByRole('button', { name: 'Choose a card for Slot 1' }).click();
    await expect(dialog).toBeVisible();
    await expect(pager).toContainText(/page 2 of \d+/);

    // Equipping from page 2 must leave the step on page 2 so the next card is one click away.
    await rows.first().getByRole('button', { name: 'Equip', exact: true }).click();
    await expect(dialog).not.toBeVisible();
    await page.getByRole('button', { name: 'Replace the card in Slot 1' }).click();
    await expect(dialog).toBeVisible();
    await expect(pager).toContainText(/page 2 of \d+/);
});
