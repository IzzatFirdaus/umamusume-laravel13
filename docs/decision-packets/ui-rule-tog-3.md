# Decision Packet — House Rule TOG-3: Status May Be Stale or Absent, but Never Wrong

## Rule Identifier
**Code**: TOG-3
**Source**: Tognazzini's First Principles (2026-10-21); `tognazzini-first-principles-2026-10-21.md:253`
**Repository Context**: The repository's rule is the acknowledgement one (50ms), because a write that has not been acknowledged reads as a dead control; compatible with Doherty 400ms response threshold

## Rule Statement
**TOG-3**: Status may be stale or absent, but never wrong.

**Interpretation**: A control that has not yet been acknowledged (within 50ms) reads as a dead control; the 400ms Doherty threshold measures *response*, not *acknowledgement*. The repository's rule acknowledges the shorter window because acknowledgement is the critical boundary — beyond 50ms without acknowledgement, the control effectively dead-ends.

**Compatible with Doherty**: 50ms = acknowledgement; 400ms = response. Both can coexist.

**Repository Ruling**: The repository's house rule is the acknowledgement one (50ms), because a write that has not been acknowledged reads as a dead control; compatible with Doherty 400ms response threshold.

## Conflicts and Ruling
| Conflict | Settlement | Where |
| --- | --- | --- |
| **Feedback timing.** Heuristics cite the 400ms Doherty threshold; Tognazzini says acknowledge a click within 50ms and show an hourglass past 500ms. | Compatible: 50ms = acknowledgement, 400ms = response. The repository's rule is the acknowledgement one, because a write that has not been acknowledged reads as a dead control. | `PROCESS-PLANS.md` §13 |
| **Repository house rule.** Tognazzini 3 vs repository's TOG-3. | The repository's TOG-3 is the acknowledgement one (50ms), compatible with Doherty; the original Tognazzini rule stands as stated in the first principles collection. | `tognazzini-first-principles-2026-10-21.md:253` |

## Applied Screens
This ruling governs any screen where write acknowledgement is the critical path, particularly:
- N7.3 Dashboard creation-door vocabulary
- N7.5 Cross-screen Snapshot position terms
- N7.20–N7.21 Task-journey and state audits
- Inherited N5.1/N5.4 forms

## Verdict
**Compatible**: The repository's TOG-3 (acknowledgement at 50ms) and Tognazzini's original precept coexist; the 50ms window is the acknowledgement boundary, the 400ms window is the response boundary. No conflict in the repository's usage.

## Category
Category B — House rule confirmed; no change needed; compatible with external source