# Decision Packet — House Rule: TOG-10

## Rule Identifier
**Code**: TOG-10

## Rule Context
**Repository House Rule**: The tenth touch-orbit guideline governing progressive disclosure and informational closure boundaries.

**Source**: `tognazzini-first-principles-2026-10-21.md`; `UI-UX-PRODUCT-AUDIT-2026-11-18.md`; `framework-ui-ux/usability-heuristics-2026-10-21.md`

## Rule Statement
**TOG-10: Progressive disclosure boundary** — Progressive disclosure may reveal additional detail or secondary information, but must not conceal primary actionability. Informational closure must not be used to hide critical controls or data. Progressive disclosure that conceals primary functionality violates TOG-10.

**Rationale**: Users must always have access to primary controls and data. Progressive disclosure must enhance, not diminish, discoverability of primary functionality.

## Conflicts and Ruling
| Conflict | Settlement | Where |
| --- | --- | --- |
| **Progressive disclosure.** Tognazzini: progressive disclosure is encouraged. Repository: progressive disclosure must not conceal primary actionability or critical controls. | Repository's boundary stands: progressive disclosure may reveal secondary information but must not conceal primary actionability or critical controls. Tognazzini's encouragement is conditioned on this boundary. | `tognazzini-first-principles-2026-10-21.md`; `UI-UX-PRODUCT-AUDIT-2026-11-18.md` |

## Applied Screens
This ruling governs all progressive disclosure implementations in the repository, where primary actionability must remain accessible.

## Verdict
**TOG-10 confirmed**: Progressive disclosure may reveal secondary information but must not conceal primary actionability or critical controls. Concealing primary functionality violates this house rule.

## Category
Category B — House rule confirmed; progressive disclosure must preserve primary actionability; concealment violates TOG-10.

---

**Key Principle**: TOG-10 establishes that progressive disclosure must not conceal primary actionability or critical controls. Tognazzini's guidance is conditioned on this boundary.