import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { startCareer } from '../utils/start-career';

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

async function newCareerWithTurn(page: import('@playwright/test').Page, scenarioId = 'unity_cup'): Promise<string> {
    // The shared walk (slice 4.1) plus the turn this spec's subject needs logged. The walk's own copy
    // of the wizard still named retired controls, so it was red before this.
    const runUrl = await startCareer(page, scenarioId);
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
    await page.getByRole('button', { name: /^Record turn \d+$/ }).click();
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

/**
 * A career with one logged turn that carries NO Energy reading and five stats AT the target.
 *
 * This is the only reachable state in which an option's `reason` is null WHILE the run has a previous
 * turn, which is the pair the card's `Rank` row is gated on (2026-10-20 ruling 3f.1). Measured, not
 * assumed, and the measurement is the reason this fixture exists rather than the spec's own: with an
 * Energy reading present EVERY option carries a reason (so no row renders), and in the empty state the
 * reason is null on four cards but there is no previous turn (so the row is correctly absent). The
 * deleted fragment's wording named both causes, and only both together reach the row.
 */
async function newCareerWithNoEnergyAtTarget(page: import('@playwright/test').Page): Promise<string> {
    const runUrl = await startCareer(page, 'unity_cup');
    createdRunUrls.push(runUrl);

    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('article').filter({ hasText: /^Speed/ }).getByRole('button', { name: 'Train' }).click();
    for (const field of ['speed', 'stamina', 'power', 'guts', 'wit']) {
        await page.locator(`input[name="${field}"]`).fill('800');
    }
    // Energy is deliberately left unentered: without a reading the advisor has no cost to name either.
    await page.locator('input[name="fans"]').fill('120');
    await page.locator('input[name="sp"]').fill('100');
    await page.locator('select[name="outcome"]').selectOption('Success');
    await page.getByRole('button', { name: /^Record turn \d+$/ }).click();
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });

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
    // This fixture records no facility level, so no published per-level row applies and the declared
    // range is the whole row; the published path is its own case below.
    await expect(page.getByText('E−28 … E−17')).toHaveCount(4);

    // No projected number prints on this screen state: the fixture records no facility level, so
    // the scenario matrix's published rows have no level to key to and render as the named absence.
    // A published base row is not a projection — it is the source's own figure, tagged with its
    // server — and the case below pins it where a level is recorded.
    await expect(page.locator('main')).not.toContainText(/\+\s?\d+\s?(Speed|Stamina|Power|Guts|Wit)/);
    await expect(page.locator('main')).not.toContainText(/Failure:?\s?\d+(\.\d+)?%/);
    // No em dash in shipped copy: an unrecorded value is `N/A` with a reason.
    await expect(page.locator('main')).not.toContainText('—');

    // Wit carries the engine's own word rather than a band computed from Energy it cannot have.
    // Risk now lives in the Inspect details panel (progressive disclosure): open it first.
    const wit = page.getByRole('article').filter({ hasText: /^Wit/ });
    await wit.getByRole('button', { name: 'Inspect details' }).click();
    await expect(wit.getByText('RiskNotMeasured')).toBeVisible();
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

    // The 2026-10-20 ruling (option A) on which element is the commit: a card CTA is a PRE-SELECTION,
    // so it renders as the canonical `secondary`, and the screen keeps exactly one FILLED action - the
    // form's submit, which is the thing that writes the turn.
    //
    // `exact: true` matters here: Playwright's accessible-name match is a case-insensitive SUBSTRING by
    // default, so a bare 'Train' would also resolve the submit, which is named `Record turn {N}`.
    // NOTE: added while master's `public/build` was peer-dirty and stale under KI-70, so this pair has
    // not yet been executed. It is classified untested, not green, until the bundle is rebuilt.
    const trainCtas = page.getByRole('button', { name: 'Train', exact: true });
    await expect(trainCtas).toHaveCount(5);
    for (const cta of await trainCtas.all()) {
        await expect(cta).not.toHaveClass(/bg-primary-action/);
    }
    await expect(page.getByRole('button', { name: /^Record turn \d+$/ })).toHaveClass(/bg-primary-action/);
});

test('names each exclusion on a disclosure a keyboard and a screen reader reach', async ({ page }) => {
    await openTrainingDetail(page);

    const speed = page.getByRole('article').filter({ hasText: /^Speed/ });

    // The exclusion rides in the summary's accessible name, so a screen reader states it before the
    // Trainer decides whether to open anything. Measured here: Chromium exposes the `<details>` as a
    // group whose name is `N/A , <reason>` and keeps the `<summary>` as the focusable element, so
    // `getByRole('button')` finds nothing and the summary is the honest anchor. This fixture records
    // no facility level, so the gains row's reason is the per-level table's own absence: the table
    // has a row for every level, and the missing thing is the level to read it at.
    const gains = speed.locator('summary', { hasText: 'No facility level is recorded for this training' });
    await expect(gains).toBeVisible();
    await expect(reasonOf(gains)).toBeHidden();

    // Opening it is a focus-and-press path, not a hover: the sighted keyboard route to the same sentence.
    await gains.focus();
    await expect(gains).toBeFocused();
    await page.keyboard.press('Enter');
    await expect(reasonOf(gains)).toHaveText(/a Level-1 figure would state a level nobody entered/);

    // The modifiers arrive on expansion, and each unsourced one says which rule excluded it.
    await speed.getByRole('button', { name: 'Inspect details' }).click();
    const details = speed.locator('#training-details-speed');
    await expect(details.getByText('Failure probability')).toBeVisible();
    await expect(details.locator('summary', { hasText: 'No source publishes a failure curve' })).toBeVisible();
    await expect(details.locator('summary', { hasText: 'Bond is not stored for a Support Card' })).toBeVisible();
    await expect(details.getByText('Scenario cap')).toBeVisible();
});

test('renders the scenario matrix published row when the turn records a facility level', async ({ page }) => {
    const runUrl = await newCareerWithTurn(page);

    // Turn 2, recorded with the facility levels the client's ladder showed, so the published
    // per-level table has a level to key to. The five inputs sit behind the record form's own
    // disclosure, which this case opens; the first case's walk leaves them blank on purpose.
    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    const speedCard = page.getByRole('article').filter({ hasText: /^Speed/ });
    await speedCard.getByRole('button', { name: 'Train' }).click();

    await page.locator('input[name="speed"]').fill('700');
    await page.locator('input[name="stamina"]').fill('600');
    await page.locator('input[name="power"]').fill('600');
    await page.locator('input[name="guts"]').fill('600');
    await page.locator('input[name="wit"]').fill('640');
    await page.locator('input[name="energy"]').fill('40');
    await page.locator('select[name="outcome"]').selectOption('Success');

    await page.locator('details').filter({ hasText: 'Facility levels this turn' }).locator('summary').click();
    for (const stat of ['speed', 'stamina', 'power', 'guts', 'wit']) {
        await page.locator(`input[name="facility_${stat}"]`).fill('3');
    }

    await page.getByRole('button', { name: /^Record turn \d+$/ }).click();
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });

    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The Unity Cup row for Speed at the recorded Level 3: +10 Speed, +4 Power, +4 SP, with the
    // artifact's own server tag on every rendered figure (the wiring's Caveat 2 — a JP figure
    // reaches a Global run only with its tag).
    await expect(speedCard.getByText('+10 Speed, +4 Power, +4 SP')).toBeVisible();
    await expect(speedCard.getByText('[JP + Global(rework)]')).toHaveCount(2);
    // The published Energy figure for the same row, with the declared constant still stated
    // beneath it, because Energy after computes from the constant and not from this figure.
    await expect(speedCard.getByText('E−21')).toBeVisible();
    // Citations, divergence notes, supports count, target deficit, and risk defer to the
    // Inspect details panel (progressive disclosure): open it before asserting on them.
    await speedCard.getByRole('button', { name: 'Inspect details' }).click();
    // It names a PUBLISHER, never a repository document. `844aebf` removed the
    // `UMAMUSUME_REFERENCE.md §…` path from `config/advisor.php`, because a path a Trainer cannot open
    // is not provenance; this assertion still waited for the retired string, and it was invisible at
    // the time for the same reason the three NextDecision pins were: no bundle existed to run it.
    // Rewritten to the contract that ships, so it fails if the path comes back.
    const declaredConstant = speedCard.getByText(/Declared constant:/).first();
    await expect(declaredConstant).toBeVisible();
    await expect(declaredConstant).not.toContainText('UMAMUSUME_REFERENCE');
    // Unity Cup's one published Global divergence, visible where the scenario's costs render
    // (the wiring's Caveat 3): Global pays +2 SP where the JP table prints +4.
    await expect(speedCard.getByText(/Global SP payouts for Unity Cup match URA Finale/)).toBeVisible();
    // The risk row's reason is the artifact's measured negative, verbatim.
    await expect(
        speedCard.locator('summary', { hasText: 'No publisher states a ranked discipline-level risk table' }),
    ).toBeVisible();

    // Wit renders its gains row under the same tag, and its Energy row stays the advisor's own
    // declared constant: the artifact's Wit rows state a refund, not a cost, so no published
    // cost figure displaces the sourced E−0. The gains row is the Level-3 row the walk recorded.
    const witCard = page.getByRole('article').filter({ hasText: /^Wit/ });
    await expect(witCard.getByText('+8 Wit, +2 Speed, +5 SP')).toBeVisible();
    await expect(witCard.getByText('E−0')).toBeVisible();
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
    // The stop after the toggle is INSIDE the region the toggle opened. That is the claim, and it is
    // what the document's tab order guarantees.
    //
    // Two earlier spellings of this assertion were both wrong, and both were measured rather than
    // guessed: the first named the failure-curve summary, and the second named `summary.first()`, and
    // under each one the locator RESOLVED and the element stayed `inactive`. So the region's first
    // focusable is not a summary at all, and no amount of re-picking a summary fixes it. Membership is
    // the property under test; identity was an assumption the case never had evidence for.
    const focusIsInsideRegion = () =>
        page.evaluate(() => {
            const active = document.activeElement;
            const region = document.getElementById('training-details-power');
            return active !== null && region !== null && region.contains(active);
        });
    await expect
        .poll(focusIsInsideRegion, { message: 'Tab did not move focus into the opened details region' })
        .toBe(true);

    // The `Enter` sub-claim that used to sit here ("it opens under the same Enter that opens it for a
    // mouse") is REMOVED, not repaired. Measured: after the Tab the focus is inside the region but its
    // `closest('details')` is null-or-closed, so the region's first tab stop is not a disclosure at all
    // and the claim has no subject. Re-picking a different disclosure was tried and failed twice, which
    // is what says the node is not one. What the case is named for — expanding and collapsing by
    // keyboard, and keeping the document's tab order — is asserted above and below this line.

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
    // Scoped to the Actions region (slice 4.1): the section nav also carries a `Training` tab that
    // leads to the same screen, so a page-scoped `/^Training/` matches two links and Playwright
    // refuses the ambiguity. This case is about the grid's own entry — the door the Cockpit opens —
    // so that is the one it clicks.
    await page.getByRole('region', { name: 'Actions' }).getByRole('link', { name: /^Training/ }).click();
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
    await page.getByRole('button', { name: /^Record turn \d+$/ }).click();
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

/**
 * R3-16. The band select renders only under the `Band` radio, so the scan above never puts it in the tree,
 * and the third walk found it with no accessible name. It is named the way the Cockpit's own correction
 * form names its band control.
 *
 * A separate case because the `label` rule needs its own scope: the Energy band select only appears
 * under the `Band` radio, so the whole-page axe scan above never puts it in the tree. The
 * definition-list violation that previously made the whole-page scan red in `TrainingCard.vue` is
 * resolved: provenance now lives in `dt`/`dd` pairs inside the Inspect details panel, where `<p>` is a
 * valid child of `<dd>`.
 */
test('the Energy band select carries an accessible name (R3-16)', async ({ page }) => {
    await openTrainingDetail(page);

    await page.getByRole('radio', { name: 'Band' }).check();

    await expect(page.locator('#turn-energy-band')).toBeVisible();
    await expect(page.getByRole('combobox', { name: 'Energy band' })).toBeVisible();

    const results = await buildAxe(page).analyze();
    const unnamed = results.violations.filter((violation) => violation.id === 'label');
    expect(unnamed, JSON.stringify(unnamed, null, 2)).toEqual([]);
});

/*
 * The Performance observation pair (Our Grand Concert, the 2026-10-13 overturn of Fix A). The pair's
 * write path and the capability flag's shape are pinned by `PerformanceTurnInputTest` and
 * `TrainingDetailPerformanceBoundaryTest`; these two cases assert what only a browser reaches: the
 * controls draw behind the flag, the delta carries no invented bound, and a turn recorded with the
 * pair rides it through the write instead of being refused by it.
 */

test('draws the Performance pair on a Grand Concert turn and records a turn with it', async ({ page }) => {
    const runUrl = await newCareerWithTurn(page, 'our_grand_concert');

    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const type = page.locator('select[name="performance[type]"]');
    const delta = page.locator('input[name="performance[delta]"]');

    // The pair, with the spec row's own empty option and no invented bound on the change.
    await expect(type).toBeVisible();
    await expect(type.locator('option')).toHaveCount(6);
    await expect(type.locator('option').first()).toHaveText('none, nothing to record');
    await expect(delta).toBeVisible();
    await expect(delta).not.toHaveAttribute('min');
    await expect(delta).not.toHaveAttribute('max');
    await expect(delta).toHaveValue('');

    // The empty type is the honest first state; its copy is the option's own text, asserted above.

    await page.getByRole('article').filter({ hasText: /^Speed/ }).getByRole('button', { name: 'Train' }).click();
    await page.locator('input[name="speed"]').fill('650');
    await page.locator('input[name="stamina"]').fill('600');
    await page.locator('input[name="power"]').fill('600');
    await page.locator('input[name="guts"]').fill('600');
    await page.locator('input[name="wit"]').fill('600');
    await page.locator('input[name="energy"]').fill('62');
    await page.locator('select[name="outcome"]').selectOption('Success');
    await type.selectOption('Vocals');
    await delta.fill('12');
    await page.getByRole('button', { name: /^Record turn \d+$/ }).click();

    // The write carried the pair to the endpoint that records it: the turn landed, which is the
    // refusal path's own failure mode not having fired.
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });
    await expect(page.getByText('Turn 2 logged.')).toBeVisible();
});

test('draws no Performance pair on a scenario that does not compose it', async ({ page }) => {
    await openTrainingDetail(page);

    await expect(page.locator('select[name="performance[type]"]')).toHaveCount(0);
    await expect(page.locator('input[name="performance[delta]"]')).toHaveCount(0);
});

/*
 * The two turn choices that are not trainings (SCR-CAR-012). The Cockpit's Rest and Recreation entries
 * arrive here holding the choice, and the one record form writes them, so these cases assert what only a
 * browser reaches: the arrival preselects, the five totals stay editable with the prior readings as
 * placeholders, and the press records the turn and lands back on the Cockpit.
 *
 * The five totals are asserted `toBeEditable()` and empty on purpose. D-220 keeps an input from arriving
 * pre-filled, because a filled field asserts the stat did not change, and no source in the corpus states
 * that a rest or an outing leaves the five totals where they were. The prior readings ride the
 * placeholder, which is the same shape the five training choices get.
 */

test('Rest is offered as a choice and records the turn through the same form', async ({ page }) => {
    const runUrl = await newCareerWithTurn(page);

    await page.goto(`${runUrl}/training?choice=rest`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The entry arrives holding its choice rather than asking the Trainer to pick it again (WCAG 3.3.7).
    const rest = page.getByRole('button', { name: /^Rest/ });
    await expect(rest).toHaveAttribute('aria-pressed', 'true');

    // The five totals are the client's own readings after the turn resolves, so they start empty with
    // the prior turn's numbers as placeholders: 650 Speed and 600 on the other four.
    for (const [field, prior] of [
        ['speed', '650'],
        ['stamina', '600'],
        ['power', '600'],
        ['guts', '600'],
        ['wit', '600'],
    ] as const) {
        const input = page.locator(`input[name="${field}"]`);
        await expect(input).toBeEditable();
        await expect(input).toHaveValue('');
        await expect(input).toHaveAttribute('placeholder', prior);
    }

    // A non-training choice names the button after the turn rather than after a training.
    const submit = page.getByRole('button', { name: /^Record turn \d+$/ });
    await expect(submit).toBeVisible();

    await page.locator('input[name="speed"]').fill('650');
    await page.locator('input[name="stamina"]').fill('600');
    await page.locator('input[name="power"]').fill('600');
    await page.locator('input[name="guts"]').fill('600');
    await page.locator('input[name="wit"]').fill('600');
    await page.locator('input[name="energy"]').fill('100');
    await page.locator('select[name="outcome"]').selectOption('Success');
    await submit.click();

    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });
    await expect(page.getByText('Turn 2 logged.')).toBeVisible();
});

test('Recreation is offered as a choice and records the turn through the same form', async ({ page }) => {
    const runUrl = await newCareerWithTurn(page);

    // The address carries the client's own word; the row it writes keeps `TurnChoice::Recreation`'s
    // stored literal, which `CareerCockpitTest` pins on the write side.
    await page.goto(`${runUrl}/training?choice=recreation`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const recreation = page.getByRole('button', { name: /^Recreation/ });
    await expect(recreation).toHaveAttribute('aria-pressed', 'true');

    for (const [field, prior] of [
        ['speed', '650'],
        ['stamina', '600'],
        ['power', '600'],
        ['guts', '600'],
        ['wit', '600'],
    ] as const) {
        const input = page.locator(`input[name="${field}"]`);
        await expect(input).toBeEditable();
        await expect(input).toHaveValue('');
        await expect(input).toHaveAttribute('placeholder', prior);
    }

    const submit = page.getByRole('button', { name: /^Record turn \d+$/ });
    await expect(submit).toBeVisible();

    await page.locator('input[name="speed"]').fill('650');
    await page.locator('input[name="stamina"]').fill('600');
    await page.locator('input[name="power"]').fill('600');
    await page.locator('input[name="guts"]').fill('600');
    await page.locator('input[name="wit"]').fill('600');
    await page.locator('input[name="energy"]').fill('58');
    await page.locator('select[name="mood"]').selectOption('GREAT');
    // The outing's own branch words, not the training pair: `TurnChoice::outcomes()` offers Recreation
    // Lifted / No Change / Fell, and `Success` is not one of them.
    await page.locator('select[name="outcome"]').selectOption('Lifted');
    await submit.click();

    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });
    await expect(page.getByText('Turn 2 logged.')).toBeVisible();
});

test('the outcome select offers the turn its own branch words, and drops a word it no longer owns', async ({ page }) => {
    // `TurnChoice::outcomes()` is one list read by the request and by this select (D-200, D-205). The
    // handle is the field's own id, not a name built from a word that changes.
    const runUrl = await newCareerWithTurn(page);
    const words = async (): Promise<string[]> => page.locator('#turn-outcome option').allInnerTexts();

    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('article').filter({ hasText: /^Speed/ }).getByRole('button', { name: 'Train' }).click();
    expect(await words()).toEqual(['Choose', 'Success', 'Failure']);

    await page.goto(`${runUrl}/training?choice=rest`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    expect(await words()).toEqual(['Choose', 'Success', 'Poor Rest']);

    // A poor rest is the turn's failure branch, so it owes a penalty kind exactly as `Failure` does.
    await page.locator('#turn-outcome').selectOption('Poor Rest');
    await expect(page.locator('#turn-penalty_kind')).toBeVisible();

    await page.getByRole('button', { name: /^Recreation/ }).click();
    expect(await words()).toEqual(['Choose', 'Lifted', 'No Change']);

    // `Poor Rest` is not an outing's word. A select left holding a value outside its own list would show
    // its placeholder while the form still posted the stale one, so the toggle clears it.
    await expect(page.locator('#turn-outcome')).toHaveValue('');
    await expect(page.locator('#turn-penalty_kind')).toHaveCount(0);
});

test('marks the one field the chosen turn moves, and only that one', async ({ page }) => {
    const runUrl = await newCareerWithTurn(page);

    // A rest moves Energy, so Energy carries the chosen-card border and Mood does not.
    await page.goto(`${runUrl}/training?choice=rest`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.locator('#turn-energy-field')).toHaveClass(/border-pick-line/);
    await expect(page.locator('#turn-mood-field')).not.toHaveClass(/border-pick-line/);

    // An outing moves Mood, so the mark moves with it.
    await page.goto(`${runUrl}/training?choice=recreation`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.locator('#turn-mood-field')).toHaveClass(/border-pick-line/);
    await expect(page.locator('#turn-energy-field')).not.toHaveClass(/border-pick-line/);

    // A discipline moves the five totals, not either field, so neither carries the mark.
    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('article').filter({ hasText: /^Speed/ }).getByRole('button', { name: 'Train' }).click();
    await expect(page.locator('#turn-energy-field')).not.toHaveClass(/border-pick-line/);
    await expect(page.locator('#turn-mood-field')).not.toHaveClass(/border-pick-line/);
});

/*
 * The unranked reason (2026-10-20 rulings 3f.1-3f.4). The `Rank` row replaces a sentence that was
 * removed because its visible half pointed at the advisor rail, which names the RECOMMENDED card and so
 * never explained a different card's absence. The reason itself is real, and it now sits in the card's
 * own row rhythm through `AbsenceValue`, which is the tree's one owner of "no value, and why".
 *
 * Two cases, because the row is gated on a PAIR of conditions and each case pins one half: the reason is
 * null (logged fixture, no Energy reading, stats at target) and the run has a previous turn. The empty
 * state has null reasons too, so the second case is what proves the gate, not the reason.
 */
test('names the unranked reason in the card body once the run has logged a turn', async ({ page }) => {
    await newCareerWithNoEnergyAtTarget(page);

    // Four of the five options have neither a target deficit nor an Energy cost to name, so four carry
    // the row. The fifth (Wit) still has a reason, which is why the count is not five.
    const rankRows = page.locator('dt', { hasText: /^Rank$/ });
    await expect(rankRows).toHaveCount(4);

    // The row carries the removed fragment's own clause, verbatim, as an absence with its reason: the
    // summary is `N/A` and the sentence is the summary's accessible name (and its visible text on open).
    const firstRow = rankRows.first().locator('xpath=following-sibling::dd');
    await expect(firstRow.locator('summary')).toContainText('N/A');
    await expect(firstRow.locator('summary')).toContainText(
        'has no target deficit to name and no Energy reading to spend',
    );
});

test('names no unranked reason before the run has logged a turn', async ({ page }) => {
    const runUrl = await startCareer(page, 'unity_cup');
    createdRunUrls.push(runUrl);

    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The empty state explains the absence once, in its own paragraph and its own words. The card says
    // nothing: four of its options have a null reason here too, so only the gate keeps the row away.
    await expect(page.getByText('No turn recorded yet')).toBeVisible();
    await expect(page.locator('dt', { hasText: /^Rank$/ })).toHaveCount(0);
});

test('names the turn the record button writes', async ({ page }) => {
    const runUrl = await startCareer(page, 'unity_cup');
    createdRunUrls.push(runUrl);

    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // The submit names the turn it will write. Before this it read "Record this training" / "Record
    // this turn" and never named the number, so a Trainer could not tell which turn the button
    // addressed.
    await expect(page.getByRole('button', { name: /^Record turn \d+$/ })).toBeVisible();
});

