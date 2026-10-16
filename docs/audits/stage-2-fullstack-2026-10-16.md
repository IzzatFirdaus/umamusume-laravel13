# Stage 2 seed — fullstack 2.0: routes and controllers

Measured 2026-10-16 against `master` @ `6fea7dc` (live main repo `D:/Projects/umamusume-laravel13`).
**Read-only**: one script plus direct greps. Nothing fixed, nothing committed. This is the first two
sub-audits of the twelve the full audit deferred at §5 — **§3.5 routes and §3.1 controllers only**.

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
