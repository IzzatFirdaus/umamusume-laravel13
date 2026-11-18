# Plan 3 of 4 - Defects with a named code site (phase 3)

**Pair:** `docs/roadmap/remaining-work-roadmap-2026-11-17.md` phase 3.
**Depends on:** plan 1 phase 0 (the tree must build before a slice's test can run).
**Measured at:** `HEAD be8893a2`, 2026-11-17.

Each slice follows the same shape: probe, failing assertion first, smallest change, gate, register note.

---

# Slice 3.1 - KI-166: the races page overflows 59 px at 320 px

## Objective

Name the offending element, stop the document panning at the WCAG 1.4.10 reflow width, and leave a case in
the page's own spec that fails if it returns.

## What is already measured (do not re-derive; do re-check if the page changed)

Round 1 probe, 320x900, a real 11-turn run:

```
SUMMARY {"docOverflow":59,"cw":320,"sw":379,"tableDirectChild":true,
         "cardOverflowX":"auto","cardHasRuleMatched":true,"offenderCount":20,"culprits":[]}
```

Read it precisely:

- `tableDirectChild: true` - `resources/js/pages/Career/RaceDecision.vue:413`'s `table.t` **is** a direct child of the
  `section.card` at `:402` (the detail card) and of `:306` (the available card renders a list, not a table).
- `cardHasRuleMatched: true`, `cardOverflowX: "auto"` - `resources/css/app.css:1339`'s
  `.card:has(> table.t)` **did** apply. The card is a real scroll container.
- `culprits: []` - no unrescued box widens the document. Every element with `right > 320` sits under a
  scrolling ancestor.
- Yet `document.documentElement.scrollWidth` is 379.

So this is **not** the cockpit's failure mode. The cockpit's 88 px was a child escaping a non-scrolling card;
here the scroll container exists and something still contributes extent. That is a different mechanism.

## Probe round 2 - the instrument the row itself asks for

Create `tests/browser/_probe-race-overflow-up.spec.ts`, run it, then delete it. Do not commit a probe.

```ts
import { test } from '@playwright/test';
import { recordTurns } from '../utils/record-turns';
import { startCareer } from '../utils/start-career';

test('probe: which ancestor carries scrollWidth > clientWidth', async ({ page }) => {
    const runId = await startCareer(page);
    await recordTurns(page, runId, 11);

    await page.goto(`/training-runs/${runId}/races`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.setViewportSize({ width: 320, height: 900 });

    const report = await page.evaluate(() => {
        const rows: unknown[] = [];
        const walk = (el: Element): void => {
            const sw = (el as HTMLElement).scrollWidth;
            const cw = (el as HTMLElement).clientWidth;
            if (sw > cw + 1) {
                const cs = getComputedStyle(el as HTMLElement);
                rows.push({
                    tag: el.tagName,
                    id: el.id,
                    cls: (el.getAttribute('class') ?? '').slice(0, 90),
                    region: el.closest('[data-region]')?.getAttribute('data-region') ?? null,
                    sw, cw, delta: sw - cw,
                    overflowX: cs.overflowX,
                    minWidth: cs.minWidth,
                    childRightMax: Math.max(...Array.from(el.children).map((c) => Math.round(c.getBoundingClientRect().right))),
                });
            }
            Array.from(el.children).forEach(walk);
        };
        walk(document.documentElement);
        return { docSW: document.documentElement.scrollWidth, docCW: document.documentElement.clientWidth, rows };
    });

    console.log(JSON.stringify(report, null, 1));
});
```

Run session-private and quote the JSON:

```bash
PLAYWRIGHT_PORT=8322 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<s>/probe8322.sqlite \
  npx playwright test tests/browser/_probe-race-overflow-up.spec.ts --workers=1 --reporter=line
```

Then delete the file (`rm tests/browser/_probe-race-overflow-up.spec.ts`) and record its JSON in the KI-166 body.

## The fix

Read the probe's deepest row whose parent shows a **smaller** delta. Three shapes cover every case this
codebase has produced, and the choice is mechanical:

| Probe says | Fix |
| --- | --- |
| A `min-w-[Nrem]` or fixed-width child inside a flex row | Replace the arbitrary width with `min-w-0` plus a token width, or let the row wrap |
| A `.card` whose `overflow-x` is `visible` while it holds a wide child | Add the `overflow-x-auto` wrapper the cockpit uses (`resources/js/components/career/NextDecision.vue:346`) |
| A `table.t` inside a card that is **not** a direct child | Wrap the table: `<div class="overflow-x-auto">` |

Do not change `.card:has(> table.t)` itself. That rule is load-bearing for every other matrix in the app and
KI-160's fix depends on it.

## The case this closes

`tests/browser/career-race-decision.spec.ts` has **zero** occurrences of `320` today. Add one case that
would have failed before the fix:

```ts
test('lays out at 320 without a horizontal scroll', async ({ page }) => {
    const runId = await startCareer(page);
    await recordTurns(page, runId, 11);
    await page.goto(`/training-runs/${runId}/races`, { waitUntil: 'domcontentloaded' });
    await page.locator('#app > *').first().waitFor();
    await page.setViewportSize({ width: 320, height: 900 });

    expect(await page.evaluate(
        () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
    )).toBe(0);
});
```

Model it on the two cases that already do this: `tests/browser/career-race-planner.spec.ts:119` and
`tests/browser/career-race-strip.spec.ts:216`.

## Files

- the component the probe names (one file, expected `resources/js/pages/Career/RaceDecision.vue`)
- `tests/browser/career-race-decision.spec.ts` (+1 case)
- `KNOWN-ISSUES.md` (dated note on KI-166: the mechanism, the fix, the case)

## Acceptance

- New case green at 320 **and** `career-race-planner.spec.ts` and `career-race-strip.spec.ts` still green.
- `career-race-strip.spec.ts` at 640 unchanged.
- KI-166's note quotes the probe JSON, the changed lines, and the case's pass line.

## If the probe finds nothing

Then the overflow is not in the document's own boxes: check `document.body`'s computed `min-width`, a
`position: fixed` element wider than the viewport, or a scroll container with `scrollbar-gutter`. Record the
negative and leave the row OPEN with the new instrument named; a probe that cannot reproduce is evidence.

---

# Slice 3.2 - KI-165: the fan ladder holds one band of nine

## Objective

`config('uma.fan_ladder')` gains the seven numeric bands the corpus states. Every band edge becomes
assertable. The two non-numeric tiers stay absent, on purpose, with a comment naming why.

## The sourced table (four independent documents agree)

| Class | Threshold (fans) | Present today |
| --- | --- | --- |
| Debut / Maiden | entry class, no count | no |
| Beginner | "1st place", no count | no |
| Bronze | 5,000 | no |
| Silver | 20,000 | no |
| Gold | 50,000 | no |
| Platinum | 100,000 | no |
| Star | 160,000 | **band floor is 0 today, not 160,000** |
| Top Star | 240,000 | name only, as this band's `next_class` |
| Legend | 320,000 | no |

Sources, each with its line:

- `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md:474` - the client's nine-step pyramid, all nine named.
- `docs/research-scratch/DESIGN-CORPUS.md:1388-1400` - the same nine as a table with thresholds, and `:2578`
  (rule **D-272**) states they are one scale that must not be merged with race gates.
- `docs/audits/ux-walkthroughs/rice-shower-unity-cup-ux-walk-9.md:194` - the readings restated with the gap
  arithmetic.
- `docs/audits/reconciliation-rounds/rice-shower-unity-cup-round8-reconciliation.md` P-60 - the path is closed.

## The change

Rewrite the `'fan_ladder'` array at `config/uma.php:578-580`. Bands are **half-open intervals** `[from, to)`,
which is what `FanLadder::bandFor()` already tests (`>= from && < to`), so no service change is needed.

**The one mapping to write, and why it is this one.** The source's own sentence is the arbiter: a run at
209,245 fans was printed by the client as `Star`, with `Top Star` at 240,000. So the band labelled `Star`
must contain 209,245. That fixes the boundaries to the *class label the client shows*, whose range runs from
the previous class's threshold up to the next class's threshold:

| Class | `from` | `to` | `next_class` | Source tier whose name it carries |
| --- | --- | --- | --- | --- |
| Bronze | 0 | 5,000 | Silver | Bronze |
| Silver | 5,000 | 20,000 | Gold | Silver |
| Gold | 20,000 | 50,000 | Platinum | Gold |
| Platinum | 50,000 | 100,000 | Star | Platinum |
| Star | 100,000 | 240,000 | Top Star | Star (covers the 160,000 tier and the audited 209,245) |
| Top Star | 240,000 | 320,000 | Legend | Top Star |
| Legend | 320,000 | `PHP_INT_MAX` | Legend | Legend |

Seven numeric classes; the source's two remaining tiers (`Beginner` = "1st place", `Debut / Maiden` = entry
class) have no fan count and stay unrepresentable.

```php
/*
 * The fan class ladder, from the client's own nine-step pyramid
 * (`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md:474`; `docs/research-scratch/DESIGN-CORPUS.md:1388-1400`,
 * rule D-272 - one scale, never merged with race-entry gates). Half-open `[from, to)`, matching
 * `FanLadder::bandFor()`.
 *
 * The boundaries are the ranges of the LABEL the client prints, not the tier's own published threshold:
 * the source shows a run at 209,245 fans printed as `Star` with `Top Star` at 240,000, so `Star` must span
 * 100,000..239,999. That keeps the one audited reading unchanged (`Star`, 30,755 to `Top Star`).
 *
 * `Legend` is the ceiling, so its `to` is PHP_INT_MAX rather than an invented figure; `bandFor()` tests
 * `< to`, and a finite pretend ceiling would answer null for a legitimate reading above it.
 *
 * ponytail: `Beginner` (keyed on "1st place") and `Debut / Maiden` (keyed on an entry class) are a known
 * ceiling - neither has a fan count to key on, so a run below Bronze answers null and the renderer prints
 * the bare count. Add them when a source states a fan count, or when the reading needs a class before the
 * first race, which would need a key other than a fan count (a schema question, not a config edit).
 *
 * @var list<array{class: string, from: int, to: int, next_class: string}>
 */
'fan_ladder' => [
    ['class' => 'Bronze',   'from' => 0,       'to' => 5000,       'next_class' => 'Silver'],
    ['class' => 'Silver',   'from' => 5000,    'to' => 20000,      'next_class' => 'Gold'],
    ['class' => 'Gold',     'from' => 20000,   'to' => 50000,      'next_class' => 'Platinum'],
    ['class' => 'Platinum', 'from' => 50000,   'to' => 100000,     'next_class' => 'Star'],
    ['class' => 'Star',     'from' => 100000,  'to' => 240000,     'next_class' => 'Top Star'],
    ['class' => 'Top Star', 'from' => 240000,  'to' => 320000,     'next_class' => 'Legend'],
    ['class' => 'Legend',   'from' => 320000,  'to' => PHP_INT_MAX, 'next_class' => 'Legend'],
],
```

**Before writing the array, re-read `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md:474` yourself.** It is the
only document that states a *class for a known fan count*, and it is what decides whether `Star` starts at
100,000 or 160,000. The table above is derived from it; if the sentence has changed, the table changes with it.

## SUPERSEDED — DO NOT EXECUTE ANY OF THE ABOVE

**Landed and committed by a peer at `2546689e`'s line.** `config/uma.php` on disk and at `HEAD` both carry **7
bands**, and `tests/Feature/FanLadderTest.php` is committed with the new boundary table. Measured
`php artisan tinker --execute="echo count(config('uma.fan_ladder'));"` -> `7`.

The shipped ladder keys each band's `from` to **that tier's own threshold**, which is what the client prints — a
tier name sits beside the count that *reaches* it:

```php
['class' => 'Bronze',   'from' => 5000,       'to' => 20000,  'next_class' => 'Silver'],
['class' => 'Silver',   'from' => 20000,      'to' => 50000,  'next_class' => 'Gold'],
['class' => 'Gold',     'from' => 50000,      'to' => 100000, 'next_class' => 'Platinum'],
['class' => 'Platinum', 'from' => 100000,     'to' => 160000, 'next_class' => 'Star'],
['class' => 'Star',     'from' => 160000,     'to' => 240000, 'next_class' => 'Top Star'],
['class' => 'Top Star', 'from' => 240000,     'to' => 320000, 'next_class' => 'Legend'],
['class' => 'Legend',   'from' => 320000,     'to' => PHP_INT_MAX, 'next_class' => 'Legend'],
```

Note **`Bronze` starts at 5,000, not 0** — a count below 5,000 answers `null` and the renderer prints the bare
count. That is deliberate and documented in the config comment.

**Both of this slice's original artefacts are superseded, not just the array.** The config array above and the
test table below both pin `Star` at `[100_000, 240_000)` and `Bronze` at `0`; the shipped values are
`Star [160_000, 240_000)` and a null below 5,000. The committed test dataset, quoted from the file:

```
[5_000,'Bronze'] [19_999,'Bronze'] [20_000,'Silver'] [49_999,'Silver'] [50_000,'Gold'] [99_999,'Gold']
[100_000,'Platinum'] [159_999,'Platinum'] [160_000,'Star'] [209_245,'Star'] [239_999,'Star']
[240_000,'Top Star'] [319_999,'Top Star'] [320_000,'Legend'] [396_191,'Legend']
```

`FanLadderTest` runs **22 passed / 28 assertions** at this tree. **Do not rewrite it.** If a later slice needs the
ladder changed, change it against `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md:474` and re-pin the whole table in
one edit, never one threshold at a time.

**Everything below is kept as the record of what was planned, so the next reader can see why it lost.** It is not
an instruction.

## Original (superseded) plan

## Tests (SUPERSEDED — the committed table is quoted above)

The text below plans a re-pin that has since landed with different values. Read it as history:

```php
it('places a count at each documented threshold in that threshold\'s class', function (int $fans, string $class): void {
    expect(FanLadder::classFor($fans))->toBe($class);
})->with([
    [0, 'Bronze'], [4_999, 'Bronze'],
    [5_000, 'Silver'], [19_999, 'Silver'],
    [20_000, 'Gold'], [49_999, 'Gold'],
    [50_000, 'Platinum'], [99_999, 'Platinum'],
    [100_000, 'Star'], [209_245, 'Star'], [239_999, 'Star'],
    [240_000, 'Top Star'], [319_999, 'Top Star'],
    [320_000, 'Legend'], [396_191, 'Legend'],
]);

it('keeps the audited reading unchanged after the ladder filled', function (): void {
    // The one reading the corpus states: 209,245 fans, class Star, 30,755 to Top Star.
    expect(FanLadder::fromValue(209_245))->toBe([
        'class' => 'Star',
        'nextThreshold' => 240_000,
        'gap' => 30_755,
        'nextClass' => 'Top Star',
    ]);
});
```

Keep the existing `it('measures the documented gap ...')` case: it becomes the second half of the pair above
and is the regression that proves the fill did not move the audited figure.

## Files

- `config/uma.php` (the array + its comment)
- `tests/Feature/FanLadderTest.php` (boundary table + re-pinned top-edge case)
- `KNOWN-ISSUES.md` (KI-165 dated closure note)

## Acceptance

- Boundary table green; `fromValue(209_245)` unchanged; `classFor(320_000)` is `Legend`.
- `grep -c "'class' =>" config/uma.php` inside the `fan_ladder` block equals the band count.
- The Cockpit's existing render needs no change: `resources/js/components/career/CareerStatePanel.vue:309` reads `class`/`gap`/`nextClass`,
  which the new bands supply for every count >= 0.
- `php artisan test --compact --filter=FanLadder < /dev/null` green; `CockpitRunIdentityTest` still green.

## Pitfall

The render guard is `props.fanLadder.class !== null`. A count of 0 now reads `Bronze` where it read the bare
number before. That is a **visible behaviour change** and belongs in the commit body: it is correct (a run
with zero fans is Bronze per the pyramid), but a reviewer seeing it without the note will read it as a bug.

---

# Slice 3.3 - KI-163: one goal-row shape

## Objective

Three declarations plus two inline shapes agree by hand today; one of them has already diverged.

## Measured (re-read at `HEAD 2546689e` — most of this slice has already landed)

| Site | State now |
| --- | --- |
| `resources/js/types.ts:96` | **`export interface GoalRow`** — the single owner |
| `resources/js/types.ts:109` | **`export interface GoalGroup`** — the single owner |
| `resources/js/components/scenario/cards/GoalGroupCard.vue` | imports both; **no local declaration** |
| `resources/js/pages/Career/Cockpit/Panels/CareerGoalsPanel.vue` | imports both; **no local declaration** |
| `resources/js/components/scenario/ScenarioCardGrid.vue` | imports both; **no inline shape** |
| `resources/js/pages/Catalog/Show.vue:91` | **still declares its own `GoalRow`**, and it genuinely differs: `label, state, year_label, turn, race_url, absence` — no fan-gate fields |

**So five of the six sites are already unified. The whole remaining slice is `Catalog/Show.vue`.** `git grep
'interface GoalRow' resources/js` returns exactly two hits, and one of them is the shared owner.

**Do not extend the shared `GoalRow` to absorb `Catalog/Show.vue`'s shape.** They are different records that happen
to share a name: the catalog one is a goal row for a *character's* goal list keyed on `turn`/`year_label`; the
career one is a scenario goal keyed on its fan gate. Either rename the catalog shape (for example
`CatalogGoalRow`) and leave it local, or give it its own exported interface in `types.ts` alongside the other. The
first is the smaller change and the one this plan recommends.

## Steps

1. Read all six blocks and confirm they are field-identical apart from `Catalog/Show.vue`. Note that
   `Catalog/Show.vue`'s divergence is deliberate: it renders a catalogue trainee's goals with no run in
   progress, so `state`, `race_url` and the fan-gate fields have nothing to hold.
2. Declare the canonical shapes once, in `resources/js/types.ts` (the file the repo already uses for shared
   types):
   ```ts
   /** A goal row's run-scoped shape (`GoalGroupCard`, `CareerGoalsPanel`, `ScenarioCardGrid`). */
   export interface GoalRow {
       label: string;
       state: string | null;
       year_label: string | null;
       turn: number | null;
       race_url: string | null;
       absence: string | null;
       fan_gate: number | null;
       fan_gate_label: string;
       fan_gate_title: string;
   }

   export interface GoalGroup {
       group: string;
       rows: GoalRow[];
       summary: string | null;
       empty_note: string | null;
   }

   /** The catalogue's narrower shape: no run is in progress, so the run-scoped fields are absent by type. */
   export type CatalogGoalRow = Pick<GoalRow, 'label' | 'absence'> & { year_label: string; turn: number };
   ```
3. Import in the four files and delete the five local declarations. `ScenarioCardGrid.vue` replaces its inline
   row type with `rows: GoalRow[]`.
4. `npm run typecheck` -> exit 0. This is the gate that proves the extraction is behaviour-preserving: every
   consumer keeps its field access.

## Files

`resources/js/types.ts` (+3 exports), `GoalGroupCard.vue`, `CareerGoalsPanel.vue`, `ScenarioCardGrid.vue`,
`Catalog/Show.vue`.

## Acceptance

- `npm run typecheck` exit 0.
- `grep -rc 'interface GoalRow\|interface GoalGroup' resources/js` returns 1 (in `types.ts`).
- Existing Cockpit objective tests green, and the browser specs that render goal rows unchanged and green.

## Why not a shared component

The three consumers render differently (a group card, a cockpit panel, a scenario grid). Extracting a
**type** removes the duplication the defect is about; extracting a component would remove three deliberate
layouts. Do the smaller thing.

---

# Slice 3.4 - KI-164: key the review payload assertions

## Objective

`SnapshotReviewTest` asserts payload rows by index, so adding a row costs a re-derivation across the file.

## Measured

`tests/Feature/SnapshotReviewTest.php`: 12 positional references - `fields.4`, `.7`, `.9` at `:54-58`; `fields.4`,
`.11` at `:154-155`; `fields.4` at `:166`; `fields.0`, `.1` at `:199-203` - plus `->has('fields', 12)` at `:153`.

## Steps

1. Add one private helper to the test class:
   ```php
   /**
    * The review payload row for one key, found by key rather than position.
    *
    * `fields` is a list ordered by the controller, so `fields.N` couples this file to that order: adding one
    * row renumbers every assertion after it. A keyed lookup makes the assertion about the row it names.
    *
    * @param  array<int, array{key: string, display: string, state: string}>  $fields
    * @return array{key: string, display: string, state: string}
    */
   private function field(array $fields, string $key): array
   {
       foreach ($fields as $field) {
           if ($field['key'] === $key) {
               return $field;
           }
       }

       $this->fail("The review payload carries no field keyed [{$key}].");
   }
   ```
2. Replace each positional assertion with the keyed form, keeping the same expectation:
   ```php
   // was: ->where('fields.4.display', 'Known · Speed 607')
   $fields = $response->viewData('page')['props']['fields'];   // or the Inertia assertion's own accessor
   expect($this->field($fields, 'speed')['display'])->toBe('Known · Speed 607');
   ```
3. Keep `->has('fields', 12)` **only** as a count guard, or replace it with a key-set assertion
   (`expect(array_column($fields, 'key'))->toBe([...])`) if the order is itself a rule the payload owns. Read
   the controller to decide: if `fields` is built in a fixed order the page renders, assert the key **list**;
   if not, assert the count.
4. Prove the fix: insert a scratch row in the controller's builder, run the file, confirm it stays green, then
   revert the scratch row.

## Files

`tests/Feature/SnapshotReviewTest.php`.

## Acceptance

The file passes; a scratch payload row addition does not break any assertion (proven then reverted); the
assertions still pin the same strings they did before.

---

# Slice 3.5 - KI-104's other half: ADR-0027 (owner act)

## Objective

Record that the register's remaining half is a signature, not a code change.

## Measured

- `docs/adr/0027-turn-and-decision-logging.md:11`: `Status: **Proposed** — drafted 2026-10-18 for the
  owner's review ... the agent cannot accept ADRs.`
- The code half is committed and the nested-disclosure defect is gone
  (`UnityCupEditor.vue` has a single `<details>` owner).

## Steps

1. Do **not** edit the status line. An agent cannot accept an ADR in this repo, and the file says so itself.
2. Append a dated note to `KNOWN-ISSUES.md` KI-104 recording: the code half is closed with its command, the
   remaining half is the owner's signature on ADR-0027, and the row therefore stays OPEN until then.
3. Present it in the decision sheet (proposal P-12).

## Acceptance

KI-104's note names both halves and the row's status is honest. No code change.

---

## Gate sequence for this plan

```bash
php artisan test --compact --filter='FanLadder|CockpitRunIdentity|SnapshotReview|DeckAnalysis|CockpitObjectives' < /dev/null
php vendor/bin/pint --test --format agent config/uma.php app/Services/FanLadder.php tests/Feature/FanLadderTest.php
php vendor/bin/phpstan analyse --no-progress --memory-limit=1G
npm run typecheck
npm run build
php tools/lore.php; php tools/lore.php code
python tools/md_fence_check.py KNOWN-ISSUES.md
```

Browser cases, session-private and one file at a time:

```bash
PLAYWRIGHT_PORT=8323 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<s>/b8323.sqlite \
  npx playwright test tests/browser/career-race-decision.spec.ts tests/browser/career-race-planner.spec.ts \
    tests/browser/career-race-strip.spec.ts --workers=1 --reporter=line
```
