# 00 — Coverage

Observation surface: **Playwright API 1.63.0 driven directly from Node v26.12.1** (`chromium.launch({headless:true})`,
script-only: navigate, resize, screenshot, read the DOM/accessibility tree/computed styles). No `*.spec.ts` and no
assertion suite was written or run. The Playwright **MCP** server was attempted first and its stdio transport had
exited; the direct API is the same observation surface without a test file.
Viewport mechanism: **real window resize** (`page.setViewportSize`), not device emulation. 1440x900 and 390x844.
Chrome that the owner already had open was **not** used for colour work: it runs Dark Reader, which would have
falsified every palette reading. Clean headless Chromium was the instrument.

Walk date: 2026-11-14. HEAD at walk: `d8238c87`. Theme at start: **light** (default); `dark` tested and reverted to light.

## Preconditions — measured, with the failures stated

| # | Precondition | Result |
|---|---|---|
| 1 | `127.0.0.1:8000` renders | **PASS.** `document.title` = `Dashboard`; `h1` = `Trainer Desk`; 0 console errors. |
| 2 | Record the app's data state | **PASS, and it changes the plan.** Careers **0**, Veterans **0**, pending review **304**, support cards **559**, trainees **139** rows (72 shown as Global) . Every career-workspace route in the coverage list was therefore unreachable until a run existed. |
| 3 | Note the scenario(s) in play | **PASS after creation.** No career existed, so none could be read. The run created for this walk is **Unity Cup**. |
| 4 | Note theme; test alternates | **PASS.** Started light. `light`, `dark`, `eva01` exist; `dark` selected, **saved** (`All changes saved`), measured, reverted. `eva01` not measured (see misses). |
| 5 | Confirm no peer work in the tree | **FAIL — the walk proceeded anyway, with the exposure recorded.** `test-results/` gained 31 files during the walk (newest 0.4 min old) and a peer `playwright test` (PID 28884/31452) plus a second worktree (`09c1fc73`, `.kilo/worktrees/cherry-pet`, running Pest) were live. **No file under `app/`, `resources/js/`, `routes/`, `config/`, `resources/css/` or `public/build/` changed during the walk (0 files in 8 min, twice sampled)**, so the rendered output was stable even though the tree was not quiet. |

**Precondition 2 was the audit's own documentation gap, now corrected.** The walk recorded "0 careers,
0 veterans" as a fixed fact. It is a *state*, and three committed seeders populate it —
`RosyDreamsRiceShowerSeeder` (one mid-career Unity Cup run), `UraFinaleRunsSeeder` (eight runs, all
`Completed`), `TrackerVeteransSeeder` (seven careers filed as Completed + a Veterans row). **None of the three is
called by `DatabaseSeeder`**, so `php artisan migrate:fresh --seed` does not produce them; each needs an explicit
`--class=` invocation. The audit could not seed (that is application logic), but it should have named seeding as
the owner action that closes these rows instead of presenting the empty DB as unreachable ground. Full detail and
the invocation order are in `04-excluded-findings.md` EX-10.

The revised precondition for the next pass is an owner choice, not an audit improvisation:

- **Option A** — owner seeds the populated set before the walk; the walk starts on data.
- **Option B** — owner confirms empty and the walk starts empty, with seeder-only screens marked
  unreachable-by-state.
- **Either way: walk both states.** A populated career hides the empty-state defects (BO-002's null panel read
  only fires on a turn-less run) and an empty career hides the populated-panel ones. This pass is Option B by
  force, and its finding set should be read as "the empty-state half".

Two preconditions that the brief assumed and the tree contradicts:

- The brief cites HEAD **`67a4830`**. The walk ran at **`d8238c87`**, which is **308 commits ahead**. `routes/web.php` changed across that gap (`6bbca89b`, the transfer-request feature), so the brief's route enumeration is a claim, not a list. It was re-derived by crawling rendered links.
- `public/build/manifest.json` is 91 minutes old and `public/hot` is absent, so the walk measured **built assets**, not a stale dev-server bundle.

## Counts at start (baseline used by every finding)

| Surface | Observed |
|---|---|
| Dashboard careers | `1` (after the walk's run was created); `0` at entry |
| `/training-runs` | 1 row (run #13) |
| `/veterans` | `0` — "The library holds no Veterans yet." |
| `/review` | `304 pending` |
| `/support-cards` | `559 catalogued` / `251 cards on [Global]` |
| `/umamusume` | `72 trainees` shown of `72` |

## Screens walked

Every row was captured at **both** viewports unless noted. `clean` means no finding was filed against it.

| Screen | Route | Verdict |
|---|---|---|
| Dashboard | `/` | **2 findings** (BO-006 attention count, BO-008 count wording) |
| Careers list | `/training-runs` | **1 finding** (BO-009 `N/A :` punctuation) |
| Create career (legacy form) | `/training-runs/create` | clean |
| CSV import | `/training-runs/import` | clean (preview step not reached — see misses) |
| Veterans index | `/veterans` | clean (good empty state) |
| Veterans compare | `/veterans/compare` | clean (good empty state) |
| Legacy Lab index | `/legacy` | clean |
| Legacy compare | `/legacy/compare` | clean |
| Review queue | `/review` | clean |
| Candidate detail | `/review/10` | **1 finding** (BO-011 focus-ring inconsistency) |
| Preferences | `/preferences` | **1 finding** (BO-004 dark `To do` contrast) |
| Help | `/help` | clean |
| Wizard · Scenario | `/career/setup/scenario` | clean |
| Wizard · Trainee | `/career/setup/trainee` | clean |
| Wizard · Trainee profile | `/career/setup/trainee/33` | clean |
| Wizard · Trainee profile (slug) | `/career/setup/trainee/agnes-digital` | **1 finding** (BO-001 dead door, 404) |
| Wizard · Build target | `/career/setup/target` | **1 finding** (BO-010 inline error placement OK, but 3 required selects unmarked) |
| Wizard · Legacy | `/career/setup/legacy` | clean |
| Wizard · Deck | `/career/setup/deck` | clean |
| Wizard · Preflight | `/career/setup/preflight` | **1 finding** (BO-006) |
| Snapshot entry | `/career/snapshot` | clean |
| Snapshot setup | `/career/snapshot/setup` | clean |
| Snapshot review | `/career/snapshot/review` | clean |
| Cockpit | `/training-runs/13/cockpit` | **2 findings** (BO-002 console error, BO-007 `floor` vs caps) |
| Training | `/training-runs/13/training` | clean |
| Races | `/training-runs/13/races` | clean |
| Race planner | `/training-runs/13/races/planner` | clean |
| Skills planner | `/training-runs/13/skills` | clean |
| Events | `/training-runs/13/events` | clean |
| Inheritance | `/training-runs/13/inheritance` | clean |
| Deck (run) | `/training-runs/13/deck` | clean |
| Timeline | `/training-runs/13/timeline` | **1 finding** (BO-005 turn-edit door not reachable — no turn exists) |
| Result | `/training-runs/13/result` | clean (correctly explains an active run has no result) |
| Run settings | `/training-runs/13/settings` | clean |
| Team | `/training-runs/13/team` | clean |
| Scenario detail | `/training-runs/13/scenario` | clean |
| Save Veteran | `/training-runs/13/veteran` | clean |
| Confirm delete | `/training-runs/13/delete` | clean |
| Retired run record | `/training-runs/13` | clean — **redirects to Cockpit** as the brief claims |
| Trainee catalogue | `/umamusume` | **1 finding** (BO-003 factory row) |
| Trainee detail | `/umamusume/agnes-digital` | clean |
| Trainee detail (factory row) | `/umamusume/aut-neque` | **instance of BO-003** |
| Skills catalogue | `/skills` | clean |
| Skill detail | `/skills/370?from=%2Fskills` | clean |
| Support cards | `/support-cards` | clean |
| Database hub | `/database` | clean |
| Database · Trainees | `/database/trainees` | clean |
| Database · Supports | `/database/supports` | clean |
| Database · Skills | `/database/skills` | clean |
| Database · Races | `/database/races` | clean |
| Race detail | `/database/races/1` | clean |
| Database · Scenarios | `/database/scenarios` | clean |
| Database · Events | `/database/events` | clean |
| Database · Shop items | `/database/shop-items` | clean |
| Database · Sparks | `/database/sparks` | clean |
| Database · Sources | `/database/sources` | clean |

**Walked: 56 route-visits across 43 distinct screens, at both viewports — 119 PNGs.**

## What I could not walk, and why

| Miss | Reason |
|---|---|
| Turn edit `/training-runs/{run}/turns/{turn}/edit` | **BO-005.** Run #13 has 0 turns, so the Timeline prints "no turn has been logged" and renders no edit door. The brief forbids typing the URL, so the route was recorded as unreachable-by-door rather than visited. |
| `eva01` theme | Out of the alternate-theme budget: `dark` was measured in full; `eva01` shares the same token surface and was not separately read. |
| CSV import preview step | The import page renders an upload control; producing a file to preview would have been a data write, not an observation. |
| Creating a second career in another scenario | One run was created to unblock the workspace. Walking URA/Trackblazer/Grand Concert career panels would need three more runs and was out of scope for one pass. |
| `/support-cards` compare panel | The page exposes no compare affordance on the index (a null result, recorded as BO-012 informational rather than a defect). |
| A completed career (Result with data) | Requires a run driven to completion; the run is Active by design. **Corrected:** this was a *state*, not a fixed fact — `UraFinaleRunsSeeder` carries eight `Completed` runs and `TrackerVeteransSeeder` files seven more. Neither runs under `migrate:fresh --seed`. See EX-10/EX-11. |
| `/veterans/{id}` detail | 0 veterans exist, so no id is reachable from any door. **Corrected:** `TrackerVeteransSeeder` creates the Veterans rows; it is an owner action, not an audit one. |
| A populated Active career (panels with data) | Run #13 has 0 turns, so every panel rendered its empty branch. `RosyDreamsRiceShowerSeeder` writes one mid-career Unity Cup run at turn 67 of 72 with caps, team tally, races and a six-card deck populated — the state that exercises the *filled* branch. Not seeded here (owner action, EX-10). |
| Cross-walk reconciliation against `KNOWN-ISSUES.md` | The register carries **uncommitted** local edits (+131 lines); reading it as the prior-finding census would have compared against peer work in progress. Recorded, not used to suppress findings. |

## Honest statement of method limits

- **The vision model was unavailable during this walk** (the auxiliary vision endpoint returned a `ChatCompletion`
  adapter error on every attempt, and the browser-driving MCP had also exited). Screenshots were captured for the
  owner, but **I did not eyeball them**. Every finding below is therefore anchored in a **measured value**
  (computed styles, computed contrast ratios, the accessibility tree, element geometry, console output, rendered
  text) rather than in a visual judgement. Aesthetic judgements that need eyes — "this card feels flat", "the
  density is oppressive" — are **not** filed, because I could not see them.
- This is a real limitation, not a formality. It means the palette, spacing, hierarchy and copy findings here are
  solid, and the pure-craft findings are missing.
