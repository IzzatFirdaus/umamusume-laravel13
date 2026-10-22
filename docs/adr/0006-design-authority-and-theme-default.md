# ADR-0006: Design authority and theme default

<!-- ADR-CONTRACT
  decision: accepted (owner ruling 2026-09-27) — Option 2, Light base with preference resolution; root DESIGN.md is the visual authority
  code: built — resources/css/app.css @theme; DESIGN.md §2.1; tests/Feature/DesignTokensTest.php
  superseded-by: —
  amended-by: —
  open: the three lower mood pills' colours in DESIGN.md §6.17 are derived, not measured; the authority ruling predates the framework corpus, so docs/framework-ui-ux/ now states visual rules of its own and no document says which wins (2026-10-22 framework check)
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
- `docs/framework-ui-ux/design-framework-index-2026-10-21.md` (the re-check table); rules SHR-1, UI-1, UI-7,
  UX-1

## Ledger

- 2026-10-22 · **framework check against `docs/framework-ui-ux/` — the authority ruling now has a third
  party it does not name.** Rule 1 above makes root `DESIGN.md` "the single source of truth for the
  app's visual contract", with `docs/research-scratch/DESIGN-CORPUS.md` as the research artifact. On
  2026-10-21 and 2026-10-22 the corpus `docs/framework-ui-ux/` was written, and it states rules that
  bind the same visual layer: **UI-1** (a screen has three type sizes and no more), **UI-7** (one
  accented element per screen), **UX-1** (a control is at least 44px), **UI-2** (every hue carries a
  meaning) and **UI-6** (space between elements carries the grouping, not a border). **None of these is
  a token, and all of them are house rules**, so the corpus is a second statement of the visual
  contract rather than a research artifact, and the ruling above does not say where it sits. Two
  further details make the collision concrete rather than theoretical: the framework index's own
  "Which framework to cite" table sends a reader with a type-scale or accent question to the framework
  file rather than to `DESIGN.md`, and its conflicts table settles rule conflicts against
  `docs/research-scratch/PROCESS-PLANS.md` §13 — a third document that this ADR does not name. **Per
  `AGENTS.md` §2 the conflict is recorded here rather than resolved silently, and per SHR-1 a
  consistency exception must be named in a document and be few.** What is owed is an owner ruling on
  the standing of the corpus: whether it is subordinate to `DESIGN.md` as a review instrument that
  cites it, and where a house rule such as the 44px target or the three-step type scale is restated
  in `DESIGN.md` so the two cannot drift. **Nothing in rule 1 or rule 2 is changed by this entry.**
