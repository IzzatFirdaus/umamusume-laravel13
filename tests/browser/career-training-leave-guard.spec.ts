import { test, expect } from '@playwright/test';
import { startCareer } from '../utils/start-career';
import { deleteRun } from '../utils/delete-run';

/*
 * F-08 — choosing the Race action must not destroy the turn a Trainer has typed.
 *
 * The defect (walk 8, `RS-UC-2026-13`; previously recorded as `R5-23` and closed `Does not
 * reproduce` because the navigation is deliberate):
 * `TrainingDetail.vue`'s `chooseAction` does `window.location.href = href` for the `Race` entry.
 * That is a hard document navigation, so `useForm`'s 21 fields are destroyed with the JS context.
 * Selecting an action and leaving the page were the same interaction, and nothing warned.
 *
 * The guard must be narrow. A confirm on every action would be worse than the defect, so the spec
 * asserts BOTH directions: dirty prompts, clean does not, and non-navigation actions never prompt.
 */

const ALL = {
    turn: '#turn-turn',
    speed: '#turn-speed',
    stamina: '#turn-stamina',
    power: '#turn-power',
    guts: '#turn-guts',
    wit: '#turn-wit',
    sp: '#turn-sp',
    fans: '#turn-fans',
    energy: '#turn-energy',
    facilitySpeed: '#facility-facility_speed',
    facilityStamina: '#facility-facility_stamina',
    facilityPower: '#facility-facility_power',
    facilityGuts: '#facility-facility_guts',
    facilityWit: '#facility-facility_wit',
} as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

test.describe.configure({ timeout: 300_000 });

/** Type a value into every field the form offers, and return what was typed. */
async function fillTheTurn(page: import('@playwright/test').Page): Promise<Record<string, string>> {
    const typed: Record<string, string> = {};

    for (const [name, selector] of Object.entries(ALL)) {
        const locator = page.locator(selector);

        if ((await locator.count()) === 0) {
            continue;
        }

        const value = name === 'turn' ? '7' : String(Object.keys(ALL).indexOf(name) + 11);

        await locator.fill(value);
        typed[name] = value;
    }

    return typed;
}

/** Assert every value the spec typed is still on the form. */
async function expectValuesIntact(
    page: import('@playwright/test').Page,
    typed: Record<string, string>,
): Promise<void> {
    for (const [name, value] of Object.entries(typed)) {
        await expect(
            page.locator(ALL[name as keyof typeof ALL]),
            `${name} did not survive the round trip`,
        ).toHaveValue(value);
    }
}

test('choosing Race with a dirty form does not silently destroy the entered values', async ({ page }) => {
    const runUrl = await startCareer(page);
    createdRunUrls.push(runUrl);

    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    const typed = await fillTheTurn(page);
    expect(Object.keys(typed).length, 'the form exposed no fields to type into').toBeGreaterThan(5);

    // Choosing the Race action is the interaction that used to navigate with no warning.
    const race = page.getByRole('radio', { name: /^Race/i }).first();
    await expect(race).toBeVisible();

    let prompted = false;

    // DISMISS, not accept: a round trip cannot prove anything on its own, because navigating away and
    // back loses `useForm` state whether or not a guard exists. The guard's job is to stop the
    // departure, so the assertion is what the page looks like after the departure is refused.
    page.on('dialog', async (dialog) => {
        prompted = true;
        await dialog.dismiss();
    });

    await race.click();
    await page.waitForLoadState('domcontentloaded');

    expect(
        prompted,
        'choosing Race with 13 entered values navigated away without asking, destroying them',
    ).toBe(true);

    await expect(page, 'the prompt was dismissed but the page navigated anyway')
        .toHaveURL(new RegExp(`${runUrl}/training$`));

    await expectValuesIntact(page, typed);
});

test('a clean form does not prompt when Race is chosen', async ({ page }) => {
    const runUrl = await startCareer(page);
    createdRunUrls.push(runUrl);

    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    let prompted = false;

    page.on('dialog', async (dialog) => {
        prompted = true;
        await dialog.dismiss();
    });

    await page.getByRole('radio', { name: /^Race/i }).first().click();
    await page.waitForLoadState('domcontentloaded');

    expect(prompted, 'an untouched form prompted before leaving, which would be worse than the defect')
        .toBe(false);

    await expect(page).toHaveURL(new RegExp(`${runUrl}/races`));
});

test('a non-navigation action never prompts', async ({ page }) => {
    const runUrl = await startCareer(page);
    createdRunUrls.push(runUrl);

    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await fillTheTurn(page);

    let prompted = false;

    page.on('dialog', async (dialog) => {
        prompted = true;
        await dialog.dismiss();
    });

    // `Rest` and the training actions call `choose()` and stay on the page; they must not warn.
    const rest = page.getByRole('radio', { name: /^Rest/i }).first();

    if ((await rest.count()) === 0) {
        test.skip(true, 'this scenario offers no Rest action');

        return;
    }

    await rest.click();

    expect(prompted, 'a stay-on-page action prompted, which the guard must never do').toBe(false);
    await expect(page).toHaveURL(new RegExp(`${runUrl}/training$`));
});
