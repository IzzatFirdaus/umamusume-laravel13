# Frontend Stat Colours & Dark Mode Integration Plan
<!-- PLAN-CONTRACT
plan: frontend-stat-colours-and-dark-mode-integration
status: landed
slices: —
superseded-by: —
amended-by: —
open: —
-->

2026-11-14. Audit-derived plan; no code written. One material premise correction up front, then the
plan the tree actually needs. Evidence: `file:line` throughout; every ratio computed (WCAG 2.x,
inputs shown in the tables).

## State at authoring (current tokens, tests, gaps)

- **Premise correction — the stat colour system already exists.** The brief's Phase 3 "design the
  custom properties" is largely built: light `--color-stat-*` at `app.css:246-252`, light tint bgs
  `:255-259` (five stats) + `--color-tint-*` `:308-313` (six), light `--color-line-*` `:314-319`,
  the full dark set (stat `:524-530`, stat-bg `:533-537`, tint `:545-550`, line `:551-556`), an
  eva01 lifted stat set `:661-667`, and `.stat-*` classes `:1515-1547`. Per-component type chips
  (`--color-type-*`, `:326-332`) also exist. The brief's "official base hexes" are a *different*
  set from the shipped values (table in Phase 3) — the shipped ones are the `icons_svg` gradient
  extractions with four darkened in light to clear the 3:1 boundary (`app.css:130-134`).
- **The gap is adoption, not tokens:** exactly two components consume any of it
  (`TrainingCard.vue:113-119` `border-line-*`; `SupportTypeMark.vue` type tiles). The other ~22
  stat-rendering files print stats in plain `text-ink`. `--color-stat-*-ink` (a darkened *text*
  tier) does not exist in any theme. Dark tint bgs are flat neutrals (`#1C1C1E`…), not chromatic.
- **Dark surfaces (verified, brief asked):** `app.css:484-493` — page `#09090B`, panel `#2A2A2A`,
  raised `#333333`, sunken `#050406`, ink `#FCFCFC`, muted `#A0A0A0`, faint `#605F5F`.
  Provenance is the **mockups' charcoal** (`app.css:475-482`), **not** HLF — the brief's "dark:
  established from HLF_reference/dark_mode" is wrong for current values; HLF was the superseded
  2026-10-24 palette (`9fa70510` replaced it the same day). No drift: tokens match their recorded
  sources (`app.css:475-482` matches `:484-493` exactly).
- **Guards:** `DesignTokensTest.php` pins the theme-static token count at 95 (`:653`), the G-18
  pair list at 41 + exact keys (`:698-715`), with stat boundary rows already present (`:467-473`,
  computed 4.24–6.23 at minting). Tailwind v4 (`package.json`: `tailwindcss ^4.0.0`, no
  `tailwind.config.js`) — `@theme` tokens auto-generate utilities, so `bg-tint-speed`,
  `text-stat-speed` etc. already compile; zero Vue files use them.

## Phase 1: Stat display inventory (measured, not assumed)

| Surface | Where | Current styling |
|---|---|---|
| Training option cards (5 stats) | `resources/js/components/career/TrainingCard.vue:113-119` | per-stat literal `border-line-*` classes (literal-string rule, `:111-112`) |
| Support type chips (7 types) | `resources/js/components/support/SupportTypeMark.vue:58-…` | inline SVG gradient from `--color-type-*`; `color-mix()` inner shading (`:81,:89`) — colour-mix already proven in this stack |
| Deck/support surfaces | `CardPicker.vue`, `CardCompare.vue`, `DeckSummary.vue`, `SupportSlot.vue`, `pages/Support/Builder.vue`, `SupportCards/{Index,Show}.vue`, `pages/Career/DeckSelect.vue` | consume `SupportTypeMark` (token-driven via the component) |
| Stat values in career UI (~22 files: `TrainingDetail.vue`, `Preflight.vue`, `BuildTarget.vue`, `Snapshot/Setup.vue`, `Runs/Import.vue`, `CareerTimeline.vue`, `Result.vue`, `TurnEdit.vue`, `SaveVeteran.vue`, `Cockpit.vue`, `UraPreFinalReview.vue`, `StatSummary.vue`…) | value+label rows | plain `text-ink`/`text-ink-strong`; no per-stat colour anywhere |
| `StatBand.vue` | **does not exist** — only historical comments (`CareerStatePanel.vue:20`, `TrainingCard.vue:112`); `AGENTS.md` §6's list is stale | n/a |
| Shared StatBadge component | **none exists** (grep-confirmed; `StatSummary.vue` is a text strip, not a badge) | — |

## Phase 2: Dark mode enhancement (what "Neon Glow" would take)

The dark theme already carries the game's exact bright stat hues (`:524-530`) — as ink, they pass
text contrast on dark panel for 5/7 (stamina 4.32 and guts-adjacent 4.47 as *base* hexes; the
shipped `#F05B4F`/`#F45E96` measure 4.32/4.71). Real gaps vs the brief's strategy:

1. **Dark tint bgs are flat neutrals**, not the brief's chromatic ~15% base tints. The ratified
   dark rationale says tints stay tints and dark "neon" is box-shadow glow at ~30% mixed from each
   element's own accent token, "never a token fork" (`DESIGN.md:185-186`). Chromatic dark tints
   would contradict that ruling → **owner ruling required**, not planned silently.
2. **`--color-stat-*-ink` does not exist** (the darkened text tier the brief's light strategy
   needs). This is additive and uncontroversial.
3. **No glow layer exists.** Permitted only on icons/indicators per the brief; the ratified
   box-shadow mechanism (`DESIGN.md:185-186`) is the route.

**HLF alignment:** current dark values do not track HLF (see above); no re-alignment is proposed —
the mockup charcoal ramp is ratified and guard-tested (1.25 floor, `DesignTokensTest:903`).

## Phase 3: Token architecture (only the missing pieces)

New tokens (naming follows the existing `stat-*-bg` pattern), light block additions:

| Token | Value (derivation) | Ratios at mint (panel / own tint / own stat-bg) |
|---|---|---|
| `--color-stat-speed-ink` | `#166EAF` (base `#1F9DFA` −30% black) | 5.23 / 4.56 / 4.69 |
| `--color-stat-stamina-ink` | `#AD3E3B` (−30%) | 5.76 / 4.97 / 4.99 |
| `--color-stat-power-ink` | `#A15B10` (−35%) | 5.06 / 4.65 / 4.65 |
| `--color-stat-guts-ink` | `#AC3B63` (−30%) | 5.71 / 4.89 / 4.89 |
| `--color-stat-wit-ink` | `#0C784F` (−35%) | 5.33 / 4.98 / 5.05 |
| `--color-stat-pal-ink` | `#946300` (−40%) | 5.02 / n/a (no tint token) |
| `--color-stat-group-ink` | `#498219` (−35%) | 4.53 / n/a (no tint token) |

Owner-correction note (item 2): the first mint (panel-only, −20/25/30/35/40% steps) failed its own
tint on five of five tinted stats (speed 3.15:1 being the worst, caught by the G-18 browser gate on
the first Slice A run); the table above is re-minted against the shipped tint hexes, all seven
pairs ≥ 4.5:1 on every surface they may sit on.

Derivation base: the **brief's seven hexes**, whose provenance is **unverified** — the brief
asserts "the client HUD" but nothing in the screenshot corpus or registry supports that attribution
(owner correction 2026-11-14, item 3; the corpus measurement that *is* supported is the `_scratch`
anchor set, which measured different values). They are used here as derivation inputs only. All seven clear 4.5:1; shown values are the *lightest* passing mix
(5% search step, math above). Dark block: **no new ink tokens** — the shipped dark stat hues
already pass text on dark panel/tints (5.34–6.16:1 on the flat tints; base hexes 4.84–6.82:1 on
chromatic tints if the owner takes them).

Whose hexes win — owner question: the brief's seven differ from shipped `--color-stat-*` (speed
`#1F9DFA` vs `#2F94EE`, pal `#F7A500` vs `#CA8417`, …). The shipped light set exists *because* the
raw game hexes fail 3:1 on light surfaces (brief's own values measure 1.97–3.24:1 on panel —
**none pass 4.5 text, 4/7 fail even the 3:1 UI floor**). Recommendation: keep the shipped
boundary/ink sets, mint the `-ink` tier from the brief's raw set at the computed darkened values
above; do not replace the shipped `--color-stat-*`.

Tailwind mapping: none needed beyond `@theme` placement (v4 auto-generates `bg-/text-/border-`).
`color-mix()` is proven in-repo (`SupportTypeMark.vue:81,89`); the `-ink` tokens are static hexes,
not mixes, so they resolve without runtime mixing.

`DesignTokensTest` compatibility (honest-update test applied):
- Token count pin 95 (`:653`): seven new light tokens → **pinned value**, update to 102 with the
  rationale recorded in the commit (count is descriptive, not a floor).
- G-18 pair list 41 + exact keys (`:698-715`): add 7 rows (`stat-*-ink on tint-*` or `on panel`,
  type text, ≥4.5) → **genuine-guard additions** (new obligations, not weakenings); count update
  same commit. Surface-step and boundary guards untouched; grey tokens untouched (the brief's
  "existing grey surfaces" hold is respected — nothing in this plan touches `:136-173`).

## Phase 4: Sliced implementation plan

- **Slice A — text-tier tokens (no component changes). EXECUTED 2026-11-14** (correction pass
  folded in): seven light `-ink` tokens (re-minted per the owner's item-2 check), seven per-theme
  dark + eva01 `-ink` overrides (per-theme foreground step, the `--color-sp-ink` pattern — the
  G-18 gate caught the light-only first cut inheriting into dark at 3.15:1), 7 new G-18 text rows
  (41 → 48), both count pins 95 → 102. Files: `app.css`, `DesignTokensTest.php`; 0 `.vue`.
  Verified: full `DesignTokensTest` 16 passed / 450 assertions (three themes), Pint clean,
  `npm run build` clean. Dark "Neon Glow" note: dark/eva01 already carry chromatic
  `--color-stat-*-bg` tints (`:533-537`); only `--color-tint-*` is flat-neutral — the plan's
  earlier "flat neutrals" line applied to the `--color-tint-*` family only.
- **Slice B — one shared `StatBadge` component.** `resources/js/components/StatBadge.vue`:
  `<StatBadge stat="speed" variant="tint|line|plain" />` rendering label + optional value, token-
  driven (literal class strings per the Tailwind-scan rule). First consumers: `TrainingDetail.vue`
  and one `Snapshot/Setup.vue` row. Blast radius: 1 new + 2 edited `.vue`. New component test
  (classes per variant, token usage) + `npm run typecheck`. Rollback: delete component, revert 2.
- **Slice C — rollout to the ~20 remaining stat-rendering files**, stratum by stratum
  (career pages → import → timeline/result). Blast radius: ≤12 `.vue` per sub-slice; screenshot
  regression set per §9 (session-private harness). Rollback: per-file revert; no token coupling.
- **Slice D — dark "Neon Glow" (ruling-gated; do not schedule without it).** If the owner overrides
  the `DESIGN.md:185-186` flat-tint rationale: chromatic dark tint values (base @15% over
  `#1C1C1E`-family, computed 4.84–6.82:1 text) + optional glow on icons only. Blast radius: dark
  block + test rows + whichever components landed in B/C. Rollback: revert dark block; light
  unaffected. **If declined: dark mode needs nothing — the shipped dark stat layer already passes
  every applicable floor; record that and stop.**

## Coverage limitations & open questions for the owner

1. **Whose seven hexes are "official"?** Brief set vs shipped `icons_svg` set differ; both trace to
   game sources (the brief's from the client HUD, the shipped from the authored SVG icons). Plan
   keeps shipped for boundaries, uses brief values only as `-ink` derivation bases.
2. **Slice D contradicts a ratified rationale** (`DESIGN.md:185-186`) — explicit ruling required;
   this plan does not assume it.
3. `AGENTS.md` §6 still lists `StatBand.vue` as a live component — stale doc, erratum candidate
   (doc hygiene, not this plan's write).
4. Screenshot regression for stat colours exists only as the §9 session-private harness pattern;
   no per-stat visual spec exists today (named gap for Slice B's test).
5. The brief's "10% bg / 20% border" opacities were evaluated as their shipped equivalents (tint
   hexes precomputed vs `color-mix` runtime): shipped static tints within ±4% luminance of the
   brief's mixes (e.g. speed base@10% ≈ `#E9F5FE` vs shipped `#E6ECF7`) — no observable fidelity
   gap; keeping static tokens avoids runtime mixing in classes.
