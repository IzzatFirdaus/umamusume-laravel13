import { test, expect } from '@playwright/test';

/*
 * Help (help.html, plan Slice 11.2). Rendered-copy evidence for the four questions the page answers
 * and the two rules it holds itself to: no link that goes nowhere, and no control described as if the
 * build had it.
 */

const ROUTES = ['/', '/training-runs', '/training-runs/import', '/database/sources', '/help'];

test.beforeEach(async ({ page }) => {
    await page.goto('/help', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
});

test('the page answers the four questions in the wireframe order', async ({ page }) => {
    await expect(page.getByRole('heading', { name: 'How this works', level: 1 })).toBeVisible();

    for (const name of ['The turn loop', 'Keyboard', 'What the tags mean', 'Where the numbers come from', 'Rules that are easier to look up', 'If something looks wrong']) {
        await expect(page.getByRole('heading', { name, level: 2 })).toBeVisible();
    }

    // Proactive help leads: the turn loop is the first region after the page head. The selector names
    // the role rather than the element, because the HFW's cards are `div.card` blocks and a `section`
    // selector would only ever match the app's old markup.
    const regions = await page.locator('main [role="region"][aria-labelledby]').evaluateAll((nodes) =>
        nodes.map((n) => n.getAttribute('aria-labelledby')),
    );
    expect(regions[0]).toBe('help-loop');
});

test('the trust vocabulary names the three exceptions and states that an unmarked figure is confirmed', async ({ page }) => {
    const tags = page.getByRole('region', { name: 'What the tags mean' });
    await expect(tags.getByRole('row').filter({ hasText: 'Calculated' })).toBeVisible();
    await expect(tags.getByRole('row').filter({ hasText: 'Estimated' })).toBeVisible();
    await expect(tags.getByRole('row').filter({ hasText: 'Unknown' })).toBeVisible();

    await expect(tags.getByText('An unmarked figure is confirmed')).toBeVisible();

    // Each level renders through its own shipped `.trust-*` primitive, not the flat `.tag`: the
    // three are visually distinct (colour and border), which is the whole point of the legend
    // (`help.html:106-108`). A single shared class would make the three rows indistinguishable.
    await expect(tags.locator('.trust.trust-calculated')).toHaveCount(1);
    await expect(tags.locator('.trust.trust-estimated')).toHaveCount(1);
    await expect(tags.locator('.trust.trust-unknown')).toHaveCount(1);
    await expect(tags.locator('.tag')).toHaveCount(0);

    // "Confirmed" is a state, not a chip: the page must not print it as a fourth mark.
    await expect(tags.getByRole('row').filter({ hasText: /^\s*✓?\s*Confirmed/ })).toHaveCount(0);
});

test('undo is described as a gap and no inert control is drawn', async ({ page }) => {
    const loop = page.getByRole('region', { name: 'The turn loop' });
    await expect(loop.getByText('there is no Undo last turn control')).toBeVisible();
    await expect(page.getByRole('button', { name: /Undo/ })).toHaveCount(0);
});

test('explains CSV turn history without prototype promises', async ({ page }) => {
    const diagnostic = page.getByRole('region', { name: 'If something looks wrong' });
    await expect(diagnostic.getByRole('row').filter({ hasText: 'A turn count disagrees' })).toContainText(
        'A CSV import saves only the turn rows you supply. Check their turn numbers in the preview before importing.',
    );
    await expect(page.getByText('position, not a history')).toHaveCount(0);
    await expect(page.getByText('Each row is a link in a built product')).toHaveCount(0);
    await expect(page.getByText('Every figure on a screen points at a source')).toHaveCount(0);
});

test('every link on the page reaches a route this app serves', async ({ page }) => {
    const hrefs = await page.locator('main a[href]').evaluateAll((nodes) =>
        nodes.map((n) => n.getAttribute('href')),
    );

    expect(hrefs.length).toBeGreaterThan(0);

    for (const href of hrefs) {
        if (href === null || !href.startsWith('/')) {
            continue;
        }

        expect(ROUTES, `${href} is not a route Help may link`).toContain(href);
        const response = await page.request.get(href);
        expect(response.status(), `${href} did not answer`).toBe(200);
    }
});

test('the wireframe apparatus is absent and the console stays clean', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error' || message.type() === 'warning') errors.push(message.text());
    });
    page.on('pageerror', (error) => errors.push(error.message));
    await page.reload({ waitUntil: 'networkidle' });
    await expect(page.getByRole('heading', { name: 'How this works', level: 1 })).toBeVisible();

    await expect(page.getByText('Annotation key')).toHaveCount(0);
    await expect(page.getByText('Static wireframe')).toHaveCount(0);
    await expect(page.getByRole('table').filter({ hasText: 'Table of contents' })).toHaveCount(0);

    expect(errors).toEqual([]);
});
