# Decision Packet — House Rule: TOG-2

## Rule Identifier
**Code**: TOG-2

## Rule Context
**Repository House Rule**: The second touch-orbit guideline governing status message disclosure and stale-state handling in user interfaces.

**Source**: `tognazzini-first-principles-2026-10-21.md:253`; `UI-UX-PRODUCT-AUDIT-2026-11-18.md`; `framework-ui-ux/usability-heuristics-2026-10-21.md`

## Rule Statement
**TOG-2: Status disclosure requirement** — Any status message presented to the user must either provide current data or explicitly indicate staleness. A status message that is stale or absent without disclosure violates TOG-2.

**Rationale**: Users must be able to distinguish between current state and stale state. Implicit staleness without disclosure is a violation of this house rule.

## Conflicts and Ruling
| Conflict | Settlement | Where |
| --- | --- | --- |
| **Status staleness.** Tognazzini: stale status permitted if not harmful. Repository: stale status must be disclosed. | Repository requires explicit disclosure when status is stale or absent; silence on staleness is a TOG-2 violation. | `tognazzini-first-principles-2026-10-21.md:253`; `UI-UX-PRODUCT-AUDIT-2026-11-18.md` |

## Applied Screens
This ruling governs all status message implementations in the repository, where stale status must carry explicit disclosure.

## Verdict
**TOG-2 confirmed**: Stale or absent status messages must carry explicit disclosure. Silence on staleness violates this house rule.

## Category
Category B — House rule confirmed; stale status must be disclosed; no conformance to Tognazzini's more permissive stance.

---

**Key Principle**: TOG-2 requires explicit disclosure for stale or absent status messages. Repository stance is stricter than Tognazzini's original guidance.