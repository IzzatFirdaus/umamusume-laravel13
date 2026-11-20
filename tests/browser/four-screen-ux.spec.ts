import { expect, test } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { fileCareer } from '../utils/file-career';
import { removeRaceSlots, seedRaceSlots, type SeededRaceSlot } from '../utils/race-slots';
import { clearPendingCandidates, seedPendingCandidates } from '../utils/review-candidates';

const routes = ['/review', '/skills', '/veterans', '/database/races'] as const;
const themes = ['light', 'dark', 'eva01'] as const;

let filedRun: string | undefined;
let raceSlots: SeededRaceSlot[] = [];

test.afterEach(async ({ page }) => {
    clearPendingCandidates();
    removeRaceSlots(raceSlots);
    raceSlots = [];

    if (filedRun !== undefined) {
        await deleteRun(page, filedRun);
        filedRun = undefined;
    }
});

for (const route of routes) {
    test(`${route} has readable tables and reachable controls in all three themes`, async ({ page }) => {
        if (route === '/review') {
            seedPendingCandidates(2, { suggested: true });
            seedPendingCandidates(2);
        }
        if (route === '/veterans') {
            filedRun = await fileCareer(page, 'Daiwa Scarlet', null);
        }
        if (route === '/database/races') {
            raceSlots = seedRaceSlots();
        }

        const pageErrors: string[] = [];
        page.on('pageerror', error => pageErrors.push(error.message));
        await page.goto(route);

        const main = page.getByRole('main');
        const table = main.locator('[role="region"][tabindex="0"]').filter({ has: page.locator('table') }).first();
        await expect(table.getByRole('rowgroup').last().getByRole('row').first()).toBeVisible();
        await expect(page.getByRole('heading', { level: 1 })).toHaveCount(1);

        for (const viewportWidth of [1280, 320]) {
            await page.setViewportSize({ width: viewportWidth, height: 900 });

            for (const theme of themes) {
                await test.step(`${theme} at ${viewportWidth}px`, async () => {
                    // The persisted preference is covered by the theme gate. Exercise each palette
                    // against the same rendered controls and scratch data here.
                    await page.locator('html').evaluate((root, value) => root.setAttribute('data-theme', value), theme);
                    await table.focus();
                    await page.keyboard.press('Tab');

                    const focus = await table.evaluate(region => {
                        const active = document.activeElement;
                        if (!(active instanceof HTMLElement) || !region.contains(active)) {
                            return null;
                        }

                        const style = getComputedStyle(active);
                        const bounds = active.getBoundingClientRect();
                        const hit = document.elementFromPoint(
                            bounds.left + bounds.width / 2,
                            bounds.top + bounds.height / 2,
                        );

                        return {
                            visible: hit !== null && active.contains(hit),
                            ring: style.outlineStyle,
                            width: parseFloat(style.outlineWidth),
                        };
                    });

                    expect(focus).toMatchObject({ visible: true, ring: 'solid' });
                    expect(focus?.width).toBeGreaterThanOrEqual(2);
                    expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(viewportWidth);

                    const { violations } = await buildAxe(page).analyze();
                    expect(violations.map(item => ({
                        id: item.id,
                        nodes: item.nodes.map(node => ({ target: node.target, summary: node.failureSummary })),
                    }))).toEqual([]);
                });
            }
        }

        expect(pageErrors).toEqual([]);
    });
}
