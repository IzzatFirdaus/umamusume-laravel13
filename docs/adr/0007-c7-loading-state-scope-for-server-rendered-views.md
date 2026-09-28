# ADR-0007: C-7 loading-state scope for server-rendered views

Status: **ACCEPTED** (owner ruling 2026-09-28, recorded here)
Date: 2026-09-28
Deciders: product owner (ruling), pre-dev agent (recording)
Supersedes: nothing; narrows the interpretation of `CONSTRAINTS.md` C-7
Related: `CONSTRAINTS.md` C-7, `docs/GATE-REGISTRY.md` (C-7 entry), root `DESIGN.md` §3/§7, antislop R-27

## Context

C-7 requires every data view to render empty, loading/refresh, and error states
(antislop R-27). The audit of 2026-09-28 found no custom loading states in any
committed Blade view (`grep -l "loading" resources/views/` empty). The app is
primarily server-rendered: the first paint of `/umamusume`, `/training-runs`, and
`/review` is a complete HTML document; there is no client fetch to indicate for.

Two ways out existed: fake skeleton components to satisfy the letter of C-7, or
narrow its scope to what the architecture can actually be doing while loading.
Faking skeletons was rejected: a skeleton that never corresponds to pending work
is decoration without purpose (antislop purpose gate) and hides nothing, since
the server already has the data when it responds.

## Decision

C-7's loading obligation is interpreted as follows, without weakening the gate:

1. For initially server-rendered pages, browser-native loading (blank-to-paint)
   satisfies the loading-state obligation for the first navigation. A custom
   in-page loading indicator is not required for initial render.
2. Custom loading states are required for user-initiated asynchronous
   operations: fetch/refresh dispatch (the stale-while-revalidate path in
   `ARCHITECTURE.md` §6), background job status, save/submit that stays on the
   same page, any partial/HTMX-style update, and any future client-side
   mutation. Today the refresh affordance itself is still unimplemented, so this
   clause binds the moment it lands.
3. Empty, error, and data states remain mandatory for all data views, unchanged.
4. Skeletons must not be added solely to satisfy the checklist; a loading state
   must correspond to real pending work.

## Alternatives Considered

| Option | Description | Why rejected |
|---|---|---|
| Add skeleton components everywhere | Literal C-7 compliance | Technique without purpose; ships decoration and implies async work that does not exist (antislop purpose gate, R-01) |
| Delete the loading clause from C-7 | Relax the bar | Weakens a written constraint to pass a check, forbidden by `CONSTRAINTS.md` preamble; user-initiated async genuinely needs indicators |
| Do nothing and record FAIL forever | Honest but inert | Blocks every frontend slice exit on a non-issue instead of scoping the requirement |

## Consequences

### Positive
- Frontend slices stop being blocked by an unsatisfiable-in-spirit reading of C-7.
- The real obligation (async feedback for refresh/job status) is stated more sharply than before.

### Negative
- The gate is no longer greppable as "contains a loading element"; review must judge which
  interactions are async. `docs/GATE-REGISTRY.md` records it as a Review-type check.

### Risks
- A future view could claim "server-rendered" while doing client fetches. Mitigation: the
  registry requires the async-interaction list to be enumerated per view in design review.

## Implementation Notes

No code change is demanded by this ADR alone. When the manual-refresh affordance ships
(`ARCHITECTURE.md` §6 stale-while-revalidate), its in-flight indicator is mandatory under
clause 2, and the KI list tracks that work item separately.

## Verification

Binary review check, registered as G-C7 in `docs/GATE-REGISTRY.md`: for each data view, the
four states are enumerated with the clause number that applies (1 for initial render, 2 for
each user-initiated async action); absence of a custom indicator is only a pass when no
async action exists on the page.
