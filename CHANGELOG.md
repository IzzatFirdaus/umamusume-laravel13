# Changelog

All notable changes to Trainer Desk are documented here. The format follows Keep a Changelog, and the
version number follows Semantic Versioning as interpreted for this project in `VERSION.md` and
`docs/adr/0019-semver-label-tag-and-release-branch.md`.

## A note on the history before 0.1.0

This file starts empty on purpose. The repository's whole history predates it, the first commit landing
2026-09-27, and no changelog existed before now. Reconstructing a per-change history after the fact would
mean writing claims about commits that were never recorded as changes, which `AGENTS.md` §5 forbids.
Entries below are written at the time of the change, not back-filled.

## [Unreleased]

### Added

- `.ai/rules/eloquent.md`: casts are declared with a `casts()` method on the model, not a `$casts`
  property. Measured at 17 models against 2.
- `.ai/rules/architecture.md`: enums live in `app/Enums/`; there is no authorization layer, with no
  `app/Policies/` and no custom middleware; browser routes and the `Api/V1` JSON controllers are separate.
- `docs/adr/0019-semver-label-tag-and-release-branch.md`: the version label, the pre-release mechanism, the
  target branch scheme, and the gate to `1.0.0`. Status is Proposed; the owner has not ruled on the open
  items it lists.
- `VERSION.md` and this file.

### Changed

- `.ai/rules/index.md` no longer reads "No rules recorded yet". It is a glob to rule-file table, which is
  the mechanism `AGENTS.md` §2 tells agents to use. Before this change the index had no rows, so
  `code-style.md` and `testing-standards.md` were unreachable through it.
- `.ai/rules/testing-standards.md` describes the database binding that this project actually uses. The
  file previously showed a per-file `use RefreshDatabase;` example importing `Pest\TestCases\TestCase`,
  which is not the path and not how isolation works here. Feature tests inherit
  `uses(TestCase::class, RefreshDatabase::class)->in('Feature')` from `tests/Pest.php`, which also applies
  `withoutVite()` globally and provides the `actingAsAdmin()` helper.
- `docs/adr/README.md`: rows for `ADR-0018` and `ADR-0019` added and the file count corrected to nineteen.
  The table stopped at 0017 while 0018 was on disk, and the prose said seventeen.
- Seven `.ai/skills/*/SKILL.md` files gained the trailing newline `code-style.md` §Formatting Standards
  requires. Content is unchanged, and the copies now match `.agents/skills/` byte for byte.

### Notes on these entries

The four `.ai` entries above are committed, at `cb34264 docs(rules): record the project conventions and
index them by glob`. `VERSION.md`, this file, and `ADR-0019` are the working-tree state as of `b6fd4e1`. A
concurrent session is active in the same checkout, so the base moves while these files are written.

No application code, schema, or copy changed in this set, so no test was added: `AGENTS.md` §9 requires
none for documentation-only work.

The documentation suites that guard these files pass: `DocCitationParityTest`, `DocSchemaDriftTest`, and
`DocCensusResilienceTest`, 8 tests, 20 assertions. The lore gate reports no hits in the files added here.

## [0.1.0] - not released

No commit is chosen and no tag exists locally or on `origin`. As of `b6fd4e1`, `master` and
`origin/master` are equal, so the history is already on the remote and only the release decision is
outstanding. The date and scope land here when the tag is created. See Release state in `VERSION.md`.
