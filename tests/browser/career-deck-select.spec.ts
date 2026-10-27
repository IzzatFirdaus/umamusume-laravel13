import { test, expect } from '@playwright/test';
import { mkdirSync, writeFileSync } from 'node:fs';
import { join, resolve } from 'node:path';
import { pathToFileURL } from 'node:url';
import { verifyPage } from './fixtures/wizard-verify';

/*
 * The support-deck step, against the HFW `career-deck.html` (slice 4b.4b).
 *
 * What this spec holds that the code cannot: the slot grid is two columns at desktop and one at 320px
 * (measured with `getBoundingClientRect`, not inferred from a class name), an empty slot is drawn dashed,
 * the Pal row states "No friendship bonus" in words rather than a blank chip, and the screen carries
 * exactly one filled action. The focus and dialog cases are unchanged: they pin behaviour the rebuild
 * does not touch.
 */

test.beforeEach(async ({ page }) => {
    await page.goto('/career/setup/deck', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
});

test('lays the six slots out as two columns, measured', async ({ page }) => {
    const grid = page.getByRole('list').filter({ has: page.getByRole('button', { name: /card for/i }) }).first();

    const measured = await grid.evaluate((el) => {
        const style = getComputedStyle(el);
        const children = Array.from(el.children) as HTMLElement[];
        // Column membership by left offset: `.card + .card` adds a 14px margin-top to every child after
        // the first, so equal `top` would count one slot per row even on a correct two-column grid.
        const lefts = children.map((child) => Math.round(child.getBoundingClientRect().left));

        return {
            display: style.display,
            columns: style.gridTemplateColumns.split(' ').filter(Boolean).length,
            tracks: new Set(lefts).size,
            children: children.length,
        };
    });

    expect(measured.display, 'the slot list is a grid').toBe('grid');
    expect(measured.children, 'six slots').toBe(6);
    expect(measured.columns, 'two tracks at desktop width').toBe(2);
    expect(measured.tracks, 'the six slots stand on two distinct columns').toBe(2);
});

test('shows the equipped count in the deck heading', async ({ page }) => {
    await expect(page.getByRole('heading', { name: /Deck\s+0 of 6 slots filled/i })).toBeVisible();
});

test('draws an empty slot as a dashed outline', async ({ page }) => {
    const slot = page.locator('#deck-slot-1');

    await expect(slot).toBeVisible();
    const border = await slot.evaluate((el) => getComputedStyle(el).borderTopStyle);

    expect(border, 'an empty slot states its absence with a dashed frame').toBe('dashed');
});

test('states the Pal rule in words, not a blank chip', async ({ page }) => {
    // The card-types table carries the rule on a deck that has no cards yet; the slot row repeats it
    // when a Pal card is actually equipped.
    await expect(page.getByText(/No friendship bonus/i).first()).toBeVisible();
});

test('prints the multiplier as multiplication, never as a sum', async ({ page }) => {
    await expect(page.getByText(/1\.25 × 1\.30 = 1\.625/)).toBeVisible();
    await expect(page.getByText(/\+55%/)).toBeVisible();
    // The "+55%" appears only in the sentence that rejects it.
    await expect(page.getByText(/not \+55%/)).toBeVisible();
});

test('names the duplicate rule on the page', async ({ page }) => {
    await expect(page.getByText(/Two identical cards cannot sit in one deck/i)).toBeVisible();
});

test('carries exactly one filled action: continue to preflight', async ({ page }) => {
    const filled = page.locator('.bg-primary-action');
    await expect(filled).toHaveCount(1);
    await expect(filled.first()).toHaveText(/Continue to preflight/i);
});

test('opens the picker as a modal dialog and closes it with Escape', async ({ page }) => {
    await page.getByRole('button', { name: /Choose a card for/i }).first().click();

    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    expect(await dialog.evaluate((el) => (el as HTMLDialogElement).matches(':modal'))).toBe(true);

    await page.keyboard.press('Escape');
    await expect(dialog).toBeHidden();
});

test('returns focus to the slot button that opened the picker', async ({ page }) => {
    // The opener is addressed by its own accessible name so the assertion can say WHICH button came
    // back: with six slots, "some button is focused" would pass even if focus landed on the wrong one.
    const opener = page.getByRole('button', { name: /Choose a card for/i }).first();
    const openerName = await opener.getAttribute('aria-label');

    await opener.click();
    await expect(page.getByRole('dialog')).toBeVisible();
    await page.keyboard.press('Escape');

    const focused = await page.evaluate(() => {
        const el = document.activeElement;

        return { tag: el?.tagName ?? null, label: el?.getAttribute('aria-label') ?? null };
    });

    expect(focused.tag, 'focus returned to a control, not the body').toBe('BUTTON');
    expect(focused.label, 'focus returned to the button that opened the dialog').toBe(openerName);
});

test('draws no analysis panel, no page-level save, no ownership prose (Phase E)', async ({ page }) => {
    // `career-deck.html:73-231` draws the slots, the diagnostics and the card types — no coverage
    // panel, no `Save deck` submit, no ownership-flag explanation. The panel lives on the run's
    // deck screen, the forward door writes before it leaves, and the flag's destination is the
    // builder's own copy.
    await expect(page.getByRole('heading', { name: /deck analysis/i })).toHaveCount(0);
    await expect(page.getByRole('button', { name: 'Save deck' })).toHaveCount(0);
    await expect(page.getByText(/Preflight writes it onto the run\s+as one value per slot/)).toHaveCount(0);
    await expect(page.getByText(/reads\s+as N\/A rather than as Owned/)).toHaveCount(0);
});

test('the diagnostics table carries five checks', async ({ page }) => {
    const diagnostics = page.locator('section[aria-labelledby="deck-diagnostics-heading"]');
    await expect(diagnostics.locator('tbody tr')).toHaveCount(5);
});

test('a slot row stays a card, not a document', async ({ page }) => {
    // Per-card ceiling: a slot is a card summary with two or three controls, not a panel that
    // swallowed the page. Measured on the slot's own descendants.
    const slot = page.locator('#deck-slot-1');
    expect(await slot.locator('*').count(), 'slot descendants under the 25-per-card ceiling').toBeLessThan(25);
    const text = (await slot.innerText()).trim();
    expect(text.length, 'slot text under the 900-character ceiling').toBeLessThan(900);
});

test('three-diff against career-deck.html writes the phase evidence', async ({ page }) => {
    // Phase E evidence: text (excess/missing), computed style and bounding rect against the HFW.
    //
    // HFW-OVERRIDE (markup: element paths) — `excess`/`missing` are the helper's structural-path
    // buckets, not content: the shell wraps the step bar in `<nav class="mb-4">` and the HFW draws a
    // bare `div.stepper`, so every descendant path below it shifts and lands in both buckets (4543
    // excess / 156 missing at both viewports, measured 2026-10-28). `career-deck.html` also draws
    // mock affordances this build does not have a source for (`Suggest medium front-runner`, a
    // `Save deck`-shaped footer row) and mock veteran cards the scratch database does not hold. The
    // content-level buckets are the gate and they are clean: text 0, computed 0, rect 0, asserted
    // below for the head band Phase A.2 rewrote and recorded in the JSON for the rest.
    const hfw = pathToFileURL(
        resolve(process.cwd(), 'docs/design-research/frontend-v2-wireframes/career-deck.html'),
    ).href;

    const dir = resolve(process.cwd(), '.scratch-uma/wizard-parity');
    mkdirSync(dir, { recursive: true });

    for (const [name, viewport] of [['1280', { width: 1280, height: 800 }], ['390', { width: 390, height: 844 }]] as const) {
        const at = await verifyPage(page, {
            appUrl: '/career/setup/deck',
            hfwUrl: hfw,
            rootSelector: '.canvas',
            ...viewport,
        });

        writeFileSync(join(dir, `deck-diff-${name}.json`), `${JSON.stringify(at, null, 2)}\n`);
    }

    const report = await verifyPage(page, {
        appUrl: '/career/setup/deck',
        hfwUrl: hfw,
        rootSelector: '.canvas',
    });

    // The content-level gate: no text, computed-style or geometry drift on this page.
    expect(report.text, `text drift: ${JSON.stringify(report.text)}`).toEqual([]);
    expect(report.computed, `computed drift: ${JSON.stringify(report.computed)}`).toEqual([]);
    expect(report.rect, `rect drift: ${JSON.stringify(report.rect)}`).toEqual([]);
});

test('keeps the picker open across a filter', async ({ page }) => {
    await page.getByRole('button', { name: /Choose a card for/i }).first().click();

    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();

    await dialog.getByLabel('Name contains').fill('Teio');
    await dialog.getByRole('button', { name: 'Filter' }).click();

    await expect(dialog).toBeVisible();
    await expect(page).toHaveURL(/q=Teio/);
});

test('finds a card by its printed name and epithet', async ({ page }) => {
    await page.getByRole('button', { name: /Choose a card for/i }).first().click();

    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();

    await dialog.getByLabel('Name contains').fill('Tokai Teio [Dream Big!]');
    await dialog.getByRole('button', { name: 'Filter' }).click();

    await expect(dialog.getByText('Tokai Teio [Dream Big!]', { exact: true })).toBeVisible();
});

test('stores the per-run card level and reads it back after a reload', async ({ page }) => {
    await page.getByRole('button', { name: 'Choose a card for Slot 1' }).click();
    const dialog = page.getByRole('dialog');
    await expect(dialog).toBeVisible();
    await dialog.getByRole('button', { name: 'Equip', exact: true }).first().click();
    await expect(page.getByText('Deck saved.')).toBeVisible();

    const level = page.getByLabel('Level for Slot 1');
    await expect(level).toBeVisible();
    await level.fill('35');

    // The per-slot level field has no writer of its own (Phase E cut the page-level `Save deck`);
    // the forward door writes the deck before it navigates. The draft keeps the value, so the
    // read-back happens on this step after a fresh arrival.
    await Promise.all([
        page.waitForResponse(
            (response) => response.url().includes('/career/setup/deck') && response.request().method() === 'PUT',
        ),
        page.getByRole('button', { name: 'Continue to preflight' }).click(),
    ]);
    await expect(page).toHaveURL(/\/career\/setup\/preflight/);

    await page.goto('/career/setup/deck', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByLabel('Level for Slot 1')).toHaveValue('35');
});
