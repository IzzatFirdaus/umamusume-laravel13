# Trainer Desk — implementation backlog (code only)

Read this file in full before any dispatch.

**Plan of record:** `trainer-desk-implementation-plan-2026-10-17.md` (Document 2). Its
numbering is the one used here (phases 0–8). Where a plan slice is cross-referenced to
Document 1's numbering, the plan-1.xx alias appears in brackets.

**Precedence (adopted from `AGENTS.md` §2, the execution contract §2, and the ledger):**
- Owner rulings > the tree > execution contract > `DESIGN.md` > `SCREEN_SPEC.md` >
  plan > audits.
- When code and a written rule disagree, the code wins; record the conflict; do not
  edit the rule to match.

**Read before every dispatch (do not skip):**
- `docs/proposals/execution-ledger-2026-10-17.md` — most current state.
- `AGENTS.md` §9 and §18.
- `DESIGN.md` §1, §2, §5, §6 — for any UI slice.
- `SCREEN_SPEC.md` §8 authority table and the dated note(s) on the screen being
  touched.
- The plan-of-record §for the slice being dispatched.
- `frontend-fidelity-2026-10-13.md` §2.3, §2.4, §6, §7 — the Track 1 / Track 2 split.
  Note: frontend-design-2026-10-13.md is **absent from the tree**; the file the plan
  means is `frontend-fidelity-2026-10-13.md`. Reported by an earlier dispatch and
  corrected here.

---

## §A — Standing rules (inherited; not repeated per slice)

- **`git commit -- <paths>` on every commit. Never bare.** The `5a08db1` split-commit
  is why.
- **One slice per dispatch.** No batching. A slice not in this file is scope
  expansion; record in the ledger, do not implement.
- **Premise check first.** Confirm HEAD, tree state, `origin/master`, per-file
  dirty on the slice's target paths, and live-peer detection. Do not start until done.
- **Cross-check every SHA** with `git show --stat` in the same report.
- **`git rev-parse origin/master` and `git status -sb`** before any "nothing pushed"
  claim.
- **Read from `git show HEAD:<file>`** when a peer edit is live on that file.
- **`npm run build` only on a quiet tree** (KI-70, KI-74). A stale `public/build`
  means any browser case reading the bundle is red on the stale artifact, not the
  code. Report red-as-stale, do not weaken the assertion.
- **Browser harness is session-private** (KI-73, KI-80). Use
  `PLAYWRIGHT_PORT=<own> PLAYWRIGHT_SCRATCH_DB=.scratch-uma/<session>/browser-<port>.sqlite`.
- **Never `pint --dirty`** (KI-75). Use explicit paths.
- **Pre-flight any broad sweep** with a timed SQLite read; contended = void (KI-74).
- **Evidence rule:** a claim is the command tail. Quote it. A gate not run is
  reported as not run.
- **`N/A` + `title`** for any unrecorded value. Never a dash. Never a default.
- **No em dash in shipped copy.** No `ADR-`/`KI-`/`D-`/`G-`/`SCR-*`/`F-xx` identifiers
  in user-visible strings. `DESIGN.md` §6 (2026-10-17) governs.
- **Lore gate on every change.** Every hit carries a one-line ruling.
- **Change class → minimum gate:**
  - Copy/styling only: affected feature test.
  - Model/action/service logic: `php artisan test --compact`.
  - Route/controller/Form Request/view: that screen's feature tests + `SCREEN_SPEC.md`
    state table (via §15 side-effect if the screen behavior changed).
  - Parser/fetch: parser tests + `Http::fake` pipeline tests.
  - Schema: migration + `ARCHITECTURE-ESSENTIALS.md` digest + PRD citation + ADR +
    `migrate:status` clean on dev database.
  - TypeScript/Vue: `npm run typecheck` (+ `npm run build` if `public/build` was
    stale; if a peer is live, red-as-stale per above).
  - Cross-layer: full suite, then hand-off sequence.
- **Hand-off sequence at slice end:**
  `php artisan test --compact` → `pint --test --format agent <paths>` →
  `phpstan analyse --no-progress --memory-limit=1G` → `npm run typecheck` →
  (`npm run build` iff quiet tree) → session-private `npm run test:browser` (spec
  only) → `composer lore` → `composer lore-code`.
- **Read-back gate before final commit of any phase:** `python tools/md_fence_check.py`
  on any `.md` touched, and `--filter=DocAccuracyTest` / `--filter=DocCitationParity`
  if the plan-of-record or `SCREEN_SPEC.md` moved.

---

## §B — Already completed — verify only, do not rebuild

Premise check each SHAs presence with `git show --stat <sha>` before assuming the fix
landed. If the fix's diff is present in HEAD, mark `already-satisfied` in the ledger
and skip. If absent, treat as TODO below.

| Plan slice | Alias | SHAs | Verify by |
|---|---|---|---|
| 0.1 | plan 1.1 (partial) | (owner ruling recorded; **code fix deferred-owner**) | `SetupDraft::visited()` docblock quotes 2026-10-13 ruling; `KI-103` is OPEN |
| 0.2 | plan 1.2 (partial) | `07ca152`, `ce6cb9d` | `grep -n "break-words min-w-0" resources/js/pages/Skills/Show.vue resources/js/pages/Career/SkillsPlanner.vue`; assertion in `tests/browser/skills.spec.ts` |
| 0.3 | plan 1.3 | `8dc73f2` (Dashboard.vue:258, LegacySelect.vue:354), `469eb13` (ScenarioSelect.vue:214) | axe `aria-required-children` on `/`, scenario step, legacy step; cases in `accessibility.spec.ts` and `career-scenario-select.spec.ts` |
| 0.5 | plan 1.4 | `acfb65f`, revised by `b6a160b` and `0899ef8` | `VeteranRow.vue` (not `Veterans/Index.vue`) carries the two-line wrap |
| 0.6 (Month half) | plan 1.7 | `b90e114` | `resources/js/pages/Career/Snapshot/Setup.vue` ~:182 |
| 0.6 (aria half) | plan 1.12 | **`c2de34b` (2026-10-17)** | `BaseSelect.vue` binds `:aria-required="required"` on both branches; `tests/browser/base-select.spec.ts` passed 15.4s; reverted red on the fix |
| 0.10 | plan 1.6 | `253f957` (+ `66f2029`, `0d3a69c`) | shell `#title` h1 alone on each wizard step |
| (verify) | plan 1.10 | `8dc73f2` or peer | `grep -rn "KI-\|ADR-" app/Services/Career/` returns zero user-visible strings; if still present, slice below as TODO |
| Phase 0 amendments | — | `1a1f88f` (0.1), `9f2504b` (0.2), `66561e1` (0.3), `09b1d41` (0.4), `b6c5784` (0.5), `ab1d849` (0.6), `5e2a9e2` (0.7), `1a5cb04` (0.8), `9b16bfd` (0.9), `0a6fee2` (0.10), `ff199ee` (0.5 correction + 0.11 spec-side), `f78ee18` (0.11 DESIGN), `6690d15` (0.11 completion) | present in `DESIGN.md` / `SCREEN_SPEC.md` changelogs |

Peer commits (do not rebase, do not edit; read for context): `b6a160b`, `0899ef8`,
`90c305d`, `77591a0`, `66f2029`, `0d3a69c`, `253f957`, `b0c536f`, `6c81dff`.

---

## §C — Deferred-owner — do not attempt

A dispatch that reaches one of these skips and states the skip.

- **0.1 stepper semantics.** KI-103 two-option ruling (change semantics vs change label).
- **1.1 Track 2 palette replacement.** Track 2 amendment per `frontend-fidelity-2026-10-13.md`
  §6. The Track 1 scale half is executable (see Phase 1 below).
- **1.10 Stepper half** (Stepper.vue). StickyFooter half is executable.
- **The ten owner-gated items in orientation §6** — same list as plan §13. Do not touch
  a slice that reaches one.
- **Track 2 clauses (radius, shadow, display font, brand colour, motion).** Until the
  `DESIGN.md` amendment is signed, only Track 1 lands. The 2026-10-15 owner-signed
  amendments are already landed in `DESIGN.md`; do not re-litigate.

---

## §D — Blocked — do not attempt until unblocked

- **0.4 duplicate career names (peer).** The eight uncommitted peer files are
  `1.9`'s named files (`VeteranRow`, `Veterans/*`, `Legacy/Index`, `Dashboard*`).
  Last write **21:09** at the batch-4 close. Recheck on every dispatch; the moment
  `git status --porcelain` on those paths is empty, 0.4 becomes eligible.
- **0.13 build debt clearance** for `469eb13`'s two red browser cases. Needs a
  quiet tree for `npm run build`. Recheck on every dispatch.
- **0.14 stale `.enamel` assertions.** Needs an owner filing decision (register entry
  vs hygiene fix). The contract-correct resolution is **hygiene fix**: the `.enamel`
  class was retired 2026-10-15, the assertion is wrong, the code is right. Awaits the
  owner's word.

---

## §E — Backlog by phase

Slices below are ordered per Document 2 §"Execution order" and each depends only on
prior phases. Batch limits from Document 2 apply: Phase 0–1 = 3 slices, Phase 2 = 1
primitive, Phase 3–8 = per Document 2's per-phase cap (3 for most, 5 for Phase 8).

If a slice's premise fails at dispatch (code already present, or the file/line the
plan names is stale), record `premise-failed` in the ledger and **skip** — do not
substitute a nearby slice.

### Phase 0 — correct existing defects (limit 3 per batch)

#### 0.7 — Encoding defects
**Depends on:** none. **Files:** locate source of `â€¦` on Event decision page and `Ã`
in Admire Vega voice actor; grep `resources/js`, `app`, `database/seeders`, `config/uma.php`;
read source-file encoding, DB connection charset, seeder JSON read. Fix at source. New
case in `tests/Feature/RenderedCopyHygieneTest.php` refusing `[ÃÂ]` in `resources/js/**/*.vue`
and in the two rendered routes. **Do not re-fix `â—‹` on `ScenarioPanel.vue`** (`77591a0`
closed it). **Acceptance:** both strings render correctly; `RenderedCopyHygieneTest` green;
`php artisan test --compact --filter=RenderedCopyHygiene` quoted. **Commit:**
`fix(copy): repair the two remaining mojibake strings and add a UTF-8 guard`.

#### 0.8 — Run-scoped deck picker (VERIFY)
**Depends on:** none. **Verify first:** `grep -n "deck_slot" resources/js/pages/Support/Builder.vue`
and `components/support/SupportSlot.vue` and `TrainingRunController::deck`. If each slot's
Choose opens the picker pre-targeted at its own slot (`?deck_slot=N`), and there is one
`Equip` action per slot respecting ownership, mark `already-satisfied` and skip. If
either is missing, build: pre-target per slot, one `Equip` per slot using the slot's
ownership (`deck_slots.ownership`, `ADR-0023`). **Files:** the three above +
`tests/browser/deck.spec.ts` (new or extended: equip to slots 2 and 6, reload, both
persist). **Acceptance:** the two slots persist through reload; ownership is honored.
**Commit:** `fix(deck): per-slot picker with a single Equip action`.

#### 0.9 — Standalone error pages (VERIFY)
**Depends on:** none. **Verify first:** `grep -n "primary-action" resources/views/errors/{404,419,500}.blade.php`.
If all three carry `--color-primary-action` classes, mark `already-satisfied`. If any
still uses olive/green, migrate: match the button class on the current `Dashboard.vue`
primary. **Files:** `resources/views/errors/*.blade.php`. **Test:** `ErrorPageViewsTest`
already asserts the views render; extend it (or a new `ErrorPagePrimaryColorTest`) to
assert the 404 primary's computed background equals the app's primary. **Commit:**
`fix(errors): migrate the standalone error documents to the primary action colour`.

#### 0.11 — stepAbsent undeclared class binding
**Depends on:** none. **Files:** `resources/js/layouts/SetupLayout.vue` (~:97),
`resources/css/app.css`. Read what the binding renders; define `stepAbsent` in
`app.css` (an `@utility` or a `.step-absent` class the element's use needs) or remove
the binding if speculative. **Acceptance:** `grep -rn "stepAbsent" resources/js resources/css`
shows either zero uses or one definition + uses. No DOM node carries an undeclared
class. **Commit:** `fix(wizard): remove or define the orphan stepAbsent class binding`.

#### 0.12 — `<dt>/<dd>` without `<dl>` at `ScenarioSelect.vue:221`
**Depends on:** none. **Files:** `resources/js/pages/Career/ScenarioSelect.vue` (:221).
Wrap the pair in `<dl>`. **Test:** extend `tests/browser/career-scenario-select.spec.ts`
with a case asserting each `<dt>` on the page has a `<dl>` ancestor. **Acceptance:**
`dlitem`-rule clean; no CSS change. **Commit:** `fix(wizard): wrap the facts grid's dt/dd
pair in a dl`.

#### 0.14 — Stale `.enamel` assertions — **BLOCKED pending owner ruling**
If the owner rules hygiene fix: read `tests/browser/career-snapshot.spec.ts:45,74,141`;
replace each `.enamel` assertion with one that checks the current treatment
(`--color-primary-action` class or attribute), or delete if the treatment no longer
exists. **Files:** `tests/browser/career-snapshot.spec.ts` only. **Acceptance:** green
at a session-private harness; `grep -rn "enamel" resources` is zero apart from
`resources/views/errors/*.blade.php` (kept). **Commit:** `test(snapshot): assert the
current primary treatment, not the retired .enamel class`.

### Phase 1 — design foundation (limit 3 per batch; one primitive per dispatch when building primitives)

#### 1.1 (Track 1 only) — Token scales
**Depends on:** none. **Files:** `resources/css/app.css`,
`tests/Feature/DesignTokensTest.php`. Declare tokens: type scale (12, 14, 16 body, 20, 28),
spacing (4, 8, 12, 16, 24, 32, 48), radius (`--radius-sm: 6`, `--radius-md: 10`),
`--shadow-overlay`. Update `DesignTokensTest` to assert presence. **Do not replace the
palette** (deferred-owner, Track 2 per `frontend-fidelity-2026-10-13.md` §6). **Do not
delete the enamel/lattice/torn-chip tokens** without `grep -rn "enamel\|lattice\|torn-chip"
resources/js resources/css` returning zero consumers. **Do not delete mood-tier or
grade-fill tokens** (`MoodPill`, `GradeBadge` still consume them). **Acceptance:**
`DesignTokensTest` green; the tripwire count `toHaveCount(59)` is re-measured and the
new value pinned in the same commit. **Commit:**
`feat(tokens): declare the type, spacing, radius and overlay-shadow scales`.

#### 1.2 — App shell (48px bar + stripe, no version string)
**Depends on:** 1.1. **Files:** `resources/js/layouts/AppLayout.vue`,
`resources/js/layouts/CareerLayout.vue`, `components/app/NavGlyph.vue` (if using one).
Remove the blue title bar and version string. Header = 48px: product name left,
collapse control right, 2px accent rule bottom. `PageHeader` (from 1.3) receives the h1
later; this slice keeps the h1 out of `<header>`. Constrain `<main>` to
`max-w-6xl mx-auto px-4 md:px-8`. Active nav item gets `aria-current="page"` + 3px left
accent bar. `scroll-padding-top/bottom` equal to the fixed bar heights.
**Test:** new `tests/browser/shell.spec.ts` — `getByRole('banner')` height === 48;
stripe height === 2; h1 lives in `<main>`; no `v2.0.0` in `<header>`. **Commit:**
`refactor(shell): 48px bar with stripe; h1 on the page background`.

#### 1.3 — `PageHeader.vue`
**Depends on:** 1.2. **Files:** new `resources/js/components/ui/PageHeader.vue`,
new `tests/browser/page-header.spec.ts`. Props `{ eyebrow?, title, description?, actions? }`.
Renders one `h1` at 28px/semibold with `text-ink-strong`; eyebrow 12px uppercase muted;
one-line description max-w-prose; `#actions` slot right-aligned. **Pilot:** adopt on
`Dashboard.vue`, `Runs/Index.vue`, `Skills/Index.vue`, `Veterans/Index.vue`,
`Database/Index.vue` in the same commit — the pilot is the acceptance, not a follow-up.
**Acceptance:** exactly one `h1` per page; h1 on page background; screenshot at 390 and
1280 (or "captured, not inspected" if viewer unavailable — do not claim inspected).
**Commit:** `feat(components): PageHeader primitive and its five pilot screens`.

#### 1.4 — `Card.vue` / `Section.vue`
**Depends on:** 1.1, 1.3. **Files:** new `resources/js/components/ui/Card.vue`,
new `resources/js/components/ui/Section.vue`. Card: `bg-raised`, `border border-rule`,
`rounded-md`, `p-4` or `p-5`, no shadow. Section: h2 at `text-lg font-semibold`, optional
description, `mt-8` gap. **Forbid nested Card inside Card** by lint rule (see 1.12).
**Commit:** `feat(components): Card and Section primitives`.

#### 1.5 — `DataTable.vue` / `Pagination.vue`
**Depends on:** 1.1, 1.3. **Files:** new `resources/js/components/ui/DataTable.vue`,
new `resources/js/components/ui/Pagination.vue`, `tests/browser/data-table.spec.ts`.
Real `<table>` with `<th scope>`, caption prop (visually hidden allowed), column defs
(`key`, `label`, `align`, `sortable`, `mono`), rows prop, `rowHref`, empty slot. Wrapper
`role="region" tabindex="0" :aria-label` with `overflow-x-auto`. Sortable `<th>` uses
`aria-sort` + button. Missing value renders `–` with `sr-only` "Not recorded".
Pagination: `<nav aria-label="Pagination">`, `aria-current="page"`, "Page X of Y",
"Showing A–B of N". **Acceptance:** pilot on `Database/Races.vue` in the same commit.
**Commit:** `feat(components): DataTable and Pagination primitives, piloted on Races`.

#### 1.6 — `StatusBadge.vue` (exception-only)
**Depends on:** 1.1. **Files:** new `resources/js/components/ui/StatusBadge.vue`,
`tests/browser/status-badge.spec.ts`. Props `{ state: 'estimated'|'unknown'|'unconfirmed', label }`.
No `confirmed` case. Renders a small pill, colour-plus-word. A row with `confirmed`
provenance renders no badge. Replace `MoodPill` ad-hoc colours, the Review
"No match" pill, and the Timeline "Updated" outline. **Commit:**
`feat(components): StatusBadge marks the exception only, never Confirmed`.

#### 1.7 — `ProvenanceMark` (rewrite of `ProvenanceBadge.vue`)
**Depends on:** 1.6. **Files:** rewrite `resources/js/components/ProvenanceBadge.vue`.
Render **nothing** for `confirmed`. Render a small badge for `calculated`, `estimated`,
`unknown`. `title` renders as sr-only text + visible popover on focus/hover (WCAG 1.4.13).
**Delete every `state="confirmed"` usage at call sites.** Per `DESIGN.md` §5's 2026-10-09
per-surface-class ruling: `ProvenanceState` stays one union; Database reference views
keep their own reading. **Acceptance:** grep `state="confirmed"` in `resources/js` is
zero. **Commit:** `refactor(components): ProvenanceMark renders the exception only`.

#### 1.8 — `FormField`, `TextInput`, `Select`, `Textarea`, `Checkbox`, `SegmentedControl`
**Depends on:** 1.1. **Files:** new `resources/js/components/ui/FormField.vue`,
`TextInput.vue`, `Select.vue`, `Textarea.vue`, `Checkbox.vue`, `SegmentedControl.vue`.
**Delete or wrap** `resources/js/components/Form/BaseSelect.vue` (its `aria-required`
work from `c2de34b` is portable). FormField owns the label `for`, hint, error
(`aria-describedby`, `role="alert"`), and "Optional" suffix. Required is default and
unmarked; only optional fields carry "(optional)". Controls 44px tall, 14/16px text,
radius 6, 1px border, shared focus ring. SegmentedControl: real radio inputs inside
`fieldset` + `legend`, selection by fill + check glyph (WCAG 1.4.1). Replace
`Owned/Rented`, `Exact/Band`, `Newest first/Oldest first`. **Pilot** one page per
commit starting with `Runs/Create.vue`. **Commit (per page):**
`feat(components): FormField family and SegmentedControl, piloted on <page>`.

#### 1.9 — `Combobox.vue`
**Depends on:** 1.8. **Files:** new `resources/js/components/ui/Combobox.vue`,
delete or wrap `resources/js/components/TraineeCombobox.vue`. Props `{ options, modelValue, label }`.
Implements `role="combobox"`, `aria-activedescendant`, arrow/Enter/Escape. `aria-live` for
result count. **Used on:** New Career, Import, Snapshot, Target skill picker.
**Commit:** `feat(components): Combobox primitive replaces TraineeCombobox`.

#### 1.10 (StickyFooter only) — `StickyFooter.vue`
**Depends on:** 1.8. **Files:** new `resources/js/components/ui/StickyFooter.vue`.
Slots for `Back` (left, outline) and primary (right). `position: sticky; bottom: 0`,
1px top border, page container matching bottom padding. `scroll-padding-bottom` on
`html` prevents obscured focus. **Do not build Stepper** (deferred-owner KI-103).
**Commit:** `feat(components): StickyFooter primitive`.

#### 1.11 — `AbsenceNote.vue`
**Depends on:** 1.8. **Files:** new `resources/js/components/ui/AbsenceNote.vue`,
reduce `resources/js/components/AbsenceValue.vue`. AbsenceNote renders one line:
"Not recorded: reward, running style, skill points." Takes `labels: string[]`.
AbsenceValue is kept only for Energy on the Cockpit. En-dash cell behavior added to
DataTable (already in 1.5). **Commit:**
`feat(components): AbsenceNote groups one absence sentence per region`.

#### 1.12 — Guard tests
**Depends on:** 1.1–1.11. **Files:** new `tests/Feature/UiHygieneTest.php`,
new `tests/browser/ui-hygiene.spec.ts`, new `tools/ui-lint.mjs`, package.json script
`lint:ui`. Pest scans `resources/js/**/*.vue` and `app/Http/Controllers/**` for
`ADR-\d`, `\bD-\d{2,3}\b`, `\bG-\d`, `KI-\d`, `§\d`, `docs/`, backticks in template
text, `.php`/`.json` paths, and visible `N/A`. Fails `<details>` whose summary is only
"N/A". Lint fails Card-in-Card and new arbitrary Tailwind values. Playwright: every
route has exactly one h1, no h2 text equal to h1, no version string in header, no
element with legacy brand-blue background. Wire `npm run lint:ui` into hand-off.
**Note:** the N/A scan fails on **visible** `N/A` only, not on `sr-only` or `title`.
**Commit:** `test(guards): the agent contract, as code`.

### Phase 2 — copy and noise purge (limit 3 per batch)

#### 2.1 — Developer text removal
**Depends on:** 1.12. **Files:** every controller composing title/reason strings,
`config/reference.php`, `config/scenarios.php` display strings, Vue pages. Run the 1.12
scan and fix every hit. **Includes** `RaceStripBuilder.php:63` (`KI-17` inside the
title) and `RaceFacts.php:64` (backtick-`ADR-0016` inside the reason) — both named in
`SCREEN_SPEC.md` §8 as enforcement gaps; this slice closes them. Replace with the
reason's own sentence (`"The turn was not recorded against this race"`,
`"Win probability is not held by this tool"`). **Acceptance:** `grep -rn "KI-\|ADR-" app/Services/Career/`
returns zero user-visible strings; `ui-hygiene.spec.ts` green. **Commit:**
`chore(copy): classify and remove developer identifiers from rendered copy`.

#### 2.2 — Data sources page
**Depends on:** 2.1. **Files:** new `resources/js/pages/Database/Sources.vue`, new
controller method `DatabaseController::sources`, `routes/web.php` (one GET), footer
link in `AppLayout.vue`. Lists each dataset's source URL, last-fetched date, ruleset
note, read from `config/reference.php` and `data_sources`. Move every inline provenance
paragraph (Events, Shop Items, Sparks, Scenarios, trainee pages) into this page or a
per-page disclosure. **Commit:** `feat(database): one Data sources page for every provenance`.

#### 2.3 — Absence policy application
**Depends on:** 2.2. **Files:** every `Career/*`, `Database/*`, `Catalog/*` page.
Delete per-field `AbsenceValue` except Energy on the Cockpit. In tables, use the
DataTable dash + sr-only. In detail views, one AbsenceNote per group. Delete repeated
"why this is empty" sentences. Keep one page-level "About this data" disclosure per
screen where an explanation matters. **Commit:**
`refactor(pages): apply the one-absence-per-group policy`.

#### 2.4 — Terminology
**Depends on:** 2.3. **Files:** `resources/js/layouts/AppLayout.vue` nav items,
`Runs/*`, `lang/en/uma.php`, button labels. "Training runs"/"run"/"New run" →
"Career"/"Careers"/"New career". "Support Deck Select" → "Support deck". Same label in
nav, h1 and button. Update Playwright locators. **Note:** identifier renames are
**out of scope** — `training_runs`, `runs.*`, controllers, models, tests keep the word
they carry (`DESIGN.md` §6 2026-10-17 amendment). **Commit:**
`chore(copy): Training runs to Careers in user-facing copy`.

#### 2.5 — Repeated sentences
**Depends on:** 2.4. **Files:** `LegacySelect.vue`, `Legacy/Builder.vue`, `RaceCard.vue`,
`Review/Index.vue`, `Preflight.vue`, `SetupLayout.vue`. Show each helper sentence once
per field group. Rank letters get one line at the top of the group. Delete duplicate
intro paragraphs on each wizard step. Delete the "Record only…" banner from non-Legacy-builder
screens (replaced by a help popover on the builder). Delete the distance-band note from
each race card; show once above the list. Delete self-referential "Back to <this page>"
links on every wizard step (see ledger §8 finding 1: `ScenarioSelect.vue:123-128`).
**Commit:** `chore(copy): show every helper sentence once`.

### Phase 3 — setup wizard (limit 3 per batch)

#### 3.1 — Wizard shell
**Depends on:** Phase 1 + 2. **Files:** `resources/js/layouts/SetupLayout.vue`. Compose
`AppLayout` + `ChoicesSoFar` (3.2) + PageHeader slot + StickyFooter slot. **Stepper
stays in its current form until owner rules.** Focus management: on step load focus h1
(`tabindex="-1"`) and set `document.title`. **Commit:**
`refactor(wizard): SetupLayout composes PageHeader and StickyFooter`.

#### 3.2 — ChoicesSoFar
**Depends on:** 3.1. **Files:** new `resources/js/components/career/ChoicesSoFar.vue`,
`SetupDraft::summary()`. Compact bar: "Scenario: Unity Cup · Trainee: Rice Shower ·
Target: Speed focus", "Not chosen" muted for gaps. Replace every "Scenario: ▶ N/A"
line. **Commit:** `feat(wizard): the choices-so-far strip`.

#### 3.3 — Step 1: Scenario
**Depends on:** 3.2. **Files:** `resources/js/pages/Career/ScenarioSelect.vue`. Cards →
radiogroup of real radio inputs. Each: name, one-sentence summary, three chips (stat cap,
team system, shop). "Show details" disclosure replaces the bordered empty-looking box.
One "Save and continue" in the footer. Selecting does not navigate. Add "Compare
scenarios" link to `/database/scenarios`. **Commit:**
`feat(wizard): step 1 uses radio cards and a single Continue`.

#### 3.4 — Step 2: Trainee
**Depends on:** 3.3. **Files:** `resources/js/pages/Career/TraineeSelect.vue`,
`Career/TraineeProfile.vue`, `TraineeSelectController`. 25 cards → DataTable: portrait,
name (`lang="ja"` second line), best three aptitudes as chips, form count, radio select
column. Sticky filter bar (Surface, Distance, Style, Search, Sort); "Clear filters" on
the same row; pageSize 25, "Page X of Y". Costume-form select only in the profile view,
labelled. Profile: `<dl>` with left-aligned values, aptitude grouped Surface/Distance/Style
with grade chips, skills as one table with a Source column, delete Build analysis.
Sticky "Select this trainee" in the footer. **Commit:**
`feat(wizard): step 2 is a filterable DataTable, not 25 cards`.

#### 3.5 — Step 3: Target
**Depends on:** 3.4. **Files:** `resources/js/pages/Career/BuildTarget.vue`. "Purpose"
in a fieldset with SegmentedControl for four purposes. Three-line stat rows → five-row
grid: Stat | Target | Ceiling (optional) | Scenario cap. "Not available" in the
Ceiling cell for Wit with a one-phrase reason. Remove progress bars until a target
value exists. Skill priorities use Combobox over the skills catalogue; each row has
Move up/down/Remove named with the skill. Hide the summary until at least one value
is set. **Commit:** `feat(wizard): step 3 is a stat table, not per-stat blocks`.

#### 3.6 — Step 4: Legacy
**Depends on:** 3.5. **Files:** `resources/js/pages/Career/LegacySelect.vue`,
`resources/js/components/legacy/AncestryNode.vue`. Desktop tree layout (trainee left,
parents middle, grandparents right; CSS grid with connector lines; stack on mobile).
Nested `<ul>` with "Parent A", "Grandparents of Parent A" headings so structure exists
without lines. Rank = Select of letters + narrow stars input. "Add spark" = text button
with plus icon, named per person. "Not chosen" once per parent until chosen. Delete four
stacked "Rank not recorded / No Sparks recorded / Spark chance N/A" lines. Affinity →
"Pair" fieldset after Parent B. Every control name includes its person. Fix
`AncestryNode.vue:102,118` absence inconsistency per `A-98`. **Commit:**
`feat(wizard): step 4 draws the tree, one helper per group`.

#### 3.7 — Step 5: Deck
**Depends on:** 3.6. **Files:** `resources/js/pages/Career/DeckSelect.vue`,
`resources/js/components/support/{SupportSlot,CardPicker}.vue`. Six slots in 3×2 grid
(one column mobile). Each: card thumb, name, type chip, Owned/Rented SegmentedControl.
Empty slot = "Empty" + "Choose card" button. Choose opens `<dialog>` with searchable
list (type, rarity, name filters); each row: art, name, type icon + text, one "Equip".
Closing returns focus to the slot button. Hide "Deck analysis" until ≥1 card.
Support-type legend: icon + text. **Commit:**
`feat(wizard): step 5 uses per-slot pickers and segmented ownership`.

#### 3.8 — Step 6: Preflight
**Depends on:** 3.7. **Files:** `resources/js/pages/Career/Preflight.vue`,
`StartCareerRequest`. Retitle "Review and start". One checklist row per step: status
icon, label, summary, "Edit" link. Delete Warnings section and seven Edit buttons.
Heading with count ("2 steps need attention") with `role="status"`. Primary "Start
career". If scenario or trainee is missing, disable it and show reason; focus the first
missing row on attempt. On success, redirect to Cockpit with `role="status"` flash
"Career started for {name}". **Commit:**
`feat(wizard): preflight is a checklist with an honest start`.

### Phase 4 — cockpit and career screens (limit 3 per batch)

#### 4.1 — CareerHeader
**Depends on:** 3.8. **Files:** new `resources/js/components/career/CareerHeader.vue`,
`CareerLayout.vue`. Header strip: trainee, scenario, status badge, turn/position.
Below it, tabs as real `<nav>` of links: Cockpit, Training, Races, Skills, Events,
Legacy, Deck, Timeline, Result. Disable tabs with unavailable reason using
`<span role="link" aria-disabled="true" title="…">` — **not** `<Link>` — per
`DESIGN.md` §5 2026-10-17. Remove per-page Cockpit/Dashboard link pairs.
**Test:** click disabled tab does not navigate. **Commit:**
`feat(components): CareerHeader — persistent tab strip; unreachable tabs are non-navigable`.

#### 4.2 — Cockpit layout
**Depends on:** 4.1. **Files:** `resources/js/pages/Career/Cockpit.vue`,
new `components/career/{StatSummary,ActionGrid,AdvisorRail}.vue`. Sticky summary at
top: energy, mood, fans, SP, turn, and the recommendation as the single primary button.
Two columns on desktop: main + sticky Advisor rail (`min-w-[20rem]`, top offset = header).
One column on mobile with the recommendation first. Scenario panels become
`<details>` accordions with one-line summary. Delete green striped capsule headers;
section titles use `Section`. Only one filled accent button on screen: the recommendation.
**Commit:** `feat(cockpit): sticky summary, advisor rail, accordioned scenarios`.

#### 4.3 — Training decision
**Depends on:** 4.2. **Files:** `Career/TrainingDetail.vue`,
new `components/career/TrainingCard.vue`. Training cards are outline buttons; the
advisor's recommendation is the one primary. Move source/provenance text into a per-card
"Sources" disclosure. Delete repeated "Not ranked" sentence. Record form becomes step
two ("What happened"), fields empty, last-recorded values as helper text (never
placeholders). Energy: fieldset with legend "Energy", vertical radios: Exact, Band,
Not recorded. **Commit:**
`feat(training): recommendation is primary, cards are secondary`.

#### 4.4 — Race decision and planner
**Depends on:** 4.2. **Files:** `Career/RaceDecision.vue`, `Career/RacePlanner.vue`,
new `components/career/RaceTable.vue`. Card stacks → DataTable: Race, Grade, Distance,
Surface, Fan gate, Status; row opens a details dialog. One AbsenceNote above the table.
Mandatory races: neutral card, delete orange alert border. Fix "NEXT" to point at the
next obligation not already past; add a test. Calendar vs off-calendar: real tab list.
Planner: compare checkbox column + sticky "N selected, Compare" tray. Remove empty
bordered boxes and "Held figures" row. Group placeholder races into one collapsed
"Races with limited data (N)" disclosure. **Commit:**
`feat(races): race decision is a DataTable with a compare tray`.

#### 4.5 — Skills planner
**Depends on:** 4.2. **Files:** `Career/SkillsPlanner.vue`. Collapse four info boxes
and empty sections into one summary line. Merge Learned cards and Acquired table into
one DataTable: Skill, Base SP, Status, Turn acquired, Conditions (expandable row).
Accessible names include the skill. "Save acquisition status" visibly enabled or states
why disabled. **Commit:** `feat(skills): planner collapses info and merges tables`.

#### 4.6 — Event, Inheritance, Timeline, Result, Save Veteran
**Depends on:** 4.2. **Files:** corresponding pages. **Event:** form first, history
below, one empty state, fix placeholder encoding (0.7 handles the source). **Inheritance:**
keep empty state, delete decision-ID reference. **Timeline:** hide filter card unless
>1 kind; "Updated" neutral badge; one label for TURN/Turn/Turn recorded. **Result and
Save Veteran:** when not applicable, tab disabled per 4.1; Result keeps one primary + one
outline. **Commit:** `refactor(career): five screens share the layout primitives`.

### Phase 5 — reference tables (limit 3 per batch)

#### 5.1 — Races
**Depends on:** 4.4 + `DataTable`. **Files:** `Database/Races.vue`,
`DatabaseController::races`. Filters (year, tier, surface) as sticky bar, sortable
headers, server-side sort whitelist, "Showing A–B of N". Drop all-N/A Gate column.
**Commit:** `feat(database): races become a filterable, sortable table`.

#### 5.2 — Skills search and detail
**Depends on:** 1.5. **Files:** `Skills/Index.vue`, `Skills/Show.vue`. Sticky filter
bar, "Page X of Y", footnote above the list, dash for missing SP. Detail: condition in
a wrapped mono block labelled "Raw engine data", effect chips in words, one provenance
disclosure. **Commit:** `feat(skills): search polish — page count, sticky filter, footnote`.

#### 5.3 — Support cards
**Depends on:** 1.5. **Files:** `SupportCards/Index.vue`, `SupportCards/Show.vue`.
Index → DataTable: Card (art + name), Rarity (one chip, `aria-label` "SSR, 3 star"),
Type, Friendship %, Training effectiveness %, Availability. Remove mono stat string.
Detail: keep effect chips. **Commit:**
`feat(support): support cards list is a table, not a mono string`.

#### 5.4 — Trainees catalog
**Depends on:** 1.5. **Files:** `Catalog/Index.vue`, `Catalog/Show.vue`. Collapse
costume forms under each trainee (`<details>`). Debut date once. Detail: group
aptitudes; collapse empty runs/aliases to one line; source details into one disclosure;
`lang="ja"` on Japanese strings. Confirm fixture-row exclusion applies (from 0.8/`74cabb2`);
if so, extend; if not, note scope. **Commit:**
`feat(catalog): collapse forms, one disclosure for provenance`.

#### 5.5 — Scenarios, Shop, Sparks, Events, hub
**Depends on:** 1.5. **Files:** `Database/{Scenarios,ShopItems,Sparks,Events,Database}.vue`.
Scenarios: two tables ("Stat caps by scenario", "Panels by scenario"), ✓/— cells.
Shop/Sparks/Events: DataTable with left-aligned text, right-aligned numbers, server tags
as chips; confidence column only for non-confirmed rows. Hub: cards with dataset name,
one-line description, record count, last-updated, chevron. **Commit:**
`feat(database): scenarios become comparison tables; hub leads with the dataset name`.

### Phase 6 — forms, lists, library (limit 3 per batch)

#### 6.1 — New career entry and forms
**Depends on:** Phase 1 `FormField` + `Combobox`. **Files:** `Runs/Create.vue`,
`Runs/Import.vue`, `Career/Snapshot/{Entry,Setup,Review}.vue`, new `NewCareer.vue`.
Single `/career/new` page with three choices (start fresh, from snapshot, import).
Nav "New career" points here; remove "New run". Rebuild New career, Import and Snapshot
on FormField/Combobox, "Optional details" disclosures. Import: file input as button +
filename; visible sample CSV header. Snapshot: one "Career setup" card; eight "I don't
know this value" checkboxes → per-row "Not shown" toggle named "Speed: not shown".
**Commit:** `feat(career): one New career entry, three paths`.

#### 6.2 — Review queue
**Depends on:** 1.5. **Files:** `Review/Index.vue`. DataTable: Source name, Japanese
name, Suggested match, Verdict, Resolve. Sort suggestions first. Resolve disabled until
verdict chosen, with reason. Show date once. Resolve button names include the candidate.
**Commit:** `feat(review): the queue is a table with sorted suggestions`.

#### 6.3 — Veterans and Legacy Lab merge
**Depends on:** 6.2. **Files:** `Veterans/*.vue`, `Legacy/*.vue`, `routes/web.php`,
`AppLayout.vue` nav. One "Veterans" area with tabs: Library, Legacy, Compare. Remove
Legacy Lab nav; redirect old routes. Library is a DataTable: Name (disambiguated label),
Scenario, Result, Tags, Actions. Newest/Oldest buttons → Select. Compare: disable until
two selected; one empty state; delete "NOT IN THIS BUILD". **Commit:**
`refactor(veterans): merge Legacy Lab and Veterans into one tabbed area`.

#### 6.4 — Dashboard
**Depends on:** 6.3. **Files:** `Dashboard.vue`, `DashboardController`. Active career
card with primary Resume; quick actions as outline buttons; Recent veterans as short
list. Hide "Recent builds" until it has data. If >1 active career, "N active careers"
with link. **Commit:** `feat(dashboard): active career, quick actions, recent veterans`.

### Phase 7 — functional slices (limit 3 per batch)

Per Document 2 §7.1–7.15. Each is small and touches its own surface. Files listed in
plan. Summary per slice:

| Slice | One-line build | Commit |
|---|---|---|
| 7.1 | `StatValue.vue` (`current`, `denominator`, `denominatorLabel`) used in `CareerStatePanel` and `EventDecision`; target ≠ ceiling, not interchanged | `feat(stats): label the denominator as target vs ceiling` |
| 7.2 | `SkillSpendCoverage` returns `'none'|'covered'|'short'`; empty state links to Target step; never "0 SP" for `none` | `feat(skills): distinguish no-priority from covered` |
| 7.3 | Deck links point at `runs.deck` for current run; add "Deck" tab; "Back to Cockpit" return; never `/career/setup/deck` from a saved run | `fix(career): deck entry links the current run, not setup` |
| 7.4 | Same `energy_state` values in create and update; omit-preserves; show band in Cockpit and Timeline | `feat(turns): energy band path accepts store/update/omit` |
| 7.5 | `IdentityForm.vue`; rarity, potential, five growth rates; partial updates preserve status/scenario/target/period | `feat(cockpit): identity editor writes validated identity fields` |
| 7.6 | `UnityCupPanel.vue` view/edit toggle covering team name, motto, league placement, preseason wins, Unity Trainings, Spirit Bursts, Extreme Bursts; hidden for other scenarios | `feat(unity-cup): state editor for the recorded counters` |
| 7.7 | Use the collapsed "Races with limited data" disclosure from 4.4; required races stay visible | `refactor(planner): collapse placeholder races without dropping rows` |
| 7.8 | Label "Correct turn N" vs "Record next turn"; preserve on validation failure; disable while pending | `fix(cockpit): correction targeting is explicit` |
| 7.9 | Count active careers; notice + link when >1 | `feat(dashboard): count active careers when more than one exists` |
| 7.10 | `TrackblazerPanel.vue` editable grade-point period, current grade points, shop coins via validated paths; catalogue stays read-only | `feat(trackblazer): state editor for GP, coins and period` |
| 7.11 | `GrandConcertPanel.vue` strip exposes schema-backed fields only; unknown ≠ zero; scenario isolation test | `feat(grand-concert): expose only schema-backed state` |
| 7.12 | `ScenarioPanel.vue` + four panel components share header, summary, edit toggle, save feedback, empty state; labels server-supplied only | `refactor(scenario): one shell, four renderers` |
| 7.13 | Result → Save Veteran: "View veteran" when already saved; idempotent save; inline conflict errors | `feat(result): save veteran is idempotent and honest about state` |
| 7.14 | Veterans/Legacy Compare rows aligned by label; dash for missing; empty and one-record states with next action | `refactor(compare): align by label, dash for missing` |
| 7.15 | "Start career" writes `umamusume_id` to draft, opens step 1 (scenario); no run before Preflight | `feat(catalog): start career hands off to the wizard` |

### Phase 8 — a11y, responsive, e2e (limit 5 per batch)

Per Document 2 §8.1–8.8.

| Slice | One-line build | Commit |
|---|---|---|
| 8.1 | Unique accessible names beginning with visible text for every repeated control; `lang="ja"` on every Japanese string; glyph-only markers → text or `aria-label`; axe + accessible-name uniqueness test per page | `fix(a11y): unique accessible names and lang tags` |
| 8.2 | Replace `role="bar"` in Inertia NProgress with a labelled `role="progressbar"` or polite status; respect reduced motion; regression assertion on DOM | `fix(a11y): valid progressbar semantics` |
| 8.3 | Standardize busy/disabled submit, field errors via `aria-describedby`, form-level `role="alert"` with focus, empty states, success messages | `refactor(forms): consistent loading/error/success states` |
| 8.4 | Every primary route at 320/390/768/1280: no horizontal overflow, all primary actions visible, 44px targets, focus not hidden by sticky bars | `test(responsive): assert every primary route` |
| 8.5 | `journey-new-career.spec.ts` (scenario → trainee → target → legacy → deck → review → start; edit identity; record turn; open deck; plan race + skill; finish; save veteran; open it) and `scenario-isolation.spec.ts` (4 scenarios, each shows only its own fields, partial updates preserve) | `test(journey): the full career loop` |
| 8.6 | Final gates as one run: `php artisan test --compact`, `pint --test <paths>`, `phpstan analyse`, `npm run typecheck`, `npm run lint:ui`, `composer lore`, `composer lore-code`, `npm run build`, session-private `npm run test:browser`, `git diff --check`. Report pre-existing failures with their commit citations | `chore(gates): final quality gate run` |
| 8.7 | `tests/browser/accessibility.spec.ts` opens every `<details>` before each axe scan | `test(a11y): open every disclosure before axe` |
| 8.8 | Route coverage map: a table of every live route and its axe scan; a test asserting every route has at least one | `test(a11y): route coverage map` |

---

## §F — Dispatch prompt

Paste this into Qoder. It cites the file; it does not repeat the file.

```
Read .scratch-uma/dispatch-2026-10-17/implementation-backlog.md in full, then read
`docs/proposals/execution-ledger-2026-10-17.md` §8 and §9 for the current state.

Execute exactly one slice: the first slice in the backlog's §E, in phase order, that is
not marked BLOCKED, not marked DEFERRED-OWNER, not already satisfied by §B, and whose
premise still holds at HEAD.

Steps:
1. Premise check (§A of the backlog). Record HEAD, tree state, `origin/master`, per-file
   dirty state on the slice's target paths, and any live peer. Do not proceed until done.
2. Verify §B for the slice's plan number. If already-satisfied, record the fact, mark
   `already-satisfied` in the ledger, and dispatch the next eligible slice.
3. Read the plan-of-record section for the slice, and its `SCREEN_SPEC.md` dated notes.
4. Implement only what the slice's Files/Build/Acceptance prescribe.
5. Run the test gates the slice names, plus the hand-off sequence for the change class.
   Quote each command's tail. A gate not run is reported as not run.
6. If the slice is red because `public/build` is stale, report red-as-stale and stop;
   do not weaken the assertion, do not run `npm run build` with a peer live.
7. One commit, path-scoped, with the message the slice names. `git commit -- <paths>`.
8. Append the slice's row to the ledger. Include blockers, deferred-owner items, and any
   out-of-scope findings — code not implemented, and per backlog §A.

Do not batch. Do not touch a slice outside the one dispatched. Do not edit a held file
(`KNOWN-ISSUES.md`, `config/scenarios.php`, `AppLayout.vue`, `NextDecision.php`,
`SCREEN_SPEC.md`, `TrainingRunController.php`, `AGENTS.md`, the peer's eight dirty files,
`.kilo/worktrees/cherry-pet`, `docs/audits/`) except where the slice names it.

If the first eligible slice is blocked, skip it and state the skip. Do not substitute.
```

---

## Deliverable 2 — the one-paragraph version

If you prefer a single dispatch that also names the concrete first three slices, use this instead of the above block:

> Read .scratch-uma/dispatch-2026-10-17/implementation-backlog.md and `docs/proposals/execution-ledger-2026-10-17.md` in full. Then: premise-check per backlog §A, skip anything in §B already satisfied, skip anything in §C or §D, and execute the first three Phase 0 slices in §E (limit 3 per batch). Recommended order for this dispatch: **0.11** (`stepAbsent`), **0.12** (`<dt>/<dd>` without `<dl>`), **0.7** (encoding) — all three are Pest-gated or component-gated and add no red debt. Each is one commit, path-scoped, per its named message. Quote each test tail; append the ledger rows; state blockers; do not touch a held file.

---

**Two things to check before you send it:**

1. **0.11, 0.12, 0.7 are all build-free** — 0.11 is a grep acceptance, 0.12 is a template change with a Playwright case (the case reads the source SFC, not `public/build`, if you follow the pattern the agent already used in `base-select.spec.ts`), 0.7 is source-side and its test case reads the source files. None of the three touches `public/build`. That is deliberate: it means the dispatch can land clean while the peer still holds the eight files, and it leaves `0.4` and `0.13` for the moment the tree goes quiet.

2. **The dispatch is now one slice or three, not the roadmap.** The suggestion you received is correct and applied: the backlog file is the whole instruction; the prompt is the sentence that says "read it, then do the next eligible thing."