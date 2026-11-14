# GP-00 Executive summary — grey-palette parity audit

2026-11-14. Report-only audit of `docs/design-research/GREY-SCALE-APP-VS-HFW.md` (the trigger).
No source, token, test or fixture file modified; deliverables only under `docs/audits/grey-parity/`.

## Headline

**The trigger is accurate and superseded by the tree.** All 16 of its figures reproduce exactly
against the commit it measured (State A, `ce90b0fa`, last commit before 2026-10-31 EOD +0800). Its
recommendation items 1+2 were **implemented two days after the doc was recorded**: `bc102252`
(2026-11-05, "HFW grey parity", Phase 7) names the document in its body and took options 1 and 2
together, with the re-measurement the doc itself warned for. Item 3 (ink) stays declined.

## Verdict

- 16/16 trigger figures MATCH (01). Items 1+2 as executed: **verified sound** — post-change
  `rule on page` 3.093:1 (+0.093 over the 3:1 floor); light surface step `panel on page` 1.057:1
  against the recorded 1.05 floor (`DESIGN.md:190`, enforced `DesignTokensTest.php:903`).
- Ship/don't-ship on (1)+(2): **already shipped, and worth what shipped.** No pending action.
- Read alike or closer: **surfaces read alike; ink does not.** Four screen pairs at two viewports
  confirm the shared surface language (tinted page, near-white panel, same border pair); ink stays
  the app's own family (`ink on panel` 13.48:1 vs HFW 6.73:1). (1)+(2) closed the surface gap, not
  the ink gap; the ink gap is a re-theme decision, not a parity fix.

## Top findings

1. **GP-01** (Medium) — the trigger carries no erratum noting its recommendation landed 2026-11-05;
   its "current" tables describe a tree that no longer exists.
2. **GP-04** (Low) — the doc's assumed ~1.10 tier threshold is not the contract: the recorded light
   floor is 1.05 (`DESIGN.md:190`); post-change step 1.057 clears it by 0.007.
3. **GP-03** — the brand-hairline open item resolves affirmatively: 1.41:1 reproduces on `#F8F9FD`,
   the page as it stood at the 2026-10-24 ruling. Never a discrepancy (01).

## Recommendation

None pending on the palette. Owner calls, not taken by this audit: append an erratum to the trigger
pointing at `bc102252` (its figures become historical), and record the ink family as the standing,
deliberate difference.

## Tree state (measured 2026-11-14)

- HEAD `d8238c87` (2026-11-14 22:02 +0800). Brief expected "near `6ff695fa`": `app.css` and
  `shell.css` are byte-identical between `6ff695fa` and HEAD, so the premise holds; proceeded.
- Working tree: 11 modified + 2 untracked, all peer-owned; none touched by this audit. Both colour
  files clean (`app.css` last `bc102252` 11-05; `shell.css` last `4828da2c` 10-22). Trigger doc
  clean, committed `78755486` 11-03.

## Coverage limitations (named, none implied away)

1. Phase 2 observations are structured vision-model readings of the PNGs, criterion-referenced;
   not pixel-difference arithmetic.
2. App sides render live seeded records; HFW sides static exemplars — same screen, different data.
3. `career-scenario.html` renders a dark-slate sidebar variant the app lacks; that pair compares
   canvas/cards/ink only (GP-08).
4. Light theme only, per brief; `dark`/`eva01` not audited.
5. Mobile pairs: all captured; dashboard pair individually inspected; the rest carried on desktop
   verdicts plus verified absence of media-query token overrides (01).
6. The committed browser contrast gate was not re-executed; its 1.05 floor was read from source and
   the arithmetic it enforces recomputed independently (pest-plugin-browser temp-path constraint,
   AGENTS.md 2026-10-23 note; the value is arithmetic, not state).
7. KI-74 pre-flight passed (timed SQLite read 0.37 s); dev DB never opened. All app captures ran on
   a session-private scratch DB (`.scratch-uma/grey-parity-2026-11-14/app.sqlite`, fresh seed).
