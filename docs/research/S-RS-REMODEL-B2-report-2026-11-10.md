# `S-RS-REMODEL-B2` — report

**⚠ RULING 3 IS STILL OWED.** The remodel is **not** complete: Moves 1 and 4 remain blocked on panel
priority, and Ruling 1 (capsule chrome for the page) is untouched. This dispatch advanced Slice 0
(F-01/F-02 landing), Move 3 (the one consolidation Ruling 2 authorized outright), and KI-158.

**Base HEAD:** `d9e4fa0e`. **Final HEAD:** `3ad5305d` before this report's own commit.
**Start peer set:** 7 modified + 6 untracked, re-measured (not carried forward).

---

## 1. Pre-flight

`d9e4fa0e` verified. Path verification: every path this dispatch names resolves
(`app/Services/Career/TeamPanelBuilder.php`, `resources/js/components/scenario/UnityCupPanel.vue`,
`resources/js/components/scenario/ScenarioPanel.vue`,
`resources/js/pages/Career/Cockpit/Panels/CareerGoalsPanel.vue`,
`resources/js/components/SpiritBurstRoster.vue`, `tests/Feature/UraPanelTest.php`, `KNOWN-ISSUES.md`).
KI-158 at `KNOWN-ISSUES.md:4753`.

### Per-file diff classification (all 7 modified files)

| File | Hunks | Class |
|---|---|---|
| `app/Services/Career/TeamPanelBuilder.php` | alert `level` + `text` now derive from row state (F-02) **plus** the `S-A-P01.OG` comment from the prior dispatch | **F-02 + prior dispatch's own C work** |
| `resources/js/pages/Career/Cockpit/Panels/CareerGoalsPanel.vue` | `:glyph` / `:tone` follow `alert.level` | **F-02** |
| `resources/js/components/scenario/ScenarioPanel.vue` | `emptyText` gate narrows to `onPanels.length === 0` | **F-01** |
| `SCREEN_SPEC.md` | `:2781` state row + alert row, both dated corrections | **F-01 + F-02 docs** |
| `tests/Feature/CockpitAlertScopeTest.php` | `not_reached` copy assertions | **F-02 tests** |
| `tests/Feature/TraineeGoalsPanelTest.php` | two new alert-vocabulary cases | **F-02 tests** |
| `tests/browser/scenario-panel.spec.ts` | `openScenarioDetail` + the F-01 no-panels case | **F-01 tests** |

**No file carries peer (unrelated-arc) work.** The hard stop did not fire. The one mixed file holds
F-02 plus this session's own earlier C comment, which the prior dispatch's report explicitly recorded
as belonging with whichever commit landed that work — so it was committed here, in the F-02 commit,
and the report says so rather than letting it ride silently.

## 2. `git add -N` correction from last dispatch

Honoured: every `git add -N` used this dispatch was followed immediately by the pathspec commit, and
any intent-to-add entry outside the commit was reset. Verified at the end: `git status --short` shows
no `^ A ` entries.

---

## 3. Slice 0 — F-01 / F-02 landed

### Commits

| Commit | Scope |
|---|---|
| `43024812` | `fix(cockpit): state-led alert vocabulary in TeamPanelBuilder (F-02)` |
| `1cd7fe84` | `fix(cockpit): glyph and tone follow alert level (F-02)` |
| `f16ffbe7` | `fix(scenario-detail): strip emptyText gates on onPanels.length (F-01)` |
| `b3607d0f` | `test(cockpit,scenario): F-01/F-02 coverage` |
| `4e7e95e3` | `docs(spec): F-01 strip guard; F-02 alert vocabulary` |

### Non-vacuity — F-02 (alert vocabulary)

Swapped all three HEAD versions into the working tree and re-ran the pins:

```
FAILED  CockpitAlertScopeTest > it raises no due alert for a trainee objective whose race the run a…
FAILED  TraineeGoalsPanelTest > it speaks the row state in the alert …
FAILED  TraineeGoalsPanelTest > it keeps the critical heading and the not-met wording only where a…
Tests:    3 failed, 27 passed (343 assertions)
```

Restored → `Tests: 30 passed (349 assertions)`. **F-02 is genuinely pinned.**

### Non-vacuity — F-01 (strip guard)

Built HEAD's `ScenarioPanel.vue`, ran the browser case:

```
1 failed
  [chromium] › tests\browser\scenario-panel.spec.ts:200:1 › keeps the no-panels sentence off a scenario that composes panels
```

Restored the fix, rebuilt, re-ran → `1 passed (20.9s)`. **F-01 is genuinely pinned.**

---

## 4. Slice 1 — Move 3 (Spirit Bursts → Burst bands)

**Plan fidelity:** `28d9afbf`'s Move 3 was specific enough (`s8` bands card + `s4`
`SpiritBurstRoster` → one card, bands table then roster rows beneath, `Estimated` stays on the bands
half, capsule text preserved). No ambiguity stop.

### Implementation

- `SpiritBurstRoster.vue` gains a **`bare`** prop: suppresses its own `card` wrapper and
  `CapsuleHeader`, prints its heading as a `.sublabel` paragraph. Default keeps the standalone card so
  the component still stands alone.
- `UnityCupPanel.vue` nests the roster inside the bands card under a `border-t` rule, and widens that
  card `s8` → **`s12`** because its `s4` sibling no longer exists.

### The regression this caught

The first build left **a 330 px hole** — removing the `s4` Spirit cell let Special Training float up
beside the `s8` bands, leaving Team race coverage alone in the next row. Widening the merged card to
`s12` fixed it. Measured after:

| Row | Panels (s) | Hole |
|---|---|---|
| 1 | Team Race (12) | 0 px |
| 2 | Team · Spirit · Team Rank (4/4/4) | 23 px |
| 3 | Unity Cup progress · Team stat grades (4/8) | 12 px |
| **4** | **Burst count bands + roster (12)** | **0 px** |
| 5 | Special Training · Team race coverage (4/8) | 12 px |
| 6 | Team race shape (12) | 0 px |

**Worst hole = 23 px** (row 2's pre-existing gap). Panel count **11 → 10**. Rows 1–3 and 5–6 are
byte-identical to `6493326a`; only row 4 changed, which is the move.

### Non-vacuity, raw DOM at 1280 px

Before: two cells in row 4 (`s8 Burst count bands` + `s4 Spirit Burst`), 11 panels total.
After: one cell (`s12 Burst count bands` containing the roster), 10 panels total.
Content preserved and asserted in the DOM: `This run: 11 bursts combined` **true**, `(this run)`
marker **true**, roster text `No teammate burst states recorded` **true**, `Spirit Burst` heading now
a `P.sublabel`.

### Test consequence

`unity-cup-panel.spec.ts` asserted `panels.length >= 13` (nine team panels + four added). The merge
removes one card, so it is `>= 12`, with the reason in a comment. Changed in
`b7ab4492` (`test(scenario-detail): card count reflects the Move 3 merge`) — not deleted, and the case still fails
if a future change un-capsules a panel.

## 5. Slice 2 — KI-158

**Commits:** `fdf6452f` (`test(cockpit): UraPanelTest expects not_reached …`) and the following
`docs(known-issues): mark KI-158 resolved`.

Verified against `27a3d374` before editing: that commit changed `$reached => 'missed'` to
`$reached => $this->objectiveHasRecord($goal, $run) ? 'missed' : 'not_reached'`, with the comment
"`missed` claims the run went and did not meet the requirement; a run imported mid-career never went".

Corrected with a dated comment naming `27a3d374` and `30eb2b4d` and why the old value no longer
matched, per `AGENTS.md` §9 — the case was **not** deleted and no test was skipped.

**Non-vacuity both ways:**
- set back to `'missed'` → `1 failed, 7 passed (137 assertions)`
- corrected value → `8 passed (140 assertions)`

This is the predicted suite effect: **2302 passed / 3 failed → 2303 passed / 2 failed**.

## 6. Slice 3 — HFW

**Commits:** `42d91678` (`docs(hfw): reflect Move 3 …`) and `3ad5305d` (the follow-up naming the real render SHA).

- The separate `s4 Spirit Burst` card is gone; the roster renders inside the `s12` bands card under a
  rule with a `.sublabel` heading.
- Annotation updated: a new item records Move 3 with its measurement, the panel enumeration now reads
  ten panels with `Burst count bands + Spirit Burst roster (s12)`, and item 1's header counts were
  corrected from "fourteen `/` eleven" to "thirteen card `h2` (ten in the bento, three below)".
- `README.md` gains the dated note: *"Rendered/current as `42d91678`; Move 3 (Spirit Bursts → Burst
  bands) reflected."*
- **Re-rendered:** `rendered-light-1280.png`, `rendered-light-bands.png`, `rendered-light-764.png`,
  `rendered-dark-764.png`. Renderer: Chromium via CDP `Page.captureScreenshot` at
  `Emulation.setDeviceMetricsOverride` 1280×1400 and 764×1400. Playwright's bundled Chromium for this
  host reports **153.0.8010.12**; the CDP session's UA string reports `Chrome/145.0.0.0`, and both are
  recorded here rather than only the expected figure.

**HFW ↔ app agreement, measured in the browser:** 10 bento cards, same spans and same row order as the
app; `lattice-bleed` 0, `bg-chrome` 0.

**One observation recorded, not acted on:** rendering the merged card shows the surviving header reads
`Burst count bands` while the card now holds two topics (the roster sits under its own `.sublabel`).
Renaming the header would be a header-chrome/content decision, i.e. Ruling 1 territory, so it is
recorded here and left for the owner.

## 7. Gates

| Gate | Command | Result |
|---|---|---|
| Tests | `vendor/bin/pest --no-coverage --compact` | **`Tests: 2 failed, 9 warnings, 2303 passed (36232 assertions)`** — read from the log's own summary line, not the shell exit. The two are `ScenarioAdvisorFinaleTest` and `UiHygieneTest`, both pre-existing and both out of scope. |
| PHPStan | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | `[OK] No errors` |
| Lint | `npm run lint:ui` | **500** findings = baseline, **delta 0** |
| Lore docs | `php tools/lore.php` | **579 hits, 75 exempt** — delta **+2** from 577. Both traced by set-diff to **my own** workstream reports from the previous dispatch (`s-rs-ki154-1-report-2026-11-10.md:130` and `S-RS-REMODEL-B1-summary-2026-11-10.md:35`), each quoting the word "pairing" from `VERIFICATION.md:20`. Same class 2 (ordinary-word substring). Ruled, not edited. |
| Lore code | `php tools/lore.php code` | **318 hits** — delta **0** |
| Typecheck | `npm run typecheck` | clean, `EXIT=0` |
| Markdown | `python tools/md_fence_check.py` | `0 invalid closing fence(s)` on every edited file |
| HFW links | `node …/link-check.mjs` | `checked 1547 references across 56 pages` / `no broken references` |
| Pint | `vendor/bin/pint --test --format agent` | `passed` on every changed PHP file |
| Browser | `unity-cup-panel.spec.ts` + `scenario-panel.spec.ts` | **25 passed, 1 failed** — the failure is the pre-existing `Gold hint Lv3, +40 matching stat` mismatch (config says "+40 **to the** matching stat"); `git diff 6493326a..HEAD` on that assertion is empty, and `config/scenarios.php` is untouched, so it neither predates nor follows this change. Out of scope per the dispatch's own `bands_source`-class exclusion. |

## 8. Post-commit peer set

`git status --short` after the commits shows the same six untracked arc documents and **no** modified
files — every modified path from pre-flight was committed by this dispatch. No peer path appears in
any commit (`git show --name-only` checked per commit).

## 9. What is still blocked

| Item | Gate |
|---|---|
| Move 1 — reflow regroup | **Ruling 3** |
| Move 4 — absorb the Team Race banner | **Ruling 3** |
| Move 2 — split the Unity Cup strip | Ruling 2 is given, but its caps block needs its own gate decided (plan §2); F-01 is now landed, so `ScenarioPanel.vue` is clean and the move is unblocked *technically* |
| Ruling 1 — capsule chrome for the page | owner |
| KI-155 / KI-156 / KI-157 | separate slices, out of scope here |
