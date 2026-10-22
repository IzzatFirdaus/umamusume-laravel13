# Frontend Architecture Plan

**Scope.** This document is in two parts. **Part I** reports what the wireframe pass found: the parity between the running Vue frontend and the static High-Fidelity Wireframe set (`docs/design-research/frontend-v2-wireframes`, "the HFW"), the mock catalogue that was built to feed it, and the divergences that still need a decision. **Part II** is a strategic, step-by-step plan to replace the application's *frontend architecture* — not its framework — with the design-system architecture the HFW proved out, inside the existing Inertia + Vue 3 stack.

**Last verified:** 2026-10-22, against `master` at `c79da9f5`. Every count in this document was re-derived from the tree during the pass that produced it: 97 components and 57 page files under `resources/js/`, 49 HFW pages, 50 `SCREEN_SPEC.md` registry rows, 77 browser specs, 200 `tests/Feature` files. Where a figure is quoted from an earlier session rather than re-measured, the source is named.

> **Amended 2026-10-22 (frontend-architecture reconciliation pass, D1 / **M-01** (D1's own label: A2; supports authoritative A16):** re-measured on `master` at **`15f7ee1b`** — five commits after `c79da9f5`, and the commit this reconciliation's numbers come from. Three of the counts in the sentence above move:
>
> - **Browser specs: 76, not 77.** Counted `tests/browser/*.spec.ts` on 2026-10-22; `playwright.config.ts:23` sets `testDir: './tests/browser'` and no spec exists outside it.
> - **`tests/Feature`: 200 is correct only as "top-level files".** Recursively the tree holds **203**, because `tests/Feature/Schema/` adds three. The figure is not wrong; its unit was unstated.
> - **Components 97 and page files 57 are confirmed** — 97 `.vue` under `resources/js/components/` (plus 5 `.ts`), 57 `.vue` under `resources/js/pages/`. Note that 8 of those 57 are Cockpit panel components (`pages/Career/Cockpit/Panels/`), not route-served pages.
>
> The sentence above stands as the record of the `c79da9f5` read it carried.

> **Re-derived 2026-10-22 at the 2.0.2 release / A1 and A2 — and the correction runs the other way: the plan's original figures were right and this note's were stale.** Measured on `master` at **`e3ac31bd`** (the 2.0.2 release commit; `da15a5ba` was HEAD when the measurement began, and neither count moved between them). Method: filesystem counts under `tests/`, each confirmed against `git ls-tree -r --name-only HEAD` so a count cannot include an untracked file by accident.
>
> - **A1 — browser specs are 77, not 76.** `tests/browser/*.spec.ts` = **77**, and **77** of them are tracked at HEAD. So **this note's "76" was a `15f7ee1b` reading**, taken before the fix pass added `tests/browser/sub-cell.spec.ts`. **Superseded: 77.** The consequence is worth stating because it inverts the earlier correction: the plan's Phase 3 gate, Phase 4 gate, risks row and Ruling-1 bullet all say **77**, and those are **correct as written** — it was M-01 and M-13 (which restates the figure as "there are 76 (A2)") that carried the stale number. A later pass reading only the dated notes would have "corrected" a correct document.
> - **A2 — `tests/Feature` is 203 top-level and 206 recursive, not 200 and 203.** Top-level `.php` = **203**; recursive = **206**, the difference being `tests/Feature/Schema/` (3 files); **206** are tracked at HEAD. So this note's "200 top-level / 203 recursive" was also a `15f7ee1b` reading, taken before the fix pass added `AbsenceChannelTest.php`, `DatabaseHubSourcesLinkTest.php` and `DesignDocEnforcementTest.php`. **Superseded: 203 / 206.**

**Status vocabulary.** This is a design plan, not a roadmap. Proposals carry `proposed` (nothing is built yet), `verified` (established in the repository), or **`open question` (needs an owner ruling)**. No slice status (`COMPLETE` / `BLOCKED BY EVIDENCE`) is used, and no feature level (`Implemented` / `Verified`) is claimed for anything the plan proposes.

> **Reverted 2026-10-22 (Group B).** The peer rewrite replaced `open question` with `ruled`; that is reverted because the general `ruled` vocabulary sits above the FQ acceptance mechanism (`answered = named assertion passes`), which is currently unsatisfiable for every `FQ-01`…`FQ-05`. The five 2026-10-22 scope rulings are cited below from `ADR-0028`, not asserted by this vocabulary.

> **Site note 2026-10-22 (close-out pass, D1 / A29 co-location).** A concurrent session rewrote the sentence above **in place**. It originally read: *"Proposals carry `proposed` (nothing is built yet), `verified` (established in the repository), or **`open question` (needs an owner ruling)**."* The word `open question` was replaced by `ruled`. **No ruling on that rewrite is recorded anywhere, and D1 has not restored it.** The change is defensible — the five scope questions were ruled the same day — but `open question` remains a live state for the fourteen entries in the FQ register, so a reader who needs it is told here that it was a sanctioned-looking removal with no sanction on record. **The full record, with the original wording at `HEAD` and the coverage check for the removed question list, is `### Owner rulings (2026-10-22)` in Part II** — search for "A concurrent session rewrote five passages". **Sanctioned 2026-10-22 (bounded); see `### Owner rulings (2026-10-22)` above for the citation and ADR-0028 for the authority.**

> **Term note 2026-10-22 (close-out pass, D1 / authoritative A20).** In this document **"migration" means a schema migration** — a database change with a reversal plan. The Phase 3 work it describes is **not** a migration in that sense: it is a change of **class spelling**, from Tailwind utility chains to named primitives, with no schema, no data, and no route touched (`ADR-0028` D7's rollback is "revert `app.css`, the migrated components and that route"). Where the prose below says "component migration", read "component re-spelling". **~30 occurrences of "migration" in the component-architecture sense are left unrenamed deliberately:** a partial rename would leave the document with two competing vocabularies, which is worse than one consistent imprecise term. A full pass would need to rename all ~30 together, or to split the document into a schema section and a class-spelling section so the word only ever appears in the first — and that is an editorial decision, not a correction.

**Decisions taken.** The five scope questions this plan raised were answered by the owner on 2026-10-22. The rulings are recorded under *Owner rulings* in Part II and are binding on the phases; Part I §6 carries each ruling beside the divergence it settles. What remains undecided is the architecture ADR itself, which Phase 0 produces.

## Amendment numbering and the A↔M map (2026-10-22)

> **Added 2026-10-22 (correction pass, D1 / authoritative **A24**; VD-01).** Two amendment series exist and must not be confused. **`A1`–`A29`** is the owner's authoritative amendment list. **`M-01`–`M-15`** is the set of measured corrections D1 actually shipped into this document; each is real, measured on the tree, and stays. Every D1-authored note below now carries its `M-` number, and states which `A` item it supports (or "additional"). **No future reference may use a bare amendment number** — write `A7` or `M-04`.
>
> The labels `A1`…`A23` that D1 originally used inside its own notes were a private, self-derived series and are **retired**; where an `M` block still contains a sub-bullet beginning `**A<n> —**`, that `<n>` is D1's historical label for its own claim, and the column below is the authority for what it means now.

| A | status | what it requires | where it lands |
|---|---|---|---|
| A1 | done | Phase 2 gate requires the D-288/G-18 block to be implemented and to assert the pair list over both themes; Phase 0 sequences it conditionally | `M-13` |
| A2 | **applied now** | move `tools/ui-screenshot-diff.mjs` to Phase 1 step 1 and point it at the **application** | Phase 1, Phase 7 note |
| A3 | **applied now** | split the Phase 7 gate into entry and exit | Phase 7 |
| A4 | **applied now** | Phase 2's primitive rule: promotion, not copy; every primitive resolves to a token | Phase 2; `M-12`, `M-03` |
| A4b | **applied now** | the measured primitive figure at two levels, conflict bucket visible | revised phase map; `M-10` |
| A5 | **applied now** | the framework check's rule set is enumerable rows, and its relationship to the other gates is stated | Phase 1; `M-04` |
| A6 | **applied now** | the debt count must fail on an **increase** against a committed baseline | Phase 1; `M-04` |
| A7 | **applied now** | Phase 4 splits into set-work and app-work, each with its own gate | Phase 4 |
| A8 | **applied now** | the `review-detail` ruling's cost, with the `SCREEN_SPEC.md` §4.5 amendment as an owner question | Phase 4; FQ-10 |
| A9 | **applied now** | the `FormTabs` specification and the one-fact-one-rendering question | Phase 4; FQ-11 |
| A10 | **applied now** | the dead-component disposition leaves the phase body and becomes an owner question; "referenced by nothing" is corrected | Phase 4; FQ-12; `M-07` |
| A11 | **applied now** | 71-vs-67 is a dev-seed artifact; name the seeder | Phase 5; `M-09` |
| A12 | **applied now** | the parity matrix's home is `primitive-reconciliation.md §7` in all **three** operative statements | Phase 0, Phase 4, phase map; `M-11`, `M-08` |
| A13 | **applied now** | catalogue provenance is date **plus** generator source commit and seed identity | Phase 5; `M-09` |
| A14 | **applied now** | the set enters the lore gate, or gets a path-scoped variant | Phase 5 |
| A15 | **applied now** | the coverage claim's exact wording, and whether `help` gets a route and a row | Phase 4/6; FQ-13; `M-08` |
| A16 | done | token counts carry a stated unit, using measured numbers | `M-01`, `M-03` |
| A17 | **applied now** | `SCREEN_SPEC.md` rows for the three Snapshot screens | Phase 4 |
| A18 | **applied now** | `DESIGN.md` §2.1 is the token table; §9 is where tokens live | Sources; Phase 2 |
| A19 | **applied now** | the `AGENTS.md` ruleblock expires by gate **and** by date | Phase 7 |
| A20 | **applied now** | retire the "migration" overload for the component work | Phase 3 |
| A21 | **applied now** | `review-detail`'s blast radius is a hard gate, not a risk row | Phase 4 |
| A22 | **applied now** | the codemod's dependency claim resolved (transitive pin or owner question) | Phase 7; FQ-14 |
| A23 | **applied now** | the codemod's expected auto-apply rate, with the variant problem stated | Phase 7 |
| A24 | done | this section | here |
| A25 | **applied now** | the ADR carries the five owner rulings, self-contained | `ADR-0028` |
| A26 | **applied now** | five checks, not three, in both operative sentences, plus the vacuous-pass condition | Phase 1; Definition of done; `M-02` |
| A27 | **applied now** | the reconciliation map exists as a file, and the literal inventory is numbered rows in this plan | Phase 2; new section; new file |
| A28 | **applied now** | one sentence at the FQ register's head stating how its ratios were obtained | FQ register; `M-15` |
| A29 | **applied now** | dated notes beside the concurrent session's in-place rewrites, recording the originals verbatim, with the peer's text untouched | five sites in Part I |

**M-series index.** `M-01` counts/commit ref · `M-02` five checks · `M-03` app.css and token units · `M-04` debt list and token convergence · `M-05` `CareerSectionNav` · `M-06` `role="status"` · `M-07` dead components · `M-08` page-coverage arithmetic · `M-09` catalogue provenance · `M-10` primitive verdicts · `M-11` PARITY home and ADR status · `M-12` token namespace and `--rule-soft` · `M-13` G-18 gate · `M-14` `@vue/compiler-sfc` and the ported checks · `M-15` the FQ register.


## Sources

One line each: path — the claim it supports.

- `docs/design-research/frontend-v2-wireframes/` — the 49-page static set, its `assets/shell.css`, and its four checks (`framework-check.mjs`, `link-check.mjs`, `render-check.mjs`, `data-check.mjs`); the visual system the plan ports.

  > **Amended 2026-10-22 (frontend-architecture reconciliation pass, D1 / **M-02** (D1's own label: A8; supports authoritative A26):** the set carries **five** checks, not four, and the fifth is the one this plan most needs. `framework-audit.mjs` (14,918 bytes) renders all 49 pages in Chromium and measures the rules a static read cannot decide: **1.4.3 contrast per text node in both themes** (`:123`), **44px effective targets** (`:157`), the heading ladder (`:191`), one-accent-per-screen (`:224`), 1.4.10 reflow at 320px (`:240`), 1.4.4 resize (`:251`), 1.4.12 spacing (`:265`) and 2.4.7 focus by tabbing (`:298`). Two consequences for Phase 1: the port must carry **five** checks, not four; and `framework-audit.mjs` calls `process.exit(0)` when Playwright is absent (`:31-34`), so a ported verbatim copy **passes vacuously** on a host without a driver. It is named here and must be fixed at port time (Non-licence 6 of `ADR-0028`), not adopted as-is.
- `docs/design-research/frontend-v2-wireframes/assets/data/` — the mock catalogue and `README.md`, the schema and provenance of the 67/106/559/35/1910 rows.
- `docs/design-research/cockpit-wireframes-2026-10-21/` — the companion set that carries the four scenario panels at full density.
- `DESIGN.md` §2 (color, typography, spacing), §3 (component inventory), §9 (where tokens live), §10 (what is enforced vs claimed), §11 (open questions) — the binding visual system the plan must not contradict.
- `docs/research-scratch/DESIGN-CORPUS.md` — the measured ramps, contrast math and the rulings (D-101, G-19) behind the token layer.
- `docs/adr/0006-design-authority-and-theme-default.md` — who owns the visual system; the authority an architecture change must satisfy.
- `docs/adr/0019-semver-label-tag-and-release-branch.md` — how a phase exit is labelled.
- `AGENTS.md` §2 (precedence), §7 (conventions), §9 (testing and gates), §11 (change-safety), §13 (UI rules) — the operational contract the plan runs under.
- `SCREEN_SPEC.md` §8 — the 50-screen registry the parity count is measured against.
- `docs/research-scratch/SKILLS-MECHANICS.md` G-SK-20 — the ruling that keeps a skill description absent.

> **Corrected 2026-10-22 (close-out pass, D1 / completes A18; item 2.4).** The `DESIGN.md` line above cites **§9 (where tokens live)** and stops there, which names the *location* of the token block but not the table that states each token's **value**. Those are two different sections and the plan needs both. The same correction was made at Phase 2 and the wording below is that wording, so the two sites agree:
>
> **`DESIGN.md` §2.1 is the token table; §9 is where tokens live.** §2.1 carries the per-role value table — light and dark in two columns, with the G-18 pair each role owes — and is what a reader consults to learn what `--color-rule` *is*. §9 says the tokens reside in `resources/css/app.css`'s `@theme static` block and that `static` is load-bearing (D-288). Phase 2 step 2 needs §2.1's values; Phase 1 step 2 and Phase 2 step 1 need §9's location rule. Citing §9 alone for the table is what made an earlier revision of this plan read as though the values were undocumented.
>
> The `DESIGN.md` line above stands as written; this note adds the second citation beside it.

---

## Working conventions

These apply to every pass touching this plan or `primitive-reconciliation.md`. Non-frontend sessions reading `AGENTS.md` will not see them; a `docs/`-level convention file is a possible later move.

One page, four rules. It exists because `docs/proposals/` is written by more than one session at a
time, and a line number measured in one session is not a location in another.

1. **KI-70 — mtime before edit.** Before editing any target file, check its mtime. If it moved since
   your last read, stop, report the foreign write, and do not edit. A shared worktree is a collision
   surface. An untracked file is not shared evidence either: cite it with its untracked status.
2. **KI-75 — no whole-worktree gate during another session.** Never run a gate that operates on the
   working tree (`pint` without `--test`, `migrate`, `npm run build`, a suite) while another session's
   work is uncommitted. Report the gate as not run, with the reason, instead of producing a result that
   describes a moving tree.
3. **Citation by anchor.** Cite by content anchor **plus** quote-back. Never a bare line number. The
   2.0.1 absorption pass is the worked failure: three of four cited line numbers were stale when
   reported, because its own insertions pushed the anchors down.
4. **Attribution discipline.** A foreign write is reported, not reconciled and not reverted.
   Complementary writes are silent reconciliation; conflicting writes are `CQ-` questions for the
   owner. A document that contradicts the tagged state is filed as a measured correction, never edited
   inside the pass that found it.

**Worked example (2026-10-22).** The A7 note's summary said the G-18 audit's status line was
"re-scoped" to read "read this audit". The sentence was intact: it still read *"Read-only. The test was
not run; agent 1's gates carry that."* and the run was added as a dated note above it. The paraphrase
described a change to a sentence rather than quoting it, and a reader acting on it would have restored
text that had never been removed. Quote first; describe only what the quote shows.


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

> **Amended 2026-10-22 (frontend-architecture reconciliation pass, D1 / **M-03** (D1's own labels: A3–A6; supports authoritative A16, A4b):** four cells of the table above are corrected by measurement on `15f7ee1b`. The rows stand as written.
>
> - **A3 — `app.css` is 629 lines, not 630.** `resources/css/app.css`, 629 lines, 29,907 bytes, git-clean on the measuring commit.
> - **A4 — "93 tokens" is right in one unit and incomplete in another.** `@theme static` contains **93 custom-property declaration sites, all 93 distinct names**: 81 are `--color-*` (81 declarations, 81 unique) and 12 are non-colour (`--radius-*` ×5, `--font-*` ×2, `--text-kpi` + its `--line-height` companion, `--container-shell-wide`, `--grid-template-columns-tiles`, `--shadow-overlay`). The dark block redeclares 66 of them, so the **file** holds 159 declaration sites resolving to 93 distinct names. `shell.css` holds **65** distinct tokens (55 `:root` ∪ 51 dark), which is the 65 the cell already states.
> - **A5 — "~120 named CSS primitives" is 107.** `shell.css` carries **107 unique top-level class selectors** (167 class names including compound selectors). The count is of selectors, not of the author's notion of a primitive; the plan's own list of portable primitives resolves to **21 families**.
> - **A6 — "13 folders" is 10 named folders plus the root.** `resources/js/components/` holds 23 `.vue` at the root and the remainder across 10 subdirectories (`career`, `catalog`, `database`, `Form`, `legacy`, `review`, `scenario`, `skills`, `support`, `ui`) — 11 buckets if the root is counted. No arrangement of the same tree yields 13.

The two have **converged on the token layer** (both use a measured `@theme`/`:root` token set with no `dark:` utilities) and **diverged on the component layer**: the app spells components with Tailwind utility classes (37 arbitrary-value lines remain as debt), while the HFW spells them with named semantic primitives whose invariants are enforced by static scripts.

> **Amended 2026-10-22 (frontend-architecture reconciliation pass, D1 / **M-04** (D1's own labels: A7, A9, A10; supports authoritative A5, A6):** three claims in the paragraph above are corrected or narrowed by measurement on `15f7ee1b`.
>
> - **A7 — "37 arbitrary-value lines remain as debt" describes a list that does not exist.** `tools/ui-lint.mjs:51-54` declares `ARBITRARY_ALLOWLIST` with **two** entries (`BaseTextarea.vue|[88px]`, `SupportTypeMark.vue|[18px]`), and `AGENTS.md` §9 calls that list "debt, not a place to add". There is no 37-line register in the tree. Worse for the Phase 1 plan: the same file's matcher at `:83` only recognises `[#…]` and `[Npx]`, so `w-[37vw]` or `text-[0.65rem]` passes the lint today — the debt cannot be recovered from the linter, and Phase 1 step 3's "the 37 arbitrary-value lines … are each either converted" has no source list to work from.
> - **A9 — "converged on the token layer" is true of the token *names* and false of the primitives.** Both files hold a measured token set, but `shell.css`'s primitives are not written against it: measured on the same commit, `shell.css` carries **115 literal hex colours across 57 lines** and **514 literal `px` tokens (55 distinct values)** outside its two token blocks, plus **10 non-var `border-radius` declarations**. Convergence is at the token definitions; the primitive layer is where the two artifacts still differ, which is the reason Phase 2 exists.
> - **A10 — `shell.css` carries four house-forbidden values, so a mechanical port imports contradictions.** It declares **4 `box-shadow`s** (`:558` inset ring on `.pickcard`, `:912` inset underline on `.subnav-item.is-current`, `:971` `var(--shadow-overlay)` on `.savebar`, `:1111` the theme switch) where `DESIGN.md` §2.3 makes `box-shadow` a house invention and limits elevation to overlays via `filter: drop-shadow()`; and it declares **one transition**, `transition:grid-template-columns .18s ease` (`:178`), whose duration (`.18s`, not `150ms`) and easing (`ease`, not `ease-out`) both sit outside MOTION 2, and which animates layout rather than any of the five sanctioned scopes. Both are CONFLICT rows in the primitive map and both are owner questions, not port instructions.

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

> **Amended 2026-10-22 (frontend-architecture reconciliation pass, D1 / **M-05** (D1's own label: A13; additional — no authoritative A item):** five of the six renamed counterparts are confirmed by measurement on `15f7ee1b`; one is not. The table stands as written.
>
> - **A13 — the `CareerSectionNav` counterpart does not exist on the cockpit pages.** The row above says the Career rail group appears "on all four cockpit pages". Measured: none of `cockpit-ura-finale.html`, `cockpit-unity-cup.html`, `cockpit-trackblazer.html`, `cockpit-grand-concert.html` contains a career section nav — searching them for `Career rail` and `Training Races` returns **0** hits. Each carries the **global** primary rail (`nav.rail-nav aria-label="Primary"`, e.g. `cockpit-ura-finale.html:25`) plus a mobile bar (`:157`), and reaches career destinations through scattered `btn` links (`:63`, `:127-128`). `CareerSectionNav` therefore classifies as **`the set is behind`**, not `renamed`: the app has a seven-anchor career section nav the set does not draw.
> - **Confirmed, no amendment — the `KpiCard` row's "59 `.tile` nodes across 15 pages" is exact.** Counted on the same commit: 59, across exactly 15 pages.
> - **Confirmed unchanged:** `DeckAnalysis` → "Deck diagnostics" (11 matching strings in `career-deck.html`); the ENERGY Low band; the "Stats and state" card; the preflight grid (`TRAINEE`/`PARENTS`/`TARGET TOTAL` present); `Best match`/`Confidence` and the match ladder in `review*.html`; the inline Source column across the `database-*.html` pages.

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

> **Amended 2026-10-22 (frontend-architecture reconciliation pass, D1 / **M-06** (D1's own label: A12; additional — no authoritative A item):** the write-confirmation half of the row above is confirmed exactly and the "other writing pages carry nothing" half is not. Measured on `15f7ee1b`:
>
> - **Confirmed: 28 writers.** `->with('status'` appears 28 times across 14 controllers.
> - **The app's holder is a shell element, not a component.** The banner lives inline in `resources/js/layouts/AppLayout.vue` (`:28` reads `page.props.flash.status`, `:33` sets `FLASH_DISMISS_MS = 5000`, `:423-448` renders `<p role="status">` with a **sibling** dismiss `<button aria-label="Dismiss">`, never inside the region — the WCAG 4.1.3 shape the row credits). So the matrix row is `app-only — not in the set` in the strict sense: there is no component to classify, and Phase 4 step 2's "promote the flash banner to a shell primitive" is a *move out of `AppLayout.vue`*, not a port from the HFW.
> - **Seven HFW pages carry `role="status"`, not one.** Beyond `preferences.html:62` ("All changes saved"), these carry it: `event-decision.html:164` (`.dirty` "Option 2 selected"), `inheritance.html:188` ("2 sparks chosen"), `legacy-builder.html:124` ("Unsaved legacy"), `race-decision.html:178` ("No race chosen"), `review-detail.html:120` ("Not yet answered"), `run-deck.html:129` ("4 of 6 slots filled"). Six of the seven are **save-bar dirty state**, not write confirmations — a different fact from the app's flash banner, and the distinction matters for Phase 4: the set already drew the "unsaved" channel and left the "saved" channel to one page.

**Dead code in the app (5).** Referenced by nothing: `UraDuelPanel`, `UraMilestoneTracker`, `Checkbox`, `Combobox`, `SegmentedControl`. Two are URA panel modules the app no longer mounts; the HFW's cockpit page still points a reader at "the Happy Meek duel panel" in the companion set, which is the treatment for a panel the code has dropped.

> **Amended 2026-10-22 (frontend-architecture reconciliation pass, D1 / **M-07** (D1's own label: A11; supports authoritative A10):** "referenced by nothing" is false for all five, and by this repository's own convention none of them is dead. Measured on `15f7ee1b`:
>
> | Component | referenced by | verdict under `SCREEN_SPEC.md` §7-13 |
> |---|---|---|
> | `UraDuelPanel` | `tests/Feature/UraFinaleSurfacesTest.php:304` and `:353` | **not dead** — a Feature test reads the file by absolute path and asserts on its source |
> | `UraMilestoneTracker` | `tests/Feature/UraFinaleSurfacesTest.php:303` | **not dead** — same |
> | `Checkbox` (`ui/`) | `tests/browser/fixtures/form-field.ts:5`, asserted by `form-field.spec.ts` | **not dead** — mounted by a browser fixture |
> | `Combobox` (`ui/`) | `tests/browser/fixtures/combobox.ts:2`, asserted by `combobox.spec.ts` (5 cases) | **not dead** |
> | `SegmentedControl` (`ui/`) | `tests/browser/fixtures/form-field.ts:7`, asserted by `form-field.spec.ts:109` | **not dead** |
>
> `SCREEN_SPEC.md` §7-13 records the ruling this rests on: a component "referenced only by a test" was **not** treated as dead there — the eight orphans of that item were closed by deciding, per component, whether to mount or delete. Applying that convention here, the honest classification is five components with **no production call site**, of which four are held alive by tests that pin an accessibility behaviour, and **zero** are "referenced by nothing". Consequences for Phase 4 step 5: deleting all five is a guaranteed red suite, not a cleanup; each needs its removal paired with the test that reads it, and `UraDuelPanel`/`UraMilestoneTracker` additionally hold the "no gate figures / no win-chance vocabulary in the component" assertions at `UraFinaleSurfacesTest.php:301-319` and the "no `state=\"unknown\"`" assertion at `:345-364`. Whether that guard retires with the file or moves to whatever replaces it is an owner question (**FQ-08**).
>
> Also corrected while in this paragraph: the set on disk is the **frontend-v2-wireframes** directory, and the companion cockpit set (`docs/design-research/cockpit-wireframes-2026-10-21/`, 5 HTML + README) draws the Happy Meek section as an `<h4>` with an annotation at `02-ura-finale.html:288`, described as a dedicated panel at `:290-291` — a drawn block, not a component.

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

> **Amended 2026-10-22 (frontend-architecture reconciliation pass, D1 / **M-08** (D1's own label: A14; supports authoritative A15):** the sentence above does not reconcile arithmetically, and one of its premises is not in the app. Measured on `15f7ee1b`:
>
> - **The 49 pages decompose into 43 registry-mapped pages, not 47.** Six of the 49 are not screens: `index.html` (the set's table of contents), the three error documents (`error-404`, `error-419`, `error-500` — which *are* registry rows, SCR-SYS-001 and siblings, but are not pages a reader navigates the set with), `help.html`, and `review-detail.html`. 49 − 6 = 43. The "47" is therefore a number this paragraph carried, not one the directory yields.
> - **`help` has no counterpart in the app.** `help.html` exists in the set (`<title>Help · Trainer Desk wireframe</title>`, `<h1>How this works</h1>`); `routes/web.php` contains **zero** `help` routes and `SCREEN_SPEC.md` contains **zero** occurrences of the word. So `help` is a screen the set invented, which is a legitimate thing for a redesign to propose but is not "one extra" alongside a registry count — it is a thirteenth thing, and Phase 4's "makes the registry claim true: 50 of 50 plus `help`" is a **new screen**, not a drawn one.
> - **The three registry screens are confirmed absent and correctly listed**, and all six files in the table exist at the stated routes. One route name is worth recording: Save Veteran is `runs.veteran` (`GET /training-runs/{run}/veteran`), not `runs.save-veteran`, so a Phase 4 reference must use the registered name.
> - **A seventh screen is outside this table.** `Database/Sources.vue` is registered at `database.sources`, appears in `SCREEN_SPEC.md` §8's authority table, and has no `SCR-*` registry row and no HFW page. It is not one of the six, so the undrawn set is six *known* plus one unregistered.

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

> **Amended 2026-10-22 (frontend-architecture reconciliation pass, D1 / **M-09** (D1's own labels: A15–A17; supports authoritative A13):** the four row counts are confirmed exactly; three provenance claims are corrected. Measured on `15f7ee1b` by loading each file into a sandbox and counting.
>
> - **Confirmed: 67 trainees, 106 forms, 559 cards, 35 effects, 1,910 skills.** 37 of the 67 trainees carry more than one form. `description` is **null on all 1,910** skill rows (0 `undefined`, 0 empty), which is G-SK-20 holding as stated.
> - **A15 — `sourceText` is an object, and its English half is absent on half the rows.** Fact 2 above says "`sourceText` carries the publisher's English as quarantined reference". It carries `{ en, end }`: `.end` is present on **1,910/1,910**, but `.en` on **985/1,910**. A surface that binds `sourceText.en` and assumes presence would render an absence on 925 rows; one that binds `.end` would not. The distinction is load-bearing for any page Phase 3 migrates.
> - **A16 — the capture date is in none of the five locations Phase 5 requires.** Phase 5 step 1 says "`assets/data/README.md` and each generated file's header carry the capture date". Measured: **0 of 5**. None of the four generated files' 25-line headers contains a `2026-10-` date, and `assets/data/README.md` contains no date, no "capture", no "frozen" and no "snapshot". The only dates in the set are read-dates in other files (`shell.css:3,38,59` — "read 2026-10-21"; `build-index.js:5`; the set `README.md`). Phase 5's gate ("the captured date is present in all five locations") is currently red, and this is the whole substance of the freeze ruling.
> - **A17 — `build-data.php` records no source identity.** Fact 1 is right that it reads the dev database joined with the committed export bodies (documented at `build-data.php:11-23`), and it does refuse the three sum failures. But it records **no commit, no seed hash, and no generated-at stamp**: a grep for `git`/`commit`/`rev-parse`/`seed` identity over the file finds only the instruction "Run `php artisan migrate:fresh --seed` first". So a catalogue regenerated tomorrow is indistinguishable from this one, which is exactly the ageing risk the freeze ruling names — and `README.md` carries nothing to close it.
> - **Fixture provenance, named:** `[Sample Form]` is written by `database/factories/CharacterCardFactory.php:27`; `Form Counted N` by `tests/Feature/CatalogRosterTreeTest.php:212` and `tests/Feature/CareerTraineeSelectTest.php:227`. Factories invoked from **tests**, not from a seeder — so the contamination is a test-run artifact, and `migrate:fresh --seed` alone does not reproduce it.
> - **The live "71" is displayed on the Dashboard.** `DashboardController.php:72` computes `'trainees' => Umamusume::count()` into `counts`, and `pages/Dashboard.vue:577` renders it as a `KpiCard` labelled "Trainees". Divergence 4 in §6 therefore has a named screen, not just a controller.

### 5. The three-page content update

The trainee list, support-card list and skill set now carry the catalogue's real values instead of illustrative ones: `catalog.html` (six real cards with aptitudes and growth), `support-cards.html` (seven real Global SSRs, one per type, with the Pal card's `None` friendship bonus), `skills.html` (six real skills, the Type filter corrected to `Speed / Recovery / Passive / Unspecified`, the Rarity filter removed because the source names no tier), and the three detail pages aligned to them. The `catalog-detail` stats table gained the ★4 and ★5 columns (summing to 500 and 550); the `support-card-detail` ladder became `Effect / Stated at / Cap` because the source states anchors, not a per-break progression; the `skill-detail` readable-description block now reads **"No client string."**

### 6. The divergences, and how each was ruled

> **Site note 2026-10-22 (close-out pass, D1 / A29 co-location).** A concurrent session rewrote **this heading and the list beneath it** in place. The heading originally read *"### 6. The divergences that need a decision"*, and the five divergences were a **numbered list**; both are now a heading that asserts they *were* ruled and a **five-row table with a Ruling column**. The five items are the same five divergences — nothing added or dropped — so the change is presentation plus new information, not a loss. **No ruling on the rewrite is recorded, and D1 has not restored the original.** Treat the Ruling column as owner-supplied information whose provenance is the unnamed session, not this plan. **Full record: `### Owner rulings (2026-10-22)` in Part II**, entry 2. **Sanctioned 2026-10-22 (bounded); see `### Owner rulings (2026-10-22)` above for the citation and ADR-0028 for the authority.**

Five divergences came out of the parity pass. All five were put to the owner and ruled on 2026-10-22; the ruling is the last column, and the plan's *Owner rulings* section carries the consequence of each.

| # | Divergence | Severity | Ruling |
|---|---|---|---|
| 1 | **`FormTabs` is the only place the substitute is weaker than the original.** The app engineered the tabs as addressable, reload-surviving, `aria-current`-carrying links; the HFW's costume table has no switcher, so a three-form trainee cannot switch forms in the set. | Accessibility regression | **Restore the link-tab control** and add the HFW's `FormDetail` beside it |
| 2 | **The flash banner was not dropped so much as not promoted.** The app has 28 writers confirming a completed write, a `role="status"` live region with a 5 s auto-dismiss, and a sibling dismiss button (never inside the region — WCAG 4.1.3). `preferences.html` already carries the surface; the other writing pages do not. | Coverage gap | **Promote** it to a shell primitive and mount it on the writing pages |
| 3 | **`review-detail.html` is ahead of the code.** The app renders that detail inline inside `Review/Index.vue`; the set gives it a page. | Divergence, not a defect | **Keep it as its own page** — the one sanctioned behaviour change |
| 4 | **The live "71 trainees" is wrong by four.** It counts the four fixture rows. The HFW and the catalogue both say 67. | Data correctness | Folded into the catalogue freeze; the set states 67 and the fixture rows stay documented, not printed |
| 5 | **The four cockpit pages delegate to a sibling set** (`cockpit-wireframes-2026-10-21/`), so the scenario panels are documented but not in the HFW's own check loop. | Scope boundary | Accepted as documented; the sibling set keeps the full-density treatment |

### 7. The enforcement gap

This is the finding the implementation plan exists to close:

| | App | HFW |
|---|---|---|
| Layout rules | `ui-lint.mjs`: no Card-in-Card, no arbitrary values (with a debt list) | `framework-check.mjs`: law rules with teeth, proven by mutation |
| Rendered rules | 77 browser specs, per-feature | `render-check.mjs`: geometry rules a static read cannot decide, all 49 pages |
| Data rules | none at the component level | `data-check.mjs`: counts, joins, sums, the classic-script load |
| Copy/identifier hygiene | `UiHygieneTest`, `LoreGateParityTest` | the annotation-key discipline |

The app has *stronger* verification overall (200 Feature tests, 77 browser specs), but its **visual-system rules live in a lint script that names debt instead of closing it**, while the HFW's rules are structured so that a violation fails the check. The plan below ports that discipline without weakening either side.

> **Re-derived 2026-10-22 (agent-2 absorption pass, B2). `tests/Feature` is 203 top-level / 206 recursive; the "200" above is a `15f7ee1b` reading.** Measured at `e3ac31bd` three ways that agree: top-level `*.php` = **203**, recursive `*.php` = **206**, and `git ls-tree -r HEAD -- tests/Feature` filtered to `*.php` = **206** tracked. The 3-file difference is `tests/Feature/Schema/`. The additions that make 200 stale are this pass's own `AbsenceChannelTest.php`, `DatabaseHubSourcesLinkTest.php` and `DesignDocEnforcementTest.php`. M-01's figures are therefore **superseded**: the amendment block at the head of this file carries the corrected pair (203/206), and 200 survives only as "top-level files, before 2026-10-22". The 77 beside it is correct and is re-derived in the note beside §M-13.

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

   > **Amended 2026-10-23 (CQ-01=C).** A single-use component that encapsulates its utility chain **is a primitive at the component layer**. The rule "components stop inventing one-off utility spells" applies to *templated page code*, not to the component that IS the primitive. `Form/*.vue` is the canonical case — seven components after the count correction at `82c94f8b`, plus the `ui/` field wrappers (`TextInput`, `Textarea`, `Select`, `Checkbox`, `FormField`) that re-use them; each carries its token utilities once, in one file, in one place, and a `.field` CSS class on top would add indirection, not remove it. The Phase 3 gate note's *"form controls … were read and confirmed clean"* already recorded this reading; the amendment makes it explicit and gives it a reason. `Form/*.vue` are **accepted, not re-spelled**, at Phase 3.
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

> **Amended 2026-10-22 (frontend-architecture reconciliation pass, D1 / **M-10** (D1's own label: A21; supports authoritative A4b):** the list above is the right *families*, but "~40 semantic classes" is not what a count of them yields, three of the named entries are not portable as written, and one is forbidden outright. The reconciliation map (same date, `15f7ee1b`) classifies every named primitive against the app's token values. Summary, in the verdicts the map uses:
>
> | verdict | count | primitives |
> |---|---|---|
> | **REUSE** — every value already resolves to a token | 4 | `.trust-*`, `.mood-*`, `.band-*` (all 10 of its tint/line tokens exist), `.stat-sp` (duplicate of `--color-sp-ink`) |
> | **RE-SPELL** — same intent, house token instead of a literal | 10 | `.tile`, `.pill`, `.tag`, `.type-chip`, `.stat-icon`, `.state`/`.empty`, `.warn`, `.note`, `.info`, `.step` ink |
> | **CONFLICT** — contradicts a house ruling; owner question | 6 | `.card` and `.srow` (border token missing, **FQ-01**), `.pickcard` and `.subnav` (`box-shadow`, **FQ-02**), `.savebar` (elevation on a non-overlay, **FQ-03**), `.state-error` (`#fff` on `--risk`, **FQ-04**) |
> | **PROMOTE-TO-TOKEN** — measured value, no token | 7 | `--color-stat-{speed,stamina,power,guts,wit,pal,group}` (**FQ-05**) |
> | **DROP** — the house forbids it | 1 | **`.skel`** — `AGENTS.md:569` bans decorative skeletons and `ADR-0007` limits loading states to user-initiated async actions |
>
> **Two structural facts the phrase "~40 semantic classes" hides.** (a) `shell.css`'s primitives do not reference the app's tokens by name — the set uses unprefixed custom properties (`var(--panel)`, `var(--rule-soft)`, `var(--ink)`) while `app.css` declares `--color-*`. A verbatim paste therefore resolves **nothing**, and because an undefined custom property inherits rather than erroring, it renders as the inherited colour instead of failing. (b) `--rule-soft` is referenced **23 times** in `shell.css` and **does not exist** in `app.css` at all. Both are why Phase 2 cannot be "copied from `shell.css`" (see **FQ-01** and **FQ-06**), and why the `@theme static` requirement in Non-licence 5 matters: `static` is what stops Tailwind pruning a token whose only consumer is a hand-written `var()`.

### Phase 0 — Rulings and preconditions

Before any code, two decisions are recorded. The five scope questions were ruled by the owner on 2026-10-22 (see *Owner rulings* below) and are inputs here, not prerequisites.

> **Site note 2026-10-22 (close-out pass, D1 / A29 co-location).** A concurrent session rewrote the first sentence above in place: it originally read *"Before any code, **three** decisions are recorded:"*, counting a middle decision that ruled the six open items. That ruling was answered the same day, so the count is now **two**. **No ruling on the rewrite is recorded, and D1 has not restored it.** The reduction is arithmetically correct — Phase 0 lists two numbered items below — but the original three-decision structure is not recoverable from this sentence alone; **full record: `### Owner rulings (2026-10-22)` in Part II**, entry 3. **Sanctioned 2026-10-22 (bounded); see `### Owner rulings (2026-10-22)` above for the citation and ADR-0028 for the authority.**

> **Verified correct 2026-10-22 (close-out pass / T4.1 — a verifiable correction, not an owner decision).** "Two" is right, and this note records why rather than leaving it as an unexplained rewrite. Phase 0's body below numbers exactly **two** decisions: the ADR, and the `DESIGN.md` §3 refresh. The third decision the older text carried was *"owner ruling on the six open items"*, and it is no longer a Phase 0 decision because it has **already been answered**: the `### Owner rulings (2026-10-22)` table records all five scope questions as ruled, so a decision whose input is settled is not a precondition any more. The count therefore fell from three to two by discharge, not by loss — and the discharged decision leaves nothing behind to track, which is why no FQ entry was created for it. Verified against the plan's own content only; no `DESIGN.md` or `SCREEN_SPEC.md` claim is involved.

1. **ADR: "Frontend architecture: named primitives over utility spells."** Accepted by the Architect; records the goal, the non-goals, the strangler policy, the `review-detail` route as the one sanctioned behaviour change, and the rollback (revert `app.css`, the migrated components and that route; the remaining routes and the data never move).
2. **`DESIGN.md` §3 refresh.** The component inventory becomes the authoritative list of the 97 components, with each marked `near-copy`, `renamed`, `absent — enhancement`, `absent — replacement`, `absent — out of scope`, or `dead`, and with the six newly drawn screens listed. This is what makes the parity matrix durable instead of session-local.

**Gate:** the ADR is accepted and `DESIGN.md` §3 carries the refreshed matrix. No code moves without both.

> **Amended 2026-10-22 (correction pass, D1 / authoritative A12 at this site, A25).** Two restatements. The gate sentence above stands as written.
>
> **A12 (site 1 of 3) — the matrix is not carried by `DESIGN.md` §3.** The gate above must read: *"the ADR is accepted and the parity matrix at `primitive-reconciliation.md §7` shows every component classified, with `DESIGN.md` §3 citing it"*. `DESIGN.md` §3 is the durable component inventory and is the *owner's* document; `primitive-reconciliation.md §7` is the derived, revisable classification that D2 writes. Putting the matrix in §3 would make a derived artefact an owner-owned claim. The other two sites are the Phase 4 gate and map row 0, each noted at its own place.
>
> **A25 — the ADR now carries the five rulings.** `ADR-0028` gains `## Owner rulings promoted from the plan (2026-10-22)` between its Context and Decision sections, carrying each of the five questions as asked, each ruling as given, and its consequence, with Decision and D6 citing it. The ADR is now self-contained: a reader who opens only the ADR can see the scope the architecture was ruled under. `ADR-0028` remains **`Proposed`** — an agent cannot accept an ADR, so the "accepted" half of the gate is still the Architect's act.

> **Amended 2026-10-22 (frontend-architecture reconciliation pass, D1 / **M-11** (D1's own labels: A22–A23; supports authoritative A12, A25):** the ADR this phase describes now exists as a draft, but it is `Proposed`, not accepted, and the classification it needs is a separate document. Three corrections.
>
> - **A23 — the ADR is `docs/adr/0028-frontend-architecture-named-primitives.md`, and its status is `Proposed`.** Phase 0 item 1 reads "Accepted by the Architect". An agent cannot accept an ADR — `ADR-0027`'s own contract line records the same constraint ("the owner's ruling is recorded; the agent cannot accept ADRs"). `ADR-0028` is therefore drafted and left `Proposed` for the Architect, and **the Phase 0 gate stays closed until that signature exists**. The gate's wording is unchanged; what this note adds is that a draft is not a signature, so Phase 0 is *started*, not passed.
> - **A22 — the parity matrix lives at `primitive-reconciliation.md §7`, and it is written in a later pass (D2).** Phase 0 item 2 says the inventory "becomes the authoritative list … with each marked `near-copy`, `renamed`, `absent — enhancement`, `absent — replacement`, `absent — out of scope`, or `dead`". `ADR-0028` D4 places the per-component classification in `primitive-reconciliation.md §7` — not a new root document (`AGENTS.md` §3 closes that set), not a research master (it is derived, and is revised whenever either side moves), and not `DESIGN.md` §3, which keeps the durable inventory and may cite `primitive-reconciliation.md §7`. **`primitive-reconciliation.md §7` does not exist yet and is not created by this pass.** `DESIGN.md` §3 gains no matrix until D2 produces one to cite.
> - **A22 (scheme) — the six categories above are insufficient, and `ADR-0028` D5 adds two.** They are **`app-only — not in the set`** (the HFW never drew it, so it is not one of the `absent` classes, which all presume a counterpart exists — the flash banner is the worked case, since it lives in `AppLayout.vue` and has no component to classify) and **`the set is behind`** (the app's treatment is newer or richer than the set's — `CareerSectionNav` is the worked case, see A13). Classification keys on **rendered signature** — ARIA roles, class families, node counts — never on names, because the HFW rewrites all copy and literal-text matching returns 0 of 97.
> - **A22 (deferral) — the per-component classification is deferred to D2 for every component a concurrent fix pass is changing.** Measured on `15f7ee1b`, the working tree carried uncommitted changes to `ProvenanceMark.vue`, `career/CareerSectionNav.vue`, `scenario/ScenarioPanel.vue`, `pages/Career/Preflight.vue` and `pages/Database/Sources.vue` (plus five specs), so their markup — and therefore their rendered signature — is mid-change. Those rows are recorded as **pending D2**, not classified here. Classifying them now would key the matrix on a tree that no longer exists when D2 opens.

> **Phase 0 gate — closed 2026-10-22.** ADR-0028 accepted. Parity classification at `primitive-reconciliation.md §7`. `DESIGN.md` §3 cites it. Phase 1 opens; no owner rulings block it.

### D2 brief

**Absorbed from `D2-BRIEF.md` 2026-10-22.** In the body below, "this file" means this section; references to `D2-BRIEF.md` name this section.

**Hand this file to a fresh agent verbatim.** It is self-contained: every number below was measured on the tree it names, and every rule it cites is quoted from its owner. Do not re-derive the numbers; do verify them if you doubt them, and say so if they moved.

---

### 1. What D2 is, and is not

**D2 writes two things and nothing else:**
1. **`primitive-reconciliation.md §7`** — the per-component parity matrix.
2. **`DESIGN.md` §3** — an **appended, dated** refresh that **cites** `primitive-reconciliation.md §7`. Append only. Do not rewrite, reorder or delete any existing sentence anywhere in `DESIGN.md`, and touch nothing outside §3.

**D2 is not:** an amendment pass (that was D1), a question register (that is D3), a set revision, or a code change. D2 **classifies**; it does not fix, does not decide, and does not resolve a conflict. Every ambiguity, divergence and owner question D2 finds goes to `## For D3` in `primitive-reconciliation.md §7`, phrased as a question with two options and the consequence of each.

**Do not** edit: `frontend-architecture-plan.md`, `primitive-reconciliation.md`, `ADR-0028`, `SCREEN_SPEC.md`, `AGENTS.md`, any code, any test, any config, `VERSION.md`, `CHANGELOG.md`. Do not write the owner-question register.

---

### 2. Preconditions — verify each, stop and report if any fails

| # | precondition | how to check |
|---|---|---|
| 1 | the tree is committed and quiet | `git status --short`; `git rev-parse --short HEAD`. Record both in the output. **Two valid bases, and D2 opens against the later one:** the release tree is `e3ac31bd` (tag `2.0.2`); `da15a5ba` is tag `2.0.1`, which 2.0.2's single `test(dashboard)` commit sits on top of. If the plan or the map changed under you, say so and do not overwrite |
| 2 | `docs/proposals/primitive-reconciliation.md` exists | it is **the authority for the primitive layer**. If it is absent, stop: the parity matrix cannot qualify a near-copy without it. **It must also be committed before D2 opens** — as of 2026-10-22 it was still untracked, along with `D2-BRIEF.md`, `g18-gate-audit.md`, `ADR-0028` and the plan's own edits. A matrix that cites an untracked map cites a file no other session's tree contains |
| 3 | the HFW set is unchanged | `docs/design-research/frontend-v2-wireframes/` — 49 `.html`, `assets/shell.css` 1,126 lines |

---

### 3. The measurements — authoritative, re-derived at `e3ac31bd` (2026-10-22, agent-2 absorption pass)

| measure | value | note |
|---|---|---|
| component files | **102** | **97 `.vue` + 5 non-SFC `.ts` modules**; say "102 files / 97 SFCs" and never conflate the two |
| … by folder | **12 buckets**: root **23**, career **20**, scenario **18**, ui **17**, Form **9**, support **5**, catalog **3**, database **2**, legacy **2**, review **2**, skills **1** | sums to 102 |
| page files | **57** | `.vue` under `resources/js/pages/`, **including** the 8 Cockpit panels at `pages/Career/Cockpit/Panels/`. State your definition of "page file" explicitly: 57 total = 49 route-served pages + 8 panel components |
| browser specs | **77** | `tests/browser/*.spec.ts` — **re-derived**: filesystem 77 and `git ls-tree -r HEAD` 77 agree. Unchanged from `da15a5ba`, because 2.0.2 corrected an existing spec rather than adding one |
| `tests/Feature` files | **203** top-level / **206** recursive | **re-derived**: top-level `.php` 203, recursive 206, tracked at HEAD 206, the 3-file difference being `tests/Feature/Schema/` |
| colour tokens pinned | **81** | `DesignTokensTest.php:271` (`toHaveCount(81)`), re-asserted at `:571` |

**Corrected 2026-10-23 (Phase 3 continuation).** The three folder counts above over-count. Measured against the filesystem at `45adcab9`: **ui 15** not 17, **Form 7** not 9, **scenario 17** not 18. **Total 97**, not 102. A name-based check also double-counts `SparkChip.vue`, which exists at both root and `legacy/` — match by path, not name. The other folder counts (root 23, career 20, support 5, catalog 3, database 2, review 2, legacy 2, skills 1) are correct; with the three above corrected, the table sums to Total **97**.

**Do not inherit a count from `frontend-architecture-plan.md` or from `DESIGN.md`.** The plan's Part I still carries pre-release figures with dated corrections beside them; the corrected value is the one above.

---

### 4. The eight categories — fixed, one per component

`near-copy` · `renamed` · `absent — enhancement` · `absent — replacement` · `absent — out of scope` · `dead` · **`app-only — not in the set`** · **`the set is behind`**

The last two exist because the first six cannot express them: a component the HFW never drew is not `absent` (every `absent` class presumes the set has a counterpart), and a screen whose HFW treatment is older than the app's is not `near-copy`. **`app-only`** — the flash banner is the worked case, since it lives inside `AppLayout.vue` and there is no component to classify. **`the set is behind`** — `CareerSectionNav` is the worked case: the app has a seven-anchor career section nav that none of the four cockpit pages draws.

---

### 5. Two qualification columns the matrix MUST carry

A `near-copy` verdict is a claim about **fidelity**, and the port will not have that fidelity unless the tokens it depends on agree. So every row carries two columns beyond the category:

1. **Which shared token names the component depends on.** Take them from `primitive-reconciliation.md` §1 (the 52 shared names) and §1.1 (the three whose light values differ).
2. **Whether each of those names agrees in value** between `app.css` and `shell.css`.

Without both, a "near-copy" records a resemblance the port cannot deliver. The three named divergences in `primitive-reconciliation.md` §1.1 are exactly the failure this prevents — `primary-action`, `on-primary-action`, `shadow-overlay`. **The rule the promotion follows: the app's value wins**, so where the two differ the ported primitive will not look like the set.

---

### 6. The 14-file signature list, and the static-set caveat

The fix pass changed the **rendered signature** of these files. The list was 16 and is now **14**: `AbsenceValue.vue` dropped (consumed but never edited) and `Database/Index.vue` dropped (its markup did not change — the **controller's payload** grew instead).

**The 14:**
`ProvenanceMark.vue` · `SubCell.vue` · `ui/DataTable.vue` · `scenario/ScenarioPanel.vue` · `scenario/TrackblazerRoutePlanner.vue` · `scenario/TrackblazerFatiguePanel.vue` · `scenario/UraMilestoneTracker.vue` · `career/CareerSectionNav.vue` · `Career/Cockpit/Panels/CareerGoalsPanel.vue` · `Career/Cockpit/Panels/TeamRankLadderPanel.vue` · `Career/Cockpit/Panels/UraMandatoryLadder.vue` · `Career/Preflight.vue` · `Review/Index.vue` · `Database/Sources.vue`

**The caveat, which is the reason the list matters:** the set is **static and was drawn from the pre-fix app**. A signature change can therefore open a divergence the inherited matrix could not contain. For each of the 14, determine whether its HFW page still matches the app's **current** signature, and record: the HFW page, whether it still matches, and exactly what differs. **A divergence found here is a SET-REVISION item, not something to absorb silently** — the set is a frozen dated artefact under owner ruling 5, and a silent divergence makes it a lie. List every one under `## Divergences introduced by the fix pass` and mirror each in `## For D3` as a question (revise the set, or revert the change — that is the owner's call).

**One divergence is already known and must be routed, not absorbed:** the set's dark `primary-action` is a literal `#2255FF` and its dark block never redeclares it, so the set's **dark primary fill is its light one**, while the app's `--color-brand-chrome` resolves to `#1A3FCC` in dark. See `primitive-reconciliation.md` §1.2 for the verdict and the route.

---

### 7. Disposition rules — no component is "dead" on an inherited claim

The plan once instructed "remove the five dead components" on the ground that they are "referenced by nothing". **That ground is false.** All five are referenced — by a Feature test or a browser fixture — and `UraMilestoneTracker.vue` had its rendered signature changed by the fix pass, which is proof it is live enough to maintain.

- Classify each of the five **on the evidence**, citing what references it.
- Cite **`SCREEN_SPEC.md` §7-13** for the disposition convention (a component referenced only by a test is *not* dead; the question is per component: mount or delete, decided, not assumed) and **§7-24** for the five panel keys whose mounting is an open owner ruling.
- **Do not mark any of them `dead` and do not recommend deletion.** The disposition is owner-ruled; it is D3's question.

---

### 8. Where the matrix lives, and how `DESIGN.md` cites it

**The matrix is `primitive-reconciliation.md §7`.** `DESIGN.md` §3 **cites** it — appended, dated. This is the authority inversion the plan's A12 corrects: `DESIGN.md` §3 keeps the app's component inventory (its own five status words, one line of purpose each), and the parity classification lives in `primitive-reconciliation.md §7`. **Do not restate the parity classification inside `DESIGN.md`.**

`primitive-reconciliation.md §7` is not a root document (`AGENTS.md` §3 closes that set), not a research master (it is derived, and revised whenever either side moves), and not part of `DESIGN.md` §3. Rationale: `ADR-0028` D4.

---

### 9. The primitive layer's authority, and Phase 2's scope statement

`docs/proposals/primitive-reconciliation.md` is **the authority for the primitive layer** — not the plan. Cite it rather than restating it.

Its **§2.1** is **Phase 2's scope statement**: the set's **107** top-level class selectors reduce to **54 portable** + **50 duplicated** + **3 dropped**, and the 54 group into **21 families** — **6 conflict, 11 re-spell, 3 reuse, 1 drop** — plus **7 promoted token values** that belong to the single `.stat-*` family and are not additive. Its **§4.1** makes the question register executable: each cause names the assertion that will fail and the pair it fails on, so an FQ is answered when its named assertion passes.

**One qualifier on that last sentence, added 2026-10-22 (agent-2 absorption pass).** The gate's first run is recorded in `## G-18 gate audit` below and in agent 1's release record: it ran at `da15a5ba`, 3 cases / 184 assertions / 0 failures / both themes, and **none of the five causes' pairs is in its list**. So §4.1's "executable" describes the intended mechanism, not the current state — the audit's findings are the gate's **initial state**, not its behaviour under Phase 2. **D2 classifies components; it does not re-litigate the gate, and it must not treat a green run as evidence that any FQ question is settled.**

---

### 10. One test that might red, and it will be telling the truth

`tests/Feature/DesignDocEnforcementTest.php` does **not** pin `DESIGN.md` §3, so D2's appended §3 needs **no test update**. But the test does red if the append (a) changes the token count, (b) moves §8's superseded sentence, or (c) removes §10's dated marker. If it reds, **it is correct** — do not edit the test; report the conflict.

---

### 11. Question namespaces — additive, never renumbered

Four prefixes, each a provenance tag, merged by concatenation:

| prefix | owner |
|---|---|
| `FQ-nn` | D1's plan questions — **FQ-01…FQ-14, FQ-16, FQ-17** |
| `BQ-nn` | the fix pass's questions |
| `EQ-nn` | the reconciliation map's causes |
| `CQ-nn` | D2's own, to be assigned by D3 |

**`FQ-15` is reserved and unused** (a `.slot-*` question that was considered and found unnecessary). **Never back-fill it and never renumber an existing `FQ`** — the owner may already be answering them. Cross-referencing across prefixes is fine; renumbering is not.

---

### 12. Output

In `primitive-reconciliation.md §7`:

1. **Header** — tree state (`HEAD`, branch, `git status --short`), the measurements table from §3 with method, and the definition of "page file" you used.
2. **The matrix** — one row per component file (102), each with: path, category (one of the eight), evidence (**rendered signature: ARIA roles, class families, node counts — never names**), the HFW counterpart, and the two qualification columns from §5.
3. **Classification counts** — by category, plus the number left **unclassified** and why. A classification you cannot evidence is `unknown`, not a guess.
4. **`## Divergences introduced by the fix pass`** — the 14-file analysis.
5. **`## For D3`** — every ambiguity, conflict, divergence and dead-component disposition, each as a question with two options and each option's consequence, ready for D3 to number `CQ-nn`.
6. **`## Not verifiable`** — with the closing artifact for each.

**Matching rule, stated once so nobody retries the failed method:** the set rewrites all copy, so literal-text matching returns **0 of 97**. Match on rendered signature only. Record that literal-text matching was tried and failed.

**Route `SCREEN_SPEC.md` §7-27 to D3, not to the matrix.** `/database/sources` is a live screen with **no `SCR-*` row** while `database-sources.html` **is** drawn — that is a registry/set bookkeeping gap, **not a parity defect**, and it is D3's question. Same for §7-25's four Snapshot routes (three GET screens: `Entry`, `Setup`, `Review`; `snapshot.store` is a POST and needs no row).

---

### 13. Stop conditions

- Read-only with respect to code, config, the plan, the map, the ADR and `SCREEN_SPEC.md`.
- Never rewrite anything in `DESIGN.md` outside an appended §3 addition.
- Do not run `npm run build`, `npm run test:browser`, or a suite. Static inspection only.
- Do not resolve a question the owner has not ruled on; prepare it.
- **Do not mark the plan's Phase 0 gate satisfied.** D2 produces the matrix; the gate is the owner's.
- Do not rename or renumber anything in §11's namespaces.


### Phase 1 — Foundation: the check harness and token parity

1. **Port the three static checks into `tools/`.** `framework-check` becomes `tools/ui-framework-check.mjs`, scanning the Vue tree the way it scans the HTML set: it reads `resources/css/app.css` and the component tree, and fails on (a) an arbitrary value outside the approved debt list, (b) a `dark:` utility, (c) a Card inside a Card, (d) a primitive used with no docstring.

   > **Amended 2026-10-22 (correction pass, D1 / authoritative A2, A5, A6, A26).** Four corrections to this step. The text above stands as written.
   >
   > **A26 — "three" is five.** The set carries **five** checks: `framework-check`, `link-check`, `render-check`, `data-check`, and **`framework-audit`**. The fifth is the one this plan most needs, because it is the only one that measures contrast, target size, reflow and focus in a rendered frame. It also carries a defect that must be fixed **at port time, as a condition of the port**: `framework-audit.mjs:31-34` calls `process.exit(0)` when Playwright is absent, so a verbatim copy **passes vacuously** on a host without a driver. The ported version must report `UNVERIFIED` and exit non-zero, or refuse to run — never "clean". (Also corrected in the Definition of done, which repeats the number.)
   >
   > **A5 — the rule set must be enumerable rows, and its relationship to the other gates stated.** Step (a)–(d) above is four rules; the primitive layer needs eleven. The check's rule set becomes:
   >
   > | # | rule |
   > |---|---|
   > | 1 | no literal colour outside `@theme static` |
   > | 2 | no literal `px`/`rem` length outside `@theme static` |
   > | 3 | no `border-radius` outside the declared scale (6/10/14/16/999, plus `50%` for a dot) |
   > | 4 | no `box-shadow` anywhere |
   > | 5 | no `transition` outside `150ms ease-out`, and none on layout properties |
   > | 6 | no `outline` wider than the base layer's `2px`/`2px` |
   > | 7 | every colour resolves through `var(--color-*)` |
   > | 8 | no arbitrary value outside the approved debt list |
   > | 9 | no `dark:` utility |
   > | 10 | no Card inside a Card |
   > | 11 | no primitive used without a docstring |
   >
   > **Relationship to the existing gates — this check is ADDITIONAL, never a replacement.** `DesignTokensTest` owns token resolution and contrast pairs; `UiHygieneTest` owns copy/identifier hygiene; the axe pass in the browser specs owns rendered accessibility. `tools/ui-framework-check.mjs` is a **source-level** check and cannot see a rendered frame, so it may not be cited as evidence for anything those three own. `tools/ui-lint.mjs` (5,172 B, 2 rules, 2-entry allowlist) is **absorbed** by the new check — its Card-in-Card and arbitrary-value rules become rows 10 and 8 — and is deleted when the new check goes green, not before.
   >
   > **A6 — a count is not a check; the debt list needs an anti-regression baseline.** Rule 8 permits a debt list, and a permitted list that nothing guards grows back. From Phase 1 onward the check must fail when the debt count **increases** against a committed baseline file, while the list is still non-empty; the baseline is regenerated only by a commit that also records the reason for the increase. When the list reaches zero the baseline file is deleted and rule 8 becomes absolute.
   >
   > **A2 (landing site) — `tools/ui-screenshot-diff.mjs` is built here, in step 1.** It renders an **application** route and writes/compares a snapshot under `.scratch-uma/<session>/snap/`. It is in Phase 1 because it needs no primitive to exist, and it targets the app because the set's `shell.css` and the app's `app.css` are separate files — a `file://` baseline of the set cannot observe an app-side change. Phase 7 keeps only the gallery-baseline discipline.
2. **Reconcile the token sets.** The app has 93 tokens, the HFW 65. The overlap is the contract; the 28 app-only tokens are either promoted into `DESIGN.md` §9 or named as app-internal. No token is deleted without a migration of its uses.

   > **Amended 2026-10-22 (frontend-architecture reconciliation pass, D1 / **M-12** (D1's own labels: A19–A20; supports authoritative A4, A5, A27):** the two totals are close, the derived **28 is not one of the numbers the tree yields**, and the sets are not in the same namespace — which is the fact that changes what Phase 1 step 2 has to do.
   >
   > - **A19 — the two token sets do not share a namespace, so "overlap" needs a normalisation step this step does not name.** `app.css` declares `--color-page`, `--color-rule`, `--color-ink`; `shell.css` declares `--page`, `--rule`, `--ink`. Comparing the raw sets for overlap returns **1** (`--shadow-overlay`), not 52. Normalising the app's `--color-` prefix away, measured on `15f7ee1b`: app **93** distinct, shell **64** distinct (54 in `:root`, 51 in the dark block), **52 shared names**, **12 shell-only**, **41 app-only**. So the phrase "the 28 app-only tokens" is a subtraction of the two headline totals (93 − 65), which is only valid if every name is shared; it is not. The actionable consequence: a primitive pasted from `shell.css` references names the app does not declare, and because an **undefined custom property inherits rather than errors**, the primitive renders in whatever colour its parent had instead of failing. Phase 2 must therefore re-spell every `var()` in a ported primitive, not copy it.
   > - **A19 (light-theme gap in the set).** Of the 52 shared names, **39 are byte-identical in light** and the remaining 13 are not all differences — **10 of them are `--tint-*` and `--line-*` declared by the app in both themes but by `shell.css` only in its dark block**. The concrete effect: `.band-*` (`background:var(--tint-X); border-color:var(--line-X)`) has **no tint at all in the set's light theme**, so all five stat bands render untinted in the theme that is the app's default. The app already holds all ten tokens, so the primitives are REUSE-once-re-spelled; the set is simply incomplete.
   > - **A19 (three non-differences, one real one).** `primary-action`/`on-primary-action` are literals in the set and `var()` aliases in the app: identical in light, but in **dark** the app's alias resolves to `#1A3FCC` while the set hardcodes `#2255FF`, so the set's dark primary fill is the light one. `shadow-overlay` differs only in notation (`0.18` vs `.18`). The **five dark `--line-*` values genuinely disagree** (app `#252C42` vs set `#3B4A6B` for speed, and so on) — the set's dark band borders are the older measurement.
   > - **A20 — `--rule-soft` is the one missing token that blocks the layer, and it fails the boundary contract.** The 12 shell-only names are `--rule-soft` plus six radius aliases (`--r-sm…--r-pill`, same values as `--radius-*`) and five layout constants (`--rail-w`, `--rail-w-collapsed`, `--shell-compact/standard/wide`, `--shell`). The radii are pure renames (**REUSE**, not new tokens). The layout constants belong to `AppLayout.vue` and are not primitives. `--rule-soft` is different: it is referenced **23 times** as the hairline of `.card`, `.tile`, `.tag`, `.type-chip`, `.warn`, `.info`, `.srow` and `.savebar`, and measured it is **1.37:1** against `panel` (light) and **1.34:1** against `panel` (dark) — far under the 3:1 that WCAG 1.4.11 asks of a component boundary, and inconsistent with the G-18 contract row `rule on page`, which the shipped `--color-rule` passes at **3.46:1** light / **3.88:1** dark. Promoting `--color-rule-soft` would therefore add a token that cannot join any 3:1 pair; re-spelling to `--color-rule` would clear it and needs no new token, at the cost of a harder border than the set draws. This is **FQ-01** and it gates Phase 2, because eight primitives cannot be ported until it is answered.
   >
   > **Unit note (added 2026-10-22, close-out pass / supports A16, A19).** Step 2's opening sentence states three counts without stating their unit, and that is how the earlier arithmetic failed. Every count about a stylesheet needs one of three units, and they are all true of the same file:
   >
   > | unit | app (`app.css`) | set (`shell.css`) |
   > |---|---|---|
   > | **distinct custom-property NAMES** | **93** = 81 `--color-*` + 12 non-colour | **64** = 54 in `:root` ∪ 51 in the dark block (41 in both) |
   > | **declaration SITES in the file** | **159** = 93 in `@theme static` + 66 dark redeclarations | — |
   > | **`--color-*` declarations, whole file** | **146** = 81 light + 65 dark | — |
   >
   > So "the app has 93 tokens" and "the app has 81 colour names" are both correct sentences about one file, differing by the 12 non-colour tokens; "159" and "146" are the same file at declaration-site grain. **And the set's total is 64, not 65** — 54 + 51 with 41 names present in both blocks.
   >
   > **Why the distinction is load-bearing rather than pedantic.** The derived "28 app-only tokens" is `93 − 65`, a subtraction of two *name* counts that is only valid if the two name-sets are the same set compared position by position. They are not: after normalising the app's `--color-` prefix away the real split is **52 shared / 12 set-only / 41 app-only**, and 41 ≠ 28. Mixing a name count with a declaration count would have produced a different wrong number again. **Rule for this document: any count of tokens states its unit**, and a subtraction is only made between counts of the same unit over the same name-space.
3. **Retire the debt list.** The 37 arbitrary-value lines and the `ui-lint.mjs` debt entries are each either converted to a token or primitive, or recorded as `ponytail:` simplifications with a ceiling. When the list is empty, the "debt, not a place to add" sentence in `AGENTS.md` §9 becomes obsolete and is corrected.

**Gate:** `npm run typecheck`; `vendor/bin/pint --test`; the new framework check runs clean on the un-migrated tree (it must pass before anything is rewritten, so the harness is proven independent of the migration).

### Phase 2 — The primitive layer

1. **Add the ~40 semantic primitives to `app.css`**, each copied from `shell.css` with its docstring and its measured geometry. This is the only phase that touches the stylesheet broadly.

   > **Amended 2026-10-22 (correction pass, D1 / authoritative A4, A18, and A4b at this site).** The step above says "copied", and copying is the one thing that must not happen. The text stands as written.
   >
   > **A4 — the primitive rule is promotion, not copy.** `shell.css` values are **source measurements**, not implementation. Concretely, for every ported primitive:
   > 1. its colour, radius, elevation and motion resolve to a token that already exists in `@theme static`, or to one the owner promotes (the seven `--color-stat-*` in FQ-05 are the only promotions currently proposed);
   > 2. its measured geometry (padding, size, measure) is expressed on the house scale, not transcribed as a literal;
   > 3. a value that contradicts a `DESIGN.md` ruling is a **conflict** and becomes an owner question — never a variant silently kept (`DESIGN.md` §2.1's scale, §2.3's border and elevation rules, §8's MOTION 2);
   > 4. a measured value that must not move is **pinned** the way `turn-chip` is pinned to `--radius-sm` — that is the only sanctioned exception, and it is named in `app.css` with its client frame.
   >
   > The mechanical reason rule 1 is not optional: `shell.css` references unprefixed names (`var(--panel)`) and the app declares `--color-panel`, so a pasted primitive resolves **nothing**, and an undefined custom property **inherits rather than errors** — the primitives would render in their parent's colour and look plausible. Only 1 of 52 names matches literally.
   >
   > **A18 — `DESIGN.md` §2.1 is the token table; §9 is where tokens live.** The Sources list and step 2 below both cite §9 for the token table. They are two different sections: **§2.1** carries the role table (light and dark value per role, with the G-18 pair), and **§9** states that tokens live in `app.css`'s `@theme static` block and why `static` is load-bearing. A reader sent to §9 for a value finds a location, not a value. Both citations should read "`DESIGN.md` §2.1 (values) and §9 (location)".
   >
   > **A4b (at this site) — the count is measured, at two levels.** "~40" is not a number the set yields. The measured figure is **21 portable primitive families: 4 reuse, 10 re-spell, 6 conflict, 1 drop**, plus **7 promoted tokens belonging to one of those families** (`.stat-*`). The buckets are exclusive at family level; the promotions are a value-level count and are not added to the family total. The **conflict bucket is deliberately visible** because it is what the FQ register is made of — a breakdown that drops it cannot be audited against FQ-01…FQ-05.
2. **Add `DESIGN.md` §2.x tokens the primitives need** (the `status-*`, `trust-*`, `mood-*` families) if any are missing from the 93.
3. **`DesignTokensTest` extension.** Each new token is asserted non-empty and resolved in both themes, the same way the existing tokens are — the HFW's own browser gate, applied to the app.

**Gate:** `DesignTokensTest` green; `npm run typecheck`; a visual snapshot diff of one migrated sample page proves the primitives render identically to the Tailwind originals.

> **Amended 2026-10-22 (frontend-architecture reconciliation pass, D1 / **M-13** (D1's own label: A1; supports authoritative A1):** this gate is incomplete, because "`DesignTokensTest` green" does not currently prove the thing Phase 2 depends on. The gate gains a stated condition and a conditional sequencing requirement.
>
> **The condition, which must be true before Phase 2 opens:** the D-288/G-18 block in `tests/Feature/DesignTokensTest.php` is **implemented and asserts the pair list over both themes** — every declared `--color-*` token resolves non-empty in light and dark, and every text/background pair meets its threshold (4.5:1 for text, 3:1 for a non-text boundary). A `markTestIncomplete` body satisfies "green" while measuring nothing, so a green run of that block is not evidence unless it asserts.
>
> **The sequencing requirement, stated conditionally because this document must be correct either way:** if a rewrite of that block has landed by the time Phase 0 runs, Phase 0 **consumes** it and the gate is met. If it has not landed — including the case where it exists only as another session's uncommitted work — Phase 0 must **sequence the implementation of that block before Phase 2 opens**. This document does not assert which case holds on any given tree; it asserts only what must be true at the gate. The reason it cannot be assumed: the block's state has changed more than once between 2026-10-04 and 2026-10-22, and `DESIGN.md` §2.1 and §10 carry two different accounts of it for exactly that reason.
>
> **Why Phase 2 is the phase that cannot proceed without it.** Phase 2 adds tokens whose only consumer is a hand-written `var()` inside a component-layer class, which Tailwind does not emit a property for on that class's behalf. Without the resolving-and-pair gate, a pruned token renders as an inherited colour and the primitive ships silently wrong — the D-288 failure the `static` qualifier exists to prevent.
>
> **Two neighbouring figures in the same phase, corrected once here and not repeated at each recurrence:** Phase 1 item 1 says "Port the **three** static checks"; the set carries **five** (A8). Phase 3's gate and Phase 4's gate say "the **77** browser specs stay green"; there are **76** (A2).

> **Superseded 2026-10-22 (agent-2 absorption pass, B1). Re-derived, not inherited: the browser-spec count is 77.** The sentence above says 76, following A2's `15f7ee1b` reading. Re-derived at `e3ac31bd` two ways that agree: `Get-ChildItem tests/browser -Filter *.spec.ts` = **77**, and `git ls-tree -r --name-only HEAD -- tests/browser` filtered to `*.spec.ts` = **77** tracked. The difference is this pass's own `tests/browser/sub-cell.spec.ts`, added by 2.0.1. So **77 is correct and 76 is superseded**, and the sibling sentence in the same note ("Phase 3's gate and Phase 4's gate say 77… there are 76") is inverted: the gates are right and the count is wrong. The same figure holds at `da15a5ba` and at `e3ac31bd`, because 2.0.2's commit `b6a7d0f0` corrected an existing spec rather than adding one. `### D2 brief` §3's 77 is the figure to carry forward.

> **Superseded 2026-10-22 (post-2.0.1 reconciliation / restates A1; gate verified live at `da15a5ba`).** The M-13 note above states A1's condition *conditionally* — "if a rewrite has landed… otherwise sequence it" — because the block's state was unknown when it was written. **It is now known, and the condition is satisfied by committed code.** Verified at the tagged release commit `da15a5ba`: `grep markTestIncomplete` over `tests/` returns **zero**; the browser block at `tests/Feature/DesignTokensTest.php:549-760` **measures**; `g18ContractPairs()` carries **31 pairs** plus **9** grade badges, re-asserted at `:571`; and the tripwire asserts `toHaveCount(81)` at `:271`.
>
> **So Phase 2's gate changes from a demand into a reference.** It must no longer *require that a gate be built*; it must **run the gate that exists**. Corrected gate wording, replacing the sentence above:
>
> > **Phase 2 gate.** The existing D-288/G-18 block in `tests/Feature/DesignTokensTest.php` is **run and green** at the phase's head commit (`php artisan test --filter='browser contrast and token resolution'`), and its output is attached. `npm run typecheck` clean. A visual diff of one migrated sample page against its pre-migration capture proves the primitives render identically.
>
> **The dependency, stated precisely.** The contrast gate is **not Phase 2 work and not a Phase 2 deliverable**. It is a **pre-existing precondition that Phase 2 consumes**, and its *run* is an **event the plan schedules**, not an artefact the plan produces. Consequences: (a) Phase 0 loses the "sequence the gate implementation" branch entirely — that branch is dead, and a Phase 0 that spends time on it is spending time on work already landed; (b) Phase 2's first action is a **gate run at the starting commit**, so any pair already failing is attributed to the tree Phase 2 inherited rather than to Phase 2's own changes; (c) Phase 2's exit gate is the **same command at the exit commit**, so the phase is bracketed by one measurement rather than judged by a new one.
>
> **Two known expected failures at that first run, and they are the point.** The gate will report `--rule-soft`-derived boundaries and the dark `#fff`-on-`--color-risk` badge as failing, because both describe primitives the plan has not ported yet and tokens the app does not yet carry. A Phase 2 opening run that is **green** is therefore itself a finding: it would mean the pair list does not cover the conflicts the map records.

> **The prediction above has fired, and it fired the way it allowed for — measured 2026-10-22 (agent-2 absorption pass; supersedes the "did not run" clause in the note above).** Three facts, all taken from the gate's own run rather than inferred.
>
> **(a) The gate ran.** The earlier account — "did not run in the sandbox: the sandbox denies `pest-plugin-browser`'s write into `vendor/`" — was true of an earlier session and is **superseded**. That write path is `vendor/pestphp/pest-plugin-browser/.temp/playwright-server.json`, hard-coded at `AlreadyStartedPlaywrightServer.php:121` with no relocation hook; in the run that produced this evidence the path was writable, so route (b) applied and the gate executed. Run at the 2.0.1 release commit `da15a5ba`, **3 cases, 184 assertions, exit 0, both themes, 0 failures**. The "ran" half is now established, not pending.
>
> **(b) It was green, so the escape clause fired.** The pair list does not cover the conflicts the map records. `grep` over `tests/Feature/DesignTokensTest.php` returns **0 occurrences** for each of `rule-soft`, `risk` (as a pair), `pick-line`, `ring`, and `stat-*` (as pairs). Zero of the two expected failures appeared — not because the design clears those bars, but because **the pairs are not tested**.
>
> **(c) The consequence for this plan's acceptance rule.** *"An FQ is answered when its named assertion passes"* is therefore **not satisfiable today** for FQ-04 at all (the pair does not exist), and only **partly** for FQ-01 (`rule on page` passes for `--color-rule` at 3.46:1 / 3.88:1; the failing `--rule-soft` boundary is invisible). This is precisely what **FQ-18** exists to rule on, and it is why FQ-18 gates Phase 2's opening rather than travelling inside it.

> **Phase 2's opening gate is satisfiable vacuously today — structural note, 2026-10-22 (agent-2 absorption pass).** The corrected Phase 2 gate above ("the block is run and green at the phase's head commit, output attached") **passes with the gate exactly as it stands**, because none of FQ-01's, FQ-02's, FQ-04's, FQ-05's or FQ-03's `box-shadow` criterion is represented in the pair list. So a green opening run says nothing about whether Phase 2's primitives implement FQ-01 through FQ-05; it says only that the 31 pairs and 9 badges the list *does* carry are still green. That is the argument for **FQ-18 as a Phase 2 precondition** rather than a same-phase deliverable: until the list is extended, the phase's own opening measurement cannot distinguish a correct implementation from an absent one.

> **Rule added 2026-10-22 (post-2.0.1 reconciliation): the FQ register is executable, and each ruling it asks for has a passing assertion as its acceptance criterion.**
>
> Each cause in `docs/proposals/primitive-reconciliation.md` §4 corresponds to a **named assertion failure** in the live gate, on a **named pair**. The owner's answer to each FQ is therefore a **proposition about the stylesheet**, and the gate going green on that pair is the **proof the proposition was right**. This converts five questions from judgement calls into measurements:
>
> | FQ | the answer is a proposition about… | the assertion that proves it | the pair | today |
> |---|---|---|---|---|
> | **FQ-01** | which token carries a card boundary | `rule on page` (3:1) once `--rule-soft` is gone or promoted | `rule on page` | passes **3.46:1 / 3.88:1** today for `--color-rule`; the *set's* soft hairline has no token and is not in the list, which is why the list cannot see it |
> | **FQ-02** | whether selection state may be a shadow | `pick-line on raised` (3:1) once the inset shadow is replaced | `pick-line on raised` | **not in the pair list** — a gap, not a failure |
> | **FQ-03** | whether a sticky bar may carry elevation | no contrast pair; the criterion is the **`box-shadow` grep in the ported framework check** | — | n/a (elevation is not a contrast fact) |
> | **FQ-04** | what ink a risk-filled badge carries in dark | the pair `on-risk on risk` (4.5:1), which does not exist until FQ-04 mints the token | `on-risk on risk` | **fails by construction** — `#fff` on dark `--color-risk` is **2.44:1** (computed) |
> | **FQ-05** | whether seven new stat colours are minted | the seven `on raised` rows (3:1 each) | `stat-* on raised` | not in the list; computed 4.24–6.23:1 once minted |
>
> **The rule, stated for the phases:** a question in this register is **answered** when its named assertion passes, not when the owner expresses satisfaction; and an answer that leaves its assertion red is **not an answer**. Where a question's criterion is not a contrast ratio (FQ-03), the question must name its own instrument — here, the ported check's `box-shadow` rule — and that instrument is then its acceptance criterion. **A question with no named assertion and no named instrument is a judgement, and must say so.**
>
> **The corollary for Phase 2's opening run:** the two expected failures named in the note above (FQ-01's boundary, FQ-04's dark badge) are the register's **first measurements**. They should be recorded as the baseline, so that a later green on either pair is attributable to a specific ruling rather than to general drift.

> **Sense-of-measurement note, added 2026-10-22 (post-2.0.1 reconciliation) — the plan is written to be correct under either answer.** Whether the gate resolves **computed styles in a rendered browser** (a rendered measurement) or reads the **stylesheet's declarations** and computes from them (a computed-from-declared measurement) is being established, and this plan does not assert which. That distinction is the one `DESIGN.md` §2.1 makes about its own table — "documented, not re-verified" — and the gate's `resolvedTokenScript()` reads `getComputedStyle` off `documentElement` after setting `data-theme`, which reads as the first sense while remaining a read of resolved *custom properties* rather than of painted pixels. **Either way the plan's obligations are unchanged:** the pair list and its thresholds are the contract (that is FQ-01…FQ-05's subject), and the gate is the instrument that applies it. What changes with the answer is only the **strength** of a green — a rendered measurement proves the shipped pixel; a declared-value computation proves the shipped declaration. Any ratio this plan or the map states stays **computed, not rendered**, until the stronger sense is confirmed and the gate's output is quoted for that pair.

> **The sense is now determined, not conditional — measured 2026-10-22 (agent-2 absorption pass; supersedes the "is being established" clause above).** The gate resolves **computed styles in a real browser**: `visit('/umamusume')` opens a Playwright page, `resolvedTokenScript()` sets `documentElement.dataset.theme`, reads `getComputedStyle(document.documentElement).getPropertyValue(name)`, routes the specified value through a probe `span`'s `color`, and reads it back with `getComputedStyle(probe).color`. That is the stronger sense: **a rendered measurement**. What stays weaker is narrower than before — the plan's own arithmetic ratios (**FQ-01's 1.37:1 and FQ-04's 2.44:1**) remain **computed, not rendered**, because their pairs are not in the list (A2(b) above). So the file now carries both statuses at once, and each is labelled: the **31 pairs + 9 badges are rendered-measured**, the five FQ causes' ratios are **computed from declared hex**.

> **Phase 2 gate — closed 2026-10-22.** The extended gate is green at head commit `ac18a9cd`: `php artisan test --filter='browser contrast'` passes, 3 cases, 245 assertions, 0 failures, both themes. The pair list carries 41 pairs (was 31) plus 9 grade badges; the nine `#FFFFFF`-fails assertions for grade fills run in light theme (D-2=A); `--color-on-risk` and seven `--color-stat-*` resolve in both themes; named-token coverage for `--color-risk`, `--color-pick-line`, `--color-ring` is in place. `npm run typecheck` clean. `node tools/ui-framework-check.mjs` carries no new findings from the ported primitives (pre-existing findings in `@layer base` and `turn-chip` are unchanged). Six CONFLICT-family `@utility` blocks ported into `app.css` with FQ-01..FQ-05 rulings applied; eleven RE-SPELL and three REUSE families ported; one DROP (`.skel`) omitted per `AGENTS.md:569`. Phase 3 opens.

### Phase 3 — Component migration (strangler)

1. **Order by dependency, leaf-first.** `Form/*`, `ui/*`, and the leaf chips (`RarityChip`, `AptitudeBadge`, `MoodPill`, `SparkChip`, `ProvenanceMark`, `AbsenceValue`, `SubCell`, `KpiCard`) migrate first; the pages last.
2. **Each migration is one commit** with the before/after snapshot and the browser spec that pinned the behaviour. Nothing about props, emits, or markup semantics changes — only the class spell.
3. **Run the component's own browser spec** after each migration (Level 1), and the related suite at the end of each folder (Level 2). The full suite is a checkpoint, not the feedback loop (AGENTS.md §9).

**Gate:** the 77 browser specs stay green throughout; no Feature test is edited to accommodate a visual change.

> **Phase 3 gate — closed 2026-10-23.** 43 component re-spell commits landed across all eleven folders (`ui/`, `Form/`, `support/`, `catalog/`, `database/`, `legacy/`, `review/`, `skills/`, `scenario/`, `career/`, root). Each commit replaces Tailwind utility class combinations that replicated a named primitive's surface with the primitive class (`.card`, `.pill`, `.pill-neutral`, `.note`, `.mood`). Components with no primitive-matching patterns — form controls, layout divs, SVG-only components — were read and confirmed clean. `node tools/ui-framework-check.mjs` exits 0 with no new findings from the re-spell. No Feature test was edited for visual accommodation. Phase 4 opens.

> **Phase 3 progress — 2026-10-23 (supersedes the closure claim above; the note above stands as written).** Measured against the tree: **57 of 95 complete**, not a closed phase. The total fell 97 → 95 and the complete count rose 50 → 57 by two moves: **two components deleted** (`UraDuelPanel`, `UraMilestoneTracker`) at `681e8b70` under FQ-12, after their vocabulary moved to `config/scenarios.php` (`e7621467`) and the component guards were narrowed to the surviving surfaces (`f69cf1c1`); and **seven `Form/*.vue` accepted rather than re-spelled** under CQ-01=C (`21018672`, Principle 2's amendment). **Remaining 38**: `scenario/` 6, `ui/` 10, `review/` 2, `career/` 6, `root/` 11, `support/` 1, `catalog/` 1, `database/` 1. Baseline non-allowlisted **24 → 23** (`b098d584`, `dce69f01`); CQ-03's second reduction did not land because both `SparkChip` components are live — ten production importers — so the deletion ruling does not apply.

> **Corrected 2026-10-23 (CQ-03 reversed).** Both `components/SparkChip.vue` and `components/legacy/SparkChip.vue` are **live**, imported by ten production files (`catalog/TraineeCatalog`, `skills/SkillCatalog`, `support/SupportCardCatalog`, `pages/Catalog/Show`, `pages/Database/ShopItems`, `pages/Database/Sparks`, `legacy/AncestryNode`, `pages/Career/InheritanceEvent`, `pages/Career/SaveVeteran`, `pages/Legacy/Builder`). They serve different purposes — the root takes `label/variant/size` for catalogue/reference screens, the legacy one takes `kindLabel/target/stars` for Spark-heavy screens — and share only a filename. **Keep both, no rename.** The `legacy/` prefix distinguishes location; the APIs distinguish purpose; the name collision is a known deliberate condition. A prior ruling to delete both was based on a false-negative reference check and is retracted.

### Phase 7 — Agent ergonomics (the transition tooling; runs alongside Phase 3)

The migration is executed largely by coding agents, and five optimisations were proposed for that. Each was assessed against what the repository already has and what it is allowed to add. Two were accepted as proposed, one accepted in changed form, one accepted and redirected, and one rejected on both dependency cost and a gate conflict.

| # | Proposal | Verdict | The reasoning |
|---|---|---|---|
| 1 | **Playwright visual diff** (`toHaveScreenshot()`) before/after each component migration | **Accepted, redirected to the static set** | `@playwright/test` is already installed and no `toHaveScreenshot` exists today, so this adds a capability rather than a package. But the baseline should be the **HFW page**, not the running app: the app needs Laravel, a scratch database and a dev server to render one chip, while the HFW renders the same primitive from `file://` in 200 ms. Diffing `file://` before/after a primitive change gives the agent a fast, deterministic signal, and diffing the app page stays the Level 2 check it already is. |
| 2 | **AST codemod** (`@vue/compiler-sfc`) to rewrite utility chains into named primitives | **Accepted, strictly scoped** | `@vue/compiler-sfc` already ships via `@vitejs/plugin-vue`. The codemod must rewrite only **mechanical, unambiguous** mappings (a class set that equals exactly one primitive) and must **refuse** anything else, printing the file and line for the agent to handle by hand. A codemod that guesses is the failure mode the plan exists to prevent: it would violate Phase 2's rule that a component renders only from a primitive that has a docstring. |
| 3 | **Husky + lint-staged** pre-commit hooks running the checks | **Rejected** | Neither package is installed, so this breaks the no-new-dependency rule for a convenience. Worse, a pre-commit hook is wrong for *this* repository: several sessions share one worktree (KI-70), so a hook fires on a peer's uncommitted files, and `pint --dirty` already demonstrated the damage a whole-worktree gate does here (KI-75). The checks stay where AGENTS.md §9 puts them — in the hand-off sequence, run against the paths the change actually touches. |
| 4 | **Storybook / Vue-Styleguidist** playground for isolated components | **Accepted in principle, redirected** | Both are large dependency additions. The repository already has the cheaper version of the same thing: the HFW set *is* an isolated component gallery. `framework-check` and `render-check` render the primitives with no Laravel, no database and no Inertia hydration, and `data-check` proves the data shape they bind. The gap Storybook would fill — edge-case props (nulls, extremes) — is instead filled by extending the HFW's own pages with those states, where they are also law-checked. |
| 5 | **`AGENTS.md` design-system ruleblock** | **Accepted** | Zero cost and the highest yield of the five: it changes what an agent does *before* it writes a class, rather than catching it afterwards. The ruleblock is written in Phase 7 step 1 and removed at Phase 6 exit, because a migration-scoped rule that outlives its migration becomes stale instruction. |

**Phase 7 steps.** (This is the plan's only out-of-sequence phase: it is numbered 7 for reference but executes beside Phase 3, because the migration is where the tooling helps.)

1. **Add the migration ruleblock to `AGENTS.md` §13** (the UI section), scoped by a dated banner that says it applies while the migration is open. Two rules, plus the escape hatch:

   - *"When you spell a component, use the primitive that already exists. Look it up in `DESIGN.md` §3, then `resources/css/app.css`, then `docs/design-research/frontend-v2-wireframes/assets/shell.css`. Never invent a class, an arbitrary value, or a `dark:` utility when a named primitive exists."*
   - *"A component migration changes the class spell and nothing else. Do not change props, emits, slot names, `aria-*` attributes, or the markup tree in the same commit; those belong to Phase 4 and carry their own spec."*
   - The escape hatch: a `ponytail:` comment naming the ceiling is the only sanctioned way to leave a utility in place, and `tools/ui-framework-check.mjs` counts them.

2. **Ship `tools/ui-screenshot-diff.mjs`.** It renders a named HFW page (or a named component's gallery entry) at a fixed viewport, writes or compares a snapshot under a session-private `.scratch-uma/<session>/snap/` directory, and exits non-zero on a pixel delta above the configured threshold. Baselines are committed only for the **primitive gallery**, never per app page: per-page baselines would rot with the data.

3. **Ship `tools/ui-codemod.mjs`.** It parses each `.vue` file with `@vue/compiler-sfc`, reads the `class` attributes, and rewrites only exact-set matches against a declarative map (`{ 'bg-panel border border-rule rounded-md p-4': 'card' }`). It prints every refusal with its file, line and class set, writes no file it cannot fully rewrite, and is run with `--dry-run` first. The map is data, committed beside the script, so the agent extends it rather than editing code.

4. **Extend two HFW pages with edge-case states** instead of adding Storybook: `catalog-detail.html` gains a null-profile form and `support-card-detail.html` gains a card whose effect rows are all absent at the top anchor. Both enter `framework-check` like any other content, so the null-state rendering is law-checked rather than merely visible.

**Phase 7 gate.** `npm run typecheck`; `node tools/ui-framework-check.mjs` clean on the migrated tree; `node tools/ui-screenshot-diff.mjs --check primitives` green against the committed baseline; `node tools/ui-codemod.mjs --dry-run` reports zero auto-applicable rewrites remaining; and the `AGENTS.md` ruleblock present with its dated banner.

> **Amended 2026-10-22 (correction pass, D1 / authoritative A2, A3, A19, A22, A23).** Four corrections and one move. The text above stands as written.
>
> **A2 — the screenshot diff moves to Phase 1 and points at the application.** The preferred remedy is taken, and the reason is structural rather than stylistic: the set's `shell.css` and the app's `app.css` are **separate files with separate token namespaces** (`--panel` vs `--color-panel`), so a `file://` baseline of the HFW page **cannot observe an app-side primitive change** — it would go green while the app regressed. The tool therefore:
> - is built in **Phase 1 step 1** (it needs no primitive to exist to render a page), and
> - renders an **app** route through the running app, not a set page through `file://`.
>
> Consequence for this phase: step 2 below is superseded as a *shipping* instruction — the tool already exists by Phase 7 — and what remains here is the **baseline discipline** (per-primitive baselines only, never per-page; committed baselines only for the primitive gallery). The forward edge is gone: Phase 1 owns the tool, Phase 7 owns the gallery baselines, and neither depends on the other's completion.
>
> **A3 — the gate above is split in two.** A single gate cannot tell "the harness works before any rewrite" from "the migration finished":
>
> - **Phase 7 entry gate** (must hold *before* Phase 3 begins): `node tools/ui-framework-check.mjs` clean on the **un-migrated** tree; the `AGENTS.md` §13 ruleblock present with its dated banner; `npm run typecheck` clean.
> - **Phase 7 exit gate** (holds at the *end* of the migration): `node tools/ui-codemod.mjs --dry-run` reports zero auto-applicable rewrites remaining; the primitive-gallery baselines green; the ruleblock's **removal** scheduled in Phase 6 with its own date.
>
> **A19 — the ruleblock expires by date as well as by gate.** The repository has direct precedent for migration-scoped guidance outliving its migration: `DESIGN.md` §11 records four open questions that were closed in code while the question list still read as open, and `AGENTS.md` §9's "51 single-file components" snapshot is the same failure. The ruleblock therefore carries **both** an exit criterion (removed at Phase 6) **and** a hard re-validation date — **2026-12-31** — beyond which it must be re-read against the tree and either re-signed or removed, whichever phase has been reached.
>
> **A22 — the codemod's dependency is transitive, and that is not the same as available.** `@vue/compiler-sfc` is present in `node_modules` only as a transitive of `@vitejs/plugin-vue`; it appears in **neither** `dependencies` nor `devDependencies` of `package.json`. Item 2's sentence "already ships via `@vitejs/plugin-vue`" is therefore true of the installed tree and false of the manifest. Two lawful resolutions, and this plan takes neither without a ruling: **promote it to a direct dependency** (which the non-goals forbid without owner approval — Phase 0 non-goal "No new dependency"), or **name the pin** (`@vitejs/plugin-vue@^6.0.9` carries it) and resolve it through the plugin's re-export. See **FQ-14**.
>
> **A23 — the codemod's premise, stated as a rate rather than an assumption.** The tool rewrites only exact-set matches, and the app's class strings routinely carry variants — `md:`, `hover:`, `focus-visible:`, `group-*`, `aria-*`. A plain-CSS primitive cannot take a variant, so a class string of `md:grid-cols-2 hover:bg-raised rounded-md border border-rule` matches **no** single primitive and must be refused. The expected **auto-apply rate is therefore low by design** — the tool is a refusal generator that occasionally applies, not a bulk rewriter; Phase 7's exit gate measures "zero auto-applicable rewrites remaining", which can be satisfied at a low rate, and the plan must not read that gate as "most components were codemodded".

**Why these are not new dependencies.** Item 1 and 4 use `@playwright/test` and its built-in snapshot API. Item 2 uses `@vue/compiler-sfc`, already present as a transitive of `@vitejs/plugin-vue` (verified in `node_modules`). No item needs a package that is not already installed, which is why the two that did (Husky, Storybook) were rejected rather than deferred.

> **Amended 2026-10-22 (frontend-architecture reconciliation pass, D1 / **M-14** (D1's own label: A18; supports authoritative A22):** the claim above is true of what is *installed* and false of what is *declared*, and the ported checks carry two defects a verbatim copy would import. Measured on `15f7ee1b`:
>
> - **`@vue/compiler-sfc` is not in `package.json` at all** — not in `dependencies` and not in `devDependencies`. It is present in `node_modules` only as a transitive of `@vitejs/plugin-vue`, which is what the sentence above says and is correct. What it does not say is that a script calling `@vue/compiler-sfc` **directly** depends on a package this repository does not declare, so Phase 7 item 3 either declares it (a new declared dependency, needing approval under `ADR-0028` Non-licence 3) or resolves it through the plugin's re-export. `npm run typecheck` would not catch the difference; an install that hoists differently would.
> - **`@playwright/test` is installed (`^1.63.0`), and `toHaveScreenshot` appears 0 times** — so item 1's "no `toHaveScreenshot` exists today" is confirmed, and the capability is genuinely additive.
> - **None of the three Phase 7 artefacts exists yet:** `tools/ui-screenshot-diff.mjs`, `tools/ui-codemod.mjs`, and the `AGENTS.md` §13 ruleblock are all absent from `tools/` and from `AGENTS.md:561`. That is consistent with an unstarted phase, and is recorded so the amendment pass does not read the "Accepted" verdicts as "landed".
> - **A port hazard, in the check Phase 1 ports first.** `framework-check.mjs:198` does not merely *permit* motion — it **fails when `.app` lacks `transition:grid-template-columns`**, and separately fails if `.rail` animates its own width (`:204`). A verbatim port of that rule into `tools/ui-framework-check.mjs` would therefore **require** the very transition MOTION 2 does not sanction (A10), so Phase 1 step 1's "(c) a Card inside a Card, (d) a primitive used with no docstring" list needs a fifth decision: which of the set's motion assertions survive translation. That is **FQ-07**.

### Phase 4 — Close the parity gaps

With the primitive layer in place, build the eight absent components and **all six undrawn screens** (owner ruling 4), in the order of their classification:

1. **Restore `FormTabs`** as the link-tab control — addressable, reload-surviving, `aria-current="page"` links carrying `?form=<id>` — and add the HFW's `FormDetail` block beside it (owner ruling 2). The HFW's flat costume table is superseded: `catalog-detail.html` gains the link-tab strip and keeps the table as the detail body.
2. **Promote the flash banner** to a shell primitive (`.flash` in `app.css`, `role="status"` strip with a sibling dismiss), and mount it on the writing pages; the HFW's `preferences.html` pattern is the reference.
3. **Draw all six screens** — three registry screens (`Runs/Create` SCR-RUN-002, `SaveVeteran` SCR-VET-003, `RacePlanner` SCR-CAR-023) and three Snapshot screens (`Entry`, `Setup`, `Review`) — and add each to the four checks. This raises the set from 49 to 55 pages and makes the registry claim true: 50 of 50 plus `help`.
4. **Make `review-detail` a route** (owner ruling 3): one controller action, one page, the queue linking to it, the inline detail in `Review/Index.vue` removed, and a `SCREEN_SPEC.md` row for the new screen. This is the plan's only behaviour change.
5. **Remove the five dead components** (`UraDuelPanel`, `UraMilestoneTracker`, `Checkbox`, `Combobox`, `SegmentedControl`) and correct the companion set's pointer to "the Happy Meek duel panel".

**Gate:** the HFW's `framework-check` / `link-check` / `render-check` run clean on the now-complete 55-page set; the parity matrix in `DESIGN.md` §3 shows zero `absent` rows; the `review-detail` route has its own browser spec and its `SCREEN_SPEC.md` row; the 77 existing browser specs stay green.

> **Phase 4a gate — closed 2026-10-23.** All five set checks clean at **56 pages**: `framework-check` (56 pages, 0 findings), `link-check` (1,547 references, no broken references), `render-check` (56 pages rendered, 0 findings), `data-check` (67 trainees, 106 forms, 559 support cards, 35 effects, 1,910 skills), `framework-audit` (56 pages × 2 themes, 0 findings). Two UI-1 heading-ladder findings fixed at `9d4c91be` (`career-race-planner.html`) and `0f2524a1` (`catalog-detail.html`); the second entered with `28692b4e`, so it is a Phase 4a regression rather than pre-existing debt. The set grew 49 → 56 (seven pages, not the eight A7 names: `run-detail` is out of scope under FQ-17).

> **Amended 2026-10-22 (correction pass, D1 / authoritative A7, A8, A9, A10, A12 at this site, A17, A21).** Phase 4 as written is one phase with one gate doing two different jobs, one of them held by the owner. It splits, and five of its instructions are restated. The text above stands as written.
>
> **A7 — the split. A phase that draws static pages and edits the app cannot have one gate.** Phase 4 becomes two sub-phases:
>
> - **Phase 4a — set-work.** Draw the six screens into the HFW (`Runs/Create`, `SaveVeteran`, `RacePlanner`, Snapshot `Entry`/`Setup`/`Review`), restore the link-tab strip and add `FormDetail` on `catalog-detail.html`. **Gate:** `framework-check`, `link-check`, `render-check`, `data-check` and `framework-audit` all clean on the enlarged set, with every new page entered on the commit that adds it.
> - **Phase 4b — app-work.** Promote the flash banner out of `AppLayout.vue`; make `review-detail` a route and remove the queue's inline detail; fix the companion set's "Happy Meek duel panel" pointer. **Gate:** `npm run typecheck`; the app's own browser specs green; the new route's spec proves one rendering and no second.
>
> **A12 at this site — the matrix is not in `DESIGN.md` §3.** The Phase 4 gate sentence above must read: *"the parity matrix at `primitive-reconciliation.md §7` shows zero unexplained `absent` rows, and `DESIGN.md` §3 cites it"*. Same correction applies to the Phase 0 gate and to map row 0 (both noted at their own sites).
>
> **A10 — the dead-component disposition leaves this phase body.** Step 5 above still says "Remove the five dead components (…)". It is **withdrawn from the phase**: D1 measured that all five are referenced by a Feature test or a browser fixture, so "referenced by nothing" is false, and deletion would be a red suite rather than a cleanup. The disposition is the owner's, per `SCREEN_SPEC.md` §7-13's precedent (per component: mount or delete, decided, not assumed) — see **FQ-12**. Nothing is deleted until that is answered.
>
> **A8 — `review-detail`'s cost, stated.** `SCREEN_SPEC.md` §4.5 currently says *"resolve controls inline per card (no detail page)"* and pins **per-card stacked errors** (`:1152`). Extracting a page therefore moves: the resolve form (`CandidateForm`, today inside each row at `Review/Index.vue:213` and `:253`), the per-card stacked error list, the first-row accent (`:accented="index === 0"`, `:216`), and the collapsed-row disclosure that keeps a card one line (`:251-258`). `ReviewFormAccessibilityTest` currently asserts only server-side prop delivery and would need re-scoping; the accessible-name assertions live in `tests/browser/review.spec.ts`. **The `SCREEN_SPEC.md` amendment this requires is recorded as an owner question, not made here** — **FQ-10**. Do not edit `SCREEN_SPEC.md` from this plan.
>
> **A9 — the `FormTabs` specification, which ruling 2 leaves open.** Three things must be decided before it is built: (a) which element the strip controls — today it is a `<nav>` of `?form=<id>` links with `aria-current="page"` (`FormTabs.vue:32-47`), which is a *location* control, not a tablist; (b) what the costume table renders when one form is active — today it is one flat table of all forms (`catalog-detail.html:123-128`), so an active-form strip beside an all-forms table renders the active form twice; (c) whether `render-check`'s baseline for that page changes. The duplication in (b) is the one-fact-one-rendering concern, raised as **FQ-11**.
>
> **A17 — three Snapshot screens have no `SCREEN_SPEC.md` row.** Phase 4 draws them, but only `review-detail` was given a row, which re-creates the omission `SCREEN_SPEC.md` §7-25 records. Drawing a screen and filing its row are one commit; a screen drawn without a row leaves §7-25 open a second time.
>
> **Strengthened 2026-10-22 (close-out pass, D1 / completes A17; item 2.2).** The A17 paragraph above states the rule but leaves step 3's instruction under-specified, so this records exactly what Phase 4 step 3 must carry. **Step 3 draws six screens and authors four documents in one commit:** for each of `Snapshot/Entry.vue`, `Snapshot/Setup.vue` and `Snapshot/Review.vue`, a `SCREEN_SPEC.md` registry row (the `SCR-CAR-0nn` numbers are the spec owner's to assign, not this plan's) **and** the §4 section that row must point at, because every other registry row in that file has one and a row without a section breaks the file's own convention. This is the substance of `SCREEN_SPEC.md` §7-25, which records that the four Snapshot routes appear in no §3 row and no §4 section and that "this is an omission rather than a deliberate exclusion". Drawing the pages without the rows would record that omission a second time. **The rows and the pages land together** — a page without its row leaves the registry claiming a screen set that excludes three screens the app ships. Whether the owner wants the three §4 sections authored at all, or rows only with the sections deferred under a dated §7-25 note, is **FQ-16**; the default until answered is that they land together, because that is what closes §7-25 rather than re-opening it.
>
> **A21 — `review-detail`'s blast radius is a hard gate, not a risk row.** The risk table's mitigation becomes an exit condition: **one route, one controller action, one page, one browser spec, one `SCREEN_SPEC.md` row, and the queue's inline detail removed in the same commit** — verified by the spec asserting the detail exists exactly once in the rendered tree.

### Phase 5 — Freeze the catalogue

> **Site note 2026-10-22 (close-out pass, D1 / A29 co-location).** A concurrent session rewrote **this heading** in place. It originally read *"### Phase 5 — The mock catalogue graduates to the app"*; the phase body below was rewritten at the same time from a promotion plan into a freeze plan. The retitle follows owner ruling 5, which reversed promotion in favour of a frozen dated snapshot, so it is substantively correct. **No ruling on the rewrite is recorded, and D1 has not restored the original.** **Full record: `### Owner rulings (2026-10-22)` in Part II**, entry 4. **Sanctioned 2026-10-22 (bounded); see `### Owner rulings (2026-10-22)` above for the citation and ADR-0028 for the authority.**

> **Verified correct 2026-10-22 (close-out pass / T4.2 — a verifiable correction, not an owner decision).** The retitle is right, and it is an **implementation of owner ruling 5**, not a stylistic preference: ruling 5 is "**keep it as a frozen dated snapshot**", which reversed the earlier promotion plan. The phase body below is a freeze plan — date-stamp the dataset, keep `data-check.mjs` as its invariant test, explicitly *do not* point `build-data.php` at the live pipeline, and state the ageing rule — so "Freeze the catalogue" names what the phase does, while "graduates to the app" named the plan that ruling 5 withdrew. The heading is therefore a consequence of a ruling already on record, which is why it needs no FQ entry of its own. Verified against the plan's own Owner-rulings row 5.

Owner ruling 5: the dataset is a **frozen dated snapshot**, not a second source of truth.

1. **Date-stamp it.** `assets/data/README.md` and each generated file's header carry the capture date; the set's README states that the numbers are a dated observation and not current.
2. **Keep `data-check.mjs`** as the catalogue's invariant test. Its counts and sums are checks the app does not run, and they are cheap to keep.
3. **Do not point `build-data.php` at the live pipeline.** Regeneration happens only when the set is revised, and the header date moves with it.
4. **State the ageing rule** in the set's README: a reader must not quote a catalogue figure as current without re-deriving it, the same rule `coverage.md` already carries for host-local figures.

**Gate:** the captured date is present in all five locations; `node data-check.mjs` is green; no page in the set implies its figures are live.

> **Amended 2026-10-22 (correction pass, D1 / authoritative A11, A13, A14, A15).** Four corrections. The gate above stands as written.
>
> **A11 — the 71-vs-67 difference is a test artifact, not a product defect, and it is not a seeder.** Measured on `15f7ee1b`: the four extra trainees are created by `tests/Feature/CatalogRosterTreeTest.php:212` (`Form Counted N`) and `tests/Feature/CareerTraineeSelectTest.php:227` (`Form Counted 1`), and the twelve `[Sample Form]` cards by `database/factories/CharacterCardFactory.php:27`. They are **factories invoked from Feature tests that run against the dev database**, not seeder rows — so `migrate:fresh --seed` alone does not reproduce them, and "name the seeder" has no correct answer: there isn't one. Two consequences: a production database reads **67**, which is the correct figure; and **freezing the catalogue does not close this** — the contamination recurs on the next Feature run against the dev database. Closing it is a test-isolation fix (point the suite at a scratch database, which `tests/browser/global-setup.ts` already does for the browser suite), and that is not this plan's scope.
>
> **A13 — provenance is the date PLUS the generator's source identity, and "frozen" is narrower than it reads.** The gate above requires the capture date in five locations, which measured **0 of 5 today**. A date is **necessary and not sufficient**: `build-data.php` currently records no commit, no seed hash and no generated-at stamp, so a catalogue regenerated tomorrow is indistinguishable from this one and the date would attest only the day someone ran it, not what they ran it against. The gate therefore also requires the generator to write (a) the git commit its inputs were read at and (b) the seed identity — the committed `database/seeders/data/*.json` filenames are already content-hashed, so their names are the identity. Second correction: **"frozen" applies to `assets/data/`, not to the pages.** Phases 4a and 7 both edit set pages and the primitive gallery; what is frozen is the dataset, so a page may change while its figures do not, and no phase may treat "frozen" as a read-only constraint on the HTML.
>
> **A14 — the lore gate's coverage of the set, measured.** `composer lore` (`tools/lore.php`, docs mode) runs `git grep` over **all tracked files** with only `vendor/` and `node_modules/` excluded — so `docs/design-research/frontend-v2-wireframes/` **is** in its scan path today, because the set is tracked. `composer lore-code` is the one that does not reach it: it scans `app/**`, `config/**`, `resources/**`, `routes/**`, `database/**`, `tests/**`, `lang/**`, and the set is in none of them. So the set has one gate and lacks the other, which is the asymmetry that matters — the set carries real trainee, card and skill names, and any **new** page must enter `composer lore` on the commit that adds it (it does so automatically by being tracked; the obligation is not to add it untracked and later).
>
> **A15 — the coverage claim, with exact wording, and `help`'s status.** Phase 4 step 3's claim "makes the registry claim true: 50 of 50 plus `help`" is arithmetically wrong twice: 50 + `help` is **51 screens**, and Phase 4a raises the set to **55 pages**, not 51. The corrected sentence for the set's README and for Phase 6 step 4 is: *"**55 pages: 50 registry screens, 3 error documents, `index`, and `help`.** Of the 50 registry screens all 50 are drawn; `help` is drawn but has no route and no registry row."* Whether `help` should get a route and a `SCR-*` row is an owner question — **FQ-13** — because `routes/web.php` has no `help` route and `SCREEN_SPEC.md` does not contain the word.

> **Superseded 2026-10-22 (post-2.0.1 reconciliation) — the quoted wording above is false on both counts, and the registry is short by four rows.** That paragraph was written before `SCREEN_SPEC.md` §7-27 was filed. §7-27 records that **`/database/sources` is a live screen with no `SCR-*` row**, in the same class as §7-25's Snapshot routes. The quoted sentence must not be used. Measured on `da15a5ba`:
>
> | fact | measured | the paragraph above said |
> |---|---|---|
> | HFW pages today | **49** | (55, after Phase 4) |
> | `SCREEN_SPEC.md` registry rows | **50** (still) | 50 |
> | registry rows **with** an HFW page | **45** | "all 50 are drawn" |
> | registry rows **undrawn** | **5** | 0 |
> | **live screens with no registry row** | **4** | 0 |
>
> **The five undrawn rows, identifiable by filename** (there is no page↔row map in the set, so this is the floor): `SCR-CAR-001` Dashboard (**no `dashboard.html` exists**), `SCR-RUN-002` Runs/Create, `SCR-RUN-003` Run detail, `SCR-VET-003` SaveVeteran, `SCR-CAR-023` RacePlanner. **The inherited "three registry screens are undrawn" was itself wrong**: it named Create, SaveVeteran and RacePlanner and missed Dashboard and Run detail.
>
> **The four missing rows:** the three Snapshot GET screens (`Entry`, `Setup`, `Review`) plus `Database/Sources`. §7-25 records *four* Snapshot **routes**, but `career.snapshot.store` is a POST and needs no row, so the row shortfall is **4, not 5** — the count that matters is screens, not routes. That correction is worth stating because "at least five" double-counts a write route.
>
> **`Database/Sources` needs a row and NOT a page.** `database-sources.html` **already exists** in the set (18,432 bytes) — the page was drawn and the registry row was never filed, which is the exact inverse of the Snapshot screens (rows missing *and* pages missing). So Phase 4's page arithmetic does **not** grow for Sources: it is a one-row, zero-page correction, and it is **derivable rather than an owner choice** — §1 puts every `routes/web.php` screen in `SCREEN_SPEC.md`'s scope, so the row is owed. Whether it also needs a §4 section is the same class of question as the Snapshot screens, and is carried by **FQ-16** rather than a new FQ.
>
> **Reconciled Phase 4 arithmetic.** Step 3's "raises the set from 49 to 55" is **correct as a page count** (49 + 6) and stays. What changes is the *composition* of the six and the row bookkeeping:
>
> - Pages: **49 → 55**, unchanged.
> - Rows: **50 → 53** (+3 Snapshot). `Database/Sources` makes **54** if filed in the same pass, and its page already exists, so no page count moves.
> - Rows undrawn after Phase 4a: **2, not 0.** The five undrawn rows are Dashboard, Create, Run detail, SaveVeteran, RacePlanner; Phase 4a draws three of them (Create, SaveVeteran, RacePlanner), leaving **Dashboard and Run detail**. So the drawn count goes **45 → 48 of 50**, and step 3's "all 50 are drawn" is true only if Dashboard and Run detail are also drawn — which no phase currently does.
>
> **Consequence for the README claim and Phase 6 step 4.** The truthful sentence is now: *"**55 pages: 50 registry screens, 3 error documents, `index`, and `help`.** Of the 50 registry screens, 48 have a page; `SCR-CAR-001` Dashboard and `SCR-RUN-003` Run detail are undrawn. The set carries one page with no registry row (`database-sources.html`) and `help` with neither route nor row."* Drawing Dashboard and Run detail, or filing them as deliberately out of scope, is a **set-revision decision for the owner** — it is raised as **FQ-17**, because it is a scope question the set's own README claim depends on and this plan may not decide it.

### Phase 6 — Verification, versioning, and done

1. **Full Level 3 sweep** on an isolated worktree at the release commit (AGENTS.md §9 test dispatch): the hand-off sequence — targeted tests, `pint --test`, PHPStan level 6, `npm run typecheck`, `npm run lint:ui`, the lore gates, `composer audit` / `npm audit --omit=dev`.
2. **`CHANGELOG.md`** entry by the Docs Writer; **`VERSION.md`** bump by the Architect per ADR-0019 at phase exit.
3. **`AGENTS.md` §9 corrections**: the "debt, not a place to add" sentence and the "51 single-file components" snapshot both go stale and are corrected with dated notes.
4. **`SCREEN_SPEC.md` row** for the `review-detail` screen, and the set README's coverage claim corrected to 50 of 50 plus `help` (owner rulings 3 and 4).

**Definition of done.** The token layer is one source of truth; every component renders from named primitives; the three static checks run on the Vue tree and fail on violation; the parity matrix has no unexplained `absent` row; the dead code is gone; the six screens are drawn and checked; the catalogue is frozen and date-stamped; **the one sanctioned behaviour change (`review-detail`) ships with its own route, browser spec and `SCREEN_SPEC.md` row, and no other product behaviour changed**; and every gate in the hand-off sequence ran with its output attached.

> **Corrected 2026-10-22 (close-out pass, D1 / authoritative A30).** Step 2 above says the `VERSION.md` bump happens "by the Architect **per ADR-0019**", and that citation does not carry the authority it is written to carry.
>
> **ADR-0019 is `Proposed`, so it is advisory and does not bind.** Its own status line reads `Status: **Proposed** (drafted 2026-10-04, no owner ruling recorded)`, and its contract block says `decision: proposed`. `AGENTS.md` §2 puts binding decisions at "`docs/adr/0001`-`0025` … Accepted ADRs bind; `Proposed` ones do not". A phase therefore cannot be gated on compliance with it, and naming it as the authority for a release label is citing a document that has declined to rule.
>
> **What Phase 6's labelling authority actually is: nothing yet named.** This note does not appoint one — that is the owner's, and `ADR-0019`'s acceptance is the obvious candidate but not the only one (the alternative is that the owner labels each release directly, as `VERSION.md`'s current `2.0.1` entry already does by recording the owner's direction rather than the ADR's rule). **Phase 6 must name its labelling authority before the phase opens**, in one line in this document or in `ADR-0019`'s own status, and until it does, step 2's "per ADR-0019" is suspended rather than satisfied.
>
> **One open item follows for the next release:** `ADR-0019`'s own acceptance. It has been `Proposed` since 2026-10-04 with no owner ruling recorded, while releases have already been labelled against it in practice. Either it is accepted — and step 2 becomes true as written — or its status is corrected to say plainly that labelling is owner-directed per release. That is an Architect/owner act, not this plan's, and it is recorded here so the next release does not inherit the same gap.
>
> **`ADR-0028` needs no matching caveat.** Its Consequences section does not reference release labelling, so the inversion this note corrects exists only in the plan. Checked for `ADR-0019`, `VERSION` and `CHANGELOG` in `docs/adr/0028-frontend-architecture-named-primitives.md`: no occurrences.

> **Evidence widened 2026-10-22 (agent-2 absorption pass, F). The precedent is now two releases, not one.** The note above cites `VERSION.md`'s `2.0.1` entry as the single instance of owner-directed labelling under a `Proposed` ADR. **`2.0.2` was cut the same day under the same rule** — a PATCH for a stale browser expectation, labelled per `ADR-0019`'s fix rule while the ADR remained `Proposed`, and its own `VERSION.md` entry records the ADR's non-binding status the same way. So "the owner labels each release directly, and the ADR's rule is applied as best practice" is now **the observed practice across two consecutive releases**, not a single data point. This is a change of **evidence, not of policy**: nothing about the labelling authority is decided here, and the open item above still stands — `ADR-0019`'s acceptance remains owed, and Phase 6 still may not open until the authority is named in one line. What the second release adds is that the interim practice is demonstrably repeatable, so "correct the ADR's status to say labelling is owner-directed per release" is the cheaper of the two options on the evidence.

> **Amended 2026-10-22 (correction pass, D1 / authoritative A26 at this site).** Two clauses of the sentence above are corrected. The sentence stands as written.
>
> - **"the three static checks" is five**: `framework-check`, `link-check`, `render-check`, `data-check`, `framework-audit` — and the ported `framework-audit` must report `UNVERIFIED` and exit non-zero when its driver is absent, never exit 0 (its current `:31-34` behaviour, which would make this clause true while nothing was measured).
> - **"the dead code is gone" cannot be a finishing condition**, because D1 measured that none of the five components is dead: all five are referenced by a Feature test or a browser fixture. The clause becomes "**every component's disposition is recorded and owner-ruled per `SCREEN_SPEC.md` §7-13**" — which can be true with five components held in place (FQ-12).

### Risks and mitigations

| Risk | Mitigation |
|---|---|
| A visual regression hides in a "near-copy" rewrite | before/after snapshots per commit; the 77 browser specs pin geometry |
| The strangler stalls mid-migration | every phase is independently shippable; the app serves the mixed tree the whole way |
| The primitive layer over-abstracts | primitives are only the ~40 the HFW proved on 49 pages; no speculative abstraction |
| The check harness false-positives on legacy pages | the harness runs clean on the un-migrated tree (Phase 1) before anything is rewritten |
| A ruling is reversed later | the ADR records the decision and its rollback; reverting `app.css` and the migrated components restores the prior architecture |
| **The `review-detail` route changes behaviour** (owner ruling 3) | one route, one controller action, one page, one browser spec, one `SCREEN_SPEC.md` row; the queue's inline detail is removed in the same commit so there is never a second rendering, and the revert is one commit |
| **The frozen catalogue ages into a false claim** (owner ruling 5) | the capture date sits in five places and the set README states the figures are a dated observation; a reader must re-derive before quoting, the rule `coverage.md` already carries |
| **Six new pages multiply the check surface** (owner ruling 4) | each new page enters the four checks on the commit that adds it, not at the end; the set is green at 55 pages or the phase is not done |
| **A codemod rewrites something it did not understand** (Phase 7 item 2) | it rewrites only exact-set matches, refuses everything else with a file and line, writes no partially-rewritten file, and runs `--dry-run` first |
| **A screenshot baseline rots and blesses a regression** (Phase 7 item 1) | baselines exist only for the primitive gallery, never per app page; the diff is a Level 1 signal, and the app's browser specs remain the authority |
| **The migration ruleblock outlives its migration** (Phase 7 item 5) | it carries a dated banner and is removed at Phase 6 exit, so it cannot become stale instruction |

### Owner rulings (2026-10-22)

**Authoritative source: `ADR-0028`'s `## Owner rulings promoted from the plan (2026-10-22)` section. The table below is a citation, not a second statement of the rulings.**

> **Site note 2026-10-22 (close-out pass, D1 / A29 co-location).** The `### Open questions for the owner` list that used to stand at the end of this document **was removed entirely** by a concurrent session and replaced by this section. The list's five questions are all answered by the five rulings in the table below, and its residual decisions are carried by the FQ register — so the removal is a succession, not a loss. **The coverage proof is the record immediately below** (entry 5 and its five-row coverage table): each removed question is mapped to the ruling that answered it and to the `FQ` entry that carries whatever remained. **No ruling on the removal is recorded, and D1 has not restored the list** — the FQ register is its successor, and restoring a second question list beside it would create two registers for one purpose. **Sanctioned 2026-10-22 (bounded); see `### Owner rulings (2026-10-22)` above for the citation and ADR-0028 for the authority.**

> **Recorded 2026-10-22 (correction pass, D1 / authoritative A29; VD-11). A concurrent session rewrote five passages of this document in place rather than appending dated notes. Its text is left untouched — D1 does not know who made these changes, on what authority, or whether they were sanctioned — and the original wording is preserved verbatim below so the owner can rule on each. Until that ruling, treat every listed passage as *changed by an unidentified session, sanction pending*.** Each entry names the site, the original sentence exactly as it stood at `HEAD` (`15f7ee1b`), and what now stands there. **All five removed items are covered by rulings 1, 4, 10 and 11; no question is lost.**
>
> 1. **Status vocabulary** (`**Status vocabulary.**` paragraph). Original: *"Proposals carry `proposed` (nothing is built yet), `verified` (established in the repository), or **`open question` (needs an owner ruling)**."* Now reads `ruled` (an owner decision recorded in this document). The change is defensible — the five questions were ruled the same day — but it is an in-place rewrite of a definition, and `open question` remains a live state for FQ-01…FQ-14.
> 2. **§6 heading and its list.** Original heading: *"### 6. The divergences that need a decision"*, followed by a five-item numbered list. Now: *"### 6. The divergences, and how each was ruled"*, followed by a five-row table with a Ruling column. The list's five items are the same five divergences; the ruling column is new information.
> 3. **Phase 0 preamble.** Original: *"Before any code, **three** decisions are recorded:"*. Now: *"Before any code, **two** decisions are recorded:"* — consistent with the owner ruling that removed the middle decision (the ruling on the six open items), which had already been answered.
> 4. **Phase 5 heading.** Original: *"### Phase 5 — The mock catalogue graduates to the app"*. Now: *"### Phase 5 — Freeze the catalogue"* — consistent with owner ruling 5, which reversed promotion in favour of a frozen snapshot.
> 5. **The `### Open questions for the owner` list — removed entirely.** Original, verbatim:
>    1. *Does "replace the frontend architecture" mean the reading above (adopt the design-system architecture inside Vue), or a larger rewrite this plan deliberately does not propose? The non-goals section is the plan's answer, but it is a scope decision only the owner can ratify.*
>    2. *`FormTabs`: restore the link-tab control (recommended), or accept the HFW's flat table and the form-switch regression it introduces?*
>    3. *`review-detail`: keep as its own page (the HFW's choice) or fold back into the queue (the app's choice)?*
>    4. *The six undrawn screens: draw them into the HFW set, or record them as out of scope in the set's README?*
>    5. *The mock catalogue: promote it to the live pipeline, or retire it once the pages render from the API?*
>
> **Coverage check for item 5 — all five are fully covered, so the list is not restored.** Each removed question is answered, and where the answer left a residual decision, the residual is an `FQ` entry:
>
> | removed question | answered by | residual decision |
> |---|---|---|
> | 1. what "replace the architecture" means | Owner rulings row 1; `ADR-0028` D1 | none |
> | 2. `FormTabs` restore vs flat table | Owner rulings row 2 (restore) | **FQ-11** — what the strip controls, and the one-fact-one-rendering question |
> | 3. `review-detail` page vs inline | Owner rulings row 3 (own page) | **FQ-10** — the `SCREEN_SPEC.md` §4.5 amendment it requires |
> | 4. six undrawn screens | Owner rulings row 4 (draw all six) | none; Phase 4a executes it |
> | 5. mock catalogue promote vs retire | Owner rulings row 5 (freeze) | **A13** — the provenance the freeze needs |
>
> **Nothing in the removed list is uncovered**, so no question is added for it. No peer text was edited, reverted or reformatted by this note.

Five scope questions were put to the owner. All five are now ruled, and the rulings are binding on this plan. They are recorded here rather than in an ADR because they set scope, not architecture; the architecture decision still needs its own ADR at Phase 0.

| # | Question | Ruling | What it changes in this plan |
|---|---|---|---|
| 1 | What "replace the frontend architecture" means | **Adopt the design-system architecture inside Vue.** Keep Inertia + Vue 3 + Tailwind v4; replace the styling and enforcement layer. | The non-goals stand as written. Part II is executed as phased. No framework, routing or props-contract change is in scope. |
| 2 | `FormTabs` | **Restore the link-tab control**, and add the HFW's `FormDetail` block beside it. | Phase 4 step 1 is now a build, not a choice. The HFW's flat costume table is superseded; `catalog-detail.html` gains the link-tab strip and keeps the table as the detail body. |
| 3 | `review-detail` | **Keep it as its own page.** | Phase 4 gains a route: the queue links to a real detail screen and the inline detail in `Review/Index.vue` is removed. This is the one ruling that changes behaviour, not only visuals, so it carries its own browser spec and its own `SCREEN_SPEC.md` row. |
| 4 | The six undrawn screens | **Draw all six** — the three registry screens and the three Snapshot screens. | Phase 4 step 3 and 4 merge into one: all six are drawn and all six enter the four checks. The set's README claim is corrected to 50 of 50 registry screens plus `help`. |
| 5 | The mock catalogue | **Keep it as a frozen dated snapshot.** | Phase 5 is replaced: `assets/data/` stays exactly as it is, is labelled with its capture date, and is regenerated only when the set is revised. `data-check.mjs` stays as its invariant test. The dataset is explicitly *not* promoted to a second source of truth. |

**Consequences worth stating plainly.**

- **Ruling 3 is the only behaviour change in the whole plan.** Everything else is a re-spelling or an addition. It therefore needs the narrowest possible blast radius: one new route, one controller action, one page, one browser spec, and a `SCREEN_SPEC.md` row for the new screen. If the owner later reverses it, the queue's inline detail is restored by reverting one commit.
- **Ruling 4 raises the HFW's page count from 49 to 55** (six new pages) and makes the registry claim true for the first time. It also means the three Snapshot screens enter the check loop, so their layouts must satisfy `framework-check`'s law rules rather than merely existing.
- **Ruling 5 removes the drift risk but adds an ageing risk.** A frozen snapshot with no capture date becomes a lie the moment someone reads it as current. The label is therefore part of the ruling, not a nicety: `assets/data/README.md` and each generated file's header must carry the capture date, and the set's README must say the numbers are a dated observation.
- **Ruling 1 keeps the plan honest about what it cannot fix.** The app's 200 Feature tests and 77 browser specs remain the authority for behaviour; this plan only closes the *visual-system* enforcement gap.

### Literal inventory (the check's rule set)

> **Added 2026-10-22 (correction pass, D1 / authoritative **A27**; VD-08).** This is the enumerated list `tools/ui-framework-check.mjs` forbids, row by row. Aggregates are not a rule set: a check needs the values. **Re-derived 2026-10-22 from `docs/design-research/frontend-v2-wireframes/assets/shell.css`** (git-clean at `15f7ee1b`, 1,126 lines) after the earlier reconciliation map proved not to exist as a file; the map is now persisted at `docs/proposals/primitive-reconciliation.md`, and every figure below is marked **re-derived in this pass**. Method: line-range scan of the two token blocks (`:root` 8-51, dark 68-92) and a global per-line match for declarations outside them, so multi-property lines are not truncated. Hex on lines 1016 and 1018 is **inside a comment** (prose about a measurement) and is excluded.

**A. Colour literals inside rules — 21.**

> **Ownership resolved 2026-10-22 (agent-2 pass / A3): the map owns the numbered rows; this section keeps the rule prose and cites them.** Two copies of one rule set existed at two counts — **this section 37 rows** (A 21 · B 10 · C 1 · D 1 · E 2 · F 2) and **`docs/proposals/primitive-reconciliation.md` §5 36 rows** (A 21 · B 10 · C 1 · D 1 · E 3). **The discrepancy is a split, not a missing rule:** this section's **F36** ("no set-only custom property") and **F37** ("no unprefixed token reference") express the map's **single rule 36** ("no unresolved `var(--…)`") as two rows, which is why 37 − 36 = 1. The map's one-row form is the better statement, because an undefined custom property inherits in both cases and the two failures are one defect.
>
> **From here on:** the **map holds the numbered rows**, and a change to a row is made there. **Rows 1–36 are at `primitive-reconciliation.md` §5**; rule 36 specifically is that section's §5-E rule 36. This section's rows are retained verbatim as the historical copy and **must not be edited** — a later pass that finds them disagreeing with the map should cite the map, not patch both.
>
> **One condition on that transfer, recorded because it is currently unmet:** the map is **untracked** in the worktree. Until it is committed, this section is the only copy that travels with the repository, so **do not delete it** on the strength of this note. The transfer completes when `docs/proposals/primitive-reconciliation.md` is staged.

> **Re-measured 2026-10-22 (agent-2 absorption pass, B3): the transfer claim above does not survive a row count, and the two files still hold two different row sets.** Counted in place: **this section carries 36 numbered rows**; **`primitive-reconciliation.md` section 5 carries 16 numbered rows**, whose numbering nevertheless runs to rule 36 because several rows group rules (row 34 "No `box-shadow` anywhere", row 35 "No `outline` wider than the base layer's `2px`"). So "rows 1-36 are at `primitive-reconciliation.md` section 5" is false as a statement of **location**: the map is a **coarser rule list that reaches 36**, this plan is a **finer inventory of 36 items with `file:line` evidence**, and neither is a copy of the other. The reconciliation is therefore **not done**. The honest division is: the map owns the **rule statements** (16 rows, in its 5-A to 5-E), this plan owns the **literal inventory** (36 rows with evidence), and each rule's authority is the map while its evidence stays here. A later pass must renumber so the mapping is one-to-one or drop one copy; this note records the measurement and does not choose. **The untracked-map condition above still holds and is now the more urgent half**: the map must be committed before D2 cites it, or this section stays the only travelling copy.
>
> **B3 resolved by content, 2026-10-22 (P.1.2).** Three rows sampled by content match in both tables: .stat-speed light #2F6BC4 at shell.css:793; #fff at .step[aria-current] .n, shell.css:1031; and the rule-only No box-shadow anywhere, whose four line numbers are identical. The finding is therefore **two renderings, one object, different granularity**: this section is the fine evidence copy with file and line per literal, the map is the coarser rule statement, and the physical row counts differ because the map groups rows (1-7, 10-17, 27-31) while this section splits rule 36 into F36 and F37. No row is missing; the earlier "two objects" framing is narrowed to granularity, not content. The untracked-map condition above still holds.
> **Correction to rule 35's line numbers (A4), because the map's copy is wrong and this section's row A-18 is right.** The map's §5-E rule 35 placed the set's `outline:3px` at "lines 1031 and 1125". Re-derived from `shell.css` on `e3ac31bd`: **line 1031 is `.step[aria-current="step"] .n{…color:#fff…}`** — a *colour* declaration, the one this section's **A-18** correctly names, and it carries **no outline**; **line 1125 is `.theme-switch button:focus-visible{outline:2px solid #fff;outline-offset:1px}`** — a **2px** outline, not 3px, and on a different selector. The set's `3px` outlines are actually at **205 (`.rail-toggle`), 332 (`.skip`), 426 (`.btn`), 899 (`.subnav-hub`), 905 (`.subnav-item`)** — the two `.subnav-*` rules being the ones this section's rule 35 prose names. A sixth, **738 (`.iconbtn`)**, is also 3px but in `--on-brand-chrome` rather than `--ring`. **So the map was wrong twice over, and the plan's A-18 was right.** Two 2px exceptions exist besides 1125: **518** (`.field .in input/select`, `2px`/`1px` offset).

| # | shell.css line | value | where |
|---|---|---|---|
| 1 | 96 | `#7FA8E8` | dark `.stat-speed` |
| 2 | 97 | `#F08A96` | dark `.stat-stamina` |
| 3 | 98 | `#C99A6B` | dark `.stat-power` |
| 4 | 99 | `#F08BB4` | dark `.stat-guts` |
| 5 | 100 | `#8CC78A` | dark `.stat-wit` |
| 6 | 101 | `#AEBB84` | dark `.stat-pal` |
| 7 | 102 | `#86C996` | dark `.stat-group` |
| 8 | 190 | `#fff` | glyph ink |
| 9 | 676 | `#fff` | on `var(--risk)` — FQ-04 |
| 10 | 793 | `#2F6BC4` | light `.stat-speed` — promotion 1 |
| 11 | 794 | `#C64B5E` | light `.stat-stamina` — promotion 2 |
| 12 | 795 | `#8A5A32` | light `.stat-power` — promotion 3 |
| 13 | 796 | `#C4557E` | light `.stat-guts` — promotion 4 |
| 14 | 797 | `#3E6B3A` | light `.stat-wit` — promotion 5 |
| 15 | 798 | `#6E7A45` | light `.stat-pal` — promotion 6 |
| 16 | 799 | `#3E7A4A` | light `.stat-group` — promotion 7 |
| 17 | 800 | `#0E7490` | light `.stat-sp` — **delete**, equals `--color-sp-ink` |
| 18 | 1031 | `#fff` | `.step[aria-current] .n` |
| 19 | 1096 | `#fff` | accent pill ink |
| 20 | 1112 | `#fff` | `.wfnav` |
| 21 | 1125 | `#fff` | focus outline on chrome |

**B. Radius literals outside the declared scale — 10.** Scale is `--radius-sm` 6 / `md` 10 / `lg` 14 / `xl` 16 / `pill` 999.

| # | line | value | verdict |
|---|---|---|---|
| 22 | 259 | `0 3px 3px 0` | off scale |
| 23 | 571 | `5px` | off scale |
| 24 | 756 | `4px` | off scale |
| 25 | 1052 | `2px` | off scale |
| 26 | 1104 | `4px` | off scale |
| 27 | 270 | `50%` | a dot — no scale step exists; permitted |
| 28 | 722 | `50%` | a dot — permitted |
| 29 | 994 | `50%` | a dot — permitted |
| 30 | 1009 | `50%` | a dot — permitted |
| 31 | 1036 | `50%` | a dot — permitted |

**C. Duration and easing — 1 declaration.**

| # | line | value | conflict |
|---|---|---|---|
| 32 | 178 | `transition:grid-template-columns .18s ease` | `.18s` ≠ `150ms`; `ease` ≠ `ease-out`; animates layout, which is not one of MOTION 2's five scopes (FQ-07) |

**D. Length literals — 1 rule, 55 distinct values.**

| # | rule |
|---|---|
| 33 | No literal `px` outside `@theme static`. Measured **514** occurrences across **55** distinct values. Most frequent: `1px` 59, `44px` 44, `2px` 30, `10px` 30, `12px` 30, `9px` 22, `6px` 21, `20px` 20. `44px` is the house target floor being honoured, not a defect. |

**E. Two structural rules the inventory implies.**

| # | rule |
|---|---|
| 34 | No `box-shadow` anywhere. Measured **4**: lines 558 (`.pickcard` inset ring), 912 (`.subnav-item.is-current` inset underline), 971 (`.savebar`), 1111 (theme switch). |
| 35 | No `outline` wider than the base layer's `2px` with `2px` offset (`app.css:525-528`). The set uses `outline:3px` on `.subnav-hub`, `.subnav-item` and adjacent rules. |

**F. Two rules about names, from the token reconciliation.**

| # | rule |
|---|---|
| 36 | No set-only custom property in a ported primitive: `--rule-soft`, `--r-sm`…`--r-pill`, `--rail-w`, `--rail-w-collapsed`, `--shell-compact/standard/wide`, `--shell`. None resolve in `app.css`. |
| 37 | No unprefixed token reference (`var(--panel)`). App tokens are `--color-*`; an undefined property inherits silently, so this must be a check, not a convention. |

### Open owner questions (FQ register, 2026-10-22)

> **Added 2026-10-22 (frontend-architecture reconciliation pass, D1 / **M-15**; supports authoritative A28).** The rulings above are settled scope. The nine questions below are **not** settled, and they are the questions the primitive→token reconciliation raised. They carry `FQ-` numbers so they merge with a sibling pass's questions without renumbering. Each names the document its answer lands in and the phase it blocks. **None is resolved here**, and no agent may resolve one: a radius outside the declared scale, a `box-shadow`, a transition outside MOTION 2, and a literal colour where a token exists are conflicts, not variations.

> **Added 2026-10-22 (correction pass, D1 / authoritative A28): every ratio in this register is computed, not measured.** Each figure is the WCAG 2.1 relative-luminance ratio of the declared hex pair — arithmetic on the values as written in `app.css` and `shell.css`, not a sample from a rendered frame. The instrument that would make them *measured* is the D-288/G-18 block in `tests/Feature/DesignTokensTest.php`, asserting every pair in both themes; until that gate is implemented and run, these ratios carry exactly the status `DESIGN.md` §2.1 gives its own table: **documented, not re-verified**.

> **Register-count correction, added 2026-10-22 (agent-2 pass / A-count drift).** The M-15 note above says "**the nine questions below**". There are now **seventeen entries** — `FQ-01`…`FQ-14`, `FQ-16`, `FQ-17`, `FQ-18` — with **`FQ-15` reserved and unused**. The growth is not an error in the original note: nine was correct when it was written, and `FQ-16` (Snapshot rows/sections), `FQ-17` (Dashboard and Run detail undrawn) and `FQ-18` (which phase extends the pair list) were added afterwards. A reader counting the register should count entries, not trust the prose. **`FQ-15` must stay empty** — reusing it would silently re-point any existing reference.
>
> **The one register is `## Open questions register` below; the questions in this head block are its plan-side history, cited for the ruling trail.**
>
> **Two words in the A28 note immediately above are now superseded, and only those two.** It says the ratio-making instrument is the gate "until that gate is **implemented and run**". The gate is **implemented and committed** at `da15a5ba` (31 pairs, 9 badges, `toHaveCount(81)`, no stubs). Whether it **ran** in the release's Pest run, or reported incomplete, is agent 1's finding and is **not asserted here** — so the ratios stay **computed, not rendered** either way, which is the status that note gives them.

**FQ-01 — `--rule-soft`: promote it, or re-spell every use to `--color-rule`?**
Eight primitives (`.card`, `.tile`, `.tag`, `.type-chip`, `.warn`, `.info`, `.srow`, `.savebar`) use a hairline the app does not have, referenced 23 times.
- **A — promote `--color-rule-soft` (`#D9D8E2` / `#3A3A44`).** Keeps the set's soft look; ships a card boundary at **1.37:1** light / **1.34:1** dark, failing WCAG 1.4.11's 3:1 and contradicting the existing G-18 row `rule on page`. `DESIGN.md` §2.3's "borders `1px --color-rule`" would need amending to declare the edge decorative.
- **B — re-spell to `--color-rule` (`#84828C` / `#6E6C73`).** Boundary clears at **3.46:1** / **3.88:1** with no new token and no doc change; every border in the set darkens, so the card grid reads harder than the HFW draws it.
- Lands in: `DESIGN.md` §2.1 + §2.3, then `app.css` `@theme static`. **Blocks Phase 2.**

**FQ-02 — the two `box-shadow` selection markers (`.pickcard`, `.subnav-item.is-current`).**
Both draw a 1px inset ring/shadow to say "selected". `DESIGN.md` §2.3 makes `box-shadow` a house invention and gives selection a boundary token.
- **A — re-spell to `--color-pick-line` at 2px (or `outline`).** No new token; the boundary already measures **6.24:1** light / **8.46:1** dark; the marker changes from a shadow to a line.
- **B — add a shadow-based selection role to §2.3.** Preserves the drawing exactly; opens an elevation path for state, which §2.3's overlay-only rule exists to prevent.
- Lands in: `DESIGN.md` §2.3. **Blocks Phase 2** (both primitives).

**FQ-03 — `.savebar` elevation.** The save bar carries `box-shadow:var(--shadow-overlay)`, but §2.3 permits elevation for overlays only (dialog, floating panel, recommendation card, dropdown) and a sticky bar is none of them.
- **A — drop the shadow; keep the bar flat like a card.** Consistent with the overlay-only rule; loses the "this floats above the page" cue.
- **B — add the sticky save bar to §2.3's overlay list as a fifth member.** Keeps the cue; the list stops being a closed enumeration and every future sticky surface inherits the claim.
- Lands in: `DESIGN.md` §2.3. **Blocks Phase 2.**

**FQ-04 — the error icon's ink: `#fff` on `var(--color-risk)`.** Measured **10.89:1** in light and **2.44:1** in dark, because dark `--color-risk` is `#FF7E8C`.
- **A — add `--color-on-risk`** (`#FFFFFF` / `#121013`, the `on-chrome` pattern). One token, one G-18 row, theme-safe.
- **B — keep `#fff` and restrict the icon to light.** No new token; ships a control that only works in one theme, which G-18 forbids.
- Lands in: `DESIGN.md` §2.1 + `app.css`. **Blocks Phase 2** (`.state-error`).

**FQ-05 — the seven stat-mark colours, and the two-families-of-five problem.** `.stat-*` uses eight literal hexes; `.stat-sp` duplicates the existing `--color-sp-ink` (reuse, not promotion), leaving seven to promote. But the app already carries `--color-type-{speed,stamina,power,guts,wit}` for support-card type chips with **different values** (`#3FA3F2` vs `#2F6BC4` for speed).
- **A — two families with distinct names** (`--color-stat-*` alongside `--color-type-*`). Seven new tokens, all clearing 3:1 on `raised` (4.24–6.23:1); four token names differ only by prefix, so a reader must know which "speed" they are seeing.
- **B — one family, the type chips re-pointed at the stat colours.** No duplicate nouns; the support-card chips change hue, a visible regression on a screen the set already ships.
- Lands in: `DESIGN.md` §2.1 + `app.css`. **Blocks Phase 2.**

**FQ-06 — how a ported primitive addresses tokens at all.** `shell.css` uses unprefixed custom properties (`var(--panel)`); `app.css` declares `--color-panel`. Only 1 of 52 names matches literally. An undefined custom property **inherits rather than errors**, so a pasted primitive renders in its parent's colour and looks plausible.
- **A — re-spell every `var()` in every ported primitive.** No new tokens; the diff is mechanical but touches every primitive, and a missed one is invisible without the D-288 gate.
- **B — declare unprefixed aliases in `app.css` so `shell.css` resolves verbatim.** A ~52-alias shadow set; doubles the token surface and defeats `DESIGN.md` §9's one-home rule.
- Lands in: `app.css` (+ `ADR-0028` D3 as the record). **Blocks Phase 1 step 2 and Phase 2.**

**FQ-07 — motion: the set animates layout, and the ported check requires it.** `shell.css:178` declares `transition:grid-template-columns .18s ease` — the wrong duration, the wrong easing, and a scope MOTION 2 does not list. Separately, `framework-check.mjs:198` **fails** when that transition is absent.
- **A — drop the transition and drop that assertion from the ported check.** Conforms to "a desk does not move"; the rail snap-collapses and the check loses one rule.
- **B — amend MOTION 2 to admit a sidebar-resize tier at `duration-150 ease-out`.** The motion table gains a sixth scope; the ported check stays satisfiable as written.
- Lands in: `DESIGN.md` §8 (+ `tools/ui-framework-check.mjs` at port time). **Blocks Phase 1 step 1** (the check's rule set) and Phase 2.

**FQ-08 — the guards that die with `UraDuelPanel` / `UraMilestoneTracker`.** Neither has a production call site, but `UraFinaleSurfacesTest.php:301-319` asserts they contain no gate figures, no percentages and no scenario name, and `:345-364` asserts neither emits `state="unknown"`. Deleting them (Phase 4 step 5) is a red suite unless the guard moves.
- **A — move those assertions onto whatever replaces the panel.** The guard survives; the replacement must satisfy them, which constrains its design.
- **B — retire the assertions with the files, recorded as an owner-approved test deletion.** Cheap; removes the only enforcement that those figures stay in `config/scenarios.php`.
- Lands in: `SCREEN_SPEC.md` §7-13 / §7-24 status + the test file. **Blocks Phase 4 step 5.**

**FQ-09 — three `DESIGN.md` §2.1 rows disagree with the shipped values, and two of the doc's versions fail their own contrast claim.**
- `--color-brand-accent-ink`: doc `#8877FF` measures **3.14:1** on page (its row claims ≥4.5); shipped `#6554E0` measures **4.93:1**.
- `--color-status-caution` dark pair: doc (`#F4E4B0` on `#8A6F1A`) measures **3.79:1**; the app never redeclares caution in dark, so it inherits light (`#6A4A00` on `#FFE9A8`, **6.74:1**).
- `--color-rule`: doc `#E4E1EA` measures **1.18:1** against page; shipped `#84828C` measures **3.46:1**.
- **A — correct the doc rows to the shipped values, with a dated note.** Docs then agree with the tokens; docs-only change; no screen moves.
- **B — correct `app.css` to the doc.** Faithful to the written table; ships `brand-accent-ink` at 3.14:1 and `rule` at 1.18:1, so it cannot pass G-18.
- Lands in: `DESIGN.md` §2.1. **Does not block a phase**, but Phase 2's token work should not begin from the table until it is corrected.

**FQ-10 — does `SCREEN_SPEC.md` §4.5 get amended for the `review-detail` page?** §4.5 rules *"resolve controls inline per card (no detail page)"* and pins per-card stacked errors. Extracting a page contradicts both, so a ruling is needed before Phase 4b builds it.
- **A — amend §4.5** to allow a detail route, re-specifying the error surface as a single stacked list on the detail page and re-scoping `ReviewFormAccessibilityTest`. The queue keeps a one-line summary per card and gains a link. Cost: a spec edit and a test re-scope; the per-card error locality is lost.
- **B — keep the inline detail** and abandon `review-detail` as a page. No spec change; the set's page becomes a documented divergence, and `ADR-0028` D6's "one sanctioned behaviour change" becomes zero.
- Lands in: `SCREEN_SPEC.md` §4.5 (owner-written; this plan may not edit it). **Blocks Phase 4b.**

**FQ-11 — what does the costume-form strip control, and what does the table render while it is active?** Ruling 2 says restore the link-tab control and add `FormDetail` beside it, but the set's table lists **all** forms.
- **A — the strip is a location control** (`?form=<id>`, `aria-current="page"`), the table lists all forms with the active row marked, and `FormDetail` renders the active form below. One rendering per fact; the active row appears twice in different roles (row and detail).
- **B — the strip filters the table** to the active form, with `FormDetail` below it. One rendering of the active form, but the other forms become invisible, and the HFW page's `render-check` baseline changes.
- Lands in: `DESIGN.md` §3 / `SCREEN_SPEC.md` SCR-CAT-002 (owner). **Blocks Phase 4a** (the set page) and **4b** (the app component).

**FQ-12 — what happens to the five components with no production call site?** `UraDuelPanel`, `UraMilestoneTracker`, `Checkbox`, `Combobox`, `SegmentedControl`. D1 measured that **all five are referenced** by a Feature test or a browser fixture, so `SCREEN_SPEC.md` §7-13's precedent applies and none is dead.
- **A — mount or delete, per component**, following §7-13: each deletion paired with the test that reads it, and the guard assertions at `UraFinaleSurfacesTest.php:301-319` and `:345-364` either moved to the replacement or retired with a stated reason. Cost: a per-component decision and a test edit each.
- **B — leave all five in place** and record them as held, as §7-13 did for its own remainder. No red suite; five components stay without a call site.
- Lands in: `SCREEN_SPEC.md` §7-13 / §7-24 status lines. **Blocks nothing** — the phase body no longer deletes them (A10).

**FQ-13 — does `help` become a real screen?** The set draws `help.html`; the app has no `help` route and `SCREEN_SPEC.md` has no `help` row.
- **A — give it a route and an `SCR-*` row**, and add it to §3's matrix. The set's page becomes truthful about the app; cost is a route, a controller action, a page component and a spec.
- **B — keep it a set-only annotation**, and say so in the set README. No app change; the set carries a page the product does not.
- Lands in: `SCREEN_SPEC.md` §3/§4 + `routes/web.php`. **Blocks Phase 6's** README correction wording (A15).

**FQ-14 — is `@vue/compiler-sfc` promoted to a direct dependency?** It is present only as a transitive of `@vitejs/plugin-vue`, and the Phase 0 non-goals forbid manifest changes without approval.
- **A — promote it** to an explicit `devDependencies` entry at the version the plugin already resolves. The codemod stops depending on a hoisting accident; cost is a `package.json` change the non-goals currently forbid.
- **B — name the pin and resolve through the plugin's re-export.** No manifest change; the codemod depends on a transitive staying resolvable, and a future plugin major could move it.
- Lands in: `package.json` + `AGENTS.md` §5 (dependency rule). **Blocks Phase 7 step 3.**

> **`FQ-15` is reserved and unused.** It was provisionally assigned to the `.slot-*` primitive conflict, then withdrawn: that conflict's only defensible resolution is an existing token (`--ink-faint` → `--ink-muted`, 2.72:1 → 4.57:1), so it is a RE-SPELL and needs no ruling. The number is left empty rather than reused, so any copy of this register that already carries an `FQ-15` reference resolves to nothing rather than to the wrong question. The next new question is `FQ-16`.

**FQ-16 — do the three Snapshot screens get `SCR-*` rows and §4 sections, or rows only?** `SCREEN_SPEC.md` §7-25 records that `career.snapshot`, `.setup`, `.review` and `.store` appear in no §3 row and no §4 section, and calls it an omission rather than an exclusion. Phase 4 step 3 now draws all three screens and puts them in the four checks, so the pages exist while the registry still omits them.
- **A — rows and §4 sections land with the pages, in one commit.** Closes §7-25 properly and keeps the file's own convention (every registry row has a §4 section). Cost: three new §4 sections — state tables, accessibility, navigation — which is a documentation pass on `SCREEN_SPEC.md` that only its owner can write; this plan may not edit it.
- **B — rows only, §4 sections deferred under a dated §7-25 note.** The registry stops claiming a screen set that excludes three shipped screens, at the cost of three rows pointing at nothing, which is a shape no other row in the file has.
- Lands in: `SCREEN_SPEC.md` §3 (matrix) and §4 (sections), plus §7-25's status line — owner-written. **Blocks Phase 4a**, because the default (A) must be known before the commit that draws the pages, and gates Phase 6's registry-totals correction.

**FQ-17 — are `SCR-CAR-001` Dashboard and `SCR-RUN-003` Run detail deliberately undrawn in the set, or is the set short two pages?** Measured on `da15a5ba`: the set has **no `dashboard.html`** and no run-detail page, so **45 of the 50 registry rows have a page** — 5 undrawn, of which Phase 4a draws three (Create, SaveVeteran, RacePlanner) and these two remain. Neither is in any phase's scope, and the set's README claim ("the 48 that match the registry") cannot be made true until this is decided. Raised here rather than resolved because it is a **set-scope question**, and the set is a frozen dated artefact under owner ruling 5 — adding two pages to it is a set revision, and declaring them out of scope is a ruling about what the set is for.
- **A — draw both pages, in Phase 4a.** The registry claim becomes true for all 50 rows; cost is two more set pages with their own law-check and a page count of 57 rather than 55, and Run detail duplicates information the four cockpit pages already carry (the app redirects `runs.show` to the cockpit, so the set would be drawing a screen the app no longer serves).
- **B — declare both deliberately out of scope, in the set's README and `SCREEN_SPEC.md`.** No new pages; the README claim becomes *"48 of 50 drawn; Dashboard and Run detail are recorded as out of scope"*, which is truthful. Cost is that the set stops being a complete rendering of the registry, and a later pass may re-open the question.
- Lands in: the set's `README.md` + `SCREEN_SPEC.md` §7-25/§7-27 status lines — owner-written. **Blocks Phase 6's README correction** (A15) and the final wording of the registry claim; does **not** block Phase 4a, which can draw its six regardless.

**FQ-18 — which phase extends the gate's pair list, so that "answered" can mean "the assertion passed"?** *Drafted 2026-10-22 (agent-2 pass), not resolved.* The rule added earlier — *an FQ is answered when its named assertion passes* — is a proposition about a gate that **will not contain the pair**. Measured against the gate as it exists at `da15a5ba`:

| FQ | the assertion this plan names | is the pair in the gate today? |
|---|---|---|
| **FQ-01** | `rule on page` at 3:1, once `--rule-soft` is gone or promoted | **partly** — `rule on page` passes today for `--color-rule` (3.46:1 / 3.88:1); the **failing** `--rule-soft` boundary is **not in the list at all**, so the gate is blind to it |
| **FQ-02** | `pick-line on raised` at 3:1 | **no** |
| **FQ-03** | *not a contrast fact* — the ported check's `box-shadow` rule | **no** — the ported check does not exist yet (Phase 1 work) |
| **FQ-04** | `on-risk on risk` at 4.5:1 | **no** — it cannot exist until FQ-04 mints the token |
| **FQ-05** | seven `stat-* on raised` rows at 3:1 | **no** |

**Zero of the five causes currently has an assertion in the gate that corresponds to the cause.** So the plan's acceptance rule is a rule **without teeth** until the pair list is extended, and **no phase currently owns that extension** — which is the defect this question exists to close. The three options:
- **A — extend the pair list first, as a Phase 2 precondition.** The pairs exist before any FQ is ruled, so the owner rules against an existing assertion. Cost: pairs whose tokens do not exist yet (`on-risk on risk`) go **red by construction**, so Phase 2 opens with a second baseline of *new expected failures* that are not FQ-01…FQ-05 failures; and the gate's scope grows before Phase 2's own changes land.
- **B — rule first, extend the pair list as part of each answer.** The owner rules on the merits; each answer becomes **two actions** (rule, then add the pair). Cost: "answered" slips until the pair is added, so each FQ carries a provisional period in which its acceptance criterion is a proposition rather than a measurement — on the plan's own criterion, a judgement dressed as a measurement.
- **C — split each FQ into two decisions taken at once: which token, and which pair asserts the answer.** The pair is then added in one Phase 2 pass, and FQ-02/04/05 go green on that pass. Preserves the criterion without reddening the gate early. Cost: each FQ becomes a two-part ruling, and the owner must answer the **instrument** as well as the **value** — a different register from "which token".
- Lands in: this register's head (extending the A28 statement) and `primitive-reconciliation.md` §4.1 — both state the acceptance rule, so both must carry the chosen mechanism. **Blocks Phase 2's opening**, because whichever option is chosen, no Phase 2 FQ work may be argued to be "answered" until the criterion's mechanism is chosen. Numbering: `FQ-15` stays reserved and unused; `FQ-16` and `FQ-17` exist; this is the next new number.

> **Measured evidence appended 2026-10-22 (agent-2 absorption pass), so this is no longer argued from design theory.** The table above was drafted against the gate's *list*; the gate has since been **run**, and it was green. That converts every "today" cell from inference to measurement, and it changes the weight of the question.
>
> | FQ | the assertion this plan names | in the gate today? | how that is now known |
> |---|---|---|---|
> | FQ-01 | `rule on page` at 3:1 once `--rule-soft` is gone or promoted | **no** | `rule-soft` **0 occurrences** in `DesignTokensTest.php`; the only `rule` pair is `rule on page` for `--color-rule`, which passes 3.46:1 / 3.88:1 |
> | FQ-02 | `pick-line on raised` at 3:1 | **no** | `pick-line` **0 occurrences** (its boundary is contracted at 6.24/8.46:1 in `DESIGN.md` §2.1 and still has no assertion) |
> | FQ-03 | the ported `box-shadow` prohibition | **no** | the ported check does not exist; Phase 1 work |
> | FQ-04 | `on-risk on risk` at 4.5:1 | **no** | `risk` **0 occurrences**; the pair cannot exist until the token is minted, and the 2.44:1 dark ratio is not tested |
> | FQ-05 | seven `stat-* on raised` rows at 3:1 | **no** | `stat-*` **0 occurrences** as pairs |
>
> **The run, quoted:** `php artisan test --filter=G-18` at `da15a5ba` → **3 passed (184 assertions)**, exit 0, both themes, **0 failures**. So the gate is green over **31 text/boundary pairs + 9 grade fills**, and green says nothing about any of the five causes. The plan's §M-13 escape clause had already allowed for exactly this ("a green opening run is itself a finding"), and it fired.
>
> **Recommendation, stated as a recommendation and not a resolution: option C.** The reasoning, revised against the measurement rather than the theory:
>
> - **Against A** — A's cost was described as "a second expected-failure baseline". The run shows that cost is now **unavoidable whichever option is chosen**, because the current green *is* a baseline and any extension breaks it. So A's distinguishing cost is smaller than when this was drafted, but its benefit is unchanged, and it still produces a red gate that cannot distinguish "FQ not yet ruled" from "primitive implemented wrongly".
> - **Against B** — B makes "answered" a fiction for the whole phase, which the run now makes concrete: the owner could rule FQ-04 today and the acceptance criterion would stay **green by absence** (the pair is not in the list), which is the exact failure mode the rule at §M-13's second block was written to prevent.
> - **For C** — the run establishes that the **instrument** is a separate decision from the **value** and is currently undecided for every cause: the gate resolves computed styles in a real browser, it carries 31 pairs, and none of the five causes maps onto one. C is the only option that makes the owner answer *which pair asserts this* at the same moment they answer *which token*, and it is the only one where each ruling is complete on its own terms without reddening the gate first.
>
> **What this note does not do:** it does not pick an option, set the mechanism, or extend the pair list. The decision is the owner's and it gates Phase 2's opening.

## G-18 gate audit (absorbed 2026-10-22)

**Absorbed from `g18-gate-audit.md` 2026-10-22.** In the body below, "this audit" means this section.

Read-only audit written before the run; the run's result is at `CHANGELOG.md:29-31`; the audit's blind-spot findings are confirmed -- `rule-soft`, `risk`, `pick-line`, `ring`, `stat-*` each 0 occurrences in `DesignTokensTest.php`.

> **Confirmation added 2026-10-22 (agent-2 absorption pass, A7). The audit was read-only and predicted the run; the run has now happened and confirms it.** §6 and §8 below predicted that FQ-01 and FQ-04 would not appear as gate failures because their pairs are not in the contract list. Agent 1 ran the gate at `da15a5ba` on 2026-10-22: **3 cases, 184 assertions, exit 0, both themes, 0 failures**, over 31 pairs + 9 grade badges. The specific confirmations: `rule-soft`, `risk`, `pick-line`, `ring` and `stat-*` each appear **0 times** as pairs in `tests/Feature/DesignTokensTest.php`. **Every finding in §5, §6 and §8 is confirmed.** The only material addition this note makes is the measurement of the run itself — its count, its exit, its themes — which an audit that did not run the gate was not positioned to take. The body below is unchanged, including its status line: read "**Read-only.** The test was not run" as a statement about *this audit*, and take the run's numbers from this note and from agent 1's release record.

**Audited:** 2026-10-22, `tests/Feature/DesignTokensTest.php`, on `master` at `15f7ee1b`.
**Read-only.** The test was not run; agent 1's gates carry that. Every claim below is from reading the file and cross-reading `resources/css/app.css` and `DESIGN.md` §2.1.

**The brief's premise is stale, and correcting it is the headline.** The brief describes this file as "a large uncommitted rewrite that `DESIGN.md`'s 2026-10-14 erratum describes as '609 lines against HEAD's 331'". Measured: the file is **761 lines** and its **working-tree diff against HEAD is empty** — `git diff --numstat` returns nothing, and `git show HEAD:…` is 761 lines. So there is no uncommitted rewrite to audit; the file is committed, and both numbers in the erratum (609 and 331) are wrong. `DESIGN.md`'s §10 note that "a peer session holds uncommitted [work]… (609 lines against HEAD's 331)" no longer describes this tree.

---

### 1. Line count and diff

| measure | value |
|---|---|
| lines now | **761** |
| working-tree diff vs HEAD | **none** (`git diff --numstat` empty) |
| HEAD version | 761 lines — identical |
| `DESIGN.md` erratum's figures | "609 lines against HEAD's 331" — **both stale** |

*Classification: documentation drift.* (`DESIGN.md` §2.1's 2026-10-14 erratum and §10's dated notes.)

### 2. Does a pair list exist?

**Yes.** Two lists:

| list | where | count |
|---|---|---|
| `g18ContractPairs()` | `:373`–`:427` | **31 pairs** |
| `g18GradeBadgeTokens()` | `:439`–`:445` | **9 grade badges** |

Each pair is `['foreground' => …, 'background' => …, 'text' => bool]`. `:616`–`:617` pin the sizes: `expect($pairs)->toHaveCount(31)->and($badges)->toHaveCount(9)`.

*Classification: correct implementation.*

### 3. Are the assertions live?

**Live.** There is **no `markTestIncomplete`** anywhere in the file (nor in any file under `tests/`). Three real `it()` blocks sit inside `describe('browser contrast and token resolution (D-288, G-18)')` at `:549`: `:567`, `:610`, `:714`.

The assertion mechanism, quoted from `:656`–`:671`:

```php
foreach ($pairs as $label => $pair) {
    $ratio = contrastRatio($colors[$pair['foreground']], $colors[$pair['background']]);
    $threshold = $pair['text'] ? 4.5 : 3.0;

    expect($ratio)->toBeGreaterThanOrEqual($threshold, sprintf(
        '%s theme: the contract pair "%s" (--color-%s on --color-%s) measures %.2f:1, under the %.1f:1 %s'
        .' threshold. …', $theme, $label, $pair['foreground'], $pair['background'],
        $ratio, $threshold, $pair['text'] ? 'text' : 'non-text boundary',
    ));
}
```

There is also a **fail-on-empty** guard at `:594`–`:600` (`expect($empty)->toBe([])`), which is D-288's specific requirement: a pruned custom property must be caught, not silently inherited.

*Classification: correct implementation. The erratum's "the committed test never enforced them" is false for this tree.*

### 4. What does it measure — runtime values or hardcoded hex?

**Runtime, resolved from the built stylesheet.** `resolvedTokenScript()` (`:457`) runs in the page: it sets `document.documentElement.dataset.theme`, reads `getComputedStyle(document.documentElement).getPropertyValue(name)`, and normalises through a probe element's computed `color`:

```php
const raw = root.getPropertyValue(name).trim();
if (raw === "") { return { raw: "", resolved: "" }; }
probe.style.color = ""; probe.style.color = raw;
return { raw: raw, resolved: getComputedStyle(probe).color };
```

`parseComputedColor()` (`:488`) then turns `rgb(…)` into channels, and `contrastRatio()` (`:527`) computes WCAG 2.1 luminance. So the *values* come from `app.css` at runtime; only the **pair membership** is hardcoded, which is correct — membership is the contract.

`beforeEach` (`:550`–`:565`) restores Vite with `$this->withVite()`, because `tests/Pest.php` strips `@vite` and a sheet-less page would make every `getPropertyValue` return empty and fire the D-288 "fail on empty" guard on a *missing asset* rather than a missing token. That is the right call and is documented in place.

*Classification: correct implementation — this is the distinction the brief was worried about, and it passes.*

### 5. Which themes, and are the 1.2:1 rows asserted?

**Both themes:** `foreach (['light', 'dark'] as $theme)` at `:573` and again at `:641`.

**Non-text boundaries (3:1): yes.** Six rows carry `'text' => false` and are asserted at 3.0: `goal on raised`, `goal-line on raised`, `green-deep on sunken`, `rule on page` (`:404`–`:411`).

**The accent stripe's 1.2:1 row: NO — and it cannot be, as written.** The threshold expression is `$pair['text'] ? 4.5 : 3.0` (`:658`). There is **no 1.2 branch anywhere in the file**, so a row for `brand-accent on brand-chrome` (`DESIGN.md` §2.1's amendment: "non-text ≥ 1.2:1") would be asserted at **3.0** and would fail. The same applies to `scrim-overlay on page` (§2.3's amendment, also ≥ 1.2:1). Both rows are **absent from the list** (see §8).

*Classification: defect — the gate's threshold model has two steps where the contract has three.*

### 6. Does the pair list's token set resolve to the current 81?

| measure | value |
|---|---|
| `--color-*` names declared in `app.css` `@theme static` | **81** |
| names the gate references (pairs + badges + `ink-strong`) | **42** |
| referenced names **not** declared by `app.css` | **0** — every referenced name resolves |
| declared names **not** covered by any pair | **39** |

The 39 uncovered, grouped:

- **Stat band lines (6):** `--color-line-speed`, `-stamina`, `-power`, `-guts`, `-wit`, `-sp`.
- **Type-chip glyphs (7):** `--color-type-speed`, `-stamina`, `-power`, `-guts`, `-wit`, `-friend`, `-group`.
- **Mark glyphs (4):** `--color-mark-ink`, `-pal`, `-group-soft`, `-group-line`.
- **Mood (6):** `--color-mood-great`, `-good`, `-normal`, `-bad`, `-awful`, `--color-on-mood`.
- **Selection boundary (1):** `--color-pick-line` — **contracted at 6.24:1 light / 8.46:1 dark in `DESIGN.md` §2.1 and asserted nowhere.**
- **Risk (1):** `--color-risk` — **the fill behind FQ-04's white-ink failure (2.44:1 dark) is not in the gate at all.**
- **Focus ring (1):** `--color-ring` — `DESIGN.md` §2.2's 3:1 non-text claim for the ring is unasserted.
- **Skill Points (2):** `--color-sp`, `--color-sp-ink`.
- **Steps and neutrals (5):** `--color-idle`, `--color-disabled`, `--color-ink-faint`, `--color-anchor`, `--color-lattice`.
- **Identity (1):** `--color-rank`.
- **Primary action (2):** `--color-primary-action`, `--color-on-primary-action` — defensible: they alias the brand-chrome pair, and `DESIGN.md` §2.1 says that row "owes no new G-18 row".
- **Brand accent (1):** `--color-brand-accent` — the stripe colour itself.
- **Scrim (1):** `--color-scrim` — the overlay shadow's colour.

*Classification: partly correct, partly defect.* Zero dangling references is the correct half. The uncovered `--color-risk`, `--color-pick-line` and `--color-ring` are defects: each is a colour whose contrast `DESIGN.md` states, and each is invisible to the gate.

### 7. The 9 grade badges, both themes, and the white-letters ban

Badges are asserted in **both themes** (the loop at `:641` encloses the badge loop at `:673`) against `ink-strong` at 4.5:1 (`:676`). The nine names match the nine declared `--color-grade-*` tokens.

**The white-letters ban is documented but not asserted.** The failure message at `:678` says *"White on these fills measures 1.20-1.44 and is banned (G-47)"* — that is prose in an assertion message, not a test. Nothing asserts that `#FFFFFF` against `--color-grade-*` **fails**; the gate only proves the shipped ink passes. So a future commit that swapped the ink to white would fail this gate (because the ratio would drop below 4.5), which is adequate in effect — but the ban itself has no assertion, and `DESIGN.md` §2.1's "white letters banned on the light fills" is enforced only by implication.

*Classification: correct in effect, drift in framing.*

### 8. Are `--shadow-overlay` and the 2026-10-15 brand/status pairs present?

**The 2026-10-15 brand and status *text* pairs are present (9 of them):** `on-brand-chrome on brand-chrome`, `brand-accent-ink on page`, `-raised`, `-sunken`, `-brand-accent-soft`, `status-positive-ink on status-positive`, `status-caution-ink on status-caution`, `ink-muted on brand-accent-soft`, `ink-strong on brand-accent-soft` — `:417`–`:425`.

**Two rows the contract requires are missing:**

| missing row | required by | threshold | why it cannot be added today |
|---|---|---|---|
| `brand-accent on brand-chrome` | `DESIGN.md` §2.1, 2026-10-15 amendment: "brand-accent on brand-chrome (non-text ≥ 1.2:1)" | **1.2:1** | no 1.2 branch in `:658`; at 3.0 it would fail |
| `scrim-overlay on page` | `DESIGN.md` §2.3, 2026-10-15 amendment: "G-18's pair list gains one row: scrim-overlay on page (non-text, ≥ 1.2:1)" | **1.2:1** | same; and `--color-scrim` is not referenced at all |

*Classification: defect — the two newest obligations are exactly the two missing, and both are missing for the same structural reason (no 1.2:1 threshold).*

### 9. What remains unimplemented

1. **A third threshold.** `$pair['text'] ? 4.5 : 3.0` must become a three-way threshold so the ≥1.2:1 rows can exist. Until then the two rows above cannot be asserted at all.
2. **The two missing rows** (`brand-accent on brand-chrome`, `scrim-overlay on page`).
3. **Coverage of `--color-risk`, `--color-pick-line`, `--color-ring`** — three colours whose ratios the design states, none asserted. `--color-risk` matters most: it is the fill FQ-04's 2.44:1 failure sits on.
4. **The 39 uncovered names** are not all obligations — glyph fills and tints legitimately carry no pair — but the file does not say *which* omissions are deliberate. A reader cannot tell an unasserted obligation from a deliberate exemption.
5. **An explicit `#FFFFFF`-fails assertion** for the grade fills (G-47), if the ban is to be tested rather than implied.

### 10. The erratum's status

`DESIGN.md`'s 2026-10-14 erratum says the committed test "contains no pair list at all — `g18ContractPairs` is absent and the block ends in `markTestIncomplete` at `:320` and `:329`", and calls the 19-pair figure "a number this file carried from a contract the committed test never enforced". Against this tree:

| erratum claim | measured |
|---|---|
| "`g18ContractPairs` is absent" | **present**, `:373`, 31 pairs |
| "the block ends in `markTestIncomplete` at `:320` and `:329`" | **no `markTestIncomplete` in the file**; the two cited line numbers now fall inside `themeStaticColorTokens()`/`themeStaticBlock()` helpers |
| "the committed test never enforced [the pairs]" | **it does** — `toBeGreaterThanOrEqual` over both themes at `:656`–`:683` |
| "19 pairs" | the list carries **31** (19 was pre-2026-10-15; the brand/status amendment added text pairs, though two ≥1.2 rows are still missing) |

**So the erratum is stale in its two load-bearing claims** — the pair-list absence and the `markTestIncomplete` body — while remaining correct about the *ratios*: the gate measures resolved values, so the ratios it computes are live, but the ratios **`DESIGN.md` states** are still not re-verified against a rendered frame in the sense §2.1 means (this gate reads custom properties, not painted pixels). The honest status is: **the gate exists and asserts; the document that says it does not is wrong; and the ≥1.2:1 obligations the document requires are the ones the gate cannot express.**

### Summary of classifications

| # | finding | classification |
|---|---|---|
| 1 | 761 lines, no uncommitted rewrite | documentation drift (`DESIGN.md` erratum) |
| 2 | 31 pairs + 9 badges exist | correct implementation |
| 3 | assertions live, no stubs | correct implementation |
| 4 | values resolved at runtime, not hardcoded | correct implementation |
| 5 | both themes asserted; no 1.2:1 threshold | **defect** |
| 6 | 0 dangling references; 39 uncovered, 3 of them obligations | **defect** (`risk`, `pick-line`, `ring`) |
| 7 | 9 badges both themes; white ban implied only | drift in framing |
| 8 | brand/status text pairs present; both ≥1.2 rows missing | **defect** |
| 9 | must-do list above | — |
| 10 | erratum's absence claim false; ratio caveat still fair | documentation drift |

**Nothing here was fixed.** The file is agent 1's on the current plan; this audit is the input to whoever owns the gate next.


### Browser triage (2026-10-22)

`dashboard.spec.ts:147` -- **CAUSED-BY-FIX, spec correctly updated to match the fix.** The old keyed title probe failed because `SubCell` now renders the absence through `AbsenceValue`'s disclosure; `b6a7d0f0` replaced the probe and the Dashboard suite reads 16 passed. **Fix correctness is a judgment from `DESIGN.md` §5, not from the run; the run proves only that the old probe failed and the new spec passed.**

`career-cockpit.spec.ts:566` -- PRE-EXISTING harness defect, not a product failure: `no such table: training_runs` without `PLAYWRIGHT_SCRATCH_DB`, 1 passed with it (`CHANGELOG.md:38-41`).

`career-legacy-deck-steps.spec.ts:212`, `:229`, `:267` -- **attribution excluded but base not proven.** The three cases fail at `:224`, `:248` and `:272`; the spec and `Deck.vue` are unchanged between `15f7ee1b` and `2.0.2`, but no clean base run exists. They are not upgraded to PRE-EXISTING.
## Open questions register (absorbed 2026-10-22)

**The owner answers here; the plan carries only a gating table.** These are the decisions that are still open after the D1 amendment pass, the 2.0.1/2.0.2 releases and the primitive reconciliation. Nothing in this file is answered, and no agent may answer one.

**Absorbed from `OPEN-QUESTIONS.md` 2026-10-22.** In the body below, "this file" means this section. Edits follow `## Working conventions` above; the absorption tree is `## Absorption tree (historical, closed 2026-10-22)` below.

**Four namespaces, additive, never renumbered:**

| prefix | owner | state |
|---|---|---|
| `FQ-nn` | the plan's questions, opened by D1 and the passes after it | **FQ-01…FQ-14, FQ-16, FQ-17, FQ-18** |
| `BQ-nn` | the fix pass's questions | **BQ-01a, BQ-01b, BQ-01c, BQ-02, BQ-03** |
| `EQ-nn` | the reconciliation map's causes | **empty, reserved** |
| `CQ-nn` | D2's findings, to be assigned by D3 | **empty, reserved** |

**`FQ-15` is reserved and unused.** It was provisionally the `.slot-*` primitive conflict, withdrawn when that conflict turned out to have one defensible resolution (an existing token). The number stays empty so that any existing `FQ-15` reference resolves to nothing rather than to the wrong question. **Do not back-fill it.**

**How this file cites.** It cites **`FQ-nn` ids, not line numbers**, because the plan is being written concurrently and its line numbers move between reads. Where a line number is given it is marked with when it was measured. `FQ-01…FQ-18` remain the plan's questions; this file states each **once, in decision-ready form**, and the plan carries the full argumentation and history. **The owner's answer lands in the plan** (the FQ register), not in two places — except `BQ-` items, whose only home is this file.

**One reconciliation, recorded rather than hidden.** `FQ-18` was added after the plan register's head note said "the nine questions below". The plan now carries a dated count correction beside that head note; this file carries the question itself. The two files therefore both mention `FQ-18` — the plan for the ruling history, this one for the decision — and **that duplication is deliberate and bounded**: it is the id and the options, not two copies of the argument.

---

### FQ-01 … FQ-05 — the primitive layer's five causes

These five are the ones the plan's rule makes **measurable**: an FQ is answered when its named assertion passes. That criterion has no teeth until the pair list is extended — see **FQ-18**, which the owner should rule on first. Full argumentation: plan §"Open owner questions (FQ register)", `FQ-01`…`FQ-05`.

#### FQ-01 — `--rule-soft`: promote it, or re-spell every use to `--color-rule`?
Eight primitives (`.card`, `.tile`, `.tag`, `.type-chip`, `.warn`, `.info`, `.srow`, `.savebar`) use a hairline the app does not have, referenced **23 times**.
- **A — promote `--color-rule-soft` (`#D9D8E2` / `#3A3A44`).** Keeps the set's soft look; ships a card boundary at **1.37:1** light / **1.34:1** dark, failing WCAG 1.4.11's 3:1 and contradicting the existing `rule on page` row. `DESIGN.md` §2.3's "borders `1px --color-rule`" would need amending to declare the edge decorative.
- **B — re-spell to `--color-rule` (`#84828C` / `#6E6C73`).** Boundary clears **3.46:1** / **3.88:1**, no new token, no doc change; every border darkens, so the grid reads harder than the set draws it.
- **Lands in:** `DESIGN.md` §2.1 + §2.3, then `app.css` `@theme static`. **Blocks Phase 2.**

#### FQ-02 — may selection state be a `box-shadow`?
`.pickcard` and `.subnav-item.is-current` draw selection with an inset `box-shadow`; `DESIGN.md` §2.3 makes `box-shadow` a house invention and gives selection a boundary token.
- **A — re-spell to `--color-pick-line` at 2px (or `outline`).** No new token; the boundary already measures **6.24:1** / **8.46:1**; the marker becomes a line.
- **B — add a shadow-based selection role to §2.3.** Preserves the drawing; opens an elevation path for state that the overlay-only rule exists to prevent.
- **Lands in:** `DESIGN.md` §2.3. **Blocks Phase 2** (both primitives).

#### FQ-03 — may a sticky save bar carry elevation?
`.savebar` carries `box-shadow:var(--shadow-overlay)`; §2.3 permits elevation for overlays only, and a sticky bar is none of the four.
- **A — drop the shadow; keep the bar flat.** Consistent with the rule; loses the "this floats above the page" cue.
- **B — add the sticky save bar to §2.3's overlay list as a fifth member.** Keeps the cue; the list stops being a closed enumeration and every future sticky surface inherits the claim.
- **Lands in:** `DESIGN.md` §2.3. **Blocks Phase 2.** *Its criterion is not a contrast ratio — see FQ-18.*

#### FQ-04 — what ink does a risk-filled badge carry in dark?
`.state-error .ic` uses `#fff` on `var(--color-risk)`: **10.89:1** light, **2.44:1 dark** (computed; dark `--color-risk` is `#FF7E8C`).
- **A — add `--color-on-risk`** (`#FFFFFF` / `#121013`, the `on-chrome` pattern). One token, one G-18 row, theme-safe.
- **B — keep `#fff` and restrict the icon to light.** No new token; ships a control that works in one theme, which G-18 forbids.
- **Lands in:** `DESIGN.md` §2.1 + `app.css`. **Blocks Phase 2** (`.state-error`).

#### FQ-05 — mint seven stat-mark colours, or re-point the type chips?
`.stat-*` uses eight literal hexes; `.stat-sp` duplicates `--color-sp-ink` (reuse), leaving **seven** to promote. The app already carries `--color-type-{speed,stamina,power,guts,wit}` for support-card chips with **different values** (`#3FA3F2` vs `#2F6BC4`).
- **A — two families with distinct names.** Seven new tokens, all clearing 3:1 on `raised` (4.24–6.23:1 computed); four names differ only by prefix, so a reader must know which "speed" they see.
- **B — one family, type chips re-pointed at the stat colours.** No duplicate nouns; the support-card chips change hue — a visible regression on a shipped screen.
- **Lands in:** `DESIGN.md` §2.1 + `app.css`. **Blocks Phase 2.**

---

### FQ-06 … FQ-14 — the layer, the motion, the docs and the deliberate absences

Compact form; the plan's block for each carries the argument.

| id | question | A | B | blocks |
|---|---|---|---|---|
| **FQ-06** | how does a ported primitive address tokens at all? | re-spell every `var()` | declare ~52 unprefixed aliases (doubles the token surface) | Phase 1 step 2, Phase 2 |
| **FQ-07** | motion: the set animates layout and the ported check *requires* it | drop the transition and that assertion | amend MOTION 2 with a sidebar-resize tier at `150ms ease-out` | Phase 1 step 1, Phase 2 |
| **FQ-08** | the guards that die with `UraDuelPanel` / `UraMilestoneTracker` | move the assertions onto the replacement | retire them as an owner-approved test deletion | Phase 4 step 5 |
| **FQ-09** | three `DESIGN.md` §2.1 rows disagree with the shipped values, and two doc versions fail their own claim | correct the doc to the shipped values | correct `app.css` to the doc (ships 3.14:1 and 1.18:1 — cannot pass G-18) | no phase; Phase 2 should not start from the table uncorrected |
| **FQ-10** | does `SCREEN_SPEC.md` §4.5 get amended for the `review-detail` page? | amend §4.5, re-scope the error surface and `ReviewFormAccessibilityTest` | keep the inline detail; `review-detail` becomes a documented divergence and ADR-0028 D6's "one behaviour change" becomes zero | Phase 4b |
| **FQ-11** | what does the costume-form strip control, and what does the table render while active? | strip is a location control; table lists all forms with the active row marked | strip filters the table; other forms become invisible | Phase 4a and 4b |
| **FQ-12** | what happens to the five components with no production call site? | mount or delete per component, per `SCREEN_SPEC.md` §7-13 | leave all five held, as §7-13 did for its own remainder | nothing (the phase body no longer deletes them) |
| **FQ-13** | does `help` become a real screen? | give it a route and an `SCR-*` row | keep it a set-only annotation, said so in the set README | Phase 6's README wording |
| **FQ-14** | is `@vue/compiler-sfc` promoted to a direct dependency? | promote it to `devDependencies` | name the pin and resolve through the plugin's re-export | Phase 7 step 3 |

---

### FQ-16 … FQ-18 — registry completeness and the gate's mechanism

#### FQ-16 — do the three Snapshot screens get rows and §4 sections, or rows only?
`SCREEN_SPEC.md` §7-25 records that the Snapshot screens appear in no §3 row and no §4 section, and calls it an omission rather than an exclusion. Phase 4a now draws all three.
- **A — rows and §4 sections land with the pages, in one commit.** Closes §7-25 properly and keeps the file's own convention (every registry row has a §4 section). Cost: three owner-written §4 sections — state tables, accessibility, navigation — which this plan may not author.
- **B — rows only, §4 sections deferred under a dated §7-25 note.** The registry stops claiming a screen set that excludes three shipped screens, at the cost of three rows pointing at nothing, a shape no other row has.
- **Lands in:** `SCREEN_SPEC.md` §3, §4 and §7-25's status line — owner-written. **Blocks Phase 4a** (the default must be known before the commit that draws the pages) and Phase 6's registry-totals correction.

#### FQ-17 — are Dashboard and Run detail deliberately undrawn, or is the set short two pages?
Measured: the set has **no `dashboard.html`** and no run-detail page, so **45 of 50 registry rows have a page** — 5 undrawn, of which Phase 4a draws three (Create, SaveVeteran, RacePlanner). `SCR-CAR-001` Dashboard and `SCR-RUN-003` Run detail remain, and neither is in any phase's scope.
- **A — draw both in Phase 4a.** The registry claim becomes true for all 50 rows; cost is two more set pages (57 not 55), and **Run detail duplicates the cockpit** — the app redirects `runs.show` to the cockpit, so the set would draw a screen the app no longer serves.
- **B — declare both deliberately out of scope, in the set's README and `SCREEN_SPEC.md`.** No new pages; the claim becomes "48 of 50 drawn; Dashboard and Run detail recorded as out of scope", which is truthful. Cost: the set stops being a complete rendering of the registry.
- **Lands in:** the set's `README.md` + `SCREEN_SPEC.md` §7-25/§7-27 status lines — owner-written. **Blocks Phase 6's README correction**; does **not** block Phase 4a.

#### FQ-18 — which phase extends the gate's pair list, so that "answered" can mean "the assertion passed"?

> **The brief the owner needs before ruling — one paragraph, then the choice.**
>
> The plan and the map both state a rule that makes this register measurable: *an FQ is answered when its named assertion passes*. Checked against the gate as it exists, **zero of the five causes has a corresponding assertion.** `FQ-01` is partly covered (`rule on page` passes for `--color-rule`, but the failing `--rule-soft` boundary is not in the list at all, so the gate is blind to it); `FQ-02`, `FQ-04` and `FQ-05` name pairs that do not exist; `FQ-03` is not a contrast fact and its instrument (the ported `box-shadow` check) does not exist yet. So the acceptance rule is **a rule without teeth**, and **no phase currently owns extending the pair list** — which is the whole of this question.
>
> **The choice is not "which token for FQ-01". It is: does the gate, as it is currently structured, constitute an answer at all?** If the answer is option A or B, then FQ-01…FQ-05 are answerable today, in their present form. If it is option C, each FQ becomes a **two-part ruling**, and the owner rules on the **mechanism** as well as the value.
>
> Evidence table and full options: plan §"Open owner questions (FQ register)", `FQ-18` (measured at 960 plan lines on 2026-10-22 20:25; cite the id, not the line).
>
> **Attribution.** The gate ran as part of agent 1's Pest suite at 2.0.1 (`1953 passed`, block `3 passed / 184 assertions`); the result was recorded in the map by agent 2's absorption pass. Neither this file nor the map claims agent 2 performed a second, independent run.

- **A — extend the pair list first, as a Phase 2 precondition.** The owner rules against an existing assertion. Cost: pairs whose tokens do not exist yet (`on-risk on risk`) go **red by construction**, so Phase 2 opens with a **second baseline** of expected failures that are not FQ-01…FQ-05 failures; and the gate's scope grows before Phase 2's own changes land.
- **B — rule first, extend the pair list as part of each answer.** The owner rules on the merits; each answer becomes two actions (rule, then add the pair). Cost: "answered" slips until the pair exists, so each FQ has a provisional period in which its criterion is a proposition rather than a measurement — **a judgement dressed as a measurement**.
- **C — split each FQ into two decisions taken at once: which token, and which pair asserts the answer.** The pair is added in one Phase 2 pass, and FQ-02/04/05 go green on that pass. Preserves the criterion without reddening the gate early. Cost: each FQ becomes a two-part ruling, and the owner must answer the **instrument** as well as the **value** — a different register from "which token".
- **Recommendation: C.** A reddens the gate for Phase 2 reasons that are not Phase 2 failures; B makes "answered" a fiction; C separates the instrument from the value, and each half is complete on its own terms.
- **Lands in:** the plan's FQ-register head (extending the A28 statement) and `primitive-reconciliation.md` §4.1 — both state the acceptance rule, so both must carry the mechanism. **Blocks Phase 2's opening.**

---

### BQ-01a / BQ-01b / BQ-01c — the `title`-only absence sites

`DESIGN.md` §5(a) rules that the reason behind an absence must be reachable without a mouse; a `title`-only reason is migration debt. Measured inventory: **74 sites**, which split three ways, and the split is the question.

#### BQ-01a — the 40 mechanically migratable sites
Each already has its sentence; only the channel changes (a `title` becomes `AbsenceValue`'s disclosure or visible prose).
- **A — schedule a slice.** Mechanical, low risk, and each site can assert its accessible name.
- **B — batch with BQ-01b's design change.** One pass, one review, but a mechanical change rides on a design decision and cannot land first.
- **Lands in:** `DESIGN.md` §5(a) is unchanged; the work is a slice in the plan's Phase 2/3. **Blocks nothing.**

#### BQ-01b — the 29 prose-embedded sites
Each sits inside a sentence; `DESIGN.md` §5 records that `<details>` is flow content and belongs in a field or cell, so moving these **restructures the sentence**, which the same section calls a design change rather than a migration.
- **A — make the design change.** Rewrites 29 sentences into rows; honest to the rule, and the densest rows lose their prose.
- **B — amend `DESIGN.md` §5 to carve them out** (a sanctioned exception for prose-embedded absences). Cheaper; the carve-out grows every time a site is judged "prose-embedded", until the rule no longer applies.
- **Lands in:** `DESIGN.md` §5 (B) or the plan's copy/design slice (A). **Blocks BQ-01a's scheduling** if A is chosen.

#### BQ-01c — the remaining 5 duplicate-channel sites
Neither mechanical nor prose-embedded: they carry **both** a `title` and a visible reason, which is one fact stated twice — the defect class the one-channel rule exists to prevent.
- **A — drop the `title`, keep the visible reason.** Cheapest, conforms, and no design change.
- **B — treat them as a `DESIGN.md` §5 case study and record the pattern.** More work; produces a rule for the next occurrence.
- **Lands in:** the sites themselves + `DESIGN.md` §5 if B. **Blocks nothing.**

---

### BQ-02 — the two `AGENTS.md` lines that contradict `DESIGN.md` §5(a)

`AGENTS.md` (Copy, and §13 UI rules) says an unrecorded value renders "`N/A` with a `title`". `DESIGN.md` §5's 2026-10-17 amendment supersedes that channel.
- **A — apply the correction**, citing `DESIGN.md` §5 as the overriding source, in `AGENTS.md`'s own dated-note format.
- **B — leave it.** Consequence, and it is mechanical: `UiHygieneTest` was written against the `AGENTS.md` wording and had to be **widened** to accept the design contract, so **any future pass that reads `AGENTS.md` first re-introduces the hover-only idiom and reds that test** — and will conclude the test is wrong.
- **Lands in:** `AGENTS.md` Copy + §13. **Blocks nothing formally**, but it degrades every future pass. The file is another session's uncommitted work, so agent 2 has not applied it.

---

### BQ-03 — the Preflight focus path

Three options, and **the third is not one of the original two.** `aria-disabled` alone does not cancel activation (so the control still activates); native `disabled` alone cancels activation but makes the "focus the first missing row on an attempted click" path unreachable.
- **A — `aria-disabled` + a click guard.** Keeps the focus path; the control remains activatable by assistive technology and by keyboard `Enter`, which is a truthfulness defect.
- **B — native `disabled`.** Honest to §5(b); the focus-on-first-missing affordance dies.
- **C — native `disabled` + a visible reason beside it + a separate, non-button affordance that moves focus.** Satisfies both contracts: the control does not activate, the reason is visible, and a real affordance carries the focus path. **Not one of the original two.**
- **Lands in:** `SCREEN_SPEC.md` SCR-CAR-010 (owner-written) + `Preflight.vue`. **Blocks nothing**; Phase 4 does not touch Preflight.

---

### ADR-0019's status

`docs/adr/0019-…` is `Status: **Proposed**` since 2026-10-04 with no owner ruling recorded, while releases have already been labelled against it in practice (`2.0.1`, `2.0.2`).
- **A — accept ADR-0019.** Phase 6 step 2's "`VERSION.md` bump by the Architect per ADR-0019" becomes true as written.
- **B — correct its status** to "labelling is owner-directed per release", which is what the release entries already record.
- **Lands in:** `docs/adr/0019-…` (its status field only). **Blocks Phase 6's opening** — the plan requires Phase 6 to name its labelling authority before the phase opens, and this is the naming.

---

### Gate coverage — and D-2, the G-47 white-letters assertion

The gate is implemented and committed (`g18ContractPairs()` 31 pairs, 9 grade badges, `toHaveCount(81)`, no stubs). It has four known coverage gaps: **no ≥1.2:1 threshold**, so the two rows the contract requires — `brand-accent on brand-chrome` and `scrim-overlay on page` — cannot be expressed at all; and **`--color-risk`, `--color-pick-line`, `--color-ring`** carry stated ratios that no pair asserts.

**Gate-coverage question** — is closing these a ruling, or Phase 2 work?
- **A — a ruling-needed item.** The owner decides what the gate must assert, and the 1.2:1 threshold becomes a token-layer decision.
- **B — a Phase 2 checklist item.** The phase adds them as work under the existing rule, with no ruling; the pair list grows as part of the primitive layer.
- **Lands in:** the plan's Phase 2 gate + `primitive-reconciliation.md` §5. **Blocks Phase 2's exit** (not its opening).

**D-2 — the G-47 white-letters assertion.** `DESIGN.md` §2.1 records that white letters are **banned** on the light grade fills, and the gate's failure message repeats the ban ("white on these fills measures 1.20-1.44"), but **nothing asserts that white fails** — the gate only proves the shipped ink passes. So the ban is enforced only by implication.
- **A — add an assertion that `#FFFFFF` on each `--color-grade-*` measures below 4.5:1 in light.** The ban becomes tested rather than implied; the assertion is a *negative* one, which is unusual in this file and needs its own message so a future reader does not read it as a defect.
- **B — record the ban as documented-only**, in `DESIGN.md` §2.1, and do not assert it. Cheaper; the ban remains a rule with no teeth, which is the state the plan's own "rule with teeth, or no rule" principle disfavours.
- **Lands in:** `tests/Feature/DesignTokensTest.php` (A) or `DESIGN.md` §2.1 (B) — both another session's files. **Blocks nothing.**

---

### Groups A–D — the four documentation questions

These are not `FQ-` or `BQ-` numbered: they concern the plan's own text and were not opened by either question stream. They stay as **Groups** until D3 assigns `CQ-nn`.

#### Group A — sanction the four peer-rewritten sites?
Six sites in the plan were rewritten **in place** by a concurrent session on 2026-10-22 (status vocabulary, §6 heading, §6 list→table, Phase 0 preamble, Phase 5 heading, and the removal of the old `Open questions for the owner` list). Six dated site notes record the originals and mark the ruling **pending**.
- **A — sanction.** Keep the text; the site notes change from "sanction pending" to "sanctioned". All five removed questions are covered by rulings 1, 4, 10 and 11, and their residuals by FQ-10, FQ-11 and A13 — **no loss**.
- **B — do not sanction.** Restore each original sentence as operative text; the §6 table and both retitles revert, and FQ-10/FQ-11 then describe questions against text that no longer presumes those outcomes.
- **Lands in:** the plan's six site notes + the A29 record. **Blocks nothing**, but Group B's answer changes what a sanction is sanctioning.

#### Group B — keep the `ruled` status word?
The plan's status vocabulary gained `ruled` ("an owner decision recorded in this document"). The repo's precedence puts **ADRs above plans**, so a plan that can assert "ruled" claims authority it does not have.
- **A — keep**, bounded to the five 2026-10-22 rulings that `ADR-0028` carries in full under its `## Owner rulings promoted from the plan` section, with a sentence saying the plan asserts no other.
- **B — revert (recommended).** The vocabulary returns to `proposed` / `verified` / `open question`, and the plan's `Owner rulings` table becomes a **citation** of ADR-0028's section rather than a statement of rulings. `ADR-0028` is already self-contained under A25, so the plan's copy is the weaker duplicate.
- **Lands in:** the plan §Status vocabulary + the Owner-rulings heading. **Blocks nothing**; take it in one commit with Group A, since the two interact.

#### Group C — Phase 0's "three decisions" → "two"
**Verified, not a decision.** Phase 0's body numbers exactly two items (the ADR; the `DESIGN.md` §3 refresh); the third was "owner ruling on the six open items", discharged because the Owner-rulings table records all five as ruled. A dated note at the site records the discharge. **No option to weigh.**

#### Group D — Phase 5's retitle
**Verified, not a decision.** "The mock catalogue graduates to the app" → "Freeze the catalogue" implements **owner ruling 5**, and the phase body is a freeze plan. A dated note at the site records it. **No option to weigh.**

---

### EQ- and CQ- — reserved

**`EQ-nn`** is reserved for the reconciliation map's causes. It is empty because the map's five causes were mapped onto `FQ-01`…`FQ-05` rather than given their own numbers; the prefix stays reserved so a future cause that is *not* an FQ can be filed without a renumber.

**`CQ-nn`** is reserved for D2's findings, to be assigned by D3. It is empty because D2 has not run: D2 is gated on a committed, settled tree, and D3 is gated on D2.

**What `CQ-` covers, and how it differs from `EQ-`.** `EQ-` is a cause in the reconciliation map: a divergence between a set primitive and an app token. `CQ-` is any other finding a reconciliation pass raises and cannot resolve on its own: a document that contradicts the tagged state, a fourth verdict shape the triage tree did not anticipate, or a structural problem in the plan's own append-only form. A `CQ-` is not a design question and does not enter `FQ-` or `BQ-`; it blocks a phase only when its own entry says so. Numbering is additive and never renumbered: the next new `CQ` is `CQ-01`, and `D3` assigns it. This section is the plan's register; there is no separate `OPEN-QUESTIONS.md`.


## Absorption tree (historical, closed 2026-10-22)

| item | state |
|---|---|
| Task 1 branches A/B/C | awaiting |
| Task 2 branch A | fired-and-executed |
| Task 2 branches B/C | untriggered |
| Task 3 four-verdict shape | asserted |
| Task 4 | closed |

**Absorbed from `AGENT-1-RETURN.md` 2026-10-22.** This tree is maintained under `## Working conventions` above. In the body below, "this file" means this section.

**Prep only. No note in this file is written.** Each branch states the exact note agent 2 writes when agent 1's four-task prompt returns, and where it lands. The point of the file is that **Task 2 branch A must be writable the same day**, without reconstructing it from a chat log.

> **Status note, 2026-10-22 20:26 — Task 2 branch A has already fired and been absorbed by a concurrent session.** This file was drafted against a tree where agent 1 had not returned. By the time it was written, a parallel agent-2 session had recorded the run in `primitive-reconciliation.md` (the run itself was part of agent 1's Pest suite at 2.0.1; agent 2 recorded the block's result): the gate "was run at `da15a5ba`: **3 cases, 184 assertions, exit 0, both themes, 0 failures**", and — in the same session's words — "Green, and green is the confirmation rather than the contradiction: each cause's pair is absent from the list, so the run could not have failed on any of them." That is **branch A**, executed as the branch specifies: the null result recorded as the finding, with the missing-pair diagnosis. **So do not apply Task 2 branch A's template again**; treat it as history and check the plan and map for the peer's note first. Branches B and C never fired, and Task 1's three branches remain untriggered — those are this file's live value.

**Read this first.** The plan is being written concurrently, so **every line number below is a measurement, not a location**. Before writing any note, locate its anchor **by content** (the quoted sentence or the `FQ-nn` id), not by the number. Line numbers below were measured on 2026-10-22 with the plan at 960 lines; treat them as approximate.

**Two standing constraints on every branch.** (1) Every note is a **dated note beside the original sentence** — never an in-place rewrite. (2) Agent 2 does not write the *finding*: if a branch's note would assert what agent 1 measured, agent 2 quotes agent 1's output and attributes it, rather than re-deriving it.

---

### Task 1 — the Pest run's incomplete/skipped counts

The question: did the D-288/G-18 browser block **execute** in the release's Pest run, or report as incomplete?

**Anchor for all branches:** the plan's M-13/A1 block — its "The sequencing requirement…" paragraph and the "Superseded 2026-10-22 … gate verified live at `da15a5ba`" note that follows it. Locate by the string `The sequencing requirement, stated conditionally` (≈ plan L419).

#### Branch A — 0 incomplete, 0 skipped, block included
**No note.** The plan's A28 note already says the gate is *implemented and committed*; a clean run establishes the *run* half, and the plan does not claim otherwise anywhere. **Record in the pass report only** that the run half is now established, with agent 1's output quoted.

#### Branch B — N incomplete / N skipped, the block among them
**Write one dated note**, immediately after the plan's "The sequencing requirement…" paragraph:
> **Qualified 2026-10-22 (post-release absorption): the block reported *incomplete*, not failed.** The `2.0.1`/`2.0.2` Pest run reported **N incomplete / N skipped**, and the D-288/G-18 browser block was among them — so the gate is implemented, committed, and **did not execute** in that run. The paragraph above is therefore right that the gate "must be true before Phase 2 opens" and right that a green run is required; what it does not say is that **the run itself has still not happened**. The clause "did not run in the sandbox for a different reason" earlier in this block is **historical**, not false: it described the sandbox that denied the plugin's write, and the present reason is the same denial, unchanged. Phase 2's opening gate run is therefore still owed, and it remains an *event the plan schedules*.
**Also qualify** the A28 header note's phrase "implemented **and run**" with a pointer to this note, since that phrasing is the one a reader will take as settled.

#### Branch C — the block did not execute for a different reason
**Write one dated note** at the same anchor, naming the reason **in agent 1's words**, with the output quoted:
> **Cause recorded 2026-10-22 (post-release absorption): the D-288/G-18 block did not execute, and the reason is X.** Agent 1's run reports: "…". This is not the sandbox write-denial the earlier note records; it is a different cause and is recorded here so a later pass does not re-diagnose it as the same one. Phase 2's opening gate run remains owed.

---

### Task 2 — the G-18 gate's result

**Every branch also updates `primitive-reconciliation.md` §4.1** — the executable-register table's "state today" column, whose five rows currently read: cause 1 "passes 3.46/3.88 for `--color-rule` … not in the pair list at all"; cause 2 "not in the pair list — a coverage gap"; cause 3 "n/a; judged by rule 34"; cause 4 "fails by construction: 2.44:1 computed"; cause 5 "not in the list; computed 4.24–6.23:1 once minted". The update replaces each cell with the **measured** value and the run's date. The table is at map §4.1, located by the heading `### 4.1 Each cause is a named assertion`.

#### Branch A — green, 0 failures — **the most important branch in this file**
This is the plan's own escape clause firing. **Write a dated note beside the M-13 block**, quoting the plan's prediction verbatim so the note is self-validating:
> **The gate ran green on 2026-10-22, and that is the finding this plan predicted.** The M-13 note above says, in terms: *"A Phase 2 opening run that is green is therefore itself a finding: it would mean the pair list does not cover the conflicts the map records."* The `2.0.1`/`2.0.2` run reports **0 failing pairs** across 31 pairs and 9 grade badges. So the pair list does **not** cover the six conflict causes: `--rule-soft`'s failing boundary is not in the list at all (so the gate cannot see it), `pick-line on raised` is absent, `on-risk on risk` does not exist, and the seven `stat-* on raised` rows are absent. **A green gate is therefore not evidence that the token layer is correct; it is evidence that the gate's scope is narrower than the contract's.** Closing that gap is **FQ-18** (which phase extends the pair list), and until FQ-18 is ruled, no FQ in the register can be "answered" by this gate.
**Cross-reference FQ-18 in the same note** (by id, not line). **Do not** record this as a pass — record it as the predicted null result.

#### Branch B — red on FQ-01 and FQ-04, as predicted
**Write a dated note** at the same anchor:
> **The register became executable on 2026-10-22: the gate's failing pairs are its first measurements.** The run reports exactly the two failures this plan predicted, and they are the register's own causes: **`FQ-01`** — the `--rule-soft` card boundary, **1.37:1** light / **1.34:1** dark against the 3:1 a non-text boundary owes; and **`FQ-04`** — `#fff` on dark `--color-risk`, **2.44:1** against 4.5:1. Failing pairs, verbatim from the run: "…". FQ-01 and FQ-04 are therefore **answered the moment their assertion passes**, and this note is the baseline that makes a later green attributable to a specific ruling rather than to drift.

#### Branch C — red on anything not FQ-01 / FQ-04
**This is a plan claim being wrong, and agent 2 must not write the addendum.** The branch's job is diagnosis, not authorship:
1. **Identify which claim** the failure contradicts — a stated ratio in `DESIGN.md` §2.1 or the map §2/§3, a pair membership claim, or a threshold claim.
2. **Name the addendum owed**, in one line, in the pass report — never in the file.
3. **Write only a placeholder-free, factual note** at the anchor:
   > **Unexpected gate failure recorded 2026-10-22:** the run reports failures outside the register's predicted pair set — "…" verbatim. This is not explained by FQ-01…FQ-05 and is referred to the owner; no plan claim has been adjusted here, because which claim is wrong is the run's output, not this pass's inference.
4. **Do not** correct a ratio, a pair or a threshold on the strength of one run.

---

### Task 3 — browser triage of five outstanding assertions

The five: `dashboard.spec.ts:147`; `career-cockpit.spec.ts:566`; `career-legacy-deck-steps.spec.ts:212`, `:229`, `:267`. Agent 1 proves each **PRE-EXISTING** or **CAUSED-BY-FIX** against `15f7ee1b`.

- **PRE-EXISTING** → **no plan change.** The plan makes no claim about these assertions' current state.
- **CAUSED-BY-FIX, and the fix is correct** → **no plan change.** The plan's release claims are about the token layer and the amendment set, not about individual specs.
- **CAUSED-BY-FIX AND FIX-WRONG** → **a plan change is owed and its wording is not yet determinable.** In that case the plan's **2.0.1 release claim** ("a fixes-only release: it corrects semantics and accessibility and adds no capability"), its **"Superseded" note** about the sandbox, and **Phase 2's premise that the current token system is correct** all need a dated note. The note's text cannot be drafted here, because it depends on *which* fix is wrong and *how* — so the branch's output is: stop, report, and ask for the specific verdict plus agent 1's evidence. **Do not** write a note that guesses the scope of a wrong fix.

---

### Task 4 — the tag decision

- **Leave `2.0.1` on `da15a5ba`** → **no plan change.** The plan does not claim which tag it is at.
- **Cut `2.0.2`** → **no plan change to any claim**, with one optional one-line addition: the plan's Phase 6 semver-precedent passage cites `2.0.1` as the first release labelled under `ADR-0019` while that ADR is still `Proposed`. If a second release is cut the same way, that passage's "first instance" framing becomes "two consecutive instances", which is *stronger* evidence for the `ADR-0019` status question (see `## Open questions register`, "ADR-0019's status") — so the addition is a courtesy, not a correction. Locate the anchor by the string `Semver` / `2.0.1` in Phase 6 and the Ruling-1 bullet; if it cannot be found by content, **do not add it** — a note that cannot be anchored is a note that will be appended in the wrong place.

---

### The four-branch summary, for the day agent 1 returns

| agent 1's task | notes to write | where |
|---|---|---|
| 1 — Pest incomplete count | **B or C only**; A writes nothing | plan, M-13 block (locate by `The sequencing requirement`) |
| 2 — G-18 result | **A, B or C** — always one; **plus** a map §4.1 update on every branch | plan M-13 block; map §4.1 `state today` column |
| 3 — browser triage | only if **CAUSED-BY-FIX AND FIX-WRONG** — and then stop rather than draft | plan (scope undeterminable) |
| 4 — tag decision | none; optional one-liner if `2.0.2` is cut | plan Phase 6 semver passage |

**The single most valuable pre-written sentence in this file** is Task 2 branch A's: the plan predicted that a green gate would mean the pair list is too narrow, and if that prediction is confirmed, the note above says so in the plan's own words on the day it happens.


### Revised phase map

| Phase | What it does | Depends on |
|---|---|---|
| 0 | ADR + `DESIGN.md` §3 refresh | the rulings above |
| 1 | Check harness ported; token sets reconciled; debt list retired | Phase 0 |
| 2 | The ~40 primitives in `app.css`; `DesignTokensTest` extended; the primitive gallery that screenshots baseline | Phase 1 |
| 3 | Leaf-first component migration (97 components, one commit each) | Phase 2 |
| 7 | **Agent ergonomics** (runs alongside Phase 3): the `AGENTS.md` ruleblock, the screenshot diff, the scoped codemod, the two edge-case galleries | Phase 2 |
| 4 | Close the gaps: `FormTabs` restored, flash banner promoted, **six screens drawn**, `review-detail` becomes a route, five dead components removed | Phase 3 |
| 5 | **Freeze the catalogue**: date-stamp it, keep `data-check.mjs`, state the ageing rule | Phase 4 |
| 6 | Level 3 sweep, `CHANGELOG`/`VERSION`, `AGENTS.md` corrections (including removing the Phase 7 ruleblock) | Phase 5 |

> **Amended 2026-10-22 (correction pass, D1 / authoritative A4b, A12 at this site, A10 at this site).** Three rows of the map above are corrected, and the map is declared free of forward edges. The table stands as written.
>
> **A4b — row 2's "~40 primitives" is the measured figure, at two levels.** Row 2 must read: *"The **21** primitive families in `app.css` — **4 reuse, 10 re-spell, 6 conflict, 1 drop** — plus the **7 promoted tokens** that belong to the `.stat-*` family; `DesignTokensTest` extended; the primitive gallery taken in Phase 7."*
> The two levels are stated separately because they answer different questions: the family buckets say **what to do with each primitive**, and the promotion count says **how many new tokens the layer costs**. They are not additive — 21 families + 7 tokens would double-count `.stat-*`, which is one family requiring seven values. The **conflict bucket stays visible** because FQ-01…FQ-05 are its members; a total that hides it cannot be reconciled with the FQ register, which is why the verification's proposed wording (which dropped CONFLICT and summed to 22) was withdrawn.
>
> **A12 (site 3 of 3) — row 0's `DESIGN.md` §3.** Row 0 must read: *"ADR accepted + `primitive-reconciliation.md §7` written (D2) + `DESIGN.md` §3 citing it"*. Phase 0's deliverable is three artefacts, not two, and the classification lives in `primitive-reconciliation.md §7`.
>
> **A10 (at this site) — row 4 no longer removes components.** "five dead components removed" must read *"component dispositions per `SCREEN_SPEC.md` §7-13, owner-ruled"*. Nothing is deleted until FQ-12 is answered; D1 measured that all five are test-referenced, so the removal is on the owner's side of the line, not the phase's.
>
> **No forward edge remains.** Phase 1 owns `tools/ui-screenshot-diff.mjs` (A2), so Phase 2's gate no longer reaches into Phase 7; Phase 7's entry gate is satisfiable before Phase 3 begins and its exit gate at the end of the migration (A3); Phase 4's two sub-phases gate independently (A7); and Phase 6's removal of the ruleblock depends only on Phase 5. The only remaining cross-phase relation is Phase 7 running *alongside* Phase 3, which is a schedule overlap, not a dependency.

> **Corrected 2026-10-22 (close-out pass, D1 / supports A4b as restated above; VD-04).** The figure in the A4b note immediately above — "4 reuse, 10 re-spell, 6 conflict, 1 drop" — was wrong in two buckets, and the same figure is wrong in `docs/proposals/primitive-reconciliation.md` §2's totals line and §4's tabulation. All three now carry the statement below. The note above stands as written.
>
> **The measured figure, at two levels that are not interchangeable:**
>
> - **FAMILIES — 21**, one exclusive verdict each: **6 CONFLICT** + **11 RE-SPELL** + **3 REUSE** + **1 DROP** = 21.
> - **CAUSES — 5 questions.** The 6 conflict families carry only **4 distinct house-rule conflicts**, because two pairs share a cause: `.card` and `.srow` are one cause (`--rule-soft` is absent from `app.css`, and both fail at the same 1.37:1 for the same reason), and `.pickcard` and `.subnav` are one cause (`box-shadow` used for selection state). Those four are `FQ-01`…`FQ-04`. **`FQ-05` is the fifth cause and is not a house-rule conflict** — it is the promotion decision that `.stat-*` forces at *value* level: 7 colour values with no token, plus the five-noun collision with `--color-type-*`.
> - **PROMOTED VALUES — 7**, all inside the single `.stat-*` family. `.stat-*` is **one family and it is REUSE** (every colour it paints reuses an existing token; `.stat-sp` is `--color-sp-ink` exactly), and it *additionally* contributes those 7 values. It is not a 22nd family, and the 7 are not additive with the 21.
>
> **Why the earlier figure could not be right.** It claimed "4 REUSE" while its own table showed two REUSE rows (`.trust-*`, `.mood-*`), reaching four only by counting `.band-*` — which is not a family row at all but a token-level observation from the map's §1 — and by splitting `.stat-*` in half. It claimed "6 CONFLICT" while its table labelled seven rows CONFLICT, reaching six only by merging `.card` with `.srow` without saying so. The bucketing was therefore mixing a family count with a cause count, which is exactly the confusion the two levels above exist to prevent.
>
> **`.slot-*` is resolved without a question.** The map records its fix — `--ink-faint` on `--sunken` measures 2.72:1, and re-spelling to the existing `--ink-muted` gives 4.57:1 with no new token and no `DESIGN.md` amendment. A conflict whose only defensible resolution is an existing token is a RE-SPELL, not an owner question, so it moved to the RE-SPELL bucket (11) and the cause count is 5, not 6. Filing it as `FQ-15` was considered and rejected: it would have asked the owner to choose against a contrast failure. **`FQ-15` is therefore reserved and unused**, and the register's next new question is `FQ-16`.

> **Corrected 2026-10-22 (post-2.0.1 reconciliation) — two rows of the map above, because the contrast gate is already built.** The map's row 2 ends "…`DesignTokensTest` extended; the primitive gallery that screenshots baseline", which treats the contrast gate as **Phase 2's own work**. It is not: the gate is implemented and committed at `da15a5ba` (31 pairs, 9 badges, `toHaveCount(81)`, no stubs). Corrected wording:
>
> - **Row 2 must read:** *"The **21** primitive families in `app.css` — **6 conflict, 11 re-spell, 3 reuse, 1 drop** — plus the **7 promoted token values** in `.stat-*`; the existing D-288/G-18 gate **run at the phase's head commit**; the primitive gallery taken in Phase 7."*
> - **Row 1's dependency is unchanged**, but its *content* loses a deliverable and gains nothing: the gate was never Phase 1's either. Phase 1 still owns the five ported checks, the token reconciliation and the debt list.
> - **The scheduled event, not a deliverable.** The plan now names **two scheduled gate runs** — one at Phase 2's head commit and one at its exit commit — and no phase in this map produces the gate. A reader looking for "who builds the contrast gate" should find the answer *"nobody: it is built; the plan consumes it"*, which is why this note exists rather than a new row.
> - **The consequence for Phase 0.** Phase 0's M-13 branch ("if a rewrite has not landed, sequence it before Phase 2 opens") is **dead**. Phase 0 no longer sequences gate work; at most it records the gate's baseline output.

---

*This document proposes the architecture and records the owner's scope rulings. It does not itself decide the architecture: the Phase 0 ADR, the `DESIGN.md` §3 refresh, and the gate evidence at each phase are what turn it into work.*
