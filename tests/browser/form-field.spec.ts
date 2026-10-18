import { test, expect } from '@playwright/test';
import { createServer, type ViteDevServer } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * The FormField family (plan 1.8). In-spec Vite fixture: no `public/build`, no `public/hot` (KI-70).
 *
 * Two of these cases are the ones that must stay distinguishable under mutation, because they name
 * opposite behaviour on near-identical markup:
 *   - a REQUIRED select reads aria-required="true"
 *   - an OPTIONAL select reads aria-required="false"
 * The two selects differ only in `required`, so a mutation of the aria-required chain must red one
 * and leave the other green. If both go red the assertion is too coarse and gets tightened.
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
        server: { host: '127.0.0.1', port: 5206, strictPort: false, watch: null, hmr: false },
    });
    await server.listen();
    base = server.resolvedUrls!.local[0]!;
});

test.afterAll(async () => {
    await server.close();
});

async function open(page: import('@playwright/test').Page): Promise<void> {
    await page.goto(`${base}form-field.html`);
    await page.locator('#fixture-main').waitFor();
}

test('FormField labels its control through for and reads its hint through aria-describedby', async ({ page }) => {
    await open(page);

    const input = page.locator('#ff-text');
    const label = page.locator('label[for="ff-text"]');

    await expect(label).toHaveText('Trainee');
    await expect(input).toBeVisible();

    // Clicking the label focuses the control: the association is real, not just a visual adjacency.
    await label.click();
    expect(await input.evaluate((el) => document.activeElement === el)).toBe(true);

    // The hint is wired to the control, so a screen reader reads the help with the field.
    const describedBy = await input.getAttribute('aria-describedby');
    expect(describedBy).toBeTruthy();
    await expect(page.locator(`#${describedBy}`)).toHaveText('The form you trained.');
});

test('an error renders with role="alert" and joins the control’s aria-describedby', async ({ page }) => {
    await open(page);

    const input = page.locator('#ff-error');
    const error = page.locator('#ff-error-error');

    await expect(error).toHaveAttribute('role', 'alert');
    await expect(error).toHaveText('Pick a scenario.');

    // The refusal is read with the control, not discovered elsewhere on the page.
    await expect(input).toHaveAttribute('aria-describedby', 'ff-error-error');
    await expect(input).toHaveAttribute('aria-invalid', 'true');

    // And a field with no error never claims to be invalid.
    await expect(page.locator('#ff-text')).not.toHaveAttribute('aria-invalid');
});

test('"(optional)" marks only the fields that are not required', async ({ page }) => {
    await open(page);

    // Required is the default and carries no marker at all.
    await expect(page.locator('label[for="ff-text"]')).toHaveText('Trainee');
    await expect(page.locator('label[for="ff-text"]')).not.toContainText('optional');

    // Only the optional field is named as such.
    await expect(page.locator('label[for="ff-optional"]')).toContainText('(optional)');
});

test('a required select reads aria-required="true"', async ({ page }) => {
    await open(page);

    const select = page.locator('#select-required');
    await expect(select).toHaveAttribute('aria-required', 'true');
    await expect(select).toHaveAttribute('required', '');
});

test('an optional select reads aria-required="false", not nothing', async ({ page }) => {
    await open(page);

    // Vue casts an absent Boolean prop to `false`, and BaseSelect renders it explicitly. This is the
    // behaviour c2de34b established; the case asserts the rendered attribute rather than the intent.
    const select = page.locator('#select-optional');
    await expect(select).toHaveAttribute('aria-required', 'false');
    await expect(select).not.toHaveAttribute('required');
});

test('SegmentedControl is a fieldset of real radios, selected by fill and a check', async ({ page }) => {
    await open(page);

    const group = page.getByRole('group', { name: 'Purpose' });
    await expect(group).toBeVisible();

    const radios = group.getByRole('radio');
    await expect(radios).toHaveCount(3);

    // The selections are genuine radio inputs, so the browser owns the roving focus and the
    // "3 of 3" announcement; arrow keys move within the group for free.
    await expect(group.getByRole('radio', { name: 'Speed' })).toBeChecked();

    // The selected segment carries a check glyph beside its fill: the state is not colour alone.
    const speedLabel = page.locator('label[for$="-speed"]');
    await expect(speedLabel).toContainText('✓');

    // Selecting another radio moves the check and leaves exactly one.
    await group.getByRole('radio', { name: 'Power' }).check();
    await expect(group.getByRole('radio', { name: 'Power' })).toBeChecked();
    await expect(page.locator('label[for$="-power"]')).toContainText('✓');
    await expect(page.locator('label[for$="-speed"]')).not.toContainText('✓');
});
