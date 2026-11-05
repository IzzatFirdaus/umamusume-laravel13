# `docs/design-research/_scratch`

Scratch space for design-research exploration: reference screenshots, color/component/token analysis, patch/probe scripts, and logs. These are working artifacts, not shipped source.

## Layout

| Path | Purpose |
| --- | --- |
| `analysis/color/` | Color clustering, accent isolation, and cluster visualizations |
| `analysis/components/` | UI component reference crops |
| `analysis/scenarios/` | Scenario chips and scenario signature data |
| `analysis/tokens/` | Design-token derivation and WCAG contrast checks |
| `references/sheets/` | 19-sheet reference screenshot set |
| `references/web/` | Web UI screenshots (EN/JP/Global) |
| `scripts/analysis/` | Core screenshot triage and clustering scripts |
| `scripts/patch/` | Scripts that patch design docs and ADRs |
| `scripts/probe/` | Pixel/color and scenario probing scripts |
| `scripts/utils/` | Small document and prototype fixes |
| `logs/` | Run logs |
| `INDEX.md` | Full 69-file inventory with sizes and links |

## File counts

- Total files: 71
- Python scripts: 19
- JSON analysis data: 6
- PNG references: 40 (`references/sheets`: 19, `references/web`: 11, `analysis`: 10)
- PowerShell scripts: 2
- Markdown: 3 (`README.md`, `REORGANIZATION_PLAN.md`, `INDEX.md`)
- Error log: 1

## Scripts

### `scripts/analysis/`

- `triage.py` — triage screenshot corpus by dimensions, color signature, and nearest-neighbour grouping
- `cluster.py` — cluster near-duplicate screenshots with dHash and build contact sheets
- `accents.py` — isolate UI accent colors by clustering high-saturation pixels per key frame
- `tokens.py` — derive Tailwind v4 ramps from measured anchors and compute WCAG contrast

### `scripts/probe/`

- `probe.py` — probe exact pixel colors in named UI regions
- `probe2.py` — second probe pass at full resolution
- `scenario_probe.py` — crop scenario chips from a stratified sample and tile them
- `sheet.py` — build labelled contact sheets from `clusters.json` filters

### `scripts/patch/`

- `patch_d185.py` — append grade-badge and UI corrections to `CONSTRAINTS.md`
- `patch_events.py` — add dynamic event resolution rules to `DESIGN.md`
- `patch_owner.py` — update ADR statuses and design sections
- `patch_races.py` — add fan readout and race calendar rules
- `patch_scenarios.py` — add scenario identity and stat cap rules
- `patch_scenarios2.py` — add scenario mechanics extracted from Game8 guides
- `patch_screenshot_corrections.py` — correct screenshot-derived design details
- `patch_strategy.py` — add strategy-driven UI rules
- `patch_vocab.py` — add stat vocabulary and mockup verification rules

### `scripts/utils/`

- `fix_order.py` — reorder component sections in `DESIGN.md`
- `fix_review.py` — fix append-last and empty-gain rendering in prototype HTML

## Analysis outputs

- `analysis/color/clusters.json` — screenshot cluster representatives and dimensions
- `analysis/color/accents.json` — isolated accent color data
- `analysis/color/colorprobes.json`, `colorprobes2.json` — pixel probe results
- `analysis/scenarios/signatures.json` — scenario signature data
- `analysis/tokens/tokens.json` — derived token ramps and contrast results

## References

- `references/sheets/sheet_1of19.png` ... `sheet_19of19.png` — 19-sheet reference set
- `references/web/` — EN, JP, and Global web UI screenshots
- `analysis/components/` — cropped component references such as stat bands and rank bands

## Source input

Most scripts read screenshots from `docs/game-screenshots` and write outputs under this scratch tree. Paths are hard-coded in each script.

## Notes

- `REORGANIZATION_PLAN.md` documents the intended folder structure.
- `debug-paths.ps1` and `reorganize-design-scratch.ps1` are PowerShell helpers.
- `logs/triage.err` contains triage error output.
- Mockup images are not a verified surface; HTML prototypes are authoritative for label correctness.