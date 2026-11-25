# Decision Packet — House Rule: TOG-15

## Rule Identifier
**Code**: TOG-15

## Rule Context
**Repository House Rule**: The fifteenth touch-orbit guideline governing help quick-reference links and inert link rationale.

**Source**: `UI-UX-PRODUCT-AUDIT-2026-11-18.md`; `framework-ui-ux/usability-heuristics-2026-10-21.md`

## Rule Statement
**TOG-15: Help quick-reference links** — Help text rows must follow the recorded inert-link rationale. Links may only target destinations that now exist, with the rationale updated accordingly. Linking only new targets reverses a recorded decision.

**Rationale**: Help documentation must maintain consistency with the recorded inert-link rationale. Updating links requires rationale update, not simple target switching.

## Conflicts and Ruling
| Conflict | Settlement | Where |
| --- | --- | --- |
| **Help link updates.** Tognazzini: links may be updated freely. Repository: linking only new targets reverses recorded decision; rationale update required. | Repository's rationale-update requirement stands. Tognazzini's free-link-update approach reverses recorded decisions and violates TOG-15. | `UI-UX-PRODUCT-AUDIT-2026-11-18.md`; `framework-ui-ux/usability-heuristics-2026-10-21.md` |

## Applied Screens
This ruling governs all Help implementations in the repository, where linking only new targets without rationale update violates TOG-15.

## Verdict
**TOG-15 confirmed**: Help text rows must follow the recorded inert-link rationale. Linking only new targets without updating the rationale reverses a recorded decision and violates this house rule.

## Category
Category B — House rule confirmed; rationale update required for link changes; linking only new targets violates TOG-15.

---

**Key Principle**: TOG-15 requires rationale update when changing Help links. Linking only new targets without rationale update reverses recorded decisions.