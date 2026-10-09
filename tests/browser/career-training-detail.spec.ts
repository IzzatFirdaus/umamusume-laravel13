import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';

/*
 * Rendered-copy and accessibility evidence for SCREEN-010, the Training Decision detail
 * (`SCR-CAR-012`, plan §8 D9). `CareerTrainingDetailTest` asserts the resolved props, including the
 * option payload's exact key set; these cases assert what only a browser reaches: the `N/A` sentences
 * and the exclusions on the disclosure that carries them, the disclosure by keyboard, the 44px sweep,
 * the reflow at 320px, the reduced-motion path, and that no projected yield or failure rate is printed
 * anywhere.
 *
 * The exclusion channel was a `title` until this pass. A `title` reaches a mouse and nothing else, so
 * these cases now address each exclusion through the summary's accessible name and open it by keyboard
 * (`AbsenceValue.vue`); the strings they pin are unchanged.
 *
 * Fixture strategy, and why it is the wizard walk: the run is built by pressing the app's own steps
 * and `Start Career`, then the first turn is recorded through Training Detail's own form, so every
 * figure this screen shows was entered rather than seeded. The row is deleted over HTTP in
 * `afterEach` (`tests/utils/delete-run.ts`), which keeps the shared
 * database clean for `runs.spec.ts`'s empty-state assertions. The wizard walk is copied rather than
 * imported because no browser spec in this tree shares one — `tests/utils/` holds the axe builder and
 * that teardown.
 *
 * Axe coverage: `@axe-core/playwright` is installed, so the last case scans this screen with the shared
 * builder and the same `wcag2a`/`wcag2aa`/`wcag21aa` tag set `accessibility.spec.ts` uses, scoped to
 * `#app`.
 */

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/**
 * One career with one logged turn, created the way a Trainer creates one.
 *
 * The wizard's target step sets every stat to 800 and the logged turn leaves Speed at 650 and the
 * other four at 600, so the largest deficit is 200 and it falls on Stamina, the first stat in the
 * matrix carrying it. Energy is logged at 62, above the sourced 50 advisory line, so the advisor ranks
 * rather than declines.
 *
 * `WRITE` is the 60s budget `career-cockpit.spec.ts` records: `php artisan serve` is a single-process
 * `php -S`, so a step's PUT queues behind whatever else is on the box.
 */
const WRITE = { timeout: 60_000 } as const;

async function newCareerWithTurn(page: import('@playwright/test').Page): Promise<string> {
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
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });

    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    // Turn 1, logged through Training Detail (Fix A): the numbers a Trainer reads off the client.
    // The retired run record screen's guided rail has no 2.0 reproduction (F2, plan §9.6; owner
    // ruling on group R-2), so Training Detail posts directly to `runs.turns.store` and the
    // response redirects to the Cockpit where the recorded turn appears in the correction selector.
    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    const speedCard = page.getByRole('article').filter({ hasText: /^Speed/ });
    await speedCard.getByRole('button', { name: 'Train' }).click();
    await page.locator('input[name="speed"]').fill('650');
    await page.locator('input[name="stamina"]').fill('600');
    await page.locator('input[name="power"]').fill('600');
    await page.locator('input[name="guts"]').fill('600');
    await page.locator('input[name="wit"]').fill('600');
    await page.locator('input[name="energy"]').fill('62');
    await page.locator('input[name="fans"]').fill('120');
    await page.locator('input[name="sp"]').fill('100');
    await page.locator('select[name="outcome"]').selectOption('Success');
    await page.getByRole('button', { name: 'Record this training' }).click();
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });
    await expect(page.getByText('Turn 1 logged.')).toBeVisible();

    return runUrl;
}

/** The Training Decision screen for a career that has logged one turn. */
async function openTrainingDetail(page: import('@playwright/test').Page): Promise<string> {
    const runUrl = await newCareerWithTurn(page);

    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    return runUrl;
}

/** The visible reason of an `AbsenceValue`: the span that follows its summary inside the `details`. */
const reasonOf = (disclosure: import('@playwright/test').Locator) =>
    disclosure.locator('xpath=following-sibling::span');

test('shows five cards, the sourced cost, and no projected number anywhere', async ({ page }) => {
    await openTrainingDetail(page);

    await expect(page.getByRole('heading', { name: 'Training decision' })).toBeVisible();
    await expect(page.getByRole('heading', { name: 'Training options' })).toBeVisible();

    for (const stat of ['Speed', 'Stamina', 'Power', 'Guts', 'Wit']) {
        await expect(page.getByRole('heading', { name: stat })).toBeVisible();
    }

    // The cost prints as the range the constant is, worst first, and names its source in the title.
    await expect(page.getByText('E−28 … E−17')).toHaveCount(4);

    // The exclusion evidence, as the two patterns below: the screen prints no projected gain per stat
    // and no failure percentage, because neither is sourced (AGENTS.md §5).
    await expect(page.locator('main')).not.toContainText(/\+\s?\d+\s?(Speed|Stamina|Power|Guts|Wit)/);
    await expect(page.locator('main')).not.toContainText(/Failure:?\s?\d+(\.\d+)?%/);
    // No em dash in shipped copy: an unrecorded value is `N/A` with a reason.
    await expect(page.locator('main')).not.toContainText('—');

    // Wit carries the engine's own word rather than a band computed from Energy it cannot have.
    await expect(page.getByText('RiskNotMeasured')).toBeVisible();
    await expect(page.getByText('E−0')).toBeVisible();

    // Exactly one RECOMMENDED marker, on the largest deficit, with the advisor's reason line beside it.
    await expect(page.getByText('RECOMMENDED')).toHaveCount(1);
    // The turn's band, in the Cockpit's words, readable on the screen the decision is made on.
    await expect(page.getByText('At or above the advisory line')).toBeVisible();
    const stamina = page.getByRole('article').filter({ hasText: /^Stamina/ });
    await expect(stamina.getByText('RECOMMENDED')).toBeVisible();
    await expect(stamina.getByText('Stamina is 200 below target (the largest deficit).')).toBeVisible();

    // The deficit is the advisor's arithmetic, and the deck count is entered data.
    await expect(stamina.getByText('200').first()).toBeVisible();
    await expect(page.getByText(/of six Support Card slots are recorded/)).toBeVisible();
});

test('names each exclusion on a disclosure a keyboard and a screen reader reach', async ({ page }) => {
    await openTrainingDetail(page);

    const speed = page.getByRole('article').filter({ hasText: /^Speed/ });

    // The exclusion rides in the summary's accessible name, so a screen reader states it before the
    // Trainer decides whether to open anything. Measured here: Chromium exposes the `<details>` as a
    // group whose name is `N/A , <reason>` and keeps the `<summary>` as the focusable element, so
    // `getByRole('button')` finds nothing and the summary is the honest anchor.
    const gains = speed.locator('summary', { hasText: 'No source publishes a per-training stat yield' });
    await expect(gains).toBeVisible();
    await expect(reasonOf(gains)).toBeHidden();

    // Opening it is a focus-and-press path, not a hover: the sighted keyboard route to the same sentence.
    await gains.focus();
    await expect(gains).toBeFocused();
    await page.keyboard.press('Enter');
    await expect(reasonOf(gains)).toHaveText(/Excluded from the advisor by design/);

    // The modifiers arrive on expansion, and each unsourced one says which rule excluded it.
    await speed.getByRole('button', { name: 'Inspect details' }).click();
    const details = speed.locator('#training-details-speed');
    await expect(details.getByText('Failure probability')).toBeVisible();
    await expect(details.locator('summary', { hasText: 'No source publishes a failure curve' })).toBeVisible();
    await expect(details.locator('summary', { hasText: 'Bond is not stored for a Support Card' })).toBeVisible();
    await expect(details.getByText('Scenario cap')).toBeVisible();
});

test('expands and collapses the details by keyboard, and keeps the tab order with the document', async ({ page }) => {
    await openTrainingDetail(page);

    const power = page.getByRole('article').filter({ hasText: /^Power/ });
    const toggle = power.getByRole('button', { name: 'Inspect details' });
    const details = page.locator('#training-details-power');
    const focusedLabel = () => page.evaluate(() => document.activeElement?.textContent?.trim().slice(0, 16) ?? '');

    await expect(details).toBeHidden();

    // Where the order leads while the region is shut, so the walk below can prove it returns here.
    await toggle.focus();
    await page.keyboard.press('Tab');
    const shutNext = await focusedLabel();
    await page.keyboard.press('Shift+Tab');

    await expect(toggle).toBeFocused();
    await expect(toggle).toHaveAttribute('aria-expanded', 'false');

    await page.keyboard.press('Enter');
    await expect(toggle).toHaveAttribute('aria-expanded', 'true');
    await expect(details).toBeVisible();
    await expect(details.getByText('Target impact')).toBeVisible();
    // The button keeps the same accessible name and keeps focus, so a keyboard Trainer is not moved
    // by their own press (WCAG 2.4.3).
    await expect(toggle).toBeFocused();

    // The region carries stops of its own now: an exclusion inside it is a disclosure, and a control a
    // Trainer must be able to reach is a tab stop. What holds is that the order is still the document's —
    // the stop after the toggle is the region's first exclusion, it opens under the same Enter that
    // opens it for a mouse, and closing the region takes the stops out again.
    await page.keyboard.press('Tab');
    const firstExclusion = details.locator('summary', { hasText: 'No source publishes a failure curve' });
    await expect(firstExclusion).toBeFocused();
    await page.keyboard.press('Enter');
    await expect(reasonOf(firstExclusion)).toHaveText(/ADR-0001 §3 keeps a numeric/);

    await page.keyboard.press('Shift+Tab');
    await expect(toggle).toBeFocused();
    await page.keyboard.press('Enter');
    await expect(toggle).toHaveAttribute('aria-expanded', 'false');
    await expect(details).toBeHidden();

    await page.keyboard.press('Tab');
    expect(await focusedLabel()).toBe(shutNext);
});

test('reaches the screen from the Cockpit and records the choice it carries', async ({ page }) => {
    const runUrl = await newCareerWithTurn(page);

    // The hydrate-and-navigate path is asserted as rendered behaviour: no console error and no
    // uncaught exception on the two documents this flow touches.
    const problems: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            problems.push(`console: ${message.text()}`);
        }
    });
    page.on('pageerror', (error) => problems.push(`pageerror: ${error.message}`));

    // The door D9 opens: the Cockpit's Training entry now leads here rather than to the run screen.
    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('link', { name: /^Training/ }).click();
    await page.waitForURL(/\/training$/, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Training decision' })).toBeVisible();
    expect(page.url()).toBe(`${runUrl}/training`);

    // `Train` holds the choice for the turn record and moves focus to it rather than posting a write
    // the Form Request would refuse for want of the numbers only the client can supply.
    const wit = page.getByRole('article').filter({ hasText: /^Wit/ });
    await wit.getByRole('button', { name: 'Train' }).click();
    await expect(page.getByRole('heading', { name: 'Record the turn' })).toBeFocused();
    await expect(page.getByText('Chosen: Wit')).toBeVisible();
    await expect(wit.getByRole('button', { name: 'Train' })).toHaveAttribute('aria-pressed', 'true');

    // The turn the form holds is the next one, and the last logged readings are placeholders only:
    // an input arriving pre-filled would assert the stat did not change (D-220).
    const speedInput = page.locator('input[name="speed"]');
    await expect(speedInput).toHaveValue('');
    await expect(speedInput).toHaveAttribute('placeholder', '650');
    await expect(page.locator('input[name="turn"]')).toHaveValue('2');

    // The page posts directly to the turn write the run screen already owns, with no `stage`
    // intermediate (F2, plan §9.6; owner ruling on group R-2 retired the preview-and-confirm rail).
    // The response redirects to the Cockpit, where the recorded turn appears in the correction
    // selector.
    await page.locator('input[name="speed"]').fill('700');
    await page.locator('input[name="stamina"]').fill('600');
    await page.locator('input[name="power"]').fill('600');
    await page.locator('input[name="guts"]').fill('600');
    await page.locator('input[name="wit"]').fill('640');
    await page.locator('input[name="energy"]').fill('40');
    await page.locator('select[name="outcome"]').selectOption('Success');
    await page.getByRole('button', { name: 'Record this training' }).click();
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });
    await expect(page.getByText('Turn 2 logged.')).toBeVisible();

    expect(problems, problems.join('\n')).toEqual([]);
});

test('holds its layout at 320px and honours reduced motion', async ({ page }) => {
    await openTrainingDetail(page);

    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.setViewportSize({ width: 320, height: 900 });

    // One column, no horizontal scroll (WCAG 1.4.10 Reflow).
    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );
    expect(overflow, 'no horizontal overflow at 320px').toBeLessThanOrEqual(0);

    for (const stat of ['Speed', 'Stamina', 'Power', 'Guts', 'Wit']) {
        await expect(page.getByRole('heading', { name: stat })).toBeVisible();
    }

    const motion = await page.getByRole('button', { name: 'Inspect details' }).first().evaluate((el) => {
        const style = getComputedStyle(el);

        return [Number.parseFloat(style.transitionDuration), Number.parseFloat(style.animationDuration)];
    });
    // `resources/css/app.css` answers the OS setting with the 0.01ms kill-switch, so a control that
    // carries any transition at all is quiet here (DESIGN.md MOTION, plan §12).
    expect(motion, 'the disclosure does not animate under reduced motion').toEqual([0.00001, 0.00001]);
});

test('meets the 44px target floor and the axe A plus AA bar', async ({ page }) => {
    await openTrainingDetail(page);

    await page.getByRole('button', { name: 'Inspect details' }).first().click();

    for (const name of ['Train', 'Inspect details']) {
        for (const target of await page.getByRole('button', { name }).all()) {
            const box = await target.boundingBox();
            expect(box?.height, `${name} reaches 44px`).toBeGreaterThanOrEqual(44);
        }
    }

    for (const field of ['speed', 'stamina', 'power', 'guts', 'wit', 'energy', 'turn']) {
        const box = await page.locator(`input[name="${field}"]`).boundingBox();
        expect(box?.height, `${field} input reaches 44px`).toBeGreaterThanOrEqual(44);
    }

    const results = await buildAxe(page).analyze();
    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
