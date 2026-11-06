# System Flows Index — Trainer Desk (`umamusume-laravel13`)

Date of writing: 2026-11-06. HEAD: `67a4830`. Branch: `master`. Documentation only; no code changed.

This is the index. Each system has its own flow document under `docs/research/`. Evidence is `routes/web.php`, `routes/api.php` and the controllers/services at the commit above. `php artisan route:list` reports 104 routes: 91 declared in `routes/web.php`, 6 in `routes/api.php`, and 7 framework or tooling routes (`artwork.show`, two `storage/*` entries, `up`, and the Inertia DevTools pair).

Companion: `ui-ux-application-context-brief.md` describes the UI/UX surface and its audit history; these documents describe **flows** — the sequences of screens, writes and persistence that make up each system.

---

## Global concepts

- **Career run (`training_runs`)** — one Trainer's playthrough of one trainee in one scenario. The core entity; most screens require `{run}`.
- **Session draft (`SetupDraft`)** — pre-run state held in the session under `career.setup`; the run is created only at Preflight.
- **Veteran** — a finished career filed into a library. Keyed on its run; only a `Completed` run is recordable.
- **Legacy Lab** — records ancestry per run (`training_runs.legacy_selection`); computes nothing.
- **Scenario** — config-driven (`config/scenarios.php`): `ura_finale`, `unity_cup`, `trackblazer`, `our_grand_concert`. The only place a scenario name enters the layout path.
- **Catalogue** — trainee, skill and support-card reference data, read-only, fed by the fetch pipeline.

---

## Flow documents

| # | File | Covers |
| --- | --- | --- |
| 1 | [`flows-navigation.md`](flows-navigation.md) | Request entry, shared Inertia props, Dashboard props and overlays, three shells, rail (10 destinations, 4 groups), mobile bar, collapsible rail, career stepper and its reachability rule, retired `runs.show` |
| 2 | [`flows-career-creation.md`](flows-career-creation.md) | Wizard (6 steps, draft keys per step, Preflight write), Snapshot (three-way field states, imported position), CSV import (header, 200-row cap, mapping carry, provenance label), legacy create form, Independent Training at creation |
| 3 | [`flows-career-workspace.md`](flows-career-workspace.md) | Cockpit props and panel gating; turn recording and correction; races; skills; events; inheritance; timeline; result; deck builder; Save Veteran; run settings/team/scenario; delete; the full write table; export |
| 4 | [`flows-veterans-legacy.md`](flows-veterans-legacy.md) | Veterans index/detail/compare, filing rules, Legacy index/builder/compare, payload shape, the record-only boundary |
| 5 | [`flows-catalogs.md`](flows-catalogs.md) | Trainee catalogue, skills catalogue (three scopes), support cards (filters, 3-card compare), artwork, shared conventions |
| 6 | [`flows-database-hub.md`](flows-database-hub.md) | Hub counts and tiles, nine areas, read-only contract |
| 7 | [`flows-review.md`](flows-review.md) | Why candidates exist, index/detail, the four verdicts, reopen, boundaries |
| 8 | [`flows-preferences.md`](flows-preferences.md) | Storage shape, read guards, write canonicalisation, streamed export, `VACUUM INTO` backup, deliberate absences |
| 9 | [`flows-fetch-pipeline.md`](flows-fetch-pipeline.md) | Fetch allowlist and politeness, snapshot short-circuit, parsers, normalisation, match tiers, capability routing, promote/provenance, artwork mirror, commands, invariants |
| 10 | [`flows-api.md`](flows-api.md) | `/api/v1` read-only surface, envelope, paging clamp, per-resource notes, what it is not |
| 11 | [`flows-help.md`](flows-help.md) | Help route, reachability, content shape, recorded overrides |

---

## Cross-cutting write routes

| Route | Method | Effect |
| --- | --- | --- |
| `runs.update` | PUT | status, scenario, period, identity, team state, build target, notes |
| `runs.destroy` | DELETE | deletes a run; requires `confirmed` |
| `runs.turns.store` / `.update` / `.destroy` | POST/PUT/DELETE | one turn row (plus failure and Performance events) |
| `runs.races.store` | POST | one race entry |
| `runs.events.store` | POST | one event row |
| `runs.inheritance.store` | POST | one inheritance event |
| `runs.skills.sync` | POST | skill statuses and levels |
| `runs.deck.sync` | POST | replaces the six deck slots |
| `runs.build-target.update` | PUT | the run's target and entered caps |
| `runs.purchases.store` | POST | a `Scenario` event (shop purchase) |
| `runs.indep.abandon` | POST | abandons the 50-minute idle window |
| `runs.veteran.store` | POST | files the career as a Veteran |
| `preferences.update` | PUT | theme, failure estimate, settings blob |
| `preferences.backup` | POST | server-side `VACUUM INTO` snapshot |
| `review.resolve` / `review.reopen` | POST | a match-candidate verdict |
| `career.*.store` (six) | PUT/POST | writes to the session draft; only `career.preflight.store` creates a run |
| `career.snapshot.store` | POST | creates an imported-position run |
| `runs.import.store` | POST | writes a run plus its turns from CSV |

Every write goes through a Form Request. There is no inline `$request->validate()` in a controller.

---

## Explicit non-goals (PRD §6)

No auth or login, no multi-user, no public deploy (loopback only), no image uploads (only tool-fetched artwork, `ADR-0021`), no race-outcome prediction, no simulation, no dual storage, no soft deletes, no Redis, no Excel, no Livewire.

---

## Current known defects touching these flows

From the round-6 reconciliation (`docs/audits/rice-shower-unity-cup-round6-reconciliation.md`, read commit `3f295e80`), the rows that change what a flow can do:

| Row | Flow | Effect |
| --- | --- | --- |
| R6-05 | Creation → Legacy → Preflight | a manual parent `name` is discarded on every write |
| R6-11 | Creation → Deck → Preflight | `card_level` is dropped by the wizard commit |
| R6-12 | Workspace → Timeline | the page reads `after`; the server sends `actual`, and the band has no representation |
| R6-03 | Creation → Trainee | the filter redirect loses the query string; three aptitude facets are dropped |
| R6-04 | Creation → Build target | the total is labelled "scenario caps" but reads the run's entered caps |
| R6-15 | Workspace → Skills | `Hint Lvl Hint Lvl 1` duplicated prefix |
| R6-17 | Workspace → Run settings | the scenario is changeable, though the wizard says it cannot be |
| R6-24 | Catalogs | the Dashboard counts 559 support cards while the picker filters 251 |
| R6-34 | Creation → Snapshot | `scenarioCountdown` is a dead pipe |
| R6-48 | Workspace → Team | the copy names a rank path no form provides |
| R6-53 | Workspace | `notes` is writable from three screens and read by none |

Full classification and the proposed slice order are in that register; `ui-ux-application-context-brief.md` §15 summarises it.
