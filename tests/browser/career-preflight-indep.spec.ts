import { test, expect } from '@playwright/test';

/*
 * Rendered-copy evidence for the Preflight step's Independent Training state.
 *
 * The SS1.1.7 entry tab is gone: Phase E2 cut the Career-mode switch and the policy radios from
 * Preflight (`ae16ec88`; `career-preflight.html:176-186` draws the Commit card and nothing else),
 * and nothing else in the product starts an idle-mode run — the mode survives only as a stored
 * state the Dashboard and Result screens display. `career-preflight.spec.ts:274` pins the
 * absence from its own walk; this file pins it from the SS1.1.7 walk (steps 1, 2 and 5), so no
 * wizard draft resurrects the controls. `StartCareerRequest` keeps `independent` nullable, so a
 * run body without the mode is still a valid start.
 *
 * **This walk stops at steps 1, 2 and 5 on purpose.** `tests/utils/start-career.ts` drives step 3 by
 * its field names (`select[name="distance"]` and friends), and step 3 is the surface a peer pass is
 * rebuilding in its own working tree, so a walk through it times out on a control this spec does not
 * need. The walk reaches the page the assertions are about and nothing else.
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

test('Preflight draws no Career mode switch and no policy selector (Phase E2 cut, SS1.1.7 walk)', async ({ page }) => {
    await draftThroughDeck(page);

    // Phase E2 cut (`ae16ec88`): `career-preflight.html:176-186` draws the Commit card and nothing
    // else. The write carries no choice of its own, so no wizard draft can resurrect the controls.
    // The sibling `career-preflight.spec.ts:274` pins the same absence from its own walk.
    await expect(page.getByText('Career mode')).toHaveCount(0);
    await expect(page.getByText('Independent Training')).toHaveCount(0);
    await expect(page.getByText('Training policy')).toHaveCount(0);

    // And the primary names the shipped write, not the cut mode.
    await expect(page.getByRole('button', { name: 'Create the run' })).toBeVisible();
});
