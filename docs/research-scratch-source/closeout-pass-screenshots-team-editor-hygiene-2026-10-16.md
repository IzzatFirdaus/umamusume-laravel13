# Screenshots, Duplicate Team Editor, and Hygiene Filing — Close-Out Pass

**Date:** 2026-10-16
**Scope:** Verify the prior pass's two screenshots; fix the duplicate `Edit team state` control; file the `.env.example` defect and verify the `AGENTS.md` encoding and the undeclared `props.saved` read. The wiring, the reconciliation panel and the deselect worktree were not touched.
**State:** working tree only. Nothing committed, staged, or pushed.

---

## 1. Precondition

HEAD unchanged since the prior passes (`71bb5ed`, master ahead 9) — the concurrent sessions still have not committed. Dirty targets: `Cockpit.vue` (peer, hunks at :546/:1053, not the team region, 1h31m old — proceed), `KNOWN-ISSUES.md` (10 min old — **my own KI-101 from the prior turn**, not peer activity; Task 3a proceeded), `AGENTS.md` (owner's rewrite, 19 min old — byte check is read-only, proceeded), `career-cockpit-forms.spec.ts` and `ScenarioSelect.vue` (my prior passes' work). `UnityCupEditor.vue` and `.env.example` clean. Peer signal: zero writes to `resources/js`, `app`, `tests`, or `config` in the 14 minutes before the check; the newest `.scratch-uma` files are this session's own artifacts. All three tasks proceeded. One location correction: the brief's `closeout-report.md` pointer resolved to `docs/research-scratch-source/scenario-field-metadata-closeout-report-2026-10-16.md` — that is the prior pass's report and was read in full.

## 2. Task 1 — the two screenshots

**The harness does not surface PNGs to me in this session** — stated explicitly; the same limit held in the prior pass. The visual claims therefore remain **anchored to passing assertions, not to pixel inspection**:

- Both files verified at byte level: valid PNG signatures, **1920×1080** (the capture viewport), `cockpit-unity-cup-no-crash.png` 80,886 bytes (05:03), `scenario-select-rule-family.png` 110,476 bytes (05:04).
- `cockpit-unity-cup-no-crash.png`: produced by the capture script's full wizard walk against the same `public/build` bundle the regression case ran on; the case asserts the strip regions, the `Spirit Bursts: not recorded` meter (`role="img"`), and zero `TypeError`/undefined-read console errors, and it passed in that bundle state (twice: the targeted run and the 42-case trio).
- `scenario-select-rule-family.png`: the script opened Unity Cup's facts disclosure before capturing; the Rule family label it depicts is pinned by `CareerScenarioSelectTest` (`Team rank`) and by `career-scenario-select.spec.ts`'s exact-text assertions, both green against the same bundle.
- No discrepancy found between what the assertions establish and what the captures were taken of; the pixel-level gap is recorded, not papered over.

## 3. Task 2 — the duplicate `Edit team state` control

**The investigation's outcome: one editor, wrapped in two identical disclosures.** `UnityCupEditor.vue` (landed `d8fb234`, R2-14) is a single form posting the live `runs.update` route, whose root was a self-contained `<details>` labelled `Edit team state`. The cockpit-wt merge added a second identical `<details>` around it in `Cockpit.vue:757` — proven by `git show 53e491a~1`: pre-merge `Cockpit.vue` contains **zero** occurrences of the text. So the rendered DOM was a disclosure inside a disclosure under one label, box-in-box — the strict-mode collision the two browser cases caught, and a user-facing impossibility (two identical controls, no way to tell which opens what).

**The fix (outcome A-shaped): the component's internal `<details>` is the duplicate, and it is gone.** The Cockpit wrapper keeps everything the component cannot own — the `v-if` gating (`composesTeamPanel && teamState !== null`), the `:open="isActive"` open-while-Active behaviour the wrapper's own comment documents as designed, and `data-region="team-state"`. The editor's `<form>` is now the component root; the form, its seven fields and its write path are untouched. `UnityCupEditor` is single-caller (only `Cockpit.vue` imports it), so no other consumer exists.

**Two further defects surfaced while making the cases pass, both fixed, both from the same merge arc:**

1. With one disclosure left, the cases failed differently: on an Active career the wrapper is **open by default** (`:open="isActive"` — the merge's own design), so the tests' unconditional summary-click *closed* the panel they were trying to open. The two cases' first click is now open-state-aware — the same conditional pattern `career-scenario-select.spec.ts`'s `openFacts` already establishes. This is the test adapting to the fixed code's designed state, not an assertion change and not a workaround for the duplicate (which no longer exists); the second click in the close-without-saving case is untouched because closing is its point.
2. The cases' read-back then exposed a **fourth merge-arc defect**: `team_rank_ladder` is registered twice in `ScenarioPanel.vue`, and the Phase 7A.1 `TeamRankLadderPanel` shadowed E3's `UnityCupPanel` (a Map's last-set-wins). The shadowed renderer draws the team facts card — the saved team name, motto and counters — while `TeamRankLadderPanel` reads a `scenario.ladder` key the section does not carry, so it rendered alerts and no ladder at all: a saved team name printed **nowhere** on the Cockpit. Fixed by removing the shadowing registration and its import. The Phase G ruling names `UnityCupPanel` as the live surface ("already print in `UnityCupPanel.vue:199-228`") and the two components it retired (`UnityCupBurstBank`, `UnityCupRaceBriefing`) were never built — so the unshadowing honours the ruling's one-fact-once rule by drawing the card once instead of zero times.

**Test result:** the two formerly-failing cases now pass, together with the SpiritBurstsPanel regression case (`3 passed (47.5s)` targeted), and the **full pair of cockpit specs is green — `33 passed (4.2m)`, zero failures** — which also covers every other case the unshadowing could have touched. `full-career-journey.spec.ts:144` uses the same single-disclosure locator and now resolves against the fixed page; that long spec was not run and is noted, not claimed.

## 4. Task 3a — `.env.example` filed as KI-102

`KNOWN-ISSUES.md` carries **KI-102: "`.env.example` cannot be parsed by the repo's own dotenv reader and ships MySQL defaults into a SQLite-only project, so a fresh clone's first boot dies twice"** — FILED 2026-10-16, OPEN. The entry records both measured consequences (the fresh-clone `package:discover` death; the fresh-worktree `getaddrinfo for mysql`), **and this pass re-verified the first premise directly rather than parroting it**: `php -r 'require "vendor/autoload.php"; Dotenv\Dotenv::createImmutable(getcwd(), ".env.example")->load();'` → `Dotenv\Exception\InvalidFileException: Failed to parse dotenv file. Encountered unexpected whitespace at [Trainer Desk].` The file's line 1 and lines 25-30 are the config-level evidence for both halves. Three fix options are enumerated — quote the value; replace the MySQL block with the SQLite triple (recommended); split into `.env.example` + `.env.mysql.example` — and the entry names the owner decision explicitly. `.env.example` was not edited.

## 5. Task 3b — `AGENTS.md` encoding: already clean; the "mojibake" was a transport artifact

Byte-level check (PHP `file_get_contents`, the bash equivalent of the brief's .NET read — same byte-level access, no `Get-Content`):

- Working tree (the owner's rewrite): BOM **absent**, double-encoded `Ã‚Â§` markers **0**, correct `Â§` markers **72**.
- HEAD (`71bb5ed`, *before* the rewrite): correct markers **70**, double-encoded **0**.

So the tracked file's bytes were never double-encoded at HEAD either — the `Ã‚Â§`/`â€“` shapes seen in earlier reads were the harness notification's transport artifact, not file content. **No repair was applied and none was needed**; the working tree's only delta from HEAD is the owner's content rewrite (13 changed lines, two extra section markers), confirmed content-only by `git diff HEAD AGENTS.md`.

## 6. Task 3c — the undeclared `props.saved` read, flagged

`ScenarioSelect.vue:113` (line numbers drifted from the brief's `:103` by the wiring pass's interface edits) reads:

```html
<SetupLayout :step="1" :visited="props.visited" :saved="props.saved">
```

while the page's `defineProps` (lines 51-57) declares only `scenarios`, `visited`, `selected` and `ruleset` — **`saved` is undeclared**, so the binding hands `SetupLayout` `undefined`. The wire carries the real value (`ScenarioSelectController::show()` passes `'saved' => SetupDraft::saved()`) and `SetupLayout` declares `saved?: number[]` — the page just never declares what it reads. `tsc --noEmit` cannot see it because the typecheck pipeline runs plain `tsc`, which does not type-check `.vue` SFC templates; only `vue-tsc` would. **Recommendation: one line — add `saved: number[];` to the page's `defineProps`.** Practical impact today is nil (the owner's 2026-10-13 ruling makes step 1's visit its own save), but the undeclared read is a real contract bug. **Not fixed**: the file is peer-owned and the deselect patch also touches it; this is the flag the next owner of that file acts on.

## 7. Files changed

| File | Change |
| ---- | ------ |
| `resources/js/components/career/UnityCupEditor.vue` | The form is the component root; the internal duplicate `<details>`/`<summary>` removed, with a comment naming the caller as the disclosure's owner |
| `resources/js/components/scenario/ScenarioPanel.vue` | The shadowing `register('team_rank_ladder', TeamRankLadderPanel)` and its import removed, with a comment citing the payload shape and the Phase G ruling |
| `tests/browser/career-cockpit-forms.spec.ts` | The two team-editor cases' first click made open-state-aware (the designed open-on-Active state), with the reason in comments |
| `KNOWN-ISSUES.md` | KI-102 appended |

## 8. Test results (actual output)

- `npm run typecheck` → clean.
- Browser, session-private (`PLAYWRIGHT_PORT=8147`, scratch `browser-8147.sqlite`; harness confirmed reading both variables): targeted `-g "team state through the team editor|without saving records nothing|without a runtime error"` → first `2 failed` (open-by-default interaction), then after the test adaptation `1 failed` (read-back), then after the unshadowing **`3 passed (47.5s)`**; full `career-cockpit-forms.spec.ts` + `career-cockpit.spec.ts` → **`33 passed (4.2m)`**, zero failures.
- Builds: two probe-builds to `.scratch-uma/team-editor-fix/build-check` then two foreground publishes (peer-quiescent box; KI-101's lesson applied both times).
- Lore: `php tools/lore.php code` → **148 hit(s)**; `php tools/lore.php docs` → **449 hit(s), 75 exempt** — identical totals to both prior passes, so zero new hits from `UnityCupEditor.vue`, `ScenarioPanel.vue`, the spec edits or KI-102.
- PHPStan (full tree): **3 errors, all pre-existing and unchanged** — `CockpitController::MONTHS` unused (:120) and the two `nullsafe.neverNull` findings at the same `?->turnIndex` statement (:291).
- Pint: not run — no PHP file changed in this pass (the brief's conditional gate; stated rather than skipped silently).

## 9. Documentation drift (flagged, not fixed)

1. **The cockpit-wt merge pattern, now four landed-red defects from one arc:** the `SpiritBurstsPanel` widget-key registration crash, the reverted `aria-label="Energy band"`, the duplicate `Edit team state` disclosure, and the `team_rank_ladder` shadow that retired the working team-facts card. All four are fixed as of this pass; the pattern to carry forward is that the arc's Phase 7A.1 registrations and bulk commit `63d1cdd` each landed against trees that had not been rebuilt or browser-run, which is exactly what KI-101's staleness refusal would catch.
2. **The same double-registration pattern exists for `grade_objectives`, `shop` and `epithet_routes`** (E4's `TrackblazerPanel` shadowed by `GradePointObjectivesPanel`, `ProShopPanel`, `EpithetRoutesPanel`). Whether those three later renderers are correctly wired is **unverified** — the Trackblazer feature tests are green, but no browser case this pass ran exercises their read surfaces the way the team case did. Flag for the owning slice; do not assume either way.
3. `ScenarioSelect.vue`'s undeclared `saved` (§6) — peer-owned, flagged with the one-line fix.
4. `SCREEN_SPEC.md`, `docs/proposals/*`, `docs/UMAMUSUME_REFERENCE.md` and the ADRs were not touched.

## 10. Explicit stop

All three tasks landed and every gate this pass's changes could affect has run. No commit, no stage, no PR; the writes outside the change set are the session-private `.scratch-uma/team-editor-fix/` logs and the gitignored `public/build` rebuilds the verification required.

PLAN COMPLETE — the two screenshots are verified (noted as assertion-anchored); the duplicate Edit team state control is fixed; .env.example is filed, AGENTS.md is verified, and props.saved is flagged. No commit or staging.
