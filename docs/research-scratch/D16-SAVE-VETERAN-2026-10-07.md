# D16 spec — Save Veteran + Veteran comparison (2026-10-07)

SCREEN-020 (Save Veteran) and SCREEN-022 (comparison). Authority: landed ADRs > AGENTS.md >
`docs/proposals/frontend-development-plan.md` > the two design briefs.

## What already exists (do not rebuild)

The **read half of D16 landed 2026-10-06** as `SCR-VET-001`/`SCR-VET-002`:

| Thing | Where |
| --- | --- |
| Library list, filters, order, absences | `VeteranController::index()`, `ListVeterans`, `Veterans/Index.vue` |
| One veteran's detail | `VeteranController::show()`, `ShowVeteran`, `Veterans/Show.vue` |
| Shared row shape, one owner | `App\Services\Legacy\VeteranRow::from()` |
| The write itself (no caller) | `App\Actions\RecordVeteran::handle()` — upserts on `training_run_id`, throws for a non-Completed run |
| Ancestry configuration compare (4 runs, needs a read-back) | `LegacyController::compare()`, `LegacyCompareRequest`, `Legacy/Compare.vue` |
| Entry point that currently names this screen absent | `Career\ResultController::saveVeteranSection()` → `['available' => false, 'reason' => …]` |

So D16's remaining work is the **write half** and a **career comparison**, not four pages.

## Decision 1 — why a second compare surface is not a duplicate

`LegacyCompareRequest` compares runs **that carry a Legacy read-back** and refuses one that has none, "a
column of absences is a screen that looks compared and is not". A career comparison must accept a filed
career with no read-back at all — that is the common case. Different eligibility rule, so different
selector and page; the columns it shares come from `VeteranRow`, which already owns the shape.

Not done: adding career columns to `Legacy/Compare.vue`. That screen is `SCR-CAR-006`, landed and tested,
and broadening it would widen another slice's recorded contract.

## Decision 2 — one `tags` field, not two

The brief asks for suggested tags the Trainer toggles **plus** custom tags. `veterans.tags` is one flat
json list and `ListVeterans` searches that list with `whereJsonContains`. Two stored arrays would be two
shapes for one column and would make the library's tag filter wrong. So: one `tags` list; suggestions are
a toggle UI affordance that appends to it, and a free-text input appends to it too.

## Decision 3 — suggested tag vocabulary gets one owner

`screen-spec-2.0` §24 names 18 tags. Today the pieces live split between `lang/en/uma.php` `terms`
(stats, styles) and `config/uma.php` `fit_distance_type`/`fit_surface_type`, and no key holds the list.
Adding `config('uma.veteran.suggested_tags')` as the one list, cited to §24. Custom tags are still allowed:
the list is a suggestion, so validation bounds shape and length, it does not allow-list membership.

## Routes (following the `runs.inheritance` / `runs.inheritance.store` pair)

| Method | URI | Name |
| --- | --- | --- |
| GET | `/training-runs/{run}/veteran` | `runs.veteran` |
| POST | `/training-runs/{run}/veteran` | `runs.veteran.store` |
| GET | `/veterans/compare` | `veterans.compare` |

`/veterans/compare` is declared **before** `/veterans/{veteran}`, the documented ordering trap
(`LegacyLabPageTest:457` for the `/legacy/compare` pair). `{veteran}` also gets `whereNumber`.

## Props contract

`Career/SaveVeteran.vue` (GET `runs.veteran`):

- `run`: `{id, trainee, trainee_ja, scenario_label, status, status_label, recordable}`
- `career`: `{turns, energy, fans}` from `stripValues()`
- `stats`: `{Speed, Stamina, Power, Guts, Wit}`, each `int|null` from the latest logged turn
- `counts`: `{skills, races}`
- `graph`: `AncestryGraph::build(...)` — the recorded sparks, read-only, reviewed before tagging
- `spark_kinds`: `AncestryGraph::SPARK_KIND_LABELS`
- `suggested_tags`: `list<string>` from config
- `saved`: `{tags, notes}|null` — an existing row means this screen is editing it
- `held`: `{factor_analysis, legacy_value, best_use}`, each `{value: null, title}` citing `ADR-0020` §3
- `notice`: `LegacyController::RECORD_ONLY_NOTICE`
- `absences`: `list<{label, reason}>`
- `library_url`, `result_url`
- `blocked`: `string|null` — the refusal copy when the run is not Completed

`Veterans/Compare.vue` (GET `veterans.compare`):

- `columns`: `list<array>` — `VeteranRow::from()` fields plus `career`, `stats`, `counts`, `aptitudes`
- `max`: `VeteranCompareRequest::MAX_VETERANS` (4)
- `comparable`: `list<{id, label}>` the picker offers (filed careers only)
- `selected`: `list<int>`
- `notice`, `absences` (inheritance usefulness, compatibility, scenario factor, rating)

## Validation (the one trust boundary in the slice)

`StoreVeteranRequest`, which owns it alone:

- `prepareForValidation` trims each tag, drops empties, dedupes case-insensitively.
- `tags`: `nullable|array|max:20`; `tags.*`: `string|min:1|max:40` plus a control-character refusal.
- `notes`: `nullable|string|max:2000`.
- `after()`: refuses a run whose status is not `Completed` as a **validation error**, so `RecordVeteran`'s
  `InvalidArgumentException` stays a domain guard and never reaches a Trainer as a 500.
- `authorize()` returns true: the repo has no auth surface by design (`PRD` NFR-1, `AGENTS.md` §12), which
  overrides the security skill's "authorization on every protected endpoint". No policy is added.
- Notes and tags render through `{{ }}` only. No `v-html`, ever.

## Held, named on screen, never invented

Factor analysis, legacy value, best-use recommendation (`SCREEN-020`'s correction row), compatibility
calculation and inheritance usefulness (`SCREEN-022`'s correction row), and favorite / archive / delete
(`SCREEN-021`) — the last three because **no column holds them** and adding one is a stored-shape change
for the schema owner, not a UI slice. C3 provides none, so per the brief they are listed as not built.

## Tests

- `tests/Feature/SaveVeteranTest.php`: the read screen's props; a Completed run files; tags and notes land;
  re-saving upserts one row; an Active run is refused with a field error not a 500; oversized and
  control-character tags refused; no recommendation string present anywhere in the payload.
- `tests/Feature/VeteranCompareTest.php`: up to four columns; a fifth refused; order preserved as posted;
  a filed career with no read-back still gets a column; held columns render as `N/A` with a title.
- `tests/browser/career-save-veteran.spec.ts`: tag toggle by keyboard with `aria-pressed`, the empty
  library state after the copy change, the save round trip, the 44px sweep, axe A+AA on `#app`.

## Propagation — the claims this slice makes false

`VeteranController.php:73` `filingNotice`, `VeteranController.php:140` comparison absence,
`ResultController::saveVeteranSection()`, `Result.vue:354-357`, `CareerResultTest.php:205-214`,
`veterans.spec.ts:10` and `:94`, `SCREEN_SPEC.md:2062` and the `SCR-VET` rows. Grep the retired literals
after editing, not just in the file edited.

## Open questions for the owner

1. `veterans` has no name column. `SCREEN-020` lists "veteran name"; the name prints as the trainee's, read
   through the run (`ADR-0010`). If the Trainer should name a career independently, that is a column.
2. One Compare button per library row today points at the ancestry surface. Repointing it at the career
   compare keeps a single affordance and is the change assumed here; the alternative is two labelled
   buttons, which is a wider row and a harder choice.
3. Whether `SCR-VET-003`/`004` are the right row ids for these two screens.
4. `RecordVeteran` throws `InvalidArgumentException`; this slice makes it unreachable from the UI. Leaving it
   is fine, but it is now a guard with no test path from a request.

---

# Built 2026-10-07

## What shipped

`SaveVeteranController::{show,store}`, `StoreVeteranRequest`, `VeteranCompareRequest`,
`pages/Career/SaveVeteran.vue`, `pages/Veterans/Compare.vue`, `VeteranController::compare()`,
`config/uma.php` `veteran.suggested_tags`, `Umamusume::{APTITUDE_AXES, aptitudeAxes()}`, the Result door,
the library's repointed Compare link and its new "Find parents for this build" search, `SCR-VET-003`/`004`
with state tables, and four test files. `RecordVeteran` now has a caller, which is the fix for the
"nothing can file a career" fact KI-69 records as class 3's root.

## Deviations from the brief

1. **The four pages became two.** `Veterans/Index.vue` and `Veterans/Show.vue` landed 2026-10-06 as
   `SCR-VET-001`/`002`; rebuilding them would have been a second copy. Only `SaveVeteran.vue` and
   `Compare.vue` are new.
2. **The `screen-spec-2.0` §24 fields "parent suitability" and "intended use" are not built.** They are the
   brief's correction-row recommendation ("Excellent Medium parent. Best used for: Medium, Pace Chaser,
   Speed-oriented builds"), and `ADR-0020` §3 holds that computation. Each prints as a held figure with the
   ruling as its `title`, and the props test greps the payload for the two recommendation strings.
3. **Favorite, archive and delete are not built.** C3 provides no column for any of three, so per the brief
   they are listed as not built. There is therefore no destructive flow, no confirmation dialog, and no
   delete-confirmation browser case, and no migration was proposed.
4. **The Factors and Ancestry views are not added to the library.** The owner's 2026-10-06 ruling rendered
   them as named absences; that ruling was not reopened here. "Find parents for this build" was built,
   because the brief calls it a search and it is one: it hands the library's own tags to the Legacy Lab's
   existing filter form and applies no score.
5. **The aptitude axis list got a model owner but not a full sweep.** Three landed controllers and
   `AptitudeGrid.vue` still map the ten axes by hand. Folding them in is a refactor across screens this
   slice does not own, so it is named rather than done.

## Adversarial review: what it changed

Twelve defects found; nine accepted and fixed, three rejected on evidence.

Fixed: the tick glyph, which `ProvenanceBadge` owns and `CareerBuildTargetTest` sweeps for (it survived once
more in my own comment text, which the sweep reads); two count labels that claimed a filter the query does not
apply, now "Skills recorded" and "Races entered" on both new pages; `aria-describedby` aimed at a span inside
its own button; a dead conditional `id`; a live region that announced tag toggles not at all; three
`maxlength` literals restating the request's constants, now served as `limits` props; a stale second docblock
stacked on the Result door; a Form Request docblock claiming a field error the template makes unreachable
through the GET; and `pairing` in a user-visible reason string, reworded to "parent combination" because
`SLICE-RECORDS.md` records that word being reworded rather than ruled.

Rejected with evidence: literal URLs in the two pages, which plan §2 requires ("There is no Ziggy, so pages
state literal URLs"); the `<div>`-wrapped `<dt>/<dd>` pairs, which are valid HTML; and the request-duplication
charge, because `veteransInOrder()` and `runsInOrder()` key on different models with opposite eligibility
rules — one shared helper would take a parameter for the thing that must differ. The copied prose was
rewritten so the two docblocks no longer repeat a sentence.

## Held open for the owner

1. **A wrong-state run gets a 200 with a refusal here, and a 404 in the Legacy Lab.** `LegacyController::builder`
   and `StoreLegacySelectionRequest` refuse a run of the wrong status by aborting; `runs.veteran` renders the
   reason instead, because §13 asks for a rendered error state and the Result screen links unconditionally.
   Both defensible, and the tree now holds both. Pick one.
2. **`Front runner` versus `Front Runner`.** The aptitude axis labels, copied from the Result screen and used
   identically by three other owners and one browser assertion, disagree with `lang/en/uma.php`'s style words,
   which §13 nominates as the owner of displayed vocabulary. Not decided here because changing one casing
   breaks another session's spec.
   **Ruled and landed 2026-10-07, same day:** `Umamusume::APTITUDE_AXES` now prints `Front Runner` and
   `Pace Chaser`, matching `lang/en/uma.php`'s `uma.terms.style_*`, with a `ponytail:` debt line naming the
   lang file as the eventual owner of these labels. Sweeping the tests found no assertion pinning either
   lowercase form, so the premise that a spec would break did not hold. Two things the ruling did not cover
   are named rather than done: `Late surger` / `End closer` at `:143-144` carry the identical defect, and
   `AptitudeGrid.vue:19-20` plus `TraineeSelectController.php:101-102` still hold the lowercase pair. Those
   are other sessions' files, held out of this slice.
3. **`Show.vue` labels the same skills count "Skills learned"** while this slice labels it "Skills recorded".
   The read half's string is the inaccurate one; fixing it is the read half's owner's edit, not a silent one.
4. **The validation bounds (20 tags, 40 characters, 2000 note characters) are chosen, not sourced.** The repo's
   precedent is the same: `StoreTrainingRunRequest` caps notes at 5000 and `StoreTurnEventRequest` at 1000,
   none published by any source. They are length ceilings on free text, not statistics, and they now travel to
   the page as props so the boundary and the form cannot disagree.
5. **Case-insensitive search does not reach stored tags.** `prepareForValidation()` de-duplicates by lowercase,
   but `ListVeterans` matches with `whereJsonContains`, which is case-sensitive in SQLite. A hand-typed
   `speed` is stored and is not findable by the `Speed` suggestion. The fix is a normalized companion column,
   which is a schema decision, not a UI one.

## Gate inventory, 2026-10-07

Pint was scoped to this slice's own paths rather than `--dirty`, because three sessions share the tree.

| Gate | Result | Evidence |
| --- | --- | --- |
| Targeted props tests | green | `SaveVeteranTest` 16 cases + `VeteranCompareTest` 7, inside `tests/Feature/…` run: 64 passed / 707 assertions with the four neighbouring suites |
| `php artisan test --compact` | **1517 passed, 2 skipped, 0 failed**, exit 0 | 26141 assertions, 524s |
| `vendor/bin/pint --test` | pass, exit 0 | 6 files on the scoped list |
| PHPStan level 6 | exit 1, **3 errors, none in this slice** | all three are `renderPage()` missing an array shape in `CatalogController:73`, `SkillController:62`, `SupportCardController:52`; `git diff HEAD` shows `renderPage` is an uncommitted addition in all three, and none is in this slice's changed set |
| `npm run typecheck` | exit 0 | `tsc --noEmit` |
| `npm run build` | **BLOCKED, exit 1** | `vue/compiler-sfc` parse error at `resources/js/pages/Preferences/Edit.vue:81`, another session's uncommitted stray brace; HEAD's copy parses. Not this slice's file, not edited |
| `npm run test:browser` | **NOT RUN** | follows from the build: a page absent from `public/build/manifest.json` cannot be opened. `career-save-veteran.spec.ts` (5 cases) and `veteran-compare.spec.ts` (4 cases) are written, unexecuted |
| `composer lore` | exit 0 | `lore-docs: 257 hit(s), 77 exempt` |
| `composer lore-code` | exit 0 | `lore-code: 115 hit(s)`; session baseline was 112 before this slice, and the +3 is the new pages' `factor` and `record` vocabulary |
| `composer audit` / `npm audit` | not run | no dependency changed |

### Rulings on the lore hits this slice adds

- `factor`, in `Factor analysis` and `A factor comparison` on the Save Veteran and comparison screens —
  **allowed.** It is `screen-spec-2.0` §24's own name for the workflow the slice refuses, and the landed read
  half already ships `The Factors view` and `the factor inventory` in the same sentences
  (`SCR-VET-001`), so the two halves stay consistent. Naming the held computation to refuse it is the
  "naming the list to forbid it" class.
- `record`, in "Skills recorded" and "Races entered" — **not a hit**; listed here only because the count moved.
  The labels were changed *away* from "learned"/"run", which claimed a filter the query does not apply.
- `pairing`, in a comparison absence reason — **reworded, not ruled.** `SLICE-RECORDS.md:1264` records the
  precedent that this word was reworded rather than filed as an allowed hit, so the string now reads
  "prices a parent combination".
- No em dash appears in any shipped string on the two new pages; the four that exist in this slice's PHP are
  inside docblocks, which is the register's own house style.

### What would complete the owed gate

```bash
# 1. another session fixes its own file: delete the stray `}` at Preferences/Edit.vue:81
npm run build
# 2. a scratch database, not the port-8127 default that serves the shared dev file (KI-69)
DB_DATABASE="$PWD/.scratch-uma/d16.sqlite" SESSION_DRIVER=file php artisan serve --host=127.0.0.1 --port=8141 &
DB_DATABASE="$PWD/.scratch-uma/d16.sqlite" php artisan migrate --seed --force
PLAYWRIGHT_BASE_URL=http://127.0.0.1:8141 npx playwright test career-save-veteran veteran-compare
```

Both specs write rows, so the scratch harness is not optional here: run against the 8127 default and they
break `veterans.spec.ts`'s empty-library assertion, which is KI-69 class 1 with a new victim.

---

## Browser gate RAN, 2026-10-07 later the same day (this section supersedes the two rows above)

`npm run build` returned exit 0 once another session fixed `Preferences/Edit.vue`, so the gate ran. Four passes,
not one: 7 failed / 2 passed, then 7 failed / 2 passed with a different cause each time, then 3 failed / 2 passed
on a five-case probe, then the nine below. Final run, port 8147, `DB_DATABASE=$PWD/.scratch-uma/d20.sqlite`,
freshly migrated and seeded, 5 passed / 4 failed, `PLAYWRIGHT_EXIT=1`, 8.1m.

| # | Case | Result | Cause |
| --- | --- | --- | --- |
| 1 | files a career from the keyboard, library reads the tags back | pass (1.1m) | |
| 2 | names the held figures as held, prints no recommendation | pass (42.0s) | |
| 3 | refuses a career that has not finished | pass (22.0s) | |
| 4 | save screen controls at 44px | pass (29.9s) | |
| 5 | axe A+AA on Save Veteran | pass (43.0s) | after the `ink-faint` fix below |
| 6 | lines careers up, reached from the library row | **fail** (1.8m) | product defect, left unfixed by the owner's stop condition |
| 7 | says nothing is selected before anything is picked | **fail** (15.9s) | `net::ERR_CONNECTION_REFUSED` |
| 8 | comparison controls at 44px | **fail** (12.9s) | `net::ERR_CONNECTION_REFUSED` |
| 9 | axe A+AA on the comparison | **fail** (3.1s) | `net::ERR_CONNECTION_REFUSED` |

Cases 7-9 are not findings: the `php artisan serve` on 8147 died during case 6's 60s wait, and every later
navigation was refused. Cases 1-5 of that pass had already completed, so the five greens are real. Nothing in
the run was re-attempted after this.

### What the browser found that the props tests could not

- **`text-ink-faint` on text, twice.** `<span lang="ja">` in `SaveVeteran.vue:226` and `Compare.vue:208` rendered
  the Japanese trainee name in `#988F87` on `#f8F8FB` — axe measured 2.99:1 against the 4.5:1 AA floor. The
  token is documented as non-text in `resources/css/app.css:49-53`, and every landed page uses `text-ink-muted`
  for the same span. Both were changed to `text-ink-muted`; the third surviving `ink-faint` in the SPA is
  `RaceCalendar.vue:217`, an `aria-hidden="true"` decorative plus sign, which is the token's stated purpose and
  is not a violation. A props test cannot see colour.
- **The Compare button does not reach the comparison.** Case 6's `waitForURL` reported the URL it actually
  landed on: `http://127.0.0.1:8147/veterans`. `Compare.vue:83-86` calls `router.get('/veterans/compare', {
  'veterans[]': picked.value })`; the key is already bracketed while the value is an array, so the parameter
  does not arrive as a `veterans` array, `VeteranCompareRequest` fails, and `redirect()->back()` follows the
  session's previous URL, which is the library. The URL literal at `:85` is correct and `VeteranController`
  passes the right `selected` prop; the defect is the param key. Unfixed, by the owner's stop condition.
- **The picker lets a Trainer un-pick the career they arrived with**, which leaves `picked` empty and the
  Compare button inert. Case 6 originally checked that same career, so the spec now checks the other one; the
  affordance itself is left as designed.

### Fixture and API defects in the two specs (fixed, none of them a product change)

1. `getByLabel('Status')` resolved to 7 elements on `runs.show` because the skill rows' labels read
   "Acquisition status". Scoping to `select[name="status"]` still hit 2, because `RacePanel.vue:241` carries a
   second one. Both fixtures now filter to the form that contains the `Change status` button.
2. `new buildAxe().scanWithin(locator)` is not this repository's helper: `tests/utils/accessibility.ts` exports
   a function taking the page, already scoped to `#app`. Both cases now call `buildAxe(page).analyze()`.
3. `getByRole('term')` does not resolve for a `dt` inside the `div` wrapper a definition list is allowed to
   use, so the held-figure assertions now address `page.locator('dt', { hasText })` and step to the sibling
   `dd`. The property asserted is unchanged: the value is `N/A` and its `title` cites `ADR-0020` §3.
4. Case 6 asserted `getByRole('heading', { name: 'Eishin Flash' })`, a role this page never prints: a career's
   name appears once, in its `th scope="col"`. It now asserts both `columnheader`s plus the section's own
   "2 careers side by side", and waits for two ids in the query before reading anything.
5. The 44px case measured the native checkbox box (24px, `h-6 w-6`) rather than the tap target. It now measures
   the wrapping `label`, which is `min-h-11`. **Measurement change, flagged for the owner's overrule**: if the
   contract means the input box itself, `Compare.vue:140` needs `h-11 w-11`, not a spec edit.
6. `fileCareer`'s docblock claimed it returned the trainee's name; it returns void. Corrected.

### Slice state after this pass

**Live, not closed.** The browser gate has run and is not green: 5 of 9 cases pass, 1 product defect is named
and deliberately unfixed, 3 cases were lost when the scratch server died, and the PHP suite has not been
re-run since the two class attributes and two spec files changed. `SCREEN_SPEC.md` carries the same state on
`SCR-VET-003` and `SCR-VET-004`.

## Re-run after the owner's rulings: 8 passed, 1 failed, exit 1 (6.7m)

Port 8150, `DB_DATABASE=$PWD/.scratch-uma/d22.sqlite`, fresh `migrate --seed`, `npm run build` exit 0 first, server
alive at the end of the run (`SERVER_ALIVE_AT_END`), so cases 7-9 are real passes this time rather than refusals.

| # | Case | Result |
| --- | --- | --- |
| 1 | files a career from the keyboard, library reads the tags back | pass 33.6s |
| 2 | names the held figures as held, prints no recommendation | pass 22.8s |
| 3 | refuses a career that has not finished | pass 16.5s |
| 4 | save screen controls at 44px | pass 27.4s |
| 5 | axe A+AA on Save Veteran | pass 36.8s |
| 6 | lines careers up, reached from the library row | **fail** 1.9m |
| 7 | says nothing is selected before anything is picked | pass 5.4s |
| 8 | comparison controls at 44px | pass 29.5s |
| 9 | axe A+AA on the comparison, with a column rendered | pass 58.2s |

**The `Compare.vue:86` fix worked, and the remaining failure is the spec's own URL pattern.** `waitForURL` reported
where it actually landed: `http://127.0.0.1:8150/veterans/compare?veterans%5B0%5D=3&veterans%5B1%5D=2`. That is the
comparison page carrying both ids, not the library, so the request is now satisfied and the defect that sent the
browser to `/veterans` is closed. What the assertion expected was `veterans[]=N&veterans[]=N`, which is the
server-rendered href on the library's door link; Inertia serialises the same array as **indexed** keys
`veterans[0]=3&veterans[1]=2`, and PHP builds the identical `veterans` array from either form. The one-line change
that closes case 6 is to match either serialisation, `veterans(%5B|\[)\d*(%5D|\])=` — **not applied**: the owner
capped this turn at three edits and one number.

Consequence to state plainly: no case in this pass asserts the two-column table's contents, because execution
stopped at line 103. The aligned rows, the `No tags recorded` cell and the second `columnheader` are therefore
unproven in a browser, and the comparison's own axe scan (case 9) ran with one column rendered, not two.

### Slice state, final for this session

`SCR-VET-003` is **landed green**: five of five cases pass, and the gate found and fixed a contrast defect the
props tests could not see. `SCR-VET-004` is **built, gate 8/9, one spec-side assertion open**: the navigation
defect is fixed at `Compare.vue:86`, and the case that proves the two-column render is one regex away. PHPStan
and Pest were not re-run after the `Umamusume.php` label edit; the sweep for the retired literals is in the
ruling note above.

## Method, kept because it is the reusable part (owner's ruling, 2026-10-07)

The value of this gate was not the number. It was four things, in order:

1. **Read several causes in one pass rather than the first one.** The 7-failure pass was decomposed into a
   strict-mode locator collision, a helper-API misuse, a role that does not resolve for `dt` inside a `div`,
   and a heading role the page never prints — one fix each, not four guess-and-rerun cycles.
2. **Let the browser find what the props tests structurally cannot.** Colour contrast and a client-side
   serialization defect are both invisible to `assertInertia`. `text-ink-faint` on text was a documented
   repo rule that only axe could enforce, and the `veterans[]` param key only misbehaves once a real Inertia
   visit builds the query.
3. **Exclude the hit that is not a defect.** The third `ink-faint` in the SPA is an `aria-hidden` decorative
   plus sign, which is the token's stated purpose. A gate that reports every occurrence of a pattern is an
   instrument, not a judgment; the finding here is the pair plus the exclusion.
4. **Stop at the stop condition.** The defect at `Compare.vue:85` was named with the URL the browser actually
   landed on as evidence, and left unfixed until the ruling came. Iterating to green would have replaced a
   real finding with a quiet one.

Related: `superpowers:verification-before-completion` (a claim is the command output), and the owner's standing
"Built, not landed" wording rule, which is why the gate state lived in `SCREEN_SPEC.md` rather than in chat.

