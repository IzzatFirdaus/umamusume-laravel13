# ADR template

An ADR records **a ruling** — what was decided and why, as of its date. It is not a status
dashboard and not a changelog. Three things that used to share one file now live apart:

| Thing | Lives in | Changes |
|---|---|---|
| The ruling | the ADR body | never — it is the record of what an author believed on its date |
| What has happened since | the ADR's `## Ledger` | append-only, newest first |
| What is true right now | `docs/adr/CURRENT.md` + the generated index | regenerated, never hand-typed |

Read order for a session: `docs/adr/CURRENT.md` (the current facts, one row each) →
the generated index at the bottom of `README.md` (one row per ADR) → the one or two ADR
bodies the row points at. A session that reads all 25 bodies has paid for state the index
should have given it.

## The contract block

Every ADR carries one `ADR-CONTRACT` HTML comment directly under its `# ADR-NNNN: …` H1.
It is the machine-readable row the index is generated from.

```
<!-- ADR-CONTRACT
  decision: accepted (owner ruling YYYY-MM-DD) — <one clause, the result>
  code: built | partial | not built | n-a — <what proves it: the test file, not a claim>
  superseded-by: <NNNN (what it replaces) | —>
  amended-by: <NNNN (what changed this) | —>
  open: <what a reader must not assume is settled | —>
-->
```

Rules:

- **No leading indent** on the field lines, and **nothing else inside the block**. A legend
  written as `field: text` inside the block parses as data and beats the real value.
- `—` is valid for an empty relation; an empty `decision`, `code` or `open` is not.
- `superseded-by` / `amended-by` values are rendered as relation text, so keep them short
  (an `NNNN` and a few words). A long reason belongs in `open`.
- **The reciprocal is mandatory.** If `0008` says `amended-by: 0012`, then `0012` must say
  `amended-by` or `superseded-by` mentioning `0008` in the same commit. `tools/adr-check.py`
  fails when a relation is visible from one file and not the other.

## Body caps

- **≤ 24 lines above the first `## `** (the H1, the contract block, and at most a short
  status sentence). Every correction goes to the ledger; the header is the most-read
  location and must stay short.
- **≤ 500 words in `## Decision`.** The Decision is the immutable ruling. Options, rejected
  alternatives and non-licences are separate sections and are deliberately kept — they are
  what stops a session re-proposing a dead option.

## The ledger

Every dated correction, erratum, "Nth correction" and status fix moves to a `## Ledger`
section at the **bottom**, one dated line each, newest first, with the original claim quoted
where the quote carries the reasoning:

```
## Ledger

- 2026-09-29 · **correction** — the status asserted "no migration, model, enum or parser
  exists yet"; by EOD three of the four objects existed and `character_cards` was
  queryable-but-empty. Kept verbatim because the false claim is the evidence the drift
  guard was written from.
```

Preserve, never rewrite: the record that a reasonable author believed a thing is what makes
a later correction trustworthy.

## What an ADR does not contain

- **Line-number re-derivations** ("was `:101`, now `:82`"). Name the symbol; skip the line.
- **Implementation notes for a single slice.** They describe a moment, not a decision.
- **Narrative about the act of correcting.** The test names it; the ADR need not argue it.
- **Figures re-typed from config or the DB** (cap matrices, mood tiers, card counts, byte
  estimates). A number that moves with the data gets a pointer — the config key, the
  migration, the test — never a typed copy. This is the rule that would have prevented
  `ADR-0021`'s stale "roughly 51 MB" and `ADR-0014`'s 23-vs-41-vs-322 count saga.
