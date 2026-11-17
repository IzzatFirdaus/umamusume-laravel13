# 01 — Findings register

**12 records · 12 active · 0 withdrawn.**

| Severity | Count |
|---|---|
| Critical | 0 |
| High | 0 |
| Medium | 1 |
| Low | 4 |
| Informational | 7 |

**By screen:** Cockpit 3 · Preflight 2 · Dashboard 2 · Careers list 1 · `/review/10` 1 · Trainee catalogue 1 ·
Preferences 1 · Wizard trainee step 1 · Timeline 1 · Support cards 1 (the last three share BO-011/BO-012 classes).

No Critical and no High findings survived verification. Three candidate findings were **withdrawn after
re-measurement** and are listed at the end, because a register that hides its retractions is not auditable.

---

## Medium

## BO-002 — The Cockpit throws a client-side TypeError on every load

- **Severity:** Medium
- **Screen / route:** `/training-runs/13/cockpit` (and therefore `/training-runs/13`, which redirects here)
- **Viewport:** both
- **Theme:** light
- **Preconditions:** any run with no recorded turn — a freshly created career.
- **Steps to reproduce:** open the Cockpit for a run with 0 turns.
- **Expected:** the screen renders with no errors in the console; it is the screen a trainer lands on immediately after creating a career.
- **Actual:** one console error per load:
  `TypeError: Cannot read properties of null (reading 'warning_message')` at
  `build/assets/Cockpit--iqvu67i.js:1:32866`. The page still renders — the `h1`, the stat rail and the advisor
  all paint — so this is a swallowed render error, not a blank screen. At the time of capture the word "warning"
  appeared nowhere in the rendered body (`bodyHasWarningWord: false`), which is consistent with a panel whose data
  is `null` for a turn-less run being read before the null is guarded.
- **Evidence:** `training-runs-13-cockpit-1440x900.png`, `training-runs-13-cockpit-390x844.png`
- **Can it be fixed in code?** yes — a null guard in the Cockpit page component around the object it reads
  `.warning_message` from (the built chunk is `Cockpit-*.js`; source is the Cockpit page under `resources/js/pages/Career/`).
- **Confidence:** **Observed.** The console text is quoted verbatim from the browser, twice, on two separate loads.

## BO-003 — A factory fixture row is published in the trainee catalogue as a real trainee

- **Severity:** Medium
- **Screen / route:** `/umamusume` (index), `/umamusume/aut-neque` (detail), `/database/trainees`
- **Viewport:** both
- **Theme:** light
- **Preconditions:** the dev database, as it stands.
- **Steps to reproduce:** open `/umamusume`; page through to the `A`-names.
- **Expected:** the catalogue lists characters from the Global release. A name like `Aut Neque` is not one.
- **Actual:** `Aut Neque` appears as an ordinary catalogue entry with a full detail page: 11 `h2` sections,
  a working route, and a "Released (Global)" status. The name is the second word-pair of the standard
  `lorem ipsum` filler ("aut neque" comes from the *Lorem ipsum* text), i.e. a seeder/factory artifact, and it is
  indistinguishable on screen from a real trainee. `72 trainees` is offered as the catalogue total, so a visitor
  counting the roster counts this row.
- **Evidence:** `umamusume-1440x900.png`, `umamusume-aut-neque-1440x900.png`, `umamusume-aut-neque-390x844.png`
- **Can it be fixed in code?** **partially** — the row exists in data, not markup, so the durable fix is a data
  reset (`/database/trainees` shows the same row). What *is* code-fixable is the surface that makes it look
  legitimate: a fixture row should not receive a "Released (Global)" badge, and the catalogue count should not
  advertise it. Owner decision needed on the data half — see `04-excluded-findings.md` EX-02.
- **Confidence:** **Observed** for the row and its detail page. **Inferred** for the provenance — the string is
  the filler-text word pair and the detail page carries no source citation, but I did not read the seeder to prove it.

## Low

## BO-006 — "5 steps need attention" is wrong twice on Preflight

- **Severity:** Low
- **Screen / route:** `/career/setup/preflight`
- **Viewport:** both
- **Theme:** light
- **Preconditions:** two states, both observed. (a) a fresh empty draft; (b) a draft with scenario, trainee and target filled.
- **Steps to reproduce:** open preflight on an empty draft, then on the filled draft used to create run #13.
- **Expected:** the sentence states how many of the five steps are actually unmet, and "[state] steps need attention"
  should agree with the CHECK table printed directly beneath it.
- **Actual:** **state (a):** the sentence reads `5 steps need attention` and the table agrees — all five read
  `Not entered`. **state (b):** scenario, trainee and target all read `Pass`, only `Legacy` (`Missing`) and `Deck`
  (`Warning`) do not, yet the sentence **still reads `5 steps need attention`**. The count is not derived from the
  rows. Separately, in state (b) the same table's Deck row renders `0 of six positions carry a cardFix this` —
  the "Fix this" link abuts the sentence with no space or separator.
- **Evidence:** `wizard-preflight-1440x900.png`, `career-setup-preflight-1440x900.png`, `career-setup-preflight-390x844.png`
- **Can it be fixed in code?** yes — the count is a computed string in the Preflight page; derive it from the same
  rows the table renders. The `cardFix this` run-together is a missing separator in that row's template.
- **Confidence:** **Observed** in both states, with the table's own `Pass`/`Missing`/`Warning` cells quoted.

## BO-001 — The trainee step's "Preview profile" door 404s when reached by slug

- **Severity:** Low
- **Screen / route:** `/career/setup/trainee/agnes-digital` (and any `{slug}` form)
- **Viewport:** both
- **Theme:** light
- **Preconditions:** wizard at step 2.
- **Steps to reproduce:** go to `/career/setup/trainee`, note the two doors a selected card carries
  ("Review Admire Vega's aptitudes and choose a form" and "Preview profile"), then follow either.
- **Expected:** both doors reach the trainee's form chooser.
- **Actual:** both doors carry the **correct** href, `/career/setup/trainee/33` — a numeric id, and that route works.
  But `/career/setup/trainee/agnes-digital`, the slug form that the route's `{umamusume}` parameter invites and that
  a user or a doc would naturally write, returns the app's 404 page (`That page does not exist`) with two
  failed-resource console errors. The route accepts a parameter whose only working value is the internal id.
- **Evidence:** `career-setup-trainee-agnes-digital-1440x900.png`, `career-setup-trainee-agnes-digital-390x844.png`,
  `wizard-trainee-profile-1440x900.png` (the working id form)
- **Can it be fixed in code?** yes — either bind the route on the slug (the catalogue's own key, `/umamusume/{slug}`)
  or print the id form as the canonical door and 404 the slug explicitly. Note the *in-app* doors are correct;
  this is a surface-consistency defect, not a broken click.
- **Confidence:** **Observed.** Following the in-app doors worked; the slug URL 404'd.

## BO-009 — Unrecorded values print as `N/A :` with a bare colon

- **Severity:** Low
- **Screen / route:** `/training-runs` (Position column); the pattern recurs wherever an absence sentence is printed inline
- **Viewport:** both
- **Theme:** light
- **Preconditions:** a run with 0 logged turns.
- **Steps to reproduce:** open `/training-runs`; read the POSITION cell of run #13.
- **Expected:** per the project's own copy rule, an unrecorded value renders as `N/A` with a `title` and no
  punctuation glued to it — the disclosure carries the sentence, the cell carries the token.
- **Actual:** the cell reads `N/A : no turn has been logged on this run yet.` — the token, then a space, a colon,
  then the sentence, all as visible body text. The visible colon reads as a typo rather than a separator.
- **Evidence:** `training-runs-1440x900.png`
- **Can it be fixed in code?** yes — the cell renders token + separator + sentence; drop the separator or move the
  sentence into the `title`. (The Cockpit's stat rail does this correctly: `N/A` with
  `title="No turn has recorded this stat yet."` and no visible colon — cited in BO-007's evidence.)
- **Confidence:** **Observed.** The cell text was read directly.

## BO-007 — The stat rail says `floor` where the scenario model says these are `caps`

- **Severity:** Low
- **Screen / route:** `/training-runs/13/cockpit` vs `/training-runs/13/scenario` and `/database/scenarios`
- **Viewport:** both
- **Theme:** light
- **Preconditions:** a Unity Cup run.
- **Steps to reproduce:** read the Cockpit stat rail, then the Scenario detail's "Published stat caps" table.
- **Expected:** one name for one concept. The number `1,300` is the scenario's **stat cap** (base 1,200 + 100 bonus).
- **Actual:** the Cockpit prints `Speed N/A / 1,300 floor`, the same for Stamina/Power/Guts, and `Wit N/A / 1,800 floor`.
  The Scenario detail prints the identical numbers under `Published stat caps ... Stat Base Bonus Cap / Speed 1200 100 1300`
  and its own prose calls them "caps" (`July 1, 2026 rework raised caps (1200→1300 per stat, Wit to 1800)`).
  A floor and a cap are opposites; a trainer reading `1,300 floor` would understand 1,300 as a minimum, which
  inverts the meaning of the number. The values agree; only the noun is wrong.
- **Evidence:** `training-runs-13-cockpit-1440x900.png`, `training-runs-13-scenario-1440x900.png`, `database-scenarios-1440x900.png`
- **Can it be fixed in code?** yes — one label in the Cockpit stat component; `ScenarioCaps` is already the single
  owner of the arithmetic, so only the noun is at issue.
- **Confidence:** **Observed.** Both surfaces were read; the numbers match and the nouns contradict.

## Informational

## BO-004 — Dark theme: the `To do` pill reads 4.15:1

- **Severity:** Informational
- **Screen / route:** `/` (Get running panel)
- **Viewport:** 1440x900
- **Theme:** **dark** (selected, saved, verified `html[data-theme="dark"]`, `body` bg `rgb(9,9,11)`), then reverted to light
- **Preconditions:** theme set to Dark and saved.
- **Steps to reproduce:** Preferences → Theme → Dark → Save changes → open the Dashboard → read the Get running panel.
- **Expected:** body-size text meets 4.5:1.
- **Actual:** the `To do` pill is `rgb(255,68,0)` on `rgb(42,42,42)` = **4.15:1** at 12px/600. It is the only
  failing pair on the Dashboard in dark (14 distinct colour/size pairs measured; 1 failure). Orange-on-dark is a
  deliberate risk hue, so the trade may be intended; the measured number is the finding, not the palette choice.
- **Evidence:** `dashboard-dark-1440x900.png`, `preferences-dark-attempt-1440x900.png`
- **Can it be fixed in code?** yes — one token (the risk colour's dark-mode value), or bump to the larger step size.
- **Confidence:** **Observed**, measured as a computed pair. Filed Informational because 4.15 vs 4.5 on a 12px
  badge is a defensible trade, not a clear failure.

## BO-008 — The Dashboard counts trainees/cards by the whole table while the catalogue counts by Global

- **Severity:** Informational
- **Screen / route:** `/` vs `/umamusume`, `/database/trainees`, `/support-cards`
- **Viewport:** both
- **Theme:** light
- **Steps to reproduce:** compare the Dashboard KPI tiles to the catalogue headers they link to.
- **Expected:** a KPI labelled `TRAINEES CATALOGUED` that opens "Browse catalog" should match what that catalogue says.
- **Actual:** the Dashboard reads `TRAINEES CATALOGUED 133` and `SUPPORT CARDS 559`; `/umamusume` reads
  `72 of 72 trainees` and `/support-cards` reads `559 catalogued` but `251 cards on [Global]` with
  `Showing 1 to 25 of 251`. **Cards reconcile** (559 catalogued is the page's own headline number); **trainees do
  not present a reconcilable pair** — 133 is not shown anywhere the catalogue prints, and the catalogue's own
  total is 72 of 72. Separately `/career/setup/trainee` states `66 trainees are catalogued`, a third figure.
  Whether 133 is "all rows including non-Global" is plausible but is **not stated** on either surface.
  Skills reconcile cleanly and are **not** a finding: `623 skills available on Global` + `Japan only 1287` + `Other 0`
  = `1,910`, exactly the Dashboard's number.
- **Evidence:** `dashboard-1440x900.png`, `umamusume-1440x900.png`, `database-trainees-1440x900.png`,
  `support-cards-1440x900.png`, `skills-1440x900.png`, `career-setup-trainee-1440x900.png`
- **Can it be fixed in code?** yes — either label the KPI with its scope (`133 rows, 72 on Global`) or print the
  same scoped number the destination prints. The wizard's third figure (66) is a separate label bug.
- **Confidence:** **Observed** — all five numbers read directly. **Uncertain** on whether 133 is intentional
  all-rows scope: nothing on screen says so, and I did not read the query.

## BO-010 — Three required selects on the Build-target step carry no required marking

- **Severity:** Low (filed Informational: the error message is good, only the affordance is silent)
- **Screen / route:** `/career/setup/target`
- **Viewport:** both
- **Theme:** light
- **Steps to reproduce:** fill only the numeric targets and press "Continue to legacy".
- **Expected:** the three mandatory selects (Purpose, Surface, Style) are marked required before the attempt.
- **Actual:** nothing marks them; they sit among five optional numeric targets and nine optional ceiling fields,
  all visually identical. On submit, validation **works and is well-written** — each select is followed by its own
  sentence (`The purpose field is required.`), not a distant alert — and the screen does not advance. So the
  failure mode is a silent affordance, not a broken validator.
- **Evidence:** `career-setup-target-1440x900.png`, `career-setup-target-390x844.png`
- **Can it be fixed in code?** yes — a required marker or a one-line note above the three, matching how the numeric
  block already explains itself ("The fields below start empty rather than at zero.").
- **Confidence:** **Observed.** The submit-and-fail cycle was walked.

## BO-011 — Four different focus-ring colours across one screen; one pair is low-contrast

- **Severity:** Informational
- **Screen / route:** `/review/10`
- **Viewport:** 1440x900
- **Theme:** light
- **Steps to reproduce:** tab from the top of `/review/10` through 20 stops and read each stop's `outline-color`.
- **Expected:** one focus-ring colour on one screen.
- **Actual:** 20 stops produced **four** outline colours: `rgb(39,86,250)` ×17 (the app's blue),
  `rgb(46,56,114)` ×1 (`Back to the queue`), `rgb(98,93,125)` ×1, `rgb(50,42,47)` ×1. Every stop had
  `outline: solid 2px`, so focus is never invisible — this is consistency, not a trap. I did **not** measure each
  ring against its own background, so I make no WCAG claim.
- **Evidence:** `review-10-1440x900.png`
- **Can it be fixed in code?** yes — the two outliers are focus styles not using the shared focus token.
- **Confidence:** **Observed** for the counts and colours. **Uncertain** on whether the two dark rings are a defect
  or intentional per-component overrides — I could not see them.

## BO-005 — The turn-edit door cannot be reached because no turn exists

- **Severity:** Informational (a state, not a defect)
- **Screen / route:** `/training-runs/13/timeline`
- **Viewport:** both
- **Theme:** light
- **Steps to reproduce:** open the Timeline for a run with 0 turns and look for an edit affordance.
- **Expected for the audit:** a door to `/training-runs/{run}/turns/{turn}/edit`.
- **Actual:** the Timeline renders its empty state ("no turn has been logged") and no edit door, correctly — there is
  no turn to edit. The route is therefore **unreachable-by-door** at this data state, and per the brief's own rule
  ("do not type the URL if a button exists") it was not forced. Filed so the coverage table's gap is accounted for.
- **Evidence:** `training-runs-13-timeline-1440x900.png`
- **Can it be fixed in code?** no — this is a data-state gap for the audit, not a product defect.
- **Confidence:** **Observed.**

## BO-012 — The support-card index exposes no compare affordance

- **Severity:** Informational
- **Screen / route:** `/support-cards`
- **Viewport:** both
- **Theme:** light
- **Steps to reproduce:** open `/support-cards` and look for any control matching compare/select/add on a card.
- **Expected per the brief:** "open three cards, open the compare panel".
- **Actual:** the index has filters, sort, and per-row "Open the card" links; no compare control exists on the
  surface. A row's detail page was opened and it likewise carries no compare affordance. Recorded as a null
  result so the brief's coverage claim is answered rather than silently skipped. `/legacy/compare` and
  `/veterans/compare` **do** exist and are functional.
- **Evidence:** `support-cards-1440x900.png`, `support-cards-390x844.png`
- **Can it be fixed in code?** unknown — a missing feature is a scope question, not a fix. See EX-04.
- **Confidence:** **Observed** (absent from the index and one detail page). **Not** classified as
  "unbuildable anywhere" — I did not exhaust the application, per the absence rule.

---

## Withdrawn after re-measurement

Three candidates looked like findings and did not survive a second, more careful read. They are recorded here
because a register that silently drops them cannot be audited.

**No finding was withdrawn as a result of the post-walk correction.** That correction argued the 0-career baseline
was avoidable by seeding. It was — three seeders exist — and that is now recorded as EX-10 in
`04-excluded-findings.md` as an owner action and an audit documentation gap. But it does not invalidate a finding:
BO-002 is a defect *of* the empty state, and a seeded walk would not have produced it. The register's Medium
finding stands, and the next pass must walk both states to keep it.

| Candidate | Why it was withdrawn |
|---|---|
| "Duplicate nav labels (`Skills` ×3, `Database` ×4)" | **My probe was wrong.** The first selector counted `aside a` and nested `nav a` for the same anchors. Deduplicating by href across the whole document gives **30 links, 0 duplicate names**. Not a defect. |
| "Dashboard counts 1,910 skills but the catalogue says 623 — contradiction" | **It reconciles.** `/skills` prints `623 Global` + `1287 Japan only` + `0 Other` = `1,910`, with the sentence "These figures do not change with the scope filter". The two numbers are a total and a scoped view of the same total. Not a defect. |
| "A fixed 281px panel covers content at 390px" | **It renders nothing.** The closed `<details>` panel reports a 281px box, but `checkVisibility()` is `false` and `elementFromPoint` at its centre returns the page content behind it. It neither paints nor intercepts. My geometry probe was measuring a box, not a thing. Not a defect. |
