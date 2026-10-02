# Product

<!-- impeccable:product-schema 1 -->

Updated 2026-10-02 from codebase evidence: routes, migrations, controllers, shipped views, and the
accepted ADRs from 0014 through 0017. The sections holding owner-confirmed product facts (Platform,
Users, Product Purpose, Positioning, Brand Commitments) carry the owner's 2026-09-27 statements
unchanged; what was refreshed there is the citation trail, not the claims.
Product truth is `PRD.md`; this file is the product summary for design and agent context. Where this
file and `PRD.md` disagree, `PRD.md` wins.

Product name: **Trainer Desk** (owner decision 2026-09-27, closing PRD OQ-1).
Visual identity contract: `DESIGN.md`.

## Platform

web

## Users

Trainers: players of the Global English version of *Umamusume Pretty Derby* (PRD §2).

- Single user per instance, desktop browser, running the tool on their own machine.
- Reads JP community wikis to plan ahead against Global releases.
- Currently keeps runs in spreadsheets or in one of the four legacy apps this consolidates.
- Audience scope is `[Global]` only, per the owner ruling recorded 2026-09-27 in
  `docs/UMAMUSUME_REFERENCE.md`: strategy-shaped content must be actionable by a Global player.

The Umamusume are a humanoid race; nothing in this product refers to them with equine vocabulary
(lore gate, `CONSTRAINTS.md` C-4).

## Product Purpose

**Trainer Desk**: one local-only tool replacing four abandoned trackers (PRD §1).

- Browse a catalog of Umamusume whose JP and Global data is cross-referenced automatically.
- Log training runs turn by turn and compare planned vs actual skills.
- Export results as CSV or JSON so data is never trapped.
- SQLite, no server, no accounts; fully usable offline against cached data after any successful
  fetch. Every engine-written fact carries a source URL and fetch timestamp.

## Positioning

What only this tool does (PRD §1, FR-B):

- The fetch engine cross-references JP-source data against Global data with a tiered matcher (Exact
  / Alias auto-promote; Fuzzy / None go to a human review queue). The engine proposes, the Trainer
  disposes.
- Provenance on every fact: source URL, fetched timestamp, raw snapshot on disk.
- Deliberately not: a cloud service, a community platform, a game client, a race simulator, or a
  generic tracker platform (PRD §6 non-goals).

## Operating Context

Confirmed operating constraints (from the product owner):

- Desktop-first primary use.
- Solo use: single user per instance, not a team workspace.
- Offline / low-bandwidth conditions are a real constraint the product must tolerate.

Development context (from the codebase):

- Laravel 13 + Blade, fronted by Vite 7 with Tailwind CSS v4 (CSS-first theme config, no
  `tailwind.config.js`) and vanilla JavaScript plus TypeScript sources under `resources/js/`.
- SQLite only, WAL mode + busy_timeout; cache and queue use the `database` stores.
- Local dev via `composer dev` (artisan serve + queue worker + pail + Vite HMR).
- Served on loopback only; the app has no auth surface and must never be exposed (ARCHITECTURE §8).
- Live-ops facts are dated snapshots with a documented re-check path, not evergreen schedules
  (`docs/UMAMUSUME_REFERENCE.md` "How to read the flags").
- Typography is a system stack with no network font: `resources/css/app.css` sets `--font-sans` to
  `ui-sans-serif, system-ui, sans-serif`, and the comment above it records that the skeleton's
  named web font was replaced deliberately. The stock `resources/views/welcome.blade.php`, which
  was the last thing loading a font over HTTPS, has been deleted.

## Capabilities and Constraints

Implemented, read from `routes/web.php` and `routes/api.php` rather than from a summary:

- Catalog browsing: `/umamusume` index and `/umamusume/{slug}` detail. Skills catalog at `/skills`.
- Training runs: full CRUD at `/training-runs`, `/training-runs/create`, and
  `/training-runs/{run}`; per-turn logging at `/training-runs/{run}/turns` with edit and delete.
- Per-turn facts recorded: the five stats, SP, condition, **Energy, Mood and Fans** (`energy`,
  `mood`, `fans` on `turn_entries`), surfaced through `x-mood-pill` and the resource strip.
- Stat ceilings are per run, not a global constant. A turn entry's ceiling for a stat is that
  scenario's own cap: `base_cap` plus the scenario's `cap_bonus` for that stat, clamped to the
  engine `hard_cap`. `StoreTurnEntryRequest` reads all of it from `App\Services\ScenarioCaps`,
  which is the single owner of that arithmetic (`ADR-0015`, accepted 2026-09-30, superseding the
  flat bound `ADR-0002` had set). A run naming no scenario keeps the base cap. The 1200
  halved-gains marker and the scenario ceiling stay two separately drawn markers.
- Skill states `Suggested` / `Acquired` / `Skipped`, written at
  `/training-runs/{run}/skills`.
- Race entries and shop purchases per turn: `/training-runs/{run}/races`,
  `/training-runs/{run}/purchases`.
- Support cards: `ADR-0014` (accepted) authorized card entities for Phase 1, so the catalog,
  `/api/v1/support-cards`, `/api/v1/support-cards/{supportCard}` and the run's deck at
  `/training-runs/{run}/deck` are in scope. What stays cut is card *collection* state: ownership,
  levels and limit-breaks are not recorded.
- Import: `ADR-0017` (accepted) imports a historical run as the CSV this app itself exports,
  through `/training-runs/import` with a preview step. That is not the legacy database import PRD
  §6 keeps cut.
- Review queue: `/review` and `/review/{candidate}` resolve Fuzzy/None match candidates (confirm,
  alias, reject).
- Export: `GET /training-runs/{run}/export/{format}`.
- Fetch engine pipeline: `php artisan uma:fetch` / `uma:reparse` / `uma:backup`, sources declared
  in `config/uma.php`.
- Read-only JSON API under `/api/v1`: `umamusume`, `umamusume/{slug}`, `training-runs`,
  `training-runs/{run}`, `support-cards`, `support-cards/{supportCard}`, with the
  `{ data, pagination }` envelope and the `{ error: { code, message } }` shape.

Not built, by decision (PRD §6, PRE-MORTEM §1 and §4.1 as embedded in
`docs/research-scratch/GOVERNANCE.md`): auth, SPA frontend, a breeding or pairing engine, EAV
storage, Excel export, an event calendar, legacy database import, MySQL or PostgreSQL, any deploy
path, race simulation or race-result prediction, dual localStorage/DB storage, and trainee image
uploads.

Open, and not to be designed as though decided:

- Qualitative next-race readiness is an **open question**, blocked by `ADR-0016`. A shipped
  `x-race-fatigue-chip` and a `RaceFatiguePayload` exist, so fatigue data is recorded; no source
  states the threshold that would turn it into a readiness judgement.

Known gaps:

- One fetch source is configured and approved (`gametora-characters`, 2026-09-27); its robots and
  live-availability verification is still outstanding, and PRD OQ-2 remains partially resolved.
- The framework-default `users` table and `User` model remain unused.

Open questions that bound the design (PRD §7): OQ-1 product name is **closed** (Trainer Desk,
2026-09-27). OQ-2 partially resolved (first source approved, verification outstanding). Still
open: remaining sources (OQ-2), fetch scheduling (OQ-3), planner reference data in the catalog
(OQ-4), and next-race readiness (`ADR-0016`).

## Brand Commitments

Owner-confirmed 2026-09-27 (recorded in `DESIGN.md`):

- Name "Trainer Desk"; tone is a serious single-user workspace, not a mobile companion or community
  platform.
- "Tactical athletics" identity: utilitarian, data-dense. Light base palette with dark opt-in via
  preference resolution (`ADR-0006`). High-contrast muted surfaces, sharp functional accents,
  monospace numerals. No pastel gradients, no soft drop shadows, no equestrian/animal iconography.
- Color anchors and the dark theme are the measured values preserved in
  `docs/research-scratch/DESIGN-CORPUS.md` under its `DESIGN.md` section (promoted to default by
  owner decision), not a newly invented palette.

Inheritance vocabulary is set by the owner ruling of 2026-09-27 in `docs/UMAMUSUME_REFERENCE.md`
§1.5: Inspiration (the system), Legacies (ancestors), Sparks (traits), Legacy Select (the pre-run
screen). Subject to that document's own staleness flags; re-verify dated claims before use.

## Evidence on Hand

- `DESIGN.md` (root): the visual system contract, owner rulings 2026-09-27.
- `docs/research-scratch/DESIGN-CORPUS.md`: the design-research corpus, embedding the measured
  color and contrast research (light and dark ramps, cap-stack spec) and the additive design
  constraints that used to be standalone files.
- `docs/research-scratch/GOVERNANCE.md`: source of truth map, the gate table (C-1 through C-9),
  the pre-mortem analysis of the four legacy repositories including the §4 planner cut and keep
  rulings, and the 2026-09-28 frontend audit findings and resolutions.
- `docs/adr/`: nineteen tracked files, `ADR-0001` through `ADR-0017` plus an index and one
  superseded branch record. Accepted decisions now include the per-scenario stat ceiling
  (`ADR-0015`), support card entities (`ADR-0014`) and historical run import (`ADR-0017`).
  `ADR-0013` is Withdrawn, and the same number is live on `archive/profiles-chain`, which is
  preserved rather than proposed.
- `docs/UMAMUSUME_REFERENCE.md`: source-cited player reference compiled 2026-09-27 (mechanics,
  roster, live-ops snapshot, server terminology map in Section 6, conflict log).
- `docs/data/2026-09-29-global-roster-crosscheck.md`: the dated two-source roster check.
- `docs/research-scratch/SLICE-RECORDS.md`: the slice verification records.
- `docs/scenarios/`: repo-authored scenario guides plus the publisher references consolidated in
  `SCENARIO-PUBLISHER-REFERENCES.md`.
- No user research, analytics, or feedback exists. Design work must not invent testimonials,
  customers, or usage claims.
- Run state a Trainer has not entered is reported as not recorded, never defaulted to zero and
  never drawn as a dash (G-19 and the absence vocabulary ruling in
  `docs/research-scratch/PROCESS-PLANS.md:574`, moved there from `docs/PLAN-UI-UX-2026-10-02.md:225` on 2026-10-03).

## Product Principles

1. Local-only, single Trainer, no auth surface. No feature may assume connectivity, accounts, or a
   second user.
2. Every engine-written fact carries provenance; a fact without provenance is deleted, not stored.
3. `is_manual` rows are sacred: the engine never overwrites a Trainer's correction.
4. The engine proposes, the Trainer disposes: uncertain matches wait in a review queue, they never
   silently merge.
5. Lore integrity: characters are Umamusume, a humanoid race. Equine vocabulary or iconography is a
   hard failure in code, data, docs, and UI (C-4).
6. No over-engineering: every class and feature cites a PRD user story or requirement, or it does
   not ship.
7. Offline resilience: a failed fetch degrades to a log entry plus retained data. It never blanks or
   corrupts the catalog.
8. Run math stays explainable: every computed number traces to Trainer-entered turns plus a
   declared, versioned constant set, and the constants used are shown where the number appears
   (`ADR-0001`). No race simulation, no race-result prediction, no race-day snapshot (PRD §6.11).

## Accessibility & Inclusion

No product-specific WCAG mandate has been set by the owner. Enforced floor instead:
`CONSTRAINTS.md` C-7 requires empty, loading/refresh, and error states on every data view
(antislop R-27), with the loading-state scope interpreted for server-rendered views by
`ADR-0007`. `DESIGN.md` records the current state and the gaps.

## Record history

- 2026-09-27: first record, from codebase evidence plus owner decisions taken the same day.
- 2026-10-02: synchronized with code and ADRs. Turn logging gained Energy, Mood and Fans; the flat
  stat bound became the per-scenario ceiling of `ADR-0015`; support card entities, deck, CSV import
  and the widened API were recorded as built; the offline-font gap closed when the stock welcome
  view was deleted and the system stack confirmed. Dead file pointers were repointed to the
  research-scratch masters that now hold that content, and retired claims are preserved in git
  history at commit `a0f3195`.
