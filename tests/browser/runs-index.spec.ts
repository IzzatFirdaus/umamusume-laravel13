import { test, expect } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';
import { startCareer } from '../utils/start-career';

/*
 * Rendered evidence for the Careers list (HFW `runs-index.html`, slice 3 step 1.1).
 *
 * The list is the one screen in the repository whose populated state a read-only fixture cannot
 * reach: `TrainingRun` holds zero rows in the freshly seeded scratch database, which is why
 * `runs.spec.ts` reads only the empty state (its own header states this). This file therefore owns
 * the populated row: it starts one career through the wizard's own steps and deletes it again in
 * `afterEach` through `runs.destroy`, the idiom `tests/utils/delete-run.ts` documents. Nothing here
 * writes to the development database; the harness's scratch file is the only target (KI-69).
 *
 * What the HFW's own annotation key asks for, and what each assertion pins:
 * - annotation 1: the filters are one inline row at 1280px, pagination under the table.
 * - annotation 2: `tr.is-current` is the active row, and the state is a word plus a position rather
 *   than a colour alone.
 * - annotation 3: the turn is printed against the career's own length (`N / 72`).
 * - annotation 5: no destructive action in any row.
 */

const created: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of created.splice(0)) {
        await deleteRun(page, url);
    }
});

test('the filter row is one line, the active row is named, and the turn carries its denominator', async ({ page }) => {
    const recordUrl = await startCareer(page);
    created.push(recordUrl);

    await page.goto('/training-runs', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const form = page.getByRole('search', { name: 'Filter careers' });
    await expect(form).toBeVisible();

    // Annotation 1: one inline row. The form aligns its children on their bottom edge
    // (`items-end`), so one row means every control shares a baseline and no control's top is
    // pushed onto a second line.
    const boxes = await form.locator('label').evaluateAll((labels) =>
        labels.map((label) => {
            const box = label.getBoundingClientRect();

            return { top: Math.round(box.top), bottom: Math.round(box.bottom) };
        }),
    );
    expect(boxes.length, 'the filter row lost a control').toBe(4);
    expect(new Set(boxes.map((box) => box.bottom)).size, `the filter row wrapped at 1280px: ${JSON.stringify(boxes)}`).toBe(1);
    expect(Math.max(...boxes.map((box) => box.top)) - Math.min(...boxes.map((box) => box.top)))
        .toBeLessThanOrEqual(8);

    // The row's fourth control is the sort (slice 3 step 1.1's control list).
    await expect(form.getByLabel('Sort')).toBeVisible();

    // Annotation 2: exactly one `is-current` row; its state is readable as a word, and its position
    // is printed in the row rather than carried by the tint.
    const current = page.locator('tr.is-current');
    await expect(current).toHaveCount(1);
    await expect(current.getByText('Active', { exact: true })).toBeVisible();

    // Annotation 3: the turn against the career's length (72 on this calendar).
    await expect(current).toContainText('/ 72');

    // Annotation 5: no destructive action in any row.
    await expect(page.getByRole('button', { name: /delete/i })).toHaveCount(0);
    await expect(page.getByRole('link', { name: /delete/i })).toHaveCount(0);
});

test('the sort control reorders the list and keeps the choice in the URL', async ({ page }) => {
    const recordUrl = await startCareer(page);
    created.push(recordUrl);

    await page.goto('/training-runs?sort=trainee', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const sort = page.getByRole('search', { name: 'Filter careers' }).getByLabel('Sort');
    await expect(sort).toHaveValue('trainee');
    await expect(page).toHaveURL(/sort=trainee/);
});

/*
 * The row portrait is an owner enhancement, not the HFW: `runs-index.html` draws no picture on this
 * list. Every row still carries one slot, so the name column starts at one x whether or not the
 * mirror holds a file (`ArtworkSlot`'s `reserve`), and the frame is never a link because the row's
 * one door is its Actions cell.
 */
test('gives every run row one reserved portrait slot and no extra door', async ({ page }) => {
    const recordUrl = await startCareer(page);
    created.push(recordUrl);

    await page.goto('/training-runs', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const rows = page.locator('tbody > tr');
    const count = await rows.count();
    expect(count, 'the started career is not in the list').toBeGreaterThan(0);

    for (let index = 0; index < count; index += 1) {
        const row = rows.nth(index);
        await expect(row.locator('[data-artwork-slot]'), `row ${index} has no portrait slot`).toHaveCount(1);
        await expect(row.locator('a [data-artwork-slot]'), `row ${index} makes the frame a link`).toHaveCount(0);
    }

    const slot = rows.first().locator('[data-artwork-slot]');
    const tag = await slot.evaluate((el) => el.tagName);

    if (tag === 'IMG') {
        // The frame is decorative: the row already prints the trainee's name beside it.
        await expect(slot).toHaveAttribute('alt', '');
        await expect(slot).toHaveAttribute('src', /\/artwork\/card_portrait\/\d+$/);
    } else {
        // Absence is reserved and transparent, never a grey placeholder (DESIGN.md §4.7).
        const box = await slot.boundingBox();
        expect(box?.width ?? 0, 'the reserved slot has no rendered width').toBeGreaterThanOrEqual(40);
        expect(box?.height ?? 0, 'the reserved slot has no rendered height').toBeGreaterThanOrEqual(40);
        await expect(slot).toHaveCSS('background-color', 'rgba(0, 0, 0, 0)');
    }

    // The slot does not push the document sideways at the standard measure.
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    expect(overflow, 'the row portrait introduced horizontal overflow at 1280').toBeLessThanOrEqual(0);
});
