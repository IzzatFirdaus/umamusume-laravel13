# UI/UX & Accessibility Reference Sheet — 2026-11-18

**Access date for every source below: 2026-11-18.** Citations like `[n]` point to the numbered register in §2; the register carries the exact title, publisher, URL and any date the page itself states. No fact in this sheet is recalled from memory; every load-bearing claim is tied to a fetched primary page. Where a page states no date, the register says "not stated on page" rather than inventing one.

**Purpose.** Cited reference for the parent agent's browser audit of Trainer Desk (local-only Laravel/Inertia/Vue 3/Tailwind v4 tool). Findings are phrased as implementation-impact notes for this app, not as generic advice.

---

## 1. Fast audit checklist (each line cites its source)

1. **Visible focus on every interactive control**, including custom Vue widgets; ring must not be time-limited and must meet 3:1 against adjacent colours. `[6][5]`
2. **Focus order = DOM order**; no positive `tabindex`, no CSS visual reordering of focusable items (flex/grid `order`, `row-reverse`), and test at every viewport with Tab/Shift+Tab. `[16]`
3. **Focused element never entirely hidden** by sticky headers/footers, toasts or non-modal overlays; prefer `scroll-padding` or displacement/reflow over overlap. `[7]`
4. **Targets at least 24x24 CSS px** or use the spacing exception (non-intersecting 24px-diameter circles); a 44px house target is stricter than AA, not the AA rule. `[9][5]`
5. **No drag-only actions** (reorder, sliders, calendar drag): add a single-pointer alternative such as tap-then-click, arrow buttons or a numeric input; keyboard equivalence alone is not enough. `[8]`
6. **Multi-step flows do not re-ask the same value**: auto-populate or offer it to select within the same process; browser autocomplete is explicitly not sufficient. `[11]`
7. **Repeated help/contact affordances sit in the same relative DOM order** across pages. `[10]`
8. **Modals**: focus moves in on open, Tab/Shift+Tab cycles inside, Escape closes, focus returns to the invoker; use `tabindex="-1"` on a static heading for long content; mark `aria-modal` only when the outside is genuinely inert *and* visually obscured. `[12]`
9. **Data tables**: native `<table>` + `<th>`; `aria-sort` on sorted headers; if cells contain many widgets, switch to the grid pattern to avoid a huge tab sequence. `[14]`
10. **Comboboxes**: declare editable vs select-only; use `aria-controls` (not legacy `aria-owns`); popup selection follows focus. `[13]`
11. **Disclosures**: real `button`, `aria-expanded`, optional `aria-controls`, Enter/Space toggle. `[15]`
12. **Forms**: visible label above each input, no placeholder-as-label; do not validate on blur; strip HTML5 validation (`novalidate`, no `required`); preserve entered values on error. `[24][21]`
13. **Errors**: always an error summary at top (even one error), keyboard focus moved to it, "There is a problem" heading, links to each field, wording identical to inline messages; prefix `<title>` with "Error: ". `[22][21]`
14. **Confirmation**: after a write, show a clear completion state plus what happens next and any reference/saved record; avoid interactive elements inside a high-contrast confirmation panel unless its focus state is fixed. `[23]`
15. **Reduced motion**: gate decorative/transitional animation on `prefers-reduced-motion`; preserve feedback-only motion where possible. `[17]`
16. **Focus styling via `:focus-visible`** is Baseline Widely available (since March 2022) — safe to use, but never suppress the indicator for keyboard users. `[18][16]`
17. **Target-size house rule**: 44px is Apple's *default* iOS/iPadOS control size and Material's *pointer* minimum; Material's touch minimum is 48x48dp. Treat 44px as a house target, not as WCAG AA. `[25][26][9]`

---

## 2. Source register (28 sources)

| # | Exact title | Publisher | URL | Date stated on page | Fetch |
|---|---|---|---|---|---|
| 1 | Web Content Accessibility Guidelines (WCAG) 2.2 (living Recommendation) | W3C / Accessibility Guidelines Working Group | https://www.w3.org/TR/WCAG22/ | Copyright 2020-2024; status section says "published ... as a Recommendation using the Recommendation track"; no explicit day | Fetched |
| 2 | Web Content Accessibility Guidelines (WCAG) 2.2 (dated Recommendation) | W3C / AGWG | https://www.w3.org/TR/2023/REC-WCAG22-20231005/ | Dated URL `...20231005`; copyright 2020-2023; body does not restate the day | Fetched |
| 3 | WCAG 2.2 is a Web Standard "W3C Recommendation" | W3C Web Accessibility Initiative (news) | https://www.w3.org/WAI/news/2023-10-05/wcag22rec | URL dated 2023-10-05; body gives no separate date | Fetched |
| 4 | Understanding Success Criterion 1.4.3: Contrast (Minimum) | W3C WAI | https://www.w3.org/WAI/WCAG22/Understanding/contrast-minimum.html | Not stated on page | Fetched |
| 5 | Understanding Success Criterion 1.4.11: Non-text Contrast | W3C WAI | https://www.w3.org/WAI/WCAG22/Understanding/non-text-contrast.html | Not stated on page | Fetched |
| 6 | Understanding Success Criterion 2.4.7: Focus Visible | W3C WAI | https://www.w3.org/WAI/WCAG22/Understanding/focus-visible.html | Not stated on page | Fetched |
| 7 | Understanding Success Criterion 2.4.11: Focus Not Obscured (Minimum) | W3C WAI | https://www.w3.org/WAI/WCAG22/Understanding/focus-not-obscured-minimum.html | Not stated on page | Fetched |
| 8 | Understanding Success Criterion 2.5.7: Dragging Movements | W3C WAI | https://www.w3.org/WAI/WCAG22/Understanding/dragging-movements.html | Not stated on page | Fetched |
| 9 | Understanding Success Criterion 2.5.8: Target Size (Minimum) | W3C WAI | https://www.w3.org/WAI/WCAG22/Understanding/target-size-minimum.html | Not stated on page | Fetched |
| 10 | Understanding Success Criterion 3.2.6: Consistent Help | W3C WAI | https://www.w3.org/WAI/WCAG22/Understanding/consistent-help.html | Not stated on page (errata noted) | Fetched |
| 11 | Understanding Success Criterion 3.3.7: Redundant Entry | W3C WAI | https://www.w3.org/WAI/WCAG22/Understanding/redundant-entry.html | Not stated on page | Fetched |
| 12 | Dialog (Modal) Pattern | W3C WAI — ARIA Authoring Practices Guide | https://www.w3.org/WAI/ARIA/apg/patterns/dialog-modal/ | Not stated on page | Fetched |
| 13 | Combobox Pattern | W3C WAI — ARIA APG | https://www.w3.org/WAI/ARIA/apg/patterns/combobox/ | Not stated on page | Fetched |
| 14 | Table Pattern | W3C WAI — ARIA APG | https://www.w3.org/WAI/ARIA/apg/patterns/table/ | Not stated on page | Fetched |
| 15 | Disclosure (Show/Hide) Pattern | W3C WAI — ARIA APG | https://www.w3.org/WAI/ARIA/apg/patterns/disclosure/ | Not stated on page | Fetched |
| 16 | Focus — Learn HTML | Google (web.dev) | https://web.dev/learn/html/focus | Last updated 2023-02-21 UTC | Fetched |
| 17 | prefers-reduced-motion: Sometimes less movement is more | Google (web.dev), author Thomas Steiner | https://web.dev/articles/prefers-reduced-motion | Last updated 2019-03-11 UTC | Fetched |
| 18 | :focus-visible CSS pseudo-class — CSS | MDN Web Docs (Mozilla/Contributors) | https://developer.mozilla.org/en-US/docs/Web/CSS/Reference/Selectors/:focus-visible | Last modified Oct 16, 2026 by MDN contributors | Fetched |
| 19 | 10 Usability Heuristics for User Interface Design | Nielsen Norman Group (Jakob Nielsen) | https://www.nngroup.com/articles/ten-usability-heuristics/ | April 24, 1994 · Last reviewed Jan. 30, 2024 | Fetched |
| 20 | 10 Design Guidelines for Reporting Errors in Forms | Nielsen Norman Group (Rachel Krause) | https://www.nngroup.com/articles/errors-forms-design-guidelines | February 3, 2019 · Last reviewed Dec. 12, 2024 | Fetched |
| 21 | Help users to Recover from validation errors (pattern) | GOV.UK Design System | https://design-system.service.gov.uk/patterns/validation/ | Not stated on page | Fetched |
| 22 | Error summary (component) | GOV.UK Design System | https://design-system.service.gov.uk/components/error-summary/ | Recent changes table: Jun 2026, Feb 2025, Jul 2024, May 2024, Nov 2022 | Fetched |
| 23 | Confirmation pages (pattern) | GOV.UK Design System | https://design-system.service.gov.uk/patterns/confirmation-pages/ | Not stated on page | Fetched |
| 24 | Text input (component) | GOV.UK Design System | https://design-system.service.gov.uk/components/text-input/ | Recent changes: Jun 2026 (v6.3.0), Mar 2026 (v6.1.0), Mar 2025, Oct 2024 | Fetched |
| 25 | Accessibility — Human Interface Guidelines | Apple Inc. | https://developer.apple.com/design/human-interface-guidelines/accessibility.md (markdown endpoint; HTML at https://developer.apple.com/design/human-interface-guidelines/accessibility is JS-gated) | Change log latest: June 9, 2025; footer copyright 2026 Apple Inc. | Fetched (markdown) |
| 26 | Accessibility designing — Material Design 3 | Google (Material Design 3) | https://m3.material.io/foundations/designing/structure | Not stated on page | Fetched |
| 27 | Baseline 2024: more tools to help web developers | Google (web.dev); Mariko Kosaka, Pete LePage, Rachel Andrew | https://web.dev/blog/baseline-project-2024 | Published Dec 10, 2024; last updated 2024-12-10 UTC | Fetched |
| 28 | Baseline | Google (web.dev) / WebDX Community Group | https://web.dev/baseline | No explicit page date; page describes Baseline 2026 as "latest and currently developing" | Fetched |

Access date for all 28: **2026-11-18**.

---

## 3. WCAG 2.2 status and the eight named criteria

### 3.1 Status

The current W3C page describes WCAG 2.2 as "published by the Accessibility Guidelines Working Group as a Recommendation using the Recommendation track" and states "W3C recommends the wide deployment of this specification as a standard for the Web". `[1]` W3C's own news item announces that WCAG 2.2 "is now a 'W3C Recommendation' web standard" and that it "adds 9 requirements (called success criteria) since WCAG 2.1"; the news URL is dated 2023-10-05 and the dated Recommendation URL is `REC-WCAG22-20231005`. `[3][2]` Conformance to 2.2 also conforms to 2.0 and 2.1, and publication of 2.2 does not deprecate the earlier versions — relevant if this app ever states a conformance target. `[1]`

### 3.2 Criteria relevant to a data-heavy tool

| Criterion | Verified level | Verified requirement (as stated by W3C) | Implementation impact for Trainer Desk |
|---|---|---|---|
| 1.4.3 Contrast (Minimum) | **AA** | Text at least 4.5:1; large-scale text at least 3:1 (18pt ~24px, or 14pt bold ~18.5px); computed ratios are thresholds, not rounded (4.499:1 fails). `[4]` | Audit `text-ink`/muted label tokens on table cells, badges and chart legends; do not rely on thin low-contrast text for unrecorded values (the app's `N/A` convention must still contrast). |
| 1.4.11 Non-text Contrast | **AA** | 3:1 against adjacent colours for UI components (including states such as focus/selection) and for graphics needed to understand content; thin anti-aliased lines may render fainter than the CSS colour. `[5]` | Input borders, focus rings, sort arrows, toggle states and chart series need at least 3:1 — not just text. `[18]` explicitly ties focus indicators to this criterion. |
| 2.4.7 Focus Visible | **AA** | Any keyboard-operable UI has a mode where the focus indicator is visible; the indicator must not be time-limited. Sufficient technique C45 is "Using CSS `:focus-visible` to provide keyboard focus indication". `[6]` | Never `outline: none` without a replacement; a Vue component that programmatically focuses must still show the ring. A 2020 W3C *blog draft* discussed promoting this to A, but the current Recommendation's Understanding page says **Level AA** — use AA. `[6]` |
| 2.4.11 Focus Not Obscured (Minimum) | **AA** (new in 2.2) | Focused component is **not entirely hidden** by author-created content; partial obscuring is permitted at AA. Named culprits: sticky footers, sticky headers, non-modal dialogs. `[7]` | Check sticky table headers/toolbars and non-modal review panels; W3C's own passing examples use `scroll-padding`, making the banner modal, or closing it on loss of focus. |
| 2.5.7 Dragging Movements | **AA** (new in 2.2) | All drag-operated functionality achievable with a **single pointer without dragging**, unless essential or user-agent-determined; keyboard equivalence alone does not satisfy it. `[8]` | Any drag-reorder or drag-to-adjust control needs tap-then-click, arrow buttons or a numeric field. W3C's examples explicitly include sortable lists and kanban boards. |
| 2.5.8 Target Size (Minimum) | **AA** (new in 2.2) | Targets at least 24x24 CSS px, or spacing exception (24px-diameter circles centred on each undersized target must not intersect), plus equivalent/inline/user-agent-control/essential exceptions. `[9]` | Dense schedule/calendar grids may rely on the spacing exception; W3C says 44x44 belongs to the stricter 2.5.5 Target Size (Enhanced) at AAA. `[9]` |
| 3.2.6 Consistent Help | **A** — *brief called it AA* | If help mechanisms (human contact details/mechanism, self-help, fully automated contact) are repeated across a set of pages, they occur in the **same relative order** to other content unless the user changes it; the criterion does not require providing help. `[10]` | If Trainer Desk repeats a help/docs link in its layout, keep it at the same DOM position across screens. |
| 3.3.7 Redundant Entry | **A** — *brief called it AA* | Information previously entered/provided in the **same process** must be auto-populated or available to select; exceptions: essential, security, no longer valid. Browser autocomplete is explicitly **not** sufficient. `[11]` | Multi-step training-run/import wizards must carry values forward; on a validation error, do not clear submitted fields. |

**Correction to the task brief:** 3.2.6 and 3.3.7 are Level **A**, not AA; 2.4.7 is AA in the current Understanding page. `[10][11][6]`

---

## 4. WAI-ARIA APG patterns and focus management

- **Dialog (modal)**: content outside is inert; the dialog contains its own tab sequence — Tab/Shift+Tab wrap inside, Escape closes; on open, focus moves to an element inside (often the first focusable, but for long/semantic content put `tabindex="-1"` on a static heading at the top and focus that, and omit `aria-describedby` on the container). On close, focus returns to the invoking element (or a logical alternative if it no longer exists). For an irreversible final step, focus the least destructive action. Mark `aria-modal="true"` **only** when application code blocks all outside interaction *and* visual styling obscures the outside. `[12]`
- **Combobox**: an input with an associated popup (listbox, grid, tree or dialog); select-only vs editable; four autocomplete behaviours (none, list with manual selection, list with automatic selection, both); selection follows focus in the popup; use `aria-controls` rather than legacy `aria-owns`. `[13]`
- **Table**: a *static* tabular structure — cells are not focusable or selectable; native HTML `<table>` is strongly preferred; use the **grid** pattern for an interactive widget. Each widget in a table is a separate tab stop, so a large number of widgets argues for switching to grid. `aria-sort` goes on the header cell of a sorted column; `aria-labelledby`/`aria-label` names the table. `[14]`
- **Disclosure**: a `button` toggles a content region; `aria-expanded` is `true`/`false` with optional `aria-controls`; Enter and Space both toggle. `[15]`

---

## 5. web.dev, MDN and Baseline

- **Focus order**: by default navigation focus order equals visual order, which equals source order; changing tab order with HTML attributes or visual order with CSS harms UX — "Don't reorder focusable elements with CSS." `flex-flow: row-reverse`, `order`, transforms and multi-column are named as divergence risks; test with Tab and Shift+Tab at every viewport. `tabindex="-1"` is focusable but not tabbable; `tabindex="0"` joins source order; values of 1 or more enter a prioritised sequence and should be avoided. `[16]`
- **Reduced motion**: `prefers-reduced-motion` detects whether the user asked the OS to minimise animation; the article distinguishes feedback motion, perception-hacking motion and decorative motion, notes vestibular reactions (dizziness, nausea, migraine) and calls reduction a medical necessity for some users. `[17]`
- **`:focus-visible`**: applies when an element matches `:focus` *and* the UA decides via heuristics that focus should be evident; intended for modality-specific focus styling (keyboard vs pointer) without changing *when* the indicator appears. MDN's accessibility section repeats the 3:1 focus-indicator requirement (citing WCAG 2.1 SC 1.4.11) and warns that removing focus styles makes keyboard navigation inaccessible. Baseline badge: **Widely available, across browsers since March 2022**. `[18]`
- **Baseline**: labels features supported by all core browsers on desktop and mobile (Chrome, Edge, Firefox, Safari); originated by the Chrome team, now defined by the **W3C WebDX Community Group**. Two stages: **Newly available** (all core browsers interoperable) and **Widely available** (**30 months** after the newly-interoperable date); before that, Limited. `[28]` The 2024 retrospective (published 2024-12-10) reports `web-features` at 81% of keys mapped and notes Interop 2024 brought Declarative Shadow DOM, `font-size-adjust`, `requestVideoFrameCallback` and `text-wrap: balance` into Baseline 2024. `[27]` Practical use: gate new CSS/JS on "Baseline Widely available" if the tool must run in any current browser; "Newly available" needs a fallback decision. `[28]`

---

## 6. Nielsen Norman Group

- **Ten heuristics** (1994-04-24, reviewed 2024-01-30). Directly actionable here: **#1 Visibility of system status** — "no action with consequences to users should be taken without informing them" and feedback should be immediate; **#3 User control and freedom** — support Undo/Redo and a clearly labelled Cancel; **#5 Error prevention** — "the best designs carefully prevent problems from occurring in the first place", distinguish slips (inattention) from mistakes (mental-model mismatch), use constraints/good defaults, remove memory burdens, support undo and warn before commitment. `[19]`
- **Form error guidelines** (2019-02-03, reviewed 2024-12-12). Prefer **inline validation** as soon as a field is finished; where server verification is required, make errors "clear, actionable, and easy to locate" after reload; keep messages **next to the field** to minimise working-memory load; do not overuse success indicators; if a user hits the same error **3 or more times**, treat it as a design defect and add extra help. `[20]`

---

## 7. GOV.UK Design System

- **Validation**: check validity, then show what went wrong and how to fix it; **turn off HTML5 validation** (`novalidate`; do not add `required`) because its styling/messages cannot be made consistent; on error, redisplay the form with values preserved, prefix the page `<title>` with "Error: ", show an error summary at the top and move keyboard focus to it, and show inline error messages; **do not validate when the user leaves a field** — wait until they try to continue. `[21]`
- **Error summary**: "Always show an error summary when there is a validation error, even if there's only one." Must move keyboard focus to it (the GOV.UK frontend JS does this), include heading "There is a problem", link to each errored answer, and use **identical wording** to the inline messages. Change history includes Jun 2026 v6.2.0 (fixed CSS outputting list code twice) and Feb 2025 (clarified automatic focus move). `[22]`
- **Confirmation pages**: use at the end of a transaction; must include a reference number if any, details of what happens next and when, contact details, likely next links, a feedback link, and a way to save a record. Warning: interactive elements inside the green confirmation panel will not be accessible without changing the panel or focus style to reach 3:1; users may bookmark confirmation pages, so make returns helpful. `[23]`
- **Text input**: all text inputs must have a **visible label above** the input, short, sentence case, no trailing colon. Avoid placeholder text as a label/hint — it vanishes while typing, is not read by all screen readers, and its browser default often fails **WCAG 2.2 SC 1.4.3**. Change history includes Jun 2026 v6.3.0 (fixed prefix/suffix text colour causing insufficient contrast) and Mar 2026 v6.1.0 (fixed focus state using the wrong shade of black). `[24]`

---

## 8. Platform target sizes — the 24 / 44 / 48 distinction

| Source | Guidance | Level/type |
|---|---|---|
| WCAG 2.2 SC 2.5.8 | At least 24x24 CSS px, or spacing exception; 44x44 belongs to AAA SC 2.5.5 | **Normative AA** `[9]` |
| Apple HIG Accessibility | iOS/iPadOS **default** control size 44x44pt, **minimum** 28x28pt (macOS 28x28/20x20; tvOS 66x66/56x56); about 12pt padding around elements with a bezel, 24pt around those without; support text enlargement of at least 200%. Change log latest June 9, 2025. | Platform recommendation, not AA `[25]` |
| Material Design 3 | Touch targets at least 48x48dp (about 9mm; recommended 7-10mm); **pointer** targets at least 44x44dp; at least 8dp spacing; page notes "iOS recommends 44x44dp targets" and that screen readers follow top-down HTML structure with landmarks/headings. | Platform recommendation, not AA `[26]` |

**House-rule implication:** adopting 44px controls is a legitimate stricter house style that simultaneously clears WCAG 2.5.8 and approximates Apple/Material; it is **not** what AA requires, so a 24px target that meets the spacing exception is not an AA failure. `[9][25][26]`

---

## 9. Fetch flags, gaps and corrections

- **Apple HTML page was JS-gated**: `web_extract` on `https://developer.apple.com/design/human-interface-guidelines/accessibility` returned only "Please turn on JavaScript in your browser ... A Markdown version of the page content is available: View Markdown". The facts in section 8 come from that site's own markdown endpoint `[25]`; nothing was substituted from memory.
- **No explicit publication/update date is stated on the page** for sources 4-15, 21, 23, 26 and 28 in the register. Dates are recorded as "not stated" rather than inferred from copyright years or URL slugs, except where the URL itself is a dated W3C Recommendation (source 2) or a dated W3C news item (source 3), which is called out explicitly.
- **Brief correction (levels)**: 3.2.6 Consistent Help and 3.3.7 Redundant Entry are **Level A**, not AA. `[10][11]`
- **Brief correction (target size)**: AA is 24x24 CSS px; 44x44 is AAA (2.5.5) and a platform default/recommendation, not the AA threshold. `[9][25][26]`
- **Potential conflict noted, not resolved**: NN/g recommends inline validation "as soon as the user has finished filling in a field" `[20]`, while GOV.UK says "Do not validate when the user moves away from a field" and to wait until submit `[21]`. Both are primary sources; for this local-only tool, follow the app's existing validation convention and record a ruling in the audit rather than silently choosing one.
- **No source could not be fetched outright** — all 28 register entries were retrieved; the one JS-gated page was replaced by the publisher's own markdown version, which is flagged above.

---

## 10. Sources

All URLs and dates are in section 2; access date 2026-11-18 for every entry. `[1]` through `[28]` refer to that table.

