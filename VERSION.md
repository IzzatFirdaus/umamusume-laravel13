# VERSION

Current version: `0.1.0`, pre-release. Not yet tagged.

| Field            | Value                         | Measured                                                                      |
| ---------------- | ----------------------------- | ----------------------------------------------------------------------------- |
| Version          | `0.1.0`                       | decision recorded in `docs/adr/0019-semver-label-tag-and-release-branch.md`   |
| Pre-release      | yes                           | GitHub Release flag, not a semver hyphen identifier                           |
| Git tags         | none, local or on `origin`    | `git tag`, `git ls-remote --tags origin`                                      |
| Release commit   | not fixed                     | see Release state below                                                       |
| Framework        | `laravel/framework` 13.32.0   | `composer.lock`, constraint `^13.0`                                           |
| PHP              | 8.5.8                         | runtime, against `require.php: ^8.3`                                          |

## Why 0.y.z

Semantic Versioning 2.0.0 item 5 reserves `0.y.z` for initial development, where anything may change and
the public API is not stable. Item 6 makes `1.0.0` the release that defines the public API. `ADR-0019`
records that this project has not defined one: `PRD.md` carries six open questions, `ADR-0001` is accepted
in part with its schema section superseded, and `KNOWN-ISSUES.md` carries open entries while the register
numbering runs to KI-58.

## What the digits mean here

The public API is three surfaces and nothing else.

1. The JSON endpoints under `/api/v1`, declared by `Route::prefix('v1')` in `routes/api.php`.
2. The SQLite schema the Trainer's own data lives in.
3. The displayed vocabulary in `lang/en/uma.php`.

- MAJOR moves when a migration invalidates existing local Trainer data rather than adding to it, or when
  an `/api/v1` field is removed or renamed.
- MINOR is additive: a new endpoint, a new screen, a new nullable column, new seeded catalog data.
- PATCH is a fix: a bug, a copy correction, or a dependency bump that changes nothing observable.

Blade internals, screen layout, and `.ai/rules` are not a public API.

## Gate to 1.0.0

A condition, not a date. All three hold before the label moves: the six open questions in `PRD.md` are
closed, the `/api/v1` response shapes are declared in `ARCHITECTURE.md`, and no `OPEN` defect touches the
API or the schema.

## Release state

No commit has been chosen for `0.1.0`. As of `b6fd4e1`, `master` and `origin/master` are equal, and four
documentation files are uncommitted. A concurrent session lands commits in the same checkout while this is
written: the base advanced through `fc64bc2`, `36b71b4` and `b6fd4e1` during one pass over these files, so
any commit named here is a pointer into a moving branch, not a fixed release point. The first dated entry
in `CHANGELOG.md` appears when a commit is picked, the tree is clean, and the tag exists.

This application runs on loopback and is not deployable. `AGENTS.md` §1 and `PRD.md` §6.10 keep it there.
