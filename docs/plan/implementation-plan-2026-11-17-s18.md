# Trainer Desk — Scenario-Flow Surface Implementation Plan (2026-11-17, session S18)

Sibling roadmap: `docs/roadmap/implementation-roadmap-2026-11-17-s18.md` (states **what** and **why**).
This plan states **how**, per slice: exact files, probe-first step, failing-first assertion, the concrete
change, acceptance, and the verification command.

**Goal:** make every rendered sentence these surfaces print agree with the value the payload actually carries,
and make every "where is this run" surface read one owner.

**Measured baseline:** HEAD `be8893a2`, 80 migrations 0 pending, bundle rebuilt 2026-11-17 13:53.

---

## Global constraints

- PHP 8.5 / Laravel 13 / Pest 4. `declare(strict_types=1)` on every PHP file touched.
- Design tokens only; no `dark:`; no new arbitrary Tailwind values (baseline `count: 209`).
- Unrecorded value renders `N/A` with `title`, never `0` or a bare dash.
- Logic in `app/Services`/`app/Actions`/controller helpers, never in Blade; Form Request on every write path.
- No commit, push or tag in this session. Stage nothing.
- One Pest process at a time. `phpunit.xml` forces `:memory:`.
- Never touch `career-preflight.spec.ts`. `full-career-journey.spec.ts` is peer-owned: not edited here.
- Do not "fix" confirmed guards: catalogue-side aptitudes, no settled November payout date, no burst-band
  placement for a disputed count, finals venue/distance stays UNVERIFIED, card level independent of limit
  break, unique-skill level ≠ hint discount, Potential Level mirrors `between:1,5`.

---

## Slice A1 — Settings lede must not print a URL

**Files:** Modify `resources/js/pages/Career/RunSettings.vue:62`.
**Test:** Modify `tests/Feature/RunSettingsIdentityTest.php` (exists; add one case) — or the nearest existing
RunSettings feature test if that name differs (probe first).

- [ ] **Step 1 — Probe.** `grep -n 'update_url' resources/js/pages/Career/RunSettings.vue | head`; confirm the
      prop is the update route and that the lede is the only place it is printed as text.
- [ ] **Step 2 — Failing assertion.** In the RunSettings feature test assert
      `assertDontSee('http://', false)` on the rendered page props' lede string, or assert the lede equals the
      new sentence. Expected: FAIL on the current string.
- [ ] **Step 3 — Change.** Replace
      `` `The identity fields the Cockpit used to hold. Everything here writes through ${props.run.update_url}.` ``
      with a sentence naming the action, e.g.
      `The identity fields the Cockpit used to hold. Every change saves through this run's own update route.`
      Keep `props.run.update_url` bound to the form's submit (it is the writer; only the prose changes).
- [ ] **Step 4 — Verify.** `php artisan test --compact --filter=RunSettings` → PASS; `npm run typecheck` → clean.
- [ ] **Step 5 — Bundle.** `npm run build`; then L4 grep: `grep -ril 'writes through' public/build` must no
      longer match `RunSettings-*.js`.

**Acceptance:** rendered lede carries no `http://`; `admin`/loopback host never appears in shipped copy.

---

## Slice A2 — DL.14 sentence must be conditional

**Files:** Modify `resources/js/pages/Preferences/Edit.vue:384`.
**Test:** Modify `tests/Feature/PreferenceControlsTest.php` (has `wideBentoEnabled` cases at `:452-535`).

- [ ] **Step 1 — Probe.** `grep -n 'held behind DL.14' resources/js/pages/Preferences/Edit.vue`; confirm no
      `v-if` guard and that `props.wideBentoEnabled` is already a declared prop.
- [ ] **Step 2 — Failing assertion.** Add a case that renders the page with the flag **true** and asserts the
      DL.14 sentence is absent, plus a case with the flag false asserting it is present.
- [ ] **Step 3 — Change.** Wrap the paragraph in `v-if="!props.wideBentoEnabled"`. Do not change the Note below
      it (the ratified-widths explanation is true in both states).
- [ ] **Step 4 — Verify.** `php artisan test --compact --filter=PreferenceControls` → PASS; `npm run typecheck`.
- [ ] **Step 5 — Bundle.** `npm run build`; `grep -ril 'held behind DL.14' public/build` still matches
      `Edit-*.js` (the string ships, guarded) — record that the grep is not the test.

**Acceptance:** flag-on renders no DL.14 claim; flag-off still explains why Wide is unavailable.

---

## Slice A3 — Catalog card must stop claiming growth is absent

**Files:** Modify `resources/js/components/catalog/TraineeCatalog.vue:375-379`; modify
`app/Http/Controllers/CatalogController.php:196-206` (index row map) **only if** the payload must carry the
figure.

**Probe first.** The false sentence is a claim about the whole catalogue, so all four layers matter:
L1 `character_cards.growth_rates` (present, 263/263), L2 index map (omits it), L3 the component (prints
absence and is **truncated mid-sentence**: `… growth row for an`), L4 shipped bundle (`TraineeCatalog.vue_…js`
matched). Decide between two honest repairs:

- **Option 1 (smallest, copy-only):** state the truth the index can support — growth rows are stored per card
  and read on the trainee's own form page. No payload change.
- **Option 2 (payload):** add `growth_rates` to the index row map and print the five percentages on the card.

Take **Option 1** unless the card visibly needs the numbers (it already links to the detail page which renders
them). If Option 1 is taken, also fix the truncation.

- [ ] **Step 1 — Failing assertion.** Extend `tests/browser/catalog.spec.ts:104-106` region (it currently
      asserts only that the word `Growth` is present) with the new sentence, or add a feature test asserting the
      rendered card copy if a rendered-copy test exists for this page. Expected: FAIL.
- [ ] **Step 2 — Change.** Replace the truncated paragraph with one complete sentence that names where the
      figure lives (the form's own page), and keep `Growth` as the leading word so the existing spec still
      finds it.
- [ ] **Step 3 — Verify.** `php artisan test --compact --filter=Catalog` → PASS; `npm run typecheck`;
      `npm run lint:ui` (no new arbitrary values).
- [ ] **Step 4 — Bundle.** `npm run build`; `grep -ril 'holds no growth row' public/build` → no match.

**Acceptance:** no shipped surface claims the catalogue stores no growth row; the absence sentence is a
complete sentence.

---

## Slice A4 — Help must describe the Undo control that exists

**Files:** Modify `resources/js/pages/Help.vue:118-124`; modify `tests/browser/help.spec.ts:54`.

- [ ] **Step 1 — Probe.** `grep -rn 'Undo turn' resources/js/pages/Career/*.vue` (five hits, all `disabled`)
      and `grep -n 'destroyTurn' routes/web.php` (`DELETE … turns/{turn}` live).
- [ ] **Step 2 — Failing assertion.** Change the spec's expected text to the new sentence. Expected: FAIL
      before the copy moves.
- [ ] **Step 3 — Change.** Rewrite the paragraph to: the control is drawn but **disabled** in this build, the
      correction path is the screen that owns the value, and the delete route exists for a later enablement.
      Keep it short; no mechanism claim beyond what is measured.
- [ ] **Step 4 — Verify.** `npm run typecheck`; if the browser spec is not run this session, say so plainly in
      the SITREP (copy-only change, spec updated but **not executed**).
- [ ] **Step 5 — Bundle.** `npm run build`; `grep -ril 'no Undo last turn' public/build` → no match.

**Acceptance:** the Help paragraph is true of the shipped build; the spec names the new sentence.

---

## Slice B1 — Dashboard resume card must print the run's own standing

**Files:** Modify `app/Http/Controllers/DashboardController.php:184-209` (`activeCareer()`);
Modify `resources/js/pages/Dashboard.vue:328-333` only if the payload key changes.
**Test:** Modify `tests/Feature/DashboardTest.php` (add a snapshot-position case beside the existing
`position_label` assertions at `:121` and `:216`).

- [ ] **Step 1 — Probe.** Confirm `activeCareer()` computes `position_label` from `$strip['turn']` (recorded
      count) while `position_turn` comes from `decisionTurn()`. Rendered evidence: `/` prints
      `Junior Year · Early January` with `imported at turn 67`; `/training-runs/36/cockpit` prints
      `Senior Year · Early October`.
- [ ] **Step 2 — Failing assertion.** Add a case: a run with `career_position = CareerPosition::fromTurnIndex(67)`
      and exactly one recorded turn must read
      `position_label === 'Senior Year · Early October'` **and** `turn === 1` **and** `position_turn === 67`.
      Expected: FAIL (current label is `Junior Year · Early January`).
- [ ] **Step 3 — Change.** Derive `position_label` from `decisionTurn()` (the same owner the Cockpit uses),
      keeping `turn` as the recorded-count figure and `position_turn` as the decision turn. If `decisionTurn()`
      is null, fall back to the recorded-count label exactly as today.
- [ ] **Step 4 — Verify.** `php artisan test --compact --filter=Dashboard` → PASS (the existing
      new-career case at `:121` must stay green: `Junior Year · Early July` for 13 recorded turns).
- [ ] **Step 5 — Bundle.** `npm run build`; `grep -ril 'imported at turn' public/build` still matches
      `Dashboard-*.js` (the pair sentence is unchanged).

**Acceptance:** the resume card's position matches the Cockpit's sentence for the same run, and the
`Turn N logged · imported at turn M` pair keeps both facts readable.

---

## Slice B2 — Cockpit race strip must read the decision turn

**Files:** Modify `app/Services/Career/RaceStripBuilder.php:85` (`$run->nextTurnToPlay()`).
**Test:** Modify `tests/Feature/RunRaceStripTest.php` (add a snapshot case).

- [ ] **Step 1 — Probe.** Measured disagreement on run `#36`: strip `this_turn = {turn: 20, year_label:
      'Senior', offered: 7}`; `/races` and `/races/planner` both plan `Turn 19`; page header `Turn 67 of 72`.
      Cause: `RaceStripBuilder` uses `nextTurnToPlay()` (68 → Senior turn 20) while the decision screens use
      `decisionTurn()` (stored position 67 → Senior turn 19).
- [ ] **Step 2 — Failing assertion.** Add a case with `career_position = fromTurnIndex(67)` and one recorded
      turn asserting `raceStrip.this_turn.turn === 19` and `empty.this_turn` still null when the calendar
      offers races there. Expected: FAIL (currently 20).
- [ ] **Step 3 — Change.** Replace `$run->nextTurnToPlay()` with `$run->decisionTurn()`. Guard: a run with no
      stored position must behave identically (`decisionTurn()` falls through to `nextTurnToPlay()`), so the
      existing new-career cases must pass unmodified.
- [ ] **Step 4 — Verify.** `php artisan test --compact --filter=RunRaceStrip` → PASS; also
      `--filter=CareerRaceDecision` and `--filter=RacePlanner` → PASS (shared reading must not move).
- [ ] **Step 5 — Bundle.** `npm run build`; no new copy, so no L4 string check.

**Acceptance:** the strip, the race decision screen and the planner all name the same turn for a snapshot run,
and the new-career path is byte-identical.

---

## Slice Z — Verification battery (runs once, after A1..B2)

- [ ] `php artisan test --compact --filter='Dashboard|RunRaceStrip|Catalog|PreferenceControls|RunSettings'`
- [ ] `npm run typecheck`
- [ ] `vendor/bin/pint.bat --test --format agent <touched php paths>`
- [ ] `vendor/bin/phpstan analyse --no-progress --memory-limit=1G`
- [ ] `npm run build`
- [ ] `python tools/md_fence_check.py docs/roadmap/implementation-roadmap-2026-11-17-s18.md docs/plan/implementation-plan-2026-11-17-s18.md docs/audits/reconciliation-rounds/*s18*.md`
- [ ] `composer lore` + `composer lore-code` — quote hit count and delta from the last recorded figure
      (`5ed45d67`: docs 478 + 75 exempt, code 182), with a one-line ruling per hit introduced by these edits.

---

## Deferred, with reason (not scheduled)

| Item | Reason |
| --- | --- |
| Review queue carrying race rows (S18-08) | Needs an entity discriminator on `match_candidates` or a second queue: schema + ADR. Owner-scope. |
| Sources ledger fetch coverage (S18-09) | `data_sources` is written per promoted trainee fact; the other 13 datasets legitimately have no provenance rows. Fixing it means backfilling or re-fetching — a data operation, not copy. |
| Owned skills at snapshot (S18-10 / R8 U-02) | Writer and picker exist; the snapshot hand-off is a flow decision. |
| U-01 / U-04 / U-06 | R8 DEFERRED owner-scope schema questions. |

---

## Review focus (inputs these slices most likely meet first)

1. **A run with a stored position but logged turns after it.** `decisionTurn()` prefers the stored position;
   a correction turn logged at 68 must not be silently overridden without the screen saying so. B1/B2 keep
   `turn` as the recorded count beside the position for exactly this reason.
2. **A run with `career_position = null` and zero turns.** `decisionTurn()` returns null; both B1 and B2 must
   fall back to today's output rather than printing a default position.
3. **A run whose scenario composes no race calendar.** `RaceStripBuilder` already returns `shown: false`
   before reading any turn; the change must stay below that guard.
4. **The flag-off preferences page.** A2's condition must be evaluated server-side or on the declared prop, not
   inferred from the selected radio.
5. **Copy that a browser spec pins.** `help.spec.ts:54` and `catalog.spec.ts:104` both quote shipped sentences;
   changing copy without changing the spec leaves a red spec that reads as a regression.
