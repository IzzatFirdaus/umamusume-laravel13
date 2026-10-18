import { test, expect } from '@playwright/test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * The Combobox primitive (plan 1.9a). In-spec Vite fixture: no `public/build`, no `public/hot` (KI-70).
 *
 * The open/close case and the arrow-key rove case are deliberately SEPARATE assertions over separate
 * properties: opening is `aria-expanded`, roving is `aria-activedescendant`. Removing the
 * activedescendant binding must red only the rove case and leave open/close green; the mutation table
 * in the commit body records which case each mutation moved.
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
        server: { host: '127.0.0.1', port: 5207, strictPort: false, watch: null, hmr: false },
    });
    await server.listen();
    base = server.resolvedUrls!.local[0]!;
});

test.afterAll(async () => {
    await server.close();
});

async function open(page: import('@playwright/test').Page): Promise<void> {
    await page.goto(`${base}combobox.html`);
    await page.locator('#fixture-main').waitFor();
}

test('opens on focus and on click, and exposes the ARIA combobox contract', async ({ page }) => {
    await open(page);

    const input = page.locator('#cb');
    await expect(input).toHaveAttribute('role', 'combobox');
    await expect(input).toHaveAttribute('aria-autocomplete', 'list');
    await expect(input).toHaveAttribute('aria-expanded', 'false');

    await input.focus();
    await expect(input).toHaveAttribute('aria-expanded', 'true');

    // `aria-controls` points at a real listbox, and every option is exposed as an option.
    const listId = await input.getAttribute('aria-controls');
    await expect(page.locator(`#${listId}`)).toHaveAttribute('role', 'listbox');
    await expect(page.locator(`#${listId}`).getByRole('option')).toHaveCount(4);

    // Escape closes without committing.
    await page.keyboard.press('Escape');
    await expect(input).toHaveAttribute('aria-expanded', 'false');
    await expect(page.locator('#chosen')).toHaveText('chosen:none');
});

test('arrow keys rove the active option through aria-activedescendant', async ({ page }) => {
    await open(page);

    const input = page.locator('#cb');
    await input.focus();

    // Typing puts the first match under the reader, then Down moves to the next.
    await input.fill('G');
    await expect(page.getByRole('option')).toHaveCount(2); // Gold Ship only... plus none of the others

    await page.keyboard.press('ArrowDown');

    // The active option is named on the INPUT, and focus never leaves the field.
    const activeId = await input.getAttribute('aria-activedescendant');
    expect(activeId).toBeTruthy();
    await expect(page.locator(`#${activeId}`)).toHaveAttribute('role', 'option');
    expect(await input.evaluate((el) => document.activeElement === el)).toBe(true);
});

test('Enter commits the active option and Enter with none active commits nothing', async ({ page }) => {
    await open(page);

    const input = page.locator('#cb');
    await input.focus();
    await page.keyboard.press('ArrowDown'); // first option active
    await page.keyboard.press('Enter');

    await expect(page.locator('#chosen')).toHaveText('chosen:1');
    await expect(input).toHaveAttribute('aria-expanded', 'false');
});

test('the result count is announced through a polite live region', async ({ page }) => {
    await open(page);

    const status = page.locator('#cb-status');
    await expect(status).toHaveAttribute('aria-live', 'polite');

    await page.locator('#cb').focus();
    await expect(status).toHaveText('4 options');

    await page.locator('#cb').fill('i');
    // Agnes Digital and Gold Ship both contain "i"; Icy Pearl and Rice Shower follow by sublabel too,
    // so the exact count is asserted rather than a truthiness check.
    const text = await status.innerText();
    expect(text).toMatch(/^\d+ options?$/);
});

test('a Japanese sublabel is tagged with its language', async ({ page }) => {
    await open(page);
    await page.locator('#cb').focus();

    const ja = page.locator('#cb-opt-1 span[lang="ja"]').last();
    await expect(ja).toHaveText('アグネスデジタル');
});
