import { test, expect } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';
import { startCareer } from '../utils/start-career';

/*
 * The `Team setup` door (`v3.0.0`).
 *
 * The cockpit's inline `Edit team state` disclosure retires in the same release, so this walks the
 * replacement path end to end: Cockpit → `Where to go` → the team page → back. The seven scalar fields
 * and the five grade selects are asserted by id because the editor owns them; this case proves the page
 * mounts the editor with its action and run identity, not that the editor's own form works (that is
 * `career-cockpit.spec.ts`'s team-state coverage, retargeted with it).
 */
const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

test('walks from the cockpit door to the team page and back', async ({ page }) => {
    // `startCareer` leaves the browser on the Cockpit, so the door is already in view.
    createdRunUrls.push(await startCareer(page, 'unity_cup'));

    await page.getByRole('link', { name: 'Team setup' }).click();
    await expect(page).toHaveURL(/\/training-runs\/\d+\/team$/);
    await expect(page.getByRole('heading', { level: 1, name: 'Edit team state' })).toBeVisible();

    for (const field of [
        'team_name',
        'team_motto',
        'team_league_placement',
        'team_preseason_wins',
        'unity_trainings_count',
        'spirit_bursts_count',
        'extreme_bursts_count',
    ]) {
        await expect(page.locator(`#${field}`), `${field} is missing from the team page`).toBeVisible();
    }

    await expect(page.getByText('Team stat grades')).toBeVisible();

    await page.getByRole('link', { name: 'Back to Cockpit' }).click();
    await expect(page).toHaveURL(/\/cockpit$/);
    await expect(page.getByRole('heading', { name: 'Where to go' })).toBeVisible();
});
