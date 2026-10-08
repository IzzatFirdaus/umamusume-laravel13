import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';

/*
 * Rendered-copy, region-separation and accessibility evidence for D14a, the run-scoped race strip
 * (`SCREEN_SPEC.md` SCR-CAR-011, interim before D14). `RunRaceStripTest` asserts the resolved props;
 * these cases assert what only a browser reaches: that the 0.1.0 catalogue grid is gone from the
 * Cockpit's left column, that a race the run already entered is never reprinted as something still to
 * come, that the three regions carry accessible names, and that the strip renders no percentage and no
 * derived distance band.
 *
 * Fixture strategy. The run is created through `/training-runs/create` and its turns are logged through
 * the run record screen's own raw form, which is the app's supported path in, and the run is deleted
 * over HTTP in `afterEach` through the shared `tests/utils/delete-run.ts`, which is also what
 * `career-race-decision.spec.ts` uses.
 *
 * Eleven turns is the cheapest fixture that puts a decision on a turn the calendar carries. Year 1 of
 * the seeded catalogue begins at turn 12 (the Junior Make Debut) and holds nothing before it, so fewer
 * writes land the decision on an empty turn. The writes get 60s rather than the config's 15s for the
 * reason that file records: `php artisan serve` is a single-process `php -S`.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/** A Unity Cup career with `$turns` logged turns, ending on the run record screen. */
async function newRun(page: import('@playwright/test').Page, turns: number): Promise<string> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Agnes Digital');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'unity_cup' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+$/, WRITE);

    const runUrl = page.url();
    createdRunUrls.push(runUrl);

    if (turns > 0) {
        const hatch = page.locator('details', { has: page.getByText('Correct a turn by hand') });
        await page.getByText('Correct a turn by hand').click();

        for (let turn = 1; turn <= turns; turn++) {
            await hatch.locator('input[name="turn"]').fill(String(turn));
            await hatch.locator('input[name="speed"]').fill('600');
            await hatch.locator('input[name="stamina"]').fill('500');
            await hatch.locator('input[name="power"]').fill('500');
            await hatch.locator('input[name="guts"]').fill('500');
            await hatch.locator('input[name="wit"]').fill('500');
            await hatch.getByRole('button', { name: 'Save correction' }).click();
            await expect(page.getByText(`Turn ${turn} logged.`)).toBeVisible(WRITE);
        }
    }

    return runUrl;
}

const strip = (page: import('@playwright/test').Page) =>
    page.getByRole('region', { name: 'Races in this run' });

test('replaces the catalogue grid with three run-scoped regions', async ({ page }) => {
    const runUrl = await newRun(page, 11);
    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The 0.1.0 calendar is not on this screen any more: its year tabs and its month grid are the
    // give-away, and neither is rendered.
    await expect(page.getByRole('heading', { name: 'Race calendar' })).toHaveCount(0);
    await expect(page.getByRole('link', { name: /Year$/ })).toHaveCount(0);

    await expect(strip(page)).toBeVisible();

    for (const name of ['Recorded in this run', 'This turn', 'Still to come']) {
        await expect(page.getByRole('region', { name })).toBeVisible();
    }

    // Nothing recorded yet, and the region says what is missing, why, and where to go.
    await expect(page.getByText('No race is recorded for this run yet')).toBeVisible();
    await expect(page.getByRole('link', { name: 'Go to the run record screen' })).toBeVisible();

    // Turn 12 is the one the debut sits on, so the count reads one rather than the year's whole list.
    const turn = page.getByRole('region', { name: 'This turn' });
    await expect(turn.getByText('Junior Year, turn 12')).toBeVisible();
    await expect(turn.getByText('The calendar carries 1 race here.')).toBeVisible();
    await expect(turn.getByRole('link', { name: 'Open Race Decision for turn 12' })).toBeVisible();

    // Still to come holds the obligations ahead of turn 12, as mandatory, and never as a Goal. The
    // debut itself is the turn being decided, so it is not still to come, and the exact text of the
    // marker is the glyph plus the word (`career-race-decision.spec.ts` records the same trap).
    const ahead = page.getByRole('region', { name: 'Still to come' });
    await expect(ahead.getByText('Junior Make Debut')).toHaveCount(0);
    await expect(ahead.getByText('URA Finals Qualifier')).toBeVisible();
    await expect(ahead.getByText('URA Finals Final (Aoharu)')).toBeVisible();
    await expect(ahead.getByText('! Mandatory', { exact: true })).toHaveCount(3);
    await expect(ahead.getByText('Goal', { exact: true })).toHaveCount(0);
});

test('keeps an entered race in the run region and out of the list ahead of it', async ({ page }) => {
    const runUrl = await newRun(page, 11);

    // Enter the debut through the screen that owns the decision, leaving the turn link alone so the
    // recorded row has to print the KI-17 absence rather than a guessed turn.
    await page.goto(`${runUrl}/races`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const opener = page.getByRole('button', { name: /^Details and actions for/ }).first();
    await expect(opener).toBeVisible();
    await opener.click();
    await expect(page.locator('dialog[open]')).toBeVisible();
    await page.getByRole('button', { name: 'Enter Race' }).click();

    // The write's own proof. The card that offers the slot starts printing what the run recorded
    // against it, and the locator is unique on the page; a heading match would not be, because the
    // drawer is still mounted while the visit is in flight.
    await expect(page.getByText('Recorded: Entered')).toBeVisible(WRITE);

    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const recorded = page.getByRole('region', { name: 'Recorded in this run' });
    await expect(recorded.getByText('Junior Make Debut')).toBeVisible();
    await expect(recorded.getByText('Entered')).toBeVisible();
    // The turn the race was run on is not tied, so the row says N/A on a disclosure that names the
    // reason for a keyboard and a screen reader as well as a mouse.
    const unrecorded = recorded.locator('summary', { hasText: 'KI-17' });
    await expect(unrecorded).toBeVisible();
    await expect(unrecorded).toContainText('N/A');

    // One race, one region: the debut is mandatory, it is entered, and it is not still to come.
    await expect(page.getByRole('region', { name: 'Still to come' }).getByText('Junior Make Debut')).toHaveCount(0);

    // And This Turn shows what the run has already done at the turn it is deciding.
    await expect(page.getByRole('region', { name: 'This turn' }).getByText('Junior Make Debut')).toBeVisible();
});

test('says so when there is no turn being decided, and prints no figure it cannot source', async ({ page }) => {
    const runUrl = await newRun(page, 1);

    // Turn 2 of Junior: the seeded calendar holds nothing before turn 12, so This Turn is absent and
    // the strip explains it rather than borrowing a race from another turn.
    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByText('No race on this scenario\'s calendar falls at turn 2.')).toBeVisible();
    await expect(page.getByRole('link', { name: 'Open Race Decision for turn 2' })).toHaveCount(0);

    // The goal races the client prints per trainee have no column, so the line is an N/A whose
    // disclosure states the reason, rather than a flag or an empty list.
    const goal = page.locator('summary', { hasText: 'KI-34' });
    await expect(goal).toContainText('N/A');
    await expect(page.getByText('Goal races:')).toBeVisible();

    // No percentage anywhere in the strip: readiness and win probability are held (`ADR-0016`).
    await expect(strip(page)).not.toContainText('%');

    // No distance band: the corpus disagrees at 1400 m and this slice derives nothing from metres.
    for (const band of ['Sprint', 'Mile', 'Medium', 'Long']) {
        await expect(strip(page).getByText(band, { exact: true })).toHaveCount(0);
    }

    // No em dash in shipped copy (AGENTS.md §5).
    await expect(strip(page)).not.toContainText('—');
});

test('walks the strip links from the keyboard with a visible focus ring', async ({ page }) => {
    const runUrl = await newRun(page, 11);
    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const decision = page.getByRole('link', { name: 'Open Race Decision for turn 12' });
    await decision.focus();
    await expect(decision).toBeFocused();
    await expect(decision).toBeInViewport();

    const outline = await decision.evaluate((el) => getComputedStyle(el).outlineWidth);
    expect(parseFloat(outline), 'the strip link has no focus ring').toBeGreaterThanOrEqual(2);

    // Every strip link is a 44px target (WCAG 2.2 SC 2.5.8).
    for (const target of [
        decision,
        page.getByRole('link', { name: 'Go to the run record screen' }),
    ]) {
        const box = await target.boundingBox();
        expect(box?.height ?? 0, 'a strip link is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
    }
});

test('holds the left column at 320px and at a 200-percent width without sideways scroll', async ({ page }) => {
    const runUrl = await newRun(page, 1);
    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // 320 is WCAG 1.4.10's reflow width. 640x512 is what a 1280x1024 display shows at 200 percent,
    // and it sits below the `md` breakpoint, so it is the same single-column regime rather than a
    // third layout. Each width is measured, not inferred from the other.
    for (const [width, height] of [[320, 900], [640, 512]] as const) {
        await page.setViewportSize({ width, height });
        await page.waitForTimeout(50);

        const overflow = await page.evaluate(
            () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
        );
        expect(overflow, `the race strip scrolls sideways at ${width}px`).toBeLessThanOrEqual(1);

        for (const name of ['Recorded in this run', 'This turn', 'Still to come']) {
            await expect(page.getByRole('region', { name })).toBeVisible();
        }
    }

    await page.setViewportSize({ width: 320, height: 900 });

    // The spec's mobile order (design-2.0 §40): the primary decision reads above the left column, so
    // the strip never pushes the actions below it.
    const ahead = await page.getByRole('heading', { name: 'Still to come' }).boundingBox();
    const actions = await page.getByRole('heading', { name: 'Actions' }).boundingBox();
    expect((actions?.y ?? 0), 'the actions are not above the left column on mobile').toBeLessThan(ahead?.y ?? 0);

    // Reduced motion is the global block's, and the probe that proves it can fail is the reason this
    // case is here rather than a computed-style read of elements that never animate.
    const duration = async (): Promise<number> =>
        page.evaluate(() => {
            const probe = document.createElement('div');
            probe.style.transitionDuration = '2s';
            document.body.appendChild(probe);
            const computed = getComputedStyle(probe).transitionDuration;
            probe.remove();

            return parseFloat(computed);
        });

    await page.emulateMedia({ reducedMotion: 'no-preference' });
    expect(await duration()).toBeGreaterThan(1);

    await page.emulateMedia({ reducedMotion: 'reduce' });
    expect(await duration()).toBeLessThan(0.05);
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    const runUrl = await newRun(page, 11);
    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // `buildAxe` scopes to `#app`, which is what makes this deterministic (KI-63).
    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
