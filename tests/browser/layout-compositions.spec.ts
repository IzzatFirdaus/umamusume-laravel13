import { test, expect, type Page } from '@playwright/test';
import { resetLayout, setLayout } from './support/layout';

/*
 * HFW v3 composition evidence: the wireframe's `.g-2` / `.g-3` / `.g-4` / `.g-side` at Standard and
 * Wide, one column for every one of them in Compact, and the one measure the wireframe gives the top
 * bar, page head and canvas (`shell.css:352-367`). Fixed viewport at 1280 so only the mode moves; the
 * 1920 case measures the three caps.
 */

const GRIDS: { path: string; grids: [selector: string, tracks: number][] }[] = [
    {
        path: '/',
        // The pickup card only exists with an active career, and the harness seeds no runs. The KPI
        // strip (g-4) and the veterans/quick-actions row (g-side) render in both the empty and the
        // populated state, so those are the two the composition can be asserted against here.
        grids: [
            ['[data-dashboard-row="kpi"]', 4],
            ['[data-dashboard-row="veterans"]', 2],
        ],
    },
    { path: '/career/setup/scenario', grids: [['main .g-2', 2]] },
    {
        path: '/preferences',
        grids: [
            ['#preferences-form', 2],
            ['#preferences-form .g-3', 3],
        ],
    },
    { path: '/database', grids: [['main .g-3', 3]] },
    { path: '/umamusume', grids: [['#catalog-results', 3]] },
    { path: '/support-cards', grids: [['main .g-2', 2]] },
    // The wizard's target step draws the HFW's two-column canvas (`career-target.html:94`), and it
    // was the one wizard step left out of this contract - which is why it kept two columns in
    // Compact and read as `standard` whatever the width preference said.
    { path: '/career/setup/target', grids: [['main .g-side', 2]] },
];

async function tracks(page: Page, selector: string): Promise<number> {
    const value = await page
        .locator(selector)
        .first()
        .evaluate((el) => getComputedStyle(el).gridTemplateColumns);

    return value.split(' ').filter((part) => part !== '').length;
}

async function assertCompositions(page: Page, expected: (declared: number) => number): Promise<void> {
    for (const pageDef of GRIDS) {
        await page.goto(pageDef.path);
        await page.locator('#app > *').first().waitFor();

        for (const [selector, declared] of pageDef.grids) {
            await expect
                .poll(() => tracks(page, selector), { message: `${pageDef.path} ${selector}` })
                .toBe(expected(declared));
        }
    }
}

test.describe('HFW layouts', () => {
    test.afterEach(async ({ page }) => {
        await resetLayout(page);
    });

    test('Standard keeps every HFW composition at 1280', async ({ page }) => {
        await page.setViewportSize({ width: 1280, height: 900 });
        await setLayout(page, 'standard');

        await page.goto('/');
        await page.locator('#app > *').first().waitFor();
        await expect(page.locator('[data-layout]').first()).toHaveAttribute('data-layout', 'standard');

        await assertCompositions(page, (declared) => declared);
    });

    test('Compact collapses every HFW grid to one column at 1280', async ({ page }) => {
        await page.setViewportSize({ width: 1280, height: 900 });
        await setLayout(page, 'compact');

        await page.goto('/');
        await page.locator('#app > *').first().waitFor();
        await expect(page.locator('[data-layout]').first()).toHaveAttribute('data-layout', 'compact');

        await assertCompositions(page, () => 1);
    });

    test('Wide keeps the composition and shares one measure at 1920', async ({ page }) => {
        await page.setViewportSize({ width: 1920, height: 1080 });

        const caps: ['compact' | 'standard' | 'wide', number][] = [
            ['compact', 768],
            ['standard', 1152],
            ['wide', 1536],
        ];

        for (const [mode, cap] of caps) {
            await setLayout(page, mode);
            await page.goto('/');
            await page.locator('#app > *').first().waitFor();

            await expect(page.locator('[data-layout]').first()).toHaveAttribute('data-layout', mode);

            const main = await page.locator('#main').boundingBox();
            const band = await page.locator('[data-shell-topbar] > div').first().boundingBox();

            expect(main).not.toBeNull();
            expect(band).not.toBeNull();
            expect(Math.abs((main?.width ?? 0) - cap), `${mode} measure`).toBeLessThanOrEqual(1);
            // One measure: the bar band and the canvas share a left edge.
            expect(Math.abs((band?.x ?? 0) - (main?.x ?? 0)), `${mode} band/canvas edge`).toBeLessThanOrEqual(1);
        }

        await assertCompositions(page, (declared) => declared);
    });

    test('no mode scrolls the document at 320, 390 or 768', async ({ page }) => {
        // `/support-cards` is excluded: its wide table overflows the document at 320px with or without
        // the area strip (measured with the strip removed), which is a pre-existing defect outside this
        // layout change. `/skills` was excluded for the same reason until 2026-10-30, when its table
        // moved inside a labelled scroll region and the exclusion was lifted.
        const paths = [
            '/',
            '/career/setup/scenario',
            '/preferences',
            '/database',
            '/training-runs',
            '/umamusume',
            '/skills',
        ];

        for (const mode of ['compact', 'standard', 'wide'] as const) {
            await setLayout(page, mode);

            for (const width of [320, 390, 768]) {
                await page.setViewportSize({ width, height: 844 });

                for (const path of paths) {
                    await page.goto(path);
                    await page.locator('#app > *').first().waitFor();

                    const overflow = await page.evaluate(
                        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
                    );
                    expect(overflow, `${path} ${mode} ${width}`).toBeLessThanOrEqual(1);
                }
            }
        }
    });
});
