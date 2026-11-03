# Blocked scenarios — Phase 4.1 and 4.2 evidence gaps

Date: 2026-11-03 (final closure pass)

## Phase 4.1 — Unity Training evaluator

**Current state:** `docs/Unity-Cup-plan.md` §"Not implemented" records: *"Nothing. The absence is the finding."* The evaluator is unimplemented and the plan itself says the absence is the finding.

**What's missing (the spec gap):**
- Input shape: what state the evaluator reads (Unity Cup training parameters, spirit burst state, team board)
- Output shape: what it produces (evaluation score, recommendation, confidence)
- Validation rules: what inputs are valid, what ranges are acceptable
- Calibrated-approximation boundary: whether the evaluator is exact or approximate, and if approximate, the calibration method

**Options:**
- (a) Provide the spec in `Unity-Cup-plan.md` — then implement per spec
- (b) Accept the evaluator is unimplemented (current state) — no further work until evidence exists
- (c) Descope the feature — remove the evaluator from the Unity Cup plan

**Recommendation:** (b) — the plan itself says the absence is the finding. No further work until evidence exists. The Unity Cup scenario ships without the evaluator; the advisor works from the scenario's existing data.

**Status:** blocked on missing spec (owner or Data Engineer to provide)

---

## Phase 4.2 — Grand Concert S8–S18 (evidence gaps)

The following slices are `BLOCKED BY EVIDENCE` per `docs/proposals/Our-Grand-Concert-plan.md`. S3–S7 and S19–S22 are `COMPLETE`.

| Slice | Title | Evidence gap |
|---|---|---|
| S8 | Lessons representation | No Lesson representation exists in the data model |
| S11 | Songs | Noun and count settled, nothing a field could carry |
| S14 | Live Bonus | Six renderings for three objects — no single representation |
| S16 | Promo Concert | No `year` column, no concert kind, no schedule |

**Additional blocked slices** (S9, S10, S12, S13, S15, S17, S18) — blocked on evidence from the above gaps cascading.

**Options for the whole set:**
- (a) Provision evidence in the reference corpus (`docs/UMAMUSUME_REFERENCE.md`) — then implement
- (b) Descope the blocked slices — ship Grand Concert without Lessons, Songs, Live Bonus, Promo Concert
- (c) Accept generic representation — implement with placeholder data until evidence arrives

**Recommendation:** (b) for S16 (Promo Concert has a structural gap that needs schema work), (a) for S8/S11/S14 (evidence can be provisioned from the JP client without schema changes).

**Status:** blocked on evidence (owner or Data Engineer to provision)
