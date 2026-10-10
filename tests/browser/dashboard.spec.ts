import { test, expect } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';

/*
 * Rendered-copy, state and accessibility evidence for SCREEN-001, the 2.0 landing screen
 * (docs/proposals/screen-spec-2.0.md; ADR-0020 §1). `DashboardTest` asserts the resolved props;
 * these assert what the browser shows and what only a browser can reach: the empty state's words,
 * the data-status badge, the 44px sweep, the keyboard path into the run, `lang="ja"` on the
 * Japanese name, and the page's own loading and error regions.
 *
 * Fixture strategy. The seeded scratch database holds zero training runs, which is the precondition
 * `playwright.config.ts`, `legacy.spec.ts` and `runs.spec.ts` all state, so the empty-career state is
 * asserted as itself. The active-career case creates its own run through the create form and deletes
 * it over HTTP in `afterEach` (`tests/utils/delete-run.ts`), so this spec leaves the database
 * exactly as it found it (the `run-detail.spec.ts` pattern).
 *
 * What is not browser-asserted, and why: the populated numbers on the card (a real turn count, a
 * recorded Energy, a career position). Reaching them needs a turn logged through the run page's
 * guided rail, which is `run-detail.spec.ts`'s surface; `DashboardTest` pins the props that drive
 * them, including the position arithmetic on the client's 24-turn grid. A newly created run has
 * logged nothing, so the states this spec can reach on one are the absent ones, which is where the
 * copy matters most.
 *
 * Axe coverage is provided by `tests/browser/accessibility.spec.ts`: `@axe-core/playwright` is installed
 * and scans pages against `wcag2a`, `wcag2aa`, and `wcag21aa`; this spec retains the hand-rolled checks
 * for target size, keyboard path, focus order, reflow and console errors.
 */

const TRAINEE = 'Agnes Digital';
const TRAINEE_JA = 'アグネスデジタル';
const EMPTY_CAREER = 'No active career. Start a new training run.';

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/** One `Active` run on the seeded trainee, created through the app's own form. */
async function createActiveRun(page: import('@playwright/test').Page, scenario: string): Promise<string> {
    await page.goto('/training-runs/create');
    await page.locator('#app > *').first().waitFor();

    await page.locator('#trainee-combobox').fill(TRAINEE);
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: scenario });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/);

    const url = page.url();
    createdRunUrls.push(url);

    return url;
}

test('states the empty career in words a Trainer can act on', async ({ page }) => {
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    // design-2.0 §29: what is missing, why it matters, what to do. The brief's own sentence is the
    // first line; the second says what a career is for, so the panel is not just a refusal.
    await expect(page.getByText(EMPTY_CAREER)).toBeVisible();
    await expect(page.getByText(/A career keeps your turns, Energy and race record/)).toBeVisible();
    await expect(page.getByRole('link', { name: 'Start a new training run' })).toBeVisible();

    // The snapshot door is the other way in, and it now sits outside both career states (R2-04).
    await expect(page.getByRole('link', { name: 'Import an existing career' })).toBeVisible();

    // Paradox of the Active User (plan §13): no onboarding wall. The rest of the page is usable on
    // first load, so the quick actions and the data panels are present with no career at all.
    const quickActions = page.getByRole('navigation', { name: 'Quick actions' });
    for (const label of ['New Career', 'Legacy Lab', 'Support Cards']) {
        await expect(quickActions.getByRole('link', { name: label })).toBeVisible();
    }

    // The two panels with no source name the absence rather than rendering an empty box.
    await expect(page.getByText('No Veterans recorded yet.')).toBeVisible();
    await expect(page.getByText('No builds recorded yet.')).toBeVisible();
});

test('reads the data-status badge, its verification date and the named ruleset', async ({ page }) => {
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    const status = page.getByRole('region', { name: 'DATA STATUS' });

    // Glyph plus text: the bullet is `aria-hidden` and the word "Current" carries the state, so
    // nothing here is shown through colour alone (WCAG 1.4.1).
    await expect(status.getByText('GLOBAL DATA')).toBeVisible();
    await expect(status.getByText('Current', { exact: true })).toBeVisible();
    await expect(status.getByText('Verified 2026-09-27')).toBeVisible();

    // `app.ruleset` is null, so the row is N/A with its reason on the element rather than a version
    // this tool does not hold (design-2.0 §48).
    await expect(status.getByText('Ruleset:')).toBeVisible();
    await expect(status.locator('[title*="No source defines a Global ruleset version"]')).toHaveText('N/A');
});

test('sizes the dashboard controls to the 44px contract', async ({ page }) => {
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    // WCAG 2.2 SC 2.5.8, floor 44px (plan §12.1). The same sweep `skills.spec.ts` and
    // `legacy.spec.ts` run, repeated here because this screen's controls are new.
    const targets = [
        page.getByRole('link', { name: 'Start a new training run' }),
        page.getByRole('navigation', { name: 'Quick actions' }).getByRole('link', { name: 'New Career' }),
        page.getByRole('navigation', { name: 'Quick actions' }).getByRole('link', { name: 'Legacy Lab' }),
        page.getByRole('navigation', { name: 'Quick actions' }).getByRole('link', { name: 'Support Cards' }),
        page.getByRole('link', { name: 'Open the Legacy Lab' }),
    ];

    for (const target of targets) {
        const box = await target.boundingBox();
        expect(box?.height ?? 0, 'a dashboard control is not sized to the 44px contract').toBeGreaterThanOrEqual(44);
    }
});

test('names the active career and resumes it from the keyboard', async ({ page }) => {
    await createActiveRun(page, 'unity_cup');

    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    const card = page.getByRole('region', { name: 'ACTIVE CAREER' });

    // The trainee, with her Japanese name in its own language (WCAG 3.1.2).
    await expect(card.getByText(TRAINEE)).toBeVisible();
    const japanese = card.locator(`[lang="ja"]`);
    await expect(japanese).toHaveText(TRAINEE_JA);

    // The scenario's label from the matrix, never its storage key (D-240).
    await expect(card.getByText('Unity Cup')).toBeVisible();

    // A brand-new run has logged nothing, so the position and Energy are absent states: N/A with a
    // reason, never a zero and never Early January (D-220).
    await expect(card.getByTitle(/not standing anywhere on the calendar yet/)).toHaveText('N/A');
    await expect(card.getByTitle(/No logged turn has recorded Energy/)).toHaveText('N/A');

    // The scenario's own resource, resolved from `config('scenarios')` by subtracting the baseline's
    // widget list. No column records it yet, so it is N/A with that reason attached.
    await expect(card.getByText('Team Rank')).toBeVisible();
    await expect(card.getByTitle(/No column records this scenario resource yet/)).toHaveText('N/A');

    // R2-04: this is the state the snapshot door exists for. A Trainer already partway through a
    // career in the client is the one who has a career to resume here, so the door has to be on the
    // screen they land on rather than only on the empty one. It is not one of the resume card's own
    // controls, so it is read from the page.
    const door = page.getByRole('link', { name: 'Import an existing career' });
    await expect(door).toBeVisible();
    await expect(door).toHaveAttribute('href', '/career/snapshot');

    // The primary action, reached and taken by keyboard alone.
    const resume = card.getByRole('link', { name: 'Resume Career' });
    const box = await resume.boundingBox();
    expect(box?.height ?? 0, 'Resume Career is not sized to the 44px contract').toBeGreaterThanOrEqual(44);

    await resume.focus();
    await expect(resume).toBeFocused();
    await page.keyboard.press('Enter');
    // Resuming continues the career, so it lands on the Cockpit (SCREEN-009), not the record sheet.
    await page.waitForURL(/\/cockpit$/);
});

test('reflows at 320px, logs no console error, and renders under reduced motion', async ({ page }) => {
    const consoleErrors: string[] = [];
    page.on('console', (message) => {
        if (message.type() === 'error') {
            consoleErrors.push(message.text());
        }
    });
    page.on('pageerror', (error) => consoleErrors.push(error.message));

    // WCAG 1.4.10 Reflow: at 320px the document must not scroll in two dimensions. `legacy.spec.ts`
    // runs the same check on its compare table; this screen has no scrollable region of its own, so
    // the document is the whole claim.
    await page.setViewportSize({ width: 320, height: 720 });
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1,
    );
    expect(overflow, 'the document scrolls horizontally at 320px').toBe(true);

    // WCAG 2.3.3 / the plan's §12 reduced-motion path: the screen is fully usable with the OS
    // preference set. The MOTION dial is 1, so this asserts the page renders and its primary action
    // is reachable rather than that a specific animation was suppressed.
    await expect(page.getByRole('link', { name: 'Start a new training run' })).toBeVisible();
    await expect(page.getByRole('navigation', { name: 'Quick actions' })).toBeVisible();

    expect(consoleErrors, `console errors: ${consoleErrors.join(' | ')}`).toEqual([]);
});

test('announces a visit in flight and takes over the failure state', async ({ page }) => {
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    // Loading: hold the response so the in-flight state is observable rather than raced. This page's
    // only user-initiated async action is following one of its links (ADR-0007), so the state is a
    // status line rather than a skeleton (design-2.0 §30).
    await page.route('**/career/setup/scenario', async (route) => {
        await new Promise((resolve) => setTimeout(resolve, 1000));
        await route.continue();
    });

    await page.getByRole('navigation', { name: 'Quick actions' }).getByRole('link', { name: 'New Career' }).click();
    await expect(page.getByRole('status')).toHaveText('Loading…');
    await page.waitForURL(/\/career\/setup\/scenario$/);

    await page.unroute('**/career/setup/scenario');

    // Error: a response Inertia cannot read fires `invalid`, and the page's own alert is the surface
    // because the listener suppresses Inertia's default modal.
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();

    await page.route('**/support-cards', (route) =>
        route.fulfill({ status: 200, contentType: 'text/html', body: '<html><body>not an Inertia response</body></html>' }),
    );

    await page.getByRole('navigation', { name: 'Quick actions' }).getByRole('link', { name: 'Support Cards' }).click();
    await expect(page.getByRole('alert')).toContainText('That page could not be loaded');
});
