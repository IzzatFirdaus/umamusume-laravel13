# ADR-0006: Design authority and theme default

Status: **PROPOSED. The authority split is drafted for acceptance; the theme-default line is
BLOCKED on an owner reconciliation** (two owner rulings dated 2026-09-27 directly conflict; see
Context). Do not implement from the blocked line.
Date: 2026-09-27
Deciders: product owner (rulings of 2026-09-27, reconciliation outstanding), pre-dev agent (drafting)
Supersedes: nothing; resolves the authority collision surfaced by the documentation audit of
2026-09-27
Related: root `DESIGN.md` §2.1, `docs/design-research/DESIGN.md` §1 and §3.7,
`docs/design-research/CONSTRAINTS.md` D-100 and D-104, `docs/adr/0002`, `docs/adr/0004`, `CONSTRAINTS.md` C-7

## Context

Two documents state the app's visual system and disagree about authority and about the default
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

Both same-day rulings carry owner provenance; the newer disk state adds a franchise-fidelity
argument (client corpus median luminance 193/255) and a technical one (bright brand lime is
accessible on dark, muted greens on light; see research §3.4/§3.7). This ADR cannot pick between
two owner decisions, so it fixes what is compatible and isolates what is not.

## Decision

Accepted portion (drafted for owner sign-off; not in force until ACCEPTED):

1. **Authority split.** Root `DESIGN.md` is the single source of truth for the app's visual
   contract (what ships). `docs/design-research/DESIGN.md` is the research artifact: measured
   anchors, ramps, and contrast math that the root file cites. Neither rewrites the other's
   body; a superseded claim is annotated with a `> [!SUPERSEDED]` blockquote naming the
   overriding document, never edited in place (research history stays intact, D5).
2. **Annotation mechanics.** When the theme-default conflict below resolves, exactly one
   section receives the blockquote: either research §3.7 (if dark-first wins) or root
   `DESIGN.md` §2.1 (if light-based wins). The losing text is preserved under the notice.

## Decision (blocked: theme default)

| Option | Default | Consequence | What changes on disk |
|---|---|---|---|
| 1 | Dark-first (sprint-authorization ruling) | App diverges from the client's high-key look at first glance; raceboard character becomes the app's identity | Root `DESIGN.md` §2.1 reverts to dark base; research §3.7 gets the SUPERSEDED blockquote; D-104 resolver keeps working (pref flips to light opt-in); in-flight `app.css`/layout work re-verifies against dark base |
| 2 | Light base + preference resolution, dark opt-in (current disk state) | Matches client resemblance; the earlier "reduce eye strain" preference is served by the per-user setting instead of the default | Sprint-authorization line 2 is recorded as superseded by the later same-day revision; no file edits; ADR closes with Option 2 |

## Alternatives Considered

| Option | Description | Why Rejected |
|---|---|---|
| Keep both claims | Leave root and research contradicting | D5 violation; downstream agents cannot tell which to build |
| Precedence by timestamp | Newest file wins silently | Works this time, hides exactly the conflict future readers need to see; violates "do not pick a winner silently" (escalation table) |
| Merge into one document | Fold research into root | Destroys measured-anchor history and the research phase's provenance trail |

## Consequences

### Positive
- One authority rule ends the DESIGN.md ambiguity class permanently, whichever theme option wins.
- The blocked line is stated as a table, so the owner's choice is a one-row decision, not a debate.

### Negative
- Until ACCEPTED, frontend work continues against the current disk state (Option 2); choosing
  Option 1 later means re-verifying the in-flight theme-resolver work (bounded: the resolver is
  default-order, not theme-specific).

### Risks
- A third same-day ruling arriving mid-implementation is now a demonstrated risk in this worktree
  (two concurrent documentation threads committed 8 docs commits today). Mitigation: this ADR is
  the single reconciliation point; cite it, not chat.

## Implementation Notes

For the implementer, not this ADR: after ACCEPTED, apply exactly the one blockquote named in the
Decision table, update root `DESIGN.md` header status line and this file's Status field, and run
the `make lore` and `tools/` gates. No research body edits.

## Verification

Binary checks once ACCEPTED:
1. `grep -c "SUPERSEDED" docs/design-research/DESIGN.md DESIGN.md` is exactly 1, in the loser file.
2. Root `DESIGN.md` §2.1 and this ADR state the same default.
3. The theme resolver's fallback order in code matches the accepted option, proven by an HTTP
   check of a catalog page with and without a stored preference (the same evidence class as
   KI-1's resolution).
