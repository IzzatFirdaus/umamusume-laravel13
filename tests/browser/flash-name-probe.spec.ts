import { test, expect } from '@playwright/test';

// Diagnostic probe for the D18 `getByRole('status', { name: 'Preferences saved.' })`
// failure at preferences.spec.ts:53. The save is slow in this sandbox, so every
// locator variant runs against ONE save. The element dump uses a CSS selector, not
// getByRole, so it works even when getByRole disagrees with the aria snapshot.

test('flash banner accessible-name probe', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('checkbox', { name: 'Numeric failure estimate' }).check();

    const saveStart = Date.now();
    await page.getByRole('button', { name: 'Save preferences' }).click();
    const saveElapsed = Date.now() - saveStart;

    const textStart = Date.now();
    await expect(page.getByText('Preferences saved.')).toBeVisible();
    const textElapsed = Date.now() - textStart;

    const probe = async (fn: () => Promise<unknown>): Promise<string> => {
        try {
            await fn();
            return 'PASS';
        } catch {
            return 'FAIL';
        }
    };

    const roleOnly = await probe(() => expect(page.getByRole('status')).toBeVisible());
    const nameRegex = await probe(() => expect(page.getByRole('status', { name: /Preferences saved/ })).toBeVisible());
    const nameSubstring = await probe(() => expect(page.getByRole('status', { name: 'Preferences saved.' })).toBeVisible());
    const nameExact = await probe(() => expect(page.getByRole('status', { name: 'Preferences saved.', exact: true })).toBeVisible());

    const dump = await page.evaluate(() => {
        const statuses = Array.from(document.querySelectorAll('[role="status"]'));
        return {
            statusCount: statuses.length,
            statuses: statuses.map((el) => {
                const range = document.createRange();
                range.selectNodeContents(el);
                return {
                    textContent: el.textContent,
                    trimmed: el.textContent ? el.textContent.trim() : null,
                    rangeText: range.toString(),
                    rangeTrimmed: range.toString().trim(),
                    ariaLabel: el.getAttribute('aria-label'),
                    ariaLabelledBy: el.getAttribute('aria-labelledby'),
                    role: el.getAttribute('role'),
                    ariaLive: el.getAttribute('aria-live'),
                    childCount: el.childNodes.length,
                    innerHtml: el.innerHTML,
                };
            }),
        };
    });

    let ariaSnapshot: unknown = null;
    try {
        ariaSnapshot = await page.locator('main').ariaSnapshot();
    } catch (e) {
        ariaSnapshot = { error: String(e) };
    }

    // eslint-disable-next-line no-console
    console.log('FLASH_PROBE', JSON.stringify({
        saveElapsedMs: saveElapsed,
        textWaitMs: textElapsed,
        roleOnly,
        nameRegex,
        nameSubstring,
        nameExact,
        dump,
        ariaSnapshot,
    }, null, 2));

    expect(roleOnly, 'role-only must pass for the element to be in the a11y tree').toBe('PASS');
});
