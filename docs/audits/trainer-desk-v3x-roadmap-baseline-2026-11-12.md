# Trainer Desk 3.x roadmap — Phase 0.1 repository baseline

**Date:** 2026-11-12
**Inspected commit:** `f029a806` (`docs(audits): reconcile F-series ownership and dispositions`), short form of
`f029a8068d4845d5aa9c23cffe875d98b7577654`, dated 2026-11-12
**Branch:** `master`, 16 commits ahead of `origin/master`, 0 behind
**Tags:** `0.1.0`, `2.0.1`, `2.0.2`, `2.1.0`, `2.1.1`. No `3.0.0` tag exists.
**Working tree:** 24 paths — 3 modified, 21 untracked (enumerated in §2)
**Scope of this pass:** establish the baseline and rule a destination for each roadmap deliverable.
No implementation, no migration, no test run, no file moved. One file added: this one.

---

## 1. What the project actually is, measured

| Fact | Measured value | Command that re-derives it |
| --- | --- | --- |
| Version | `3.0.0`, pre-release | `VERSION.md` |
| Framework | Laravel `13.32.0` ��� | `composer.lock` |
| PHP | 8.5.8 runtime against `require.php: ^8.3` | `VERSION.md` |
| Web surface | Inertia + Vue 3 SFCs: 54 page files, 117 `.vue` under `components/` | `ls resources/js/pages/**/*.vue \| wc -l`; `ls -R resources/js/components \| grep -c '\.vue$'` |
| Routes | 104 | `php artisan route:list --json` |
| Write routes (non-API) | 30 | `php artisan route:list --json` filtered to POST/PUT/PATCH/DELETE |
| Screens in spec | 30 `SCR-CAR-*` rows; 27 numbered gap entries in `SCREEN_SPEC.md` §7 | `grep -cE '^\| SCR-CAR-' SCREEN_SPEC.md` |
| Tests | 245 Feature files, 9 Unit, 120 browser specs | `ls tests/Feature/*.php \| wc -l`, etc. |
| Open defects | 69 OPEN, 84 CLOSED, 2 CLOSED-MEASURED, 5 CLOSED-WONTFIX (KI-1..KI-160) | `grep -oE 'KI-[0-9]+ (OPEN\|CLOSED[A-Z-]*)' KNOWN-ISSUES.md` |
| UI-lint debt | 209 arbitrary values, 24 allowlisted entries | `tools/ui-lint-baseline.json` |
| Doc masters | 21 files under `docs/research-scratch/` | `ls docs/research-scratch/*.md` |
| Root markdown | 15 files (13 named in `AGENTS.md` §14 plus `CONTRIBUTING.md` and `SKILL.md`) | `ls *.md` |

**The project is 3.x, not 2.0.** `VERSION.md` reads `3.0.0` pre-release for the HFW-parity landing of
2026-10-27; `2.0.1`, `2.0.2`, `2.1.0` and `2.1.1` are tagged and behind it. The roadmap document's title
and its "Trainer Desk 2.0" framing are wrong on the version, and two of its deliverable filenames carry
`trainer-desk-2`. Recorded as a TODO correction in §5, not as a documentation edit here.

---

## 2. Working tree, path by path

**3 modified:**

| Path | +/− | Owner | Note |
| --- | --- | --- | --- |
| `docs/UMAMUSUME_REFERENCE.md` | +381 / −66 | this program | The 2026-11-12 reconciliation, 2931 lines at `HEAD` → 3246 on disk. Uncommitted. |
| `docs/framework-ui-ux/_audit-prompt.md` | +38 | peer | Modified by another session. `post-remediation-verification-handoff.md` §C names it as this workstream's, at odds with the peer note there; unresolved, so untouched. |
| `docs/research/ui-ux-application-context-brief.md` | +109 | peer | Pre-existing, ownership not established. Untouched. |

**21 untracked:**

- `docs/audits/trainer-desk-v3-ux/` — 8 sibling reports + `screenshots/` (59 PNG) + `scenario-research/` (6
  files, 1,533 lines). Only `04-ux-findings.md` is tracked in that directory.
- `docs/audits/trainer-desk-v3-ux-retest/` — 10 reports + `screenshots/` (72 PNG). Nothing tracked.
- `docs/audits/RS-UC-2026-13/`, `docs/audits/RS-UC-SCENARIO-DETAIL-2026-11-09.md`,
  `docs/audits/RS-UC-SCENARIO-DETAIL-2026-11-09/`, `docs/research/RS-UC-SCENARIO-DETAIL-2026-11-09/`,
  `docs/research/scenario-detail-design-language-survey-2026-11-09.md` — peer-owned.
- `docs/audits/audit-methodology-and-remediation-specs.md`, `post-remediation-verification-handoff.md`,
  `progress-report-2026-11-12.md`, `remediation-phase-progress-report.md`, `walk-8-follow-up-plan.md` —
  audit records from the remediation phase.

**Ownership rule that follows.** `docs/audits/trainer-desk-v3-ux/` and `…-retest/` are recorded as
**peer-owned, explicit prohibition** in `post-remediation-verification-handoff.md` §C. They are inputs to
this program and are not this program's to stage.

**Seven commits landed after the suite baseline** (`a9c2b142..HEAD`): `3aee95e2`, `06a0a8d3`, `04894584`,
`050da391`, `8e46c412`, `f192f75a`, `f029a806`. Code touched: `ScenarioSelect.vue`, `TraineeSelect.vue`,
`TrainingDetail.vue`, four browser specs. The 2,325-pass figure in §6 is therefore one commit-series
behind `HEAD`, and that gap is disclosed rather than closed by a fresh run.

---

## 3. Deliverable map — all 59 roadmap slices

Verdict vocabulary: **EXISTS** (shipped and covered by tests; nothing to build), **PARTIAL** (something
real ships, and a named half does not), **MISSING**, **NEEDS-DECISION** (cannot be started or scoped
without an owner ruling).

Tally: **29 EXISTS · 18 PARTIAL · 5 MISSING · 7 NEEDS-DECISION.**

| Slice | Verdict | What already ships / what is open |
| --- | --- | --- |
| 0.1 baseline | NEEDS-DECISION | This document. Destination ruled in §4; content measured here. |
| 0.2 blocking decisions | PARTIAL | `scenario-research/scenario-information-gaps.md` §3 already writes 4 of the 6 as one-line rulings with evidence and a recommendation. F-08 is closed (§5 item 4). Notice-archive count is an instrument question. |
| 0.3 change-boundary plan | MISSING | Not written. The measured inventory it needs is §2. |
| 1.1 route/screen inventory | PARTIAL | `SCREEN_SPEC.md` §2/§3 owns the matrix (30 `SCR-CAR-*`, route names pinned by `DocAccuracyTest`); no single route→controller→page map exists. |
| 1.2 Cockpit audit | PARTIAL | `04-ux-findings.md` (981 lines, HEAD `bd0a8693`) + `05-cross-cutting-quality.md`, retested at `00b5cfef`. Ten commits stale; refresh, do not re-audit. |
| 1.3 scenario UX audit | PARTIAL | Six `scenario-research/` deliverables (1,533 lines) + `03-scenario-comparison.md`. No Grand Concert live walk; 5 F-series findings unmeasured. |
| 1.4 defect verification | PARTIAL | `post-remediation-verification-handoff.md` §B and `f-series-ownership-inventory-2026-11-12.md` classify 13 F items and both suite failures. F-05/06/07/09/10 remain OPEN — UNMEASURED. |
| 1.5 quality + a11y audit | EXISTS | `05-cross-cutting-quality.md`, `tests/browser/accessibility.spec.ts`, `ui-hygiene.spec.ts`, the D-288/G-18 contrast gate. |
| 1.6 implementation backlog | NEEDS-DECISION | Four registers already serve: `SCREEN_SPEC.md` §7 (27 entries), `KNOWN-ISSUES.md` (69 open), `09-consolidated-remediation-plan.md` (6 items, all closed or refuted), `walk-8-follow-up-plan.md`. Whether a fifth 13-field document is wanted is an owner call. |
| 2.1 career lifecycle | PARTIAL | `RunStatus` = `Active`/`Completed`/`Retired`; independent runs carry their own status; archival is the `veterans` table, not a run state. No contract document. |
| 2.2 scenario configuration | EXISTS | `config/scenarios.php` is the declared single source of truth (D-240, gate G-33), verified 2026-11-02, four keys: `ura_finale`, `unity_cup`, `trackblazer`, `our_grand_concert`. `ScenarioIsolationTest`, `ScenarioMetadataTest`, `ScenarioCapParityTest`. |
| 2.3 read-model contract | PARTIAL | `CockpitController` carries ~25 named section builders plus `ScenarioSectionBuilder`; the contract exists in code and not as a document. |
| 2.4 shared terminology | NEEDS-DECISION | Gated on 0.2. Measured: the reference correction has restored `Result Points`; the app still prints `Grade Points`. |
| 2.5 validation/persistence | EXISTS | FormRequest on **26 of 30** product write routes; the 4 without one (`career.trainee.carry`, `review.reopen`, `runs.indep.abandon`, `runs.turns.destroy`) are route-model-bound and take no payload. Zero inline `$request->validate()` in any controller. `ADR-0025` (entered caps), `ADR-0029` (measurement columns). *(Corrected 2026-11-12, 0.3 pass: the original sentence said "all 30".)* |
| 3.1 Cockpit IA | NEEDS-DECISION | `Cockpit.vue` is 1,157 lines, and 8 panels plus ~20 `career/` components already exist. A split is a refactor against a composition re-baselined at v3.0.0 (KI-139) — owner call, not a given. |
| 3.2 header + progression | EXISTS | `CockpitIdentityCard.vue`, `headerSection()`, `CareerPositionLabel`, `career-cockpit.spec.ts`. |
| 3.3 stats/aptitudes/growth | EXISTS | `StatSummary.vue`, `AptitudeGrid.vue`, `AptitudeBadge.vue`, `capCompositionSection()`, `ScenarioCaps` (ADR-0015, ADR-0025). |
| 3.4 training decision | EXISTS | `TrainingDecisionController` (858 lines), `TrainingDetail.vue`, `TrainingCard.vue`, `ActionGrid.vue`, `career-training-detail.spec.ts`. |
| 3.5 obligations/deadlines | EXISTS | `objectiveSection()`, `obligationSlots()`, `dueLabel()`, `ObjectiveRow.vue`, `NextDecision.vue`; UX-09 residual closed by `339035b5`. |
| 3.6 warnings/recommendations | EXISTS | `AdvisorRail.vue`, `NextDecision.vue`, `App\Services\Advisor`, `AdvisorRail`/`NextDecisionTest` coverage. |
| 3.7 navigation + write-path preservation | EXISTS | Already discharged: `SCR-RUN-003` retired 2026-10-09 (F2), all writes have a Cockpit owner. See §5 item 3. |
| 4.1 URA Finale | EXISTS | `UraMandatoryLadder.vue`, `UraPreFinalReview.vue`, `HappyMeekPanel.vue`, duel section, `UraPanelTest`, `UraFinaleSurfacesTest`, panel spec. |
| 4.2 Unity Cup | EXISTS | `UnityCupPanel.vue`, `UnityCupTeamBoard.vue`, `UnityCupTournamentReadiness.vue`, `SpiritBurstsPanel.vue`, `TeamRankLadderPanel.vue`, `TeamRaceFacilityPanel.vue`, `UnityCupEditor.vue`; three feature tests; spirit proxy migration 2026-11-10. |
| 4.3 Trackblazer | PARTIAL | `TrackblazerPanel`/`Fatigue`/`RaceCalendar`/`ResourceLedger`/`RoutePlanner` and `ShopPurchasePayload` ship. `grade_points` and `shop_coins` have **no stored run column**, so the strip prints `N/A` by design (`ScenarioSectionBuilder` comment, KI-154). |
| 4.4 Our Grand Concert | PARTIAL | Performance foundation complete (5 slices), finale state/execution, advisor, cockpit integration. Lessons, Songs, Live Bonuses, Promo Concerts are **BLOCKED BY EVIDENCE** — `SCREEN_SPEC.md` §7-19, §7-21, §7-22, §7-23, each with a measured reopen condition. See §5 item 5. |
| 4.5 parity + separation | EXISTS | `ScenarioIsolationTest`, `ScenarioCapParityTest`, the refusal guards in `GrandConcertPanelTest` and `grand-concert-panel.spec.ts`. |
| 5.1 write-path inventory | PARTIAL | The migration is done; the inventory document is not written. 30 write routes measured. |
| 5.2 career editing | EXISTS | `runs.update`, `build-target.update`, `settings`, `team`, `deck.sync`. |
| 5.3 progression recording | EXISTS | `turns.store`/`update`/`destroy`, `races.store`, `events.store`, `inheritance.store`, `purchases.store`, `indep-abandon`. |
| 5.4 skills | EXISTS | `skills.sync`, `SkillsPlannerController`, `run_skills` + `skill_levels`, `career-skills-planner.spec.ts`, `04894584`. |
| 5.5 completion + archival | EXISTS | `SaveVeteranController`, `runs.veteran`, `SaveVeteranTest`, `career-save-veteran.spec.ts`. |
| 5.6 legacy retirement | EXISTS | Done 2026-10-09: `GET /training-runs/{run}` 302s to `runs.cockpit`; 38 feature files and 9 browser specs swept in the same commit. |
| 6.1 Veterans contract | MISSING | `docs/architecture/veterans-record-contract.md` does not exist and no master owns the subject. |
| 6.2 Veterans library | EXISTS | `Veterans/Index.vue` (553), `VeteranRow`, `ListVeterans`, `VeteranLibraryScreenTest`, `VeteranLibraryTest`. |
| 6.3 search/filtering | PARTIAL | Three shared facets + `order` ship (`VeteranSearchRequest`). A completion-date filter is **not supportable**: no column holds the date a career finished, and the request says so. Schema-gated, ADR required. |
| 6.4 historical detail | EXISTS | `Veterans/Show.vue` (339), `ShowVeteran`, `Veterans/Compare.vue` (896), `VeteranCompareTest`. |
| 6.5 record integrity | PARTIAL | No integrity report; `Veterans/Index.vue:528` is red in `UiHygieneTest` for a shipped docs path (§6). |
| 7.1 Legacy Lab boundary | PARTIAL | The boundary ships as the `LegacyController` docblock rule (FR-G-4: "records and compares what the Trainer entered, and it computes nothing"). No standalone contract document. |
| 7.2 selection | EXISTS | `Legacy/Index.vue`, `career.legacy.store`, `LegacyLabPageTest`, `LegacySelectionSchemaTest`. |
| 7.3 comparison | EXISTS | `Legacy/Compare.vue` (690), `AncestryGraph`, `StartingCapProjection`, `LegacyDisambiguationTest`. |
| 7.4 combination evaluation | EXISTS | `Legacy/Builder.vue` (960), `VeteranRow`, `CardFor` precedence fixed by `fc6f775d` (RTX-01). |
| 7.5 Veterans integration | EXISTS | Provenance links rendered by the builder; `CareerLegacyDeckStepsTest`, `career-legacy-parent-card-sync.spec.ts`. |
| 8.1 visual system | EXISTS | `DESIGN.md`, token-only utilities, `ui-lint` at 209 + 24 allowlist, `CardSection`, `components/ui/`. |
| 8.2 navigation/IA | EXISTS | `AppLayout.vue`, ten destinations, one-`h1`-per-route gate, `career-section-nav.spec.ts`. |
| 8.3 responsive/desktop | PARTIAL | KI-160 is OPEN (Cockpit 88 px overflow at 640 px, Races 59 px at 320 px). NativePHP is absent from `master` — see §5 item 2. |
| 8.4 accessibility | EXISTS | `accessibility.spec.ts`, contrast gate, `UiHygieneTest` aria/`N/A` rules. |
| 8.5 error/loading/recovery | EXISTS | `ADR-0007`, `errors/404`+`419`+`500`, `AbsenceValue.vue`, the `N/A`-with-`title` idiom. |
| 9.1 backend suite | EXISTS (baseline) | 2,325 passed / 2 failed / 9 warnings / 36,595 assertions at `a9c2b142`. §6. |
| 9.2 frontend suite | NEEDS-DECISION | Playwright (120 specs) + `tsc` ship. There is **no Vue component-test harness**: no vitest, no `@vue/test-utils` in `package.json`. Adding one is a new dependency (`AGENTS.md` §5). |
| 9.3 four scenario E2E | PARTIAL | One whole-career walk (`full-career-journey.spec.ts`) + per-panel specs. No per-scenario archive→Veterans journey for all four. |
| 9.4 Legacy Lab E2E | PARTIAL | `career-legacy-*.spec.ts` cover the steps; no single select→compare→evaluate→prove-unchanged walk. |
| 9.5 data integrity | PARTIAL | `RefreshDatabase` everywhere, `ScenarioIsolationTest`, `LegacySelectionSchemaTest`, import tests. No rollback/migration-reversal suite. |
| 9.6 NativePHP regression | MISSING | Not on `master`. §5 item 2. |
| 9.7 final acceptance | NEEDS-DECISION | Two 3.0.0 tag blockers are recorded in `VERSION.md`; both are stale or runnable (§5 item 8). |
| 10.1 architecture docs | PARTIAL | `ARCHITECTURE.md` (15 sections) + digest, maintained. Lifecycle, read-model, Veterans and Legacy Lab contracts have no section. |
| 10.2 user docs | NEEDS-DECISION | `Help.vue` is a static Inertia page with no `docs/` source; no user guide exists. A `docs/user-guide/` tree is a new documentation tree and needs a ruling. |
| 10.3 developer docs | EXISTS | `README.md` (19 sections incl. Testing, Structure, Documentation Map), `CONTRIBUTING.md`, `AGENTS.md`, `MCP_SETUP.md`. |
| 10.4 final report | MISSING | By definition — it is the program's last artifact. |
| 10.5 final repository review | MISSING | By definition. |

---

## 4. Documentation destination ruling

The roadmap proposes five new trees: `docs/decisions/`, `docs/architecture/`, `docs/implementation/`,
`docs/testing/`, `docs/reports/`. **None exists, and creating them violates standing rulings.**
`AGENTS.md` §3 closes the root set; `docs/research-scratch/INDEX.md` §"File discipline" forbids a new
standalone markdown file without a written owner authorization and names the master that owns each
subject; `docs/audits/README.md` owns the two legitimate audit shapes.

| Roadmap deliverable | Destination (ruled) | Authority |
| --- | --- | --- |
| Rulings owed / open decisions (0.2) | `docs/research-scratch/PLANS-AND-BRIEFS.md`, one section per package | Precedent: `## group-ab-rulings-owed.md` and `## remaining-work-rulings-owed.md` were folded there as "two-option write-ups awaiting an owner call, the class this master holds" |
| A ruling once made | ADR in `docs/adr/` if it binds architecture or schema; otherwise a dated section in the owning master | `AGENTS.md` §11, `ARCHITECTURE.md` §13 |
| Architecture contracts (2.1, 2.3, 6.1, 7.1) | `ARCHITECTURE.md` section + matching paragraph in `ARCHITECTURE-ESSENTIALS.md` | Digest-travels-with-source rule (§11); `ARCHITECTURE.md` beats its digest |
| Scenario configuration contract (2.2) | `config/scenarios.php` header comment + `ARCHITECTURE.md` §3 | D-240 / G-33 already make the config the owner |
| Slice implementation reports (4.1–4.4, 10.1) | `docs/research-scratch/SLICE-RECORDS.md` (append), plus the slice's own plan in `docs/proposals/` | §11 execution-ledger rule; `ARCHITECTURE.md` §13 "a plan document is the authority for its own slices" |
| Test/regression/acceptance reports (9.1–9.7, 10.4, 10.5) | `docs/audits/<name>.md`, loose, no evidence directory unless screenshots are captured | `docs/audits/README.md` rules 1 and 4 |
| Audit with evidence (1.2, 1.3, 1.5) | `docs/audits/<AUDIT-ID>/screens/` (split shape) | `docs/audits/README.md` rule 2 |
| User guide (10.2) | **Not a tree.** `resources/js/pages/Help.vue` content + `README.md` | §3 closes doc trees; Help is the shipped user surface |
| This baseline record | Loose at `docs/audits/trainer-desk-v3x-roadmap-baseline-2026-11-12.md` | `docs/audits/README.md` rule 1; no captures, so no directory (rule 4) |

**One naming correction to carry:** `trainer-desk-2-*` filenames in 0.2, 9.7 and 10.4 become
`trainer-desk-3x-*`. The project is 3.x.

---

## 5. TODO corrections — roadmap assumptions the tree contradicts

Each is a measured conflict, not a preference. Numbered for citation.

1. **Version.** "Trainer Desk 2.0" → 3.x throughout. `VERSION.md`: `3.0.0` pre-release, `2.1.1` the last tag.
2. **NativePHP is not a completed foundation.** The roadmap lists "existing NativePHP integration
   verification" as a completed input and builds 8.3 and 9.6 on it. Measured: no `config/nativephp.php`,
   no NativePHP package in `composer.json`, no `nativephp/` directory on `master`. The work lives on
   branch `worktree/cherry-pick` (`7da98984`; commits `d9a5267`, `3e22cbe`), which is **not an ancestor of
   `HEAD`** (`git merge-base --is-ancestor worktree/cherry-pick HEAD` fails). Its nine-file diff includes a
   committed 8.4 MB `nativephp/electron/dist/win-unpacked/resources/app.asar` build artifact. 8.3 and 9.6
   are blocked on an owner decision, not on verification work.
3. **The legacy career record page no longer owns write paths.** The roadmap's Phase 5 premise is that it
   "remains an intentional bridge while it owns write paths". Measured: `SCR-RUN-003` was **retired
   2026-10-09 (F2)**; `GET /training-runs/{run}` 302s to `runs.cockpit`, and 38 feature files plus 9
   browser specs were swept in the same commit. 5.1 and 5.6 collapse from a migration into a written
   inventory plus a parity check.
4. **F-08 needs no selection between two fixes.** The roadmap asks to "inspect both `51d78d3e` and
   `3aee95e2` … select one authoritative implementation". They are sequential, not competing: `51d78d3e`
   adds the Race-action confirm guard, `3aee95e2` adds per-tab draft persistence. Both are reachable from
   `HEAD`, and `f-series-ownership-inventory-2026-11-12.md` records F-08 **CLOSED** after recording that
   the guard alone did not satisfy the contract.
5. **Grand Concert 4.4 is mostly not buildable, and building it would break its own rules.** Lessons,
   Songs, Live Bonuses and Promo Concerts are BLOCKED BY EVIDENCE, each with a `SCREEN_SPEC.md` §7 entry
   naming the measured blocker and the artifact that reopens it (§7-19, §7-21, §7-22, §7-23). The measured
   blockers are structural, not editorial: no Song title exists in any committed export (9 greps, 8 zero
   hits), no table can hold the Promo Concert series (`scenario_slots` has no `year` column and a live
   unique index that would collide two Late-December concerts), and the Live Bonus vocabulary has six
   renderings for three objects from two publishers. Building the roadmap's 4.4 list means printing invented
   costs and magnitudes, which roadmap §3.7 and `AGENTS.md` §5 both forbid. Reduce 4.4 to: verify the shipped
   Performance foundation and finale surfaces, and record the block.
6. **"Vue component tests" presume a harness that does not exist.** `package.json` carries Playwright,
   `tsc`, `vue-tsgo` and Vite; there is no vitest and no `@vue/test-utils`. 9.2 as written needs a new
   dependency, which requires owner approval.
7. **The Grand Concert finale name is not a defect.** Measured: two surfaces print the event name from
   `finale_label`; one prints the scenario name from `label`. The gap file already calls it a design
   question. The live defect next to it is `ScenarioAdvisorFinaleTest:72`, which pins
   `'Our Grand Concert'` against a composer that now produces `'Our Grand Concert finals'`.
8. **Both 3.0.0 tag blockers as written in `VERSION.md` are stale or now runnable.**
   Blocker (a), "`full-career-journey.spec.ts` is red … its locator matches two `details`": the spec
   contains **no** `details` locator at all (`grep -c details` → 0); it navigates to `/team` directly at
   :135 and fills `input[name="team_name"]` at :138, rewritten in `202e8753` (2026-11-01). The clause
   describes a file that no longer exists.
   Blocker (b), "the full browser suite has not run because this host failed the quiet-box precondition":
   the KI-74 pre-flight now measures **0.002 s** for a trivial SQLite read, so the precondition this clause
   failed is met.
   Neither is a new regression; both are documentation drifting behind the tree (`AGENTS.md` §2: the code
   wins and the rule is stale).
9. **The notice-archive count blocks nothing in the app.** 181 notices to 2026-11-10 against a prior 154,
   with 72 postdating 2026-10-05 and 109 on the old basis. No code reads the archive. Demote from a Phase 0
   blocker to a recorded-open instrument question in `UMAMUSUME_REFERENCE.md` §4.6.
10. **Veterans' completion-date filter needs a schema decision, not a query.** `VeteranSearchRequest`'s own
    comment: "no column holds the date a career finished, so 'oldest first' is a statement about this list
    and the page says so."
11. **The "one oversized Cockpit component" framing is a refactor proposal.** `Cockpit.vue` is 1,157 lines,
    but 8 panels and ~20 `career/` components already carry the sections the roadmap asks to extract, and the
    composition was re-baselined at v3.0.0 (KI-139 is the record of the last time stale locators were
    patched one at a time).
12. **Trackblazer's resource tallies have no writer.** The panel and shop purchase path ship; `grade_points`
    and `shop_coins` have no run column, so a tally reads `N/A` by the deliberate absence rule (D-220,
    KI-154). Distinguish "screen exists" from "counter is recorded" in 4.3.
13. **The 5 unmeasured F-series findings are the cheapest open work in Phase 1.** F-05, F-06, F-07, F-09,
    F-10 are all `OPEN — UNMEASURED` and each needs one live check, not an investigation.

---

## 6. Baseline preserved, not rediscovered

| Item | Recorded figure | Source | Status of this pass |
| --- | --- | --- | --- |
| Full Laravel suite | `2 failed, 9 warnings, 2325 passed (36595 assertions)`, exit 1, 301.17 s | `post-remediation-verification-handoff.md` §B and §D; `progress-report-2026-11-12.md` § | Carried forward unchanged. **Not re-run**: this pass makes no implementation change and a Level 3 run is a checkpoint, not a baseline probe. |
| Failure A | `ScenarioAdvisorFinaleTest:72`, expected `'Our Grand Concert'`, actual `'Our Grand Concert finals'` | same | Pre-existing, classified: stale expectation introduced-by/left-behind by `49a90c3a1`. Owner UX-25 / KI-82. Not a regression. |
| Failure B | `UiHygieneTest:243` — `BurstBandsCard.vue:87` ships a section reference; `Veterans/Index.vue:528` ships a docs path | same | Pre-existing, classified: **real product defect**, owner-held. `f2f0a8e5` claims "red by design" and no exception mechanism records it. Not a regression. |
| Tag blocker (a) | journey spec red on a `details` locator | `VERSION.md` | Measured stale — see §5 item 8. |
| Tag blocker (b) | full browser suite unrun, host contended | `VERSION.md` | Precondition now met (0.002 s SQLite probe). Not run in this pass. |
| UI-lint debt | 209 arbitrary values, 24 allowlists | `tools/ui-lint-baseline.json` | Matches; no additions made. |
| Lore gate | `595` docs hits / `75` exempt, measured this pass (`php tools/lore.php`) | last recorded observation was 583 @ 2026-11-10 | Run. The delta is the peer-owned untracked audit corpus (`trainer-desk-v3-ux-retest/`, walk-8, the scenario-research set), none of which this pass wrote. This file measures **0** hits under the pattern list. |

The tree is quiet enough for a Level 3 run. No suite figure in this document is a new measurement, and none
is presented as one.

---

## 7. Decisions this pass could not make

These block the affected slices. Each is a one-line owner call.

| # | Decision | Blocks | Recommended option |
| --- | --- | --- | --- |
| A | Merge `worktree/cherry-pick` (NativePHP) into `master` — and if so, does the committed `.asar` stay out? — or cut the desktop work from scope? | 8.3, 9.6 | Merge the source, exclude the build artifact |
| B | Keep the Grand Concert evidence blocks, or authorize a Global client-capture pass to reopen Lessons/Songs/Live Bonuses/Promo Concerts? | 4.4 | Keep the blocks; record them as the honest ceiling |
| C | Add a Vue component-test harness, or restate 9.2 against Playwright + feature coverage? | 9.2 | Restate; the browser suite already walks the flows |
| D | One consolidated 13-field implementation backlog, or treat the four existing registers as the backlog? | 1.6 | Treat the registers as the backlog; write the field spec only for items that pass 0.2 |
| E | Trackblazer vocabulary: print `Result Points` and keep `grade_*` keys (A), or leave the app's wording (B)? | 2.4, 4.3 | A — `AGENTS.md` §13 requires the client's word |
| F | May Trackblazer derive the variant point track from `aptitude_turf`/`aptitude_dirt`? | 4.3 | Yes — it selects a published requirement rather than predicting an outcome, and both named examples resolve unambiguously |
| G | URA duel copy: fix `duel.win.gains` in config or the page, and print `+4` (sourced) or the qualitative tier? | 2.4 | Config edit; print `+4` |
| H | Is `docs/user-guide/` authorized, or does user-facing help live in `Help.vue` + `README.md`? | 10.2 | `Help.vue` + `README.md`; no new tree |
| I | Confirm the corrected reading of both `VERSION.md` tag blockers before that file is edited | 9.7 | Confirm, then correct `VERSION.md` as a dated erratum |

---

## 8. What this pass deliberately did not do

- No implementation change, no migration, no test edit, no dependency.
- No new documentation tree; §4 rules destinations for existing ones only.
- No re-research of a settled ruling; the four §3 gaps are quoted, not re-argued.
- No reimplementation of a closed F-series fix; the ten remediation commits were checked for ancestry only.
- No staging or commit of any path, including peer-owned ones.
