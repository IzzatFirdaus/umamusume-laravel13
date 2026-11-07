# Round 7 — path corrections (`RS-UC-R7-PATHS`)

**Status:** confirmed at HEAD. Every row below was measured on this tree, not inherited from the implementation dispatch.
The dispatch named eight paths that the tree does not carry. A slice that opens on a wrong path burns its pre-flight and
its RED, so these are recorded before any slice opens.

**Measured at:** `05be66164c71fa4b01f717adf60c22601ec1e265` (`master`), 2026-11-07.
**Register classification:** `docs/audits/rice-shower-unity-cup-round7-reconciliation.md` at `05be6616`, statuses frozen
at `8a3cf21c`. No row is reclassified here.

---

## §0 Baseline, captured at session start

```
$ git rev-parse HEAD
05be66164c71fa4b01f717adf60c22601ec1e265

$ git stash list
stash@{0}: WIP on master: b4458c09 fix(raceplanner): remove the duplicate closing template tag the group loop left
stash@{1}: On master: peer-traineeprofile
stash@{2}: On master: Phase 2.1 work in progress (foreign, pre-state)
```

**Three stash entries exist and none is this session's.** None is popped, applied, or inspected. The standing
prohibition on `git stash pop` is why they are recorded rather than used.

```
$ git status --short -- <the six peer paths>
 D docs/audits/AUDIT-SUPPORT-CARDS-UIUX-2026-11-05.md
 M docs/imports/Umamusume_Progress_Tracker_Runs.md
 M docs/imports/ura-finale-runs.md
 D docs/proposals/PLAN_SLICES_6_7.md
 M docs/research-scratch/AUDIT-AND-VERIFICATION.md
 M docs/research-scratch/PROCESS-PLANS.md

$ git status --short
 D docs/audits/AUDIT-SUPPORT-CARDS-UIUX-2026-11-05.md
 M docs/imports/Umamusume_Progress_Tracker_Runs.md
 M docs/imports/ura-finale-runs.md
 D docs/proposals/PLAN_SLICES_6_7.md
 M docs/research-scratch/AUDIT-AND-VERIFICATION.md
 M docs/research-scratch/PROCESS-PLANS.md
?? docs/audits/RS-UC-2026-10/
?? docs/audits/RS-UC-2026-11-snapshot-door/
?? docs/audits/RS-UC-2026-12/
?? docs/audits/rice-shower-unity-cup-ux-walk-6.md
?? docs/audits/rice-shower-unity-cup-ux-walk-7-snapshot-door.md
?? docs/audits/rice-shower-unity-cup-ux-walk-7.md
?? docs/imports/ura-finale-runs.md.bak
?? screen_data.json
```

**Peer set, byte-identical target:** the six paths above, in those exact statuses, plus the seven untracked entries.
Every slice's post-commit `git status --short` must show exactly this set plus the three round-7 audit files this
session creates. Any other appearance is an incident.

**Untracked peer artifacts not to be read or staged:** `docs/imports/ura-finale-runs.md.bak`, `screen_data.json`,
`docs/audits/RS-UC-2026-10/`.

---

## §1 The eight corrections

| # | Dispatch path | Actual path at `05be6616` | Slices | Evidence |
| --- | --- | --- | --- | --- |
| 1 | `resources/js/components/ui/Disclosure.vue` | **does not exist** | S22 | `ls` → `No such file or directory` |
| 2 | `resources/js/components/career/AncestorEditor.vue` | `resources/js/components/legacy/AncestorEditor.vue` | S9, S11 | `find resources/js -name 'AncestorEditor.vue'` → one hit, under `legacy/` |
| 3 | `tests/Feature/TrainingDetailTest.php` | `tests/Feature/CareerTrainingDetailTest.php` | S1, S13 | dispatch name absent; `Career*` present |
| 4 | `tests/Feature/TeamPanelBuilderTest.php` | **does not exist** | S3 | no file of that name; `TeamPanelBuilder` is exercised by `ScenarioPanelTest.php` and `Career/SpiritBurstCountdownTest.php` |
| 5 | `tests/Feature/RaceDecisionTest.php` | `tests/Feature/CareerRaceDecisionTest.php` | S13 | dispatch name absent; `Career*` present |
| 6 | `tests/Feature/EventDecisionTest.php` | `tests/Feature/CareerEventDecisionTest.php` | S13 | dispatch name absent; `Career*` present |
| 7 | `tests/Feature/SkillsPlannerTest.php` | `tests/Feature/CareerSkillsPlannerTest.php` (+ `CareerSkillsPlannerRecommendationTest.php`) | S19 | dispatch name absent; two `Career*` files present |
| 8 | `tests/browser/snapshot-review.spec.ts` | **does not exist** — `tests/browser/career-snapshot.spec.ts` is the only snapshot spec | S18a | `ls tests/browser/ \| grep -i snap` → one hit |

**Every other path the dispatch names exists.** Verified present: `app/Services/Career/TeamPanelBuilder.php`,
`resources/js/pages/Career/TrainingDetail.vue`, `BuildTarget.vue`, `Cockpit.vue`, `InheritanceEvent.vue`,
`LegacySelect.vue`, `EventDecision.vue`, `RaceDecision.vue`, `Timeline.vue`, `TraineeSelect.vue`, `Preflight.vue`,
`TraineeProfile.vue`, `Snapshot/Review.vue`, `SkillsPlanner.vue`, `components/career/RecordedActionCard.vue`,
`components/career/CareerStatePanel.vue`, `components/career/CareerSectionNav.vue`,
`components/support/SupportSlot.vue`, `app/Http/Requests/StoreLegacySelectionRequest.php`,
`app/Models/Legacy/LegacySelectionPayload.php`, `app/Services/Skills/HintDiscount.php`,
`app/Http/Controllers/Career/SnapshotController.php`, `TrainingDecisionController.php`.

---

## §2 Slice-specific consequences

### 2.1 S22 — there is no disclosure primitive to repair

`aria-expanded` is carried by nine files, and none is a shared disclosure component:

```
resources/js/components/career/TrainingCard.vue
resources/js/components/Form/SearchableSkillPicker.vue
resources/js/components/review/CandidateForm.vue
resources/js/components/TraineeCombobox.vue
resources/js/components/ui/Combobox.vue
resources/js/layouts/AppLayout.vue
resources/js/pages/Database/Events.vue
resources/js/pages/Database/ShopItems.vue
resources/js/pages/Database/Sparks.vue
```

**`R7-91`'s caret defect therefore has no single owner to fix.** The walk recorded it on the Events and Skills career
screens. S22 must grep for the actual caret glyph at open and either find a shared component then or repair each
surface. **Recorded as an open question for S22, not a blocker**: S22 is a Phase 5 cosmetic slice and can be
re-scoped at its own pre-flight.

### 2.2 S3 — the objective-state case must be written fresh

`tests/Feature/CockpitObjectivesTest.php` exists but contains no reference to `TeamPanelBuilder`. The builder is
exercised only by `tests/Feature/ScenarioPanelTest.php` and `tests/Feature/Career/SpiritBurstCountdownTest.php`.
**S3's RED is a new case in `ScenarioPanelTest.php`** (or a new dedicated file), not an edit to an existing assertion.

The state match to change, read at `05be6616`, `app/Services/Career/TeamPanelBuilder.php:253-263`:

```php
$reached = $goal->isAtOrBeforeTurn($next);

$state = match (true) {
    $goal->global_unreleased => null,
    $met => 'completed',
    $next === null => 'upcoming',
    $goal->turnWithinYear() === (int) $next['turn']
        && $goal->career_year === (int) $next['year'] => 'current',
    $reached => 'missed',
    default => 'upcoming',
};
```

`$met` false + `$reached` true falls to `'missed'`, which is exactly `R7-82`. The race path at `:107-114` carries the
same shape and `R7-22`'s `current` verdict comes from `:260-261`. **One predicate change must reach both arms** — S3
touches two matches in one file.

### 2.3 S1 — the field's default is already correct

Read at `05be6616`, `app/Http/Controllers/Career/TrainingDecisionController.php:574`:

```php
'turn' => $run->nextTurnNumber(),
```

**The server sends the next turn to play, not `1`.** The walk observed `1` on a wizard-created fresh run, where
`nextTurnNumber()` *is* `1`. The defect `R7-20` names is therefore the **label and affordance**, not the default value:
`Turn *` reads as "the run's position" while the value is "the turn to play", and a mid-career user has no prompt
saying which is which. S1's RED is a label/helper assertion, never "the field opens at 1".

### 2.4 S18 splits

The dispatch pairs `R7-83` (timeline joins recorded races) with `R7-99` (the snapshot review's omitted identity
fields) under one owner call. **`R7-99` needs no ruling** — it is a read-back omission with a settled
`Confirmed defect` status and a named file. It lands as **S18a**, ahead of the timeline work, which stays **S18b**
pending the `(i)`/`(ii)` shape ruling.

---

## §3 What this file is not

- Not a reclassification. Register §4 statuses stay as read at `8a3cf21c`.
- Not an edit to any walk, or to rounds 1–6.
- Not authority to touch a peer-owned path. The six peer paths and the untracked peer artifacts stay as captured in §0.
