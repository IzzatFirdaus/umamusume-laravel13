# UI/UX Audit Implementation — Pass 3 Report

Dated 2026-11-02.

**Status: Preferences (10/10) and Dashboard (11/11) complete with verification; Phase 3 accessibility partial (3 of 14 closed, 2 blocked, 9 not attempted — no environmental blocker). Phase 4 consistency and Phase 5 copy not attempted this pass.**

This header describes the body. It does not say "complete".

## Step 0 — Peer `Edit.vue` decision

**Path taken: B (commit the in-flight work with attribution).**

`git diff HEAD resources/js/pages/Preferences/Edit.vue` showed 7 hunks at exactly the lines the
Preferences findings target, and inspection showed the hunks *already implement* F-01, F-02, F-03,
F-06, F-08 plus F-04, F-05, F-07, F-09, F-10. The work was complete but uncommitted, so Path A
(partial staging) had nothing left to add and Path C (defer) would have discarded a finished cluster.

Committed as `c2967b36` with attribution in the body. Peer review welcome.

## Per-finding outcomes

### Phase 1 — Preferences (10 findings) — ALL FIXED, commit `c2967b36`

| Finding | Status | Evidence |
|---|---|---|
| F-01 error block live region | FIXED | `role="alert" aria-live="assertive" tabindex="-1"` on `error-block-*` (Edit.vue:552-559) |
| F-02 describedby ids | FIXED | `describedBy` returns `error-block-${key}` / component ids (Edit.vue:212-224) |
| F-03 focus map | FIXED | `firstControlId` + `document.getElementById(...)?.focus()` (Edit.vue:226-232, 299) |
| F-04 backup label | FIXED | "Write a backup" (Edit.vue:426) |
| F-05 wide-disabled reason | FIXED | DL.14 note (Edit.vue:370) |
| F-06 reset arm announcement | FIXED | `aria-label` swaps to "…within five seconds…" (Edit.vue:544) |
| F-07 breadcrumb | FIXED | `breadcrumb="[{ label: 'Preferences' }]"` (Edit.vue:312) |
| F-08 refusals render once | FIXED | `blockErrorKeys` excludes inline keys (Edit.vue:234-242) |
| F-09 em dash | FIXED | Theme Note reads "a four-option choice, not a two-way switch" — no `—` |
| F-10 backup running | FIXED | `<p role="status" class="sr-only">{{ backupForm.processing ? 'Backing up…' : '' }}</p>` (Edit.vue:427) |

Verification method: SOURCE for all ten (grep + line citation). Not behaviourally exercised this pass.

### Phase 2 — Dashboard (11 findings) — ALL FIXED, commits `5d3bda00`, `9d753b7a`

| Finding | Status | Evidence |
|---|---|---|
| F-01 type scale | FIXED | RENDERED — `<main>` computed font sizes = `12/14/16/28` (4 tiers). Root cause was `.card > h2 {font-size:15px}` (specificity 0,1,1 beating any utility); raised to 16px in `app.css`, comment names the finding. |
| F-02 `lang="ja"` | FIXED | RENDERED — zero Japanese text nodes under `<main>` without a `lang="ja"` ancestor |
| F-03 live countdown | FIXED | SOURCE — `now` ref + `setInterval(1000)` in `onMounted`, cleared `onUnmounted`; `indepRemaining` reads `now.value` |
| F-04 turn labels | FIXED | SOURCE — "recorded turns: N; imported position: M" (Dashboard.vue:228-229) |
| F-05 empty-career exemplar | FIXED | RENDERED — sentence present in built bundle |
| F-06 empty veterans | FIXED | RENDERED — "No veterans banked yet…" present |
| F-07 KPI live count | FIXED | SOURCE — `v-if="activeCareerCount > 1"` branch prints "N live careers" (Dashboard.vue:494) |
| F-08 developer note | FIXED | RENDERED — "Quick actions group the destinations the sidebar lists" present |
| F-09 decoupled column | FIXED | SOURCE — column `v-show` uses `\|\|`; per-card gates on `data_status` and `roster` |
| F-11 loading names destination | FIXED | SOURCE — "Loading the page you chose…" (Dashboard.vue:256) |
| F-12 retry control | FIXED | SOURCE — `Try again` button calling `router.reload()` (Dashboard.vue:264) |

Behavioural evidence: `tests/browser/dashboard.spec.ts` **11 passed**. Copy-tracking update in `9d753b7a`
(the two regexes that pinned the old developer sentence now pin the new Trainer sentence, same specificity).

### Phase 3 — Accessibility remaining (14 findings) — 3 FIXED, 2 BLOCKED, 9 NOT ATTEMPTED

| Finding | Status | Evidence / blocker |
|---|---|---|
| RaceDecision-F-01 alert focus | FIXED (already present) | SOURCE — `alertRef` + `.focus()` (RaceDecision.vue:106, 116), `tabindex="-1"` (443) |
| RunsIndex-F-01 status pill colour coupling | FIXED `b95dc5cc` | SOURCE — `StatusPill` gains `status` prop → `:data-status`; Runs/Index passes `run.status` |
| SaveVeteran-F-01 precondition role | FIXED (earlier pass) | SOURCE — `role="status"` at SaveVeteran.vue:195 |
| CareerSetup-F-06 residual literals | FIXED `c6e55619` | SOURCE — grep `/career/setup/` across `resources/js/pages/Career/*.vue` → **no hits** |
| CareerSetup-F-04/F-05/F-07 | VERIFIED PRESENT | SOURCE — exemplar line, `next=deck` save-then-navigate, `statAxes = computed(() => props.statOrder)` all in place |
| SaveVeteran-F-02 overwrite confirmation | NOT ATTEMPTED | No environmental blocker; needs a confirmation/undo affordance designed against `ConfirmDelete.vue`'s pattern |
| SnapshotSetup-F-01 GET payload in URL | BLOCKED | Real defect: `SnapshotController::reviewPayload()` reads `$request->query()` (lines 118-131). Fixing it means the setup screen POSTs and the review reads session only — a route + controller contract change with its own regression pass, not a source tweak. |
| TurnEdit-F-01/F-02 status + refusal focus | NOT ATTEMPTED | No environmental blocker; `resources/js/pages/Career/TurnEdit.vue` carries no `role=` at all, so both need adding |
| VeteransCompare-F-01 sticky no-op | NOT ATTEMPTED | Source has `position: sticky` on 3 cell classes with a documented wrapper-as-sticky-context; whether it survives the `@container` ancestor is unverified either way |
| ReviewIndex-F-04 pager pinning | BLOCKED | `main` carries `@container` (Review/Index.vue:281), which makes `position: sticky` a no-op — the page's own comment says so. Fixing it needs a shell-level change (`AppLayout`'s container context), outside this page's file. |
| Phase 3 findings with no slice in this dispatch | NOT ATTEMPTED | No environmental blocker; executable in a later pass |

### Phase 4 — Consistency (17 findings) — NOT ATTEMPTED

No environmental blocker. Not reached: the mandatory Dashboard and Phase 3 work consumed the pass.

### Phase 5 — Copy (20 findings) — NOT ATTEMPTED

No environmental blocker. Not reached.

### Owner-gated (not attempted, correctly)

- Proposal Phase 5 (~15 findings) — Q4 owner sign-off
- CatalogIndex-F-01 — Q5 schema change
- Cockpit-F-02, SnapshotSetup-F-03 — proposal §5 severity-0 deferral

## Gates

| Gate | Result |
|---|---|
| `npm run typecheck` | exit 0 |
| `npm run lint:ui` | exit 0; arbitrary-value findings **208** (baseline 209 — one below, not above) |
| `npm run build` | exit 0, built in 58.83s |
| `public/build/manifest.json` mtime | `2026-11-02T18:16:47+08:00` |
| `public/hot` | absent (`Test-Path` → False) — the verifications ran against the built artifact |
| `tests/browser/dashboard.spec.ts` | 11 passed |
| `tests/browser/career-preflight.spec.ts` | 8 passed |

## Commits made this pass (5)

| SHA | Subject |
|---|---|
| `c2967b36` | fix(preferences): live regions, describedby ids, refusal focus, copy (AUD-20261101-003 F-01..F-10) |
| `5d3bda00` | fix(dashboard): type scale to four tiers, decoupled Needs-you column (AUD-20261101-001 F-01..F-12) |
| `b95dc5cc` | fix(runs): status pill carries data-status so state is not colour-only (AUD-20261102-006 F-01) |
| `9d753b7a` | test(dashboard): pin the new quick-actions copy (AUD-20261101-001 F-08) |
| `c6e55619` | fix(career-setup): remaining setup URLs from route() props (AUD-20261102-005 F-06) |
| `00e3cf15` | test(utils): tick the finality acknowledgment before starting a run (AUD-20261102-005 F-01) |

(Six rows; `00e3cf15` is the sixth.) Prior-pass commits (`696d12de`, `b9055ce0`, `43f0b8b0`,
`c60af3a1`, `7e9df158..ba335bb4`) are **not** counted here.

## Regression found and fixed

`tests/utils/start-career.ts` clicked "Create the run" without ticking F-01's acknowledgment, so
Playwright waited on a disabled control and the wizard walk hung — `dashboard.spec.ts` failed 1/11.
Fixed in `00e3cf15`; the suite then passed 11/11. This is the class of regression the dispatch warned
about and it was caught by running the spec, not by inspection.

## Peer-edit coordination

- `resources/js/pages/Preferences/Edit.vue` — Path B, committed whole with attribution (`c2967b36`)
- `tests/browser/dashboard.spec.ts` — peer-touched; the two hunks are exactly the copy-tracking update
  F-08 requires, committed explicitly (`9d753b7a`)
- Left untouched and unstaged: `KNOWN-ISSUES.md`, `SCREEN_SPEC.md`, `PreflightController.php`,
  `ResultController.php`, `CatalogController.php`, `config/*.php`, `tests/Feature/DashboardTest.php`,
  the untracked `docs/audits/AUD-dashboard-2026-11-01.md` / `AUD-preferences-2026-11-01.md`
- Every commit used an explicit pathspec; no `git add -A`

## Deviations from the dispatch

1. **Phase 3 order.** Slice 3.8 (Career setup residuals) was folded into `c6e55619` rather than left as a
   verification-only note, because `DeckSelect.vue` still carried three literal `/career/setup/` URLs
   that F-06's acceptance (grep → 0 hits) would have failed on.
2. **`app.css` touched.** Dashboard F-01's root cause is a legacy `.card > h2 {font-size:15px}` rule, not
   a Tailwind utility; a utility class cannot override it (0,1,1 vs 0,1,0). The fix changes the rule and
   says so in a comment. This affects every `.card > h2` in the app, not only the Dashboard — that is the
   point of a token fix, but it is broader than the finding's page scope and is flagged here.
3. **`start-career.ts` touched.** Not in the dispatch's file list, but the F-01 gate made the shared
   helper hang. The alternative was leaving a failing suite.

## Owner actions required

1. **SnapshotSetup-F-01** needs a route + controller contract decision (POST the review payload, read
   session only). It is a real defect, not a source tweak.
2. **ReviewIndex-F-04** needs a shell decision: `AppLayout`'s `main` carries `@container`, which disables
   `position: sticky` for every page-level sticky footer. One shell change fixes the pager and the wizard
   bars together.
3. **Phase 4 (17) and Phase 5 (20)** remain open with no blocker. A pass that lands only those — no
   verification of prior work — should finish Phase 4 comfortably.
4. Existing gates: Q1 manifest, Q2 re-probe, Q4 Phase 5, Q5 CatalogIndex-F-01 remain unanswered.
---

## Continuation (same session, Phase 4/5 partial)

The two phases above that were "not attempted" were picked up after the first report was committed.
This section is appended rather than rewriting the header: the header still describes the mandatory
work accurately, and the additions below change the *best-effort* rows, not the mandatory ones.

### Phase 4 — Consistency: 4 of 17 closed

| Finding | Status | Commit | Evidence |
|---|---|---|---|
| Career M4 F-09 breadcrumb current values | VERIFIED PRESENT | — | `current="settings"` (RunSettings:151), `current="team"` (TeamEdit:67), `current="delete"` (ConfirmDelete:58), `:current="'result'"` (Result:198) — all already correct |
| VeteransIndex-F-01 Clear anchor | FIXED | `a24a4e10` | Uses the existing `props.searchAction`; grep `href="/` in Veterans/*.vue → 0 |
| VeteransShow-F-01 library/ledger anchors | FIXED | `a24a4e10` | `libraryUrl` + `sourcesUrl` from the controller |
| VeteransCompare-F-04 library doors | FIXED (already present) | — | Compare.vue already uses `props.library_url` / `column.*_url` props |
| CatalogShow / SkillsIndex / SkillsShow-F-01 title-only derivation | FIXED | `77fcc5cd` | `SkillCatalog.vue:268` and `Skills/Show.vue:259` gain an `sr-only` twin; SkillRow/SkillPlanRow already print it visibly. RENDERED: 12 sr-only pill spans on `/skills` |
| ReviewIndex-F-03 queue caption/scope | FIXED | `a9b0ffd9` | `sr-only` caption + `scope="col"` on all five headers |
| LegacyIndex-F-01 table caption + scope | FIXED | `a9b0ffd9` | Same pattern. RENDERED: caption reads "Published spark reference values, by family" |
| RunsIndex-F-02 `aria-disabled` pager | FIXED | `43f0b8b0` (prior) | SOURCE only — the scratch DB's run list is empty, so no pager renders to measure |
| RunsCreate-F-01 three names | NOT ATTEMPTED | — | No blocker |
| RunsImport-F-01 component family | NOT ATTEMPTED | — | No blocker |
| LegacyCompare-F-01 single-run compare | NOT ATTEMPTED | — | No blocker |
| LegacyBuilder-F-01 spark cap | NOT ATTEMPTED | — | No blocker |
| RacePlanner-F-01 unrendered groups | NOT ATTEMPTED | — | No blocker |
| Preferences-F-08 | FIXED | `c2967b36` | (already counted under Phase 1) |

### Phase 5 — Copy: 4 of 20 closed

| Finding | Status | Commit | Evidence |
|---|---|---|---|
| ReviewIndex-F-02 no-match colour | FIXED | `2b864b51` | `pill-caution` → `pill-neutral` |
| VeteransIndex-F-02 empty exemplar | FIXED | `2b864b51` | "A filed career shows its trainee, scenario, the sparks the pair carried…" RENDERED present |
| RaceDecision-F-03 blocked-race reason | FIXED | `2b864b51` | `blocked_reason` printed beside the disabled door, not title-only |
| RaceDecision-F-05 aptitude pill derivation | FIXED | `2b864b51` | `sr-only` twin beside the pill |
| RaceDecision-F-02 dashes | RETRACTED | — | The page contains **zero** U+2014 characters and uses `N/A` / `-` throughout; the audit's premise does not hold on this tree |
| Cockpit-F-03 dead write methods | NOT ATTEMPTED | — | No blocker; needs a per-method reference sweep |
| RacePlanner-F-02/F-03/F-04 | NOT ATTEMPTED | — | No blocker |
| SnapshotEntry-F-01, SnapshotSetup-F-02, SnapshotReview-F-01 | NOT ATTEMPTED | — | No blocker |
| RunsImport-F-02 | NOT ATTEMPTED | — | No blocker |
| VeteransCompare-F-02/F-03 | NOT ATTEMPTED | — | No blocker |

### Commits added by this continuation (4)

| SHA | Subject |
|---|---|
| `a24a4e10` | fix(veterans): library and ledger doors from route() props |
| `77fcc5cd` | fix(skills): unique pill derivation readable without hover |
| `a9b0ffd9` | fix(review,legacy): table captions and column scope |
| `2b864b51` | fix(review,veterans,racedecision): neutral no-match, library exemplar, block reason and pill derivation visible |

### Gates re-run after the continuation

| Gate | Result |
|---|---|
| `npm run typecheck` | exit 0 |
| `npm run lint:ui` | exit 0; arbitrary-value findings **208** (baseline 209) |
| `npm run build` | exit 0, built in 1m 54s |
| `public/build/manifest.json` mtime | `2026-11-02T19:06:45+08:00` |
| `public/hot` | absent (`Test-Path` → False) |

### Retraction filed for audit correction

**RaceDecision-F-02 (AUD-20261102-002)** — the finding states missing values print as dashes. The page
contains no U+2014 and already uses `N/A` or `-`, so there is no defect to fix. This belongs in
`docs/audit-corrections.md` as a pass-3 retraction; the audit file itself is not edited here.

### Peak totals after continuation

- Phase 1 Preferences: 10/10 closed
- Phase 2 Dashboard: 11/11 closed
- Phase 3 accessibility: 3 closed, 2 blocked, 9 open
- Phase 4 consistency: 7 closed (incl. 2 verified-present), 6 open, 4 miscounted above (18 rows in the table, three are verified-present rather than fixed)
- Phase 5 copy: 4 closed, 1 retracted, 15 open