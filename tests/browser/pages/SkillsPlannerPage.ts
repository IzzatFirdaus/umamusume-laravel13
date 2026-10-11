import { expect, type Page, type Locator } from '@playwright/test';
import type { SkillFixture } from '../fixtures/riceShowerUnityCup.fixture';

const WRITE = { timeout: 60_000 } as const;

/**
 * Page object for the Skills Planner.
 */
export class SkillsPlannerPage {
    public constructor(public readonly page: Page) {}

    public async assertCoverageWarning(): Promise<void> {
        await this.page.getByRole('status').filter({ hasText: 'The skills still to learn cost' }).waitFor();
    }

    public async assertNoPrioritiesMessage(): Promise<void> {
        await this.page.getByText('This run has no build target, so no skill is Required and there is nothing to sum.').waitFor();
    }

    public learnedGroup(): Locator {
        return this.page.getByRole('region', { name: 'Learned' });
    }

    public requiredGroup(): Locator {
        return this.page.getByRole('region', { name: 'Required' });
    }

    /**
     * Mark a skill as Acquired through the AcquisitionStatusEditor table.
     */
    public async markAcquired(skill: SkillFixture, turnAcquired: number): Promise<void> {
        const row = this.page.locator('tr', { hasText: skill.name });
        await row.locator('select').selectOption('Acquired');
        await row.locator('input[type="number"]').fill(String(turnAcquired));
    }

    public async saveAcquisitionStatus(): Promise<void> {
        const button = this.page.getByRole('button', { name: 'Save acquisition status' });
        await expect(button).toBeEnabled();
        await button.click();
        // The controller redirects to the Cockpit after a successful save.
        await this.page.waitForURL(/\/cockpit$/, WRITE);
    }
}
