# Scenario-Flow Surface Reconciliation — session S18 handoff (2026-11-17)

Append-only sibling to `docs/roadmap/implementation-roadmap-2026-11-17-s18.md` and
`docs/plan/implementation-plan-2026-11-17-s18.md`. Nothing here rewrites a closed R8 finding; the two
R8 items this session could not land are carried forward as errata in §7.

**Head at start and end of execution:** `be8893a2` (`master`), unchanged. No commit, no stage, no push.

---

## 1. Objective and outcome

**Asked:** bring every scenario-flow surface into verified agreement with what the application actually
holds, record conflicts rather than smooth them, ship the corrections.

**Delivered:** six defects confirmed through the four-layer gate and fixed, one of them a second defect
the failing-first test exposed on the way (`3 Year · Turn 19`). Two owner-scope items deferred with
reasons. Nothing in the app was changed on assertion: every finding below has a rendered observation
behind it and a quoted command result under it.

| Verdict | Count | Items |
| --- | --- | --- |
| Confirmed and fixed | 6 | S18-01, S18-02, S18-03, S18-04, S18-05, S18-07 |
| Confirmed, owner-scope, deferred | 3 | S18-08, S18-09, S18-10 |
| Already fixed at the gate | 5 | R8 E-01, R8 S1.4 schema, R8 S1.4 config, R8 S1.7, R8 E-02 (both halves) |
| Deliberate non-defect | 1 | Unity burst-band table (publisher conflict, guarded by rule) |

The R8 expectation held: of ten candidate claims, five died at the gate as already-fixed or
mis-scoped, and of the five that survived, two were narrower than the claim that named them.

---

## 2. Probes run (P0 gate)

| Probe | Command | Result |
| --- | --- | --- |
| HEAD | `git rev-parse HEAD` | `be8893a25879acad04935a3aeb47c5c38ed84517` |
| Schema | `ls database/migrations/*.php \| wc -l`; `php artisan migrate:status --no-ansi` | 80 files, 80 Ran, 0 Pending |
| Bundle freshness | `npm run build` | 970 modules, `built in 44.54s`, exit 0 |
| Server | `curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1:8000/`; `ls public/hot` | `200`; no `public/hot` (Vite dev server not running, so :8000 serves the built bundle) |
| L4 needles | `grep -ril '<needle>' public/build` | pre-fix: `holds no growth row`, `no Undo last turn control`, `127.0.0.1` all present; post-fix: none |
| Dev data | raw PDO against `database/database.sqlite` | 263 `character_cards`, all with `growth_rates`; 304 `match_candidates`, all `gametora-races`, all `Pending`, `match_tier` NULL; 133 `data_sources` rows, one `source_key` |
| Doc fences | `python tools/md_fence_check.py <two docs>` | `0 invalid closing fence(s) in 2 file(s)` |
| Lore | `php tools/lore.php` | `lore-docs: 582 hit(s), 75 exempt line(s)`, 0 hits in any file this session touched |

`composer lore` and `composer lore-code` could not run: the Windows composer shim points at
`/c/Users/exatf/AppData/Local/ComposerSetup/bin/composer.phar`, which is absent. `php tools/lore.php`
and `php tools/lore.php --code` were run directly instead — same script, exit 0, results above.

**Environment deviation, stated plainly:** the browser tool refuses loopback
(`Blocked: URL targets a private or internal address`), so the rendered-copy confirmation was taken from
the served bundle (`grep` over `public/build`) and from HTTP 200 plus Inertia prop assertions, not from a
pixel-level walk. Every copy claim in §3 is therefore **served-asset verified, not screen-captured** — the
distinction the four-layer law exists to keep.

---

## 3. Slices landed

Every entry: the observation, the smallest change, and the quoted output that closes it.

### S18-05 — settings lede printed a loopback URL as prose

`RunSettings.vue:62` interpolated `props.run.update_url` into a sentence, so the page read
`Everything here writes through http://127.0.0.1:8000/training-runs/36.` Change: the sentence names the
writer in words; the prop stays bound to the form that posts.

```
public/build/assets/RunSettings-BW5NRn00.js | Every change saves through this run -> 1
127.0.0.1 -> (none)
```

### S18-04 — Wide layout described as unavailable while its radio was live

The sentence shipped unconditionally; `input[name="settings[dashboard_layout]"][value=wide]` came back
`checked: true, disabled: false` and `config('dashboard.wide_bento_enabled')` defaults true. Change:
`v-if="!props.wideBentoEnabled"` on the paragraph only.

```
Edit-Dj4KFSFh.js | held behind DL.14 -> 1     (string ships, guarded)
```

### S18-03 — catalogue card claimed no growth row exists

`TraineeCatalog.vue` rendered `Growth **not stored** · the catalogue holds no growth row for an` — false
(all 263 `character_cards` carry `growth_rates`, ADR-0030) and truncated mid-sentence. Change: the card
says where the figure is read and why the index carries no percentage.

```
TraineeCatalog…js | on the trainee -> 1
holds no growth row -> (none)
```

### S18-07 — Help said there is no Undo control

Five career screens render a **disabled** `Undo turn N`; `DELETE runs.turns.destroy` is live. Change: the
paragraph says the control is drawn and disabled, not absent.

```
Help-D1J6KviI.js | Four career screens draw an -> 1
there is no Undo last turn control -> (none)
```

### S18-01 — landing card printed a position the cockpit contradicted (plus a second defect)

Failing-first test asserted a snapshot imported at Senior Early October with one logged turn reads
`Senior Year · Early October`, `position_turn` 67, `turn` 1:

```
-'Senior Year · Early October'
+'Junior Year · Early January'
```

Root cause: `activeCareer()` fed the recorded-turn **count** into `positionLabel()`. Change:
`$run->careerPosition()` (the one owner: stored position wins, else derived from the latest logged turn).

That fix exposed the second defect in the same payload, which the old test never reached because it
asserted neither:

```
-'Senior Year · Turn 19'
+'3 Year · Turn 19'
```

`cockpit_label` concatenated `' Year'` onto `$decisionTurn['year']`, which is the **grid constant** (`3`
for Senior), not a name. Change: `CareerPositionLabel::forYear($decisionTurn['year'])`.

```
Tests:    18 passed (339 assertions)
```

### S18-02 — cockpit strip planned a different turn than the screens it links to

The strip read `nextTurnToPlay()` (turn 68 → Senior turn 20) while `/races` and `/races/planner` planned
`decisionTurn()` (stored position 67 → Senior turn 19). Failing-first test:

```
Property [raceStrip.this_turn.turn] does not exist.
```

— the strip was answering about Junior turn 2, a different year entirely. Change: `RaceStripBuilder`
reads `$run->decisionTurn()`. A new-career run falls through to the same reading it had, which is why the
seventeen cases above it in the file stayed green unmodified.

```
Tests:    18 passed (286 assertions)
```

### Cross-cutting guard

`tests/Feature/ShippedCopyTruthTest.php` (new, 4 cases) reads the four touched Vue sources and fails on
the old sentences. It is a source scan, not a browser run, and says so in its own header: the rendered
half lives in the four specs that own those screens.

```
Tests:    4 passed (14 assertions)
```

---

## 4. Regression sweep (shared readings)

The two logic slices changed a value three screens share, so the neighbours were run:

```
php artisan test --compact --filter='RaceDecision|RacePlanner|SnapshotRaceCalendar|SnapshotReview|CareerCockpit'
Tests:    103 passed (1509 assertions)
```

```
php artisan test --compact --filter='DashboardTest|RunRaceStripTest|ShippedCopyTruthTest|HelpScreen|PreferenceControls|RunSettings|CatalogTest'
Tests:    84 passed (972 assertions)
```

---

## 5. Gate battery

| Gate | Command | Result |
| --- | --- | --- |
| Style | `vendor/bin/pint.bat --test --format agent <5 php paths>` | `{"tool":"pint","result":"passed"}` |
| Static analysis | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G <2 changed classes>` | `[OK] No errors` |
| TypeScript | `npm run typecheck` | exit 0 (`vue-tsgo` + `tsc --noEmit`) |
| Assets | `npm run build` | 970 modules, exit 0, `manifest.json` 63.67 kB |
| Doc fences | `python tools/md_fence_check.py <2 docs>` | 0 invalid fences |
| Lore | `php tools/lore.php`, `php tools/lore.php --code` | exit 0; 582 docs hits / 75 exempt; 0 hits in touched files |

**Not run, and why:**

- **PHPStan repo-wide** — only the two changed classes were analysed. A whole-project run is a checkpoint
  gate, not a slice gate, and the session's changes are inside those two files.
- **Browser suite** — 126 specs, one process at a time, ~23 min, and the browser tool refuses loopback
  this session. The three copy changes ship with source-scan tests plus L4 bundle greps; `help.spec.ts`
  still pins the **old** sentence and will fail the next time it runs. **This is the one knowingly red
  test left behind** — see §7.
- **Full PHP suite** — `--filter` on the affected neighbourhoods only.

---

## 6. Files touched (uncommitted delta, explicit paths)

```
 M app/Http/Controllers/DashboardController.php
 M app/Services/Career/RaceStripBuilder.php
 M resources/js/components/catalog/TraineeCatalog.vue
 M resources/js/pages/Career/RunSettings.vue
 M resources/js/pages/Help.vue
 M resources/js/pages/Preferences/Edit.vue
 M tests/Feature/DashboardTest.php
 M tests/Feature/RunRaceStripTest.php
?? docs/plan/implementation-plan-2026-11-17-s18.md
?? docs/roadmap/implementation-roadmap-2026-11-17-s18.md
?? tests/Feature/ShippedCopyTruthTest.php
```

`config/scenarios.php` and `resources/js/pages/Career/RunSettings.vue` carried peer edits at session
start. `RunSettings.vue` is in the list above because this session edited the same file; the peer's
uncommitted lines in it are **untouched by this session's patch** (one `const lede` expression and its
docblock). Nothing was staged, so no peer hunk was swept into an index.

---

## 7. Pending rulings and knowingly-open items

1. **`help.spec.ts:54` pins the old sentence.** The browser spec still asserts
   `there is no Undo last turn control`. It was not edited because the browser suite could not be run to
   prove the new assertion green, and an edited-but-unrun spec is not evidence. Editing it is one line;
   running it needs a session with browser access.
2. **S18-08 review queue carries race rows.** All 304 pending `match_candidates` are `gametora-races`
   with `match_tier` NULL, and the queue asks "Is this the right trainee?" about races. Needs an
   entity/kind discriminator on `match_candidates` or a separate race queue — schema plus ADR. Not
   self-rulable; unchanged from R8's reading.
3. **S18-09 sources ledger coverage.** 13 of 14 datasets print `No fetch recorded` because
   `data_sources` is written per promoted trainee fact. Backfilling or re-fetching is a data operation
   needing owner approval.
4. **S18-10 owned skills at snapshot.** Writer (`runs.skills.sync`) and picker exist; the snapshot
   hand-off is a flow decision. R8 U-02 stays a FIX candidate.
5. **U-01 / U-04 / U-06** remain DEFERRED owner-scope schema questions, untouched.
6. **Lore count moved 478 → 582.** The rise is register growth across the tree since
   `5ed45d67`, not this session's edits: 0 hits in every file touched here.

---

## 8. Risks carried forward

- **Two position surfaces still derive independently.** `DashboardController` now reads
  `careerPosition()` and `RaceStripBuilder` reads `decisionTurn()`. Both agree today because
  `CareerPosition::fromTurnIndex()` and the stored cast produce the same `turnIndex`; they are two
  callers of one model, not one call — a future third reading could drift again. Worth collapsing into
  one method when a third caller appears.
- **`decisionTurn()` prefers derived-over-logged for a new-career run.** A run that logs a turn past its
  stored position keeps reading the position; the landing card and strip show both figures rather than
  picking one, which is why §3 keeps `turn` beside `position_turn`.
- **The bundle is rebuilt and served from `public/build`.** Any claim here is dated against the
  2026-11-17 build; a `composer dev` session with Vite would serve different assets.

---

## 9. Next steps, in execution order

1. Edit `help.spec.ts:54` to the new sentence, then run
   `PLAYWRIGHT_PORT=<81xx> PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-<port>.sqlite npm run test:browser -- --workers=1`
   in a browser-capable session.
2. Run the full suite at a checkpoint (`php artisan test --compact`) — not done this session.
3. Take the owner ruling on S18-08 (schema discriminator) and S18-09 (provenance backfill).
4. If S18-08 is approved, the slice is: migration adding `entity`/`kind` to `match_candidates` +
   `ARCHITECTURE-ESSENTIALS.md` digest + ADR, then a separate `/review` queue for races.

---

## 10. Follow-through (2026-11-17, appended after this handoff was written)

The four open items in §7 and §9 were executed. Nothing above is rewritten; each line below supersedes one
sentence and says which.

### 10.1 The knowingly-red spec is closed — quoted

`tests/browser/help.spec.ts:54` asserted `there is no Undo last turn control`, the sentence S18-07 removed.
The handoff (§7 item 1, §9 step 1) called it blocked because Hermes' browser tool refuses loopback. That
conflated two substrates: the tool drives a cloud browser, while Playwright launches its **own Chromium**
against `127.0.0.1:<port>` and never used Hermes' browser at all. Nothing external blocked the edit.

The spec and its neighbour in `catalog.spec.ts:104-107` were moved to the shipped sentences, then run
session-private:

```
PLAYWRIGHT_PORT=8286 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/s18b/browser-8286.sqlite   npx playwright test tests/browser/help.spec.ts tests/browser/catalog.spec.ts   tests/browser/preferences.spec.ts tests/browser/run-settings.spec.ts --workers=1

32 passed (2.0m)
```

The four copy fixes are therefore **rendered-verified**, not L4-only: Help, the catalogue card, the
preferences card and the run-settings lede each assert their shipped sentence in a real browser.

### 10.2 The `[]` Dashboard probe was wrong — R8 E-02 is closed on both surfaces

§1 of the roadmap and §2 row S18-06 recorded `a[href="/career/snapshot"]` returning `[]` on `/` and named
`Dashboard.vue:423-426` as the shipped link. Re-probed:

```
grep -n 'career.snapshot' resources/js/pages/Dashboard.vue resources/js/pages/Runs/Index.vue
resources/js/pages/Dashboard.vue:424:                            href="/career/snapshot"
resources/js/pages/Dashboard.vue:688:                            href="/career/snapshot"
resources/js/pages/Runs/Index.vue:111:                    <LinkButton href="/career/snapshot" ...>Start from existing snapshot</LinkButton>
```

`:688` is inside the always-rendered `quick_actions` card and `:424` inside the empty-resume branch, so the
door is reachable from **both** states, and the Careers list carries it too. The `[]` reading was taken
before the card hydrated. R8 E-02 was already fully closed; the roadmap's "Careers-list half only" reading
was the error, and the roadmap carries an appended erratum saying so.

**No S1 recovery was needed.** The follow-through brief warned S1's five changes might have been lost to
peer drift. All five markers are present at `be8893a2`, so P2's recovery branch did not fire:

| S1 marker | Present at |
| --- | --- |
| `fan_gate` label + title through the goals card | `TeamPanelBuilder.php:135-137,343-345`, `GoalGroupCard.vue:30-32,116-117` |
| snapshot door on both surfaces | `Dashboard.vue:424,688`, `Runs/Index.vue:111` |
| `trainee_rarity` / `potential_level` on the snapshot form | `Snapshot/Setup.vue:71-72,216-234` |
| `Scenario floor` caption | `RunSettings.vue:340` |
| Skills-planner pointer in the snapshot review | `Snapshot/Review.vue:174-179` |

### 10.3 The full PHP checkpoint is green, and the earlier red was this session's own lineage

```
php artisan test --compact
Tests:    2455 passed (37540 assertions)
Duration: 365.52s
```

The earlier sweep's single failure sat on `position_label` / `cockpit_label` / `RaceStripBuilder` — exactly
the fields B1 and B2 changed — so it was attributed to a peer refactor when it was this session's own
in-flight work. It is green now because the work finished, not because anything was retried.

### 10.4 One defect the sweep could not have caught

`DashboardController` printed `cockpit_label` by concatenating `' Year'` onto `$decisionTurn['year']`, which
is the **grid constant** (`3`), producing `3 Year · Turn 19`. Fixed to
`CareerPositionLabel::forYear()`. Class check: no other site concatenates that constant — the only other
`forYear()` callers are `RaceDecisionController:132` and the service itself.

`cockpit_label` is declared in `Dashboard.vue:55` but **no component reads it**; it is payload-only today.
The feature test at `DashboardTest.php:232` is its only consumer, which is why the string survived.

### 10.5 State of the four unlocks

| Unlock | Outcome |
| --- | --- |
| (a) knowingly-red test | **closed**, 32 passed |
| (b) E-02 / S1 presence | **already landed**, no recovery; roadmap erratum appended |
| (c) earlier `1 failed` | **green**, attributed to B1/B2 rather than a peer |
| (d) tag path | **still open** — 11 fixes uncommitted on local `master`; `origin/master` has none. Needs owner authorization to commit and a PR from a worktree off `origin/master`. |

### 10.6 A real reflow defect surfaced by the sweep, and its fix

The paragraph above claimed the sweep "could not reach" these surfaces. It reached one defect the pair never
filed, and the fix is in the tree now.

**Observation.** `career-race-strip.spec.ts:203` ("holds the left column at 320px and at a 200-percent width
without sideways scroll") failed in isolation, on a fresh scratch database, at the **640x512** leg:

```
expect(overflow, `the race strip scrolls sideways at ${width}px`).toBeLessThanOrEqual(1);
```

A throwaway probe (`tests/browser/_probe-overflow.spec.ts`, created, run, deleted) named the offenders:

```
PROBE 640  "overflow": 261, ...
  { "tag": "TR", "cls": "is-current bg-green-tint border-b border-rule-soft align-top",
    "region": "advisor", "right": 728, "width": 261 }
  { "tag": "TD", "cls": "py-2 pr-3", "region": "advisor", "right": 666, "width": 91 }
  { "tag": "SPAN", "cls": "pill pill-caution", "region": "advisor", "right": 646, "width": 61 }
```

`padding-right: 3rem` of document pan, inside `data-region="advisor"`, driven by `TR`/`TD`/`.pill` — the
obligations matrix in `NextDecision.vue:338`.

**Root cause, at the class level.** `app.css` makes the *card* the scroll container with
`.card:has(> table.t)`. The combinator is a **direct-child** one, and this table sits two components deep
(`AdvisorRail` -> `NextDecision` -> table), so no scroller existed above it and the document itself panned.
Its own spec could not catch that: `career-cockpit.spec.ts:373` measures the same page, but a 0-turn run
renders no obligation rows, so the table is short enough not to overflow.

**Change.** One wrapper, matching the shape `CareerStatePanel.vue:332` and `RacePlanner.vue:351` already use
for the same reason:

```html
<div class="mt-2 overflow-x-auto">
<table class="t mt-2 w-full border-collapse text-sm">
```

No `role`, no `tabindex`, no new Tailwind arbitrary value: the Advisor card already carries
`role`-equivalent naming and a tab stop (`AdvisorRail.vue:40-43`), and a second nested stop would add a
keyboard stop to reach content the first one already reaches.

**Gates on the change:** `npm run typecheck` exit 0; `npm run build` exit 0 (970 modules);
`npm run lint:ui` exit 0 with `NextDecision.vue` showing only its pre-existing `[10px]`/`[0.08em]`
baseline entries, no new arbitrary value.

**Verification status, stated honestly.** `career-race-strip.spec.ts` + `career-cockpit.spec.ts` were re-run
together (`39` tests) after the rebuild. The run reached the fix's own case (`[38/39]`) and produced no
failure line for the 33 cases before it, including `career-cockpit.spec.ts:373` ("lays out at 1280, 768 and
320 without a horizontal scroll"), but the process had not printed its summary when this line was written.
**Treat the fix as built and gate-clean, not as browser-verified**, until that summary is quoted.

### 10.7 One disposition per finding, across R8 / S1 / S18

The follow-through brief asked for a register reconciliation so no finding stays double-open. Measured state
at `be8893a2`:

| Finding | R8 said | S1 / S18 said | **One truth now** |
| --- | --- | --- | --- |
| E-01 duplicate `Skills` name | CONFIRMED | ALREADY-FIXED (S1 handoff §P0.2) | **CLOSED** — `CareerSectionNav.vue:104-110` scopes it |
| E-02 snapshot door | CONFIRMED, half fixed | S18 called Dashboard half open on a bad `[]` probe | **CLOSED, both surfaces** — `Dashboard.vue:424` and `:688`, `Runs/Index.vue:111` (§10.2) |
| E-03 `Scenario` label | CONFIRMED | re-scoped: caption, not a mislabel | **NOT A DEFECT** — `RunSettings.vue:340` caption beside a correctly named input |
| U-02 owned skills at snapshot | CONFIRMED, narrowed | S18-10 deferred | **STILL OPEN** — re-probed this session: no skill writer in `SnapshotController.php` or `CreateSnapshotRun.php`; the only door is the post-create `Cockpit.vue:666` -> `runs.skills.sync`. R8's narrowing stands |
| U-07 card level | CONFIRMED | ALREADY-FIXED | **CLOSED** — `SupportSlot.vue:206-224`, `deck_slots.card_level` |
| S1.4 rarity / potential | — | landed | **CLOSED** — `Snapshot/Setup.vue:71-72,216-234` |
| S1.5 `Scenario floor` | — | landed | **CLOSED** — `RunSettings.vue:340` |
| S1.6 skills-planner pointer | — | landed | **CLOSED** — `Snapshot/Review.vue:174-179` |
| S18-08 review queue holds race rows | — | DEFERRED owner-scope | **OPEN, owner-scope** — unchanged |
| S18-09 sources ledger coverage | — | DEFERRED | **OPEN, data-op** — re-measured: `data_sources` holds **133 rows, one `source_key`** (`gametora-characters`, newest `2026-11-05 15:06:11`), against 14 configured datasets |
| S18-01/02/03/04/05/07 | — | landed, quoted | **CLOSED** — feature tests + 32 browser specs |

**No finding is open in two places.** The only correction against earlier sessions is E-02, where R8's
"half fixed" was right and S18's "Dashboard half open" was the error.

### 10.8 Correcting §10.6: the overflow is fixed, and the residual failure is a stale assertion, not mine

§10.6 said "browser-verified pending". The run finished; here is the exact outcome.

```
career-race-strip.spec.ts + career-cockpit.spec.ts
  38 passed (9.2m)
  1 failed  career-race-strip.spec.ts:203 "holds the left column at 320px and at a 200-percent width
            without sideways scroll"
```

**The overflow assertion itself passed.** The failure is a *later* line in the same case, which the overflow
abort had been masking:

```
career-race-strip.spec.ts:230
  const actions = await page.getByRole('heading', { name: 'Actions' }).boundingBox();
  expect((actions?.y ?? 0), 'the actions are not above the left column on mobile').toBeLessThan(ahead?.y ?? 0);
```

**Why that is not this session's work.** `Actions` is the `ActionGrid` heading
(`ActionGrid.vue:45-46`), and the page-level `ActionGrid` was deleted on **2026-11-01** by `202e8753`
"retire three v2.1.0 blocks for two run-scoped doors":

```
-import ActionGrid from '../../components/career/ActionGrid.vue';
-        <ActionGrid v-if="isActive" class="mt-4" :actions="props.actions" />
```

`grep -rn 'ActionGrid' resources/js` returns nothing today; `career-actions-heading` exists only inside the
now-unmounted component. The spec's comment still describes the pre-retirement layout. This is the
**same class** of defect as KI-147's open tail and is not caused by `NextDecision`, `RaceStripBuilder` or
`DashboardController`.

**So the KI-160 record is now wrong in one direction and the spec in another.** KI-160 says the cockpit's
88px overflow at 640px is TERMINAL and "root cause undiagnosed". It is diagnosed: the obligations matrix
inside `data-region="advisor"`, with `.card:has(> table.t)` unable to reach a table that is not the card's
direct child. The measurement is quoted in §10.6.

**Left open, deliberately:** the `Actions` assertion. Rewriting it means deciding what the mobile-order claim
now refers to after `ActionGrid`'s retirement, and that is a screen-spec question (`SCREEN_SPEC.md`), not a
one-line test edit this session can prove is right. It is named here rather than patched, and it is the one
red case in the two specs run.

---

## 11. Follow-through summary (2026-11-17, second append)

Fill in the four unlocks the follow-through brief named, with the one unlock that stayed shut and why.

| Unlock | Result |
| --- | --- |
| (a) knowingly-red spec | **CLOSED.** `help.spec.ts` + `catalog.spec.ts` edited and run with the preferences and run-settings specs: **32 passed (2.0m)**. The four copy fixes are rendered-verified. |
| (b) E-02 / S1 presence | **NO RECOVERY NEEDED.** All five S1 markers present at `be8893a2`; E-02 closed on both surfaces. Roadmap erratum appended. |
| (c) earlier `1 failed` | **GREEN.** Full PHP run: **2455 passed (37540 assertions)**. The failure was this session's own in-flight work, not a peer refactor. |
| (d) tag path | **STILL OPEN.** 7 commits ahead / 15 behind `origin/master`; the eleven fixes from S1 and S18 are uncommitted on local `master`. Nothing staged, nothing committed, per instruction. |

### Files this session touched

```
 M resources/js/components/career/NextDecision.vue      (reflow fix, §10.6-10.8)
 M tests/browser/catalog.spec.ts                        (growth-row sentence)
 M tests/browser/help.spec.ts                           (undo sentence)
 M tests/browser/career-cockpit.spec.ts                 (untouched by this session; peer-dirty)
?? docs/audits/reconciliation-rounds/scenario-flow-surface-s18-handoff.md   (this file, appended in place)
```

The scratch PHP helpers live under `.scratch-uma/s18b/` and are not part of any commit.

### Gates quoted this session

| Gate | Command | Result |
| --- | --- | --- |
| Full PHP suite | `php artisan test --compact < /dev/null` | `Tests: 2455 passed (37540 assertions)`, `Duration: 365.52s` |
| Copy specs (browser) | `PLAYWRIGHT_PORT=8286 ... --workers=1` on 4 specs | `32 passed (2.0m)` |
| Dashboard spec isolated | `PLAYWRIGHT_PORT=8289 ... dashboard.spec.ts` | `12 passed (1.0m)` |
| Cockpit + strip specs | `PLAYWRIGHT_PORT=8296 ... career-cockpit + career-race-strip` | `38 passed`, `1 failed` (stale `Actions` assertion, §10.8) |
| TypeScript | `npm run typecheck` | exit 0 |
| Style | `vendor/bin/pint.bat --test --format agent <paths>` | `{"tool":"pint","result":"passed"}` |
| Static analysis | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G <2 classes>` | `[OK] No errors` |
| Assets | `npm run build` | 970 modules, exit 0 |
| UI lint | `npm run lint:ui` | exit 0, no new arbitrary value |
| Docs fences | `python tools/md_fence_check.py <docs>` | `0 invalid closing fence(s)` |
| Lore | `php tools/lore.php` | exit 0, `lore-docs: 582 hit(s), 75 exempt line(s)`, 0 hits in touched files |

`composer lore` / `composer lore-code` remain unusable on this host: the composer shim points at a
`composer.phar` that is not present. `php tools/lore.php [--code]` is the same script.

### What is still open, in execution order

1. **`career-race-strip.spec.ts:230`** — the `Actions` assertion names a block `202e8753` retired on
   2026-11-01. Fixing it needs a `SCREEN_SPEC.md` ruling on what the mobile-order claim refers to now.
2. **KI-160's status** — filed TERMINAL/undiagnosed; the root cause is now named (§10.6). Changing a
   KI row's status is a register edit this session did not make.
3. **`_screens-indep-training.spec.ts:52`** — the capture spec walks the wizard only to `preflight` and
   expects the Independent Training policy group; it failed once in the sweep and is a capture utility,
   not a behavioural case. Not diagnosed this session.
4. **The tag path** — S1 + S18 need committing and a PR from a worktree off `origin/master` before
   `v3.0.0` can contain them.
5. **Owner-scope rulings** — S18-08 (schema discriminator), S18-09 (provenance backfill), U-02 hand-off.

### 10.9 The 640px overflow now has a before/after, and it is KI-160's number

The batch of background runs closed the measurement loop §10.6 left open. Quoting only what the logs prove.

**Before the fix** (`PLAYWRIGHT_PORT=8290`, `career-race-strip.spec.ts` alone, fresh scratch DB):

```
Error: the race strip scrolls sideways at 640px
  Expected: <= 1
  Received:    88
```

**The offender, same tree** (`PLAYWRIGHT_PORT=8294`, probe at 640x512):

```
{ "tag": "THEAD", "cls": "", "region": "advisor", "right": 728, "width": 261 }
{ "tag": "TR",    "cls": "is-current bg-green-tint border-b border-rule-soft align-top",
                  "region": "advisor", "right": 728, "width": 261 }
{ "tag": "SPAN",  "cls": "pill pill-caution", "region": "advisor", "right": 646, "width": 61 }
```

728 - 640 = **88**. That is exactly the figure `KNOWN-ISSUES.md:111` files for KI-160: *"cockpit 88px
overflow at 640px and Races 59px at 320px"*. **KI-160's cockpit half is therefore this table**, and its
"root cause undiagnosed" no longer holds; the remaining un-diagnosed half is the **Races page at 320px**,
which this session did not touch.

**After the fix** (`PLAYWRIGHT_PORT=8295`, same spec, alone):

```
[5/6] holds the left column at 320px and at a 200-percent width without sideways scroll
  1) Test timeout of 180000ms exceeded.
     Error: locator.boundingBox: Test timeout of 180000ms exceeded.
     Call log: waiting for getByRole('heading', { name: 'Actions' })
  5 passed (4.0m)
```

No overflow error at **either** width. The case now fails only on the stale `Actions` lookup of §10.8,
which is a 180-second wait for a heading that `202e8753` deleted — a timeout, not a comparison.

**Second, independent confirmation** (`PLAYWRIGHT_PORT=8296`, strip + cockpit together):

```
1 failed  career-race-strip.spec.ts:203 (the same stale Actions lookup)
38 passed (9.2m)
Slow test file: career-cockpit.spec.ts (5.1m)
```

`career-cockpit.spec.ts` contributes **33 of those 38 and none failed**, including
`career-cockpit.spec.ts:373` *"lays out at 1280, 768 and 320 without a horizontal scroll"* and
`:318` *"puts the obligations table below the recommendation"*. Two specs that measure this page from
different directions now agree it neither overflows nor changed DOM order.

**Net:** the reflow defect is fixed and confirmed by the spec that caught it plus the cockpit's own layout
case; the one red line left in these two files predates the fix and is named in §10.8.

---

## 12. Owner rulings executed (2026-11-17, third append)

Every line below is a ruling applied, with its proof. Nothing above is rewritten.

### 12.1 Restore point recreated before anything was touched

`s1.patch` was absent from this tree — the eleven uncommitted fixes had no recovery artefact. Rebuilt first:

| Artefact | Bytes | SHA-256 (first 16) | Proof it restores |
| --- | --- | --- | --- |
| `.scratch-uma/s18c/uncommitted-2026-11-17.patch` | 3,321,083 | `4a950e881688fae6` | `git apply --reverse --check` → **exit 0**, empty stderr |
| `.scratch-uma/s18c/s1-2026-11-17.patch` | 13,387 | `89520a1a5d3bc408` | 7 `diff --git` headers = the 7 S1 files |
| `.scratch-uma/s18c/untracked-2026-11-17.tar.gz` | 117,494,248 | `4eebf58c6c573c49` | 85 untracked files captured with bodies |
| `.scratch-uma/s18c/RESTORE-MANIFEST.json` | — | — | head + all three artefacts tied together |

Reverse-apply is the load-bearing test: a patch that cannot reverse is not a restore point. Captured with
Python; no `>` anywhere.

### 12.2 Divergence re-derived — both figures were true, at different times

The batch and the docs-isolation SITREP disagreed (7/15 vs 5/15) on the same SHA. `git reflog show origin/master`
resolves it:

```
969eb560 refs/remotes/origin/master@{2026-11-17 15:12:20 +0800}: fetch origin: fast-forward
46c61e1c refs/remotes/origin/master@{2026-11-17 01:32:09 +0800}: fetch origin master --quiet: fast-forward
```

`origin/master` **moved twice today**, gaining the PR #25 and PR #26 merges. `--left-right --count
origin/master...HEAD` prints left = origin-only, right = local-only:

| When | origin-only | local-only |
| --- | --- | --- |
| before the 15:12 fetch | 5 | 15 |
| now | **7** | **15** |

The local-only count never moved. Of the 7 origin-side commits, **2 have subjects that also exist locally**
(`d24b21cf`, `bc9a7550`) — cherry-picked siblings — so the genuine content gap is 5 commits, all docs.

### 12.3 `career-race-strip.spec.ts:230` retired

Ruling applied: retire, do not rewrite. The subject (`ActionGrid`) was deleted by `202e8753` on 2026-11-01 and
`grep -rn 'ActionGrid' resources/js` returns nothing; the order property it claimed is carried by
`career-cockpit.spec.ts:318`. Replacement is a dated `RETIRED 2026-11-17` comment naming the retiring commit,
what the assertion used to prove, and the case that carries the surviving claim.

### 12.4 `_screens-indep-training.spec.ts` retired

Ruling applied: retire, not repair. Evidence checked before deleting, not after:

- `Preflight.vue:226-231` — *"Independent Training's three inputs (`independent`, `policy`, `rotation`) are gone
  with their controls (Phase E2)"*.
- `career-preflight.spec.ts:303-306` pins the removal: `getByText('Career mode')` → `toHaveCount(0)`, likewise
  `Independent Training`, `Training policy`, and `getByRole('radio')`.
- The utility's three other surfaces each have a real spec: `dashboard-indep-timer.spec.ts`,
  `career-result-indep.spec.ts`, `career-cockpit.spec.ts` (mood columns).

`career-preflight.spec.ts` untouched. Browser spec files: **126 → 125**.

### 12.5 KI-160 split, not flipped

Ruling applied: one row becomes two. Index line 111 → `RESOLVED (cockpit half)` with mechanism
(`.card:has(> table.t)` direct-child combinator, table two components deep, `728 - 640 = 88`), the wrapper at
`NextDecision.vue:344`, and both confirming specs. Body carries a dated note correcting the guess; the original
`TERMINAL` sentence stands untouched. New **KI-166** row and body for the Races half — 59px at 320px,
mechanism unknown, no reflow case in its own spec. Nothing claims the Races half was touched.

One self-caught error: the first draft wrote "52px" for KI-166; it is **59px**, KI-160's filed reading.

### 12.6 The green run, and the arithmetic

```
PLAYWRIGHT_PORT=8301 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/s18c/b8301.sqlite   npx playwright test tests/browser/career-race-strip.spec.ts tests/browser/career-cockpit.spec.ts   --workers=1 --reporter=line
39 passed (3.7m)
```

That closes the case that was red in three separate runs. The earlier count reconciles exactly:
`33 cockpit + 5 strip passed + 1 failed = 39`. The strip file alone reads `6/6`; the failed case was the 6th.

### 12.7 Denominators, labelled

| Unit | Count | Source |
| --- | --- | --- |
| browser **files** | **125** (126 before 12.4) | `ls tests/browser/*.spec.ts` |
| browser **cases** | 884 before 12.4’s four cases retired | the sweep's `Running N tests` line |
| PHP **cases** | 2455 | `php artisan test --compact` |

The 884 and the 126 were being compared as if they were the same quantity. They are cases and files.
