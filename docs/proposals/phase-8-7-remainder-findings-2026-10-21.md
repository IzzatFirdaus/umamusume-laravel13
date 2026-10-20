# Phase 8.7 remainder — findings (2026-10-21)

**Baseline:** master `1e5a947b`, ahead 5 of `origin/master` `440b2ef8`.
**Outcome:** three diagnostics, **zero code commits**. Two of the three findings are "no gap in the
current tree"; one needs a copy ruling. Everything below is measured, not inferred.

---

## 1. `legacy.spec.ts:149` — the case pins deleted subjects (copy ruling owed)

`tests/browser/legacy.spec.ts` → "draws the six-node graph, assigns by keyboard and states every
absence". The case carries **11 assertion groups**; the drift is in the case's *subjects*, not only
its locators.

### The disclosure DOM, measured

`/legacy/{run}` renders **six** `<details>`:

| # | Summary text | Owner |
|---|---|---|
| 1 | `iWhat this screen records` (an info glyph + the sentence) | record-only popover — `Legacy/Builder.vue:312-318` |
| 2–5 | `Sparks for Grandparent A1 / A2 / B1 / B2` | per-node Spark panels — `Legacy/Builder.vue:456+` |
| 6 | `More` → *New Career / Veterans / Skills / Review / Database / Settings* | **`AppLayout.vue`'s own mobile disclosure** |

So the two whole-page locators (`page.locator('details summary')`, `page.locator('details')`) resolve
**6** elements each and trip strict mode.

### The working anchor

```typescript
// `getByRole('group', { name })` does NOT work: `group` is not a name-from-content role, so a
// <details> exposes no accessible name.
const recordOnly = page.locator('details', { has: page.getByText('What this screen records') });
await expect(recordOnly).toBeVisible();
await expect(recordOnly).toContainText(RECORD_ONLY_NOTICE);
```

`openAllDisclosures(page)` (`tests/utils/accessibility.ts:37` — sets `details.open`, fires no handler,
idempotent) is required before the assertions that read *inside* the collapsed per-node panels.

### The four deleted subjects

| Case assertion | Deleted subject | Current state in the tree |
|---|---|---|
| `:176-177` | `'N/A, not chosen'` visible text | now a **`title` attribute** — `AncestryNode.vue:92` ("two absences it is: not chosen, or not read") |
| `:182-183` | `Spark chance` cell contains `"N/A"` | wording changed |
| `:184` | `"no sourced star-roll table"` | survives only as a **docblock comment** — `AncestryNode.vue:20` |
| `:189` | `getByLabel('Assign Parent A')` | label now reads **`Assign to Parent A`** — `AncestryNode.vue:128` (`Assign {{ label }}`) |
| `:207+` | `'Star editor'` | absent |

**Copy-drift pattern, in order: visible text → `title` attribute → docblock comment → renamed
label.** The case pins the *old channel* for each fact.

### Ruling owed (three options)

1. **Delete** the five assertions as pinning deleted behaviour; keep the case for what the tree
   ships. *(Structural — narrowing coverage leaves no failable signal.)*
2. **Resurrect** the deleted subjects (revert the copy change). Valid only if the current wording is
   a regression.
3. **Re-point** the assertions at the new mechanism: assert `title` instead of text, assert the
   renamed label, assert the current Spark-chance wording. *(Behavioural — each re-pointed assertion
   can be made red.)*

### Attempts made and reverted

Two partial rewrites were attempted and **reverted**, per the rule that a fix which advances to a
new failure is evidence the diagnosis is incomplete:

- scope by `hasText` → strict mode cleared, then failed at a later `toBeVisible`;
- scope by `has:` + `openAllDisclosures` → assertions 1–2 passed, then failed at
  `getByText('N/A, not chosen')` — because that text is not in the DOM at all.

No partial work was left in the tree.

---

## 2. `legacy.spec.ts:330` — WCAG 1.4.10, and the overflow is state-dependent

The case asserts the **correct** invariant, not a stale shape:

```typescript
const overflow = await page.evaluate(
    () => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 1,
);
expect(overflow, 'the document scrolls horizontally at 320px').toBe(true);
```

`Received: false` means **the page overflows at 320 px** — a live WCAG 1.4.10 violation. The case is
the invariant; the tree is the variable. **Do not relax the assertion.**

### Why the first dump missed it

The case navigates to `/legacy/compare?runs[]={run}` (`:333`, **populated**) and sets the 320 px
viewport **after** load (`:334`). A dump of the **empty** `/legacy/compare` reports
`scrollWidth 320 = clientWidth 320`, `overflows: false`, and no element wider than the viewport. The
empty state does not overflow; the populated one does. A dump that omits the query param, or that
sets the viewport before navigating, measures a different page.

`Legacy/Compare.vue:232-234` is correctly built (`overflow-x-auto` region over a `min-w-[40rem]`
table), so the overflow is not the table escaping its container.

**Next measurement:** reproduce the case's exact state — `?runs[]={run}`, load, *then*
`setViewportSize({320, 800})` — and enumerate every element with
`getBoundingClientRect().right > viewport + 1`, with its closest layout ancestor. If the owner is
`AppLayout.vue` (a sidebar or shell element), the fix is **coord-before-touch** and waits for
coordination; if it is a Legacy-local wrapper, the fix dispatches.

---

## 3. `dashboard.spec.ts:370` — **no gap**; the red does not reproduce

The prior hypothesis was intra-file state contamination: `:300` and `:338` save a layout preference
through `/preferences`, and `:370` assumes the standard width on arrival.

**Measured, and the hypothesis does not hold:**

| Run | State | Result |
|---|---|---|
| file order, reused scratch DB, fix reverted | — | **14 passed** |
| file order, **fresh** scratch DB, fix reverted | — | **14 passed** |
| file order, fix applied | — | **14 passed** |

`dashboard.spec.ts` is green in file order on a fresh database with the change reverted, so the case
is **not** contaminated by its own file, and the earlier red (observed in the 6.4 walk) no longer
reproduces — most plausibly fixed by the side agent's responsive commits landed since.

### The mechanism, for whoever writes the next case

The layout preference is **server-side**: `useLayout.ts:35` reads
`page.props?.preferences?.settings?.dashboard_layout`, and `PreferenceController` stores it
(`app/Http/Controllers/PreferenceController.php:64-65, 135-143`) with the values
`standard | compact | wide` — surfaced as the radio group **Compact / Standard / Wide**
(`resources/js/pages/Preferences/Edit.vue:374`). Because it lives in the **database**, it survives
across cases in a file, across files, and across a whole run. A case that reads it must either
establish it or be independent of it; a case that writes it should restore the default.

**Decision: zero commits for this item.** The case is green as shipped.

---

## 4. Unity Cup panel — partial measurement (step 1.1 not completed)

`resources/js/components/scenario/UnityCupPanel.vue` already carries the facts the docs landing
brought, as far as the partial read reached:

- `:85` `team_league_placement: number | null` — league placement is a first-class field;
- `:89` `spirit_bursts_count: number | null` and `:143-144` the segment
  `Spirit Bursts {n} / {m} Extreme` — the burst counter already splits the two halves;
- `:12` — "the figures this slice introduces get their provenance beside them: the +30 ranking
  bonus …" — the +30 bonus is documented as carrying provenance;
- `:108` `composesBursts` gates the burst region on the `spirit_bursts` widget key.

**Not yet classified:** the five questions of step 1.1 (burst payout shape, the Wit column's
`+10` vs `+15`, the finals ground, the league-placement source note, and whether `+30` is bare or
labelled). A full read of the panel is required before any fix is proposed.

---

## Summary of the Phase 8.7 remainder

| Item | Verdict | Commits |
|---|---|---|
| `legacy.spec.ts:149` | copy ruling owed (3 options) | 0 — blocked |
| `legacy.spec.ts:330` | live WCAG 1.4.10 violation; owner not yet localised | 0 — needs the populated-state dump |
| `dashboard.spec.ts:370` | **no gap** — green as shipped | 0 |
| Unity Cup (step 1.1) | partially measured; five questions unclassified | 0 — incomplete |
