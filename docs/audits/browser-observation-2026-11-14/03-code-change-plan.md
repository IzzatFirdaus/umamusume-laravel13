# 03 — Code-change plan

Ordered by journey unblocking first, then severity, then confidence. Every slice names the screens, the findings
and the code surface. Findings that need a decision I cannot make are in `04-excluded-findings.md`, not here.

Slices 1–4 are all small, single-file fixes. Slice 1 is first because a console error on the first screen after
career creation sits on the critical path of every new career; slices 5–6 are polish on the setup path a trainer
walks once per career.

---

## Slice 1 — Guard the Cockpit's null panel read

- **Affects:** Cockpit `/training-runs/{run}/cockpit` (and `/training-runs/{run}`, which redirects to it)
- **Findings:** BO-002
- **Code surface:** the Cockpit page component under `resources/js/pages/Career/` (built chunk `Cockpit-*.js`,
  error at `1:32866`), at the property read on `.warning_message`.
- **Why first:** this is the landing screen immediately after a career is created, and it errors on **every** run
  that has no turn recorded — i.e. every new career's first paint. Severity is Medium because the page still
  renders, but it is the highest-traffic defect in the register.
- **Acceptance:** a run with 0 turns loads the Cockpit with an empty console.

## Slice 2 — Derive the Preflight attention count from its own table

- **Affects:** Preflight `/career/setup/preflight`
- **Findings:** BO-006
- **Code surface:** the Preflight page component — the "N steps need attention" string, derived from the same row
  states the CHECK table already renders; plus the missing separator in the Deck row's `…carry a card` + "Fix this".
- **Acceptance:** the count equals the rows that are not `Pass`, in both the empty-draft and filled-draft states.

## Slice 3 — One noun for the stat ceiling: `cap`, not `floor`

- **Affects:** Cockpit `/training-runs/{run}/cockpit`; the number originates from `ScenarioCaps`
- **Findings:** BO-007
- **Code surface:** the Cockpit stat-rail component's label (`floor` → `cap`). The **arithmetic** is already
  correct and already owned by one class — this is a label change, and must not touch the computation.
- **Acceptance:** the Cockpit rail and the Scenario detail's "Published stat caps" table use the same noun for the
  same number on the same run.

## Slice 4 — Fix the trainee profile door's parameter

- **Affects:** wizard Trainee step `/career/setup/trainee`
- **Findings:** BO-001
- **Code surface:** the trainee-profile route binding (`/career/setup/trainee/{umamusume}`) and whichever of the
  routes/controller pair resolves the parameter. The in-app doors already emit the working `/trainee/33` form, so
  either bind on the slug — matching `/umamusume/{slug}`, which works — or make the id the documented form and
  reject the slug deliberately.
- **Acceptance:** the URL a reader would construct from the route parameter resolves to the form chooser, or the
  route no longer advertises a parameter it does not accept.

## Slice 5 — Remove the bare colon from inline absence cells

- **Affects:** Careers list `/training-runs` (POSITION column)
- **Findings:** BO-009
- **Code surface:** the runs-table cell renderer that composes `N/A` + separator + sentence. Match the Cockpit's
  pattern: the token is visible, the sentence is the disclosure.
- **Acceptance:** the cell prints `N/A` with the sentence available as a `title`, no visible colon.

## Slice 6 — Mark the three required selects on Build target

- **Affects:** wizard Build target `/career/setup/target`
- **Findings:** BO-010
- **Code surface:** the Build-target form's Purpose/Surface/Style field group — a required marker or a one-line
  note above the three.
- **Acceptance:** a trainer can tell which fields block the step before pressing continue. The validator is already
  correct and must not change.

## Slice 7 — Scope-annotate the Dashboard KPI counts

- **Affects:** Dashboard `/`
- **Findings:** BO-008
- **Code surface:** the Dashboard KPI tile component (and the wizard trainee step's `66 trainees are catalogued`
  sentence, which is a third figure for the same fact).
- **Acceptance:** each count states its scope, and the trainee figure a trainer sees on the Dashboard is
  reconcilable with the catalogue it links to.

## Slice 8 — One focus-ring token

- **Affects:** candidate detail `/review/{candidate}` (the two outlier components; the class is app-wide)
- **Findings:** BO-011
- **Code surface:** the focus style on the "Back to the queue" and summary/`tabindex=0` elements, brought onto the
  shared focus token already used by the other 17 stops.
- **Acceptance:** 20 consecutive tab stops on `/review/10` report one `outline-color`.

## Slice 9 — Non-text contrast of the dark `To do` pill

- **Affects:** Dashboard `/` in dark theme
- **Findings:** BO-004
- **Code surface:** the risk token's dark value, or the badge's size step.
- **Why last:** it is one measured pair at 4.15:1 against a 4.5:1 target on a 12px badge, and orange-on-dark is a
  deliberate hue. If the owner prefers the hue, this slice is correctly skipped — that is a ruling, not a bug.
  Recorded as a slice so the option is visible, not because the palette is wrong.

---

## Deliberately not slices

`BO-003` (factory row in the catalogue) is **partially** code-fixable — the badge and count wording are code, the
row itself is data — and is excluded from this plan as EX-02. `BO-005` and `BO-012` are states/absences, not fixes
(EX-03, EX-04).

Nothing in this plan requires a schema change, an ADR, an external-config change, or a new dependency.

---

## Next pass: seed, then walk both states

Not a code slice — a precondition for the next audit, recorded here because this plan is where the next reader
looks. The reference surface already exists in the tree and this pass could not use it:

- **`docs/[Rosy_Dreams]Rice_Shower_Unity-Cup.md`** — 68 `[Global]` client frames captured 2026-10-03 (67 distinct;
  one pair in the 17:07 set is byte-identical). A real Unity Cup career read off the client, frame by frame:
  live caps 1325/1322/1368/1308/1800 against scenario floors of 1300/1800, an 8th-place league finish, six blue
  sparks across a six-ancestor tree, a 20-member roster. It is the **ground truth** the "domain fidelity" checklist
  in the brief asks for, and it is cited by the seeder that reproduces the same run.
- **`database/seeders/RosyDreamsRiceShowerSeeder.php`** — imports that exact run: `[Rosy Dreams] Rice Shower`,
  Unity Cup, `Active`, Senior Early October, turn 67 of 72, `team_league_placement = 8`, races and skills and a
  six-card deck populated. Active, not Completed: it cannot reach the Result screen.

Three-way comparison the next pass can run, which this one could not:

| Surface | Establishes |
|---|---|
| the doc | what the client rendered (the numbers a trainer actually saw) |
| the seeder | what the app stored |
| the rendered page | what the app shows |

That turns BO-007's floor/cap confusion from a two-surface contradiction into a **three-way** one, and lets the
next pass test the doc's own claims against a rendered screen: the two-ceiling problem (scenario floor vs live
cap), the roster gap (20 members in the client, six deck cards in the app), and the league-placement bonus
(the doc shows `+30` at 8th; the app stores `team_league_placement = 8` and renders the placement with no bonus —
`Cockpit.vue:526` renders the number, `TeamCard.vue:33` prints it in a `<dd>`). **BO-007 is the one this pass
already measured; the other three need the seeded run and belong to the next pass.**

**Walk both states.** A populated career hides BO-002 (the null panel read fires only on a turn-less run); a
turn-less run hides every populated-panel defect. Neither walk substitutes for the other.
