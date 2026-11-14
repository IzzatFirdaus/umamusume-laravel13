# GP-07b Design-research scratch review

2026-11-14. Read-only review of `docs/design-research/_scratch/` against
`docs/plans/frontend-colour-update-2026-11-14.md` and the grey-parity audit. One file written (this
memo); nothing under `_scratch/`, the plan, the audit, tokens, tests or source touched.

## Method and limitations

All hexes below are **declared or probe-recorded values read from tracked JSON**, never eyeballed:
`analysis/tokens/tokens.json` (derived anchors + WCAG block), `analysis/color/colorprobes*.json`
(pixel probes with named regions and coordinates, produced by `scripts/probe/probe*.py`), and
`analysis/color/accents.json` (per-frame accent clustering). Per the brief, declared values outrank
rendered pixels; no re-sampling was performed. PNGs carry no ICC profile (sRGB assumed — stated as
a limitation, not measured). `_scratch/` is peer evidence and reference, not a ratified spec
(brief rule 2/7); the game is the app's reference, never its target.

## Inventory

**Premise erratum:** the brief says the directory "has appeared". It did not: 31 of 71 files are
**tracked** (JSON/MD/PY; commits `b494d6fb` 2026-10-07 → `80025efe` 2026-11-05), and the 40 PNGs
are deliberately **gitignored** by `.gitignore:155` (`/docs/design-research/**/*.png`). The tree
has carried this corpus since 2026-09-27; it is the source the ratified palette already cites
(`DESIGN.md:153` "19 client sheets"; `app.css:128` cites `references/web/en-home-hero.png` by name).

| Group (count) | Type | Provenance class | Basis |
|---|---|---|---|
| `references/web/` (11 PNG, 1440x900) | game official-site captures (EN/JP/Global) | **Ratified-adjacent** | named in `app.css:128`; 2026-09-27 mtimes; marketing site, so UI-reference only where it shows UI |
| `references/sheets/` (19 PNG, 1500x1696) | the 19 client UI sheets | **Ratified-adjacent** | `DESIGN.md:153` cites "19 client sheets"; clean game UI |
| `analysis/tokens/tokens.json|.py` (2, tracked) | declared anchors, ramps, WCAG block | **Ratified-adjacent (strongest)** | declared values, derivation method in-file (`tokens.py:8`: "anchors measured in RAW-FINDINGS 3.2") |
| `analysis/color/colorprobes.json|.2` (2, tracked) | named-region pixel probes | **Reference-quality** | per-probe coordinates + source filenames in-file |
| `analysis/color/accents.json` (tracked) | per-frame accent cluster means | **Reference-quality** | per-frame file + shares recorded |
| `analysis/color/clusters.json`, `signatures.json` (2) | dHash clustering data | Ambiguous as colour evidence (structural, not colour-declaring) | no colour anchors inside |
| `analysis/color/cs_*.png` (3), `analysis/components/` (6 RGBA crops), `analysis/scenarios/scenario_chips.png` | contact sheets / component crops | Reference-degraded → evidence-of-shape only | derivatives of the corpus, useful for locating regions, not for hexes |
| `scripts/` (19 PY), `logs/triage.err`, root PS1/MD (8) | tooling + docs | n/a (method artefacts) | README/INDEX describe them |

## Extracted colour language (declared values only)

- **Ink anchors** (`tokens.json:14-15`): `ink-body #6A5641`, `ink-strong #482720` — warm brown;
  the derivation ramp puts `ink-body`'s 400-step at `#988F87` (`tokens.json:170`) = the HFW's
  `ink-faint` verbatim. Corpus WCAG block (`tokens.json:231-233`): ink-body on panel **6.56:1**,
  on white **6.95:1**, ink-strong on panel **12.49:1**.
- **Surfaces** (`tokens.json:16-18`): `surface-panel #F8F8FB`, `surface-idle #D2D2DB`,
  `surface-disabled #D0D1D0` — the last two identical to the HFW's `idle`/`disabled`
  (`shell.css:11`). Probe corroboration: `colorprobes2.json` `skill-card-idle-lavender #D2D2DB`.
  No declared `page` or `sunken` anchor exists in the corpus — the game's page/card arrangement is
  recorded only as near-white panel means in `accents.json` (`panel_white_mean #F7F7FB`–`#FAF9FC`
  across 9 frames).
- **Borders:** no hairline/rule anchor or probe exists in the corpus. Not extractable.
- **Semantic accents** (recorded, **out of scope** per the audit's deferral): action-green
  `#7FCC09`, confirm-green `#4E7906`, info-blue `#0088E0`, skill-cyan `#009FE1`, mood-great
  `#FB5590`, mood-good `#ED8036`, discount-orange `#FF9A2C`, selected-gold `#EFC96A`,
  grade-indigo `#351F70`, alert-crimson `#800014` (`tokens.json:3-13`), plus the probe set
  (e.g. `mood-pill-GOOD #ED8036`, `colorprobes.json:82-88`).
- **Dark mode:** **no artefact shows the game's dark UI.** Every capture is the light client.

## Comparison against app and plan

1. **Ink temperature** — warm, with high confidence at the anchor level: the corpus's own measured
   ink anchors (`#6A5641`/`#482720`) are byte-identical to the HFW set C1a targets. The cluster
   means in `accents.json` (`text_brown_mean` `#66505D`, `#503E57`, …) wander cooler/mauve because
   they average anti-aliased pixels against varied backgrounds — the declared anchors are the
   cleaned measurement and win. **Supports C1a as the game-faithful set; supports, does not
   compel** (C1 is a taste ruling, not a fidelity mandate).
2. **Surface polarity** — near-white panels on a tinted page, matching both the app's current light
   palette (`page #F4F4FF`, `panel #FBFBFD`) and the HFW's. No third arrangement in evidence; the
   corpus declares no page/sunken tiers of its own.
3. **Dark mode** — **not answerable from this material.** No dark-UI capture exists anywhere in
   `_scratch/`. No candidate is named because none can be evidenced; the app's dark theme
   (`app.css:484-495` at HEAD) is untouched by this review. Any dark-mode improvement is a **scope
   expansion** needing new reference material first.
4. **Contrast posture** — rough band: body ink sits at **6.5–7:1** (corpus's own block: 6.56/6.95)
   — the HFW's softer band, not the app's stricter ~13:1. Heading/strong ink is a higher tier
   (12.49). Screenshot-derived, therefore banded, not contractual.
5. **What the app does that the game does not** — the app's house style in the grey language is
   governance, not hue: a formal four-tier surface ramp (`page/panel/raised/sunken`), a dedicated
   non-text `ink-faint` tier, floor-guarded steps (1.05 light / 1.25 dark, `DesignTokensTest:903`),
   and per-pair contract rows (41 G-18 pairs). The corpus records anchors and accents only — no
   tier contract, no rule/hairline language, no floors. That governance is what the plan's slices
   must continue to satisfy, whatever ink the owner picks.

## Evidence classification against the plan

**Confirms the plan**, with one strengthening note and no new work:

- Nothing in the colour plan needs to change: C2's findings, the guard structure, and the surface
  decisions are untouched by this corpus (it ratifies no surface or rule values the app lacks).
- **C1 strengthening note:** if the owner takes C1a, its target set is not merely the HFW's taste —
  it is the corpus's own measured ink language, from the same reference body the app's brand
  ruling already cites. That is an argument *for* C1a's fidelity, recorded here for the ruling;
  it does not move any figure, and C1 remains declined-by-default until the owner rules.
- No dark-mode candidate is enabled (no material). No C2 impact. No challenge to any ratified
  decision; none is re-opened on screenshot evidence.

## Verdict and owner question(s)

- **Does `_scratch` change the current plan? No.** Reviewed-and-parked, provenance noted above for
  any future colour pass. The one open ruling C1 stands exactly as the plan left it; this corpus
  adds fidelity evidence to C1a's "for" column, nothing more.
- **Owner question the corpus raises (optional, not new):** if the C1 ruling is taken, does the
  owner want the full HFW four-value set (C1a as written) or the corpus-faithful subset that omits
  `ink-faint` (whose HFW value `#988F87` is also the corpus's `ink-body`-400 — the two sources
  agree here, so this collapses to "no difference"; recorded for completeness).
- **What could not be established:** the game's dark UI (absent); any rule/hairline language
  (no anchor or probe exists); the page/sunken tier arrangement (never declared); colour space
  beyond sRGB assumption (no ICC profiles).

## Coverage limitations

- Declared/probe values are 2026-10-08-era extractions of 2026-07-14/17/18 captures; version drift
  since then is unmeasurable from this material.
- `accents.json` cluster means are background-contaminated (noted above) and were not used for any
  conclusion.
- Contact sheets and component crops were not pixel-sampled (Reference-degraded; declared values
  outrank them per the brief).
- `references/web/` is the official marketing site — used here only as corroborating context for
  provenance, never as UI-colour evidence.
