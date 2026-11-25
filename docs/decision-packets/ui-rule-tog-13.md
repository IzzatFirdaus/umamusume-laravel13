# Decision Packet — House Rule: TOG-13

## Rule Identifier
**Code**: TOG-13

## Rule Context
**Repository House Rule**: The thirteenth touch-orbit guideline governing table focusable named scroll regions and data table accessibility.

**Source**: `UI-UX-PRODUCT-AUDIT-2026-11-18.md`; `framework-ui-ux/usability-heuristics-2026-10-21.md`

## Rule Statement
**TOG-13: Data table focusable regions** — Wide tables must use focusable named scroll regions for effective keyboard navigation. Tables without focusable named scroll regions violate TOG-13.

**Rationale**: Keyboard users must be able to navigate wide tables effectively. Named scroll regions provide meaningful focus targets beyond generic table cell navigation.

## Conflicts and Ruling
| Conflict | Settlement | Where |
| --- | --- | --- |
| **Table navigation.** Tognazzini: table navigation adequate with generic scrolling. Repository: focusable named scroll regions are mandatory for wide tables. | Repository's mandatory focusable named scroll regions stand. Tognazzini's generic scrolling is insufficient for keyboard accessibility of wide tables. | `UI-UX-PRODUCT-AUDIT-2026-11-18.md`; `framework-ui-ux/usability-heuristics-2026-10-21.md` |

## Applied Screens
This ruling governs all data table implementations in the repository, where missing focusable named scroll regions are TOG-13 violations.

## Verdict
**TOG-13 confirmed**: Wide tables must use focusable named scroll regions for effective keyboard navigation. Tables without these regions violate this house rule.

## Category
Category B — House rule confirmed; focusable named scroll regions mandatory for wide tables; missing features violate TOG-13.

---

**Key Principle**: TOG-13 requires focusable named scroll regions for wide tables. Tognazzini's generic scrolling is insufficient for keyboard accessibility.