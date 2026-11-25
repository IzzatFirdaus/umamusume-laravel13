# Decision Packet — House Rule: TOG-9

## Rule Identifier
**Code**: TOG-9

## Rule Context
**Repository House Rule**: The ninth touch-orbit guideline governing interactive target sizing relative to WCAG 2.5.8 AA minimums.

**Source**: `wcag-2.2-accessibility-2026-10-21.md`; `framework-ui-ux/usability-heuristics-2026-10-21.md`; `UI-UX-PRODUCT-AUDIT-2026-11-18.md`

## Rule Statement
**TOG-9: Touch target minimum** — All interactive targets must meet or exceed the repository's 44px house rule minimum. Targets below 44px must carry a documented spacing exception per the house floor rule; targets relying on the WCAG 24px spacing exception must not be described as AA conformant.

**Rationale**: The repository's house rule requires 44px minimum touch targets. This is stricter than WCAG 2.5.8 AA's 24px minimum. Targets using the WCAG spacing exception must not claim AA conformance.

## Conflicts and Ruling
| Conflict | Settlement | Where |
| --- | --- | --- |
| **Target size vs WCAG AA.** Tognazzini/Material: 48x48dp or 24px AA. Repository: 44px house rule. | Repository's 44px house rule stands as the minimum. WCAG 24px AA is a separate conformance level; the house rule is not AA conformance, it is a stricter house style. | `wcag-2.2-accessibility-2026-10-21.md`; `framework-ui-ux/usability-heuristics-2026-10-21.md` |

## Applied Screens
This ruling governs all interactive target sizing in the repository, where 44px is the house rule minimum and WCAG AA 24px is a different conformance level.

## Verdict
**TOG-9 confirmed**: All interactive targets must meet the 44px house rule minimum. Targets below 44px must carry a documented spacing exception; WCAG AA 24px conformance must not be claimed for house-rule targets.

## Category
Category B — House rule confirmed; 44px minimum stands; WCAG AA 24px is a separate conformance level; spacing exceptions must be documented.

---

**Key Principle**: TOG-9 establishes 44px house rule minimum for interactive targets. This is stricter than WCAG 2.5.8 AA's 24px minimum; the two are not conformance-equivalent.