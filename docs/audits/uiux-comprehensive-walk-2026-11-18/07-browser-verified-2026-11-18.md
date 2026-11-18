# Browser-verified findings and fixes, 2026-11-18 (second pass)

This pass drove the running application at `http://127.0.0.1:8000` in Chromium through CDP, read the rendered DOM, took screenshots, and re-verified every change in the browser afterwards. It reports what was measured, not what the source implied.

## How to read the provenance labels

- **measured** — read out of a live browser session (geometry, `textContent`, `document.activeElement`, computed styles, request counts).
- **static-read** — read from source at the stated `path:line` without browser confirmation.
- **inferred** — a conclusion drawn from the two above; may be wrong.

## Application state used

`php artisan serve` on `127.0.0.1:8000`, serving `public/build` (`manifest.json` newer than `resources/js`, no `public/hot`, `INERTIA_SSR_ENABLED=false`, so the KI-76 trap does not apply). Browser sweeps ran at 1440x900, 764x429, 390x844 and 320x900.

## Findings, fixes and their verification

### F-1 `1of 72` — the Turn tile's suffix had no separator in the accessible text (measured)

- **Measured before:** `document.querySelector('[data-tile="turn"] [data-tile-value]').textContent` returned `"1of 72"`, and the tile's own `textContent` returned `"Turn1of 72"`. The gap on screen came only from `ml-1` on the suffix `<span>`, so a screen reader announced the figure and the bound as one run.
- **Fix:** `resources/js/components/ui/Tile.vue` renders `{{ ' ' + suffix }}`. An HTML `&#32;` entity was tried first and **failed** — Vue's whitespace handling dropped it, and the built chunk still showed `t(e.suffix)` with no leading space (`static-read` of `public/build/assets/Tile...js`, then re-measured).
- **Measured after:** `"1 of 72"`.
- **Spec that pinned the defect:** `tests/browser/dashboard.spec.ts:275` asserted `/^\d+of \d+$/`, which made the missing space the expectation. Updated to `/^\d+ of \d+$/` with the reason in the comment.
- **New guard:** `tests/browser/browser-walk-2026-11-18.spec.ts` case 1.

### F-2 Scenario cards were 4% clickable (measured)

- **Measured before:** a 3x4 probe grid over one `[data-scenario-card]` found **1 of 12** points resolving inside the card's only interactive element. That element was the `<label>` on the heading row: 106x44 inside a 561x216 card, **4% coverage**. The card carried the border, the `is-chosen` tint and the "✓ Selected" word, so it presented as one selectable option while the summary, tags and padding were inert.
- **Why the obvious fix is wrong:** widening the `<label>` to wrap the card would pull the summary, tags and status word into the radio's accessible name. The component's own comments record that constraint at `ScenarioSelect.vue:257-264`.
- **Fix:** the card takes a click handler (`ScenarioSelect.vue`, `onCardClick`) that selects unless the event originated inside `input, a, button, select, textarea, details, summary, label` — so the input's own activation is not paid for twice, and a future link or disclosure inside a card keeps its behaviour.
- **Measured after:** **12 of 12** probe points resolve inside the card, `cursor: pointer`, and clicking the summary paragraph produced `is-chosen` plus "✓ Selected" with **0 PUT requests counted** by an instrumented `fetch` (the earlier count was 0 because Inertia's router does not go through `window.fetch`; the selection was confirmed instead by the persisted state after reload, which still read `URA Finale`).
- **New guard:** `browser-walk-2026-11-18.spec.ts` case 2.

### F-3 Preflight's disabled `Create the run` had no explanation at the point of use (measured)

- **Measured:** at 1440x900 the button sat at `top: 173`; the `#confirmed` checkbox that enables it sat at `top: 1125` — **952 px away, below the fold**. No sentence near the button named the acknowledgement. The page's own copy states "A warning is not a block", so a dead primary action with no visible reason is a genuine dead end (WCAG 3.3.1).
- **Fix:** `resources/js/pages/Career/Preflight.vue` renders `[data-ack-hint]` — "Tick the confirmation below to enable Create the run." — in the head band beside the button.
- **Two constraints found while fixing it, both measured or statically confirmed:**
  1. `tests/browser/wizard-actions.spec.ts:73-78` asserts the head band holds **exactly two** `a, button` doors, with the forward one last. An anchor inside the hint would have broken that invariant, so the hint is plain text.
  2. The hint is gated on `canStart`, because when a required step is missing the page already prints `startBlocker`'s sentence ("A scenario and a trainee are not entered", asserted at `career-preflight.spec.ts:336`). Gating avoids two competing explanations.
- **Measured after:** hint present with `canStart` true and unticked; `|hint.top - button.top|` under 120 px; ticking the box removes the hint.
- **Measured absent-when-correct:** on a fresh draft (no scenario, no trainee) `[data-ack-hint]` count is 0, so the empty state is unchanged.
- **New guard:** `browser-walk-2026-11-18.spec.ts` case 3.

### F-4 KI-166 was fixed only for the direct-child shape; `/legacy` still overflowed (measured)

- **Measured before:** `/legacy` at 390 px reported `documentElement.scrollWidth - clientWidth = 41`. The culprit was the same class as KI-166: an `sr-only` span (`position: absolute`) inside a table cell reporting `right: 431` against a 390 px viewport, escaping a `div.overflow-x-auto` that was `position: static` (the scroller itself measured correctly at `clientWidth 324 / scrollWidth 406`, so it was not the scroller's fault).
- **Why the existing fix missed it:** `resources/css/app.css` fixed `.card:has(> table.t)`, which requires the table to be a **direct child** of the card. `Legacy/Index.vue:73` nests it one level deeper.
- **Fix:** `:where(.card, [class*="overflow-x-auto"]):has(table) { position: relative; }` — closes the class rather than the instance. 27 of 31 `overflow-x-auto`-with-table wrappers had no `position: relative` (`static-read` inventory); four had been patched individually and stay correct.
- **Measured after:** `/legacy` overflow **0**, and the wrapper still scrolls (`scrollWidth > clientWidth`).
- **Sweep:** 48 routes at 390 px, **0** with document overflow (the pre-fix sweep found 1). 54 routes at 1440 px, 0 console errors, 0 failed requests, exactly one `h1` each.

## Findings that were investigated and cleared (not defects)

| Claim | Verdict | Measurement |
|---|---|---|
| Vision review: "fixed bottom nav clips the CTA / content needs bottom padding" | **Refuted** | At 390 px the nav is 45 px tall at `top: 799`; `main` already reserves `padding-bottom: 96px`; at full scroll the last content box ends at 641, above the nav. At 1440 px there is no fixed nav and `elementFromPoint` on the CTA centre returns the `<a>` itself (`hitIsSelf: true`). |
| Vision review: "empty cockpit is a dead end with no CTA" | **Refuted** | The empty state carries "Record the first turn through Training" as a real link, plus the section tabs and the next-action doors. `mainInteractive` enumerated them from the DOM. |
| Vision review: "`Verified 2026-11-02` is a future date" | **Refuted** | The audit date is 2026-11-18, so 2026-11-02 is 16 days in the past. |
| Vision review: "Dashboard states the same absence 4x" | **Refuted as designed** | `Tile.vue`'s docblock and `DESIGN.md` §5 require the visible sentence beside an absent tile: a `title` alone is unreadable on touch and invisible in print. The tile's `hint` and the card's prose are deliberate pairs, not duplication. |
| `/trainees` and `/umamusume/1` returning 404 | **My error** | Neither is a route (`/umamusume` takes a slug; there is no `/trainees` route). The 404 page itself rendered correctly with one `h1`. |

## Pre-existing failure found, recorded, not fixed

`tests/browser/career-scenario-select.spec.ts:147` (`selects a scenario by keyboard and keeps it after a round trip`) fails at `expect(trackblazer).toBeFocused()`. It **fails identically with my working-tree changes reverted to `HEAD`** and the build rebuilt, so it is not a regression from this work. Filed as **KI-167** in `KNOWN-ISSUES.md` with the four proving commands and an explicit statement that the root cause is not established.

## Gates run (output, not assertion)

| Gate | Command | Result |
|---|---|---|
| Build | `npm run build` | exit 0, `✓ built in 30.33s` |
| Types | `npm run typecheck` | exit 0 (`vue-tsgo` + `tsc --noEmit`) |
| Types (specs) | `npx tsc --noEmit` | exit 0 |
| UI lint | `npm run lint:ui` | **516** findings, unchanged from the pre-session baseline (no new debt) |
| Wizard head band + preflight | `playwright test wizard-actions career-preflight career-preflight-indep` | **26 passed** |
| Dashboard, scenario, wizard flow, axe | `playwright test dashboard career-scenario-select wizard-flow accessibility` | **29 passed, 1 failed** (KI-167 only) |
| New pinning spec | `playwright test browser-walk-2026-11-18` | **3 passed** |

Every Playwright run used a session-private database (`.scratch-uma/audit-2026-11-18/browser-84NN.sqlite`, `targets canonical: no`) and its own port.

## Limitations of this pass

- **No human usability testing.** No representative user was observed, so learnability, memorisation and satisfaction are **not** measured; only mechanism, state and geometry were.
- **Vision analysis is unreliable here.** The model produced four confident claims about the dashboard that measurement refuted (table above), so its verdicts are treated as leads to measure, never as findings. Two earlier calls also failed with HTTP 429.
- **Safari and Firefox were not driven.** All compatibility statements about non-Chromium engines are source-derived.
- **`/legacy` is the only route whose pre-fix overflow was re-measured after the CSS fix per-route.** The other 47 were swept post-fix only.
- **KI-167 remains open.** Its root cause is unestablished and the suite carries one red case because of it.
- No commit was made; nothing was staged.
