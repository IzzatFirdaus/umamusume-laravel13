# ADR-0007: C-7 loading-state scope for server-rendered views

<!-- ADR-CONTRACT
  decision: accepted (owner ruling 2026-09-28) — C-7's loading obligation is scoped to real async work; browser-native loading satisfies initial server render
  code: built — GOVERNANCE.md §"CONSTRAINTS.md (root quality bar)" C-7 row and its interpretation blockquote
  superseded-by: —
  amended-by: —
  open: the manual-refresh affordance it anticipates is still unimplemented, so clause 2 binds the moment it lands
-->

Status: **ACCEPTED** (owner ruling 2026-09-28). **The interpretation is folded into the bar** at
`docs/research-scratch/GOVERNANCE.md` §"CONSTRAINTS.md (root quality bar)" C-7's row and the blockquote
beneath it, and registered as a Review-type check in §"GATE-REGISTRY.md". The full body — the audit
finding, the three alternatives, the verification check — is preserved at
`archive/adr/0007-c7-loading-state-scope-for-server-rendered-views.md`.

## The rule

C-7's loading obligation is interpreted as follows, without weakening the gate:

1. For initially server-rendered pages, browser-native loading (blank-to-paint) satisfies the
   loading-state obligation for the first navigation. A custom in-page loading indicator is not required
   for initial render.
2. **Custom loading states are required for user-initiated asynchronous operations:** fetch/refresh
   dispatch, background job status, save/submit that stays on the same page, any partial update, and any
   future client-side mutation.
3. Empty, error and data states remain mandatory for all data views, unchanged.
4. Skeletons must not be added solely to satisfy the checklist; a loading state must correspond to real
   pending work.

## Why the fold and not a stub that repeats it

This is a reading of one bar entry, not an architecture decision, and nothing else in the corpus cited
this ADR. The rule belongs beside the threshold it interprets, and `CONSTRAINTS.md` is a pointer stub
whose own text says "Do not add rules to it" — so the target is the embedded bar in
`docs/research-scratch/GOVERNANCE.md`, which already carries the clause verbatim.

## Alternatives rejected

| Option | Why rejected |
|---|---|
| Add skeleton components everywhere | Technique without purpose; ships decoration and implies async work that does not exist (antislop purpose gate, R-01) |
| Delete the loading clause from C-7 | Weakens a written constraint to pass a check, forbidden by `CONSTRAINTS.md`'s preamble |
| Do nothing and record FAIL forever | Blocks every frontend slice exit on a non-issue instead of scoping the requirement |

## Links

- `docs/research-scratch/GOVERNANCE.md` §"CONSTRAINTS.md (root quality bar)" (C-7 row and the
  interpretation blockquote) and §"GATE-REGISTRY.md" (G-C7)
- Root `CONSTRAINTS.md` (the pointer stub); `DESIGN.md` §3, §7; antislop R-27
- Full body: `archive/adr/0007-c7-loading-state-scope-for-server-rendered-views.md`
