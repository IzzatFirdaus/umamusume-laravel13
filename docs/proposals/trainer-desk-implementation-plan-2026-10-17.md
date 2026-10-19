# Trainer Desk: Implementation Instructions

**What changed from the previous version, and why.** The structure and voice are preserved. Corrections fall into five categories, listed here so the agent reads them before the work:

First, **four slices are already landed** and would be re-worked if not marked: 0.2 (07ca152, ce6cb9d), 0.3 (8dc73f2, plus 469eb13's third site), 0.5 (acfb65f), 0.10 (253f957). The prompt now names the commits and says "verify, do not rebuild".

Second, **two slices silently reverse owner rulings** and must be held: 0.1 reverses KI-103's 2026-10-13 "completed means visited, not saved" ruling; 1.1 replaces the palette, which is a Track 2 amendment per frontend-design-2026-10-13.md §6. Both are marked deferred-owner in the enhanced version.

Third, **slice 0.6 mixes a landed part with an unlanded part** — 1.7 (Month placeholder, b90e114) is done; 1.12 (aria-required on BaseSelect) is not. The enhanced prompt splits them.

Fourth, **the prompt assumes the image viewer is available**. It was unavailable in Batch 2. The durable gate is now a Playwright assertion; screenshots are captured for the record but the pass/fail is the assertion.

Fifth, **five slices from the arc's recent findings are missing**: 1.13 (stepAbsent undeclared class at SetupLayout.vue:97), 1.15 (<dt>/<dd> without <dl> at ScenarioSelect.vue:221), 1.16 (build debt clearance for 469eb13's red cases), Phase 8.7 (open every disclosure before axe scan), Phase 8.8 (route coverage map). The enhanced version adds all five.

The shared-index hazard, the external-push pattern, the DESIGN.md:1 peer edit conflict, and the batch limits are now explicit. Without them the prompt would produce the same drift the prior dispatches produced.

---

## Status as of 2026-10-20

This block supersedes the "What changed from the previous version" section above without deleting it. HEAD is `38a0a28`; `origin/master` is `b0b3ec9` (65 commits ahead of origin/master; no push performed this dispatch).

- Phase 0: complete except 0.1 (deferred-owner, KI-103).
- Phase 1: complete except 1.1-palette (deferred-owner, Track 2), 1.1-type-scale (blocked-owner — plan's 12/14/16/20/28 vs signed DESIGN.md §2.2's 12/14/18/30), 1.9b (retired), 1.9c-A (retired), 1.9c-B (moved to 4.2), 1.9d (withdrawn), 1.10-Stepper (deferred-owner).
- Phase 2: complete.
- Phase 3: complete.
- Phase 4: 4.1 landed; 4.2–4.6 pending.
- Phase 5–8: pending.
- The ledger at `docs/proposals/execution-ledger-2026-10-17.md` is the source of truth for what has landed; this plan is the source of truth for what remains. Where they disagree, the ledger wins.
- Three deferred cherry-picks wait on peer's ledger append; four commits already on master under new SHAs (c7b6424, 186a489, c963f64, ed9e97f).
- KI-70 side effect: master's public/build at 2026-10-20 03:39:17 reflects peer-dirty source; quiet-tree rebuild owed.

### Per-slice status as of 2026-10-20

0.1 deferred-owner (KI-103) — unchanged
0.2 07ca152 + ce6cb9d (+b6a160b, 0899ef8)
0.3 8dc73f2 + 469eb13
0.4a 74cabb2
0.4b fab7091
0.5 acfb65f
0.6a b90e114
0.6b c2de34b
0.7 b2e7317
0.8 already-satisfied (dispatch 7, no commit; the query param is `slot` not `deck_slot`)
0.9 d1a0e1b
0.10 253f957
0.11 9c56bba
0.12 dfef6c7
0.13 3e2bada
0.14 aee7a3a
1.1a 0ca8c1a (radius + shadow guard)
1.1b deferred-owner (palette, Track 2)
1.1c blocked-owner (type scale contradicts signed DESIGN.md §2.2)
1.2 ca8e47f, fixed 3e2bada
1.3 457237a
1.4 7e61e31
1.5 d63c0a6
1.6 ab1557f
1.7 91a6047
1.8 b69c648 + 648d3ec
1.9a 0bbd821
1.9b retired (premise-failed)
1.9c-A retired
1.9c-B moved to 4.2
1.9d withdrawn
1.10a 66e31d4 (StickyFooter)
1.10b deferred-owner (Stepper, KI-103)
1.11 9015bf6
1.12 fda6ebd + be77389 + 652a033
2.1a dd5ee6a
2.1b 12c47db
2.1c d84fa36
2.1d 29f8715
2.1e aaf2fe6
2.1f 844aebf + 360f7ee
2.2 553b5f3
2.3a 343f194
2.3b partial (AbsenceValue breadth deferred)
2.4 4f7ebb8 + 7a1055c
2.5 555fd3e
2.5w folded into 3.4–3.6
3.1 238d91c (stepper NOT included — deferred-owner; the shell composes PageHeader + StickyFooter + actions slot only)
3.2 eb002c5
3.3 3e8e924
3.4 1c59be8 + bb94035 + 1fe9c1d + 2e87837
3.5 6c9d099 (peer)
3.6 bbc1814 (peer)
3.7 5f2bfde
3.8 4096b11 + follow-ups
4.1 8a1f167 + c7b6424 + 186a489
4.2 pending (Section id extension approved; two commits)
4.3–4.6 pending
5.1–5.5 pending
6.1–6.4 pending
7.1–7.6 landed via peer (`44a7b13`, `d95da07`, `5d26b49` + `e2889ff`, `c3583f0` + `d21fcc1` + `3a1ba9b`, `8e51b9f`, `d8fb234`) — verify-first when reached
7.7–7.15 pending
8.1–8.8 pending

---

## Cross-phase slices added during execution

BaseSelect a11y (`a51a47c`), Snapshot wrapper a11y (`beb92cc`), test hygiene (`4777c81`), walk repairs (`2341f75`→`c963f64`, `b8b0def`→`ed9e97f`), Section id extension (pending 4.2), retired-`Confirmed` cases reported not fixed (`grand-concert-panel.spec.ts:86`, `trackblazer-panel.spec.ts:384`).

## Operator action list (as of 2026-10-20)

1. Commit the peer ledger append → cherry-pick 47488f0, 68ffe6f, 132cee8, a09ea94.
2. Commit the three Vue files → quiet-tree rebuild `public/build`.
3. Cherry-pick Phase 4 commits when ready.
4. Rule on the type scale.

## Document 1 ↔ Document 2 numbering

See the mapping table in the current status doc.

---

**Path corrections (dated 2026-10-20):**
- "frontend-design-2026-10-13.md §6" is a nonexistent path. The file is `frontend-fidelity-2026-10-13.md`. (Erratum: original sentence preserved above.)
- The .scratch-uma ledger path in the final paragraph: ledger now lives at `docs/proposals/execution-ledger-2026-10-17.md` (tracked) after the file move. (Erratum: original sentence preserved above.)

---

**Batch-limit paragraph update (dated 2026-10-20):** Phase 0/3–7 = 3, Phase 2/8 = 5, Phase 1 = one primitive per dispatch — still correct, but "at most three slices" now adds: unless the slice's premise check reveals the slice is already-satisfied, in which case take the next slice in order without consuming a batch slot.

", "old_string">
The shared-index hazard, the external-push pattern, the DESIGN.md:1 peer edit conflict, and the batch limits are now explicit. Without them the prompt would produce the same drift the prior dispatches produced.

---

Every step below changes code, markup, styles or tests. None is a documentation, report or summary step.

**Working rules for the whole project**

One slice per commit. Each slice ends with its listed tests green plus npm run typecheck, npm run build and vendor/bin/pint --test --format agent <paths> on the files touched. **Never pint --dirty** — several sessions share this tree and --dirty reformats another session's uncommitted files (KI-75).

Use a session-private Playwright port and a scratch database. Never run the browser suite against database/database.sqlite.

**Never run npm run build while a peer is live on the tree** (KI-70). A peer's uncommitted file will be swept into the bundle; check git status --porcelain before the build and confirm the tree has been clean for the duration.

**Every commit uses git commit -- <paths>.** Never a bare git commit. The shared git index is how 5a08db1 swept a peer's controller edit into a commit describing already-landed work; it cost four dispatches to recover.

**Cross-check every reported SHA** with git show --stat <sha> in the same pass. **Quote git rev-parse origin/master and git status -sb** before claiming "nothing pushed" — three external pushes have happened in this arc without an agent action.

**Read from git show HEAD:<file> when a peer's uncommitted edit is present.** DESIGN.md:1 carries a leading space before the # heading that breaks the title; do not read the working-tree copy while that edit is live.

**Before any UI edit, run git show HEAD:resources/js/... for the target file** and confirm the tree's version is what you are about to change. Batch 2 found the plan's file paths stale on two of three slices.

**The durable acceptance for any UI slice is a Playwright assertion** on DOM structure, computed style, or geometry. Screenshots are captured for the record under .scratch-uma/<batch>/shots/ but the pass or fail is the assertion — the image viewer was unavailable in Batch 2 and may be again.

Build primitives before pages. No page may add a one-off colour, size or spacing value. Where a slice says "delete", delete the file or the string. Do not comment it out.

**Every UI change runs the affected spec** before the commit and quotes the output tail. "Tests pass" without the tail is not a claim.

**The frontend-design skill** should be loaded before any UI slice if available; if not, DESIGN.md is the governing document and the slice says so in its report.

**A batch is at most three slices** for Phase 0 and Phases 3–7, **one primitive per dispatch** for Phase 1, and **five slices** for Phases 2 and 8. When the limit is reached, stop and hand off.

---

## Already-landed slices — verify, do not rebuild

The following slices landed in Batches 1–3 of the current arc. Each row names the slice, the commit, and what to check.

**Slice 0.2 (Skill and planner overflow).** Landed as 07ca152 for the detail page and ce6cb9d for the planner. Revised by peer commits b6a160b and 0899ef8, which added the Playwright overflow cases. Verify by running grep -n "break-words min-w-0" resources/js/pages/Skills/Show.vue resources/js/pages/Career/SkillsPlanner.vue and confirming the assertion exists in tests/browser/skills.spec.ts. Do not rebuild.

**Slice 0.3 (Invalid list roles).** Landed as 8dc73f2 for Dashboard.vue:258 and LegacySelect.vue:354, plus 469eb13 for the third site at ScenarioSelect.vue:214 (inside a collapsed <details> that a settled axe scan does not enter). The two browser cases in 469eb13 are **RED** pending a npm run build (see 1.16 below). Do not rebuild the fix; run 1.16 on a quiet tree to close the gate.

**Slice 0.5 (Veteran names on mobile).** Landed as acfb65f. The plan's file target was Veterans/Index.vue; the row lives in resources/js/components/VeteranRow.vue (imported at Index.vue:7, rendered at :241), shared with the Legacy Lab list. Verify by reading VeteranRow.vue and confirming the two-line wrap at narrow widths. Do not rebuild; do not edit Veterans/Index.vue.

**Slice 0.10 (Wizard heading semantics).** Landed as 253f957, which **satisfies the requirement by removing** the six in-body <p text-3xl> elements. The layout's #title slot at AppLayout.vue:351 owns the single <h1>. Verify by running the outline probe from tests/browser/career-scenario-select.spec.ts; each step has exactly one <h1> followed by <h2>s. Do not rebuild by adding an <h1> back into the body — that reproduces the duplicate the commit fixed.

---

## Deferred-owner slices — do not implement

**Slice 0.1 (Stepper truth).** The instruction says "Remove any state derived from 'visited'." That reverses the 2026-10-13 owner ruling recorded in KNOWN-ISSUES.md KI-103: *"'completed' means visited, not written to the draft."* Phase 0 slice 0.6 already quoted that ruling verbatim into SCREEN_SPEC.md's SCR-CAR-002. The visual audit's P0 is real, but its resolution has two options — either change the *semantics* (reverse KI-103) or change the *label* (keep visited as the completion semantic, stop rendering "done" for visited-only steps). Both are one-line changes to SetupLayout.vue. **Do not implement either until the owner rules.** Mark the slice deferred-owner in the ledger.

**Slice 1.1 (Tokens), palette portion.** Replacing the palette with the neutral system listed below is a **Track 2 amendment** per frontend-design-2026-10-13.md §6's Track 2 D ("A wider semantic colour vocabulary"). The current palette is client-measured (DESIGN.md §2.1: "both palettes are measured from client frames"). Replacing measured values with a preference is a design-contract change, not a Track 1 craft fix. **Do not implement the palette replacement until the owner rules.** The type scale, spacing scale, radius scale and shadow token are Track 1 (no ruling needed) and can land. Delete the palette-replacement step; keep the scale declarations.

**Slices 1.1's token deletion, enamel/lattice/torn-chip family.** These are owner-signed motifs per DESIGN.md §2.3's 2026-09-27 ruling ("three franchise motifs are material, not elevation, and are allowed"). The checkered header bar retirement is 0.10; retiring the *tokens* themselves is a Track 2 amendment. Measure consumer usage first (grep -rn "enamel\|lattice\|torn-chip" resources/js resources/css) and if any consumer remains, the token stays. If none remains, the token may be deleted as dead code. **Do not delete mood-tier or grade-fill tokens** — these are consumed by real components (MoodPill, GradeBadge) and carry client convention per DESIGN.md §6.7; retire their consumers only when the slice that replaces those components lands (Phase 1 slice 1.6 and Phase 3 slice 3.8 respectively).

---

## Phase 0: Correctness fixes (no design decisions)

### 0.1 Stepper truth — **DEFERRED-OWNER**, see "Deferred-owner slices" above.

### 0.2 Skill and planner overflow — **ALREADY LANDED**, see "Already-landed slices" above.

### 0.3 Invalid list roles — **ALREADY LANDED**, see "Already-landed slices" above.

### 0.4 Duplicate names and fixture rows — **SPLIT**: 1.8 landed, 1.9 blocked.

**Files:** app/Actions/ListVeterans.php, Runs/Index.vue, Veterans/*.vue, Legacy/Index.vue, the roster query behind the trainee step.

**Already landed as 74cabb2:** the trainee-picker fixture exclusion (plan slice 1.8). The Form Counted 1..4 rows appear **only in tests** (CareerTraineeSelectTest.php:227, CatalogRosterTreeTest.php:212); no seeder emits them. Both plan branches said "filter the query," so the branch was right for the wrong reason. The catalog screen /umamusume still shows every row and remains untouched by this fix. Verify by running tests/Feature/CareerTraineeSelectTest.php.

**Blocked as 1.9 (duplicate names):** the peer's eight uncommitted files are exactly this slice's files. Check git status --porcelain; if the peer's tree is dirty, **do not start**. Once clean:
- Add a label field to the run and veteran row mappers, built as "{name} · {scenario} · #{id}". Use it for every checkbox, link text and accessible name.
- Test: no two selectable rows on one page share the same rendered label. Land in veterans.spec.ts and veterans-compare.spec.ts.

### 0.5 Veteran names on mobile — **ALREADY LANDED**, see "Already-landed slices" above.

### 0.6 Snapshot Month and select semantics — **SPLIT**: Month landed, aria-required did not.

**Month portion** is landed as b90e114 (plan slice 1.7). The premise was corrected: Month's options were 1..12 with no empty option, so a fresh form's career_month='' matched nothing → selectedIndex -1 → **blank unlabelled select**, not a silent January record. StoreSnapshotRequest (required|integer|min:1|max:12) refuses blank. Verify by reading resources/js/pages/Career/Snapshot/Setup.vue around line 182 and confirming the placeholder is present. Do not rebuild.

**aria-required on BaseSelect is NOT landed** (plan slice 1.12).
- **Files:** resources/js/components/Form/BaseSelect.vue.
- **Change:** bind :aria-required="required" on the <select>.
- **Test:** the snapshot form's required selects expose aria-required="true"; a case in career-snapshot.spec.ts.

### 0.7 Encoding

**Files:** the layout that emits the document, the catalog profile mapper, the event-form placeholder.

Find where the placeholder â€¦ and the garbled voice-actor string originate. Check the source file encoding, the DB connection charset and the seeder JSON read. Fix at the source. Replace literal ellipsis characters in placeholders with … saved as UTF-8.

The audit agent's 77591a0 already fixed a **different** mojibake case (â—‹ on ScenarioPanel.vue); do not re-fix that one. The two remaining are on the Event decision page and the Admire Vega trainee page.

**Pest test:** render the Event decision page and the catalog detail for Admire Vega. Assert the response contains neither â€ nor Ã. Add a RenderedCopyHygieneTest case that fails on the [ÃÂ] sequence in resources/js/**/*.vue and in rendered copy.

### 0.8 Run-scoped deck picker

**Files:** pages/Support/Builder.vue, components/support/SupportSlot.vue, TrainingRunController::deck.

Make each slot's "Choose" button open the picker pre-targeted to that slot (?deck_slot=N). Replace "Equip owned to Slot 1" and "Equip rented to Slot 1" with one "Equip" button that uses the slot's ownership setting. Test: equip a card to slots 2 and 6 and reload; both persist.

### 0.9 Standalone error pages

**Files:** resources/views/errors/{404,419,500}.blade.php, resources/css/app.css.

**Change:** the primary button class becomes the same primary-action token used by the app. If 1.1's palette replacement lands first, the token is --color-primary-action per app.css:292; if not, use the current primary token (bg-[--color-primary-action] or the class the app's primary button already uses). Verify by reading resources/js/pages/Dashboard.vue's primary button class and matching it.

**Test:** the 404 primary button's computed background equals the app's primary button background.

### 0.10 Wizard heading semantics — **ALREADY LANDED**, see "Already-landed slices" above.

### 0.11 stepAbsent undeclared class binding — **NEW**

**Files:** resources/js/layouts/SetupLayout.vue (line 97) plus wherever the class should be defined, in resources/css/app.css.

Read what line 97 renders. Either define the class or remove the binding if it was speculative. Acceptance: no DOM node carries a class name with no definition in resources/css/app.css.

**Test:** grep the class name across the tree; zero uses, or one definition and its uses.

### 0.12 <dt>/<dd> without <dl> in ScenarioSelect.vue — **NEW**

**Files:** resources/js/pages/Career/ScenarioSelect.vue (line 221).

ScenarioSelect.vue:221 renders <dt>/<dd> inside a plain <div> with no <dl> ancestor — the exact shape SubCell.vue exists to prevent.

Wrap the pair in a <dl> element. Small change to the template; no CSS or data change.

**Test:** a case in career-scenario-select.spec.ts asserts each <dt> on the page has a <dl> ancestor.

### 0.13 Build debt clearance — **NEW, blocked on peer**

The two browser cases landed by 469eb13 are red because public/build was built at 20:59, before the fix. The harness serves the built manifest (public/hot absent, playwright.config.ts runs php artisan serve with no Vite).

This slice has no code change. It runs on the first quiet tree where npm run build completes without violating KI-70.

**Action:** confirm the peer's tree is clean and stays clean for the duration of the build. Run npm run build. Run accessibility.spec.ts's rule-scoped axe case over /, the scenario step with one disclosure opened, and the Legacy step; run career-scenario-select.spec.ts's structure case.

If either fails after the build, the fix is wrong, not the build — investigate before moving on.

No commit. This slice produces evidence.

### 0.14 Stale .enamel browser assertions — **NEW, needs filing decision**

career-snapshot.spec.ts at lines 45, 74 and 141 assert .enamel count, but the class was purged from the Vue application on 2026-10-15. grep enamel resources returns only resources/views/errors/{404,419,500}.blade.php. These have been red in every browser run since that date and reported as "pre-existing" without filing.

Read each assertion. The .enamel class was a control-treatment marker retired when the primary action colour moved to --color-primary-action. Replace the assertion with an equivalent that checks the current treatment, or delete the assertion if the treatment no longer exists.

**Acceptance:** career-snapshot.spec.ts runs green on a session-private harness; no .enamel string remains.

**Needs a filing decision before dispatch.** Either file a KNOWN-ISSUES.md entry (register rule) or land as a hygiene fix (this slice). The wrong answer is to keep reporting it as "pre-existing" without owner.

---

## Phase 1: Design foundation

**Track 2 note.** The palette portion of 1.1 is a Track 2 amendment per frontend-design-2026-10-13.md §6 Track 2 D. This phase's *other* slices — the type scale, spacing, radius, shadow, and the primitives 1.3 through 1.11 — are Track 1 and executable. The palette replacement waits on the owner.

### 1.1 Tokens — **SPLIT**: scales Track 1, palette Track 2

**Files:** resources/css/app.css, tests/Feature/DesignTokensTest.php.

**Track 1 portion (executable now).** Declare a type scale as tokens: 12, 14, 16 (body), 20, 28. Add a spacing scale of 4, 8, 12, 16, 24, 32, 48. Declare radius 6 and 10, and one --shadow-overlay. Update DesignTokensTest to assert the new scale tokens exist. Implement the browser check as real assertions rather than markTestIncomplete.

**Track 2 portion (deferred-owner).** The palette replacement. The proposed values are neutral-system values:

- page: light #F6F7F9, dark #0F141C
- surface: light #FFFFFF, dark #161D28
- border: light #D5DBE5, dark #2C3748
- ink: light #1B2433, dark #E8ECF3
- ink-muted: light #4A5568, dark #A8B3C4
- accent: light #1D4ED8, dark #8FB0FF
- on-accent: light #FFFFFF, dark #0F141C
- accent-soft: light #E8EEFD, dark #1E2A44
- positive: light #17663A, dark #6FD39B
- caution: light #8A5A00, dark #F2C46D
- danger: light #B42318, dark #FF8B7E

Do not implement the palette replacement without an owner ruling. The current palette is client-measured per DESIGN.md §2.1; replacing measured values with a preference is a Track 2 decision.

**Token deletion — measure first.** The enamel, lattice and torn-chip tokens are owner-signed motifs per DESIGN.md §2.3's 2026-09-27 ruling. The checkered header bar retirement is 0.10; retiring the tokens themselves requires confirming no consumer remains:
- grep -rn "enamel\|lattice\|torn-chip" resources/js resources/css
- If consumers remain, the token stays.
- If none, the token is dead code and may be deleted.

**Do not delete** mood-tier or grade-fill tokens. They are consumed by MoodPill and GradeBadge and carry client convention per DESIGN.md §6.7. Retire their consumers only when the replacement components land (slice 1.6 and slice 3.8 respectively).

### 1.2 App shell

**Files:** layouts/AppLayout.vue, layouts/CareerLayout.vue, components/app/NavGlyph.vue.

Remove the blue title bar and the version string. The shell header becomes a 48px bar: product name, collapse control, and a 2px accent rule at its bottom edge. Page titles move into PageHeader (1.3), on the page background. Constrain <main> to max-w-6xl mx-auto with px-4 md:px-8. Add aria-current="page" and a 3px left accent bar on the active nav item, in addition to the fill. Add scroll-padding-top and scroll-padding-bottom equal to the fixed bar heights.

**Test:** shell.spec.ts (new) asserts getByRole('banner').boundingBox().height === 48; stripe height is 2; <h1> is a descendant of <main>, not <header>; no v2.0.0 string in <header>.

### 1.3 PageHeader

**Files:** components/ui/PageHeader.vue.

Props: eyebrow?, title, description?. Renders one <h1> at 28px/semibold with text-ink, an optional eyebrow at 12px uppercase muted, and an optional one-line description capped at max-w-prose. A #actions slot sits right-aligned. Replace the title slot usage in every page with this component, piloted on five pages first: Dashboard.vue, Runs/Index.vue, Skills/Index.vue, Veterans/Index.vue, Database/Index.vue.

### 1.4 Card and Section

**Files:** components/ui/Card.vue, components/ui/Section.vue.

Card: surface background, 1px border, radius 10, padding 16 or 20, no shadow. Section: an h2 at 20px, optional description, 32px gap above. Forbid nested Card inside Card by lint rule (1.12).

### 1.5 DataTable

**Files:** components/ui/DataTable.vue, components/ui/Pagination.vue.

Real <table> with <th scope>, a caption prop (visually hidden allowed), column definitions (key, label, align, sortable, mono), a rows prop, a rowHref option and an empty slot. A wrapper with overflow-x-auto role="region" tabindex="0" :aria-label. Sortable headers use aria-sort and a button inside the th. A missing value renders as an en dash with <span class="sr-only">Not recorded</span>. Pagination: <nav aria-label="Pagination">, aria-current="page", "Page X of Y" text, and a Showing A–B of N line.

### 1.6 StatusBadge

**Files:** components/ui/StatusBadge.vue.

Props: tone: 'neutral'|'positive'|'caution'|'danger'|'info', label, optional icon. Always icon plus text. Tones map to token pairs from 1.1. Replace MoodPill's ad hoc colours, the Review "No match" pill and the Timeline "Updated" outline with this component.

### 1.7 ProvenanceMark

**Files:** components/ProvenanceBadge.vue (rewrite).

Render nothing for confirmed. Render a small badge for calculated, estimated and unknown. Accept a title that renders as sr-only text plus a visible popover on focus/hover (WCAG 1.4.13 compliant — hover-only information fails the standard). Delete every state="confirmed" usage at call sites.

### 1.8 FormField, inputs, SegmentedControl

**Files:** components/ui/{FormField,TextInput,Select,Textarea,Checkbox,SegmentedControl}.vue, delete or wrap components/Form/BaseSelect.vue.

FormField owns the <label for>, hint, error (aria-describedby, role="alert" on error), and the "Optional" suffix. Required is the default and unmarked. Only optional fields carry "(optional)". All controls are 44px tall, 14px or 16px text, radius 6, 1px border, a shared focus ring from the token. SegmentedControl renders real radio inputs inside a fieldset with a legend, selection shown by fill plus a check mark. Replace each page's local input classes with these components, one page per commit starting with Runs/Create.vue.

### 1.9 Combobox

**Files:** components/ui/Combobox.vue (generalise TraineeCombobox.vue).

Props: options (value, label, sublabel, lang), modelValue, label. Implements role=combobox, aria-activedescendant, arrow, Enter and Escape handling. Used for trainee on New run, Import and Snapshot, and for skill names on Target. Delete TraineeCombobox.vue and update its Playwright spec.

### 1.10 Stepper and StickyFooter

**Files:** components/ui/Stepper.vue, components/ui/StickyFooter.vue.

Stepper takes the steps from 0.1 **after the owner rules on the state semantics**. Until then, the layout keeps its current stepper. StickyFooter has Back (outline) on the left and a primary action on the right, position: sticky; bottom: 0 with a 1px top border, and the page container gets matching bottom padding. Add scroll-padding-bottom so focus is never hidden.

**Track:** StickyFooter is executable now (Track 1). Stepper is deferred-owner.

### 1.11 AbsenceNote

**Files:** components/ui/AbsenceNote.vue, components/AbsenceValue.vue (reduce).

AbsenceNote renders one line: "Not recorded: reward, running style, skill points." It takes labels: string[]. AbsenceValue is kept only for Energy on the Cockpit and any single decision-blocking value. Add the en-dash cell behaviour to DataTable (1.5).

### 1.12 Guard tests (agent contract as code)

**Files:** tests/Feature/UiHygieneTest.php, tests/browser/ui-hygiene.spec.ts, tools/ui-lint.mjs, package.json script lint:ui.

Pest: scan resources/js/**/*.vue and app/Http/Controllers/** string literals for ADR-\d, \bD-\d{2,3}\b, \bG-\d, KI-\d, §\d, docs/, backticks in template text, .php/.json file paths, and N/A in template text. Fail on <details> whose summary is only "N/A". Lint: fail on Card nested in Card and on new arbitrary Tailwind values ([#, [\d+px]). Playwright: on every route, assert exactly one h1, no h2 text equal to the h1, no version string in the header, and no element with a background equal to the legacy brand-blue token. Wire npm run lint:ui into the hand-off sequence.

**Note on the N/A scan.** The sr-only "Not recorded" span from 1.5 and the AbsenceValue disclosure on the Cockpit are legitimate. The scan should fail on N/A inside template text nodes (visible), not on N/A inside title attributes or sr-only spans.

---

## Phase 2: Copy and noise purge (five slices per dispatch)

### 2.1 Developer text removal

**Files:** all controllers that build title/reason strings, config/reference.php, config/scenarios.php display strings, Vue pages.

Run the 1.12 scan and fix every hit: delete the identifier, keep the plain-language sentence, or delete the sentence if it only restated the identifier. Delete "TrainerAdvisor holds no event advice" and similar internal-name copy. Remove seeder paths, field names (skills_unique) and source URLs from trainee, support-card and skill pages. Keep the source link inside one disclosure.

**Note:** RaceStripBuilder.php:63 prints (KI-17) and RaceFacts.php:64 prints a backtick-quoted ADR-0016 in user-visible titles. These are Phase 0 1.10-class defects and should be fixed here if not already done. Replace with "The turn was not recorded against this race" and "Win probability is not held by this tool" respectively.

### 2.2 Data sources page

**Files:** routes/web.php, Database/Sources.vue, DatabaseController::sources, footer link in AppLayout.vue.

Create /database/sources listing each dataset, its source URL, last-fetched date and ruleset note, read from config/reference.php and data_sources. Link it from the footer ("Data sources"). Move every provenance paragraph that was inline on Events, Shop Items, Sparks, Scenarios and trainee pages into this page or into one disclosure per page.

### 2.3 Absence policy application

**Files:** every Career/*, Database/*, Catalog/* page.

Delete per-field AbsenceValue usage except Energy on the Cockpit. In tables, rely on the DataTable dash. In detail views, add one AbsenceNote per group. Delete explanatory "why this is empty" sentences repeated per row or card. Keep one page-level "About this data" disclosure where an explanation matters.

### 2.4 Terminology

**Files:** AppLayout.vue nav items, Runs/*, lang/en/uma.php, button labels.

Rename user-facing "Training runs", "run", "New run" to "Career"/"Careers"/"New career". Rename "Support Deck Select" to "Support deck". Use the same label in nav, h1 and button. Update Playwright locators accordingly.

### 2.5 Repeated sentences

**Files:** LegacySelect.vue, Legacy/Builder.vue, RaceCard.vue, Review/Index.vue, Preflight.vue, SetupLayout intro text.

Show each helper sentence once per field group, never per field. Rank letters get one line at the top of the group. Delete the duplicate intro paragraph on every wizard step. Delete the "Record only…" banner from screens other than the Legacy builder; replace with a short help popover on the builder. Delete the distance-band note from each race card; show it once above the race list. Delete "Back to <this page>" links on every wizard step.

---

## Phase 3: Setup wizard rebuild (three slices per dispatch)

### 3.1 Wizard shell

**Files:** layouts/SetupLayout.vue.

Compose AppLayout + ChoicesSoFar + PageHeader slot + StickyFooter slot. The stepper stays in its current form until the owner rules on 0.1. Add focus management: on each step load, focus the h1 (tabindex="-1") and set document.title.

### 3.2 ChoicesSoFar

**Files:** components/career/ChoicesSoFar.vue, SetupDraft::summary().

A compact bar: "Scenario: Unity Cup · Trainee: Rice Shower · Target: Speed focus", showing "Not chosen" in muted text for gaps. Replace every "Scenario: ▶ N/A"-style line.

### 3.3 Step 1, Scenario

**Files:** Career/ScenarioSelect.vue.

Cards become a radiogroup of real radio inputs styled as cards. Each shows name, one-sentence summary and three chips (stat cap, team system, shop). A "Show details" disclosure replaces the bordered empty-looking box. One "Save and continue" in the footer. Selecting a card no longer navigates. Add a "Compare scenarios" link to /database/scenarios.

### 3.4 Step 2, Trainee

**Files:** Career/TraineeSelect.vue, Career/TraineeProfile.vue, TraineeSelectController.

Replace 25 stacked cards with a DataTable: portrait, name (with lang="ja" second line), best three aptitudes as chips, form count, a radio-select column. Sticky filter bar with Surface, Distance, Style, Search, Sort; "Clear filters" on the same row; pageSize 25 and "Page X of Y". Costume-form select appears only inside the profile view for multi-form trainees, labelled. Profile page: dl with left-aligned values, aptitude grouped into Surface/Distance/Style rows of grade chips, skills as one table with a Source column, delete the Build analysis section. Sticky "Select this trainee" in the footer.

### 3.5 Step 3, Target

**Files:** Career/BuildTarget.vue.

Group "Purpose" in a fieldset with a SegmentedControl for the four purposes. Replace the three-line stat rows with a five-row grid: Stat | Target | Ceiling (optional) | Scenario cap. Show "Not available" in the Ceiling cell for Wit with a one-phrase reason. Remove the progress bars until a target value exists. Skill priorities use the Combobox over the skills catalogue; each row has Move up, Move down and Remove buttons named with the skill. Hide the summary until at least one value is set.

### 3.6 Step 4, Legacy

**Files:** Career/LegacySelect.vue, components/legacy/AncestryNode.vue.

Lay out a tree on desktop: trainee left, parents middle, grandparents right (CSS grid with connector lines); stack on mobile. Provide a nested <ul> structure with headings "Parent A", "Grandparents of Parent A" so structure exists without lines. Rank is a Select of letters beside a narrow numeric stars input. "Add spark" is a text button with a plus icon, named per person. Show "Not chosen" once per parent until chosen. Delete the four stacked "Rank not recorded / No Sparks recorded / Spark chance N/A" lines. Move Affinity into a "Pair" fieldset after Parent B. Every control name includes its person: "Parent A, own rank (letters)".

### 3.7 Step 5, Support deck

**Files:** Career/DeckSelect.vue, components/support/{SupportSlot,CardPicker}.vue.

Show six slots in a 3×2 grid (one column on mobile). Each slot shows card thumb, name, type chip and an Owned/Rented SegmentedControl. An empty slot shows "Empty" and a "Choose card" button. "Choose card" opens a <dialog> picker with a searchable list (type, rarity, name filters), each row with art, name, type icon plus text, and one "Equip" button. Closing returns focus to the slot button. Hide "Deck analysis" until at least one card is equipped. Support-type legend: icon plus text, with distinct shapes per type.

### 3.8 Step 6, Review and start

**Files:** Career/Preflight.vue, StartCareerRequest.

Retitle the page "Review and start". One checklist row per step: status icon, label, summary of what was entered or "Not entered", and an "Edit" link. Delete the Warnings section and the seven Edit buttons. Show a heading with a count ("2 steps need attention") with role="status". Primary "Start career". If a required step (scenario, trainee) is missing, disable it and show the reason; focus the first missing row on attempt. On success, redirect to the Cockpit with a role="status" flash "Career started for {name}".

---

## Phase 4: Cockpit and career screens (three slices per dispatch)

### 4.1 CareerHeader

**Files:** components/career/CareerHeader.vue, layouts/CareerLayout.vue.

A persistent header: trainee, scenario, status badge, turn/position. Below it, tabs as a real <nav> of links: Cockpit, Training, Races, Skills, Events, Legacy, Deck, Timeline, Result. Disable tabs with an unavailable reason ("Available when the career is completed") using <span role="link" aria-disabled="true"> with a title, **not** <Link> — aria-disabled alone does not disable navigation. Remove per-page Cockpit/Dashboard link pairs.

**Test:** a click on the disabled tab does not navigate — assert URL is unchanged.

### 4.2 Cockpit layout

**Files:** Career/Cockpit.vue, components/career/{StatSummary,ActionGrid,AdvisorRail}.vue.

A sticky summary strip at the top: energy, mood, fans, SP, turn, and the recommended action as the single primary button. Two columns on desktop: main content and a sticky Advisor rail (min-w-[20rem], top offset equal to the header). One column on mobile with the recommendation first. Scenario panels become collapsible <details>-style sections, each with a one-line summary visible when collapsed. Delete the green striped capsule headers; section titles use Section. Only one filled accent button on screen: the recommendation.

### 4.3 Training decision

**Files:** Career/TrainingDetail.vue, components/career/TrainingCard.vue.

Training cards are outline buttons; the advisor's recommendation is the one primary. Move source/provenance text into a per-card "Sources" disclosure. Delete the repeated "Not ranked" sentence. The record form becomes step two ("What happened"), with fields initially empty and last-recorded values shown as helper text, not placeholders. Energy becomes a fieldset with legend "Energy" and vertical radios: Exact, Band, Not recorded.

### 4.4 Race decision and planner

**Files:** Career/RaceDecision.vue, Career/RacePlanner.vue, components/career/RaceTable.vue.

Replace card stacks with DataTable: Race, Grade, Distance, Surface, Fan gate, Status, with the row opening a details dialog. Place one AbsenceNote above the table ("Not recorded for any race here: reward, running style…"). Mandatory races use a neutral card; delete the orange alert border. Fix the "NEXT" marker to point at the next obligation not already past, and add a test. Calendar vs off-calendar becomes a real tab list. Planner: compare checkbox column plus a sticky "N selected, Compare" tray; remove the empty bordered boxes and "Held figures" row. Group placeholder races with no catalogue details into one collapsed "Races with limited data (N)" disclosure (Phase 7 slice 7.7 relies on this).

### 4.5 Skills planner

**Files:** Career/SkillsPlanner.vue.

Collapse the four info boxes and empty sections into one summary line. Merge Learned cards and the Acquired table into one DataTable: Skill, Base SP, Status, Turn acquired, Conditions (expandable row). Use accessible names including the skill. Make "Save acquisition status" visibly enabled or show why it is disabled.

### 4.6 Event, Inheritance, Timeline, Result, Save Veteran

**Files:** corresponding pages.

Event: form first, history below; one empty state; fix placeholder encoding (0.7). Inheritance: keep the empty state (one sentence, one primary, one outline); delete the decision-ID reference. Timeline: hide the filter card unless more than one kind exists; render "Updated" with a neutral badge; replace the TURN/Turn/Turn recorded triplet with one label. Result and Save Veteran: when not applicable, never reachable (tab disabled per 4.1); the Result page keeps one primary and one outline.

---

## Phase 5: Reference tables (three slices per dispatch)

### 5.1 Race database

**Files:** Database/Races.vue, DatabaseController::races.

Add filters (year, tier, surface), sortable headers, server-side sort whitelist, "Showing A–B of N". Drop the all-N/A Gate column; show a badge only for mandatory/special.

### 5.2 Skills search and detail

**Files:** Skills/Index.vue, Skills/Show.vue.

Sticky filter bar, Page X of Y, footnote above the list, dash for missing SP. Detail: condition in a wrapped monospace block with a "Raw engine data" label, effect chips named in words if a label exists, and one provenance disclosure.

### 5.3 Support cards

**Files:** SupportCards/Index.vue, SupportCards/Show.vue.

Index becomes a DataTable: Card (art + name), Rarity (one chip, "SSR" with aria-label "SSR, 3 star"), Type, Friendship %, Training effectiveness %, Availability. Remove the monospace stat string. Detail keeps effect chips.

### 5.4 Trainees catalog

**Files:** Catalog/Index.vue, Catalog/Show.vue.

Collapse costume forms under each trainee (<details>). Show debut date once. Detail: group aptitudes, collapse empty runs and aliases into one-line rows, move source details into one disclosure, put lang="ja" on Japanese strings. Confirm whether the fixture-row exclusion from 0.4 should extend to the catalog screen — if so, apply the same controller filter; if not, note the scope difference in the ledger.

### 5.5 Scenarios, Shop, Sparks, Events, hub

**Files:** Database/{Scenarios,ShopItems,Sparks,Events,Database}.vue.

Scenarios: two tables, "Stat caps by scenario" and "Panels by scenario" (✓ / —), replacing four cards. Shop, Sparks, Events: DataTable with left-aligned text, right-aligned numbers, server tags as chips (JP, Global), and the confidence column only for non-confirmed rows. Hub: cards with dataset name, one-line description, record count, last updated, chevron.

---

## Phase 6: Forms, lists and library (three slices per dispatch)

### 6.1 New career entry and forms

**Files:** Runs/Create.vue, Runs/Import.vue, Career/Snapshot/{Entry,Setup,Review}.vue, NewCareer.vue.

Create a single /career/new page with three choices (start fresh, from snapshot, import a finished career). Point the nav "New career" at it; remove the redundant "New run" button. Rebuild New career, Import and Snapshot on FormField/Combobox, with "Optional details" disclosures. Import: file input as button plus filename; a visible sample CSV header. Snapshot: one "Career setup" card; replace eight "I don't know this value" checkboxes with a per-row "Not shown" toggle named "Speed: not shown".

### 6.2 Review queue

**Files:** Review/Index.vue.

DataTable: Source name, Japanese name, Suggested match, Verdict, Resolve. Sort rows with suggestions first. Resolve is disabled until a verdict is chosen, with the reason in text. Show the date once. Resolve button names include the candidate.

### 6.3 Veterans and Legacy Lab merge

**Files:** Veterans/*.vue, Legacy/*.vue, routes/web.php, AppLayout.vue nav.

One "Veterans" area with tabs: Library, Legacy, Compare. Remove the Legacy Lab nav item and redirect old routes. Library is a DataTable: Name (disambiguated label), Scenario, Result, Tags, Actions. Replace Newest/Oldest buttons with a Select. Compare: disable Compare until two are selected; one empty state; delete "NOT IN THIS BUILD".

### 6.4 Dashboard

**Files:** Dashboard.vue, DashboardController.

Active career card with primary Resume, quick actions as outline buttons, Recent veterans as a short list. Hide "Recent builds" until it has data. If more than one active career exists, show "N active careers" with a link (Phase 7.9 supplies the count).

---

## Phase 7: Remaining functional slices (three slices per dispatch)

### 7.1 Stat denominators

**Files:** CareerStatePanel.vue, Career/EventDecision.vue, shared StatValue.vue.

Add StatValue with current, denominator, denominatorLabel ("Target" or "Ceiling"). Use it in both screens. Missing target renders "No target". Test target vs ceiling are never swapped.

### 7.2 Skills planner empty-priority state

**Files:** app/Services/SkillSpendCoverage.php, SkillsPlannerController, SkillsPlanner.vue.

Return state: 'none'|'covered'|'short'. For none, render "No priorities set" with a link to the Target step. Never show "0 SP" for none. Tests for all three states.

### 7.3 Deck builder entry

**Files:** Career/TrainingDetail.vue, CareerHeader.vue.

Point deck links at runs.deck for the current run. Add the "Deck" tab (4.1). Add a "Back to Cockpit" return link. Test the link never targets /career/setup/deck.

### 7.4 Energy band path

**Files:** StoreTurnEntryRequest, turn actions, TrainingDetail.vue, Cockpit correction form, Timeline row.

Accept the same energy_state values in create and update. Preserve the stored value when the key is omitted. Show the band in Cockpit and Timeline. Feature test for store, update and omitted-key preservation.

### 7.5 Identity editor

**Files:** Cockpit.vue, components/career/IdentityForm.vue, StoreTrainingRunRequest.

Editable rarity, potential level and five growth rates, posted to runs.update. Partial updates keep status, scenario, target and period unchanged. Header reflects saved values.

### 7.6 Unity Cup state editor

**Files:** components/scenario/UnityCupPanel.vue, StoreTrainingRunRequest.

View/edit toggle covering team name, motto, league placement, preseason wins, Unity Trainings, Spirit Bursts, Extreme Bursts. Hidden for other scenarios. Feature tests: persistence, validation, scenario isolation.

### 7.7 Race planner density

**Files:** RacePlanner.vue.

Use the collapsed "Races with limited data" disclosure from 4.4. Required races stay visible. Test that every race remains reachable.

### 7.8 Turn correction

**Files:** Cockpit correction component.

Label "Correct turn N" vs "Record next turn". Preserve form state on validation failure. Disable the submit while pending. Test it updates only the targeted turn.

### 7.9 Dashboard multi-career

**Files:** DashboardController, Dashboard.vue.

Count active careers with a count query. Show a notice and link when more than one exist.

### 7.10 Trackblazer state

**Files:** components/scenario/TrackblazerPanel.vue.

Make grade-point period, current grade points and shop coins editable through validated paths where the schema supports them. Hide on other scenarios. Catalogue stays read-only.

### 7.11 Our Grand Concert state

**Files:** components/scenario/GrandConcertPanel.vue (strip), controller props.

Expose only fields already in the schema. Keep unknown distinct from zero. No computed mechanics. Scenario-isolation tests.

### 7.12 Scenario shell consistency

**Files:** ScenarioPanel.vue and four panel components.

Shared header, summary, edit/view toggle, save feedback and empty state. Labels come from server props only.

### 7.13 Result → Save Veteran

**Files:** Career/Result.vue, SaveVeteranController.

If already saved, show "View veteran" instead of "Save". Idempotent save. Inline conflict errors.

### 7.14 Veteran and Legacy comparison

**Files:** Veterans/Compare.vue, Legacy/Compare.vue.

Align rows by label; dash for missing; empty and one-record states with a next action; responsive row layout.

### 7.15 Catalog to wizard handoff

**Files:** Catalog/Show.vue, TraineeSelectController.

"Start career" writes umamusume_id to the draft and opens step 1 (scenario). Test no run exists before Preflight.

---

## Phase 8: Accessibility, responsive and end-to-end (five slices per dispatch)

### 8.1 Names and semantics

**Files:** all repeated controls.

Give every repeated control a unique accessible name beginning with its visible text ("Compare Vodka, URA Finale, #7"). Add lang="ja" to every Japanese string. Replace glyph-only markers (○, stars) with text or aria-label. Test with axe plus a Playwright accessible-name uniqueness check per page.

### 8.2 Progress indicator (KI-63)

**Files:** resources/js/spa.ts, shell.

Replace the invalid role="bar" with a valid, labelled role="progressbar" or a polite status region. Respect reduced motion. Regression assertion on rendered DOM.

### 8.3 States

**Files:** touched flows.

Standardise busy/disabled submit, field-level errors with aria-describedby, form-level role="alert" with focus, empty states with one action, and accurate success messages ("Saved", "Recorded").

### 8.4 Responsive sweep

**Files:** Playwright specs.

Run every primary route at 320, 390, 768 and 1280px. Assert no horizontal page overflow (excluding labelled scroll regions), all primary actions visible, 44px targets (tests/utils/tap-targets.ts), and focus not hidden by sticky bars.

### 8.5 Journey tests

**Files:** tests/browser/journey-new-career.spec.ts, tests/browser/scenario-isolation.spec.ts.

Journey: scenario → trainee → target → legacy → deck → review → start; edit identity; record a turn; open the deck; plan a race and a skill; finish; save a veteran; open it. Assert no run before Start and exactly one after. Isolation: parameterise the four scenarios; assert each shows only its own fields; assert partial updates preserve unrelated fields.

### 8.6 Final gates

Run in order: php artisan test --compact, vendor/bin/pint --test --format agent <files>, vendor/bin/phpstan analyse --no-progress --memory-limit=1G, npm run typecheck, npm run lint:ui, composer lore, composer lore-code, npm run build, npm run test:browser (private port and scratch DB), git diff --check. Fix failures caused by these slices only. Report pre-existing failures as pre-existing with their commit citation.

### 8.7 Open every disclosure before axe scan — **NEW**

**Files:** tests/browser/accessibility.spec.ts.

Before each axe scan, open every <details> on the page: page.locator('details').evaluateAll(details => details.forEach(d => d.open = true)) before buildAxe(page).analyze(). The third role="list" site at ScenarioSelect.vue:214 survived Batch 1 because it lived inside a closed disclosure that a settled scan never entered. This case makes that kind of miss impossible.

### 8.8 Route coverage map — **NEW**

**Files:** tests/browser/accessibility.spec.ts or a new tests/browser/route-coverage.spec.ts.

A table of every live route and its corresponding axe scan case. A test asserts that every route in the map has at least one axe scan. /career/setup/scenario had no scan until Batch 3; the map prevents that kind of gap.

---

## Execution order

0.4 (duplicate-name portion, blocked on peer) → 0.6 (aria-required portion) → 0.7 → 0.8 → 0.9 → 0.11 → 0.12 → 0.13 (blocked on quiet tree) → 0.14 (needs filing decision). Slices 0.1 (stepper) and 0.2, 0.3, 0.5, 0.10 are marked already-done or deferred-owner — verify, do not rebuild.

Then 1.1 (scales portion; palette deferred-owner) → 1.2 (needs 1.3) → 1.3 through 1.9 (one primitive per dispatch) → 1.11 → 1.12. 1.10's StickyFooter half is executable; its Stepper half is deferred-owner.

Then 2.1 → 2.5, 3.1 → 3.8, 4.1 → 4.6, 5.1 → 5.5, 6.1 → 6.4, 7.1 → 7.15, 8.1 → 8.8.

Slices 7.1 through 7.15 depend on Phase 1 primitives only, so they may be pulled forward after Phase 2 if needed. If a slice's change is already in the tree, verify it with its listed test and skip it rather than rebuilding it — the "Already-landed slices" section above names the SHAs for the four slices that are done.

**The batch limit is not a suggestion.** When the limit is reached, stop and hand off. Do not expand because a slice ran faster than expected. The next dispatch reads the execution ledger at .scratch-uma/execution-ledger-2026-10-17.md before its own Phase 0.