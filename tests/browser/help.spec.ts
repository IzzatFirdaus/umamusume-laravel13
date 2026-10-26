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

    // Proactive help leads: the turn loop is the first region after the page head.
    const regions = await page.locator('main section[aria-labelledby]').evaluateAll((nodes) =>
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

    // "Confirmed" is a state, not a chip: the page must not print it as a fourth mark.
    await expect(tags.getByRole('row').filter({ hasText: /^\s*✓?\s*Confirmed/ })).toHaveCount(0);
});

test('undo is described as a gap and no inert control is drawn', async ({ page }) => {
    const loop = page.getByRole('region', { name: 'The turn loop' });
    await expect(loop.getByText('there is no Undo last turn control')).toBeVisible();
    await expect(page.getByRole('button', { name: /Undo/ })).toHaveCount(0);
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
        if (message.type() === 'error') errors.push(message.text());
    });

    await expect(page.getByText('Annotation key')).toHaveCount(0);
    await expect(page.getByText('Static wireframe')).toHaveCount(0);
    await expect(page.getByRole('table').filter({ hasText: 'Table of contents' })).toHaveCount(0);

    expect(errors).toEqual([]);
});
