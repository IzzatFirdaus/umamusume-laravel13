# Resolution record: Rice Shower / Unity Cup UX walk

What happened to each finding in `docs/audits/rice-shower-unity-cup-ux-walk.md` after the
remediation began. The audit is the historical baseline and is **not** rewritten; this file records
only what came afterwards, and a finding whose text later proves wrong is corrected by a dated
erratum in its own document, never by editing the audit.

All of it landed on 2026-10-09, in one session. The session-boundary record is below, because the
brief's stopping point and what was actually done are not the same thing.

## Session boundaries

The brief makes Session 1 Phase 1 only (D1-D3) and states: "Do not start Phase 2 in Session 1 unless
explicitly instructed."

**Phase 2 was started in the same session, and it was explicitly instructed.** Before any code was
changed, the scope was put to the owner as a choice between "Session 1 only (D1-D3)", the brief's
mandatory stopping point, and "Session 1 + Session 2 (D1-D9)". The owner chose the second. The same
exchange authorised one commit per completed slice, and authorised the `deck_slots.ownership` column
together with a drafted ADR - the commit, not the ruling: `ADR-0023` still stands Proposed awaiting the
owner's decision.

So the record is **Session 1 = D1, D2, D3** and **Session 2 = D4-D8 delivered, D9 not delivered**. The
boundary was authorized rather than crossed, but it was not closed in the sense the brief intended: the
Session 1 handoff and the Session 2 handoff came out as one artifact, so the intermediate stable
handoff the brief asked for before Phase 2 opened does not exist as a separate record.

**Phase 2 is not complete.** Its Definition-of-Done list includes "duplicate turn-entry business logic
is eliminated or placed behind one canonical contract" and "one authoritative turn persistence path
exists". The second is satisfied - and was already satisfied before this work, which is D9's finding.
The first is not done. Phase 2 closes when D9's remaining work lands.

## Status

| Finding | Status | Slice | Commit | Evidence |
| ------- | ------ | ----- | ------ | -------- |
| D1 inheritance HTTP 500 | **Fixed** (browser half green, 2026-10-09) | 1.1 | `291f6ea` | `CareerInheritanceEventTest` (14 cases); `career-inheritance-event.spec.ts` **8 of 8 passed**, 5.8 min |
| D2 blank trailing Spark row | **Fixed** | 1.2 | `a4b2b26` | `CareerLegacyDeckStepsTest` (1 new case) |
| D3 rented/friend flag not persisted | **Fixed** | 1.3 | `fdebcc9` | `RunDeckTest`, `SupportDeckBuilderTest`, `CareerPreflightTest` |
| D4 target validation contradicts the UI | **Fixed** | 2.1 | `be02297` | `CareerBuildTargetTest` (3 new cases); the sibling `KI-47` named in the follow-up is already **closed** |
| D5 trainee stored-choice readback `N/A` | **Fixed** | 2.2 | `dd6dd22` | `CareerTraineeSelectTest` (1 new case) |
| D6 turn table hides Fans/Energy | **Fixed** (rendered surface removed, 2026-10-09; see below) | 2.3 | `89e8453` | `TurnRowActionsTest` (2 new cases) |
| D7 wizard step numbering skips 3 | **Fixed** (rendered surface removed, 2026-10-09; see below) | 2.4 | `6d53ffc` | `GuidedTurnOnRunViewTest` (1 new case) |
| D8 Rice Shower aptitude mismatch | **Verified, not a tool defect** | 2.5 | `be48e60` | `CareerTraineeSelectTest` (1 new case) |
| D9 duplicate turn-entry paths | **Fixed** — one shared field source, all three surfaces wired and labelled alike (2026-10-09) | 2.6 | `8e3d33c`, `5e41137`, `7a81c7c` + this commit | `TurnEntryFieldSourceTest` (4 cases) |
| Career position / snapshot mode | **Landed** (Phases 4–5; 4.4 and 5.3 cockpit-read deferred, see close) | 4.1–5.4 | `0025f37`, `d0bd138`, `a31000c`, `b890cd5`, `fc66978`, `8c7628d` | `SnapshotReviewTest`, `SnapshotTimelineTest`, `SnapshotRaceCalendarTest`, `SnapshotTrainingDecisionTest` |
| Legacy disambiguation, ancestor rank, grandparent sparks | **Landed** (Phase 6) | 6.1–6.3 | `31c9f5d`, `5876f64`, `f39ff03` | `LegacyDisambiguationTest`, `AncestorRankTest`, `GrandparentSparksTest` |
| Scenario metadata, typed spark targets | **Landed** (Phase 7) | 7.1–7.2 | `1098546`, `6fa0d01` | `ScenarioMetadataTest`, `SparkTargetValidationTest` |
| Energy model (exact/band/unknown) | **Landed** (Phase 8.1; §11 record added at `837d995`) | 8.1 | `1ec92ab` | `EnergyStateTest` |

## Per finding

### D1 — inheritance HTTP 500: Fixed

`StoreLegacySelectionRequest::payload()` stores `legacies.*.ancestors` as a list of **names**
(`ADR-0010` Consequences §2) and `InheritanceEventController::legacySection()` mapped each entry as an
array with `slot`/`name` keys, so `array_map()` threw on the first string. Names are the canonical
shape: `LegacySelectionSchemaTest` already pinned them, `AncestryGraph::parentNode()` already read
them, and the one stored record on the dev database held names. The controller reads the name list,
`LegacySelectionPayload` refuses a slot-record ancestor instead of letting a second shape exist, and
the page's props contract is `list<string|null>`. The test fixture was converted to the real UI
payload and zero/one/multiple-ancestor cases added.

### D2 — blank trailing Spark row: Fixed

The Spark editor appends an empty trailing row on `Add Spark` (kind only, no target, no stars).
`StoreLegacySelectionRequest::payload()` stored it unchanged, so the Preflight contract listed a
phantom bare `Blue`; the dev database still holds that exact row on run 8. The write boundary now
discards a Spark carrying neither a target nor a star count, and `LegacySelect.vue` applies the same
rule so the chip list and the posted payload agree while the row is still a draft.

**Not done:** the phantom already stored on run 8 was left in place. Nothing reads it as a Spark
except the Inheritance page's predicted counts, and rewriting a stored record to fix a display is a
data edit this slice did not authorise.

### D3 — rented/friend flag: Fixed

`deck_slots` had no ownership column (`ADR-0014`), so the flag lived only in the wizard's session
draft and `/training-runs/{run}/deck` printed the one value it could read. `deck_slots.ownership`
(nullable `OWNED` / `RENTED`, null = not recorded) is written by both writers and read by the
builder; `DeckSlot::booted()` guards the value. The run screen's deck panel has no ownership control,
so its re-save preserves what the slot already holds. Docs travel with the schema: `ADR-0023`
(Proposed, for the owner's ruling), a dated erratum in `ADR-0014`, the digest, and the ADR index.

### D4 — target validation: Fixed

The message under every stat offered "or leave the whole target unset", but `purpose` and all five
stats were `required`, so that branch was unreachable and an empty save answered nine errors. The
whole target is now optional and all or nothing: nothing entered means no field is required and
`payload()` answers null (which clears the column); any value makes every field required. The
no-target downstream path needed no change (`TrainerAdvisor::deficits()` already answers nulls).

**The sibling named in the follow-up is already closed, and that premise did not hold.** The
instruction was to reference `KI-47` as "the still-open sibling" — the no-scenario band/form ceiling
disagreement (`/1400` on the bar against `max=1200` on the form). It is not open. `KI-47` was ruled by
the human owner on 2026-10-01 ("the band is wrong") and **closed the same day by `04658f2`**, four days
before this remediation began, which is why it is absent from `KNOWN-ISSUES.md`. The lesson is already
carried at the surface: `BuildTargetController`'s docblock names `ScenarioCaps::forRun` as the authority
(`ADR-0015`, KI-47) and `StoreTurnEntryRequest`'s own comment states a no-scenario run "keeps 1200", so
band and form read the same number. No open register entry covers a band/form ceiling disagreement, so
D4 may read as fully closed. Recorded rather than silently complied with, because writing "still open"
would put a false status in this record. The band's current rendered number was **not** re-measured in a
browser in this session.

### D5 — trainee readback: Fixed

The step's `Stored choice:` readout derived the name from `trainees.data`, the paginated and filtered
roster, so a Trainer who searched and selected saw `N/A` beside a flash confirming the selection. The
name now travels with the stored selection.

### D6 — turn table Fans/Energy: Fixed, rendered surface removed

The payload already carried `energy` and `fans`; only the table lacked the columns. Both are now
printed, and a turn that recorded neither names its own absence (`N/A` with a `title`). The read path
and the columns' presence are pinned by feature tests; the rendered-DOM assertion in
`run-detail.spec.ts` was **not executed** (see the infrastructure note).

**2026-10-09 resolution — rendered surface removed.** The peer's cockpit cutover deleted
`Runs/Show.vue`, its `show()` method and `run-detail.spec.ts` in one pass (`TrainingRunController`
`:198`), so the run-screen turn table the rendered assertion addressed no longer exists on this tree,
and `runs.show` is a redirect to `runs.cockpit`. The behaviour the assertion guarded — Energy and Fans
reachable and printed — is carried by the cockpit, whose correction form reads Energy and Fans
(`Cockpit.vue:446-452`) and whose `career-cockpit.spec.ts` records Energy through it (the "records
Energy through the cockpit correction and then marks one action" case). The D6 feature tests were
removed with the surface. Status: **Fixed (rendered surface removed)**.

### D7 — wizard step numbering: Fixed, rendered surface removed

The indicator counted `current`'s position inside `def.steps`, the scenario's turn vocabulary (Unity
Cup's five include a facility and a team-race step the rail never lands on), so it read "Step 2 of 5"
then "Step 4 of 5". The rail has two stages; the server now sends `flow` and the indicator and the
progress pips count those. The rendered assertion in `run-detail.spec.ts` was **not executed**.

**2026-10-09 resolution — rendered surface removed.** The same cutover removed the `runs.show` guided
rail entirely, so there is no step indicator left on this tree to assert against. The cockpit has no
guided flow and no step indicator, so the assertion cannot be re-addressed. Status: **Fixed (rendered
surface removed)**.

### D8 — Rice Shower aptitudes: Verified, not a tool defect

The audit reported a catalogue/display mismatch (document: Mile B, Front A, Late B; picker: C, B, C).
The picker is faithful. The committed export the seeder reads
(`database/seeders/data/gametora-characters.e9e9ee6d.json`, card `103001`, `[Rosy Dreams]`) carries
`aptitude: ["A","G","E","C","A","A","B","A","C","G"]` in the turf..end order; the seeded column holds
it letter for letter and `TraineeSelectController::aptitudes()` maps index to index. No seed,
transformation or display defect exists on the tool's side, so nothing was corrected: overriding the
project's own source with one document's transcription is the change the brief's non-goal forbids.
All ten letters are pinned for that trainee, and the scenario document gains a dated erratum
recording the disagreement as open.

The **authority ruling** is recorded at the governing ADR rather than only in the document: `ADR-0004`
gained a dated erratum stating that the GameTora export is authoritative for aptitude letters, that a
scenario document is a secondary reference whose disagreements resolve in the catalogue's favour, and
that a disagreement is recorded open rather than resolved in the display path.

### D9 — duplicate turn-entry paths: Diagnosed, not implemented

**The premise does not hold as stated.** The two surfaces are `Runs/Show.vue` (the guided rail plus
the `Correct a turn by hand` disclosure) and `TrainingDecisionController`'s decision page. Both POST
to **the same route** (`runs.turns.store`) through **the same Form Request**
(`StoreTurnEntryRequest`) and the same controller method, so there is one persistence path, one
validation set and one normalization contract. `TrainingDecisionController` says so itself: "The
write is the guided turn's, unchanged."

What is left of the finding is **presentational**. The backend diagnosis below is the artifact the
follow-up's §6 asked for: written before any code, so the refactor is designed rather than improvised.

**Backend diagnosis (2026-10-09).** The persistence contract is already single, and was before this
remediation. One route, one Form Request, one controller method, one table:

| Layer           | Owner                                                                |
| --------------- | -------------------------------------------------------------------- |
| Route           | `POST /training-runs/{run}/turns` -> `runs.turns.store` (`routes/web.php:175`) |
| Form Request    | `App\Http\Requests\StoreTurnEntryRequest`                            |
| Controller      | `TrainingRunController::storeTurn()` (`:1769`)                       |
| Stored row      | `turn_entries`                                                       |

Three surfaces post to that route and are validated by that request:

| Surface                  | Component                    | Field-list source                                                       | Stat label       |
| ------------------------ | ---------------------------- | ----------------------------------------------------------------------- | ---------------- |
| Run record, raw hatch    | `Runs/Show.vue:720-732`      | `const statWords = ['speed', …, 'wit']` (`:119`)                         | `Speed *`        |
| Training decision        | `Career/TrainingDetail.vue:304-366` | `const statFields = [{ name: 'speed', label: 'Speed' }, …]` (`:92-98`) | `Speed total *`  |
| Cockpit correction       | `Career/Cockpit.vue`         | `const statFields = [{ name: 'speed', label: 'Speed' }, …]` (`:189`)     | `Speed`          |

So the duplication is **three-way, not two-way**: the same five-stat list is declared three times,
twice under the identical name `statFields` with the same `{ name, label }` shape, and the labels
drift. Each surface also declares its own extras (`Show.vue` adds `sp` and `condition`;
`TrainingDetail.vue` adds `sp`, `energy` and `fans`), and that part genuinely differs, so it stays
per-surface.

**The shape the fix takes, and the alternative rejected.** One shared field-definition module (a plain
exported constant read by all three surfaces), not a shared form component and not a base class. That
is `ADR-0018`'s reasoning when it chose `App\Services\PageSize` over a base `FormRequest`: the surfaces
agree on the field list and the labels and disagree on everything that matters — which extras they
offer, whether they preview or write, what they call themselves. A shared form component would push a
union of three behaviours into one place, and the drift it removed would return as a props contract
with a flag per surface. `ADR-0018`'s rejected base-class alternative is the precedent, recorded with
the same specificity.

**What cannot be moved yet.** Of the four files the unification touches, three carry another session's
uncommitted work: `TrainingRunController.php`, `StoreTurnEntryRequest.php`, `Runs/Show.vue` and
`Career/Cockpit.vue` are all modified in the working tree by a concurrent session; only
`Career/TrainingDetail.vue` is clean. `ADR-0018` left two copies of the clamp in place for exactly this
reason, and the same rule applies: the unification waits for those files to be free.

**Remaining, explicitly.** One shared turn-entry field definition read by all three surfaces; the label
vocabulary reconciled (`Speed` on the run record against `Speed total *` on the decision screen); and
each surface naming whether it creates or edits a turn. Until that lands, Phase 2's canonical-contract
gate stays open. **D9 is not Fixed.**

## Infrastructure findings

1. **The database guard refused every Windows path (fixed, `033c974`).**
   `DatabaseGuard::isAbsolute()` wrote its character class as `[\\/]`, which compiles to a class
   holding only `/`, so every drive path using backslashes read as relative and
   `assertAbsolutePath()` refused it at application boot for any isolated role. The Playwright harness
   passes its scratch database as a Windows path (Node's `join()` emits backslashes), so
   `npm run test:browser` could not start its server at all. The predicate now normalises the
   separator first, and `tests/Unit/DatabaseGuardPathTest.php` pins both separators (the method had no
   coverage, which is how the regression shipped).

2. **The browser suite cannot be executed on this host (outstanding).** With the guard fixed, the
   server boots and answers `/up` in ~1.3s, but the specs fail in their own setup: the scratch
   database is seeded by `DatabaseSeeder`, which seeds **no Veterans**, while
   `career-inheritance-event.spec.ts` selects a parent by label (`Symboli Rudolf`) from the
   run-scoped Legacy Lab's Veteran picker. Every case fails at that `selectOption` before reaching
   the page under test. This is a pre-existing gap introduced when KI-69 made the harness own a fresh
   database, not a regression from this remediation. The minimum fix is a Veteran in the browser
   seed (or a spec that creates its own), after which the harness's cost on a contended host should be
   measured again: a two-spec run exceeded 17 minutes here while a peer session was rebuilding assets.

   Consequence: D1, D2, D3, D5, D6, D7 and D8 have feature-test coverage that passed, and the
   rendered-DOM halves added to `run-detail.spec.ts` (D6, D7) are written but unrun. No browser claim
   in this record is a pass.

3. **`app/Http/Controllers/TrainingRunController.php` and `resources/js/pages/Runs/Show.vue` carry
   another session's uncommitted work.** Each of this remediation's commits stages only its own hunks
   in those two files (built from the index and written back with `git update-index --cacheinfo`); the
   other session's work is untouched in the working tree.

### Correction to finding 2, same day — the seed gap was the first blocker, not the cause

Item 2 above diagnosed the missing Veterans. Removing that dependency (a spec-local edit — this suite
must start with zero runs, and a Veteran is a filed run) moved the failure and exposed the real one:
`career-inheritance-event.spec.ts` addresses its ancestry controls as `input[name="legacies.0.rank"]`,
`[name="legacies.0.ancestors.0"]` and `[name="legacies.0.sparks.0.kind"]`, and **no such `name`
attribute exists on either surface**. `Legacy/Builder.vue` and `Career/LegacySelect.vue` both bind
those fields with `v-model` and give them an `:id` (`rank-parent_a`) but no `name`; only the parent pick
carries a `name`, because `AncestryNode` is handed `controlName` — which is why the original failure
resolved the locator and then reported "did not find some options" instead of never finding it.

The spec's own docblock claims "the legacy configuration is entered via the run-scoped Legacy Lab
builder". The builder cannot be addressed the way the spec addresses it, so **this spec has never been
able to pass**. `openInheritanceEvent()` needs re-addressing by `id`/role, or rebuilding on the wizard
path the audit actually used — roughly twenty selectors, each verification costing many minutes on this
host. Not attempted here. The partial edit made while diagnosing this was reverted; the spec file is
unchanged from `HEAD`.

**The D1 browser gate is green (2026-10-09), and the sentence above is superseded by it.**
`career-inheritance-event.spec.ts` passed **8 of 8** in 5.8 minutes on the scratch harness: the
predicted/observed separation and badges, the milestone glyphs, the keyboard entry through the observed
form, the 44px sweep, the axe scan, the empty state, the aggregate Spark counts and the no-computed-figure
case. Getting there took the fixture off surfaces that no longer render: the turns are recorded over
`runs.turns.store` (the shape `ura-panel.spec.ts` already uses) because `runs.show` is now a redirect to
the cockpit and no rendered surface offers a create-turn form, and the completion wait reads the write's
own redirect rather than a session flash a second render can consume.

**D6 and D7's rendered assertions are unrunnable on this tree, and that is a structural finding rather
than a skipped run.** Their spec, `run-detail.spec.ts`, drives the guided rail and the turn table, both
of which live in `Runs/Show.vue` - and `runs.show` is now a redirect to the cockpit
(`routes/web.php:116`), with `TrainingRunController::show()` left unrouted. A one-case probe of the spec
failed exactly there, at `page.waitForURL: Test timeout of 180000ms exceeded`, before reaching any D6 or
D7 assertion. Both stay **Fixed (browser half unrunnable)**: their feature coverage is green, and their
rendered half belongs to whoever lands the cockpit cutover, since the cockpit is the surface that now
renders and it is peer-held.

### Correction to finding 2, later the same day — the addressing fix landed (Path B)

The paragraph above recorded the re-addressing as "not attempted here" and the spec as "unchanged from
`HEAD`". Both have since changed: the fix landed, and the spec file is no longer at `HEAD`.

**The path was decided by a check, not by preference.** `Legacy/Builder.vue` carries an unstaged peer
hunk in this shared worktree (a `:model-value` binding on the `AncestryNode` invocation, ~`:243`), so the
component fix ("Path A") was not available — a file carrying another session's uncommitted work is not
touched in this remediation. The spec-only fix ("Path B") was taken instead and touched no component.

**The `name` gap is a convention gap, not a functional one (Case B).** Verified before deciding:
`Legacy/Builder.vue` does render a real `<form>` (`:207`) but submits through Inertia's `useForm` with
`@submit.prevent="submit"`, never native submission and never `FormData(form)`; `Career/LegacySelect.vue`
has no `<form>` element at all; and `FormData` appears nowhere on either surface (`resources/js` carries
it only in `GuidedStep.vue:110` and `Import.vue`'s `forceFormData`). A `name` attribute on the rank,
ancestor, rented or Spark controls is therefore an HTML-convention improvement, not a load-bearing
contract, and adding it would have been a production change made to accommodate a test. It is recorded
as owed, not claimed as a defect.

**Path B had to fix two independent misaddresses, not one.**

1. **The field addresses.** The rank, ancestor, rented and Spark controls carry `v-model` and an `:id`
   and no `name`; only the parent pick carries one, because `AncestryNode` is handed `controlName`
   (plan §4.1 item 10, `KNOWN-ISSUES.md` KI-69). The fixture now addresses them by `:id`
   (`#rank-parent_a`, `#ancestor-parent_a-0`) and by role plus the label the wrapping `<label>` gives
   them ("Kind", "Applies to", "Stars").
2. **The save contract.** The spec waited on a `Save Legacy` button and a `Saving the Legacy…` status
   toast. Those belong to the **wizard** (`Career/LegacySelect.vue:451,454`); the surface this fixture
   actually navigates to, the run-scoped builder, has `Confirm Inheritance` (`:449`) and no
   `role="status"` element at all. The wait is now the disclosure flipping to the recorded state, which
   is the signal a Trainer reads.

The parent pick is left unchosen: this suite's scratch database holds no Veterans (`KI-69` class 3), so
the roster renders no options, and `legacies.*.legacy_id` is nullable, so the payload is valid without
one. The picker's own contract is covered by `ancestry-node-picker.spec.ts`, a component-level case
mounted for exactly that reason.

**A third defect in the same spec, found by reading the page rather than by the run.** The case
`predicted section aggregates sparks across both parents and grandparents` asserts `White: 2 Sparks` and
`Scenario: 1 Spark`, but the fixture enters only blue, pink and green Sparks, and
`InheritanceEventController::predictedSection()` (`:195-204`) emits a kind **only when at least one Spark
of it exists**. Those two assertions cannot pass against any fixture entering blue/pink/green only. They
are left unchanged rather than fitted to the fixture, and are recorded here as an open defect in this
spec.

**The one authorized run (2026-10-09).** `PLAYWRIGHT_PORT=8233 npx playwright test
tests/browser/career-inheritance-event.spec.ts`. The fixture's re-addressing **worked**: the run was
created, twenty turns logged, and every re-addressed control was filled and persisted. The compare
surface the write redirects to reads `Parent A rank 3`, `Parent A ancestors Symboli Rudolf, Mejiro
McQueen`, `Parent B rank 2`, `Rented from a friend B`, `Blue Sparks 2`, `Pink Sparks 1`, `Green Sparks
1` — the payload `StoreLegacySelectionRequest::payload()` stored. So `#rank-parent_a`,
`#ancestor-parent_a-0`, the "Kind"/"Applies to"/"Stars" role addresses and the "Rented from a friend"
checkbox all resolve, and the fixture's data entry is sound.

The run still **failed**, on a third misaddress in the same fixture: the completion wait.
`getByText('This run already has a Legacy selection recorded.')` never resolved, because
`LegacyController::update()` does not return to the builder — it redirects to
`route('legacy.compare', ['runs' => [$run->id]])` with the flash `Inheritance recorded.`
(`LegacyController.php:248-250`). The DOM snapshot Playwright captured on failure **is** that compare
page, carrying the flash, which is how the redirect was identified rather than guessed. The write
succeeded; the wait looked in the wrong place.

**The fix is one line and is deliberately not applied here.** The wait becomes the write's own signal:

```ts
await page.getByRole('button', { name: 'Confirm Inheritance' }).click();
await expect(page.getByText('Inheritance recorded.')).toBeVisible(WRITE);
```

It is left for the next slice because the follow-up's §4 is explicit that a new issue found by the run
is recorded and fixed as a separate slice, never folded into the same commit, and a second run to
verify it is outside this session's budget.

**The run split 4/4, and the second failure was caused by a concurrent session, not by this spec.**
Cases 1-4 reached the completion wait above. Cases 5-8 failed **earlier**, at the fixture's
`page.waitForURL(/\/training-runs\/\d+$/)` after `Create run`, having navigated to
`/training-runs/{N}/cockpit` instead of `/training-runs/{N}`. The cause is a peer's uncommitted edit
landing **during** the run: `git diff -- app/Http/Controllers/TrainingRunController.php` shows that
session's in-flight cockpit cutover, which rewrites every `runs.show` redirect to `runs.cockpit`,
including `store()`'s:

```diff
-        return redirect()->route('runs.show', $run)->with('status', 'Run created.');
+        return redirect()->route('runs.cockpit', $run)->with('status', 'Run created.');
```

The change is uncommitted, so the running `php -S` served the old redirect for the first four cases and
the new one for the last four once the peer saved the file. That is the KI-70/KI-74 class — two sessions
on one checkout — and it is why one fixture produced two different failures inside one run.

**The second fix, also one line, also not applied.** The address has to survive that cutover, because
`HEAD` still redirects to `runs.show` while the peer's tree redirects to `runs.cockpit`:

```ts
await page.waitForURL(/\/training-runs\/\d+(\/cockpit)?$/, WRITE);
```

Recording it rather than hardening the fixture against a peer's uncommitted work is deliberate: the spec
should be corrected once the cutover lands, not fitted to a working tree that is mid-flight.

**Net result of the one authorized run.** 8 of 8 failed, exit 1, ~13.4 minutes of test time (about 15
with the harness's migrate-and-seed). No case reached its own assertions. Nothing here is a browser
pass, so D6 and D7 remain "Fixed (browser half unrun)", and Phase 3 stays closed.

## Schema

`2026_10_09_120000_add_ownership_to_deck_slots_table` is applied to the dev database
(`migrate:status` clean). It adds one nullable column and has a working `down()`. The test suite
builds its own schema from the migration files, so the migration is exercised on every run.

## Verification performed

- `vendor/bin/pest <focused files>` for every slice; the counts are in each commit message.
- `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` — clean after each slice.
- `npm run typecheck` — clean after the Vue changes (D1, D2, D3, D5, D6, D7).
- `vendor/bin/pint` on the changed files (not `--dirty`: the tree carries another session's
  uncommitted files and `--dirty` would reformat them).
- `php artisan migrate:status` on the dev database — clean.
- **Not run:** the full suite as one command, the browser suite, `composer lore` / `composer lore-code`,
  and `composer audit` / `npm audit` (no dependency changed).

## What remains unsupported from the source snapshot

Unchanged by this remediation: the run's own per-stat caps (1325/1322/1368/1308), Energy as a
band or qualitative state (a numeric Energy is stored, but a snapshot that states "near a third" has
nowhere to go and the run then reads `N/A` everywhere), the trainee's star rating and Potential Level, the four grandparents' Spark sets,
ancestor letter ranks (B+, UG), card levels and limit breaks, team identity and the Unity Cup
progression counts, the race-day card, and the mid-career position itself. Each needs the snapshot
architecture (Phase 3 onward) or a dedicated slice; none was faked.

## Close — final status (2026-10-09)

**D1–D9 final status.** D1 **Fixed** (browser half green: `career-inheritance-event.spec.ts` 8 of 8).
D2 **Fixed**. D3 **Fixed**. D4 **Fixed**. D5 **Fixed**. D6 **Fixed (rendered surface removed)**. D7
**Fixed (rendered surface removed)**. D8 **Verified, not a tool defect**. D9 **Fixed** — one shared
field source, all three surfaces wired and labelled alike.

**Phases and commits (flat list).** Phase 4: `0025f37`, `d0bd138` (4.1–4.2), `a31000c` (4.3). Phase 5:
`b890cd5` (5.1), `fc66978` (5.2), `8c7628d` (5.4). Phase 6: `31c9f5d`, `5876f64`, `f39ff03`. Phase 7:
`1098546`, `6fa0d01`. Phase 8.1: `1ec92ab`; its §11 package (ADR-0024 + digest + index) at `837d995`.
D6/D7 resolution: `e8a3dff`.

**Outstanding at close.**

- **Slices 4.4 and 5.3 are deferred.** Both read `careerPosition` / `scenarioCountdown` in the cockpit.
  `app/Http/Controllers/Career/CockpitController.php` carries an unauthored peer hunk (staged and
  unstaged, `MM`), so the two slices could not be landed without staging another session's work.
  `SnapshotCockpitTest` and `SnapshotScenarioCountdownTest` were not created for the same reason. The
  cockpit's YEAR/MONTH/TURN block and its countdown still derive from the run's turn number rather than
  the imported position.
- **D6 and D7's rendered assertions are not run**, because the surface they addressed (`Runs/Show.vue`
  and its `run-detail.spec.ts`) was deleted by the peer's cockpit cutover. The behaviour they guarded is
  carried by the cockpit and its `career-cockpit.spec.ts`, which was not re-run in this close (Option B
  in the D6/D7 resolution did not require it).
- **Deliberately out of scope**, per the task: new audits, session reports, `ADR-0019`/`ADR-0022`/
  `ADR-0023` edits, and any change to `resources/js/pages/Runs/Show.vue` or the `runs.show` redirect.

**Verification performed at close.** Focused Pest on every landed slice's file — 47 passed. Full
`vendor/bin/phpstan analyse` — clean. `npm run typecheck` — clean. `npm run build` — clean.
`php artisan migrate:status` on the dev database — no pending. `composer lore` — 302 hits, 77 exempt
(unchanged; no new hit from the close). `composer lore-code` — no hit from any file changed here.
`career-inheritance-event.spec.ts` — 8 of 8 passed. Not run: the full suite as one command, and
`composer audit` / `npm audit` (no dependency changed).
