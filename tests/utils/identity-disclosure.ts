import { expect, type Page } from '@playwright/test';

/*
 * The Cockpit's identity writes sit behind one disclosure (audit 2026-10-12 §3.3: the fields a Trainer
 * must open to change a value), and a collapsed `<details>` lays out none of its controls, so a case that
 * drives one has to open it first. Same reason `tap-targets.ts` skips an unrendered control: a locator
 * that waits on a box the browser never paints times out on a page that is working as designed.
 *
 * "Edit identity" is nested inside "Career tools", so the control sits under TWO collapsed disclosures
 * and the helper opens the chain from the outside in. A nested summary is not rendered while its parent
 * is closed — measured, not assumed: with "Career tools" shut, the "Edit identity" summary reports
 * `isVisible() === false` and `click()` waits out its timeout, and the control becomes visible only once
 * both are open.
 *
 * The guard counts the disclosures that OWN the control — those with no other disclosure between them
 * and the select — so a second identity disclosure is still a failure while the grouping above it is
 * not. Counting every containing disclosure instead would count "Career tools" as a second one, and
 * the helper would refuse the page it was written for.
 *
 * Idempotent because a blind `click()` closes an open one, and a summary is not the kind of control a
 * caller can tell the state of from outside. No caller here reaches it twice on one page load.
 */
export async function openIdentityDisclosure(page: Page): Promise<void> {
    // The Cockpit renders client-side, so a caller that arrives straight after a navigation can beat
    // the select into the DOM and count zero owners. Wait for the control itself before counting.
    await page.locator('select#status').first().waitFor({ state: 'attached' });

    const containers = page.locator('details').filter({ has: page.locator('select#status') });

    // An owner is the disclosure with no OTHER disclosure between it and the select. The predicate
    // cannot use `querySelector('details select#status')`: selector matching resolves the ancestor
    // part against the whole document, so the node itself satisfies `details` and every container
    // would look nested. Comparing the inner disclosures against the select is the real test.
    const owners = await containers.evaluateAll((nodes) =>
        nodes.filter((node) => {
            const select = node.querySelector('select#status');

            if (select === null) {
                return false;
            }

            return !Array.from(node.querySelectorAll('details')).some((inner) => inner.contains(select));
        }).length,
    );

    expect(owners, 'the Cockpit does not render exactly one disclosure holding select#status').toBe(1);

    // Every disclosure between the select and the document, outermost first. The chain is the same set as
    // `containers`, in the same DOM order: an ancestor contains the select, so it contains the select's
    // own disclosure too, and an ancestor always precedes its descendant in document order.
    const chain = await page.locator('select#status').evaluate((select) => {
        const disclosures: HTMLDetailsElement[] = [];

        for (let node: Element | null = select; node !== null; node = node.parentElement) {
            if (node instanceof HTMLDetailsElement) {
                disclosures.unshift(node);
            }
        }

        return disclosures.map((disclosure) => disclosure.open);
    });

    for (const [index, open] of chain.entries()) {
        if (open) {
            continue;
        }

        // The disclosure's own summary is the first `<summary>` inside it; a nested disclosure's summary
        // comes later in document order.
        await containers.nth(index).locator('summary').first().click();
    }
}
