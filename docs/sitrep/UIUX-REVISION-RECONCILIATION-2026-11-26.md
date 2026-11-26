# UI/UX Revision Reconciliation — Nine Revision Areas vs ND-1..ND-16 (2026-11-26)

**Author:** Technical Documentation Engineer
**Scope:** Reconcile the nine original UI/UX revision areas against authoritative owner decisions. Distinguish approved policy, review guidance and house rules. No functionality reopened.

## 1. What the "nine UI/UX revisions" are

The original programme's "nine UI/UX revisions" are **nine revision areas**, not the ND-1..ND-16 owner-decision set. The existence of sixteen ND decisions does **not** establish equivalence between the two sets; no authoritative mapping equates them. The nine areas are:

1. Typography hierarchy
2. Semantic colours
3. Spacing and surfaces
4. Action hierarchy
5. Perceptual grouping
6. Purposeful motion
7. Accessible interactive targets
8. Dominant tasks
9. Meaningful statistical identity

These areas are distinct from, and must not be conflated with, the following categories:

- **ND-1..ND-16 owner decisions** — review-driven layout/behaviour reversals requiring an owner ruling (ND-4 in `area-f-plan-n2-10-nd-4.txt`; others `area-j-nd-000N.txt`). Descriptive until an owner ruling is recorded.
- **UI-1..UI-7** — a separate UI identifier series, not the nine areas and not the ND set.
- **TOG supporting research** — descriptive research input; never mandatory requirement without owner authorization.
- **DESIGN.md requirements** — design tokens and binding design rules.
- **AGENTS.md house rules** — §13 UI/UX rules; always binding, never owner-gated.
- **Technical review guidance** — `docs/audits/2026-11-21-frontend-browser-review/`, `2026-11-22-frontend-browser-review/`, `2026-11-22-bento-design-language-review/`. Descriptive findings, not authority.
- **Owner-approved policies** — the ND packets once an owner ruling is recorded; until then each is OWNER-GATED.

No equivalence between the nine areas and ND-1..16 is asserted. Historical owner decisions are not redefined.

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