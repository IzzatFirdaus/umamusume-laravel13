# Decision Packet — House Rule: TOG-7

## Rule Identifier
**Code**: TOG-7

## Rule Context
**Repository House Rule**: The seventh touch-orbit guideline governing data provenance printing and provenance transparency requirements.

**Source**: `UI-UX-PRODUCT-AUDIT-2026-11-18.md`; `framework-ui-ux/usability-heuristics-2026-10-21.md`

## Rule Statement
**TOG-7: Data provenance printing** — Any dashboard or figure that displays derived data must print the data provenance beside the figure, showing source and derivation path. Provenance must be visible and not hidden from the user.

**Rationale**: Users must be able to trace derived data back to its source. Hidden or absent provenance violates user trust and this house rule.

## Conflicts and Ruling
| Conflict | Settlement | Where |
| --- | --- | --- |
| **Provenance disclosure.** Tognazzini: provenance optional if not critical. Repository: provenance must be printed beside any displayed derived data. | Repository requires provenance printing beside any displayed derived data. Tognazzini's optional approach is not acceptable when data is displayed. | `UI-UX-PRODUCT-AUDIT-2026-11-18.md`; `framework-ui-ux/usability-heuristics-2026-10-21.md` |

## Applied Screens
This ruling governs all dashboard and figure implementations in the repository, where derived data must carry visible provenance.

## Verdict
**TOG-7 confirmed**: Any dashboard or figure displaying derived data must print provenance beside the figure. Hidden or absent provenance violates this house rule.

## Category
Category B — House rule confirmed; provenance must be printed; no optional approach permitted.

---

**Key Principle**: TOG-7 requires data provenance printing beside any displayed derived data. This is a transparency house rule.