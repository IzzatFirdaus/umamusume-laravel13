# Verification report — RTX-01, UX-09, RTX-02, F-13

**Date:** 2026-11-12
**Starting reference HEAD:** `3aee95e2`
**Round type:** verification only. No product code, test, or audit artifact was modified. Nothing
staged, nothing committed.

---

## A. Starting state

- **Actual HEAD:** `3aee95e2` (`fix(training): keep an in-progress turn as a per-tab draft (F-08)`).
- **Branch:** `master`.
- **Git status:** 23 paths — 2 modified, 21 untracked, all documentation from earlier workstreams;
  **0 staged, 0 conflicts**. No product source carried uncommitted work at any point in this round.
- **All four implementation commits present and reachable:** `fc6f775d` (RTX-01), `339035b5` (UX-09),
  `aba21651` (RTX-02), `b2832ee6` (F-13).
- **Subsequent commits touching their files: none.** Each commit's file set was checked individually
  with `git log <sha>..HEAD -- <file>`; every file in all four commits returns empty, so no later
  commit has altered the behaviour under test.
- **Concurrent peer work:** none observed during this round. The tree did not move; HEAD was stable
  from recon to report.

---

## B. Finding verdicts

### 1. RTX-01 — stale parent card library pick — **ACCEPTED**

| Field | Evidence |
| --- | --- |
| Original acceptance criteria | `docs/audits/trainer-desk-v3-ux-retest/04-ux-findings-retest.md:44-58`. Expected: "the step's own summary ('Choose a veteran \| Not chosen') reflects the pick." Actual (pre-fix): the select committed (`#legacy-pick-0.value === "1"`) but the summary above the form still read "Parent A \| Not chosen" until save. Recommendation: "bind the summary line to the select's live value." |
| Implementation commit | `fc6f775d`, verified present and reachable; files `resources/js/pages/Career/LegacySelect.vue` and `tests/browser/career-legacy-parent-card-sync.spec.ts`, **neither modified by any later commit**. |
| Regression proof (A) | `the parent card shows the pick before the step is saved` asserts the card's `.pill` is `'Not chosen'` **before** the pick, then `toHaveText('Chosen')` after `selectOption`, plus the empty-state string `Pick from your veteran library, or leave this slot empty.` at `toHaveCount(0)`. That sequence fails on the pre-fix behaviour, so it genuinely catches the original defect. Second case `S-NOT-F03: the pick reaches the form model, so only the resolver is wrong` reads the picker's own value to separate "never committed" from "resolver precedence wrong". Third case `the picked veteran survives a save and reads back as Chosen` covers the persistence round trip. |
| Behaviour proof (B) | Live at `/career/setup/legacy` on HEAD: before any pick the card's pill read **`Not chosen`**. After choosing manual entry and naming the parent `Vodka`, the pill read **`Chosen`** and the card rendered `Vodka` — **before any save**. That is the contract's exact expected transition. The dev database carries no seeded veterans, so the manual-entry path was used; it exercises the same `cardFor()` resolver under test. |
| Truth-source proof (C) | The component's own rendered DOM (`parentACard.locator('.pill')` text and the card body) is the authoritative source for this finding: the contract is about what the summary **displays**, not about persisted state. The third regression case supplies the persistence half via the save round trip. |
| Verdict | **ACCEPTED** |

**Note on the empty-state string.** The live probe's page body still contained
`Pick from your veteran library, or leave this slot empty.` because that string belongs to **Parent B's**
card, which was still empty. The spec scopes its assertion per-card
(`page.locator('.card', { hasText: 'Parent A' })`), so it is not affected. The live reading is
consistent with the spec, not contradictory.

### 2. UX-09 — finale row says the calendar cannot date it — **ACCEPTED**

| Field | Evidence |
| --- | --- |
| Original acceptance criteria | `docs/audits/trainer-desk-v3-ux/04-ux-findings.md:383-420`. Expected: "a due turn or a due window for each obligation." Actual: rows read `URA Finals Qualifier / unknown / no date` while the Scenario panel two blocks above said `Finale 5 turns away`. Recommendation: "let the rail read `FinaleReader`'s position for the finals block instead of printing the null." Residual tooltip correction is `339035b5`'s own second half: the cell was dated but its `title` still said the calendar could not date it. |
| Implementation commit | `339035b5`, files `resources/js/components/career/NextDecision.vue` and `tests/browser/career-cockpit-obligation-due.spec.ts`, **neither modified by any later commit**. The dating half was fixed upstream by `be983f87`. |
| Regression proof (A) | `a dated finale row does not claim the calendar cannot date it` first asserts the cell **is** dated (`toHaveText(/turns? away\|This turn\|Passed/)`) as a precondition, then asserts it does **not** carry `/does not date/i` in its title, then asserts it **does** carry `/does not place this obligation on the turn grid/i`. The second case `a genuinely undated obligation keeps the undated tooltip` asserts a far-from-finale row still reads `no date` **with** the `/does not date/i` title. Together they pin all three states the contract distinguishes. |
| Behaviour proof (B) | Live at `/training-runs/11/cockpit`, the Advisor region's three finale rows read: `URA Finals Qualifier … 5 turns away`, `URA Finals Semifinal … 5 turns away`, `URA Finals Final (Aoharu) … 5 turns away`. Each cell's `title` reads: `The calendar does not place this obligation on the turn grid; the figure is the finale distance the Scenario panel reports.` The Scenario panel on the same screen reports `Finale 5 turns away`. **The two surfaces now agree**; the pre-fix contradiction (`no date` beside `5 turns away`) is gone. |
| Truth-source proof (C) | The rendered DOM — the `td` text and its `title` attribute — is authoritative here, because the finding is entirely about displayed label versus displayed tooltip. Both were read directly from the live page rather than inferred from the underlying date. The evidence records the exact strings (§ above). |
| Verdict | **ACCEPTED** |

**Distinct states the UI now separates, all observed:** undated (`no date` + "does not date" title),
finale-distance (`5 turns away` + "does not place … on the turn grid" title), and grid-position
(`Junior Year · Turn 12`, no tooltip). The contract's requirement that label and tooltip not
contradict is satisfied on the finale rows specifically, which was the defect.

### 3. RTX-02 — dashboard prints a position for a zero-turn career — **ACCEPTED**

| Field | Evidence |
| --- | --- |
| Original acceptance criteria | `docs/audits/trainer-desk-v3-ux-retest/04-ux-findings-retest.md:60-83`. Expected: "the resume card states a turn position the cockpit can also show." Actual: the card printed `TURN 0 of 72` and `Turn 0 logged.` for a run whose cockpit said `No turn recorded yet`. Recommendation: "have the card print the cockpit's phrase when no turn exists." |
| Implementation commit | `aba21651`, files `resources/js/pages/Dashboard.vue` and `tests/browser/dashboard-turn-state.spec.ts`, **neither modified by any later commit**. |
| Regression proof (A) | `a career with no logged turn states no position` asserts the note does **not** contain `/Turn 0 logged/i`, **does** contain `/no turn (has been\|recorded)/i`, and that the Turn tile does **not** contain `/\b0\b/`. All three are user-visible-text assertions, not implementation details. `a logged turn reads as the position it is` and `an imported career names both the logged count and the imported position` pin the other two states. |
| Behaviour proof (B) | The zero-turn branch of `careerNote` was read from source: `if (career.turn === 0) { … return \`No turn has been logged for this career yet.${imported ?? ''}\`; }` — the cockpit's wording, with the imported position appended only when `cockpit_turn` is non-null. The live dev tree holds one run at `turn = 1`, so the zero state is not reachable from the dev database; it is exercised by the spec, which creates its own run in a scratch database (the correct isolation, not a workaround). Live confirmation of the **logged** state was obtained: run 11's card reads `Turn 1 logged · imported at turn 68.` |
| Truth-source proof (C) | Source-level control flow plus the spec's rendered assertions. The database is **not** the authority for this finding, because the defect was purely presentational: both strings derived from the same `career.turn` prop, and the fix changed wording only. `career.turn` itself is unchanged by the commit. |
| Verdict | **ACCEPTED** |

**Observed but out of scope.** Run 11 renders `imported at turn 68` while `training_runs.imported_at`
is `null`. The `68` comes from `DashboardController.php:193` — `'cockpit_turn' => $decisionTurn['turn_index']` — which is the cockpit's own standing, and the controller's comment at `:198-201` documents the disagreement between the recorded-turn count and the cockpit position as a **known defect** (`HFW index.html:102-107`), not a rendering choice. That is a pre-existing, separately documented matter outside RTX-02's contract, which concerned only the zero-turn wording. Recorded here so it is not mistaken for a regression.

### 4. F-13 — planned skill spend allegedly stays zero — **REFUTED**

| Field | Evidence |
| --- | --- |
| Original claim | `docs/audits/rice-shower-unity-cup-ux-walk-8.md:1037`: "`PLANNED SPEND` stays `0` and `REMAINING` stays `173` after a 140 SP skill is added; the screen is titled `Spend 173 Skill Points` and its budget bar cannot see its own plans." The walk's own narrative at `:844` records what it did: skill picker `Pace Chaser Straightaways ◎`, then pressed **`Add to run`**. |
| Implementation commit | `b2832ee6`, file `tests/browser/career-skills-planner.spec.ts`, **unmodified by any later commit**. No product code changed — the finding was refuted, not fixed. |
| Regression proof (A) | `career-skills-planner.spec.ts` gained a structural read of the printed figures: the label `Planned spend` is located, then its value is read from `xpath=following-sibling::*[1]`, asserting `'0'` in the unplanned state. This replaced a prior body that asserted only the savebar, the commit button and the "Too expensive" label — the case's title claimed to verify the spend and remaining figures while asserting neither. The suite is now **14/14**. |
| Behaviour proof (B) | Live at `/training-runs/11/skills` on HEAD, at the same skill the walk used (`Pace Chaser Straightaways ◎`, `COST 140`):<br><br>**before tick** — `Planned spend 0`, `Remaining 173`, h1 `Spend 173 Skill Points`<br>**after tick** — `Planned spend 140`, `Remaining 33`, h1 unchanged<br>**after untick** — `Planned spend 0`, `Remaining 173`<br><br>Delta is exactly 140, the row's own cost, and it reverses exactly. The walk's `0 → 0` does not reproduce. |
| Truth-source proof (C) | Component state and its arithmetic, read from source: `plannedSpend = computed(() => plannedRows.value.reduce((sum, row) => sum + rowCost(row), 0))`, `remaining = computed(() => props.coverage.sp - plannedSpend.value)`, and `plannedRows = allRows.filter(row => planned.value.includes(row.id))`. `togglePlan(id)` is what mutates `planned` — the plan checkbox. The displayed totals are computed from that same state, so display and truth cannot diverge. The bar is rendered from these two computeds directly (`{{ plannedSpend }}`, `remaining`). |
| Why the walk saw `0 → 0` | `Add to run` and the plan checkbox are **different actions on the same screen**. `Add to run` submits `pickerForm.post(props.run.skills_sync_url, { onSuccess: () => { planned.value = [] } })` — it records the skill as acquired and deliberately clears the plan list. The plan checkbox (`togglePlan`) is what adds to the budget. The walk's own steps table records `Add to run - pressed`, so it exercised the acquire path and then read a budget bar that correctly ignores acquired skills. The finding named the wrong control. |
| Verdict | **REFUTED** |

---

## C. Gaps and discrepancies

**Product defects: none found in the four findings.** All four satisfy their original acceptance
contracts as measured on `3aee95e2`.

**Documentation discrepancies — three, all pre-existing and none introduced this round:**

1. **F-13's finding names the wrong control.** `docs/audits/rice-shower-unity-cup-ux-walk-8.md:1037`
   describes the defect as following "a 140 SP skill is added", but the walk's own `:844` narrative
   and its values table show `Add to run` was pressed, not the plan checkbox. The finding and its own
   evidence disagree; the evidence is correct.

2. **The plan's restore-point arithmetic is wrong.** `09-consolidated-remediation-plan.md` §9.1 states
   "15 scalars + 6 preview = 21 values, plus the two performance refs (23 restore points)". Measured
   from `TrainingDetail.vue`, the form holds **21 scalars** (the 15 named, plus five `facility_*` and
   `failure_rate`) + 6 `preview_gains` keys + 2 refs = **29**. The five facility levels and
   `failure_rate` are omitted from the plan's count, and §9.1's own selector list makes the same
   omission. (This does not affect the F-08 implementation landed in `3aee95e2`, which persists
   `form.data()` wholesale and therefore covers every value regardless of the count.)

3. **The dev tree cannot exercise RTX-02's zero-turn state.** The only run present stands at
   `turn = 1`. The state is covered by the spec's own scratch-database run, which is the correct
   isolation, but a reader reproducing by hand on the dev database will not reach it.

**Test-coverage gaps: none material.** All four specs assert user-visible text or component state, and
each was read line by line rather than trusted by title. F-13's replaced assertion is the one instance
where a title previously promised more than its body delivered, and it has been closed.

**Environmental blockers: none.** Browser runs used explicit `PLAYWRIGHT_PORT` values (8461, 8471) with
session-private `PLAYWRIGHT_SCRATCH_DB` paths. One run initially failed to write its log because
`.scratch-uma/verify/` did not exist; that was harness setup, corrected by creating the directory, and
is not a product failure.

---

## D. Finding board

| Finding | Verdict | Recommendation |
| --- | --- | --- |
| RTX-01 | **ACCEPTED** | Close. |
| UX-09 | **ACCEPTED** | Close. |
| RTX-02 | **ACCEPTED** | Close. |
| F-13 | **REFUTED** | Close as refuted; the finding's control reference should be corrected to `Add to run` for future readers. |

The reconciliation register was **not** edited — this round is verification only.

---

## E. Outstanding independent work

Unchanged by this round, and deliberately not addressed:

1. **`ScenarioAdvisorFinaleTest`** — stale expectation after `49a90c3a1` appended `' finals'` to the
   advisor's label. The correct wording is a UX-25 / KI-82 copy decision (`finale_label` is
   `'The Grand Concert'` while `label` is `'Our Grand Concert'`), not a test edit.
2. **`UiHygieneTest`** — two genuine shipped-copy defects: `BurstBandsCard.vue:87` ships
   `UMAMUSUME_REFERENCE §2.2.2, conflict row 57` inside a rendered `detail`; `Veterans/Index.vue:528`
   ships `title="Source: docs/UMAMUSUME_REFERENCE.md"`. Neither is allowlisted.
3. **`f2f0a8e5`'s "red by design" claim** — its commit subject asserts a deliberate exception, but
   `configIdentifierDebt()` covers `config/*.php` only, its own comment says adding an entry is not
   welcome, and neither file is listed. **No written ruling exists.** It is not an approved exception
   and must not be treated as one.
4. **Pending shared-database migration**
   `2026_11_10_010500_add_current_spirit_proxy_to_training_runs_table` — still Pending, not run,
   awaiting owner authorization.

---

## F. Final Git state

- **No tracked file changed.** 23 paths existed at recon and the same 23 are still untracked or
  modified now, plus this report, making 24. Every one is documentation from earlier workstreams or
  this round's own output; no product source and no tracked file was touched.
- **No files staged.** `git diff --cached --name-only` is empty.
- **No commits created.** HEAD remains `3aee95e2` — one commit (`3aee95e2`) exists since `a9c2b142`,
  and it predates this round.
- **No peer-owned work modified.** `docs/audits/trainer-desk-v3-ux/`,
  `docs/audits/trainer-desk-v3-ux-retest/`, and the scenario-detail artifacts were read only.
- **Shared development database untouched.** No migration was run; the only database reads were
  read-only `SELECT`/`PRAGMA` queries against `database/database.sqlite`, and the browser runs used
  session-private scratch databases.

### Evidence commands run

| Command | Result |
| --- | --- |
| `git rev-parse --short HEAD` | `3aee95e2` |
| `git status --short \| wc -l` | `23` (0 staged, 0 conflicts) |
| `npx playwright test dashboard-turn-state career-cockpit-obligation-due career-legacy-parent-card-sync` (`PLAYWRIGHT_PORT=8471`) | **8 passed**, exit 0 |
| `grep`/`git show --name-only` per commit, per file | all four commits' files: no later edits |
| Live browser reads (RTX-01, UX-09, F-13) | values recorded in §B above |
| `sqlite3` read-only queries | `training_runs` 1 row (turn 1), `turn_entries` 1 row (turn 67) |
