# Trainer Desk screens: UI audit (40 page components, 43 routes, plus the three error documents)

> **Reference only. Not authorization.** Nothing in this file is a decision. No component, token, copy
> string, gate or spec line was changed to produce it. Every section names the files an approval would
> touch and touches none of them.
>
> Recorded 2026-10-13 from a full read of every in-scope page and its composed components, plus the
> controllers that build their props. It is the third audit in this directory:
> `docs/proposals/cockpit-ui-audit-2026-10-12.md` owns `SCR-CAR-011` (the Cockpit) and
> `docs/proposals/setup-wizard-ui-audit-2026-10-12.md` owns the six `/career/setup/*` steps. Those two
> surfaces are excluded here, and where a finding is really about the shell they share with everything
> else, it is reported once in §2 and cross-referenced rather than restated.
>
> **Method limit, stated up front.** This is a source audit. No browser, dev server, Playwright run, PHP
> test, build, or lore gate was executed for it, with the two exceptions named in §1.3. Every claim about
> rendered appearance (contrast, computed height, whether a repaint happens, whether a bar moves) is marked
> `needs measurement` and names the measurement that would settle it. `AGENTS.md` §9's evidence rule
> applies: a gate that was not run is reported as not run.

## 0. Scope, standards, and how the findings were produced

### 0.1 In scope

| Cluster | Routes | Page files |
| ------- | ------ | ---------- |
| A: shell, home, errors | `/`, the shared shell, `404/419/500` | `Dashboard.vue`, `layouts/AppLayout.vue`, `resources/views/app.blade.php`, `resources/views/errors/*` |
| B: reference catalogs | `/umamusume`, `/umamusume/{slug}`, `/skills`, `/skills/{skill}`, `/support-cards`, `/support-cards/{card}` | `Catalog/{Index,Show}`, `Skills/{Index,Show}`, `SupportCards/{Index,Show}` |
| C: the Database hub | `/database` plus `trainees, supports, skills, races, scenarios, events, shop-items, sparks` | `Database/{Index,Trainees,Supports,Skills,Races,Scenarios,Events,ShopItems,Sparks}` |
| D1: run lifecycle | `/training-runs`, `/training-runs/create`, `/training-runs/import` (both states) | `Runs/{Index,Create,Import}` |
| D2: queue and settings | `/review`, `/preferences` (+ `export`, `backup`) | `Review/Index`, `Preferences/Edit` |
| E: Legacy Lab, Veteran library, deck builder | `/legacy`, `/legacy/{run}`, `/legacy/compare`, `/veterans`, `/veterans/{veteran}`, `/veterans/compare`, `/training-runs/{run}/deck` | `Legacy/{Index,Builder,Compare}`, `Veterans/{Index,Show,Compare}`, `Support/Builder` |
| F: career decision screens | `/training-runs/{run}/{training,races,races/planner,skills}` | `Career/{TrainingDetail,RaceDecision,RacePlanner,SkillsPlanner}` |
| G: record, outcome, snapshot | `/training-runs/{run}/{events,inheritance,timeline,result,veteran}`, `/career/snapshot{,/setup,/review}` | `Career/{EventDecision,InheritanceEvent,Timeline,Result,SaveVeteran}`, `Career/Snapshot/{Entry,Setup,Review}` |

Excluded by the brief: `Career/Cockpit.vue` and the seven wizard pages (`ScenarioSelect`, `TraineeSelect`,
`TraineeProfile`, `BuildTarget`, `LegacySelect`, `DeckSelect`, `Preflight`). `Career/Snapshot/*` sits under
`/career/snapshot`, not `/career/setup/`, so it is in scope; it is a second door beside the wizard
(`routes/web.php:99-112`), not a step of it.

### 0.2 The three standards applied

1. **`DESIGN.md`**, the shipped visual contract. The clauses this audit measured against, with their lines:
   the Design Read and dial `ENERGY 1 / RHYTHM 1 / MOTION 1` (`:24-27`), colour-is-semantic and the
   no-pastel-no-shadow no-marketing principles (`:49-64`), the token roles and their measured pairs
   (`:110-129`, `:145-162`), the type scale (`:188-190`), the ratified widths (`:197-208`), the wide-table
   focusable-region rule (`:209-224`), spacing plus one `h1` plus one primary action plus `rounded-md`
   (`:225-230`), the three motifs and their single homes (`:231-248`), the absence and provenance rules
   (`:567-624`), the lore boundary (`:626-660`), motion (`:679-726`), and the WCAG 2.2 AA floor with the
   44px house bar (`:851-872`).
2. **`antislop` core (`R-01`..`R-38`) plus `antislop-ui`.** For an Operate surface the live rules are the
   Hard Gates `R-02` (no em dash in copy), `R-03` (no overflow, 44px), `R-17`/`R-18`/`R-36`/`R-38` (no
   invented numbers, claims, or disguised placeholders), `R-24`/`R-26` (no dead nav, no dead controls),
   `R-25`/`R-32`/`R-34` (contrast, keyboard, every shipped theme), `R-27` (empty, loading, error), and the
   Purpose-Gate and Quality-Lock pair `R-01`, `R-04`, `R-06`, `R-09`, `R-11`, `R-12`, `R-15`, `R-16`, `R-19`,
   `R-29`, `R-31`. From `antislop-ui` the App and Dashboard tells were the checklist: default dashboard
   shell, stat cards with invented deltas, filler activity feed, chart without a question, generic
   Name/Status/Date columns, filler data in fields, and an empty state that names neither cause nor action.
3. **impeccable `audit` and `critique` rubrics**: the five dimensions (Accessibility, Performance, Theming,
   Responsive, Implementation Integrity) scored 0-4 per cluster, severity `P0`..`P3`, the cognitive-load
   checklist (chunking to four, one focal point, no memory bridge, progressive disclosure that holds
   something back), and the two personas that matter for a single-Trainer analyst desk: Alex (keyboard-first,
   high-volume) and Sam (screen reader, contrast, keyboard-only).

### 0.3 What was run

Two commands, both read-only, per `AGENTS.md` §9's background rules:

- `npm run typecheck` on 2026-10-13: exit 0, output `> tsc --noEmit`. Its meaning is in §4.1, not here.
- `impeccable detect --json resources/js/pages`: `[]`, zero findings. Also an instrument result (§4.6).

Nothing else ran. The browser suite, `php artisan test`, Pint, PHPStan, and both lore gates are **not run**
for this audit, so no claim in it is gate evidence.

## 1. Score card

### 1.1 Cluster scores (impeccable's five dimensions, 0-4)

| Cluster | A11y | Perf | Theme | Resp | Integrity | Sum | One-line reason |
| ------- | ---- | ---- | ----- | ---- | --------- | --- | --------------- |
| A shell, home, errors | 3 | 3 | 4 | 2 | 2 | **14** | nav, skip link and both themes are exemplary; the shell applies no ratified measure and the front door's headings invert the scale |
| B reference catalogs | 2 | 2 | 3 | 3 | 2 | **12** | artwork, provenance and filter idioms are correct; absence reasons are mouse-only and the two most frequent actions are full reloads |
| C Database hub | 3 | 3 | 4 | 3 | 2 | **15** | the best-themed cluster; a dead prop path, four header sizes, and an unvalidated config-to-badge index |
| D1 run lifecycle | 2 | 3 | 3 | 3 | 2 | **13** | the index and combobox are the app's model surfaces; the import step bypasses its own form machinery and errors are visual only |
| D2 queue, settings | 3 | 3 | 2 | 2 | 2 | **12** | correct ARIA tablist and live regions; sub-44px primary commit, an irreversible default, and a theme save that does not repaint |
| E Legacy, Veterans, deck | 2 | 3 | 2 | 3 | 2 | **12** | one row shape and one notice constant are strong; the builder is a narrower copy of the wizard's editor and drops recorded ranks |
| F career decisions | 3 | 3 | 3 | 2 | 3 | **14** | caps, glyphs and the guided write are single-owned; motif dose and the type scale are not held |
| G record, outcome, snapshot | 2 | 3 | 2 | 3 | 2 | **12** | the rail disclosures and refusals are right; five surfaces style themselves with tokens that do not exist, and the confirm screen omits identity |
| **Tree (this pass)** | 20 | 23 | 23 | 21 | 17 | **104 / 160** | 65%: impeccable's band for this is *Acceptable, significant work needed*, and the work is concentrated in three dimensions, not spread thin |

Reading the columns matters more than the total. **Theming reaches 23/32 (72%) while Implementation
Integrity sits at 17/32 (53%) and Accessibility and Responsive at 20/32 each.** The palette, tokens, motifs
and both themes are close to the contract; the drift is in the layer where a decision was written down once
and then not carried into the components, plus a set of props that no longer match the server or the child
that consumes them.

### 1.2 Severity counts

| Cluster | P0 | P1 | P2 | P3 | Total |
| ------- | -- | -- | -- | -- | ----- |
| A | 0 | 2 | 4 | 6 | 12 |
| B | 0 | 2 | 5 | 5 | 12 |
| C | 0 | 2 | 2 | 7 | 11 |
| D1 | 0 | 4 | 2 | 2 | 8 |
| D2 | 0 | 4 | 3 | 1 | 8 |
| E | 1 | 4 | 6 | 1 | 12 |
| F | 0 | 1 | 5 | 6 | 12 |
| G | 0 | 7 | 4 | 1 | 12 |
| **Total** | **1** | **26** | **31** | **29** | **87** |

One P0 (`F-E1`, data loss on the Legacy builder's confirm) is not a design finding at all and is called out
in §3.6 as a defect that belongs in `KNOWN-ISSUES.md`, not in a UI slice queue.

### 1.3 What the audit found already correct

This is the antislop purpose test run over the whole surface, and the app passes it broadly:

- **None of the classic slop vocabulary exists here.** Sweeps across the eight clusters found zero
  gradients-as-identity, zero glassmorphism, zero glow, zero decorative skeletons, zero `dark:` utilities,
  zero raw hexes in components, zero shadows (the contract bans them at `DESIGN.md:231`), no bento mosaic,
  no stat-card row with invented deltas, no activity feed, no chart placed without a question, and no
  generic `Name/Status/Date/Actions` table with a decorative row menu. The one near-miss is a
  `status_label` column that can only ever hold one value (`F-E12`), which is a column-content defect, not
  the template tell. The marks the app does use (`⚠`, `✦`, `◆`, `★`, and one typographic `i`) are
  glyph-plus-word state markers, not emoji decoration; the `i` is filed at `F-E9`. `DESIGN.md:24-27`'s dial
  `ENERGY 1 / RHYTHM 1 / MOTION 1` is honoured almost to the letter: the two places it is not are filed,
  `F-G10`'s single `animate-pulse` and §4.4's record that the shell does carry a `transition-[width]` the
  contract says the tree contains none of.
- **Swap-the-logo test:** `PRODUCT.md` and the client-vocabulary rules produce a surface that could not
  belong to another product. Torn-page turn chip, argyle lattice bleed on capsule headers, the enamel sheen,
  the orange-up blue-down rule, and the four trust labels are all specific to this tool.
- **Single-owner discipline is real and holds:** `ProvenanceBadge.vue` owns the four marks,
  `AbsenceValue.vue` owns the keyboard-reachable absence, `VeteranRow` is the one shape six surfaces print
  (asserted, not hoped for: `tests/Feature/VeteranLibraryScreenTest.php:78-101`), `ScenarioCaps::forRun` is
  read by both the form's `max` and the request that validates it (`TrainingDetail.vue:410`,
  `StoreTurnEntryRequest.php:70,81-85`, closing `ADR-0015`), and `TraineeCombobox.vue` is a genuine combobox
  with `aria-activedescendant` resolved only to a visible option.
- **Honesty under missing data is the strongest trait in the product.** Held computations render as refusals
  with the ruling as the reason (`RaceDecisionController.php:250-256`, `SaveVeteran.vue:273-283`,
  `AncestryGraph.php:203-209`), no screen prints a score, band, chance or prediction, and empty states name
  the cause and the command that fills them (`SkillCatalog.vue:130-149`, `SupportCardCatalog.vue:158-178`,
  `Support/Builder.vue:396-410`).
- **Accessibility plumbing exists and is tested:** one base-layer `:focus-visible` ring (`app.css:334-337`),
  skip link to a real `#main`, `scroll-padding-bottom` under the pinned mobile bar, a reduced-motion block,
  axe A+AA scans scoped to `#app` including one mid-visit, 44px sweeps with a shared helper
  (`tests/utils/tap-targets.ts`), `lang="ja"` on Japanese runs, and focus return on every disclosure.

The findings below are therefore not "the app looks AI-generated". They are the narrower, more fixable set:
rules that shipped in one place and not in another, channels a keyboard user cannot reach, and controls
whose stated behaviour and actual behaviour disagree.

## 2. Cross-cutting findings

Eight themes account for most of the weight. Each merges the cluster rows named in its own list; the cluster
tables in §3 keep the per-site citations.

### 2.1 X-1. An absence's reason is still mouse-only on 40 of 43 routes (P1)

`DESIGN.md:576-585` settled this: the marker is a native `<details>` whose summary carries the reason as its
accessible name, because "a `title` is reachable with a mouse and by nothing else". The component shipped
(`AbsenceValue.vue`), and the migration stalled.

- **Measured on this tree:** `AbsenceValue` is imported by **10 files**. The clusters enumerated
  **roughly 100 unmigrated `title`-only sites** between them: `F-E3` lists 37 lines across 7 files, `F-G7`
  lists 26 across 4, `F-F1` 11 across 6, `F-B2` 9 across 5, `F-C2` 8, `F-D23` 5, `F-A1` 4 plus the shell's
  version absence that repeats on all 43 routes, `F-D1` (whole columns render blank rather than `N/A`).
- **The register understates it.** `SCREEN_SPEC.md:3341-3359` item 17 records "**17** single-line sites
  across 9 files" and names only `TrainingCard`, `RunRaceStrip`, `SkillsPlanner`, `RaceCard`, `SaveVeteran`,
  `AncestryNode`, `UraPanel`, `Result`, `ScenarioPanel`. Its own text already concedes that multi-line sites
  are out of a single-line sweep's reach; this pass found that the un-swept remainder includes every catalog,
  Database, Legacy, Veteran, Dashboard, Review and Preferences screen. Not one of those files appears in the
  register's count.
- **Impact.** The reason is the half that tells the Trainer whether to go enter a reading or stop looking.
  `Legacy/Compare.vue:262` ("This Legacy's own star count was not recorded") and
  `Veterans/Compare.vue:265` ("no source publishes this trainee's aptitude letter") are different
  instructions, and a screen-reader user hears only "N/A" for both.
- **Fix, once:** migrate the call sites to the existing component, `compact` inside cells and rails, passing
  each site's current sentence verbatim. The three placements and display constraints in `DESIGN.md:587-600`
  are the reason this cannot be a blind sed pass, and the specs that pin the current idiom by
  `getByTitle(...)` must move with it, which needs owner approval per `AGENTS.md` §9.
- Merged rows: `F-A1`, `F-B1`, `F-B2`, `F-C2`, `F-D1`, `F-D23`, `F-E3`, `F-F1`, `F-G7`.

### 2.2 X-2. The type scale never landed, and half the pages print their title twice (P2)

`DESIGN.md:188-190` specifies section headers `text-lg font-600` and a page title `text-2xl font-600`, one
`h1` per page. The shell renders `#title` at `text-base` (`AppLayout.vue:277-281`; cockpit audit §2.1), so
every page starts one to two steps low.

- **Measured:** **75** `<h2>`/`<h3>` elements carry `text-base font-semibold` across 29 files in
  `resources/js`, and **30** sites already use the specified `text-lg font-semibold`. Both treatments ship at
  once, which is worse than uniform drift: `Database/ShopItems.vue:155` is compliant and its sibling
  `Database/Events.vue:69` is not.
- **Duplicated titles:** `Skills/Index.vue:37` prints "Skill search" as `#title` and `SkillCatalog.vue:74`
  prints the identical string as a `text-2xl` `h2`; same for `SupportCards/Index.vue:49` with
  `SupportCardCatalog.vue:91`, `Support/Builder.vue:257,259` (same string twice on one page), `Review/Index.vue:33,35`
  and `Preferences/Edit.vue:163,165` (two different names for one page), the eight Database areas
  (`F-C4`), and `Catalog/Index.vue:50`, whose `h1` reads "Database" while the sidebar it came from reads
  "Database", the visible heading reads "Umamusume catalog", and `SCREEN_SPEC.md:695` calls the entry point
  "Nav 'Catalog'".
- **Inverted case:** four clusters report section headers rendered as `text-xs font-bold uppercase
  tracking-wide text-ink-muted` (`Dashboard.vue:112,201,246,297,313`, `Veterans/Show.vue:47,142,186`,
  `Legacy/Index.vue` region labels, `Snapshot/Setup.vue`). That is `antislop-ui`'s "generic AI typography"
  tell (uppercase with wide tracking, `R-06`) applied to a heading, and it puts the section label below the
  body size while the data beside it is `text-lg` or `text-2xl`. On the Dashboard the largest words on the
  page are three numbers set in `<p>`.
- **Fix:** one shell decision (cockpit audit §3.1), then per page either delete the duplicate heading or
  demote it to the section step, and promote the eyebrow labels to `text-lg font-semibold text-ink-strong`,
  which is already the shipped class pair elsewhere. Do not restyle pages before the shell decision, or the
  spread widens.
- Merged rows: `F-A3`, `F-B6`, `F-C4`, `F-C5`, `F-D27`, `F-E10`, `F-F5`, `F-G12`.

### 2.3 X-3. The enamel motif lost its single home (P2)

`DESIGN.md:231-248` rules the gloss **material** and names exactly one home: the primary action button.
`:228` rules one primary action per screen.

- **Measured:** **52** `enamel` occurrences in `resources/js`. Screens carrying more than one: all four of
  cluster F (`TrainingDetail.vue:634-640` plus five per-card `Train` buttons; `SkillsPlanner.vue` has three
  co-equal writes), cluster E's `Remove Spark` button, which wears the gloss on a **destructive** control
  while `Add Spark` beside it is plain (`Legacy/Builder.vue:384` versus `:395`), `Veterans/Index.vue:154,200`,
  `Support/Builder.vue:307,379`, `Result.vue:341,355` (a read and a write side by side, equally glossed),
  `SaveVeteran.vue:304,333,398` (tag chips, an add button, and the save), and `Timeline.vue:110` filter chips.
  The error documents' secondary anchors use `rounded-full border-2` (`F-A9`), so a pill shape appears on
  non-primary controls in exactly the places the product already failed.
- **Impact.** The one affordance that means "press this to finish" now marks selection steps, toggles,
  deletes and secondary reads. Hierarchy is carried by borders alone, and the motif's scarcity is what spent
  its signal.
- **Fix per screen:** keep enamel on the completion write, restyle the rest with the bordered secondary pair
  the same files already use. No new token, no new class. Where a screen genuinely owns several writes
  (the skills planner), that is an owner call about whether `one primary action per screen` binds a
  multi-write screen, which §6 asks.
- Related colour findings in the same family: the goal pennant's `goal-line` hue borrowed for obligations,
  warnings, and a learned list (`F-F2`), a deck error band painted in the action green (`F-E8`), the success
  flash painted action-green (`F-A7`), and `animate-pulse` on one milestone (`F-G10`; my own grep confirms
  `InheritanceEvent.vue:156` is the only `animate-*` in the tree, against `DESIGN.md:679-726`'s MOTION 1).
- Merged rows: `F-A9`, `F-E7`, `F-E8`, `F-F2`, `F-F3`, `F-G9`, `F-G10`.

### 2.4 X-4. Writes that are refused, or that fire twice, are silent (P1)

`AGENTS.md` §13 requires every data view to render its error state, and `ADR-0007` gives user-initiated
writes their own feedback. Four distinct mechanisms fail:

1. **Errors the page never prints.** `F-D2`: eight of the eleven import columns have rules in
   `ImportHistoricalRunRequest.php:134-138` and no render path in `Import.vue`, which reads only five stat
   names plus `mood` and `energy`. `F-E2`: the deck page reads only `errors.deck`, while the common refusal
   (a card not in the catalogue) is attached to `deck.2.support_card_id`, a key the page never touches;
   `tests/Feature/SupportDeckBuilderTest.php:265` pins that key as the shape that arrives. `F-G5`: a rejected
   snapshot lands back on a Review page that renders no error text at all.
2. **The control does not say it is working.** `F-D3`: the import commit posts through `router.post`, outside
   the page's own `useForm`, so `form.processing` never becomes true, the button never disables, and a second
   press writes a second run from the same 200-row payload (no idempotency key at
   `TrainingRunController.php:1088-1099`).
3. **The warning hides when the risk arrives.** `F-E4`: "Confirming replaces the whole deck" is gated on
   `analysis.covered > 0`, so the moment a Trainer empties the slots, the only sentence about the pending
   destructive write disappears.
4. **Focus is not managed, and defaults commit writes.** `F-F9` (one of four screens manages focus on a
   refused write, three do not), `F-A8` (the shell's `role="status"` node is created in the same tick as its
   message, so announcement depends on engine behaviour), `F-D21` (the review card preselects `Confirmed`,
   the one verdict that writes a catalog row, with no confirm step and no view that still lists the row).

- **Fix pattern, once:** iterate the error object the page already receives rather than a hardcoded key list,
  route every write through the page's own `useForm` so `processing` exists, gate a destructive warning on
  the pending write rather than on current data, and adopt the shipped refusal idiom
  (`role="alert" tabindex="-1"` plus focus move, `EventDecision.vue:325-336`).
- Merged rows: `F-D2`, `F-D3`, `F-F9`, `F-A8`, `F-A7`, `F-E2`, `F-E4`, `F-G3`, `F-G5`, `F-D21`, `F-D24`.

### 2.5 X-5. Form and control semantics that only a mouse sees (P1)

`F-D4`: seven error paragraphs on `Runs/Create.vue` and four on `Import.vue` are unattached, `aria-invalid`
and `aria-describedby` are absent while `CandidateForm.vue:40-41` ships the correct association elsewhere in
the same tree. `F-G4`: `Snapshot/Setup.vue:183-215` wraps a number input and an "I don't know this value"
checkbox in one `<label>` with no `for` or `id`, so the input's accessible name is the whole block and the
checkbox is unnamed. `F-D22`: the review queue's primary commit is an `enamel ... px-3 py-1.5` pill with no
height floor, in a screen whose sibling (`Preferences/Edit.vue`) carries `min-h-11` on all ten controls;
`tests/browser/review.spec.ts` has no target sweep and no axe scan while `preferences.spec.ts:237-248` has
both. `F-E11`: `/legacy` and `/veterans` pagers are `px-3 py-1` anchors while the deck and support-catalog
pagers on the same product carry `min-h-11`. `F-G11`: the star-roll table scrolls in a bare
`overflow-x-auto` div with no `role="region"`, `tabindex="0"` or label, one call site away from the correct
wrapper at `Result.vue:251-253`. `F-F8`: a disabled Compare toggle's class list is identical to the
unselected one, so the fifth pick fails silently.

- **Fix:** reuse the four idioms already in the tree (`describedBy()` at `Preferences/Edit.vue:217`,
  `tests/utils/tap-targets.ts` for the sweep, `Result.vue:251` for the region, `SkillPlanRow.vue:113`'s
  `disabled:opacity-40`). No new component.
- Merged rows: `F-D4`, `F-D22`, `F-E11`, `F-F8`, `F-G4`, `F-G11`.

### 2.6 X-6. Copy that is addressed to a repository contributor (P2)

- `F-G2` (P1): two **em dashes** in shipped, server-composed copy at `TimelineController.php:240` and `:270`,
  rendered verbatim by `CareerTimeline.vue:208,213`. `AGENTS.md` §5 and `R-02` both ban them, and the
  sibling surface spells the same state with a hyphen (`EventCard.vue:38`). Why it survived is an instrument
  finding, not a judgement call: see §4.2.
- `F-B3`, `F-D28`: literal backticks around `php artisan uma:fetch ...` render as punctuation in Trainer
  sentences on four surfaces, while `Catalog/Show.vue:328` uses a real `<code>` for the same class of key.
- `F-D28`: the Settings screen prints `(PRD US-11, PRD §6 non-goal 12)`, "`ADR-0001` §3 records...",
  "which `AGENTS.md` §5 scopes to the owner", and "(design-2.0 §48)" in body copy. Those citations are true
  and they are not user-facing language (`R-16`).
- `F-B7`: one release fact in five spellings, two of them internal notation: `available on [Global]`, a `dt`
  reading "Released on [Global]", raw column values `JP-only` beside the terms-map `Japan only`, and
  `(Global)`. The bracketed tag is this repository's documentation notation (`AGENTS.md` §1), not a client
  word. `F-B10`: `AptitudeGrid.vue:9-23` re-types "Front runner" where `lang/en/uma.php:109-112` and
  `Umamusume.php:141` both say "Front Runner".
- `F-E5` (P1): `Veterans/Show.vue:104` labels the same integer "Skills learned" that
  `Veterans/Compare.vue:249-252` labels "Skills recorded" *and explains is not "learned"* (the pivot holds
  Suggested and Skipped rows). One label overstates the data.
- `F-A10`: the 419 document's title and `h1` say "Sign-in session expired" in a product with no sign-in
  (`AGENTS.md` §12), while the body copy two lines down explains exactly that.
- `F-A6`: the Dashboard's DATA STATUS panel hard-codes `'Current'` (`DashboardController.php:252`) while the
  adjacent source string appends "source marked stale" when `config('scenarios.php:51')` flips. The panel
  that answers "can I trust this data" contradicts itself the first time the flag is set.
- Merged rows: `F-A6`, `F-A10`, `F-B3`, `F-B7`, `F-B10`, `F-D28`, `F-E5`, `F-G2`.

### 2.7 X-7. Props that no longer match the child, the server, or the token set (P1, P2)

The cluster files list these individually; the pattern is one worth naming because nothing catches it.

- `F-G1` (P1): `bg-warning` / `text-on-warning` / `text-warning` are used at **eight sites in six files**
  (`EventDecision.vue:286,421`, `Result.vue:296,302`, `CareerTimeline.vue:104`, `EventCard.vue:36,61`,
  `ScenarioStatusBadge.vue:36`). There is no `--color-warning` role anywhere in `app.css`'s theme block, so
  Tailwind v4 emits no utility and each renders as unstyled text. Verified by grep of `resources/**` on
  2026-10-13. The product's only "this record is incomplete" signal is invisible; the ⚠ glyph and the words
  survive, so it is a lost affordance rather than lost content. The declared pair for this job is `risk`
  (`DESIGN.md:127`), and the same pages already use it (`EventDecision.vue:74,330`).
- `F-C1` (P1): `Database/ShopItems.vue:79` passes `:recheck="props.recheck"`; the page's own props interface
  at `:40-45` does not declare it, and the controller does send it (`DatabaseController.php:255-258`). The
  Shop Items area silently loses the re-check pointer that Events and Sparks both render, breaking the
  "every reference view carries one" rule its own component docblock states.
- `F-G6` (P1): `EventCard.vue:67` renders `JSON.stringify(props.deltas ?? {})` for a prop that is neither
  declared (`:8-20`) nor sent (`EventDecisionController.php:177-191`), so a delta-only recorded outcome
  prints the literal `Deltas: {}`.
- `F-F11`, `F-E12a`, `F-G8`, `F-D1` (runs.total unused), `F-D8`: page-local interfaces missing fields their
  children require (`RacePlanner.vue:54-60` versus `RacePlanList.vue:43`), `veteran_url` sent and ignored,
  an `after` value built per row and rendered nowhere, a prop pinned by a test and never printed, and a
  provenance string written by the commit that the confirm step never shows.
- Merged rows: `F-C1`, `F-C10`, `F-D8`, `F-E12`, `F-F11`, `F-F12`, `F-G1`, `F-G6`, `F-G8`.

### 2.8 X-8. Measure and width: the contract's three ratified numbers are used by almost nobody (P2)

`AppLayout.vue:295` is `<main id="main" class="flex-1 px-6 py-6 pb-24 md:pb-6">` (verified by read): no
`max-w` of any kind, so on the 1280px+ primary target every page's prose runs to the shell column's full
width. `F-A2` notes the three standalone error documents **do** constrain themselves to
`mx-auto max-w-5xl`, so the copies are the compliant ones. `F-F6`: none of the four career decision screens
constrains prose or form width; the wizard audit §1.2 found the same for five of six wizard steps.
`F-D6` and `F-G12` find `max-w-2xl`, a value `DESIGN.md:197-208` does not ratify, used in three places
(`Runs/Import.vue:147,235`, `Snapshot/Entry.vue:18`, `Snapshot/Review.vue:57`) while `SCREEN_SPEC.md:576`
records `max-w-2xl` as the shipped import form card, which makes the doc list the stale side per
`AGENTS.md` §2. Wide tables are the one class of surface that is right: they scroll inside a focusable
labelled region and are never reflowed (`F-C` "Already correct", `Result.vue:251-259`,
`RacePlanner.vue:219-224`).

## 3. Findings by cluster

Each row is the cluster's own finding, with its citations. Fix text is compressed; the full version of each,
including the alternative that was rejected and why, is in the pass notes for that cluster. Severity is the
impeccable scale. "Guarded" names the tests or specs an approval would have to move with the change
(`AGENTS.md` §9: no test is deleted or weakened to make a change pass).

### 3.1 Cluster A: shell, home, errors

| ID | Sev | Finding | Files | Fix | Guarded |
| -- | --- | ------- | ----- | --- | ------- |
| F-A1 | P1 | Home-screen absences are mouse-only | `Dashboard.vue:133-137,154-161,170-177`, `AppLayout.vue:291-293` | `AbsenceValue :reason compact` | `dashboard.spec.ts:142-148` |
| F-A2 | P1 | The shell applies no ratified measure to `main` | `AppLayout.vue:295`, errors constrain themselves (`404:38`) | `mx-auto w-full max-w-5xl` on `main` | `dashboard.spec.ts:195-224`, `veterans.spec.ts:40-43` |
| F-A3 | P2 | Five `h2`s at `text-xs uppercase`, bodies at `text-lg`/`text-2xl` | `Dashboard.vue:112,201,246,297,313` | promote to `text-lg font-semibold`, demote the count `dd`s | `dashboard.spec.ts:87-92,130` (accessible names) |
| F-A4 | P2 | RECENT VETERANS' only two CTAs point at `/legacy` while `/veterans` is one click away, and the empty state contradicts the library's own filing sentence | `Dashboard.vue:246-291`, `DashboardController.php:201`, `VeteranController.php:78` | point `library_url` at `veterans.index`, reuse `filingNotice` | `dashboard.spec.ts:79,115`, `veterans.spec.ts:100-130` |
| F-A5 | P2 | The mobile bar's More disclosure never signals that the current page lives inside it | `AppLayout.vue:128-129,186-187,344` | one computed over `moreItems` using the existing `isCurrent()`, plus an `sr-only` word | `veterans.spec.ts:35,51`, `KeyboardPathTest.php:69` |
| F-A6 | P2 | DATA STATUS prints a literal `Current` beside a stale flag | `DashboardController.php:248-272`, `config/scenarios.php:41,50-51` | compose `state` from the same flag | `DashboardTest.php:57-60`, `dashboard.spec.ts:91-92` |
| F-A7 | P3 | The success flash paints confirmation in the action green | `AppLayout.vue:296-302`, `app.css:57-61` | `border-rule bg-raised`, the pair `Cockpit.vue:464` uses | `TokenPairHygieneTest.php:93` |
| F-A8 | P3 | The `role="status"` region is created in the same tick as its message | `AppLayout.vue:296-302` | keep one region mounted, bind only its text | four specs resolve `getByRole('status')` as a single node |
| F-A9 | P3 | Error-page secondary anchors are `rounded-full border-2` | `404:52-59`, `419:49-52`, `500:46-49` | copy `Dashboard.vue:236`'s secondary pair | `ErrorPageViewsTest.php:47,110-113` |
| F-A10 | P3 | 419 title says "Sign-in session expired" in a product with no sign-in | `419.blade.php:17,35` (its own comment says so) | retitle to the cause the body states | `ErrorPageViewsTest.php:41-52` asserts that string |
| F-A11 | P3 | The visit indicator may paint a static bar | `VisitProgressBar.vue:27-31` `appearance-none` with no `::-webkit-progress-value` | measure first, then at most a `bg-idle` track | `accessibility.spec.ts:42-59` |
| F-A12 | P3 | The third motif has no `@utility`; the turn chip is six class lists in one component | `app.css:291-323` defines `enamel` and `lattice-bleed` only; `ResourceStrip.vue:106-119` | `@utility turn-chip` beside `lattice-bleed` | `DesignTokensTest.php:184-205` |

### 3.2 Cluster B: reference catalogs

| ID | Sev | Finding | Files | Fix | Guarded |
| -- | --- | ------- | ----- | --- | ------- |
| F-B1 | P1 | Skill mechanics: `{{ group.condition ?? 'N/A' }}` prints a bare marker and `v-if` drops the precondition and base-time rows entirely | `Skills/Show.vue:115,118,123`, `SkillController.php:129` | keep the rows, render through `AbsenceValue` | `skills.spec.ts:75-79` |
| F-B2 | P1 | Nine absence reasons are `title`-only across the three details and two lists | `Catalog/Show.vue:165,235,242`; `Skills/Show.vue:76,84`; `SupportCards/Show.vue:126,134,145-150`; `SkillRow.vue:22` | swap to `AbsenceValue` in the `dd`; the two in-sentence sites are blocked until the sentence becomes a row (`DESIGN.md:597-600`) | `skills.spec.ts:69-72` pins `[title=...]` and `toHaveText('N/A')` |
| F-B3 | P2 | Markdown backticks render as characters in remedy sentences on all six screens | `Catalog/Show.vue:146,275,281,330`, `TraineeCatalog.vue:123`, `SkillCatalog.vue:135-136`, `SupportCardCatalog.vue:167-168,260` | delete the backticks, use the existing `<code>` idiom | `EmptyStateCommandNamesTest.php:60,81-82` (pattern survives) |
| F-B4 | P2 | Catalog index prints no result count and one message for "empty catalog" and "no match" | `TraineeCatalog.vue:119-124,39-43`, `CatalogController.php:171-182` | port the two-state split and the `X of Y` line the siblings ship | `catalog.spec.ts:22-26`, `SCREEN_SPEC.md:703,719` |
| F-B5 | P2 | Row drill-in and pager are full document reloads on screens that already use Inertia `Link` | `TraineeCatalog.vue:145,196`, `SkillCatalog.vue:160,187`, `SupportCardCatalog.vue:218,244`, `SkillRow.vue:12` | `Link` with `preserve-scroll` for internal destinations; external source URLs stay anchors | `skills.spec.ts:57`, `navigation-determinism.spec.ts:75-90` |
| F-B6 | P2 | Three index screens print their page title twice, in two sizes, and one pair of names disagrees | `Skills/Index.vue:37` vs `SkillCatalog.vue:74`; `Catalog/Index.vue:50` vs `TraineeCatalog.vue:88` | one string per screen, owned by the page | `catalog.spec.ts:10`, `support-cards.spec.ts:11-17` |
| F-B7 | P2 | One release fact, five spellings, two of them internal notation | `SkillCatalog.vue:152`, `SupportCards/Show.vue:131`, `Catalog/Show.vue:331,180`, `SupportCard.php:69` | route through `ReleaseStatus::label()` and extend `lang/en/uma.php`; add server-side availability words like `typeWords()` already does | `support-cards.spec.ts:50,87`, `catalog-detail.spec.ts:95-104` |
| F-B8 | P3 | The unique-skill fact carries `◆` on the index and `✦` on the detail | `SkillCatalog.vue:168-172` vs `SkillRow.vue:15-19`, `Skills/Show.vue:91-95` | one glyph; `✦` is the pinned one | `skills.spec.ts:66-67` |
| F-B9 | P3 | Two date formats in one Provenance section (`M j, Y` at `CatalogController.php:353`, `Y-m-d H:i T` at `:388`) | `Catalog/Show.vue:397-403`, `Skills/Show.vue:184` | one formatter for the read-instant, after the owner ruling `AGENTS.md` §16 asks for | `CatalogRosterTreeTest.php:541-565`, `CatalogDetailPageTest.php:228` |
| F-B10 | P3 | Running-style words re-typed in the component, in the wrong case | `AptitudeGrid.vue:9-23` vs `lang/en/uma.php:109-112`, `Umamusume.php:141` | pass `aptitudeLabels()` through and delete the local list | five tests compare `Front Runner` |
| F-B11 | P3 | Three filter surfaces, two focus implementations | `SkillCatalog.vue:87,96,110`, `SupportCardCatalog.vue:101,115,127,139` vs `TraineeCatalog.vue:98,103,109` | delete the redundant per-control utilities; the base ring owns it | 44px sweeps, axe |
| F-B12 | P3 | The cluster's densest label colour is outside the contrast contract | `g18ContractPairs()` at `DesignTokensTest.php:298` carries one `ink-muted` pair | add `ink-muted on raised` and `ink-muted on panel`, let the gate answer | `DesignTokensTest.php:532` (list must match the contract) |

### 3.3 Cluster C: Database hub and eight areas

| ID | Sev | Finding | Files | Fix | Guarded |
| -- | --- | ------- | ----- | --- | ------- |
| F-C1 | P1 | Shop Items passes a prop its interface never declares, so the re-check pointer it must have never renders | `ShopItems.vue:40-45,79`, `DatabaseController.php:255-258`, `SourceNote.vue:16,24-34` | add the missing prop line; the data already arrives | `DatabaseTest.php:136-156` |
| F-C2 | P1 | Races and Skills state absence reasons by mouse only | `Races.vue:118-140`, `SkillCatalog.vue:171-180` | `AbsenceValue` with `compact` | `database.spec.ts:179-185` pins `getByTitle` |
| F-C3 | P2 | Config state strings index the badge with no validation: one typo renders a whole reference view | `DatabaseController.php:188,230,292`, `ProvenanceBadge.vue:54-61`, `config/reference.php:28-34` | one whitelist in the three loops, falling back to `unknown` | `DatabaseTest.php:117-135` |
| F-C4 | P2 | Every area pairs a small `h1` with a larger duplicate `h2` | five areas `text-2xl` under `AppLayout.vue:281`; shared lists print the standalone heading | demote or delete the duplicates | `database.spec.ts:100,117,150,158,195` pin `level: 2` |
| F-C5 | P3 | Four section-header sizes coexist inside one hub (`text-xl`, `text-lg`, `text-base`, `text-sm`) | `Scenarios.vue:68,90,119,133`, `ShopItems.vue:155`, `Sparks.vue:177`, `Events.vue:69,85` | set the drifted ones to the size the cluster already ships | `database.spec.ts:200-201` |
| F-C6 | P3 | Hub area links hover to the colour they are already on | `Index.vue:37,42` (`bg-raised` ground, `hover:bg-raised`) | `hover:bg-panel`, the pair Events and Sparks use | `database.spec.ts:29-53` |
| F-C7 | P3 | Pagination is the same block copied into four files, as raw anchors that reload the document | `Races.vue:159-171`, `TraineeCatalog.vue:194-206`, `SkillCatalog.vue:185-197`, `SupportCardCatalog.vue:242-254` | one pager, or `Link`; keep `withQueryString` | 44px sweep at `database.spec.ts:268-285` |
| F-C8 | P3 | `TraineeCatalog`'s empty state diverges from its two siblings (no count line, no source argument, one message for two states) | `TraineeCatalog.vue:119-124` vs `SkillCatalog.vue:130-152` | port the two-state split, name the source from `config('uma.sources')` | `database.spec.ts:142-151` |
| F-C9 | P3 | Events and Sparks drawers disclose nothing the row does not already print | `Events.vue:124-141`, `Sparks.vue:141-161` vs `ShopItems.vue:131-143` | delete the drawer, or move one extra sentence into it | `database.spec.ts:77-94` pins the drawer path |
| F-C10 | P3 | The four-state union is hand-re-declared far past the file count that documents it | `ProvenanceBadge.vue:12` plus 18 more files, 49 literal sites measured (§4.4) | alias once per file; report the doc figure as stale | the `defineProps` compiler limit |
| F-C11 | P3 | Redundant per-control focus outlines beside the base ring | as `F-B11`, in the Database-rendering copies | delete | needs measurement (which ring paints) |

### 3.4 Cluster D1: runs index, create, import

| ID | Sev | Finding | Files | Fix | Guarded |
| -- | --- | ------- | ----- | --- | ------- |
| F-D1 | P1 | The import preview prints **blank cells** where the file has no value, indistinguishable from a dropped column | `Import.vue:65-67,200-211`, `TrainingRunController.php:1185` | `AbsenceValue` with `compact` on the null branch only; present cells stay raw | `run-import.spec.ts:80-92`; `SCREEN_SPEC.md:632` needs a §7 entry |
| F-D2 | P1 | Eight of eleven validated import columns produce errors the page never renders | `Import.vue:59,80-101,321-322`, `ImportHistoricalRunRequest.php:134-138` | drive the existing `rowErrors` loop from the `headers` prop, so a column cannot exist without a render path | `run-import.spec.ts:97-107` pins the composed sentence |
| F-D3 | P1 | The confirm step posts outside `useForm`: no pending state, no disable, and two presses write two runs | `Import.vue:127-139,217-222`, `TrainingRunController.php:1088-1099` | post through the page's own form (or one `submitting` ref) with the `:disabled`/`aria-busy` pair the step-one button already has | `run-import.spec.ts:81,131` |
| F-D4 | P1 | Field errors and required state on both forms are visual only | `Create.vue:107-212`, `TraineeCombobox.vue:456,460`, `Import.vue:243-283` | `aria-invalid` plus `aria-describedby` using the shipped idiom | `runs.spec.ts:76-107` (the combobox's ARIA surface) |
| F-D5 | P2 | Two scenario-less runs of one trainee give the links list the same name twice; status and date sit outside the anchor | `Index.vue:67-75`, `TrainingRunController.php:62,66-68` | `aria-label` from props already on the row, or move the meta span inside the link | `RunsIndexTest.php:69-76`, `NoStaleRunRecordLinksTest.php` |
| F-D6 | P2 | The confirm half carries no width; the page uses `max-w-2xl`, which the ratified list does not name | `Import.vue:147,157-165,235`, `DESIGN.md:197-208`, `SCREEN_SPEC.md:576` | apply `max-w-3xl` to the prose halves; leave the table region as a scroll contract | `run-import.spec.ts:57-73,87-92` |
| F-D7 | P3 | Index row and preview table run at the smallest type in the app, with `font-mono` on prose | `Index.vue:73-75`, `Import.vue:198,207,307` | `text-sm` with mono on the numerals only, the split `Import.vue:207` already makes | `runs.spec.ts:41-56`, `DesignTokensTest.php:126-153` |
| F-D8 | P3 | The commit stores a provenance string (`pasted CSV (a3f9c1d2)`) the preview never shows | `TrainingRunController.php:1073-1096`, `ImportHistoricalRunRequest.php:205-222` | pass `sourceLabel()` into the preview payload and append it to the identity line | `run-import.spec.ts:80-95,124-141` |

### 3.5 Cluster D2: review queue, preferences

| ID | Sev | Finding | Files | Fix | Guarded |
| -- | --- | ------- | ----- | --- | ------- |
| D21 | P1 | The verdict defaults to `Confirmed`, the one option that writes a catalog row, with no confirm step and no view that still lists the row | `CandidateForm.vue:16-20,77-83`, `ResolveMatchCandidate.php:31-47`, `ReviewController.php:30` | start the field empty with a `Not set` option; the request's `required` rule already exists | `ReviewQueueTest.php:23-62`, `review.spec.ts:40-43` |
| D22 | P1 | The queue's primary commit is the only enamel action in the tree with no height floor, on the screen with no target sweep and no axe scan | `CandidateForm.vue:42,58,68,80`, `Review/Index.vue:83,87` | add the shipped `inline-flex min-h-11 items-center`, sweep with `tests/utils/tap-targets.ts` | `review.spec.ts:21-23` |
| D23 | P1 | Five Settings absences, including the Restore and Reset refusals, are `title`-only | `Preferences/Edit.vue:293-298,358-363,416-430,452-458` | `AbsenceValue` without `compact` (settings rows are not dense cells) | `preferences.spec.ts:114-169` asserts those exact `title`s |
| D24 | P1 | Saving a theme change confirms success and changes nothing on screen | `Edit.vue:137-153,166-170,211-225`, `app.blade.php:7`, `PreferenceController.php:122`; no `data-theme` write exists in `resources/js` | the server already owns first paint, so widen the flash to name the effect rather than adding a client write | `PreferenceControlsTest.php:53-71`, `preferences.spec.ts:41-67` |
| D25 | P2 | The merge target is a bare numeric id while the app ships a trainee combobox; a wrong-but-existing id is unreversible | `CandidateForm.vue:49-59`, `ResolveMatchCandidate.php:32-34`, `Review/Index.vue:65-67` | reuse `TraineeCombobox.vue`, or at minimum print the suggested row's id | `ReviewFormAccessibilityTest.php:19-29` |
| D26 | P2 | One empty state covers "never fetched" and "you just cleared thirty", and tells the Trainer to run a fetch in both | `Review/Index.vue:41-46`, `ReviewController.php:27-33,64` | branch on the pending count; the cleared branch names where the promoted rows now live | `review.spec.ts:12-14,34-37` detect on `Nothing pending.` |
| D27 | P2 | Both pages print a `text-2xl` `h2` under the shell `h1`, with different names; the tier badge is the smallest item on the row | `Review/Index.vue:33,35,57-60`, `Edit.vue:163,165` | delete the duplicate `h2`s; one visible badge step, mono for the date tail | `preferences.spec.ts:11,121-122` |
| D28 | P3 | Governance citations, literal backticks, `space-y-6`/`space-y-4`/`-mt-2` rhythm, and two identically-styled primary and backup buttons | `Review/Index.vue:45`, `Edit.vue:166-170,173,241-247,286,360,407,418,427,455,482` | strip the citations, keep both sentences the specs assert, use `space-y-3`, gloss only the submit | `preferences.spec.ts:112-113,114-115,123,137,140,160,169,181` |

### 3.6 Cluster E: Legacy Lab, Veteran library, deck builder

| ID | Sev | Finding | Files | Fix | Guarded |
| -- | --- | ------- | ----- | --- | ------- |
| **F-E1** | **P0** | The Legacy builder is a narrower second copy of the wizard's ancestry editor: it never passes `rank-label`, so a recorded rank prints "Rank not recorded", and its confirm re-posts without the four keys the request then nulls, so rank letter, ancestor ranks and ancestor Sparks are **lost on a routine save** with no message | `Builder.vue:37-48,235-248`, `AncestryNode.vue:54,77`, `AncestryGraph.php:176-185,194-198`, `StoreLegacySelectionRequest.php:309-320`, `LegacyController.php:236-251` | the payload already carries the data: declare and pass the props, and seed plus post the four keys the wizard step already posts | `LegacyLabPageTest.php:243-312`, `legacy.spec.ts:153-283`, `AncestorRankTest.php:52-62`, `GrandparentRanksTest.php:66-112` |
| F-E2 | P1 | A refused deck write reaches the page but never the screen (the common refusal is keyed to a slot the page does not read) | `Builder.vue:107-109,273-275`, `StoreDeckRequest.php:54-91`, `SupportDeckBuilderTest.php:248-266` | iterate the errors object as `Veterans/Compare.vue:164-173` already does | `support-deck.spec.ts:91-146` |
| F-E3 | P1 | 37 absence reasons across seven files are mouse-only, in the tree's largest un-migrated pocket; `AncestryNode` uses `AbsenceValue` for the Spark chance and `title` for its own rank three lines above | `Legacy/{Index,Compare,Builder}`, `Veterans/{Index,Show,Compare}`, `AncestryNode.vue:93,101,117`, `SupportSlot.vue:138,171`, `SparkChip.vue:58` | `AbsenceValue compact`; where the reason already prints as prose, drop the `title` | `legacy.spec.ts:177-185,275-277`, `support-deck.spec.ts:83` |
| F-E4 | P1 | The clear-all warning disappears exactly when the deck is empty, i.e. when the destructive write is largest | `Builder.vue:304-316` gated on `analysis.covered`, `DeckAnalysis.php:122-124` | delete the `v-if`; the `slots` array is the truth it needs | `support-deck.spec.ts:148-185` |
| F-E5 | P1 | One integer, two labels, and one label ("Skills learned") claims more than the pivot holds | `Veterans/Show.vue:104-111` vs `Compare.vue:248-259`, `VeteranRow.php:11-26` | one label per fact, owned server-side, with the compare surface's qualifier sentence on both | `VeteranLibraryScreenTest.php:103-138` pins values, not labels |
| F-E6 | P2 | `DeckAnalysis` prints its own `Calculated` chip (a second owner of the badge vocabulary), leaves its `Confirmed` anchors unlabelled, heads pure counts with "Strengths"/"Weaknesses", and ships the grammar defect "1 of the 1 cards carry" | `DeckAnalysis.vue:11-15,92-97,136-155`, `DeckAnalysis.php:277,299` | import `ProvenanceBadge`, retitle the two panels, fix the plural | `support-deck.spec.ts:171-185`, `DeckAnalysisTest.php:98-188` |
| F-E7 | P2 | Enamel on a destructive `Remove Spark`, two or three chrome pills per screen | `Builder.vue:382-400,444-450`, `Veterans/Index.vue:152-201`, `Support/Builder.vue:305-382` | enamel only on the screen's commit | `support-deck.spec.ts:91-111`, `legacy.spec.ts:193-194` |
| F-E8 | P2 | The deck's error band is `border-goal-line bg-green-tint` while its two sibling screens write the same message in `risk` | `Builder.vue:273-275` vs `Career/DeckSelect.vue:241`, `Legacy/Builder.vue:438` | copy the sibling's class list | `DesignTokensTest.php:334,344` |
| F-E9 | P2 | One record-only contract, three renderings: `bg-sunken` note on Legacy, bare line on Veterans, **bare line with no role** on `/veterans/compare`, plus a typographic `i` used as a glyph and the refusal stated three times on one screen | `Legacy/{Index,Builder,Compare}`, `Veterans/{Index,Show,Compare}:112/44/120`, `NavGlyph.vue:56` | one rendering, keep `role="note"`, drop the duplicate footer | `legacy.spec.ts:97-101,159,242`, `veterans.spec.ts:91` |
| F-E10 | P2 | Four pages print a second `text-2xl` heading under the shell title (`Support/Builder` prints `Support deck` twice); `Legacy/Builder.vue:176` puts a trainee *name* at page-title size; three Veteran section headers are `text-xs uppercase text-ink-muted` | as listed | delete duplicates, promote the eyebrows | `veterans.spec.ts:90`, `support-deck.spec.ts:165` |
| F-E11 | P2 | Two library pagers miss the 44px bar while two other pagers meet it, and row buttons hover to the colour they sit on | `Legacy/Index.vue:237,251`, `Veterans/Index.vue:255,279` vs `Support/Builder.vue:476`, `SupportCardCatalog.vue:248` | the existing class list; `hover:bg-sunken` | `support-deck.spec.ts:91-111` |
| F-E12 | P3 | `/legacy` rows have no link to the Veteran detail although `veteran_url` is in the payload; a `status_label` column that can only ever hold one value sits in the deciding slot; the compare caption says "nothing here is ... totalled" while the cell adds two parents | `Legacy/Index.vue:20-31,184-241`, `VeteranRow.php:24-26`, `Legacy/Compare.vue:202-204,367-384` | link the name, drop the invariant column, align caption and arithmetic | `VeteranLibraryScreenTest.php:78-101`, `legacy.spec.ts:317-325` |

F-E1 is filed as a UI finding because it was found from the read path, but its data-loss half is a defect
and `AGENTS.md` §11 says a new `KI-nn` entry is the register's own four-part form, in the owner's hand. It
should not sit in a design queue.

### 3.7 Cluster F: career decision screens

| ID | Sev | Finding | Files | Fix | Guarded |
| -- | --- | ------- | ----- | --- | ------- |
| F-F1 | P1 | Absence reasons still `title`-only on all four screens, including two held figures where the ADR-0016 reason lives only in a tooltip; `RaceCard` renders a bare `N/A` with no reason in any channel | `RaceDecision.vue:140`, `RacePlanner.vue:133-135,145-152,254-256,303-305`, `TrainingCard.vue:410-420`, `SkillPlanRow.vue:84`, `RaceCard.vue:164,212` | `AbsenceValue compact`, server's sentence verbatim; supply the null-title arm | `career-race-decision.spec.ts:71` pins the hover channel |
| F-F2 | P2 | The goal pennant's hue is borrowed for obligation, warning, and a learned list, contradicting the screen's own ruling that mandatory is never a Goal | `RaceDecision.vue:149,152,182`, `SkillsPlanner.vue:257-259,284` vs `AlertRow.vue:9-14`, `RunRaceStrip.vue:157-159` | weight plus `text-ink-strong`, `border-rule` | `DesignTokensTest.php:344,545` |
| F-F3 | P2 | Every screen ships multiple enamel primaries (five `Train` buttons plus "Record this training"; three co-equal writes on the skills planner) | as §2.3 | one gloss per screen; the cards already have a secondary treatment | four 44px sweeps count height only |
| F-F4 | P2 | Two permanent refusal panels sit above the only content that can demand action | `RacePlanner.vue:127-156` above `:161-182`; `RaceDecision.vue:187-193` | reorder inside the existing components; nothing deleted | `career-race-planner.spec.ts:61` reads the region by name |
| F-F5 | P2 | 18 section headers at `text-base`, zero at the specified `text-lg`; `SkillsPlanner` skips a level (`h2` to `h4`) | line list in the cluster notes | the scale step plus `h4` to `h3` | `career-skills-planner.spec.ts:149` |
| F-F6 | P2 | None of the four constrains prose or form width; the only measure in the cluster is one `max-w-prose` helper line | `TrainingDetail.vue:386`, `RaceDecision.vue:252`, `SkillsPlanner.vue:314,349`, `RacePlanner.vue:185-215` | the shipped `max-w-3xl` idiom | `career-race-planner.spec.ts:204-216` |
| F-F7 | P3 | "Senior Year turn ." renders for the seven finale-block obligations | `RaceDecision.vue:181-185` vs the guard at `:170` | reuse the file's own guarded arm | `CareerRaceDecisionTest.php:183` |
| F-F8 | P3 | The fifth Compare pick is refused invisibly: the disabled class list equals the unselected one | `RacePlanList.vue:88-105` | the cluster's own `disabled:opacity-40` | `career-race-planner.spec.ts:132,177` |
| F-F9 | P3 | Write feedback is uneven: one screen moves focus and associates every field, three print one floating message | `TrainingDetail.vue:200-214,249-253` against `RaceDecision.vue:309-311`, `RaceCard.vue:233-235`, `SkillsPlanner.vue:329-331` | extend the existing idiom | `career-race-decision.spec.ts:111` |
| F-F10 | P3 | One fact read out up to five times (the turn number on TrainingDetail; the next obligation twice; the readiness refusal in a panel and every drawer; the hint-lack sentence twice) | line list in the notes | one prose reading per screen | `career-skills-planner.spec.ts:111` |
| F-F11 | P3 | Page-local interfaces drifted from child and server: `Group` lacks `total`/`truncated` the child requires and the controller sends; `status_labels` is shipped and unused; the win-probability refusal is passed into the *readiness* prop | `RacePlanner.vue:54-60,196` vs `RacePlanList.vue:43`, `SkillsPlanner.vue:53`, `AcquisitionStatusEditor.vue:127` | align the three | `CareerRacePlannerTest.php:362` |
| F-F12 | P3 | Stale docblocks and retired-component citations, including `AGENTS.md` §7 naming four components that are not in the tree | `TrainingDecisionController.php:47-52`, `RunRaceStrip.vue:7-9`, `AGENTS.md` §7, `DESIGN.md:124,569` | report-only; the corrections belong to a docs pass | `GuidedTurnStagesTest.php:40-88` |

### 3.8 Cluster G: record, outcome, snapshot

| ID | Sev | Finding | Files | Fix | Guarded |
| -- | --- | ------- | ----- | --- | ------- |
| F-G1 | P1 | Five surfaces' incomplete-record chips use `bg-warning`/`text-on-warning`, tokens that do not exist, so the product's only "needs attention" signal renders unstyled | `EventDecision.vue:286,421`, `EventCard.vue:36,61`, `CareerTimeline.vue:104`, `Result.vue:296,302`, `ScenarioStatusBadge.vue:36`; `app.css` has no `warning` role | repoint at the declared `risk` pair the same pages already use | `career-event-decision.spec.ts:115-136` pins the words and `title`, not the fill |
| F-G2 | P1 | Em dashes in server-owned UI copy on the two most scan-relevant rail lines | `TimelineController.php:240,270`, rendered at `CareerTimeline.vue:208,213` | use the hyphen form `EventCard.vue:38` already ships | `career-timeline.spec.ts:68-95` |
| F-G3 | P1 | The snapshot confirm screen reads back eight state numbers and never names the trainee, scenario, position or notes it is about to write | `SnapshotController.php:116-148`, `Review.vue:33,63-76`, `routes/web.php:109-112` | one identity row from the `payload` prop, using `SaveVeteran.vue:203-237`'s `dt/dd` shape | `SnapshotReviewTest.php:46-98` |
| F-G4 | P1 | Each stat field wraps its number input and its "I don't know" checkbox in one `<label>` with no `for` or `id`: the input's name becomes the block's text and the checkbox is unnamed | `Snapshot/Setup.vue:183-215` | three labels, real ids; the toggle handler is already correct | `SnapshotReviewTest.php:46-58`; the passing axe scan is not counter-evidence |
| F-G5 | P1 | Both snapshot refusal points are silent: Setup prints errors for three fields only while the request rules eight, and the confirm POST lands on a page with no error surface at all | `Setup.vue:123,137,170`, `StoreSnapshotRequest.php:48-56`, `Review.vue:33-37` | print per-field errors as Setup already does for three, and reuse the `role="alert" tabindex="-1"` block `EventDecision.vue:325-336` owns | `SnapshotReviewTest.php:93-98` |
| F-G6 | P1 | A recorded outcome with only deltas prints the literal `Deltas: {}`, from a prop that is not declared and never sent | `EventCard.vue:65-68,8-20`, `EventDecisionController.php:177-191,239-243` | delete the line; print the outcome as prose, or compose one `outcome_text` server-side | `career-event-decision.spec.ts:115-136` |
| F-G7 | P1 | 26 absence reasons across four record screens are mouse-only, and `EventDecision.vue:218-247` hand-rolls a second copy of `CareerStatePanel`'s already-migrated grid | as cited | migrate, and adopt `CareerStatePanel` rather than duplicating it | `career-inheritance-event.spec.ts:176-190` pins the pre-migration `title`s (owner approval) |
| F-G8 | P2 | The rail's audit pane renders two identical five-stat tables from one server call, prints the result string twice or three times, and ships an `after` field no template reads | `CareerTimeline.vue:174-214`, `TimelineController.php:207-209` | one block per row, drop the dead field | `career-timeline.spec.ts:86-91` pins the duplication, so the spec moves too |
| F-G9 | P2 | Enamel on filter chips, tag chips, and two side-by-side primaries on Result | `Timeline.vue:110`, `SaveVeteran.vue:304,333,396-402`, `Result.vue:341,355` | keep the write control, reuse the secondary pair in the same files | `career-timeline.spec.ts:107`, `career-save-veteran.spec.ts:141` |
| F-G10 | P2 | The one `animate-pulse` in the app marks a static "current milestone" | `InheritanceEvent.vue:156` | delete the class; the fill and the word already carry it | `career-inheritance-event.spec.ts:195-270` pins glyph and labels, not motion |
| F-G11 | P2 | The star-roll table scrolls in a div the keyboard cannot reach, one call site from the correct wrapper | `InheritanceEvent.vue:310-333` vs `Result.vue:251-253` | add the region's three attributes | `career-inheritance-event.spec.ts:163,337` |
| F-G12 | P3 | 30 section headers across six screens at `text-base`, zero at `text-lg`; two snapshot pages use `max-w-2xl` while the same flow uses `max-w-3xl` | line list in the notes | fold into the shell decision; normalise the two widths | `career-timeline.spec.ts:107`, `career-result.spec.ts:128` |

## 4. Instrument findings: what the quality bar currently cannot see

These five are the audit's most actionable output, because each one is a gate that reports a pass while the
class of defect it names is present in the tree. They are reported, not repaired: `AGENTS.md` §5 forbids new
suppressions and §2 forbids silently rewriting a rule to match code.

### 4.1 `npm run typecheck` reads no Vue file

`package.json:8` is `"typecheck": "tsc --noEmit"` and `tsconfig.json:15` includes
`resources/js/**/*.ts`, `tests/browser/**/*.ts`, `tests/utils/**/*.ts`. There is no `vue-tsc` in
`devDependencies` (`package.json:12-23`). A plain `tsc` cannot parse an SFC, so **none of the 48 page
components or the 64 components under `resources/js/components/` (counted on the working tree, 2026-10-13) is
type-checked**, while `AGENTS.md` §9 and §15 list `npm run typecheck` as the gate "if TypeScript, Vue or Blade
changed" and this pass observed it exit **0**.

Two escapes found the same way prove the ceiling rather than argue it: `Database/ShopItems.vue:79` reads a
prop its own interface does not declare (`F-C1`), and `career/EventCard.vue:67` reads `props.deltas`, which
is neither declared nor sent by the controller (`F-G6`). Both are `TS2339` candidates in a checked file and
both are invisible today. Any fix to those two lines should be accompanied by a decision on whether the gate
gains an SFC-aware pass; that is a dependency question (`AGENTS.md` §5, no new package without owner
approval), so it is a question here and not a proposal.

### 4.2 The copy-hygiene guard cannot see server-composed copy

`tests/Feature/RenderedCopyHygieneTest.php:35-49` builds its file list by walking for `.blade.php` and
`.vue` only. It exists precisely because `tools/gate.py` checked the D-79 em dash only over prototype HTML
and "a whole slice of Blade work landed with em dashes in copy that renders" (its own header, `:12-16`). The
same class has now moved one layer up: `TimelineController.php:240` and `:270` ship two em dashes in strings
the rail prints verbatim (`F-G2`), and the guard exits green. The Inertia prop payload is today's Blade
template.

### 4.3 The G-18 contrast contract covers one `ink-muted` pairing

Carried from the cockpit audit §2.9 and re-confirmed here: `g18ContractPairs()` (`:298` in the same test)
pins 19 pairs and exactly one carries `ink-muted`, against `sunken`. This pass found the app's dominant
muted usage is `ink-muted` on `raised` and `panel` (`F-B12` enumerates 21 label sites on three detail screens
alone; `F-E10` finds three *section headings* in the muted token). Two pair rows would convert a family of
judgement calls into a gate result. No ratio is claimed in this document, and none should be.

### 4.4 Registers that lag the code

| Register | What it says | What this pass measured |
| -------- | ------------ | ----------------------- |
| `SCREEN_SPEC.md:3341-3359` item 17 | 17 `title`-on-`N/A` sites across 9 files | roughly 100 enumerated unmigrated sites across five clusters; no catalog, Database, Legacy, Veteran, Dashboard, Review or Preferences file appears in the list |
| `DESIGN.md:615-618` | a fifth trust label "is a four-file change" | the four-member union is written out at **49 sites in 19 files** (measured 2026-10-13 with `git grep -E "'confirmed' \| 'calculated' \| 'estimated' \| 'unknown'"`) |
| `DESIGN.md:693-696` | "The tree contains zero `transition-*`, `duration-*` and `animate-*` utilities" | `AppLayout.vue:204` carries `transition-[width]` plus `motion-reduce:transition-none`, pinned by `KeyboardPathTest.php:101-102`, and `InheritanceEvent.vue:156` carries `animate-pulse` |
| `AGENTS.md:209-211` and `DESIGN.md:124` | `ResourceStrip.vue`, `StatBand.vue`, `RaceCalendar.vue`, `GuidedStep.vue`, `GradePointMeter.vue` are the live scenario-driven components | only `ResourceStrip.vue` is in the tree (verified: `find resources/js -name StatBand.vue` etc. return nothing). The nearest shipped relatives are scenario-specific (`scenario/TrackblazerRaceCalendar.vue`, `scenario/RaceCalendarNote.vue`) or differently shaped (`career/RaceCard.vue`, `career/ObjectiveRow.vue`); `DESIGN.md:410-411` and `:569` carry two further stale figures, and §3's page inventory names `Runs/Show.vue` and `Database/Database.vue`, neither of which exists |
| `DESIGN.md:569` | shipped validation bound "0..1200" | per-run entered ceilings are enforced through `ScenarioCaps::forRun` (`StoreTurnEntryRequest.php:53-85`, `ScenarioCaps.php:103-125`, `EnteredStatCapsTest.php:77`) |

Per `AGENTS.md` §2 these are code-wins-rule-stale statements: reported here, not edited there. They matter
because a future brief sized by §7's component list will ask for work on files that do not exist; this pass's
own brief did exactly that, and the correction is recorded at the top of the cluster F notes.

### 4.5 An unvalidated index between config and a component

`ProvenanceBadge.vue:61` indexes `BADGES[props.state]` with no fallback, and three Database controllers pass
`(string) $row['state']` straight out of `config/reference.php` (`DatabaseController.php:188,230,292`) with no
whitelist. `DESIGN.md:619-621` already names this failure mode. One capitalisation slip in the config takes a
whole reference view down at render time, and no build step or test catches it first (`F-C3`).

### 4.6 The deterministic detector saw nothing

`impeccable detect --json resources/js/pages` returned `[]`. The detector's rule set is CSS and markup-slop
shaped (gradients, glass, radius, shadow, generic content) and this app has genuinely none of those, so the
empty result is partly a real pass and partly an instrument limit: it does not read SFC prop contracts, token
existence, or a `title`-only channel, and those three are exactly the channels the P1s in §2.1, §2.5 and §2.7
travel on. Useful signal: the slop-taste failure mode antislop is built to catch is not present here. The
failure mode that is, is drift between a written rule and the 40 screens that shipped before it.

## 5. What a later pass must not do

1. Do not move a colour token to answer a contrast question. Propose pair rows in `g18ContractPairs()` and
   let the browser gate decide (cockpit audit §2.9, `F-B12`).
2. Do not add a fifth provenance label, a `Recorded`/`Missing` chip, or a badge that qualifies a section
   rather than a displayed value (`DESIGN.md:607-624`, `F-E6`).
3. Do not invent a `--color-warning` role to justify the eight dead classes. Use the declared `risk` pair
   (`F-G1`, `F-E8`); `DESIGN.md:127` reserves it for failure and rejection, and the incomplete-record state
   is that state.
4. Do not reflow the race calendar, the turn log, or the compare tables into stacked cards. The contract is
   `overflow-x-auto` plus `role="region"` plus `tabindex="0"` plus an `aria-label`
   (`DESIGN.md:209-224`), and it is already correctly implemented at `Result.vue:251-259`,
   `RacePlanner.vue:219-224`, and both compare surfaces.
5. Do not add a cold-load spinner, a skeleton, or an `aria-busy` on a page load. `ADR-0007` reserves custom
   loading for user-initiated async, and `AGENTS.md` §13 plus `DESIGN.md` §3 treat the absence as deliberate.
6. Do not add a score, percentage, confidence, risk grade, win chance or recommendation to any surface,
   including the deck analysis and the compare tables (`ADR-0001` §3, `ADR-0020` §2-§3, `PRD.md` §6;
   `F-E6` is about *labelling*, not about earning the right to compute).
7. Do not name a scenario in a component (gate G-33) and do not give a scenario-driven component a default
   `scenarioLabel` (`AGENTS.md` §7).
8. Do not restyle per-page headings before the shell `#title` decision in cockpit audit §3.1 is settled;
   independent page restyling is how §2.2's 75-versus-30 spread got worse.
9. Do not migrate an absence without moving the specs that pin the current channel. `getByTitle(...)`
   assertions exist in `dashboard`, `preferences`, `database`, `skills`, `legacy`,
   `career-race-decision`, `career-inheritance-event` and `career-event-decision` specs. That is a test
   change, which needs owner approval and a stated reason (`AGENTS.md` §9, §14).
10. Do not add a client-side theme write to fix `F-D24`. Server-composed first paint is pinned twice
    (`DesignTokensTest.php:184-205`, `ErrorPageViewsTest.php:48-51`).
11. Do not treat a passing axe scan as proof of an accessible name. `F-G4`'s nested-label block scans clean:
    a label that wraps two controls is valid ARIA-free markup, and axe only reports what it can see.
12. Do not widen `AGENTS.md` §7's component list as a side effect, and do not hand-edit `PRODUCT.md`,
    `SKILL.md`, or the derived ADR index.

## 6. Questions for the owner

1. **Absence migration (§2.1).** Roughly 100 sites, eight clusters, and at least seven specs that pin the
   current `title` channel. Take it as one slice with the spec moves included, screen by screen behind
   `SCR-CAR-011`'s pattern, or leave the register at 17 and accept that the keyboard Trainer gets "N/A" on
   40 of 43 routes?
2. **Type scale (§2.2).** `DESIGN.md:188-190` marks the scale **Proposed**, not established. Either the shell
   `h1` and the 75 `text-base` headers move to the written scale in one pass, or the written scale is
   restated to what shipped and the drift ends by ruling rather than by CSS. Which?
3. **Enamel and multi-write screens (§2.3, `F-F3`).** Does "one primary action per screen" bind a screen that
   genuinely owns three writes? If it does not, `DESIGN.md:228` needs a clause; if it does, six screens need
   the treatment reduced.
4. **`F-E1` (the P0).** Fix as a defect slice outside this audit's scope, and file the register entry. Its
   data-loss half is not a design question and its "must not break" list already names the four tests that
   pin the shape.
5. **`F-D1` versus `SCREEN_SPEC.md:632`.** The spec says the preview renders values "verbatim as raw strings
   straight from the parse"; `AGENTS.md` §5 says an unrecorded value renders `N/A` with a reason. Which
   governs the import preview's empty cells? (The rest of the row's argument for raw values, KI-46's
   row-pointing messages, is unaffected either way.)
6. **Gate reach (§4.1, §4.2).** Do you want the typecheck to see SFCs (a dependency question, since `vue-tsc`
   is not installed) and the copy-hygiene sweep extended to the PHP that composes UI strings, or do you want
   both left as source-audit findings that a human re-checks?

## 7. Verification plan for any approved subset

Per `AGENTS.md` §9's cost-aware escalation. Nothing below was run for this audit.

| Approved change | Level 1 while working | Level 2 at slice end | Level 3 before hand-off |
| --------------- | --------------------- | -------------------- | ---------------------- |
| §2.1 absence migration (any subset) | the feature test for the touched screen plus the spec that pins its `title` | those plus `tests/browser/accessibility.spec.ts` | the hand-off sequence, plus a browser pass on the touched screens with a session-private pair (`PLAYWRIGHT_PORT=<port> PLAYWRIGHT_SCRATCH_DB=.scratch-uma/browser-<port>.sqlite npm run test:browser`) |
| §2.2 type scale, shell and pages | `tests/browser/<screen>.spec.ts` for the touched pages | the same plus `CareerCockpitTest` (shared shell) | the full suite, since `AppLayout` is felt by every screen |
| §2.3 motif dose, `F-E8`, `F-A7` | the touched screen's spec (height sweeps count height, not gloss) | the cluster's specs | `php artisan test --compact`, then `vendor/bin/pint --test --format agent <explicit paths>` (never `--dirty`, KI-75) and PHPStan |
| §2.4 write-feedback and double-submit (`F-D2`, `F-D3`, `F-E2`, `F-E4`, `F-G5`) | the flow's feature test plus its browser spec | add `SupportDeckBuilderTest`, `ImportHistoricalRunTest`, `SnapshotReviewTest` | the full suite; a double-submit fix needs a counted assertion against a scratch database, not a screenshot |
| §2.5 form semantics (`F-D4`, `F-G4`, `F-D22`, `F-E11`, `F-G11`) | the screen's spec plus `tests/utils/tap-targets.ts` where height is involved | `tests/browser/accessibility.spec.ts` | the full suite; announce behaviour needs a real AT pass, which this tree has never recorded |
| §2.6 copy (`F-G2`, `F-B3`, `F-B7`, `F-B10`, `F-D28`) | `RenderedCopyHygieneTest`, `EmptyStateCommandNamesTest`, the named browser assertions | the touched screens' feature tests | `composer lore` and `composer lore-code`, with a one-line ruling per hit |
| §3.6 `F-E1` (P0) | `LegacyLabPageTest`, `AncestorRankTest`, `GrandparentRanksTest`, `legacy.spec.ts` | those plus `CareerLegacyDeckStepsTest` | the full suite and the hand-off sequence; this one changes a write path, so it is not a UI-only change |
| §4.3 G-18 pair rows | `php artisan test --compact --filter=DesignTokensTest` | the browser half of the same file | the pair list and the `DESIGN.md:145-148` count move together, then the full suite |

Pre-flight before any Level 3 or browser run: time one trivial SQLite read against the dev file
(`KI-74`). If it takes more than a few seconds the box is contended and the sweep is void before it starts.

## 8. Hand-off notes

- **Deliverables produced:** this file. Eight cluster notes with the full per-finding reasoning, the
  rejected alternatives, and the complete citation lists live in the session scratch directory
  `.scratch-uma/ui-audit-2026-10-13/` (`cluster-A.md`..`cluster-G.md`, `cluster-D1.md`, `cluster-D2.md`,
  plus `typecheck.log`). They are gitignored, so anything in them worth keeping is quoted into this
  document; if a later pass needs the unabridged reasoning behind a specific row, the scratch directory is
  the source and may already be gone.
- **Not delivered by this pass, stated plainly:** no browser measurement (so §2.2's size claims are class
  reads, not pixels, and `F-A11`, `F-C6`, `F-D24`, `F-E7` remain open questions), no contrast arithmetic, no
  suite run, no pint or PHPStan, no lore gate, and no fix of any kind. `AGENTS.md` §7's rule that this audit
  was told to enforce also says the doc is written only when the task asks; it asked, and this is it.
- **Facts this pass corrected in its own brief:** the four components §7 of `AGENTS.md` names as the career
  surface's scenario-driven set are not in the tree; `transition-*` does ship in the shell; the badge-union
  re-declaration count is 49 sites in 19 files, not four files; and `npm run typecheck` cannot see any of
  them. All four are recorded in §4.4 rather than repaired in place.
