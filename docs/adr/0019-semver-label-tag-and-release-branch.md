# ADR-0019: A semver label, a tag, and a release branch for a local-only tool

Status: **Proposed (drafted 2026-10-04, no owner ruling recorded).** The owner set the version number
(`0.1.0`) and the constraint that the release must not target `master`. The remaining choices in this
document, the pre-release mechanism, the tag/branch scheme, and the correction owed to `AGENTS.md` §14,
await that ruling. Nothing was tagged and nothing was published while this was written.

## Context

The repository has no version of its own. Measured at `fc64bc2` on 2026-10-04:

| Instrument | Result |
|---|---|
| `composer.json` `version` | absent; `name` is still the skeleton default `laravel/laravel` |
| Git tags, local and on `origin` | zero (`git tag` empty, `git ls-remote --tags origin` empty) |
| `VERSION`, `CHANGELOG.md` at root | neither exists |
| Commits | 524, spanning `2026-09-27T01:45:31+08:00` to `2026-10-04T11:42:22+08:00` |
| `AGENTS.md` §14 | "There is no changelog or version file to bump." |

Under Semantic Versioning 2.0.0, item 5 reserves `0.y.z` for initial development where anything may
change and the public API should not be considered stable, and item 6 makes `1.0.0` the release that
*defines* the public API. This codebase has not defined one in a single place, and its own decision
record is still moving: `ADR-0001` is `accepted in part` with its §5 superseded by `ADR-0003`, `PRD.md`
carries six open questions, and `KNOWN-ISSUES.md` carries two entries both marked `OPEN` while the
register numbering runs to KI-58. `0.y.z` is therefore the correct band, and the first release in it is
`0.1.0`.

The surfaces that a version number would actually be promising. These, and only these, are the public
API for semver purposes:

1. The JSON contract at `/api/v1`, declared by `Route::prefix('v1')` in `routes/api.php:10`, six endpoints.
2. The SQLite schema the Trainer's own data lives in, forty migrations, each declaring a `down()`. The
   declaration is verified by grep and satisfies the letter of `AGENTS.md` §11. It is not proof that each
   `down()` runs: the C-5 bar in `GOVERNANCE.md` reads "every migration has working `down()`" while naming
   `php artisan migrate:fresh --seed` as its gate, and that command drops tables directly without ever
   calling `down()`. The gap is recorded as parked in `docs/research-scratch/AUDIT-AND-VERIFICATION.md` and
   carries no `KI-nn` entry.
3. The displayed vocabulary in `lang/en/uma.php`, because it is what the owner reads and what the lore gate protects.

Blade component internals, screen layout, and `.ai/rules` are not a public API. Changing them moves the
label at most one minor.

## Decision

**The label is `0.1.0` and the release is a pre-release.** The digits mean:

- **MAJOR** moves when a migration invalidates existing local Trainer data rather than adding to it, or
  when a `/api/v1` field is removed or renamed.
- **MINOR** moves on additive work: a new endpoint, a new screen, a new nullable column, new seeded
  catalog data.
- **PATCH** moves on fixes: a bug, a copy correction, a dependency patch bump.

The dependency upgrade discussed alongside this decision, widening the majors available to Pest,
PHPUnit, Tinker, Collision and Boost while `laravel/framework` stays at `^13.0`, changes nothing a
consumer can observe. It is a **PATCH**, not a major, whatever the constraints themselves do.

**The tag is `0.1.0`, annotated, and the target is `release/0.1.0`.** The branch is created from the
commit chosen for release and the tag is placed on that commit. Because `release/0.1.0` starts at the
same commit `master` is at, the branch is a pointer, not a fork, and history does not split. A release
branch that is never merged back would strand the version, so `master` fast-forwards to the release
commit as part of the same operation, and the branch is kept only as the address the tag hangs off.

**The gate to `1.0.0` is a stated condition, not a date.** All three must hold: the six `PRD.md` open
questions are closed, the `/api/v1` response shapes are declared in `ARCHITECTURE.md`, and no `OPEN`
defect touches the API or the schema.

## Pre-release means two different things, and only one is meant here

Semantic Versioning spells a pre-release as a hyphenated identifier, `0.1.0-alpha.1`, and item 9 defines
it as an unstable version *leading up to* `0.1.0`. GitHub separately has a "set as a pre-release" flag on
a Release object, which marks the published release as unofficial without changing its tag.

**This decision uses the GitHub flag and keeps the tag plain at `0.1.0`.** The reason is that the
instability signal here comes from being below `1.0.0`, which item 5 already supplies, and
`0.1.0-alpha.1` would advertise a candidate for a specific `0.1.0` that is not what is meant. If the
owner prefers the signal to survive outside GitHub, for example in a registry or a lockfile, the suffix
form is the alternative and the tag becomes `0.1.0-rc.1`. This choice is the one left open.

## What blocks publishing right now

Three facts, none of them a judgement call. Measured at `fc64bc2`, then re-checked at `b6fd4e1`; the first
blocker closed itself while this document was being written.

**Remote state.** At `fc64bc2`, `master` was sixty-four commits ahead of `origin/master` with zero behind,
and that commit was not on the remote at all, so no release could reference it. A GitHub Release is created
against a tag that must exist in the remote repository, so publishing would have required pushing
sixty-four commits of a local-only tool first. A concurrent session has since pushed, and at `b6fd4e1` the
two refs are equal, so pushing is no longer the obstacle. `AGENTS.md` §1 keeps the *application* off any
public host, which is a different thing from the repository being on GitHub, but the history is now
readable by whoever can read the remote.

**No tool in this session creates a release.** `gh` is absent from `PATH`, and the connected GitHub MCP
server exposes only `list_releases`, `get_latest_release`, and `get_release_by_tag`, all read-only. The
release itself must be created by the owner in the GitHub UI or from a machine with `gh`.

**The tree does not hold still.** At `fc64bc2` sixty-eight tracked files were modified and twelve were
untracked, and the `.ai/rules` work sat inside them. A concurrent session then committed that work under
`cb34264 docs(rules): record the project conventions and index them by glob` and moved the base three times
in one pass over these files. No release commit can be chosen while another writer is landing on the same
branch, which is the durable version of this blocker.

## Alternatives considered

**`1.0.0` now.** Semantic versioning's own FAQ argues that software used in production should probably
already be `1.0.0`, and this tool runs daily against real Trainer data. Rejected, because `1.0.0` is a
promise about the API surface rather than about how carefully the software is used, and the surfaces that
would carry the promise are still taking migrations while six product questions stay open. The owner's
data being precious is the argument for tagging releases at all, not the argument for claiming stability.

**Tag directly on `master`.** Cheapest, and excluded by the owner's instruction that the target not be
`master`. Recorded here so the exclusion is visible rather than assumed.

**Calendar versioning.** Rejected. There is no release cadence to encode, no CI, and no distribution
channel, so a date-based label would carry no information the tag date does not already carry.

**A root `CHANGELOG.md`.** Not permitted. `AGENTS.md` §3 states that no new markdown file is created in
the repository root, and root documentation is a closed standing set. The release notes belong in the
GitHub Release body, which is where a consumer looks and which needs no tracked file. If a durable
change history is ever wanted, §3 sends that content into a master under `docs/research-scratch/`.

> *[Dated erratum, 2026-10-04]* The owner directed that `VERSION.md` and `CHANGELOG.md` be written at the
> root on the same day this ADR was drafted, which overrides the §3 objection above for these two files.
> The original reasoning is preserved rather than deleted, because it is the reason the pair needs a
> stated rule rather than existing by accident. Two consequences follow. `AGENTS.md` §3, which forbids new
> root markdown, and §14, which states "There is no changelog or version file to bump", are now both
> contradicted by the tree and are owed a dated owner correction under §11; neither has been edited. And
> the release-note duplication rule changes: the GitHub Release body carries the notes for consumers, and
> `CHANGELOG.md` records changes at the time they are made rather than reconstructing them.

## Consequences

`AGENTS.md` §14 currently reads "There is no changelog or version file to bump." A version label
contradicts the first half of that sentence, so §14 is corrected with a dated change-log entry rather
than silently rewritten, per §11. That edit is owed and has not been made.

`docs/adr/README.md` gains a row for this ADR. Its table was already stale, ending at `0017` while
`0018` existed on disk and the prose counted seventeen files, so both rows are added and the count
corrected. The index is derived and the regeneration command in that file is authoritative; the table
carries short subjects rather than the raw heading text, so the rows are matched to that style.

No root file is created by this decision. The release notes are the GitHub Release body and are repeated
below for convenience.

If `release/0.1.0` is created while the upgrade worktree is also open, both point into the same history
and neither carries the peer's uncommitted work. The order that avoids a stranded version is: land the
uncommitted work, create `release/0.1.0` from it, tag, then branch the upgrade from the tag.

## Release title and notes as proposed

Title: `Trainer Desk 0.1.0 (pre-release)`

Notes:

> First tagged release of Trainer Desk, a local-only Laravel 13 tool for the Global English release of
> Umamusume Pretty Derby. It is a pre-release: the version is below `1.0.0`, which under Semantic
> Versioning means the public API is not yet considered stable and any part of it may change between
> minor versions.
>
> Public API for versioning purposes is three surfaces: the `/api/v1` JSON endpoints, the SQLite schema,
> and the displayed vocabulary in `lang/en/uma.php`. A MAJOR moves when a migration invalidates existing
> local Trainer data or when an `/api/v1` field is removed or renamed. A MINOR is additive. A PATCH is a
> fix, including dependency bumps that change nothing observable.
>
> This application runs on loopback and is not deployable. There is no hosted instance, no upgrade
> service, and no support commitment. Trainer data lives in one local SQLite file and is not migrated
> between releases by any automation; back it up with `php artisan uma:backup` before applying a
> migration set.
>
> Set the pre-release flag on this release. The move to `1.0.0` is gated on closing the six open questions
> in `PRD.md`, declaring the `/api/v1` response shapes in `ARCHITECTURE.md`, and clearing open defects that
> touch the API or the schema, as recorded in `ADR-0019`.
