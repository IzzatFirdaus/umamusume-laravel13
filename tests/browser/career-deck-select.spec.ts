import { test, expect } from '@playwright/test';

/*
 * The support-deck step (plan 3.7). Rendered evidence, so it reads the built bundle.
 *
 * What this spec holds that the code cannot: the dialog actually opens as a modal, the grid is three
 * columns at desktop and one at 320px (measured with `getBoundingClientRect`, not inferred from a class
 * name), the analysis panel appears only once a card is equipped, and focus returns to the slot's own
 * button after an equip. The focus case is asserted through `document.activeElement` because that is
 * the only thing that proves the return happened rather than that a `focus()` call exists.
 */

test.beforeEach(async ({ page }) => {
    await page.goto('/career/setup/deck', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
});

test('lays the six slots out as three columns, measured', async ({ page }) => {
    const grid = page.getByRole('list').filter({ has: page.getByRole('button', { name: /card for/i }) }).first();

    const measured = await grid.evaluate((el) => {
        const style = getComputedStyle(el);
        const children = Array.from(el.children) as HTMLElement[];
        const firstRowTop = children[0]?.getBoundingClientRect().top;

        return {
            display: style.display,
            columns: style.gridTemplateColumns.split(' ').filter(Boolean).length,
            perRow: children.filter((child) => child.getBoundingClientRect().top === firstRowTop).length,
            children: children.length,
        };
    });

    expect(measured.display, 'the slot list is a grid').toBe('grid');
    expect(measured.children, 'six slots').toBe(6);
    expect(measured.perRow, 'three slots to a row at desktop width').toBe(3);
});

test('opens the picker as a modal dialog and closes it with Escape', async ({ page }) => {
    await page.getByRole('button', { name: /Choose a card for/i }).first().click();

    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    // A modal dialog makes the page behind it inert; `open` on a `<dialog>` in modal state is what
    // `showModal()` sets.
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

        return {
            tag: el?.tagName ?? null,
            label: el?.getAttribute('aria-label') ?? null,
        };
    });

    expect(focused.tag, 'focus returned to a control, not the body').toBe('BUTTON');
    expect(focused.label, 'focus returned to the button that opened the dialog').toBe(openerName);
});

test('hides the deck analysis until a card is equipped', async ({ page }) => {
    // A fresh draft holds no card, so the panel that counts a deck has nothing to count.
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

    // The round trip refines the list with `preserveState`; the dialog stays open over it rather than
    // costing a click per search (R4-21).
    await expect(dialog).toBeVisible();
    await expect(page).toHaveURL(/q=Teio/);
});

test('finds a card by its printed name and epithet', async ({ page }) => {
    await page.getByRole('button', { name: /Choose a card for/i }).first().click();

    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();

    // The printed label is `Name [Epithet]`; searching it whole must match (R4-21).
    await dialog.getByLabel('Name contains').fill('Tokai Teio [Dream Big!]');
    await dialog.getByRole('button', { name: 'Filter' }).click();

    await expect(dialog.getByText('Tokai Teio [Dream Big!]', { exact: true })).toBeVisible();
});
