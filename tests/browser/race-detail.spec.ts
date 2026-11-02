import { test, expect } from '@playwright/test';
import { seedRaceEnrichment, clearRaceEnrichment, type EnrichedRaceIds } from '../utils/race-slots';

/*
 * Rendered evidence for `/database/races/{id}`, the race catalogue's detail page (the enriched
 * half of the race-enrichment landing). The server and parser tests own the payload; these own
 * what only a browser shows: the populated row, the null-column absence notes, the reward-id
 * disclosure, and a focus ring a keyboard Trainer can see.
 *
 * Fixtures are three seeded rows from the race catalogue: 46 (track + rewards populated), 3
 * (track populated, rewards null) and 1 (a special calendar race with no enriched join at all).
 */

const READ = { timeout: 30_000 } as const;

let ids: EnrichedRaceIds;

test.beforeAll(() => {
    ids = seedRaceEnrichment();
});

test.afterAll(() => {
    if (ids !== undefined) {
        clearRaceEnrichment(ids);
    }
});

async function hydrate(page: import('@playwright/test').Page, url: string): Promise<number | null> {
    const response = await page.goto(url, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    return response?.status() ?? null;
}

test('answers 200 and renders the enriched race', async ({ page }) => {
    expect(await hydrate(page, `/database/races/${ids.populated}`)).toBe(200);

    await expect(page.getByRole('heading', { name: 'Asahi Hai Futurity Stakes', level: 1 })).toBeVisible();

    const conditions = page.locator('.card').filter({ has: page.getByRole('heading', { name: 'Track and conditions' }) });
    await expect(conditions.getByRole('row', { name: /Track name\s+Hanshin/ })).toBeVisible();
    await expect(conditions.getByRole('row', { name: /Direction\s+Right-Handed/ })).toBeVisible();
    await expect(conditions.getByRole('row', { name: /Season\s+Winter \(early\)/ })).toBeVisible();
    await expect(conditions.getByRole('row', { name: /Entries\s+18/ })).toBeVisible();
    await expect(conditions.getByRole('row', { name: /Factor effects\s+Speed, Guts/ })).toBeVisible();

    // A source code with no label is disclosed as a code, never printed bare.
    await expect(conditions.getByRole('row', { name: /Course\s+Code 3/ })).toBeVisible();

    const rewards = page.locator('.card').filter({ has: page.getByRole('heading', { name: 'Rewards' }) });
    await expect(rewards.getByRole('columnheader', { name: 'Item id' })).toBeVisible();
    await expect(rewards.getByRole('cell', { name: '100%' }).first()).toBeVisible();
    await expect(rewards.getByText(/Rewards are stored by the source's internal item id/)).toBeVisible();

    // No raw reward JSON reaches the facts table; the card above is the only reward surface.
    await expect(page.getByText(/"num_min"/)).toHaveCount(0);
});

test('renders a partial race with an absence note for the column the source lacks', async ({ page }) => {
    expect(await hydrate(page, `/database/races/${ids.partial}`)).toBe(200);

    await expect(page.getByRole('heading', { name: 'Chukyo Junior Stakes', level: 1 })).toBeVisible();

    const conditions = page.locator('.card').filter({ has: page.getByRole('heading', { name: 'Track and conditions' }) });
    await expect(conditions.getByRole('row', { name: /Track name\s+Chukyo/ })).toBeVisible();
    await expect(conditions.getByRole('row', { name: /Entries\s+16/ })).toBeVisible();
    // The absence is a disclosure: the summary states N/A and names the reason, and opening it
    // prints the same sentence beside the value it annotates.
    const factorAbsence = conditions.locator('summary').filter({ hasText: 'No factor effects recorded for this slot.' });
    await expect(factorAbsence).toBeVisible();
    await factorAbsence.click();
    await expect(conditions.getByText('No factor effects recorded for this slot.', { exact: true })).toBeVisible();

    // Rewards are null on this row: the absence is stated and the id disclosure is not drawn.
    const rewards = page.locator('.card').filter({ has: page.getByRole('heading', { name: 'Rewards' }) });
    const rewardAbsence = rewards.locator('summary').filter({ hasText: 'No reward data recorded for this slot.' });
    await expect(rewardAbsence).toBeVisible();
    await rewardAbsence.click();
    await expect(rewards.getByText('No reward data recorded for this slot.', { exact: true })).toBeVisible();
    await expect(page.getByText(/Rewards are stored by the source's internal item id/)).toHaveCount(0);

    // Null never becomes a dash.
    await expect(page.getByRole('cell', { name: '-', exact: true })).toHaveCount(0);
    await expect(page.getByRole('cell', { name: '—', exact: true })).toHaveCount(0);
});

test('renders the empty join as absence notes rather than a crash', async ({ page }) => {
    expect(await hydrate(page, `/database/races/${ids.empty}`)).toBe(200);

    await expect(page.getByRole('heading', { name: 'Junior Make Debut', level: 1 })).toBeVisible();

    const conditions = page.locator('.card').filter({ has: page.getByRole('heading', { name: 'Track and conditions' }) });
    const reasons = [
        'No track name recorded for this slot.',
        'No direction recorded for this slot.',
        'No entry count recorded for this slot.',
        'No factor effects recorded for this slot.',
    ];

    for (const reason of reasons) {
        await expect(conditions.locator('summary').filter({ hasText: reason })).toBeVisible();
    }

    // Opening one states the same sentence the summary named.
    await conditions.locator('summary').filter({ hasText: reasons[0] }).click();
    await expect(conditions.getByText(reasons[0], { exact: true })).toBeVisible();

    // The page still answers every region; a failed join does not remove the screen.
    for (const heading of ['Identity', 'Rewards', 'Fan and payout', 'Flags', 'Calendar position', 'Race facts']) {
        await expect(page.getByRole('heading', { name: heading, level: 2 })).toBeVisible(READ);
    }
});

test('keeps a visible focus ring on the source-ledger door', async ({ page }) => {
    await hydrate(page, `/database/races/${ids.populated}`);

    // Keyboard modality, then focus the one door off the page.
    await page.keyboard.press('Tab');
    const door = page.getByRole('link', { name: 'Where these datasets come from' });
    await door.focus();
    await expect(door).toBeFocused();

    const outline = await door.evaluate((el) => getComputedStyle(el).outlineWidth);
    expect(parseFloat(outline), 'the focused door paints no ring').toBeGreaterThan(0);
});