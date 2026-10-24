# Group A + Group B pass report — 2026-10-24

**Pass.** Audit, plan, and land the Group A + Group B items the four UX walks documented as missing and
no reconciliation round had claimed.
**HEAD at start.** `3cdc466c` (R4 Phase 1 tip). **HEAD at close.** recorded in `Slices landed` below.
**Version cut.** `2.1.1` (PATCH).

## Method

Thirteen items were audited against HEAD, planned, ruled by the owner, then implemented slice by slice.
Each landed slice is its own commit with a feature test and a browser case; every commit body names the
Group A/B item and the ruling. Two Phase 3 items were found not to reproduce (B-0.1, B-0.3) and are
recorded, not implemented. No schema, migration, or new route landed: every Group A slice is read-only or
config, and the two Group B slices are UI/write-path on existing routes.

Evidence rule applied throughout: a claim is the command output; a gate not run is reported as not run.

## Audit summary

Nineteen items audited (`docs/audits/group-ab-audit-2026-10-24.md`). Classification at audit time, then
after the owner's ruling:

| Item | Group | Audit state | Post-ruling |
|---|---|---|---|
| A-0.1 Cap composition | A | openable | landed |
| A-0.2 Run-stored aptitudes | A | needs-schema | ruled-out (catalog-only) |
| A-0.3 Owned-skill inventory | A | landed (`fbd37bdd`) | dropped |
| A-0.4 Reached effect values | A | needs-schema | blocked: A-0.5 |
| A-0.5 Card client levels | A | needs-schema | blocked: ADR |
| A-0.6 Roster editor | A | needs-schema | openable → landed |
| A-0.7 +30 ranking bonus | A | landed (`6a74f857`) | dropped |
| A-0.8 Purchasable pool | A | openable | landed |
| A-0.9 Burst band position | A | landed (`5eba0d01`) | dropped |
| A-0.10 Top Star threshold | A | openable | landed |
| A-0.11 Mood Effect percentages | A | needs-shape-decision | openable → landed |
| B-0.1 Scenario mandatory races | B | needs-shape-decision | does not reproduce (`KI-122`) |
| B-0.2 Cleared-goal recording | B | needs-shape-decision | landed |
| B-0.3 Pending-turn + date | B | needs-shape-decision | does not reproduce (`KI-123`) |
| B-0.4 Scheduled-race state | B | needs-shape-decision | landed |
| B-0.5 Team stat grades | B | needs-schema | blocked: ADR |
| B-0.6 Current Spirit gauge | B | needs-schema | blocked: ADR |
| B-0.7 Skill levels | B | needs-schema | blocked: ADR |
| B-0.8 Borrowed-card owner | B | needs-shape-decision | ruled-out (loopback) |

## Slices landed

| SHA | Item | What it does | Test that pins it |
|---|---|---|---|
| `18d6f6f1` | docs | audit + plan + rulings-owed | — |
| `f748a378` | A-0.10 | fan line names 240,000 beside the gap | `career-cockpit.spec.ts` "fan ladder threshold" |
| `92a3d773` | A-0.1 | `capComposition` section: caps + blue sparks, §1.2 non-reconciliation named | `CockpitCapCompositionTest`; browser "cap composition panel" |
| `5f359aae` | A-0.8 | `purchasable` pool with prices, sum, shortfall (capped 100 cheapest rows) | `CareerSkillsPlannerTest` new case; browser "purchasable pool" |
| `d6c30475` | A-0.6 | derived team roster from the six deck cards | `CockpitRosterTest`; browser "team roster" |
| `d8aaeb27` | A-0.11 | mood effect headline (+20%/+4%) from `config('uma.mood_effect')` | `CockpitMoodEffectTest`; browser "mood effect headline" |
| `7466cf4d` | B-0.4 | "Scheduled, not yet run." on an un-entered calendar race card | `career-race-decision.spec.ts` "scheduled, not yet run" |
| `f20ea1db` | B-0.2 | race-drawer "Record completed race" (status Completed + placement) | `CockpitGoalFromCompletedRaceTest`; browser "record-result path" |
| `e5bf5677` | docs | 9 `KNOWN-ISSUES` entries + plan index | — |

## Slices deferred

| Item | Why | Where it lives |
|---|---|---|
| A-0.2 | Catalog-only; D8 verified catalog aptitudes authoritative | `KI-115` (closed, does not reproduce) |
| A-0.4 | Needs a per-run card level first | `KI-116` (blocked: A-0.5) |
| A-0.5 | Per-run `deck_slots.card_level` is a migration; §11 needs an ADR | `KI-117` (blocked: ADR) |
| A-0.3, A-0.7, A-0.9 | Already landed before this pass | dropped; landing SHAs in the audit |
| B-0.1 | Does not reproduce — the shared finals rounds are correct by source | `KI-122` (closed) |
| B-0.3 | Does not reproduce — the snapshot form already records year/month/phase | `KI-123` (closed) |
| B-0.5 | Team stat grades is a migration; ADR first | `KI-118` (blocked: ADR) |
| B-0.6 | `current_spirit` is a migration; ADR first | `KI-119` (blocked: ADR) |
| B-0.7 | Skill levels is a migration; ADR first | `KI-120` (blocked: ADR) |
| B-0.8 | Ruled out — a loopback, single-Trainer tool has no second account identity | `KI-121` (closed) |

## KNOWN-ISSUES entries filed

`KI-115` (A-0.2, closed), `KI-116` (A-0.4), `KI-117` (A-0.5), `KI-118` (B-0.5), `KI-119` (B-0.6),
`KI-120` (B-0.7), `KI-121` (B-0.8, closed), `KI-122` (B-0.1, closed), `KI-123` (B-0.3, closed).

## Rulings owed (still open)

None for this pass — every ruled item either landed or is recorded. The four `blocked: ADR` items
(A-0.5, B-0.5, B-0.6, B-0.7) unblock when an ADR exists; A-0.4 unblocks when A-0.5 lands. The full
two-option write-ups remain at `docs/proposals/group-ab-rulings-owed.md`.

## Gates run

- **Phase 0.** `vendor/bin/pest tests/Feature/ScenarioMetadataTest.php` -> 3 passed (44 assertions).
  `npm run typecheck` -> exit 0.
- **Phase 2.** `php artisan test --compact` on the six touched/nearby feature files -> 77 passed (1002
  assertions). `npm run typecheck` -> exit 0. `npm run build` -> built.
  Browser cases (session-private `PLAYWRIGHT_PORT=8144`, `.scratch-uma/group-ab-2026-10-24/browser-8144.sqlite`):
  first run **3 passed, 2 failed** — the fan-ladder case failed on a `·` encoding in the assertion (fixed
  to match the two numbers), and the cap-composition case timed out waiting for the app root under host
  load (a timeout, not an assertion). Both re-ran focused and **passed** (4.6 s, 3.8 s). No product
  regression; the timeout is classified infrastructure/host-load.
- **Phase 3.** `php artisan test --compact` on `CockpitGoalFromCompletedRaceTest`, `CareerRaceDecisionTest`,
  `CockpitObjectivesTest`, `CockpitTeamPanelTest`, `CockpitRosterTest` -> 39 passed (662 assertions).
  `npm run typecheck` -> exit 0. `npm run build` -> built. Browser cases: B-0.4 "scheduled, not yet run"
  passed (3.2 s); B-0.2 "record-result path" passed (3.2 s).
- **Phase 4.** `grep -cE "^### KI-" KNOWN-ISSUES.md` -> 55 before, **64 after** (+9).
- **Static.** `vendor/bin/pint --test --format agent` passed on every changed PHP file
  (`CockpitController`, `SkillsPlannerController`, `CockpitCapCompositionTest`, `CareerSkillsPlannerTest`,
  `CockpitRosterTest`, `CockpitMoodEffectTest`, `CockpitGoalFromCompletedRaceTest`, `config/uma.php`).
  `npm run typecheck` clean after every slice.

## What this pass does not do

- No schema, migration, or new route. A-0.5, B-0.5, B-0.6, B-0.7 remain blocked on an ADR; A-0.4 remains
  blocked on A-0.5.
- B-0.1 is not implemented: the source (`docs/scenarios/09-global-race-calendar.md:105,632-633`) states the
  three URA Finals rounds are shared and only the Final forks, so the current mandatory list is correct and
  an owner filter would delete legitimate shared obligations. Recorded as `KI-122`.
- B-0.3 is not implemented: `Snapshot/Setup.vue:165-186` already renders Year/Month/Phase and
  `CreateSnapshotRun:58-79` writes `career_position`. Recorded as `KI-123`.
- The full browser suite was not run as a whole (cost-aware escalation, `AGENTS.md` §9); the new cases were
  run focused and are green.
- S2b (R4-03) and S11 (R4-13) remain their own slices, not opened here.

## Dated addition 2026-10-24 (release review, pre-push)

**Tag position.** `2.1.1` is tagged at `cb19af5e`, not at `41290a48` (the changelog commit). The tag was
first placed on `90be90fa` (this report), then re-pointed to `cb19af5e` when A-0.10's test-assertion
correction landed after the report was cut. `41290a48` (CHANGELOG) and `dfdd7088` (`VERSION.md`) are
ancestors of the tag; the tag ships the changelog, this report, and the test fix together. `2.1.1` has
never been pushed, so the re-point is local only.

**Corrections to the release notes found in review.** Two gaps in the 2.1.1 notes were closed in a follow-up
commit before push: the entry now records the R4 Phase 1 fixes (`d8aaeb27` and the eight commits before it)
that this tag is the first to ship, and carries a `Verification` section stating the bar honestly (Pest and
the new browser cases green; the browser suite not run as a whole; two pre-existing reds unchanged).

**External state anomalies observed, not acted on.** In this worktree the remote `refs/tags/2.1.0` resolves
through tag object `48ed53e9` (tagger 2026-10-24 15:12 +0800) to `d8aaeb27` - this pass's A-0.11 commit -
not to the historical 2.1.0 release commit; `origin/master` was also already at `cb19af5e` and
`origin/release/2.1.0` at `d8aaeb27` without a push from this session. No tag or ref was moved by this pass
for `2.1.0`; the re-point is reported for the owner to verify against `c590186f` before any push, because
moving a published release tag is irreversible.