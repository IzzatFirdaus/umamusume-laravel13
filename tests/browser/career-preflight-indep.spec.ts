import { test, expect } from './support/base-test';

/*
 * Rendered-copy evidence for the Preflight step's Independent Training state.
 *
 * S3.1 (2026-11-18) restored the door Phase E2 had cut. The history matters because this file used
 * to assert the opposite: `ae16ec88` removed the Career-mode switch and the policy radios because
 * `career-preflight.html:176-186` draws the Commit card and nothing else, and for two months nothing
 * in the product could start an idle-mode run — the mode survived only as a stored state the
 * Dashboard and Result screens displayed, while `Lifecycle::start()` and `StartCareerRequest` had
 * been wired the whole time. The master roadmap's S3.1 named that as the one real functional gap and
 * ordered the controls restored. They now live in a disclosure inside the Commit card, so the
 * wireframe's single filled action is still the page's single filled action.
 *
 * This file pins the RESTORED state from the SS1.1.7 walk: the disclosure exists, it is closed by
 * default so the page still reads as the wireframe draws it, and opening it reveals the three inputs.
 * `career-preflight.spec.ts:274` pins the same from its own walk.
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

test('Preflight offers the Independent Training door S3.1 restored, closed by default', async ({ page }) => {
    await draftThroughDeck(page);

    // The disclosure exists and is closed, so the Commit card still reads as the wireframe draws it.
    const disclosure = page.locator('[data-independent-training]');
    await expect(disclosure).toHaveCount(1);
    await expect(disclosure).not.toHaveAttribute('open', '');
    await expect(disclosure.getByText('Start this run as an Independent Training session')).toBeVisible();

    // The page's write is still the shipped one, and still the only filled action.
    await expect(page.getByRole('button', { name: 'Create the run' })).toBeVisible();

    // Opening it reveals the three inputs the mode fixes (UMAMUSUME_REFERENCE.md §1.1.7). The policy
    // select and the priority picker appear only once the toggle is on, which is the reveal S3.1's
    // acceptance line asks a browser case to prove.
    await disclosure.locator('summary').click();
    const toggle = page.locator('#independent');
    await expect(toggle).toHaveCount(1);
    await expect(page.locator('#policy')).toHaveCount(0);

    await toggle.check();
    await expect(page.locator('#policy')).toBeVisible();
    await expect(page.locator('#priority-skills')).toBeVisible();

    // The policy select is bound to the enum's own cases, not a page-local list.
    await expect(page.locator('#policy option')).toHaveCount(3);
});
