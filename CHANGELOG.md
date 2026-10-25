# Changelog

All notable changes to Trainer Desk are documented here. The format follows Keep a Changelog, and the
version number follows Semantic Versioning as interpreted for this project in `VERSION.md` and
`docs/adr/0019-semver-label-tag-and-release-branch.md`.

## A note on the history before 2.0.0

This file starts empty on purpose. The repository's whole history predates it, the first commit landing
2026-09-27, and no changelog existed before now. Reconstructing a per-change history after the fact would
mean writing claims about commits that were never recorded as changes, which `AGENTS.md` §5 forbids.
Entries below are written at the time of the change, not back-filled.

## [2.1.1] - 2026-10-24

A PATCH release from the Group A + Group B audit pass. Read-only panels and copy on existing screens,
one config entry, and one write affordance. No new route, screen, API field, or schema change.

### Added

- **Cockpit cap composition** (A-0.1) - the caps the run states beside the blue sparks its Legacy carries,
  with the source's §1.2 non-reconciliation named.
- **Cockpit team roster** (A-0.6) - derived from the six deck cards, with no stored roster.
- **Top Star threshold** (A-0.10) - 240,000 named beside the fan gap.
- **Mood Effect headline** (A-0.11) - the great tier's +20% training / +4% running beside the mood,
  sourced from `config('uma.mood_effect')` (`UMAMUSUME_REFERENCE.md` §1.1.6).
- **Purchasable skill pool** (A-0.8) - the priced skills not yet acquired, sorted by cost, with the sum and
  the shortfall against the recorded SP.
- **Completed-race recording** (B-0.2) - the race card's drawer records a completed race with its
  placement, the write path the trainee-goal ladder reads.
- **Scheduled-not-run line** (B-0.4) - an un-entered calendar race states that it is scheduled, not run.

### Known issues filed

- `KI-115`..`KI-123` - Group A and Group B items not landed: two ruled out (A-0.2 catalog-only, B-0.8
  loopback), four blocked on an ADR (A-0.5, B-0.5, B-0.6, B-0.7), one blocked on A-0.5 (A-0.4), and two
  that do not reproduce (B-0.1 shared finals rounds, B-0.3 snapshot date field).

### Fixed (R4 Phase 1, also first shipped in this tag)

*Dated addition 2026-10-24 (review of the 2.1.1 release): `2.1.1` is the first tag that ships the R4
Phase 1 pass (`d8aaeb27` and the eight commits before it). That work landed after `2.1.0` and had no
changelog entry of its own, so it is recorded here rather than left invisible.*

- **Run id** shipped on the runs list and the cockpit header (R4-01, R4-02).
- **Preflight** step count reconciled and the unofferable purpose copy dropped (R4-04, R4-05, R4-24).
- **Race header** prints the absolute turn; the hardcoded Careers row is gone (R4-09, R4-30).
- **Run deck** renders the career nav (R4-29).
- **Deck picker** names the ownership default (R4-20), matches the composed label and stays open across a
  filter (R4-21).
- **Trainee picker** gains a forward control at the bottom (R4-22) and preserves the filter through the
  select redirect (R4-23).
- **Runs list** breaks the `created_at` tie in its order (`d41d5dc2`).

### Verification

Pest: 96 passed (1371 assertions) on the eight touched and nearby feature files at `cb19af5e`. Typecheck:
`tsc --noEmit` exit 0. Build: `npm run build` clean. Pint: `--test` passed on every changed PHP file.
Browser: the newly added cases are green in focused session-private runs (five Group A cases, two Group B
cases); the browser suite was **not** run as a whole, and two pre-existing reds recorded in the R4 Phase 1
pass report are unchanged and not addressed here.

## [2.1.0] - 2026-10-23

A MINOR release. The frontend architecture migrates to named primitives; one behaviour change ships
(`review-detail` as its own route). No new screens, no new API fields, no schema change.

### Added

- **Primitive layer.** 21 named semantic families in `resources/css/app.css` (`card`, `tile`, `pill`,
  `tag`, `type-chip`, `stat-icon`, `state`, `empty`, `warn`, `note`, `info`, `srow`, `pickcard`,
  `subnav`, `stepper`, `step`, `savebar`, `trust-*`, `mood-*`, `slot-*`, `stat-*`). `.skel` not
  ported (`AGENTS.md` bans decorative skeletons).
- **`Flash.vue`** — the write-confirmation strip extracted from `AppLayout.vue` to a component;
  `role="status"` with a sibling dismiss and the 5 s auto-dismiss moved inside it.
- **`review.detail` route** — `GET /review/{candidate}`, rendering `Review/Detail`. The queue keeps the
  comparison it always made (pill, provenance badge, fetch date, the reason a row has no suggestion) and
  links to the page that decides. The only behaviour change in this release.
- **Seven HFW registry screens** — Dashboard, Runs/Create, SaveVeteran, RacePlanner, Snapshot Entry,
  Setup, Review. Set at 56 pages. Two edge-case states added to existing pages (null-profile form;
  all-absent-at-top-anchor effect table).
- **`@vue/compiler-sfc`** promoted to `devDependencies` (FQ-14=A), and `tools/ui-codemod.mjs` with its
  declarative map: refusal-by-default, exact-set matches only.
- **`tests/baselines/primitives-root.png`** — the primitive gallery baseline, written after a fresh
  build. `ui-screenshot-diff` now defaults to a committed directory, because a baseline only its own
  session can see is not a baseline.

### Changed

- **95 SFCs re-spelled under the CQ-05 criterion.** 50 re-spelled to named primitives; 7 accepted under
  CQ-01 (`Form/*.vue` are primitives at the component layer); 39 accepted under CQ-05 (rule-7 + rule-8
  compliant token utilities). `SparkChip.vue` exists twice on purpose: two live components with
  different props, sharing only a filename (CQ-03 reversed).
- **Contrast gate extended** — 41 pairs + 9 grade badges, both themes, three-way threshold
  (`text 4.5` / `non-text 3.0` / `hairline 1.2`). `--color-risk`, `--color-pick-line` and
  `--color-ring` now covered by name. 89 colour tokens (was 81).
- **The scrim pair was withdrawn** (`45adcab9`): `parseComputedColor()` drops alpha, so a 62 %-black
  overlay was compared to a near-black page as if opaque and measured 1.05:1. The token stays; the
  overlay treatment is enforced by `--shadow-overlay` and its `toContain` assertion.

### Fixed

- **UI-1 heading ladder** on `career-race-planner.html` and `catalog-detail.html`, both themes
  (`9d4c91be`, `0f2524a1`).
- **The review queue fixture resolved a different scratch database than the suite migrated**
  (`16185919`). `scratchDatabasePath()` stamps pid and load-time millisecond, so all 13 review cases
  died in `beforeEach` with "no such table". The fix publishes the migrated path once from
  `global-setup`; third instance of that family, after `b6a7d0f0` and the `SCRATCH_DATABASE` fix.
- **`RouteCoverageTest` route coverage** for the new detail route (`a3332d1c`): a literal navigation and
  an axe scan, with the fixture resetting `sqlite_sequence` so the seeded row has a reproducible id.

### Removed

- **`UraDuelPanel.vue`, `UraMilestoneTracker.vue`** — no production call site; their guard vocabulary
  moved to a `config/scenarios.php` test first, so nothing was lost with them.

### Verification

Full Pest, full Playwright (fresh `npm run build`, before the run and again for the base comparison),
PHPStan L6, `pint --test` scoped to the release-touched paths, `typecheck`, `lint:ui`, the lore gates,
`composer audit` and `npm audit --omit=dev` — all run at tag; outputs under `.scratch-uma/p6/`.

- **Pest:** 1,956 passed, 31,878 assertions, 0 failed.
- **Playwright:** 547 tests, **501 passed, 46 failed** — and all 46 are exactly the reference set at
  `primitive-reconciliation.md §7.4`, compared by `file:line` against that run: **0 new failures,
  0 caused by this release**. §7.4's counts were measured on a matching build at both ends; the
  comparison is in `.scratch-uma/p6/playwright-full.log`.
- **Audits:** `composer audit` clean, `npm audit --omit=dev` clean, after the version bumps below.
- **Lore gates:** `composer lore` 515 docs hits / 75 exempt lines; `composer lore-code` 189 hits. Both
  advisory; every hit is pre-existing and carries the ruling its register records.

### Deferred — pre-existing Playwright failures (46)

The 46 failures in `primitive-reconciliation.md §7.4` remain: **rendering 40, timeout 5, harness 1**.
None is caused by this release; each fails at `b75921b4` with a matching build. Largest blocks are the
scenario panels (`ura-panel` 8, `career-legacy-deck-steps` 6, `support-deck` 5, `unity-cup-panel` 3),
which no phase of this work claimed to finish. The single harness row is
`full-career-journey.spec.ts`: it refuses to run without `PLAYWRIGHT_PORT` and `PLAYWRIGHT_SCRATCH_DB`.

### Deferred — peer-side pint formatting (3)

Per the release bar's scoping, `pint --test` was run on the release-touched paths (0 failures). These
files fail `pint --test` at tag and are not part of this release's change set, so they are recorded
rather than reformatted: `tests/Feature/FinaleReportingTest.php`,
`tests/Feature/TraineeGoalGrainTest.php`, `tests/Feature/TraineeGoalReaderTest.php`. Reformatting
another session's files is the KI-75 damage pattern this repository has already paid for.

> **Dated note 2026-10-24 (verification gap, disclosed).** Three peer commits landed **during** this
> release's Playwright run — `52625c8a` (CSS: wire the `.stat-*` primitives to their tokens, 23:08),
> `b0cf49e4` and `dc00dff9` (a11y: visible reasons on disabled controls, 23:09–23:10). The browser suite
> that produced the figures above started at 22:57 and built its bundle then, so those three commits are
> inside the tag but were **not** measured by it. The Pest run that closed at 23:44 did include them
> (1,956 passed, 0 failed); the browser-side coverage of the CSS change is the open question.
>
> A full Playwright re-run against the tagged tree was started at 00:07 and its result supersedes the
> 501/46 figures once it lands; until then the exposure is one CSS commit touching every surface that
> renders a stat colour, plus two a11y commits whose specs are in the suite. This note exists so the gap
> is on the record rather than discovered later by whoever reads the tag.
>
> **Resolved 2026-10-24 01:02.** The re-run finished against the tagged tree: **547 tests, 501 passed,
> 46 failed** — the same total, and all 46 are again exactly the §7.4 reference set compared by
> `file:line` (**0 new**). The three mid-pass commits changed no browser outcome, so the figures above
> stand and the gap is closed by measurement rather than by argument. Log:
> `.scratch-uma/p6/playwright-tagged.log`.

### Deferred — dependency advisories

None outstanding. `league/commonmark` 2.10.1 → 2.10.2 closes GHSA-3q6v-r5mr-hxv8 (high), `vite`
7.0.7 → 7.3.7 closes its three high advisories, and `npm audit fix` resolved the
`follow-redirects` and `source-map-js` transitives.

## [2.0.2] - 2026-10-22

A PATCH on top of 2.0.1. It corrects one stale browser expectation that 2.0.1's absence-channel
change invalidated, and records what 2.0.1's verification established. No product behaviour changes.

### Fixed

- 	ests/browser/dashboard.spec.ts located the Dashboard's Energy and Team Rank absences by
  getByTitle(...). SubCell renders those reasons on AbsenceValue's disclosure since 2.0.1, so
  the probe matched nothing and the case failed with "element(s) not found". The expectation now
  reads the reason off the details, the idiom career-build-target.spec.ts already records. The
  position line keeps its getByTitle because that tile is a plain span. 16 passed.

### Verification recorded

- Full Pest suite at 2.0.1: **1953 passed (31795 assertions)**, 0 incomplete, 0 skipped,
  0 markTestIncomplete in the log. The D-288/G-18 browser block **ran**: it is included in that
  count and passes on its own at 3 passed / 184 assertions.
- The G-18 gate resolves **computed styles in a real browser** (Playwright), not the stylesheet's
  declared values, so its ratios are rendered measurements. It asserts 31 text/boundary pairs plus
  9 grade-badge fills, in both themes, and fails on any declared token that resolves empty.
- The gate does **not** cover the two pairs the FQ register's FQ-01 and FQ-04 concern
  (--rule-soft and a white-on---risk dark pair): neither token appears in its pair list. A pass
  therefore does not mean those two pass.
- career-cockpit.spec.ts fails without PLAYWRIGHT_SCRATCH_DB because SCRATCH_DATABASE is a
  module-level constant and the worker recomputes it, diverging from the path global-setup
  migrated. It passes with the override set. The defect predates 2.0.1; 2.0.1's harness change is
  necessary but not sufficient, and the structural fix is to publish the path from global-setup.
## [2.0.1] - 2026-10-22

A fixes-only release: it corrects semantics and accessibility and adds no capability. Label per
`docs/adr/0019-semver-label-tag-and-release-branch.md`'s PATCH rule. That ADR is **Proposed**, so it does
not bind; the label is applied because the owner directed this pass to ship as its own release under it.

### Fixed

- **A provenance state outside the union crashed the page.** `ProvenanceMark.vue` indexed its mark map with
  no fallback, and `defineProps` validates nothing at runtime, so an out-of-vocabulary `state` in
  `config/reference.php` rendered `undefined` and threw on `mark.glyph`, taking the whole Database page with
  it. It degrades to no mark now, as `confirmed` already did. `tests/browser/provenance-mark.spec.ts`.
- **The Cockpit's ruleset absence was hover-only.** `N/A` carried its reason in a `title`; it renders through
  `AbsenceValue`'s disclosure now, so the reason is the summary's accessible name and opens without a script.
  `tests/browser/scenario-panel.spec.ts`.
- **`/database/sources` had no door.** The page is registered and served, and the Database hub — the only
  index of Database screens — did not link it, so it was reachable only by typing the URL. The hub carries a
  ninth area. `tests/Feature/DatabaseHubSourcesLinkTest.php`.
- **The Cockpit's section nav rendered an unreachable tab as a focusable `role="link"` with `aria-disabled`**
  and its reason in a `title`, which announces a state without cancelling activation. It is non-focusable
  text now, with its reason as visible prose. `tests/browser/career-section-nav.spec.ts`.
- **`Start career` was enabled while the screen forbade it.** It carried `aria-disabled` and a click guard
  instead of the native `disabled` attribute, so assistive tech and keyboard activation still reached the
  handler. It is natively disabled, with its reason printed beside it.
  `tests/browser/career-preflight.spec.ts`.
- **The review queue labelled one tier fact twice**: a `StatusBadge` "No match" and a `ProvenanceBadge`
  "Unknown" on the same row. The badge is the single owner of the state word, so the duplicate pill is gone.
  `tests/browser/review.spec.ts`.
- **Absent cells and tiles stated their reason in a `title` only.** `DataTable` and `SubCell` render
  `AbsenceValue` now, and six sites in the scenario and Cockpit panels lost a redundant `title` where the
  reason was already printed. `tests/browser/data-table.spec.ts`, `tests/browser/sub-cell.spec.ts`, and
  `tests/Feature/AbsenceChannelTest.php`, which also ratchets the 74 measured sites still outstanding.
- **`DESIGN.md`'s §10 enforcement index described a tree that no longer exists.** The token count is 81 (not
  60 or 59), the D-288/G-18 block is implemented rather than two `markTestIncomplete` stubs, and §8's "zero
  `transition-*`" claim was false. Corrected by dated notes and pinned by
  `tests/Feature/DesignDocEnforcementTest.php`.

### Changed

- `VERSION.md` to `2.0.1`.
- `SCREEN_SPEC.md`: the Database hub's area count, dated notes on `SCR-REV-001`, `SCR-CAR-010`,
  `SCR-CAR-011`, and a new `§7-38` register entry.

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

- `SKILL.md` renamed to `SKILL_REGISTRY.md` 2026-10-19 and restructured: frontmatter removed (a <!-- census: quoted-original -->
  repository root is not one of the five skill scopes, so the file never loaded as a skill), body cut
  to the six-section skeleton (run first, reading the output, coverage gaps, installed/scanned/
  available, violations and `--repair`, provenance), and the seven-row coverage table moved to
  `coverage.md`. History moved out with it: the 2026-10-02 and 2026-10-11 rewrite record, the pinned <!-- census: quoted-original -->
  example totals it once carried as warnings (each stale within days of being written - deleted
  rather than relocated, git history keeps them), and the rebuttal paragraphs against `AGENTS.md` §3
  and `GOVERNANCE.md` §6, replaced in the file by one provenance line pointing at `AGENTS.md` §2 (the
  code wins) for the stale generated-file rule. The pairing named there is real: `.agents/skills.json`
  inventories `php artisan skill:manage` commands, not agent skills, so `GOVERNANCE.md` §6's
  machine-readable-half pairing is a naming collision (`AGENTS.md` §8, `docs/SKILL_AUTOMATION.md`).

- The dashboard's active career reads its progress as a rail of 72 discrete turn marks instead of a
  progress bar, and the rail carries the career's own length: the client's grid is 24 turns to a
  career year over three career years.
- The dashboard's grid follows the shell's layout width, so the Wide layout renders at the wide
  shell's measure instead of self-capping at the standard one.
- The dashboard's active career is weight-emphasized: the trainee's name is the largest thing in its
  card, and the card headings beside it stay at their muted size.
- The dashboard's data-freshness line moved from the Data Status card to the page header, beside the
  page title. The card keeps the live state and the ruleset version.

### Fixed

- The dashboard's turn progress was measured against 24 turns, which is one career year, so the bar
  read as complete from turn twenty-four onward in a seventy-two turn career. The rail now carries
  the model's own career length.

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