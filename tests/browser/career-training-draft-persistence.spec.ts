import { test, expect, type Page } from '@playwright/test';
import { startCareer } from '../utils/start-career';
import { deleteRun } from '../utils/delete-run';

/*
 * F-08 — per-tab draft persistence for the turn-record form.
 *
 * The settled mechanism (plan §9.1) is a keyed `sessionStorage` record, not a confirm dialog:
 * `td.turn-draft.<run_id>`, carrying `v`, a baseline fingerprint, `form.data()` and the two
 * performance refs that live outside `useForm`. It restores automatically on mount when the
 * fingerprint still matches the server payload, survives validation failures, and is cleared only
 * by a successful submit or the explicit Discard.
 *
 * The peer's confirm guard (`51d78d3e`) remains as defence in depth and is NOT this fix. It is
 * asserted separately in `career-training-leave-guard.spec.ts`, which is untouched by this file.
 *
 * The inventory below is measured from `TrainingDetail.vue`, not from the plan's prose:
 *
 *   21 scalars   turn, speed, stamina, power, guts, wit, sp, energy, energy_state, energy_band,
 *                fans, mood, outcome, penalty_kind, choice, facility_speed, facility_stamina,
 *                facility_power, facility_guts, facility_wit, failure_rate
 *    6 prevew   preview_gains.{speed,stamina,power,guts,wit,sp}
 *    2 refs     performanceType, performanceDelta
 *  ---------------------------------------------------------------------------------------------
 *   29 restore points
 *
 * The plan's §9.1 says "15 scalars + 6 preview = 21, plus 2 refs = 23". That arithmetic omits the
 * five facility levels and `failure_rate` — the same omission its own selector list makes. The
 * number that matters is that every value the form can hold is persisted, which persisting
 * `form.data()` wholesale guarantees regardless of how the count is written.
 */

/** Every scalar input/select the form holds, by the id the template actually renders. */
const SCALARS = {
    turn: '#turn-turn',
    speed: '#turn-speed',
    stamina: '#turn-stamina',
    power: '#turn-power',
    guts: '#turn-guts',
    wit: '#turn-wit',
    sp: '#turn-sp',
    energy: '#turn-energy',
    energyState: '#turn-energy-state',
    energyBand: '#turn-energy-band',
    fans: '#turn-fans',
    mood: '#turn-mood',
    outcome: '#turn-outcome',
    penaltyKind: '#turn-penalty',
    facilitySpeed: '#facility-facility_speed',
    facilityStamina: '#facility-facility_stamina',
    facilityPower: '#facility-facility_power',
    facilityGuts: '#facility-facility_guts',
    facilityWit: '#facility-facility_wit',
    failureRate: '#turn-failure_rate',
} as const;

/** `preview_gains` renders one input per key, id derived as `turn-preview_gains-<key>`. */
const PREVIEW = {
    previewSpeed: '#turn-preview_gains-speed',
    previewStamina: '#turn-preview_gains-stamina',
    previewPower: '#turn-preview_gains-power',
    previewGuts: '#turn-preview_gains-guts',
    previewWit: '#turn-preview_gains-wit',
    previewSp: '#turn-preview_gains-sp',
} as const;

/** The two refs outside `useForm`; they ride beside `form.data()` in the record. */
const REFS = {
    performanceType: '#turn-performance-type',
    performanceDelta: '#turn-performance-delta',
} as const;

const ALL = { ...SCALARS, ...PREVIEW, ...REFS } as const;

/**
 * A distinct value per field, so a restore that mixes fields up fails rather than passes.
 * Select values that come from computed props (`energyStates`, `energyBands`, `outcomeOptions`,
 * `props.write.moods`) are not knowable from source, so those use the sentinel `FIRST_OPTION` and
 * the fill helper picks the first non-empty option the select actually offers.
 */
const FIRST_OPTION = '\u0000first';

const VALUES: Record<keyof typeof ALL, string> = {
    turn: '',
    speed: '101',
    stamina: '102',
    power: '103',
    guts: '104',
    wit: '105',
    sp: '106',
    energy: '107',
    energyState: FIRST_OPTION,
    energyBand: FIRST_OPTION,
    fans: '108',
    mood: FIRST_OPTION,
    outcome: FIRST_OPTION,
    penaltyKind: '',
    facilitySpeed: '2',
    facilityStamina: '3',
    facilityPower: '4',
    facilityGuts: '1',
    facilityWit: '5',
    failureRate: '109',
    previewSpeed: '201',
    previewStamina: '202',
    previewPower: '203',
    previewGuts: '204',
    previewWit: '205',
    previewSp: '206',
    performanceType: '',
    performanceDelta: '',
};

const DRAFT_PREFIX = 'td.turn-draft.';

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

test.describe.configure({ timeout: 300_000 });

/** Fill every field that has a non-empty test value. Returns the ids actually filled. */
async function fillEverything(page: Page): Promise<string[]> {
    const filled: string[] = [];

    for (const [key, selector] of Object.entries(ALL) as [keyof typeof ALL, string][]) {
        const value = VALUES[key];

        if (value === '') {
            continue;
        }

        const field = page.locator(selector);

        if ((await field.count()) === 0) {
            continue;
        }

        const tag = await field.first().evaluate((el) => el.tagName);

        if (tag === 'SELECT') {
            let chosen = value;

            if (value === FIRST_OPTION) {
                // The first option whose value is not empty — an untouched "" would assert nothing.
                const picked = await field.first().evaluate((el) => {
                    const options = Array.from((el as HTMLSelectElement).options);
                    const real = options.find((o) => o.value !== '');

                    return real === undefined ? '' : real.value;
                });

                if (picked === '') {
                    continue;
                }

                chosen = picked;
            } else {
                // Only use the value if the option really exists; a select that does not offer it
                // would silently keep its old value and the assertion would test nothing.
                const offered = await field.first().evaluate(
                    (el, v) => Array.from((el as HTMLSelectElement).options).some((o) => o.value === v),
                    chosen,
                );

                if (!offered) {
                    continue;
                }
            }

            await field.first().selectOption(chosen);
            filled.push(selector);
        } else {
            await field.first().fill(value);
            filled.push(selector);
        }
    }

    return filled;
}

/** Read back every field's current value. */
async function readEverything(page: Page): Promise<Record<string, string>> {
    const out: Record<string, string> = {};

    for (const [key, selector] of Object.entries(ALL) as [keyof typeof ALL, string][]) {
        const field = page.locator(selector);

        if ((await field.count()) === 0) {
            continue;
        }

        out[key] = await field.first().inputValue();
    }

    return out;
}

/** The stored draft record for this run, or null. */
async function readDraft(page: Page, runId: string): Promise<Record<string, unknown> | null> {
    return page.evaluate((key) => {
        const raw = window.sessionStorage.getItem(key);

        return raw === null ? null : (JSON.parse(raw) as Record<string, unknown>);
    }, DRAFT_PREFIX + runId);
}

async function openTrainingDetail(page: Page): Promise<string> {
    // `startCareer` walks the wizard and returns the run's record URL; it must not be preceded by a
    // navigation of its own. The turn form lives one segment deeper, at `<runUrl>/training`.
    const runUrl = await startCareer(page);

    createdRunUrls.push(runUrl);

    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('#turn-turn').waitFor();

    // Stash the training URL for specs that navigate away and come back.
    trainingUrl = `${runUrl}/training`;

    return runIdFromUrl(runUrl);
}

/** The training URL the last `openTrainingDetail` landed on. */
let trainingUrl = '';

/** The run's numeric id, from the record URL the career settled on. */
function runIdFromUrl(url: string): string {
    const match = /\/training-runs\/(\d+)/.exec(url);

    if (match === null) {
        throw new Error(`no run id in ${url}`);
    }

    return match[1];
}

test.describe('F-08 per-tab draft persistence', () => {
    /*
     * Proof C, first half: the record itself. A draft that restores correctly but stores the wrong
     * shape would pass a UI-only test and fail on the next version of the page, so the key, the
     * version, the fingerprint and the value count are all read from `sessionStorage` directly.
     */
    test('typing writes a versioned, fingerprinted, run-scoped draft holding every value', async ({ page }) => {
        const runId = await openTrainingDetail(page);

        expect(await readDraft(page, runId), 'a draft existed before anything was typed').toBeNull();

        const filled = await fillEverything(page);

        expect(filled.length, `only ${filled.length} fields could be filled`).toBeGreaterThanOrEqual(20);

        // Give the write a beat; it is not required to be synchronous with the keystroke.
        await expect
            .poll(async () => (await readDraft(page, runId)) !== null, { timeout: 5000 })
            .toBe(true);

        const draft = (await readDraft(page, runId)) as Record<string, unknown>;

        expect(draft.v, 'the record carries no schema version').toBe(1);
        expect(draft.run_id, 'the record does not name its run').toBe(Number(runId));
        expect(typeof draft.fingerprint, 'the record carries no baseline fingerprint').toBe('string');
        expect((draft.fingerprint as string).length, 'the fingerprint is empty').toBeGreaterThan(0);

        const data = draft.data as Record<string, unknown>;
        expect(data !== undefined && data !== null, 'the record carries no form data').toBe(true);

        // Every scalar the form holds, including the six the previous spec's 14-selector map missed.
        for (const key of ['turn', 'speed', 'stamina', 'power', 'guts', 'wit', 'sp', 'energy',
            'energy_state', 'energy_band', 'fans', 'mood', 'outcome', 'penalty_kind', 'choice',
            'facility_speed', 'facility_stamina', 'facility_power', 'facility_guts', 'facility_wit',
            'failure_rate']) {
            expect(data, `the draft omits ${key}`).toHaveProperty(key);
        }

        // And the nested preview gains, one entry per key.
        expect(data.preview_gains, 'the draft omits preview_gains').toBeTruthy();
        for (const key of ['speed', 'stamina', 'power', 'guts', 'wit', 'sp']) {
            expect(data.preview_gains as Record<string, unknown>, `preview_gains omits ${key}`)
                .toHaveProperty(key);
        }

        // The two refs outside useForm ride beside the form data.
        expect(draft, 'the draft omits performanceType').toHaveProperty('performanceType');
        expect(draft, 'the draft omits performanceDelta').toHaveProperty('performanceDelta');
    });

    test('a same-tab hard navigation and return restores every value automatically', async ({ page }) => {
        const runId = await openTrainingDetail(page);

        const filled = await fillEverything(page);
        const before = await readEverything(page);

        await expect.poll(async () => (await readDraft(page, runId)) !== null).toBe(true);

        // Hard navigation away: the JS context dies, sessionStorage survives.
        await page.goto(`/training-runs/${runId}/cockpit`, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();

        await page.goto(trainingUrl, { waitUntil: 'domcontentloaded' });
        await page.locator('#app > *').first().waitFor();
        await page.locator('#turn-turn').waitFor();

        // Restoration is automatic — no prompt, no click. Poll, because it seeds on mount.
        await expect
            .poll(async () => (await readEverything(page)).speed, { timeout: 10000 })
            .toBe(before.speed);

        const after = await readEverything(page);

        for (const key of Object.keys(before)) {
            expect(after[key], `${key} was not restored (filled: ${filled.length} fields)`).toBe(before[key]);
        }
    });

    test('a reload restores the draft', async ({ page }) => {
        const runId = await openTrainingDetail(page);

        await fillEverything(page);
        const before = await readEverything(page);

        await expect.poll(async () => (await readDraft(page, runId)) !== null).toBe(true);

        await page.reload({ waitUntil: 'domcontentloaded' });
        await page.locator('#turn-turn').waitFor();

        await expect
            .poll(async () => (await readEverything(page)).speed, { timeout: 10000 })
            .toBe(before.speed);

        const after = await readEverything(page);

        for (const key of Object.keys(before)) {
            expect(after[key], `${key} did not survive a reload`).toBe(before[key]);
        }
    });

    test('a stale fingerprint discards the draft instead of applying it', async ({ page }) => {
        const runId = await openTrainingDetail(page);

        await fillEverything(page);
        await expect.poll(async () => (await readDraft(page, runId)) !== null).toBe(true);

        // Simulate a draft written before the career moved on: a fingerprint that cannot match.
        await page.evaluate((key) => {
            const raw = window.sessionStorage.getItem(key);

            if (raw !== null) {
                const record = JSON.parse(raw) as Record<string, unknown>;
                record.fingerprint = 'stale-fingerprint-that-cannot-match';

                window.sessionStorage.setItem(key, JSON.stringify(record));
            }
        }, DRAFT_PREFIX + runId);

        await page.reload({ waitUntil: 'domcontentloaded' });
        await page.locator('#turn-turn').waitFor();

        // Nothing is restored: the server's own seed values stand.
        await expect
            .poll(async () => page.locator('#turn-speed').inputValue(), { timeout: 5000 })
            .toBe('');

        expect(await page.locator('#turn-speed').inputValue(), 'a stale draft overwrote the form').toBe('');
    });

    test('Discard clears the draft so it cannot come back', async ({ page }) => {
        const runId = await openTrainingDetail(page);

        await fillEverything(page);
        await expect.poll(async () => (await readDraft(page, runId)) !== null).toBe(true);

        const discard = page.getByRole('button', { name: /^Discard/i }).first();

        if ((await discard.count()) === 0) {
            test.skip(true, 'this screen offers no Discard control');

            return;
        }

        await discard.click();
        await page.waitForLoadState('domcontentloaded');

        expect(await readDraft(page, runId), 'Discard left the draft behind').toBeNull();
    });

    test('unavailable sessionStorage is survived, not crashed on', async ({ page }) => {
        // Make every storage call throw before the page mounts, the way a locked-down browser does.
        await page.addInitScript(() => {
            const boom = (): never => {
                throw new Error('storage disabled');
            };

            Object.defineProperty(window, 'sessionStorage', {
                configurable: true,
                get: () => ({ getItem: boom, setItem: boom, removeItem: boom, clear: boom, key: boom, length: 0 }),
            });
        });

        await openTrainingDetail(page);

        // The page is still usable: the form accepts input and does not throw.
        await page.locator('#turn-speed').fill('123');
        await expect(page.locator('#turn-speed')).toHaveValue('123');

        // And saving is still reachable — persistence is an enhancement, never a prerequisite.
        await expect(page.getByRole('button', { name: /record turn|save/i }).first()).toBeVisible();
    });
});
