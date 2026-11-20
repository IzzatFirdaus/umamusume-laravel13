# Career setup wizard: legacy manual input — findings

## Issue

Agents testing the career setup wizard (manual input mode) reported that legacy parent selection always chooses an existing veteran from the library instead of accepting manually entered parent information. No auto-selection defect exists in code.

## Conclusion: no code defect

Complete trace of the legacy selection chain found no auto-pick, no default veteran, and no forced choice of any existing veteran in any reviewed file:

- `resources/js/pages/Career/LegacySelect.vue` — `__manual__` sentinel option appended unconditionally by `pickOptions()` (after `Not chosen` and all roster veterans). Picking it sets `manual[index] = true`, reveals `BaseInput` name field ("Parent name"), and `cardFor()` prints "Chosen" + typed name. Line 973 states "no screen invents default pair". Cold-load restores stored payloads only; never invents a pair.
- `resources/js/components/Form/BaseSelect.vue` — picker component; `__manual__` passes through as a normal option; no default selection logic.
- `app\Http\Controllers\Career\LegacySelectController.php`, `app\Services\Career\SetupDraft.php`, `app\Http\Requests\Career\StoreDraftLegacyRequest.php`, `app\Http\Requests\StoreLegacySelectionRequest.php` — accept `legacy_id: null` plus `name` string for manual entries.
- `resources/js/pages/Career/TraineeSelect.vue` — trainee/Umamusume selection only; not legacy parents. Ruled out.
- `resources/js/pages/Career/BuildTarget.vue` — stat targets, distance, surface, style; no veteran selection logic.
- `resources/js/pages/Career/SaveVeteran.vue` — read-only page; not involved in selection.

## Default page state

On load, neither parent is selected. The picker opens on `Not chosen` (value `''`), not on a veteran. Manual mode is opt-in per parent.

## Manual input flow (correct, end-to-end)

1. Open Parent or Parent B veteran picker in `LegacySelect.vue` (wizard step 4).
2. Pick **"Not in library - enter name"** (`__manual__`) — bottom of list, after all roster veterans.
3. Type parent name in the revealed **Parent name** `BaseInput`.
4. Card shows **"Chosen"** + typed name; manual mode active for that parent.
5. Save posts `legacy_id: null` with `name` string; backend validates and persists.

## Recommendation

This is a test/harness data issue, not an application defect. If manual input still resolves to an existing veteran, inspect:

- Test data / fixtures seeding veteran rows and asserting against them instead of manual entries.
- Cold-load path (`docs/research/flows-career-creation.md:68`) restoring a previously stored draft — verify stored payload carries `name` not `legacy_id`.
- Any harness/wrapper calling the wizard that pre-selects a veteran outside `LegacySelect.vue`.

## Verification

- `git status --short` — working tree clean of this finding (no code change warranted).
- Full `npx vitest run` timed out at 120s (exit 124); `vitest legacy` exit 0 but executed a worktree's tests, not this project's. Targeted run not completed for this finding; no test was found to assert forced veteran selection.
