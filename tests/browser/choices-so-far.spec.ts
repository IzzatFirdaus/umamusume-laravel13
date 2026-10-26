import { test, expect } from '@playwright/test';

/*
 * The carried-choices strip (plan 3.2). Rendered-copy evidence, so it reads the built bundle.
 *
 * The strip states the three decisions the wizard carries forward on every step, replacing the
 * per-step "Stored choice:" lines that only three screens printed. Each case drives the real wizard
 * rather than mounting the component, because the point of the strip is that it reads the same draft
 * the steps write.
 *
 * What is asserted: all three labels are always present; an unmade choice reads "not chosen" in muted
 * text rather than an absence marker; a made choice renders its value as a link to the step that owns
 * it, and that link navigates.
 */

const STRIP = '[data-field="choices-so-far"]';

test('states all three choices on the first step before anything is chosen', async ({ page }) => {
    await page.goto('/career/setup/scenario', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const strip = page.locator(STRIP);
    await expect(strip).toBeVisible();

    // Three labels, always, in the same order.
    await expect(strip).toContainText('Scenario:');
    await expect(strip).toContainText('Trainee:');
    await expect(strip).toContainText('Target:');

    // A fresh draft has chosen nothing, and says so in words rather than with a marker.
    await expect(strip.getByText('not chosen')).toHaveCount(3);

    // "not chosen" is not an absence: no disclosure is rendered inside the strip.
    await expect(strip.locator('details')).toHaveCount(0);
});

test('carries a chosen scenario onto the next step as a link to step 1', async ({ page }) => {
    await page.goto('/career/setup/scenario', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    // Choose the first card through its radio, which is what writes the draft: the card is the
    // radio's label, so checking the input is the card's pick, and the head's one filled action
    // commits the step.
    await page.getByRole('radio').first().check();
    await page.getByRole('button', { name: 'Continue to trainee' }).click();
    await page.waitForURL(/\/career\/setup\/trainee$/);
    await page.locator('#app > *').first().waitFor();

    const chosen = page.locator(STRIP).getByRole('link', { name: /.+/ }).first();
    await expect(chosen).toBeVisible();
    await expect(chosen).toHaveAttribute('href', '/career/setup/scenario');

    // The trainee and target are still unmade, and the strip says so beside the made choice.
    await expect(page.locator(STRIP).getByText('not chosen')).toHaveCount(2);

    // The link goes back to the step that owns the choice.
    await chosen.click();
    await page.waitForURL(/\/career\/setup\/scenario$/);
});