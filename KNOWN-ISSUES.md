# Known issues

Defects found during the scenario-aware design and component work that were **out of that
phase's scope**, so they are recorded here rather than fixed in passing. Each entry states
the command or file that proves it, not just the symptom.

Discovered 2026-09-27. None of these were introduced by the component work; the component
work is what made them visible, because the prototype phase had no running server to hit.

**Status (2026-09-29, Slice 10):** 17 issues filed. **14 resolved/closed** (KI-1–9, KI-11–14,
KI-18). **3 open**: KI-10 (ratio half), KI-15 (which Grade Point track applies), KI-17 (the
consecutive-race count cannot be derived from the log). Slice 10 filed nothing; it closed KI-18,
which `f7a59e8` had already fixed in Slice 9, and landed two register corrections against the
Slice 7 commit `94db315`: KI-15 was written into this file twice, so the filed count is 17 rather
than the 18 reported below, and the line below names `KI-1–9, KI-11–14` as 14 resolved when that
range is 13 — the closed set was 13 until KI-18 joined it. Both counts now come from the headings
in this file rather than from prose about them. Prior:
**Status (2026-09-28, Slice 8):** 18 issues filed. **14 resolved/closed** (KI-1–9, KI-11–14).
**4 open**: KI-10 (ratio half), KI-15 (which Grade Point track applies), KI-17 (the consecutive-race
count cannot be derived from the log), KI-18 (the burst roster prints tool identifiers as UI copy).
Nothing closed in Slice 8; it built the panels and filed what they exposed. Prior:
**Status (2026-09-28, Slice 7):** 16 issues filed. **14 resolved/closed** (KI-1–9, KI-11–14).
**2 open.** KI-10 is split: its schema half (the objective bucket) closed with `e103122`, and its
ratio half stays open because no source names the placement scaling. KI-15 is filed new: the three
Grade Point tracks have no sourced rule for choosing one, and the two Trackblazer guides disagree
about which track a sprint-only trainee belongs to.
Prior: **Status (2026-09-28, Slice 6):** Slice 6 closed KI-14's last residual as
correct-by-design, filed nothing, and moved no threshold; the two `lore-code` hits that arrived
with the concurrent session's review-queue work were ruled allowed under C-4 and written into
`docs/design-research/CONSTRAINTS.md` §3.2 with citations.

---

## KI-1 `x-layout` requests a Vite entry that does not exist — RESOLVED 2026-09-27

> **Resolved.** `layout.blade.php` now requests `resources/js/app.ts`, matching
> `vite.config.js` and the manifest keys. Verified by HTTP status rather than by reading the
> edit: `/training-runs` and `/review` returned **500** before the change and **200** after it,
> with `assets/app-*.css` and the JS entry both linked. `/umamusume` still returns 500, and that
> is **KI-2**, not this — the two were confusable because both presented as a blank page.
> Found while implementing the D-104 theme resolver: that script lives in this same `<head>`, and
> until KI-1 was closed the layout could not render at all, so the theme behaviour could not be
> verified through the real app on any page. Original text below, kept as written.

**Symptom.** Every page rendering through `x-layout` fails to load its stylesheet.

**Evidence.**

```
resources/views/components/layout.blade.php:7    @vite(['resources/css/app.css', 'resources/js/app.js'])
public/build/manifest.json keys                  resources/css/app.css, resources/js/app.ts
vite.config.js input                             resources/js/app.ts
```

There is no `resources/js/app.js` on disk and no such manifest key, so `@vite()` throws
`ViteManifestNotFoundException`. `resources/views/welcome.blade.php:15` gets this right and
requests `app.ts`, which is why the bug is confined to the layout rather than being global.

**Cause.** The entrypoint was renamed to TypeScript at some point and the layout was not
updated with it.

**Fix.** One word: `app.js` to `app.ts` at `layout.blade.php:7`.

**Why it is not fixed here.** It is another phase's file, and it is currently masked by
KI-2, so fixing it alone would not make any page load. Fix the two together and verify
`/umamusume` returns 200.

---

## KI-2 Catalog pages die on a cache that cannot hand back models — RESOLVED 2026-09-28

> **Resolved in `d53a4b1`.** The diagnosis below was written as a guess and both
> halves of it were wrong, so they are corrected here rather than quietly dropped.
>
> - **Not** "the controller hands the view a collection of strings, or a
>   `pluck()`-style list". The controller returned a real `Collection<Umamusume>`.
>   The strings were made by the cache, not by the query.
> - **Not** "Feature tests cannot reproduce this because each test starts with an
>   empty cache; the 500 requires stale cached rows". That annotation would have
>   stopped anyone trying. A test reproduces it deterministically in four lines:
>   point `cache.default` at `database`, flush, visit once to write, visit again to
>   read. No pre-existing rows are needed — the round-trip is the bug. That is
>   `tests/Feature/CatalogCacheRenderTest.php`, and all three of its cases failed
>   with this exact message before the fix.
>
> **Actual cause.** `config/cache.php:128` sets `serializable_classes => false`, so
> `DatabaseStore::unserialize()` calls `unserialize($value, ['allowed_classes' =>
> false])` and every object comes back as `__PHP_Incomplete_Class` (proved in tinker:
> `returned class=__PHP_Incomplete_Class`). Blade iterating that yields strings, which
> is the `->slug` error. The suite never saw it because `phpunit.xml:25` pins
> `CACHE_STORE=array` while `.env` runs `CACHE_STORE=database`, and the array store
> hands back the objects it was given.
>
> **Fix.** The cache now holds primitives — the page's ids and its total — and the
> models are re-read from them; `show()` is not cached at all. The framework setting
> was left alone: it is a deliberate refusal to revive objects from a persistent store,
> and relaxing it to keep caching model graphs would trade a lore-scale safety default
> for a micro-optimisation on a local SQLite read path.
>
> **Runtime pass, which the annotation asked for.** `php artisan serve` against the
> real database cache: `/umamusume -> 200`, `/training-runs -> 200`. Recorded with the
> rest of the slice in `docs/design-research/verification/slice-2-2026-09-28.md`. The
> detail page had the same defect and was covered by neither this report nor the old
> test.

**Symptom.** `GET /umamusume` returns **500**. `GET /training-runs` also returns **500**.

**Evidence.**

```
storage/logs/laravel.log:
[2026-09-27 00:15:48] local.ERROR: Attempt to read property "slug" on string
  (View: ...\resources\views\catalog\index.blade.php)  ViewException

resources/views/catalog/index.blade.php:29
  <a href="{{ route('catalog.show', $umamusume->slug) }}" ...>
```

Measured against a running server: `/umamusume` and `/training-runs` both returned 500,
while a page not using `x-layout` returned 200.

**Cause.** The controller hands the view a collection of strings, or a `pluck()`-style
list, where the template dereferences `->slug`. Either the query should hydrate models or
the view should read array keys.

**Impact.** The catalog is the priority surface per `DESIGN.md` §4.1, so the app's first
screen does not currently render. This also hides KI-1: the view throws before `@vite`
in the layout head is ever reached, so the manifest error cannot surface until this one is
fixed.

**Owner.** The phase that owns `CatalogController`.

---

**Status annotation (2026-09-28, docs audit).** Runtime verification pending after the current frontend dirty work lands. Feature tests cannot reproduce this because each test starts with an empty cache; the 500 requires stale cached rows from before the view's model switch. Evidence gap: runtime HTTP pass not executed in the audit/remediation turns. Close it with a real-server pass (`/umamusume` and `/training-runs` returning 200) or by confirming the cache-key version bump cleared old entries.

## KI-3 `welcome.blade.php` breaks the offline requirement — RESOLVED 2026-09-28

**Evidence.**

```
resources/views/welcome.blade.php:10   <link rel="preconnect" href="https://fonts.bunny.net">
resources/views/welcome.blade.php:11   <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" ...>
```

`PRD.md` NFR-1 makes this a local-only tool. A remote stylesheet is a network dependency on
first paint, and it loads `instrument-sans`, a font the app does not bundle.

**Fix.** `DESIGN.md` §2.2 already replaced `--font-sans` with the system stack, so the link
tag is now dead weight as well as illegal. Delete lines 10 and 11.

**Status.** Already noted in `DESIGN.md` §7 as "a one-line fix nobody has applied". Still open.

**Closed 2026-09-28 (`5548b9e`).** Both `<link>` lines are deleted; `welcome.blade.php`
now loads its assets through `@vite` like every other page. Measured on a live server
rather than inferred: the rendered document is 39,987 bytes, `grep -c bunny` returns 0
against that response, and every asset the browser goes on to request is same-origin. The
first measurement of that kind was a false pass — the server had died, so `grep -c` was
counting an empty body — which is why the byte count is in the record.

**Guard.** `tests/Feature/RenderedCopyHygieneTest.php` fails any Blade file that carries a
remote `rel=stylesheet|preconnect|preload|dns-prefetch` link or a remote `<script src>`, so
this cannot come back as a one-line fix nobody applies again. Anchor `href`s stay allowed: a
link a Trainer chooses to click is not a dependency the page cannot render without.

**Residual, not fixed.** The page's inline `<style>` block still declares
`--font-sans:"Instrument Sans", ui-sans-serif, system-ui, …` inside a vendored Tailwind
blob. Nothing loads Instrument Sans any more, so the stack falls through to the system
fonts `DESIGN.md` §2.2 chose — the right outcome by accident rather than by editing the
declaration. The blob also duplicates CSS the Vite build already ships. Left alone because
the landing page's markup is not this slice's scope; worth a decision about whether
`welcome.blade.php` should carry 38 KB of inlined CSS at all.

---

## KI-4 `make lore` cannot see untracked files, and checks no terminology — RESOLVED 2026-09-28

**Symptom.** A brand-new file passes the lore gate by being invisible to it.

**Evidence.** `git grep` searches tracked files only:

```
$ git ls-files --error-unmatch config/scenarios.php
Did you forget to 'git add'?

$ git grep -inw "planned" -- 'config/**' 'resources/**'      # tracked only
(nothing)
$ git grep --untracked -inw "planned" -- 'config/**' 'resources/**'
(found)
```

Separately, the `lore` target greps equine vocabulary only. It has no pattern for
`wisdom` or `motivation`, which are the terms the source wikis use throughout for Wit and
Mood, so the project's own constraint about client terminology is unenforced by it.

**Mitigated by.** The `lore-code` target added 2026-09-27, which uses `--untracked`, adds the
terminology set, and scopes to shipped code so `docs/` can keep quoting the wiki English it
warns against. `make lore` is left untouched.

**Still open.** `make` itself is not installed in this environment (`make: command not
found`), so neither target can be run as documented. The recipes were executed directly.
Consider a composer script so the gate does not depend on a GNU make binary on Windows.

**Closed 2026-09-28 (`cc3f963`).** `composer lore` and `composer lore-code` run both
targets through `tools/lore.php`, which hands `git grep` an argument array instead of a
shell string. That matters more than convenience: a composer script holding the Makefile's
command text runs through `cmd.exe`, which does not quote with single quotes, so
`-- ':!vendor'` reaches git still quoted, git errors, the recipe's `|| true` swallows it,
and the gate prints nothing and exits 0. The failure mode this replaced was a gate that
reported clean while matching no file at all.

Parity is measured rather than claimed. On one tree, the recipe bodies run verbatim and
`composer lore` report the same count, and the `lore-code` body and `composer lore-code`
both report 4. `LoreGateParityTest` compares the pattern strings between the Makefile and
the runner, and dropping `withers` from the script is what proves the guard bites. One side
effect is recorded rather than hidden: tracking the runner made it a permanent self-hit, so
the sweep now prints the scanner's own pattern lines beside `gate.py`'s, and
`docs/GATE-REGISTRY.md` allowed class 4 names both files with their composition — counts
there are dated, because any rules file quoting a banned word moves the total. The Makefile
targets stay — `CONSTRAINTS.md` C-4 names them — and `lore-code` cannot self-hit because
`tools/` is outside its path list.

The untracked-file half of this entry was never closed by the runner: `make lore` reads
tracked files only by design, and `lore-code` reads untracked copy inside app paths only.
That scope split is registered as a gap, not a fix.

---

## KI-5 A test asserts a fabricated skill name built on a banned word — FIXED 2026-09-27

**Fixed.** `tests/Feature/TrainingRunTest.php` now uses real Global skill strings read from
`.scratch-uma/skills.json` (`Certain Victory`, `1st Place Kiss☆`, `Feel the Burn!`), and the local
variable is renamed `suggested`, matching the client enum. `app/Enums/SkillAcquisition.php`'s
comment reworded `planned` to `marked for this run` so the banned word leaves the shipped code
as well as the test. Verified: the test passes and the `lore-code` gate is clean on
`app/**` `tests/**` `lang/**`.

**Original defect, kept as written.**

**Evidence.**

```
tests/Feature/TrainingRunTest.php:54   $planned = Skill::factory()->create(['name' => 'Planned Skill']);
tests/Feature/TrainingRunTest.php:72   ->assertSee('Planned Skill')
```

Two problems in one line. `planned` is on the banned terminology list, because the client
enum is `Suggested`. And the string is invented, so a test asserts that a fabricated catalog
name reaches the screen, which is the failure mode `CONSTRAINTS.md` D-76 exists to stop.

**Fix.** Use a real Global skill string from `docs/UMAMUSUME_REFERENCE.md`, and rename the
local variable, which is what the `lore-code` grep is actually matching on.

**Not fixed here.** It is another phase's test file, and changing test fixtures needs the
owner of that suite to agree the replacement string.

---

## KI-6 The shipped font fails the design system's own mandate — **CLOSED as decision (2026-09-27)**

**The conflict.** `docs/design-research/DESIGN.md` §4.1 makes a rounded humanist sans a hard
rule and bans neutral grotesques as the primary voice; gate **G-21** checks that body, heading and
numeral text resolve to "the declared rounded face, never to a banned grotesque". The shipped stack is
`resources/css/app.css` `--font-sans: ui-sans-serif, system-ui, sans-serif`. Measured in a browser on
2026-09-27, that resolves to a system grotesque on Windows and macOS. **G-21 fails on our own code,
today, and that is the approved state.**

**Why it is allowed.** Root `CONSTRAINTS.md` C-8 bars adding a dependency without approval, and the
instruction on the implementation phase was "no new dependencies". A webfont is a dependency: bundling
Nunito or M PLUS Rounded 1s means either a CDN link, which recreates KI-3's offline break against
NFR-1, or committing font binaries plus a build step. Neither was authorised, so the owner parked the
proposal rather than silently shipping a face — and `app.css` carries that parking note so the choice
reads as a decision and not an oversight.

**Ruling B5 (2026-09-27):** C-8 exemption granted. The system stack (`ui-sans-serif, system-ui, sans-serif`) is the shipped identity. G-21 technically fails on the system grotesque but is **exempted** per this ruling. The rounded face (Nunito / M PLUS Rounded 1s) is parked pending a C-8 dependency approval that has not been granted. The exemption is recorded in `docs/design-research/CONSTRAINTS.md` G-21 so the gate's failure is a known decision, not noise. **Do not "fix" this by fetching a font, and do not fix it by deleting §4.1 either** — the mandate is the design intent, and it is the *approval* that is outstanding.

---

## Related, tracked elsewhere rather than here

- **Stat grade banding is unsourced.** Implemented as a provisional 150-point banding and
  labelled in the UI. See `docs/design-research/DESIGN.md` §11.3 and the comment block in
  `resources/views/components/stat-band.blade.php` for the exact evidence needed to close it.
- **Light band tints measure 1.10 to 1.21 against the surface they render on**, where
  `docs/design-research/DESIGN.md` §3.6 documents 1.04 to 1.14 against the panel. The rule
  never named its second colour. See research §11.9 and D-258.
- **`APP_NAME` in `.env` is still `Laravel`**, while the product is Trainer Desk.
  Recorded in `DESIGN.md` §1.
- **`PRD.md` still lists US-10 at P2**, although `ADR-0003` promoted it to P1.
- **Run notes are create-only.** Severity: Low. Category: Product Question. The
  training-run update form carries no `notes` field, so notes can be written at creation and
  never corrected or added afterwards through the UI. The behaviour is untested as well as
  unexposed: the update assertions in `tests/Feature/TrainingRunTest.php` cover the other
  fields but not `notes`, and the update Form Request does not accept it. Update semantics are
  technically correct as written, so this is not a defect — it is either an intentional
  initial-context-only design or an oversight, and those two readings imply different work.
  Not fixed in this phase; it needs a product decision. Owner: Product / Architect.
- **`ApiV1Test.php:54` does not test what its title claims.** Severity: Low. Category:
  Test-Integrity Defect. The test is titled "returns a validation error shape", but its body
  probes the unknown route `/api/v1/nope` and asserts a 404 envelope, so the title advertises a
  test that does not exist. The cause is structural rather than a slip: `routes/api.php`
  declares only GET routes, so the `ValidationException` branch at `bootstrap/app.php:32` is
  unreachable until a write endpoint exists. Not fixed now; it resolves when the first write
  endpoint lands, at which point this case is either retitled or folded into
  `ApiV1ValidationEnvelopeTest`. Owner: QA / Backend.

---

## KI-7 Blocker: em dashes in rendered Blade copy — RESOLVED 2026-09-28

Status: Closed (`ab915f8`)  
Severity: Blocker  
Owner: Frontend  
Do-not-land: No

### Evidence (2026-09-28 disk state)

- `resources/views/components/grade-point-meter.blade.php:94` (rendered; :43 is a PHPDoc comment, not shipped copy)
- `resources/views/components/guided-step.blade.php:166`, `:202`, `:209` (rendered; :116 is inside a `{{-- --}}` Blade comment, not shipped copy)

### Rule violated

- Settled owner ruling (2026-09-28): the disclosure glyph is `N/A` with an optional `title` tooltip, never an em dash.
- R-02 bans em dashes in shipped copy; the C-4/R-02 no-carve-out ruling applies.
- Note: the dirty `ARCHITECTURE-ESSENTIALS.md` D-220 line currently documents rendering "as `—` / not yet recorded", which contradicts the settled ruling; it belongs to the in-flight frontend slice and must be reconciled to `N/A` wording when that slice commits.

### Required fix

Replace rendered em dashes with compliant punctuation (comma, colon, parentheses) or `N/A` + `title="..."` where the dash acts as a disclosure marker.

**Automated catch: added 2026-09-28.** `tools/gate.py` checks em dashes (D-79) only in
prototype HTML, so `RenderedCopyHygieneTest` was written to cover shipped Blade: it walks
every `.blade.php` under `resources/views`, strips Blade and both PHP comment forms, and
fails on any en or em dash that can reach a Trainer. A companion test floors the sweep at
12 views, because a first draft filtered on `getExtension() === 'blade'`, which matches
nothing (`foo.blade.php` reports the extension `php`), and the guard would have passed
having scanned zero files.

### Notes

- Both files are uncommitted frontend work; the blocker exists in the working tree, not in HEAD for `guided-step` (tracked, dirty) and nowhere tracked for `grade-point-meter` (untracked).
- **Currency (2026-09-28, Slice 2).** `grade-point-meter.blade.php` and its test are now tracked — they were committed in `70f9218`, which also re-lit that component's progress fill. `guided-step` remains uncommitted, so the em-dash blocker described above still stands for it.

---

## KI-8 `/design-preview` 500s on a grade label the badge map has no entry for — RESOLVED 2026-09-28

**Symptom.** `GET /design-preview` returns **500**: `Undefined array key "B+"` at
`resources/views/components/stat-band.blade.php:99`.

**Cause.** `config('scenarios.grade_banding')` now emits half-step labels
(`B+`, `A-` and friends — seventeen of them at `step => 50`), while the component's
`$gradeClass` map has keys for the nine base grades only (G, F, E, D, C, B, A, S, SS).
Any stat that lands on a half-step dereferences a missing key.

**Evidence.** Live server, 2026-09-28: `design-preview -> 500` while
`umamusume -> 200` and `training-runs -> 200` on the same boot.

**Impact at the time.** `x-stat-band` was rendered by this route and nothing else, so the stat
band had no reachable surface and **no test file at all** — its rendered contrast pairs could
not be measured in Slice 2's manual pass. That pass still fixed the band's progress fill
(`70f9218`, same 1.62:1 defect as the meter's bar) and guards it by reading both
component sources, but makes no claim about unmeasured pairs.

**Both halves of that impact are gone.** Slice 3 gave the band a test file and measured the
pairs (`726f106`, `78697e9`), and Slice 5 mounted it on the run screen, which is the surface a
Trainer reaches (`d50a0ec`): the band now renders on all four scenario-bearing R17 runs, and
the claim "rendered by this route and nothing else" no longer describes the tree. The sentence
above is kept because it is what Slice 2 measured, not because it is still true.

**Decision needed, not made here.** Either collapse half-steps onto the base grade's fill
(needs its ratio recorded per D-10 before it ships) or give the seventeen labels a map of
seventeen tokens. Nine fills versus seventeen labels is a design-system decision, and it
is outside Slice 2's scope.

**Owner.** The phase that owns `grade_banding` in `config/scenarios.php`.

**Closed 2026-09-28 (`726f106`, R19).** The owner made the design-system call the entry
asked for: the fill keys on the base letter with the modifier stripped, the badge prints the
full label. A `B+` is a B's colour and the `+` is carried by the text, so nine fills serve
seventeen labels without inventing eight tokens nobody sourced.

The route returns **200** and every R17 fixture run returns 200 with zero console errors.
The band now has a test file: `StatBandTest` walks all seventeen banding labels and asserts
each one appears as a rendered badge, matched on the badge span rather than as a substring
(`B` is inside `B+` and inside class names, so a plain `contains()` would pass a band that
printed the wrong letter or none). `78697e9` measured the half Slice 2 could not: eighteen
badge-fill pairs, minimum **9.00** against 4.5:1, and the bar fill at 4.20 light / 11.74
dark against 3:1 — the claim `FRONTEND-SPEC-DIVERGENCE.md` §5 had carried unmeasured.

---

## KI-9 The selection gold measures 1.59:1 against `raised` in the light theme — RESOLVED 2026-09-28

**Symptom.** `--color-pick` (`#EFC96A` light) used as a 2px boundary on a `raised`
surface reads **1.59:1** — under WCAG 1.4.11's 3:1 for non-text boundaries. Measured in
the browser on the Grade Point ladder's `aria-current="step"` row and on the race
calendar's `current` cell. Dark is fine (`#F5B73C` on `#24262A` = 8.46:1).

**Why it is not fixed in passing.** `--color-pick` does three jobs at once: this
boundary, `*::selection`'s background, and part of the focus/selection pair recorded in
`DESIGN.md` §8. Re-stepping it means re-measuring all three surfaces in both themes,
which is its own pass. Nothing is colour-only today — the current step also carries
`aria-current` and heavier text, so D-12 holds and no user is left unable to read state.

**Owner.** Design system, with the token-pair table in `DESIGN.md`.

**Closed 2026-09-28 (`725a5ff`, R15).** The token was not re-stepped; the two jobs were
split. `--color-pick` keeps the fill, where it is right (8.34 light, 10.57 dark under
`--color-on-pick`), and `--color-pick-line` (`#7A5C10` light, an alias to `--color-pick` in
dark, where that value already cleared) carries the 2px boundary. Measured on the rendered
element's own `border-top-color` against the first opaque background beneath it, both
themes: **6.24** light / **8.46** dark on `raised`, **5.89** on the ladder's light `panel`,
against WCAG 1.4.11's 3:1. All four boundary consumers moved together — the calendar's
`current` cell, the meter ladder's `aria-current` step, the guided step's selected card and
its step links — and `::selection` plus the focus ring were re-measured to prove the split
did not disturb them.

Guard: `DesignTokensTest` fails any view that reaches back for `border-pick`, with
`(?![-\w])` so it does not flag `border-pick-line` itself. The realistic regression is one
component drifting back to the prettier token, not all four going wrong at once.

Found while writing the docs, recorded not fixed: `DESIGN.md`'s `JapanOnly` row specified
`--color-pick` **as text**, which is 1.59:1 — a text-contrast failure rather than a
boundary one. Nothing implements it; the catalog renders that status as `--color-ink-muted`
label text. The row now says so, so the next reader does not build the bug the spec
described.

---

## KI-10 Trackblazer Grade Points cannot be totalled or bucketed from what is stored — SCHEMA HALF CLOSED 2026-09-28 (Slice 7), RATIO HALF OPEN

**Half (b), the bucket, is closed.** Slice 7 added the two columns the attribution needed,
both entered by the Trainer and neither derived (`e103122`):

- `race_entries.objective_index`, nullable unsigned tinyint, 1..4: the period a finish
  counts toward.
- `training_runs.current_objective_index`, nullable unsigned tinyint, 1..4: the period the
  Trainer reports as live.

The range and the scenario-conditional rule are enforced in `TrainingRun::assertGradePeriod()`,
called from both models' saving guards, and validated again at the HTTP boundary by
`StoreTrainingRunRequest`. A column CHECK could bound the number but cannot see the run's
scenario, so the guard is the load-bearing half (the precedent is `ScenarioSlot`'s own
`saving` checks).

**What the meter does with it now.** `gradeEarnedFor(i)` sums priced finishes inside period i
alone; `gradeEarned()` is the reported period only and returns null while no period is
reported, so the panel renders "no period reported" rather than a zero or a guessed target
(D-220). Other periods render as collapsed ladder rows carrying their own sums, and no
cumulative total appears anywhere, because D-232 says surplus dies at the deadline. Finishes
entered with no period are counted in the disclosure rather than silently dropped from every
total. Proven by `tests/Feature/GradePointPeriodTest.php`: cross-period independence, the
null period, an unpriceable finish inside one period only, a G1 win landing in the period it
was entered against, and a rejection for period 5.

**Half (a), the placement ratio, stays open.** No source names the scaling below first, so the
withholding rule is unchanged and is now scoped per period: an unpriceable finish inside the
current period withholds that period's total and poisons none of the others. Closing it needs
either a sourced ratio or an owner ruling that ships an `[Unverified]` placeholder, and neither
has arrived.

**Owner.** Design system with the Planner Domain Specialist, alongside KI-15 for the track
question.

**Original defect text kept below for traceability.**

**Symptom.** `TrainingRun::gradeEarned()` returns `null` for any run holding a finish
below first, and the meter shows "not yet recorded" even though races happened.

**Cause.** Two independent gaps, both measured rather than assumed:

1. `docs/scenarios/05-trackblazer-gametora.md` prices Grade Points for **1st place only**
   (`grade_point_by_grade`: G1 100 … Pre-OP 20) and states that lower placements "scale
   down proportionally (similar to how Fan gain scales)" without naming a ratio. No
   placement scaling table for Grade Points exists in the corpus, and no fan-placement
   table exists either, so there is nothing to derive it from.
2. D-232 makes the four objectives separate deadlines judged alone, but `race_entries`
   carries no turn, year, or objective bucket, so a race cannot be attributed to the
   period it counts toward.

**Current behaviour is deliberate.** Withholding beats understating: a partial sum shown
as a total would be a false claim about a trainee (D-256).

**Required fix.** (a) a sourced placement ratio, or a `[Unverified]` placeholder decision
from the owner; (b) a turn or year bucket on `race_entries`, which is schema work and
therefore an ADR, not a patch.

**Owner.** Planner Domain Specialist with Data Engineer for the source.

---

## KI-11 Design-system debts left visible by the Slice 2 gate run — BOTH HALVES CLOSED 2026-09-28

- **CLOSED (`5c65597`). `database/seeders/ScenarioSlotSeeder.php` was an empty stub** —
  `run()` contained only `//`, and `DatabaseSeeder` never called it. Against
  `CONSTRAINTS.md`'s floor ("no unimplemented stubs") it was filled with sourced slot rows or
  removed; the slot rows are fetch-engine work per ADR-0003 Amendment R3, so there was
  nothing honest to put in it and the file is deleted. `scenario_slots` stays empty after a
  clean seed by design, which is also what makes any `scenario_races` backfill vacuous: a
  re-measured fresh scratch database seeds 24 tables and 10 skills and **0 slots**.
- **CLOSED (`71bbb4b` + `d50a0ec`, measured in Slice 5). `--color-green-tint` now has its
  committed consumer.** The goal cell moved to `bg-raised` in `bb6eec6` and the token was
  referenced by no utility after that; retirement was refused under R14 on G-60 grounds, and
  R23 settled the alternative - the Safe band word lands in Slice 5, or the token is retired
  and the spec amended in the same slice. It landed: the energy band word renders `ink` on
  `green-tint` on the run screen, and the pair measures **6.40:1 light / 12.60:1 dark** off the
  rendered element (`docs/design-research/verification/slice-5-2026-09-28.md` §3). The token
  is no longer a declared value awaiting a consumer, and the "unverified in practice" debt this
  bullet recorded is paid.

**Owner.** Design system. Both halves closed 2026-09-28.

---

## KI-12 The Grade Point meter says nothing was entered when races were entered but cannot be priced — RESOLVED 2026-09-28

**Symptom.** A Trackblazer run holding two completed 1st-place races — one linked to a
`G1` slot worth 100 points, one free-form with no slot — renders:

> **not yet recorded** — no Grade Points are entered for this run, so there is no progress
> to show yet.

**Evidence.** Browser pass, 2026-09-28, `/training-runs/2` on an isolated database with
both entries present; `gradeEarned()` returns `null` while
`raceEntries` holds `[{slot: 6, tier: "G1", placement: 1}, {slot: null, tier: null,
placement: 1}]`.

**Cause.** `gradeEarned()` withholds the whole total when any completed entry cannot be
priced, which is the deliberate KI-10 rule: a partial sum shown as a total would
understate. The *sentence* is the defect, not the arithmetic — it attributes the blank
panel to the Trainer having entered nothing.

**Not fixed here.** Withholding is correct and the copy needs to name the real reason, but
the wording differs by cause ("nothing entered" versus "a recorded result whose point value
is not published"), so the honest fix is for `gradeEarned()` to expose which case it is —
a new prop on a component whose other consumers are pinned by three existing assertions.
That is a copy decision with a schema consequence, and it belongs with KI-10's resolution.

**Interim truthfulness of what is shown:** the panel claims nothing about points, so no
false number is drawn. The misleading part is the implication that the Trainer's races were
not recorded.

**Owner.** Design system with the Planner Domain Specialist, alongside KI-10.

**Closed 2026-09-28 (`2816309`, R18).** The meter now has three states instead of two.
`gradeEarned()` exposes the reason through `gradeUnpricedCount()`, and the component reads
it: **"not yet totalled"** plus the count of logged results with no published Grade Point
value, and "any total here would count less than this run earned" naming why the figure is
withheld; **"not yet recorded"** only when no races are logged. The arithmetic did not move —
withholding stayed correct per KI-10 — the sentence changed.

The middle state is the one that matters and the one a two-state design loses: a Trainer who
entered two first-place finishes is not the same case as a Trainer who entered nothing, and
the old copy asserted the second over the first. KI-10 itself remains open: the placement
ratio and the year bucket are unfixed, and this closure does not claim otherwise.

---

## KI-13 Blocker: the models and migrations this branch's own code resolves against exist on no ref — **RESOLVED 2026-09-28 (Slice 4)**

**Severity:** Blocker — `master` and `docs/audit-remediation` were both affected  
**Owner:** Planner Domain Specialist with the concurrent frontend session  
**Do-not-land:** Was Yes — now lifted

**Resolution.** All three measures executed in Slice 4 (R20–R25):

1. **Five load-bearing files committed** (`35fb0c7` on `docs/audit-remediation`):
   `app/Models/ScenarioSlot.php`, `app/Models/Preference.php`,
   `database/factories/ScenarioSlotFactory.php`, `database/factories/PreferenceFactory.php`,
   `database/migrations/2026_09_27_121500_create_preferences_table.php`,
   `database/migrations/2026_09_27_153416_create_scenario_slots_table.php`.
   Authored by concurrent session, reason KI-13.

2. **Duplicate `is_manual` migration deleted + feat branch merged** (`dc13d8d`):
   Untracked `2026_09_27_132304_add_is_manual_to_scenario_races_table.php` deleted.
   `feat/scenario-races-is-manual` inspected — single commit `46b8d3e` (is_manual only) —
   merged at `dc13d8d` (no squash, no rewrite). The kept migration is
   `2026_09_27_183245_add_is_manual_to_scenario_races_table.php`.

3. **Coherence re-measured (all three outputs):**
   - `git ls-tree -r HEAD database/migrations | wc -l` = **20** — equals on-disk count (20).
   - `git grep -l "class ScenarioSlot" HEAD` → `app/Models/ScenarioSlot.php`, `database/factories/ScenarioSlotFactory.php`.
   - `git grep -l "class Preference" HEAD` → `app/Models/Preference.php`, `database/factories/PreferenceFactory.php`.
   - Fresh scratch-DB `migrate:fresh --seed` → **23 tables**:
     `cache`, `cache_locks`, `data_sources`, `failed_jobs`, `job_batches`, `jobs`,
     `match_candidates`, `migrations`, `password_reset_tokens`, `preferences`,
     `race_entries`, `run_skills`, `scenario_races`, `scenario_slots`, `scenarios`,
     `sessions`, `skills`, `sqlite_sequence`, `training_runs`, `turn_entries`,
     `turn_events`, `umamusume`, `umamusume_aliases`, `users`.

4. **Fast-forward + push** (`33949f5`):
   `master` fast-forwarded to reconciled tip. Pushed once, no force:
   - `origin/docs/audit-remediation` → `9b774f948fe8a859bfec67400f4f5a0388cfee73`
   - `origin/master` → `33949f5cb74089b8a836abdb944282e2dd26063d`

5. **Docs updated** (this commit): KI-13 RESOLVED with shas + T3 outputs; PLAN topology
   paragraph rewritten (master equals tip, feat merged, push state); PLAN slice exit criteria
   gain R25's line (every cited sha verified via `git cat-file -e` in-session; a record's own
   sha labelled self-citation) and the checkout-coherence amendment from R20 (boot files, not
   test coverage); KI-11 gains R23's consumer commitment (Safe band word in Slice 5,
   retire-and-amend if it does not land); KNOWN-ISSUES header count refreshed.

6. **Gates** (CONSTRAINTS order): pest, pint --dirty, phpstan, composer lore, composer
   lore-code (parity), gate.py, npm run build (declared-vs-pruned token count) — all PASS.

**Verification.** Clean checkout of `master` at `33949f5` boots; `php artisan test --compact`
passes; all coherence checks hold.

**Original defect text kept below for traceability.**

---

## KI-14 The guided rail declared radio semantics its elements did not have — RESOLVED 2026-09-28

**Filed here rather than found in the wild.** This is the accessibility item R10 deferred out
of Slice 2, and it had no entry to close, so it gets one now instead of a claim in a commit
message. The audit line it resolves is
`docs/design-research/FRONTEND-BRIEF-AUDIT.md` row "9 accessibility".

**Symptom.** `x-guided-step` rendered a `role="radiogroup"` whose children were
`<button type="button" role="radio" aria-checked>`. Two separate failures in one element: the
group claimed a widget semantics its children did not implement (no roving focus, no
arrow-key selection, no `aria-checked` state change without a script that was never written),
and a `type="button"` carries no value on submit, so the rail could not post a choice at all.
It was a picture of a radio group.

**Resolution (`d50a0ec`, `6a53c15`).** The choices are now real `<input type="radio">`
elements inside the `radiogroup`, each wrapped in the client's banner shape, so §6.10 and
§8.6's "banner buttons, never radio inputs" hold for what a Trainer sees while the element
carries the state. Consequences, all measured with pressed keys:

- Arrow keys rove and select natively; `ArrowRight` from `training-Speed` lands on
  `training-Wit` with `checked: true`. No roving-tabindex script was written, because the
  platform already does it and a reimplementation would fight it.
- The selection posts: `choice=training-Guts` reaches the request, which is what makes
  D-51's two stages possible without a script.
- Focus is visible where it previously could not be: the banner shows
  `outline: 2px solid rgb(78, 121, 6)` (`--color-ring`) with `matches(':focus-visible')` true,
  established with a real `Tab`, not `element.focus()`.
- `role="radio"` and `aria-checked` are gone from the markup, so nothing claims what the
  element does not do. `RunViewFrameTest` and `GuidedTurnOnRunViewTest` pin the group's shape.

**Residual, closed as correct-by-design 2026-09-28 (R31).** `aria-live` is absent from the rail,
and that is the right answer rather than an unfinished one. The preview panel appears through a
navigation: selecting an option submits, the server renders, the browser loads a new document, and
focus and the announcement come from the platform's own handling of that load. A live region is for
content that changes under a reader who has not moved; there is no such change here, so an
`aria-live="polite"` wrapper would announce a panel the user is already being taken to, and would
be a claim about the interaction that, like the `role="radio"` claims just removed above, the
element does not support. The rule this does not weaken is `docs/design-research/DESIGN.md:1406`
(§10 Accessibility): a gain bubble that updates in place under a Trainer who stays put still owes
`aria-live="polite"`, and there is no in-place gain bubble on this screen to give one.

---

## KI-15 The three Grade Point tracks have no sourced rule for choosing one — FILED 2026-09-28 (Slice 7), OPEN

**Symptom.** `TrainingRun::gradeObjectives()` renders the `standard` track (60 / 300 / 300)
for every Trackblazer run. For a dirt-leaning trainee the client asks 30 / 200 / 300, and for
a turf trainee whose range outside short distances is weak it asks 60 / 200 / 300, so the
meter can show a target that character cannot be held to.

**Cause, and it is a source conflict rather than a missing number.** The two Trackblazer
guides disagree about the same character class:

- `docs/scenarios/04-trackblazer-umaguide.md:48` puts "Sprint Umas with poor aptitude in
  other distances" on the **Dirt** requirement track.
- `docs/scenarios/05-trackblazer-gametora.md:25` gives a turf character with poor aptitude
  outside short distances a **third** track, in which only the Classic objective drops to 200.

Neither names the aptitude letter or letters that place a trainee in a track. `umamusumes`
does carry the ten aptitude letters (`ADR-0004`, `PRD.md` OQ-4 closed 2026-09-27), so the data
to build a rule exists; the rule does not, and any threshold this tool picked would be its own
invention dressed as a game fact (D-20, D-256).

**Current behaviour is deliberate.** `standard` is rendered and the code says so at
`app/Models/TrainingRun.php:378-385`. A wrong denominator is worse than a conservative one,
because a Trainer cannot tell they were handed the wrong track at all.

**Required fix.** Either a capture or a dated secondary source naming the aptitude condition
per track, or an owner ruling that ships a Trainer-entered track selector — a third column on
`training_runs`, which is the D-270 pattern Slice 7 used for the period itself. That is why the
choice is recorded here rather than made silently: adding it is a schema decision, and Slice 7
was given two columns, not three.

**Owner.** Planner Domain Specialist with the owner; the schema decision is the owner's alone.

---

## KI-17 The consecutive-race count cannot be derived from the log, so it is entered — FILED 2026-09-28 (Slice 8), OPEN

**Symptom.** D-230 states that Trackblazer's Race Fatigue is safe to surface because "consecutive
race count is already recoverable from `turn_entries`, and that premise is false as the schema
stands. `race_entries` points at a `scenario_slots` row (month, half, tier) and never at a turn,
and the guided flow offers no race choice, so no logged turn can be identified as a race turn.
There is also no `race_entries.turn` to read and none was sanctioned.

**Current behaviour is deliberate.** `TrainingRun::consecutiveRaceCount()` returns null and says
why at `app/Models/TrainingRun.php:639-650`, and the chip renders "no consecutive-race reading
recorded". The count is instead entered as `RaceFatiguePayload {consecutive_races}` on the turn it
applies to, which keeps the fact without inventing a link, and the panel prints one qualitative
word for the band (unlikely / possible / likely / certain) with the pointer to
`docs/scenarios/05-trackblazer-gametora.md` §Race Fatigue, never the percentages: one source is
not two (D-230).

**Required fix.** Either a link from a race entry to the turn it happened on, or a guided choice
that marks a race turn. Both are schema or flow decisions, so neither is taken here.

**Owner.** Planner Domain Specialist with Architect.

---

## KI-18 The Spirit Burst roster prints tool identifiers where a Trainer reads a state — RESOLVED by `f7a59e8` (Slice 9), MARKED CLOSED 2026-09-29 (Slice 10, R52)

**Symptom.** `resources/views/components/spirit-burst-roster.blade.php` renders
`$row['state']->value`, so the chip reads `NormalBurstSpent` and `ExtremeChargeable`. Those are
this tool's backing values, not Global client strings: `app/Enums/SpiritBurstState.php:8-20` says
the case values are deliberately not client copy and that a display slice owes a label map
alongside them. This is that display slice, so the debt came due here.

**Why it is not patched in this slice.** `SpiritBurstPayloadTest` and `ScenarioPanelUiTest` assert
the six `value` strings appear in the rendered page, so a label map means changing the enum, the
component and both tests together. The `/impeccable audit` raised it as its only P1 (score 16/20,
`slice-8-2026-09-28.md` §5), and the audit ran under "score only, do not change code", so the
finding is recorded here rather than absorbed silently into the same commit that reported it.

**Required fix.** `SpiritBurstState::label()` returning words a Trainer reads ("charged", "held",
"burst spent", "extreme chargeable", "extreme spent"), the roster printing the label, and the two
tests asserting the label instead of the value. No new client string is invented: the words
describe a machine this tool models, and the source names none of them.

**Resolution (`f7a59e8`, Slice 9).** All three parts shipped in one commit: `label()` on the enum
mapping the six cases to `Chargeable`, `Charged`, `Charged, held`, `Burst spent`, `Extreme
chargeable`, `Extreme spent`; `spirit-burst-roster.blade.php` printing `$row['state']->label()`;
and `ScenarioPanelUiTest` asserting the labels while failing if a backing value reaches the
response (`tests/Feature/ScenarioPanelUiTest.php:105-114`). The measured proof is
`docs/design-research/verification/slice-9-2026-09-28.md` §5, whose browser row for the roster
reads "all five labels present, zero backing values leaked". `value` stays the storage identifier.

**Why it was still marked open here.** Slice 9 fixed it and closed it in its own record but never
recomputed the register's status line, so the entry contradicted the commit that resolved it. R52
closes the bookkeeping, not the defect.

**Owner.** Frontend with the Lore Guardian, next pass on the Unity Cup panels.
