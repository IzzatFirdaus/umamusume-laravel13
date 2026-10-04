# Reorganization Plan for `docs/design-research/_scratch`

## Current State Analysis

| Category | Count | Examples |
|----------|-------|----------|
| Python scripts (analysis/patching) | 21 | `patch_*.py`, `probe*.py`, `cluster.py`, `tokens.py`, `triage.py` |
| JSON data (analysis outputs) | 6 | `clusters.json`, `colorprobes*.json`, `accents.json`, `signatures.json`, `tokens.json` |
| PNG images - Design system sheets | 19 | `sheet_1of19.png` ... `sheet_19of19.png` |
| PNG images - Color/cluster analysis | 3 | `cs_bigclusters_1of1.png`, `cs_wide_1of2.png`, `cs_wide_2of2.png` |
| PNG images - Scenario chips | 1 | `scenario_chips.png` |
| PNG images - Legacy UI crops | 6 | `affinity_*.png`, `legacyslots_*.png`, `rank_band_*.png`, `sparks_head_*.png`, `statband_*.png` |
| PNG images - Web UI references (EN/JP) | 10 | `en-home-*.png`, `jp-home-*.png`, `global-*.png` |
| Error log | 1 | `triage.err` |

## Proposed Folder Structure

```
docs/design-research/_scratch/
├── analysis/
│   ├── color/              # Color clustering, probes, accents
│   │   ├── clusters.json
│   │   ├── colorprobes.json
│   │   ├── colorprobes2.json
│   │   ├── accents.json
│   │   ├── cs_bigclusters_1of1.png
│   │   ├── cs_wide_1of2.png
│   │   └── cs_wide_2of2.png
│   ├── components/         # UI component references
│   │   ├── statband_153801.png
│   │   ├── statband_154010.png
│   │   ├── rank_band_062511.png
│   │   ├── sparks_head_153801.png
│   │   ├── affinity_154010.png
│   │   └── legacyslots_153846.png
│   ├── scenarios/          # Scenario-related analysis
│   │   ├── scenario_chips.png
│   │   └── signatures.json
│   └── tokens/             # Design token analysis
│       ├── tokens.json
│       └── tokens.py
├── references/
│   ├── web/                # Web UI screenshots (EN/JP/Global)
│   │   ├── en-home-hero.png
│   │   ├── en-home-characters-bento.png
│   │   ├── en-characters-grid.png
│   │   ├── en-home-news-section.png
│   │   ├── en-news-listing.png
│   │   ├── global-home-1440x900.png
│   │   ├── global-news-list-1440x900.png
│   │   ├── jp-home-1440x900.png
│   │   ├── jp-home-hero.png
│   │   ├── jp-home-news-and-contents.png
│   │   └── jp-news-list-1440x900.png
│   └── sheets/             # 19-sheet reference set
│       ├── sheet_1of19.png ... sheet_19of19.png
├── scripts/
│   ├── patch/              # Data correction/transformation scripts
│   │   ├── patch_d185.py
│   │   ├── patch_events.py
│   │   ├── patch_owner.py
│   │   ├── patch_races.py
│   │   ├── patch_scenarios.py
│   │   ├── patch_scenarios2.py
│   │   ├── patch_screenshot_corrections.py
│   │   ├── patch_strategy.py
│   │   └── patch_vocab.py
│   ├── probe/              # Exploration/inspection scripts
│   │   ├── probe.py
│   │   ├── probe2.py
│   │   ├── scenario_probe.py
│   │   └── sheet.py
│   ├── analysis/           # Core analysis scripts
│   │   ├── cluster.py
│   │   ├── accents.py
│   │   └── triage.py
│   └── utils/              # Utility/fix scripts
│       ├── fix_order.py
│       └── fix_review.py
└── logs/
    └── triage.err
```

## Mapping Rules

| Pattern | Destination |
|---------|-------------|
| `sheet_*.png` | `references/sheets/` |
| `cs_*.png` | `analysis/color/` |
| `scenario_chips.png` | `analysis/scenarios/` |
| `signatures.json` | `analysis/scenarios/` |
| `accents.json`, `accents.py` | `analysis/color/` |
| `clusters.json`, `cluster.py` | `analysis/color/` |
| `colorprobes*.json` | `analysis/color/` |
| `tokens.json`, `tokens.py` | `analysis/tokens/` |
| `statband_*.png`, `rank_band_*.png`, `sparks_head_*.png`, `affinity_*.png`, `legacyslots_*.png` | `analysis/components/` |
| `en-*.png`, `jp-*.png`, `global-*.png` | `references/web/` |
| `legacy_crops/*` | `analysis/components/` (merged) |
| `web/*` | `references/web/` (merged) |
| `patch_*.py` | `scripts/patch/` |
| `probe*.py`, `scenario_probe.py`, `sheet.py` | `scripts/probe/` |
| `cluster.py`, `triage.py` | `scripts/analysis/` |
| `fix_*.py` | `scripts/utils/` |
| `triage.err` | `logs/` |