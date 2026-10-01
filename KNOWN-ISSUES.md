# Known issues

Defects found during the scenario-aware design and component work that were **out of that
phase's scope**, so they are recorded here rather than fixed in passing. Each entry states
the command or file that proves it, not just the symptom.

Discovered 2026-09-27. None of these were introduced by the component work; the component
work is what made them visible, because the prototype phase had no running server to hit.

**Status (2026-09-30, KI-33 / KI-36 pass):** **35 filed, 24 closed, 11 open**, nothing filed here and two
closed. KI-33 closes across `dd90330` (the two json lists, the parser that keeps them, the pre-populate at
run creation) and `4902f1d` (the repeater it called a required part); KI-36 closes in the same `4902f1d`,
which is the same-commit grouping R-3 asked for rather than two commits touching one block. Both closures name what
they do not cover: KI-33's four-group picker is unblocked by the storage and still unbuilt, and the
`skills_innate` / `skills_unique` columns are not yet in ADR-0008, `ARCHITECTURE.md` §3, the ESSENTIALS
digest or D-30, because this session was barred from those files and `DocSchemaDriftTest` only pins
`training_runs`. That doc gap is the next register pass's business, not a reason to hold the schema.
Prior:
**Status (2026-09-29, trainee detail and skill selector pass, updated with KI-26 closure):** **35 filed, 22 closed, 13 open.** Four
entries land here from the design pass (KI-33, KI-35, KI-36, KI-37), and **KI-26 is closed** against `f2c978b`
(unescaped `LIKE` in `CatalogController` fixed on master; register lagged by one commit). **KI-34 is a reservation,
not a lost entry**: it is the per-character goal-race filing from the previous pass, deliberately not landed by
this block's sequencing. Unlike KI-16 — which is a genuine renumbering hole, KI-30's pre-merge number —
this gap has an owner and a next step. A heading grep reads 25 closed and 14 open (including KI-10 and KI-25 which
carry both words, plus KI-23b and KI-24b) where the register says 22 closed and 13 open (KI-10 ratio half counted
open, KI-26 closed). Both are headings holding more history than a regex reads. A heading grep for `CLOSED` or
`OPEN` over-counts, because a heading can name both states (KI-10's schema-half-closed/ratio-half-open, KI-25's
closed-then-reopened). The three numbers in this block are the authoritative counts; a heading grep is indicative
only.
Prior:
**Status (2026-09-29, Screen D dark-theme pass):** **31 filed, 21 closed, 10 open**, one filed here.
**KI-32** is the missing `color-scheme` declaration: native form controls keep painting light widgets on
the dark surface, observed as two different unchecked renderings of the same checkbox across loads. The
dark pass itself is clean — every contrast pair, overflow and focus measurement on both themes is in
`SKILLS-GAPS.md` §8. Prior:
**Status (2026-09-29, `fix/frontend-audit-2026-09-28` merge):** **30 filed, 21 closed, 9 open**, two filed here
and both closed here, neither by this merge's own hand. The branch carried them as KI-16 and KI-17; both were
renumbered to KI-30 and KI-31 because master's register already holds KI-17 and documents KI-16 as never
filed. They arrive **closed**: the landing-route pin by R57's redirect, which `ExampleTest` now asserts by name,
and the six `SkillAutomationTest` failures by measurement at `eb23fa8` — 7 passed there, 0 failing suite-wide —
with the fixing commit deliberately left unnamed rather than guessed. Prior:
**Status (2026-09-29, Screen D browser pass):** **28 filed, 19 closed, 9 open**, one filed here. **KI-29** is
`/umamusume`'s form controls measuring 30/31/32px against `DESIGN.md` §6.14's 44, found by measuring the
rendered page rather than reading the classes; Screen D's own controls are fixed, the older surface is not.
Two of this thread's three open items on that one file (KI-26, KI-29) can be cleared by whoever next owns
`catalog/index.blade.php` and `CatalogController`.
**Record, not a defect:** `b8a296f` carries `83086b0`'s subject line pasted in error; its body and content
are correct (the three fixture rows Screen D renders — `200471`, `300141`, `202391`). Nothing downstream
cites that SHA, so the mismatch is cosmetic and it is **not rewritten**: the commit sits 14 deep with
concurrent sessions landing on top of it in a shared `.git`, and a rebase to fix one subject line is not
worth the shared-history cost. The rebase stays possible whenever the tree is quiet; this line exists so a
reader who hits the mismatch in `git log` meets the record instead of re-diagnosing it. Prior:
**Status (2026-09-29, catalogue fill pass):** **27 filed, 19 closed, 8 open**, one filed here. **KI-28**
is the silent one: with a column absent from the schema, SQLite reads the quoted identifier inside
`whereNotNull('col')` as a **string literal**, so the predicate is true for every row and nothing is
raised. It surfaced because a count of linked race entries returned 1 against a `race_entries` whose
`PRAGMA table_info` lists no such column — and the unquoted spelling of the same query threw
`no such column`. An un-migrated database therefore drops every catalogue↔entry link without
complaining. The mitigation is the record itself: no `Schema::hasColumn` guard goes into the query
path, because the trap is SQLite's behaviour and every future column addition inherits it. This pass
also **met KI-27 in the wild** while filling the catalogue — `uma:fetch` reported "unchanged since last
snapshot; nothing written" against a database holding 0 rows, because a throwaway fetch had already put
the same document on the shared `storage/` disk — and the reparse workaround KI-27 names is what wrote
the 410 rows. That is a second reproduction of an open entry, not a new one. Prior:
**Status (2026-09-29, Screen D pass):** **26 filed, 19 closed, 7 open**, two filed and none closed here.
**KI-26** is the unescaped `LIKE` on `/umamusume`: a search of `%` returns the whole catalog rather than the
rows whose key contains that character. Reproduced through that controller, not by a hand-written query, and
left unfixed here because `CatalogController` is not this pass's surface and the file is on master in a shared
tree. Screen D escapes and tests the difference. **KI-27** is the fetch that reports itself complete against a
database it never wrote to, found by actually filling the real database rather than a scratch one. Prior:
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
**Status (2026-09-29, trainee detail and skill selector pass):** **35 filed, 21 closed, 14 open.** Four
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
KI-22. Prior:**Status (2026-09-29, Slice 12 T3):** 21 issues filed. **16 resolved/closed** (KI-1–9, KI-11–14,
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

**Forward, 2026-09-30 (documentation inventory, snapshot `c1e14a3`).** The layout half landed:
`layout.blade.php` now reads `@vite(['resources/css/app.css', 'resources/js/app.ts'])` at line 24,
so this entry stays resolved. Two things in the record above no longer resolve, and neither was
edited when it stopped being true. The citation `resources/views/welcome.blade.php:15` points at a
view deleted at `65f8b92` on 2026-09-29 while closing KI-20. And `resources/js/app.js` is still
described as the current entrypoint by two live guidance files, `.ai/guidelines/framework/core.md`
and `.ai/skills/tailwindcss-development/SKILL.md`, both of which quote the
`@vite(['resources/css/app.css', 'resources/js/app.js'])` boilerplate. Nothing guards that pair:
this entry's own resolution test checks HTTP status, not documentation strings.

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

**Forward, 2026-09-30 (documentation inventory, snapshot `c1e14a3`).** That question is closed by
deletion rather than by answer: `Route::view('/', 'welcome')` became a redirect to `runs.index`,
`welcome.blade.php` was deleted, and KI-20 closed, all in `65f8b92` on 2026-09-29. `ExampleTest`
asserts the redirect by name. The 38 KB blob went with the file. The `**Evidence.**` block above
keeps its line numbers as recorded, because it is the output of a grep that ran on 2026-09-28
against a file that existed then; a reader who runs it today gets no match, and that is the
expected result, not a broken gate.

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

## KI-26 A search of `%` returns the whole catalog, because `CatalogController` interpolates the query into a `LIKE` it never escapes — RESOLVED by `f2c978b`, CLOSED 2026-09-29

**What is wrong.** `app/Http/Controllers/CatalogController.php:39` folds the `search` query through
`NameNormalizer`, and `:44-49` interpolates the result into `match_key LIKE "%{key}%"` (and the same shape
into `lower(alias) like ?`). `NameNormalizer` lowercases, NFKD-folds and strips marks, spaces and dashes —
it does not touch `%` or `_`, and SQLite's `LIKE` has **no default escape character**, so both are pattern
wildcards. A Trainer typing `%` is not asking for a skill whose name contains a percent sign; they are
asking for every row, and the screen answers as though that were the intended question.

**Proof, through the controller rather than a query I wrote.** `GET /umamusume?search=%` returns
`total=2 of all=2` against the seeded catalog, and `?search=_` likewise, while `?search=zzzqqq` returns `0`.
Two rows is the whole table, so the mechanism is visible even at seed size. Measured on the imported
`skills` table, the same clause shape is the difference between **623 of 623** `[Global]` rows for an
unescaped `%` and **1** row for an escaped one, which is the row whose client name really is
`Givin' It 1000%`. The alias branch is the same defect one clause over.

**Why it matters more than an odd empty result.** The screen does not say "this was treated as a pattern".
It prints the total count and the pagination, so `%` reads as *every skill matches your search*, which is
the failure this register keeps naming: a control that answers a question nobody asked.

**Why it is not fixed here.** `CatalogController` is on master, is not Screen D's surface, and the tree is
shared with at least one other session editing views. The new surface escapes and is tested; that is the
whole of what this pass could do without reaching into another file's behaviour.

**What fixing it needs.** Escape `%` and `_` (with `ESCAPE '\'`, or `addcslashes($key, '\%_')` into a named
escape clause) in both the `match_key` and the alias branch, plus one test that a literal `%` query returns
the rows containing one and not the rows that merely exist. `Screen D`'s version is
`SkillController::query()` with `SkillSearchScreenTest`'s wildcard case, so a shared helper is the shape the
fix probably takes — but that is two surfaces' behaviour to change together, and it belongs to whoever next
owns `CatalogController`.

**Closed 2026-09-29 against `f2c978b`.** The `LIKE` metacharacters are escaped at
`CatalogController.php:60` and `:82`; `CatalogRosterTreeTest.php:159` pins `%` and `_` as
literals. The fix commit named the defect in its subject and did not touch this file, so the
register lagged the tree by one commit. No re-fix is owed.

**Owner.** whoever picks up `CatalogController`; found while building the second server-driven filter
surface and noticing the first one had no escape.

## KI-27 `uma:fetch` reports "unchanged since last snapshot" against a database it never wrote to — FILED 2026-09-29 (Screen D pass), OPEN

**What is wrong.** `app/Services/DataPipeline/SourceFetcher.php:57-59` builds the snapshot path as
`snapshots/{source}/{today}/{sha256(body)}.html` on `Storage::disk('local')` and sets
`$unchanged = Storage::exists($path)`. Both halves of that key — the document's hash and the date — are
properties of **the document**. The thing that actually decides whether rows must be written is **the
database in front of the pipeline**, and that never enters the check. `storage/` is shared by every
database in the working tree: `database/database.sqlite` and whatever `.scratch-uma/*.sqlite` a session
points `DB_DATABASE` at. First fetcher writes the snapshot; every other database asked the same document the
same day is told nothing changed, and stays empty.

**Proof, from filling the real database rather than a scratch one.** After the import pass had written
1,910 rows into `.scratch-uma/skills-c5.sqlite` at 10:37 today, `php artisan uma:fetch gametora-skills`
against `database/database.sqlite` printed `'gametora-skills' unchanged since last snapshot; nothing
written.` and `release_status='GlobalReleased' and name_is_client=1` still counted **0** there, with
`storage/app/private/snapshots/gametora-skills/2026-09-29/609afe88…​.html` (2,502,242 bytes) already on disk.
`php artisan uma:reparse gametora-skills` — same snapshot, zero network — then wrote
`7 updated, 1903 created, 0 skipped (manual), 0 to review`, and `/skills` renders **623 of 623**.

**Why it is worse than an idempotent no-op.** The message is true about the document and misleading about
the Trainer's data, and NFR-2 promises a failed or skipped fetch leaves previous data intact rather than
promising the rows exist. A Trainer who runs the command the screen itself names, reads "nothing written",
and has no way to know a second command is the one that works. Screen D's empty state and the README both
now name both commands, which treats the symptom in copy; the check is the thing that should change.

**What fixing it needs.** The short-circuit has to be a property of the target, not only of the disk. Three
shapes: compare against what the database already holds for that source; keep the snapshot as a body cache
but continue into the pipeline anyway; or key the snapshot path per database. The middle one looks nearly
free and there is evidence for it — `StoreSkills` upserts on `export_id` and the reparse above re-ran over
seven adopted rows without duplicating anything, so re-running the pipeline on unchanged bytes is already
safe. Choosing among them is Architect's, and it touches every source, not just skills.

**One thing this ruled out, because it looked like a defect and is not.** The reparse path stamps
`source_url` with the **pinned** URL rather than the manifest-resolved one (stated in `UmaReparse`'s
docblock). Here the pin and the document agree — `skills.609afe88.json` against a snapshot whose sha256
begins `609afe88` — so all 1,910 rows' provenance is accurate, and `snapshot_path` carries the full hash
regardless. KI-24 is about the pin going stale; today it has not for this source.

**Owner.** Data pipeline, with Architect for the decision. Found by doing the thing the screen tells a
Trainer to do.

## KI-28 SQLite reads an unknown double-quoted identifier as a string literal, so a missing column fails silently — FILED 2026-09-29 (catalogue fill pass), OPEN as a hazard; the record is the mitigation

**The hazard.** SQLite degrades unknown double-quoted identifiers to string literals. With a column
absent from the schema, `whereNotNull('col')` does not throw — SQLite parses `"col"` as the literal
string `"col"`, which is never null, so the predicate is always true. An un-migrated dev database will
silently drop every catalogue↔entry link without erroring. Post-migrate this trap goes away, but any
future column addition has the same failure mode.

**Proof, from the fill pass.** `database/database.sqlite` had six pending migrations and
`PRAGMA table_info(race_entries)` listed 11 columns with no `race_catalog_slot_id`. In one process, in
this order:

| Query | Result |
| --- | --- |
| `DB::table('race_entries')->whereNotNull('race_catalog_slot_id')->count()` | **1**, no error. Laravel quotes identifiers with `"`, so SQLite read the column name as a string and matched every row. |
| `select race_catalog_slot_id from race_entries limit 1` — unquoted | `General error: 1 no such column: race_catalog_slot_id` |
| `select "race_catalog_slot_id" as v from race_entries limit 1` | returns the **string** `race_catalog_slot_id` as column `v` |

The two readings that looked contradictory during the diagnosis — a count that succeeds against a
column the schema denies — are the same query with and without the quoting style that decides which of
SQLite's two interpretations fires.

**Why it matters more than a missing column usually does.** A read that throws is a screen that fails
and gets filed. This one returns a plausible number: `TrainingRun::calendarCells()` calls
`whereNotNull('race_catalog_slot_id')` and, on an unmigrated database, receives **every** race entry
keyed by a null attribute — so no catalogue cell finds its entry and the grid quietly reports races as
never run, on a page that returns 200. The write path is louder for once: an `INSERT` naming a column
that is not there is a hard error, which is why `uma:fetch` has to follow `php artisan migrate` and not
the other way round.

**What this does not need.** No `Schema::hasColumn` guards in the query path. The trap is SQLite's
behaviour rather than the code's, and a guard per query is a tax paid forever against one database that
was behind its own migrations. The record is the mitigation: read `php artisan migrate:status --pending`
before trusting a zero from a linked-column count on a development database.

**Owner.** nobody — hazard record. Filed so the next session whose `count()` succeeds against a column
`PRAGMA` denies does not spend an afternoon on it.

## KI-29 `/umamusume`'s form controls measure 30/31/32px against DESIGN.md §6.14's 44 — FILED 2026-09-29 (Screen D browser pass), OPEN

**What is wrong.** `DESIGN.md` §6.14 is the tool's one deliberate extension of the client's interface ("the
client has almost no text inputs, so this is our extension and it must not look like one") and it fixes an
input at **height 44**, radius 8, `body` text in `ink`, with a 2px `green` focus border. The catalog
index's controls are 30px and 31px tall. They are built from `rounded-md border border-rule bg-raised px-2
py-1`, which sets no height, so the browser's intrinsic size wins and §6.14's number is simply absent.

**Proof, measured in a rendered page at two widths.** `getBoundingClientRect` on `http://127.0.0.1:8123/umamusume`:
`input[name=search]` **177×30**, `select[name=status]` **162×31**, `button[type=submit]` **56×32** — identical
at 1280×800 and 390×844. Screen D was written by copying that markup, so it measured the same 30/31 until
the browser pass caught it; its controls are now **44/44** (`h-11`, which is already this repository's idiom
for a 44px row in `epithet-checklist`, `race-calendar` and `grade-point-meter`) and its checkbox is 24×24.

**What it is not.** Not an accessibility failure: 30–32px clears WCAG 2.2 AA's 24px target floor, and the
focus ring on that surface is present and 2px green. So the claim is bounded to what the file says — the
surface does not meet the project's own written spec, and the reason §6.14 states for existing (not looking
like a generic web form) is the reason it matters.

**Why it is not fixed here.** Same posture as KI-26: `resources/views/catalog/index.blade.php` is on master,
is not Screen D's surface, and the tree is shared with sessions actively editing views. Adding `h-11` there
is a one-class change and it should go with whatever else that file's next owner does — which is also where
the `LIKE` escape from KI-26 lives, in the controller behind it.

**Owner.** whoever next owns `catalog/index.blade.php`. Found by measuring the new screen against the old one
rather than trusting that copied classes produced a spec-compliant result.
---

## KI-30 The landing route was pinned to HTTP 200 by a stock test, and nothing recorded it — FILED 2026-09-28 on `fix/frontend-audit-2026-09-28` (as KI-16), CLOSED 2026-09-29 (merged tip)

**Renumbered on merge.** It arrived as `KI-16` on `fix/frontend-audit-2026-09-28`. The register's own recipe
reads counts off `grep -c "^## KI-"` and states twice that **KI-16 was never filed**, which is why the numbers
run past it — and `PLAN.md:521` plus two lines of the slice-15 verification record name that hole. Filling it
would have retroactively falsified four written statements, so the entry lands at the end of the sequence and
the hole stays a hole.

`tests/Feature/ExampleTest.php:7` asserted `$this->get('/')->assertStatus(200)`. That was the framework's own
example test, left in place, and it was the only thing in the repository constraining what `/` is allowed to
be. The audit's F-1 offered two fixes for the skeleton splash — render it through the app shell, or delete the
route and redirect `/` into the product — and the redirect option was rejected specifically because a 302 would
fail that assertion, in a file the frontend slice was told not to edit. So a decision about the product's front
door was being made by a leftover framework test that never says it is doing that. The coupling is not wrong,
exactly: a local tool answering 200 on `/` is defensible, and a hand-typed `localhost:8000` should not bounce.
It is the silence that is the problem. **Required fix:** either the owner states the contract in `PRD.md`
("`/` is a product surface and answers 200") and `ExampleTest` is replaced by an assertion that names it, or
the coupling is accepted knowingly and this entry closes as a decision. Not fixed in
`fix/frontend-audit-2026-09-28`, which chose the shell option and so kept the 200 either way.

**Closed by the owner's later ruling, not by this merge.** R57 made `/` a redirect into the product, and
`ExampleTest` on the merged tip now reads `it('redirects the home page to the runs index')` asserting
`assertRedirect(route('runs.index'))` — an assertion that names its own contract, which is the outcome this
entry asked for. Verified against `eb23fa8`, not inferred from the branch. The `PRD.md` half of the Required
fix is **not** done and is not claimed here; the coupling is now recorded rather than silent, which is what the
entry actually gated on.

**Owner:** closed. The `PRD.md` wording, if it is ever wanted, remains the owner's.

---

## KI-31 Six `SkillAutomationTest` failures had no owner and predated the frontend audit — FILED 2026-09-28 on `fix/frontend-audit-2026-09-28` (as KI-17), CLOSED 2026-09-29 (merged tip) ON MEASUREMENT, CAUSE NOT ESTABLISHED

**Renumbered on merge, and this one was a real collision:** master's KI-17 is a different defect — the
consecutive-race count, filed Slice 8 and closed Slice 15 — and carries thirteen references in the register.

`tests/Feature/SkillAutomationTest.php` failed six tests: *discovers skills from the registry*,
*matches skills to task descriptions by relevance*, *ranks the most relevant skill first*,
*builds an execution plan in dependency order*, *executes a skill without parameters*,
*auto-executes skills for a task and discloses matches*. They were not caused by any recent slice.
Measured evidence: at `7d4b8cf` (base of `fix/frontend-audit-2026-09-28`) the full suite reported
exactly those 6 failures and no others, and they were already present at `a292ef7`, where they were
reproduced with unrelated work stashed. Nothing in `KNOWN-ISSUES.md`, `PLAN.md`, the ADRs or the
2026-09-28 audit named an owner for them, which meant every slice since shipped against a red
`CONSTRAINTS.md` C-1 gate and treated it as background noise. **This entry was a paper trail, not a
fix.** The failures were untouched there: the frontend slice has no remit over
`app/Services/SkillRegistry.php`, `SkillMatcher.php` or `SkillExecutor.php`, and guessing at a
skill-matching contract without its author is how 6 become 8. **Required fix:** someone owns the
skill-automation subsystem, states whether the six expectations are still the spec, and either
repairs the code or retires the tests with a reason in the commit message, per the `CONSTRAINTS.md`
floor on skipped tests.

**Closed on measurement at the merged tip.** `php artisan test tests/Feature/SkillAutomationTest.php` at
`eb23fa8` reports **7 passed (17 assertions)** and the full suite reports **676 passed, 2 skipped, 0 failed**,
so C-1 is green for the first time in the thread this entry opened. **What turned it green is not established
here.** The test file has not changed since it was added at `cf8021d`; the candidates are `aa5b05c` (the
ADR-0011 skill catalogue rework, which rebuilt the data the matcher and executor read) and `f0f508c` (the
PHPDoc pass across those same classes). Naming one without bisecting would be a guess, and this register's own
KI-22 — "filed on a wrong cause" — is the reason not to. The ownership question stands unanswered: nobody has
stated whether the six expectations are the spec; they simply pass now.

**Owner:** unassigned for the subsystem. This closes as no-longer-reproducing, not as adopted.

## KI-32 No `color-scheme` is declared, so native form controls paint light widgets on the dark theme — FILED 2026-09-29 (Screen D dark pass), OPEN

**What is wrong.** `resources/css/app.css` declares no `color-scheme` anywhere (`grep -n "color-scheme" →
no output`), so a browser keeps using its **light** UA skin for native controls — checkbox, `select`
dropdown, scrollbars, any future date or number input — while the page around them is the dark theme. The
dark theme here is a token override (`D-101`), and tokens do not reach a control the browser paints itself.

**Proof, and its limit.** On `http://127.0.0.1:8123/skills` with `prefers-color-scheme: dark` emulated, the
same unchecked `input[name=unique]` computed `backgroundColor: rgb(255,255,255)` on one load and
`rgb(36, 38, 42)` — `#24262A`, this project's own dark surface anchor — on another, with nothing in the page
different but the order in which the theme was applied during my measurement sequence. So the reproduction
is "the same control paints two ways depending on load order", which is the defect's actual shape, and it is
**not** a deterministic screenshot diff. The checked state is unambiguous either way: blue fill with a white
tick, distinguishable from empty at 24px.

**What it is not.** Not a contrast failure, and not a reason to hold the screen. Every pair measured on
`/skills` in dark clears AA comfortably — `ink-muted` on the list **6.64**, on the page **8.55**, `h1`
**19.51**, the ✦ badge **17.61**, row name **15.15** — with no page-level horizontal overflow at 1280 or 390
and a computed `solid 2px rgb(127,204,9)` focus ring, same as light.

**What fixing it needs.** Two declarations: `color-scheme: light` on the root and `color-scheme: dark`
inside the existing `html[data-theme='dark']` block, which is the block `D-101` already owns. Not done here:
`resources/css/app.css` is the design-system surface and other sessions are editing views and that file in
this shared tree — the same posture as KI-26 and KI-29.

**Where the two declarations actually live.** `color-scheme: light` sits in a `:root` rule at
`resources/css/app.css:219`, beside the `@theme static` block rather than inside it; `color-scheme: dark` is
at `:229` inside `html[data-theme='dark']`. Tailwind v4's `@theme` block rejects non-custom-property
declarations (`@theme blocks must only contain custom properties or @keyframes`), so the light-theme
declaration cannot go in the block and was never going to. A later reader following this entry's fix direction
should not "fix" the `:root` rule back into `@theme`.

**Owner.** design-system. Found while closing the dark-theme gap on Screen D, by measuring a native control
rather than trusting that a token override covers everything drawn on the page.

## KI-33 A trainee's own innate and unique skills are published by the source and stored nowhere, so a run cannot pre-populate them — FILED 2026-09-29 (per-trainee skill scoping pass), CLOSED 2026-09-30 (Slice A storage and pre-populate, Slice B repeater)

**Symptom.** A run created for a trainee renders "None." in its Skills section
(`resources/views/runs/show.blade.php:405`) because nothing in the schema records which skills are hers.
The picker cannot group by "her skills", and D-44's `Suggested` state — planned *before* the run — has no
data to be planned from, so the plan-versus-actual comparison the screen exists to show is empty on turn
one and only becomes real after the Trainer types the list by hand.

**Cause, stated against the ref it was measured on.** `GametoraCharacterParser` reads `char_id`, the name
fields, the release fields and the ten aptitude columns, and **none of the six `skills_*` keys** — they
arrive in the document and are dropped at parse time. There is also no table to put them in on `master`:
`character_cards`, `CharacterCard` and `ADR-0008` live on `feat/catalog-roster-and-trainee-selector`
(tip `cf5fc75`) and are absent from this ref, which is what `SKILLS-GAPS.md` G-SK-6 records. One
prerequisite, then two consequences: the card layer merges, the parser keeps the lists, the columns exist.
The merge gates the rest and is not this entry's to make.

**Fix direction, in dependency order.**
1. Land the card layer (the G-SK-6 merge decision — Architect).
2. `character_cards` gains `skills_innate` as a **json list**, not a scalar: the live document carries
   exactly three innate ids on all 268 records. `skills_unique` is also **a list, not one nullable id** —
   22 records carry two (card `100701` holds `10071` and `100071`), so a scalar column would silently drop
   one of her uniques.
3. The parser reads the keys and the writer stores them, honouring `is_manual` (FR-B-4) like every other
   reference writer.
4. Run creation pre-populates `run_skills` at status `Suggested` from the chosen card's innate and unique
   lists. `skills_evo`'s `{new, old}` pairs and `skills_awakening_en` are the awakening ladder and are
   **out of scope for the pre-populate** — an awakened skill is reached mid-run, not chosen at start.

**Two sub-findings that belong to this entry, not to the design brief that found them (owner ruling
2026-09-29).**

- **The skills form has no repeater, and the pre-populate needs one.** `resources/views/runs/show.blade.php`
  hard-indexes `skills[0]` three times (`:447`, `:455`, `:460`): one row, one submit, no "add another". A
  build pre-populated with four or five rows cannot be *edited* in a one-row form, which is the job the
  pre-populate exists for. This is a **required part** of the entry, not an optional follow-on — a slice
  that lands the storage and skips the repeater has shipped data nobody can plan against. It also edits
  the same seventeen lines as **KI-36**, so the two land together or the label defect is reproduced once
  per row.
- **`syncSkills` is an upsert named as a sync.** `TrainingRunController::syncSkills` (`:546-557`) calls
  `setSkillStatus` per submitted row and never detaches, despite the route being `runs.skills.sync`. That
  is why a save will not wipe a pre-populated row today — a side effect, not a guarantee, and the
  difference becomes a data-loss question the moment the repeater above submits several rows at once. This
  entry's owner decides whether the route is renamed or a `detach` is added; the design brief for the
  selector only records the behaviour so nobody reads the name as replace-all.

**No backfill into an existing run.** A run in progress has actual rows the Trainer entered; deriving
`Suggested` into it after the fact overwrites memory with plan, and every figure on the screen is
Trainer-entered by rule (D-270).

**Source citation.** Live document `character-cards`, resolved through
`https://gametora.com/data/manifests/umamusume.json` to hash **`e9e9ee6d`**, fetched **2026-09-29**, HTTP
200, 251,294 bytes, 268 records, 34 top-level keys. Verified on three cards across three trainees:
`100101` Special Week (`skills_innate [200512, 201352, 200732]`, `skills_unique [100011]`), `100701`
Gold Ship (`[201591, 201212, 201472]`, `[10071, 100071]`), `112701` Fenomeno (`[200742, 202772, 202482]`,
`[101271]`). **Join measured at verification time:** the document references 1,513 distinct skill ids and
**all 1,513 exist in `skills.export_id`** — innate 289/289, unique 290/290 — so this is about storage, not
about a key that fails to match, which is what KI-23 is. The 290 independently reproduces the figure
`ADR-0011` §5 reconciles against `is_unique` 294.

**Availability caveat, recorded so nobody inherits it silently.** Card `112701` has `release_en: null` and
a populated `skills_awakening`: a card form can hold skill data while not being on `[Global]`. The
innate and unique lists are therefore not a Global statement, and the pre-populate must read the same
availability rule `Skill::scopeAvailableOnGlobal()` (`app/Models/Skill.php:80-85`) applies — otherwise the
run screen offers a Global Trainer a skill their client cannot show.

**Owner.** Data Engineer for the parser and the columns, with Architect for the card-layer merge; Planner
Domain Specialist for the pre-populate writer and the repeater at run creation, which is a controller and
form change with its own tests.

**Downstream.** This is the prerequisite `G-SK-13` names: a picker that filters to "her skills" cannot
work until "her skills" is a stored fact, so slicing the picker first reproduces the same defect one level
up.

**Closed 2026-09-30 across two commits.** `dd90330` adds `skills_innate` and `skills_unique` to
`character_cards` as **nullable json lists** and makes `GametoraCharacterCardParser` keep them:
`intList()` filters on each element's type rather than coercing it, because `intval()` of a nested array
returns `1`, so the sketch of a helper that mapped `intval` over the list would have stored the skill id
`1` twice for a malformed value and parsed clean. The store writes both keys through its named projection,
and `TrainingRunController::store()` seeds `run_skills` at `Suggested` inside the same transaction as the
run — creation only, every id resolving through `Skill::scopeAvailableOnGlobal()` because a card's list is
not a Global statement (Fenomeno's `112701` is this entry's own case), and `setSkillStatus` upserts so a
seed cannot duplicate a row. `4902f1d` lands the repeater this entry called a required part.

**Both lists, not one.** The 22 records carrying two uniques are the reason the columns are json: a
nullable `skills_unique_id` would have stored Gold Ship's `10071` and lost `100071` with nothing
downstream able to tell. `CharacterCardParserTest` pins the two-value case on the fixture row this entry
cites, and `TrainingRunTest` pins the four-into-three filtering case — a card with five ids, four of them
Global, seeds four rows.

**The no-backfill rule is tested, not asserted.** `it refuses to backfill an existing run when a later run
is created from the same card` creates a run, sets one skill to `Acquired` at turn 7, creates a *second*
run through the real route, and requires the first run's pivot to be untouched. D-270 says every figure on
a run in progress is Trainer-entered; a pre-populate that reached backwards would overwrite memory with
plan, and only the second creation exposes that behaviour rather than the first.

**What this entry's own text now supersedes.** Its Cause paragraph says `character_cards`, `CharacterCard`
and ADR-0008 "are absent from this ref" and puts the card-layer merge as step 1 of the fix. That landed
before this closure: all three are on master, and `git ls-tree` at `dd90330`'s parent shows the migration,
model, enum, contract, parser, store, factory and three test files. Step 1 was therefore already done, and
the paragraph is kept as written by this repo's convention that a superseded claim becomes an erratum
rather than a silent edit.

**One open end, and it is not this entry's.** The four-group picker (`G-SK-13` / D-3, the combobox that
would group "her innate / her unique / her awakening / everything else") is unbuilt. Reusing
`resources/js/trainee-combobox.ts` for it means generalising a single-instance module hardwired to the
trainee payload, which is a refactor on another session's surface rather than the one-Blade-file change the
brief budgeted. The storage this entry needed is now in place, so that slice is unblocked; it is not
closed here.

## KI-35 The trainee detail page is a metadata stub: ten parsed columns rendered nowhere, no section for skills, forms or goals, and an absence vocabulary its own specification invented — FILED 2026-09-29 (trainee detail and skill selector design pass), OPEN

**Symptom.** `resources/views/catalog/show.blade.php` renders four things and stops: a name header with a
"Back to catalog" link, a four-cell metadata card, an aliases list, a provenance list. That is the whole
page (`:1-71`). A Trainer deciding *which trainee to run* cannot see whether she is Sprint or Long, Turf
or Dirt, Front or End, because the ten aptitude columns are not on it; and no section exists that would
hold her skills, her costume forms or her goal races. The page has **no primary action** — its only
outbound link goes backwards (`:11`) — which contradicts `DESIGN.md` §2.3's one-primary-action-per-screen.

**Three defect classes, and they do not share a cause. Do not let the easiest one carry the other two.**

**Class A — the view renders none of what the schema supports.** `umamusume` carries ten `char(1)`
aptitude columns (`ADR-0004`), documented at `app/Models/Umamusume.php:30-39` and fillable at `:46`, and
`GametoraCharacterParser` already reads all ten keys (`:31-40`, spread at `:101`), with
`tests/Feature/GametoraAptitudeTest.php` on the mapping. `grep -rn aptitude resources/views/` returns
**zero hits**. Measured against the only database in the tree, though: `umamusume` holds **2 rows and all
ten columns are NULL on both**. So the accurate statement is *parsed, schema-ready, unimported, and
unrendered* — and two consequences follow, the second of which a designer meets first. The fix needs a
view and one fetch, no pipeline work at all; and on any database nobody has imported into, this section's
first impression is ten absences, which is a state to design rather than to wait out.

**Class B — sections that should exist while they hold nothing.** Skills, costume forms and goal races
are not stored facts on `master`: the card layer is on its branch (G-SK-6) and `ura-objectives` is
verified but not ingested. None of that blocks a heading. The run screen carries the pattern already and
uses it heavily — "not yet recorded" at `grade-point-meter.blade.php:149`, `:226` and
`guided-step.blade.php:212`, `:247`, `:275`, `:303`; "not recorded" at `grade-point-meter.blade.php:209`
and `mood-pill.blade.php:3`; `N/A` with its reason written down at `resource-strip.blade.php:52-57`. The
trainee page has no such pattern, so what it offers is silence, and silence on a page that lists no
skills reads as "she has none" — false, and the same statement error D-220's matrix warns about for
widgets.

**Class C — three defects in what *is* rendered, and the first is a specification defect, not a view one.**

1. **"Unknown" as a value for JP debut and Global debut** (`:21`, `:25`) is the view obeying its contract,
   not straying from it: `DESIGN.md` §4.2 said *"dateless rows show 'Unknown', never a sentinel (CLAUDE.md
   data rules)"*, and `CLAUDE.md:23`'s actual rule is *"no sentinel **dates** for 'unreleased' (use
   nullable date + `release_status`)"* — a storage rule about what goes in a column. §4.2 turned it into
   display copy, and "Unknown" is what fell out. It reads as a state the trainee is in; every other
   absence in the app is worded as a state of the record. **The correction belongs in §4.2** (landed with
   this entry, same block), and a view fixed before its specification is amended fails review against the
   contract it is meant to satisfy.
2. **§4.2's "amber notice" is unimplementable and the view already says so.** `:33-38` records that the
   token set has no caution chrome, `up` means increase and `risk` means failure, and `pick` measures
   1.60:1 on the raised surface so it cannot carry a boundary; the JapanOnly notice is copy over
   `ink-faint` (3.26:1 light / 4.21:1 dark). Implementation right, specification stale, second clause in
   the same paragraph.
3. **Provenance is the longest sentence on a page with nothing else** (`:58`). A real disclosure — NFR-2
   and US-1 depend on it, and it stays — but as the dominant text it makes the emptiness louder than the
   trainee. The defect is proportion, not presence; and it is already the last section, so the fix is
   quieting, not moving.

**Also in scope, small.** `:47`'s "No aliases yet." is a fourth absence vocabulary on a page already
running a second one. Unify it when the section is next touched.

**What the page should be: a workspace for that trainee, in eight ordered sections.** Identity (same
fields, `N/A` + `title` instead of "Unknown") → Aptitudes grid → Skills → Costume forms → Goal races →
Her runs → Aliases → Provenance, last and quiet. **No brief file is cited for the detail, because none
has been written yet:** the section-by-section states, each section's prerequisite (stored now / frame
ships now / awaits the merge) and the primary-action choice were delivered in the 2026-09-29 design pass
report and live nowhere in the tree. They belong in `docs/design-research/` beside
`replan-mobile-first.md`, in a commit that writes that file — not in a citation that points at it early,
which is the defect KI-25's withdrawn closure was caught for. The eight-section structure itself is
recorded above, and §4.2 now carries it, so this entry stands on its own until the brief is filed.

**Owner.** Frontend with the design-system owner. The disclosure wording is the §4.2 amendment landing in
this block, the aptitude grid is a view change against columns that already exist, and the three empty
section frames need only vocabulary the run screen already uses. The *data* behind sections 3-5 is someone
else's — the card-layer merge (G-SK-6) and the goal ingest this entry deliberately leaves to **KI-34** —
and none of it blocks a section, because a UI/UX deliverable is a section, its states and its copy, while
a data deliverable is what fills them.

**Erratum 2026-10-01.** Landed by the Phase B2 follow-up dispatch. Two of this entry's Class A
measurements are superseded, and the follow-up's own draft understated the second one, so the correction
is recorded against the measurement rather than against the draft.

**The row count.** Class A records `umamusume` as holding "2 rows and all ten columns are NULL on both".
A read-only PDO query on 2026-10-01 returns **67 rows**, with all ten `aptitude_*` columns non-null on all
67. The new rows sit behind the shared database's write-ahead log, not in its main file: the main file
holds at 1,310,720 bytes and 2026-10-01 02:41:21 while `database/database.sqlite-wal` moved from 168,952
bytes at 03:03:21 to 337,872 at 14:49:02 the same day. That is why a session fingerprinting the main file's
SHA-256 sees no change while a query sees 67 trainees, and it is the WAL shape §6's `cp` row warns about,
read from the other side.

**"Rendered nowhere" is superseded too, and this is the half the follow-up's draft asserted the opposite
of.** Class A cites `grep -rn aptitude resources/views/` returning **zero hits**. On 2026-10-01 that grep
returns three files: `resources/views/components/aptitude-grid.blade.php`, which renders all ten letters;
`resources/views/catalog/partials/form-detail.blade.php:56`, which invokes `<x-aptitude-grid
:umamusume="$trainee" />`; and `resources/views/components/form-tabs.blade.php`. `git log --diff-filter=A`
dates the grid to `555b0cb`, 2026-09-30 03:44, the same commit that landed the profile block and the
costume form tabs, and that is the morning after this entry was filed on 2026-09-29. Class A was accurate
when written. The entry's own Owner paragraph already planned for it, calling the grid "a view change
against columns that already exist", so the grid is the entry's predicted fix arriving, not a contradiction
of its finding.

**What still holds, and what does not.** Class A's `grep` half no longer holds. The heading's "ten parsed
columns rendered nowhere" no longer holds. Class B's costume-forms half no longer holds, since `555b0cb`
shipped form tabs. What stands: Class B's skills half, because `resources/views/catalog/show.blade.php:225-229`
still tells Trainers the card skill arrays "are not stored" while `dd90330` (2026-09-30 16:09) stores two of
the four, and the page still carries no skills section; Class B's goal-races half; and Class C's first
defect, because `catalog/show.blade.php:158` and `:162` still print `Unknown` for the two debut dates.

**Status.** OPEN, and not re-statused here. Class A is superseded in full, Class B is reduced to two of its
three cases, Class C stands. Narrowing the heading is the owner's pen, and this entry deliberately does not
close, because the false Trainer-facing sentence the heading's second half describes is still on master.

**Commands that re-test every claim above.** `php -r` with a PDO read of
`select count(*) from umamusume` and one `where aptitude_<c> is not null` per column;
`grep -rn aptitude resources/views/`; `git log --diff-filter=A --format='%h %ad %s' --date=short --
resources/views/components/aptitude-grid.blade.php`; `stat -c '%s %y' database/database.sqlite
database/database.sqlite-wal`; `grep -n 'Unknown' resources/views/catalog/show.blade.php`;
`git show HEAD:resources/views/catalog/show.blade.php | grep -n 'Skill lists are not shown'`.

## KI-36 The run screen's skills editor wraps three controls in one label, so two of them have no accessible name — FILED 2026-09-29 (skill selector design pass, as F-12), CLOSED 2026-09-30 (with KI-33's repeater)

**Symptom.** `resources/views/runs/show.blade.php:445-461` puts a single `<label>` around three controls:
the skill `<select>` (`:447`), the acquisition-status `<select>` (`:455`) and the turn
`<input type="number">` (`:460`). An implicit label association binds to the **first** labelable
descendant, so only the skill picker is named. A screen reader announcing the row hears one named control,
one **unnamed** combobox whose only content is the three option words (`Suggested` / `Acquired` /
`Skipped`), and a number field whose visible name is `placeholder="Turn"` — which disappears the moment
the field holds a value, so the control that is *filled* is the one that has *lost* its label. This is
F-12 in `docs/frontend-review/2026-09-28/README.md:302-310`, filed against this same block, and it has
been carried unfixed through every pass that has touched the file since. The review noted the contrast
itself: "the create form labels every field, so the gap is local to this block."

**Why it survived.** Every check that runs here looks at the rule rather than the name. G-13's
rendered-text sweep finds `undefined` and `NaN`, not an absent accessible name. A server-render assertion
sees three controls with correct `name=` attributes and passes, because `name` is the form key, not the
label. And the row *has* a visible "Skill" caption, which is exactly what makes the other two read as
labelled to anyone skimming the HTML.

**Fix direction.** One `<label>` per control, or `label` + `id` pairs; keep the visual layout
(`flex flex-wrap items-center gap-2`) unchanged, since appearance was never the defect. The status select
needs a name a Trainer reads as its purpose — "Acquisition status" — and the turn input keeps its
placeholder as a hint while gaining a real name, so the two do not trade places. **Same block, same
commit, deliberately:** KI-33's required repeater replaces `skills[0]` with N rows across these same
seventeen lines, and doing the repeater without the labels reproduces this defect once per row instead of
once per form.

**Owner.** Frontend. Small, local, and the whole fix is inside one `<form>` element.

**Closed 2026-09-30 by `4902f1d`, together with KI-33's repeater as this entry asked.** One
`<label for>` per control across every row: `Skill`, `Acquisition status`, `Turn acquired`, with the
status select named in the words the entry proposed and the turn input keeping its placeholder as a
hint. `tests/Feature/RunSkillRowLabelsTest.php` reads the rendered document through `DOMDocument` and
walks every control in the form, asserting each has an `id`, that exactly one `<label for>` names it,
and that the three names are distinct — a `for` pointing at nothing, or three labels all reading
"Skill", fails there. The substring route was refused deliberately: `assertSee` on a label word passes
on a `for` that resolves to no control.

One deviation from the fix direction, stated rather than left to be noticed: the entry asked to keep
`flex flex-wrap items-center gap-2`. Per-control labels put the caption above each field, which is
what `DESIGN.md` §6.14 specifies for a form field anyway ("label above at `label`, `ink`"), so the row
is now `items-end` with three stacked label/control pairs. Appearance was never the defect; the entry's
point was that the fix must not be *carried* by an appearance change, and it is not.

## KI-37 The run screen's form controls measure 31/30/40px against DESIGN.md §6.14's 44 — FILED 2026-09-29 (skill selector design pass), OPEN

**Symptom, measured.** The run screen's skills editor was read in a browser at a 390px viewport: the
`<select>` at `resources/views/runs/show.blade.php:447` computes **31.0px** tall, the
`<input type="number">` at `:460` **30.0px**, and `button[type=submit]` ("Save skill status", `:462`)
**40.0px**. The specification says **height 44** for a form field
(`docs/design-research/DESIGN.md` §6.14, `:849`). The same reading across the page found 12 selects at
31px, 20 number inputs at 30px and 11 submit buttons at 40px — the run screen's default, not one control.

**Why this is a separate entry and not a widening of KI-29.** KI-29's heading names its own surface:
*"`/umamusume`'s form controls measure 30/31/32px against DESIGN.md §6.14's 44"* (the `KI-29` heading).
Folding the run screen behind that number would leave a reader of the catalog-index entry waiting for a
fix that was never made there, and would let one surface close the other by proximity. Two surfaces, two
entries, one shared cause. (KI-29's heading is quoted above; it is cited by number rather than by line,
because a line citation in this file rotted within one commit of being written — adding this very entry
shifted it thirteen lines.)

**The precedent that shows it is cheap.** `5ff7aca` ("size Screen D's form controls to DESIGN.md 6.14's
44, and the checkbox to the AA floor") moved the same 30/31/16px readings to 44/44/24 with no token
change, no layout change and no new utility. So §6.14's 44 is implementable against the existing token
set and this is copy-forward, not design work. The controls here carry `px-2 py-1` and no height — the
shape Screen D had before that commit.

**What the fix has to say, not only do.** Raising a row from 30px to 44px grows the guided-turn block, the
skills editor and the race form, and `docs/design-research/CONSTRAINTS.md:171` refuses mobile compromise
in exchange for desktop density — so the change belongs with the density question rather than as a quiet
CSS edit. It does **not** belong with dropping columns or shrinking the stat band. **And it is not a
floor question at all:** the accepted replan addendum (`docs/design-research/replan-mobile-first.md` §1,
accepted 2026-09-29 and not yet in D-40) names 768px as the supported minimum and refuses to drop a
column below it, whereas a control below its own specified height fails at 1280px exactly as it fails at
390px. Saying so keeps a reader from folding "make it work at phone width" into "make the control meet §6.14
at every width", and keeps this fix from being parked behind the replan.

**Not asserted.** No target-size *standard* is claimed here. The repository's accessibility mandate is
**WCAG 2.1 AA** (D-10), and 2.1 has no minimum-target-size criterion; 24×24 is WCAG 2.2 SC 2.5.8 and is
not an obligation in this project. This entry is measured against **§6.14**, a design-system rule the
repository wrote for itself and Screen D already honours. Adopting 2.5.8 as a gate would be a separate
ruling with a number, a scope and an instrument attached.

**Owner.** Frontend with the design-system owner: the spec is the authority, and the run screen is the
second surface to break it after the catalog index.

**Measurement owed, recorded 2026-10-01.** The `h-11` fix landed in `c17e63b` and is asserted at the DOM
level: `RunSkillRowLabelsTest.php` checks the class on every control in the skills form, counts the controls
it checked so the loop cannot pass over an empty set, and asserts `step="1"` on the turn input. A browser
measurement of `getBoundingClientRect().height` at 1280x800 against a scratch database was **not** taken, and
the reason has two parts, because naming only the first would repeat the error this register's own
second-order erratum rows warn about, where a correction gave a true half of a mechanism and got believed
anyway.

1. `browser-testing-with-devtools` is installed but not registered in the session that landed the fix. It
   sits at `~/.qoder/skills/browser-testing-with-devtools`, a symlink to `~/.agents/skills/` of the same
   name, and the Skill tool returns `Skill "browser-testing-with-devtools" not found`. So this is an
   environment gap, not an absent skill, and a future dispatcher should not treat the name as unavailable
   in general.
2. The tool that was available is the reason not to blame the skill alone: the Playwright MCP server was
   connected in that session, so a measurement was reachable. It was not taken because measuring needs a
   scratch database, a built asset bundle and a running server, and the dispatch authorised the class
   assertion as the fallback. The gap is a scoping decision, not a missing instrument.

**What the measurement must confirm, and why 44 is expected rather than assumed.** `h-11` is `height:
2.75rem`, which is 44px at a 16px root. `resources/css/app.css:1` is `@import 'tailwindcss'`, so preflight's
`box-sizing: border-box` applies and the 1px `--color-rule` border sits inside the 44 rather than adding to
it; no root `font-size` override is present in that file. That is the mechanism, not the measurement. The
number the register should eventually carry is four read heights, and it should be read against a scratch
database with a run-unique name, at 1280x800 and 390x844, the way the skills-section review's section E read
the 31/31/30/32 set this entry replaced.

**Rides with.** The next slice that stands up a browser scratch database. The class assertion holds until
then, and the entry stays OPEN on the measurement rather than on the fix: `c17e63b` changed the four
controls, and no number in this register yet records the four heights after the change.

---

## KI-23b The character parser read a source key the GameTora export never published, so fetched trainees lost their Japanese name — FILED and RESOLVED 2026-09-29 (catalog roster, Task 2)

Filed as KI-21 at branch base `b387e07`; renumbered to **KI-23b** on merge because trunk already holds KI-23 (the skills pass's parser-citation defect, a different entry) — the `b` suffix is the trail, since commits `d755da3` and `e8ead2d` say KI-21 in their messages and were not rewritten.
**Symptom.** `app/Services/DataPipeline/Parsers/GametoraCharacterParser.php:92` read
`$card['name_ja']` — that line number is the defective line as it stood before the fix; `d755da3`
replaced it with a comment plus the corrected read, so today's `:93` is the line being described. The
`character-cards` export publishes `name_jp`: over its 268 rows `name_ja`
appears 0 times and `name_jp` 268 — erratum E-12's count over the fetched export at
`research-scratch/data/json/character-cards.json`, a scratch path this repo does not track, so the
figure is cited rather than re-runnable from here. So every trainee `uma:fetch` created stored a null
`name_ja`, and `PRD.md` US-1's acceptance test ("each detail page shows `name`, `name_ja`, release
status, and provenance") went unmet for fetched data while the whole suite stayed green.

**Line numbers re-derived 2026-09-29 by Task 6's extraction `db8603c`.** Both numbers above stay as filed
because each names its own tree: `:92` was the defective read in the branch base `b387e07`, and `:93` the
corrected line as `d755da3` left it. Task 6 moved the debut loop out of `GametoraCharacterParser::parse()`
into the shared `debutForms()` member, so the comment-and-read pair this symptom describes is now at
`:73-74` — `:74` is `'name_ja' => $this->textOrNull($card['name_jp'] ?? null)`. Nothing in the claim moved
with the line: source key `name_jp`, record key `name_ja`, no fallback chain, still pinned by
`tests/Feature/GametoraCharacterParserTest.php:113-128`.

**Cause.** Both guards over that one line were empty. The committed sample
`tests/Fixtures/gametora-character-cards.sample.json` was authored against a guessed key and held
`"name_ja": null` on every row, so the test loading it could only ever agree with the parser. And the
one catalog test that asserts a Japanese name, `tests/Feature/CatalogTest.php:33`, seeds `name_ja`
through the factory rather than fetching it, so it never reached the parser. Neither of them read the
source.

**Fix (this change).** The parser reads `$card['name_jp']` and still emits the record key `name_ja`,
which is the contract `SourceParser`, `app/Actions/PromoteMatchedRecord.php:52,64` and
`app/Services/DataPipeline/PipelineRunner.php:65` all read, and matches the `umamusume.name_ja`
column; only the source-side key moved. The sample now spells the key `name_jp` and carries the
client's real Japanese strings instead of `null`. `GametoraCharacterParserTest` gained a body-level
test that the name arrives from `name_jp`, an assertion of `スペシャルウィーク` on the record loaded
from the fixture, and its `card()` helper now emits `name_jp`, because that helper builds a body in
the export's shape. Deliberately not written: `$card['name_jp'] ?? $card['name_ja'] ?? null`. A
fallback chain accepts a body carrying neither key, which is how this defect stayed invisible: a null
reads as "the source has no Japanese name" instead of "you are reading the wrong key".

**Corrected 2026-09-29 by the review follow-up `e8ead2d`.** That ban was prose-only when this entry was
written, and prose is not a guard. `tests/Feature/GametoraCharacterParserTest.php:113-128` now feeds a
body carrying **only** `name_ja` and asserts the emitted `name_ja` is null, so reintroducing
`$card['name_jp'] ?? $card['name_ja'] ?? null` fails a test instead of passing one. The same commit
corrected the two `GametoraAptitudeTest` bodies to `name_jp` and added the persisted-column assertion at
`:85`, as the Residual paragraph below records.

**Standing lesson.** A fixture that agrees with the code instead of with the source proves nothing.
Its keys and value shapes are a recording of the export, not a mirror of the parser beside it; where
the two agree against the source, the pair has no coverage and still reports green. The same reasoning
closes the second half: a test that seeds the value it claims to show is not evidence either, because
US-1 is about a page the fetch built.

**Residual (withdrawn 2026-09-29 by `e8ead2d`).** This paragraph claimed that
`tests/Feature/GametoraAptitudeTest.php:20,67` still spelled the source key `name_ja` in two inline
bodies, that those tests asserted aptitudes and never the name, and that the rename was left for a later
pass over that file. All three were true at `d755da3` and none survives `e8ead2d`: both lines now spell
`name_jp`, and `tests/Feature/GametoraAptitudeTest.php:85` asserts the persisted column on the row the
pipeline promoted (`->and($umamusume->name_ja)->toBe('スペシャルウィーク')`), which is the proof this entry
was missing: source key read, record emitted, column stored. The history stands as written above;
nothing is left over from it in that file.

**Owner.** Data Engineer. Filed and closed by the same commit (`d755da3`), because the fix and its proof
landed together; the review follow-up `e8ead2d` strengthened that proof (and corrected the Residual
paragraph above) rather than reopening the entry.

---

## KI-24b A fresh clone or worktree has six red tests before anyone touches it, because the skill registry file is gitignored - FILED 2026-09-29 (catalog roster, Task 1), CLOSED 2026-09-30 (option b: the suite skips on an absent registry)

**Symptom.** On a clean `git worktree add` or `git clone` of this repo, `php artisan test --compact` reports **6 failed / 364 passed / 2 skipped** at a commit where every other working tree sees green. All six failures are in `tests/Feature/SkillAutomationTest.php` (the file's seven tests; six of them fail without the registry and the seventh asserts an unknown-skill error, which an empty registry already produces), and the assertion that fails reads `Failed asserting that ... contains 'Route Inspector'`. The six-of-seven split was re-measured on 2026-09-30 rather than carried forward: with the fix reverted and the registry moved aside, the file reports `6 failed, 1 passed (7 assertions)`, the pass being the unknown-skill test, which an empty registry already satisfies. That is the whole reason the heading says six and the skip below says seven — they are two different measurements and must not be rounded into each other.

**Cause.** `app/Services/SkillRegistry.php:23` resolves its path as `base_path('.agents/skills.json')`, and `app/Services/SkillExecutor.php:340` reads `base_path('.agents/config.json')`. `.gitignore:49` ignores `/.agents`, so that directory exists only in a working tree where some tool wrote it. `git worktree add` and `git clone` check out tracked files only, so a fresh tree has no registry and the tests that read it fail for a reason unrelated to the change under test.

**Why it matters beyond one red run.** The failure is indistinguishable from a genuine regression at the exact moment a slice most needs a trustworthy baseline: Step 4 of any plan's setup task is "prove the gates are green before you start," and six red tests there means either stopping for a base that is not actually dirty, or proceeding with no baseline at all. Nothing in the output names the missing file, so the first response is to suspect the code.

**Fix chosen, option (b): the suite skips, with a named reason.** (a) and (c) were rejected: (c) leaves the trap armed for the next worktree, and (a) cannot be honestly done here, because `.agents/` is not project config. Measured on 2026-09-30, it holds `mcp_config.json` (secret-adjacent by category), a vendored Python package under `jev-ultrafast-mcp/` whose 245 `__pycache__` directories are build output, a compiled Windows binary at `skills/impeccable/scripts/bin/windows-x64/impeccable.exe`, and other tools' vendored source and LICENSE files — 4,431 files in all. It is also not the only copy: the same vendored tree is already present at `.github/skills/`, byte-identical for that binary (sha256 `477E544FC8880A5E…` in both), and `git ls-files` returns **0 files for both paths**, so `.agents/` is a second on-disk copy of a tool cache that is itself untracked, not a project asset. Committing any of that is not a registry fixture, so a tracked `skills.json` would have to be a synthetic one, and a synthetic registry makes the assertions below test the fixture rather than the matcher.

`tests/Feature/SkillAutomationTest.php` now carries one `beforeEach` guard that calls `markTestSkipped` with a named reason when `base_path('.agents/skills.json')` is absent, so the seven tests report `skipped` on a fresh tree instead of `failed`. One guard rather than seven calls, so a future eighth test in this file inherits the skip rather than forgetting it. Measured in both states on 2026-09-30, with the file moved aside and restored:

    registry present (developer tree)  ->  7 passed
    registry absent  (fresh clone)      ->  7 skipped, 0 failed

**The class, because the instance is not the part that recurs.** A test whose input lives in a gitignored path passes in the developer's tree and fails in every fresh clone. The detection is one command, and it should be run against any test that reads a `base_path(...)` under an ignored directory:

    git ls-files --error-unmatch .agents/skills.json

Non-zero exit means the input is untracked, and the test proves nothing anywhere — it is not merely unrunnable in CI. `.gitignore:46-55` ignores `/.agents`, `/.claude`, `/.cursor`, `/.grok`, `/CLAUDE.md` and others, so any test reaching into those inherits this shape. This is the same failure as KI-39 (`SkillFactory` deriving `match_key` with a different algorithm than production) one grain up: a test that passes while exercising something other than the thing under test.

**Owner.** Data Engineer with whoever owns `docs/SKILL_AUTOMATION.md`. Found by the catalog-roster plan's Task 1 provisioning step, which now copies `.agents` into its worktree; that copy is a workaround local to one branch and does not close this entry.

---

## KI-38 A card with the source's placeholder title is stored and rendered as a real Global costume - FILED 2026-09-29 (catalog roster, Task 13 browser pass), OPEN

**Symptom.** `character_cards` row `card_id = 103601` (Air Shakur, `char_id 1036`) carries `title = "[unsigned]"` alongside a real `release_en` of `2026-06-18` and `rarity = 3`. The catalog list, the trainee detail page and the run-form selector all print `[unsigned]` as though it were the costume's name, with a date and a star rating beside it. Measured on the live export: over its 268 rows, `title` equals the literal string `unsigned` on exactly one, so this is one card and not a pattern.

**Cause, and why a parser must not "fix" it.** The placeholder is the source's own value, so rewriting it in `GametoraCharacterCardParser` would be the engine editing a stated fact - the rule `ADR-0004` and D-33 both turn on. The real cause is the admission rule: `character_cards` is Global-only, and this card qualifies because it has a `release_en`. Nothing checks that it also has a *name*, so a row can satisfy the table's one stated precondition while carrying no displayable content.

**Why it matters.** D-220 says a widget with no mechanic is absent rather than an empty slot, and has no way to say "this row has no title yet". A Trainer sees a dated, rated costume that does not exist under that name, and the run form offers it as a selection. G-16 governs fixture names being real client strings; this is the stored-data equivalent, and it is currently invisible to every gate because the string is a legitimate bracketed title in form.

**Fix candidates, none chosen here.** (a) Treat a placeholder title as absent for display and search while keeping the row, so the card is reachable from her detail page but not offered by name in the selector. (b) Flag the card `unconfirmed` so the cross-check queue surfaces it, and let a human verdict decide whether the release date is trustworthy at all. (c) Accept it as a dated snapshot of an upstream placeholder and render it with a marker. (a) is the smallest change that stops a placeholder reaching a selection; (b) is the only one that also asks whether the date deserves trust.

**Owner.** Data Engineer, with the Lore Guardian consulted on (c) since it puts a non-client string on a display path. Found by the Task 13 browser pass against `http://127.0.0.1:8099/umamusume`.

---

## KI-39 `SkillFactory` derives `match_key` with a different algorithm than production, so search tests prove nothing - FILED 2026-09-29 (catalog roster, Task 13), OPEN

**Symptom.** `database/factories/SkillFactory.php:24` computes the match key as `mb_strtolower(str_replace('-', '', Str::slug($name)))`. Production computes it with `NameNormalizer::normalize()`, which is NFKD, then strip combining marks, then strip the five characters in `NameNormalizer::FOLDED_CHARACTERS`. `Str::slug` transliterates punctuation to nothing; the normalizer keeps it. Measured over 200 sampled `name_is_client` skills, **119 produce a different key**:

| Name | Factory key | Normalizer key |
|---|---|---|
| `Warning Shot!` | `warningshot` | `warningshot!` |
| `Empress's Pride` | `empressspride` | `empress'spride` |
| `1st Place Kiss` + star glyph | `1stplacekiss` | `1stplacekiss` + star glyph |
| `Class Rep + Speed = Bakushin` | `classrepspeedbakushin` | `classrep+speed=bakushin` |

Over all 135 trainee names, 3 differ: `Mr. C.B.`, `K.S.Miracle`, `Curren Bouquetd'or`.

**Cause.** The factory was written as a convenience and reimplemented the normalizer's intent rather than calling it. It is the same class of defect as `KI-23b`, where a factory-seeded `name_ja` meant the parser's wrong source key stayed invisible: the fixture agreed with the code because both were wrong in the same direction.

**Why it matters.** Any search test that seeds a skill through the factory asserts against a key production never writes. It passes whether or not `normalize()` is correct, and it fails for the wrong reason the moment `normalize()` changes. The divergence is largest exactly where punctuation is richest, which is the Global skill set.

**Fix candidates, none chosen here.** (a) Have `SkillFactory` call `NameNormalizer` through the container, so one algorithm exists. This changes keys in every test that seeds a skill, so it needs a full-suite run and an honest count of what moved. (b) Delete `match_key` from the factory and let the model or an observer derive it, which closes the "two places compute it" shape permanently. (a) is the smaller diff; (b) is the one that cannot drift back.

**Owner.** Laravel Dev. Measured 2026-09-29 with a tinker script comparing both algorithms over the imported `skills` table and the whole `umamusume` table.

---

## KI-40 `NameNormalizer`'s fold has a Unicode ceiling, and the failure mode reads as "no such trainee" - FILED 2026-09-29 (catalog roster, Task 13), OPEN as a hazard

**Symptom.** `NameNormalizer::normalize()` folds by NFKD, removes combining marks, then removes exactly the five characters in `FOLDED_CHARACTERS`. NFKD does not decompose the Latin ligature and stroked letters, and none of them are in that list, so they survive into the key. Measured pairs:

| Pair | Result |
|---|---|
| `Cafe` / `Cafe` + acute | match |
| `El Condor` / `El Condor` + acute | match |
| `Tokai` / `Tokai` + macron | match |
| `Straights` / `Str` + ae ligature + `ight` | **no match** |
| `Odawara` / `O` + stroke + `dawara` | **no match** |

**Severity today, measured rather than assumed.** The ae ligature appears 375 times in the 268-row `character-cards` export, but only in `name_tw` (144 rows), `title_tw` (81), `title_jp` (45) and `title_ko` (4) - **none of which this tool stores or searches**. `O`-stroke, `D`-stroke, `L`-stroke, thorn, sharp-s and oe-ligature appear **0 times**. No `[Global]` English name currently folds wrong.

**Why it is still filed.** The failure mode is silent and wrong-looking: a name that fails to match presents as "no such trainee" or "no trainee or card found", not as a normalizer that does not cover this letter. The ceiling becomes live the moment a source adds a `[Global]` name carrying one of these letters, or the day this tool starts indexing `name_tw` - and the second is a plausible future scope, since the export has been carrying those fields all along.

**Fix candidates, none chosen here.** (a) Add a transliteration step for the Latin-1 letters NFKD leaves alone, so the fold is defined by a class rather than a list of five. (b) Record the ceiling as a known limit next to `FOLDED_CHARACTERS` and add a test that pins the pairs that do not match, so a future source that trips it is a test failure rather than a support question. (b) costs one test and converts a silent wrong answer into a loud one; (a) is the real fix and needs a decision on which letters are in scope.

**Owner.** Data Engineer. The ceiling is a property of the normalizer, so any fix belongs with the fold's own owner rather than with a caller.

---

## KI-41 The run form's roster is ordered by `name` with no tiebreaker, so the order is total by accident - FILED 2026-09-29 (catalog roster, Task 13), OPEN as a latent hazard

**Symptom.** `app/Http/Controllers/TrainingRunController.php:82` ends the roster query with `->orderBy('name')` and no secondary key. SQLite resolves ties by row order, which can change across a re-import or a vacuum, so two trainees whose names compare equal could swap places between two page loads of the same data.

**Severity today, measured.** 0 duplicate names in `umamusume`, and 135 distinct normalized names across 135 trainees, so the order is total in fact. This is filed as a hazard, not a live bug: the property holds because the data happens to be unique, not because the query guarantees it, and nothing in the test suite would fail if a future import introduced a tie.

**Why it matters for the surface it feeds.** The combobox groups options under trainee headers and its empty state is "10 most recently released cards, newest first", so a Trainer reads the group order as meaningful. An unstable order would make the same query return two different screens, which reads as a bug in the selector rather than in the query.

**Fix candidates, none chosen here.** (a) Add `->orderBy('id')` as a tiebreaker, which makes the order total by construction and costs one clause. (b) Add a unique index on the normalized name, which is an Architect decision and would also constrain manual rows. (a) is the smaller change and does not constrain what a Trainer may write.

**Owner.** Architect, or Laravel Dev under (a). Found by the Task 13 browser pass, where the wrap behaviour was verified from both ends of the list.

---

## KI-42 Nothing runs the gates on push, so a defect that only a fresh checkout can see is found by whoever remembers to look - FILED 2026-09-30 (catalog roster, review of the two near-misses below), OPEN, backlog only

**Symptom.** `.github/` exists and holds agents, hooks, prompts and skills, but there is no `.github/workflows/` directory: `Test-Path .github\workflows` is `False`, so no job runs on any push or pull request. The full gate is a local command, `composer test`, which runs `npm run typecheck` and then Pest (`composer.json`). It is therefore run only when a person in one working tree chooses to run it.

**Why this is filed rather than left as a preference.** Two defects from this session would have been caught on the commit that introduced them, had anything run the gate against a clean checkout. They were caught anyway, which is the actual problem, because both were caught by accident rather than by a control:

1. **An untracked gate input** - `.agents/skills.json` is gitignored, so `tests/Feature/SkillAutomationTest.php` was green in every developer tree and red in every fresh clone. Filed as KI-24b, which is now closed by a named skip rather than by a fixture, so this instance is spent; the shape recurs for any test reading a `base_path(...)` under an ignored directory.
2. **A commit measured on the wrong tree** - `d192fa1` added `resources/js/types/global.d.ts` on the belief that a tracked declaration was missing. It was not; `resources/js/bootstrap.ts:3-7` already declares `Window.axios`. The local gate caught it in the same session and `85b37a4` reverted it. To be exact about what this instance is: it is **not** a live defect and **not** something CI missed, because the file was never merged. It is a worked example of the same wrong-tree error that also produced the branch-freshness miscount and the KI-39 factory drift, all of which passed a check that had not fingerprinted what it was checking.

**The common cause is not the missing workflow.** It is that every gate in this repo runs inside one developer's working tree, on demand, where ignored files exist and only one commit is checked out. A clean-checkout run on push is what makes the ignored-input and wrong-tree classes visible without a person noticing them, and the two instances above were caught by a person noticing.

**Fix candidate, deliberately not chosen here, and deliberately not built on this branch.** Add a GitHub Actions workflow running `composer install --no-interaction`, `npm ci`, `npm run build`, and `composer test` on every push and pull request. This is an owner decision, not an implementation detail, for three reasons that are measurable today: the project is local-only by intent (`AGENTS.md`, Phase 1 non-goals in `PRD.md` §6), so enabling a hosted runner may be out of scope entirely; `.agents/` and `.github/skills/` are untracked in this tree, so a hosted runner has no skill registry and any future test that reads one will need KI-24b's skip rather than the file; and `node_modules` and `vendor` are absent from a fresh clone, so the first CI run is also a first-install run. Nothing about the scope change is decided here. Recorded as backlog so the decision is not re-litigated per slice.

**Owner.** Human owner, with the Architect, because the answer may be "no CI in Phase 1" rather than a workflow. Raised by the review of the two instances above; both are already resolved in the tree, so this entry carries no failing gate today.

## KI-43 The run detail page is now 80-89% support-deck markup, because six selects each repeat all 252 Global cards - FILED 2026-10-01 (Slice 2 browser pass), OPEN

**Symptom.** `resources/views/components/deck-panel.blade.php` renders one `<select>` per slot over the
whole Global catalogue. Six slots times the shipped catalogue is 1,512 `<option>` elements on every run
screen, equipped or not. Measured from the fetched pages against the imported 559-card catalogue
(`.scratch-uma/measure-deck-weight.php`, replayable):

| Run state | Page | Deck block | Block share |
|---|---|---|---|
| six cards equipped | 360,492 B | 296,537 B | 82.3% |
| two cards equipped | 363,341 B | 293,021 B | 80.6% |
| **nothing equipped** | 329,355 B | 291,547 B | **88.5%** |

The options alone are 207,504 B, 57.6% of the page. Longest single label is 71 characters.

**The last row is the finding.** A run with an empty deck still carries 291 KB of picker, because an
unfilled `<select>` holds the same 252 options as a filled one. The cost is not the Trainer's data; it is
the choice list, paid on every run page whether or not anyone is choosing. Before this slice the same
pages rendered 51-99 KB with an eight-card catalogue, so the panel did not add a section, it became the
section.

**Why filed rather than fixed in the slice.** No gate covers it. C-6 budgets catalog-index latency at
~1,000 Umamusume (NFR-3), not rendered page weight, and the suite is green at 972. The fix is a design
change, not a bug fix: it alters how a Trainer finds one card among 252, which is `DESIGN.md` territory and
touches the combobox precedent already set for the trainee picker.

**Fix candidates, not chosen here.**

1. **Search-first picker**, the shape `resources/js/trainee-combobox.ts` already implements for trainees:
   a text input over a JSON payload, with six selects reserved as the no-JS fallback and `disabled` until
   the script claims them. Reuses an existing component pattern rather than inventing one; the same
   disabled-input reasoning at `runs/create.blade.php:9-23` applies unchanged.
2. **Filtered shortlist.** Offer the five stat types matching the run's deck need plus the Pal slot, so the
   list is ~40 cards rather than 252, with an explicit "show every card" disclosure for the rare case.
   Cheaper to build, but it decides for the Trainer which cards are worth being able to find.
3. Leave it. On a local-only tool with one Trainer and no network round-trip, 360 KB is parsed rather than
   downloaded. Defensible, and the reason this is filed rather than escalated.

**Owner.** Human owner with the designer, because option 2 makes a product decision about what a Trainer
should be able to reach, and option 1 decides whether the deck is the second surface to adopt the
combobox pattern or the test case for generalising it.

## KI-44 A copied SQLite file is not the database: WAL is declared in config, and the main file can hold nothing at all - FILED 2026-10-01 (Slice 2 scratch-database incident, confirmed by a probe), OPEN

**Rule.** Do not `cp` a SQLite database in this repository. An open WAL-mode database is **three files** —
`x.sqlite`, `x.sqlite-wal`, `x.sqlite-shm` — and the main file is not self-contained. Move or delete all
three together, or do not move any of them. To obtain a single-file copy, checkpoint first:
`VACUUM INTO 'target'`, or `PRAGMA wal_checkpoint(TRUNCATE)` and then copy. To obtain a working test
database, run `php artisan migrate` against a new path.

**This is not an accident of someone's environment.** `config/database.php:42` declares
`'journal_mode' => env('DB_JOURNAL_MODE', 'wal')`, and line 41 declares `busy_timeout` 10000. Both arrived
in the initial skeleton commit `fda6ff0` and are still there. A claim circulating in a peer report that
"no setting in this repository enables WAL mode" is wrong at the config layer; the observation that WAL is
in effect was right, and the reason is written into the connection array.

**Measured, on a throwaway file (`.scratch-uma/wal-probe.php`, replayable):**

```
after migrate + one Eloquent insert:
  main = 4,096 B   -wal = 1,751,032 B   -shm = 32,768 B

copy of the main file alone:
  -> SQLSTATE[HY000]: General error: 1 no such table: support_cards
     (the live database at that moment held 1 row)

after PRAGMA wal_checkpoint(TRUNCATE), the same copy reports 1 row.
```

99.8% of the bytes were in the WAL, and the copied main file did not even **declare the table**. The copy
is not a stale snapshot of the database; it is a different, nearly empty database that opens without
complaint.

**Two distinct symptoms, same cause.** Slice 2 hit this for real: `cp database/database.sqlite
.scratch-uma/test.db` produced a file every later command rejected as `database disk image is malformed`.
The probe above produces the quieter outcome, `no such table`. Which one you get depends on where the WAL
was in its lifecycle when the copy was taken. **The malformed case at least announces itself; the
missing-table case, and the empty-but-valid case between them, are silent** — they read as a database that
legitimately has nothing in it, which is how a fixture ends up reporting zero rows for a population that
exists.

**Read-only access on a WAL database is a separate trap and reports as corruption.** Opening the main file
without write permission to the directory yields `SQLSTATE[HY000]: disk I/O error`. That is a permissions
condition, not damage. Do not respond to it by deleting or rebuilding the file.

**Reference implementation already in the repo.** `app/Console/Commands/UmaBackup.php:32` runs
`PRAGMA wal_checkpoint(TRUNCATE);` before `copy()` at line 43, which is exactly the right sequence, and its
own docblock at lines 11-13 says so. Anything else in this repo that copies a database should call that
checkpoint or use `VACUUM INTO`; a bare `copy()` of the live file is the bug.

**Related decoy.** `database/database.sqlite.bak` is 4,096 bytes — one empty page — and is a WAL-mode
main-file copy, not a backup. Its size equals the "main" figure above. Do not treat it as a restore point.

**Owner.** Every agent and human working in this repo; the rule is operational rather than design. Filed so
that the next session does not re-derive it from a corrupted scratch file.

## KI-45 `race_catalog_slots` has no offline population path, so any feature keyed on a race's distance or surface is unbuildable today - FILED 2026-10-01 (Slice 3 pre-flight data check), OPEN, blocking Slice 3

**Symptom.** `race_catalog_slots` is the only table in the schema carrying `distance`, `distance_band`,
`surface` and `grade_code` for a race, and it holds **0 rows**. `scenario_slots`, the table a run's
`race_entries` actually reference, has **no distance or surface column at all**. There is therefore no path
from "the next race on this run's calendar" to "what this race asks of her", which is the input any
readiness or aptitude comparison needs.

**Why it stays empty.** It is fetch-only, and nothing seeds it:

```
grep -rln "race_catalog_slots" database/seeders/ app/Console/Commands/     # → no files
```

`uma:fetch` with `GametoraRaceCatalogParser` is the only writer, and the source has no `seed_file` key, so
`migrate --seed` cannot reproduce it offline. A peer session's fresh `migrate:fresh --seed` produced 296
`scenario_slots` and 0 `race_catalog_slots` for exactly that reason. The upstream bodies are already on disk
(`research-scratch/data/json/races.json` 125,292 B, `race_instances.json` 213,813 B,
`racetracks.json` 107,466 B), so the data exists and only the offline path to it is missing.

**Second, independent gap on the same table.** `scenario_slots.tier` is NULL on **141 of 296** rows, and the
155 that have a tier carry only G1 (34), G2 (42), G3 (76) and OP (3) — no `Pre-OP`, no `EX`. That is
R75's strict nullification working as designed (`docs/design-research/verification/slice-15-2026-09-29.md`
§2: an uncorroborated grade is worse than an absent one), and it means config and table do not share a grade
vocabulary: `config('scenarios.php')` `grade_point_by_grade` carries `Pre-OP`, and D-153 records `EX` as a
sixth label. Any code that maps a slot's tier to a grade weight will silently miss 48% of rows.

**What is NOT missing, so nobody re-checks it.** `scenario_slots.fans_needed` is non-null on 296 of 296, so
the fan gate is computable today. All 67 `umamusume` rows carry all ten aptitude letters, with **0 NULL cells
across those ten columns**, and `x-aptitude-grid` renders them as the export's A–G. The trainee side of any
such comparison is complete; the requirement side is not.

**A near-miss recorded, because the column reads as populated and is not.** `is_maiden_gated` is non-null on
all 296 rows, which looks like a working gate — but **0 of the 296 are set to true**, so a maiden restriction
read from that column evaluates correctly and never fires. Column presence is not signal presence; the count
that matters is the non-zero one. Anything that builds a gate on it needs the per-row flag to arrive from the
source rather than from a default.

**Consequence.** Slice 3 (qualitative next-race readiness) is held. `docs/adr/0016-next-race-readiness-open-question.md`
records the measurement, the three candidate shapes and the fact that none was chosen; `PRD.md` §6.11 stands
unamended, so "no prediction engine" is currently a requirement this repository **satisfies**. A future
slice that reads the Slice 3 brief as live authorization would have to invent the requirements to finish it,
which fails the provenance floor and Planner Rule 5.

**Re-verify in three commands** (read-only, against any populated database):

```
SELECT COUNT(*) FROM race_catalog_slots;                              -- 0 today
SELECT COUNT(*) FROM scenario_slots WHERE tier IS NULL;               -- 141 of 296
SELECT COUNT(*) FROM umamusume WHERE aptitude_turf IS NULL;           -- 0; aptitudes are fine
```

**Fix candidates.** Give the race-catalog source a committed `seed_file` and a seeder, which is the narrow
change and unlocks the whole feature; or add `distance_band` and `surface` to `scenario_slots` at the
`ScenarioSlotSeeder` layer, which duplicates a fact `race_catalog_slots` already owns and needs a reason; or
scope the surface to gates only, which needs no data at all but is a smaller product. Not chosen here.

**Owner.** Human owner, with the Data Engineer, because the first candidate is a source-and-seed decision and
touches `config/uma.php`, which currently carries a concurrent session's uncommitted `seed_file` work.

## KI-46 The import's per-stat error names the internal array path (`turns.0.speed`) to the Trainer - FILED 2026-10-01 (Slice 4 browser pass), OPEN

**Observed, rendered in a real browser** (`.scratch-uma/slice4.sqlite` served on `127.0.0.1:8123`, one row of
`speed = 9999` under URA Finale):

> A speed value is outside what this scenario allows: The turns.0.speed field must be between 0 and 1400.

The ceiling and the column are right. `turns.0.speed` is the Form Request's internal nested key, and it is the
only part of the sentence that tells the Trainer **which row** broke.

**Why it is still there rather than fixed.** Laravel's `attributes()` maps a wildcard key to one string, so
`turns.*.speed => 'speed'` renames every row's error to the same word and the row identification is lost.
Keeping Laravel's default path is what keeps the row number visible. The fix is a message that carries the
row explicitly, which is a `withValidator()` pass over the parsed rows rather than a rename, and it is copy
work on a message that is already truthful and already actionable — the CSV stays in the textarea, so the
Trainer can find row 0 and correct it.

**Consequence.** None for correctness. It is a voice violation: `AGENTS.md` gates copy, and an internal
identifier in a Trainer-facing sentence is the kind of string the Lore Guardian would flag on sight. Recorded
rather than fixed inside Slice 4 because the fix is not a one-line rename and the message does not mislead.

**Owner.** Docs Writer / Lore Guardian with the Laravel Dev, on the next pass that touches import copy.

## KI-47 A run with no scenario shows a ceiling 200 higher than the one its own form enforces — the disagreement ADR-0015 exists to prevent, alive on the no-scenario branch - FILED 2026-10-01 (Slice 4 browser pass), **RULED 2026-10-01 by the human owner: the band is wrong. CLOSED 2026-10-01 by `04658f2`.**

**Symptom, seen in a real browser.** `http://127.0.0.1:8123/training-runs/4` — a run whose `scenario` column
is `NULL`, holding two logged turns — renders every stat band as **`/ 1,400`**. The turn form on that same
page refuses any stat above **`1,200`**. A Trainer who trusts the band and types 1,300 is rejected by the
server, which is precisely the failure ADR-0015's own text names: *"the disagreement was the defect ADR-0002
recorded and ADR-0015 closes, where a Trainer could see a reachable cap the form rejected."*

**Measured, replayable** (`.scratch-uma/cap-probe.php`, run against `.scratch-uma/slice4.sqlite`):

```
run 4 (scenario NULL)
  stored scenario=NULL  hasScenario=false  scenarioKey()=ura_finale
  forRun()  Speed=1200, Stamina=1200, Power=1200, Guts=1200, Wit=1200
  band()    Speed=1400, Stamina=1400, Power=1400, Guts=1400, Wit=1400
  disagree: YES
run 3 (ura_finale)
  forRun()  Speed=1400 …   band()  Speed=1400 …   disagree: NO
config: base_cap=1200 hard_cap=2000 baseline=ura_finale ura bonus Speed=200
```

**Root cause, named.** `ADR-0015` moved both readers onto `App\Services\ScenarioCaps`, and that half worked:
there is one owner of the arithmetic. It did not make the two readers **call it with the same argument**.

- The form and the validator call `ScenarioCaps::forRun($run)`, which returns the base cap with **no bonus**
  when `! $run->hasScenario()`. That refusal is deliberate and documented in the same file: *"a bonus belongs
  to a scenario the Trainer chose, and lending URA Finale's +200 to a run that never picked it would accept
  a number the tool has no source for (D-220, D-221)."*
- `resources/views/runs/show.blade.php:310` and `:325` hand the band `$run->scenarioKey()`, and
  `scenarioKey()` resolves `null` to `config('scenarios.baseline')` = `ura_finale`. `x-stat-band` then calls
  `ScenarioCaps::stat('ura_finale', …)` at line 87 and gets **1,400**.

So the run that never chose a scenario is lent URA Finale's bonus by the display path — the exact thing
`forRun()` was written to refuse. `ScenarioCaps` eliminated duplicate arithmetic, not duplicate inputs, and
the split survived inside the single owner.

**Why the import makes it urgent rather than marginal.** A historical run arriving from a paper sheet very
often names no scenario — the sheets recorded the trainee and the turns, not the career's scenario (that is
why the import form offers "Not set (baseline strip)" as its default). Slice 4 therefore produces
`scenario = NULL` runs on its main path, and every one of them lands on the page where the two numbers
disagree. Pre-existing, but newly reached at volume.

**Fix candidate, and why it is not applied here.** `show.blade.php:28` already computes the correct number
into `$statCaps` via `ScenarioCaps::forRun($run)` and does not pass it to the band. Handing the band those
caps instead of a scenario key is the narrow correction, and it makes the page structurally unable to show a
number the form disagrees with. It is **not** applied in Slice 4 because it changes a rendered ceiling on
every no-scenario run page, which is `ADR-0015`'s surface and the Architect's call, not a slice-local
cleanup. Escalated per `AGENTS.md` escalation path 1.

**Open question for the owner, stated both ways.** Either the band is wrong and a no-scenario run should
display 1,200 — which is what `forRun()` and the validator assert; or `scenarioKey()`'s null-to-baseline
resolution is the truth and a no-scenario run *is* URA Finale, in which case `forRun()`'s refusal is wrong
and the validator has been rejecting legal turns. The two positions imply different numbers on the same
page, so one of them has to lose. This file takes neither side. **(Answered — see the ruling below. The
paragraph is kept as the record of what was escalated, not as an open question.)**

**RULING, 2026-10-01, human owner: the band is wrong.** Three independent pieces of evidence, in the
owner's framing:

1. `ScenarioCaps::forRun()` refuses the bonus for a no-scenario run **in writing**, with its reasoning
   attached ("a bonus belongs to a scenario the Trainer chose").
2. **Both** write-side validators call `forRun()`. The turn form rejects 1,201 on a no-scenario run.
3. `x-stat-band`'s own `@props` comment records that a named default was removed *precisely because*
   "silently resolving to the baseline would rate a trainee against the wrong ceiling."

So the band is displaying the thing its own contract forbids. The tie-break the owner names is a
citation-count one, and it is decisive rather than stylistic: **the validator's contract is cited in three
places and the band's in one comment, so the band loses** — which is also why this is characterised as a
fix, not a preference. Either the band lies about the validator or the validator lies about the band, and
only one of them is corroborated.

**Fix, as ruled.** Pass the already-computed `$statCaps` from `resources/views/runs/show.blade.php:28` into
the band instead of a scenario key. One line at the call site.

**Who owes it, and when.** Not Slice 4, and **not Slice 4's successor either** — this is `ADR-0015`'s
surface, and it is the exact class of follow-up that ADR-0015's own register chapter was written to enable.
It goes to the Architect, and the fix lands in **whichever slice next touches `runs/show.blade.php` on the
no-scenario branch**. Explicitly **not** to be parked in a review queue: the source of the disagreement is
now documented in two places, so a reader of the band should not have to rediscover it.

**Owner.** Architect (`ADR-0015`, `ScenarioCaps`) owns the ruling; Laravel Dev applies the one-line view
change on the next slice that touches that branch. Filed by the Slice 4 browser pass.

**CLOSED 2026-10-01 by `04658f2`.** The band takes a required `caps` map as its only ceiling source and the
`scenario` prop is now the truth of the label plus the footer's bonus breakdown; `showData()` passes
`ScenarioCaps::forRun($run)` and the run's real scenario or null. Verified in a browser on a scratch database:
a no-scenario run with `speed = 1250` renders `/ 1,200` five times and `/ 1,400` zero times, and the same
value under `ura_finale` still renders `/ 1,400` with its `+200` breakdown intact — the fix removes an invented
bonus, not a chosen one.

**Two corrections to this entry's own forward-looking parts, recorded rather than quietly edited:**

1. **"One line at the call site" — both in the ruling and in the fix candidate above — was wrong.** The
   ruling was sound (stop the band deriving its own number) but the edit is a props-contract change, because
   passing `caps` alone would have left the footer still reading `cap_bonus` off a scenario key: a no-scenario
   page would then print a `+ Speed +200` breakdown beside bars capped at 1200. That is the same coupling that
   caused KI-47, relocated one line down the page. The footer needed a no-bonus branch, and the component an
   up-front guard, so the wrong state became unrepresentable rather than merely unlikely at this call site.
2. **A third shape of the same bug surfaced while fixing it.** Blade in this Laravel version leaves a
   defaultless `@props` entry *undefined* when the caller omits it. The first cut used `'caps',`, and the
   guard never ran — reading `$caps` raised `Undefined variable` and the page failed with a PHP notice instead
   of the message the component was written to emit. `caps` therefore takes a `null` default, which is not the
   D-240 smell the `scenario` comment warns about (that was a scenario *name* living in a view), so the guard
   fires as intended. Anyone adding a required prop to a component should give it a null default *and* check
   it, because the silent version of this failure is a guard that never executes.

**Also checked, no finding:** `x-guided-step` reads the scenario for step ordering and panel flags and derives
no ceiling, and already carries a separate `declared` flag for the absence case — so it does not have this
defect and was not changed. `x-resource-strip` keeps `scenarioKey()` because it composes a descriptor, not a
number. `ScenarioCaps` itself is unchanged, as the ruling required.

**Evidence trail.** `ADR-0015` carries the dated erratum for the consequence its own list asserted but did not
achieve. `docs/design-research/verification/slice-1-stat-ceilings-2026-09-30.md` §3 had already named this
defect and deferred it as the owner's call; that deferral is discharged, and the five tests added in `04658f2`
are what stops it reopening.



## KI-48 The architecture docs still state the flat `0..1200` stat bound that ADR-0015 superseded — filed, not fixed - FILED 2026-10-01 (owner ruling after the Slice 4 report), OPEN

**Gap.** Two governance documents assert a validation bound the code no longer applies:

- `ARCHITECTURE-ESSENTIALS.md:36` — `turn_entries: … stats validated 0..1200, turn >= 1`
- `ARCHITECTURE.md:158` — `-- stats validated 0..1200, turn >= 1 (StoreTurnEntryRequest)`

`ADR-0015` replaced that flat bound with the run's own per-stat scenario ceiling, and `PRD.md` already carries
the supersession in two places — `:28` ("**Superseded in part 2026-09-30 by `ADR-0015`: the flat 0..1200 test
becomes a per-stat, per-scenario ceiling**") and `:128` ("`ADR-0015`, which supersedes the flat 0..1200
recorded here"). The PRD was corrected forward; the architecture docs were not.

**Why it matters more than a stale number.** `0..1200` is the exact value a **no-scenario** run is held to, so
the sentence reads as correct to anyone looking at one, and is wrong for every run that names a scenario —
Unity Cup Wit goes to 1,800, Trackblazer Stamina to 1,900. A reader who takes the digest at face value
builds a form that rejects legal gameplay data, which is `CONSTRAINTS.md` D-31's original complaint
(*"A real run cannot be recorded … this is a product blocker, not a display question"*) resurrected by
documentation rather than by code. It is also the sibling of **KI-47** on the read side: KI-47 is a rendered
number that disagrees with the validator, this is a documented number that disagrees with it.

**Class.** This is the same drift shape as the Slice 2 twelve-column issue, which took three passes to close:
one document corrected forward while a peer document kept the old value, and each pass finding one more
carrier. `DocSchemaDriftTest` reads four governance docs for guarded wording but does not compare this kind of
prose bound against the rules the request classes actually apply, so nothing fails when the digest lags.

**Filed rather than fixed here, on the owner's instruction.** The correction belongs to whoever owns
`ARCHITECTURE-ESSENTIALS.md` and `ARCHITECTURE.md`, in the same change that names the ADR-0015 supersession,
so the fix and its citation land together. Not applied in a Slice 4 follow-up.

**Owner.** Docs Writer, as owner of digest currency. Suggested landing: one edit covering **both** lines above,
each citing `ADR-0015`, since fixing only the ESSENTIALS line leaves `ARCHITECTURE.md:158` stating the same
wrong bound.

## KI-49 Three source bodies are untracked, so three pipelines cannot be re-run from a fresh clone. Filed, not fixed.

**Gap.** `database/seeders/data/skills.609afe88.json`, `database/seeders/data/characters.c6676539.json` and `database/seeders/data/gametora-characters.e9e9ee6d.json` exist on disk, are in no commit, and are not ignored. Verified 2026-10-01, each with its own command:

- `git ls-files --error-unmatch` on all three returns no, so none is tracked.
- `git check-ignore -q` on all three returns no, so this is not a deliberate ignore. The files were simply never added.
- Sizes on disk: 4,399,937, 2,068,129 and 2,791,919 bytes.

**Why it matters now, and not only in principle.** `config/uma.php` names all three bodies through `seed_file` keys (`config/uma.php:75`, `:113`, `:180` in the working copy). `git show HEAD:config/uma.php` greps **0** matches for `seed_file`, so the keys exist only inside an uncommitted peer diff. Neither the pointers nor the bodies are on any ref. Corrected on the same day, because that sentence overreached in its own filing: the `seed_file` keys are indeed absent from every ref, and the bodies are absent from every ref, but two of the three file names do appear at `HEAD`, as `url` values in `config/uma.php` (`skills.609afe88` once, `characters.c6676539` once, `gametora-characters.e9e9ee6d` not at all). So a fresh clone can re-fetch them over the network; what it cannot do is seed offline, and that is the real gap. The rest of this entry is unaffected. Three consumers depend on them: the skills import, which `app/Enums/ReleaseStatus.php` and `ADR-0011` govern; the characters import and roster crosscheck; and the Batch 2 extraction at `docs/design-research/skill-facts-2026-10-01.md`, whose 623 rows cannot be regenerated from a fresh clone because the file it read is not in history. The extraction document says so in its own provenance warning, but a warning in a derived document does not make the source durable.

**Adjacent findings, deliberately not folded in.** KI-24b is a gitignored skill registry, and P-6 with N-3 in `docs/design-research/slice-6-currency-2026-10-01.md` concern one citation pointing at `factors.json`. Those are single pointers into ignored scratch. This is three data bodies with no rule and no commit, so the failure mode differs: a pipeline that is green in one working tree and unrunnable in every other checkout.

**Fix options, owner's call.** Either (a) track the three bodies, about 9 MB together, as the committed source truth for these pipelines, or (b) declare them ephemeral and retarget each pipeline to a source that a ref can resolve, recording manifest hash and fetch date so provenance survives the move. What must not stand is the current middle state, where `seed_file` names bodies no ref carries.

**Not acted on here.** This dispatch's fence forbids adding the files, and a decision to commit or discard 9 MB of source data is the owner's, not an agent's. Filed only.

**Owner.** Data Engineer for the pipeline retarget, with the owner deciding between (a) and (b). Docs Writer owns the correction to `ADR-0011`'s provenance line if option (a) is taken, since that ADR currently cites the manifest hash as though it were retrievable.

## KI-50 `Blueprint::check()` is a silent no-op on SQLite, and no test would notice a second one. Filed, not fixed.

**Gap.** `database/migrations/2026_09_30_142618_create_support_cards_and_support_effects_tables.php` writes four column-level `->check(...)` calls, at `:27` on `support_effects.calc`, `:42` on `support_cards.rarity`, `:43` on `support_cards.type` and `:66` on `deck_slots.slot_position`. On this project's stack, Laravel 13's SQLite grammar does not render that modifier at all, so the DDL that migration produced carries no `CHECK` token on any of the three tables. The table-level spelling is not an escape either: `Blueprint` has no `check()` method, so writing one raises `BadMethodCallException`. A constraint that cannot fail is worse than an absent constraint, because the source reads as a guarantee and the database enforces nothing.

**The one instance is repaired. The class is not.** `database/migrations/2026_09_30_151945_correct_support_card_schema_and_constraints.php` rebuilds all three tables by create-copy-swap in raw SQL, and the constraints are real in the live schema. Read `sqlite_master` on the shared database: `deck_slots` carries `check ("slot_position" between 1 and 6)`, `support_cards` carries two, and `support_effects` carries `check ("calc" in ('mult', 'add'))`. That is the whole of the repair, and it is a repair of the three tables that migration happened to touch.

**Canary, so the zero above is a real zero.** The same `sqlite_master` query returns a `check` token for two tables nobody rebuilt: `scenario_slots` and `race_catalog_slots` both hold `check ("half" in ('Early', 'Late'))`. Those come from `$table->enum('half', ['Early', 'Late'])` at `2026_09_27_153416_create_scenario_slots_table.php:45` and `2026_09_28_180350_create_race_catalog_slots_table.php:49`, which the SQLite grammar does render. So `enum()` produces an enforced constraint, `check()` produces nothing, and the query that finds three repaired tables can also find two working ones. The absence on the `142618` tables is a property of those tables, not of the query.

**Why it matters now.** Two ways. First, nothing in the toolchain fails on a `->check()`. A migration using it runs, reports success, and the next reviewer reads the source and believes the constraint holds, exactly as this repository's own correction docblock had to record at `151945:12-17`. Second, no test reads the emitted DDL for a constraint. `tests/Feature/SupportCardTest.php:50` reads `PRAGMA table_info(support_cards)` to prove a column is absent and `:176` reads `PRAGMA foreign_key_list` to prove a foreign key is absent, which is the right instrument and the right instinct. No test anywhere reads `sqlite_master` or `PRAGMA index_list` to prove a `CHECK` exists, so the four constraints `142618` failed to create were invisible to the suite, and the four `151945` created are unverified by it. A migration that drops a constraint again would also be invisible.

**Fix options, owner's call.** Either (a) add a test that reads `sqlite_master` and asserts a `check` token on every table whose source declares a constrained domain, which turns the silent no-op into a failing test, or (b) retire the spelling. Neither needs a migration: the three tables already hold real constraints, so this is a guard, not a repair. A gate that greps `database/migrations/` for `->check(` and fails is the cheaper half of (a) and catches the case before the migration is written rather than after it ships.

**Not acted on here.** This dispatch's fence covers the run screen's skills section, and adding a schema-drift test is a different surface owned by the Architect and QA. The finding is filed so the hazard is written down before the next support-card or slot migration reaches for `->check()`. Note also that `142618` and `151945` are not modified by this filing and must not be: `151945`'s `down()` deliberately reconstructs the constraint-free shape so a rollback returns the database to the state its parent left.

**Owner.** Architect for the choice between the test and the grep, with QA owning whichever guard is chosen.

**Mechanism, measured on 2026-10-01 rather than quoted from this entry's heading.** The heading says `Blueprint::check()`, and that name is imprecise in two ways, both verified against the installed framework. `vendor/laravel/framework/src/Illuminate/Database/Schema/Blueprint.php` has 2,036 lines and **0** of them contain `check`, and the class defines no `__call`. So there is no `Blueprint::check()` at all, table-level or otherwise. The four calls in `142618` sit on the column object: `addColumn` returns a `ColumnDefinition`, which extends `Illuminate\Support\Fluent`, and `Fluent::__call` at `vendor/laravel/framework/src/Illuminate/Support/Fluent.php:130` stores any unknown method name as an attribute. `SQLiteGrammar.php` has exactly **1** line containing `check`, line 876, which is the return value of `typeEnum()`, and it defines no `modifyCheck`. The attribute is therefore set, read by nothing, and dropped without an error. Two consequences for whoever fixes this. First, the accurate sentence is: a `->check()` call on a column lands on the column's Fluent object and stores an attribute no SQLite grammar modifier reads. Second, the only path by which this framework writes a CHECK on SQLite is an `enum` column, which is why the constraints in the live DDL come from `151945`'s hand-written SQL and not from any Laravel construct. A gate built on either assumption must test against built DDL, because source text shows neither.

## KI-51 The seeder code that reads the untracked bodies is itself untracked, so the skills and roster pipelines have no implementation on any ref. Filed, not fixed.

**Gap.** Three PHP classes under `database/seeders/` exist on disk, are in no commit, and match no `.gitignore` rule. Verified 2026-10-01, all by per-file `git ls-files --error-unmatch` and `git check-ignore`:

- `database/seeders/ReadsCommittedSource.php`, 2,284 bytes, mtime 2026-09-30 23:19. Untracked, not ignored.
- `database/seeders/SourceDocumentSeeder.php`, 4,389 bytes, mtime 23:17. Untracked, not ignored.
- `database/seeders/UmamusumeRosterSeeder.php`, 5,416 bytes, mtime 23:13. Untracked, not ignored.

The tracked seeder surface at `HEAD` is four files: `DatabaseSeeder.php`, `ScenarioSlotSeeder.php`, `SkillSeeder.php`, `UmamusumeSeeder.php`.

**The wiring is missing too, and that is the part a reader will otherwise get wrong.** `git show HEAD:database/seeders/DatabaseSeeder.php` contains **0** references to the three untracked classes. The working copy contains **5**, so the only thing that invokes this loader is an uncommitted peer diff. The sole tracked mentions of `SourceDocumentSeeder` at `HEAD` are prose in comments, at `app/Console/Commands/UmaImportSupportCards.php:93` and `tests/Feature/SupportCardFetchTest.php:152`, and `ReadsCommittedSource` is named nowhere in tracked content at all. `SkillSeeder.php` is tracked but independent of this path: it seeds nine hardcoded names through `NameNormalizer`, states in its own docblock that `sp_cost` and `type` are deliberately left for the import, and does not read a body.

**Consequence, stated asymmetrically because the asymmetry is the finding.** A fresh clone gets the schema, the models, the views and a tracked seeder that does not touch the source bodies. It does not get the loader, the roster seeder, or the invocation. It does get a working support-card path, because `support-cards.88dea522.json` and `support_effects.ca447e53.json` are tracked and `UmaImportSupportCards.php` is tracked. So the pipelines split in two: one committed end to end, and one whose code, data and wiring are all off-ref. This is KI-49's subject two layers deeper, and it is not the same finding. KI-49 is about bodies. Committing the bodies would not make the skills pipeline run, because nothing on a ref reads them.

**Fix options, owner's call.** Either (a) track the three classes and the invocation in `DatabaseSeeder.php`, or (b) fold the loader back into tracked code so the tracked seeder reads the bodies directly. Option (a) plus KI-49 option (a) is the only combination that makes a fresh clone able to seed offline. What must not stand is the current shape, where the tracked entry point is unaware of an untracked implementation that three untracked files depend on.

**Not acted on here.** The fence on this dispatch forbids tracking source files, and the decision is the owner's. Filed only, with the measurement commands above so the state can be re-checked rather than re-argued.

**Owner.** Data Engineer for the loader and the roster seeder, with the owner deciding between (a) and (b) alongside the KI-49 decision. The two should be ruled together, since either answer on one changes the cost of the other.

## KI-52 Two files share the basename `DESIGN.md` with disjoint section numbering, and citations do not say which one they mean. Filed, not fixed.

**Symptom.** The repository carries two documents with the same basename and different structures.
`DESIGN.md` at the root (394 lines, 27,646 bytes, mtime 2026-09-29) is the **surface specification**: §2
"Visual identity" with §2.3 "Spacing and layout", §3 "Component inventory (actual committed Blade)", §4
"Surface specifications" including §4.2 "Catalog detail `/umamusume/{slug}`", §5 "Data display rules".
`docs/design-research/DESIGN.md` (1,640 lines, 152,232 bytes, mtime 2026-09-30) is the **design system**:
§3 "Colour", §6 "Component anatomy" including §6.11, §6.14 and §6.16, §8 "UX architecture" including
§8.4, §10 "Accessibility". Neither file announces the other, and a reader cannot disambiguate a citation
by grepping its section number, because the number exists in only one of the two.

**Measured, one instance.** A dispatch this session asked for "`DESIGN.md` §6.14 (form field spec), §2.3
(screen structure), §6.5 (stat band markers)" and named the `docs/design-research/` path. §6.14 and §6.5
are real there. **§2.3 is not in that file at all**, and the section the dispatch described as "screen
structure" does not exist in either: the root file's §2.3 is spacing and layout. So the citation resolves
in neither file to the thing named. A register entry inherits the same unreachable pointer: KI-35's
symptom attributes a rule to "`DESIGN.md` §2.3's one-primary-action-per-screen".

**Why it matters.** Every design dispatch, register entry, ADR and review that cites a `DESIGN.md`
section without the path is ambiguous, and the ambiguity is silent: the citation looks resolvable, and a
reader who opens the wrong file finds either nothing or, worse, a different rule at a similar number. The
same failure shape as KI-49 and KI-51, where a path that looked canonical turned out not to be the one
that carries the content.

**Fix options, owner's call.** Either (a) rename one file, and the smaller diff is the root file, since
the `docs/design-research/` corpus is already one namespace and the root name is the one borrowed from
convention; or (b) keep both names and make the path qualification mandatory in prose, with each file's
opening lines naming the other. Whichever is chosen, a grep for `DESIGN.md` across the corpus will still
need a pass, since the broken citations are already written.

**Not acted on here.** The dispatch's fence forbids renaming either file and forbids edits to the design
record. Filed only, with the measurements above so the state can be re-checked rather than re-argued.

**Owner.** Docs Writer for the cross-reference line and the sweep of existing citations; the rename
decision is the owner's, because it moves a path that other documents cite by name.
