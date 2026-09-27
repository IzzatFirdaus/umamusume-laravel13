# Known issues

Defects found during the scenario-aware design and component work that were **out of that
phase's scope**, so they are recorded here rather than fixed in passing. Each entry states
the command or file that proves it, not just the symptom.

Discovered 2026-09-27. None of these were introduced by the component work; the component
work is what made them visible, because the prototype phase had no running server to hit.

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

## KI-2 Catalog index passes strings where the view expects models

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

## KI-3 `welcome.blade.php` breaks the offline requirement

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

---

## KI-4 `make lore` cannot see untracked files, and checks no terminology

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
