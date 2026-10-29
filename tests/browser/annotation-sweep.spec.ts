import { expect, test } from '@playwright/test';

/*
 * S12 — the annotation sweep.
 *
 * The wireframe set is a document: every page in it carries a `.keybox` "Annotation key", a
 * "Static wireframe" provenance chip, and (on the landing screen) a "Table of contents" card. None of
 * those is a product element. They are the document's own navigation aids and its editorial notes, and
 * a screen that renders one has copied the paper rather than the product.
 *
 * This sweep is route-wide rather than per-page on purpose. The per-page specs each guard the screen
 * they own, which is the right place for a screen's own assertions and the wrong place for a release
 * gate: a new page added without its own guard would pass every existing one. Here the guard follows
 * the route table, so a page that never got a spec is still held to the rule.
 *
 * The console assertion rides along because both failures are the same failure: apparatus pasted from
 * the document usually arrives with an asset or a component that resolves to nothing.
 */

/** The routes that render a page a Trainer can reach without a seeded run or candidate. */
const routes: [string, string][] = [
    ['/', 'Dashboard'],
    ['/career/setup/scenario', 'Career scenario'],
    ['/career/setup/trainee', 'Career trainee'],
    ['/career/setup/target', 'Career build target'],
    ['/career/setup/legacy', 'Career legacy'],
    ['/career/setup/deck', 'Career deck'],
    ['/career/setup/preflight', 'Career preflight'],
    ['/career/snapshot', 'Career snapshot entry'],
    ['/umamusume', 'Catalog'],
    ['/skills', 'Skills'],
    ['/support-cards', 'Support cards'],
    ['/training-runs', 'Runs'],
    ['/training-runs/create', 'Runs create'],
    ['/training-runs/import', 'Runs import'],
    ['/review', 'Review queue'],
    ['/legacy', 'Legacy lab'],
    ['/veterans', 'Veterans'],
    ['/help', 'Help'],
    ['/preferences', 'Preferences'],
];

for (const [path, name] of routes) {
    test(`${name} renders no wireframe apparatus`, async ({ page }) => {
        const errors: string[] = [];
        page.on('console', (message) => {
            if (message.type() === 'error') errors.push(message.text());
        });

        await page.goto(path);
        await page.locator('#app > *').first().waitFor();

        // The document's three blocks, by class and by heading, so neither a renamed heading nor a
        // restyled block slips through.
        await expect(page.locator('.keybox'), `${path}: annotation key block`).toHaveCount(0);
        await expect(page.locator('.toc'), `${path}: table of contents block`).toHaveCount(0);
        await expect(page.getByRole('heading', { name: /Annotation key/i })).toHaveCount(0);
        await expect(page.getByRole('heading', { name: /Table of contents/i })).toHaveCount(0);

        // The document's provenance chip. Matched as a word inside a chip, not as a loose substring:
        // a page may legitimately say "wireframe" in a comment, and no page renders this label.
        await expect(page.getByText('Static wireframe')).toHaveCount(0);

        expect(errors, `${path}: console errors`).toEqual([]);
    });
}
