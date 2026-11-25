# Decision Packet — House Rule: TOG-12

## Rule Identifier
**Code**: TOG-12

## Rule Context
**Repository House Rule**: The twelfth touch-orbit guideline governing global navigation and stepper item accessibility.

**Source**: `UI-UX-PRODUCT-AUDIT-2026-11-18.md`; `framework-ui-ux/usability-heuristics-2026-10-21.md`

## Rule Statement
**TOG-12: Global navigation and steppers** — All navigation items and stepper components must carry `aria-current` for the current state, and focusable named scroll regions must be available for wide tables. Navigation and stepper items without these features violate TOG-12.

**Rationale**: Accessible navigation and stepper components ensure all users can understand their current position and navigate the interface effectively.

## Conflicts and Ruling
| Conflict | Settlement | Where |
| --- | --- | --- |
| **Navigation accessibility.** Tognazzini: accessibility recommended but not always enforced. Repository: `aria-current` and focusable scroll regions are mandatory for navigation and steppers. | Repository's mandatory accessibility features stand. Tognazzini's recommendations are insufficient when `aria-current` and scroll regions are absent. | `UI-UX-PRODUCT-AUDIT-2026-11-18.md`; `framework-ui-ux/usability-heuristics-2026-10-21.md` |

## Applied Screens
This ruling governs all navigation and stepper implementations in the repository, where missing `aria-current` or focusable scroll regions are TOG-12 violations.

## Verdict
**TOG-12 confirmed**: All navigation items and stepper components must carry `aria-current` for the current state, and focusable named scroll regions must be available for wide tables. Missing features violate this house rule.

## Category
Category B — House rule confirmed; `aria-current` and focusable scroll regions mandatory; missing features violate TOG-12.

---

**Key Principle**: TOG-12 requires `aria-current` and focusable named scroll regions for navigation and steppers. Tognazzini's recommendations are insufficient when these features are absent.