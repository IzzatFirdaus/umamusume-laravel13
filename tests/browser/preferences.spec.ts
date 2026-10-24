import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';

// Rendered-copy evidence for the ported Preferences screen (SCR-SYS-002). The server-side
// PreferenceControlsTest asserts the props; this asserts what the browser actually shows.

test('Preferences renders its heading, both controls and the shell nav', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByRole('heading', { name: 'Preferences', level: 2 })).toBeVisible();

    // The theme control offers the three stored values (light, dark, eva01) plus follow-the-OS.
    const theme = page.getByLabel('Theme');
    await expect(theme).toBeVisible();
    await expect(theme.locator('option')).toHaveText([
        'Follow the system',
        'Light',
        'Dark',
        'Evangelion Unit-01',
    ]);

    await expect(page.getByRole('checkbox', { name: 'Numeric failure estimate' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Save preferences' })).toBeVisible();

    // The shell's navigation is present (the sidebar on desktop).
    await expect(page.getByRole('link', { name: 'Dashboard' })).toBeVisible();
});

test('selecting Evangelion Unit-01 persists, reads back and renders the third palette', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    await page.getByLabel('Theme').selectOption('eva01');
    await page.getByRole('button', { name: 'Save preferences' }).click();
    // The theme-change flash carries the extra "shows on your next reload" sentence.
    await expect(page.getByRole('status')).toContainText('Preferences saved.');

    // Persisted server-side: a reload prints the attribute on the first paint and the control
    // comes back preselected, so this is the stored value rather than a client-only flip.
    await page.reload();
    await page.locator('#app > *').first().waitFor();
    await expect(page.locator('html')).toHaveAttribute('data-theme', 'eva01');
    await expect(page.getByLabel('Theme')).toHaveValue('eva01');

    // The palette resolves through the shell: the page field is the requested #1A1025, not the
    // light #F7F5FF and not the dark #0B0B0D.
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();
    await expect(page.locator('.bg-page').first()).toHaveCSS('background-color', 'rgb(26, 16, 37)');

    // Reset to follow-the-system so no later spec inherits a stored theme.
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();
    await page.getByLabel('Theme').selectOption('');
    await page.getByRole('button', { name: 'Save preferences' }).click();
    await expect(page.getByRole('status')).toContainText('Preferences saved.');
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
    await expect(page.getByTitle('Destructive: restoring replaces the database file, which is an owner\'s action to take. Absent rather than a button without a backend.')).toBeVisible();
    await expect(page.getByTitle('Destructive; adding it needs the owner\'s approval')).toBeVisible();

    // Game version: ruleset is N/A, verified date is sourced from config. R2-19 makes the heading name
    // the fact it prints, and the match is `exact` because the retired wording is a prefix of the new
    // one: a substring match here would pass on either wording and assert nothing.
    await page.getByRole('tab', { name: 'Game version' }).click();
    await expect(page.getByText('Global Ruleset Version', { exact: true })).toBeVisible();
    await expect(page.getByText('Global Ruleset', { exact: true })).toHaveCount(0);
    // `0646381` stripped the internal citation from every screen's copy, so the title is the plain
    // sentence; the exact match stays because the retired wording is a prefix of it and a substring
    // assertion would pass on either.
    await expect(page.getByTitle('No source defines a Global ruleset version')).toBeVisible();
    // `exact` because the per-tab subtitle contains the same word ("last-verified date") and a
    // substring match would resolve to both.
    await expect(page.getByText('Verified', { exact: true })).toBeVisible();
    await expect(page.getByText('2026-09-27')).toBeVisible();
    await expect(page.getByText('Global Ruleset Version', { exact: true })).toHaveClass(/text-lg/);
});

test('the General category stores what the app can hold and names the one it cannot source', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    // Language is one honest option, disabled, with the constraint stated under it as a visible
    // hint: offering a second language would be a false choice, and an absence would hide the one
    // this build has. Slice 0.5 moved this from a `title` (hover-only, unreliable on a disabled
    // control) to the shared control's hint, so the assertion follows the stronger rendering.
    const language = page.getByLabel('Language');
    await expect(language).toBeDisabled();
    await expect(language.locator('option')).toHaveText(['English (Global)']);
    await expect(page.getByText('This build is Global-labelled only', { exact: false })).toBeVisible();

    // Units is a sourcing absence, and its title names the real reason rather than a missing column.
    await expect(page.getByTitle(/Sourcing absence: the only unit this tool prints is race distance/)).toBeVisible();
    await expect(page.getByText('Units', { exact: true })).toHaveClass(/text-lg/);

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

    // Each input carries its range hint (hyphen, not the en dash R-02 bans) beside the ceiling
    // the section paragraph already states.
    await expect(page.getByText('0-2000')).toHaveCount(5);

    // Risk tolerance is D4's open question, and the row names it rather than offering a control.
    await expect(page.getByTitle(/risk tolerance enters the stored payload is the Build Target screen's own open question/)).toBeVisible();
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

test('the estimate note reads as one line of state plus one sourced note', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    await expect(page.getByText('Numeric failure estimate is off.')).toBeVisible();
    await expect(
        page.getByText('No source publishes a failure curve; the stored choice is kept for a future model.'),
    ).toBeVisible();

    // The retired contributor copy (X-6) is gone in both of its internal-model sentences.
    await expect(page.getByText('has to show its formula beside it')).toHaveCount(0);
    await expect(page.getByText('the band the app can source from')).toHaveCount(0);
});

test('import and export render through the shared secondary button treatment', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('tab', { name: 'Data' }).click();

    for (const name of ['Open import', 'Download export']) {
        const action = page.getByRole('link', { name });
        await expect(action).toHaveClass(/rounded-md/);
        await expect(action).toHaveClass(/border-rule/);
        await expect(action).not.toHaveClass(/pick-line/);
    }
});

test('the screen carries exactly one accented primary and states the save scope', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    // The accent budget (plan 6B.12): one accented primary per screen, and it is the commit. The
    // shell nav's active chip is a background, not a filled action, so it does not count. The
    // selector reads the variant attribute, not the retired `enamel` class the gloss used to carry.
    await expect(page.locator('[data-variant="primary"]')).toHaveCount(1);
    await expect(page.getByRole('button', { name: 'Save preferences' })).toHaveAttribute('data-variant', 'primary');

    // The one global Save says what its scope is (plan 6B.5); it renders once, outside the panels.
    await expect(page.getByText('Saves every tab in one write.')).toBeVisible();
});

test('every tab renders its one-line subtitle under the shared shell header', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    const subtitles: Record<string, string> = {
        General: 'Appearance, language and default scenario.',
        Recommendation: 'Advisor defaults and stat-target pre-fills.',
        Data: 'Import, export and backup.',
        'Game version': 'Ruleset version and last-verified date.',
    };

    for (const [tab, subtitle] of Object.entries(subtitles)) {
        await page.getByRole('tab', { name: tab }).click();
        await expect(page.getByText(subtitle)).toBeVisible();

        // The shell's PREFERENCES eyebrow rides above the strip on every tab (plan 6B.13), so the
        // header does not collapse when the panel swaps.
        await expect(page.locator('header').getByText('Preferences', { exact: true })).toBeVisible();
    }
});

test('the active tab lives in the URL and survives a reload', async ({ page }) => {
    // A reload lands on the tab the Trainer was reading (plan 6B.10), and an unknown tab name
    // falls back to the default rather than rendering an empty panel.
    await page.goto('/preferences?tab=data');
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByRole('tab', { name: 'Data' })).toHaveAttribute('aria-selected', 'true');
    await expect(page.getByRole('link', { name: 'Open import' })).toBeVisible();

    await page.getByRole('tab', { name: 'Game version' }).click();
    await expect(page).toHaveURL(/tab=game-version/);

    await page.reload();
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByRole('tab', { name: 'Game version' })).toHaveAttribute('aria-selected', 'true');

    await page.goto('/preferences?tab=nonsense');
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByRole('tab', { name: 'General' })).toHaveAttribute('aria-selected', 'true');
});

test('the saved banner dismisses by hand and then on its own', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    await page.getByRole('button', { name: 'Save preferences' }).click();
    await expect(page.getByRole('status')).toHaveText('Preferences saved.');

    // The dismiss affordance clears the message while the live region stays mounted (WCAG 4.1.3).
    await page.getByRole('button', { name: 'Dismiss' }).click();
    await expect(page.getByText('Preferences saved.')).toHaveCount(0);

    // A second save re-announces: the layout watches the props object, not the message text, so an
    // identical sentence after a dismiss still reaches the region.
    await page.getByRole('button', { name: 'Save preferences' }).click();
    await expect(page.getByRole('status')).toHaveText('Preferences saved.');

    // And the banner leaves on its own after five seconds (plan 6B.6).
    await page.waitForTimeout(5500);
    await expect(page.getByText('Preferences saved.')).toHaveCount(0);
});

test('the General category offers Layout width and Show on Dashboard controls', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    // Layout width: three radios, Standard selected by default. The width applies to every shell
    // screen, not only the Dashboard (DESIGN.md §2.3 shell-width amendment).
    await expect(page.getByText('Layout width')).toBeVisible();
    await expect(page.getByRole('radio', { name: 'Standard', exact: true })).toBeVisible();
    await expect(page.getByRole('radio', { name: 'Standard', exact: true })).toBeChecked();
    const wide = page.getByRole('radio', { name: 'Wide', exact: true });
    await expect(wide).toBeVisible();
    await expect(wide).toBeEnabled();
    // Enabled radio has aria-disabled="false" (the attribute is present with value false).
    await expect(wide).toHaveAttribute('aria-disabled', 'false');
    await expect(wide).not.toHaveAttribute('aria-describedby');
    await expect(page.locator('#wide-bento-hint')).toHaveCount(0);
    await expect(page.getByRole('radio', { name: 'Compact' })).toBeVisible();

    // Show on Dashboard: five checkboxes, all checked by default (none hidden).
    await expect(page.getByText('Show on Dashboard')).toBeVisible();
    for (const label of ['Active Career', 'Quick Actions', 'Recent Veterans', 'Data Status']) {
        await expect(page.getByRole('checkbox', { name: label })).toBeVisible();
        await expect(page.getByRole('checkbox', { name: label })).toBeChecked();
    }
});

test('the saved dashboard layout and hidden cards survive a reload and reflect on the Dashboard', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    // Switch to Focused layout and hide the Data Status card.
    await page.getByRole('radio', { name: 'Compact' }).check();
    await page.getByRole('checkbox', { name: 'Data Status' }).uncheck();
    await page.getByRole('button', { name: 'Save preferences' }).click();
    await expect(page.getByRole('status')).toHaveText('Preferences saved.');

    // The values survive a reload: Focused radio stays checked, Data Status stays unchecked.
    await page.reload();
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByRole('radio', { name: 'Compact' })).toBeChecked();
    await expect(page.getByRole('checkbox', { name: 'Data Status' })).not.toBeChecked();

    // The Dashboard reads the same stored preferences: Data Status region is gone, and the
    // grid container carries the focused class (single column, centred).
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByRole('region', { name: 'DATA STATUS' })).toHaveCount(0);

    // Reset: back to Bento, all cards shown. The shared dev database must not hold the test
    // mutation, so the next spec sees the default grid.
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('radio', { name: 'Standard', exact: true }).check();
    await page.getByRole('checkbox', { name: 'Data Status' }).check();
    await page.getByRole('button', { name: 'Save preferences' }).click();
    await expect(page.getByRole('status')).toHaveText('Preferences saved.');
});

test('Reset layout to defaults restores bento and every card with a two-click confirm', async ({ page }) => {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();

    // Move away from the defaults first, so the reset has something to undo.
    await page.getByRole('radio', { name: 'Compact' }).check();
    await page.getByRole('checkbox', { name: 'Data Status' }).uncheck();
    await page.getByRole('button', { name: 'Save preferences' }).click();
    await expect(page.getByRole('status')).toHaveText('Preferences saved.');

    // First click only arms the button: its label flips and nothing is committed.
    await page.getByRole('button', { name: 'Reset layout to defaults' }).click();
    await expect(page.getByRole('button', { name: 'Reset?' })).toBeVisible();

    // Second click commits: Bento is selected again and every card is shown.
    await page.getByRole('button', { name: 'Reset?' }).click();
    await expect(page.getByRole('radio', { name: 'Standard', exact: true })).toBeChecked();
    for (const label of ['Active Career', 'Quick Actions', 'Recent Veterans', 'Data Status']) {
        await expect(page.getByRole('checkbox', { name: label })).toBeChecked();
    }
    await expect(page.getByRole('status').filter({ hasText: 'Layout width reset to defaults.' })).toBeAttached();

    // The stored value survives a reload as the default.
    await page.reload();
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByRole('radio', { name: 'Standard', exact: true })).toBeChecked();
});
