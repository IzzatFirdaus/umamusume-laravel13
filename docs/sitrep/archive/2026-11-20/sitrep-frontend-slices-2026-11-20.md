# SITREP — frontend-only slice pass over `docs/plan/implementation-plan-2026-11-20.md`

**Session date:** 2026-11-20/21
**Baseline commit:** `39a41ace` (`feat(review): add entity filters and transaction-safe bulk Reject`)
**Working tree:** dirty by design — 89 changed/untracked entries, shared with a concurrent peer session
**Nothing in this session was staged or committed.**

This report follows the `docs/sitrep/` convention (AGENTS.md §3, §3a "Report" step). It owns no
roadmap ordering; it records progress on plan slices and what remains.

---

## 1. Objective

Execute the frontend-only improvements authorised by
`docs/plan/implementation-plan-2026-11-20.md` under a brief that permitted **only** presentation,
browser interaction and frontend verification files. Explicitly excluded: `app/**`, `database/**`,
`routes/**`, `config/**`, dependencies, release/CI, and the full Pest / full Playwright suites.

Owner-gated slices were to be left alone: **N3.16, N3.28, N3.40, N6.18**.

---

## 2. Starting state — the thing a resuming agent must know first

The tree was **not** clean at session start, and the plan's completion labels were stale.

- `git rev-parse HEAD` = `39a41ace`, but 57 files were already modified and 9 untracked.
- A **concurrent peer session was actively writing** the same plan's Phase 3/6 slices. Their
  uncommitted work included N3.1, N3.2, N3.3, N3.5, N3.12, N3.13, N3.19, N3.20, N3.21, N3.23, N3.24,
  N3.25, N3.26, N3.30, N3.32, N6.1, N6.3, N6.7, N6.8, N6.10, N6.13.
- Mtimes confirmed live writes throughout (e.g. `DeckSummary.vue` 02:26, `Preflight.vue` 21:24,
  `ShopItems.vue` 21:51). Peer process count reached 16 php/node at 04:12.

**Consequence:** every slice had to be claim-tested against the tree before touching it. Slices already
landed by the peer were left untouched, not duplicated.

---

## 3. Claim-test results — slices the plan lists as open but that were already satisfied

Measured, not assumed. These were **not** re-implemented:

| Slice | Finding | Evidence |
|---|---|---|
| **N3.9** | Refuted. Alarm Clock explanation appears once visibly, plus once inside a labelled `<details>`. Peer had already added `career-event-decision.spec.ts:193`. | `grep -n 'alarm clock'` on `EventDecision.vue` |
| **N3.12** | Satisfied. Pager hidden at one page (`v-if="pageCount > 1"`), phase grouping present. | `Timeline.vue:298-303`, `:314` |
| **N3.13** | Satisfied. `isDirty` present ×4 in `RunSettings.vue`. | `grep -c isDirty` |
| **N3.19** | Satisfied. `percent()` caps at 99.9 below equality; spec pins `100% of the ceiling`. | `BuildTarget.vue:238-255` |
| **N3.20** | Satisfied by peer. `errorFor(position)` added to `DeckSelect.vue`. | `git diff DeckSelect.vue` |
| **N3.21** | Satisfied by peer. `impliedTierIndex` drives both the option label and a "Current" mark. | `git diff DeckSummary.vue` |
| **N3.25** | Satisfied. `selectedHeadlineForm` returns `null` rather than falling back to `forms[0]`. | `TraineeSelect.vue:435-444` |

**Also refuted — a plan claim about shared primitives:** the brief and plan reference `buttonStyles`.
It does not exist at that path. The real module is `resources/js/components/Form/buttonStyles.ts`
(exporting `buttonClasses`). Its `destructive` variant is
`'border border-risk bg-raised px-4 text-risk hover:bg-panel'`, which N3.11 reuses by hand.

---

## 4. Verified completed work

Ten slices. All frontend-only; **no file under `app/`, `database/`, `routes/`, `config/` was touched by
this session.** In every case below, the modification is additive to presentation or a correction of
false copy.

### N3.6 — TrainingDetail absence dedupe
`resources/js/pages/Career/TrainingDetail.vue` (+30/−3)
- The payload sends the **same ~180-character absence sentence** for every discipline with no recorded
  facility level, so it printed five times down one column.
- Rows now render `AbsenceValue` compact (`N/A` + reason on disclosure); one `Note` carries the
  explanation for the whole group.
- **Trap hit:** the first version of the Note printed a visible bare `N/A` and failed
  `UiHygieneTest` ("Visible N/A without a reason"). Fixed by giving the marker a `title`.
- New spec: `career-training-detail.spec.ts` — `states the missing facility level once, not five times
  down the column (N3.6)`.

### N3.8 — SkillsPlanner learned/candidate separation **+ a factual defect**
`resources/js/pages/Career/SkillsPlanner.vue` (+68/−10)
- Learned skills were buried in a closed `<details>` ("Build target and recorded skills"). A
  `data-region="skills-learned-strip"` section now shows them above the purchase table, reading the
  server's own `groups[key='learned']` — no re-derivation.
- **Corrected false user-facing copy.** Two sentences claimed no hint discount is applied ("this build
  does not record hint levels"). `SkillsPlannerController::coverageSection()` prices every row at
  `HintDiscount::price($baseCost, $hintLevel)` when a level is recorded, and the controller's own
  comment says so. Both sentences rewritten to state the actual rule. **No spec pinned the false copy**
  (verified by grep), so this was a truth fix, not a test fix.

### N3.10 — InheritanceEvent legacy status + door
`resources/js/pages/Career/InheritanceEvent.vue` (+30/−1)
- `data-region="legacy-status"` card states whether a Legacy configuration exists for the run (read
  from the same `props.legacy` the savebar uses) and links to `/legacy`.
- Added `Link` to the `@inertiajs/vue3` import.

### N3.11 — Deck equip naming + Remove safeguard
`resources/js/components/support/SupportSlot.vue` (+67/−3), `CardPicker.vue` (+14/−3)
- Equip buttons name their destination: `Equip to {{ slotLabel }}`, matching the existing
  `Support/Builder.vue:535` convention.
- `Remove` demoted to `border-risk`/`text-risk` (no filled background — a destructive control must not
  out-shout a primary one) behind a **native `<dialog>`** confirmation using `showModal()` (focus trap,
  inert background, Escape), mirroring `RaceCard.vue`. `clearSlot()` posts immediately with no undo, so
  this was the least-guarded, most consequential control in the row.
- **Trap hit:** the first version moved the destination into `aria-label` to avoid breaking three
  `exact: true` locators. That was wrong twice over — `aria-label` overrides the accessible name, so it
  would still break them, and it left the visible text ambiguous. Final resolution: visible label grows,
  and the three locators in `career-deck-select.spec.ts` + one in `career-deck-summary.spec.ts` were
  updated to `/^Equip to /`.
- New spec: `career-deck-select.spec.ts` — `Remove asks before it empties a slot, and cancelling keeps
  the card (N3.11)`, asserting the blank is written **only** by the confirm button and that both
  `Keep the card` and Escape leave the slot intact.

### N3.14 — UnityCupEditor grouping, proxy help, dirty state
`resources/js/components/career/UnityCupEditor.vue` (+33/−1)
- Flat two-column grid regrouped into four `<fieldset>`s: Team identity / Season performance / Unity Cup
  counters / Team stat grades. Order, ids, names and the blank-posts-null rule unchanged.
- Spirit proxy rationale promoted from a source comment to visible help text (transcription, not a gauge).
- `isDirty` indicator added beside `Saved.`, same shape as `RunSettings.vue` (N3.13).

### N3.15 — ScenarioDetail overview strip
`resources/js/pages/Career/ScenarioDetail.vue` (+60/−0)
- `data-region="scenario-overview"` strip before the panel suite, projected from **already-shipped**
  props (`widgets`, `widget_labels`, `widget_values`, `widgets_absence`). No computation, no
  scenario-name branch — gate G-33 holds (a fifth scenario reaches it for one config entry).
- **Partial.** The team-door half is **blocked**; see §7.

### N3.37 — Veterans detail absence and action copy
`resources/js/pages/Veterans/Show.vue` (+51/−23)
- Six inline / `title`-only `N/A`s replaced with `AbsenceValue` (house primitive, visible reason).
- `Status` split into **`Career status`** (`status_label`, the run's Active/Completed/Retired) and
  **`Veteran: Banked`** — two facts that were previously adjacent and confusable.
- Transfer: consequence stated in one visible line beside the button; the dialog stays as confirmation.
- Provenance card now separates the local record from the external Source Ledger.
- Door relabelled `Open the Cockpit` → **`Open the source run`** (wireframe's own word; destination
  unchanged, `props.runUrl`).
- New spec: `veterans.spec.ts` — `separates the career status from the banked state on the detail page`.

### N6.6 — Races absence reasons
`resources/js/pages/Database/Races.vue` (+14/−5 within a larger N6.16 count)
- Five `reason="Not recorded"` placeholders replaced with column-specific sentences. Confirmed live in
  the Playwright ARIA snapshot, e.g.
  `"No surface is recorded for this race in the catalogue, so whether it runs on turf or dirt is not known."`

### N6.15 — Help question index + limitations
`resources/js/pages/Help.vue` (+80/−0, **purely additive**)
- `data-region="help-index"` nav over the page's **eight existing `h2` ids** — no anchor invented.
- New `help-limits` card ("What this tool cannot do") with four rows: undo, prediction, image upload,
  dual storage.
- Troubleshooting restructured into What you see / Why / What to do, **keeping the `<table>` shape and
  the original sentences** because `help.spec.ts:63` pins a `row` role and one exact sentence.
- New spec case: `a question index reaches every card it names (N6.15)` — follows all 8 links and asserts
  each anchor exists exactly once.

### N6.16 — Contextual Help deep links
`Races.vue`, `Sources.vue` (+10/−1), `TraineeCatalog.vue` (peer-dirty; my hunk only)
- Three surfaces link instead of restating: Races → `/help#help-tags`,
  Sources → `/help#help-source`, TraineeCatalog → `/help#help-tags`.
- **There was zero prior art** for fragment links in the tree, so whether Inertia honours the hash was
  genuinely unknown. It does — the spec asserts the target is scrolled into the viewport
  (`getBoundingClientRect().top` within `innerHeight`), and passes.
- **Trap hit:** the Races link was first placed inside the `v-if="props.totalCount === 0"` empty-state
  branch. The scratch catalogue holds 410 slots, so it never rendered and the spec failed with
  "element(s) not found". Moved to the always-rendered results header.

---

## 5. Files changed by this session

Source (11):
```
resources/js/components/career/UnityCupEditor.vue          +33  -1
resources/js/components/support/CardPicker.vue             +14  -3
resources/js/components/support/SupportSlot.vue            +67  -3
resources/js/pages/Career/InheritanceEvent.vue             +30  -1
resources/js/pages/Career/ScenarioDetail.vue               +60  -0
resources/js/pages/Career/SkillsPlanner.vue                +68  -10
resources/js/pages/Career/TrainingDetail.vue               +30  -3
resources/js/pages/Database/Races.vue                      +14  -5
resources/js/pages/Database/Sources.vue                    +10  -1
resources/js/pages/Help.vue                                +80  -0
resources/js/pages/Veterans/Show.vue                       +51  -23
resources/js/components/catalog/TraineeCatalog.vue         +6  -1  (file total; my hunk is the +5, the -1 is the peer's)
```

Specs (5):
```
tests/browser/career-deck-select.spec.ts                   +49  -3
tests/browser/career-deck-summary.spec.ts                   +1  -1
tests/browser/career-training-detail.spec.ts               +23  -0
tests/browser/help.spec.ts                                 +68  -0
tests/browser/veterans.spec.ts                             +23  -0
```

**Per-hunk attribution for `TraineeCatalog.vue`** (shared with the peer): my change is the `@@ -287`
hunk adding the `What unconfirmed means` link. The peer's change is `@@ -399`, removing `ADR-0030` from
a sentence. Stage only the first if this file is ever committed.

---

## 6. Verification actually run

All commands below were executed in this session. Output quoted as observed.

| Command | Result |
|---|---|
| `npm run typecheck` | exit 0 (clean) — run repeatedly, last at 04:2x |
| `npm run lint:ui` | exit 0; arbitrary-value debt at baseline **209** |
| `npm run build` | ✓ built (multiple times; required because the browser harness refuses a stale bundle) |
| `php artisan test` (focused, PHP) | `UiHygiene + DatabaseRacesSort + DatabaseHubSourcesLink + HelpScreen` → **13 passed, 183 assertions** |
| `pest RenderedCopyHygieneTest` | **1 failed, 7 passed (38 assertions)** — pre-existing conflict, see B-2. Not caused by this session; the failing character is in `HEAD:` bytes too. |
| `playwright` — `help.spec.ts` alone | **10 passed** |
| `playwright` — `help + veterans + team-edit + training-detail` | **41 passed** |
| `playwright` — `deck-select + inheritance-event + unity-cup-panel` | **43 passed, 1 failed** |
| `playwright` — final consolidated 5 files | **63 passed, 1 failed** |

**Full Pest suite and full Playwright suite were deliberately NOT run** — excluded by the brief. No
claim is made about them.

### Lint debt discipline
My edits initially added **5 arbitrary Tailwind values** (four `tracking-[0.06em]` / `[0.07em]` and one
`max-w-[24rem]`), tripping the `debt-increase` ratchet at 209+1. All five were replaced with tokens
(`tracking-wide`, `max-w-prose`) and the ratchet is back at baseline. This is worth knowing because the
ceiling is a hard fail, not a warning.

---

## 7. Blockers

### B-1 — N3.15 team door: payload does not carry `team_url` (backend change required)
- `CockpitController::scenarioDetail()` (line 223) sends `run`, `scenario`, `backUrl` — **no `team_url`**.
- The sibling `show()` (line 311) *does* compute it:
  `'team_url' => $run->composesPanel('team_rank_ladder') ? route('runs.team', $run) : null,`
- The plan agrees (`docs/plan/implementation-plan-2026-11-20.md:526`): "`app/Http/Controllers/Career/CockpitController.php:223` (scenario action; ships no team_url)".
- **Not implemented**, deliberately: adding a control that calls a nonexistent prop would be a fake
  affordance. The fix is a one-line addition to `scenarioDetail()` plus the prop declaration — but that
  is `app/**`, outside this brief.

### B-2 — `Help.vue` `1–5` is an unsatisfiable two-test conflict that pre-exists at HEAD
**This entry corrects an earlier reading in this same session.** The `1–5` row was described mid-session
as "a peer repeatedly reverting my fix". That was wrong. The rewrite is a **deliberate copy-gate
correction**, and the actual defect is a contradiction inside the repository.

The two requirements, both **unmodified at HEAD**:

| # | Requirement | Location | Wants |
|---|---|---|---|
| 1 | `RenderedCopyHygieneTest > ships no em dash or en dash in rendered copy` | `tests/Feature/RenderedCopyHygieneTest.php:123` — `preg_match('/[\x{2013}\x{2014}]/u', $line)` | **no** U+2013 |
| 2 | `help.spec.ts > the keyboard table names only controls the build wires` | `tests/browser/help.spec.ts:113` at HEAD | **exactly** `1–5 (…)`, U+2013 |

Measured against `git show HEAD:` bytes:
- `HEAD:resources/js/pages/Help.vue` **contains** `'1–5 (training decisions only)'` (en dash).
- `HEAD:tests/Feature/RenderedCopyHygieneTest.php` **contains** the `\x{2013}` ban.
- `HEAD:tests/browser/help.spec.ts` **pins** the en dash.

**Therefore, on a clean checkout of `39a41ace`, the PHP copy gate is already red on `Help.vue`, and the
browser spec only passes because the violating character is present.** The two cannot both be satisfied
without editing one of them. This is a pre-existing repository contradiction, not a product of this
session or of any peer edit.

Current working tree resolution: the file holds the **en dash** (satisfying the browser spec),
so `RenderedCopyHygieneTest` reports one hit. Verified by running it:

```
FAILED  RenderedCopyHygieneTest > ships no em dash or en dash in rendered copy
+Array [ 'resources/js\pages\Help.vue' => Array [ 0 => 49 ], ]
Tests: 1 failed, 7 passed (38 assertions)
```

(The gate strips template comments before numbering, so its reported line 49 is the rendered row at
source line 110.)

**Not resolved here, deliberately.** Choosing the fix is a copy decision with an accessibility
consequence, and it is exactly the class of change the brief excluded (editing tests to go green is
also barred). The two candidate resolutions are in §9, step 2. Note that `AGENTS.md` §5 bans only the
**em** dash in shipped copy; the **en** dash is banned solely by this test's implementation, which is
worth confirming against the owner's intent before either side is changed.

---

## 8. Pre-existing failures, proven not caused by this session

| Spec | Failure | Proof it is not mine |
|---|---|---|
| `career-deck-select.spec.ts:163` | computed-style drift (`fontSize` 14px vs 12.5px, `lineHeight`, `padding`) on the **"Card types"** table | That table is byte-identical to HEAD; `git diff` on `DeckSelect.vue` contains **zero** lines touching `deck-types`, `Boosts its own`, or `No friendship bonus` |
| `career-skills-planner.spec.ts` ×3 | locator wants `textbox`; component declares `role="combobox"` | `SearchableSkillPicker.vue` is **unmodified from HEAD**; the role landed in peer commit **`62dc6a6f`** |
| `database.spec.ts:158` | Sources table 19 rows, expected 18 | Peer's `config/reference.php` adds **4** `conflict-*` rows; my `Sources.vue` diff touches no table row |
| `database.spec.ts:216` | Shop Items block | `ShopItems.vue` is peer-modified (+25/−15); never touched by this session |

---

## 9. Outstanding work, dependency-ordered

1. **Decide the `1–5` conflict (B-2).** It blocks any claim that the PHP gates are green, and it
   pre-exists at HEAD, so it is not "someone else's bug to ignore". Two resolutions, one must be chosen
   by the owner or a brief holder:
   - **(a) Change the copy to `1-5` (hyphen)** and update `help.spec.ts:181` to match. Satisfies the
     copy gate as written; costs the typographically correct range mark.
   - **(b) Keep the en dash** and allow it in `RenderedCopyHygieneTest` for this one row (or narrow its
     regex to U+2014 only, given `AGENTS.md` §5 bans only the em dash). Satisfies the browser spec and
     the documented copy rule; requires a test edit, which needs authorisation.
   *Verify either way:* `vendor/bin/pest tests/Feature/RenderedCopyHygieneTest.php --compact` → 8 passed;
   `PLAYWRIGHT_PORT=<new> PLAYWRIGHT_SCRATCH_DB=... npm run test:browser -- tests/browser/help.spec.ts` → green.
2. **N3.15 team door (needs backend, so needs authorisation).** Add to
   `CockpitController::scenarioDetail()` (line 223):
   `'team_url' => $run->composesPanel('team_rank_ladder') ? route('runs.team', $run) : null,`
   then declare `teamUrl: string | null` in `ScenarioDetail.vue` props and render a contextual
   `Edit team state` link inside the team section, gated on the URL's presence (never on a scenario
   name, per G-33). Verify with `ScenarioDetailTest`, `ScenarioPanelTest`, `ScenarioIsolationTest` and a
   browser case asserting the link resolves. **This crosses `app/**` and is not authorised under the
   current brief.**
3. **Remaining Phase 3/6 frontend slices not attempted** (untouched by either session, in scope but not
   reached): N3.4 (Where-to-go dedupe), N3.7 (race selection clarity), N3.17 (orphan/dead-code),
   N3.22/N3.26/N3.27 (wizard), N3.31/N3.33/N3.34/N3.35/N3.36 (snapshot), N3.38 (veteran structure
   pass), N6.4/N6.5/N6.9/N6.11/N6.12/N6.14 (database). Several sit in files the peer was editing —
   **re-measure before touching.** Note N6.5 (`Sources.vue` filters) explicitly needs coordination,
   since this session now also holds a hunk in that file.
4. **Owner-gated, do not execute:** N3.16, N3.28, N3.40, N6.18.

### Checks required to verify each
- Any `resources/js/**` change → `npm run typecheck` && `npm run lint:ui` (expect debt 209) && `npm run build`
  (the browser harness hard-fails on a stale bundle).
- Any changed screen → its focused browser spec, session-private:
  `PLAYWRIGHT_PORT=<new port> PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-<port>.sqlite npm run test:browser -- <spec>`.
  Never reuse port 8391-8401 or `.scratch-uma/n337-2026-11-20/` (this session's).
- Any `N/A` introduced in a Vue template → run `UiHygieneTest`; it fails a visible bare `N/A` with no
  `title`.

---

## 10. Environment notes worth carrying forward

- **Scratch dir for this session:** `.scratch-uma/n337-2026-11-20/` (browser DBs 8391-8401, plus a
  stale `playwright.lock` from the first refused run). Safe to delete.
- **The browser harness refuses to run against a stale `public/build`** (`global-setup.ts:184-208`,
  comparing `manifest.json` mtime against the newest file under `resources/js`). Rebuild after every
  Vue edit, or the run errors before any test executes — that error has exit code 0, so read the output,
  not the code.
- **Port collisions:** `netstat -an | grep LISTENING | grep -E ':8[1-5][0-9][0-9]'` before starting.
- **Contention:** with the peer's 16 php/node processes live, generic tool calls (`grep`, `ls`) timed out
  repeatedly ("pre_tool_call plugin callback timed out") and one shell invocation failed outright with
  "No shell found". Retries succeeded. Do not read these as code faults.
- **Reading the failure detail without a browser:** `test-results/<slug>/error-context.md` holds the live
  DOM accessibility tree at the failure. This is how both placement bugs (N6.16 empty-state link, the
  Races row count) were diagnosed without re-running anything.

---

## 11. Relation to the peer sitrep (must be read together)

`docs/sitrep/sitrep-2026-11-21-frontend-uiux-session-handoff.md` (untracked, peer-authored, 238 lines,
mtime 04:07:58) covers the **same working tree** from a different scope: the
`trainerdesk-complete-uiux-implementation-plan-2026-11-20.md` family, the N4.8/N4.9 gate work, and a
peer's N6.3 backend slice. The two reports are **complementary, not duplicates**, and neither supersedes
the other.

Where they touch:

| Topic | Peer sitrep | This sitrep |
|---|---|---|
| `Help.vue` N6.15 task index | §1.1 line 30 lists it as "further peer work observed but not attributed" | Owned and detailed here (§4 N6.15) with its passing spec case |
| `TrainingDetail.vue` gains-absence note | §6.1 item 2 reports the `UiHygieneTest` N/A red as peer work it would not fix | **Mine**; the fix was applied here and `UiHygieneTest` is now green (13 passed) |
| `UnityCupEditor.vue` | §1.1/§9.4 mention it as an unattributed mid-write touching typecheck | **Mine** (N3.14); typecheck is clean on the current bytes |
| N6.6 Races absence | §9 item 15 lists it as open backend work (`RaceFacts.php`) | **Frontend half done here**: the five placeholder reasons in `Races.vue` |
| The `1–5` dash | §6.1 item 1 identifies the same conflict, and asks the peer to author the row "without the dash" | Same conflict analysed as **B-2**, with the added finding that **both tests are unmodified at HEAD**, so it pre-exists both sessions |

**Agreement on the dash:** both reports independently reach it, and both leave it unresolved. Two
sessions agreeing on a conflict is stronger evidence than either alone; a resuming agent should treat
B-2 as confirmed, not anecdotal.

**Disagreement to be aware of:** the peer sitrep attributes some of these files to "further peer work
observed but not attributed". For the specific files listed above, this report has the clearer
attribution because those edits originate here. Where the two conflict, **this report wins for the ten
slices in §4**, and the peer sitrep wins for its own N4.8/N4.9 and N6.3 backend work.

---

## 12. Explicitly not inspected

- The **full** Pest suite and **full** Playwright suite (excluded by the brief; no claim made).
- `docs/audits/2026-11-20-ui-ux-application-assessment.md` and
  `docs/design-research/*.jfif` (untracked, peer-owned, not read).
- The peer session's intent or remaining work; only its **written files** were observed.
- `SCREEN_SPEC.md` / `DESIGN.md` currency for the changed screens — the plan defers that to **N3.39**
  and **N6.17**, neither of which was attempted. **Several copy strings changed** (Veterans status
  labels, source-run door, SkillsPlanner hint-cost sentences, Races absence reasons); those documents
  are now behind the tree and N3.39/N6.17 must cover them.

---

## 13. Handoff summary

**Verified state:** ten frontend slices (N3.6, N3.8, N3.10, N3.11, N3.14, N3.15-overview, N3.37, N6.6,
N6.15, N6.16) are implemented in the working tree on top of `39a41ace`, uncommitted, verified by
**63 passing browser tests** and **13 passing PHP tests (183 assertions)**, with typecheck, lint:ui and
build clean. No backend file was modified. `RenderedCopyHygieneTest` is **red** on one pre-existing
`Help.vue` en-dash conflict (B-2) that also fails at HEAD; it is not a regression from this work.

**Remaining:** N3.15's team door (blocked on a backend prop), plus N3.4, N3.7, N3.17, N3.22, N3.26,
N3.27, N3.31, N3.33-N3.36, N3.38, N6.4, N6.5, N6.9, N6.11, N6.12, N6.14 — none of which either session
has started. N3.16/N3.28/N3.40/N6.18 stay owner-gated.

**Blockers:** (1) `scenarioDetail()` ships no `team_url`; (2) the `1–5` en-dash is required by
`help.spec.ts` and banned by `RenderedCopyHygieneTest`, both unmodified at HEAD — an unsatisfiable pair
that needs an owner or brief-holder decision.

**Exact next step:** obtain a ruling on B-2 (allow the en dash, or change the copy to `1-5` and update
`help.spec.ts:181`), because it is the only thing preventing a fully green focused gate set. Then, if
authorised, make the one-line `team_url` addition in `CockpitController::scenarioDetail()` (line 223),
which is the only thing standing between N3.15 and complete.
