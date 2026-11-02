# Audit report — SCR-CAR-001 Dashboard (Trainer Desk 2.0 landing screen)

## 1. Identity

- Audit id: AUD-20261101-001
- Date: 2026-11-01
- Mode: **M2 screen audit** (A11y overlay applied as a separate track, Phase 5)
- Scope: `GET /` (`home`), `app/Http/Controllers/DashboardController.php`, `resources/js/pages/Dashboard.vue`, against `SCREEN_SPEC.md` §SCR-CAR-001, `tests/Feature/DashboardTest.php`, `tests/browser/dashboard.spec.ts`.
- States covered: **empty** (no active career; no veterans; no turn logged; scenario resource absent; all cards hidden), **populated** (active career + veterans), **loading** (Inertia visit in flight), **error** (visit failed). Both themes read from source; four widths (320/390/768/1280) named but not measured; keyboard-only read from source.
- Out of scope: backend behaviour beyond the controller's own queries, the other `SCR-CAR-*` screens, performance, content strategy, AAA criteria, the `eva01` third theme, and any fix.
- Frameworks applied: `design-framework-index-2026-10-21.md` first, then Heuristics, Shneiderman, Tognazzini, Gerhardt-Powals, IA, Gestalt, Norman, Laws of UX, Laws of UI, WCAG 2.2 AA, per §4 routing. Read from the files this run, not from memory.
- Rendered evidence: **FALSE**. Server-rendered Inertia HTML/props were fetched from `http://127.0.0.1:8000/` (see §4) and structural/copy claims below are `OBSERVED_SOURCE` against that document; visual, contrast, target-size and reflow claims are `UNMEASURED` because Chromium cannot launch in this sandbox (`spawn EPERM`).

## 2. Executive summary

- **F-01 — the type scale on the Dashboard is not a scale.** At least nine distinct computed sizes appear in `Dashboard.vue` (`text-[26px]`, `text-[19px]`, `text-[14px]`, `text-[12.5px]`, `text-[12px]`, `text-[11.5px]`, `text-[10px]`, plus `text-sm`/`text-xs`/`text-base`), against `UI-1`'s three (page, section, body). Same shape as the deck audit's F-05; here it is the landing screen, so it is seen first.
- **F-02 — the Japanese trainee name is printed without `lang="ja"`** in both the resume card (`Dashboard.vue:290,293`) and the veterans table (`:542`), against `WCAG 3.1.2` (Language of Parts, AA) and `H2`. The `SCREEN_SPEC` §SCR-CAR-001 promises the name in `lang="ja"`; the view does not set it.
- **F-03 — the Independent Training countdown does not count down.** `indepRemaining` is a `computed` over `Date.now()` with no timer, so the "N:NN remaining" string is frozen at the render that produced it for the whole 50-minute window.
- **F-06 — the empty Veterans state prints nothing.** When `recentVeterans.rows` is empty the table is omitted and only the footer ("0 veterans · 0 shown") remains; `SCREEN_SPEC` §SCR-CAR-001 promises an absence message and a path out. IA-5 (exemplar) and H9 are the rules.
- **Largest unmeasured risk:** reflow at 320 px. The page uses `.g-side`/`.g-4`/`.g-3` grids and a table with `overflow-x-auto`; no width was measured, so a horizontal-scroll failure like the deck audit's F-01 cannot be ruled out or claimed here.

## 3. Scope and coverage

| Surface | empty | populated | error | loading | light | dark | 1280 | 768 | 390 | 320 | keyboard |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Page head / lede | yes | yes | yes | yes | src | src | named | named | named | named | src |
| Active-career card | yes | yes | n/a | n/a | src | src | named | named | named | named | src |
| Needs-you column | yes (all cards hidden) | yes | n/a | n/a | src | src | named | named | named | named | src |
| KPI strip | yes | yes | n/a | n/a | src | src | named | named | named | named | src |
| Recent veterans | yes | yes | n/a | n/a | src | src | named | named | named | named | src |
| Quick actions | yes | yes | n/a | n/a | src | src | named | named | named | named | src |

Framework coverage (applied and produced findings): `UI-1`, `H2`/`WCAG 3.1.2`, `H1`/`TOG-1`/`TOG-3`, `GP-3`/`H6`, `IA-5`, `H1`, `H4`/`SHR-1`, `UX-1`, `H9`, `H8`.
Applied, measured clean from source (no finding): `H4`/`SHR-1` consistency of components; `H5`/`NRM-3` (refused writes leave state — none here); `SHR-7` (energy, turn, resource, team rank on the decision screen); `GP-1`/`GP-3` (grouped and labelled sums); `GT-2`/`UI-4` (absent values carry a word and a title); `IA-1` (directly reachable, own crumb); `IA-2` (banded quick actions); `WCAG 2.1.1`/`2.4.7`/`4.1.2`/`4.1.3` (links, buttons, labels, status regions present); `WCAG 1.4.1` (stale state carries word + border).
`N/A` by the framework's own list: `2.5.7` (no drag), `2.1.4` (no accelerators), `3.3.8` (no auth), `1.2`/`1.4.2`/`2.3` (no media/audio/flashing), `SHR-4`/`SHR-6` (no multi-step flow or committed turn on this screen).
Not reached: M3/M4 phases (single screen), `IA-3`/`IA-6` (structure between screens out of scope), AAA criteria.

## 4. Evidence status and limits

- `RENDERED_EVIDENCE = FALSE`. Chromium cannot launch in this sandbox (`spawn EPERM`); no screenshot, DOM measurement, contrast ratio, target-size measurement or reflow pass was run.
- Server-rendered HTML and the Inertia props were fetched with webfetch from `http://127.0.0.1:8000/` and are the basis for every `OBSERVED_SOURCE` claim below (props observed: `activeCareerCount: 2`, `finishedRunCount: 7`, `globalScenarioCount: 4`, `catalogueTrainees: 67`, `trainees: 71` unused, `reviewPending: 67`, `verified_at: 2026-09-27`). Structural and copy claims are `OBSERVED_SOURCE`; visual claims are `UNMEASURED`.
- Contrast, target size, reflow, 200% resize and text-spacing override were not measured; all are in §6.
- Provenance caveats carried (§3.1 of the audit prompt): Gerhardt-Powals primary text paywalled, items 5/6 an unconfirmed split (nothing here rests on that boundary); IA prose unread, only names/summaries are the article's own (IA-5 used only as its derived sentence); Norman's book not fetched, quotes from the encyclopedia entry; Tognazzini sixteen are the original edition, 2014 count unverified; Laws of UX holds 29 (Zeigarnik from its own page); Laws of UI holds 13 and publishes Closure and Continuity; no "Law of Experience" (it is Mental Model, UX-17).
- Attribution rule applied: Closure/Continuity cited to `uilaws-2026-10-21.md`; Common Fate cited to `gestalt-principles-2026-10-21.md`; Fitts/Hick/Jakob cited to `laws-of-ux-2026-10-21.md`; 44 px described as a house rule exceeding 2.5.8, never as conformance.

## 5. Findings

| ID | Rule ID | Framework | Surface / state | Observation | Evidence status | Evidence location | Severity | Confidence | Recommendation |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| F-01 | `UI-1` | Laws of UI | Whole screen, populated and empty, all widths | At least nine distinct type sizes on one screen: `text-[26px]`, `text-[19px]`, `text-[14px]`, `text-[12.5px]`, `text-[12px]`, `text-[11.5px]`, `text-[10px]`, plus `text-sm`/`text-xs`/`text-base` (and `text-[11px]` in the tile suffix). `UI-1` permits three: page, section, body. | OBSERVED_SOURCE | `resources/js/pages/Dashboard.vue:290,293,376,409-413,426,435-436,451-452,476-478,485,491,497,499,505,507,525,528-531,564,592,595` | 2 | Medium | Fold the 11.5/12/12.5/10 steps into the 12 px body step at the token layer, then re-measure. Acceptance: no more than three distinct computed sizes under `main`. |
| F-02 | `WCAG 3.1.2` (AA) (secondary: `H2`) | WCAG 2.2 | Resume card and veterans table, populated, all widths | The trainee name and its Japanese counterpart are printed without `lang="ja"`: the resume card's name span (`:290`) and meta line (`:293`, which joins `trainee_ja`), and the veterans table cell (`:542`, `{{ veteran.trainee_ja }}`). `SCREEN_SPEC` §SCR-CAR-001 promises the name in `lang="ja"`; the view does not set it. | OBSERVED_SOURCE | `resources/js/pages/Dashboard.vue:290,293,542` | 2 | High | Add `lang="ja"` to the spans carrying `trainee_ja` (and to the trainee name where the source is Japanese). Acceptance: every Japanese-name node carries `lang="ja"`. |
| F-03 | `H1` (secondary: `TOG-1`, `TOG-3`) | Heuristics / Tognazzini | Resume card, populated, all widths | Independent Training's "N:NN remaining" is a `computed` over `Date.now()` with no timer (`indepRemaining`, `:149`), so the string is frozen at the render that produced it for the whole 50-minute window. A Trainer watching the card sees a stale countdown. | OBSERVED_SOURCE | `resources/js/pages/Dashboard.vue:149-163,309` | 2 | High | Drive the countdown from a `setInterval` (or recompute on the visit's `onSuccess`), clearing on unmount. Acceptance: the value advances each second while the window is open. |
| F-04 | `GP-3` (secondary: `H6`, `H1`) | Gerhardt-Powals | Resume card, populated, all widths | The card prints two turn figures for one career — `turn` (recorded, `:282`) and `cockpit_turn` (imported position, `:278`) — and explains the disagreement only in the trailing note (`careerNote`, `:195-222`). A first-time reader must read the note to know which number is which. | OBSERVED_SOURCE | `resources/js/pages/Dashboard.vue:195-222,278-282,376` | 2 | Medium | Collapse to one labelled figure, or label each figure's source inline ("recorded" vs "imported") so the note is not the only decoder. Acceptance: the two numbers never appear unlabelled. |
| F-05 | `IA-5` (secondary: `H9`) | Information architecture | Resume card, empty state (no active career), all widths | The empty card states the absence ("Not recorded: no career is running.") and offers two actions, but shows no example of what a running career looks like. `IA-5` asks for an example of the contents; `SCREEN_SPEC` §SCR-CAR-001 asks for one sentence on what a career holds (present) plus the action. | OBSERVED_SOURCE | `resources/js/pages/Dashboard.vue:370-388` | 2 | Medium | Add one example line or a thumbnail of a career strip to the empty card. Acceptance: the empty card shows what a populated card contains. |
| F-06 | `IA-5` (secondary: `H9`) | Information architecture | Recent veterans, empty state (no veterans), all widths | When `recentVeterans.rows` is empty the table is omitted entirely (`v-if`, `:518`) and only the footer ("0 veterans · 0 shown") remains; there is no absence message and no path out, although `SCREEN_SPEC` §SCR-CAR-001 promises both ("finish a run, set it to Completed, save it from the Legacy Lab"). | OBSERVED_SOURCE | `resources/js/pages/Dashboard.vue:516-572` | 2 | High | Render an empty-state line naming the absence and the path out, plus the library link. Acceptance: with `total === 0` the card prints the absence message and the door. |
| F-07 | `H1` (secondary: `SHR-3`) | Heuristics | KPI strip, populated with more than one active career, all widths | The "Careers running" tile (`:476-479`) names only the newest active career, while `activeCareerCount` can exceed one (observed 2 in the fetched props) and the lede carries the count. The other live careers are reachable only through "All careers". | OBSERVED_SOURCE | `resources/js/pages/Dashboard.vue:476-479`, controller `activeCareerCount` | 2 | Medium | Name the count in the tile subline when more than one is live, or link to the filtered list. Acceptance: with `activeCareerCount > 1` the tile names how many are live. |
| F-08 | `H2` (secondary: `H8`) | Heuristics | Quick actions card, populated/empty, all widths | The card ships a developer-facing note inside a `<details>` ("Why four destinations", `:590-596`): "The running app's sidebar carries ten flat items; this dashboard is the same set, grouped, with one primary action." That is internal commentary in shipped copy. | OBSERVED_SOURCE | `resources/js/pages/Dashboard.vue:588-596` | 2 | High | Remove the note or replace it with Trainer-facing copy. Acceptance: no developer-facing commentary in the rendered page. |
| F-09 | `H4` (secondary: `SHR-1`) | Heuristics | Settings coupling, all states | The "Needs you" column is gated by `v-show="!isCardHidden('roster') && !isCardHidden('data_status')"` (`:394`), so hiding either the "Roster" or the "Data status" toggle removes the entire column, including the data-verification tile, with no per-card control. Both toggles are documented as switching this column. | OBSERVED_SOURCE | `resources/js/pages/Dashboard.vue:126-138,394-396` | 2 | High | Decouple the column's visibility from either single toggle, or rename the toggles to match what they hide. Acceptance: hiding "Roster" does not remove the data-status tile. |
| F-10 | `UX-1` (explicitly **not** `WCAG 2.5.8`) | Laws of UX / Tognazzini | Independent Training "Give up" control, populated, all widths | The "Give up" button is `min-h-9` (`:316`, ~36 px), below the 44 px house rule (`UX-1`), though above 2.5.8's 24 px floor. It is a secondary action in a dense row, so no conformance failure is claimed. | OBSERVED_SOURCE | `resources/js/pages/Dashboard.vue:316` | 1 | High | Leave as is if the dense-row form stays named (as `AbsenceValue`'s compact case does); otherwise raise to `min-h-11`. Acceptance: the exception is recorded, not silent. |
| F-11 | `H1` (secondary: `TOG-1`) | Heuristics | Page-level loading, all widths | While an Inertia visit started from this screen is in flight the only feedback is a `<p role="status">Loading...</p>` (`:246`) with no indication of what is loading. | OBSERVED_SOURCE | `resources/js/pages/Dashboard.vue:246` | 1 | High | Name the destination in the status text (e.g. "Loading the page you chose…"). Acceptance: the status text names what is in flight. |
| F-12 | `H9` (secondary: `WCAG 3.3.3`) | Heuristics | Page-level error, all widths | A failed visit renders "That page could not be loaded. Try again, or open it from the navigation." (`:249-254`) with no retry control; the advice is textual only. | OBSERVED_SOURCE | `resources/js/pages/Dashboard.vue:249-254` | 1 | Medium | Add a retry action (or make "Try again" a button that re-issues the visit). Acceptance: a failed visit offers a one-click retry. |

No finding is filed twice for one defect; each row carries at most one primary rule.

## 6. Unmeasured register

| Claim | Why unmeasured | What would settle it |
| --- | --- | --- |
| Dark- and light-theme body text meets 4.5:1 on every surface | No browser; intended ratios in `app.css` only | Rendered contrast pass in both themes at all four widths |
| Non-text contrast (borders, focus rings, risk edge) meets 3:1 | No browser | Rendered non-text contrast measurement |
| Reflow at 320 px produces no two-dimensional scrolling | No browser; `.g-side`/`.g-4`/`.g-3` grids and `overflow-x-auto` table not measured | Rendered reflow pass at 320 px, populated, both themes |
| Target sizes of the 44 px house rule hold on every control | No browser; boxes not measured | Rendered target-size sweep |
| 200% text resize and the 1.4.12 text-spacing override lose nothing | No resize pass run | Resize + text-spacing pass at 320 px |
| Screen-reader announcement of the loading/error status and the veterans table | No AT attached | NVDA/VoiceOver pass over populated and empty states |
| Whether the `Pill tone="accent"` and the primary button share a hue (one accent per screen) | Colour not measured | Computed-style colour comparison in both themes |
| ArtworkSlot `alt=""` on portraits is acceptable because the name is text | Not resolved by source alone | AT pass + a decision on whether a portrait is decorative |
| Whether the `needsYou` `v-show` leaves a stale `aria-labelledby` when hidden | Not measured | Accessibility-tree inspection with both toggles off |

## 7. Conflict and gap log (not findings)

Settlements applied rather than filed as defects:

- **44 px vs 2.5.8.** F-10 is filed as the house rule (`UX-1`) and explicitly not as conformance, per `wcag-2.2-accessibility-2026-10-21.md`: the 44 px rule exceeds 2.5.8. No conformance claim is made.
- **Density vs minimalism.** The dashboard is deliberately dense (HFW clone); `H8` is about signal against noise, and every element here carries a fact. No finding.
- **Closure vs honesty.** The empty ancestor slot / "not recorded" tiles look like closed shapes but carry the word; `GT-2`/`UI-4` hold. No finding.
- **Feedback timing.** The 50 ms acknowledgement and 400 ms Doherty response are read as the index reads them; the only acknowledgement surface here is the `role="status"` loading line, and its latency is unmeasured (§6).
- **Consistency vs exceptions.** The three recorded exceptions (dark theme's three deviations, the Database density) are not on this screen. No finding.
- **Turn-count disagreement.** F-04 names the two numbers; the underlying cause (imported position vs recorded turns) is a data-model defect the controller already labels as known. The screen's disclosure is the correct response; the defect belongs to the run model, not to this screen's copy.
- **`SCREEN_SPEC` drift.** §SCR-CAR-001 promises a ruleset row and three catalogue counts on the data-status badge; the view prints neither (ruleset is absent from the template; `counts.trainees` = 71 is sent but never rendered). This is a documentation/contract drift rather than a screen defect, and is recorded here, not filed as a finding.
- **Framework-set defects noted.** `SCREEN_SPEC.md` §SCR-CAR-001 and `DashboardController` disagree on the data-status badge's contents (above). `wcag-2.2-accessibility-2026-10-21.md` omits `3.1.2` (Language of Parts) from its enumerated subset although it is a standard AA criterion; F-02 cites it directly.

Gaps confirmed in the register rather than filed per screen: `H3` no undo after a committed turn (not applicable — no turn is committed here), `H7` no accelerators, `H10` no help surface. On this screen none of the three becomes a finding beyond what is already registered; F-11/F-12 touch the same area but are screen-level.

## 8. Recommendations

1. **F-01** (M): take the type scale to the token layer; three steps. Check: ≤3 distinct computed sizes under `main`.
2. **F-02** (S): add `lang="ja"` to Japanese-name nodes. Check: every `trainee_ja` node carries `lang="ja"`.
3. **F-03** (S): timer-driven countdown. Check: value advances each second.
4. **F-04** (S): label each turn figure's source. Check: no unlabelled turn number.
5. **F-06** (S): empty veterans message + door. Check: `total === 0` prints the absence line.
6. **F-05** (M): exemplar in the empty career card. Check: empty card shows a populated example.
7. **F-07** (S): name the live count when >1. Check: `activeCareerCount > 1` names it.
8. **F-08** (S): remove developer note. Check: no internal commentary in the page.
9. **F-09** (S): decouple the column's visibility. Check: hiding "Roster" keeps data status.
10. **F-10** needs no change; recorded so the 44 px rule is not later misread as 2.5.8.
11. **F-11** (S): name the loading target. Check: status text names the in-flight page.
12. **F-12** (S): retry control on failure. Check: failed visit offers one-click retry.

## 9. Change-audit re-check

Not owed (mode is M2). The screen has not changed in this pass; no commit is being made.

## 10. Exit checklist

- Scope, mode, surfaces, states, viewports and themes stated.
- Framework routing recorded, narrowest rule per question, secondaries listed.
- §3.1 provenance caveats carried.
- Evidence capability recorded as FALSE; `spawn EPERM` named; server-rendered HTML fetch noted; no rendered claim made.
- Every finding has a rule ID, named surface and state, specific observation, evidence status.
- No severity 3/4 finding rests on inference (the highest severity here is 2).
- Severity and confidence are separate axes.
- No defect filed twice.
- Known conflicts logged as settlements.
- Gaps left in the register.
- Unmeasured register complete with settlers.
- Template followed.
- No conformance claimed beyond criteria tested; no rendered observation claimed.
- Report does not modify app code.
