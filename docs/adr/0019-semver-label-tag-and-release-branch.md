# ADR-0019: A semver label, a tag, and a release branch for a local-only tool

<!-- ADR-CONTRACT
  decision: proposed — the owner set the version (0.1.0) and the constraint that the release must not target master; the pre-release mechanism, the tag/branch scheme and the AGENTS.md correction await that ruling
  code: not built — nothing was tagged and nothing was published; VERSION.md and CHANGELOG.md exist at the root by owner direction
  superseded-by: —
  amended-by: —
  open: the AGENTS.md §14 and §3 correction is owed and not landed; the pre-release mechanism (GitHub flag vs hyphenated suffix) is the one choice left open
-->

Status: **Proposed** (drafted 2026-10-04, no owner ruling recorded). The owner set the version number
(`0.1.0`) and the constraint that the release must not target `master`. The remaining choices — the
pre-release mechanism, the tag/branch scheme, and the correction owed to `AGENTS.md` §14 — await that ruling.
Nothing was tagged and nothing was published while this was written.

## Context

The repository had no version of its own. Under Semantic Versioning 2.0.0, item 5 reserves `0.y.z` for initial
development where anything may change and the public API should not be considered stable, and item 6 makes
`1.0.0` the release that *defines* the public API. This codebase has not defined one in a single place, and
its own decision record is still moving: `ADR-0001` is accepted in part, with its schema section replaced by
`ADR-0003`; `PRD.md` carries open questions, and `KNOWN-ISSUES.md` carries open entries. `0.y.z` is therefore
the correct band, and the first release in it is `0.1.0`.

**The surfaces a version number promises, and only these:** the JSON contract at `/api/v1`, the SQLite schema
the Trainer's own data lives in, and the displayed vocabulary in `lang/en/uma.php`. Blade component
internals, screen layout and `.ai/rules` are not a public API; changing them moves the label at most one
minor.

## Decision

**The label is `0.1.0` and the release is a pre-release.**

- **MAJOR** moves when a migration invalidates existing local Trainer data rather than adding to it, or when
  a `/api/v1` field is removed or renamed.
- **MINOR** moves on additive work: a new endpoint, a new screen, a new nullable column, new seeded catalog
  data.
- **PATCH** moves on fixes: a bug, a copy correction, a dependency patch bump. A dependency upgrade that
  changes nothing a consumer can observe is a PATCH, whatever the constraints themselves do.

**The tag is `0.1.0`, annotated, and the target is `release/0.1.0`.** The branch is created from the commit
chosen for release and the tag is placed on that commit. Because `release/0.1.0` starts at the same commit
`master` is at, the branch is a pointer, not a fork, and history does not split. `master` fast-forwards to the
release commit as part of the same operation, so the branch is kept only as the address the tag hangs off.

**The gate to `1.0.0` is a stated condition, not a date.** All three must hold: the `PRD.md` open questions
are closed, the `/api/v1` response shapes are declared in `ARCHITECTURE.md`, and no `OPEN` defect touches the
API or the schema.

## Pre-release means two different things, and only one is meant here

Semantic Versioning spells a pre-release as a hyphenated identifier, `0.1.0-alpha.1`, and item 9 defines it as
an unstable version *leading up to* `0.1.0`. GitHub separately has a "set as a pre-release" flag on a Release
object, which marks the published release as unofficial without changing its tag.

**This decision uses the GitHub flag and keeps the tag plain at `0.1.0`.** The instability signal here comes
from being below `1.0.0`, which item 5 already supplies, and `0.1.0-alpha.1` would advertise a candidate for a
specific `0.1.0` that is not what is meant. **This choice is the one left open**: if the owner prefers the
signal to survive outside GitHub, the suffix form is the alternative and the tag becomes `0.1.0-rc.1`.

## What blocks publishing right now

Three facts, none of them a judgement call:

- **No tool in this session creates a release.** `gh` is absent from `PATH`, and the connected GitHub MCP
  server exposes only read-only release endpoints. The release itself must be created by the owner in the
  GitHub UI or from a machine with `gh`.
- **The tree does not hold still.** No release commit can be chosen while another writer is landing on the
  same branch, which is the durable version of this blocker.
- `AGENTS.md` §1 keeps the *application* off any public host, which is a different thing from the repository
  being on GitHub.

## Alternatives considered

- **`1.0.0` now.** Semantic versioning's own FAQ argues that software used in production should probably
  already be `1.0.0`, and this tool runs daily against real Trainer data. Rejected, because `1.0.0` is a
  promise about the API surface rather than about how carefully the software is used, and the surfaces that
  would carry the promise are still taking migrations while product questions stay open.
- **Tag directly on `master`.** Cheapest, and excluded by the owner's instruction that the target not be
  `master`. Recorded so the exclusion is visible rather than assumed.
- **Calendar versioning.** Rejected. There is no release cadence to encode, no CI, and no distribution
  channel, so a date-based label would carry no information the tag date does not already carry.
- **A root `CHANGELOG.md`.** Not permitted under `AGENTS.md` §3 as originally written. See the ledger: the
  owner directed that `VERSION.md` and `CHANGELOG.md` be written at the root, which overrides that objection
  for these two files.

## Consequences

`AGENTS.md` §14 reads "There is no changelog or version file to bump." A version label contradicts the first
half of that sentence, so §14 is corrected with a dated change-log entry rather than silently rewritten, per
§11. **That edit is owed and has not been made.** `AGENTS.md` §3, which forbids new root markdown, is owed the
same correction.

`docs/adr/README.md` gains a row for this ADR. Its table was already stale, so the row and the count are
corrected together; **the index is now generated** from each ADR's contract block, so there is no hand-typed
count left to go stale.

No root file is created by this decision. The release notes are the GitHub Release body.

If `release/0.1.0` is created while another worktree is also open, both point into the same history and
neither carries the peer's uncommitted work. The order that avoids a stranded version is: land the
uncommitted work, create `release/0.1.0` from it, tag, then branch the upgrade from the tag.

## Release title and notes as proposed

Title: `Trainer Desk 0.1.0 (pre-release)`. The notes state that it is a pre-release below `1.0.0`; that the
public API for versioning purposes is the `/api/v1` endpoints, the SQLite schema and the displayed
vocabulary; that this application runs on loopback and is not deployable; that Trainer data lives in one
local SQLite file and is not migrated between releases by any automation, so `php artisan uma:backup` is the
backup path; and that the pre-release flag should be set, with the move to `1.0.0` gated on the three
conditions in the Decision.

## Links

- `AGENTS.md` §3, §11, §14 (the corrections owed); `PRD.md` open questions; `ARCHITECTURE.md` (the `/api/v1`
  shapes to declare); `KNOWN-ISSUES.md` (the open defects that touch the API or schema)
- `VERSION.md`, `CHANGELOG.md` (both at the root by owner direction)
- `ADR-0001` (accepted in part; the decision record still moving), `ADR-0003` (its §5 supersession)
- Semantic Versioning 2.0.0 items 5, 6 and 9

## Ledger

- 2026-10-04 · **erratum — `VERSION.md` and `CHANGELOG.md` are at the root after all.** The owner directed
  that both be written at the root on the same day this ADR was drafted, which overrides the §3 objection the
  Alternatives section records. The original reasoning is preserved rather than deleted, because it is the
  reason the pair needs a stated rule rather than existing by accident. Two consequences follow and neither
  has been edited: `AGENTS.md` §3, which forbids new root markdown, and §14, which states "There is no
  changelog or version file to bump", are both contradicted by the tree and are owed a dated owner correction
  under §11. And the release-note rule changes: the GitHub Release body carries the notes for consumers, and
  `CHANGELOG.md` records changes at the time they are made rather than reconstructing them.
- 2026-10-12 · **the index this ADR asked for is generated.** The consequence above asked for a row and a
  corrected count. `docs/adr/README.md` now carries a table spliced between `<!-- adr-index:begin -->` and
  `<!-- adr-index:end -->` by `python3 tools/adr-index.py docs/adr --write`, derived from each ADR's contract
  block, so the count and the rows cannot go stale. The hand-maintained table and the `head -1` / `awk`
  regeneration snippet are gone.
