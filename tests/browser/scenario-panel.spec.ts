import { test, expect } from '@playwright/test';
import { buildAxe, openAllDisclosures } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { expectTapTargets } from '../utils/tap-targets';

/*
 * The scenario panel registry's browser seam, `SCR-CAR-019` (SCREEN-014 / 015 / 016, plan §9 E1).
 * `ScenarioPanelTest` pins the payload and the G-33 property.
 *
 * The rendered half of this spec went with the panel shell in the HFW parity pass: the cockpit no
 * longer mounts `ScenarioPanel`, so there is no disclosure to open and no fallback line to read on a
 * page. What survives is the registry resolution (a widget key resolves the panel component and the
 * map resolves its own kind) and the two page-level sweeps that never depended on the panel.
 *
 * A run is created through the assistant screens the way the other career specs do it, then walked
 * to the Cockpit. Writes get 60s because `php artisan serve` is a single-process `php -S`.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

/** A career of the given scenario, created the short way, then its Cockpit. */
async function openCockpit(page: import('@playwright/test').Page, scenario: string): Promise<void> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#trainee-combobox').fill('Rice Shower');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: scenario });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);
    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

/** A Unity Cup career, then its Cockpit: the one scenario with two resources and three panels on. */
async function openUnityCupCockpit(page: import('@playwright/test').Page): Promise<void> {
    await openCockpit(page, 'unity_cup');
}

test('keeps a widget key from resolving a panel component, and each map resolves its own kind', async () => {
    // The registry seam in isolation. The two maps are separate so a config entry that names a
    // widget exactly like a registered panel flag cannot resolve the panel component against widget
    // props — the defect `f523dcd` removed from the shell's registrations (F3).
    const { registerWidget, registerPanel, resolveWidget, resolvePanel } = await import(
        '../../resources/js/components/scenario/registry'
    );

    expect(resolveWidget('no_renderer_claims_this_key')).toBeUndefined();
    expect(resolvePanel('no_renderer_claims_this_key')).toBeUndefined();

    const widgetProbe = { name: 'WidgetProbe', render: (): null => null };
    const panelProbe = { name: 'PanelProbe', render: (): null => null };

    // A widget registered under a key resolves in the widget map only.
    registerWidget('probe_widget', widgetProbe);
    expect(resolveWidget('probe_widget')).toBe(widgetProbe);
    expect(resolvePanel('probe_widget')).toBeUndefined();

    // A panel registered under the same key resolves in the panel map only.
    registerPanel('probe_widget', panelProbe);
    expect(resolvePanel('probe_widget')).toBe(panelProbe);
    expect(resolveWidget('probe_widget')).toBe(widgetProbe);
});

test('keeps the 44px floor across the cockpit and reflows at 320 px', async ({ page }) => {
    await openUnityCupCockpit(page);

    // The scenario panel shell was removed with the HFW parity pass, but the page-level sweep still
    // earns its keep: it covers every control the cockpit carries. `expectTapTargets` skips what a
    // collapsed disclosure hides and asserts it measured something; 10 is well under the ~16 controls
    // this page carries, so a selector that stopped matching still fails.
    await expectTapTargets(page.locator('main a, main button'), 10);

    await page.setViewportSize({ width: 320, height: 700 });
    await expect(page.getByRole('region', { name: 'Scenario' })).toBeVisible();
    // No sideways scroll at the reflow width (WCAG 1.4.10).
    const overflow = await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    );
    expect(overflow, 'the cockpit scrolls sideways at 320px').toBeLessThanOrEqual(1);
});

test('passes the axe A + AA scan with the panel mounted', async ({ page }) => {
    await openUnityCupCockpit(page);

    await openAllDisclosures(page);
    await buildAxe(page).analyze();
});
