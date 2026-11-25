# Research Documentation Index — Trainer Desk (`umamusume-laravel13`)

This directory holds flow-level documentation for every system surface in the Trainer Desk codebase. Each file is a standalone audit-style record: what exists, what is verified, what is defective, and at which commit.

## Documentation hierarchy

```
AGENTS.md > GOVERNANCE.md > ADRs > PRD > ARCHITECTURE.md > DESIGN.md > SCREEN_SPEC.md
         > docs/research/ (this directory, flow-level detail)
```

Use this directory when you need to understand how a screen works end to end. Use `docs/adr/`, `PRD.md`, etc. for decisions and specs, not for flow behavior.

## Flow documents

| # | File | Covers | Status |
|---|------|--------|--------|
| 1 | [`SYSTEM-FLOWS.md`](SYSTEM-FLOWS.md) | Index: global concepts, cross-cutting write routes, non-goals, known defects (R6) | Current at `3600741e` |
| 2 | [`flows-navigation.md`](flows-navigation.md) | Request entry, shared Inertia props, Dashboard, three shells, rail, mobile bar, career stepper, reachability rules | Current at `3600741e` |
| 3 | [`flows-career-creation.md`](flows-career-creation.md) | Wizard (6 steps), Snapshot, CSV import, legacy create, Preflight | **Backend committed at `3600741e`; N3.27/N3.31 frontend consumption is working-tree only — frontend-pending** |
| 4 | [`flows-career-workspace.md`](flows-career-workspace.md) | Cockpit, turns, races, skills, events, inheritance, deck, timeline, result, deck builder, Save Veteran, settings/team/scenario, delete, export | Current at `3600741e` |
| 5 | [`flows-veterans-legacy.md`](flows-veterans-legacy.md) | Veterans index/detail/compare, filing rules, Legacy index/builder/compare | Current at `3600741e` |
| 6 | [`flows-catalogs.md`](flows-catalogs.md) | Trainee catalogue, skills catalogue (three scopes), support cards (filters, 3-card compare), artwork | Current at `3600741e` |
| 7 | [`flows-database-hub.md`](flows-database-hub.md) | Hub counts and tiles, nine areas, read-only contract | Current at `3600741e` |
| 8 | [`flows-review.md`](flows-review.md) | Match candidate queue, four verdicts, reopen, bulk reject, boundaries | Current at `3600741e` |
| 9 | [`flows-preferences.md`](flows-preferences.md) | Storage shape, read guards, write canonicalisation, streamed export, `VACUUM INTO` backup | Current at `3600741e` |
| 10 | [`flows-fetch-pipeline.md`](flows-fetch-pipeline.md) | Fetch allowlist, snapshot short-circuit, parsers, normalisation, match tiers, commands, invariants | Current at `3600741e` |
| 11 | [`flows-api.md`](flows-api.md) | `/api/v1` read-only surface, envelope, paging clamp, per-resource notes | Current at `3600741e` |
| 12 | [`flows-help.md`](flows-help.md) | Help route, reachability, content shape, recorded overrides | Current at `3600741e` |

Companion records:

| File | Covers |
|------|--------|
| [`ui-ux-application-context-brief.md`](ui-ux-application-context-brief.md) | UI/UX surface, audit history, round-6 reconciliation summary |
| [`game-mechanics-corpus-source-research-2026-11-18.md`](game-mechanics-corpus-source-research-2026-11-18.md) | External source research for game-mechanics claims in `docs/research-scratch/` |

## Verified state at HEAD `3600741e`

- **HEAD**: `3600741e` (2 commits ahead of `829ef0ac`)
- **Routes**: 104 total (69 GET|HEAD, 98 named non-API routes)
- **Scale**: 62 Vue pages, 119 components, 269 feature tests, 123 browser specs, 37 ADRs, KI-168
- **Working tree**: dirty entries from concurrent workstreams; gate results against `3600741e` unverified
- **Frontend-pending**: N3.27 (`statusNotice` on `ScenarioSelect.vue`) and N3.31 (`comboboxRoster`/`TraineeRow` on `Snapshot/Setup.vue`) present in working tree only — not committed

## Maintenance conventions

### 1. Refresh notes

Every `flows-*.md` file carries dated refresh notes at its head. When a new commit changes the surface this file documents, append a new `Refresh note (YYYY-MM-DD, HEAD `<commit>`)` block before the first `##` heading. Format:

```
Refresh note (YYYY-MM-DD, HEAD `<commit>`): <one sentence on what changed at this commit>. Measured scale at `<commit>`: <scale numbers if measured>. <surface-specific status>.
```

**Do not repeat** repository-wide statistics (route counts, page/component/test counts, ADR count, KI count) that already appear in the Consolidated Revision History below. Per-file notes should cover only surface-specific changes.

### 2. Change-detection matrix

When application code changes, update every document whose surface is affected:

| Application change | Documents to check |
|---|---|
| `routes/web.php` or `routes/api.php` added/removed/renamed route | `SYSTEM-FLOWS.md` (cross-cutting write routes, route inventory), `README.md` (Consolidated Revision History), every `flows-*.md` that lists routes |
| Controller added/renamed/moved | `SYSTEM-FLOWS.md`, the `flows-*.md` that cites the controller in its Evidence line |
| Form Request validation rule added/changed | `flows-*.md` that documents the request contract (e.g. `flows-career-creation.md` for draft step requests, `flows-career-workspace.md` for turn/race/skill requests) |
| Model relationship/scope added/changed | `flows-career-workspace.md` (Cockpit props, turn position), `flows-catalogs.md` (catalog rows), `flows-veterans-legacy.md` (Veteran eager loads) |
| Config key added/changed (`config/scenarios.php`, `config/uma.php`, `config/reference.php`) | `SYSTEM-FLOWS.md` (global concepts), `flows-catalogs.md` (filters/scopes), `flows-database-hub.md` (scenarios tile), `flows-preferences.md` (guards) |
| Migration/seeder added | `flows-database-hub.md` (hub counts), `flows-fetch-pipeline.md` (promote invariants) |
| Vue page/component/layout added/changed | `flows-navigation.md` (shells, rail, mobile bar), `flows-career-creation.md` (wizard steps), `flows-career-workspace.md` (workspace panels), `ui-ux-application-context-brief.md` (IA, navigation) |
| Inertia shared prop added/changed | `flows-navigation.md` (Shared Inertia props), `flows-career-workspace.md` (Cockpit props) |
| Navigation destination added/removed | `flows-navigation.md` (rail destinations, mobile bar), `README.md` (status column) |
| User-visible interaction (button, modal, copy) changed | `ui-ux-application-context-brief.md` (interaction patterns, copy hygiene), relevant `flows-*.md` screen section |

### 3. Status labeling

Distinguish implementation state in every document:

| Label | Meaning | When to use |
|---|---|---|
| **Current** | Describes committed behavior at verified HEAD | Main text sections of `flows-*.md` |
| **Frontend-pending** | Backend committed, frontend consumption working-tree only | README status column, `flows-career-creation.md` refresh note |
| **Unbuilt** | Documented requirement, no implementation | `flows-database-hub.md` §5 (TD-08), `flows-preferences.md` §7 (deliberate absences) |
| **Part-addressed** | Partial fix landed, reconciliation pending | `flows-catalogs.md` §3.5 (R6-24 count split) |
| **Historical** | Accurate at a past commit, may be stale now | Dated refresh notes, `ui-ux-application-context-brief.md` §1–§16 |
| **Proposed** | Design/plan, not built | `ui-ux-application-context-brief.md` (PROPOSED labels), roadmap docs |

### 4. Broken references

Audit files moved from `docs/audits/<name>.md` to `docs/audits/reconciliation-rounds/<name>.md` (reconciliations) and `docs/audits/ux-walkthroughs/<name>.md` (walks). Fix any reference that omits the subdirectory.

### 5. Route counts

When `php artisan route:list --except-vendor` output changes, update the Consolidated Revision History and every per-flow refresh note that cites a route count. Use consistent definitions:

- **Total routes**: all rows from `route:list --except-vendor` (currently 104)
- **GET|HEAD routes**: rows starting with `GET|HEAD` (currently 69)
- **Web GET|HEAD**: non-`/api/v1` GET|HEAD rows (currently 63)
- **API GET|HEAD**: `/api/v1/*` GET|HEAD rows (currently 6)
- **Named non-API routes**: non-`/api/v1` rows with a name before `›` (currently 98)
- **Named API routes**: `/api/v1/*` rows with a name (currently 6)

### 6. Frontend-pending claims

If a backend slice's frontend consumption lands in a commit, remove the `frontend-pending` caveat from all affected `flows-*.md` files and update their refresh notes. Update README.md status column accordingly. If frontend consumption is working-tree only, state that explicitly in the refresh note; do not claim it is shipped.

### 7. Source citations and verification metadata

Every substantive claim in a `flows-*.md` file must carry evidence:

- **Code citation**: file path and optional line number (e.g. `routes/web.php:261`, `CockpitController.php:55`)
- **Test citation**: test class name that pins the behavior (e.g. `VeteranCompareTest`, `RouteCoverageTest`)
- **Commit reference**: when a behavior was introduced or changed (e.g. `3600741e`, `6dc1b41d`)
- **Defect reference**: `KI-nn` or `R6-nn` when a known defect affects the claim

Do not assert accuracy without evidence. If a claim cannot be verified against the current tree, mark it UNVERIFIED rather than asserting it holds.

### 8. Cross-document reconciliation

Documents sharing the same contract must agree:

- **Route inventory**: `SYSTEM-FLOWS.md` cross-cutting write routes, `flows-review.md` routes table, and `routes/web.php` must all list the same routes (e.g. `review/bulk`, `POST`, `review.bulk`)
- **Route counts**: README.md Consolidated Revision History and per-flow refresh notes must use the same definitions
- **Frontend-pending status**: README.md status column and `flows-career-creation.md` refresh note must agree
- **Defect statuses**: `KNOWN-ISSUES.md` (KI-nn) and `SYSTEM-FLOWS.md` known defects table must agree

When one document is corrected, check every other document that cites the same contract.

### 9. Do not document uncommitted changes

If a change exists only in the working tree (uncommitted), state that explicitly. Do not claim it shipped, do not update "Current at" status to include it, and do not remove `frontend-pending` caveats until the commit lands.

## Practical validation

### Markdown and links

Run markdownlint before committing docs changes:

```
npx markdownlint-cli2 "docs/research/*.md"
```

No rules disabled beyond `.markdownlint.json` defaults (MD013 line length, MD024 duplicate headings, MD025 multiple top-level headings, MD033 inline HTML, MD041 first-line heading are all relaxed).

### Internal link and path verification

Verify every referenced repository path exists before committing:

```
ls docs/audits/reconciliation-rounds/ docs/audits/ux-walkthroughs/ docs/research-scratch/AUDIT-AND-VERIFICATION.md
```

Any `docs/audits/` reference missing the `reconciliation-rounds/` or `ux-walkthroughs/` subdirectory is broken.

### Route inventory verification

Verify route counts match documentation:

```
php artisan route:list --except-vendor | grep -c "›"            # total named routes (expect 104)
php artisan route:list --except-vendor | grep "api/v1" | grep "GET|HEAD" | wc -l   # API GET (expect 6)
php artisan route:list --except-vendor | grep "GET|HEAD" | grep -v "api/v1" | wc -l  # web GET (expect 63)
```

If any count differs from README.md Consolidated Revision History, update both the history and all per-flow refresh notes citing it.

### Test pin verification

Verify every test class cited in a `flows-*.md` Evidence/Verification section exists:

```
ls tests/Feature/<CitedTest>.php tests/browser/<cited>.spec.ts
```

### Cross-document reconciliation

After any correction, check sibling documents that cite the same contract:

- Route inventory: `SYSTEM-FLOWS.md` cross-cutting write routes vs `flows-review.md` routes table vs `routes/web.php`
- Frontend-pending status: README.md status column vs `flows-career-creation.md` refresh note
- Defect statuses: `KNOWN-ISSUES.md` (KI-nn) vs `SYSTEM-FLOWS.md` known defects table

## Consolidated Revision History

| Date | HEAD | What changed | Surface-specific |
|------|------|-------------|------------------|
| 2026-11-20 | `e04a6db0` | 226 commits past `67a4830`; CI workflows exist; gates run locally | All flows: baseline refresh |
| 2026-11-22 | `829ef0ac` | 6 commits past `e04a6db0`; N3.27 statusNotice, N3.31 comboboxRoster/TraineeRow backend committed; scale measured | All flows: scale updated |
| 2026-11-23 | `3600741e` | `6dc1b41d` telemetry (LatitudeTraceMiddleware + LangfuseTraceMiddleware on `/api/v1`, both opt-in); `3600741e` five backend slices (N3.27, N3.31, N3.7, N6.5, TR-01, BENO-VET-001); N3.27/N3.31 frontend consumption is **working-tree only** | Scale: 104 routes (69 GET|HEAD: 63 web + 6 `/api/v1`), 98 named non-API routes |

Repository scale at verified HEAD `3600741e` (measured at `829ef0ac`, unchanged by `3600741e`):
- 62 Vue pages, 119 components, 269 feature test files, 123 browser specs, 37 ADRs, KI-168
- Working tree carries dirty entries from concurrent workstreams; gate results against `3600741e` unverified

## What this directory is not

- Not a design spec: use `DESIGN.md` / `SCREEN_SPEC.md` for screen authority.
- Not an ADR register: use `docs/adr/` for architecture decisions.
- Not a tutorial: these are audit-style records, not step-by-step guides.
- Not the source of truth for code: code is always authoritative. If a flow doc and the code disagree, update the doc.
