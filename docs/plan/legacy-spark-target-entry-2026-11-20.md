# Legacy spark target entry: race-win and "+" sparks (2026-11-20)
<!-- PLAN-CONTRACT
plan: legacy-spark-target-entry-2026-11-20
status: drafted
slices: —
superseded-by: —
amended-by: —
open: no roadmap slice owns this work; owner directed the plan and the fix on 2026-11-20; executable premise re-check in step 0
-->

## Goal

Make the two spark classes the Legacy form could not accept enterable through **both** entry surfaces:
the wizard's `/career/setup/legacy` step and the run-scoped Legacy Lab builder. No payload or schema
change: `legacy_selection` already stores a Spark target as verbatim free text, and the write boundary
already leaves `Skill` targets unvalidated.

## Measured premise (from the 2026-11-20 verification pass; re-derive in step 0)

| Claim | Value | Provenance |
|---|---|---|
| Sparks in the guide's §1.5 tree | 57 = 6 blue + 6 pink + 6 green + 39 white | measured: parsed the section anchors in `docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md` |
| Successfully entered in the reported session | 38 = 18 structural + 20 white skill hints | measured: every one of those 20 has an exact-name hit in `database/seeders/data/skills.609afe88.json` |
| Unenterable | 19 = 18 race-win names + `Ignited Spirit: Guts +` | measured: 0 catalog hits for all 18 race names; catalog carries `Ignited Spirit GUTS`, not the `:`/`+` spelling |
| Category vocabulary | `Stat`, `Aptitude`, `Skill` only | static read: `app/Models/Legacy/LegacySelectionPayload.php:74`, `resources/js/pages/Career/LegacySelect.vue:608`, `resources/js/components/legacy/AncestorEditor.vue:67`, `resources/js/pages/Legacy/Builder.vue:156` |
| Write boundary | `Stat`/`Aptitude` checked; `Skill` (default) returned as free text | static read: `app/Http/Requests/StoreLegacySelectionRequest.php:221-229` |
| Race names in race catalogue | 12/18 near-matches, 6 spelling mismatches (`Japan C.`, `URA Finale`, `Asahi Hai F.S.`, `JBC L. Classic`, `NHK Mile C.`, `Sprinters S.`) | measured: `database/seeders/data/races.json` |

The mismatch is why a strict catalogue allowlist would reject the client's own spelling. Race targets
stay free text at the boundary, exactly as `Skill` does today.

## Scope

In: one shared vocabulary constant, one config key, the wizard controller's prop assembly, the three
Vue category lists (two entry surfaces plus the shared prop types), focused Pest tests, a `SCREEN_SPEC.md`
state note.

Out: no migration, no ADR (no stored shape changes), no new dependency, no computed inheritance value,
no change to `SPARK_KINDS` (both white families remain one display kind), no change to the saved form's
existing payload, no commit, no full browser sweep unless the focused suite demands it.

## Design decisions

- **D1 — add a fourth category `Race`.** It is the client's own competition-spark axis
  (`docs/UMAMUSUME_REFERENCE.md:708`, "White Sparks, competition") and keeps a race name out of a
  dropdown labelled `Skill`. The payload stores neither category, so this is a UI vocabulary only.
- **D2 — offer the guide's 18 exact race spellings**, plus the distinct titles already in the race
  catalogue for other runs. Exact client spelling wins over normalisation: this tool records what was
  read, never rewrites it (`ADR-0020` §3). Source the 18 strings in `config/uma.php` with a citation
  comment; do not read a doc at runtime.
- **D3 — add `Ignited Spirit: Guts +` to the `Skill` target list** as a sourced spark-only display
  name. It is the only such name this run needed; the other four `+` variants are a known ceiling
  (upgrade path: another line in the same config key), not part of this slice.
- **D4 — no validator change beyond the vocabulary list.** `Race` falls through the `default => null`
  branch like `Skill`, because abbreviations are client spellings and must be preserved verbatim.
- **D5 — do not touch the owner's open, unsaved page.** New options take effect after a rebuild and
  a fresh load of the route; the current unsaved values are not migrated or read from the browser.

**Dated revision 2026-11-20 (during implementation): D2 narrowed to the config list only.** The race
catalogue was measured at 322 rows / 304 distinct titles, but six of the guide's eighteen entries are
abbreviations the catalogue spells differently (`Japan C.`, `URA Finale`, `Asahi Hai F.S.`,
`JBC L. Classic`, `NHK Mile C.`, `Sprinters S.`). Offering catalogue titles would let a Trainer store a
name the client did not print, which breaks the record-only contract. The picker therefore offers the
fifteen distinct client strings from the source guide only. The removed half of D2 (catalogue titles)
is deliberately NOT implemented; the extension path is another sourced line in `config/uma.php`.
A second revision was needed after the first test run: an assertion that the seeded catalogue carries
`Ignited Spirit GUTS` was wrong because the `:memory:` test DB is unseeded; the case now pins the
configured entry's shape instead. Read-back recovery in both Vue surfaces was also added to the slice:
a stored race target would otherwise reopen as `Category=Skill`.

## Execution log (2026-11-20)

| Gate | Output |
|---|---|
| `php artisan test --compact tests/Feature/SparkTargetValidationTest.php` | first run 1 failed / 13 passed (bad catalogue assertion, corrected above); final **14 passed, 79 assertions** |
| `php artisan test --compact --filter=Legacy` | **83 passed, 1323 assertions** |
| `npm run typecheck` | exit 0, no output |
| `npm run lint:ui` | exit 0; 524 findings printed, all pre-existing (the only hit in an edited file is `LegacySelect.vue:473`, not a line this slice touched) |
| `vendor/bin/pint.bat --test --format agent <6 changed PHP paths>` | `{"tool":"pint","result":"passed"}` |
| `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` | `[OK] No errors` |
| `php tools/doc_census.py` via `php artisan test --filter=DocCitationParity` | **2 passed, 5 assertions** |
| `python tools/md_fence_check.py <this file>` | `0 invalid closing fence(s) in 1 file(s)` |
| `php tools/lore.php` / `php tools/lore.php code` | docs 629 hits / 75 exempt, code 368 hits (advisory). Zero new hits from this slice: every hit in a changed file is on a line this slice did not add. |

**Build, run after the owner reported the reload still showed three categories (2026-11-20).** The
source edits were intact and `npm run build` had never run; `public/build` was stamped 01:30, before
the edits, and `public/hot` is absent, so the page was reading the old bundle. `npm run typecheck`
was clean and `resources/js/pages/Help.vue`'s mtime had been stable for eight seconds, so the build
was safe to run: `npm run build` exited 0 (970 modules, 17.65 s). Verified in the emitted chunks:
`Stat","Aptitude","Skill","Race` is present in all three (`LegacySelect-BNEeYomh.js`,
`AncestorEditor.vue_vue_type_script_setup_true_lang-Cmyct4VA.js`, `Builder-bss4A7GL.js`). No
`bootstrap/cache/config.php`, so the config-sourced target lists are read live. A page reload alone
could not have produced the new options; a hard reload is now needed to drop the cached manifest.

**Not run:** full browser sweep and full Pest suite. This was a Level 1/2 change with focused and
related coverage, not a checkpoint.

**Open for the owner:** the open unsaved form is untouched. A hard reload will discard values
that were never saved. Saving the 38 entries with the current page first preserves them on the
server; after the hard reload the 19 missing entries can be added to the saved record.

## Acceptance criteria

1. A Trainer can select `Category=Race` and then click each of the fifteen distinct client race names
   that cover all eighteen entries in the guide; a save posts and round-trips the exact string under
   `kind=white`.
2. `Ignited Spirit: Guts +` is present in the `Category=Skill` target list and round-trips verbatim.
3. The existing 38 targets, the five spark kinds, rank fields and the `Not recorded` affinity option
   all still read back unchanged.
4. A hand-made POST with an unlisted Race or Skill target is still accepted (free text), and one with
   a bad `Stat`/`Aptitude` target is still refused.
5. Both entry surfaces show the same four categories and the same target lists.

## TODOs (ordered)

1. **Re-verify premise:** `git rev-parse HEAD`, `git diff HEAD -- <each path below>`, re-read the four
   category sites and the two config/controller sites. Record anything a peer changed; do not sweep it.
2. **Config:** add `legacy_spark_targets` to `config/uma.php` with the 18 race strings (source anchor)
   and the one spark-only skill string.
3. **Vocabulary:** add `Race` to `LegacySelectionPayload::SPARK_CATEGORIES`; leave the request's
   match arm on `default` and extend its docblock with the reason.
4. **Controller:** add `Race` to the `sparkTargets` prop in `LegacySelectController::show()`, merging
   the config list with `RaceCatalogSlot::query()->pluck('title')` (distinct, sorted); merge the
   configured spark-only name into the `Skill` list. Mirror the same prop assembly in
   `LegacyController::show()` if it builds `sparkTargets` separately — verify first, do not assume.
5. **Vue:** add `Race` to the three category lists and widen the `sparkTargets` prop types in
   `resources/js/pages/Career/LegacySelect.vue`, `resources/js/components/legacy/AncestorEditor.vue`,
   `resources/js/pages/Legacy/Builder.vue`. No `SparkTargetPicker.vue` change: it is already generic.
6. **Tests (failing first):** extend the focused feature coverage for the wizard and the builder to
   assert (a) `Race` in categories, (b) all 18 exact strings and the `+` name in `sparkTargets`,
   (c) verbatim round-trip of one race and the `+` spark through the write, (d) an off-list `Stat`
   still refused. Find the existing test files first; do not invent prop names.
7. **Docs:** add one dated row/note to the `SCR-CAR-008` and `SCR-CAR-006` state tables in
   `SCREEN_SPEC.md` naming the fourth category and the spark-only `Skill` entry. Stage only this hunk
   if the file carries peer edits.
8. **Gates:** targeted Pest -> `npm run typecheck` -> `npm run lint:ui` -> `vendor/bin/pint.bat --test
   --format agent <explicit paths>` -> `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`
   -> lore gate with a ruling per hit. Report unrun gates as not run.
9. **Hand-off:** quote the commands and their output, name changed paths, state what was not verified
   in a browser, and leave the tree uncommitted.

## Verification commands

```bash
git rev-parse HEAD
git diff HEAD -- config/uma.php app/Models/Legacy/LegacySelectionPayload.php \
  app/Http/Requests/StoreLegacySelectionRequest.php \
  app/Http/Controllers/Career/LegacySelectController.php app/Http/Controllers/LegacyController.php \
  resources/js/pages/Career/LegacySelect.vue resources/js/components/legacy/AncestorEditor.vue \
  resources/js/pages/Legacy/Builder.vue SCREEN_SPEC.md
php artisan test --compact --filter=Legacy
npm run typecheck
npm run lint:ui
vendor/bin/pint.bat --test --format agent <changed PHP paths>
vendor/bin/phpstan analyse --no-progress --memory-limit=1G
php tools/lore.php && php tools/lore.php code
```

## Risks / dependencies

- **Peer-dirty tree:** 82 porcelain rows at authoring; whole-file adds would sweep peer work. Name
  every path, stage hunks only, never commit without instruction.
- **Shared vocabulary:** three Vue copies plus one PHP constant must agree; a missed copy is a silent
  mismatch between the two surfaces. Step 1's grep is the guard; typecheck alone will not catch it.
- **Test file names:** write assertions only after reading the existing controller and its tests; a
  guessed Inertia prop name reads as a missing capability when it is only a wrong name.
- **Client spelling:** do not normalise the six mismatched race names; that would store a value the
  Trainer did not read and break the record-only contract.
- **Open page:** the owner's unsaved form is left alone; the fix is visible only after reload.
