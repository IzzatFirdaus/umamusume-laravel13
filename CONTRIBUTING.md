# Contributing

## Repository conventions

- **Lore gate** — repository text and code never use equine vocabulary for the Umamusume
  (`docs/research-scratch/GOVERNANCE.md` §"CONSTRAINTS.md (root quality bar, C-1 to C-9)" C-4).

## Documentation gates

Two gates, to run before pushing. This repository has no CI: `.github/workflows/` is absent, so nothing
verifies a change that is not verified locally.

- `php artisan test --filter=DocCitationParity` — fails when dead markdown citations exceed the recorded
  baseline. The limit is `DOC_CITATION_BASELINE` in `tests/Feature/DocCitationParityTest.php`.
- `php artisan test --filter=DocAccuracyTest` — must be 3 passed / 80 assertions / 0 risky.

Two diagnostics, which report where the dead targets are rather than gating on them:

- `python tools/doc_census.py` — prints the citation census and exits 0 whatever it finds. GONE is a
  citation to a file that is absent from the measured subject; UNTRACKED is a citation to a file that is
  on disk but not in git, which works locally and breaks on a fresh clone.
- `python tools/md_fence_check.py <files>` — fenced-code sanity for Markdown.

Constitution greps: `composer lore` and `composer lore-code` — report-only, always exit 0.

**The census measures a git ref, so its number is reproducible.** `tools/doc_census.py` measures `HEAD` by
default and prints the subject it measured on its first line. `python tools/doc_census.py --working-tree`
measures the working tree instead. In a shared tree the two differ, and the difference is the other
sessions' work. `tests/Feature/DocCitationParityTest.php` is the gate.

## Working in a shared tree

- **Commit subjects** are `type(scope): summary`: lowercase, imperative, one line.
- **Documentation-only changes ship without a test**; a behaviour change ships with one. A doc commit
  still runs the documentation gates, and it still carries the lore gate.
- **`AGENTS.md` §11 owns the protected list, and the root document set is closed.** No new markdown file
  is created in the repository root; new documentation content goes into a `docs/research-scratch/` master
  or `KNOWN-ISSUES.md`, and `PRODUCT.md` is regenerated rather than hand-edited.
- **`docs/research-scratch/SLICE-RECORDS.md` section `## execution-ledger-2026-10-17.md` is append-only.**
  One numbered section per dispatch, an earlier section is never edited, and a correction is a new dated
  section that names what it supersedes.
- **This tree holds other sessions' uncommitted work.** Do not revert it, reformat it, or commit it. Scope
  your edits and your tools to your own files, and if a file you need is held by another session, skip it
  and say so in the report.