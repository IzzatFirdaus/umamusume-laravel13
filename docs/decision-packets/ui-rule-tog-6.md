# Decision Packet — House Rule: TOG-6

## Rule Identifier
**Code**: TOG-6

## Rule Context
**Repository House Rule**: The sixth touch-orbit guideline governing preflight disclosure and explicit acknowledgement requirements for steps that will create data.

**Source**: `tognazzini-first-principles-2026-10-21.md:253`; `UI-UX-PRODUCT-AUDIT-2026-11-18.md:16`; `framework-ui-ux/usability-heuristics-2026-10-21.md`

## Rule Statement
**TOG-6: Preflight disclosure and acknowledgement** — Any step that will create data (run, position, configuration) must present a preflight disclosure stating what will be created, what the run will be measured against, and require an explicit acknowledgement before the one write executes.

**Rationale**: Users must explicitly acknowledge data-creation steps before execution. This prevents accidental creation of runs, positions, or configurations without user awareness.

## Conflicts and Ruling
| Conflict | Settlement | Where |
| --- | --- | --- |
| **Preflight disclosure.** Tognazzini: optional disclosure for data-creation steps. Repository: mandatory preflight disclosure with explicit acknowledgement required. | Repository's mandatory preflight disclosure and acknowledgement requirement stands. Tognazzini's optional approach is not acceptable. | `tognazzini-first-principles-2026-10-21.md:253`; `UI-UX-PRODUCT-AUDIT-2026-11-18.md:16` |

## Applied Screens
This ruling governs all preflight disclosure implementations in the repository, where steps creating data must present disclosure and require explicit acknowledgement.

## Verdict
**TOG-6 confirmed**: Mandatory preflight disclosure and explicit acknowledgement required before any data-creation step. This is a non-negotiable house rule.

## Category
Category B — House rule confirmed; mandatory preflight disclosure and acknowledgement; no optional approach permitted.

---

**Key Principle**: TOG-6 establishes mandatory preflight disclosure and acknowledgement for data-creation steps. This is a safety house rule distinct from Tognazzini's optional approach.