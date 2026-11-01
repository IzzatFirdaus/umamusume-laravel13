import { expect, type Page } from '@playwright/test';

/*
 * The scenario panels (URA, Trackblazer, Grand Concert, Unity Cup) used to render inside a closed
 * `details[data-region="scenario-detail"]` on the Cockpit, so a spec that read a panel had to open a
 * disclosure. In v3.0.0 that disclosure is gone and the panels live on their own page, reached by the
 * `Scenario detail` door in "Where to go".
 *
 * `openScenarioDetail` keeps its name and signature, so every spec that already calls it follows the
 * panels to the new page without a change of its own. It derives the run id from whatever run page the
 * browser is on, because callers reach it from the Cockpit, from the team page and from the panels
 * page itself.
 *
 * The contract is "the panels are now on screen", not "the browser is back where it started". A case
 * whose subject is the shell calls `backToCockpit` when it needs the shell again; folding that into
 * this helper would silently undo the navigation every panel-reading case depends on.
 */

/** The run id in the current URL, or null when the browser is not on a run page. */
function runId(page: Page): string | null {
    return page.url().match(/\/training-runs\/(\d+)/)?.[1] ?? null;
}

export async function openScenarioDetail(page: Page): Promise<void> {
    // The page mounts client-side, so the cockpit's own regions do not exist yet on a cold arrival.
    await page.locator('#app > *').first().waitFor();

    if (/\/scenario(\?|#|$)/.test(page.url())) {
        return;
    }

    const id = runId(page);

    if (id === null) {
        throw new Error(`openScenarioDetail expects a run page; the browser is on ${page.url()}`);
    }

    await page.goto(`/training-runs/${id}/scenario`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await expect(page).toHaveURL(new RegExp(`/training-runs/${id}/scenario$`));
}

/**
 * Returns to the Cockpit for the run in the current URL. For a case whose subject is the shell but
 * which shares an opener that lands on the panels page.
 */
export async function backToCockpit(page: Page): Promise<void> {
    await gotoRunPage(page, 'cockpit');
}

/** Opens the team write's own page, which replaced the cockpit's `Edit team state` disclosure. */
export async function openTeamEdit(page: Page): Promise<void> {
    await gotoRunPage(page, 'team');
}

async function gotoRunPage(page: Page, segment: 'cockpit' | 'team'): Promise<void> {
    const id = runId(page);

    if (id === null) {
        throw new Error(`expected a run page; the browser is on ${page.url()}`);
    }

    await page.goto(`/training-runs/${id}/${segment}`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}
