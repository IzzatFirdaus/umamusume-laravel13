# Screenshot corpus coverage

Generated `2026-10-13` by `tools/screenshots.py report`. Counts come from `inventory.jsonl` and `notes-index.jsonl`, never from this text.

## Corpus

| Measure | Value |
| --- | --- |
| Original screenshots on disk | 1159 |
| Readable | 1159 |
| Unreadable or unsupported | 0 |
| Unique content (SHA-256) | 1159 |
| Exact duplicate frames | 0 |
| Exact duplicate bytes redundant | 0 |
| Frames in the canonical clusters.json | 1159 |
| Frames with no cluster entry | 0 |
| Distinct dimensions | 946 |
| Capture span | 2026-07-14 025344 to 2026-07-18 161917 |

## Review states

| State | Count | Share |
| --- | --- | --- |
| `not_reviewed` | 1002 | 86.5% |
| `documented_unverified` | 0 | 0.0% |
| `verified` | 157 | 13.5% |
| `partial` | 0 | 0.0% |
| `skipped` | 0 | 0.0% |
| `duplicate_candidate` | 0 | 0.0% |
| **total** | **1159** | |

`documented_unverified` counts frames a legacy note names. It is not review coverage.

## Note reconciliation

| Measure | Value |
| --- | --- |
| Legacy note files parsed | 11 |
| Note references reconciled | 52 |
| Frames with at least one note | 52 |
| Frames with no note at all | 1107 |
| Unresolved note references | 0 |

Match classes: `exact` 50, `probable` 2

### Legacy note files

| Note file | Sections | Matched | Unresolved | Observation chars |
| --- | --- | --- | --- | --- |
| `00-race-entry-screenshots.md` | 10 | 10 | 0 | 21618 |
| `01-training-screenshots.md` | 14 | 14 | 0 | 32008 |
| `02-career-progression-screenshots.md` | 9 | 9 | 0 | 20406 |
| `03-skill-selection-screenshots.md` | 5 | 5 | 0 | 12604 |
| `04-character-detail-screenshots.md` | 3 | 3 | 0 | 6033 |
| `05-menu-screenshots.md` | 3 | 3 | 0 | 8091 |
| `06-other-screenshots.md` | 5 | 5 | 0 | 10044 |
| `PHASE-B-REPORT.md` | 0 | 0 | 0 | 0 |
| `Screenshot 2026-07-17 233324.md` | 1 | 1 | 0 | 2405 |
| `Screenshot 2026-07-18 002830.md` | 1 | 1 | 0 | 2066 |
| `Screenshot 2026-07-18 154126.md` | 1 | 1 | 0 | 1785 |

## Categories from notes

| Category | Frames |
| --- | --- |
| unassigned | 1107 |
| training | 14 |
| race-entry | 10 |
| career-progression | 10 |
| skill-selection | 6 |
| other | 5 |
| character-detail | 4 |
| menu | 3 |

## Dimensions

| Dimensions | Frames |
| --- | --- |
| 1920x1080 | 173 |
| 809x1079 | 8 |
| 810x1079 | 8 |
| 811x1079 | 3 |
| 689x951 | 3 |
| 1529x1079 | 2 |
| 685x964 | 2 |
| 698x975 | 2 |
| 689x960 | 2 |
| 698x962 | 2 |
| 700x967 | 2 |
| 700x986 | 2 |

## Near-duplicate clusters

From the canonical `clusters.json` (dHash, Hamming 6, 60-frame rolling window). A cluster is a perceptual group, not a byte identity: this corpus has **0 exact duplicate groups**, so every cluster below holds visually similar but distinct frames.

| Cluster | Size | Representative |
| --- | --- | --- |
| 725 | 58 | `2026-07-18 001811` |
| 80 | 38 | `2026-07-14 202142` |
| 66 | 23 | `2026-07-14 194819` |
| 64 | 17 | `2026-07-14 194531` |
| 707 | 14 | `2026-07-17 230755` |
| 708 | 14 | `2026-07-17 230902` |
| 60 | 13 | `2026-07-14 194158` |
| 749 | 11 | `2026-07-18 160543` |
| 7 | 10 | `2026-07-14 121144` |
| 62 | 10 | `2026-07-14 194326` |

Total distinct clusters on this corpus: 750.

## Unmatched note references

None.

## Outstanding coverage

| Bucket | Count |
| --- | --- |
| `not_reviewed` | 1002 |
| `documented_unverified` | 0 |
| `partial` | 0 |
| `skipped` | 0 |
| `duplicate_candidate` | 0 |
| `verified` | 157 |

The queue is generated, not typed: `tools/screenshots.py queue`.

