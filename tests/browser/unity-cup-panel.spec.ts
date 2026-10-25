import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { expectTapTargets } from '../utils/tap-targets';

/*
 * The Unity Cup Team Cockpit (SCREEN-015, plan §9.1). `UnityCupPanelTest` asserts the `team`
 * payload — full team, partial team, none; these cases assert what only a browser reaches: the
 * configured facts and named absences a real run shows, the reading order and the keyboard path
 * through the panel, the meter's text and its bar, the 44px sweep, the 320px reflow and axe.
 *
 * **Every data state here is an absence, on purpose.** No UI write records a team rank, a burst
 * state or a Spirit reading (the capture proposal of `SCREEN_SPEC.md` §7-6 is still the owner's),
 * and no seeder creates `team_race` scenario slots, so a browser can only see the panel the way a
 * fresh run meets it: the config facts rendered and every recorded figure named as absent. The
 * populated roster and the recorded rank are proven at props level in `UnityCupPanelTest`, which is
 * the honest split while no write path exists.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

async function openUnityCupCockpit(page: import('@playwright/test').Page): Promise<void> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);
    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

test('renders the configured burst bands, their payout timing and the Extreme caveat', async ({ page }) => {
    await openUnityCupCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });

    // The four config bands, first and last transcribed values, in a real table with row headers.
    const table = section.getByRole('table', { name: /Reward by combined Spirit Burst/ });
    await expect(table).toBeVisible();
    await expect(table.getByRole('row').getByText('4–6', { exact: true })).toBeVisible();
    await expect(table.getByText('Gold hint Lv3, +40 matching stat, +40 SP')).toBeVisible();

    // The caveat names all three publisher conflicts, in words: state never rides the ⚠ mark alone.
    await expect(section.getByText(/Three parts of this table are publisher conflicts/)).toBeVisible();

    // The table's own provenance marker, per design-2.0 §49.
    await expect(section.getByRole('img', { name: /^Estimated/ })).toBeVisible();

    // The payout timing renders, and the run's own band position is named as absent, not guessed.
    await expect(section.getByText(/The gold versions arrive from the scripted event in Senior Year, Late November/)).toBeVisible();
    await expect(section.getByText(/Where this run sits in the bands is not shown/)).toBeVisible();
});

test('shows the Spirit meter as text and a bar, with readiness and the held advice named absent', async ({ page }) => {
    await openUnityCupCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });

    // The meter carries the reading twice: the text and the labelled bar's accessible name.
    const meter = section.getByRole('img', { name: /Current Spirit: not recorded/ });
    await expect(meter).toBeVisible();
    await expect(section.getByText('Current Spirit', { exact: true })).toBeVisible();
    await expect(section.getByText('N/A').first()).toBeVisible();

    // Burst readiness is not derivable — no recorded Spirit, no configured threshold — so the
    // sentence says why instead of printing a percentage or a band off nothing.
    await expect(section.getByText(/Burst readiness is not computed/)).toBeVisible();

    // "Recommended timing" and "projected benefit" are held advice (ADR-0020 §3): a named absence
    // whose title carries the ruling.
    const held = section.getByText('Recommended timing and projected benefit are not built.');
    await expect(held).toBeVisible();
    await expect(held).toHaveAttribute('title', /Both are advice/);
});

test('draws the rank gauge, the five stat grades and the roster as their empty states', async ({ page }) => {
    await openUnityCupCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });

    // No rank recorded: the gauge says so, and the config ladder still renders as reference.
    await expect(section.getByText(/Rank not recorded/)).toBeVisible();
    const ladder = section.getByRole('list', { name: 'League ladder' });
    await expect(ladder).toBeVisible();
    // Each rung is one `li` carrying the letter AND its level, so the rung is counted and read by its
    // own text rather than matched as an exact string (an exact 'G' never matches `G lv 1`).
    await expect(ladder.getByRole('listitem')).toHaveCount(8);
    await expect(ladder.locator('li').first()).toContainText('G');
    await expect(ladder.locator('li').last()).toContainText('S');
    // The +30 bonus binds to a recorded rank; with none, no bonus claim is made at all.
    await expect(section.getByText(/Team Ranking Bonus/)).toHaveCount(0);

    // Five team stat grades, each N/A with the reason, never a zero or a dash.
    const grades = section.getByRole('list', { name: 'Team stat grades' });
    await expect(grades).toBeVisible();
    await expect(grades.getByText('N/A')).toHaveCount(5);

    // The roster is empty and says what a recorded one would carry, including the six states.
    await expect(section.getByText(/No teammate burst states recorded/)).toBeVisible();

    // The Special Training facts are config's: the rework removal and the Wit burst bonus.
    await expect(section.getByText(/The extra Energy cost on Special Training was removed on 2026-07-01/)).toBeVisible();
    await expect(section.getByText(/A burst on the Wit facility grants 5 Energy recovery/)).toBeVisible();
});

test('renders the team races panel with the configured margin and its empty state', async ({ page }) => {
    await openUnityCupCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });

    await expect(section.getByText(/Aim for at least 3 circles/)).toBeVisible();
    await expect(section.getByText(/as a margin, not a win condition/)).toBeVisible();
    await expect(section.getByText(/No team races recorded/)).toBeVisible();

    // The calendar flag's renderer points at the column that draws it rather than the fallback
    // E1 showed before E3 registered.
    await expect(section.getByText(/The race calendar renders in this screen's Races in this run column/)).toBeVisible();
    await expect(section.getByText(/cannot draw yet/)).toHaveCount(0);
});

test('reaches the panel by keyboard without adding a control or trapping focus', async ({ page }) => {
    await openUnityCupCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });

    // Nothing is pointer-gated: the roster's empty reading is announced through its own status role,
    // with no click and no hover. This fails if the panel ever hides a reading behind disclosure.
    await expect(section.getByText(/No teammate burst states recorded\./)).toBeVisible();

    // The panel adds no form control of its own, so there is no control inside it that a keyboard path
    // could step over. This assertion fails the day an input, a select or a button lands in a panel.
    //
    // Amended 2026-10-25 (owner ruling R3, `DESIGN.md` §5(a)): the absence channel is permitted here, so
    // the guard measures what a stop can *do*, not how many there are. Measured this pass: the region's
    // only links are `TeamRankLadderPanel`'s "Record this race" rows, every one of them navigating to the
    // run's own races screen; its only other focusable node is `ProvenanceMark`'s `role="img"` mark, which
    // opens a tooltip and acts on nothing; and its only disclosure is `AbsenceValue`'s.
    await expect(section.locator('input, select, textarea, button')).toHaveCount(0);
    await expect(section.locator('[tabindex]:not([role="img"])')).toHaveCount(0);
    await expect(section.locator('details:not(:has(> summary:has-text("N/A")))')).toHaveCount(0);

    const links = section.locator('a');
    const linkCount = await links.count();

    for (let i = 0; i < linkCount; i++) {
        expect(await links.nth(i).getAttribute('href'), 'a link in the region left the run').toMatch(/\/races$/);
    }

    // And the region is not a trap: from the last control above it, Tab reaches the region's own stops —
    // `AbsenceValue`'s summary and `ProvenanceMark`'s mark — and then leaves. Every stop inside is one of
    // those two and never a control, which is the amended rule's whole content.
    const controls = page.locator('main a, main button');
    const count = await controls.count();
    const region = await section.boundingBox();
    expect(region, 'the scenario region has no box').not.toBeNull();

    let above = -1;

    for (let i = 0; i < count; i++) {
        const box = await controls.nth(i).boundingBox();

        if (box !== null && region !== null && box.y + box.height <= region.y) {
            above = i;
        }
    }

    expect(above, 'no control sits above the scenario region').toBeGreaterThanOrEqual(0);

    await controls.nth(above).focus();

    const readFocus = () =>
        page.evaluate(() => {
            const el = document.activeElement;

            if (el === null) {
                return null;
            }

            return {
                tag: el.tagName,
                insideRegion: el.closest('section[aria-labelledby="career-scenario-heading"]') !== null,
                isControl:
                    ['BUTTON', 'INPUT', 'SELECT', 'TEXTAREA'].includes(el.tagName) ||
                    (el.hasAttribute('tabindex') && el.getAttribute('role') !== 'img'),
            };
        });

    let escaped = false;

    // Bounded: the region holds 14 stops and the page a few more, so 40 Tabs either leaves it or the
    // region trapped focus.
    for (let step = 0; step < 40 && !escaped; step++) {
        await page.keyboard.press('Tab');
        const stop = await readFocus();

        expect(stop, 'focus left the document while tabbing through the region').not.toBeNull();
        expect(stop?.isControl, 'Tab stopped on a control inside a region that adds none').toBe(false);
        escaped = stop?.insideRegion === false;
    }

    expect(escaped, 'focus never left the Scenario region: it traps Tab').toBe(true);

    // The panel's copy stayed on screen while focus moved past it.
    await expect(section.getByText(/No teammate burst states recorded\./)).toBeVisible();
});

test('sizes the panel controls to the 44px contract and reflows at 320 px', async ({ page }) => {
    await openUnityCupCockpit(page);

    // The same page-wide sweep E1 runs, now through the shared helper: it skips what a collapsed
    // disclosure hides and asserts it measured something. 10 is well under the ~17 controls this page
    // carries.
    await expectTapTargets(page.locator('main a, main button'), 10);

    // WCAG 1.4.10: both columns stack at 320 and nothing scrolls sideways.
    await page.setViewportSize({ width: 320, height: 900 });
    const section = page.getByRole('region', { name: 'Scenario' });
    await expect(section.getByText(/Rank not recorded/)).toBeVisible();
    await expect(section.getByText(/No team races recorded/)).toBeVisible();
    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );
    expect(overflow, 'the cockpit scrolls sideways at 320px').toBeLessThanOrEqual(1);
});

test('passes the axe A + AA scan with the Team Cockpit mounted', async ({ page }) => {
    await openUnityCupCockpit(page);

    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});

/*
 * G4: the team race's published shape. `UnityCupSurfacesTest` pins the payload and the dual readout's
 * firing rule; these cases assert what only a browser reaches — the five categories as a real table with
 * Dirt among them, the reason printed under it rather than behind it, the refusal sentence, and the
 * owner's 2026-10-11 ruling on the region's focusables, as amended 2026-10-25 by ruling R3.
 *
 * **The board has no keyboard row stop, on purpose.** The brief asked for row-level keyboard reach; the
 * ruling that nothing in this panel may be clicked open or tab-stopped outranks it, and every row is
 * already in the document order the case above traces with Tab. That ruling was amended 2026-10-25
 * (owner ruling R3, `DESIGN.md` §5(a)): `AbsenceValue`'s `<details>` is now a stop the rule allows, so the
 * guard below measures controls: a control landing in a panel is still the failure.
 */

test('draws the five team race categories as a table with the published ask and one named absence', async ({ page }) => {
    await openUnityCupCockpit(page);

    const board = page.getByRole('region', { name: 'Team race coverage' });
    await expect(board).toBeVisible();

    // Header plus five category rows. Dirt is a category of the team race and not a distance band, so
    // its presence is the assertion that the board is not the four-band list the target form uses.
    await expect(board.getByRole('row')).toHaveCount(6);
    await expect(board.getByRole('rowheader', { name: 'Dirt' })).toBeVisible();
    await expect(board.getByRole('rowheader', { name: 'Sprint' })).toBeVisible();

    // The ask once, the blank per row, and the reason on the page rather than in a tooltip.
    await expect(board.getByText('3 fielded in each')).toBeVisible();
    await expect(board.getByRole('cell', { name: 'N/A' })).toHaveCount(5);
    await expect(board.getByText(/No column records who this run fields/i)).toBeVisible();

    // The ruling, measured, as amended 2026-10-25 (owner ruling R3): the region adds no form control, its
    // only disclosures are `AbsenceValue`'s, and its only other focusable node is `ProvenanceMark`'s
    // `role="img"` mark. The board itself adds neither, which is the claim this case owns.
    const region = page.getByRole('region', { name: 'Scenario' });
    await expect(region.locator('input, select, textarea, button')).toHaveCount(0);
    await expect(region.locator('[tabindex]:not([role="img"])')).toHaveCount(0);
    await expect(region.locator('details:not(:has(> summary:has-text("N/A")))')).toHaveCount(0);
    await expect(board.locator('a, button, input, select, textarea, details, [tabindex]')).toHaveCount(0);
});

test('stacks the coverage table at 320 px without a sideways scroll', async ({ page }) => {
    await openUnityCupCockpit(page);

    await page.setViewportSize({ width: 320, height: 900 });

    const board = page.getByRole('region', { name: 'Team race coverage' });
    await expect(board.getByRole('rowheader', { name: 'Long' })).toBeVisible();

    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );
    expect(overflow, 'the coverage table scrolls the page sideways at 320px').toBeLessThanOrEqual(1);
});

test('states the team race shape and prints no opponent projection, no chance and no percentage', async ({ page }) => {
    await openUnityCupCockpit(page);

    const shape = page.getByRole('region', { name: 'Team race shape' });
    await expect(shape).toBeVisible();
    await expect(shape.getByText(/Arrives every 6 months, at Late June and Late December/)).toBeVisible();
    await expect(shape.getByText(/5 races, one per category/)).toBeVisible();
    await expect(shape.getByText(/Clearing the set pays an increase to all stats, sized by performance/)).toBeVisible();

    // The one answer the corpus supports, stated as an absence with its reason beside it. The pairs are
    // addressed as elements, not by role name: `term` and `definition` are not name-from-content roles, so
    // Chromium computes no accessible name for a `dt` and `getByRole('term', { name })` can never match.
    // Measured in this region: `getByRole('term')` resolves 2 elements, each with an empty name.
    const terms = shape.locator('dt');
    await expect(terms).toHaveCount(2);
    await expect(terms.nth(0)).toHaveText('Opponent projection');
    await expect(terms.nth(1)).toHaveText('Next team race');
    await expect(shape.locator('dd').first().getByText(/No source says how strong each opponent team is/)).toBeVisible();
    await expect(shape.getByText(/no source publishes the career turn/)).toBeVisible();

    // Ruling 2a: both answers are absences, so no provenance badge accompanies either `N/A` —
    // a badge qualifies a displayed value, and `unknown` beside an absence was retired.
    await expect(shape.getByRole('img', { name: /^Unknown/ })).toHaveCount(0);
    await expect(shape.locator('dd').first().getByText('N/A')).toBeVisible();

    const text = await shape.innerText();
    expect(text).not.toContain('%');
    expect(text.toLowerCase()).not.toContain('probability');
});

test('heads every Scenario panel with the documented capsule', async ({ page }) => {
    await openUnityCupCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });

    // Every panel-level card under Scenario carries the capsule (DESIGN.md §2.3): the owner's rule is
    // that everything in this section looks like everything else in it. Only top-level cards count; a
    // card nested inside a panel is that panel's own notice, not a peer panel (Team Race's
    // "No team races recorded" empty state is one).
    const panels = await section.evaluate((el) => {
        const cards = Array.from(el.querySelectorAll<HTMLElement>('.card'));

        return cards
            .filter((card) => card.parentElement?.closest('.card') === null)
            .map((card) => ({
                capsule: card.querySelector('.lattice-bleed') !== null,
                title: (card.querySelector('.lattice-bleed')?.textContent
                    ?? card.querySelector('h1,h2,h3,h4,h5,h6')?.textContent
                    ?? '').trim(),
            }));
    });

    // Nine panels render on a fresh Unity Cup run; the Team and Unity Cup progress cards are
    // conditional on data this run does not carry. A count drop below nine means a panel stopped
    // rendering, which this case also refuses to pass silently.
    expect(panels.length, JSON.stringify(panels)).toBeGreaterThanOrEqual(9);
    expect(panels.filter((panel) => !panel.capsule), JSON.stringify(panels)).toEqual([]);
});

test('capsules the four Scenario panels that sat outside the card pattern', async ({ page }) => {
    await openUnityCupCockpit(page);

    const section = page.getByRole('region', { name: 'Scenario' });

    const panels = await section.evaluate((el) => {
        const cards = Array.from(el.querySelectorAll<HTMLElement>('.card'));

        return cards
            .filter((card) => card.parentElement?.closest('.card') === null)
            .map((card) => ({
                capsule: card.querySelector('.lattice-bleed') !== null,
                title: (card.querySelector('.lattice-bleed')?.textContent
                    ?? card.querySelector('h1,h2,h3,h4,h5,h6')?.textContent
                    ?? '').trim(),
            }));
    });

    const titles = panels.map((panel) => panel.title);

    // The four panels the owner named. Before this slice they were bare sections with plain
    // headings, so they were not `.card`s at all and never reached the capsule assertion below.
    for (const name of ['Trainee goals', 'Mandatory races', 'Unity Cup', 'Published stat caps']) {
        expect(titles, JSON.stringify(panels)).toContain(name);
    }

    expect(panels.filter((panel) => !panel.capsule), JSON.stringify(panels)).toEqual([]);

    // Nine cards rendered before this slice (Team and Unity Cup progress are data-conditional) plus
    // the four added here.
    expect(panels.length, JSON.stringify(panels)).toBeGreaterThanOrEqual(13);
});

test('heads both scenario blocks with the documented capsule and keeps the region name', async ({ page }) => {
    await openUnityCupCockpit(page);

    for (const name of ['Team race coverage', 'Team race shape']) {
        const block = page.getByRole('region', { name });
        await expect(block).toBeVisible();

        // The documented capsule chrome (`DESIGN.md` §2.3): the lattice-bleed motif on the chrome fill,
        // one per block, as the eight sibling scenario panels render it. Structural assertion, not a
        // pixel colour: the class list is what a later restyle would break.
        const capsule = block.locator('.lattice-bleed.bg-chrome');
        await expect(capsule).toHaveCount(1);
        await expect(capsule.getByText(name, { exact: true })).toBeVisible();

        // The semantic heading survives for assistive tech (the region's accessible name still comes
        // from it) and it is not a second visible title beside the capsule.
        const heading = block.getByRole('heading', { name });
        await expect(heading).toHaveCount(1);
        await expect(heading).toHaveClass(/sr-only/);
    }
});

test('shows no dual-value readout while the rail has nothing to rank', async ({ page }) => {
    await openUnityCupCockpit(page);

    // A fresh run records no Energy and no target, so the rail refuses and the readout that hangs off a
    // ranked action is absent rather than empty. The ranked case is `career-cockpit.spec.ts`'s subject;
    // the firing rule itself is pinned at props level in `UnityCupSurfacesTest`.
    await expect(page.getByRole('region', { name: 'Advisor' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'What this session pays' })).toHaveCount(0);
});

test('passes the axe A + AA scan with the G4 blocks mounted', async ({ page }) => {
    await openUnityCupCockpit(page);

    await expect(page.getByRole('region', { name: 'Team race coverage' })).toBeVisible();

    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
