# Screenshot research registry

Machine-readable, per-image registry for the original in-game screenshot corpus
(`docs/game-screenshots/`, 1159 PNGs, gitignored). It exists so that a future agent
can resume research across sessions without re-reading every historical note, and so
that visual inspection is reserved for images that actually need it.

This file is the schema, state model, and command reference. The data it describes
lives in the JSONL files beside it.

## Layout

| File | Owned by | Purpose |
| --- | --- | --- |
| `inventory.jsonl` | `screenshots.py inventory` | One record per original screenshot. Machine fields are regenerated; review fields are preserved. |
| `notes-index.jsonl` | `screenshots.py notes` | One record per (frame, note file) reconciliation, with match class. |
| `notes-files.jsonl` | `screenshots.py notes` | One record per legacy note file: sections found, matched, unresolved, observation size. |
| `review-log.jsonl` | `screenshots.py review` | Append-only history of every recorded review decision. |
| `batches/*.jsonl` | `screenshots.py review` | One file per batch, listing which ids it reviewed and with what result. |
| `COVERAGE.md` | `screenshots.py report` | Generated coverage dashboard. |
| `sheets/*.png` | `screenshots.py sheet` | Generated labelled contact sheets. |
| `sheets/*.json` | `screenshots.py sheet` | The cell -> frame mapping for each generated sheet. |
| `refs-index.jsonl` | `screenshots.py refs` | One record per frame cited in the repository docs: every doc that names it, the first citation, and whether it resolves to an inventory record. |
| `findings.md` | agent-authored | Scenario mechanic briefs, UI-flow catalog, and the outstanding evidence queue, each entry linked to frame refs. |
| `CHECKPOINT.md` | session | A concise, machine-shaped progress record for resuming; lists the state counts and the next concrete step. |

## Stable identity

- `ref` is the capture timestamp, the part of the filename after `Screenshot `
  and before `.png`. Every legacy note names a frame this way, so it is the
  human-facing key.
- `sid` is the first 12 hex chars of the file's SHA-256. It is content-derived,
  so it survives a rename or move. The registry stores both; the notes index
  links `ref` to `sid`.
- Never identify an image by filename alone. Two files that happen to share a
  stem but differ in bytes are different screenshots.

## Inventory record fields

The following keys are owned by the `inventory` subcommand and are overwritten on
each run. All others are preserved, which is what makes review progress durable.

| Key | Meaning |
| --- | --- |
| `schema_version` | Bumped when the record shape changes. |
| `sid` | First 12 hex of SHA-256. |
| `ref` | Capture timestamp (filename stem). |
| `filename` | Exact name on disk. |
| `path` | Repository-relative path. |
| `ext`, `format` | File extension and lowercase format tag. |
| `sha256` | Full SHA-256. Empty if the file is unreadable. |
| `width`, `height` | PNG IHDR dimensions, `null` if unknown. |
| `bytes` | File size in bytes. |
| `dhash` | Perceptual hash in hex, matching the construction in `cluster.py`. `null` if Pillow is absent. |
| `dhash_matches_cluster` | `true` when the file's cluster representative is itself, `false` when a different rep is used, `null` when no cluster. |
| `exact_duplicate_of` | The first sibling in the same SHA-256 group, or `null` when the frame is unique. |
| `exact_duplicate_group` | The first 16 hex of the SHA-256, shared by all exact siblings. |
| `exact_duplicate_count` | Size of the SHA-256 group, 1 when unique. |
| `cluster_index` | Position in `clusters.json`, or `null`. |
| `cluster_rep`, `cluster_count`, `cluster_size` | Representative, declared count, and actual member-list length of that cluster. |
| `near_duplicate_cluster` | Filenames of the other members of this frame's cluster. A hint only; a cluster is not a byte identity (the corpus has 0 exact duplicates). |
| `in_clusters`, `in_signatures` | Whether the filename appears in the canonical `clusters.json` / `signatures.json`. |
| `readable` | `false` when the file could not be opened or hashed. |
| `note_refs` | Set by `notes`. One object per note file that names this frame: `note_file`, `note_path`, `match`, `read_date`, `category`, `screen`. |
| `note_files` | Distinct note files naming this frame. |
| `category_from_notes`, `screen_from_notes` | The first-category and first-screen values taken from a note for this frame. |
| `first_seen`, `last_seen` | When this record was first carried forward, and the most recent inventory run. |
| `inventory_run` | The most recent inventory run date. |

Review fields, owned by `screenshots.py review` and preserved on regeneration:

| Key | Meaning |
| --- | --- |
| `state` | One of the review states below. |
| `scenario` | URA Finale / Unity Cup / Trackblazer / Our Grand Concert, or `null`. |
| `screen` | Screen or screen type observed. |
| `observations` | Append-only list of `{date, by, batch, text}`. Direct observations only. |
| `evidence` | Mechanic claims linking to other frames, each with `{claim, supports[], confidence, kind}` where `kind` is `observed` or `inferred`. |
| `review_history` | Append-only list of `{date, state, by, batch, confidence, note}`. |
| `skipped_reason` | Why a frame is `skipped`. Required when the state is `skipped`. |
| `next_action` | The concrete next step for a `skipped` or `partial` frame. |
| `confidence` | `low`, `medium`, or `high`, set by the last review. |
| `outstanding` | Open questions this frame still needs answered. |
| `provenance` | Records where a state came from, e.g. a `legacy-notes` entry. |

## Review states

| State | Meaning | Evidence required |
| --- | --- | --- |
| `not_reviewed` | No evidence the original image has been examined. | Nothing. |
| `documented_unverified` | A historical note names the frame, but the original image has not been examined against that note in this registry. | At least one `exact` or `probable` note reference. |
| `verified` | The original image was examined for the recorded claims; identity is established. | A non-empty `review_history` with an observation, and an `observations` entry. |
| `partial` | Examined, but a relevant text, value, condition, or mechanic remains uncertain. | A `review_history` entry with an `outstanding` question. |
| `skipped` | Could not or should not be fully reviewed this pass. | A non-empty `skipped_reason` and, where practical, a `next_action`. |
| `duplicate_candidate` | May duplicate another frame; the relationship is not yet conclusive. | A `near_duplicate_cluster` entry or a `duplicate_candidate` state written by a reviewer. |

Transitions:

- `documented_unverified` to `verified` requires an actual inspection and a
  recorded observation. The `review` subcommand refuses to set `verified`
  without `--note`.
- `documented_unverified` to `partial` is the common path when a note names
  the frame but the note does not fully establish the values it carries.
- `skipped` and `partial` remain visible in the queue until they transition to
  another state; they do not disappear.
- A `duplicate_candidate` frame does not get counted as reviewed even if its
  cluster has been visually examined: the relationship must be established
  before the frame's coverage can close.
- States are written only through `review`. A successful `inventory` or
  `notes` run never advances a state.

`review_history` is append-only; it is never rewritten by the tool. A review
entry always carries the `by` field that identifies the inspector, and the
`batch` field that links it to a decision record in `batches/`.

## Commands

All commands are `python tools/screenshots.py <sub>`, run from any working
directory; the script resolves paths from its own location.

| Sub | Purpose | Notes |
| --- | --- | --- |
| `inventory` | Regenerate machine fields for every frame; preserve review fields. | Idempotent; detects missing files, hash changes, and files referenced by `clusters.json` but not on disk. |
| `notes` | Reconcile every legacy note file against the inventory; promote state to `documented_unverified` where a note exists. | Never advances to `verified`. |
| `validate` | Integrity checks: unique sids, valid states, file existence, hash consistency, evidence references, orphan records. | Exits non-zero on any failure. |
| `report` | Write `COVERAGE.md` and print it. | Counts come from the registry, not from the text. |
| `queue` | Prioritized list of outstanding frames. | `--limit`, `--state` to filter. |
| `show` | Print filtered records. | `--state`, `--category`, `--note`, `--ids`, `--full`, `--no-notes`, `--json`. |
| `sheet` | Labelled contact sheet for a set of frames, with a cell -> sid mapping. | `--ids`, `--state`, `--rows`, `--limit`. |
| `refs` | Reverse index of frame citations across the repository. | Writes `refs-index.jsonl`; `validate` flags a stale one. |
| `batch` | Define, list, and inspect a persisted research batch. | `--id` required; `--show` reads, otherwise writes. |
| `review` | Record a decision for one or more frames. | `--ids`, `--state`, `--observer`, `--batch`, `--note`, `--reason`, `--confidence`, `--next-action`, `--scenario`, `--category`, `--questions`. |
| `evidence` | Append a mechanic claim back-linked to the frames that support it. | `--id`, `--claim`, `--supports`, `--kind`, `--confidence`, `--observer`. |
| `selftest` | Assert the tool's invariants on a throwaway corpus. | Exits non-zero on any assertion failure. |

## Resuming a session

The minimum context a new session needs:

1. This file.
2. `python tools/screenshots.py report` - the current dashboard.
3. `python tools/screenshots.py queue --limit 30` - the next batch of work.
4. The `batches/` files - what the previous session already completed.
5. For each frame in the next batch, `python tools/screenshots.py show --ids <refs> --json` to see the full record including its observations.

Do not re-read every note file. The notes index carries the extracted
category, screen, observation size, and referenced frames; open the original
note only when the index says the description is thin.

## Rules the tool enforces

- `verified` without an observation: refused.
- `skipped` without a reason: refused.
- Invalid state in `review_history`: validation failure.
- Evidence citing an unknown frame: validation failure.
- An exact duplicate being marked `verified`: warning; the representative should be verified instead.
- An on-disk frame with no inventory record: validation failure.
- An inventory record with no file on disk: warning.

## What this file deliberately does not claim

- The corpus is not complete for the four Global scenarios. `DESIGN-CORPUS.md`
  § `## SCREENSHOT-MANIFEST.md` states, and the `queue` subcommand surfaces,
  that Trackblazer and Our Grand Concert have no frames at all, and URA
  Finale has only two. The registry records that absence; it does not fill it.
- A note is a lead, not a verification. The state `documented_unverified`
  makes that boundary explicit.
- The `near_duplicate_cluster` field is a hint, not a byte identity. The
  canonical `clusters.json` has a known class of over-collapse (rolling-window
  dHash with Hamming 6 chains neighbouring frames that share HUD chrome; see
  `PHASE-B-REPORT.md` item 10.2). When a finding depends on a cluster, say so.
