# ADR-0006: Design authority and theme default

Status: **ACCEPTED** — Option 2 (Light base + preference resolution, dark opt-in)
Date: 2026-09-27
Deciders: product owner (ruling 2026-09-27), pre-dev agent (drafting)
Supersedes: sprint-authorization ruling "dark-first is the app contract" (2026-09-27 same-day)
Related: root `DESIGN.md` §2.1, `docs/design-research/DESIGN.md` §1 and §3.7,
`docs/design-research/CONSTRAINTS.md` D-100 and D-104, `docs/adr/0002`, `docs/adr/0004`, `CONSTRAINTS.md` C-7

## Context

Two documents stated the app's visual system and disagreed about authority and about the default
theme. The evidence, in order of arrival:

1. Owner ruling (interview, 2026-09-27, earlier): "Theme: Dark mode default." Root `DESIGN.md`
   was created dark-first and promoted the research package's measured raceboard dark anchors.
2. The documentation audit flagged the collision with `docs/design-research/DESIGN.md` §3.7
   ("Light is the default and stays the default", D-100).
3. Sprint authorization (this session) ruled: root `DESIGN.md` is the single source of truth,
   dark-first is the app contract, and research §3.7 gets a `> [!SUPERSEDED]` annotation.
4. Concurrently (same day, now in the tree): root `DESIGN.md` §2.1 was revised to state the
   opposite default: "Light is the base palette (owner ruling 2026-09-27, reversing the earlier
   dark-first promotion) because the client is a high-key interface (research D-100)", with dark
   as a measured opt-in via a preference resolver (`D-104`: stored preference ->
   `prefers-color-scheme` -> light). That resolver is being implemented now in the working tree.

Both same-day rulings carried owner provenance; the newer disk state added a franchise-fidelity
argument (client corpus median luminance 193/255) and a technical one (bright brand lime is
accessible on dark, muted greens on light; see research §3.4/§3.7).

## Decision

1. **Authority split.** Root `DESIGN.md` is the single source of truth for the app's visual
   contract (what ships). `docs/design-research/DESIGN.md` is the research artifact: measured
   anchors, ramps, and contrast math that the root file cites. Neither rewrites the other's
   body; a superseded claim is annotated with a `> [!SUPERSEDED]` blockquote naming the
   overriding document, never edited in place (research history stays intact, D5).

2. **Theme default: Option 2 — Light base + preference resolution, dark opt-in.**
   The disk state is the truth. The implementation work is already in flight (app.css +223 lines,
   layout edits, preference resolver). Reversing to Option 1 now would mean throwing away a day's
   work for a same-day reversal that itself reversed an earlier same-day ruling.

   **Reasoning:**
   - Franchise fidelity: the client corpus is high-key (median luminance 193/255). Light base
     matches the game's visual language at first glance.
   - The "reduce eye strain" preference is served by the per-user setting (D-104 resolver),
     not by forcing dark as the default.
   - The preference resolver (stored preference → `prefers-color-scheme` → light) is the correct
     technical pattern regardless of which theme is default.
   - Two same-day reversals is the demonstrated risk; Option 2 is what's implemented, so it's
     what ships.

3. **Annotation application.** The `> [!SUPERSEDED]` blockquote applies to the sprint-authorization
   ruling (the "dark-first is the app contract" line), not to research §3.7.
   Root `DESIGN.md` §2.1 stays as-is (light base, owner ruling 2026-09-27).
   Research `docs/design-research/DESIGN.md` §3.7 stays as-is (measured light anchors, intact).

   **Closing statement:** "Light is the base palette. Dark is a measured opt-in via preference
   resolution. This matches the client's high-key visual language and serves eye-strain concerns
   per-user rather than by default."

## Alternatives Considered

| Option | Description | Why Rejected |
|---|---|---|
| Keep both claims | Leave root and research contradicting | D5 violation; downstream agents cannot tell which to build |
| Precedence by timestamp | Newest file wins silently | Works this time, hides exactly the conflict future readers need to see; violates "do not pick a winner silently" (escalation table) |
| Merge into one document | Fold research into root | Destroys measured-anchor history and the research phase's provenance trail |

## Consequences

### Positive
- One authority rule ends the DESIGN.md ambiguity class permanently.
- The implemented theme-resolver work (D-104) requires no re-verification.

### Negative
- The earlier "dark-first" sprint-authorization ruling is formally superseded; its
  documentation must carry the blockquote.

### Risks
- A third same-day ruling arriving mid-implementation is now a demonstrated risk in this worktree
  (two concurrent documentation threads committed 8 docs commits today). Mitigation: this ADR is
  the single reconciliation point; cite it, not chat.

## Implementation Notes

For the implementer: apply exactly the one blockquote named above (sprint-authorization line),
update root `DESIGN.md` header status line and this file's Status field to ACCEPTED, and run
the `make lore` and `tools/` gates. No research body edits.

## Verification

Binary checks:
1. `grep -c "SUPERSEDED" docs/design-research/DESIGN.md DESIGN.md` is exactly 1, in the sprint-authorization artifact.
2. Root `DESIGN.md` §2.1 and this ADR state the same default (light base).
3. The theme resolver's fallback order in code matches the accepted option, proven by an HTTP
   check of a catalog page with and without a stored preference (the same evidence class as
   KI-1's resolution).

(End of file - total 102 lines)