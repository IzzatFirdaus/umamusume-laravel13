# Modern Web Guidance — First-Pass Audit Report

**Scope:** Web-facing surface of Trainer Desk: `resources/js/**` (Vue 3 SFCs, composables, layouts), `resources/css/{app,layout-grid}.css`, `resources/views/app.blade.php` and error shells, `public/`. Backend (Laravel/PHP), tests, and docs excluded. Audit read-only; no source files changed.

**Method:** `modern-web-guidance` `legacy-scan` over `resources`, `resources/views`, `public` (35 deterministic rules / 33 guides, ~20% of corpus) plus targeted `search`/`retrieve` for dialogs, HTML semantics, forms, performance, and interest-triggered tooltips, compared against retrieved guides.

**legacy-scan result:** 1 finding in `resources` (IME-unsafe Enter), 0 in `resources/views`, 0 in `public`.

## Findings

| # | File:line | Current pattern | MWG-recommended pattern | Rationale (guide) | Remediation |
|---|---|---|---|---|---|
| 1 | `resources/js/pages/Career/TrainingDetail.vue:475` | `if (event.key === 'Enter')` triggers `recordTurn()` without IME-composition gating | Check `event.isComposing` / `event.keyCode === 229` before treating Enter as submit | `ime-safe-enter-submit` (forms §7) | Add guard `if (event.isComposing \|\| event.keyCode === 229) return;` before the Enter branch |
| 2 | `resources/js/pages/Runs/Index.vue:114-119` | `<form role="search" aria-label="Filter careers">` for search/filtering | Wrap search/filtering controls in a `<search>` landmark element | `html` §1, `forms` §1 | Wrap the form in `<search>…</search>` (or replace `role="search"` with the element) |
| 3 | `resources/js/components/ProvenanceMark.vue` (template, hover/focus tooltip span) | Custom JS-toggled `role="tooltip"` span on `mouseenter`/`focus`; closes on `mouseleave`/`blur` only | Native Popover API `popover="hint"` + `interestfor` on trigger; Esc dismiss, hoverable content, light-dismiss | `interest-triggered-tooltips` (WCAG 1.4.13) | Migrate to `popover="hint"` + `interestfor`; add `interestfor` polyfill (Chrome 149, limited availability) and ARIA wiring script. Or, minimally, add Escape dismiss + hoverable behavior to current implementation |
| 4 | `resources/js/components/ArtworkSlot.vue:85,91` | `loading="lazy"` on every image, including above-the-fold/detail-header slots | Do not lazy-load above-the-fold/LCP images; use eager (default) for visible slots; `fetchpriority="high"` for LCP candidate | `performance` (LCP), `html` §3 | Add an `eager`/`priority` prop (or infer from slot context) so above-the-fold slots are eager; reserve lazy for list rows |
| 5 | `resources/js/components/ArtworkSlot.vue:85-93` | No `width`/`height` attributes; geometry from Tailwind `size-*` classes | Specify intrinsic dimensions to reserve aspect ratio and prevent CLS | `html` §3, `performance` (images) | Intentional per DESIGN.md §4.7 (Tailwind box reserves both edges). Verify CLS in practice; if any shift, add matching `width`/`height` (or CSS aspect-ratio) without changing geometry |
| 6 | `resources/js/components/career/RaceCard.vue`, `support/CardPicker.vue`, `pages/Legacy/Builder.vue` (dialog elements) | Modal `<dialog>` opened via `showModal()` with no overscroll containment | `dialog:modal { overscroll-behavior: contain; }` and `dialog::backdrop { overflow: hidden; overscroll-behavior: contain; }` | `dialog` (scroll containment) | Add the two CSS rules to `resources/css/app.css` |
| 7 | `resources/js/components/Form/BaseInput.vue` (props/template) | No `autocorrect`, `autocapitalize`, `spellcheck`, `enterkeyhint`, `inputmode` props | Expose these attributes for mobile keyboard/IME UX | `forms` §3 | Add optional props and bind them (default off for search/identifier fields) |
| 8 | `resources/js/pages/Runs/Index.vue:114` (and any filter form) | No `<fieldset>`/`<legend>` grouping of filter controls | Group related controls with `<fieldset>` + `<legend>` | `forms` §1 | Wrap the filter selects in a `<fieldset>` with a visually-hidden `<legend>` where appropriate |
| 9 | `resources/js/components/career/RaceCard.vue` dialog (and peers) | No `closedby` attribute; only Esc closes (no click-outside) | Choose `closedby` deliberately: `any` for light dismiss, with Safari fallback | `dialog` (dismissal) | Optional UX decision: add `closedby="any"` where light dismiss is desired, plus the documented pointer-position fallback for browsers without `closedby` |
| 10 | `resources/css/app.css` / long list pages (Database, Veterans) | No `content-visibility: auto` on off-screen sections | Use `content-visibility: auto` + `contain-intrinsic-size` on long off-screen sections | `performance` (CSS containment) | Optional: evaluate page lengths; apply only where lists are long enough to benefit |

## Features where a fallback is currently absent (per MWG)

- **IME-safe Enter (F1):** no fallback needed; fix is a one-line guard.
- **`<search>` landmark (F2):** no fallback; element is Baseline.
- **Popover/interest (F3):** `interestfor` is Limited (Chrome/Edge 149 only); requires `interestfor` polyfill and `@oddbird/popover-polyfill` for `popover` where unsupported. Current custom implementation has no polyfill but is universally supported.
- **Overscroll containment (F6):** `overscroll-behavior` is Limited (Chrome 144+/Firefox 150, not Safari); guide says treat as progressive enhancement, no fallback to `overflow: hidden` on body.
- **`closedby` (F9):** Limited (Chrome 134+/Firefox 141, not Safari); Esc already closes modals without it.
- **Anchor positioning (if migrating tooltips):** Limited; requires `@oddbird/css-anchor-positioning` polyfill or `@supports` gating.

## Not flagged (already aligned with MWG)

- Native `<dialog>` + `showModal()` for modals (not `show()`/`open` attr) — aligned.
- Native `<select>`/`<details>`/`<summary>` disclosures — aligned.
- Container queries and `:has()` in CSS — aligned.
- `prefers-reduced-motion` block in `app.css` — aligned.
- Theme pre-paint script in `app.blade.php` — aligned.
- ArtworkSlot uses `loading="lazy"` + `decoding="async"` for list rows — aligned for below-fold.
- Combobox uses `aria-autocomplete="list"`, `aria-expanded`, `aria-activedescendant` — aligned.

## Stop

Audit complete. No source files were modified. Awaiting approval before any remediation phase.

---

## Dated erratum — 2026-11-18

The findings table above is the record of the audit as written. Three of its ten rows describe patterns that the tree has since changed, and grep over `resources/` on 2026-11-18 measures them as remediated. The original sentences are not rewritten.

| Row | Row claimed | Measured 2026-11-18 |
|---|---|---|
| 1 | `TrainingDetail.vue:475` triggers Enter without IME gating | **Remediated.** `resources/js/pages/Career/TrainingDetail.vue:479` guards with `event.isComposing || event.keyCode === 229`. |
| 4 | `ArtworkSlot.vue:85,91` lazy-loads every image | **Remediated.** The component gained an `eager` prop; both `<img>` branches bind `loading`/`fetchpriority` from it (`ArtworkSlot.vue:91,98`). |
| 6 | Dialogs have no overscroll containment | **Remediated.** `resources/css/app.css:2077-2084` carries `dialog:modal { overscroll-behavior: contain }` and the `::backdrop` rule. |

Two further rows closed without code, deliberately: row 5 (intrinsic `width`/`height`) rests on the recorded `DESIGN.md` §4.7 decision, and row 10 (`content-visibility`) was filed optional and no measured problem motivates it.

Row 9 is **not** closed. `closedby="any"` shipped on two dialogs (`RaceCard.vue:242`, `CardPicker.vue:181`) without the fallback this audit's own guide marks MANDATORY. Dated detail, verified compatibility data and the two closing options are in `docs/audits/mwg-audit/mwg-verified-research-2026-11-18.md` §5.

`legacy-scan resources --json` reports `totalFindings: 0` across 35 rules and 33 guides at this date.
