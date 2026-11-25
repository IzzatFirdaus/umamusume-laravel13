# SITREP — frontend completion pass (independent Frontend Completion Agent brief)

**Report date:** 2026-11-21
**Baseline commit:** `1b987cec` (`chore(ci): simplify NativePHP workflow artifact plumbing`)
**Working tree:** dirty by design — 95 porcelain rows, shared with a concurrent peer session
**Staged:** nothing (`git diff --cached` empty throughout)
**Committed/pushed/tagged by this session:** nothing

Follows the `docs/sitrep/` convention (AGENTS.md §3, §3a "Report"). Read alongside
`sitrep-2026-11-21-frontend-uiux-session-handoff.md` (peer, different scope — see §9).

---

## 1. Objective and constraints

Brief: finish every achievable, authorised, non-owner-gated **frontend** improvement.

Permitted: `resources/js/**`, `resources/css/**`, `resources/views/**`, `lang/en/uma.php` copy,
`tests/browser/**`, existing frontend a11y test utilities.
Forbidden: `app/**`, `database/**`, `routes/**`, `config/**`, PHP tests, dependency manifests, CI,
governance docs, `SCREEN_SPEC.md`, another agent's files. No commits, no full suites.

Owner-gated, untouched: **N3.16, N3.28, N3.40, N6.18**.

---

## 2. Priority 0 — verification blockers (all four resolved)

### P0.1 — Help `1–5` copy conflict → **DONE**
The two requirements, both unmodified at HEAD, were mutually unsatisfiable:

| Requirement | Location | Wanted |
|---|---|---|
| `RenderedCopyHygieneTest > ships no em dash or en dash in rendered copy` | `RenderedCopyHygieneTest.php:123` — `[\x{2013}\x{2014}]` | no U+2013 |
| `help.spec.ts` keyboard case | `help.spec.ts:113` at HEAD | exactly `1–5`, U+2013 |

**Resolution taken:** the brief's recommended minimal fix — adopt ASCII `1-5` in the copy and update
the focused browser assertion. Both files are in frontend scope; the PHP test was **not** touched.

- `resources/js/pages/Help.vue`: `'1–5 (training decisions only)'` → `'1-5 (training decisions only)'`
- `tests/browser/help.spec.ts`: locator `/1–5 \(…\)/` → `/1-5 \(…\)/`

**Verified:** `vendor/bin/pest RenderedCopyHygieneTest.php` → **8 passed (38 assertions)** (was 1 failed / 7 passed).

> Context worth keeping: an earlier session repeatedly "restored" the en dash, reading the rewrites as
> peer interference. They were gate-driven. The dash must stay ASCII.

### P0.2 — Deck parity → **DONE** (four distinct defects, in order of discovery)
`career-deck-select.spec.ts:163`. Computed drift fell **380 → 64 → 1 → 0** as each real cause was fixed.

1. **380 drifts: theme was never pinned.** `tests/browser/fixtures/wizard-verify.ts` captured the app
   under the host OS theme (`prefers-color-scheme`) while the wireframes are light-only, so navy ink
   (`rgb(8,31,56)`) was compared against the mock's brown (`rgb(72,39,32)`) on every heading and cell.
   Fix: `page.emulateMedia({ colorScheme: 'light' })` plus `removeAttribute('data-theme')` before each
   capture (a server-printed `data-theme` would otherwise win). Same lever `database-parity.spec.ts:102`
   already uses.
2. **64 drifts: two tables hand-rolled instead of using the ported `table.t` primitive.** "Card types"
   and "Deck diagnostics" used `text-sm` (14px) + `py-2`/`py-1.5`; the HFW's `table.t` is 12.5px with
   `7px 9px` cells and 10px uppercase heads. Fix: `class="t mt-3 w-full"` on both, hand-rolled padding/
   weight dropped. No new arbitrary values.
3. **1 drift: `.note > b` blockified.** `.note` is `display:flex` (`app.css:1647`), so a bare `<b>` as a
   direct child becomes a block and the bold lead-in took its own row. The wireframe wraps the prose in
   a `<span>`; the app had dropped it. Fix: restore the `<span>` wrapper on **two** notes (multiplier
   note and friendship-gauge note) — markup, not a `display` override.
4. **Then the `rect` bucket.** Every remaining entry was an `h2`, table cell or bold run offset by
   exactly the type-step difference. Excluded under the ruling `database-parity.spec.ts:74` already
   records (`DESIGN_TOKENS` per-property exclusion; `rect: []` on all six of its screens), because
   `DESIGN.md` owns palette and type scale and the wireframe predates it. **Not invented to force
   green** — quoted in the spec with its source.

**Verified:** `career-deck-select.spec.ts` → **21 passed** (was 20 passed / 1 failed).

### P0.3 — Skills picker role mismatch → **DONE** (stale locator)
`SearchableSkillPicker.vue` implements the **ARIA 1.2 combobox** pattern correctly
(`role="combobox"`, `aria-autocomplete="list"`, `aria-controls`, `aria-expanded`,
`aria-activedescendant`). The spec asked for `textbox`. The app's own
`career-build-target.spec.ts:348` already locates this same component as `combobox`.

Per the brief ("do not downgrade correctly implemented combobox merely to satisfy an outdated
locator"), the **spec** was corrected: 5 occurrences of `getByRole('textbox', { name: 'Skill to add' })`
→ `getByRole('combobox', …)` in `career-skills-planner.spec.ts`.

**Verified:** `career-skills-planner.spec.ts` → **17 passed** in the file run; **36 passed** jointly with
`career-legacy-deck-steps.spec.ts`.

### P0.4 — Database drift → **DONE** (two separate causes)
- **Source Ledger 19 vs 18 rows.** Not a UI defect. `config/reference.php`'s `conflicts` block carries
  `source.anchor`, and `DatabaseController::sources()` transcribes **any** config block with one, so the
  N6.3 landing legitimately added a 5th transcribed dataset. It sends no `recheck.url`, so it adds a row
  and no publisher link. Assertion updated 18→19 rows, 4→5 transcribed; link count stays 17. Reason
  recorded in the spec.
- **Shop Items.** The spec expanded the table with a plain `'Show all'` (substring match), then looked
  for `/^Show all \d+$/` **again** — impossible once the first press worked. Rewritten as one coherent
  sequence: expand via `Show all 19`, assert 19 shown, then collapse via `Show fewer`, assert 10.

**Verified:** `database.spec.ts` green within the final consolidated run.

---

## 3. Priority 1 — Career workspace

### N3.4 — Where-to-go dedupe → **DONE, qualified**
`resources/js/pages/Career/Cockpit.vue`. The card listed six destinations already in
`CareerSectionNav` (Cockpit, Training, Races, Skills, Deck, Timeline, Result).

The plan says "drop the 6 duplicated buttons, keep the 3 contextual doors + advisor-driven next
action … pure removal". **Two qualifications were necessary and are recorded in the code:**

1. **`Run deck` retained.** It is the deck builder's only door on this page and
   `career-cockpit-forms.spec.ts:282` pins it by name **and** href. Removing it would delete a tested
   entry for no reader benefit.
2. **The duplicates survive on a non-active run.** The stepper is mounted under
   `v-if="isActiveRun"` (`CareerLayout.vue:151`), so a Completed or Retired career has **no nav at
   all**. Unconditional deletion would leave Training/Races/Skills/Timeline/Result unreachable — a
   regression the plan's "pure removal" risk note did not anticipate. The set is therefore gated on
   `isActive`, not deleted.

Implementation note: `isActive` had to be moved above `whereToGo` to avoid a temporal-dead-zone read
in the computed. `career-cockpit.spec.ts:703` pins `Career result` on a **Completed** run, which this
preserves.

**Verified:** `career-preflight.spec.ts + career-cockpit.spec.ts` → **47 passed**.

### N3.7 — Race decision UX → **NOT ATTEMPTED**
Unchanged from the peer's state. Remaining work is scoped in §10.

### N3.15 — Scenario detail + team door → **DONE**
The blocker this session earlier reported is **gone**: the supporting agent added `team_url` to
`CockpitController::scenarioDetail()`:
`'team_url' => $run->composesPanel('team_rank_ladder') ? route('runs.team', $run) : null,`

Frontend half implemented in `resources/js/pages/Career/ScenarioDetail.vue`:
- declared `team_url: string | null` and rendered an `Edit team state` `LinkButton` in the header
  actions, gated on `props.team_url !== null` — never on a scenario name (G-33 holds).
- The overview strip (already landed) is unchanged.

**Trap hit and fixed:** the prop was first declared camelCase (`teamUrl`). Inertia hands a page the
keys the controller wrote **verbatim** — it does not camel-case — so the prop read `undefined`, the
`!== null` gate passed, and the door rendered with a *wrong* href (`/training-runs/5/scenario`, the
sibling `backUrl`'s value leaking into the assertion). Renamed to snake case to match the payload. The
reason is recorded in the prop's docblock.

**Verified:** `scenario-detail-order.spec.ts` → **5 passed**, including the new
`offers the team-setup door on a run whose scenario composes a team panel (N3.15)`, which also follows
the door to `/training-runs/{id}/team` and asserts the destination heading.

### N3.22 — Preflight optional Legacy → **NOT REPRODUCED → reverted**
Attempted: `checkState()` treated a visited-but-empty **legacy** step as `Skipped`, on the reasoning
that `StartCareerRequest::rules()` makes `legacy_selection` `nullable` and states "the wizard's
warnings never block".

**That broke three tests**, and the break was decisive:
```
career-preflight.spec.ts:230  expect(attentionCount).toBe(2)  →  received 1
  "the table carries Legacy Missing and Deck Warning"   (BO-006, recorded contract)
career-preflight.spec.ts:186  reviews the five entered steps as one checklist row each
career-preflight.spec.ts:54   keeps a card level and a rented flag … (R6-11)
```
`career-preflight.spec.ts:242-250` records the opposite as a deliberate decision: a draft with one
equipped card and no ancestors must read `Legacy Missing` and count as one of two attention steps.

**Reverted**, with the reasoning preserved as a code comment so this is not re-attempted blind.
Changing that count is a test-owner call, not a frontend edit.

---

## 4. Priority 2 — Snapshot and Veteran

### N3.31 — Snapshot trainee combobox → **BACKEND-BLOCKED (incompatible contract)**
`TraineeCombobox.vue` requires:
- `roster: TraineeRow[]` where `TraineeRow = { umamusumeId, trainee, traineeJa, cards: CardRow[] }`
- `emit('commit', hit: Hit)` where `Hit = { trainee, card }`
- and renders the copy **"No costume card confirmed yet"** for a cardless row.

Snapshot's payload is `trainees: { id: number; name: string }[]` and the brief explicitly forbids
adding a costume-form field the Snapshot contract does not support. Mapping `{id,name}` into
`TraineeRow` would require inventing `cards: []`, which makes every row render the "no costume card"
sentence — a claim about a concept Snapshot does not collect. **Not implemented; would misinform.**

### N3.33 — Unknown-value ergonomics → **DONE**
`resources/js/pages/Career/Snapshot/Setup.vue`. Added one bulk action, `Mark unfilled values as
unknown`, plus a live count and a post-action report.

Safety properties, each deliberate and asserted:
- **Only unfilled fields change.** `unfilledFields` excludes any field whose value is non-empty after
  `trim()`. `String(0)` is `'0'`, so **a valid recorded zero is never touched** — that is the exact
  failure mode the brief names.
- Already-`unknown` fields are excluded, so the affected count is honest.
- Known values are never overwritten; the existing erase-on-tick semantics of `toggleUnknown` are
  unchanged.
- Disabled with a stated reason when nothing is left, so no inert control.
- Keyboard reachable as a plain button; the count is in an `aria-describedby` target and a
  `role="status"` line names the fields it changed (`Power, Guts, Wit, Energy, Fans, Skill Points`).
- `FIELD_LABEL` is derived from the existing `STAT_FIELDS`/`META_FIELDS` arrays rather than added as a
  fourth list.

**Verified:** `career-snapshot.spec.ts` → **17 passed**, including the new
`marks only unfilled values as unknown, and never a recorded number (N3.33)` at `:199`.

### N3.30 — Snapshot onboarding → **ALREADY SATISFIED** (peer)
Entry.vue's `data-region="snapshot-readiness"` list is present and correct; not re-implemented.

### N3.34 — Snapshot Energy copy → **ALREADY SATISFIED** (present in Setup.vue)

### N3.35 / N3.36 / N3.38 → **NOT ATTEMPTED** (see §10)

---

## 5. Files changed by this session

Source (6):
```
resources/js/pages/Career/Cockpit.vue              +85  -15   N3.4 dedupe (gated) + isActive reorder
resources/js/pages/Career/DeckSelect.vue           +63  -30   P0.2 table.t x2, .note span wrappers
resources/js/pages/Career/ScenarioDetail.vue       +~30 -0    N3.15 team door (team_url prop)
resources/js/pages/Career/Snapshot/Setup.vue       +134 -1    N3.33 bulk unknown action
resources/js/pages/Career/Preflight.vue            +9   -6    N3.22 attempted then reverted; reason kept
resources/js/pages/Help.vue                        +81  -1    P0.1 ASCII dash; N6.15 index + limits
```
Tests (8):
```
tests/browser/fixtures/wizard-verify.ts            +20  -0    P0.2 theme pin
tests/browser/career-deck-select.spec.ts           +81  -5    P0.2 DESIGN_TOKENS/rect rationale
tests/browser/database.spec.ts                     +30  -7    P0.4 both causes
tests/browser/help.spec.ts                         +69  -1    P0.1 locator; N6.15/N6.16 cases
tests/browser/career-skills-planner.spec.ts        +5   -5    P0.3 combobox locators
tests/browser/career-snapshot.spec.ts              +68  -0    N3.33 case
tests/browser/scenario-detail-order.spec.ts        +~22 -0    N3.15 door case
tests/browser/career-preflight.spec.ts             +2   -2    Equip locator
tests/browser/career-legacy-deck-steps.spec.ts     +7   -2    Equip locator + Remove confirmation
tests/browser/support-picker-pagination.spec.ts    +1   -1    Equip locator
```

**Shared-file handling.** `DeckSelect.vue`, `Cockpit.vue`, `Preflight.vue`, `Setup.vue` and
`ScenarioDetail.vue` carry peer hunks. A write to `DeckSelect.vue` warned of an external edit; the
file was re-read and the peer's `errorFor(position)` (their N3.20) confirmed intact. No peer hunk was
removed. Per-hunk attribution for `TraineeCatalog.vue` from the earlier pass still stands: my hunk is
`@@ -287` only.

---

## 6. Verification actually run

Every command below was executed; output quoted as observed at the times run.

| Command | Result |
|---|---|
| `npm run typecheck` | **exit 0** (run ≥6 times, including after the final N3.15 edit) |
| `npm run lint:ui` | **exit 0**; arbitrary-value debt at baseline **209** |
| `npm run build` | ✓ built (many times — the browser harness hard-fails on a stale manifest) |
| `pest RenderedCopyHygieneTest + UiHygieneTest` | **13 passed, 43 assertions** |
| `pest RenderedCopyHygieneTest` alone | **8 passed** (was 1 failed/7) — P0.1 |
| `playwright  deck-select` | **21 passed** — P0.2 |
| `playwright  scenario-detail-order` | **5 passed** — N3.15 |
| `playwright  snapshot` | **17 passed** — N3.33 |
| `playwright  legacy-deck-steps + skills-planner` | **36 passed** |
| `playwright  preflight + cockpit` | **47 passed** |
| `playwright  deck-select + database + help + veterans + training-detail` | **94 passed** |

**Full Pest suite and full Playwright suite were deliberately NOT run** (brief §10). No claim is made
about them. WebKit/Firefox were not run; only the configured `chromium` project exists.

### Lint-debt discipline
One earlier pass added 5 arbitrary Tailwind values and tripped the `debt-increase` ratchet at 209+1;
all five were replaced with tokens (`tracking-wide`, `max-w-prose`). This session added none — the two
table fixes **remove** hand-rolled values in favour of the `t` primitive.

---

## 7. Deviations from the plan, stated plainly

1. **N3.4 was not "pure removal".** Gate on `isActive` + keep `Run deck`. Two test-pinned facts made
   literal removal a regression. Reasoning is in the code.
2. **N3.22 was attempted and reverted.** The plan's premise (Legacy is optional, so show `Skipped`) is
   contradicted by a recorded test contract (BO-006). Not resolved by me.
3. **N3.31 not implemented.** Contract incompatible; implementing it would surface a costume-card
   concept Snapshot does not collect.
4. **P0.2 excluded `rect` drift.** Applied the ruling `database-parity.spec.ts:74` already records,
   rather than asserting the wireframe's palette against `DESIGN.md`'s. A vacuous
   `expect(...).toBeGreaterThanOrEqual(0)` I briefly wrote was **removed**, not left in.
5. **P0.3/P0.4 changed tests, not the UI.** In each case evidence showed the assertion was stale and
   the UI correct (ARIA-correct combobox; legitimate new ledger row; logically impossible re-click).

---

## 8. What could not be inspected

- The **full** Pest and Playwright suites (excluded by the brief).
- `docs/audits/2026-11-20-ui-ux-application-assessment.md` and `docs/design-research/*.jfif`
  (untracked, peer-owned, not read).
- The peer session's intent; only its **written files** were observed.
- `SCREEN_SPEC.md` / `DESIGN.md` currency for the changed screens — deferred to **N3.39** and
  **N6.17**, neither attempted. **Copy changed** in this pass (Help keyboard row, Veterans status
  labels, Races absence reasons, SkillsPlanner hint-cost sentences), so those documents are behind the
  tree.
- Non-Chromium browsers.

---

## 9. Relation to the peer sitrep

`docs/sitrep/sitrep-2026-11-21-frontend-uiux-session-handoff.md` (untracked, peer-authored) covers the
same tree from a different scope: the `trainerdesk-complete-uiux-implementation-plan-2026-11-20.md`
family, N4.8/N4.9 gate work, and a peer's N6.3 backend slice. **Complementary, not duplicate.**

Where they intersect:
- It lists `Help.vue` N6.15, `TrainingDetail.vue` gains-absence and `UnityCupEditor.vue` as
  "unattributed further peer work". Those are the **earlier pass's** work, documented in
  `sitrep-frontend-slices-2026-11-20.md`.
- It independently identifies the same `1–5` dash conflict. **Two sessions agreeing makes it
  confirmed, not anecdotal** — and this session resolved it, with the copy now ASCII.
- It reports `TrainingDetail.vue:828` `UiHygieneTest` red as peer work it would not fix. That was
  fixed in the earlier pass; the gate is green (13 passed).

Precedence: **this report owns** the P0 resolutions, N3.4, N3.15, N3.22's revert and N3.33. The peer
sitrep owns its N4.8/N4.9 and N6.3 backend work.

---

## 10. Outstanding work, dependency-ordered

1. **N3.38 — Veteran detail restructuring.** `Veterans/Show.vue` is clean of conflict and the work is
   self-contained: split career summary from metadata, give Trainer notes their own section, group
   "Built from" + "Used as parent in" under one lineage heading, and lead the ancestry empty state with
   "No ancestry was recorded for this source run." Needs an `HFW-OVERRIDE` note for the card-shape
   change (recordable as a page comment). Preserve the long-notes pin at `veterans.spec.ts:291`.
   *Verify:* `veterans.spec.ts` green + a new case pinning the split.
2. **N3.7 — Race decision UX.** Selection visibility, action hierarchy, aptitude-axis labels,
   layout stretching, Enter-vs-Record-Result distinction, honest consecutive-race copy. The
   consecutive count needs a server prop — that half goes to the supporting agent; do not compute it in
   Vue. *Verify:* `career-race-decision.spec.ts` + `RaceDecisionTest`.
3. **N3.35 — validation summaries** and **N3.36 — live Snapshot summary.** N3.35's
   refusal-state-persistence half is backend (supporting agent); N3.36 must show only trainee,
   scenario, position (if authoritative), and the three state counts — no derived stats.
4. **N3.22** — needs a **test-owner ruling** before any retry: either `Legacy Missing` stays (recorded
   contract) or the contract changes. Do not re-attempt blind.
5. **N3.31** — needs a Snapshot-specific accessible selector, or a `TraineeCombobox` variant that does
   not require costume cards. Contract decision belongs to the owner/supporting agent.
6. **Owner-gated:** N3.16, N3.28, N3.40, N6.18. **Do not execute.**
7. **N3.39 / N6.17** — doc currency and theme/reflow verification for the changed screens.

### Checks required for each
- Any `resources/js/**` edit → `npm run typecheck` && `npm run lint:ui` (expect debt **209**) && `npm run build`.
- Any changed screen → its focused spec, session-private:
  `PLAYWRIGHT_PORT=<new> PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-<port>.sqlite npm run test:browser -- <spec>`.
  **Create the scratch directory first** — the harness `writeFileSync`s into it and errors before any
  test if it is missing. Never reuse ports 8411-8426 or `.scratch-uma/fca-2026-11-21/`.
- Any visible `N/A` added → `UiHygieneTest` (fails a bare `N/A` with no `title`).
- Any shipped copy with a dash → `RenderedCopyHygieneTest` (bans **both** U+2013 and U+2014).

---

## 11. Environment notes worth carrying

- **Session scratch:** `.scratch-uma/fca-2026-11-21/` (browser DBs 8411-8426). Safe to delete.
- **The browser harness refuses a stale `public/build`** (`global-setup.ts:184-208`) and that failure
  exits **0** — read the output, not the exit code.
- **Inertia passes prop keys verbatim.** A camelCase declaration of a snake_case payload key yields
  `undefined`, and a `!== null` gate silently passes. This cost one round on N3.15.
- **`.note` is `display:flex`** (`app.css:1647`); a `<b>` as a direct child is blockified. Wrap prose
  in a `<span>` like the wireframes do rather than overriding `display`.
- **`table.t` is the house data-table primitive** (`app.css:1245`, ported from `shell.css:576`); use it
  instead of hand-rolling `text-sm`/`py-*` tables.
- **Reading a failure without a browser:** `test-results/<slug>/error-context.md` holds the live DOM
  accessibility tree. This is how the N3.15 wrong-href and the Races row count were diagnosed.
- **Contention:** with the peer's php/node processes live, generic tool calls intermittently returned
  "pre_tool_call plugin callback timed out". Retries succeeded; not a code fault.

---

## 12. Handoff summary

**Verified state:** all four P0 blockers resolved. N3.4 (gated dedupe), N3.15 (team door, now that the
backend ships `team_url`), N3.33 (bulk unknown action) and the earlier pass's ten slices are
implemented in the working tree on top of `1b987cec`, uncommitted. Gates: `typecheck` 0, `lint:ui` 0 at
debt 209, `build` clean, PHP hygiene **13 passed**, browser **94 / 47 / 36 / 21 / 17 / 5 passed** across
the six runs. No backend, database, route or config file was modified by this session.

**Remaining:** N3.38, N3.7 (half backend), N3.35 (half backend), N3.36, plus N3.39/N6.17 doc work.
N3.16/N3.28/N3.40/N6.18 stay owner-gated.

**Blockers:** N3.22 needs a test-owner ruling (recorded contract says `Legacy Missing`); N3.31 needs a
TraineeCombobox contract that does not require costume cards.

**Exact next step:** implement **N3.38** in `resources/js/pages/Veterans/Show.vue` — split the Record
into career summary vs metadata, lift Trainer notes to their own section, group "Built from" and "Used
as parent in" under one lineage heading with the two conditions kept distinct, and add the
`HFW-OVERRIDE` comment for the card-shape change. Verify with
`PLAYWRIGHT_PORT=8427 PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-8427.sqlite npm run test:browser -- tests/browser/veterans.spec.ts`
after `mkdir -p` on that directory and `npm run build`. Keep the long-notes pin at `:291` green.
