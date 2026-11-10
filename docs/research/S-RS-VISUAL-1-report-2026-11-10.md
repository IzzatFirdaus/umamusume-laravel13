# Dispatch report — `S-RS-VISUAL-1`

**Commit base:** `0235f76f` — `docs(research): S-RS-REMODEL-C2 report`
**Directive:** owner ruling 2026-11-10, *"I want visual frontend scenario detail change per HFW, not just in codes."*
**Outcome:** the Unity Cup Scenario detail page renders in the HFW's order, at its spans, with plain `h2` headings and no capsule chrome. Verified by a live 1280 px render, not by inspection.

---

## 1. The one thing that must be recorded, because it looks like a regression

**This slice reopens a hole that a previous commit closed on purpose.**

`6493326a` widened the row-3 pairing from `s4 + s4` to `s4 + s8`, closing a measured **341 px** gap of unused tracks (`GRID-REVIEW.md` §P4). The HFW draws that row as `s4 + s4`. Following the owner's directive — match the HFW, holes included — the grid now draws `s4`, and **the 341 px hole is back**.

That reversal is intentional, not a regression, and it is recorded here so a later reader does not read the arc as having undone its own work:

> Row-3's span reverts to the HFW's `4+4`, reopening the 341 px hole closed at `6493326a`. The owner's HFW directive supersedes that earlier defect fix; the reversal is the ruled consequence, not a defect reintroduced by accident. If the after-render reads worse than the closed version, that is a review decision for the owner, and it is cheap to redirect.

Three other rows sit under 12 tracks as well, and all four are the drawing's own:

| Row (HFW order) | Card | Tracks used |
|---|---|---|
| 2 | Unity Cup progress | 4 / 12 |
| 4 | Special Training | 4 / 12 |
| 7 | Team race coverage | 8 / 12 |
| 10 | Team stat grades | 8 / 12 |

---

## 2. Measured result

Captured from a live render at 1280 px on a fresh Unity Cup career (`Rice Shower`), via the repository's own Playwright harness on a session-private port and scratch database.

| | before (`0235f76f`) | after | HFW target |
|---|---|---|---|
| Grid container | 992 px | **992 px** | 992 px |
| Tracks | 12 | **12** | 12 |
| Gap | 12 px | **14 px** | 14 px |
| Cards in grid | 8 | **10** | 12 (full data) |
| Capsules on page | 12 | **0** | 0 |
| `h2` in grid | 0 | **10** | 12 (full data) |
| Reference strip | outside grid | **outside grid** | outside grid |

**Order, as rendered (a fresh run records no team identity, no counters, no Spirit value):**

```
Team Race · Burst count bands · Special Training · Trainee goals · Mandatory races
Team race coverage · Team race shape · Spirit · Team Rank · Team stat grades
```

Against the HFW's twelve: `Team` and `Unity Cup progress` are absent because the run records nothing for them (`v-if` data gates, unchanged behaviour). The remaining ten appear in the drawing's relative order.

**Spans, as rendered:** `12 / 12 / 4 / 12 / 12 / 8 / 12 / 4 / 4 / 8` — matching the HFW row for row, four holes included.

Artifacts: `.scratch-uma/S-RS-VISUAL-1/before-1280.png`, `before-1280.dom.json`, `after-1280.png`, `after-1280.dom.json`.

---

## 3. Component splits

The HFW order interleaves cards that three components drew, so no arrangement of contiguous component blocks could produce it — the consequence `docs/proposals/2026-11-10-scenario-detail-remodel.md` §A.7 flagged as open. The split resolves it.

**Created — `resources/js/components/scenario/cards/` (one card each):**

| File | Draws | Moved from |
|---|---|---|
| `TeamCard.vue` | Team | `UnityCupPanel` inline |
| `SpiritCard.vue` | Spirit | `UnityCupPanel` inline |
| `ProgressCard.vue` | Unity Cup progress | `UnityCupPanel` inline |
| `StatGradesCard.vue` | Team stat grades | `UnityCupPanel` inline |
| `BurstBandsCard.vue` | Burst count bands + roster | `UnityCupPanel` inline |
| `SpecialTrainingCard.vue` | Special Training | `UnityCupPanel` inline |
| `GoalGroupCard.vue` | one goal group | `CareerGoalsPanel`'s `v-for` |

**Created — the composer:** `resources/js/components/scenario/ScenarioCardGrid.vue` owns the grid, the ordered card list and the spans. The page owns the order; each card draws itself.

**Changed:** `UnityCupPanel.vue` keeps its two registrations and delegates the grid; 365 lines removed, 8 kept as the `team_race`-only arm. `ScenarioPanel.vue` gains a `gridDrawsGoals` computed and stands the `career_goals` registration down where the grid already places those cards — keyed on flags, never a scenario name (G-33).

**Reused unchanged:** `TeamRankGauge`, `TeamRacePanel`, `UnityCupTeamBoard`, `UnityCupTournamentReadiness`, `SpiritBurstRoster`.

**Not deleted, contrary to the runbook:** `CareerGoalsPanel.vue` stays. It still draws URA's and Our Grand Concert's goals, and `tests/Feature/AbsenceChannelTest.php:147` pins its path in the F-11 migrated list. Deleting it would have failed that suite for no gain.

---

## 4. Spec changes, and why

| File | Change | Reason |
|---|---|---|
| `unity-cup-panel.spec.ts:345` | "heads every panel with the documented capsule" → "heads every panel with a plain h2, not a capsule"; asserts **zero** `.lattice-bleed`, every card headed, count `>= 10` | W.1 = C inverted the assertion's subject. The count was re-derived from the live render (10), not inherited (was `>= 9`) |
| `unity-cup-panel.spec.ts:374` | "capsules the four panels" → "places the four goal and reference cards"; asserts each title exists **exactly once** | Same inversion, plus a new guard: the grid and `career_goals` could double-render the goals, and this is what catches it |
| `unity-cup-panel.spec.ts:408` | "both blocks with the capsule" → "both blocks with a plain h2"; asserts `text-lg`, **not** `sr-only` | The heading is now the visible title rather than a hidden duplicate beside chrome |
| `unity-cup-panel.spec.ts:132` | `/Aim for at least 3 circles/` → `/aim for at least 3 circles/` | **Pre-existing stale assertion, not this slice's.** W.3 = C (`1f73875f`) moved the rule into the strip and lowercased it; the browser case kept the banner's old capitalisation and has been failing at HEAD. Re-pointed rather than deleted, so the rule stays pinned |
| `scenario-detail-order.spec.ts` (new) | 4 cases: order, spans, zero capsules, strip outside grid | The dispatch asked for a manual visual check. A human eye does not survive the next restyle, so the order and spans are asserted as data |
| `UnityCupPanelTest.php:294, 312` | both source-text assertions re-pointed to `ScenarioCardGrid.vue` / `BurstBandsCard.vue` | The copy they pin moved with the split; the assertions' meaning is unchanged |

**Non-vacuous, proven:** swapping two card positions in the grid turned exactly the two order cases red (`2 failed, 2 passed`); reverted and the file is byte-identical to its backup.

---

## 5. Gates

| Gate | Command | Result |
|---|---|---|
| Typecheck | `npm run typecheck` | **exit 0** |
| UI lint | `npm run lint:ui` | **500 findings, exit 0** — identical to the 500 pre-change, delta **0** (198 `arbitrary-value` findings, baseline 209) |
| PHPStan | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | **`[OK] No errors`** |
| Build | `npm run build` | **exit 0** |
| Suite | `vendor/bin/pest --compact --no-coverage` | **2 failed, 2305 passed (36289 assertions)** — identical failures to baseline |
| Lore (docs) | `php tools/lore.php` | **582 hits / 75 exempt** |
| Lore (code) | `php tools/lore.php code` | **319 hits** |
| Fence check | `python tools/md_fence_check.py DESIGN.md SCREEN_SPEC.md` | **0 invalid fences** |

**Runner correction:** `php artisan test --compact` does not run on this host. With stdin not a tty it prints `stdin is not a tty` and exits 1 without executing a single test — `</dev/null` does not help. `vendor/bin/pest --compact --no-coverage` is the working invocation and produced every suite number in this report.

**Lore rulings, one per hit this slice introduced:**

- `resources/js/components/scenario/cards/GoalGroupCard.vue:9` — `mount` in "own mount point". **Class 2**: a substring inside an ordinary technical word, naming a component host. Fourteen other files in the tree use `mount`/`mounts` in the same sense. No framing, no display copy.
- `tests/browser/scenario-detail-order.spec.ts:17` — `grass` in "grass-and-goal cards". **Class 2** and removed anyway: reworded to "the goal cards" because the phrase was mine and added nothing.

The docs count rose 478 → 582 since the last recorded figure; the increase is the 2026-11-09/10 research corpus (audit, survey, HFW README, GRID-REVIEW), which this slice cites rather than writes.

---

## 6. Suite result, and the classification of every failure

**Baseline at `0235f76f`:** `2 failed, 9 warnings, 2305 passed (36241 assertions)`.
Reds: `ScenarioAdvisorFinaleTest` (assertion at `:72`) and `UiHygieneTest` (assertion at `:243`, naming two sites: `TeamPanelBuilder.php:620` and `UnityCupPanel.vue:379`).

**After this slice:** `2 failed, 9 warnings, 2305 passed (36289 assertions)`.
**Same two failures, same count, no regression.** Assertions rose by 48, which is the new coverage (four browser cases plus the re-pointed assertions), not a shifted assertion count.

**One relocation, as predicted:** `UiHygieneTest`'s section-reference site moved from `UnityCupPanel.vue:379` to `cards/BurstBandsCard.vue:81` because the burst-band copy moved with the split. Same failure, same count, new path. Not fixed and not worsened — the copy still carries `UMAMUSUME_REFERENCE §2.2.2` because that citation is the provenance the repo requires; removing it to make the gate green would be a suppression, not a fix.

**Two failures were mine, and both are now fixed.** During this slice the run reached `4 failed`; the two extra were `UnityCupPanelTest` source-text assertions pointing at `UnityCupPanel.vue`, a file the split emptied. They were resolved by moving the copy to the files that now render it and re-pointing the assertions — never by weakening one.

**One real defect this gate caught, worth naming.** My first extraction **dropped the `+30` line entirely**, losing KI-155's league-placement correction and its `ProvenanceBadge`. `UnityCupPanelTest` caught it. Both are restored on the rank card, where the client prints the figure. Without that test the page would have silently lost a corrected attribution and a provenance mark. A second near-miss: while restoring the burst-band copy I truncated its `§2.2.2` citation, and `UiHygieneTest` went green — a pass that was the *absence* of provenance rather than a fix. The citation is restored and the test is red again at exactly its baseline site, which is the honest state.

**Pre-existing failure outside this slice, found while regression-testing the other scenarios:** `ura-panel.spec.ts:193` ("marks the debut missed once its turn has passed") fails at `0235f76f` with this slice stashed — verified by stashing, rebuilding, re-running, and restoring. Not caused by this work.

**Regression pass on the sibling specs:** 43 of 44 passed across `ura-panel`, `grand-concert-panel`, `trackblazer-panel` and `scenario-panel`; the single failure is the pre-existing `ura-panel` case above.

---

## 7. Dispatch-versus-tree corrections

| Dispatch claim | Measured fact | Resolution |
|---|---|---|
| Delete `CareerGoalsPanel.vue` | `AbsenceChannelTest.php:147` pins its path | Kept the file; it still serves URA and Our Grand Concert |
| 6 components carry `CapsuleHeader` on this page | 8 do | All 8 changed |
| Task 3.3: strip the banner's rule text | Already landed at `1f73875f` | Verify-only; measured one hit, in the strip |
| Task 4.4: update feature capsule assertions | `grep` across `tests/Feature` returns zero | No-op |
| `tests/browser/scenario-detail.spec.ts` | Does not exist | The capsule assertions live in `unity-cup-panel.spec.ts` |
| `php artisan test --compact` as the runner | Aborts without a tty | `vendor/bin/pest --compact --no-coverage` |
| Track scenario 7 | No run 7 on this tree (runs 3 and 8 only) | Every screenshot is a fresh run created by the spec |
| "Every row 12/12 used tracks" (Task 3.2 acceptance) | Contradicts Fork 1's "HFW order authoritative" | Owner ruled: HFW spans verbatim, holes included |
| `TeamRaceFacilityPanel` cleanup (Task 2.7) | No scenario config sets `team_race_facility`, so it never renders here | Deliberately skipped; `team-race-facility-panel.spec.ts` stays green untouched |

---

## 8. Known limitations, stated rather than implied

- **URA, Our Grand Concert and Trackblazer keep their payload order.** They lose capsule chrome (page-wide, as ruled) but are not reordered; no HFW rules on their order. Measured: `team_rank_ladder` is `true` for `unity_cup` alone, so the grid is structurally Unity-Cup-only rather than branched on a scenario name.
- **`Team` and `Unity Cup progress` are not visible on a fresh run.** They are `v-if` data gates that predate this slice. The order spec asserts the rendered subset in the HFW's relative order and names both absences.
- **A concurrent agent built a parallel implementation of this slice in the same worktree** and edited `UnityCupPanel.vue` while this one did. Its files (`UnityCupComposer.vue`, six `UnityCup*Card.vue`) were retired in favour of this implementation because the composer left both goal-card positions as empty placeholders, which would have removed Trainee goals and Mandatory races from the page entirely, and its rank card dropped the `+30` `ProvenanceBadge`. Its bytes are preserved at `.scratch-uma/S-RS-VISUAL-1/collision/` for review, not deleted without a record.
- **The 15 px vs 16 px heading conflict is unresolved by design.** The HFW draws `.card > h2` at 15 px; `app.css:1024` sets 16 px deliberately (`AUD-20261101-001 F-01`) and that rule out-specifies any utility class. The page uses 16 px and the divergence is recorded here rather than papered over with an arbitrary value.
- **Two files in this change are not this slice's work, and are declared rather than claimed.** A concurrent agent edited `TeamRaceFacilityPanel.vue` (capsule → plain `h2`) and `tests/browser/team-race-facility-panel.spec.ts` (assertion re-pointed to match) in this same worktree. `TeamRaceFacilityPanel` never renders on this page — no scenario config sets `team_race_facility` — so the change is harmless and consistent with the page-wide ruling, and its spec passes (2/2 verified). It is left in place rather than reverted because reverting would break that spec. **This slice's own §4 deliberately did not touch that component**; the diff belongs to the concurrent agent, and the hand-off says so instead of folding it into this report's account.
