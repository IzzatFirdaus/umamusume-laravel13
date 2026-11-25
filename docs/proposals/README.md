# Trainer Desk proposals directory

## Purpose and authority boundary

This directory holds descriptive proposals, scenario design specifications, visual design records and
implementation assessments. It is **not** an execution authority and **not** a second specification.
`AGENTS.md` §3 and `docs/INDEX.md` place proposals below the quality bar, accepted ADRs, `PRD.md`,
`ARCHITECTURE.md`, `DESIGN.md`, `SCREEN_SPEC.md`, `KNOWN-ISSUES.md` and the five canonical roadmaps
indexed by `docs/roadmap/INDEX.md`. Accepted ideas sequence into a roadmap slice; a proposal never
creates one by itself. Paths and component names stated in the older bodies are claims to verify
against `git ls-files`, not live references.

`docs/proposals/CONVENTIONS.md` in this directory defines the document contract, verdict vocabulary and
maintenance triggers. Read it before updating a file here.

## Inventory and classification (verified 2026-11-24)

| File | Class | Lifecycle | What it owns |
|---|---|---|---|
| `2026-11-19-visual-identity-palette-and-backdrop.md` | Visual design record | Historical proposal, main slices landed and later superseded by `DESIGN.md` | Azure Daybreak, Midnight Circuit and EVA-01 measurements, rejected palette variants, backdrop asset reasoning |
| `URA-Finale-plan.md` | Scenario design specification | Active scenario record with historical slice statuses | URA Finale intended screens, mechanics and per-feature verification history |
| `Unity-Cup-plan.md` | Scenario design specification | Active scenario record with historical slice statuses and a terminal finale-absence ruling | Unity Cup team state, bursts, team races, finale handoff |
| `Trackblazer-plan.md` | Scenario design specification | Active scenario record; wireframes are historical design artifacts, not shipped screens | Trackblazer routes, checkpoints, shop, epithets, Climax |
| `Our-Grand-Concert-plan.md` | Scenario design specification and slice ledger | Active scenario record with evidence-gated slices | Grand Concert phases A-F, established slice identifiers, acceptance records |
| `consolidated-ui-ux-and-remaining-work-2026-11-18.md` | Implementation assessment | Historical assessment; ordering role superseded by the canonical roadmaps | 2026-11-18 phase A-E dependency view and its re-measured claims |
| `remaining-implementation-2026-11-17.md` | Owner-decision package | Historical; P-1..P-12 decision context retained, execution role superseded | Original R-1..R-9 recommendations, rejected alternatives and owner rulings |
| `docs/proposals/README.md` | Directory index | Active | This navigation and coverage view |
| `docs/proposals/CONVENTIONS.md` | Directory conventions | Active | Structure, evidence classes, verification metadata and maintenance contract |

Paths were deliberately left unchanged. Inbound references to the scenario plans and the visual
record exist in `AGENTS.md`, `ARCHITECTURE.md`, `DESIGN.md`, `SCREEN_SPEC.md`, `KNOWN-ISSUES.md`,
ADRs and audit reports; a rename would orphan them.

## Verification baseline

| Field | Measured value |
|---|---|
| Review date | 2026-11-24 |
| Branch / commit | `master` / `3600741e3a67f06e542337de4ad7a517a7ffa188` |
| Application version | `3.0.0`, pre-release (`VERSION.md`) |
| Framework / runtime | Laravel `13.42.0`, PHP `8.5.8`, Inertia Laravel `3.5.1`, Pest `4.7.8` |
| Divergence | `0` behind / `3` ahead of `origin/master` |
| Working tree at measurement | 279 modified, 102 untracked, 27 deleted, 11 added rows after Phase 3 verification and concurrent peer work |
| Verification scope | Static inspection, re-derived token/contrast/asset data, targeted PHP tests (139 passed across three focused runs), read-only DB counts, and focused browser runs from a freshly built isolated worktree under `.scratch-uma/worktrees/`. The peer-owned `public/build` was never rebuilt or bypassed. No full Pest/Playwright sweep was run. |
| Full-suite status | NOT VERIFIED at this HEAD; `VERSION.md` dated notes record the same boundary. Focused checks are not a full-suite claim. |
| Citation census | `python tools/doc_census.py` -> `ratchet: 546 <= 708` at HEAD; `--working-tree` -> `604 <= 708`; baseline unchanged |

Historical measurements inside the documents carry their own dates and commits. This baseline
describes the reconciliation pass only; it does not promote an old measurement to a current one.

**Targeted verification actually executed on 2026-11-24** (tests use the isolated in-memory database
required by `phpunit.xml`, not the owner's development database):

| Command | Result |
|---|---|
| `php artisan test --compact --filter='DesignTokensTest\|UraFinaleSurfacesTest\|UnityCupPanelTest\|TrackblazerPanelTest\|GrandConcertPanelTest'` | 68 passed, 1346 assertions |
| `php artisan test --compact --filter='TeamRank\|SpiritBurst\|PerformanceFoundationTest\|PerformanceReadPathTest\|TrainingDetailPerformanceBoundaryTest\|SaveVeteranTest\|CareerResultTest'` | 69 passed, 623 assertions |
| `php artisan test --compact --filter=DocCitationParityTest` | 2 passed, 5 assertions |
| Isolated `database-theme-matrix.spec.ts -g "persist through a real Preferences save"` | 1 passed; three palettes survive a real save and reach Database/Help on first paint |
| Isolated `database-theme-matrix.spec.ts -g "help holds its heading"` | 1 passed; light/dark/EVA-01 hold heading, axe-clean render and no sideways scroll at 1280 and 320 |
| Isolated `page-header.spec.ts` + `team-race-facility-panel.spec.ts` | 8 passed; closes the stale page-header tier and confirms the KI-127 isolated mount (composed surface still deferred) |
| Isolated backdrop probe (scratch spec) | 1 passed; all three themes render `image-set()` 1x/1.5x with `fixed`/`cover`; forced-colors and the 404 shell render `background-image: none` |
| Isolated `career-profile.spec.ts` | 8 passed, 1 failed; the failure expects an artwork `<img>` that the isolated environment does not mirror. Environment-limited, not a product regression; no assertion weakened. |
| Read-only PDO query on `database/database.sqlite` | 18 `training_runs` (17 `ura_finale`, 1 `unity_cup`), 8 veterans, 39 race entries; stored Unity Cup `team_rank=S`, `team_name=Blue Bloom` |
| `PLAYWRIGHT_PORT=8397 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/docs-recon-1124/browser-8397.sqlite npm run test:browser -- tests/browser/database-theme-matrix.spec.ts -g "persist through a real Preferences save"` | Refused by `global-setup.ts`: `public/build` older than concurrently edited `resources/js`. No browser observation was taken; the fresh-build override was not used. |

These runs cover the named slices' test contracts; they do not prove rendered behaviour, absence
states, or the uncommitted advisor layer. Each scenario document records the same boundary.

## Scenario coverage

| Scenario | Proposal | Mechanics authority | Repository guide | Evidence boundary |
|---|---|---|---|---|
| URA Finale | `URA-Finale-plan.md` | `docs/UMAMUSUME_REFERENCE.md`, `docs/scenarios/01-ura-finale.md`, `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` if present | `docs/scenarios/01-ura-finale.md` | Imported run records are observations, not proof of an interactive screen |
| Unity Cup | `Unity-Cup-plan.md` | `docs/UMAMUSUME_REFERENCE.md`, `docs/scenarios/02-unity-cup.md`, `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` | `docs/scenarios/02-unity-cup.md` | Recorded run data, derived values, eligible event pools and hypothetical reconstruction are separate classes |
| Trackblazer | `Trackblazer-plan.md` | `docs/UMAMUSUME_REFERENCE.md`, `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` | `docs/scenarios/03-trackblazer.md` is a **superseded pre-release snapshot** per `docs/research-scratch/GOVERNANCE.md` §4.1; read the Trackblazer sections of the publisher reference for shipped mechanics | Wireframes are design drawings, not descriptions of implemented screens |
| Our Grand Concert | `Our-Grand-Concert-plan.md` | `docs/UMAMUSUME_REFERENCE.md`, `docs/scenarios/07-grand-concert.md`, newer captures under `docs/audits/` | `docs/scenarios/07-grand-concert.md` | Known-Gap Stub status in `GOVERNANCE.md` §4; evidence-blocked slices stay blocked until a ruling |

`docs/scenarios/08-grand-masters-jp-only.md` is JP-only reference material and is not imported into
Global app data, config or UI copy. Scenario names in application code come only from
`config/scenarios.php`; a hardcoded name in a page or component is a defect (`AGENTS.md` §7).

## Cross-scenario consistency map

Shared functionality is documented once in its owning specification. Scenario plans reference it
and carry only genuine differences.

| Shared concern | Authoritative location |
|---|---|
| Scenario registration, caps, panels, steps and labels | `config/scenarios.php`; stat-ceiling arithmetic in `app/Services/ScenarioCaps.php`; tier table in `docs/research-scratch/GOVERNANCE.md` §4 |
| Career creation and setup flow | `routes/web.php` `career.scenario` through `career.preflight`; `SCREEN_SPEC.md` §§2, 4, 6; `docs/research/flows-career-creation.md` |
| Active, completed, imported and snapshot runs | `app/Models/TrainingRun.php`, `routes/web.php` `runs.*` and `career.snapshot.*`, `docs/research/flows-career-workspace.md` |
| Trainee forms and identity | `app/Models/Umamusume.php`, `docs/research/flows-career-creation.md`, `lang/en/uma.php` |
| Parents, grandparents and Sparks | `app/Models/Legacy/LegacySelectionPayload.php`, `ADR-0010`, `docs/research/flows-veterans-legacy.md` |
| Support deck and card progression | `routes/web.php` `career.deck.*` and `runs.deck.sync`, `resources/js/components/support/`, `docs/research/flows-catalogs.md` |
| Training, race decisions and turn events | `app/Http/Controllers/Career/`, `routes/web.php` `runs.training`, `runs.races.decision`, `runs.events.decision`, `docs/research/SYSTEM-FLOWS.md` |
| Skill recording and hint level | `app/Models/RunSkill.php`, `ADR-0035`, `config/uma.php` `skills.hint_discount` |
| Career objectives and completion | `config/scenarios.php` `objective_priority`, `app/Http/Controllers/Career/ResultController.php`, `SCREEN_SPEC.md` §§2, 4, 6 |
| Veterans, Legacy reuse and transfers | `app/Models/Veteran.php`, `app/Http/Controllers/VeteranController.php`, `docs/research/flows-veterans-legacy.md` |
| Advisor layer | `ADR-0038` is **proposed**; `config/advisor_ai.php` master switch defaults off, so shipped recommendations are unchanged. Uncommitted advisor code in the working tree is not a ruling. |
| Loading, empty, error and unsupported states | `SCREEN_SPEC.md` §§2, 4, 6 and `ADR-0007`; `N/A` with disclosure, never a fabricated zero |
| Persistence and reload | Form Requests in `app/Http/Requests/`, models in `app/Models/`, `docs/research/SYSTEM-FLOWS.md`; suite-green does not prove the dev database matches |

## Active recommendation surface at this baseline

Recommendations are recorded here for navigation only. They do not form a new execution order; the
owning canonical roadmap and `KNOWN-ISSUES.md` decide what is worked.

- **Cross-cutting, evidence-limited:** obtain attributable full Pest and browser runs before quoting the release bar; re-audit the two remaining `N/A` disclosure candidates in `SparkSummary.vue` and `RacePlanner.vue`. KI-127 is closed by an isolated 2/2 fixture, with the composed-surface pin deferred while the panel is a dead registration. KI-104 is closed with a dated body correction against accepted ADR-0027.
- **Visual:** print and forced-colors opt-out, and limiting unconditional fixed backdrop attachment on narrow viewports, remain unimplemented; the EVA-01 green-share target remains recorded as unmet. The `#4A73A0` lines in `DESIGN.md` and `resources/css/app.css` are historical derivation steps, followed in both files by the shipped `#4E79A8`; they are not current-value claims.
- **Owner-gated:** team-race slots and roster/preseason rows stay out of scope without a citable source and ADR; ADR-0038 remains proposed. BO-004 is recorded DONE with `--color-risk: #FF6F3B` (`docs/roadmap/DECISIONS-AND-DEPENDENCIES.md` D-6), and `win-unpacked` is ignored at `.gitignore:92`; neither is an active recommendation.
- **Scenario-specific:** read each plan's `Current-state reconciliation` section. Scenario plans must not be used as a second roadmap, and a historical slice completion does not prove current behaviour.

## Supersession relationships

- The visual record's §4 palette tables and `--backdrop-image: none` promise are superseded by
  `DESIGN.md` §2.1 and its later change log; its measured reasoning is retained.
- The 2026-11-18 consolidation no longer owns ordering. Its dated refutations remain valuable because
  they name work that must not be repeated.
- The 2026-11-17 decision package is historical. P-3 (`win-unpacked`) and P-6 (BO-004) were resolved
  after that pass; P-10 and P-11 remain owner-gated. The rest landed, were resolved by measurement,
  or moved to a canonical roadmap.
- Scenario plan slice statuses are historical per-slice records. Where a plan and the current code
  disagree, the current-state section at the top of that plan is the reading path, and the code plus
  authoritative specs are the evidence.

## Recommended reading order

1. `AGENTS.md` §2, §3, §3a and §9 for precedence and verification rules.
2. `docs/proposals/CONVENTIONS.md` in this directory for how to read and update these files.
3. This index for classification and baseline.
4. The relevant scenario plan's current-state section, then `PRD.md`, `ARCHITECTURE.md`,
   `SCREEN_SPEC.md` §§2, 4, 6, `DESIGN.md` (visual work), `docs/UMAMUSUME_REFERENCE.md` (mechanics) and the
   owning canonical roadmap.
5. The original body of a proposal only for its reasoning, rejected alternatives and historical
   evidence.

## Governing-document pointers corrected in this pass

Several substantiated inconsistencies were corrected narrowly, without changing a requirement,
ruling or agent permission: `ARCHITECTURE.md` §13 now names the five canonical roadmaps as the
execution authority and this directory as descriptive input; the `SCREEN_SPEC.md §8` pointers in
`AGENTS.md` (two), `docs/roadmap/INDEX.md` and `CLAUDE.md` now point to the real inventory/state
sections (the numbering drift is recorded at `SCREEN_SPEC.md:3650`); and the KI-104 body in
`KNOWN-ISSUES.md` carries a dated status correction against accepted ADR-0027 while keeping the filed
text verbatim.

The five canonical roadmaps are currently untracked working-tree files; their 2026-11-24 reconciliation
was made in place without staging or committing them. Treat their status rows as working-tree evidence
until they land. The `#4A73A0` occurrences in `DESIGN.md` and `resources/css/app.css` were examined and left intact:
each is a historical derivation step followed by the shipped `#4E79A8`, not a current token claim.
`PRODUCT-AND-CAPABILITY-ROADMAP.md` and its siblings each claim a *single* authority within their own
domain, which is consistent with the distributed model in `docs/roadmap/INDEX.md`; no correction was
needed. Any future finding that a rule, requirement or owner ruling is wrong belongs in an ADR
erratum or an owner decision, not in a pointer edit.
