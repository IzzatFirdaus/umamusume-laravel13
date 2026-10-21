# WCAG 2.2, and what this repository must meet

Status: **settled reference.** Records the structure and the success criteria that bind this product.

## Provenance

| Field | Value |
| --- | --- |
| Standard | [Web Content Accessibility Guidelines (WCAG) 2.2](https://www.w3.org/TR/WCAG22/) |
| Status | **W3C Recommendation, 12 December 2024** |
| Published this version | [REC-WCAG22-20241212](https://www.w3.org/TR/2024/REC-WCAG22-20241212/) |
| Edits | Alastair Campbell, Chuck Adams, Rachael Bradley Montgomery, Michael Cooper, Andrew Kirkpatrick |
| Errata | [exists](https://www.w3.org/WAI/WCAG22/errata/) |
| Fetched | 2026-10-21 |

WCAG 2.2 **extends** 2.1 and *"Content that conforms to WCAG 2.2 also conforms to WCAG 2.0 and WCAG 2.1."*
It does not supersede them, but W3C *"advises the use of WCAG 2.2 to maximize future applicability."*

## The target this repository adopts

**WCAG 2.2 Level AA.** This is the operative bar in `GOVERNANCE.md`'s accessibility contract, recorded as
an owner ruling on 2026-10-03. Level A alone is not the bar; Level AAA is not claimed.

## The four layers of guidance

The standard is deliberately layered, and only one layer is testable:

| Layer | What it is | Testable |
| --- | --- | --- |
| **Principles** | perceivable, operable, understandable, robust | no |
| **Guidelines** | the 13 goals under the principles | no — *"The guidelines are not testable, but provide the framework"* |
| **Success criteria** | the requirements | **yes** — *"testable statements that are not technology-specific"* |
| **Techniques** | sufficient and advisory, plus documented common failures | informative |

A review therefore cites a **success criterion**, never a principle. "This fails perceivable" is not a
finding.

## The four principles and thirteen guidelines

**1 Perceivable** — information and components must be presentable in ways users can perceive.
1.1 Text Alternatives · 1.2 Time-based Media · 1.3 Adaptable · 1.4 Distinguishable

**2 Operable** — components must be operable.
2.1 Keyboard Accessible · 2.2 Enough Time · 2.3 Seizures and Physical Reactions · 2.4 Navigable ·
2.5 Input Modalities

**3 Understandable** — information and operation must be understandable.
3.1 Readable · 3.2 Predictable · 3.3 Input Assistance

**4 Robust** — content must be robust enough to work with assistive technologies.
4.1 Compatible

## The success criteria that bind this product

Not the full 87. This is the subset a Trainer Desk screen can actually violate, with the level each carries.

### Contrast and colour

| SC | Level | Requirement |
| --- | --- | --- |
| **1.4.1 Use of Color** | A | *"Color is not used as the only visual means of conveying information, indicating an action, prompting a response, or distinguishing a visual element."* |
| **1.4.3 Contrast (Minimum)** | AA | **4.5:1** for text; **3:1** for large-scale text (*"at least 18 point or 14 point bold"*) |
| **1.4.6 Contrast (Enhanced)** | AAA | 7:1 / 4.5:1 — **not claimed** |
| **1.4.11 Non-text Contrast** | AA | 3:1 for control boundaries, states and graphical objects |
| **1.4.13 Content on Hover or Focus** | AA | dismissable, hoverable, persistent |

1.4.3's exceptions matter here: **incidental** text (inactive components, pure decoration) and
**logotypes** have no requirement.

### Layout and resize

| SC | Level | Requirement |
| --- | --- | --- |
| **1.4.4 Resize Text** | AA | resizeable to **200%** without loss of content or functionality |
| **1.4.10 Reflow** | AA | no two-dimensional scrolling at 320 CSS px |
| **1.4.12 Text Spacing** | AA | no loss when line-height is 1.5, paragraph spacing 2×, letter spacing 0.12em, word spacing 0.16em |
| **1.3.4 Orientation** | AA | not restricted to one orientation |

### Keyboard and focus

| SC | Level | Requirement |
| --- | --- | --- |
| **2.1.1 Keyboard** | A | all functionality operable from a keyboard |
| **2.1.2 No Keyboard Trap** | A | focus can always leave |
| **2.1.4 Character Key Shortcuts** | A | single-character shortcuts can be remapped, turned off, or are only active on focus |
| **2.4.3 Focus Order** | A | focus order preserves meaning |
| **2.4.7 Focus Visible** | AA | the keyboard focus indicator is visible |
| **2.4.11 Focus Not Obscured (Minimum)** | AA | **new in 2.2** — a focused component is not entirely hidden by author-created content |
| **2.4.13 Focus Appearance** | AAA | — **not claimed** |

**2.1.4 is the one this product has to think about**, because the guided flow uses `1` to `5` as
single-character accelerators. That is permitted only if the shortcut can be turned off, remapped, or is
active only when the control has focus — which is the condition the help page now states.

### Pointer and targets

| SC | Level | Requirement |
| --- | --- | --- |
| **2.5.1 Pointer Gestures** | A | multi-point or path gestures have a single-pointer alternative |
| **2.5.2 Pointer Cancellation** | A | activation happens on up-event, not down |
| **2.5.3 Label in Name** | A | the accessible name contains the visible label text |
| **2.5.4 Motion Actuation** | A | device-motion functions have a UI alternative |
| **2.5.5 Target Size (Enhanced)** | AAA | 44×44 — **not claimed, and met anyway by choice** |
| **2.5.7 Dragging Movements** | AA | **new in 2.2** — any drag operation has a single-pointer alternative |
| **2.5.8 Target Size (Minimum)** | AA | **new in 2.2** — **24×24 CSS px** |

**The 44px rule here is a house rule that exceeds 2.5.8.** The AA requirement is 24×24; this product ships
44px because it matches the app's own `h-11` and Fitts's Law. That is a deliberate margin, and it should be
described as one rather than as conformance to 2.5.8.

### Errors, help and consistency

| SC | Level | Requirement |
| --- | --- | --- |
| **3.3.1 Error Identification** | A | errors are described in text and identified |
| **3.3.2 Labels or Instructions** | A | labels or instructions where input is required |
| **3.3.3 Error Suggestion** | AA | suggest a correction where one is known |
| **3.3.7 Redundant Entry** | AA | **new in 2.2** — information already entered is not requested again |
| **3.2.3 Consistent Navigation** | AA | repeated navigation is in the same relative order |
| **3.2.4 Consistent Identification** | AA | components with the same function are identified consistently |
| **3.2.6 Consistent Help** | A | **new in 2.2** — help mechanisms appear in the same relative order |
| **3.3.8 Accessible Authentication (Minimum)** | AA | **new in 2.2** — no cognitive test for authentication |

**3.2.6 Consistent Help is the criterion the new help page answers.** The `?` sits in the same position in
every page's top bar, which is exactly what the criterion asks for.

**3.3.7 Redundant Entry is why the import never asks for a field it already parsed.** It shows the parse and
asks only for the miss.

### Semantics and status

| SC | Level | Requirement |
| --- | --- | --- |
| **1.3.1 Info and Relationships** | A | structure and relationships programmatically determined |
| **1.3.2 Meaningful Sequence** | A | a correct reading sequence exists |
| **1.3.3 Sensory Characteristics** | A | instructions do not rely solely on shape, colour, size, location, orientation or sound |
| **2.4.2 Page Titled** | A | pages have descriptive titles |
| **2.4.6 Headings and Labels** | AA | headings and labels describe topic or purpose |
| **4.1.2 Name, Role, Value** | A | every component exposes name, role and value |
| **4.1.3 Status Messages** | AA | status messages are exposed without a focus change |

### Removed in 2.2

**4.1.1 Parsing is obsolete and removed.** It is recorded here because older checklists still cite it, and
a review that reports a 4.1.1 failure is reporting against a withdrawn criterion.

### Not applicable to this product

The whole of **1.2 Time-based Media** (no media), **1.4.2 Audio Control** (no audio), **2.2.3 No Timing**,
**2.3 Seizures** (no flashing), **3.3.8 Accessible Authentication** (there is no authentication by design,
`PRD.md` NFR-1 and `ARCHITECTURE.md` §8). Their absence is a design fact, not an achievement.

## How the AA criteria map onto this repository

| Criterion | Implemented by | Where it is visible |
| --- | --- | --- |
| 1.4.1 Use of Colour | D-12; the `.trust` glyph-plus-word vocabulary | Every state carries a word or glyph. |
| 1.4.3 Contrast | the approved-pair table; the dark ramp measured separately | `app.css` records each pair's ratio per theme. |
| 1.4.11 Non-text Contrast | rule and control-boundary tokens | The rule edge carries the distinction the page/panel step cannot. |
| 1.4.4 / 1.4.10 / 1.4.12 | the three layout widths; the 320px floor | The wide tables scroll inside a labelled region, never the page. |
| 2.1.1 / 2.4.7 | D-55; the 3px focus ring (D-13) | `outline: none` without a replacement is a review failure. |
| 2.1.4 Character Key Shortcuts | **the help page states `1`–`5` and offers Escape** | The shortcut is focus-scoped in the flow. |
| 2.4.11 Focus Not Obscured | the sticky footer rule | Sticky UI must not cover a focused control. |
| 2.5.3 Label in Name | visible label equals accessible name | No icon-only control without a text label in the same element. |
| 2.5.7 Daraging Movements | no drag operation in the product | Not applicable, stated rather than assumed. |
| 2.5.8 Target Size | the 44px rule, **exceeding** the 24px minimum | Every control. |
| 3.3.1 / 3.3.3 / 3.3.7 | the import preview's per-field found/missing tags | Ask only for the miss. |
| 3.2.6 Consistent Help | **the `?` in the same top-bar position on every page** | Added with the help page. |
| 4.1.2 Name, Role, Value | `<label>` wrapping every field; radio groups named | `import-preview` binds 20 fields. |
| 4.1.3 Status Messages | `role="status"` on a saved write | Never a focus steal. |

## Gap register

| Criterion | Gap | Status |
| --- | --- | --- |
| **1.4.3 Contrast** | **No pair has been measured.** `app.css` records intended ratios; the G-18 gate measures nothing yet and `DESIGN.md` §2.1 says so. | open, recorded in the gate registry |
| **1.4.11 Non-text Contrast** | Recorded against the `line-*` tokens in `SLICE-RECORDS.md`: those tokens carry no contrast claim. | open |
| **2.1.4 Character Key Shortcuts** | The accelerators are stated, but the product has no remap or disable control. | open |
| **2.4.11 Focus Not Obscured** | A boundary was measured under 3:1 against its background in `PROCESS-PLANS.md`, and the sticky-bar interaction is unmeasured at 390px. | open |
| **4.1.1 Parsing** | Recorded as a defect in `UX-DELIVERABLES.md` (KI-63, Inertia NProgress `role="bar"`), but **4.1.1 is withdrawn in 2.2** — the defect is real under 4.1.2, and the citation needs correcting. | recorded |

**The measurement limit is the finding that matters.** Every claim in this file is source-and-markup
evidence. Chromium cannot launch under this repository's sandbox (`spawn EPERM`), so no contrast ratio, no
target size and no reflow behaviour has been measured in a browser. A C-7 review citing this file must say
which claims are unmeasured.

## What this framework decides that the others do not

WCAG is the only **normative** framework in this set. The others advise; this one defines conformance, and
it is the only one where a failure has a number attached (1.4.3's 4.5:1, 2.5.8's 24px, 1.4.10's 320px).

Three consequences follow:

- **It is the only framework with a version and an errata list.** A review must name WCAG 2.2 AA, not
  "accessible".
- **It separates testable from untestable.** Principles and guidelines are not findings; only success
  criteria are.
- **It says what does not apply.** A product with no media, no audio, no flashing and no authentication
  fails none of those criteria, and saying so is part of the record rather than an omission.

## Checkable rules derived

| Id | Rule | Source |
| --- | --- | --- |
| WCAG-1 | A review cites a success criterion, never a principle. | layers of guidance |
| WCAG-2 | Text contrast is 4.5:1, large text 3:1, non-text 3:1. | 1.4.3, 1.4.11 |
| WCAG-3 | A single-character accelerator is focus-scoped or disableable. | 2.1.4 |
| WCAG-4 | A help mechanism sits in the same relative order on every page. | 3.2.6 |
| WCAG-5 | Never ask twice for information already given. | 3.3.7 |
| WCAG-6 | A focused control is never fully hidden by author content. | 2.4.11 |
| WCAG-7 | Do not cite 4.1.1; it is withdrawn in 2.2. | change log |
| WCAG-8 | An unmeasured criterion is reported as unmeasured, not as passed. | conformance |

## Change log

| Date | Change |
| --- | --- |
| 2026-10-21 | Created from the W3C WCAG 2.2 Recommendation. Records the AA subset, the five criteria new in 2.2, the withdrawal of 4.1.1, and the unmeasured state of every contrast claim in this repository. |
