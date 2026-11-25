# D01 frontend handoff — roster CRUD needs one backend line

## What the frontend ships

- `resources/js/components/career/UnityCupRoster.vue` (new): add/edit/remove roster members,
  posts `runs.roster.store/update/destroy`. Graceful "unavailable" state when no `roster` prop.
- `resources/js/pages/Career/TeamEdit.vue`: accepts optional `roster` prop, renders `UnityCupRoster`.
- `resources/js/pages/Career/Cockpit.vue`: roster read-out row (`Team roster` row: `N / 20` + ACE/trainee flags).

## Backend gap (one line)

`CockpitController::teamEdit()` does NOT pass `roster` to the page. The routes exist and the CRUD
component works, but the page receives `undefined` → renders "Roster unavailable".

## Required addition to `CockpitController::teamEdit()`

```php
return Inertia::render('Career/TeamEdit', [
    'run' => $this->runSection($run),
    'team' => $this->team->teamSection($run),
    'roster' => $this->rosterSectionWithUrls($run),  // ← ADD
    'backUrl' => route('runs.cockpit', $run),
    'scenarioDetailUrl' => route('runs.scenario-detail', $run),
]);
```

Where `rosterSectionWithUrls()` returns the existing `rosterSection($run)` PLUS write URLs per row:

```php
private function rosterSectionWithUrls(TrainingRun $run): array
{
    $section = $this->rosterSection($run);
    $section['store_url'] = route('runs.roster.store', $run);
    $section['max_members'] = RosterMember::MAX_MEMBERS;
    $section['members'] = array_map(function (array $m) use ($run) {
        $m['update_url'] = route('runs.roster.update', [$run, $m['id']]);
        $m['destroy_url'] = route('runs.roster.destroy', [$run, $m['id']]);
        return $m;
    }, $section['members']);
    return $section;
}
```

Or inline the URL enrichment in `teamEdit()`. Either works; the prop shape the frontend expects:

`{members: [{id, name, grade, speed, stamina, power, guts, wit, distance_assignment, is_ace, is_trainee, umamusume_id, position, update_url, destroy_url}], empty, unrecorded, store_url, max_members}`.

## D02 verified

`team_rank` is absent from `StoreRosterMemberRequest`, `RosterMemberController`, and `RosterMember`
model. `TeamEditTest` confirms Team Rank writes/reads still work. Roster edits cannot clobber rank.

## Verification

- vue-tsc: clean (2 pre-existing errors in AncestryNode.vue/Select.vue, not mine)
- vite build: green
- Feature suite: 75 cockpit + 6 TeamEdit + 23 UiHygiene tests pass
- Browser (career-trainee-select R3-31 keyboard): pre-existing failure on baseline, not a regression