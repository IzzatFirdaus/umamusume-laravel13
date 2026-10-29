import { test, expect } from '@playwright/test';

/*
 * Rendered-copy evidence for the Independent Training entry tab (`UMAMUSUME_REFERENCE.md` §1.1.7).
 *
 * `CareerPreflightTest` and `IndependentTrainingStoreTest` assert the resolved props and the write;
 * this file asserts what only a browser reaches — that the mode toggle and the policy selector are on
 * the commitment screen and read in the client's own nouns.
 *
 * **This walk stops at steps 1, 2 and 5 on purpose.** `tests/utils/start-career.ts` drives step 3 by
 * its field names (`select[name="distance"]` and friends), and step 3 is the surface a peer pass is
 * rebuilding in its own working tree, so a walk through it times out on a control this spec does not
 * need. The entry tab renders from the draft's trainee and scenario alone — the toggle, the policy
 * selector and the primary all appear without a build target — so the walk reaches the page the
 * assertions are about and nothing else.
 */

const WRITE = { timeout: 60_000 } as const;

/** Steps 1, 2 and 5, which is as far as the entry tab needs the draft filled. */
async function draftThroughDeck(page: import('@playwright/test').Page): Promise<void> {
    await page.goto('/career/setup/scenario', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#scenario-radio-unity_cup').click();
    await expect(page.locator('#scenario-radio-unity_cup')).toHaveAttribute('aria-checked', 'true', WRITE);

    await page.goto('/career/setup/trainee', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    const traineeButton = page.locator('button[id^="trainee-"]').first();
    await traineeButton.click();
    await expect(traineeButton).toHaveAttribute('aria-pressed', 'true', WRITE);

    await page.goto('/career/setup/deck', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#deck-slot-1 button[aria-label^="Choose a card"]').click();
    await page.getByRole('dialog').getByRole('button', { name: 'Equip' }).first().click();
    await expect(page.locator('#deck-slot-1')).not.toContainText('Empty.', WRITE);
    await page.locator('#deck-slot-1').getByRole('button', { name: 'Owned' }).click();
    await expect(page.locator('#deck-slot-1').getByRole('button', { name: 'Owned' })).toHaveAttribute(
        'aria-pressed',
        'true',
        WRITE,
    );
    // No page-level "Save deck" exists any more: Phase E cut it (`career-deck-select.spec.ts:215`), and
    // the forward door is the step's one submit-shaped control, writing the deck before it navigates.
    // Clicking the dead label waited three minutes for a button the page deliberately does not draw.
    await page.getByRole('button', { name: 'Continue to preflight' }).click();
    await page.locator('#app > *').first().waitFor();

    await page.goto('/career/setup/preflight', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

test('offers the Career mode toggle and reveals the policy selector in the client nouns', async ({ page }) => {
    await draftThroughDeck(page);

    const mode = page.getByRole('group', { name: 'Career mode' });
    await expect(mode).toBeVisible();
    await expect(mode.getByRole('button', { name: 'Normal Career' })).toBeVisible();
    await expect(mode.getByRole('button', { name: 'Independent Training' })).toBeVisible();

    // The policy selector is the idle mode's one required input, so it is absent until the mode is on.
    await expect(page.getByRole('group', { name: 'Training policy' })).toHaveCount(0);

    await mode.getByRole('button', { name: 'Independent Training' }).click();

    const policy = page.getByRole('group', { name: 'Training policy' });
    await expect(policy).toBeVisible({ timeout: WRITE.timeout });
    await expect(policy.getByLabel('Balanced')).toBeVisible();
    await expect(policy.getByLabel('Stamina focus')).toBeVisible();
    await expect(policy.getByLabel('Sprint focus')).toBeVisible();

    // The recommendation the reference records as Kamigame's, printed as the selector's own note.
    await expect(page.getByText('Balanced is recommended', { exact: false })).toBeVisible();

    // And the primary names the mode it will start.
    await expect(page.getByRole('button', { name: 'Start Independent Training' })).toBeVisible();
});
