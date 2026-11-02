# Follow-up closeout — Catalog Show fix and addendum Phases B–E

**Date:** 2026-11-03 (Asia/Kuala_Lumpur)
**Status:** Catalog Show landed with rendered evidence; addendum B–E landed or verified item by
item. Three closure items (the trainee-profile plan file, the Catalog Show KI, and the
`session_cost` comment) are recorded here rather than as separate edits, each with its reason in
"Deviations". No code work remains open from this dispatch; the owner-gated items are unchanged.

## 1. Catalog Show fix

`88043b3b` — *fix(catalog): render base stats and growth rates on trainee detail*.

- `CatalogController::show()` now sends `character_cards.base_stats` / `.growth_rates` per card
  plus `scenarios.stat_order`; `Catalog/Show.vue` reads the active form, prints the five base
  values and growth percentages, hides both absence notes when a row exists, and prints each
  costume form's recorded growth row instead of the retired "stores neither" claim.
- Rendered evidence: `/umamusume/vodka` shows **96/61/105/75/88** base stats and
  **10/0/20/0/0%** growth, 0 "Not recorded" in the base block, both absence notes gone, the
  Costumes growth column populated for both forms. (Vodka's own values; the dispatch's sample
  `90/89/83/92/96` is Tokai Teio's debut form.)
- JP-only null card: `/umamusume/mejiro-ryan` (card 66, `base_stats` null) renders
  "No base-stat figure is recorded for this form." and "No growth figure is recorded for this
  form.", no console error.
- 320px: `document.documentElement.scrollWidth` 305 (no overflow).
- `catalog-detail.spec.ts` gains a rounded-figures case that passes; the file's untouched
  single-form case fails pre-existing (the enriched scratch DB now holds more than one form for
  `silence-suzuka`).

## 2. Plan closure and the Catalog Show KI

**Trainee-profile plan.** The path named by the dispatch,
`docs/proposals/2026-11-02-trainee-profile-data-fix.md`, does not exist in this tree (listed
`docs/proposals/`, searched all `docs/**/*.md`). No file was created to hold a closure: the
dispatch limits documentation to closures, and inventing the plan in order to close it would be a
new artifact. The closure is therefore recorded here:

- Phase 1 wizard fix: `cf088ccb`.
- Phase 2 migration `2026_11_02_141639` is applied (migrate:status `[24] Ran`, reparse
  `266 updated, 0 created, 4 skipped`); the parser/model/store/controller changes are present in
  the shared working tree but have no commit of their own (the owning session has not committed
  them), so there is no hash to cite for that half.
- ADR-0030 is present; `tests/browser/trainee-profile.spec.ts` passes 3/3.
- Deferred Catalogue follow-up: `88043b3b` (this pass).

**Catalog Show KI.** HEAD's `KI-149` is the `.card > h2` type-step issue. The Catalog Show
entry — which reuses the number — lives inside the peer's uncommitted 273-line addition to
`KNOWN-ISSUES.md` (the file also carries a corrupted UTF-16 fragment in that block). Editing it in
place would require committing the peer's block, and no hunk of it applies against HEAD, so the
state was not flipped. Resolution for the record: the Catalog Show divergence is **RESOLVED by
`88043b3b`**; the peer should flip that entry when their block lands and renumber it (next free
number is KI-151).

## 3. Addendum B–E status

| Item | Status | Evidence |
|---|---|---|
| B.1 RaceDetail M2 audit | **FIXED** — `45ebbc37` | findings below |
| B.2 race-detail browser spec | **FIXED** — `64446e10` | 4/4 pass |
| B.3 null-column absence notes | **VERIFIED** (page already used `AbsenceValue`; covered in `45ebbc37`) | ids 3/1 render 29 absence notes, 0 dash cells |
| B.4 rewards display | **FIXED** — `45ebbc37`, option (b) | "Item id" + visible disclosure; no item dictionary exists, so no name invented |
| B.5 8 skipped races | **VERIFIED expected** — `6cbc52e4` | ids 1, 2, 405–410: all have null `track_id`/`race_id`/`fans_gain_curve` (debut, maiden, URA Finals); no join defect |
| C.1 Races.vue re-audit | **VERIFIED** (no commit) | F-01 5 sizes; F-03 410 = 410; F-06 payout SourceNote; F-11 44px; F-13 305@320 |
| D.1 missing gates | **RUN**; 4 new PHPStan errors fixed in `1c15a29a` | below |
| E.1 Rejected-candidate spot-check | **VERIFIED** (no revert) | 5/5 sampled propose names now in `umamusume` (JapanOnly promotions); 68 rejected: 66 null proposals, 2 valid ids, 0 missing trainees |
| E.2 `session_cost` citation | **ALREADY SATISFIED** (no edit) | `config/advisor.php:23-25` documents 2023-02-25 as the source's publication date and marks it stale; the requested "last verified" line would contradict that |

### RaceDetail audit findings

- **RD-01 rewards (NRM-1, sev 2, FIXED):** the table printed the raw item id with no disclosure.
  Now the column is "Item id" and the card states the catalogue records no name for them; the
  chance column prints percentages (100%/5%/1%) instead of raw fractions.
- **RD-02 raw reward JSON (H8, sev 2, FIXED):** the decoded facts table printed
  `reward_summary` as JSON. The page filters that row; the Rewards card is the only reward
  surface. (`RaceFacts` is peer-owned; the residual "Reward"/"Course" facts there are deferred.)
- **RD-03 machine codes (NRM-1, sev 1, FIXED):** direction/season printed code+label; course
  printed a bare code when its label was null. Labels print alone; a missing label prints
  "Code N" visibly as a code.
- **RD-04 factor-effect casing (H4, sev 1, FIXED):** "speed, guts" now reads "Speed, Guts"; an
  unrecognised source value is left verbatim.
- **RD-05 provenance (GP-2, sev 1, FIXED):** the page names the four enrichment datasets and
  links `/database/sources`; the door carries the 44px target.
- **RD-06 dead null-slot branch (hygiene, sev 0, FIXED):** the route `findOrFail()`s, so the
  "No race selected" branch was unreachable; removed.
- **RD-07 type scale / targets / contrast / reflow (VERIFIED):** `.card > h2` 16px; no in-main
  control under 44px except the documented 24px `AbsenceValue` markers; contrast light
  21 / 13.93 / 6.21 / 21, dark 14.35 / 13.99 / 4.83 / 12.63; scrollWidth 305/375/753/1265 at
  320/390/768/1280; focus ring 3px solid.

## 4. Gates

- `npm run typecheck` → exit 0.
- `npm run lint:ui` → exit 0; 207 finding lines (tool summary 509 across categories), equal to
  pass 5's 207 and below the 209 baseline.
- `npm run build` → exit 0 (manifest rebuilt); `public/hot` absent.
- `vendor/bin/pint --test` on every PHP file touched this pass → passed; `php -l` clean.
- `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` → 15 errors, **11 after
  `1c15a29a`**; the remaining 11 are pre-existing and outside the enrichment files
  (InheritanceEventController, PreflightController ×2, ScenarioSelectController,
  DashboardController, LegacyController ×2, TrainingRunController, IndependentTraining ×3).
  The four enrichment-parser contract errors are fixed by giving them their own
  `RaceEnrichmentSourceParser` contract.
- Parser feature tests: `GametoraRace{Fans,Parser,Reward,Racetrack}ParserTest` → 8 passed
  (29 assertions).
- `trainee-profile.spec.ts` + `career-snapshot.spec.ts` → 17 passed (3 + 14).
- `race-detail.spec.ts` → 4 passed.
- Full `npm run test:browser` → **NOT RUN TO COMPLETION.** The suite is 109 spec files at
  `workers: 1`; the run started 01:09 and had reached `career-cockpit.spec.ts` (alphabetical)
  at 01:31 when it was stopped, so a complete pass would run well over an hour. It is recorded as
  not passed, not as green.
- Targeted regression set instead — the specs this pass's source changes touch:
  `catalog.spec.ts`, `catalog-to-wizard.spec.ts`, `database.spec.ts`, `database-subpages.spec.ts`
  → **47 passed, 2 failed**. One failure was a real race-enrichment regression (the source
  ledger still expected 14 rows after four enrichment sources were added); fixed by `d6c5cd3d`
  and its case re-run green. The other (`catalog-to-wizard.spec.ts`) expects a `New career`
  button on the catalog page that an earlier pass renamed; it is pre-existing spec drift and was
  not changed here.

## 5. Remaining owner-gated items

Unchanged: proposal §7 Q2 (re-probe), Q4 (Phase 5 owner findings), Q5 (CatalogIndex schema
change). No new owner decision is needed for this dispatch.

## 6. Deviations and corrections

- **Dispatch paths:** the snapshot pages live under `Career/Snapshot/`, and the race routes are
  `/training-runs/{run}/races`, `/turns/{turn}/edit`, `/veteran`, `/database/races/{id}`. The
  synthetic URLs in the prior dispatch were not used.
- **"Create three specs":** all three files already existed; they were extended. `race-detail`
  was genuinely new.
- **KI and plan:** see §2. The plan file is absent; the Catalog KI is peer-uncommitted with a
  numbering collision, so neither got the prescribed commit. The resolution is recorded here.
- **`session_cost`:** already documented; the requested comment would have mis-stated the date's
  meaning.
- **RunsImport F-01:** fully closed — 0 form-field `ui/*` imports remain (only `PageHeader` and
  `Card` chrome).
- **Cockpit F-03:** removed identifiers grep to 0; the file's 7 pre-existing spec failures remain
  (proved at HEAD in the prior pass).
- **Landing untracked session files:** `RaceDetail.vue`, `EnrichRaces.php` and the four
  enrichment parsers were untracked from the enrichment session; they were landed here with the
  audit/citation/contract changes, each commit body saying so.
- **Full browser suite:** started and stopped as §4 records; the full-suite gate is therefore
  **not run**, and only the affected subset plus the named specs carry browser evidence.
- **`catalog-to-wizard.spec.ts`:** fails on a renamed catalog action (pre-existing); not fixed
  here because it is outside this pass's source changes and the dispatch's trap list owns its own
  set of pre-existing failures.