import { expect, type Page } from '@playwright/test';

/*
 * The Cockpit's identity writes sit behind one disclosure (audit 2026-10-12 §3.3: the fields a Trainer
 * must open to change a value), and a collapsed `<details>` lays out none of its controls, so a case that
 * drives one has to open it first. Same reason `tap-targets.ts` skips an unrendered control: a locator
 * that waits on a box the browser never paints times out on a page that is working as designed.
 *
 * Idempotent because a blind `click()` closes an open one, and a summary is not the kind of control a
 * caller can tell the state of from outside. No caller here reaches it twice on one page load.
 */
export async function openIdentityDisclosure(page: Page): Promise<void> {
    const identity = page.locator('details').filter({ has: page.locator('select#status') });

    expect(await identity.count(), 'the Cockpit does not render exactly one disclosure holding select#status').toBe(1);

    if (!(await identity.evaluate((el) => (el as HTMLDetailsElement).open))) {
        await identity.locator('summary').click();
    }
}
