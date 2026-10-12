# ADR-0006: Design authority and theme default

<!-- ADR-CONTRACT
  decision: accepted (owner ruling 2026-09-27) — Option 2, Light base with preference resolution; root DESIGN.md is the visual authority
  code: built — resources/css/app.css @theme; DESIGN.md §2.1; tests/Feature/DesignTokensTest.php
  superseded-by: —
  amended-by: —
  open: the three lower mood pills' colours in DESIGN.md §6.17 are derived, not measured
-->

Status: **ACCEPTED** — Option 2 (Light base + preference resolution, dark opt-in), owner ruling
2026-09-27. **Both rules are folded into root `DESIGN.md` §2.1** and this file is a pointer to them.
The full body — the two same-day reversals, the four alternatives, the verification checks — is
preserved at `archive/adr/0006-design-authority-and-theme-default.md`.

## The two rules

1. **Authority split.** Root `DESIGN.md` is the single source of truth for the app's visual contract
   (what ships). `docs/research-scratch/DESIGN-CORPUS.md` section "DESIGN.md" is the research artifact:
   measured anchors, ramps and contrast math the root file cites. Neither rewrites the other's body; a
   superseded claim is annotated with a `> [!SUPERSEDED]` blockquote naming the overriding document,
   never edited in place (D5). The annotation is the whole reconciliation mechanism — there is no silent
   winner, and a later reader can see that a claim was overridden rather than never having existed.
2. **Theme default.** Light is the base palette because the client is high-key (research D-100), with dark
   as a measured palette of its own and an opt-in resolved stored preference → `prefers-color-scheme` →
   light (D-104). Light is not a forced default: a Trainer on a dark OS gets dark.

## Why the fold and not a stub that repeats them

These are design rules, not architecture decisions, and nothing else in the corpus cited this ADR. A
second copy in `docs/adr/` is a second source for one rule — the exact defect the authority split above
exists to prevent. The rules now have one home (`DESIGN.md` §2.1) and this file names it.

## Alternatives rejected

| Option | Why rejected |
|---|---|
| Keep both claims standing | D5 violation; downstream agents cannot tell which to build |
| Precedence by timestamp | Newest file wins silently — hides the conflict future readers need to see |
| Merge into one document | Destroys measured-anchor history and the research phase's provenance trail |

## Links

- Root `DESIGN.md` §2.1 (both rules live here), §6.17 (the derived pill colours)
- `docs/research-scratch/DESIGN-CORPUS.md` section "DESIGN.md" §3, §3.7; D-100, D-104
- `CONSTRAINTS.md` C-7; full body: `archive/adr/0006-design-authority-and-theme-default.md`
