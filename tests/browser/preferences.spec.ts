import { test, expect } from '@playwright/test';
import { buildAxe } from '../utils/accessibility';

// Rendered-copy evidence for the Preferences screen (SCR-SYS-002), rewritten against the HFW
// (preferences.html): one page head with one save verb, then a two-column card grid. The
// server-side PreferenceControlsTest asserts the props; this asserts what the browser shows.

async function ready(page: import('@playwright/test').Page): Promise<void> {
    await page.goto('/preferences');
    await page.locator('#app > *').first().waitFor();
}

test('Preferences renders its heading, the savebar and the shell nav', async ({ page }) => {
    await ready(page);

    await expect(page.getByRole('heading', { name: 'Preferences', level: 1 })).toBeVisible();
    await expect(page.getByText(/Settings are stored|Layout, language, theme/)).toBeVisible();

    // The savebar carries the screen's state line beside the one primary. Unedited, the form is
    // clean, so the line says so and the control is disabled (preferences.html:62-63).
    await expect(page.getByText('All changes saved', { exact: true })).toBeVisible();
    const save = page.getByRole('button', { name: 'Save changes' });
    await expect(save).toBeVisible();
    await expect(save).toBeDisabled();

    await expect(page.getByRole('link', { name: 'Dashboard' })).toBeVisible();
});

test('the screen is the wireframe card grid, in its reading order', async ({ page }) => {
    await ready(page);

    // preferences.html:73-189: left column Layout width / Target defaults / Data, right column
    // Theme / Language / Dashboard. Those five cards are the screen.
    const headings = await page.locator('.card > h2').allTextContents();
    expect(headings).toEqual([
        'Layout width',
        'Target defaults',
        'Data',
        'Theme',
        'Language',
        'Dashboard',
    ]);

    // The wireframe's annotation key is a document artifact, not a screen (plan rule 9).
    await expect(page.getByText('Annotation key')).toHaveCount(0);

    // Two columns on a desktop viewport.
    await page.setViewportSize({ width: 1280, height: 800 });
    const layout = await page
        .locator('.card', { has: page.getByRole('heading', { name: 'Layout width', exact: true }) })
        .first()
        .boundingBox();
    const theme = await page
        .locator('.card', { has: page.getByRole('heading', { name: 'Theme', exact: true }) })
        .first()
        .boundingBox();
    expect(theme!.x).toBeGreaterThan(layout!.x + layout!.width - 1);
});

test('layout width is three stated options with their widths', async ({ page }) => {
    await ready(page);

    // preferences.html:78-98: each option names its measure, because the three words alone do not.
    const card = page.locator('.card', { has: page.getByRole('heading', { name: 'Layout width', exact: true }) }).first();
    await expect(card.getByText(/1152px/)).toBeVisible();
    await expect(card.getByText(/1536px/)).toBeVisible();
    // The Compact option reads the way the wireframe reads.
    await expect(card.locator('label', { hasText: 'Compact' })).toContainText(
        'Focused composition. A single reading column, best for forms and long copy.',
    );
    // And the note states the three measures the options are named after.
    await expect(card.getByText(/768 \/ 1152 \/ 1536/)).toBeVisible();

    const radios = page.locator('input[name="settings[dashboard_layout]"]');
    await expect(radios).toHaveCount(3);

    await page.getByLabel(/^Wide/).check();
    await expect(page.getByRole('button', { name: 'Save changes' })).toBeEnabled();
    await expect(page.getByText('Unsaved changes')).toBeVisible();

    await page.getByLabel(/^Standard/).check();
    await expect(page.getByRole('button', { name: 'Save changes' })).toBeDisabled();
    await expect(page.getByText('All changes saved')).toBeVisible();
});

test('theme is a choice of options, not a two-way switch', async ({ page }) => {
    await ready(page);

    // preferences.html:137-159 draws three. This build stores a fourth resolved palette (`eva01`,
    // owner request 2026-10-24), and a stored value a Trainer may already have selected cannot be
    // dropped from the only control that sets it, so the fourth option renders too.
    const radios = page.locator('input[name="theme"]');
    await expect(radios).toHaveCount(4);
    for (const label of ['Follow the system', 'Light', 'Dark', 'Evangelion Unit-01']) {
        await expect(page.getByText(label, { exact: true })).toBeVisible();
    }
    await expect(page.getByText('OS decides. Nothing stored.')).toBeVisible();

    // The note states the one fact the card exists to state: this is the app's only theme control.
    await expect(page.getByText(/only theme control/)).toBeVisible();
});

test('the language control is the one language the corpus holds, and says why', async ({ page }) => {
    await ready(page);

    // preferences.html:167-173 draws a select with two languages. This build's corpus is
    // Global-labelled only and the writer refuses a second language, so the control carries the
    // one option that exists, disabled, and names the reason.
    const select = page.getByLabel('Interface language');
    await expect(select).toBeVisible();
    await expect(select).toBeDisabled();
    await expect(select.locator('option')).toHaveCount(1);
    await expect(page.getByText("the corpus holds no other language's strings")).toBeVisible();

    // The wireframe's own line about game vocabulary still reads on the card.
    await expect(
        page.getByText(/Game vocabulary is the client's own English either way/),
    ).toBeVisible();
});

test('the target defaults grid carries the five stats and the default scenario', async ({ page }) => {
    await ready(page);

    const card = page
        .locator('.card', { has: page.getByRole('heading', { name: 'Target defaults', exact: true }) })
        .first();

    for (const stat of ['Speed', 'Stamina', 'Power', 'Guts', 'Wit']) {
        await expect(card.getByLabel(stat, { exact: true })).toBeVisible();
    }

    // The wireframe's sixth cell is the default scenario, which is the preference this build
    // stores: one option per scenario the matrix composes, plus the empty state.
    const scenario = card.getByLabel('Default scenario');
    await expect(scenario).toBeVisible();
    const options = await scenario.locator('option').allTextContents();
    expect(options[0]).toBe('Not set');
    expect(options.length).toBeGreaterThan(1);
});

test('saving a preference confirms in place without a full reload', async ({ page }) => {
    await ready(page);

    await page.getByRole('checkbox', { name: 'Numeric failure estimate' }).check();
    await expect(page.getByRole('button', { name: 'Save changes' })).toBeEnabled();
    await page.getByRole('button', { name: 'Save changes' }).click();
    await expect(page.getByRole('status').filter({ hasText: 'Preferences saved.' })).toBeVisible();

    // The form returns to clean, so the control is disabled again.
    await expect(page.getByRole('button', { name: 'Save changes' })).toBeDisabled();
    await expect(page.getByText('All changes saved')).toBeVisible();

    await page.reload();
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByRole('checkbox', { name: 'Numeric failure estimate' })).toBeChecked();

    // Leave it off: the browser suite shares the database with every other spec.
    await page.getByRole('checkbox', { name: 'Numeric failure estimate' }).uncheck();
    await page.getByRole('button', { name: 'Save changes' }).click();
    await expect(page.getByRole('status').filter({ hasText: 'Preferences saved.' })).toBeVisible();
});

test('a second save re-announces the flash after a real edit', async ({ page }) => {
    await ready(page);

    const save = page.getByRole('button', { name: 'Save changes' });
    const speed = page.getByLabel('Speed', { exact: true });
    const flash = page.getByRole('status').filter({ hasText: 'Preferences saved.' });

    // The screen starts clean, so the save verb is disabled: one save model, one verb, disabled
    // when clean (preferences.html:62-63).
    await expect(save).toBeDisabled();
    await expect(page.getByText('All changes saved')).toBeVisible();

    // A real edit, not a no-op submit: a stat target the Trainer typed. The control is disabled
    // while clean, so this also proves the nested write is seen by the clean/dirty comparison.
    await speed.fill('900');
    await expect(save).toBeEnabled();
    await expect(page.getByText('Unsaved changes')).toBeVisible();
    await save.click();
    await expect(flash).toBeVisible();

    // The dismiss affordance clears the message while the live region stays mounted (WCAG 4.1.3),
    // so the next announcement has to be a new one rather than the same sentence still on screen.
    await page.getByRole('button', { name: 'Dismiss' }).click();
    await expect(page.getByText('Preferences saved.')).toHaveCount(0);

    // A second genuine write re-announces: the layout watches the props object, not the text.
    await speed.fill('950');
    await expect(save).toBeEnabled();
    await save.click();
    await expect(flash).toBeVisible();

    // And the banner leaves on its own after five seconds (plan 6B.6).
    await page.waitForTimeout(5500);
    await expect(page.getByText('Preferences saved.')).toHaveCount(0);

    // Leave the store as it was found: a stored stat default pre-fills the build-target step, and
    // the suite shares one database.
    await speed.fill('');
    await expect(save).toBeEnabled();
    await save.click();
    await expect(flash).toBeVisible();
});

test('refuses a stat target above the engine ceiling and ties the message to its input', async ({ page }) => {
    await ready(page);

    const speed = page.getByLabel('Speed', { exact: true });
    await expect(speed).toBeVisible();
    // The ceiling is the range the field itself states, so the number the refusal is measured
    // against is the one the field carries.
    const hint = await page.locator('#hint-settings_stat_target_defaults_Speed').textContent();
    const cap = Number(hint?.split('-')[1]);
    expect(cap).toBeGreaterThan(0);

    await speed.fill(String(cap + 1));
    await expect(page.getByRole('button', { name: 'Save changes' })).toBeEnabled();
    await page.getByRole('button', { name: 'Save changes' }).click();

    // The refusal is referenced by the input that caused it (SCREEN_SPEC.md SCR-SYS-002).
    await expect(speed).toHaveAttribute('aria-invalid', 'true');

    // Read describedby only after the refusal has landed: before the response the input still
    // advertises its hint id, so a read taken straight after the click captures the wrong token.
    const describedBy = await speed.getAttribute('aria-describedby');
    expect(describedBy).toBeTruthy();
    const errorId = (describedBy ?? '').split(' ').find((id) => id.includes('error'));
    expect(errorId).toBeTruthy();
    await expect(page.locator(`#${errorId}`)).toBeVisible();

    // The refusal wrote nothing, so clearing the field returns the form to the state it loaded in and
    // the save control goes back to refusing to fire. Asserted rather than clicked: a second save here
    // would be a no-op write the disabled state deliberately prevents.
    await speed.fill('');
    await expect(page.getByRole('button', { name: 'Save changes' })).toBeDisabled();
});

test('the Data card offers the export and the backup, and the backup is a real write', async ({ page }) => {
    await ready(page);

    // preferences.html:119-128 draws the Data card: the two actions and the line that says what
    // each one is. This build's backup is server-side and reports the path it wrote, so the
    // control is never an inert button.
    const card = page.locator('.card', { has: page.getByRole('heading', { name: 'Data', exact: true }) }).first();
    const exportLink = card.getByRole('link', { name: /Export everything as JSON/ });
    await expect(exportLink).toHaveAttribute('href', /export/);
    await expect(card.getByRole('button', { name: 'Download a backup' })).toBeVisible();
    await expect(
        card.getByText('Export is a file download. A backup is a complete copy you can restore from.'),
    ).toBeVisible();

    // The backup writes a snapshot and says where it went.
    await card.getByRole('button', { name: 'Download a backup' }).click();
    const flash = page.getByRole('status').filter({ hasText: 'Backup written to' });
    await expect(flash).toBeVisible();
    await expect(flash).toContainText('backups');
});

test('the Dashboard card states which cards are shown', async ({ page }) => {
    await ready(page);

    const card = page.locator('.card', { has: page.getByRole('heading', { name: 'Dashboard', exact: true }) }).first();
    await expect(card.getByRole('columnheader', { name: 'Card' })).toBeVisible();
    await expect(card.getByRole('columnheader', { name: 'Shown' })).toBeVisible();
    await expect(card.getByText('Hidden cards are excluded, not collapsed.')).toBeVisible();

    // The wireframe's four rows, each with the live control that shows whether it is hidden.
    // The Shown cell carries the control, whose own accessible name is the row's label, so the
    // label cell is the first of the two.
    for (const label of ['Active career', 'Needs you', 'Recent veterans', 'Quick actions']) {
        await expect(card.getByRole('cell', { name: label, exact: true }).first()).toBeVisible();
        await expect(card.getByRole('checkbox', { name: label })).toBeVisible();
    }
});

test('the saved dashboard layout and hidden cards survive a reload and reflect on the Dashboard', async ({ page }) => {
    await ready(page);

    await page.getByLabel(/^Compact/).check();
    await page.getByRole('button', { name: 'Save changes' }).click();
    await expect(page.getByRole('status').filter({ hasText: 'Preferences saved.' })).toBeVisible();

    await page.reload();
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByLabel(/^Compact/)).toBeChecked();

    // The Dashboard honours it. The landing screen is `/`, not `/dashboard`.
    await page.goto('/');
    await page.locator('#app > *').first().waitFor();
    await expect(page.locator('[data-dashboard-root]')).toHaveAttribute('data-layout', 'compact');

    // Reset so the suite leaves the store as it found it.
    await ready(page);
    await page.getByLabel(/^Standard/).check();
    await page.getByRole('button', { name: 'Save changes' }).click();
    await expect(page.getByRole('status').filter({ hasText: 'Preferences saved.' })).toBeVisible();
});

test('Reset layout to defaults is a two-click confirm that commits', async ({ page }) => {
    await ready(page);

    const reset = page.getByRole('button', { name: 'Reset layout to defaults' });
    await reset.click();
    await expect(page.getByRole('button', { name: 'Reset?' })).toBeVisible();

    await page.getByRole('button', { name: 'Reset?' }).click();
    await expect(page.getByRole('status').filter({ hasText: 'Preferences saved.' })).toBeVisible();
});

test('sizes the preferences controls to the 44px contract and refrains at 320px', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 640 });
    await ready(page);

    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );
    expect(overflow).toBeLessThanOrEqual(1);

    await page.setViewportSize({ width: 1280, height: 800 });
    const box = await page.getByRole('button', { name: 'Save changes' }).boundingBox();
    expect(box!.height).toBeGreaterThanOrEqual(44);
});

test('passes an axe scan at WCAG A and AA', async ({ page }) => {
    await ready(page);
    const axe = await buildAxe(page);
    const results = await axe.analyze();
    expect(results.violations.map((v) => `${v.id}: ${v.nodes.length}`)).toEqual([]);
});
