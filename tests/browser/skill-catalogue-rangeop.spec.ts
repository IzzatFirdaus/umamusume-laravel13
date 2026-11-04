import { test, expect } from '@playwright/test';
import {
    inventoryRangeOpCount,
    removeRangeOperatorSkills,
    seedRangeOperatorSkills,
} from '../utils/seed-range-operator';

/*
 * D6: the catalogue footer's range-operator count must be a COMPUTED number.
 *
 * The original fault was a hardcoded `rangeOpCount = 0` placeholder that never reached the
 * footer. A feature test of `rangeOperatorCount()` proves the method works, but not that the
 * value reaches the surface — and when this spec was first written it caught exactly that: the
 * controller computed 1 and the component still rendered the zero state, because
 * `Pages/Skills/Index.vue` never forwarded `rangeOpCount` to `SkillCatalog`. Both the prop
 * declaration and the binding were missing.
 *
 * These two cases close the gap:
 *
 *  1. Zero state: no range operators → footer shows the zero-state sentence and never "0".
 *  2. Non-zero: one seeded `distance_type<=2` skill + two exact-token controls → the footer's
 *     rendered number equals `uma:skill-inventory`'s independent count for the same scratch DB.
 *
 * The expected number is read from the inventory command, not written into the assertion, so a
 * footer that renders a constant fails. The seed writes into the suite's own scratch database
 * (KI-69) and is removed after.
 */

test.describe.configure({ timeout: 240_000 });

test.describe('Skill catalogue footer range-operator count (D6)', () => {
    test.afterEach(() => {
        removeRangeOperatorSkills();
    });

    test('footer shows zero-state text and never "0" when no range operators exist', async ({ page }) => {
        expect(inventoryRangeOpCount(), 'fixture DB must start with no range operators').toBe(0);

        await page.goto('/skills');
        await page.locator('#app > *').first().waitFor();

        const footer = page.getByTestId('catalogue-footer');
        const sentence = page.getByTestId('range-op-sentence');

        await expect(footer).toBeVisible();
        await expect(sentence).toContainText('no skill in the current catalog uses a range operator on distance');

        // SHR-1: the sentinel never renders as "0".
        await expect(sentence).not.toContainText('0');
    });

    test('footer renders the computed count when range operators exist', async ({ page }) => {
        seedRangeOperatorSkills();

        // Independent count: the inventory command's own parse of the same column, not
        // `SkillController::rangeOperatorCount()` — that method is what this test verifies.
        const expected = inventoryRangeOpCount();
        expect(expected, 'the seeded range-operator skill must be counted').toBe(1);

        await page.goto('/skills');
        await page.locator('#app > *').first().waitFor();

        const footer = page.getByTestId('catalogue-footer');
        const sentence = page.getByTestId('range-op-sentence');

        await expect(footer).toBeVisible();

        // Read the rendered number out of the DOM and compare it to the independent count.
        const rendered = await sentence.textContent();
        const match = rendered?.match(/^(\d+) skills have range operators on distance/);

        expect(match, `footer did not render a count: ${JSON.stringify(rendered)}`).not.toBeNull();
        expect(Number(match?.[1])).toBe(expected);

        // The placeholder the D6 rule forbids would render "0"; assert it does not.
        await expect(sentence).not.toContainText('0');
        await expect(sentence).not.toContainText('no skill in the current catalog uses a range operator on distance');
    });
});
