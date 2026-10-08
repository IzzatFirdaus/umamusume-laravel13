import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';

/*
 * Rendered-copy, layout and accessibility evidence for SCREEN-009, the Career Cockpit
 * (`SCR-CAR-011`, plan §8 D8). `CareerCockpitTest` asserts the resolved props — the advisor contract
 * with Energy present and absent, the seven entries and the single marker, the config-driven widget
 * list. These cases assert what only a browser reaches: the three breakpoints and the reflow, the
 * keyboard path through the action grid, the reduced-motion path, the 44px sweep, and the refusal
 * sentence a run with no Energy prints.
 *
 * Fixture strategy. The run is built by walking the wizard's own steps and pressing `Start Career`, so
 * every fact under test was entered through the app rather than seeded, and the row is deleted over
 * HTTP in `afterEach` — the shared `tests/utils/delete-run.ts` teardown, which is what keeps this
 * spec from breaking `runs.spec.ts`'s empty-state assertions.
 *
 * Axe coverage. `@axe-core/playwright` is installed, so the last case below scans this screen with
 * the shared `tests/utils/accessibility.ts` builder and the same `wcag2a`/`wcag2aa`/`wcag21aa` tag
 * set `accessibility.spec.ts` uses. It lives here rather than there because the cockpit needs a run,
 * and the run is built by this file's own wizard walk. The hand-rolled checks (target size, keyboard
 * path, focus ring, reflow, reduced motion) are above it.
 */

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/**
 * One career, created the way a Trainer creates one: the wizard's steps, then `Start Career`.
 *
 * The deck step is filled because `StartCareerRequest` re-runs each step's own rules, and the deck is
 * one of them; the ancestry step is left alone, which is the state a fresh install meets (the seeded
 * scratch database holds no Veterans).
 *
 * Each step's write is given 60s rather than the config's 15s. `php artisan serve` is a single-process
 * `php -S`, so a step's PUT queues behind whatever else is on the box; `playwright.config.ts` already
 * raised the *test* budget to 180s for exactly that reason (11-16s per request observed under load),
 * and a button that reads "Saving…" at 15s is the host's queue rather than a stuck form.
 */
const WRITE = { timeout: 60_000 } as const;

async function newCareer(page: import('@playwright/test').Page): Promise<string> {
    await page.goto('/career/setup/scenario', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#scenario-unity_cup').click();
    await expect(page.locator('#scenario-unity_cup')).toHaveAttribute('aria-pressed', 'true', WRITE);

    await page.goto('/career/setup/trainee', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    const traineeButton = page.getByRole('button', { name: 'Select Trainee' }).first();
    await traineeButton.click();
    await expect(traineeButton).toHaveAttribute('aria-pressed', 'true', WRITE);

    await page.goto('/career/setup/target', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.selectOption('select[name="purpose"]', 'StoryClear');
    await page.selectOption('select[name="distance"]', 'Medium');
    await page.selectOption('select[name="surface"]', 'Turf');
    await page.selectOption('select[name="style"]', 'Pace Chaser');

    for (const stat of ['Speed', 'Stamina', 'Power', 'Guts', 'Wit']) {
        await page.locator(`input[name="targets[${stat}]"]`).fill('800');
    }

    await page.getByRole('button', { name: 'Save target' }).click();
    await page.locator('#app > *').first().waitFor();

    await page.goto('/career/setup/deck', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#deck-pick').selectOption({ index: 1 });
    await page.getByRole('button', { name: /^Equip owned to/ }).first().click();
    await expect(page.locator('#deck-slot-1')).not.toContainText('Not equipped', WRITE);

    await page.goto('/career/setup/preflight', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('button', { name: 'Start Career' }).click();
    // `Start Career` lands on the Cockpit, not the run record screen (SCR-CAR-010 redirects to
    // `runs.cockpit`), so this waits for the Cockpit and remembers the *record* URL: the cleanup
    // addresses the run by the id in that path, which is the plainest form of it.
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });

    createdRunUrls.push(page.url().replace(/\/cockpit$/, ''));

    return page.url();
}

/** The Cockpit for a freshly created career: `Start Career` already lands on it. */
async function openCockpit(page: import('@playwright/test').Page): Promise<void> {
    await newCareer(page);
    await page.locator('#app > *').first().waitFor();
}

test('names every absent value and refuses to rank until Energy is entered', async ({ page }) => {
    await openCockpit(page);

    // The header claims no position for a run that has logged nothing, and every reading is `N/A`
    // with a reason rather than a zero (D-220). No dash is printed anywhere (AGENTS.md §5).
    await expect(page.getByRole('heading', { name: 'Stats and state' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Advisor' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Actions' })).toBeVisible();

    await expect(page.getByText('N/A').first()).toBeVisible();
    // No em dash anywhere in shipped copy: an unrecorded value is `N/A` with a reason (AGENTS.md §5).
    await expect(page.locator('main')).not.toContainText('—');

    // The empty state names what is missing, why it matters and what to do.
    await expect(page.getByRole('heading', { name: 'No turn recorded yet' })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Record the first turn on the run screen' })).toBeVisible();

    // Energy absent is C2's refusal, printed as the one reason line rather than a band read off nothing.
    await expect(
        page.getByText('Recommendation unavailable because Energy has not been entered.'),
    ).toBeVisible();

    // Seven entries, and no RECOMMENDED marker at all while the advisor declines.
    await expect(page.getByRole('link', { name: /^Training/ })).toBeVisible();
    await expect(page.getByRole('link', { name: /^Inheritance/ })).toBeVisible();
    // Scoped to the Actions region the way the ranked case below is, and for the same reason:
    // `getByText` matches case-insensitively as a substring, and since Phase E the word also occurs
    // in panel prose naming a held field ("Recommended timing and projected benefit are not built",
    // Unity Cup's SCREEN-015 absence). The claim here is about the grid's marker, not about the word.
    const actions = page.getByRole('region', { name: 'Actions' });
    await expect(actions.getByText('RECOMMENDED')).toHaveCount(0);
});

test('lays out at 1280, 768 and 320 without a horizontal scroll', async ({ page }) => {
    await openCockpit(page);

    for (const width of [1280, 768, 320]) {
        await page.setViewportSize({ width, height: 900 });
        await page.waitForTimeout(50);

        // WCAG 1.4.10: no two-dimensional scrolling for a primary decision.
        const overflow = await page.evaluate(
            () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
        );
        expect(overflow, `the cockpit scrolls sideways at ${width}px`).toBeLessThanOrEqual(1);

        // Every region is in the accessibility tree at every width: nothing is hidden by a breakpoint.
        await expect(page.getByRole('heading', { name: 'Races in this run' })).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Actions' })).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Advisor' })).toBeVisible();
        await expect(page.getByRole('heading', { name: 'Scenario' })).toBeVisible();
    }

    // The spec's mobile order: the recommendation stays above the actions (design-2.0 §40).
    await page.setViewportSize({ width: 320, height: 900 });
    const advisor = await page.getByRole('heading', { name: 'Advisor' }).boundingBox();
    const actions = await page.getByRole('heading', { name: 'Actions' }).boundingBox();
    expect((advisor?.y ?? 0), 'the recommendation is not above the actions on mobile').toBeLessThan(actions?.y ?? 0);
});

test('walks the action grid from the keyboard with a visible focus ring', async ({ page }) => {
    await openCockpit(page);

    const training = page.getByRole('link', { name: /^Training/ });
    await training.focus();
    await expect(training).toBeFocused();

    // Tab moves through the grid in reading order; the next entry takes focus, and it carries the
    // global `:focus-visible` ring rather than relying on a hover state (WCAG 2.4.7).
    await page.keyboard.press('Tab');
    const race = page.getByRole('link', { name: /^Race/ });
    await expect(race).toBeFocused();
    await expect(race).toBeInViewport();

    const outline = await race.evaluate((el) => getComputedStyle(el).outlineWidth);
    expect(parseFloat(outline), 'the focused grid entry has no focus ring').toBeGreaterThanOrEqual(2);

    // And the last entry is reachable without being hidden under the sticky mobile nav (SC 2.4.11).
    await page.setViewportSize({ width: 320, height: 700 });
    const inheritance = page.getByRole('link', { name: /^Inheritance/ });
    await inheritance.focus();
    await expect(inheritance).toBeInViewport();
});

test('quiets transitions when the operating system asks for reduced motion', async ({ page }) => {
    await openCockpit(page);

    // The probe carries a two-second transition of its own. Under `reduce` the global block must
    // override it, and under `no-preference` it must not — so this check can fail, which is what makes
    // it evidence rather than a no-op (design-2.0 §43, plan §12.4).
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

test('sizes the cockpit controls to the 44px contract', async ({ page }) => {
    await openCockpit(page);

    // WCAG 2.2 SC 2.5.8, floor 44px (plan §12.1). The same sweep `dashboard.spec.ts` runs.
    const targets = [
        page.getByRole('link', { name: /^Training/ }),
        page.getByRole('link', { name: /^Race/ }),
        page.getByRole('link', { name: /^Rest/ }),
        page.getByRole('link', { name: /^Recreation/ }),
        page.getByRole('link', { name: /^Scenario action/ }),
        page.getByRole('link', { name: /^Event/ }),
        page.getByRole('link', { name: /^Inheritance/ }),
        // `exact`, because `getByRole`'s name match is a case-insensitive substring: the career bar's
        // door and the race strip's own "Go to the run record screen" both contain this phrase.
        page.getByRole('link', { name: 'Run record', exact: true }),
        page.getByRole('link', { name: 'Dashboard' }).first(),
        page.getByRole('link', { name: 'Record the first turn on the run screen' }),
    ];

    for (const target of targets) {
        const box = await target.boundingBox();
        expect(box?.height ?? 0, 'a cockpit control is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
    }
});

test('records Energy through the cockpit correction and then marks one action', async ({ page }) => {
    await openCockpit(page);

    // A turn has to exist before there is a state to correct, so the run screen records one through
    // its own raw form — the surface this slice reuses rather than replaces.
    await page.getByRole('link', { name: 'Run record', exact: true }).click();
    await page.waitForURL(/\/training-runs\/\d+$/, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The run screen carries two forms whose fields share a name (the guided rail and this raw
    // form), so every locator is scoped to the disclosure rather than the page.
    const hatch = page.locator('details', { has: page.getByText('Correct a turn by hand') });
    await page.getByText('Correct a turn by hand').click();
    await hatch.locator('input[name="turn"]').fill('1');
    await hatch.locator('input[name="speed"]').fill('600');
    await hatch.locator('input[name="stamina"]').fill('700');
    await hatch.locator('input[name="power"]').fill('700');
    await hatch.locator('input[name="guts"]').fill('700');
    await hatch.locator('input[name="wit"]').fill('700');
    await hatch.getByRole('button', { name: 'Save correction' }).click();

    // The write settles before anything is read: Inertia patches the page in place, so waiting for a
    // navigation would resolve against the response that is still in flight. The flash banner is the
    // run screen's own proof that the turn landed.
    await expect(page.getByText('Turn 1 logged.')).toBeVisible();

    // Back on the cockpit, the correction disclosure opens on the stored turn and writes through the
    // route that already owns a turn: no second endpoint exists for it.
    await page.getByRole('link', { name: 'Career Cockpit' }).click();
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const correction = page.locator('details', { has: page.getByText('Correct turn 1 by hand') });
    await page.getByText('Correct turn 1 by hand').click();
    await correction.locator('input[name="energy"]').fill('60');
    await correction.getByRole('button', { name: 'Save correction' }).click();

    // The header is the proof the write landed, and the proof it landed where the form was posted:
    // a correction made on the Cockpit returns to the Cockpit rather than to the run screen.
    const header = page.locator('section[aria-labelledby="career-header-heading"]');
    await expect(header.getByText('60/100')).toBeVisible();

    // With Energy recorded, the advisor ranks: Speed carries the largest deficit, so it is the
    // recommendation, and exactly one entry carries the marker (Von Restorff, plan §13).
    await expect(page.getByText('Speed is 200 below target (the largest deficit).')).toBeVisible();
    await expect(page.getByText('At or above the advisory line')).toBeVisible();

    // Scoped to the Actions region, not the page: `getByText` matches case-insensitively, and the
    // card's own button reads "Go to the recommended action", which would make a page-wide count 2.
    const actions = page.getByRole('region', { name: 'Actions' });
    await expect(actions.getByText('RECOMMENDED')).toHaveCount(1);
    await expect(actions.getByRole('link', { name: /^Training.*RECOMMENDED/ })).toBeVisible();

    // The Trainer's own button, not an automatic execution.
    await expect(page.getByRole('link', { name: 'Go to the recommended action' })).toBeVisible();

    // The ranked state scans too: the accented recommendation card and the RECOMMENDED marker are the
    // two treatments the absent state does not carry.
    const ranked = await buildAxe(page).analyze();
    expect(ranked.violations, JSON.stringify(ranked.violations, null, 2)).toEqual([]);
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await openCockpit(page);

    // `buildAxe` scopes to `#app`, which is what makes this deterministic: Inertia's NProgress bar is
    // appended to `<body>` as a sibling of the app root and carries an invalid `role="bar"` (KI-63).
    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
