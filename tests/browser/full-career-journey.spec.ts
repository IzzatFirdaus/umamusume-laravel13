import { test, expect } from '@playwright/test';
import { DatabaseSync } from 'node:sqlite';
import path from 'node:path';
import { deleteRun } from '../utils/delete-run';
import { openIdentityDisclosure } from '../utils/identity-disclosure';
import { walkWizard } from '../utils/start-career';

/*
 * The complete new-career journey (Slice 6.1): one deterministic walk from the wizard's first step
 * to a filed Veteran, asserting at the two ends that the run exists only from Preflight on. The
 * steps are the app's own surfaces in their own order - Scenario, Trainee, Target, Legacy, Deck,
 * Preflight, then the Cockpit's identity and team editors, the guided turn on Training Detail, the
 * run-scoped deck, both planners, the status finish and the Save Veteran door.
 *
 * **Re-baselined 2026-10-30 against the shipped screens (KI-139).** The walk was written against a
 * cockpit shape four parity commits replaced, and five stale locators were patched one at a time
 * (:114, :128, :133, :150, :176). Every step after the cockpit editors is now read against what
 * ships: the run deck is `Support/Builder.vue`, which saves through its own savebar and returns to
 * the Cockpit with `Deck saved.`; the Result screen's banking door is `Bank as veteran`; the Save
 * Veteran submit carries the trainee's name. The deck step also makes a write the old steps never
 * made, so the slots it asserts are the ones the run stored. This is a re-baseline, not a
 * weakening: every claim the old file made that still applies is present.
 *
 * Run counting is one mechanism, fixed here: the scratch database is read directly with `node:sqlite`
 * (Node 22+'s own client; `better-sqlite3` is not a dependency of this package and none is added for
 * a test). The count is asserted as a delta against the file's state at the walk's start, which is
 * the same assertion as "zero runs before Preflight and exactly one after" on the clean scratch
 * database the session-private pair seeds, without making the spec wrong if a crashed spec left a
 * row behind. WAL lets this reader run beside the server the suite started.
 *
 * Every reload assertion reads a value back from storage, so the walk proves persistence and not
 * merely that the write returned 200.
 */

const WRITE = { timeout: 60_000 } as const;

const createdRunUrls: string[] = [];

test.afterEach(async ({ page }) => {
    for (const url of createdRunUrls.splice(0)) {
        await deleteRun(page, url);
    }
});

function scratchRunCount(): number {
    const file = process.env.PLAYWRIGHT_SCRATCH_DB;

    if (file === undefined || file === '') {
        throw new Error(
            'full-career-journey reads the scratch database directly: run it with PLAYWRIGHT_PORT and '
            + 'PLAYWRIGHT_SCRATCH_DB set to the session-private pair (KI-73, KI-80).',
        );
    }

    const db = new DatabaseSync(path.resolve(file));

    try {
        const row = db.prepare('SELECT COUNT(*) AS n FROM training_runs').get();

        return Number((row as { n: number }).n);
    } finally {
        db.close();
    }
}

/**
 * The wizard's first five steps, ending at Preflight, without starting the career.
 *
 * The shared walk (slice 4.1), plus the two assertions only this journey makes: the ancestry step is
 * visited and named, and the trainee's own name comes back so the later screens can be checked against
 * it. `onLegacy` is the additive hook the helper grew for exactly this — the alternative was a second
 * private copy of the walk, which is what four specs carried and what had gone stale.
 */
async function walkToPreflight(page: import('@playwright/test').Page): Promise<string> {
    return walkWizard(page, 'unity_cup', {
        onLegacy: async (step) => {
            await expect(step.getByRole('heading', { name: 'Inherit from two parents', level: 1 })).toBeVisible();
            await expect(step.getByText('Step 4 of 6').first()).toBeVisible();
        },
    });
}

test('walks the whole career from the wizard to a filed Veteran', async ({ page }) => {
    // The walk is the whole product surface in one pass; the 180s default is a per-screen budget,
    // not a whole-journey one.
    test.setTimeout(300_000);

    const baseCount = scratchRunCount();

    const traineeName = await walkToPreflight(page);

    // The wizard echo joins the name with the card title (`TraineeSelect.vue:575-579`), while every
    // screen after Preflight prints `umamusume.name` alone, which is what the run and the Veteran
    // store (`SaveVeteranController.php:68`). Splitting on the title bracket is what keeps the later
    // assertions about the actual record rather than about the picker card.
    const traineeRecordName = traineeName.replace(/\s*\[.*\]$/, '');

    // The wizard's own contract, held at its end: no run row exists before Preflight.
    expect(scratchRunCount()).toBe(baseCount);

    await page.goto('/career/setup/preflight', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.getByRole('button', { name: 'Create the run' }).click();
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });

    // And Preflight is the run's birth: exactly one row more than the walk started with.
    expect(scratchRunCount()).toBe(baseCount + 1);

    const runUrl = page.url().replace(/\/cockpit$/, '');
    createdRunUrls.push(runUrl);

    // --- Identity and Unity Cup state, through the Cockpit's own editors (Slices 1.5 and 1.6) ---
    await openIdentityDisclosure(page);
    await page.selectOption('#trainee_rarity', '3');
    await page.locator('#potential_level').fill('2');
    // One growth figure is a legal sparse row: a stat with no growth is simply absent.
    await page.locator('input[name="growth_rate[Speed]"]').fill('10');
    await page.getByRole('button', { name: 'Save identity' }).click();
    await expect(page.getByText('Run updated.')).toBeVisible(WRITE);
    // The old `CockpitIdentityCard` header this used to assert is no longer rendered by the shipped
    // cockpit, so the walk reads the save back where it now lives: the identity editor after a reload.
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await openIdentityDisclosure(page);
    await expect(page.locator('#trainee_rarity')).toHaveValue('3');
    await expect(page.locator('#potential_level')).toHaveValue('2');

    // Neither the label text nor the team-name field is unique here: `run-tools` nests the
    // team-state disclosure, so a descendant match on the field finds both and the walk fails on
    // strict mode before the assertion. Naming the panel by its own attribute is what makes the
    // selector single.
    const teamEditor = page.locator('details[data-region="team-state"]');
    // The panel is `open` while the career is active — Cockpit.vue sets `:open="isActive"`, because the
    // panel the Active screen has always shown stays shown and only closes once the career has ended.
    // The journey used to click the summary first, which TOGGLED IT CLOSED and left the input hidden, so
    // the fill sat at its 300s budget waiting for an element that could never become visible. Asserting
    // the contract instead of toggling it is what makes the next line possible.
    await expect(teamEditor).toHaveAttribute('open', '');
    await teamEditor.locator('input[name="team_name"]').fill('Blue Bloom');
    await teamEditor.locator('input[name="spirit_bursts_count"]').fill('6');
    await teamEditor.getByRole('button', { name: 'Save team state' }).click();
    await expect(page.getByText('Run updated.')).toBeVisible(WRITE);
    // The saved name now appears twice on the cockpit: the Scenario card's own table prints it as
    // the `Team` row's cell (Cockpit.vue:1024-1040, outside any disclosure and always visible), and
    // UnityCupPanel repeats it inside the closed `scenario-detail` disclosure as a hidden `<dd>`. A
    // bare getByText resolves to two elements, so the walk names the visible cell it means.
    await expect(page.getByRole('cell', { name: 'Blue Bloom' })).toBeVisible();

    // Reload: the identity and the team state are read back from storage, not from the form.
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    // The old CockpitIdentityCard header this used to assert is no longer rendered by the shipped
    // cockpit (removed at 530d62b9, zero imports in resources/js), so the reload reads the save back
    // where it now lives - the identity editor - exactly as the peer's re-baseline does at :105-111.
    await openIdentityDisclosure(page);
    await expect(page.locator('#trainee_rarity')).toHaveValue('3');
    await expect(page.locator('#potential_level')).toHaveValue('2');
    // The Scenario card's Team row survives the reload in the same always-visible cell, so the
    // scoped cell locator keeps this assertion single-element too.
    await expect(page.getByRole('cell', { name: 'Blue Bloom' })).toBeVisible();

    // --- One guided turn, recorded on Training Detail (Slice 1.4's surface) ---
    await page.goto(`${runUrl}/training`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    // The Train button this used to click lived inside a training card that the shipped screen no
    // longer draws: the discipline is picked on the screen's own radio list, and the one filled action
    // is the savebar's `Record turn N` (TrainingCard.vue:383-388, KI-138). The write boundary also
    // requires the five client totals, which stay in one collapsed disclosure.
    await page.getByRole('radio', { name: 'Speed training' }).check();
    await page.getByText('Client totals this turn (required to record)').click();
    await page.locator('input[name="speed"]').fill('650');
    await page.locator('input[name="stamina"]').fill('600');
    await page.locator('input[name="power"]').fill('600');
    await page.locator('input[name="guts"]').fill('600');
    await page.locator('input[name="wit"]').fill('600');
    await page.locator('input[name="energy"]').fill('62');
    await page.locator('select[name="outcome"]').selectOption('Success');
    await page.getByRole('button', { name: /^Record turn \d+$/ }).click();
    await page.waitForURL(/\/cockpit$/, { waitUntil: 'domcontentloaded' });
    await expect(page.getByText('Turn 1 logged.')).toBeVisible();

    // Reload: the recorded turn is in the correction selector, which is the Cockpit's own read-back.
    // The selector renders one option per logged turn and no placeholder (the two-turn case in
    // `career-cockpit-forms.spec.ts` pins that shape), so one turn is one option.
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.locator('#correction-turn-select').locator('option')).toHaveCount(1);
    await expect(page.locator('#correction-turn-select').locator('option')).toHaveText('Turn 1');

    // --- The run-scoped deck: six slots, each with its own controls (R2-06's semantics) ---
    //
    // The door is the "Where to go" entry, not the Run identity link: that one sits inside the closed
    // `data-region="run-tools"` disclosure (Cockpit.vue:1144, :1276-1283), and the walk reads and
    // writes the deck rather than the identity, so it takes the path that is always rendered (KI-139).
    await page.getByRole('link', { name: 'Run deck', exact: true }).click();
    await page.waitForURL(/\/deck$/, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    for (let position = 1; position <= 6; position++) {
        await expect(page.locator(`#deck-slot-${position}`)).toBeVisible();
    }
    // Each slot offers exactly one door whether it holds a card or not: `Choose a card for <slot>`
    // when empty, `Replace the card in <slot>` when filled (SupportSlot.vue:241). Which one a slot
    // draws is stored state, so this counts the doors and leaves the state to the read-back below.
    // The Friends slot is labelled `Slot 6 · Friends`, so the match is on the `Slot ` prefix, not `Slot \d+`.
    const slotList = page.getByRole('list').filter({ has: page.locator('#deck-slot-6') });
    await expect(slotList.getByRole('button', { name: /^(Choose a card for|Replace the card in) Slot / })).toHaveCount(6);

    // The write the old steps never made: this screen's savebar posts `runs.deck.sync`, so the walk
    // equips a card and flags its slot before leaving, and the deck it later reads back is the one the
    // run stored rather than one this page drew from the query string.
    // The picker is its own collapsed disclosure and a slot door does not open it (Builder.vue:351;
    // `startReplace` only re-queries with `slot`), so the walk opens it the way `support-deck.spec.ts`
    // does. The door is `Choose a card for <slot>` on an empty slot and `Replace the card in <slot>`
    // on a filled one, and the run arrives with two cards already stored, so both spellings are legal.
    await page.locator('summary').filter({ hasText: 'Choose a card' }).click();
    await page.getByRole('button', { name: /^(Choose a card for|Replace the card in) Slot 1$/ }).click();
    await page.getByRole('button', { name: /^Equip to Slot 1/ }).first().click();
    await expect(page.locator('#deck-slot-1')).not.toContainText('Not equipped');
    await page.getByRole('group', { name: 'Ownership of Slot 1' }).getByRole('button', { name: 'Owned', exact: true }).click();

    await page.getByRole('button', { name: 'Save deck' }).click();
    await page.waitForURL(/\/cockpit$/, WRITE);
    // The savebar answers with `Deck saved.`, but that flash is a client-side banner that clears
    // itself after five seconds (Flash.vue:24), so asserting it here would race the walk. The
    // redirect above plus the read-back below is the durable proof that the write landed.

    // Read back from storage rather than from the write's own response.
    await page.goto(`${runUrl}/deck`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.locator('#deck-slot-1')).not.toContainText('Not equipped');
    await expect(page.getByRole('group', { name: 'Ownership of Slot 1' }).getByRole('button', { name: 'Owned', exact: true }))
        .toHaveAttribute('aria-pressed', 'true');

    // --- The two planners render for this run ---
    await page.goto(`${runUrl}/races/planner`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByRole('heading', { name: 'Plan the calendar' })).toBeVisible();

    await page.goto(`${runUrl}/skills`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.locator('summary').filter({ hasText: 'Build target, recorded skills and the add form' }).click();
    await expect(page.getByRole('heading', { name: 'Skill Point coverage' })).toBeVisible();

    // --- Finish through the status flow, then file the Veteran from the Result screen ---
    // The status form is the Cockpit's; the walk is on the Skills Planner after the planners' visit.
    await page.goto(`${runUrl}/cockpit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await openIdentityDisclosure(page);
    const statusForm = page.locator('form').filter({ has: page.getByRole('button', { name: 'Change status' }) });
    await statusForm.locator('select[name="status"]').selectOption('Completed');
    await statusForm.getByRole('button', { name: 'Change status' }).click();
    await expect(page.getByText('Run updated.')).toBeVisible(WRITE);

    await page.goto(`${runUrl}/result`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    // The Result screen offers its banking door only once the career is finished, which is why the
    // status write above belongs to the walk: `Bank as a veteran` is an anchor to
    // `runs.veteran` rather than a control inside a region.
    await page.getByRole('link', { name: 'Bank as a veteran' }).click();
    await page.waitForURL(/\/veteran$/, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();

    await page.locator('#veteran-notes').fill('The whole journey, end to end.');
    // The submit's accessible name is its own text, `Save Veteran`; the trainee's name is the page
    // title, not part of the button (SaveVeteran.vue:412).
    await page.getByRole('button', { name: 'Save Veteran', exact: true }).click();
    await page.waitForURL(/\/veterans\/\d+$/, WRITE);
    await expect(page.getByText(/Veteran saved:/)).toBeVisible();
    await expect(page.getByRole('heading', { name: traineeRecordName })).toBeVisible();

    // Reload: the filed record is read back from storage.
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByRole('heading', { name: traineeRecordName })).toBeVisible();

    // And the library lists it: `/veterans` is the library the nav names, and the walk's filed
    // record is one of its rows. The row is addressed as the link whose accessible name carries
    // the trainee, never as a bare text match - the Lab's own picker carries the same name inside
    // a hidden option, and `.first()` over `getByText` would read that one and fail.
    await page.goto('/veterans', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await expect(page.getByRole('link', { name: traineeRecordName, exact: true }).first()).toBeVisible();
});
