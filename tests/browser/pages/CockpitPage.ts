import type { Page, Locator } from '@playwright/test';

/**
 * Page object for the Career Cockpit, the read-only career dashboard.
 */
export class CockpitPage {
    public constructor(public readonly page: Page) {}

    public async goto(runUrl: string): Promise<void> {
        await this.page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
        await this.page.locator('#app > *').first().waitFor();
    }

    public region(name: string): Locator {
        return this.page.getByRole('region', { name });
    }

    public async assertTraineeAndScenario(traineeName: string, scenarioLabel: string): Promise<void> {
        await this.page.getByRole('heading', { name: traineeName, exact: true }).waitFor();
        await this.page.getByText(scenarioLabel).first().waitFor();
    }

    public async assertPositionLabel(year: string, month: string, turn: number): Promise<void> {
        const header = this.page.locator('section[aria-labelledby="career-header-heading"]');
        await header.getByText(year).waitFor();
        await header.getByText(month).waitFor();
        await header.getByText(String(turn)).waitFor();
    }

    public async assertStat(stat: string, value: number): Promise<void> {
        const row = this.page.getByRole('region', { name: 'Stats and state' });
        await row.getByText(`${stat}`, { exact: true }).waitFor();
        await row.getByText(String(value)).first().waitFor();
    }

    public async assertFansAndSP(fans: number, sp: number): Promise<void> {
        const region = this.page.getByRole('region', { name: 'Stats and state' });
        await region.getByText(fans.toLocaleString('en-US')).waitFor();
        await region.getByText(sp.toLocaleString('en-US')).waitFor();
    }

    public async openDeck(runUrl: string): Promise<void> {
        await this.page.goto(`${runUrl}/deck`, { waitUntil: 'domcontentloaded' });
        await this.page.locator('#app > *').first().waitFor();
    }

    public async openSkills(runUrl: string): Promise<void> {
        await this.page.goto(`${runUrl}/skills`, { waitUntil: 'domcontentloaded' });
        await this.page.locator('#app > *').first().waitFor();
    }

    public async openRaceDecision(runUrl: string): Promise<void> {
        await this.page.goto(`${runUrl}/races`, { waitUntil: 'domcontentloaded' });
        await this.page.locator('#app > *').first().waitFor();
    }

    public async openTraining(runUrl: string): Promise<void> {
        await this.page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
        await this.page.locator('#app > *').first().waitFor();
    }

    public async openVeteran(runUrl: string): Promise<void> {
        await this.page.goto(`${runUrl}/veteran`, { waitUntil: 'domcontentloaded' });
        await this.page.locator('#app > *').first().waitFor();
    }
}
