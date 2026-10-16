# Stage 2 seed — fullstack 2.0: routes and controllers

Measured 2026-10-16 against `master` @ `6fea7dc` (live main repo `D:/Projects/umamusume-laravel13`).
**Read-only**: one script plus direct greps. Nothing fixed, nothing committed. This is the first two
sub-audits of the twelve the full audit deferred at §5 — **§3.5 routes and §3.1 controllers only**.

---

**Dated forward note, 2026-10-16.** This file carries two sets of figures. The body below (§1–§4) is the seed as committed at `4e57083`; the appended revision at §5 supersedes its route, controller and method counts, and was landed by `ab1484a` ("close the parser's route bound with `route:list`"). A reader looking for current figures should read the appended revision, not this body.

**Current figures (post-`ab1484a`):** 86 named routes (60 live, 17 orphan, 3 redirect-only), 32 concrete controllers carrying 86 public methods, 0 non-routed public methods, 0 actions pointing at an undefined method. `CockpitController` remains at 2,789 lines.

**Superseded figures (seed body, §1–§4):** 81 named routes (54 live, 15 orphan, 8 api, 3 redirect), 31 controllers (1 untested). These were accurate at `6fea7dc` and are preserved as the record of that read, per the audit file's append-only convention.

The seed's 15-orphan figure also did not sum in the original (12 named plus a "+ 4 API names" parenthetical). The appended revision's 17 replaces it.


**Forward-note addendum, 2026-10-17.** Three section-3 verdicts were superseded after section 1-4 was filed at `4e57083`; do not treat the seed body as current on them:

- **section 3's Database/* verdict.** Section 3 named three unrouted page files; section 6 adjudicates them live. Routes register at `routes/web.php:289-291` (`database.trainees`, `database.skills`, `database.supports`), served by `CatalogController@databaseIndex` (`CatalogController.php:72`), `SkillController@databaseIndex` (`SkillController.php:59`), `SupportCardController@databaseIndex` (`SupportCardController.php:49`); all render through `renderPage()`, which the seed's single-line parser could not see.

- **section 3's literal-path count.** Section 3 named two wizard literal sites; section 7 enumerates eleven write sites across nine files. Four files state the literal-URL pattern is deliberate - there is no Ziggy dependency here: `BuildTarget.vue:170`, `ScenarioSelect.vue:66`, `TraineeProfile.vue:116`, `TraineeSelect.vue:206`.

- **section 3's orphan adjudication.** Section 3 named 15 orphan candidates; section 5 adjudicates `runs.create` live regardless (23 test files, `SCREEN_SPEC.md:217`; "Confirmed-dead: 0 of 86"). Its UI link was deliberately retired: `AppLayout.vue:123` points "New Career" at `career.scenario`, not `runs.create`.

**Stale items in this file's own section 10.** The `uma:backup` command test closed at `23f9d64` (`test(backup): pin uma:backup contract on scratch database`), and dark-ramp >= 3:1 already passes: G-18 measures all 19 text/background pairs plus 9 grade-badge fills in both themes, worst dark 5.48:1. Current figures live at section 5 onward - section 5 supersedes section 3's route, controller and method counts; sections 6-7 supersede the Database/*, literal-path and orphan figures above.

---

## 1. Method, and the bounds on what it can see

A parser walked `routes/web.php` and `routes/api.php`, resolved controller FQCNs through each file's
`use` statements, and cross-referenced every route name against `app/`, every `Inertia::render(...)`
against the files under `resources/js/pages/`, and every public controller method against the route
set. Output: `.scratch-uma/audit-agent-2026-10-16/stage2b.txt`.

Three bounds, stated before any number is read as a finding:

1. **The frontend never names a route.** This app ships no Ziggy; controllers compose URLs server-side
   (`route('x')` into an Inertia prop) and the Vue reads `props.run.update_url`, `props.action`. A first
   pass classified reachability by searching `resources/js` for route names and reported **54 redirect-only
   and 13 orphan**. That was an instrument failure, not a finding — corrected to judge from `app/`, which
   inverted most of the table. The rule: in this codebase, *a route name absent from the frontend is normal*.
2. **Routes outside `routes/`.** `bootstrap/app.php:29` registers `artwork.show`, which `routes/web.php:276`
   says is deliberately outside the `web` group. So the parsed total is **80 named routes in `routes/`, and
   81 including `bootstrap/app.php`**.
3. **Single-line declarations only.** The regex needs `Route::verb(...)` and `[Cls::class, 'method']` on one
   line. Multi-line or grouped declarations are missed, which is why §3's non-routed-method list is a
   **candidate list, not a verdict**.

## 2. §3.5 — routes

81 named routes, **zero unnamed**. Classification of the 80 parsed:

| Kind | Count | Meaning |
|---|---|---|
| **live** | 54 | name passed to `route()` in `app/` as a composed URL, i.e. a browser reaches it through a rendered prop |
| **orphan** | 15 | name never referenced in `app/` — reachable only by typing the URI |
| **api** | 8 | the read-only `/api/v1` JSON surface declared in `routes/api.php` |
| **redirect** | 1 | `runs.show` → `TrainingRunController::redirect`, the self-declared "compatibility doorway" |
| **redirect-only** | 2 | `runs.skills.planner`, `legacy.compare` — referenced in `app/` only as a redirect target |

### The 15 orphans

```
home                     GET  /                      DashboardController::index
career.scenario.store    PUT  /career/setup/scenario  ScenarioSelectController::store
career.trainee.store     PUT  /career/setup/trainee   TraineeSelectController::store
career.trainee.profile   GET  /career/setup/trainee/{umamusume}   TraineeProfileController::show
career.target.store      PUT  /career/setup/target    BuildTargetController::store
career.legacy.store      PUT  /career/setup/legacy    LegacySelectController::store
career.preflight         GET  /career/setup/preflight PreflightController::show
career.snapshot          GET  /career/snapshot        SnapshotController::entry
skills.index             GET  /skills                SkillController::index
runs.create              GET  /training-runs/create   TrainingRunController::create
review.resolve           POST /review/{candidate}     ReviewController::resolve
preferences.update       PUT  /preferences           PreferenceController::update
(+ 4 API names counted with the api bucket where they are also unreferenced)
```

**Why most of these are names-in-use-by-URI, not dead code — and one real drift.** Two wizard pages post to
a **literal path** instead of the named route:

- `resources/js/pages/Career/BuildTarget.vue:172` → `form.put('/career/setup/target', …)`
- `resources/js/pages/Career/LegacySelect.vue:166` → `… ).put('/career/setup/legacy', …)`

The URI is live; the **route name is orphaned because the frontend bypassed it.** That contradicts
`AGENTS.md` §7's "`named routes and `route()` everywhere`" rule directly, and it is the mechanism behind
`career.target.store` and `career.legacy.store` reading orphan. The same explanation covers
`career.scenario.store` and `career.trainee.store` (wizard forms whose action is not composed server-side),
and `home` — the root route, reached by `/`, its name referenced nowhere.

`review.resolve`, `preferences.update`, `career.snapshot`, `career.preflight`, `runs.create`,
`skills.index` and `career.trainee.profile` need a per-route judgement the parser cannot make
(is the URI reached from a control the frontend builds another way?). **Named, not concluded.**

## 3. §3.1 — controllers

**31 controllers.** One has no Feature test naming it: `ArtworkAssetController` (66 lines). Everything else
is covered by a test that names the class.

Size, measured — this is the number the full audit deferred:

| Controller | Lines |
|---|---|
| **`CockpitController`** | **2,789** |
| `TrainingDecisionController` | 743 |
| `CatalogController` | 691 |
| `RacePlannerController` | 597 |
| `SkillsPlannerController` | 564 |
| `PreflightController` | 547 |
| next 25 | 447 down to 66 |

`CockpitController` is **2,789 lines against `AGENTS.md` §7's "thin controllers; logic in `app/Actions` or
`app/Services`"** — 3.7× the next-largest. The audit's provisional pointer said "≥ 2,650"; the measured
figure is 2,789, and the four follow-up commits since that read did not touch it. This is the single clearest
architecture-vs-code divergence the seed found, and it is a **refactoring decision, not a defect** — it belongs
to the Architect (§4 role table), not to an audit.

### Component ↔ page cross-check

- **42** distinct components are named by `Inertia::render(...)` in `app/`. **Zero** of them lack a file under
  `resources/js/pages/` — no route renders a page that does not exist. The failure mode the dispatch asked to
  flag is **absent**.
- **59** page files on disk; **17** not matched to a render string. Those 17 decompose, and only three are real:

| Group | n | Verdict |
|---|---|---|
| `pages/Career/Cockpit/Panels/*.vue` | 11 | **Instrument false positive.** These are Vue sub-components imported by `ScenarioPanel.vue`/`Cockpit.vue`, not Inertia pages that happen to live under `pages/` |
| `Catalog/Index`, `Skills/Index`, `SupportCards/Index` | 3 | **Explained.** `CatalogController.php:80` and `SupportCardController.php:57` render a **variable** — `Inertia::render($page, $props)` — so no literal matches. Live, not dead |
| **`Database/Skills.vue`, `Database/Supports.vue`, `Database/Trainees.vue`** | **3** | **Genuinely unrouted page files.** Each imports `AppLayout` and a catalog component (`SkillCatalog`, `SupportCardCatalog`, `TraineeCatalog`) — page-level markers — while `DatabaseController` renders only `Database/Index`, `Races`, `Scenarios`, `Events`, `ShopItems`, `Sparks`, all as literals. **Three Inertia shells no controller can reach** |

The three `Database/*` shells sit directly behind `SCREEN-023`'s "three new views" in the full audit's
Appendix B, which graded that row **LANDED** on the evidence that `Database/Index.vue:5-6` names them. The
seed contradicts that: the files exist and are pages, but **no route or render reaches them**. Either
`Database/Index.vue` is the live surface and these three are superseded scaffolding, or the routes are
declared in a form the parser cannot see (bound 3). **Which is a named finding for the next pass, not a
conclusion here.**

### Non-routed public methods — candidate list

`ArtworkAssetController::show`, `LegacyController::builder`, `LegacyController::update`,
`SaveVeteranController::show`, `SaveVeteranController::store`, `VeteranController::show` — 6 flagged.
`ArtworkAssetController::show` is **explained** (its route is `artwork.show`, registered in
`bootstrap/app.php:29`, bound 2). The other five are almost certainly the same class of miss —
`docs/audits` and `SCREEN_SPEC.md` both reference `runs.save-veteran` and the Legacy builder as live
surfaces — so they are reported as **parser blindness, not dead code**, and left unverified.

## 4. What this seed did NOT do

§3.2 requests/models/services/actions/enums · §3.3 schema and `migrate:status` · §3.4 data pipeline ·
§3.6 config keys and dead config · §3.7 the quality bar's gate inventory · §3.8 ADR coverage incl.
`ADR-0020` program scope · §3.9 tests by layer · §3.10 `lang/en` dead and missing keys · §3.11 tools ·
§3.12 legacy Blade. **Ten of twelve sub-audits remain.** Stage 3 (Appendix A rows 3, 5, 7, 13, 40, 41 and
the 53 backlog rows) was **not attempted** — this pass spent its budget on items 1 and 2 of the ordering
before it.

No gate ran beyond what is named above: nothing was executed except the read-only parser and greps.
`php artisan route:list` was **not** run, which is the single command that would close bound 3 and turn the
orphan and non-routed-method candidate lists into verdicts.

**STAGE 2 SEED FILED — §3.5 and §3.1 of twelve; 81 named routes (54 live, 15 orphan, 8 api, 3 redirect), 31 controllers (1 untested), `CockpitController` measured at 2,789 lines, 3 unrouted `Database/*` page files, 0 render-without-page defects. No file changed but this one. Nothing committed.**

## 5. Bound 3 closed with `route:list`, 2026-10-16

`php artisan route:list --json` ran at `4e57083`, exit 0, captured to
`.scratch-uma/audit-agent-2026-10-16/route-list.json` (17,887 bytes), and every §2 and §3 candidate was
re-verified against it by `.scratch-uma/audit-agent-2026-10-16/{compare.py,stage2c.py,stage2d.py}`
(output in the sibling `.txt` files). Bound 3 was real: the single-line regex under-counted, and every
candidate it produced dissolved.

**The framework table.** 92 rows, **89 named**, 3 unnamed. The unnamed three are all framework-injected
(`_inertia/devtools/entries`, `_inertia/devtools/entries/{id}`, `up`), so the seed's "zero unnamed" survives
as "the **app** declares zero unnamed routes". Three further named rows are package-owned
(`storage.local`, `storage.local.upload`, `boost.browser-logs`). **The app's own surface is 86 named routes,
not 81.**

| Seed | This pass | Cause |
|---|---|---|
| 81 named | **86** named | 5 wrapped-chain routes invisible to a one-line regex |
| 8 `api` | **6** `api.v1.*` | the parser read `routes/api.php:11-16` without the `->name('api.v1.')` group prefix and counted two `web.php` routes in the same bucket |
| 54 live / 15 orphan / 1 redirect / 2 redirect-only | **60 live / 17 orphan / 3 redirect-only** | the added 11 routes plus 5 new orphans; `runs.show` (seed: `redirect`) now sits with the other two |

The seed's 15-orphan figure also did not sum: the block printed 12 names and a parenthetical "+ 4 API names"
= 16. Recorded as an arithmetic slip in the seed's own §2, not a measurement change.

**Why the parser missed 15 rows — three mechanisms, not one.**

- **5 wrapped chains**: `legacy.builder`, `legacy.update`, `veterans.show`, `runs.veteran`, `runs.veteran.store`.
  Each is `Route::verb(...)` followed by `->whereNumber(...)` on the **next** line, so `\](.*?);` — which the
  scanner applies per `splitlines()` entry — never reaches its semicolon. `routes/web.php:227-232,246-248,258-263`.
- **6 group-prefixed names**: the `api.v1.*` set. The literal `->name()` in the file is `umamusume.index`; the
  registered name is `api.v1.umamusume.index`. The parser's four `training-runs.*` / `umamusume.*` rows are
  **invented names**, and are the only false positives the framework table caught.
- **3 closures + 1 out-of-`routes/` file**: `boost.browser-logs`, `storage.local`, `storage.local.upload`
  carry `Closure` actions the `[Cls::class, 'method']` pattern cannot match, and `artwork.show` is bound 2.

### §3.5 verdicts — the 15 orphans, and the 5 the parser never saw

All 12 names the seed listed **are** registered routes and **are** unreferenced by name in `app/`, so each
orphan verdict stands as a classification. Five more join them: `legacy.update`, `runs.store`,
`runs.import.store`, `runs.import.preview`, `support-cards.index`.

But "orphan" now has a sharper meaning, and it is not dead code. A literal-path sweep over `resources/js`
(`.scratch-uma/audit-agent-2026-10-16/phase4.py`) reaches **16 of the 17 by URI**. The single route with
neither a name reference nor a literal-path mention in `resources/js` is `runs.create` — and it is live
regardless: `SCREEN_SPEC.md:217` documents it as the transitional create surface, and **23 files under
`tests/`** drive it by URI (36 `page.goto` sites across the browser suite, 9 `test()->get()` calls in
`TraineeSelectorTest.php` and `RunCreateSurfaceTest.php`, plus the `DesignTokensTest.php` route fixture). The 6 `api.v1.*` names are unreferenced by design and exercised by four Feature files
(`ApiV1Test.php`, `ApiV1SupportCardTest.php`, `ApiV1RunIndexPaginationTest.php`,
`ApiV1ValidationEnvelopeTest.php`). **Confirmed-dead routes: 0 of 86.**

### §3.1 verdicts — non-routed methods: the candidate list was entirely artifact

Re-derived against the framework's own action strings, keyed by **file path** rather than short class name
(`SupportCardController`, `TrainingRunController` and `UmamusumeController` each exist twice, once under
`Api/V1/`, and a short-name key silently merges them).

33 files under `app/Http/Controllers/`, one of them the method-less base `Controller.php`, so **32 concrete
controllers** (the seed said 31) carrying **86 public methods** excluding `__construct`/`__invoke`. All 86
appear as a route action. The reverse check runs the other way and also returns **0**: no registered action
points at a method that is not defined.

Each of the seed's six candidates is routed:

| Candidate | Registered as |
|---|---|
| `ArtworkAssetController::show` | `artwork.show` `GET artwork/{kind}/{id}` (bound 2) |
| `LegacyController::builder` | `legacy.builder` `GET legacy/{run}` |
| `LegacyController::update` | `legacy.update` `PUT legacy/{run}` |
| `SaveVeteranController::show` | `runs.veteran` `GET training-runs/{run}/veteran` |
| `SaveVeteranController::store` | `runs.veteran.store` `POST training-runs/{run}/veteran` |
| `VeteranController::show` | `veterans.show` `GET veterans/{veteran}` |

**Non-routed public controller methods: 0.**

### A bound the seed did not state, found while closing bound 3

Two new instrument bounds came out of the re-run and both are worth keeping:

1. **`route(` is overloaded.** `$this->route('run')` in a Form Request reads a route *parameter*; it is not
   the URL helper. The first re-run reported `run` and `turn` as "references to unregistered routes"
   (14 call sites, `app/Http/Requests/StoreBuildTargetRequest.php:122`, `StoreTurnEntryRequest.php:67-68`
   among them). Excluding `->route(` and `::route(` removed exactly those two rows and **moved no bucket**:
   every app route name is dotted and every parameter is not, so the artifact could not have inflated `live`.
2. **`Inertia::render()` is not always a literal.** Three controllers pass the page name through a private
   `renderPage(string $page, array $props)` helper — `CatalogController.php:78-81`,
   `SkillController.php:65-68`, `SupportCardController.php:55-58`. A `Inertia::render\('X'` regex therefore
   saw **42** page names where the real number is **48**, and the §3.1 "**17** not matched to a render string" list
   was 6 rows too long. This is the mechanism behind the `Database/*` question below.

With that fix, the page cross-check is: **48** distinct page names handed to a render call, **59** `.vue`
files under `resources/js/pages/`, **11** with no render call, **0** render names with no file. All 11 sit in
`pages/Career/Cockpit/Panels/` and are child components rather than routed pages. Six are imported by
`resources/js/components/scenario/ScenarioPanel.vue:50-55`. **Five are not imported or mentioned anywhere in
`resources/js` except in comments**: `EpithetRoutesPanel.vue`, `GradePointObjectivesPanel.vue`,
`ProShopPanel.vue`, `SpiritBurstsPanel.vue`, `TeamRankLadderPanel.vue`. `ScenarioPanel.vue:79-89` says why
two of them are absent — the Phase 7A.1 registrations were removed because a `Map` re-registration shadowed
the first entry. Files left in place; disposition is an owner question.

## 6. `Database/*` verdict — case (b): the parser missed the declaration, Appendix B was right

The three files are **live surfaces with routes and controllers**; the seed's contradiction was a second
instance of the same regex bound, and `SCREEN-023 LANDED` in the full audit's Appendix B stands.

| Page file | Route (framework table) | Controller action | Render site |
|---|---|---|---|
| `resources/js/pages/Database/Trainees.vue` | `database.trainees` `GET database/trainees` | `CatalogController@databaseIndex` | `app/Http/Controllers/CatalogController.php:72` |
| `resources/js/pages/Database/Skills.vue` | `database.skills` `GET database/skills` | `SkillController@databaseIndex` | `app/Http/Controllers/SkillController.php:59` |
| `resources/js/pages/Database/Supports.vue` | `database.supports` `GET database/supports` | `SupportCardController@databaseIndex` | `app/Http/Controllers/SupportCardController.php:49` |

Each call is `$this->renderPage('Database/Trainees', ...)`, which the `Inertia::render` regex cannot see.
The same indirection accounts for the other three rows the seed listed as unrendered: `Catalog/Index.vue`
(`CatalogController.php:57`), `Skills/Index.vue` (`SkillController.php:48`) and `SupportCards/Index.vue`
(`SupportCardController.php:39`). So **6 of the seed's 17** were
parser blindness and the remaining **11** are the Cockpit panels, which are never routed pages.

The pages are not incidental scaffolding either: `resources/js/pages/Database/Skills.vue:2-8` states its own
provenance in its header comment — "*The Database hub's Skills area (SCREEN-023, plan §8 D17) ... behind
`SkillController::databaseIndex`*" — and `CatalogController.php:60-73` explains why the body is shared
rather than the URL borrowed: "*A redirect was the smaller diff and it is refused by the slice brief,
because no owner ruling authorises one*". The seed's row "**Genuinely unrouted page files**" (§3.1, line 108) is withdrawn, and with it the closing
line's "3 unrouted `Database/*` page files".
No file deleted, no route added.

## 7. Finding: 11 frontend call sites bypass named routes, 2026-10-16

The seed named two. Enumerating the whole pattern (`phase4.py`, confirmed by direct grep) returns **11 call
sites across 9 files**, and they are the mechanism behind **every** non-API entry in the §3.5 orphan list.
This contradicts `AGENTS.md` §7's "named routes and `route()` everywhere".

Writes (form submissions) — 11:

| File | Line | Literal path | Named route it bypasses |
|---|---|---|---|
| `resources/js/pages/Career/ScenarioSelect.vue` | 78 | `/career/setup/scenario` | `career.scenario.store` |
| `resources/js/pages/Career/TraineeSelect.vue` | 210 | `/career/setup/trainee` | `career.trainee.store` |
| `resources/js/pages/Career/TraineeProfile.vue` | 119 | `/career/setup/trainee` | `career.trainee.store` |
| `resources/js/pages/Career/BuildTarget.vue` | 172 | `/career/setup/target` | `career.target.store` |
| `resources/js/pages/Career/LegacySelect.vue` | 166 | `/career/setup/legacy` | `career.legacy.store` |
| `resources/js/pages/Legacy/Builder.vue` | 285 | `` `/legacy/${props.run.id}` `` | `legacy.update` |
| `resources/js/pages/Preferences/Edit.vue` | 238 | `/preferences` | `preferences.update` |
| `resources/js/pages/Runs/Create.vue` | 90 | `/training-runs` | `runs.store` |
| `resources/js/pages/Runs/Import.vue` | 172 | `/training-runs/import` | `runs.import.store` |
| `resources/js/pages/Runs/Import.vue` | 281 | `/training-runs/import/preview` | `runs.import.preview` |
| `resources/js/components/review/CandidateForm.vue` | 50 | `` `/review/${props.candidate.id}` `` | `review.resolve` |

Navigation is the same breach at larger scale: **at least 31** `href="/..."` / `router.push("/...")` literals
(one grep pattern, letter-leading paths only, so `href="/"` roots and other binding forms are excluded)
across the pages
(`Dashboard.vue:206,350,353,392`, `SetupLayout.vue`, `Runs/Index.vue:46,52`, the wizard's back-links, and
the `/veterans`, `/umamusume`, `/support-cards`, `/skills`, `/database`, `/legacy` links). The wizard's own
step-back links restate the paths its forms post to, so a URI change is a multi-file edit today.

The route names orphaned by this pattern: `career.scenario.store`, `career.trainee.store`,
`career.target.store`, `career.legacy.store`, `legacy.update`, `preferences.update`, `runs.store`,
`runs.import.store`, `runs.import.preview`, `review.resolve` — 10 of the 17 §3.5 orphans.

Not fixed: all nine files are page components shared with peer sessions, and this pass's brief says report,
not repair. Options for the owner, not for an agent to pick: **(a)** replace each literal with a `route()`
call the server already composes into a prop, which restores the §7 rule and makes the orphan list empty;
**(b)** keep the literals and drop the now-unused route names, which is weaker — the names are what
`SCREEN_SPEC.md` and the docs cite, and removing them breaks the citation surface; **(c)** rule that the
frontend may address URIs literally and amend §7 to say so, which makes today's code legal and the finding
closed by ruling. The audit takes no position; the choice changes 11 lines or one rule.

## 8. §3.2 — dead-code inventory, 2026-10-16

`.scratch-uma/audit-agent-2026-10-16/{phase5.py,phase5b.py}`, outputs in the sibling `.txt`. The class
census searched 2,049 files (docs excluded, so a doc mention cannot mask a missing caller); the
command-verb census adds `docs/`, `README.md`, `AGENTS.md`, `Makefile`, `composer.json` and
`package.json`. Line counts below are `wc -l`, not the script's off-by-one.

**One wording correction to §7, which is append-only above:** its last paragraph says "all nine files are
page components". Nine is the file count; eight are pages and one, `components/review/CandidateForm.vue`,
is a component.

### Counts in scope: 153 classes

| Directory | Tracked `.php` |
|---|---|
| `app/Http/Requests` | 31 |
| `app/Models` | 31 |
| `app/Services` | 43 |
| `app/Actions` | 16 |
| `app/Enums` | 21 (one of them, `HasLabel.php`, is a **trait**) |
| `app/Console/Commands` | 8 |
| `app/Domain` | 3 |

Every Form Request, every Service, every Action and every Model has at least one non-test reference. The
result is **2 of 153** with none, and both are console commands.

### The two zero-reference classes — and why the instrument cannot decide them

| File | Lines | Verb | Registered | Exercised by |
|---|---|---|---|---|
| `app/Console/Commands/MigrateToMariaDb.php` | 84 | `db:backup:mysql` | yes — `php artisan list` line 56 | **nothing in the repository** |
| `app/Console/Commands/UmaDbGuard.php` | 75 | `uma:db-guard` | yes — `php artisan list` line 191 | `tests/Feature/DatabaseSafetyTest.php:192`, `tests/browser/global-setup.ts:133` |

**Bound:** a command is addressed by its verb, not its class name, so "no file references the class" proves
nothing for this directory. `php artisan list` (captured to `.scratch-uma/audit-agent-2026-10-16/artisan-list.txt`)
plus a verb sweep is the instrument that decides, and it splits the two: `uma:db-guard` is live, and the
class-name silence is an artifact. `bootstrap/app.php:18` registers `routes/console.php` (which holds only
the `inspire` closure) and there is no `withCommands()` call, so both classes arrive by skeleton
auto-discovery of `app/Console/Commands` — which the framework list confirms rather than this pass assuming.

`db:backup:mysql` is therefore the one genuinely unexercised command surface: 84 lines whose verb appears
nowhere outside its own signature line at `MigrateToMariaDb.php:12` — no test, no doc, no `Makefile` target,
no `composer` script, and there is no CI to run it. The 276-line action it wraps *is* tested
(`app/Actions/MigrateToMariaDbAction.php`, exercised at `tests/Feature/MariaDbBackupTest.php:22,39,62,85`),
so the gap is the command wrapper and the `config/mysql-backup.php` (13 lines) it reads. This is the same
subsystem the doc-drift audit already graded **UNDOCUMENTED-LIVE** at
`docs/audits/doc-drift-audit-2026-10-16.md:167-168`, with the `AGENTS.md` §1 "SQLite only" conflict recorded
unresolved at `:194`. **Owner question, held: not deleted, not documented, not wired.**

### Enums: 13 cases with no `Class::Case` reference, and not one of them unreachable

`AliasLanguage::Romanized` · `BuildPurpose::{ChampionsMeeting, ParentFarming, SkillFarming}` ·
`MoodTier::Awful` · `PerformanceType::Passion` · `SpiritBurstState::NormalBurstSpent` ·
`TurnChoice::{TrainStamina, TrainPower, TrainGuts}`.

The second instrument asks whether anything hydrates the whole case set, and all six owning enums do:
`BuildPurpose` 4 hydration sites (`BuildTargetController`, `StoreBuildTargetRequest`), `MoodTier` 4,
`PerformanceType` 4, `TurnChoice` 6, `AliasLanguage` 3, `SpiritBurstState` 3
(incl. `app/Models/TurnEvents/SpiritBurstPayload.php`). A case reached through `from()`/`tryFrom()` never
needs its symbol spelled out, so **no unreachable enum case is claimed**. Two rows deserve the note anyway:
`PerformanceType::Passion`'s value appears only in `PerformanceFoundationTest.php` and
`PerformanceTurnInputTest.php` — test-only, no production site produces it; `TurnChoice`'s three training
cases exist as composed keys (`training-Stamina`, seen in `resources/js/types.ts`), so their absence as
symbols is by construction. **Instrument bound:** the census treats every file under `app/Enums/` as an
enum, and `HasLabel.php` is a trait — its "never hydrated" line is a false positive.

### Service and Action method surface: 15 candidates, adjudicated

| Candidate | Verdict | Evidence |
|---|---|---|
| `SkillRegistry::all` (`:42`) | **uncalled** | no `->all(` in any file naming `SkillRegistry` |
| `SkillRegistry::has` (`:63`) | **uncalled** | same |
| `SkillRegistry::registryPath` (`:78`) | **uncalled** | the only repo hit is the declaration |
| `SkillRegistry::require` (`:86`) | **uncalled** | no `->require(` or `::require(` site exists anywhere |
| `SkillExecutor::getLog` (`:135`) | **uncalled** | declaration is the only hit |
| `SkillExecutor::executePlan` (`:122`) | self-only → visibility | called at `$this->executePlan` inside its own class |
| `ArtworkMirror::sourceIds` (`:131`) | self-only → visibility | called from a sibling public method in the same class |
| `DatabaseGuard::{connectionName, isSqlite, sameFile, assertNoConfigurationCache, assertAbsolutePath, assertNotCanonical, assertMatchesExpected}` (7) | self-only → visibility | every site the repo-wide sweep found is `$this->` inside `DatabaseGuard.php`; no external call exists |
| `Contracts\ScenarioSourceParser::parse` | live, instrument false positive | a contract method invoked through the interface; 17 `->parse(` sites incl. `PipelineRunner` |

So: **5 public methods with no caller anywhere, 9 public methods callable only from inside their own class**
(a visibility finding, not a dead-code one), and 1 false positive. Four of the five uncalled methods sit on
one class: `SkillRegistry` exposes 8 non-constructor methods and its users (`ManageSkills.php:54,116,151`,
`SkillExecutor.php:45`, `SkillMatcher.php:32,95`, `SkillAutomationTest.php:27,28`) use only
`enabled`, `get`, `count` and `countEnabled`. That the whole `Skill{Registry,Matcher,Executor}` tooling layer
is also the layer whose test suite self-skips when `.agents/skills.json` is absent (§9) is the reason no
caller has appeared for its accessors.

No file was removed, no method visibility changed, no enum case deleted. **No gate ran in this section**:
nothing here is proven by executing the suite.

## 9. §3.9 — tests by layer, 2026-10-16

`.scratch-uma/audit-agent-2026-10-16/phase6.py` over `git ls-files tests`, with the case counts taken
independently from `grep -c` so the two instruments are separable. **The suite did not run in this pass**;
every number below is a declaration count, not an execution result, and no claim of a passing suite is made.

### Layers

| Layer | Files | Declared cases |
|---|---|---|
| `tests/Feature` | 196 `.php` | 1,919 `test()`/`it()` |
| `tests/Unit` | 7 `.php` | 41 |
| `tests/browser` | 54 `.spec.ts` + 13 non-spec files (11 `.ts`, 2 `.html`, incl. `global-setup.ts`) | 386 `test()` |
| `tests/Fixtures` | 1 `.php` + 4 stored JSON bodies | — |
| glue | `tests/Pest.php`, `tests/TestCase.php` | — |

`grep -rhoE "^\s*(test|it)\("` gives 1,960 across `Feature` + `Unit` (the 1,919 + 41 split) and 386 across
the specs: **2,346 declared cases total**. The script's lower figure (2,014) counted only tracked files and
only cases whose title opens on the same line — a stricter instrument on a smaller set, recorded here so the
two are not read as a discrepancy.

**The suite is also untracked at the edge:** `tests/browser/layout-width.spec.ts` exists on disk and is not
in `git ls-files`, and 22 test files carry uncommitted peer edits. A run this pass would have measured the
working tree, not HEAD.

### Stale-reference sweep

The dispatch for this pass reduced the scope from "every test naming anything absent" to three bounded
checks. All three are clean, and the two that lit up are instrument artifacts:

- **`route('name')` in a test, for a name the framework does not register: 0.** The sweep excluded
  `$this->route('param')` (the parameter accessor, §5's bound) and checked the remainder against the
  `route:list` name set.
- **`use App\…` / FQCN strings pointing at a nonexistent file: 4 apparent, 0 real.** All four are in
  `tests/Feature/LessonEvidenceBoundaryTest.php`, where `App\Enums\LessonKind`, `App\Enums\LessonType`,
  `App\Models\TurnEvents\LessonPayload` and `App\Models\Lesson` appear as **string literals inside
  `enum_exists()`/`class_exists()` assertions negated at `:39-42`** — the Grand Concert Slice 8 evidence
  boundary, which pins that those classes do *not* exist. A FQCN regex cannot tell a reference from an
  absence claim; the file had to be read. **Bound for the next pass: an absence-pinning test is the one
  place where "references a nonexistent class" is correct behaviour.**
- **Literal URIs in a test that match no registered route: 1 apparent, 0 real** —
  `ErrorPageViewsTest.php` requesting `/no-such-page`, a deliberate 404 probe.

So: no stale test file was found. The ten suspects the full audit named were not individually re-checked
beyond this sweep, and that wider read stays a follow-up.

### Skipped and conditional cases: 6, each with a stated reason

| Site | Mechanism | Stated reason | Consequence |
|---|---|---|---|
| `tests/Feature/DesignTokensTest.php:515` | `markTestSkipped` | "Playwright browser driver not installed; skipping D-288/G-18 browser gate." | that design-token gate is inert on a host without the driver |
| `tests/Feature/SkillAutomationTest.php:12` | `markTestSkipped` | its input `.agents/skills.json` is excluded by `.gitignore:49`, so "a fresh clone or worktree has no registry and these tests would fail for a reason unrelated to the change under test" | the entire `Skill{Registry,Matcher,Executor}` tooling layer is **untested in a fresh worktree** — which is the same condition §8 found four uncalled accessors under |
| `tests/browser/review.spec.ts:16,38,54,68` | `test.skip(true, …)` | "No pending candidates in the scratch database" (four variants: to name / measure / etc.) | **4 of the 5 tests in that spec are conditional on queue state**; on an empty `/review` queue the surface has no browser coverage at all, and the run still reports green |

No `test.only`, `describe.only` or `->only()` marker exists in `tests/` — checked, nothing silent is narrowing
a run.

### What §3.9 does not cover

`SCREEN_SPEC.md` state-table coverage per screen, the gate registry's Level-3 evidence, and whether the
1,960 PHP cases actually pass at this HEAD — the suite was not run here, and by the evidence rule a
declaration count is not a pass.

**STAGE 2 AT FOUR OF TWELVE — §3.5, §3.1, §3.2 and §3.9 filed. Bound 3 closed against the framework's own
route table: 86 app named routes, 0 confirmed-dead, 0 non-routed controller methods. The three `Database/*`
pages are live behind a `renderPage()` parameter and the seed's "genuinely unrouted" row is withdrawn.
11 frontend literal-path sites enumerated with the 10 route names each one orphans; the single orphan with
no literal twin is `runs.create`, exercised by 23 test files. §3.2 names 5 uncalled methods and the one
unexercised command, `db:backup:mysql`, held for the owner with the rest of the MariaDB subsystem. §3.9
reports 2,346 declared cases, 0 stale references and 6 conditional skips. Nothing fixed, nothing pushed,
no gate run.**

## Notes for downstream prompt authors

Added 2026-10-16. Facts for whoever drafts the next commit-agent prompt against `career-cockpit-forms.spec.ts`.

**The browser-spec target is two-axis, not a single pass/fail line.** The prior commit-agent prompt targeted `FULL-SPEC CONFIRMED — 17/17`. That target is unreachable at `HEAD`:

| Instrument | HEAD | working tree |
|---|---|---|
| `grep -cE "^[[:space:]]*test\("` | 16 | 17 |
| the earlier `grep -c "^  test("` | 0 | 0 |

The indented-optional pattern is the corrected instrument; the earlier pattern matched nothing because `test(` calls sit at column 0. At `HEAD` the measured result is **14/16**, with the two reds being the team-editor open-guard cases. The working tree has a 17th case (peer-added, uncommitted) that also runs the cockpit walk and also asserts the Unity Cup bursts widget renders **as a meter**.

**The Spirit-Bursts meter assertion now appears in two spec files, not one.** The committed spec asserts it at `career-cockpit-forms.spec.ts` around the `:79` region. The peer's uncommitted 17th case asserts the same shape. Both assert a widget shape no component emits: `{label}: not recorded + role="img"` is emitted only by `ResourceMeter.vue:35`, used once at `HEAD` for `label="Current Spirit"` at `UnityCupPanel.vue:236`. Whoever resolves the Spirit Bursts question — Option A (component defect), Option B (spec defect), or Option C (both) from the scenario-panel investigation — has to touch both spec files, not one.

**Therefore:** the next commit-agent prompt should target a two-axis count — "team-editor reds classified, Spirit-Bursts reds counted" — not a single `N/N`. A single line invites the reader to treat the Spirit-Bursts assertion as confirmation of the wizard-walk fix. It is not; it is a second red on an unresolved widget-shape question.

**Windows scratch-writing must be byte-safe.** PowerShell's `Get-Content`, `Out-File` and `Set-Content` mangle non-ASCII characters (`—`, `§`, `–`) when they round-trip through the default encoding — measured on the 2026-10-16 pass, where a `Get-Content` read of `KNOWN-ISSUES.md` showed the `−` in the KI-104 entry as `âˆ’`. Any commit-agent pass that edits a file carrying non-ASCII — the register, the audits, `AGENTS.md`, `SCREEN_SPEC.md` — assembles its scratch output in Python (`open(..., encoding='utf-8')`) or with `System.IO.File::WriteAllText` under explicit UTF-8, and verifies the written bytes with `git hash-object -w` before pointing the index at them.

## 10. §3.3 — schema and migrate:status, 2026-10-16

Measured at HEAD `8389da8`, read-only, against `database/migrations/` and `database/database.sqlite`.

### Migration census

| Question | Answer |
|---|---|
| Migration files on disk | 62 |
| Migrations without a `down()` method | 0 |
| Newest migration (filename) | `2026_10_13_120000_create_trainee_goals_table.php` |
| Newest migration (mtime) | 2026-10-13 21:51 |

Every one of the 62 files declares `public function down`. The newest is the trainee-goals table, three days older than this pass.

### Applied-vs-files against the dev database

`php artisan migrate:status` (read-only; nothing applied, nothing rolled back):

| Question | Answer |
|---|---|
| Ran rows | 62 |
| Pending rows | 0 |
| Migration files on disk | 62 |
| Discrepancy (files minus ran) | 0 |

**No pending migration on the dev file.** The KI-60 class defect — committed migrations sitting Pending while the in-memory suite passes because `phpunit.xml` forces `DB_DATABASE=:memory:` — is not present today: the dev database's `migrations` table carries all 62 files at Ran. This is the evidence half of the KI-60 remediation; the applied-to-dev leg stays open and is named out of scope below.

### Schema census

32 tables on disk. 23 Eloquent models, one per app table; 8 further classes under `app/Models/` are `final readonly` payload value objects (`Advisor\BuildTargetPayload`, `Legacy\LegacySelectionPayload`, and six `TurnEvents\*Payload`), not table-backed.

| Metric | Value |
|---|---|
| Tables on disk | 32 |
| Table-backed models | 23 |
| Orphan tables (no model) | 9 |
| Orphan models (no table) | 0 |

The nine orphan tables are the framework's own, every one expected: `cache`, `cache_locks`, `failed_jobs`, `job_batches`, `jobs`, `migrations`, `password_reset_tokens`, `sessions`, `sqlite_sequence`. `preferences` is model-backed (`Preference`), so it is not among them; `users` carries the framework-default `User` model (`AGENTS.md` §17).

### Model-vs-column cross-reference

Each model's `getFillable()` and `getCasts()` keys were checked against its table's columns (census written from `pragma table_info`).

| Result | Count |
|---|---|
| Models with a fillable or cast naming a column absent from the table | 0 |

**Zero discrepancies.** Every fillable attribute and every cast key resolves to a real column. `RunSkill` (a `Pivot`) declares neither. This is the class of defect §3.3 exists to catch, and none is present.

### MariaDB subsystem migration footprint

| Question | Answer |
|---|---|
| Migrations referencing MariaDB/MySQL | 3 files — comments and one driver guard only; no MariaDB-specific table or column |
| Config files declaring MySQL/MariaDB connection details | `config/database.php` (connections `mysql`, `mariadb`, `mysql_backup`) and `config/mysql-backup.php` |
| Does `MigrateToMariaDb` add migrations? | No — command-only (`db:backup:mysql`), copies rows through `MigrateToMariaDbAction` |
| Does `UmaDbGuard` add migrations? | No — no `Schema::` or migration reference in the file |

The three migration hits are `2026_09_27_093945` (a comment: "MySQL DDL the grammar discards here"), `2026_09_28_180350` (a comment plus `if (DB::connection()->getDriverName() !== 'mysql')` around a functional index), and `2026_10_01_124051` (a comment on NULLs in unique indexes). None creates MariaDB-only schema. `MigrateToMariaDbAction` uses `Schema::hasTable` only to check its target exists and `migrateTable()` to copy rows; it creates no table and writes no migration file. **The subsystem's migration footprint is zero files** — the subsystem is command, action and config, not schema.

### What §3.3 does not cover

The MariaDB subsystem's disposition (owner item), the applied-to-dev leg of the KI-60 remediation (whether migrations are applied to the shared dev file as standing practice — owner item), and the correctness of any migration's `down()` beyond its presence. This pass confirmed 62 declarations exist; it did not run a rollback.

## 11. §3.6 — dead config, 2026-10-16

Measured at HEAD `8389da8`, read-only.

### Config file census

17 files under `config/`: `advisor.php`, `app.php`, `auth.php`, `cache.php`, `dashboard.php`, `database-safety.php`, `database.php`, `filesystems.php`, `logging.php`, `mail.php`, `mysql-backup.php`, `queue.php`, `reference.php`, `scenarios.php`, `services.php`, `session.php`, `uma.php`.

Seven are repo-authored (`advisor`, `dashboard`, `database-safety`, `mysql-backup`, `reference`, `scenarios`, `uma`); ten are the Laravel skeleton (`app`, `auth`, `cache`, `database`, `filesystems`, `logging`, `mail`, `queue`, `services`, `session`).

### Per-file key counts

Instrument: every leaf key under each root (read from the booted config repository) cross-referenced against every `config('…')` / `Config::get('…')` literal in the repo's own PHP (`app/`, `routes/`, `config/`, `tests/`, `database/`, `bootstrap/`, `resources/`, `tools/`, `lang/`, `public/`). A key is live if a call names it or a prefix of it. 422 `config(` call sites exist in the app tree (`app/` 212, `tests/` 204, `database/` 5, `tools/` 1); `resources/js/` carries 7 more that are not Laravel config reads.

| Config file | Declared | Live | Dead |
|---|---|---|---|
| `config/scenarios.php` | 1006 | 1006 | 0 |
| `config/reference.php` | 202 | 202 | 0 |
| `config/uma.php` | 105 | 105 | 0 |
| `config/advisor.php` | 18 | 18 | 0 |
| `config/database-safety.php` | 12 | 12 | 0 |
| `config/mysql-backup.php` | 7 | 7 | 0 |
| `config/dashboard.php` | 1 | 1 | 0 |
| **app-owned subtotal** | **1351** | **1351** | **0** |
| `config/database.php` | 116 | 90 | 26 |
| `config/app.php` | 88 | 27 | 61 |
| `config/logging.php` | 57 | 0 | 57 |
| `config/cache.php` | 39 | 1 | 38 |
| `config/queue.php` | 38 | 0 | 38 |
| `config/mail.php` | 31 | 0 | 31 |
| `config/filesystems.php` | 23 | 0 | 23 |
| `config/session.php` | 18 | 0 | 18 |
| `config/auth.php` | 11 | 0 | 11 |
| `config/services.php` | 7 | 0 | 7 |
| **framework subtotal** | **428** | **118** | **310** |
| **total** | **1779** | **1469** | **310** |

### Dead keys, named

**App-owned: none.** Every one of the 1,351 keys under the seven repo-authored roots is reachable from the repo's own PHP. `config/scenarios.php` is the largest single file (1,006 keys) and `config/uma.php` the second (105); both are fully read. The thinnest is `config/mysql-backup.php` (7 keys), read only by `config/database.php:90-96` and `tests/Feature/MariaDbBackupTest.php` — no `app/` code reads it directly.

**Framework skeleton: 310 keys with no reader in the repo's own PHP.** These are Laravel defaults; the "dead" label is the instrument's, not a claim that nothing reads them. Every framework root is consumed by `vendor/laravel/framework`, statically (`config('app.name')`, `config('app.debug')`, `config('app.timezone')`, `config('app.locale')`, `config('session.driver')`, `config('auth.defaults.passwords')`, `config('cache.default')`, `config('logging.default')`, `config('mail.default')`, `config('queue.default')`, `config('database.migrations')`, `config('database.redis')` all appear in `vendor/laravel/framework/src`) and dynamically by name, which a static per-leaf sweep cannot see: `LogManager.php:603` reads `logging.channels.{$name}`, `CacheManager.php:485` reads `cache.stores.{$name}`, `QueueManager.php:415` reads `queue.connections.{$name}`.

The 310, by root:

- **`app` (61)** — `env`, `debug`, `url`, `frontend_url`, `asset_url`, `timezone`, `locale`, `fallback_locale`, `faker_locale`, `cipher`, `key`, `previous_keys`, `maintenance.driver`, `maintenance.store`, and the 47 `app.aliases.*` entries (`App` through `Vite`).
- **`logging` (57)** — every `logging.channels.*` leaf (`stack`, `single`, `daily`, `monthly`, `slack`, `papertrail`, `stderr`, `syslog`, `errorlog`, `null`, `emergency`, and the repo-added `skill_automation` and `browser`), plus `logging.default` and `logging.deprecations.channel` / `.trace`.
- **`cache` (38)** — every `cache.stores.*` leaf except `cache.stores.database.connection` (read by `tests/Feature/CatalogCacheRenderTest.php`): `array`, `session`, `database`, `file`, `storage`, `memcached`, `redis`, `dynamodb`, `octane`, `failover` — plus `cache.prefix` and `cache.serializable_classes`.
- **`queue` (38)** — every `queue.connections.*` leaf (`sync`, `database`, `beanstalkd`, `sqs`, `redis`, `deferred`, `failover`, `background`), plus `queue.batching.*`, `queue.failed.*`, `queue.default`.
- **`mail` (31)** — every `mail.mailers.*` leaf, `mail.from.address` / `.name`, `mail.markdown.*`, `mail.default`.
- **`database` (26)** — the 25 `database.redis.*` leaves (`client`, `options.*`, `default.*`, `cache.*`) and `database.migrations.table` / `database.migrations.update_date_on_publish`.
- **`filesystems` (23)** — every `filesystems.disks.*` leaf (`local`, `public`, `s3`), `filesystems.default`, and one key that is an absolute path (`filesystems.links.<public_path>/storage`), an artifact of `public_path('storage')` used as an array key.
- **`session` (18)** — `driver`, `lifetime`, `expire_on_close`, `encrypt`, `files`, `connection`, `table`, `store`, `lottery.*`, `cookie`, `path`, `domain`, `secure`, `http_only`, `same_site`, `partitioned`, `serialization`.
- **`auth` (11)** — `defaults.guard`, `defaults.passwords`, `guards.web.driver` / `.provider`, `providers.users.driver` / `.model`, `passwords.users.*`, `password_timeout`.
- **`services` (7)** — `postmark.key`, `resend.key`, `ses.key` / `.secret` / `.region`, `slack.notifications.bot_user_oauth_token` / `.channel`.

### `env()` outside `config/`

One match in the whole app tree, and it is a comment:

`tools/debug/test-final-analysis.php:10` — `// The config uses: env('DB_DATABASE', database_path('database.sqlite'))`

No real `env()` call exists outside `config/`: `app/`, `routes/`, `bootstrap/`, `resources/`, `database/`, `lang/`, `public/` and `tests/` are all clean. `AGENTS.md` §7 holds.

### `config()` inside config files

Nine matches, but only seven are calls:

| Site | Read | Kind |
|---|---|---|
| `config/database.php:90-96` | `config('mysql-backup.host')` … `.collation` (7 reads) | call — the `mysql_backup` connection mirrors `config/mysql-backup.php` |
| `config/scenarios.php:48` | `config('advisor')` | comment — prose in a doc block, not a call |
| `config/uma.php:380` | `config('uma.sources')` | comment — prose in a doc block, not a call |

The only real coupling is `config/database.php` reading `config/mysql-backup.php` for its `mysql_backup` connection. Reported, not judged: one config file reading another's values is the documented Laravel pattern.

### What §3.6 does not cover

Whether any dead key *should* be removed (a slice-level change, not an audit finding), whether the MariaDB config file's contents should be documented or deleted (owner item), and the framework roots' true read-set inside `vendor/` — the instrument scans the repo's own PHP only, so the 310 framework-root keys are reported as unreferenced by this repo, not as unread anywhere.

## 11. §3.10 — lang/en dead and missing keys, 2026-10-17

Measured at HEAD `e820cdd`, static, read-only.

### Locale directory census

| Question | Answer |
|---|---|
| Locale directories under `lang/` | `en` |
| Total PHP files under `lang/` | 1 |
| Files under `lang/en/` | 1 (`lang/en/uma.php`, 130 lines) |
| Any non-`en` locale | none |

Single-locale tree. `lang/en/uma.php` is the only lang file; no `ja`, no `jp`, no vendor-published lang.

### Per-file key counts

| Lang file | Keys declared | Keys read | Dead keys |
|---|---|---|---|
| `lang/en/uma.php` | 49 | 27 | 22 |

49 declared leaves: 27 enum-display labels across 8 groups (`release_status` 3, `run_status` 3, `race_entry_status` 4, `skill_acquisition` 3, `alias_language` 3, `match_tier` 4, `card_rarity` 3, `build_purpose` 4) plus a `terms` reference group of 22.

**Reading convention.** The only lang reader in the tree is `HasLabel::label()` (`app/Enums/HasLabel.php:20`), which builds `uma.{Str::snake(class_basename)}.{CaseName}` and calls `trans()`. No `__()`, `trans()`, or `lang()` call anywhere names a lang key literally — every lang key in this tree is reached dynamically through that trait. Nine enums use `HasLabel`: `ReleaseStatus`, `RunStatus`, `RaceEntryStatus`, `SkillAcquisition`, `AliasLanguage`, `MatchTier`, `CardRarity`, `BuildPurpose`, `TraineeGoalType`.

### Dead keys (declared, never read) — 22

All 22 are the `terms` reference group. None is read: the only mention of `uma.terms.*` in the entire tree is a `ponytail:` comment at `Umamusume.php:133` — *"display strings do not belong on a model; read these labels from `uma.terms.style_*`"* — a deferred-slice note, not a read. The group is documented as reference data deliberately not wired into any view (`lang/en/uma.php:78-84`: "this tool has no Scouts, Spark or running-style surface in Phase 1 (PRD §6), so nothing here is wired into a view").

| Key | Value |
|---|---|
| `uma.terms.scouts` | Scouts |
| `uma.terms.pull_currency` | Carats |
| `uma.terms.inheritance_trait` | Spark |
| `uma.terms.finished_character` | Veteran Umamusume |
| `uma.terms.trainee` | Trainee Umamusume |
| `uma.terms.team_pvp` | Team Trials |
| `uma.terms.card_speed` | Speed |
| `uma.terms.card_stamina` | Stamina |
| `uma.terms.card_power` | Power |
| `uma.terms.card_guts` | Guts |
| `uma.terms.card_wit` | Wit |
| `uma.terms.card_group` | Group |
| `uma.terms.card_pal` | Pal |
| `uma.terms.style_front_runner` | Front Runner |
| `uma.terms.style_pace_chaser` | Pace Chaser |
| `uma.terms.style_late_surger` | Late Surger |
| `uma.terms.style_end_closer` | End Closer |
| `uma.terms.mood_great` | GREAT |
| `uma.terms.mood_good` | GOOD |
| `uma.terms.mood_normal` | NORMAL |
| `uma.terms.mood_bad` | BAD |
| `uma.terms.mood_awful` | AWFUL |

These 22 are a data reference, not dead config in the §3.6 sense: they are source-cited client vocabulary staged for a surface PRD §6 has not cut. They are reported, not judged.

### Missing keys (called but not declared) — 5

| Enum | Cases | Labeled | Missing |
|---|---|---|---|
| `ReleaseStatus` | 3 | 3 | 0 |
| `RunStatus` | 3 | 3 | 0 |
| `RaceEntryStatus` | 4 | 4 | 0 |
| `SkillAcquisition` | 3 | 3 | 0 |
| `AliasLanguage` | 3 | 3 | 0 |
| `MatchTier` | 4 | 4 | 0 |
| `CardRarity` | 3 | 3 | 0 |
| `BuildPurpose` | 4 | 4 | 0 |
| **`TraineeGoalType`** | **5** | **0** | **5** |

`TraineeGoalType` uses `HasLabel`, so `HasLabel::label()` builds `uma.trainee_goal_type.{name}` and calls `trans()` — but `lang/en/uma.php` declares no `trainee_goal_type` group. The five keys below are called every time a `TraineeGoalType` case is labelled and resolve to themselves:

- `uma.trainee_goal_type.Placement`
- `uma.trainee_goal_type.Participate`
- `uma.trainee_goal_type.Fan`
- `uma.trainee_goal_type.FinalRound`
- `uma.trainee_goal_type.Undecoded`

**This one is not silent.** `HasLabel::label()` falls back to `Str::headline($this->name)` when `trans()` returns the key unchanged (`HasLabel.php:22`), so the page prints `Placement`, `Participate`, `Fan`, `Final Round`, `Undecoded` — headline-cased raw case names — rather than the key. The failure is a missing group, not a missing mechanism; no other enum in the tree hits it.

### Enum label coverage — non-HasLabel enums

Seven further enums define their own `label()` and do not read `lang/`: `CareerYear` (hardcoded `match`: "Junior Year", "Classic Year", "Senior Year"), `TurnChoice`, `PerformanceType` (returns `$this->value` verbatim — the client's own word), `SnapshotFieldState`, `EnergyState`, `SpiritBurstState`. `MoodTier`, `CareerPhase`, `RunMode`, `TurnEventType` and `CandidateStatus` have no `label()` at all. None of these ten touches `lang/en/uma.php`, so they contribute no key reads and no missing keys.

### What §3.10 does not cover

Whether the 22 `uma.terms.*` keys should be removed, moved, or wired to a surface (a slice-level change and, for `terms`, a PRD §6 scope question, not an audit finding). Whether a second locale should exist (a product decision). Whether the 5 `trainee_goal_type` labels should be added, and what wording they should carry (the `build_purpose` precedent at `lang/en/uma.php:63-65` shows the wording is a design decision, not a mechanical transform). This section also does not judge `Umamusume.php:133`'s deferred `ponytail:` note.
## 12. §3.4 — data pipeline, 2026-10-17

Measured at HEAD `e820cdd`, static analysis only. No pipeline command was executed: no `uma:fetch`, no `uma:reparse`, no migration, no network.

### Directory census

| Question | Answer |
|---|---|
| Files under `app/Services/DataPipeline/` | 23 |
| Total lines | 3,138 |
| Parsers | 9 |
| Contracts | 9 |
| Subdirectories | `Contracts/`, `Parsers/` |

Five top-level classes — `PipelineRunner.php`, `SourceFetcher.php`, `CrossReferenceMatcher.php`, `NameNormalizer.php`, `ArtworkMirror.php` — and 9 contracts (`SourceParser` plus eight kind-specific siblings: `RaceCatalogSourceParser`, `SkillSourceParser`, `CharacterCardSourceParser`, `ProfileSourceParser`, `SupportCardSourceParser`, `SupportEffectSourceParser`, `TraineeGoalSourceParser`, `ScenarioSourceParser`).

### Source-to-parser-to-command map

Nine source entries in `config('uma.sources')`.

| Source key | Parser | Command | `seed_file` | Pinned URL / manifest |
|---|---|---|---|---|
| `gametora-characters` | `GametoraCharacterParser` | `uma:fetch` / `uma:reparse` | `gametora-characters.e9e9ee6d.json` | pinned |
| `gametora-character-cards` | `GametoraCharacterCardParser` | `uma:fetch` / `uma:reparse` | `gametora-characters.e9e9ee6d.json` (same body, other grain) | pinned |
| `gametora-race-catalog` | `GametoraRaceCatalogParser` | `uma:fetch` / `uma:reparse` | `race_instances.json` | pinned + manifest `race_instances` |
| `gametora-skills` | `GametoraSkillsParser` | `uma:fetch` / `uma:reparse` | `skills.609afe88.json` | pinned + manifest |
| `gametora-character-profiles` | `GametoraCharacterProfileParser` | `uma:fetch` / `uma:reparse` | `characters.c6676539.json` | pinned + manifest |
| `gametora-support-cards` | `GametoraSupportCardParser` | `uma:fetch` / `uma:reparse` | `support-cards.88dea522.json` | pinned + manifest |
| `gametora-support-effects` | `GametoraSupportEffectParser` | `uma:fetch` / `uma:reparse` | `support_effects.ca447e53.json` | pinned + manifest |
| `gametora-ura-objectives` | `GametoraUraObjectivesParser` | `uma:fetch` / `uma:reparse` | `ura-objectives.ead4bbfe.json` | pinned |
| `gametora-artwork` | **(none — by design)** | `uma:fetch-art` | none | `https://media.gametora.com/umamusume/` |

Mismatches found:

- **One source entry with no parser: `gametora-artwork`.** This is the documented `ADR-0021` pattern, not a gap: the asset host stays allowlisted without being parsed, and `uma:fetch` steps over any source entry that declares no parser. `config/uma.php` and `AGENTS.md` §8 both state it.
- **One parser on disk with no source entry: `GametoraScenarioParser`** (implements `ScenarioSourceParser`). It is not wired to any `config('uma.sources')` entry, and its contract `ScenarioSourceParser` is implemented by nothing else. It is **not dead**: two tests exercise it — `GametoraScenarioParserTest.php` (parses a stored body) and `ScenarioCapParityTest.php` (pins `GametoraScenarioParser::BASE_STAT_CAP` against `config('scenarios.base_cap')`, the "two owners agree" mechanism named in its docblock). The scenario capability is carried by the `scenarios` config source rather than by a fetch entry.

No other mismatch: every parser declared on disk is referenced by a source entry, and every source entry that declares a parser names a class that exists.

### Fetcher allowlist verification

| Question | Answer |
|---|---|
| `SourceFetcher` call sites | 3 (app) — `UmaFetch.php:23,82`, `ArtworkMirror.php:35,151`, and `config/uma.php` |
| Hosts allowlisted | 2 — `gametora.com` (20 references across source urls, manifests, bases), `media.gametora.com` (1) |
| Any outbound HTTP path bypassing `SourceFetcher` | **none** |

The SSRF floor holds. `Http::withHeaders(...)` at `SourceFetcher.php:333` is the only outbound HTTP call in `app/`. Every other I/O call is a local `file_get_contents` — a committed seed body (`ReadsCommittedSource.php:54`, `ScenarioSlotSeeder.php:203`, `UmaImportSupportCards.php:99`, `tools/roster-crosscheck.php`), a fetched snapshot (`HandleInertiaRequests.php:82`), or an uploaded CSV (`ImportHistoricalRunRequest.php:83`). No `GuzzleHttp\Client`, no `curl_`, no raw `new Client`, no `fopen()` to a URL. `ArtworkMirror` reaches the network only through the injected `SourceFetcher` (`ArtworkMirror.php:35,151`).

### Pipeline stage trace

| Stage | Owner | Call sites |
|---|---|---|
| fetch | `SourceFetcher::fetch()` / `fetchAsset()` | `UmaFetch.php:82`, `ArtworkMirror.php:151` |
| snapshot | `SourceFetcher` (`:66-70`, inside fetch) | same two |
| parse | parser class chosen by `is_a()` in `PipelineRunner::run()` | `UmaFetch.php`, `UmaReparse.php` |
| normalize | `NameNormalizer` | 12 app refs (`PromoteMatchedRecord`, `StoreSkills`, `CatalogController`, API controller, seeders) |
| match | `CrossReferenceMatcher` | 3 app (`PipelineRunner`, `MatchCandidate`, `UmamusumeRosterSeeder`) |
| promote | `PipelineRunner::run()` → `Store*` / `PromoteMatchedRecord` | the 7 `is_a()` branches + fall-through loop |
| review | `MatchCandidate::updateOrCreate` in `PipelineRunner::run()` | fall-through loop only |

`PipelineRunner::run()` (`PipelineRunner.php:54`) holds the whole back half: seven `is_a()` branches (`RaceCatalogSourceParser:71`, `TraineeGoalSourceParser:97`, `SkillSourceParser:128`, `CharacterCardSourceParser:157`, `ProfileSourceParser:198`, `SupportCardSourceParser:227`, `SupportEffectSourceParser:242`) route reference rows past the match stage to their own writer, and the fall-through loop at `:255-302` matches on `record['name']`, promotes on `MatchTier::Exact`/`Alias`, and files everything else into `match_candidates` for review. Both entry points — `uma:fetch` (network) and `uma:reparse` (snapshot replay) — arrive through this one method. Every stage's data flow is traceable end to end.

### Promote path checks

Eight promote-path actions: `PromoteMatchedRecord`, `StoreCharacterCards`, `StoreCharacterProfiles`, `StoreRaceCatalogSlots`, `StoreSkills`, `StoreSupportCards`, `StoreSupportEffects`, `StoreTraineeGoals`.

| Question | Answer |
|---|---|
| Store actions | 8 (7 `Store*` + `PromoteMatchedRecord`) |
| Any action that does not check `is_manual` | 0 of 8 — with one schema-qualified exception below |
| Any action that does not write a `data_sources` row | 0 |
| Any action that does not run inside `DB::transaction` | 0 |

**Both Floor rules hold, with one stated exception.** Seven of the eight actions carry a real runtime `is_manual` guard — `StoreCharacterCards.php:46` selects `['id', 'is_manual']` and `:74-75` skips a manual row, incrementing `skipped`; the other six follow the same shape. The eighth, `StoreSupportEffects`, cannot: `support_effects` has no `is_manual` column (neither `2026_09_30_142618` nor `2026_09_30_151945` adds one), and the action's own docblock states it — *"This table cannot honour the `is_manual` stop sign … inventing a guard for a column that does not exist would be a stub, and adding the column is a schema change owned by the Architect"* (`StoreSupportEffects.php:19-23`). That is a recorded schema gap, not an unrecorded violation. **No Floor breach.**

### Orphan methods

No orphan class in the pipeline. Every top-level class has both application and test references.

| Class | app refs | test refs |
|---|---|---|
| `SourceFetcher` | 3 (`UmaFetch`, `ArtworkMirror`, `config/uma.php`) | 5 |
| `PipelineRunner` | 11 (`UmaFetch`, `UmaReparse`, `UmaImportSupportCards`, `CatalogController`, …) | 8 |
| `NameNormalizer` | 12 | 5 |
| `CrossReferenceMatcher` | 3 | 1 (`CrossReferenceMatcherTest`) |
| `ArtworkMirror` | 10 | 5 |

### Snapshot path verification

| Question | Answer |
|---|---|
| Snapshot directory path | `storage/app/private/snapshots/{sourceKey}/{sha256}.html` (`SourceFetcher.php:66-70`) |
| Files on disk | 6 |
| Snapshot hash algorithm | **sha256** — `hash('sha256', $body)` (`SourceFetcher.php:66`) |
| Any code path writing outside the gitignored location | none |

Gitignored by `storage/app/private/.gitignore:1` (`*`). All six artifacts sit under that tree; no snapshot writer resolves a path outside it. `ArtworkMirror` writes artwork under a sibling private path (`storage/app/private/artwork/`), also gitignored, and reaches the network only through `SourceFetcher`.

### What §3.4 does not cover

Whether any source should be added or removed, and whether `gametora-artwork` or `GametoraScenarioParser` should be wired or deleted (product decisions). Whether a parser's output is *correct* against its upstream document — this pass traced structure, not data (a parser-correctness sweep is slice work with fixtures). The fetch-source legality question stays with `AGENTS.md` escalation 5: the `gametora.com` robots.txt and rate-limit question is recorded as formally unanswered in `config/uma.php`'s own comment, and this pass does not settle it. Nor does it re-verify the seed-file bodies themselves, or exercise `uma:fetch` / `uma:reparse` end to end — the trace is static.
## 13. §3.7 — quality bar's gate inventory, 2026-10-17

Measured at HEAD `608aff3`. **No gate was executed.** This pass inventories; the last-run proof of any gate is a commit message, not a fresh execution, and the tree has no CI (`.github/workflows/` absent), so every "last run" reading below is freshness, not pass/fail.

### The gate sources

| Source | Gates named | Range |
|---|---|---|
| `docs/research-scratch/GOVERNANCE.md` §GATE-REGISTRY.md | 10 (C-1..C-8, G-60, G-61) | C-1 to C-8, G-60, G-61 |
| `AGENTS.md` §9 hand-off sequence | 7 steps | test, pint, phpstan, typecheck, lore ×2, audit |
| `docs/research-scratch/DESIGN-CORPUS.md` G table | 70 rows | G-1 .. G-59 plus lettered variants (G-15a, G-16b, G-17c …) |
| `composer.json` scripts | 18 | setup, dev, test, lore, lore-code, docs, docs-adr, lint, fix, typecheck, analyse, + 7 framework hooks |
| `package.json` scripts | 5 | build, dev, typecheck, test:a11y, test:browser |

The G-series is layered, not duplicated. `GOVERNANCE.md:463-465` states the split: the folded research corpus owns the D-number design rules and the prototype gate series, and the registry "does not restate them; it records where they run and how they bind the shipped app." `AGENTS.md` names **zero** G-numbers — it carries the C-series and the executable sequence only.

**The 70 G-rows are a prototype review checklist, not a runnable gate table.** Every row's "how to check" column is a manual instruction in the imperative — `render the same run header in all four Global scenarios` (G-33), `grep artifacts sourced from wiki tables for Wisdom and Motivation` (G-38), `measure the computed font-family of body, a heading and a numeral` (G-21), `for each screen, read the §10n row matching its scenario` (G-40). None names a command. Eight are executed by `tools/gate.py`; the rest are reviewer-judged, which is the registry's stated design rather than a gap in it.

### Documented vs executable

| Gate | Documented in | Executable | Command |
|---|---|---|---|
| C-1 Tests | registry, `AGENTS.md` §9 | yes | `php artisan test --compact` |
| C-2 Static analysis | registry, `AGENTS.md` §9 | yes | `vendor/bin/phpstan analyse --no-progress --memory-limit=1G` |
| C-3 Style | registry, `AGENTS.md` §9 | yes | `vendor/bin/pint --test --format agent <paths>` |
| C-4 Lore | registry, `AGENTS.md` §5, §9 | yes | `composer lore`, `composer lore-code` → `tools/lore.php` |
| C-5 Migrations | registry | manual by approval | `php artisan migrate`; §11 forbids it against the shared dev file |
| C-6 Floor | registry | grep + diff review | `git grep`; no script, by design |
| C-7 UI states | registry | reviewer enumeration | none; interpretive per ADR-0007 |
| C-8 Dependencies | registry, `AGENTS.md` §5 | yes | `composer audit`, `npm audit --omit=dev` |
| G-60 Retired literals | registry | **partial — registry says so** | reviewer grep; scanner pending (`:590`) |
| G-61 ADR contract | registry | yes | `python tools/adr-check.py docs/adr` / `composer docs-adr` |
| G-1, 2, 3, 4, 13, 16, 27, 31, 32 | G table | yes | `tools/gate.py` |
| G-18 Contrast | G table | yes | `tests/Feature/DesignTokensTest.php` (browser) |
| Remaining 59 G-rows | G table | **reviewer-judged** | no command exists; the row *is* the check |

**Documented gates with no executable:**

- **59 of the 70 G-rows are reviewer-executed.** Their subject is stated and their pass criterion is stated; only a runner is absent, and the table's design expects that — it is a prototype review sheet. G-18 is the one G-row promoted to an automated test.
- **G-60's scanner is pending.** The registry's own Gaps table (`:590`) records "G-60 scanner does not yet read a retired-value registry file — G-60 currently reviewer-enforced." Stated, not hidden.
- **C-6 and C-7** are non-automatable by design, as their registry rows declare. Neither is a gap.

**Executable checks with no documented gate:**

| Executable | Documented? |
|---|---|
| `tools/adr-check.py` / `composer docs-adr` | yes — G-61 |
| `tools/doc_census.py` / `composer docs` | **no gate id** — `AGENTS.md` §10 names the command; no C- or G-number claims it |
| `tools/md_fence_check.py` | **no gate id** — `AGENTS.md` §10 names it |
| `tools/roster-crosscheck.php` | **no gate id** — listed in `AGENTS.md` §6 |
| `tools/check_untracked.py` | **no gate id** — listed in `AGENTS.md` §6 |
| `tools/gate-preflight.php` | **no gate id** — untracked at HEAD; not in `AGENTS.md` |
| `tools/dev-logs-pane.php` | no — runtime dev tool, not a gate |
| `npm run test:a11y` | **no gate id** — `AGENTS.md` §9 names browser gates but not this script |
| `npm run test:browser` | partly — named under the per-session harness rule (KI-73, KI-80) |

**Six tracked executables enforce rules no gate id claims.** This is the `pint --dirty` shape: the executable bar outruns the documented bar.

### `tools/gate.py` banner under-reporting — confirmed at HEAD

`DESIGN-CORPUS.md`'s note that the banner omits G-27, G-31 and G-32 is **confirmed**. The checks exist; the banner does not name them.

- `gate.py:270-271` — *"Checked: G-1 lore, G-2 forms, G-3 terminology, G-4 tokens, G-13 rendered text, G-16 sample data, D-79 em dash, D-84 emoji."*
- `gate.py:272` — *"Not machine-checkable (reviewer): G-5, G-6, G-7, G-9, G-11, G-12, G-14, G-15, G-17."*
- **G-27, G-31, G-32 appear in neither list** while being enforced at `gate.py:211-215` (G-31 run length, G-32 cap value) and `gate.py:242-261` (G-27 inline-script syntax, which self-skips with a printed note when `node` is off PATH at `:256`).

A reader taking the banner as the coverage statement under-counts the scanner by three gates. This is the registry's own Gaps-table failure mode, occurring inside the gate rather than beside it.

### Gate coverage by code path

| Gate | Subject | Fires on | Skips |
|---|---|---|---|
| C-2 PHPStan | `app` only (`phpstan.neon` `paths`) | `app/**` | `tests/`, `database/`, `tools/` |
| Typecheck | `resources/js/**/*.ts`, `tests/browser/**/*.ts`, `tests/utils/**/*.ts` (`tsconfig.json` `include`) | all three | nothing in the TS tree excluded |
| C-3 Pint | files named on the command line | explicit `<paths>` | `--dirty` banned (KI-75); no whole-tree form in the bar |
| C-4 Lore | app paths + `docs/` markers | `tools/lore.php`, both modes | `tools/` outside `lore-code`'s path list |
| G-18 | the two source trees | `DesignTokensTest.php` | skipped without the Playwright driver (`markTestSkipped`) |

**KI-93 is closed.** `tsconfig.json` includes `tests/browser/**/*.ts` and `tests/utils/**/*.ts`; the invisible-`tests/` exclusion is no longer present. Measured from the file, not from the issue's status line.

Two gaps stay visible in the configs themselves. PHPStan reads `app` alone, so `database/migrations/`, `tools/` and `database/seeders/` are outside C-2's subject. And Pint has no whole-tree form in the bar, so PHP outside a hand-off's named paths is ungated by construction — which is what KI-75 had to correct in prose.

### Gate freshness (last-touching commit)

| Gate file | Last touched |
|---|---|
| `tools/gate.py` | `a3f0795` — "point design gate at corpus master and align three prototypes' shipped tokens" |
| `tools/lore.php` | `13b2708` — "drop exclusion deleted file, and mark lines protecting" |
| `tools/adr-check.py` | `1baa824` — "add contract/index/check machinery and `CURRENT.md`" |
| `tests/Feature/DesignTokensTest.php` | `04bb402` — "Preserve stranded concurrent-session work before merging cockpit-wt" |
| `phpstan.neon` | `fda6ff0` — "initialize Laravel 13 application skeleton" |
| `pint.json` | `fda6ff0` — "initialize Laravel 13 application skeleton" |

Both static-analysis configs are untouched since the skeleton commit while the runners have moved — the expected shape: a threshold config should be stable, a scanner should not be.

### What §3.7 does not cover

Whether any reviewer-executed gate (the 59 G-rows, C-6, C-7, G-60's pending scanner) should be built, automated, or retired — an owner decision, not an audit finding. Whether a gate's pass threshold is correct — a QA ruling; the bar forbids relaxing one to make a check pass. Whether the browser suite's per-session harness (`PLAYWRIGHT_PORT`, `PLAYWRIGHT_SCRATCH_DB`, KI-73 and KI-80) is the right shape — a tooling decision. And whether any gate passes: no gate was executed, so this section is the surface, not the verdict.