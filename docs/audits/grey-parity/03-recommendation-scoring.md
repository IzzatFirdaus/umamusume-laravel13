# GP-03 Recommendation scoring

The trigger's recommendation was scored as requested, then checked against the tree: **items 1 and
2 were already implemented in `bc102252` (2026-11-05)**, together, in one commit, with the
re-measurement the document warned would be needed. Scoring below is therefore retrospective:
each item is scored (a) as proposed against the 2026-10-31 tree, and (b) as executed.

## Change 1 — `panel` `#FFFFFF` → `#FBFBFD`

- (a) As proposed: sound. Restores the HFW's half-step below `raised`; collapses no pair; the
  predicted `panel→sunken` drop recomputes to exactly 1.130:1 and the post-change `panel on page`
  step is 1.057:1, clearing the recorded light floor of 1.05 (`DESIGN.md:190`, enforced at
  `tests/Feature/DesignTokensTest.php:903`) by 0.007.
- (b) As executed: **verified sound**. State B arithmetic in 01; `DesignTokensTest` pins the step
  floor live (`:903`) and the boundary row (`:894`).
- **Score: correct as proposed, correctly executed.** The doc's threshold note ("about 1.13") was
  right, but its framing understated the real guard: the binding floor is 1.05, not the assumed
  ~1.10 — so the change cleared the contract with more room than the doc believed (GP-04).
- Tests pinning `panel` or a dependent ratio (Phase 1 grep): `tests/Feature/DesignTokensTest.php`
  is the only file in the suite that pins grey tokens or their ratios (resolved-token reads,
  `contrastRatio()` at `:594`, surface step at `:887-914`, boundary at `:894-899`). It pins
  *floors*, not hex values, for these pairs, so the change did not require a test edit —
  the honest-update question is moot, and the guard is a genuine one (it would have failed a
  change that dropped the step below 1.05). `git grep` for `F4F5F9|FBFBFD|F4F4FF|EDEDF1` across
  `resources/js` returns nothing: no component hardcodes these hexes.

## Change 2 — `page` `#F4F5F9` → `#F4F4FF`

- (a) As proposed: sound, with the one risk the doc named: `rule on page` moves. Measured, not
  assumed: `rule #8C8A9A` on `page #F4F4FF` is **3.093:1**, margin **+0.093** over the 3:1 floor
  (WCAG 2.2 SC 1.4.11). Thin but passing, as the doc predicted.
- (b) As executed: **verified** — and the commit went further than the doc: it corrected the stale
  comment figure (3.19 → 3.10 on the old page) and re-measured `rule-soft` (1.30:1, unchanged) —
  exactly the discipline the document asked for ("re-measure the four boundary ratios afterwards").
- Post-change `page→sunken`: 1.069:1 — same ramp order as HFW (page above sunken), no inversion.
- Brand/status clash: none found. `page` is a background; the semantic families (status, mood,
  pick, goal, brand) sit on panels/cards, and no test or token comment depends on the old page hex.
- Tests pinning `page`: same single file, same floor-based guards (`DesignTokensTest.php:894-914`).
  No test edit was required; the boundary assertion now asserts a figure (3.09) that is 0.09 from
  its floor — a genuine guard worth its existence (GP-05).

## Change 3 — ink family (declined by the doc; still declined by the tree)

- Blast radius confirmed from the tree: `ink`/`ink-strong` are consumed by every screen through
  `body { color }` and the Tailwind theme; both other themes re-declare the ink ramp (dark
  `app.css:486-489`, eva01 `app.css:623-626`), so a light-ink move is a three-theme re-theme, not a
  light-block fix.
- Contrast figures in `app.css` that would need re-measurement: every recorded pair that names
  `ink`, `ink-strong`, `ink-muted` or `ink-faint` in the light block's comments (the muted-tier
  4.5 rows, the faint non-text note at `app.css:145-151`, the rule pair at `:153-168`) plus the
  `DesignTokensTest` ink pairs. The doc's "every contrast figure recorded" framing is confirmed.
- The doc's "re-theme, not parity fix" framing: **confirmed**. The HFW ink is warm brown
  (`#6A5641`, 6.73:1 on its card); the app's is near-black (`#322A2F`, 13.48:1 on the new panel).
  Both clear WCAG AA with room; this is taste and tone, not accessibility.
- **Score: decline confirmed.** Nothing in the parity goal requires it; cost is a full re-theme
  and re-measurement of every ink pair in three themes.

## Verdict block (required)

- After (1)+(2): does the app achieve "screen and wireframe read alike"? **Surfaces: yes — the
  four desktop pairs read with the same surface language, verified visually. Ink: no.** `ink on
  panel` is 13.48:1 (app) vs 6.73:1 (HFW); body text is cool near-black vs warm brown. (1)+(2)
  close the surface gap, not the ink gap — exactly as the document itself stated.
- Overall score on (1)+(2): **worth shipping — and already shipped correctly** (`bc102252`,
  2026-11-05). No pending action on the palette. The remaining "read alike" delta is the ink
  family, which stays an owner call (re-theme), not a parity defect.
