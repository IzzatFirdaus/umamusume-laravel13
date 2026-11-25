# Decision Packet — House Rule: TOG-14

## Rule Identifier
**Code**: TOG-14

## Rule Context
**Repository House Rule**: The fourteenth touch-orbit guideline governing wide table scroll and data provenance printing.

**Source**: `UI-UX-PRODUCT-AUDIT-2026-11-18.md`; `framework-ui-ux/usability-heuristics-2026-10-21.md`

## Rule Statement
**TOG-14: Data provenance printing in tables** — Any table displaying derived data must print provenance beside the table, showing source and derivation path. Provenance must be visible and not hidden from the user, consistent with TOG-7's principle extended to table contexts.

**Rationale**: Users must be able to trace derived data back to its source even within table contexts. Hidden or absent provenance in tables violates this house rule.

## Conflicts and Ruling
| Conflict | Settlement | Where |
| --- | --- | --- |
| **Provenance in tables.** Tognazzini: provenance optional in table contexts. Repository: provenance must be printed beside any table displaying derived data, consistent with TOG-7. | Repository requires provenance printing beside tables displaying derived data, consistent with TOG-7. Tognazzini's optional approach is not acceptable. | `UI-UX-PRODUCT-AUDIT-2026-11-18.md`; `framework-ui-ux/usability-heuristics-2026-10-21.md` |

## Applied Screens
This ruling governs all table implementations displaying derived data in the repository, where provenance must be visible beside the table.

## Verdict
**TOG-14 confirmed**: Any table displaying derived data must print provenance beside the table. Hidden or absent provenance in tables violates this house rule.

## Category
Category B — House rule confirmed; provenance must be printed beside tables displaying derived data; consistent with TOG-7.

---

**Key Principle**: TOG-14 extends TOG-7's provenance principle to table contexts. Derived data in tables must carry visible provenance.