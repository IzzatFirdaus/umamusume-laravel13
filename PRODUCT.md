# Product

<!-- impeccable:product-schema 1 -->

Updated 2026-10-08 from codebase evidence: `routes/web.php`, the `SCREEN_SPEC.md` §3 screen matrix, the
shipped Vue page set, `config/uma.php` and `config/advisor.php`, and the accepted ADRs from 0018 through
0021. The owner-confirmed sections (Platform, Users, Brand Commitments) keep the owner's 2026-09-27
statements intact: Users gained one bullet drawn from PRD US-14 and nothing was removed from any of them.
What moved is the Trainer Desk 2.0 direction those statements now sit inside, authorized by
`ADR-0020` (owner ruling 2026-10-04) and recorded in `PRD.md` as US-13 to US-15 and FR-F and FR-G.
Product truth is `PRD.md`; this file is the product summary for design and agent context. Where this
file and `PRD.md` disagree, `PRD.md` wins.

Three states are kept apart throughout this record, because the project's own sources keep them apart:

- **Shipped**: read from `routes/web.php` and the `SCREEN_SPEC.md` §3 status column.
- **Authorized direction**: an accepted ADR plus a PRD story, not yet built or built only in part.
- **Exploratory**: `docs/proposals/design-2.0.md` and `docs/proposals/screen-spec-2.0.md`, both filed
  2026-10-04 under the banner "2.0 design target, reference only, not authorization". A screen named in
  those two files is a design target, not a capability, and several of their sections name computation the
  project has ruled banned.

Product name: **Trainer Desk** (owner decision 2026-09-27, closing PRD OQ-1).
Visual identity contract: `DESIGN.md`.

## Platform

web

## Users

Trainers: players of the Global English version of *Umamusume Pretty Derby* (PRD §2).

- Single user per instance, desktop browser, running the tool on their own machine.
- Reads JP community wikis to plan ahead against Global releases.
- Currently keeps runs in spreadsheets or in one of the four legacy apps this consolidates.
- The job is deciding what to do next in a career, not only recording what happened: US-14 states it as
  deciding faster "without the app deciding for me".
- Audience scope is `[Global]` only, per the owner ruling recorded 2026-09-27 in
  `docs/UMAMUSUME_REFERENCE.md`: strategy-shaped content must be actionable by a Global player.

The Umamusume are a humanoid race; nothing in this product refers to them with equine vocabulary
(lore gate, `CONSTRAINTS.md` C-4).

## Product Purpose

**Trainer Desk** began as one local-only tool replacing four abandoned trackers (PRD §1). Trainer Desk 2.0
(`ADR-0020`) widens the proposition: the tool also helps the Trainer make the best decision available right
now, and shows the working behind it. The product statement of that shift is "The app
advises; the Trainer decides" (`docs/research-scratch/PROCESS-PLANS.md`, section `## trainer-advisor.md`
§1).

- Browse a catalog of Umamusume whose JP and Global data is cross-referenced automatically.
- Plan a career before it starts (scenario, trainee, build target, ancestry, support deck), then read and
  decide it turn by turn while it runs (PRD US-13 to US-15).
- Log training runs turn by turn and compare planned vs actual skills.
- Advise the next action against the target the Trainer set, with a reason line for every suggestion
  (`ADR-0020` §2, PRD FR-F).
- Carry a finished career into the next one: a completed run saves as a Veteran, and the Veteran library
  feeds the next career's parent choice (PRD FR-G-3).
- Export results as CSV or JSON so data is never trapped.
- SQLite, no server, no accounts; fully usable offline against cached data after any successful
  fetch. Every engine-written fact carries a source URL and fetch timestamp.

## Positioning

What only this tool does (PRD §1, FR-B):

- The fetch engine cross-references JP-source data against Global data with a tiered matcher (Exact
  / Alias auto-promote; Fuzzy / None go to a human review queue). The engine proposes, the Trainer
  disposes.
- Provenance on every fact: source URL, fetched timestamp, raw snapshot on disk.
- A deterministic, explainable advisor running over the Trainer's own entered data on the Trainer's own
  machine. Each suggestion names the arithmetic it came from, and the constants behind it carry their
  source and the date that source was read (`ADR-0001`, widened by `ADR-0020` §2; PRD FR-F-2 and FR-F-3).
  No recommendation comes from a model the Trainer cannot inspect, and none is invented.
- Deliberately not: a cloud service, a community platform, a game client, a race simulator, or a
  generic tracker platform (PRD §6 non-goals). It is also not an optimizer or an automation: it records,
  it explains, it advises, and the decision and the outcome stay the Trainer's (`PRD.md` §6.3, §6.11).

## Operating Context

Confirmed operating constraints (from the product owner):

- Desktop-first primary use.
- Solo use: single user per instance, not a team workspace.
- Offline / low-bandwidth conditions are a real constraint the product must tolerate.

Development context (from the codebase):

- Laravel 13 with the web surface on **Inertia + Vue 3 + TypeScript** (`inertiajs/inertia-laravel`,
  `@inertiajs/vue3`). `ADR-0020` §1 lifted the "No SPA frontend" non-goal at `PRD.md` §6.2 on 2026-10-04,
  and Phases A to E carried it through: the app is one client-rendered shell over the same loopback-only,
  single-Trainer backend, not a second product. Built by Vite 7 with Tailwind CSS v4 (CSS-first theme
  config, no `tailwind.config.js`). Blade survives only as the Inertia root `resources/views/app.blade.php`
  and `resources/views/errors/`; the Blade shell and its component library were deleted in slice B1.
- SQLite only, WAL mode + busy_timeout; cache and queue use the `database` stores.
- Local dev via `composer dev` (artisan serve + queue worker + pail + Vite HMR).
- Served on loopback only; the app has no auth surface and must never be exposed (ARCHITECTURE §8).
- Live-ops facts are dated snapshots with a documented re-check path, not evergreen schedules
  (`docs/UMAMUSUME_REFERENCE.md` "How to read the flags").
- Typography is a system stack with no network font: `resources/css/app.css` sets `--font-sans` to
  `ui-sans-serif, system-ui, sans-serif`, and the comment above it records that the skeleton's
  named web font was replaced deliberately. The stock `resources/views/welcome.blade.php`, which
  was the last thing loading a font over HTTPS, has been deleted.

The Trainer's workflow, as the shipped product holds it. The two mode names come from the reference-only
2.0 target; the three phases below are what the application actually does, and `ADR-0020` §1 adopted the
interactive-cockpit interaction model as the reason for the rewrite:

- **Before a career (preparation).** The setup wizard collects the scenario, the trainee, the build target,
  the six-node ancestry and the support deck as a draft, and writes once, at Preflight, which creates the
  run. Nothing is created early, so an unfinished plan never appears as a career (`ADR-0020` §1,
  PRD US-13).
- **During a career (active).** The Cockpit and the decision screens around it record the turn the Trainer
  chose and rank the entered options against that Trainer's own target (`ADR-0020` §2).
- **After a career.** The timeline and the result report what was recorded; a finished run may be saved as
  a Veteran, tagged and searched, and the library feeds the next career's parent choice
  (`ADR-0020` §3, PRD FR-G).

The application never presents its own record as the game's authoritative state: it is a record of what the
Trainer entered, plus what the fetch engine promoted with provenance (`ADR-0001`, `ADR-0020` §2).

## Capabilities and Constraints

Shipped, read from `routes/web.php` and the `SCREEN_SPEC.md` §3 matrix rather than from a summary:

- **Dashboard at `/`** as the front door of the 2.0 surface (`ADR-0020` §1; `SCR-CAR-001`). The legacy run
  list and the record screen still serve their own routes; no cutover redirect exists, so the two entry
  points coexist by decision rather than by accident.
- **Career setup wizard**: scenario, trainee and trainee profile, build target, Legacy Select (the
  six-node ancestry), support deck, then Preflight. Each step writes a session draft; Preflight is the
  wizard's one write and creates the run (`ADR-0020` §1, `ADR-0010`, `ADR-0014`, PRD US-13).
- **Career surfaces**: the Cockpit, the Training Decision, the Race Decision, the Scenario Race Planner,
  the Skills Planner, the Event Decision, the Inheritance Event, the Timeline and the Result. Several carry
  no write of their own at all; each one that writes owns a single route, and no existing write gained a
  second owner so that a career screen could exist.
- **Trainer Advisor v1**: ranks the six energy-relevant options (Speed, Stamina, Power, Guts, Wit, Rest)
  against the run's build target and the declared Energy constants; two band states against the single
  sourced threshold; a row-level reason line per suggestion; the constants and their source dates come
  from `config/advisor.php` and are printed beside the figure they produced (`ADR-0001`, `ADR-0020` §2,
  PRD FR-F-2 and FR-F-3, `app/Services/Advisor/TrainerAdvisor.php`).
- **Veteran library**: a completed run files as a Veteran, and the library browses, searches and reads one
  record back (`SCR-VET-003`, whose browser gate ran green). Comparison of up to four careers is committed
  but its gate ran 8 passed and 1 failed, leaving the two-column render unasserted in a browser
  (`SCR-VET-004`), so it is built and not landed rather than verified shipped.
- **Legacy Lab**: browse the saved Legacies, edit one run's six-node ancestry, compare configurations. It
  is record-only: it stores what the Trainer entered and aligns stored rows, and computes nothing
  (`ADR-0020` §3 under `ADR-0010`, PRD FR-G, `SCR-CAR-006`).
- **Reference data**: the Umamusume catalog and detail, the skills catalog, the support-card catalog, and
  the `/database` hub with its eight reference areas.
- **Training runs**: CRUD, per-turn logging with edit and delete, skill states, deck, race entries, shop
  purchases, import of this app's own CSV export, and CSV or JSON export.
- **Per-turn facts recorded**: the five stats, SP, condition, **Energy, Mood and Fans** (`energy`,
  `mood`, `fans` on `turn_entries`), surfaced through `MoodPill.vue` and the scenario resource strip.
- **Stat ceilings are per run, not a global constant.** A turn entry's ceiling for a stat is that
  scenario's own cap: `base_cap` plus the scenario's `cap_bonus` for that stat, clamped to the
  engine `hard_cap`. `StoreTurnEntryRequest` reads all of it from `App\Services\ScenarioCaps`,
  which is the single owner of that arithmetic (`ADR-0015`, accepted 2026-09-30, superseding the
  flat bound `ADR-0002` had set). A run naming no scenario keeps the base cap. The 1200
  halved-gains marker and the scenario ceiling stay two separately drawn markers.
- **Review queue**: `/review` resolves Fuzzy/None match candidates (confirm, alias, reject).
- **Preferences and the Data panel**: the two authorized UI keys (`ADR-0006`, PRD US-11), plus a JSON
  export of this tool's own data and a server-side backup. Restore and reset stay absent because they are
  destructive (`AGENTS.md` §5).
- **Fetch engine pipeline**: `php artisan uma:fetch` / `uma:reparse` / `uma:backup`, sources declared in
  `config/uma.php`; `uma:fetch-art` mirrors artwork by id (`ADR-0021`), read back through the loopback
  `artwork.show` route with nothing stored in the database.
- **Read-only JSON API under `/api/v1`**: `umamusume`, `umamusume/{slug}`, `training-runs`,
  `training-runs/{run}`, `support-cards`, `support-cards/{supportCard}`, with the
  `{ data, pagination }` envelope and the `{ error: { code, message } }` shape.

Authorized, named, and not built:

- A numeric failure estimate for the advisor (`ADR-0001` §3, PRD US-11's toggle): it needs an owner-ruled,
  config-stored, clearly-labelled model because no source publishes a curve. The preference persists and
  changes no calculation, and the screen says so rather than offering a silent switch.
- A race advisory in the advisor, which waits on the `ADR-0016` data blocker.
- Skill icons in the artwork mirror, which need a `skills.iconid` migration of their own (PRD OQ-6,
  `ADR-0021`).

Not built, by decision (PRD §6, PRE-MORTEM §1 and §4.1 as embedded in
`docs/research-scratch/GOVERNANCE.md`, `ADR-0020` §3 and §4): auth, EAV
storage, Excel export, an event calendar, legacy database import, MySQL or PostgreSQL, any deploy
path, race simulation or race-result prediction, dual localStorage/DB storage, and trainee image
uploads. The entry this record used to carry for "SPA frontend" is withdrawn: `ADR-0020` §1 lifted that
non-goal on 2026-10-04 and the rewrite shipped.

The `PRD.md` §6.3 non-goal, "**No breeding/pairing engine**", stands unchanged and its boundary needs
stating here, because Trainer Desk 2.0 adds Legacy-adjacent surfaces. What that item bans is
**computation**, and `ADR-0010` narrowed only the recording half:

- Built and authorized: recording. A run stores its two parent references and the six-node ancestry the
  Trainer chose; a completed run saves as a searchable Veteran; the Legacy Lab browses and compares stored
  configurations; the Inheritance Event records the outcomes the Trainer saw.
- Still banned: nothing computes a Spark firing, an affinity payout, an offspring, an "expected
  inheritance", or a recommended parent combination. "Optimize Parents" and any auto-proposed ancestry are
  computation (`ADR-0020` §3, PRD FR-G-4). A Legacy Lab is a place to record, browse and pick, not an
  optimizer.

Open, and not to be designed as though decided:

- Qualitative next-race readiness is an **open question**, blocked by `ADR-0016`. A shipped
  `RaceFatiguePayload` and the fatigue chip exist, so fatigue data is recorded; no source states the
  threshold that would turn it into a readiness judgement, and the Race Decision renders the figure as
  `N/A` with the blocker named.
- **Which provenance vocabulary the product uses is unsettled.** `ADR-0020` §2 adopted "confirmed /
  calculated / RNG / user input" as the display contract and PRD FR-F-3 repeats it. The shipped
  `ProvenanceBadge.vue`, built to `design-2.0` §49, renders **Confirmed / Calculated / Estimated /
  Unknown** instead, and the same reference target elsewhere proposes Observed / Calculated / Predicted /
  RNG. Per `AGENTS.md` §2 the code wins and the written rule is stale, but the ADR is the higher layer, so
  this record names the disagreement rather than picking a set. What is settled across all three is the
  rule underneath them: no figure may read as more certain than its provenance, and no numeric confidence
  is printed at all.
- **How far the Legacy Lab goes past recording is unresolved.** The recorded six-node ancestry is built;
  the star-roll odds beside a Spark are a sourced absence and render `N/A` (`SCR-CAR-006` gap 2), and the
  reference target's "optimize the entire ancestry" and "low factor probability" warnings are computation
  the ruling holds (`ADR-0020` §3). Whether a planning aid beyond recording and sourced display is ever
  wanted is the owner's call, not this record's.
- The Unity Cup team figures (team rank, league position, burst counts, the team-race rounds) have no write
  path, so the panel reads them as absent; the capture proposal that would settle them is the owner's and
  none of its six open questions is answered (`SCREEN_SPEC.md` §7-6).

Known gaps:

- Every declared fetch source is a GameTora structured-JSON document (eight entries in `config/uma.php` on
  2026-10-08, including the artwork host, which declares no parser and is stepped over). PRD OQ-2 records
  the approval of that dataset family and leaves robots and live-availability verification outstanding; it
  is still outstanding. KI-67: the race catalogue's pinned document has been withdrawn, so `uma:fetch`
  cannot refresh the career calendar at all right now.
- The framework-default `users` table and `User` model remain unused.

Open questions that bound the design (PRD §7): OQ-1 product name is **closed** (Trainer Desk,
2026-09-27). OQ-2 partially resolved (dataset family approved, verification outstanding). OQ-4 **closed**
2026-09-27 as engine-owned facts with provenance. Still open: fetch scheduling (OQ-3), running-style
eligibility on the skill screen (OQ-5, which explicitly refused a `best_for` feature), artwork placement
and skill icons (OQ-6), next-race readiness (`ADR-0016`), and the release-branch question `ADR-0019`
leaves Proposed rather than binding.

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

Trainer Desk 2.0 changes none of this. The rewrite carries the same identity and the same regression-tested
accessibility behavior rather than resetting either (`ADR-0020` §1 and §5), and `DESIGN.md` stays the owner
of the visual system.

Inheritance vocabulary is set by the owner ruling of 2026-09-27 in `docs/UMAMUSUME_REFERENCE.md`
§1.5: Inspiration (the system), Legacies (ancestors), Sparks (traits), Legacy Select (the pre-run
screen). Subject to that document's own staleness flags; re-verify dated claims before use.

## Evidence on Hand

- `PRD.md`: product truth, US-1 to US-15, FR-A to FR-G, §6 non-goals with the dated lifts recorded in
  place, §7 open questions.
- `DESIGN.md` (root): the visual system contract, owner rulings 2026-09-27, audited against the tree
  2026-10-04 and re-read for the ported Inertia pages on 2026-10-08.
- `SCREEN_SPEC.md`: the authoritative screen record of what exists, `SCR-CAR-001` to `024`,
  `SCR-VET-001` to `004`, `SCR-SYS-001` to `010`, with §7 as the gap and conflict register and §8 as the
  authority table.
- `docs/proposals/design-2.0.md` and `docs/proposals/screen-spec-2.0.md`: the 2.0 design target,
  **reference only, not authorization**, each carrying a governance banner over the sections that name
  deferred or banned computation. Their 2026-10-05 "product direction corrections" sections are the same
  instrument's notes, not accepted decisions.
- `docs/proposals/frontend-development-plan.md`: the Phase A to E build plan and its per-slice close-outs;
  a plan, so it cannot override a gate or an ADR (`AGENTS.md` §2).
- `docs/research-scratch/PROCESS-PLANS.md`, section `## trainer-advisor.md`: the subsystem 1 spec the
  shipped advisor is built to, including its out-of-scope list.
- `docs/research-scratch/DESIGN-CORPUS.md`: the design-research corpus, embedding the measured
  color and contrast research (light and dark ramps, cap-stack spec) and the additive design
  constraints that used to be standalone files.
- `docs/research-scratch/GOVERNANCE.md`: source of truth map, the gate table (C-1 through C-9),
  the pre-mortem analysis of the four legacy repositories including the §4 planner cut and keep
  rulings, and the 2026-09-28 frontend audit findings and resolutions.
- `docs/adr/`: twenty-three tracked files, `ADR-0001` through `ADR-0021` plus the derived index and one
  superseded branch record. Accepted decisions include the per-scenario stat ceiling (`ADR-0015`), support
  card entities (`ADR-0014`), historical run import (`ADR-0017`), one facet contract for the filter
  surfaces (`ADR-0018`), the Trainer Desk 2.0 program (`ADR-0020`) and sourced artwork (`ADR-0021`).
  `ADR-0013` is Withdrawn and `ADR-0019` is Proposed; `ADR-0016` carries no verdict by design.
- `docs/UMAMUSUME_REFERENCE.md`: source-cited player reference compiled 2026-09-27 (mechanics,
  roster, live-ops snapshot, server terminology map in Section 6, conflict log).
- `docs/research-scratch/CATALOG-ROSTER-WORKSTREAM.md`: the roster workstream master. It embeds the dated
  2026-09-29 two-source Global roster crosscheck (consolidation log, Provenance items 4 and 5); the
  standalone `docs/data/` files this record used to cite were folded in and deleted, so cite the master.
- `KNOWN-ISSUES.md`: the live defect register, and the source of the KI numbers cited above.
- `docs/research-scratch/SLICE-RECORDS.md`: the slice verification records.
- `docs/scenarios/`: repo-authored scenario guides plus the publisher references consolidated in
  `SCENARIO-PUBLISHER-REFERENCES.md`.
- No user research, analytics, or feedback exists. Design work must not invent testimonials,
  customers, or usage claims.
- Run state a Trainer has not entered renders as `N/A` with a `title` naming the reason: never defaulted to
  zero, never a dash, never the word for absence standing in for a value (`AGENTS.md` §5 Copy,
  `DESIGN.md`'s absence rule, enforced by `RenderedCopyHygieneTest`). The canonical recorded-as-absence
  wording is "not recorded".

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
9. The app advises, the Trainer decides (`ADR-0020` §2, PRD US-14). A recommendation never selects for the
   Trainer, never arrives without a derivable reason, and never implies an outcome the product cannot
   know. Nothing is generated by a model the Trainer cannot read.
10. Recording is allowed; deriving is not (`ADR-0010`, `ADR-0020` §3, PRD FR-G-4). The product may store
    what the Trainer entered and search it, and may display a probability a source states, but it computes
    no inheritance outcome and no race result.
11. A figure never reads as more certain than its provenance (`ADR-0020` §2). A displayed figure carries
    its provenance state beside it, a figure with no state is a defect, and an unsourced figure is a named
    absence rather than an invented one. Which four words the states take is unsettled (see Open above);
    the rule that they must be distinguishable is not.

## Accessibility & Inclusion

The design system sets **WCAG 2.2 level AA on every shipped screen** as its conformance target
(`DESIGN.md` §12), verified by an axe pass plus a keyboard-only pass, 200% zoom, 320px reflow and a
reduced-motion check; the criterion-by-criterion disposition lives in
`docs/proposals/frontend-development-plan.md` §12. `@axe-core/playwright` is installed and
`tests/browser/accessibility.spec.ts` scans pages against `wcag2a`, `wcag2aa` and `wcag21aa`, so the
method exists and runs. Each screen's pass claim is that slice's own record: this file does not re-run
the browser suite and does not certify conformance. The rewrite carries the accessibility behavior that was
regression-tested on the Blade stack (keyboard paths, target sizes, review-form labels, focusable scroll
regions) as an obligation, not an option (`ADR-0020` §5).

The enforced floor underneath it stays: `CONSTRAINTS.md` C-7 requires empty, loading/refresh, and error
states on every data view (antislop R-27), with the loading-state scope read for server-rendered views by
`ADR-0007` and the career surfaces carrying their own `role="status"` loading lines. The 44px house bar,
one base-layer focus ring, and "colour is never the only signal" are recorded in `DESIGN.md`.

## Record history

- 2026-09-27: first record, from codebase evidence plus owner decisions taken the same day.
- 2026-10-02: synchronized with code and ADRs. Turn logging gained Energy, Mood and Fans; the flat
  stat bound became the per-scenario ceiling of `ADR-0015`; support card entities, deck, CSV import
  and the widened API were recorded as built; the offline-font gap closed when the stock welcome
  view was deleted and the system stack confirmed. Dead file pointers were repointed to the
  research-scratch masters that now hold that content, and retired claims are preserved in git
  history at commit `a0f3195`.
- 2026-10-08: brought into agreement with Trainer Desk 2.0 on `ADR-0020` and `PRD.md` US-13 to US-15,
  FR-F and FR-G. The withdrawn "SPA frontend" non-goal is replaced by the shipped Inertia + Vue surface
  (`ADR-0020` §1), the career lifecycle, the build target, the advisor and the Veteran library are recorded
  at product level, and the Legacy Lab boundary is stated as recording versus deriving (`ADR-0010`,
  `ADR-0020` §3). Purpose and Positioning now carry decision support, and three principles (9 to 11) name
  the Trainer-as-decision-maker relationship, the recording ceiling, and the certainty rule. Accessibility
  was stale: `DESIGN.md` §12 sets WCAG 2.2 AA as the target. Dead pointers fixed: the ADR count, the
  `docs/data/` roster crosscheck (now cited through `CATALOG-ROSTER-WORKSTREAM.md`), the `x-mood-pill`
  Blade name (the component is `MoodPill.vue`), and the absence-vocabulary citation, which pointed at a
  code block and at gate G-19, a token-palette gate. Left open on purpose: the readiness question
  (`ADR-0016`), which provenance vocabulary governs, how far the Legacy Lab goes past recording, the
  Unity Cup capture proposal, and OQ-3, OQ-5 and OQ-6. No owner-confirmed field was reopened, and no
  screen composition or visual token was copied in: those stay with `SCREEN_SPEC.md` and `DESIGN.md`.
