import { test, expect } from '@playwright/test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * IME-safe Enter in the three unguarded pickers (plan 5.2, MWG `ime-safe-enter-submit`). In-spec
 * Vite fixture: no `public/build`, no `public/hot` (KI-70).
 *
 * Trainer Desk stores and searches Japanese names, so an Enter handler that submits on `keydown`
 * without checking `isComposing` sends a half-composed string. Each case dispatches a composing
 * Enter and asserts the picker did NOT commit, then a plain Enter and asserts it DID - guarding is
 * additive, so a guard that swallowed every Enter would red the second half.
 *
 * `ui/Combobox.vue` carries its own fixture and its own two cases in `combobox.spec.ts`.
 */
let server: ViteDevServer;
let base: string;

const fixtures = path.resolve(path.dirname(fileURLToPath(import.meta.url)), 'fixtures');

test.beforeAll(async () => {
    server = await createServer({
        configFile: false,
        root: fixtures,
        plugins: [vue()],
        logLevel: 'silent',
        appType: 'mpa',
        server: { host: '127.0.0.1', port: 5213, strictPort: false, watch: null, hmr: false },
    });
    await server.listen();
    base = server.resolvedUrls!.local[0]!;
});

test.afterAll(async () => {
    await server.close();
});

async function open(page: import('@playwright/test').Page): Promise<void> {
    await page.goto(`${base}ime-enter.html`);
    await page.locator('#fixture-main').waitFor();
}

/**
 * Put the first option under the cursor the way a keyboard user reaches it. Typing alone leaves no
 * cursor on some of these pickers (`SearchableSkillPicker` opens with `highlightedIndex = -1`), so
 * the Down press is what makes the following Enter a selection rather than a no-op.
 */
async function activateFirstOption(
    page: import('@playwright/test').Page,
    locator: import('@playwright/test').Locator,
    query: string,
): Promise<void> {
    await locator.focus();
    await locator.fill(query);
    await expect(locator).toHaveAttribute('aria-expanded', 'true');
    // A cursor parked on a real row is what makes the following Enter a selection rather than a
    // submit. `TraineeCombobox` only parks one when the query matched, so the callers pass a query
    // their own fixture data matches.
    await expect(locator).not.toHaveAttribute('aria-activedescendant', '');
    await page.keyboard.press('ArrowDown');
}

test('SearchableSkillPicker: a composing Enter does not select, a plain Enter does', async ({ page }) => {
    await open(page);

    const input = page.getByPlaceholder('Search skills');
    await activateFirstOption(page, input, 'Aim');

    await input.dispatchEvent('keydown', { key: 'Enter', isComposing: true });
    await expect(page.locator('#skill-chosen')).toHaveText('skill:none');

    await page.keyboard.press('Enter');
    await expect(page.locator('#skill-chosen')).not.toHaveText('skill:none');
});

test('SparkTargetPicker: a composing Enter does not select, a plain Enter does', async ({ page }) => {
    await open(page);

    const input = page.locator('#spark-target');
    await activateFirstOption(page, input, 'St');

    await input.dispatchEvent('keydown', { key: 'Enter', isComposing: true });
    await expect(page.locator('#spark-chosen')).toHaveText('spark:none');

    await page.keyboard.press('Enter');
    await expect(page.locator('#spark-chosen')).not.toHaveText('spark:none');
});

test('TraineeCombobox: a composing Enter does not choose, a plain Enter does', async ({ page }) => {
    await open(page);

    const input = page.locator('#trainee-combobox');
    await activateFirstOption(page, input, 'Vod');

    await input.dispatchEvent('keydown', { key: 'Enter', isComposing: true });
    await expect(page.locator('#trainee-chosen')).toHaveText('trainee:none');

    await page.keyboard.press('Enter');
    await expect(page.locator('#trainee-chosen')).not.toHaveText('trainee:none');
});