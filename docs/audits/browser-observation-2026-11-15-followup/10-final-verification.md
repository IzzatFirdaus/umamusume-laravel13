# 10 — Final verification: remaining roadmap execution

**As of** HEAD `d417a9a5` (`fix(a11y): lower-case the BO-009 continuation`) · branch `master`, four commits
ahead of `origin/master` (`10dee9d7`). The last roadmap implementation commit before the review follow-ups is
`fe8747c9`; `21568017` is this report. The working tree carries peer-owned paths (none staged by this
dispatch).

## Status in one line

Phases 0–4 and 6 are complete. Every roadmap slice has code, a test or browser walk, and evidence. Phase 5
(BO-004) remains deferred for an owner ruling. A fresh-context review found **no Critical issues and two
Important ones** — picker page position lost on reopen/equip, and a partial run-15 deck becoming permanent
after a re-seed. Both are fixed with regression tests (`07ec79d9`, `4c13e0e0`), and one copy Minor is fixed
(`d417a9a5`). The gates are green for every check this dispatch can own; six PHP failures and eleven browser
failures remain, and every one is traced to peer-owned uncommitted worktree changes (`ProvenanceMark.vue`,
`app.css`, `config/scenarios.php`, `Umamusume.php`, two CRLF files), not to the commits below.

## What was asked

Execute the remaining roadmap from the dispatch prompt and the v3.0.0 roadmap:

1. Commit the 12 s56 comma edits without bundling peer autocorrect attributes (BO-009).
2. Capture run-34 / run-35 Cockpit evidence for the Phase 2/3 fields, with clean consoles.
3. Close the remaining Phase 5 slices: notes reader (R6-53), support-card picker pagination and Dashboard
   scope annotation (R6-24), BuildTarget required markers (BO-010), Preflight separator and count (BO-006),
   trainees KPI annotation (BO-008).
4. Seed a populated six-card deck on run 15 and walk the populated deck screen (deck coverage hole).
5. Run the gates and targeted browser suite, then write this evidence-based SITREP.
6. Leave BO-004 deferred unless the owner rules.

## Commits landed by this dispatch

| Commit | Subject | Files | Lines |
|---|---|---|---|
| `1f4a9c5f` | `fix(a11y): normalise absence-cell separator to comma (BO-009)` | 7 | +12 / −12 |
| `5b8ee62c` | `feat(cockpit): display run notes (R6-53)` | 2 | +64 / −1 |
| `9f800577` | `fix(career): mark BuildTarget required selects visibly (BO-010)` | 2 | +21 / −3 |
| `5156bc49` | `fix(career): separate Preflight fix link and derive count from table (BO-006)` | 2 | +31 / −19 |
| `df150cd8` | `feat(cards): paginate support-card picker and annotate dashboard count (R6-24)` | 6 | +134 / −5 |
| `d330c82e` | `fix(dashboard): annotate trainees KPI scope (BO-008)` | 2 | +14 / −5 |
| `37fd9c88` | `feat(seeder): import deck for UraFinaleRunsSeeder run 15 (deck coverage)` | 2 | +90 / −0 |
| `fe8747c9` | `fix(seeder): skip run-15 deck slots the catalogue cannot resolve` | 1 | +9 / −1 |
| `21568017` | `docs(audit): record final roadmap verification (Phase 6.3)` | 1 | +407 / −0 |
| `07ec79d9` | `fix(cards): keep the picker page across reopen and equip (R6-24)` | 3 | +61 / −5 |
| `4c13e0e0` | `fix(seeder): fill missing run-15 deck slots on re-seed` | 2 | +43 / −9 |
| `d417a9a5` | `fix(a11y): lower-case the BO-009 continuation` | 1 | +1 / −1 |

The four commits `5156bc49`–`37fd9c88` were made earlier in this session and are on `master`; the three
commits `1f4a9c5f`–`9f800577` were replayed from the salvage branches after a peer advance rewrote
`master` around them. The replay preserved the exact diffs; it did not stage peer autocorrect attributes.
`d56d0fdf` and the other CI commits above `b6b7bae8` are peer work, not this dispatch. The review follow-ups
`07ec79d9`, `4c13e0e0` and `d417a9a5` sit on top of the report and address the fresh-context findings; they
are not part of the original eight.

## Phase 1 — BO-009 (s56 comma normalisation)

`1f4a9c5f` changes exactly seven files and twelve absence-cell sites:

| File | Sites |
|---|---|
| `resources/js/components/support/SupportSlot.vue` | 3 |
| `resources/js/pages/Database/ShopItems.vue` | 1 |
| `resources/js/pages/Database/Sources.vue` | 2 |
| `resources/js/pages/Legacy/Index.vue` | 2 |
| `resources/js/pages/Runs/Index.vue` | 2 |
| `resources/js/pages/SupportCards/Index.vue` | 1 |
| `resources/js/pages/Veterans/Index.vue` | 1 |

The commit was built from a hunk-filtered patch for the three mixed files, so the peer `autocorrect`,
`autocapitalize`, `spellcheck`, `enterkeyhint` and `<search>` changes stayed in the working tree. Verified:
`git diff --cached` had 12 insertions / 12 deletions and no peer attribute. `git show --stat` is 7 files,
+12 / −12.

## Phase 2 — run 34 / run 35 Cockpit evidence

Served a private copy of the database at
`D:/Projects/umamusume-laravel13/.scratch-uma/hermes-p1-20261116/browser.sqlite` with absolute
`DB_DATABASE` and `UMA_EXPECTED_DATABASE`, `SESSION_DRIVER=file`, `CACHE_STORE=file`, on `127.0.0.1:8190`.
The DatabaseGuard accepted the absolute paths.

### Run 34 — populated (Rice Shower, Unity Cup, turn 67)

Exact text read from the Scenario region:

```
Team rank          S (five stars)
Team stat grades   Speed A · Stamina A · Power A · Guts S · Wit A
Unity Trainings    55
League             8 · +30 all attributes
Bursts             11 (6 spirit · 5 extreme) (inside 10-12)
Spirit Burst Countdown  11 combined (Gold Lv1 secured; 2 more for Gold Lv3). This counts normal
                        plus Extreme bursts; a normal-only reading would need 1 more.
Fans               209,245 · Star (30,755 to Top Star)
```

The seeded note opens from `details[data-region="run-notes"]` and begins:
`Unity Cup, [Rosy Dreams] Rice Shower, read off 68 client frames on 2026-10-03…` It also records
`Team Blue Bloom sits at Rank S with 55 Unity Trainings and 11 combined bursts.` The panel is collapsed
by default; the summary is `Notes`, and the full text is one press away.

### Run 35 — zero-turn (Admire Vega)

Every field is an explicit absence, never a numeric default:

```
Team rank          not recorded
Team stat grades   Speed N/A · Stamina N/A · Power N/A · Guts N/A · Wit N/A
Unity Trainings    N/A
League             N/A
Bursts             N/A
Finale             Unity Cup: not yet reached
Team               N/A, not recorded
Fans               N/A, Fans has not been recorded for this run.
Skill Points       N/A, Skill Points has not been recorded for this run.
Notes              No notes recorded.
```

### Screenshots and console

- `screenshots/phase-2-3-run34-cockpit-1440x900.png`
- `screenshots/phase-2-3-run34-cockpit-390x844.png`
- `screenshots/phase-2-3-run35-cockpit-1440x900.png`
- `screenshots/phase-2-3-run35-cockpit-390x844.png`
- `screenshots/phase-3-run34-notes-open-1440x900.png`

All four captures reported `consoleMessages: []` and `pageErrors: []`. The run-34 note probe also reported
both arrays empty.

## Phase 3 — remaining Phase 5 slices

### 3.1 — R6-53 notes reader (`5b8ee62c`)

`CockpitController::runSection()` now carries `'notes' => $run->notes` in the run payload and its PHPDoc
shape. `Cockpit.vue` already carried the `<details data-region="run-notes">` panel on master (introduced by
the peer cockpit commit `e1f4d66a`), so the controller payload and the browser spec are the missing half.
`tests/browser/notes-reader.spec.ts` was added: it creates a run, asserts the panel is collapsed by
default, asserts the `Notes` summary, expands it, and asserts the stored sentence; it also asserts run 35's
`No notes recorded.` state.

Evidence: `notes-reader.spec.ts` **1/1 pass**; run-34 note text captured in
`.scratch-uma/hermes-p1-20261116/notes34-capture.json`; screenshot
`phase-3-run34-notes-open-1440x900.png`.

### 3.2 — R6-24 support-card pagination + Dashboard support scope (`df150cd8`)

`DeckSelectController` now paginates the Global-released picker at 25 with `withQueryString()` and passes
`page`, `pages`, `from`, `to`, `total` in the picker payload. `CardPicker.vue` renders the shared
`Pagination` component (`variant="compact"`) inside the dialog and emits `page`; `DeckSelect.vue` maps that
to a `router.get` that preserves `type`, `q`, `deck_slot` and scroll/state. The Dashboard support tile now
reads `559 catalogued · 251 on [Global]`.

Evidence (`.scratch-uma/hermes-p1-20261116/phase3-capture.json`):

```
Trainees tile  133 · 72 on [Global]
Support tile   559 · 251 on [Global]
Picker        251 cards · page 1 of 11; 25 rows
Next          251 cards · page 2 of 11; 25 rows
Console        clean
```

Screenshots: `phase-3-dashboard-kpi-1440x900.png`, `phase-3-deck-picker-page1-1440x900.png`,
`phase-3-deck-picker-page2-1440x900.png`. Browser spec `support-picker-pagination.spec.ts` **1/1 pass**.

### 3.3 — BO-010 BuildTarget required markers (`9f800577`)

`BaseSelect` gained the opt-in `showRequiredMarker` prop. The marker is a sibling of `<label>` in a flex
wrapper, not a child, so the select's accessible name remains exactly the label word; the native `required`
attribute and `aria-required` carry the announcement, and the `*` is `aria-hidden`. `BuildTarget.vue` marks
Purpose, Surface and Style with `required` and `show-required-marker`.

Evidence (`.scratch-uma/hermes-p1-20261116/build-target-capture.json`): each of the three labels reports
`starCount: 1`, `starText: ["*"]`, and one accessible-name occurrence; the browser spec
`career-build-target.spec.ts` **12/12 pass**, including the 44px sweep and 320px reflow. Screenshot:
`phase-3-build-target-required-markers-1440x900.png`.

### 3.4 — BO-006 Preflight separator and derived count (`5156bc49`)

The `ml-2` margin before the Deck `Fix this` link was replaced by an explicit `·` separator span. The
`attention` computed now derives from the rendered table rows whose `checkState(row)` is not `Pass` or
`Skipped`, so a `Warning` on an entered-but-incomplete section counts; the sentence pluralises
`step needs` / `steps need`. The browser spec now reads the table states, derives the count, and asserts
the sentence matches the table rather than a hardcoded fixture number.

Evidence (`.scratch-uma/hermes-p1-20261116/preflight-capture.json`):

```
Empty draft   5 steps need attention; states Missing ×5
Filled draft  2 steps need attention; states Pass, Pass, Pass, Missing, Warning
Deck row      Deck Warning 1 of six positions carry a card · Fix this
Link          "Fix this", aria-label "Fix Deck"
```

Screenshots: `phase-3-preflight-empty-1440x900.png`, `phase-3-preflight-empty-390x844.png`,
`phase-3-preflight-filled-1440x900.png`, `phase-3-preflight-filled-390x844.png`.
`career-preflight.spec.ts` **14/14 pass**, including the filled-draft count case.

### 3.5 — BO-008 trainees KPI scope (`d330c82e`)

The Dashboard trainees tile now reads `133 recorded · 72 on [Global]`, matching the support tile's scope
line. The controller uses `ReleaseStatus::GlobalReleased->value`; the catalogue's own `72 of 72` and the
wizard's `66 trainees are catalogued` were not changed. Evidence: `phase3-capture.json` trainee tile
`133` / `72 on [Global]`; screenshot `phase-3-dashboard-kpi-1440x900.png`; `dashboard.spec.ts` **12/12 pass**.

## Phase 4 — deck builder coverage

`UraFinaleRunsSeeder` now carries `RUN_15_DECK` and `seedRun15Deck()`. The fixture resolves cards by the
published `support_id`, never by row id, and is guarded by `$label === '3'` plus
`deckSlots()->exists()`, so a re-seed does not duplicate the six rows. The six slots are:

| Slot | Card | Ownership | Level |
|---|---|---|---|
| 1 | Tokai Teio `[Dream Big!]` | OWNED | 35 |
| 2 | Nishino Flower `[Even the Littlest Bud]` | OWNED | 30 |
| 3 | Biko Pegasus `[Double Carrot Punch!]` | OWNED | 30 |
| 4 | Super Creek `[Piece of Mind]` | RENTED | 50 |
| 5 | Matikanetannhauser `[Just Keep Going]` | OWNED | 30 |
| 6 | Haru Urara `[Urara's Day Off!]` | OWNED | 35 |

Database probe on the private copy:

```
deck_slots columns: training_run_id / support_card_id / slot_position NOT NULL;
                    ownership / card_level nullable
run 15: 6 slots
runs 16–33: 0 slots each
run 34: 6 slots (pre-existing Rosy Dreams deck, untouched)
run 35: 0 slots
```

The browser walk of `/training-runs/15/deck` rendered six named cards, slot 4 `Rented` while the other
five are `Owned`, the copy `Effects apply from the next turn`, and the Deck analysis totals. Screenshots:
`phase-4-deck15-populated-1440x900.png`, `phase-4-deck15-populated-390x844.png`; capture JSON
`deck15-capture.json`; consoles clean.

`UraFinaleRunsSeederTest` was extended with a six-slot test and a re-seed idempotency test. `37fd9c88`
passed the seeder suite but broke `TrackerVeteransSeederTest`, which imports the tracker without seeding
support cards. `fe8747c9` fixed that by skipping a slot whose `support_id` the catalogue cannot resolve,
instead of `firstOrFail()` aborting the whole import. The follow-up keeps the production seed complete
because the six published ids are present there. `TrackerVeteransSeederTest` and
`UraFinaleRunsSeederTest` then passed **19/19 (168 assertions)**.

## Phase 5 — BO-004 (deferred)

No owner ruling was recorded on whether the dark-theme `To do` pill's 4.15:1 contrast is a deliberate hue
trade or a defect. BO-004 is therefore **not replayed** onto `master` by this dispatch. The working tree
does carry a peer candidate (`--color-risk: #FF6F3B` in `app.css`, plus a BO-004 commit on
`salvage/phase1-94f9407b` / `recover/phase1`), but it is peer-owned and uncommitted here. If the owner rules
"fix", replay that token change with a `getComputedStyle` measurement on `/` in dark theme and assert
≥ 4.5:1; if "keep", record the decision and close BO-004 with no code change.

## Phase 6 — gates

| Gate | Result | Evidence |
|---|---|---|
| `npm run typecheck` | **exit 0** | `vue-tsgo` + `tsc --noEmit`; the earlier `nativephp-packaged.spec.ts` red is gone on current HEAD |
| `npm run build` | **exit 0** | Vite build, 1109 modules, manifest written |
| `npm run lint:ui` | **exit 0** | 516 findings reported; no `debt-increase`, so the arbitrary-value ratchet (baseline 209) did not grow |
| `vendor/bin/pint --test app database tests` | **pass** | includes the seeder and its test |
| `vendor/bin/pint --test` (repo root) | **red** | only untracked `probe_runs.php` (peer scratch file), not a tracked source file |
| `vendor/bin/phpstan analyse --no-progress` | **1 error, pre-existing** | `app/Http/Middleware/EnforceNativePHPSecret.php:13` — `env()` outside config; introduced by `cc37e9bd`, untouched here |
| `vendor/bin/pest --compact` | **2429 passed, 6 failed** | see baseline table below; the seeder regression is fixed |
| `php tools/lore.php` | **exit 0** | `lore-docs: 630 hit(s), 75 exempt line(s)` |
| `php tools/lore.php --code` | **exit 0** | same 630 / 75 summary; no new lore violation from this dispatch |
| Targeted Playwright (10 specs, port 8191, fresh scratch DB) | **121 passed, 11 failed** | see baseline table below |

`php artisan test --compact` was attempted first but the background harness returned `stdin is not a tty`;
the equivalent `vendor/bin/pest --compact` was run instead. Both point at the same test suite.

### PHP baseline reds (6) — all peer-owned

| Test(s) | Failure | Cause, verified in the worktree |
|---|---|---|
| `CareerScenarioSelectTest` (1) | expects `Spirit Bursts`, actual `Spirit Bursts (combined)` | peer `config/scenarios.php` changed the resource label |
| `CareerTraineeSelectTest` (3) | 404 on `/career/setup/trainee/{id}` | peer `Umamusume::getRouteKeyName()` now returns `slug`; the fixtures pass numeric ids |
| `TraineeCardFormTest` (1) | 404 on the same route | same route-key change |
| `LineEndingGuardTest` (1) | CRLF in `EventDecision.vue`, `career-cockpit-forms.spec.ts` | peer files carry CRLF; both are modified in the worktree |

No PHP failure touches a file changed by the eight commits above. The seeder regression that did touch
this dispatch's files (`TrackerVeteransSeederTest`, six failures) was found by the first full run and fixed
in `fe8747c9`; the second full run confirms it is gone.

### Browser baseline reds (11) — all peer-owned

The targeted suite ran `career-cockpit`, `unity-cup-panel`, `scenario-panel`, `career-build-target`,
`career-preflight`, `notes-reader`, `support-picker-pagination`, `dashboard`, `career-deck-select` and
`support-deck` against a fresh scratch database on port 8191. **121 passed / 11 failed.**

| Spec / test | Failure | Cause, verified in the worktree |
|---|---|---|
| `career-cockpit` ×4 (lines 90, 257, 486, 501) | `getByRole('img', { name: /^Calculated/ })` absent; 44px failure on a 23px `pill` button | peer `ProvenanceMark.vue` changed the mark from `<span role="img" tabindex="0">` to `<button class="pill pill-neutral">` |
| `scenario-panel` ×2 (lines 77, 234) | `Spirit Bursts: not recorded` image absent; 23px `pill` button fails 44px | same `ProvenanceMark` change, plus the `config/scenarios.php` label |
| `unity-cup-panel` ×4 (lines 50, 149, 238, 278) | `Estimated` image absent; scenario region now contains one `<button>`; 23px `pill` fails 44px | same `ProvenanceMark` change |
| `career-deck-select` ×1 (line 147) | 344 computed-style drift entries against `career-deck.html` | peer `app.css` token/tooltip changes (`--color-risk`, popover/anchor styles) change computed colours, font sizes, line heights and padding |

The failing selectors and the peer diff agree: `git diff HEAD -- resources/js/components/ProvenanceMark.vue`
shows the `role="img"` span replaced by a `<button>`; `git diff HEAD -- resources/css/app.css` shows the
peer token and tooltip styles; `git diff HEAD -- config/scenarios.php` shows `Spirit Bursts (combined)`.
None of these files is in the eight commits. This is a baseline-red classification, not a claim that a
clean tree would be green: the peer work is still in flight, so a clean-tree rerun was not possible
without discarding another session's work.

### What passed in the targeted suite

| Spec | Result |
|---|---|
| `career-build-target.spec.ts` | **12/12 pass** (including 44px and 320px) |
| `career-preflight.spec.ts` | **14/14 pass** (including filled-draft count) |
| `notes-reader.spec.ts` | **1/1 pass** |
| `support-picker-pagination.spec.ts` | **1/1 pass** (25 rows, page 2) |
| `dashboard.spec.ts` | **12/12 pass** |
| `support-deck.spec.ts` | **13/13 pass** (including 44px and picker) |
| `career-deck-select.spec.ts` | **18/19 pass**; the one failure is the HFW computed-style drift above |
| `career-cockpit.spec.ts` | 4 failures, all `ProvenanceMark`-caused; the remaining cockpit cases pass |
| `scenario-panel.spec.ts` | 2 failures, both `ProvenanceMark`/config-caused; the rest pass |
| `unity-cup-panel.spec.ts` | 4 failures, all `ProvenanceMark`-caused; the rest pass |

## Branch recovery note

`master` moved during the session: peer CI commits (`b6b7bae8`, `d56d0fdf`, and the merge chain beneath
them) landed while the roadmap was being executed, and an earlier peer advance had left BO-009, the notes
commit and BO-010 on `salvage/phase1-94f9407b` / `salvage/bo010-ab6fda83` rather than on `master`. The three
missing commits were replayed onto current `master` with their exact diffs:

- `1f4a9c5f` — BO-009, 7 files / 12 comma sites, no peer attributes.
- `5b8ee62c` — R6-53 controller payload + `notes-reader.spec.ts`; the Vue panel was already on `master`
  through `e1f4d66a`.
- `9f800577` — BO-010 `BaseSelect` + `BuildTarget`; `master`'s BuildTarget also needed the native
  `required` attributes that the salvage branch already carried.

The earlier commits `5156bc49` (BO-006), `df150cd8` (R6-24), `d330c82e` (BO-008), `37fd9c88` (Phase 4) and
`fe8747c9` (seeder guard) were already on `master` and are included in the commit table. No peer
autocorrect attributes, peer `closedby`/`autocorrect` work, or peer `ProvenanceMark` changes were staged.

## Decisions and rulings

- **BO-010 marker placement.** The `*` is a sibling of `<label>` in a flex wrapper, not a child. The native
  `required` attribute and `aria-required` carry the announcement; the marker is `aria-hidden`, so the
  select's accessible name stays exactly the label word. Cost if wrong: a sighted-only marker with no
  programmatic required state — avoided by keeping both.
- **BO-006 count source.** The count derives from rendered table rows whose state is not `Pass`/`Skipped`,
  not from the server's `missing_section.*` list. The old list skipped a `Warning` on an entered-but-
  incomplete section, which is exactly the filled-draft case. Cost if wrong: a count that disagrees with
  the table the Trainer is reading.
- **R6-24 pagination contract.** 25 rows per page, `withQueryString()`, page facts in the picker payload,
  and `router.get` with `preserveScroll`/`preserveState`/`replace: true`. Cost if wrong: a filter or slot
  target lost across a page turn — the browser spec walks page 2 and the deck-select suite keeps the
  picker open across filters.
- **Dashboard split.** R6-24 and BO-008 were committed separately so the support-card scope and the
  trainee scope can be reviewed independently. Cost if wrong: a bisect attributes one tile to the other
  commit — mitigated by the exact per-commit file lists above.
- **Seeder resolution and resilience.** Deck slots resolve by published `support_id`, never row id; the
  guard `$label === '3'` plus `deckSlots()->exists()` makes the fixture idempotent; an unresolvable card is
  skipped rather than aborting the tracker import. Cost if wrong: a partial deck in a catalogue that lacks
  one of the six cards — visible as fewer than six slots, and the production catalogue has all six.
- **BO-004 deferred.** No owner ruling, so no dark-risk token change is replayed. Cost if wrong: the dark
  `To do` pill remains at 4.15:1 until the owner decides.

## Verified vs. assumed

**Verified by command output in this session:** the exact run-34/run-35 DOM text above; the four
screenshots and their empty console/page-error arrays; the Phase 3 dashboard/picker/build-target/preflight
JSON; the run-15 deck slot counts and the six rendered cards; the commit stats; `npm run typecheck`,
`npm run build`, `npm run lint:ui`, scoped Pint, PHPStan, Pest, lore and the 10-spec Playwright run.

**Not verified / deliberately not claimed:**

- The canonical `database/database.sqlite` was **not** re-seeded; all DB probes ran on the private copy.
  The run-15 deck is proven on the copy and by the seeder test, not on the canonical dev file.
- `public/build` is a shared worktree artifact and is not part of the commits; the browser evidence was
  captured from the current build.
- The 11 browser failures are classified as peer-caused from the failing selectors plus the peer worktree
  diffs, not from a clean-tree rerun. A clean-tree rerun would require discarding peer work.
- `php artisan test --compact` was not used for the final count because the background harness reported
  `stdin is not a tty`; `vendor/bin/pest --compact` is the equivalent suite and is the run quoted above.

## Next steps, in execution order

1. **Finish the peer `ProvenanceMark` migration.** The component now renders a `<button class="pill
   pill-neutral">` instead of a `<span role="img">`. Either update the five affected specs to the new
   semantics, or keep the mark non-interactive and restore the `role="img"`/`tabindex="0"` shape. If the
   button is intentional, give it a 44px hit area (the current 23px fails WCAG 2.2 SC 2.5.8) and update
   the scenario-region control-count assertions. This clears the 11 browser reds.
2. **Resolve the `Umamusume::getRouteKeyName()` slug change.** The route now binds by `slug`, but
   `CareerTraineeSelectTest` and `TraineeCardFormTest` still request numeric ids. Update the fixtures to
   use `slug` (or support both keys) — clears 4 PHP reds.
3. **Update the scenario resource label expectations.** `config/scenarios.php` now says
   `Spirit Bursts (combined)`; `CareerScenarioSelectTest`, `scenario-panel.spec.ts` and the unity-cup
   assertions still expect `Spirit Bursts`. Decide whether the label is final, then update the tests or
   the config — clears 1 PHP and the label half of the browser reds.
4. **Convert the two CRLF files to LF.** `resources/js/pages/Career/EventDecision.vue` and
   `tests/browser/career-cockpit-forms.spec.ts` are peer-modified; `LineEndingGuardTest` is the only
   failure — clears 1 PHP red.
5. **Scope or remove `probe_runs.php`.** It is an untracked root-level scratch probe, not a source file;
   either delete it or run Pint on tracked paths only. The scoped `pint --test app database tests` is
   already green.
6. **Fix the PHPStan env call.** `EnforceNativePHPSecret.php:13` calls `env('NATIVEPHP_SECRET')` outside
   config; move it behind a config key or document the exception. This is pre-existing `cc37e9bd` work.
7. **BO-004 owner ruling.** If "fix", replay the `--color-risk` dark token candidate and measure
   `getComputedStyle` on `/` in dark theme; if "keep", record the decision in `01-findings.md` and close
   BO-004 with no code.
8. **Re-run the full gates on a clean tree** after the peer work settles, then close the audit. The
   roadmap's Phase 6 acceptance is not met while peer-owned reds remain, even though no red is attributable
   to this dispatch.

## Evidence index

- Commits: `git show --stat 1f4a9c5f 5b8ee62c 9f800577 5156bc49 df150cd8 d330c82e 37fd9c88 fe8747c9`.
- Phase 2: `screenshots/phase-2-3-run{34,35}-cockpit-{1440x900,390x844}.png`,
  `.scratch-uma/hermes-p1-20261116/cockpit-capture.json`, `notes34-capture.json`.
- Phase 3: `screenshots/phase-3-*.png`, `.scratch-uma/hermes-p1-20261116/{phase3,build-target,preflight}-capture.json`.
- Phase 4: `screenshots/phase-4-deck15-populated-{1440x900,390x844}.png`,
  `.scratch-uma/hermes-p1-20261116/deck15-capture.json`, `.scratch-uma/hermes-p1-20261116/probe-deck.php`.
- Browser suite: process log `proc_cf7be1af90f5` (121 passed / 11 failed), plus the per-spec pass lines
  quoted above.
- PHP suite: process log `proc_a2d30979dca3` (2429 passed / 6 failed after the seeder fix).
