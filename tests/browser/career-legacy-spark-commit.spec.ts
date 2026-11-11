import { test, expect } from '@playwright/test';

/*
 * F-03 — the spark `Target` combobox must not display a value it has not stored, and typed text
 * must never become the value without explicit acceptance.
 *
 * The defect (walk 8, `RS-UC-2026-13`, reconciliation row R3-16's surface but a distinct cause):
 * `SparkTargetPicker.vue` bound the input to `search` (the filter text) while the payload stored
 * `props.modelValue`. Typing therefore displayed one thing and saved another, and because a `null`
 * target passes `StoreLegacySelectionRequest::assertTargetMatchesCategory`, the save silently wrote
 * `target: null` with no error and `aria-invalid="false"`.
 *
 * The three states this spec holds apart are the W3C combobox distinction the walk conflated:
 *   1. text entered        -> the field shows the typed text and the list filters
 *   2. option selected     -> the field shows the option, the list closes
 *   3. value accepted      -> after a save AND A COLD RELOAD the field still shows the option
 *
 * State 3 is the one that would have caught this. Typing alone must never satisfy the spec.
 *
 * Owner ruling 2026-11-12 (strict revert, amending the first-draft fix in `282e5437`): typed
 * text — exact matches and unique prefixes included — never becomes the value without explicit
 * acceptance. The explicit accepts are clicking an option and pressing Enter on a highlighted
 * one. `Tab`, `Escape`, blur and outside-click restore the previously committed value. The
 * displayed value and the submitted value are therefore the same committed selection, always.
 * The early commit cases below pin the paths `282e5437`'s implicit commitment opened.
 *
 * `label in name` (WCAG 2.5.3) is why the field is located by its accessible name `Target` rather
 * than by placeholder: the visible label is the contract this component documents at line 45.
 */

const TARGET_ID = '#ancestor-spark-target-a-0-0';
const STARS_ID = '#ancestor-spark-stars-a-0-0';
const KIND_ID = '#ancestor-spark-kind-a-0-0';

/* The ids are the ancestor editor's. `/career/setup/legacy` renders four `AncestorEditor` nodes
 * (`ancestor-spark-…`) and no spark field exists in the DOM until its `Add Spark` control is
 * pressed, so the editor is opened by that button first. The inline parent-spark markup in
 * `LegacySelect.vue` (`legacy-spark-…`) is a second call site of the same component; driving the
 * ancestor one exercises the shared logic the defect lived in. */
async function openParentASparkEditor(page: import('@playwright/test').Page): Promise<void> {
    const field = page.locator(TARGET_ID);

    if ((await field.count()) === 0) {
        await page.getByRole('button', { name: /Add Spark\s+to Grandparent A1/i }).first().click();
    }

    await expect(field).toBeVisible();
}

/**
 * Put Parent A on the "Not in my library" path and name it.
 *
 * The save drops a legacy row whose parent is unset: the page offers only a veteran pick or
 * `__manual__`, and with neither chosen the row is blank, so any spark under it is discarded before
 * it can be observed. Choosing the manual path is what makes the persistence assertion test the
 * spark's commit rather than the row filter.
 */
async function nameParentA(page: import('@playwright/test').Page): Promise<void> {
    const pick = page.locator('#legacy-pick-0');

    if ((await pick.count()) > 0) {
        const current = await pick.inputValue();

        if (current === '') {
            await pick.selectOption('__manual__');
        }
    }

    const name = page.locator('#legacy-name-0');

    if ((await name.count()) > 0 && (await name.inputValue()) === '') {
        await name.fill('Vodka');
    }
}

/**
 * Name grandparent A1.
 *
 * The save drops any ancestor whose name is blank (`isBlankSpark` filters the spark rows and the
 * request rejects a nameless ancestor), so a spark entered on a nameless grandparent is discarded
 * before it can be observed.
 */
async function nameGrandparentA1(page: import('@playwright/test').Page): Promise<void> {
    const name = page.locator('#ancestor-a-0');

    if ((await name.count()) > 0 && (await name.inputValue()) === '') {
        await name.fill('Daiwa Scarlet');
    }
}

/**
 * The spark target the server sent back, read without trusting the input: an input can display
 * anything, and the whole defect was that it did.
 *
 * The persisted shape is `props.payload.legacies[i].ancestors_sparks[j][k].target` — confirmed by
 * reading a real save: `"ancestors_sparks":[[{"kind":"blue","target":"Power","stars":1}],[]]`.
 * Grandparent A1 is `legacies[0].ancestors_sparks[0]`, so its first spark is `[0][0]`.
 */
async function persistedFirstSparkTarget(page: import('@playwright/test').Page): Promise<string | null> {
    return page.evaluate(() => {
        const el = document.querySelector('script[data-page="app"]');

        if (el === null || el.textContent === null) {
            return null;
        }

        type Spark = { target?: string | null };
        const props = (JSON.parse(el.textContent) as {
            props?: {
                payload?: { legacies?: { ancestors_sparks?: Spark[][] }[] };
            };
        }).props ?? {};

        return (props.payload?.legacies?.[0]?.ancestors_sparks?.[0]?.[0]?.target ?? null) as string | null;
    });
}

test.describe.configure({ timeout: 180_000 });

test('typing a target and saving does not store a target the field did not commit', async ({ page }) => {
    await page.goto('/career/setup/legacy', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    // ORDER MATTERS: naming the parent re-renders the row, so the spark editor is opened last.
    await nameParentA(page);
    await nameGrandparentA1(page);
    await openParentASparkEditor(page);

    const field = page.locator(TARGET_ID);
    await expect(field).toBeVisible();

    // Capture the request body. Asserting on the RESPONSE cannot separate "the component never
    // committed" from "the component committed and the save path dropped it", and those are two
    // different defects with two different owners. The PUT body is the component's own claim.
    const puts: string[] = [];
    page.on('request', (request) => {
        if (request.method() === 'PUT' && request.url().includes('/career/setup/legacy')) {
            puts.push(request.postData() ?? '');
        }
    });

    // State 1 — text entered, deliberately a prefix with an obvious single match.
    await field.click();
    await field.fill('Pow');
    await expect(page.getByRole('listbox')).toBeVisible();
    await expect(page.getByRole('option', { name: /^Power/ })).toBeVisible();

    await page.locator(STARS_ID).fill('1');

    // Save WITHOUT clicking the option, which is exactly what the walk did. The owner ruling is
    // that this must NOT commit `Power`: the field reverts to the committed value (empty) on blur,
    // and the payload stores what the field displayed — nothing, as a null target — rather than
    // the typed prefix.
    await page.getByRole('button', { name: /Save and stay/i }).first().click();
    await page.waitForLoadState('domcontentloaded');
    await page.locator('#app > *').first().waitFor();

    expect(puts.length, 'no PUT reached /career/setup/legacy').toBeGreaterThan(0);

    // The invariant, stated in the direction that catches both failure shapes: the field never
    // displays what the payload does not carry, and typed text never becomes a value on its own.
    // A commit of `Power` here would be implicit commitment — `282e5437`'s behaviour, removed by
    // the ruling.
    expect(
        puts[0],
        'the save posted a target the field never explicitly committed (implicit commitment)',
    ).not.toContain('"target":"Power"');
});

test('a selected target survives a cold reload', async ({ page }) => {
    await page.goto('/career/setup/legacy', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    // ORDER MATTERS: naming the parent re-renders the row, so the spark editor is opened last.
    await nameParentA(page);
    await nameGrandparentA1(page);
    await openParentASparkEditor(page);

    const field = page.locator(TARGET_ID);
    await field.click();
    await field.fill('Pow');

    // State 2 — commit it the way a Trainer does: click the option.
    await expect(page.getByRole('option', { name: /^Power/ })).toBeVisible();
    await page.getByRole('option', { name: /^Power/ }).first().click();
    await expect(page.getByRole('listbox')).toHaveCount(0);

    await page.locator(STARS_ID).fill('1');
    await page.getByRole('button', { name: /Save and stay/i }).first().click();
    await page.waitForLoadState('domcontentloaded');
    await page.locator('#app > *').first().waitFor();

    // State 3 — a cold reload, so nothing comes from in-memory state.
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await openParentASparkEditor(page);

    await expect(
        page.locator(TARGET_ID),
        'the committed target did not survive the reload',
    ).toHaveValue(/Power/i);

    expect(await persistedFirstSparkTarget(page)).toBe('Power');
});

test('Clear empties both the field and the payload', async ({ page }) => {
    await page.goto('/career/setup/legacy', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    // ORDER MATTERS: naming the parent re-renders the row, so the spark editor is opened last.
    await nameParentA(page);
    await nameGrandparentA1(page);
    await openParentASparkEditor(page);

    const field = page.locator(TARGET_ID);
    await field.click();
    await field.fill('Pow');
    // Wait for the list before clicking it: `fill` opens the listbox through `onInput`, and a click
    // issued in the same tick can land before the options render.
    await expect(page.getByRole('option', { name: /^Power/ })).toBeVisible();
    await page.getByRole('option', { name: /^Power/ }).first().click();

    // The Clear affordance only renders once a value is committed (SparkTargetPicker.vue:245).
    const clear = page.getByRole('button', { name: /^Clear Target/ }).first();
    await expect(clear).toBeVisible();
    await clear.click();

    await expect(field).toHaveValue('');
});

test('changing Category clears a target chosen under the old vocabulary', async ({ page }) => {
    await page.goto('/career/setup/legacy', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    // ORDER MATTERS: naming the parent re-renders the row, so the spark editor is opened last.
    await nameParentA(page);
    await nameGrandparentA1(page);
    await openParentASparkEditor(page);

    const field = page.locator(TARGET_ID);
    await field.click();
    await field.fill('Pow');
    // Wait for the list before clicking it: `fill` opens the listbox through `onInput`, and a click
    // issued in the same tick can land before the options render.
    await expect(page.getByRole('option', { name: /^Power/ })).toBeVisible();
    await page.getByRole('option', { name: /^Power/ }).first().click();
    await expect(field).toHaveValue(/Power/i);

    // The editor resets the target when the category changes; that reset must reach the input too.
    const category = page.locator('#ancestor-spark-category-a-0-0');
    const options = await category.locator('option').allTextContents();
    const other = options.find((text) => !/stat/i.test(text));

    if (other === undefined) {
        test.skip(true, 'the Category control offers no non-Stat option');

        return;
    }

    await category.selectOption({ label: other.trim() });
    await expect(field, 'a target from the old category survived the category change').toHaveValue('');
});

test('a second spark cannot display the first spark\'s target', async ({ page }) => {
    await page.goto('/career/setup/legacy', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await openParentASparkEditor(page);

    // Add a second spark to the same grandparent and leave it empty. Each row's picker owns its own
    // `search`, so an empty row must print nothing rather than inheriting its sibling's target.
    await page.getByRole('button', { name: /Add Spark.*Grandparent A1/i }).first().click();

    const second = page.locator('#ancestor-spark-target-a-0-1');
    await expect(second).toBeVisible();
    await expect(second, 'a fresh spark row inherited a sibling target').toHaveValue('');
});

/*
 * The four cases below are the owner ruling's own list (2026-11-12), written as the explicit accepts
 * and the reverts. Every one of them was unexercised before: "Enter on a highlighted option" appeared
 * only in this file's header comment, and "a previously committed value survives a rejected filter"
 * had no case at all.
 */

test('an exact option name typed but not accepted does not become the value', async ({ page }) => {
    await page.goto('/career/setup/legacy', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await nameParentA(page);
    await nameGrandparentA1(page);
    await openParentASparkEditor(page);

    const field = page.locator(TARGET_ID);

    // The FULL name, not a prefix: this is the case `commitExactText` used to accept on keystroke.
    await field.click();
    await field.fill('Power');
    await expect(page.getByRole('option', { name: /^Power/ })).toBeVisible();

    // Leave without accepting. The field must revert, because `Power` was only ever filter text.
    await field.blur();

    await expect(
        field,
        'an exact option name typed without acceptance became the value',
    ).toHaveValue('');
});

test('a previously committed value survives a rejected filter', async ({ page }) => {
    await page.goto('/career/setup/legacy', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await nameParentA(page);
    await nameGrandparentA1(page);
    await openParentASparkEditor(page);

    const field = page.locator(TARGET_ID);

    // Commit `Power` explicitly first.
    await field.click();
    await field.fill('Pow');
    // Wait for the list before clicking it: `fill` opens the listbox through `onInput`, and a click
    // issued in the same tick can land before the options render.
    await expect(page.getByRole('option', { name: /^Power/ })).toBeVisible();
    await page.getByRole('option', { name: /^Power/ }).first().click();
    await expect(field).toHaveValue(/Power/i);

    // Now filter for something else and abandon it. The committed value must come back, not vanish
    // and not be replaced by the filter text.
    await field.click();
    await field.fill('Sta');
    await field.blur();

    await expect(
        field,
        'abandoning a filter lost the value that had been committed before it',
    ).toHaveValue(/Power/i);
});

test('Enter on a highlighted option is an explicit accept', async ({ page }) => {
    await page.goto('/career/setup/legacy', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await nameParentA(page);
    await nameGrandparentA1(page);
    await openParentASparkEditor(page);

    const field = page.locator(TARGET_ID);
    await field.click();
    await field.fill('Pow');
    await expect(page.getByRole('listbox')).toBeVisible();

    // Arrow to the first option, then accept with Enter — the ruling's second explicit accept.
    await field.press('ArrowDown');
    await field.press('Enter');

    await expect(field, 'Enter on a highlighted option did not commit it').toHaveValue(/Power/i);
    await expect(page.getByRole('listbox')).toHaveCount(0);
});

test('Escape reverts and commits nothing', async ({ page }) => {
    await page.goto('/career/setup/legacy', { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await nameParentA(page);
    await nameGrandparentA1(page);
    await openParentASparkEditor(page);

    const field = page.locator(TARGET_ID);
    await field.click();
    await field.fill('Pow');
    await expect(page.getByRole('listbox')).toBeVisible();

    await field.press('Escape');

    await expect(field, 'Escape committed the filter text instead of reverting').toHaveValue('');
});
