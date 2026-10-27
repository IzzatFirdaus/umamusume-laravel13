import { test, expect, type Page } from '@playwright/test';

/*
 * The wizard's head-band action pair (ST-4 visual parity, Phase A.2).
 *
 * `career-*.html` draws every step's doors in `.hrow-actions`, in one order: the position line, then a
 * back affordance, then the one filled forward action. `eca5a628` cut the shell's extra exit link and did
 * not add the back affordance the wireframe draws, which left steps 2-5 with no exit above the fold.
 *
 * What is asserted here is the shape of that row on every step, not the label of a particular button: the
 * pair has to exist, the position has to be readable in it, and the two doors have to be the two the
 * wireframe draws. A page that grows a third control in the head band fails case 1, which is the
 * regression this file exists to catch — a step that answers "how do I leave" twice is the state
 * `eca5a628` was undoing.
 */

type Step = {
    slug: string;
    route: string;
    step: number;
    /** The wireframe's own back label, read off the file named in its comment. */
    back: RegExp;
    forward: RegExp;
};

/**
 * The six steps this file measures, with labels taken from each wireframe's `.hrow-actions` block:
 * `career-scenario.html:61-65`, `career-trainee.html:61-65`, `career-target.html:61-65`,
 * `career-legacy.html:61-65`, `career-deck.html:64-68`, `career-preflight.html:61-65`.
 *
 * The scenario step's back affordance is `Cancel`, not `Back`: it is the flow's entrance, and the
 * wireframe sends it to `index.html` rather than to a previous step.
 */
const STEPS: Step[] = [
    { slug: 'scenario', route: '/career/setup/scenario', step: 1, back: /^\s*Cancel\s*$/, forward: /^\s*Continue to trainee\s*$/i },
    { slug: 'trainee', route: '/career/setup/trainee', step: 2, back: /^\s*Back\s*$/, forward: /^\s*Continue to build target\s*$/i },
    { slug: 'target', route: '/career/setup/target', step: 3, back: /^\s*Back\s*$/, forward: /^\s*Continue to legacy\s*$/i },
    { slug: 'legacy', route: '/career/setup/legacy', step: 4, back: /^\s*Back\s*$/, forward: /^\s*Continue to deck\s*$/i },
    { slug: 'deck', route: '/career/setup/deck', step: 5, back: /^\s*Back\s*$/, forward: /^\s*Continue to preflight\s*$/i },
    { slug: 'preflight', route: '/career/setup/preflight', step: 6, back: /^\s*Back to deck\s*$/, forward: /^\s*Create the run\s*$/i },
];

async function open(page: Page, route: string): Promise<void> {
    await page.goto(route);
    await page.locator('#app > *').first().waitFor();
}

/** The head band's action row. Both `PageHeader` and `SetupLayout` mark it the same way. */
const actions = (page: Page) => page.locator('[data-region="hrow-actions"]');

test.describe('wizard head band actions', () => {
    /*
     * The whole-flow invariants, one page load each: the row holds exactly the position, a back
     * affordance and a forward action, and nothing anywhere on the step says "Leave setup".
     *
     * The count is on `a, button` rather than on children: `SetupLayout` renders the position as a
     * `<span>` inside the same row, so a child count would be three and would stop measuring "how many
     * doors are there".
     */
    for (const step of STEPS) {
        test(`${step.slug}: head band carries the position, a back door and one forward door`, async ({ page }) => {
            await open(page, step.route);

            const row = actions(page);
            await expect(row, `${step.slug} renders a head band action row`).toHaveCount(1);

            // Position first, in the wireframe's words.
            await expect(row.locator('[data-region="hrow-position"]')).toHaveText(
                new RegExp(`Step ${step.step} of 6`),
            );

            // Exactly two doors. A third is the duplicate-exit defect this phase removed.
            const doors = row.locator('a, button');
            await expect(doors, `${step.slug} draws exactly a back door and a forward door`).toHaveCount(2);

            // Order is the wireframe's: back, then forward. The forward is the row's last door.
            await expect(doors.first()).toHaveText(step.back);
            await expect(doors.last()).toHaveText(step.forward);

            // One filled action per screen: the forward door. `data-variant` is `BaseButton`'s own marker;
            // the plain `<a>`/`<button>` doors this phase added carry the primary-action background class.
            const filled = row.locator('.bg-primary-action, [data-variant="primary"]');
            await expect(filled, `${step.slug} has exactly one filled head-band action`).toHaveCount(1);
            await expect(filled).toHaveText(step.forward);
        });
    }

    test('no step renders a second exit labelled "Leave setup"', async ({ page }) => {
        for (const step of STEPS) {
            await open(page, step.route);

            await expect(
                page.getByRole('link', { name: 'Leave setup' }),
                `${step.slug} must not draw the shell's removed exit link`,
            ).toHaveCount(0);
        }
    });

    test('scenario: the back door is Cancel, and it leaves the flow', async ({ page }) => {
        await open(page, '/career/setup/scenario');

        const cancel = actions(page).locator('a, button').first();
        await expect(cancel).toHaveText(/^\s*Cancel\s*$/);
        // Inertia's `<Link>` renders an absolute `href`, so these read the path: `/` and
        // `http://host/` are the same destination, and the port changes per run.
        await expect(cancel).toHaveAttribute('href', /\/$/);
    });

    test('preflight: the back door returns to the deck step', async ({ page }) => {
        await open(page, '/career/setup/preflight');

        const back = actions(page).locator('a, button').first();
        await expect(back).toHaveText(/^\s*Back to deck\s*$/);
        await expect(back).toHaveAttribute('href', /\/career\/setup\/deck$/);
    });

    test('every step links back to the step before it', async ({ page }) => {
        const previous: Record<string, string> = {
            trainee: '/career/setup/scenario',
            target: '/career/setup/trainee',
            legacy: '/career/setup/target',
            deck: '/career/setup/legacy',
        };

        for (const [slug, href] of Object.entries(previous)) {
            const step = STEPS.find((candidate) => candidate.slug === slug);

            if (step === undefined) {
                throw new Error(`no step named ${slug}`);
            }

            await open(page, step.route);
            await expect(
                actions(page).locator('a, button').first(),
                `${slug} back door`,
            ).toHaveAttribute('href', new RegExp(`${href}$`));
        }
    });

    /*
     * Blocked by a peer session, not by this phase.
     *
     * `resources/js/pages/Career/TraineeProfile.vue` carries a 427-line rewrite in flight (mtime
     * 2026-10-27 01:44:01), so Phase 4 — which owns this page — cannot start and this phase may not edit
     * the file. The page already prints "Choose a different trainee" and "Continue to build target", but
     * both still sit in the body rather than in `.hrow-actions`, so asserting the pair here would fail
     * against a file no phase in this slice is allowed to change.
     *
     * When Phase 4 lands, delete `fixme` and this case joins the loop above.
     */
    test.fixme('profile: head band carries "Choose a different trainee" and the forward door', async ({ page }) => {
        await open(page, '/career/setup/trainee/1');

        const row = actions(page);
        await expect(row.locator('a, button')).toHaveCount(2);
        await expect(row.locator('a, button').first()).toHaveText(/^\s*Choose a different trainee\s*$/i);
        await expect(row.locator('a, button').last()).toHaveText(/^\s*Continue to build target\s*$/i);
    });
});
