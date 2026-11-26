# Backend Handoff — D01 roster prop closure

**To:** Coordinator (Senior Project Manager), Fullstack (integration accountability)
**From:** Backend Architect
**Date:** 2026-11-26
**Closes:** D01 backend gap (`docs/plan/D01-frontend-handoff.md` §"Backend gap (one line)")
**Tree verified:** `b6e5120e` (branch `backend`)

## What was open

`CockpitController::teamEdit()` did not pass `roster`, so `Career/TeamEdit.vue` rendered
"Roster unavailable" while `runs.roster.store/update/destroy` routes already existed.

## What changed

Already landed in `9e87eab2`; re-verified this session, no new edit required.

```php
// app/Http/Controllers/Career/CockpitController.php:202
'roster' => $this->rosterSection($run),
```

`teamEdit()` also carries `'rosterMembers.umamusume', 'rosterMembers.supportCard'` in its
`loadMissing` (line 197), so the section does not lazy-load per row.

`rosterSection()` (lines 687-738) emits the exact prop shape the frontend handoff named:
`{members[{id,name,grade,speed,stamina,power,guts,wit,speed_cap,stamina_cap,power_cap,guts_cap,wit_cap,origin,support_card_id,aptitude_turf,aptitude_dirt,aptitude_sprint,aptitude_mile,aptitude_medium,aptitude_long,aptitude_front_runner,aptitude_pace_chaser,aptitude_late_surger,aptitude_end_closer,distance_assignment,is_ace,is_trainee,umamusume_id,position,update_url,destroy_url}], empty, unrecorded, store_url, max_members}`

No `rosterSectionWithUrls()` helper was added — `rosterSection()` already carries the URLs, so a
second method would have been duplication.

## Verification (executed this session, HEAD `b6e5120e`)

```
php artisan test tests/Feature/TeamEditTest.php tests/Feature/CockpitRosterTest.php --compact
  Tests:    9 passed (137 assertions)
  Duration: 15.88s
```

Covers: team-edit page loads for team-recording scenario, non-team scenario redirects with reason,
every editor-bound field carried, Team Rank letter round-trip through `runs.update`, invalid letter
refused, `S+` accepted, roster rows read from `roster_members` (not derived from deck), absence
naming, and per-row `update_url` / `destroy_url`.

D02 (`team_rank` absent from roster write path) unchanged and still covered by
`tests/Feature/RosterDoesNotTouchTeamRankTest.php`.

## Commit identities

- `9e87eab2` — `fix(database-safety): FS-INT-3 launcher sanitization + isolated-role protection`
  (contains the `teamEdit()` roster prop line)
- `b6e5120e` — `Merge branch 'master' into backend` (current verified tree)

Neither commit is on `master` (`8a9d4f8d`). `backend` is ahead of `master` by 10, behind by 0; ahead
of `origin/backend` by 5, behind by 26. Integration into master is Fullstack's call.

## Remaining dependencies

- Frontend may now delete the null-roster "unavailable" fallback path if it was only a bridge, or
  keep it for runs with no roster rows — that is a frontend decision, not a backend gap.
- Nothing else blocks D01.

## Blockers

None at the backend boundary. Release qualification is unaffected by this handoff and remains
blocked per `docs/sitrep/sitrep-2026-11-25-verification-failure.md` (comprehensive-run triage is
Fullstack's).
