# Decision Packet — House Rule: TOG-4

## Rule Identifier
**Code**: TOG-4

## Rule Context
**Repository House Rule**: The fourth touch-orbit guideline governing error prevention and user confirmation requirements for destructive or irreversible operations.

**Source**: `tognazzini-first-principles-2026-10-21.md:253`; `UI-UX-PRODUCT-AUDIT-2026-11-18.md`; `framework-ui-ux/usability-heuristics-2026-10-21.md`

## Rule Statement
**TOG-4: Destructive operation confirmation** — Any destructive or irreversible operation (delete, purge, reset, irreversible configuration change) must present a confirmed user acknowledgment before execution. Unconfirmed destructive operations are prohibited.

**Rationale**: Users must explicitly acknowledge destructive operations before they execute. This is a core safety house rule preventing accidental data loss or irreversible configuration changes.

## Conflicts and Ruling
| Conflict | Settlement | Where |
| --- | --- | --- |
| **Confirmation requirement.** Tognazzini: optional confirmation for destructive operations. Repository: mandatory confirmation required. | Repository's mandatory confirmation requirement stands. Tognazzini's optional approach is not acceptable for repository operations classified as destructive. | `tognazzini-first-principles-2026-10-21.md:253`; `UI-UX-PRODUCT-AUDIT-2026-11-18.md` |

## Applied Screens
This ruling governs all destructive operation implementations in the repository, where unconfirmed destructive operations are blocked with a confirmation dialog.

## Verdict
**TOG-4 confirmed**: Mandatory user confirmation required before any destructive or irreversible operation. This is a non-negotiable house rule.

## Category
Category B — House rule confirmed; mandatory confirmation for destructive operations; no optional approach permitted.

---

**Key Principle**: TOG-4 establishes mandatory confirmation for destructive/irreversible operations. This is a safety house rule distinct from Tognazzini's optional approach.