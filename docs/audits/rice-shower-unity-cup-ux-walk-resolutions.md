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
| D1 inheritance HTTP 500 | **Fixed** | 1.1 | `291f6ea` | `CareerInheritanceEventTest` (14 cases) |
| D2 blank trailing Spark row | **Fixed** | 1.2 | `a4b2b26` | `CareerLegacyDeckStepsTest` (1 new case) |
| D3 rented/friend flag not persisted | **Fixed** | 1.3 | `fdebcc9` | `RunDeckTest`, `SupportDeckBuilderTest`, `CareerPreflightTest` |
| D4 target validation contradicts the UI | **Fixed** | 2.1 | `be02297` | `CareerBuildTargetTest` (3 new cases) |
| D5 trainee stored-choice readback `N/A` | **Fixed** | 2.2 | `dd6dd22` | `CareerTraineeSelectTest` (1 new case) |
| D6 turn table hides Fans/Energy | **Fixed** (browser half unrun) | 2.3 | `89e8453` | `TurnRowActionsTest` (2 new cases) |
| D7 wizard step numbering skips 3 | **Fixed** (browser half unrun) | 2.4 | `6d53ffc` | `GuidedTurnOnRunViewTest` (1 new case) |
| D8 Rice Shower aptitude mismatch | **Verified, not a tool defect** | 2.5 | `be48e60` | `CareerTraineeSelectTest` (1 new case) |
| D9 duplicate turn-entry paths | **Diagnosed; not implemented** | 2.6 | — | diagnosis below |
| Career position / snapshot mode | Not started (Phase 3+) | — | — | — |
| Legacy disambiguation, ancestor rank, grandparent sparks | Not started (Phase 6) | — | — | — |
| Scenario metadata, typed spark targets | Not started (Phase 7) | — | — | — |
| Energy model (exact/band/unknown) | Not started (Phase 8) | — | — | — |

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

### D5 — trainee readback: Fixed

The step's `Stored choice:` readout derived the name from `trainees.data`, the paginated and filtered
roster, so a Trainer who searched and selected saw `N/A` beside a flash confirming the selection. The
name now travels with the stored selection.

### D6 — turn table Fans/Energy: Fixed, browser half unrun

The payload already carried `energy` and `fans`; only the table lacked the columns. Both are now
printed, and a turn that recorded neither names its own absence (`N/A` with a `title`). The read path
and the columns' presence are pinned by feature tests; the rendered-DOM assertion in
`run-detail.spec.ts` was **not executed** (see the infrastructure note).

### D7 — wizard step numbering: Fixed, browser half unrun

The indicator counted `current`'s position inside `def.steps`, the scenario's turn vocabulary (Unity
Cup's five include a facility and a team-race step the rail never lands on), so it read "Step 2 of 5"
then "Step 4 of 5". The rail has two stages; the server now sends `flow` and the indicator and the
progress pips count those. The rendered assertion in `run-detail.spec.ts` was **not executed**.

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

What is left of the finding is **presentational**: the two forms render the same fields under
different labels (`Speed` on the run record, `Speed total *` on the decision screen), so a Trainer
meets the same form twice and has to work out which one to submit. Unifying that means one shared
field-definition component used by both surfaces, and naming on each whether it creates or edits a
turn. That is a real refactor and it was not started, because a half-migrated pair of forms is worse
than the duplication it would remove.

**Remaining, explicitly.** One shared turn-entry field definition used by both surfaces; the label
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

**No browser claim in this record is a pass, and Phase 3 must not open until one is.** D6 and D7 stay
"Fixed (browser half unrun)"; their assertions in `run-detail.spec.ts` remain written and unexecuted.

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
