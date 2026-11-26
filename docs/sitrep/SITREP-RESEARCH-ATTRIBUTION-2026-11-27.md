# SITREP — Research Attribution and Provenance Reconciliation (2026-11-27)

Docs — Technical Documentation Engineer. Session version: Docs v3.1.
Source: Coordinator msg#142 (author correction + missing-delivery finding). Revision: `fd9f3e2e`.

Scope: documentation reconciliation only. No application database access, no test execution, no code
change. Message bodies were read from the `trainerdesk` room; authorship below is as recorded in the
mailbox, not inferred from content.

## 1. Corrected author attribution

Coordinator msg#117 labelled messages #101–#110 as "RESEARCH CLASSIFICATION CORRECTIONS". That
attribution was wrong. The technical content of those messages stands; the ownership does not.

| Messages | Actual author | Prior label |
|---|---|---|
| #101, #103, #105, #107, #109, #110 | **docs** | Research classification corrections |
| #70, #71, #72, #73, #81, #82, #83, #84 | **deep-research** | — (unchanged) |

Historical evidence is **corrected, not deleted**. The messages remain in the room with their original
`author` field; this record states what that field says so later readers do not inherit the wrong label.
Do not count #101–#110 as Deep Research delivery.

## 2. Identity correction — `ux`, not `ux-researcher`

Message #110 carries recipient `ux-researcher`, which is not an established identity. The current UX
delivery identity is **`ux`** (as used in #89 and #95). Future addressing uses `ux`. This is an
addressing correction, not a change to the UX Expert's status: UX remains one of the three participating
Experts whose formal authority is owner-confirmation-pending.

## 3. Deep Research message set and classifications

Actual Deep Research messages: #70, #71, #72, #73, #81, #82, #83, #84 — eight messages, no others.

| Message | Classification |
|---|---|
| #70 | PARTIAL — Trackblazer |
| #71 | PARTIAL — Docs input |
| #72 | NOT_OPERATIONAL wake-up / VERIFIED_PARTIAL telemetry |
| #73 | VERIFIED_PARTIAL — external services |
| #81 | PARTIAL — Happy Meek |
| #82 | BLOCKED_BY_EVIDENCE — Grand Concert |
| #83 | NOT_OPERATIONAL / VERIFIED_PARTIAL |
| #84 | PARTIAL — Trackblazer; Global-capability statement refined to UNCONFIRMED |

## 4. The three claimed Advisor Research artifacts are not delivered

Assignment #115 requested three deliverables: (a) Global gameplay evidence for recommendation rules,
(b) scenario-specific opportunity evidence, and (c) an Advisor evidence matrix.

**None is delivered.** No Deep Research message exists after #114. No matching file was found in the
primary, docs, backend or fullstack worktrees by Coordinator. Therefore:

- Do not record these as delivered, complete, or partially complete.
- Do not cite them as evidence for any Advisor rule.
- They become delivered only when their files exist at a stated repository path (or inline content is
  supplied), with contents, hashes and the actual MCP receipt establishing the delivery.

A claim of delivery without a file, a hash and a receipt is not evidence. If a deliverable cannot be
evidenced, the correct report is the gap.

## 5. Preserved distinctions

These separations survive this reconciliation and must not collapse into each other:

| Class | Meaning | Not to be confused with |
|---|---|---|
| Independently sourced finding | Evidence gathered and cited from a primary source | Peer research, which is a second-hand account |
| Peer research | Another Expert's report, attributed and unverified here | An accepted requirement |
| Application policy | What the code does now, read from source | What the code should do |
| Proposed behaviour | A described change with no ruling | Approved scope |
| Accepted requirement | Owner ruling or accepted ADR | A proposal, a finding, or a passing test |

A finding does not become a requirement by being repeated in a sitrep. A proposal does not become
approved by being documented. A passing targeted test does not become release qualification.

## 6. Effect on existing records

- `SITREP-MCP-RECONCILIATION-2026-11-26.md` carries no #101–#110 attribution text; nothing to correct
  there. This record is the correction of the msg#117 label itself.
- No roadmap, ADR, plan or issue-register entry depended on the wrong attribution; none is reopened.
- The Happy Meek and Trackblazer classifications in §3 are unchanged by the attribution fix — the
  content was always valid, only its owner was mislabelled.
