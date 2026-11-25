# Decision Packet — House Rule: TOG-8

## Rule Identifier
**Code**: TOG-8

## Rule Context
**Repository House Rule**: The eighth touch-orbit guideline governing status message consistency and named exceptions in status disclosure.

**Source**: `UI-UX-PRODUCT-AUDIT-2026-11-18.md`; `framework-ui-ux/usability-heuristics-2026-10-21.md`

## Rule Statement
**TOG-8: Status consistency and named exceptions** — Status messages must maintain consistency in formatting, terminology, and disclosure pattern. Named exceptions to the consistent pattern must be documented and justified. Undocumented status inconsistencies violate TOG-8.

**Rationale**: Consistent status messages improve user mental models. Undocumented exceptions create confusion and violate the house rule's consistency requirement.

## Conflicts and Ruling
| Conflict | Settlement | Where |
| --- | --- | --- |
| **Status consistency.** Tognazzini: consistent status preferred but exceptions natural. Repository: status must be consistent; any exception must be named and documented. | Repository requires consistent status formatting and terminology. Any exception must be named and documented; undocumented exceptions are TOG-8 violations. | `UI-UX-PRODUCT-AUDIT-2026-11-18.md`; `framework-ui-ux/usability-heuristics-2026-10-21.md` |

## Applied Screens
This ruling governs all status message implementations in the repository, where undocumented status inconsistencies are violations of TOG-8.

## Verdict
**TOG-8 confirmed**: Status messages must maintain consistency in formatting, terminology, and disclosure pattern. Any exception must be named and documented; undocumented exceptions violate this house rule.

## Category
Category B — House rule confirmed; status consistency required; undocumented exceptions violate TOG-8.

---

**Key Principle**: TOG-8 requires status message consistency with named and documented exceptions. Undocumented exceptions are violations.