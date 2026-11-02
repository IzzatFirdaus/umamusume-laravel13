# UI/UX Audit Implementation — Pass 5 Report

Dated 2026-11-02.

**Pass 5 — Decisions A and B applied; Phase 4 tail closed (2 fixed, 2 retracted with evidence); Phase 5
reached 2 of its 7 rows. `npm run build` passes on this pass's own changes; it fails in the working tree
on a peer's in-flight syntax error in `TraineeProfile.vue`, which is not this pass's file and was not
edited. Steps 6.1 and 6.2 were not attempted.**

## 1. Owner decisions applied

**Decision A — seeder/parser coordination.** Applied, though not in the file the dispatch named. The
undefined-key failure was not in `SourceDocumentSeeder` (it only throws a collected message); the read
that failed is `PipelineRunner`'s `$record['name']`, which the shared trainee loop performs for every
source. The peer's race parsers emit `name_en`/`name_ja` and no `name`. `PipelineRunner::recordName()`
now reads `name`, `name_en` or `title` in that order and throws naming the source and record id when
none is present. Committed `335881fd`; `phpstan` and `pint` pass on the file.
**Effect: the seeding failure is gone and `runs-create.spec.ts` passes (1 passed).**

**Decision B — shell `@container` vs page-level sticky.** Applied for the genuine instance and
**partially retracted for the instance the audit mis-attributed**:
- `Review/Index.vue` carried a bare `sticky bottom-0` pager — a real page-level case. Declaration
  removed, reason stated where it was. `b0f5a1ef`.
- `Veterans/Compare.vue`'s three `position: sticky` cells sit inside `.comparison-region`, an
  `overflow-x-auto` element that is **its own scrollport**. Sticky there resolves against that
  scrollport, not the viewport, so `main`'s containment does not reach them. **Removing them would be a
  regression**, so they stay and the finding is retracted. `2187e057`.
- **KI-150** filed (`d7869fff`) for the constraint itself.

**Decision C — race enrichment scope.** Recorded as guidance only; no code change, and the plan's own
implementer applies it. The two unresolved enrichment blockers (`factor_effect_1/2` mapping; reward
items rendering as ids) are unchanged and remain theirs.

## 2. Per-finding outcomes

### 2.1 Phase 4 tail

| Finding | Audit | Status | Evidence |
|---|---|---|---|
| RacePlanner-F-01 unrendered groups | AUD-20261102-003 | **FIXED** `627a7248` | SOURCE — template now loops `orderedGroups`, so `optional` and `rival` render |
| RacePlanner-F-03 dead computed | AUD-20261102-003 | **FIXED** `627a7248` | SOURCE — `orderedGroups` is the loop's source; the dead computed became the renderer |
| RunsImport-F-01 component family | AUD-20261102-018 | **BLOCKED** | Family migration rewrites every field's prop contract; recommendation recorded (`Form/*` canonical) and it needs its own pass |
| LegacyBuilder-F-01 spark cap | AUD-20261102-025 | **RETRACTED** `2187e057` | Finding presupposes a ceiling no source states; `MAX_SPARK_STARS` is a star ceiling |
| VeteransCompare-F-01 sticky | AUD-20261102-027 | **RETRACTED** `2187e057` | Cells stick inside their own scrollport; page-level containment does not apply |
| ReviewIndex-F-04 pager pinning | AUD-20261102-021 | **FIXED** `b0f5a1ef` | SOURCE — no `sticky` remains in `Review/Index.vue` but the explanation |

### 2.2 Phase 5

| Finding | Audit | Status | Evidence |
|---|---|---|---|
| RacePlanner-F-02 cap uncommunicated | AUD-20261102-003 | **FIXED** `627a7248` | SOURCE — `#compare-limit-hint` names the cap of four; a fifth Compare is disabled with `aria-describedby` |
| RacePlanner-F-04 deadline prefix | AUD-20261102-003 | **FIXED** `627a7248` | SOURCE — controller no longer prefixes the alert; three copy pins updated |
| VeteransCompare-F-03 neutral pill | AUD-20261102-027 | **FIXED** `a76a95a1` | SOURCE — `same` and `single column` map to `pill-neutral` |
| VeteransCompare-F-02 verdict below `lg` | AUD-20261102-027 | **NOT ATTEMPTED** | No environmental blocker; the below-`lg` placement needs a layout decision the audit does not make |
| SnapshotEntry-F-01, SnapshotSetup-F-02, SnapshotReview-F-01 | AUD-20261102-007/-008/-009 | **NOT ATTEMPTED** | No environmental blocker; not reached |
| Cockpit-F-03 dead write methods | AUD-20261102-001 | **NOT ATTEMPTED** | No environmental blocker; the per-method sweep was not run |

### 2.3 Reverted during the pass

`4657be9e`'s single-column guard in `Legacy/Compare.vue` **broke two `legacy.spec.ts` cases**: the
builder→compare flow legitimately compares one run, and the guard removed the region the specs read.
The guard is reverted; F-01's real defect (caution-tinted neutral verdicts) is fixed by F-03's mapping.
This is a self-caught regression, found by running the spec rather than by inspection.

## 3. Gates

| Gate | Result |
|---|---|
| `npm run typecheck` | exit 0 |
| `npm run lint:ui` | exit 0; **207** arbitrary-value findings (baseline 209 — down 1) |
| `npm run build` | exit 0 **on this pass's changes** (manifest mtime `2026-11-02T22:34:56+08:00`); **fails in the working tree** on a peer syntax error, see §5 |
| `public/hot` | absent (`Test-Path` → False) |

### Specs run

| Spec | Result | Classification |
|---|---|---|
| `runs-create.spec.ts` | **1 passed** | Decision A unblocked it; closes pass 4's B-3 |
| `career-preflight.spec.ts` | passed (in the combined run: 15 of 17 across both files) | — |
| `legacy.spec.ts` | **2 failed** at `:232` and `:373` | **REGRESSION FROM THIS PASS, now reverted** — both failures read the compare region my single-column guard removed; the guard is gone and the tree rebuilt |
| `run-import.spec.ts` | not re-run | its known pre-existing `:123` failure is unchanged by this pass |
| `dashboard.spec.ts` | not re-run | no Dashboard change this pass; pass-3 result stands |

## 4. Peer coordination

- `app/Services/DataPipeline/PipelineRunner.php` — peer-modified (27 added lines, Japan-only promotion).
  The name helper was added on top and committed whole only after `git diff` confirmed the peer's hunks
  are additive and untouched; `pint` moved one of my `use` lines, not theirs.
- `KNOWN-ISSUES.md` — carries the peer's KI-141..KI-148 uncommitted. My KI-149 and KI-150 were staged
  onto `HEAD`, committed, and the peer's block restored unstaged. Current working tree: peer's eight
  entries unstaged, both of mine committed.
- **Not modified:** `config/uma.php`, the three untracked `GametoraRace*Parser.php` files,
  `TraineeProfile.vue`, `SCREEN_SPEC.md`.

## 5. Blocking item found, not fixed

`resources/js/pages/Career/TraineeProfile.vue` **does not compile in the working tree**: the peer's
uncommitted edit removed the `const aptitudeGroups = computed<AptitudeGroup[] | null>(() => {` opening
line and left its body orphaned at what is now line 212-216. `npm run build` fails at
`TraineeProfile.vue:215:8` with a babel parse error.
**Evidence it is not this pass's:** `git status` shows the file ` M`; it was last committed by
`c60af3a1` (pass 3). Stashing that one file and rebuilding gives `✓ built in 1m 7s`, which is how this
pass's own build gate was verified. The stash was restored afterwards; `stash@{1}` holds a duplicate
of the same peer state and was left in place rather than dropped.

## 6. Deviations

1. **Decision A landed in `PipelineRunner`, not `SourceDocumentSeeder`** — the dispatch's premise about
   the failing read was wrong; the seeder only assembles the failure message.
2. **Decision B applied asymmetrically** — the review pager's declaration was removed; the veterans
   cells were kept and the finding retracted, because the mechanism differs.
3. **`LegacyCompare-F-01`'s fix was reverted** after two spec failures proved the remedy wrong for the
   app's single-run flow.
4. **A duplicate `</template>` shipped in `627a7248`** and broke the SFC build; caught by the gate and
   fixed in `b4458c09`.

## 7. Owner actions required

1. **Peer's `TraineeProfile.vue` syntax error** blocks a clean tree-wide build; it is theirs to fix.
2. **RunsImport-F-01** needs the component-family direction confirmed (`Form/*` recommended).
3. **VeteransCompare-F-02** needs a below-`lg` placement decision.
4. Unchanged gates: Q2 re-probe, Q4 Phase 5 owner findings (~15), Q5 CatalogIndex-F-01.

## 8. Commits made this pass (8)

| SHA | Subject |
|---|---|
| `335881fd` | fix(pipeline): read a record's name leniently so one source's shape cannot crash another |
| `627a7248` | fix(raceplanner): render every group, name the cap, drop the dead computed and the Critical prefix (AUD-20261102-003 F-01..F-04) |
| `b0f5a1ef` | fix(review): remove the dead sticky declaration on the pager (AUD-20261102-021 F-04) |
| `d7869fff` | docs(known-issues): KI-150 page-level sticky unavailable under the shell container context |
| `a76a95a1` | fix(veterans): neutral verdicts use the neutral pill (AUD-20261102-027 F-03) |
| `2187e057` | docs(audit-corrections): retract LegacyBuilder-F-01 and VeteransCompare-F-01 with evidence |
| `b4458c09` | fix(raceplanner): remove the duplicate closing template tag the group loop left |

Prior-pass commits are not counted here.