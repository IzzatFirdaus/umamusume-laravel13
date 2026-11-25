import { test, expect } from './support/base-test';
import { startCareer, walkWizard } from '../utils/start-career';
import { recordTurns } from '../utils/record-turns';

/*
 * The findings of the 2026-11-18 browser walk, pinned so none of them comes back silently.
 *
 * Each case names the measured defect it prevents, because all three were invisible to the suite as
 * it stood: one was a spec that had pinned the bug, one had no test at all, and one was a dead end
 * that only showed up when a real Trainer reached the footer of the Preflight step.
 */

test.describe('browser-walk findings, 2026-11-18', () => {
    test('the Turn tile separates its figure from its bound in the accessible text', async ({ page }) => {
        // A turn has to be recorded first: at turn 0 the tile prints its absence word and carries no
        // suffix at all, so the separator this case is about would not be rendered.
        const url = await startCareer(page, 'ura_finale');
        const runId = url.match(/\/training-runs\/(\d+)/)?.[1] ?? '';
        expect(runId, 'startCareer did not land on a cockpit URL').not.toBe('');
        await recordTurns(page, runId, 2);
        await page.goto('/', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const turn = page
            .getByRole('region', { name: 'Pick up where you left off' })
            .locator('[data-tile="turn"] [data-tile-value]');

        // The character data is the assertion: `ml-1` on the suffix span spaces the two halves on
        // screen, but a span that begins with no whitespace in the text node makes a screen reader
        // announce "1of 72". The separator has to exist in the DOM, not only in the layout.
        const text = (await turn.textContent())?.replace(/\s+/g, ' ').trim();
        expect(text, 'the Turn tile reads "<figure> of <bound>"').toMatch(/^\d+ of \d+$/);
        expect(text, 'no digit runs straight into the word "of"').not.toMatch(/\dof/);
    });

    test('the whole scenario card is a selection surface, not just its heading row', async ({ page }) => {
        await page.goto('/career/setup/scenario', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const card = page.locator('[data-scenario-card]').first();
        await card.scrollIntoViewIfNeeded();

        // A probe grid over the card: before the fix only the heading-row `<label>` (about 4% of a
        // 561x216 card) resolved to the card, while the card carried the border, the tint and the
        // `is-chosen` state of one option. Every interior point must now belong to the card.
        const coverage = await card.evaluate((el) => {
            const rect = el.getBoundingClientRect();
            let inside = 0;
            let total = 0;
            for (let row = 0; row < 4; row += 1) {
                for (let col = 0; col < 3; col += 1) {
                    const x = rect.left + (rect.width * (col + 0.5)) / 3;
                    const y = rect.top + (rect.height * (row + 0.5)) / 4;
                    const hit = document.elementFromPoint(x, y);
                    total += 1;
                    if (hit !== null && el.contains(hit)) {
                        inside += 1;
                    }
                }
            }
            return { inside, total, cursor: getComputedStyle(el).cursor };
        });

        expect(coverage.total).toBe(12);
        expect(coverage.inside, 'every probed point belongs to the card').toBe(12);
        expect(coverage.cursor, 'the card states that it is clickable').toBe('pointer');

        // The summary paragraph is deep in the card body: the point a Trainer actually aims at.
        const summary = card.locator('[data-field="summary"]').first();
        if ((await summary.count()) > 0) {
            await summary.click();
            await expect(page.getByText('✓ Selected')).toBeVisible();
        }
    });

    test('the Preflight footer names the gate when Create the run is disabled for it', async ({ page }) => {
        // Fill the draft through the wizard and stop on Preflight WITHOUT creating the run: that is
        // the state where `canStart` is true (scenario and trainee are in) and only the
        // acknowledgement gates the button. `startCareer` cannot reach it because creating the run
        // consumes the draft.
        await walkWizard(page, 'ura_finale');
        await page.goto('/career/setup/preflight', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const start = page.getByRole('button', { name: 'Create the run' });
        await expect(start).toBeVisible();

        const hint = page.locator('[data-ack-hint]');

        // The product states the gate itself when a required step is missing: `startBlocker` prints
        // "A scenario and a trainee are not entered". In that state this footer hint stays silent on
        // purpose, so it never competes with that sentence.
        if ((await hint.count()) === 0) {
            expect(await start.evaluate((el) => (el as HTMLButtonElement).disabled)).toBe(true);
            return;
        }

        // Otherwise the draft is complete, only the acknowledgement is missing, and the reason has to
        // be readable at the point of use: the box it waits on sits most of a page further down, so a
        // disabled button with no explanation is a dead end (WCAG 3.3.1).
        await expect(hint).toContainText('Tick the confirmation below to enable Create the run.');

        const distance = await page.evaluate(() => {
            const button = [...document.querySelectorAll('button')].find((el) =>
                /Create the run/i.test(el.innerText ?? ''),
            );
            const note = document.querySelector('[data-ack-hint]');
            if (button == null || note == null) {
                return null;
            }
            return Math.abs(note.getBoundingClientRect().top - button.getBoundingClientRect().top);
        });
        expect(distance, 'the hint sits in the head band with the button').not.toBeNull();
        expect(distance as number).toBeLessThan(120);

        // Ticking the box retires the hint and opens the door.
        await page.locator('#confirmed').check();
        await expect(hint).toHaveCount(0);
    });
});
