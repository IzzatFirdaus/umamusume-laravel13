# Known issues

Defects found during the scenario-aware design and component work that were **out of that
phase's scope**, so they are recorded here rather than fixed in passing. Each entry states
the command or file that proves it, not just the symptom.

Discovered 2026-09-27. None of these were introduced by the component work; the component
work is what made them visible, because the prototype phase had no running server to hit.

**Status (2026-09-29, Slice 16):** **24 filed, 19 closed, 5 open.** **KI-25 is back to OPEN** under R85.
`b8c0a54` did land the focusable scroll region and its test, and that work is not being undone; what is
being withdrawn is the closure, because the issue's **measurement half has never been read in a browser** —
arrow-key traversal of the two regions, the document-level `scrollWidth` after scoping the overflow to the
table, and every column and region other than the one element originally probed are all unmeasured. The
closed text also cited `docs/design-research/verification/slice-16-2026-09-29.md`, which does not exist. A
register entry whose evidence is a dangling path is not a closed entry.
**What re-closes it:** the responsive contract — D-40's "no mobile-first compromise" reconciled with the
768px floor `b8c0a54` wrote into `DESIGN.md` §2.3 (that amend is itself held pending R82) — plus a read-only
measurement pass whose numbers land beside the closure. The other four open items are KI-10's ratio half,
KI-15, and the skills pass's KI-23 and KI-24. A heading grep reads 21 closed and 3 open; the register says
19 and 5, and the two entries in between are known and named — KI-10, whose heading says CLOSED while its
ratio half is not, and KI-25, whose heading now reads CLOSED *inside* a RE-OPENED sequence. Neither is a
counting error to fix; both are headings that carry more history than a regex can read. Prior:
**Status (2026-09-29, Slice 15):** **24 filed, 19 closed, 5 open.** The Schema Session landed all three
items its brief named. **KI-17 is closed** on the link it asked for (`d06199c`): `race_entries` can now
point at the turn a race was run on, and the closure carries its own limit — the count is still entered,
because a turn with no named link is not a turn that did not race. **KI-10's second schema half closed**
with `grade_points_earned` (`3711894`), which gives each finish a figure of its own; its **ratio half stays
open**, since nothing below first is priced and no source names the scaling. **Legacy Select landed as
schema only** — one `legacy_selection` json payload under `ADR-0010`, with no screen and no computation
(`26aa9fe`), which closes D-268 as a storage question and leaves it open as a UI one. **KI-25 is filed** by
this slice's browser pass: the turn log table overflows a 390px viewport, measured, and it is not a Slice 15
defect — the diff that slice is one view file, and it is not the table's.

**Two sessions, one register, one commit — said plainly rather than hidden in a count.** This block was
written while the skills pass still held this file uncommitted in the same shared working tree, and the
commit that carries it (`781e2b9` onward) therefore also carries **KI-23 and KI-24, their status block, and
their `ADR-0011` prose, all authored by that session and not by this one.** The owner directed the register
be written rather than deferred; authorship is named here so provenance survives the merge, and the counts
above fold in their two filings because KI-23 and KI-24 are in the file this block tallies. The mobile
finding in KI-25 was left out of their block deliberately: their status line says "The three Slice 14 open
items — KI-10, KI-15, KI-17 — are untouched by any of this", which was true of their pass and is now true
no longer. Prior:
**Status (2026-09-29, skills pass):** **23 filed, 18 closed, 5 open.** The skills audit pass filed
KI-23 and KI-24 while measuring the export the skills import would read, and both are about the existing
fetch path rather than the new one: the characters parser reads a key the live document does not have and
its own fixture repeats that key so the suite cannot see it (KI-23), and a stale cache-busting hash answers
`200` with stale content, which falsifies the safety claim written in `config/uma.php:50-54` and leaves the
live `gametora-characters` pin behind the publisher's current document (KI-24). Neither was introduced by
this pass; both were reachable only by comparing the parsers against a captured source. The three Slice 14
open items — KI-10, KI-15, KI-17 — are untouched by any of this. The skills gaps themselves are not filed
here: they are recorded in `docs/design-research/SKILLS-GAPS.md`, because a missing surface is a gap and a
key that never matches is a defect. **These counts are superseded by the Slice 15 header above, which
files KI-25 in the same shared tree while this pass held the file uncommitted: 24 filed, 19 closed,
5 open.** Prior:
**Status (2026-09-29, Slice 14):** **Unchanged — 21 filed, 18 closed, 3 open.** Slice 14 settled the
tier-label question Slice 13 left contested: G1, G2 and G3 are now sourced **per race** from a dated
two-publisher extraction (`329cec1`) and the seeder holds no code-to-label constant, so G-16c is green
for `database/seeders/` and `config/`. That reaches none of the three open items — KI-10 is the Grade
Point placement ratio, KI-15 which GP track applies, KI-17 the consecutive-race count — so all three
stay open. No KI was filed for the two questions Slice 14 leaves, because neither is a defect: the 115
Open rows resting on a disclosed code-level generalisation, and the parser's map unreconciled under
R72's idle condition. Both are recorded with their reasoning in D-153 and in
`docs/design-research/verification/slice-14-2026-09-29.md` §2.5 and §7, where a reader of the map will
meet them. Prior:
**Status (2026-09-29, Slice 13):** 21 issues filed. **18 resolved/closed** (KI-1–9, KI-11–14,
KI-18–22). **3 open**: KI-10 (ratio half), KI-15 (which Grade Point track applies), KI-17 (the
consecutive-race count cannot be derived from the log). Slice 13 closed KI-21 by rebuilding the race
entry form as server-driven disclosure (`6c1969f`, no dependency added) and pinning it with rendered
DOM rather than HTML-source assertions. It closed KI-22 too, **but KI-22 was filed on a wrong cause
and that is recorded in its own entry**: Slice 12 grepped for `isFreeRace`, did not find the
`$manual` parameter that had replaced it, and read a missing identifier as a missing branch. The
real defect was the marker on a finished free race, which the read path's owner fixed in `b6d68b6`.
Counts read off `grep -c "^## KI-"` = 21; KI-16 was never filed, which is why the numbers run to
KI-22. Prior:
**Status (2026-09-29, Slice 12 T3):** 21 issues filed. **16 resolved/closed** (KI-1–9, KI-11–14,
KI-18–20). **5 open**: KI-10 (ratio half), KI-15 (which Grade Point track applies), KI-17 (the
consecutive-race count cannot be derived from the log), and two filed by the T3 browser pass —
KI-21 (the race entry form is built on Alpine.js, which is not a dependency, so neither path renders)
and KI-22 (a free_race cell renders `state=past` without the Trainer-entered marker, so R61's calendar
rule is no longer implemented). Both block this slice's T4: gates would go green over a form nobody
can use. KI-22 is a shared-master collision with concurrent commit `82959e9`, not a Slice 12 defect.
Prior:
**Status (2026-09-29, Slice 12):** 19 issues filed. **16 resolved/closed** (KI-1–9, KI-11–14,
KI-18–20). **3 open**: KI-10 (ratio half), KI-15 (which Grade Point track applies), KI-17 (the
consecutive-race count cannot be derived from the log). Slice 11 closed KI-20 by measuring the pair
and stepping the dark `--color-risk`; Slice 12 closed KI-19 on the successful second `update` attempt
(engine v0.1.5) and re-closed KI-11 on current evidence, its deletion basis having been superseded by
the sourced seeder at `f0ae288` and its token half now measured against the Slice 8 epithet rows.
Counts read off `grep -c "^## KI-"` (19 headings at that time; KI-16 was never filed, which is why the
numbers run to KI-20). Prior:
**Status (2026-09-29, Slice 11):** 19 issues filed. **15 resolved/closed** (KI-1–9, KI-11–14,
KI-18, KI-20). **4 open**: KI-10 (ratio half), KI-15 (which Grade Point track applies), KI-17 (the
consecutive-race count cannot be derived from the log), KI-19 (the impeccable tool cannot update
itself). Slice 11 closed KI-20 by measuring `text-risk` on `bg-raised` in both themes and stepping
the dark-theme token from #FF6B7A (4.33:1) to #FF7E8C (4.77:1) per the D-259 precedent. Prior:
**Status (2026-09-29, Slice 10):** 19 issues filed. **14 resolved/closed** (KI-1–9, KI-11–14,
KI-18). **5 open**: KI-10 (ratio half), KI-15 (which Grade Point track applies), KI-17 (the
consecutive-race count cannot be derived from the log), KI-19 (the impeccable tool cannot update
itself), KI-20 (the shop error text is an unmeasured contrast pair). Slice 10 closed KI-18,
which `f7a59e8` had already fixed in Slice 9, and filed KI-19 and KI-20.
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

## KI-10 Trackblazer Grade Points cannot be totalled or bucketed from what is stored — SCHEMA HALVES CLOSED 2026-09-28 (Slice 7) AND 2026-09-29 (Slice 15), RATIO HALF OPEN

**Half (c), the figure itself, is closed — and closing it did not touch half (a).** Slice 15 added
`race_entries.grade_points_earned`, a nullable unsigned integer written by `RaceEntry`'s existing saving
guard (`3711894`), so a finish now carries the number it paid instead of having it recomputed from a slot
on every read. That is the schema half the Slice 15 brief counted as this issue's remaining one. It is
**not** the ratio: the column is priced for a 1st place and null below first, for the same missing-source
reason as before. Two consequences worth stating, because both are easy to read backwards:

- The meter sums the column through one private `pointsOf()`, which falls back to pricing a row written
  before the column existed. A local database holding runs from Slice 7 therefore keeps the points it
  already showed rather than losing them to a null column — a migration that silently de-prices existing
  wins would be a regression dressed as a schema change.
- `grade_point_by_grade` and `shop_coins_by_placement` sit three lines apart in `config/scenarios.php` and
  the second has the 100/60/30/0 shape a placement ratio would need. It is not one: the source document
  says Shop Coins "do not depend on race grade at all". `GradePointsEarnedTest` asserts 2nd-in-a-G1 stores
  null beside 4th-in-a-G1 so the borrow cannot be made quietly later.

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
was entered against, and a rejection for period 5. **Those nine tests pass unchanged after
Slice 15, which is the evidence that reading from the column preserved the semantics rather
than redefining them.**

**Half (a), the placement ratio, stays open.** No source names the scaling below first, so the
withholding rule is unchanged and is now scoped per period: an unpriceable finish inside the
current period withholds that period's total and poisons none of the others. Closing it needs
either a sourced ratio or an owner ruling that ships an `[Unverified]` placeholder, and neither
has arrived. What Slice 15 changes is where a placeholder would go: it would now be a value on
the row, per finish, rather than a rule applied at read time to every period at once — which is
a strictly better shape for a decision of that kind and is the one lasting gain of half (c).

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

## KI-11 Design-system debts left visible by the Slice 2 gate run — CLOSED 2026-09-28, RE-CLOSED ON CURRENT EVIDENCE 2026-09-29 (Slice 12)

- **CLOSED (`5c65597`), superseded by `f0ae288`. `database/seeders/ScenarioSlotSeeder.php` was an
  empty stub** — `run()` contained only `//`, and `DatabaseSeeder` never called it. Against
  `CONSTRAINTS.md`'s floor ("no unimplemented stubs") it was filled with sourced slot rows or
  removed; the slot rows were fetch-engine work per ADR-0003 Amendment R3, so there was
  nothing honest to put in it and the file was deleted. `scenario_slots` stayed empty after a
  clean seed by design, and a re-measured fresh scratch database seeded 24 tables, 10 skills and
  **0 slots**.

  **That closure basis no longer describes the tree.** Slice 11 restored the seeder with sourced
  URA Finale rows read from the committed client export (R55, `f0ae288`), so the debt is now paid
  by content rather than by deletion, and the "0 slots" measurement is withdrawn as stale. Three
  files still cite that stale basis and should be corrected by their owners:
  `app/Http/Controllers/TrainingRunController.php:78` ("Empty by design until the fetch engine
  lands (KI-11)"), and the free-form-race notes at `slice-8-2026-09-28.md:127` and
  `slice-9-2026-09-28.md:78`. The URA half is seeded; Trackblazer and Unity Cup still have zero
  rows, so those two sentences are half-true and were written before R55.
- **CLOSED (`71bbb4b` + `d50a0ec`, measured in Slice 5; second consumer measured in Slice 8).
  `--color-green-tint` has committed consumers.** The goal cell moved to `bg-raised` in `bb6eec6`
  and the token was referenced by no utility after that; retirement was refused under R14 on G-60
  grounds, and R23 settled the alternative: the Safe band word lands in Slice 5, or the token is
  retired and the spec amended in the same slice. It landed: the energy band word renders `ink` on
  `green-tint` on the run screen, and the pair measures **6.40:1 light / 12.60:1 dark** off the
  rendered element (`docs/design-research/verification/slice-5-2026-09-28.md` §3).

  **The Slice 8 epithet rows are the second consumer, and they hold.**
  `slice-8-2026-09-28.md` §4 measured the same token pair on two more surfaces from the new panels:
  `ink` on `green-tint` at **6.40** on the earned epithet row (line 79), and `ink-muted` on
  `green-tint` at **5.33 light / 6.58 dark** on the route-and-reward meta line (line 80). One
  consumer could be an accident of a single component; three, on two different panels, is the token
  doing a job. R23's promise is paid twice over, and the "unverified in practice" debt this bullet
  recorded is closed on measurement rather than on the existence of a class name.

**Owner.** Design system. First half closed 2026-09-28 by deletion and re-closed 2026-09-29 on
sourced content; second half closed 2026-09-28 and re-measured against the Slice 8 epithet rows.

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

## KI-17 The consecutive-race count cannot be derived from the log, so it is entered — FILED 2026-09-28 (Slice 8), CLOSED 2026-09-29 (Slice 15) ON THE LINK, NOT ON THE COUNT

**Closed on the fix this issue named, and the closure is narrower than its title.** The brief for Slice 15
directed "KI-17 closed", and the link the Required fix below asked for now exists. What does **not** exist is
a derived count: `consecutiveRaceCount()` still returns null and the figure is still entered, because the
link's absence is not evidence of a turn without a race. A reader who takes this title to mean Race Fatigue
now has a number behind it will be wrong, which is why the limit is in the heading rather than only below.

**Symptom.** D-230 states that Trackblazer's Race Fatigue is safe to surface because "consecutive
race count is already recoverable from `turn_entries`, and that premise is false as the schema
stands. `race_entries` points at a `scenario_slots` row (month, half, tier) and never at a turn,
and the guided flow offers no race choice, so no logged turn can be identified as a race turn.
There is also no `race_entries.turn` to read and none was sanctioned.

**Current behaviour is deliberate.** `TrainingRun::consecutiveRaceCount()` returns null and says
why at `app/Models/TrainingRun.php:811-826`, and the chip renders "no consecutive-race reading
recorded". The count is instead entered as `RaceFatiguePayload {consecutive_races}` on the turn it
applies to, which keeps the fact without inventing a link, and the panel prints one qualitative
word for the band (unlikely / possible / likely / certain) with the pointer to
`docs/scenarios/05-trackblazer-gametora.md` §Race Fatigue, never the percentages: one source is
not two (D-230).

*(The range this entry originally cited was `:639-650`. It has moved twice since — once before
Slice 15 and once inside it — and the number a `file:line` carried when a defect was filed is not a
fact worth preserving, while a wrong pointer to read is. So it is updated, and the original is named
here rather than quietly replaced.)*

**Required fix.** Either a link from a race entry to the turn it happened on, or a guided choice
that marks a race turn. Both are schema or flow decisions, so neither is taken here.

**Closed by `d06199c` (Slice 15 T1 of the Schema Session), on the first of the two options.**

- **Delivered.** `race_entries.turn_entry_id`, a nullable foreign key to `turn_entries` with
  `nullOnDelete`, entered by the Trainer from a dropdown of that run's own turns in the race panel. A turn
  belonging to another run is rejected at the Form Request and no row is written. `RaceEntry::tierKey()` and
  the KI-17 tests are the proof; `slice-15-2026-09-29.md` §3 records the RED run.
- **Still true of the symptom.** The count is not derived. A null link states "the Trainer has not named the
  turn", which is not the proposition "this turn held no race", and a run of consecutive races inferred from
  that gap would be a guess printed as a reading (D-270). So `consecutiveRaceCount()` remains `return null`,
  and `RaceFatiguePayload {consecutive_races}` remains the way the fact is kept. Both docblocks said the
  link column had not been given, which stopped being true at this commit, and now say what is actually
  missing instead.
- **What would close the remaining half.** Not schema. It needs a flow ruling on whether an unnamed turn may
  be read as a non-race turn at all — which is D-230's premise revisited, and belongs to the Planner Domain
  Specialist with Architect, not to a slice that was given the link.

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

---

## KI-19 The impeccable tool cannot update itself, so a maintenance slice cannot measure a version delta — FILED 2026-09-29 (Slice 10), RESOLVED 2026-09-29 (Slice 12, R66)

**Symptom.** `C:/Users/exatf/.agents/skills/impeccable/scripts/impeccable.cmd check` and the same
launcher's `update` both return `Could not verify skill bundle: HTTP 404. Nothing was installed`.
`--version` reads `4.0.0` before and after, so Slice 10's re-audit ran on the incumbent build rather
than an updated one. Upstream points the report at `pbakaus/impeccable` issue #479.

**Attempt log (both dated 2026-09-29, per R66).**

| Attempt | Slice | Command | Result |
|---|---|---|---|
| 1 | Slice 10 | `impeccable.cmd update` | `Could not verify skill bundle: HTTP 404. Nothing was installed`. `--version` unchanged at 4.0.0 |
| 2 | Slice 12 | `impeccable.cmd update` | Succeeded. Engine v0.1.5 (windows-x64) installed into `.kiro/` and `.opencode/` script bins; hooks installed into `.claude`, `.cursor`, `.agents`, `.github`, `.grok`. `--version` still reports 4.0.0 |

**The version half of the finding stands.** `--version` reports the *skill bundle* version (4.0.0),
not the engine version (v0.1.5), so a same-`--version` reading is not evidence that nothing changed.
The engine binary is what runs `detect`, and it did move. What Slice 10 could not do — compare
audits across a version delta — is now possible; the second attempt is what closes it.

**Second half of the finding, unchanged.** The interface the brief names, `npx impeccable update`, is
still not this project's: `impeccable` is in no `package.json` and has no `node_modules/.bin` entry,
so an `npx` run would fetch an unrelated registry package under that name. The launcher next to the
installed skill is the real interface, and it is what was run both times.

**Audit at the updated engine (R66).** `impeccable.cmd detect` over the two Slice 11 surfaces —
`resources/views/components/race-panel.blade.php` and `resources/views/components/race-calendar.blade.php`
— returns **1 finding**: `race-calendar.blade.php:148 [side-tab] border-l-5`. Not acted on: the left
edge marks the mandatory goal race, so it carries information rather than decorating, which is the
exception the rule itself names. Evidence in `slice-12-2026-09-29.md` §4.

**Required fix.** Nothing in this repository. The upstream bundle URL resolves again as of the second
attempt; if it 404s in a later slice, record both attempt dates rather than one, because a single
date cannot distinguish a transient failure from a moved URL.

**Owner.** Whoever runs the tool update, outside this repo. Closed by the successful second attempt.

---

## KI-20 The shop error text has never been measured as a rendered pair — RESOLVED 2026-09-29 (Slice 11)

**Symptom.** `shop-panel.blade.php` renders validation messages in `text-risk` on the form's
`bg-raised` ground, and Slice 10 T3 moved those messages from one combined block to one per field,
so the pair is now on four inputs instead of one. No record measures `risk` as text on `raised`:
`slice-8-2026-09-28.md` §4 and `slice-9-2026-09-28.md` §5 both measured the opposite direction
(`on-chrome` on `risk` 10.89 light / 6.88 dark, and `on-pick` on `risk`), which says nothing about
the red glyph on a raised card.

**Measurement (Slice 11).** Computed from CSS token values in `resources/css/app.css`:

| Theme | Foreground | Background | Ratio | Threshold | Verdict |
|-------|-----------|------------|-------|-----------|---------|
| Light | #800014 (`--color-risk`) | #FFFFFF (`--color-raised`) | 10.04:1 | 4.5:1 AA text | PASS |
| Dark (before) | #FF6B7A (`--color-risk`) | #24262A (`--color-raised`) | 4.33:1 | 4.5:1 AA text | FAIL |
| Dark (after) | #FF7E8C (`--color-risk`) | #24262A (`--color-raised`) | 4.77:1 | 4.5:1 AA text | PASS |

Cross-pair verification after stepping: `border-risk` on `bg-raised` (non-text boundary, 3:1) =
4.77:1 PASS; `bg-risk` with `text-on-chrome` (#121013 on #FF7E8C) = 6.15:1 PASS. Light theme
unchanged at 10.04:1.

**Fix.** Dark-theme `--color-risk` stepped from #FF6B7A to #FF7E8C in `resources/css/app.css`,
following the D-259 precedent (`--color-on-mood`, `--color-on-green`): the ink moves to clear the
threshold while the hue family stays. No component changes needed; the token fix propagates to all
four shop-panel error spans and every other `text-risk` consumer.

**Owner.** Frontend with the design-system owner. Closed by measurement and token step in Slice 11.

---

## KI-21 The race entry form is built on Alpine.js, which is not a dependency, so neither path renders — FILED 2026-09-29 (Slice 12), CLOSED 2026-09-29 (Slice 13, R67)

**Symptom.** On a live run screen, the race panel's two-path entry form rendered no fields at all:
`window.Alpine` false, two `template[x-if]` branches inert, `scenario_slot_id` / `title` / `month` /
`half` absent from the DOM, and the hidden `entry_mode` input posting the empty string. Measured in
`slice-12-2026-09-29.md` §7.2.

**Cause.** `race-panel.blade.php` was written against Alpine, which is not in `package.json` and is
not imported by `resources/js/app.ts`. A `<template>` element's children stay unrendered until a
framework clones them out, so Blade emitted a form the browser never showed.

**Fix (`6c1969f`, route 2 — no dependency).** The form is now server-driven disclosure, the shape
`guided-step` already uses: the mode switch is two GET forms submitting `entry_mode` to the run
screen, and the branch is chosen on the server before the response is sent. `old()` wins over the
query default so a failed write returns to the branch being filled, and the entered values return
with it — including placement, status, circles and period, which sit outside both branches and were
retypeable before. `package.json` is untouched. The calendar session's own comment at
`race-calendar.blade.php:89` — "there is no runtime JavaScript dependency in this project" — is the
evidence this was the house stance and not just the permitted route.

**Proof is the rendered DOM, not the HTML source (R71).** `RaceEntryDisclosureTest` resolves every
field through `DOMDocument` and refuses any whose ancestor chain contains a `template` element. All
8 tests failed against HEAD before the fix; `assertSee('name="title"')` would have passed against the
broken code, which is why the original Slice 11 browser check missed this entirely. The file also
writes a free race through the rendered form rather than posting directly, so the DOM path is proven
end to end and not just the HTTP layer.

**Zero-Alpine grep.** `grep -n "x-data\|@click\|x-if\|template x-if\|x-model"
resources/views/components/race-panel.blade.php` → no output, exit 1. Same across all of
`resources/views`. A first attempt showed one hit, in my own comment quoting `template x-if`; a grep a
comment can satisfy is not a check, so the comment moved.

**Owner.** Closed by Slice 13.

---

## KI-22 A free_race cell renders `state=past` without the Trainer-entered marker — FILED 2026-09-29 (Slice 12), CLOSED 2026-09-29 (Slice 13), FILED ON A WRONG CAUSE

**The filed cause was wrong, and the correction is the useful part.**

What was reported: concurrent commit `82959e9` had deleted R61's free-race branch, evidenced by
`grep -n "isFreeRace" app/Models/TrainingRun.php` returning nothing. What is true: `82959e9` did not
remove the branch, it reshaped it into a `bool $manual` parameter, which `isFreeRace` cannot match.
`git blame` attributes the current `if ($manual)` block to `82959e9` itself at lines 433-436. **A
missing identifier was read as a missing branch** — the grep could not find what it was not looking
for, exactly the failure KI-21 was filed against one section earlier.

The concurrent session reached the same conclusion independently in `5dcc06c`, which identifies what
was genuinely missing: nothing tested the model.

**What the observation really caught**, and the only real defect here: `calendarCell()` checked the
recorded entry before the manual flag, so a free race with a finish took the `past` return and lost
its marker. Slice 13 measured it, declined to invent the requirement, and left the call to the read
path's owner. That owner made it in `b6d68b6 fix(calendar): the Trainer-entered marker survives the
finish`: `app/Models/TrainingRun.php:437` now returns `past` **and** `manual => true` for a free race
with a recorded entry, because `free_race` is provenance about where the record came from, and
provenance does not expire when the race is run.

**Pinned by rendering tests, not by the existing one.** `FreeRaceCalendarCellTest` (`cb9b61f`,
`5ed1ebd`) creates a row, fetches the run over HTTP, and requires the marker inside a `role="img"`
cell with the open dashed treatment, an `aria-label` naming the state, and zero `border-l-goal`
pennants. `RaceCalendarTest.php:276`, which appeared to cover this, hand-writes
`['state' => 'past', 'label' => 'Local Stakes (Trainer-entered)']` into the cells array and asserts
the substring — it never calls `calendarCell()`, so it passes on label text whether or not the model
emits `manual`.

**Owner.** Closed by Slice 13 with the read path's own fix. Slice 12's §7.3 stands as filed and is
corrected forward in `slice-13-2026-09-29.md` §4, not edited in place.

## KI-23 `uma:fetch` never fills `umamusume.name_ja`, and its own fixture repeats the parser's wrong key — FILED 2026-09-29 (skills pass), OPEN

**The defect is a key name, and the reason it survived is that the test was written from the parser
rather than from the source.**

`app/Services/DataPipeline/Parsers/GametoraCharacterParser.php:92` emits
`'name_ja' => $this->textOrNull($card['name_ja'] ?? null)`. The live document has no `name_ja` key.
Measured 2026-09-29 against the manifest's current `character-cards.e9e9ee6d.json` (268 records): the
name-bearing keys are `name_en`, `name_jp`, `name_ko`, `name_tw`, `url_name` — `name_jp` is populated on
268 of 268, `name_ja` on **0**. The expression therefore always yields null, and every character promoted
by `uma:fetch` stores no Japanese name. `PRD.md` FR-A-1's "Japanese name (nullable until
cross-referenced)" is being satisfied by the nullability rather than by the fetch.

**Why the suite cannot see it.** `tests/Feature/GametoraCharacterParserTest.php:22` builds its rows
through a `card()` helper that writes `'name_ja' => $jp`, and the committed
`tests/Fixtures/gametora-character-cards.sample.json` carries the same key. Both inputs were authored to
the parser's expectation, so the assertion at `:39` — `name_ja` is `エピファネイア` — passes against a
shape the source does not produce. The sample fixture holds 7 of the live document's 33 keys; `aptitude`,
`skills_innate`, `skills_unique`, `title_en_gl` and the rest are absent from it, so it is a sketch of the
document, not a capture of it. This is the KI-22 failure mode one layer down: a test that hand-writes the
producer's input cannot falsify the producer.

**Why a seeded database looks correct.** `php artisan tinker` reports `umamusume` rows = 2, rows with
`name_ja` = 2 — both from `UmamusumeSeeder`, which writes the value directly. The seeder fills the column
the fetch leaves empty, so local inspection confirms the wrong thing.

**What fixing it needs.** Read `name_jp`. Rebuild the fixture from a slice of the live document so the key
names belong to the source, and add an assertion that a live-shaped row yields a **non-null** `name_ja` —
without that direction, a corrected key and a broken one both satisfy a fixture written to match.
`ADR-0011` records that the skills parser does not inherit the pattern (it reads `name_en` and `jpname`,
and its fixture is cut from the live document).

**Owner.** Data Engineer. Out of the skills pass's scope because it edits the characters parser and its
fixture, not the skills path; filed here because the skills import was only found by measuring the same
document family, and the next reader of `GametoraCharacterParser` should not have to rediscover it.

## KI-24 A stale source hash answers 200 with stale content, so a pinned URL fails silently — FILED 2026-09-29 (skills pass), OPEN

**The comment's safety claim is the defect.** `config/uma.php:50-54` states, of the cache-busting token in
each source URL: *"it rotates when the source republishes, so a stale hash surfaces as a fetch failure and
not as silently old data."* Measured 2026-09-29, it does the opposite:

| URL | HTTP | Body |
|---|---|---|
| `skills.f4a1e02d.json` (the hash every `UMAMUSUME_REFERENCE.md` citation names) | **200** | 1,910 rows, 621 stated available on `[Global]` |
| `skills.609afe88.json` (today's manifest value) | 200 | 1,910 rows, 623 available, **68 rows differ in content** |
| `character-cards.679f7c2e.json` (**live `gametora-characters` pin**) | 200 | 251,242 bytes |
| `character-cards.e9e9ee6d.json` (today's manifest value) | 200 | 251,294 bytes |

Old hashes keep serving, so a pin does not fail loudly — it quietly fetches an outdated document forever.
The characters source is in that state now: `uma:fetch` pulls a roster three days behind the publisher's
current document with nothing to report. Nothing in `ARCHITECTURE.md` §5 requires the pin either; its only
hash language is snapshot-content hashing for idempotence (`:192`, `:212`), so a write-up that cites §5 for
"resolve through the manifest rather than hardcoding" is citing a sentence the file does not contain.

**Two consequences, one of them about the corpus.** `ADR-0011` §1 resolves the skills URL through
`https://gametora.com/data/manifests/umamusume.json` at fetch time for exactly this reason, and records the
resolved hash as provenance. Separately, the corpus's `[B]`-tier citations
("`skills.f4a1e02d.json`", "`character-cards.679f7c2e.json`") point at documents that still resolve and
are no longer current — which is **anchor drift, not a wrong fact**: the hash in a citation is provenance
about when the claim was measured, so the citations must not be rewritten to the new hashes. `D-254`'s
dated-snapshot policy already covers how a reader should treat them.

**What fixing it needs.** An owner decision on the engine's URL model, because it changes both existing
sources: manifest resolution at fetch time, a documented pinned fallback, and the resolved hash recorded on
`data_sources` so a later reader can tell which document a fact came from. The `config/uma.php` comment is
then corrected to state what actually happens.

**Scope as it stands after `ADR-0011`: this is half-fixed, deliberately.** `gametora-skills` resolves
through the manifest now. The two older sources still pin, and re-measured the same day: `character-cards`
is pinned at `679f7c2e` while the manifest publishes `e9e9ee6d`, so **the characters import is serving a
superseded document today**; `race_instances` is pinned at `294424fc`, which matches the manifest right now
and will therefore go stale silently at its next republish, the same way the characters pin already did. A
reader who concludes the pinning problem was solved with the new source has read it wrong.

**Owner.** Architect with the Data Engineer. Discovered while approving a third source, which is the point
at which the pinning convention was about to be copied forward.

---

## KI-25 The turn log forces the page into horizontal scroll at phone width — FILED 2026-09-29 (Slice 15 browser pass), CLOSED 2026-09-29 (Slice 16 T1), RE-OPENED 2026-09-29 (R85), OPEN

**Re-opened by R85, and the reason is the measurement half, not the markup.** `b8c0a54` landed the focusable
scroll region: the nine-column log now sits inside `overflow-x-auto` with `role="region"`, `tabindex="0"`
and `aria-label="Turn log"`, matching `race-calendar.blade.php`, and `tests/Feature/TurnLogScrollRegionTest.php`
(2 tests, 12 assertions, passing) proves both regions carry those attributes in the rendered page. That work
stands and is not being reverted. What is withdrawn is the closure, because **a PHP assertion can only read
rendered attributes, and the claim being closed was about behaviour**:

- whether arrow keys actually scroll either region — never pressed in a browser;
- whether scoping the overflow to the table removed the document-level sideways scroll at all, i.e. whether
  `scrollWidth` is still 476 at a 390px viewport after the change — never re-read;
- the other eight columns, the race calendar's own traversal, and every other region on the screen —
  originally probed as one element and never swept;
- and the closed text cited `docs/design-research/verification/slice-16-2026-09-29.md`, a file that does not
  exist. A closure whose evidence is a dangling path is not a closure.

**What would re-close it.** Two things, in this order. First the **responsive contract**: D-40's standing
sentence — "no mobile-first compromise is accepted in exchange for desktop density" — has to be reconciled
with the 768px floor `b8c0a54` wrote into `DESIGN.md` §2.3, and that amend is itself held pending R82,
because a contract the owner has not ratified cannot be the basis for closing a defect. Second, a
**read-only browser measurement** whose numbers are recorded beside the closure rather than inferred from
the attributes that make it possible.

**The `DESIGN.md` §2.3 floor travels with this issue.** It was written on the strength of this closure, so
until the measurement pass runs, "768px is the supported minimum" is a proposal resting on an attribute
read, and §2.3 says so.

**Owner.** Frontend/Design-system with Architect, unchanged: the fix needs a breakpoint decision the repo
does not currently record. Found while measuring something else — the Slice 15 browser pass was sent to
check contrast on a new turn control, and the overflow surfaced only because the same script read the
viewport width too.

**The 2026-09-29 closure text is kept below, struck as withdrawn rather than edited away, so the sequence
is auditable.**

~~**Closed as a designed fallback, which is not the same as fixed.** Slice 16 T1 (R81) scoped the horizontal~~
~~scroll to the table instead of the page and gave the scroll container a tab stop… the disposition is a~~
~~ruling about reachability.~~ Its two load-bearing sentences — that the columns past the edge "are now
reachable by arrow keys", and that the runtime fact was "measured in the Slice 16 browser pass" — are
withdrawn. Both rested on the rendered attributes the test reads, not on a browser read.

**Original defect text kept below for traceability.**

**Symptom, measured rather than inferred.** At a 390 × 844 viewport the run screen's document reports
`scrollWidth 476` against `innerWidth 390`, so the whole page scrolls sideways. Eleven elements sit past
the right edge and all eleven trace to one root: the turn log `table.mt-3 w-full border-collapse text-sm`
in `resources/views/runs/show.blade.php`, measured 460px wide across its nine columns (Turn, Speed,
Stamina, Power, Guts, Wit, SP, Condition, Mood). `w-full` cannot shrink a table whose columns demand more
than the container gives them, so the width is the content's, not the stylesheet's.

**This is not a Slice 15 defect, and the way to say that is the diff, not the assertion.**
`git diff 72e5157..HEAD -- resources/views/` for this slice is one file,
`resources/views/components/race-panel.blade.php`, 22 insertions — the turn dropdown and the read-back
chip, which measured `x=42, w=106` and sits fully inside its own panel at 390px. The table was not
touched by any commit in the range.

**Why it was filed at all, when the first reading blamed this slice.** The mobile probe's own output was
`horizontalOverflow: true` on a page this slice had just edited, and the next thought was to fix what had
just been written. Naming the overflowing element is what replaced that guess with a measurement, and it
is the same correction Slice 12 and Slice 13 record in `slice-15-2026-09-29.md` §8.4: reach for the cause
the instrument reports, not the one that fits the story.

**Current behaviour is unverified rather than verified-safe.** No screen width below 476 CSS px has ever
been a stated target for this tool — `CONSTRAINTS.md` and `DESIGN.md` carry no breakpoint contract for the
run screen — so this may be a known and accepted shape rather than a regression. It is filed because a
Trainer on a phone cannot read the log without dragging the page, and nothing in the repo says that is
intended.

**What fixing it needs.** A decision about which of the nine columns the log actually shows at phone
width, and that is a layout ruling on a component this issue's author does not own:
`resources/views/runs/show.blade.php` belongs to the frontend surface, and `DESIGN.md` carries the
responsive rules. The mechanical options are a horizontally scrollable container scoped to the table
rather than the page, a stacked card rendering below a breakpoint, or fewer columns; the first is the
smallest diff and the last loses data. None is chosen here.

**Owner.** Frontend/Design-system owner with Architect, since the fix needs a breakpoint decision the repo
does not currently record. Found while measuring something else: the browser pass was sent to check
contrast on the new turn control, and the overflow surfaced only because the same script read the viewport
width too.
