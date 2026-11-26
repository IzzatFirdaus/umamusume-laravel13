# UI/UX Revision Reconciliation — ND-1..ND-16 (2026-11-26)

**Author:** Technical Documentation Engineer
**Scope:** Reconcile the nine original UI/UX revisions against authoritative owner decisions. Distinguish approved policy, review guidance and house rules. No functionality reopened.

## 1. What the "nine UI/UX revisions" are

The original programme's "nine UI/UX revisions" resolve to the **ND-1..ND-16 owner-decision set** (ND-4 lives in the combined Area F packet `area-f-plan-n2-10-nd-4.txt`; the other fifteen are `area-j-nd-000N.txt`). These are the review-driven layout/behaviour reversals that require an owner ruling before a spec assertion is inverted. They are distinct from:

- **House rules** — `AGENTS.md` §13 (UI/UX rules) + `DESIGN.md` tokens. Always binding, never owner-gated.
- **Review guidance** — `docs/audits/2026-11-21-frontend-browser-review/`, `2026-11-22-frontend-browser-review/`, `2026-11-22-bento-design-language-review/`. Descriptive findings, not authority.
- **Approved policy** — the ND packets once an owner ruling is recorded; until then each is OWNER-GATED.

## 2. ND set status (all OWNER-GATED, none silently applied)

| ND | Title | Status |
|---|---|---|
| ND-1 | HFW write-field placements | OWNER-GATED |
| ND-2 | Race-candidate confirm semantics | OWNER-GATED |
| ND-3 | KI-104 duplicate block renumbering | OWNER-GATED (register ruling wins) |
| ND-4 | Veteran transfer / re-bank lifecycle | Owner hold preserved (N2.10); ND-4 third-state retrieval in progress |
| ND-5 | Abandon-independent-training copy | OWNER-GATED |
| ND-6 | Cockpit mandatory-race obligations | OWNER-GATED |
| ND-7 | Deck slot 5 review claims | OWNER-GATED |
| ND-8 | Orphan components and dead props | OWNER-GATED (9 orphans, delete on zero-importer re-confirm) |
| ND-9 | Wizard rulings review | OWNER-GATED |
| ND-10 | Snapshot/veteran rulings review | OWNER-GATED |
| ND-11 | Hub grouping + source ledger doors | OWNER-GATED |
| ND-12 | Demote catalog card release badge | OWNER-GATED |
| ND-13 | Spark family colour identities | OWNER-GATED (palette ruling pending DESIGN.md §2) |
| ND-14 | Per-row evidence tier in ledger | OWNER-GATED |
| ND-15 | Help rules quick-reference links | OWNER-GATED |
| ND-16 | Scenario column identities + max-cap highlight + chart | OWNER-GATED |

## 3. Reconciliation rule applied

- No ND reversal is treated as approved policy until the owner ruling is recorded in the packet.
- Review findings (audits) are cited as evidence, never as authority to invert a spec assertion.
- House rules (§13/DESIGN.md) are applied continuously and are not part of the gated set.
- No declined functionality (A-0.14/KI-126) reopened; no unsupported gameplay invented.

## 4. Outstanding

- ND-4 third lifecycle state retrieval (in progress, owner hold on N2.10 stands).
- ND-13 palette ruling (DESIGN.md §2).
- All other ND packets await owner ruling; each names its own SCREEN_SPEC §7 entry and inverted assertions.