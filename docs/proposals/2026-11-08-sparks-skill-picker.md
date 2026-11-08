# Proposal — Sparks skill picker: replace the native `<select>` with a searchable picker

**Status:** proposal, not slice. Awaiting owner ruling.
**Date opened:** 2026-11-08
**Source:** user's session, 2026-11-08; screenshot `/umamusume/vodka` profile page and the Legacy step's `Kind` / `Category` / `Target` row; user's words quoted in §1.
**Register reference:** none (not a Round 7 row).
**Audit reference:** none (`AUD-20261108-R7-A`, `-B`, `-C` did not name this).
**Ownership:** proposal recommends; §7 lists the decisions the owner makes.

---

## §1 Problem

The Sparks editor renders its `Target` field as a native `<select>` bound to the whole skill catalogue. On the Legacy setup step the `Skill` category offers **1,910 options in one flat list** (measured, §2 M-6). Scrolling it is the only way to reach a name you already know.

The user's words, verbatim:

> *"i manually try input all sparks each legacy/veterans, skills need scrolled manually not enter input and listed and trainer can just choose list like trainee list parents and grandparents. so many skills. UX issue solved."*

Two surfaces carry this row:

| Surface | File | `Target` selects on the page |
| --- | --- | --- |
| Legacy setup step (wizard) | `resources/js/pages/Career/LegacySelect.vue` | 1 per parent spark, 1 per grandparent spark (via `AncestorEditor`) |
| Legacy Lab builder (run-scoped) | `resources/js/pages/Legacy/Builder.vue` | 1 per parent spark, 1 per grandparent spark (via `AncestorEditor`) |
| Shared grandparent editor | `resources/js/components/legacy/AncestorEditor.vue` | 1 per spark, both surfaces |

The user's comparison is exact: `TraineeSelect.vue` and `CardPicker.vue` already solve the same shape of problem on the same wizard.

---

## §2 Evidence

Pre-flight baseline: `e7e0c5b22e93758f76e0716d9bdbb32ac4e8ab4e`, dirty paths 7 (all untracked audit/import files, none in scope).
`RENDERED_EVIDENCE = TRUE` — `playwright.chromium.launch()` returned `launched 153.0.8010.12`; every rendered figure below is a live DOM read against `php artisan serve --port=8177`.

| # | Measurement | Result |
| --- | --- | --- |
| M-1 | `select count(*) from skills` | **1910** |
| M-2 | `select count(distinct name) from skills` | **1897** (10 names duplicated; e.g. `Carnival Bonus` ×5) |
| M-3 | `skills where release_status = 'GlobalReleased'` | **623** rows, **621** distinct names |
| M-4 | Server option list `LegacySelectController.php:197` | `Skill::query()->orderBy('name')->pluck('name')->all()` — **no `availableOnGlobal()` scope**, so all 1,910 rows, including 1,287 `JapanOnly` |
| M-5 | Same assembly in `LegacyController.php:270` | identical — both doors send the same 1,910 |
| M-6 | **Rendered** option count, `<select id="ancestor-spark-target-a-0-0">` with Category = `Skill` | **1911** (`1` placeholder + `1910` skills). First five: `#BeautifulSprint`, `#CuteChaos♪`, `#GotYouDownBad♪`, `#LookatCurren`, … Last three: `α-star*`, `∴win Q.E.D.`, `♡ 3D Nail Art` |
| M-7 | Rendered option count with Category = `Stat` / `Aptitude` | **6** / **11** — the wall is `Skill` only |
| M-8 | Existing searchable skill picker in the tree | `resources/js/components/Form/SearchableSkillPicker.vue`, **278 lines**, already used by `BuildTarget.vue:632` and `SkillsPlanner.vue:907` |
| M-9 | What `SearchableSkillPicker` exposes | props `options: {id,name,type}[]`, `placeholder`, `label`, `disabled`, `error`, `hint`; emit `select: {id, name}`. Combobox with `role="listbox"`/`role="option"`, `aria-activedescendant`, ArrowUp/Down, Enter, Escape, outside-click, no-match message (`:245-273`, `:108-141`) |
| M-10 | Skill fields carrying searchable text | `name` 1910/1910, `name_ja` 1910/1910, `description` 985/1910, `type` ∈ {Recovery, Speed, Passive, null} |
| M-11 | `AncestorEditor.vue` `Target` options bind | `props.sparkTargets[spark.category]` — the **string list** (`:181`), no id, no type |
| M-12 | `LegacySelect.vue` `Target` options bind | same string list (`:576`) |
| M-13 | Server-side category boundary | `StoreLegacySelectionRequest::assertTargetMatchesCategory()` (`:203-226`) validates `Stat` and `Aptitude` against config lists and **leaves `Skill` unvalidated** — "the UI offers only catalogue rows, and the stored target is the string the Trainer read, not an id" |
| M-14 | Are any stored spark targets `JapanOnly`? | **None.** `training_runs` with a non-null `legacy_selection`: **1** (run 3). Its two legs record `"sparks":[]` and no `ancestors_sparks`; `veterans` table: **0** rows; `import_source` non-null runs: **0**. No stored target exists to be orphaned by the `availableOnGlobal()` filter, so §7 decision 1's guard is preventive — but today's `<select>` offers all 1,910, so a `JapanOnly` target is reachable through the current UI and the guard becomes load-bearing the moment one is saved |

### What the tree does *not* have

`SearchableSkillPicker` is the mechanism the user is asking for, and it exists. What does not exist is a picker that:
- accepts a **string** target rather than `{id, name}` (the spark payload stores a name, under no foreign key);
- carries a **category-scoped** option list (Stat/Aptitude/Skill are three different vocabularies behind one control);
- renders **inside a row of four sibling controls** (Kind, Category, Target, Stars) rather than as a full-width block.

---

## §3 What current code does

`LegacySelectController::show()` (`app/Http/Controllers/Career/LegacySelectController.php:194-198`):

```php
'sparkTargets' => [
    'Stat' => array_values((array) config('scenarios.stat_order')),
    'Aptitude' => LegacySelectionPayload::SPARK_APTITUDES,
    'Skill' => Skill::query()->orderBy('name')->pluck('name')->all(),
],
```

`LegacyController::show()` (`:270-274`) composes the identical array, deliberately — the comment says both surfaces post against the one request, so a separate copy would let the two offer different options for one validated field.

`AncestorEditor.vue:177-182` renders it:

```vue
<BaseSelect
    :id="sparkId('ancestor-spark-target', sparkIndex)"
    v-model="spark.target"
    label="Target"
    :options="[{ value: '', label: 'Not recorded' }, ...(props.sparkTargets[spark.category as 'Stat' | 'Aptitude' | 'Skill'] || []).map((target) => ({ value: target, label: target }))]"
/>
```

`LegacySelect.vue:572-578` renders the same, with `:error="errorFor(index, `sparks.${sparkIndex}.target`)"` added.

`BaseSelect.vue` is a genuine `<select>` (template lines 1-60): one `<option>` per list entry. Chromium renders 1,910 options as one scrolling popup — type-ahead works on a native select, but only as a jump-to-prefix, not as a filter, and the user's report is that they scroll.

`AncestorEditor.vue:5-9` carries a load-bearing warning that bounds any fix:

> *"**The field-id contract is load-bearing and cannot be renamed.** Three specs locate these nodes by id and label — `legacy.spec.ts`, `legacy-index.spec.ts` and `career-inheritance-event.spec.ts` — so the shape is fixed at `ancestor-${slot}-${index}`, `ancestor-rank-letter-${slot}-${index}`, `ancestor-rank-${slot}-${index}`, `ancestor-spark-kind-${slot}-${index}-${sparkIndex}` (and `-category-`, `-target-`, `-stars-`). Renaming an id here is a spec break, not a refactor."*

---

## §4 Proposal recommends

**Extend the existing `SearchableSkillPicker` into a variant the spark row can use, rather than building a second picker.**

Specifically:

1. **One new component**, `resources/js/components/legacy/SparkTargetPicker.vue`, modelled on `SearchableSkillPicker.vue` and **not** a fork of it if the generalisation is small. It takes the *category-scoped* list as a prop (`targets: string[]`) plus `category: 'Stat' | 'Aptitude' | 'Skill'`, and emits `select: string`.
2. **Three input modes behind one control.** Stat (5 options) and Aptitude (10 options) stay a picker-shaped list because the component handles them without special-casing; Skill (623 Global / 1,910 all) gets the search box. The control's own shape does not change with the category, so the row's layout is stable (`SHR-1`, one component per role).
3. **Filter the Skill list to `availableOnGlobal()`.** The current list offers 1,287 `JapanOnly` skills a `[Global]` client cannot produce. This is the single highest-value change and it is **independent of the picker**: 1,910 → 623 options, a 67% cut, by adding one scope call in two controllers. This is `UX-2`'s "survivable count" applied at the source rather than in the widget. **One guard rides with it:** the picker must always include the row's own current value even when that value falls outside the filtered list, or a spark recorded against a `JapanOnly` target (a Veteran's imported run, say) would render its Target as unset and the stored value would become invisible on the next edit. The guard is stated in §7 decision 1.
4. **Id is preserved.** `id="ancestor-spark-target-${slot}-${index}-${sparkIndex}"` moves onto the picker's `<input>`, and the three specs that locate `getByLabel('Target')` + the id keep working. Where `SearchableSkillPicker` uses `useId()`, this variant must accept the caller's id — the contract is external.

**Rules cited:** `UX-2` (option count survivable — 1,910 in a flat list is not), `SHR-2` (a novice can type a name; an expert is not slowed — a flat `<select>` serves neither), `IA-3` (the library is browsable by more than one axis; today Skill is one alphabetical axis with no filter), `WCAG 4.1.2` (name/role/value on the replacement).

**Related, not filed:** `H6` (recognition over recall) is the same defect stated heuristically; `UX-2` is the narrower citation and `H6` travels as the secondary reading.

---

## §5 Rejected alternatives

| Alternative | Why rejected |
| --- | --- |
| **A. Inline searchable `<datalist>` on a plain text input** | Native, zero component cost, but the browser's autocomplete popup is not styleable, does not report a count, announces nothing (`4.1.2`), and in Chromium caps its visible list at ~10 rows. It also accepts free text, which the server's `Skill` branch permits (M-13) but which lets a typo through as a stored target. |
| **B. `CardPicker`'s modal dialog** | `CardPicker.vue` is a `<dialog>` that fetches its own options and keeps a filter bar (269 lines). Sparks arrive up to **6 rows per legacy × 2 legs**, each with its own Kind/Category/Target/Stars. Six modals on one screen is worse than six selects, and the spark row's target is a small field beside three siblings, not a deck-slot decision. |
| **C. Leave `<select>`, add type-ahead only** | Native `<select>` type-ahead already exists and already fails the user's report. It jumps to a prefix; it does not filter. |
| **D. Replace `BaseSelect` globally** | 27+ call sites, most of them 3-11 option lists where a `<select>` is correct. The defect is on one field at one cardinality. |
| **E. Conditional control — keep `<select>` for Stat (6 options) and Aptitude (11 options), swap in the picker only for Skill (623)** | Proportionate to each count, and it is the smallest possible surface change: two thirds of the row's categories keep the control they already have. **Rejected** because the row's control shape would then depend on the category the Trainer picked. Changing Kind or Category from `Skill` to `Aptitude` would swap a combobox for a native `<select>` under the cursor — `SHR-1`'s "the same thing behaves the same everywhere" failing from the other direction, and the swap lands mid-edit with focus to lose. `SearchableSkillPicker` handles a 5-item list as well as a 623-item one for no extra cost, so the proportion argument buys nothing and the inconsistency costs a re-learn. This is the alternative the one-unified-shape recommendation in §4 item 2 exists to refuse. |

**Recommended over all five:** the focused variant in §4, because it reuses a picker the tree already ships and already passes the accessibility contract (`SearchableSkillPicker.vue:10-14` documents listbox semantics, 2.5.3, 44px, and the D-55 keyboard contract).

---

## §6 Fix would not change

- **No bulk spark entry.** Typing/pasting a whole spark set at once is `R7-36` facet (b)'s territory (`docs/audits/rice-shower-cup-round7-reconciliation.md:956`) and is a separate proposal.
- **No change to what a spark stores.** The payload keeps `{kind, target, stars}`, target a string. No foreign key, no id column, no migration.
- **No change to the category boundary.** `assertTargetMatchesCategory()` stays as-is; the picker narrows what is offered, the request still refuses a mismatched pair.
- **No change to `Skill` validation.** Adding `assertTargetMatchesCategory`'s `Skill` branch to validate against the catalogue is a *possible* hardening, but it is a trust-boundary change the user did not ask for and it would refuse targets already stored by older runs. Recorded as an owner decision in §7, not done here.
- **No change to the Kind or Category controls.** They keep `BaseSelect` — 5 and 3 options.
- **No change to `BaseSelect.vue`.** 27 call sites stay on the native control.
- **No scenario name** enters a component (`AGENTS.md` §7, gate G-33).

---

## §7 Owner decisions required

| # | Question | Recommendation | Why |
| --- | --- | --- | --- |
| 1 | **Filter the Skill list to `availableOnGlobal()`?** 1,910 → 623 | **Yes.** Do it in both controllers, in the same change. **The picker must include the row's current value even when the filter excludes it** — a `Current: <name>` row prepended to the list, or an equivalent fallback, so a spark already recorded against a `JapanOnly` target still renders its stored value on re-edit rather than showing empty and silently losing it on save | A `[Global]` run cannot produce a `JapanOnly` skill. Offering 1,287 impossible targets is the larger half of the defect and costs one scope call. Note the two doors must change together (M-5). **Measured: no such row exists in the dev database today** — one run carries `legacy_selection`, its two legs record `sparks: []` and no ancestor sparks, and there are zero Veterans (M-14). The guard is therefore preventive against future imported or Veteran-sourced data, and it is load-bearing the moment one exists: today's `<select>` offers all 1,910, so a stored `JapanOnly` target is reachable by the current UI, not just by an import. |
| 2 | **Inline combobox or modal dialog?** | **Inline** (combobox over the row) | The row already carries four controls; a modal per spark is 6 dialogs on one screen. `CardPicker`'s modal answers a different question (browse a catalog, pick one of six slots). |
| 3 | **Does the picker inherit the row's Category?** | **Inherits** — it is the same `spark.category` that already drives the list | The current `<select>` is already category-cascaded (`@change="spark.target = ''"` on the Category select, `LegacySelect.vue:570`). A picker that filtered independently would offer the wrong vocabulary and the server would refuse the write. |
| 4 | **What does search match on?** | **Name only**, with `name_ja` as a secondary match if it is cheap | `description` is populated for 985/1,910 (M-10) and the option list is built from `name` alone today (M-4); matching a field the payload does not carry means sending a second array. Name-only is what the user asked for ("choose list like trainee list"). |
| 5 | **Keyboard contract** | **Preserve `SearchableSkillPicker`'s**: type, ArrowUp/Down, Enter, Escape, Tab closes | `SearchableSkillPicker.vue:108-141` already implements it and documents it as the D-55 contract. A new hand-rolled picker would have to re-earn it. |
| 6 | **Reuse `SearchableSkillPicker` or build a sibling?** | **Build a sibling that shares the pattern**, unless the generalisation proves under ~30 lines | `SearchableSkillPicker` takes `{id, name, type}[]` and emits `{id, name}`; sparks need `string[]` → `string`. Generalising means either a discriminated union prop or a second emit shape at 3 call sites. Decide at implementation time; §9 shapes both. |
| 7 | **Where does it land?** | **Both surfaces** — `LegacySelect.vue` (wizard) and `Legacy/Builder.vue` (run-scoped), through `AncestorEditor.vue` for the four grandparent nodes | `AncestorEditor` is shared by both (`LegacySelect.vue:36`, `Legacy/Builder.vue:53`), so one edit covers four of the six editors; the two parent rows are the page's own markup. |
| 8 | **Harden the `Skill` category server-side?** | **No, not in this change** | Out of the user's ask; would refuse stored targets from older runs. Record as a follow-up if the owner wants it. |

---

## §8 Risk and reversibility

- **Blast radius.** Three `.vue` files, two `.php` controllers (the `availableOnGlobal()` scope and, if decision 6 goes the generalisation route, `SearchableSkillPicker.vue`). No migration, no model, no route.
- **Reversibility.** The picker is a leaf component behind one prop contract. Reverting means restoring the `<BaseSelect>` block in `AncestorEditor.vue:177-182` and `LegacySelect.vue:572-578` — the string list, the `v-model`, and the error bind are all unchanged, so the revert is the two original blocks. The `availableOnGlobal()` scope is one call and reverts independently.
- **What breaks if removed.** The spark target field stops filtering. Nothing else reads it.
- **Spec risk.** `AncestorEditor.vue:5-9` names three browser specs that locate these fields by id and label. The ids are preserved (`§4` item 4), but `getByLabel('Target')` against an `<input>` instead of a `<select>` changes what Playwright's `selectOption()` can do. **The three specs need a read before implementation** and likely a `fill()`-based helper. This is the one real cost of the change and it should be measured, not assumed.
- **Duplicate-name risk.** 10 names exist twice (M-2), including `Carnival Bonus` ×5. A name-based picker will show five identical rows. The current `<select>` has the same problem, so this is not a regression, but a picker with a count label would make it visible.

---

## §9 Proposed slice shape (not commitment)

**Not a commitment.** Naming a shape, not a slice id, commit or phase.

- **One commit or two.** Decision 1 (`availableOnGlobal()`) is a server-only change and could land alone with its own test; the picker is the frontend half. Two commits if the owner wants the count reduction immediately.
- **Files.** `resources/js/components/legacy/SparkTargetPicker.vue` (new), `resources/js/components/legacy/AncestorEditor.vue`, `resources/js/pages/Career/LegacySelect.vue`, `app/Http/Controllers/Career/LegacySelectController.php`, `app/Http/Controllers/LegacyController.php`; `resources/js/pages/Legacy/Builder.vue` only if the parent rows differ from `LegacySelect`'s.
- **Test file.** A feature test asserting `sparkTargets.Skill` excludes a `JapanOnly` name and includes a `GlobalReleased` one, pinning the count drop; a browser spec on the Legacy step asserting the Target control is a combobox, that typing `Xceleration` narrows it, and that ArrowDown + Enter selects.
- **RED looks like.** The feature test fails with `Expected [623 rows] but got [1910]` before the scope lands. The browser spec fails locating a `role="listbox"` beside the Target label, because a `<select>` exposes `role="combobox"` with no listbox child.
- **Pint/PHPStan/typecheck** per the hand-off sequence; `npm run lint:ui` will flag any new arbitrary Tailwind value, and the picker should reuse `SearchableSkillPicker`'s existing classes rather than invent any.

---

## §10 Proposal does not do

- No code. No file under `app/`, `resources/`, `database/`, `tests/`, `config/`, `routes/` is changed by this session.
- No slice opened. No RED, no GREEN, no pre-flight gate.
- No dispatch. No slice id, commit SHA or phase named.
- No re-opening of Round 7. This defect is not `R7-36`, `R7-68`, `R7-78`, `R7-79` or `R7-80`: those record sparks being **absent or unrepresented**; the editors exist and store correctly (`R7-36` is `Does not reproduce` at `cdd0083a`, recon `:926`). This is the picker's ergonomics, which no register row names.
- No claim that the audits named this. `AUD-20261108-R7-A/B/C` did not.
- No third issue. One observation worth recording: `Skill` targets are unvalidated server-side (M-13). Noted in §6 and §7 decision 8, not proposed.

---

## §11 Change log

| Date | Change |
| --- | --- |
| 2026-11-08 | Opened. Pre-flight baseline `e7e0c5b22e93758f76e0716d9bdbb32ac4e8ab4e`, `git status --short` = 7 untracked (`docs/audits/RS-UC-2026-10/`, `RS-UC-2026-11-snapshot-door/`, `RS-UC-2026-12/`, `rice-shower-unity-cup-ux-walk-6.md`, `-walk-7-snapshot-door.md`, `-walk-7.md`, `docs/imports/ura-finale-runs.md.bak`). `RENDERED_EVIDENCE = TRUE` (`launched 153.0.8010.12`). Measurements M-1..M-13 taken against `database/database.sqlite` and a live `php artisan serve --port=8177`. |
| 2026-11-08 | Refinement — §5 alternative E: conditional picker (`<select>` for Stat/Aptitude, picker only for Skill) rejected on `SHR-1` |
| 2026-11-08 | Refinement — §7 decision 1 guard: the picker includes the row's current value even when the `availableOnGlobal()` filter excludes it; M-14 added recording that no such value exists yet |
| 2026-11-08 | Refinement — duplicate check against `docs/proposals/` run (title filter + body grep for `character_cards.*base.?stat`, `spark.?target`, `1,?910`, `JapanOnly`); the only hits are this proposal's own text. No prior proposal covers this surface. |
