# `docs/research-scratch-source`

Scratch source tree for the Umamusume reference research pass: raw HTML/JSON captures, reports, reorganization manifests, and scripts used to assemble and verify `docs/UMAMUSUME_REFERENCE.md` and related docs. Working artifacts, not shipped source.

## Layout

| Path | Purpose |
| --- | --- |
| Root | Reports, inventories, raw JSON datasets, and manifests |
| `data/` | Captured HTML/JSON source data and its manifest |
| `data/html/` | Game8 and official-site HTML captures plus a few JSON extracts |
| `data/json/` | GameTora/export JSON datasets (races, characters, mobs, missions, etc.) |
| `reorg/` | Reorganization manifest and rewrite journal |
| `scripts/` | Python scripts for assembly, generation, patching, probing, and verification |
| `INDEX.md` | Full 133-file inventory with sizes and links |

## File counts

- Total files: 133
- Python scripts: 31
- JSON: 63
- HTML: 26
- Markdown reports: 6
- CSV: 1
- TSV: 1
- TXT: 4
- JS: 1

## Root files

Reports and datasets at the tree root:

- `closeout-pass-screenshots-team-editor-hygiene-2026-10-16.md` — closeout pass report
- `reference-integration-report-2026-10-15.md` — reference integration report
- `scenario-field-metadata-report.md` — scenario field metadata report
- `scenario-field-metadata-wiring-report-2026-10-16.md` — wiring report
- `scenario-field-metadata-closeout-report-2026-10-16.md` — closeout report
- `race-field-data-report.md` — race field data report
- `screenshots-inventory.csv` — screenshot inventory
- `sec2-secondsource.tsv` — second-source crosscheck data
- `link-report.txt` — link sweep report
- `metrics.txt` — gate metrics
- `docinv_xref.json` — documentation inventory cross-reference
- `scenario-field-metadata-2026-10-15.json` — scenario field metadata
- `race-field-data.json` — large race field dataset (about 1.3 MB)

## `data/`

- `data/manifest.json` — content-hash manifest for captured data files
- `data/html/` — 32 files, mostly Game8 HTML captures such as `game8-536520.html`, plus `g8-news.html`, `official-news.html`, `reddit-tiers.json`, `umaguide-agenda.html`, and a JSON extract
- `data/json/` — 54 files, including character, card, support-card, race, race-instance, track, scenario, mission, mob, gacha, event, training-event, and localization datasets

## `reorg/`

- `reorg/manifest.json` — planned move map for reorganizing the former `research-scratch` tree
- `reorg/rewrite-journal.json` — citation rewrite journal

## Scripts

The 31 Python scripts cover assembly, generation, patching, probing, and verification. Major groups:

- Assembly/build: `assemble.py`, `build.py`, `merge_ux_docs.py`, `verify_ux_merge.py`
- Reference generation: `gen_roster.py`, `gen_calendar.py`, `gen_scenario_spine.py`, `replace_g1.py`, `resync_doc.py`, `splice_calendar.py`
- Patching: `patch_final.py`, `patch_round2.py`, `embed_docsync.py`
- Data decoding/probing: `cap_decode.py`, `decode_races.py`, `facprobe.py`, `probe_targets.py`, `recompute.py`, `strategy_truth.py`, `truthcheck.py`
- Fetching: `fetch_global_races.py`
- Link and gate checks: `checklinks.py`, `linkcheck.py`, `gates.py`, `final_measure.py`, `measure_plan_refs.py`
- Reorganization: `reorg_scratch.py`
- Utilities: `conflict_stats.py`, `finalize_terms.py`, `scrub_emoji.py`, `fix`-style helpers

Each script has a module docstring; see `INDEX.md` for the complete file list.

## Source input

Scripts generally read from `research-scratch/data/json/` or `docs/game-screenshots` and write outputs under `research-scratch/` or `docs/`. Many paths are hard-coded.

## Notes

- The reorg manifest describes a planned move from the old `research-scratch/` layout; this `research-scratch-source/` tree is the captured source copy.
- `data/manifest.json` records content hashes for captured datasets.