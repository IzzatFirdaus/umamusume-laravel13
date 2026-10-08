import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';

// Rendered-copy evidence for the ported Preferences screen (SCR-SYS-002). The server-side
// PreferenceControlsTest asserts the props; this asserts what the browser actually shows.

test('Preferences renders its heading, both controls and the shell nav', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Preferences', level: 2 })).toBeVisible();

    // The theme control offers the three authorized values (follow-the-OS, light, dark).
    const theme = page.getByLabel('Theme');
    await expect(theme).toBeVisible();
    await expect(theme.locator('option')).toHaveText([
        'Follow the system',
        'Light',
        'Dark',
    ]);

    await expect(page.getByRole('checkbox', { name: 'Numeric failure estimate' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Save preferences' })).toBeVisible();

    // The shell's navigation is present (the sidebar on desktop).
    await expect(page.getByRole('link', { name: 'Dashboard' })).toBeVisible();
});

test('the shell navigation reaches Settings and carries no form', async ({ page }) => {
    // The nav carries the link and the form lives on its own screen, so the shell stays form-free
    // (which keeps the page-wide control counts honest). Client-rendered now, so asserted here.
    await page.goto('/umamusume');
    await page.locator('#app > *').first().waitFor();

    const nav = page.getByRole('navigation', { name: 'Primary' }).first();
    await expect(nav.getByRole('link', { name: 'Settings' })).toBeVisible();
    await expect(nav.locator('form')).toHaveCount(0);
    await expect(nav.locator('input[name="failure_estimate"]')).toHaveCount(0);
});

test('saving a preference confirms in place without a full reload', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('checkbox', { name: 'Numeric failure estimate' }).check();
    await page.getByRole('button', { name: 'Save preferences' }).click();

    // The flash status is rendered by the shell after the Inertia visit.
    await expect(page.getByText('Preferences saved.')).toBeVisible();

    // Assert the status region itself, not just DOM text: getByRole('status')
    // matches only accessibility-tree regions, so an `aria-hidden` glyph cannot
    // satisfy it (the known `legacy.spec.ts` failure mode), and toHaveText pins
    // the message. getByRole('status', { name }) is not used: the `status` role
    // is not an ARIA name-from-content role, so Playwright never matches its
    // content-derived name.
    const status = page.getByRole('status');
    await expect(status).toBeVisible();
    await expect(status).toHaveText('Preferences saved.');

    // Both directions are the round trip, and leaving it off is the point: this spec runs against
    // the shared dev database, so a save that stays on would change what the next session's
    // Dashboard and failure-estimate rows read. `off` is the default ADR-0001 §3 records.
    await page.getByRole('checkbox', { name: 'Numeric failure estimate' }).uncheck();
    await page.getByRole('button', { name: 'Save preferences' }).click();
    await expect(page.getByRole('status')).toHaveText('Preferences saved.');
});

test('Settings categories tablist navigates by keyboard and switches panels', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    // General is active by default; the Theme control is visible.
    await expect(page.getByRole('tab', { name: 'General' })).toHaveAttribute('aria-selected', 'true');

    // Arrow keys cycle through the four tabs.
    await page.getByRole('tab', { name: 'General' }).focus();
    await page.keyboard.press('ArrowRight');
    await expect(page.getByRole('tab', { name: 'Recommendation' })).toHaveAttribute('aria-selected', 'true');
    await expect(page.getByText('Recommendation aggressiveness')).toBeVisible();

    await page.keyboard.press('ArrowRight');
    await expect(page.getByRole('tab', { name: 'Data' })).toHaveAttribute('aria-selected', 'true');
    await expect(page.getByRole('link', { name: 'Open import' })).toBeVisible();

    // ArrowLeft goes back.
    await page.keyboard.press('ArrowLeft');
    await expect(page.getByRole('tab', { name: 'Recommendation' })).toHaveAttribute('aria-selected', 'true');

    // Home and End jump to the first and last tab.
    await page.getByRole('tab', { name: 'Recommendation' }).focus();
    await page.keyboard.press('Home');
    await expect(page.getByRole('tab', { name: 'General' })).toHaveAttribute('aria-selected', 'true');
    await page.keyboard.press('End');
    await expect(page.getByRole('tab', { name: 'Game version' })).toHaveAttribute('aria-selected', 'true');

    // Clicking a tab activates it.
    await page.getByRole('tab', { name: 'General' }).click();
    await expect(page.getByRole('tab', { name: 'General' })).toHaveAttribute('aria-selected', 'true');
});

test('the Data and Game version categories render what they can and name what they cannot', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    // Data: Import and Export are real, Backup is a server-side action, and the two destructive
    // ones are absences rather than buttons without a backend.
    await page.getByRole('tab', { name: 'Data' }).click();
    await expect(page.getByRole('link', { name: 'Open import' })).toHaveAttribute('href', /\/training-runs\/import$/);
    await expect(page.getByRole('link', { name: 'Download export' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Back up now' })).toBeVisible();
    await expect(page.getByText('server-side action', { exact: false })).toBeVisible();
    await expect(page.getByText('Nothing is downloaded', { exact: false })).toBeVisible();
    await expect(page.getByTitle('Destructive: restoring replaces the database file, which AGENTS.md §5 scopes to the owner. Absent rather than a button without a backend.')).toBeVisible();
    await expect(page.getByTitle('Destructive; requires owner approval for a route (AGENTS §5)')).toBeVisible();

    // Game version: ruleset is N/A, verified date is sourced from config.
    await page.getByRole('tab', { name: 'Game version' }).click();
    await expect(page.getByText('Global Ruleset')).toBeVisible();
    await expect(page.getByTitle('No source defines a Global ruleset version (design-2.0 §48)')).toBeVisible();
    await expect(page.getByText('Verified')).toBeVisible();
    await expect(page.getByText('2026-09-27')).toBeVisible();
});

test('the General category stores what the app can hold and names the one it cannot source', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    // Language is one honest option, disabled, with the constraint in its title: offering a second
    // language would be a false choice, and an absence would hide the one this build has.
    const language = page.getByLabel('Language');
    await expect(language).toBeDisabled();
    await expect(language.locator('option')).toHaveText(['English (Global)']);
    await expect(language).toHaveAttribute('title', /Global-labelled/);

    // Units is a sourcing absence, and its title names the real reason rather than a missing column.
    await expect(page.getByTitle(/Sourcing absence: the only unit this tool prints is race distance/)).toBeVisible();

    // The Default-scenario control reads the matrix as a whole, so it offers every scenario the
    // config composes plus the explicit "none".
    const scenario = page.getByLabel('Default scenario');
    await expect(scenario).toBeVisible();
    await expect(scenario.locator('option').first()).toHaveText('No default');
    await expect(scenario.locator('option')).toHaveCount(5);
});

test('the Recommendation category stores its two preferences and names the held one', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('tab', { name: 'Recommendation' }).click();

    // Stored now, read later: the control is enabled and says so, because a control that stores a
    // value the app does not yet read is honest provided the title names the follow-up.
    const aggressiveness = page.getByLabel('Recommendation aggressiveness');
    await expect(aggressiveness).toBeEnabled();
    await expect(aggressiveness).toHaveAttribute('title', 'Stored now; the advisor reads it in a follow-up slice.');
    await expect(aggressiveness.locator('option')).toHaveText(['Not set', 'Conservative', 'Balanced', 'Aggressive']);

    // Five stat inputs, labelled by the matrix's own stat names, against the engine ceiling.
    for (const stat of ['Speed', 'Stamina', 'Power', 'Guts', 'Wit']) {
        await expect(page.getByLabel(stat, { exact: true })).toBeVisible();
    }

    // Risk tolerance is D4's open question, and the row names it rather than offering a control.
    await expect(page.getByTitle(/whether it enters the stored payload is that screen's own open question/)).toBeVisible();
});

test('refuses a stat target above the engine ceiling, ties the message to its input and moves focus there', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('tab', { name: 'Recommendation' }).click();

    // One bound, one candidate: only Speed is out of range, so the focus path has exactly one target
    // and the refusal under test is the Form Request's, not the browser's.
    await page.getByLabel('Speed', { exact: true }).fill('2001');
    await page.getByRole('button', { name: 'Save preferences' }).click();

    // The panel the refused key lives in is shown, the message is referenced by the input that caused
    // it, and focus lands on that input (SCREEN_SPEC.md SCR-SYS-002, Form error).
    await expect(page.getByRole('tab', { name: 'Recommendation' })).toHaveAttribute('aria-selected', 'true');
    const speed = page.getByLabel('Speed', { exact: true });
    await expect(speed).toHaveAttribute('aria-describedby', 'error-settings_stat_target_defaults_Speed');
    await expect(page.locator('#error-settings_stat_target_defaults_Speed')).not.toHaveText('');
    await expect(speed).toBeFocused();

    // Nothing was stored and nothing was cleared: the Trainer's own entry is still in the field.
    await expect(speed).toHaveValue('2001');
});

test('a saved default scenario survives a reload', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    await page.getByLabel('Default scenario').selectOption('unity_cup');
    await page.getByRole('button', { name: 'Save preferences' }).click();

    const status = page.getByRole('status');
    await expect(status).toHaveText('Preferences saved.');

    // The value is the server's, not the client's: a fresh visit reads it back from the store.
    await page.reload();
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByLabel('Default scenario')).toHaveValue('unity_cup');

    // Clear it again. The browser suite runs against the shared dev database, and a stored default
    // would pre-select the wizard's first step for every later spec — the same coupling the
    // Feature test cannot have, because RefreshDatabase gives each of its own schema.
    await page.getByLabel('Default scenario').selectOption('');
    await page.getByRole('button', { name: 'Save preferences' }).click();
    await expect(page.getByRole('status')).toHaveText('Preferences saved.');
});

test('the tabs answer reduced motion by switching without a transition', async ({ page }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('tab', { name: 'General' }).focus();
    await page.keyboard.press('ArrowRight');

    // The switch itself carries no animation to suppress. The app's global reduce sets
    // `0.00001s` rather than `none`, so the number is what is measured, not the literal —
    // the same reading `career-training-detail.spec.ts` takes.
    await expect(page.getByRole('tab', { name: 'Recommendation' })).toHaveAttribute('aria-selected', 'true');
    const transition = await page.getByRole('tab', { name: 'Recommendation' }).evaluate(
        (element) => Number.parseFloat(getComputedStyle(element).transitionDuration),
    );

    expect(transition).toBeLessThan(0.01);
});

test('sizes the preferences controls to the 44px contract and refrains at 320px', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    const undersized = await page.locator('main').evaluate((root) =>
        Array.from(root.querySelectorAll('button, a[href]'))
            .map((element) => ({ text: (element.textContent ?? '').trim().slice(0, 40), height: Math.round(element.getBoundingClientRect().height) }))
            .filter((control) => control.height > 0 && control.height < 44)
            .map((control) => `${control.height}px: ${control.text}`),
    );

    expect(undersized, `controls under the 44px contract: ${JSON.stringify(undersized)}`).toEqual([]);

    // The wide controls are single-column selects, so the page must reflow rather than scroll.
    await page.setViewportSize({ width: 320, height: 800 });
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);

    expect(overflow, 'the document scrolls horizontally at 320px').toBeLessThanOrEqual(1);
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    const results = await buildAxe(page).analyze();

    expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
});
