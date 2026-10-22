# Frontend Architecture Plan

**Scope.** This document is in two parts. **Part I** reports what the wireframe pass found: the parity between the running Vue frontend and the static High-Fidelity Wireframe set (`docs/design-research/frontend-v2-wireframes`, "the HFW"), the mock catalogue that was built to feed it, and the divergences that still need a decision. **Part II** is a strategic, step-by-step plan to replace the application's *frontend architecture* — not its framework — with the design-system architecture the HFW proved out, inside the existing Inertia + Vue 3 stack.

**Last verified:** 2026-10-22, against `master` at `c79da9f5`. Every count in this document was re-derived from the tree during the pass that produced it: 97 components and 57 page files under `resources/js/`, 49 HFW pages, 50 `SCREEN_SPEC.md` registry rows, 77 browser specs, 200 `tests/Feature` files. Where a figure is quoted from an earlier session rather than re-measured, the source is named.

**Status vocabulary.** This is a design plan, not a roadmap. Proposals carry `proposed` (nothing is built yet), `verified` (established in the repository), or `open question` (needs an owner ruling). No slice status (`COMPLETE` / `BLOCKED BY EVIDENCE`) is used, and no feature level (`Implemented` / `Verified`) is claimed for anything the plan proposes.

## Sources

One line each: path — the claim it supports.

- `docs/design-research/frontend-v2-wireframes/` — the 49-page static set, its `assets/shell.css`, and its four checks (`framework-check.mjs`, `link-check.mjs`, `render-check.mjs`, `data-check.mjs`); the visual system the plan ports.
- `docs/design-research/frontend-v2-wireframes/assets/data/` — the mock catalogue and `README.md`, the schema and provenance of the 67/106/559/35/1910 rows.
- `docs/design-research/cockpit-wireframes-2026-10-21/` — the companion set that carries the four scenario panels at full density.
- `DESIGN.md` §2 (color, typography, spacing), §3 (component inventory), §9 (where tokens live), §10 (what is enforced vs claimed), §11 (open questions) — the binding visual system the plan must not contradict.
- `docs/research-scratch/DESIGN-CORPUS.md` — the measured ramps, contrast math and the rulings (D-101, G-19) behind the token layer.
- `docs/adr/0006-design-authority-and-theme-default.md` — who owns the visual system; the authority an architecture change must satisfy.
- `docs/adr/0019-semver-label-tag-and-release-branch.md` — how a phase exit is labelled.
- `AGENTS.md` §2 (precedence), §7 (conventions), §9 (testing and gates), §11 (change-safety), §13 (UI rules) — the operational contract the plan runs under.
- `SCREEN_SPEC.md` §8 — the 50-screen registry the parity count is measured against.
- `docs/research-scratch/SKILLS-MECHANICS.md` G-SK-20 — the ruling that keeps a skill description absent.

---

## Part I — Findings and observations

### 1. Two artifacts, one visual system, different enforcement

The repository now holds **two renderings of the same product**:

| | Running app | HFW |
|---|---|---|
| Framework | Inertia + Vue 3 + TypeScript + Vite | static HTML, one shared `shell.css` |
| Pages | 57 (incl. aliases) | 49 |
| Components | 97 across 13 folders | ~120 named CSS primitives |
| Layouts | 3 (`AppLayout`, `CareerLayout`, `SetupLayout`) | one shell |
| Styling | Tailwind v4 `@theme static`, 93 tokens, `app.css` 630 lines | 65 tokens, `shell.css` 1,126 lines, named primitives |
| Checks | `ui-lint.mjs` (5 KB), `UiHygieneTest`, `DesignTokensTest`, `LoreGateParityTest`, 77 browser specs | `framework-check.mjs` + `link-check.mjs` + `render-check.mjs` + `data-check.mjs` |

The two have **converged on the token layer** (both use a measured `@theme`/`:root` token set with no `dark:` utilities) and **diverged on the component layer**: the app spells components with Tailwind utility classes (37 arbitrary-value lines remain as debt), while the HFW spells them with named semantic primitives whose invariants are enforced by static scripts.

### 2. The parity matrix (97 components)

The pass compared every component in `resources/js/components/` against the HFW's rendered output, not against names. Matching names proved useless — the HFW rewrites all copy, so literal-text matching returned 0 of 97; the reliable signal was rendered signature (ARIA roles, class families, node counts).

**Near-copies (the bulk).** These are reproduced with the same information and hierarchy, rebuilt in named primitives rather than Tailwind classes: `RarityChip`, `AptitudeBadge`, `AptitudeGrid`, `MoodPill`, `SparkChip`, `ProvenanceMark`, `AbsenceValue`, `SubCell`, `StatSummary`, `CareerStatePanel`, `EnergyGauge`, `KpiCard`, `SkillRow`, `Pagination`, `DataTable`, `AlertRow`, `SourceNote`, `CandidateForm`, `AncestryNode`, `ArtworkSlot`, `SupportTypeMark`, `ActionGrid`, `GoalMark`, `ObjectiveRow`, and the scenario-panel family.

**Drawn but not captured by name (6).** These are present in the HFW under a different name or slot, and an inventory keyed on component names misses them:

| App component | HFW counterpart |
|---|---|
| `DeckAnalysis` | the **"Deck diagnostics"** card (`career-deck.html`): `Six slots filled · Duplicate card in the deck · Cards already at orange gauge · Cards carrying a friendship bonus` |
| `CareerSectionNav` | the **Career rail group** (`Cockpit · Training Races · Skills · Events · Inheritance · Deck · Timeline · Result`) on all four cockpit pages |
| `EnergyGauge` | the **ENERGY Low band** line in the Stats-and-state card |
| `CareerStatePanel` | the **"Stats and state"** card (`+0% 607 / 1325`, the no-target caption) |
| `StatSummary` / `SubCell` | the same card's stat rows and label/value pairs |
| `KpiCard` | the **KPI strip** (`FINALE Completed · 1st · TURN 75 · RACES 4 of 4 · SKILL POINTS 34`; 59 `.tile` nodes across 15 pages) |

**Absent (8).** Verified by rendered signature — zero `role="tab"`, zero `role="progressbar"`, zero `role="dialog"` — and classified against the source that builds them:

| Item | App does | HFW does | Category |
|---|---|---|---|
| `FormTabs` / `FormDetail` | `<nav aria-label="Costume forms">` of addressable links, `aria-current="page"`, `?form=<id>` | a flat **"Costumes"** table with no switcher | **Replacement** — and the one regression: a three-form trainee cannot switch forms |
| `RunRaceStrip` | the run's recorded races as a row strip | absorbed into `timeline.html` (`Races entered 4`, Kind and Year filters) | **Enhancement** — endorsed by `CockpitController:57` |
| `DeckAnalysis` | empty-first "Deck analysis" panel | **"Deck diagnostics"**, a filled check-based table | **Enhancement** |
| `ChoicesSoFar` | lowercase `·`-separated strip | the **preflight grid** (`TRAINEE / FORM / SCENARIO / DISTANCE / PARENTS / DECK / TARGET TOTAL`), which adds facts the strip never showed | **Enhancement** |
| `SuggestionHint` | `Suggested id: N, matched from X` | **`Best match` + `Confidence` columns** and a "How a match is decided" ladder | **Replacement** — the raw id is gone, the explanation is added |
| `ReferenceDrawer` | `role="dialog"` panel with a Close button | an **inline source column** on the three database pages | **Replacement** — a real simplification, no information lost |
| `VisitProgressBar` | native `<progress role="progressbar">` while a visit is in flight | nothing — a static page has no visits | **Out of scope by construction** |
| Flash banner | `role="status"` strip, 28 writers, 5 s auto-dismiss | `preferences.html` carries `role="status">All changes saved` in `.hrow-position`; the other writing pages carry nothing | **Dropped, but only because it was not promoted** — see §6 |

**Dead code in the app (5).** Referenced by nothing: `UraDuelPanel`, `UraMilestoneTracker`, `Checkbox`, `Combobox`, `SegmentedControl`. Two are URA panel modules the app no longer mounts; the HFW's cockpit page still points a reader at "the Happy Meek duel panel" in the companion set, which is the treatment for a panel the code has dropped.

### 3. Screen coverage

The HFW set's README claims *"49 screens: the 48 that match `SCREEN_SPEC.md`'s registry"*. The registry holds **50** screen rows; the set draws **47** of them. Three registry screens are undrawn, and three non-registry screens are absent:

| Screen | Route | Registry |
|---|---|---|
| `Runs/Create.vue` | `GET /training-runs/create` | SCR-RUN-002 |
| `Career/SaveVeteran.vue` | `GET /training-runs/{run}/veteran` | SCR-VET-003 |
| `Career/RacePlanner.vue` | `GET /training-runs/{run}/races/planner` | SCR-CAR-023 |
| `Career/Snapshot/Entry.vue` | `GET /career/snapshot` | — |
| `Career/Snapshot/Setup.vue` | `GET /career/snapshot/setup` | — |
| `Career/Snapshot/Review.vue` | `GET /career/snapshot/review` | — |

Reverse divergence: `review-detail.html` is a page the app does not have — the app renders the match detail inside `Review/Index.vue`. That is legitimate for a redesign, but it is why 49 pages cover 47 registry screens plus `help` plus one extra.

### 4. The mock catalogue (what was built to feed the set)

`assets/data/` holds the catalogue the app already keeps, shaped for a page to read, with no invented value:

| File | Holds | Rows |
|---|---|---|
| `umamusume.js` | the roster with forms, aptitudes, base stats, growth and breakthrough rows | 67 trainees, 106 forms |
| `support-cards.js` | cards with type, rarity, effect anchors and caps | 559 |
| `support-effects.js` | the effect dictionary (the join target) | 35 |
| `skills.js` | names, derived type, SP cost, class code, gate keys, engine conditions | 1,910 |

Three facts matter for the plan:

1. **Every figure is the app's own.** `build-data.php` reads the dev database joined with the committed GameTora export bodies, and refuses to write a dataset whose growth rows do not sum to 30, whose base rows do not sum to 450/425/400, or whose effect join does not resolve.
2. **A skill carries no description, and that is a ruling, not a gap.** G-SK-20 holds that no description column may be added until a client-string source exists; the export's English fails the terminology table 189 times. `description` is null on all 1,910 rows, and `sourceText` carries the publisher's English as quarantined reference.
3. **The dev database holds four rows the game does not** — "Form Counted 1…4" and twelve "[Sample Form]" cards written by a test. The app's live counter reads 71 trainees; the catalogue holds 67. The dataset's rule is a join, not a name filter, and the generator fails if a real row falls out.

### 5. The three-page content update

The trainee list, support-card list and skill set now carry the catalogue's real values instead of illustrative ones: `catalog.html` (six real cards with aptitudes and growth), `support-cards.html` (seven real Global SSRs, one per type, with the Pal card's `None` friendship bonus), `skills.html` (six real skills, the Type filter corrected to `Speed / Recovery / Passive / Unspecified`, the Rarity filter removed because the source names no tier), and the three detail pages aligned to them. The `catalog-detail` stats table gained the ★4 and ★5 columns (summing to 500 and 550); the `support-card-detail` ladder became `Effect / Stated at / Cap` because the source states anchors, not a per-break progression; the `skill-detail` readable-description block now reads **"No client string."**

### 6. The divergences that need a decision

1. **`FormTabs` is the only place the substitute is weaker than the original.** The app engineered the tabs as addressable, reload-surviving, `aria-current`-carrying links; the HFW's costume table has no switcher. A three-form trainee cannot switch forms in the set.
2. **The flash banner was not dropped so much as not promoted.** The app has 28 writers confirming a completed write, a `role="status"` live region with a 5 s auto-dismiss, and a sibling dismiss button (never inside the region — WCAG 4.1.3). `preferences.html` already carries the surface (`role="status">All changes saved`); the other writing pages do not.
3. **`review-detail.html` is ahead of the code.** The app renders that detail inline; the set gives it a page.
4. **The live "71 trainees" is wrong by four.** It counts the fixture rows. The HFW and the catalogue both say 67.
5. **The four cockpit pages delegate to a sibling set** (`cockpit-wireframes-2026-10-21/`), so the scenario panels are documented but not in the HFW's own check loop.

### 7. The enforcement gap

This is the finding the implementation plan exists to close:

| | App | HFW |
|---|---|---|
| Layout rules | `ui-lint.mjs`: no Card-in-Card, no arbitrary values (with a debt list) | `framework-check.mjs`: law rules with teeth, proven by mutation |
| Rendered rules | 77 browser specs, per-feature | `render-check.mjs`: geometry rules a static read cannot decide, all 49 pages |
| Data rules | none at the component level | `data-check.mjs`: counts, joins, sums, the classic-script load |
| Copy/identifier hygiene | `UiHygieneTest`, `LoreGateParityTest` | the annotation-key discipline |

The app has *stronger* verification overall (200 Feature tests, 77 browser specs), but its **visual-system rules live in a lint script that names debt instead of closing it**, while the HFW's rules are structured so that a violation fails the check. The plan below ports that discipline without weakening either side.

### 8. What the findings establish, and what they do not

**Established:** the app and the HFW share one token system and one roster of components; six components are drawn under other names; eight are absent and each has a defensible classification; three registry screens are undrawn; five components are dead; the mock catalogue is real data with a documented provenance and one ruled absence.

**Not established:** that the HFW's static-HTML architecture should *itself* become the app — a static wireframe performs no writes, no server round-trips, and no visit states, so "replace the frontend architecture" cannot mean "make the app static". It means: adopt the design-system architecture (tokens → named primitives → components → checked pages) the HFW proved out, inside the running Vue stack. Part II assumes exactly that reading, and states it as the goal's first line.

---

## Part II — Implementation plan

### Goal and non-goals

**Goal.** Replace the application's *frontend architecture* — the way components are spelled and enforced — with the HFW's proven design-system architecture, without changing the framework, the data layer, or any product behaviour a Trainer can observe.

**Non-goals.** No new framework (Vue 3 and Inertia stay). No new dependency beyond what is already installed (the check harness is plain Node plus the already-present Playwright). No change to any route, controller, model, migration, or API shape. No copy change. No re-litigation of a closed ruling (G-SK-20, D-101, G-19, ADR-0006).

### Principles

1. **ADR-0006 governs.** `DESIGN.md` owns the visual system; this plan is how a *proposed* `DESIGN.md` §3 (the component inventory) becomes the *enforced* one. An architecture change of this size needs an ADR recording the decision and its rollback, not a silent rewrite.
2. **One source of truth per layer.** Tokens stay in `app.css`'s `@theme static`; primitives become named classes there; components stop inventing one-off utility spells.
3. **A rule with teeth, or no rule.** Every invariant the HFW checks gets a check that can fail; a check that cannot fail is deleted.
4. **The lore gate and the quality bar are not touchable.** No suppression, no weakening, no new `@phpstan-ignore` / `eslint-disable` / arbitrary value.
5. **Strangler, not big-bang.** The app keeps serving; pages migrate one at a time behind the same routes.

### The target architecture

```
tokens            app.css @theme static            (already exists — §Phase 1 aligns it)
   |
named primitives app.css component layer           (ported from shell.css — §Phase 2)
   |
components       resources/js/components/**        (rewritten to use only primitives — §Phase 3)
   |
pages            resources/js/pages/**             (law-driven layout, checked — §Phase 4)
   |
checks           framework/render/data, ported     (runs on the Vue tree — §Phase 1 and 5)
```

The key move is the **primitive layer**: the app gains the ~40 semantic classes the HFW proved (`card`, `tile`, `pill`, `tag`, `type-chip`, `stat-icon`, `state`, `skel`, `warn`, `note`, `srow`, `pickcard`, `subnav`, `stepper`, `savebar`, `trust-*`, `mood-*`, `slot-*`, the `stat-*` marks, the `band-*` tints) as first-class classes in `app.css`, each with a docstring that is the same sentence the HFW's `shell.css` uses.

### Phase 0 — Rulings and preconditions

Before any code, three decisions are recorded:

1. **ADR: "Frontend architecture: named primitives over utility spells."** Accepted by the Architect; records the goal, the non-goals, the strangler policy, and the rollback (revert `app.css` and the migrated components; the routes and data never moved).
2. **Owner ruling on the six open items** (§6): confirm `FormTabs` stays a link-tab control (the HFW's flat table is a redesign that lost an accessibility property the app engineered for), confirm the flash banner is promoted to a shell primitive, and confirm `review-detail` stays a separate page or folds back into the queue.
3. **`DESIGN.md` §3 refresh.** The component inventory becomes the authoritative list of the 97 components, with each marked `near-copy`, `renamed`, `absent — enhancement`, `absent — replacement`, `absent — out of scope`, or `dead`. This is what makes the parity matrix durable instead of session-local.

**Gate:** the ADR is accepted; the owner ruling is in writing. No code moves without both.

### Phase 1 — Foundation: the check harness and token parity

1. **Port the three static checks into `tools/`.** `framework-check` becomes `tools/ui-framework-check.mjs`, scanning the Vue tree the way it scans the HTML set: it reads `resources/css/app.css` and the component tree, and fails on (a) an arbitrary value outside the approved debt list, (b) a `dark:` utility, (c) a Card inside a Card, (d) a primitive used with no docstring.
2. **Reconcile the token sets.** The app has 93 tokens, the HFW 65. The overlap is the contract; the 28 app-only tokens are either promoted into `DESIGN.md` §9 or named as app-internal. No token is deleted without a migration of its uses.
3. **Retire the debt list.** The 37 arbitrary-value lines and the `ui-lint.mjs` debt entries are each either converted to a token or primitive, or recorded as `ponytail:` simplifications with a ceiling. When the list is empty, the "debt, not a place to add" sentence in `AGENTS.md` §9 becomes obsolete and is corrected.

**Gate:** `npm run typecheck`; `vendor/bin/pint --test`; the new framework check runs clean on the un-migrated tree (it must pass before anything is rewritten, so the harness is proven independent of the migration).

### Phase 2 — The primitive layer

1. **Add the ~40 semantic primitives to `app.css`**, each copied from `shell.css` with its docstring and its measured geometry. This is the only phase that touches the stylesheet broadly.
2. **Add `DESIGN.md` §2.x tokens the primitives need** (the `status-*`, `trust-*`, `mood-*` families) if any are missing from the 93.
3. **`DesignTokensTest` extension.** Each new token is asserted non-empty and resolved in both themes, the same way the existing tokens are — the HFW's own browser gate, applied to the app.

**Gate:** `DesignTokensTest` green; `npm run typecheck`; a visual snapshot diff of one migrated sample page proves the primitives render identically to the Tailwind originals.

### Phase 3 — Component migration (strangler)

1. **Order by dependency, leaf-first.** `Form/*`, `ui/*`, and the leaf chips (`RarityChip`, `AptitudeBadge`, `MoodPill`, `SparkChip`, `ProvenanceMark`, `AbsenceValue`, `SubCell`, `KpiCard`) migrate first; the pages last.
2. **Each migration is one commit** with the before/after snapshot and the browser spec that pinned the behaviour. Nothing about props, emits, or markup semantics changes — only the class spell.
3. **Run the component's own browser spec** after each migration (Level 1), and the related suite at the end of each folder (Level 2). The full suite is a checkpoint, not the feedback loop (AGENTS.md §9).

**Gate:** the 77 browser specs stay green throughout; no Feature test is edited to accommodate a visual change.

### Phase 4 — Close the parity gaps

With the primitive layer in place, build the eight absent components and the six undrawn screens, in the order of their classification:

1. **Restore `FormTabs`** as the link-tab control (the owner ruling from Phase 0), then add the HFW's `FormDetail` block beside it — enhancement plus the accessibility the app already had.
2. **Promote the flash banner** to a shell primitive (`.flash` in `app.css`, `role="status"` strip with a sibling dismiss), and mount it on the writing pages; the HFW's `preferences.html` pattern is the reference.
3. **Draw the three registry screens** — `Runs/Create` (SCR-RUN-002), `SaveVeteran` (SCR-VET-003), `RacePlanner` (SCR-CAR-023) — in the HFW set, and add their pages to the four checks.
4. **Fold the Snapshot Entry/Setup/Review** into the set or record them as deliberately out of scope in the set's README, matching the honest "the 48 that match" claim.
5. **Remove the five dead components** (`UraDuelPanel`, `UraMilestoneTracker`, `Checkbox`, `Combobox`, `SegmentedControl`) and correct the companion set's pointer to "the Happy Meek duel panel".

**Gate:** the HFW's `framework-check` / `link-check` / `render-check` run clean on the now-complete set; the parity matrix in `DESIGN.md` §3 shows zero `absent` rows that are not also `out of scope`.

### Phase 5 — The mock catalogue graduates to the app

The dataset was built for the wireframes, but it is the app's own data:

1. **Point `build-data.php` at the live pipeline instead of the dev database**, so the catalogue tracks promotion the way the pages already do.
2. **Or, better, retire the dataset as a separate artefact** once the pages render from the API: the wireframe set keeps `assets/data/` as its snapshot, and the app's pages keep their server props. The decision is which of these two the owner wants; the dataset was never meant to be a second source of truth for a running app.
3. **Keep `data-check.mjs`** as the catalogue's invariant test regardless, because its counts and sums are checks the app does not currently run.

**Gate:** `php artisan test` and the browser suite green; `composer lore` and `composer lore-code` ruled.

### Phase 6 — Verification, versioning, and done

1. **Full Level 3 sweep** on an isolated worktree at the release commit (AGENTS.md §9 test dispatch): the hand-off sequence — targeted tests, `pint --test`, PHPStan level 6, `npm run typecheck`, `npm run lint:ui`, the lore gates, `composer audit` / `npm audit --omit=dev`.
2. **`CHANGELOG.md`** entry by the Docs Writer; **`VERSION.md`** bump by the Architect per ADR-0019 at phase exit.
3. **`AGENTS.md` §9 corrections**: the "debt, not a place to add" sentence and the "51 single-file components" snapshot both go stale and are corrected with dated notes.

**Definition of done.** The token layer is one source of truth; every component renders from named primitives; the three static checks run on the Vue tree and fail on violation; the parity matrix has no unexplained `absent` row; the dead code is gone; no product behaviour changed; and every gate in the hand-off sequence ran with its output attached.

### Risks and mitigations

| Risk | Mitigation |
|---|---|
| A visual regression hides in a "near-copy" rewrite | before/after snapshots per commit; the 77 browser specs pin geometry |
| The strangler stalls mid-migration | every phase is independently shippable; the app serves the mixed tree the whole way |
| The primitive layer over-abstracts | primitives are only the ~40 the HFW proved on 49 pages; no speculative abstraction |
| The check harness false-positives on legacy pages | the harness runs clean on the un-migrated tree (Phase 1) before anything is rewritten |
| A ruling is reversed later | the ADR records the decision and its rollback; reverting `app.css` and the migrated components restores the prior architecture |

### Open questions for the owner

1. Does "replace the frontend architecture" mean the reading above (adopt the design-system architecture inside Vue), or a larger rewrite this plan deliberately does not propose? The non-goals section is the plan's answer, but it is a scope decision only the owner can ratify.
2. `FormTabs`: restore the link-tab control (recommended), or accept the HFW's flat table and the form-switch regression it introduces?
3. `review-detail`: keep as its own page (the HFW's choice) or fold back into the queue (the app's choice)?
4. The six undrawn screens: draw them into the HFW set, or record them as out of scope in the set's README?
5. The mock catalogue: promote it to the live pipeline, or retire it once the pages render from the API?

---

*This document proposes; it decides nothing. The ADR, the owner rulings in §Phase 0, and the gate evidence at each phase are what turn it into work.*