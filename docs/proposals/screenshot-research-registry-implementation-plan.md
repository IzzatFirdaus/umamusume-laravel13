# Screenshot Research Registry — Remaining Implementation Plan

**Repository:** `D:\Projects\umamusume-laravel13`  
**Subsystem:** `docs/research-scratch/screenshot-registry/`  
**Purpose:** Implement the remaining work needed to make the screenshot registry reliable, maintainable, and useful as an evidence source for Trainer Desk 2.0. This plan covers registry tooling, review workflow, evidence synthesis, and scenario screenshot capture—not the Laravel application itself.

## Scope and rules

This plan is based on the supplied Screenshot Research Registry progress reports. It excludes work already reported as completed:
- Initial inventory of all 1,159 screenshots and SHA-256 identities.
- Legacy-note reconciliation (52 references; 50 exact and 2 probable).
- B001, B002, and B003 review batches and their recorded observations.
- The OS-level registry lock, unique temporary files, and three-phase concurrency regression test.
- B001/B002 roll-up repairs, the B003 manifest/log, `findings.md`, and the confirmed hint-discount evidence claim.
- Resolution of the one partial review and disposition of the three unresolved citations.
- Existing `validate`, 14-assertion `selftest`, and concurrency regression gates, which were passing at the report's end.

**Before each slice:** inspect the current repository and relevant registry files. If the described behavior already exists, verify it and skip the slice rather than duplicating it. Keep changes within `docs/research-scratch/screenshot-registry/` unless a step explicitly describes new screenshot capture files under the existing screenshot corpus; never edit original screenshots. Do not commit. Never run inventory-mutating commands concurrently unless the concurrency test explicitly exercises isolated throwaway data. Preserve append-only review provenance. Every `verified` state must have a real visual observation.

## Phase 0 — Establish a safe working baseline

### Slice 0.1 — Verify the actual starting state

**Changes:** No production artifact changes are required if the reported state still holds. This is a required gate before implementation slices.

1. Inspect `README.md`, `CHECKPOINT.md`, `COVERAGE.md`, `findings.md`, `FINAL_REPORT.md`, and the B001–B003 manifests/logs.
2. Inspect `git status` and confirm which changes are pre-existing in the shared worktree.
3. Run:
   - `python tools/screenshots.py validate`
   - `python tools/screenshots.py selftest`
   - `python tools/screenshots_concurrency_test.py`
4. Recompute state counts from `inventory.jsonl`; compare them with `COVERAGE.md` and `CHECKPOINT.md`.
5. Open at least one known screenshot through the same image-inspection path intended for future batches.

**Acceptance:** The baseline is recorded in working notes for the implementer; validation gates pass; image inspection is demonstrably available. If any gate fails, fix that defect before starting another slice. Do not treat this baseline check itself as a deliverable.

## Phase 1 — Finish registry consistency and traceability

### Slice 1.1 — Reconcile remaining citation and inventory discrepancies

**Target files:** `refs-index.jsonl`, `inventory.jsonl`, `tools/screenshots.py`, `tools/screenshots_selftest.py`.

1. Re-run the reference-index command and enumerate entries where `in_inventory` is false.
2. For each reference, inspect its cited source document and existing corpus-missing evidence.
3. Encode a machine-readable disposition for each of the three known cases: missing frame with no disk/git copy, explicitly missing frame, and filename typo pointing at the verified actual frame. Do not invent an alias between different captures.
4. Add a validator invariant that every unresolved citation has a documented disposition, while every resolvable citation maps to the exact inventory `ref`/`sid`.
5. Add throwaway-corpus tests for each disposition type and for a genuinely new, undocumented missing citation.

**Tangible result:** Reference records carry explicit disposition fields (or an equivalent existing schema), and validation tests catch regressions.

**Acceptance:** All three known exceptions are machine-readable and explained; an unknown unresolved reference fails validation rather than being silently accepted.

### Slice 1.2 — Make inventory and batch roll-ups self-consistent

**Target files:** `tools/screenshots.py`, `tools/screenshots_selftest.py`, `batches/B001.json`, `batches/B002.json`, `batches/B003.json`, and their JSONL logs.

1. Inspect how review history, per-batch JSONL logs, and batch-level results are currently updated.
2. Add a deterministic batch-summary operation that computes totals from the batch's frame list and latest valid review event per frame.
3. Ensure summary regeneration is idempotent: running it twice produces the same result and never deletes append-only history.
4. Add checks that the manifest frame count equals the number of unique frame IDs, and the summary totals reconcile with the latest batch review states.
5. Regenerate existing batch summaries only if current manifests remain stale.

**Tangible result:** A reusable batch-summary command or function plus regression tests; corrected manifests only where required.

**Acceptance:** B001, B002, and B003 all reconcile with their logs and current inventory. A second summary run yields no diff.

### Slice 1.3 — Make registry mutations transaction-safe across commands

**Target files:** `tools/screenshots.py`, `tools/screenshots_concurrency_test.py`, `tools/_review_worker.py`.

1. Audit every command that reads and rewrites shared JSONL/JSON files—not only `review`, `inventory`, `notes`, and `evidence`.
2. Route every read-modify-write mutation through the existing re-entrant `registry_lock()` or an equivalent transaction boundary.
3. Use unique temp files and safe replacement for every shared generated file.
4. Extend the throwaway-corpus concurrency test to race only commands that are intended to be concurrently safe; assert no lost updates, malformed JSONL, mismatched history, or stale summaries.
5. Document in CLI help which commands mutate shared state and are serialized.

**Tangible result:** Broader locking coverage and automated concurrency regression cases.

**Acceptance:** Concurrent mutation tests pass repeatedly on Windows and POSIX-compatible environments where supported. If a command cannot safely be parallelized, it must serialize internally or reject overlapping mutation explicitly.

### Slice 1.4 — Make validation cover the full registry contract

**Target files:** `tools/screenshots.py`, `tools/screenshots_selftest.py`, `tools/screenshots_concurrency_test.py`.

Add validator checks for:
1. Unique `ref` and `sid` identity constraints.
2. Every inventory record has a corresponding file or an explicit missing-file disposition.
3. Every `verified` record has a non-empty observation and provenance.
4. `review_history` matches append-only `review-log.jsonl`.
5. Batch IDs in inventory history resolve to a batch manifest/log.
6. Evidence claims reference known evidence IDs and real inventory frame IDs, or carry an explicit missing-frame limitation.
7. Generated coverage totals match the inventory.
8. Citation exceptions are dispositioned.
9. No duplicate JSON keys or malformed JSONL records.

Add isolated fixture cases for both valid exceptions and invalid data.

**Tangible result:** Validator and selftests enforce the stated registry invariants.

**Acceptance:** Existing real registry validates cleanly; each new invariant has at least one passing and one failing fixture where practical.

## Phase 2 — Make the research workflow repeatable

### Slice 2.1 — Build a useful queue planner for B004 and later batches

**Target files:** `tools/screenshots.py`, `tools/list_clusters.py`, `tools/build_b002.py` (refactor or replace only if justified), new `tools/build_batch.py`, `tools/screenshots_selftest.py`.

1. Inspect the current `queue`, cluster, note, and batch-selection commands.
2. Add a reusable batch builder with explicit, composable priorities:
   - remaining `documented_unverified` frames to validate legacy notes;
   - one representative from a large cluster before selecting multiple members;
   - no-note, full-desktop screenshots with UI not yet represented;
   - time-spread singletons to avoid overfocusing on one run segment;
   - unresolved high-value questions recorded in `findings.md`.
3. Prevent accidental over-selection from a perceptual cluster. Cluster membership is a candidate grouping, not proof of duplicates.
4. Emit a reviewable candidate manifest with selection reasons, current state, note coverage, cluster ID/size, dimensions, and capture timestamp.
5. Add `--dry-run` and deterministic ordering. The builder must not mutate review state.

**Tangible result:** A reusable `build_batch.py` tool and a candidate manifest for B004 (approximately 20–30 frames).

**Acceptance:** Re-running with unchanged inputs produces byte-identical candidate output; each selected frame has a traceable reason; no frame is silently upgraded to reviewed.

### Slice 2.2 — Add native-resolution review support and image sanity checks

**Target files:** `tools/screenshots.py`, new `tools/review_images.py` only if the current environment benefits from it, `tools/screenshots_selftest.py`.

1. Confirm the existing `show` and `sheet` paths can open an original at native resolution.
2. Add a helper that presents one image at a time with its `ref`, `sid`, dimensions, file path, current state, legacy note excerpt, and nearby cluster members.
3. Make it possible to zoom/crop a region for legibility without writing back to the original image.
4. Add explicit outcomes for unreadable files, insufficient resolution, obscured UI, and ambiguous text; these outcomes must not be labeled `verified`.
5. Add tests that the helper never alters original bytes or review state by itself.

**Tangible result:** A repeatable visual-review aid that supports full-resolution inspection without altering source screenshots.

**Acceptance:** A reviewer can inspect a candidate and capture an observation using a single predictable workflow; opening or zooming images never changes inventory state.

### Slice 2.3 — Add structured observation fields

**Target files:** `tools/screenshots.py`, `inventory.jsonl` schema, `tools/screenshots_selftest.py`.

1. Inspect existing observation formats before changing the schema.
2. Add optional structured fields without discarding free-text observations:
   - `screen_type`
   - `scenario`
   - `career_phase`
   - `visible_mechanics`
   - `direct_values`
   - `limitations`
   - `follow_up_question`
3. Keep values nullable when the screenshot does not establish them.
4. Preserve existing records and append-only review history; provide a one-way migration or tolerant reader for old entries.
5. Add tests for old records, fully structured records, and partially populated records.

**Tangible result:** Structured observations that can be queried by scenario, screen, phase, and mechanic while retaining original notes.

**Acceptance:** Existing JSONL remains readable; migration is repeatable; no field is filled by inference unless marked as inference.

### Slice 2.4 — Add evidence-to-observation traceability

**Target files:** `evidence.jsonl`, `tools/screenshots.py`, `findings.md`, `tools/screenshots_selftest.py`.

1. Define or formalize claim fields: stable evidence ID, concise claim, status (`inferred`, `observed`, or `confirmed`), confidence, supporting `ref`/`sid` list, counterevidence, limitations, and next verification question.
2. Make the tool validate that supporting frames actually exist and are visually reviewed.
3. Prevent a claim from becoming `confirmed` solely because many near-duplicate frames repeat the same UI.
4. For cluster-based support, record whether supporting frames are independent capture contexts.
5. Add a query command to list claims by status, confidence, scenario, or unresolved question.

**Tangible result:** Evidence claims become queryable, auditable records linked to exact frames.

**Acceptance:** Every claim can be traced to specific frames; inferred claims remain explicitly inferred until the acceptance criteria for confirmation are met.

### Slice 2.5 — Add a focused evidence browser/report command

**Target files:** `tools/screenshots.py`, `findings.md`, `COVERAGE.md`.

1. Add a command that filters reviewed frames and evidence by scenario, screen type, mechanic, state, note coverage, or confidence.
2. Output both human-readable Markdown and machine-readable JSON without modifying source records.
3. Include counts for unique frames, cluster representatives, independent contexts, and open questions.
4. Mark scenario gaps as `capture_required`, not as an empty search result.
5. Keep `COVERAGE.md` generated and deterministic; do not manually patch generated values.

**Tangible result:** A reusable evidence browser/export command for implementation agents.

**Acceptance:** A query for any confirmed mechanic returns its claim, supporting frames, limitations, and provenance; a query for Trackblazer or Our Grand Concert clearly reports that capture is required.

## Phase 3 — Continue visual research efficiently

### Slice 3.1 — Complete B004: remaining documented-unverified frames

**Target files:** `batches/B004.json`, `batches/B004.jsonl`, `inventory.jsonl`, `review-log.jsonl`, `COVERAGE.md`.

1. Use the batch planner to select up to 30 candidates, prioritizing the 40 `documented_unverified` frames still present at session start, adjusted to the current live count.
2. Inspect each screenshot against the exact legacy note that references it.
3. Record what the image directly shows and separately note any discrepancy with the legacy description.
4. Use `verified` only when the claim is legible and supported; otherwise keep or move to an appropriate non-verified state with a reason.
5. Run review mutations sequentially unless the registry's transaction model and test coverage explicitly establish safe parallel use.

**Tangible result:** B004 manifest/log, review history, and updated inventory observations.

**Acceptance:** Every selected frame has a review decision, observation or skip reason, and source note comparison; no legacy statement is accepted solely because it exists in a note.

### Slice 3.2 — Characterize high-value cluster variation

**Target files:** `batches/B005.json`, `batches/B005.jsonl`, `inventory.jsonl`, `review-log.jsonl`.

1. Recompute cluster membership from the canonical cluster file; do not hardcode old cluster sizes.
2. Select a small number of additional frames from clusters 725, 80, 66, 707, and 708, based on current membership and B001–B003 review history.
3. Compare members for actual changes in values, selected training, turn count, phase, modal state, and scenario.
4. If members differ meaningfully, record those distinctions rather than treating the cluster as duplicate evidence.
5. If a member adds no new information, mark it `duplicate_candidate` only if the registry's defined criteria are met; otherwise leave the state unchanged and record the reason in the batch log.

**Tangible result:** B005 evidence of within-cluster variation, with per-frame observations for meaningful changes.

**Acceptance:** Cluster similarity is never used as a substitute for visual comparison; cluster-derived findings disclose the known rolling-window dHash over-collapse limitation.

### Slice 3.3 — Review remaining legacy-note frames in bounded batches

**Target files:** `batches/B006.json` onward, corresponding JSONL logs, `inventory.jsonl`, `review-log.jsonl`, `COVERAGE.md`.

1. Recompute the remaining `documented_unverified` queue after B004.
2. Generate bounded batches of 20–30, ordered by note category and expected implementation value.
3. Validate race-entry, training, career-progression, skill-selection, character-detail, menu, and miscellaneous note categories.
4. Keep each batch independent and resumable; never mark an entire note file verified because one frame matched.
5. Regenerate coverage and run validation after each completed batch.

**Tangible result:** A sequence of review batches that steadily reconciles the legacy notes with the original images.

**Acceptance:** Each batch can be resumed from its manifest/log; state totals and provenance reconcile after every batch.

### Slice 3.4 — Review no-note screenshots for new UI structures

**Target files:** `batches/B00N.json` onward, corresponding logs, `inventory.jsonl`, `review-log.jsonl`.

1. Once note-referenced frames have been addressed, select no-note screenshots by underrepresented screen type and time coverage.
2. Favor full-desktop captures and screens that expose previously unseen modals, menus, results, training panels, or navigation.
3. Use one representative per perceptual cluster first, then inspect additional members only when they may reveal state transitions.
4. Record screen structure and direct values as observations, not as general game rules.
5. Stop spending review effort on repeated screens that add no new implementation-relevant information.

**Tangible result:** Reviewed records for previously undocumented UI structures and state transitions.

**Acceptance:** Every reviewed frame is tied to a research question or selection rationale; coverage grows by useful evidence, not by maximizing the reviewed percentage.

## Phase 4 — Turn screenshot findings into implementation-ready evidence

### Slice 4.1 — Promote only sufficiently supported mechanic claims

**Target files:** `evidence.jsonl`, `findings.md`, related batch logs.

1. Review the existing evidence claims and their support independence.
2. Recheck the mood ladder, growth-rate display, URA Finale, hint discounts, and failure-rate claims against their exact cited frames.
3. Preserve low-confidence bootstrap seeds separately from observed confirmations.
4. For each claim, state whether it describes literal UI rendering, a single observed value, or a generalized mechanic.
5. Do not generalize a failure-rate formula, stat ceiling rule, or other formula from a single frame or repeated near-duplicates.
6. Add new claims only when the visual evidence supports a distinct and useful assertion.

**Tangible result:** Evidence records with appropriately calibrated status, confidence, independent support, and limitations.

**Acceptance:** No claim is promoted solely to make the evidence list look complete; unsupported formulas remain open questions.

### Slice 4.2 — Build implementation-ready UI contracts from observations

**Target files:** New machine-readable contract files under `contracts/` (for example `contracts/training-screen.json`, `contracts/skill-selection.json`, `contracts/race-results.json`, `contracts/legacy-selection.json`), plus a query/validation helper.

1. Choose only screen types with enough reviewed evidence to establish visible structure.
2. For each contract, list visible components, exact labels, observed states, values and units, interactions visibly supported by the screenshots, and unresolved questions.
3. Link every observed field/state to the exact `ref` and `sid`.
4. Distinguish screenshot-observed behavior from intended Trainer Desk behavior; screenshots alone do not establish application write semantics.
5. Add a schema validator for required contract fields and valid evidence references.

**Tangible result:** Versioned, machine-readable UI contracts for evidence-backed screen types.

**Acceptance:** An implementation agent can find the evidence for each field/state; unsupported screens remain marked as needing capture or research.

### Slice 4.3 — Generate targeted evidence packets for implementation tasks

**Target files:** `tools/screenshots.py`, new `tools/evidence_packet.py`, generated files under `packets/`.

1. Accept a task topic such as “skill hint discount,” “training failure display,” or “race result layout.”
2. Retrieve matching claims, reviewed frames, exact references, observations, limitations, and relevant contract files.
3. Include a short evidence-strength note that distinguishes independent frames from near-duplicate cluster members.
4. Produce a Markdown packet and JSON manifest suitable for an implementation agent to consume.
5. Ensure packet generation is read-only and deterministic.

**Tangible result:** Reusable evidence packets for feature implementation, each backed by exact source screenshots.

**Acceptance:** Re-running a packet build without input changes produces no diff; every statement in a packet links to a claim or direct frame observation.

## Phase 5 — Close scenario-specific capture gaps

### Slice 5.1 — Define a capture intake and provenance workflow

**Target files:** New `capture/README.md`, `capture/manifest.jsonl`, `tools/import_captures.py`, and selftests.

1. Define a separate intake path for newly captured screenshots so the existing 1,159 originals remain untouched.
2. Record capture date/time, scenario, game version/region when known, screen type, source filename, SHA-256, dimensions, and operator-supplied context.
3. Validate file readability and hashes before accepting a capture into the research intake.
4. Never overwrite a file with a matching timestamp stem; distinguish identity by hash and preserve both sources.
5. Require explicit human review before a new capture is copied into the canonical corpus or referenced as confirmed evidence.

**Tangible result:** A capture-intake tool and provenance manifest with duplicate-stem protection.

**Acceptance:** Import tests cover valid images, corrupt files, repeated names with different bytes, and exact-byte duplicates. Importing never silently overwrites originals.

### Slice 5.2 — Capture Trackblazer screens

**Target files:** New screenshot files in the designated capture-intake area; `capture/manifest.jsonl`; relevant review batch and evidence records.

1. Capture the actual Global Trackblazer screens; do not synthesize or reconstruct them.
2. Prioritize screens needed to understand the scenario's unique state: scenario overview, turn/deadline display, training and event interactions, point/lesson/shop systems where present, and end-of-run results.
3. Capture transitions or alternate states when they materially change labels, values, or available actions.
4. Import captures through the intake workflow, inspect them at native resolution, and record only directly visible facts.
5. Link resulting observations to exact capture hashes and keep any inferred mechanic separate.

**Tangible result:** A real, hash-identified Trackblazer screenshot set plus reviewed observations.

**Acceptance:** Trackblazer is no longer represented solely as a capture gap; each mechanic claim has traceable original evidence and explicit limitations.

### Slice 5.3 — Capture Our Grand Concert screens

**Target files:** New screenshot files in the designated capture-intake area; `capture/manifest.jsonl`; relevant review batch and evidence records.

1. Capture actual Global Our Grand Concert screens; do not fill the gap by repeatedly searching the existing corpus.
2. Prioritize concert/deadline state, Performance Points, lesson selection, support bonds, training, event choices, and final concert/result states where present.
3. Capture meaningful variations across turns and deadline proximity rather than only one static screen.
4. Import, hash, inspect, and review captures using the same provenance path as Trackblazer.
5. Link observations and claims to exact frames and preserve unresolved mechanics as questions.

**Tangible result:** A real, reviewed Our Grand Concert screenshot set with auditable evidence.

**Acceptance:** The scenario gap is closed only for the screen states actually captured; uncaptured states remain explicitly listed as gaps.

### Slice 5.4 — Expand URA Finale coverage without overgeneralizing

**Target files:** Capture intake files if new screenshots are available, `capture/manifest.jsonl`, new review batch, evidence records.

1. The existing corpus contains only two URA Finale frames; treat them as narrow coverage.
2. Capture or locate genuine additional URA states needed for implementation, such as earlier career phases, race transitions, goal states, and results.
3. Review new frames with the same process and distinguish scenario-specific labels from shared UI.
4. Do not claim complete URA coverage merely because the two existing frames are verified.

**Tangible result:** Additional real URA evidence for the selected missing states, or an explicit machine-readable `capture_required` record if capture cannot be performed in the current environment.

**Acceptance:** Every expanded claim is backed by actual frames; lack of access is recorded as a capture blocker, not filled with assumptions.

## Phase 6 — Operationalize, test, and hand off

### Slice 6.1 — Add an end-to-end registry smoke test

**Target files:** `tools/screenshots_e2e_test.py` and any required isolated fixtures.

Exercise a complete lifecycle on a temporary corpus:
1. Inventory source images and compute hashes.
2. Reconcile a legacy note reference.
3. Build a candidate batch.
4. Inspect metadata without mutation.
5. Apply a review and verify append-only history.
6. Add an evidence claim.
7. Generate a batch summary and coverage report.
8. Generate an evidence packet or contract.
9. Run validation and all regression tests.
10. Re-run the pipeline and verify deterministic outputs.

**Tangible result:** An automated end-to-end regression test using throwaway files only.

**Acceptance:** The test leaves the real corpus and registry unchanged and fails if any lifecycle stage loses provenance or produces inconsistent counts.

### Slice 6.2 — Add a single safe research command

**Target files:** `tools/research.py` or a documented wrapper under `tools/`, plus tests.

1. Compose existing commands into explicit subcommands such as `check`, `plan-batch`, `summarize-evidence`, and `build-packet`.
2. Do not create a monolithic command that silently mutates review state.
3. Print the proposed input set before any review-state mutation.
4. Require an explicit flag for writes and preserve a dry-run default for batch planning.
5. Return non-zero status on failed validation or mismatched summaries.

**Tangible result:** A predictable entry point for future research sessions.

**Acceptance:** Read-only operations are safe by default; mutating operations are explicit, serialized, and validated.

### Slice 6.3 — Verify final registry integrity and handoff readiness

**Target files:** Only code/data fixes required by failed gates; regenerated `COVERAGE.md` if stale.

1. Run validation, selftest, concurrency regression, and end-to-end tests.
2. Confirm all batch manifests reconcile with their JSONL logs and inventory review history.
3. Confirm no `verified` frame lacks an observation or provenance.
4. Confirm evidence and contract references resolve to exact `ref`/`sid` identities.
5. Confirm capture gaps for Trackblazer, Our Grand Concert, and undercovered URA states are honestly represented.
6. Inspect `git status`; ensure no files outside the permitted registry/intake scope were changed and no commits were created.

**Tangible result:** A registry whose code, records, evidence links, and automated tests agree.

**Acceptance:** All gates pass; generated outputs are current; the next batch and remaining capture blockers are machine-readable and actionable.

## Recommended execution order

1. Phase 0 baseline.
2. Phase 1 consistency, transaction, and validation hardening.
3. Phase 2 batch planner and observation/evidence tooling.
4. Phase 3 B004 and later visual review batches.
5. Phase 4 evidence-backed UI contracts and packets.
6. Phase 5 real scenario captures as game access permits.
7. Phase 6 end-to-end automation and final verification.

## Completion criteria

The registry is implementation-ready when:
- All inventory, review, batch, and citation invariants are enforced automatically.
- Every verified frame has real observation and provenance.
- Batch planning is deterministic, explainable, and read-only by default.
- Evidence claims link to exact frames and distinguish direct observation from generalization.
- UI contracts and evidence packets are generated from source-backed records.
- Trackblazer and Our Grand Concert have actual captures for the states claimed as covered.
- URA coverage limitations remain explicit until additional states are captured.
- The end-to-end test, selftest, concurrency test, and validator pass without changing the original screenshot corpus.
