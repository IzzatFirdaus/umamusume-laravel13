import { test, expect } from '@playwright/test';

// Second probe: does the accessible name settle if we let the Inertia transition finish before
// polling? Distinguishes "the name is wrong" from "the name is unstable during the transition".

test('flash banner accessible-name settles after transition', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('checkbox', { name: 'Numeric failure estimate' }).check();
    await page.getByRole('button', { name: 'Save preferences' }).click();

    // Confirm the text is present, then give the Inertia transition room to settle.
    await expect(page.getByText('Preferences saved.')).toBeVisible();
    await page.waitForTimeout(4000);

    let roleOnlyResult = 'not-run';
    try {
        await expect(page.getByRole('status')).toBeVisible();
        roleOnlyResult = 'PASS';
    } catch {
        roleOnlyResult = 'FAIL';
    }

    let nameRegexResult = 'not-run';
    try {
        await expect(page.getByRole('status', { name: /Preferences saved/ })).toBeVisible();
        nameRegexResult = 'PASS';
    } catch {
        nameRegexResult = 'FAIL';
    }

    let exactNameResult = 'not-run';
    try {
        await expect(page.getByRole('status', { name: 'Preferences saved.' })).toBeVisible();
        exactNameResult = 'PASS';
    } catch {
        exactNameResult = 'FAIL';
    }

    const probe = await page.getByRole('status').first().evaluate((el) => {
        const range = document.createRange();
        range.selectNodeContents(el);
        return {
            textContent: el.textContent,
            rangeText: range.toString(),
            childCount: el.childNodes.length,
            childHtml: el.innerHTML,
            computedRole: el.getAttribute('role'),
            parentHidden: el.closest('[aria-hidden="true"]') !== null,
            displayNone: getComputedStyle(el).display,
            visibilityHidden: getComputedStyle(el).visibility,
        };
    });

    // How many `role="status"` nodes are in the DOM, and what is each one's text? The probe above
    // uses `.first()` while the expects use the raw locator; if there is more than one, the two are
    // reading different elements and the whole comparison is void.
    const allStatus = await page.locator('[role="status"]').all();
    const allStatusTexts = await Promise.all(allStatus.map((loc) => loc.textContent()));

    // eslint-disable-next-line no-console
    console.log('FLASH_PROBE_SETTLED', JSON.stringify({ roleOnlyResult, nameRegexResult, exactNameResult, statusCount: allStatus.length, statusTexts: allStatusTexts, probe }, null, 2));

    expect(nameRegexResult, 'name-regex probe recorded after settle').toBe('PASS');
});