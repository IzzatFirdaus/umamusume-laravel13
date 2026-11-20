# TrainerDesk · Four-screen UX review

**19 November 2026 · Azure Daybreak**  
**Status** The rollout is implemented in the working tree. Focused PHP and Chromium verification passed. The screenshots and examples below record the original review. The implementation update records the broader gate failures.

| Screenshot metric | Value |
| --- | ---: |
| Review: pending | 304 |
| Review: resolved that day | 8 |
| Veterans: registered | 8 |
| Races: listed slots | 410 |

## Review · Very high priority

**Make one correct decision at a time.** Show pending, resolved today and rows shown (25 in the screenshot) separately. The screenshot showed “0 of 304 resolved” beside “8 resolved today.” The source check found the progress bar uses all-time resolutions plus pending for its denominator. Name the different scopes rather than treating the screenshot as proof of a current arithmetic bug.

| Import evidence (illustrative) | Catalogue comparison (illustrative) |
| --- | --- |
| Tokyo Yushun (Japanese Derby), gametora-races · 5 Nov 2026 | No suggested match. Do not imply a confidence score exists. |
| Keep the recorded reason and original-language label when available. | When there is a candidate, show its canonical name, identifier, known aliases and evidence for or against the match. |

Explain the queue once, then let each row emphasize the imported name, source, date and evidence. Reuse the side-by-side decision view and explicit verdict. Leave without saving, announce success, and make the next pending case easy to reach without losing queue position or supported filters. Do not automatically or bulk approve pending cases.

**Source check:** [Review index](../../resources/js/pages/Review/Index.vue#L63) uses all-time counts. [Review detail](../../resources/js/pages/Review/Detail.vue#L132) pairs source and candidate. [Review controller](../../app/Http/Controllers/ReviewController.php#L201) records a manual verdict and returns to the queue with confirmation.

## Skills · High priority

**Put effect and cost first.** Group name search with Scope, Unique, Type and Distance. Keep the selected Global or Japan scope visible, summarize active filters, and distinguish matching results from the scope total. Separate Library navigation, Database section links and local filters.

| Default results | Technical details |
| --- | --- |
| Linked skill name, alternate name and Unique badge when supported | Exact, selectable engine predicates in monospace |
| Verified type, readable effect and base SP cost | Source provenance and unresolved interpretations |
| `N/A` with an accessible reason when a value is absent | Distinguish unknown classification from intentionally untyped |

**Source check:** [Skill catalogue](../../resources/js/components/skills/SkillCatalog.vue#L399) shows the engine condition beside the effect. [Skill detail](../../resources/js/pages/Skills/Show.vue#L286) already exposes exact predicates. Keep the catalogue read-only. Any sorting must cover the complete filtered result, not just the visible page.

## Veterans · High priority

**Help Trainers find and compare finished careers.** Keep portrait, name, scenario, completion status and date, and a short Spark summary in the row. Move long notes and alternate names to detail. Group selected count, Compare and Clear selection. Comparison must accept only a valid selection and allow no more than six Veterans.

| Veteran | Completion | Scenario | Sparks | Action |
| --- | --- | --- | --- | --- |
| Vodka | Completed · 14 Nov | URA Finale | N/A · Not recorded | Open |
| Daiwa Scarlet | Completed · 14 Nov | URA Finale | N/A · Not recorded | Open |
| Tokai Teio | Completed · 14 Nov | URA Finale | N/A · Not recorded | Open |

*Conceptual compact library. Example records from the screenshot review, not queried records.*

Make Open the routine route. Separate Offer for Transfer visually and retain confirmation explaining the consequence. Keep team eligibility visible, fold Spark grades into **How Sparks work**, and hide pagination when there is only one page.

**Source check:** [Veteran compare request](../../app/Http/Requests/VeteranCompareRequest.php#L33) caps comparisons at six. [Veterans index](../../resources/js/pages/Veterans/Index.vue#L219) already confirms transfers.

## Races · Medium–high priority

**Keep the dense reference table.** Emphasize the race name and right-align distance and fan requirements with tabular numerals. Add name, grade, distance and fan-requirement sorts over the complete filtered set. Show direction visually and use `aria-sort` on the active header.

| Distance | Catalogue band | Source export | Proposed cue |
| --- | --- | --- | --- |
| 1,400 m | Sprint | Mile | Source conflict, linked to the explanation |

Describe only what the available filters do: search by name, grade, surface and distance. They do not calculate trainee aptitude. Keep the distance bands and payout caveats reachable under **Race reference**.

**Source check:** [Database controller](../../app/Http/Controllers/DatabaseController.php#L120) filters on those four fields and orders by calendar. [Races page](../../resources/js/pages/Database/Races.vue#L304) already marks the 1,400 m conflict and formats numeric columns.

## Shared design, rollout and quality checks

Make page headings, section headings and normal reading text distinct. Give tables and forms opaque reading surfaces and keep stadium art around them. Preserve semantic roles across Azure Daybreak, Midnight Circuit and EVA-01: primary actions, actual success and special items should look different. Keep routes intact. Use the [shared absence convention](../../DESIGN.md#L1182) rather than rendering unrecorded data as zero.

### Rollout

- [x] Audit shared typography, controls, table spacing, surfaces and selection states.
- [x] Tighten Review's queue and decision flow without changing manual resolution.
- [x] Put Skills search and effects first; move raw conditions into Technical details.
- [x] Compact Veterans and group comparison controls without changing transfers.
- [x] Add Races sorting without sacrificing density or source caveats.

### Acceptance checks

| Check | Target |
| --- | --- |
| Contrast | At least 4.5:1 for normal text and 3:1 for required non-text control boundaries and states, subject to the standards' exceptions. |
| Targets | Keep TrainerDesk's 44 CSS px house target for controls. WCAG 2.2 AA generally sets 24 × 24 CSS px with exceptions. |
| Keyboard and tables | Visible, unobscured focus; logical order; semantic headers and sorting; accessible disclosure controls. |
| Reflow | At 320 CSS px, contain necessary two-dimensional scrolling within the table region, not the page. |
| Feedback | Announce updated results and successful decisions without unnecessary focus movement. |
| Theme regression | Check all four routes in all three themes after shared changes. [KI-160](../../KNOWN-ISSUES.md#L4997) concerns historical Cockpit overflow, not evidence that these routes fail. |

## Implementation update · 20 November 2026

| Area | Working-tree result |
| --- | --- |
| Shared design | The four screens use the existing heading tiers, semantic tokens and opaque table or form panels. Scrolling stays inside named table regions. The palette and wallpaper wiring are shared. |
| Review | Pending, resolved today and rows shown have separate labels. All-time progress has its own line. A verdict carries the current page back to the queue and offers the next pending case. The decision page shows source and catalog identifiers and recorded aliases. |
| Skills | Applied scope, filters and result totals are visible. The table puts the readable effect and base SP ahead of a Technical details disclosure. Unmapped or missing classifications and intentionally untyped effects have distinct reasons. |
| Veterans | The table shows identity, completion status, the filing date, recorded Sparks and parent-use count. Notes and alternate names are available on detail. Compare enables for two through six selected rows, and the transfer confirmation remains separate. |
| Races | Name, grade, distance and fan-requirement controls sort all matching slots before pagination. The active header shows the direction. Calendar order remains available, unrecorded numbers sort last, and the 1,400 m conflict opens Race reference. |

The Veteran filing date is labeled separately because the application does not store a completion timestamp. The library requires two to six selected Veterans for Compare. Existing direct comparison links to a single filed Veteran are still supported by the comparison route.

### Verification on `8fd7e18e` with working-tree changes

| Check | Observed result |
| --- | --- |
| Focused Pest | 142 passed, 2,368 assertions. Covers Review, Skills, Veterans, Races, copy density and UI hygiene. Ran with the existing in-memory settings and a scratch runner config because the default bootstrap path failed PHP's readability check. |
| Production assets | `npm run build -- --configLoader runner` passed. Vite reported the existing `/img/backdrop.webp` fallback warning. |
| Type and style | `npm run typecheck`, `npm run lint:ui`, and Pint on the changed PHP paths passed. UI lint reported advisory findings. |
| Theme and responsive matrix | Chromium passed all 24 screen, theme and viewport combinations at 320 px and 1280 px. Each check required keyboard focus with a visible, unobscured ring, no document overflow, and zero flagged axe violations. |
| Browser interactions | Chromium passed 19 checks plus the scratch fixture cleanup. It covered Review decisions and queue continuation, Skills filter persistence and technical details, Veterans selection and transfer confirmation, and Races ordering before pagination. The standard loopback runner was blocked by Windows socket permissions and the full seed failure, so Chromium received responses from Laravel's HTTP kernel against a session-private SQLite file. |
| Contrast samples | Text on table surfaces, focus rings, and the Review progress fill were checked across all three themes. Muted text on panel surfaces measured 6.81:1 or higher. The progress fill against its track measured 3.86:1 or higher. |
| Full Pest | The first run had 2,515 passes and eight failures. The Review copy check passed after restoring the short explanation. The Review controller's line ending was normalized and cleared from the follow-up line-ending check. Remaining failures concern loopback browser access (four cases), line endings outside this slice, the race fan/reward seed inputs, and the career-delete redirect. |
| PHPStan | Blocked before analysis. The Windows runner reported `phpstan.neon` unreadable. |
| Lore checks | Both advisory checks ran. Docs returned 632 hits and 75 exemptions, up 154 hits from the recorded baseline. Code returned 368 hits, up 186. In this slice, `stable` describes software ordering or test selectors, and `endurance` quotes a source fixture. Existing internal `factor` references describe the recorded Spark data. |

## Sources

- Attached *TrainerDesk UI/UX Improvement Review*, §§1–7, and its 19 November 2026 screenshot observations.
- Repository source checks linked above; the counts at the top are not repository measurements.
- [NN/g: Data Tables: Four Major User Tasks](https://www.nngroup.com/articles/data-tables/) and [Ten Usability Heuristics](https://www.nngroup.com/articles/ten-usability-heuristics/).
- [IBM Carbon: Data table](https://www.carbondesignsystem.com/building-blocks/core/components/data-table/guidelines).
- [GOV.UK Design System: Tables](https://design-system.service.gov.uk/components/table/) and [Pagination](https://design-system.service.gov.uk/components/pagination/).
- [W3C: Sortable Table Example](https://www.w3.org/WAI/ARIA/apg/patterns/table/examples/sortable-table/) and [WCAG 2.2](https://www.w3.org/TR/WCAG22/).
