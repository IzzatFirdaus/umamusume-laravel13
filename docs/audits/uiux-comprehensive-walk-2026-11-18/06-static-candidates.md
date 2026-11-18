Disposition: C-01 through C-04 were re-measured in Chromium, confirmed and fixed (see 01-findings.md).
C-05, C-06, C-08 and C-09 were not re-measured in this pass and are not findings.
C-07/C-10 were re-measured: the pager spans are non-focusable (tabIndex -1), so the 36px target is not an activation target; not filed.

# UI/UX Static Discrepancy Candidates — Trainer Desk

Date: 2026-11-18

**Status: CANDIDATE ONLY — expressly NOT verified in a browser; the parent agent will re-measure.**

Method: read-only static comparison of design documentation against the shipped frontend source. No browser rendering, interaction, or measurement was performed. No application or documentation file was modified; nothing was written outside this scratch file.

Sources compared:
- `DESIGN.md` (visual system, tokens, components, motion)
- `SCREEN_SPEC.md` §3–§4 (screen matrix and per-screen states)
- `docs/framework-ui-ux/design-framework-index-2026-10-21.md`
- `routes/web.php`
- `resources/js/layouts/`, `resources/js/components/`, `resources/js/pages/`

Ranking: by likely user-visible impact (information hierarchy, labels, control availability, empty/loading/error states, focus behaviour, keyboard paths, confirmation/feedback, responsive behaviour, visual consistency). Cosmetic nits without a documented rule are excluded.

## Candidates (ranked by likely user impact)

**Every row below is CANDIDATE ONLY. The implementation side is a source read; it is not a rendered observation. The "documented expectation" is a one-sentence statement of the binding doc rule, with its own file:line. Several expectations name these exact items as known migration debt, which strengthens the candidate rather than excusing it.**

### C-01 — Transfer Requests page title renders at the section-header tier
- **Implementation:** `resources/js/pages/Veterans/TransferRequests.vue:174` — `<h1 class="text-lg font-semibold text-ink-strong">Transfer Requests</h1>` inside the page body; the page fills no `#title` slot on `<AppLayout>` (`:169`), so this is the document's only `h1` at `text-lg` (18px) rather than the shell/`PageHeader` `text-3xl` (30px).
- **Documented expectation (one sentence):** The page title is the single `h1` at the Critical tier `text-3xl font-semibold text-ink-strong`, emitted by the shell's `#title` slot or `PageHeader`, and a page never repeats a title-level element in body copy (`DESIGN.md:368`, `DESIGN.md:358`, `DESIGN.md:394-398`).
- **Likely impact:** This screen's name reads the same size as its section headers and visibly smaller than every other page's title (broken information hierarchy).
- **Re-measure:** computed font-size of the `h1` at `/veterans/transfers` versus `/veterans`; count of `h1` elements; whether the shell title band is empty.

### C-02 — Career Timeline's turn-log table scrolls without a focusable, labelled region
- **Implementation:** `resources/js/pages/Career/Timeline.vue:259` — `<div class="overflow-x-auto">` wraps a `min-w-[44rem]` table (`:260`, seven columns) with no `role="region"`, no `tabindex="0"` and no `aria-label`; the two sibling wide tables that *do* carry all three are visible at `Runs/Import.vue:598` and `Legacy/Builder.vue:794`.
- **Documented expectation (one sentence):** The turn log is a wide table that scrolls horizontally inside a focusable region — `overflow-x-auto` with `role="region"`, `tabindex="0"` and an `aria-label` — so clipped columns are reachable by arrow keys and not by trackpad alone (`DESIGN.md:458-466`; the turn log is the flattened rail at `SCREEN_SPEC.md:208-212`).
- **Likely impact:** On a narrow viewport a keyboard-only Trainer cannot reach the clipped columns (Energy, SP, Momentum), and a screen reader gets no labelled scrollable region.
- **Re-measure:** at 320px and 768px, check the scroller's `role`/`tabindex`/`aria-label`, whether it is a Tab stop, and whether arrow keys scroll it.

### C-03 — Dashboard Energy and Team Rank absence reasons are title-only tooltips
- **Implementation:** `resources/js/pages/Dashboard.vue:389` passes `hint="No logged turn has recorded Energy…"` and `:396` passes the Team Rank `value_hint` into `Tile`; `resources/js/components/ui/Tile.vue:35` renders that reason solely as `:title="hint"` on the value div. (The resource tile at `Dashboard.vue:381` uses the same channel.)
- **Documented expectation (one sentence):** An absence reason must be reachable by keyboard and screen reader through `AbsenceValue`'s `<details>` summary or visible prose, and a `title`-only reason is superseded migration debt — with the Dashboard's Energy and Team Rank tiles named explicitly as the remaining instances (`DESIGN.md:893-899`, `DESIGN.md:960-965`).
- **Likely impact:** A keyboard or touch Trainer sees "not recorded" with no way to learn whether to go log a turn or stop looking.
- **Re-measure:** hover the `data-tile="energy"`/`data-tile="team_rank"` value; then Tab to it and check for any announced reason or disclosure affordance.

### C-04 — Review queue prints two provenance labels for one non-Fuzzy tier fact
- **Implementation:** `resources/js/pages/Review/Index.vue:199` renders `<span class="pill pill-neutral">No match</span>` whenever there is no suggestion; `:204-207` then renders `ProvenanceBadge` with `state="unknown"` from `badgeFor()` (`:72-75`) whenever the tier is not `Fuzzy`, so a None-tier row shows both labels for the same fact.
- **Documented expectation (one sentence):** `ProvenanceBadge` is the single owner of the state word, so the review queue keeps the badge and drops the `StatusPill` ("No match") when the tier is not `Fuzzy` (`DESIGN.md:974-980`).
- **Likely impact:** The row tells the Trainer "No match" and "Unknown" about the same tier, reading as two conflicting verdicts.
- **Re-measure:** filter `/review` to a candidate with no suggestion and `match_tier = None`, then count provenance-shaped labels in that row.

### C-05 — Spark summary star-count absence gives its reason only in a `title`
- **Implementation:** `resources/js/components/legacy/SparkSummary.vue:100` — `<span v-else title="No star count was recorded for this spark.">N/A</span>` inside the sparks table cell.
- **Documented expectation (one sentence):** An absence states its reason where a keyboard and a screen reader can reach it — via `AbsenceValue`'s disclosure or visible prose — and a `title`-only reason is the superseded, unreachable idiom (`DESIGN.md:893-899`, `DESIGN.md:960-965`).
- **Likely impact:** The "why" of the missing star count is mouse-hover-only and disappears on touch; the figure looks unexplained.
- **Re-measure:** Tab through a Spark row whose star count is null and check whether any reason is announced or disclosed.

### C-06 — Race Planner's comparison and alignment wide tables are not keyboard-scrollable
- **Implementation:** `resources/js/pages/Career/RacePlanner.vue:351` and `:381` — two `<div class="overflow-x-auto">` wrappers around `min-w-[40rem]` tables with no `role="region"`, `tabindex="0"` or `aria-label`; the cells at `:364` also carry title-only `N/A` reasons.
- **Documented expectation (one sentence):** Wide tables keep their clipped columns reachable by keyboard through a labelled focusable scroll region (`overflow-x-auto` + `role="region"` + `tabindex="0"` + `aria-label`) rather than reflowing or hiding them (`DESIGN.md:458-466`).
- **Likely impact:** At three-or-more selected races on a narrow screen, keyboard and screen-reader users cannot read the compared columns at all.
- **Re-measure:** select 3 races at 768px, Tab to the comparison/alignment tables and test arrow-key scrolling plus the region's accessible name.

### C-07 — Unavailable pagination controls use `aria-disabled` instead of the named-absence shape
- **Implementation:** `resources/js/pages/Career/TraineeSelect.vue:889` and `:904` render `<span aria-disabled="true">Previous/Next</span>`; `resources/js/pages/Runs/Index.vue:315` and `:323` do the same for the run list, with no visible reason.
- **Documented expectation (one sentence):** `aria-disabled` alone is never the shape for an unavailable destination or action — it announces a state without cancelling activation — so the control must be natively disabled or be a non-focusable named absence that states its reason (`DESIGN.md:984-990`; the same shape is recorded as the Cockpit fix at `SCREEN_SPEC.md:1932-1936`).
- **Likely impact:** A screen reader announces "Previous, dimmed" with no explanation, and the surfaced state is not the documented named absence.
- **Re-measure:** on page 1 of `/training-runs` and `/career/setup/trainee`, inspect the unavailable pager elements' role, `aria-disabled`, focusability and any adjacent reason text.

### C-08 — Race Planner comparison `N/A` cells expose the reason only via `title`
- **Implementation:** `resources/js/pages/Career/RacePlanner.vue:364` — `<span v-else class="text-ink-muted" :title="cell(...)?.title ?? undefined">N/A</span>`.
- **Documented expectation (one sentence):** The reason behind an absence must be reachable by keyboard and screen reader through the `AbsenceValue` disclosure or visible prose, never through a `title` alone (`DESIGN.md:893-899`, `DESIGN.md:960-965`).
- **Likely impact:** A Trainer comparing races cannot discover why a property is blank without a mouse hover.
- **Re-measure:** Tab to a comparison cell whose value is null and check for an announced reason or disclosure.

### C-09 — Transfer Request dates are formatted in the browser's locale/timezone
- **Implementation:** `resources/js/pages/Veterans/TransferRequests.vue:164` — `new Date(value).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })`, called from `:234` and `:242` for completed/withdrawn timestamps, with no configured display timezone and no zone suffix.
- **Documented expectation (one sentence):** Timestamps always render in `display_timezone` and always carry a zone suffix, while date-only values never render a time (`DESIGN.md:891-892`).
- **Likely impact:** A UTC-midnight timestamp can display one day earlier for a Trainer west of UTC, and no zone is shown to disambiguate.
- **Re-measure:** compare the rendered completed date against the stored timestamp under two browser timezones (e.g. UTC and UTC−8).

### C-10 — Run-list pager unavailable spans are 36px while sibling controls are 44px
- **Implementation:** `resources/js/pages/Runs/Index.vue:316` and `:324` use `min-h-9` (36px) on the disabled Previous/Next spans, whereas the live pager controls are `LinkButton` (`buttonStyles.ts:7`, `min-h-11` = 44px) and `TraineeSelect.vue:883/898` uses `min-h-11` on the same row.
- **Documented expectation (one sentence):** The house target bar is 44px (`min-h-11`/`h-11`) — stricter than WCAG 2.2's 24px floor — and 44px is the design intent for controls (`DESIGN.md:1357-1358`; framework index conflict row at `docs/framework-ui-ux/design-framework-index-2026-10-21.md:84`).
- **Likely impact:** The unavailable pager boxes are visibly shorter than the adjacent live buttons; if any becomes interactive the tap target misses intent.
- **Re-measure:** computed height of the disabled versus live pager elements on `/training-runs` at desktop and mobile widths.

## Coverage / exclusions (read-only, so recorded explicitly)

- No rendered screenshot, computed style, focus order, or interaction was observed. Every "likely impact" is a prediction to be confirmed or withdrawn by the parent agent.
- Frameworks consulted only via `design-framework-index-2026-10-21.md`; the derived rule files in `docs/framework-ui-ux/` were not re-opened, so rule IDs from them are not cited here. The expectations above come from `DESIGN.md`, `SCREEN_SPEC.md` and the index's recorded conflicts only.
- Absence-class claims were kept narrow: each cites a specific rendered element rather than asserting a capability is missing application-wide. No "not implemented anywhere" claim is made.
- Items examined and deliberately **not** filed: the mobile `More` disclosure and sticky collapsible rail (`AppLayout.vue:646-708`, `:381-403`) match their documented shape; the `to: null` "not built" branch (`AppLayout.vue:458-472`, `:663-666`, `:699-702`) is a dead branch today — the comment at `:135-138` says no destination uses it — so it is not a live divergence; empty-state copy in the catalog, skills, support-card, run, veteran and review lists states what is missing, why and the next step, so it clears `DESIGN.md:993-995`; `ui/PageHeader.vue:68` and `AppLayout.vue:591` intentionally choose one `h1` path between them; confirmation for the destructive delete is a dedicated refusal page (`Career/ConfirmDelete.vue:34-46`, `:72-87`) and was not flagged.
- No application, documentation, config or test file was edited; no build, git or write command outside this scratch file was run.
