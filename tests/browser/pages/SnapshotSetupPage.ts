import type { Page } from '@playwright/test';
import { STARTING_STATE, RICE_SHOWER } from '../fixtures/riceShowerUnityCup.fixture';

const WRITE = { timeout: 60_000 } as const;

/**
 * Page object for recording an in-progress Rice Shower Unity Cup run through the snapshot flow.
 */
export class SnapshotSetupPage {
    public constructor(public readonly page: Page) {}

    /**
     * Walk the snapshot form, review screen, and creation to land on the new run's Cockpit.
     * Returns the bare run URL (without `/cockpit`).
     */
    public async createRiceShowerSnapshot(): Promise<string> {
        await this.page.goto('/career/snapshot/setup', { waitUntil: 'domcontentloaded' });
        await this.page.locator('#app > *').first().waitFor();

        await this.page.selectOption('select[name="scenario"]', { value: STARTING_STATE.scenario });

        // The trainee select is a plain <select>; Rice Shower is in the seeded roster.
        await this.page.selectOption('select[name="umamusume_id"]', { label: RICE_SHOWER.name });

        await this.page.selectOption('select[name="career_year"]', { value: String(STARTING_STATE.careerYear) });
        await this.page.selectOption('select[name="career_month"]', { value: String(STARTING_STATE.careerMonth) });
        await this.page.selectOption('select[name="career_phase"]', { value: STARTING_STATE.careerPhase });

        await this.page.locator('input[name="speed"]').fill(String(STARTING_STATE.stats.speed));
        await this.page.locator('input[name="stamina"]').fill(String(STARTING_STATE.stats.stamina));
        await this.page.locator('input[name="power"]').fill(String(STARTING_STATE.stats.power));
        await this.page.locator('input[name="guts"]').fill(String(STARTING_STATE.stats.guts));
        await this.page.locator('input[name="wit"]').fill(String(STARTING_STATE.stats.wit));
        await this.page.locator('input[name="fans"]').fill(String(STARTING_STATE.fans));
        await this.page.locator('input[name="skill_points"]').fill(String(STARTING_STATE.skillPoints));

        await this.page.getByRole('button', { name: 'Review snapshot' }).click();
        await this.page.waitForURL(/\/career\/snapshot\/review/, WRITE);
        await this.page.locator('#app > *').first().waitFor();

        // Confirm on the review screen.
        await this.page.getByRole('button', { name: 'Record snapshot' }).click();
        await this.page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);

        return this.page.url().replace(/\/cockpit$/, '');
    }
}
