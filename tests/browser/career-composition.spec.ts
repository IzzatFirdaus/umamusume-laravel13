import { test, expect, type Page } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';
import { startCareer } from '../utils/start-career';
import { uraCompletedRun } from '../utils/ura-completed-run';

/*
 * The career-set composition gate (Slice 8 of the career layout-parity plan).
 *
 * The nine career run screens answer to twelve wireframes, and until this spec nothing asserted
 * their composition as a set: each screen's own spec guards its behaviour, and `layout-compositions`
 * covers the non-career surfaces. So a page could stop rendering its regions and still pass every
 * existing check, which is the vacuous-pass failure `database-parity.spec.ts` guards against on the
 * database pages.
 *
 * What is asserted here, per screen:
 *
 *   1. exactly one `h1` — the document outline stays one document wide
 *   2. the page-level region grid is one of the ported `.g-*` primitives, never a hand-rolled
 *      `grid-cols-[...]`, so the HFW's track pair and its container crossing are the page's
 *   3. the region `h2` list starts with the HFW's own order for that screen
 *   4. no `.keybox`, `.mockbar` or `.toc` — the wireframe document's apparatus never reaches a page
 *      (`annotation-sweep.spec.ts` holds the other routes to the same rule; these nine are not in
 *      its list, so this is their only screen-level guard)
 *
 * Fixture: one run, started through the wizard by the shared helper and deleted in `afterEach`, which
 * is the strategy `career-cockpit.spec.ts` states and `tests/utils/delete-run.ts` cascades.
 */

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/** The HFW's own region order, per screen, from each wireframe's `h2` sequence. */
const SCREENS: { path: string; hfws: string; regions: string[] }[] = [
    {
        path: 'cockpit',
        hfws: 'cockpit-unity-cup.html',
        regions: ['Stats and state', 'Scenario'],
    },
    {
        path: 'training',
        hfws: 'training-decision.html',
        regions: ['Disciplines', 'Other actions', 'Why', 'Recorded outcome'],
    },
    {
        path: 'races',
        hfws: 'race-decision.html',
        regions: ['Available this turn', 'Consecutive races'],
    },
    {
        path: 'skills',
        hfws: 'skills-planner.html',
        regions: ['Skills', 'Why the hint ladder is not linear'],
    },
    {
        path: 'events',
        hfws: 'event-decision.html',
        regions: ['What the event shows', 'Record the outcome', "This event's provenance"],
    },
    {
        path: 'inheritance',
        hfws: 'inheritance.html',
        regions: ['Inheritance', 'Result of this pick'],
    },
    {
        path: 'timeline',
        hfws: 'timeline.html',
        regions: ['The record', 'Phase grouping'],
    },
];

/*
 * The Result screen answers to a FINISHED run, so it cannot share the wizard fixture: a run created
 * a moment ago is Active, and `Result.vue` prints "This career is still running" for that state
 * instead of the HFW's finished body. It is asserted against the seeded completed run
 * `uraCompletedRun()` provides, which is the same fixture `containment.spec.ts` uses.
 */
const RESULT_REGIONS = ['Final stats', 'Finale-ready check', 'What can be banked', 'Export'];

/** The heading texts of a page's `h2`s, in document order. */
async function regionHeadings(page: Page): Promise<string[]> {
    return page
        .locator('main h2')
        .evaluateAll((nodes) =>
            nodes
                .map((node) => (node.textContent ?? '').replace(/\s+/g, ' ').trim())
                .filter((text) => text.length > 0),
        );
}

test.describe('career composition', () => {
    test('every career screen keeps the HFW region order, one h1, a .g-* grid and no apparatus', async ({
        page,
    }) => {
        // Desktop width, so a `.g-*` region is expected to have resolved its multi-track pair; the
        // Compact and narrow bands deliberately collapse to one column and are covered by
        // `layout-compositions.spec.ts`.
        await page.setViewportSize({ width: 1280, height: 900 });

        const runUrl = await startCareer(page);
        createdRunUrls.push(runUrl);

        for (const screen of SCREENS) {
            await page.goto(`${runUrl}/${screen.path}`, { waitUntil: 'domcontentloaded' });
            await page.locator('#app > *').first().waitFor();

            const where = `${screen.path} (${screen.hfws})`;

            // 1. One `h1`. It is NOT inside `main`: `CareerLayout` forwards `#title` to `AppLayout`, so
            //    the band owns the document's only `h1` and the page body starts at `h2`. Measuring
            //    `main h1` here returns zero on every career screen for that reason, not because the
            //    outline is wrong.
            await expect(page.locator('h1'), `${where}: one h1`).toHaveCount(1);

            // 2. The page-level grid is a ported primitive. `[class*="grid-cols-["]` is the shape the
            //    hand-rolled tracks used, and the plan's whole point is that no career page keeps one.
            const handRolled = await page
                .locator('main div[class*="grid"]')
                .evaluateAll((nodes) =>
                    nodes.filter((node) => /grid-cols-\[/.test(node.className)).length,
                );
            await expect(handRolled, `${where}: no arbitrary grid-cols-[...]`).toBe(0);

            // 2b. The positive half of the same rule: at desktop width the screen really does lay out
            //     on a ported primitive, so "no arbitrary grid" cannot be satisfied by having no grid
            //     at all. One `.g-*` region with two or more resolved tracks is the evidence.
            //
            //     Not every HFW draws a track pair. `skills-planner.html` is two stacked cards in one
            //     column with no side pair at all, so Skills is the one screen where a zero here is
            //     the wireframe's own shape rather than a miss — and it is named below, not excluded by
            //     a blanket `>= 0`, which would have made the assertion vacuous for every screen.
            const primitiveTracks = await page
                .locator('main .g-2, main .g-3, main .g-4, main .g-side')
                .evaluateAll((nodes) =>
                    nodes
                        .map((node) =>
                            getComputedStyle(node)
                                .gridTemplateColumns.split(' ')
                                .filter((part) => part !== '').length,
                        )
                        .reduce((most, count) => Math.max(most, count), 0),
                );
            if (screen.path === 'skills') {
                expect(
                    primitiveTracks,
                    `${where}: skills-planner.html draws no track pair, so zero is its own shape`,
                ).toBe(0);
            } else {
                expect(
                    primitiveTracks,
                    `${where}: a .g-* region resolves more than one track at this width`,
                ).toBeGreaterThan(1);
            }

            // 3. The region order, asserted as a SUBSEQUENCE.
            //
            // A prefix comparison fails for a reason that is not a layout defect: the cockpit of a
            // freshly-created run leads with its own "No turn recorded yet" empty state, because a
            // run with no logged turn has no state to print. That heading is the screen answering
            // the state it is in, not a region out of order.
            //
            // A subsequence keeps both halves of the guard: a missing region fails, and a region
            // rendered out of order fails. What it deliberately does not do is forbid an extra
            // heading the HFW could not draw — the empty state, a scenario panel, a disclosure.
            const headings = (await regionHeadings(page)).map((text) => text.toLowerCase());
            let cursor = 0;
            for (const region of screen.regions) {
                const found = headings.findIndex(
                    (text, index) => index >= cursor && text.includes(region.toLowerCase()),
                );
                expect(
                    found,
                    `${where}: "${region}" is present and after the region before it. Rendered order: ${headings.join(' | ')}`,
                ).toBeGreaterThanOrEqual(0);
                cursor = found + 1;
            }

            // 4. The document's apparatus.
            await expect(page.locator('main .keybox'), `${where}: no .keybox`).toHaveCount(0);
            await expect(page.locator('main .mockbar'), `${where}: no .mockbar`).toHaveCount(0);
            await expect(page.locator('main .toc'), `${where}: no .toc`).toHaveCount(0);
        }
    });

    test('the Result screen keeps result.html\'s region order on a finished run', async ({ page }) => {
        await page.setViewportSize({ width: 1280, height: 900 });

        const runId = uraCompletedRun();

        await page.goto(`/training-runs/${runId}/result`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const where = 'result (result.html)';

        await expect(page.locator('h1'), `${where}: one h1`).toHaveCount(1);

        const headings = (await regionHeadings(page)).map((text) => text.toLowerCase());
        let cursor = 0;
        for (const region of RESULT_REGIONS) {
            const found = headings.findIndex(
                (text, index) => index >= cursor && text.includes(region.toLowerCase()),
            );
            expect(
                found,
                `${where}: "${region}" is present and in order. Rendered order: ${headings.join(' | ')}`,
            ).toBeGreaterThanOrEqual(0);
            cursor = found + 1;
        }

        // The two export doors are real links to the route that serves them, not prose saying no
        // route exists — the sentence this replaced was stale while `runs.export` had shipped.
        await expect(
            page.getByRole('link', { name: /Download CSV/i }),
            `${where}: CSV download`,
        ).toHaveAttribute('href', new RegExp(`/training-runs/${runId}/export/csv$`));
        await expect(
            page.getByRole('link', { name: /Download JSON/i }),
            `${where}: JSON download`,
        ).toHaveAttribute('href', new RegExp(`/training-runs/${runId}/export/json$`));

        await expect(page.locator('main .keybox'), `${where}: no .keybox`).toHaveCount(0);
        await expect(page.locator('main .mockbar'), `${where}: no .mockbar`).toHaveCount(0);
        await expect(page.locator('main .toc'), `${where}: no .toc`).toHaveCount(0);
    });
});
