# Decision Packets — Grand Concert Slices S9 through S18 (Summary)

Based on the Grand Concert implementation plan (`Our-Grand-Concert-plan.md`, verified 2026-10-21), the following slices S9–S18 were identified with their statuses:

## Slice 9 — Lessons Turn Lifecycle
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: Gated on Slice 8 (Lessons Evidence + Foundation), which closed the gate: no Lesson representation exists
- **Dependency**: Slice 9's status matches the Current Status table and Slice 8's outcome; only if Slice 8 establishes a valid Lesson representation
- **Evidence**: Slice 8's own outcome and `SCREEN_SPEC.md` changelog row for 2026-10-09 record the outcome; Slice 9 cannot proceed without Slice 8
- **Evidence hierarchy**: `verified` (repo) for Slice 8 gate closure; `unknown` for Slice 9 without Slice 8 establishment
- **Related**: `SCREEN_SPEC.md` §7-21; Slice 8 — Lessons Evidence + Foundation

## Slice 10 — Lessons Trainer UI
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: Gated on Slice 8 (Lessons Evidence + Foundation), which closed the gate: no Lesson representation exists
- **Dependency**: Slice 10's status matches the Current Status table and Slice 8's outcome
- **Evidence**: Slice 8's outcome determines Slice 10's status; no independent evidence
- **Related**: `SCREEN_SPEC.md` §7-22; Slice 8 — Lessons Evidence + Foundation

## Slice 11 — Songs Evidence + Foundation
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: Gated on Slice 11 establishing truthful Song representation
- **Evidence**: No truthful Song representation exists to integrate; the noun and count are settled but nothing a field could carry is; no verified Song interaction or status display
- **Related**: `SCREEN_SPEC.md` §7-21; songs have a settled noun and count; nothing else a surface could read

## Slice 12 — Songs Lifecycle Integration
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: Gated on Slice 11 establishing truthful representation
- **If Slice 11 establishes truthful representation**: Slice 12 would integrate Songs lifecycle; currently blocked because Slice 11 is blocked
- **Related**: `SCREEN_SPEC.md` §7-21; Slice 11 — Songs Evidence + Foundation

## Slice 13 — Songs Trainer UI
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: Gated on Slice 11 (no verified Song interaction or status display)
- **Evidence**: No verified Song interaction or status display exists; the surface named below has since moved — see the dated note
- **Related**: `SCREEN_SPEC.md` §7-21; Slice 11 — Songs Evidence + Foundation

## Slice 14 — Live Bonus Evidence + Foundation
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: Gated on Slice 14 closing the gate: no verified Live Bonus vocabulary or value exists to persist or read
- **Evidence**: No verified Live Bonus vocabulary or value exists to persist or read; the structure is the best-corroborated thing in this scenario
- **Related**: `SCREEN_SPEC.md` §7-22; Live Bonuses have a corroborated structure and six English names for three objects, none of them client copy

## Slice 15 — Live Bonus Lifecycle / Read Model
- **Status**: BLOCKED BY EVIDENCE (re-verified 2026-10-21)
- **Gate**: Capability re-verified 2026-10-21; the surface named below has since moved — see the dated note
- **Evidence**: The surface named below has since moved; decision re-verified against the code 2026-10-21; code since moved and the decision no longer holds — see the dated note
- **Related**: `SCREEN_SPEC.md` §7-22; Live Bonus lifecycle

## Slice 16 — Promotional Lives Evidence + Foundation
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: Gated on evidence, not on Slice 16: no Promotional Live representation exists and no table can hold the series without a schema change
- **Evidence**: No Promotional Live representation exists; no table can hold the series without a schema change; the event's own noun is official (Promo Concert)
- **Related**: `SCREEN_SPEC.md` §7-23; Promotional Lives; event noun is official

## Slice 17 — Promotional Lives Lifecycle
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: Gated on Slice 16; no Promotional Live lifecycle representation exists
- **Evidence**: No Promotional Live lifecycle representation exists; the event's own noun is official (Promo Concert)
- **Related**: `SCREEN_SPEC.md` §7-23; Promotional Lives lifecycle

## Slice 18 — Promotional Lives Trainer UI
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: Gated on Slice 16; no verified interaction, schedule or status to display, though the event's own noun is official
- **Evidence**: No verified interaction, schedule or status to display; the event's own noun is official (Promo Concert)
- **Related**: `SCREEN_SPEC.md` §7-23; Promotional Lives Trainer UI

---
**Note**: This summary covers slices S9–S18 from the Grand Concert implementation plan (verified 2026-10-21). Each slice carries a `COMPLETE` or `BLOCKED BY EVIDENCE` status as documented in the plan. Slices S9–S12 are gated on Slice 8 (Lessons Evidence + Foundation); Slices S13–S15 are gated on Slice 11 (Songs Evidence + Foundation); Slices S16–S18 are gated on Slice 16 (Promotional Lives Evidence + Foundation). No new decision packets are created for these slices in this summary; the statuses are recorded for reference.