# Plan template

A plan records **what a slice or session intends to do and why**, as of its date. It is not a
decision record and not a status dashboard. Three things that used to share one file now live apart:

| Thing | Lives in | Changes |
|---|---|---|
| The intent | the plan body | never — it is the record of what an author believed on its date |
| What has happened since | the plan's `## Ledger` | append-only, newest first |
| What is true right now | the owning roadmap or session report | regenerated, never hand-typed |

Read order for a session: the roadmap index (current scope, one row per slice) → the plan body
for the slice in question → the ledger for what changed after the plan was written.

## The contract block

Every plan carries one `PLAN-CONTRACT` HTML comment directly under its `# PLAN-…` H1.
It is the machine-readable row a census or index can be generated from.

```
<!-- PLAN-CONTRACT
  plan: <short slug, e.g. implementation-plan-2026-11-16>
  status: drafted | in-progress | landed | dropped | superseded
  slices: <slice ids this plan covers, comma-separated | —>
  superseded-by: <plan file that replaces this one | —>
  amended-by: <plan file that changed this | —>
  open: <what a reader must not assume is settled | —>
-->
```

Rules:
- **No leading indent** on the field lines, and **nothing else inside the block**.
- `—` is valid for an empty relation; an empty `plan`, `status` or `open` is not.
- `superseded-by` / `amended-by` values are rendered as relation text, so keep them short.
- **The reciprocal is mandatory.** If `implementation-plan-2026-11-16.md` says
  `amended-by: implementation-plan-2026-11-17.md`, then the latter must mention the former
  in the same commit.

## Body caps

- **≤ 24 lines above the first `## `** (the H1, the contract block, and at most a short status
  sentence). Every correction goes to the ledger; the header is the most-read location and
  must stay short.
- **≤ 500 words in `## Intent`.** The Intent is the immutable plan. Options, rejected
  alternatives and non-licences are separate sections and are deliberately kept.

## The ledger

Every dated correction, erratum, "Nth correction" and status fix moves to a `## Ledger`
section at the **bottom**, one dated line each, newest first, with the original claim quoted
where the quote carries the reasoning.

## What a plan does not contain

- **Line-number re-derivations** ("was `:101`, now `:82`"). Name the symbol; skip the line.
- **Implementation notes for a single slice.** They describe a moment, not a plan.
- **Narrative about the act of correcting.** The test names it; the plan need not argue it.
- **Figures re-typed from config or the DB.** A number that moves with the data gets a pointer
  — the config key, the migration, the test — never a typed copy.
