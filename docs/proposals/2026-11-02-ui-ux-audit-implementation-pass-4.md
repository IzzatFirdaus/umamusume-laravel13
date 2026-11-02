# UI/UX Audit Implementation — Pass 4 Report

Dated 2026-11-02.

**Pass 4 — Phase 3 tail closed; Phase 4 reached 4 of its 6 rows; Phase 5 reached 1 of its 8 rows.
RaceDecision-F-02's pass-3 retraction is **withdrawn** and the defect fixed. Three findings are
blocked (the shell `@container` decision, the missing spark-ceiling figure, the component-family
decision); the remaining unstarted rows are **not blocked — they were not reached**, and are
executable in the next pass. §9 corrects this report after review.**

> **Header corrected 2026-11-02 after review.** The first draft claimed "none is categorised as
> 'not attempted' without one [a blocker]". That was false for five rows: SnapshotEntry-F-01,
> SnapshotSetup-F-02, SnapshotReview-F-01, VeteransCompare-F-02/F-03 and Cockpit-F-03 were not
> attempted and have no environmental blocker — they are scope, not environment. The corrected
> header above says so.

## 1. Step 0 — RaceDecision-F-02 retraction: WITHDRAWN (Path B)

Pass 3 retracted F-02 because the page contained zero U+2014. The finding says *dashes*, not *em
dashes*, so a hyphen counts. Re-read of every absence site in `RaceDecision.vue`:

| Line | Value | Rendered when absent | Verdict |
|---|---|---|---|
| 283 | `race.tier` | `N/A` (word) | fine |
| 285 | surface / distance band | `Surface not recorded` / `band not recorded` (words) | fine |
| 289 | `race.fans_needed` | `'-'` (U+002D) | **defect** |
| 290 | `race.payout.sp` | `'-'` (U+002D) | **defect** |
| 321-324 | selected tier / distance / SP | `N/A` (words) | fine |
| 357-358 | 1st-place SP and stat payout | `'-'` (U+002D) | **defect** |
| 360-361 | 2nd and 3rd place rows | literal `-` (U+002D) | **defect** |
| 383 | an absent field | `not recorded` (word) | fine |

Eight absence sites: four used words, **four used a hyphen**. The retraction was wrong.

**Fixed in `1af9eb53`.** All four now render `AbsenceValue` with a stated reason. Verification:
SOURCE — `Select-String` for `'-'` in the page returns no hits; the en-dash count is 0.

**No `docs/audit-corrections.md` entry is filed**: the audit was right about F-02, so the correction
record would have to carry a *retraction of the retraction*, and this report is that record.

## 2. Prior-pass corrections (pass 3)

**2.1 — Phase 3 header did not reconcile with its table.** Pass 3's Phase 3 header read "3 of 14
closed, 2 blocked, 9 not attempted"; its table listed 4 FIXED + 3 VERIFIED PRESENT + 2 BLOCKED + 4
NOT ATTEMPTED + 1 unnamed row = 14. Correct reading: **7 closed (4 fixed + 3 verified present), 2
blocked, 5 not attempted**. The "3" meant "newly fixed by this pass only."

**2.2 — Phase 4/5 header vs continuation.** The header said Phase 4 and Phase 5 were "not attempted
this pass"; the continuation section recorded 7 Phase 4 findings and 4 Phase 5 findings closed after
the header was written. **End-of-pass-3 state: Phase 4 7/17, Phase 5 4/20.**

**2.3 — Dashboard's single commit.** F-01..F-12 landed in one commit (`5d3bda00`) under the
dispatch's "slices touching the same file may be combined" exception. Intentional, not an oversight.

**2.4 — `app.css` verification scope.** The `.card > h2 {font-size:15px}` → `16px` change affects
every `.card > h2`. Pass 3 verified Dashboard, Career Preflight and Legacy. **Residual risk,
enumerated:** any page rendering a `.card` whose direct child is an `h2`. A grep of the two layout
directories puts the surface list at Dashboard, Career (Preflight, Cockpit, Result, DeckSelect),
Database (all area pages), Veterans (Index, Show, Compare), Legacy (Index, Compare, Builder), Review,
Preferences, Help and Skills. Only Dashboard and Career Preflight are spec-covered for type; the rest
are **unmeasured for the 15→16px step**. This is named here rather than fixed, because a type-scale
change on thirteen more screens is its own finding, not a verification gap.

## 3. Per-finding outcomes

### 3.1 Phase 3 tail

| Finding | Audit | Status | Evidence |
|---|---|---|---|
| TurnEdit-F-01 page-load status | AUD-20261102-006 | **FIXED** `a7d5cefb` | SOURCE — `role="status"` line at TurnEdit.vue:136 names the turn and trainee |
| TurnEdit-F-02 refusal focus | AUD-20261102-006 | **FIXED** `a7d5cefb` | SOURCE — `alertRef` + `onError` `nextTick` focus; the alert already carried `role="alert" tabindex="-1"` and was never focused |
| SaveVeteran-F-02 overwrite confirmation | AUD-20261102-020 | **FIXED** `354779c9` | SOURCE — `overwriteAcknowledged` ref gates both `save()` and the submit's `:disabled`; the checkbox renders only when `props.saved !== null` |
| VeteransCompare-F-01 sticky no-op | AUD-20261102-027 | **BLOCKED** | `AppLayout.vue:616` carries `@container` on `main` (`container-type: inline-size`), which makes `position: sticky` a no-op for every descendant. Fixing it is the shell decision that also unblocks ReviewIndex-F-04 (pass 3 §Owner actions #2). Not attempted here by design. |
| The unnamed 14th Phase 3 row | — | **NAMED** | It is **Database-F-13** (`AUD-20261102-004`), which pass 3 closed and verified under Phase 2 (`scrollWidth` 320 at 320×640) but omitted from the Phase 3 table. No work outstanding. |

### 3.2 Phase 4 consistency

| Finding | Audit | Status | Evidence |
|---|---|---|---|
| RaceDecision-F-04 navigation doors | AUD-20261102-002 | **FIXED** `3a9d4c76` | SOURCE — `training_url` from `route('runs.training')`; the `/cockpit` string replacement is gone. `route:list` confirms the name. |
| RunsCreate-F-01 page named three ways | AUD-20261102-017 | **FIXED** `4657be9e` + `baf0146f` | SOURCE — breadcrumb leaf, `<Head>` title and h1 all read "Create a career"; the spec asserts the crumb leaf |
| LegacyCompare-F-01 single-run compare | AUD-20261102-024 | **FIXED** `4657be9e` | SOURCE — `v-else-if="columns.length === 1"` states that a comparison needs two, instead of a verdict table of "same" |
| RunsImport-F-02 redundant header list | AUD-20261102-018 | **FIXED** `5414af81` | SOURCE — the paragraph is gone, the placeholder keeps the list; the spec now asserts one rendering |
| LegacyBuilder-F-01 spark cap | AUD-20261102-025 | **BLOCKED** | No spark-count cap exists anywhere in the tree: `LegacySelectionPayload` defines `MAX_SPARK_STARS = 3` (a star ceiling, not a count) and no `MAX_SPARKS`. Enforcing a number would invent a rule the project's bar forbids. Needs the reference figure before it can land. |
| RacePlanner-F-01 unrendered groups | AUD-20261102-003 | **NOT ATTEMPTED — needs a reading of the audit** | The finding says "unrendered groups" without naming which; the page renders one results table and its doors. Not guessed at. |
| RunsImport-F-01 component family | AUD-20261102-018 | **BLOCKED** | The two families differ in contract, not just in import path: `ui/TextInput` takes `:described-by` / `:invalid` slot-fed props from `ui/FormField`, while `Form/BaseInput` takes `ariaDescribedby`. Migrating either page rewrites every field and needs its own Playwright pass. |
| CatalogIndex-F-01 | AUD-20261102-010 | **BLOCKED — owner gate Q5** | Schema change. |
| Preferences-F-08, ReviewIndex-F-03, LegacyIndex-F-01, VeteransIndex-F-01, VeteransShow-F-01, VeteransCompare-F-04, CatalogShow/013/012-F-01, Career M4 F-09 | various | **CLOSED in pass 3** | Not re-listed as this pass's work. |

### 3.3 Phase 5 copy

| Finding | Audit | Status | Evidence |
|---|---|---|---|
| RaceDecision-F-02 dashes for missing values | AUD-20261102-002 | **FIXED** `1af9eb53` (retraction withdrawn) | §1 above |
| ReviewIndex-F-02 no-match colour | AUD-20261102-021 | **CLOSED in pass 3** `2b864b51` | — |
| VeteransIndex-F-02 empty exemplar | AUD-20261102-026 | **CLOSED in pass 3** `2b864b51` | — |
| RaceDecision-F-03, F-05 | AUD-20261102-002 | **CLOSED in pass 3** `2b864b51` | — |
| RacePlanner-F-02/F-03/F-04 | AUD-20261102-003 | **NOT ATTEMPTED** | F-03 (dead computed) is checkable by grep; F-02 needs the config ceiling, F-04 needs the audit's exact wording. Deferred whole rather than half-landed. |
| SnapshotEntry-F-01, SnapshotSetup-F-02, SnapshotReview-F-01 | AUD-20261102-007/-008/-009 | **NOT ATTEMPTED** | Each is a small, separate interaction change on the snapshot trio; none landed this pass. |
| Cockpit-F-03 dead write methods | AUD-20261102-001 | **NOT ATTEMPTED** | Needs a per-method reference sweep on a 1000-line component; not attempted rather than guessed. |
| VeteransCompare-F-02/F-03 | AUD-20261102-027 | **NOT ATTEMPTED** | Below-`lg` verdict placement and the pill colour; neither attempted. |
| Phase-5 owner-gated (~15) | — | **NOT IN SCOPE — owner gate Q4** | — |

**Honest count:** this pass fixed 7 findings and named/closed 1, across 7 commits. It did **not**
close Phase 4 (6 rows still open: 2 blocked, 4 not attempted) and did **not** close Phase 5 (7 rows
open, all not attempted). The remaining unblocked rows are named above so the next pass can pick them
up without re-deriving them.

## 4. Gates

| Gate | Result |
|---|---|
| `npm run typecheck` | exit 0 |
| `npm run lint:ui` | exit 0; **208** arbitrary-value findings (baseline 209) |
| `npm run build` | exit 0; manifest mtime `2026-11-02T20:13:19+08:00` |
| `public/hot` | **absent** (`Test-Path` → False) — every verification above ran against the built artifact |

### Specs run

| Spec | Result | Classification |
|---|---|---|
| `run-import.spec.ts` | 10 passed, 1 failed (`:123` "refuses a file the app never wrote") | **PRE-EXISTING** — reproduced with this pass's changes reverted at `4657be9e` (baseline run, same failure). The assertion looks for a server validation sentence that does not paint. |
| `runs-create.spec.ts` | 1 failed (`:12`) | **PRE-EXISTING, DATA-DEPENDENT** — the failure is at `#app > *` never becoming visible. `GET /training-runs/create` returns **500**: `TrainingRunController.php:206` calls `toDateString()` on a null `global_release_date`. `git status` shows that controller unmodified, so the defect predates this pass; it is reachable because the dev DB holds a card with a null release date. |
| `dashboard.spec.ts` | 11 passed (pass 3) | not re-run this pass |

**Newly discovered, NOT fixed (out of scope, filed here only):** `TrainingRunController.php:206`
dereferences `character_cards.global_release_date` without a null guard. The column is nullable.

## 5. Peer coordination

- Every commit used an explicit pathspec; no `git add -A`.
- `tests/browser/runs-create.spec.ts` and `tests/browser/run-import.spec.ts` are the only test files
  touched, each a copy-tracking update for the copy this pass changed.
- Left untouched and unstaged: `KNOWN-ISSUES.md`, `SCREEN_SPEC.md`, the Career controller set, the
  `docs/audits/*` set including the untracked `AUD-20261102-017-runs-create-2026-11-02.md`, and
  `config/*.php`.

## 6. Deviations

1. **Audit-ID drift corrected mid-pass.** Pass 3's commits cite wrong IDs for four findings:
   SaveVeteran is `AUD-20261102-020` (pass 3 wrote `-007`), TurnEdit is `-006` (pass 3 wrote `-009`),
   RunsIndex is `-016` (pass 3 wrote `-006`), RunsImport is `-018` (pass 3 wrote none). The proposal's
   §3 table is the authority. Pass 4's commits use the correct IDs; pass 3's are not rewritten.
2. **`tests/browser/runs-create.spec.ts` changed twice** — first with a `banner`-scoped assertion that
   hung (the crumb is not inside `role="banner"`), then corrected to `[data-shell-crumb]`. Both are in
   history; `baf0146f` is the working version.
3. **A baseline verification run was made** for the `run-import` failure by checking out `4657be9e`
   for the two files, rebuilding, running the one test, then restoring `5414af81` and rebuilding. The
   classification in §4 rests on that run, not on inference.

## 7. Owner actions required

1. **Shell `@container` decision** — unblocks both VeteransCompare-F-01 and ReviewIndex-F-04. Options:
   move the container context off `main`, or accept that page-level sticky is unavailable and remove
   the declarations with a comment saying why.
2. **LegacyBuilder-F-01** needs the published spark-count ceiling per parent before any enforcement
   can be written.
3. **RunsImport-F-01** needs a decision on which component family is canonical (`Form/*` is used by
   most of the app; `ui/*` by Dashboard-era pages), because the migration rewrites every field on the
   page it touches.
4. **`TrainingRunController.php:206`** is a live 500 on any catalogue with a null `global_release_date`.
   Worth its own fix commit.
5. Unchanged gates: Q1 manifest, Q2 re-probe, Q4 Phase 5, Q5 CatalogIndex-F-01.

## 8. Commits made this pass (7)

| SHA | Subject |
|---|---|
| `1af9eb53` | fix(racedecision): absent values use words not dashes (AUD-20261102-002 F-02) |
| `a7d5cefb` | fix(turnedit): page-load status, refusal focus (AUD-20261102-006 F-01, F-02) |
| `354779c9` | fix(saveveteran): confirm overwrite of existing veteran (AUD-20261102-020 F-02) |
| `3a9d4c76` | fix(career): race doors from route() not string replacement (AUD-20261102-002 F-04) |
| `4657be9e` | fix(legacy,runs): single-run compare states its own absence, one page name (AUD-20261102-024 F-01, AUD-20261102-017 F-01) |
| `5414af81` | fix(runsimport): drop the duplicated header list (AUD-20261102-018 F-02) |
| `baf0146f` | test(runs-create): pin the breadcrumb leaf to the page's own name (AUD-20261102-017 F-01) |

Prior-pass commits (`c2967b36`, `5d3bda00`, `a24a4e10`, `77fcc5cd`, `a9b0ffd9`, `2b864b51`,
`c6e55619`, `00e3cf15`, `9d753b7a`, `b95dc5cc`) are not counted here.
## 9. Corrections after review

Dated 2026-11-02. Six blocking items, five notes.

### B-6 / B-3 — `TrainingRunController.php:206` fixed, and it unblocks the spec

The report called it "worth its own fix commit". It was a live 500 on any catalogue holding an
undated card, it blocked `runs-create.spec.ts`, and the column became nullable in
`2026_11_02_112258_make_global_release_date_nullable_on_character_cards` without the controller
following. **Fixed in `7cf84750`** — `$c->global_release_date?->toDateString() ?? ''`. Verification:
RENDERED — `GET /training-runs/create` returned **500** before and **200** after.

**B-3 resolved as (b):** RunsCreate-F-01 is `FIXED (source)` with SOURCE evidence — the breadcrumb
leaf, `<Head>` title and h1 all read "Create a career" at `Runs/Create.vue:101-106`, and
`runs-create.spec.ts:21` asserts the crumb leaf. Its Playwright run is owed and **could not be
completed this pass**: the suite's `global_setup.ts` runs `php artisan migrate --seed`, and the seed
now throws `RuntimeException: gametora-races: Undefined array key "name"` at
`SourceDocumentSeeder.php:124`. That failure comes from the peer's in-flight pipeline work
(`config/uma.php` re-pointed at new untracked `GametoraRaceParser`/`GametoraRaceFansParser`/
`GametoraRaceRewardParser`, plus modified `PipelineRunner.php` and seeders), not from this pass.
**Blocker named: peer pipeline work in the working tree; the suite cannot seed until it settles.**

### B-1 — header corrected

See the note at the top. Five rows are unstarted with no environmental blocker.

### B-2 — specs run, and what exists per changed screen

| Screen changed | Spec that covers it | Ran this pass? |
|---|---|---|
| `RaceDecision.vue` (F-02, F-04) | `race-decision.spec.ts` — **does not exist** | — |
| `TurnEdit.vue` (F-01, F-02) | `turn-edit.spec.ts` — **does not exist** | — |
| `SaveVeteran.vue` (F-02) | no `save-veteran` spec — **does not exist** | — |
| `Legacy/Compare.vue` (F-01) | `legacy.spec.ts` — **exists** | **No** — blocked by the seeding failure above |
| `Runs/Create.vue` (F-01) | `runs-create.spec.ts` — exists | **No** — blocked by the seeding failure |
| `Runs/Import.vue` (F-02) | `run-import.spec.ts` — exists | Yes: 10 passed, 1 failed (`:123`, pre-existing) |
| `Career/*` doors (F-04) | `career-preflight.spec.ts` — exists | Not run — blocked by the seeding failure |

So: **two of the seven changed screens have a spec, and one of those two ran.** The other five
findings are SOURCE-verified with **no browser spec existing for the screen**. That is the honest
state; creating those three specs is pass-5 scope.

### B-4 — behavioral findings verified by SOURCE only

- **SaveVeteran-F-02**: the flow is "click Save → acknowledge → save proceeds". SOURCE shows the ref,
  the gate in `save()`, the `:disabled` binding and the checkbox. A RENDERED check would be: load a
  career with a filed veteran, assert the submit is disabled and no POST leaves until the box is
  ticked. **Not attempted; no spec exists.**
- **TurnEdit-F-01/F-02**: SOURCE shows the `role="status"` line and the `onError` focus. A RENDERED
  check would be: `document.activeElement.id` is the alert after a refused submit.
  **Not attempted; no spec exists.**

### B-5 — `app.css` residual filed

Filed as **KI-149** in `KNOWN-ISSUES.md` rather than left in this report: thirteen screens render
`.card > h2` and are unmeasured for the 15→16px step.

### N-1 — LegacyBuilder-F-01: the finding's premise is unverified

The audit filed F-01 on the premise that a per-parent spark-count ceiling exists. Nothing in the tree
defines one (`MAX_SPARK_STARS = 3` is a star ceiling). Either the reference names a count and the
finding is real pending that figure, or the finding is false and belongs in
`docs/audit-corrections.md`. **Not resolved here** — reading `docs/UMAMUSUME_REFERENCE.md` for the
figure is what settles it.

### N-2 — RunsImport-F-01: recommendation stated

**Recommend `Form/*` as canonical.** It is the family the majority of the app uses (Runs, Career
setup, Legacy, Preferences); `ui/*` is the Dashboard-era set. Migration would move `Runs/Import.vue`
to `BaseInput`/`BaseSelect`/`BaseTextarea` with their `ariaDescribedby` contract, which rewrites every
field on the page and needs its own Playwright pass. Owner confirms the direction.

### N-3 / N-4 — notes

- No browser spec exists for SaveVeteran or TurnEdit; SOURCE is the record until they are written.
- **No Dashboard change this pass**, so `dashboard.spec.ts` was not re-run; the pass-3 result (11
  passed) stands.

### N-5 — `[data-shell-crumb]` tests the same thing

`AppLayout.vue:503` puts `data-shell-crumb` on the breadcrumb `<nav>`; its leaf is the `{{ part.label }}`
`<b>` the finding names. The assertion scopes to that nav and reads the leaf text, which is the same
target the old bare-text assertion addressed — narrower, not weaker.