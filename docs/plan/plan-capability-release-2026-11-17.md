# Plan 4 of 4 - Capability gaps and the release bar (phases 4-5)
<!-- PLAN-CONTRACT
plan: plan-capability-release-2026-11-17
status: landed
slices: —
superseded-by: —
amended-by: —
open: —
-->

**Pair:** `docs/roadmap/remaining-work-roadmap-2026-11-17.md` phases 4 and 5.
**Depends on:** plan 1 (phase 0), plan 2 (phase 2 - the browser sweep is release condition 2).
**Measured at:** `HEAD be8893a2`, 2026-11-17.

Phase 4 splits cleanly: two slices are PRD-allowed and small (4.1, 4.2), three are owner-gated and must not
be built on an agent's authority (4.3, 4.4, 4.5), one is a small recording gap (4.6).

---

# Slice 4.1 - Team Rank has readers but no writer

## Objective

Make the rank enterable. Today a column, a payload class and five readers exist, and no screen can write it.

## Measured

- Column: `database/migrations/2026_11_15_143000_add_team_rank_to_training_runs.php:33`
  (`string team_rank nullable`), with a model `saving` guard validating against `TeamRankPayload::LETTERS`.
- Readers: `app/Http/Controllers/Career/CockpitController.php:535`, `app/Http/Controllers/DashboardController.php:234`, `TrainingRun::latestTeamRank()` at
  `:1376` (column first, then the latest turn event).
- Vocabulary: `app/Models/TurnEvents/TeamRankPayload.php` `LETTERS = ['G','F','E','D','C','B','A','S','S+']`.
- **No writer**: `grep -rn team_rank app/Http/Requests/` -> no hits.
- The editor refuses by sentence: `resources/js/components/career/UnityCupEditor.vue:226` reads
  "Team Rank is recorded through a turn's team-race event, not through this editor." That path is
  unreachable from the UI - only tests write `deltas => ['rank' => ...]`.

## Steps

1. **Validator.** Add to `app/Http/Requests/StoreTrainingRunRequest.php`, beside the other `team_*` rules
   (`:85-96`):
   ```php
   // The rank is one stored current value (R6-48), so it arrives as a plain field, not a turn event.
   // `S+` is legal here and maps no facility level - `team_rank_ladder` deliberately has no entry for it.
   'team_rank' => ['nullable', 'string', Rule::in(\App\Models\TurnEvents\TeamRankPayload::LETTERS)],
   ```
   and a message beside the existing `team_stat_grades.*.in` message at `:281`:
   ```php
   'team_rank.in' => 'Team rank must be one of the league letters (G-S, or S+ for overall).',
   ```
2. **Form state.** `UnityCupEditor.vue`: add `team_rank: text(props.team.team_rank)` to the form object
   (beside `team_preseason_wins` at `:64`), a null-coalescing transform beside `:89`, and a select:
   ```html
   <label for="team_rank" class="flex flex-col gap-1">
       <span class="text-xs font-semibold text-ink-muted">Team Rank</span>
       <select id="team_rank" v-model="form.team_rank" name="team_rank" class="...">
           <option value="">Not stated</option>
           <option v-for="letter in rankLetters" :key="letter" :value="letter">{{ letter }}</option>
       </select>
   </label>
   ```
   with `const rankLetters = ['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S', 'S+'] as const;` declared once at the
   top of the script block, and the `:226` sentence **deleted** (it now describes a path the page replaces).
3. **Prop.** `CockpitController::teamEdit()` must pass the current rank to the page. Read `:311`'s
   `'team_url'` shape and add `team_rank` to the same `team` array the editor already receives.
4. **Feature test** (`tests/Feature/CockpitTeamPanelTest.php` or a new `TeamRankWriteTest.php`):
   ```php
   it('writes a team rank through runs.update and reads it back on both surfaces', function (): void {
       $run = TrainingRun::factory()->create();

       $this->put(route('runs.update', $run), [
           'umamusume_id' => $run->umamusume_id,
           'status' => $run->status->value,
           'team_rank' => 'S+',
       ])->assertRedirect();

       expect($run->fresh()->team_rank)->toBe('S+');
       $this->get(route('runs.cockpit', $run))->assertInertia(
           fn (Assert $page) => $page->where('identity.team_rank', 'S+'),
       );
   });

   it('refuses a letter the league board does not show', function (): void {
       $run = TrainingRun::factory()->create();
       $this->put(route('runs.update', $run), [
           'umamusume_id' => $run->umamusume_id,
           'status' => $run->status->value,
           'team_rank' => 'SS',
       ])->assertSessionHasErrors('team_rank');
   });
   ```
   Read the existing `StoreTrainingRunRequest::rules()` for the full required key set before writing the PUT
   body; `umamusume_id` and `status` are required (`RunSettings.vue`'s own comment at `:61-64` says the whole
   key set must be posted).
5. **Browser case** (`tests/browser/career-cockpit-forms.spec.ts` or the team-edit spec): set the rank,
   save, reload, assert the Cockpit strip and the Dashboard tile both print it.

## Files

`app/Http/Requests/StoreTrainingRunRequest.php`, `app/Http/Controllers/Career/CockpitController.php`,
`resources/js/components/career/UnityCupEditor.vue`, one feature test file, one browser spec.

## Acceptance

- The two feature cases above green.
- `S+` is accepted by the validator and refused a facility level by `team_rank_ladder` (read
  `config/scenarios.php`'s ladder to confirm it has no `S+` entry; if it does, that is a second defect).
- The Cockpit strip, the Team panel and the Dashboard tile all read the same value after a reload.
- `grep -n 'recorded through a turn' resources/js` -> 0 hits.

## Risk

This is the only slice here that writes Trainer data. The model `saving` guard already rejects an invalid
letter, so a bad request cannot persist; the Form Request rule makes the failure a validation error rather
than a 500.

---

# Slice 4.2 - Unity Cup declares no finale structure

## Objective

The scenario detail page prints "declares no structure beyond it" while the finale race is on the calendar,
because `config/scenarios.php`'s `unity_cup` block has no `finale` key.

## Measured

- `config/scenarios.php` `unity_cup` keys: `label, full_label, live_on_global, ruleset_version, cap_bonus,
  widgets, steps, training_extras, card_tags, panels, scenario_links, facility_level_source, goal_source,
  team_training_energy_penalty, wit_burst_energy_bonus, team_race` - **no `finale`**.
- The only declaring entry is `trackblazer`: `'finale' => ['kind' => 'points_league', 'races' => 3]` at `:1024`.
- `app/Services/Career/ScenarioSectionBuilder.php:152-156` documents that exactly (a corrected comment: it used
  to name `ura_finale`); `finaleAbsence()` returns the absence sentence.
- The commentary in the scenario corpus describes the finals as a set of races; **the capability matrix's
  phrase "five races" was not found in the config or the corpus by two independent reads in this pass.**

## Steps

1. **Find the sourced race count before writing a key.** Read `docs/scenarios/02-unity-cup.md` and
   `docs/research-scratch/SCENARIO-PUBLISHER-REFERENCES.md` for the finals description; grep both for
   `final`, `finale`, and the race names. If a count is stated, use it. If not, this slice becomes "record the
   terminal absence" and stops - do **not** invent a `races` number.
2. If sourced, add beside `team_race` in the `unity_cup` block:
   ```php
   /* The finals are a declared set of races on the career calendar, not a simulation (PRD §6.11 keeps
      outcome prediction cut). Count and source from docs/scenarios/02-unity-cup.md:<line>. */
   'finale' => ['kind' => 'race_set', 'races' => <N>],
   ```
3. Confirm `ScenarioSectionBuilder::finaleAbsence()` now returns null for `unity_cup` and the page prints the
   declared structure. Read `:281-298` for the branch it takes.
4. Feature test in `tests/Feature/ScenarioPanelTest.php`: `finale_structure` is non-null for `unity_cup` and
   carries the declared count.

## Files

`config/scenarios.php`, possibly `tests/Feature/ScenarioPanelTest.php`.

## Acceptance

Scenario detail prints the declared structure for Unity Cup; the test pins the key; no number appears without
a cited line in the comment.

## Explicitly not in scope

No finals UI, no bracket, no simulation. PRD §6.11 bans race-outcome computation, and recording a finale race
already has a path (race entry).

---

# Slice 4.3 / 4.4 / 4.5 - Owner-gated; record, do not build

These three are recorded so they are not re-proposed. **No code change until an ADR or a terminal ruling.**
Each is named here with its own id so the roadmap's row and this plan's section map one-to-one.

# Slice 4.3 - Team-race slots cannot exist

| Field | Detail |
| --- | --- |
| What a Trainer cannot do | Enter circles on a team race: `app/Http/Requests/StoreRaceEntryRequest.php:66` validates circles against a `team_race` slot, and no code creates one |
| Why it is gated | `database/seeders/ScenarioSlotSeeder.php:18` says "Unity Cup: zero rows (no citable source yet)"; `config/scenarios.php:482` describes the team-race shape (categories, field size, opponents) but no per-round slot rows exist |
| What the plan needs | A citable source for the round schedule, or a terminal ruling that the shape config is the whole record |

# Slice 4.4 - Roster is derived, not recorded

| Field | Detail |
| --- | --- |
| What a Trainer cannot do | Record who is on the team (the client shows 18 named vs 20/20) |
| Why it is gated | R8 §8.3 keeps the roster half DEFERRED; `CockpitController::rosterSection()` derives it from the deck and its own comment says there is no stored roster and no column |
| What the plan needs | An ADR for a sub-model (`team_members`) plus a write path |

# Slice 4.5 - Preseason rounds collapse to a count

| Field | Detail |
| --- | --- |
| What a Trainer cannot do | Record R1-R4 with dates instead of the single `Preseason wins` figure |
| Why it is gated | `database/migrations/2026_10_10_150000:31` states "count-only by design (D-220)" |
| What the plan needs | An ADR for `preseason_rounds` rows, or a ruling that the count is the product truth |

**What this plan does for them:** one dated note each in `KNOWN-ISSUES.md` (or the R8 register's disposition
column) naming the blocking decision, so a future session finds the gate instead of re-deriving it. That note
is the deliverable; anything else violates AGENTS.md §16.

---

# Slice 4.6 - Per-ancestor residuals

## Objective

The legacy editor ships rank, stars, spark kind/target/stars and add/remove. Two facts it cannot record:
a per-ancestor **guest** flag, and a per-ancestor **identity** (so two ancestors sharing a name are
distinguishable).

## Measured

- Editor fields: `resources/js/components/legacy/AncestorEditor.vue:126-218`.
- Validator: `app/Http/Requests/StoreLegacySelectionRequest.php:114-128`.
- Storage: `app/Models/Legacy/LegacySelectionPayload.php:273-291`; the payload already has an `ancestors_meta` key (`:52`, `:249`)
  that **nothing in `resources/js` reads**.
- `is_guest` exists at the **parent** level only (`resources/js/pages/Career/LegacySelect.vue:113`).
- PRD §6.3 permits recording: "Inheritance is recorded, not computed ... stored in
  `training_runs.legacy_selection`, and nothing more" - recording two more fields is inside that allowance,
  and nothing here computes a payout.

## Steps

1. Read `LegacySelectionPayload`'s ancestor shape and `ancestors_meta` usage. If `ancestors_meta` is the
   intended home, use it rather than adding a key.
2. Add two fields to the ancestor row shape: `guest: bool` and `label: string|null` (a distinguishing name the
   Trainer types, e.g. "Daiwa A").
3. Validate both in `StoreLegacySelectionRequest` (`guest` boolean, `label` max 255 nullable).
4. Render them in `AncestorEditor.vue` beside the existing star/rank inputs, following the field pattern the
   component already uses (a `<label>` plus an `<input>`/select, `min-h-11`).
5. Round-trip test: build a payload with two same-named ancestors distinguished by `label`, one flagged
   `guest`, and assert it survives save and reload.
6. Browser case on `/career/setup/legacy`: enter two ancestors, distinguish them, reload, assert both render.

## Files

`app/Models/Legacy/LegacySelectionPayload.php`, `app/Http/Requests/StoreLegacySelectionRequest.php`,
`resources/js/components/career/AncestorEditor.vue`, one feature test, one browser spec (or an added case to
`career-legacy-deck-steps.spec.ts`).

## Acceptance

Two same-named ancestors are distinguishable after a reload; the guest flag round-trips; no schema change
(the payload is JSON); `npm run typecheck` and the feature test green.

## Pitfall

The payload is a **compatibility payload** (`ADR-0010`). Adding keys is fine; renaming or removing an existing
one breaks a planner import. Read the class's own docblock before editing.

---

# Slice 5.1 - The seven release conditions

## Objective

Produce one quoted output per condition. A condition without its command output is not a condition.

| # | Condition | Command | Expected this pass |
| --- | --- | --- | --- |
| 1 | PHP suite green, skips explained | `php artisan test --compact < /dev/null` | 2455 passed / 37,540 assertions; the two `markTestSkipped` guards named (`tests/Feature/DesignTokensTest.php:651`, `tests/Feature/SkillAutomationTest.php:12`) |
| 2 | Browser suite green | plan 2 slice 2.6 | 0 failures after 2.1-2.3, or <=3 named rows |
| 3 | Style, static analysis, types, assets | `php vendor/bin/pint --test --format agent <paths>`; `php vendor/bin/phpstan analyse --no-progress --memory-limit=1G`; `npm run typecheck`; `npm run build` | all green (measured green today except `build`, which needs a fresh run) |
| 4 | WCAG AA token pairs | `php artisan test --compact --filter=DesignTokensTest < /dev/null`; plus the BO-004 pair measurement | 17 passed / 480 assertions; the dark risk pair re-measured, not quoted |
| 5 | Clean clone renders; offline artwork placeholder | fresh clone in `.scratch-uma/worktrees/`: `composer install`, `npm ci`, `npm run build`, `php artisan migrate --seed`, then load a card detail with an empty mirror | pending 0.2/0.3; the placeholder is `ArtworkSlot.vue` rendering nothing for a null url (DESIGN.md §4.7) |
| 6 | QWEN.md contradicts nothing | read `QWEN.md` against the tree; it already states CI exists and does not gate, artwork is manual, uploads are cut | green at `3cbbd8b9`; re-read if docs moved |
| 7 | Register names every terminal state | `python <skill>/scripts/audit_status_register.py KNOWN-ISSUES.md` | 0 contradictions after phase 1; every OPEN row carries a reason |

## Steps

1. Run each command; paste its summary line and exit code into the hand-off. Not a paraphrase.
2. Classify any red before re-running (regression / pre-existing / unrelated / infra / flaky).
3. For condition 4, re-measure the dark `--color-risk` pair (`#FF6F3B` on `#121212`):
   ```js
   // scratch script; contrast ratio via WCAG relative luminance
   ```
   Quote the ratio. BO-004 closes on a measurement, not on a note.
4. For condition 5, the clone must be created **inside the repo** (`.scratch-uma/worktrees/`), per
   AGENTS.md §12.

## Acceptance

Seven outputs, each with its exit code. An exception is named with the ruling that permits it. A skipped arm
is reported as skipped.

---

# Slice 5.2 - `VERSION.md` states two false things

## Measured

1. `VERSION.md:9` (Git tags row) says: "Two conditions still block the tag: `full-career-journey.spec.ts` is
   red and peer-owned (its locator matches two `details`; the fix is `details[data-region="team-state"]`)...
   " - but `grep -n 'details' tests/browser/full-career-journey.spec.ts` returns **one prose comment** and no
   such locator; the identity helper was rewritten to navigate to `/settings`. Two independent plans record it
   as green. **A tag is being held by a false premise.**
2. The same row says only `0.1.0` is tagged, while `VERSION.md`'s own table lists five tags and `git tag -l`
   prints `0.1.0 2.0.1 2.0.2 2.1.0 2.1.1`.
3. `:9` also duplicates a sentence ("Whoever cuts it updates this row and the Release commit row." twice).

## Steps

1. Re-measure before editing: `git tag -l`; run the journey spec alone session-private and quote the result.
2. Append a dated correction, do **not** rewrite the original sentence (AGENTS.md §11): a `Dated note
   2026-11-17` paragraph under the row recording what was measured and that the blocker is withdrawn.
3. Delete the duplicated sentence.
4. Update the `Release commit` row only when a commit actually exists.

## Files

`VERSION.md`.

## Acceptance

`git tag -l` quoted; the journey spec's own result quoted; no row states a condition the tree contradicts.
`python tools/md_fence_check.py VERSION.md` -> 0 invalid fences.

---

# Slice 5.3 - `CHANGELOG.md`: one Unreleased heading, and name the release content

## Measured

- Two `## [Unreleased]` headings: `:14` and `:464`.
- `grep -c 'S18' CHANGELOG.md` -> 0; `grep -c 'S1'` -> 0. Nothing tells a reader the snapshot pipeline or the
  S18 surface fixes are in 3.0.0.
- `## [3.0.0] - unreleased` at `:79` carries the HFW parity work.

## Steps

1. Read both Unreleased sections; fold the second into the first (it is the older one, and the file's own
   note says a newer entry goes on top). Merge only under headings that already exist there.
2. Under `## [3.0.0] - unreleased`, add the two entries with their commits:
   ```markdown
   ### Added
   - **Snapshot pipeline** (S1): a career already in progress is recorded from its current position, with
     trainee rarity, Potential Level and the client's full scenario label; the run's owned skills are added
     afterwards on its Skills planner (PR #27, `0bb3efd0`).
   ### Fixed
   - **Scenario-flow surfaces** (S18): the settings lede no longer prints a route as prose, the wide-layout
     sentence is conditional on the flag, the catalogue card stops claiming growth is absent, Help describes
     the Undo control that exists, the docking card reads the same position the cockpit does, and the strip
     plans the same turn the screens it links to use.
   ```
3. Keep the keep-a-changelog form; do not date it (the tag dates it).

## Files

`CHANGELOG.md`.

## Acceptance

`grep -c '^## \[Unreleased\]' CHANGELOG.md` -> 1; the S1 and S18 entries present under `3.0.0`; fences clean.

---

# Slice 5.4 - The tag (owner act)

## Objective

Present the evidence and let the owner cut it. **The agent does not tag and does not push a tag.**

## Steps

1. Assemble: the seven condition outputs (5.1), the `VERSION.md` correction (5.2), the changelog entries (5.3),
   and `git log --oneline origin/master..<release-branch>` showing exactly what the tag would publish.
2. State plainly which commit the tag should land on: the tip of the landed branch **after** it is merged to
   `origin/master`, never a local commit that has not passed the gates.
3. Ask the owner to cut, or to record why the release waits.
4. After the owner tags, verify: `git tag -l 'v3*'`; `gh release view v3.0.0`;
   `gh run list --workflow=release.yml --limit 1`.

## Acceptance

`v3.0.0` exists on a commit reachable from `origin/master`, or a written decision records the wait. The agent
never runs `git tag` or `git push --tags`.

---

## Gate sequence for this plan

```bash
php artisan test --compact --filter='TeamRankWrite|CockpitTeamPanel|ScenarioPanel|LegacySelection|AncestorRank' < /dev/null
php vendor/bin/pint --test --format agent app/Http/Requests/StoreTrainingRunRequest.php app/Http/Controllers/Career/CockpitController.php config/scenarios.php
php vendor/bin/phpstan analyse --no-progress --memory-limit=1G
npm run typecheck
npm run build
node tools/ui-framework-check.mjs
php tools/lore.php; php tools/lore.php code
python tools/md_fence_check.py VERSION.md CHANGELOG.md
composer audit --no-dev; npm audit --omit=dev
```

Browser cases, session-private:

```bash
PLAYWRIGHT_PORT=8324 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<s>/b8324.sqlite \
  npx playwright test tests/browser/career-cockpit-forms.spec.ts tests/browser/career-legacy-deck-steps.spec.ts \
  --workers=1 --reporter=line
```
