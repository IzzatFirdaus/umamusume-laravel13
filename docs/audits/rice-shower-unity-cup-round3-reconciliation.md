# Round 3 Reconciliation: Rice Shower / Unity Cup UX Walk — Third Pass

Reconciles `docs/audits/rice-shower-unity-cup-ux-walk-3.md` (the third browser walk) against the two
earlier registers — `docs/audits/rice-shower-unity-cup-ux-walk-resolutions.md` (walk 1, `D1`–`D9` and the
remediation slices) and `docs/audits/rice-shower-unity-cup-round2-reconciliation.md` (walk 2, `R2-01`–`R2-21`)
— and against the code.

The audit names its own tree as `2c2fd0e` (`2026-10-11T23:30:34+0800`). This reconciliation reads the code at
that commit **and** at `HEAD`, `7ae1847` (`2026-10-12T00:25:15+0800`), which is the audit's commit plus one
unrelated commit (the screenshot registry). Where the two differ the row says so.

Nothing in this document is a fix. It classifies, names root causes, and proposes slices; no file under
`app/`, `resources/`, `tests/` or `database/` was changed to produce it, and the third audit is left exactly
as it was written.

## Method

Each observation from the third audit is classified as one of:

- **Confirmed fixed** — a later commit already addresses it; the code read proves it.
- **Confirmed defect** — the code read reproduces the failure.
- **Known limitation** — intentional and consistent with a recorded decision (`ADR`, `PRD` §6).
- **Unrepresented source data** — a real gap in what the tool can hold, not a rendering bug.
- **Source conflict / open** — the evidence does not settle it; recorded open.
- **Does not reproduce** — the observation contradicts the source at the audit's own commit.

**A claim is the code read.** Every "Current status" below is a read of a named file at a named commit,
not a restatement of the audit. Where the audit's observation and the source disagree, the source wins and
the row is marked **Does not reproduce** with the command that shows it — the audit's own rule
(`AGENTS.md` §2, "when code and a written rule disagree, the code wins"), applied to an audit rather than a
rule.

## Verification note (read before the table)

Five of the third audit's observations **do not reproduce** against the source at `2c2fd0e`, its own
commit. They are not minor wording differences; each asserts that a surface is missing something the code
has, or quotes a string the tree does not contain.

| # | Audit says | Source at `2c2fd0e` says | Command |
| - | ---------- | ------------------------ | ------- |
| V-1 | F-01: the Advisor prints `Recommendation unavailable because Energy has not been entered.` and "there is no band to read" | The advisor ranks from a recorded band (`TrainerAdvisor.php:84-86` calls `bandAdvice()`), and the quoted string is in no file under `app/` or `resources/`; it was `RecommendationCard.vue`'s, and that component was deleted at `4caa02d` (`2026-10-11 20:08`), four hours before the walk | `git show 2c2fd0e:resources/js/components/career/NextDecision.vue \| grep -c unavailable` → `0`; `grep -rn "Recommendation unavailable" app/ resources/` → no hit |
| V-2 | E-04: `UG` is not in the `Own rank (letters)` suggestion set (listed as `G…S, B+, A+, S+`) | `RANK_LETTERS` includes `'UG'`, and the picker's datalist is fed from it (`LegacySelectController.php:119`) | `git show 2c2fd0e:app/Models/Legacy/LegacySelectionPayload.php \| grep -n "RANK_LETTERS ="` → the list ends `… 'S+', 'UG']` |
| V-3 | E-08 / U-03: the identity form "offers no field for either" rarity or Potential Level | The form renders a `Rarity` select and a `Potential Level` input (`Cockpit.vue:524-554`), added by `8e51b9f` (`2026-10-11 01:24`) | `git show 2c2fd0e:resources/js/pages/Career/Cockpit.vue \| grep -c potential_level` → `8` |
| V-4 | U-09: the scenario panel "carries Team Rank and Spirit Bursts readouts only" | The cockpit renders `UnityCupEditor.vue` (`Cockpit.vue:626`), the team-fact write path landed `d8fb234` (`2026-10-11`) | `grep -n "UnityCupEditor" resources/js/pages/Career/Cockpit.vue` → import `:33`, render `:626` |
| V-5 | U-02 / E-07: the run's position has "no field anywhere" | True of the wizard, but the snapshot path carries it: `Career/Snapshot/Entry.vue` and `Setup.vue` exist and the Dashboard links `/career/snapshot` | `ls resources/js/pages/Career/Snapshot/` → `Entry.vue`, `Setup.vue` |

**What the five share, and the two readings that explain them.** V-1, V-2 and V-3 each match the tree as it
stood *before* a `2026-10-11` slice: `RecommendationCard.vue` was removed at `20:08`, `UG` was added to
`RANK_LETTERS` at `06:04` (`8a7b2e1`), and the rarity/potential fields were added at `01:24` (`8e51b9f`).
So either (a) the walk's browser served a stale `public/build` from the early hours of `2026-10-11`, or
(b) those observations were carried forward from the earlier passes rather than re-read. The audit did not
run `npm run build` (it records the server as pre-existing) and its screenshot directory is empty, so the
instrument cannot be checked from the audit alone. **Neither reading is proven here.** The consequence for
this register is the same either way: those five rows are marked **Does not reproduce** and need a fresh,
build-current read before anyone opens work on them.

The rest of the audit does reproduce: the `Ruleset version` / `Rule family` card headings match
`ScenarioSelect.vue:193,225`; the build-target `max` is the server cap (`BuildTarget.vue:147`); the
`deck_slot` query lag, the grandparent-spark count and the `Total across both parents and grandparents`
label all read true against the source. So the audit is not wholesale wrong; the divergence is confined to
the items above, and this note exists so a later reader does not spend a slice "fixing" a field that is
already there.

## Reconciliation table

| Finding | Evidence (walk 3) | Prior status | Current status (verified) | Root cause / note | Proposed slice | Regression test |
| --- | --- | --- | --- | --- | --- | --- |
| **R3-01: a recorded Energy band has no read display** | S09 `Band → Low`; S10 strip `Energy N/A`, S14 career state `Energy N/A`; correction form round-trips `energy_state: band`, `energy_band: low` | Walk 2 recorded the same display gap; `R2-16` (`c3583f0`) fixed the *write* path | **Confirmed defect (display half only)** — the strip reads `$latest?->energy` (`CockpitController.php:301,385`) and the event screen reads it too (`EventDecisionController.php:125`), so a band-only run prints `N/A`; **the Advisor half does not reproduce** (V-1) | The numeric column `turn_entries.energy` and the band pair `energy_state`/`energy_band` are separate fields; every read display consumes only `energy`. The advisor is the one surface that already reads the band (`TrainerAdvisor.php:84`) | Give the strip and the event screen a band read path (`Energy: Low (band)`), or a shared "reading" helper both call | `CareerCockpitTest` / `EventDecisionTest` case: record `band=low`, assert the rendered state names the band |
| **R3-02: Predicted Sparks excludes grandparent Sparks** | S11 `BLUE 2 Sparks` against a Legacy config holding three blues, on a count labelled "Total across both parents and grandparents" | Not testable in passes 1–2 (no grandparent Sparks saved) | **Confirmed defect** | `InheritanceEventController::predictedSection()` (`:182-205`) collects only `$legacyData['sparks']`; grandparent Sparks live under the optional `ancestors_sparks` key (`LegacySelectionPayload.php:214-218`) and are never summed. The label at `InheritanceEvent.vue:272` and the intro at `:256` both claim grandparents | Include `ancestors_sparks` in the sum, or relabel the count to name the nodes it sums | Feature test: enter a grandparent Spark, assert the total counts it |
| **R3-03: `?deck_slot=N` lags the picker by one click** | S06: after `Replace the card in Slot 2` the URL read `?deck_slot=1`; after Slot 3 it read `?deck_slot=2` | New this pass | **Confirmed defect (minor)** | `DeckSelect.vue:199-203` `replace()` moves `filling` and `form.deck_slot` client-side and issues **no** navigation; the query is server-seeded (`DeckSelectController.php:262`) and only changes on the next write's redirect (`:114-117`). A deep link copied between Replace and the next equip points at the previous slot | Update the query in the same render as the retarget (`router.get(..., {only:[], preserveState:true})`), or drop it from the URL | Browser case: `Replace` then assert `location.search` names the new slot |
| **R3-04: `Save correction` gives no success feedback** | S10: `Save correction` and `Save identity` returned with no flash and no alert | New this pass | **Confirmed for the correction form; does not reproduce for the identity form** | `Cockpit.vue` prints `Saved.` for `statusForm.recentlySuccessful` (`:467`) and `identityForm.recentlySuccessful` (`:586`); the correction form has only the error alert (`:676-686`) and an `onSuccess` focus move (`:273-275`) — no success line | Add a `correctionForm.recentlySuccessful` line, the way the sibling forms carry one | Component/feature case: submit the correction, assert the confirmation renders |
| **R3-05: catalogue aptitudes disagree with the source document** | S03 picker reads Mile C / Front B / Late C; source line 58 reads Mile B / Front A / Late B | Walk 1 `D8`: verified, **not** a tool defect; `ADR-0004` erratum: the GameTora export is authoritative | **Known limitation / open** — unchanged | The seeded column reproduces the committed export letter for letter; the source's own dated erratum (lines 61-71) calls the disagreement open. `ADR-0004` records the ruling | None | — |
| **R3-06: the run's own costume form is unnamed and cannot be picked** | S03: the card reads `2 costume forms`; the run's `[Rosy Dreams]` form is the unnamed one; the portrait served is the debut form | New this pass | **Unrepresented source data** — a card-form selector does not exist | `character_cards` is seeded per card; the picker offers the card, and its costume forms are not a selectable dimension | Owner call: a form selector is a new feature, not a defect fix | — |
| **R3-07: the no-target state is stated several ways** | S04/S08/S14/S17: `no target set`, `N/1300 cap`, "compare against nothing"; the scenario base vanishes from the cockpit strip when no target exists | Walk 2's `R2-09` (stat denominators) → landed `44a7b13` | **Partially fixed; residual wording** | `CareerStatePanel.vue:82-96,106-110` names the absence in prose and labels the second number `cap` or `target` per `denominatorLabel`; the cockpit passes the target-based label, so a no-target run shows no denominator while the event screen shows the cap | Optional copy pass; `R2-09`'s structural fix already landed | `StatDenominatorConsistencyTest` exists (R2-09) |
| **R3-08: `UG` is off the rank suggestion set** | S05: `UG` is not in `Own rank (letters)` (`G…S, B+, A+, S+`); typing it works | New this pass | **Does not reproduce** (V-2) — `UG` is in `RANK_LETTERS` at `2c2fd0e` | — | None; re-verify if the observation recurs on a current build | — |
| **R3-09: grandparents take Sparks but no rank field** | S05: four of six ranks (B+, A, UG, UG) have no home; only the two parents carry `Own rank` | New this pass | **Confirmed defect (scope gap)** — `LegacySelect.vue` binds `rank`/`rank_letter` per parent (`:106-107,430-463`) and `ancestors_sparks` per ancestor (`:112,368-372`); no rank is bound on an ancestor node | The `AncestryNode`/grandparent surface was built for Sparks (round-2 `f39ff03`) but the rank fields were only ever parent-level | Add the two `Own rank` fields to the grandparent nodes, or state why a grandparent rank is out of scope | `GrandparentSparksTest` sibling case for an ancestor rank |
| **R3-10: recording the source's Spark table is ~35 one-row editors** | S05: three controls per Spark row, per-node disclosures, no bulk entry, no running count | New this pass | **Unrepresented workflow** — a real cost, not a bug | The editor is per-row by design (`LegacySelect.vue:368-372`) | Optional: a running count, or a bulk paste; owner call | — |
| **R3-11: the run's position is inexpressible from the wizard** | S04/S08/S09/S15: a Senior Early October snapshot logs as `Junior Year · Early January · Turn 1` | Walk 2's `R2-01`/`R2-04`; the snapshot door landed `65d7068` | **Known limitation, by design** — the wizard creates a new career; the snapshot import carries the position (V-5) | `TrainingRun::hasImportedPosition()` gates the imported reading (`CockpitController.php:285`); a run with no logged turn legitimately claims no calendar place | None; the audit's run was created through the wizard, so Turn 1 is what the tool knows | `SnapshotCockpitTest`, `dashboard.spec.ts` |
| **R3-12: rarity / Potential Level not editable** | S03/S08: named as unrecorded by a badge, "no field" | Walk 2's `R2-13` → landed `8e51b9f` | **Does not reproduce** (V-3) — the identity form renders both | — | None; re-verify on a current build | `TraineeRarityPotentialTest`, career-cockpit-forms spec |
| **R3-13: scenario cards are asymmetric; the Unity Cup card prints one value twice** | S02: Unity Cup fully described, three others mostly `N/A`; `Ruleset version · N/A` beside `Rule family · Team` | Walk 2's `R2-05` (`9777531`) and `R2-19` (`24f7e48`) | **Known limitation** — the two headings are two different facts, both correct; the asymmetry is catalog completeness | `ScenarioSelect.vue:193,225`; the grid row is the tool-wide version (null by ruling), the block row is the scenario's rule family | None | `ScenarioMetadataTest`, `RulesetVersionVocabularyTest` |
| **R3-14: `New Career` starts beside an Active career with no prompt** | S01: a clean draft began beside an in-progress career | Walk 2's `R2-15` → known limitation (multi-run support) | **Known limitation** | Multi-run support is intended; the Dashboard shows the most recent active career and still offers a new draft | Optional: Dashboard copy naming multi-run support | — |
| **R3-15: the hint-discount ladder is disclaimed** | S17: `Hint discounts are not modelled.` (pass 1 rendered the ladder) | Walk 2's `R2-11` → known limitation, `ADR-0001` withdrew the ladder | **Known limitation** | `SkillsPlanner.vue:227-232` states the absence; the docblock (`:14`) gives the reason (no column holds a hint level) | None | — |
| **R3-16: several controls render without accessible names** | S05/S09: the Spark `Target` selects and the energy band select are unnamed | New this pass | **Reported, not re-measured here** — plausible but unverified against the templates | The Spark `Target` selects in `LegacySelect.vue` and the band select in `TrainingDetail.vue:444-451` (which carries `aria-describedby` but may sit outside a `<label>`) | A focused a11y slice, with the axe scan scoped to `#app` | axe case on the Legacy and Training screens |
| **R3-17: the window title falls back to `Trainer Desk` on some arrivals** | S06/S09: title settled only after the first re-render | New this pass | **Cosmetic, unverified** — an Inertia title race | Not re-measured here | Optional | — |
| **R3-18: the run's own caps and their composition have no home** | S04/S09: caps 1325/1322/1368/1308 exceed the inputs' `max`, and §1.2's composition has no field | New this pass | **Unrepresented source data** | The build target's `max` is `ScenarioCaps::forRun()` (`BuildTarget.vue:147`, `CockpitController.php:369`); the *composition inputs* (Sparks, support Max-Stat) are not asked for anywhere | Owner call: a cap-composition feature | — |
| **R3-19: the run's position (year/month, turns left)** | S04/S08/S09/S15 | Walk 2's `R2-01`/`R2-04` | **Known limitation** — see R3-11 (snapshot path) | As R3-11 | None beyond R3-11 | — |
| **R3-20: rarity ★3 and Potential Level 2** | S03/S08 | Walk 2's `R2-13` | **Does not reproduce** — see R3-12 | — | None | — |
| **R3-21: the `[Rosy Dreams]` costume identity** | S03 | New this pass | **Unrepresented** — see R3-06 | — | — | — |
| **R3-22: the four grandparents' ranks** | S05 | New this pass | **Confirmed** — see R3-09 | — | — | — |
| **R3-23: three blues and every pink/green/white Spark not entered** | S05 (I-05) | New this pass | **Not a gap** — the fields exist and accepted the three entered; volume only (the audit says so) | `ancestors_sparks` and `sparks` accept the full table | None | — |
| **R3-24: §1.3's owned-skill list and §2.2's purchasable pool** | S14/S17: the planner reorders priorities a target names; it adds none | New this pass | **Unrepresented source data** | The skills planner reads the build target's priorities; there is no per-run owned-skill inventory | Owner call | — |
| **R3-25: card client levels / limit breaks** | S06/S18 | Round 2 Phase 14 → **Deferred**; `ADR-0014` declined `user_support_cards`; `PRD` §6.9 outstanding | **Deferred, owner-gated** | Unchanged from the round-2 close | None until the owner rules | — |
| **R3-26: §1.8 team identity has no input** | S08: "Team Rank and Spirit Bursts readouts only" | Walk 2's `R2-14` → landed `d8fb234` | **Does not reproduce** (V-4) — `UnityCupEditor.vue` is rendered | — | None; re-verify on a current build | `CockpitTeamPanelTest`, `CockpitUnityCupCountersTest` |
| **R3-27: §1.6's race-day card** | S12/S13: going, fan ladder, entry criteria, `top contender` verdict | Walk 2's `R2-12` (payout) landed `473bb19`/`057db67` | **Partially represented** — the fan gate and payout columns exist; the going and the per-place ladder do not | `RaceFacts`/`RaceCard` carry what the columns hold; the going is not a stored column | Owner call on a going column | `CareerRacePlannerTest` |
| **R3-28: the cleared goals and the active Arima Kinen goal** | S12/S13 | `KI-34` (goal races unrecorded) | **Known limitation** — `KI-34` | Goal races have no recorded source | Track `KI-34` | — |
| **R3-29: §2.1's hint-discount ladder as a figure** | S17 | Walk 2's `R2-11` | **Known limitation** — see R3-15 | — | None | — |

## Baseline test report

- **HEAD** `7ae1847` is the audit's commit `2c2fd0e` plus exactly one commit — the screenshot registry —
  which touches no surface the audit walked (verified: `git show --stat 7ae1847` lists only
  `docs/research-scratch/screenshot-registry/**`).
- **Migrations:** not run by this reconciliation.
- **Focused tests:** **not run.** This document is a read-only classification; every claim in the table is a
  file read at a named commit, not a test result. No gate was executed, and none is claimed.
- **Tree state:** dirty with the screenshot-registry work and several `docs/research-scratch/` masters
  (see *Concurrent changes* below). The third audit itself is untracked.

## Proposed slice order

### Phase 1 — defects that reproduce (small, self-contained)
1. **R3-02** — include grandparent Sparks in the Predicted Sparks total (or relabel). One method, one label,
   one test. Cheapest and highest-signal.
2. **R3-04** — a success line on `Save correction`, matching its two sibling forms. Copy only.
3. **R3-03** — make the `deck_slot` query follow the retarget, or drop it from the URL.

### Phase 2 — re-verify the audit before any work
4. **R3-01** (strip/event-screen display), **R3-08**, **R3-12**, **R3-26** — re-read each on a current
   `npm run build`. R3-08/R3-12/R3-26 are expected to close as "does not reproduce"; R3-01's display half
   is expected to hold.

### Phase 3 — the rest
5. **R3-09** — grandparent rank fields (a real scope gap; needs an owner call on whether it is wanted).
6. **R3-16** — accessible names on the Spark `Target` and band selects.
7. **R3-06/R3-18/R3-24/R3-27** — owner calls; each is a feature, not a fix.

## Concurrent changes constraint

The tree is not quiet. At the time of writing, modified and untracked work under `docs/` and
`docs/research-scratch/` (the screenshot registry, several masters, `SCREEN_SPEC.md`, `KNOWN-ISSUES.md`,
`SKILL.md`) belongs to other sessions. Any slice that lands must use an explicit pathspec and verify the
committed blob against `HEAD` afterwards — the round-2 register's own rule, written after `380350e`
captured a peer's uncommitted controller:

> after any pathspec commit, diff the new commit against the previous HEAD for every file that was dirty
> before you started; a clean `git status` afterwards proves nothing about what went in.

## Per-finding detail

### R3-01 — a recorded Energy band has no read display

**What reproduces.** `CockpitController::stateSection()` builds the meta rows from `$latest?->energy`
(`app/Http/Controllers/Career/CockpitController.php:385`), and `headerSection()` does the same
(`:301`). `EventDecisionController` reads `$latest?->energy` (`:125`). So a turn that recorded only
`energy_state = band` and `energy_band = low` — which `StoreTurnEntryRequest:129-133` accepts and
`TurnEntry` casts (`:125`) — leaves every one of those reads null, and the surfaces print `N/A`.

**What does not reproduce.** The audit's claim that the Advisor "says there is no band to read". The
advisor ranks from a recorded band:

```php
// app/Services/Advisor/TrainerAdvisor.php:84
if ($latest?->energy_state === EnergyState::Band && $latest->energy_band !== null) {
    return $this->bandAdvice($latest, $target, $deficits, $latest->energy_band, $run);
}
```

and the behaviour is pinned by a passing test, `NextDecisionTest.php:332` — *"ranks from a recorded Energy
band and refuses only when the reading itself is absent"* — which records `band = low` and asserts
`recommended.action === 'Rest'`. The string the audit quotes is not in the tree (V-1).

**So R3-01 is narrower than the audit states:** the strip and the event screen need a band read path; the
advisor already has one.

### R3-02 — Predicted Sparks excludes grandparent Sparks

**Root cause, read.** `predictedSection()` walks each legacy's own `sparks` only:

```php
// app/Http/Controllers/Career/InheritanceEventController.php:189-193
foreach ($legacy->legacies as $legacyData) {
    foreach ($legacyData['sparks'] as $spark) {
        $allSparks[] = $spark;
    }
}
```

Grandparent Sparks are a **separate** key on the same record — `ancestors_sparks`, aligned by index with
`ancestors` (`LegacySelectionPayload.php:214-218`, validated at `:233-264`). The S07 contract displays them,
so they are stored; the count never reads them. The label the audit flagged is real and wrong for this
run: `InheritanceEvent.vue:272` prints `Total across both parents and grandparents`, and the intro at
`:256` says "Parent and grandparent Sparks".

**Fix shape.** Sum `sparks` plus every list in `ancestors_sparks` per legacy. The alternative the audit
offers (relabel the count to name the nodes it sums) is smaller but leaves the number understating the
configuration the contract shows.

### R3-03 — `?deck_slot=N` lags the picker

**Root cause, read.** `replace()` retargets the picker in client state and stops:

```ts
// resources/js/pages/Career/DeckSelect.vue:199-203
function replace(position: number): void {
    filling.value = position;
    form.deck_slot = position;
    void nextTick(() => document.getElementById('deck-pick')?.focus());
}
```

No navigation. The query is written only by `filter()`'s GET (`:149-164`) and by the write's redirect
(`DeckSelectController.php:114-117`). So between `Replace` and the next equip the address bar still names
the slot the last server round-trip wrote — exactly the one-click lag the audit measured. A copied deep link
in that window points at the previous slot.

### R3-04 — correction feedback

**Read.** The cockpit has three sibling header forms plus the correction form. Two carry a success line:

```vue
<!-- resources/js/pages/Career/Cockpit.vue -->
:467  <p v-if="statusForm.recentlySuccessful" …>Saved.</p>
:586  <p v-if="identityForm.recentlySuccessful" …>Saved.</p>
```

The correction form does not — only the error alert at `:676-686` and an `onSuccess` focus move at
`:273-275`. The audit's "identity form shows nothing" half does not reproduce; the correction half does.

## Next action

Open **R3-02** first: it is a confirmed defect with a named root cause, one method to change, one label to
reconcile and one test to add, and it is the only finding here that understates a value the tool already
displays elsewhere. Before opening anything else, take the Phase 2 re-verification of **R3-08**, **R3-12**
and **R3-26** on a current build — if they close as "does not reproduce", the audit's remaining surface is
three real items and a set of owner calls, not the twenty-nine its register lists.

## Definition-of-done checklist

- [x] Every walk-3 finding has a row (`R3-01`–`R3-29`).
- [x] Every "Current status" is a file read at a named commit; no status is a restatement of the audit.
- [x] Every observation that contradicts the source is marked **Does not reproduce** with its command.
- [x] Root causes are named for the functional findings (`R3-01`–`R3-04`).
- [x] The audit is left unedited; this register is additive.
- [ ] A current-build re-read of the five non-reproducing observations — **not done**; it needs a browser
      run and a `npm run build`, which this read-only pass did not perform.
- [ ] No gate run — stated, not implied away.

## Dated addition (2026-10-12, remediation pass at `7ae1847`)

The gates in the checklist above have now been run. `R3-01`, `R3-02`, `R3-03`, `R3-04`, `R3-09` and
`R3-16` landed as commits `2470a4c`, `3e136a0`, `e02537e`, `d7f41dc` and `3a1ba9b`; `R3-08`, `R3-12` and
`R3-26` closed as **Does not reproduce** on a current build, exactly as the Verification note predicted,
and `R3-05`, `R3-13`, `R3-15`, `R3-19` and `R3-28` needed no code. The rows below are additive; no status
above is rewritten.

| ID | Finding | Evidence | Status |
| --- | --- | --- | --- |
| **R3-30** | A provenance line breaks the training cards' definition list. `TrainingCard.vue:242-253` puts the visible read-date `<p class="mt-0.5 text-xs text-ink-muted">` inside the same `div` group as the `Energy cost` `dt`/`dd` pair, **outside** the `dd`, so the `<dl>` group holds a non-`dt`/`dd` child. axe reports it as `definition-list` (serious, `wcag131` = WCAG 1.3.1) once the cards are expanded. | `career-training-detail.spec.ts` "meets the 44px target floor and the axe A plus AA bar" fails its page-wide `toEqual([])` scan on `buildAxe(page)`; the violation targets are `.border-line-speed > .space-y-2`, `.border-pick-line > .space-y-2`, `.border-line-power > .space-y-2` and `.border-line-guts > .space-y-2`, i.e. one per training card. `TrainingCard.vue` is absent from every R3 commit's file list (`git show --name-only` on the five hashes above), which is what makes it pre-existing rather than a regression. | **Confirmed, pre-existing, out of scope for R3-01..R3-29.** Its own slice fixes it; it was not folded into the accessibility slice, which is why `R3-16`'s band-select assertion is scoped to the `label` rule and lives in its own case. |
| **R3-32** | A growing Inertia DevTools cache makes the first request after boot fatal, so browser evidence on this tree silently stops being evidence. `inertia-laravel`'s `DevToolsServiceProvider::register()` calls `EntriesRepository::pruneIfDue()`, which walks `storage/inertia-devtools` and `unlink()`s expired entries **synchronously inside the request**. Past roughly 28k files that loop exceeds PHP's `max_execution_time` (30s) and the request dies, so the harness's `/up` readiness probe never returns clean. | Measured at `storage/inertia-devtools` = 28,518 files. Probe log: `PHP Fatal error: Maximum execution time of 30 seconds exceeded in .../Filesystem.php on line 308`, stack `EntriesRepository::pruneIfDue()` → `prune()` → `deleteEntries()` → `Filesystem->delete()`, with the same `/up` logged at **~33s**; after moving the directory aside, the identical probe returned `READY after 2s: /up -> 200` at ~3s. Three browser invocations this session aborted with `Timed out waiting 60000ms from config.webServer` and `"expected": 0` while `artisan serve` started fine by hand. | **Confirmed, environmental, mitigated reversibly.** The cache was moved to `storage/inertia-devtools-aside-20261012` (28,518 files intact, nothing deleted; `storage/` is gitignored and the directory writes its own `.gitignore`). This is the proximate cause of R3-31's symptom and of the `career-inheritance-event.spec.ts` `waitForURL` timeout earlier in this pass, which was attributed to CPU contention alone and should be read as this. Standing remedy for the owner to rule on: disable DevTools for the `browser` database role rather than sweeping its cache between runs. |
| **R3-31** | The trainee step's success flash reads as absent in the keyboard case. `career-trainee-select.spec.ts` "selects a trainee by keyboard, restores focus, and keeps the choice across the profile screen" waits for the one-shot `Trainee set: <name>.` line and reports `element(s) not found`, while the same failure-time snapshot carries `Stored choice: Admire Vega` and `button "Select Trainee" [pressed]`, both read off server props. The two facts together say the visit **did** complete and the snapshot was taken after the 15s `expect` ceiling, not that the flash was lost. | **Reproduced on a quiet box, so not timing and not R3-32.** Re-run at `PLAYWRIGHT_PORT=8308` with the DevTools cache removed and `artisan serve` answering `/up` in ~2s: `career-trainee-select.spec.ts`'s keyboard case still failed the same locator while the two other selected cases passed (`"expected": 2, "unexpected": 1`). A prefetch cause stays ruled out from the code (`AppLayout.vue:107,241,318,350` are hover-only and the wizard entry does not prefetch; `View Profile` is a plain `<a href>`). What remains is keyboard-path-specific: walk 3 recorded this very flash rendering for a pointer click ("`Select Trainee` flashed `Trainee set: Rice Shower.`"), and the keyboard case is the only test in the file that asserts the flash, so no click case covers it. The write path is proven correct independently: `CareerTraineeSelectTest` 16 passed and the legacy step's identical flash assertion passes in `career-legacy-deck-steps.spec.ts` (22 tests, exit 0). | **Confirmed, open, needs its own slice.** Not folded into Slice 8 and not skipped. A diagnosis should read the request sequence of one keyboard submit (how many visits follow the `Enter`, and which render carries `flash.status`) rather than widen the wait: the snapshot shows `Stored choice: Admire Vega` and `[pressed]`, so the visit completed and only the one-shot line is missing. |

### Slice landing record (2026-10-12, close-out)

| Slice | Finding | Commit | Proof |
| --- | --- | --- | --- |
| 1 | R3-02 | `2470a4c` | `CareerInheritanceEventTest` 15 passed; RED first (Blue 1 vs 2, grandparent White absent). |
| 5 | R3-09 | `3e136a0` | `GrandparentRanksTest` 4 passed; `CareerLegacyDeckStepsTest` node literal extended; 22 browser tests exit 0. |
| 6 | R3-16 | `e02537e` | Band select named and asserted by role and by the `label` rule; the Legacy `Target` selects measured already-named, so no markup changed there. |
| 3 | R3-03 | `d7f41dc` | `career-deck-select.spec.ts` 2 cases in the 22-test exit-0 sweep. |
| 2, 4, 12 | R3-04, R3-01, consistency | `3a1ba9b` | `CockpitEnergyBandDisplayTest` 3, `EnergyReadingConsistencyTest` 5, `CareerCockpitTest`, `EnergyStateTest` unchanged labels; rendered band proven in `career-cockpit-forms.spec.ts`. |
| — | residual selector | `54a303f` | Ancestor input count scoped to the name fields after Slice 5 widened the `ancestor-` prefix. |
| 8 | R3-06 | `f08ac63` | `TraineeCardFormTest` 4 passed, RED first; picker case green in the browser; `lore-code` flat at 133 with no new hit. |
| 14 | R3-30 | `f6f393f` | **R3-30 closed.** The page-wide axe scan in `career-training-detail.spec.ts` is green at HEAD with the read date inside its `dd`. |
| 7 | R2-13 | none needed | The locator the slice asked for was already correct at `career-cockpit-forms.spec.ts:148`; the case is green. Verify-and-report, not a rebuild. |

**Slice 2 deviation, ruled accepted (owner, 2026-10-12).** The manual's `role="status"` and `text-emerald-600` were not applied: the emerald utility is not a design token, which §13 forbids, and a live-region role on one of three sibling `Saved.`-style lines would announce one form's write and not its neighbours'. The line ships in the siblings' exact classes. No code change follows from this ruling.

**Not landed, and why.** Slices 9, 10 and 11 are all answered yes with confirmed shapes but each is a schema change; §11 sends a migration with an ADR, an `ARCHITECTURE-ESSENTIALS.md` digest line and a PRD citation, and §15 makes that part of done, so none can land code-only. Slice 13 is partial: `career-legacy-deck-steps` and `career-deck-select` ran whole (22, exit 0), `career-trainee-select` ran whole with one pre-existing-classed red, and `career-training-detail` and `career-cockpit-forms` ran only their touched cases.

### Gates at HEAD `f6f393f` (2026-10-12)

`php artisan test --compact` — **1707 passed, 2 failed, 2 skipped**, 29,064 assertions, 525s. Both failures predate this program's work and neither is in a file it changed; both are left for their own slices rather than folded in.

| Red | Cause, with proof | Belongs to |
| --- | --- | --- |
| `TurnEntryFieldSourceTest` — "has the cockpit read the shared list instead of declaring its own" | The case pins the literal `import { TURN_ENTRY_STAT_FIELDS as statFields } from …`, and `c3583f0` (2026-10-10, "energy band editable and prefillable … (R2-16)") folded three names into one statement at `Cockpit.vue:54`, so the substring no longer exists. `git log -1 -S"TURN_ENTRY_ENERGY_BANDS as energyBands" -- resources/js/pages/Career/Cockpit.vue` answers `c3583f0`. Red for two days before this pass began. | The R2-16 slice's owner: either the pin tolerates a grouped import or the import is split. |
| `DocCitationParityTest` — dead citations 784 over a 708 baseline | `tools/doc_census.py:36` measures `git ls-files -- *.md`, so only **tracked** markdown is counted; every document this pass edited is untracked and cannot contribute. The working tree carries 23 dirty tracked docs and several untracked ones that tracked docs cite, which the census reports as `UNTRACKED` dead links (its own line 165: "on disk but not in git"). | Whichever session lands the doc work: the ratchet is meant to fall, never to be raised (`DocCitationParityTest:25`), so the fix is committing or repointing the citations, not moving 708. |

### Dated correction and closure, later the same day (2026-10-12)

The `DocCitationParityTest` row in the table above is wrong in two ways. Its original sentence is kept rather than
rewritten because §11 says a dated claim that proves wrong is corrected by appending, not editing.

1. "every document this pass edited is untracked and cannot contribute" holds for the two audit files and fails
   for the masters: the dirty tracked docs measured 49 dead citations' worth at this tree, 35 of them on lines the
   uncommitted diff added, so the pass did contribute. The sentence's own reasoning, that only tracked markdown is
   counted, is what made the error easy to make.
2. The dominant cause was not prose at all. Two resolver gaps in `tools/doc_census.py` counted citations to
   tracked, present files as dead: a slash-bearing ref was never tried beside its citing file, and a bare name was
   tried in only three directories, so `code-style.md` (tracked at `.ai/rules/`) and the `docs/proposals/`,
   `docs/scenarios/`, `docs/audits/` and `screenshot-notes/` names all read dead. 127 of the 784 are that, each one
   a tracked file that exists, and all 27 distinct bare names resolve to exactly one tracked file. The genuinely
   absent targets stay counted: `docs/research-scratch/DESIGN-CORPUS.md` still 61, and `RAW-FINDINGS.md`,
   `SKILLS-GAPS.md`, `docs/research-scratch/GOVERNANCE.md` and `docs/research-scratch/GOVERNANCE.md` all still dead.

Both reds are now closed and the ratchet bar was not touched: `DOC_CITATION_BASELINE` still reads 708 and its
pinning case still asserts 708.

| Red | Closure | Proof |
| --- | --- | --- |
| `TurnEntryFieldSourceTest` | The pin now asserts the three things that matter (`TURN_ENTRY_STAT_FIELDS as statFields`, the domain module path, no local `const statFields = [`), the same way the sibling training-decision case was corrected on 2026-10-10 for the identical over-pinned-literal fault. | `vendor/bin/pest tests/Feature/TurnEntryFieldSourceTest.php` → 4 passed, exit 0. |
| `DocCitationParityTest` | Both resolver rules fixed in `dead_links()`; recorded as `KNOWN-ISSUES.md` KI-98 with `tools/doc_census_selftest.py` as its check. Corrected series: 556 at `ee93b38` (the commit that set the 708 bar, so the bar itself carried ~152 false positives), 642 at `f6f393f`, 665 in this worktree including the 8 absent targets KI-98 names as its own evidence. | `python tools/doc_census_selftest.py` → exit 0, and exit 1 when run against `git show HEAD:tools/doc_census.py`, so the check fails when the resolver regresses. `vendor/bin/pest tests/Feature/DocCitationParityTest.php` → 2 passed, exit 0. |

Neither red belongs to a Round 3 slice: `c3583f0` (2026-10-10) made the first, and the second was already red at
`f6f393f` for an instrument reason. Both were attributed before anything was re-run, per §9's classification step.
R3-31 stays open and unsolved; it is a browser-suite finding, not one of these two gates.

**Level 3 re-run after the two closures.** `php artisan test --compact` → **2 skipped, 1709 passed (29,066
assertions), exit 0**, 187s. The same command at `f6f393f` read 1707 passed, **2 failed**, 2 skipped, 525s: the two
fixed cases are the whole difference, and no other case moved. Pint and PHPStan were re-run on the changed paths
(`{"tool":"pint","result":"passed"}`, `[OK] No errors`), and `composer lore` / `lore-code` both exit 0. One warning
travels with every run and is not this pass's: `tests/Feature/DatabaseSafetyTest.php:8` carries a `use Mockery;`
that PHP reports as having no effect, so the suite is green but noisy; it is a one-line deletion, left here rather
than folded into a gate fix.

Environment note for the next reader: the browser harness on this tree was failing to start at all until `storage/inertia-devtools` was moved aside (R3-32). If `config.webServer` times out again, count that directory before suspecting a port.

## Dated correction (2026-10-13, repository-evidence pass at HEAD `7c13bf9`)

An external cross-check of this register confirmed its game-mechanic readings — the Unity Cup team-race
shape, the 1300 / 1800 scenario bases, the `[Rosy Dreams]` aptitude letters, the 10 / 20 / 30 / 35 / 40 hint
ladder — and correctly declined to certify the project-specific rulings, which only the repository can
settle. Reading those rulings against the code and the decisions found **two wrong citations and no wrong
classification.** The items below stay **Known limitation**; only the decision each names was mis-stated.

**1. R3-15 and R3-29 attribute the hint-ladder withdrawal to `ADR-0001`. That ADR does not own it.**
`ADR-0001` lifts the `PRD.md` §6.11 no-prediction non-goal for Energy guidance, and its text never mentions
hints, discounts or a ladder. The rulings that do own the absence are `ADR-0011` — decision "ruled the
hint-level copy to **level only, no percentage**", open "no hint levels and no discounts are imported",
consequence "**No hint levels, no discounts.** Nothing in this import puts a hint level anywhere" — and
`ADR-0014`, which lists hint-level accumulation among what is out of scope, with the support-card hint
pools cut by `ADR-0005` (R37). The commit that removed the ladder, `992be59` (2026-10-10), names the
owner's ruling A5 and the reason in its body: "no column holds a hint level, so nothing the run records can
price one". Its **subject line** is where the "per ADR-0001" phrasing begins. The value travels in three
earlier sites, quoted here so a reader arriving from any of them reaches this correction:
`docs/audits/rice-shower-unity-cup-round2-reconciliation.md:33` (R2-11),
`docs/audits/rice-shower-unity-cup-ux-walk-resolutions.md:57` ("hint ladder removed per ADR-0001"), and
their mirrors at `docs/research-scratch/AUDIT-AND-VERIFICATION.md:7753` and `:8233`. Per `AGENTS.md` §11
those registers are not rewritten; this addition is the correction.

R2-11's instruction "Document that hint discounts are **stored** but not displayed" is wrong in its premise
as well as its citation: no discount and no run hint level is stored anywhere. `hint_discount` appears in no
file under `config/`, `app/` or `resources/js/` today, `992be59` deleted both `config('uma.skills.hint_discount')`
and `SkillsPlannerController::ladderRows()`, and the only hint data in the schema is
`support_cards.hint_skills` / `event_skills`, which are card-to-skill id lists and not a level.

**2. R3-28's `KI-34` chain points at a register line that no longer exists.** KI-34 is a reserved number
with no entry written — `docs/research-scratch/AUDIT-AND-VERIFICATION.md:949` states it as "a reservation:
number reserved, entry never written" — and the register's KI-01 to KI-37 text moved into that master on
2026-10-03, so the root `KNOWN-ISSUES.md` carries no KI-34 line at all. The docblock on
`app/Http/Controllers/Career/TraineeProfileController.php` named `KNOWN-ISSUES.md:10` for the reservation;
that pointer is corrected in place to the master section. The goal-race gap itself is unchanged and still
real: no `trainee_goals` table, model or factory exists, and `CockpitController` renders the named absence
for exactly that reason.

**Confirmed against the repository, with this pass's readings.** The evidence the external review asked for:

| Memo claim | Repository evidence |
| --- | --- |
| R3-05 and walk-1 D8: the GameTora export is authoritative for the ten letters, and the catalogue holds | `docs/adr/0004-aptitude-and-scenario-cap-reference-data.md:68-84`, which quotes the export as `["A","G","E","C","A","A","B","A","C","G"]`. Read off this tree's database, `umamusume` id 30 `Rice Shower` holds `A G E C A A B A C G`, so the stored row matches the export letter for letter and the source document's B / A / B stays an erratum rather than a correction |
| R3-11 and V-5: the wizard cannot express a mid-career position, the snapshot import can | `TrainingRun::hasImportedPosition()` is `career_position !== null` (`app/Models/TrainingRun.php:715-718`), consumed at `CockpitController.php:285` and `TimelineController.php:82,95,124`; `resources/js/pages/Career/Snapshot/` holds `Entry.vue`, `Setup.vue`, `Review.vue`; pinned by `TrainingRunCareerPositionTest`, `CreateSnapshotRunTest`, `SnapshotCockpitTest` |
| R3-25: `ADR-0014` declined the collection table | `docs/adr/0014-support-card-entities.md:4` ("the collection stays out"), `:24` (the `UserSupportCard` row: "Collection tracking is still the feature §6.9 cut"), `:177-180` (`ADR-0023`'s dated erratum adds a per-slot `ownership` and keeps the collection cut) |
| The slice landing record's commits | all eight hashes exist with the subjects the table names: `2470a4c`, `3e136a0`, `e02537e`, `d7f41dc`, `3a1ba9b`, `54a303f`, `f08ac63`, `f6f393f` |
| "seven entries qualify as known limitations" | not this register's headline metric. See **Counting rule** below: 29 rows, 8 labelled **Known limitation**, 6 distinct findings behind those labels, and seven reachable only by dropping the row that is also marked `/ open` |

### Counting rule (the metric a close-out claim rests on)

Three different numbers get called "the count" in this register's circulation, so the rule is written down.
The rows were parsed from the table rather than counted by eye: `| **R3-` matches 29 rows in the register
table, with no gaps across `R3-01`..`R3-29` and no duplicate id.

- **Register rows: 29.** The table's own size. This is what the Definition-of-done checkbox means
  ("Every walk-3 finding has a row (`R3-01`–`R3-29`)") and what "not the twenty-nine its register lists"
  counts.
- **Distinct findings: 24.** Five rows are pointers to another row rather than findings of their own —
  R3-19→R3-11, R3-20→R3-12, R3-21→R3-06, R3-22→R3-09, R3-29→R3-15. A pointer row must not be counted twice
  or a close-out reads as five findings more than exist.
- **ID space: 32.** `R3-30`, `R3-31` and `R3-32` are this pass's own findings, recorded in the dated
  addition's table, not walk-3 observations. They are outside the 29-row register and must not be folded
  into a "walk 3 said" count.

**Known limitations: 8 rows, 6 distinct findings.** The eight rows labelled **Known limitation** are R3-05
(`/ open`), R3-11 (`by design`), R3-13, R3-14, R3-15, R3-19, R3-28 (`KI-34`) and R3-29; collapsing the two
pointer rows (R3-19→R3-11, R3-29→R3-15) leaves **six**: R3-05, R3-11, R3-13, R3-14, R3-15, R3-28. **Seven is
reachable exactly one way**: eight labelled rows less R3-05, which is the only one of them the register also
marks `/ open` and which `ADR-0004` leaves a source conflict rather than a settled ruling. That is a
defensible count of *settled* limitations, but it is a row count with one row removed, not a finding count,
and nothing in this register states it. The headline for this class is therefore **six findings across eight
rows**, with "settled: five across seven rows" written out wherever the exclusion is meant. "Seven" as a
finding count is withdrawn rather than preserved.

The remaining 29-row partition, so the classes are reconcilable to the total: 4 **Confirmed defect** (R3-01,
R3-02, R3-03, R3-09), 1 confirmed-in-part (R3-04), 8 **Known limitation**, 4 **Does not reproduce** (R3-08,
R3-12, R3-26 and the R3-20 pointer), 4 **Unrepresented source data** (R3-06, R3-18, R3-24 and the R3-21
pointer), 1 **Unrepresented workflow** (R3-10), 2 **Partially** fixed/represented (R3-07, R3-27), 1
**Reported, not re-measured** (R3-16), 1 **Cosmetic, unverified** (R3-17), 1 **Not a gap** (R3-23), 1
**Deferred, owner-gated** (R3-25), 1 confirmed pointer (R3-22). Sum: 29.

The two gates this register left red are green at `7c13bf9`: `vendor/bin/pest
tests/Feature/TurnEntryFieldSourceTest.php tests/Feature/DocCitationParityTest.php` → 6 passed, exit 0. The
`SkillsPlanner.vue:227-232` and `:14` line reads in R3-15 are frozen at the commit that register declares it
read, as the Method section requires, and are not re-derived here; the absence copy now sits at `:269` and
`:274`. The suite totals above (`1707 passed / 2 failed` at `f6f393f`, `1709 passed` after the two closures)
are 2026-10-12 readings: this pass ran only the two named files, so neither figure is restated as current.

