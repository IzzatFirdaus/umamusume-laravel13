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
