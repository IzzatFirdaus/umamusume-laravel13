import { expect, type Page, type Locator } from '@playwright/test';
import type { SupportCardFixture } from '../fixtures/riceShowerUnityCup.fixture';

const WRITE = { timeout: 60_000 } as const;

/**
 * Page object for the support deck builder.
 */
export class DeckBuilderPage {
    public constructor(public readonly page: Page) {}

    public slot(position: number): Locator {
        return this.page.locator(`#deck-slot-${position}`);
    }

    /**
     * Equip a card into a slot by searching for it in the picker.
     */
    public async equipCard(position: number, card: SupportCardFixture): Promise<void> {
        const slot = this.slot(position);
        // An empty slot's control says "Choose a card for …"; a filled one says "Replace the card
        // in …". `slot` labels can carry a suffix ("Slot 6 · Friends"), so the match is on the
        // position's word boundary rather than on an exact label.
        await slot
            .getByRole('button', { name: new RegExp(`^(?:Choose a card for|Replace the card in) Slot ${position}\\b`) })
            .click();

        // `startReplace` is an async Inertia navigation that carries `slot=<position>` in the query
        // string; the picker's Equip buttons only target this slot once it has landed. Filtering
        // before that would send the stale slot back and the label would never change.
        await this.page.waitForURL(new RegExp(`[?&]slot=${position}\\b`), { timeout: 30_000 });

        // The picker now targets this slot. Search by a distinctive card title fragment.
        const queryInput = this.page.locator('input[name="query"]');
        await queryInput.fill('');
        await queryInput.fill(card.searchQuery);
        await this.page.getByRole('button', { name: 'Filter' }).click();

        // Locate the card's own row and its Equip button. Slot labels differ ("Slot 2", the friend
        // slot reads "Slot 6 · Friends"), so matching the label text is brittle; the row is unique
        // per card and carries exactly one Equip button.
        const row = this.page.locator('#deck-picker-results li', {
            has: this.page.getByRole('link', { name: card.displayName }),
        });
        await row.getByRole('button', { name: /Equip to Slot/ }).click();

        // Focus returns to the slot; wait until the slot shows the card name.
        await this.slot(position).getByText(card.displayName).waitFor();
    }

    /**
     * Toggle the ownership of a slot between Owned and Rented.
     */
    public async setOwnership(position: number, ownership: 'Owned' | 'Rented'): Promise<void> {
        const button = this.slot(position).getByRole('button', { name: ownership, exact: true });
        await button.click();
        await expect(button).toHaveAttribute('aria-pressed', 'true');
    }

    public async saveDeck(): Promise<void> {
        await this.page.getByRole('button', { name: 'Confirm deck' }).click();
        // The write redirects to the Cockpit, the same door every other run write returns to.
        await this.page.waitForURL(/\/cockpit$/, WRITE);
    }
}
