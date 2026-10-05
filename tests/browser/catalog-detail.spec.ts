import { test, expect } from '@playwright/test';

// Rendered-copy evidence for the ported Catalog detail page (SCR-CAT-002, ADR-0020 §1).
// The server-side tests (CatalogDetailPageTest, CatalogSkillListsTest, CatalogRosterTreeTest)
// assert the resolved props; these assert what the browser shows. Runs against the app's own
// scratch database (playwright.config.ts), so the fixtures are seeded rows: `special-week` has
// three costume forms, a profile and a provenance row; `silence-suzuka` has exactly one form.
//
// Not covered here, and it is not coverable by this suite: the positive "Not yet released on Global"
// notice. `UmamusumeRosterSeeder::inScope()` files `JapanOnly` rows as pending candidates rather than
// promoting them (PRD FR-A-1: this is a Global-client tool; measured 68 promoted of 135), so no
// `umamusume` row in any database this app builds can reach that branch, and rewriting the served page
// object in flight does not work either: Blade points at the Vite dev server on `localhost:5173`, and
// Chromium's private-network check refuses those asset requests for a `route.fulfill()`-synthesized
// document, so the page never hydrates. What is proven: the prop that drives the branch
// (`CatalogDetailPageTest`), and that the machine token stays off a real page (the last test below).

const EIGHT_SECTIONS = [
    'Basic information',
    'Aptitude',
    'Costume forms',
    'Skills',
    'Goal races',
    'Her runs',
    'Aliases',
    'Provenance',
];

test('renders the eight sections in the binding order', async ({ page }) => {
    await page.goto('/umamusume/special-week');
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Special Week', level: 1 })).toBeVisible();

    // WS-2 Task 2.5. The order is the workstream head's, not the alphabetical list the task
    // checklist uses to sweep them.
    const headings = await page.getByRole('heading', { level: 2 }).allTextContents();
    expect(headings).toEqual(EIGHT_SECTIONS);
});

test('prints the Japanese name in its own language and links the provenance source', async ({ page }) => {
    await page.goto('/umamusume/special-week');
    await page.locator('#app > *').first().waitFor();

    // WCAG 3.1.2 Language of Parts: the Japanese name carries lang="ja" so a screen reader
    // switches voice rather than reading the kana as English.
    await expect(page.locator('[lang="ja"]', { hasText: 'スペシャルウィーク' }).first()).toBeVisible();

    // The trainee's own fetch history is on screen as a real link, not a parked string.
    await expect(page.getByRole('link', { name: /character-cards/ }).first()).toBeVisible();
});

test('switches costume forms from the tab strip with the keyboard', async ({ page }) => {
    await page.goto('/umamusume/special-week');
    await page.locator('#app > *').first().waitFor();

    const tabs = page.getByRole('navigation', { name: 'Costume forms' }).getByRole('link');
    await expect(tabs).toHaveCount(3);

    // The active form is announced, not just styled (WCAG 1.3.1 / 4.1.2).
    await expect(tabs.first()).toHaveAttribute('aria-current', 'page');

    // Each tab is a 44px target, the house bar over WCAG 2.5.8's 24px floor.
    const heights = await tabs.evaluateAll((els) => els.map((el) => el.getBoundingClientRect().height));
    for (const height of heights) {
        expect(height).toBeGreaterThanOrEqual(44);
    }

    // Native links, so Tab reaches them and Enter follows them: the defect the port replaced was
    // a CSS-only radio group whose panels never left `hidden` and needed a submit to address.
    const second = tabs.nth(1);
    const secondTitle = ((await second.textContent()) ?? '').trim();

    await second.focus();
    await page.keyboard.press('Enter');

    await expect(page).toHaveURL(/form=\d+/);
    await expect(second).toHaveAttribute('aria-current', 'page');
    await expect(tabs.first()).not.toHaveAttribute('aria-current', 'page');
    // The panel switched to the form the tab names.
    await expect(page.getByRole('heading', { name: secondTitle, level: 3 })).toBeVisible();
});

test('renders no tab strip for a trainee with a single form', async ({ page }) => {
    await page.goto('/umamusume/silence-suzuka');
    await page.locator('#app > *').first().waitFor();

    // One form is not a choice, so there is no strip and no radio group: no chrome, not less page.
    await expect(page.getByRole('navigation', { name: 'Costume forms' })).toHaveCount(0);
    await expect(page.locator('input[type="radio"]')).toHaveCount(0);
    await expect(page.getByRole('heading', { name: 'Costume forms', level: 2 })).toBeVisible();
    await expect(page.getByRole('heading', { name: '[Innocent Silence]', level: 3 })).toBeVisible();
});

test('prints no release-status machine token and no notice for a Global trainee', async ({ page }) => {
    await page.goto('/umamusume/special-week');
    await page.locator('#app > *').first().waitFor();

    // The client word prints; the enum's backing value never reaches the page (EnumLabelTest
    // pins the prop, this pins the rendered DOM).
    const html = await page.content();
    expect(html).not.toContain('JapanOnly');

    await expect(page.getByText(/Not yet released on Global/)).toHaveCount(0);
});
