# Trainer Desk 3.x — Phase 0.3 change-boundary plan

**Date:** 2026-11-12
**Inspected commit:** `f029a806`
**Supersedes:** the roadmap's own 0.3, which assumed no baseline existed. `docs/audits/trainer-desk-v3x-roadmap-baseline-2026-11-12.md` is the baseline; this file is what survives it.
**Companion:** `docs/research-scratch/PLANS-AND-BRIEFS.md` §`trainer-desk-3x-roadmap-rulings-2026-11-12.md` (rulings A–J).

Purpose: reduce the roadmap's 59 slices to the set that is actually outstanding, with a boundary for
each. No implementation, no test run, no file moved.

---

## 1. Four corrections to the 0.1 map, measured after it was written

These were filed `MISSING` or `PARTIAL` in 0.1 and are not. The 0.1 file keeps its sentences (it is a dated
record); this section is the correction.

### 1.1 The four "missing contract documents" already exist as `ARCHITECTURE.md` sections

| Roadmap deliverable | 0.1 verdict | Measured |
| --- | --- | --- |
| 6.1 Veterans record contract | MISSING | **EXISTS** — `ARCHITECTURE.md:193-202`. Owns: record-only under FR-G-4 ("nothing derives a Spark firing, an affinity payout or an offspring"), `training_run_id` unique (one run is one Veteran, re-save rewrites tags/notes), cascade, only Trainer tags + notes stored, no `is_manual` write, no `data_sources` row. Digest carries the same at `ARCHITECTURE-ESSENTIALS.md:125`. |
| 7.1 Legacy Lab boundary | PARTIAL | **EXISTS** — same paragraph, FR-G-4, plus the live rule in `LegacyController`'s class docblock ("records and compares what the Trainer entered, and it computes nothing"). |
| 2.1 Career lifecycle | PARTIAL | **EXISTS** — `ARCHITECTURE.md:169` owns `RunStatus: Active\|Completed\|Retired`, default `Active`. Archival is not a run state: it is a `veterans` row pointing at the run (1.1 above). The roadmap's "abandoned" and "archived" states are **not missing — they are the same facts under other names** (`Retired` and a `veterans` row). No new enum case is justified. |
| 2.3 Cockpit read-model contract | PARTIAL | **EXISTS** — `resources/js/types.ts` owns the wire contracts by name ("The shared Next Decision contract … Fixed here once because G2 to G6 all consume it", `:35`; "The scenario panel contract (plan §9 E1). Fixed once here because E2, E3, E4 and E6 all consume it", `:178`), and `CockpitController` carries **26** named section builders. A prose duplicate would be a second answer about one shape. |

**Consequence:** 10.1 is not "write six architecture documents". It is one edit — the stale sentence below.

### 1.2 `ARCHITECTURE.md:202` is stale, and it is the only real documentation gap found

> `-- ShowVeteran are the record/list/show path; no routes or screens yet (slice D16 builds those).`

D16 landed. Three `veterans` routes are registered (`veterans.index`, `veterans.show`, `veterans.compare`),
plus `runs.veteran` / `runs.veteran.store`, and `resources/js/pages/Veterans/` holds `Index.vue` (553),
`Show.vue` (339) and `Compare.vue` (896). The clause is false and has been since the D16 commit.

**Boundary:** one dated erratum in `ARCHITECTURE.md` + the matching digest correction. `AGENTS.md` §11
(digest travels with the source). No new document.

### 1.3 `docs/audits/README.md` evidence rules make one roadmap deliverable illegal as specified

Roadmap 1.2/1.5 ask for "before-state screenshots or annotated captures" in a new audit directory. Rule 4:
"**A report that captured nothing gets no directory.** Do not create an empty `screens/` as a placeholder."
Rule 2: evidence lives in `docs/audits/<AUDIT-ID>/screens/`, never beside the report.

**Boundary:** the refresh reuses the **existing** evidence trees — `trainer-desk-v3-ux/screenshots/` (59 PNG)
and `trainer-desk-v3-ux-retest/screenshots/` (72 PNG). Both are peer-owned (see §4). No new directory.

### 1.4 One genuine gap 0.1 did not catch: migration reversibility is unasserted

Roadmap 9.5 asks for "database migration and rollback behavior".

| Measured | Result |
| --- | --- |
| Migrations carrying `down()` | **77 of 77** |
| Tests asserting it | **0** |
| What `DatabaseSafetyTest` covers | the *guard* that refuses destructive commands on the canonical file — not reversibility |

So reversibility is claimed by `AGENTS.md` §11 ("Every migration has a working `down()`") and proven by
nothing. **Boundary:** one small test that reads `database/migrations/*.php` and asserts each declares `down()`
— a static check, no database, `tests/Unit`. It does not prove a `down()` *works*; that needs a real
`migrate:rollback` run on a scratch database, which is the 9.5 gate, not this slice.

---

## 2. Reconciled slice set

Verdicts: **DONE** (drop from the roadmap), **NARROWED** (ships; named remainder only), **OPEN** (genuinely
outstanding), **PARKED** (ruled out of scope).

### Phase 0

| Slice | Verdict | Boundary |
| --- | --- | --- |
| 0.1 baseline | DONE | `docs/audits/trainer-desk-v3x-roadmap-baseline-2026-11-12.md` |
| 0.2 rulings | DONE | `PLANS-AND-BRIEFS.md` §`trainer-desk-3x-roadmap-rulings-2026-11-12.md`; J awaits the owner |
| 0.3 change-boundary | DONE | this file |
| 0.4 commit plan | OPEN | Doc-only boundaries, §3 below. Blocked on the peer-owned paths in §4. |

### Phase 1 — audit refresh

| Slice | Verdict | Boundary |
| --- | --- | --- |
| 1.1 route/screen inventory | NARROWED | `SCREEN_SPEC.md` §2/§3 already owns the matrix and `DocAccuracyTest` pins 7 page references against the tree. **Remainder:** a route→controller→page map as a section in `AUDIT-AND-VERIFICATION.md`, not a new file. |
| 1.2 Cockpit audit | NARROWED | `04-ux-findings.md` (981 lines) exists at `bd0a8693`. **Remainder:** re-measure findings against `f029a806` provenance gates, no fresh walk. |
| 1.3 scenario UX audit | NARROWED | Six `scenario-research/` deliverables (1,533 lines) + `03-scenario-comparison.md`. **Remainder:** a Grand Concert live walk, which no document covers. |
| 1.4 defect verification | OPEN | The real Phase 1 work: 5 unmeasured F items, 1 spec defect (§2 item 1), 2 suite failures. |
| 1.5 quality + a11y audit | DONE | `05-cross-cutting-quality.md`, `accessibility.spec.ts`, contrast gate, `UiHygieneTest`. |
| 1.6 implementation backlog | DONE (dissolved) | Ruling D1: the four registers are the backlog. |

### Phase 2 — contracts

| Slice | Verdict | Boundary |
| --- | --- | --- |
| 2.1 lifecycle | DONE | `ARCHITECTURE.md:169` + `veterans` FK. No enum case added. |
| 2.2 scenario config | DONE | `config/scenarios.php`, D-240/G-33, 4 keys, 3 tests. Ruling F's evidence upgrade recorded there. |
| 2.3 read-model | DONE | `resources/js/types.ts` + 26 section builders. |
| 2.4 terminology | OPEN | Rulings E (10 display sites → `Result Points`) and G (`config:266` + `Preflight.vue:812` → `+4`). Both one commit. |
| 2.5 validation | DONE | **26 of the 30 product write routes take a FormRequest**; the other 4 are route-model-bound actions with no user payload (`career.trainee.carry`, `review.reopen`, `runs.indep.abandon`, `runs.turns.destroy`) and validate nothing because they receive nothing. Zero inline `$request->validate()` calls in any controller. |

### Phase 3 — Cockpit

| Slice | Verdict | Boundary |
| --- | --- | --- |
| 3.1 information architecture | OPEN — owner call | `Cockpit.vue` is 1,157 lines with 8 panels + ~20 `career/` components already extracted. A split is a refactor against a composition re-baselined at v3.0.0 (KI-139). **No slice until the owner names what the split buys.** |
| 3.2–3.6 | DONE | Header, stats/caps, training, obligations, advisor all ship with panel + spec coverage. |
| 3.7 nav + write-path preservation | DONE | `SCR-RUN-003` retired 2026-10-09 (F2); `GET /training-runs/{run}` 302s to cockpit; 38 feature files + 9 specs swept same commit. |

### Phase 4 — scenarios

| Slice | Verdict | Boundary |
| --- | --- | --- |
| 4.1 URA | DONE | Ladder, pre-final, Happy Meek, duel, `UraPanelTest`, `UraFinaleSurfacesTest`, panel spec. |
| 4.2 Unity Cup | DONE | Panel, team board, readiness, bursts, rank ladder, team race facility, editor, 3 feature tests. |
| 4.3 Trackblazer | NARROWED | Ships: panel, fatigue, race calendar, ledger, route planner, shop purchase path. **Remainder:** (a) ruling E display copy, (b) ruling F variant-track derivation + the 8-tie print, (c) ruling J finale name. Note `grade_points`/`shop_coins` have **no run column** (D-220/KI-154) — a tally recording path is a schema decision, not in this slice. |
| 4.4 Grand Concert | NARROWED | Ships: Performance foundation (5 slices), finale state/execution, advisor, cockpit integration, 6 feature suites. **Remainder:** verify shipped surfaces; record the block. Do not build the blocked halves (ruling B1). |
| 4.5 parity + separation | DONE | `ScenarioIsolationTest`, `ScenarioCapParityTest`, refusal guards. |

### Phase 5 — recording

| Slice | Verdict | Boundary |
| --- | --- | --- |
| 5.1 write-path inventory | NARROWED | 33 non-API write routes measured, of which **30 are product writes** — the other three are `_boost/browser-logs` (dev tool), `storage/{path}` (local upload), `preferences/backup` (maintenance). **Remainder:** the inventory table, appended to `AUDIT-AND-VERIFICATION.md`. |
| 5.2–5.5 | DONE | Editing, progression, skills, completion/archival all ship. |
| 5.6 legacy retirement | DONE | Already retired. Collapses to the 5.1 parity statement. |

### Phase 6 — Veterans

| Slice | Verdict | Boundary |
| --- | --- | --- |
| 6.1 contract | DONE | `ARCHITECTURE.md:193-202`. |
| 6.2 library | DONE | `Index.vue`, `VeteranRow`, `ListVeterans`, 2 feature tests. |
| 6.3 search/filtering | OPEN — schema-gated | Three facets + `order` ship. A completion-date filter needs a column that does not exist; `VeteranSearchRequest`'s own comment says so. **ADR before any query work.** |
| 6.4 historical detail | DONE | `Show.vue`, `Compare.vue`, `ShowVeteran`, `VeteranCompareTest`. |
| 6.5 record integrity | NARROWED | **Remainder:** `Veterans/Index.vue:528` ships `title="Source: docs/UMAMUSUME_REFERENCE.md"` — a real `UiHygieneTest` failure (`post-remediation-verification-handoff.md` §B, Failure B). One-line fix + the §-anchor in `BurstBandsCard.vue:87`. |

### Phase 7 — Legacy Lab

| Slice | Verdict | Boundary |
| --- | --- | --- |
| 7.1–7.5 | DONE | Boundary in `ARCHITECTURE.md`, selection/comparison/builder pages (690/960 lines), `AncestryGraph`, `StartingCapProjection`, `VeteranRow`, RTX-01 fixed by `fc6f775d`. |

### Phase 8 — application quality

| Slice | Verdict | Boundary |
| --- | --- | --- |
| 8.1 visual system | DONE | Tokens only, `ui-lint` 209 + 24 allowlist, no additions this program. |
| 8.2 navigation | DONE | `AppLayout.vue`, ten destinations, one-`h1` gate. |
| 8.3 responsive + desktop | NARROWED | **Remainder:** KI-160 OPEN — Cockpit 88 px overflow at 640 px, Races 59 px at 320 px. Desktop half, descoped by ruling A1. |
| 8.4 accessibility | DONE | `accessibility.spec.ts`, contrast gate, aria rules. |
| 8.5 error/loading states | DONE | `ADR-0007`, three error views, `AbsenceValue.vue`. |

### Phase 9 — verification

| Slice | Verdict | Boundary |
| --- | --- | --- |
| 9.1 backend suite | NARROWED | Baseline 2,325 / 2 failed. **Remainder:** classify the 2, then re-run at the 9.7 gate. |
| 9.2 frontend suite | NARROWED | Ruling C1: Playwright + `vue-tsgo` + `tsc` + `lint:ui`. No vitest. |
| 9.3 four scenario journeys | NARROWED | One whole-career walk exists. **Remainder:** three more, one per scenario, each ending at a filed Veteran. |
| 9.4 Legacy Lab E2E | OPEN | Steps covered by `career-legacy-*.spec.ts`; no single select→compare→evaluate→prove-unchanged walk. |
| 9.5 data integrity | NARROWED | §1.4 above: the `down()` static test, then a real scratch-DB rollback run. |
| 9.6 NativePHP | PARKED | Ruling A1. |
| 9.7 final acceptance | OPEN | Three tag blockers, §2 item 1 below. |

### Phase 10 — docs

| Slice | Verdict | Boundary |
| --- | --- | --- |
| 10.1 architecture docs | NARROWED | The stale `ARCHITECTURE.md:202` sentence + digest. Nothing else. |
| 10.2 user docs | NARROWED | Ruling H1: `Help.vue` + `README.md`. |
| 10.3 developer docs | DONE | `README.md` 19 sections, `CONTRIBUTING.md`, `AGENTS.md`, `MCP_SETUP.md`. |
| 10.4 final report | OPEN | By definition. |
| 10.5 final review | OPEN | By definition. |

---

## 3. Tally

Recounted from the tables above by slice, not by row: a range cell (`3.2–3.6`, `5.2–5.5`, `7.1–7.5`) covers
several slices and a row count would under-report it.

| Verdict | Slices |
| --- | --- |
| **DONE — drop** | **36** |
| **NARROWED — ships, named remainder** | **14** |
| **OPEN — genuinely outstanding** | **9** (0.4, 1.4, 2.4, 9.4, 9.7, 10.4, 10.5 outstanding; 3.1 and 6.3 owner-gated) |
| **PARKED** | **1** (9.6) |
| **Total** | **60** |

Sixty and not fifty-nine because 4.3's ruling-J item is counted as its own boundary alongside the slice.
In 0.1 the same set read 29 EXISTS / 18 PARTIAL / 5 MISSING / 7 NEEDS-DECISION. The movement is almost entirely
§1: four "missing contracts" were already written, and one deliverable (1.6) dissolved under ruling D1.

**The real program is §3.1.** Everything else is either shipped or a named one-line remainder, and §4 lists
what another session owns.

### 3.1 The outstanding work, ordered by cost

| # | Work | Cost | Slice |
| --- | --- | --- | --- |
| 1 | Tick `#confirmed` at `full-career-journey.spec.ts:103` + re-run the spec | ~2 lines + 5 min | 9.7(a) |
| 2 | Fix `Veterans/Index.vue:528` + `BurstBandsCard.vue:87` → `UiHygieneTest` green | 2 lines | 6.5 |
| 3 | Ruling G: `config/scenarios.php:266` + `Preflight.vue:812` → `+4` | 2 lines + 1 test | 2.4 |
| 4 | `ScenarioAdvisorFinaleTest:72` wording — owner UX-25 / KI-82 | 1 line + a copy call | 9.1 |
| 5 | `ARCHITECTURE.md:202` dated erratum + digest | 2 sentences | 10.1 |
| 6 | Ruling E: 10 display sites → `Result Points` | mechanical | 2.4 |
| 7 | Migration `down()` static test | one `tests/Unit` file | 9.5 |
| 8 | Ruling F: variant-track derivation + 8-tie print + tests | one service + one panel branch | 4.3 |
| 9 | KI-160 overflow (88 px @ 640, 59 px @ 320) | CSS | 8.3 |
| 10 | Three scenario journeys (URA, Trackblazer, Grand Concert) | 3 specs | 9.3 |
| 11 | Legacy Lab E2E walk | 1 spec | 9.4 |
| 12 | Grand Concert live walk (1.3 remainder) | 1 audit section | 1.3 |
| 13 | Five F-series live measurements (F-05/06/07/09/10) | 5 checks | 1.4 |
| 14 | Full suite + browser suite at the 9.7 gate | the gate | 9.7 |

---

## 4. Blocked on another session, not on this program

| Path | Why it cannot be staged here |
| --- | --- |
| `docs/audits/trainer-desk-v3-ux/` (8 reports + `scenario-research/` + 59 PNG) | Peer-owned, explicit prohibition — `post-remediation-verification-handoff.md` §C |
| `docs/audits/trainer-desk-v3-ux-retest/` (10 reports + 72 PNG) | Same |
| `docs/audits/RS-UC-2026-13/`, `RS-UC-SCENARIO-DETAIL-*`, `docs/research/RS-UC-*`, `docs/research/scenario-detail-design-language-survey-*` | Peer-owned |
| `docs/framework-ui-ux/_audit-prompt.md` | Modified, ownership disputed between the handoff and the peer note — unresolved |
| `docs/research/ui-ux-application-context-brief.md` | Modified, ownership not established |

**Consequence for 0.4:** the doc-only commit this program can make is
`docs/UMAMUSUME_REFERENCE.md` + `docs/research-scratch/PLANS-AND-BRIEFS.md` + the two files this program wrote
(`trainer-desk-v3x-roadmap-baseline-2026-11-12.md`, this file). Nothing else. The scenario-research set is
inside a peer-owned directory and its commit belongs to that owner.

---

## 5. What 0.3 does not decide

- Whether 3.1's Cockpit split is wanted. Owner call; no slice until the benefit is named.
- Whether 6.3's completion-date filter justifies a column + ADR. Owner call.
- Ruling J's disposition. Owner call; it changes printed copy on four surfaces.
- Whether the 2 remaining suite failures are fixed here or by their named owners (UX-25/KI-82, and the
  `UiHygieneTest` pair which §3.1 items 2 places in this program because the fix is mechanical).
