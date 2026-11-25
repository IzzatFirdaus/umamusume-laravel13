import { test, expect } from './support/base-test';
import { execFileSync } from 'node:child_process';
import { buildAxe, openAllDisclosures } from '../utils/accessibility';
import { deleteRun } from '../utils/delete-run';
import { openIdentitySettings } from '../utils/identity-disclosure';
import { resolvedScratchDatabase } from './global-setup';

/*
 * Browser evidence for the published career reference panel (`CareerReferencePanel.vue`), which no
 * other spec covers. The panel is a read-only `<details>` disclosure rendered by both the run
 * Timeline (`/training-runs/{id}/timeline`) and the Veteran detail screen
 * (`/veterans/{id}`), fed from the same `CareerReference::forRun()` payload both controllers
 * pass as `reference`.
 *
 * The server side (payload shape, absence, Timeline/Veteran parity) is pinned by
 * `tests/Feature/CareerReferenceEnrichmentTest.php`; what a Pest test cannot see is the rendered
 * disclosure: axe does not descend into a closed `<details>` (A-91's lesson), and the panel's
 * source/skill links, its missing-data fallback, and its evidence-boundary copy are all inside one.
 *
 * Three fixture halves, all through the UI:
 *  - Available: Tokai Teio's `[Peak Joy]` card (100301) + `ura_finale`. Card 100301 carries an
 *    objective schedule in `config/imported_career_reference.php`, so the panel opens with card
 *    facts, schedule and mechanics. Typing the card title, not the trainee name, is load-bearing:
 *    name+Enter commits the combobox's date-sort default, and an undated Japan-only form sorts
 *    ahead of every dated Global card.
 *  - Veteran parity: the same career filed through `/training-runs/{id}/veteran` after its status
 *    becomes Completed — the only path that puts a row in `veterans` (`tests/utils/file-career.ts`).
 *  - Absent: an imported run. The import form collects no costume card (`importChoices()` is
 *    deliberately narrower than `create()`'s), so the run names a trainee alone and the panel
 *    prints its fallback sentence. A create-page run cannot reach this state: its combobox always
 *    commits a Global-released card, and published skills alone make `available` true.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    while (createdRunUrls.length > 0) {
        await deleteRun(page, createdRunUrls.pop() as string);
    }
});

async function createRunWithCard(page: import('@playwright/test').Page): Promise<string> {
    await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    // The card title commits the exact form: the combobox prefix-matches `titleKey` (brackets
    // stripped), and one trainee owns it, so Enter on row zero is this card and nothing else.
    await page.locator('#trainee-combobox').fill('Peak Joy');
    await page.keyboard.press('Enter');
    await page.selectOption('select[name="scenario"]', { value: 'ura_finale' });
    await page.getByRole('button', { name: 'Create run' }).click();
    await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);

    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    return runUrl;
}

async function openTimeline(page: import('@playwright/test').Page, runUrl: string): Promise<void> {
    await page.goto(`${runUrl}/timeline`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
}

test.describe('Career reference panel', () => {
    test('prints the published reference on the Timeline with card facts, schedule and mechanics', async ({ page }) => {
        const runUrl = await createRunWithCard(page);

        await openTimeline(page, runUrl);

        const panel = page.locator('[data-career-reference]');
        await expect(panel).toHaveCount(1);

        // The disclosure is closed by default: the summary is reachable, and opening it is how the
        // content becomes a fact the case can read.
        const summary = panel.getByText('Published career reference');
        await expect(summary).toBeVisible();
        await summary.click();

        // Card reference: values a Pest test already pins server-side, now seen rendered.
        await expect(panel.getByText('Card reference')).toBeVisible();
        await expect(panel.getByText('[Peak Joy]', { exact: false })).toBeVisible();
        await expect(panel.getByRole('link', { name: 'Card source' })).toBeVisible();

        // The evidence boundary is copy, not a claim: the disclaimer and the schedule note.
        await expect(panel.getByText(/Published reference only\. Nothing here confirms/)).toBeVisible();
        await expect(panel.getByText("Published dates, not this career's results.")).toBeVisible();

        // An objective row rendered as a milestone, not as a result.
        await expect(panel.getByText(/Turn 12: Junior Make Debut/)).toBeVisible();

        // Mechanics reference: the scenario's own mechanics render beside the baseline sections.
        await expect(panel.getByText('Mechanics reference')).toBeVisible();
        await expect(panel.getByText('URA Finale mechanics')).toBeVisible();
        // Source conflicts stay visible rather than resolved (Phase A's evidence taxonomy).
        await expect(panel.getByText(/Source conflict: GameTora/).first()).toBeVisible();

        // Source links inside the closed-at-load disclosure: the Timeline's accessibility gate
        // measures links in `main`, so these carry the 44px minimum.
        const sourceLink = panel.getByRole('link', { name: 'Card source' });
        const minHeight = await sourceLink.evaluate((el) => parseFloat(getComputedStyle(el).minHeight));
        expect(minHeight, 'source links meet the 44px target').toBeGreaterThanOrEqual(44);
    });

    test('exposes the same panel on the Veteran detail screen', async ({ page }) => {
        const runUrl = await createRunWithCard(page);

        // A career that is not Completed cannot be filed: the status changes first, through the
        // same identity-settings form `fileCareer` walks.
        await openIdentitySettings(page);
        const statusForm = page.locator('form').filter({ has: page.getByRole('button', { name: 'Change status' }) });
        await statusForm.locator('select[name="status"]').selectOption('Completed');
        await statusForm.getByRole('button', { name: 'Change status' }).click();
        await expect(page.getByText('Run updated.')).toBeVisible(WRITE);

        await page.goto(`${runUrl}/veteran`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();
        await page.getByRole('button', { name: 'Save Veteran' }).click();
        await page.waitForURL(/\/veterans\/\d+$/, WRITE);
        const veteranUrl = page.url();

        await page.goto(veteranUrl, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        const panel = page.locator('[data-career-reference]');
        await expect(panel).toHaveCount(1);
        await panel.getByText('Published career reference').click();

        await expect(panel.getByText('Card reference')).toBeVisible();
        await expect(panel.getByText('[Peak Joy]', { exact: false })).toBeVisible();
        await expect(panel.getByText(/Turn 12: Junior Make Debut/)).toBeVisible();

        // Teardown needs nothing here: createRunWithCard already registered the run URL, and the
        // run DELETE cascades the Veteran row with it. Pushing the same run twice would 404 the
        // second delete.
    });

    test('names the absence when no published reference matches the career', async ({ page }) => {
        // `available` false needs a run with no card and no scenario reference. The seeded roster
        // leaves no cardless combobox row (every Global trainee owns at least her Global debut
        // card), so the state is reached the way the product says it happens: a manual trainee
        // (FR-B-4) is cardless by rule — nothing is attached under a hand-entered name — and the
        // combobox paints her one "No costume card confirmed yet" row that commits
        // `character_card_id` null. The tinker fixture follows
        // `career-legacy-parent-card-sync.spec.ts`'s pattern: it writes only to this suite's own
        // scratch database and only what a Trainer's own write path would write.
        const DB = resolvedScratchDatabase();
        const tinkerEnv = { ...process.env, DB_DATABASE: DB, UMA_DATABASE_ROLE: 'browser', UMA_EXPECTED_DATABASE: DB };
        const tinkerOutput = execFileSync('php', ['artisan', 'tinker', '--execute=' + [
            "$t = App\\Models\\Umamusume::create([",
            "    'slug' => 'manual-cardless-probe',",
            "    'name' => 'Manual Cardless Probe',",
            "    'release_status' => App\\Enums\\ReleaseStatus::GlobalReleased->value,",
            "    'is_manual' => true,",
            "]);",
            "echo 'ID:'.$t->id;",
        ].join(' ')], {
            cwd: process.cwd(),
            env: tinkerEnv,
            encoding: 'utf8',
        });
        const traineeId = /ID:(\d+)/.exec(tinkerOutput)?.[1];
        if (traineeId === undefined) {
            throw new Error('the manual trainee fixture did not report its id');
        }

        await page.goto('/training-runs/create', { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();
        await page.locator('#trainee-combobox').fill('Manual Cardless Probe');
        await page.keyboard.press('Enter');
        await page.selectOption('select[name="scenario"]', { value: '' });
        await page.getByRole('button', { name: 'Create run' }).click();
        await page.waitForURL(/\/training-runs\/\d+\/cockpit$/, WRITE);

        const runUrl = page.url().replace(/\/cockpit$/, '');
        createdRunUrls.push(runUrl);

        await openTimeline(page, runUrl);

        await expect(page.locator('[data-career-reference]')).toHaveCount(0);
        await expect(page.getByText(/No published reference matches this career's card and scenario/)).toBeVisible();
        await expect(page.getByText(/The facts below remain based only on stored observations/)).toBeVisible();
    });

    test('passes an axe scan at WCAG A and AA with the disclosure open', async ({ page }) => {
        const runUrl = await createRunWithCard(page);

        await openTimeline(page, runUrl);

        // axe does not descend into a closed disclosure (A-91), so the panel's sections and links
        // are only scanned after `openAllDisclosures` opens every `<details>` on the page.
        await openAllDisclosures(page);
        const results = await buildAxe(page).analyze();

        expect(results.violations, JSON.stringify(results.violations, null, 2)).toEqual([]);
    });
});
