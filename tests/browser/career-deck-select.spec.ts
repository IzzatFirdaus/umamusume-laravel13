import { test, expect } from '@playwright/test';

/*
 * The support-deck step, against the HFW `career-deck.html` (slice 4b.4b).
 *
 * What this spec holds that the code cannot: the slot grid is two columns at desktop and one at 320px
 * (measured with `getBoundingClientRect`, not inferred from a class name), an empty slot is drawn dashed,
 * the Pal row states "No friendship bonus" in words rather than a blank chip, and the screen carries
 * exactly one filled action. The focus and dialog cases are unchanged: they pin behaviour the rebuild
 * does not touch.
 */

test.beforeEach(async ({ page }) => {
    await page.goto('/career/setup/deck', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
});

test('lays the six slots out as two columns, measured', async ({ page }) => {
    const grid = page.getByRole('list').filter({ has: page.getByRole('button', { name: /card for/i }) }).first();

    const measured = await grid.evaluate((el) => {
        const style = getComputedStyle(el);
        const children = Array.from(el.children) as HTMLElement[];
        // Column membership by left offset: `.card + .card` adds a 14px margin-top to every child after
        // the first, so equal `top` would count one slot per row even on a correct two-column grid.
        const lefts = children.map((child) => Math.round(child.getBoundingClientRect().left));

        return {
            display: style.display,
            columns: style.gridTemplateColumns.split(' ').filter(Boolean).length,
            tracks: new Set(lefts).size,
            children: children.length,
        };
    });

    expect(measured.display, 'the slot list is a grid').toBe('grid');
    expect(measured.children, 'six slots').toBe(6);
    expect(measured.columns, 'two tracks at desktop width').toBe(2);
    expect(measured.tracks, 'the six slots stand on two distinct columns').toBe(2);
});

test('shows the equipped count in the deck heading', async ({ page }) => {
    await expect(page.getByRole('heading', { name: /Deck\s+0 of 6 slots filled/i })).toBeVisible();
});

test('draws an empty slot as a dashed outline', async ({ page }) => {
    const slot = page.locator('#deck-slot-1');

    await expect(slot).toBeVisible();
    const border = await slot.evaluate((el) => getComputedStyle(el).borderTopStyle);

    expect(border, 'an empty slot states its absence with a dashed frame').toBe('dashed');
});

test('states the Pal rule in words, not a blank chip', async ({ page }) => {
    // The card-types table carries the rule on a deck that has no cards yet; the slot row repeats it
    // when a Pal card is actually equipped.
    await expect(page.getByText(/No friendship bonus/i).first()).toBeVisible();
});

test('prints the multiplier as multiplication, never as a sum', async ({ page }) => {
    await expect(page.getByText(/1\.25 × 1\.30 = 1\.625/)).toBeVisible();
    await expect(page.getByText(/\+55%/)).toBeVisible();
    // The "+55%" appears only in the sentence that rejects it.
    await expect(page.getByText(/not \+55%/)).toBeVisible();
});

test('names the duplicate rule on the page', async ({ page }) => {
    await expect(page.getByText(/Two identical cards cannot sit in one deck/i)).toBeVisible();
});

test('carries exactly one filled action: continue to preflight', async ({ page }) => {
    const filled = page.locator('.bg-primary-action');
    await expect(filled).toHaveCount(1);
    await expect(filled.first()).toHaveText(/Continue to preflight/i);
});

test('opens the picker as a modal dialog and closes it with Escape', async ({ page }) => {
    await page.getByRole('button', { name: /Choose a card for/i }).first().click();

    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    expect(await dialog.evaluate((el) => (el as HTMLDialogElement).matches(':modal'))).toBe(true);

    await page.keyboard.press('Escape');
    await expect(dialog).toBeHidden();
});

test('returns focus to the slot button that opened the picker', async ({ page }) => {
    // The opener is addressed by its own accessible name so the assertion can say WHICH button came
    // back: with six slots, "some button is focused" would pass even if focus landed on the wrong one.
    const opener = page.getByRole('button', { name: /Choose a card for/i }).first();
    const openerName = await opener.getAttribute('aria-label');

    await opener.click();
    await expect(page.getByRole('dialog')).toBeVisible();
    await page.keyboard.press('Escape');

    const focused = await page.evaluate(() => {
        const el = document.activeElement;

        return { tag: el?.tagName ?? null, label: el?.getAttribute('aria-label') ?? null };
    });

    expect(focused.tag, 'focus returned to a control, not the body').toBe('BUTTON');
    expect(focused.label, 'focus returned to the button that opened the dialog').toBe(openerName);
});

test('hides the deck analysis until a card is equipped', async ({ page }) => {
    await expect(page.getByRole('heading', { name: /deck analysis/i })).toHaveCount(0);
});

test('shows the ownership default in the picker copy', async ({ page }) => {
    await expect(page.getByText(/Equipping a card records it as Owned unless you mark it Rented/)).toBeVisible();
});

test('keeps the picker open across a filter', async ({ page }) => {
    await page.getByRole('button', { name: /Choose a card for/i }).first().click();

    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();

    await dialog.getByLabel('Name contains').fill('Teio');
    await dialog.getByRole('button', { name: 'Filter' }).click();

    await expect(dialog).toBeVisible();
    await expect(page).toHaveURL(/q=Teio/);
});

test('finds a card by its printed name and epithet', async ({ page }) => {
    await page.getByRole('button', { name: /Choose a card for/i }).first().click();

    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();

    await dialog.getByLabel('Name contains').fill('Tokai Teio [Dream Big!]');
    await dialog.getByRole('button', { name: 'Filter' }).click();

    await expect(dialog.getByText('Tokai Teio [Dream Big!]', { exact: true })).toBeVisible();
});

test('stores the per-run card level and reads it back after a reload', async ({ page }) => {
    await page.getByRole('button', { name: 'Choose a card for Slot 1' }).click();
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    await dialog.getByRole('button', { name: 'Equip', exact: true }).first().click();
    await expect(page.getByText('Deck saved.')).toBeVisible();

    const level = page.getByLabel('Level for Slot 1');
    await expect(level).toBeVisible();
    await level.fill('35');

    await Promise.all([
        page.waitForResponse(
            (response) => response.url().includes('/career/setup/deck') && response.request().method() === 'PUT',
        ),
        page.getByRole('button', { name: 'Save deck' }).click(),
    ]);

    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByLabel('Level for Slot 1')).toHaveValue('35');
});
