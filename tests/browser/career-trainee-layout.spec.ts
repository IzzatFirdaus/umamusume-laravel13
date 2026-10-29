import { expect, test } from '@playwright/test';
import { resetLayout, setLayout } from './support/layout';

/*
 * The three Layout-width modes are a composition, not only a width cap (`career-trainee.html` reads its
 * shape from `shell.css:412-414`, which collapses every grid to one column and tightens the lede to 60ch
 * when the layout is compact). The app drew three 231px cards inside compact's own 716px measure and left
 * the lede uncapped at 14px, so the modes differed only in width - which is no reason to offer them.
 *
 * `layout-width.spec.ts` pins the shell class on `#main`. This spec pins what the step itself does with
 * the mode, because that is the half that was missing.
 */

/** The roster grid's resolved track count, which is the property the wireframe's compact rule changes. */
async function trackCount(page: import('@playwright/test').Page): Promise<number> {
    return page
        .locator('[data-region="card-grid"]')
        .evaluate((el) => getComputedStyle(el).gridTemplateColumns.trim().split(/\s+/).length);
}

test.describe('trainee step follows the layout width mode', () => {
    test.beforeEach(async ({ page }) => {
        await page.setViewportSize({ width: 1440, height: 900 });
    });

    // The preference is shared state, so every case leaves Standard behind even when it fails midway.
    test.afterEach(async ({ page }) => {
        await resetLayout(page);
    });

    test('Compact stacks the roster into one column and caps the lede at 60ch', async ({ page }) => {
        await setLayout(page, 'compact');
        await page.goto('/career/setup/trainee');
        await page.locator('[data-trainee-card]').first().waitFor();

        expect(await trackCount(page), 'the wireframe collapses every grid to one column').toBe(1);
        await expect(page.locator('[data-region="lede"]')).toHaveClass(/max-w-lede-compact/);
        await expect(page.locator('[data-region="lede"]')).toHaveCSS('font-size', '13px');
    });

    test('Standard keeps the three-column grid and the 66ch lede', async ({ page }) => {
        await setLayout(page, 'standard');
        await page.goto('/career/setup/trainee');
        await page.locator('[data-trainee-card]').first().waitFor();

        expect(await trackCount(page)).toBe(3);
        await expect(page.locator('[data-region="lede"]')).toHaveClass(/max-w-lede/);
        await expect(page.locator('[data-region="lede"]')).not.toHaveClass(/max-w-lede-compact/);
    });

    test('a card takes the wireframe corner and padding in every mode', async ({ page }) => {
        await page.goto('/career/setup/trainee');
        await page.locator('[data-trainee-card]').first().waitFor();

        // `--radius-card` is the 10px corner the wireframe's `.card` carries (`shell.css:370`); the
        // roster drew `rounded-lg`, which resolves to the 24px dialog radius in this app's theme.
        await expect(page.locator('[data-trainee-card]').first()).toHaveCSS('border-radius', '10px');
        await expect(page.locator('[data-trainee-card]').first()).toHaveCSS('padding', '14px 16px');
    });

    test('an aptitude value never breaks between its axis name and its letter', async ({ page }) => {
        await page.goto('/career/setup/trainee');
        await page.locator('[data-trainee-card]').first().waitFor();

        // At three columns a value cell is ~205px wide, and a plain space let the row break inside
        // "Front runner G", leaving a bare "G" as a whole line of its own. The binding is a non-breaking
        // space, not `whitespace-nowrap`: the span still has to be breakable at 320px, where the reflow
        // case below measures the document against the window.
        const wrapped = await page
            .locator('[data-trainee-card] dt + dd > span:not([aria-hidden])')
            .evaluateAll((nodes) => nodes.filter((node) => node.getClientRects().length > 1).length);

        expect(wrapped, 'an axis span wrapped onto more than one line at the three-column measure').toBe(0);
    });
});
