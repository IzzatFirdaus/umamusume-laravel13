import { test, expect } from '@playwright/test';
import type { Page } from '@playwright/test';
import { deleteRun } from '../utils/delete-run';
import { buildAxe } from '../utils/accessibility';
import { SnapshotSetupPage } from './pages/SnapshotSetupPage';
import { CockpitPage } from './pages/CockpitPage';
import { DeckBuilderPage } from './pages/DeckBuilderPage';
import { SkillsPlannerPage } from './pages/SkillsPlannerPage';
import { seedOwnedSkills, setBuildTarget } from './support/riceShowerHelpers';
import {
    RICE_SHOWER,
    STARTING_STATE,
    SUPPORT_DECK,
    OWNED_SKILLS,
    UNITY_CUP_TEAM_INFO,
    KYOTO_DAISHOTEN,
} from './fixtures/riceShowerUnityCup.fixture';

/**
 * Repository-grounded regression suite for the documented Rice Shower Unity Cup run.
 *
 * Source: docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md
 *
 * What is tested:
 *   - Snapshot entry records the starting state.
 *   - Cockpit reflects trainee, scenario, position, stats, fans and SP.
 *   - Deck builder can equip the six documented cards and persist them.
 *   - Skills planner can mark the documented owned skills as acquired.
 *   - Race decision screen lists the calendar race at the snapshot turn.
 *   - Training decision screen allows a turn to be recorded.
 *   - Completed run can be filed as a Veteran and appears in the library.
 *
 * What is not tested (documented limitations):
 *   - Unity Cup team rank, burst counters and roster: the UI has no write path and renders them as named absences.
 *   - The November event reward and Unity Cup finals: no playable UI exists.
 *   - Skill purchasing / hint discounts: the application explicitly does not model them.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

async function createRun(page: Page): Promise<string> {
    const setup = new SnapshotSetupPage(page);
    const runUrl = await setup.createRiceShowerSnapshot();
    createdRunUrls.push(runUrl);
    return runUrl;
}

test.describe('Starting state', () => {
    test('records the snapshot and shows it on the cockpit', async ({ page }) => {
        const runUrl = await createRun(page);
        const cockpit = new CockpitPage(page);
        await cockpit.goto(runUrl);

        await cockpit.assertTraineeAndScenario(RICE_SHOWER.name, 'Unity Cup');
        await cockpit.assertPositionLabel('Senior Year', 'October', 67);

        const stats = STARTING_STATE.stats;
        await cockpit.assertStat('Speed', stats.speed);
        await cockpit.assertStat('Stamina', stats.stamina);
        await cockpit.assertStat('Power', stats.power);
        await cockpit.assertStat('Guts', stats.guts);
        await cockpit.assertStat('Wit', stats.wit);

        await cockpit.assertFansAndSP(STARTING_STATE.fans, STARTING_STATE.skillPoints);

        // The Unity Cup scenario panel renders config facts and refuses to invent recorded values.
        const scenario = cockpit.region('Scenario');
        await expect(scenario).toBeVisible();
        await expect(scenario.getByText(/Unity Cup/)).toBeVisible();

        // The team facts the run never recorded are rendered as absences, never as the report's
        // values. A Cockpit that printed any of these would be inventing a record (report §1.8;
        // none of these strings exists in `config/scenarios.php`, so the panel cannot source them).
        await expect(page.getByText(UNITY_CUP_TEAM_INFO.team)).toHaveCount(0);
        await expect(page.getByText(UNITY_CUP_TEAM_INFO.motto)).toHaveCount(0);
        await expect(page.getByText(UNITY_CUP_TEAM_INFO.rankingBonus)).toHaveCount(0);
        await expect(page.getByText(UNITY_CUP_TEAM_INFO.members)).toHaveCount(0);
    });

    test('passes an axe scan on the cockpit with the snapshot loaded', async ({ page }) => {
        const runUrl = await createRun(page);
        await new CockpitPage(page).goto(runUrl);

        const results = await buildAxe(page).analyze();
        expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
    });
});

test.describe('Support deck', () => {
    test('equips the six documented cards and persists the layout', async ({ page }) => {
        const runUrl = await createRun(page);
        const cockpit = new CockpitPage(page);
        await cockpit.openDeck(runUrl);

        const builder = new DeckBuilderPage(page);

        for (let i = 0; i < SUPPORT_DECK.length; i++) {
            const card = SUPPORT_DECK[i];
            const position = i + 1;
            await builder.equipCard(position, card);

            if (card.isFriend) {
                await builder.setOwnership(position, 'Rented');
            } else {
                await builder.setOwnership(position, 'Owned');
            }
        }

        await builder.saveDeck();

        // The save redirects to the Cockpit; the persisted layout is read back on the builder.
        await cockpit.openDeck(runUrl);

        // After save, every slot still shows the expected card name.
        for (let i = 0; i < SUPPORT_DECK.length; i++) {
            const position = i + 1;
            await expect(builder.slot(position)).toContainText(SUPPORT_DECK[i].displayName);
        }

        // The analysis section rendered because at least one card contributes an effect.
        const analysis = page.getByRole('region', { name: 'Deck analysis' });
        await expect(analysis).toBeVisible();
        await expect(analysis.getByRole('heading', { name: 'Training power' })).toBeVisible();
    });
});

test.describe('Skills planner', () => {
    test('seeds and marks the documented owned skills as acquired', async ({ page }) => {
        const runUrl = await createRun(page);

        await setBuildTarget({ runUrl, page });
        await seedOwnedSkills({ runUrl, page });

        const cockpit = new CockpitPage(page);
        await cockpit.openSkills(runUrl);

        const planner = new SkillsPlannerPage(page);
        await expect(planner.requiredGroup()).toBeVisible();

        // Mark the first three owned skills as Acquired through the editor.
        for (const skill of OWNED_SKILLS.slice(0, 3)) {
            await planner.markAcquired(skill, 67);
        }
        await planner.saveAcquisitionStatus();

        // The save redirects to the Cockpit; navigate back and assert they moved into the Learned group.
        await cockpit.openSkills(runUrl);

        const learned = planner.learnedGroup();
        for (const skill of OWNED_SKILLS.slice(0, 3)) {
            await expect(learned.getByText(skill.name)).toBeVisible();
        }
    });
});

test.describe('Race decision', () => {
    test('shows the calendar at the snapshot turn', async ({ page }) => {
        const runUrl = await createRun(page);
        const cockpit = new CockpitPage(page);
        await cockpit.openRaceDecision(runUrl);

        // The snapshot is at Senior Early October; a race on the calendar should be listed.
        const section = page.getByRole('region', { name: 'Races to decide between' });
        await expect(section).toBeVisible();
        const raceCard = section.locator('li', { has: page.getByRole('heading', { name: KYOTO_DAISHOTEN.title }) }).first();
        await expect(raceCard.getByText(KYOTO_DAISHOTEN.tier).first()).toBeVisible();
    });
});

test.describe('Training decision', () => {
    test('records a training turn and advances the career', async ({ page }) => {
        const runUrl = await createRun(page);
        const cockpit = new CockpitPage(page);
        await cockpit.openTraining(runUrl);

        const speedCard = page.locator('article', { has: page.getByRole('heading', { name: 'Speed', exact: true }) });
        await speedCard.getByRole('button', { name: 'Train' }).click();
        await page.locator('input[name="speed"]').fill('650');
        await page.locator('input[name="stamina"]').fill('590');
        await page.locator('input[name="power"]').fill('560');
        await page.locator('input[name="guts"]').fill('750');
        await page.locator('input[name="wit"]').fill('400');
        await page.locator('input[name="sp"]').fill('180');
        await page.locator('input[name="fans"]').fill('210000');
        await page.locator('input[name="energy"]').fill('50');

        await page.locator('select[name="outcome"]').selectOption('Success');
        await page.getByRole('button', { name: 'Record this training' }).click();
        await page.waitForURL(/\/cockpit$/, WRITE);

        // The Cockpit now shows the recorded turn.
        const recorded = page.getByRole('region', { name: 'Recorded action' });
        await expect(recorded).toBeVisible();
        await expect(recorded).toContainText('Speed');
        await expect(recorded).toContainText('Recorded');
    });
});

test.describe('Veteran lifecycle', () => {
    test('saves a Veteran after completing the run', async ({ page }) => {
        const runUrl = await createRun(page);

        // Mark run completed through the Cockpit status form.
        const cockpit = new CockpitPage(page);
        await cockpit.goto(runUrl);
        const statusForm = page.locator('form').filter({ has: page.getByRole('button', { name: 'Change status' }) });
        await statusForm.locator('select[name="status"]').selectOption('Completed');
        await statusForm.getByRole('button', { name: 'Change status' }).click();
        await page.getByText('Run updated.').waitFor(WRITE);

        await cockpit.openVeteran(runUrl);
        await page.getByRole('heading', { name: 'Save Veteran' }).waitFor();

        await page.locator('#custom-tag').fill('unity-cup regression');
        await page.getByRole('button', { name: 'Add tag' }).click();
        await page.locator('#veteran-notes').fill('Rice Shower Unity Cup regression run.');
        await page.getByRole('button', { name: 'Save Veteran' }).click();

        await page.waitForURL(/\/veterans\/\d+$/, WRITE);
        await expect(page.getByText('Veteran saved: Rice Shower.')).toBeVisible();
        await expect(page.getByText('unity-cup regression')).toBeVisible();
    });
});
