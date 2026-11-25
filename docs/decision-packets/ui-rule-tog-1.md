# Decision Packet — House Rule: TOG-1

## Rule Identifier
**Code**: TOG-1

## Rule Context
**Repository House Rule**: The first touch-orbit guideline governing interactive element acknowledgment timing and status disclosure requirements.

**Source**: `tognazzini-first-principles-2026-10-21.md:253`; `UI-UX-PRODUCT-AUDIT-2026-11-18.md`; `framework-ui-ux/usability-heuristics-2026-10-21.md`

## Rule Statement
**TOG-1: Acknowledgement threshold** — Any interactive control must provide visual acknowledgement within 50ms of user activation. Failure to acknowledge within this threshold renders the control effectively unresponsive per repository policy.

**Rationale**: The 50ms acknowledgement threshold is a hard constraint derived from Tognazzini's first principles collection. Controls exceeding this threshold require a documented exception or redesign.

## Conflicts and Ruling
| Conflict | Settlement | Where |
| --- | --- | --- |
| **Acknowledgement timing.** Tognazzini original: variable depending on control type. Repository: fixed 50ms threshold. | Repository's 50ms threshold stands as the fixed acknowledgement requirement; Tognazzini's variable approach is noted as historical reference only. | `tognazzini-first-principles-2026-10-21.md:253`; `framework-ui-ux/usability-heuristics-2026-10-21.md` |

## Applied Screens
This ruling governs all interactive controls in the repository, where 50ms acknowledgement is the minimum threshold for visual feedback.

## Verdict
**TOG-1 confirmed**: The 50ms fixed acknowledgement threshold is the repository's house rule. All interactive controls must acknowledge within 50ms of user activation. Controls failing this threshold require exception documentation or redesign.

## Category
Category B — House rule confirmed; 50ms acknowledgement threshold stands; exceptions require documented ruling.

---

**Key Principle**: TOG-1 establishes 50ms fixed acknowledgement threshold for all interactive controls. This is a house rule, not WCAG conformance.