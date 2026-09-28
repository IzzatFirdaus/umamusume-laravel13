# Product

<!-- impeccable:product-schema 1 -->

Updated 2026-09-27 from codebase evidence plus owner decisions recorded the same day.
Product truth is `PRD.md`; this file is the product summary for design and agent
context. Where this file and `PRD.md` disagree, `PRD.md` wins.

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
  `docs/UMAMUSUME_REFERENCE.md`: strategy-shaped content must be actionable by a Global
  player.

The Umamusume are a humanoid race; nothing in this product refers to them with equine
vocabulary (lore gate, `CONSTRAINTS.md` C-4).

## Product Purpose

**Trainer Desk**: one local-only tool replacing four abandoned trackers (PRD §1):

- Browse a catalog of Umamusume whose JP and Global data is cross-referenced automatically.
- Log training runs turn by turn and compare planned vs actual skills.
- Export results as CSV or JSON so data is never trapped.
- SQLite, no server, no accounts; fully usable offline against cached data after any
  successful fetch. Every engine-written fact carries a source URL and fetch timestamp.

## Positioning

What only this tool does (PRD §1, FR-B):

- The fetch engine cross-references JP-source data against Global data with a tiered
  matcher (Exact / Alias auto-promote; Fuzzy / None go to a human review queue). The
  engine proposes, the Trainer disposes.
- Provenance on every fact: source URL, fetched timestamp, raw snapshot on disk.
- Deliberately not: a cloud service, a community platform, a game client, a race
  simulator, or a generic tracker platform (PRD §6 non-goals).

## Operating Context

Confirmed operating constraints (from the product owner):

- Desktop-first primary use.
- Solo use: single user per instance, not a team workspace.
- Offline / low-bandwidth conditions are a real constraint the product must tolerate.

Development context (from the codebase):

- Laravel 13 + Blade, fronted by Vite 7 with Tailwind CSS v4 (CSS-first theme config,
  no `tailwind.config.js`) and vanilla JavaScript.
- SQLite only, WAL mode + busy_timeout; cache and queue use the `database` stores.
- Local dev via `composer dev` (artisan serve + queue worker + pail + Vite HMR).
- Served on loopback only; the app has no auth surface and must never be exposed
  (ARCHITECTURE §8).
- Live-ops facts are dated snapshots with a documented re-check path, not evergreen
  schedules (`docs/UMAMUSUME_REFERENCE.md` "How to read the flags").

## Capabilities and Constraints

Implemented (verified against `routes/web.php` and committed views/tests):

- Catalog browsing: `/umamusume` index with release-status filter, normalized search;
  `/umamusume/{slug}` detail with aliases and provenance.
- Training runs: full CRUD at `/training-runs`, per-turn stat logging validated
  0..1200 in shipped code (ADR-0002, accepted 2026-09-27, rules the bound to
  0..2000 with soft-cap/scenario-ceiling display distinct; validation change
  not yet implemented), skill states Suggested / Acquired / Skipped.
- Review queue: `/review` resolves Fuzzy/None match candidates (confirm, alias, reject).
- Export: `GET /training-runs/{run}/export/csv|json`.
- Fetch engine pipeline: `php artisan uma:fetch` / `uma:reparse` / `uma:backup`
  (`config/uma.php`; one owner-approved source, `gametora-characters`, 2026-09-27;
  PRD OQ-2 partially resolved).
- Read-only JSON API: `/api/v1/umamusume`, `/api/v1/training-runs` with the
  `{ data, pagination }` envelope and the `{ error: { code, message } }` shape.

Not built, by decision (PRD §6, Pre-Mortem §1 and §4.1): auth, SPA frontend, breeding
engine, EAV storage, Excel export, event calendar, legacy database import, MySQL/PG,
support cards, any deploy path, race simulation or predictions, dual localStorage/DB
storage, trainee image uploads.

Known gaps:

- `resources/views/welcome.blade.php` (framework default) links an external font from
  fonts.bunny.net, which contradicts the offline constraint; the app's own layout
  component does not.
- One fetch source is configured and approved (`gametora-characters`); its robots/live
  availability verification is still outstanding (OQ-2, partially resolved).
- The framework-default `users` table and `User` model remain unused.

Open questions that bound the design (PRD §7): OQ-1 product name is **closed**
(Trainer Desk, 2026-09-27). OQ-2 partially resolved (first source approved 2026-09-27,
verification outstanding). Still open: remaining sources (OQ-2), fetch scheduling (OQ-3),
planner reference data in catalog (OQ-4).

## Brand Commitments

Owner-confirmed 2026-09-27 (recorded in `DESIGN.md`):

- Name "Trainer Desk"; tone is a serious single-user workspace, not a mobile
  companion or community platform.
- "Tactical athletics" identity: utilitarian, data-dense. Light base palette with
  dark opt-in via preference resolution (ADR-0006).
  High-contrast muted surfaces, sharp functional accents, monospace numerals.
  No pastel gradients, no soft drop shadows, no equestrian/animal iconography.
- Color anchors and the dark theme are the measured values from
  `docs/design-research/DESIGN.md` §3 (promoted to default by owner decision),
  not a newly invented palette.

Still a framework default, not yet a commitment: `--font-sans` in
`resources/css/app.css` references "Instrument Sans" (fetched externally only by
the stock welcome page). `DESIGN.md` proposes replacing it with a system stack.

## Evidence on Hand

- `DESIGN.md` (root): the visual system contract, owner rulings 2026-09-27.
- `docs/design-research/DESIGN.md`: screenshot-anchored color/contrast research
  with measured light and dark ramps (its §3.3, §3.7) and the cap-stack spec
  (§6.21) referenced by `docs/adr/ADR-0002`.
- `docs/adr/`: three accepted ADRs (energy guidance, stat caps, schema
  expansion) recorded 2026-09-27.
- `docs/UMAMUSUME_REFERENCE.md`: source-cited player reference compiled
  2026-09-27 (mechanics, roster, live-ops snapshot, server terminology map in
  Section 6, conflict log). Inheritance wording set by owner ruling
  2026-09-27: Inspiration (system), Legacies (ancestors), Sparks (traits),
  Legacy Select (pre-run screen), per its §1.5. Subject to its own staleness
  flags; re-verify dated claims before use.
- `docs/PRE-MORTEM.md`: risk analysis of the four legacy repositories
  consolidated, including the feature-by-feature cut/keep rulings for the
  planner app (§4).
- `docs/scenarios/` exists in the tree (authored separately; verify before
  citing).
- No user research, analytics, or feedback exists. Design work must not invent
  testimonials, customers, or usage claims.

## Product Principles

1. Local-only, single Trainer, no auth surface. No feature may assume connectivity,
   accounts, or a second user.
2. Every engine-written fact carries provenance; a fact without provenance is deleted,
   not stored.
3. `is_manual` rows are sacred: the engine never overwrites a Trainer's correction.
4. The engine proposes, the Trainer disposes: uncertain matches wait in a review
   queue, they never silently merge.
5. Lore integrity: characters are Umamusume, a humanoid race. Equine vocabulary or
   iconography is a hard failure in code, data, docs, and UI (C-4).
6. No over-engineering: every class and feature cites a PRD user story or requirement,
   or it does not ship.
7. Offline resilience: a failed fetch degrades to a log entry plus retained data. It
   never blanks or corrupts the catalog.
8. Run math is deterministic and explainable: every computed number traces to
   Trainer-entered turns (no simulation, no speculation).

## Accessibility & Inclusion

No product-specific WCAG mandate has been set by the owner. Enforced floor instead:
`CONSTRAINTS.md` C-7 requires empty, loading, and error states on every data view
(antislop R-27), and the shipped views use labeled controls, semantic tables and lists,
and one h1 per page. `DESIGN.md` records the current state and the gaps.
