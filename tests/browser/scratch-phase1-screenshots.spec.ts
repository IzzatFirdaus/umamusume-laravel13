import { test, expect } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

async function createRun(page: any): Promise<string> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Agnes Digital');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/);
    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);
    return runUrl;
}

async function screenshot(page: any, name: string, width: number) {
    await page.setViewportSize({ width, height: 900 });
    await page.screenshot({ path: `.scratch-uma/phase1-dispatch-screens/${name}-${width}.png`, fullPage: true });
}

test.describe('Phase 1 dispatch screenshots', () => {
    test('skills detail at 390 and 1280', async ({ page }) => {
        await page.goto('/skills?search=lookatcurren');
        await page.locator('#app > *').first().waitFor();
        await page.locator('#skill-results').getByRole('link', { name: '#LookatCurren' }).click();
        await expect(page).toHaveURL(/\/skills\/\d+$/);
        await page.locator('#app > *').first().waitFor();
        await screenshot(page, 'skills-detail', 390);
        await screenshot(page, 'skills-detail', 1280);
    });

    test('skills planner at 390 and 1280', async ({ page }) => {
        const runUrl = await createRun(page);
        await page.goto(`${runUrl}/skills`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();
        await screenshot(page, 'skills-planner', 390);
        await screenshot(page, 'skills-planner', 1280);
    });

    test('dashboard at 390 and 1280', async ({ page }) => {
        await page.goto('/', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();
        await screenshot(page, 'dashboard', 390);
        await screenshot(page, 'dashboard', 1280);
    });

    test('legacy step at 390 and 1280', async ({ page }) => {
        const runUrl = await createRun(page);
        await page.goto(`${runUrl}/setup/legacy`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();
        await screenshot(page, 'legacy-step', 390);
        await screenshot(page, 'legacy-step', 1280);
    });
});
