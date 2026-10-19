# Changelog

All notable changes to Trainer Desk are documented here. The format follows Keep a Changelog, and the
version number follows Semantic Versioning as interpreted for this project in `VERSION.md` and
`docs/adr/0019-semver-label-tag-and-release-branch.md`.

## A note on the history before 2.0.0

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
- `PRD.md` restructured 2026-10-18 to state current requirements only: superseded text, dated errata,
  amendment notes and open-question history moved out of the requirement lines and into the section
  "PRD history relocated from `PRD.md`" at the foot of this file, and the open-question register into
  docs/OPEN-QUESTIONS.md. No requirement, ID, number or acceptance test was removed or renumbered; the §6
  non-goal numbering is unchanged because `DESIGN.md`, `README.md`, `SCREEN_SPEC.md`, `CLAUDE.md` and
  several ADRs cite `PRD §6.x` by number.
- Seven `.ai/skills/*/SKILL.md` files gained the trailing newline `code-style.md` §Formatting Standards
  requires. Content is unchanged, and the copies now match `.agents/skills/` byte for byte.

- `SKILL.md` renamed to `SKILL_REGISTRY.md` 2026-10-19 and restructured: frontmatter removed (a
  repository root is not one of the five skill scopes, so the file never loaded as a skill), body cut
  to the six-section skeleton (run first, reading the output, coverage gaps, installed/scanned/
  available, violations and `--repair`, provenance), and the seven-row coverage table moved to
  `coverage.md`. History moved out with it: the 2026-10-02 and 2026-10-11 rewrite record, the pinned
  example totals it once carried as warnings (each stale within days of being written - deleted
  rather than relocated, git history keeps them), and the rebuttal paragraphs against `AGENTS.md` §3
  and `GOVERNANCE.md` §6, replaced in the file by one provenance line pointing at `AGENTS.md` §2 (the
  code wins) for the stale generated-file rule. The pairing named there is real: `.agents/skills.json`
  inventories `php artisan skill:manage` commands, not agent skills, so `GOVERNANCE.md` §6's
  machine-readable-half pairing is a naming collision (`AGENTS.md` §8, `docs/SKILL_AUTOMATION.md`).

### Notes on these entries

The four `.ai` entries above are committed, at `cb34264 docs(rules): record the project conventions and
index them by glob`. `VERSION.md`, this file, and `ADR-0019` are the working-tree state as of `b6fd4e1`. A
concurrent session is active in the same checkout, so the base moves while these files are written.

No application code, schema, or copy changed in this set, so no test was added: `AGENTS.md` §9 requires
none for documentation-only work.

The documentation suites that guard these files pass: `DocCitationParityTest`, `DocSchemaDriftTest`, and
`DocCensusResilienceTest`, 8 tests, 20 assertions. The lore gate reports no hits in the files added here.

## [2.0.0] - not released

No commit is chosen and no tag exists locally or on `origin`. As of `b6fd4e1`, `master` and
`origin/master` are equal, so the history is already on the remote and only the release decision is
outstanding. The date and scope land here when the tag is created. See Release state in `VERSION.md`.

## PRD history relocated from `PRD.md` (2026-10-18)

The owner's PRD-restructure task of 2026-10-18 moved every superseded statement, dated erratum,
amendment note and open-question history out of the requirement lines of `PRD.md` and into this file.
Each entry below is the `PRD.md` text as it was written, kept verbatim with the date it carried, newest
first. This is a relocation, not a back-fill: nothing here is reconstructed from commit history, which
the note at the head of this file forbids.

### 2026-10-18 — the header status line was replaced

Replaced, from the top of `PRD.md`:

> Status: Phase 1 (documentation & scaffolding). Revision 0.2 (fourth legacy repository integrated;
> sections changed by it are marked [rev 0.2 — repo #4]). Product name: **Trainer Desk**, owner decision
> 2026-09-27, closing OQ-1.

The product-name fact and the OQ-1 closure stay in `PRD.md` §1 and in docs/OPEN-QUESTIONS.md. The "Revision
0.2" marker set is the next entry.

### Undated in the source — Revision 0.2, fourth legacy repository integrated

`PRD.md` carried the inline tag `[rev 0.2 — repo #4]` on fourteen lines, and the header defined it as
"fourth legacy repository integrated". The tagged lines were: the header, §1, §2's legacy-apps bullet,
US-3, US-4, C-2, C-3, non-goals 7, 8, 9, 11, 12, 13, and OQ-4. The tag narrates which revision changed a
line, not what the line requires, so the tags were dropped and the requirement text they accompanied was
kept. `PRD.md` §2's sentence naming the fourth repository's contribution stands on its own: it proves the
planning workflow (Trainers pre-pick skills before a run and compare plan vs actual afterwards).

### 2026-10-12 — §11 travel for `ADR-0025` (per-run entered ceilings), C-1 and C-2

From `- C-1: `TrainingRun`` and `- C-2: `TurnEntry``, moved out of the requirement lines:

> *§11 travel (2026-10-12).* `ADR-0025` adds four optional per-stat ceilings
> (`cap_speed`/`cap_stamina`/`cap_power`/`cap_guts`) to this requirement's object: each is the figure the
> Trainer read off the client, entered and never derived, and Wit carries none because no ruled surface
> writes it.

> *§11 travel (2026-10-12).* `ADR-0025` puts a ceiling the Trainer entered for this run's stat ahead of that
> arithmetic in `ScenarioCaps::forRun()`, so a stat is accepted to the figure the client showed; the entered
> figure stays bounded by the engine `hard_cap` and may not sit below the highest value the run already
> recorded for that stat. `ADR-0015` stands unchanged as the fallback a run gets when it states no ceiling.

> *Erratum 2026-10-12 (`ADR-0025`, Accepted).* `training_runs` gains four nullable columns `cap_speed`,
> `cap_stamina`, `cap_power` and `cap_guts`, each holding the per-stat ceiling the Trainer read off the
> client for that run (migrated `2026_10_12_140000`). A non-null entered cap outranks the `ADR-0015`
> arithmetic as the bound a stat is validated against; null reverts to it byte-identically. An entered cap
> is bounded 1..`hard_cap` with no floor at the scenario base, may not sit below the highest value the run
> already recorded for that stat, and is Trainer data the engine never writes. Wit carries no `cap_wit`.
> `ScenarioCaps` remains the single ceiling owner and reports which of the two answered.

The column names, the `forRun()` owner and the 1..`hard_cap` bound now stand in C-1 and C-2 as the current
rule. Note for the owner: `PRD.md` wrote "`ADR-0025`, Accepted" while `docs/adr/0025` states
**Status: Proposed** with its `ADR-CONTRACT` decision field reading `proposed`, and the owner ruling
recorded there is "store entered caps, derive nothing" with the wording awaiting approval.

### 2026-10-12 — schema reconciliation erratum on C-2 (`turn_entries`)

Moved out of the requirement line, verbatim:

> *Erratum 2026-10-12 (schema reconciliation).* The implemented `turn_entries` schema carries additional
> columns not listed above: `energy_state`/`energy_band` (**ADR-0024**, Accepted 2026-10-09, migrated
> `2026_10_09_170000`), `facility_speed`/`stamina`/`power`/`guts`/`wit` (UX walk Phase 12, write path
> unwired, no ADR), `failure_rate`/`preview_gains` (UX walk Phase 12, transcribed readings,
> ADR-0016/ADR-0020 §3, no ADR), `choice`/`decision_snapshot`/`corrected_from`/`superseded_by` (Phase G
> ruling D-1a–D-1d, not yet filed as ADR — governance gap). The unique constraint on
> `(training_run_id, turn)` was replaced by a partial unique index `WHERE corrected_from IS NULL` plus a
> plain lookup index (migrated `2026_10_10_200000`).

The column names and the partial-index rule now stand in C-2 as the current schema statement; the migration
file names, the Phase 12 provenance and the "not yet filed as ADR — governance gap" record are the history
this entry keeps. The governance gap is still open: no ADR covers `choice`, `decision_snapshot`,
`corrected_from` or `superseded_by`.

### 2026-10-09 — NFR-5 backup mechanism, `KNOWN-ISSUES.md` KI-78

Moved out of NFR-5, verbatim:

> *Erratum 2026-10-09 (KI-78).* The mechanism named above is retired. `app/Actions/BackupDatabase.php` now
> uses `VACUUM INTO` on its own PDO connection, which reads inside a transaction of its own and leaves the
> source database untouched; the old checkpoint-then-copy form folded the write-ahead log into the main file
> and emptied the `-wal` as a side effect. NFR-5's property — a consistent single-file snapshot — is
> unchanged. Snapshot names are written in UTC (`now()->utc()->format('Ymd-His')`), which is what the
> timestamps in `docs/research-scratch/SLICE-RECORDS.md` already show.

The retired mechanism NFR-5 had named is the "WAL checkpoint, then copy" form. `VACUUM INTO` and the UTC
naming are now the requirement text; the side-effect account of the old form is this entry.

### 2026-10-05 — OQ-6 raised by `ADR-0021`, then narrowed and re-narrowed the same day

Moved out of the open-question entry, verbatim (the "Answered in part" block):

> **Answered in part 2026-10-05; the two sentences above about an empty mirror and an absent component are
> kept as written and are now superseded.** The owner placed the slots and they are built: `resources/js/
> components/ArtworkSlot.vue` renders a frame on the catalog index (trainee header and each costume-form
> row), the trainee detail Identity section, the support-card index and the support-card detail header. The
> mirror is no longer empty by construction — the first `uma:fetch-art` pass resolved all 665 ids into 45 MB
> with zero unresolved, so `ADR-0012` Erratum 4's falsifier did not fire (`ADR-0021` Erratum 1). Every slot
> in the tree is purely visual today: `alt=""` wherever the row already prints the name, and no slot carries
> a rarity marker or any other fact, which answers the "does a slot ever carry information" half as *not
> yet, by choice*.

and (the narrowing block):

> **Narrowed 2026-10-05; re-narrowed the same day after the port.** Three parts of this question are now
> settled and two are not, so the entry stays open rather than being closed against a partial answer.
> **Settled:** the catalog index (trainee card header and costume-form row) and the support-card pair now
> render a mirrored slot, with `design-2.0` §45a fixing placement, geometry and click action; the mechanism
> works end to end, streamed over a loopback route with nothing in the database; and no slot carries
> information — every one is purely visual, which answers the rarity-marker half of the question in the
> negative. **Also unresolved inside the settled part:** the catalog index resolves a trainee's portrait from
> her top-rarity form while the detail page resolves it from the active or first form, so a multi-form
> trainee can show two different portraits across the two screens. That is a placement decision this
> document's owner should make rather than either screen assuming. **One consequence worth recording for
> whoever reads this next:** the settled placements are rendered by `resources/js/components/ArtworkSlot.vue`,
> not by Blade. The Blade slot components built for these screens on 2026-10-05 were deleted the same day,
> because the A1 to A3 ports retired the only screens that used them. A future slot is a Vue component and a
> controller prop, not a Blade partial.

What `PRD.md` §7 and docs/OPEN-QUESTIONS.md now keep is the question, the current status, the four surfaces
that are answered, and the two that are not. The 665-id / 45 MB first-pass figure and the two ADR erratum
cross-references are this entry's record.

### 2026-10-05 — non-goal 13 confirmed unchanged by `ADR-0021`

The `text` code block that sat after the §6 list, moved out of the fenced block into a sentence:

> **Unchanged by `ADR-0021` (2026-10-05)**, which authorizes a different object: game artwork the tool
> fetches itself by id from an allowlisted asset host. There is still no file input, no user-supplied art and
> no route that accepts an upload; see OQ-6.

### 2026-10-04 — `ADR-0020` reverses non-goal 2, extends non-goal 11, adds the Veteran library

Moved out of non-goal 2, verbatim:

> **Reversed 2026-10-04 by `ADR-0020`:** the UI moves to Inertia + Vue as a full rewrite, adopted for the
> interactive-cockpit interaction model.

Moved out of non-goal 11, verbatim:

> Repo #4's manual aptitude-grade predictions and immutable snapshots are cut (Pre-Mortem §4.1); **Amended
> 2026-10-04 by `ADR-0020`:** `ADR-0001`'s Energy-guidance lift is widened to target-based training
> recommendation (ranking Trainer-entered options against a Trainer-entered build target), with all of
> `ADR-0001`'s guardrails carried forward. Race outcomes are unchanged — no race simulation, win-probability,
> or race-day snapshot — reaffirmed on the `ADR-0016` data blocker.

Moved out of non-goal 3, verbatim:

> **Veteran library authorized 2026-10-04 by `ADR-0020` under that recording allowance:** a completed run may
> be saved as a tagged, searchable parent record.

### 2026-10-01 — OQ-5 filed by the Slice 5 Phase A verdict

The filing attribution and the design-research pointer moved here; the answer stays in
docs/OPEN-QUESTIONS.md. Verbatim:

> OQ-5 (OPEN 2026-10-01, filed by the Slice 5 Phase A verdict) ... Phase A's verdict was **(c): do not ship a
> skill recommendation** ... Phase A records the only defensible shape: **derived at read time from
> `condition_groups`, no new column, no backfill, so it cannot drift from the data it reads**
> Phase A therefore left its counts
> unlabelled on purpose.

Note for the census: the design-research file named in the quote above is named here and is not
in the tree; it was a `docs/design-research/` working document. The 123-of-623 (19.7%) measurement, the
`condition_groups` 100% figure, the GameTora 30-key and Game8 `Tier / Skill / Points` source findings and the
number-to-label precondition are current facts and remain in the open-question entry.

### 2026-09-30 — `ADR-0015` supersedes the flat 0..1200 in US-3 and C-2

Moved out of the US-3 acceptance test, verbatim:

> **Superseded in part 2026-09-30 by `ADR-0015`: the flat 0..1200 test becomes a per-stat, per-scenario
> ceiling; a run that names no scenario is still held to 1200, and SP carries no ceiling. Travel for
> `ADR-0025` (the §11 travel that carries it): a ceiling the Trainer entered for that run's stat becomes the
> bound instead, so a stat above the scenario figure is accepted when the client showed it, and a ceiling
> below a value the run already recorded is rejected.**

and out of C-2, the supersession chain the line narrated:

> (`ADR-0015`, which supersedes the flat 0..1200 recorded here and the 0..2000 of `ADR-0002` option B; the
> 1,200 halved-gains line and the scenario ceiling stay two separate visible markers per `ADR-0002`'s UI
> condition)

The three figures are facts worth keeping, so they are stated here once: the original bound was a flat
0..1200, `ADR-0002` option B proposed 0..2000, `ADR-0015` replaced both with a per-stat per-scenario ceiling,
`ADR-0025` put an entered per-run ceiling ahead of it, and `ADR-0002`'s UI condition (the 1,200 halved-gains
line and the scenario ceiling stay two separate visible markers) still holds.

### 2026-09-30 — §6.9's first half lifted by `ADR-0014`, superseding `ADR-0005`

Moved out of non-goal 9, US-12, A-8 and C-6, verbatim:

> Promised by uma-tracker's abandoned PRD, never built anywhere (repo #4's support-card component is an empty
> stub with no backing model); deferred until the cross-reference engine is proven on characters and skills.
> **Partly lifted 2026-09-30 by the owner's Slice 2 authorization, recorded in `ADR-0014` which supersedes
> `ADR-0005`:** ... The cross-reference engine the deferral hinged on is proven: `character_cards` landed
> under `ADR-0008`, and `ADR-0005`'s re-verification exercised the `char_id` join against all 559
> support-card records.

> Authorized 2026-09-30 by the owner's Slice 2 ruling, which supersedes `ADR-0005` and lifts the first half of
> §6.9 (`ADR-0014`). (US-12)

> `[ADR-0014; owner authorization 2026-09-30 lifting §6.9's first half]` (A-8, C-6)

What stays cut is in non-goal 9 as the current rule: no collection, no `user_support_cards`, no levels, limit
breaks or Unique Perk states, and card *tier* labels held for want of a current Global source.

### 2026-09-30 — A-7 profile fields, owner ruling under `AGENTS.md` escalation 2, and its erratum

Moved out of A-7, verbatim:

> [ADR-0012 Decision 4; owner ruling 2026-09-30 under `AGENTS.md` escalation 2]: a trainee profile block on
> the sibling `umamusume_profiles` table, one row per trainee, carrying **four** fields this PRD did not name
> before this line: **voice actor**, **birthday**, **height**, and **three sizes**.

> *[Dated erratum 2026-09-30: the first draft of this line said the source "states a Japanese and an English
> one and they disagree about who speaks for her." The body refutes it — `va_en` is a romanisation, not a
> second cast — and a block that showed only `va_ja` would render Japanese script to a Global reader.]*

The `docs/adr/0012-card-detail-fields-and-images.md` Decision 4 paragraph "Decision 4's PRD citation is
partial", which A-7 said this line upgraded from an owner ruling to a requirement, is this entry's record.
The four fields, the two-script reasoning, the nullability measurements and the three-grain split remain in
A-7 as the current requirement.

### 2026-09-30 — non-goal 3 narrowed by `ADR-0010`

Moved out of non-goal 3, verbatim:

> **Narrowed 2026-09-29 by `ADR-0010`:** "nothing more" caps *computation*, not *recording*. ...
> `training_runs.legacy_selection` now stores what the Legacy Select screen displays, because `CONSTRAINTS.md`
> D-268 found the two references short of what that screen produces.

`ADR-0010` is dated 2026-09-29 in its own status line; `PRD.md` carried the narrowing sentence under the
2026-09-30 block above. What stands as the current rule is in non-goal 3: two optional parent references plus
recorded Legacy selection data, and no inheritance computation.

### 2026-09-29 — D-1 amended and D-3 added by `ADR-0011`; A-6 provenance history

Moved out of D-1 and D-3, verbatim:

> [amended 2026-09-29 by `ADR-0011`] adds **`rarity`**, **the export's own skill id**, and the provenance
> columns `ADR-0004` requires.

> D-3 [added 2026-09-29, `ADR-0011`]

Moved out of A-6's provenance parenthetical, verbatim:

> Provenance is inline on the card row, as the sibling reference tables do (`ADR-0003` Amendment R3;
> `scenario_races`, `scenario_slots` and `race_catalog_slots` carry all four fields, `scenarios` predates the
> set and carries three)

The four provenance field names, the card-level `is_manual` and B-4's role stay in A-6; the sibling-table
comparison and `ADR-0003` Amendment R3 are this entry's record. `ADR-0008` is the ruling A-6 cites today.

### 2026-09-28 — OQ-2 partially resolved, and its original wording

Moved out of OQ-2, verbatim:

> Original wording: candidates observed in legacy docs were community wikis (GamePress, umamusume.wiki) and
> the official JP site; each addition stays a legal/robots.txt review plus one parser class, and the Trainer
> picks.

The standing rule inside that wording — a new source is a legal/robots.txt review plus one parser class, and
the Trainer picks the sources — is current and stays in OQ-2 and in `AGENTS.md` §11.

### 2026-09-27 — OQ-1 and OQ-4 closed by the owner; US-11's key set authorized

Moved out of OQ-4, the closure framing that narrates the decision rather than stating it:

> OQ-4 [rev 0.2 — repo #4]: **Closed 2026-09-27 by the owner: enters as engine-owned facts with provenance.**

Moved out of US-11, the authorization the story line carried:

> Authorized 2026-09-27 for two keys only: the theme (`light` / `dark` / follow the OS) and the numeric
> failure-estimate toggle (off by default, `ADR-0001` §3).

The two-key contract and the off-by-default toggle are current and stay in US-11 and in non-goal 12's
no-browser-storage rule; the date and the `ADR-0001` §3 authority are this entry's record.

OQ-1's closure ("Product name is **Trainer Desk**, owner decision 2026-09-27, closing OQ-1") stays in the
one-line closed entry, because the name is a live requirement, not history. `APP_NAME` in `.env` matching it
at the next config change is still an unexecuted instruction and is recorded as such.
