import { expect, test } from './support/base-test';

/*
 * MWG F9: `closedby="any"` and its mandatory pointer-position fallback.
 *
 * `closedby` is Limited availability (Chrome 134+, Firefox 141+; not Safari). Where it is absent a
 * modal `<dialog>` carrying only the attribute behaves as `closerequest`: Escape closes it, a
 * backdrop click does nothing. The guidance marks the fallback mandatory, so `useDialogLightDismiss`
 * feature-detects `'closedBy' in HTMLDialogElement.prototype` and, only when that is false, closes
 * on a click whose point falls outside the dialog's own box.
 *
 * The three owners are `CardPicker.vue`, `SupportSlot.vue` and `RaceCard.vue`. They share the one
 * composable, so this file drives the behaviour on the cheapest surface to reach — the deck step's
 * card picker — and asserts the wiring on all three where the wiring is what can silently rot.
 *
 * The native half runs under the suite's Chromium, which does support `closedby`. The fallback half
 * deletes the prototype member before any page script runs, which is exactly the condition the
 * composable tests for; the case also proves the deletion took, so a browser that made the member
 * non-configurable cannot turn this file into a vacuous pass.
 */

const DECK = '/career/setup/deck';
const DIALOG = 'dialog[aria-label="Choose a card for Slot 1"]';

async function openPicker(page: import('@playwright/test').Page): Promise<import('@playwright/test').Locator> {
    await page.goto(DECK, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('button', { name: 'Choose a card for Slot 1' }).click();

    const dialog = page.locator(DIALOG);
    await expect(dialog).toBeVisible();

    return dialog;
}

/** A point guaranteed outside the dialog's own box: the sheet is centred, so the canvas corner is backdrop. */
async function clickBackdrop(page: import('@playwright/test').Page, dialog: import('@playwright/test').Locator): Promise<void> {
    const box = await dialog.boundingBox();

    expect(box, 'the dialog box is measurable').not.toBeNull();
    await page.mouse.click(5, 5);
}

test('native closedby: a click inside keeps the sheet, Escape and a backdrop click close it', async ({ page }) => {
    const dialog = await openPicker(page);

    // Proves this browser really is on the native path, so the assertions below are not being
    // satisfied by the fallback in disguise.
    const native = await page.evaluate(() => 'closedBy' in HTMLDialogElement.prototype);

    expect(native, 'this Chromium does not expose closedBy, so the native half measured nothing').toBe(true);

    // Inside the box: the click must not be mistaken for a dismissal, or a stray press on a row
    // would throw away the Trainer's place in a long roster.
    await dialog.getByRole('heading', { name: 'Choose a card for Slot 1' }).click();
    await expect(dialog).toBeVisible();

    await page.keyboard.press('Escape');
    await expect(dialog).not.toBeVisible();

    await openPicker(page);
    await clickBackdrop(page, dialog);
    await expect(dialog).not.toBeVisible();
});

test('fallback: without native closedBy a backdrop click closes, an inside click does not', async ({ browser }) => {
    const context = await browser.newContext();
    const page = await context.newPage();

    // Before any page script: this is the Safari condition. If the member cannot be removed the
    // fallback is unreachable here and the case fails rather than quietly measuring the native path.
    await page.addInitScript(() => {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        delete (HTMLDialogElement.prototype as any).closedBy;
    });

    await page.goto(DECK, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const fallbackActive = await page.evaluate(() => !('closedBy' in HTMLDialogElement.prototype));
    expect(fallbackActive, 'the prototype member could not be removed, so the fallback never ran').toBe(true);

    await page.getByRole('button', { name: 'Choose a card for Slot 1' }).click();
    const dialog = page.locator(DIALOG);
    await expect(dialog).toBeVisible();

    // Inside click: survives. This is the regression the pointer-position check exists to prevent —
    // a naive `click` listener on the dialog would close on every interaction with its contents.
    await dialog.getByRole('heading', { name: 'Choose a card for Slot 1' }).click();
    await expect(dialog).toBeVisible();

    // Backdrop click: closes, which native `closerequest` alone would not do.
    await clickBackdrop(page, dialog);
    await expect(dialog).not.toBeVisible();

    // Reopening still works and still dismisses, so the fallback did not leave a stale listener that
    // swallows later clicks.
    await page.getByRole('button', { name: 'Choose a card for Slot 1' }).click();
    await expect(dialog).toBeVisible();
    await clickBackdrop(page, dialog);
    await expect(dialog).not.toBeVisible();

    // Escape is the browser's own closerequest path and must keep working alongside the fallback.
    await page.getByRole('button', { name: 'Choose a card for Slot 1' }).click();
    await expect(dialog).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(dialog).not.toBeVisible();

    await context.close();
});
