# Trainer Desk — Implementation Plan (2026-11-17)
<!-- PLAN-CONTRACT
plan: implementation-plan-2026-11-17
status: landed
slices: —
superseded-by: —
amended-by: —
open: —
-->

Paired with `docs/roadmap/implementation-roadmap-2026-11-17.md` (what/why). This file is **how**, with exact
commands, per slice. Measured at HEAD `248e1cbb`, migrations 0 pending.

---

## Global constraints

- Laravel 13 / PHP 8.5, Pest 4, Inertia + Vue 3 + TS, Tailwind v4. SQLite only, loopback only.
- Design tokens only; no `dark:`; Global client vocabulary (`lang/en/uma.php`); `N/A` with title, never a bare dash.
- No new package without approval; no `pint --dirty`; one Pest process at a time; browser runs session-private.
- Every write path is a Form Request; logic in Actions/Services; named routes; `config()` not `env()` in app code.
- Stage explicit paths only; never commit/stage without the owner's instruction.

---

## S1.1 — Rename run-rail Skills control (E-01)

**Files:** `resources/js/pages/Career/Cockpit.vue` (nav builder ~line 666).

1. In the nav links array, change the run-rail entry's label to `Skills planner` (it already reads
   `label: 'Skills planner'` — verify the rendered control's accessible name, not the array literal) or add
   `aria-label: 'Skills planner'` if the visible text must stay `Skills`.
2. Probe first: read the rendered DOM of the cockpit nav to see which element carries the duplicate name.
   `grep -n "Skills" resources/js/pages/Career/Cockpit.vue` — the collision is the sidebar global `Skills`
   (`/skills`) vs the run-rail `Skills` (`/training-runs/{id}/skills`).
3. Feature test (new or extended): `php artisan test --filter=CockpitNavTest` — assert the run-rail link's
   accessible name is unique on the page.
4. Verification: `npm run typecheck`; browser spot-run
   `PLAYWRIGHT_PORT=8191 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-8191.sqlite npx playwright test tests/browser/career-cockpit.spec.ts --workers=1` (spec name verify with `ls tests/browser | grep cockpit`).

## S1.2 — Link the snapshot door (E-02)

**Files:** `resources/js/pages/Dashboard.vue`, `resources/js/pages/Runs/Index.vue`.

1. Add one link each: `Start from existing snapshot` → `route('career.snapshot')` (named route exists,
   `routes/web.php:109`).
2. Placement: Dashboard quick-actions group; Careers list next to `Import a run`.
3. Feature test asserting both surfaces carry the href; browser assertion that the link navigates.
4. Verification: `php artisan test --filter=DashboardTest`, `npm run typecheck`.

## S1.3 — Settings ceiling labels (E-03)

**Files:** `resources/js/pages/Career/RunSettings.vue`.

1. Replace the four `Scenario 1300` labels (and the Wit `Scenario 1800`) with
   `Run ceiling (scenario floor N)` — one string, five inputs.
2. Feature test: label text asserted; cockpit strip `floor`/`cap` display unchanged (separate component).
3. Verification: `php artisan test --filter=RunSettings`, `npm run typecheck`.

## S1.4 — Snapshot rarity/potential + full scenario label (U-03)

**Files:** `resources/js/pages/Career/Snapshot/Setup.vue`, `Review.vue`, `app/Http/Controllers/SnapshotController.php`
(+ Form Request), scenario label map.

1. **Verify first:** does `training_runs` carry rarity/potential columns? `grep -n "rarity\|potential" database/migrations/*.php`.
   If the settings screen persists them via existing columns, the snapshot form needs no migration — reuse them.
2. Add `Rarity` (segmented ★1–★3, default Not stated) + `Potential Level` (number) to Setup; mirror on Review
   (`Known · Rarity ★3` etc.).
3. Full scenario label: render `Unity Cup: Shine On, Team Spirit!` from the scenario config block
   (`config/scenarios.php:416` carries `unity_cup`; check for an existing `label` key before adding one — do not
   invent a config key). If the label exists in `lang/en/uma.php` terms map, use it.
4. Feature test: snapshot round-trip carries ★3/2; review lists them Known.
5. Verification: `php artisan test --filter=Snapshot`, `npm run typecheck`, migration only if step 1 requires it
   (+ digest update + ADR if schema changes).

## S1.5 — Fan criteria on goal rows (U-05b)

**Files:** Cockpit goals board component (locate via `grep -rn "Trainee goals" resources/js`).

1. Each goal row gains the race's fan criterion beside the state, sourced from the calendar row the board
   already resolves (the race screen prints `FANS NEEDED 2000`; reuse that source, don't re-derive).
2. Feature test: Nikkei Sho row shows its threshold.
3. Verification: `php artisan test --filter=Cockpit`, `npm run typecheck`.

## S1.6 — Owned-skills editor at snapshot (U-02)

**Files:** `resources/js/pages/Career/SkillsPlanner.vue` (add-skill writer exists per commit `9cc21b8f`),
snapshot flow hand-off, `routes/web.php:238` (`runs.skills.sync`).

1. **Verify first:** read the add-skill writer shipped by `9cc21b8f` — if it already writes acquired skills
   through the UI, the remaining gap is only the post-create hand-off (cockpit "Where to go" already links the
   planner; a one-line pointer + copy may be the whole slice).
2. If an editor is needed: owned-skill add path sets `acquired` status by default for snapshot entries;
   hint level defaults per source.
3. Feature test: create snapshot run → add 11 named skills → planner lists 11 recorded (aligning with the
   capability matrix's "add via API, edit via UI" gap).
4. Verification: `php artisan test --filter=Skills`, `npm run typecheck`; browser spot-run on the planner.

## S1.7 — Card level per deck slot (U-07)

**Files:** `resources/js/pages/Support/Builder.vue` (slot panel), deck save path,
`database/migrations/` (only if no column exists).

1. **Verify first:** `grep -n "level" app/Models/TrainingRunDeckSlot.php` (or the actual slot model — locate via
   `grep -rn "deck_slots\|SupportSlot" database/migrations app/Models`). If a level column exists, this is
   UI-only; if not, migration + digest + ADR per §11.
2. Add numeric `Level` input to each slot panel (1–50 bound by SSR break caps per reference §1.4.2); persist per
   slot; deck analysis unaffected.
3. Feature test: set 35/30/30/50/30/35, save, reload, values persist.
4. Verification: `php artisan test --filter=Deck`, `npm run typecheck`; migration verified on scratch DB if added.

---

## S2 — KI register truth (measured denominator: 108 index rows, 9 OPEN)

Per-entry probe (one command each, e.g. KI-160 → viewport probe at 640px on the Cockpit; KI-145 →
`node tools/ui-framework-check.mjs` count vs baseline). Then:

- **FIX bucket:** one commit per entry + failing-first test. Known targets: KI-137/KI-136
  (`full-career-journey.spec.ts` locator → `details[data-region="team-state"]`, **peer-owned — coordinate**),
  KI-141/KI-159 (`title=`-only reasons → visible text), KI-160 (`NextDecision.vue` overflow), KI-145
  (arbitrary-value debt), KI-143/KI-147 (spec-vs-render mismatches).
- **TERMINAL bucket:** record classification in the entry body (e.g. KI-150 page-level sticky — CONFIRMED-terminal
  per `AppLayout.vue` container-query constraint).
- **ALREADY-FIXED bucket:** verify + close with the probe command quoted.

Acceptance (amended 2026-11-17): every index row carries a current-state verdict; the count is read from the index, not from a body-pattern grep. Measured at S2 close: 108 rows, 9 OPEN, each with a stated reason, zero unaccounted index/body contradictions.

---

## S3 — Browser suite truth

1. Triage per spec: grep the asserted literal against `resources/js` (absent → stale; present → re-run isolated).
2. Isolated re-runs, one process:
   ```bash
   PLAYWRIGHT_PORT=8191 \
   PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-8191.sqlite \
   npx playwright test tests/browser/<spec>.spec.ts --workers=1 --reporter=line
   ```
3. `full-career-journey.spec.ts`: apply the `details[data-region="team-state"]` locator fix (coordinate with peer).
4. Full sweep on the session-private harness; classify any residual per roadmap S3.

---

## S4 — Release bar commands

```bash
php vendor/bin/pest --compact                     # condition 1
npx playwright test                               # condition 2 (session-private env vars)
vendor/bin/pint --test --format agent <paths>     # condition 3 (never --dirty)
vendor/bin/phpstan analyse --no-progress --memory-limit=1G
npm run typecheck && npm run build
php vendor/bin/pest --filter=DesignTokensTest     # condition 4
composer setup                                    # condition 5 (scratch copy, never the dev file)
```

Condition 6 (QWEN.md read) and 7 (register parse) are read-throughs; 7 closes with S2 (108 rows, 9 OPEN).

---

## S5 — Version truth

Update `VERSION.md` rows (tag state, release-commit row) only from executed gate output; present the evidence
bundle to the owner. **The owner cuts the tag; the agent does not.**

---

## S6 — Scenario walks

Trackblazer → Our Grand Concert → URA Finale (control). One line per observable event (what happened / intent /
expected vs observed / evidence); defects to `KNOWN-ISSUES.md`; walks and code changes never share a turn. Use
the Hermes `browser_exec` substrate with URL-verification per click (banked in `trainer-desk-ui-audit`).

## S7 — Owner questions

U-01 (per-ancestor sparks), U-06 (rounds-as-rows), U-04 (roster/Team Rank), November event + finals UI
boundary — presented as the R8 register's DEFERRED rows; each gets an ADR or a terminal ruling when the owner
answers.
