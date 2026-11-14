# GP-09b Closeout — stat colours & warm ink (Phases 1-3)

2026-11-15. Phases 1-3 of the stat-colour plan executed, verified and committed. Phase 4 (dark
"Neon Glow") remains **parked and untouched** per the standing ruling -- no `--color-stat-*-bg`
change, no glow/box-shadow utilities.

## Commits

| Commit | Subject |
|---|---|
| `5b8e1a83` | feat(ui): add StatBadge component and integrate into stat input forms |
| `e712598c` | refactor(theme): apply C1a warm-brown ink re-theme to light mode |
| `31b853c1` | chore(cleanup): remove orphaned CareerTimeline component |

The audit/plan documentation trail itself is committed as `f6071350`
(docs(audits): grey-parity arc reports, colour plans and closeout memo).

## Orphan deletion

`CareerTimeline.vue` deleted after re-verification at commit time: zero references in
`resources/js`, `routes/` and `tests/` (imports, dynamic `import()`, path strings, route
definitions, test readers all swept). The live timeline is `pages/Career/Timeline.vue`.

## Final baseline

Full suite after all three commits: **2,415 passed / 37,220 assertions**, re-run after all commits (the two mid-run
pinned-value updates -- TurnEntryFieldSourceTest's badge shape, DESIGN.md's count note 95 -> 102
-- landed before this run). Typecheck and build clean. Pint
clean on every touched file. Lore 615/75, zero new hits from this work.

## Scope boundary

Peer-owned dirty files (hooks, seeders, `KNOWN-ISSUES.md`, `SCREEN_SPEC.md`, reference docs,
`global-setup.ts`) and the untracked peer audit dirs were left untouched; the empty stray
`command'` file in the repo root is peer debris, reported not removed.
