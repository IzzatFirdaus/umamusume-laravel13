# Grand Concert Slices S9–S18 — Individual Definitions
**Date:** 2026-11-25
**Source:** `Our-Grand-Concert-plan.md` (verified 2026-10-21); `SCREEN_SPEC.md` §7-21 through §7-23
**Status:** All ten slices BLOCKED BY EVIDENCE. No slice may start "small follow-up" (MASTER S3.5 rule).
## Dependency structure (corrected 2026-11-25)
Three foundation slices (S11, S14, S16) establish evidence capability group.
lifecycle and UI slices in group depend on foundation. foundation slices
**not** gated on themselves — blocked by absence client-captured evidence.
| Group | Foundation | Lifecycle | UI |
|---|---|---|---|
| Lessons | S11 (Songs Evidence) — *see note* | S9, S10 | — |
| Songs | S11 | S12 | S13 |
| Live Bonus | S14 | S15 | — |
| Promotional Lives | S16 | S17 | S18 |
> **Lessons S8/S11 dependency inconsistency (preserved unresolved — commit `e8c370bc`).** The table above lists the Lessons foundation as **S11 (Songs Evidence)**, but S9 and S10 are gated on **Slice 8 (Lessons Evidence + Foundation)** — two different foundations for the same group. S8 is "Lessons Evidence + Foundation" (closed, no Lesson representation exists); S11 is "Songs Evidence + Foundation". Whether Lessons lifecycle/UI depend on S8 or S11 is unresolved; do not silently pick one. This is distinct from the S16 self-dependency (resolved below): S16 was a circular gate, this is a cross-reference ambiguity between two real foundation slices.
> **S16 self-dependency resolved.** earlier summary stated S16 was "Gated on Slice 16 closing
gate" — circular dependency. S16 **foundation** slice Promotional Lives; it
> blocked by absence Promotional Live representation and need schema change,
> not by itself. S17 and S18 depend on S16.
---
## Slice 9 — Lessons Turn Lifecycle
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: Gated on Slice 8 (Lessons Evidence + Foundation), which closed: no Lesson representation exists
- **Owner question**: Whether Lesson turn lifecycle designed without client capture Lesson screen
- **Evidence needed**: Client capture Lesson turn; SCREEN_SPEC §7-21 changelog row
- **Responsible**: Technical Documentation Engineer (evidence); Backend (implementation, unblocked)
## Slice 10 — Lessons Trainer UI
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: Gated on Slice 8 (Lessons Evidence + Foundation)
- **Owner question**: Whether Lessons Trainer UI can be designed without verified Lesson interaction copy
- **Evidence needed**: Slice 8 outcome; client capture Lesson interaction
- **Responsible**: Frontend (implementation, unblocked)
## Slice 11 — Songs Evidence + Foundation
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: No truthful Song representation exists; noun and count settled but nothing field could carry
- **Owner question**: Whether Song representation can be established without client capture Song selection or status screen
- **Evidence needed**: Client capture Song selection/status; verified Song interaction
- **Responsible**: Technical Documentation Engineer (evidence); Backend (schema, unblocked)
- **Note**: This foundation S12 and S13. Not gated on itself.
## Slice 12 — Songs Lifecycle Integration
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: Gated on Slice 11 establishing truthful Song representation
- **Owner question**: Whether Song lifecycle integration can proceed without Slice 11's foundation
- **Evidence needed**: Slice 11 outcome
- **Responsible**: Backend (implementation, unblocked)
## Slice 13 — Songs Trainer UI
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: Gated on Slice 11 (no verified Song interaction or status display)
- **Owner question**: Whether Songs Trainer UI can be designed without verified interaction copy
- **Evidence needed**: Slice 11 outcome; client capture Song interaction
- **Responsible**: Frontend (implementation, unblocked)
## Slice 14 — Live Bonus Evidence + Foundation
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: No verified Live Bonus vocabulary or value exists persist or read
- **Owner question**: Whether Live Bonus evidence can be established without client capture
- **Evidence needed**: Client capture Live Bonus screen; verified vocabulary
- **Responsible**: Technical Documentation Engineer (evidence); Backend (schema, unblocked)
- **Note**: Foundation for S15. Not gated on itself.
## Slice 15 — Live Bonus Lifecycle / Read Model
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: Gated on Slice 14 (no verified Live Bonus vocabulary or value)
- **Owner question**: Whether Live Bonus lifecycle can proceed without Slice 14's foundation
- **Evidence needed**: Slice 14 outcome
- **Responsible**: Backend (implementation, when unblocked)
## Slice 16 — Promotional Lives Evidence + Foundation
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: No Promotional Live representation exists; no table can hold series without schema change
- **Owner question**: Whether Promotional Live representation can be established; event noun (Promo Concert) official, but no verified interaction, schedule or status exists
- **Evidence needed**: Client capture of Promotional Live screen; schema ruling
- **Responsible**: Technical Documentation Engineer (evidence); Backend (schema, unblocked)
- **Note**: Foundation S17 and S18. **Not gated on itself** — earlier "Gated on Slice 16" circular dependency, now resolved.
## Slice 17 — Promotional Lives Lifecycle
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: Gated on Slice 16 (no Promotional Live representation exists)
- **Owner question**: Whether Promotional Lives lifecycle can proceed without Slice 16's foundation
- **Evidence needed**: Slice 16 outcome
- **Responsible**: Backend (implementation, unblocked)
## Slice 18 — Promotional Lives Trainer UI
- **Status**: BLOCKED BY EVIDENCE
- **Gate**: Gated on Slice 16 (no verified interaction, schedule or status display)
- **Owner question**: Whether Promotional Lives Trainer UI can be designed without verified interaction copy
- **Evidence needed**: Slice 16 outcome; client capture of Promotional Live interaction
- **Responsible**: Frontend (implementation, unblocked)
---
## Summary
All ten slices are BLOCKED BY EVIDENCE. None is owner decision — each needs client-captured
evidence (screenshots, interaction recordings, or Help text captures) before implementation can
begin. S16 self-dependency been resolved: S16 foundation slice Promotional
Lives, not gated on itself. No slice may started "small follow-up" per MASTER S3.5.
