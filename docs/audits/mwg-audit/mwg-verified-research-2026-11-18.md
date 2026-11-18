# Modern Web Guidance in this repository — verified research and compliance review

**Date:** 2026-11-18
**Subject:** what Modern Web Guidance (MWG) is, what this repository installed and applied, and whether the applied patterns meet the stated browser-support policy.
**Method:** repository inspection (read-only) plus web verification against primary sources. Every load-bearing claim carries a source and an access date of 2026-11-18. Claims that could not be verified are labelled **UNVERIFIED**.

---

## 1. What MWG is

MWG is an agent-skill corpus published by Google Chrome, distributed on npm as `modern-web-guidance` and on GitHub as `GoogleChrome/modern-web-guidance`. It ships roughly 200 topic guides (forms, CSS, HTML, performance, accessibility, security, UI components and behaviours) plus a search/retrieve CLI and a curated `legacy-scan` that covers about 20% of the corpus with deterministic rules.

The design premise, stated by the publisher: coding agents either write obsolete JavaScript that re-implements platform features, or adopt new features without feature detection. MWG pairs every recommendation with Baseline support data and a fallback pattern, and its evaluation reports fallback-check pass rates rising from 43% unguided to 87% guided ([S1]).

**Version facts (measured this pass):**

| Item | Value | How measured |
|---|---|---|
| Corpus installed in this repo | skill-version `2026_11_09-5bcc021b`, 208 guide entries | `.agents/skills/modern-web-guidance/skill-version.txt`, `gen/guides.json` |
| Installed package wrapper | `package.json` version `1.0.0` | `.agents/skills/modern-web-guidance/package.json` |
| Latest on npm | `1.0.1` | `npm view modern-web-guidance version` |
| Installer | `npx modern-web-guidance@latest install` / `update` | [S1] |

The installed corpus therefore predates the published package by one patch release. **UNVERIFIED:** whether `1.0.1` changes any guide this repository depends on; that requires a diff of the two tarballs and was not performed.

---

## 2. What this repository installed

MWG is vendored into six agent-config directories, all gitignored scaffolding: `.agents/`, `.claude/`, `.crush/`, `.hermes/`, `.kiro/`, `.qoder/`. Only `.agents/skills/modern-web-guidance` plus `.ai/` are tracked instruction sources per `AGENTS.md` §11. Each copy carries `modern-web.mjs`, `search.mjs`, `legacy-scan.mjs` (~5 MB of scan rules), the guide corpus and the vector artefacts used by semantic search.

`AGENTS.md` §13 states the repository's browser policy:

> Browser baseline: `Baseline 2024` (web-platform-dx). A platform feature that shipped in every core browser by the end of 2024 is usable without a polyfill; the `modern-web-guidance` guides assume it.

That policy matters for §5: it is **narrower** than MWG's default. MWG assumes *Baseline Widely available* is safe and demands fallbacks for *Newly available* and *Limited availability* features ([S1], [S2]). The repo's policy allows anything that reached every core browser during 2024 without a polyfill.

---

## 3. What was applied, and where

A prior MWG audit exists at `docs/audits/mwg-audit/mwg-audit-report.md` (10 findings, read-only). Re-measuring the tree this pass, **seven of its ten findings are already remediated** and three were deliberate non-actions. Current state, measured by grep over `resources/`:

| Audit # | Pattern | Present now | Evidence |
|---|---|---|---|
| 1 | IME-safe Enter guard | Yes | `resources/js/pages/Career/TrainingDetail.vue:479` |
| 2 | `<search>` landmark | Yes | `resources/js/pages/Runs/Index.vue:119`; no `role="search"` remains |
| 3 | `popover="hint"` + `interestfor` tooltip | Yes | `resources/js/components/ProvenanceMark.vue:71,83` |
| 4 | LCP image eager + `fetchpriority` | Yes | `resources/js/components/ArtworkSlot.vue:91,98` |
| 5 | Intrinsic `width`/`height` on images | No, by design | `provenance` note in the audit row: Tailwind box reserves both edges |
| 6 | Dialog overscroll containment | Yes | `resources/css/app.css:2077-2084` |
| 7 | `inputmode` / `enterkeyhint` props | Yes | `resources/js/components/Form/BaseInput.vue:35-36,127-128,160` |
| 8 | `fieldset`/`legend` grouping | Yes | 10 sites, e.g. `resources/js/components/ui/SegmentedControl.vue:42` |
| 9 | `closedby` on dialogs | **Yes, without fallback** | `resources/js/components/career/RaceCard.vue:242`, `resources/js/components/support/CardPicker.vue:181` |
| 10 | `content-visibility: auto` | Not applied | 0 occurrences; the audit filed it as optional |

The prior audit's own table is therefore **stale on rows 1, 4 and 6**; it lists them as current patterns when the tree has already migrated. Its `## Stop` section ("awaiting approval before any remediation phase") no longer describes the tree either.

---

## 4. Verified compatibility facts

All six claims below were checked against MDN and, where MDN was terse, a second source.

| Feature | Verified status | Detail | Source |
|---|---|---|---|
| `<dialog>` element | **Baseline Widely available** | Across browsers since March 2022 | [S3] |
| `closedby` attribute | **Limited availability** | Chrome 134 (Mar 2025), Edge 134 (Mar 2025), Firefox 141 (Jul 2025); **not supported in Safari** | [S4], [S5], [S6] |
| Popover API (`popover` attr, `showPopover`) | **Baseline 2025, Newly available** | Across browsers since January 2025 | [S7] |
| `popover="hint"` | **Limited availability** | Chrome/Edge 151 (Jul 2026), Firefox 153; **Safari not supported**; in supporting-but-not-hint browsers the invalid value falls back to `manual` | [S8], [S9], [S6] |
| Interest invokers (`interestfor`) | **Limited availability** | Chrome/Edge 149 (Jun 2026); Firefox and Safari unsupported; conditional polyfill required | [S8] |
| `position-area` (anchor positioning) | **Baseline 2026, Newly available** | Works across latest browsers since January 2026 | [S10] |
| `overscroll-behavior` | **Limited availability** | "not Baseline because it does not work in some of the most widely-used browsers" | [S11] |
| `<search>` element | **Baseline Widely available** | Across browsers since October 2023 | [S12] |
| `KeyboardEvent.isComposing` | **Limited availability** per MDN | Supported in practice by all engines; MDN's compat table is why the guide pairs it with the `keyCode === 229` check | [S13] |

**The one genuine conflict in the sources, stated rather than smoothed:** MDN's `KeyboardEvent.isComposing` page carries a "Limited availability" banner ([S13]), while the MWG guide treats the `isComposing || keyCode === 229` pair as the portable check with no fallback needed. The repository follows the guide and uses both signals together, which satisfies either reading.

**Discrepancy in the installed corpus worth recording:** the installed `interest-triggered-tooltips` guide states `popover="hint"` is supported from "Chrome 151 (Jul 2026)" while its interest-invoker section states "Chrome 149 (Jun 2026)" for `interestfor`, and the web-platform-dx developer-signals issue lists `popover="hint"` from Chrome Android 133 (Feb 2025) ([S9]). Three different numbers for the same feature. None changes this repository's decision, because Safari is unsupported in all three readings.

---

## 5. Compliance finding: two applied patterns are outside this repo's own policy

The repo's policy is **Baseline 2024** (`AGENTS.md` §13). Against §4's verified data:

| Applied pattern | Baseline | Inside a Baseline 2024 policy? | Mandated fallback present? |
|---|---|---|---|
| `popover="hint"` + `interestfor` tooltip (`ProvenanceMark.vue`) | Limited availability; Safari unsupported | **No** | **Partially.** The `interestfor` polyfill is conditionally loaded and feature-detected (`resources/js/polyfills.js:18-20`), satisfying the guide's mandatory detection step [S8]. `popover` itself is polyfilled conditionally at `:14-16`. However `@oddbird/popover-polyfill` does **not** polyfill `hint` where native `popover` exists, and the guide's `beforetoggle` → `popover="auto"` toggletip correction [S8] is not implemented. It is not needed here: this mark is a hover/focus tooltip, not a click-opened toggletip, so the "must happen in `beforetoggle`" clause does not apply. |
| `closedby="any"` on two dialogs | Limited availability; Safari unsupported | **No** | **No.** The guide marks the fallback *MANDATORY*: "For browsers that do not yet support `closedby`, you **must** implement a fallback" with a `'closedBy' in HTMLDialogElement.prototype` feature check [S5]. Grep over `resources/js` finds `closedby` only as the two attributes, with no `closedBy` probe and no click-outside listener. |
| Dialog overscroll containment (`app.css:2074-2084`) | `overscroll-behavior` is Limited availability | No | **Correct by omission.** The comment already states it is progressive enhancement and that unsupported browsers simply ignore it; the guide accepts exactly that treatment [S11]. |

### 5.1 What the `closedby` gap actually costs

Both dialogs are opened with `showModal()` (`RaceCard.vue:85`, `CardPicker.vue:155`), and per [S3] a modal dialog with no valid `closedby` behaves as `"closerequest"`: Escape still closes it, and each dialog has a visible Close button (`RaceCard.vue:249-255`). So the practical loss is **backdrop-click dismissal only, in Safari**. It is a degraded interaction, not a trap: no user becomes unable to close the dialog. That is why this is recorded as a compliance gap rather than an accessibility failure.

### 5.2 Two ways to close it, with the trade-off

1. **Add the guide's fallback** (~15 lines, zero dependencies) inside each dialog component's `open()`: probe `'closedBy' in HTMLDialogElement.prototype` and, when absent, attach the documented click-outside listener that compares `event.target` and the dialog's `getBoundingClientRect()`. Costs one small duplicated helper (or one shared composable) and makes Safari match Chrome and Firefox.
2. **Record an explicit policy exception.** Narrow the `AGENTS.md` §13 sentence to say that progressive-enhancement-only features may exceed Baseline 2024 when their absence degrades rather than blocks, and list `closedby`, `popover="hint"` and `overscroll-behavior` as named exceptions.

Option 1 is the smaller change and the one MWG actually mandates. Option 2 is the honest move if the owner deliberately wants no light-dismiss in Safari.

---

## 6. What holds, verified

- `legacy-scan resources --json` now reports **0 findings across 35 rules / 33 guides**. The prior audit recorded 1 finding (IME-unsafe Enter), which is fixed at `TrainingDetail.vue:479`.
- Native `<dialog>` + `showModal()` rather than `show()`/`open` — correct per [S3].
- `<search>` replaces `role="search"` — correct per [S12], and the element is Baseline Widely available.
- `fetchpriority`/`loading` distinction in `ArtworkSlot.vue` matches [S14]: `loading` defaults to `eager`, and lazy-loading an above-the-fold image is the documented anti-pattern.
- Anchor-positioning rules are gated behind `@supports (position-area: block-start)` (`app.css:2097`), so browsers without native anchor positioning keep the UA's default popover placement rather than being stranded. `position-area` is Baseline 2026 [S10], so this gate is the correct treatment under either policy.
- The `popover` polyfill escape hatch uses `:is(:popover-open, .\:popover-open)` as the guide requires, because a bare `:popover-open` selector is discarded wholesale in browsers that lack it [S8].

---

## 7. Recommendations, ordered by cost

| # | Action | Cost | Why |
|---|---|---|---|
| 1 | Add the mandated `closedBy` feature-detect fallback to `RaceCard.vue` and `CardPicker.vue` | S | Closes the only *MANDATORY* gap; restores backdrop dismissal in Safari |
| 2 | Append a dated erratum to `docs/audits/mwg-audit/mwg-audit-report.md` marking rows 1, 4 and 6 remediated | S | The report currently tells a reader to fix three things that are already fixed |
| 3 | Amend `AGENTS.md` §13 with a named-exception clause for progressive-enhancement features | S | Brings the written policy and the shipped tree into agreement either way |
| 4 | Run `npx modern-web-guidance@latest update` and diff the corpus | M | Installed corpus is `2026_11_09-5bcc021b`; npm latest is `1.0.1` and three sources disagree on `popover="hint"` versions |
| 5 | Re-run `legacy-scan` in CI-equivalent local gate | S | It is deterministic and currently cheap; keeps the 0-findings baseline honest |

**Deliberately not recommended:** adopting `content-visibility: auto` (audit row 10). It was filed optional, no measured layout or INP problem motivates it, and it changes intrinsic sizing — a bad trade on a data-dense table application without evidence.

---

## 8. Limits of this review

- No browser was driven for this pass. The absence claims here are static (grep) plus publisher compatibility data; they are not rendered observations.
- I did not open the two dialogs in Safari. The Safari behaviour is asserted from MDN and the Chrome developer blog ([S4], [S5], [S6]), not measured on this machine. Treat "backdrop click does nothing in Safari" as source-derived, not observed.
- The npm `1.0.1` diff was not performed (§1).
- The `popover="hint"` Safari path was reasoned from the polyfill's own README (which states it "does not polyfill `popover="hint"`") and the guide's fallback text, not from a Safari run.

---

## Sources

All accessed **2026-11-18**.

- **[S1]** "Cross-browser by default: How Baseline fallbacks work in Modern Web Guidance", Tara Agyemang, Chrome for Developers, published 2026-11-12. https://developer.chrome.com/blog/cross-browser-by-default
- **[S2]** Modern Web Guidance skill instructions, `SKILL.md` (installed corpus `2026_11_09-5bcc021b`), §"Interpreting Browser Support & Fallbacks". `.agents/skills/modern-web-guidance/SKILL.md`
- **[S3]** "`<dialog>` HTML dialog element", MDN Web Docs, last modified 2026-11-10. https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/dialog
- **[S4]** "`closedby` attribute" within [S3] (same page, attribute table).
- **[S5]** MWG guide `light-dismiss-a-dialog`, installed corpus; fallback section quotes Chrome 134 / Edge 134 / Firefox 141 and "Unsupported in: Safari", with the MANDATORY `closedBy` probe. `.agents/skills/modern-web-guidance/guides/ui-behaviors/light-dismiss-a-dialog.md`; upstream https://github.com/GoogleChrome/modern-web-guidance/blob/main/skills/modern-web-guidance/guides/ui-behaviors/light-dismiss-a-dialog.md
- **[S6]** "Web features explorer — Limited availability", WebDX Community Group, listing `closedby` blocked by Safari since December 2025. https://web-platform-dx.github.io/web-features-explorer/limited-availability/
- **[S7]** "Popover API", MDN Web Docs, last modified 2025-12-17; page states Baseline 2025, newly available since January 2025. https://developer.mozilla.org/en-US/docs/Web/API/Popover_API
- **[S8]** MWG guide `interest-triggered-tooltips`, installed corpus; support table (Chrome 149 / Edge 149 for interest invokers; Chrome 151 / Edge 151 / Firefox 153 for `popover="hint"`; Safari unsupported in both), the `@oddbird/popover-polyfill` hint caveat, the `beforetoggle` toggletip correction and the `:is(:popover-open, .\:popover-open)` rule. `.agents/skills/modern-web-guidance/guides/ui-behaviors/interest-triggered-tooltips.md`
- **[S9]** "popover=hint", web-platform-dx developer-signals issue #274, listing Chrome Android 133 (2025-02-04), Firefox 149 (2026-03-24), Safari not supported. https://github.com/web-platform-dx/developer-signals/issues/274
- **[S10]** "position-area CSS property", MDN Web Docs, last modified 2026-09-14; page states Baseline 2026, newly available since January 2026. https://developer.mozilla.org/en-US/docs/Web/CSS/position-area
- **[S11]** "overscroll-behavior CSS property", MDN Web Docs, last modified 2026-10-13; page states "Limited availability … not Baseline because it does not work in some of the most widely-used browsers". https://developer.mozilla.org/en-US/docs/Web/CSS/overscroll-behavior
- **[S12]** "`<search>` HTML generic search element", MDN Web Docs, last modified 2026-10-14; Baseline Widely available since October 2023. https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/search
- **[S13]** "KeyboardEvent: isComposing property", MDN Web Docs, no stated modification date on the fetched page; carries a Limited availability banner. https://developer.mozilla.org/en-US/docs/Web/API/KeyboardEvent/isComposing
- **[S14]** "`<img>` HTML image embed element", MDN Web Docs, last modified 2026-09-11; documents `loading` defaulting to `eager`, the `lazy` + missing-dimensions reflow hazard, and `fetchpriority` values. https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/img
