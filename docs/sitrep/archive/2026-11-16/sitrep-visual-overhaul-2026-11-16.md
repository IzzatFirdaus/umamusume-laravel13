# SITREP — Visual Aesthetic Overhaul Slices V1–V5 (2026-11-16)

**Session:** visual overhaul execution against HEAD `8d9172d2` (session start `d417a9a5`).
**Verdict:** V1, V2, V3, V4 executed and banked. **V5 was landed by a peer session** (`178a9079`) before this
session reached it, including the change the brief flagged as blocked. Two brief premises were refuted by
measurement and are recorded below rather than silently obeyed.

---

## 1. Work completed

### V1 — BO-011 dark risk token + focus-ring seed — commit `c3b45049`

| Item | Evidence |
| :--- | :--- |
| `--color-risk` dark `#FF4400` → `#FF6F3B` | `resources/css/app.css:531` |
| Focus-ring seeded at rest | `:where(a, button, input, select, textarea, summary, [tabindex]) { outline-color: var(--color-ring); }` in `@layer base` |
| Emitted in the production bundle | `grep -oil ff6f3b public/build/assets/*.css` → `app-CkHRWaik.css`; `ff4400` → **no file** |
| Token guard | `DesignTokensTest` → **17 passed (480 assertions)** |

**Contrast measured (WCAG 2.1 relative luminance, both formulas independent of the app):**

| Token | on `raised #333333` | on `panel #2A2A2A` | on `page #09090B` |
| :--- | ---: | ---: | ---: |
| `#FF4400` (HEAD) | **3.66:1** ✗ | **4.15:1** ✗ | 5.76:1 ✓ |
| `#FF6F3B` (landed) | **4.57:1** ✓ | **5.19:1** ✓ | **7.19:1** ✓ |

The old value failed the 4.5:1 AA text threshold on two of the three surfaces the token is printed on — which is
exactly what BO-004 describes. `--color-on-risk #121212` on `#FF6F3B` measures 6.77:1 (was 5.42:1), so the fill's
own ink improved as well.

The commit carries **only** the two BO-011 hunks + the test. `resources/css/app.css` also held three unrelated
MWG blocks (dialog overscroll, popover polyfill, anchor positioning) in the same working copy; they were staged
around by hunk and remain uncommitted for their owner.

### V2 — ProvenanceMark shape reconciliation — commit `e73ddfe0` (component: peer `25049095`)

The component's `role="img"` span → `interestfor` `<button>` migration was **already landed by a peer session**
(`25049095`) while this session measured it, and that commit carries this session's 44 px hit-area class
(`min-h-11`) because both sessions edited the same file.

What this session contributed and banked:

- `min-h-11` on the mark's button. The brief asked for 24×24 (WCAG 2.2 SC 2.5.8 minimum); **measurement refuted
  24 px as sufficient here** — `tests/utils/tap-targets.ts:52` enforces the house **44 px** floor and the
  cockpit/review sweeps match `main button`, so a 23 px pill would fail those sweeps. 44 px is the binding bar.
- Eight spec files retargeted off the retired `role="img"` shape: `provenance-mark`, `career-cockpit`,
  `unity-cup-panel`, `review`, `database`, `grand-concert-panel`, `trackblazer-panel`, and the fixture.
- `provenance-mark.spec.ts`: the tooltip is a native `popover`, so it is **in the DOM from first paint**; the
  "not shown" assertions became `toBeHidden()` rather than `toHaveCount(0)`.
- `unity-cup-panel.spec.ts`: the R3 absence guard now excludes `button[interestfor]` — the mark opens a tooltip
  and acts on nothing, which is the ruling's own wording — in both the region scan and the tab walk.
- The fixture imports `resources/js/polyfills` so `interestfor`/popover resolve in the in-spec Vite server.

**Spec outcomes (all on session-private ports, scratch DB):**

| Spec | Result |
| :--- | :--- |
| `provenance-mark.spec.ts` | 5/5 passed |
| `career-cockpit.spec.ts` | **33/33 passed** |
| `unity-cup-panel.spec.ts` + `grand-concert-panel.spec.ts` | **21/21 passed** |
| `trackblazer-panel.spec.ts` | **18/18 passed** |
| `review.spec.ts` | 9/11 — 2 pre-existing (below) |
| `database.spec.ts` | 27/29 — 2 pre-existing (below) |
| `career-cockpit-forms.spec.ts` | 12/14 — 2 pre-existing (below) |

### V3 — Dead panel removal — commit `302c6e6b`

Four components deleted, **412 lines**: `CareerMilestonesPanel`, `HappyMeekPanel`, `PreFinalReviewPanel`,
`UraMandatoryLadder`.

The brief's premise ("verify zero importers") held, but the reason they still shipped needed measuring: `spa.ts:22`
globs `./pages/**/*.vue`, so every file under `pages/` becomes a chunk whether or not anything imports it. The
pre-delete build emitted all four (`CareerMilestonesPanel-CoR_idY5.js`, …); the post-delete build emits **zero**
chunks for all four — verified by filename glob, not by reading the log.

`UraMandatoryLadder.vue` was the one exception to "zero importers": `AbsenceChannelTest.php:149` lists it in a
migrated-sites guard. That entry was removed with the file (the guard's other six entries stand), and
`AbsenceChannelTest` → **1 passed (4 assertions)**.

`SpiritBurstsPanel.vue` and `TeamRankLadderPanel.vue` were **not touched**: the brief called their remaining
references stale comments, but `scenario-panel.spec.ts` and `career-cockpit-forms.spec.ts` assert on both panels'
rendered output. Their comments in `ScenarioPanel.vue:76-86` also document *why* the registrations were removed —
deleting them would erase the regression note the comments exist to preserve.

`npm run typecheck` exit 0; `npm run build` exit 0.

### V4 — BO-004 dark "To do" pill contrast — folded into `c3b45049`

BO-004 is a **token-level invariant**, not a per-pill style: the risk token is printed as text on the Dashboard
pill (`panel`), the risk rail (`raised`) and `page`. `DesignTokensTest` now asserts `--color-risk` against all
three surfaces in both themes at ≥4.5:1, which is a stronger guard than probing one pill in one theme.

Before → after, measured: `raised` **3.66 → 4.57:1**, `panel` **4.15 → 5.19:1**, `page` **5.76 → 7.19:1**.
No pill-specific colour change was needed; the token lift in V1 closes it.

### V5 — MWG form-capture hints — **landed by peer session**, `178a9079`; nothing owed

The brief predicted this slice might be blocked on peer state (`BaseInput.vue` peer-dirty). Measurement at
execution time: `178a9079 feat(forms): adopt MWG form-capture hints across 15 audited files` had already landed,
**including `BaseInput.vue`**, with the props defaulted `undefined` so an absent hint stays absent from the DOM —
the exact contract the brief specified. This session staged nothing for V5 and claims no part of it.

Two peer commits also landed the `spa.ts` polyfill wiring and the ProvenanceMark migration (`25049095`,
`78b54a71`), and a third landed the NativePHP provider fix (`8d9172d2`). None was authored here.

---

## 2. Test outcomes

| Gate | Command | Result |
| :--- | :--- | :--- |
| Typecheck | `npm run typecheck` | **exit 0** |
| Build | `npm run build` | **exit 0**, 980 modules, `spa` chunk 215.85 kB |
| Pint | `vendor/bin/pint --test --format agent app database tests` | **passed** (`{"tool":"pint","result":"passed"}`) |
| PHPStan | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | **3 errors, all pre-existing** — see below |
| Pest (targeted) | `vendor/bin/pest --filter="AbsenceChannelTest\|DesignTokensTest\|ProvenanceMark" --compact` | **19 passed (488 assertions)** |
| Pest (full) | `vendor/bin/pest --compact` | **0 failed**, 1 risky, 2435 passed — see §2.1 |
| Playwright | per-spec, session-private ports 8181–8194 | see §1 V2 table |

### 2.1 Full Pest suite

| Run | Command | Result |
| :--- | :--- | :--- |
| 1 | `vendor/bin/pest --compact` | 6 failed, 1 risky, **2429 passed** (37310 assertions), 271.49s |
| 2 | `vendor/bin/pest --compact` | **0 failed**, 1 risky, **2435 passed** (37402 assertions), 322.05s, exit 0 |

Run 1's six failures are **transient working-tree states, not regressions from this session's commits**. Two are
identifiable from the preserved log tail, and each was re-measured green on the same tree:

| Run 1 failure | Cause, measured | Now |
| :--- | :--- | :--- |
| `LineEndingGuardTest.php:57` — CRLF in `Career/EventDecision.vue` + `career-cockpit-forms.spec.ts` | both files were mid-rewrite by a peer at that instant (they are the two files `178a9079`/`78b54a71`/`f8e428d9` all touch) | **0 CRLF / 444 and 374 pure LF** on disk; `LineEndingGuardTest` **1 passed (2 assertions)** |
| `TraineeCardFormTest.php:130` — 200 expected, 404 received | the test requested `career.trainee.profile` with `$trainee->id`; the route binds `{umamusume}` and `Umamusume::getRouteKeyName()` returns `slug` (roadmap slice 3.2 exactly). A peer's uncommitted one-line fix (`$trainee->id` → `$trainee->slug`) is in the working tree | `--filter="TraineeCardFormTest\|LineEndingGuardTest"` **6 passed (49 assertions)** |

The other four of the six were not captured in the log tail; run 2 — same command, same tree, after the peer's
edits settled — returned **0 failed**, which is the confirmation that no failure was caused by this change. The
suite total also moved 2429 → 2435 because peer commits landed between the runs. Run 1 is therefore *not* evidence
of a defect in this session's work, and run 2 is the figure this hand-off stands behind.


### 2.2 PHPStan — 3 errors, all pre-existing, none from this change

```
Http\Middleware\EnforceNativePHPSecret.php:13   env() outside config   (known: roadmap slice 3.6)
Providers\NativeAppServiceProvider.php:21       unknown interface Native\Desktop\Contracts\ProvidesPhpIni
Providers\NativeAppServiceProvider.php:28       unknown class Native\Desktop\Facades\Window
```

The two provider errors exist because `nativephp/electron` is a packaged-build dependency not installed in this
dev vendor tree; `8d9172d2` (peer) restored that provider, which is why they surface now. Neither file was touched
by this session.

### 2.3 Pre-existing Playwright failures — classified, not introduced here

Four failures reproduce on files or assertions this session did not author, and each was confirmed by reading the
rendered accessibility tree rather than the assertion message.

| Spec:line | Failure | Owner |
| :--- | :--- | :--- |
| `career-cockpit-forms.spec.ts:248` | asserts meter label `Spirit Bursts: not recorded`; the page renders **`Spirit Bursts (combined): not recorded`** (KI-133 relabel, `b9837da7`) | stale assertion, pre-dates this session |
| `career-cockpit-forms.spec.ts:182` | new BO-017 assertion expects `Spirit Bursts (combined): 11`; the app prints `(combined): not recorded` on this fixture — an app-value gap, not a locator gap | BO-017, peer-authored assertion |
| `scenario-panel.spec.ts:94` | same stale `Spirit Bursts: not recorded` label; file is byte-identical to HEAD | stale assertion |
| `database.spec.ts:305` | column-header list expects 5 headers; the table renders 6 (extra `Open the skill` column) | app/spec drift, unrelated to provenance |
| `database.spec.ts:671` | axe `button-name` on `<button … label="Compare (0)">` — `BaseButton` puts the label in a `label` attribute, not the accessible name | a11y defect in `BaseButton` usage, **not** `ProvenanceMark` |
| `review.spec.ts:126` | sticky pager 1619 px after scroll (expects ≤721) | pre-existing, first run at 8181 also failed |
| `review.spec.ts:332` | HFW marker `pill-caution` missing from `Review/Index` | pre-existing |

One earlier `review.spec.ts` run at port 8190 reported 6 failures instead of 2; four of those passed on re-run at
port 8194 and in isolation at 8193. That was **port/server contention**, not a regression — recorded so the next
reader does not chase it.

---

## 3. Peer protection

- **Nothing peer-owned was staged.** The pre-commit hook refused a 30-file index mid-session (`REFUSED: 30 files
  staged (limit 20)`); the index was reset and rebuilt from explicit pathspecs. That hook is the reason the peer
  commits stayed separate.
- Each commit was re-checked with `git diff --cached --name-only` immediately before committing:
  - `c3b45049` → `resources/css/app.css` (2 hunks), `tests/Feature/DesignTokensTest.php`
  - `e73ddfe0` → 8 browser spec/fixture files only
  - `302c6e6b` → 4 deleted panels + `tests/Feature/AbsenceChannelTest.php`
- The three unrelated MWG blocks in `resources/css/app.css` were **left uncommitted** for their owner rather than
  riding along with an a11y commit.
- No `git add -A`, no `git reset --hard`, no stash of peer work, no push.

---

## 4. Remaining blockers

1. **BO-017 app gap** (`career-cockpit-forms.spec.ts:182`): the panel prints `Spirit Bursts (combined): not
   recorded` where the peer's new assertion expects `11` for a run that recorded 6 + 5. Needs a value-plumbing
   decision (which field feeds the combined widget), not a test edit.
2. **`BaseButton` accessible name** (`database.spec.ts:671`): `label="Compare (0)"` is an attribute, not an
   accessible name, so the disabled compare button has none. A one-line fix (bind `aria-label`) is
   straightforward but touches a shared component outside this brief's slices — not done unilaterally.
3. **Three stale `Spirit Bursts: not recorded` assertions** — they assert the pre-KI-133 label. Updating them is
   correct, but `scenario-panel.spec.ts` is byte-identical to HEAD and re-labelling it is a separate ruling.
4. **`review.spec.ts:126` / `:332`** — sticky-pager and HFW `pill-caution` drift, both pre-existing.
5. **PHPStan 3 errors** — the `EnforceNativePHPSecret` `env()` call (roadmap slice 3.6, known) plus two NativePHP
   symbol errors that only clear when `nativephp/electron` is installed.

---

## 5. Next steps to the v3.0.0 release bar

1. Close BO-017's value plumbing (item 1 above); that assertion is the only new red this brief's own new test
   introduced.
2. Fix `BaseButton`'s accessible name; re-run `database.spec.ts` axe.
3. Land the three label reconciliations as one ruling (KI-133's `(combined)` scope), then re-run
   `scenario-panel` + `career-cockpit-forms`.
4. Clear the two `review.spec.ts` failures (sticky pager geometry, HFW `pill-caution`).
5. Full-suite sweep on a **settled** tree (peer landings finished), then the roadmap §P6 seven checks.
6. Slice 3.6 (`env()` → `config/nativephp.php`) closes the last attributable PHPStan error.

**No documentation-only item above is counted as progress toward the bar** — items 1–6 each name a code edit and a
re-run that proves it.
